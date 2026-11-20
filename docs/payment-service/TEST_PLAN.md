# Travclicks Payment Service — Test Plan

**Date:** 2026-10-14  
**Framework:** PHPUnit 10 (`tests/Unit/Finance`, `tests/Unit/Payment`, feature tests as needed)  
**External systems:** Mock Stripe SDK, mock finance HTTP (`FinanceService`), mock supplier APIs. **No real charges.**

---

## 1. Order classification

| Case | Expected |
|------|----------|
| All `order_type=online` | `online` |
| All `offline` | `offline` |
| Mix online + offline | `mix` |
| Empty orders | `empty` |
| Null `order_type` | treated as offline (compat) |
| Tampered client label ignored | server uses `orders.order_type` only |

**Files:** `tests/Unit/Payment/OrderClassificationServiceTest.php`

---

## 2. Payment settings

| Case | Expected |
|------|----------|
| Offline always enabled | cannot disable via service |
| Wallet default on | new installs |
| Online default off | rejects card when disabled |
| Pending intents when Online disabled | not auto-cancelled |

**Files:** `tests/Unit/Payment/PaymentSettingsServiceTest.php`

---

## 3. Eligibility

| Case | Expected |
|------|----------|
| Online order + wallet enabled + sufficient DMC allocation | Wallet visible |
| Sufficient total, insufficient DMC allocation | Wallet hidden + reason |
| Online disabled | Card hidden |
| DMC Connect not ready (offline/mix stage 2) | Card hidden + reason |
| Mix stage 1 | payable = online component only |
| Mix stage 2 | offline methods + DMC card/wallet |

**Files:** `tests/Unit/Payment/PaymentEligibilityServiceTest.php`

---

## 4. Online order orchestration

| Case | Expected |
|------|----------|
| Wallet success path | reserve/commit, commission once |
| Partial supplier success | charge booked only; release rest |
| All unavailable | no confirm; full release/refund |
| Duplicate idempotency key | same attempt returned |
| Amount tampering | rejected |

**Files:** `tests/Unit/Payment/PaymentOrchestratorTest.php` (mocked deps)

---

## 5. Offline / Mix

| Case | Expected |
|------|----------|
| Offline confirm without supplier book | preserved |
| Offline card recipient = DMC | destination account set |
| Mix stage 1 blocks confirm without payment | enforced |
| Mix stage 2 overpay rejected | enforced |

---

## 6. Currency snapshots

| Case | Expected |
|------|----------|
| Same currency | rate 1 snapshot |
| INR wallet → SGD order | snapshot persisted |
| Rate update later | historical snapshot unchanged |
| Missing rate | fail safe |

**Files:** `tests/Unit/Payment/CurrencySnapshotServiceTest.php`

---

## 7. Security / regression

| Case | Expected |
|------|----------|
| Cross-DMC Connect access | 403 |
| Invalid Stripe webhook signature | rejected |
| Existing FinanceService / orchestrator unit tests | still pass |

---

## 8. Execution commands

```bash
php vendor/bin/phpunit --filter Payment
php vendor/bin/phpunit tests/Unit/Finance
vendor/bin/pint --test app/Services/Payment app/Services/Finance 2>/dev/null || true
```

---

## 9. Manual / env-dependent (not automated)

- Stripe Connect Express onboarding in test mode
- Live supplier booking sandbox
- End-to-end Mix UI on confirmed bookings page

Document results in IMPLEMENTATION_PLAN progress log.
