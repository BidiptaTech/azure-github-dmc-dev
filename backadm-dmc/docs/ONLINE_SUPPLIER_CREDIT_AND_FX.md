# Online services — vendor credit, DMC currency preview, money rounding

## 1. Vendor credit (TravClicks ↔ API vendor)

Admin page: **Settings → Vendor Credit** (`/supplier-credits`, TravClicks admin only).

One account per **vendor code + environment** (`demo` = Sandbox, `live`), in the currency
that vendor settles in (USD, SGD, …). Tables:

| Table | Purpose |
|---|---|
| `supplier_credit_accounts` | Balance, totals (added / debited / refunded), low-balance alert, `enforce_limit` |
| `supplier_credit_transactions` | Append-only statement: one row per top-up, adjustment, booking debit, cancellation refund |

Every statement row stores: vendor amount + vendor currency, FX rate used (`countries.current_rate`,
SGD pivot), amount in the account currency, balance before/after, tour/order ids, vendor and agency
references, and a reconciliation status. Money is 2 decimals; FX rates keep 8.

### When the balance moves

| Event | Code path | Effect |
|---|---|---|
| Hotel approved (vendor booked) | `HotelBookingController` approve → `recordHotelBooking()` | − vendor **net** price (gross if net missing) |
| Hotel booked via API | `Api\OnlineHotelController::book` | same, `channel=api` |
| Attraction paid with vendor | `HotelBookingController` attraction approve, after `payOrder()` | − `requiredPaymentAmount()` |
| Hotel cancelled with vendor | `OnlineHotelCancellationService` | + debit − vendor cancellation charge (simulated cancels ignored) |
| Attraction voucher cancelled | `OnlineAttractionCancellationService`, attraction reject | + full debit, marked **needs review** (vendor returns no figure) |
| Admin | Vendor Credit page | top-up / manual adjustment (note required) |

- **Before** a vendor booking, `assertSufficient()` blocks it when the account is active, enforced and
  can't cover the cost. DMC users see a generic message (TravClicks' balance is never shown to them).
- If no account exists yet, the first transaction auto-creates one with **enforcement off**
  (`auto_created = true`) so nothing is lost and no booking is blocked. Set the credit and enable the limit.
- Debits/refunds are idempotent (`idempotency_key`), so retried approvals/cancels never double-post.
- Recording after the vendor confirmed never throws; failures are logged (`supplier_credit_*`).

Reconciliation against vendor statements is a later phase; columns `reconciliation_status`,
`reconciled_at`, `reconciled_by` are ready for it.

## 2. DMC currency in online price breakdowns

Every online price breakdown keeps the service currency (SGD) and, when the DMC's wallet currency
differs, adds **Exchange rate** and **Total in your currency**:
add-hotel modal, Lite hotel breakdown, Pro hotel breakdown, Lite attraction panel, Pro attraction
breakdown (online attractions). One JS helper does it: `window.StpDmcFx` (online-hotel-modal partial).

The rate shown **includes the currency conversion commission**, exactly like the wallet charge
(`FxConversionCharge`): effective rate = base × (1 + `PAYMENT_FX_CONVERSION_CHARGE_PERCENT` %),
6 dp; IDR amounts in whole rupiah, others 2 dp. Example: SGD 35.20 → 1 SGD = 14,443.30 IDR
(14,160.10 + 2%) → IDR 508,404 — the same figure the wallet reserves.

Not changed on purpose: commission **credits** to the DMC wallet use the base rate (a conversion
fee is a charge on the payer, not on money paid to the DMC); TravClicks ↔ vendor credit uses the base
rate (internal); offline quotation display conversions use their own CurrencyHelper path.

## 2a. Online payment hold (supplier_initial, wallet + Stripe)

1. **Pay** → full amount is held (Stripe manual-capture authorisation / wallet reservation). The tour
   is unlocked for booking as before; Payment Details shows the row with `payment_hold = held`.
2. **Book** → each online service approved with its vendor takes its share of the hold (split by
   selling price; shares always sum to the held amount). Wallet: that share is committed immediately.
   Stripe: one hold stays open (Stripe allows a single capture).
3. **Settle** when every service is booked or removed: Stripe captures the booked total **once**,
   wallet releases the rest; the payment row becomes the charged amount (`captured` /
   `partially_captured`), or declined (`released`) when nothing was booked. Commission is recorded for
   booked services only.
- `payments:settle-online-holds` runs every 15 min: picks up removals made on any screen and
  force-settles holds older than `PAYMENT_ONLINE_HOLD_STRIPE_MAX_DAYS` (6; Stripe holds expire ~7 days)
  or `PAYMENT_ONLINE_HOLD_WALLET_MAX_DAYS` (30). Kill-switch: `PAYMENT_ONLINE_HOLD_ENABLED=false`.
- Stripe authorisation is verified server-side (PaymentIntent must be `requires_capture` for the
  exact amount) — the browser is never trusted.
- Wallet fixes: release without an amount releases only **that** reservation (not every hold on the
  wallet); commit/release are idempotent; a commit can never exceed its reservation.

## 2b. Stripe Connect default currency

The connect page has a **Default currency** selector, pre-filled with the DMC's own currency and sent
as `defaults.currency`. Stripe only allows currencies supported for the business country (e.g. IDR is
not allowed for an SG account; Indonesian merchant accounts are not available to this platform) — the
page explains this. Charges can still be made in the tour currency (e.g. IDR); Stripe converts on payout.

## 3. Money rounding

Finance/payment services (`app/Services/Finance`, `app/Services/Payment`, `Country::convertAmount`)
round money to **2 decimals**. Existing ledger columns remain `numeric(18,4)`; historical rows were not
rewritten. Stripe amounts go through `PaymentGatewayService::toMinorUnits()` (handles zero-decimal
currencies such as JPY/KRW/VND).

## 4. Indexes / partitioning

`2026_11_18_100000_add_performance_indexes_to_orders_tours_payments` adds the missing indexes
(notably `orders.tour_id`) — applied.

**Month/year partitioning is implemented and tested, but opt-in** (`database/migrations_optional/`):
tours, orders and the 9 payment/ledger tables are range-partitioned on `created_at`, one partition per
month (`orders_p202611`) + `_pdefault`. Global uniqueness (`tour_id`, `booking_id`, idempotency keys,
UUIDs) is kept by `partition_unique_keys` + trigger (same SQLSTATE 23505); foreign keys into partitioned
tables become triggers (23503; `order_activity` keeps ON DELETE CASCADE). Originals are kept as
`<table>__prepart` for rollback. `partitions:ensure` (monthly) creates months ahead and moves stray
rows out of the default partition.

Measured on a full copy of the data before deciding:

| | plain tables | partitioned |
|---|---|---|
| New connection, first 4 queries (every PHP request) | ~55 ms | ~335 ms |
| `orders where tour_id = ?` (warm) | ~0.4 ms | ~2.6 ms |

The app looks rows up by `tour_id`/`id`, not by date, so pruning never applies and every query plans
over all monthly partitions. Enable only together with a connection pooler (PgBouncer) and once
tables are large enough for date-based maintenance to matter. Commands are in the migration header.
