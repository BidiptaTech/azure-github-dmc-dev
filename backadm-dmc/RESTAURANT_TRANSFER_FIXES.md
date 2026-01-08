# Restaurant Transfer Display and Editing Fixes

## Date: December 20, 2025

## Issues Fixed

### ✅ 1. Transfer Table Display - Restaurant Name / Destination Format

**Problem**: 
When a restaurant had a transfer, the transfer table was showing "Restaurant Name Transfer" instead of "Restaurant Name / Destination" format.

**Root Cause**:
The transfer entry `service` field was being set as:
```javascript
service: `${restaurantName} Transfer`
```

**Solution**:
Updated the service field to include the destination:
```javascript
service: `${restaurantName} / ${transferDestination}`
```

**Files Modified**:
- Line ~9245: Updated editing meal section
- Line ~9362: Updated adding new meal section

**Example**:
- **Before**: "Golden Dragon Restaurant Transfer"
- **After**: "Golden Dragon Restaurant / Sentosa"

---

### ✅ 2. Restaurant Editing - Transfer Fields Not Populated

**Problem**: 
When editing a restaurant that had a linked transfer, the transfer fields (destination, vehicle type, way, transfer type) were not showing the selected values.

**Root Cause**:
The code was checking `meal.transferInfo` which contains basic transfer metadata, but when a transfer is created, the full details are stored in the `transferList` array with the `transferId` reference. The editing function wasn't looking up the transfer details from `transferList`.

**Solution**:

#### A. Updated `populateMealFormForEdit` function (Line ~9768-9795)
Added logic to look up transfer details from `transferList` if `transferId` exists:

```javascript
// Transfer fields
const transferCheckbox = rowToUse.querySelector(`.meal-transfer-checkbox[data-meal-id="${mealId}"]`);
if (transferCheckbox) {
    const hasTransfer = !!meal.transferInfo || !!meal.transferId;
    transferCheckbox.checked = hasTransfer;
    if (hasTransfer) {
        // Try to get transfer info from transferList if transferId exists
        let tInfo = meal.transferInfo || {};
        if (meal.transferId) {
            const linkedTransfer = transferList.find(t => t.id === meal.transferId);
            if (linkedTransfer) {
                tInfo = {
                    destination: linkedTransfer.destination,
                    vehicleType: linkedTransfer.vehicleType || 'sedan',
                    type: linkedTransfer.mode === 'Private' ? 'private' : 'sic',
                    way: linkedTransfer.way === 'Both Way' ? 'both-way' : 'one-way'
                };
            }
        }
        setVal('.meal-transfer-destination', tInfo.destination);
        setVal('.meal-vehicle-type', tInfo.vehicleType || 'sedan');
        setVal('.meal-transfer-type', tInfo.type || 'sic');
        const direction = tInfo.way === 'both-way' || tInfo.way === 'Both Way' || tInfo.way === '2way' ? '2way' : '1way';
        setVal('.meal-direction', direction);
    }
}
```

#### B. Updated `ensureMealRowForEdit` function (Line ~9594-9612)
Added logic to get transfer info from `transferList` when creating a dynamic row:

```javascript
// Get transfer info - check transferList first if transferId exists
let transferInfo = meal.transferInfo || {};
const hasTransfer = !!meal.transferInfo || !!meal.transferId;

if (meal.transferId && !meal.transferInfo) {
    const linkedTransfer = transferList.find(t => t.id === meal.transferId);
    if (linkedTransfer) {
        transferInfo = {
            destination: linkedTransfer.destination,
            vehicleType: linkedTransfer.vehicleType || 'sedan',
            type: linkedTransfer.mode === 'Private' ? 'private' : 'sic',
            way: linkedTransfer.way === 'Both Way' ? 'both-way' : 'one-way'
        };
    }
}
```

#### C. Created `getMealDestinationOptionsHTML` helper function (Line ~7568-7577)
Created a new helper function specifically for meal transfer destinations that uses simple destination names (not IDs):

```javascript
// Helper function to get meal transfer destination options HTML (simple destination names)
function getMealDestinationOptionsHTML(selectedValue) {
    const destinations = [
        @foreach($destinations as $dest)
            '{{ $dest->name }}',
        @endforeach
    ];
    return destinations.map(dest => 
        `<option value="${dest}" ${selectedValue === dest ? 'selected' : ''}>${dest}</option>`
    ).join('');
}
```

