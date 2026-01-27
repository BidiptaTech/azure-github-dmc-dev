@extends('layouts.layout')
@section('title', 'Tour Details - ' . $tour->display_id)
@extends('layouts.datatablecss')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y tour-details-compact">
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
                    {{-- <li><a class="dropdown-item" href="#" onclick="exportTour()"><i class="ri-download-line me-2"></i> Export PDF</a></li> --}}
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
                    @php
                        $overviewStatusClass = match($tour->tour_status) {
                            'Actual' => 'success',
                            'Confirmed' => 'primary',
                            'Definite' => 'info',
                            'Prospect', 'Tentative' => 'warning',
                            'Refunded', 'Refund - Pending' => 'olive',
                            'Cancelled' => 'danger',
                            default => 'secondary'
                        };
                    @endphp
                    <span class="badge bg-{{ $overviewStatusClass }} fs-6">
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
                                        <span class="badge bg-{{ $overviewStatusClass }}">
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
                    @php
                        $adultCount  = $tour->adult  ?? 0;
                        $childCount  = $tour->child  ?? 0;
                        $infantCount = $tour->infant ?? 0;
                        $totalGuests = $adultCount + $childCount + $infantCount;
                    @endphp
                    <div class="row text-center">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="guest-summary-item">
                                <div class="avatar avatar-xl mx-auto mb-2">
                                    <div class="avatar-initial bg-primary rounded-circle">
                                        <i class="ri-user-line ri-24px"></i>
                                    </div>
                                </div>
                                <h5 class="mb-1">{{ $adultCount }}</h5>
                                <p class="text-muted mb-0">Adults</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="guest-summary-item">
                                <div class="avatar avatar-xl mx-auto mb-2">
                                    <div class="avatar-initial bg-warning rounded-circle">
                                        <i class="ri-user-smile-line ri-24px"></i>
                                    </div>
                                </div>
                                <h5 class="mb-1">{{ $childCount }}</h5>
                                <p class="text-muted mb-0">Children</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="guest-summary-item">
                                <div class="avatar avatar-xl mx-auto mb-2">
                                    <div class="avatar-initial bg-success rounded-circle">
                                        <i class="ri-bear-smile-line ri-24px"></i>
                                    </div>
                                </div>
                                <h5 class="mb-1">{{ $infantCount }}</h5>
                                <p class="text-muted mb-0">Infants</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="guest-summary-item">
                                <div class="avatar avatar-xl mx-auto mb-2">
                                    <div class="avatar-initial bg-info rounded-circle">
                                        <i class="ri-group-line ri-24px"></i>
                                    </div>
                                </div>
                                <h5 class="mb-1">{{ $totalGuests }}</h5>
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

            <!-- Tour Timeline -->
            <div class="card timeline-card shadow-sm">
                <div class="card-header border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="timeline-header-icon">
                            <i class="ri-calendar-line"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-0 fw-semibold">Tour Timeline</h5>
                            <small class="text-muted">Key dates for this booking</small>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-4">
                    <div class="modern-timeline">
                        <!-- Tour Created -->
                        <div class="timeline-event">
                            <div class="timeline-badge bg-primary">
                                <i class="ri-add-circle-line"></i>
                            </div>
                            <div class="timeline-panel">
                                <div class="timeline-heading">
                                    <h6 class="timeline-title mb-1">Tour Created</h6>
                                </div>
                                <div class="timeline-body">
                                    <p class="mb-1 text-muted">
                                        <i class="ri-calendar-event-line me-1"></i>
                                        {{ $tour->created_at->format('D, M d, Y') }}
                                    </p>
                                    <p class="mb-0">
                                        <i class="ri-time-line me-1 text-muted"></i>
                                        <span class="text-muted">{{ $tour->created_at->format('h:i A') }}</span>
                                        <span class="badge bg-label-secondary ms-2">{{ $tour->created_at->diffForHumans() }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Last Updated -->
                        @if($tour->updated_at != $tour->created_at)
                        <div class="timeline-event">
                            <div class="timeline-badge bg-warning">
                                <i class="ri-edit-line"></i>
                            </div>
                            <div class="timeline-panel">
                                <div class="timeline-heading">
                                    <h6 class="timeline-title mb-1">Last Updated</h6>
                                </div>
                                <div class="timeline-body">
                                    <p class="mb-1 text-muted">
                                        <i class="ri-calendar-event-line me-1"></i>
                                        {{ $tour->updated_at->format('D, M d, Y') }}
                                    </p>
                                    <p class="mb-0">
                                        <i class="ri-time-line me-1 text-muted"></i>
                                        <span class="text-muted">{{ $tour->updated_at->format('h:i A') }}</span>
                                        <span class="badge bg-label-warning ms-2">{{ $tour->updated_at->diffForHumans() }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Tour Start Date -->
                        @if($tour->check_in_time)
                        @php
                            $checkInTime = \Carbon\Carbon::parse($tour->check_in_time);
                            $isStarted = $checkInTime->isPast();
                        @endphp
                        <div class="timeline-event">
                            <div class="timeline-badge bg-{{ $isStarted ? 'success' : 'info' }}">
                                <i class="ri-flight-takeoff-line"></i>
                            </div>
                            <div class="timeline-panel">
                                <div class="timeline-heading">
                                    <h6 class="timeline-title mb-1">{{ $isStarted ? 'Tour Started' : 'Tour Start Date' }}</h6>
                                </div>
                                <div class="timeline-body">
                                    <p class="mb-1 text-muted">
                                        <i class="ri-calendar-event-line me-1"></i>
                                        {{ $checkInTime->format('D, M d, Y') }}
                                    </p>
                                    <p class="mb-0">
                                        <i class="ri-time-line me-1 text-muted"></i>
                                        <span class="text-muted">{{ $checkInTime->format('h:i A') }}</span>
                                        <span class="badge bg-label-{{ $isStarted ? 'success' : 'info' }} ms-2">
                                            {{ $isStarted ? 'Started ' : 'Starts ' }}{{ $checkInTime->diffForHumans() }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Tour End Date -->
                        @if($tour->check_out_time)
                        @php
                            $checkOutTime = \Carbon\Carbon::parse($tour->check_out_time);
                            $isCompleted = $checkOutTime->isPast();
                        @endphp
                        <div class="timeline-event">
                            <div class="timeline-badge bg-{{ $isCompleted ? 'success' : 'secondary' }}">
                                <i class="ri-flag-line"></i>
                            </div>
                            <div class="timeline-panel">
                                <div class="timeline-heading">
                                    <h6 class="timeline-title mb-1">{{ $isCompleted ? 'Tour Completed' : 'Tour End Date' }}</h6>
                                </div>
                                <div class="timeline-body">
                                    <p class="mb-1 text-muted">
                                        <i class="ri-calendar-event-line me-1"></i>
                                        {{ $checkOutTime->format('D, M d, Y') }}
                                    </p>
                                    <p class="mb-0">
                                        <i class="ri-time-line me-1 text-muted"></i>
                                        <span class="text-muted">{{ $checkOutTime->format('h:i A') }}</span>
                                        <span class="badge bg-label-{{ $isCompleted ? 'success' : 'secondary' }} ms-2">
                                            {{ $isCompleted ? 'Ended ' : 'Ends ' }}{{ $checkOutTime->diffForHumans() }}
                                        </span>
                                    </p>
                                </div>
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
                            @php
                                $adultCountStats  = $tour->adult  ?? 0;
                                $childCountStats  = $tour->child  ?? 0;
                                $infantCountStats = $tour->infant ?? 0;
                                $totalGuestsStats = $adultCountStats + $childCountStats + $infantCountStats;
                            @endphp
                            <h4 class="text-info">{{ $totalGuestsStats }}</h4>
                            <small class="text-muted">
                                Total Guests 
                                <span class="d-block mt-1">
                                    <span class="badge bg-label-primary me-1">A: {{ $adultCountStats }}</span>
                                    <span class="badge bg-label-warning me-1">C: {{ $childCountStats }}</span>
                                    <span class="badge bg-label-success">I: {{ $infantCountStats }}</span>
                                </span>
                            </small>
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
            <div class="card timeline-card shadow-sm">
                <div class="card-header border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-start w-100">
                        <div class="d-flex align-items-center">
                            <div class="timeline-header-icon">
                                <i class="ri-history-line"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-0 fw-semibold">Status History</h5>
                                <small class="text-muted">Full lifecycle of this tour</small>
                            </div>
                        </div>
                        <div class="text-end">
                            @php
                                $statusBadgeClass = match($tour->tour_status) {
                                    'Actual' => 'success',
                                    'Confirmed' => 'primary',
                                    'Definite' => 'info',
                                    'Prospect', 'Tentative' => 'warning',
                                    'Refunded', 'Refund - Pending' => 'olive',
                                    'Cancelled' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusBadgeClass }} mb-1">{{ $tour->tour_status }}</span>
                            <div>
                                <small class="text-muted d-block">Updated {{ $tour->updated_at->format('M d, Y') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-4">
                    @php
                        $trackDetails = [];
                        if (!empty($tour->track_details)) {
                            $decoded = is_array($tour->track_details)
                                ? $tour->track_details
                                : json_decode($tour->track_details, true);
                            if (is_array($decoded)) {
                                $trackDetails = $decoded;
                            }
                        }

                        // Sort by date ascending
                        if (!empty($trackDetails)) {
                            usort($trackDetails, function ($a, $b) {
                                $ad = $a['date'] ?? null;
                                $bd = $b['date'] ?? null;
                                if ($ad === $bd) return 0;
                                if (!$ad) return -1;
                                if (!$bd) return 1;
                                return strtotime($ad) <=> strtotime($bd);
                            });
                        }

                        // Helper functions
                        function getStatusColor($status) {
                            return match(true) {
                                str_contains($status, 'Actual') => 'success',
                                str_contains($status, 'Confirmed') => 'primary',
                                str_contains($status, 'Definite') => 'info',
                                str_contains($status, 'Prospect') || str_contains($status, 'Tentative') => 'warning',
                                str_contains($status, 'Refund') => 'olive',
                                str_contains($status, 'Cancel') => 'danger',
                                default => 'secondary'
                            };
                        }

                        function getStatusIcon($status) {
                            return match(true) {
                                str_contains($status, 'Actual') => 'ri-check-double-line',
                                str_contains($status, 'Confirmed') => 'ri-checkbox-circle-line',
                                str_contains($status, 'Definite') => 'ri-checkbox-line',
                                str_contains($status, 'Prospect') || str_contains($status, 'Tentative') => 'ri-question-line',
                                str_contains($status, 'Cancel') => 'ri-close-circle-line',
                                str_contains($status, 'Refund') => 'ri-refund-line',
                                default => 'ri-record-circle-line'
                            };
                        }
                    @endphp

                    <div class="modern-timeline">
                        <!-- Initial Tour Created -->
                        <div class="timeline-event">
                            <div class="timeline-badge bg-secondary">
                                <i class="ri-add-circle-line"></i>
                            </div>
                            <div class="timeline-panel">
                                <div class="timeline-heading">
                                    <h6 class="timeline-title mb-1">Tour Created</h6>
                                </div>
                                <div class="timeline-body">
                                    <p class="mb-1 text-muted">
                                        <i class="ri-calendar-event-line me-1"></i>
                                        {{ $tour->created_at->format('D, M d, Y') }}
                                    </p>
                                    <p class="mb-0">
                                        <i class="ri-time-line me-1 text-muted"></i>
                                        <span class="text-muted">{{ $tour->created_at->format('h:i A') }}</span>
                                        <span class="badge bg-label-secondary ms-2">{{ $tour->created_at->diffForHumans() }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Status Changes from track_details -->
                        @if(!empty($trackDetails))
                            @foreach($trackDetails as $item)
                                @php
                                    $from = $item['from'] ?? null;
                                    $to = $item['to'] ?? null;
                                    $dateString = $item['date'] ?? null;
                                    $date = $dateString ? \Carbon\Carbon::parse($dateString) : null;
                                    $statusLabel = $to ?? $from ?? 'Status Change';
                                    $color = getStatusColor($statusLabel);
                                    $icon = getStatusIcon($statusLabel);
                                @endphp
                                <div class="timeline-event">
                                    <div class="timeline-badge bg-{{ $color }}">
                                        <i class="{{ $icon }}"></i>
                                    </div>
                                    <div class="timeline-panel">
                                        <div class="timeline-heading">
                                            <h6 class="timeline-title mb-1">
                                                @if(empty($from))
                                                    <span class="badge bg-label-{{ $color }}">{{ $to }}</span>
                                                @else
                                                    <span class="text-muted">{{ $from }}</span>
                                                    <i class="ri-arrow-right-line mx-1"></i>
                                                    <span class="badge bg-label-{{ $color }}">{{ $to }}</span>
                                                @endif
                                            </h6>
                                        </div>
                                        @if($date)
                                        <div class="timeline-body">
                                            <p class="mb-1 text-muted">
                                                <i class="ri-calendar-event-line me-1"></i>
                                                {{ $date->format('D, M d, Y') }}
                                            </p>
                                            <p class="mb-0">
                                                <i class="ri-time-line me-1 text-muted"></i>
                                                <span class="text-muted">{{ $date->format('h:i A') }}</span>
                                                <span class="badge bg-label-{{ $color }} ms-2">{{ $date->diffForHumans() }}</span>
                                            </p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <!-- Fallback if no track history -->
                            <div class="timeline-event">
                                <div class="timeline-badge bg-{{ getStatusColor($tour->tour_status) }}">
                                    <i class="{{ getStatusIcon($tour->tour_status) }}"></i>
                                </div>
                                <div class="timeline-panel">
                                    <div class="timeline-heading">
                                        <h6 class="timeline-title mb-1">Current Status</h6>
                                    </div>
                                    <div class="timeline-body">
                                        <span class="badge bg-{{ getStatusColor($tour->tour_status) }} mb-2">{{ $tour->tour_status }}</span>
                                        <p class="mb-0 text-muted">
                                            <small>Since {{ $tour->updated_at->format('M d, Y') }}</small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ============================================
   MODERN TIMELINE CARD DESIGN
   ============================================ */

/* Timeline Card */
.timeline-card {
    border: none;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.timeline-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
    transform: translateY(-2px);
}

.timeline-card .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 1.25rem 1.5rem;
}

.timeline-card .card-header h5,
.timeline-card .card-header small,
.timeline-card .card-header .text-muted {
    color: #fff !important;
}

.timeline-header-icon {
    width: 48px;
    height: 48px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #fff;
    backdrop-filter: blur(10px);
}

/* Modern Timeline Container */
.modern-timeline {
    position: relative;
    padding: 0;
    margin: 0;
}

/* Vertical Connector Line */
.modern-timeline::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 10px;
    bottom: 10px;
    width: 2px;
    background: linear-gradient(180deg, #e9ecef 0%, #dee2e6 100%);
    z-index: 0;
}

/* Timeline Event */
.timeline-event {
    position: relative;
    display: flex;
    align-items: flex-start;
    margin-bottom: 1.75rem;
    padding-left: 0;
}

.timeline-event:last-child {
    margin-bottom: 0;
}

.timeline-event:last-child .modern-timeline::before {
    display: none;
}

/* Timeline Badge (Icon Circle) */
.timeline-badge {
    position: relative;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #fff;
    flex-shrink: 0;
    z-index: 1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.timeline-event:hover .timeline-badge {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

/* Timeline Panel (Content Area) */
.timeline-panel {
    flex: 1;
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1rem 1.25rem;
    margin-left: 1rem;
    position: relative;
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.timeline-event:hover .timeline-panel {
    background: #fff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transform: translateX(4px);
}

/* Arrow pointing to badge */
.timeline-panel::before {
    content: '';
    position: absolute;
    left: -8px;
    top: 12px;
    width: 0;
    height: 0;
    border-top: 8px solid transparent;
    border-bottom: 8px solid transparent;
    border-right: 8px solid #f8f9fa;
    transition: border-color 0.3s ease;
}

.timeline-event:hover .timeline-panel::before {
    border-right-color: #fff;
}

/* Timeline Heading */
.timeline-heading {
    margin-bottom: 0.5rem;
}

.timeline-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
    line-height: 1.4;
}

/* Timeline Body */
.timeline-body p {
    font-size: 0.875rem;
    margin-bottom: 0.35rem;
    color: #6c757d;
}

.timeline-body p:last-child {
    margin-bottom: 0;
}

.timeline-body i {
    font-size: 14px;
    opacity: 0.8;
}

/* Badge Styles */
.timeline-body .badge,
.timeline-heading .badge {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.35rem 0.65rem;
    border-radius: 6px;
}

/* Guest Info Refinements */
.guest-summary-item h5 {
    font-weight: 600;
    font-size: 1.5rem;
    color: #2c3e50;
}

.guest-summary-item p {
    font-size: 0.875rem;
    color: #6c757d;
}

.guest-summary-item .avatar-initial {
    transition: all 0.3s ease;
}

.guest-summary-item:hover .avatar-initial {
    transform: scale(1.05);
}

/* Responsive Design */
@media (max-width: 768px) {
    .timeline-header-icon {
        width: 40px;
        height: 40px;
        font-size: 20px;
    }

    .timeline-badge {
        width: 36px;
        height: 36px;
        font-size: 16px;
    }

    .timeline-panel {
        padding: 0.875rem 1rem;
        margin-left: 0.75rem;
    }

    .modern-timeline::before {
        left: 18px;
    }
}

/* Print Styles */
@media print {
    .timeline-card {
        box-shadow: none !important;
        page-break-inside: avoid;
    }

    .timeline-event {
        page-break-inside: avoid;
    }
}

/* Smooth Animations */
.timeline-event {
    animation: fadeInUp 0.5s ease-out;
    animation-fill-mode: both;
}

.timeline-event:nth-child(1) { animation-delay: 0.1s; }
.timeline-event:nth-child(2) { animation-delay: 0.2s; }
.timeline-event:nth-child(3) { animation-delay: 0.3s; }
.timeline-event:nth-child(4) { animation-delay: 0.4s; }
.timeline-event:nth-child(5) { animation-delay: 0.5s; }

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Status Badge in Header */
.timeline-card .card-header .badge {
    font-size: 0.75rem;
    padding: 0.45rem 0.85rem;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

/* Custom Olive Color for Refund Status */
.bg-olive {
    background-color: #556b2f !important; /* olive green */
    color: #fff !important;
}

.bg-label-olive {
    background-color: rgba(85, 107, 47, 0.12) !important;
    color: #556b2f !important;
    border: 1px solid rgba(85, 107, 47, 0.35);
}

/* ============================================
   COMPACT DESKTOP LAYOUT FOR TOUR DETAILS
   ============================================ */

@media (min-width: 992px) {
    .tour-details-compact {
        font-size: 0.9rem;
    }

    .tour-details-compact h4,
    .tour-details-compact h5 {
        font-size: 1rem;
    }

    .tour-details-compact .card-header {
        padding: 0.5rem 0.9rem;
    }

    .tour-details-compact .card-body {
        padding: 0.75rem 0.9rem;
    }

    .tour-details-compact .table td,
    .tour-details-compact .table th {
        padding: 0.3rem 0.4rem;
        font-size: 0.85rem;
    }

    .tour-details-compact .guest-summary-item h5 {
        font-size: 1.2rem;
    }

    .tour-details-compact .avatar.avatar-xl {
        width: 2.5rem;
        height: 2.5rem;
    }

    .tour-details-compact .avatar.avatar-xl .avatar-initial i {
        font-size: 1.1rem;
    }

    .tour-details-compact .timeline-card .card-header {
        padding: 0.75rem 1rem;
    }

    .tour-details-compact .timeline-panel {
        padding: 0.75rem 0.9rem;
        margin-left: 0.75rem;
    }

    .tour-details-compact .timeline-event {
        margin-bottom: 1.1rem;
    }
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
