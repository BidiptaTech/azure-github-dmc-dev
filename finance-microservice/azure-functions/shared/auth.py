"""Authentication, validation, and idempotency helpers."""
import hashlib
import hmac
import json
from datetime import datetime, timezone
from typing import Any, Optional

import azure.functions as func

from shared.config import get_settings
from shared.db import execute, fetch_one, get_connection
from shared.logger import log_event, log_warning


class AuthError(Exception):
    def __init__(self, message: str, status_code: int = 401):
        super().__init__(message)
        self.status_code = status_code


class ValidationError(Exception):
    def __init__(self, message: str, status_code: int = 400):
        super().__init__(message)
        self.status_code = status_code


def verify_bearer_token(req: func.HttpRequest) -> None:
    """Validate Bearer token from Laravel or internal services."""
    settings = get_settings()
    expected = settings["bearer_token"]
    if not expected:
        log_warning("auth_skipped", reason="FINANCE_API_BEARER_TOKEN not configured")
        return

    auth_header = req.headers.get("Authorization", "")
    if not auth_header.startswith("Bearer "):
        raise AuthError("Missing or invalid Authorization header")

    token = auth_header[7:].strip()
    if not hmac.compare_digest(token, expected):
        raise AuthError("Invalid bearer token")


def verify_webhook_signature(raw_body: bytes, signature: str) -> bool:
    """HMAC-SHA256 verification for payment gateway webhooks."""
    secret = get_settings()["webhook_secret"]
    if not secret:
        log_warning("webhook_signature_skipped", reason="FINANCE_WEBHOOK_SECRET not set")
        return True

    expected = hmac.new(
        secret.encode("utf-8"),
        raw_body,
        hashlib.sha256,
    ).hexdigest()

    provided = signature.replace("sha256=", "").strip()
    return hmac.compare_digest(expected, provided)


def hash_request_body(body: dict) -> str:
    canonical = json.dumps(body, sort_keys=True, separators=(",", ":"), default=str)
    return hashlib.sha256(canonical.encode("utf-8")).hexdigest()


def check_idempotency(
    idempotency_key: str,
    operation: str,
    request_hash: str,
) -> Optional[dict]:
    """Return cached response if same key+operation+hash exists."""
    with get_connection() as conn:
        row = fetch_one(
            conn,
            """
            SELECT request_hash, response_json AS response_payload, expires_at
            FROM idempotency_store
            WHERE idempotency_key = %s AND operation_name = %s
            """,
            (idempotency_key, operation),
        )
        if not row:
            return None

        if row["expires_at"] < datetime.now(timezone.utc):
            return None

        if row["request_hash"] != request_hash:
            raise ValidationError(
                "Idempotency key reused with different request payload",
                status_code=409,
            )

        log_event("idempotency_cache_hit", idempotency_key=idempotency_key, operation=operation)
        return row["response_payload"]


def store_idempotency(
    idempotency_key: str,
    operation: str,
    request_hash: str,
    response: dict,
) -> None:
    ttl_hours = get_settings()["idempotency_ttl_hours"]
    with get_connection() as conn:
        execute(
            conn,
            """
            INSERT INTO idempotency_store
                (idempotency_key, operation_name, request_hash, response_json, expires_at)
            VALUES (%s, %s, %s, %s::jsonb, NOW() + (%s || ' hours')::INTERVAL)
            ON CONFLICT (idempotency_key, operation_name) DO NOTHING
            """,
            (idempotency_key, operation, request_hash, json.dumps(response), str(ttl_hours)),
        )


def generate_finance_reference(conn, prefix: str = "FIN") -> str:
    """Generate monotonic finance reference: FIN-YYYYMMDD-SEQ."""
    row = fetch_one(conn, "SELECT nextval('finance_reference_seq') AS seq")
    seq = row["seq"]
    date_part = datetime.now(timezone.utc).strftime("%Y%m%d")
    return f"{prefix}-{date_part}-{seq}"
