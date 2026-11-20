# Travclicks Payment Service — Implementation Plan

**Status:** Complete (Laravel-only)  
**Date:** 2026-10-14  
**Scope:** Online / Offline / Mix payments, Stripe Connect + application fees, wallet safeguards, FX snapshots.  
**Engine:** Laravel `WalletLedgerService` (Python microservice removed; Razorpay removed).

---

## 1. Repository and architecture findings

| Item | Finding |
|------|---------|
| Framework | Laravel 11 (`matoxi/laravel`), PHP 8.2+, PHPUnit 10 |
| Finance SoT | Azure Functions Python microservice + PostgreSQL `public` finance tables |
| Laravel role | HTTP proxy, orchestration, UI, RBAC; **not** ledger writer of record |
| Gateway today | Razorpay wired in `PaymentGatewayService`; Stripe PHP SDK present but **demo-only** (`StripePaymentController`) |
| Legacy wallet | `user_wallets` + `App\Models\Wallet` — **do not use** for new work |
| Canonical wallet | `public.wallet` + `public.wallet_ledger` via microservice |
| Order pipeline | Lite (`SingleTourPackageController`) / Pro (`EnquiryFormPro`) → enquiry negotiation → Confirm → service Approve → offline `payment_details` |
| Item source | Per-order `orders.order_type` (`online` \| `offline`); **no tour-level classification** |
| Customer payments UI | `#addPaymentModal` on confirmed/definite/actual; Cash / Card / Cheque / Bank Transfer (`online` = bank transfer) |
| Settings | Generic `settings` table exists; **no** Online/Offline/Wallet payment toggles |

Related docs already in repo:

- `finance-microservice/docs/SOFTWARE_DESIGN_DOCUMENT.md`
- `finance-microservice/docs/API_SPECIFICATION.md`
- `docs/sequence-diagrams/*`

---

## 2. Existing payment and wallet implementation

### 2.1 Must reuse (canonical)

- Tables: `wallet`, `wallet_ledger`, `payment_transactions`, `settlements`, `idempotency_store`, `callback_processing_log`
- Tours finance columns: `finance_reference`, `payment_mode`, `finance_status`, `selling_price`, etc.
- Services: `FinanceService`, `WalletService`, `WalletQueryService`, `SettlementService`, `PaymentGatewayService`, `BookingFinanceOrchestrator`
- Microservice: `wallet.py`, `payment.py`, `settlement.py`, `refund.py`, `callbacks.py`
- Jobs: `RetryFinanceOperationJob`, `ProcessFinanceSettlementJob`
- Currency: `CurrencyHelper` + `CurrencyService` (live rates; **no immutable snapshot table yet**)

### 2.2 Must preserve unchanged

- Enquiry status codes `1/2/3` and tour status string ladder
- Follow-ups Confirm → `BookingsController::agentNegotiation`
- Offline `tours.payment_details` JSON + verify → Definite
- Per-service online approve recheck/book in `HotelBookingController`
- Existing Cash / Cheque / Bank Transfer recording
- Razorpay path and finance microservice APIs (extend, do not replace)
- Historical wallet/ledger rows

### 2.3 Gaps and risks

| Gap | Risk |
|-----|------|
| No tour Online/Offline/Mix classification | Wrong payment stage / recipient |
| Agent wallet not DMC-scoped | Cross-DMC fund spend |
| `reserved_balance` unused | Premature full debit before supplier outcomes |
| Ledger CHECK missing `allocation_*` | Allocate may fail on strict PG |
| Online path creates Razorpay order without pending `payment_transactions` | Callback cannot settle |
| No platform payment settings | Cannot disable Online / Wallet safely |
| No Stripe Connect | Cannot route card pay to DMC vs Travclicks |
| No FX snapshot table | Historical amounts not reconstructable |
| Web confirm hardcodes 50/50 markup | Incorrect commission |
| New Enquiries “Confirm Tour” posts to wrong endpoint | Separate bug; **out of scope** unless blocking payment |

---

## 3. Business rules (target)

### Order classification (server-side)

| Type | Rule |
|------|------|
| `online` | ≥1 item and all classifiable items are `orders.order_type = online` |
| `offline` | ≥1 item and all classifiable items are `offline` (or null treated as offline for compat) |
| `mix` | ≥1 online and ≥1 offline |
| `empty` | No items — **not** online/offline; payment initiation rejected |

Null `order_type` → treat as **offline** for compatibility (documented); new Lite/Pro writes continue to set type.

