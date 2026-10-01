# Arrival/Departure Port & Vehicle Filtering Fix

## Issues Reported
1. ❌ Arrival/departure port still not showing in destination dropdown
2. ❌ When changing from Shared to Private (or vice versa), the vehicle type is not changing/filtering in arrival and departure popups

## Root Causes Identified

### Issue 1: Missing `data-port-id` in Arrival Destination Dropdown
**Problem:** 
- The arrival port dropdown (line 1954) had `data-port-id="{{ $port->port_id }}"`
- The departure port dropdown (line 2064) had `data-port-id="{{ $port->port_id }}"`
- BUT the arrival **destination** dropdown ports (line 1979) were missing this attribute
- This caused the port default value auto-selection logic to fail

**Location:** `resources/views/enquiryform_pro/create.blade.php` line ~1979

**Fix Applied:**
Added `data-port-id="{{ $port->port_id }}"` to the ports in the arrival destination dropdown.

### Issue 2: Missing `data-sharable` Attribute on Vehicle Options
**Problem:**
- Vehicle options in both arrival (lines 2009-2013) and departure (lines 2119-2123) dropdowns were missing the `data-sharable` attribute
- This attribute is critical for filtering vehicles based on whether they support Private, Shared, or Both service types
- Without it, the UI couldn't determine which vehicles to show/hide when switching service types

**Vehicle Sharable Values:**
- **1** = Private only
- **2** = Shared only
- **3** = Both (can be used as either Private or Shared)

### Issue 3: No JavaScript Logic to Filter Vehicles
**Problem:**
- The service type dropdowns (`arrivalTransferType` and `departureTransferType`) had no `onchange` event handlers
- There was no JavaScript function to filter the vehicle dropdown based on the selected service type
- When users switched from "Private" to "Shared" (or vice versa), all vehicles remained visible regardless of their sharable property

## Files Modified

### 1. `resources/views/enquiryform_pro/create.blade.php`

#### Change 1: Added `data-port-id` to Arrival Destination Ports
**Line ~1979:**
```blade
<!-- BEFORE -->
<option value="{{ $port->id }}" data-name="{{ $port->port_name }}" data-type="port" data-country="{{ $port->country }}">{{ $port->port_name }}</option>

<!-- AFTER -->
<option value="{{ $port->id }}" data-name="{{ $port->port_name }}" data-type="port" data-port-id="{{ $port->port_id }}" data-country="{{ $port->country }}">{{ $port->port_name }}</option>
```

#### Change 2: Added `data-sharable` to Arrival Vehicle Options
**Lines ~2009-2013:**
```blade
<!-- BEFORE -->
<option value="{{ $vehicle->vehicle_id }}" 
    data-type="{{ $vehicle->vehicle_type }}"
    data-seating="{{ $vehicle->seating_capacity }}"
    data-base-price="{{ $vehicle->base_price ?? 0 }}"
    data-sharable-price="{{ $vehicle->sharable_base_price ?? 0 }}">

<!-- AFTER -->
<option value="{{ $vehicle->vehicle_id }}" 
    data-type="{{ $vehicle->vehicle_type }}"
    data-seating="{{ $vehicle->seating_capacity }}"
    data-base-price="{{ $vehicle->base_price ?? 0 }}"
    data-sharable-price="{{ $vehicle->sharable_base_price ?? 0 }}"
    data-sharable="{{ $vehicle->sharable ?? 1 }}">
```

#### Change 3: Added `data-sharable` to Departure Vehicle Options
**Lines ~2119-2123:**
Same as Change 2 above, but for the departure vehicle dropdown.

#### Change 4: Added `onchange` Events to Service Type Dropdowns
**Arrival Transfer Type (Line ~2043):**
```blade
<!-- BEFORE -->
<select class="form-select form-select-sm" id="arrivalTransferType" style="font-size: 10px;">

<!-- AFTER -->
<select class="form-select form-select-sm" id="arrivalTransferType" style="font-size: 10px;" onchange="filterArrivalVehiclesByServiceType()">
```

**Departure Transfer Type (Line ~2154):**
```blade
<!-- BEFORE -->
<select class="form-select form-select-sm" id="departureTransferType" style="font-size: 10px;">

<!-- AFTER -->
<select class="form-select form-select-sm" id="departureTransferType" style="font-size: 10px;" onchange="filterDepartureVehiclesByServiceType()">
```

