"""Settlement logic: supplier deduction, markup distribution, ledger creation."""
import json
from datetime import datetime, timezone
from decimal import Decimal

from shared.db import db_transaction, execute, execute_returning, fetch_one
from shared.logger import log_event
from shared.models import OfflineSettlementRequest, OnlineSettlementRequest
from shared.payment import create_payment_transaction, update_tour_finance_snapshot
from shared.wallet import credit_wallet, debit_wallet, get_or_create_wallet


def calculate_commission(
    supplier_cost: Decimal,
    travclicks_markup_percent: Decimal,
    dmc_commission_percent: Decimal,
    agent_markup_percent: Decimal = Decimal("0"),
) -> dict:
    """
    Business rule:
      travclicks_price = supplier_cost + travclicks_markup
      selling_price = travclicks_price + dmc_commission + agent_markup
    Percentages applied on supplier_cost and travclicks_price respectively.
    """
    travclicks_markup = (supplier_cost * travclicks_markup_percent / Decimal("100")).quantize(Decimal("0.0001"))
    travclicks_price = supplier_cost + travclicks_markup
    dmc_commission = (travclicks_price * dmc_commission_percent / Decimal("100")).quantize(Decimal("0.0001"))
    agent_markup = (travclicks_price * agent_markup_percent / Decimal("100")).quantize(Decimal("0.0001"))
    selling_price = travclicks_price + dmc_commission + agent_markup

    return {
        "supplier_cost": supplier_cost,
        "travclicks_markup": travclicks_markup,
        "travclicks_price": travclicks_price,
        "dmc_commission": dmc_commission,
        "selling_price": selling_price,
        "travclicks_profit": travclicks_markup,
        "dmc_profit": dmc_commission,
    }


def _create_settlement(
    conn,
    settlement_reference: str,
    tour_id: int,
    payment_transaction_id: int,
    settlement_type: str,
    supplier_api_id: str,
    supplier_cost: Decimal,
    travclicks_markup: Decimal,
    dmc_commission: Decimal,
    selling_price: Decimal,
    supplier_booking_reference: str | None = None,
    wallet_debit_amount: Decimal | None = None,
    wallet_credit_amount: Decimal | None = None,
    idempotency_key: str | None = None,
    metadata: dict | None = None,
) -> dict:
    travclicks_profit = travclicks_markup
    dmc_profit = dmc_commission

    return execute_returning(
        conn,
        """
        INSERT INTO settlements
            (settlement_reference, tour_id, payment_transaction_id, settlement_type,
             settlement_status, supplier_api_id, supplier_booking_reference,
             supplier_cost, travclicks_markup, travclicks_profit,
             dmc_commission, dmc_profit, selling_price,
             wallet_debit_amount, wallet_credit_amount,
             finance_status, settled_at, settlement_metadata, idempotency_key)
        VALUES (%s, %s, %s, %s, 'completed', %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
                'settled', NOW(), %s::jsonb, %s)
        RETURNING *
        """,
        (
            settlement_reference, tour_id, payment_transaction_id, settlement_type,
            supplier_api_id, supplier_booking_reference,
            supplier_cost, travclicks_markup, travclicks_profit,
            dmc_commission, dmc_profit, selling_price,
            wallet_debit_amount or Decimal("0"),
            wallet_credit_amount or Decimal("0"),
            json.dumps(metadata or {}),
            idempotency_key,
        ),
    )


def _deduct_supplier_wallet(
    conn,
    supplier_api_id: str,
    supplier_cost: Decimal,
    finance_reference: str,
    tour_id: int,
    settlement_id: int,
    currency_code: str = "USD",
) -> None:
    """Deduct supplier API wallet credits (Travclicks → Supplier)."""
    if supplier_cost <= 0 or supplier_api_id in ("offline", "none", ""):
        return

    supplier_wallet = get_or_create_wallet(
        conn, "supplier", 0, currency_code, supplier_api_id,
    )
    debit_wallet(
        conn, supplier_wallet, supplier_cost, finance_reference,
        "booking_debit", f"Supplier API deduction for {supplier_api_id}",
        tour_id=tour_id, settlement_id=settlement_id,
    )


