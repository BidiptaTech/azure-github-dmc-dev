# Debug Guide: Hotels, Attractions & Restaurants Issues

## Reported Issues

1. **Can't select hotel** - Hotel dropdown not working
2. **Attraction prices not showing** - Prices appear as 0 or blank
3. **Uncertain about data** - Not sure if restaurants, attractions, and hotels belong to correct DMC ID
4. **Price verification needed** - Need to confirm all prices are correct

## Debugging Steps Added

### 1. Enhanced Logging (Backend)

#### Hotels API (`getHotelsByDestination`)
```php
\Log::info('getHotelsByDestination - DMC ID determined', [
    'dmc_id' => $dmc_id,
    'user_id' => $user->userId,
    'role_id' => $user->role_id,
    'destination' => $destination
]);

\Log::info('getHotelsByDestination - Hotels found', [
    'count' => $hotels->count(),
    'hotel_ids' => $hotels->pluck('id')->toArray(),
    'hotel_names' => $hotels->pluck('name')->toArray()
]);
```

#### Attractions API (`getAttractionsByDestination`)
```php
\Log::info('getAttractionsByDestination - DMC ID determined', [
    'dmc_id' => $dmc_id,
    'user_id' => $user->userId,
    'role_id' => $user->role_id,
    'destination' => $destination
]);

\Log::info('getAttractionsByDestination - Attractions found', [
    'count' => $attractions->count(),
    'attraction_ids' => $attractions->pluck('id')->toArray(),
    'attraction_names' => $attractions->pluck('name')->toArray(),
    'prices' => $attractions->map(function($a) {
        return [
            'id' => $a->id,
            'name' => $a->name,
            'adult_price' => $a->adult_price,
            'child_price' => $a->child_price
        ];
    })->toArray()
]);
```

#### Restaurants (in `create()` method)
```php
\Log::info('EnquiryFormPro create() - Restaurants loaded', [
    'dmc_id' => $dmc_id,
    'count' => $restaurants->count(),
    'restaurant_ids' => $restaurants->pluck('restaurant_id')->toArray(),
    'restaurant_names' => $restaurants->pluck('name')->toArray()
]);

\Log::info('EnquiryFormPro create() - Meals loaded', [
    'dmc_id' => $dmc_id,
    'meal_count' => $meals->count(),
    'restaurant_count' => count($restaurantIds)
]);
```

### 2. Enhanced Console Logging (Frontend)

#### Hotels
```javascript
console.log('Loading hotels for destination:', destination);
console.log('Hotels API response data:', data);
console.log('Hotels count:', data.hotels ? data.hotels.length : 0);
console.log('DMC ID:', data.dmc_id);
console.log('Adding hotel:', hotel.name, 'ID:', hotel.id, 'Rooms:', hotel.rooms ? hotel.rooms.length : 0);
```

#### Attractions
```javascript
console.log('Loading attractions for destination:', destination);
console.log('Attractions API response data:', data);
console.log('Attractions count:', data.count);
console.log('DMC ID:', data.dmc_id);
console.log('Attraction:', attr.name, 'Adult Price:', attr.adult_price, 'Child Price:', attr.child_price);
```

### 3. API Response Enhancement

All API responses now include:
- `dmc_id` - So you can verify which DMC's data is being returned
- `destination` - To confirm the destination filter
- Detailed data arrays

## How to Debug

### Step 1: Check Browser Console

1. Open the Enquiry Form Pro page
2. Open browser DevTools (F12)
3. Go to Console tab
4. Select a destination in any section (Hotels/Attractions/Restaurants)
5. Look for console logs:

**For Hotels:**
```
Loading hotels for destination: Singapore
Hotels API response status: 200
Hotels API response data: {success: true, hotels: [...], dmc_id: 4, destination: "Singapore"}
Hotels count: 5
DMC ID: 4
Adding hotel: Grand Hotel ID: 1 Rooms: 3
Adding hotel: Marina Bay Hotel ID: 2 Rooms: 5
...
Hotels loaded successfully. Total: 5
```

**For Attractions:**
```
Loading attractions for destination: Singapore
Attractions API response status: 200
Attractions API response data: {success: true, attractions: [...], count: 10, dmc_id: 4}
Attractions count: 10
DMC ID: 4
Attraction: Universal Studios Adult Price: 79.00 Child Price: 59.00
Attraction: Gardens by the Bay Adult Price: 28.00 Child Price: 15.00
...
```

