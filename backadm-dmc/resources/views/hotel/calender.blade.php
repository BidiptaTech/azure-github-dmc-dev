@extends('layouts.layout')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-bs5/2.1.8/dataTables.bootstrap5.min.css" integrity="sha512-9d9bjYZUo25k3MPAMpx+OUyvGQcbJe8qGOUJilgowXEPc0lNCVoe+zHZX8HszzkDJEUynZeF648jP9JLX1Pi7A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
<style>
    /* Blackout dates: Red with white text */
    .event-blackout {
        background-color: rgba(255, 0, 0, 0.1); /* Darker red */
        color: #8B0000; /* Dark red text */
    }

    /* Fair dates: Orange with black text */
    .event-fair {
        background-color: rgba(255, 165, 0, 0.1); /* Darker orange */
        color: #FF8C00; /* Dark orange text */
    }

    /* Season dates: Green with black text */
    .event-season {
        background-color: rgba(34, 139, 34, 0.1); /* Darker green */
        color: #228B22; /* Dark green text */
    }

    /* Weekends: Lighter Yellow with black text */
    .fc-day-sun,
    .fc-day-sat {
        background-color: rgba(255, 215, 0, 0.3); /* Lighter yellow */
        color: #FFD700; /* Gold text */
    }

    /* Base dates with price */
    .price-container {
        font-size: 14px;
        font-weight: bold;
        margin-top: 5px;
        text-align: center;
    }

    /* Event names */
    .event-name {
        font-size: 14px;
        font-weight: bold;
        color: #333; /* Dark color for better readability */
        text-align: center;
        margin-top: 3px;
    }

    /* Styling for past dates */
    .fc-day-past {
        background-color: rgba(169, 169, 169, 0.3); /* Light gray */
        color: #696969; /* Dim gray text */
    }

    /* Color legend styling */
    .color-legend {
        display: flex;
        justify-content: space-around;
        margin-bottom: 15px;
    }
    .color-box {
        width: 30px;
        height: 30px;
        display: inline-block;
    }
    .color-text {
        margin-left: 10px;
        line-height: 30px;
    }
    .fc .fc-daygrid-day-frame {
    position: relative;
    min-height: 50%;
    }
</style>

@section('content')
<div class="page-content mt-1"> <!-- Added margin-top -->
    <div class="page-container">
        <div class="row justify-content-center">
            <div class="col-lg-12 col-md-10 col-sm-12">
                <div class="card">
                    @include('hotel.tapview', ['hotel' => $hotel])
                    <div class="card-body p-1">
                        <div class="d-flex justify-content-between align-items-center my-4">
                            <h2 class="text-center mb-0">Monthly Calendar</h2>
                            <button id="exportExcel" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </button>
                        </div>

                        <!-- Color Legend -->
                        <div class="color-legend">
                            <div>
                                <div class="color-box" style="background-color: rgba(255, 0, 0, 0.1);"></div>
                                <span class="color-text">Blackout Date</span>
                            </div>
                            <div>
                                <div class="color-box" style="background-color: rgba(255, 165, 0, 0.1);"></div>
                                <span class="color-text">Fair Date</span>
                            </div>
                            <div>
                                <div class="color-box" style="background-color: rgba(34, 139, 34, 0.1);"></div>
                                <span class="color-text">Season Date</span>
                            </div>
                            <div>
                                <div class="color-box" style="background-color: rgba(255, 215, 0, 0.3);"></div>
                                <span class="color-text">Weekend</span>
                            </div>
                        </div>

                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Options Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export Options</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Export Range</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="exportRange" id="monthlyExport" value="monthly" checked>
                            <label class="form-check-label" for="monthlyExport">
                                Monthly Export (Next 30 days from today)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="exportRange" id="yearlyExport" value="yearly">
                            <label class="form-check-label" for="yearlyExport">
                                Yearly Export (Next 365 days from today)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmExport">Export</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- ExcelJS for proper Excel export with colors -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
