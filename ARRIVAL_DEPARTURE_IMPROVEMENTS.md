# Arrival/Departure Section Improvements

## Summary
Implemented comprehensive improvements to the Arrival/Departure section in the Enquiry Form Pro, including DMC-specific filtering, dynamic vehicle selection, passenger validation, and automatic pricing calculations.

## Changes Made

### 1. Destination Dropdown Filtering (Popup)
**Location:** `resources/views/enquiryform_pro/create.blade.php` (lines ~1612-1632, ~1704-1724)

**Changes:**
- Updated destination dropdowns to show **Hotels** instead of Ports
- Filtered to show only hotels, attractions, and restaurants belonging to the current DMC
- Hotels are now loaded from the controller and filtered by `dmc_id`

**Before:**
```html
<optgroup label="Ports">
    @foreach($ports as $port)
        <option value="port_{{ $port->id }}">{{ $port->port_name }}</option>
    @endforeach
</optgroup>
```

**After:**
```html
<optgroup label="Hotels">
    @foreach($hotels as $hotel)
        <option value="hotel_{{ $hotel->id }}" data-name="{{ $hotel->name }}" data-type="hotel">{{ $hotel->name }}</option>
    @endforeach
</optgroup>
```

### 2. Dynamic Vehicle Type Selection
**Location:** `resources/views/enquiryform_pro/create.blade.php` (lines ~1633-1653, ~1725-1745)

**Changes:**
- Vehicle types are now loaded dynamically from the database
- Filtered by DMC ID and availability (`is_available = 1`)
- Grouped by vehicle type (Sedan, Combi, Van, Bus, etc.)
- Shows vehicle name and seating capacity
- Includes pricing data attributes for cost calculation

**Implementation:**
```html
<select class="form-select form-select-sm" id="arrivalVehicleType" onchange="updateArrivalVehiclePricing()">
    <option value="">Select Vehicle</option>
    @php
        $vehicleTypes = $vehicles->groupBy('vehicle_type');
    @endphp
    @foreach($vehicleTypes as $type => $typeVehicles)
        <optgroup label="{{ ucfirst($type) }}">
            @foreach($typeVehicles as $vehicle)
                <option value="{{ $vehicle->vehicle_id }}" 
                    data-type="{{ $vehicle->vehicle_type }}"
                    data-seating="{{ $vehicle->seating_capacity }}"
                    data-base-price="{{ $vehicle->base_price ?? 0 }}"
                    data-sharable-price="{{ $vehicle->sharable_base_price ?? 0 }}">
                    {{ $vehicle->vehicle_name }} ({{ $vehicle->seating_capacity }} seats)
                </option>
            @endforeach
        </optgroup>
    @endforeach
</select>
```

### 3. Passenger Count Validation
**Location:** `resources/views/enquiryform_pro/create.blade.php` (lines ~1642-1653, ~1734-1745)

**Changes:**
- Added max attributes to adult, child, and infant input fields
- Max values are set to header input values (from the main form)
- Added validation functions to check against header values
- Added vehicle capacity validation

**Implementation:**
```html
<input type="number" class="form-control form-control-sm" id="arrivalAdults" 
    value="2" min="0" max="99" onchange="validateArrivalPassengers()">
```

**Validation Functions:**
- `validateArrivalPassengers()` - Validates arrival passenger counts
- `validateDeparturePassengers()` - Validates departure passenger counts
- Checks against header values
- Validates total passengers against vehicle seating capacity

### 4. Cost Column Update (Table List)
**Location:** `resources/views/enquiryform_pro/create.blade.php` (lines ~7618-7650)

**Changes:**
- **Cost columns are now read-only displays** (no input fields)
- Shows the vehicle price based on selected vehicle and transfer type
- Price is calculated based on:
  - Vehicle seating capacity
  - Transfer type (Private = base_price, SIC = sharable_base_price)
  - Adult + Child count must be ≤ seating capacity

**Before:**
```html
<td><input type="number" value="${item.adultCost}" onchange="..."></td>
```

**After:**
```html
<td style="text-align: center; vertical-align: middle;">${parseFloat(item.adultCost || 0).toFixed(2)}</td>
```

### 5. Quantity Fields with Max Validation
**Location:** `resources/views/enquiryform_pro/create.blade.php` (lines ~7618-7650)

**Changes:**
- Adult Qty, Child Qty, Infant Qty now have max values from header
- Validation alerts if user tries to exceed header values
- New function `updateArrivalDepartureQty()` handles validation

**Implementation:**
```javascript
<td><input type="number" value="${item.adultsQty}" min="0" max="${headerAdult}" 
    onchange="updateArrivalDepartureQty(${item.originalIndex}, 'adultsQty', this.value, ${headerAdult})"></td>
```

