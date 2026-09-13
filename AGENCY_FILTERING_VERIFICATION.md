# Agency Filtering by DMC - Verification Guide

## Summary
The agency filtering by DMC is **already implemented and working** in your system. This document explains how it works and how to verify it's functioning correctly.

## How Agency Filtering Works

### 1. Database Structure
- **agencies** table has a `dmc_id` field (JSON array)
- Each agency can be associated with multiple DMCs
- DMCs must "select" agencies to make them available for their team

### 2. Filtering Logic

#### In the Popup Modal (`resources/views/layouts/sidebar.blade.php`)
When a user selects a destination in the "Create Single Tour Pro" popup:
- JavaScript calls: `route('enquiry-form-pro.get-agencies')` 
- Passes: destination name(s)
- Backend filters agencies by:
  1. **Destination/Country** (must match selected destination)
  2. **DMC ID** (must include current user's DMC in the `dmc_id` JSON array)

#### In the Main Form (`resources/views/enquiryform_pro/create.blade.php`)
When the form loads with initial data:
- Controller method: `EnquiryFormPro@create()`
- Filters agencies by:
  1. **Status** = 1 (active)
  2. **DMC ID** using `whereJsonContains('dmc_id', (int) $dmc_id)`
  3. **Destination/Country**

### 3. DMC ID Determination Logic

The system determines the DMC ID based on user role:

```php
Role ID 11: Direct DMC user → DMC ID = user's userId
Role IDs 33, 34, 128, 129, 130, etc.: Sales Head → DMC ID = user's created_by
Role IDs 37, 64, 65, 66, 67, 68: Sales Manager → DMC ID = (created_by user's) created_by
Role IDs 38, 81, 90, 108, etc.: Assistant Sales Manager → DMC ID = ((created_by user's) created_by user's) created_by
```

## Verification Steps

### Step 1: Check User's DMC Assignment
1. Log in as a DMC user or sub-user
2. Open browser console (F12)
3. Click "Create Single Tour Pro" button
4. Select a destination
5. Check console logs for: `Agencies loaded for destination: [destination], DMC ID: [id], Count: [number]`

### Step 2: Verify Agency Selection
1. Ensure your DMC has "selected" agencies in that destination
2. Go to: **Agencies** menu → **Agency List**
3. Check if agencies have your DMC ID in their `dmc_id` field
4. Only agencies with your DMC ID should appear in the dropdown

### Step 3: Check Backend Logs
Enhanced logging has been added. Check Laravel logs at:
`storage/logs/laravel.log`

Look for these log entries:
```
EnquiryFormPro create() - DMC ID determined
EnquiryFormPro create() - Agencies loaded
EnquiryFormPro getAgencies() - AJAX request
```

## Common Issues & Solutions

### Issue 1: "No agencies available (filtered by destination & DMC)"
**Cause:** No agencies in that destination have been selected by your DMC

**Solution:**
1. Go to Agencies menu
2. Select agencies for your DMC in that destination
3. Ensure the agency's `dmc_id` field includes your DMC ID

### Issue 2: Seeing agencies from other DMCs
**Cause:** Database inconsistency or role misconfiguration

**Solution:**
1. Check user's role_id and created_by hierarchy
2. Verify DMC ID determination logic is correct for that role
3. Check logs to see what DMC ID is being used

### Issue 3: Agency shows in list but shouldn't
**Cause:** Agency's `dmc_id` array includes your DMC ID incorrectly

**Solution:**
1. Check the agency record in database
2. Verify `dmc_id` JSON field
3. Remove DMC ID if needed using Agency management

## Code Locations

### Controller
- File: `app/Http/Controllers/EnquiryFormPro.php`
- Methods:
  - `create()` - Lines 78-447 (main form load)
  - `getAgencies()` - Lines 508-577 (AJAX for popup)

### View (Popup Modal)
- File: `resources/views/layouts/sidebar.blade.php`
- Lines: 2322-2970
- JavaScript Functions:
  - `loadAgenciesByDestination()` - Line 2481
  - `loadAgenciesByDestinations()` - Line 2518

### View (Main Form)
- File: `resources/views/enquiryform_pro/create.blade.php`
- Agency Dropdown: Lines 1032-1037

### Model
- File: `app/Models/Agency.php`
- Methods for DMC management:
  - `addDmcId($dmcId)` - Line 97
  - `removeDmcId($dmcId)` - Line 111
  - `hasSelectedByDmc($dmcId)` - Line 125

### Routes
- File: `routes/web.php`
- Route: `enquiry-form-pro.get-agencies` - Line 218

## Testing Checklist

- [ ] Log in as DMC user (role_id = 11)
- [ ] Verify DMC ID is logged correctly in console
- [ ] Select a destination where you have agencies
- [ ] Verify only your agencies appear in dropdown
- [ ] Try with multiple destinations
- [ ] Test with sub-users (Sales Head, Sales Manager, etc.)
- [ ] Verify each role gets correct DMC ID
- [ ] Check that other DMCs' agencies don't appear

## Recent Changes

Added enhanced logging (Jan 2026):
- `EnquiryFormPro@create()` now logs agency loading results
- `EnquiryFormPro@getAgencies()` now logs AJAX requests with DMC ID and results
- Console logs show DMC ID and agency count in popup modal

## Support

If agencies are still not filtering correctly:
1. Check Laravel logs at `storage/logs/laravel.log`
2. Check browser console for JavaScript errors
3. Verify database: `SELECT agency_id, agency_name, country, dmc_id FROM agencies WHERE status = 1`
4. Verify user hierarchy: `SELECT userId, role_id, created_by FROM users WHERE userId = [your_user_id]`

