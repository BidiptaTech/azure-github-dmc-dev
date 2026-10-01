# Remove & Reject Service Implementation Reference

This document maps where **remove** and **reject** service actions are triggered and which controller methods handle them. All these paths call `CommonHelper::maybeRevertTourStatusToNewEnquiry()` when a service is soft-deleted, so the tour status can revert to "New Enquiry" if the tour has negotiation history in `enquiry_comments`.

---

## 1. Remove Service (Edit Form)

**Controller:** `SingleTourPackageController::cancelOrder`  
**Route:** `POST /api/orders/{id}/cancel`  
**Route name:** `api.orders.cancel`

### Where it's triggered

| View | Trigger |
|------|---------|
| `resources/views/single-tour-package/editform.blade.php` | Remove buttons → `removeHotelService()`, `removeAttractionService()`, `removeGuideService()`, `removeRestaurantService()`, `removeTransportService()` |
| `resources/views/single-tour-package/editform23-12.blade.php` | Same remove buttons |
| `resources/views/single-tour-package/edit.blade.php` | Same remove buttons |

**Flow:** User clicks "Remove" on a service → SweetAlert confirmation → `fetch(POST api.orders.cancel)` → `cancelOrder()` → soft delete order → `maybeRevertTourStatusToNewEnquiry()`.

---

## 2. Reject Service (Booking Pages)

**Controller:** `HotelBookingController`  
**Routes:** Under `/booking/reject-*` prefix

### 2.1 Reject Hotel Booking

- **Method:** `rejectHotelBooking`
- **Route:** `POST /booking/reject-hotel-booking`
- **Route name:** `booking.reject.hotel.booking`

### 2.2 Reject Restaurant Booking

- **Method:** `rejectRestaurantBooking`
- **Route:** `POST /booking/reject-restaurant-booking`
- **Route name:** `booking.reject.restaurant.booking`

### 2.3 Reject Attraction Booking

- **Method:** `rejectAttractionBooking`
- **Route:** `POST /booking/reject-attraction-booking`
- **Route name:** `booking.reject.attraction.booking`

### 2.4 Reject Guide Booking

- **Method:** `rejectGuideBooking`
- **Route:** `POST /booking/reject-guide-booking`
- **Route name:** `booking.reject.guide.booking`

### 2.5 Reject Arrival Booking (Entry Port)

- **Method:** `rejectArrivalBooking`
- **Route:** `POST /booking/reject-arrival-booking`
- **Route name:** `booking.reject.arrival.booking`

### 2.6 Reject Departure Booking (Exit Port)

- **Method:** `rejectDepartureBooking`
- **Route:** `POST /booking/reject-departure-booking`
- **Route name:** `booking.reject.departure.booking`

### 2.7 Reject Hourly Transport Booking

- **Method:** `rejectHourlyBooking`
- **Route:** `POST /booking/reject-hourly-booking`
- **Route name:** `booking.reject.hourly.booking`

### 2.8 Reject Point-to-Point Transport Booking

- **Method:** `rejectPointToPointBooking`
- **Route:** `POST /booking/reject-point-to-point-booking`
- **Route name:** `booking.reject.point.to.point.booking`

### 2.9 Reject Local Transport Booking

- **Method:** `rejectLocalTransportBooking`
- **Route:** `POST /booking/reject-local-transport-booking`
- **Route name:** `booking.reject.local.transport.booking`

### Where reject actions are triggered

| View | Functions |
|------|-----------|
| `resources/views/bookings/confirmed.blade.php` | `rejectHotelBooking()`, `rejectAttractionBooking()`, `rejectGuideBooking()`, `rejectLocalTransportBooking()`, etc. |
| `resources/views/bookings/confirmed_bk.blade.php` | Same |
| `resources/views/bookings/definite.blade.php` | Same |
| `resources/views/bookings/definite_bk.blade.php` | Same |
| `resources/views/bookings/actual.blade.php` | Same |

**Flow:** User clicks "Reject" on a booking → Reject modal with reason → `fetch(POST /booking/reject-*)` → corresponding controller method → soft delete order → `maybeRevertTourStatusToNewEnquiry()`.

---

## 3. Delete Services Outside Date Range

**Controller:** `EditTourController::deleteServicesOutsideDateRange`  
**Route:** `POST /single-tour-package/{tour}/info`  
**Route name:** `single-tour-package.update-info`

**Trigger:** Called internally when `updateTour()` receives `delete_affected_services=true` and tour dates change.

**Flow:** User updates tour dates → modal confirms affected services → user confirms deletion → `deleteServicesOutsideDateRange()` → soft delete orders outside new date range → `maybeRevertTourStatusToNewEnquiry()` for each affected tour.

---

## 4. API Cancel Booking

**Controller:** `Api\TourController::CancelBooking`  
**Route:** `POST /api/cancel-booking` (API)

**Trigger:** External API call to cancel a booking (soft deletes the order).

---

## Summary: All Methods Using `maybeRevertTourStatusToNewEnquiry`

| Controller | Method | Action |
|------------|--------|--------|
| `SingleTourPackageController` | `cancelOrder` | Remove service from edit form |
| `HotelBookingController` | `rejectHotelBooking` | Reject hotel |
| `HotelBookingController` | `rejectRestaurantBooking` | Reject restaurant |
| `HotelBookingController` | `rejectAttractionBooking` | Reject attraction |
| `HotelBookingController` | `rejectGuideBooking` | Reject guide |
| `HotelBookingController` | `rejectArrivalBooking` | Reject arrival (entry port) |
| `HotelBookingController` | `rejectDepartureBooking` | Reject departure (exit port) |
| `HotelBookingController` | `rejectHourlyBooking` | Reject hourly transport |
| `HotelBookingController` | `rejectPointToPointBooking` | Reject point-to-point transport |
| `HotelBookingController` | `rejectLocalTransportBooking` | Reject local transport |
| `EditTourController` | `deleteServicesOutsideDateRange` | Delete services outside new tour dates |
| `EditTourController updated.php` | `deleteServicesOutsideDateRange` | Same (backup/legacy file) |
| `Api\TourController` | `CancelBooking` | API cancel booking |
