# Bulk Upload vs Normal Create — Inconsistency Audit

**Date:** 19 Nov 2026  
**Scope:** Align bulk upload with the normal add/create flow for product master data only. No booking/enquiry/tour flows.  
**Primary code:** `app/Http/Controllers/BulkUploadController.php`  
**Status:** Backend P0/P1 done. **UI entry points restored** (sidebar + service CTAs + beds Bulk tab). Hotel parent ROOM_CATEGORIES cost columns still pending.

---

## 1. Executive summary

Bulk upload templates and writers were largely built before the **cost / sell pricing model** and several newer master fields landed on the normal create screens. As a result, records created via bulk often land in the DB with:

- **Sell prices only** (cost left `NULL` / `0`)
- **Missing identity / ops fields** (phone, remarks, attraction type, meal item name, etc.)
- **Incomplete ownership linkage** (`dmc_id` / `is_active` not always set the same way as normal create)

### Severity at a glance

| Service | Bulk maturity vs normal | Priority | Main gap theme |
|---|---|---|---|
| **Tickets** | **Done** | **P0** | Cost+sell (6 pairs) + remarks in both ticket upload paths |
| **Meals** (restaurant-scoped) | **Done** | **P0** | Item name + cost/sell; both paths share `processMealUpload` |
| **Attractions** (parent) | **Done** | **P1** | phone, type, remarks, transport prices, is_active (dmc_id not in CSV) |
| **Restaurants** (parent) | **Done** | **P1** | phone, email/password, remarks; restaurant-only CSV (no nested meals) |
| **Hotels** (parent multi-section CSV) | Partially behind | **P1** | Room category cost/profit fields still missing in parent template |
| **Vehicles** | Mostly done | **P2** | country + color added; hourly ladder / zone mappings still post-create |
| **Guides** | Largely aligned | **P3** | Minor polish only |
| **Drivers** | Largely aligned | **P3** | Minor / non-pricing gaps |
| **Hotel Beds** (hotel-scoped) | Aligned on cost | **P3** | Keep as reference implementation |
| **Hotel Seasons** (hotel-scoped) | Aligned on cost | **P3** | Keep as reference implementation |

**Recommended implementation order:** Tickets → Meals → Attractions → Restaurants → Hotel room section → Vehicles polish.

---

## 2. Architecture map (bulk feature only)

### Entry points

| UI / route pattern | Controller methods | Template download | Upload writer |
|---|---|---|---|
| `/bulk-upload/tickets` + attraction-scoped routes | `tickets`, `attractionTickets` | `downloadAttractionTicketTemplate` / `generateAttractionTicketCsvData` | `uploadAttractionTickets` |
| `/bulk-upload/meals` + restaurant-scoped routes | `meals`, `restaurantMeals` | `generateDmcMealTemplate` / `generateRestaurantMealCsvData` | `uploadMeals` → `processMealUpload` / `uploadRestaurantMeals` → `createMeal` |
| `/bulk-upload/attractions` | `attractions` | `downloadDmcAttractionTemplate` / Travclicks variant | `uploadAttractions` |
| `/bulk-upload/restaurants` | `restaurants` | `downloadVirtualDmcRestaurantTemplate` | `uploadRestaurants` → `uploadVirtualDmcRestaurants` |
| `/bulk-upload/hotels` | `hotels` | `generateHotelCsvData` | `uploadHotels` → `parseHotelBlocks` |
| Hotel beds scoped | `hotelBeds` | `downloadHotelBedTemplate` | `uploadHotelBeds` |
| Hotel seasons scoped | `hotelSeasons` | `downloadHotelSeasonTemplate` | `uploadHotelSeasons` |
| `/bulk-upload/vehicles` | `vehicles` | `downloadVehicleTemplate` | `uploadVehicles` → `uploadVehicleData` |
| `/bulk-upload/guides` | `guides` | `downloadDummyGuideTemplate` | `uploadGuides` → `uploadGuideData` |
| `/bulk-upload/drivers` | `drivers` | `downloadDmcDriverTemplate` | `uploadDrivers` → `uploadDriverData` |

