# Local Transfer Zone Price Fix

## Issue Reported
In the create pro form, the local transfer popup was showing zone price (40) for "Crowne Plaza Changi Airport by IHG → Marina South Pier" transfer, even though Marina South Pier doesn't have a zone mapping for the specific DMC ID.

The user reported: "local transfer zone pricing not working correctly, restaurant, hotel, attraction transfer zone pricing working perfect, please follow that and fix it"

## Root Cause Analysis

### Original Issue (First Fix)
The local transfer popup was not correctly fetching zone prices because:
1. **Missing DMC ID**: The JavaScript code was not passing `dmc_id` to the API endpoint
2. **No Zone Extraction**: The `getZonePrices` function was using hotel_unique_id, attraction_id, and restaurant_id directly instead of extracting zone_id from zone_assignments

### Current Issue (Second Fix - THE REAL PROBLEM!)
After comparing with working hotel/attraction/restaurant popups, found the root cause:
- **Missing `data-zone-id` attributes** in local pickup and drop dropdowns
- Hotel/attraction/restaurant popups have `data-zone-id="{{ $hotel->zone_id ?? '' }}"` on their options
- Local transfer dropdowns were **missing** these `data-zone-id` attributes
- The `fetchZonePrice()` function relies on extracting `zone_id` from the dropdown's `data-zone-id` attribute first
- Without this attribute, it was using entity IDs (hotel_unique_id, attraction_id, etc.) directly, causing incorrect lookups

## Fixes Applied

### Fix 1: Add `data-zone-id` Attributes to Local Transfer Dropdowns (create.blade.php - Lines 2938-2978) ⭐ KEY FIX

Added the missing `data-zone-id` attributes to match hotel/attraction/restaurant popup behavior:

**Local Pickup Dropdown** (lines 2938-2952):
```blade
<optgroup label="Hotels">
    @foreach($hotels as $hotel)
        <option value="{{ $hotel->hotel_unique_id }}" 
                data-name="{{ $hotel->name }}" 
                data-type="hotel" 
                data-hotel-unique-id="{{ $hotel->hotel_unique_id }}" 
                data-zone-id="{{ $hotel->zone_id ?? '' }}"  <!-- ADDED THIS -->
                data-city="{{ $hotel->city ?? '' }}" 
                data-country="{{ $hotel->country ?? '' }}">
            {{ $hotel->name }}
        </option>
    @endforeach
</optgroup>
<optgroup label="Attractions">
    @foreach($attractions as $attr)
        <option value="{{ $attr->attraction_id }}" 
                data-name="{{ $attr->name }}" 
                data-type="attraction" 
                data-attraction-id="{{ $attr->attraction_id }}" 
                data-zone-id="{{ $attr->zone_id ?? '' }}"  <!-- ADDED THIS -->
                data-location="{{ $attr->location ?? '' }}" 
                data-country="{{ $attr->country ?? '' }}">
            {{ $attr->name }}
        </option>
    @endforeach
</optgroup>
<optgroup label="Restaurants">
    @foreach($restaurants as $rest)
        <option value="{{ $rest->restaurant_id }}" 
                data-name="{{ $rest->name }}" 
                data-type="restaurant" 
                data-restaurant-id="{{ $rest->restaurant_id }}" 
                data-zone-id="{{ $rest->zone_id ?? '' }}"  <!-- ADDED THIS -->
                data-city="{{ $rest->city ?? '' }}" 
                data-country="{{ $rest->country ?? '' }}">
            {{ $rest->name }}
        </option>
    @endforeach
</optgroup>
```

**Local Drop Dropdown** (lines 2964-2978): Same attributes added

This matches exactly how hotel/attraction/restaurant transfer popups work (see line 1864, 1869 for comparison).

### Fix 2: Add DMC ID Parameter (create.blade.php - Line ~19593)
```javascript
const apiUrl = `{{ route('enquiry-form-pro.get-zone-prices') }}?vehicle_id=${encodeURIComponent(vehicleId)}&pickup_id=${encodeURIComponent(actualPickupId)}&drop_id=${encodeURIComponent(actualDropId)}&pickup_type=${encodeURIComponent(actualPickupType)}&drop_type=${encodeURIComponent(actualDropType)}&dmc_id=${encodeURIComponent(dmcId)}`;
```

