-- =============================================================================
-- Travclicks Finance ù compatibility patch for earlier schema variants
-- Preserves existing data while adding columns expected by the current services.
-- =============================================================================

DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM pg_type WHERE typname = 'payment_status_enum') THEN
        IF NOT EXISTS (
            SELECT 1 FROM pg_enum e JOIN pg_type t ON t.oid = e.enumtypid
            WHERE t.typname = 'payment_status_enum' AND e.enumlabel = 'partial'
        ) THEN
            ALTER TYPE payment_status_enum ADD VALUE 'partial';
        END IF;
    END IF;

    IF EXISTS (SELECT 1 FROM pg_type WHERE typname = 'finance_status_enum') THEN
        IF NOT EXISTS (
            SELECT 1 FROM pg_enum e JOIN pg_type t ON t.oid = e.enumtypid
            WHERE t.typname = 'finance_status_enum' AND e.enumlabel = 'partially_settled'
        ) THEN
            ALTER TYPE finance_status_enum ADD VALUE 'partially_settled';
        END IF;
    END IF;
END $$;

-- Wallet: normalize currency and supplier columns.
ALTER TABLE wallet
    ADD COLUMN IF NOT EXISTS wallet_owner_type VARCHAR(32),
    ADD COLUMN IF NOT EXISTS wallet_status VARCHAR(20),
    ADD COLUMN IF NOT EXISTS wallet_currency_code CHAR(3);

UPDATE wallet
SET wallet_currency_code = COALESCE(wallet_currency_code, currency_code, 'USD')
WHERE wallet_currency_code IS NULL;

ALTER TABLE wallet
    ALTER COLUMN wallet_currency_code SET DEFAULT 'USD',
    ALTER COLUMN wallet_currency_code SET NOT NULL;

DROP INDEX IF EXISTS uq_wallet_owner_currency;
DROP INDEX IF EXISTS idx_wallet_supplier_api;
DROP INDEX IF EXISTS idx_wallet_supplier;

ALTER TABLE wallet
    ALTER COLUMN supplier_api_id TYPE VARCHAR(64) USING supplier_api_id::VARCHAR;

-- Wallet ledger: add canonical debit/credit columns used by the new service.
ALTER TABLE wallet_ledger
    ADD COLUMN IF NOT EXISTS ledger_entry_type VARCHAR(32),
    ADD COLUMN IF NOT EXISTS debit_amount NUMERIC(18,4) NOT NULL DEFAULT 0.0000,
    ADD COLUMN IF NOT EXISTS credit_amount NUMERIC(18,4) NOT NULL DEFAULT 0.0000,
    ADD COLUMN IF NOT EXISTS ledger_description TEXT,
    ADD COLUMN IF NOT EXISTS ledger_metadata JSONB DEFAULT '{}'::jsonb,
    ADD COLUMN IF NOT EXISTS tour_id BIGINT;

UPDATE wallet_ledger
SET ledger_entry_type = COALESCE(ledger_entry_type, entry_type),
    debit_amount = CASE WHEN COALESCE(entry_type, '') IN ('booking_debit', 'refund_debit', 'debit') THEN ABS(COALESCE(amount, 0)) ELSE debit_amount END,
    credit_amount = CASE WHEN COALESCE(entry_type, '') NOT IN ('booking_debit', 'refund_debit', 'debit') THEN ABS(COALESCE(amount, 0)) ELSE credit_amount END,
    ledger_description = COALESCE(ledger_description, description, 'Wallet ledger entry')
WHERE ledger_entry_type IS NULL OR ledger_description IS NULL;

ALTER TABLE wallet_ledger
    ALTER COLUMN ledger_entry_type SET DEFAULT 'adjustment',
    ALTER COLUMN ledger_entry_type SET NOT NULL,
    ALTER COLUMN ledger_description SET DEFAULT 'Wallet ledger entry',
    ALTER COLUMN ledger_description SET NOT NULL;

-- Payment transactions: add columns referenced by callback/refund logic.
ALTER TABLE payment_transactions
    ALTER COLUMN supplier_api_id TYPE VARCHAR(64) USING supplier_api_id::VARCHAR,
    ADD COLUMN IF NOT EXISTS payment_mode VARCHAR(32),
    ADD COLUMN IF NOT EXISTS payment_gateway_response JSONB,
    ADD COLUMN IF NOT EXISTS last_error_message TEXT;

UPDATE payment_transactions
SET payment_mode = CASE
    WHEN payment_mode IS NOT NULL AND payment_mode <> '' THEN payment_mode
    WHEN wallet_id IS NOT NULL THEN 'wallet'
    WHEN payment_gateway_name IS NOT NULL OR gateway_transaction_reference IS NOT NULL THEN 'online_gateway'
    ELSE 'offline_office'
END
WHERE payment_mode IS NULL OR payment_mode = '';

ALTER TABLE payment_transactions
    ALTER COLUMN payment_mode SET DEFAULT 'wallet';

UPDATE payment_transactions SET payment_mode = 'wallet' WHERE payment_mode IS NULL;

-- Tours: supplier_api_id must be VARCHAR for finance microservice snapshots.
ALTER TABLE tours
    ALTER COLUMN supplier_api_id TYPE VARCHAR(64) USING supplier_api_id::VARCHAR;

-- Settlements: add canonical columns used by current Python code.
ALTER TABLE settlements
    ALTER COLUMN supplier_api_id TYPE VARCHAR(64) USING supplier_api_id::VARCHAR,
    ADD COLUMN IF NOT EXISTS settlement_type VARCHAR(32),
    ADD COLUMN IF NOT EXISTS settlement_status VARCHAR(32) NOT NULL DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS selling_price NUMERIC(18,4) NOT NULL DEFAULT 0.0000,
    ADD COLUMN IF NOT EXISTS settlement_metadata JSONB DEFAULT '{}'::jsonb,
    ADD COLUMN IF NOT EXISTS idempotency_key VARCHAR(128),
    ADD COLUMN IF NOT EXISTS retry_count INTEGER NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS last_error_message TEXT;

UPDATE settlements
SET settlement_type = COALESCE(NULLIF(settlement_type, ''), 'wallet_booking'),
    settlement_status = COALESCE(NULLIF(settlement_status, ''), CASE WHEN finance_status = 'settled' THEN 'completed' ELSE 'pending' END),
    selling_price = COALESCE(NULLIF(selling_price, 0), agent_selling_price, 0),
    settlement_metadata = COALESCE(settlement_metadata, metadata_json, '{}'::jsonb);

CREATE UNIQUE INDEX IF NOT EXISTS uq_settlement_idempotency
    ON settlements (idempotency_key)
    WHERE idempotency_key IS NOT NULL;

CREATE UNIQUE INDEX IF NOT EXISTS uq_wallet_owner_currency_supplier
    ON wallet (wallet_owner_type, wallet_owner_id, wallet_currency_code, COALESCE(supplier_api_id, ''))
    WHERE deleted_at IS NULL;

CREATE INDEX IF NOT EXISTS idx_wallet_supplier
    ON wallet (supplier_api_id)
    WHERE supplier_api_id IS NOT NULL AND deleted_at IS NULL;
