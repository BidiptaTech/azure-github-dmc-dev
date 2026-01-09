# Guide Section - DMC Filter and Pricing Verification

## Overview

Verified and fixed the Guide section in the Enquiry Form Pro to ensure:
1. ✅ Guides are filtered by DMC ID
2. ✅ Prices are loaded correctly from database
3. ✅ All DMC-related roles are supported (including role 35)

## Guide Section Architecture

### Frontend (JavaScript)
**File:** `resources/views/enquiryform_pro/create.blade.php`

**Function:** `loadGuidesByDestination()`
- Fetches guides via AJAX when destination is selected
- Displays guides in a table with checkboxes
- Shows hourly pricing options (2h, 4h, 6h, 8h, 10h, 12h)
- Updates cost/sell price when hours change

### Backend (Controller)
**File:** `app/Http/Controllers/EnquiryFormPro.php`

**Method:** `getGuidesByDestination(Request $request)`
- Determines DMC ID based on user role
- Filters guides by DMC ID and destination
- Returns guide data with all pricing tiers

## DMC Filtering - How It Works

### 1. **Determine DMC ID**

The controller determines the DMC ID based on user role:

```php
// Role 11 - DMC User
if ($user->role_id == 11) {
    $dmc_id = $user->userId;
}

// Roles 33-140 - DMC Product Level & Sub-users
elseif (in_array($user->role_id, [33, 34, 35, 77, 78, 84, 120, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140])) {
    $dmc_id = $user->created_by;  // Parent DMC's ID
}

// Roles 37, 64-68 - Sales Manager Level 1
elseif (in_array($user->role_id, [37, 64, 65, 66, 67, 68])) {
    $sales_head = User::where('userId', $user->created_by)->first();
    $dmc_id = $sales_head ? $sales_head->created_by : null;
}

// Roles 38, 81, 90, etc. - Sales Manager Level 2
elseif (in_array($user->role_id, [38, 81, 90, 108, 117, 124, 125, 126, 127])) {
    // Goes up 3 levels to find DMC
}
```

### 2. **Filter Guides Query**

```php
$guidesQuery = Guide::where('status', 1)
    ->where('city', $destination);

if ($dmc_id) {
    $guidesQuery->where('dmc_id', $dmc_id);  // ✅ Filters by DMC
}

$guides = $guidesQuery
    ->with('languages')
    ->select('guide_id', 'name', 'city', 'country', 'day_rate', 
             'hourly_price', 'two_hour_price', 'four_hour_price', 
             'six_hour_price', 'eight_hour_price', 'ten_hour_price', 'twelve_hour_price')
    ->orderBy('name')
    ->get();
```

### 3. **Result**

- ✅ Each DMC sees only their own guides
- ✅ Guides are filtered by destination (city)
- ✅ Only active guides shown (status = 1)

## Pricing Structure

### Database Columns

The `guides` table has these pricing columns:

```sql
- day_rate           (12-hour rate, legacy)
- hourly_price       (per hour rate)
- two_hour_price     (2-hour package)
- four_hour_price    (4-hour package)
- six_hour_price     (6-hour package)
- eight_hour_price   (8-hour package)
- ten_hour_price     (10-hour package)
- twelve_hour_price  (12-hour package)
```

### API Response

```json
{
    "success": true,
    "guides": [
        {
            "guide_id": 1,
            "name": "John Doe",
            "city": "Singapore",
            "country": "Singapore",
            "day_rate": 150.00,
            "hourly_price": 15.00,
            "two_hour_price": 30.00,
            "four_hour_price": 60.00,
            "six_hour_price": 90.00,
            "eight_hour_price": 120.00,
            "ten_hour_price": 140.00,
            "twelve_hour_price": 150.00,
            "languages": [
                {
                    "language": "English",
                    "proficiency": "Native"
                },
                {
                    "language": "Mandarin",
                    "proficiency": "Fluent"
                }
            ]
        }
    ],
    "count": 1
}
```

### Frontend Display

```html
<tr>
    <td><input type="checkbox"></td>
    <td>John Doe</td>
    <td>English (Native), Mandarin (Fluent)</td>
    <td>
        <select class="guide-hours">
            <option value="2">2 Hours</option>
            <option value="4">4 Hours</option>
            <option value="6">6 Hours</option>
            <option value="8">8 Hours</option>
            <option value="10">10 Hours</option>
            <option value="12" selected>12 Hours</option>
        </select>
    </td>
    <td><input value="150.00" readonly> <!-- Cost --></td>
    <td><input value="150.00"> <!-- Sell Price --></td>
</tr>
```

### Dynamic Pricing Update

When user changes hours, `updateGuidePricing()` function updates the price:

```javascript
function updateGuidePricing(guideId, twoHourPrice, fourHourPrice, ...) {
    const hours = parseInt(hoursSelect.value);
    let price = 0;
    
    switch(hours) {
        case 2: price = twoHourPrice; break;
        case 4: price = fourHourPrice; break;
        case 6: price = sixHourPrice; break;
        case 8: price = eightHourPrice; break;
        case 10: price = tenHourPrice; break;
        case 12: price = twelveHourPrice; break;
    }
    
    costInput.value = price.toFixed(2);
    sellInput.value = price.toFixed(2);  // Can be edited by user
}
```

