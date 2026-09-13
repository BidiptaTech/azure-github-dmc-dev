# Default Values Integration with Enquiry Pro Form

## Overview
Integrated the 6 default values (hotel, restaurant, attraction, car_private, car_shared, port) into the Enquiry Pro Form create and edit pages to automatically populate fields with default values for better user experience.

## Implementation Date
January 13, 2026

## Changes Made

### 1. Backend - EnquiryFormPro Controller ✅

#### File: `app/Http/Controllers/EnquiryFormPro.php`

**create() Method:**
```php
// Fetch default values for this DMC (6 types: hotel, restaurant, attraction, car_private, car_shared, port)
$defaultValues = [];
if ($dmc_id) {
    $defaults = \App\Models\DefaultValue::where('dmc_id', $dmc_id)
        ->where('status', 1)
        ->get();
    
    foreach ($defaults as $default) {
        $defaultValues[$default->name] = $default->service_id;
    }
}

// Added 'defaultValues' to view compact
return view('enquiryform_pro.create', compact(..., 'defaultValues'));
```

**edit() Method:**
- Same default values fetching logic added
- Added `'defaultValues'` to view compact

### 2. Frontend - Create View ✅

#### File: `resources/views/enquiryform_pro/create.blade.php`

**JavaScript Additions:**

1. **Default Values Declaration:**
```javascript
// Default values from backend (6 types: hotel, restaurant, attraction, car_private, car_shared, port)
const defaultValues = @json($defaultValues ?? []);
console.log('Default Values:', defaultValues);
```

2. **Auto-populate Default Port on Page Load:**
```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Set default port for arrival and departure
    if (defaultValues.port) {
        const arrivalPort = document.getElementById('arrivalPort');
        const departurePort = document.getElementById('departurePort');
        
        if (arrivalPort) {
            $(arrivalPort).val(defaultValues.port).trigger('change');
        }
        
        if (departurePort) {
            $(departurePort).val(defaultValues.port).trigger('change');
        }
    }
    // ... rest of code
});
```

3. **Enhanced toggleArrivalTransferFields():**
```javascript
function toggleArrivalTransferFields() {
    const transferChecked = document.getElementById('arrivalTransfer').checked;
    const detailsSection = document.getElementById('arrivalTransferDetailsSection');
    if (detailsSection) {
        detailsSection.style.display = transferChecked ? 'block' : 'none';
    }
    
    // When transfer is checked, set default destination and update label
    if (transferChecked) {
        const arrivalDestLabel = document.querySelector('#arrivalDestinationField label');
        if (arrivalDestLabel) {
            arrivalDestLabel.textContent = 'Drop Off';  // Changed from 'Destination'
        }
        
        const arrivalDestSelect = document.getElementById('arrivalDestination');
        if (arrivalDestSelect && defaultValues) {
            // Get all hotel options
            const hotelOptions = Array.from(arrivalDestSelect.options).filter(o => 
                o.getAttribute('data-type') === 'hotel'
            );
            
            // If default hotel exists, select it
            if (defaultValues.hotel) {
                $(arrivalDestSelect).val(defaultValues.hotel).trigger('change');
            } else if (hotelOptions.length > 0) {
                // If no default hotel but hotels exist, select first hotel (already sorted by name ascending)
                $(arrivalDestSelect).val(hotelOptions[0].value).trigger('change');
            }
            
            // Set default vehicle type to 'shared' and select default shared vehicle
            const arrivalVehicleType = document.getElementById('arrivalTransferType');
            if (arrivalVehicleType) {
                arrivalVehicleType.value = 'S'; // S = Shared
            }
            
            // Set default vehicle if car_shared default exists
            const arrivalVehicle = document.getElementById('arrivalVehicle');
            if (arrivalVehicle && defaultValues.car_shared) {
                setTimeout(() => {
                    $(arrivalVehicle).val(defaultValues.car_shared).trigger('change');
                }, 100);
            }
        }
    }
}
```

