# Hotels, Attractions & Restaurants - DMC Filter and Pricing Verification

## Summary

Verified and fixed DMC filtering and pricing for all service types in the Enquiry Form Pro:

| Service | DMC Filter | Pricing | Role 35 Support | Status |
|---------|------------|---------|-----------------|---------|
| **Hotels** | ❌ → ✅ | ✅ | ❌ → ✅ | **FIXED** |
| **Attractions** | ✅ | ✅ | ❌ → ✅ | **FIXED** |
| **Restaurants** | ✅ | ✅ | ✅ | **OK** |
| **Guides** | ✅ | ✅ | ❌ → ✅ | **FIXED** (previous) |
| **Miscellaneous** | ✅ | ✅ | ❌ → ✅ | **FIXED** (previous) |

## Issues Found & Fixed

### 1. Hotels - DMC Filter Was Commented Out! ❌

**Problem:**
```php
// Line 357 in getHotelsByDestination()
//   ->whereJsonContains('dmc_id', $dmc_id)  // ❌ COMMENTED OUT!
```

The DMC filter was completely disabled, showing ALL hotels to ALL users!

**Fix Applied:**
- Added DMC ID determination logic
- Enabled DMC filtering
- Added role 35 and other missing roles
- Added logging

**After:**
```php
// Determine DMC ID
$dmc_id = null;
if ($user->role_id == 11) {
    $dmc_id = $user->userId;
} elseif (in_array($user->role_id, [33, 34, 35, 77, 78, 84, ...])) {
    $dmc_id = $user->created_by;
}

// Filter by DMC
if ($dmc_id) {
    $hotelsQuery->whereJsonContains('dmc_id', (int) $dmc_id);
}
```

### 2. Attractions - Missing Role 35 ❌

**Problem:**
```php
elseif (in_array($user->role_id, [33, 34, 128, 129, ...])) {
    // ❌ Role 35 missing!
}
```

**Fix Applied:**
```php
elseif (in_array($user->role_id, [33, 34, 35, 77, 78, 84, 120, ...])) {
    $dmc_id = $user->created_by;
}
```

### 3. Restaurants - Already Correct ✅

Restaurants use data loaded in the `create()` method, which was already fixed earlier to include role 35.

## Architecture Overview

### Hotels

**Frontend:**
- File: `resources/views/enquiryform_pro/create.blade.php`
- Function: `loadHotelsByDestination()`
- API Call: `/enquiry-form-pro/get-hotels?destination=Singapore`

**Backend:**
- Method: `getHotelsByDestination(Request $request)`
- Filters: `dmc_id` (JSON), `city`, `status`, `is_active`, `is_complete`
- Returns: Hotels with rooms and bed configurations

**Pricing:**
- Room prices stored in `rooms` table
- Seasonal pricing in `hotel_seasons` table
- Weekend/weekday pricing supported
- Extra bed pricing included

### Attractions

**Frontend:**
- Function: `loadAttractionsByDestination()`
- API Call: `/enquiry-form-pro/get-attractions?destination=Singapore`

**Backend:**
- Method: `getAttractionsByDestination(Request $request)`
- Filters: `dmc_id` (JSON), `location`, `status`
- Returns: Attractions with pricing

**Pricing:**
- `adult_price` - Adult ticket price
- `child_price` - Child ticket price
- `senior_adult_price` - Senior price
- Prices loaded directly from database

### Restaurants

**Frontend:**
- Function: `loadRestaurantsByDestination()`
- Uses: Data from `@json($restaurants)` (loaded in controller)
- Filters: Client-side by `city` field

**Backend:**
- Data loaded in: `create()` method
- Filters: `dmc_id` (JSON), `status`
- Meals loaded separately with pricing

**Pricing:**
- Meal prices in `meals` table
- `adult_price` - Adult meal price
- `child_price` - Child meal price
- `price` - General price (fallback)

## Database Schema