Supporting UI: `resources/views/bulk-upload/*.blade.php`, `public/js/bulk-upload.js`, history via `UploadHistory`.

**UI restore (done):** Sidebar Bulk Upload menu uncommented; scoped CTAs restored/added on tickets, meals, beds (Bulk tab), restaurants, attractions, hotels, guides, drivers, vehicles. `rooms.blade.php` has no hotel context — room import remains on `create-room` via `rooms.import`. DMC role checks in `BulkUploadController` now use `(int) role_id !== 11` so int/string DB values both work.

### Important design note

There are **two parallel meal upload paths** (`uploadMeals` / `processMealUpload` vs `uploadRestaurantMeals`) and **two ticket paths** (legacy `uploadTickets` vs attraction-scoped `uploadAttractionTickets`). Templates differ slightly (e.g. Status column present only on restaurant-meal scoped template). Any fix must update **both** paths or consolidate them first.

---

## 3. Service-by-service field matrix

Legend for **Bulk status**:
- ✅ Present in template **and** persisted
- ⚠️ Present in UI helper only / partial / naming drift
- ❌ Missing from template and/or not persisted by bulk
- N/A Not applicable to that service

---

### 3.1 Tickets — **P0**

**Normal create:** `resources/views/tickets/add-ticket.blade.php` → `TicketController::store`  
**Bulk:** `generateAttractionTicketCsvData` + `uploadAttractionTickets` (~L4092–4354)

#### Current bulk CSV columns (10)

| # | Bulk column | DB column written |
|---|---|---|
| 1 | Ticket Name | `name` |
| 2–4 | Child/Adult/Senior Sell (local) | `child_price`, `adult_price`, `senior_adult_price` |
| 5–7 | Child/Adult/Senior Sell (foreigner) | `child_price_nri`, `adult_price_nri`, `senior_adult_price_nri` |
| 8 | Important Notes | `description` |
| 9 | Terms & Conditions | `terms_conditions` |
| 10 | Status | `status` |
| — | (implicit) | `attraction_id`, `dmc_id`, `created_by` |

#### Gap table

| Field (normal / DB) | Normal create | Bulk template | Bulk persist | Gap |
|---|---|---|---|---|
| `child_cost_price` | Required in UI | ❌ | ❌ | **Missing** |
| `adult_cost_price` | Required in UI | ❌ | ❌ | **Missing** |
| `senior_adult_cost_price` | Required in UI | ❌ | ❌ | **Missing** |
| `child_cost_price_nri` | Required in UI | ❌ | ❌ | **Missing** |
| `adult_cost_price_nri` | Required in UI | ❌ | ❌ | **Missing** |
| `senior_adult_cost_price_nri` | Required in UI | ❌ | ❌ | **Missing** |
| `remarks` | Optional | ❌ | ❌ | **Missing** |
| Sell prices (6) | Required | ✅ | ✅ | OK |
| `description` / notes | Required | ✅ | ✅ | OK |
| `terms_conditions` | Required | ✅ | ✅ | OK |
| `status` | Checkbox | ✅ | ✅ | OK |
| `profit_type` / `profit_on_cost` | UI calculator only | ❌ | ❌ | **Not DB columns** — do **not** add to CSV; sell is derived client-side on normal form |

#### DB truth (`tickets` table)

Has cost columns:
`child_cost_price`, `adult_cost_price`, `senior_adult_cost_price`, `child_cost_price_nri`, `adult_cost_price_nri`, `senior_adult_cost_price_nri`

`Ticket::$fillable` is stale (omits cost columns) but normal store uses direct property assignment, so fillable is not the blocker for bulk either.

#### Behaviour / validation mismatches

