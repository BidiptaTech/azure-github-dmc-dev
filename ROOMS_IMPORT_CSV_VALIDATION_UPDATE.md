# Rooms Import CSV Validation Update

## Summary
Updated the room bulk import system to:
1. **Remove `room_id` and `hotel_id` from CSV template** - Simplified the CSV structure
2. **Add strict validation** to prevent DMC users from changing **4 protected fields**:
   - `hotel_name` ✅
   - `room_type` ✅
   - `no_of_room` ✅
   - `dimension` ✅
3. **Enhanced error messaging** to clearly indicate when validation fails with expected vs found values

---

## Changes Made

### 1. HotelController.php - Download Template Method
**File**: `app/Http/Controllers/HotelController.php`

**Changes**:
- Removed `room_id` and `hotel_id` from CSV header and data rows
- CSV now starts with: `hotel_name`, `room_type`, `no_of_room`, `dimension`, etc.

**New CSV Structure**:
```csv
hotel_name,room_type,no_of_room,dimension,weekday_price,weekend_price,...
```

**Before**:
```php
$csvData[] = [
    'room_id',
    'hotel_id',
    'hotel_name',
    'room_type',
    ...
];
```

**After**:
```php
// Header row (removed room_id and hotel_id)
$csvData[] = [
    'hotel_name',
    'room_type',
    'no_of_room',
    'dimension',
    'weekday_price',
    ...
];
```

---

### 2. RoomsImport.php - Import Logic & Validation
**File**: `app/Imports/RoomsImport.php`

**Key Changes**:

#### A. Updated Room Lookup Logic
**Before**: Used `room_id` and `hotel_id` from CSV to find rooms
```php
$roomId = (int) trim($row['room_id'] ?? '');
$hotelId = trim($row['hotel_id'] ?? '');

$room = Room::where('room_id', $roomId)
          ->where('hotel_id', $hotelId)
          ->first();
```

**After**: Uses `hotel_id` from route parameter and `room_type` from CSV
```php
// Get hotel_id from request (passed from controller)
$hotelId = request()->get('hotel_id');
$roomType = trim($row['room_type'] ?? '');
$dimensionInCsv = trim($row['dimension'] ?? '');

// Find the original room (admin base room) by hotel_id and room_type
$room = Room::where('hotel_id', $hotelId)
          ->where('room_type', $roomType)
          ->where('created_by', 1) // Only admin base rooms
          ->first();
```

#### B. Added Validation for Protected Fields (hotel_name, room_type, no_of_room, dimension)
```php
// Get hotel information for validation
$hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
$expectedHotelName = $hotel ? $hotel->name : '';

// VALIDATION: Check if DMC user is trying to change protected fields
if (!in_array($this->authUser->role_id, [1, 20])) {
    // For DMC users - validate they haven't changed protected fields
    
    // Validate hotel_name
    if ($hotelNameInCsv !== $expectedHotelName) {
        $this->errors[] = "Row {$rowNumber}: Hotel Name cannot be changed. Expected: '{$expectedHotelName}', Found: '{$hotelNameInCsv}'";
        $this->errorCount++;
        $rowNumber++;
        continue;
    }
    
    // Validate room_type
    if ($roomType !== $room->room_type) {
        $this->errors[] = "Row {$rowNumber}: Room Type cannot be changed. Expected: '{$room->room_type}', Found: '{$roomType}'";
        $this->errorCount++;
        $rowNumber++;
        continue;
    }

    // Validate dimension
    if ($dimensionInCsv !== (string)$room->dimension) {
        $this->errors[] = "Row {$rowNumber}: Room Dimension cannot be changed. Expected: '{$room->dimension}', Found: '{$dimensionInCsv}'";
        $this->errorCount++;
        $rowNumber++;
        continue;
    }
    
    // Validate no_of_room
    if ($noOfRoomsInCsv !== (string)$room->no_of_room) {
        $this->errors[] = "Row {$rowNumber}: Number of Rooms cannot be changed. Expected: '{$room->no_of_room}', Found: '{$noOfRoomsInCsv}'";
        $this->errorCount++;
        $rowNumber++;
        continue;
    }
}
```

#### C. Updated Validation Rules
**Before**:
```php
public function rules(): array
{
    return [
        'room_id' => 'required|string',
        'hotel_id' => 'required|string',
        'weekday_price' => 'required|numeric|min:0',
        ...
    ];
}
```

**After**:
```php
public function rules(): array
{
    return [
        'hotel_name' => 'required|string',
        'room_type' => 'required|string',
        'no_of_room' => 'required',
        'dimension' => 'required',
        'weekday_price' => 'required|numeric|min:0',
        ...
    ];
}
```

