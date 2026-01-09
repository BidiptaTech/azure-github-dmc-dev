@extends('layouts.layout')
@section('title', 'Driver Jobs')

@section('styles')
<style>
    .driver-schedule-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 30px;
        color: white;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    .driver-schedule-header h4 {
        font-weight: 600;
        margin: 0;
        font-size: 28px;
    }
    
    .filter-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        margin-bottom: 25px;
    }
    
    .form-select, .form-control {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        padding: 12px 15px;
        transition: all 0.3s ease;
    }
    
    .form-select:focus, .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .export-buttons {
        display: flex;
        gap: 10px;
    }
    
    .btn-export {
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-export-success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }
    
    .btn-export-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(17, 153, 142, 0.3);
    }
    
    .btn-export-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .btn-export-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    
    .schedule-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }
    
    .nav-tabs {
        border: none;
        background: #f8f9fa;
        border-radius: 12px;
        padding: 8px;
        margin-bottom: 25px;
    }
    
    .nav-tabs .nav-item {
        flex: 1;
    }
    
    .nav-tabs .nav-link {
        border: none;
        border-radius: 8px;
        color: #6c757d;
        font-weight: 600;
        padding: 12px 20px;
        transition: all 0.3s ease;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .nav-tabs .nav-link:hover {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }
    
    .nav-tabs .nav-link.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    .nav-tabs .nav-link i {
        font-size: 16px;
    }
    
    .table {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .table {
        width: 100% !important;
        margin: 0 !important;
    }
    
    .table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
        padding: 15px 12px;
        border: none;
        white-space: nowrap;
    }
    
    .table thead th:first-child {
        border-top-left-radius: 10px;
    }
    
    .table thead th:last-child {
        border-top-right-radius: 10px;
    }
    
    .table tbody tr {
        transition: all 0.3s ease;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fe;
        transform: scale(1.01);
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    
    .table tbody td {
        padding: 15px 12px;
        vertical-align: middle;
        border: none;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .table tbody tr:last-child td:first-child {
        border-bottom-left-radius: 10px;
    }
    
    .table tbody tr:last-child td:last-child {
        border-bottom-right-radius: 10px;
    }
    
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #f8f9fe;
    }
    
    .dataTables_wrapper .dataTables_length {
        float: left;
    }
    
    .dataTables_wrapper .dataTables_info {
        padding-top: 1em;
        float: left;
    }
    
    .dataTables_wrapper .dataTables_paginate {
        float: right;
        padding-top: 1em;
    }
    
    .dt-buttons {
        margin-bottom: 15px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        float: left;
    }
    
    .dt-button {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        border: none !important;
        color: white !important;
        border-radius: 8px !important;
        padding: 8px 16px !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
        font-size: 13px !important;
    }
    
    .dt-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3) !important;
    }
    
    .dataTables_wrapper {
        width: 100% !important;
    }
    
    .dataTables_wrapper .dataTables_filter {
        float: right;
        text-align: right;
    }
    
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        padding: 8px 15px;
        transition: all 0.3s ease;
        width: 250px;
    }
    
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        outline: none;
    }
    
    .dataTables_wrapper .dataTables_length select {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        padding: 6px 12px;
        margin: 0 5px;
    }
    
    .page-item.active .page-link {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
    }
    
    .page-link {
        color: #667eea;
        border-radius: 8px;
        margin: 0 3px;
        transition: all 0.3s ease;
    }
    
    .page-link:hover {
        background-color: #f8f9fe;
        color: #667eea;
    }
    
    .badge {
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 11px;
    }
    
    .customer-info {
        display: flex;
        flex-direction: column;
    }
    
    .customer-name {
        font-weight: 600;
        color: #2d3748;
    }
    
    .customer-phone {
        color: #718096;
        font-size: 12px;
        margin-top: 2px;
    }
    
    .serial-number {
        font-weight: 700;
        color: #667eea;
        background: rgba(102, 126, 234, 0.1);
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }
    
    /* Ensure tables use full width */
    .dataTables_wrapper .row {
        width: 100%;
        margin: 0;
    }
    
    .dataTables_wrapper .row > div {
        padding: 0 12px;
    }
    
    .table-responsive {
        overflow-x: visible !important;
    }
    
    /* Full width for table container */
    #scheduleTabsContent {
        width: 100%;
    }
    
    .tab-pane {
        width: 100%;
    }
    
    @media (max-width: 768px) {
        .driver-schedule-header {
            padding: 20px;
        }
        
        .driver-schedule-header h4 {
            font-size: 22px;
        }
        
        .export-buttons {
            flex-direction: column;
            width: 100%;
        }
        
        .btn-export {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid h-100 pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <!-- Header -->
            <div class="driver-schedule-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h4 class="mb-1">
                            <i class="ri-steering-2-fill me-2"></i>Driver Schedule Management
                        </h4>
                        <p class="mb-0 opacity-90">Manage and track driver assignments efficiently</p>
                    </div>
                    <div class="export-buttons" style="display: none;" id="exportButtonsContainer">
                        <button id="exportScheduleBtn" class="btn btn-export btn-export-success">
                            <i class="ri-file-excel-2-line"></i> Export to Excel
                        </button>
                        <button id="exportCalendarBtn" class="btn btn-export btn-export-primary">
                            <i class="ri-calendar-2-line"></i> Export Job Sheet
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Filter Card -->
            <div class="filter-card">
                <div class="row">
                    @if(in_array(Auth::user()->role_id, [1,2,7,8,14,15,106]))
                    <!-- Admin can see all fields -->
                    <div class="col-lg-4 col-md-6 mb-3">
                        <label for="master_dmc_id" class="form-label"><i class="ri-admin-line me-1"></i>Master DMC</label>
                        <select class="form-select" id="master_dmc_id" name="master_dmc_id">
                            <option value="">Select Master DMC</option>
                            @foreach(\App\Models\User::where('role_id', 10)->get() as $masterDmc)
                                <option value="{{ $masterDmc->userId }}">{{ $masterDmc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-3">
                        <label for="dmc_id" class="form-label"><i class="ri-building-2-line me-1"></i>DMC</label>
                        <select class="form-select" id="dmc_id" name="dmc_id">
                            <option value="">Select DMC</option>
                            <!-- Will be populated via AJAX -->
                        </select>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-3">
                        <label for="driver_id" class="form-label"><i class="ri-steering-2-line me-1"></i>Driver</label>
                        <select class="form-select" id="driver_id" name="driver_id">
                            <option value="">Select Driver</option>
                            <!-- Will be populated via AJAX -->
                        </select>
                    </div>
                    @elseif(Auth::user()->role_id == 10 || Auth::user()->role_id == 26 || Auth::user()->role_id == 50 || Auth::user()->role_id == 98)
                    <!-- Master DMC can only see DMC and driver fields -->
                    <div class="col-md-6 mb-3">
                        <label for="dmc_id" class="form-label"><i class="ri-building-2-line me-1"></i>DMC</label>
                        <select class="form-select" id="dmc_id" name="dmc_id">
                            <option value="">Select DMC</option>
                            @foreach($dmcs as $dmc)
                                <option value="{{ $dmc->userId }}">{{ $dmc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="driver_id" class="form-label"><i class="ri-steering-2-line me-1"></i>Driver</label>
                        <select class="form-select" id="driver_id" name="driver_id">
                            <option value="">Select Driver</option>
                            <!-- Will be populated via AJAX -->
                        </select>
                    </div>
                    @elseif(in_array(Auth::user()->role_id, [11,34,66,108, 128, 131, 132, 134, 135, 137, 138]))
                    <!-- DMC can only see driver field -->
                    <div class="col-md-12 mb-3">
                        <label for="driver_id" class="form-label"><i class="ri-steering-2-line me-1"></i>Driver</label>
                        <select class="form-select" id="driver_id" name="driver_id">
                            <option value="">Select Driver</option>
                            @foreach($dmcDrivers as $driver)
                                <option value="{{ $driver->driver_id }}">{{ $driver->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Driver Schedule Section -->
            <div id="driverScheduleSection" style="display: none;">
                <div class="schedule-card">
                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs" id="scheduleTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past" type="button" role="tab" aria-controls="past" aria-selected="false">
                                <i class="ri-history-line"></i> Past
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="ongoing-tab" data-bs-toggle="tab" data-bs-target="#ongoing" type="button" role="tab" aria-controls="ongoing" aria-selected="false">
                                <i class="ri-time-line"></i> Ongoing
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab" aria-controls="upcoming" aria-selected="true">
                                <i class="ri-calendar-event-line"></i> Upcoming
                            </button>
                        </li>
                    </ul>

                    <!-- Tabs Content -->
                    <div class="tab-content" id="scheduleTabsContent">
                        <!-- Past Tab -->
                        <div class="tab-pane fade" id="past" role="tabpanel" aria-labelledby="past-tab">
                                    <div class="table-responsive">
                                        <table class="datatables-past table table-striped table-bordered" id="pastScheduleTable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Type</th>
                                                    <th>Pickup Date</th>
                                                    <th>Pickup Time</th>
                                                    <th>Pickup Location</th>
                                                    <th>Dropoff Location</th>
                                                    <th>Customer</th>
                                                    <th>Vehicle</th>
                                                    <th>Tour ID</th>
                                                </tr>
                                            </thead>
                                            <tbody id="pastScheduleTableBody">
                                                <!-- Will be populated via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Ongoing Tab -->
                                <div class="tab-pane fade" id="ongoing" role="tabpanel" aria-labelledby="ongoing-tab">
                                    <div class="table-responsive">
                                        <table class="datatables-ongoing table table-striped table-bordered" id="ongoingScheduleTable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Type</th>
                                                    <th>Pickup Date</th>
                                                    <th>Pickup Time</th>
                                                    <th>Pickup Location</th>
                                                    <th>Dropoff Location</th>
                                                    <th>Customer</th>
                                                    <th>Vehicle</th>
                                                    <th>Tour ID</th>
                                                </tr>
                                            </thead>
                                            <tbody id="ongoingScheduleTableBody">
                                                <!-- Will be populated via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                        <!-- Upcoming Tab -->
                        <div class="tab-pane fade show active" id="upcoming" role="tabpanel" aria-labelledby="upcoming-tab">
                                    <div class="table-responsive">
                                        <table class="datatables-upcoming table table-striped table-bordered" id="upcomingScheduleTable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Type</th>
                                                    <th>Pickup Date</th>
                                                    <th>Pickup Time</th>
                                                    <th>Pickup Location</th>
                                                    <th>Dropoff Location</th>
                                                    <th>Customer</th>
                                                    <th>Vehicle</th>
                                                    <th>Tour ID</th>
                                                </tr>
                                            </thead>
                                            <tbody id="upcomingScheduleTableBody">
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
<!-- DataTable CSS & JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">

<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.colVis.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

<script>
    const authUserRoleId = {{ auth()->user()->roleid ?? 'null' }};
    $(document).ready(function() {
        // When Master DMC is selected (Admin view)
        function fetchDMCs(masterDmcId) {
            $('#dmc_id').empty().append('<option value="">Select DMC</option>');
            $('#driver_id').empty().append('<option value="">Select Driver</option>');

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
        }
        $('#master_dmc_id').change(function() {
            const masterDmcId = $(this).val();
            fetchDMCs(masterDmcId);
        });

        function fetchDrivers(dmcId){
            $('#driver_id').empty().append('<option value="">Select Driver</option>');
            
            if (dmcId) {
                const url = "{{ route('get.drivers', ':id') }}".replace(':id', dmcId);
                $.ajax({
                    url: url,
                    type: "GET",
                    data: { dmc_id: dmcId },
                    dataType: 'json',
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $.each(response.drivers, function(key, driver) {
                                $('#driver_id').append(`<option value="${driver.driver_id}">${driver.name}</option>`);
                            });
                        }
                    },
                    error: function(error) {
                        console.error('Error fetching Drivers:', error);
                    }
                });
            }
        }

        // When DMC is selected (Admin or Master DMC view)
        $('#dmc_id').change(function() {
            const dmcId = $(this).val();
            fetchDrivers(dmcId);
        });

        // When Driver is selected - fetch schedule
        $('#driver_id').change(function() {
            const driverId = $(this).val();
            console.log(driverId);
            
            if (driverId) {
                $('#driverScheduleSection').hide();
                $('#exportScheduleBtn').hide();
                $('#exportCalendarBtn').hide();
                const url = "{{ route('get.driver.schedule', ':driverId') }}".replace(':driverId', driverId);
                
                $.ajax({
                    url: url,
                    data: { driver_id: driverId },
                    dataType: 'json',
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            // Populate the schedule table
                            const scheduleData = response.schedule;
                            
                            if (scheduleData.length > 0) {
                                // Filter data by date
                                const today = new Date();
                                today.setHours(0, 0, 0, 0);
                                
                                const pastData = [];
                                const ongoingData = [];
                                const upcomingData = [];
                                
                                scheduleData.forEach(function(item) {
                                    const pickupDate = parsePickupDate(item.pickup_date);
                                    
                                    if (pickupDate) {
                                        if (pickupDate < today) {
                                            pastData.push(item);
                                        } else if (pickupDate.toDateString() === today.toDateString()) {
                                            ongoingData.push(item);
                                        } else {
                                            upcomingData.push(item);
                                        }
                                    } else {
                                        // If date can't be parsed, put in upcoming
                                        upcomingData.push(item);
                                    }
                                });
                                
                                // Populate Past table
                                populateTable('pastScheduleTableBody', pastData);
                                
                                // Populate Ongoing table
                                populateTable('ongoingScheduleTableBody', ongoingData);
                                
                                // Populate Upcoming table
                                populateTable('upcomingScheduleTableBody', upcomingData);
                                
                                $('#driverScheduleSection').show();
                                $('#exportButtonsContainer').show();
                                
                                // Store schedule data for Excel export
                                window.driverScheduleData = scheduleData;
                            } else {
                                $('#pastScheduleTableBody').html('<tr><td colspan="9" class="text-center">No schedule found</td></tr>');
                                $('#ongoingScheduleTableBody').html('<tr><td colspan="9" class="text-center">No schedule found</td></tr>');
                                $('#upcomingScheduleTableBody').html('<tr><td colspan="9" class="text-center">No schedule found</td></tr>');
                                $('#driverScheduleSection').show();
                            }
                        }
                    },
                    error: function(error) {
                        console.error('Error fetching Driver Schedule:', error);
                        $('#pastScheduleTableBody').html('<tr><td colspan="9" class="text-center">Error loading schedule</td></tr>');
                        $('#ongoingScheduleTableBody').html('<tr><td colspan="9" class="text-center">Error loading schedule</td></tr>');
                        $('#upcomingScheduleTableBody').html('<tr><td colspan="9" class="text-center">Error loading schedule</td></tr>');
                        $('#driverScheduleSection').show();
                    }
                });
            } else {
                $('#driverScheduleSection').hide();
                $('#exportButtonsContainer').hide();
            }
        });

        // Export to Excel button
        $('#exportScheduleBtn').click(function(e) {
            e.preventDefault();
            
            if (window.driverScheduleData && window.driverScheduleData.length > 0) {
                // Format data for Excel export
                const excelData = window.driverScheduleData.map(item => ({
                    'Type': item.type,
                    'Pickup Date': item.pickup_date,
                    'Pickup Time': item.pickup_time,
                    'Pickup Location': item.pickup_location,
                    'Dropoff Location': item.dropoff_location,
                    'Customer Name': item.customer_name,
                    'Customer Phone': item.customer_phone,
                    'Customer Email': item.customer_email,
                    'Vehicle': item.vehicle_name,
                    'Booking Type': item.booking_type,
                    'Price': item.total_price,
                    'Status': item.status
                }));
                
                // Create a workbook and worksheet
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.json_to_sheet(excelData);
                
                // Add the worksheet to the workbook
                XLSX.utils.book_append_sheet(wb, ws, "Driver Schedule");
                
                // Get driver name for filename
                const driverName = $('#driver_id option:selected').text().replace(/\s+/g, '_').toLowerCase();
                const fileName = `driver_schedule_${driverName}_${new Date().toISOString().slice(0,10)}.xlsx`;
                
                // Export to file
                XLSX.writeFile(wb, fileName);
            } else {
                alert('No schedule data to export');
            }
        });
        
        // Export Calendar to Excel button
        $('#exportCalendarBtn').click(function(e) {
            e.preventDefault();
            
            if (window.driverScheduleData && window.driverScheduleData.length > 0) {
                createAllMonthsCalendarExcel(window.driverScheduleData);
            } else {
                alert('No schedule data to export');
            }
        });
        
        // Helper function to parse pickup date in various formats
        function parsePickupDate(dateStr) {
            if (!dateStr || dateStr === 'N/A') return null;
            
            try {
                // Try format: Sep 28 - Sep 30, 2025 (take first date)
                const rangeMatch = dateStr.match(/(\w{3})\s+(\d{1,2}).*?(\d{4})/);
                if (rangeMatch) {
                    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    const month = monthNames.indexOf(rangeMatch[1]);
                    const day = parseInt(rangeMatch[2]);
                    const year = parseInt(rangeMatch[3]);
                    
                    if (month !== -1) {
                        return new Date(year, month, day);
                    }
                }
                
                // Try ISO format: 2025-09-10
                const isoMatch = dateStr.match(/(\d{4})-(\d{2})-(\d{2})/);
                if (isoMatch) {
                    return new Date(parseInt(isoMatch[1]), parseInt(isoMatch[2]) - 1, parseInt(isoMatch[3]));
                }
                
                // Try standard date parsing
                const date = new Date(dateStr);
                if (!isNaN(date.getTime())) {
                    return date;
                }
            } catch (e) {
                console.error('Error parsing date:', dateStr, e);
            }
            
            return null;
        }
        
        // DataTable instances
        let pastDataTable = null;
        let ongoingDataTable = null;
        let upcomingDataTable = null;
        
        // Helper function to populate table with DataTables
        function populateTable(tableBodyId, data) {
            const tableId = tableBodyId.replace('TableBody', 'Table');
            const tableClass = tableBodyId.replace('ScheduleTableBody', '');
            
            // Destroy existing DataTable if it exists
            if (tableId === 'pastScheduleTable' && pastDataTable) {
                pastDataTable.destroy();
                pastDataTable = null;
            } else if (tableId === 'ongoingScheduleTable' && ongoingDataTable) {
                ongoingDataTable.destroy();
                ongoingDataTable = null;
            } else if (tableId === 'upcomingScheduleTable' && upcomingDataTable) {
                upcomingDataTable.destroy();
                upcomingDataTable = null;
            }
            
            let tableHTML = '';
            
            if (data.length > 0) {
                data.forEach(function(item, index) {
                    tableHTML += `<tr>
                        <td><span class="serial-number">${index + 1}</span></td>
                        <td><span class="badge bg-primary">${item.type || 'N/A'}</span></td>
                        <td><i class="ri-calendar-line me-1 text-primary"></i>${item.pickup_date || 'N/A'}</td>
                        <td><i class="ri-time-line me-1 text-success"></i>${item.pickup_time || 'N/A'}</td>
                        <td><i class="ri-map-pin-line me-1 text-danger"></i>${item.pickup_location || 'N/A'}</td>
                        <td><i class="ri-map-pin-2-line me-1 text-info"></i>${item.dropoff_location || 'N/A'}</td>
                        <td>
                            <div class="customer-info">
                                <span class="customer-name"><i class="ri-user-3-line me-1"></i>${item.customer_name || 'N/A'}</span>
                                <span class="customer-phone"><i class="ri-phone-line me-1"></i>${item.customer_phone || ''}</span>
                            </div>
                        </td>
                        <td><i class="ri-car-line me-1 text-warning"></i>${item.vehicle_name || 'N/A'}</td>
                        <td><span class="badge bg-success"><i class="ri-route-line me-1"></i>${item.tour_id || 'N/A'}</span></td>
                    </tr>`;
                });
            } else {
                tableHTML = '<tr><td colspan="9" class="text-center">No schedule found</td></tr>';
            }
            
            $('#' + tableBodyId).html(tableHTML);
            
            // Initialize DataTable after populating data
            if (data.length > 0) {
                const dataTableOptions = {
                    responsive: true,
                    autoWidth: false,
                    scrollX: false,
                    dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>' +
                         '<"row"<"col-sm-12"tr>>' +
                         '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                    buttons: [
                        'copy',
                        'csv',
                        'excel',
                        'pdf',
                        'print'
                    ],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search...",
                    },
                    lengthMenu: [10, 25, 50, 100],
                    pageLength: 10,
                    order: [[0, 'asc']],
                    columnDefs: [
                        { width: "4%", targets: 0 },    // Serial number
                        { width: "9%", targets: 1 },    // Type
                        { width: "11%", targets: 2 },   // Pickup Date
                        { width: "9%", targets: 3 },    // Pickup Time
                        { width: "14%", targets: 4 },   // Pickup Location
                        { width: "14%", targets: 5 },   // Dropoff Location
                        { width: "16%", targets: 6 },   // Customer
                        { width: "13%", targets: 7 },   // Vehicle
                        { width: "10%", targets: 8 }    // Tour ID
                    ]
                };
                
                if (tableId === 'pastScheduleTable') {
                    pastDataTable = $('.datatables-past').DataTable(dataTableOptions);
                } else if (tableId === 'ongoingScheduleTable') {
                    ongoingDataTable = $('.datatables-ongoing').DataTable(dataTableOptions);
                } else if (tableId === 'upcomingScheduleTable') {
                    upcomingDataTable = $('.datatables-upcoming').DataTable(dataTableOptions);
                }
            }
        }
        
        // Export all months in one file, one sheet per month using ExcelJS
        async function createAllMonthsCalendarExcel(scheduleData) {
            // Get today's date at midnight
            const today = new Date();
            today.setHours(0,0,0,0);
            
            const workbook = new ExcelJS.Workbook();
            
            // 1. First build Booking Details sheet
            const detailsSheet = workbook.addWorksheet('Booking Details');
            const detailsHeaders = [
                'Type', 'Service', 'Date', 'Time', 'Customer', 'Phone', 'Email',
                'Vehicle', 'Pickup', 'Dropoff', 'Price', 'Passengers', 'Children', 'Status'
            ];
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
                const row = detailsSheet.addRow([
                    booking.type || 'N/A',
                    booking.service_type || 'N/A',
                    dateKey,
                    timeKey,
                    booking.customer_name || 'N/A',
                    booking.customer_phone || 'N/A',
                    booking.customer_email || 'N/A',
                    booking.vehicle_name || 'N/A',
                    booking.pickup_location || 'N/A',
                    booking.dropoff_location || 'N/A',
                    booking.total_price || 'N/A',
                    booking.pax || 'N/A',
                    booking.child || 'N/A',
                    booking.status || 'N/A'
                ]);
                
                // Store the row number for this date+time combination
                if (!dateTimeToRows[dateTimeKey]) {
                    dateTimeToRows[dateTimeKey] = [];
                }
                dateTimeToRows[dateTimeKey].push(detailsRowNum);
                
                detailsRowNum++;
            });
            
            console.log('Date/Time mapping:', dateTimeToRows);
            
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
            const driverName = $('#driver_id option:selected').text();
            const fileDriverName = driverName.replace(/\s+/g, '_').toLowerCase();
            const fileName = `driver_calendar_${fileDriverName}_all_months.xlsx`;
            
            const buffer = await workbook.xlsx.writeBuffer();
            saveAs(new Blob([buffer]), fileName);
        }
    });
</script>
@endsection
