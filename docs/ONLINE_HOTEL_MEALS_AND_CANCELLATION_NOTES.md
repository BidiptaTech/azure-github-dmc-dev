# Online Hotel — Dynamic Meals & Supplier Cancellation

**Date:** 15 October 2026  
**Scope:** MG Bedbank (primary); architecture left open for Hotelbeds / Tiniva  
**Status:** Implemented and verified in local environment

---

## 1. Summary of today’s work

| Area | What changed |
|------|----------------|
| **Meal Plan UI** | Meal Plan dropdown is no longer hard-coded. It is built from the selected hotel’s live room rates (room + meal + price rows). Changing meal rebinds the rate and updates displayed price / booking payload. |
| **MG CancelReservation** | Confirmed online MG hotels are cancelled via `/Hotel/CancelReservation` when removed. Raw cancel request/response stored on `orders.booking_details`. |
| **Flexible cancel architecture** | `OnlineHotelCancellable` + factory `makeCancellable()` so Hotelbeds/Tiniva can be wired later without changing the remove UI. |
| **Lite edit-save bugfix** | Edit save was soft-deleting via `clearTourServices` **without** calling cancel. Fixed: cancel removed hotels; preserve kept hotels’ orders/`booking_details`. |
| **API messages** | User-facing cancel errors do not expose supplier product names (`mg_bedbank`, `hotelbeds`, etc.). Details go to logs only. |

---

## 2. Dynamic Meal Plan (selected hotel)

### 2.1 Problem

Meal Plan was a static list (Room Only, Breakfast, etc.). Price only followed the **Room Type** row. On MG, the same room code often has multiple meal plans with different net prices (`roomDetails` = room + meal + price). Selecting Meal alone did not switch rate.

### 2.2 Behaviour now

**File:** `resources/views/single-tour-package/partials/online-hotel-modal.blade.php`

1. **Room Type** — unique rooms grouped by `room_id` (fallback: room name).
2. **Meal Plan** — options for that group only; each option’s `value` = index into `onlineCurrentRooms`.
3. On meal change → `applySelectedRoomDetails(room)` → price, beds, cancellation policy, `onlineSelectedRoom` for booking.
4. On Add → `mealPlan` / `roomType` taken from the selected room object (mapped labels), **not** from the raw select value (index).

### 2.3 Key helpers

| Helper | Role |
|--------|------|
| `roomGroupKey(room)` | Group by `room_id` / `roomId` |
| `roomGroupLabel(room)` | Display name for Room Type |
| `roomMealOptionLabel(room)` | Supplier meal name (e.g. Breakfast / BDBF) |
| `populateOnlineMealsForGroup(groupKey, preferredIdx)` | Rebuild Meal Plan select |
| `applySelectedRoomDetails(room)` | Sync price + policy + selection |

### 2.4 Empty / loading states

- Meal select starts disabled with placeholder `Select Meal Plan`.
- Cleared when hotel cleared or no rooms.
- Add button requires `onlineSelectedRoom` when rooms exist (including non–two-step / SearchHotel-first MG).

---

## 3. Online hotel cancellation architecture

### 3.1 Design goals

1. Cancel **only** when the hotel was successfully booked with the supplier (`orders.booking_details` populated with a real confirmation).
2. If `booking_details` is empty → enquiry only → soft-delete locally, **no** third-party call.
3. Supplier cancel **before** soft-delete; on API failure → **block** remove (422) so a live reservation is not orphaned.
4. Store full cancel payload/response on the same order’s `booking_details`.
5. Do not show supplier API names in UI/API error messages.
6. Add Hotelbeds / Tiniva later by implementing one interface + factory registration.

### 3.2 Layer diagram

