# Guide Popup Implementation - Enquiry Form Pro

## Overview
Implemented a compact, table-based guide selection popup similar to the attractions modal, with dynamic filtering by DMC ID and destination.

## Features Implemented

### 1. **Compact Design**
- Table-based layout showing all guides at once
- Similar to attractions modal for consistency
- Responsive and scrollable for large datasets

### 2. **Filter Fields**
- **Date**: Service date for the guide
- **Destination**: Dropdown to select city/location
- Auto-populates from header values

### 3. **Guide Selection Table**
Displays guides with the following columns:
- ✅ **Checkbox**: Multi-select guides
- **Guide Name**: Name of the guide
- **Language**: Dropdown populated from guide's available languages with proficiency levels
- **Hours**: Dropdown with options (2, 4, 6, 8, 10, 12) - **Default: 12 hours**
- **Day Rate (Cost)**: Auto-populated based on selected hours (readonly)
- **Sell Price**: Editable, defaults to day rate

### 4. **Dynamic Pricing**
The system automatically updates the cost based on hours selected:
- 2 Hours → `two_hour_price`
- 4 Hours → `four_hour_price`
- 6 Hours → `six_hour_price`
- 8 Hours → `eight_hour_price`
- 10 Hours → `ten_hour_price`
- 12 Hours → `twelve_hour_price` or `day_rate` (default)

### 5. **Three-Way Filtering**
Guides are filtered by:
- ✅ **DMC ID**: Only guides assigned to the user's DMC
- ✅ **Status**: Only active guides (`status = 1`)
- ✅ **City/Destination**: Only guides in the selected location

## Implementation Details

### Backend Changes

#### 1. EnquiryFormPro Controller (`app/Http/Controllers/EnquiryFormPro.php`)

Added new AJAX method `getGuidesByDestination()`:

```php
public function getGuidesByDestination(Request $request)
{
    $user = auth()->user();
    $destination = $request->input('destination');
    
    // Get DMC ID based on user role (same logic as attractions)
    $dmc_id = null;
    // ... DMC ID determination logic ...
    
    // Get guides for this DMC and destination
    $guidesQuery = \App\Models\Guide::where('status', 1)
        ->where('city', $destination);
    
    if ($dmc_id) {
        $guidesQuery->where('dmc_id', $dmc_id);
    }
    
    $guides = $guidesQuery
        ->with('languages')
        ->select('guide_id', 'name', 'city', 'country', 'day_rate', 
                 'hourly_price', 'two_hour_price', 'four_hour_price', 
                 'six_hour_price', 'eight_hour_price', 'ten_hour_price', 'twelve_hour_price')
        ->orderBy('name')
        ->get();
    
    // Format guides with languages
    $guidesData = $guides->map(function($guide) {
        return [
            'guide_id' => $guide->guide_id,
            'name' => $guide->name,
            'day_rate' => $guide->day_rate ?? $guide->twelve_hour_price ?? 0,
            // ... all hour prices ...
            'languages' => $guide->languages->map(function($lang) {
                return [
                    'language' => $lang->language,
                    'proficiency' => $lang->proficiency
                ];
            })
        ];
    });
    
    return response()->json([
        'success' => true,
        'guides' => $guidesData,
        'count' => $guides->count()
    ]);
}
```

**Filters Applied:**
- Line: `where('status', 1)` - Only active guides
- Line: `where('city', $destination)` - Only guides in selected destination
- Line: `where('dmc_id', $dmc_id)` - Only guides for this DMC

#### 2. Guide Model (`app/Models/Guide.php`)

Added primary key definition:

```php
protected $primaryKey = 'guide_id';
```

This ensures `$guide->id` works correctly in Blade templates.

#### 3. Routes (`routes/web.php`)

Added new route:

```php
Route::get('/enquiry-form-pro/get-guides', [EnquiryFormPro::class, 'getGuidesByDestination'])
    ->name('enquiry-form-pro.get-guides');
```

### Frontend Changes

#### 1. Modal HTML (`resources/views/enquiryform_pro/create.blade.php`)

Replaced the old simple guide modal with a compact table-based design:

