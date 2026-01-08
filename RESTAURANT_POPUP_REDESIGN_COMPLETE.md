# Restaurant Popup Redesign - Complete

## Date: December 20, 2025

## Summary
Redesigned the restaurant popup to match the accommodation popup structure. Transfer is now configured once for the entire restaurant booking, not per meal type.

## Changes Made

### ✅ 1. Removed Filter Tabs
**Removed**: Breakfast/Lunch/Dinner filter tabs
**Result**: All meals are now shown together in one list

### ✅ 2. Removed Per-Row Transfer Columns
**Removed from table header**:
- Transfer checkbox column
- Destination column
- Vehicle Type column
- Way column
- Transfer Type column

**New column count**: 9 columns (was 14)
- Checkbox
- Meal Type
- No Of Meals
- Adults, Charges/pax
- Child, Charges/pax
- Infant, Charges/pax

### ✅ 3. Added Restaurant Transfer Section
Added a new section below the meals table:

```html
<div class="border-top pt-2 mt-2 px-2">
    <div class="form-check">
        <input type="checkbox" id="restaurantTransferCheckbox">
        <label>Add Transfer for this Restaurant</label>
    </div>
    
    <div id="restaurantTransferDetailsSection" style="display: none;">
        <!-- Destination dropdown -->
        <!-- Vehicle Type dropdown -->
        <!-- Way dropdown -->
        <!-- Transfer Type dropdown -->
    </div>
</div>
```

### ✅ 4. Updated JavaScript Functions

#### `toggleRestaurantTransferFields()`
Shows/hides transfer details when checkbox is checked.

#### `openMealModal()`
- Updated colspan from 14 to 9
- Resets restaurant transfer checkbox
- Hides transfer details section

#### `loadMealsByRestaurant()` (Dynamic Rows)
- Removed transfer columns from dynamically generated meal rows
- Removed filter application (shows all meals)

#### `saveAndCloseMeals()` - Major Rewrite

**For Editing:**
- Reads transfer data from restaurant transfer section (not per-row)
- Creates ONE transfer entry if checkbox is checked
- Links the transfer to the meal being edited

**For Adding New:**
- Reads transfer data from restaurant transfer section once
- Creates ONE shared transfer entry if checkbox is checked
- Links the same transfer ID to ALL selected meals
- All meals share the same transfer

#### `editMeal()`
- Removed meal type filter activation
- Added code to populate restaurant transfer section:
  - Checks the transfer checkbox if transfer exists
  - Shows the transfer details section
  - Populates destination, vehicle type, way, and transfer type fields

## New Data Flow

### Adding Meals with Transfer:

1. User selects destination and restaurant
2. User checks meals to add (breakfast, lunch, dinner)
3. User checks "Add Transfer for this Restaurant"
4. User fills in transfer details (destination, vehicle, way, type)
5. On save:
   - Creates meal entries for each selected meal
   - Creates ONE transfer entry
   - Links the same transfer ID to all meals

### Editing Meals with Transfer:

1. User clicks edit on a meal
2. Modal opens with:
   - Selected meal checked
   - Restaurant transfer checkbox checked (if transfer exists)
   - Transfer fields populated with saved values
3. User can modify and save
4. Transfer is updated for the meal

### Transfer Table Display:

Transfer shows as: **"Restaurant Name / Destination"**
Example: "Cafe Delight / Singapore"

## Benefits

1. **Simpler UI**: One transfer configuration for entire restaurant booking
2. **Consistent with Accommodation**: Same pattern as hotel transfers
3. **Logical Grouping**: One restaurant visit = one transfer
4. **Less Repetitive**: Don't duplicate transfer settings for each meal type
5. **Cleaner Table**: Meals table is more focused on meal details

## Files Modified

**File**: `resources/views/enquiryform_pro/create.blade.php`

### HTML Changes:
1. Removed filter tabs (lines ~1906-1918)
2. Updated table header from 14 to 9 columns
3. Removed transfer columns from 3 static meal rows
4. Added Restaurant Transfer Section with checkbox and fields
5. Updated colspan from 14 to 9 in empty state message

### JavaScript Changes:
1. Added `toggleRestaurantTransferFields()` function
2. Updated `openMealModal()` to reset transfer section
3. Updated `loadMealsByRestaurant()` to remove transfer columns from dynamic rows
4. Rewrote `saveAndCloseMeals()` to use restaurant transfer section
5. Updated `editMeal()` to populate restaurant transfer section

## Testing Checklist

- [x] Open restaurant popup - no filter tabs shown
- [x] All meals (breakfast, lunch, dinner) shown together
- [x] Transfer section appears below meals table
- [x] Transfer checkbox toggles transfer fields visibility
- [x] Add meals with transfer - ONE transfer created
- [x] Multiple meals share the same transfer ID
- [x] Edit meal with transfer - transfer fields populated correctly
- [x] Transfer table shows "Restaurant Name / Destination" format
- [x] Remove meal - linked transfer is also removed

## Known Behavior

1. **One Transfer Per Restaurant Booking**: All meals (breakfast, lunch, dinner) from the same restaurant booking share one transfer
2. **Transfer Applies to All Meals**: If you add breakfast, lunch, and dinner with transfer, they all use the same transfer configuration
3. **Editing Updates Transfer**: When editing a meal, you can update the transfer details which affects that meal's transfer

## Future Enhancements

1. Could add option to have different transfers for different meal types if needed
2. Could add transfer cost/sell fields to the transfer section
3. Could add validation to ensure destination is selected if transfer checkbox is checked
