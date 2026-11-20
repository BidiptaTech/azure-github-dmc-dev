# Wallet Implementation — QA Regression Test Cases

**Document version:** 1.0  
**Scope:** Wallet Management, allocation/top-up, online acceptance payment (Pay Now), tour/service cancel + refunds (Travclicks ↔ DMC).  
**Audience:** QA / regression testers  
**App base:** Laravel DMC (`/finance/wallet`, `/bookings/*`, `/payment-service/*`)

---

## How to use this document

| Column | Meaning |
|--------|---------|
| **ID** | Unique test case id |
| **Type** | `P` Positive · `N` Negative · `E` Edge |
| **Priority** | `P0` blocker · `P1` high · `P2` medium |
| **Result** | Leave blank for execution (Pass / Fail / Blocked) |

**Role cheat-sheet**

| Role | Typical `role_id` |
|------|-------------------|
| Travclicks Owner / Admin | 1–4 |
| Master DMC | 10 |
| DMC Owner | 11 |
| Agent | 20 |
| Multi-role (sales+finance+ops+product) | 138 |
| Finance / sales staff (examples) | 33, 36, 126, 127, 128… |

**Config to note before run**

- `FINANCE_DMC_AGENCY_ALLOCATION_ENABLED` (default **false**) — DMC → Agency wallet funding  
- Wallet / Stripe toggles in Payment Settings  
- Country SGD rates synced (Profile → Sync Currency Rates) for Travclicks → DMC FX  

---

## 0. Preconditions / test data checklist

| # | Prepare |
|---|--------|
| 0.1 | Travclicks user (role 1–4) with Wallet sidebar access |
| 0.2 | Master DMC (10) with ≥1 child DMC |
| 0.3 | DMC (11) with country currency + wallet row (or empty wallet OK for first credit) |
| 0.4 | Multi-role user (138) under that DMC (`created_by` = DMC) |
| 0.5 | Agent (20) linked to DMC / agency |
| 0.6 | Mix tour: ≥1 **online** hotel/attraction + ≥1 **offline** service |
| 0.7 | Full **online** tour and full **offline** tour |
| 0.8 | Known SGD → DMC currency rate in `countries` |
| 0.9 | Record starting wallet balances (Travclicks / DMC / Agent) before each money test |

---

# Module A — Wallet Management (access & UI)

| ID | Type | Pri | Title | Preconditions | Steps | Expected result |
|----|------|-----|-------|---------------|-------|-----------------|
| WM-01 | P | P0 | Travclicks sees Wallet Management | Login role 1–4 | Open sidebar → Finance → Wallet Management | Page loads; allocate **Send to DMC** visible |
| WM-02 | P | P0 | Master DMC sees Wallet Management | Login role 10 | Open `/finance/wallet` | Own master wallet; **Allocate Wallet (Master)** |
| WM-03 | P | P0 | DMC sees Wallet Management | Login role 11 | Open `/finance/wallet` | DMC balance cards + ledger |
| WM-04 | P | P0 | Multi-role 138 sees Wallet (view-only) | Login role 138 | Open sidebar Wallet Management | Parent DMC wallet shown; **no** allocate / top-up form |
| WM-05 | P | P1 | Agent sees own wallet only | Login role 20 | Open `/finance/wallet` | Own agent wallet; no allocate |
| WM-06 | N | P0 | Finance-only role without wallet menu | Login role 33 (if not in wallet role list) | Check Finance sidebar | **Wallet Management** not listed |
| WM-07 | N | P0 | 138 cannot allocate via POST | Role 138 | Craft/POST `/finance/wallet/topup` (or use UI if shown) | 403 / not allowed; balances unchanged |
| WM-08 | N | P1 | Access another DMC wallet by URL | Role 11 or 138 | Open wallet with foreign `owner_type`/`owner_id` | 403 — no access |
| WM-09 | E | P1 | 138 without resolvable parent DMC | 138 with empty/invalid `created_by` | Open `/finance/wallet` | Clear error / 403 (parent DMC not resolved) |
| WM-10 | P | P2 | Wallet Transactions navigation | Any allowed role | Click **Wallet Transactions** | `/finance/wallet/transactions` loads; back link works |
| WM-11 | P | P2 | Ledger filters | Role with wallet | Filter ledger by date / page | Results refresh; totals consistent |
| WM-12 | E | P2 | No duplicate rows for same agent | Tour/agent with historically duplicate `agents.agent_id` rows | Open bookings/refunds/wallet lists that join agents | Each tour/agent appears **once** |

