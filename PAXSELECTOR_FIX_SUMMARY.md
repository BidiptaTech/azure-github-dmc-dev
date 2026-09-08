# PaxSelector Infinite Loop Fix - Summary

## Problem Analysis

### The "Maximum Depth Error" Issue
When attraction bookings existed with adult/children counts that didn't match the search parameters, the attraction's `PaxSelector` component would throw a "Maximum depth exceeded" error (infinite loop). However, the guide's `PassengerSelection` component didn't have this issue.

## Root Cause

### PaxSelector.jsx (Attraction) - BEFORE FIX:

```javascript
// Lines 186-203 - PROBLEMATIC CODE
useEffect(() => {
    if (isUpdatingFromPropsRef.current) {
      isUpdatingFromPropsRef.current = false;
      return;
    }

    const currentPax = selectedPax || { Adults: 0, Children: 0, Seniors: 0 };
    if (
      currentPax.Adults !== guestCounts.Adults ||
      currentPax.Children !== guestCounts.Children ||
      currentPax.Seniors !== guestCounts.Seniors
    ) {
      onPaxChange(guestCounts);  // ⚠️ TRIGGERS PARENT UPDATE
    }
  }, [guestCounts, onPaxChange, selectedPax]); // ⚠️ selectedPax CAUSES LOOP
```

**The Infinite Loop Cycle:**
1. `selectedPax` changes (from parent)
2. `useEffect` triggers and calls `onPaxChange(guestCounts)`
3. Parent updates state and passes new `selectedPax` prop
4. `useEffect` triggers again → **INFINITE LOOP**

### PassengerSelection.jsx (Guide) - WORKING CORRECTLY:

```javascript
// Lines 165-172 - CORRECT PATTERN
const notifyParent = useCallback((counts) => {
    if (onChange && 
        (!value || 
          value.Adults !== counts.Adults || 
          value.Children !== counts.Children)) {
      onChange(counts);
    }
  }, [onChange, value]); // ✅ Stable dependencies

// Only called explicitly from user interactions (handleCounterChange)
// NOT automatically triggered by prop changes
```

**Key Difference:**
- **Guide:** Uses a memoized callback that's only called when user interacts (clicks +/- buttons)
- **Attraction:** Automatically calls parent onChange in a useEffect whenever props change

## The Solution

### Changes Made to PaxSelector.jsx:

1. **Added a memoized callback similar to PassengerSelection:**
   ```javascript
   // Track last notified value to prevent redundant calls
   const lastNotifiedRef = useRef(null);

   const notifyParent = useCallback((counts) => {
     // Skip if we're updating from props
     if (isUpdatingFromPropsRef.current) {
       isUpdatingFromPropsRef.current = false;
       return;
     }

     // Check if values actually changed from current props
     if (!onPaxChange) return;
     
     const currentPax = selectedPax || { Adults: 0, Children: 0, Seniors: 0 };
     const hasChanged = 
       currentPax.Adults !== counts.Adults ||
       currentPax.Children !== counts.Children ||
       currentPax.Seniors !== counts.Seniors;
     
     // Also check against last notified value to prevent redundant calls
     const lastNotifiedStr = lastNotifiedRef.current;
     const currentStr = JSON.stringify(counts);
     
     if (hasChanged && lastNotifiedStr !== currentStr) {
       lastNotifiedRef.current = currentStr;
       onPaxChange(counts);
     }
   }, [onPaxChange, selectedPax]);
   ```

2. **Updated handleCounterChange to call notifyParent explicitly:**
   ```javascript
   const handleCounterChange = useCallback((name, value) => {
     // ... validation logic ...
     
     updatedCounts = {
       ...updatedCounts,
       [name]: value
     };
     
     setGuestCounts(updatedCounts);
     notifyParent(updatedCounts); // ✅ Explicit call, not automatic
   }, [guestCounts, originalAdultCount, maxValues, notifyParent]);
   ```

3. **Removed the problematic useEffect that was causing the infinite loop**

4. **Added useCallback wrappers for consistency:**
   - `handleClick`
   - `handleClose`
   - `handleCounterChange`

## Why This Fix Works

### Before:
- ❌ Parent updates prop → useEffect runs → calls onChange → parent updates prop → **LOOP**
- ❌ No tracking of last notified value
- ❌ Automatic callback on every prop change

### After:
- ✅ Parent updates prop → state updates but no callback
- ✅ User clicks +/- → handleCounterChange → notifyParent → parent updates (ONE WAY)
- ✅ Tracks last notified value to prevent redundant calls
- ✅ Only calls parent when user explicitly changes values

## Testing Checklist

Test these scenarios to verify the fix:

1. ✅ **Mismatch scenario:** Booking has 2 adults, search has 4 adults
   - Component should display booking's 2 adults
   - No infinite loop should occur
   - User can adjust values up to max (4)

2. ✅ **Normal scenario:** Booking matches search parameters
   - Component should work normally
   - User can adjust values within limits

3. ✅ **User interaction:** Click +/- buttons
   - Values should update correctly
   - Parent should receive updates only when user clicks
   - No duplicate calls to parent

4. ✅ **Seniors adjustment:** Increase/decrease seniors
   - Adults should adjust automatically
   - Total shouldn't exceed maximum
   - Parent should receive correct values

## Benefits

1. **No more infinite loops** - Even when booking and search params don't match
2. **Better performance** - Fewer unnecessary re-renders and parent updates
3. **Consistent pattern** - Now matches the working PassengerSelection pattern
4. **Predictable behavior** - Updates only happen from explicit user interactions

## Code Comparison

| Aspect | OLD (Broken) | NEW (Fixed) |
|--------|--------------|-------------|
| Parent notification | Automatic via useEffect | Explicit via notifyParent callback |
| Loop prevention | Single ref check | Ref + last value tracking |
| User interaction | State update → useEffect → parent | State update → notifyParent → parent |
| Prop changes | Triggers parent update | Updates local state only |
| Performance | Many unnecessary calls | Only calls when needed |

## Related Files

- **Fixed file:** `src/components/Tour-Packages/attraction/PaxSelector.jsx`
- **Reference pattern:** `src/components/Tour-Packages/Guide/PassengerSelection.jsx`
- **Parent component:** `src/components/Tour-Packages/attraction/index.jsx`


