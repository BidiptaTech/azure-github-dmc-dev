# Implementation Summary - Group Pax & Markup Columns

## ✅ What Was Done

The Group Pax and Markup settings have been implemented as **COLUMNS** in the `settings` table (not as separate rows).

## 📊 Database Structure

### New Columns Added to `settings` Table

```sql
ALTER TABLE settings ADD COLUMN group_pax INTEGER NULL;
ALTER TABLE settings ADD COLUMN markup_service VARCHAR(255) NULL;
ALTER TABLE settings ADD COLUMN markup_type VARCHAR(255) NULL;
ALTER TABLE settings ADD COLUMN markup_value DECIMAL(10, 2) NULL;
```

**Column Details:**
- `group_pax` - INTEGER - Number of pax for groups
- `markup_service` - VARCHAR - Options: `all_service`, `hotels_only`, `others_only`
- `markup_type` - VARCHAR - Options: `by_value`, `by_percentage`
- `markup_value` - DECIMAL(10,2) - The markup value/percentage

## 🚀 Deployment Steps

### Option 1: Laravel Migration (Recommended)
```bash
cd c:\xampp\htdocs\azure_new_files
php artisan migrate
```

### Option 2: Direct SQL (PostgreSQL)
```bash
psql -U your_username -d your_database -f database/manual_updates/add_master_settings.sql
```

### Option 3: Direct SQL (MySQL)
```bash
mysql -u your_username -p your_database < database/manual_updates/add_master_settings_mysql.sql
```

## 📁 Files Modified

### 1. Migration File (New)
- **Path**: `database/migrations/2025_01_31_000000_add_group_pax_and_markup_columns_to_settings_table.php`
- **Action**: Adds 4 columns to settings table
- **Rollback**: Supported

### 2. View File
- **Path**: `resources/views/master-setting.blade.php`
- **Changes**: Added 4 input fields in the form

### 3. Controller File
- **Path**: `app/Http/Controllers/MasterSettingController.php`
- **Changes**: 
  - `index()`: Reads from columns
  - `store()`: Writes to columns

### 4. SQL Files (Manual)
- **PostgreSQL**: `database/manual_updates/add_master_settings.sql`
- **MySQL**: `database/manual_updates/add_master_settings_mysql.sql`

## 🎯 How It Works

### Saving Settings
When the form is submitted:
1. The controller receives `group_pax`, `markup_service`, `markup_type`, `markup_value`
2. It updates **ALL** rows in the settings table with these column values
3. This ensures consistency across all settings records

### Reading Settings
When displaying the form:
1. The controller gets the first settings record: `Setting::first()`
2. It reads the column values: `$settings->group_pax`, etc.
3. These values are passed to the view

## 💻 Code Examples

### Retrieving in Code
```php
use App\Models\Setting;

$settings = Setting::first();

$groupPax = $settings->group_pax;
$markupService = $settings->markup_service;
$markupType = $settings->markup_type;
$markupValue = $settings->markup_value;
```

### Applying Markup Logic
```php
if ($settings->markup_service === 'all_service' || 
    ($settings->markup_service === 'hotels_only' && $serviceType === 'hotel')) {
    
    if ($settings->markup_type === 'by_percentage') {
        $markup = $basePrice * ($settings->markup_value / 100);
    } else {
        $markup = $settings->markup_value;
    }
    
    $finalPrice = $basePrice + $markup;
}
```

## ✅ Testing Checklist

- [ ] Run migration: `php artisan migrate`
- [ ] Access Master Settings page
- [ ] Verify 4 new fields are visible
- [ ] Enter test values:
  - Group Pax: 10
  - Markup Service: All Service
  - Markup Type: By Percentage
  - Markup Value: 15.00
- [ ] Click Submit
- [ ] Refresh page - verify values persist
- [ ] Check database: `SELECT group_pax, markup_service, markup_type, markup_value FROM settings LIMIT 1;`
- [ ] Verify other settings (logo, currency, etc.) still work

## 🔄 Rollback

If you need to remove the columns:

```bash
php artisan migrate:rollback
```

Or manually:

**PostgreSQL:**
```sql
ALTER TABLE settings DROP COLUMN IF EXISTS group_pax;
ALTER TABLE settings DROP COLUMN IF EXISTS markup_service;
ALTER TABLE settings DROP COLUMN IF EXISTS markup_type;
ALTER TABLE settings DROP COLUMN IF EXISTS markup_value;
```

**MySQL:**
```sql
ALTER TABLE `settings` DROP COLUMN `group_pax`;
ALTER TABLE `settings` DROP COLUMN `markup_service`;
ALTER TABLE `settings` DROP COLUMN `markup_type`;
ALTER TABLE `settings` DROP COLUMN `markup_value`;
```

## 📝 Notes

- All columns are NULLABLE (optional)
- Values are stored in the same table row as other settings
- All settings records will have the same values for these columns
- The implementation is backward compatible
- No existing functionality is affected