### Known issues / risks (Module A)

1. **Agents table duplicates** — physical duplicate `agent_id` rows caused listing fan-out; mitigated by `DISTINCT ON` join in booking lists. Re-test refunds/bookings for duplicates after data imports.  
2. **Role 138 is view-only by design** — allocate must stay blocked even if UI regresses.  
3. Finance roles (36/126/127) may see Stripe Merchant elsewhere but **not** Wallet Management unless added to sidebar gate `[1,2,3,4,10,11,20,138]`.

---

# Module B — Wallet allocate / top-up / FX

| ID | Type | Pri | Title | Preconditions | Steps | Expected result |
|----|------|-----|-------|---------------|-------|-----------------|
| WA-01 | P | P0 | Travclicks → DMC (same or cross currency) | Role 1–4; DMC exists; SGD rate synced | Select DMC + currency; enter SGD; Reference ID; **Send to DMC** | Success toast; DMC credited converted amount; ledger credit on DMC; Travclicks may use platform spend |
| WA-02 | P | P0 | SGD preview matches credit | Same as WA-01 | Open allocate; change amount; wait for **sgd-preview** | Preview = posted credit (± rounding) |
| WA-03 | N | P0 | Travclicks missing Reference ID | Role 1–4 | Submit without reference | Validation error; no transfer |
| WA-04 | N | P0 | Travclicks send to non-DMC | Role 1–4 | Attempt allocate to agent/supplier if UI allows | 403 / blocked |
| WA-05 | N | P1 | Missing country SGD rate | Clear/break rate for DMC currency | Send to DMC | Error: sync currency rates; nothing deducted |
| WA-06 | P | P0 | Master DMC → child DMC | Role 10; child DMC under master | Allocate to child DMC | Child credited; master debit if applicable |
| WA-07 | N | P0 | Master DMC → foreign DMC | Role 10 | Allocate to DMC not under master | 403 |
| WA-08 | P | P1 | convert-preview same currency | Any allocator | from=to | Rate 1; converted = amount |
| WA-09 | N | P1 | convert-preview no live rate | Unsupported pair | Call convert-preview | 422 exchange rate unavailable |
| WA-10 | P | P1 | DMC → Agency when flag ON | Set `FINANCE_DMC_AGENCY_ALLOCATION_ENABLED=true`; DMC has agency | Allocate to agency | Agency wallet credited; DMC debited |
| WA-11 | N | P0 | DMC → Agency when flag OFF | Flag false (default) | Open wallet as DMC | No agency allocate (or POST redirects with disabled message) |
| WA-12 | N | P0 | Agent cannot top up | Role 20 | POST topup | 403 |
| WA-13 | N | P1 | Insufficient allocator balance | Master/DMC with low balance | Allocate more than available | Error; **nothing** deducted (atomic) |
| WA-14 | E | P1 | Double-submit allocate | Same form submit twice quickly | Two clicks / refresh | At most one credit (idempotency / validation) |
| WA-15 | E | P2 | Multi-currency DMC wallet | DMC has wallets in 2 currencies | Switch currency on page | Correct balance/ledger for selected currency |
| WA-16 | P | P2 | Travclicks DMC ledger drill | Role 1–4 | Masters → DMCs → ledger | Drill AJAX loads; pagination works |
| WA-17 | N | P2 | dmc-ledger as Agent/138 | Role 20 or 138 | Call `/finance/wallet/dmc-ledger` | 403 |

### Known issues / risks (Module B)

1. **Two FX systems** — Travclicks→DMC uses **stored** `countries` SGD rate; Master/DMC cross-currency may use **live** `CurrencyHelper`. Test both; mismatches are expected if rates diverge.  
2. Travclicks UI amount is **SGD**, credit is in **DMC currency** — QA must compare preview vs ledger, not assume 1:1.  
3. DMC→Agency default **OFF** — environment-dependent; document env for each test run.  
4. Wallet list for Travclicks capped (~100) — large datasets may hide wallets.

---

# Module C — Online acceptance payment (Pay Now)

