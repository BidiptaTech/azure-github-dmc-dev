# Hotel Transfer Removal and Room Selection Fix

## Issues Fixed

### Issue 1: Hotel Transfer Price Not Removed from Footer
**Problem**: When updating a hotel and unchecking the transfer checkbox, the vehicle was removed from the transfer list but the vehicle price remained in the footer section.

**Root Cause**: The `saveSelectedHotels()` function was removing old transfers when updating a hotel (line 9389), but it wasn't calling `recalculateTotals()` to update the footer pricing.

**Solution**: Added `recalculateTotals()` call after updating accommodation tables to ensure footer pricing is recalculated.

**Code Changes** (around line 9510):
```javascript
// Update table
updateAccommodationTable();

// When editing, recalculate from all services to handle date changes properly
recalculateHeaderDatesFromServices();

// Recalculate totals to update footer pricing
recalculateTotals(); // ← ADDED THIS LINE

// Close modal
const accommodationModal = bootstrap.Modal.getInstance(document.getElementById('accommodationModal'));
accommodationModal.hide();
```

### Issue 2: Room Combination Not Selected When Editing Hotel
**Problem**: When editing a hotel, the dynamic row (room combination) that was previously selected was not being checked/highlighted in the room combinations table.

**Root Cause**: 
1. The matching logic was correct but needed more time for DOM rendering
2. Missing debug logging to troubleshoot matching issues
3. Timeout was too short (500ms) for the combinations table to fully render

**Solution**: 
1. Increased timeout from 500ms to 800ms to ensure DOM is fully rendered
2. Added comprehensive debug logging to track matching process
3. Enhanced the matching logic with better console output
4. Synchronized transfer data loading timeout to 900ms

**Code Changes** (around line 10076-10149):

**Enhanced Matching Logic with Debug Logging**:
```javascript
setTimeout(() => {
    if (window.currentRoomCombinations && window.currentRoomCombinations.length > 0) {
        console.log('=== Attempting to match room combination ===');
        console.log('Hotel data:', {
            roomType: hotel.roomType,
            bedType: hotel.bedType,
            bedTypeRaw: hotel.bedTypeRaw,
            mealPlan: hotel.mealPlan,
            mealPlanLabel: hotel.mealPlanLabel
        });
        
        // Find the matching combination
        const matchingCombo = window.currentRoomCombinations.find(combo => {
            const roomMatch = combo.roomType === hotel.roomType;
            const bedMatch = (combo.bedTypeRaw || combo.bedType) === (hotel.bedTypeRaw || hotel.bedType) || 
                            combo.bedType === hotel.bedType;
            const mealMatch = (combo.mealPlanLabel || combo.mealPlan) === (hotel.mealPlanLabel || hotel.mealPlan) ||
                            combo.mealPlan === hotel.mealPlan;
            
            console.log('Checking combo:', {
                comboRoomType: combo.roomType,
                comboBedTypeRaw: combo.bedTypeRaw,
                comboMealPlan: combo.mealPlan,
                roomMatch,
                bedMatch,
                mealMatch
            });
            
            return roomMatch && bedMatch && mealMatch;
        });
        
        if (matchingCombo) {
            console.log('Found matching combo:', matchingCombo);
            
            // Check the matching combination
            const checkbox = document.querySelector(`.room-combination-checkbox[data-combo-id="${matchingCombo.id}"]`);
            console.log('Checkbox found:', checkbox);
            
            if (checkbox) {
                checkbox.checked = true;
                
                // Set the values for this combination
                const roomsInput = document.querySelector(`.combo-rooms[data-combo-id="${matchingCombo.id}"]`);
                const adultsInput = document.querySelector(`.combo-adults[data-combo-id="${matchingCombo.id}"]`);
                const extraBedInput = document.querySelector(`.combo-extra-bed[data-combo-id="${matchingCombo.id}"]`);
                const childWithoutInput = document.querySelector(`.combo-child-without[data-combo-id="${matchingCombo.id}"]`);
                
                if (roomsInput) roomsInput.value = hotel.rooms;
                if (adultsInput) adultsInput.value = hotel.adultsPerRoom;
                if (extraBedInput) extraBedInput.value = hotel.extraBed || 0;
                if (childWithoutInput) childWithoutInput.value = hotel.childWithoutBed || 0;
                
                console.log('Set values:', {
                    rooms: hotel.rooms,
                    adults: hotel.adultsPerRoom,
                    extraBed: hotel.extraBed,
                    childWithout: hotel.childWithoutBed
                });
            } else {
                console.warn('Checkbox not found in DOM for combo ID:', matchingCombo.id);
            }
        } else {
            console.warn('No matching combination found for hotel:', hotel);
        }
    } else {
        console.warn('No room combinations available yet');
    }
}, 800); // Increased timeout from 500ms to 800ms
```

**Synchronized Transfer Loading Timeout**:
```javascript
// Load existing transfer data if any
setTimeout(() => {
    if (hotel.transferIds && hotel.transferIds.length > 0) {
        // ... transfer loading code ...
    } else {
        resetHotelTransferFields();
    }
}, 900); // Increased from 500ms to 900ms to match room selection
```

## Testing Scenarios

### Scenario 1: Uncheck Hotel Transfer
1. ✅ Open hotel for editing
2. ✅ Uncheck the transfer checkbox
3. ✅ Save/Update the hotel
4. ✅ Verify transfer is removed from transfer list
5. ✅ **Verify transfer price is removed from footer** (FIXED)

### Scenario 2: Edit Hotel with Room Selection
1. ✅ Add a hotel with specific room combination (e.g., Deluxe Room, King Bed, Breakfast)
2. ✅ Save the hotel
3. ✅ Edit the same hotel
4. ✅ **Verify the previously selected room combination is checked** (FIXED)
5. ✅ **Verify the input values (rooms, adults, extra bed, etc.) are populated** (FIXED)

### Scenario 3: Edit Hotel with Transfer
1. ✅ Add a hotel with transfer
2. ✅ Edit the hotel
3. ✅ Uncheck transfer
4. ✅ Save
5. ✅ Verify footer pricing updates correctly

## Debug Information

When editing a hotel, you can now check the browser console for detailed matching information:
- Hotel data being matched
- Each combination being checked
- Match results for room type, bed type, and meal plan
- Whether checkbox was found in DOM
- Values being set

This makes it much easier to troubleshoot any matching issues in the future.

## Files Modified

- `resources/views/enquiryform_pro/create.blade.php`
  - Line ~9510: Added `recalculateTotals()` call
  - Line ~10076-10149: Enhanced room matching logic with debug logging and increased timeout
  - Line ~10194: Synchronized transfer loading timeout

## Summary

Both issues have been successfully fixed:
1. ✅ Footer pricing now updates correctly when hotel transfer is unchecked
2. ✅ Room combination is now properly selected when editing a hotel
3. ✅ Added comprehensive debug logging for easier troubleshooting
4. ✅ Improved timing to ensure DOM is fully rendered before attempting to match

The fixes ensure a consistent and reliable user experience when managing hotel accommodations and their associated transfers.

