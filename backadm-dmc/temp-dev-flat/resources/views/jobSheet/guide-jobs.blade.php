@extends('layouts.layout')
@section('title', 'Guide Jobs')
@section('content')
<div class="container-fluid h-100 pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="rounded h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Guide Schedule</h5>
                    <div>
                        <button id="exportScheduleBtn" class="btn btn-success me-2" style="display: none;">
                            <i class="fas fa-file-excel"></i> Export List to Excel
                        </button>
                        <button id="exportCalendarBtn" class="btn btn-primary" style="display: none;">
                            <i class="fas fa-calendar-alt"></i> Export Job Sheet
                        </button>
                    </div>
                </div>
                <div class="row">
                    @if(in_array(Auth::user()->role_id, [1,7,14,97]))
                    <!-- Admin can see all fields -->
                    <div class="col-md-4 mb-3">
                        <label for="master_dmc_id" class="form-label">Master DMC</label>
                        <select class="form-select" id="master_dmc_id" name="master_dmc_id">
                            <option value="">Select Master DMC</option>
                            @foreach(\App\Models\User::where('role_id', 10)->get() as $masterDmc)
                                <option value="{{ $masterDmc->userId }}">{{ $masterDmc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="dmc_id" class="form-label">DMC</label>
                        <select class="form-select" id="dmc_id" name="dmc_id">
                            <option value="">Select DMC</option>
                            <!-- Will be populated via AJAX -->
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="guide_id" class="form-label">Guide</label>
                        <select class="form-select" id="guide_id" name="guide_id">
                            <option value="">Select Guide</option>
                            <!-- Will be populated via AJAX -->
                        </select>
                    </div>
                    @elseif(Auth::user()->role_id == 10 || Auth::user()->role_id == 26 || Auth::user()->role_id == 50 || Auth::user()->role_id == 98)
                    <!-- Master DMC can only see DMC and guide fields -->
                    <div class="col-md-6 mb-3">
                        <label for="dmc_id" class="form-label">DMC</label>
                        <select class="form-select" id="dmc_id" name="dmc_id">
                            <option value="">Select DMC</option>
                            @foreach($dmcs as $dmc)
                                <option value="{{ $dmc->userId }}">{{ $dmc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="guide_id" class="form-label">Guide</label>
                        <select class="form-select" id="guide_id" name="guide_id">
                            <option value="">Select Guide</option>
                            <!-- Will be populated via AJAX -->
                        </select>
                    </div>
                    @elseif(in_array(Auth::user()->role_id, [11, 34, 65, 99]))
                    <!-- DMC can only see guide field -->
                    <div class="col-md-12 mb-3">
                        <label for="guide_id" class="form-label">Guide</label>
                        <select class="form-select" id="guide_id" name="guide_id">
                            <option value="">Select Guide</option>
                            @foreach($dmcGuides as $guide)
                                <option value="{{ $guide->guide_id }}">{{ $guide->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>

                <!-- Guide Schedule Section -->
                <div id="guideScheduleSection" class="mt-4" style="display: none;">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Guide Schedule</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-responsive" id="scheduleTable">
                                    <thead>
                                        <tr>                                            
                                            <th>Order ID</th>
                                            <th>Type</th>
                                            <th>Pickup Date</th>
                                            <th>Pickup Time</th>
                                            <th>Pickup Location</th>
                                            <th>Customer</th>
                                            <th>Guide</th>
                                        </tr>
                                    </thead>
                                    <tbody id="scheduleTableBody">
                                        <!-- Will be populated via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script>
    $(document).ready(function() {
        // When Master DMC is selected (Admin view)
        $('#master_dmc_id').change(function() {
            const masterDmcId = $(this).val();
            $('#dmc_id').empty().append('<option value="">Select DMC</option>');
            $('#guide_id').empty().append('<option value="">Select Guide</option>');
            
            if (masterDmcId) {
                const url = "{{ route('get.dmcs', ':id') }}".replace(':id', masterDmcId);
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $.each(response.dmcs, function(key, dmc) {
                                $('#dmc_id').append(`<option value="${dmc.userId}">${dmc.name}</option>`);
                            });
                        }
                    },
                    error: function(error) {
                        console.error('Error fetching DMCs:', error);
                    }
                });
            }
        });
        
        // When DMC is selected (Admin or Master DMC view)
        $('#dmc_id').change(function() {
            const dmcId = $(this).val();
            $('#guide_id').empty().append('<option value="">Select Guide</option>');
            
            if (dmcId) {
                const url = "{{ route('get.guides', ':id') }}".replace(':id', dmcId);
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $.each(response.guides, function(key, guide) {
                                $('#guide_id').append(`<option value="${guide.guide_id}">${guide.name}</option>`);
                            });
                        }
                    },
                    error: function(error) {
                        console.error('Error fetching Guides:', error);
                    }
                });
            }
        });

        // When Guide is selected - fetch schedule
        $('#guide_id').change(function() {
            const guideId = $(this).val();
            
            if (guideId) {
                $('#guideScheduleSection').hide();
                $('#exportScheduleBtn').hide();
                $('#exportCalendarBtn').hide();
                const url = "{{ route('get.guide.schedule', ':id') }}".replace(':id', guideId);
                
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            // Populate the schedule table
                            const scheduleData = response.schedule;
                            let tableHTML = '';
                            
                            if (scheduleData.length > 0) {
                                scheduleData.forEach(function(item) {
                                                                        tableHTML += `<tr>                                        <td>${item.order_id || 'N/A'}</td>                                        <td>${item.type || 'N/A'}</td>                                        <td>${item.pickup_date || 'N/A'}</td>                                        <td>${item.pickup_time || 'N/A'}</td>                                        <td>${item.pickup_location || 'N/A'}</td>                                        <td>${item.customer_name || 'N/A'}<br><small>${item.customer_phone || 'N/A'}</small></td>                                        <td>${item.guide_name || 'N/A'}</td>                                    </tr>`;
                                });
                                
                                $('#scheduleTableBody').html(tableHTML);
                                $('#guideScheduleSection').show();
                                $('#exportScheduleBtn').show();
                                $('#exportCalendarBtn').show();
                                
                                // Store schedule data for Excel export
                                window.guideScheduleData = scheduleData;
                            } else {
                                $('#scheduleTableBody').html('<tr><td colspan="7" class="text-center">No schedule found for this guide</td></tr>');
                                $('#guideScheduleSection').show();
                            }
                        }
                    },
                    error: function(error) {
                        console.error('Error fetching Guide Schedule:', error);
                        $('#scheduleTableBody').html('<tr><td colspan="7" class="text-center">Error loading schedule</td></tr>');
                        $('#guideScheduleSection').show();
                    }
                });
            } else {
                $('#guideScheduleSection').hide();
                $('#exportScheduleBtn').hide();
                $('#exportCalendarBtn').hide();
            }
        });

        // Export to Excel button
        $('#exportScheduleBtn').click(function(e) {
            e.preventDefault();
            
            if (window.guideScheduleData && window.guideScheduleData.length > 0) {
                                // Format data for Excel export                const excelData = window.guideScheduleData.map(item => ({                    'Order ID': item.order_id || 'N/A',                    'Type': item.type || 'N/A',                    'Pickup Date': item.pickup_date || 'N/A',                    'Pickup Time': item.pickup_time || 'N/A',                    'Pickup Location': item.pickup_location || 'N/A',                    'Customer Name': item.customer_name || 'N/A',                    'Customer Phone': item.customer_phone || 'N/A',                    'Customer Email': item.customer_email || 'N/A',                    'Guide': item.guide_name || 'N/A',                    'Booking Type': item.booking_type || 'N/A',                    'Price': item.total_price || 'N/A'                }));
                
                // Create a workbook and worksheet
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.json_to_sheet(excelData);
                
                // Add the worksheet to the workbook
                XLSX.utils.book_append_sheet(wb, ws, "Guide Schedule");
                
                // Get guide name for filename
                const guideName = $('#guide_id option:selected').text().replace(/\s+/g, '_').toLowerCase();
                const fileName = `guide_schedule_${guideName}_${new Date().toISOString().slice(0,10)}.xlsx`;
                
                // Export to file
                XLSX.writeFile(wb, fileName);
            } else {
                alert('No schedule data to export');
            }
        });
        
        // Export Calendar to Excel button
        $('#exportCalendarBtn').click(function(e) {
            e.preventDefault();
            
            if (window.guideScheduleData && window.guideScheduleData.length > 0) {
                createAllMonthsCalendarExcel(window.guideScheduleData);
            } else {
                alert('No schedule data to export');
            }
        });
        
        // Export all months in one file, one sheet per month using ExcelJS
        async function createAllMonthsCalendarExcel(scheduleData) {
            // Get today's date at midnight
            const today = new Date();
            today.setHours(0,0,0,0);
            
            const workbook = new ExcelJS.Workbook();
            
            // 1. First build Booking Details sheet
            const detailsSheet = workbook.addWorksheet('Booking Details');
                        const detailsHeaders = [                'Order ID', 'Type', 'Service', 'Date', 'Time', 'Customer', 'Phone', 'Email',                'Guide', 'Location', 'Price', 'Passengers'            ];
            detailsSheet.addRow(detailsHeaders);
            
            // Style header
            detailsSheet.getRow(1).eachCell((cell) => {
                cell.font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 10 };
                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF4472C4' } };
                cell.alignment = { horizontal: 'center', vertical: 'middle' };
                cell.border = { top: {style:'thin'}, left: {style:'thin'}, bottom: {style:'thin'}, right: {style:'thin'} };
            });
            
            // Create a mapping of date+time to row numbers
            const dateTimeToRows = {};
            let detailsRowNum = 2;
            
            // Add all bookings as rows
            scheduleData.forEach((booking, index) => {
                // Create a key using date and time
                const dateKey = booking.pickup_date || 'N/A';
                const timeKey = booking.pickup_time || 'N/A';
                const dateTimeKey = `${dateKey}|${timeKey}`;
                
                // Add the booking details
                                const row = detailsSheet.addRow([                    booking.order_id || `Booking #${index+1}`,                    booking.type || 'N/A',                    booking.service_type || 'N/A',                    dateKey,                    timeKey,                    booking.customer_name || 'N/A',                    booking.customer_phone || 'N/A',                    booking.customer_email || 'N/A',                    booking.guide_name || 'N/A',                    booking.pickup_location || 'N/A',                    booking.total_price || 'N/A',                    booking.pax || 'N/A'                ]);
                
                // Store the row number for this date+time combination
                if (!dateTimeToRows[dateTimeKey]) {
                    dateTimeToRows[dateTimeKey] = [];
                }
                dateTimeToRows[dateTimeKey].push(detailsRowNum);
                
                detailsRowNum++;
            });
            
            // Set column widths for details sheet
            for (let i = 1; i <= detailsHeaders.length; i++) {
                detailsSheet.getColumn(i).width = 15;
            }
            
            // Find all unique months/years for future bookings
            const monthsYears = {};
            scheduleData.forEach(item => {
                let d = item.pickup_date;
                if (!d || d === 'N/A') return;
                
                let dateObj = new Date(d);
                if (isNaN(dateObj.getTime())) {
                    const parts = d.split(/[-\/]/);
                    if (parts.length === 3) {
                        dateObj = new Date(parts[2], parts[1] - 1, parts[0]);
                        if (isNaN(dateObj.getTime())) {
                            dateObj = new Date(parts[2], parts[0] - 1, parts[1]);
                        }
                    }
                }
                
                if (dateObj < today) return; // skip past dates
                
                if (!isNaN(dateObj.getTime())) {
                    const y = dateObj.getFullYear();
                    const m = dateObj.getMonth();
                    if (!monthsYears[y]) monthsYears[y] = {};
                    monthsYears[y][m] = true;
                }
            });
            
            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            
            // Now create calendar sheets
            Object.keys(monthsYears).sort().forEach(y => {
                Object.keys(monthsYears[y]).sort((a,b)=>a-b).forEach(m => {
                    const month = parseInt(m);
                    const year = parseInt(y);
                    const monthName = monthNames[month];
                    const daysInMonth = new Date(year, month + 1, 0).getDate();
                    
                    const worksheet = workbook.addWorksheet(`${monthName} ${year}`);
                    
                    // Build header row
                    const headerRow = ['Time'];
                    for (let day = 1; day <= daysInMonth; day++) headerRow.push(day.toString());
                    worksheet.addRow(headerRow);
                    
                    // Add time rows
                    for (let hour = 0; hour < 24; hour++) {
                        const row = [`${hour.toString().padStart(2, '0')}:00`];
                        for (let day = 1; day <= daysInMonth; day++) row.push('');
                        worksheet.addRow(row);
                    }
                    
                    // Style header row
                    worksheet.getRow(1).eachCell((cell, colNumber) => {
                        if (colNumber === 1) {
                            cell.value = 'Time';
                            cell.font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 8 };
                            cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF305496' } };
                        } else {
                            cell.font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 8 };
                            cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF4472C4' } };
                        }
                        cell.alignment = { horizontal: 'center', vertical: 'middle' };
                        cell.border = { top: {style:'thin'}, left: {style:'thin'}, bottom: {style:'thin'}, right: {style:'thin'} };
                    });
                    
                    // Style time column
                    for (let r = 2; r <= 25; r++) {
                        const cell = worksheet.getCell(r, 1);
                        cell.font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 8 };
                        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF595959' } };
                        cell.alignment = { horizontal: 'center', vertical: 'middle' };
                        cell.border = { top: {style:'thin'}, left: {style:'thin'}, bottom: {style:'thin'}, right: {style:'thin'} };
                    }
                    
                    // Add bookings with hyperlinks to details sheet
                    scheduleData.forEach(booking => {
                        if (booking.pickup_date === 'N/A' || !booking.pickup_time) return;
                        
                        let bookingDate;
                        try {
                            bookingDate = new Date(booking.pickup_date);
                            if (isNaN(bookingDate.getTime())) {
                                const parts = booking.pickup_date.split(/[-\/]/);
                                if (parts.length === 3) {
                                    bookingDate = new Date(parts[2], parts[1] - 1, parts[0]);
                                    if (isNaN(bookingDate.getTime())) {
                                        bookingDate = new Date(parts[2], parts[0] - 1, parts[1]);
                                    }
                                }
                            }
                        } catch (e) { return; }
                        
                        if (!bookingDate || isNaN(bookingDate.getTime()) || bookingDate < today) return;
                        if (bookingDate.getMonth() !== month || bookingDate.getFullYear() !== year) return;
                        
                        const day = bookingDate.getDate();
                        let hour = 0;
                        let originalTime = booking.pickup_time; // Store original time string
                        try {
                            let timeStr = booking.pickup_time;
                            let isPM = timeStr.toLowerCase().includes('pm');
                            let timeDigits = timeStr.match(/\d+/g);
                            if (timeDigits && timeDigits.length >= 1) {
                                hour = parseInt(timeDigits[0]);
                                if (isPM && hour < 12) hour += 12;
                                if (!isPM && hour === 12) hour = 0;
                            }
                        } catch (e) { return; }
                        
                        // ExcelJS rows/cols are 1-based, header is row 1, time 00:00 is row 2
                        const rowIdx = hour + 2;
                        const colIdx = day + 1;
                        const cell = worksheet.getCell(rowIdx, colIdx);
                        
                        // We set basic properties for all booked cells
                        cell.value = "Booked";
                        cell.font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 8 };
                        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFF0000' } };
                        cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
                        cell.border = { top: {style:'thin'}, left: {style:'thin'}, bottom: {style:'thin'}, right: {style:'thin'} };
                        
                        // Create key to find matching rows in details sheet
                        const dateTimeKey = `${booking.pickup_date}|${booking.pickup_time}`;
                        
                        // If we have rows matching this date/time
                        if (dateTimeToRows[dateTimeKey] && dateTimeToRows[dateTimeKey].length > 0) {
                            // Get the first row number for this date/time
                            const firstRowNum = dateTimeToRows[dateTimeKey][0];
                            
                            // Use Excel's native HYPERLINK formula directly
                            // This is the most reliable way to create internal links in Excel
                            cell.value = { formula: `HYPERLINK("#'Booking Details'!A${firstRowNum}","Booked")` };
                            
                            // Make it look like a hyperlink
                            cell.font = { 
                                bold: true, 
                                color: { argb: 'FFFFFFFF' }, 
                                size: 8,
                                underline: true 
                            };
                            
                            // Highlight all matching rows in the details sheet
                            dateTimeToRows[dateTimeKey].forEach(rowNum => {
                                const detailsRow = detailsSheet.getRow(rowNum);
                                detailsRow.eachCell(detailCell => {
                                    detailCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFFF2CC' } };
                                });
                                detailsRow.commit();
                            });
                        }
                        
                        worksheet.getColumn(colIdx).width = Math.max(worksheet.getColumn(colIdx).width || 4, 6);
                        worksheet.getRow(rowIdx).height = Math.max(worksheet.getRow(rowIdx).height || 20, 25);
                    });
                    
                    // Set column widths (double for day columns)
                    worksheet.getColumn(1).width = 8;
                    for (let i = 2; i <= daysInMonth + 1; i++) worksheet.getColumn(i).width = 6;
                    // Set row heights
                    worksheet.getRow(1).height = 25;
                    for (let i = 2; i <= 25; i++) worksheet.getRow(i).height = 20;
                    
                    // Freeze first row and column
                    worksheet.views = [{ state: 'frozen', xSplit: 1, ySplit: 1 }];
                });
            });
            
            // Save file
            const guideName = $('#guide_id option:selected').text();
            const fileGuideName = guideName.replace(/\s+/g, '_').toLowerCase();
            const fileName = `guide_calendar_${fileGuideName}_all_months.xlsx`;
            
            const buffer = await workbook.xlsx.writeBuffer();
            saveAs(new Blob([buffer]), fileName);
        }
    });
</script>
@endsection
