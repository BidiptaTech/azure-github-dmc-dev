# DMC Invoice & DMC Offer — Pricing Strategy

> Spec for Travclicks-level base invoice prices and DMC-scoped time-bound offer prices.  
> Sidebar: **General Settings → DMC Invoice** and **General Settings → DMC Offer**.  
> Read this before creating migrations, models, routes, or UI.

---

## Business goal (clarified)

Two related but different pricing layers:

| Layer | Menu tab | Table | Scope | Currency | Purpose |
|--------|----------|--------|--------|----------|---------|
| **Base invoice prices** | DMC Invoice | `dmc_invoice` | Travclicks / platform level | Always **SGD** | Default adult / child / infant rates used for Travclicks-level invoice pricing |
| **Promotional offer prices** | DMC Offer | `dmc_offer` | Per DMC + date range | Always **SGD** (Travclicks stores these prices) | Override / special rates for a chosen DMC between `start_date` and `end_date` |

### Intended runtime rule (proposed)

When pricing is resolved for a DMC on a given date:

1. Look for an **active** `dmc_offer` row where  
   `dmc_id = users.userId` **and**  
   `start_date <= booking_date <= end_date`.
2. If found → use offer `adult_price` / `child_price` / `infant_price`.
3. If not found → fall back to Travclicks-level `dmc_invoice` prices (SGD).

> Confirm with product: if multiple offers overlap for the same DMC + date, which wins?  
> Recommendation: **reject overlapping ranges on save**, or take the **latest created** / **lowest price** (pick one rule and enforce it).

---

## Sidebar placement

Under existing **Setting → General Settings** (near Itinerary / Quotation Settings):

- **DMC Invoice** — manage Travclicks base prices
- **DMC Offer** — pick a DMC from listing, set date range + prices

Suggested access (to confirm):

| Tab | Who can see / edit |
|-----|--------------------|
| DMC Invoice | Admin (`role_id = 1`) — platform-level only |
| DMC Offer | Admin (`role_id = 1`); optionally DMC (`11`, `138`) for their own rows only |

---

## Table 1 — `dmc_invoice` (Travclicks base prices)

### Strategy

- Stores **platform / Travclicks-level** adult, child, infant prices.
- **Currency is always SGD** (fixed; do not let UI pick another currency). Travclicks stores and compares at this level in SGD.
- Not tied to a specific DMC user (no `dmc_id` on this table).

### Proposed columns

| Column | Type | Notes |
|--------|------|--------|
| `id` | BIGINT PK (auto) | Primary key |
| `dmc_invoice_id` | VARCHAR / BIGINT UNIQUE nullable | **Clarify meaning** (see Open questions). If only one global price set is needed, this column may be unnecessary |
| `adult_price` | DECIMAL(12,2) | Required |
| `child_price` | DECIMAL(12,2) | Required |
| `infant_price` | DECIMAL(12,2) | Required |
| `currency` | CHAR(3) default `'SGD'` | Always store `SGD`; enforce in app + DB default |
| `created_at` / `updated_at` | timestamps | Standard Laravel |
| `created_by` | BIGINT nullable → `users.userId` | Optional audit |

### UI behaviour

- Form: Adult / Child / Infant price fields + read-only currency label **SGD**.
- Likely **single active row** (edit in place) or a small history list — confirm which.

---

## Table 2 — `dmc_offer` (per-DMC dated offers)

### Strategy

1. Admin (or allowed role) opens **DMC Offer**.
2. **Choose a DMC** from the DMC user listing (`users` where `role_id` in DMC roles, e.g. `11`, and any multi-role that acts as DMC such as `138`).
3. Set **start_date** and **end_date**.
4. Enter **adult_price**, **child_price**, **infant_price**.
5. On save, store that DMC’s `users.userId` into column **`dmc_id`** (same convention as `invoice_templates.dmc_id` → `users.userId`).
6. **Currency is always SGD** — Travclicks puts/stores these offer prices in SGD. Do not allow another currency in UI or DB.

### Proposed columns

| Column | Type | Notes |
|--------|------|--------|
| `id` | BIGINT PK (auto) | Primary key |
| `dmc_offer_id` | VARCHAR / BIGINT UNIQUE nullable | **Clarify meaning** (see Open questions) |
| `dmc_id` | BIGINT NOT NULL | FK → `users.userId` (the chosen DMC) |
| `start_date` | DATE NOT NULL | Inclusive |
| `end_date` | DATE NOT NULL | Inclusive; must be `>= start_date` |
| `adult_price` | DECIMAL(12,2) | Required; stored in SGD |
| `child_price` | DECIMAL(12,2) | Required; stored in SGD |
| `infant_price` | DECIMAL(12,2) | Required; stored in SGD |
| `currency` | CHAR(3) default `'SGD'` | Always store `SGD`; enforce in app + DB default (Travclicks) |
| `created_at` / `updated_at` | timestamps | Standard Laravel |
| `created_by` | BIGINT nullable → `users.userId` | Optional audit |

