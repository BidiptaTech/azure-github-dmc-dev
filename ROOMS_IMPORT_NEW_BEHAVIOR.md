# Room Import - New Row Creation Behavior

## 🎯 **Overview**

When DMC users import room prices via CSV, the system now **creates NEW room rows** instead of updating admin base rooms. This ensures admin pricing remains unchanged while allowing DMCs to have their own customized pricing.

---

## 📊 **How It Works**

### **For Admin Users (role_id = 1 or 20)**
- Admin imports update their **own existing rooms**
- `created_by = 1` remains unchanged
- Direct price updates on the same row

### **For DMC Users (user_type = 2)**
- **First Import**: Creates **NEW room rows** with incremented `room_id`
- **Subsequent Imports**: Updates **their own existing rooms**
- Admin base rooms (`created_by = 1`) remain **untouched**

---

## 🔄 **Import Flow**

### **Step 1: Download Template**
```
Admin Base Room:
- room_id: R000001
- hotel_id: H123456
- room_type: Deluxe Room
- created_by: 1
- weekday_price: 100.00
```

### **Step 2: DMC Uploads CSV with Custom Prices**
```csv
room_id,hotel_id,room_type,weekday_price,weekend_price,...
R000001,H123456,Deluxe Room,150.00,180.00,...
```

### **Step 3: System Creates NEW Row**
```
NEW DMC Room:
- room_id: R000015 (auto-incremented)
- hotel_id: H123456
- room_type: Deluxe Room
- created_by: 4 (DMC's userId)
- dmc_id: 4
- weekday_price: 150.00 (from CSV)
- weekend_price: 180.00 (from CSV)
```

### **Result in Database:**
| room_id | hotel_id | room_type | created_by | weekday_price | weekend_price |
|---------|----------|-----------|------------|---------------|---------------|
| R000001 | H123456  | Deluxe Room | 1 | 100.00 | 120.00 |
| R000015 | H123456  | Deluxe Room | 4 | 150.00 | 180.00 |

---

## 🔑 **Key Features**

### **1. Auto-Incremented Room ID**
```php
private function generateNewRoomId()
{
    $lastRoom = Room::withTrashed()->orderBy('created_at', 'desc')->first();
    $lastIdNumeric = (int) filter_var($lastRoom->room_id, FILTER_SANITIZE_NUMBER_INT);
    $nextIdNumeric = $lastIdNumeric + 1;
    
    return 'R' . str_pad($nextIdNumeric, 6, '0', STR_PAD_LEFT);
    // Example: R000015, R000016, R000017
}
```

### **2. Duplicate Detection**
- If DMC already has a room for the same `hotel_id` + `room_type`:
  - **Updates existing DMC room** instead of creating a new one
  - Prevents duplicate room entries

### **3. Ownership Tracking**
```php
$newRoom->created_by = $authUser->userId;  // DMC's User ID
$newRoom->dmc_id = $authUser->userId;      // DMC's User ID
$newRoom->dmc_base_room = 0;                // Not a base room
$newRoom->base_room = 0;                    // DMC rooms are never base rooms
```

---

## 📝 **Import Logic (RoomsImport.php)**

### **Admin Import**
```php
if (in_array($this->authUser->role_id, [1, 20])) {
    // Update admin's own room directly
    $room->weekday_price = $row['weekday_price'];
    $room->save();
}
```

### **DMC Import**
```php
else {
    // Check if DMC already has their own room
    $existingDmcRoom = Room::where('hotel_id', $hotelId)
                          ->where('room_type', $room->room_type)
                          ->where('created_by', $this->authUser->userId)
                          ->first();

    if ($existingDmcRoom) {
        // Update DMC's existing room
        $existingDmcRoom->weekday_price = $row['weekday_price'];
        $existingDmcRoom->save();
    } else {
        // Create NEW room row
        $this->createDmcRoomCopy($room, $row);
    }
}
```

---

## 🎨 **User Interface Updates**

### **Updated Instructions**
1. **Getting Started**
   - ✅ "Upload to **create your own custom rooms** with your prices"
   - ✅ "New rooms will be created with incremented room_id"

