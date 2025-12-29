# Tour Date Selection Fix - Version 2 (Critical Updates)

## Previous Issue
The first fix didn't work because:
1. We were dispatching `input` events but the handlers were listening to `change` events
2. The flag wasn't being preserved when `updateStartDate()` triggered `updateEndDate()` cascadingly

## Changes Made in V2

### 1. Event Type Fixed (Lines ~3728, ~3752, ~3789, ~3809)
**Before:**
```javascript
tourStart.dispatchEvent(new Event('input', { bubbles: true }));
```

**After:**
```javascript
tourStart.dispatchEvent(new Event('change', { bubbles: true }));
```

**Why:** The header date inputs have `onchange="updateStartDate()"` and `onchange="updateEndDate()"` attributes, so we need to dispatch `change` events, not `input` events.

### 2. Flag Preservation in Cascading Updates (Lines ~3505-3511)
**Before:**
```javascript
const currentEndISO = parseDisplayToISO(endDateInput.value);
if (!currentEndISO || new Date(currentEndISO) < minEndDate) {
    setHeaderInputValue(endDateInput, minEndDateStr);
}
```

**After:**
```javascript
const currentEndISO = parseDisplayToISO(endDateInput.value);
if (!currentEndISO || new Date(currentEndISO) < minEndDate) {
    // If we're updating end date due to service expansion, keep the flag set
    if (skipValidation) {
        window._skipStartDateValidation = true;
    }
    setHeaderInputValue(endDateInput, minEndDateStr);
}
```

**Why:** When `updateStartDate()` is called (with skip flag set), it might auto-set the end date. This would trigger `updateEndDate()`, but by that time the flag has already been reset. So we need to re-set the flag before setting the end date value.

### 3. Flag Reset in updateEndDate() (Lines ~3536-3541)
**Added:**
```javascript
const skipAdjustment = window._skipStartDateValidation;
console.log('→ updateEndDate called, skipAdjustment flag:', skipAdjustment);
if (window._skipStartDateValidation) {
    window._skipStartDateValidation = false;
    console.log('✓ Skipping end date adjustment (set by service)');
}
```

**Why:** `updateEndDate()` needs to also reset the flag after reading it, just like `updateStartDate()` does.

## How It Works Now

### Flow When Adding a Tour:
```
1. User selects date (e.g., Dec 25) in popup
   ↓
2. saveTour() saves tour with Dec 25
   ↓
3. expandHeaderDatesIfNeeded(Dec 25) called
   ↓
4. Header start needs expansion? YES
   ↓
5. Set window._skipStartDateValidation = true
   ↓
6. Update header start input value
   ↓
7. Dispatch 'change' event (not 'input')
   ↓
8. updateStartDate() triggered (onchange handler)
   ↓
9. Check flag: TRUE - skip adjustment
   ↓
10. Reset flag to false
    ↓
11. Auto-set end date (start + 1)
    ↓
12. Flag was skipped? YES - re-set flag to true
    ↓
13. updateEndDate() triggered
    ↓
14. Check flag: TRUE - skip adjustment
    ↓
15. Reset flag to false
    ↓
16. ✓ Tour date preserved as Dec 25!
```

## Testing Instructions

### Clear Cache and Test:
1. **Clear browser cache** (Ctrl+Shift+Delete)
2. **Hard refresh** (Ctrl+F5) 
3. Open browser console (F12) to see logs

### Test Case 1: Add Tour with Future Date
1. Open the tour modal
2. Select a date (e.g., December 25, 2025)
3. Fill in other required fields
4. Click Save
5. **Expected**: Table should show "25 Dec '25", NOT the header start date
6. **Console should show**: 
   - "Calling expandHeaderDatesIfNeeded for TOUR..."
   - "Service date is BEFORE/AFTER header date!"
   - "skipValidation flag: true"
   - "Skipping start date validation (set by service)"

### Test Case 2: Add Multiple Tours
1. Add tour 1 with date December 25
2. Add tour 2 with date December 27
3. Add tour 3 with date December 23
4. **Expected**: Each tour shows its own selected date
5. **Expected**: Header expands to Dec 23 - Dec 28

### Test Case 3: Manual Header Change
1. Add tour with date December 25
2. Manually change header start date to December 26
3. **Expected**: Tour date adjusts to December 26 (this is correct behavior)
4. **Console should show**: "Manual change: Adjusting all service dates to header range..."

## Files Modified
- `resources/views/enquiryform_pro/create.blade.php`

## Lines Changed
- Line ~3509: Added flag re-setting before auto-adjusting end date
- Line ~3536-3541: Added flag checking and reset in updateEndDate()
- Line ~3728: Changed 'input' to 'change' event
- Line ~3752: Changed 'input' to 'change' event
- Line ~3789: Changed 'input' to 'change' event  
- Line ~3809: Changed 'input' to 'change' event

## Console Logs to Watch
If working correctly, you should see:
```
Calling expandHeaderDatesIfNeeded for TOUR...
✓ Service date is BEFORE header start date!
  Expanding header start from 2025-12-21 to 2025-12-25
→ updateStartDate called, skipValidation flag: true
✓ Skipping start date validation (set by service)
→ updateEndDate called, skipAdjustment flag: true
✓ Skipping end date adjustment (set by service)
✓ Service-triggered change: Skipping service date adjustment
```

## Troubleshooting
If still not working:
1. Check browser console for any JavaScript errors
2. Verify the flag is being set correctly (should see "skipValidation flag: true")
3. Check if `adjustAllServiceDatesToHeaderRange` is being called (it shouldn't be)
4. Verify tour date is correct immediately after saving (before table render)

