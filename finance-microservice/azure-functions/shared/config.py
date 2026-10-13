"""Configuration loaded from environment / Azure App Settings."""
import os
from functools import lru_cache


@lru_cache(maxsize=1)
def get_settings() -> dict:
    return {
        "database_url": os.environ.get(
            "FINANCE_DATABASE_URL",
            os.environ.get(
                "DATABASE_URL",
                "postgresql://postgres:postgres@localhost:5432/dmc_dev",
            ),
        ),
        "bearer_token": os.environ.get("FINANCE_API_BEARER_TOKEN", ""),
        "webhook_secret": os.environ.get("FINANCE_WEBHOOK_SECRET", ""),
        "max_retry_count": int(os.environ.get("FINANCE_MAX_RETRY_COUNT", "3")),
        "idempotency_ttl_hours": int(os.environ.get("FINANCE_IDEMPOTENCY_TTL_HOURS", "24")),
        "log_level": os.environ.get("FINANCE_LOG_LEVEL", "INFO"),
        "service_name": "finance-microservice",
    }