def _booking_credit_exists(
    conn,
    settlement_id: int,
    owner_type: str,
    owner_id: int,
) -> bool:
    row = fetch_one(
        conn,
        """
        SELECT wl.id
        FROM wallet_ledger wl
        JOIN wallet w ON w.id = wl.wallet_id
        WHERE wl.settlement_id = %s
          AND wl.ledger_entry_type = 'booking_credit'
          AND w.wallet_owner_type = %s
          AND w.wallet_owner_id = %s
        LIMIT 1
        """,
        (settlement_id, owner_type, owner_id),
    )
    return row is not None


def _distribute_wallet_booking_proceeds(
    conn,
    payment: dict,
    settlement: dict,
    dmc_id: int | None = None,
    agent_id: int | None = None,
) -> None:
    """
    After agent wallet debit, credit DMC commission and Travclicks markup wallets.
    selling_price = supplier_cost + travclicks_markup + dmc_commission
    """
    currency = payment.get("payment_received_currency") or "USD"
    settlement_id = settlement["id"]
    settlement_ref = settlement["settlement_reference"]
    tour_id = payment["tour_id"]
    dmc_commission = Decimal(str(payment["dmc_commission"]))
    travclicks_markup = Decimal(str(payment["travclicks_markup"]))

    if dmc_id and dmc_commission > 0 and not _booking_credit_exists(conn, settlement_id, "dmc", dmc_id):
        dmc_wallet = get_or_create_wallet(conn, "dmc", dmc_id, currency)
        credit_wallet(
            conn,
            dmc_wallet,
            dmc_commission,
            settlement_ref,
            "booking_credit",
            f"Booking commission credit for tour {tour_id}",
            tour_id=tour_id,
            payment_transaction_id=payment["id"],
            settlement_id=settlement_id,
            metadata={
                "transfer": {
                    "from_type": "agent" if agent_id else "wallet",
                    "from_id": agent_id or 0,
                    "to_type": "dmc",
                    "to_id": dmc_id,
                    "entry": "dmc_commission",
                }
            },
        )

    if travclicks_markup > 0 and not _booking_credit_exists(conn, settlement_id, "travclicks", 1):
        travclicks_wallet = get_or_create_wallet(conn, "travclicks", 1, currency)
        credit_wallet(
            conn,
            travclicks_wallet,
            travclicks_markup,
            settlement_ref,
            "booking_credit",
            f"Travclicks markup for tour {tour_id}",
            tour_id=tour_id,
            payment_transaction_id=payment["id"],
            settlement_id=settlement_id,
            metadata={
                "transfer": {
                    "from_type": "agent" if agent_id else "wallet",
                    "from_id": agent_id or 0,
                    "to_type": "travclicks",
                    "to_id": 1,
                    "entry": "travclicks_markup",
                }
            },
        )

    total_credited = Decimal("0")
    if dmc_id and dmc_commission > 0:
        total_credited += dmc_commission
    if travclicks_markup > 0:
        total_credited += travclicks_markup

    if total_credited > 0:
        execute(
            conn,
            """
            UPDATE settlements
            SET wallet_credit_amount = %s, updated_at = NOW()
            WHERE id = %s
            """,
            (total_credited, settlement_id),
        )


def distribute_wallet_booking_proceeds_for_payment(
    conn,
    payment_transaction_id: int,
    dmc_id: int | None = None,
    agent_id: int | None = None,
) -> dict:
    """Idempotent repair: credit DMC/Travclicks for an existing wallet booking payment."""
    payment = fetch_one(
        conn,
        "SELECT * FROM payment_transactions WHERE id = %s FOR UPDATE",
        (payment_transaction_id,),
    )
    if not payment:
        raise ValueError(f"Payment transaction {payment_transaction_id} not found")

    settlement = fetch_one(
        conn,
        "SELECT * FROM settlements WHERE payment_transaction_id = %s ORDER BY id DESC LIMIT 1",
        (payment_transaction_id,),
    )
    if not settlement:
        raise ValueError(f"No settlement found for payment {payment_transaction_id}")

    _distribute_wallet_booking_proceeds(conn, payment, settlement, dmc_id, agent_id)
    return settlement


