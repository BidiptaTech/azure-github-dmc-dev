# Hotel Rooms Bulk Import Implementation

## Overview
This document describes the implementation of the hotel rooms bulk import feature for DMC (Destination Management Company) users. The feature allows DMC users to update room prices and meal options in bulk by uploading a CSV file.

## Features Implemented

### 1. **DMC User Restrictions**
   - DMC users can ONLY edit:
     - Room prices (Single/Double, Weekday/Weekend)
     - Meal options (Breakfast, Lunch, Dinner)
     - Meal types (Buffet/Set Menu) and prices
   - DMC users CANNOT edit:
     - Room names/categories
     - Room dimensions
     - Room counts
     - Base room structure

### 2. **Import Button**
   - Added "Import Rooms" button to hotel rooms listing page
   - Only visible to DMC users (user_type = 2)
   - Located next to the Export dropdown button

### 3. **Import Template**
   - Downloads CSV template with existing room data from database
   - Includes all rooms accessible to the DMC user
   - Contains current prices and meal options
   - Template includes these columns:
     - `room_id` - Room identifier (required)
     - `hotel_id` - Hotel identifier (required)
     - `hotel_name` - Hotel name (reference only)
     - `room_type` - Room category (view only, cannot be changed)
     - `weekday_price` - Single weekday price (required)
     - `weekend_price` - Single weekend price (required)
     - `double_weekday_price` - Double weekday price (required)
     - `double_weekend_price` - Double weekend price (required)
     - `breakfast` - Breakfast included (0 or 1)
     - `breakfast_type` - Buffet or Set Menu (required if breakfast=1)
     - `breakfast_price` - Breakfast price (required if breakfast=1)
     - `lunch` - Lunch included (0 or 1)
     - `lunch_type` - Buffet or Set Menu (required if lunch=1)
     - `lunch_price` - Lunch price (required if lunch=1)
     - `dinner` - Dinner included (0 or 1)
     - `dinner_type` - Buffet or Set Menu (required if dinner=1)
     - `dinner_price` - Dinner price (required if dinner=1)
     - `base_room_type` - Base room flag (0 or 1, admin only)
     - `rooms_only_off` - Rooms only offer (0 or 1, admin only)

### 4. **Import Processing**
   - **File Validation**:
     - Accepts CSV files only
     - Maximum file size: 10MB
     - Validates required fields
     - Checks meal option dependencies
   
   - **Business Rules**:
     - If breakfast/lunch/dinner = 1, then type and price are mandatory
     - Meal type must be either "Buffet" or "Set Menu"
     - Meal prices must be non-negative numbers
     - DMC users can only update rooms in hotels they have access to
   
   - **DMC Room Creation**:
     - If DMC doesn't have their own room variation, creates a new one
     - Maintains original room as base reference
     - Allows each DMC to have custom pricing
   
   - **Duplicate Prevention**:
     - Uses cache to prevent duplicate uploads within 5 minutes
     - Based on user ID, filename, and file size

### 5. **Upload History**
   - Displays last 10 uploads for the current user
   - Shows for each upload:
     - Date and time (formatted and relative)
     - Original filename
     - Total records processed
     - Success count
     - Error count
     - Status (Success, Partial, Failed)
     - Error details (with modal view)
   
   - **Desktop and Mobile Views**:
     - Responsive table for desktop
     - Card-based layout for mobile
   
   - **Error Details Modal**:
     - Shows upload summary statistics
     - Lists all error messages
     - Numbered error list for easy reference
     - Scrollable content for long error lists

### 6. **Success/Error Messages**
   - **Success**: All rooms updated successfully
   - **Warning**: Some rooms updated, some failed (shows counts and first 10 errors)
   - **Error**: No rooms updated (shows all errors up to 10, with link to history)
   - Messages include icons and structured formatting
   - Auto-hide after 10 seconds
   - Smooth scroll to message on page load

## Files Created/Modified

### New Files Created:
1. **`resources/views/hotel/rooms-import.blade.php`**
   - Main import interface
   - Upload form with drag-and-drop
   - Live file preview
   - Upload history section
   - Error details modals
   - Modern UI with gradients and animations

2. **`app/Imports/RoomsImport.php`**
   - CSV processing logic
   - Row-by-row validation
   - Meal options validation
   - Room creation/update logic
   - Error collection and reporting
   - DMC room variation handling