### Hotels Table:
```sql
CREATE TABLE hotels (
    id BIGSERIAL PRIMARY KEY,
    dmc_id JSONB,                    -- ✅ Array of DMC IDs [1,2,3]
    name VARCHAR(255),
    city VARCHAR(255),               -- ✅ Filter by destination
    status INTEGER DEFAULT 1,
    is_active BOOLEAN DEFAULT true,
    is_complete BOOLEAN DEFAULT false,
    ...
);
```

### Rooms Table:
```sql
CREATE TABLE rooms (
    room_id BIGSERIAL PRIMARY KEY,
    hotel_id BIGINT,
    room_type VARCHAR(255),
    base_price DECIMAL(10,2),       -- ✅ Base room price
    weekend_price DECIMAL(10,2),
    weekday_price DECIMAL(10,2),
    status INTEGER DEFAULT 1,
    ...
);
```

### Attractions Table:
```sql
CREATE TABLE attractions (
    attraction_id BIGSERIAL PRIMARY KEY,
    dmc_id JSONB,                    -- ✅ Array of DMC IDs
    name VARCHAR(255),
    location VARCHAR(255),           -- ✅ Filter by destination
    adult_price DECIMAL(10,2),       -- ✅ Adult price
    child_price DECIMAL(10,2),       -- ✅ Child price
    senior_adult_price DECIMAL(10,2),
    status INTEGER DEFAULT 1,
    ...
);
```

### Restaurants Table:
```sql
CREATE TABLE restaurants (
    restaurant_id BIGSERIAL PRIMARY KEY,
    dmc_id JSONB,                    -- ✅ Array of DMC IDs
    name VARCHAR(255),
    city VARCHAR(255),               -- ✅ Filter by destination
    status INTEGER DEFAULT 1,
    ...
);
```

### Meals Table:
```sql
CREATE TABLE meals (
    meal_id BIGSERIAL PRIMARY KEY,
    restaurant_id BIGINT,
    name VARCHAR(255),
    type VARCHAR(50),                -- breakfast/lunch/dinner
    adult_price DECIMAL(10,2),       -- ✅ Adult meal price
    child_price DECIMAL(10,2),       -- ✅ Child meal price
    price DECIMAL(10,2),             -- General price
    ...
);
```

## Pricing Verification

### Hotels ✅
```json
{
    "hotel_id": 1,
    "name": "Grand Hotel",
    "rooms": [
        {
            "room_id": 101,
            "room_type": "Deluxe",
            "base_price": 150.00,
            "weekend_price": 180.00,
            "weekday_price": 140.00,
            "bed_types": [
                {
                    "bed_type": "King",
                    "max_occupancy": 2,
                    "extra_bed_price": 30.00
                }
            ]
        }
    ]
}
```

### Attractions ✅
```json
{
    "id": 1,
    "name": "Universal Studios",
    "location": "Singapore",
    "adult_price": 79.00,
    "child_price": 59.00,
    "senior_adult_price": 39.00
}
```

### Restaurants ✅
```json
{
    "restaurant_id": 1,
    "name": "Seafood Paradise",
    "city": "Singapore",
    "meals": [
        {
            "meal_id": 1,
            "name": "Set Menu A",
            "type": "lunch",
            "adult_price": 25.00,
            "child_price": 15.00
        }
    ]
}
```

## Testing Checklist

### Test Hotels:

1. **Login as Role 35 User**
2. **Open Hotel Modal**
3. **Select Destination: Singapore**
4. **Verify:**
   - [ ] Only hotels with `dmc_id` containing your DMC ID
   - [ ] Prices loaded from database
   - [ ] Room types and bed configurations shown
   - [ ] Weekend/weekday pricing available

5. **Check Logs:**
```
getHotelsByDestination - DMC ID determined
{
    "dmc_id": 5,
    "user_id": 8,
    "role_id": 35,
    "destination": "Singapore"
}
```

