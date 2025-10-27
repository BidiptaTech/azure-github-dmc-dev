@extends('layouts.layout')
@section('content')

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css" rel="stylesheet">

<style>
    /* Simple visual improvements without changing functionality */
    .card {
        border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.07);
        margin-bottom: 25px;
        border: none;
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #efefef;
        padding: 15px 20px;
    }
    
    .card-body {
        padding: 20px;
    }
    
    .card-title {
        color: #3a3a3a;
        font-weight: 600;
        margin-bottom: 0;
    }
    
    .form-control {
        border-color: #e2e5ec;
        padding: 8px 12px;
        border-radius: 4px;
    }
    
    .form-control:focus {
        border-color: #6777ef;
        box-shadow: 0 0 0 0.2rem rgba(103, 119, 239, 0.1);
    }
    
    .btn-primary {
        background-color: #6777ef;
        border-color: #6777ef;
    }
    
    .btn-primary:hover {
        background-color: #5a68d1;
        border-color: #5a68d1;
    }
    
    .btn-success {
        background-color: #47c363;
        border-color: #47c363;
    }
    
    .btn-success:hover {
        background-color: #3bb557;
        border-color: #3bb557;
    }
    
    .form-label {
        font-weight: 500;
        color: #4a4a4a;
    }
    
    .table {
        border: 1px solid #f2f2f2;
    }
    
    .table thead th {
        background-color: #f8f9fa;
        color: #4a4a4a;
        font-weight: 600;
        border-bottom: 2px solid #efefef;
        padding: 12px 10px;
    }
    
    .table td {
        padding: 12px 10px;
        vertical-align: middle;
    }
    
    .table tbody tr:hover {
        background-color: #f9f9f9;
    }
    
    /* Better Select2 styling */
    .select2-container--default .select2-selection--single {
        border-color: #e2e5ec;
        height: 38px;
        line-height: 38px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 12px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    
    /* Select2 in table cells */
    .table td .select2-container {
        width: 100% !important;
        min-width: 150px;
    }
    
    /* Select2 dropdown styling */
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #6777ef;
    }
    
    .select2-search--dropdown .select2-search__field {
        border-color: #e2e5ec;
        padding: 6px 12px;
    }
    
    .select2-dropdown {
        border-color: #e2e5ec;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    /* Custom alert styling */
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
        position: relative;
    }
    
    .alert-success {
        background-color: #47c363;
        border-color: #3bb557;
        color: white;
    }
    
    .alert-danger {
        background-color: #e74c3c;
        border-color: #c0392b;
        color: white;
    }
    
    .alert-info {
        background-color: #3498db;
        border-color: #2980b9;
        color: white;
    }
    
    .alert-warning {
        background-color: #f39c12;
        border-color: #e67e22;
        color: white;
    }
    
    /* Message wrapper for dynamic alerts */
    #message-wrapper {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        width: 300px;
    }
</style>

<div class="content-wrapper">
    
    <div class="container-xxl flex-grow-1 container-p-y">
    <h5 class="mb-4">Driver Job Sheet</h5>
        <!-- Alert message container -->
        <div id="message-wrapper">
            <x-alert />
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <form id="driverJobsheetForm" method="POST" action="{{ route('jobsheet.store.driver') }}">
                    @csrf
                    <div class="row align-items-center">
                        <!-- Tour ID Dropdown -->
                        <input type="hidden" name="dmc_id" id="dmc_id" value="{{ $dmcId }}">
                        
                        <input type="hidden" name="tour_id" id="tour_id" value="">
                        <!-- Date Input -->
                        <div class="col-md-5">
                            <label for="dateSelect" class="form-label"><strong>Date</strong><span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="dateSelect" name="date" required>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tour Orders Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Job sheet Data</h5>
                <button id="exportOrdersBtn" class="btn btn-success" style="display: none;">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <form id="assignDriverForm" method="POST" action="{{ route('jobsheet.store.driver.assignments') }}">
                        @csrf
                        <input type="hidden" name="tourId" id="hiddenTourId" value="">
                        <input type="hidden" name="date" id="hiddenDate" value="">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tourOrdersTable">
                                <thead>
                                    <tr>
                                        <th>Tour ID</th>
                                        <th>Pickup Time</th>
                                        <th>Pickup Location</th>
                                        <th>Pickup Zone</th>
                                        <th>Dropoff Location</th>
                                        <th>Dropoff Zone</th>
                                        <th>Assign Driver</th>
                                        <th>Assign Vehicle</th>
                                        <th>Order Type</th>
                                        <th>Tour Type</th>
                                    </tr>
                                </thead>
                                <tbody id="tourOrdersTableBody">
                                    <tr>
                                        <td colspan="10" class="text-center">Loading data...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js"></script>

