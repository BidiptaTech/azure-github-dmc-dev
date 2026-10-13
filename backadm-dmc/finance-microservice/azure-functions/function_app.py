"""Travclicks Finance Microservice — Azure Functions Python v2 entry point."""
import json
import traceback
from decimal import Decimal

import azure.functions as func

from shared.auth import (
  AuthError,
  ValidationError,
  check_idempotency,
  generate_finance_reference,
  hash_request_body,
  store_idempotency,
  verify_bearer_token,
  verify_webhook_signature,
)
from shared.callbacks import CallbackError, process_payment_callback
from shared.db import db_transaction
from shared.logger import log_event, log_exception
from shared.models import (
  ApiResponse,
  CommissionCalculationRequest,
  CommissionCalculationResponse,
  OfflineSettlementRequest,
  OnlineSettlementRequest,
  RefundRequest,
  WalletAllocateRequest,
  WalletBookingRequest,
  WalletTopupRequest,
)
from shared.payment import process_wallet_booking_payment
from shared.refund import RefundError, process_refund
from shared.settlement import (
  calculate_commission,
  distribute_wallet_booking_proceeds_for_payment,
  process_offline_settlement,
  process_online_settlement,
  process_wallet_booking_settlement,
)
from shared.wallet import InsufficientBalanceError, process_wallet_allocate, process_wallet_topup

app = func.FunctionApp(http_auth_level=func.AuthLevel.ANONYMOUS)


def _json_response(data: dict, status_code: int = 200) -> func.HttpResponse:
  return func.HttpResponse(
    json.dumps(data, default=str),
    status_code=status_code,
    mimetype="application/json",
  )


def _handle_request(req: func.HttpRequest, operation: str, handler):
  try:
    verify_bearer_token(req)
    body = req.get_json()
    request_hash = hash_request_body(body)

    idempotency_key = body.get("idempotency_key")
    if idempotency_key:
      cached = check_idempotency(idempotency_key, operation, request_hash)
      if cached:
        return _json_response(cached)

    result = handler(body)
    response = ApiResponse(success=True, message="OK", data=result).model_dump()
    if idempotency_key:
      store_idempotency(idempotency_key, operation, request_hash, response)
    return _json_response(response)

  except AuthError as exc:
    return _json_response(
      ApiResponse(success=False, message=str(exc), error_code="AUTH_ERROR").model_dump(),
      exc.status_code,
    )
  except ValidationError as exc:
    return _json_response(
      ApiResponse(success=False, message=str(exc), error_code="VALIDATION_ERROR").model_dump(),
      exc.status_code,
    )
  except InsufficientBalanceError as exc:
    return _json_response(
      ApiResponse(success=False, message=str(exc), error_code="INSUFFICIENT_BALANCE").model_dump(),
      402,
    )
  except (RefundError, CallbackError) as exc:
    return _json_response(
      ApiResponse(success=False, message=str(exc), error_code="BUSINESS_ERROR").model_dump(),
      422,
    )
  except Exception as exc:
    log_exception(f"{operation}_failed", exc)
    return _json_response(
      ApiResponse(
        success=False,
        message="Internal server error",
        error_code="INTERNAL_ERROR",
        data={"detail": str(exc)},
      ).model_dump(),
      500,
    )


@app.route(route="wallet/topup", methods=["POST"])
def WalletTopupFunction(req: func.HttpRequest) -> func.HttpResponse:
  """Credit wallet for DMC/Agent/Supplier prepaid balance."""
  def handler(body):
    request = WalletTopupRequest(**body)
    with db_transaction() as conn:
      finance_ref = generate_finance_reference(conn)
    return process_wallet_topup(request, finance_ref)

  return _handle_request(req, "wallet_topup", handler)