| Topic | Normal | Bulk |
|---|---|---|
| Cost required? | UI marks cost required; controller validates nullable | No cost at all |
| Sell required? | Adult required; others nullable in controller | All 6 sells required and must be `> 0` (stricter) |
| Remarks | Saved | Never set |
| Zero prices | Often allowed by backend | Rejected |

#### Proposed bulk column set (target — match normal)

```
Ticket Name*
Child Cost (local)*, Child Sell (local)*
Adult Cost (local)*, Adult Sell (local)*
Senior Cost (local)*, Senior Sell (local)*
Child Cost (foreigner)*, Child Sell (foreigner)*
Adult Cost (foreigner)*, Adult Sell (foreigner)*
Senior Cost (foreigner)*, Senior Sell (foreigner)*
Important Notes*
Remarks (optional)
Terms & Conditions*
Status (1/0)
```

---

### 3.2 Meals — **P0**

**Normal create:** `resources/views/meals/create-meals.blade.php` → `MealController::store`  
**Bulk:** `generateDmcMealTemplate` / `generateRestaurantMealCsvData` + `createMeal` / `uploadRestaurantMeals` (~L1820–1875, L4494+, L5022+, L5402+)

#### Current bulk CSV columns

DMC template (8 cols): Meal Type*, Beverage*, Meals*, Item Price, Item Type, Adult Price, Child Price, Item Description*

Restaurant-scoped template (9 cols): same + Status

#### Gap table

| Field (normal / DB) | Normal create | Bulk template | Bulk persist | Gap |
|---|---|---|---|---|
| `name` (Item Name) | Required in UI | ❌ | Hardcoded `'Menu Item'` | **Critical** |
| `item_cost_price` | Yes (Set Menu) | ❌ | ❌ | **Missing** |
| `adult_cost_price` | Yes (Buffet) | ❌ | ❌ | **Missing** |
| `child_cost_price` | Yes (Buffet) | ❌ | ❌ | **Missing** |
| Sell `price` / `adult_price` / `child_price` | Yes | ✅ (type-dependent) | ✅ | OK |
| `meal_period` / `category` / `type` / `item_type` | Yes | ✅ | ✅ | OK |
| `item_description` | Required | ✅ | ✅ | OK |
| `is_active` / status | Required on | ⚠️ only on one template | Partial | Align both paths |
| `files` (image) | Optional upload | ❌ | ❌ | Accept URL later or leave manual |
| `profit_type` / `profit_on_cost` | UI calculator | ❌ | ❌ | Not DB — skip CSV |

#### Behaviour mismatches

| Topic | Normal | Bulk |
|---|---|---|
| Item name | User-entered | Always `"Menu Item"` |
| Cost prices | Persisted | Never written |
| Buffet vs Set Menu rules | Toggle fields in UI | Enforced in CSV validation (good) |
| Dual upload endpoints | One store | Two writers with slight template drift |

#### Proposed bulk column set (target)

```
Meal Type* (Breakfast/Lunch/Dinner)
Beverage* (Alcoholic/Non Alcoholic/No Beverage)
Meals* (Buffet/Set Menu)
Item Name*
Item Cost Price          // required for Set Menu
Item Sell Price          // required for Set Menu
Item Type                // required for Set Menu
Adult Cost Price         // required for Buffet
Adult Sell Price         // required for Buffet
Child Cost Price         // required for Buffet
Child Sell Price         // required for Buffet
Item Description*
Status (1/0)*
```

---

### 3.3 Attractions (parent) — **P1**

**Normal create:** `resources/views/attractions/create-attraction.blade.php` → `AttractionController::store`  
**Bulk:** `downloadDmcAttractionTemplate` + `uploadAttractions` (~L2394–2448, L3620–3732)

#### Current bulk columns (18)

Name, Country, City, Senior Age, Max Child Age, Lat/Lng, Morning/Afternoon/Evening/Night Opening, Open/Close Time, Master/Additional Images, Description(notes), Terms, Status

#### Gap table