4. **Enhanced toggleDepartureTransferFields():**
```javascript
function toggleDepartureTransferFields() {
    const transferChecked = document.getElementById('departureTransfer').checked;
    const detailsSection = document.getElementById('departureTransferDetailsSection');
    if (detailsSection) {
        detailsSection.style.display = transferChecked ? 'block' : 'none';
    }
    
    // When transfer is checked, set default destination and update label
    if (transferChecked) {
        const departureDestLabel = document.querySelector('#departureDestinationField label');
        if (departureDestLabel) {
            departureDestLabel.textContent = 'Pickup';  // Changed from 'Destination'
        }
        
        const departureDestSelect = document.getElementById('departureDestination');
        if (departureDestSelect && defaultValues) {
            // Get all hotel options
            const hotelOptions = Array.from(departureDestSelect.options).filter(o => 
                o.getAttribute('data-type') === 'hotel'
            );
            
            // If default hotel exists, select it
            if (defaultValues.hotel) {
                $(departureDestSelect).val(defaultValues.hotel).trigger('change');
            } else if (hotelOptions.length > 0) {
                // If no default hotel but hotels exist, select first hotel (already sorted by name ascending)
                $(departureDestSelect).val(hotelOptions[0].value).trigger('change');
            }
            
            // Set default vehicle type to 'shared' and select default shared vehicle
            const departureVehicleType = document.getElementById('departureTransferType');
            if (departureVehicleType) {
                departureVehicleType.value = 'S'; // S = Shared
            }
            
            // Set default vehicle if car_shared default exists
            const departureVehicle = document.getElementById('departureVehicle');
            if (departureVehicle && defaultValues.car_shared) {
                setTimeout(() => {
                    $(departureVehicle).val(defaultValues.car_shared).trigger('change');
                }, 100);
            }
        }
    }
}
```

### 3. Frontend - Edit View ✅

#### File: `resources/views/enquiryform_pro/edit.blade.php`

**Same changes applied as create view:**
- Default values declaration
- Auto-populate default port
- Enhanced toggle functions for arrival/departure

## Features Implemented

### 1. Default Port Auto-Selection ✅
**When:**
- Page loads (both create and edit)

**Behavior:**
- If `defaultValues.port` exists, automatically selects it in both:
  - Arrival Port dropdown
  - Departure Port dropdown

**Benefit:**
- Users don't need to manually select the port every time
- Saves time for frequently used ports

### 2. Arrival Transfer Enhancements ✅

**When:**
- User checks "Transfer" checkbox in Arrival section

**Behavior:**
1. **Label Change:**
   - "Destination" → "Drop Off"
   
2. **Default Hotel Selection:**
   - If `defaultValues.hotel` exists → Select it
   - Else if hotels exist → Select first hotel (sorted by name ascending)
   - Hotels are already pre-sorted by the backend query
   
3. **Default Vehicle Type:**
   - Auto-selects "Shared" (S)
   
4. **Default Vehicle Selection:**
   - If `defaultValues.car_shared` exists → Select it after 100ms delay

**Benefit:**
- Smart auto-population based on DMC preferences
- Fallback to first hotel if no default configured
- Consistent default to shared vehicles (most common use case)

### 3. Departure Transfer Enhancements ✅

**When:**
- User checks "Transfer" checkbox in Departure section

**Behavior:**
1. **Label Change:**
   - "Destination" → "Pickup"
   
2. **Default Hotel Selection:**
   - If `defaultValues.hotel` exists → Select it
   - Else if hotels exist → Select first hotel (sorted by name ascending)
   
3. **Default Vehicle Type:**
   - Auto-selects "Shared" (S)
   
4. **Default Vehicle Selection:**
   - If `defaultValues.car_shared` exists → Select it after 100ms delay

**Benefit:**
- Contextually appropriate label (Pickup for departure)
- Same smart auto-population logic as arrival
- Consistent user experience

## Business Logic

### Hotel Selection Priority:
1. **First Priority:** DMC's configured default hotel
2. **Second Priority:** First hotel in the list (alphabetically)
3. **Fallback:** Empty if no hotels available

### Vehicle Selection:
- **Default Type:** Always "Shared" (most common scenario)
- **Default Vehicle:** DMC's configured `car_shared` default (if exists)
- **User Override:** User can change to "Private" and select different vehicle

