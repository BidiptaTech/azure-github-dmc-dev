# Miscellaneous Items - Next Steps Implementation Guide

## What's Already Done ✅

1. ✅ Database tables created (`miscellaneous_items`, `miscellaneous_prices`)
2. ✅ Models created with relationships
3. ✅ Controllers created (Admin + DMC)
4. ✅ Routes added to `routes/web.php`
5. ✅ Sidebar menu items added
6. ✅ API endpoint ready for Enquiry Pro form

---

## What You Need to Do Next

### Step 1: Run Database Migration

```bash
cd c:\xampp\htdocs\azure_new_files
php artisan migrate
```

This will create the two new tables.

---

### Step 2: Create Admin Views

Create these view files in `resources/views/admin/miscellaneous/`:

#### A. `index.blade.php` - List all items
```blade
@extends('layouts.layout')
@section('title', 'Miscellaneous Items')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Miscellaneous Items</h5>
            <a href="{{ route('admin.miscellaneous.create') }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i> Add New Item
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Item Name</th>
                            <th>Description</th>
                            <th>DMCs Using</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr>
                            <td>{{ $item->mis_id }}</td>
                            <td>
                                @if($item->image)
                                    <img src="{{ asset('storage/' . $item->image) }}" 
                                         alt="{{ $item->item_name }}" 
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <span class="text-muted">No image</span>
                                @endif
                            </td>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ Str::limit($item->description, 50) }}</td>
                            <td>
                                <span class="badge bg-info">{{ $item->prices_count }} DMCs</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $item->status ? 'success' : 'danger' }}">
                                    {{ $item->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.miscellaneous.edit', $item->mis_id) }}" 
                                   class="btn btn-sm btn-primary">
                                    <i class="ri-edit-line"></i>
                                </a>
                                <form action="{{ route('admin.miscellaneous.destroy', $item->mis_id) }}" 
                                      method="POST" 
                                      style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Are you sure?')">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection
```

#### B. `create.blade.php` - Create new item
```blade
@extends('layouts.layout')
@section('title', 'Create Miscellaneous Item')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Create Miscellaneous Item</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.miscellaneous.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-3">
                    <label for="item_name" class="form-label">Item Name *</label>
                    <input type="text" class="form-control @error('item_name') is-invalid @enderror" 
                           id="item_name" name="item_name" value="{{ old('item_name') }}" required>
                    @error('item_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="image" class="form-label">Image</label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                           id="image" name="image" accept="image/*">
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="status" class="form-label">Status *</label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Create Item</button>
                    <a href="{{ route('admin.miscellaneous.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
```

#### C. `edit.blade.php` - Edit item
```blade
@extends('layouts.layout')
@section('title', 'Edit Miscellaneous Item')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Miscellaneous Item</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.miscellaneous.update', $item->mis_id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="item_name" class="form-label">Item Name *</label>
                    <input type="text" class="form-control @error('item_name') is-invalid @enderror" 
                           id="item_name" name="item_name" value="{{ old('item_name', $item->item_name) }}" required>
                    @error('item_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="3">{{ old('description', $item->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="image" class="form-label">Image</label>
                    @if($item->image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->item_name }}" 
                                 style="max-width: 200px; max-height: 200px;">
                        </div>
                    @endif
                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                           id="image" name="image" accept="image/*">
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="status" class="form-label">Status *</label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="1" {{ old('status', $item->status) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('status', $item->status) == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Update Item</button>
                    <a href="{{ route('admin.miscellaneous.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
```

---

### Step 3: Create DMC Views

Create these files in `resources/views/dmc/miscellaneous/`:

