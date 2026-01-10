# Miscellaneous Items Implementation Guide

## Overview
Dynamic Miscellaneous Items system with separate admin and DMC interfaces for managing items and pricing.

---

## Database Structure

### Table 1: `miscellaneous_items` (Master Table)
```sql
- mis_id (PRIMARY KEY)
- item_name (VARCHAR 255)
- description (TEXT, nullable)
- image (VARCHAR, nullable)
- status (TINYINT, default 1) - 1=Active, 0=Inactive
- created_at
- updated_at
- deleted_at (soft deletes)
```

### Table 2: `miscellaneous_prices` (Pricing Table)
```sql
- id (PRIMARY KEY)
- mis_id (FOREIGN KEY -> miscellaneous_items.mis_id)
- dmc_id (UNSIGNEDBIGINT)
- adult_price (DECIMAL 10,2)
- child_price (DECIMAL 10,2)
- infant_price (DECIMAL 10,2)
- adult_cost (DECIMAL 10,2, nullable)
- child_cost (DECIMAL 10,2, nullable)
- infant_cost (DECIMAL 10,2, nullable)
- status (TINYINT, default 1)
- created_at
- updated_at
- UNIQUE KEY (mis_id, dmc_id)
```

---

## Files Created

### 1. **Migrations**
- ✅ `database/migrations/2025_12_24_095010_create_miscellaneous_items_table.php`
- ✅ `database/migrations/2025_12_24_095053_create_miscellaneous_prices_table.php`

### 2. **Models**
- ✅ `app/Models/MiscellaneousItem.php`
  - Relationships: `prices()`, `priceForDmc($dmcId)`
  - Scopes: `active()`
  - Static method: `getItemsForDmc($dmcId)`

- ✅ `app/Models/MiscellaneousPrice.php`
  - Relationship: `item()`
  - Scope: `active()`

### 3. **Controllers**
- ✅ `app/Http/Controllers/Admin/MiscellaneousItemController.php`
  - Full CRUD for admin to manage items
  - Methods: index, create, store, show, edit, update, destroy
  
- ✅ `app/Http/Controllers/Dmc/MiscellaneousPriceController.php`
  - DMC pricing management
  - Methods: index, edit, update, bulkUpdate, getItemsForDmc

### 4. **Routes** (Added to `routes/web.php`)
```php
// Admin Routes
Route::resource('admin/miscellaneous', MiscellaneousItemController::class);

// DMC Routes
Route::get('/dmc/miscellaneous', [MiscellaneousPriceController::class, 'index']);
Route::get('/dmc/miscellaneous/{id}/edit', [MiscellaneousPriceController::class, 'edit']);
Route::put('/dmc/miscellaneous/{id}', [MiscellaneousPriceController::class, 'update']);
Route::post('/dmc/miscellaneous/bulk-update', [MiscellaneousPriceController::class, 'bulkUpdate']);

// API for Enquiry Pro Form
Route::get('/api/miscellaneous/dmc/{dmcId}', [MiscellaneousPriceController::class, 'getItemsForDmc']);
```

### 5. **Sidebar Menu** (Updated `resources/views/layouts/sidebar.blade.php`)
- ✅ Added under "Product Configuration" section:
  - **Admin Menu**: "Miscellaneous Items" (Admin only - role_id 1, 20)
  - **DMC Menu**: "Miscellaneous Pricing" (DMC roles)

---

## How It Works

### Admin Workflow (Super Admin/Product Level 1):
1. Login as Admin (role_id: 1 or 20)
2. Navigate to **Product Configuration → Miscellaneous Items**
3. Add new items with:
   - Item Name
   - Description
   - Image (optional)
   - Status (Active/Inactive)
4. Items are created **without prices** (product level)

### DMC Workflow (Set Pricing):
1. Login as DMC user (role_id: 11, 35, 76, 77, 84, etc.)
2. Navigate to **Product Configuration → Miscellaneous Pricing**
3. See all active miscellaneous items
4. Set prices for each item:
   - Adult Price & Cost
   - Child Price & Cost
   - Infant Price & Cost
   - Status (Active/Inactive)