2. **Important Notes**
   - ✅ "**NEW rows will be created** - admin rooms stay unchanged"
   - ✅ "Your new rooms get auto-incremented room_id (e.g., R000015, R000016)"

3. **Room Creation Notice (Green Alert)**
   - ✅ "**Create NEW room rows** in the database with incremented room_id"
   - ✅ "Admin base rooms remain unchanged (created_by = 1)"
   - ✅ "Your new rooms have **created_by = Your User ID**"

---

## 🔍 **Room Listing Display**

### **DMC View**
- Shows **only their own rooms** (created_by = DMC's userId)
- Admin base rooms are **hidden** from DMC users
- Action buttons (Edit/Delete) work only on their own rooms

### **Admin View**
- Shows **all rooms** (Admin + all DMC rooms)
- DMC filter dropdown allows filtering by specific DMC
- Can see which DMC owns which room

---

## 📊 **Database Schema**

### **Key Columns**
```sql
rooms table:
- room_id (VARCHAR, PRIMARY KEY) - Auto-incremented (R000001, R000002...)
- hotel_id (VARCHAR)
- room_type (VARCHAR)
- created_by (BIGINT) - 1 for Admin, DMC userId for DMC rooms
- dmc_id (BIGINT) - NULL for Admin, DMC userId for DMC rooms
- dmc_base_room (NUMERIC) - 0 for DMC custom rooms
- base_room (NUMERIC) - 0 for DMC rooms, 1 for admin base rooms
- weekday_price, weekend_price, etc. (NUMERIC)
```

---

## ✅ **Benefits**

1. **Admin Pricing Protection**
   - Admin base prices remain unchanged
   - No accidental overwrites by DMC imports

2. **DMC Flexibility**
   - DMCs can set their own custom prices
   - Independent pricing from admin base rates

3. **Clear Ownership**
   - `created_by` column clearly identifies room owner
   - Easy to filter and display DMC-specific rooms

4. **Update Safety**
   - First import creates new rooms
   - Subsequent imports update existing DMC rooms
   - No duplicate entries

5. **Audit Trail**
   - Clear history of who created/owns each room
   - Easy tracking of room customizations

---

## 🚀 **Example Scenario**

### **Initial State: Admin Creates Base Room**
```
Room R000001:
- hotel_id: H123456
- room_type: Deluxe Room
- created_by: 1 (Admin)
- weekday_price: 100.00
```

### **DMC #1 (userId=4) Imports**
```
✅ Creates NEW Room R000015:
- hotel_id: H123456
- room_type: Deluxe Room
- created_by: 4 (DMC #1)
- weekday_price: 150.00
```

### **DMC #2 (userId=7) Imports**
```
✅ Creates NEW Room R000016:
- hotel_id: H123456
- room_type: Deluxe Room
- created_by: 7 (DMC #2)
- weekday_price: 180.00
```

### **Final Database State**
| room_id | hotel_id | room_type | created_by | weekday_price |
|---------|----------|-----------|------------|---------------|
| R000001 | H123456  | Deluxe Room | 1 (Admin)  | 100.00 |
| R000015 | H123456  | Deluxe Room | 4 (DMC #1) | 150.00 |
| R000016 | H123456  | Deluxe Room | 7 (DMC #2) | 180.00 |

---

## 📱 **Files Modified**

1. **app/Imports/RoomsImport.php**
   - Updated import logic to create new rows
   - Added `createDmcRoomCopy()` method
   - Added `generateNewRoomId()` method

2. **resources/views/hotel/rooms-import.blade.php**
   - Updated instructions and notices
   - Changed "Ownership Transfer" to "Room Creation"
   - Updated Getting Started steps
   - Enhanced Important Notes section

---

## 🎉 **Result**

Now DMCs can:
- ✅ Download admin base room templates
- ✅ Set their own custom prices
- ✅ Create their own room entries
- ✅ Update their rooms anytime
- ✅ See only their own rooms in listings

While Admin:
- ✅ Maintains base pricing integrity
- ✅ Can see all DMC rooms
- ✅ Has full control over base rooms
- ✅ Can filter by specific DMC

**Perfect separation of pricing and ownership!** 🚀

