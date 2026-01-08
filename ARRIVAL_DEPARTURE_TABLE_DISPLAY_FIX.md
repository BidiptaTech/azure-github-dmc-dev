# Arrival/Departure Table Display Fix

## Issues Fixed

### 1. Port Not Displaying in Dropdown List
**Problem**: The port dropdown was not showing options when adding or editing arrival/departure entries.

**Root Cause**: Select2 initialization was not being properly re-initialized after showing/hiding modal fields.

**Solution**: 
- Added Select2 re-initialization in `openArrivalDepartureModal()` function with a 100ms delay to ensure fields are visible before initialization
- Added Select2 re-initialization in `editArrivalDeparture()` function to ensure dropdowns work when editing existing entries
- Both functions now destroy existing Select2 instances before re-initializing to prevent conflicts

### 2. Vehicle Type Not Displayed
**Problem**: The Vehicle Type was not displayed in the arrival/departure table, even though the data was being saved.

**Solution**: 
- Updated `updateArrivalDepartureTable()` function to display vehicle type below the port name
- Vehicle type is displayed with proper capitalization (e.g., "Sedan", "Suv")
- Displayed in smaller, gray text (9px, #6c757d color)

### 3. SIC/Private Not Displayed
**Problem**: The SIC/Private (transfer type) was not displayed in the arrival/departure table.

**Solution**: 
- Updated `updateArrivalDepartureTable()` function to display transfer type below the port name
- Transfer type is displayed in uppercase (e.g., "SIC", "PRIVATE")
- Displayed alongside vehicle type in format: "Vehicle Type / SIC or PRIVATE"
- Displayed in smaller, gray text (9px, #6c757d color)

## Changes Made

### File: `resources/views/enquiryform_pro/create.blade.php`

#### 1. Table Header (Lines 1104-1120)
Table header remains unchanged - Vehicle Type and SIC/Private are displayed within the Port column as secondary information.

#### 2. Table Body Rendering Update (Lines 7657-7700)
Added vehicle type and transfer type display below port name:
```javascript
// Format vehicle type and transfer type for display
const vehicleTypeDisplay = item.vehicleType ? item.vehicleType.charAt(0).toUpperCase() + item.vehicleType.slice(1) : '';
const transferTypeDisplay = item.transferType ? item.transferType.toUpperCase() : '';

// Build the secondary info line (vehicle type and SIC/Private)
let secondaryInfo = '';
if (vehicleTypeDisplay && transferTypeDisplay) {
    secondaryInfo = `<div style="font-size: 9px; color: #6c757d; margin-top: 2px;">${vehicleTypeDisplay} / ${transferTypeDisplay}</div>`;
} else if (vehicleTypeDisplay) {
    secondaryInfo = `<div style="font-size: 9px; color: #6c757d; margin-top: 2px;">${vehicleTypeDisplay}</div>`;
} else if (transferTypeDisplay) {
    secondaryInfo = `<div style="font-size: 9px; color: #6c757d; margin-top: 2px;">${transferTypeDisplay}</div>`;
}

return `
    <tr>
        ...
        <td>
            <a href="javascript:void(0)" onclick="editArrivalDeparture(${item.originalIndex})" style="color: #0d6efd; text-decoration: underline; cursor: pointer;">
                ${item.portName || '-'}
            </a>
            ${secondaryInfo}    <!-- NEW: Vehicle Type / SIC/Private below port name -->
        </td>
        <td>${item.flightNo || '-'}</td>
        <td>${item.type}</td>
        ...
    </tr>
`;
```

#### 3. Select2 Re-initialization in openArrivalDepartureModal() (Lines 7604-7621)
```javascript
// Re-initialize Select2 for port dropdowns after showing fields
setTimeout(() => {
    if (typeof $.fn.select2 !== 'undefined') {
        // Destroy existing Select2 instances if they exist
        if ($('#arrivalPort').hasClass('select2-hidden-accessible')) {
            $('#arrivalPort').select2('destroy');
        }
        if ($('#departurePort').hasClass('select2-hidden-accessible')) {
            $('#departurePort').select2('destroy');
        }
        
        // Re-initialize Select2
        $('.select2-port').select2({
            placeholder: 'Search and select port',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#accommodationModal')
        });
    }
}, 100);
```

#### 4. Select2 Re-initialization in editArrivalDeparture() (Lines 7789-7806)
```javascript
// Re-initialize Select2 for port dropdowns to ensure they work properly
if (typeof $.fn.select2 !== 'undefined') {
    // Destroy existing Select2 instances if they exist
    if ($('#arrivalPort').hasClass('select2-hidden-accessible')) {
        $('#arrivalPort').select2('destroy');
    }
    if ($('#departurePort').hasClass('select2-hidden-accessible')) {
        $('#departurePort').select2('destroy');
    }
    
    // Re-initialize Select2
    $('.select2-port').select2({
        placeholder: 'Search and select port',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#accommodationModal')
    });
}
```

## Testing Steps

### Test 1: Add New Arrival/Departure
1. Click "+ Add" button in Arrival/Departure section
2. Click on "Arrival Port" dropdown
3. **Expected**: Dropdown should open and show list of ports with search functionality
4. Select a port, fill in other details
5. Save the entry
6. **Expected**: Table should display the port name, vehicle type, and SIC/Private columns

### Test 2: Edit Existing Arrival/Departure
1. Click on an existing port name in the arrival/departure table
2. **Expected**: Modal should open with port dropdown showing the selected port
3. Click on the port dropdown
4. **Expected**: Dropdown should open and allow changing the port
5. Verify vehicle type and transfer type are displayed correctly

### Test 3: Verify Vehicle Type and SIC/Private Display
1. Add multiple arrival/departure entries with different vehicle types (Sedan, SUV, etc.)
2. Add entries with different transfer types (SIC, Private)
3. **Expected**: 
   - Below each port name, you should see small gray text showing: "Sedan / SIC", "Suv / PRIVATE", etc.
   - If only vehicle type exists: shows "Sedan"
   - If only transfer type exists: shows "SIC"
   - If neither exists: no secondary line is displayed

## Data Flow

1. **Port Selection**: 
   - User selects port from Select2 dropdown
   - Port ID and Port Name are saved to `arrivalDepartureList`
   - Port Name is displayed in table as clickable link (main line)

2. **Vehicle Type**:
   - Selected from dropdown when "Transfer" checkbox is checked
   - Saved as lowercase (e.g., "sedan", "suv")
   - Displayed with first letter capitalized (e.g., "Sedan", "Suv")
   - Shown below port name in small gray text

3. **Transfer Type (SIC/Private)**:
   - Selected from dropdown when "Transfer" checkbox is checked
   - Saved as lowercase (e.g., "sic", "private")
   - Displayed in uppercase (e.g., "SIC", "PRIVATE")
   - Shown below port name in small gray text, after vehicle type

## Display Format

The Port column now displays information in a stacked format:

```
Port Name (clickable, blue, underlined)
Vehicle Type / SIC or PRIVATE (small, gray text)
```

Examples:
- `Mumbai Airport`
  `Sedan / SIC`

- `Delhi Railway Station`
  `Suv / PRIVATE`

- `Bangalore Airport`
  *(no secondary line if no transfer selected)*

## Notes

- Port dropdown uses Select2 for searchable dropdown functionality
- Select2 must be initialized after modal fields are visible
- Destroying and re-initializing Select2 prevents conflicts with existing instances
- Vehicle type and transfer type are only displayed if they have values
- Secondary info line uses 9px font size and #6c757d color (Bootstrap's text-muted)
- The data was already being saved correctly; only the display was missing

## Status
✅ **COMPLETED** - All three issues have been fixed and tested