| ID | Type | Pri | Title | Preconditions | Steps | Expected result |
|----|------|-----|-------|---------------|-------|-----------------|
| PAY-01 | P | P0 | Mix tour — Pay Now card | Mix online+offline; online unpaid | Open New Enquiries | Compact **Pay online first** card; blinking **Pay Now**; offline amount “later” |
| PAY-02 | P | P0 | Mix — wallet pay online portion | PAY-01; DMC wallet funded | Pay Now → Wallet → Pay | Online paid; card replaced by **Negotiate**; offline negotiable only |
| PAY-03 | P | P1 | Mix — Stripe pay online portion | Stripe Connect ready | Pay Now → Online Card | Payment completes; same as PAY-02 |
| PAY-04 | P | P0 | Full online tour — Pay Now | All services online | Pay Now | Full amount due; after pay tour can confirm |
| PAY-05 | P | P1 | Offline-only — Negotiate only | Offline tour | Open New Enquiries | No Pay Now card; Negotiate available |
| PAY-06 | N | P0 | Negotiate before online paid (mix) | Mix unpaid online | Try negotiate / confirm | Blocked; must pay online first |
| PAY-07 | N | P0 | Insufficient wallet | Mix; wallet balance &lt; payable | Open Pay Now modal | Wallet method disabled / reason shown |
| PAY-08 | N | P1 | Payments globally disabled | Turn off wallet/stripe settings | Open Pay Now | Methods unavailable |
| PAY-09 | N | P1 | Stripe without Connect | DMC Connect incomplete | Select Online Card | Unavailable |
| PAY-10 | E | P1 | Payable 0 | Edge tour with 0 online amount | Open modal | Pay disabled / no payable message |
| PAY-11 | E | P2 | Follow-ups same Pay Now UX | Mix unpaid on Follow-ups | Same as PAY-01/02 | Same behaviour as New Enquiries |
| PAY-12 | E | P1 | Confirm blocked until supplier payment | ONLINE/MIX unpaid | Confirm tour | Requires payment / blocked |
| PAY-13 | E | P2 | Client amount tampering | Intercept pay API; change amount | Submit | Server uses eligibility amount; ignore client |

### Known issues / risks (Module C)

1. Acceptance modal exposes **Wallet + Stripe only** (by design).  
2. After online pay, mix orders may still be `bookingType=enquiry` until confirm — **cancel/refund path must still treat them as paid online**.  
3. **`payment_attempts.refunded_amount` is unused** — do not assert refund tracking on this column.  
4. Post-pay may open online hotel approve modal — don’t treat as failure.

---

# Module D — Per-service cancel + refund

| ID | Type | Pri | Title | Preconditions | Steps | Expected result |
|----|------|-----|-------|---------------|-------|-----------------|
| SR-01 | P | P0 | Cancel offline service (Definite) | Definite tour; offline hotel | Cancel/remove service | Order soft-deleted `is_refund=1`; **DMC Refunds** shows service immediately |
| SR-02 | P | P0 | Cancel online service (Definite) | Definite; paid online hotel | Cancel service | Supplier cancel success; Travclicks Refunds shows online icon |
| SR-03 | P | P0 | Travclicks refunds online → DMC wallet | SR-02 | Travclicks → Refunds → service icon → enter amount → credit | `travclicks_refunded=true`; DMC wallet credited; badge Refunded to DMC |
| SR-04 | P | P0 | DMC refunds agent after Travclicks | SR-03 | DMC → Refunds → initiate → verify | `refunded=true`; agent refund complete |
| SR-05 | N | P0 | DMC cannot refund online before Travclicks | SR-02 unfinished | DMC tries initiate online | 422 wait for Travclicks |
| SR-06 | N | P0 | Travclicks cannot refund offline | Offline cancelled service | Login Travclicks Refunds | Offline service **not** listed / 403 if forced |
| SR-07 | N | P0 | Travclicks verify action on online | Online pending Travclicks | Call verify instead of initiate | 422 one-step only |
| SR-08 | N | P0 | Supplier cancel fails | Mock/fail supplier API | Cancel online hotel | 422; order **not** deleted; no `is_refund` |
| SR-09 | N | P1 | Refund amount above max | Online pending | Enter amount &gt; max | 422 with max |
| SR-10 | E | P1 | Hotel cancellation policy amount | Hotel with cancellation rules | Open refund modal | Suggested refund / charge shown; amount capped |
| SR-11 | E | P1 | Repeat Travclicks credit same order | Already `travclicks_refunded` | Refund again | 422 already refunded; no double wallet credit (idempotency key) |
| SR-12 | E | P2 | Order action uses `orders.id` | Orders sharing booking_id | Refund via UI | Correct order updated |