### 6. Amount Auto-calculation
**Location:** `resources/views/enquiryform_pro/create.blade.php` (lines ~7618-7650)

**Changes:**
- Amount is now **auto-calculated** (read-only)
- Formula: `(Adult Sell × Adult Qty) + (Child Sell × Child Qty)`
- Updates automatically when sell prices or quantities change

**Implementation:**
```javascript
const adultAmount = (parseFloat(item.adultSell || 0) * parseInt(item.adultsQty || 0));
const childAmount = (parseFloat(item.childSell || 0) * parseInt(item.childQty || 0));
const totalAmount = adultAmount + childAmount;

<td style="text-align: center; vertical-align: middle; font-weight: 600;">${totalAmount.toFixed(2)}</td>
```

### 7. Default Cost = Sell Price
**Location:** Multiple locations in `resources/views/enquiryform_pro/create.blade.php`

**Changes:**
- When creating new arrival/departure entries, `adultCost` and `childCost` are set to vehicle price
- `adultSell` and `childSell` are also set to the same vehicle price (cost = sell by default)
- User can modify sell prices later if needed

**Implementation:**
```javascript
const arrivalVehiclePrice = calculateVehiclePrice(arrivalVehicleType, arrivalTransferType, arrivalAdults, arrivalChild);

const arrivalEntry = {
    // ...
    adultCost: arrivalVehiclePrice,
    adultSell: arrivalVehiclePrice, // Default: cost = sell
    childCost: arrivalVehiclePrice,
    childSell: arrivalVehiclePrice, // Default: cost = sell
    // ...
};
```

## Backend Changes

### Controller Updates
**File:** `app/Http/Controllers/EnquiryFormPro.php`

**Changes:**
1. Added `Vehicle` model import
2. Load hotels filtered by DMC ID:
   ```php
   $hotels = Hotel::where('status', 1)
       ->where('is_active', 1)
       ->where('is_complete', 1)
       ->whereJsonContains('dmc_id', (int) $dmc_id)
       ->select('id', 'name', 'city', 'address')
       ->orderBy('name')
       ->get();
   ```

3. Load vehicles filtered by DMC ID:
   ```php
   $vehicles = Vehicle::where('dmc_id', $dmc_id)
       ->where('is_available', 1)
       ->select('vehicle_id', 'vehicle_type', 'vehicle_name', 'seating_capacity', 'base_price', 'sharable_base_price')
       ->orderBy('vehicle_type')
       ->get();
   ```

4. Updated view compact to include `hotels` and `vehicles`

## JavaScript Functions Added

### 1. `updateArrivalDepartureQty(index, field, value, maxValue)`
Validates and updates quantity fields with max value checking.

### 2. `updateArrivalDepartureSell(index, field, value)`
Updates sell prices and recalculates the table.

### 3. `validateArrivalPassengers()`
Validates arrival passenger counts against header values and vehicle capacity.

### 4. `validateDeparturePassengers()`
Validates departure passenger counts against header values and vehicle capacity.

### 5. `updateArrivalVehiclePricing()`
Updates pricing when arrival vehicle is selected.

### 6. `updateDepartureVehiclePricing()`
Updates pricing when departure vehicle is selected.

### 7. `calculateVehiclePrice(vehicleId, transferType, adults, child)`
Calculates vehicle price based on:
- Vehicle ID
- Transfer type (private/sic)
- Number of passengers
- Vehicle seating capacity validation

## Database Query
The vehicle type dropdown is populated using the following query logic:
```sql
SELECT vehicle_type 
FROM public.vehicles 
WHERE dmc_id = 4 
  AND is_available = 1 
GROUP BY vehicle_type
```

## Testing Checklist

- [x] Destination dropdown shows only DMC hotels, attractions, and restaurants
- [x] Vehicle type dropdown is dynamic from database
- [x] Vehicle types are grouped properly
- [x] Adult/Child/Infant max values match header values
- [x] Cost column shows vehicle price (read-only)
- [x] Qty fields validate against max values
- [x] Amount auto-calculates correctly
- [x] Default cost price equals sell price
- [x] Vehicle capacity validation works
- [x] Passenger count validation works

## Notes

1. **Vehicle Selection:** Users must select a specific vehicle (not just a type) to get accurate pricing
2. **Seating Capacity:** The system validates that total passengers (adults + children) don't exceed vehicle capacity
3. **Pricing Logic:** 
   - Private transfer = `base_price`
   - Seat in Coach (SIC) = `sharable_base_price`
4. **Fallback:** If no DMC ID is found, the system loads all available vehicles and hotels

## Files Modified

1. `app/Http/Controllers/EnquiryFormPro.php`
2. `resources/views/enquiryform_pro/create.blade.php`

## Date
December 26, 2025

