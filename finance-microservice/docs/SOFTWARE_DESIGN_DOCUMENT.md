# Travclicks Finance Microservice — Software Design Document

**Version:** 1.0  
**Date:** 2026-07-08  
**Author:** Travclicks Engineering  
**Status:** Production Ready

---

## 1. Executive Summary

The Finance Microservice handles all monetary operations for the Travclicks B2B Travel Platform: wallet management, online/offline payments, supplier settlements, commission calculation, and refunds. It is implemented as an **Azure Function App (Python v2)** with a dedicated **`finance` PostgreSQL schema**, integrated into the existing Laravel booking service via HTTP.

**Why a separate microservice?**
- **Isolation:** Payment logic is security-sensitive and benefits from independent deployment and scaling.
- **Single source of truth:** All financial calculations live in one bounded context (DDD).
- **Technology fit:** Python Azure Functions provide serverless scaling for millions of booking events.
- **Auditability:** Immutable wallet ledger and idempotent APIs support compliance requirements.

**Pricing example (business rule):**

| Party | Amount |
|-------|--------|
| Supplier API cost | $100 |
| Travclicks markup | $50 |
| Travclicks price | $150 |
| DMC commission | $70 |
| Agent selling price | $220 |
| Supplier receives | $100 |
| Travclicks profit | $50 |
| DMC profit | $70 |

---

## 2. Architecture

### 2.1 High-Level Architecture

```mermaid
flowchart LR
    subgraph Presentation
        A1[Agent Mobile/Web]
        A2[DMC Dashboard]
        A3[Finance Console]
    end

    subgraph BookingService["Laravel Booking Service"]
        API[REST API]
        Queue[Redis/Azure Queue]
    end

    subgraph FinanceMS["Finance Microservice"]
        AF[Azure Functions Python]
        Shared[Shared Modules]
    end

    subgraph Data
        PG[(PostgreSQL)]
        AI[Application Insights]
    end

    subgraph ThirdParty
        GW[Razorpay / PSP]
        SUP[HotelBeds / WebBeds / TBO]
    end

    A1 & A2 & A3 --> API
    API -->|Bearer Token| AF
    GW -->|Webhook| API
    API -->|Forward| AF
    AF --> Shared
    Shared --> PG
    AF --> AI
    AF --> SUP
    API --> Queue
    Queue --> API
```

### 2.2 Azure Function Architecture

```mermaid
flowchart TB
    subgraph FunctionApp["Azure Function App"]
        FA[function_app.py]
        subgraph SharedModules
            AUTH[auth.py]
            DB[db.py]
            WAL[wallet.py]
            PAY[payment.py]
            SET[settlement.py]
            REF[refund.py]
            CB[callbacks.py]
            MOD[models.py]
        end
    end

    FA --> AUTH
    FA --> WAL & PAY & SET & REF & CB
    WAL & PAY & SET & REF & CB --> DB
    FA --> MOD
```

### 2.3 Laravel ↔ Azure Communication

```mermaid
sequenceDiagram
    participant L as Laravel
    participant Q as Queue
    participant A as Azure Functions
    participant DB as PostgreSQL

    L->>A: POST /wallet/booking (Bearer + idempotency_key)
    A->>A: verify_bearer_token()
    A->>A: check_idempotency()
    A->>DB: BEGIN TRANSACTION
    A->>DB: debit wallet, create payment, create settlement
    A->>DB: update tours finance columns
    A->>DB: COMMIT
    A-->>L: { success, data: { finance_reference, settlement_uuid } }
    L-->>L: Return to client

    Note over L,Q: On failure
    L->>Q: RetryFinanceOperationJob (exponential backoff)
    Q->>A: Retry same idempotency_key
```

---

## 3. Database Design

### 3.1 ER Diagram

```mermaid
erDiagram
    tours ||--o| payment_transactions : "payment_transaction_id"
    tours ||--o| settlements : "settlement_id"
    wallet ||--o{ wallet_ledger : "wallet_id"
    wallet ||--o{ payment_transactions : "wallet_id"
    payment_transactions ||--o{ settlements : "payment_transaction_id"
    payment_transactions ||--o{ callback_processing_log : "payment_transaction_id"
    wallet_ledger }o--|| payment_transactions : "payment_transaction_id"
    wallet_ledger }o--|| settlements : "settlement_id"

    tours {
        bigint id PK
        varchar finance_reference
        bigint payment_transaction_id
        bigint settlement_id
        varchar payment_mode
        varchar payment_status
        numeric supplier_cost
        numeric travclicks_markup
        numeric dmc_commission
        numeric selling_price
        varchar supplier_api_id
        varchar finance_status
    }

    wallet {
        bigint id PK
        uuid wallet_uuid UK
        varchar wallet_owner_type
        bigint wallet_owner_id
        varchar supplier_api_id
        char wallet_currency_code
        numeric available_balance
        varchar wallet_status
    }

    payment_transactions {
        bigint id PK
        uuid payment_transaction_uuid UK
        varchar finance_reference UK
        bigint tour_id
        varchar payment_mode
        varchar payment_status
        numeric supplier_cost
        numeric selling_price
        varchar idempotency_key UK
    }

    settlements {
        bigint id PK
        uuid settlement_uuid UK
        varchar settlement_reference UK
        bigint tour_id
        bigint payment_transaction_id FK
        varchar settlement_type
        varchar settlement_status
        numeric travclicks_profit
        numeric dmc_profit
        varchar finance_status
    }

    wallet_ledger {
        bigint id PK
        uuid ledger_uuid UK
        bigint wallet_id FK
        varchar ledger_entry_type
        numeric debit_amount
        numeric credit_amount
        numeric balance_after
    }
```