| Field | Normal | Bulk template | Bulk persist | Gap |
|---|---|---|---|---|
| `phone` | Required | ❌ | ❌ | **Missing** |
| `attraction_type` | Required | ❌ | ❌ | **Missing** |
| `remarks` | Optional | ❌ | ❌ | **Missing** |
| `price_shared` | Yes | ❌ | ❌ | **Missing** |
| `price_private` | Yes | ❌ | ❌ | **Missing** |
| `is_active` | From status checkbox | ❌ (only `status`) | ❌ | **Missing / inconsistent** |
| `dmc_id` | **Not written on create** (assignment is a later DMC-select flow via `addDmcId`) | N/A — never from Excel | Same as normal (left null) | **Not a gap** — resolve from auth/selection backend, never from CSV |
| `created_by` | Auth user | N/A | ✅ `$auth_user->userId` | OK |
| Age / times / images / notes / terms | Yes | ✅ | ✅ | OK |
| Legacy form prices `adult_price`/`child_price`/`senior_adult_price` | Forced to `0` in normal store | N/A | N/A | Intentionally unused for tickets model — **do not revive in bulk** |

#### Notes

- Normal create still shows child/adult/senior price inputs, but controller hard-sets them to `0` and real ticket pricing lives on **Tickets**. Bulk correctly omits those — keep that way.
- In `AttractionController::store`, `$dmc_id` is computed from the auth user/role, but `$attraction->dmc_id = …` is **commented out**. Ownership uses JSON `dmc_id` via later `addDmcId()` selection. Bulk must **not** add a `dmc_id` Excel column.
- Bulk still needs **phone**, **attraction_type**, **remarks**, **transport prices**, and **`is_active`** (alongside existing `status`) to match normal create.

---

### 3.4 Restaurants (parent) — **P1**

**Normal create:** `resources/views/restaurants/add-restaurant.blade.php` → `RestaurantController::store`  
**Bulk:** `downloadVirtualDmcRestaurantTemplate` + `uploadVirtualDmcRestaurants` (~L994–1020, L1541–1691)

#### Gap table

| Field | Normal | Bulk template | Bulk persist | Gap |
|---|---|---|---|---|
| `phone` | Required | ❌ | ❌ | **Missing** |
| `email` (`restaurant_email`) | Required | ❌ | ❌ | **Missing** |
| `password` | Required | ❌ | ❌ | **Missing** (security: optional in bulk or generate + force reset) |
| `remarks` | Optional | ❌ | ❌ | **Missing** |
| `dmc_id` | **Not written on create** (later DMC-select via `addDmcId`) | N/A — never from Excel | Same as normal (left null); only `created_by` from auth | **Not a gap** — never from CSV |
| `created_by` | Auth user | N/A | ✅ `$auth_user->userId` | OK |
| Name/cuisine/geo/meal windows/images/description/terms/status | Yes | ✅ | ✅ | OK |
| Nested meal columns in restaurant template header mismatch | — | Header lists restaurant cols only; sample rows append meal cols without matching headers | Fragile | Fix header/schema or drop nested meals from restaurant parent CSV |

#### Recommendation

Keep restaurant parent CSV for **restaurant master only**. Meals should stay on restaurant-scoped meal bulk (after meal P0 fix). Nested meal columns in restaurant template currently look broken/header-mismatched.

Bulk restaurant upload is Virtual DMC–only (`role_id = 20`). Same as attractions: do **not** introduce a `dmc_id` Excel column; ownership stays backend-driven.

---

### 3.5 Hotels (parent multi-section CSV) — **P1**

**Bulk template header** (`generateHotelCsvData` ~L209–216) includes room sell prices and meal sell prices, plus bed mapping cost for extra bed / babycot.

#### Gap vs normal room create (`hotel/create-room.blade.php`)

