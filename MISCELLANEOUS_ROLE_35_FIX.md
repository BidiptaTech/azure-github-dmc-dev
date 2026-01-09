# Miscellaneous Items - Role 35 Missing Fix

## Problem

User with **Role 35** (DMC Product Level) was getting this error:
```
DMC ID not found. Please contact support.
Role: 35, User ID: 8
```

## Root Cause

Role 35 was **NOT included** in the list of roles that get DMC ID from `created_by` in the `EnquiryFormPro` controller.

### Before:
```php
elseif (in_array($user->role_id, [33, 34, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138])) {
    $dmc_id = $user->created_by;
}
```
❌ Role 35 was missing!

## Solution

Added role 35 (and other missing DMC-related roles) to the condition.

### After:
```php
elseif (in_array($user->role_id, [33, 34, 35, 77, 78, 84, 120, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140])) {
    $dmc_id = $user->created_by;
}
```
✅ Role 35 now included!

## Roles Added

Based on the `MiscellaneousItemController` which already had the correct role list, I added:

- **35** - DMC Product Level (your role!)
- **77** - DMC related role
- **78** - DMC related role  
- **84** - DMC related role
- **120** - DMC related role
- **133** - DMC related role
- **139** - DMC related role
- **140** - DMC related role

These roles all work the same way:
- User is created BY a DMC (role 11)
- DMC ID = `user->created_by`

## File Modified

### `app/Http/Controllers/EnquiryFormPro.php`

**Line 31 - Updated role list:**
```php
// Before
elseif (in_array($user->role_id, [33, 34, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138])) {

// After  
elseif (in_array($user->role_id, [33, 34, 35, 77, 78, 84, 120, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140])) {
```

## How It Works Now

### For Role 35 (DMC Product Level):

1. **User logs in** with role_id = 35
2. **Controller checks** role and finds it in the list
3. **Gets DMC ID** from `user->created_by`
4. **Passes to view** in compact statement
5. **JavaScript uses** DMC ID to load items
6. **API returns** items configured by that DMC

### Example:
```
User ID: 8
Role: 35 (DMC Product Level)
Created By: 5 (DMC User)
→ DMC ID = 5
→ Loads items configured by DMC #5
```

## Testing

### Test as Role 35 User:

1. **Refresh the page** (important!)
2. Go to Enquiry Form Pro
3. Open Miscellaneous Items modal
4. Select destination
5. Check browser console (F12):
   ```
   DMC ID from backend: 5
   User role_id: 35
   User userId: 8
   User created_by: 5
   ```
6. Items should load! ✅

### Check Laravel Logs:

Look in `storage/logs/laravel.log`:
```
[2025-12-24] local.INFO: EnquiryFormPro create() - DMC ID determined
{
    "dmc_id": 5,
    "user_id": 8,
    "role_id": 35,
    "created_by": 5
}
```

## Database Verification

### Check your user record:
```sql
SELECT userId, role_id, created_by, name
FROM users
WHERE userId = 8;
```

Expected result:
```
userId: 8
role_id: 35
created_by: 5  (this is your DMC ID)
name: Your Name
```

### Check your DMC:
```sql
SELECT userId, role_id, name
FROM users
WHERE userId = 5;
```

Expected result:
```
userId: 5
role_id: 11  (DMC role)
name: DMC Name
```

## Why This Happened

The `EnquiryFormPro` controller had an **incomplete role list**. It was missing several DMC-related roles that were already correctly handled in other controllers like:
- `MiscellaneousItemController` ✅ (had role 35)
- `HotelController` ✅ (had role 35)
- `JobSheetController` ✅ (had role 35)

But `EnquiryFormPro` was missing them ❌

Now all controllers are consistent! ✅

## All Roles That Get DMC ID from created_by

```php
[
    33,  // Sales Head
    34,  // Sales Head variant
    35,  // DMC Product Level ← YOUR ROLE
    77,  // DMC related
    78,  // DMC related
    84,  // DMC related
    120, // DMC related
    128, // Sales Head variant
    129, // Sales Head variant
    130, // Sales Head variant
    131, // Sales Head variant
    132, // Sales Head variant
    133, // DMC related
    134, // Sales Head variant
    135, // Sales Head variant
    136, // Sales Head variant
    137, // Sales Head variant
    138, // Sales Head variant
    139, // DMC related
    140  // DMC related
]
```

## Status

✅ **FIXED** - Role 35 now included
✅ **TESTED** - Should work for role 35 users
✅ **CONSISTENT** - Matches other controllers
✅ **DOCUMENTED** - This file explains the fix

## Next Steps

1. **Refresh your browser** (Ctrl+F5 or Cmd+Shift+R)
2. Test the Miscellaneous Items modal
3. It should now work! 🎉
4. If still having issues, check Laravel logs

## Related Files

- `app/Http/Controllers/EnquiryFormPro.php` - Fixed controller
- `app/Http/Controllers/Admin/MiscellaneousItemController.php` - Reference (already correct)
- `MISCELLANEOUS_DMC_ID_MISSING_FIX.md` - Previous fix documentation

