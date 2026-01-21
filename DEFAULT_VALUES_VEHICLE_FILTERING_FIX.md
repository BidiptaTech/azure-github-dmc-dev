# Default Values Vehicle Filtering Fix

## Issue Description
When trying to add a default value for "Car (Private)" or "Car (Shared)" in the Default Values form, no vehicles were showing up in the dropdown even though vehicles with the appropriate service types existed for the DMC.

## Root Cause
The `DefaultValueController` was incorrectly filtering vehicles using the `vehicle_type` field with values `['private', 'both']` and `['shared', 'both']`. 

However, in the Vehicle model:
- `vehicle_type` refers to the **vehicle category** (e.g., sedan, SUV, van, etc.)
- `sharable` is the correct field that determines the **sharing configuration**

### Vehicle Sharable Field Values:
- **1** = Private only
- **2** = Shared only  
- **3** = Both (can be used as either Private or Shared)

## Files Modified

### 1. `app/Http/Controllers/DefaultValueController.php`

#### Changes Made:
- **create() method** (lines 143-159): Updated vehicle filtering for both private and shared vehicles
- **edit() method** (lines 282-298): Updated vehicle filtering for both private and shared vehicles
- **getServices() method** (lines 447-486): Updated AJAX endpoint for fetching vehicles

#### What Changed:
```php
// BEFORE (Incorrect):
->whereIn('vehicle_type', ['private', 'both'])

// AFTER (Correct):
->whereIn('sharable', [1, 3]) // Private or Both
```

#### Additional Improvements:
- Added `sharable` field to the SELECT clause
- Improved display labels to show both vehicle type (sedan, SUV) and sharing option (Private, Shared, Both)
- Added comments explaining the sharable field values

### 2. `resources/views/default-values/create.blade.php`

#### Changes Made:
Updated the vehicle display in dropdown options to show:
- Vehicle name
- Vehicle type (sedan, SUV, etc.)
- Sharing configuration (Private, Shared, or Both)

**Example display:**
```
Toyota Camry (Sedan - Private)
Honda Accord (Sedan - Both)
Mercedes Sprinter (Van - Shared)
```

### 3. `resources/views/default-values/edit.blade.php`

#### Changes Made:
Same improvements as create.blade.php for consistency

## How the Fix Works

### For Private Vehicles:
```php
$privateVehicles = Vehicle::where('dmc_id', $dmcId)
    ->where('is_available', 1)
    ->whereIn('sharable', [1, 3]) // 1=Private, 3=Both
    ->select('vehicle_id', 'vehicle_name', 'vehicle_type', 'sharable')
    ->orderBy('vehicle_name')
    ->get();
```

This will now correctly fetch:
- Vehicles marked as "Private only" (sharable = 1)
- Vehicles marked as "Both" (sharable = 3)

### For Shared Vehicles:
```php
$sharedVehicles = Vehicle::where('dmc_id', $dmcId)
    ->where('is_available', 1)
    ->whereIn('sharable', [2, 3]) // 2=Shared, 3=Both
    ->select('vehicle_id', 'vehicle_name', 'vehicle_type', 'sharable')
    ->orderBy('vehicle_name')
    ->get();
```

This will now correctly fetch:
- Vehicles marked as "Shared only" (sharable = 2)
- Vehicles marked as "Both" (sharable = 3)

## Testing the Fix

1. Navigate to **Default Values → Add New**
2. Select **Service Type: Car (Private)**
3. The "Select Car (Private)" dropdown should now show all vehicles where:
   - `dmc_id` matches your DMC
   - `is_available` = 1
   - `sharable` IN (1, 3)

4. Select **Service Type: Car (Shared)**
5. The "Select Car (Shared)" dropdown should now show all vehicles where:
   - `dmc_id` matches your DMC
   - `is_available` = 1
   - `sharable` IN (2, 3)

## Expected Results

After this fix:
- ✅ Vehicles will appear in the dropdown based on their correct sharing configuration
- ✅ The dropdown will show clear information: vehicle name, type, and sharing option
- ✅ AJAX loading of vehicles will also work correctly
- ✅ Both create and edit forms will display vehicles consistently

## Related Code References

The rest of the codebase already correctly uses the `sharable` field:
- `BulkUploadController.php` - line 3329
- `VehicleController.php` - stores sharable values correctly
- `single-tour-package/edit.blade.php` - line 5271 (uses sharable for service type options)

This fix brings the DefaultValueController in line with the rest of the application.

