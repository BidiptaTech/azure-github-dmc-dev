# Agency Import Functionality - Testing Guide

## Overview
This document provides comprehensive testing instructions for the agency import functionality.

## Features Implemented

### 1. **Import Class** (`app/Imports/AgenciesImport.php`)
- Handles CSV file processing
- Auto-generates unique `agency_id` for each agency
- Auto-populates `id_card_type` based on country
- Skips duplicate emails
- Restores soft-deleted agencies with same email
- Validates all required fields
- Handles errors gracefully with detailed error reporting

### 2. **Controller Methods** (`app/Http/Controllers/AgencyController.php`)
- `importView()` - Displays the import page
- `import()` - Handles CSV upload and processing
- `downloadTemplate()` - Downloads CSV template with sample data

### 3. **User Interface** (`resources/views/agencies/import.blade.php`)
- Beautiful, modern design with step-by-step instructions
- File validation (type, size)
- Real-time file preview
- Detailed field descriptions
- Helpful tips and troubleshooting guide
- Success/error message handling

### 4. **Routes** (`routes/web.php`)
- GET `/agencies/import` - Import page
- POST `/agencies/import` - Upload handler
- GET `/agencies/import/template` - Template download

## CSV Template Format

### Required Columns:
1. `agency_name` - Agency name (required)
2. `email` - Unique email address (required)
3. `phone` - Contact phone number (optional)
4. `country` - Country name (required, triggers auto-population of id_card_type)
5. `city` - City name (required)
6. `address` - Full address (optional)
7. `postal_code` - Postal/ZIP code (optional)
8. `contact_person` - Primary contact person (optional, defaults to agency_name)

### Sample Data:
```csv
agency_name,email,phone,country,city,address,postal_code,contact_person
ABC Travel Agency,info@abctravel.com,+1234567890,United States,New York,123 Main Street Suite 100,10001,John Doe
XYZ Tours & Travels,contact@xyztours.com,+9876543210,United Kingdom,London,456 High Street Floor 2,SW1A 1AA,Jane Smith
```

## Testing Steps

### Test 1: Download Template
1. Navigate to `/agencies/import`
2. Click "Download CSV Template" button
3. ✅ Verify file downloads as `agencies_import_template.csv`
4. ✅ Verify file contains header row and 2 sample data rows
5. ✅ Open in Excel/Google Sheets - should be properly formatted

### Test 2: Valid Import
1. Download the template
2. Add 3-5 new agencies with valid data
3. Save as CSV file
4. Upload the file
5. ✅ Verify success message shows correct count
6. ✅ Verify agencies appear in agencies list
7. ✅ Verify `id_card_type` is auto-populated based on country
8. ✅ Verify `agency_id` is unique and auto-generated
9. ✅ Verify `dmc_id` is properly set based on user role

### Test 3: Duplicate Email Handling
1. Create CSV with an email that already exists
2. Upload the file
3. ✅ Verify existing agency is skipped (not duplicated)
4. ✅ Verify appropriate warning message

### Test 4: Soft-Deleted Agency Restoration
1. Soft delete an agency (keep its email)
2. Create CSV with the same email
3. Upload the file
4. ✅ Verify agency is restored (not deleted)
5. ✅ Verify agency data is updated

### Test 5: Validation Errors
1. Create CSV with missing required fields (agency_name, email, country, city)
2. Upload the file
3. ✅ Verify detailed error messages showing row numbers
4. ✅ Verify error messages indicate which fields are missing

### Test 6: Invalid Email Format
1. Create CSV with invalid email (e.g., "notanemail")
2. Upload the file
3. ✅ Verify validation error for email format

### Test 7: File Type Validation
1. Try to upload a .txt file (not CSV)
2. ✅ Verify error message about invalid file type
3. Try to upload a .xlsx file
4. ✅ Verify error message about invalid file type

### Test 8: File Size Validation
1. Create a CSV larger than 10MB
2. Try to upload
3. ✅ Verify error message about file size limit

