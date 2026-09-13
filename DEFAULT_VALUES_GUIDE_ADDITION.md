# Guide Support for Default Values Feature

## Overview
This document describes the implementation of Guide support for the Default Values feature in the DMC system. This enhancement allows DMCs to configure a default guide that can be pre-selected when creating enquiries.

## Implementation Date
January 13, 2026

## Changes Made

### 1. Database Migration
**File:** `database/migrations/2026_01_13_100000_add_guide_to_default_value_table.php`

**Changes:**
- Modified the `name` field constraint in the `default_value` table to include 'guide'
- Updated constraint values from: `['hotel', 'restaurant', 'attraction', 'car_private', 'car_shared', 'port']`
- To: `['hotel', 'restaurant', 'attraction', 'car_private', 'car_shared', 'port', 'guide']`
- **PostgreSQL Compatible:** Uses CHECK constraint instead of ENUM

**Migration Command:**
```bash
php artisan migrate --path=database/migrations/2026_01_13_100000_add_guide_to_default_value_table.php
```

**Status:** ✅ Migration completed successfully on January 13, 2026

### 2. Model Updates
**File:** `app/Models/DefaultValue.php`

**Changes:**
- Added `guide()` relationship method to link with the Guide model using `guide_id`
- Updated `getServiceAttribute()` method to include the 'guide' case
- Updated `getServiceTypeDisplayName()` method to include 'Guide' display name

**Key Code:**
```php
public function guide()
{
    return $this->belongsTo(Guide::class, 'service_id', 'guide_id');
}
```

### 3. Controller Updates
**File:** `app/Http/Controllers/DefaultValueController.php`

**Changes:**
- Added `use App\Models\Guide;` import statement
- Updated `index()` method to load guide relationships
- Updated `create()` method to:
  - Include 'guide' in available types
  - Fetch guides for the DMC (active guides with status 1 or 3)
  - Pass guides to the view
- Updated `store()` validation to accept 'guide' as a valid service type
- Updated `edit()` method to fetch guides for the DMC
- Updated `getServices()` AJAX method to handle guide service type

**Guide Query Logic:**
```php
$guides = Guide::where('dmc_id', $dmcId)
    ->whereIn('status', [1, 3]) // Active guides
    ->select('guide_id', 'name', 'email', 'contact_no')
    ->orderBy('name')
    ->get();
```

### 4. View Updates

#### Create View
**File:** `resources/views/default-values/create.blade.php`

**Changes:**
- Added 'Guide' option to the service type dropdown
- Added guide selection section with dropdown populated from guides table
- Guide dropdown shows guide name and email

#### Edit View
**File:** `resources/views/default-values/edit.blade.php`

**Changes:**
- Added guide section using `@elseif($defaultValue->name == 'guide')`
- Guide dropdown shows guide name and email
- Selected guide is pre-populated based on existing default value

#### Index View
**File:** `resources/views/default-values/index.blade.php`

**Changes:**
- Added guide badge display with purple color and user-star icon
- Updated service name retrieval logic to handle guide names
- Updated info alert to mention "Guide" as a configurable service type
- Updated available types list to include 'guide'
- Changed count check from `< 5` to `< 7` to account for guide

### 5. Guide Data Structure

**Table:** `guides`
**Primary Key:** `guide_id`
**DMC Relationship:** `dmc_id` field links to User table

**Active Guide Status:** 
- Status 1: Active
- Status 3: Also treated as active

**Fields Used:**
- `guide_id` - Primary identifier
- `name` - Guide name
- `email` - Guide email
- `contact_no` - Contact number
- `dmc_id` - DMC ownership
- `status` - Active status

## Usage

### Creating a Default Guide Value

1. Navigate to "Default Values" from the sidebar
2. Click "Add Default Value"
3. Select "Guide" from the Service Type dropdown
4. Select a guide from the dropdown (shows active guides for your DMC)
5. Set the status (Active/Inactive)
6. Click "Save Default Value"

### Editing a Default Guide Value

1. Navigate to "Default Values" list
2. Click the edit (pencil) icon on the guide row
3. Change the selected guide or status
4. Click "Update Default Value"

### Viewing Default Guide

The default guide will appear in the list with:
- A purple badge with user-star icon
- The guide's name
- Active/Inactive status
- Last update timestamp

## Integration Points

The default guide value can be used in:
- **Enquiry Form Pro** - Auto-select the default guide when creating new enquiries
- **Single Tour Packages** - Pre-populate guide selection
- **Job Sheets** - Default guide for job sheet creation

## Validation Rules

- Each DMC can have only ONE default guide
- The guide must belong to the DMC (matched by `dmc_id`)
- The guide must be active (status 1 or 3)
- Service ID must be a valid `guide_id` from the guides table

## Technical Notes

1. **Relationship Key:** The default_value table stores `guide_id` in the `service_id` field
2. **Filtering:** Only guides with `dmc_id` matching the current DMC are available
3. **Status Filter:** Only active guides (status 1 or 3) are shown in dropdowns
4. **AJAX Support:** The `getServices()` method supports dynamic guide loading

## Testing Checklist

- [ ] Migration runs successfully
- [ ] Can create a default guide value
- [ ] Can edit a default guide value
- [ ] Can delete a default guide value
- [ ] Guide appears in the list with correct badge
- [ ] Only DMC's own guides are shown
- [ ] Only active guides are available for selection
- [ ] Validation prevents duplicate guide defaults
- [ ] Guide name displays correctly in the list

## Benefits

1. **Efficiency:** DMCs can pre-select their most commonly used guide
2. **Consistency:** Ensures the same guide is used across enquiries by default
3. **User Experience:** Reduces manual selection work for frequent bookings
4. **Flexibility:** Can be changed or disabled at any time

## Maximum Default Values

After this implementation, each DMC can now configure up to **7 default values**:
1. Hotel
2. Restaurant
3. Attraction
4. Car (Private)
5. Car (Shared)
6. Port
7. **Guide** (NEW)

## Future Enhancements

Potential future improvements:
- Support for multiple default guides (e.g., by language or region)
- Guide availability calendar integration with defaults
- Guide pricing integration
- Auto-assignment based on booking requirements

## Related Files

- `app/Models/Guide.php` - Guide model
- `app/Http/Controllers/GuideController.php` - Guide management
- `database/migrations/*_create_guides_table.php` - Original guide table creation
- `resources/views/guides/guide.blade.php` - Guide management interface

## Author
AI Assistant

## Date
January 13, 2026