| Area | Normal room create | Hotel bulk ROOM_CATEGORIES section |
|---|---|---|
| Weekday/weekend single & double **sell** | Yes | ✅ present |
| Weekday/weekend single & double **cost** | Yes (`weekday_cost_price`, etc.) | ❌ missing |
| Meal sell prices | Yes | ✅ present |
| Meal **cost** prices | Yes (`breakfast_cost_price`, …) | ❌ missing |
| `profit_type` / `profit_amount` | Yes (persisted on `rooms`) | ❌ missing |
| Child with/without bed cost | Yes | ❌ missing |
| Extra bed / babycot cost on mappings | Yes | ✅ present in mapping section |

**Scoped hotel beds & seasons bulk are already cost-aware** — treat them as the pattern to copy for tickets/meals/rooms.

---

### 3.6 Hotel Beds (scoped) — **P3 / reference**

Template already has Extra Bed / Baby Cot **Sell + Cost**. Upload persists both.  
**Action:** Use as reference; only fix residual mismatches if found vs `hotel/beds` UI (force child rules already present).

---

### 3.7 Hotel Seasons (scoped) — **P3 / reference**

Template already has Single/Double weekday/weekend sell+cost and meal sell+cost. Upload persists into `rates`.  
**Action:** Keep as reference.

---

### 3.8 Vehicles — **P2**

**Bulk already includes** paired sell/cost for base, per-km tiers, per-hour, cancel, and night variants — good.

#### Remaining gaps vs `vehicles/add-vehicle.blade.php`

| Field / feature | Normal | Bulk | Gap |
|---|---|---|---|
| `country` | Required | ❌ | Missing |
| `vehicle_color` | Required | ❌ | Missing |
| `hourly_price_1` … `hourly_price_12` | Present | ❌ | Missing ladder |
| Zone mappings + `private_cost_price` / `shared_cost_price` / profit fields | Separate post-create flow | ❌ | Out of scope for single-row CSV unless dedicated section |
| `cancellation_sell` vs `cancel_cost` naming | Form uses `cancellation_sell` + `cancellation_cost` | Bulk uses Cancel Sell/Cost → `cancel_cost` / `cancel_cost_price` | Naming / column dual-write risk — verify VehicleController store mapping and mirror exactly |
| Sharable-specific cost columns (`sharable_*_cost_*`) | Used when sharable | Bulk writes into non-sharable columns then relies on `sharable` flag | Confirm writer branches correctly for Private/Sharable/Both |

---

### 3.9 Guides — **P3**

Bulk template already includes Minimum/Hourly/2–12hr **Sell + Cost**, night surcharge/times, languages. Upload persists cost fields (~L2053–2070).  
**Likely residual:** WhatsApp number, close days/dates, approval flags — confirm against create-guide if product wants parity; not part of recent cost-price rollout.

---

### 3.10 Drivers — **P3**

Bulk covers identity/license/geo/status. No cost model on drivers table.  
**Likely residual:** bank fields, WhatsApp, operational city IDs, vehicle link — only if product requires parity.

---

## 4. Cross-cutting inconsistencies

1. **Cost/Sell dual pricing** was added to normal UIs for tickets, meals, rooms, vehicles, guides, beds, seasons. Bulk was updated for guides/vehicles/beds/seasons but **not** tickets/meals/hotel-room section.
2. **`profit_type` / `profit_on_cost`** on ticket & meal forms are **client-side calculators only** (no DB columns). Bulk should persist **final cost + sell**, not profit helpers.
3. **`dmc_id` for attractions/restaurants** is intentionally **not** set at create time in normal or bulk (assignment is a later DMC-select / `addDmcId` flow). Do **not** put `dmc_id` in Excel. Still fix attraction **`is_active`** (bulk currently writes `status` only).
4. **Dual endpoints** for meals/tickets risk fixing one path and leaving the other stale.
5. **Validation strictness** differs (bulk often rejects zeros; normal allows nullable/zero for some prices).
6. **`Ticket::$fillable`** omits cost columns — harmless with direct assignment, but should be cleaned when implementing ticket bulk to avoid future mass-assignment bugs.
7. Restaurant parent CSV appears to **append meal cells without matching headers** when exporting existing restaurants — fragile for users.

