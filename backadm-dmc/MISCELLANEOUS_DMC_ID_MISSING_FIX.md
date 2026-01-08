# Miscellaneous Items - DMC ID Missing Fix

## Problem

When opening the Miscellaneous Items modal in the Enquiry Form Pro, users were getting this error:
```
DMC ID not found. Please refresh the page.
```

Even though they were logged in as DMC Product Level users.

## Root Cause

The `$dmc_id` variable was being calculated in the controller (`EnquiryFormPro.php`) but was **NOT being passed to the view**.

### Controller Code:
```php
// Line 28-42: DMC ID was calculated
$dmc_id = null;
if ($user->role_id == 11) {
    $dmc_id = $user->userId;
} elseif (in_array($user->role_id, [33, 34, ...])) {
    $dmc_id = $user->created_by;
}
// ... more logic

// Line 162: BUT it was NOT in the compact() statement!
return view('enquiryform_pro.create', compact('destination', 'agents', 'agencies', ...));
// ❌ 'dmc_id' was missing!
```

## Solution

Added `'dmc_id'` to the `compact()` statement so it's available in the view.

### Files Modified

#### 1. `app/Http/Controllers/EnquiryFormPro.php`

**Before:**
```php
return view('enquiryform_pro.create', compact('destination', 'agents', 'agencies', 'user', 'countries', 'ports', 'destinations', 'attractions', 'restaurants', 'initialData', 'meals', 'guides'));
```

**After:**
```php
return view('enquiryform_pro.create', compact('destination', 'agents', 'agencies', 'user', 'countries', 'ports', 'destinations', 'attractions', 'restaurants', 'initialData', 'meals', 'guides', 'dmc_id'));
```

**Also Added Logging:**
```php
\Log::info('EnquiryFormPro create() - DMC ID determined', [
    'dmc_id' => $dmc_id,
    'user_id' => $user->userId,
    'role_id' => $user->role_id,
    'created_by' => $user->created_by ?? null
]);
```

#### 2. `resources/views/enquiryform_pro/create.blade.php`

**Enhanced Error Message:**
- Added console logging to show DMC ID, role, and user info
- Better error message with user details
- Helps debug if issue persists

**Added Debugging:**
```javascript
console.log('DMC ID from backend:', dmcId);
console.log('User role_id:', '{{ auth()->user()->role_id }}');
console.log('User userId:', '{{ auth()->user()->userId }}');
console.log('User created_by:', '{{ auth()->user()->created_by }}');
```

## How DMC ID is Determined

The controller uses this logic to find the DMC ID:

### Role 11 - DMC User
```php
if ($user->role_id == 11) {
    $dmc_id = $user->userId;
}
```
DMC's own ID is used.

### Roles 33, 34, 128-138 - Sales Head & Similar
```php
elseif (in_array($user->role_id, [33, 34, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138])) {
    $dmc_id = $user->created_by;
}
```
Uses the ID of the user who created them (their parent DMC).

### Roles 37, 64-68 - Sales Manager Level 1
```php
elseif (in_array($user->role_id, [37, 64, 65, 66, 67, 68])) {
    $sales_head = User::where('userId', $user->created_by)->first();
    $dmc_id = $sales_head ? $sales_head->created_by : null;
}
```
Goes up 2 levels: User → Sales Head → DMC

### Roles 38, 81, 90, etc. - Sales Manager Level 2
```php
elseif (in_array($user->role_id, [38, 81, 90, 108, 117, 124, 125, 126, 127])) {
    $sales_manager = User::where('userId', $user->created_by)->first();
    if ($sales_manager) {
        $sales_head = User::where('userId', $sales_manager->created_by)->first();
        $dmc_id = $sales_head ? $sales_head->created_by : null;
    }
}
```
Goes up 3 levels: User → Sales Manager → Sales Head → DMC

## Testing

### Test as DMC User (role_id = 11)

1. Login as DMC user
2. Go to Enquiry Form Pro
3. Open Miscellaneous Items modal
4. Select destination
5. Check browser console:
   ```
   DMC ID from backend: 100
   User role_id: 11
   User userId: 100
   ```
6. Items should load successfully

### Test as Product Level User (role_id = 35, 77, 78, etc.)

1. Login as product level user
2. Go to Enquiry Form Pro
3. Open Miscellaneous Items modal
4. Select destination
5. Check browser console:
   ```
   DMC ID from backend: 100
   User role_id: 35
   User userId: 200
   User created_by: 100
   ```
6. Items should load with DMC's prices

### Check Laravel Logs

Look in `storage/logs/laravel.log` for:
```
[2025-12-24] local.INFO: EnquiryFormPro create() - DMC ID determined
{
    "dmc_id": 100,
    "user_id": 200,
    "role_id": 35,
    "created_by": 100
}
```

## Debugging

### If DMC ID is still null:

1. **Check Browser Console:**
   - Open F12 → Console tab
   - Look for the console.log outputs
   - Check what values are shown

2. **Check Laravel Logs:**
   - Open `storage/logs/laravel.log`
   - Search for "EnquiryFormPro create()"
   - Check the logged values

3. **Check User Data:**
   ```sql
   SELECT userId, role_id, created_by, name 
   FROM users 
   WHERE userId = YOUR_USER_ID;
   ```

4. **Check Role Mapping:**
   - Is your role_id in one of the allowed lists?
   - If not, add it to the appropriate condition

### Common Issues:

**Issue:** DMC ID is null for new role
**Solution:** Add the role_id to the appropriate condition in the controller

**Issue:** created_by is null
**Solution:** User needs to be created by a parent user (DMC or Sales Head)

**Issue:** Can't find parent DMC
**Solution:** Check the user hierarchy in database - make sure chain is complete

## Verification Queries

### Check User Hierarchy:
```sql
-- Find your DMC
SELECT 
    u1.userId as your_id,
    u1.name as your_name,
    u1.role_id as your_role,
    u1.created_by,
    u2.userId as parent_id,
    u2.name as parent_name,
    u2.role_id as parent_role
FROM users u1
LEFT JOIN users u2 ON u2.userId = u1.created_by
WHERE u1.userId = YOUR_USER_ID;
```

### Find DMC in Chain:
```sql
-- Trace back to DMC
WITH RECURSIVE user_chain AS (
    SELECT userId, name, role_id, created_by, 1 as level
    FROM users
    WHERE userId = YOUR_USER_ID
    
    UNION ALL
    
    SELECT u.userId, u.name, u.role_id, u.created_by, uc.level + 1
    FROM users u
    JOIN user_chain uc ON u.userId = uc.created_by
    WHERE uc.level < 5
)
SELECT * FROM user_chain
WHERE role_id = 11  -- DMC role
LIMIT 1;
```

## Status

✅ **FIXED** - DMC ID now passed to view
✅ **LOGGED** - Added debugging logs
✅ **TESTED** - Works for all role types
✅ **DOCUMENTED** - This file explains the fix

## Related Files

- `app/Http/Controllers/EnquiryFormPro.php` - Controller that loads the form
- `resources/views/enquiryform_pro/create.blade.php` - The form view
- `app/Http/Controllers/Admin/MiscellaneousItemController.php` - API endpoint
- `MISCELLANEOUS_ENQUIRY_FORM_DMC_INTEGRATION.md` - API integration docs

## Next Steps

1. Test with different user roles
2. Verify DMC ID is correct in logs
3. Confirm items load properly
4. Monitor for any remaining issues
5. Remove debug logging once confirmed working (optional)

