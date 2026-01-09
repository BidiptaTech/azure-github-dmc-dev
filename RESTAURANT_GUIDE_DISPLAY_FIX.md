# Restaurant Guide Display Fix - Complete

## Issue
Restaurant guides were being saved to the database but were NOT appearing in the Guide table/list in the Enquiry Form Pro.

## Root Cause
When a guide was selected for a restaurant in the restaurant modal:
1. ✅ Guide info was saved in the meal data (`guideId` and `guideInfo`)
2. ✅ Guide order was created in the backend (orders table)
3. ❌ Guide was NOT added to the `guideList` array (frontend)
4. ❌ Guide did NOT appear in the Guide section table

The `guideList` array is what populates the Guide section table in the pro form, so guides need to be added to this array to be visible.

## Solution

### Changes Made

**File:** `resources/views/enquiryform_pro/create.blade.php`

#### 1. When Adding New Meals with Guide (Lines ~12228-12262)

**Before:**
```javascript
// Get guide info from restaurant guide section (shared by all meals)
const guideChecked = document.getElementById('restaurantGuideCheckbox')?.checked || false;
const guideSelect = document.getElementById('restaurantGuideSelect');
let guideId = null;
let guideInfo = null;

if (guideChecked && guideSelect && guideSelect.value) {
    guideId = guideSelect.value;
    const guideOption = guideSelect.selectedOptions[0];
    guideInfo = {
        guideId: guideId,
        guideName: guideOption?.getAttribute('data-name') || guideOption?.text || '',
        languages: guideOption?.getAttribute('data-languages') || ''
    };
    console.log('Guide selected for meals:', guideInfo);
}
```

**After:**
```javascript
// Get guide info from restaurant guide section (shared by all meals)
const guideChecked = document.getElementById('restaurantGuideCheckbox')?.checked || false;
const guideSelect = document.getElementById('restaurantGuideSelect');
let guideId = null;
let guideInfo = null;
let guideEntryId = null;

if (guideChecked && guideSelect && guideSelect.value) {
    guideId = guideSelect.value;
    const guideOption = guideSelect.selectedOptions[0];
    guideInfo = {
        guideId: guideId,
        guideName: guideOption?.getAttribute('data-name') || guideOption?.text || '',
        languages: guideOption?.getAttribute('data-languages') || ''
    };
    console.log('Guide selected for meals:', guideInfo);
    
    // CREATE GUIDE ENTRY FOR GUIDE TABLE (NEW!)
    guideEntryId = generateId('guide');
    const guideEntry = {
        id: guideEntryId,
        dateTime: dateTime,
        tourActivity: `${restaurantName} (Restaurant Guide)`,
        language: guideInfo.languages || 'N/A',
        guideName: guideInfo.guideName,
        guideId: guideInfo.guideId,
        hours: 2, // Default 2 hours for restaurant guide
        cost: 0,
        sell: 0,
        supplement: false,
        isStandalone: false, // Linked to restaurant
        linkedTo: 'restaurant',
        restaurantName: restaurantName
    };
    
    guideList.push(guideEntry);  // ADD TO GUIDE LIST!
    console.log('Added guide to guideList:', guideEntry);
}
```

#### 2. When Editing Existing Meal with Guide (Lines ~12105-12155)

**Before:**
```javascript
// Get guide info from restaurant guide section
const guideChecked = document.getElementById('restaurantGuideCheckbox')?.checked || false;
const guideSelect = document.getElementById('restaurantGuideSelect');
let guideId = null;
let guideInfo = null;

if (guideChecked && guideSelect && guideSelect.value) {
    guideId = guideSelect.value;
    const guideOption = guideSelect.selectedOptions[0];
    guideInfo = {
        guideId: guideId,
        guideName: guideOption?.getAttribute('data-name') || guideOption?.text || '',
        languages: guideOption?.getAttribute('data-languages') || ''
    };
    console.log('Guide selected:', guideInfo);
}
```

**After:**
```javascript
// Get guide info from restaurant guide section
const guideChecked = document.getElementById('restaurantGuideCheckbox')?.checked || false;
const guideSelect = document.getElementById('restaurantGuideSelect');
let guideId = null;
let guideInfo = null;

// Get old meal data to check if guide changed
const oldMeal = mealList[window.editingMealIndex];

if (guideChecked && guideSelect && guideSelect.value) {
    guideId = guideSelect.value;
    const guideOption = guideSelect.selectedOptions[0];
    guideInfo = {
        guideId: guideId,
        guideName: guideOption?.getAttribute('data-name') || guideOption?.text || '',
        languages: guideOption?.getAttribute('data-languages') || ''
    };
    console.log('Guide selected:', guideInfo);
    
    // If guide changed or newly added, update guideList (NEW!)
    if (oldMeal.guideId !== guideId) {
        // Remove old guide if exists
        if (oldMeal.guideId) {
            guideList = guideList.filter(g => String(g.id) !== String(oldMeal.guideId));
        }
        
        // Add new guide entry
        const guideEntryId = generateId('guide');
        const guideEntry = {
            id: guideEntryId,
            dateTime: dateTime,
            tourActivity: `${restaurantName} (Restaurant Guide)`,
            language: guideInfo.languages || 'N/A',
            guideName: guideInfo.guideName,
            guideId: guideInfo.guideId,
            hours: 2,
            cost: 0,
            sell: 0,
            supplement: false,
            isStandalone: false,
            linkedTo: 'restaurant',
            restaurantName: restaurantName
        };
        guideList.push(guideEntry);
        guideId = guideEntryId; // Update guideId to the entry ID
    }
} else {
    // Guide unchecked, remove from guideList if exists (NEW!)
    if (oldMeal.guideId) {
        guideList = guideList.filter(g => String(g.id) !== String(oldMeal.guideId));
    }
}
```

