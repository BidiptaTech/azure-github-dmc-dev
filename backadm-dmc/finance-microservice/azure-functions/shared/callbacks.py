"""Payment gateway callback processing with idempotency and retry support."""
import hashlib
import json
from datetime import datetime, timezone
from decimal import Decimal

from shared.auth import verify_webhook_signature
from shared.config import get_settings
from shared.db import db_transaction, execute, execute_returning, fetch_one
from shared.logger import log_event, log_warning
from shared.payment import update_tour_finance_snapshot
from shared.settlement import process_online_settlement
from shared.models import OnlineSettlementRequest


class CallbackError(Exception):
    pass


def _hash_payload(payload: dict) -> str:
    canonical = json.dumps(payload, sort_keys=True, separators=(",", ":"), default=str)
    return hashlib.sha256(canonical.encode("utf-8")).hexdigest()


def process_payment_callback(
    gateway_name: str,
    gateway_ref: str,
    body: dict,
    raw_body: bytes,
    signature: str | None,
) -> dict:
    if signature and not verify_webhook_signature(raw_body, signature):
        raise CallbackError("Invalid webhook signature")

    payload_hash = _hash_payload(body)

    with db_transaction() as conn:
        existing_callback = fetch_one(
            conn,
            """
            SELECT * FROM callback_processing_log
            WHERE gateway_name = %s AND gateway_transaction_reference = %s
            """,
            (gateway_name, gateway_ref),
        )
        if existing_callback:
            log_event("callback_duplicate_ignored", gateway_ref=gateway_ref)
            return {
                "status": "already_processed",
                "gateway_transaction_reference": gateway_ref,
            }

        payment = fetch_one(
            conn,
            """
            SELECT * FROM payment_transactions
            WHERE gateway_transaction_reference = %s
               OR finance_reference = %s
            ORDER BY created_at DESC
            LIMIT 1
            FOR UPDATE
            """,
            (gateway_ref, body.get("merchant_reference", "")),
        )

        status = (
            body.get("status")
            or body.get("payment_status")
            or body.get("event")
            or ""
        ).lower()

        if status in ("failed", "failure", "payment.failed"):
            if payment:
                execute(
                    conn,
                    """
                    UPDATE payment_transactions
                    SET payment_status = 'failed',
                        payment_gateway_response = %s::jsonb,
                        last_error_message = %s,
                        retry_count = retry_count + 1,
                        updated_at = NOW()
                    WHERE id = %s
                    """,
                    (json.dumps(body), body.get("error_message", "Payment failed"), payment["id"]),
                )
                update_tour_finance_snapshot(conn, payment["tour_id"], {
                    "payment_status": "failed",
                    "payment_gateway_response": body,
                    "finance_status": "failed",
                })
            _log_callback(conn, gateway_name, gateway_ref, payload_hash, payment["id"] if payment else None)
            return {"status": "failed", "gateway_transaction_reference": gateway_ref}

        if status in ("partial", "partially_paid", "payment.partial"):
            received = Decimal(str(body.get("amount_paid", body.get("amount", 0))))
            if payment:
                execute(
                    conn,
                    """
                    UPDATE payment_transactions
                    SET payment_status = 'partial',
                        payment_received_amount = %s,
                        payment_gateway_response = %s::jsonb,
                        payment_received_datetime = NOW(),
                        updated_at = NOW()
                    WHERE id = %s
                    """,
                    (received, json.dumps(body), payment["id"]),
                )
                update_tour_finance_snapshot(conn, payment["tour_id"], {
                    "payment_status": "partial",
                    "payment_received_amount": received,
                    "payment_gateway_response": body,
                    "finance_status": "partially_settled",
                })
            _log_callback(conn, gateway_name, gateway_ref, payload_hash, payment["id"] if payment else None)
            return {"status": "partial", "gateway_transaction_reference": gateway_ref}

        if status not in ("success", "captured", "paid", "payment.captured", "authorized"):
            raise CallbackError(f"Unrecognized callback status: {status}")

        if not payment:
            raise CallbackError(f"No payment record found for gateway ref {gateway_ref}")

        if payment["payment_status"] == "captured":
            _log_callback(conn, gateway_name, gateway_ref, payload_hash, payment["id"])
            return {"status": "already_captured", "gateway_transaction_reference": gateway_ref}

        received_amount = Decimal(str(
            body.get("amount_paid", body.get("amount", payment["selling_price"]))
        ))

        execute(
            conn,
            """
            UPDATE payment_transactions
            SET payment_status = 'captured',
                payment_received_amount = %s,
                payment_received_currency = %s,
                payment_received_datetime = NOW(),
                payment_gateway_response = %s::jsonb,
                payment_gateway_name = %s,
                updated_at = NOW()
            WHERE id = %s
            """,
            (
                received_amount,
                body.get("currency", payment.get("payment_received_currency", "USD")),
                json.dumps(body),
                gateway_name,
                payment["id"],
            ),
        )

        _log_callback(conn, gateway_name, gateway_ref, payload_hash, payment["id"])

    settlement_request = OnlineSettlementRequest(
        idempotency_key=f"callback-{gateway_ref}",
        tour_id=payment["tour_id"],
        payment_transaction_id=payment["id"],
        gateway_transaction_reference=gateway_ref,
        payment_gateway_name=gateway_name,
        supplier_api_id=payment["supplier_api_id"] or "unknown",
        supplier_booking_reference=payment.get("supplier_booking_reference"),
        supplier_cost=Decimal(str(payment["supplier_cost"])),
        travclicks_markup=Decimal(str(payment["travclicks_markup"])),
        dmc_commission=Decimal(str(payment["dmc_commission"])),
        selling_price=Decimal(str(payment["selling_price"])),
        payment_received_amount=received_amount,
        payment_received_currency=body.get("currency", "USD"),
        payment_received_datetime=datetime.now(timezone.utc),
        gateway_response_json=body,
    )

    settlement = process_online_settlement(
        settlement_request,
        settlement_reference=f"SET-{gateway_ref}",
    )

    log_event(
        "callback_processed",
        gateway_ref=gateway_ref,
        tour_id=payment["tour_id"],
        settlement_reference=settlement["settlement_reference"],
    )

    return {
        "status": "captured",
        "gateway_transaction_reference": gateway_ref,
        "settlement_uuid": str(settlement["settlement_uuid"]),
        "settlement_status": settlement["settlement_status"],
    }


def _log_callback(conn, gateway_name, gateway_ref, payload_hash, payment_id):
    execute_returning(
        conn,
        """
        INSERT INTO callback_processing_log
            (gateway_name, gateway_transaction_reference, callback_payload_hash, payment_transaction_id)
        VALUES (%s, %s, %s, %s)
        ON CONFLICT (gateway_name, gateway_transaction_reference) DO NOTHING
        RETURNING id
        """,
        (gateway_name, gateway_ref, payload_hash, payment_id),
    )