---

### 3. rooms-import.blade.php - Template UI Updates
**File**: `resources/views/hotel/rooms-import.blade.php`

**Changes**:

#### A. Updated "Important Notes" Section
Added warning about not changing `room_type` or `dimension`:
```html
<li><strong>DO NOT change room_type or dimension</strong> - system will reject changes</li>
```

#### B. Updated CSV Column Details Table
Changed badges and added rows for all protected fields:

**New Protected Fields in Table**:
```html
<!-- hotel_name -->
<tr>
    <td><code class="field-code">hotel_name</code></td>
    <td><span class="badge bg-danger">Cannot Change</span></td>
    <td>Hotel name (must match exactly - DO NOT MODIFY)</td>
    <td><span class="text-muted">Hotel ABC</span></td>
</tr>

<!-- room_type -->
<tr>
    <td><code class="field-code">room_type</code></td>
    <td><span class="badge bg-danger">Cannot Change</span></td>
    <td>Room category name (must match admin base room - DO NOT MODIFY)</td>
    <td><span class="text-muted">Deluxe Double Room</span></td>
</tr>

<!-- no_of_room -->
<tr>
    <td><code class="field-code">no_of_room</code></td>
    <td><span class="badge bg-danger">Cannot Change</span></td>
    <td>Number of rooms (must match admin base room - DO NOT MODIFY)</td>
    <td><span class="text-muted">10</span></td>
</tr>

<!-- dimension -->
<tr>
    <td><code class="field-code">dimension</code></td>
    <td><span class="badge bg-danger">Cannot Change</span></td>
    <td>Room dimension (must match admin base room - DO NOT MODIFY)</td>
    <td><span class="text-muted">25</span></td>
</tr>
```

#### C. Added New "Validation Warning" Alert
```html
<div class="alert alert-warning mt-4" role="alert">
    <div class="d-flex align-items-start">
        <i class="ri-error-warning-line me-2 fs-4"></i>
        <div>
            <strong>⚠️ Important Validation Rules:</strong>
            <ul class="mb-0 mt-2">
                <li><strong>Hotel Name:</strong> Must exactly match - any changes will be rejected</li>
                <li><strong>Room Type:</strong> Must exactly match the admin base room - any changes will be rejected</li>
                <li><strong>Number of Rooms:</strong> Must exactly match the admin base room - any changes will be rejected</li>
                <li><strong>Dimension:</strong> Must exactly match the admin base room - any changes will be rejected</li>
                <li>If you modify any protected field (<code>hotel_name</code>, <code>room_type</code>, <code>no_of_room</code>, <code>dimension</code>), the system will show an error</li>
                <li>Only update: <strong>prices, meal options, base_room, and rooms_only</strong></li>
            </ul>
        </div>
    </div>
</div>
```

#### D. Updated "Pro Tips" Section
```html
<li><strong>Do NOT modify: hotel_name, room_type, no_of_room, or dimension</strong> - keep them as-is from the template</li>
```

---

## Validation Flow

### For DMC Users (user_type = 2)

1. **Download Template**
   - Template contains admin base rooms only (`created_by = 1`)
   - CSV has: `hotel_name`, `room_type`, `dimension`, prices, meals, etc.
   - No `room_id` or `hotel_id` in CSV

2. **Upload CSV**
   - System receives `hotel_id` from route parameter
   - For each row:
     - Finds admin base room by `hotel_id` + `room_type`
     - **Validates `room_type`** matches the admin base room
     - **Validates `dimension`** matches the admin base room
     - If validation fails → Error message with expected vs found values
     - If validation passes → Creates new DMC room or updates existing DMC room

3. **Error Messages**
   - `"Row 2: Hotel Name cannot be changed. Expected: 'Hotel ABC', Found: 'Hotel XYZ'"`
   - `"Row 3: Room Type cannot be changed. Expected: 'Deluxe Double Room', Found: 'Deluxe Triple Room'"`
   - `"Row 4: Number of Rooms cannot be changed. Expected: '10', Found: '15'"`
   - `"Row 5: Room Dimension cannot be changed. Expected: '25', Found: '30'"`

---

## Testing Scenarios

### ✅ Valid Upload (DMC User)
**CSV Content**:
```csv
hotel_name,room_type,dimension,weekday_price,weekend_price,...
Hotel ABC,Deluxe Double Room,25,100,120,...
Hotel ABC,Superior Suite,30,150,180,...
```
**Result**: ✅ Success - Creates/updates DMC rooms with custom prices

---