<script>
// Define route URLs using Blade
const getOrdersByDateUrl = "{{ route('get.orders.by.date', ['date' => ':date']) }}";
const updateDriverVehicleAssignmentUrl = "{{ route('update.driver.vehicle.assignment') }}";

// Initialize data from controller
var initialOrders = {!! json_encode($orders ?? []) !!};
var initialDrivers = {!! json_encode($drivers ?? []) !!};
var initialVehicles = {!! json_encode($vehicles ?? []) !!};
var dataTableInitialized = false; // Track if DataTable is initialized

$(document).ready(function() {
    let datePicker = null;
    
    // Custom alert function
    function showAlert(type, message) {
        // Create the alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <p>${message}</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Add to the DOM
        const messageWrapper = document.getElementById('message-wrapper');
        messageWrapper.appendChild(alertDiv);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 300);
        }, 5000);
    }
    
    // Initialize table with orders data from controller
    function initializeTable() {
        // Check if we have initial data from controller
        if (typeof initialOrders !== 'undefined' && initialOrders.length > 0) {
            let tableHTML = '';
            
            initialOrders.forEach(function(item, index) {
                // Handle data as array or object (flexibility for different data structures)
                const orderData = item.data || {};
                let dataItem;
                console.log("item === ", item);
                // Check if data is array or object and extract the right data
                if (Array.isArray(orderData) && orderData.length > 0) {
                    dataItem = orderData[0];
                } else if (typeof orderData === 'object') {
                    dataItem = orderData;
                } else {
                    dataItem = {};
                }
                
                tableHTML += `
                    <tr>
                        <td>${item.tour_id || 'N/A'}</td>
                        <td>${dataItem.entrytime || 'N/A'}</td>
                        <td>${dataItem.entrypickup || 'N/A'}</td>
                        <td>${item.pickup_zone || 'N/A'}</td>
                        <td>${dataItem.entrydropoff || 'N/A'}</td>
                        <td>${item.dropoff_zone || 'N/A'}</td>
                        <td>
                            <select class="form-control driver-select" 
                                name="driver_id[${index}]" 
                                data-order-id="${item.id || ''}" 
                                data-tour-id="${item.tour_id || ''}"
                                data-order-type="${item.type || ''}"
                                data-entry-time="${dataItem.entrytime || ''}"
                                data-entrypickup="${dataItem.entrypickup || ''}"
                                data-entrydropoff="${dataItem.entrydropoff || ''}"
                                data-type="${dataItem.type || ''}">
                                <option value="">Select Driver</option>
                                ${(function() {
                                    let options = '';
                                    if (initialDrivers.length) {
                                        initialDrivers.forEach(driver => {
                                            const isSelected = item.assigned_driver_id && (driver.driver_id == item.assigned_driver_id);
                                            options += `<option ${isSelected ? 'selected' : ''} value="${driver.driver_id}">${driver.name}</option>`;
                                        });
                                    }
                                    return options;
                                })()}
                            </select>
                            <input type="hidden" name="order_id[${index}]" value="${item.id || ''}">
                            <input type="hidden" name="tour_id[${index}]" value="${item.tour_id || ''}">
                        </td>
                        <td>
                            <select class="form-control vehicle-select" 
                                name="vehicle_id[${index}]" 
                                data-order-id="${item.booking_id || ''}" 
                                data-tour-id="${item.tour_id || ''}"
                                data-order-type="${item.type || ''}"
                                data-entry-time="${dataItem.entrytime || ''}"
                                data-entrypickup="${dataItem.entrypickup || ''}"
                                data-entrydropoff="${dataItem.entrydropoff || ''}"
                                data-type="${dataItem.type || ''}">
                                <option value="">Select Vehicle</option>
                                ${(function() {
                                    let options = '';
                                    if (initialVehicles.length) {
                                        initialVehicles.forEach(vehicle => {
                                            const isSelected = item.assigned_vehicle_id && (vehicle.vehicle_id == item.assigned_vehicle_id);
                                            options += `<option ${isSelected ? 'selected' : ''} value="${vehicle.vehicle_id}">${vehicle.vehicle_name}</option>`;
                                        });
                                    }
                                    return options;
                                })()}
                            </select>
                        </td>
                        <td>${item.type || 'N/A'}</td>
                        <td>${dataItem.type || 'N/A'}</td>
                    </tr>`;
            });
            
            $('#tourOrdersTableBody').html(tableHTML);
            $('#exportOrdersBtn').show();
            
            // Initialize Select2 for driver and vehicle dropdowns
            initializeSelect2();
            
            // Initialize DataTable
            initializeDataTable();
        } else {
            $('#tourOrdersTableBody').html('<tr><td colspan="10" class="text-center">No orders found</td></tr>');
            $('#exportOrdersBtn').hide();
        }
    }

    // First load the table with initial data from controller
    initializeTable();
    
    // Calculate tomorrow's date
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    // Initialize Flatpickr for date input
    datePicker = flatpickr("#dateSelect", {
        dateFormat: "Y-m-d",
        disableMobile: "true",
        defaultDate: tomorrow,
        onChange: function(selectedDates, dateStr) {
            // Load orders based on selected date
            loadOrdersByDate(dateStr);
        },
        enabled: true
    });

    // Function to load orders by date
    function loadOrdersByDate(date) {
        // Clean up any existing DataTable
        cleanupDataTable();
        
        if (!date) {
            $('#tourOrdersTableBody').html('<tr><td colspan="10" class="text-center">Please select a date</td></tr>');
            $('#exportOrdersBtn').hide();
            return;
        }

        // Set hidden date field for the assignment form
        $('#hiddenDate').val(date);
        
        // Show loading indicator
        $('#tourOrdersTableBody').html('<tr><td colspan="10" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading data...</td></tr>');

        fetch(getOrdersByDateUrl.replace(':date', date), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(response => {
            if (response.success) {
                const orders = response.data;
                let tableHTML = '';
                
                if (orders && orders.length > 0) {
                    orders.forEach(function(item, index) {
                        //The data is already parsed by Laravel, no need to parse again
                        const orderData = item.data || {};
                        
                        // Handle data as array or object
                        let dataItem;
                        if (Array.isArray(orderData) && orderData.length > 0) {
                            dataItem = orderData[0];
                        } else if (typeof orderData === 'object') {
                            dataItem = orderData;
                        } else {
                            dataItem = {};
                        }
                        
                        tableHTML += `
                            <tr>
                                <td>${item.tour_id || 'N/A'}</td>
                                <td>${dataItem.entrytime || 'N/A'}</td>
                                <td>${dataItem.entrypickup || 'N/A'}</td>
                                <td>${item.pickup_zone || 'N/A'}</td>
                                <td>${dataItem.entrydropoff || 'N/A'}</td>
                                <td>${item.dropoff_zone || 'N/A'}</td>
                                <td>
                                    <select class="form-control driver-select" 
                                        name="driver_id[${index}]" 
                                        data-order-id="${item.id || ''}" 
                                        data-tour-id="${item.tour_id || ''}"
                                        data-order-type="${item.type || ''}"
                                        data-entry-time="${dataItem.entrytime || ''}"
                                        data-entrypickup="${dataItem.entrypickup || ''}"
                                        data-entrydropoff="${dataItem.entrydropoff || ''}"
                                        data-type="${dataItem.type || ''}">
                                        <option value="">Select Driver</option>
                                        ${(function() {
                                            let options = '';
                                            if (response.drivers && response.drivers.length) {
                                                response.drivers.forEach(driver => {
                                                    const isSelected = item.assigned_driver_id && (driver.driver_id == item.assigned_driver_id);
                                                    options += `<option ${isSelected ? 'selected' : ''} value="${driver.driver_id}">${driver.name} - ${driver.license_no} </option>`;
                                                });
                                            }
                                            return options;
                                        })()}
                                    </select>
                                    <input type="hidden" name="order_id[${index}]" value="${item.id || ''}">
                                    <input type="hidden" name="tour_id[${index}]" value="${item.tour_id || ''}">
                                </td>
                                <td>
                                    <select class="form-control vehicle-select" 
                                        name="vehicle_id[${index}]" 
                                        data-order-id="${item.id || ''}" 
                                        data-tour-id="${item.tour_id || ''}"
                                        data-order-type="${item.type || ''}"
                                        data-entry-time="${dataItem.entrytime || ''}"
                                        data-entrypickup="${dataItem.entrypickup || ''}"
                                        data-entrydropoff="${dataItem.entrydropoff || ''}"
                                        data-type="${dataItem.type || ''}">
                                        <option value="">Select Vehicle</option>
                                        ${(function() {
                                            let options = '';
                                            if (response.vehicles && response.vehicles.length) {
                                                response.vehicles.forEach(vehicle => {
                                                    const isSelected = item.assigned_vehicle_id && (vehicle.vehicle_id == item.assigned_vehicle_id);
                                                    options += `<option ${isSelected ? 'selected' : ''} value="${vehicle.vehicle_id}">${vehicle.vehicle_name}</option>`;
                                                });
                                            }
                                            return options;
                                        })()}
                                    </select>
                                </td>
                                <td>${item.type || 'N/A'}</td>
                                <td>${dataItem.type || 'N/A'}</td>
                            </tr>`;
                    });
                    
                    $('#exportOrdersBtn').show();
                    $('#tourOrdersTableBody').html(tableHTML);
                    
                    // Initialize Select2 for driver and vehicle dropdowns
                    initializeSelect2();
                    
                    // Initialize DataTable after content is loaded
                    initializeDataTable();
                } else {
                    $('#tourOrdersTableBody').html('<tr><td colspan="10" class="text-center">No orders found for this date</td></tr>');
                    $('#exportOrdersBtn').hide();
                }
            } else {
                const errorMessage = response.message || 'Error loading orders';
                console.error('Error:', errorMessage);
                showAlert('error', errorMessage);
                $('#tourOrdersTableBody').html('<tr><td colspan="10" class="text-center">Error loading orders</td></tr>');
                $('#exportOrdersBtn').hide();
            }
        })
        .catch(error => {
            console.error('Error fetching orders by date:', error);
            const errorMessage = error.message || 'Error fetching orders';
            showAlert('error', errorMessage);
            $('#tourOrdersTableBody').html('<tr><td colspan="10" class="text-center">Error loading orders</td></tr>');
            $('#exportOrdersBtn').hide();
        });
    }
    
    // Function to safely clean up DataTable
    function cleanupDataTable() {
        try {
            // If DataTable exists and is initialized
            if (dataTableInitialized) {
                // First remove all event handlers to prevent memory leaks
                $('#tourOrdersTable').off();
                
                // Try standard destroy
                try {
                    $('#tourOrdersTable').DataTable().destroy();
                } catch (e) {
                    console.log("Standard destroy failed, using alternative cleanup");
                }
                
                // Remove all DataTables related classes
                $('#tourOrdersTable')
                    .removeClass('dataTable')
                    .find('thead th')
                    .removeClass('sorting sorting_asc sorting_desc');
                
                // Reset the flag
                dataTableInitialized = false;
            }
        } catch (e) {
            console.error("Error cleaning up DataTable:", e);
        }
    }
    
    // Function to initialize DataTable
    function initializeDataTable() {
        try {
            // Clean up first
            cleanupDataTable();
            
            // Check if we have actual data rows (not just a message)
            const hasData = $('#tourOrdersTableBody tr').length > 0 && 
                           !$('#tourOrdersTableBody tr td[colspan]').length;
            
            if (hasData) {
                // Initialize DataTable with minimal options
                $('#tourOrdersTable').DataTable({
                    paging: true,
                    ordering: true,
                    info: true,
                    searching: true,
                    columnDefs: [
                        { orderable: false, targets: [6, 7] }  // Disable sorting on Assign Driver and Assign Vehicle columns
                    ]
                });
                
                // Set the flag
                dataTableInitialized = true;
                console.log("DataTable initialized successfully");
            } else {
                console.log("No data available, skipping DataTable initialization");
            }
        } catch (e) {
            console.error("DataTable initialization error:", e);
        }
    }
    
    // Function to initialize Select2 on driver and vehicle dropdowns
    function initializeSelect2() {
        try {
            // Destroy existing Select2 instances first
            $('.driver-select').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
            });
            $('.vehicle-select').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
            });
            
            // Initialize Select2 on driver dropdowns
            $('.driver-select').select2({
                placeholder: "Select Driver",
                allowClear: true,
                width: '100%',
                dropdownParent: $('#tourOrdersTable').parent()
            });
            
            // Initialize Select2 on vehicle dropdowns
            $('.vehicle-select').select2({
                placeholder: "Select Vehicle",
                allowClear: true,
                width: '100%',
                dropdownParent: $('#tourOrdersTable').parent()
            });
            
            console.log("Select2 initialized successfully");
        } catch (e) {
            console.error("Select2 initialization error:", e);
        }
    }

    // Export to Excel functionality
    $('#exportOrdersBtn').click(function(e) {
        e.preventDefault();
        
        // Build fresh export data directly from the table DOM to ensure accuracy
        const excelData = [];
        
        $('#tourOrdersTable tbody tr').each(function() {
            const $row = $(this);
            
            // Skip rows that are DataTables placeholders or have no data
            if ($row.find('td[colspan]').length > 0) {
                return; // Skip this row
            }
            
            const cells = $row.find('td');
            if (cells.length < 10) {
                return; // Skip incomplete rows
            }
            
            // Extract data directly from the table cells
            const tourId = $(cells[0]).text().trim();
            const pickupTime = $(cells[1]).text().trim();
            const pickupLocation = $(cells[2]).text().trim();
            const pickupZone = $(cells[3]).text().trim();
            const dropoffLocation = $(cells[4]).text().trim();
            const dropoffZone = $(cells[5]).text().trim();
            const orderType = $(cells[8]).text().trim();
            const tourType = $(cells[9]).text().trim();
            
            // Get selected driver and vehicle from the dropdowns
            const driverSelect = $(cells[6]).find('.driver-select');
            const vehicleSelect = $(cells[7]).find('.vehicle-select');
            
            const assignedDriver = driverSelect.find('option:selected').text().trim() || 'Not Assigned';
            const assignedVehicle = vehicleSelect.find('option:selected').text().trim() || 'Not Assigned';
            
            // Add to excel data
            excelData.push({
                'Tour ID': tourId,
                'Pickup Time': pickupTime,
                'Pickup Location': pickupLocation,
                'Pickup Zone': pickupZone,
                'Dropoff Location': dropoffLocation,
                'Dropoff Zone': dropoffZone,
                'Assigned Driver': assignedDriver,
                'Assigned Vehicle': assignedVehicle,
                'Order Type': orderType,
                'Tour Type': tourType
            });
        });
        
        if (excelData.length > 0) {
            // Create a workbook and worksheet
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(excelData);
            
            // Auto-size columns
            const colWidths = [];
            Object.keys(excelData[0]).forEach((key, index) => {
                const maxLength = Math.max(
                    key.length,
                    ...excelData.map(row => String(row[key]).length)
                );
                colWidths[index] = { wch: Math.min(maxLength + 2, 50) };
            });
            ws['!cols'] = colWidths;
            
            // Add the worksheet to the workbook
            XLSX.utils.book_append_sheet(wb, ws, "Driver Jobsheet");
            
            // Get date for filename
            const selectedDate = $('#dateSelect').val();
            const fileName = `driver_jobsheet_${selectedDate}.xlsx`;
            
            // Export to file
            XLSX.writeFile(wb, fileName);
            
            // Show success message
            showAlert('success', 'Driver jobsheet exported successfully!');
        } else {
            showAlert('warning', 'No data to export');
        }
    });

    // No tour selection handler needed anymore

    // Handle main form submission (creating a jobsheet)
    $('#driverJobsheetForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('date', $('#dateSelect').val());
        formData.append('dmc_id', $('#dmc_id').val());
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        fetch($(this).attr('action'), {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                // Load orders to show the latest data
                loadOrdersByDate($('#dateSelect').val());
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error creating jobsheet:', error);
            showAlert('error', 'An error occurred while creating the jobsheet: ' + error.message);
        });
    });

    // Handle assignments form submission
    $('#assignDriverForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);

        fetch($(this).attr('action'), {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                // Refresh the orders to show the latest assignments
                loadOrdersByDate($('#hiddenDate').val());
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error saving assignments:', error);
            showAlert('error', 'An error occurred while saving assignments: ' + error.message);
        });
    });

    // Handle driver selection change
    $(document).on('change', '.driver-select', function() {
        const $select = $(this);
        const driverId = $select.val();
        const orderType = $select.data('order-type');
        console.log("orderType = ", orderType);
        const entryTime = $select.data('entry-time');
        const entryPickup = $select.data('entrypickup');
        const entryDropoff = $select.data('entrydropoff');
        const type = $select.data('type');
        const orderId = $select.data('order-id');
        const vehicleId = $select.closest('tr').find('.vehicle-select').val();
        const tourId = $select.data('tour-id') || $('#hiddenTourId').val();
        const date = $('#hiddenDate').val();
        const dmcId = $('#dmc_id').val();
        
        // Prepare form data
        const formData = new FormData();
        formData.append('order_type', orderType);
        formData.append('entry_time', entryTime);
        formData.append('entrypickup', entryPickup);
        formData.append('entrydropoff', entryDropoff);
        formData.append('type', type);
        formData.append('driver_id', driverId);
        formData.append('vehicle_id', vehicleId);
        formData.append('tour_id', tourId);
        formData.append('date', date);
        formData.append('dmc_id', dmcId);
        formData.append('order_id', orderId);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        // Make fetch API call
        fetch('{{ route("update.driver.vehicle.assignment") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            // First parse the response to JSON
            return response.json().then(data => {
                // If response is not ok, throw error with the actual message from server
                if (!response.ok) {
                    throw new Error(data.message || `HTTP error! status: ${response.status}`);
                }
                return data;
            });
        })
        .then(data => {
            if (data.success) {
                let vehicle = data.vehicle;
                console.log("vehicle = ", vehicle?.vehicle_name);
                // Find the vehicle select in the same row as the driver select
                const $vehicleSelect = $select.closest('tr').find('.vehicle-select');
                // If we have a vehicle from the response, select it
                if (vehicle) {
                     // First try with vehicle_id
                    $vehicleSelect.find('option').each(function() {
                        if ($(this).val() === vehicle.vehicle_id.toString() || 
                            $(this).text() === vehicle.vehicle_name) {
                            $vehicleSelect.val($(this).val());
                            return false; // break the loop
                        }
                    });
                    $vehicleSelect.trigger('change');
                }
                
                showAlert('success', 'Driver assigned successfully');
            } else {
                showAlert('error', data.message || 'Failed to assign driver');
            }
        })
        .catch(error => {
            console.error('Error updating driver assignment:', error);
            showAlert('error', error.message || 'Error updating driver assignment');
        });
    });

    // Handle vehicle selection change
    $(document).on('change', '.vehicle-select', function() {
        const $select = $(this);
        const vehicleId = $select.val();
        const orderType = $select.data('order-type');
        const entryTime = $select.data('entry-time');
        const entryPickup = $select.data('entrypickup');
        const entryDropoff = $select.data('entrydropoff');
        const type = $select.data('type');
        const orderId = $select.data('order-id');
        const driverId = $select.closest('tr').find('.driver-select').val();
        const tourId = $select.data('tour-id') || $('#hiddenTourId').val();
        const date = $('#hiddenDate').val();
        const dmcId = $('#dmc_id').val();
        
        // Prepare form data
        const formData = new FormData();
        formData.append('order_type', orderType);
        formData.append('entry_time', entryTime);
        formData.append('entrypickup', entryPickup);
        formData.append('entrydropoff', entryDropoff);
        formData.append('type', type);
        formData.append('driver_id', driverId);
        formData.append('vehicle_id', vehicleId);
        formData.append('tour_id', tourId);
        formData.append('date', date);
        formData.append('dmc_id', dmcId);
        formData.append('order_id', orderId);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        // Make fetch API call
        fetch('{{ route("update.driver.vehicle.assignment") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            // First parse the response to JSON
            return response.json().then(data => {
                // If response is not ok, throw error with the actual message from server
                if (!response.ok) {
                    throw new Error(data.message || `HTTP error! status: ${response.status}`);
                }
                return data;
            });
        })
        .then(data => {
            if (data.success) {
                showAlert('success', 'Vehicle assigned successfully');
            } else {
                showAlert('error', data.message || 'Failed to assign vehicle');
            }
        })
        .catch(error => {
            console.error('Error updating vehicle assignment:', error);
            showAlert('error', error.message || 'Error updating vehicle assignment');
        });
    });

});
</script>
@endsection
