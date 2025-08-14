@extends('layouts.layout')
@section('title', 'Confirmed Bookings')
@extends('layouts.datatablecss')

<!-- Date Range Picker CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<!-- Add SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<!-- Add SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
    /* Payment Processing Overlay */
    .payment-processing-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        color: white;
        visibility: hidden;
        opacity: 0;
        transition: opacity 0.3s, visibility 0.3s;
    }
    
    .payment-processing-overlay.active {
        visibility: visible;
        opacity: 1;
    }
    
    .payment-spinner {
        width: 80px;
        height: 80px;
        border: 8px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: #38ef7d;
        animation: spin 1s ease-in-out infinite;
        margin-bottom: 20px;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Payment validation styles */
    .is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 5px rgba(220, 53, 69, 0.3) !important;
    }

    .payment-validation-error {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 5px;
        padding: 8px 12px;
        margin-top: 5px;
        color: #721c24;
        font-size: 14px;
    }
</style>

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
                <span id="rangeCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</span>
                <span id="rangeLabel">{{ date('F') }}</span> Confirmed
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
                            <h5 class="card-title mb-1" id="statConfirmedCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</h5>
                            <p class="text-muted mb-0" id="statConfirmedLabel">{{ date('F') }} Confirmed</p>
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
                            <h5 class="card-title mb-1" id="statTodayCount">{{ $tours->where('created_at', '>=', now()->today())->count() }}</h5>
                            <p class="text-muted mb-0">Today's Confirmed</p>
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
                            <h5 class="card-title mb-1" id="statAdultsCount">{{ $tours->where('created_at', '>=', now()->startOfMonth())->where('created_at', '<=', now()->endOfMonth())->where('adult', '>', 0)->sum('adult') }}</h5>
                            <p class="text-muted mb-0" id="statAdultsLabel">{{ date('F') }} Adults</p>
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
                            <h5 class="card-title mb-1" id="statChildrenCount">{{ $tours->where('created_at', '>=', now()->startOfMonth())->where('created_at', '<=', now()->endOfMonth())->where('child', '>', 0)->sum('child') }}</h5>
                            <p class="text-muted mb-0" id="statChildrenLabel">{{ date('F') }} Children</p>
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
    {{-- </div> --}}
        {{-- <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">${{ number_format(($tours->where('adult', '>', 0)->sum('adult') + $tours->where('child', '>', 0)->sum('child'))) }}</h5>
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
        </div> --}}
    </div>

         <!-- Upcoming Tours Alert -->
     {{-- @php
         $upcomingTours = $tours->where('check_in_time', '>=', now())->where('check_in_time', '<=', now()->addDays(7));
         $upcomingCount = $upcomingTours->count();
     @endphp
     @if($upcomingCount > 0)
     <div class="alert alert-info mb-4">
         <div class="d-flex align-items-center">
             <i class="ri-calendar-event-line ri-24px me-3"></i>
             <div>
                 <h6 class="alert-heading mb-1">Upcoming Tours Next Week</h6>
                 <p class="mb-0">{{ $upcomingCount }} {{ $upcomingCount == 1 ? 'on hold booking is' : 'on hold bookings are' }} scheduled to start within the next 7 days.</p>
             </div>
             <button class="btn btn-info ms-auto" onclick="showUpcomingTours()">
                 <i class="ri-eye-line me-1"></i> View All
             </button>
         </div>
     </div>
     @endif --}}

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
                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Tour ID, Display ID...">
                </div>
                {{-- <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="On Hold">On Hold</option>
                        <option value="Starting Soon">Starting Soon</option>
                        <option value="In Progress">In Progress</option>
                    </select>
                </div> --}}
                <div class="col-md-2">
                    <label class="form-label">Destination</label>
                    <select class="form-select" id="destinationFilter">
                        <option value="">All Destinations</option>
                        @foreach($tours->pluck('destination')->unique()->filter() as $destination)
                            <option value="{{ $destination }}">{{ $destination }}</option>
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
                {{-- <div class="col-md-2">
                    <label class="form-label">Time Range</label>
                    <select class="form-select" id="timeFilter">
                        <option value="">All Time</option>
                        <option value="this_week">This Week</option>
                        <option value="next_week">Next Week</option>
                        <option value="this_month">This Month</option>
                        <option value="next_month">Next Month</option>
                    </select>
                </div> --}}
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <input type="text" class="form-control" id="dateRange" placeholder="Select date range" readonly>
                    <input type="hidden" id="dateRangeStart">
                    <input type="hidden" id="dateRangeEnd">
                </div>
            </div>
        </div>
    </div>

    <!-- Tours Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Confirmed Bookings List</h5>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-success btn-sm dropdown-toggle" type="button" id="exportDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportCopy">Copy</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportCSV">CSV</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportExcel">Excel</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportPDF">PDF</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportPrint">Print</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body">

            <div class="table-responsive">
                <table class="datatables-basic table table-bordered" id="toursTable">
                    <thead class="table-light">
                        <tr>
                            {{-- <th>
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th> --}}
                            <th>#</th>
                            <th>Tour Details</th>
                            <th>Destination</th>
                            <th>Services</th>
                            <th>Guests</th>
                            <th>Agent</th>
                            <th>Travel Dates</th>
                            <th>Payment Status</th>
                            <th>Confirmation Date</th>
                            {{-- <th>Status</th> --}}
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tours as $key => $tour)
                        <tr 
                            class="{{ $tour->check_in_time && \Carbon\Carbon::parse($tour->check_in_time)->diffInDays(now(), false) <= 7 && \Carbon\Carbon::parse($tour->check_in_time)->diffInDays(now(), false) >= 0 ? 'table-info' : '' }}"
                            data-updated-at="{{ optional($tour->updated_at)->toDateString() }}"
                            data-created-at="{{ optional($tour->created_at)->toDateString() }}"
                            data-adult="{{ (int)($tour->adult ?? 0) }}"
                            data-child="{{ (int)($tour->child ?? 0) }}"
                        >
                            {{-- <td>
                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $tour->tour_id }}">
                            </td> --}}
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
                                    <span class="fw-medium">{{ $tour->destination ?? 'N/A' }}</span>
                                    <small class="text-muted">{{ $tour->city ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    @php
                                        $svc = [
                                            'hotel' => $tour->hotel ?? 0,
                                            'attraction' => $tour->attraction ?? 0,
                                            'restaurent' => $tour->restaurent ?? 0,
                                            'travel' => $tour->travel ?? 0,
                                            'guide' => $tour->guide ?? 0,
                                            'port' => $tour->port ?? 0,
                                        ];
                                        $icons = [
                                            'hotel' => 'ri-hotel-line',
                                            'attraction' => 'ri-building-2-line',
                                            'restaurent' => 'ri-restaurant-2-line',
                                            'travel' => 'ri-bus-2-line',
                                            'guide' => 'ri-user-voice-line',
                                            'port' => 'ri-ship-line',
                                        ];
                                    @endphp
                                    @foreach($svc as $key=>$count)
                                        @if(intval($count) > 0)
                                            <span class="badge bg-light text-dark border">
                                                <i class="{{ $icons[$key] }} me-1"></i>{{ ucfirst($key) }}: {{ $count }}
                                            </span>
                                        @endif
                                    @endforeach
                                    @if(array_sum(array_map('intval', $svc)) === 0)
                                        <span class="text-muted">No services</span>
                                    @endif
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
                                @php
                                    // Calculate payment details
                                    $tourTotalPrice = 0;
                                    foreach ($tour->booking as $booking) {
                                        if (in_array($booking->status, [1, 2, 3])) { // Only count approved or declined bookings
                                            $data = is_string($booking->data) ? json_decode($booking->data, true) : $booking->data;
                                            if (is_array($data)) {
                                                foreach ($data as $item) {
                                                    if (isset($item['totalPrice'])) {
                                                        $tourTotalPrice += (float)$item['totalPrice'];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $enquiry = \App\Models\Enquiry::where('tour_id', $tour->tour_id)->where('status', 2)->first();
                                    $discountAmount = $enquiry ? ($enquiry->actual_amount - $enquiry->amount) : 0;
                                    $finalAmount = ceil($tourTotalPrice) - $discountAmount;
                                    
                                    $paymentData = is_string($tour->payment_details) ? json_decode($tour->payment_details, true) : $tour->payment_details;
                                    $totalPaid = 0;
                                    $hasPendingPayments = false;
                                    
                                    if (is_array($paymentData) && !empty($paymentData)) {
                                        foreach ($paymentData as $payment) {
                                            if (isset($payment['status']) && $payment['status'] == 1) {
                                                $totalPaid += isset($payment['amount']) ? (float)$payment['amount'] : 0;
                                            }
                                            if (isset($payment['status']) && $payment['status'] == 0) {
                                                $hasPendingPayments = true;
                                            }
                                        }
                                    }
                                    $remainingAmount = $finalAmount - $totalPaid;
                                @endphp
                                
                                @if(empty($paymentData))
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-exclamation-circle me-1"></i> Payment Not Started
                                    </span>
                                @elseif($hasPendingPayments && $totalPaid == 0)
                                    <span class="badge bg-secondary text-white">
                                        <i class="fas fa-clock me-1"></i> Pending Approval
                                    </span>
                                @elseif($remainingAmount > 0)
                                    <span class="badge bg-info text-white">
                                        <i class="fas fa-money-bill-wave me-1"></i> Partial Payment 
                                        @if($hasPendingPayments)
                                            ({{ number_format($totalPaid, 2) }} Paid + Pending)
                                        @else
                                            ({{ number_format($totalPaid, 2) }} Paid)
                                        @endif
                                    </span>
                                @else
                                    <span class="badge bg-success text-white">
                                        <i class="fas fa-check-circle me-1"></i> Fully Paid ({{ number_format($totalPaid, 2) }})
                                    </span>
                                @endif
                            </td>                                                       
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $tour->updated_at->format('D, M d, Y') }}</span>
                                    <small class="text-muted">{{ $tour->updated_at->diffForHumans() }}</small>
                                </div>
                            </td>
                            {{-- <td>
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
                                         <i class="ri-check-double-line me-1"></i>On Hold
                                     </span>
                                 @endif
                             </td> --}}
                            {{-- <td>
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
                            </td> --}}
                            <td>
                                <div class="d-flex flex-column gap-2">
                                    <a href="{{ route('bookings.view-tour', $tour->tour_id) }}" 
                                       class="btn btn-outline-primary btn-sm rounded-pill">
                                        <i class="ri-eye-line"></i> View
                                    </a>
                                    
                                    <a href="{{ route('tour.itinerary', ['tourId' => $tour->tour_id]) }}" 
                                       class="btn btn-outline-success btn-sm rounded-pill"
                                       onclick="event.stopPropagation(); window.open(this.href, '_blank'); return false;"
                                       style="text-decoration:none; cursor:pointer; transition: all 0.2s ease;">
                                        <i class="fas fa-calendar-alt"></i> View Itinerary
                                    </a>
                                    
                                    @if(auth()->user()->role_id == 36 || auth()->user()->role_id == 126 || auth()->user()->role_id == 127 || auth()->user()->role_id == 124 || auth()->user()->role_id == 125)
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#showPaymentModal{{ $tour->tour_id }}">
                                            <i class="fas fa-history me-1"></i> Payment History
                                        </button>
                                    @else
                                        @if(!empty($paymentData))
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#showPaymentModal{{ $tour->tour_id }}">
                                                <i class="fas fa-history me-1"></i> Payment History
                                            </button>
                                        @endif

                                        @if(in_array(auth()->user()->role_id, [1, 2, 3, 4, 10, 11, 12, 24, 28, 33, 37, 38]))
                                            @if($remainingAmount > 0 && !$hasPendingPayments)
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal{{ $tour->tour_id }}" onclick="checkPendingPayments({{ $tour->tour_id }})">
                                                    <i class="fas fa-plus-circle me-1"></i> Add Payment
                                                </button>
                                            @endif
                                        @endif
                                    @endif
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
            {{-- <div class="d-flex justify-content-between align-items-center mt-4">
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

<!-- Payment Processing Overlay -->
<div class="payment-processing-overlay" id="paymentProcessingOverlay">
    <div class="payment-spinner"></div>
    <div class="payment-text">Processing payment...</div>
</div>

<!-- Payment Modals for each tour -->
@foreach($tours as $tour)
    @php
        // Recalculate payment details for modals
        $tourTotalPrice = 0;
        foreach ($tour->booking as $booking) {
            if (in_array($booking->status, [1, 2, 3])) {
                $data = is_string($booking->data) ? json_decode($booking->data, true) : $booking->data;
                if (is_array($data)) {
                    foreach ($data as $item) {
                        if (isset($item['totalPrice'])) {
                            $tourTotalPrice += (float)$item['totalPrice'];
                        }
                    }
                }
            }
        }
        $enquiry = \App\Models\Enquiry::where('tour_id', $tour->tour_id)->where('status', 2)->first();
        $discountAmount = $enquiry ? ($enquiry->actual_amount - $enquiry->amount) : 0;
        $finalAmount = ceil($tourTotalPrice) - $discountAmount;
        
        $paymentData = is_string($tour->payment_details) ? json_decode($tour->payment_details, true) : $tour->payment_details;
        $totalPaid = 0;
        if (is_array($paymentData) && !empty($paymentData)) {
            foreach ($paymentData as $payment) {
                if (isset($payment['status']) && $payment['status'] == 1) {
                    $totalPaid += isset($payment['amount']) ? (float)$payment['amount'] : 0;
                }
            }
        }
        $remainingAmount = $finalAmount - $totalPaid;
    @endphp

    <!-- Payment History Modal -->
    <div class="modal fade" id="showPaymentModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="showPaymentModalLabel{{ $tour->tour_id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg rounded">
                <div class="modal-header bg-primary text-white d-flex align-items-center justify-content-start" style="padding: 15px; border-radius: 8px;">
                    <h5 class="modal-title d-flex align-items-center" id="showPaymentModalLabel{{ $tour->tour_id }}" style="margin: 0; font-weight: bold; color: white;">
                        <i class="fas fa-history me-2" style="color: #38ef7d; font-size: 1.4rem;"></i> 
                        <span style="color: white;">Payment History for Tour #{{ $tour->tour_id }}</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                </div>
                <div class="modal-body p-4">
                    @if(!empty($paymentData))
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">Payment Date</th>
                                        <th class="text-center">Record Date</th>
                                        <th class="text-center">Amount (SGD)</th>
                                        <th class="text-center">Original Amount</th>
                                        <th class="text-center">Currency</th>
                                        <th class="text-center">Exchange Rate</th>
                                        <th class="text-center">Payment Mode</th>
                                        <th class="text-center">Transaction ID</th>
                                        <th class="text-center">Remarks</th>
                                        <th class="text-center">Status</th>
                                        @if(auth()->user()->role_id == 36 || auth()->user()->role_id == 126 || auth()->user()->role_id == 127)
                                            <th class="text-center">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paymentData as $index => $payment)
                                        <tr>
                                            <td class="text-center">{{ isset($payment['payment_date']) ? \Carbon\Carbon::parse($payment['payment_date'])->format('M d, Y') : 'N/A' }}</td>
                                            <td class="text-center">{{ isset($payment['created_at']) ? \Carbon\Carbon::parse($payment['created_at'])->format('M d, Y') : 'N/A' }}</td>
                                            <td class="text-center fw-bold text-success">{{ isset($payment['amount']) ? number_format($payment['amount'], 2) : '0.00' }}</td>
                                            <td class="text-center">{{ isset($payment['original_amount']) ? number_format($payment['original_amount'], 2) : number_format($payment['amount'] ?? 0, 2) }}</td>
                                            <td class="text-center">{{ $payment['currency'] ?? 'SGD' }}</td>
                                            <td class="text-center">{{ isset($payment['exchange_rate']) ? number_format($payment['exchange_rate'], 4) : '1.0000' }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">{{ ucfirst($payment['payment_type'] ?? 'N/A') }}</span>
                                            </td>
                                            <td class="text-center">{{ $payment['transaction_id'] ?? 'N/A' }}</td>
                                            <td class="text-center">{{ $payment['remarks'] ?? 'N/A' }}</td>
                                            <td class="text-center">
                                                @if(isset($payment['status']))
                                                    @if($payment['status'] == 1)
                                                        <span class="badge bg-success text-white">
                                                            <i class="fas fa-check-circle me-1"></i>Verified
                                                        </span>
                                                    @elseif($payment['status'] == 2)
                                                        <span class="badge bg-danger text-white">
                                                            <i class="fas fa-times-circle me-1"></i>Declined
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="fas fa-clock me-1"></i>Pending
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary text-white">Unknown</span>
                                                @endif
                                            </td>
                                            @if(auth()->user()->role_id == 36 || auth()->user()->role_id == 126 || auth()->user()->role_id == 127)
                                                <td class="text-center">
                                                    @if(!isset($payment['status']) || $payment['status'] == 0)
                                                        <div class="d-flex justify-content-center gap-2">
                                                            <button type="button" class="btn btn-sm btn-success" onclick="verifyPayment({{ $tour->tour_id }}, {{ $index }})">
                                                                <i class="fas fa-check-circle me-1"></i> Verify
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="declinePayment({{ $tour->tour_id }}, {{ $index }})">
                                                                <i class="fas fa-times-circle me-1"></i> Decline
                                                            </button>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">No action needed</span>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Payment Summary -->
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Total Amount</h6>
                                        <h4>{{ number_format($finalAmount, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Paid Amount</h6>
                                        <h4>{{ number_format($totalPaid, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Remaining Amount</h6>
                                        <h4>{{ number_format($remainingAmount, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Payment Records</h5>
                            <p class="text-muted">No payments have been recorded for this tour yet.</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer bg-light d-flex justify-content-end" style="padding: 15px; border-radius: 8px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Payment Modal -->
    <div class="modal fade" id="addPaymentModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="addPaymentModalLabel{{ $tour->tour_id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg rounded">
                <div class="modal-header bg-primary text-white d-flex align-items-center justify-content-start" style="padding: 15px; border-radius: 8px;">
                    <h5 class="modal-title d-flex align-items-center" id="addPaymentModalLabel{{ $tour->tour_id }}" style="margin: 0; font-weight: bold; color: white;">
                        <i class="fas fa-money-bill-wave me-2" style="color: #38ef7d; font-size: 1.4rem;"></i> 
                        <span style="color: white;">Add Payment for Tour #{{ $tour->tour_id }}</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="paymentForm{{ $tour->tour_id }}" action="{{ route('tour.add-payment', $tour->tour_id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="tour_id" value="{{ $tour->tour_id }}">
                        
                        <!-- Display Due Amount Info (Read-only) -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-info-circle text-info me-2"></i>Payment Information
                            </label>
                            <div class="alert alert-info">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <small class="text-muted">Total Amount</small>
                                        <div class="fw-bold text-primary">{{ number_format($finalAmount, 2) }} SGD</div>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted">Paid Amount</small>
                                        <div class="fw-bold text-success">{{ number_format($totalPaid, 2) }} SGD</div>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted">Remaining</small>
                                        <div class="fw-bold text-danger">{{ number_format($remainingAmount, 2) }} SGD</div>
                                    </div>
                                </div>
                            </div>
                            <!-- Hidden input for validation -->
                            <input type="hidden" id="amount{{ $tour->tour_id }}" name="amount" value="{{ $remainingAmount }}">
                        </div>
                        
                        <!-- Currency Selection -->
                        <div class="mb-4">
                            <label for="currency{{ $tour->tour_id }}" class="form-label fw-bold">
                                <i class="fas fa-coins text-warning me-2"></i>Select Currency
                            </label>
                            <select class="form-select form-control-lg" 
                                id="currency{{ $tour->tour_id }}" 
                                name="currency" 
                                onchange="updatePaymentAmountEnhanced({{ $tour->tour_id }}, this.value)"
                                required>
                                <option value="">Select Currency</option>
                                @foreach(\App\Models\Setting::getCurrencyCodes() as $currency)
                                    <option value="{{ $currency }}" {{ $currency == 'SGD' ? 'selected' : '' }}>
                                        {{ $currency }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Exchange Rate (Editable) -->
                        <div class="mb-4" id="exchangeRateSection{{ $tour->tour_id }}" style="display: none;">
                            <label for="exchange_rate{{ $tour->tour_id }}" class="form-label fw-bold">
                                <i class="fas fa-calculator text-primary me-2"></i>Exchange Rate
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">1 SGD =</span>
                                <input type="number" 
                                    class="form-control form-control-lg" 
                                    id="exchange_rate{{ $tour->tour_id }}" 
                                    name="exchange_rate" 
                                    value="1.00" 
                                    min="0" 
                                    step="0.0001"
                                    oninput="recalculateFromExchangeRate({{ $tour->tour_id }})">
                                <span class="input-group-text bg-light" id="exchangeRateCurrency{{ $tour->tour_id }}">SGD</span>
                            </div>
                            <div class="mt-1">
                                <small class="text-success" id="exchangeRateSource{{ $tour->tour_id }}">
                                    <i class="fas fa-globe me-1"></i>
                                    Rate Source: <span id="rateSourceText{{ $tour->tour_id }}">API</span>
                                </small>
                            </div>
                        </div>
                        
                        <!-- Payment Amount in Selected Currency -->
                        <div class="mb-4">
                            <label for="payment_amount{{ $tour->tour_id }}" class="form-label fw-bold">
                                <i class="fas fa-money-bill-wave text-success me-2"></i>Payment Amount
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light" id="currencySymbol{{ $tour->tour_id }}">SGD</span>
                                <input type="number" 
                                    class="form-control form-control-lg" 
                                    id="payment_amount{{ $tour->tour_id }}" 
                                    name="payment_amount" 
                                    placeholder="Enter payment amount" 
                                    required
                                    min="0" 
                                    max="{{ $remainingAmount }}"
                                    step="0.01"
                                    oninput="validatePaymentAmountInput({{ $tour->tour_id }})"
                                    onblur="validatePaymentAmountInput({{ $tour->tour_id }})">
                            </div>
                            <div class="mt-2" id="conversionInfoContainer{{ $tour->tour_id }}" style="display: none;">
                                <small class="text-info" id="conversionInfo{{ $tour->tour_id }}">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Amount in SGD: {{ number_format($remainingAmount, 2) }}
                                </small>
                            </div>
                            <div class="mt-1">
                                <small class="text-danger" id="paymentValidationError{{ $tour->tour_id }}" style="display: none;">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    <span id="validationMessage{{ $tour->tour_id }}"></span>
                                </small>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="payment_date{{ $tour->tour_id }}" class="form-label fw-bold">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>Payment Date
                            </label>
                            <input type="date" 
                                class="form-control form-control-lg" 
                                id="payment_date{{ $tour->tour_id }}" 
                                name="payment_date" 
                                value="{{ date('Y-m-d') }}"
                                required>
                        </div>

                        <div class="mb-4">
                            <label for="payment_type{{ $tour->tour_id }}" class="form-label fw-bold">
                                <i class="fas fa-credit-card text-primary me-2"></i>Payment Type
                            </label>
                            <select class="form-select form-control-lg" 
                                id="payment_type{{ $tour->tour_id }}" 
                                name="payment_type" 
                                required>
                                <option value="">Select Payment Type</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="cheque">Cheque</option>
                                <option value="online">Bank Transfer</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="transaction_id{{ $tour->tour_id }}" class="form-label fw-bold">
                                <i class="fas fa-hashtag text-primary me-2"></i>Transaction ID (Optional)
                            </label>
                            <input type="text" class="form-control form-control-lg" id="transaction_id{{ $tour->tour_id }}" name="transaction_id">
                        </div>
                        
                        <div class="mb-4">
                            <label for="remarks{{ $tour->tour_id }}" class="form-label fw-bold">
                                <i class="fas fa-comment-alt text-warning me-2"></i>Remarks (Optional)
                            </label>
                            <textarea class="form-control" id="remarks{{ $tour->tour_id }}" name="remarks" rows="3" placeholder="Enter payment remarks here..."></textarea>
                        </div>                                       
                    </form>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between" style="padding: 15px; border-radius: 8px;">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-success" id="savePaymentBtn{{ $tour->tour_id }}" onclick="submitPaymentForm({{ $tour->tour_id }})">
                        <i class="fas fa-save me-2"></i>Verify Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
@endforeach

<script>
// Payment Functions
function checkPendingPayments(tourId) {
    // This function checks for pending payments before opening the modal
    console.log('Checking pending payments for tour:', tourId);
}

function validateAmount(input, maxAmount) {
    const value = parseFloat(input.value);
    if (value > maxAmount) {
        input.value = maxAmount;
    }
    if (value < 0) {
        input.value = 0;
    }
}

function updatePaymentAmountEnhanced(tourId, selectedCurrency) {
    const exchangeRateSection = document.getElementById(`exchangeRateSection${tourId}`);
    const exchangeRateInput = document.getElementById(`exchange_rate${tourId}`);
    const exchangeRateCurrency = document.getElementById(`exchangeRateCurrency${tourId}`);
    const currencySymbol = document.getElementById(`currencySymbol${tourId}`);
    const conversionInfoContainer = document.getElementById(`conversionInfoContainer${tourId}`);
    
    if (selectedCurrency && selectedCurrency !== 'SGD') {
        exchangeRateSection.style.display = 'block';
        exchangeRateCurrency.textContent = selectedCurrency;
        currencySymbol.textContent = selectedCurrency;
        conversionInfoContainer.style.display = 'block';
        
        // Fetch exchange rate (placeholder - replace with actual API call)
        fetchExchangeRate(selectedCurrency, tourId);
    } else {
        exchangeRateSection.style.display = 'none';
        exchangeRateInput.value = '1.00';
        currencySymbol.textContent = 'SGD';
        conversionInfoContainer.style.display = 'none';
    }
}

function fetchExchangeRate(currency, tourId) {
    // Placeholder for exchange rate API call
    console.log(`Fetching exchange rate for ${currency}`);
    
    const exchangeRateInput = document.getElementById(`exchange_rate${tourId}`);
    const rateSourceText = document.getElementById(`rateSourceText${tourId}`);
    
    // Set default rates (replace with actual API call)
    const defaultRates = {
        'USD': 0.74,
        'EUR': 0.69,
        'GBP': 0.59,
        'AUD': 1.09,
        'JPY': 109.50,
        'CNY': 5.12,
        'INR': 61.75
    };
    
    if (defaultRates[currency]) {
        exchangeRateInput.value = defaultRates[currency];
        rateSourceText.textContent = 'Default';
    }
}

function recalculateFromExchangeRate(tourId) {
    const exchangeRate = parseFloat(document.getElementById(`exchange_rate${tourId}`).value);
    const sgdAmount = parseFloat(document.getElementById(`amount${tourId}`).value);
    const paymentAmountInput = document.getElementById(`payment_amount${tourId}`);
    
    if (exchangeRate && sgdAmount) {
        const convertedAmount = sgdAmount * exchangeRate;
        paymentAmountInput.value = convertedAmount.toFixed(2);
    }
}

function validatePaymentAmountInput(tourId) {
    const paymentAmount = parseFloat(document.getElementById(`payment_amount${tourId}`).value);
    const exchangeRate = parseFloat(document.getElementById(`exchange_rate${tourId}`).value) || 1;
    const maxSGDAmount = parseFloat(document.getElementById(`amount${tourId}`).value);
    const selectedCurrency = document.getElementById(`currency${tourId}`).value;
    
    const validationError = document.getElementById(`paymentValidationError${tourId}`);
    const validationMessage = document.getElementById(`validationMessage${tourId}`);
    const conversionInfo = document.getElementById(`conversionInfo${tourId}`);
    
    if (!paymentAmount || paymentAmount <= 0) {
        validationError.style.display = 'block';
        validationMessage.textContent = 'Please enter a valid payment amount';
        document.getElementById(`savePaymentBtn${tourId}`).disabled = true;
        return;
    }
    
    // Calculate equivalent SGD amount
    const equivalentSGD = selectedCurrency === 'SGD' ? paymentAmount : (paymentAmount / exchangeRate);
    
    if (equivalentSGD > maxSGDAmount) {
        validationError.style.display = 'block';
        validationMessage.textContent = `Amount exceeds maximum allowed (${maxSGDAmount.toFixed(2)} SGD)`;
        document.getElementById(`savePaymentBtn${tourId}`).disabled = true;
    } else {
        validationError.style.display = 'none';
        document.getElementById(`savePaymentBtn${tourId}`).disabled = false;
    }
    
    // Update conversion info
    if (selectedCurrency !== 'SGD') {
        conversionInfo.innerHTML = `<i class="fas fa-info-circle me-1"></i>Amount in SGD: ${equivalentSGD.toFixed(2)}`;
    } else {
        conversionInfo.innerHTML = `<i class="fas fa-info-circle me-1"></i>Amount: ${paymentAmount.toFixed(2)} SGD`;
    }
}

function submitPaymentForm(tourId) {
    const form = document.getElementById(`paymentForm${tourId}`);
    const overlay = document.getElementById('paymentProcessingOverlay');
    const modal = document.getElementById(`addPaymentModal${tourId}`);
    
    // More robust button selection - try multiple selectors
    let submitBtn = document.getElementById(`savePaymentBtn${tourId}`);
    if (!submitBtn) {
        submitBtn = form.querySelector('button[onclick*="submitPaymentForm"]');
    }
    if (!submitBtn) {
        submitBtn = form.querySelector('button[type="submit"]');
    }
    if (!submitBtn) {
        submitBtn = form.querySelector('.btn-success');
    }
    
    console.log('Submitting payment form for tour:', tourId);
    console.log('Form action:', form.action);
    console.log('Submit button found:', submitBtn);
    
    if (form.checkValidity()) {
        // Disable submit button immediately to prevent multiple submissions
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        }
        
        // Close modal immediately to prevent multiple submissions
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) {
            modalInstance.hide();
        }
        
        overlay.classList.add('active');
        
        // Use AJAX to submit the form for better user experience
        const formData = new FormData(form);
        
        // Log form data for debugging
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }
        
        $.ajax({
            url: form.action,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                overlay.classList.remove('active');
                
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message || 'Payment has been recorded and is pending verification.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Close the modal and reload the page
                        $(`#addPaymentModal${tourId}`).modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message || 'Failed to submit payment.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr, status, error) {
                overlay.classList.remove('active');
                
                // Re-enable submit button
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Verify Payment';
                }
                
                // Reopen modal if error occurs
                const modalInstance = new bootstrap.Modal(modal);
                modalInstance.show();
                
                console.error('Error submitting payment:', error);
                
                let errorMessage = 'An error occurred while submitting the payment.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    // Handle validation errors
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join(', ');
                }
                
                Swal.fire({
                    title: 'Error!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    } else {
        // Reset button if validation fails
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Verify Payment';
        }
        form.reportValidity();
    }
}

// Add event listeners to reset forms when modals are closed
document.addEventListener('DOMContentLoaded', function() {
    // Reset payment forms when modals are hidden
    const paymentModals = document.querySelectorAll('[id^="addPaymentModal"]');
    paymentModals.forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function() {
            const tourId = this.id.replace('addPaymentModal', '');
            const form = document.getElementById(`paymentForm${tourId}`);
            
            // More robust button selection
            let submitBtn = document.getElementById(`savePaymentBtn${tourId}`);
            if (!submitBtn) {
                submitBtn = form.querySelector('button[onclick*="submitPaymentForm"]');
            }
            if (!submitBtn) {
                submitBtn = form.querySelector('button[type="submit"]');
            }
            if (!submitBtn) {
                submitBtn = form.querySelector('.btn-success');
            }
            
            // Reset form
            form.reset();
            
            // Reset submit button
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Verify Payment';
            }
            
            // Reset currency selection to SGD
            const currencySelect = form.querySelector('select[name="currency"]');
            if (currencySelect) {
                currencySelect.value = 'SGD';
                updatePaymentAmountEnhanced(tourId, 'SGD');
            }
        });
    });
});

function updatePaymentStatus(tourId, paymentIndex, status, amount) {
    if (status === 1) {
        verifyPayment(tourId, paymentIndex);
    } else {
        declinePayment(tourId, paymentIndex);
    }
}

// Define base URL using Laravel's URL helper
const BASE_URL = "{{ url('/') }}";

// Helper function to close payment modal
function closePaymentModal(tourId) {
    console.log('Closing payment modal for tour:', tourId);
    
    // Method 1: Try to find the specific modal
    const modal = document.getElementById(`showPaymentModal${tourId}`);
    console.log('Modal element found:', modal);
    
    if (modal) {
        // Method 1a: Bootstrap Modal instance
        try {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            console.log('Bootstrap modal instance:', modalInstance);
            if (modalInstance) {
                modalInstance.hide();
                console.log('Bootstrap modal hide called');
            }
        } catch (e) {
            console.log('Bootstrap method failed:', e);
        }
        
        // Method 1b: jQuery modal hide
        try {
            if (typeof $ !== 'undefined') {
                $(`#showPaymentModal${tourId}`).modal('hide');
                console.log('jQuery modal hide called');
            }
        } catch (e) {
            console.log('jQuery method failed:', e);
        }
        
        // Method 1c: Force hide using CSS
        modal.style.display = 'none';
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        console.log('CSS modal hide applied');
    }
    
    // Method 2: Close any open modal as fallback
    try {
        const allOpenModals = document.querySelectorAll('.modal.show');
        console.log('Found open modals:', allOpenModals.length);
        
        allOpenModals.forEach(openModal => {
            try {
                const instance = bootstrap.Modal.getInstance(openModal);
                if (instance) {
                    instance.hide();
                    console.log('Closed modal:', openModal.id);
                }
            } catch (e) {
                console.log('Error closing modal instance:', e);
            }
        });
    } catch (e) {
        console.log('Fallback method failed:', e);
    }
    
    // Method 3: Remove backdrop and reset body
    try {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        console.log('Backdrop and body reset');
    } catch (e) {
        console.log('Backdrop reset failed:', e);
    }
}

function verifyPayment(tourId, paymentIndex) {
    if (confirm('Are you sure you want to verify this payment?')) {
        // Close the modal immediately when user confirms
        closePaymentModal(tourId);
        
        // Show loading overlay
        const overlay = document.getElementById('paymentProcessingOverlay');
        if (overlay) {
            overlay.classList.add('active');
        }
        
        // Use jQuery AJAX with proper CSRF token handling and absolute URL
        $.ajax({
            url: `${BASE_URL}/tour/${tourId}/verify-payment`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                payment_index: paymentIndex
            },
            success: function(response) {
                // Hide loading overlay
                if (overlay) {
                    overlay.classList.remove('active');
                }
                
                if (response.success) {
                    // Show success message
                    Swal.fire({
                        title: 'Success!',
                        text: 'Payment has been verified successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload the page after user clicks OK
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message || 'Failed to verify payment.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr, status, error) {
                // Hide loading overlay
                if (overlay) {
                    overlay.classList.remove('active');
                }
                
                console.error('Error verifying payment:', error);
                
                let errorMessage = 'An error occurred while verifying the payment.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    title: 'Error!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    }
}

function declinePayment(tourId, paymentIndex) {
    if (confirm('Are you sure you want to decline this payment?')) {
        // Close the modal immediately when user confirms
        closePaymentModal(tourId);
        
        // Show loading overlay
        const overlay = document.getElementById('paymentProcessingOverlay');
        if (overlay) {
            overlay.classList.add('active');
        }
        
        // Use jQuery AJAX with proper CSRF token handling and absolute URL
        $.ajax({
            url: `${BASE_URL}/tour/${tourId}/decline-payment`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                payment_index: paymentIndex
            },
            success: function(response) {
                // Hide loading overlay
                if (overlay) {
                    overlay.classList.remove('active');
                }
                
                if (response.success) {
                    // Show success message
                    Swal.fire({
                        title: 'Success!',
                        text: 'Payment has been declined successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload the page after user clicks OK
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message || 'Failed to decline payment.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr, status, error) {
                // Hide loading overlay
                if (overlay) {
                    overlay.classList.remove('active');
                }
                
                console.error('Error declining payment:', error);
                
                let errorMessage = 'An error occurred while declining the payment.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    title: 'Error!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    }
}

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
    const dr = document.getElementById('dateRange');
    const ds = document.getElementById('dateRangeStart');
    const de = document.getElementById('dateRangeEnd');
    if (dr) dr.value = '';
    if (ds) ds.value = '';
    if (de) de.value = '';
    filterTable();
}

function filterTable() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    // const statusFilter = document.getElementById('statusFilter')?.value || '';
    const destinationFilter = document.getElementById('destinationFilter')?.value || '';
    const agentFilter = document.getElementById('agentFilter')?.value || '';
    const timeFilter = document.getElementById('timeFilter')?.value || '';
    const dateStart = document.getElementById('dateRangeStart')?.value || '';
    const dateEnd = document.getElementById('dateRangeEnd')?.value || '';
    
    const rows = document.querySelectorAll('#toursTable tbody tr');
    
    rows.forEach(row => {
        if (row.cells.length === 1) return; // Skip empty state row
        
        const tourDetails = row.cells[1]?.textContent.toLowerCase() || '';
        const destination = row.cells[2]?.querySelector('.fw-medium')?.textContent || '';
        const agent = row.cells[5]?.querySelector('.fw-medium')?.textContent || '';
        const status = row.cells[8]?.querySelector('.badge')?.textContent.toLowerCase() || '';
        const travelDates = row.cells[6]?.textContent.toLowerCase() || '';
        const confirmationDateText = row.cells[7]?.textContent || '';
        const updatedAt = row.getAttribute('data-updated-at');
        
        let show = true;
        
        if (searchTerm && !tourDetails.includes(searchTerm)) {
            show = false;
        }
        
        // if (statusFilter && !status.includes(statusFilter.toLowerCase())) {
        //     show = false;
        // }
        
        if (destinationFilter && destination !== destinationFilter) {
            show = false;
        }
        
        if (agentFilter && agent !== agentFilter) {
            show = false;
        }
        
        // Date range filtering (check both created_at and updated_at)
        if (dateStart && dateEnd && (updatedAt || row.getAttribute('data-created-at'))) {
            const createdAt = row.getAttribute('data-created-at');
            const s = new Date(dateStart + 'T00:00:00');
            const e = new Date(dateEnd + 'T23:59:59');
            let dateInRange = false;
            
            // Check updated_at if available
            if (updatedAt) {
                const updatedDate = new Date(updatedAt + 'T00:00:00');
                if (updatedDate >= s && updatedDate <= e) {
                    dateInRange = true;
                }
            }
            
            // Check created_at if available and updated_at didn't match
            if (!dateInRange && createdAt) {
                const createdDate = new Date(createdAt + 'T00:00:00');
                if (createdDate >= s && createdDate <= e) {
                    dateInRange = true;
                }
            }
            
            if (!dateInRange) {
                show = false;
            }
        }
        
        if (timeFilter) {
             const daysToGoMatch = travelDates.match(/(\d+) days to go/);
             const daysToGo = daysToGoMatch ? parseInt(daysToGoMatch[1]) : null;
             const isStartingToday = travelDates.includes('starting today');
             const isInProgress = travelDates.includes('started') || travelDates.includes('days ago');
             
             if (timeFilter === 'this_week') {
                 // Show tours starting within 7 days or starting today
                 if (!((daysToGo !== null && daysToGo <= 7) || isStartingToday)) {
                     show = false;
                 }
             } else if (timeFilter === 'next_week') {
                 // Show tours starting in 8-14 days
                 if (!(daysToGo !== null && daysToGo >= 8 && daysToGo <= 14)) {
                     show = false;
                 }
             } else if (timeFilter === 'this_month') {
                 // Show tours starting within 30 days
                 if (!((daysToGo !== null && daysToGo <= 30) || isStartingToday)) {
                     show = false;
                 }
             } else if (timeFilter === 'next_month') {
                 // Show tours starting in 31-60 days
                 if (!(daysToGo !== null && daysToGo >= 31 && daysToGo <= 60)) {
                     show = false;
                 }
             }
         }
        
        row.style.display = show ? '' : 'none';
    });

    // Update header/cards counts based on visible rows
    const visibleRows = Array.from(document.querySelectorAll('#toursTable tbody tr')).filter(r => r.style.display !== 'none' && r.cells.length > 1);
    const rangeCount = visibleRows.length;
    const adults = visibleRows.reduce((sum, r) => sum + parseInt(r.getAttribute('data-adult') || '0', 10), 0);
    const children = visibleRows.reduce((sum, r) => sum + parseInt(r.getAttribute('data-child') || '0', 10), 0);
    
    // Count today's bookings from visible rows
    const today = new Date().toISOString().split('T')[0];
    const todayCount = visibleRows.filter(r => {
        const createdAt = r.getAttribute('data-created-at');
        return createdAt === today;
    }).length;

    const countEl = document.getElementById('rangeCount');
    const labelEl = document.getElementById('rangeLabel');
    const statConfirmed = document.getElementById('statConfirmedCount');
    const statConfirmedLabel = document.getElementById('statConfirmedLabel');
    const statAdults = document.getElementById('statAdultsCount');
    const statAdultsLabel = document.getElementById('statAdultsLabel');
    const statChildren = document.getElementById('statChildrenCount');
    const statChildrenLabel = document.getElementById('statChildrenLabel');
    const statToday = document.getElementById('statTodayCount');

    if (countEl) countEl.textContent = rangeCount;
    if (statConfirmed) statConfirmed.textContent = rangeCount;
    if (statAdults) statAdults.textContent = adults;
    if (statChildren) statChildren.textContent = children;
    if (statToday) statToday.textContent = todayCount;

    if (dateStart && dateEnd) {
        const start = new Date(dateStart);
        const end = new Date(dateEnd);
        
        // Format the date range label
        let label;
        if (start.getMonth() === end.getMonth() && start.getFullYear() === end.getFullYear()) {
            // Same month
            if (start.getDate() === 1 && end.getDate() === new Date(end.getFullYear(), end.getMonth() + 1, 0).getDate()) {
                // Full month
                label = start.toLocaleString('default', { month: 'long', year: 'numeric' });
            } else {
                label = `${start.getDate()}-${end.getDate()} ${start.toLocaleString('default', { month: 'short' })}, ${start.getFullYear()}`;
            }
        } else {
            label = `${start.toLocaleString('default', { month: 'short' })} ${start.getDate()} - ${end.toLocaleString('default', { month: 'short' })} ${end.getDate()}, ${end.getFullYear()}`;
        }
        
        if (labelEl) labelEl.textContent = label;
        if (statConfirmedLabel) statConfirmedLabel.textContent = `Confirmed - ${label}`;
        if (statAdultsLabel) statAdultsLabel.textContent = `Adults - ${label}`;
        if (statChildrenLabel) statChildrenLabel.textContent = `Childrens - ${label}`;
    } else {
        const month = new Date().toLocaleString('default', { month: 'long' });
        if (labelEl) labelEl.textContent = month;
        if (statConfirmedLabel) statConfirmedLabel.textContent = `${month} Confirmed`;
        if (statAdultsLabel) statAdultsLabel.textContent = `${month} Adults`;
        if (statChildrenLabel) statChildrenLabel.textContent = `${month} Children`;
    }
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    // document.getElementById('statusFilter').value = '';
    document.getElementById('destinationFilter').value = '';
    document.getElementById('agentFilter').value = '';
    document.getElementById('timeFilter').value = '';
    const dr = document.getElementById('dateRange');
    const ds = document.getElementById('dateRangeStart');
    const de = document.getElementById('dateRangeEnd');
    if (dr) dr.value = '';
    if (ds) ds.value = '';
    if (de) de.value = '';
    filterTable();
}

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    // const statusFilter = document.getElementById('statusFilter');
    const destinationFilter = document.getElementById('destinationFilter');
    const agentFilter = document.getElementById('agentFilter');
    const timeFilter = document.getElementById('timeFilter');
    const dateRange = document.getElementById('dateRange');
    const dateRangeStart = document.getElementById('dateRangeStart');
    const dateRangeEnd = document.getElementById('dateRangeEnd');
    
    // Add event listeners
    if (searchInput) searchInput.addEventListener('input', filterTable);
    // if (statusFilter) statusFilter.addEventListener('change', filterTable);
    if (destinationFilter) destinationFilter.addEventListener('change', filterTable);
    if (agentFilter) agentFilter.addEventListener('change', filterTable);
    if (timeFilter) timeFilter.addEventListener('change', filterTable);
    // Date range picker will be initialized in scripts section where jQuery is available
    
    // Apply initial filter on page load to show today's data
    filterTable();
});
</script>
@endsection

@section('scripts')
<!-- Date Range Picker JS - Load after jQuery -->
<script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<script>
    // Wait for all scripts to load before initializing
    $(document).ready(function() {
        // Small delay to ensure all scripts are loaded
        setTimeout(function() {
            initializeDateRangePicker();
            initializeDataTable();
        }, 200);
    });
    
    function initializeDateRangePicker() {
        // Initialize date range picker first
        const dateRange = document.getElementById('dateRange');
        const dateRangeStart = document.getElementById('dateRangeStart');
        const dateRangeEnd = document.getElementById('dateRangeEnd');
        
        if (dateRange && typeof moment !== 'undefined' && typeof $.fn.daterangepicker !== 'undefined') {
            // Set default to current month
            const startOfMonth = moment().startOf('month');
            const endOfMonth = moment().endOf('month');
            
            $(dateRange).daterangepicker({
                opens: 'left',
                autoUpdateInput: true,
                maxDate: moment(), // No future dates
                startDate: startOfMonth,
                endDate: endOfMonth,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'MMM DD, YYYY'
                }
            });

            // Set initial values for current month
            $(dateRange).val(startOfMonth.format('MMM DD') + ' - ' + endOfMonth.format('MMM DD, YYYY'));
            if (dateRangeStart) dateRangeStart.value = startOfMonth.format('YYYY-MM-DD');
            if (dateRangeEnd) dateRangeEnd.value = endOfMonth.format('YYYY-MM-DD');

            $(dateRange).on('apply.daterangepicker', function(ev, picker) {
                const start = picker.startDate.clone().startOf('day');
                const end = picker.endDate.clone().endOf('day');
                $(this).val(start.format('MMM DD') + ' - ' + end.format('MMM DD, YYYY'));
                if (dateRangeStart) dateRangeStart.value = start.format('YYYY-MM-DD');
                if (dateRangeEnd) dateRangeEnd.value = end.format('YYYY-MM-DD');
                filterTable();
            });

            $(dateRange).on('cancel.daterangepicker', function() {
                $(this).val('');
                if (dateRangeStart) dateRangeStart.value = '';
                if (dateRangeEnd) dateRangeEnd.value = '';
                filterTable();
            });
            
            // Apply initial filter with current month data
            setTimeout(function() {
                filterTable();
            }, 100);
        } else {
            console.error('Date range picker could not be initialized. Missing dependencies:', {
                dateRange: !!dateRange,
                moment: typeof moment !== 'undefined',
                daterangepicker: typeof $.fn.daterangepicker !== 'undefined',
                jquery: typeof $ !== 'undefined'
            });
            
            // Fallback: still set initial date values for current month
            if (dateRange && typeof moment !== 'undefined') {
                const startOfMonth = moment().startOf('month');
                const endOfMonth = moment().endOf('month');
                if (dateRangeStart) dateRangeStart.value = startOfMonth.format('YYYY-MM-DD');
                if (dateRangeEnd) dateRangeEnd.value = endOfMonth.format('YYYY-MM-DD');
                setTimeout(function() {
                    filterTable();
                }, 100);
            }
        }
    }
    
    function initializeDataTable() {
        // Check if DataTable is already initialized
        if ($.fn.DataTable.isDataTable('.datatables-basic')) {
            $('.datatables-basic').DataTable().destroy();
        }
        
        // Initialize DataTable with export buttons
        var table = $('.datatables-basic').DataTable({
            responsive: true,
            dom: 'lrtip', // Removed 'B' to hide the buttons, keeping l=length, r=processing, t=table, i=info, p=pagination
            buttons: [
                'copy',
                'csv',
                'excel',
                'pdf',
                'print' // Keep buttons for functionality but don't show them
            ],
            searching: false, // Disable built-in searching since we use custom filters
            language: {
                search: "DataTable Search:",
                searchPlaceholder: "Search all columns...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            lengthMenu: [10, 25, 50, 100], // Customize number of entries per page
            pageLength: 25,
            //  order: [[7, 'desc']], // Sort by Confirmation Date column (index 7) in descending order
            columnDefs: [
                {
                    targets: [8], // Actions column (index 8)
                    orderable: false,
                    searchable: false
                },
                {
                    targets: [3], // Guests column (index 3)
                    orderable: false
                },
                {
                    targets: [8], // Status column (index 8)
                    orderable: false
                }
            ],
            initComplete: function() {
                console.log('DataTable initialized successfully');
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
    }
</script>
@endsection

@extends('layouts.datatablejs')