### 3.2 Why `tours` Columns Were Added (Not Redesigned)

The existing `tours` table is owned by the booking service. Finance columns are **denormalized snapshots** for fast list/detail queries without cross-schema joins on every booking screen.

| Column | Type | Nullable | Purpose | Example |
|--------|------|----------|---------|---------|
| `finance_reference` | VARCHAR(64) | YES | Links to finance microservice operation | `FIN-20260708-100001` |
| `payment_transaction_id` | BIGINT | YES | FK to `payment_transactions.id` | `12345` |
| `settlement_id` | BIGINT | YES | FK to `settlements.id` | `6789` |
| `payment_mode` | VARCHAR(32) | YES | Payment channel | `wallet` |
| `payment_status` | VARCHAR(32) | YES | Payment lifecycle | `captured` |
| `gateway_transaction_reference` | VARCHAR(128) | YES | PSP transaction ID | `pay_abc123` |
| `gateway_response_json` | JSONB | YES | Create-payment API response | `{ "id": "order_xyz" }` |
| `supplier_cost` | NUMERIC(18,4) | YES | Net supplier price | `100.0000` |
| `travclicks_markup` | NUMERIC(18,4) | YES | Platform markup | `50.0000` |
| `dmc_commission` | NUMERIC(18,4) | YES | DMC profit component | `70.0000` |
| `selling_price` | NUMERIC(18,4) | YES | Agent-facing price | `220.0000` |
| `supplier_api_id` | VARCHAR(64) | YES | Supplier identifier | `hotelbeds` |
| `supplier_booking_reference` | VARCHAR(128) | YES | Supplier confirmation | `HB-2026-998877` |
| `finance_status` | VARCHAR(32) | YES | Settlement state | `settled` |
| `payment_received_amount` | NUMERIC(18,4) | YES | Actual received amount | `220.0000` |
| `payment_received_currency` | CHAR(3) | YES | ISO 4217 | `USD` |
| `payment_gateway_name` | VARCHAR(64) | YES | PSP name | `razorpay` |
| `payment_gateway_response` | JSONB | YES | Webhook/callback payload | `{ "status": "captured" }` |
| `payment_received_datetime` | TIMESTAMPTZ | YES | Payment confirmation time | `2026-07-08T10:30:00Z` |
| `master_dmc_id` | BIGINT | YES | Hierarchy routing | `5` |

**Source of truth:** public schema finance tables (`wallet`, `wallet_ledger`, `payment_transactions`, `settlements`). `tours` columns are updated by the microservice on every financial event.

---

## 4. Sequence Diagrams

### 4.1 Wallet Top-up

```mermaid
sequenceDiagram
    participant DMC as DMC User
    participant L as Laravel
    participant A as Azure /wallet/topup
    participant W as wallet
    participant Ld as wallet_ledger

    DMC->>L: POST /api/v1/finance/wallet/topup
    L->>A: Forward with Bearer token
    A->>A: Validate + idempotency check
    A->>W: get_or_create_wallet (FOR UPDATE)
    A->>W: UPDATE available_balance += amount
    A->>Ld: INSERT ledger entry (topup, credit)
    A-->>L: { finance_reference, wallet_uuid, available_balance }
    L-->>DMC: 200 OK
```

### 4.2 Wallet Booking

```mermaid
sequenceDiagram
    participant Agent
    participant L as Laravel
    participant A as Azure /wallet/booking
    participant W as wallet
    participant P as payment_transactions
    participant S as settlements
    participant T as tours
    participant SW as supplier wallet

    Agent->>L: POST /api/v1/finance/wallet/booking
    L->>A: Forward request
    A->>A: BEGIN TRANSACTION
    A->>P: INSERT payment (mode=wallet, status=captured)
    A->>W: debit_wallet(selling_price)
    A->>S: INSERT settlement (wallet_booking)
    A->>SW: debit supplier_cost
    A->>T: UPDATE finance columns
    A->>A: COMMIT
    A-->>L: { finance_reference, settlement_uuid }
    L-->>Agent: 200 OK
```

### 4.3 Online Payment Booking

