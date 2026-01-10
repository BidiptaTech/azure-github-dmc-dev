# Restaurant Guide Implementation - Complete

## Overview
Restaurant guides are now automatically created as separate guide orders in the `orders` table when a guide is selected for a restaurant in the Enquiry Form Pro, similar to how attraction guides work.

## Implementation Details

### 1. Backend Changes (EnquiryFormPro.php)

**Location:** `app/Http/Controllers/EnquiryFormPro.php` (Lines 1392-1467)

**What was added:**
- Auto-creation of guide orders when a guide is selected for a restaurant
- Guide orders are linked to their parent restaurant order using `linked_to_restaurant` field
- Duplicate prevention using unique guide identifiers (MD5 hash)
- Proper logging for debugging

**Key Features:**
```php
// Guide data structure includes:
- guide_id: The selected guide's ID
- guide_name: Guide's name
- languages: Array of languages the guide speaks
- linked_to_restaurant: Restaurant booking ID (for linking)
- bookingDate: Date of service
- hours: Default 2 hours for restaurant guides
- All customer information (fullName, email, phone, etc.)
```

### 2. Frontend (Already Exists)

**Location:** `resources/views/enquiryform_pro/create.blade.php`

**Existing Features:**
- Restaurant guide checkbox (line 2652)
- Guide selection dropdown (line 2665)
- Guide data is captured and sent with meal data
- Guide info includes: guideId, guideName, languages

**JavaScript Functions:**
- `toggleRestaurantGuideFields()`: Shows/hides guide selection fields
- Guide data is added to meal object when saving

### 3. Data Flow

```
User Action:
1. User opens restaurant modal in pro form
2. User checks "Add Guide for this Restaurant"
3. User selects a guide from dropdown
4. User saves the restaurant/meal

Backend Processing:
1. Restaurant order is created first (type: 'restaurant')
2. If guide is selected (guideInfo exists):
   - Create unique identifier to prevent duplicates
   - Build guide data structure
   - Create separate guide order (type: 'guide')
   - Link guide to restaurant using 'linked_to_restaurant' field
   - Log the creation
3. Both orders saved to orders table

Database Structure:
- Restaurant Order: type='restaurant', booking_id=R123
- Guide Order: type='guide', booking_id=G456, data contains 'linked_to_restaurant'=R123
```

### 4. How Linking Works

**Attraction Guide (for reference):**
```php
'linked_to_attraction' => $bookingId
```

**Restaurant Guide (new implementation):**
```php
'linked_to_restaurant' => $bookingId
```

Both are stored in the `data` JSON field of the guide order, allowing views and reports to show which service the guide is linked to.

### 5. Duplicate Prevention

The system prevents duplicate guide orders using MD5 hash:
```php
$guideIdentifier = md5(
    ($meal['guideId'] ?? '') . 
    ($meal['restaurantName'] ?? '') . 
    $guideDate . 
    'restaurant'
);
```

This ensures:
- Same guide for same restaurant on same date = only one order
- Different restaurants or dates = separate orders
- Multiple meals at same restaurant with same guide = one guide order

### 6. Display in Views

Guide orders with `linked_to_restaurant` will appear in:
- Guide jobsheet creation (assign guides to jobs)
- Guide job list (view assigned guide jobs)
- Tour details/bookings views
- Finance reports (guide service revenue)
- Any view that queries orders with type='guide'

The `linked_to_restaurant` field in the order data allows views to show:
- Which restaurant the guide is for
- Service date and time
- Customer information
- Pricing details

### 7. Comparison: Attraction vs Restaurant Guides

| Feature | Attraction Guide | Restaurant Guide |
|---------|------------------|------------------|
| **Creation** | Auto-created (currently disabled) | Auto-created (enabled) |
| **Link Field** | `linked_to_attraction` | `linked_to_restaurant` |
| **Default Hours** | Based on attraction | 2 hours (default) |
| **Pricing** | From guide options | 0 (to be set later) |
| **Data Source** | Attraction modal | Restaurant modal |
| **Duplicate Check** | MD5 hash | MD5 hash |

### 8. Testing Checklist

✅ **Backend:**
- Guide order created when guide selected for restaurant
- Guide order NOT created when guide not selected
- Duplicate prevention works (same guide, restaurant, date)
- Proper linking with `linked_to_restaurant` field
- Logging shows creation events

✅ **Frontend:**
- Guide checkbox toggles guide selection fields
- Guide dropdown populated with available guides
- Guide data sent with meal data
- Guide info preserved when editing meals

✅ **Database:**
- Guide orders appear in `orders` table with type='guide'
- Guide data includes `linked_to_restaurant` field
- Restaurant booking_id matches guide's linked_to_restaurant value

✅ **Display:**
- Guide orders appear in guide jobsheet
- Linked restaurant information accessible
- Guide can be assigned to tours
- Reports include restaurant guide orders

### 9. Code Locations

**Controller:**
```
app/Http/Controllers/EnquiryFormPro.php
Lines 1392-1467: Restaurant guide order creation
```

**View:**
```
resources/views/enquiryform_pro/create.blade.php
Lines 2647-2677: Restaurant guide selection UI
Lines 12105-12120: Guide data capture in JavaScript
Lines 11574-11587: Guide section reset on modal close
```

### 10. Logging

The system logs guide creation for debugging:
```php
\Log::info('Created guide order for restaurant', [
    'guide_identifier' => $guideIdentifier,
    'guide_name' => $guideInfo['guideName'] ?? 'Guide',
    'restaurant_name' => $meal['restaurantName'] ?? ''
]);
```

Check logs at: `storage/logs/laravel.log`

### 11. Future Enhancements

Potential improvements:
1. **Pricing:** Add guide pricing calculation for restaurants (currently set to 0)
2. **Hours:** Allow custom hours selection in restaurant modal
3. **Multiple Guides:** Support multiple guides per restaurant
4. **Guide Availability:** Check guide availability before selection
5. **Auto-assign:** Suggest guides based on languages/experience

### 12. Related Files

- `app/Http/Controllers/EnquiryFormPro.php` - Main controller
- `resources/views/enquiryform_pro/create.blade.php` - Pro form view
- `app/Models/Order.php` - Order model
- `app/Models/Guide.php` - Guide model
- `resources/views/CreateJobSheet/create-guide-jobsheet.blade.php` - Guide jobsheet

## Summary

Restaurant guides now work exactly like attraction guides:
1. ✅ Selected in the pro form restaurant modal
2. ✅ Saved as separate guide orders in the orders table
3. ✅ Linked to their parent restaurant using `linked_to_restaurant`
4. ✅ Appear in guide jobsheets and reports
5. ✅ Prevent duplicates using unique identifiers

The implementation is complete and follows the same pattern as attraction guides, ensuring consistency across the application.

