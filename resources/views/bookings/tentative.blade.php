@extends('layouts.layout')
@section('title', 'Tentative Bookings')
@extends('layouts.datatablecss')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @include('bookings.partials.booking-type-tabs', [
        'type' => 'tours',
        'toursUrl' => route('bookings.tentative'),
        'packagesUrl' => route('package-bookings.follow-ups'),
    ])
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-2">
                <span class="text-muted fw-light">Bookings /</span> Tentative Bookings
            </h4>
            <p class="text-muted">Manage tentative bookings awaiting confirmation</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-warning fs-6">
                <i class="ri-bookmark-line me-1"></i>
                {{ $tours->total() }} Tentative
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
                            <p class="text-muted mb-0">Total Tentative</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-warning rounded">
                                <i class="ri-bookmark-line ri-24px"></i>
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
                            <h5 class="card-title mb-1">{{ $tours->where('check_in_time', '>=', now())->where('check_in_time', '<=', now()->addDays(30))->count() }}</h5>
                            <p class="text-muted mb-0">Next 30 Days</p>
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
                            <h5 class="card-title mb-1">{{ $tours->where('updated_at', '<', now()->subDays(3))->count() }}</h5>
                            <p class="text-muted mb-0">Pending Action</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-danger rounded">
                                <i class="ri-time-line ri-24px"></i>
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
                            <h5 class="card-title mb-1">{{ number_format(($tours->where('adult', '>', 0)->sum('adult') + $tours->where('child', '>', 0)->sum('child')) * 1500) }}</h5>
                            <p class="text-muted mb-0">Potential Revenue</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-success rounded">
                                <i class="ri-money-dollar-circle-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Required Alert -->
    @if($tours->where('updated_at', '<', now()->subDays(3))->count() > 0)
    <div class="alert alert-warning mb-4">
        <div class="d-flex align-items-center">
            <i class="ri-alarm-warning-line ri-24px me-3"></i>
            <div>
                <h6 class="alert-heading mb-1">Action Required</h6>
                <p class="mb-0">{{ $tours->where('updated_at', '<', now()->subDays(3))->count() }} tentative bookings need immediate attention to avoid cancellation.</p>
            </div>
            <button class="btn btn-warning ms-auto" onclick="showPendingActions()">
                <i class="ri-eye-line me-1"></i> View All
            </button>
        </div>
    </div>
    @endif

    <!-- Tours Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Tentative Bookings List</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-success" onclick="bulkConfirm()">
                    <i class="ri-check-double-line me-1"></i> Bulk Confirm
                </button>
                <button class="btn btn-sm btn-outline-primary" onclick="exportData()">
                    <i class="ri-download-line me-1"></i> Export
                </button>
            </div>
        </div>
        <div class="card-body">
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
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tours as $tour)
                        <tr class="{{ $tour->updated_at < now()->subDays(3) ? 'table-warning' : '' }}">
                            <td>
                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $tour->tour_id }}">
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-primary">{{ $tour->display_id }}</strong>
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
                                        <small><strong>Check-in:</strong> {{ \Carbon\Carbon::parse($tour->check_in_time)->format('M d, Y') }}</small>
                                    @endif
                                    @if($tour->check_out_time)
                                        <small><strong>Check-out:</strong> {{ \Carbon\Carbon::parse($tour->check_out_time)->format('M d, Y') }}</small>
                                    @endif
                                    @if($tour->check_in_time)
                                        @php
                                            $daysUntilTravel = \Carbon\Carbon::parse($tour->check_in_time)->diffInDays(now(), false);
                                        @endphp
                                        @if($daysUntilTravel < 0)
                                            <span class="badge bg-success mt-1">{{ abs($daysUntilTravel) }} days to go</span>
                                        @elseif($daysUntilTravel == 0)
                                            <span class="badge bg-danger mt-1">Today</span>
                                        @else
                                            <span class="badge bg-secondary mt-1">{{ $daysUntilTravel }} days ago</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($tour->updated_at < now()->subDays(3))
                                    <span class="badge bg-danger">
                                        <i class="ri-alarm-warning-line me-1"></i>Urgent
                                    </span>
                                @elseif($tour->check_in_time && \Carbon\Carbon::parse($tour->check_in_time)->diffInDays(now(), false) <= 7)
                                    <span class="badge bg-warning">
                                        <i class="ri-time-line me-1"></i>Soon
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="ri-bookmark-line me-1"></i>Tentative
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $tour->updated_at->format('M d, Y') }}</span>
                                    <small class="text-muted">{{ $tour->updated_at->diffForHumans() }}</small>
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
                                            <a class="dropdown-item text-success" href="#" onclick="confirmBooking('{{ $tour->tour_id }}')">
                                                <i class="ri-check-double-line me-2"></i> Confirm Booking
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="requestPayment('{{ $tour->tour_id }}')">
                                                <i class="ri-money-dollar-circle-line me-2"></i> Request Payment
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="sendReminder('{{ $tour->tour_id }}')">
                                                <i class="ri-mail-send-line me-2"></i> Send Reminder
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="extendDeadline('{{ $tour->tour_id }}')">
                                                <i class="ri-calendar-schedule-line me-2"></i> Extend Deadline
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="cancelBooking('{{ $tour->tour_id }}')">
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
                                    <i class="ri-bookmark-line ri-48px text-muted mb-2"></i>
                                    <h6 class="text-muted">No tentative bookings</h6>
                                    <p class="text-muted mb-0">All bookings have been confirmed or there are no tentative bookings.</p>
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
function confirmBooking(tourId) {
    if (confirm('Are you sure you want to confirm this booking? This will move it to confirmed status.')) {
        console.log('Confirming booking', tourId);
        // Add AJAX call here
    }
}

function requestPayment(tourId) {
    console.log('Requesting payment for tour', tourId);
    // Implementation for payment request
}

function sendReminder(tourId) {
    console.log('Sending reminder for tour', tourId);
    // Implementation for sending reminder
}

function extendDeadline(tourId) {
    const newDeadline = prompt('Enter new deadline (YYYY-MM-DD):');
    if (newDeadline) {
        console.log('Extending deadline for tour', tourId, 'to', newDeadline);
    }
}

function cancelBooking(tourId) {
    if (confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
        console.log('Cancelling booking', tourId);
    }
}

function bulkConfirm() {
    const selectedTours = document.querySelectorAll('.row-checkbox:checked');
    if (selectedTours.length === 0) {
        alert('Please select at least one booking to confirm.');
        return;
    }
    
    if (confirm(`Are you sure you want to confirm ${selectedTours.length} bookings?`)) {
        console.log('Bulk confirming', selectedTours.length, 'bookings');
    }
}

function showPendingActions() {
    // Filter table to show only pending actions
    console.log('Showing pending actions');
}

function exportData() {
    console.log('Exporting tentative bookings data...');
}

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
