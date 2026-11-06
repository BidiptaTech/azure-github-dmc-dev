-- Performance index for agent tour listings at scale.
CREATE INDEX IF NOT EXISTS idx_tours_agent_created_at
    ON tours (agent_id, created_at DESC, tour_id DESC);

COMMENT ON INDEX idx_tours_agent_created_at IS
    'Covers paginated agent tour listings: WHERE agent_id = ? [AND created_at range] ORDER BY created_at DESC, tour_id DESC';
