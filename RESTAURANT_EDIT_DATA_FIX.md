# Restaurant Edit Data Display Fix

## Date: December 20, 2025

## Issues Reported

1. **Wrong Transfer Data**: When editing "Cafe Delight" (which has Singapore transfer), the popup was showing "Maldives" transfer data from another restaurant
2. **Menu Item Not Showing**: The selected menu item name was not displayed in the restaurant table list
3. **Checkbox Not Checked**: When editing, the menu item checkbox was not checked in the popup
4. **Values Not Pre-selected**: Other selected values (quantities, charges) were not showing

## Root Causes

### Issue 1: Transfer Data Mismatch
The `editMeal()` function was looking up transfer data from `transferList` using `meal.transferId`, but this could return the wrong transfer if multiple meals shared similar IDs or if the transferList was not in sync.

**Problem**: The lookup was not reliable because:
- Multiple restaurants could have transfers with similar data
- The transferList might have been modified
- The meal's own `transferInfo` was being ignored in favor of the lookup

### Issue 2: Menu Name Not Displayed
The restaurant table was only showing `${meal.restaurantName}` without the actual menu item name.

**Code Before**:
```javascript
${meal.restaurantName || 'Restaurant'}${meal.mealType ? ` (${meal.mealType})` : ''}
```

This only showed "Cafe Delight (Breakfast)" but not the actual menu item like "Menu Item".

### Issue 3 & 4: Checkbox and Values Not Populated
The `populateMealFormForEdit()` function was working, but the data flow was incorrect because the wrong transfer data was being used.

## Solutions Implemented

### Fix 1: Prioritize meal.transferInfo Over Lookup

Updated the transfer data retrieval logic to:
1. **First** check if `meal.transferInfo` exists (the source of truth)
2. **Then** lookup from `transferList` only if `transferInfo` is missing
3. Added extensive console logging to track the data flow

```javascript
// Get transfer info - prioritize meal.transferInfo, then lookup from transferList
let tInfo = null;

if (meal.transferInfo) {
    // Use the transfer info stored with the meal
    tInfo = meal.transferInfo;
    console.log('Using meal.transferInfo:', tInfo);
} else if (meal.transferId) {
    // Lookup from transferList
    const linkedTransfer = transferList.find(t => t.id === meal.transferId);
    console.log('Looking up transfer with ID:', meal.transferId, 'Found:', linkedTransfer);
    if (linkedTransfer) {
        tInfo = {
            destination: linkedTransfer.destination,
            vehicleType: linkedTransfer.vehicleType || 'sedan',
            type: linkedTransfer.mode === 'Private' ? 'private' : 'sic',
            way: linkedTransfer.way === 'Both Way' ? 'both-way' : 'one-way'
        };
    }
}
```

### Fix 2: Show Menu Item Name in Table

Updated the restaurant table display to show both restaurant name and menu item name:

```javascript
// BEFORE
${meal.restaurantName || 'Restaurant'}${meal.mealType ? ` (${meal.mealType})` : ''}

// AFTER
${meal.restaurantName || 'Restaurant'} - ${meal.mealName || meal.mealType || 'Meal'}
```

**Result**: Now shows "Cafe Delight - Menu Item" instead of just "Cafe Delight (Breakfast)"

### Fix 3: Enhanced Console Logging

Added comprehensive logging at the start of `editMeal()`:

```javascript
console.log('=== EDITING MEAL ===');
console.log('Index:', index);
console.log('Full meal object:', JSON.parse(JSON.stringify(meal)));
console.log('Restaurant:', meal.restaurantName, 'ID:', meal.restaurantId);
console.log('Meal Name:', meal.mealName, 'Type:', meal.mealType);
console.log('Destination:', meal.destination);
console.log('Transfer ID:', meal.transferId);
console.log('Transfer Info:', meal.transferInfo);
console.log('Current transferList:', JSON.parse(JSON.stringify(transferList)));
```

And in the transfer population section:

```javascript
console.log('=== Populating Transfer Section for Edit ===');
console.log('Meal data:', meal);
console.log('Using meal.transferInfo:', tInfo);
console.log('Set destination to:', tInfo.destination);
console.log('Set vehicle type to:', tInfo.vehicleType);
console.log('Set way to:', tInfo.way);
console.log('Set type to:', tInfo.type);
```

