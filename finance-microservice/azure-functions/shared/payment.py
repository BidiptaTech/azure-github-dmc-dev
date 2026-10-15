"""Payment transaction creation and wallet booking payment."""
import json
from decimal import Decimal

from shared.db import execute, execute_returning, fetch_one
from shared.logger import log_event
from shared.models import WalletBookingRequest
from shared.wallet import debit_wallet, get_or_create_wallet


def create_payment_transaction(
    conn,
    finance_reference: str,
    tour_id: int,
    payment_mode: str,
    supplier_api_id: str,
    supplier_cost: Decimal,
    travclicks_markup: Decimal,
    dmc_commission: Decimal,
    selling_price: Decimal,
    idempotency_key: str,
    payment_status: str = "pending",
    payment_gateway_name: str | None = None,
    gateway_transaction_reference: str | None = None,
    gateway_response_json: dict | None = None,
    supplier_booking_reference: str | None = None,
    wallet_id: int | None = None,
    payment_received_amount: Decimal | None = None,
    payment_received_currency: str | None = None,
) -> dict:
    return execute_returning(
        conn,
        """
        INSERT INTO payment_transactions
            (finance_reference, tour_id, payment_mode, payment_status,
             payment_gateway_name, gateway_transaction_reference, gateway_response_json,
             supplier_api_id, supplier_booking_reference,
             supplier_cost, travclicks_markup, dmc_commission, selling_price,
             payment_received_amount, payment_received_currency,
             wallet_id, idempotency_key)
        VALUES (%s, %s, %s, %s, %s, %s, %s::jsonb, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        RETURNING *
        """,
        (
            finance_reference, tour_id, payment_mode, payment_status,
            payment_gateway_name, gateway_transaction_reference,
            json.dumps(gateway_response_json) if gateway_response_json else None,
            supplier_api_id, supplier_booking_reference,
            supplier_cost, travclicks_markup, dmc_commission, selling_price,
            payment_received_amount, payment_received_currency,
            wallet_id, idempotency_key,
        ),
    )


def update_tour_finance_snapshot(
    conn,
    tour_id: int,
    fields: dict,
) -> None:
    """Denormalize finance fields onto tours row for booking service reads."""
    set_clauses = []
    params = []
    for key, value in fields.items():
        if key in ("gateway_response_json", "payment_gateway_response") and value is not None:
            set_clauses.append(f"{key} = %s::jsonb")
            params.append(json.dumps(value))
        else:
            set_clauses.append(f"{key} = %s")
            params.append(value)

    if not set_clauses:
        return

    params.append(tour_id)
    execute(
        conn,
        f"UPDATE tours SET {', '.join(set_clauses)}, updated_at = NOW() WHERE id = %s",
        tuple(params),
    )


def process_wallet_booking_payment(
    conn,
    request: WalletBookingRequest,
    finance_ref: str,
) -> dict:
    """Create payment record and debit wallet atomically."""
    existing = fetch_one(
        conn,
        "SELECT * FROM payment_transactions WHERE idempotency_key = %s",
        (request.idempotency_key,),
    )
    if existing:
        log_event("wallet_booking_idempotent", idempotency_key=request.idempotency_key)
        return existing

    wallet = get_or_create_wallet(
        conn,
        request.wallet_owner_type,
        request.wallet_owner_id,
        request.wallet_currency_code,
    )

    payment = create_payment_transaction(
        conn,
        finance_reference=finance_ref,
        tour_id=request.tour_id,
        payment_mode="wallet",
        supplier_api_id=request.supplier_api_id,
        supplier_cost=request.supplier_cost,
        travclicks_markup=request.travclicks_markup,
        dmc_commission=request.dmc_commission,
        selling_price=request.selling_price,
        idempotency_key=request.idempotency_key,
        payment_status="captured",
        supplier_booking_reference=request.supplier_booking_reference,
        wallet_id=wallet["id"],
        payment_received_amount=request.selling_price,
        payment_received_currency=request.wallet_currency_code,
    )

    debit_wallet(
        conn, wallet, request.selling_price, finance_ref,
        "booking_debit", f"Wallet booking debit for tour {request.tour_id}",
        tour_id=request.tour_id,
        payment_transaction_id=payment["id"],
    )

    update_tour_finance_snapshot(conn, request.tour_id, {
        "finance_reference": finance_ref,
        "payment_transaction_id": payment["id"],
        "payment_mode": "wallet",
        "payment_status": "captured",
        "supplier_cost": request.supplier_cost,
        "travclicks_markup": request.travclicks_markup,
        "dmc_commission": request.dmc_commission,
        "selling_price": request.selling_price,
        "supplier_api_id": request.supplier_api_id,
        "supplier_booking_reference": request.supplier_booking_reference,
        "finance_status": "pending",
        "payment_received_amount": request.selling_price,
        "payment_received_currency": request.wallet_currency_code,
    })

    log_event(
        "wallet_booking_payment_created",
        finance_reference=finance_ref,
        tour_id=request.tour_id,
        payment_id=payment["id"],
    )
    return payment
