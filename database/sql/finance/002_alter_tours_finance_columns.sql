-- =============================================================================
-- Travclicks Finance ------ tours table finance columns
-- Reason: Denormalized finance snapshot on booking row for fast reads by
--         booking service without joining finance schema on every list query.
--         Source of truth remains public finance tables (wallet, payment_transactions, etc.).
-- =============================================================================

-- finance_reference: links booking to finance microservice operations
-- Example: FIN-20260708-100001
ALTER TABLE tours ADD COLUMN IF NOT EXISTS finance_reference VARCHAR(64) NULL;
COMMENT ON COLUMN tours.finance_reference IS
    'Primary finance operation reference from finance microservice.';

-- payment_transaction_id: FK to payment_transactions (logical)
-- Example: 12345
ALTER TABLE tours ADD COLUMN IF NOT EXISTS payment_transaction_id BIGINT NULL;
COMMENT ON COLUMN tours.payment_transaction_id IS
    'ID of the payment record in payment_transactions.';

-- settlement_id: FK to settlements
-- Example: 6789
ALTER TABLE tours ADD COLUMN IF NOT EXISTS settlement_id BIGINT NULL;
COMMENT ON COLUMN tours.settlement_id IS
    'ID of settlement record after supplier/markup distribution.';

-- payment_mode: how the booking was paid
-- Example: wallet | online_gateway | offline_office
ALTER TABLE tours ADD COLUMN IF NOT EXISTS payment_mode VARCHAR(32) NULL;
COMMENT ON COLUMN tours.payment_mode IS
    'Payment channel used for this booking.';

-- payment_status: current payment lifecycle state
-- Example: captured
ALTER TABLE tours ADD COLUMN IF NOT EXISTS payment_status VARCHAR(32) NULL DEFAULT 'pending';
COMMENT ON COLUMN tours.payment_status IS
    'Payment lifecycle: pending, captured, failed, refunded, etc.';

-- gateway_transaction_reference: PSP transaction ID
-- Example: pay_abc123xyz
ALTER TABLE tours ADD COLUMN IF NOT EXISTS gateway_transaction_reference VARCHAR(128) NULL;
COMMENT ON COLUMN tours.gateway_transaction_reference IS
    'Payment gateway transaction reference for online payments.';

-- gateway_response_json: full gateway create-payment response
ALTER TABLE tours ADD COLUMN IF NOT EXISTS gateway_response_json JSONB NULL;
COMMENT ON COLUMN tours.gateway_response_json IS
    'Complete JSON response from payment gateway create-payment API.';

-- supplier_cost: net supplier API price
-- Example: 100.0000
ALTER TABLE tours ADD COLUMN IF NOT EXISTS supplier_cost NUMERIC(18,4) NULL;
COMMENT ON COLUMN tours.supplier_cost IS
    'Net cost paid to supplier API. Example: 100 USD for HotelBeds.';

-- travclicks_markup: Travclicks profit component
-- Example: 50.0000
ALTER TABLE tours ADD COLUMN IF NOT EXISTS travclicks_markup NUMERIC(18,4) NULL;
COMMENT ON COLUMN tours.travclicks_markup IS
    'Travclicks markup amount added on supplier cost.';

-- dmc_commission: DMC profit component
-- Example: 70.0000
ALTER TABLE tours ADD COLUMN IF NOT EXISTS dmc_commission NUMERIC(18,4) NULL;
COMMENT ON COLUMN tours.dmc_commission IS
    'DMC commission amount on Travclicks price.';

-- selling_price: agent-facing final price
-- Example: 220.0000
ALTER TABLE tours ADD COLUMN IF NOT EXISTS selling_price NUMERIC(18,4) NULL;
COMMENT ON COLUMN tours.selling_price IS
    'Final selling price shown to agent. selling_price = supplier_cost + markup + commission.';

-- supplier_api_id: which supplier API was used
-- Example: hotelbeds
ALTER TABLE tours ADD COLUMN IF NOT EXISTS supplier_api_id VARCHAR(64) NULL;
COMMENT ON COLUMN tours.supplier_api_id IS
    'Supplier API identifier: hotelbeds, webbeds, tbo, ratehawk, etc.';