#### `index.blade.php` - DMC Pricing (Bulk Update)
```blade
@extends('layouts.layout')
@section('title', 'Miscellaneous Pricing')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Set Miscellaneous Item Prices</h5>
            <p class="text-muted mb-0">Set your DMC's pricing for miscellaneous items</p>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            
            <form action="{{ route('dmc.miscellaneous.bulk-update') }}" method="POST">
                @csrf
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Adult Price</th>
                                <th>Child Price</th>
                                <th>Infant Price</th>
                                <th>Adult Cost</th>
                                <th>Child Cost</th>
                                <th>Infant Cost</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $index => $item)
                            <tr>
                                <td>{{ $item->item_name }}</td>
                                <input type="hidden" name="items[{{ $index }}][mis_id]" value="{{ $item->mis_id }}">
                                
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm" 
                                           name="items[{{ $index }}][adult_price]" 
                                           value="{{ $item->priceForDmc->adult_price ?? 0 }}" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm" 
                                           name="items[{{ $index }}][child_price]" 
                                           value="{{ $item->priceForDmc->child_price ?? 0 }}" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm" 
                                           name="items[{{ $index }}][infant_price]" 
                                           value="{{ $item->priceForDmc->infant_price ?? 0 }}" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm" 
                                           name="items[{{ $index }}][adult_cost]" 
                                           value="{{ $item->priceForDmc->adult_cost ?? 0 }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm" 
                                           name="items[{{ $index }}][child_cost]" 
                                           value="{{ $item->priceForDmc->child_cost ?? 0 }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm" 
                                           name="items[{{ $index }}][infant_cost]" 
                                           value="{{ $item->priceForDmc->infant_cost ?? 0 }}">
                                </td>
                                <td>
                                    <select class="form-select form-select-sm" name="items[{{ $index }}][status]" required>
                                        <option value="1" {{ ($item->priceForDmc->status ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ ($item->priceForDmc->status ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Save All Prices</button>
                </div>
            </form>
            
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection
```

---

### Step 4: Update Enquiry Pro Form

In `resources/views/enquiryform_pro/create.blade.php`, add this JavaScript:

```javascript
// Add this in your JavaScript section (around line 11500+)

// Global variable to store miscellaneous items
let miscellaneousItemsList = [];

// Load miscellaneous items for the current DMC
async function loadMiscellaneousItems(dmcId) {
    try {
        const response = await fetch(`/api/miscellaneous/dmc/${dmcId}`);
        const items = await response.json();
        miscellaneousItemsList = items;
        
        console.log('Loaded miscellaneous items:', items);
        
        // Update the Miscellaneous section with dynamic items
        updateMiscellaneousSection();
    } catch (error) {
        console.error('Error loading miscellaneous items:', error);
    }
}

// Update Miscellaneous section with dynamic items
function updateMiscellaneousSection() {
    const container = document.getElementById('miscellaneousItemsContainer');
    if (!container || miscellaneousItemsList.length === 0) return;
    
    // Clear existing static items
    container.innerHTML = '';
    
    // Add dynamic items
    miscellaneousItemsList.forEach((item, index) => {
        const itemHtml = `
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" 
                       id="misc_${item.mis_id}" 
                       value="${item.mis_id}"
                       data-item-name="${item.item_name}"
                       data-adult-price="${item.adult_price}"
                       data-child-price="${item.child_price}"
                       data-infant-price="${item.infant_price}">
                <label class="form-check-label" for="misc_${item.mis_id}">
                    ${item.item_name}
                    <small class="text-muted">
                        (Adult: $${item.adult_price}, Child: $${item.child_price}, Infant: $${item.infant_price})
                    </small>
                </label>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', itemHtml);
    });
}

// Load items when DMC is selected or page loads
$(document).ready(function() {
    // Replace with your actual DMC ID source
    const dmcId = {{ auth()->user()->dmc_id ?? 4 }}; // Adjust based on your auth structure
    loadMiscellaneousItems(dmcId);
});
```

---

### Step 5: Test the System

1. **As Admin**:
   - Go to: Product Configuration → Miscellaneous Items
   - Add items like: "Airport Transfer", "Travel Insurance", "SIM Card", etc.

2. **As DMC**:
   - Go to: Product Configuration → Miscellaneous Pricing
   - Set prices for each item

3. **In Enquiry Pro Form**:
   - Items should load dynamically
   - Prices should reflect DMC's settings

---

## Folder Structure

```
resources/views/
├── admin/
│   └── miscellaneous/
│       ├── index.blade.php
│       ├── create.blade.php
│       └── edit.blade.php
├── dmc/
│   └── miscellaneous/
│       └── index.blade.php
└── enquiryform_pro/
    └── create.blade.php (update)
```

---

## Quick Commands

```bash
# Run migration
php artisan migrate

# Create view directories
mkdir resources/views/admin/miscellaneous
mkdir resources/views/dmc/miscellaneous

# Clear cache
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

---

## Done! 🎉

After completing these steps, you'll have a fully functional dynamic Miscellaneous Items system!
