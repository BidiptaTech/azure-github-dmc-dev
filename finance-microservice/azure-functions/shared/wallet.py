"""Wallet operations: top-up, debit/credit ledger, and allocations.

Note: your DB finance schema appears to be an earlier variant with required
columns on wallet_ledger: (entry_type, amount, balance_before, balance_after,
currency_code, description). This module writes to both:
  - the legacy required columns (entry_type/amount/...)
  - the newer UI-friendly columns (ledger_entry_type/debit_amount/credit_amount/...)
"""
import json
from decimal import Decimal
from typing import Optional

from shared.db import db_transaction, execute, execute_returning, fetch_one
from shared.logger import log_event
from shared.models import WalletAllocateRequest, WalletTopupRequest


class InsufficientBalanceError(Exception):
    pass


def get_or_create_wallet(
    conn,
    owner_type: str,
    owner_id: int,
    currency_code: str,
    supplier_api_id: Optional[str] = None,
) -> dict:
    row = fetch_one(
        conn,
        """
        SELECT * FROM wallet
        WHERE wallet_owner_type = %s
          AND wallet_owner_id = %s
          AND wallet_currency_code = %s
          AND (supplier_api_id IS NOT DISTINCT FROM %s)
          AND deleted_at IS NULL
        FOR UPDATE
        """,
        (owner_type, owner_id, currency_code, supplier_api_id),
    )
    if row:
        return row

    return execute_returning(
        conn,
        """
        INSERT INTO wallet
            (wallet_owner_type, wallet_owner_id, supplier_api_id, wallet_currency_code)
        VALUES (%s, %s, %s, %s)
        RETURNING *
        """,
        (owner_type, owner_id, supplier_api_id, currency_code),
    )


def _create_ledger_entry(
    conn,
    wallet_id: int,
    finance_reference: str,
    ledger_entry_type: str,
    v1_entry_type: str,
    amount: Decimal,
    debit_amount: Decimal,
    credit_amount: Decimal,
    balance_before: Decimal,
    balance_after: Decimal,
    currency_code: str,
    description: str,
    tour_id: Optional[int] = None,
    payment_transaction_id: Optional[int] = None,
    settlement_id: Optional[int] = None,
    metadata: Optional[dict] = None,
) -> dict:
    return execute_returning(
        conn,
        """
        INSERT INTO wallet_ledger
            (wallet_id,
             finance_reference,
             entry_type,
             amount,
             balance_before,
             balance_after,
             currency_code,
             description,
             tour_id,
             payment_transaction_id,
             settlement_id,
             ledger_entry_type,
             debit_amount,
             credit_amount,
             ledger_description,
             ledger_metadata)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s::jsonb)
        RETURNING *
        """,
        (
            wallet_id,
            finance_reference,
            v1_entry_type,
            amount,
            balance_before,
            balance_after,
            currency_code,
            description,
            tour_id,
            payment_transaction_id,
            settlement_id,
            ledger_entry_type,
            debit_amount,
            credit_amount,
            description,
            json.dumps(metadata or {}),
        ),
    )


def credit_wallet(
    conn,
    wallet: dict,
    amount: Decimal,
    finance_reference: str,
    entry_type: str,
    description: str,
    tour_id: Optional[int] = None,
    payment_transaction_id: Optional[int] = None,
    settlement_id: Optional[int] = None,
    metadata: Optional[dict] = None,
) -> dict:
    balance_before = Decimal(str(wallet["available_balance"]))
    new_balance = balance_before + amount
    execute(
        conn,
        """
        UPDATE wallet
        SET available_balance = %s,
            total_credited = total_credited + %s,
            updated_at = NOW()
        WHERE id = %s
        """,
        (new_balance, amount, wallet["id"]),
    )
    return _create_ledger_entry(
        conn,
        wallet["id"],
        finance_reference,
        ledger_entry_type=entry_type,
        v1_entry_type="credit",
        amount=amount,
        debit_amount=Decimal("0"),
        credit_amount=amount,
        balance_before=balance_before,
        balance_after=new_balance,
        currency_code=str(wallet.get("wallet_currency_code") or wallet.get("currency_code") or "USD"),
        description=description,
        tour_id=tour_id,
        payment_transaction_id=payment_transaction_id,
        settlement_id=settlement_id,
        metadata=metadata,
    )


def debit_wallet(
    conn,
    wallet: dict,
    amount: Decimal,
    finance_reference: str,
    entry_type: str,
    description: str,
    tour_id: Optional[int] = None,
    payment_transaction_id: Optional[int] = None,
    settlement_id: Optional[int] = None,
    metadata: Optional[dict] = None,
) -> dict:
    balance_before = Decimal(str(wallet["available_balance"]))
    if balance_before < amount:
        raise InsufficientBalanceError(
            f"Insufficient wallet balance: available={balance_before}, required={amount}"
        )

    new_balance = balance_before - amount
    execute(
        conn,
        """
        UPDATE wallet
        SET available_balance = %s,
            total_debited = total_debited + %s,
            updated_at = NOW()
        WHERE id = %s
        """,
        (new_balance, amount, wallet["id"]),
    )
    return _create_ledger_entry(
        conn,
        wallet["id"],
        finance_reference,
        ledger_entry_type=entry_type,
        v1_entry_type="debit",
        amount=amount,
        debit_amount=amount,
        credit_amount=Decimal("0"),
        balance_before=balance_before,
        balance_after=new_balance,
        currency_code=str(wallet.get("wallet_currency_code") or wallet.get("currency_code") or "USD"),
        description=description,
        tour_id=tour_id,
        payment_transaction_id=payment_transaction_id,
        settlement_id=settlement_id,
        metadata=metadata,
    )