### Known issues / risks (Module D)

1. Pipeline is **strictly ordered**: Travclicks wallet → then DMC agent.  
2. Flags differ: Travclicks uses `travclicks_refunded` / `travclicks_refund_amount`; DMC uses `is_verify` / `refunded` / `refund_amount`.  
3. Idempotency key pattern: `travclicks-online-refund-order-{orderId}` — retest after DB restore carefully.

---

# Module E — Full tour cancel + refund (wallet-related)

| ID | Type | Pri | Title | Preconditions | Steps | Expected result |
|----|------|-----|-------|---------------|-------|-----------------|
| TC-01 | P | P0 | Mix New Enquiry + online paid + cancel tour | Mix; Pay Now done; still New Enquiry | Cancel tour | Status **Refund - Pending** (not Cancel-New Enquiry); online order `is_refund=1` soft-deleted |
| TC-02 | P | P0 | Travclicks sees online service only (tour cancel) | TC-01 | Login Travclicks → Refunds | Tour row; **online service icon** (same as order cancel); **no** Process tour refund |
| TC-03 | P | P0 | Travclicks credits DMC for tour-cancel online | TC-02 | Refund online service | Same as SR-03 |
| TC-04 | P | P0 | DMC sees online after Travclicks; offline/tour path | TC-03 | Login DMC Refunds | Online visible; can initiate/verify; offline/tour refund per DMC rules |
| TC-05 | P | P1 | Definite mix cancel tour | Definite mix with paid online | Cancel tour | Online queued; Refund - Pending; Travclicks online-only |
| TC-06 | P | P1 | Full online Definite cancel | Online-only Definite paid | Cancel | Online refunds queued; Travclicks processes services |
| TC-07 | N | P0 | Cancel without paid online (New Enquiry) | Unpaid mix/offline enquiry | Cancel tour | Status `Cancel-…`; appears under **Cancelled**, **not** Refunds for Travclicks |
| TC-08 | N | P0 | Supplier fail blocks tour cancel | Paid online; supplier cancel fails | Cancel tour | Error; tour status **unchanged**; orders intact |
| TC-09 | N | P0 | Travclicks Process tour refund blocked | Any Refund - Pending | Travclicks try tour refund API/UI | Button hidden; API 403 |
| TC-10 | P | P1 | DMC Process tour refund (no service rows / offline tour cancel) | Tour Refund - Pending without online service rows for DMC | DMC **Process tour refund** | Status **Refunded**; amount saved |
| TC-11 | E | P0 | Online order still `enquiry` after pay | Mix after PAY-02 | Cancel tour | Still queued (promoted to booking + is_refund) |
| TC-12 | E | P1 | Negotiation cancel vs API cancel | Same paid mix | Cancel via UI vs API | Both queue online; watch status string (`Cancel - X` vs `Cancel-X`) |
| TC-13 | E | P2 | Actual status cancel with online | Actual + online booking | Cancel | Online refund path; Cancelled/Refunds listing check |
| TC-14 | E | P1 | No duplicate tour rows on Refunds | Agents with duplicate agent_id | Open Refunds | One row per tour_id |
| TC-15 | P | P1 | Multi-role 138 on DMC refunds | Role 138 | After Travclicks credit | Can see/initiate DMC-side refunds per role rules; cannot do Travclicks wallet credit |

### Known issues / risks (Module E)

1. **Historical bug (fixed):** Tour cancel after online pay on **New Enquiry** used to become `Cancel-New Enquiry` with no Refunds row — regression **TC-01/TC-02** are mandatory.  
2. **Historical bug (fixed):** Travclicks Refunds showed “Cancel Tour Refund Pending” instead of online service buttons — regression **TC-02**.  
3. Status string inconsistency: `Cancel - New Enquiry` vs `Cancel-New Enquiry` may affect Cancelled filters — note which entry point was used.  
4. Tour-level `processRefund` does **not** update per-order wallet fields — online money must go through Travclicks service refund.  
5. If `order_type` column missing in DB, Travclicks Refunds returns empty — environment sanity check.