---

## 5. Proposed implementation plan (next phase — do not start yet)

### Phase A — P0 (must ship together)

1. **Tickets**
   - Extend attraction ticket CSV template with 6 cost columns + optional remarks.
   - Update `uploadAttractionTickets` (and legacy `uploadTickets` if still reachable) to persist costs/remarks.
   - Align validation with `TicketController::store` (required vs nullable) after product confirmation.
   - Update `Ticket::$fillable` / casts for cost fields.
2. **Meals**
   - Add Item Name + cost columns to both meal templates.
   - Update `createMeal` + `uploadRestaurantMeals` writer.
   - Stop hardcoding `name = 'Menu Item'`.
   - Unify the two meal upload paths’ column contracts.

### Phase B — P1

3. **Attractions:** add phone, attraction_type, remarks, price_shared, price_private; set `is_active` from status (keep `dmc_id` out of CSV — backend/auth + later selection only).
4. **Restaurants:** add phone, remarks; decide email/password strategy for bulk; keep `dmc_id` out of CSV; simplify CSV to restaurant-only.
5. **Hotel parent ROOM_CATEGORIES:** add room + meal cost columns and profit fields to match room create.

### Phase C — P2/P3

6. **Vehicles:** country, color, hourly ladder; verify cancel-sell column mapping; document zone mapping as separate post-upload step (or new CSV section).
7. Guides/Drivers polish only if product requests full field parity.
8. Regression: download template → fill sample → upload → compare DB row to an equivalent normal-create row.

### Acceptance criteria (per service)

For a service marked done:

- Template columns cover every **persisted** field from normal create (excluding file-upload UX that cannot map to CSV, unless URL-based).
- Upload writer writes the same DB columns as normal store.
- A bulk-created record is usable in booking/pricing flows without manual edit to fill costs.
- Both duplicate routes/writers (if any) share one column contract.

---

## 6. Out of scope (explicit)

- Booking / enquiry / tour package flows
- Online supplier hotels/attractions
- Changing normal create UI (except fixing stale model fillable if needed for bulk)
- Adding DB columns that do not already exist
- Implementing zone-mapping grids inside vehicle CSV unless product explicitly requests it later

---

## 7. Open questions for product / BA before coding

1. For tickets/meals: should cost columns be **required** in bulk (matching UI) or optional with default `0`?
2. For restaurants bulk: should email/password be required, auto-generated, or omitted (login created later)?
3. ~~Should attraction/restaurant bulk set `dmc_id`…?~~ **Resolved:** No Excel column. Match normal create — leave `dmc_id` null at create; ownership via auth/`created_by` + later DMC selection.
4. Confirm hotel **parent** multi-section CSV is still used in production, or only scoped beds/seasons bulk.
5. Vehicle zone mappings: keep as post-create UI only, or add a second CSV section?

---

## 8. File index for implementers

| Concern | Path |
|---|---|
| Bulk controller | `app/Http/Controllers/BulkUploadController.php` |
| Ticket normal store | `app/Http/Controllers/TicketController.php` |
| Ticket create UI | `resources/views/tickets/add-ticket.blade.php` |
| Meal normal store | `app/Http/Controllers/MealController.php` |
| Meal create UI | `resources/views/meals/create-meals.blade.php` |
| Attraction normal store | `app/Http/Controllers/AttractionController.php` |
| Attraction create UI | `resources/views/attractions/create-attraction.blade.php` |
| Restaurant normal store | `app/Http/Controllers/RestaurantController.php` |
| Restaurant create UI | `resources/views/restaurants/add-restaurant.blade.php` |
| Bulk views | `resources/views/bulk-upload/*.blade.php` |
| Routes | `routes/web.php` (`bulk-upload.*`, scoped meal/ticket/bed/season routes) |

---

**End of audit.** Ready for implementation kickoff starting with **Tickets + Meals (P0)** once the open questions above are answered.
