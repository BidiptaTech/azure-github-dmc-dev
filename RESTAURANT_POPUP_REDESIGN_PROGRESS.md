# Restaurant Popup Redesign - Progress

## Goal
Redesign the restaurant popup to match the accommodation popup structure:
- Show all breakfast, lunch, dinner options in a table
- Have a separate "Restaurant Transfer Section" below (not per-row transfers)
- Transfer applies to the entire restaurant booking, not individual meals

## Completed Changes

### ✅ 1. Updated Table Header
- Removed transfer-related columns from the meals table header
- Reduced from 14 columns to 9 columns:
  - Checkbox
  - Meal Type
  - No Of Meals
  - Adults, Charges/pax
  - Child, Charges/pax
  - Infant, Charges/pax

### ✅ 2. Updated Static Meal Rows
- Removed transfer fields from all 3 static rows:
  - Additional Breakfast (meal-id="1")
  - Dinner (meal-id="2")
  - Lunch (meal-id="3")
- Each row now only has meal-related fields

### ✅ 3. Added Restaurant Transfer Section
Added a new section below the meals table with:
```html
<div class="border-top pt-2 mt-2 px-2">
    <div class="form-check">
        <input type="checkbox" id="restaurantTransferCheckbox" onchange="toggleRestaurantTransferFields()">
        <label>Add Transfer for this Restaurant</label>
    </div>
    
    <div id="restaurantTransferDetailsSection" style="display: none;">
        <!-- Transfer fields: Destination, Vehicle Type, Way, Transfer Type -->
    </div>
</div>
```

### ✅ 4. Added Toggle Function
Created `toggleRestaurantTransferFields()` function to show/hide transfer details when checkbox is checked.

## Pending Changes

### 🔄 1. Update `openMealModal()` Function
Need to reset the restaurant transfer section when opening the modal:
```javascript
// Reset restaurant transfer section
const restaurantTransferCheckbox = document.getElementById('restaurantTransferCheckbox');
if (restaurantTransferCheckbox) {
    restaurantTransferCheckbox.checked = false;
}

const restaurantTransferDetailsSection = document.getElementById('restaurantTransferDetailsSection');
if (restaurantTransferDetailsSection) {
    restaurantTransferDetailsSection.style.display = 'none';
}
```

### 🔄 2. Update Dynamic Meal Row Generation
The `loadMealsByRestaurant()` function generates dynamic meal rows. Need to remove transfer columns from the generated HTML.

### 🔄 3. Update `saveAndCloseMeals()` Function
Currently reads transfer data from each row. Need to change to:
1. Read transfer data from the restaurant transfer section (single set of fields)
2. Create ONE transfer entry if checkbox is checked
3. Link the transfer to ALL selected meals

### 🔄 4. Update `editMeal()` Function
When editing a meal with transfer:
1. Check the restaurant transfer checkbox
2. Populate the restaurant transfer fields (destination, vehicle type, way, type)
3. Show the transfer details section

### 🔄 5. Remove Per-Row Transfer Logic
Clean up all code that reads/writes per-row transfer fields:
- Remove `.meal-transfer-checkbox` selectors
- Remove `.meal-transfer-destination` selectors
- Remove `.meal-vehicle-type` selectors
- Remove `.meal-direction` selectors
- Remove `.meal-transfer-type` selectors

### 🔄 6. Update Data Structure
Change how transfer is stored in meal objects:
- Currently: Each meal can have its own transfer
- New: All meals in one restaurant booking share one transfer

## New Data Flow

### Adding Meals with Transfer:
1. User selects meals (breakfast, lunch, dinner)
2. User checks "Add Transfer for this Restaurant"
3. User fills in transfer details (destination, vehicle, way, type)
4. On save:
   - Create meal entries for each selected meal
   - Create ONE transfer entry
   - Link the transfer ID to all meal entries

### Editing Meals with Transfer:
1. User clicks edit on a meal
2. Modal opens with:
   - Selected meals checked
   - Restaurant transfer checkbox checked (if transfer exists)
   - Transfer fields populated with saved values
3. User can modify and save

## Benefits of New Design

1. **Simpler UI**: Users don't need to configure transfer for each meal type
2. **Consistent with Accommodation**: Same pattern as hotel transfers
3. **Logical Grouping**: One restaurant booking = one transfer
4. **Less Repetitive**: Don't duplicate transfer settings for breakfast/lunch/dinner

## Next Steps

1. Update `openMealModal()` to reset transfer section
2. Update dynamic row generation to remove transfer columns
3. Rewrite `saveAndCloseMeals()` to use new transfer section
4. Update `editMeal()` to populate transfer section
5. Test adding meals with/without transfer
6. Test editing meals with/without transfer
7. Verify transfer table displays correctly
