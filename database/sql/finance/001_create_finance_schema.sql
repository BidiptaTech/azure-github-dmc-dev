-- =============================================================================
-- Travclicks Finance Microservice ------ Core Tables (public schema)
-- PostgreSQL 14+
-- =============================================================================

-- Rename legacy Laravel user-balance wallet table if present (avoids name clash).
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = 'public' AND table_name = 'wallet'
    ) AND NOT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = 'public' AND table_name = 'wallet' AND column_name = 'wallet_owner_type'
    ) THEN
        ALTER TABLE public.wallet RENAME TO user_wallets;
        IF EXISTS (
            SELECT 1 FROM pg_constraint
            WHERE conname = 'wallet_pkey' AND conrelid = 'public.user_wallets'::regclass
        ) THEN
            ALTER TABLE public.user_wallets RENAME CONSTRAINT wallet_pkey TO user_wallets_pkey;
        END IF;
        IF EXISTS (
            SELECT 1 FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace
            WHERE n.nspname = 'public' AND c.relkind = 'S' AND c.relname = 'wallet_id_seq'
        ) THEN
            ALTER SEQUENCE public.wallet_id_seq RENAME TO user_wallets_id_seq;
            ALTER TABLE public.user_wallets
                ALTER COLUMN id SET DEFAULT nextval('public.user_wallets_id_seq'::regclass);
        END IF;
    END IF;
END $$;

CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- -----------------------------------------------------------------------------
-- finance_reference_seq ------ monotonic reference generator
-- -----------------------------------------------------------------------------
CREATE SEQUENCE IF NOT EXISTS finance_reference_seq
    START WITH 100000
    INCREMENT BY 1
    NO MAXVALUE
    CACHE 50;

COMMENT ON SEQUENCE finance_reference_seq IS
    'Generates sequential finance reference numbers (FIN-*, SET-*, REF-*).';

-- -----------------------------------------------------------------------------
-- idempotency_store ------ prevents duplicate API side-effects
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS idempotency_store (
    id                      BIGSERIAL       PRIMARY KEY,
    idempotency_key         VARCHAR(128)    NOT NULL,
    operation_name          VARCHAR(64)     NOT NULL,
    request_hash            VARCHAR(64)     NOT NULL,
    response_json           JSONB           NOT NULL,
    expires_at              TIMESTAMPTZ     NOT NULL DEFAULT (NOW() + INTERVAL '24 hours'),
    created_at              TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    CONSTRAINT uq_idempotency_operation UNIQUE (idempotency_key, operation_name)
);

CREATE INDEX IF NOT EXISTS idx_idempotency_expires
    ON idempotency_store (expires_at);

COMMENT ON TABLE idempotency_store IS
    'Stores idempotent API responses for 24h to prevent duplicate wallet/settlement operations.';

-- -----------------------------------------------------------------------------
-- wallet ------ prepaid balances for Travclicks, DMC, Agent, Supplier
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS wallet (
    id                      BIGSERIAL       PRIMARY KEY,
    wallet_uuid             UUID            NOT NULL DEFAULT gen_random_uuid(),
    wallet_owner_type       VARCHAR(32)     NOT NULL,
    wallet_owner_id         BIGINT          NOT NULL,
    supplier_api_id         VARCHAR(64)     NULL,
    wallet_currency_code    CHAR(3)         NOT NULL DEFAULT 'USD',
    available_balance       NUMERIC(18,4)   NOT NULL DEFAULT 0.0000,
    reserved_balance        NUMERIC(18,4)   NOT NULL DEFAULT 0.0000,
    total_credited          NUMERIC(18,4)   NOT NULL DEFAULT 0.0000,
    total_debited           NUMERIC(18,4)   NOT NULL DEFAULT 0.0000,
    wallet_status           VARCHAR(20)     NOT NULL DEFAULT 'active',
    created_at              TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at              TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    deleted_at              TIMESTAMPTZ     NULL,
    CONSTRAINT uq_wallet_uuid UNIQUE (wallet_uuid),
    CONSTRAINT uq_wallet_owner UNIQUE (wallet_owner_type, wallet_owner_id, wallet_currency_code, supplier_api_id),
    CONSTRAINT chk_wallet_owner_type CHECK (
        wallet_owner_type IN ('travclicks', 'master_dmc', 'dmc', 'agent', 'supplier')
    ),
    CONSTRAINT chk_wallet_status CHECK (
        wallet_status IN ('active', 'suspended', 'closed')
    ),
    CONSTRAINT chk_wallet_available_balance CHECK (available_balance >= 0),
    CONSTRAINT chk_wallet_reserved_balance CHECK (reserved_balance >= 0)
);

