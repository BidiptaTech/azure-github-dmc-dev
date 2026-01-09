# Miscellaneous Items - Updated Approach (Like Restaurant Selection)

## 🔄 **Changed Approach**

Based on your feedback, the system now works exactly like **Restaurant/Attraction selection**:

1. **Admin** creates miscellaneous items (Product Level 1)
2. **DMC** selects items and sets prices (like selecting restaurants)
3. **Enquiry Pro Form** fetches selected items with prices

---

## ✅ **What's Implemented**

### 1. **Database Tables**
- ✅ `miscellaneous_items` - Master items table
- ✅ `miscellaneous_prices` - DMC pricing table

### 2. **Routes Updated**

```php
// Admin Routes (Product Level 1)
GET    /miscellaneous              -> miscellaneous.index
GET    /miscellaneous/create       -> miscellaneous.create
POST   /miscellaneous              -> miscellaneous.store
GET    /miscellaneous/{id}/edit    -> miscellaneous.edit
PUT    /miscellaneous/{id}         -> miscellaneous.update
DELETE /miscellaneous/{id}         -> miscellaneous.destroy

// DMC Routes (Like Restaurant Selection)
GET    /services/miscellaneous        -> services.miscellaneous (selection page)
POST   /services/miscellaneous/update -> services.miscellaneous.update (bulk update)
POST   /services/miscellaneous/select -> services.miscellaneous.select (select item)
POST   /services/miscellaneous/remove -> services.miscellaneous.remove (remove item)

// API Route (Enquiry Pro Form)
GET    /api/miscellaneous/dmc/{dmcId} -> api.miscellaneous.dmc
```

### 3. **Controller Methods Added**

In `App\Http\Controllers\Admin\MiscellaneousItemController.php`:

| Method | Purpose |
|--------|---------|
| `dmcMiscellaneousSelection()` | Show selection page (like restaurants) |
| `updateDmcMiscellaneous()` | Bulk update selected items & prices |
| `selectMiscellaneous()` | AJAX select single item |
| `removeMiscellaneous()` | AJAX remove single item |
| `getItemsForDmc()` | API for Enquiry Pro form |

### 4. **Sidebar Menu**

**Admin Menu** (Product Configuration):
- "Miscellaneous Items" → `/miscellaneous`

**DMC Menu** (Services Management):
- "Select Miscellaneous" → `/services/miscellaneous`

---

## 🎯 **How It Works Now**

### **Admin Workflow:**
1. Go to: **Product Configuration → Miscellaneous Items**
2. Add items (name, description, image)
3. No prices - just items

### **DMC Workflow (Like Restaurants):**
1. Go to: **Services → Select Miscellaneous**
2. See two sections:
   - **Available Items** (not selected)
   - **Selected Items** (with prices)
3. Select items and set prices:
   - Adult Price/Cost
   - Child Price/Cost
   - Infant Price/Cost
4. Save all at once (bulk update)

### **Enquiry Pro Form:**
```javascript
// Load items for DMC
fetch('/api/miscellaneous/dmc/4')
  .then(r => r.json())
  .then(items => {
    // Use items in form
  });
```

---

## 📋 **Next Steps to Complete**

### 1. **Run Migration**
```bash
php artisan migrate
```

### 2. **Create Admin Views**

Create: `resources/views/miscellaneous/` folder

Files needed:
- `index.blade.php` - List items
- `create.blade.php` - Create item
- `edit.blade.php` - Edit item

(Same code as in `MISCELLANEOUS_NEXT_STEPS.md`, but update routes from `admin.miscellaneous.X` to `miscellaneous.X`)

### 3. **Create DMC Selection View**

Create: `resources/views/services/miscellaneous.blade.php`

**Example structure** (copy from `services/restaurants.blade.php`):

