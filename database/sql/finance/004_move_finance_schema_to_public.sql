-- =============================================================================
-- Move finance schema objects to public schema (preserves existing data)
-- =============================================================================

-- Legacy Laravel user balance wallet (not finance microservice wallet).
-- Renamed to avoid constraint/table name clash when finance.wallet moves to public.
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = 'public' AND table_name = 'wallet'
    ) AND NOT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = 'public' AND table_name = 'wallet' AND column_name = 'wallet_owner_type'
    ) AND EXISTS (
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = 'finance' AND table_name = 'wallet'
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

-- Legacy Laravel wallet table (user balance) ------ only when finance schema not present.
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = 'public' AND table_name = 'wallet'
    ) AND NOT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = 'public' AND table_name = 'wallet' AND column_name = 'wallet_owner_type'
    ) AND NOT EXISTS (
        SELECT 1 FROM information_schema.schemata WHERE schema_name = 'finance'
    ) THEN
        ALTER TABLE public.wallet RENAME TO user_wallets;
    END IF;
END $$;

DO $$
DECLARE
    tbl TEXT;
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.schemata WHERE schema_name = 'finance') THEN
        RETURN;
    END IF;

    -- Move custom enum types first (if present in finance schema).
    IF EXISTS (SELECT 1 FROM pg_type t JOIN pg_namespace n ON n.oid = t.typnamespace
               WHERE n.nspname = 'finance' AND t.typname = 'payment_status_enum') THEN
        EXECUTE 'ALTER TYPE finance.payment_status_enum SET SCHEMA public';
    END IF;

    IF EXISTS (SELECT 1 FROM pg_type t JOIN pg_namespace n ON n.oid = t.typnamespace
               WHERE n.nspname = 'finance' AND t.typname = 'finance_status_enum') THEN
        EXECUTE 'ALTER TYPE finance.finance_status_enum SET SCHEMA public';
    END IF;

    -- Move sequence before tables that may reference it.
    IF EXISTS (SELECT 1 FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace
               WHERE n.nspname = 'finance' AND c.relkind = 'S' AND c.relname = 'finance_reference_seq') THEN
        EXECUTE 'ALTER SEQUENCE finance.finance_reference_seq SET SCHEMA public';
    END IF;

    -- Rename legacy idempotency_keys to idempotency_store if needed.
    IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'finance' AND table_name = 'idempotency_keys')
       AND NOT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'finance' AND table_name = 'idempotency_store') THEN
        ALTER TABLE finance.idempotency_keys RENAME TO idempotency_store;
        IF EXISTS (
            SELECT 1 FROM information_schema.columns
            WHERE table_schema = 'finance' AND table_name = 'idempotency_store' AND column_name = 'response_payload'
        ) THEN
            ALTER TABLE finance.idempotency_store RENAME COLUMN response_payload TO response_json;
        END IF;
    END IF;

    FOREACH tbl IN ARRAY ARRAY[
        'idempotency_store',
        'idempotency_keys',
        'wallet',
        'payment_transactions',
        'settlements',
        'wallet_ledger',
        'callback_processing_log',
        'gateway_callback_log'
    ] LOOP
        IF EXISTS (
            SELECT 1 FROM information_schema.tables
            WHERE table_schema = 'finance' AND table_name = tbl
        ) AND NOT EXISTS (
            SELECT 1 FROM information_schema.tables
            WHERE table_schema = 'public' AND table_name = tbl
        ) THEN
            EXECUTE format('ALTER TABLE finance.%I SET SCHEMA public', tbl);
        END IF;
    END LOOP;

    -- Move updated_at trigger function to public.
    IF EXISTS (
        SELECT 1 FROM pg_proc p JOIN pg_namespace n ON n.oid = p.pronamespace
        WHERE n.nspname = 'finance' AND p.proname = 'set_updated_at'
    ) THEN
        ALTER FUNCTION finance.set_updated_at() SET SCHEMA public;
        ALTER FUNCTION public.set_updated_at() RENAME TO set_finance_updated_at;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM pg_proc p JOIN pg_namespace n ON n.oid = p.pronamespace
        WHERE n.nspname = 'public' AND p.proname = 'set_finance_updated_at'
    ) THEN
        EXECUTE $fn$
            CREATE OR REPLACE FUNCTION set_finance_updated_at()
            RETURNS TRIGGER AS $body$
            BEGIN
                NEW.updated_at = NOW();
                RETURN NEW;
            END;
            $body$ LANGUAGE plpgsql
        $fn$;
    END IF;

    -- Rebind triggers to public function.
    IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'wallet') THEN
        DROP TRIGGER IF EXISTS trg_wallet_updated_at ON public.wallet;
        CREATE TRIGGER trg_wallet_updated_at
            BEFORE UPDATE ON public.wallet
            FOR EACH ROW EXECUTE FUNCTION set_finance_updated_at();
    END IF;

    IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'payment_transactions') THEN
        DROP TRIGGER IF EXISTS trg_payment_updated_at ON public.payment_transactions;
        CREATE TRIGGER trg_payment_updated_at
            BEFORE UPDATE ON public.payment_transactions
            FOR EACH ROW EXECUTE FUNCTION set_finance_updated_at();
    END IF;

    IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'settlements') THEN
        DROP TRIGGER IF EXISTS trg_settlement_updated_at ON public.settlements;
        CREATE TRIGGER trg_settlement_updated_at
            BEFORE UPDATE ON public.settlements
            FOR EACH ROW EXECUTE FUNCTION set_finance_updated_at();
    END IF;

    DROP SCHEMA IF EXISTS finance CASCADE;
END $$;