### Fix 3: Add Vehicle DMC Validation (EnquiryFormPro.php) ⭐ CRITICAL FOR PORT-TO-PORT

Added validation to ensure the vehicle belongs to the requesting DMC:

```php
// Verify that the vehicle belongs to this DMC (important for port-to-port transfers)
if ($dmcId) {
    $vehicle = \App\Models\Vehicle::where('vehicle_id', $vehicleId)->first();
    if (!$vehicle || $vehicle->dmc_id != $dmcId) {
        return response()->json([
            'success' => false,
            'message' => 'Vehicle not found for this DMC',
            'data' => ['private_price' => 0, 'shared_price' => 0]
        ]);
    }
}
```

**Why this is critical for port-to-port transfers:**
- Ports don't have DMC-specific zone assignments (they're global)
- But vehicles ARE DMC-specific
- By checking vehicle ownership, we ensure DMC isolation even for port-to-port transfers
- If another DMC created a mapping for the same ports with a different vehicle, it won't affect your DMC

### Fix 4: Extract Zone IDs from zone_assignments (EnquiryFormPro.php)
Updated `getZonePrices()` function to:

1. **Accept dmc_id parameter**
2. **Extract zone_id for Hotels**:
   ```php
   if ($fromZoneType === 'Hotel' && $dmcId) {
       $hotel = \App\Models\Hotel::where('hotel_unique_id', $pickupId)->first();
       if ($hotel) {
           $zoneId = $hotel->getZoneForDmc($dmcId);
           if ($zoneId) {
               $fromZoneId = $zoneId;
           }
       }
   }
   ```

3. **Extract zone_id for Attractions**:
   ```php
   if ($fromZoneType === 'Attraction' && $dmcId) {
       $attraction = \App\Models\Attraction::where('attraction_id', $pickupId)->first();
       if ($attraction) {
           $zoneId = $attraction->getZoneForDmc($dmcId);
           if ($zoneId) {
               $fromZoneId = $zoneId;
           }
       }
   }
   ```

4. **Extract zone_id for Restaurants**:
   ```php
   if ($fromZoneType === 'Restaurant' && $dmcId) {
       $restaurant = \App\Models\Restaurant::where('restaurant_id', $pickupId)->first();
       if ($restaurant) {
           $zoneId = $restaurant->getZoneForDmc($dmcId);
           if ($zoneId) {
               $fromZoneId = $zoneId;
           }
       }
   }
   ```

5. **Validate zone extraction**:
   ```php
   // If we couldn't extract zone_id for hotels/attractions/restaurants, return zero prices
   if (($fromZoneType !== 'Port' && !$fromZoneId) || ($toZoneType !== 'Port' && !$toZoneId)) {
       return response()->json([
           'success' => false,
           'message' => 'No zone assignment found for selected location with this DMC',
           'data' => ['private_price' => 0, 'shared_price' => 0]
       ]);
   }
   ```

6. **Added comprehensive logging**:
   - Log extracted zone IDs before lookup
   - Log when zone mapping is not found
   - Log when zone mapping is found with details

## How Ports Work (CRITICAL FOR PORT-TO-PORT TRANSFERS)
- **Port Structure**: Ports use `port_id` directly as `zone_id` in `vehicle_zone_mappings`
- **No zone_assignments**: Unlike hotels, attractions, and restaurants, ports don't have a `zone_assignments` JSON column
- **Direct Mapping**: The `port_id` IS the zone identifier in the vehicle_zone_mappings table
- **Port Types**: Airports, Seaports, LandPorts, Railway, BusStand are all categorized as "ports"
  - Example: "Changi Airport" is a port (type: Airport)
  - Example: "Marina South Pier" is a port (type: Seaport)

### Port-to-Port Transfer Issue

**The Problem**: When BOTH locations are ports (e.g., Airport → Seaport):
1. Both use `port_id` directly as `zone_id`
2. Ports are global entities (not DMC-specific)
3. The `vehicle_zone_mappings` table doesn't have a `dmc_id` column
4. This means port-to-port mappings could be shared across DMCs if they use the same vehicle type

**The Solution** (Fix 4): 
- Verify that the vehicle belongs to the requesting DMC before returning zone prices
- Since vehicles ARE DMC-specific (have `dmc_id` column), this ensures DMC isolation
- If the vehicle doesn't belong to the requesting DMC, return zero price