### Indexes / constraints (recommended)

- Index: `(dmc_id, start_date, end_date)`
- App validation: no overlapping date ranges for the same `dmc_id`
- Check: `end_date >= start_date`

### UI behaviour

- Dropdown / searchable list of DMCs → selected value = `users.userId` → saved as `dmc_id`
- Date range picker + three price fields + read-only currency label **SGD**
- List view: DMC name, date range, prices (SGD), actions (edit / delete)

---

## Relationship diagram

```text
users (DMC)
  userId  <──────────────────────────── dmc_offer.dmc_id
  role_id in (11, 138, …)

dmc_invoice   (Travclicks / global base prices, currency = SGD)
  id
  adult_price / child_price / infant_price
  currency = 'SGD'

dmc_offer     (time-bound override per DMC, currency = SGD)
  id
  dmc_id → users.userId
  start_date / end_date
  adult_price / child_price / infant_price
  currency = 'SGD'
```

---

## Alignment with existing codebase

- DMC ownership already uses **`users.userId`** as `dmc_id` (see `invoice_templates`, `quotation_templates`).
- General Settings menu lives in `resources/views/layouts/sidebar.blade.php` (Setting block ~1589–1716).
- New menu items should sit with other General Settings children and follow the same `Request::is(...)` active/open patterns.

---

## Open questions (need confirmation before build)

1. **`dmc_invoice_id` / `dmc_offer_id`**  
   - External / business codes?  
   - Or redundant with auto `id`?  
   - Recommendation: use only `id` as PK unless you have a real external code; drop the extra `*_id` columns if not needed.

2. **`dmc_invoice` cardinality**  
   - One global Travclicks row only?  
   - Or multiple historical versions?

3. **Does `dmc_invoice` need `dmc_id`?**  
   - Current strategy: **No** (Travclicks-level).  
   - If each DMC should have its own base invoice prices, add `dmc_id` and rename the mental model to “DMC base invoice” instead of Travclicks global.

4. **Who can manage offers?**  
   - Admin only, or DMC can manage own offers?

5. **Where are these prices consumed?**  
   - Invoice PDF? Quotation? Enquiry Pro? Travclicks sync API?  
   - Document the consumer next so routes/models can match.

### Confirmed rules

- **`dmc_invoice.currency`** = always **SGD** (Travclicks-level store).
- **`dmc_offer.currency`** = always **SGD** (Travclicks puts/stores these prices in SGD).

---

## Implementation checklist

- [x] Migration: `2026_11_12_170000_create_dmc_invoice_table`
- [x] Migration: `2026_11_12_170100_create_dmc_offer_table`
- [x] Models: `DmcInvoice`, `DmcOffer` (+ relations / helpers)
- [x] Controllers + routes: `/dmc-invoice`, `/dmc-offer` (Admin `role_id = 1`)
- [x] Blade list/create/edit views
- [x] Sidebar links under General Settings: **DMC Invoice**, **DMC Offer**
- [x] Validation: SGD lock, date range, overlap reject on offers
- [x] Wire consumer: **Travclicks Invoice** (DMC portal monthly PDF)
  - Menu: General Settings → Travclicks Invoice (`/travclicks-invoice`)
  - Tours: `tour_status = Actual`, `check_out_time` in selected month and `< now`
  - Line = pax × `dmc_invoice` SGD rates; offer on `check_in_time` → discount badge + discount amount
  - Tax from active DMC `taxes` when enabled; grand total SGD + DMC currency conversion when different

### Implementation notes (defaults chosen)

- `dmc_invoice_id` / `dmc_offer_id` auto-set to row `id` after create.
- Admin-only access (same as DMC Func App).
- Offer DMC picker includes **only** `role_id` **11** (true DMC). Role **138** multi-role users share the same `company_name` and were causing duplicate labels in the dropdown.
- **One** `dmc_invoice` row only (Travclicks single price set); add form hidden after first save — edit/delete only.
- **One** `dmc_offer` per `dmc_id` (unique index); DMCs that already have an offer are removed from the add dropdown.

---

## Summary in one sentence

**`dmc_invoice` = Travclicks global adult/child/infant prices always in SGD; `dmc_offer` = same SGD price fields for a selected DMC (`dmc_id` = `users.userId`) valid only between `start_date` and `end_date`, used as a dated override when present — both tables store SGD because Travclicks uses that currency.**