#### Change 5: Added JavaScript Filter Functions
**Location:** After `toggleDepartureTransferFields()` function (~line 3940)

**New Functions Added:**

1. **`filterArrivalVehiclesByServiceType()`**
   - Filters arrival vehicle dropdown based on selected service type (P/S)
   - Shows only vehicles where sharable property matches the service type:
     - Private (P): Shows vehicles with sharable = 1 or 3
     - Shared (S): Shows vehicles with sharable = 2 or 3
   - Hides optgroups if all their vehicles are filtered out
   - Clears selection if currently selected vehicle becomes hidden
   - Triggers pricing update if selection changes

2. **`filterDepartureVehiclesByServiceType()`**
   - Same functionality as arrival filter but for departure vehicles

#### Change 6: Updated Toggle Functions to Auto-Filter
**`toggleArrivalTransferFields()`:**
```javascript
// Added this when transfer is checked:
setTimeout(() => filterArrivalVehiclesByServiceType(), 100);
```

**`toggleDepartureTransferFields()`:**
```javascript
// Added this when transfer is checked:
setTimeout(() => filterDepartureVehiclesByServiceType(), 100);
```

This ensures vehicles are filtered immediately when the transfer checkbox is enabled, based on the default service type (Shared).

## How the Fix Works

### Port Auto-Selection
1. When default port value exists, the JavaScript looks for matching options
2. It now checks BOTH `data-port-id` and `value` attributes
3. Since `data-port-id` is now present on all port options (including destination dropdown), the match succeeds
4. Port gets auto-selected properly

### Vehicle Filtering by Service Type
1. User selects service type (Private or Shared)
2. `filterArrivalVehiclesByServiceType()` or `filterDepartureVehiclesByServiceType()` is called
3. Function reads the `data-sharable` attribute from each vehicle option
4. Based on the service type selected:
   - **Private (P)**: Only shows vehicles with `sharable = 1` (Private only) or `sharable = 3` (Both)
   - **Shared (S)**: Only shows vehicles with `sharable = 2` (Shared only) or `sharable = 3` (Both)
5. Options are hidden/shown using `display` style property
6. Optgroups are also hidden if all their vehicles are filtered out
7. If the currently selected vehicle is now hidden, the selection is cleared and pricing is updated

## Testing Instructions

### Test 1: Port Selection
1. Go to Enquiry Form Pro → Create
2. Add an accommodation
3. Check the "Transfer" checkbox for arrival
4. Click on the Destination dropdown
5. **Expected:** Ports should appear in the dropdown with the default port (if configured) auto-selected

### Test 2: Vehicle Filtering - Arrival
1. In the arrival section, check "Transfer"
2. The default service type is "Shared"
3. Open the "Vehicle Type" dropdown
4. **Expected:** Only vehicles with `sharable = 2` (Shared) or `sharable = 3` (Both) should be visible
5. Change service type to "Private"
6. **Expected:** Vehicle dropdown should update to show only vehicles with `sharable = 1` (Private) or `sharable = 3` (Both)
7. If a vehicle was selected and is no longer compatible, it should be cleared

### Test 3: Vehicle Filtering - Departure
1. Same as Test 2, but in the departure section
2. Should behave identically to arrival

### Test 4: Vehicle Selection Persistence
1. Select service type "Private" and choose a vehicle that supports private only (`sharable = 1`)
2. Switch to "Shared"
3. **Expected:** The selected vehicle should be cleared since it doesn't support shared service
4. Vehicle dropdown should now show only shared-compatible vehicles

## Related Files

This fix works in conjunction with the earlier fix in:
- `app/Http/Controllers/DefaultValueController.php` - which ensures vehicles are fetched with correct `sharable` filtering
- `resources/views/default-values/create.blade.php` - which displays vehicles with sharable information

## Summary

✅ **Fixed:** Port options in arrival destination dropdown now have `data-port-id` attribute  
✅ **Fixed:** Vehicle options now have `data-sharable` attribute for proper filtering  
✅ **Fixed:** Service type dropdowns now trigger vehicle filtering when changed  
✅ **Added:** JavaScript functions to filter vehicles based on service type and sharable property  
✅ **Added:** Auto-filtering when transfer fields are first shown  

The enquiry form now properly filters vehicles based on the selected service type (Private/Shared), showing only vehicles that support the chosen service configuration.

