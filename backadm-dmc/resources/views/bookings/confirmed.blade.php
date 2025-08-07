@extends('layouts.layout')
@section('title', 'Confirmed Bookings')
@extends('layouts.datatablecss')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-2">
                <span class="text-muted fw-light">Bookings /</span> Confirmed Bookings
            </h4>
            <p class="text-muted">Manage confirmed bookings ready for processing</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-success fs-6">
                <i class="ri-check-double-line me-1"></i>
                {{ $tours->total() }} Confirmed
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
                            <p class="text-muted mb-0">Total Confirmed</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-success rounded">
                                <i class="ri-check-double-line ri-24px"></i>
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
                            <h5 class="card-title mb-1">{{ $tours->where('check_in_time', '>=', now())->where('check_in_time', '<=', now()->addDays(7))->count() }}</h5>
                            <p class="text-muted mb-0">This Week</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-primary rounded">
                                <i class="ri-calendar-todo-line ri-24px"></i>
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
                            <h5 class="card-title mb-1">{{ $tours->where('check_in_time', '>=', now()->addDays(8))->where('check_in_time', '<=', now()->addDays(30))->count() }}</h5>
                            <p class="text-muted mb-0">Next Month</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-info rounded">
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
                            <h5 class="card-title mb-1">${{ number_format(($tours->where('adult', '>', 0)->sum('adult') + $tours->where('child', '>', 0)->sum('child')) * 2000) }}</h5>
                            <p class="text-muted mb-0">Confirmed Revenue</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-warning rounded">
                                <i class="ri-money-dollar-circle-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Tours Alert -->
    @php
        $upcomingTours = $tours->where('check_in_time', '>=', now())->where('check_in_time', '<=', now()->addDays(7));
    @endphp
    @if($upcomingTours->count() > 0)
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center">
            <i class="ri-calendar-event-line ri-24px me-3"></i>
            <div>
                <h6 class="alert-heading mb-1">Upcoming Tours This Week</h6>
                <p class="mb-0">{{ $upcomingTours->count() }} confirmed bookings are scheduled to start within the next 7 days.</p>
            </div>
            <button class="btn btn-info ms-auto" onclick="showUpcomingTours()">
                <i class="ri-eye-line me-1"></i> View All
            </button>
        </div>
    </div>
    @endif

    <!-- Tours Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Confirmed Bookings List</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary" onclick="bulkMakeDefinite()">
                    <i class="ri-arrow-right-line me-1"></i> Make Definite
                </button>
                <button class="btn btn-sm btn-outline-success" onclick="generateVouchers()">
                    <i class="ri-file-text-line me-1"></i> Generate Vouchers
                </button>
                <button class="btn btn-sm btn-outline-primary" onclick="exportData()">
                    <i class="ri-download-line me-1"></i> Export
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter Options -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search by Tour ID, Display ID, Destination...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="timeFilter">
                        <option value="">All Time</option>
                        <option value="this_week">This Week</option>
                        <option value="next_week">Next Week</option>
                        <option value="this_month">This Month</option>
                        <option value="next_month">Next Month</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="destinationFilter">
                        <option value="">All Destinations</option>
                        @foreach($tours->pluck('country')->unique()->filter() as $country)
                            <option value="{{ $country }}">{{ $country }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                        <i class="ri-refresh-line me-1"></i> Reset
                    </button>
                </div>
            </div>

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
                            <th>Travel Dates</th>
                            <th>Confirmation Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tours as $key => $tour)
                        <tr class="{{ $tour->check_in_time && \Carbon\Carbon::parse($tour->check_in_time)->diffInDays(now(), false) <= 7 && \Carbon\Carbon::parse($tour->check_in_time)->diffInDays(now(), false) >= 0 ? 'table-info' : '' }}">
                            <td>
                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $tour->tour_id }}">
                            </td>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-success">{{ $tour->display_id }}</strong>
                                    <small class="text-muted">Tour ID: #{{ $tour->tour_id }}</small>
                                    @if($tour->multi_enq_id)
                                        <small class="text-info">Multi: {{ $tour->multi_enq_id }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ $tour->country ?? 'N/A' }}</span>
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
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ $tour->agent_name ?? 'N/A' }}</span>
                                    <small class="text-muted">ID: {{ $tour->agent_id ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    @if($tour->check_in_time)
                                        <small><strong>Check-in:</strong> {{ \Carbon\Carbon::parse($tour->check_in_time)->format('D, M d, Y') }}</small>
                                    @endif
                            
                                    @if($tour->check_out_time)
                                        <small><strong>Check-out:</strong> {{ \Carbon\Carbon::parse($tour->check_out_time)->format('D, M d, Y') }}</small>
                                    @endif
                            
                                    @if($tour->check_in_time)
                                        @php
                                            $checkIn = \Carbon\Carbon::parse($tour->check_in_time);
                                            $daysUntilTravel = floor(now()->floatDiffInDays($checkIn, false)); // Floor to get whole number
                                        @endphp
                            
                                        @if($daysUntilTravel > 0)
                                            <span class="badge bg-primary mt-1">{{ $daysUntilTravel }} days to go</span>
                                        @elseif($daysUntilTravel === 0)
                                            <span class="badge bg-success mt-1">Starting Today</span>
                                        @else
                                            <span class="badge bg-secondary mt-1">Started {{ abs($daysUntilTravel) }} days ago</span>
                                        @endif
                                    @endif
                                </div>
                            </td>                                                       
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $tour->updated_at->format('M d, Y') }}</span>
                                    <small class="text-muted">{{ $tour->updated_at->diffForHumans() }}</small>
                                </div>
                            </td>
                            <td>
                                @if($tour->check_in_time && \Carbon\Carbon::parse($tour->check_in_time)->diffInDays(now(), false) <= 3 && \Carbon\Carbon::parse($tour->check_in_time)->diffInDays(now(), false) >= 0)
                                    <span class="badge bg-warning">
                                        <i class="ri-time-line me-1"></i>Starting Soon
                                    </span>
                                @elseif($tour->check_in_time && \Carbon\Carbon::parse($tour->check_in_time)->diffInDays(now(), false) < 0)
                                    <span class="badge bg-danger">
                                        <i class="ri-calendar-event-line me-1"></i>In Progress
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="ri-check-double-line me-1"></i>Confirmed
                                    </span>
                                @endif
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
                                            <a class="dropdown-item text-primary" href="#" onclick="makeDefinite('{{ $tour->tour_id }}')">
                                                <i class="ri-arrow-right-line me-2"></i> Make Definite
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="generateVoucher('{{ $tour->tour_id }}')">
                                                <i class="ri-file-text-line me-2"></i> Generate Voucher
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="sendConfirmation('{{ $tour->tour_id }}')">
                                                <i class="ri-mail-send-line me-2"></i> Send Confirmation
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="sendItinerary('{{ $tour->tour_id }}')">
                                                <i class="ri-map-line me-2"></i> Send Itinerary
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="modifyBooking('{{ $tour->tour_id }}')">
                                                <i class="ri-edit-line me-2"></i> Modify Booking
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="cancelConfirmed('{{ $tour->tour_id }}')">
                                                <i class="ri-close-line me-2"></i> Cancel Booking
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
                                    <i class="ri-check-double-line ri-48px text-muted mb-2"></i>
                                    <h6 class="text-muted">No confirmed bookings</h6>
                                    <p class="text-muted mb-0">All bookings are in other stages or there are no confirmed bookings yet.</p>
                                </div>
                            </td>
                        </tr> --}}
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <p class="text-muted mb-0">
                        Showing {{ $tours->firstItem() ?? 0 }} to {{ $tours->lastItem() ?? 0 }} of {{ $tours->total() }} results
                    </p>
                </div>
                <div>
                    {{ $tours->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function makeDefinite(tourId) {
    if (confirm('Are you sure you want to make this booking definite? This will move it to the definite bookings section.')) {
        console.log('Making booking definite', tourId);
        // Add AJAX call here
    }
}

function generateVoucher(tourId) {
    console.log('Generating voucher for tour', tourId);
    // Implementation for voucher generation
}

function sendConfirmation(tourId) {
    console.log('Sending confirmation email for tour', tourId);
    // Implementation for sending confirmation
}

function sendItinerary(tourId) {
    console.log('Sending itinerary for tour', tourId);
    // Implementation for sending itinerary
}

function modifyBooking(tourId) {
    console.log('Modifying booking', tourId);
    // Redirect to modification page
}

function cancelConfirmed(tourId) {
    if (confirm('Are you sure you want to cancel this confirmed booking? This may require refund processing.')) {
        console.log('Cancelling confirmed booking', tourId);
    }
}

function bulkMakeDefinite() {
    const selectedTours = document.querySelectorAll('.row-checkbox:checked');
    if (selectedTours.length === 0) {
        alert('Please select at least one booking to make definite.');
        return;
    }
    
    if (confirm(`Are you sure you want to make ${selectedTours.length} bookings definite?`)) {
        console.log('Bulk making definite', selectedTours.length, 'bookings');
    }
}

function generateVouchers() {
    const selectedTours = document.querySelectorAll('.row-checkbox:checked');
    if (selectedTours.length === 0) {
        alert('Please select at least one booking to generate vouchers.');
        return;
    }
    
    console.log('Generating vouchers for', selectedTours.length, 'bookings');
}

function showUpcomingTours() {
    // Filter to show only upcoming tours
    document.getElementById('timeFilter').value = 'this_week';
    filterTable();
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('timeFilter').value = '';
    document.getElementById('destinationFilter').value = '';
    filterTable();
}

function filterTable() {
    // Implementation for client-side filtering
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const timeFilter = document.getElementById('timeFilter').value;
    const destinationFilter = document.getElementById('destinationFilter').value;
    
    const rows = document.querySelectorAll('#toursTable tbody tr');
    
    rows.forEach(row => {
        if (row.cells.length === 1) return; // Skip empty state row
        
        let show = true;
        
        // Search filter
        if (searchTerm) {
            const tourDetails = row.cells[1].textContent.toLowerCase();
            const destination = row.cells[2].textContent.toLowerCase();
            if (!tourDetails.includes(searchTerm) && !destination.includes(searchTerm)) {
                show = false;
            }
        }
        
        // Destination filter
        if (destinationFilter) {
            const country = row.cells[2].querySelector('.fw-medium')?.textContent || '';
            if (country !== destinationFilter) {
                show = false;
            }
        }
        
        row.style.display = show ? '' : 'none';
    });
}

function exportData() {
    console.log('Exporting confirmed bookings data...');
}

// Initialize filters
document.getElementById('searchInput').addEventListener('input', filterTable);
document.getElementById('timeFilter').addEventListener('change', filterTable);
document.getElementById('destinationFilter').addEventListener('change', filterTable);

// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});
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
