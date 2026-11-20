# Enquiry Form Pro — Online Hotel & Online Attraction

This guide explains how **Online Hotel** and **Online Attraction** work on Enquiry Form Pro (`create` / `edit`), how they differ from offline booking, and how data is listed and stored.

## Who can use it

Online booking is enabled only when Master DMC online API is on for the logged-in user:

```php
\App\Helpers\CommonHelper::masterDmcOnlineApiEnabled(auth()->user())
```

If disabled, Offline/Online toggles and modals are not shown.

## Where it appears (UI)

On **Accommodation** and **Attractions** section headers:

| Control | Action |
|--------|--------|
| **Offline** (default) | Existing Pro modal (catalog hotels / attractions + transfer/guide options) |
| **Online** | Opens the same STP live modals used by Lite |
| **+ Add** | Still opens the offline Pro modal (unchanged) |

### Online Hotel flow (like Lite)

1. Choose **Online** on Accommodation (or use the Online radio).
2. Modal: **Online Hotel Booking** → city, dates, guests → **Fetch Hotels**.
3. Select hotel → room / bed / meal → add.
4. Row appears on the Pro accommodation listing with an **ONLINE HOTEL** badge.
5. **No auto Arrival / Departure** is driven by online hotel rows (offline hotels still drive that panel).

### Online Attraction flow (like Lite)

1. Choose **Online** on Attractions.
2. Modal: **Online Attraction Booking** → fetch full catalog → pick attraction → ticket → add.
3. Row appears on the Pro tour listing with an **ONLINE ATTRACTION** badge.
4. **No transfer / guide** on online attraction rows (`Selection: withoutTransport`).

## Pricing rule (business)

**Option 1 — Online display price for both Cost and Sell**

| Column | Online behaviour |
|--------|------------------|
| Hotel Avg Cost / Sell | Same value (live display / per-night avg) |
| Attraction Cost/Pax / Sell/Pax | Same unit price from the online ticket |

Offline rows keep separate cost vs sell. Online sell fields are read-only so they stay equal to cost.

## Listing markers

- Hotel name cell: purple **ONLINE HOTEL** badge + “Live API · cost = sell”.
- Attraction name cell: cyan **ONLINE ATTRACTION** badge + “No transfer · cost = sell”.
- Clicking edit on an online row shows an info message: remove and re-add via Online to change details.

## Storage (same shape as Lite online)

Submit still uses Pro FormData keys:

- `accommodations` ← `transformAccommodationData()`
- `tours` ← `transformTourData()`

Online rows keep Pro listing fields **and** Lite-style online metadata:

### Hotel (extra fields)

- `isOnlineHotel: true`
- `hotelSourceType: 'online'`
- `priceMode: 'online'`
- `onlineHotelBooking` (rate key, room, markup, check-in/out, …)
- `onlineHotelRaw`, `onlineHotelSource`, `api_environment`
- `transfer_options: null`
- `avgCost` / `avgSell` / `cost` / `sell` all equal

### Attraction (extra fields)

- `isOnlineAttraction: true`
- `attractionSourceType: 'online'`
- `sku_id`, `ticket_sku_id`, `provider_ticket_id`
- `supplier_code`, `api_environment`, `onlineAttractionRaw`
- `lowest_ticket_price` / `highest_ticket_price`
- `Selection: 'withoutTransport'`, `transfer_options: null`
- adult/child/infant **cost === sell**

## APIs used (shared with STP)

| Route name | Purpose |
|------------|---------|
| `fetch-online-hotels` | Hotel search |
| `fetch-online-hotel-rooms` | Room availability (two-step suppliers) |
| `fetch-online-attractions` | Full attraction catalog (`fetch_all`) |
| `fetch-online-attraction-tickets` | Lazy tickets for selected SKU |

## Files involved

| File | Role |
|------|------|
| `resources/views/enquiryform_pro/partials/online-booking-js.blade.php` | Pro hooks: toggles, map → list, store patches, badges |
| `resources/views/single-tour-package/partials/online-hotel-modal.blade.php` | Shared online hotel UI |
| `resources/views/single-tour-package/partials/online-attraction-modal.blade.php` | Shared online attraction UI |
| `resources/views/enquiryform_pro/create.blade.php` | Toggle UI + includes |
| `resources/views/enquiryform_pro/edit.blade.php` | Toggle UI + includes |

## Developer notes

1. Modals push scripts via `@stack('scripts')` (after `@yield('scripts')` in `layouts/layout.blade.php`).
2. Pro defines `window.pushSelectedHotel` and `window.pushSelectedOnlineAttraction` before the user clicks Add.
3. Attraction modal uses Lite path when `__stpLiteOnlineAttractionRoot` or `__enquiryProOnlineAttractionActive` is set.
4. Offline booking paths are untouched when the Offline radio / **+ Add** is used.

## Quick test checklist

- [ ] Online toggles visible only when Master DMC online API is enabled  
- [ ] Online hotel adds a row with **ONLINE HOTEL** badge; Avg Cost = Sell  
- [ ] Online hotel does not change Arrival/Departure when only online hotels exist  
- [ ] Online attraction adds a row with **ONLINE ATTRACTION** badge; no transfer  
- [ ] Save payload contains `isOnlineHotel` / `onlineHotelBooking` and `isOnlineAttraction` / `sku_id`  
- [ ] Offline + Add still works as before  