def process_wallet_booking_settlement(
    conn,
    tour_id: int,
    payment_transaction_id: int,
    idempotency_key: str,
    settlement_reference: str,
    dmc_id: int | None = None,
    agent_id: int | None = None,
) -> dict:
    existing = fetch_one(
        conn,
        "SELECT * FROM settlements WHERE idempotency_key = %s",
        (idempotency_key,),
    )
    if existing:
        return existing

    payment = fetch_one(
        conn,
        "SELECT * FROM payment_transactions WHERE id = %s FOR UPDATE",
        (payment_transaction_id,),
    )
    if not payment:
        raise ValueError(f"Payment transaction {payment_transaction_id} not found")

    settlement = _create_settlement(
        conn,
        settlement_reference=settlement_reference,
        tour_id=tour_id,
        payment_transaction_id=payment_transaction_id,
        settlement_type="wallet_booking",
        supplier_api_id=payment["supplier_api_id"],
        supplier_cost=Decimal(str(payment["supplier_cost"])),
        travclicks_markup=Decimal(str(payment["travclicks_markup"])),
        dmc_commission=Decimal(str(payment["dmc_commission"])),
        selling_price=Decimal(str(payment["selling_price"])),
        supplier_booking_reference=payment.get("supplier_booking_reference"),
        wallet_debit_amount=Decimal(str(payment["selling_price"])),
        idempotency_key=idempotency_key,
    )

    _deduct_supplier_wallet(
        conn,
        payment["supplier_api_id"],
        Decimal(str(payment["supplier_cost"])),
        settlement_reference,
        tour_id,
        settlement["id"],
        payment.get("payment_received_currency") or "USD",
    )

    _distribute_wallet_booking_proceeds(
        conn, payment, settlement, dmc_id=dmc_id, agent_id=agent_id,
    )

    update_tour_finance_snapshot(conn, tour_id, {
        "settlement_id": settlement["id"],
        "finance_status": "settled",
        "finance_reference": payment["finance_reference"],
    })

    log_event(
        "wallet_booking_settlement_completed",
        settlement_reference=settlement_reference,
        tour_id=tour_id,
    )
    return settlement


def process_online_settlement(
    request: OnlineSettlementRequest,
    settlement_reference: str,
) -> dict:
    with db_transaction() as conn:
        existing = fetch_one(
            conn,
            "SELECT * FROM settlements WHERE idempotency_key = %s",
            (request.idempotency_key,),
        )
        if existing:
            return existing

        payment = fetch_one(
            conn,
            "SELECT * FROM payment_transactions WHERE id = %s FOR UPDATE",
            (request.payment_transaction_id,),
        )
        if not payment:
            raise ValueError("Payment transaction not found")

        execute(
            conn,
            """
            UPDATE payment_transactions
            SET payment_status = 'captured',
                gateway_transaction_reference = %s,
                payment_gateway_name = %s,
                payment_received_amount = %s,
                payment_received_currency = %s,
                payment_received_datetime = COALESCE(%s, NOW()),
                gateway_response_json = COALESCE(%s::jsonb, gateway_response_json),
                updated_at = NOW()
            WHERE id = %s
            """,
            (
                request.gateway_transaction_reference,
                request.payment_gateway_name,
                request.payment_received_amount,
                request.payment_received_currency,
                request.payment_received_datetime,
                json.dumps(request.gateway_response_json) if request.gateway_response_json else None,
                request.payment_transaction_id,
            ),
        )

        settlement = _create_settlement(
            conn,
            settlement_reference=settlement_reference,
            tour_id=request.tour_id,
            payment_transaction_id=request.payment_transaction_id,
            settlement_type="online_payment",
            supplier_api_id=request.supplier_api_id,
            supplier_cost=request.supplier_cost,
            travclicks_markup=request.travclicks_markup,
            dmc_commission=request.dmc_commission,
            selling_price=request.selling_price,
            supplier_booking_reference=request.supplier_booking_reference,
            wallet_credit_amount=request.payment_received_amount,
            idempotency_key=request.idempotency_key,
        )

        _deduct_supplier_wallet(
            conn,
            request.supplier_api_id,
            request.supplier_cost,
            settlement_reference,
            request.tour_id,
            settlement["id"],
            request.payment_received_currency,
        )

        update_tour_finance_snapshot(conn, request.tour_id, {
            "settlement_id": settlement["id"],
            "finance_status": "settled",
            "payment_status": "captured",
            "payment_mode": "online_gateway",
            "gateway_transaction_reference": request.gateway_transaction_reference,
            "payment_gateway_name": request.payment_gateway_name,
            "payment_received_amount": request.payment_received_amount,
            "payment_received_currency": request.payment_received_currency,
            "payment_received_datetime": request.payment_received_datetime or datetime.now(timezone.utc),
            "supplier_cost": request.supplier_cost,
            "travclicks_markup": request.travclicks_markup,
            "dmc_commission": request.dmc_commission,
            "selling_price": request.selling_price,
            "supplier_api_id": request.supplier_api_id,
            "supplier_booking_reference": request.supplier_booking_reference,
        })

        return settlement


