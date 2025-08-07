@extends('layouts.layout')
@section('title', 'New Enquiries')
@extends('layouts.datatablecss')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-2">
                <span class="text-muted fw-light">Bookings /</span> New Enquiries
            </h4>
            <p class="text-muted">Manage all new enquiries and convert them to bookings</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary fs-6">
                <i class="ri-file-list-line me-1"></i>
                {{ $tours->total() }} Total Enquiries
            </span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">{{ $tours->total() }}</h5>
                            <p class="text-muted mb-0">Total Enquiries</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-primary rounded">
                                <i class="ri-questionnaire-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">{{ $tours->where('created_at', '>=', now()->today())->count() }}</h5>
                            <p class="text-muted mb-0">Today's Enquiries</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-success rounded">
                                <i class="ri-calendar-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">{{ $tours->where('adult', '>', 0)->sum('adult') }}</h5>
                            <p class="text-muted mb-0">Total Adults</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-info rounded">
                                <i class="ri-user-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">{{ $tours->where('child', '>', 0)->sum('child') }}</h5>
                            <p class="text-muted mb-0">Total Children</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-warning rounded">
                                <i class="ri-user-smile-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Filters</h5>
            <button class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
                <i class="ri-refresh-line me-1"></i> Reset
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Tour ID, Display ID, Destination...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Country</label>
                    <select class="form-select" id="countryFilter">
                        <option value="">All Countries</option>
                        @foreach($tours->pluck('destination')->unique()->filter() as $destination)
                            <option value="{{ $destination }}">{{ $destination }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">City</label>
                    <select class="form-select" id="cityFilter">
                        <option value="">All Cities</option>
                        @foreach($tours->pluck('city')->unique()->filter() as $city)
                            <option value="{{ $city }}">{{ $city }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Agent</label>
                    <select class="form-select" id="agentFilter">
                        <option value="">All Agents</option>
                        @foreach($tours->where('agent_name', '!=', null)->pluck('agent_name', 'agent_id')->unique() as $agentId => $agentName)
                            <option value="{{ $agentName }}">{{ $agentName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date Range</label>
                    <input type="date" class="form-control" id="dateFilter">
                </div>
            </div>
        </div>
    </div>

    <!-- Tours Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">New Enquiries List</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary" onclick="exportData()">
                    <i class="ri-download-line me-1"></i> Export
                </button>
                <button class="btn btn-sm btn-primary" onclick="bulkActions()">
                    <i class="ri-settings-line me-1"></i> Bulk Actions
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="datatables-basic table table-bordered" id="toursTable">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>#</th>
                            <th>Tour Details</th>
                            <th>Destination</th>
                            <th>Guests</th>
                            <th>Agent</th>
                            <th>Check-in/Check-out</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tours as $key => $tour)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $tour->tour_id }}">
                            </td>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-primary">{{ $tour->display_id }}</strong>
                                    <small class="text-muted">Tour ID: #{{ $tour->tour_id }}</small>
                                    @if($tour->multi_enq_id)
                                        <small class="text-info">Multi: {{ $tour->multi_enq_id }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ $tour->destination ?? 'N/A' }}</span>
                                    <small class="text-muted">{{ $tour->city ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    @if($tour->adult > 0)
                                        <span class="badge bg-primary">{{ $tour->adult }} Adults</span>
                                    @endif
                                    @if($tour->child > 0)
                                        <span class="badge bg-warning">{{ $tour->child }} Children</span>
                                    @endif
                                    @if($tour->adult == 0 && $tour->child == 0)
                                        <span class="text-muted">No guests specified</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    @if($tour->agent_name)
                                        <span class="fw-medium">{{ $tour->agent_name }}</span>
                                        <small class="text-muted">ID: {{ $tour->agent_id }}</small>
                                    @else
                                        <span class="text-muted">No agent assigned</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    @if($tour->check_in_time)
                                        <small><strong>In:</strong> {{ \Carbon\Carbon::parse($tour->check_in_time)->format('D, M d, Y') }}</small>
                                    @endif
                                    @if($tour->check_out_time)
                                        <small><strong>Out:</strong> {{ \Carbon\Carbon::parse($tour->check_out_time)->format('D, M d, Y') }}</small>
                                    @endif
                                    @if(!$tour->check_in_time && !$tour->check_out_time)
                                        <span class="text-muted">Not specified</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $tour->created_at->format('D,  M d, Y') }}</span>
                                    <small class="text-muted">{{ $tour->created_at->format('h:i A') }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('bookings.view-tour', $tour->tour_id) }}">
                                                <i class="ri-eye-line me-2"></i> View Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="convertToProspect('{{ $tour->tour_id }}')">
                                                <i class="ri-arrow-right-line me-2"></i> Move to Follow Up
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="convertToTentative('{{ $tour->tour_id }}')">
                                                <i class="ri-bookmark-line me-2"></i> Mark as Tentative
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="deleteTour('{{ $tour->tour_id }}')">
                                                <i class="ri-delete-bin-line me-2"></i> Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        {{-- <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="ri-inbox-line ri-48px text-muted mb-2"></i>
                                    <h6 class="text-muted">No new enquiries found</h6>
                                    <p class="text-muted mb-0">All enquiries have been processed or there are no new enquiries yet.</p>
                                </div>
                            </td>
                        </tr> --}}
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <p class="text-muted mb-0">
                        Showing {{ $tours->firstItem() ?? 0 }} to {{ $tours->lastItem() ?? 0 }} of {{ $tours->total() }} results
                    </p>
                </div>
                <div>
                    {{ $tours->links() }}
                </div>
            </div> --}}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable-like functionality
    const searchInput = document.getElementById('searchInput');
    const countryFilter = document.getElementById('countryFilter');
    const cityFilter = document.getElementById('cityFilter');
    const agentFilter = document.getElementById('agentFilter');
    const dateFilter = document.getElementById('dateFilter');
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        filterTable();
    });
    
    countryFilter.addEventListener('change', function() {
        filterTable();
    });
    
    cityFilter.addEventListener('change', function() {
        filterTable();
    });
    
    agentFilter.addEventListener('change', function() {
        filterTable();
    });
    
    dateFilter.addEventListener('change', function() {
        filterTable();
    });
    
    // Select all functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
});

