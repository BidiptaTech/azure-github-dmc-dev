# Restaurant Guide Syntax Error Fix

## Issue
JavaScript error in console:
```
Uncaught SyntaxError: Identifier 'oldMeal' has already been declared (at create:16233:19)
```

## Root Cause
In the `saveAndCloseMeals()` function, when editing a meal, the variable `oldMeal` was declared twice:
1. First declaration at line 12028: `const oldMeal = mealList[window.editingMealIndex];`
2. Second declaration at line 12112: `const oldMeal = mealList[window.editingMealIndex];` (duplicate)

This caused a JavaScript syntax error because you cannot declare the same `const` variable twice in the same scope.

## Fix Applied

**File:** `resources/views/enquiryform_pro/create.blade.php`

**Before (Line 12111-12112):**
```javascript
// Get old meal data to check if guide changed
const oldMeal = mealList[window.editingMealIndex];  // ❌ Duplicate declaration
```

**After (Line 12111-12112):**
```javascript
// oldMeal is already declared above at line 12028, reuse it  // ✅ Comment only, no redeclaration
```

## Explanation

The `oldMeal` variable is already declared earlier in the same function scope (line 12028) for handling transfer updates. Since JavaScript uses function-level scoping for variables, we can simply reuse the existing `oldMeal` variable instead of redeclaring it.

## Testing

1. Open browser console
2. Navigate to Enquiry Form Pro
3. Verify no syntax errors appear
4. Test restaurant guide functionality:
   - Add restaurant with guide ✅
   - Edit restaurant guide ✅
   - Remove restaurant guide ✅

## Status
✅ **Fixed** - Syntax error resolved, restaurant guide functionality working correctly.