CREATE INDEX IF NOT EXISTS idx_wallet_owner
    ON wallet (wallet_owner_type, wallet_owner_id)
    WHERE deleted_at IS NULL;

CREATE INDEX IF NOT EXISTS idx_wallet_supplier
    ON wallet (supplier_api_id)
    WHERE supplier_api_id IS NOT NULL AND deleted_at IS NULL;

COMMENT ON TABLE wallet IS
    'Prepaid wallet balances per hierarchy entity. Example: DMC wallet for agent bookings.';

-- -----------------------------------------------------------------------------
-- wallet_ledger ------ immutable audit trail for every wallet movement
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS wallet_ledger (
    id                      BIGSERIAL       PRIMARY KEY,
    ledger_uuid             UUID            NOT NULL DEFAULT gen_random_uuid(),
    wallet_id               BIGINT          NOT NULL REFERENCES wallet(id),
    finance_reference       VARCHAR(64)     NOT NULL,
    ledger_entry_type       VARCHAR(32)     NOT NULL,
    debit_amount            NUMERIC(18,4)   NOT NULL DEFAULT 0.0000,
    credit_amount           NUMERIC(18,4)   NOT NULL DEFAULT 0.0000,
    balance_after           NUMERIC(18,4)   NOT NULL,
    tour_id                 BIGINT          NULL,
    payment_transaction_id  BIGINT          NULL,
    settlement_id           BIGINT          NULL,
    ledger_description      TEXT            NOT NULL,
    ledger_metadata         JSONB           NULL DEFAULT '{}',
    created_at              TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    CONSTRAINT uq_ledger_uuid UNIQUE (ledger_uuid),
    CONSTRAINT chk_ledger_entry_type CHECK (
        ledger_entry_type IN ('topup', 'booking_debit', 'booking_credit', 'refund_credit', 'refund_debit', 'reserve', 'release', 'adjustment')
    ),
    CONSTRAINT chk_ledger_amount CHECK (
        (debit_amount > 0 AND credit_amount = 0) OR (credit_amount > 0 AND debit_amount = 0)
    )
);

CREATE INDEX IF NOT EXISTS idx_wallet_ledger_wallet
    ON wallet_ledger (wallet_id, created_at DESC);

CREATE INDEX IF NOT EXISTS idx_wallet_ledger_finance_ref
    ON wallet_ledger (finance_reference);

COMMENT ON TABLE wallet_ledger IS
    'Immutable double-entry style ledger for wallet movements. Never updated, only inserted.';