```
┌─────────────────────────────────────────────────────────────────┐
│  UI                                                              │
│  A) Classic remove: removeHotelService → POST api.orders.cancel  │
│  B) Lite edit save: clearAllServices → clearTourServices         │
│                     then storeServiceOrders                      │
└────────────────────────────┬────────────────────────────────────┘
                             │
         ┌───────────────────┴───────────────────┐
         ▼                                       ▼
┌─────────────────────────┐         ┌────────────────────────────┐
│ SingleTourPackageController│      │ EditTourController         │
│ ::cancelOrder             │      │ ::clearTourServices        │
└────────────┬──────────────┘      └─────────────┬──────────────┘
             │                                     │
             └──────────────┬──────────────────────┘
                            ▼
             ┌──────────────────────────────────┐
             │ OnlineHotelCancellationService   │
             │ ::cancelIfApplicable(Order)      │
             └────────────────┬─────────────────┘
                              │
             ┌────────────────┴─────────────────┐
             │ OnlineHotelBookingServiceFactory │
             │ ::makeCancellable(supplier_code) │
             └────────────────┬─────────────────┘
                              │
              ┌───────────────┴────────────────┐
              ▼                                ▼
   ┌──────────────────────┐      ┌─────────────────────────┐
   │ MgBedbankBookingService│     │ (future) Hotelbeds /    │
   │ OnlineHotelCancellable │     │ Tinivia BookingService  │
   └──────────┬───────────┘      └─────────────────────────┘
              ▼
   ┌──────────────────────┐
   │ MgBedbankClient      │
   │ ::cancelReservation  │  → POST …/CancelReservation
   └──────────────────────┘
```

### 3.3 Contracts & factory

| Piece | Path | Role |
|-------|------|------|
| `OnlineHotelCancellable` | `app/Services/HotelSuppliers/Contracts/OnlineHotelCancellable.php` | `cancelFromBookingDetails($bookingDetails, $credentials, $options = [])` |
| `OnlineHotelCancellationService` | `app/Services/HotelSuppliers/OnlineHotelCancellationService.php` | Gates, credentials, persist, logging, generic errors |
| `OnlineHotelBookingServiceFactory` | `app/Services/HotelSuppliers/OnlineHotelBookingServiceFactory.php` | `make()` = book; `makeCancellable()` = cancel (separate capability) |
| `MgBedbankBookingService` | `app/Services/HotelSuppliers/MgBedbank/MgBedbankBookingService.php` | Book + Cancel for MG |
| `MgBedbankClient::cancelReservation` | `app/Services/HotelSuppliers/MgBedbank/MgBedbankClient.php` | HTTP to `CancelReservation` |

**Factory today:**

```php
makeCancellable():
  'mg_bedbank' => MgBedbankBookingService
  // hotelbeds / tinivia — commented placeholders for later
```

Book and cancel are separate: a supplier can search/book before cancel is implemented. Until cancel is registered, removing a **confirmed** booking for that supplier returns 422 (reservation not left open while local order vanishes).

### 3.4 Gates in `cancelIfApplicable`

Runs only when all applicable checks pass:

| Check | Outcome if fail |
|-------|-----------------|
| `type === 'hotel'` | Skip (null) |
| `order_type === 'online'` **or** `booking_details.supplier_code` is a known online supplier | Skip if neither |
| `booking_details` non-empty | Skip (never booked) |
| Not already cancelled (`cancelled_at` / `cancellation.cancelled_at`) | Return existing cancellation |
| `wasConfirmedWithSupplier()` — e.g. MG `mgBookingID`, or Hotelbeds `supplier_booking_reference` / booking reference; `book_response.status !== false` | Skip |
| `supportsCancellation(supplier_code)` | **Throw** (block remove) |
| Credentials configured | **Throw** (generic message) |

### 3.5 MG CancelReservation mapping

**Request (built in `MgBedbankBookingService::cancelFromBookingDetails`):**

| Field | Source |
|-------|--------|
| `MGBookingID` | `book_response.bookingDetails.mgBookingID` |
| `AgencyBookingID` | Same object / `agency_booking_id` |
| `SimulationFlag` | `false` for real cancel (optional simulate via `$options`) |
| `CancelDate` | Only when simulating |
| `Language` / `DetailLevel` | Credentials / `FULL` |

