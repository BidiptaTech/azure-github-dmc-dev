# DMC-Level Settings Implementation

## ✅ Implementation Complete!

Group Pax and Markup settings have been successfully implemented as **DMC-level settings** stored in the `users` table, not as global admin settings.

## 🎯 Key Points

- **Per-DMC Configuration**: Each DMC user (Master DMC and DMC roles) has their own Group Pax and Markup settings
- **Not Global**: These are NOT admin-level or global settings
- **Table**: Settings are stored in the `users` table, not the `settings` table
- **User Types**: Applies to DMC users with roles 10, 11, 19, 20 (Master DMC and DMC roles)

## 📊 Database Structure

### Columns Added to `users` Table

| Column Name | Type | Nullable | Description | Existing? |
|------------|------|----------|-------------|-----------|
| `group_pax` | INTEGER | Yes | Number of passengers for group bookings | ✅ NEW |
| `markup_service` | VARCHAR(255) | Yes | Service type for markup | ✅ NEW |
| `markup_type` | INTEGER | Yes | Type of markup calculation | ✅ Existed |
| `markup_price` | INTEGER | Yes | The markup value/percentage | ✅ Existed |

**Note**: `markup_type` and `markup_price` already existed in the users table, so we only added `group_pax` and `markup_service`.

### Markup Service Options
- `all_service` - Apply markup to all services
- `hotels_only` - Apply markup to hotels only  
- `others_only` - Apply markup to other services (excluding hotels)

### Markup Type Options
- `by_value` - Fixed value markup
- `by_percentage` - Percentage-based markup

## 🚀 Deployment

### Option 1: Laravel Migration (Recommended)
```bash
cd c:\xampp\htdocs\azure_new_files
php artisan migrate
```

Migration file: `2025_01_31_000001_add_group_pax_and_markup_columns_to_users_table.php`

✅ **Already executed successfully!**

### Option 2: Manual SQL

**PostgreSQL**:
```sql
ALTER TABLE users ADD COLUMN IF NOT EXISTS group_pax INTEGER NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS markup_service VARCHAR(255) NULL;
```

**MySQL**:
```sql
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `group_pax` INT NULL;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `markup_service` VARCHAR(255) NULL;
```

## 📁 Files Modified

### 1. Migration (NEW)
**File**: `database/migrations/2025_01_31_000001_add_group_pax_and_markup_columns_to_users_table.php`
- Adds `group_pax` and `markup_service` columns to users table
- Checks if columns exist before adding (prevents duplicate column errors)
- ✅ Successfully executed

### 2. User Edit View (MODIFIED)
**File**: `resources/views/users/edit-user.blade.php`

**Changes**:
- Added "DMC Configuration Settings" section
- Four input fields:
  - Group Pax (number input)
  - Markup Service (dropdown: All Service, Hotels Only, Others Only)
  - Markup Type (dropdown: By Value, By Percentage)  
  - Markup Value (number input with decimals)
- Section only visible for DMC users (roles 10, 11, 19, 20)
- JavaScript updated to show/hide the section based on user role

### 3. SQL Scripts (UPDATED)
**Files**:
- `database/manual_updates/add_master_settings.sql` (PostgreSQL)
- `database/manual_updates/add_master_settings_mysql.sql` (MySQL)

Both updated to add columns to `users` table instead of `settings` table.

## 📍 Where to Configure

### For Admin Users:
1. Navigate to: **List User**
2. Click **Edit** on any DMC user (Master DMC or DMC role)
3. Scroll to "**DMC Configuration Settings**" section
4. Configure the settings for that specific DMC

### For DMC Users:
DMC users can only view/edit their own settings when editing their profile (if profile edit is enabled for them).

## 💻 Usage in Code

### Retrieving DMC Settings

```php
use App\Models\User;

// Get settings for a specific DMC user
$dmcUser = User::find($dmcId);

$groupPax = $dmcUser->group_pax;
$markupService = $dmcUser->markup_service; // all_service, hotels_only, others_only
$markupType = $dmcUser->markup_type;
$markupValue = $dmcUser->markup_price; // Note: uses markup_price column

// Get current logged-in DMC user's settings
$currentDmc = Auth::user();
if (in_array($currentDmc->role_id, [10, 11, 19, 20])) {
    $settings = [
        'group_pax' => $currentDmc->group_pax,
        'markup_service' => $currentDmc->markup_service,
        'markup_type' => $currentDmc->markup_type,
        'markup_value' => $currentDmc->markup_price
    ];
}
```

