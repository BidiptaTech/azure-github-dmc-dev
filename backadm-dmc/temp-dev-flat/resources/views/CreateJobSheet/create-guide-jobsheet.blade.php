@extends('layouts.layout')
@section('content')

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css" rel="stylesheet">

<style>
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
const updateDriverVehicleAssignmentUrl = "{{ route('update.guide.jobsheet') }}";
const getOrdersByDateUrl = "{{ route('get.orders.by.date', ['date' => ':date']) }}";

// Initialize data from controller
var initialOrders = {!! json_encode($orders ?? []) !!};
var initialGuides = {!! json_encode($guides ?? []) !!};
var dataTableInitialized = false; // Track if DataTable is initialized

$(document).ready(function() {
    let datePicker = null;
    // Store the current tour guide orders data for export
    let tourGuideOrdersData = [];

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
            tourGuideOrdersData = []; // Reset the export data
            
            initialOrders.forEach(function(item, index) {
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
                // Store for export
                tourGuideOrdersData.push({
                    tour_id: item.tour_id || 'N/A',
                    order_type: item.type || 'N/A',
                    pickup_time: dataItem.entrytime || 'N/A',
                    pickup_location: dataItem.entrypickup || 'N/A',
                    tour_type: dataItem.type || 'N/A',
                    assigned_guide: initialGuides.find(g => dataItem.guide_id == g.guide_id)?.name || 'Not Assigned',
                    customer_name: dataItem.fullName || 'N/A',
                    customer_phone: dataItem.customer_phone || dataItem.phone || 'N/A',
                    customer_email: dataItem.customer_email || dataItem.email || 'N/A',
                    total_price: dataItem.totalPrice || 'N/A',
                    pax: dataItem.pax || 'N/A'
                });
                
                tableHTML += `
                    <tr>
                        <td>${item.tour_id || 'N/A'}</td>
                        <td>${item.type || 'N/A'}</td>
                        <td>${dataItem.entrytime || 'N/A'}</td>
                        <td>${dataItem.entrypickup || 'N/A'}</td>
                        <td>${dataItem.type || 'N/A'}</td>
                        <td>
                            <select class="form-control guide-select" name="guide_id[${index}]" data-order-id="${item.booking_id || ''}" data-tour-id="${item.tour_id || ''}">
                                <option value="">Select Guide</option>
                                ${(function() {
                                    let options = '';
                                    if (initialGuides.length) {
                                        initialGuides.forEach(guide => {
                                            const isSelected = dataItem.guide_id == guide.guide_id;
                                            options += `<option ${isSelected ? 'selected' : ''} value="${guide.guide_id}">${guide.name}</option>`;
                                        });
                                    }
                                    return options;
                                })()}
                            </select>
                            <input type="hidden" name="order_id[${index}]" value="${item.id || ''}">
                            <input type="hidden" name="tour_id[${index}]" id="tour_id[${index}]" value="${item.tour_id || ''}">
                        </td>
                    </tr>`;
            });
            
            $('#tourOrdersTableBody').html(tableHTML);
            $('#exportOrdersBtn').show();
            
            // Initialize DataTable
            initializeDataTable();
        } else {
            $('#tourOrdersTableBody').html('<tr><td colspan="6" class="text-center">No orders found</td></tr>');
            $('#exportOrdersBtn').hide();
        }
    }

    // Function to load orders by date
    function loadOrdersByDate(date) {
        // Clean up any existing DataTable
        cleanupDataTable();
        
        if (!date) {
            $('#tourOrdersTableBody').html('<tr><td colspan="6" class="text-center">Please select a date</td></tr>');
            $('#exportOrdersBtn').hide();
            tourGuideOrdersData = [];
            return;
        }

        // Set hidden date field
        $('#dateSelect').val(date);
        
        // Show loading indicator
        $('#tourOrdersTableBody').html('<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading data...</td></tr>');

        $.ajax({
            url: getOrdersByDateUrl.replace(':date', date) + '?type=guide',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const orders = response.data;
                    let tableHTML = '';
                    tourGuideOrdersData = []; // Reset the export data
                    
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
                            
                            // Store data for export
                            const $select = $(`select[data-order-id="${item.booking_id}"]`);
                            const selectedGuideName = $select.find('option:selected').text() || 'Not Assigned';
                            tourGuideOrdersData.push({
                                order_id: item.booking_id || item.id || 'N/A',
                                tour_id: item.tour_id || 'N/A',
                                order_type: item.type || 'N/A',
                                pickup_time: dataItem.entrytime || 'N/A',
                                pickup_location: dataItem.entrypickup || 'N/A',
                                tour_type: dataItem.type || 'N/A',
                                assigned_guide: selectedGuideName,
                                customer_name: dataItem.fullName || 'N/A',
                                customer_phone: dataItem.customer_phone || dataItem.phone || 'N/A',
                                customer_email: dataItem.customer_email || dataItem.email || 'N/A'
                            });
                            
                            tableHTML += `
                                <tr>
                                    <td>${item.tour_id || 'N/A'}</td>
                                    <td>${item.type || 'N/A'}</td>
                                    <td>${dataItem.entrytime || 'N/A'}</td>
                                    <td>${dataItem.entrypickup || 'N/A'}</td>
                                    <td>${dataItem.type || 'N/A'}</td>
                                    <td>
                                        <select class="form-control guide-select" name="guide_id[${index}]" data-order-id="${item.booking_id || ''}" data-tour-id="${item.tour_id || ''}">
                                            <option value="">Select Guide</option>
                                            ${(function() {
                                                let options = '';
                                                if (response.guides && response.guides.length) {
                                                    response.guides.forEach(guide => {
                                                        const isSelected = dataItem.guide_id == guide.guide_id;
                                                        options += `<option ${isSelected ? 'selected' : ''} value="${guide.guide_id}">${guide.name} - ${guide.government_license_no}</option>`;
                                                    });
                                                }
                                                return options;
                                            })()}
                                        </select>
                                        <input type="hidden" name="order_id[${index}]" value="${item.booking_id || ''}">
                                        <input type="hidden" name="tour_id[${index}]" value="${item.tour_id || ''}">
                                    </td>
                                </tr>`;
                        });
                        
                        $('#tourOrdersTableBody').html(tableHTML);
                        $('#exportOrdersBtn').show();
                        
                        // Initialize DataTable
                        initializeDataTable();
                    } else {
                        $('#tourOrdersTableBody').html('<tr><td colspan="6" class="text-center">No orders found for this date</td></tr>');
                        $('#exportOrdersBtn').hide();
                    }
                } else {
                    const errorMessage = response.message || 'Error loading orders';
                    console.error('Error:', errorMessage);
                    showAlert('error', errorMessage);
                    $('#tourOrdersTableBody').html('<tr><td colspan="6" class="text-center">Error loading orders</td></tr>');
                    $('#exportOrdersBtn').hide();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching orders by date:', {xhr, status, error});
                const errorMessage = xhr.responseJSON?.message || 'Error fetching orders';
                showAlert('error', errorMessage);
                $('#tourOrdersTableBody').html('<tr><td colspan="6" class="text-center">Error loading orders</td></tr>');
                $('#exportOrdersBtn').hide();
            }
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
                        { orderable: false, targets: [5] }  // Disable sorting on guide select column
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

    // Handle guide selection change
    $(document).on('change', '.guide-select', function() {
        const $select = $(this);
        const guideId = $select.val();
        const orderId = $select.data('order-id');
        const date = $('#dateSelect').val();
        const dmcId = $('#dmc_id').val();
        const tourId = $select.data('tour-id');

        const selectedGuideName = $select.find('option:selected').text().trim();
        const item = tourGuideOrdersData.find(obj => obj.order_id == orderId);
        if (item) {
            item.assigned_guide = selectedGuideName;
        }
        
        
        // Make AJAX call to update the guide assignment
        $.ajax({
            url: updateDriverVehicleAssignmentUrl,
            method: 'POST',
            data: {
                tour_id: tourId,
                order_id: orderId,
                guide_id: guideId,
                date: date,
                dmc_id: dmcId,
                
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Guide assigned successfully');
                } else {
                    showAlert('error', response.message || 'Failed to assign guide');
                }
            },
            error: function(xhr) {
                showAlert('error', 'Error updating guide assignment');
                console.error('Error updating guide assignment:', xhr);
            }
        });
    });

    // Export to Excel functionality
    $('#exportOrdersBtn').click(function(e) {
        e.preventDefault();
        
        if (tourGuideOrdersData && tourGuideOrdersData.length > 0) {
            // Format data for Excel export
            const excelData = tourGuideOrdersData.map(item => ({
                'Tour ID': item.tour_id,
                'Order Type': item.order_type,
                'Pickup Time': item.pickup_time,
                'Pickup Location': item.pickup_location,
                'Tour Type': item.tour_type,
                'Assigned Guide': item.assigned_guide,
                'Customer Name': item.customer_name,
                'Customer Phone': item.customer_phone,
                'Customer Email': item.customer_email
            }));
            
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
        
        const formData = {
            date: $('#dateSelect').val(),
            dmc_id: $('#dmc_id').val()
        };

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    // Reload guide orders to reflect the changes
                    loadOrdersByDate(formData.date);
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(key => {
                        showAlert('error', errors[key][0]);
                    });
                } else {
                    showAlert('error', xhr.responseJSON.message || 'An error occurred while creating the guide jobsheet');
                }
            }
        });
    });
});
</script>
@endsection
