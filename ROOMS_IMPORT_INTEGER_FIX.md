# Room Import - Integer room_id Fix

## 🐛 **Problem**

```
SQLSTATE[22P02]: Invalid text representation: 7 ERROR: invalid input syntax for type integer: "R000078"
```

The import was failing because:
1. **room_id** column in database is **INTEGER**, not VARCHAR
2. Code was generating string IDs like "R000078"
3. Database couldn't store string in integer column

---

## ✅ **Solution**

### **1. Updated Room ID Generation**

**Before (❌ Wrong):**
```php
// Generated string format: "R000001", "R000002"
$newRoomId = 'R' . str_pad($nextIdNumeric, 6, '0', STR_PAD_LEFT);
```

**After (✅ Correct):**
```php
// Uses CommonHelper - generates integer: 1, 2, 15, 78
$roomId = \App\Helpers\CommonHelper::createId($room_max_id);
return $roomId; // Returns integer, not string
```

### **2. Fixed CSV Room ID Lookup**

**Before (❌ Wrong):**
```php
$roomId = trim($row['room_id'] ?? ''); // String type
```

**After (✅ Correct):**
```php
$roomId = (int) trim($row['room_id'] ?? ''); // Cast to integer
```

### **3. Updated generateNewRoomId() Method**

Now follows the exact same pattern as `HotelController::storeroom()`:

```php
private function generateNewRoomId()
{
    // Get the last room ID (including soft deleted)
    $lastRoom = Room::withTrashed()->orderBy('id', 'desc')->first();
    $room_max_id = $lastRoom->room_id ?? 0;
    
    // Use CommonHelper to create ID (returns an integer)
    $roomId = \App\Helpers\CommonHelper::createId($room_max_id);
    
    // Ensure uniqueness
    while (Room::where('room_id', $roomId)->exists()) {
        $roomId = \App\Helpers\CommonHelper::createId($roomId);
    }
    
    return $roomId; // Integer: 15, 16, 17, 78, etc.
}
```

---

## 📊 **How CommonHelper Works**

Based on `HotelController::storeroom()` pattern:

```php
// Example flow:
$lastRoom = Room::withTrashed()->orderBy('id', 'desc')->first();
$room_max_id = $lastRoom->room_id; // e.g., 77

$roomId = CommonHelper::createId(77); // Returns 78
```

The helper automatically increments the ID and ensures it's an **integer**.

---

## 🔄 **Updated Logic Flow**

### **Admin Import:**
1. Reads CSV with room_id as integer
2. Updates existing admin room
3. No new rows created

### **DMC Import:**

#### **First Upload (Room doesn't exist):**
```
1. Find admin base room (room_id = 1)
2. Check if DMC has this room type → NO
3. Generate new room_id using CommonHelper → 15
4. Create NEW row:
   - room_id: 15 (integer)
   - created_by: 4 (DMC user ID)
   - hotel_id: H123456
   - room_type: Deluxe Room
   - prices: custom DMC prices
```

#### **Second Upload (Room exists):**
```
1. Find admin base room (room_id = 1)
2. Check if DMC has this room type → YES (room_id = 15)
3. Update existing DMC room (room_id = 15)
4. No new row created
```

---

## 📝 **Database Schema**

```sql
rooms table:
- room_id: INTEGER (PRIMARY KEY) ← Auto-incremented by CommonHelper
- hotel_id: VARCHAR
- room_type: VARCHAR
- created_by: BIGINT (1 = Admin, 4 = DMC User ID)
- weekday_price: NUMERIC
- weekend_price: NUMERIC
- etc...
```

---

## 🎨 **UI Updates**

### **CSV Column Details Table:**
```
room_id | Required | Unique room identifier - INTEGER | 1, 2, 15, 78
```

### **Important Notes:**
- Your new rooms get auto-incremented room_id (e.g., 15, 16, 17)

### **Room Creation Notice:**
- First Upload: Creates NEW room rows with auto-incremented room_id (e.g., 15, 16, 17)
- Subsequent Uploads: Updates your existing rooms (no new rows created)

---

## 🔍 **Example Scenario**

### **Database State:**
| room_id | hotel_id | room_type | created_by | weekday_price |
|---------|----------|-----------|------------|---------------|
| 1       | H123456  | Deluxe    | 1 (Admin)  | 100.00       |
| 4       | H123456  | Suite     | 1 (Admin)  | 200.00       |

### **DMC Downloads Template:**
```csv
room_id,hotel_id,room_type,weekday_price,...
1,H123456,Deluxe,150.00,...
4,H123456,Suite,250.00,...
```

### **DMC Uploads CSV (First Time):**

**System Creates 2 NEW Rows:**
| room_id | hotel_id | room_type | created_by | weekday_price |
|---------|----------|-----------|------------|---------------|
| 1       | H123456  | Deluxe    | 1 (Admin)  | 100.00       |
| 4       | H123456  | Suite     | 1 (Admin)  | 200.00       |
| **15**  | H123456  | Deluxe    | 4 (DMC)    | **150.00**   |
| **16**  | H123456  | Suite     | 4 (DMC)    | **250.00**   |

### **DMC Uploads CSV Again (Update Prices):**

**System Updates Existing DMC Rows:**
| room_id | hotel_id | room_type | created_by | weekday_price |
|---------|----------|-----------|------------|---------------|
| 1       | H123456  | Deluxe    | 1 (Admin)  | 100.00       |
| 4       | H123456  | Suite     | 1 (Admin)  | 200.00       |
| 15      | H123456  | Deluxe    | 4 (DMC)    | **175.00** ← Updated |
| 16      | H123456  | Suite     | 4 (DMC)    | **275.00** ← Updated |

**No new rows created on subsequent uploads!**

---

## 📂 **Files Modified**

### **1. app/Imports/RoomsImport.php**
```php
// Line 79: Cast room_id to integer
$roomId = (int) trim($row['room_id'] ?? '');

// Line 355-370: Use CommonHelper for room_id generation
private function generateNewRoomId()
{
    $lastRoom = Room::withTrashed()->orderBy('id', 'desc')->first();
    $room_max_id = $lastRoom->room_id ?? 0;
    $roomId = \App\Helpers\CommonHelper::createId($room_max_id);
    
    while (Room::where('room_id', $roomId)->exists()) {
        $roomId = \App\Helpers\CommonHelper::createId($roomId);
    }
    
    return $roomId; // Integer
}
```

### **2. resources/views/hotel/rooms-import.blade.php**
- Updated room_id examples: "1, 2, 15, 78" (not "R000001")
- Updated instructions to reflect integer IDs
- Clarified first upload vs subsequent upload behavior

---

## ✅ **Result**

Now the import:
- ✅ Uses **integer room_id** (not string)
- ✅ Uses **CommonHelper::createId()** (same as manual room creation)
- ✅ **First upload:** Creates new DMC rooms with auto-incremented IDs
- ✅ **Subsequent uploads:** Updates existing DMC rooms
- ✅ No more "invalid input syntax for type integer" errors!
- ✅ Maintains consistency with `HotelController::storeroom()` logic

**Database schema compliance achieved!** 🎉