@app.route(route="wallet/allocate", methods=["POST"])
def WalletAllocateFunction(req: func.HttpRequest) -> func.HttpResponse:
  """Allocate funds: debit allocator wallet and credit recipient wallet."""

  def handler(body):
    request = WalletAllocateRequest(**body)
    with db_transaction() as conn:
      finance_ref = generate_finance_reference(conn, "ALLOC")
      return process_wallet_allocate(request, finance_ref)

  return _handle_request(req, "wallet_allocate", handler)


@app.route(route="wallet/booking", methods=["POST"])
def WalletBookingFunction(req: func.HttpRequest) -> func.HttpResponse:
  """Deduct wallet and settle booking atomically."""
  def handler(body):
    request = WalletBookingRequest(**body)
    with db_transaction() as conn:
      finance_ref = generate_finance_reference(conn)
      payment = process_wallet_booking_payment(conn, request, finance_ref)
      settlement_ref = f"SET-{finance_ref}"
      settlement = process_wallet_booking_settlement(
        conn, request.tour_id, payment["id"],
        f"{request.idempotency_key}-settlement", settlement_ref,
        dmc_id=request.dmc_id,
        agent_id=request.agent_id,
      )
    return {
      "finance_reference": finance_ref,
      "payment_transaction_uuid": str(payment["payment_transaction_uuid"]),
      "settlement_uuid": str(settlement["settlement_uuid"]),
      "settlement_status": settlement["settlement_status"],
    }

  return _handle_request(req, "wallet_booking", handler)


@app.route(route="wallet/booking/distribute-proceeds", methods=["POST"])
def DistributeBookingProceedsFunction(req: func.HttpRequest) -> func.HttpResponse:
  """Idempotent repair: credit DMC/Travclicks after agent wallet booking debit."""
  def handler(body):
    payment_transaction_id = int(body["payment_transaction_id"])
    dmc_id = int(body["dmc_id"]) if body.get("dmc_id") else None
    agent_id = int(body["agent_id"]) if body.get("agent_id") else None
    with db_transaction() as conn:
      settlement = distribute_wallet_booking_proceeds_for_payment(
        conn, payment_transaction_id, dmc_id=dmc_id, agent_id=agent_id,
      )
    return {
      "settlement_reference": settlement["settlement_reference"],
      "wallet_credit_amount": str(settlement.get("wallet_credit_amount", 0)),
    }

  return _handle_request(req, "wallet_booking_distribute_proceeds", handler)


@app.route(route="settlement/online", methods=["POST"])
def OnlineBookingSettlementFunction(req: func.HttpRequest) -> func.HttpResponse:
  """Settle online gateway-captured booking."""
  def handler(body):
    request = OnlineSettlementRequest(**body)
    settlement_ref = f"SET-{request.gateway_transaction_reference}"
    result = process_online_settlement(request, settlement_ref)
    return {
      "settlement_uuid": str(result["settlement_uuid"]),
      "settlement_reference": result["settlement_reference"],
      "settlement_status": result["settlement_status"],
      "travclicks_profit": str(result["travclicks_profit"]),
      "dmc_profit": str(result["dmc_profit"]),
    }

  return _handle_request(req, "online_settlement", handler)


@app.route(route="settlement/offline", methods=["POST"])
def OfflineBookingSettlementFunction(req: func.HttpRequest) -> func.HttpResponse:
  """Settle offline (office) payment after finance confirmation."""
  def handler(body):
    request = OfflineSettlementRequest(**body)
    with db_transaction() as conn:
      settlement_ref = generate_finance_reference(conn, "OFF")
    result = process_offline_settlement(request, settlement_ref)
    return {
      "settlement_uuid": str(result["settlement_uuid"]),
      "settlement_reference": result["settlement_reference"],
      "settlement_status": result["settlement_status"],
    }

  return _handle_request(req, "offline_settlement", handler)


