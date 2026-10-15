-- =============================================================================
-- Add wallet_owner_type to legacy public.wallet rows (pre-canonical schema)
-- =============================================================================

ALTER TABLE wallet
    ADD COLUMN IF NOT EXISTS wallet_owner_type VARCHAR(32),
    ADD COLUMN IF NOT EXISTS wallet_status VARCHAR(20);

-- Infer owner type from users.role_id when wallet_owner_id is a DMC user.
UPDATE wallet w
SET wallet_owner_type = CASE
        WHEN u.role_id::text IN ('10') THEN 'master_dmc'
        WHEN u.role_id::text IN ('11') THEN 'dmc'
        ELSE NULL
    END
FROM users u
WHERE u."userId" = w.wallet_owner_id
  AND w.wallet_owner_type IS NULL;

-- Infer agent wallets when wallet_owner_id matches agents.agent_id.
UPDATE wallet w
SET wallet_owner_type = 'agent'
FROM agents a
WHERE a.agent_id = w.wallet_owner_id
  AND w.wallet_owner_type IS NULL;

-- Platform wallet convention.
UPDATE wallet
SET wallet_owner_type = 'travclicks'
WHERE wallet_owner_type IS NULL
  AND wallet_owner_id = 1;

UPDATE wallet
SET wallet_owner_type = COALESCE(wallet_owner_type, 'supplier')
WHERE wallet_owner_type IS NULL;

UPDATE wallet
SET wallet_status = COALESCE(wallet_status, status, 'active')
WHERE wallet_status IS NULL;

ALTER TABLE wallet
    ALTER COLUMN wallet_owner_type SET NOT NULL,
    ALTER COLUMN wallet_status SET DEFAULT 'active',
    ALTER COLUMN wallet_status SET NOT NULL;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'chk_wallet_owner_type'
    ) THEN
        ALTER TABLE wallet ADD CONSTRAINT chk_wallet_owner_type CHECK (
            wallet_owner_type IN ('travclicks', 'master_dmc', 'dmc', 'agent', 'supplier')
        );
    END IF;
END $$;

CREATE INDEX IF NOT EXISTS idx_wallet_owner
    ON wallet (wallet_owner_type, wallet_owner_id)
    WHERE deleted_at IS NULL;

CREATE UNIQUE INDEX IF NOT EXISTS uq_wallet_owner_currency_supplier
    ON wallet (wallet_owner_type, wallet_owner_id, wallet_currency_code, COALESCE(supplier_api_id, ''))
    WHERE deleted_at IS NULL;
