"""PostgreSQL connection and transaction helpers."""
from contextlib import contextmanager
from typing import Any, Optional

import psycopg2
import psycopg2.extras
from psycopg2 import pool

from shared.config import get_settings
from shared.logger import log_event, log_exception

_connection_pool: Optional[pool.ThreadedConnectionPool] = None


def _get_pool() -> pool.ThreadedConnectionPool:
    global _connection_pool
    if _connection_pool is None:
        settings = get_settings()
        _connection_pool = pool.ThreadedConnectionPool(
            minconn=1,
            maxconn=20,
            dsn=settings["database_url"],
        )
        log_event("db_pool_initialized")
    return _connection_pool


@contextmanager
def get_connection():
    """Acquire connection from pool; return to pool on exit."""
    conn = _get_pool().getconn()
    try:
        yield conn
    finally:
        _get_pool().putconn(conn)


@contextmanager
def db_transaction():
    """Atomic transaction with rollback on failure."""
    with get_connection() as conn:
        conn.autocommit = False
        try:
            yield conn
            conn.commit()
        except Exception:
            conn.rollback()
            raise
        finally:
            conn.autocommit = True


def fetch_one(conn, query: str, params: tuple = ()) -> Optional[dict]:
    with conn.cursor(cursor_factory=psycopg2.extras.RealDictCursor) as cur:
        cur.execute(query, params)
        row = cur.fetchone()
        return dict(row) if row else None


def fetch_all(conn, query: str, params: tuple = ()) -> list[dict]:
    with conn.cursor(cursor_factory=psycopg2.extras.RealDictCursor) as cur:
        cur.execute(query, params)
        return [dict(r) for r in cur.fetchall()]


def execute(conn, query: str, params: tuple = ()) -> int:
    with conn.cursor() as cur:
        cur.execute(query, params)
        return cur.rowcount


def execute_returning(conn, query: str, params: tuple = ()) -> dict:
    with conn.cursor(cursor_factory=psycopg2.extras.RealDictCursor) as cur:
        cur.execute(query, params)
        row = cur.fetchone()
        if not row:
            raise RuntimeError("execute_returning: no row returned")
        return dict(row)
