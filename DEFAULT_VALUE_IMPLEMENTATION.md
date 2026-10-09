# Default Value Feature Implementation

## Overview
This document describes the complete implementation of the Default Value feature for the DMC system. This feature allows each DMC to configure default values for 6 different service types: Hotel, Restaurant, Attraction, Car (Private), Car (Shared), and Port.

## Implementation Date
January 13, 2026

## Features

### 1. Database Schema
**Table Name:** `default_value`

**Columns:**
- `id` - Primary key (auto-increment)
- `default_id` - Unique identifier for the default value
- `dmc_id` - Foreign key to the DMC user
- `name` - ENUM: 'hotel', 'restaurant', 'attraction', 'car_private', 'car_shared', 'port'
- `service_id` - Stores the service identifier (hotel_unique_id, restaurant_id, attraction_id, vehicle_id, port_id)
- `status` - TINYINT: 1=Active, 0=Inactive
- `deleted_at` - Soft delete timestamp
- `created_at` - Creation timestamp
- `updated_at` - Update timestamp

**Constraints:**
- Each DMC can have a maximum of 1 default value per service type (6 total)
- The `default_id` field is unique across all records
- Indexes are added on `dmc_id`, `name`, and `(dmc_id, name)` for performance

**Migration File:** `database/migrations/2026_01_13_000000_create_default_value_table.php`

### 2. Model
**Location:** `app/Models/DefaultValue.php`

**Features:**
- Soft deletes enabled
- Relationships with Hotel, Restaurant, Attraction, and Vehicle models
- Dynamic `getServiceAttribute()` method to retrieve the related service
- Helper method `getServiceTypeDisplayName()` for UI display

**Key Methods:**
- `dmc()` - Relationship to User (DMC)
- `hotel()` - Relationship to Hotel
- `restaurant()` - Relationship to Restaurant
- `attraction()` - Relationship to Attraction
- `vehicle()` - Relationship to Vehicle (for both car_private and car_shared)
- `port()` - Relationship to Port

### 3. Controller
**Location:** `app/Http/Controllers/DefaultValueController.php`

**Methods:**
1. `index()` - Display all default values for the DMC
2. `create()` - Show form to add a new default value
3. `store()` - Save a new default value
4. `edit($id)` - Show form to edit an existing default value
5. `update($id)` - Update an existing default value
6. `destroy($id)` - Soft delete a default value
7. `getServices(Request $request)` - AJAX endpoint to fetch services by type

**Security Features:**
- DMC ID resolution based on role hierarchy (same as Zone controller)
- Permission checks to ensure users can only access their own DMC's data
- Validation to prevent duplicate entries per service type
- Encrypted URL parameters for edit/delete operations

**Role-Based Access:**
Supports the following role IDs (same as Zone feature):
- 11, 20: Direct DMC roles
- 35, 130, 132, 133, 135, 136, 137, 138: Team roles under DMC
- 76, 139: Roles under Product Head
- 111, 140: Roles under Product Manager

### 4. Views

#### Index Page (`resources/views/default-values/index.blade.php`)
- Lists all configured default values for the DMC
- Displays service type badges with icons
- Shows service name, status, and last updated time
- Edit and delete actions
- Shows available types that can still be configured
- Empty state with call-to-action when no defaults are set

#### Create Page (`resources/views/default-values/create.blade.php`)
- Form to add a new default value
- Dynamic service selection based on type
- Only shows available service types (not yet configured)
- JavaScript validation and dynamic form handling
- Dropdowns populated with:
  - Hotels: All active hotels for the DMC
  - Restaurants: All active restaurants for the DMC
  - Attractions: All active attractions for the DMC
  - Car (Private): Vehicles with 'private' or 'both' type
  - Car (Shared): Vehicles with 'shared' or 'both' type
  - Ports: All active ports

#### Edit Page (`resources/views/default-values/edit.blade.php`)
- Form to edit an existing default value
- Service type is read-only (cannot be changed)
- Can update the selected service or status
- Pre-populated with current values

### 5. Routes
**Location:** `routes/web.php`

```php
Route::resource('default-values', DefaultValueController::class);
Route::get('/default-values/get-services', [DefaultValueController::class, 'getServices'])->name('default-values.get-services');
```

**Available Routes:**
- `GET /default-values` - Index page
- `GET /default-values/create` - Create form
- `POST /default-values` - Store new default value
- `GET /default-values/{id}/edit` - Edit form
- `PUT /default-values/{id}` - Update default value
- `DELETE /default-values/{id}` - Delete default value
- `GET /default-values/get-services` - AJAX endpoint for services

### 6. Sidebar Menu
**Location:** `resources/views/layouts/sidebar.blade.php`

**Menu Structure:**
- Added under "Product Configuration" section
- Positioned right after "Zones" menu item
- Uses the same permissions as Zones
- Active state highlighting on relevant routes

**Permission Roles (Same as Zones):**
Role IDs: 11, 35, 76, 111, 139, 140, 130, 132, 133, 135, 136, 137, 138

**Menu Display:**
```
DMC Users
└── All Products
    └── Product Configuration
        ├── Zones
        └── Default Value (NEW)
```

### 7. Service Type Details (6 Types)