3. **`ROOMS_BULK_IMPORT_IMPLEMENTATION.md`**
   - This documentation file

### Modified Files:
1. **`resources/views/hotel/create-room.blade.php`**
   - Added "Import Rooms" button
   - Only visible to DMC users

2. **`app/Http/Controllers/HotelController.php`**
   - Added `roomsImportView()` method
   - Added `roomsImport()` method
   - Added `roomsDownloadTemplate()` method

3. **`routes/web.php`**
   - Added route: `GET /rooms/import` → `rooms.import`
   - Added route: `POST /rooms/import` → `rooms.import.upload`
   - Added route: `GET /rooms/import/template` → `rooms.import.template`

## Routes

```php
// Room Bulk Import Routes - Only for DMC users (user_type = 2)
Route::get('/rooms/import', [HotelController::class, 'roomsImportView'])->name('rooms.import');
Route::post('/rooms/import', [HotelController::class, 'roomsImport'])->name('rooms.import.upload');
Route::get('/rooms/import/template', [HotelController::class, 'roomsDownloadTemplate'])->name('rooms.import.template');
```

## User Workflow

### Step 1: Access Import Page
1. Log in as DMC user
2. Navigate to hotel rooms listing
3. Click "Import Rooms" button

### Step 2: Download Template
1. Click "Download Template" button
2. Receives CSV file with current room data
3. Template includes all rooms for DMC's hotels

### Step 3: Modify Template
1. Open CSV file in spreadsheet software
2. Update prices for Single/Double Weekday/Weekend
3. Update meal options (breakfast, lunch, dinner)
4. Set meal types and prices if meal included
5. Do NOT modify room_id, hotel_id, or room_type
6. Save as CSV file

### Step 4: Upload File
1. Drag and drop CSV file or click to browse
2. File preview shows filename and size
3. Click "Upload & Import" button
4. System processes file and shows progress

### Step 5: View Results
1. Success/warning/error message displays at top
2. Shows count of successful and failed updates
3. Lists up to 10 error messages inline
4. Full error details available in upload history

### Step 6: Review History
1. Scroll to "Upload History" section
2. View all recent uploads with status
3. Click "View Errors" for failed rows
4. Modal shows detailed error information

## Validation Rules

### Required Fields:
- `room_id`
- `hotel_id`
- `weekday_price`
- `weekend_price`
- `double_weekday_price`
- `double_weekend_price`

### Conditional Requirements:
- If `breakfast = 1`:
  - `breakfast_type` is required (must be "Buffet" or "Set Menu")
  - `breakfast_price` is required (must be >= 0)
- If `lunch = 1`:
  - `lunch_type` is required (must be "Buffet" or "Set Menu")
  - `lunch_price` is required (must be >= 0)
- If `dinner = 1`:
  - `dinner_type` is required (must be "Buffet" or "Set Menu")
  - `dinner_price` is required (must be >= 0)

### Business Rules:
- Room must exist in database
- Hotel must be accessible to DMC user
- Prices must be non-negative numbers
- Only meal types "Buffet" and "Set Menu" are allowed
- `base_room_type` and `rooms_only_off` are 0 or 1

## Error Handling

### Common Errors:
1. **"Room not found"**: Room ID doesn't exist or doesn't match hotel ID
2. **"Hotel not found"**: Hotel ID doesn't exist in database
3. **"You don't have access to this hotel"**: DMC user not authorized for this hotel
4. **"Meal type required when meal is included"**: Breakfast/lunch/dinner = 1 but type is empty
5. **"Invalid meal type"**: Meal type is not "Buffet" or "Set Menu"
6. **"Valid meal price required"**: Meal price is missing, not numeric, or negative
7. **"Weekday price is required"**: Required price field is empty
8. **"Price must be a number"**: Price field contains non-numeric value
9. **"Price cannot be negative"**: Price is less than 0

### Error Display:
- Errors shown with row numbers for easy correction
- Up to 10 errors displayed inline in alert
- Full error list available in upload history modal
- Errors grouped by type for easier understanding

## Security & Permissions

### Access Control:
- Only DMC users (user_type = 2) can access import feature
- 403 Forbidden error for unauthorized users
- Users can only update rooms in hotels they have access to
- Hotel access validated via `dmc_id` JSON field