## Debugging the Current Issue

To debug why "Marina South Pier" is showing price 40:

1. **Check the logs** in `storage/logs/laravel.log`:
   ```
   grep "Final zone IDs for vehicle_zone_mappings lookup" storage/logs/laravel.log
   grep "Vehicle zone mapping found" storage/logs/laravel.log
   ```

2. **Expected log output**:
   ```
   Final zone IDs for vehicle_zone_mappings lookup: 
   {
       "vehicle_id": "xxx",
       "from_zone_id": "hotel_zone_id",
       "to_zone_id": "port_id",
       "from_zone_type": "Hotel",
       "to_zone_type": "Port",
       "dmc_id": "your_dmc_id"
   }
   ```

3. **Check database directly**:
   ```sql
   -- Check if Marina South Pier has a mapping for this vehicle
   SELECT * FROM vehicle_zone_mappings 
   WHERE vehicle_id = 'vehicle_id'
   AND (
       (from_zone_id = 'hotel_zone' AND to_zone_id = 'marina_south_pier_port_id')
       OR
       (from_zone_id = 'marina_south_pier_port_id' AND to_zone_id = 'hotel_zone')
   )
   AND deleted_at IS NULL;
   ```

4. **Check Hotel Zone Assignment**:
   ```sql
   -- Check if Crowne Plaza has zone assignment for this DMC
   SELECT hotel_unique_id, name, zone_assignments 
   FROM hotels 
   WHERE name LIKE '%Crowne Plaza Changi%';
   ```

5. **Check Port Details**:
   ```sql
   -- Get Marina South Pier port_id
   SELECT port_id, port_name, type, country 
   FROM ports 
   WHERE port_name LIKE '%Marina South%';
   ```

## How The Fix Works

### Before Fix:
1. User selects "Crowne Plaza Changi Airport" (hotel) and "Marina South Pier" (port)
2. Local pickup/drop options **didn't have** `data-zone-id` attribute
3. `fetchZonePrice()` function couldn't extract zone_id from dropdown
4. Falls back to using `hotel_unique_id` or `port_id` directly
5. Backend tries to lookup with wrong IDs, might find unrelated mappings
6. Returns incorrect price of 40

### After Fix:
1. User selects "Crowne Plaza Changi Airport" (hotel) and "Marina South Pier" (port)
2. Local pickup/drop options **now have** `data-zone-id="{{ $hotel->zone_id }}"` attribute
3. `fetchZonePrice()` function extracts correct zone_id from dropdown's data attribute (lines 19091-19096 for hotels)
4. Backend receives correct zone_id for the specific DMC
5. If no zone assignment exists for that DMC, zone_id will be null/empty
6. Backend returns zero price if zone_id is missing (validation added at line ~2042)
7. User sees correct price or zero if no mapping exists

## Why It Matches Hotel/Attraction/Restaurant Behavior

All three working popups have `data-zone-id` on their dropdowns:
- **Hotel Transfer Destination** (line 1864): `data-zone-id="{{ $hotel->zone_id ?? '' }}"`
- **Attraction Transfer** (line 1869): `data-zone-id="{{ $attr->zone_id ?? '' }}"`
- **Restaurant Transfer**: Similar pattern

The `fetchZonePrice()` function logic (lines 19055-19257) checks for `data-zone-id` first:
```javascript
const zoneId = pickupOption.getAttribute('data-zone-id');
if (zoneId) {
    console.log('Found zone_id for pickup from dropdown:', zoneId);
    actualPickupId = zoneId;  // Use the zone_id
    foundHotelUniqueId = true;
    break;
}
```

Without `data-zone-id`, it tries complex fallback logic that may not work correctly.

## Testing

To verify the fix:
1. Clear browser cache
2. Reload the create pro form page
3. Try adding local transfer: Crowne Plaza Changi → Marina South Pier
4. Check browser console logs for: `"Found zone_id for pickup from dropdown"`
5. If Marina South Pier has no zone for your DMC, you should see zero price
6. If there IS a valid mapping, you should see the correct zone price

## Files Modified
1. `resources/views/enquiryform_pro/create.blade.php` - Added data-zone-id attributes to local pickup/drop dropdowns AND added dmc_id to API call
2. `app/Http/Controllers/EnquiryFormPro.php` - Enhanced getZonePrices() with zone extraction and validation