### Payment recipient routing

| Scenario | Stage | Card recipient | Wallet allocation |
|----------|-------|----------------|-------------------|
| Online order accept | `supplier_initial` | Travclicks platform Stripe | Agent wallet for order’s DMC |
| Mix accept (online portion) | `supplier_initial` | Travclicks | Agent wallet for order’s DMC |
| Offline order card | `dmc_collection` | DMC Connect account | Agent wallet for order’s DMC |
| Mix post-confirm part-pay | `dmc_collection` | DMC Connect account | Agent wallet for order’s DMC |

### Payment settings

- Offline: always enabled (backend enforced)
- Wallet: enabled by default; toggleable
- Online (Stripe): disabled by default; requires valid sandbox/live config for active env

### Stripe Connect model (selected)

**Destination charges** on the platform account with `transfer_data.destination = dmc_stripe_account_id` for DMC-directed payments. Platform direct charges (no destination) for Travclicks `supplier_initial` collections.

Rationale: existing app has a single Stripe platform key; destination charges avoid requiring each DMC to be the merchant of record for PCI/checkout UX while still settling to the connected account. Application fees / commission remain in finance settlement + wallet credits (existing commission model), not assumed as Stripe `application_fee_amount` unless product confirms fee split later.

**Blocker (do not guess):** Whether contractual commission must also be expressed as Stripe `application_fee_amount` vs wallet-only. Default: **wallet/settlement commission only**; document for product confirmation.

---

## 4. Proposed database changes

| Change | Reason |
|--------|--------|
| `tours.order_classification` | Persist classification for stable lifecycle |
| `tours.payment_stage` | Track `none` / `supplier_initial` / `dmc_collection` |
| `payment_platform_settings` | Online/Wallet toggles + Stripe env (secrets via env, not HTML) |
| `dmc_stripe_accounts` | Connect account id + capability status per DMC |
| `currency_conversion_snapshots` | Immutable FX for financial events |
| `payment_transactions` extras: `payment_stage`, `payment_recipient`, `stripe_account_id`, `order_classification`, `payable_amount`, `captured_amount`, `fx_snapshot_id` | Multi-payment / routing / audit |
| `wallet.source_dmc_id` | DMC-scoped agent allocations |
| Expand `wallet_ledger` CHECK for `allocation_debit` / `allocation_credit` | Fix allocate under strict CHECK |
| `supplier_booking_outcomes` | Per-item online booking result audit |
| `payment_attempts` | Idempotent initiation + orchestration state |

All migrations are additive / nullable / defaulted for backward compatibility.

---

## 5. Reliability controls

- Idempotency keys on every payment initiation (reuse `idempotency_store` + `payment_transactions.idempotency_key`)
- Webhook dedup via `callback_processing_log` (extend gateway_name for `stripe`)
- Wallet `FOR UPDATE` + reserve/commit/release
- Orchestration state machine persisted on `payment_attempts` (saga; no fake distributed DB+Stripe+supplier transaction)
- Bounded retries via existing jobs
- Uncertain supplier outcomes → reconciliation required before retry/refund

---

## 6. Security requirements

- Server recomputes amount, eligibility, recipient, FX, commission
- DMC isolation on Connect settings and payment records
- Stripe webhook signature verification
- No secrets in Blade/JS/logs
- CSRF on web; Bearer on finance API; IDOR checks via existing WalletQueryService RBAC patterns
- Reject frontend-supplied rates, balances, Connect account ids

---

## 7. Numbered implementation checklist

### Phase 1 — Discovery
1. [DONE] Map order lifecycle, payment UI, finance stack, Stripe/Razorpay, FX
2. [DONE] Identify canonical wallet tables and regression risks

### Phase 2 — Planning
3. [DONE] Write `IMPLEMENTATION_PLAN.md`
4. [DONE] Write `ARCHITECTURE.md`
5. [DONE] Write `TEST_PLAN.md`

### Phase 3 — Foundation
6. [DONE] Migration: tours classification/stage + payment settings + Connect + FX snapshots + payment attempt/outcome columns
7. [DONE] `OrderClassificationService`
8. [DONE] `CurrencySnapshotService`
9. [DONE] `payment_attempts` / `supplier_booking_outcomes` tables (query via DB; no Eloquent required)

