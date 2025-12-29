# Restaurant Transfer Removal Fix

## Date: December 20, 2025

## Issue
When editing a restaurant and unchecking the transfer checkbox, the linked transfer should be removed from the transferList.

## Solution

### Updated Logic in `saveAndCloseMeals()` Function

The save function now follows this sequence when editing:

1. **FIRST: Remove old transfer** (if it exists)
2. **THEN: Create new transfer** (if checkbox is checked)

This ensures that:
- If user unchecks transfer → old transfer is removed, no new transfer is created
- If user keeps transfer checked → old transfer is removed, new transfer is created
- If user checks transfer for first time → no old transfer to remove, new transfer is created

### Code Flow

```javascript
// FIRST: Remove old transfer if it exists
const oldMeal = mealList[window.editingMealIndex];
if (oldMeal && oldMeal.transferId) {
    console.log('Removing old transfer with ID:', oldMeal.transferId);
    transferList = transferList.filter(t => t.id !== oldMeal.transferId);
}

// THEN: Create new transfer if checkbox is checked
if (transferChecked && transferDestination) {
    // Create new transfer entry
    transferEntryId = generateId('transfer');
    transferInfo = { ... };
    transferEntry = { ... };
    transferList.push(transferEntry);
    transferId = transferEntryId;
} else if (!transferChecked) {
    console.log('Transfer checkbox unchecked - no new transfer will be created');
}

// Update meal data
const mealData = {
    ...
    transferId: transferId, // Will be null if transfer unchecked
    transferInfo: transferInfo, // Will be null if transfer unchecked
};
```

## Scenarios

### Scenario 1: User Unchecks Transfer
**Initial State**: Meal has transfer (transferId: "transfer_123")

**User Action**: 
1. Opens edit modal
2. Transfer checkbox is checked
3. User unchecks the transfer checkbox
4. Clicks "Save & Close"

**Result**:
- Old transfer "transfer_123" is removed from transferList
- No new transfer is created
- Meal's transferId is set to null
- Meal's transferInfo is set to null
- Transfer table no longer shows the transfer

### Scenario 2: User Updates Transfer
**Initial State**: Meal has transfer (transferId: "transfer_123", destination: "Singapore")

**User Action**:
1. Opens edit modal
2. Transfer checkbox is checked
3. User changes destination to "Sentosa"
4. Clicks "Save & Close"

**Result**:
- Old transfer "transfer_123" is removed from transferList
- New transfer "transfer_456" is created with destination "Sentosa"
- Meal's transferId is updated to "transfer_456"
- Meal's transferInfo is updated with new values
- Transfer table shows updated transfer

### Scenario 3: User Adds Transfer
**Initial State**: Meal has no transfer (transferId: null)

**User Action**:
1. Opens edit modal
2. Transfer checkbox is unchecked
3. User checks the transfer checkbox
4. User fills in transfer details
5. Clicks "Save & Close"

**Result**:
- No old transfer to remove
- New transfer "transfer_789" is created
- Meal's transferId is set to "transfer_789"
- Meal's transferInfo is set with values
- Transfer table shows new transfer

### Scenario 4: User Keeps Transfer Unchanged
**Initial State**: Meal has transfer (transferId: "transfer_123")

**User Action**:
1. Opens edit modal
2. Transfer checkbox is checked
3. User doesn't change anything
4. Clicks "Save & Close"

**Result**:
- Old transfer "transfer_123" is removed from transferList
- New transfer "transfer_456" is created with same values
- Meal's transferId is updated to "transfer_456"
- Meal's transferInfo remains the same
- Transfer table shows transfer (with new ID but same details)

## Console Logging

Added console logs to help debug transfer operations:

```
=== Editing Meal - Transfer Status ===
Transfer checkbox checked: true/false
Transfer destination: "Singapore"
Removing old transfer with ID: transfer_123
Transfers removed: 1
Creating new transfer for restaurant: Cafe Delight
New transfer created with ID: transfer_456
Updated meal data: {...}
```

## Testing Checklist

- [x] Edit meal with transfer, uncheck transfer → transfer removed
- [x] Edit meal with transfer, change destination → old removed, new created
- [x] Edit meal without transfer, add transfer → new transfer created
- [x] Edit meal with transfer, keep unchanged → old removed, new created
- [x] Transfer table updates correctly after each operation
- [x] Console logs show correct transfer operations

## Files Modified

**File**: `resources/views/enquiryform_pro/create.blade.php`

**Lines Modified**: ~9139-9235 (saveAndCloseMeals function - editing section)

### Key Changes:
1. Moved "remove old transfer" logic BEFORE "create new transfer" logic
2. Added console logging for debugging
3. Ensured transferId and transferInfo are set to null when transfer is unchecked
4. Added explicit check for unchecked transfer with console log

## Benefits

1. **Clean Data**: Old transfers are always removed when editing
2. **Correct Behavior**: Unchecking transfer properly removes it
3. **Debuggable**: Console logs show exactly what's happening
4. **Consistent**: Same logic applies whether adding, updating, or removing transfer

## Notes

- The old transfer is ALWAYS removed first (if it exists)
- A new transfer is only created if the checkbox is checked AND destination is selected
- If transfer is unchecked, transferId and transferInfo are explicitly set to null
- Each edit operation generates a new transfer ID (this is intentional to avoid conflicts)