#### Hotel
- Stores: `hotel_unique_id`
- Displays: Hotel name
- Filter: Active hotels for the DMC
- Maximum: 1 per DMC

#### Restaurant
- Stores: `restaurant_id`
- Displays: Restaurant name
- Filter: Active restaurants for the DMC
- Maximum: 1 per DMC

#### Attraction
- Stores: `attraction_id`
- Displays: Attraction name
- Filter: Active attractions for the DMC
- Maximum: 1 per DMC

#### Car (Private)
- Stores: `vehicle_id`
- Displays: Vehicle name and type
- Filter: Active vehicles with type 'private' or 'both'
- Maximum: 1 per DMC

#### Car (Shared)
- Stores: `vehicle_id`
- Displays: Vehicle name and type
- Filter: Active vehicles with type 'shared' or 'both'
- Maximum: 1 per DMC

#### Port
- Stores: `port_id`
- Displays: Port name and country
- Filter: Active ports
- Maximum: 1 per DMC

## Usage Instructions

### For DMCs:
1. Navigate to DMC Users → All Products → Product Configuration → Default Value
2. Click "Add Default Value" to configure a new default
3. Select the service type (Hotel, Restaurant, Attraction, Car Private, or Car Shared)
4. Choose the specific service from the dropdown
5. Set the status (Active/Inactive)
6. Click "Save Default Value"

### To Edit:
1. Go to the Default Values list
2. Click the edit icon (pencil) next to the default value
3. Change the selected service or status
4. Click "Update Default Value"

### To Delete:
1. Go to the Default Values list
2. Click the delete icon (trash bin) next to the default value
3. Confirm the deletion

## Business Rules

1. **One Per Type**: Each DMC can configure only ONE default value per service type (maximum 6 total)
2. **DMC-Specific**: Default values are specific to each DMC and isolated from others
3. **Active Services Only**: Only active services can be selected as default values
4. **Vehicle Type Filtering**: 
   - Car Private shows vehicles with type 'private' or 'both'
   - Car Shared shows vehicles with type 'shared' or 'both'
5. **Port Availability**: Ports are available to all DMCs (not DMC-specific)
6. **Soft Deletes**: Deleted default values are soft-deleted and can be restored if needed
7. **Future Updates**: DMCs can update or change their default values at any time

## Permissions & Access Control

### Enquiry Form Permissions:
- **Enquiry Pro Form**: Role IDs [33, 37, 38, 128, 129, 130, 134, 135, 136, 138]
- **Enquiry Lite Form**: Role IDs [33, 37, 38, 128, 129, 130, 134, 135, 136, 138]
- Both forms have identical permissions ✓

### Default Value Permissions:
Matches Zone permissions exactly:
- Role IDs: [11, 35, 76, 111, 139, 140, 130, 132, 133, 135, 136, 137, 138]

## Technical Notes

### DMC ID Resolution
The controller uses the same DMC ID resolution logic as the Zone controller:
1. Direct DMC roles (11, 20) → Use userId
2. Team roles (35, 130-138) → Use created_by
3. Product Head roles (76, 139) → Navigate up hierarchy
4. Product Manager roles (111, 140) → Navigate up hierarchy

### Security Measures
1. All IDs in URLs are encrypted using Laravel's Crypt facade
2. DMC ownership is verified on all operations
3. Validation prevents duplicate entries per service type
4. Only services belonging to the DMC are shown in dropdowns

### UI/UX Features
1. Color-coded service type badges
2. Icon representation for each service type
3. Auto-dismissing success/error alerts
4. Dynamic form fields based on selection
5. Available types indicator
6. Empty state with helpful guidance

## Testing Checklist

- [ ] Run migration: `php artisan migrate`
- [ ] Verify table creation and structure
- [ ] Test Create functionality for all 5 service types
- [ ] Test Edit functionality
- [ ] Test Delete functionality (soft delete)
- [ ] Verify permission checks for different roles
- [ ] Test that DMCs can only see their own defaults
- [ ] Verify maximum 1 default per type constraint
- [ ] Test vehicle type filtering (private vs shared)
- [ ] Verify sidebar menu appears correctly
- [ ] Test active state highlighting in sidebar
- [ ] Verify form validation
- [ ] Test with different role IDs

## Future Enhancements

Potential future features:
1. API endpoints for mobile apps
2. Default value history/audit trail
3. Bulk import/export functionality
4. Default value usage statistics
5. Integration with enquiry forms to auto-populate defaults
6. Notification when default service becomes inactive

## Files Modified/Created

### Created:
1. `database/migrations/2026_01_13_000000_create_default_value_table.php`
2. `app/Models/DefaultValue.php`
3. `app/Http/Controllers/DefaultValueController.php`
4. `resources/views/default-values/index.blade.php`
5. `resources/views/default-values/create.blade.php`
6. `resources/views/default-values/edit.blade.php`
7. `DEFAULT_VALUE_IMPLEMENTATION.md` (this file)

### Modified:
1. `routes/web.php` - Added routes and controller import
2. `resources/views/layouts/sidebar.blade.php` - Added menu item and active states

## Support

For issues or questions, refer to:
- Zone implementation (similar structure)
- DMC role hierarchy documentation
- Laravel documentation for soft deletes and relationships

---

**Implementation Complete** ✓
All 7 tasks completed successfully.