### Step 2: Check Laravel Logs

```powershell
cd c:\xampp\htdocs\azure_new_files
Get-Content storage/logs/laravel.log -Tail 50
```

Look for entries like:
```
[2025-12-24 12:00:00] local.INFO: EnquiryFormPro create() - DMC ID determined 
{"dmc_id":4,"user_id":8,"role_id":"35","created_by":4}

[2025-12-24 12:00:01] local.INFO: EnquiryFormPro create() - Restaurants loaded 
{"dmc_id":4,"count":8,"restaurant_ids":[1,2,3,4,5,6,7,8],"restaurant_names":["Restaurant A","Restaurant B",...]}

[2025-12-24 12:00:02] local.INFO: getHotelsByDestination - DMC ID determined 
{"dmc_id":4,"user_id":8,"role_id":"35","destination":"Singapore"}

[2025-12-24 12:00:03] local.INFO: getHotelsByDestination - Hotels found 
{"count":5,"hotel_ids":[1,2,3,4,5],"hotel_names":["Hotel A","Hotel B",...]}
```

### Step 3: Use Debug Route

Navigate to: `http://localhost/azure_new_files/public/debug/dmc-data`

This will show you all data for DMC ID 4:
```json
{
    "dmc_id": 4,
    "hotels": [
        {
            "id": 1,
            "name": "Grand Hotel",
            "dmc_id": [4, 5],
            "city": "Singapore"
        }
    ],
    "attractions": [
        {
            "attraction_id": 1,
            "name": "Universal Studios",
            "dmc_id": [4],
            "location": "Singapore",
            "adult_price": "79.00",
            "child_price": "59.00"
        }
    ],
    "restaurants": [
        {
            "restaurant_id": 1,
            "name": "Seafood Paradise",
            "dmc_id": [4, 5],
            "city": "Singapore"
        }
    ]
}
```

### Step 4: Verify Database Directly

#### Check Hotels for DMC 4:
```sql
SELECT id, name, dmc_id, city, status, is_active, is_complete
FROM hotels
WHERE dmc_id::jsonb @> '4'
  AND status = 1
  AND is_active = true
  AND is_complete = true
ORDER BY name;
```

#### Check Attractions for DMC 4:
```sql
SELECT attraction_id, name, dmc_id, location, adult_price, child_price, status
FROM attractions
WHERE dmc_id::jsonb @> '4'
  AND status = 1
ORDER BY name;
```

#### Check Restaurants for DMC 4:
```sql
SELECT restaurant_id, name, dmc_id, city, status
FROM restaurants
WHERE dmc_id::jsonb @> '4'
  AND status = 1
ORDER BY name;
```

#### Check Meals with Prices:
```sql
SELECT 
    m.meal_id,
    m.restaurant_id,
    r.name as restaurant_name,
    m.name as meal_name,
    m.type,
    m.adult_price,
    m.child_price,
    m.price
FROM meals m
JOIN restaurants r ON r.restaurant_id = m.restaurant_id
WHERE r.dmc_id::jsonb @> '4'
  AND r.status = 1
ORDER BY r.name, m.name;
```

## Common Issues & Solutions

### Issue 1: No Hotels Showing

**Possible Causes:**
1. No hotels assigned to DMC ID 4
2. Hotels not marked as `is_active` or `is_complete`
3. Hotels in different city than selected destination
4. DMC ID filtering issue

**Check:**
```sql
-- See all hotels regardless of filters
SELECT id, name, dmc_id, city, status, is_active, is_complete
FROM hotels
WHERE dmc_id::jsonb @> '4';

-- If this returns 0 rows, DMC 4 has no hotels assigned
-- If it returns rows but they have is_active=false or is_complete=false, that's the issue
```

**Solution:**
- Assign hotels to DMC 4 in hotel management
- Ensure hotels are marked as active and complete
- Verify hotel city matches destination

### Issue 2: Attraction Prices Showing as 0

**Possible Causes:**
1. Prices not set in database (NULL or 0)
2. Wrong column being read
3. Data type issue

**Check:**
```sql
SELECT 
    attraction_id,
    name,
    adult_price,
    child_price,
    senior_adult_price
FROM attractions
WHERE dmc_id::jsonb @> '4'
  AND status = 1;
```

**Solution:**
- Update attraction prices in attraction management
- Ensure prices are decimal/numeric, not NULL

### Issue 3: Can't Select Hotel