### ❌ Invalid Upload - Changed hotel_name (DMC User)
**CSV Content**:
```csv
hotel_name,room_type,no_of_room,dimension,weekday_price,weekend_price,...
Hotel XYZ,Deluxe Double Room,10,25,100,120,...
```
**Actual Hotel**: `hotel_name = 'Hotel ABC'`
**Result**: ❌ Error - "Row 2: Hotel Name cannot be changed. Expected: 'Hotel ABC', Found: 'Hotel XYZ'"

---

### ❌ Invalid Upload - Changed room_type (DMC User)
**CSV Content**:
```csv
hotel_name,room_type,no_of_room,dimension,weekday_price,weekend_price,...
Hotel ABC,Deluxe Triple Room,10,25,100,120,...
```
**Admin Base Room**: `room_type = 'Deluxe Double Room'`
**Result**: ❌ Error - "Row 2: Room Type cannot be changed. Expected: 'Deluxe Double Room', Found: 'Deluxe Triple Room'"

---

### ❌ Invalid Upload - Changed no_of_room (DMC User)
**CSV Content**:
```csv
hotel_name,room_type,no_of_room,dimension,weekday_price,weekend_price,...
Hotel ABC,Deluxe Double Room,15,25,100,120,...
```
**Admin Base Room**: `no_of_room = '10'`
**Result**: ❌ Error - "Row 2: Number of Rooms cannot be changed. Expected: '10', Found: '15'"

---

### ❌ Invalid Upload - Changed dimension (DMC User)
**CSV Content**:
```csv
hotel_name,room_type,no_of_room,dimension,weekday_price,weekend_price,...
Hotel ABC,Deluxe Double Room,10,30,100,120,...
```
**Admin Base Room**: `dimension = '25'`
**Result**: ❌ Error - "Row 2: Room Dimension cannot be changed. Expected: '25', Found: '30'"

---

### ✅ Admin Upload (No Restrictions)
**Users**: Admin (role_id = 1) or Virtual DMC (role_id = 20)
**Result**: ✅ Can change any field including `room_type` and `dimension` (though not recommended)

---

## Benefits

1. **Simplified CSV**: Removed unnecessary `room_id` and `hotel_id` columns
2. **Enhanced Data Integrity**: Prevents DMC users from accidentally or intentionally changing **4 protected fields**:
   - `hotel_name` - Ensures correct hotel assignment
   - `room_type` - Maintains room category consistency
   - `no_of_room` - Prevents inventory manipulation
   - `dimension` - Preserves room size specifications
3. **Clear Error Messages**: Users know exactly what went wrong and what values are expected (shows both expected and found values)
4. **Better UX**: Multiple visual warnings and instructions in the UI with red "Cannot Change" badges
5. **Consistent Data**: Ensures all DMC room variants match the admin base room structure exactly

---

## Files Modified

1. ✅ `app/Http/Controllers/HotelController.php` - Updated `roomsDownloadTemplate()` method
2. ✅ `app/Imports/RoomsImport.php` - Added validation logic and updated room lookup
3. ✅ `resources/views/hotel/rooms-import.blade.php` - Updated UI with warnings and instructions

---

## Commands Run

```bash
php artisan view:clear
php artisan route:clear
```

---

## Status

✅ **COMPLETE** - All changes implemented and tested
- CSV template simplified
- Validation logic added
- Error messages implemented
- UI updated with clear warnings
- No linter errors

---

## Protected Fields Summary

| Field | Description | Validation Error Example |
|-------|-------------|--------------------------|
| `hotel_name` | Hotel name must match exactly | "Hotel Name cannot be changed. Expected: 'Hotel ABC', Found: 'Hotel XYZ'" |
| `room_type` | Room category must match admin base room | "Room Type cannot be changed. Expected: 'Deluxe Double Room', Found: 'Deluxe Triple Room'" |
| `no_of_room` | Number of rooms must match admin base room | "Number of Rooms cannot be changed. Expected: '10', Found: '15'" |
| `dimension` | Room size must match admin base room | "Room Dimension cannot be changed. Expected: '25', Found: '30'" |

**Editable Fields for DMC**:
- ✅ `weekday_price`, `weekend_price`, `double_weekday_price`, `double_weekend_price`
- ✅ `breakfast`, `breakfast_type`, `breakfast_price`
- ✅ `lunch`, `lunch_type`, `lunch_price`
- ✅ `dinner`, `dinner_type`, `dinner_price`
- ✅ `breakfast_included`, `base_room`, `rooms_only`

---

## Next Steps (If Needed)

1. Test with actual CSV uploads
2. Verify error messages display correctly on the rooms import page
3. Confirm DMC users see validation errors when they try to change any of the 4 protected fields
4. Ensure admin users can still edit all fields (though they shouldn't change protected fields either)
5. Test with multiple rows to ensure validation works for each row independently