**Success / special cases:**

- Success → normalize status, charges, store `cancel_payload` + `cancel_response`.
- Error `JRVXML106` (already cancelled) → treated as success so local remove can proceed (`already_cancelled: true`).

### 3.6 Where cancel data is stored

Column: **`orders.booking_details`** (JSON), merged onto existing book data **before** soft-delete:

```json
{
  "supplier_code": "mg_bedbank",
  "api_environment": "demo",
  "agency_booking_id": "ONLINE-…",
  "book_payload": { … },
  "book_response": { … },
  "booked_at": "…",
  "recheck": { … },

  "cancelled_at": "2026-10-15T…",
  "cancellation": {
    "supplier_code": "mg_bedbank",
    "api_environment": "demo",
    "mg_booking_id": "AGSG…",
    "agency_booking_id": "ONLINE-…",
    "status": "CANCELCONF",
    "cancellation_charges": {
      "amount": 0,
      "b2b_markup": 0,
      "gross_amount": 0,
      "currency": "SGD"
    },
    "cancel_payload": { … },
    "cancel_response": { /* full third-party body */ },
    "cancelled_at": "…"
  }
}
```

Persistence uses `DB::table('orders')->update(...)` (same style as approve) so Eloquent model events cannot drop the cancel blob; then the order is soft-deleted (`deleted_at` set). Data remains on the soft-deleted row.

### 3.7 Example `booking_details` shapes (pre-cancel)

**MG (book success):** `supplier_code`, `agency_booking_id`, `book_payload`, `book_response.bookingDetails.mgBookingID`, `status: CONF`, `recheck`, …

**Hotelbeds (book success):** `supplier_code`, `supplier_booking_reference`, `book_response.booking.reference`, …

Cancel for Hotelbeds is **not** implemented yet; remove of a confirmed Hotelbeds order is blocked until `makeCancellable` includes it.

---

## 4. Remove paths (critical distinction)

### 4.1 Path A — Classic “Remove” on saved order card

```
editform: removeHotelService(booking_id)
  → POST /api/orders/{booking_id}/cancel  (api.orders.cancel)
  → SingleTourPackageController::cancelOrder
  → OnlineHotelCancellationService::cancelIfApplicable
  → soft-delete order
```

- `{id}` in the route is **`orders.booking_id`**, not `orders.id`  
  (e.g. `id=9143`, `booking_id=9079` → button passes `9079`).

### 4.2 Path B — Lite edit **Save** (this was the bug)

```
submit.js: updateTourPackage()
  → clearAllServices(tourId)     // POST …/services/clear
  → EditTourController::clearTourServices
  → postServiceOrders(...)       // package-store-orders
```

**Before today’s fix:** `clearTourServices` soft-deleted every order with **no** supplier cancel. That is why `booking_details` after “remove” still looked like a pure book payload (no `cancellation` / `cancelled_at`).

**After today’s fix:**

1. Frontend sends `preserve_booking_ids[]` = `_booking_id` of hotels **still** in the form.
2. Clear:
   - **Preserved** hotel orders → not deleted (keeps `booking_details`).
   - **Removed** hotel orders → `cancelIfApplicable` then soft-delete.
3. Store:
   - Rows with `_booking_id` matching a preserved order → **update in place** (do not recreate).
   - New hotels → `Order::create` as before.

### 4.3 Path C — Draft UI only (`hotel.js` `removeAddedHotel`)

Removes a row from in-memory / hidden `hotel_data` **only**. No `orders` row, no cancel API. Supplier cancel happens later when that hotel exists as a confirmed order and is cleared/removed via A or B.

### 4.4 Other clear paths

City-plan / tour-date change also calls `clearTourServices` **without** preserve IDs (intentional wipe). Those paths now also cancel confirmed online hotels before soft-delete.

---

## 5. Files touched (today)

### Backend

