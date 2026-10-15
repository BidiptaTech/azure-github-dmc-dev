"""Structured logging for Azure Functions."""
import json
import logging
import traceback
from datetime import datetime, timezone

from shared.config import get_settings

_settings = get_settings()
_logger = logging.getLogger(_settings["service_name"])
_logger.setLevel(getattr(logging, _settings["log_level"], logging.INFO))

if not _logger.handlers:
    handler = logging.StreamHandler()
    handler.setFormatter(logging.Formatter("%(message)s"))
    _logger.addHandler(handler)


def _emit(level: str, event: str, **kwargs) -> None:
    payload = {
        "timestamp": datetime.now(timezone.utc).isoformat(),
        "service": _settings["service_name"],
        "event": event,
        "level": level,
        **kwargs,
    }
    getattr(_logger, level.lower(), _logger.info)(json.dumps(payload, default=str))


def log_event(event: str, **kwargs) -> None:
    _emit("INFO", event, **kwargs)


def log_warning(event: str, **kwargs) -> None:
    _emit("WARNING", event, **kwargs)


def log_exception(event: str, exc: Exception, **kwargs) -> None:
    _emit(
        "ERROR",
        event,
        error=str(exc),
        traceback=traceback.format_exc(),
        **kwargs,
    )
