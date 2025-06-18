@extends('layouts.layout')
@section('content')

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">

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
    
    /* Badge styles */
    .badge {
        padding: 5px 10px;
        font-size: 0.8rem;
        font-weight: 500;
        border-radius: 4px;
    }
    
    .badge-primary {
        background-color: #6777ef;
        color: #fff;
    }
    
    .badge-success {
        background-color: #47c363;
        color: #fff;
    }
    
    .badge-info {
        background-color: #3abaf4;
        color: #fff;
    }
    
    .badge-warning {
        background-color: #ffa426;
        color: #fff;
    }
    
    /* Filter section */
    .filters {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
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
        <div id="message-wrapper">
            @include('components.alert')
        </div>
        <h5 class="mb-4">Jobsheet Records</h5>
        
        <!-- Filters Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Filter Jobsheets</h5>
            </div>
            <div class="card-body">
                <form id="filterForm">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="dateFilter" class="form-label">Filter by Date</label>
                            <input type="text" id="dateFilter" class="form-control" placeholder="Select date">
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Apply Filter</button>
                            <button type="button" id="resetFilter" class="btn btn-outline-secondary">Show All</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Jobsheets Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Jobsheet Assignments</h5>
                <button id="exportBtn" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="jobsheetsTable">
                        <thead>
                            <tr>
                                <th>Tour ID</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Service Type</th>
                                <th>Journey Time</th>
                                <th>Pickup Location</th>
                                <th>Dropoff Location</th>
                                <th>Driver</th>
                                <th>Vehicle</th>
                                <th>Guide</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Will be populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="jobsheetModal" tabindex="-1" aria-labelledby="jobsheetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="jobsheetModalLabel">Jobsheet Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Tour ID:</strong> <span id="modal-tour-id"></span></p>
                        <p><strong>Date:</strong> <span id="modal-date"></span></p>
                        <p><strong>Type:</strong> <span id="modal-type"></span></p>
                        <p><strong>Service Type:</strong> <span id="modal-service-type"></span></p>
                        <p><strong>Journey Time:</strong> <span id="modal-journey-time"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Pickup Location:</strong> <span id="modal-pickup"></span></p>
                        <p><strong>Dropoff Location:</strong> <span id="modal-dropoff"></span></p>
                        <p><strong>Driver:</strong> <span id="modal-driver"></span></p>
                        <p><strong>Vehicle:</strong> <span id="modal-vehicle"></span></p>
                        <p><strong>Guide:</strong> <span id="modal-guide"></span></p>
                        <p><strong>DMC:</strong> <span id="modal-dmc"></span></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Flatpickr for date filter
    const datePicker = flatpickr("#dateFilter", {
        dateFormat: "Y-m-d",
        allowInput: true,
        disableMobile: "true"
    });
    
    // Custom alert function to replace toastr
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
    
    // Initialize DataTable
    const jobsheetsTable = $('#jobsheetsTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('jobsheets.data') }}",
            data: function(d) {
                d.date = $('#dateFilter').val();
            }
        },
        columns: [
            { data: 'tour_id', name: 'tour_id' },
            { data: 'date', name: 'date' },
            { data: 'type', name: 'type' },
            { data: 'service_type', name: 'service_type' },
            { data: 'journey_time', name: 'journey_time' },
            { data: 'pickup', name: 'pickup' },
            { data: 'dropoff', name: 'dropoff' },
            { data: 'driver', name: 'driver' },
            { data: 'vehicle', name: 'vehicle' },
            { data: 'guide', name: 'guide' },
            { 
                data: 'actions', 
                name: 'actions',
                orderable: false,
                searchable: false
            }
        ],
        order: [[2, 'desc']], // Default sort by date (descending)
        pageLength: 15, // Show more records per page
        dom: 'lfrtip', // Show length menu and processing
        lengthMenu: [[10, 15, 25, 50, -1], [10, 15, 25, 50, "All"]]
    });
    
    // Load all data when page loads
    jobsheetsTable.ajax.reload();
    
    // Show welcome message
    showAlert('success', 'Jobsheet data loaded successfully');
    
    // Handle filter form submission
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        jobsheetsTable.ajax.reload();
        showAlert('info', 'Filtered by selected date');
    });
    
    // Handle reset filter button to show all data
    $('#resetFilter').on('click', function() {
        datePicker.clear();
        jobsheetsTable.ajax.reload();
        showAlert('info', 'Showing all jobsheet data');
    });
    
    // Export to Excel functionality
    $('#exportBtn').on('click', function() {
        // Get current filtered data
        const filterData = {
            date: $('#dateFilter').val()
        };
        
        // Show loading state
        const $btn = $(this);
        const originalText = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin"></i> Exporting...');
        $btn.prop('disabled', true);
        
        // Get data via AJAX
        $.ajax({
            url: "{{ route('jobsheets.export') }}",
            method: 'GET',
            data: filterData,
            success: function(response) {
                if (response.success) {
                    // Create workbook and worksheet
                    const wb = XLSX.utils.book_new();
                    const ws = XLSX.utils.json_to_sheet(response.data);
                    
                    // Add the worksheet to the workbook
                    XLSX.utils.book_append_sheet(wb, ws, "Jobsheets");
                    
                    // Generate filename
                    const date = new Date();
                    const filename = `jobsheets_export_${date.getFullYear()}-${date.getMonth()+1}-${date.getDate()}.xlsx`;
                    
                    // Export to file
                    XLSX.writeFile(wb, filename);
                    
                    showAlert('success', 'Jobsheets exported successfully!');
                } else {
                    showAlert('error', response.message || 'Error exporting jobsheets');
                }
                
                // Restore button state
                $btn.html(originalText);
                $btn.prop('disabled', false);
            },
            error: function(xhr) {
                console.error('Export error:', xhr);
                showAlert('error', 'Error exporting jobsheets');
                
                // Restore button state
                $btn.html(originalText);
                $btn.prop('disabled', false);
            }
        });
    });
    
    // Show jobsheet details in modal
    $(document).on('click', '.view-details', function() {
        const jobsheetId = $(this).data('id');
        
        $.ajax({
            url: "{{ route('jobsheets.details', '') }}/" + jobsheetId,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    // Populate modal with jobsheet details
                    $('#modal-jobsheet-id').text(data.jobsheet_id);
                    $('#modal-tour-id').text(data.tour_id);
                    $('#modal-date').text(data.date);
                    $('#modal-type').text(data.type);
                    $('#modal-service-type').text(data.service_type);
                    $('#modal-journey-time').text(data.journey_time);
                    
                    // Parse JSON data for pickup/dropoff if needed
                    const jsonData = typeof data.data === 'string' ? JSON.parse(data.data) : data.data;
                    $('#modal-pickup').text(jsonData.pickup || 'N/A');
                    $('#modal-dropoff').text(jsonData.dropoff || 'N/A');
                    
                    // Set other fields
                    $('#modal-driver').text(data.driver_name || 'Not Assigned');
                    $('#modal-vehicle').text(data.vehicle_name || 'Not Assigned');
                    $('#modal-guide').text(data.guide_name || 'Not Assigned');
                    $('#modal-dmc').text(data.dmc_name || 'N/A');
                    
                    // Show the modal
                    $('#jobsheetModal').modal('show');
                } else {
                    showAlert('error', response.message || 'Error loading jobsheet details');
                }
            },
            error: function(xhr) {
                console.error('Error loading jobsheet details:', xhr);
                showAlert('error', 'Error loading jobsheet details');
            }
        });
    });
});
</script>
@endsection 