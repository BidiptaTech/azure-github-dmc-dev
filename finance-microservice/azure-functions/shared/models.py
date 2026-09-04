"""Pydantic request/response models for finance API."""
from datetime import datetime
from decimal import Decimal
from typing import Any, Optional
from uuid import UUID

from pydantic import BaseModel, Field, field_validator


class ApiResponse(BaseModel):
    success: bool
    message: str
    data: Optional[dict] = None
    error_code: Optional[str] = None


class WalletTopupRequest(BaseModel):
    idempotency_key: str = Field(..., min_length=8, max_length=128)
    wallet_owner_type: str = Field(..., pattern=r"^(travclicks|master_dmc|dmc|agent|supplier)$")
    wallet_owner_id: int = Field(..., gt=0)
    topup_amount: Decimal = Field(..., gt=0)
    wallet_currency_code: str = Field(default="USD", min_length=3, max_length=3)
    supplier_api_id: Optional[str] = None
    ledger_description: str = Field(default="Wallet top-up")
    metadata: Optional[dict] = None


class WalletAllocateRequest(BaseModel):
    """Wallet allocation: debit allocator wallet and credit recipient wallet."""

    idempotency_key: str = Field(..., min_length=8, max_length=128)

    allocator_wallet_owner_type: str = Field(
        ...,
        pattern=r"^(travclicks|master_dmc|dmc)$",
    )
    allocator_wallet_owner_id: int = Field(..., gt=0)

    recipient_wallet_owner_type: str = Field(
        ...,
        pattern=r"^(travclicks|master_dmc|dmc|agent|supplier)$",
    )
    recipient_wallet_owner_id: int = Field(..., gt=0)

    allocation_amount: Decimal = Field(..., gt=0)
    wallet_currency_code: str = Field(default="USD", min_length=3, max_length=3)
    # Optional cross-currency fields. When omitted, both sides use wallet_currency_code
    # and allocation_amount (same-currency transfer).
    allocator_currency_code: Optional[str] = Field(default=None, min_length=3, max_length=3)
    recipient_currency_code: Optional[str] = Field(default=None, min_length=3, max_length=3)
    allocator_amount: Optional[Decimal] = Field(default=None, gt=0)
    recipient_amount: Optional[Decimal] = Field(default=None, gt=0)
    exchange_rate: Optional[Decimal] = Field(default=None, gt=0)
    supplier_api_id: Optional[str] = None

    debit_ledger_description: Optional[str] = None
    credit_ledger_description: Optional[str] = None

    ledger_description: str = Field(default="Wallet allocation")
    metadata: Optional[dict] = None

    @field_validator("allocator_currency_code", "recipient_currency_code", mode="before")
    @classmethod
    def uppercase_optional_currency(cls, v):
        if v is None or v == "":
            return None
        return str(v).upper()

    @field_validator("wallet_currency_code", mode="before")
    @classmethod
    def uppercase_wallet_currency(cls, v):
        return str(v or "USD").upper()


class WalletBookingRequest(BaseModel):
    idempotency_key: str = Field(..., min_length=8, max_length=128)
    tour_id: int = Field(..., gt=0)
    wallet_owner_type: str = Field(..., pattern=r"^(dmc|agent|travclicks)$")
    wallet_owner_id: int = Field(..., gt=0)
    supplier_api_id: str
    supplier_booking_reference: Optional[str] = None
    supplier_cost: Decimal = Field(..., ge=0)
    travclicks_markup: Decimal = Field(..., ge=0)
    dmc_commission: Decimal = Field(..., ge=0)
    selling_price: Decimal = Field(..., gt=0)
    wallet_currency_code: str = Field(default="USD", min_length=3, max_length=3)
    dmc_id: Optional[int] = None
    agent_id: Optional[int] = None
    master_dmc_id: Optional[int] = None

    @field_validator("selling_price")
    @classmethod
    def validate_selling_price(cls, v, info):
        data = info.data
        expected = data.get("supplier_cost", 0) + data.get("travclicks_markup", 0) + data.get("dmc_commission", 0)
        if abs(v - expected) > Decimal("0.01"):
            raise ValueError(f"selling_price must equal supplier_cost + travclicks_markup + dmc_commission (got {v}, expected {expected})")
        return v


class OnlineSettlementRequest(BaseModel):
    idempotency_key: str = Field(..., min_length=8, max_length=128)
    tour_id: int = Field(..., gt=0)
    payment_transaction_id: int = Field(..., gt=0)
    gateway_transaction_reference: str
    payment_gateway_name: str
    supplier_api_id: str
    supplier_booking_reference: Optional[str] = None
    supplier_cost: Decimal = Field(..., ge=0)
    travclicks_markup: Decimal = Field(..., ge=0)
    dmc_commission: Decimal = Field(..., ge=0)
    selling_price: Decimal = Field(..., gt=0)
    payment_received_amount: Decimal = Field(..., gt=0)
    payment_received_currency: str = Field(default="USD", min_length=3, max_length=3)
    payment_received_datetime: Optional[datetime] = None
    gateway_response_json: Optional[dict] = None


class OfflineSettlementRequest(BaseModel):
    idempotency_key: str = Field(..., min_length=8, max_length=128)
    tour_id: int = Field(..., gt=0)
    finance_confirmed_by: str
    supplier_api_id: str
    supplier_booking_reference: Optional[str] = None
    supplier_cost: Decimal = Field(..., ge=0)
    travclicks_markup: Decimal = Field(..., ge=0)
    dmc_commission: Decimal = Field(..., ge=0)
    selling_price: Decimal = Field(..., gt=0)
    payment_received_amount: Decimal = Field(..., gt=0)
    payment_received_currency: str = Field(default="USD", min_length=3, max_length=3)
    payment_received_datetime: Optional[datetime] = None
    office_receipt_reference: Optional[str] = None


class RefundRequest(BaseModel):
    idempotency_key: str = Field(..., min_length=8, max_length=128)
    tour_id: int = Field(..., gt=0)
    payment_transaction_id: int = Field(..., gt=0)
    refund_type: str = Field(..., pattern=r"^(wallet|gateway|supplier|full)$")
    refund_amount: Decimal = Field(..., gt=0)
    refund_currency_code: str = Field(default="USD", min_length=3, max_length=3)
    refund_reason: str = Field(default="Customer cancellation")
    wallet_owner_type: Optional[str] = None
    wallet_owner_id: Optional[int] = None
    gateway_refund_reference: Optional[str] = None
    supplier_refund_reference: Optional[str] = None


class CommissionCalculationRequest(BaseModel):
    supplier_cost: Decimal = Field(..., gt=0)
    travclicks_markup_percent: Decimal = Field(..., ge=0)
    dmc_commission_percent: Decimal = Field(..., ge=0)
    agent_markup_percent: Decimal = Field(default=Decimal("0"), ge=0)
    currency_code: str = Field(default="USD", min_length=3, max_length=3)


class CommissionCalculationResponse(BaseModel):
    supplier_cost: Decimal
    travclicks_markup: Decimal
    travclicks_price: Decimal
    dmc_commission: Decimal
    selling_price: Decimal
    travclicks_profit: Decimal
    dmc_profit: Decimal
    currency_code: str