<!-- FileSaver.js for saving files -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');
        const rateDates = @json($rate_dates);  // Data coming from backend
        const weekendDays = [0, 6];  // Weekend days: Sunday (0) and Saturday (6)
        const weekdayBasePrice = @json($weekday_base_price);  // Minimum weekday base price
        const weekendBasePrice = @json($weekend_base_price);  // Minimum weekend base price
        const hotelName = @json($hotel->name);  // Hotel name for the file name
        
        // Initialize Bootstrap 5 modal
        const exportModalEl = document.getElementById('exportModal');
        if (exportModalEl) {
            const exportModal = new bootstrap.Modal(exportModalEl);
            console.log("Modal initialized with Bootstrap 5");
            
            // Button to open modal
            document.getElementById('exportExcel').addEventListener('click', function(e) {
                e.preventDefault();
                console.log("Export button clicked, opening modal");
                exportModal.show();
            });
        }

        // Function to get date range based on export type
        function getDateRange(exportType) {
            const today = new Date();
            // Get the calendar's valid end date
            const validEndDate = new Date('{{ date("Y", strtotime("+1 year")) }}-12-31');
            
            let startDate, endDate;
            
            if (exportType === 'monthly') {
                // Monthly export: from today to 30 days later
                startDate = new Date(today);
                endDate = new Date(today);
                endDate.setDate(today.getDate() + 30); // 30 days from today
            } else { // yearly
                // Yearly export: from today to one year later
                startDate = new Date(today);
                endDate = new Date(today);
                endDate.setFullYear(today.getFullYear() + 1); // 1 year from today
                
                // If yearly range exceeds valid calendar range, adjust end date
                if (endDate > validEndDate) {
                    endDate = validEndDate;
                }
            }
            
            return { startDate, endDate };
        }

        // Function to prepare data for Excel export
        function prepareExcelData(exportType) {
            const data = [];
            // Remove the headers from here as they're added directly to the worksheet
            // const headers = ['Date', 'Event', 'Event Type', 'Price'];
            // data.push(headers);

            const { startDate, endDate } = getDateRange(exportType);
            const currentDate = new Date(startDate);

            while (currentDate <= endDate) {
                const dateStr = currentDate.toISOString().split('T')[0];
                
                // Calculate the shifted date (since the calendar data has a shift)
                const shiftedDate = new Date(currentDate);
                shiftedDate.setDate(shiftedDate.getDate() + 1);
                const shiftedDateStr = shiftedDate.toISOString().split('T')[0];
                
                // Default values
                let event = 'No Event';
                let eventType = 'Regular';
                let price = '';
                
                // Check if the shifted date has data in rateDates
                if (rateDates[shiftedDateStr]) {
                    const rateData = rateDates[shiftedDateStr];
                    event = rateData.event || 'No Event';
                    eventType = rateData.event_type || 'Regular';
                    price = rateData.price || '';
                } else {
                    // Assign default price based on whether it's a weekend or weekday (original date)
                    // This logic matches how prices are determined in the calendar display (see dayCellDidMount)
                    const day = currentDate.getDay(); // 0 = Sunday, 6 = Saturday
                    const isWeekend = weekendDays.includes(day);
                    price = isWeekend ? weekendBasePrice : weekdayBasePrice;
                }

                // Add the row to the data
                const row = [dateStr, event, eventType, `₹${price}`];
                data.push(row);

                // Move to the next day
                currentDate.setDate(currentDate.getDate() + 1);
            }

            return data;
        }

        // Function to export to Excel using ExcelJS (supports colors)
        async function exportToExcel(exportType) {
            console.log("Starting Excel export with ExcelJS...");
            
            try {
                // Show a loading message
                const exportButton = document.getElementById('exportExcel');
                const originalText = exportButton.innerHTML;
                exportButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';
                exportButton.disabled = true;
                
                // First prepare the data
                const data = prepareExcelData(exportType);
                
                // Create a new Excel workbook and worksheet
                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet('Calendar Data');
                
                // Event type color mapping
                const typeColors = {
                    'Blackout Date': { bg: 'FFCCCC', font: '8B0000' }, // Light red bg, dark red text
                    'Fair Date': { bg: 'FFDEAD', font: 'FF8C00' },     // Light orange bg, dark orange text
                    'Season': { bg: 'CCFFCC', font: '228B22' },        // Light green bg, dark green text
                    'Regular': { bg: 'FFFFFF', font: '000000' }        // White bg, black text
                };
                
                // Set column widths - don't add header row automatically
                worksheet.columns = [
                    { key: 'date', width: 25 },
                    { key: 'event', width: 30 },
                    { key: 'eventType', width: 20 },
                    { key: 'price', width: 15 }
                ];
                
                // Track the current month for adding headers and separators
                let currentMonth = null;
                let rowIndex = 1; // Start from row 1 since we're not adding a header row
                
                // Process all data rows (starting from index 1 to skip original header)
                for (let i = 1; i < data.length; i++) {
                    const dateStr = data[i][0];
                    const date = new Date(dateStr);
                    const month = date.getMonth();
                    const year = date.getFullYear();
                    
                    // If month changes or this is the first item, add month header and column headers
                    if (currentMonth === null || month !== currentMonth) {
                        // Add a blank row for spacing if not the first month
                        if (currentMonth !== null) {
                            const spacerRow = worksheet.addRow(['', '', '', '']);
                            rowIndex++;
                        }
                        
                        // Add month name and year as a header
                        const monthYearHeader = date.toLocaleString('default', { month: 'long', year: 'numeric' });
                        const monthRow = worksheet.addRow([monthYearHeader, '', '', '']);
                        rowIndex++;
                        
                        // Style the month header
                        monthRow.font = { bold: true, size: 14 };
                        monthRow.fill = {
                            type: 'pattern',
                            pattern: 'solid',
                            fgColor: { argb: 'C6E0B4' }
                        };
                        monthRow.alignment = { horizontal: 'center' };
                        
                        // Merge cells for the month header (explicitly specify all 4 columns)
                        worksheet.mergeCells(`A${rowIndex}:D${rowIndex}`);
                        
                        // Add border to the merged month header
                        monthRow.eachCell({ includeEmpty: true }, (cell, colNumber) => {
                            if (colNumber <= 4) {
                                cell.border = {
                                    top: { style: 'thin' },
                                    left: { style: 'thin' },
                                    bottom: { style: 'thin' },
                                    right: { style: 'thin' }
                                };
                            }
                        });
                        
                        // Add a new header row for this month
                        const subHeaderRow = worksheet.addRow(['Date', 'Event', 'Event Type', 'Price']);
                        rowIndex++;
                        
                        // Style the sub-header row
                        subHeaderRow.font = { bold: true };
                        subHeaderRow.fill = {
                            type: 'pattern',
                            pattern: 'solid',
                            fgColor: { argb: 'E0E0E0' }
                        };
                        subHeaderRow.alignment = { horizontal: 'center' };
                        
                        // Add borders to the sub-header row
                        subHeaderRow.eachCell((cell) => {
                            cell.border = {
                                top: { style: 'thin' },
                                left: { style: 'thin' },
                                bottom: { style: 'thin' },
                                right: { style: 'thin' }
                            };
                        });
                        
                        currentMonth = month;
                    }
                    
                    // Format the date: "Sun, 17th May'25"
                    const dayName = date.toLocaleString('en-US', { weekday: 'short' });
                    const day = date.getDate();
                    const monthName = date.toLocaleString('en-US', { month: 'short' });
                    const shortYear = date.toLocaleString('en-US', { year: '2-digit' });
                    
                    // Add ordinal suffix to day (1st, 2nd, 3rd, etc.)
                    function getOrdinalSuffix(d) {
                        if (d > 3 && d < 21) return 'th';
                        switch (d % 10) {
                            case 1: return 'st';
                            case 2: return 'nd';
                            case 3: return 'rd';
                            default: return 'th';
                        }
                    }
                    
                    const formattedDate = `${dayName}, ${day}${getOrdinalSuffix(day)} ${monthName}'${shortYear}`;
                    
                    // Get event type and color scheme
                    const event = data[i][1];
                    const eventType = data[i][2] || 'Regular';
                    const price = data[i][3];
                    const colorSet = typeColors[eventType] || typeColors.Regular;
                    
                    // Add a data row
                    const dataRow = worksheet.addRow([formattedDate, event, eventType, price]);
                    rowIndex++;
                    
                    // Style the entire row with the appropriate color
                    dataRow.eachCell({ includeEmpty: true }, (cell, colNumber) => {
                        if (colNumber <= 4) {
                            // Apply background color
                            cell.fill = {
                                type: 'pattern',
                                pattern: 'solid',
                                fgColor: { argb: colorSet.bg }
                            };
                            
                            // Apply font color
                            cell.font = {
                                color: { argb: colorSet.font }
                            };
                            
                            // Align text
                            cell.alignment = {
                                horizontal: colNumber === 1 ? 'left' : 'center'
                            };
                            
                            // Add borders to each cell
                            cell.border = {
                                top: { style: 'thin' },
                                left: { style: 'thin' },
                                bottom: { style: 'thin' },
                                right: { style: 'thin' }
                            };
                        }
                    });
                    
                    // Check if the next row is a different month or if this is the last row
                    const isLastRow = (i === data.length - 1);
                    const nextDifferentMonth = !isLastRow && (new Date(data[i+1][0]).getMonth() !== month);
                    
                    // If this is the last day of a month, add a separator
                    if (nextDifferentMonth || isLastRow) {
                        const monthName = date.toLocaleString('default', { month: 'long', year: 'numeric' });
                        const separatorRow = worksheet.addRow([`=== End of ${monthName} ===`, '', '', '']);
                        rowIndex++;
                        
                        // Style the separator row
                        separatorRow.font = { bold: true, color: { argb: 'FFFFFF' } };
                        separatorRow.fill = {
                            type: 'pattern',
                            pattern: 'solid',
                            fgColor: { argb: '4472C4' }
                        };
                        separatorRow.alignment = { horizontal: 'center' };
                        
                        // Merge cells for the separator (explicitly specify all 4 columns)
                        worksheet.mergeCells(`A${rowIndex}:D${rowIndex}`);
                        
                        // Add border to the merged separator row
                        separatorRow.eachCell({ includeEmpty: true }, (cell, colNumber) => {
                            if (colNumber <= 4) {
                                cell.border = {
                                    top: { style: 'thin' },
                                    left: { style: 'thin' },
                                    bottom: { style: 'thin' },
                                    right: { style: 'thin' }
                                };
                            }
                        });
                    }
                }
                
                // Generate filename
                const { startDate, endDate } = getDateRange(exportType);
                const dateRange = `${startDate.toISOString().split('T')[0]}_to_${endDate.toISOString().split('T')[0]}`;
                const fileName = `${hotelName.replace(/[^a-z0-9]/gi, '_').toLowerCase()}_calendar_${dateRange}.xlsx`;
                
                console.log("Writing Excel file...");
                
                // Generate the Excel file
                const buffer = await workbook.xlsx.writeBuffer();
                saveAs(new Blob([buffer]), fileName);
                
                // Reset the export button
                exportButton.innerHTML = originalText;
                exportButton.disabled = false;
                
                console.log("Excel export complete!");
                return true;
            } catch (error) {
                console.error("Error in Excel export:", error);
                alert("There was an error exporting to Excel: " + error.message);
                
                // Reset the export button
                const exportButton = document.getElementById('exportExcel');
                exportButton.innerHTML = '<i class="fas fa-file-excel"></i> Export to Excel';
                exportButton.disabled = false;
                
                return false;
            }
        }
        
        // Function to handle export button click
        window.triggerExport = function() {
            console.log("Export button clicked through onclick");
            try {
                // Get the selected export type
                const exportType = document.querySelector('input[name="exportRange"]:checked').value;
                console.log("Selected export type:", exportType);
                
                // Hide the modal first before processing
                const exportModal = document.getElementById('exportModal');
                const bsModal = bootstrap.Modal.getInstance(exportModal);
                if (bsModal) {
                    bsModal.hide();
                }
                
                // Run the export (it handles its own loading state)
                exportToExcel(exportType);
            } catch (error) {
                console.error("Error in triggerExport:", error);
                alert("There was an error preparing the export: " + error.message);
            }
        };

        // Also set up the event listener for the button (backup approach)
        document.getElementById('confirmExport').addEventListener('click', function() {
            console.log("Export button clicked through event listener");
            triggerExport();
        });

        // Ensure the modal is properly initialized with Bootstrap 5
        const exportModal = document.getElementById('exportModal');
        exportModal.addEventListener('shown.bs.modal', function () {
            console.log("Modal opened");
        });

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'en',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth',
            },
            validRange: {
                start: '{{ date("Y") }}-01-01',
                end: '{{ date("Y", strtotime("+1 year")) }}-12-31',
            },
            dayCellDidMount: function (info) {
                const cell = info.el;
                let price = null;
                let eventName = null;
                let eventClass = '';

                // Original date from FullCalendar
                const originalDate = info.date;
                const originalDateStr = originalDate.toISOString().split('T')[0]; // 'YYYY-MM-DD'

                // Shift the event date by one day (adjust event related data only)
                const shiftedDate = new Date(originalDate);
                shiftedDate.setDate(shiftedDate.getDate() + 1); // Shift by 1 day
                const shiftedDateStr = shiftedDate.toISOString().split('T')[0]; // Get the shifted date

                console.log("Original Date from Calendar: ", originalDateStr);
                console.log("Shifted Date for Event: ", shiftedDateStr);

                // Check if the shifted date has any events from the backend data
                if (rateDates[shiftedDateStr]) {
                    const rate = rateDates[shiftedDateStr];
                    price = rate.price;
                    eventName = rate.event;

                    // Assign event color based on event type
                    if (rate.event_type === 'Blackout Date') {
                        eventClass = 'event-blackout';
                    } else if (rate.event_type === 'Fair Date') {
                        eventClass = 'event-fair';
                    } else if (rate.event_type === 'Season') {
                        eventClass = 'event-season';
                    }
                } else {
                    // Assign default price based on whether it's a weekend or weekday (original date)
                    // This logic matches how prices are determined in the calendar display (see dayCellDidMount)
                    const day = originalDate.getDay(); // 0 = Sunday, 6 = Saturday
                    const isWeekend = weekendDays.includes(day);
                    price = isWeekend ? weekendBasePrice : weekdayBasePrice;
                }

                // Apply event color if event exists; otherwise, apply weekend color if it's a weekend (based on original date)
                if (eventClass) {
                    cell.classList.add(eventClass); // Apply event color class for shifted date
                } else if (price) {
                    // Apply a lighter color for weekends without events
                    const day = originalDate.getDay();
                    if (day === 0 || day === 6) { // Sunday or Saturday
                        cell.classList.add('fc-day-sun', 'fc-day-sat');
                    }
                }

                // Display event name and price in the cell if available
                if (eventName) {
                    const eventNameElement = document.createElement('div');
                    eventNameElement.className = 'event-name';
                    eventNameElement.textContent = eventName;
                    cell.appendChild(eventNameElement);
                }
                if (price) {
                    const priceElement = document.createElement('div');
                    priceElement.className = 'price-container';
                    priceElement.textContent = `₹${price}`;
                    cell.appendChild(priceElement);
                }
            },
            height: 'auto',
            aspectRatio: 1.5,
            // Show 30 days at a time
            views: {
                dayGridMonth: {
                    visibleRange: function (currentDate) {
                        const startDate = currentDate;
                        const endDate = new Date(currentDate);
                        endDate.setDate(startDate.getDate() + 29); // 30-day view
                        return { start: startDate, end: endDate };
                    }
                }
            }
        });
        calendar.render();
    });
</script>

@endsection