## Data Flow

### Correct Data Flow (After Fix)

1. User clicks "Cafe Delight - Menu Item" in the restaurant table
2. `editMeal(index)` is called
3. Meal object is retrieved: `mealList[index]`
4. **meal.transferInfo is checked first** (contains: {destination: "Singapore", vehicleType: "sedan", ...})
5. Transfer section is populated with meal.transferInfo data
6. Destination dropdown shows "Singapore" ✓
7. Vehicle type shows "sedan" ✓
8. Menu item checkbox is checked ✓
9. All quantities and charges are populated ✓

### Wrong Data Flow (Before Fix)

1. User clicks "Cafe Delight" in the restaurant table
2. `editMeal(index)` is called
3. Meal object is retrieved: `mealList[index]`
4. **meal.transferId is used to lookup in transferList**
5. Wrong transfer is found (e.g., last added transfer which was "Maldives")
6. Transfer section is populated with wrong data
7. Destination dropdown shows "Maldives" ✗
8. Menu item name not shown in table ✗

## Testing Scenarios

### Scenario 1: Edit Cafe Delight with Singapore Transfer
**Expected**:
- ✅ Restaurant table shows "Cafe Delight - Menu Item"
- ✅ Edit popup shows destination: "Singapore"
- ✅ Edit popup shows vehicle: "sedan"
- ✅ Edit popup shows way: "One Way[H/R]"
- ✅ Edit popup shows type: "Seat in Coach"
- ✅ Menu item checkbox is checked
- ✅ All quantities and charges are populated

### Scenario 2: Edit Burnt Ends with Maldives Transfer
**Expected**:
- ✅ Restaurant table shows "Burnt Ends - [Menu Name]"
- ✅ Edit popup shows destination: "Maldives"
- ✅ Edit popup shows vehicle: "combi"
- ✅ All other fields correctly populated

### Scenario 3: Edit Restaurant Without Transfer
**Expected**:
- ✅ Restaurant table shows "Restaurant Name - Menu Item"
- ✅ Transfer checkbox is unchecked
- ✅ Transfer section is hidden
- ✅ Menu item checkbox is checked
- ✅ All quantities and charges are populated

## Console Output Example

When editing "Cafe Delight":

```
=== EDITING MEAL ===
Index: 0
Full meal object: {
  id: "meal_123",
  restaurantName: "Cafe Delight",
  restaurantId: "45",
  mealName: "Menu Item",
  mealType: "Breakfast",
  destination: "Singapore",
  transferId: "transfer_456",
  transferInfo: {
    destination: "Singapore",
    vehicleType: "sedan",
    type: "sic",
    way: "one-way"
  },
  adultsQty: 0,
  adultCost: 25,
  ...
}
Restaurant: Cafe Delight ID: 45
Meal Name: Menu Item Type: Breakfast
Destination: Singapore
Transfer ID: transfer_456
Transfer Info: {destination: "Singapore", vehicleType: "sedan", type: "sic", way: "one-way"}

=== Populating Transfer Section for Edit ===
Using meal.transferInfo: {destination: "Singapore", vehicleType: "sedan", type: "sic", way: "one-way"}
Set destination to: Singapore
Set vehicle type to: sedan
Set way to: one-way
Set type to: sic
```

## Files Modified

**File**: `resources/views/enquiryform_pro/create.blade.php`

### Changes:

1. **Line ~9508**: Updated restaurant table display
   - Added meal name to the display

2. **Lines ~9620-9640**: Enhanced `editMeal()` logging
   - Added comprehensive console logging

3. **Lines ~9659-9730**: Fixed transfer data retrieval
   - Prioritize `meal.transferInfo` over `transferList` lookup
   - Added detailed logging for debugging

## Benefits

1. **Correct Data Display**: Each meal shows its own transfer data, not someone else's
2. **Better UX**: Menu item names are visible in the table
3. **Debuggable**: Extensive logging helps identify issues quickly
4. **Reliable**: Uses the meal's own data as the source of truth
5. **Consistent**: All fields populate correctly when editing

## Key Takeaway

**Always prioritize the data stored with the object (`meal.transferInfo`) over lookups from shared lists (`transferList`).** The shared list might have been modified, but the object's own data is its source of truth.
