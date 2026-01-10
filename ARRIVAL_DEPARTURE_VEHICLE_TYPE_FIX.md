# Arrival/Departure Vehicle Type Display Fix

## Issue Fixed

### Problem: Table Showing "8/Private" Instead of Vehicle Type Name
**Symptom**: The arrival/departure table was displaying numbers like "8/Private" instead of the vehicle type name like "Sedan/Private".

**Root Cause**: The vehicle dropdown stores the `vehicle_id` (a number) as its value, but the code was using `.value` to get the vehicle type, which returned the ID instead of the vehicle type name.

**Solution**: Updated the code to extract the vehicle type name from the `data-type` attribute of the selected option instead of using the dropdown's value.

## Changes Made

### File: `resources/views/enquiryform_pro/create.blade.php`

#### 1. Fix for Editing Arrival (Lines ~6389-6392)
**Before:**
```javascript
const arrivalVehicleType = document.getElementById('arrivalVehicleType')?.value || 'sedan';
```

**After:**
```javascript
// Get vehicle type name from data attribute instead of value (which is vehicle_id)
const arrivalVehicleSelect = document.getElementById('arrivalVehicleType');
const arrivalVehicleType = arrivalVehicleSelect?.selectedOptions[0]?.getAttribute('data-type') || '';
```

#### 2. Fix for Editing Departure (Lines ~6470-6473)
**Before:**
```javascript
const departureVehicleType = document.getElementById('departureVehicleType')?.value || 'sedan';
```

**After:**
```javascript
// Get vehicle type name from data attribute instead of value (which is vehicle_id)
const departureVehicleSelect = document.getElementById('departureVehicleType');
const departureVehicleType = departureVehicleSelect?.selectedOptions[0]?.getAttribute('data-type') || '';
```

#### 3. Fix for Adding New Arrival (Lines ~6568-6571)
**Before:**
```javascript
arrivalVehicleType = document.getElementById('arrivalVehicleType')?.value || 'sedan';
```

**After:**
```javascript
// Get vehicle type name from data attribute instead of value (which is vehicle_id)
const arrivalVehicleSelect = document.getElementById('arrivalVehicleType');
arrivalVehicleType = arrivalVehicleSelect?.selectedOptions[0]?.getAttribute('data-type') || '';
```

#### 4. Fix for Adding New Departure (Lines ~6659-6662)
**Before:**
```javascript
departureVehicleType = document.getElementById('departureVehicleType')?.value || 'sedan';
```

**After:**
```javascript
// Get vehicle type name from data attribute instead of value (which is vehicle_id)
const departureVehicleSelect = document.getElementById('departureVehicleType');
departureVehicleType = departureVehicleSelect?.selectedOptions[0]?.getAttribute('data-type') || '';
```

## How It Works

### Vehicle Dropdown Structure
The vehicle dropdown is structured as follows:
```html
<select id="arrivalVehicleType">
    <option value="">Select Vehicle</option>
    <optgroup label="Sedan">
        <option value="8" data-type="sedan" data-seating="4" data-base-price="100">
            Toyota Camry (4 seats)
        </option>
        <option value="9" data-type="sedan" data-seating="4" data-base-price="120">
            Honda Accord (4 seats)
        </option>
    </optgroup>
    <optgroup label="Suv">
        <option value="10" data-type="suv" data-seating="7" data-base-price="150">
            Toyota Fortuner (7 seats)
        </option>
    </optgroup>
</select>
```

### Data Extraction
- **value**: Returns the vehicle_id (e.g., "8", "9", "10")
- **data-type**: Returns the vehicle type name (e.g., "sedan", "suv", "van")
- **data-seating**: Returns seating capacity
- **data-base-price**: Returns base price

### Display in Table
The vehicle type is now correctly extracted and displayed:
- Stored: "sedan", "suv", "van", etc.
- Displayed: "Sedan", "Suv", "Van", etc. (capitalized first letter)
- Combined with transfer type: "Sedan / SIC", "Suv / PRIVATE", etc.

## Testing Steps

### Test 1: Add New Arrival with Transfer
1. Click "+ Add" in Arrival/Departure section
2. Fill in arrival date and port
3. Check the "Transfer" checkbox
4. Select a vehicle from the dropdown (e.g., "Toyota Camry (4 seats)")
5. Select transfer type (SIC or Private)
6. Save the entry
7. **Expected**: Table should show "Sedan / SIC" (not "8 / SIC")

### Test 2: Edit Existing Arrival/Departure
1. Click on an existing arrival/departure entry
2. Change the vehicle type to a different vehicle
3. Save the changes
4. **Expected**: Table should show the correct vehicle type name

### Test 3: Different Vehicle Types
1. Add multiple entries with different vehicle types:
   - Sedan
   - SUV
   - Van
   - Bus
2. **Expected**: Each should display the correct vehicle type name below the port

## Destination Dropdown Clarification

The **Destination** dropdown (shown when Transfer checkbox is checked) correctly displays:
- Hotels
- Attractions
- Restaurants

This is the intended behavior. The destination dropdown is for selecting where the transfer goes to/from, not for selecting ports. Ports are selected in the separate "Port" dropdown field.

## Status
✅ **COMPLETED** - Vehicle type now displays correctly as name instead of ID