def process_wallet_topup(request: WalletTopupRequest, finance_ref: str) -> dict:
    with db_transaction() as conn:
        wallet = get_or_create_wallet(
            conn,
            request.wallet_owner_type,
            request.wallet_owner_id,
            request.wallet_currency_code,
            request.supplier_api_id,
        )
        ledger = credit_wallet(
            conn, wallet, request.topup_amount, finance_ref,
            "topup", request.ledger_description,
            metadata=request.metadata,
        )
        log_event(
            "wallet_topup_completed",
            finance_reference=finance_ref,
            wallet_id=wallet["id"],
            amount=str(request.topup_amount),
        )
        return {
            "finance_reference": finance_ref,
            "wallet_uuid": str(wallet["wallet_uuid"]),
            "available_balance": str(
                Decimal(str(wallet["available_balance"])) + request.topup_amount
            ),
            "ledger_uuid": str(ledger["ledger_uuid"]),
        }


def process_wallet_allocate(request: WalletAllocateRequest, finance_ref: str) -> dict:
    """Allocate/transfer funds by debiting allocator wallet and crediting recipient wallet.

    Supports same-currency and cross-currency transfers:
    - Same currency: debit/credit `allocation_amount` in `wallet_currency_code`.
    - Cross currency: debit `allocator_amount` in `allocator_currency_code`,
      credit `recipient_amount` in `recipient_currency_code`.
    """
    allocator_currency = (request.allocator_currency_code or request.wallet_currency_code or "USD").upper()
    recipient_currency = (request.recipient_currency_code or request.wallet_currency_code or "USD").upper()
    debit_amount = request.allocator_amount if request.allocator_amount is not None else request.allocation_amount
    credit_amount = request.recipient_amount if request.recipient_amount is not None else request.allocation_amount

    with db_transaction() as conn:
        allocator_wallet = get_or_create_wallet(
            conn,
            request.allocator_wallet_owner_type,
            request.allocator_wallet_owner_id,
            allocator_currency,
            request.supplier_api_id,
        )
        recipient_wallet = get_or_create_wallet(
            conn,
            request.recipient_wallet_owner_type,
            request.recipient_wallet_owner_id,
            recipient_currency,
            request.supplier_api_id,
        )

        if allocator_currency == recipient_currency:
            fx_note = ""
        else:
            rate_txt = f" @ {request.exchange_rate}" if request.exchange_rate is not None else ""
            fx_note = (
                f" ({debit_amount} {allocator_currency} → {credit_amount} {recipient_currency}{rate_txt})"
            )

        debit_desc = request.debit_ledger_description or (
            f"Wallet allocation debit to {request.recipient_wallet_owner_type}:{request.recipient_wallet_owner_id}{fx_note}"
        )
        credit_desc = request.credit_ledger_description or (
            f"Wallet allocation credit from {request.allocator_wallet_owner_type}:{request.allocator_wallet_owner_id}{fx_note}"
        )
        transfer_meta = dict(request.metadata or {})
        transfer_meta.update(
            {
                "fx": {
                    "allocator_currency": allocator_currency,
                    "recipient_currency": recipient_currency,
                    "allocator_amount": str(debit_amount),
                    "recipient_amount": str(credit_amount),
                    "exchange_rate": str(request.exchange_rate) if request.exchange_rate is not None else None,
                }
            }
        )

        debit_ledger = debit_wallet(
            conn,
            allocator_wallet,
            debit_amount,
            finance_ref,
            "allocation_debit",
            debit_desc,
            metadata={**transfer_meta, "side": "debit"},
        )
        credit_ledger = credit_wallet(
            conn,
            recipient_wallet,
            credit_amount,
            finance_ref,
            "allocation_credit",
            credit_desc,
            metadata={**transfer_meta, "side": "credit"},
        )

        log_event(
            "wallet_allocation_completed",
            finance_reference=finance_ref,
            allocator_wallet_id=allocator_wallet["id"],
            recipient_wallet_id=recipient_wallet["id"],
            allocator_currency=allocator_currency,
            recipient_currency=recipient_currency,
            debit_amount=str(debit_amount),
            credit_amount=str(credit_amount),
        )

        return {
            "finance_reference": finance_ref,
            "allocator_wallet_uuid": str(allocator_wallet["wallet_uuid"]),
            "recipient_wallet_uuid": str(recipient_wallet["wallet_uuid"]),
            "allocator_currency_code": allocator_currency,
            "recipient_currency_code": recipient_currency,
            "allocator_amount": str(debit_amount),
            "recipient_amount": str(credit_amount),
            "exchange_rate": str(request.exchange_rate) if request.exchange_rate is not None else None,
            "allocator_available_balance": str(Decimal(str(allocator_wallet["available_balance"])) - debit_amount),
            "recipient_available_balance": str(Decimal(str(recipient_wallet["available_balance"])) + credit_amount),
            "debit_ledger_uuid": str(debit_ledger["ledger_uuid"]),
            "credit_ledger_uuid": str(credit_ledger["ledger_uuid"]),
        }
