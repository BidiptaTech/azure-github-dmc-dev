@extends('layouts.layout')
@section('title', 'Tour Details - ' . $tour->display_id)
@extends('layouts.datatablecss')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-2">
                <span class="text-muted fw-light">Bookings /</span> Tour Details
            </h4>
            <p class="text-muted">Detailed information for {{ $tour->display_id }}</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" onclick="history.back()">
                <i class="ri-arrow-left-line me-1"></i> Back
            </button>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="ri-settings-line me-1"></i> Actions
                </button>
                <ul class="dropdown-menu">
                    {{-- <li><a class="dropdown-item" href="#" onclick="editTour()"><i class="ri-edit-line me-2"></i> Edit Tour</a></li> --}}
                    <li><a class="dropdown-item" href="#" onclick="printTour()"><i class="ri-printer-line me-2"></i> Print Details</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportTour()"><i class="ri-download-line me-2"></i> Export PDF</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Tour Overview -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tour Overview</h5>
                    <span class="badge bg-{{ $tour->tour_status == 'Actual' ? 'success' : ($tour->tour_status == 'Confirmed' ? 'primary' : ($tour->tour_status == 'Definite' ? 'info' : 'warning')) }} fs-6">
                        {{ $tour->tour_status }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Tour Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Display ID:</strong></td>
                                    <td>
                                        <span class="badge bg-primary text-white rounded-pill shadow fw-semibold">
                                            {{ $tour->display_id }}
                                        </span>
                                    </td>                                    
                                </tr>
                                <tr>
                                    <td><strong>Tour ID:</strong></td>
                                    <td>
                                        <span class="badge bg-primary text-white rounded-pill shadow fw-semibold">
                                            {{ $tour->tour_id }}
                                        </span>
                                    </td>
                                </tr>
                                @if($tour->multi_enq_id)
                                <tr>
                                    <td><strong>Multi Enquiry ID:</strong></td>
                                    <td>
                                        <span class="badge bg-primary text-white rounded-pill shadow fw-semibold">
                                            {{ $tour->multi_enq_id }}
                                        </span>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $tour->tour_status == 'Actual' ? 'success' : ($tour->tour_status == 'Confirmed' ? 'primary' : ($tour->tour_status == 'Definite' ? 'info' : 'warning')) }}">
                                            {{ $tour->tour_status }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Destination</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Country:</strong></td>
                                    <td>{{ $tour->destination ?? 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>City:</strong></td>
                                    <td>{{ $tour->city ?? 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Check-in:</strong></td>
                                    <td>{{ $tour->check_in_time ? \Carbon\Carbon::parse($tour->check_in_time)->format('D, M d, Y h:i A') : 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Check-out:</strong></td>
                                    <td>{{ $tour->check_out_time ? \Carbon\Carbon::parse($tour->check_out_time)->format('D, M d, Y h:i A') : 'Not specified' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guest Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Guest Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="avatar avatar-xl mx-auto mb-2">
                                    <div class="avatar-initial bg-primary rounded-circle">
                                        <i class="ri-user-line ri-24px"></i>
                                    </div>
                                </div>
                                <h5 class="mb-1">{{ $tour->adult ?? 0 }}</h5>
                                <p class="text-muted mb-0">Adults</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="avatar avatar-xl mx-auto mb-2">
                                    <div class="avatar-initial bg-warning rounded-circle">
                                        <i class="ri-user-smile-line ri-24px"></i>
                                    </div>
                                </div>
                                <h5 class="mb-1">{{ $tour->child ?? 0 }}</h5>
                                <p class="text-muted mb-0">Children</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="avatar avatar-xl mx-auto mb-2">
                                    <div class="avatar-initial bg-info rounded-circle">
                                        <i class="ri-group-line ri-24px"></i>
                                    </div>
                                </div>
                                <h5 class="mb-1">{{ ($tour->adult ?? 0) + ($tour->child ?? 0) }}</h5>
                                <p class="text-muted mb-0">Total Guests</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Details (Only for Actual bookings) -->
            @if($tour->tour_status == 'Actual' && !empty($tour->parsed_payment_details))
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Payment Details</h5>
                </div>
                <div class="card-body">
                    @php
                        $totalPaid = 0;
                    @endphp
                    <div class="row">
                        @foreach($tour->parsed_payment_details as $index => $payment)
                        @php
                            $totalPaid += floatval($payment['amount'] ?? 0);
                        @endphp
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0">Payment #{{ $index + 1 }}</h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Amount</small>
                                            <h6 class="text-success">${{ number_format(floatval($payment['amount'] ?? 0)) }}</h6>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Type</small>
                                            <div>
                                                <span class="badge bg-primary">{{ ucfirst($payment['payment_type'] ?? 'N/A') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Date</small>
                                            <div>{{ $payment['payment_date'] ?? 'N/A' }}</div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Status</small>
                                            <div>
                                                <span class="badge bg-{{ ($payment['status'] ?? 0) == 1 ? 'success' : 'warning' }}">
                                                    {{ ($payment['status'] ?? 0) == 1 ? 'Confirmed' : 'Pending' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    @if(!empty($payment['transaction_id']))
                                    <hr class="my-2">
                                    <small class="text-muted">Transaction ID</small>
                                    <div><code>{{ $payment['transaction_id'] }}</code></div>
                                    @endif
                                    @if(!empty($payment['remarks']))
                                    <hr class="my-2">
                                    <small class="text-muted">Remarks</small>
                                    <div>{{ $payment['remarks'] }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Payment Summary -->
                    <div class="alert alert-info">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="alert-heading mb-1">Payment Summary</h6>
                                <p class="mb-0">Total amount received from {{ count($tour->parsed_payment_details) }} payment(s)</p>
                            </div>
                            <div class="col-auto">
                                <h4 class="text-info mb-0">${{ number_format($totalPaid) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Timeline -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Tour Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Tour Created</h6>
                                <p class="text-muted mb-0">{{ $tour->created_at->format('D, M d, Y h:i A') }}</p>
                                <small class="text-muted">{{ $tour->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        
                        @if($tour->updated_at != $tour->created_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Last Updated</h6>
                                <p class="text-muted mb-0">{{ $tour->updated_at->format('D, M d, Y h:i A') }}</p>
                                <small class="text-muted">{{ $tour->updated_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        @endif
                        
                        @if($tour->check_in_time)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-{{ \Carbon\Carbon::parse($tour->check_in_time)->isPast() ? 'success' : 'info' }}"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">{{ \Carbon\Carbon::parse($tour->check_in_time)->isPast() ? 'Tour Started' : 'Tour Start Date' }}</h6>
                                <p class="text-muted mb-0">{{ \Carbon\Carbon::parse($tour->check_in_time)->format('D, M d, Y h:i A') }}</p>
                                <small class="text-muted">
                                    @if(\Carbon\Carbon::parse($tour->check_in_time)->isPast())
                                        Started {{ \Carbon\Carbon::parse($tour->check_in_time)->diffForHumans() }}
                                    @else
                                        Starts {{ \Carbon\Carbon::parse($tour->check_in_time)->diffForHumans() }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        @endif
                        
                        @if($tour->check_out_time)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-{{ \Carbon\Carbon::parse($tour->check_out_time)->isPast() ? 'success' : 'secondary' }}"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">{{ \Carbon\Carbon::parse($tour->check_out_time)->isPast() ? 'Tour Completed' : 'Tour End Date' }}</h6>
                                <p class="text-muted mb-0">{{ \Carbon\Carbon::parse($tour->check_out_time)->format('D, M d, Y h:i A') }}</p>
                                <small class="text-muted">
                                    @if(\Carbon\Carbon::parse($tour->check_out_time)->isPast())
                                        Completed {{ \Carbon\Carbon::parse($tour->check_out_time)->diffForHumans() }}
                                    @else
                                        Ends {{ \Carbon\Carbon::parse($tour->check_out_time)->diffForHumans() }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            {{-- <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($tour->tour_status == 'New Enquiry')
                            <button class="btn btn-outline-primary" onclick="moveToFollowUp()">
                                <i class="ri-arrow-right-line me-1"></i> Move to Follow Up
                            </button>
                            <button class="btn btn-outline-warning" onclick="markAsTentative()">
                                <i class="ri-bookmark-line me-1"></i> Mark as Tentative
                            </button>
                        @elseif($tour->tour_status == 'Prospect')
                            <button class="btn btn-outline-warning" onclick="markAsTentative()">
                                <i class="ri-bookmark-line me-1"></i> Mark as Tentative
                            </button>
                            <button class="btn btn-outline-success" onclick="confirmBooking()">
                                <i class="ri-check-double-line me-1"></i> Confirm Booking
                            </button>
                        @elseif($tour->tour_status == 'Tentative')
                            <button class="btn btn-outline-success" onclick="confirmBooking()">
                                <i class="ri-check-double-line me-1"></i> Confirm Booking
                            </button>
                        @elseif($tour->tour_status == 'Confirmed')
                            <button class="btn btn-outline-info" onclick="makeDefinite()">
                                <i class="ri-arrow-right-line me-1"></i> Make Definite
                            </button>
                            <button class="btn btn-outline-primary" onclick="generateVoucher()">
                                <i class="ri-file-text-line me-1"></i> Generate Voucher
                            </button>
                        @elseif($tour->tour_status == 'Definite')
                            <button class="btn btn-outline-success" onclick="makeActual()">
                                <i class="ri-play-circle-line me-1"></i> Make Actual
                            </button>
                            <button class="btn btn-outline-info" onclick="assignServices()">
                                <i class="ri-team-line me-1"></i> Assign Services
                            </button>
                        @elseif($tour->tour_status == 'Actual')
                            <button class="btn btn-outline-primary" onclick="addPayment()">
                                <i class="ri-money-dollar-circle-line me-1"></i> Add Payment
                            </button>
                            <button class="btn btn-outline-success" onclick="generateInvoice()">
                                <i class="ri-file-text-line me-1"></i> Generate Invoice
                            </button>
                        @endif
                        
                        <hr>
                        <button class="btn btn-outline-secondary" onclick="sendEmail()">
                            <i class="ri-mail-send-line me-1"></i> Send Email
                        </button>
                        <button class="btn btn-outline-info" onclick="viewItinerary()">
                            <i class="ri-map-line me-1"></i> View Itinerary
                        </button>
                    </div>
                </div>
            </div> --}}

            <!-- Tour Statistics -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Tour Statistics</h5>
                </div>
                <div class="card-body">
                    @if($tour->check_in_time && $tour->check_out_time)
                        @php
                            $duration = \Carbon\Carbon::parse($tour->check_in_time)->diffInDays(\Carbon\Carbon::parse($tour->check_out_time)) + 1;
                            $checkIn = \Carbon\Carbon::parse($tour->check_in_time);
                            $daysUntilStart = floor($checkIn->floatDiffInDays(now(), false));
                        @endphp
                        <div class="row text-center">
                            <div class="col-6">
                                <h4 class="text-primary">{{ $duration }}</h4>
                                <small class="text-muted">Duration (Days)</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-{{ $daysUntilStart < 0 ? 'success' : 'warning' }}">
                                    {{ abs($daysUntilStart) }}
                                </h4>
                                <small class="text-muted">
                                    {{ $daysUntilStart < 0 ? 'Days to Start' : 'Days Since Start' }}
                                </small>
                            </div>
                        </div>
                    @endif
                    
                    <hr>
                    <div class="row text-center">
                        <div class="col-12">
                            <h4 class="text-info">{{ ($tour->adult ?? 0) + ($tour->child ?? 0) }}</h4>
                            <small class="text-muted">Total Guests</small>
                        </div>
                    </div>
                    
                    @if($tour->tour_status == 'Actual' && !empty($tour->parsed_payment_details))
                        <hr>
                        <div class="row text-center">
                            <div class="col-12">
                                @php
                                    $totalPaid = array_sum(array_column($tour->parsed_payment_details, 'amount'));
                                @endphp
                                <h4 class="text-success">${{ number_format($totalPaid) }}</h4>
                                <small class="text-muted">Total Payments</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Status History -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Status History</h5>
                </div>
                <div class="card-body">
                    <div class="timeline timeline-sm">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Current Status</h6>
                                <span class="badge bg-{{ $tour->tour_status == 'Actual' ? 'success' : ($tour->tour_status == 'Confirmed' ? 'primary' : ($tour->tour_status == 'Definite' ? 'info' : 'warning')) }}">
                                    {{ $tour->tour_status }}
                                </span>
                                <div><small class="text-muted">Since {{ $tour->updated_at->format('D, M d, Y') }}</small></div>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="text-center mt-3">
                        <button class="btn btn-sm btn-outline-secondary" onclick="viewFullHistory()">
                            <i class="ri-history-line me-1"></i> View Full History
                        </button>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-sm .timeline-marker {
    width: 8px;
    height: 8px;
    left: -23px;
}

.timeline-content {
    padding-left: 10px;
}
</style>

<script>
function moveToFollowUp() {
    if (confirm('Move this enquiry to Follow Up status?')) {
        console.log('Moving to follow up');
        // Add AJAX call
    }
}

function markAsTentative() {
    if (confirm('Mark this booking as Tentative?')) {
        console.log('Marking as tentative');
        // Add AJAX call
    }
}

function confirmBooking() {
    if (confirm('Confirm this booking?')) {
        console.log('Confirming booking');
        // Add AJAX call
    }
}

function makeDefinite() {
    if (confirm('Make this booking definite?')) {
        console.log('Making definite');
        // Add AJAX call
    }
}

function makeActual() {
    if (confirm('Make this booking actual?')) {
        console.log('Making actual');
        // Add AJAX call
    }
}

function generateVoucher() {
    console.log('Generating voucher');
    // Implementation
}

function assignServices() {
    console.log('Assigning services');
    // Implementation
}

function addPayment() {
    console.log('Adding payment');
    // Implementation
}

function generateInvoice() {
    console.log('Generating invoice');
    // Implementation
}

function sendEmail() {
    console.log('Sending email');
    // Implementation
}

function viewItinerary() {
    console.log('Viewing itinerary');
    // Implementation
}

function editTour() {
    console.log('Editing tour');
    // Implementation
}

function printTour() {
    window.print();
}

function exportTour() {
    console.log('Exporting tour to PDF');
    
    // Get the tour ID from the current page
    const tourId = {{ $tour->tour_id }};
    
    // Create the export URL
    const exportUrl = '/bookings/export-tour-pdf/' + tourId;
    
    // Open the PDF export in a new window to trigger download
    window.open(exportUrl, '_blank');
}

function viewFullHistory() {
    console.log('Viewing full status history');
    // Implementation
}
</script>
@endsection

@extends('layouts.datatablejs')