```blade
@extends('layouts.layout')
@section('title', 'Select Miscellaneous Items')

@section('content')
<div class="container-fluid">
    <h3>Select Miscellaneous Items & Set Prices</h3>
    
    <form action="{{ route('services.miscellaneous.update') }}" method="POST">
        @csrf
        
        <!-- Available Items Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Available Items</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Select</th>
                            <th>Item Name</th>
                            <th>Adult Price</th>
                            <th>Child Price</th>
                            <th>Infant Price</th>
                            <th>Adult Cost</th>
                            <th>Child Cost</th>
                            <th>Infant Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($availableItems as $item)
                        <tr>
                            <td>
                                <input type="checkbox" name="selected_items[{{ $item->mis_id }}][selected]" value="1">
                            </td>
                            <td>{{ $item->item_name }}</td>
                            <td>
                                <input type="number" step="0.01" class="form-control form-control-sm" 
                                       name="selected_items[{{ $item->mis_id }}][adult_price]" value="0">
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control form-control-sm" 
                                       name="selected_items[{{ $item->mis_id }}][child_price]" value="0">
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control form-control-sm" 
                                       name="selected_items[{{ $item->mis_id }}][infant_price]" value="0">
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control form-control-sm" 
                                       name="selected_items[{{ $item->mis_id }}][adult_cost]" value="0">
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control form-control-sm" 
                                       name="selected_items[{{ $item->mis_id }}][child_cost]" value="0">
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control form-control-sm" 
                                       name="selected_items[{{ $item->mis_id }}][infant_cost]" value="0">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Selected Items Section -->
        <div class="card">
            <div class="card-header">
                <h5>Your Selected Items</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Keep</th>
                            <th>Item Name</th>
                            <th>Adult Price</th>
                            <th>Child Price</th>
                            <th>Infant Price</th>
                            <th>Adult Cost</th>
                            <th>Child Cost</th>
                            <th>Infant Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($selectedItems as $item)
                        <tr>
                            <td>
                                <input type="checkbox" name="selected_items[{{ $item->mis_id }}][selected]" value="1" checked>
                            </td>
                            <td>{{ $item->item_name }}</td>
                            <td>
                                <input type="number" step="0.01" class="form-control form-control-sm" 
                                       name="selected_items[{{ $item->mis_id }}][adult_price]" 
                                       value="{{ $item->adult_price }}">
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control form-control-sm" 
                                       name="selected_items[{{ $item->mis_id }}][child_price]" 
                                       value="{{ $item->child_price }}">
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control form-control-sm" 
                                       name="selected_items[{{ $item->mis_id }}][infant_price]" 
                                       value="{{ $item->infant_price }}">
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control form-control-sm" 
                                       name="selected_items[{{ $item->mis_id }}][adult_cost]" 
                                       value="{{ $item->adult_cost }}">
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control form-control-sm" 
                                       name="selected_items[{{ $item->mis_id }}][child_cost]" 
                                       value="{{ $item->child_cost }}">
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control form-control-sm" 
                                       name="selected_items[{{ $item->mis_id }}][infant_cost]" 
                                       value="{{ $item->infant_cost }}">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary mt-3">Save Selection & Prices</button>
    </form>
</div>
@endsection
```

---

## 🚀 **Summary**

### **Files Modified:**
1. ✅ `routes/web.php` - Updated route structure
2. ✅ `app/Http/Controllers/Admin/MiscellaneousItemController.php` - Added DMC selection methods
3. ✅ `resources/views/layouts/sidebar.blade.php` - Updated menu items

### **What You Need to Do:**
1. Run `php artisan migrate`
2. Create admin views in `resources/views/miscellaneous/`
3. Create DMC selection view in `resources/views/services/miscellaneous.blade.php`
4. Test the flow!

---

## 📝 **Testing Checklist**

- [ ] Admin can create miscellaneous items
- [ ] DMC can see selection page
- [ ] DMC can select items and set prices
- [ ] DMC can update prices
- [ ] DMC can remove items
- [ ] Enquiry Pro form fetches items via API
- [ ] Items display correctly in form

---

## 🎯 **Key Difference from Before**

**Before**: Separate admin & DMC pricing interfaces

**Now**: 
- Admin = Product management only
- DMC = Selection + Pricing (like restaurants)
- Simpler & more consistent with existing patterns!
