# Destination Dropdown Fixes

## Date: December 26, 2025

## Issues Fixed

### 1. Header Destination Dropdown Showing When Blank
**Problem:** The destination dropdown in the header was showing even when the search input was empty/blank. Users would click on the input field and see all destinations immediately, which was confusing.

**Solution:** Modified the focus and input event listeners to only show the dropdown when the user starts typing.

**File Changed:** `resources/views/enquiryform_pro/create.blade.php`

**Changes Made (Lines ~3440-3449):**

**Before:**
```javascript
// Show dropdown on input focus
searchInput.addEventListener('focus', () => {
    positionDropdown();
    filterDestinations('');
});

// Filter destinations as user types
searchInput.addEventListener('input', (e) => {
    filterDestinations(e.target.value);
});
```

**After:**
```javascript
// Show dropdown on input focus only if there's input
searchInput.addEventListener('focus', () => {
    // Only show dropdown if user starts typing
    if (searchInput.value.trim().length > 0) {
        positionDropdown();
        filterDestinations(searchInput.value);
    }
});

// Filter destinations as user types
searchInput.addEventListener('input', (e) => {
    const value = e.target.value;
    if (value.trim().length > 0) {
        positionDropdown();
        filterDestinations(value);
    } else {
        dropdown.style.display = 'none';
    }
});
```

**Additional Safeguard (Lines ~3689-3698):**

Added a check in the `filterDestinations()` function itself to prevent the dropdown from showing when the search term is empty:

```javascript
function filterDestinations(searchTerm) {
    const dropdown = document.getElementById('destinationDropdown');
    if (!dropdown) return;
    
    // Don't show dropdown if search term is empty
    if (!searchTerm || searchTerm.trim().length === 0) {
        dropdown.style.display = 'none';
        return;
    }
    
    // ... rest of the function
}
```

This ensures that even if `filterDestinations()` is called from other places (like `updateDestinationTags()`), it won't show the dropdown when the search is empty.

**Behavior:**
- Dropdown now only appears when user starts typing (at least 1 character)
- Dropdown hides when input is cleared
- More intuitive user experience

---

### 2. Miscellaneous Dropdown Not Showing DMC Countries
**Problem:** The miscellaneous items modal's destination dropdown was showing ALL countries instead of only the DMC's countries. This was inconsistent with other modals (Tour Details, Guide, Meal Details) which correctly showed only DMC countries.

**Root Cause:** The `$master_dmc_destinations` variable was being set to all active countries instead of the filtered DMC countries.

**Solution:** Changed `$master_dmc_destinations` to use the same filtered `$countries` collection that is used for the header and other modals.

**File Changed:** `app/Http/Controllers/EnquiryFormPro.php`

**Changes Made (Lines ~133-134):**

**Before:**
```php
// Get destinations for master DMC (all countries they operate in)
$master_dmc_destinations = Country::where('is_active', 1)->orderBy('name')->get();
```

**After:**
```php
// Get destinations for master DMC (use the same filtered countries as header)
$master_dmc_destinations = $countries;
```

**Behavior:**
- Miscellaneous modal now shows only DMC's countries
- Consistent with Tour Details, Guide, and Meal Details modals
- All modals now properly filter destinations based on the DMC's assigned countries

---

## Verification

### How to Test

1. **Header Destination Dropdown:**
   - Open Enquiry Form Pro
   - Click on the destination search input (without typing)
   - ✓ Dropdown should NOT appear
   - Start typing a country name
   - ✓ Dropdown should appear with filtered results
   - Clear the input
   - ✓ Dropdown should disappear

2. **Miscellaneous Dropdown:**
   - Open Enquiry Form Pro
   - Click "+ Add" in the Miscellaneous section
   - Check the Destination dropdown
   - ✓ Should only show DMC's assigned countries (same as header destinations)
   - Compare with Tour Details modal
   - ✓ Both should show the same list of countries

### Related Variables

All modals now use the correct destination variables:

| Modal | Dropdown ID | Variable Used | Status |
|-------|-------------|---------------|--------|
| Header | destinationSearchInput | `$countries` | ✓ Correct |
| Tour Details | tourDestination | `$destinations` (= `$countries`) | ✓ Correct |
| Guide | guideDestination | `$destinations` (= `$countries`) | ✓ Correct |
| Meal Details | mealDestination | `$destinations` (= `$countries`) | ✓ Correct |
| Miscellaneous | miscDestination | `$master_dmc_destinations` (= `$countries`) | ✓ Fixed |

---

## Technical Notes

### Country Filtering Logic (EnquiryFormPro.php)

The `$countries` variable is populated based on the logged-in user's `master_dmc_id`:

1. Find all users with the same `master_dmc_id`
2. Extract all countries from their `country` field (comma-separated)
3. Remove duplicates
4. Get Country objects matching these names
5. Filter by `is_active = 1`
6. Order by name

This ensures that:
- All DMC users see only their assigned countries
- Sub-users inherit the master DMC's countries
- Consistent filtering across all modals

### Auto-Fill Behavior

The `autoFillModalFields()` function (lines ~3780-4200) filters dropdown options based on selected header destinations:
- If header has selected destinations, only those are shown in modal dropdowns
- If only one destination is selected, it's auto-selected in the modal
- This applies to all modals: Tour, Guide, Meal, and Miscellaneous

---

## Files Modified

1. `resources/views/enquiryform_pro/create.blade.php`
   - Lines ~3440-3449: Updated focus and input event listeners

2. `app/Http/Controllers/EnquiryFormPro.php`
   - Lines ~133-134: Changed `$master_dmc_destinations` assignment

---

## Status

✅ **COMPLETED** - Both issues resolved and tested
- No linting errors
- Consistent behavior across all modals
- Improved user experience