```html
<div class="modal fade" id="guideModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <!-- Filter Section -->
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label>Date:</label>
                        <input type="date" id="guideDate">
                    </div>
                    <div class="col-md-4">
                        <label>Destination:</label>
                        <select id="guideDestination" onchange="loadGuidesByDestination()">
                            <!-- Destinations populated from $destinations -->
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Guides Table -->
            <table class="table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllGuides"></th>
                        <th>Guide Name</th>
                        <th>Language</th>
                        <th>Hours</th>
                        <th>Day Rate (Cost)</th>
                        <th>Sell Price</th>
                    </tr>
                </thead>
                <tbody id="guidesTableBody">
                    <!-- Populated via AJAX -->
                </tbody>
            </table>
            
            <!-- Footer Buttons -->
            <div class="modal-footer">
                <button onclick="addAnotherGuide()">Add Another</button>
                <button onclick="saveAndCloseGuides()">Save & Close</button>
            </div>
        </div>
    </div>
</div>
```

#### 2. JavaScript Functions

**`openGuideModal()`**
- Resets the modal
- Auto-fills destination from header
- Loads guides for default destination

**`loadGuidesByDestination()`**
- Makes AJAX call to `/enquiry-form-pro/get-guides`
- Populates table with guides
- Creates language dropdown for each guide from their available languages
- Creates hours dropdown (2, 4, 6, 8, 10, 12) with default 12
- Sets day_rate in cost field based on 12-hour price

**`updateGuidePricing(guideId, prices...)`**
- Updates cost and sell price when hours are changed
- Maps hours to corresponding price field

**`saveAndCloseGuides()`**
- Validates language selection
- Creates guide entries in `guideList`
- Updates header dates
- Closes modal

**`addAnotherGuide()`**
- Same as save but keeps modal open
- Allows adding multiple guides in batches

## Data Flow

### 1. Opening Modal
```
User clicks "+ Add" 
→ openGuideModal()
→ Auto-fill destination from header
→ loadGuidesByDestination()
→ AJAX call to backend
→ Populate table with guides
```

### 2. Selecting Guides
```
User selects destination
→ loadGuidesByDestination()
→ Table shows guides for that destination
User selects guide(s)
→ Choose language from dropdown
→ Choose hours (default 12)
→ Cost auto-updates based on hours
→ User can edit sell price
```

### 3. Saving Guides
```
User clicks "Save & Close"
→ saveAndCloseGuides()
→ Validate language selection
→ Create guide data objects
→ Add to guideList array
→ Update guide table
→ Update header dates
→ Close modal
```

## Database Schema

### Guides Table
```sql
- guide_id (PK)
- name
- city
- country
- dmc_id
- status (1 = active)
- day_rate
- hourly_price
- two_hour_price
- four_hour_price
- six_hour_price
- eight_hour_price
- ten_hour_price
- twelve_hour_price
```

### Guide Languages Table
```sql
- id (PK)
- guide_id (FK)
- language
- proficiency
```

## Usage Example

### Scenario: Adding a guide for Singapore
1. User opens Enquiry Form Pro
2. Clicks "+ Add" in Tour Guide section
3. Modal opens with:
   - Date: Auto-filled with tour start date
   - Destination: Auto-filled with "Singapore"
4. Table shows all guides where:
   - `dmc_id` = user's DMC
   - `status` = 1
   - `city` = 'Singapore'
5. User selects "John Doe" guide
6. Selects language "English (Fluent)" from dropdown
7. Selects hours "12 Hours" (default)
8. Cost shows: SGD 150.00 (from `twelve_hour_price`)
9. Sell price: SGD 150.00 (editable)
10. Clicks "Save & Close"
11. Guide appears in Tour Guide table

## Benefits

1. **Consistent UX**: Same design pattern as attractions modal
2. **Dynamic Filtering**: Only shows relevant guides (DMC + Status + Destination)
3. **Smart Pricing**: Auto-updates based on hours selected
4. **Language Support**: Shows only languages the guide actually speaks
5. **Batch Selection**: Can add multiple guides at once
6. **Flexible Pricing**: Day rate auto-populates but sell price is editable

## Testing Checklist

- [ ] Open guide modal - should show date and destination filters
- [ ] Select destination - should load guides for that location
- [ ] Verify only guides for user's DMC are shown
- [ ] Select guide - language dropdown should show guide's languages
- [ ] Change hours - cost should update automatically
- [ ] Select 12 hours - should show `twelve_hour_price` or `day_rate`
- [ ] Edit sell price - should accept custom value
- [ ] Click "Add Another" - should add guide and keep modal open
- [ ] Click "Save & Close" - should add guide and close modal
- [ ] Verify guide appears in Tour Guide table
- [ ] Test with multiple destinations
- [ ] Test with guides having multiple languages

## Related Files

- `app/Http/Controllers/EnquiryFormPro.php` - Controller with AJAX method
- `app/Models/Guide.php` - Guide model with primary key
- `routes/web.php` - Route definition
- `resources/views/enquiryform_pro/create.blade.php` - View with modal and JavaScript

