"""Refund processing: wallet, gateway, supplier."""
import json
from decimal import Decimal

from shared.db import db_transaction, execute, execute_returning, fetch_one
from shared.logger import log_event
from shared.models import RefundRequest
from shared.payment import update_tour_finance_snapshot
from shared.wallet import credit_wallet, get_or_create_wallet


class RefundError(Exception):
    pass


def process_refund(request: RefundRequest, finance_ref: str) -> dict:
    with db_transaction() as conn:
        payment = fetch_one(
            conn,
            "SELECT * FROM payment_transactions WHERE id = %s FOR UPDATE",
            (request.payment_transaction_id,),
        )
        if not payment:
            raise RefundError(f"Payment transaction {request.payment_transaction_id} not found")

        if payment["payment_status"] == "refunded":
            raise RefundError("Payment already fully refunded")

        if request.refund_amount > Decimal(str(payment["selling_price"])):
            raise RefundError("Refund amount exceeds selling price")

        refund_settlement = execute_returning(
            conn,
            """
            INSERT INTO settlements
                (settlement_reference, tour_id, payment_transaction_id, settlement_type,
                 settlement_status, supplier_api_id, supplier_cost, travclicks_markup,
                 travclicks_profit, dmc_commission, dmc_profit, selling_price,
                 wallet_credit_amount, finance_status, settled_at, settlement_metadata, idempotency_key)
            VALUES (%s, %s, %s, 'refund', 'completed', %s, %s, %s, %s, %s, %s, %s, %s,
                    'refunded', NOW(), %s::jsonb, %s)
            RETURNING *
            """,
            (
                finance_ref,
                request.tour_id,
                request.payment_transaction_id,
                payment["supplier_api_id"],
                payment["supplier_cost"],
                payment["travclicks_markup"],
                payment["travclicks_markup"],
                payment["dmc_commission"],
                payment["dmc_commission"],
                payment["selling_price"],
                request.refund_amount,
                json.dumps({
                    "refund_type": request.refund_type,
                    "refund_reason": request.refund_reason,
                    "gateway_refund_reference": request.gateway_refund_reference,
                    "supplier_refund_reference": request.supplier_refund_reference,
                }),
                request.idempotency_key,
            ),
        )

        if request.refund_type in ("wallet", "full") and request.wallet_owner_type and request.wallet_owner_id:
            wallet = get_or_create_wallet(
                conn,
                request.wallet_owner_type,
                request.wallet_owner_id,
                request.refund_currency_code,
            )
            credit_wallet(
                conn, wallet, request.refund_amount, finance_ref,
                "refund_credit", request.refund_reason,
                tour_id=request.tour_id,
                payment_transaction_id=payment["id"],
                settlement_id=refund_settlement["id"],
            )

        if request.refund_type in ("supplier", "full"):
            supplier_wallet = get_or_create_wallet(
                conn, "supplier", 0, request.refund_currency_code, payment["supplier_api_id"],
            )
            supplier_refund = min(
                request.refund_amount,
                Decimal(str(payment["supplier_cost"])),
            )
            credit_wallet(
                conn, supplier_wallet, supplier_refund, finance_ref,
                "refund_credit", f"Supplier refund for {payment['supplier_api_id']}",
                tour_id=request.tour_id,
                settlement_id=refund_settlement["id"],
            )

        new_status = "refunded" if request.refund_amount >= Decimal(str(payment["selling_price"])) else "partial"
        execute(
            conn,
            """
            UPDATE payment_transactions
            SET payment_status = %s, updated_at = NOW()
            WHERE id = %s
            """,
            (new_status, request.payment_transaction_id),
        )

        update_tour_finance_snapshot(conn, request.tour_id, {
            "finance_status": "refunded",
            "payment_status": new_status,
            "finance_reference": finance_ref,
            "settlement_id": refund_settlement["id"],
        })

        log_event(
            "refund_completed",
            finance_reference=finance_ref,
            tour_id=request.tour_id,
            refund_amount=str(request.refund_amount),
            refund_type=request.refund_type,
        )

        return {
            "payment_transaction_uuid": payment["payment_transaction_uuid"],
            "payment_status": new_status,
            "settlement_uuid": str(refund_settlement["settlement_uuid"]),
        }