**Why a separate function?**
- Attraction transfers use complex IDs like `port_123`, `attraction_456`
- Meal transfers use simple destination names like `"Singapore"`, `"Sentosa"`
- The new function accepts an optional `selectedValue` parameter to pre-select the correct destination

#### D. Updated dynamic row generation (Line ~9083-9087)
Changed to use the new `getMealDestinationOptionsHTML()` function instead of `getDestinationOptionsHTML()`.

---

## Transfer Data Flow

### When Creating a Meal with Transfer:

1. User checks transfer checkbox and fills in:
   - Destination (e.g., "Sentosa")
   - Vehicle Type (e.g., "Sedan")
   - Way (e.g., "2 Way[H/R]")
   - Transfer Type (e.g., "Seat in Coach")

2. Code creates transfer entry in `transferList`:
```javascript
{
    id: transferEntryId,
    dateTime: dateTime,
    service: `${restaurantName} / ${transferDestination}`,  // ✅ Now includes destination
    mode: transferType === 'private' ? 'Private' : 'SIC',
    vehicleType: vehicleType,
    way: direction === '2way' ? 'Both Way' : 'One Way',
    destination: transferDestination,
    adults: adultsQty,
    child: childQty,
    cost: 0,
    sell: 0,
    isStandalone: false
}
```

3. Code stores reference in meal object:
```javascript
{
    ...mealData,
    transferId: transferEntryId,
    transferInfo: {
        destination: transferDestination,
        vehicleType: vehicleType,
        type: transferType,
        way: direction
    }
}
```

### When Editing a Meal with Transfer:

1. Code looks up transfer from `transferList` using `meal.transferId`
2. Extracts transfer details and populates form fields
3. Transfer checkbox is checked
4. All dropdowns show correct selected values

---

## Testing Checklist

- [x] Add restaurant with transfer - verify transfer shows as "Restaurant Name / Destination" in transfer table
- [x] Edit restaurant with transfer - verify transfer checkbox is checked
- [x] Edit restaurant with transfer - verify destination dropdown shows correct value
- [x] Edit restaurant with transfer - verify vehicle type dropdown shows correct value
- [x] Edit restaurant with transfer - verify way dropdown shows correct value (1-way/2-way)
- [x] Edit restaurant with transfer - verify transfer type dropdown shows correct value (SIC/Private)
- [x] Update restaurant transfer details - verify changes are saved correctly
- [x] Remove restaurant - verify linked transfer is also removed

---

## Files Modified

**File**: `resources/views/enquiryform_pro/create.blade.php`

1. **Line ~9245**: Updated transfer service name format (editing section)
2. **Line ~9362**: Updated transfer service name format (adding section)
3. **Line ~7568-7577**: Created `getMealDestinationOptionsHTML()` helper function
4. **Line ~9594-9612**: Updated `ensureMealRowForEdit` to get transfer info from transferList
5. **Line ~9656**: Updated to use `getMealDestinationOptionsHTML()` in dynamic row
6. **Line ~9768-9795**: Updated `populateMealFormForEdit` to look up transfer from transferList
7. **Line ~9083-9087**: Updated dynamic meal row to use new destination function

---

## Key Improvements

1. **Better Transfer Display**: Transfer table now clearly shows "Restaurant / Destination" format
2. **Complete Transfer Editing**: All transfer fields are now properly populated when editing
3. **Consistent Data Handling**: Transfer data is retrieved from the authoritative source (transferList)
4. **Proper Dropdown Pre-selection**: Destination dropdown correctly shows selected value
5. **Separate Helper Functions**: Meal destinations and attraction destinations now have separate helper functions for clarity

---

## Notes

- The `transferInfo` object in the meal stores basic metadata
- The full transfer details are in `transferList` array
- When editing, we always look up from `transferList` for the most accurate data
- The `getMealDestinationOptionsHTML()` function is specific to meal transfers and uses simple destination names
- The `getDestinationOptionsHTML()` function is for attraction transfers and uses complex IDs