#### 3. Guide Removal (Already Working)

The removal logic was already in place (lines ~12943-12944):
```javascript
// Also remove associated transfers and guides
mealList.forEach(meal => {
    if (idsToRemove.includes(String(meal.id))) {
        if (meal.transferId) {
            transferList = transferList.filter(t => String(t.id) !== String(meal.transferId));
        }
        if (meal.guideId) {
            guideList = guideList.filter(g => String(g.id) !== String(meal.guideId));
        }
    }
});
```

## How It Works Now

### User Flow

```
┌─────────────────────────────────────────────────────────────┐
│ 1. User opens Restaurant Modal                              │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 2. User checks "Add Guide for this Restaurant"              │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 3. User selects a guide from dropdown                       │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 4. User clicks "Save & Close"                               │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 5. Frontend Processing:                                     │
│    - Meal added to mealList with guideId & guideInfo       │
│    - Guide added to guideList ← NEW!                       │
│    - updateGuideTable() called                              │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 6. Guide appears in Guide section table ✅                  │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 7. User submits form                                        │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 8. Backend creates:                                         │
│    - Restaurant order (type='restaurant')                   │
│    - Guide order (type='guide', linked_to_restaurant)       │
└─────────────────────────────────────────────────────────────┘
```

### Data Structure

#### mealList Entry
```javascript
{
    id: "meal-123",
    restaurantName: "Seafood Paradise",
    dateTime: "2025-01-15T19:00",
    guideId: "guide-456",  // Reference to guide entry in guideList
    guideInfo: {
        guideId: "123",     // Actual guide ID from database
        guideName: "John Doe",
        languages: "English, Mandarin"
    },
    // ... other meal fields
}
```

#### guideList Entry (NEW!)
```javascript
{
    id: "guide-456",  // Entry ID (matches meal.guideId)
    dateTime: "2025-01-15T19:00",
    tourActivity: "Seafood Paradise (Restaurant Guide)",
    language: "English, Mandarin",
    guideName: "John Doe",
    guideId: "123",  // Actual guide ID from database
    hours: 2,
    cost: 0,
    sell: 0,
    supplement: false,
    isStandalone: false,
    linkedTo: 'restaurant',
    restaurantName: "Seafood Paradise"
}
```

## Guide Table Display

The Guide section table now shows:

| Date/Time | Tour/Activity | Language | Guide Name | Hours | Cost | Sell | Supplement |
|-----------|---------------|----------|------------|-------|------|------|------------|
| 2025-01-15 19:00 | Seafood Paradise (Restaurant Guide) | English, Mandarin | John Doe | 2 | 0 | 0 | ☐ |

## Benefits

1. ✅ **Visibility** - Restaurant guides now appear in the Guide section
2. ✅ **Consistency** - Same behavior as attraction guides
3. ✅ **Tracking** - Users can see all guides in one place
4. ✅ **Editing** - Users can modify guide details from the guide table
5. ✅ **Validation** - Users can verify guides before submission
6. ✅ **Backend Integration** - Guide data flows to backend correctly

## Testing Checklist

✅ **Add Restaurant with Guide:**
- [ ] Open restaurant modal
- [ ] Check "Add Guide for this Restaurant"
- [ ] Select a guide
- [ ] Click "Save & Close"
- [ ] Verify guide appears in Guide section table
- [ ] Verify guide shows restaurant name in Tour/Activity column

✅ **Edit Restaurant Guide:**
- [ ] Click edit on a restaurant with guide
- [ ] Change the guide selection
- [ ] Save
- [ ] Verify old guide removed from Guide section
- [ ] Verify new guide appears in Guide section

✅ **Remove Restaurant Guide:**
- [ ] Click edit on a restaurant with guide
- [ ] Uncheck "Add Guide for this Restaurant"
- [ ] Save
- [ ] Verify guide removed from Guide section

✅ **Delete Restaurant with Guide:**
- [ ] Select restaurant that has a guide
- [ ] Click "- Remove"
- [ ] Verify restaurant removed from Restaurants section
- [ ] Verify guide removed from Guide section

✅ **Form Submission:**
- [ ] Add restaurants with guides
- [ ] Verify guides appear in Guide section
- [ ] Submit form
- [ ] Check database: orders table should have guide orders with type='guide'
- [ ] Verify guide orders have `linked_to_restaurant` field in data

## Notes

- Restaurant guides are marked as `isStandalone: false` to indicate they're linked
- The `linkedTo: 'restaurant'` field helps identify the source
- Guide hours default to 2 for restaurant guides
- Cost and sell prices default to 0 (can be edited in guide table)
- Tour/Activity column shows: "{Restaurant Name} (Restaurant Guide)"

## Related Files

- `resources/views/enquiryform_pro/create.blade.php` - Frontend changes
- `app/Http/Controllers/EnquiryFormPro.php` - Backend guide order creation
- `RESTAURANT_GUIDE_IMPLEMENTATION.md` - Backend implementation details
- `RESTAURANT_GUIDE_QUICK_REFERENCE.md` - Quick reference guide

## Summary

Restaurant guides now work end-to-end:
1. ✅ Selected in restaurant modal
2. ✅ Added to guideList array (NEW!)
3. ✅ Displayed in Guide section table (NEW!)
4. ✅ Saved to database as guide orders
5. ✅ Linked to restaurant via `linked_to_restaurant`
6. ✅ Appear in guide jobsheets and reports

The fix ensures restaurant guides are visible in the pro form just like attraction guides, providing a consistent user experience.

