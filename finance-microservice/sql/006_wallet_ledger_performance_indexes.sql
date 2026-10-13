-- Performance indexes for wallet_ledger at scale (millions of rows).
-- Per-wallet listing: wallet_id + created_at + id supports filtered ORDER BY without a sort step.
CREATE INDEX IF NOT EXISTS idx_wallet_ledger_wallet_created_id
    ON wallet_ledger (wallet_id, created_at DESC, id DESC);

-- Cross-wallet / reporting scans by date range.
CREATE INDEX IF NOT EXISTS idx_wallet_ledger_created_at
    ON wallet_ledger (created_at DESC);

COMMENT ON INDEX idx_wallet_ledger_wallet_created_id IS
    'Covers paginated wallet ledger listings: WHERE wallet_id = ? [AND created_at range] ORDER BY created_at DESC, id DESC';