```mermaid
sequenceDiagram
    participant Agent
    participant L as Laravel
    participant GW as Payment Gateway
    participant A as Azure Functions
    participant DB as PostgreSQL

    Agent->>L: POST /api/v1/finance/payment/create
    L->>GW: Create order
    GW-->>L: order_id + client_secret
    L->>DB: tours.payment_status = pending
    L-->>Agent: Gateway checkout details

    Agent->>GW: Complete payment
    GW->>L: POST /api/v1/finance/webhook/payment
    L->>A: POST /payment/callback
    A->>A: Verify signature + idempotency
    A->>DB: UPDATE payment_transactions (captured)
    A->>DB: INSERT settlement + deduct supplier wallet
    A->>DB: UPDATE tours (settled)
    A-->>L: { status: captured }
    L-->>GW: 200 OK
```

### 4.4 Offline Booking

```mermaid
sequenceDiagram
    participant Finance as Finance Team
    participant L as Laravel
    participant A as Azure /settlement/offline
    participant DB as PostgreSQL

    Finance->>L: Confirm office payment received
    Finance->>L: POST /api/v1/finance/settlement/offline
    L->>A: Forward with finance_confirmed_by
    A->>DB: INSERT payment (mode=offline_office)
    A->>DB: INSERT settlement
    A->>DB: Deduct supplier wallet
    A->>DB: UPDATE tours finance_status=settled
    A-->>L: settlement_reference
    L-->>Finance: 200 OK
```

### 4.5 Refund

```mermaid
sequenceDiagram
    participant Agent
    participant L as Laravel
    participant A as Azure /refund
    participant W as wallet
    participant SW as supplier wallet
    participant DB as PostgreSQL

    Agent->>L: POST /api/v1/finance/refund
    L->>A: refund_type=full
    A->>DB: INSERT settlement (type=refund)
    A->>W: credit_wallet (customer wallet refund)
    A->>SW: credit_wallet (supplier credit restore)
    A->>DB: UPDATE payment_status=refunded
    A->>DB: UPDATE tours finance_status=refunded
    A-->>L: { payment_status: refunded }
    L-->>Agent: 200 OK
```

---

## 5. Business Rules

1. **Selling price validation:** `selling_price = supplier_cost + travclicks_markup + dmc_commission` (±$0.01 tolerance).
2. **Wallet debit:** Booking fails with HTTP 402 if `available_balance < selling_price`.
3. **Idempotency:** Same `idempotency_key` + operation returns cached response for 24 hours.
4. **Callback deduplication:** `callback_processing_log` unique on `(gateway_name, gateway_transaction_reference)`.
5. **Supplier wallet:** Travclicks maintains per-supplier API credit wallets; settlement debits `supplier_cost`.
6. **Atomicity:** All wallet + payment + settlement operations use PostgreSQL transactions with rollback on failure.

---

## 6. Deployment Architecture

### Azure Resources

| Resource | Purpose |
|----------|---------|
| Azure Function App (Python 3.11, Consumption/Premium) | Finance HTTP API |
| Azure Database for PostgreSQL Flexible Server | Shared DB with `finance` schema |
| Azure Key Vault | Bearer token, webhook secret, DB connection |
| Application Insights | Structured logging, alerts |
| Azure Storage Queue | Dead-letter for exhausted retries |
| Azure API Management (optional) | Rate limiting, WAF |

### Security

- **Laravel → Azure:** Bearer token (`FINANCE_API_BEARER_TOKEN`) via `Authorization: Bearer <token>`.
- **Gateway → Laravel:** HMAC-SHA256 signature (`X-Gateway-Signature`).
- **Database:** Connection string in Key Vault; finance schema restricted to function managed identity.

### Monitoring & Logging

- Structured JSON logs via `shared/logger.py`.
- Application Insights custom events: `wallet_topup_completed`, `callback_processed`, `refund_completed`.
- Alerts on: 5xx rate > 1%, callback processing lag > 5 min, wallet negative balance attempts.

### Retry Strategy

| Layer | Strategy |
|-------|----------|
| Azure Functions | Exponential backoff (host.json): 3 retries, 2s–30s |
| Laravel Queue | `RetryFinanceOperationJob`: 10s, 60s, 300s backoff |
| Idempotency | Same key prevents duplicate financial side-effects |

### Performance & Scalability

- Connection pool: 1–20 connections per function instance.
- Indexed columns on `tour_id`, `gateway_transaction_reference`, `finance_reference`.
- Horizontal scaling: Azure Functions auto-scale on HTTP queue depth.
- Target: 10,000 settlements/minute at Premium plan with read replicas.

### Backup & Disaster Recovery

- PostgreSQL: Point-in-time recovery (35 days), geo-redundant backup.
- `wallet_ledger` is append-only — never deleted, supports forensic audit.
- RTO: 1 hour, RPO: 5 minutes.

---

## 7. Future Enhancements

- Multi-currency FX conversion at settlement time.
- Event-driven architecture via Azure Service Bus (booking.created → auto-settle).
- Real-time finance dashboard with read replicas.
- Stripe/Adyen gateway adapters.
- Automated supplier reconciliation batch jobs.
