# Attractions Filter Fix - Singapore Location Issue

## Problem
The enquiry form was showing only 7 attractions for Singapore when there were many more in the database. The attractions were not being filtered by location/destination on the initial page load.

## Root Cause
In the `EnquiryFormPro` controller's `create()` method, attractions were being loaded based on DMC ID and Status only, **without filtering by the user's destination (location field)**. This meant:

1. **Initial Load**: All attractions for the DMC were loaded, regardless of location
   - ✅ DMC ID filter applied
   - ✅ Status filter applied
   - ❌ Location filter MISSING

2. **AJAX Call**: When selecting a destination in the modal, attractions were correctly filtered by all three conditions
   - ✅ DMC ID filter applied
   - ✅ Status filter applied
   - ✅ Location filter applied

This inconsistency caused the issue where all attractions (not just Singapore ones) were shown on initial load.

## Filter Conditions Applied

The fix ensures that attractions are filtered by **ALL THREE conditions**:

| Condition | Field | Value | Purpose |
|-----------|-------|-------|---------|
| 1. DMC ID | `dmc_id` (JSON) | Current user's DMC ID | Only show attractions assigned to this DMC |
| 2. Status | `status` | 1 (active) | Only show active attractions |
| 3. Location | `location` | User's destination (e.g., "Singapore") | Only show attractions in the selected location |

**Query Example for Singapore:**
```php
Attraction::whereJsonContains('dmc_id', (int) $dmc_id)
    ->where('status', 1)
    ->where('location', 'Singapore')
    ->get();
```

This ensures that if there are 50 attractions in the database, but only 15 match all three conditions (DMC ID + Status + Location = Singapore), then only those 15 will be displayed.

## Changes Made

### 1. EnquiryFormPro Controller (`app/Http/Controllers/EnquiryFormPro.php`)

#### Lines 70-98: Added location filtering for DMC users
```php
// Get attractions for this DMC (attractions use 'location' field, not 'city')
// Filter by destination if we have initial data, otherwise use user's country
$attractionDestination = $destination;
if ($initialData && isset($initialData['destination_display'])) {
    // For multiple destinations, we'll load attractions for all of them
    if (isset($initialData['destinations_array'])) {
        $attractionDestination = $initialData['destinations_array'];
    } else {
        $attractionDestination = $initialData['destination_display'];
    }
}

$attractionsQuery = Attraction::whereJsonContains('dmc_id', (int) $dmc_id)
    ->where('status', 1);

// Apply destination filter
if (is_array($attractionDestination)) {
    $attractionsQuery->whereIn('location', $attractionDestination);
} else {
    $attractionsQuery->where('location', $attractionDestination);
}

$attractions = $attractionsQuery
    ->select('attraction_id', 'name', 'location', 'open_time', 'close_time', 
             'adult_price', 'child_price', 'senior_adult_price')
    ->orderBy('name')
    ->get();
```

#### Lines 114-138: Added location filtering for non-DMC users (fallback)
Same filtering logic applied to the fallback case when no DMC ID is available.

### 2. Attraction Model (`app/Models/Attraction.php`)

#### Line 14: Added primary key definition
```php
protected $primaryKey = 'attraction_id';
```

This ensures that `$attr->id` works correctly in Blade templates, as the table uses `attraction_id` instead of the default `id` field.

## How It Works Now

### Initial Page Load
1. User's country/destination is determined (default: Singapore)
2. If initial data exists from popup, use that destination(s)
3. **Attractions are filtered by ALL THREE conditions**:
   - ✅ **DMC ID**: `whereJsonContains('dmc_id', (int) $dmc_id)` - Only attractions assigned to this DMC
   - ✅ **Status**: `where('status', 1)` - Only active attractions
   - ✅ **Location**: `where('location', $destination)` - Only attractions in the selected destination (e.g., Singapore)

### AJAX Call (When Changing Destination)
The existing `getAttractionsByDestination()` method (lines 378-427) applies the same three filters:
- ✅ **Status**: `where('status', 1)` - Line 409
- ✅ **Location**: `where('location', $destination)` - Line 410
- ✅ **DMC ID**: `whereJsonContains('dmc_id', (int) $dmc_id)` - Line 413 (if applicable)

### Multiple Destinations Support
If the user selects multiple destinations in the popup:
- The code uses `whereIn('location', $attractionDestination)` to fetch attractions for all selected destinations
- Single destination uses `where('location', $attractionDestination)`

## Benefits

1. **Three-Way Filtering**: All attractions are filtered by DMC ID + Status + Location
2. **Consistent Filtering**: Both initial load and AJAX calls apply the same three filters
3. **Better Performance**: Only relevant attractions are loaded, reducing data transfer
4. **Accurate Display**: Users see only attractions that match ALL three conditions
5. **Multiple Destination Support**: Properly handles both single and multiple destination scenarios

## Testing

To verify the fix:

1. **Single Destination Test**:
   - Login as a DMC user with Singapore as their country
   - Navigate to Enquiry Form Pro
   - Verify that only Singapore attractions are shown initially
   - Change destination in the modal and verify attractions update correctly

2. **Multiple Destinations Test**:
   - Use the popup to select multiple destinations
   - Verify attractions for all selected destinations are shown

3. **Database Verification**:
   - Check the database for attractions where `location = 'Singapore'` and `status = 1`
   - Compare with what's displayed in the form
   - All matching attractions should now be visible

## Related Files
- `app/Http/Controllers/EnquiryFormPro.php` - Main controller with filtering logic
- `app/Models/Attraction.php` - Model with primary key definition
- `resources/views/enquiryform_pro/create.blade.php` - View that displays attractions