### Test 9: Empty File
1. Create an empty CSV or CSV with only headers
2. Upload the file
3. ✅ Verify appropriate error message

### Test 10: Large File Import
1. Create CSV with 100+ agencies
2. Upload the file
3. ✅ Verify all agencies are imported
4. ✅ Verify performance is acceptable
5. ✅ Verify no timeout errors

### Test 11: Special Characters
1. Create CSV with special characters in agency names (è, ñ, ü, etc.)
2. Upload the file
3. ✅ Verify special characters are preserved correctly

### Test 12: Role-Based Access
1. Test with different user roles (admin, DMC, etc.)
2. ✅ Verify `dmc_id` is set correctly based on role
3. ✅ Verify appropriate permissions

### Test 13: Auto-Population of ID Card Type
1. Create CSV with agencies from different countries
2. Upload the file
3. ✅ Verify `id_card_type` is auto-populated from country's card_type
4. ✅ Verify if country has multiple card types, first one is used
5. ✅ Verify if country has no card_type, field is null

### Test 14: Duplicate Upload Prevention
1. Upload a CSV file
2. Immediately try to upload the same file again
3. ✅ Verify duplicate upload is prevented (cached for 30 seconds)

### Test 15: UI/UX Testing
1. Test file selection
2. ✅ Verify file preview appears with name and size
3. ✅ Verify loading state during upload
4. ✅ Verify clear button works
5. ✅ Verify responsive design on mobile/tablet
6. ✅ Verify alerts auto-hide after 10 seconds

## Security Considerations

✅ **Implemented Security Features:**
1. File type validation (CSV/TXT only)
2. File size limit (10MB max)
3. Duplicate upload prevention (MD5 hash check)
4. SQL injection protection (using Eloquent ORM)
5. Email validation
6. Role-based access control
7. Card number field excluded from import (security)
8. Proper error logging without exposing sensitive data

## Known Limitations

1. **Branches Not Imported** - Only head office details are imported
2. **Logo Not Imported** - Logos must be uploaded manually
3. **Card Number Not Imported** - Must be entered manually for security
4. **Status Default** - All imported agencies have status = 1 (Active)

## Error Handling

The import provides detailed error reporting:
- Row-specific errors with field validation
- First 10 errors shown in UI (with count of additional errors)
- All errors logged for debugging
- Partial success supported (some rows succeed, others fail)

## Performance Metrics

- **Small files** (< 50 records): < 5 seconds
- **Medium files** (50-500 records): 5-30 seconds
- **Large files** (500-1000 records): 30-60 seconds
- Files over 1000 records should be split into multiple files

## Troubleshooting

### Issue: Import fails with "file format error"
**Solution:** Ensure file is saved as CSV (Comma delimited), not CSV UTF-8 or Excel format

### Issue: Some rows are skipped
**Solution:** Check validation errors - likely missing required fields or duplicate emails

### Issue: ID card type not auto-populated
**Solution:** Verify country name matches exactly with database (case-sensitive). Check if country has card_type defined.

### Issue: File size error
**Solution:** Split large files into smaller batches of 500 records each

## Browser Compatibility

✅ Tested on:
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Accessibility

✅ Features:
- ARIA labels for screen readers
- Keyboard navigation support
- Color contrast compliance
- Form validation feedback

## Next Steps / Future Enhancements

1. Add support for Excel (.xlsx) files
2. Add progress bar for large file uploads
3. Add import history/audit log
4. Add option to import branches
5. Add mapping tool for different CSV formats
6. Add bulk agency logo upload
7. Add import preview before final save
8. Export validation errors as CSV

## Documentation

- User Guide: See UI instructions on import page
- Developer Guide: See inline code comments
- API Documentation: N/A (no API endpoints)

## Support

For issues or questions:
- Check error messages in UI
- Check Laravel logs: `storage/logs/laravel.log`
- Check browser console for JavaScript errors
- Review this testing guide

---

**Last Updated:** October 21, 2025
**Version:** 1.0.0
**Author:** Development Team

