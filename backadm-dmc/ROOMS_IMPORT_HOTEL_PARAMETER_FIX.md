# Room Import - Hotel ID Parameter Fix

## 🎯 **Goal**

Pass `hotel_id` directly in the route when clicking "Import Rooms", eliminating the need for a hotel selector dropdown on the import template.

---

## ✅ **What Changed**

### **Before (❌):**
1. User clicks "Import Rooms" → Goes to import page
2. User selects hotel from dropdown
3. User downloads template for selected hotel
4. User uploads CSV

### **After (✅):**
1. User clicks "Import Rooms" from specific hotel page
2. System automatically knows which hotel
3. Template shows hotel name (no dropdown needed)
4. User downloads template for that hotel directly
5. User uploads CSV

---

## 📝 **Files Modified**

### **1. routes/web.php**

**Before:**
```php
Route::get('/rooms/import', [HotelController::class, 'roomsImportView'])
    ->name('rooms.import');
```

**After:**
```php
Route::get('/rooms/import/{hotel_id}', [HotelController::class, 'roomsImportView'])
    ->name('rooms.import');
```

**Change:** Added `{hotel_id}` parameter to route

---

### **2. resources/views/hotel/create-room.blade.php**

**Before:**
```blade
<a href="{{ route('rooms.import') }}" class="btn btn-success btn-sm">
    <i class="fas fa-file-upload"></i> Import Rooms
</a>
```

**After:**
```blade
<a href="{{ route('rooms.import', ['hotel_id' => $hotel->hotel_unique_id]) }}" 
   class="btn btn-success btn-sm">
    <i class="fas fa-file-upload"></i> Import Rooms
</a>
```

**Change:** Pass `hotel_id` parameter in route

---

### **3. app/Http/Controllers/HotelController.php**

**Before:**
```php
public function roomsImportView()
{
    $user = Auth::user();
    
    if ($user->user_type != 2) {
        abort(403, 'You do not have permission to import rooms.');
    }

    // Get hotels accessible to this DMC
    $hotels = Hotel::whereJsonContains('dmc_id', $user->userId)
        ->orderBy('name')
        ->get();

    $uploadHistory = \App\Models\UploadHistory::where('upload_type', 'rooms')
        ->where('uploaded_by', $user->userId)
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

    return view('hotel.rooms-import', compact('uploadHistory', 'hotels'));
}
```

**After:**
```php
public function roomsImportView($hotel_id)
{
    $user = Auth::user();
    
    if ($user->user_type != 2) {
        abort(403, 'You do not have permission to import rooms.');
    }

    // Get the specific hotel
    $hotel = Hotel::where('hotel_unique_id', $hotel_id)->first();

    if (!$hotel) {
        return redirect()->back()->with('error', 'Hotel not found.');
    }

    // Check if user has access to this hotel
    $dmcIds = is_string($hotel->dmc_id) ? json_decode($hotel->dmc_id, true) : $hotel->dmc_id;
    if (!is_array($dmcIds)) {
        $dmcIds = [];
    }

    if (!in_array($user->userId, $dmcIds)) {
        return redirect()->back()->with('error', 'You do not have access to this hotel.');
    }

    // Get recent upload history
    $uploadHistory = \App\Models\UploadHistory::where('upload_type', 'rooms')
        ->where('uploaded_by', $user->userId)
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

    return view('hotel.rooms-import', compact('uploadHistory', 'hotel'));
}
```

**Changes:**
- Accept `$hotel_id` parameter
- Fetch specific hotel using `hotel_id`
- Validate user has access to the hotel
- Pass `$hotel` (singular) instead of `$hotels` (collection)

---

### **4. resources/views/hotel/rooms-import.blade.php**

#### **A. Removed Hotel Selector Dropdown**

**Before:**
```blade
<div class="mb-4">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h6 class="mb-3">
                <i class="ri-building-line me-2"></i>Select Hotel
            </h6>
            <div class="row">
                <div class="col-md-8">
                    <select class="form-select" id="hotelSelect" required>
                        <option value="">-- Select a Hotel --</option>
                        @foreach($hotels as $hotel)
                            <option value="{{ $hotel->hotel_unique_id }}">
                                {{ $hotel->name }} ({{ $hotel->city ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-primary w-100" 
                            id="downloadTemplateBtn" disabled>
                        <i class="ri-download-cloud-2-line me-2"></i>Download Template
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
```

**After:**
```blade
<div class="mb-4">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h6 class="mb-3">
                <i class="ri-building-line me-2"></i>Hotel Information
            </h6>
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="icon-wrapper me-3" style="...gradient...">
                            <i class="ri-hotel-line text-white fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">{{ $hotel->name }}</h5>
                            <p class="text-muted mb-0">
                                <i class="ri-map-pin-line me-1"></i>{{ $hotel->city ?? 'N/A' }}
                                @if($hotel->country), {{ $hotel->country }}@endif
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('rooms.import.template', ['hotel_id' => $hotel->hotel_unique_id]) }}" 
                       class="btn btn-primary w-100">
                        <i class="ri-download-cloud-2-line me-2"></i>Download Template
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
```

**Changes:**
- Removed dropdown selector
- Show hotel name with icon
- Direct download link (no JavaScript needed)

#### **B. Updated Instructions**