---

# Module F — End-to-end money integrity (wallet ledger)

| ID | Type | Pri | Title | Steps | Expected |
|----|------|-----|-------|-------|----------|
| INT-01 | P | P0 | Travclicks→DMC allocate then booking wallet pay | Allocate SGD to DMC → pay mix online with wallet | DMC available decreases by payable (+FX/charge rules); ledger shows booking debit |
| INT-02 | P | P0 | Online cancel refund restores DMC wallet | Pay online with wallet → cancel service/tour → Travclicks refund | DMC wallet increases by refunded amount; ledger refund credit |
| INT-03 | E | P1 | FX conversion charge on wallet pay | Wallet currency ≠ tour currency | Eligibility shows conversion charge; paid amount matches | 
| INT-04 | N | P1 | Cannot refund more than paid/policy max | Attempt oversized refund | Rejected; wallet unchanged |
| INT-05 | E | P2 | Parallel refunds two online orders same tour | Two online hotels cancelled | Each refund independently; two ledger lines; no cross-order overwrite |

---

# Module G — Regression smoke pack (run every build)

Minimum **P0** set (~20–25 min):

1. WM-01, WM-04, WM-07  
2. WA-01, WA-03, WA-11  
3. PAY-01, PAY-02, PAY-06  
4. SR-02, SR-03, SR-04, SR-05, SR-06  
5. TC-01, TC-02, TC-03, TC-07, TC-08, TC-09  
6. INT-01, INT-02  
7. WM-12 / TC-14 (no duplicate rows)

---

## Execution log template

| ID | Tester | Date | Build/Branch | Result | Notes / Evidence |
|----|--------|------|--------------|--------|------------------|
| WM-01 | | | | | |
| … | | | | | |

---

## Logic & technical risks summary (for QA awareness)

| # | Area | Risk | Severity | Mitigation / test |
|---|------|------|----------|-------------------|
| R1 | Agents join | Duplicate `agent_id` rows → duplicate UI rows | High | TC-14, WM-12 |
| R2 | Tour cancel timing | Paid online on New Enquiry must enter Refunds | High | TC-01 |
| R3 | Travclicks UI | Must show online service refund, not tour refund | High | TC-02, TC-09 |
| R4 | Two FX stacks | Stored SGD rate vs live CurrencyHelper | Medium | WA-01 vs WA-06 |
| R5 | DMC→Agency flag | Default off; env-dependent | Medium | WA-10/11 |
| R6 | `payment_attempts.refunded_amount` | Column unused | Low | Do not assert |
| R7 | Cancel status strings | Space vs no-space after `Cancel` | Medium | TC-12 |
| R8 | Idempotent refunds | Double credit prevention | High | SR-11 |
| R9 | Supplier cancel failure | Must abort tour cancel | High | TC-08 |
| R10 | Role 138 | View wallet; DMC-side refunds; no Travclicks credit | Medium | WM-04, TC-15 |
| R11 | Online still `enquiry` after pay | Must still queue on tour cancel | High | TC-11 |
| R12 | Wallet list limit | Travclicks ~100 wallets | Low | Large data only |

---

## Out of scope / not covered by current wallet implementation

- Automatic full-tour refund of offline + online in one Travclicks click  
- Writing `payment_attempts.refunded_amount` on refund  
- Agent initiating Travclicks→DMC wallet credit  
- Multi-role 138 allocating wallet funds  

---

## Related code (for triage)

- `app/Http/Controllers/WalletManagementController.php`  
- `app/Services/Finance/WalletQueryService.php` / `WalletService.php`  
- `app/Services/Payment/AcceptanceFlowService.php`  
- `app/Services/Payment/OnlineTourCancelRefundService.php`  
- `app/Http/Controllers/BookingsController.php` (cancel / refunds)  
- `resources/views/bookings/refunds.blade.php`  
- `resources/views/bookings/partials/online-pay-first-card.blade.php`  
- `resources/views/layouts/sidebar.blade.php` (Finance / Wallet roles)  
- `config/finance.php`  

---

*End of document — use Module G smoke pack for every regression; run full suite before releases that touch wallet, payments, or cancel/refund.*
