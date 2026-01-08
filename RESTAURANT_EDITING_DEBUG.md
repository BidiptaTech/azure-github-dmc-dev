# Restaurant Editing Debug Guide

## Issue
When editing a restaurant with a transfer:
1. Transfer checkbox is not checked
2. Destination dropdown shows "Select Destination" instead of the saved value

## Debug Steps

### 1. Check Browser Console
Open the browser console (F12) and click "Edit" on a restaurant that has a transfer. Look for these console logs:

```javascript
Editing meal: {object}
Meal transferId: "transfer_xxx"
Meal transferInfo: {object}
Linked transfer found: {object}
Has transfer: true/false
Looking for transfer with id: "transfer_xxx" Found: {object}
Setting transfer values: {object}
```

### 2. What to Check

#### A. Meal Object Should Have:
```javascript
{
    transferId: "transfer_12345",  // ID of the linked transfer
    transferInfo: {
        destination: "Singapore",
        vehicleType: "sedan",
        type: "sic",
        way: "both-way"
    }
}
```

#### B. TransferList Should Contain:
```javascript
{
    id: "transfer_12345",  // Same as meal.transferId
    destination: "Singapore",
    vehicleType: "sedan",
    mode: "SIC",  // Note: "SIC" or "Private"
    way: "Both Way",  // Note: "Both Way" or "One Way"
    service: "Restaurant Name / Singapore",
    isStandalone: false
}
```

### 3. Common Issues

#### Issue 1: transferId is null or undefined
**Symptom**: Console shows `Meal transferId: null` or `undefined`

**Cause**: Transfer wasn't saved when creating the meal

**Solution**: Check the save meal function to ensure transferId is being set

#### Issue 2: Linked transfer not found
**Symptom**: Console shows `Linked transfer found: undefined`

**Cause**: Transfer was removed from transferList or ID mismatch

**Solution**: Check if transfer exists in transferList array

#### Issue 3: Transfer checkbox not checked
**Symptom**: `hasTransfer` is false even though transfer exists

**Cause**: Both `meal.transferInfo` and `meal.transferId` are falsy

**Solution**: Ensure at least one is set when saving

#### Issue 4: Destination dropdown not populated
**Symptom**: Dropdown shows "Select Destination" even after setVal

**Possible Causes**:
1. The destination value doesn't match any option in the dropdown
2. The dropdown hasn't been populated yet when setVal is called
3. The mealId doesn't match between the row and the selector

### 4. Manual Test

Try this in the browser console after opening the edit modal:

```javascript
// Check if meal has transfer data
console.log('Current editing meal:', mealList[window.editingMealIndex]);

// Check transferList
console.log('All transfers:', transferList);

// Check if the row exists
const row = document.querySelector('.meal-row');
console.log('Meal row found:', row);

// Check if transfer checkbox exists
const checkbox = document.querySelector('.meal-transfer-checkbox');
console.log('Transfer checkbox:', checkbox, 'Checked:', checkbox?.checked);

// Check destination dropdown
const destDropdown = document.querySelector('.meal-transfer-destination');
console.log('Destination dropdown:', destDropdown, 'Value:', destDropdown?.value);
console.log('Destination options:', Array.from(destDropdown?.options || []).map(o => o.value));
```

### 5. Expected Flow

When editing a meal with transfer:

1. `editMeal(index)` is called
2. Meal object is retrieved from `mealList[index]`
3. Modal opens and restaurant/destination are set
4. `populateMealFormForEdit(meal)` is called
5. It finds or creates the meal row
6. It checks `meal.transferInfo` or `meal.transferId`
7. If `meal.transferId` exists, it looks up the transfer in `transferList`
8. It extracts transfer details and calls `setVal()` for each field
9. Transfer checkbox should be checked
10. All dropdowns should show selected values

### 6. Destination Dropdown Issue

The destination dropdown in the restaurant popup should show destinations (countries/cities) like:
- Singapore
- Malaysia
- Thailand
- etc.

**NOT** ports, hotels, restaurants, or attractions.

The static HTML rows use:
```blade
@foreach($destinations as $dest)
    <option value="{{ $dest->name }}">{{ $dest->name }}</option>
@endforeach
```

The dynamic rows use:
```javascript
getMealDestinationOptionsHTML(transferInfo.destination)
```

Make sure `getMealDestinationOptionsHTML()` function is working correctly.

## Next Steps

1. Open browser console (F12)
2. Click "Edit" on a restaurant with transfer
3. Copy all console logs
4. Share the logs to identify the issue