### Data Protection:
- File uploads cached to prevent duplicates
- 5-minute cache window for same file
- Temporary files deleted after processing
- Upload history tied to user account

## Technical Implementation Details

### DMC Room Variations:
- Original rooms created by admin (role_id 1 or 20)
- DMC users can create variations with custom prices
- System checks for existing DMC room before creating new one
- Variations linked via hotel_id + room_type + created_by
- `dmc_base_room = 0` for DMC variations

### CSV Processing:
- Uses Maatwebsite/Excel package
- WithHeadingRow trait for automatic header mapping
- WithValidation trait for row-level validation
- ToCollection for flexible processing
- Processes rows sequentially with error collection

### Upload History:
- Stored in `upload_history` table
- Fields: upload_type, uploaded_by, original_file_name, total_records, success_count, error_count, errors (JSON), status
- Status calculated automatically: success, partial, or failed
- Timestamps auto-managed by Laravel
- Uses accessors for formatted dates

## Testing Scenarios

### Test Case 1: Successful Import
1. Download template
2. Update prices only
3. Upload file
4. Verify: All rooms updated, success message shown

### Test Case 2: Meal Option Changes
1. Download template
2. Set breakfast = 1, add type and price
3. Upload file
4. Verify: Meal options updated, breakfast shows in room details

### Test Case 3: Missing Meal Details
1. Download template
2. Set lunch = 1, but leave type and price empty
3. Upload file
4. Verify: Error message about required meal type and price

### Test Case 4: Invalid Hotel Access
1. Modify CSV to include rooms from another DMC's hotel
2. Upload file
3. Verify: Error message "You don't have access to this hotel"

### Test Case 5: Duplicate Upload
1. Upload a file successfully
2. Upload same file again within 5 minutes
3. Verify: Error message about duplicate upload

### Test Case 6: Large File
1. Create CSV with 1000+ rooms
2. Upload file
3. Verify: Processing completes, all rows processed

### Test Case 7: Mixed Success/Errors
1. Create CSV with some valid and some invalid rows
2. Upload file
3. Verify: Warning message shows both success and error counts

## Future Enhancements

### Potential Improvements:
1. **Batch Processing**: Process large files in chunks for better performance
2. **Preview Before Import**: Show changes before applying them
3. **Rollback Feature**: Ability to undo last import
4. **Export Current Prices**: Export just prices without other room data
5. **Import Templates**: Save common price update patterns as templates
6. **Scheduling**: Schedule price updates for future dates
7. **Notifications**: Email notifications on import completion
8. **Audit Trail**: Detailed log of price changes over time
9. **Bulk Operations**: Apply percentage increase/decrease to all rooms
10. **Multi-Hotel Support**: Import prices for multiple hotels at once

## Troubleshooting

### Issue: Import button not visible
**Solution**: Verify user has `user_type = 2` (DMC user)

### Issue: Template download returns empty file
**Solution**: Check that DMC user has hotels assigned in `dmc_id` field

### Issue: All rows failing validation
**Solution**: Verify CSV format matches template structure

### Issue: Meal options not updating
**Solution**: Ensure meal type is exactly "Buffet" or "Set Menu" (case-sensitive)

### Issue: Duplicate upload error persists
**Solution**: Wait 5 minutes or clear application cache

### Issue: Large file timeout
**Solution**: Increase `max_execution_time` in php.ini or process in smaller batches

## Support & Maintenance

### Log Files:
- Import errors logged to Laravel log: `storage/logs/laravel.log`
- Search for: "Room import error"
- Includes row numbers and error details

### Database Tables:
- **Main table**: `rooms` (hotel rooms)
- **History table**: `upload_history` (import records)
- **Users table**: `users` (DMC user info)
- **Hotels table**: `hotels` (hotel data and DMC access)

### Cache Keys:
- Format: `room_upload_{userId}_{md5(filename+size)}`
- TTL: 5 minutes
- Can be cleared manually if needed

## Conclusion

The Hotel Rooms Bulk Import feature provides DMC users with an efficient way to manage room prices and meal options at scale. The implementation follows Laravel best practices, includes comprehensive validation, and provides excellent user feedback through detailed error messages and upload history tracking.

The system is designed to be extensible, allowing for future enhancements while maintaining data integrity and security through proper access controls and validation rules.