### Phase 4 — Settings & Stripe Connect
10. [DONE] `PaymentSettingsService` (Offline always on)
11. [DONE] Config + admin settings API/controller
12. [DONE] `StripeConnectService` onboarding + account sync + webhook handling
13. [DONE] Extend `PaymentGatewayService` for Stripe PaymentIntents (platform + destination)

### Phase 5 — Wallet
14. [DONE] `source_dmc_id` on wallet + allocate credits DMC-scoped agent wallets
15. [DONE] Reserve / commit / release in microservice + Laravel proxy
16. [DONE] Fix ledger entry type CHECK (`allocation_*`, `commit`)
17. [DONE] DMC allocation eligibility helper in Laravel

### Phase 6 — Orchestration
18. [DONE] `PaymentEligibilityService` (central methods response)
19. [DONE] `PaymentOrchestrator` Online / Offline / Mix stages
20. [DONE] Wire confirm gate for Online/Mix; Offline confirm + auto wallet preserved
21. [DONE] Supplier booking outcome recording + amount reconciliation hooks

### Phase 7 — Frontend
22. [DONE] Extend add-payment modal: Wallet + Card (Stripe) options from eligibility API
23. [DONE] Read-only amount mode for supplier_initial stage
24. [DONE] Compact helper text for stage/recipient/unavailable reasons

### Phase 8 — Tests
25. [DONE] Unit tests: classification, eligibility, settings, FX snapshot, orchestrator
26. [DONE] PHPUnit Payment + Finance: 21 tests (1 skipped without migration table)

### Phase 9 — Delivery
27. [DONE] Update checklist statuses, ops notes (`OPERATIONS.md`), env vars, rollback notes
28. [DONE] Document unverified: live Connect onboarding, supplier sandbox E2E

---

## 8. Acceptance criteria (phase-mapped)

See master prompt §18. A criterion is marked verified only after automated test or explicit safe verification.

| # | Criterion | Phase | Status |
|---|-----------|-------|--------|
| 1 | Classification correct | 3,8 | VERIFIED (unit) |
| 2 | Lite/Pro/enquiry preserved | 6,8 | PRESERVED (gate additive) |
| 3 | Payment popup reused | 7 | DONE (script extends modal) |
| 4–6 | Online / Offline / Mix workflows | 6 | IMPLEMENTED (supplier E2E unverified) |
| 7–8 | Recipient routing | 4,6 | VERIFIED (unit + services) |
| 9–10 | Wallet DMC scope + commission | 5,6 | IMPLEMENTED (microservice + eligibility) |
| 11 | FX snapshots | 3,5 | VERIFIED (unit) |
| 12–14 | Server validation / partial fail / webhooks | 6,8 | PARTIAL (unit; live Stripe unverified) |
| 15–17 | Offline methods / history / migrations | 3,7 | DONE |
| 18–20 | Tests / plan accuracy / no prod charges | 8,9 | VERIFIED (21 PHPUnit; no live charges) |

---

## 9. Rollback and migration considerations

- All new columns nullable or defaulted; dropping them does not destroy legacy `payment_details` or wallet history.
- Do not reverse-rename `user_wallets`.
- Stripe Connect account rows can be soft-disabled without deleting.
- Disabling Online/Wallet settings does **not** cancel in-flight PaymentIntents; pending attempts remain until timeout job / webhook terminal state.
- If `source_dmc_id` migration runs: legacy agent wallets (`source_dmc_id NULL`) remain spendable only under **compatibility mode** flag (`payment.wallet_legacy_unscoped_agent=true` default true initially) until allocations are re-keyed.

---

## 10. Unresolved decisions (product confirmation)

1. Stripe `application_fee_amount` vs wallet-only commission for Connect destination charges.
2. Whether Online card should replace Razorpay as `finance.payment_gateways.default` or run as parallel `stripe` when settings Online enabled (default: **parallel**; Razorpay preserved).
3. Whether Mix post-confirm offline methods should also create `payment_transactions` rows (default: **yes**, via orchestrator, while still writing `payment_details` for UI parity).

---

## 11. Progress log

| Date | Note |
|------|------|
| 2026-10-14 | Audit complete; plan/architecture/test docs authored; implementation starting Phase 3. |
| 2026-10-14 | Phases 3–9 implemented on existing finance stack. PHPUnit Payment+Finance: 21 OK (1 skipped). |
| 2026-10-15 | Online skips enquiry and confirms after full payment. Mix pays online services first; negotiation is the offline total only. Commission is credited once on the wallet ledger (no 50/50 split, no Stripe application fee on the same commission). Cross-currency payments add the conversion charge. |
