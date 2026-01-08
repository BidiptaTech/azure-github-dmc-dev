# Restaurant Guide - Quick Reference

## What Changed?

Restaurant guides are now **automatically saved to the guide table** (orders table with type='guide') when selected in the pro form, just like attraction guides.

## User Flow

```
┌─────────────────────────────────────────────────────────────┐
│ 1. User opens Restaurant Modal in Pro Form                  │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 2. User checks "Add Guide for this Restaurant"              │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 3. Guide dropdown appears - User selects a guide            │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 4. User saves restaurant/meal                               │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 5. User submits entire pro form                             │
└─────────────────────────────────────────────────────────────┘
```

## What Happens in Backend?

```
┌──────────────────────────────────────────────────────────────┐
│ Restaurant Order Created                                      │
│ ─────────────────────────                                    │
│ Type: restaurant                                             │
│ Booking ID: R123456                                          │
│ Data: {restaurantName, mealType, pricing, etc.}             │
└──────────────────────────────────────────────────────────────┘
                            ↓
┌──────────────────────────────────────────────────────────────┐
│ IF Guide Selected → Guide Order Created                      │
│ ────────────────────────────────────────                     │
│ Type: guide                                                  │
│ Booking ID: G789012                                          │
│ Data: {                                                      │
│   guide_id: 123,                                             │
│   guide_name: "John Doe",                                    │
│   languages: ["English", "Mandarin"],                        │
│   linked_to_restaurant: "R123456",  ← LINKED!               │
│   bookingDate: "2025-01-15",                                 │
│   hours: 2                                                   │
│ }                                                            │
└──────────────────────────────────────────────────────────────┘
```

## Database Structure

### Orders Table - Restaurant Entry
```
id: 1001
booking_id: R123456
agent_id: 5001
tour_id: T789
type: restaurant
data: [{restaurantId: 45, restaurantName: "Seafood Paradise", ...}]
```

### Orders Table - Guide Entry (NEW!)
```
id: 1002
booking_id: G789012
agent_id: 5001
tour_id: T789
type: guide
data: [{
  guide_id: 123,
  guide_name: "John Doe",
  linked_to_restaurant: "R123456",  ← Links to restaurant above
  bookingDate: "2025-01-15",
  hours: 2,
  ...
}]
```

## Where Guides Appear

✅ **Guide Jobsheet** - Assign guides to tours
✅ **Guide Job List** - View all guide assignments
✅ **Tour Details** - Shows all services including guides
✅ **Finance Reports** - Guide service revenue
✅ **Bookings View** - All orders for a tour

## Key Features

### 1. Automatic Linking
- Guide order automatically linked to restaurant via `linked_to_restaurant` field
- No manual linking required

### 2. Duplicate Prevention
- Same guide + same restaurant + same date = ONE guide order
- Different dates or restaurants = separate guide orders

### 3. Consistent with Attractions
- Works exactly like attraction guides
- Same data structure
- Same linking pattern

## Code Changes

### File Modified
```
app/Http/Controllers/EnquiryFormPro.php
```

### Lines Added
```
Lines 1392-1467: Restaurant guide order creation logic
```

### What It Does
1. Checks if guide is selected (`guideInfo` exists)
2. Creates unique identifier to prevent duplicates
3. Builds guide data structure
4. Creates guide order in orders table
5. Links guide to restaurant using `linked_to_restaurant`
6. Logs creation for debugging

## Testing

### How to Test
1. Open Pro Form
2. Add a restaurant
3. Check "Add Guide for this Restaurant"
4. Select a guide
5. Save restaurant
6. Submit form
7. Check database: `orders` table should have TWO entries:
   - One with type='restaurant'
   - One with type='guide' (with linked_to_restaurant field)

### Expected Result
```sql
-- Restaurant order
SELECT * FROM orders WHERE type='restaurant' AND booking_id='R123456';

-- Linked guide order
SELECT * FROM orders WHERE type='guide' 
AND data->0->>'linked_to_restaurant' = 'R123456';
```

## Comparison

| Feature | Before | After |
|---------|--------|-------|
| Guide Selection | ✅ Available in UI | ✅ Available in UI |
| Guide Saved | ❌ Only in restaurant data | ✅ Separate guide order |
| Guide in Jobsheet | ❌ Not visible | ✅ Visible |
| Guide Linking | ❌ No linking | ✅ Linked via `linked_to_restaurant` |
| Duplicate Prevention | ❌ None | ✅ MD5 hash check |

## Benefits

1. **Visibility** - Guides appear in guide jobsheets
2. **Tracking** - Separate orders for better tracking
3. **Consistency** - Same pattern as attraction guides
4. **Reporting** - Guide services included in reports
5. **Assignment** - Guides can be assigned to tours easily

## Notes

- Guide pricing is set to 0 (can be updated later)
- Default hours: 2 hours for restaurant guides
- Guide data preserved in both restaurant and guide orders
- Logs available in `storage/logs/laravel.log`

## Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Search for: "Created guide order for restaurant"
3. Verify guide data structure in orders table
4. Ensure `linked_to_restaurant` field exists in guide order data