-- -----------------------------------------------------------------------------
-- payment_transactions ------ all payment records (wallet, gateway, offline)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payment_transactions (
    id                          BIGSERIAL       PRIMARY KEY,
    payment_transaction_uuid    UUID            NOT NULL DEFAULT gen_random_uuid(),
    finance_reference             VARCHAR(64)     NOT NULL,
    tour_id                       BIGINT          NOT NULL,
    payment_mode                  VARCHAR(32)     NOT NULL,
    payment_status                VARCHAR(32)     NOT NULL DEFAULT 'pending',
    payment_gateway_name          VARCHAR(64)     NULL,
    gateway_transaction_reference VARCHAR(128)    NULL,
    gateway_response_json         JSONB           NULL,
    supplier_api_id               VARCHAR(64)     NULL,
    supplier_booking_reference    VARCHAR(128)    NULL,
    supplier_cost                 NUMERIC(18,4)   NOT NULL DEFAULT 0.0000,
    travclicks_markup             NUMERIC(18,4)   NOT NULL DEFAULT 0.0000,
    dmc_commission                NUMERIC(18,4)   NOT NULL DEFAULT 0.0000,
    selling_price                 NUMERIC(18,4)   NOT NULL DEFAULT 0.0000,
    payment_received_amount       NUMERIC(18,4)   NULL,
    payment_received_currency     CHAR(3)         NULL,
    payment_received_datetime       TIMESTAMPTZ     NULL,
    payment_gateway_response      JSONB           NULL,
    wallet_id                     BIGINT          NULL REFERENCES wallet(id),
    idempotency_key               VARCHAR(128)    NULL,
    retry_count                   INTEGER         NOT NULL DEFAULT 0,
    last_error_message            TEXT            NULL,
    created_at                    TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at                    TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    deleted_at                    TIMESTAMPTZ     NULL,
    CONSTRAINT uq_payment_transaction_uuid UNIQUE (payment_transaction_uuid),
    CONSTRAINT uq_payment_finance_reference UNIQUE (finance_reference),
    CONSTRAINT uq_payment_idempotency UNIQUE (idempotency_key),
    CONSTRAINT chk_payment_mode CHECK (
        payment_mode IN ('wallet', 'online_gateway', 'offline_office')
    ),
    CONSTRAINT chk_payment_status CHECK (
        payment_status IN ('pending', 'authorized', 'captured', 'failed', 'partial', 'refunded', 'cancelled')
    ),
    CONSTRAINT chk_payment_amounts CHECK (
        supplier_cost >= 0 AND travclicks_markup >= 0 AND dmc_commission >= 0 AND selling_price >= 0
    )
);

CREATE INDEX IF NOT EXISTS idx_payment_tour
    ON payment_transactions (tour_id)
    WHERE deleted_at IS NULL;

CREATE INDEX IF NOT EXISTS idx_payment_gateway_ref
    ON payment_transactions (gateway_transaction_reference)
    WHERE gateway_transaction_reference IS NOT NULL;

CREATE INDEX IF NOT EXISTS idx_payment_status
    ON payment_transactions (payment_status, created_at DESC);

COMMENT ON TABLE payment_transactions IS
    'Central payment record linking booking, gateway, wallet, and supplier references.';

-- -----------------------------------------------------------------------------
-- settlements ------ supplier deduction, markup distribution, ledger creation
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settlements (
    id                          BIGSERIAL       PRIMARY KEY,
    settlement_uuid             UUID            NOT NULL DEFAULT gen_random_uuid(),
    settlement_reference        VARCHAR(64)     NOT NULL,
    tour_id                     BIGINT          NOT NULL,
    payment_transaction_id      BIGINT          NOT NULL REFERENCES payment_transactions(id),
    settlement_type               VARCHAR(32)     NOT NULL,
    settlement_status             VARCHAR(32)     NOT NULL DEFAULT 'pending',
    supplier_api_id               VARCHAR(64)     NOT NULL,
    supplier_booking_reference    VARCHAR(128)    NULL,
    supplier_cost                 NUMERIC(18,4)   NOT NULL DEFAULT 0.0000,
    travclicks_markup             NUMERIC(18,4)   NOT NULL DEFAULT 0.0000,
    travclicks_profit             NUMERIC(18,4)   NOT NULL DEFAULT 0.0000,
    dmc_commission                NUMERIC(18,4)   NOT NULL DEFAULT 0.0000,
    dmc_profit                    NUMERIC(18,4)   NOT NULL DEFAULT 0.0000,
    selling_price                 NUMERIC(18,4)   NOT NULL DEFAULT 0.0000,
    wallet_debit_amount           NUMERIC(18,4)   NULL DEFAULT 0.0000,
    wallet_credit_amount          NUMERIC(18,4)   NULL DEFAULT 0.0000,
    finance_status                VARCHAR(32)     NOT NULL DEFAULT 'pending',
    settled_at                    TIMESTAMPTZ     NULL,
    settlement_metadata           JSONB           NULL DEFAULT '{}',
    idempotency_key               VARCHAR(128)    NULL,
    retry_count                   INTEGER         NOT NULL DEFAULT 0,
    last_error_message            TEXT            NULL,
    created_at                    TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at                    TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    deleted_at                    TIMESTAMPTZ     NULL,
    CONSTRAINT uq_settlement_uuid UNIQUE (settlement_uuid),
    CONSTRAINT uq_settlement_reference UNIQUE (settlement_reference),
    CONSTRAINT uq_settlement_idempotency UNIQUE (idempotency_key),
    CONSTRAINT chk_settlement_type CHECK (
        settlement_type IN ('wallet_booking', 'online_payment', 'offline_payment', 'refund')
    ),
    CONSTRAINT chk_settlement_status CHECK (
        settlement_status IN ('pending', 'processing', 'completed', 'failed', 'reversed')
    ),
    CONSTRAINT chk_finance_status CHECK (
        finance_status IN ('pending', 'partially_settled', 'settled', 'failed', 'refunded')
    )
);