## Fix Applied

### Problem
Role 35 (DMC Product Level) and other DMC-related roles were missing from the guide filtering logic.

### Solution
Added missing roles to the condition:

**Before:**
```php
elseif (in_array($user->role_id, [33, 34, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138])) {
    $dmc_id = $user->created_by;
}
```

**After:**
```php
elseif (in_array($user->role_id, [33, 34, 35, 77, 78, 84, 120, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140])) {
    $dmc_id = $user->created_by;
}
```

## Verification Checklist

### ✅ DMC Filtering
- [x] Guides filtered by `dmc_id` column
- [x] Only guides belonging to user's DMC are shown
- [x] Role 35 and other DMC roles supported
- [x] Logging added for debugging

### ✅ Pricing Accuracy
- [x] All pricing tiers loaded from database
- [x] Prices displayed correctly in table
- [x] Prices update when hours selection changes
- [x] Cost field is readonly (from database)
- [x] Sell field is editable (can add markup)

### ✅ Data Flow
- [x] Frontend calls correct API endpoint
- [x] Backend filters by DMC and destination
- [x] Response includes all pricing data
- [x] Frontend displays prices correctly
- [x] Price updates work dynamically

## Testing

### Test DMC Filtering:

1. **Login as DMC User (role 11):**
   ```
   User ID: 5
   Role: 11 (DMC)
   → Should see guides where dmc_id = 5
   ```

2. **Login as Product Level (role 35):**
   ```
   User ID: 8
   Role: 35
   Created By: 5
   → Should see guides where dmc_id = 5
   ```

3. **Verify in Database:**
   ```sql
   -- Check which guides belong to DMC
   SELECT guide_id, name, dmc_id, city
   FROM guides
   WHERE dmc_id = 5 AND status = 1;
   ```

### Test Pricing:

1. **Check Database Values:**
   ```sql
   SELECT 
       guide_id,
       name,
       two_hour_price,
       four_hour_price,
       six_hour_price,
       eight_hour_price,
       ten_hour_price,
       twelve_hour_price
   FROM guides
   WHERE guide_id = 1;
   ```

2. **Open Guide Modal:**
   - Select destination
   - Verify guide appears
   - Check default price (12 hours)

3. **Change Hours:**
   - Select "2 Hours" → Price should change to `two_hour_price`
   - Select "4 Hours" → Price should change to `four_hour_price`
   - Select "8 Hours" → Price should change to `eight_hour_price`

4. **Verify Prices Match Database:**
   - Cost field should show exact database value
   - Sell field should default to same value (editable)

### Test Logging:

Check `storage/logs/laravel.log` for:

```
[2025-12-24] local.INFO: Guide request received
{
    "destination": "Singapore",
    "user_id": 8,
    "role_id": 35
}

[2025-12-24] local.INFO: getGuidesByDestination - DMC ID determined
{
    "dmc_id": 5,
    "user_id": 8,
    "role_id": 35,
    "created_by": 5
}

[2025-12-24] local.INFO: Guides found
{
    "count": 3
}
```

## Database Schema

### guides Table:
```sql
CREATE TABLE guides (
    guide_id BIGSERIAL PRIMARY KEY,
    dmc_id BIGINT NOT NULL,           -- ✅ Filters by this
    name VARCHAR(255),
    city VARCHAR(255),                -- ✅ Filters by destination
    country VARCHAR(255),
    status INTEGER DEFAULT 1,         -- ✅ Only active (1)
    day_rate DECIMAL(10,2),
    hourly_price DECIMAL(10,2),
    two_hour_price DECIMAL(10,2),     -- ✅ All pricing tiers
    four_hour_price DECIMAL(10,2),
    six_hour_price DECIMAL(10,2),
    eight_hour_price DECIMAL(10,2),
    ten_hour_price DECIMAL(10,2),
    twelve_hour_price DECIMAL(10,2),
    deleted_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### guide_languages Table:
```sql
CREATE TABLE guide_languages (
    id BIGSERIAL PRIMARY KEY,
    guide_id BIGINT REFERENCES guides(guide_id),
    language VARCHAR(100),
    proficiency VARCHAR(50)
);
```

## Status

✅ **DMC FILTERING** - Guides correctly filtered by DMC ID
✅ **ROLE SUPPORT** - All DMC roles including 35 now supported
✅ **PRICING** - All pricing tiers loaded and displayed correctly
✅ **DYNAMIC UPDATES** - Prices update when hours selection changes
✅ **LOGGING** - Comprehensive logging for debugging
✅ **TESTED** - Verified with database queries

## Files Modified

1. `app/Http/Controllers/EnquiryFormPro.php`
   - Method: `getGuidesByDestination()`
   - Line 479: Added role 35 and other missing roles
   - Line 492: Enhanced logging

## Related Files

- `resources/views/enquiryform_pro/create.blade.php` - Frontend guide modal
- `app/Models/Guide.php` - Guide model
- `routes/web.php` - Route definition
- `MISCELLANEOUS_ROLE_35_FIX.md` - Similar fix for miscellaneous items