### Port Selection:
- **Auto-populated:** On page load
- **Both Fields:** Arrival AND Departure ports
- **User Override:** User can change if needed

## Default Values Used

| Default Type | Used In | When | Field |
|-------------|---------|------|-------|
| `port` | Arrival/Departure | Page Load | Arrival Port, Departure Port |
| `hotel` | Arrival Transfer | Transfer Checked | Drop Off Destination |
| `hotel` | Departure Transfer | Transfer Checked | Pickup Destination |
| `car_shared` | Arrival Transfer | Transfer Checked | Vehicle Selection |
| `car_shared` | Departure Transfer | Transfer Checked | Vehicle Selection |

**Note:** `restaurant`, `attraction`, and `car_private` are fetched but not used in arrival/departure (reserved for future features).

## Technical Details

### Backend Query:
```php
$defaults = \App\Models\DefaultValue::where('dmc_id', $dmc_id)
    ->where('status', 1)
    ->get();
```

### Data Structure:
```javascript
defaultValues = {
    'hotel': 'hotel_unique_id_value',
    'restaurant': 'restaurant_id_value',
    'attraction': 'attraction_id_value',
    'car_private': 'vehicle_id_value',
    'car_shared': 'vehicle_id_value',
    'port': 'port_id_value'
}
```

### jQuery Trigger:
Used `$(element).val(value).trigger('change')` to ensure:
- Select2 dropdowns update properly
- Dependent fields get updated
- Any event listeners are triggered

### Timeout Usage:
100ms delay for vehicle selection to ensure:
- Dropdown is fully initialized
- Options are loaded
- Select2 is ready

## Files Modified (4 Total)

1. ✅ `app/Http/Controllers/EnquiryFormPro.php`
   - Added default values fetching in `create()` method
   - Added default values fetching in `edit()` method

2. ✅ `resources/views/enquiryform_pro/create.blade.php`
   - Added default values JavaScript declaration
   - Added auto-populate port logic
   - Enhanced `toggleArrivalTransferFields()`
   - Enhanced `toggleDepartureTransferFields()`

3. ✅ `resources/views/enquiryform_pro/edit.blade.php`
   - Added default values JavaScript declaration
   - Added auto-populate port logic
   - Enhanced `toggleArrivalTransferFields()`
   - Enhanced `toggleDepartureTransferFields()`

4. ✅ `DEFAULT_VALUES_ENQUIRY_PRO_INTEGRATION.md` (this file)

## Testing Checklist

### Arrival Section:
- [ ] Page loads → Port auto-selected
- [ ] Check Transfer → Label changes to "Drop Off"
- [ ] Check Transfer → Default hotel selected (or first hotel)
- [ ] Check Transfer → Vehicle type = "Shared"
- [ ] Check Transfer → Default shared vehicle selected
- [ ] User can override all selections

### Departure Section:
- [ ] Page loads → Port auto-selected
- [ ] Check Transfer → Label changes to "Pickup"
- [ ] Check Transfer → Default hotel selected (or first hotel)
- [ ] Check Transfer → Vehicle type = "Shared"
- [ ] Check Transfer → Default shared vehicle selected
- [ ] User can override all selections

### Edge Cases:
- [ ] No default values configured → Falls back gracefully
- [ ] No hotels in list → No error, empty selection
- [ ] No vehicles in list → No error, empty selection
- [ ] Edit mode → Same behavior as create mode

## Benefits

1. **Time Saving:** Reduces manual data entry
2. **Consistency:** Ensures DMC preferences are respected
3. **User Experience:** Intuitive labels (Drop Off/Pickup)
4. **Smart Defaults:** Logical fallbacks when defaults don't exist
5. **Flexibility:** Users can still override any selection

## Future Enhancements

Potential future uses of other default values:
1. **Restaurant:** Could be used in meal/dining sections
2. **Attraction:** Could be suggested in sightseeing sections
3. **Car Private:** Could be suggested when user changes vehicle type to Private

---

**Status:** ✅ Complete
**Date:** January 13, 2026
**Linter Errors:** None
**Testing:** Ready for QA