| File | Change |
|------|--------|
| `app/Services/HotelSuppliers/Contracts/OnlineHotelCancellable.php` | Cancel contract |
| `app/Services/HotelSuppliers/OnlineHotelCancellationService.php` | Orchestrator, gates, persist, generic errors |
| `app/Services/HotelSuppliers/OnlineHotelBookingServiceFactory.php` | `makeCancellable` / `supportsCancellation` |
| `app/Services/HotelSuppliers/MgBedbank/MgBedbankBookingService.php` | `cancelFromBookingDetails` |
| `app/Services/HotelSuppliers/MgBedbank/MgBedbankClient.php` | `cancelReservation` |
| `app/Http/Controllers/SingleTourPackageController.php` | `cancelOrder` wiring; hotel upsert on store; hydrate `_booking_id` / online flags |
| `app/Http/Controllers/EditTourController.php` | `clearTourServices` preserve + cancel |

### Frontend

| File | Change |
|------|--------|
| `resources/views/single-tour-package/partials/online-hotel-modal.blade.php` | Dynamic meal plan + price sync |
| `public/js/single-tour-package/lite/submit.js` | `preserve_booking_ids[]` on clear |

### Tests

| File | Coverage |
|------|----------|
| `tests/Feature/MgBedbankCancelReservationTest.php` | MG cancel payload / already-cancelled |
| `tests/Feature/OnlineHotelCancellationServiceTest.php` | Gates, MG cancel persist, no supplier name in unsupported error |

---

## 6. Extending cancel to Hotelbeds / Tiniva

1. Implement cancel on the supplier client (e.g. Hotelbeds cancel booking API).
2. Implement `OnlineHotelCancellable` on that booking service (read IDs from that supplier’s `booking_details` shape).
3. Register in `OnlineHotelBookingServiceFactory::makeCancellable`.
4. Keep user-facing messages generic; log `supplier_code` server-side only.

No changes required to `removeHotelService`, `cancelOrder`, or `clearTourServices` for a new supplier beyond factory registration (and any supplier-specific ID extraction in `wasConfirmedWithSupplier` if needed).

---

## 7. Operational notes / how to verify

1. Hard-refresh edit page so `submit.js` is not cached.
2. Add online MG hotel → approve (writes `booking_details`) → remove hotel from lite UI → **Save**.
3. Inspect soft-deleted order: `booking_details` must contain `cancelled_at` and `cancellation.cancel_response`.
4. Logs: `Online hotel cancelled with supplier` (success) or skip/failure reasons under `storage/logs/laravel.log`.
5. If cancel fails → clear/remove returns **422**, order stays, reservation not soft-deleted locally only.

### Known intentional behaviours

- Confirmed **Hotelbeds** remove still blocked until cancel is implemented.
- Empty `booking_details` → no third-party cancel (never booked).
- Soft-deleted rows retain cancel history in `booking_details`.

---

## 8. Related prior context (same initiative, earlier in session)

Not the focus of the cancel notes above, but part of the same online-hotel stream:

- MG **SearchHotel-first** search (cached GetHotelList IDs → SearchHotel; chunking; `pricesOnSearch`).
- Online hotel modal: rooms beside guests, stay-date bounds, multi-room pax.
- Hotelbeds CheckRate / Book services (book path; cancel still pending).

---

## 9. Quick reference — “where is X?”

| Question | Answer |
|----------|--------|
| Where is CancelReservation called? | `MgBedbankClient::cancelReservation` via `MgBedbankBookingService::cancelFromBookingDetails` |
| Where is cancel response stored? | `orders.booking_details.cancellation` (+ `cancelled_at`) |
| Why was cancel missing after edit remove? | Lite save used `clearTourServices`, which did not cancel (fixed) |
| How do we keep a booked hotel on save? | `preserve_booking_ids[]` + in-place update in `storeServiceOrders` |
| How do we hide supplier names from UI? | Orchestrator/controller return generic `RuntimeException` messages; detail in logs |
| Draft remove in hotel.js? | Local JSON only — no orders / no cancel |
