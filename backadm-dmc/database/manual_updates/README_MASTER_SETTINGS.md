# Master Settings - Group Pax and Markup Implementation

## Overview
This document describes the implementation of new master settings for Group Pax and Markup configuration.

## New Settings Added

### 1. Group Pax
- **Setting Name**: `group_pax`
- **Type**: Integer
- **Description**: Number of passengers for group bookings
- **Location**: General Settings > Master Settings

### 2. Markup Configuration
Three related settings control the markup behavior:

#### a. Markup Service (`markup_service`)
- **Options**:
  - `all_service` - Apply markup to all services
  - `hotels_only` - Apply markup to hotels only
  - `others_only` - Apply markup to other services (excluding hotels)

#### b. Markup Type (`markup_type`)
- **Options**:
  - `by_value` - Apply fixed value markup
  - `by_percentage` - Apply percentage-based markup

#### c. Markup Value (`markup_value`)
- **Type**: Decimal (2 decimal places)
- **Description**: The actual markup value/percentage to apply

## Database Changes

### Settings Table - New Columns
Four new columns have been added to the `settings` table:

| Column Name | Type | Nullable | Description |
|------------|------|----------|-------------|
| `group_pax` | INTEGER | Yes | Number of passengers for group bookings |
| `markup_service` | VARCHAR(255) | Yes | Service type for markup (all_service, hotels_only, others_only) |
| `markup_type` | VARCHAR(255) | Yes | Type of markup calculation (by_value, by_percentage) |
| `markup_value` | DECIMAL(10,2) | Yes | The markup value/percentage |

### Implementation Options

#### Option 1: Run Laravel Migration (Recommended)
```bash
php artisan migrate
```

This will run the migration: `2025_01_31_000000_add_group_pax_and_markup_columns_to_settings_table.php`

#### Option 2: Manual SQL Execution

**For PostgreSQL**:
Run the SQL script located at: `database/manual_updates/add_master_settings.sql`

```bash
psql -U your_username -d your_database -f database/manual_updates/add_master_settings.sql
```

**For MySQL**:
Run the SQL script located at: `database/manual_updates/add_master_settings_mysql.sql`

```bash
mysql -u your_username -p your_database < database/manual_updates/add_master_settings_mysql.sql
```

Or execute the SQL queries directly in your database management tool (pgAdmin, phpMyAdmin, etc.).

## Files Modified

### 1. View File
**File**: `resources/views/master-setting.blade.php`
- Added Group Pax input field
- Added Markup Service dropdown (All Service, Hotels Only, Others Only)
- Added Markup Type dropdown (By Value, By Percentage)
- Added Markup Value input field

### 2. Controller File
**File**: `app/Http/Controllers/MasterSettingController.php`
- Updated `index()` method to fetch settings from columns
- Updated `store()` method to save column values to all settings records
- Handles both column-based settings and name-value pair settings

### 4. SQL File (New)
**File**: `database/manual_updates/add_master_settings.sql`
- Manual SQL queries to insert the new settings

## Usage

### Accessing the Settings Page
1. Login to the admin panel
2. Navigate to: **General Settings > Master Settings**
3. The new fields will appear after Tax Percentage

### Setting Group Pax
1. Enter the number of passengers for group bookings
2. Leave empty if not using group pax functionality

### Configuring Markup
1. **Step 1**: Select **Markup Service** type:
   - All Service - markup applies to everything
   - Hotels Only - markup applies only to hotel bookings
   - Others Only - markup applies to non-hotel services

2. **Step 2**: Select **Markup Type**:
   - By Value - fixed amount markup (e.g., $50)
   - By Percentage - percentage-based markup (e.g., 10%)

3. **Step 3**: Enter **Markup Value**:
   - For "By Value": Enter the fixed amount (e.g., 50.00)
   - For "By Percentage": Enter the percentage (e.g., 10.00 for 10%)

4. Click **Submit** to save changes

## Validation

The form includes client-side validation:
- Group Pax: Must be a positive number (if provided)
- Markup Service: Optional dropdown selection
- Markup Type: Optional dropdown selection
- Markup Value: Must be a non-negative decimal number (if provided)

## Retrieving Settings in Code

To retrieve these settings in your application code:

```php
use App\Models\Setting;

// Get settings from columns (all settings records will have these values)
$settings = Setting::first();

$groupPax = $settings->group_pax ?? '';
$markupService = $settings->markup_service ?? '';
$markupType = $settings->markup_type ?? '';
$markupValue = $settings->markup_value ?? '';

// Example usage
if ($markupService === 'all_service') {
    // Apply markup to all services
    if ($markupType === 'by_percentage') {
        $markup = $price * ($markupValue / 100);
    } else {
        $markup = $markupValue;
    }
}
```

## Notes

- All new fields are optional and can be left empty
- The settings are stored in the same `settings` table using the existing key-value structure
- No migration is required as we're not altering the table structure
- The seeder uses `updateOrCreate` to prevent duplicate entries
- The manual SQL script uses `ON DUPLICATE KEY UPDATE` for the same purpose

## Testing

After implementation:
1. ✅ Access the Master Settings page
2. ✅ Verify all new fields are visible
3. ✅ Test saving with various combinations of values
4. ✅ Verify settings are persisted in the database
5. ✅ Test form validation
6. ✅ Verify existing settings are not affected

## Future Enhancements

Consider implementing:
- Dynamic markup calculation based on these settings
- Admin logs for markup changes
- Different markup rules for different user roles
- Markup preview/calculator