function filterTable() {
    // Implementation for client-side filtering
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const countryFilter = document.getElementById('countryFilter').value;
    const cityFilter = document.getElementById('cityFilter').value;
    const agentFilter = document.getElementById('agentFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;
    
    const rows = document.querySelectorAll('#toursTable tbody tr');
    
    rows.forEach(row => {
        if (row.cells.length === 1) return; // Skip empty state row
        
        const tourDetails = row.cells[1].textContent.toLowerCase();
        const destination = row.cells[2].textContent.toLowerCase();
        const country = row.cells[2].querySelector('.fw-medium')?.textContent || '';
        const city = row.cells[2].querySelector('.text-muted')?.textContent || '';
        const agent = row.cells[4].querySelector('.fw-medium')?.textContent || '';
        
        let show = true;
        
        if (searchTerm && !tourDetails.includes(searchTerm) && !destination.includes(searchTerm)) {
            show = false;
        }
        
        if (countryFilter && country !== countryFilter) {
            show = false;
        }
        
        if (cityFilter && city !== cityFilter) {
            show = false;
        }
        
        if (agentFilter && agent !== agentFilter) {
            show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('countryFilter').value = '';
    document.getElementById('cityFilter').value = '';
    document.getElementById('agentFilter').value = '';
    document.getElementById('dateFilter').value = '';
    filterTable();
}

function convertToProspect(tourId) {
    if (confirm('Are you sure you want to move this enquiry to Follow Up?')) {
        // Implementation for status update
        console.log('Converting tour', tourId, 'to Prospect status');
        // Add AJAX call here
    }
}

function convertToTentative(tourId) {
    if (confirm('Are you sure you want to mark this enquiry as Tentative?')) {
        // Implementation for status update
        console.log('Converting tour', tourId, 'to Tentative status');
        // Add AJAX call here
    }
}

function deleteTour(tourId) {
    if (confirm('Are you sure you want to delete this tour? This action cannot be undone.')) {
        // Implementation for deletion
        console.log('Deleting tour', tourId);
        // Add AJAX call here
    }
}

function exportData() {
    // Implementation for data export
    console.log('Exporting data...');
}

function bulkActions() {
    const selectedTours = document.querySelectorAll('.row-checkbox:checked');
    if (selectedTours.length === 0) {
        alert('Please select at least one tour for bulk actions.');
        return;
    }
    
    // Implementation for bulk actions
    console.log('Bulk actions for', selectedTours.length, 'tours');
}
</script>
@endsection
@section('scripts')
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
{{-- <script>
    $(document).ready(function() {
        // Initialize DataTable with export buttons
        var table = $('.datatables-basic').DataTable({
            responsive: true,
            buttons: [
                'copy',
                'csv',
                'excel',
                'pdf',
                'print' // Enable copy, CSV, Excel, PDF, and Print buttons
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            },
            lengthMenu: [10, 25, 50, 100], // Customize number of entries per page
            pageLength: 10, // Default number of entries per page
            drawCallback: function() {
                // Reinitialize Select2 for guide and driver dropdowns after each draw
                $(document).ready(function () {
                    let currentDeclineBookingId = null;
                    let currentDeclineRow = null;
                    
                    // Open Approve Modal and set booking ID and Tour ID
                    $(".approve-btn").on("click", function () {
                        let bookingId = $(this).data("id");
                        let tourId = $(this).closest("tr").find("td:eq(1)").text(); // Get tour ID from the second column
                        
                        // Set values in modal
                        $("#bookingId").val(bookingId);
                        $("#modalTourId").text("Tour ID: #" + tourId.trim());
                        
                        // Reset the form and hide loader when opening modal
                        $("#approveForm")[0].reset();
                        $("#approveLoader").hide();
                        $("#approveButtonText").show();
                        $("#approveButtonSpinner").hide();
                        $("#approveSubmitBtn").prop("disabled", false);
                    });
    
                    // Handle Approve Form Submission
                    $("#approveForm").on("submit", function (e) {
                        e.preventDefault();
    
                        // Show loading spinner and disable button
                        $("#approveLoader").show();
                        $("#approveButtonText").text("Processing...");
                        $("#approveButtonSpinner").show();
                        $("#approveSubmitBtn").prop("disabled", true);
                        
                        let formData = new FormData(this);
                        let bookingId = $("#bookingId").val();
                        let row = $("button[data-id='" + bookingId + "']").closest("tr"); // Get table row
    
                        $.ajax({
                            url: "{{ route('bookings.approve') }}",
                            type: "POST",
                            data: formData,
                            processData: false,
                            contentType: false,
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            success: function (response) {
                                if (response.success) {
                                    // Hide spinner
                                    $("#approveLoader").hide();
                                    $("#approveModal").modal("hide"); // Hide modal
    
                                    // Apply green background
                                    row.addClass("row-approved");
                                    
                                    // Wait a moment to show the color change, then swipe left
                                    setTimeout(function() {
                                        row.addClass("swipe-left");
                                        
                                        // Show global loader during refresh
                                        setTimeout(function() {
                                            $("#globalLoader").addClass("active");
                                            
                                            // Delay and refresh the page
                                            setTimeout(function () {
                                                location.reload();
                                            }, 800);
                                        }, 700); // After swipe animation is mostly done
                                    }, 300);
                                }
                            },
                            error: function (xhr) {
                                // Hide spinner and re-enable button on error
                                $("#approveLoader").hide();
                                $("#approveButtonText").text("Approved");
                                $("#approveButtonSpinner").hide();
                                $("#approveSubmitBtn").prop("disabled", false);
                                
                                alert("Error! " + (xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong."));
                            }
                        });
                    });
    
                    // Handle Decline Button Click with custom confirmation
                    $(".decline-btn").on("click", function () {
                        currentDeclineBookingId = $(this).data("id");
                        currentDeclineRow = $(this).closest("tr");
                        
                        // Show custom confirmation dialog
                        $("#declineConfirmDialog").addClass("active");
                    });
                    
                    // Handle Cancel Decline
                    $("#cancelDecline").on("click", function() {
                        $("#declineConfirmDialog").removeClass("active");
                        currentDeclineBookingId = null;
                        currentDeclineRow = null;
                    });
                    
                    // Handle Confirm Decline
                    $("#confirmDecline").on("click", function() {
                        if (!currentDeclineBookingId) return;
                        
                        // Hide confirmation dialog
                        $("#declineConfirmDialog").removeClass("active");
                        
                        // Show global loader
                        $("#globalLoader").addClass("active");
    
                        $.ajax({
                            url: "{{ route('bookings.decline') }}",
                            type: "POST",
                            data: {
                                booking_id: currentDeclineBookingId,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function (response) {
                                if (response.success) {
                                    // Hide global loader
                                    $("#globalLoader").removeClass("active");
                                    
                                    // Apply red background
                                    currentDeclineRow.addClass("row-declined");
                                    
                                    // Wait a moment to show the color change, then swipe left
                                    setTimeout(function() {
                                        currentDeclineRow.addClass("swipe-left");
                                        
                                        // Show global loader during refresh
                                        setTimeout(function() {
                                            $("#globalLoader").addClass("active");
                                            
                                            // Delay and refresh the page
                                            setTimeout(function () {
                                                location.reload();
                                            }, 800);
                                        }, 700); // After swipe animation is mostly done
                                    }, 300);
                                }
                            },
                            error: function (xhr) {
                                // Hide global loader on error
                                $("#globalLoader").removeClass("active");
                                alert("Error! " + (xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong."));
                            }
                        });
                    });
                    
                    // Close confirmation dialog when clicking outside
                    $(document).on("click", function(e) {
                        if (
                            $("#declineConfirmDialog").hasClass("active") && 
                            !$(e.target).closest(".confirm-content").length && 
                            !$(e.target).closest(".decline-btn").length
                        ) {
                            $("#declineConfirmDialog").removeClass("active");
                            currentDeclineBookingId = null;
                            currentDeclineRow = null;
                        }
                    });
                });
            }
        });
    
        // Custom export button functionality (for the dropdown)
        $('#exportCopy').on('click', function() {
            table.button('.buttons-copy').trigger();
        });
    
        $('#exportCSV').on('click', function() {
            table.button('.buttons-csv').trigger();
        });
    
        $('#exportExcel').on('click', function() {
            table.button('.buttons-excel').trigger();
        });
    
        $('#exportPDF').on('click', function() {
            table.button('.buttons-pdf').trigger();
        });
    
        $('#exportPrint').on('click', function() {
            table.button('.buttons-print').trigger();
        });
    });
</script> --}}
@endsection

@extends('layouts.datatablejs')
