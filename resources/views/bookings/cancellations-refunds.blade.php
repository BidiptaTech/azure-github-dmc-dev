@extends('layouts.layout')
@section('title', 'Cancellations & Refunds')
@extends('layouts.datatablecss')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-2">
                <span class="text-muted fw-light">Bookings /</span> Cancellations & Refunds
            </h4>
            <p class="text-muted">Manage cancelled bookings and refund processing</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-danger fs-6">
                <i class="ri-close-circle-line me-1"></i>
                {{ $tours->total() }} Cancelled
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
                            <p class="text-muted mb-0">Total Cancelled</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-danger rounded">
                                <i class="ri-close-circle-line ri-24px"></i>
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
                            @php
                                $pendingRefunds = $tours->filter(function($tour) {
                                    if (empty($tour->payment_details)) return false;
                                    $payments = json_decode($tour->payment_details, true);
                                    return is_array($payments) && count($payments) > 0;
                                })->count();
                            @endphp
                            <h5 class="card-title mb-1">{{ $pendingRefunds }}</h5>
                            <p class="text-muted mb-0">Refund Required</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-warning rounded">
                                <i class="ri-refund-line ri-24px"></i>
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
                            <h5 class="card-title mb-1">{{ $tours->where('updated_at', '>=', now()->today())->count() }}</h5>
                            <p class="text-muted mb-0">Today</p>
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
                            @php
                                $totalRefundAmount = 0;
                                foreach($tours as $tour) {
                                    if ($tour->payment_details) {
                                        $payments = json_decode($tour->payment_details, true);
                                        if (is_array($payments)) {
                                            foreach($payments as $payment) {
                                                $totalRefundAmount += floatval($payment['amount'] ?? 0);
                                            }
                                        }
                                    }
                                }
                            @endphp
                            <h5 class="card-title mb-1">${{ number_format($totalRefundAmount) }}</h5>
                            <p class="text-muted mb-0">Refund Amount</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-secondary rounded">
                                <i class="ri-money-dollar-circle-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tours Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Cancelled Bookings List</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-warning" onclick="processRefunds()">
                    <i class="ri-refund-line me-1"></i> Process Refunds
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
                            <th>Original Dates</th>
                            <th>Payment Details</th>
                            <th>Cancellation Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tours as $tour)
                        @php
                            $paymentDetails = [];
                            $totalAmount = 0;
                            
                            if ($tour->payment_details) {
                                try {
                                    $paymentDetails = json_decode($tour->payment_details, true) ?? [];
                                    if (is_array($paymentDetails)) {
                                        foreach($paymentDetails as $payment) {
                                            $totalAmount += floatval($payment['amount'] ?? 0);
                                        }
                                    }
                                } catch (\Exception $e) {
                                    $paymentDetails = [];
                                }
                            }
                        @endphp
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $tour->tour_id }}">
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-danger">{{ $tour->display_id }}</strong>
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
                                    @if(!$tour->check_in_time && !$tour->check_out_time)
                                        <span class="text-muted">Not specified</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    @if($totalAmount > 0)
                                        <strong class="text-warning">${{ number_format($totalAmount) }}</strong>
                                        <small class="text-danger">Refund Required</small>
                                        <button class="btn btn-sm btn-outline-info mt-1" onclick="showRefundDetails('{{ $tour->tour_id }}')">
                                            <i class="ri-eye-line"></i> Details
                                        </button>
                                    @else
                                        <span class="text-muted">No payments</span>
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
                                        @if($totalAmount > 0)
                                        <li>
                                            <a class="dropdown-item text-warning" href="#" onclick="processRefund('{{ $tour->tour_id }}')">
                                                <i class="ri-refund-line me-2"></i> Process Refund
                                            </a>
                                        </li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="sendCancellationEmail('{{ $tour->tour_id }}')">
                                                <i class="ri-mail-send-line me-2"></i> Send Cancellation Email
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="viewCancellationReason('{{ $tour->tour_id }}')">
                                                <i class="ri-question-line me-2"></i> Cancellation Reason
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="generateRefundReport('{{ $tour->tour_id }}')">
                                                <i class="ri-file-text-line me-2"></i> Refund Report
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-success" href="#" onclick="restoreBooking('{{ $tour->tour_id }}')">
                                                <i class="ri-refresh-line me-2"></i> Restore Booking
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
                                    <i class="ri-check-circle-line ri-48px text-success mb-2"></i>
                                    <h6 class="text-success">No cancellations</h6>
                                    <p class="text-muted mb-0">Great! There are no cancelled bookings at the moment.</p>
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
function showRefundDetails(tourId) {
    console.log('Showing refund details for tour', tourId);
    // Implementation for showing refund details
}

function processRefund(tourId) {
    if (confirm('Are you sure you want to process the refund for this booking?')) {
        console.log('Processing refund for tour', tourId);
        // Implementation for processing refund
    }
}

function sendCancellationEmail(tourId) {
    console.log('Sending cancellation email for tour', tourId);
    // Implementation for sending cancellation email
}

function viewCancellationReason(tourId) {
    console.log('Viewing cancellation reason for tour', tourId);
    // Implementation for viewing cancellation reason
}

function generateRefundReport(tourId) {
    console.log('Generating refund report for tour', tourId);
    // Implementation for generating refund report
}

function restoreBooking(tourId) {
    if (confirm('Are you sure you want to restore this cancelled booking?')) {
        console.log('Restoring booking', tourId);
        // Implementation for restoring booking
    }
}

function processRefunds() {
    const selectedTours = document.querySelectorAll('.row-checkbox:checked');
    if (selectedTours.length === 0) {
        alert('Please select at least one cancelled booking to process refunds.');
        return;
    }
    
    if (confirm(`Are you sure you want to process refunds for ${selectedTours.length} bookings?`)) {
        console.log('Processing bulk refunds for', selectedTours.length, 'bookings');
    }
}

function exportData() {
    console.log('Exporting cancellations and refunds data...');
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