**Before:**
```blade
<li>Select a hotel from the dropdown above</li>
<li>Download template with admin base rooms only</li>
```

**After:**
```blade
<li>Download template with admin base rooms for <strong>{{ $hotel->name }}</strong></li>
```

#### **C. Updated Back Button**

**Before:**
```blade
<a href="javascript:history.back()" class="btn btn-outline-light btn-sm">
    <i class="ri-arrow-left-line me-1"></i>Back to Rooms
</a>
```

**After:**
```blade
<a href="{{ route('hotels.createroom', ['id' => $hotel->hotel_unique_id]) }}" 
   class="btn btn-outline-light btn-sm">
    <i class="ri-arrow-left-line me-1"></i>Back to Rooms
</a>
```

#### **D. Added Hidden Input in Upload Form**

**Before:**
```blade
<form action="{{ route('rooms.import.upload') }}" method="POST" ...>
    @csrf
    <div class="file-upload-wrapper">
```

**After:**
```blade
<form action="{{ route('rooms.import.upload') }}" method="POST" ...>
    @csrf
    <input type="hidden" name="hotel_id" value="{{ $hotel->hotel_unique_id }}">
    <div class="file-upload-wrapper">
```

#### **E. Simplified JavaScript**

**Before:**
```javascript
const hotelSelect = document.getElementById('hotelSelect');
const downloadTemplateBtn = document.getElementById('downloadTemplateBtn');

// Handle hotel selection
hotelSelect.addEventListener('change', function() {
    if (this.value) {
        downloadTemplateBtn.disabled = false;
    } else {
        downloadTemplateBtn.disabled = true;
    }
});

// Handle download template button click
downloadTemplateBtn.addEventListener('click', function() {
    const selectedHotel = hotelSelect.value;
    if (selectedHotel) {
        window.location.href = '{{ route("rooms.import.template") }}?hotel_id=' + selectedHotel;
    }
});
```

**After:**
```javascript
// Removed all hotel selection logic
// Download button is now a direct <a> link
```

---

## 🔄 **User Flow**

### **Step-by-Step:**

1. **User Views Hotel Rooms**
   ```
   URL: /hotels/H123456/rooms
   Page: create-room.blade.php
   ```

2. **User Clicks "Import Rooms"**
   ```
   Button: <a href="{{ route('rooms.import', ['hotel_id' => 'H123456']) }}">
   Navigates to: /rooms/import/H123456
   ```

3. **Import Page Loads**
   ```
   Controller: roomsImportView('H123456')
   - Fetches Hotel with hotel_unique_id = H123456
   - Validates DMC has access
   - Loads upload history
   - Shows hotel information (no selector)
   ```

4. **User Downloads Template**
   ```
   Button: <a href="{{ route('rooms.import.template', ['hotel_id' => 'H123456']) }}">
   Downloads: CSV with admin base rooms for Hotel H123456
   ```

5. **User Uploads CSV**
   ```
   Form submits with:
   - file: uploaded CSV
   - hotel_id: H123456 (hidden input)
   
   Controller processes import for Hotel H123456
   ```

6. **User Returns to Rooms**
   ```
   Button: <a href="{{ route('hotels.createroom', ['id' => 'H123456']) }}">
   Returns to: /hotels/H123456/rooms
   ```

---

## ✅ **Benefits**

### **1. Better UX**
- ✅ No need to select hotel (already known from context)
- ✅ Fewer clicks to download template
- ✅ Clear which hotel you're importing for

### **2. Simpler Code**
- ✅ Removed hotel selector dropdown
- ✅ Removed JavaScript for hotel selection
- ✅ Direct download link (no JavaScript needed)

### **3. Better Security**
- ✅ Validates user has access to specific hotel
- ✅ Can't accidentally import to wrong hotel
- ✅ Clear audit trail (hotel_id in route)

### **4. Consistent Navigation**
- ✅ Back button returns to exact hotel page
- ✅ Breadcrumb trail is clear
- ✅ URL shows which hotel: `/rooms/import/H123456`

---

## 📊 **Example Scenario**

### **Hotel: Grand Plaza (H123456)**

**1. View Rooms:**
```
URL: /hotels/H123456/rooms
Shows: Room listing for Grand Plaza
```

**2. Click "Import Rooms":**
```
URL: /rooms/import/H123456
Shows: Import page for "Grand Plaza"
       - Hotel name displayed prominently
       - Download template button ready
       - No dropdown needed
```

**3. Download Template:**
```
Click: Download Template button
Downloads: grand_plaza_rooms_template.csv
Contains: Admin base rooms for Grand Plaza only
```

**4. Upload CSV:**
```
Upload: grand_plaza_rooms_updated.csv
System: Creates/updates rooms for Grand Plaza (H123456)
Result: New DMC rooms created with custom prices
```

**5. Return:**
```
Click: Back to Rooms
Returns: /hotels/H123456/rooms
Shows: Room listing for Grand Plaza (including new DMC rooms)
```

---

## 🎉 **Result**

Now the room import process is:
- ✅ **Contextual** - Knows which hotel from the route
- ✅ **Simpler** - No hotel selection needed
- ✅ **Faster** - Fewer clicks to download template
- ✅ **Clearer** - Hotel name shown prominently
- ✅ **Safer** - Can't accidentally import to wrong hotel

**Perfect user experience!** 🚀

