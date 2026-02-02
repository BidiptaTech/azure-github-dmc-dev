@extends('layouts.layout')
@section('content')

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css" rel="stylesheet">

<style>
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

    /* Assign Guide: view (read-only text + pen) vs edit (dropdown) */
    .assign-guide-cell {
        min-width: 180px;
    }
    .assign-guide-view {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        min-height: 38px;
    }
    .assign-guide-text {
        flex: 1;
        padding: 6px 0;
        color: #333;
    }
    .assign-guide-text.empty {
        color: #999;
        font-style: italic;
    }
    .assign-guide-edit-btn {
        flex-shrink: 0;
        padding: 4px 8px;
        color: #6777ef;
        border: 1px solid #e2e5ec;
        background: #fff;
        border-radius: 4px;
        cursor: pointer;
    }
    .assign-guide-edit-btn:hover {
        background: #f8f9fa;
        color: #5568d3;
    }
    .assign-guide-edit {
        display: none;
        min-width: 180px;
    }
    .assign-guide-edit.is-active {
        display: block;
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h5 class="mb-4">Tour Guide Job Sheet</h5>
        <!-- Alert message container -->
        <div id="message-wrapper">
            <x-alert />
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <form id="guideJobsheetForm" method="POST" action="{{ route('jobsheet.store.guide') }}">
                    @csrf
                    <div class="row align-items-center">
                        
                        <!-- Date Input -->
                        <div class="col-md-5">
                            <label for="dateSelect" class="form-label"><strong>Date</strong><span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="dateSelect" name="date" required>
                        </div>
                    </div>
                    <input type="hidden" id="dmc_id" name="dmc_id" value="{{$dmcId}}">
                </form>
            </div>
        </div>

        <!-- Tour Guide Orders Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Tour Guide Orders</h5>
                <button id="exportOrdersBtn" class="btn btn-success" style="display: none;">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tourOrdersTable">
                        <thead>
                            <tr>
                                <th>Tour ID</th>
                                <th>Order Type</th>
                                <th>Pickup Time</th>
                                <th>Pickup Location</th>
                                <th>Tour Type</th>
                                <th>Guide</th>
                                <th>Assign Guide</th>
                                <!-- Hidden columns for now -->
                                <!-- <th>Booking Type</th>
                                <th>Total Price</th>
                                <th>Pax</th>
                                <th>Status</th> -->
                            </tr>
                        </thead>
                        <tbody id="tourOrdersTableBody">
                            <!-- Will be populated via AJAX -->
                        </tbody>
                    </table>
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
const getGuidesUrl = "{{ route('get.guides', ['dmcId' => ':dmcId']) }}";
const updateDriverVehicleAssignmentUrl = "{{ route('update.driver.vehicle.assignment') }}";
const getOrdersByDateUrl = "{{ route('get.orders.by.date', ['date' => ':date']) }}";

// Initialize data from controller
var initialOrders = {!! json_encode($orders ?? []) !!};
var initialGuides = {!! json_encode($guides ?? []) !!};
var dataTableInitialized = false; // Track if DataTable is initialized



$(document).ready(function() {
    let datePicker = null;
    
    // Calculate tomorrow's date
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomorrowStr = tomorrow.toISOString().split('T')[0];

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
        console.log("initialOrders = ", initialOrders);
        if (typeof initialOrders !== 'undefined' && initialOrders.length > 0) {
            let tableHTML = '';
            
            initialOrders.forEach(function(item, index) {
                console.log("Full item structure:", item);
                console.log("item.tour:", item.tour);
                console.log("item.tour?.id:", item.tour?.id);
                console.log("item.tour?.tour_id:", item.tour?.tour_id);
                // Handle data as array or object (flexibility for different data structures)
                const orderData = item.data || {};
                let dataItem;
                
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
                        <td>${item.type || 'N/A'}</td>
                        <td>${dataItem.entrytime || 'N/A'}</td>
                        <td>${dataItem.entrypickup || 'N/A'}</td>
                        <td>${dataItem.type || 'N/A'}</td>
                        <td>${(function() {
                            if (item.OrderGuide) {
                                const guide = item.OrderGuide;
                                const languages = guide.languages && guide.languages.length > 0 
                                    ? ' (' + guide.languages.map(lang => lang.language).join(', ') + ')'
                                    : '';
                                return guide.name + ' - ' + (guide.government_license_no || 'N/A') + languages;
                            }
                            return 'N/A';
                        })()}</td>
                        <td class="assign-guide-cell">
                            <div class="assign-guide-view">
                                <span class="assign-guide-text ${!(item.assigned_guide_id) ? 'empty' : ''}">${(function() {
                                    if (!item.assigned_guide_id) return 'Not Assigned';
                                    const g = initialGuides.find(gr => gr.guide_id == item.assigned_guide_id);
                                    if (g) {
                                        const lang = g.languages && g.languages.length ? ' (' + g.languages.map(l => l.language).join(', ') + ')' : '';
                                        return g.name + lang;
                                    }
                                    return 'Guide #' + item.assigned_guide_id;
                                })()}</span>
                                <button type="button" class="assign-guide-edit-btn" title="Edit guide" aria-label="Edit guide"><i class="fas fa-pen"></i></button>
                            </div>
                            <div class="assign-guide-edit">
                                <select class="form-control guide-select" 
                                    name="guide_id[${index}]" 
                                    data-order-id="${item.booking_id || item.id || ''}" 
                                    data-tour-id="${item.tour_id_numeric || ''}"
                                    data-order-type="${item.type || ''}"
                                    data-entry-time="${dataItem.entrytime || ''}"
                                    data-entrypickup="${dataItem.entrypickup || ''}"
                                    data-type="${item.type || ''}">
                                    <option value="">Select Guide</option>
                                    ${(function() {
                                        let options = '';
                                        if (initialGuides.length) {
                                            initialGuides.forEach(guide => {
                                                const isSelected = item.assigned_guide_id && (guide.guide_id == item.assigned_guide_id);
                                                const languages = guide.languages && guide.languages.length > 0 
                                                    ? ' (' + guide.languages.map(lang => lang.language).join(', ') + ')'
                                                    : '';
                                                options += `<option ${isSelected ? 'selected' : ''} value="${guide.guide_id}">${guide.name}${languages}</option>`;
                                            });
                                        }
                                        return options;
                                    })()}
                                </select>
                            </div>
                            <input type="hidden" name="order_id[${index}]" value="${item.id || ''}">
                            <input type="hidden" name="tour_id[${index}]" id="tour_id[${index}]" value="${item.tour_id || ''}">
                        </td>
                    </tr>`;
            });
            
            $('#tourOrdersTableBody').html(tableHTML);
            $('#exportOrdersBtn').show();
            
            // Don't init Select2 on hidden dropdowns; init on pen click
            
            // Initialize DataTable
            initializeDataTable();
        } else {
            $('#tourOrdersTableBody').html('<tr><td colspan="7" class="text-center">No orders found</td></tr>');
            $('#exportOrdersBtn').hide();
        }
    }

    // Function to load orders by date
    function loadOrdersByDate(date) {
        // Clean up any existing DataTable
        cleanupDataTable();
        
        if (!date) {
            $('#tourOrdersTableBody').html('<tr><td colspan="7" class="text-center">Please select a date</td></tr>');
            $('#exportOrdersBtn').hide();
            return;
        }

        // Show loading indicator
        $('#tourOrdersTableBody').html('<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading data...</td></tr>');

        fetch(getOrdersByDateUrl.replace(':date', date) + '?type=guide', {
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
        .then(function(response) {
                if (response.success) {
                    const orders = response.data;
                    let tableHTML = '';
                    console.log("orders = ", orders);
                    
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
                                    <td>${item.type || 'N/A'}</td>
                                    <td>${dataItem.entrytime || 'N/A'}</td>
                                    <td>${dataItem.entrypickup || 'N/A'}</td>
                                    <td>${dataItem.type || 'N/A'}</td>
                                    <td>${(function() {
                                        if (item.OrderGuide) {
                                            const guide = item.OrderGuide;
                                            const languages = guide.languages && guide.languages.length > 0 
                                                ? ' (' + guide.languages.map(lang => lang.language).join(', ') + ')'
                                                : '';
                                            return guide.name + ' - ' + (guide.government_license_no || 'N/A') + languages;
                                        }
                                        return 'N/A';
                                    })()}</td>
                                    <td class="assign-guide-cell">
                                        <div class="assign-guide-view">
                                            <span class="assign-guide-text ${!(item.assigned_guide_id) ? 'empty' : ''}">${(function() {
                                                if (!item.assigned_guide_id) return 'Not Assigned';
                                                const g = response.guides && response.guides.find(gr => gr.guide_id == item.assigned_guide_id);
                                                if (g) {
                                                    const lang = g.languages && g.languages.length ? ' (' + g.languages.map(l => l.language).join(', ') + ')' : '';
                                                    return g.name + (g.government_license_no ? ' - ' + g.government_license_no : '') + lang;
                                                }
                                                return 'Guide #' + item.assigned_guide_id;
                                            })()}</span>
                                            <button type="button" class="assign-guide-edit-btn" title="Edit guide" aria-label="Edit guide"><i class="fas fa-pen"></i></button>
                                        </div>
                                        <div class="assign-guide-edit">
                                            <select class="form-control guide-select" 
                                                name="guide_id[${index}]" 
                                                data-order-id="${item.booking_id || item.id || ''}" 
                                                data-tour-id="${item.tour_id_numeric || ''}"
                                                data-order-type="${item.type || ''}"
                                                data-entry-time="${dataItem.entrytime || ''}"
                                                data-entrypickup="${dataItem.entrypickup || ''}"
                                                data-type="${item.type || ''}">
                                                <option value="">Select Guide</option>
                                                ${(function() {
                                                    let options = '';
                                                    if (response.guides && response.guides.length) {
                                                        response.guides.forEach(guide => {
                                                            const isSelected = item.assigned_guide_id && (guide.guide_id == item.assigned_guide_id);
                                                            const languages = guide.languages && guide.languages.length > 0 
                                                                ? ' (' + guide.languages.map(lang => lang.language).join(', ') + ')'
                                                                : '';
                                                            options += `<option ${isSelected ? 'selected' : ''} value="${guide.guide_id}">${guide.name} - ${guide.government_license_no}${languages}</option>`;
                                                        });
                                                    }
                                                    return options;
                                                })()}
                                            </select>
                                        </div>
                                        <input type="hidden" name="order_id[${index}]" value="${item.booking_id || ''}">
                                        <input type="hidden" name="tour_id[${index}]" value="${item.tour_id || ''}">
                                    </td>
                                </tr>`;
                        });
                        
                        $('#tourOrdersTableBody').html(tableHTML);
                        $('#exportOrdersBtn').show();
                        
                        // Don't init Select2 on hidden dropdowns; init on pen click
                        
                        // Initialize DataTable
                        initializeDataTable();
                    } else {
                        $('#tourOrdersTableBody').html('<tr><td colspan="7" class="text-center">No orders found for this date</td></tr>');
                        $('#exportOrdersBtn').hide();
                    }
                } else {
                    const errorMessage = response.message || 'Error loading orders';
                    console.error('Error:', errorMessage);
                    showAlert('error', errorMessage);
                    $('#tourOrdersTableBody').html('<tr><td colspan="7" class="text-center">Error loading orders</td></tr>');
                    $('#exportOrdersBtn').hide();
                }
        })
        .catch(error => {
            console.error('Error fetching orders by date:', error);
            const errorMessage = error.message || 'Error fetching orders';
            showAlert('error', errorMessage);
            $('#tourOrdersTableBody').html('<tr><td colspan="7" class="text-center">Error loading orders</td></tr>');
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
                        { orderable: false, targets: [6] }  // Disable sorting on guide select column
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
    
    // Function to initialize Select2 on guide dropdowns
    function initializeSelect2() {
        try {
            // Destroy existing Select2 instances first
            $('.guide-select').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
            });
            
            // Initialize Select2 on guide dropdowns
            $('.guide-select').select2({
                placeholder: "Select Guide",
                allowClear: true,
                width: '100%',
                dropdownParent: $('#tourOrdersTable').parent()
            });
            
            console.log("Select2 initialized successfully");
        } catch (e) {
            console.error("Select2 initialization error:", e);
        }
    }

    // Initialize Flatpickr for date input FIRST
    datePicker = flatpickr("#dateSelect", {
        dateFormat: "Y-m-d",
        disableMobile: "true",
        defaultDate: tomorrowStr,
        onChange: function(selectedDates, dateStr) {
            // Load orders based on selected date
            loadOrdersByDate(dateStr);
        }
    });
    
    // Set the date value explicitly
    setTimeout(function() {
        if (datePicker) {
            datePicker.setDate(tomorrowStr);
        }
        $('#dateSelect').val(tomorrowStr);
        console.log("Date set to:", tomorrowStr, "Input value:", $('#dateSelect').val());
    }, 100);
    
    // Then load the table with initial data from controller
    initializeTable();

    // Pen icon: show dropdown for Assign Guide, then collapse back to text on close
    $(document).on('click', '.assign-guide-edit-btn', function() {
        const $btn = $(this);
        const $cell = $btn.closest('.assign-guide-cell');
        const $view = $cell.find('.assign-guide-view');
        const $edit = $cell.find('.assign-guide-edit');
        const $select = $cell.find('.guide-select').first();
        $view.hide();
        $edit.addClass('is-active').show();
        if (!$select.hasClass('select2-hidden-accessible')) {
            $select.select2({
                placeholder: "Select Guide",
                allowClear: true,
                width: '100%',
                dropdownParent: $('#tourOrdersTable').parent()
            });
            $select.on('select2:close', function() {
                const selectedText = $select.find('option:selected').text();
                $cell.find('.assign-guide-text').text(selectedText || 'Not Assigned').toggleClass('empty', !$select.val());
                $edit.removeClass('is-active').hide();
                $view.show();
            });
        }
        $select.select2('open');
    });

    // Handle guide selection change
    $(document).on('change', '.guide-select', function() {
        const $select = $(this);
        const guideId = $select.val();
        const orderId = $select.data('order-id');
        const orderType = $select.data('order-type');
        const entryTime = $select.data('entry-time');
        const entryPickup = $select.data('entrypickup');
        const type = $select.data('type');
        const tourId = $select.data('tour-id');
        const date = $('#dateSelect').val();
        const dmcId = $('#dmc_id').val();
        
        console.log('=== Guide Selection Debug ===');
        console.log('Guide change data:', { guideId, orderId, orderType, entryTime, entryPickup, type, tourId, date, dmcId });
        console.log('Select element:', $select[0]);
        console.log('All data attributes:', $select.data());
        console.log('Parent row HTML:', $select.closest('tr').html());
        
        // Validate required fields
        if (!orderType || !type || !entryTime) {
            console.error('Missing required data:', { orderType, type, entryTime });
            showAlert('error', 'Missing required data. Please refresh the page and try again.');
            return;
        }
        
        if (!tourId || tourId === 'undefined' || tourId === '') {
            console.error('Invalid tour ID:', tourId);
            showAlert('error', 'Invalid tour ID. Please refresh the page and try again.');
            return;
        }

        // Prepare form data - matching the driver/vehicle assignment pattern
        const formData = new FormData();
        formData.append('order_type', orderType || '');
        formData.append('entry_time', entryTime || '');
        formData.append('entrypickup', entryPickup || '');
        formData.append('type', type || '');
        formData.append('guide_id', guideId || '');
        formData.append('tour_id', tourId || '');
        formData.append('date', date || '');
        formData.append('dmc_id', dmcId || '');
        formData.append('order_id', orderId || '');
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        console.log('FormData being sent:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        // Make fetch API call to update the guide assignment
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
                showAlert('success', 'Guide assigned successfully');
            } else {
                showAlert('error', data.message || 'Failed to assign guide');
            }
        })
        .catch(error => {
            console.error('Error updating guide assignment:', error);
            showAlert('error', error.message || 'Error updating guide assignment');
        });
    });

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
            if (cells.length < 7) {
                return; // Skip incomplete rows
            }
            
            // Extract data directly from the table cells
            const tourId = $(cells[0]).text().trim();
            const orderType = $(cells[1]).text().trim();
            const pickupTime = $(cells[2]).text().trim();
            const pickupLocation = $(cells[3]).text().trim();
            const tourType = $(cells[4]).text().trim();
            const guide = $(cells[5]).text().trim();
            
            // Get selected guide from the dropdown
            const guideSelect = $(cells[6]).find('.guide-select');
            const assignedGuide = guideSelect.find('option:selected').text().trim() || 'Not Assigned';
            
            // Add to excel data
            excelData.push({
                'Tour ID': tourId,
                'Order Type': orderType,
                'Pickup Time': pickupTime,
                'Pickup Location': pickupLocation,
                'Tour Type': tourType,
                'Guide': guide,
                'Assigned Guide': assignedGuide
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
            XLSX.utils.book_append_sheet(wb, ws, "Guide Jobsheet");
            
            // Get date for filename
            const selectedDate = $('#dateSelect').val();
            const fileName = `guide_jobsheet_${selectedDate}.xlsx`;
            
            // Export to file
            XLSX.writeFile(wb, fileName);
            
            // Show success message
            showAlert('success', 'Guide jobsheet exported successfully!');
        } else {
            showAlert('warning', 'No data to export');
        }
    });

    // Handle form submission
    $('#guideJobsheetForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('date', $('#dateSelect').val());
        formData.append('dmc_id', $('#dmc_id').val());
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        const dateValue = $('#dateSelect').val();

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
                // Reload guide orders to reflect the changes
                loadOrdersByDate(dateValue);
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error creating guide jobsheet:', error);
            showAlert('error', 'An error occurred while creating the guide jobsheet: ' + error.message);
        });
    });
});
</script>
@endsection