def process_offline_settlement(
    request: OfflineSettlementRequest,
    settlement_reference: str,
) -> dict:
    with db_transaction() as conn:
        existing = fetch_one(
            conn,
            "SELECT * FROM settlements WHERE idempotency_key = %s",
            (request.idempotency_key,),
        )
        if existing:
            return existing

        finance_ref = settlement_reference.replace("OFF-", "FIN-") if settlement_reference.startswith("OFF-") else settlement_reference

        payment = create_payment_transaction(
            conn,
            finance_reference=finance_ref,
            tour_id=request.tour_id,
            payment_mode="offline_office",
            supplier_api_id=request.supplier_api_id,
            supplier_cost=request.supplier_cost,
            travclicks_markup=request.travclicks_markup,
            dmc_commission=request.dmc_commission,
            selling_price=request.selling_price,
            idempotency_key=request.idempotency_key,
            payment_status="captured",
            supplier_booking_reference=request.supplier_booking_reference,
            payment_received_amount=request.payment_received_amount,
            payment_received_currency=request.payment_received_currency,
        )

        settlement = _create_settlement(
            conn,
            settlement_reference=settlement_reference,
            tour_id=request.tour_id,
            payment_transaction_id=payment["id"],
            settlement_type="offline_payment",
            supplier_api_id=request.supplier_api_id,
            supplier_cost=request.supplier_cost,
            travclicks_markup=request.travclicks_markup,
            dmc_commission=request.dmc_commission,
            selling_price=request.selling_price,
            supplier_booking_reference=request.supplier_booking_reference,
            wallet_credit_amount=request.payment_received_amount,
            idempotency_key=f"{request.idempotency_key}-settlement",
            metadata={
                "finance_confirmed_by": request.finance_confirmed_by,
                "office_receipt_reference": request.office_receipt_reference,
            },
        )

        _deduct_supplier_wallet(
            conn,
            request.supplier_api_id,
            request.supplier_cost,
            settlement_reference,
            request.tour_id,
            settlement["id"],
            request.payment_received_currency,
        )

        update_tour_finance_snapshot(conn, request.tour_id, {
            "finance_reference": finance_ref,
            "payment_transaction_id": payment["id"],
            "settlement_id": settlement["id"],
            "finance_status": "settled",
            "payment_status": "captured",
            "payment_mode": "offline_office",
            "payment_received_amount": request.payment_received_amount,
            "payment_received_currency": request.payment_received_currency,
            "payment_received_datetime": request.payment_received_datetime or datetime.now(timezone.utc),
            "supplier_cost": request.supplier_cost,
            "travclicks_markup": request.travclicks_markup,
            "dmc_commission": request.dmc_commission,
            "selling_price": request.selling_price,
            "supplier_api_id": request.supplier_api_id,
            "supplier_booking_reference": request.supplier_booking_reference,
        })

        return settlement
