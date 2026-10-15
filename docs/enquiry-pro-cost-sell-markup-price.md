# Enquiry Pro — Cost / Sell / Markup Price Display

> Spec for Pro create + edit templates. Read this before changing pricing UI or store logic.

## Business goal

Users must see **three clear money layers** (real-world DMC flow):

| Field | Meaning | Editable? |
|--------|---------|-----------|
| **Cost** | Supplier / net cost (no DMC markup) | Usually readonly on listing |
| **Sell** | Actual sell **before** DMC profile markup | Yes (where already editable) |
| **Markup Price** | Sell **after** DMC hotel/other markup | Readonly (derived) |

Today Sell often shows a value that already includes DMC markup. That is confusing. Split them.

---

## Markup source

- Profile: DMC `markup_json` (same as Lite `StpLiteDmcMarkup`)
- **Hotel** services → `enquiryProApplyDmcMarkup(base, 'hotel')`
- **Other** (attraction, restaurant, transfer, guide, misc, A/D) → `kind: 'other'`

Example (hotel 5%):

```text
Sell (base)     = 2650.00
Markup amount   = 2650 × 5% = 132.50
Markup Price    = 2650 + 132.50 = 2782.50
```

---

## Hotel listing (Accommodation table)

Columns after Rooms:

`Avg Cost` | `Sell` | `Markup Price` | Extra Bed | CWB | CNB | Infant | …

- **Avg Cost** = per-night cost (unchanged)
- **Sell** = per-night **base** sell (no DMC markup)
- **Markup Price** = `applyDmcMarkup(Sell, 'hotel').price` (readonly)

On change of Sell → refresh Markup Price + footer.

---

## Footer: Single / Twin / Triple / Child / Infant

Each occupancy group shows **three** sub-columns:

`Cost` | `Sell` | `Markup`

### Hotel stay totals (per room line)

```text
nights = hotel.nights
rooms  = hotel.rooms (default 1)

stayCost       = AvgCost × nights × rooms
staySell       = Sell × nights × rooms          // BASE — no DMC markup
stayMarkup     = MarkupPrice × nights × rooms   // = apply(Sell,'hotel').price × nights × rooms
               // equivalent: apply(staySell, 'hotel').price when markup is % 
```

### Occupancy split (same as today, applied to each layer)

```text
Single  Cost/Sell/Markup  = stayX / 1
Twin    Cost/Sell/Markup  = stayX / 2
Triple  Cost/Sell/Markup  = (stayX + extraBedStay) / 3   // extra bed only if enabled
```

Example (Sell 2650, 5% hotel, 4 nights, 1 room, no extra bed):

| | Cost | Sell (base) | Markup Price |
|--|------|-------------|--------------|
| Single | 2590×4 = **10360** | 2650×4 = **10600** | 2782.50×4 = **11130** |
| Twin | 10360/2 = **5180** | 10600/2 = **5300** | 11130/2 = **5565** |
| Triple | 10360/3 → ceil **3454** | 10600/3 → ceil | 11130/3 → ceil **3711** |

Other non-supplement services (tours, transfers, guides, local transfers) still add into the same footer row on Cost/Sell as today; their **markup layer** uses `other` DMC markup on that service’s sell contribution.

Package Total (no hotel / all hotels supplement): Adult & Child also get `Cost | Sell | Markup`.

---

## Other service listings

Wherever a sell (or sell/pax) column exists, add **Markup Price** beside it:

- Attraction / Tour
- Restaurant / Meal
- Arrival / Departure
- Local Transfer
- Guide
- Misc

Rules:

- Sell / Sell per pax = **base**
- Markup Price = `applyDmcMarkup(baseLineOrPerPax, 'other').price`
- Cost unchanged

---

## Create vs Edit