**Possible Causes:**
1. JavaScript error preventing dropdown population
2. API returning empty response
3. Hotel dropdown disabled
4. Rooms not configured for hotel

**Check:**
1. Browser console for JavaScript errors
2. Network tab in DevTools - check API response
3. Verify hotels have rooms configured

**Solution:**
- Check console logs for specific error
- Verify API response includes hotels with rooms
- Ensure hotel rooms are marked as active

### Issue 4: Wrong DMC Data Showing

**Possible Causes:**
1. DMC ID determination logic incorrect
2. User role not in the role array
3. `created_by` field incorrect

**Check Logs:**
```
[2025-12-24 12:00:00] local.INFO: EnquiryFormPro create() - DMC ID determined 
{"dmc_id":4,"user_id":8,"role_id":"35","created_by":4}
```

**Verify:**
- `dmc_id` should be 4 for your DMC
- `role_id` should be 35 (or appropriate role)
- `created_by` should point to DMC user

**Solution:**
- If `dmc_id` is wrong, check user's `created_by` field in database
- If role not supported, add to role array in controller

## Verification Checklist

### Hotels ✓
- [ ] Browser console shows hotels loading
- [ ] DMC ID in console matches your DMC (4)
- [ ] Hotel count > 0
- [ ] Hotels appear in dropdown
- [ ] Can select a hotel
- [ ] Rooms appear after selection
- [ ] Room prices visible

### Attractions ✓
- [ ] Browser console shows attractions loading
- [ ] DMC ID in console matches your DMC (4)
- [ ] Attraction count > 0
- [ ] Attractions appear in table
- [ ] Adult prices show correctly (not 0)
- [ ] Child prices show correctly (not 0)
- [ ] Prices match database

### Restaurants ✓
- [ ] Laravel log shows restaurants loaded
- [ ] Restaurant count > 0
- [ ] Restaurants appear in dropdown
- [ ] Can select a restaurant
- [ ] Meals appear for restaurant
- [ ] Meal prices visible
- [ ] Prices match database

## Files Modified

### Backend:
1. `app/Http/Controllers/EnquiryFormPro.php`
   - Added logging to `getHotelsByDestination()`
   - Added logging to `getAttractionsByDestination()`
   - Added logging to `create()` for restaurants and attractions
   - Enhanced API responses with `dmc_id` and `destination`

2. `routes/web.php`
   - Added debug route `/debug/dmc-data`

### Frontend:
3. `resources/views/enquiryform_pro/create.blade.php`
   - Added console logging to `loadHotelsByDestination()`
   - Added console logging to `loadAttractionsByDestination()`
   - Enhanced error messages with DMC ID info

## Next Steps

1. **Test the page** - Open Enquiry Form Pro
2. **Check console** - Look for the new console logs
3. **Check logs** - Review Laravel logs for backend data
4. **Verify data** - Use debug route to see what data exists
5. **Report findings** - Share console output and log entries

## Expected Output

### If Everything Works:

**Console:**
```
Loading hotels for destination: Singapore
Hotels API response data: {...}
Hotels count: 5
DMC ID: 4
Adding hotel: Grand Hotel ID: 1 Rooms: 3
Hotels loaded successfully. Total: 5

Loading attractions for destination: Singapore
Attractions count: 10
DMC ID: 4
Attraction: Universal Studios Adult Price: 79.00 Child Price: 59.00
```

**Logs:**
```
[INFO] getHotelsByDestination - Hotels found {"count":5,"hotel_ids":[1,2,3,4,5]}
[INFO] getAttractionsByDestination - Attractions found {"count":10,"prices":[...]}
```

### If No Data:

**Console:**
```
Loading hotels for destination: Singapore
Hotels count: 0
No hotels available for this destination and your DMC
```

**Logs:**
```
[INFO] getHotelsByDestination - Hotels found {"count":0,"hotel_ids":[]}
```

**Action:** Check database - DMC 4 may not have any hotels assigned for Singapore.

## Support

If issues persist after following this guide:

1. **Collect Data:**
   - Browser console output (full)
   - Laravel log entries (last 100 lines)
   - Debug route output
   - Database query results

2. **Check:**
   - User role ID
   - DMC ID being used
   - Destination being selected
   - Data actually exists in database

3. **Common Fixes:**
   - Clear browser cache
   - Clear Laravel cache: `php artisan cache:clear`
   - Restart server
   - Check database connections