-- supplier_booking_reference: supplier confirmation number
-- Example: HB-2026-998877
ALTER TABLE tours ADD COLUMN IF NOT EXISTS supplier_booking_reference VARCHAR(128) NULL;
COMMENT ON COLUMN tours.supplier_booking_reference IS
    'Booking reference returned by supplier API.';

-- finance_status: overall finance settlement state on booking
-- Example: settled
ALTER TABLE tours ADD COLUMN IF NOT EXISTS finance_status VARCHAR(32) NULL DEFAULT 'pending';
COMMENT ON COLUMN tours.finance_status IS
    'Finance settlement status: pending, settled, failed, refunded.';

-- payment_received_amount: actual amount received from customer/gateway
ALTER TABLE tours ADD COLUMN IF NOT EXISTS payment_received_amount NUMERIC(18,4) NULL;
COMMENT ON COLUMN tours.payment_received_amount IS
    'Actual payment amount received (may differ for partial payments).';

-- payment_received_currency: ISO 4217 currency
-- Example: USD
ALTER TABLE tours ADD COLUMN IF NOT EXISTS payment_received_currency CHAR(3) NULL;
COMMENT ON COLUMN tours.payment_received_currency IS
    'Currency of payment received.';

-- payment_gateway_name: PSP name
-- Example: razorpay
ALTER TABLE tours ADD COLUMN IF NOT EXISTS payment_gateway_name VARCHAR(64) NULL;
COMMENT ON COLUMN tours.payment_gateway_name IS
    'Payment gateway provider name.';

-- payment_gateway_response: full callback/webhook response
ALTER TABLE tours ADD COLUMN IF NOT EXISTS payment_gateway_response JSONB NULL;
COMMENT ON COLUMN tours.payment_gateway_response IS
    'Complete payment gateway callback/webhook JSON payload.';

-- payment_received_datetime: when payment was confirmed
ALTER TABLE tours ADD COLUMN IF NOT EXISTS payment_received_datetime TIMESTAMPTZ NULL;
COMMENT ON COLUMN tours.payment_received_datetime IS
    'Timestamp when payment was confirmed by gateway or finance team.';

-- master_dmc_id: hierarchy support for commission routing
ALTER TABLE tours ADD COLUMN IF NOT EXISTS master_dmc_id BIGINT NULL;
COMMENT ON COLUMN tours.master_dmc_id IS
    'Master DMC ID for commission hierarchy routing.';

-- Indexes for finance queries on booking list screens
CREATE INDEX IF NOT EXISTS idx_tours_finance_reference ON tours (finance_reference) WHERE finance_reference IS NOT NULL;
CREATE INDEX IF NOT EXISTS idx_tours_payment_status ON tours (payment_status) WHERE payment_status IS NOT NULL;
CREATE INDEX IF NOT EXISTS idx_tours_finance_status ON tours (finance_status) WHERE finance_status IS NOT NULL;
CREATE INDEX IF NOT EXISTS idx_tours_gateway_ref ON tours (gateway_transaction_reference) WHERE gateway_transaction_reference IS NOT NULL;
CREATE INDEX IF NOT EXISTS idx_tours_supplier_ref ON tours (supplier_booking_reference) WHERE supplier_booking_reference IS NOT NULL;

-- Check constraints (idempotent via DO block; NOT VALID for legacy rows)
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_tours_payment_mode') THEN
        ALTER TABLE tours ADD CONSTRAINT chk_tours_payment_mode CHECK (
            payment_mode IS NULL OR payment_mode IN ('wallet', 'online_gateway', 'offline_office')
        ) NOT VALID;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_tours_payment_status') THEN
        ALTER TABLE tours ADD CONSTRAINT chk_tours_payment_status CHECK (
            payment_status IS NULL OR payment_status IN ('pending', 'authorized', 'captured', 'failed', 'partial', 'refunded', 'cancelled')
        ) NOT VALID;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_tours_finance_status') THEN
        ALTER TABLE tours ADD CONSTRAINT chk_tours_finance_status CHECK (
            finance_status IS NULL OR finance_status IN ('pending', 'partially_settled', 'settled', 'failed', 'refunded')
        ) NOT VALID;
    END IF;
END $$;