| Surface | Behaviour |
|---------|-----------|
| **Create** | After add, listing shows Cost / Sell / Markup Price; footer uses base sell for Sell cols and marked for Markup cols |
| **Edit** | Hydrate `hotel.sell` / service sell as **base** (prefer `base_grand_total` / `dmc_profile_markup.base_price` when JSON was stored marked). Same three columns. |

Helpers (shared):

- `enquiryProApplyDmcMarkup(base, kind)` — already exists
- `enquiryProSplitDmcMarkupFromFinal(final, kind)` — peel marked → base (Lite `splitFromFinal`)
- `enquiryProMarkupPriceFromSell(sell, kind)` → `{ base, markup_amount, markup_price, applied }`
- `enquiryProResolveDisplayBaseSell(value, kind, storedMeta?)` — use stored `base_grand_total` only when value is empty / equals that base / equals marked final; never replace a smaller per-night hotel Sell with stay grand total

---

## Store / order JSON (do not break)

Store behaviour stays Lite-aligned:

- Persist **marked** commercial total on `totalPrice` / `grand_total` / nested transfer+guide sell
- Persist **base** on `base_grand_total` and `dmc_profile_markup.base_price`
- Listing **Sell** always edits/displays base; Markup Price is display-only

Do **not** double-apply markup on save: stamp helpers must use base (`base_grand_total` / unresolved sell).

---

## UI notes

- Markup Price cells: readonly, light highlight (e.g. soft violet / green) so they read as “after markup”
- Tooltips: `Sell = price before DMC markup` / `Markup Price = sell after DMC profile markup`
- Keep column order: Cost → Sell → Markup (never Markup before Sell)
- Round footer Cost with existing `roundToNextZero`; Sell/Markup use same rounding as current Sell

---

## Implementation checklist

1. [x] Add helpers in `dmc-price-breakdown-scripts.blade.php` (split + display pair + `enquiryProMarkupPriceTdHtml`)
2. [x] Accommodation table header + row: Markup Price (create + edit)
3. [x] `recalculateTotals` footer: colspan 3 + Markup cells (create + edit)
4. [x] Other service table headers/rows: Markup Price (create + edit) — tour, meal, transfer, guide, misc
5. [x] Edit hydrate: base sell from `base_grand_total` / peel (`enquiryProResolveDisplayBaseSell`)
6. [ ] Verify store still stamps markup once; View details / booking cards still use marked totals

### Notes for next work

- Arrival/Departure listing has no Cost/Sell columns today — Markup not added there; totals still flow via transfer/guide + price-break modal.
- Per-pax Sell (tour/meal/misc) applies `other` markup on the per-pax sell only (ticket/meal unit), not on combined transfer+guide grand total (that remains in price-break / store stamp).
- When editing Guide Sell on listing, keep `base_grand_total` in sync so Markup Price refreshes correctly.
- `applyMarkupDiscount()` footer column indexes (after Cost|Sell|Markup): Sell `[2,5,8,11,14,17]`, Markup `[3,6,9,12,15,18]`. Sell stays **base**; DMC hotel/other markup is applied only to Markup Price cells (do not double-apply onto Sell).
- **Miscellaneous:** listing shows Markup per Sell/Pax; View details uses `buildMiscBreakdown`; store stamps `totalPrice` + `adultSellMarked`/`childSellMarked`/`infantSellMarked` via `enquiryProAttachOtherServiceMarkup` (unit `adultSell` stays base). Bookings modal prefers marked `totalPrice` / `dmc_profile_markup`.
- **Edit hydrate (attraction / restaurant):** JSON stores marked `guide_options.sell` / `transfer_options.lineSell` plus `base_grand_total`. Listing + View details must use **base** (`base_grand_total` / `dmc_profile_markup.base_price`) via `extractGuideCostSell`, `enquiryProResolveGuideSellBase`, `enquiryProResolveTransferSellBase` — never mark again on already-marked line items.

---

## Out of scope (unless requested)

- Changing invoice/PDF layouts
- Changing Lite package UI
- Changing DMC markup admin settings UI
