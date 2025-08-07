@extends('layouts.layout')
@section('title', 'Definite Bookings')
@extends('layouts.datatablecss')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-2">
                <span class="text-muted fw-light">Bookings /</span> Definite Bookings
            </h4>
            <p class="text-muted">Manage definite bookings ready for execution</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-info fs-6">
                <i class="ri-shield-check-line me-1"></i>
                {{ $tours->total() }} Definite
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
                            <p class="text-muted mb-0">Total Definite</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-info rounded">
                                <i class="ri-shield-check-line ri-24px"></i>
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
                            <p class="text-muted mb-0">Starting This Week</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-warning rounded">
                                <i class="ri-calendar-week-line ri-24px"></i>
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
                            <h5 class="card-title mb-1">{{ $tours->where('check_in_time', '<', now())->count() }}</h5>
                            <p class="text-muted mb-0">Ready to Execute</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-success rounded">
                                <i class="ri-play-circle-line ri-24px"></i>
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
                            <h5 class="card-title mb-1">${{ number_format(($tours->where('adult', '>', 0)->sum('adult') + $tours->where('child', '>', 0)->sum('child')) * 2500) }}</h5>
                            <p class="text-muted mb-0">Locked Revenue</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-primary rounded">
                                <i class="ri-money-dollar-circle-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Required Alert -->
    @php
        $readyToExecute = $tours->where('check_in_time', '<', now());
    @endphp
    @if($readyToExecute->count() > 0)
    <div class="alert alert-success mb-4">
        <div class="d-flex align-items-center">
            <i class="ri-play-circle-line ri-24px me-3"></i>
            <div>
                <h6 class="alert-heading mb-1">Ready for Execution</h6>
                <p class="mb-0">{{ $readyToExecute->count() }} definite bookings are ready to be moved to actual status.</p>
            </div>
            <button class="btn btn-success ms-auto" onclick="bulkMakeActual()">
                <i class="ri-arrow-right-line me-1"></i> Execute All
            </button>
        </div>
    </div>
    @endif

    <!-- Tours Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Definite Bookings List</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-success" onclick="bulkMakeActual()">
                    <i class="ri-play-circle-line me-1"></i> Make Actual
                </button>
                <button class="btn btn-sm btn-outline-primary" onclick="assignServices()">
                    <i class="ri-team-line me-1"></i> Assign Services
                </button>
                <button class="btn btn-sm btn-outline-info" onclick="generateJobSheets()">
                    <i class="ri-file-list-line me-1"></i> Job Sheets
                </button>
                <button class="btn btn-sm btn-outline-primary" onclick="exportData()">
                    <i class="ri-download-line me-1"></i> Export
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter Options -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search by Tour ID, Display ID...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="ready">Ready to Execute</option>
                        <option value="upcoming">Upcoming</option>
                        <option value="future">Future</option>
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
                <div class="col-md-3">
                    <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                        <i class="ri-refresh-line me-1"></i> Reset Filters
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover" id="toursTable">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>Tour Details</th>
                            <th>Destination</th>
                            <th>Guests</th>
                            <th>Travel Dates</th>
                            <th>Execution Status</th>
                            <th>Services</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tours as $tour)
                        <tr class="{{ $tour->check_in_time && \Carbon\Carbon::parse($tour->check_in_time)->isPast() ? 'table-success' : ($tour->check_in_time && \Carbon\Carbon::parse($tour->check_in_time)->diffInDays(now(), false) <= 7 ? 'table-warning' : '') }}">
                            <td>
                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $tour->tour_id }}">
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-info">{{ $tour->display_id }}</strong>
                                    <small class="text-muted">ID: {{ $tour->tour_id }}</small>
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
                                    @if($tour->check_in_time)
                                        <small><strong>Start:</strong> {{ \Carbon\Carbon::parse($tour->check_in_time)->format('M d, Y') }}</small>
                                    @endif
                                    @if($tour->check_out_time)
                                        <small><strong>End:</strong> {{ \Carbon\Carbon::parse($tour->check_out_time)->format('M d, Y') }}</small>
                                    @endif
                                    @if($tour->check_in_time)
                                        @php
                                            $daysUntilTravel = \Carbon\Carbon::parse($tour->check_in_time)->diffInDays(now(), false);
                                        @endphp
                                        @if($daysUntilTravel < 0)
                                            <span class="badge bg-primary mt-1">{{ abs($daysUntilTravel) }} days to go</span>
                                        @elseif($daysUntilTravel == 0)
                                            <span class="badge bg-success mt-1">Starting Today</span>
                                        @else
                                            <span class="badge bg-success mt-1">Ready to Execute</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($tour->check_in_time && \Carbon\Carbon::parse($tour->check_in_time)->isPast())
                                    <span class="badge bg-success">
                                        <i class="ri-play-circle-line me-1"></i>Ready
                                    </span>
                                @elseif($tour->check_in_time && \Carbon\Carbon::parse($tour->check_in_time)->diffInDays(now(), false) <= 7)
                                    <span class="badge bg-warning">
                                        <i class="ri-time-line me-1"></i>Soon
                                    </span>
                                @else
                                    <span class="badge bg-info">
                                        <i class="ri-shield-check-line me-1"></i>Definite
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <span class="badge bg-light text-dark" title="Hotel Services">
                                        <i class="ri-hotel-line"></i> H
                                    </span>
                                    <span class="badge bg-light text-dark" title="Transport Services">
                                        <i class="ri-car-line"></i> T
                                    </span>
                                    <span class="badge bg-light text-dark" title="Guide Services">
                                        <i class="ri-user-line"></i> G
                                    </span>
                                    <span class="badge bg-light text-dark" title="Attraction Services">
                                        <i class="ri-camera-line"></i> A
                                    </span>
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
                                        @if($tour->check_in_time && \Carbon\Carbon::parse($tour->check_in_time)->isPast())
                                        <li>
                                            <a class="dropdown-item text-success" href="#" onclick="makeActual('{{ $tour->tour_id }}')">
                                                <i class="ri-play-circle-line me-2"></i> Make Actual
                                            </a>
                                        </li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="assignAllServices('{{ $tour->tour_id }}')">
                                                <i class="ri-team-line me-2"></i> Assign Services
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="generateJobSheet('{{ $tour->tour_id }}')">
                                                <i class="ri-file-list-line me-2"></i> Generate Job Sheet
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="viewItinerary('{{ $tour->tour_id }}')">
                                                <i class="ri-map-line me-2"></i> View Itinerary
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="sendPreTourInfo('{{ $tour->tour_id }}')">
                                                <i class="ri-mail-send-line me-2"></i> Send Pre-Tour Info
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="downloadVouchers('{{ $tour->tour_id }}')">
                                                <i class="ri-download-line me-2"></i> Download Vouchers
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="modifyBooking('{{ $tour->tour_id }}')">
                                                <i class="ri-edit-line me-2"></i> Modify Booking
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="cancelDefinite('{{ $tour->tour_id }}')">
                                                <i class="ri-close-line me-2"></i> Cancel Booking
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="ri-shield-check-line ri-48px text-muted mb-2"></i>
                                    <h6 class="text-muted">No definite bookings</h6>
                                    <p class="text-muted mb-0">All bookings are in other stages or there are no definite bookings yet.</p>
                                </div>
                            </td>
                        </tr>
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
function makeActual(tourId) {
    if (confirm('Are you sure you want to make this booking actual? This will move it to the actual bookings section.')) {
        console.log('Making booking actual', tourId);
        // Add AJAX call here
    }
}

