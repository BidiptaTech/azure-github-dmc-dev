# Guide Popup Troubleshooting Guide

## Issue Fixed: "Error loading guides. Please try again."

### Root Cause
The `Guide` model was not imported at the top of the `EnquiryFormPro` controller, causing a class not found error.

### Solution Applied
Added `use App\Models\Guide;` to the controller imports.

## Changes Made

### 1. Added Guide Model Import
**File:** `app/Http/Controllers/EnquiryFormPro.php`

```php
use App\Models\Guide;  // Added this line
```

### 2. Added Error Handling & Logging
Added try-catch block with detailed logging:

```php
try {
    // Guide loading logic
    \Log::info('Guide request received', [...]);
    \Log::info('DMC ID determined', [...]);
    \Log::info('Guides found', [...]);
    
} catch (\Exception $e) {
    \Log::error('Error fetching guides', [
        'error' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile()
    ]);
    
    return response()->json([
        'success' => false,
        'message' => 'Error loading guides: ' . $e->getMessage(),
        'guides' => []
    ], 500);
}
```

### 3. Enhanced JavaScript Error Handling
Added better error messages in the frontend:

```javascript
.then(response => {
    console.log('Response status:', response.status);
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json();
})
.catch(error => {
    console.error('Error loading guides:', error);
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">
        Error loading guides: ${error.message}<br>
        <small>Please check the browser console for more details.</small>
    </td></tr>`;
});
```

## How to Debug Future Issues

### 1. Check Browser Console
Open browser DevTools (F12) → Console tab
- Look for JavaScript errors
- Check the fetch request URL
- Verify the response data

### 2. Check Network Tab
Open browser DevTools (F12) → Network tab
- Find the request to `/enquiry-form-pro/get-guides`
- Check the status code (should be 200)
- Click on the request to see:
  - Request URL
  - Request parameters
  - Response data

### 3. Check Laravel Logs
**File:** `storage/logs/laravel.log`

Look for entries like:
```
[2025-01-20 10:30:00] local.INFO: Guide request received {"destination":"Singapore",...}
[2025-01-20 10:30:00] local.INFO: DMC ID determined {"dmc_id":4}
[2025-01-20 10:30:00] local.INFO: Guides found {"count":5}
```

Or errors:
```
[2025-01-20 10:30:00] local.ERROR: Error fetching guides {"error":"..."}
```

### 4. Test the Route Directly
Open in browser or Postman:
```
GET http://your-domain/enquiry-form-pro/get-guides?destination=Singapore
```

Expected response:
```json
{
  "success": true,
  "guides": [...],
  "count": 5
}
```

## Common Issues & Solutions

### Issue 1: "Destination is required"
**Symptom:** Error message in modal
**Cause:** Destination dropdown is empty or not selected
**Solution:** 
- Check if destinations are loaded in the dropdown
- Verify `$destinations` variable is passed to the view
- Select a destination before the AJAX call

### Issue 2: No guides showing (empty table)
**Symptom:** "No guides found for this destination"
**Possible Causes:**
1. No guides in database for that destination
2. Guides not assigned to user's DMC
3. Guides have `status != 1`

**Solution:**
Check database:
```sql
SELECT * FROM guides 
WHERE city = 'Singapore' 
  AND dmc_id = 4 
  AND status = 1;
```

### Issue 3: "Class 'App\Models\Guide' not found"
**Symptom:** 500 error in network tab
**Cause:** Guide model not imported
**Solution:** Already fixed - ensure `use App\Models\Guide;` is at the top of controller

### Issue 4: Languages dropdown is empty
**Symptom:** Language dropdown shows only "Select Language"
**Cause:** Guide has no languages in `guide_languages` table
**Solution:**
Check database:
```sql
SELECT * FROM guide_languages WHERE guide_id = 123;
```

Add languages for the guide if missing.

### Issue 5: Pricing shows 0.00
**Symptom:** Day Rate shows 0.00
**Cause:** Guide has no pricing set
**Solution:**
Check database:
```sql
SELECT day_rate, twelve_hour_price FROM guides WHERE guide_id = 123;
```

Update guide pricing in the guides management section.

### Issue 6: 404 Error
**Symptom:** Network tab shows 404 for `/enquiry-form-pro/get-guides`
**Cause:** Route not registered or cache issue
**Solution:**
```bash
php artisan route:clear
php artisan route:cache
```

Or check if route exists:
```bash
php artisan route:list | grep get-guides
```

## Testing Checklist

After fixing, verify:
- [ ] Open guide modal - no errors
- [ ] Select destination - guides load
- [ ] Console shows: "Loading guides for destination: Singapore"
- [ ] Console shows: "Guides data received: {success: true, ...}"
- [ ] Network tab shows 200 status for get-guides request
- [ ] Guides table populates with data
- [ ] Language dropdowns show guide's languages
- [ ] Hours dropdown defaults to 12
- [ ] Day Rate shows correct price
- [ ] Can select and save guides

## Quick Fix Commands

If issues persist:

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Regenerate autoload files
composer dump-autoload

# Check logs
tail -f storage/logs/laravel.log
```

## Support Information

If the issue persists after trying all solutions:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console for JavaScript errors
3. Check network tab for failed requests
4. Verify database has guides with:
   - `status = 1`
   - `city` matching destination
   - `dmc_id` matching user's DMC
5. Ensure guide has languages in `guide_languages` table

## Files to Check

| File | Purpose |
|------|---------|
| `app/Http/Controllers/EnquiryFormPro.php` | Controller with AJAX method |
| `routes/web.php` | Route definition |
| `resources/views/enquiryform_pro/create.blade.php` | Frontend JavaScript |
| `storage/logs/laravel.log` | Error logs |
| Database: `guides` table | Guide data |
| Database: `guide_languages` table | Guide languages |

