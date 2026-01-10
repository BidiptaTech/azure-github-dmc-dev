# Arrival/Departure Add Button - Clear Fields Fix

## Issue
When clicking the "+ Add" button in the Arrival/Departure section, the modal popup was showing previous data instead of blank/cleared fields.

## Root Cause
The timing of field clearing was incorrect:
1. `openArrivalDepartureModal()` set `window.skipArrivalDepartureAutoPopulate = true`
2. Called `openAccommodationModal()` 
3. Tried to clear fields at 100ms timeout
4. But `openAccommodationModal()` has a 200ms timeout that auto-populates existing arrival/departure data (lines 5384-5415)
5. Even though the skip flag was checked, the 100ms clear happened BEFORE the 200ms auto-populate, so old data appeared

## Solution
Changed the order of operations in `openArrivalDepartureModal()`:

### Before:
```javascript
function openArrivalDepartureModal() {
    window.skipArrivalDepartureAutoPopulate = true;
    openAccommodationModal();
    
    setTimeout(() => {
        // Clear fields at 100ms
        document.getElementById('arrivalDateTime').value = '';
        // ... etc
    }, 100);
}
```

### After:
```javascript
function openArrivalDepartureModal() {
    // 1. Clear all fields BEFORE opening modal
    document.getElementById('arrivalDateTime').value = '';
    document.getElementById('arrivalFlightNo').value = '';
    document.getElementById('arrivalTransfer').checked = false;
    document.getElementById('departureDateTime').value = '';
    document.getElementById('departureFlightNo').value = '';
    document.getElementById('departureTransfer').checked = false;
    
    // 2. Reset Select2 dropdowns
    $('#arrivalPort').val(null).trigger('change');
    $('#departurePort').val(null).trigger('change');
    $('#arrivalDestination').val(null).trigger('change');
    $('#departureDestination').val(null).trigger('change');
    
    // 3. Set flag to prevent auto-population
    window.skipArrivalDepartureAutoPopulate = true;
    
    // 4. Open modal
    openAccommodationModal();
    
    // 5. Set mode flag
    window.isArrivalDepartureOnlyMode = true;
    
    // ... rest of setup
    
    // 6. Reset flag after delay (300ms to ensure auto-populate timeout completes)
    setTimeout(() => {
        window.skipArrivalDepartureAutoPopulate = false;
    }, 300);
}
```

## Key Changes

1. **Clear fields BEFORE opening modal** - This ensures fields are empty before any auto-population logic runs

2. **Use `val(null)` for Select2 dropdowns** - This properly clears Select2 dropdowns instead of `val('')`

3. **Removed redundant setTimeout clearing** - No longer needed since we clear before opening

4. **Extended flag reset timeout to 300ms** - Ensures it resets after the 200ms auto-populate timeout in `openAccommodationModal()`

5. **Reset transfer fields to defaults** - Set vehicle type, pax numbers, way, and type to default values

6. **Hide transfer detail sections** - Ensure transfer details are hidden initially

## Result
✅ Clicking "+ Add" button now opens a completely clean form with all fields empty and ready for new data entry.

## Files Modified
- `resources/views/enquiryform_pro/create.blade.php` (lines ~7416-7501)

## Date Fixed
December 22, 2024