function assignAllServices(tourId) {
    console.log('Assigning all services for tour', tourId);
    // Redirect to service assignment page
}

function generateJobSheet(tourId) {
    console.log('Generating job sheet for tour', tourId);
    // Implementation for job sheet generation
}

function viewItinerary(tourId) {
    console.log('Viewing itinerary for tour', tourId);
    // Implementation for viewing itinerary
}

function sendPreTourInfo(tourId) {
    console.log('Sending pre-tour information for tour', tourId);
    // Implementation for sending pre-tour info
}

function downloadVouchers(tourId) {
    console.log('Downloading vouchers for tour', tourId);
    // Implementation for downloading vouchers
}

function modifyBooking(tourId) {
    console.log('Modifying booking', tourId);
    // Redirect to modification page
}

function cancelDefinite(tourId) {
    if (confirm('Are you sure you want to cancel this definite booking? This may require refund processing and service cancellations.')) {
        console.log('Cancelling definite booking', tourId);
    }
}

function bulkMakeActual() {
    const selectedTours = document.querySelectorAll('.row-checkbox:checked');
    if (selectedTours.length === 0) {
        alert('Please select at least one booking to make actual.');
        return;
    }
    
    if (confirm(`Are you sure you want to make ${selectedTours.length} bookings actual?`)) {
        console.log('Bulk making actual', selectedTours.length, 'bookings');
    }
}

function assignServices() {
    const selectedTours = document.querySelectorAll('.row-checkbox:checked');
    if (selectedTours.length === 0) {
        alert('Please select at least one booking to assign services.');
        return;
    }
    
    console.log('Assigning services for', selectedTours.length, 'bookings');
}

function generateJobSheets() {
    const selectedTours = document.querySelectorAll('.row-checkbox:checked');
    if (selectedTours.length === 0) {
        alert('Please select at least one booking to generate job sheets.');
        return;
    }
    
    console.log('Generating job sheets for', selectedTours.length, 'bookings');
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('destinationFilter').value = '';
    filterTable();
}

function filterTable() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const destinationFilter = document.getElementById('destinationFilter').value;
    
    const rows = document.querySelectorAll('#toursTable tbody tr');
    
    rows.forEach(row => {
        if (row.cells.length === 1) return; // Skip empty state row
        
        let show = true;
        
        // Search filter
        if (searchTerm) {
            const tourDetails = row.cells[1].textContent.toLowerCase();
            if (!tourDetails.includes(searchTerm)) {
                show = false;
            }
        }
        
        // Status filter
        if (statusFilter) {
            const hasReadyClass = row.classList.contains('table-success');
            const hasUpcomingClass = row.classList.contains('table-warning');
            
            if (statusFilter === 'ready' && !hasReadyClass) show = false;
            if (statusFilter === 'upcoming' && !hasUpcomingClass) show = false;
            if (statusFilter === 'future' && (hasReadyClass || hasUpcomingClass)) show = false;
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
    console.log('Exporting definite bookings data...');
}

// Initialize filters
document.getElementById('searchInput').addEventListener('input', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);
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

@extends('layouts.datatablejs')