6. **Database Verification:**
```sql
SELECT id, name, dmc_id, city
FROM hotels
WHERE dmc_id::jsonb @> '5'
  AND city = 'Singapore'
  AND status = 1;
```

### Test Attractions:

1. **Open Attraction Modal**
2. **Select Destination: Singapore**
3. **Verify:**
   - [ ] Only attractions with your DMC ID
   - [ ] Adult/Child prices shown correctly
   - [ ] Prices match database

4. **Database Verification:**
```sql
SELECT attraction_id, name, dmc_id, location, adult_price, child_price
FROM attractions
WHERE dmc_id::jsonb @> '5'
  AND location = 'Singapore'
  AND status = 1;
```

### Test Restaurants:

1. **Open Meal/Restaurant Modal**
2. **Select Destination: Singapore**
3. **Verify:**
   - [ ] Only restaurants in Singapore for your DMC
   - [ ] Meal prices loaded correctly
   - [ ] Adult/Child pricing shown

4. **Database Verification:**
```sql
SELECT r.restaurant_id, r.name, r.dmc_id, r.city,
       m.meal_id, m.name as meal_name, m.adult_price, m.child_price
FROM restaurants r
LEFT JOIN meals m ON m.restaurant_id = r.restaurant_id
WHERE r.dmc_id::jsonb @> '5'
  AND r.city = 'Singapore'
  AND r.status = 1;
```

## DMC Filtering Logic

All services now use consistent DMC ID determination:

```php
// Role 11 - DMC User
if ($user->role_id == 11) {
    $dmc_id = $user->userId;
}

// Roles 33-140 - DMC Product Level & Sub-users
elseif (in_array($user->role_id, [33, 34, 35, 77, 78, 84, 120, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140])) {
    $dmc_id = $user->created_by;
}

// Roles 37, 64-68 - Sales Manager Level 1
elseif (in_array($user->role_id, [37, 64, 65, 66, 67, 68])) {
    $sales_head = User::where('userId', $user->created_by)->first();
    $dmc_id = $sales_head ? $sales_head->created_by : null;
}

// Roles 38, 81, 90, etc. - Sales Manager Level 2
elseif (in_array($user->role_id, [38, 81, 90, 108, 117, 124, 125, 126, 127])) {
    // Goes up 3 levels
}
```

## Files Modified

1. `app/Http/Controllers/EnquiryFormPro.php`
   - Method: `getHotelsByDestination()` - Added DMC filtering
   - Method: `getAttractionsByDestination()` - Added role 35
   - Both methods: Added comprehensive logging

## Status

✅ **HOTELS** - DMC filtering enabled, role 35 added, pricing correct
✅ **ATTRACTIONS** - Role 35 added, DMC filtering working, pricing correct
✅ **RESTAURANTS** - Already working correctly, pricing correct
✅ **GUIDES** - Fixed previously, pricing correct
✅ **MISCELLANEOUS** - Fixed previously, pricing correct

## All Services Summary

| Service | Filter Column | Filter Type | Pricing Columns |
|---------|---------------|-------------|-----------------|
| Hotels | `dmc_id` | JSONB Array | `base_price`, `weekend_price`, `weekday_price` |
| Attractions | `dmc_id` | JSONB Array | `adult_price`, `child_price`, `senior_adult_price` |
| Restaurants | `dmc_id` | JSONB Array | Meals: `adult_price`, `child_price` |
| Guides | `dmc_id` | Integer | `two_hour_price` ... `twelve_hour_price` |
| Miscellaneous | `dmc_id` | Integer (via prices table) | `adult_price`, `child_price`, `infant_price` |

## Related Documentation

- `GUIDE_SECTION_DMC_FILTER_AND_PRICING.md` - Guide section details
- `MISCELLANEOUS_ENQUIRY_FORM_DMC_INTEGRATION.md` - Miscellaneous integration
- `MISCELLANEOUS_ROLE_35_FIX.md` - Role 35 fix details