@app.route(route="payment/callback", methods=["POST"])
def PaymentCallbackFunction(req: func.HttpRequest) -> func.HttpResponse:
  """Receive and process payment gateway webhook callbacks."""
  try:
    raw_body = req.get_body()
    body = json.loads(raw_body)
    signature = req.headers.get("X-Gateway-Signature") or req.headers.get("X-Webhook-Signature")

    gateway_name = body.get("gateway_name", body.get("payment_gateway_name", "default"))
    gateway_ref = body.get("transaction_id", body.get("gateway_transaction_reference", ""))

    if not gateway_ref:
      return _json_response(
        ApiResponse(success=False, message="Missing gateway transaction reference", error_code="VALIDATION_ERROR").model_dump(),
        400,
      )

    result = process_payment_callback(gateway_name, gateway_ref, body, raw_body, signature)
    return _json_response(ApiResponse(success=True, message="Callback processed", data=result).model_dump())

  except CallbackError as exc:
    return _json_response(
      ApiResponse(success=False, message=str(exc), error_code="CALLBACK_ERROR").model_dump(),
      422,
    )
  except Exception as exc:
    log_exception("payment_callback_failed", exc)
    return _json_response(
      ApiResponse(success=False, message="Callback processing failed", error_code="INTERNAL_ERROR").model_dump(),
      500,
    )


@app.route(route="refund", methods=["POST"])
def RefundFunction(req: func.HttpRequest) -> func.HttpResponse:
  """Process wallet/gateway/supplier refund."""
  def handler(body):
    request = RefundRequest(**body)
    with db_transaction() as conn:
      finance_ref = generate_finance_reference(conn, "REF")
    result = process_refund(request, finance_ref)
    return {
      "finance_reference": finance_ref,
      "payment_transaction_uuid": str(result["payment_transaction_uuid"]),
      "payment_status": result["payment_status"],
    }

  return _handle_request(req, "refund", handler)


@app.route(route="commission/calculate", methods=["POST"])
def CommissionCalculationFunction(req: func.HttpRequest) -> func.HttpResponse:
  """Calculate markup, commission, and selling price."""
  try:
    verify_bearer_token(req)
    body = req.get_json()
    request = CommissionCalculationRequest(**body)
    result = calculate_commission(
      request.supplier_cost,
      request.travclicks_markup_percent,
      request.dmc_commission_percent,
      request.agent_markup_percent,
    )
    response = CommissionCalculationResponse(
      **result,
      currency_code=request.currency_code,
    )
    return _json_response(
      ApiResponse(success=True, message="Commission calculated", data=response.model_dump(mode="json")).model_dump()
    )
  except AuthError as exc:
    return _json_response(ApiResponse(success=False, message=str(exc), error_code="AUTH_ERROR").model_dump(), exc.status_code)
  except Exception as exc:
    log_exception("commission_calculation_failed", exc)
    return _json_response(ApiResponse(success=False, message=str(exc), error_code="INTERNAL_ERROR").model_dump(), 500)


@app.route(route="health", methods=["GET"])
def HealthCheckFunction(req: func.HttpRequest) -> func.HttpResponse:
  """Liveness and database connectivity check."""
  try:
    from shared.db import fetch_one, get_connection
    with get_connection() as conn:
      row = fetch_one(conn, "SELECT 1 AS ok")
    db_ok = row and row.get("ok") == 1
    return _json_response({
      "status": "healthy" if db_ok else "degraded",
      "service": "finance-microservice",
      "database": "connected" if db_ok else "disconnected",
    })
  except Exception as exc:
    return _json_response({"status": "unhealthy", "error": str(exc)}, 503)


@app.route(route="webhook/verify", methods=["POST"])
def WebhookVerificationFunction(req: func.HttpRequest) -> func.HttpResponse:
  """Verify gateway webhook signature without processing."""
  try:
    raw_body = req.get_body()
    signature = req.headers.get("X-Gateway-Signature", "")
    verified = verify_webhook_signature(raw_body, signature) if signature else False
    return _json_response({
      "signature_verified": verified,
      "message": "Signature valid" if verified else "Signature invalid or missing",
    }, 200 if verified else 401)
  except Exception as exc:
    return _json_response({"signature_verified": False, "error": str(exc)}, 500)
