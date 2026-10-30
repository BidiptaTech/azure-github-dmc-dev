# Default Value Feature - Quick Start Guide

## ✅ Implementation Complete

All components have been successfully implemented and the database migration has been run.

## 🎯 What Was Created

### 1. Database Table: `default_value`
✅ Table created with all required columns
✅ Indexes added for performance
✅ Soft deletes enabled

### 2. Files Created (7 new files)
```
database/migrations/2026_01_13_000000_create_default_value_table.php
app/Models/DefaultValue.php
app/Http/Controllers/DefaultValueController.php
resources/views/default-values/index.blade.php
resources/views/default-values/create.blade.php
resources/views/default-values/edit.blade.php
DEFAULT_VALUE_IMPLEMENTATION.md
```

### 3. Files Modified (2 files)
```
routes/web.php (Added routes)
resources/views/layouts/sidebar.blade.php (Added menu item)
```

## 🚀 How to Access

### For DMC Users:
1. Log in as a DMC user (Role IDs: 11, 35, 76, 111, 139, 140, 130, 132, 133, 135, 136, 137, 138)
2. Navigate to: **DMC Users → All Products → Product Configuration → Default Value**
3. Click "Add Default Value" to configure defaults

### Menu Location:
```
Sidebar
└── DMC Users
    └── All Products
        └── Product Configuration
            ├── Zones
            └── Default Value ← NEW!
```

## 📋 Service Types Available

Each DMC can configure ONE default for each type:

| Service Type | Description | Stored ID | Filter |
|-------------|-------------|-----------|---------|
| **Hotel** | Default hotel | hotel_unique_id | Active hotels for DMC |
| **Restaurant** | Default restaurant | restaurant_id | Active restaurants for DMC |
| **Attraction** | Default attraction | attraction_id | Active attractions for DMC |
| **Car (Private)** | Default private car | vehicle_id | Vehicles with 'private' or 'both' |
| **Car (Shared)** | Default shared car | vehicle_id | Vehicles with 'shared' or 'both' |
| **Port** | Default port | port_id | All active ports |

## 🔧 Features

- ✅ Add, Edit, Delete default values
- ✅ View all configured defaults in a table
- ✅ Status management (Active/Inactive)
- ✅ DMC-specific isolation (each DMC only sees their own)
- ✅ Role-based permissions (same as Zones)
- ✅ Soft deletes (can be restored)
- ✅ Maximum 1 default per service type per DMC (6 types total)
- ✅ Dynamic service selection based on type
- ✅ Color-coded badges and icons

## ⚙️ Technical Details

### Routes
- `GET /default-values` - List all defaults
- `GET /default-values/create` - Create form
- `POST /default-values` - Store new default
- `GET /default-values/{id}/edit` - Edit form
- `PUT /default-values/{id}` - Update default
- `DELETE /default-values/{id}` - Delete default

### Permissions (Same as Zones)
Role IDs with access: 11, 35, 76, 111, 139, 140, 130, 132, 133, 135, 136, 137, 138

### Database Structure
```sql
Table: default_value
- id (Primary Key)
- default_id (Unique Identifier)
- dmc_id (Foreign Key to users)
- name (ENUM: hotel, restaurant, attraction, car_private, car_shared, port)
- service_id (VARCHAR: stores hotel_unique_id, restaurant_id, port_id, etc.)
- status (TINYINT: 1=Active, 0=Inactive)
- deleted_at (Soft Delete)
- created_at
- updated_at
```

## 🧪 Testing Checklist

To test the implementation:

1. **Login as DMC user** (Role ID 11 recommended)
2. **Check menu visibility**
   - Go to sidebar → DMC Users → All Products → Product Configuration
   - Verify "Default Value" appears after "Zones"
3. **Test Create**
   - Click "Default Value" menu
   - Click "Add Default Value"
   - Select "Hotel" and choose a hotel
   - Set status to "Active"
   - Click "Save"
4. **Test View**
   - Verify the hotel appears in the list
   - Check that the badge shows "Hotel" with green color
5. **Test Edit**
   - Click the pencil icon
   - Change to a different hotel
   - Click "Update"
6. **Test Delete**
   - Click the delete icon
   - Confirm deletion
   - Verify it's removed from the list
7. **Test Constraints**
   - Try adding another hotel default
   - Should show error: "Default value for hotel already exists"
8. **Test Other Types**
   - Add Restaurant, Attraction, Car (Private), Car (Shared), Port
   - Verify each can be added once

## 📝 Business Rules

1. **One Per Type**: Each DMC can only have 1 default per service type (max 6 total)
2. **DMC Isolation**: DMCs can only see and manage their own defaults
3. **Active Services**: Only active services appear in dropdowns
4. **Vehicle Filtering**: 
   - Car Private: Shows vehicles with type='private' or 'both'
   - Car Shared: Shows vehicles with type='shared' or 'both'
5. **Port Availability**: Ports are available to all DMCs (not DMC-specific)
6. **Updates Allowed**: DMCs can change their defaults at any time
7. **Soft Delete**: Deleted defaults are soft-deleted (can be restored)

## 🎨 UI Features

- **Color-Coded Badges**:
  - Hotel: Green (success)
  - Restaurant: Yellow (warning)
  - Attraction: Blue (info)
  - Car (Private): Primary Blue
  - Car (Shared): Gray (secondary)
  - Port: Dark (dark)

- **Icons**:
  - Hotel: 🏨 (ri-hotel-line)
  - Restaurant: 🍽️ (ri-restaurant-2-line)
  - Attraction: 🏞️ (ri-landscape-line)
  - Car (Private): 🚗 (ri-car-line)
  - Car (Shared): 🚕 (ri-taxi-line)
  - Port: 🚢 (ri-ship-line)

- **Alerts**: Auto-dismiss after 3 seconds
- **Empty State**: Helpful message when no defaults configured
- **Available Types**: Shows which types can still be added

## 📱 Responsive Design

All views are responsive and work on:
- Desktop
- Tablet
- Mobile

## 🔒 Security

- ✅ All URLs encrypted with Crypt::encrypt()
- ✅ DMC ownership verified on all operations
- ✅ Role-based access control
- ✅ Validation prevents duplicate entries
- ✅ CSRF protection on all forms

## 💡 Future Integration Ideas

1. **Auto-populate Enquiry Forms**: Use defaults when creating tours
2. **Default Value History**: Track changes over time
3. **Usage Statistics**: Show how often defaults are used
4. **API Endpoints**: Mobile app integration
5. **Notifications**: Alert when default service becomes inactive

## 🆘 Troubleshooting

### Menu not visible?
- Check user role ID is in: 11, 35, 76, 111, 139, 140, 130, 132, 133, 135, 136, 137, 138
- Clear browser cache
- Refresh the page

### Can't see services in dropdown?
- Ensure you have active services configured
- Check that services belong to your DMC
- For vehicles, check the vehicle_type field

### Error: "DMC ID not found"
- Contact administrator
- Check your user account setup
- Verify role hierarchy is correct

### Can't add second default of same type?
- This is by design (1 per type per DMC)
- Edit the existing one instead
- Or delete the existing one first

## 📧 Support

For more details, see: `DEFAULT_VALUE_IMPLEMENTATION.md`

---

**Status**: ✅ Ready for Testing
**Migration**: ✅ Completed
**Version**: 1.0
**Date**: January 13, 2026