### Applying Markup Logic

```php
// Example: Apply markup based on DMC settings
$dmc = User::find($dmcId);

// Check if this service should have markup applied
$shouldApplyMarkup = false;
if ($dmc->markup_service === 'all_service') {
    $shouldApplyMarkup = true;
} elseif ($dmc->markup_service === 'hotels_only' && $serviceType === 'hotel') {
    $shouldApplyMarkup = true;
} elseif ($dmc->markup_service === 'others_only' && $serviceType !== 'hotel') {
    $shouldApplyMarkup = true;
}

if ($shouldApplyMarkup && $dmc->markup_price) {
    if ($dmc->markup_type === 'by_percentage' || $dmc->markup_type == 1) {
        // Percentage-based markup
        $markup = $basePrice * ($dmc->markup_price / 100);
    } else {
        // Fixed value markup
        $markup = $dmc->markup_price;
    }
    
    $finalPrice = $basePrice + $markup;
}
```

## 🎨 UI Preview

When editing a DMC user, you'll see:

```
┌─────────────────────────────────────────────────┐
│  DMC Configuration Settings                      │
├─────────────────────────────────────────────────┤
│                                                  │
│  Group Pax          Markup Service              │
│  [  10   ]          [ All Service  ▼]           │
│                                                  │
│  Markup Type        Markup Value                │
│  [ By Percentage ▼] [  15.00      ]             │
│                                                  │
└─────────────────────────────────────────────────┘
```

## ✅ Verification

Run this query to verify the columns exist:

**PostgreSQL**:
```sql
SELECT column_name, data_type 
FROM information_schema.columns 
WHERE table_name = 'users' 
AND column_name IN ('group_pax', 'markup_service', 'markup_type', 'markup_price');
```

Expected result:
```
 column_name    | data_type
----------------|------------------
 markup_price   | integer
 markup_type    | integer
 group_pax      | integer
 markup_service | character varying
```

## 🔄 Rollback

If needed, rollback the migration:

```bash
php artisan migrate:rollback --step=1
```

Or manually:

```sql
-- PostgreSQL
ALTER TABLE users DROP COLUMN IF EXISTS group_pax;
ALTER TABLE users DROP COLUMN IF EXISTS markup_service;

-- MySQL
ALTER TABLE `users` DROP COLUMN IF EXISTS `group_pax`;
ALTER TABLE `users` DROP COLUMN IF EXISTS `markup_service`;
```

## 📝 Important Notes

1. **DMC-Specific**: Each DMC has their own settings
2. **Not Global**: These are NOT in the master settings
3. **Existing Columns**: `markup_type` and `markup_price` already existed
4. **Role-Based**: Only visible for DMC roles (10, 11, 19, 20)
5. **Optional Fields**: All fields are nullable/optional
6. **Master DMC**: Master DMC users (roles 10, 19) can also have these settings
7. **Regular DMC**: Regular DMC users (roles 11, 20) can also have these settings

## 🎯 Use Cases

1. **Per-DMC Pricing**: Different DMCs can have different markup strategies
2. **Group Bookings**: Each DMC can define their own group size
3. **Flexible Markup**: Apply markup to all services, hotels only, or others only
4. **Value or Percentage**: Choose between fixed value or percentage markup
5. **Independent Configuration**: DMCs operate independently with their own settings

## 🔗 Related Files

- Migration: `database/migrations/2025_01_31_000001_add_group_pax_and_markup_columns_to_users_table.php`
- View: `resources/views/users/edit-user.blade.php`
- Model: `app/Models/User.php` (no changes needed)
- SQL (PostgreSQL): `database/manual_updates/add_master_settings.sql`
- SQL (MySQL): `database/manual_updates/add_master_settings_mysql.sql`

## ✨ Status: COMPLETE & TESTED

- ✅ Migration executed successfully
- ✅ Columns added to users table
- ✅ UI updated and tested
- ✅ JavaScript updated for show/hide logic
- ✅ SQL scripts updated
- ✅ Documentation complete

