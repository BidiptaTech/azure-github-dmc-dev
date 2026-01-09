# Quick Test Guide - Hotels, Attractions & Restaurants

## 🚀 Quick Start

### 1. Open the Page
Navigate to: **Enquiry Form Pro** page

### 2. Open Browser Console
Press **F12** → Go to **Console** tab

### 3. Select a Destination
Choose **Singapore** (or any destination) in:
- Hotel section
- Attraction section  
- Restaurant section

### 4. Watch the Console

You should see output like this:

```
✅ Loading hotels for destination: Singapore
✅ Hotels API response data: {success: true, hotels: Array(5), dmc_id: 4, destination: "Singapore"}
✅ Hotels count: 5
✅ DMC ID: 4
✅ Adding hotel: Grand Hotel ID: 1 Rooms: 3
✅ Hotels loaded successfully. Total: 5

✅ Loading attractions for destination: Singapore
✅ Attractions count: 10
✅ DMC ID: 4
✅ Attraction: Universal Studios Adult Price: 79.00 Child Price: 59.00
```

## ✅ What to Check

### Hotels
- [ ] Console shows "Loading hotels for destination"
- [ ] DMC ID = 4 (your DMC)
- [ ] Hotels count > 0
- [ ] Hotels appear in dropdown
- [ ] Can select a hotel
- [ ] Rooms load after selection

### Attractions
- [ ] Console shows "Loading attractions for destination"
- [ ] DMC ID = 4
- [ ] Attractions count > 0
- [ ] Attractions appear in table
- [ ] Adult price NOT 0.00
- [ ] Child price NOT 0.00

### Restaurants
- [ ] Restaurants appear in dropdown
- [ ] Can select a restaurant
- [ ] Meals load for restaurant
- [ ] Meal prices visible

## ❌ If You See Problems

### Problem: "Hotels count: 0"
**Meaning:** No hotels assigned to your DMC for this destination

**Check Database:**
```sql
SELECT id, name, dmc_id, city 
FROM hotels 
WHERE dmc_id::jsonb @> '4' 
  AND city = 'Singapore'
  AND status = 1;
```

**Fix:** Assign hotels to DMC 4 in hotel management

---

### Problem: "Attraction: ... Adult Price: 0.00"
**Meaning:** Attraction prices not set in database

**Check Database:**
```sql
SELECT attraction_id, name, adult_price, child_price 
FROM attractions 
WHERE dmc_id::jsonb @> '4' 
  AND location = 'Singapore';
```

**Fix:** Update attraction prices in attraction management

---

### Problem: "DMC ID: null"
**Meaning:** DMC ID not being determined correctly

**Check Logs:**
```powershell
Get-Content storage/logs/laravel.log -Tail 20
```

Look for:
```
[INFO] EnquiryFormPro create() - DMC ID determined 
{"dmc_id":4,"user_id":8,"role_id":"35","created_by":4}
```

**Fix:** Check user's `created_by` field in database

---

### Problem: Can't select hotel
**Meaning:** Dropdown disabled or no hotels loaded

**Check:**
1. Console for JavaScript errors
2. Network tab → Check API response
3. Verify hotels have rooms

---

## 🔍 Advanced Debugging

### View All DMC Data
Navigate to: `http://localhost/azure_new_files/public/debug/dmc-data`

This shows all hotels, attractions, and restaurants for DMC 4.

### Check Laravel Logs
```powershell
cd c:\xampp\htdocs\azure_new_files
Get-Content storage/logs/laravel.log -Tail 50
```

### Check Network Tab
1. Open DevTools (F12)
2. Go to **Network** tab
3. Select a destination
4. Look for API calls:
   - `/enquiry-form-pro/get-hotels?destination=Singapore`
   - `/enquiry-form-pro/get-attractions?destination=Singapore`
5. Click on the call
6. Check **Response** tab to see data

## 📊 Expected Data Structure

### Hotels API Response:
```json
{
    "success": true,
    "hotels": [
        {
            "id": 1,
            "name": "Grand Hotel",
            "city": "Singapore",
            "rooms": [
                {
                    "room_id": 101,
                    "room_type": "Deluxe",
                    "base_price": 150.00,
                    "bed_types": [...]
                }
            ]
        }
    ],
    "dmc_id": 4,
    "destination": "Singapore"
}
```

### Attractions API Response:
```json
{
    "success": true,
    "attractions": [
        {
            "id": 1,
            "name": "Universal Studios",
            "location": "Singapore",
            "adult_price": "79.00",
            "child_price": "59.00"
        }
    ],
    "count": 10,
    "dmc_id": 4,
    "destination": "Singapore"
}
```

## 🎯 Success Criteria

### ✅ Everything Working:
- Hotels dropdown populates
- Can select hotel
- Rooms appear with prices
- Attractions table populates
- Attraction prices show (not 0)
- Restaurants dropdown populates
- Meals show with prices
- All data belongs to DMC 4

### ❌ Issues to Report:
- Empty dropdowns/tables
- Prices showing as 0.00
- Wrong DMC ID in console
- JavaScript errors in console
- API errors in Network tab

## 📝 What to Report

If you find issues, provide:

1. **Console Output** (copy all)
2. **Network Tab** (screenshot of API response)
3. **Laravel Logs** (last 50 lines)
4. **Database Query Results** (if you ran them)
5. **Specific Issue** (what's not working)

## 🔧 Quick Fixes

### Clear Cache:
```powershell
cd c:\xampp\htdocs\azure_new_files
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Restart Server:
Stop and restart your XAMPP Apache server

### Clear Browser Cache:
- Ctrl + Shift + Delete
- Clear cached images and files
- Reload page (Ctrl + F5)

## 📞 Need Help?

1. Follow this guide step by step
2. Collect the data mentioned in "What to Report"
3. Share your findings

The console logs and Laravel logs will tell us exactly what's happening!