5. Save prices (unique per DMC)

### Enquiry Pro Form Integration:
1. Load miscellaneous items via API: `/api/miscellaneous/dmc/{dmcId}`
2. Returns only items with active prices for that DMC
3. Use returned data to populate Miscellaneous section dynamically

---

## Next Steps (TODO)

### ⏳ **View Files Still Needed**:

#### 1. Admin Views (`resources/views/admin/miscellaneous/`)
- `index.blade.php` - List all items
- `create.blade.php` - Create new item form
- `edit.blade.php` - Edit item form
- `show.blade.php` - View item details (optional)

#### 2. DMC Views (`resources/views/dmc/miscellaneous/`)
- `index.blade.php` - List items with pricing form (bulk update)
- `edit.blade.php` - Edit single item price (optional)

#### 3. Enquiry Pro Form Updates (`resources/views/enquiryform_pro/create.blade.php`)
- Load miscellaneous items via AJAX
- Display items dynamically in Miscellaneous section
- Replace static items with dynamic data

---

## API Response Format

**Endpoint**: `GET /api/miscellaneous/dmc/{dmcId}`

**Response**:
```json
[
  {
    "mis_id": 1,
    "item_name": "Airport Transfer",
    "description": "Transfer service from/to airport",
    "image": "https://example.com/storage/miscellaneous/image.jpg",
    "adult_price": 50.00,
    "child_price": 25.00,
    "infant_price": 0.00,
    "adult_cost": 40.00,
    "child_cost": 20.00,
    "infant_cost": 0.00
  }
]
```

---

## Database Migration Commands

```bash
# Run migrations
php artisan migrate

# Rollback if needed
php artisan migrate:rollback --step=2

# Refresh migrations
php artisan migrate:refresh
```

---

## Usage Example in Enquiry Pro Form

```javascript
// Load miscellaneous items for a DMC
async function loadMiscellaneousItems(dmcId) {
    try {
        const response = await fetch(`/api/miscellaneous/dmc/${dmcId}`);
        const items = await response.json();
        
        // Populate Miscellaneous section
        window.miscellaneousItems = items;
        updateMiscellaneousSection(items);
    } catch (error) {
        console.error('Error loading miscellaneous items:', error);
    }
}

// Usage
loadMiscellaneousItems(4); // DMC ID = 4
```

---

## Permission Notes

### Admin Permissions:
- Role IDs: 1, 20 (Super Admin, Product Admin)
- Can: Create, Edit, Delete items
- Cannot: Set prices (that's DMC's job)

### DMC Permissions:
- Role IDs: 11, 35, 76, 77, 84, 111, 130, 132, 133, 135, 136, 137, 138, 139, 140
- Can: View items, Set/Update prices for their DMC
- Cannot: Create or delete items

---

## Benefits

1. ✅ **Centralized Management**: Admin controls what items exist
2. ✅ **DMC Flexibility**: Each DMC sets their own prices
3. ✅ **No Static Code**: Miscellaneous items are fully dynamic
4. ✅ **Scalable**: Add unlimited items without code changes
5. ✅ **Multi-DMC Support**: Each DMC has independent pricing
6. ✅ **API Ready**: Enquiry Pro form fetches real-time data

---

## Implementation Status

| Task | Status |
|------|--------|
| Database Migrations | ✅ Complete |
| Eloquent Models | ✅ Complete |
| Admin Controller | ✅ Complete |
| DMC Controller | ✅ Complete |
| Routes | ✅ Complete |
| Sidebar Menu | ✅ Complete |
| Admin Views | ⏳ Pending |
| DMC Views | ⏳ Pending |
| Enquiry Pro Integration | ⏳ Pending |

---

## Contact

For questions or issues, refer to:
- Models: `app/Models/MiscellaneousItem.php`
- Controllers: `app/Http/Controllers/Admin/` & `app/Http/Controllers/Dmc/`
- Routes: `routes/web.php` (lines 141-162)
- Sidebar: `resources/views/layouts/sidebar.blade.php` (lines 1275-1289)