CREATE INDEX IF NOT EXISTS idx_settlement_tour
    ON settlements (tour_id)
    WHERE deleted_at IS NULL;

CREATE INDEX IF NOT EXISTS idx_settlement_payment
    ON settlements (payment_transaction_id);

CREATE INDEX IF NOT EXISTS idx_settlement_status
    ON settlements (settlement_status, created_at DESC);

COMMENT ON TABLE settlements IS
    'Settlement records distributing supplier cost, Travclicks markup, and DMC commission.';

ALTER TABLE wallet_ledger
    ADD CONSTRAINT fk_ledger_payment
    FOREIGN KEY (payment_transaction_id) REFERENCES payment_transactions(id);

ALTER TABLE wallet_ledger
    ADD CONSTRAINT fk_ledger_settlement
    FOREIGN KEY (settlement_id) REFERENCES settlements(id);

-- -----------------------------------------------------------------------------
-- callback_processing_log ------ duplicate callback prevention
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS callback_processing_log (
    id                          BIGSERIAL       PRIMARY KEY,
    gateway_name                VARCHAR(64)     NOT NULL,
    gateway_transaction_reference VARCHAR(128)    NOT NULL,
    callback_payload_hash       VARCHAR(64)     NOT NULL,
    processing_status           VARCHAR(32)     NOT NULL DEFAULT 'processed',
    payment_transaction_id      BIGINT          NULL REFERENCES payment_transactions(id),
    processed_at                TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    CONSTRAINT uq_callback_gateway_ref UNIQUE (gateway_name, gateway_transaction_reference)
);

COMMENT ON TABLE callback_processing_log IS
    'Ensures payment gateway callbacks are processed exactly once (idempotency).';

-- -----------------------------------------------------------------------------
-- updated_at trigger
-- -----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION set_finance_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_wallet_updated_at ON wallet;
CREATE TRIGGER trg_wallet_updated_at
    BEFORE UPDATE ON wallet
    FOR EACH ROW EXECUTE FUNCTION set_finance_updated_at();

DROP TRIGGER IF EXISTS trg_payment_updated_at ON payment_transactions;
CREATE TRIGGER trg_payment_updated_at
    BEFORE UPDATE ON payment_transactions
    FOR EACH ROW EXECUTE FUNCTION set_finance_updated_at();

DROP TRIGGER IF EXISTS trg_settlement_updated_at ON settlements;
CREATE TRIGGER trg_settlement_updated_at
    BEFORE UPDATE ON settlements
    FOR EACH ROW EXECUTE FUNCTION set_finance_updated_at();
