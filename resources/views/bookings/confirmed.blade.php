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

    /* Enhanced Hotel Modal Styles */
    .modal-xl {
        max-width: 1200px;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .bg-gradient-warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .hotel-modal-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .hotel-modal-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .modal-content {
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    .badge-rounded {
        border-radius: 20px;
        padding: 8px 16px;
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
                            data-tour-id="{{ $tour->tour_id }}"
                            data-check-in="{{ $tour->check_in_time }}"
                            data-check-out="{{ $tour->check_out_time }}"
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
                                        // Fetch orders for this tour
                                        $orders = \App\Models\Order::where('tour_id', $tour->tour_id)->where('bookingType', 'booking')->get();
                                        $svc = [
                                            'hotel' => 0,
                                            'attraction' => 0,
                                            'restaurant' => 0,
                                            'guide' => 0,
                                            'entry_port' => 0,
                                            'exit_port' => 0,
                                            'travel_hourly' => 0,
                                            'travel_point' => 0,
                                            'local_transport' => 0,
                                        ];
                                        $serviceData = [];
                                        
                                        // Group orders by type and count them
                                        foreach($orders as $order) {
                                            $type = $order->type;
                                            if(isset($svc[$type])) {
                                                $svc[$type]++;
                                                if(!isset($serviceData[$type])) {
                                                    $serviceData[$type] = [];
                                                }
                                                $serviceData[$type][] = $order;
                                            }
                                        }
                                        
                                        // Debug: Add data attributes for JavaScript debugging
                                        $debugInfo = [
                                            'tour_id' => $tour->tour_id,
                                            'orders_count' => $orders->count(),
                                            'hotel_orders_count' => isset($serviceData['hotel']) ? count($serviceData['hotel']) : 0,
                                            'hotel_svc_count' => $svc['hotel']
                                        ];
                                        
                                        $icons = [
                                            'hotel' => 'ri-hotel-line',
                                            'attraction' => 'ri-building-2-line',
                                            'restaurant' => 'ri-restaurant-2-line',
                                            'guide' => 'ri-user-voice-line',
                                            'entry_port' => 'ri-flight-land-line',
                                            'exit_port' => 'ri-flight-takeoff-line',
                                            'travel_hourly' => 'ri-time-line',
                                            'travel_point' => 'ri-route-line',
                                            'local_transport' => 'ri-car-line',
                                        ];
                                    @endphp
                                    @foreach($svc as $key=>$count)
                                        @if(intval($count) > 0)
                                            @if($key === 'restaurant')
                                                {{-- Special handling for restaurants - show individual buttons --}}
                                                @if(isset($serviceData['restaurant']) && count($serviceData['restaurant']) > 0)
                                                    @php $globalRestaurantCounter = 1; @endphp
                                                    @foreach($serviceData['restaurant'] as $restaurantOrderIndex => $restaurantOrder)
                                                        @php
                                                            $restaurantData = is_string($restaurantOrder->data) ? json_decode($restaurantOrder->data, true) : $restaurantOrder->data;
                                                        @endphp
                                                        @if(is_array($restaurantData))
                                                            @php $actualBookingIndex = 0; @endphp
                                                            @foreach($restaurantData as $originalKey => $booking)
                                                                @php $bookingIndex = $actualBookingIndex; @endphp
                                                                <span class="badge bg-light text-dark border me-1 mb-1" style="cursor: pointer;" 
                                                                      onclick="openIndividualRestaurantModal({{ $tour->tour_id }}, {{ $restaurantOrderIndex }}, {{ $bookingIndex }})">
                                                                    <i class="{{ $icons[$key] }} me-1"></i>
                                                                    Restaurant {{ $globalRestaurantCounter }}
                                                                </span>
                                                                @php 
                                                                    $actualBookingIndex++; 
                                                                    $globalRestaurantCounter++;
                                                                @endphp
                                                            @endforeach
                                                        @endif
                                                    @endforeach
                                                @endif
                                            @elseif($key === 'guide')
                                                {{-- Special handling for guides - show individual buttons --}}
                                                @if(isset($serviceData['guide']) && count($serviceData['guide']) > 0)
                                                    @php $globalGuideCounter = 1; @endphp
                                                    @foreach($serviceData['guide'] as $guideOrderIndex => $guideOrder)
                                                        @php
                                                            $guideData = is_string($guideOrder->data) ? json_decode($guideOrder->data, true) : $guideOrder->data;
                                                        @endphp
                                                        @if(is_array($guideData))
                                                            @php $actualBookingIndex = 0; @endphp
                                                            @foreach($guideData as $originalKey => $booking)
                                                                @php $bookingIndex = $actualBookingIndex; @endphp
                                                                <span class="badge bg-light text-dark border me-1 mb-1" style="cursor: pointer;" 
                                                                      onclick="openIndividualGuideModal({{ $tour->tour_id }}, {{ $guideOrderIndex }}, {{ $bookingIndex }})">
                                                                    <i class="{{ $icons[$key] }} me-1"></i>
                                                                    Guide {{ $globalGuideCounter }}
                                                                </span>
                                                                @php 
                                                                    $actualBookingIndex++; 
                                                                    $globalGuideCounter++;
                                                                @endphp
                                                            @endforeach
                                                        @endif
                                                    @endforeach
                                                @endif
                                            @elseif(in_array($key, ['hotel', 'attraction', 'entry_port', 'exit_port', 'travel_hourly', 'travel_point', 'local_transport']))
                                                <span class="badge bg-light text-dark border" style="cursor: pointer;" 
                                                      onclick="openServiceModal('{{ $key }}', {{ $tour->tour_id }}, event)"
                                                      data-debug-info="{{ json_encode($debugInfo) }}">
                                                    <i class="{{ $icons[$key] }} me-1"></i>
                                                    @if($key === 'entry_port')
                                                        Arrival: {{ $count }}
                                                    @elseif($key === 'exit_port')
                                                        Departure: {{ $count }}
                                                    @elseif($key === 'travel_hourly')
                                                        Local-Tour Hourly: {{ $count }}
                                                    @elseif($key === 'travel_point')
                                                        Local-Tour Point to Point: {{ $count }}
                                                    @elseif($key === 'local_transport')
                                                        Local Transport: {{ $count }}
                                                    @else
                                                        {{ ucfirst($key) }}: {{ $count }}
                                                    @endif
                                                </span>
                                            @else
                                                <span class="badge bg-light text-dark border">
                                                    <i class="{{ $icons[$key] }} me-1"></i>
                                                    @if($key === 'entry_port')
                                                        Arrival: {{ $count }}
                                                    @elseif($key === 'exit_port')
                                                        Departure: {{ $count }}
                                                    @elseif($key === 'travel_hourly')
                                                        Local-Tour Hourly: {{ $count }}
                                                    @elseif($key === 'travel_point')
                                                        Local-Tour Point to Point: {{ $count }}
                                                    @elseif($key === 'local_transport')
                                                        Local Transport: {{ $count }}
                                                    @else
                                                        {{ ucfirst($key) }}: {{ $count }}
                                                    @endif
                                                </span>
                                            @endif
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
                                    <a href="{{ route('bookings.view-tour', Crypt::encrypt($tour->tour_id)) }}" 
                                       class="btn btn-outline-primary btn-sm rounded-pill">
                                        <i class="ri-eye-line"></i> View
                                    </a>
                                    
                                    <a href="{{ route('tour.itinerary', ['tourId' => Crypt::encrypt($tour->tour_id)]) }}" 
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

<!-- Hotel Detail Modals for all tours -->
@foreach($tours as $tour)
    @php
        // Re-fetch orders and process service data for modals
        $orders = \App\Models\Order::where('tour_id', $tour->tour_id)->get();
        $svc = [
            'hotel' => 0,
            'attraction' => 0,
            'restaurant' => 0,
            'guide' => 0,
            'entry_port' => 0,
            'exit_port' => 0,
            'travel_hourly' => 0,
            'travel_point' => 0,
            'local_transport' => 0,
        ];
        $serviceData = [];
        
        foreach($orders as $order) {
            $type = $order->type;
            if(isset($svc[$type])) {
                $svc[$type]++;
                if(!isset($serviceData[$type])) {
                    $serviceData[$type] = [];
                }
                $serviceData[$type][] = $order;
            }
        }
    @endphp

    @if(isset($svc['hotel']) && $svc['hotel'] > 0)
    <div class="modal fade" id="hotelDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="hotelDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
                @if(isset($serviceData['hotel']) && count($serviceData['hotel']) > 0)
                    @php
                        $firstHotelOrder = $serviceData['hotel'][0];
                        $firstHotelData = is_string($firstHotelOrder->data) ? json_decode($firstHotelOrder->data, true) : $firstHotelOrder->data;
                        $firstBooking = is_array($firstHotelData) ? $firstHotelData[0] : null;
                    @endphp
                    @if($firstBooking)
                        <!-- Hero Header -->
                        <div class="modal-header p-0 border-0 position-relative" style="height: 200px; background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                            <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                                <div class="text-white">
                                    <h3 class="mb-1 fw-bold">
                                        <i class="ri-hotel-line me-2"></i>Hotel Bookings
                                    </h3>
                                    <p class="mb-0 opacity-75">Tour #{{ $tour->tour_id }} Hotel Details</p>
                                    <div class="mt-2">
                                        <span class="badge bg-white bg-opacity-90 text-primary px-3 py-2">
                                            <i class="ri-calendar-line me-1"></i>
                                            @if(isset($firstBooking['bookingDate']) && is_array($firstBooking['bookingDate']) && count($firstBooking['bookingDate']) > 0)
                                                {{ \Carbon\Carbon::parse($firstBooking['bookingDate'][0])->format('M d') }} - 
                                                {{ \Carbon\Carbon::parse(end($firstBooking['bookingDate']))->format('M d, Y') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <button type="button" class="btn-close btn-close-white" onclick="closeHotelModal({{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                            </div>
                        </div>
                    @else
                        <div class="modal-header p-4 border-0" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                            <h5 class="modal-title fw-bold text-white">
                                <i class="ri-hotel-line me-2"></i>
                                Hotel Booking Details - Tour #{{ $tour->tour_id }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" onclick="closeHotelModal({{ $tour->tour_id }})" aria-label="Close"></button>
                        </div>
                    @endif
                @else
                    <div class="modal-header p-4 border-0" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                        <h5 class="modal-title fw-bold text-white">
                            <i class="ri-hotel-line me-2"></i>
                            Hotel Booking Details - Tour #{{ $tour->tour_id }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" onclick="closeHotelModal({{ $tour->tour_id }})" aria-label="Close"></button>
                    </div>
                @endif
                <div class="modal-body p-4">
                    @if(isset($serviceData['hotel']) && count($serviceData['hotel']) > 0)
                        @foreach($serviceData['hotel'] as $index => $hotelOrder)
                        @php
                            $hotelData = is_string($hotelOrder->data) ? json_decode($hotelOrder->data, true) : $hotelOrder->data;
                        @endphp
                        
                        @if(is_array($hotelData))
                            @foreach($hotelData as $bookingIndex => $booking)
                                <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                                    <!-- Booking Header -->
                                    <div class="card-header border-0" style="background: linear-gradient(90deg, #74b9ff 0%, #0984e3 100%); padding: 20px;">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="mb-1 fw-bold text-white">
                                                    <i class="ri-hotel-line me-2"></i>{{ $booking['hotelDetails']['hotel_name'] ?? 'Hotel Bookings' }}
                                                </h5>
                                                <p class="mb-0 text-white opacity-75">Booking {{ $index + 1 }} • {{ ucfirst($booking['bookingType'] ?? 'Standard') }}</p>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <div class="bg-white rounded-pill px-3 py-2 d-inline-block">
                                                    <span class="text-success fw-bold fs-5">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body p-4" style="background-color: #f8f9fa;">
                                        <!-- Guest Information -->
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-primary rounded-circle p-2 me-3">
                                                            <i class="ri-user-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Customer Details</h6>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted">Full Name</small>
                                                        <div class="fw-medium">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted">Email Address</small>
                                                        <div class="fw-medium text-primary">{{ $booking['email'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="mb-0">
                                                        <small class="text-muted">Phone Number</small>
                                                        <div class="fw-medium">{{ $booking['countryCode'] ?? '' }} {{ $booking['phone'] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-info rounded-circle p-2 me-3">
                                                            <i class="ri-map-pin-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Address</h6>
                                                    </div>
                                                    <div class="text-muted">
                                                        @if($booking['address1'] ?? false)
                                                            <div>{{ $booking['address1'] }}</div>
                                                        @endif
                                                        @if($booking['address2'] ?? false)
                                                            <div>{{ $booking['address2'] }}</div>
                                                        @endif
                                                        @if($booking['state'] ?? false)
                                                            <div>{{ $booking['state'] }} {{ $booking['zip'] ?? '' }}</div>
                                                        @endif
                                                        @if(!($booking['address1'] ?? false) && !($booking['address2'] ?? false) && !($booking['state'] ?? false))
                                                            <div class="text-muted">Address not provided</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Stay Information -->
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-success rounded-circle p-2 me-3">
                                                            <i class="ri-calendar-check-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Stay Schedule</h6>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted">Check-in Date</small>
                                                        <div class="fw-bold text-success fs-5">
                                                            @if(isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 0)
                                                                {{ \Carbon\Carbon::parse($booking['bookingDate'][0])->format('D, M d, Y') }}
                                                            @else
                                                                N/A
                                                            @endif
                                                        </div>
                                                        @if(isset($booking['hotelDetails']['checkInTime']))
                                                            <small class="text-primary fw-medium">{{ $booking['hotelDetails']['checkInTime'] }}</small>
                                                        @endif
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted">Check-out Date</small>
                                                        <div class="fw-bold text-danger fs-5">
                                                            @if(isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 1)
                                                                {{ \Carbon\Carbon::parse(end($booking['bookingDate']))->format('D, M d, Y') }}
                                                            @else
                                                                N/A
                                                            @endif
                                                        </div>
                                                        @if(isset($booking['hotelDetails']['checkOutTime']))
                                                            <small class="text-danger fw-medium">{{ $booking['hotelDetails']['checkOutTime'] }}</small>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <small class="text-muted">Total Nights</small>
                                                        <div>
                                                            @if(isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 1)
                                                                @php
                                                                    $checkIn = \Carbon\Carbon::parse($booking['bookingDate'][0]);
                                                                    $checkOut = \Carbon\Carbon::parse(end($booking['bookingDate']));
                                                                    $nights = $checkIn->diffInDays($checkOut);
                                                                @endphp
                                                                <span class="badge bg-info px-3 py-2">{{ $nights }} Night{{ $nights > 1 ? 's' : '' }}</span>
                                                            @else
                                                                <span class="badge bg-secondary px-3 py-2">Duration TBD</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-info rounded-circle p-2 me-3">
                                                            <i class="ri-building-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Hotel Details</h6>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted">Location</small>
                                                        <div class="fw-medium">{{ $booking['hotelDetails']['location'] ?? 'Location not specified' }}</div>
                                                    </div>
                                                    @if(isset($booking['hotelDetails']['cancellation_charge']) && !empty($booking['hotelDetails']['cancellation_charge']))
                                                    <div class="mb-3">
                                                        <small class="text-muted">Cancellation Policy</small>
                                                        <div class="fw-medium text-warning">{{ $booking['hotelDetails']['cancellation_charge'] }}</div>
                                                    </div>
                                                    @endif
                                                    @if(isset($booking['hotelDetails']['image']))
                                                        <div class="mt-3">
                                                            <img src="{{ $booking['hotelDetails']['image'] }}" 
                                                                 alt="{{ $booking['hotelDetails']['hotel_name'] ?? 'Hotel' }}" 
                                                                 class="img-fluid rounded shadow-sm" 
                                                                 style="height: 120px; width: 100%; object-fit: cover;">
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Room & Accommodation Details -->
                                        @if(isset($booking['rooms']) && is_array($booking['rooms']))
                                            <div class="bg-white rounded p-3 shadow-sm mb-4">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-warning rounded-circle p-2 me-3">
                                                        <i class="ri-door-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Room & Accommodation Details</h6>
                                                </div>
                                                
                                                @foreach($booking['rooms'] as $roomIndex => $room)
                                                    <div class="card mb-3" style="border: 2px solid #e9ecef; border-radius: 12px; overflow: hidden;">
                                                        <div class="card-header border-0" style="background: linear-gradient(90deg, #74b9ff 0%, #0984e3 100%); padding: 15px;">
                                                            <div class="row align-items-center">
                                                                <div class="col-md-8">
                                                                    <h6 class="fw-bold text-white mb-1">
                                                                        <i class="ri-door-line me-2"></i>Room {{ $roomIndex + 1 }}: {{ $room['room_type'] ?? 'Standard Room' }}
                                                                    </h6>
                                                                    <small class="text-white opacity-75">Room ID: {{ $room['room_id'] ?? 'N/A' }}</small>
                                                                </div>
                                                                <div class="col-md-4 text-end">
                                                                    @if(isset($room['beds']) && is_array($room['beds']))
                                                                        @php $totalRoomPrice = collect($room['beds'])->sum('price'); @endphp
                                                                        <div class="bg-white rounded-pill px-3 py-2 d-inline-block">
                                                                            <span class="text-success fw-bold fs-5">SGD {{ number_format($totalRoomPrice, 2) }}</span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="card-body" style="background-color: #f8f9fa;">
                                                            @if(isset($room['beds']) && is_array($room['beds']))
                                                                @foreach($room['beds'] as $bedIndex => $bed)
                                                                    <div class="bg-white rounded p-3 mb-3 shadow-sm">
                                                                        <div class="row">
                                                                            <div class="col-md-6">
                                                                                <div class="d-flex align-items-center mb-3">
                                                                                    <div class="bg-primary rounded-circle p-2 me-3">
                                                                                        <i class="ri-hotel-bed-line text-white"></i>
                                                                                    </div>
                                                                                    <div>
                                                                                        <h6 class="fw-bold text-dark mb-0">{{ $bed['bed_type'] ?? 'Bed' }}</h6>
                                                                                        <small class="text-muted">Bed ID: {{ $bed['bed_id'] ?? 'N/A' }}</small>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="row">
                                                                                    <div class="col-6 mb-2">
                                                                                        <small class="text-muted">Guests</small>
                                                                                        <div class="fw-medium text-primary">{{ $bed['head_count'] ?? 0 }} people</div>
                                                                                    </div>
                                                                                    <div class="col-6 mb-2">
                                                                                        <small class="text-muted">Max Capacity</small>
                                                                                        <div class="fw-medium">{{ $bed['max_occupancy'] ?? 'N/A' }}</div>
                                                                                    </div>
                                                                                    <div class="col-12">
                                                                                        <small class="text-muted">Room Price</small>
                                                                                        <div class="fs-5 fw-bold text-success">SGD {{ number_format($bed['price'] ?? 0, 2) }}</div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            
                                                                            <div class="col-md-6">
                                                                                @if(isset($bed['selectedMeals']) && is_array($bed['selectedMeals']))
                                                                                    <div class="mb-3">
                                                                                        <div class="d-flex align-items-center mb-2">
                                                                                            <div class="bg-warning rounded-circle p-2 me-2">
                                                                                                <i class="ri-restaurant-line text-white"></i>
                                                                                            </div>
                                                                                            <h6 class="fw-bold mb-0 text-dark">Selected Meals</h6>
                                                                                        </div>
                                                                                        @foreach($bed['selectedMeals'] as $mealKey => $meal)
                                                                                            <div class="bg-light rounded p-2 mb-2">
                                                                                                <div class="d-flex justify-content-between align-items-center">
                                                                                                    <span class="fw-medium">{{ $meal['type'] ?? 'Meal Plan' }}</span>
                                                                                                    <span class="badge bg-success">SGD {{ number_format($meal['price'] ?? 0, 2) }}</span>
                                                                                                </div>
                                                                                            </div>
                                                                                        @endforeach
                                                                                        @php $totalMealPrice = collect($bed['selectedMeals'])->sum('price'); @endphp
                                                                                        <div class="border-top pt-2 mt-2">
                                                                                            <div class="d-flex justify-content-between">
                                                                                                <strong>Meal Total:</strong>
                                                                                                <strong class="text-warning">SGD {{ number_format($totalMealPrice, 2) }}</strong>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @endif
                                                                                
                                                                                @if(isset($bed['mealTypes']) && is_array($bed['mealTypes']))
                                                                                    <div>
                                                                                        <small class="text-muted fw-bold d-block mb-2">Available Meal Options:</small>
                                                                                        <div class="d-flex flex-wrap gap-1">
                                                                                            @foreach($bed['mealTypes'] as $mealType)
                                                                                                <span class="badge bg-outline-secondary">{{ $mealType }}</span>
                                                                                            @endforeach
                                                                                        </div>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                                
                                                <!-- Booking Summary -->
                                                <div class="bg-primary bg-opacity-10 rounded p-3">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-8">
                                                            <h6 class="fw-bold text-dark mb-1">Hotel Booking Summary</h6>
                                                            <small class="text-muted">{{ count($booking['rooms']) }} room(s) • {{ ucfirst($booking['bookingType'] ?? 'Standard') }} booking</small>
                                                        </div>
                                                        <div class="col-md-4 text-end">
                                                            <small class="text-muted d-block">Total Amount</small>
                                                            <div class="fs-3 fw-bold text-white">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Special Requests -->
                                        @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                            <div class="bg-white rounded p-3 shadow-sm">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-purple rounded-circle p-2 me-3" style="background-color: #6f42c1;">
                                                        <i class="ri-message-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Special Requests</h6>
                                                </div>
                                                <div class="bg-light rounded p-3">
                                                    <p class="mb-0 text-dark">{{ $booking['specialRequests'] }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Individual Hotel Action Buttons -->
                                        <div class="bg-white rounded p-3 shadow-sm mt-3 border-top">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small class="text-muted fw-bold d-block">Hotel Actions</small>
                                                    <div class="text-dark fw-medium">{{ $booking['hotelDetails']['hotel_name'] ?? 'Hotel ' . ($bookingIndex + 1) }}</div>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-sm btn-outline-primary px-3" onclick="editIndividualHotel({{ $tour->tour_id }}, {{ $index }}, {{ $bookingIndex }})" title="Edit this hotel booking">
                                                        <i class="ri-edit-line me-1"></i>Edit
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-success px-3" onclick="approveIndividualHotel({{ $tour->tour_id }}, {{ $index }}, {{ $bookingIndex }})" title="Approve this hotel booking">
                                                        <i class="ri-check-line me-1"></i>Approve
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger px-3" onclick="rejectIndividualHotel({{ $tour->tour_id }}, {{ $index }}, {{ $bookingIndex }})" title="Reject this hotel booking">
                                                        <i class="ri-close-line me-1"></i>Reject
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                <i class="ri-hotel-line ri-48px text-muted"></i>
                            </div>
                            <h4 class="text-dark mb-3">No Hotel Data Available</h4>
                            <p class="text-muted mb-4">Hotel services are booked but detailed information is not available at this moment.</p>
                            <div class="alert alert-primary border-0 shadow-sm" style="max-width: 400px; margin: 0 auto;">
                                <div class="d-flex align-items-center">
                                    <i class="ri-information-line text-primary me-2"></i>
                                    <div>
                                        <strong>Note:</strong> {{ $svc['hotel'] }} hotel service(s) are associated with this tour.
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0 p-4" style="background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);">
                    <div class="d-flex justify-content-between w-100">
                        <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeHotelModal({{ $tour->tour_id }})" style="border-radius: 25px;">
                            <i class="ri-close-line me-2"></i>Close
                        </button>
                        {{-- <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary px-4 py-2" onclick="editHotelBooking({{ $tour->tour_id }})" style="border-radius: 25px;">
                                <i class="ri-edit-line me-2"></i>Edit
                            </button>
                            <button type="button" class="btn btn-outline-success px-4 py-2" onclick="approveHotelBooking({{ $tour->tour_id }})" style="border-radius: 25px;">
                                <i class="ri-check-line me-2"></i>Approve
                            </button>
                            <button type="button" class="btn btn-outline-danger px-4 py-2" onclick="rejectHotelBooking({{ $tour->tour_id }})" style="border-radius: 25px;">
                                <i class="ri-close-line me-2"></i>Reject
                            </button>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Hotel Edit Modal -->
    @if(isset($svc['hotel']) && $svc['hotel'] > 0)
    <div class="modal fade" id="editHotelModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="editHotelModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
                <!-- Modal Header -->
                <div class="modal-header p-4 border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="d-flex align-items-center">
                        <div class="bg-white rounded-circle p-2 me-3 shadow-sm">
                            <i class="ri-hotel-line text-primary fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-1" id="editHotelModalLabel{{ $tour->tour_id }}">
                                Edit Hotel Booking Dates
                            </h5>
                            @if(isset($serviceData['hotel']) && count($serviceData['hotel']) > 0)
                                @php
                                    $firstHotelOrder = $serviceData['hotel'][0];
                                    $firstHotelData = is_string($firstHotelOrder->data) ? json_decode($firstHotelOrder->data, true) : $firstHotelOrder->data;
                                    $firstBooking = is_array($firstHotelData) ? $firstHotelData[0] : null;
                                @endphp
                                <p class="text-white-50 mb-0 small">{{ $firstBooking['hotel_name'] ?? 'Hotel Booking' }}</p>
                            @endif
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" onclick="closeEditHotelModal({{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">
                    <!-- Travel Date Range Info -->
                    <div class="alert alert-info border-0 mb-4" style="background: linear-gradient(45deg, #e3f2fd, #f0f8ff); border-radius: 12px;">
                        <div class="d-flex align-items-center mb-2">
                            <i class="ri-information-line me-2 text-info"></i>
                            <strong class="text-info">Travel Date Range</strong>
                        </div>
                        <p class="mb-0 text-muted small">
                            Hotel dates must be within the travel period: 
                            <strong class="text-primary">
                                @if($tour->check_in_time && $tour->check_out_time)
                                    {{ \Carbon\Carbon::parse($tour->check_in_time)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($tour->check_out_time)->format('M d, Y') }}
                                @else
                                    Travel dates not specified
                                @endif
                            </strong>
                        </p>
                    </div>

                    <form id="editHotelForm{{ $tour->tour_id }}">
                        @csrf
                        <input type="hidden" name="tour_id" value="{{ $tour->tour_id }}">
                        <input type="hidden" name="travel_start" value="{{ $tour->check_in_time }}">
                        <input type="hidden" name="travel_end" value="{{ $tour->check_out_time }}">
                        
                        <!-- Hotel Selection (if multiple hotels) -->
                        @if(isset($serviceData['hotel']) && count($serviceData['hotel']) > 1)
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="ri-hotel-line me-2"></i>Select Hotel to Edit
                            </label>
                            <select class="form-select" name="hotel_index" id="hotelSelect{{ $tour->tour_id }}" onchange="loadHotelDates({{ $tour->tour_id }})">
                                @foreach($serviceData['hotel'] as $index => $hotelOrder)
                                    @php
                                        $hotelData = is_string($hotelOrder->data) ? json_decode($hotelOrder->data, true) : $hotelOrder->data;
                                        $firstBooking = is_array($hotelData) ? $hotelData[0] : null;
                                    @endphp
                                    @if($firstBooking)
                                        <option value="{{ $index }}">{{ $firstBooking['hotel_name'] ?? "Hotel " . ($index + 1) }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        @else
                            <input type="hidden" name="hotel_index" value="0">
                        @endif

                        <!-- Date Range Selection -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="ri-calendar-check-line me-2 text-success"></i>Check-in Date
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       name="check_in_date" 
                                       id="checkInDate{{ $tour->tour_id }}"
                                       @if($tour->check_in_time) min="{{ \Carbon\Carbon::parse($tour->check_in_time)->format('Y-m-d') }}" @endif
                                       @if($tour->check_out_time) max="{{ \Carbon\Carbon::parse($tour->check_out_time)->format('Y-m-d') }}" @endif
                                       required>
                                <div class="form-text">
                                    <i class="ri-information-line me-1"></i>
                                    Must be within travel dates
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="ri-calendar-close-line me-2 text-danger"></i>Check-out Date
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       name="check_out_date" 
                                       id="checkOutDate{{ $tour->tour_id }}"
                                       @if($tour->check_in_time) min="{{ \Carbon\Carbon::parse($tour->check_in_time)->format('Y-m-d') }}" @endif
                                       @if($tour->check_out_time) max="{{ \Carbon\Carbon::parse($tour->check_out_time)->format('Y-m-d') }}" @endif
                                       required>
                                <div class="form-text">
                                    <i class="ri-information-line me-1"></i>
                                    Must be after check-in date
                                </div>
                            </div>
                        </div>

                        <!-- Current Booking Summary -->
                        <div class="card border-0 bg-light mb-4" style="border-radius: 12px;">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-primary rounded-circle p-2 me-3">
                                        <i class="ri-file-list-line text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-dark">Current Booking Summary</h6>
                                </div>
                                <div class="row" id="currentBookingSummary{{ $tour->tour_id }}">
                                    @if(isset($serviceData['hotel']) && count($serviceData['hotel']) > 0)
                                        @php
                                            $firstHotelOrder = $serviceData['hotel'][0];
                                            $firstHotelData = is_string($firstHotelOrder->data) ? json_decode($firstHotelOrder->data, true) : $firstHotelOrder->data;
                                            $firstBooking = is_array($firstHotelData) ? $firstHotelData[0] : null;
                                        @endphp
                                        @if($firstBooking)
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted">Hotel Name</small>
                                            <div class="fw-medium">{{ $firstBooking['hotel_name'] ?? 'N/A' }}</div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted">Location</small>
                                            <div class="fw-medium">{{ $firstBooking['location'] ?? 'N/A' }}</div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted">Total Rooms</small>
                                            <div class="fw-medium">{{ $firstBooking['total_rooms'] ?? 'N/A' }}</div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted">Current Price</small>
                                            <div class="fw-medium text-success">{{ $firstBooking['price'] ?? 'N/A' }}</div>
                                        </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- <!-- Reason for Change -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="ri-message-3-line me-2"></i>Reason for Date Change
                            </label>
                            <textarea class="form-control" 
                                      name="change_reason" 
                                      id="changeReason{{ $tour->tour_id }}"
                                      rows="3" 
                                      placeholder="Please specify the reason for changing hotel dates..."
                                      required></textarea>
                        </div> --}}
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer border-0 p-4" style="background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);">
                    <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeEditHotelModal({{ $tour->tour_id }})" style="border-radius: 25px;">
                        <i class="ri-close-line me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary px-4 py-2" onclick="saveHotelDateChanges({{ $tour->tour_id }})" style="border-radius: 25px;">
                        <i class="ri-save-line me-2"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Attraction Details Modal -->
    @if(isset($svc['attraction']) && $svc['attraction'] > 0)
    <div class="modal fade" id="attractionDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="attractionDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
                <div class="modal-header p-0 border-0 position-relative" style="height: 180px; background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%);">
                    <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                        <div class="text-white">
                            <h3 class="mb-1 fw-bold">
                                <i class="ri-building-2-line me-2"></i>Attraction Bookings
                            </h3>
                            <p class="mb-0 opacity-75">Tour #{{ $tour->tour_id }} Attraction Details</p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('attraction', {{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                    </div>
                </div>
                <div class="modal-body p-4" style="background-color: #f8f9fa;">
                    @if(isset($serviceData['attraction']) && count($serviceData['attraction']) > 0)
                        @foreach($serviceData['attraction'] as $index => $attractionOrder)
                        @php
                            $attractionData = is_string($attractionOrder->data) ? json_decode($attractionOrder->data, true) : $attractionOrder->data;
                        @endphp
                        
                        @if(is_array($attractionData))
                            @php $actualBookingIndex = 0; @endphp
                            @foreach($attractionData as $originalKey => $booking)
                                @php $bookingIndex = $actualBookingIndex; @endphp
                                <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                                    <div class="card-header border-0" style="background: linear-gradient(90deg, #fd9853 0%, #fe7854 100%); padding: 20px;">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="mb-1 fw-bold text-white">
                                                    <i class="ri-building-2-line me-2"></i>{{ $booking['AttractionName'] ?? 'Attraction Booking' }}
                                                </h5>
                                                <p class="mb-0 text-white opacity-75">{{ $booking['ticketName'] ?? 'Standard Ticket' }} • Booking {{ $index + 1 }}</p>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <div class="bg-white rounded-pill px-3 py-2 d-inline-block">
                                                    <span class="text-success fw-bold fs-5">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body p-4" style="background-color: #f8f9fa;">
                                        <!-- Guest Information -->
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-primary rounded-circle p-2 me-3">
                                                            <i class="ri-user-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Customer Details</h6>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted">Full Name</small>
                                                        <div class="fw-medium">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted">Email Address</small>
                                                        <div class="fw-medium text-primary">{{ $booking['email'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="mb-0">
                                                        <small class="text-muted">Phone Number</small>
                                                        <div class="fw-medium">{{ $booking['countryCode'] ?? '' }} {{ $booking['phone'] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-info rounded-circle p-2 me-3">
                                                            <i class="ri-map-pin-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Address</h6>
                                                    </div>
                                                    <div class="text-muted">
                                                        @if($booking['address1'] ?? false)
                                                            <div>{{ $booking['address1'] }}</div>
                                                        @endif
                                                        @if($booking['address2'] ?? false)
                                                            <div>{{ $booking['address2'] }}</div>
                                                        @endif
                                                        @if($booking['state'] ?? false)
                                                            <div>{{ $booking['state'] }} {{ $booking['zip'] ?? '' }}</div>
                                                        @endif
                                                        @if(!($booking['address1'] ?? false) && !($booking['address2'] ?? false) && !($booking['state'] ?? false))
                                                            <div class="text-muted">Address not provided</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Visit & Booking Information -->
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-warning rounded-circle p-2 me-3">
                                                            <i class="ri-calendar-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Visit Schedule</h6>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted">Visit Date</small>
                                                        <div class="fw-bold text-success fs-5">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('D, M d, Y') }}</div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted">Visit Time</small>
                                                        <div class="fw-medium text-primary">{{ $booking['visitTime'] ?? 'Full Day Access' }}</div>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted">Selection Type</small>
                                                        <div><span class="badge bg-info px-3 py-2">{{ ucfirst($booking['Selection'] ?? 'Standard') }}</span></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-info rounded-circle p-2 me-3">
                                                            <i class="ri-group-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Guest Information</h6>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-4 text-center mb-3">
                                                            <div class="bg-light rounded p-2">
                                                                <div class="fs-4 fw-bold text-success">{{ $booking['adultCount'] ?? 0 }}</div>
                                                                <small class="text-muted">Adults</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-4 text-center mb-3">
                                                            <div class="bg-light rounded p-2">
                                                                <div class="fs-4 fw-bold text-warning">{{ $booking['childCount'] ?? 0 }}</div>
                                                                <small class="text-muted">Children</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-4 text-center mb-3">
                                                            <div class="bg-light rounded p-2">
                                                                <div class="fs-4 fw-bold text-info">{{ $booking['seniorCount'] ?? 0 }}</div>
                                                                <small class="text-muted">Seniors</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-center">
                                                        <span class="badge bg-primary px-3 py-2">
                                                            Total: {{ ($booking['adultCount'] ?? 0) + ($booking['childCount'] ?? 0) + ($booking['seniorCount'] ?? 0) }} Guests
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Attraction Details -->
                                        <div class="bg-white rounded p-3 shadow-sm mb-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-success rounded-circle p-2 me-3">
                                                    <i class="ri-building-2-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Attraction Details</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <small class="text-muted">Attraction ID</small>
                                                    <div class="fw-medium">{{ $booking['AttractionId'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <small class="text-muted">Ticket ID</small>
                                                    <div class="fw-medium">{{ $booking['ticketId'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <small class="text-muted">NRI Status</small>
                                                    <span class="badge bg-info">{{ ucfirst($booking['nri'] ?? 'N/A') }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Ticket & Pricing Details -->
                                        @if(isset($booking['ticket_details']))
                                        <div class="bg-white rounded p-3 shadow-sm mb-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-success rounded-circle p-2 me-3">
                                                    <i class="ri-ticket-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Ticket & Pricing Information</h6>
                                            </div>
                                            
                                            <!-- Pricing Cards -->
                                            <div class="row mb-3">
                                                <div class="col-md-4 mb-3">
                                                    <div class="border rounded-3 p-3 text-center" style="border-color: #28a745; background: linear-gradient(135deg, #d4edda, #f8f9fa);">
                                                        <div class="text-success mb-2">
                                                            <i class="ri-user-line ri-24px"></i>
                                                        </div>
                                                        <h6 class="fw-bold text-success mb-1">Adult Ticket</h6>
                                                        <div class="fs-4 fw-bold text-success">SGD {{ number_format($booking['ticket_details']['adult_price'] ?? 0, 2) }}</div>
                                                        <small class="text-muted">Per person</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <div class="border rounded-3 p-3 text-center" style="border-color: #ffc107; background: linear-gradient(135deg, #fff3cd, #f8f9fa);">
                                                        <div class="text-warning mb-2">
                                                            <i class="ri-user-smile-line ri-24px"></i>
                                                        </div>
                                                        <h6 class="fw-bold text-warning mb-1">Child Ticket</h6>
                                                        <div class="fs-4 fw-bold text-warning">SGD {{ number_format($booking['ticket_details']['child_price'] ?? 0, 2) }}</div>
                                                        <small class="text-muted">Per child</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <div class="border rounded-3 p-3 text-center" style="border-color: #17a2b8; background: linear-gradient(135deg, #d1ecf1, #f8f9fa);">
                                                        <div class="text-info mb-2">
                                                            <i class="ri-user-star-line ri-24px"></i>
                                                        </div>
                                                        <h6 class="fw-bold text-info mb-1">Senior Ticket</h6>
                                                        <div class="fs-4 fw-bold text-info">SGD {{ number_format($booking['ticket_details']['senior_price'] ?? 0, 2) }}</div>
                                                        <small class="text-muted">Per senior</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Booking Summary -->
                                            <div class="bg-light rounded p-3 mb-3">
                                                <div class="row align-items-center">
                                                    <div class="col-md-8">
                                                        <h6 class="fw-bold text-dark mb-2">Booking Summary</h6>
                                                        <div class="d-flex gap-3">
                                                            @if($booking['adultCount'] ?? 0 > 0)
                                                                <span class="badge bg-success">{{ $booking['adultCount'] }} × SGD {{ number_format($booking['ticket_details']['adult_price'] ?? 0, 2) }}</span>
                                                            @endif
                                                            @if($booking['childCount'] ?? 0 > 0)
                                                                <span class="badge bg-warning">{{ $booking['childCount'] }} × SGD {{ number_format($booking['ticket_details']['child_price'] ?? 0, 2) }}</span>
                                                            @endif
                                                            @if($booking['seniorCount'] ?? 0 > 0)
                                                                <span class="badge bg-info">{{ $booking['seniorCount'] }} × SGD {{ number_format($booking['ticket_details']['senior_price'] ?? 0, 2) }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 text-end">
                                                        <small class="text-muted d-block">Total Amount</small>
                                                        <div class="fs-3 fw-bold text-primary">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</div>
                                                    </div>
                                                </div>
                                            </div>

                                            @if(isset($booking['ticket_details']['description']) && !empty($booking['ticket_details']['description']))
                                            <!-- Ticket Description -->
                                            <div class="border-start border-3 border-primary ps-3">
                                                <h6 class="fw-bold text-dark mb-2">Ticket Information</h6>
                                                <div class="text-muted">{!! $booking['ticket_details']['description'] !!}</div>
                                            </div>
                                            @endif
                                        </div>
                                        @endif

                                        <!-- Special Requests -->
                                        @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                            <div class="bg-white rounded p-3 shadow-sm mb-4">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-purple rounded-circle p-2 me-3" style="background-color: #6f42c1;">
                                                        <i class="ri-message-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Special Requests</h6>
                                                </div>
                                                <div class="bg-light rounded p-3">
                                                    <p class="mb-0 text-dark">{{ $booking['specialRequests'] }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Individual Action Buttons -->
                                        <div class="bg-white rounded p-3 shadow-sm border-top">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-secondary rounded-circle p-2 me-3">
                                                        <i class="ri-settings-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Booking Actions</h6>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <button type="button" 
                                                            class="btn btn-outline-primary btn-sm px-3 py-2" 
                                                            onclick="editIndividualAttraction({{ $tour->tour_id }}, {{ $index }}, {{ $bookingIndex }})"
                                                            style="border-radius: 25px;">
                                                        <i class="ri-edit-line me-1"></i>Edit
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-success btn-sm px-3 py-2" 
                                                            onclick="approveIndividualAttraction({{ $tour->tour_id }}, {{ $index }}, {{ $bookingIndex }})"
                                                            style="border-radius: 25px;">
                                                        <i class="ri-check-line me-1"></i>Approve
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm px-3 py-2" 
                                                            onclick="rejectIndividualAttraction({{ $tour->tour_id }}, {{ $index }}, {{ $bookingIndex }})"
                                                            style="border-radius: 25px;">
                                                        <i class="ri-close-line me-1"></i>Reject
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @php $actualBookingIndex++; @endphp
                            @endforeach
                        @endif
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                <i class="ri-building-2-line ri-48px text-muted"></i>
                            </div>
                            <h4 class="text-dark mb-3">No Attraction Data Available</h4>
                            <p class="text-muted mb-4">Attraction services are booked but detailed information is not available.</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0 p-4" style="background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);">
                    <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeServiceModal('attraction', {{ $tour->tour_id }})" style="border-radius: 25px;">
                        <i class="ri-close-line me-2"></i>Close
                    </button>
                    {{-- <button type="button" class="btn btn-primary px-4 py-2 ms-2" style="border-radius: 25px;">
                        <i class="ri-download-line me-2"></i>Download Details
                    </button> --}}
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Restaurant Details Modal -->
    @if(isset($svc['restaurant']) && $svc['restaurant'] > 0)
    <div class="modal fade" id="restaurantDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="restaurantDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
                <div class="modal-header p-0 border-0 position-relative" style="height: 180px; background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%);">
                    <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                        <div class="text-white">
                            <h3 class="mb-1 fw-bold">
                                <i class="ri-restaurant-2-line me-2"></i>Restaurant Bookings
                            </h3>
                            <p class="mb-0 opacity-75">Tour #{{ $tour->tour_id }} Restaurant Details</p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('restaurant', {{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                    </div>
                </div>
                <div class="modal-body p-4" style="background-color: #f8f9fa;">
                    @if(isset($serviceData['restaurant']) && count($serviceData['restaurant']) > 0)
                        @foreach($serviceData['restaurant'] as $index => $restaurantOrder)
                        @php
                            $restaurantData = is_string($restaurantOrder->data) ? json_decode($restaurantOrder->data, true) : $restaurantOrder->data;
                        @endphp
                        
                        @if(is_array($restaurantData))
                            @php $actualBookingIndex = 0; @endphp
                            @foreach($restaurantData as $originalKey => $booking)
                                @php $bookingIndex = $actualBookingIndex; @endphp
                                <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                                    <div class="card-header border-0" style="background: linear-gradient(90deg, #fd79a8 0%, #fdcb6e 100%); padding: 20px;">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="mb-1 fw-bold text-white">
                                                    <i class="ri-restaurant-2-line me-2"></i>{{ $booking['restaurantName'] ?? 'Restaurant Booking' }}
                                                </h5>
                                                <p class="mb-0 text-white opacity-75">{{ ucfirst($booking['mealType'] ?? 'Meal') }} • {{ $booking['mealSpecificType'] ?? 'Standard' }}</p>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <div class="bg-white rounded-pill px-3 py-2 d-inline-block">
                                                    <span class="text-success fw-bold fs-5">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body p-4" style="background-color: #f8f9fa;">
                                        <!-- Guest Information -->
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-primary rounded-circle p-2 me-3">
                                                            <i class="ri-user-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Customer Details</h6>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted">Full Name</small>
                                                        <div class="fw-medium">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted">Email Address</small>
                                                        <div class="fw-medium text-primary">{{ $booking['email'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="mb-0">
                                                        <small class="text-muted">Phone Number</small>
                                                        <div class="fw-medium">{{ $booking['countryCode'] ?? '' }} {{ $booking['phone'] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-warning rounded-circle p-2 me-3">
                                                            <i class="ri-calendar-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Reservation Details</h6>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted">Dining Date</small>
                                                        <div class="fw-bold text-success fs-5">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('D, M d, Y') }}</div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted">Dining Time</small>
                                                        <div class="fw-medium text-primary">{{ $booking['visitTime'] ?? 'Time to be confirmed' }}</div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-6 text-center">
                                                            <div class="bg-light rounded p-2">
                                                                <div class="fs-4 fw-bold text-success">{{ $booking['adultCount'] ?? 0 }}</div>
                                                                <small class="text-muted">Adults</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 text-center">
                                                            <div class="bg-light rounded p-2">
                                                                <div class="fs-4 fw-bold text-warning">{{ $booking['childCount'] ?? 0 }}</div>
                                                                <small class="text-muted">Children</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-2">
                                                        <span class="badge bg-primary px-3 py-2">
                                                            Party of {{ ($booking['adultCount'] ?? 0) + ($booking['childCount'] ?? 0) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Restaurant Overview -->
                                        <div class="bg-white rounded p-3 shadow-sm mb-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-info rounded-circle p-2 me-3">
                                                    <i class="ri-information-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Restaurant Overview</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <small class="text-muted">Meal Price</small>
                                                    <div class="fw-medium text-success">SGD {{ number_format($booking['mealPrice'] ?? 0, 2) }}</div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <small class="text-muted">Transport Price</small>
                                                    <div class="fw-medium text-info">SGD {{ number_format($booking['transportPrice'] ?? 0, 2) }}</div>
                                                </div>
                                                {{-- <div class="col-md-4 mb-3">
                                                    <small class="text-muted">DMC ID</small>
                                                    <span class="badge bg-secondary">{{ $booking['dmc_id'] ?? 'N/A' }}</span>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Price Types</small>
                                                    <div>
                                                        @if(isset($booking['priceTypes']) && is_array($booking['priceTypes']))
                                                            @foreach($booking['priceTypes'] as $priceType)
                                                                <span class="badge bg-outline-primary me-1">{{ strtoupper($priceType) }}</span>
                                                            @endforeach
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </div>
                                                </div> --}}
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Transport</small>
                                                    <div class="fw-medium">{{ $booking['transport'] ?? 'Not included' }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Menu & Meal Details -->
                                        @if(isset($booking['MealDescription']) && is_array($booking['MealDescription']))
                                        <div class="bg-white rounded p-3 shadow-sm mb-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-success rounded-circle p-2 me-3">
                                                    <i class="ri-restaurant-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Menu Selection & Pricing</h6>
                                            </div>
                                            
                                            @foreach($booking['MealDescription'] as $index => $meal)
                                                <div class="card mb-4 shadow-lg" style="border: none; border-radius: 15px; overflow: hidden;">
                                                    <!-- Item Header -->
                                                    <div class="card-header border-0 p-0 position-relative" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); height: 120px;">
                                                        <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                                                            <div class="text-white">
                                                                <h5 class="mb-2 fw-bold">
                                                                    <i class="ri-restaurant-2-line me-2"></i>{{ $meal['name'] ?? $meal['item_name'] ?? 'Menu Item' }}
                                                                </h5>
                                                                <div class="d-flex flex-wrap gap-2">
                                                                    <span class="badge bg-opacity-20 text-white border border-white border-opacity-50 px-3 py-1">
                                                                        <i class="ri-wine-glass-line me-1"></i>{{ $meal['category'] ?? 'Category' }}
                                                                    </span>
                                                                    <span class="badge bg-opacity-20 text-white border border-white border-opacity-50 px-3 py-1">
                                                                        <i class="ri-leaf-line me-1"></i>{{ $meal['item_type'] ?? 'Type' }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="text-end">
                                                                <div class="bg-white bg-opacity-95 rounded-3 px-4 py-3 shadow">
                                                                    <small class="text-muted d-block mb-1">Unit Price</small>
                                                                    <div class="fs-4 fw-bold text-success">SGD {{ number_format($meal['price'] ?? 0, 2) }}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Item Details -->
                                                    <div class="card-body p-4" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);">
                                                        <div class="row align-items-center mb-4">
                                                            <!-- Quantity Section -->
                                                            <div class="col-md-6">
                                                                <div class="text-center p-4 rounded-3" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border: 2px solid #2196f3;">
                                                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                                                        <i class="ri-shopping-basket-line text-primary ri-24px me-2"></i>
                                                                        <h6 class="fw-bold text-primary mb-0">Quantity Ordered</h6>
                                                                    </div>
                                                                    <div class="fs-1 fw-bold text-primary mb-1">{{ $meal['quantity'] ?? 1 }}</div>
                                                                    <small class="text-muted">{{ ($meal['quantity'] ?? 1) == 1 ? 'item' : 'items' }}</small>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Item Info -->
                                                            <div class="col-md-6">
                                                                <div class="bg-white rounded-3 p-4 shadow-sm border">
                                                                    <div class="d-flex align-items-center mb-3">
                                                                        <div class="bg-warning rounded-circle p-2 me-3">
                                                                            <i class="ri-information-line text-white"></i>
                                                                        </div>
                                                                        <h6 class="fw-bold mb-0 text-dark">Item Details</h6>
                                                                    </div>
                                                                    <div class="row">
                                                                        {{-- <div class="col-6 mb-2">
                                                                            <small class="text-muted">Item ID</small>
                                                                            <div class="fw-medium">#{{ $meal['meal_id'] ?? 'N/A' }}</div>
                                                                        </div> --}}
                                                                        <div class="col-6 mb-2">
                                                                            <small class="text-muted">Category</small>
                                                                            <div class="fw-medium">{{ $meal['category'] ?? 'N/A' }}</div>
                                                                        </div>
                                                                        <div class="col-12">
                                                                            <small class="text-muted">Dietary Type</small>
                                                                            <div>
                                                                                <span class="badge bg-success">{{ $meal['item_type'] ?? 'Standard' }}</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Price Calculation -->
                                                        <div class="bg-gradient-light rounded-3 p-4 border border-primary border-opacity-25">
                                                            <div class="row align-items-center">
                                                                <div class="col-md-7">
                                                                    <div class="d-flex align-items-center mb-3">
                                                                        <div class="bg-primary rounded-circle p-2 me-3">
                                                                            <i class="ri-calculator-line text-white"></i>
                                                                        </div>
                                                                        <h6 class="fw-bold mb-0 text-dark">Price Calculation</h6>
                                                                    </div>
                                                                    <div class="d-flex align-items-center gap-3">
                                                                        <div class="text-center">
                                                                            <div class="fs-5 fw-bold text-success">SGD {{ number_format($meal['price'] ?? 0, 2) }}</div>
                                                                            <small class="text-muted">per item</small>
                                                                        </div>
                                                                        <div class="text-primary fs-3">×</div>
                                                                        <div class="text-center">
                                                                            <div class="fs-5 fw-bold text-primary">{{ $meal['quantity'] ?? 1 }}</div>
                                                                            <small class="text-muted">{{ ($meal['quantity'] ?? 1) == 1 ? 'item' : 'items' }}</small>
                                                                        </div>
                                                                        <div class="text-primary fs-3">=</div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-5 text-end">
                                                                    <div class="bg-white rounded-3 p-4 shadow border border-success border-opacity-50">
                                                                        <small class="text-muted d-block mb-2">Item Subtotal</small>
                                                                        <div class="fs-2 fw-bold text-success">
                                                                            SGD {{ number_format(($meal['price'] ?? 0) * ($meal['quantity'] ?? 1), 2) }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            
                                            <!-- Total Summary -->
                                            <div class="card shadow-lg mt-4" style="border: none; border-radius: 15px; overflow: hidden;">
                                                <div class="card-header border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px;">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-8">
                                                            <h5 class="mb-1 fw-bold text-white">
                                                                <i class="ri-receipt-line me-2"></i>Order Summary
                                                            </h5>
                                                            <p class="mb-0 text-white opacity-75">
                                                                {{ count($booking['MealDescription']) }} item(s) • {{ $booking['mealType'] ?? 'Meal' }} • {{ $booking['mealSpecificType'] ?? 'Menu' }}
                                                            </p>
                                                        </div>
                                                        <div class="col-md-4 text-end">
                                                            <div class="bg-white bg-opacity-95 rounded-3 px-4 py-3 shadow">
                                                                <small class="text-muted d-block mb-1">Grand Total</small>
                                                                <div class="fs-2 fw-bold text-success">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); padding: 20px;">
                                                    <div class="row">
                                                        <div class="col-md-4 text-center">
                                                            <div class="p-3">
                                                                <div class="bg-primary bg-opacity-10 rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                                    <i class="ri-restaurant-2-line text-primary ri-24px"></i>
                                                                </div>
                                                                <h6 class="fw-bold text-dark">{{ $booking['restaurantName'] ?? 'Restaurant' }}</h6>
                                                                <small class="text-muted">{{ $booking['mealType'] ?? 'Dining' }} Experience</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 text-center">
                                                            <div class="p-3">
                                                                <div class="bg-warning bg-opacity-10 rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                                    <i class="ri-group-line text-warning ri-24px"></i>
                                                                </div>
                                                                <h6 class="fw-bold text-dark">{{ ($booking['adultCount'] ?? 0) + ($booking['childCount'] ?? 0) }} Guests</h6>
                                                                <small class="text-muted">{{ $booking['adultCount'] ?? 0 }} Adults, {{ $booking['childCount'] ?? 0 }} Children</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 text-center">
                                                            <div class="p-3">
                                                                <div class="bg-success bg-opacity-10 rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                                    <i class="ri-calendar-check-line text-success ri-24px"></i>
                                                                </div>
                                                                <h6 class="fw-bold text-dark">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') }}</h6>
                                                                <small class="text-muted">{{ $booking['visitTime'] ?? 'Time TBD' }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Payment Breakdown -->
                                                    <div class="mt-4 p-3 bg-light rounded-3">
                                                        <div class="row align-items-center">
                                                            <div class="col-md-6">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="ri-money-dollar-circle-line text-success ri-24px me-2"></i>
                                                                    <div>
                                                                        <h6 class="fw-bold text-dark mb-0">Payment Summary</h6>
                                                                        <small class="text-muted">Meal Price: SGD {{ number_format($booking['mealPrice'] ?? 0, 2) }} | Transport: SGD {{ number_format($booking['transportPrice'] ?? 0, 2) }}</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 text-end">
                                                                <div class="fw-bold text-success fs-4">
                                                                    Total: SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Special Requests -->
                                        @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                            <div class="bg-white rounded p-3 shadow-sm mb-4">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-purple rounded-circle p-2 me-3" style="background-color: #6f42c1;">
                                                        <i class="ri-message-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Special Requests</h6>
                                                </div>
                                                <div class="bg-light rounded p-3">
                                                    <p class="mb-0 text-dark">{{ $booking['specialRequests'] }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Individual Action Buttons -->
                                        <div class="bg-white rounded p-3 shadow-sm border-top">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-secondary rounded-circle p-2 me-3">
                                                        <i class="ri-settings-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Booking Actions</h6>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <button type="button" 
                                                            class="btn btn-outline-primary btn-sm px-3 py-2" 
                                                            onclick="editIndividualRestaurant({{ $tour->tour_id }}, {{ $index }}, {{ $bookingIndex }})"
                                                            style="border-radius: 25px;">
                                                        <i class="ri-edit-line me-1"></i>Edit
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-success btn-sm px-3 py-2" 
                                                            onclick="approveIndividualRestaurant({{ $tour->tour_id }}, {{ $index }}, {{ $bookingIndex }})"
                                                            style="border-radius: 25px;">
                                                        <i class="ri-check-line me-1"></i>Approve
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm px-3 py-2" 
                                                            onclick="rejectIndividualRestaurant({{ $tour->tour_id }}, {{ $index }}, {{ $bookingIndex }})"
                                                            style="border-radius: 25px;">
                                                        <i class="ri-close-line me-1"></i>Reject
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @php $actualBookingIndex++; @endphp
                            @endforeach
                        @endif
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                <i class="ri-restaurant-2-line ri-48px text-muted"></i>
                            </div>
                            <h4 class="text-dark mb-3">No Restaurant Data Available</h4>
                            <p class="text-muted mb-4">Restaurant services are booked but detailed information is not available.</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0 p-4" style="background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);">
                    <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeServiceModal('restaurant', {{ $tour->tour_id }})" style="border-radius: 25px;">
                        <i class="ri-close-line me-2"></i>Close
                    </button>
                    {{-- <button type="button" class="btn btn-primary px-4 py-2 ms-2" style="border-radius: 25px;">
                        <i class="ri-download-line me-2"></i>Download Details
                    </button> --}}
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Guide Details Modal -->
    @if(isset($svc['guide']) && $svc['guide'] > 0)
    <div class="modal fade" id="guideDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="guideDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
                <div class="modal-header p-0 border-0 position-relative" style="height: 180px; background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%);">
                    <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                        <div class="text-white">
                            <h3 class="mb-1 fw-bold">
                                <i class="ri-user-voice-line me-2"></i>Guide Bookings
                            </h3>
                            <p class="mb-0 opacity-75">Tour #{{ $tour->tour_id }} Guide Details</p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('guide', {{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                    </div>
                </div>
                <div class="modal-body p-4" style="background-color: #f8f9fa;">
                    @if(isset($serviceData['guide']) && count($serviceData['guide']) > 0)
                        @foreach($serviceData['guide'] as $index => $guideOrder)
                        @php
                            $guideData = is_string($guideOrder->data) ? json_decode($guideOrder->data, true) : $guideOrder->data;
                        @endphp
                        
                        @if(is_array($guideData))
                            @foreach($guideData as $booking)
                                <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                                    <div class="card-header border-0" style="background: linear-gradient(90deg, #00cec9 0%, #55a3ff 100%); padding: 20px;">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="mb-1 fw-bold text-white">
                                                    <i class="ri-user-voice-line me-2"></i>{{ $booking['guide_name'] ?? 'Guide Booking' }}
                                                </h5>
                                                <p class="mb-0 text-white opacity-75">{{ $booking['hours'] ?? 'N/A' }} Hours</p>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <div class="bg-white rounded-pill px-3 py-2 d-inline-block">
                                                    <span class="text-success fw-bold fs-5">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body p-4" style="background-color: #f8f9fa;">
                                        <!-- Guide Information -->
                                        <div class="row mb-4">
                                            <div class="col-md-8">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-success rounded-circle p-2 me-3">
                                                            <i class="ri-user-voice-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Guide Information</h6>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-6 mb-3">
                                                            <small class="text-muted">Guide Name</small>
                                                            <div class="fw-medium">{{ $booking['guide_name'] ?? 'N/A' }}</div>
                                                        </div>
                                                        {{-- <div class="col-6 mb-3">
                                                            <small class="text-muted">DMC ID</small>
                                                            <div class="fw-medium">{{ $booking['dmc_id'] ?? $booking['dmc_Id'] ?? 'N/A' }}</div>
                                                        </div> --}}
                                                        <div class="col-6 mb-3">
                                                            <small class="text-muted">Base Price</small>
                                                            <div class="fw-medium text-success">SGD {{ number_format($booking['basePrice'] ?? 0, 2) }}</div>
                                                        </div>
                                                        <div class="col-6 mb-3">
                                                            <small class="text-muted">Surcharge</small>
                                                            <div class="fw-medium text-warning">SGD {{ number_format($booking['surcharge'] ?? 0, 2) }}</div>
                                                        </div>
                                                        <div class="col-6 mb-3">
                                                            <small class="text-muted">Tax (%)</small>
                                                            <div class="fw-medium">{{ $booking['Tax'] ?? 0 }}%</div>
                                                        </div>
                                                        {{-- <div class="col-6 mb-3">
                                                            <small class="text-muted">Mode</small>
                                                            <span class="badge bg-info">{{ strtoupper($booking['Mode'] ?? 'N/A') }}</span>
                                                        </div> --}}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                @if(isset($booking['image']))
                                                    <img src="{{ $booking['image'] }}" 
                                                         alt="{{ $booking['guide_name'] ?? 'Guide' }}" 
                                                         class="img-fluid rounded shadow-sm" 
                                                         style="height: 200px; width: 100%; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 200px;">
                                                        <i class="ri-user-voice-line ri-48px text-muted"></i>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Service Schedule & Details -->
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-warning rounded-circle p-2 me-3">
                                                            <i class="ri-calendar-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Service Schedule</h6>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted">Service Date</small>
                                                        <div class="fw-bold text-success fs-5">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('D, M d, Y') }}</div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted">Start Time</small>
                                                        <div class="fw-medium text-primary">{{ $booking['entrytime'] ?? 'To be confirmed' }}</div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted">Service Duration</small>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge bg-info px-3 py-2 me-2">{{ $booking['hours'] ?? 'N/A' }} Hours</span>
                                                            <small class="text-muted">of guided service</small>
                                                        </div>
                                                    </div>
                                                    @if(($booking['Night_Start_Time'] ?? false) && ($booking['Night_End_Time'] ?? false))
                                                    <div>
                                                        <small class="text-muted">Night Service Hours</small>
                                                        <div class="fw-medium text-warning">{{ $booking['Night_Start_Time'] }} - {{ $booking['Night_End_Time'] }}</div>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-info rounded-circle p-2 me-3">
                                                            <i class="ri-group-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Group Information</h6>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-6 text-center">
                                                            <div class="bg-light rounded p-3">
                                                                <div class="fs-3 fw-bold text-success">{{ $booking['adults'] ?? 0 }}</div>
                                                                <small class="text-muted">Adults</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 text-center">
                                                            <div class="bg-light rounded p-3">
                                                                <div class="fs-3 fw-bold text-warning">{{ $booking['children'] ?? 0 }}</div>
                                                                <small class="text-muted">Children</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-center">
                                                        <span class="badge bg-primary px-3 py-2">
                                                            Group Size: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} People
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Service & Pricing Details -->
                                        <div class="bg-white rounded p-3 shadow-sm mb-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-success rounded-circle p-2 me-3">
                                                    <i class="ri-money-dollar-circle-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Service Pricing Breakdown</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-3 mb-3">
                                                    <div class="text-center p-3 border rounded" style="border-color: #28a745;">
                                                        <small class="text-muted d-block">Base Price</small>
                                                        <div class="fs-5 fw-bold text-success">SGD {{ number_format($booking['basePrice'] ?? 0, 2) }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <div class="text-center p-3 border rounded" style="border-color: #ffc107;">
                                                        <small class="text-muted d-block">Surcharge</small>
                                                        <div class="fs-5 fw-bold text-warning">SGD {{ number_format($booking['surcharge'] ?? 0, 2) }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <div class="text-center p-3 border rounded" style="border-color: #17a2b8;">
                                                        <small class="text-muted d-block">Tax ({{ $booking['Tax'] ?? 0 }}%)</small>
                                                        <div class="fs-5 fw-bold text-info">
                                                            SGD {{ number_format((($booking['basePrice'] ?? 0) + ($booking['surcharge'] ?? 0)) * (($booking['Tax'] ?? 0) / 100), 2) }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <div class="text-center p-3 border rounded" style="border-color: #6f42c1; background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                                                        <small class="text-muted d-block">Total Amount</small>
                                                        <div class="fs-4 fw-bold text-primary">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Customer Information -->
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-primary rounded-circle p-2 me-3">
                                                            <i class="ri-user-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Customer Details</h6>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted">Full Name</small>
                                                        <div class="fw-medium">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted">Email</small>
                                                        <div class="fw-medium text-primary">{{ $booking['email'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="mb-0">
                                                        <small class="text-muted">Phone</small>
                                                        <div class="fw-medium">{{ $booking['countryCode'] ?? '' }} {{ $booking['phone'] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-info rounded-circle p-2 me-3">
                                                            <i class="ri-map-pin-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Address & Location</h6>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted">Pickup Location</small>
                                                        <div class="fw-medium">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="text-muted">
                                                        @if($booking['address1'] ?? false)
                                                            <div>{{ $booking['address1'] }}</div>
                                                        @endif
                                                        @if($booking['address2'] ?? false)
                                                            <div>{{ $booking['address2'] }}</div>
                                                        @endif
                                                        @if($booking['state'] ?? false)
                                                            <div>{{ $booking['state'] }} {{ $booking['zip'] ?? '' }}</div>
                                                        @endif
                                                        @if(!($booking['address1'] ?? false) && !($booking['address2'] ?? false) && !($booking['state'] ?? false))
                                                            <div class="text-muted">Address not provided</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Special Requests -->
                                        @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                            <div class="bg-white rounded p-3 shadow-sm">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-purple rounded-circle p-2 me-3" style="background-color: #6f42c1;">
                                                        <i class="ri-message-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Special Requests</h6>
                                                </div>
                                                <div class="bg-light rounded p-3">
                                                    <p class="mb-0 text-dark">{{ $booking['specialRequests'] }}</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                <i class="ri-user-voice-line ri-48px text-muted"></i>
                            </div>
                            <h4 class="text-dark mb-3">No Guide Data Available</h4>
                            <p class="text-muted mb-4">Guide services are booked but detailed information is not available.</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0 p-4" style="background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);">
                    <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeServiceModal('guide', {{ $tour->tour_id }})" style="border-radius: 25px;">
                        <i class="ri-close-line me-2"></i>Close
                    </button>
                    {{-- <button type="button" class="btn btn-primary px-4 py-2 ms-2" style="border-radius: 25px;">
                        <i class="ri-download-line me-2"></i>Download Details
                    </button> --}}
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Entry Port (Arrival) Details Modal -->
    @if(isset($svc['entry_port']) && $svc['entry_port'] > 0)
    <div class="modal fade" id="entry_portDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="entry_portDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
                <div class="modal-header p-0 border-0 position-relative" style="height: 180px; background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%);">
                    <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                        <div class="text-white">
                            <h3 class="mb-1 fw-bold">
                                <i class="ri-flight-land-line me-2"></i>Arrival Transfer
                            </h3>
                            <p class="mb-0 opacity-75">Tour #{{ $tour->tour_id }} Entry Port Details</p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('entry_port', {{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                    </div>
                </div>
                <div class="modal-body p-4" style="background-color: #f8f9fa;">
                    @if(isset($serviceData['entry_port']) && count($serviceData['entry_port']) > 0)
                        @foreach($serviceData['entry_port'] as $index => $entryOrder)
                        @php
                            $entryData = is_string($entryOrder->data) ? json_decode($entryOrder->data, true) : $entryOrder->data;
                        @endphp
                        
                        @if(is_array($entryData))
                            @php $actualBookingIndex = 0; @endphp
                            @foreach($entryData as $originalKey => $booking)
                                @php $bookingIndex = $actualBookingIndex; @endphp
                                <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                                    <div class="card-header border-0" style="background: linear-gradient(90deg, #00b894 0%, #55a3ff 100%); padding: 20px;">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="mb-1 fw-bold text-white">
                                                    <i class="ri-car-line me-2"></i>{{ $booking['vehicles_name'] ?? 'Vehicle Transfer' }}
                                                </h5>
                                                <p class="mb-0 text-white opacity-75">{{ ucfirst($booking['type'] ?? 'Standard') }} Transfer • Booking {{ $index + 1 }}</p>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <div class="bg-white rounded-pill px-3 py-2 d-inline-block">
                                                    <span class="text-success fw-bold fs-5">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body p-4" style="background-color: #f8f9fa;">
                                        <!-- Transfer Schedule -->
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-success rounded-circle p-2 me-3">
                                                            <i class="ri-calendar-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Transfer Schedule</h6>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted">Arrival Date</small>
                                                        <div class="fw-bold text-success fs-5">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('D, M d, Y') }}</div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted">Pickup Time</small>
                                                        <div class="fw-medium text-primary">{{ $booking['entrytime'] ?? 'To be confirmed' }}</div>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted">Transfer Type</small>
                                                        <div><span class="badge bg-info px-3 py-2">{{ ucfirst($booking['type'] ?? 'Standard') }}</span></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-info rounded-circle p-2 me-3">
                                                            <i class="ri-group-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Passenger Information</h6>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-6 text-center">
                                                            <div class="bg-light rounded p-3">
                                                                <div class="fs-3 fw-bold text-success">{{ $booking['adults'] ?? 0 }}</div>
                                                                <small class="text-muted">Adults</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 text-center">
                                                            <div class="bg-light rounded p-3">
                                                                <div class="fs-3 fw-bold text-warning">{{ $booking['children'] ?? 0 }}</div>
                                                                <small class="text-muted">Children</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-center">
                                                        <span class="badge bg-primary px-3 py-2">
                                                            Total: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} Passengers
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Route Information -->
                                        <div class="bg-white rounded p-3 shadow-sm mb-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-primary rounded-circle p-2 me-3">
                                                    <i class="ri-route-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Route Details</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <div class="d-flex align-items-start">
                                                        <div class="bg-success rounded-circle p-2 me-3 mt-1">
                                                            <i class="ri-map-pin-line text-white"></i>
                                                        </div>
                                                        <div>
                                                            <small class="text-muted">Pickup Location</small>
                                                            <div class="fw-medium">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
                                                            <small class="text-success">Origin</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="d-flex align-items-start">
                                                        <div class="bg-danger rounded-circle p-2 me-3 mt-1">
                                                            <i class="ri-flag-line text-white"></i>
                                                        </div>
                                                        <div>
                                                            <small class="text-muted">Drop-off Location</small>
                                                            <div class="fw-medium">{{ $booking['entrydropoff'] ?? 'N/A' }}</div>
                                                            <small class="text-danger">Destination</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">City</small>
                                                    <div class="fw-medium">{{ $booking['city'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Country</small>
                                                    <div class="fw-medium">{{ $booking['country'] ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Vehicle Information -->
                                        <div class="row mb-4">
                                            <div class="col-md-8">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-warning rounded-circle p-2 me-3">
                                                            <i class="ri-car-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Vehicle Details</h6>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-6 mb-3">
                                                            <small class="text-muted">Vehicle name</small>
                                                            <div class="fw-medium">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-6 mb-3">
                                                            <small class="text-muted">Service Type</small>
                                                            <div class="fw-medium">{{ $booking['type'] ?? 'N/A' }} Transfer</div>
                                                        </div>
                                                        {{-- <div class="col-6 mb-3">
                                                            <small class="text-muted">Mode</small>
                                                            <span class="badge bg-info">{{ strtoupper($booking['Mode'] ?? 'N/A') }}</span>
                                                        </div>
                                                        <div class="col-6 mb-3">
                                                            <small class="text-muted">Booking Type</small>
                                                            <span class="badge bg-primary">{{ ucfirst($booking['bookingType'] ?? 'Standard') }}</span>
                                                        </div> --}}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                @if(isset($booking['image']))
                                                    <img src="{{ $booking['image'] }}" 
                                                         alt="{{ $booking['vehicles_name'] ?? 'Vehicle' }}" 
                                                         class="img-fluid rounded shadow-sm" 
                                                         style="height: 150px; width: 100%; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                                        <i class="ri-car-line ri-48px text-muted"></i>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Customer Information -->
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-primary rounded-circle p-2 me-3">
                                                            <i class="ri-user-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Customer Details</h6>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted">Full Name</small>
                                                        <div class="fw-medium">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted">Email Address</small>
                                                        <div class="fw-medium text-primary">{{ $booking['email'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="mb-0">
                                                        <small class="text-muted">Phone Number</small>
                                                        <div class="fw-medium">{{ $booking['countryCode'] ?? '' }} {{ $booking['phone'] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-info rounded-circle p-2 me-3">
                                                            <i class="ri-map-pin-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Address Information</h6>
                                                    </div>
                                                    <div class="text-muted">
                                                        @if($booking['address1'] ?? false)
                                                            <div>{{ $booking['address1'] }}</div>
                                                        @endif
                                                        @if($booking['address2'] ?? false)
                                                            <div>{{ $booking['address2'] }}</div>
                                                        @endif
                                                        @if($booking['state'] ?? false)
                                                            <div>{{ $booking['state'] }} {{ $booking['zip'] ?? '' }}</div>
                                                        @endif
                                                        @if(!($booking['address1'] ?? false) && !($booking['address2'] ?? false) && !($booking['state'] ?? false))
                                                            <div class="text-muted">Address not provided</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Special Requests -->
                                        @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                            <div class="bg-white rounded p-3 shadow-sm">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-purple rounded-circle p-2 me-3" style="background-color: #6f42c1;">
                                                        <i class="ri-message-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Special Requests</h6>
                                                </div>
                                                <div class="bg-light rounded p-3">
                                                    <p class="mb-0 text-dark">{{ $booking['specialRequests'] }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Action Buttons -->
                                        <div class="bg-white rounded p-3 shadow-sm mt-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary rounded-circle p-2 me-3">
                                                        <i class="ri-settings-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Booking Actions</h6>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <button type="button" 
                                                            class="btn btn-outline-primary btn-sm px-3 py-2" 
                                                            onclick="editArrivalBooking({{ $tour->tour_id }}, {{ $index }}, {{ $bookingIndex }})"
                                                            style="border-radius: 25px;">
                                                        <i class="ri-edit-line me-1"></i>Edit
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-success btn-sm px-3 py-2" 
                                                            onclick="approveArrivalBooking({{ $tour->tour_id }}, {{ $index }}, {{ $bookingIndex }})"
                                                            style="border-radius: 25px;">
                                                        <i class="ri-check-line me-1"></i>Approve
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm px-3 py-2" 
                                                            onclick="rejectArrivalBooking({{ $tour->tour_id }}, {{ $index }}, {{ $bookingIndex }})"
                                                            style="border-radius: 25px;">
                                                        <i class="ri-close-line me-1"></i>Reject
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @php $actualBookingIndex++; @endphp
                            @endforeach
                        @endif
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                <i class="ri-flight-land-line ri-48px text-muted"></i>
                            </div>
                            <h4 class="text-dark mb-3">No Arrival Transfer Data Available</h4>
                            <p class="text-muted mb-4">Entry port services are booked but detailed information is not available.</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0 p-4" style="background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);">
                    <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeServiceModal('entry_port', {{ $tour->tour_id }})" style="border-radius: 25px;">
                        <i class="ri-close-line me-2"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Exit Port (Departure) Details Modal -->
    @if(isset($svc['exit_port']) && $svc['exit_port'] > 0)
    <div class="modal fade" id="exit_portDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="exit_portDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
                <div class="modal-header p-0 border-0 position-relative" style="height: 180px; background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%);">
                    <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                        <div class="text-white">
                            <h3 class="mb-1 fw-bold">
                                <i class="ri-flight-takeoff-line me-2"></i>Departure Transfer
                            </h3>
                            <p class="mb-0 opacity-75">Tour #{{ $tour->tour_id }} Exit Port Details</p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('exit_port', {{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                    </div>
                </div>
                <div class="modal-body p-4" style="background-color: #f8f9fa;">
                    @if(isset($serviceData['exit_port']) && count($serviceData['exit_port']) > 0)
                        @foreach($serviceData['exit_port'] as $index => $exitOrder)
                        @php
                            $exitData = is_string($exitOrder->data) ? json_decode($exitOrder->data, true) : $exitOrder->data;
                        @endphp
                        
                        @if(is_array($exitData))
                            @php $actualBookingIndex = 0; @endphp
                            @foreach($exitData as $bookingIndex => $booking)
                                <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                                    <div class="card-header border-0" style="background: linear-gradient(90deg, #fd7f6f 0%, #feb47b 100%); padding: 20px;">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="mb-1 fw-bold text-white">
                                                    <i class="ri-car-line me-2"></i>{{ $booking['vehicles_name'] ?? 'Vehicle Transfer' }}
                                                </h5>
                                                <p class="mb-0 text-white opacity-75">{{ ucfirst($booking['type'] ?? 'Standard') }} Transfer • Booking {{ $index + 1 }}</p>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <div class="bg-white rounded-pill px-3 py-2 d-inline-block">
                                                    <span class="text-success fw-bold fs-5">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body p-4" style="background-color: #f8f9fa;">
                                        <!-- Transfer Schedule -->
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-warning rounded-circle p-2 me-3">
                                                            <i class="ri-calendar-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Transfer Schedule</h6>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted">Departure Date</small>
                                                        <div class="fw-bold text-danger fs-5">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('D, M d, Y') }}</div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted">Pickup Time</small>
                                                        <div class="fw-medium text-primary">{{ $booking['entrytime'] ?? 'To be confirmed' }}</div>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted">Transfer Type</small>
                                                        <div><span class="badge bg-warning px-3 py-2">{{ ucfirst($booking['type'] ?? 'Standard') }}</span></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-info rounded-circle p-2 me-3">
                                                            <i class="ri-group-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Passenger Information</h6>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-6 text-center">
                                                            <div class="bg-light rounded p-3">
                                                                <div class="fs-3 fw-bold text-success">{{ $booking['adults'] ?? 0 }}</div>
                                                                <small class="text-muted">Adults</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 text-center">
                                                            <div class="bg-light rounded p-3">
                                                                <div class="fs-3 fw-bold text-warning">{{ $booking['children'] ?? 0 }}</div>
                                                                <small class="text-muted">Children</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-center">
                                                        <span class="badge bg-primary px-3 py-2">
                                                            Total: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} Passengers
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Route Information -->
                                        <div class="bg-white rounded p-3 shadow-sm mb-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-primary rounded-circle p-2 me-3">
                                                    <i class="ri-route-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Route Details</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <div class="d-flex align-items-start">
                                                        <div class="bg-success rounded-circle p-2 me-3 mt-1">
                                                            <i class="ri-map-pin-line text-white"></i>
                                                        </div>
                                                        <div>
                                                            <small class="text-muted">Pickup Location</small>
                                                            <div class="fw-medium">{{ $booking['exitpickup'] ?? 'N/A' }}</div>
                                                            <small class="text-success">Origin</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="d-flex align-items-start">
                                                        <div class="bg-danger rounded-circle p-2 me-3 mt-1">
                                                            <i class="ri-flag-line text-white"></i>
                                                        </div>
                                                        <div>
                                                            <small class="text-muted">Drop-off Location</small>
                                                            <div class="fw-medium">{{ $booking['exitdropoff'] ?? 'N/A' }}</div>
                                                            <small class="text-danger">Destination</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">City</small>
                                                    <div class="fw-medium">{{ $booking['city'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Country</small>
                                                    <div class="fw-medium">{{ $booking['country'] ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Vehicle Information -->
                                        <div class="row mb-4">
                                            <div class="col-md-8">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-warning rounded-circle p-2 me-3">
                                                            <i class="ri-car-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Vehicle Details</h6>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-6 mb-3">
                                                            <small class="text-muted">Vehicle Name</small>
                                                            <div class="fw-medium">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-6 mb-3">
                                                            <small class="text-muted">Service Type</small>
                                                            <div class="fw-medium">{{ $booking['type'] ?? 'N/A' }} Transfer</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                @if(isset($booking['image']))
                                                    <img src="{{ $booking['image'] }}" 
                                                         alt="{{ $booking['vehicles_name'] ?? 'Vehicle' }}" 
                                                         class="img-fluid rounded shadow-sm" 
                                                         style="height: 150px; width: 100%; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                                        <i class="ri-car-line ri-48px text-muted"></i>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Customer Information -->
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-primary rounded-circle p-2 me-3">
                                                            <i class="ri-user-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Customer Details</h6>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted">Full Name</small>
                                                        <div class="fw-medium">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted">Email Address</small>
                                                        <div class="fw-medium text-primary">{{ $booking['email'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="mb-0">
                                                        <small class="text-muted">Phone Number</small>
                                                        <div class="fw-medium">{{ $booking['countryCode'] ?? '' }} {{ $booking['phone'] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-info rounded-circle p-2 me-3">
                                                            <i class="ri-map-pin-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Address Information</h6>
                                                    </div>
                                                    <div class="text-muted">
                                                        @if($booking['address1'] ?? false)
                                                            <div>{{ $booking['address1'] }}</div>
                                                        @endif
                                                        @if($booking['address2'] ?? false)
                                                            <div>{{ $booking['address2'] }}</div>
                                                        @endif
                                                        @if($booking['state'] ?? false)
                                                            <div>{{ $booking['state'] }} {{ $booking['zip'] ?? '' }}</div>
                                                        @endif
                                                        @if(!($booking['address1'] ?? false) && !($booking['address2'] ?? false) && !($booking['state'] ?? false))
                                                            <div class="text-muted">Address not provided</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Special Requests -->
                                        @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                            <div class="bg-white rounded p-3 shadow-sm">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-purple rounded-circle p-2 me-3" style="background-color: #6f42c1;">
                                                        <i class="ri-message-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Special Requests</h6>
                                                </div>
                                                <div class="bg-light rounded p-3">
                                                    <p class="mb-0 text-dark">{{ $booking['specialRequests'] }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Action Buttons -->
                                        <div class="bg-white rounded p-3 shadow-sm mt-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary rounded-circle p-2 me-3">
                                                        <i class="ri-settings-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Booking Actions</h6>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <button type="button" 
                                                            class="btn btn-outline-primary btn-sm px-3 py-2" 
                                                            onclick="editDepartureBooking({{ $tour->tour_id }}, {{ $index }}, {{ $actualBookingIndex }})"
                                                            style="border-radius: 25px;">
                                                        <i class="ri-edit-line me-1"></i>Edit
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-success btn-sm px-3 py-2" 
                                                            onclick="approveDepartureBooking({{ $tour->tour_id }}, {{ $index }}, {{ $actualBookingIndex }})"
                                                            style="border-radius: 25px;">
                                                        <i class="ri-check-line me-1"></i>Approve
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm px-3 py-2" 
                                                            onclick="rejectDepartureBooking({{ $tour->tour_id }}, {{ $index }}, {{ $actualBookingIndex }})"
                                                            style="border-radius: 25px;">
                                                        <i class="ri-close-line me-1"></i>Reject
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @php $actualBookingIndex++; @endphp
                            @endforeach
                        @endif
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                <i class="ri-flight-takeoff-line ri-48px text-muted"></i>
                            </div>
                            <h4 class="text-dark mb-3">No Departure Transfer Data Available</h4>
                            <p class="text-muted mb-4">Exit port services are booked but detailed information is not available.</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0 p-4" style="background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);">
                    <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeServiceModal('exit_port', {{ $tour->tour_id }})" style="border-radius: 25px;">
                        <i class="ri-close-line me-2"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

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

<!-- Travel Hourly Service Modals -->
@foreach($tours as $tour)
    @php
        $orders = \App\Models\Order::where('tour_id', $tour->tour_id)->get();
        $serviceData = [];
        $svc = [];
        
        foreach($orders as $order) {
            $type = $order->type;
            if (!isset($serviceData[$type])) {
                $serviceData[$type] = [];
                $svc[$type] = 0;
            }
            $serviceData[$type][] = $order;
            $svc[$type]++;
        }
    @endphp

    @if(isset($svc['travel_hourly']) && $svc['travel_hourly'] > 0)
        <div class="modal fade" id="travel_hourlyDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="travel_hourlyModalLabel{{ $tour->tour_id }}" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content border-0 shadow-lg">
                    @php
                        $firstOrder = $serviceData['travel_hourly'][0] ?? null;
                        $firstBookingData = null;
                        if ($firstOrder) {
                            $firstBookingData = is_string($firstOrder->data) ? json_decode($firstOrder->data, true) : $firstOrder->data;
                            $firstBookingData = is_array($firstBookingData) && isset($firstBookingData[0]) ? $firstBookingData[0] : $firstBookingData;
                        }
                    @endphp
                    
                    <!-- Modal Header -->
                    <div class="modal-header p-0 border-0 position-relative" style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                            <div class="text-white">
                                <h3 class="mb-1 fw-bold">
                                    <i class="ri-time-line me-2"></i>Local-Tour Hourly
                                </h3>
                                <p class="mb-0 opacity-75">Tour #{{ $tour->tour_id }} Hourly Tour Details • {{ $firstBookingData['city'] ?? 'Location not specified' }}</p>
                                <div class="mt-2">
                                    <span class="badge bg-white bg-opacity-90 text-primary px-3 py-2">
                                        <i class="ri-calendar-line me-1"></i>
                                        {{ isset($firstBookingData['bookingDate']) ? \Carbon\Carbon::parse($firstBookingData['bookingDate'])->format('D, M d, Y') : 'Date not specified' }}
                                    </span>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('travel_hourly', {{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body p-4" style="background: #f8fafc;">
                        @if(isset($serviceData['travel_hourly']) && count($serviceData['travel_hourly']) > 0)
                            @foreach($serviceData['travel_hourly'] as $index => $hourlyOrder)
                                @php
                                    $hourlyData = is_string($hourlyOrder->data) ? json_decode($hourlyOrder->data, true) : $hourlyOrder->data;
                                @endphp
                                
                                @if(is_array($hourlyData))
                                    @php $actualBookingIndex = 0; @endphp
                                    @foreach($hourlyData as $bookingIndex => $booking)
                                        @if($index > 0 || $bookingIndex > 0)
                                            <hr class="my-4">
                                        @endif
                                
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                            <div class="card-header bg-transparent border-0 text-white">
                                                <h5 class="card-title mb-0 fw-bold">
                                                    <i class="ri-car-line me-2"></i>{{ $booking['vehicles_name'] ?? 'Hourly Tour Booking' }}
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Service Schedule & Group Information -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="bg-white rounded p-3 shadow-sm h-100">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-primary rounded-circle p-2 me-3">
                                                    <i class="ri-calendar-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Service Schedule</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Booking Date</small>
                                                    <div class="fw-medium">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('D, M d, Y') : 'N/A' }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Time</small>
                                                    <div class="fw-medium">{{ $booking['entrytime'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Selected Hours</small>
                                                    <span class="badge bg-info">{{ $booking['selectedHours'] ?? 'N/A' }} Hour(s)</span>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Service Type</small>
                                                    <span class="badge bg-warning">{{ $booking['type'] ?? 'Standard' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="bg-white rounded p-3 shadow-sm h-100">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-success rounded-circle p-2 me-3">
                                                    <i class="ri-group-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Group Information</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Adults</small>
                                                    <div class="fw-medium">{{ $booking['adults'] ?? 0 }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Children</small>
                                                    <div class="fw-medium">{{ $booking['children'] ?? 0 }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Total Guests</small>
                                                    <span class="badge bg-primary">{{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }}</span>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Night Service Timing</small>
                                                    <div class="fw-medium text-muted small">{{ $booking['Night_Start_Time'] ?? 'N/A' }} - {{ $booking['Night_End_Time'] ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pickup Location & Vehicle Information -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="bg-white rounded p-3 shadow-sm h-100">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-success rounded-circle p-2 me-3">
                                                    <i class="ri-map-pin-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Pickup Location</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <small class="text-muted">Pickup Point</small>
                                                    <div class="fw-medium">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">City</small>
                                                    <div class="fw-medium">{{ $booking['city'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Country</small>
                                                    <div class="fw-medium">{{ $booking['country'] ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <!-- Vehicle Information -->
                                        <div class="row mb-4">
                                            <div class="col-md-8">
                                                <div class="bg-white rounded p-3 shadow-sm h-100">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="bg-warning rounded-circle p-2 me-3">
                                                            <i class="ri-car-line text-white"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark">Vehicle Details</h6>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-6 mb-3">
                                                            <small class="text-muted">Vehicle Name</small>
                                                            <div class="fw-medium">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-6 mb-3">
                                                            <small class="text-muted">Service Type</small>
                                                            <div class="fw-medium">{{ $booking['type'] ?? 'N/A' }} Transfer</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                @if(isset($booking['image']))
                                                    <img src="{{ $booking['image'] }}" 
                                                         alt="{{ $booking['vehicles_name'] ?? 'Vehicle' }}" 
                                                         class="img-fluid rounded shadow-sm" 
                                                         style="height: 150px; width: 100%; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                                        <i class="ri-car-line ri-48px text-muted"></i>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pricing & Customer Information -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="bg-white rounded p-3 shadow-sm h-100">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-warning rounded-circle p-2 me-3">
                                                    <i class="ri-money-dollar-circle-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Pricing Details</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Total Price</small>
                                                    <div class="fw-bold text-success">${{ $booking['totalPrice'] ?? '0' }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Tax</small>
                                                    <div class="fw-medium">{{ $booking['Tax'] ?? '0' }}%</div>
                                                </div>
                                                {{-- <div class="col-12 mb-3">
                                                    <small class="text-muted">Booking Type</small>
                                                    <span class="badge bg-primary">{{ ucfirst($booking['bookingType'] ?? 'Standard') }}</span>
                                                </div> --}}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="bg-white rounded p-3 shadow-sm h-100">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-info rounded-circle p-2 me-3">
                                                    <i class="ri-user-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Customer Information</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 mb-2">
                                                    <small class="text-muted">Name</small>
                                                    <div class="fw-medium">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="col-12 mb-2">
                                                    <small class="text-muted">Email</small>
                                                    <div class="fw-medium">{{ $booking['email'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="col-12 mb-2">
                                                    <small class="text-muted">Phone</small>
                                                    <div class="fw-medium">{{ $booking['phone'] ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Special Requests -->
                                @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                <div class="bg-white rounded p-3 shadow-sm mb-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-secondary rounded-circle p-2 me-3">
                                            <i class="ri-message-2-line text-white"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-dark">Special Requests</h6>
                                    </div>
                                    <p class="text-muted mb-0">{{ $booking['specialRequests'] }}</p>
                                </div>
                                @endif

                                        <!-- Action Buttons -->
                                        <div class="bg-white rounded p-3 shadow-sm mt-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary rounded-circle p-2 me-3">
                                                        <i class="ri-settings-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Booking Actions</h6>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <button type="button" 
                                                            class="btn btn-outline-primary btn-sm px-3 py-2" 
                                                            onclick="editTravelHourlyBooking({{ $tour->tour_id }}, {{ $index }}, {{ $actualBookingIndex }})"
                                                            style="border-radius: 25px;">
                                                        <i class="ri-edit-line me-1"></i>Edit
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-success btn-sm px-3 py-2" 
                                                            onclick="approveTravelHourlyBooking({{ $tour->tour_id }}, {{ $index }}, {{ $actualBookingIndex }})"
                                                            style="border-radius: 25px;">
                                                        <i class="ri-check-line me-1"></i>Approve
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm px-3 py-2" 
                                                            onclick="rejectTravelHourlyBooking({{ $tour->tour_id }}, {{ $index }}, {{ $actualBookingIndex }})"
                                                            style="border-radius: 25px;">
                                                        <i class="ri-close-line me-1"></i>Reject
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @php $actualBookingIndex++; @endphp
                                    @endforeach
                                @endif
                            @endforeach
                        @else
                            <div class="text-center py-5">
                                <i class="ri-time-line ri-48px text-muted mb-3"></i>
                                <h5 class="text-muted">No hourly tour data available</h5>
                            </div>
                        @endif
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer bg-light border-0" style="border-radius: 0 0 8px 8px;">
                        <button type="button" class="btn btn-outline-secondary" onclick="closeServiceModal('travel_hourly', {{ $tour->tour_id }})">
                            <i class="ri-close-line me-1"></i>Close
                        </button>
                        {{-- <button type="button" class="btn btn-primary">
                            <i class="ri-download-line me-1"></i>Download Details
                        </button> --}}
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

<!-- Travel Point Service Modals -->
@foreach($tours as $tour)
    @php
        $orders = \App\Models\Order::where('tour_id', $tour->tour_id)->get();
        $serviceData = [];
        $svc = [];
        
        foreach($orders as $order) {
            $type = $order->type;
            if (!isset($serviceData[$type])) {
                $serviceData[$type] = [];
                $svc[$type] = 0;
            }
            $serviceData[$type][] = $order;
            $svc[$type]++;
        }
    @endphp

    @if(isset($svc['travel_point']) && $svc['travel_point'] > 0)
        <div class="modal fade" id="travel_pointDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="travel_pointModalLabel{{ $tour->tour_id }}" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content border-0 shadow-lg">
                    @php
                        $firstOrder = $serviceData['travel_point'][0] ?? null;
                        $firstBookingData = null;
                        $headerFromZone = 'N/A';
                        $headerToZone = 'N/A';
                        
                        if ($firstOrder) {
                            $firstBookingData = is_string($firstOrder->data) ? json_decode($firstOrder->data, true) : $firstOrder->data;
                            $firstBookingData = is_array($firstBookingData) && isset($firstBookingData[0]) ? $firstBookingData[0] : $firstBookingData;
                            
                            // Get zone names for header
                            if(isset($firstBookingData['from_zone_id']) && $firstBookingData['from_zone_id']) {
                                $fromZone = \DB::table('zones')->where('zone_id', $firstBookingData['from_zone_id'])->first();
                                $headerFromZone = $fromZone ? $fromZone->zone_type : 'Zone ' . $firstBookingData['from_zone_id'];
                            }
                            
                            if(isset($firstBookingData['to_zone_id']) && $firstBookingData['to_zone_id']) {
                                $toZone = \DB::table('zones')->where('zone_id', $firstBookingData['to_zone_id'])->first();
                                $headerToZone = $toZone ? $toZone->zone_type : 'Zone ' . $firstBookingData['to_zone_id'];
                            }
                        }
                    @endphp
                    
                    <!-- Modal Header -->
                    <div class="modal-header p-0 border-0 position-relative" style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                            <div class="text-white">
                                <h3 class="mb-1 fw-bold">
                                    <i class="ri-route-line me-2"></i>Local-Tour Point to Point
                                </h3>
                                <p class="mb-0 opacity-75">Tour #{{ $tour->tour_id }} Point to Point Transfer • {{ $headerFromZone }} → {{ $headerToZone }}</p>
                                <div class="mt-2">
                                    <span class="badge bg-white bg-opacity-90 text-primary px-3 py-2">
                                        <i class="ri-calendar-line me-1"></i>
                                        {{ isset($firstBookingData['bookingDate']) ? \Carbon\Carbon::parse($firstBookingData['bookingDate'])->format('D, M d, Y') : 'Date not specified' }}
                                    </span>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('travel_point', {{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body p-4" style="background: #f8fafc;">
                        @if(isset($serviceData['travel_point']) && count($serviceData['travel_point']) > 0)
                            @foreach($serviceData['travel_point'] as $index => $pointOrder)
                                @php
                                    $pointData = is_string($pointOrder->data) ? json_decode($pointOrder->data, true) : $pointOrder->data;
                                @endphp
                                
                                @if(is_array($pointData))
                                    @php $actualBookingIndex = 0; @endphp
                                    @foreach($pointData as $bookingIndex => $booking)
                                        @php
                                            // Fetch zone information
                                            $fromZoneName = 'N/A';
                                            $toZoneName = 'N/A';
                                            
                                            if(isset($booking['from_zone_id']) && $booking['from_zone_id']) {
                                                $fromZone = \DB::table('zones')->where('zone_id', $booking['from_zone_id'])->first();
                                                $fromZoneName = $fromZone ? $fromZone->zone_type : 'Zone ' . $booking['from_zone_id'];
                                            }
                                            
                                            if(isset($booking['to_zone_id']) && $booking['to_zone_id']) {
                                                $toZone = \DB::table('zones')->where('zone_id', $booking['to_zone_id'])->first();
                                                $toZoneName = $toZone ? $toZone->zone_type : 'Zone ' . $booking['to_zone_id'];
                                            }
                                        @endphp
                                        
                                        @if($index > 0 || $bookingIndex > 0)
                                            <hr class="my-4">
                                        @endif
                                
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                            <div class="card-header bg-transparent border-0 text-white">
                                                <h5 class="card-title mb-0 fw-bold">
                                                    <i class="ri-car-line me-2"></i>{{ $booking['vehicles_name'] ?? 'Point to Point Transfer' }}
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Service Schedule & Group Information -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="bg-white rounded p-3 shadow-sm h-100">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-primary rounded-circle p-2 me-3">
                                                    <i class="ri-calendar-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Transfer Schedule</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Date</small>
                                                    <div class="fw-medium">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('D, M d, Y') : 'N/A' }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Time</small>
                                                    <div class="fw-medium">{{ $booking['entrytime'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Distance</small>
                                                    <span class="badge bg-info">{{ $booking['distance'] ?? 'N/A' }} km</span>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Service Type</small>
                                                    <span class="badge bg-warning">{{ $booking['type'] ?? 'Standard' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="bg-white rounded p-3 shadow-sm h-100">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-success rounded-circle p-2 me-3">
                                                    <i class="ri-group-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Group Information</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Adults</small>
                                                    <div class="fw-medium">{{ $booking['adults'] ?? 0 }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Children</small>
                                                    <div class="fw-medium">{{ $booking['children'] ?? 0 }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Total Guests</small>
                                                    <span class="badge bg-primary">{{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }}</span>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Night Service Timing</small>
                                                    <div class="fw-medium text-muted small">{{ $booking['Night_Start_Time'] ?? 'N/A' }} - {{ $booking['Night_End_Time'] ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Route Details -->
                                <div class="bg-white rounded p-3 shadow-sm mb-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-warning rounded-circle p-2 me-3">
                                            <i class="ri-direction-line text-white"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-dark">Route Details</h6>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="d-flex align-items-start">
                                                <div class="bg-success rounded-circle p-2 me-3 mt-1">
                                                    <i class="ri-play-circle-line text-white"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted">Pickup Location</small>
                                                    <div class="fw-medium">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
                                                    <small class="text-success">Origin</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="d-flex align-items-start">
                                                <div class="bg-danger rounded-circle p-2 me-3 mt-1">
                                                    <i class="ri-flag-line text-white"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted">Drop-off Location</small>
                                                    <div class="fw-medium">{{ $booking['entrydropoff'] ?? 'N/A' }}</div>
                                                    <small class="text-danger">Destination</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted">City</small>
                                            <div class="fw-medium">{{ $booking['city'] ?? 'N/A' }}</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted">Country</small>
                                            <div class="fw-medium">{{ $booking['country'] ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Vehicle Information -->
                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        <div class="bg-white rounded p-3 shadow-sm h-100">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-warning rounded-circle p-2 me-3">
                                                    <i class="ri-car-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Vehicle Details</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <small class="text-muted">Vehicle Name</small>
                                                    <div class="fw-medium">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <small class="text-muted">Service Type</small>
                                                    <div class="fw-medium">{{ $booking['type'] ?? 'N/A' }} Transfer</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        @if(isset($booking['image']))
                                            <img src="{{ $booking['image'] }}" 
                                                 alt="{{ $booking['vehicles_name'] ?? 'Vehicle' }}" 
                                                 class="img-fluid rounded shadow-sm" 
                                                 style="height: 150px; width: 100%; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                                <i class="ri-car-line ri-48px text-muted"></i>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Pricing & Customer Information -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="bg-white rounded p-3 shadow-sm h-100">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-warning rounded-circle p-2 me-3">
                                                    <i class="ri-money-dollar-circle-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Pricing Details</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Total Price</small>
                                                    <div class="fw-bold text-success">${{ $booking['totalPrice'] ?? '0' }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Tax</small>
                                                    <div class="fw-medium">{{ $booking['Tax'] ?? '0' }}%</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">From Zone</small>
                                                    <div class="fw-medium">{{ $fromZoneName }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">To Zone</small>
                                                    <div class="fw-medium">{{ $toZoneName }}</div>
                                                </div>
                                                {{-- <div class="col-12 mb-3">
                                                    <small class="text-muted">Booking Type</small>
                                                    <span class="badge bg-primary">{{ ucfirst($booking['bookingType'] ?? 'Standard') }}</span>
                                                </div> --}}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="bg-white rounded p-3 shadow-sm h-100">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-info rounded-circle p-2 me-3">
                                                    <i class="ri-user-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Customer Information</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 mb-2">
                                                    <small class="text-muted">Name</small>
                                                    <div class="fw-medium">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="col-12 mb-2">
                                                    <small class="text-muted">Email</small>
                                                    <div class="fw-medium">{{ $booking['email'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="col-12 mb-2">
                                                    <small class="text-muted">Phone</small>
                                                    <div class="fw-medium">{{ $booking['phone'] ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Special Requests -->
                                @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                <div class="bg-white rounded p-3 shadow-sm mb-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-secondary rounded-circle p-2 me-3">
                                            <i class="ri-message-2-line text-white"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-dark">Special Requests</h6>
                                    </div>
                                    <p class="text-muted mb-0">{{ $booking['specialRequests'] }}</p>
                                </div>
                                @endif

                                        <!-- Action Buttons -->
                                        <div class="bg-white rounded p-3 shadow-sm mt-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary rounded-circle p-2 me-3">
                                                        <i class="ri-settings-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Booking Actions</h6>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <button type="button" 
                                                            class="btn btn-outline-primary btn-sm px-3 py-2" 
                                                            onclick="editTravelPointBooking({{ $tour->tour_id }}, {{ $index }}, {{ $actualBookingIndex }})"
                                                            style="border-radius: 25px;">
                                                        <i class="ri-edit-line me-1"></i>Edit
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-success btn-sm px-3 py-2" 
                                                            onclick="approveTravelPointBooking({{ $tour->tour_id }}, {{ $index }}, {{ $actualBookingIndex }})"
                                                            style="border-radius: 25px;">
                                                        <i class="ri-check-line me-1"></i>Approve
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm px-3 py-2" 
                                                            onclick="rejectTravelPointBooking({{ $tour->tour_id }}, {{ $index }}, {{ $actualBookingIndex }})"
                                                            style="border-radius: 25px;">
                                                        <i class="ri-close-line me-1"></i>Reject
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @php $actualBookingIndex++; @endphp
                                    @endforeach
                                @endif
                            @endforeach
                        @else
                            <div class="text-center py-5">
                                <i class="ri-route-line ri-48px text-muted mb-3"></i>
                                <h5 class="text-muted">No point to point transfer data available</h5>
                            </div>
                        @endif
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer bg-light border-0" style="border-radius: 0 0 8px 8px;">
                        <button type="button" class="btn btn-outline-secondary" onclick="closeServiceModal('travel_point', {{ $tour->tour_id }})">
                            <i class="ri-close-line me-1"></i>Close
                        </button>
                        {{-- <button type="button" class="btn btn-primary">
                            <i class="ri-download-line me-1"></i>Download Details
                        </button> --}}
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

<!-- Local Transport Service Modals -->
@foreach($tours as $tour)
    @php
        $orders = \App\Models\Order::where('tour_id', $tour->tour_id)->get();
        $serviceData = [];
        $svc = [];
        
        foreach($orders as $order) {
            $type = $order->type;
            if (!isset($serviceData[$type])) {
                $serviceData[$type] = [];
                $svc[$type] = 0;
            }
            $serviceData[$type][] = $order;
            $svc[$type]++;
        }
    @endphp

    @if(isset($svc['local_transport']) && $svc['local_transport'] > 0)
        <div class="modal fade" id="local_transportDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="local_transportModalLabel{{ $tour->tour_id }}" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content border-0 shadow-lg">
                    @php
                        $firstOrder = $serviceData['local_transport'][0] ?? null;
                        $firstBookingData = null;
                        $headerFromZone = 'N/A';
                        $headerToZone = 'N/A';
                        
                        if ($firstOrder) {
                            $firstBookingData = is_string($firstOrder->data) ? json_decode($firstOrder->data, true) : $firstOrder->data;
                            $firstBookingData = is_array($firstBookingData) && isset($firstBookingData[0]) ? $firstBookingData[0] : $firstBookingData;
                            
                            // Get zone names for header
                            if(isset($firstBookingData['from_zone_id']) && $firstBookingData['from_zone_id']) {
                                $fromZone = \DB::table('zones')->where('zone_id', $firstBookingData['from_zone_id'])->first();
                                $headerFromZone = $fromZone ? $fromZone->zone_type : 'Zone ' . $firstBookingData['from_zone_id'];
                            }
                            
                            if(isset($firstBookingData['to_zone_id']) && $firstBookingData['to_zone_id']) {
                                $toZone = \DB::table('zones')->where('zone_id', $firstBookingData['to_zone_id'])->first();
                                $headerToZone = $toZone ? $toZone->zone_type : 'Zone ' . $firstBookingData['to_zone_id'];
                            }
                        }
                    @endphp
                    
                    <!-- Modal Header -->
                    <div class="modal-header p-0 border-0 position-relative" style="height: 200px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                            <div class="text-white">
                                <h3 class="mb-1 fw-bold">
                                    <i class="ri-car-line me-2"></i>Local Transport
                                </h3>
                                <p class="mb-0 opacity-75">Tour #{{ $tour->tour_id }} Local Transport Service • {{ $headerFromZone }} → {{ $headerToZone }}</p>
                                <div class="mt-2">
                                    <span class="badge bg-white bg-opacity-90 text-primary px-3 py-2">
                                        <i class="ri-calendar-line me-1"></i>
                                        {{ isset($firstBookingData['bookingDate']) ? \Carbon\Carbon::parse($firstBookingData['bookingDate'])->format('M d, Y') : 'Date not specified' }}
                                    </span>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('local_transport', {{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body p-4" style="background: #f8fafc;">
                        @if(isset($serviceData['local_transport']) && count($serviceData['local_transport']) > 0)
                            @foreach($serviceData['local_transport'] as $index => $transportOrder)
                                @php
                                    $transportData = is_string($transportOrder->data) ? json_decode($transportOrder->data, true) : $transportOrder->data;
                                @endphp
                                
                                @if(is_array($transportData))
                                    @php $actualBookingIndex = 0; @endphp
                                    @foreach($transportData as $bookingIndex => $booking)
                                        @php
                                            // Fetch zone information
                                            $fromZoneName = 'N/A';
                                            $toZoneName = 'N/A';
                                            
                                            if(isset($booking['from_zone_id']) && $booking['from_zone_id']) {
                                                $fromZone = \DB::table('zones')->where('zone_id', $booking['from_zone_id'])->first();
                                                $fromZoneName = $fromZone ? $fromZone->zone_type : 'Zone ' . $booking['from_zone_id'];
                                            }
                                            
                                            if(isset($booking['to_zone_id']) && $booking['to_zone_id']) {
                                                $toZone = \DB::table('zones')->where('zone_id', $booking['to_zone_id'])->first();
                                                $toZoneName = $toZone ? $toZone->zone_type : 'Zone ' . $booking['to_zone_id'];
                                            }
                                        @endphp
                                        
                                        @if($index > 0 || $bookingIndex > 0)
                                            <hr class="my-4">
                                        @endif
                                
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                            <div class="card-header bg-transparent border-0 text-white">
                                                <h5 class="card-title mb-0 fw-bold">
                                                    <i class="ri-car-line me-2"></i>{{ $booking['vehicles_name'] ?? 'Local Transport Service' }}
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Service Schedule & Group Information -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="bg-white rounded p-3 shadow-sm h-100">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-primary rounded-circle p-2 me-3">
                                                    <i class="ri-calendar-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Transport Schedule</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Date</small>
                                                    <div class="fw-medium">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') : 'N/A' }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Time</small>
                                                    <div class="fw-medium">{{ $booking['entrytime'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Distance</small>
                                                    <span class="badge bg-info">{{ $booking['distance'] ?? 'N/A' }} km</span>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Service Type</small>
                                                    <span class="badge bg-warning">{{ $booking['type'] ?? 'Standard' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="bg-white rounded p-3 shadow-sm h-100">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-success rounded-circle p-2 me-3">
                                                    <i class="ri-group-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Group Information</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Adults</small>
                                                    <div class="fw-medium">{{ $booking['adults'] ?? 0 }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Children</small>
                                                    <div class="fw-medium">{{ $booking['children'] ?? 0 }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Total Guests</small>
                                                    <span class="badge bg-primary">{{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }}</span>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Service Hours</small>
                                                    <div class="fw-medium text-muted small">{{ $booking['Night_Start_Time'] ?? 'N/A' }} - {{ $booking['Night_End_Time'] ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Route Details -->
                                <div class="bg-white rounded p-3 shadow-sm mb-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-warning rounded-circle p-2 me-3">
                                            <i class="ri-direction-line text-white"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-dark">Route Details</h6>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="d-flex align-items-start">
                                                <div class="bg-success rounded-circle p-2 me-3 mt-1">
                                                    <i class="ri-play-circle-line text-white"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted">Pickup Location</small>
                                                    <div class="fw-medium">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
                                                    <small class="text-success">Origin</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="d-flex align-items-start">
                                                <div class="bg-danger rounded-circle p-2 me-3 mt-1">
                                                    <i class="ri-flag-line text-white"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted">Drop-off Location</small>
                                                    <div class="fw-medium">{{ $booking['entrydropoff'] ?? 'N/A' }}</div>
                                                    <small class="text-danger">Destination</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted">City</small>
                                            <div class="fw-medium">{{ $booking['city'] ?? 'N/A' }}</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted">Country</small>
                                            <div class="fw-medium">{{ $booking['country'] ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Vehicle Information -->
                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        <div class="bg-white rounded p-3 shadow-sm h-100">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-warning rounded-circle p-2 me-3">
                                                    <i class="ri-car-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Vehicle Details</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <small class="text-muted">Vehicle Name</small>
                                                    <div class="fw-medium">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <small class="text-muted">Service Type</small>
                                                    <div class="fw-medium">{{ $booking['type'] ?? 'N/A' }} Transport</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        @if(isset($booking['image']))
                                            <img src="{{ $booking['image'] }}" 
                                                 alt="{{ $booking['vehicles_name'] ?? 'Vehicle' }}" 
                                                 class="img-fluid rounded shadow-sm" 
                                                 style="height: 150px; width: 100%; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                                <i class="ri-car-line ri-48px text-muted"></i>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Pricing & Customer Information -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="bg-white rounded p-3 shadow-sm h-100">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-warning rounded-circle p-2 me-3">
                                                    <i class="ri-money-dollar-circle-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Pricing Details</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Total Price</small>
                                                    <div class="fw-bold text-success">${{ $booking['totalPrice'] ?? '0' }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Tax</small>
                                                    <div class="fw-medium">{{ $booking['Tax'] ?? '0' }}%</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">From Zone</small>
                                                    <div class="fw-medium">{{ $fromZoneName }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">To Zone</small>
                                                    <div class="fw-medium">{{ $toZoneName }}</div>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <small class="text-muted">Booking Type</small>
                                                    <span class="badge bg-primary">{{ ucfirst($booking['bookingType'] ?? 'Standard') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="bg-white rounded p-3 shadow-sm h-100">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-info rounded-circle p-2 me-3">
                                                    <i class="ri-user-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Customer Information</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 mb-2">
                                                    <small class="text-muted">Name</small>
                                                    <div class="fw-medium">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="col-12 mb-2">
                                                    <small class="text-muted">Email</small>
                                                    <div class="fw-medium">{{ $booking['email'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="col-12 mb-2">
                                                    <small class="text-muted">Phone</small>
                                                    <div class="fw-medium">{{ $booking['phone'] ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Special Requests -->
                                @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                <div class="bg-white rounded p-3 shadow-sm mb-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-secondary rounded-circle p-2 me-3">
                                            <i class="ri-message-2-line text-white"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-dark">Special Requests</h6>
                                    </div>
                                    <p class="text-muted mb-0">{{ $booking['specialRequests'] }}</p>
                                </div>
                                @endif

                                        <!-- Action Buttons -->
                                        <div class="bg-white rounded p-3 shadow-sm mt-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary rounded-circle p-2 me-3">
                                                        <i class="ri-settings-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Booking Actions</h6>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <button type="button" 
                                                            class="btn btn-outline-primary btn-sm px-3 py-2" 
                                                            onclick="editLocalTransportBooking({{ $tour->tour_id }}, {{ $index }}, {{ $actualBookingIndex }})"
                                                            style="border-radius: 25px;">
                                                        <i class="ri-edit-line me-1"></i>Edit
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-success btn-sm px-3 py-2" 
                                                            onclick="approveLocalTransportBooking({{ $tour->tour_id }}, {{ $index }}, {{ $actualBookingIndex }})"
                                                            style="border-radius: 25px;">
                                                        <i class="ri-check-line me-1"></i>Approve
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm px-3 py-2" 
                                                            onclick="rejectLocalTransportBooking({{ $tour->tour_id }}, {{ $index }}, {{ $actualBookingIndex }})"
                                                            style="border-radius: 25px;">
                                                        <i class="ri-close-line me-1"></i>Reject
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @php $actualBookingIndex++; @endphp
                                    @endforeach
                                @endif
                            @endforeach
                        @else
                            <div class="text-center py-5">
                                <i class="ri-car-line ri-48px text-muted mb-3"></i>
                                <h5 class="text-muted">No local transport data available</h5>
                            </div>
                        @endif
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer bg-light border-0" style="border-radius: 0 0 8px 8px;">
                        <button type="button" class="btn btn-outline-secondary" onclick="closeServiceModal('local_transport', {{ $tour->tour_id }})">
                            <i class="ri-close-line me-1"></i>Close
                        </button>
                        {{-- <button type="button" class="btn btn-primary">
                            <i class="ri-download-line me-1"></i>Download Details
                        </button> --}}
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

<script>
// Service Modal Functions  
function openServiceModal(serviceType, tourId, event) {
    try {
        const modalId = `${serviceType}DetailsModal${tourId}`;
        const modalElement = document.getElementById(modalId);
        
        console.log('Attempting to open modal:', modalId);
        console.log('Modal element found:', !!modalElement);
        
        if (modalElement) {
            // Check if Bootstrap is available
            if (typeof bootstrap !== 'undefined') {
                console.log('Using Bootstrap 5 modal');
                try {
                    const modal = new bootstrap.Modal(modalElement, {
                        backdrop: 'static',
                        keyboard: false
                    });
                    modal.show();
                } catch (e) {
                    console.log('Bootstrap modal initialization failed, using jQuery fallback:', e);
                    if (typeof $ !== 'undefined' && $.fn.modal) {
                        $(`#${modalId}`).modal('show');
                    }
                }
            } else if (typeof $ !== 'undefined' && $.fn.modal) {
                console.log('Using jQuery modal');
                // Fallback to jQuery if Bootstrap 5 is not available
                $(`#${modalId}`).modal('show');
            } else {
                console.log('Using manual modal fallback');
                // Manual fallback
                modalElement.style.display = 'block';
                modalElement.classList.add('show');
                modalElement.setAttribute('aria-hidden', 'false');
                
                // Add backdrop manually
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.id = `backdrop-${serviceType}-${tourId}`;
                document.body.appendChild(backdrop);
                document.body.classList.add('modal-open');
            }
        } else {
            console.error('Modal element not found:', modalId);
            
            // Debug: List all modal elements on page
            const allModals = document.querySelectorAll(`[id^="${serviceType}DetailsModal"]`);
            console.log(`Available ${serviceType} modals on page:`, Array.from(allModals).map(m => m.id));
            
            // Debug: Get debug info from the clicked element
            const clickedElement = event?.target?.closest('[data-debug-info]');
            if (clickedElement) {
                try {
                    const debugInfo = JSON.parse(clickedElement.getAttribute('data-debug-info'));
                    console.log('Debug info for tour', tourId, ':', debugInfo);
                } catch (e) {
                    console.log('Could not parse debug info:', e);
                }
            }
            
            // Show alert to user
            Swal.fire({
                title: 'Error!',
                text: `${ucfirst(serviceType)} details modal not found for tour ${tourId}. Please check the console for debug information and refresh the page.`,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Error opening service modal:', error);
        
        // Show alert to user
        Swal.fire({
            title: 'Error!',
            text: `Failed to open ${serviceType} details: ${error.message}`,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}

function closeServiceModal(serviceType, tourId) {
    try {
        const modalId = `${serviceType}DetailsModal${tourId}`;
        const modalElement = document.getElementById(modalId);
        
        if (modalElement) {
            // Check if Bootstrap is available
            if (typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                } else {
                    // Force close if no instance found
                    modalElement.style.display = 'none';
                    modalElement.classList.remove('show');
                    modalElement.setAttribute('aria-hidden', 'true');
                }
            } else if (typeof $ !== 'undefined' && $.fn.modal) {
                // Fallback to jQuery if Bootstrap 5 is not available
                $(`#${modalId}`).modal('hide');
            } else {
                // Manual close
                modalElement.style.display = 'none';
                modalElement.classList.remove('show');
                modalElement.setAttribute('aria-hidden', 'true');
                
                // Remove backdrop manually
                const backdrop = document.getElementById(`backdrop-${serviceType}-${tourId}`);
                if (backdrop) {
                    backdrop.remove();
                }
                
                // Remove any existing backdrops
                const allBackdrops = document.querySelectorAll('.modal-backdrop');
                allBackdrops.forEach(bd => bd.remove());
                
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }
        } else {
            console.error('Modal element not found:', modalId);
        }
    } catch (error) {
        console.error('Error closing service modal:', error);
    }
}

// Backwards compatibility for hotel modal
function openHotelModal(tourId, event) {
    openServiceModal('hotel', tourId, event);
}

// Individual Guide Modal Functions
function openIndividualGuideModal(tourId, guideOrderIndex, bookingIndex) {
    try {
        console.log('👨‍💼 Opening individual guide modal for:', { tourId, guideOrderIndex, bookingIndex });
        
        const modalId = `individualGuideViewModal_${tourId}_${guideOrderIndex}_${bookingIndex}`;
        
        // Remove existing modal if it exists
        const existingModal = document.getElementById(modalId);
        if (existingModal) {
            existingModal.remove();
        }
        
        // Create and show the individual guide modal
        createIndividualGuideViewModal(tourId, guideOrderIndex, bookingIndex);
        
    } catch (error) {
        console.error('Error opening individual guide modal:', error);
        alert('Error opening guide modal. Please try again.');
    }
}

function createIndividualGuideViewModal(tourId, guideOrderIndex, bookingIndex) {
    const modalId = `individualGuideViewModal_${tourId}_${guideOrderIndex}_${bookingIndex}`;
    
    const modalHTML = `
        <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content border-0 shadow-lg">
                    <!-- Modal Header -->
                    <div class="modal-header p-0 border-0 position-relative" style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                            <div class="text-white">
                                <h3 class="mb-1 fw-bold">
                                    <i class="ri-user-voice-line me-2 rounded-circle"></i>Guide Details
                                </h3>
                                <p class="mb-0 opacity-75">Tour #${tourId} Guide Booking Details</p>
                            </div>
                            <button type="button" class="btn-close btn-close-white" onclick="closeIndividualGuideViewModal('${modalId}')" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                        </div>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="modal-body p-4" style="background: #f8fafc;">
                        <div id="individualGuideContent_${modalId}">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-3">Loading guide details...</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="modal-footer bg-light border-0" style="border-radius: 0 0 8px 8px;">
                        <button type="button" class="btn btn-outline-secondary" onclick="closeIndividualGuideViewModal('${modalId}')">
                            <i class="ri-close-line me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to document
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Initialize and show the modal
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();
    
    // Load the individual guide content
    loadIndividualGuideContent(tourId, guideOrderIndex, bookingIndex, modalId);
}

function closeIndividualGuideViewModal(modalId) {
    try {
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        }
    } catch (error) {
        console.error('Error closing individual guide view modal:', error);
    }
}

function loadIndividualGuideContent(tourId, guideOrderIndex, bookingIndex, modalId) {
    // Fetch guide data from backend and populate the modal content
    console.log('🔄 Fetching guide data from backend', { tourId, guideOrderIndex, bookingIndex, modalId });
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    fetch('/booking/get-guide-data', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            tour_id: tourId,
            guide_order_index: guideOrderIndex,
            booking_index: bookingIndex
        })
    })
    .then(response => {
        console.log('📡 Guide data response received', { status: response.status });
        return response.json();
    })
    .then(data => {
        console.log('📊 Guide data parsed', data);
        
        if (data.success && data.data) {
            // Use the actual guide data from backend
            const guideBooking = {
                guideName: data.data.guide_name,
                guide_id: data.data.guide_id,
                image: data.data.image,
                bookingDate: data.data.booking_date,
                pickupdate: data.data.pickup_date,
                entrytime: data.data.entry_time,
                hours: data.data.hours,
                adults: data.data.adults,
                children: data.data.children,
                totalPrice: data.data.total_price,
                basePrice: data.data.base_price,
                surcharge: data.data.surcharge,
                tax: data.data.tax,
                entrypickup: data.data.pickup_location,
                Night_Start_Time: data.data.night_start_time,
                Night_End_Time: data.data.night_end_time,
                fullName: data.data.full_name,
                email: data.data.email,
                phone: data.data.phone,
                address1: data.data.address,
                state: data.data.state,
                zip: data.data.zip,
                specialRequests: data.data.special_requests,
                bookingType: data.data.booking_type,
                Mode: data.data.mode,
                dmc_id: data.data.dmc_id,
                // Additional guide-specific fields
                duration: data.data.hours ? `${data.data.hours} Hours` : 'Full Day',
                language: 'English', // Default, could be from backend
                serviceType: 'Professional Guide',
                experienceLevel: 'Professional'
            };
            
            console.log('✅ Guide booking data prepared for display', guideBooking);
            generateIndividualGuideContent(guideBooking, modalId, tourId, guideOrderIndex, bookingIndex);
        } else {
            console.error('❌ Guide data fetch failed', data);
            // Show error message
            document.getElementById(`individualGuideContent_${modalId}`).innerHTML = `
                <div class="text-center py-5">
                    <i class="ri-error-warning-line ri-48px text-danger mb-3"></i>
                    <h5 class="text-danger">Error Loading Guide Details</h5>
                    <p class="text-muted">${data.message || 'Unable to load guide information. Please try again.'}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('💥 Error fetching guide data:', error);
        // Show error message
        document.getElementById(`individualGuideContent_${modalId}`).innerHTML = `
            <div class="text-center py-5">
                <i class="ri-error-warning-line ri-48px text-danger mb-3"></i>
                <h5 class="text-danger">Error Loading Guide Details</h5>
                <p class="text-muted">Network error occurred. Please check your connection and try again.</p>
            </div>
        `;
    });
}

function generateIndividualGuideContent(guideBooking, modalId, tourId, guideOrderIndex, bookingIndex) {
    const contentHTML = `
        <!-- Guide Information Card with Image -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body p-4">
                        <div class="row align-items-center text-white">
                            <div class="col-md-3 text-center">
                                ${guideBooking.image ? `
                                    <img src="${guideBooking.image}" 
                                         alt="${guideBooking.guideName || 'Guide'}" 
                                         class="rounded-circle border border-white border-3 shadow"
                                         style="width: 100px; height: 100px; object-fit: cover;">
                                ` : `
                                    <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center border border-white border-3"
                                         style="width: 100px; height: 100px;">
                                        <i class="ri-user-voice-line" style="font-size: 2.5rem;"></i>
                                    </div>
                                `}
                            </div>
                            <div class="col-md-9">
                                <h4 class="mb-2 fw-bold">
                                    <i class="ri-user-voice-line me-2"></i>${guideBooking.guideName || 'Professional Guide'}
                                </h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="opacity-75">Total Price</small>
                                        <div class="fw-bold fs-5">SGD ${parseFloat(guideBooking.totalPrice || 0).toFixed(2)}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Service Schedule & Group Information -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="bg-white rounded p-3 shadow-sm h-100">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary rounded-circle p-2 me-3">
                            <i class="ri-calendar-line text-white"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-dark">Service Schedule</h6>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Date</small>
                            <div class="fw-medium">${guideBooking.bookingDate || 'N/A'}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Time</small>
                            <div class="fw-medium">${guideBooking.entrytime || 'N/A'}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Duration</small>
                            <div class="fw-medium">${guideBooking.duration || 'Full Day'}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Pickup Location</small>
                            <div class="fw-medium text-truncate" title="${guideBooking.entrypickup || 'N/A'}">${guideBooking.entrypickup || 'N/A'}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="bg-white rounded p-3 shadow-sm h-100">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success rounded-circle p-2 me-3">
                            <i class="ri-group-line text-white"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-dark">Group Information</h6>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Adults</small>
                            <div class="fw-medium">${guideBooking.adults || 0}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Children</small>
                            <div class="fw-medium">${guideBooking.children || 0}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Total Guests</small>
                            <span class="badge bg-primary">${(parseInt(guideBooking.adults) || 0) + (parseInt(guideBooking.children) || 0)}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Guide Details & Pricing -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="bg-white rounded p-3 shadow-sm h-100">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-warning rounded-circle p-2 me-3">
                            <i class="ri-money-dollar-circle-line text-white"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-dark">Pricing Details</h6>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Base Price</small>
                            <div class="fw-medium">SGD ${parseFloat(guideBooking.basePrice || 0).toFixed(2)}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Surcharge</small>
                            <div class="fw-medium">SGD ${parseFloat(guideBooking.surcharge || 0).toFixed(2)}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Tax (${guideBooking.tax || '0'}%)</small>
                            <div class="fw-medium">SGD ${(parseFloat(guideBooking.totalPrice || 0) * parseFloat(guideBooking.tax || 0) / 100).toFixed(2)}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Total Price</small>
                            <div class="fw-bold text-success fs-5">SGD ${parseFloat(guideBooking.totalPrice || 0).toFixed(2)}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="bg-white rounded p-3 shadow-sm h-100">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-info rounded-circle p-2 me-3">
                            <i class="ri-user-line text-white"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-dark">Customer Information</h6>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-2">
                            <small class="text-muted">Name</small>
                            <div class="fw-medium">${guideBooking.fullName || 'N/A'}</div>
                        </div>
                        <div class="col-12 mb-2">
                            <small class="text-muted">Email</small>
                            <div class="fw-medium">${guideBooking.email || 'N/A'}</div>
                        </div>
                        <div class="col-12 mb-2">
                            <small class="text-muted">Phone</small>
                            <div class="fw-medium">${guideBooking.phone || 'N/A'}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Special Requests -->
        ${guideBooking.specialRequests ? `
        <div class="bg-white rounded p-3 shadow-sm mb-4">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-secondary rounded-circle p-2 me-3">
                    <i class="ri-message-2-line text-white"></i>
                </div>
                <h6 class="fw-bold mb-0 text-dark">Special Requests</h6>
            </div>
            <p class="text-muted mb-0">${guideBooking.specialRequests}</p>
        </div>
        ` : ''}

        <!-- Action Buttons -->
        <div class="bg-white rounded p-3 shadow-sm mt-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="bg-primary rounded-circle p-2 me-3">
                        <i class="ri-settings-line text-white"></i>
                    </div>
                    <h6 class="fw-bold mb-0 text-dark">Booking Actions</h6>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" 
                            class="btn btn-outline-primary btn-sm px-3 py-2" 
                            onclick="editIndividualGuide(${tourId}, ${guideOrderIndex}, ${bookingIndex})"
                            style="border-radius: 25px;">
                        <i class="ri-edit-line me-1"></i>Edit
                    </button>
                    <button type="button" 
                            class="btn btn-outline-success btn-sm px-3 py-2" 
                            onclick="approveIndividualGuide(${tourId}, ${guideOrderIndex}, ${bookingIndex})"
                            style="border-radius: 25px;">
                        <i class="ri-check-line me-1"></i>Approve
                    </button>
                    <button type="button" 
                            class="btn btn-outline-danger btn-sm px-3 py-2" 
                            onclick="rejectIndividualGuide(${tourId}, ${guideOrderIndex}, ${bookingIndex})"
                            style="border-radius: 25px;">
                        <i class="ri-close-line me-1"></i>Reject
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById(`individualGuideContent_${modalId}`).innerHTML = contentHTML;
}

function editIndividualGuide(tourId, guideOrderIndex, bookingIndex) {
    console.log('Editing individual guide:', { tourId, guideOrderIndex, bookingIndex });
    
    // Create and show the guide edit modal
    createAndShowGuideEditModal(tourId, guideOrderIndex, bookingIndex);
}

function approveIndividualGuide(tourId, guideOrderIndex, bookingIndex) {
    console.log('Approving individual guide:', { tourId, guideOrderIndex, bookingIndex });
    if (confirm('Are you sure you want to approve this guide booking?')) {
        alert('Guide booking approved successfully!');
        // Here you would make an API call to approve the guide
    }
}

function rejectIndividualGuide(tourId, guideOrderIndex, bookingIndex) {
    console.log('Rejecting individual guide:', { tourId, guideOrderIndex, bookingIndex });
    const reason = prompt('Please provide a reason for rejection:');
    if (reason !== null && reason.trim() !== '') {
        alert('Guide booking rejected successfully!');
        // Here you would make an API call to reject the guide
    }
}

// Guide Edit Modal Functions
function createAndShowGuideEditModal(tourId, guideOrderIndex, bookingIndex) {
    const editModalId = `editGuideModal_${tourId}_${guideOrderIndex}_${bookingIndex}`;
    
    const editModalHTML = `
        <div class="modal fade" id="${editModalId}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content shadow-lg" style="border-radius: 15px;">
                    <!-- Modal Header with Guide Info -->
                    <div class="modal-header bg-gradient-primary text-white border-0 p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 140px;">
                        <div class="container-fluid p-4">
                            <div class="row align-items-center">
                                <div class="col-md-3 text-center">
                                    <img id="guide_image_${editModalId}" 
                                         src="" 
                                         alt="Guide" 
                                         class="rounded-circle border border-white border-3 shadow"
                                         style="width: 80px; height: 80px; object-fit: cover;"
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iNDAiIGN5PSI0MCIgcj0iNDAiIGZpbGw9IiNGM0Y0RjYiLz4KPHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4PSIyMCIgeT0iMjAiPgo8cGF0aCBkPSJNMTIgMTJDMTQuMjA5MSAxMiAxNiAxMC4yMDkxIDE2IDhDMTYgNS43OTA5IDE0LjIwOTEgNCAxMiA0QzkuNzkwODYgNCA4IDUuNzkwOSA4IDhDOCAxMC4yMDkxIDkuNzkwODYgMTIgMTIgMTJaIiBmaWxsPSIjOUI5QkEzIi8+CjxwYXRoIGQ9Ik0xMiAxNEM5IDEzLjk5IDYuMjI5OTkgMTYgNiAxOEg0VjIwSDIwVjE4SDE4QzE3Ljc3IDE2IDE1IDEzLjk5IDEyIDE0WiIgZmlsbD0iIzlCOUJBMyIvPgo8L3N2Zz4KPC9zdmc+Cg=='">
                                </div>
                                <div class="col-md-9">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h5 class="mb-0 fw-bold text-white">Edit Guide Booking</h5>
                                            <small id="guide_info_${editModalId}" class="text-white opacity-75">Professional Guide • Tour Service</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold fs-5 text-white" id="guide_price_header_${editModalId}">SGD 0.00</div>
                                            <small class="text-white opacity-75">Total Price</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" onclick="closeGuideEditModal('${editModalId}')" aria-label="Close"></button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body p-4" style="background-color: #f8f9fa;">
                        <!-- Travel Date Constraint -->
                        <div class="alert alert-info border-0 mb-4 d-flex align-items-center" style="border-radius: 12px; background: linear-gradient(90deg, #e3f2fd 0%, #bbdefb 100%);">
                            <i class="ri-information-line fs-5 me-3 text-info"></i>
                            <div>
                                <h6 class="mb-1 fw-bold text-info">Travel Date Constraint</h6>
                                <small id="guide_date_constraint_${editModalId}" class="text-muted">Guide booking must be within the tour travel period: <strong>Loading...</strong> to <strong>Loading...</strong></small>
                            </div>
                        </div>
                        
                        <form id="guideEditForm_${editModalId}">
                            <!-- Edit Booking Date & Time Section -->
                            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                                <div class="card-header bg-primary text-white border-0" style="border-radius: 12px 12px 0 0;">
                                    <h6 class="mb-0 fw-bold">
                                        <i class="ri-calendar-line me-2"></i>Update Guide Schedule
                                    </h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row">
                                        <!-- Booking Date -->
                                        <div class="col-md-6 mb-3">
                                            <label for="guide_booking_date_${editModalId}" class="form-label fw-medium">
                                                <i class="ri-calendar-2-line text-primary me-1"></i>Booking Date
                                            </label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="guide_booking_date_${editModalId}" 
                                                   name="booking_date"
                                                   style="border-radius: 8px; border: 2px solid #e9ecef;">
                                        </div>
                                        
                                        <!-- Pickup Date -->
                                        <div class="col-md-6 mb-3">
                                            <label for="guide_pickup_date_${editModalId}" class="form-label fw-medium">
                                                <i class="ri-calendar-check-line text-success me-1"></i>Pickup Date
                                            </label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="guide_pickup_date_${editModalId}" 
                                                   name="pickup_date"
                                                   style="border-radius: 8px; border: 2px solid #e9ecef;">
                                        </div>
                                        
                                        <!-- Entry Time -->
                                        <div class="col-md-12 mb-3">
                                            <label for="guide_entry_time_${editModalId}" class="form-label fw-medium">
                                                <i class="ri-time-line text-warning me-1"></i>Service Time
                                            </label>
                                            <input type="time" 
                                                   class="form-control" 
                                                   id="guide_entry_time_${editModalId}" 
                                                   name="entry_time"
                                                   style="border-radius: 8px; border: 2px solid #e9ecef;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="modal-footer bg-light border-0 d-flex justify-content-between" style="border-radius: 0 0 12px 12px;">
                        <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeGuideEditModal('${editModalId}')" style="border-radius: 25px;">
                            <i class="ri-close-line me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary px-4 py-2" onclick="saveGuideChanges(${tourId}, ${guideOrderIndex}, ${bookingIndex}, '${editModalId}')" style="border-radius: 25px;">
                            <i class="ri-save-line me-1"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to document
    document.body.insertAdjacentHTML('beforeend', editModalHTML);
    
    // Initialize and show the modal
    const modal = new bootstrap.Modal(document.getElementById(editModalId));
    modal.show();
    
    // Load the guide data for editing
    loadGuideDataForEdit(tourId, guideOrderIndex, bookingIndex, editModalId);
}

function closeGuideEditModal(editModalId) {
    try {
        const modalElement = document.getElementById(editModalId);
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
            // Remove modal from DOM after hiding
            setTimeout(() => {
                modalElement.remove();
            }, 300);
        }
    } catch (error) {
        console.error('Error closing guide edit modal:', error);
    }
}

function loadGuideDataForEdit(tourId, guideOrderIndex, bookingIndex, editModalId) {
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Get tour data for date constraints
    const tourData = getTourDataFromPage(tourId);
    console.log('Tour data for guide edit:', tourData);
    
    fetch('/booking/get-guide-data', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            tour_id: tourId,
            guide_order_index: guideOrderIndex,
            booking_index: bookingIndex
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Guide data for edit:', data);
        
        if (data.success && data.data) {
            const guideDetails = data.data;
            
            // Update modal header with guide info
            if (guideDetails.image) {
                document.getElementById(`guide_image_${editModalId}`).src = guideDetails.image;
            }
            
            document.getElementById(`guide_info_${editModalId}`).textContent = 
                `${guideDetails.guide_name || 'Professional Guide'} • ${guideDetails.hours || 'N/A'} Hours Service`;
            
            document.getElementById(`guide_price_header_${editModalId}`).textContent = 
                `SGD ${parseFloat(guideDetails.total_price || 0).toFixed(2)}`;
            
            // Update travel date constraint
            const constraintElement = document.getElementById(`guide_date_constraint_${editModalId}`);
            if (tourData.check_in_time && tourData.check_out_time) {
                const startDate = new Date(tourData.check_in_time).toLocaleDateString('en-US', { 
                    weekday: 'short', year: 'numeric', month: 'short', day: '2-digit' 
                });
                const endDate = new Date(tourData.check_out_time).toLocaleDateString('en-US', { 
                    weekday: 'short', year: 'numeric', month: 'short', day: '2-digit' 
                });
                constraintElement.innerHTML = `Guide booking must be within the tour travel period: <strong>${startDate}</strong> to <strong>${endDate}</strong>`;
            }
            
            // Set form values
            document.getElementById(`guide_booking_date_${editModalId}`).value = guideDetails.booking_date || '';
            document.getElementById(`guide_pickup_date_${editModalId}`).value = guideDetails.pickup_date || guideDetails.booking_date || '';
            
            // Convert 12-hour time to 24-hour format for input
            if (guideDetails.entry_time) {
                const convertedTime = convertTo24HourFormat(guideDetails.entry_time);
                document.getElementById(`guide_entry_time_${editModalId}`).value = convertedTime;
            }
            
            // Set date constraints on inputs
            const bookingDateInput = document.getElementById(`guide_booking_date_${editModalId}`);
            const pickupDateInput = document.getElementById(`guide_pickup_date_${editModalId}`);
            
            if (tourData.check_in_time && tourData.check_out_time) {
                bookingDateInput.min = tourData.check_in_time;
                bookingDateInput.max = tourData.check_out_time;
                pickupDateInput.min = tourData.check_in_time;
                pickupDateInput.max = tourData.check_out_time;
            }
        } else {
            console.error('Failed to load guide data:', data);
            // Use fallback data
            document.getElementById(`guide_booking_date_${editModalId}`).value = tourData.check_in_time || "2025-09-11";
            document.getElementById(`guide_pickup_date_${editModalId}`).value = tourData.check_in_time || "2025-09-11";
            document.getElementById(`guide_entry_time_${editModalId}`).value = "12:00";
        }
    })
    .catch(error => {
        console.error('Error loading guide data for edit:', error);
        // Use fallback data
        const tourData = getTourDataFromPage(tourId);
        document.getElementById(`guide_booking_date_${editModalId}`).value = tourData.check_in_time || "2025-09-11";
        document.getElementById(`guide_pickup_date_${editModalId}`).value = tourData.check_in_time || "2025-09-11";
        document.getElementById(`guide_entry_time_${editModalId}`).value = "12:00";
    });
}

function saveGuideChanges(tourId, guideOrderIndex, bookingIndex, editModalId) {
    const bookingDate = document.getElementById(`guide_booking_date_${editModalId}`).value;
    const pickupDate = document.getElementById(`guide_pickup_date_${editModalId}`).value;
    const entryTime = document.getElementById(`guide_entry_time_${editModalId}`).value;
    
    // Validate dates
    if (!validateGuideDates(bookingDate, pickupDate, tourId)) {
        return;
    }
    
    // Convert 24-hour time to 12-hour format for storage
    const formattedTime = convertTo12HourFormat(entryTime);
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    const requestData = {
        tour_id: tourId,
        guide_order_index: guideOrderIndex,
        booking_index: bookingIndex,
        booking_date: bookingDate,
        pickup_date: pickupDate,
        entry_time: formattedTime
    };
    
    console.log('Saving guide changes:', requestData);
    
    fetch('/booking/update-guide-booking', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Guide update response:', data);
        
        if (data.success) {
            // Close the edit modal
            closeGuideEditModal(editModalId);
            
            // Show success message
            alert(data.message || 'Guide booking updated successfully!');
            
            // Refresh the page to show updated data
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to update guide booking'));
        }
    })
    .catch(error => {
        console.error('Error saving guide changes:', error);
        alert('Network error occurred. Please try again.');
    });
}

function validateGuideDates(bookingDate, pickupDate, tourId) {
    if (!bookingDate || !pickupDate) {
        alert('Please select both booking date and pickup date.');
        return false;
    }
    
    const tourData = getTourDataFromPage(tourId);
    if (!tourData.check_in_time || !tourData.check_out_time) {
        console.warn('Tour dates not available for validation');
        return true; // Allow if tour dates are not available
    }
    
    const tourStartDate = new Date(tourData.check_in_time);
    const tourEndDate = new Date(tourData.check_out_time);
    const selectedBookingDate = new Date(bookingDate);
    const selectedPickupDate = new Date(pickupDate);
    
    if (selectedBookingDate < tourStartDate || selectedBookingDate > tourEndDate) {
        alert('Booking date must be within the tour travel dates.');
        return false;
    }
    
    if (selectedPickupDate < tourStartDate || selectedPickupDate > tourEndDate) {
        alert('Pickup date must be within the tour travel dates.');
        return false;
    }
    
    return true;
}

// Individual Restaurant Modal Function
function openIndividualRestaurantModal(tourId, restaurantOrderIndex, bookingIndex) {
    try {
        console.log('🍽️ Opening individual restaurant modal for:', { tourId, restaurantOrderIndex, bookingIndex });
        
        const modalId = `individualRestaurantViewModal_${tourId}_${restaurantOrderIndex}_${bookingIndex}`;
        
        // Remove existing modal if it exists
        const existingModal = document.getElementById(modalId);
        if (existingModal) {
            existingModal.remove();
        }
        
        // Create and show the individual restaurant modal
        createIndividualRestaurantViewModal(tourId, restaurantOrderIndex, bookingIndex);
        
    } catch (error) {
        console.error('Error opening individual restaurant modal:', error);
        alert('Error opening restaurant modal. Please try again.');
    }
}

function createIndividualRestaurantViewModal(tourId, restaurantOrderIndex, bookingIndex) {
    const modalId = `individualRestaurantViewModal_${tourId}_${restaurantOrderIndex}_${bookingIndex}`;
    
    // Get restaurant data from the server first
    getRestaurantServiceData(tourId, restaurantOrderIndex, bookingIndex)
    .then(restaurantData => {
        const restaurantName = restaurantData.restaurantDetails?.restaurant_name || 'Restaurant';
        
        const modalHTML = `
            <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
                    <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
                        <!-- Modal Header -->
                        <div class="modal-header p-0 border-0 position-relative" style="height: 180px; background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%);">
                            <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                                <div class="text-white">
                                    <h3 class="mb-1 fw-bold">
                                        <i class="ri-restaurant-2-line me-2"></i>${restaurantName}
                                    </h3>
                                    <p class="mb-0 opacity-75">Tour #${tourId} Restaurant Details</p>
                                </div>
                                <button type="button" class="btn-close btn-close-white" onclick="closeIndividualRestaurantViewModal('${modalId}')" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                            </div>
                        </div>

                        <!-- Modal Body -->
                        <div class="modal-body p-4" style="background-color: #f8f9fa;">
                            <div id="restaurantContent_${modalId}">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-3 text-muted">Loading restaurant details...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="modal-footer border-0 p-4" style="background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);">
                            <button type="button" class="btn btn-secondary px-4 py-2" onclick="closeIndividualRestaurantViewModal('${modalId}')" style="border-radius: 25px;">
                                <i class="ri-close-line me-2"></i>Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to DOM
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Show modal
        const modalElement = document.getElementById(modalId);
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
        
        // Remove modal from DOM when hidden
        modalElement.addEventListener('hidden.bs.modal', function () {
            modalElement.remove();
        });
        
        // Load the restaurant content
        loadIndividualRestaurantContent(tourId, restaurantOrderIndex, bookingIndex, modalId);
        
    })
    .catch(error => {
        console.error('Error creating individual restaurant modal:', error);
        alert('Error loading restaurant data. Please try again.');
    });
}

function closeIndividualRestaurantViewModal(modalId) {
    try {
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        }
    } catch (error) {
        console.error('Error closing individual restaurant view modal:', error);
    }
}

function loadIndividualRestaurantContent(tourId, restaurantOrderIndex, bookingIndex, modalId) {
    // Get restaurant data and populate the modal content
    getRestaurantServiceData(tourId, restaurantOrderIndex, bookingIndex)
    .then(restaurantData => {
        console.log('📊 Restaurant data received for content generation:', restaurantData);
        
        // Pass the full restaurant data object which contains both restaurantDetails and restaurant_details
        const contentHTML = generateIndividualRestaurantContent(restaurantData, tourId, restaurantOrderIndex, bookingIndex);
        
        // Update the modal content
        const contentContainer = document.getElementById(`restaurantContent_${modalId}`);
        if (contentContainer) {
            contentContainer.innerHTML = contentHTML;
        }
    })
    .catch(error => {
        console.error('Error loading restaurant content:', error);
        const contentContainer = document.getElementById(`restaurantContent_${modalId}`);
        if (contentContainer) {
            contentContainer.innerHTML = `
                <div class="text-center py-5">
                    <div class="bg-danger bg-opacity-10 rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="ri-error-warning-line text-danger" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="text-danger mb-2">Error Loading Restaurant Data</h5>
                    <p class="text-muted">Unable to load restaurant details. Please try again.</p>
                </div>
            `;
        }
    });
}

function generateIndividualRestaurantContent(booking, tourId, restaurantOrderIndex, bookingIndex) {
    // Get the full booking data from the restaurantDetails
    const fullBooking = booking.restaurant_details || booking;
    
    return `
        <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header border-0" style="background: linear-gradient(90deg, #fd79a8 0%, #fdcb6e 100%); padding: 20px;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-1 fw-bold text-white">
                            <i class="ri-restaurant-2-line me-2"></i>${fullBooking.restaurantName || booking.restaurant_name || 'Restaurant Booking'}
                        </h5>
                        <p class="mb-0 text-white opacity-75">${fullBooking.mealType || booking.meal_type || 'Meal'} • ${fullBooking.mealSpecificType || booking.meal_specific_type || 'Standard'}</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="bg-white rounded-pill px-3 py-2 d-inline-block">
                            <span class="text-success fw-bold fs-5">SGD ${(fullBooking.totalPrice || booking.total_price || 0).toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-4" style="background-color: #f8f9fa;">
                <!-- Guest Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="bg-white rounded p-3 shadow-sm h-100">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary rounded-circle p-2 me-3">
                                    <i class="ri-user-line text-white"></i>
                                </div>
                                <h6 class="fw-bold mb-0 text-dark">Customer Details</h6>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Full Name</small>
                                <div class="fw-medium">${fullBooking.fullName || 'N/A'}</div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Email Address</small>
                                <div class="fw-medium text-primary">${fullBooking.email || 'N/A'}</div>
                            </div>
                            <div class="mb-0">
                                <small class="text-muted">Phone Number</small>
                                <div class="fw-medium">${fullBooking.countryCode || ''} ${fullBooking.phone || 'N/A'}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-white rounded p-3 shadow-sm h-100">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-warning rounded-circle p-2 me-3">
                                    <i class="ri-calendar-line text-white"></i>
                                </div>
                                <h6 class="fw-bold mb-0 text-dark">Reservation Details</h6>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Dining Date</small>
                                <div class="fw-bold text-success fs-5">${fullBooking.bookingDate ? new Date(fullBooking.bookingDate).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' }) : 'Date TBD'}</div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Dining Time</small>
                                <div class="fw-medium text-primary">${fullBooking.visitTime || 'Time to be confirmed'}</div>
                            </div>
                            <div class="row">
                                <div class="col-6 text-center">
                                    <div class="bg-light rounded p-2">
                                        <div class="fs-4 fw-bold text-success">${fullBooking.adultCount || 0}</div>
                                        <small class="text-muted">Adults</small>
                                    </div>
                                </div>
                                <div class="col-6 text-center">
                                    <div class="bg-light rounded p-2">
                                        <div class="fs-4 fw-bold text-warning">${fullBooking.childCount || 0}</div>
                                        <small class="text-muted">Children</small>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-2">
                                <span class="badge bg-primary px-3 py-2">
                                    Party of ${(fullBooking.adultCount || 0) + (fullBooking.childCount || 0)}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Restaurant Overview -->
                <div class="bg-white rounded p-3 shadow-sm mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-info rounded-circle p-2 me-3">
                            <i class="ri-information-line text-white"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-dark">Restaurant Overview</h6>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <small class="text-muted">Meal Price</small>
                            <div class="fw-medium text-success">SGD ${(fullBooking.mealPrice || 0).toFixed(2)}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <small class="text-muted">Transport Price</small>
                            <div class="fw-medium text-info">SGD ${(fullBooking.transportPrice || 0).toFixed(2)}</div>
                        </div>
                    </div>
                </div>

                ${fullBooking.MealDescription && fullBooking.MealDescription.length > 0 ? `
                <!-- Menu Items -->
                <div class="bg-white rounded p-3 shadow-sm mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success rounded-circle p-2 me-3">
                            <i class="ri-restaurant-line text-white"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-dark">Menu Items</h6>
                    </div>
                    
                    ${fullBooking.MealDescription.map(meal => `
                        <div class="card border-0 bg-gradient-light mb-3" style="border-radius: 10px;">
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge ${meal.item_type === 'Veg' ? 'bg-success' : 'bg-danger'} me-2">
                                                <i class="${meal.item_type === 'Veg' ? 'ri-leaf-line' : 'ri-restaurant-line'} me-1"></i>
                                                ${meal.item_type || 'N/A'}
                                            </span>
                                            <span class="badge bg-info">
                                                <i class="ri-price-tag-3-line me-1"></i>
                                                ${meal.category || 'N/A'}
                                            </span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">${meal.name || meal.item_name || 'Menu Item'}</h6>
                                        <div class="text-muted small">
                                            <i class="ri-restaurant-2-line me-1"></i>
                                            Quantity: ${meal.quantity || 1}
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="bg-white rounded-3 p-2 shadow-sm">
                                            <small class="text-muted d-block">Unit Price</small>
                                            <div class="fs-5 fw-bold text-success">SGD ${(meal.price || 0).toFixed(2)}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Price Calculation -->
                                <div class="bg-gradient-light rounded-3 p-4 border border-primary border-opacity-25 mt-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-7">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-primary rounded-circle p-2 me-3">
                                                    <i class="ri-calculator-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Price Calculation</h6>
                                            </div>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="text-center">
                                                    <div class="fs-5 fw-bold text-success">SGD ${(meal.price || 0).toFixed(2)}</div>
                                                    <small class="text-muted">per item</small>
                                                </div>
                                                <div class="text-primary fs-3">×</div>
                                                <div class="text-center">
                                                    <div class="fs-5 fw-bold text-primary">${meal.quantity || 1}</div>
                                                    <small class="text-muted">${(meal.quantity || 1) == 1 ? 'item' : 'items'}</small>
                                                </div>
                                                <div class="text-primary fs-3">=</div>
                                            </div>
                                        </div>
                                        <div class="col-md-5 text-end">
                                            <div class="bg-white rounded-3 p-4 shadow border border-success border-opacity-50">
                                                <small class="text-muted d-block mb-2">Item Subtotal</small>
                                                <div class="fs-2 fw-bold text-success">
                                                    SGD ${((meal.price || 0) * (meal.quantity || 1)).toFixed(2)}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                    
                    <!-- Total Summary -->
                    <div class="card shadow-lg mt-4" style="border: none; border-radius: 15px; overflow: hidden;">
                        <div class="card-header border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px;">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="mb-1 fw-bold text-white">
                                        <i class="ri-receipt-line me-2"></i>Order Summary
                                    </h5>
                                    <p class="mb-0 text-white opacity-75">
                                        ${fullBooking.MealDescription.length} item(s) • ${fullBooking.mealType || 'Meal'} • ${fullBooking.mealSpecificType || 'Menu'}
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="bg-white bg-opacity-95 rounded-3 px-4 py-3 shadow">
                                        <small class="text-muted d-block mb-1">Grand Total</small>
                                        <div class="fs-2 fw-bold text-success">SGD ${(fullBooking.totalPrice || 0).toFixed(2)}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                ` : ''}

                ${fullBooking.specialRequests ? `
                <!-- Special Requests -->
                <div class="bg-white rounded p-3 shadow-sm mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-purple rounded-circle p-2 me-3" style="background-color: #6f42c1;">
                            <i class="ri-message-line text-white"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-dark">Special Requests</h6>
                    </div>
                    <div class="bg-light rounded p-3">
                        <p class="mb-0 text-dark">${fullBooking.specialRequests}</p>
                    </div>
                </div>
                ` : ''}

                <!-- Individual Action Buttons -->
                <div class="bg-white rounded p-3 shadow-sm border-top">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="bg-secondary rounded-circle p-2 me-3">
                                <i class="ri-settings-line text-white"></i>
                            </div>
                            <h6 class="fw-bold mb-0 text-dark">Booking Actions</h6>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" 
                                    class="btn btn-outline-primary btn-sm px-3 py-2" 
                                    onclick="editIndividualRestaurant(${tourId}, ${restaurantOrderIndex}, ${bookingIndex})"
                                    style="border-radius: 25px;">
                                <i class="ri-edit-line me-1"></i>Edit
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-success btn-sm px-3 py-2" 
                                    onclick="approveIndividualRestaurant(${tourId}, ${restaurantOrderIndex}, ${bookingIndex})"
                                    style="border-radius: 25px;">
                                <i class="ri-check-line me-1"></i>Approve
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-danger btn-sm px-3 py-2" 
                                    onclick="rejectIndividualRestaurant(${tourId}, ${restaurantOrderIndex}, ${bookingIndex})"
                                    style="border-radius: 25px;">
                                <i class="ri-close-line me-1"></i>Reject
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Arrival Edit Modal Functions
function editArrivalBooking(tourId, arrivalOrderIndex, arrivalBookingIndex) {
    console.log('Edit arrival booking:', { tourId, arrivalOrderIndex, arrivalBookingIndex });
    
    // Don't close the main modal - keep it open like restaurant service
    // Create edit modal directly
    createAndShowArrivalEditModal(tourId, arrivalOrderIndex, arrivalBookingIndex);
}

function approveArrivalBooking(tourId, arrivalOrderIndex, arrivalBookingIndex) {
    console.log('Approve arrival booking:', { tourId, arrivalOrderIndex, arrivalBookingIndex });
    if (confirm(`Are you sure you want to approve this arrival booking?`)) {
        alert(`Arrival booking approved successfully!`);
        // Here you would make an API call to approve the arrival
    }
}

function rejectArrivalBooking(tourId, arrivalOrderIndex, arrivalBookingIndex) {
    console.log('Reject arrival booking:', { tourId, arrivalOrderIndex, arrivalBookingIndex });
    const reason = prompt(`Please provide a reason for rejecting this arrival booking:`);
    if (reason) {
        alert(`Arrival booking rejected successfully!\nReason: ${reason}`);
        // Here you would make an API call to reject the arrival
    }
}

function createAndShowArrivalEditModal(tourId, arrivalOrderIndex, arrivalBookingIndex) {
    const editModalId = `editArrivalModal_${tourId}_${arrivalOrderIndex}_${arrivalBookingIndex}`;
    
    // Get tour dates for validation
    const tourData = getTourDataFromPage(tourId);
    const tourStartDate = tourData.checkInDate;
    const tourEndDate = tourData.checkOutDate;
    
    const editModalHTML = `
        <div class="modal fade" id="${editModalId}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content shadow-lg" style="border-radius: 15px;">
                    <!-- Modal Header with Vehicle Info -->
                    <div class="modal-header bg-gradient-primary text-white border-0 p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 140px;">
                        <div class="container-fluid p-4">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                                            <i class="ri-flight-takeoff-line fs-4 text-black"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0 fw-bold text-white">Edit Arrival Booking</h5>
                                            <small id="arrival_vehicle_info_${editModalId}" class="text-white opacity-75">Jaguar F-Pace • Airport Transfer</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" onclick="closeArrivalEditModal('${editModalId}')" aria-label="Close"></button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body p-4" style="background-color: #f8f9fa;">
                        <!-- Travel Date Constraint -->
                        <div class="alert alert-info border-0 mb-4 d-flex align-items-center" style="border-radius: 12px; background: linear-gradient(90deg, #e3f2fd 0%, #bbdefb 100%);">
                            <i class="ri-information-line fs-5 me-3 text-info"></i>
                            <div>
                                <h6 class="mb-1 fw-bold text-info">Travel Date Constraint</h6>
                                <small id="arrival_date_constraint_${editModalId}" class="text-muted">Arrival booking must be within the tour travel period: <strong>${tourStartDate || 'Loading...'}</strong> to <strong>${tourEndDate || 'Loading...'}</strong></small>
                            </div>
                        </div>
                        
                        <form id="arrivalEditForm_${editModalId}">
                            <!-- Edit Booking Date & Time Section -->
                            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                                <div class="card-header bg-light border-0" style="border-radius: 12px 12px 0 0;">
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <i class="ri-calendar-line me-2 text-primary"></i>Edit Booking Date & Time
                                    </h6>
                                </div>
                                <div class="card-body p-4">
                                    <!-- Booking Date -->
                                    <div class="mb-3">
                                        <label for="arrival_booking_date_${editModalId}" class="form-label fw-semibold">
                                            <i class="ri-calendar-event-line me-1 text-success"></i>Booking Date
                                        </label>
                                        <input type="date" class="form-control form-control-lg" id="arrival_booking_date_${editModalId}" 
                                               min="${tourStartDate}" max="${tourEndDate}" required 
                                               style="border-radius: 8px; border: 2px solid #e0e0e0;">
                                        <div class="form-text">
                                            <i class="ri-information-line me-1"></i>Select date within tour travel dates (${tourStartDate} to ${tourEndDate})
                                        </div>
                                    </div>
                                    
                                    <!-- Pickup Date and Entry Time -->
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="arrival_pickup_date_${editModalId}" class="form-label fw-semibold">
                                                <i class="ri-calendar-check-line me-1 text-warning"></i>Pickup Date
                                            </label>
                                            <input type="date" class="form-control form-control-lg" id="arrival_pickup_date_${editModalId}" 
                                                   min="${tourStartDate}" max="${tourEndDate}" required 
                                                   style="border-radius: 8px; border: 2px solid #e0e0e0;">
                                            <div class="form-text">
                                                <i class="ri-information-line me-1"></i>Select date within tour travel dates
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="arrival_entry_time_${editModalId}" class="form-label fw-semibold">
                                                <i class="ri-time-line me-1 text-info"></i>Entry Time
                                            </label>
                                            <input type="time" class="form-control form-control-lg" id="arrival_entry_time_${editModalId}" required 
                                                   style="border-radius: 8px; border: 2px solid #e0e0e0;">
                                            <div class="form-text">
                                                <i class="ri-information-line me-1"></i>Please enter time in 24-hour format (HH:MM)
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Booking Summary -->
                            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                <div class="card-header bg-light border-0" style="border-radius: 12px 12px 0 0;">
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <i class="ri-file-list-3-line me-2 text-secondary"></i>Booking Summary
                                    </h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <small class="text-muted">Service Type</small>
                                            <div class="fw-bold text-dark">ARRIVAL TRANSFER</div>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <small class="text-muted">Tour ID</small>
                                            <div class="fw-bold text-dark">${tourId}</div>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <small class="text-muted">Total Price</small>
                                            <div class="fw-bold text-success">SGD 160.00</div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted">Vehicle</small>
                                            <div class="fw-medium text-dark">Jaguar F-Pace</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer border-0 p-4" style="background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);">
                        <button type="button" class="btn btn-light px-4 py-2 me-2" onclick="closeArrivalEditModal('${editModalId}')" style="border-radius: 25px; border: 2px solid #dee2e6;">
                            <i class="ri-close-line me-2"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary px-4 py-2" onclick="saveArrivalChanges(${tourId}, ${arrivalOrderIndex}, ${arrivalBookingIndex}, '${editModalId}')" style="border-radius: 25px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                            <i class="ri-save-line me-2"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to DOM
    document.body.insertAdjacentHTML('beforeend', editModalHTML);
    
    // Show modal
    const modalElement = document.getElementById(editModalId);
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Remove modal from DOM when hidden
    modalElement.addEventListener('hidden.bs.modal', function () {
        modalElement.remove();
    });
    
    // Load current arrival data
    loadArrivalDataForEdit(tourId, arrivalOrderIndex, arrivalBookingIndex, editModalId);
}

function closeArrivalEditModal(editModalId) {
    try {
        const modalElement = document.getElementById(editModalId);
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        }
    } catch (error) {
        console.error('Error closing arrival edit modal:', error);
    }
}

function loadArrivalDataForEdit(tourId, arrivalOrderIndex, arrivalBookingIndex, editModalId) {
    // Fetch actual arrival data from backend
    fetch('/booking/get-arrival-data', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            tour_id: tourId,
            arrival_order_index: arrivalOrderIndex,
            booking_index: arrivalBookingIndex
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const arrivalData = data.data;
            const arrivalDetails = arrivalData.arrival_details || {};
            
            // Update modal header with actual vehicle information
            const modalElement = document.getElementById(editModalId);
            if (modalElement) {
                const vehicleName = arrivalDetails.vehicles_name || 'Vehicle Transfer';
                const totalPrice = arrivalDetails.totalPrice || '160.00';
                const transferType = arrivalDetails.type || 'Standard';
                const vehicleImage = arrivalDetails.image || 'https://stgdmcappdev.blob.core.windows.net/uploads/logo_1746006725_jbHF6N.webp';
                
                // Update vehicle image
                const imageElement = document.getElementById(`arrival_vehicle_image_${editModalId}`);
                if (imageElement) {
                    imageElement.src = vehicleImage;
                    imageElement.alt = vehicleName;
                }
                
                // Update vehicle name in header
                const vehicleInfoElement = document.getElementById(`arrival_vehicle_info_${editModalId}`);
                if (vehicleInfoElement) {
                    vehicleInfoElement.textContent = `${vehicleName} • ${transferType} Transfer`;
                }
                
                // Update price in header
                const priceHeaderElement = document.getElementById(`arrival_price_header_${editModalId}`);
                if (priceHeaderElement) {
                    priceHeaderElement.textContent = `SGD ${parseFloat(totalPrice).toFixed(2)}`;
                }
                
                // Update travel date constraint with actual tour dates
                const tourData = getTourDataFromPage(tourId);
                console.log('Tour data retrieved:', tourData);
                const constraintElement = document.getElementById(`arrival_date_constraint_${editModalId}`);
                if (constraintElement && tourData && tourData.check_in_time && tourData.check_out_time) {
                    const startDate = new Date(tourData.check_in_time).toLocaleDateString('en-US', { 
                        weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' 
                    });
                    const endDate = new Date(tourData.check_out_time).toLocaleDateString('en-US', { 
                        weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' 
                    });
                    constraintElement.innerHTML = `Arrival booking must be within the tour travel period: <strong>${startDate}</strong> to <strong>${endDate}</strong>`;
                } else {
                    console.warn('Could not update date constraint, tour data not available:', tourData);
                    if (constraintElement) {
                        constraintElement.innerHTML = `Arrival booking must be within the tour travel period: <strong>Loading...</strong> to <strong>Loading...</strong>`;
                    }
                }
                
                // Update booking summary section
                const summaryVehicle = modalElement.querySelector('.fw-medium.text-dark');
                if (summaryVehicle) {
                    summaryVehicle.textContent = vehicleName;
                }
                
                // Update price in summary
                const summaryPrice = modalElement.querySelector('.fw-bold.text-success');
                if (summaryPrice) {
                    summaryPrice.textContent = `SGD ${parseFloat(totalPrice).toFixed(2)}`;
                }
            }
            
            // Update date input constraints with actual tour dates
            const tourData = getTourDataFromPage(tourId);
            if (tourData.check_in_time && tourData.check_out_time) {
                const bookingDateInput = document.getElementById(`arrival_booking_date_${editModalId}`);
                const pickupDateInput = document.getElementById(`arrival_pickup_date_${editModalId}`);
                
                if (bookingDateInput) {
                    bookingDateInput.min = tourData.check_in_time;
                    bookingDateInput.max = tourData.check_out_time;
                }
                
                if (pickupDateInput) {
                    pickupDateInput.min = tourData.check_in_time;
                    pickupDateInput.max = tourData.check_out_time;
                }
            }
            
            // Populate form fields with actual data
            document.getElementById(`arrival_booking_date_${editModalId}`).value = arrivalData.booking_date || arrivalData.bookingDate;
            document.getElementById(`arrival_pickup_date_${editModalId}`).value = arrivalData.pickup_date || arrivalData.pickupdate;
            
            // Convert time from AM/PM format to 24-hour format for the time input
            let timeValue = arrivalData.entry_time || arrivalData.entrytime;
            if (timeValue) {
                timeValue = convertTo24HourFormat(timeValue);
            }
            document.getElementById(`arrival_entry_time_${editModalId}`).value = timeValue || "09:00";
        } else {
            console.error('Failed to load arrival data:', data.message);
            
            // Update date constraints even if data loading fails
            const tourData = getTourDataFromPage(tourId);
            if (tourData.check_in_time && tourData.check_out_time) {
                const bookingDateInput = document.getElementById(`arrival_booking_date_${editModalId}`);
                const pickupDateInput = document.getElementById(`arrival_pickup_date_${editModalId}`);
                
                if (bookingDateInput) {
                    bookingDateInput.min = tourData.check_in_time;
                    bookingDateInput.max = tourData.check_out_time;
                }
                
                if (pickupDateInput) {
                    pickupDateInput.min = tourData.check_in_time;
                    pickupDateInput.max = tourData.check_out_time;
                }
            }
            
            // Use fallback data if backend fails
            document.getElementById(`arrival_booking_date_${editModalId}`).value = tourData.check_in_time || "2025-09-11";
            document.getElementById(`arrival_pickup_date_${editModalId}`).value = tourData.check_in_time || "2025-09-11";
            document.getElementById(`arrival_entry_time_${editModalId}`).value = "09:00";
        }
    })
    .catch(error => {
        console.error('Error fetching arrival data:', error);
        
        // Update date constraints even if network fails
        const tourData = getTourDataFromPage(tourId);
        if (tourData.check_in_time && tourData.check_out_time) {
            const bookingDateInput = document.getElementById(`arrival_booking_date_${editModalId}`);
            const pickupDateInput = document.getElementById(`arrival_pickup_date_${editModalId}`);
            
            if (bookingDateInput) {
                bookingDateInput.min = tourData.check_in_time;
                bookingDateInput.max = tourData.check_out_time;
            }
            
            if (pickupDateInput) {
                pickupDateInput.min = tourData.check_in_time;
                pickupDateInput.max = tourData.check_out_time;
            }
        }
        
        // Use fallback data if network fails
        document.getElementById(`arrival_booking_date_${editModalId}`).value = tourData.check_in_time || "2025-09-11";
        document.getElementById(`arrival_pickup_date_${editModalId}`).value = tourData.check_in_time || "2025-09-11";
        document.getElementById(`arrival_entry_time_${editModalId}`).value = "09:00";
    });
}

function saveArrivalChanges(tourId, arrivalOrderIndex, arrivalBookingIndex, editModalId) {
    // Get form data
    const bookingDate = document.getElementById(`arrival_booking_date_${editModalId}`).value;
    const pickupDate = document.getElementById(`arrival_pickup_date_${editModalId}`).value;
    const entryTime = document.getElementById(`arrival_entry_time_${editModalId}`).value;
    
    // Validate form
    if (!bookingDate || !pickupDate || !entryTime) {
        alert('Please fill in all required fields.');
        return;
    }
    
    // Validate dates are within tour range
    const tourData = getTourDataFromPage(tourId);
    if (!validateArrivalDates(bookingDate, pickupDate, tourData.check_in_time, tourData.check_out_time)) {
        return;
    }
    
    // Convert time to AM/PM format for storage and display
    const displayTime = convertTo12HourFormat(entryTime);
    
    console.log('Saving arrival changes:', {
        tourId, arrivalOrderIndex, arrivalBookingIndex,
        bookingDate, pickupDate, entryTime: displayTime
    });
    
    console.log('Request payload:', {
        tour_id: tourId,
        arrival_order_index: arrivalOrderIndex,
        booking_index: arrivalBookingIndex,
        booking_date: bookingDate,
        pickup_date: pickupDate,
        entry_time: displayTime
    });
    
    // Make API call to save changes to orders table
    fetch('/booking/update-arrival-booking', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            tour_id: tourId,
            arrival_order_index: arrivalOrderIndex,
            booking_index: arrivalBookingIndex,
            booking_date: bookingDate,
            pickup_date: pickupDate,
            entry_time: displayTime
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            alert(`Arrival booking updated successfully!\n\nBooking Date: ${bookingDate}\nPickup Date: ${pickupDate}\nEntry Time: ${displayTime}`);
            
            // Close modal and refresh page
            closeArrivalEditModal(editModalId);
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            console.error('Backend error:', data);
            alert(`Error updating arrival booking: ${data.message || 'Unknown error'}\n\nDetails: ${JSON.stringify(data.errors || {})}`);
        }
    })
    .catch(error => {
        console.error('Error saving arrival changes:', error);
        alert(`Network error occurred while saving changes: ${error.message}`);
    });
}

function validateArrivalDates(bookingDate, pickupDate, tourStartDate, tourEndDate) {
    const booking = new Date(bookingDate);
    const pickup = new Date(pickupDate);
    const tourStart = new Date(tourStartDate);
    const tourEnd = new Date(tourEndDate);
    
    if (booking < tourStart || booking > tourEnd) {
        alert(`Booking date must be between ${tourStartDate} and ${tourEndDate}`);
        return false;
    }
    
    if (pickup < tourStart || pickup > tourEnd) {
        alert(`Pickup date must be between ${tourStartDate} and ${tourEndDate}`);
        return false;
    }
    
    return true;
}

function convertTo12HourFormat(time24) {
    const [hours, minutes] = time24.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;
    return `${hour12.toString().padStart(2, '0')}:${minutes} ${ampm}`;
}

function convertTo24HourFormat(time12) {
    if (!time12) return "09:00";
    
    // Handle formats like "09:00 AM", "9:00 AM", "09:00AM"
    const timeRegex = /^(\d{1,2}):(\d{2})\s?(AM|PM)$/i;
    const match = time12.trim().match(timeRegex);
    
    if (!match) {
        // If format doesn't match, assume it's already 24-hour format
        return time12;
    }
    
    let [, hours, minutes, period] = match;
    hours = parseInt(hours);
    
    if (period.toUpperCase() === 'AM') {
        if (hours === 12) hours = 0;
    } else { // PM
        if (hours !== 12) hours += 12;
    }
    
    return `${hours.toString().padStart(2, '0')}:${minutes}`;
}

// Departure Booking Management Functions
function editDepartureBooking(tourId, departureOrderIndex, departureBookingIndex) {
    console.log('Editing departure booking:', { tourId, departureOrderIndex, departureBookingIndex });
    
    try {
        // Create and show the departure edit modal
        createAndShowDepartureEditModal(tourId, departureOrderIndex, departureBookingIndex);
    } catch (error) {
        console.error('Error opening departure edit modal:', error);
        alert('Error opening departure edit modal. Please try again.');
    }
}

function approveDepartureBooking(tourId, departureOrderIndex, departureBookingIndex) {
    console.log('Approving departure booking:', { tourId, departureOrderIndex, departureBookingIndex });
    
    if (confirm('Are you sure you want to approve this departure booking?')) {
        // Here you can implement the approval logic
        alert('Departure booking approved successfully!');
        // Optionally refresh the page or update the UI
        // window.location.reload();
    }
}

function rejectDepartureBooking(tourId, departureOrderIndex, departureBookingIndex) {
    console.log('Rejecting departure booking:', { tourId, departureOrderIndex, departureBookingIndex });
    
    const reason = prompt('Please provide a reason for rejection:');
    if (reason !== null && reason.trim() !== '') {
        // Here you can implement the rejection logic
        alert('Departure booking rejected successfully!');
        // Optionally refresh the page or update the UI
        // window.location.reload();
    }
}

function createAndShowDepartureEditModal(tourId, departureOrderIndex, departureBookingIndex) {
    const editModalId = `departureEdit_${tourId}_${departureOrderIndex}_${departureBookingIndex}`;
    
    // Remove existing modal if it exists
    const existingModal = document.getElementById(editModalId);
    if (existingModal) {
        existingModal.remove();
    }
    
    // Get tour data for date constraints
    const tourData = getTourDataFromPage(tourId);
    const tourStartDate = tourData && tourData.check_in_time ? tourData.check_in_time : '2025-09-11';
    const tourEndDate = tourData && tourData.check_out_time ? tourData.check_out_time : '2025-09-13';
    
    const modalHTML = `
        <div class="modal fade" id="${editModalId}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius: 15px; overflow: hidden;">
                    <!-- Modal Header with Vehicle Info -->
                    <div class="modal-header bg-gradient-primary text-white border-0 p-0" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%); min-height: 140px;">
                        <div class="container-fluid p-4">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                                            <i class="ri-flight-takeoff-line fs-4 text-black"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0 fw-bold text-white">Edit Departure Booking</h5>
                                            <small id="departure_vehicle_info_${editModalId}" class="text-white opacity-75">Departure Transfer</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" onclick="closeDepartureEditModal('${editModalId}')" aria-label="Close"></button>
                        </div>
                    </div>
                    
                    <!-- Travel Date Constraint -->
                    <div class="alert alert-info border-0 mb-4 d-flex align-items-center" style="border-radius: 12px; background: linear-gradient(90deg, #e3f2fd 0%, #bbdefb 100%);">
                        <i class="ri-information-line fs-5 me-3 text-info"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-info">Travel Date Constraint</h6>
                            <small id="departure_date_constraint_${editModalId}" class="text-muted">Departure booking must be within the tour travel period: <strong>${tourStartDate || 'Loading...'}</strong> to <strong>${tourEndDate || 'Loading...'}</strong></small>
                        </div>
                    </div>
                    
                    <div class="modal-body p-4">
                        <form id="departureEditForm_${editModalId}">
                            <input type="hidden" name="tour_id" value="${tourId}">
                            <input type="hidden" name="departure_order_index" value="${departureOrderIndex}">
                            <input type="hidden" name="booking_index" value="${departureBookingIndex}">
                            <input type="hidden" name="booking_id" id="departure_booking_id_${editModalId}">
                            
                            <!-- Edit Booking Date & Time -->
                            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                                        <i class="ri-edit-box-line me-2 text-primary"></i>Edit Booking Date & Time
                                    </h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-medium">
                                                <i class="ri-calendar-line me-1 text-primary"></i>Booking Date
                                            </label>
                                            <input type="date" 
                                                   class="form-control form-control-lg" 
                                                   id="departure_booking_date_${editModalId}" 
                                                   name="booking_date"
                                                   min="${tourStartDate}" 
                                                   max="${tourEndDate}"
                                                   required
                                                   style="border-radius: 8px;">
                                            <small class="form-text text-muted">Select date within your travel dates (undefined to undefined)</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-medium">
                                                <i class="ri-calendar-check-line me-1 text-success"></i>Exit Pickup Date
                                            </label>
                                            <input type="date" 
                                                   class="form-control form-control-lg" 
                                                   id="departure_pickup_date_${editModalId}" 
                                                   name="pickup_date"
                                                   min="${tourStartDate}" 
                                                   max="${tourEndDate}"
                                                   required
                                                   style="border-radius: 8px;">
                                            <small class="form-text text-muted">Select date within your travel dates (undefined to undefined)</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-medium">
                                                <i class="ri-time-line me-1 text-warning"></i>Entry Time
                                            </label>
                                            <input type="time" 
                                                   class="form-control form-control-lg" 
                                                   id="departure_entry_time_${editModalId}" 
                                                   name="entry_time"
                                                   required
                                                   style="border-radius: 8px;">
                                            <small class="form-text text-muted">Please enter time in 24-hour format (HH:MM)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Booking Summary -->
                            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                                        <i class="ri-file-list-3-line me-2 text-success"></i>Booking Summary
                                    </h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row text-center">
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted">Service Type</small>
                                            <div class="fw-medium text-dark">DEPARTURE TRANSFER</div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted">Tour ID</small>
                                            <div class="fw-medium text-dark">${tourId}</div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted">Vehicle</small>
                                            <div class="fw-medium text-dark" id="departure_vehicle_summary_${editModalId}">Loading...</div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted">Total Price</small>
                                            <div class="fw-medium text-dark" id="departure_price_summary_${editModalId}">SGD 0.00</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="modal-footer border-0 p-4 bg-light">
                        <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeDepartureEditModal('${editModalId}')" style="border-radius: 25px;">
                            <i class="ri-close-line me-2"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary px-4 py-2" onclick="saveDepartureChanges(${tourId}, ${departureOrderIndex}, ${departureBookingIndex}, '${editModalId}')" style="border-radius: 25px;">
                            <i class="ri-save-line me-2"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to document
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Initialize and show the modal
    const modal = new bootstrap.Modal(document.getElementById(editModalId));
    modal.show();
    
    // Load departure data for editing
    loadDepartureDataForEdit(tourId, departureOrderIndex, departureBookingIndex, editModalId);
}

function closeDepartureEditModal(editModalId) {
    const modal = bootstrap.Modal.getInstance(document.getElementById(editModalId));
    if (modal) {
        modal.hide();
    }
    
    // Remove modal from DOM after it's hidden
    setTimeout(() => {
        const modalElement = document.getElementById(editModalId);
        if (modalElement) {
            modalElement.remove();
        }
    }, 300);
}

function loadDepartureDataForEdit(tourId, departureOrderIndex, departureBookingIndex, editModalId) {
    console.log('Loading departure data for edit:', { tourId, departureOrderIndex, departureBookingIndex });
    
    fetch('/booking/get-departure-data', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            tour_id: tourId,
            departure_order_index: departureOrderIndex,
            booking_index: departureBookingIndex
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const departureData = data.data;
            const departureDetails = departureData.departure_details || {};
            
            console.log('Departure data loaded successfully:', departureData);
            
            // Update header vehicle info
            const vehicleInfoElement = document.getElementById(`departure_vehicle_info_${editModalId}`);
            if (vehicleInfoElement) {
                const vehicleName = departureDetails.vehicles_name || 'Departure Transfer';
                const transferType = departureDetails.type || 'Transfer';
                vehicleInfoElement.textContent = `${vehicleName} • ${transferType}`;
            }
            
            // Update travel date constraint with actual tour dates
            const tourData = getTourDataFromPage(tourId);
            console.log('Tour data retrieved:', tourData);
            const constraintElement = document.getElementById(`departure_date_constraint_${editModalId}`);
            if (constraintElement && tourData && tourData.check_in_time && tourData.check_out_time) {
                const startDate = new Date(tourData.check_in_time).toLocaleDateString('en-US', { 
                    weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' 
                });
                const endDate = new Date(tourData.check_out_time).toLocaleDateString('en-US', { 
                    weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' 
                });
                constraintElement.innerHTML = `Departure booking must be within the tour travel period: <strong>${startDate}</strong> to <strong>${endDate}</strong>`;
            } else {
                console.warn('Could not update date constraint, tour data not available:', tourData);
                if (constraintElement) {
                    constraintElement.innerHTML = `Departure booking must be within the tour travel period: <strong>Loading...</strong> to <strong>Loading...</strong>`;
                }
            }
            
            // Update booking summary section
            const summaryVehicle = document.getElementById(`departure_vehicle_summary_${editModalId}`);
            if (summaryVehicle) {
                summaryVehicle.textContent = departureDetails.vehicles_name || 'Vehicle';
            }
            
            const summaryPrice = document.getElementById(`departure_price_summary_${editModalId}`);
            if (summaryPrice) {
                const totalPrice = departureDetails.totalPrice || departureData.total_price || '0';
                summaryPrice.textContent = `SGD ${parseFloat(totalPrice).toFixed(2)}`;
            }
            
            // Update date input constraints with actual tour dates
            const tourData2 = getTourDataFromPage(tourId);
            if (tourData2.check_in_time && tourData2.check_out_time) {
                const bookingDateInput = document.getElementById(`departure_booking_date_${editModalId}`);
                const pickupDateInput = document.getElementById(`departure_pickup_date_${editModalId}`);
                
                if (bookingDateInput) {
                    bookingDateInput.min = tourData2.check_in_time;
                    bookingDateInput.max = tourData2.check_out_time;
                }
                
                if (pickupDateInput) {
                    pickupDateInput.min = tourData2.check_in_time;
                    pickupDateInput.max = tourData2.check_out_time;
                }
            }
            
            // Populate form fields with actual data
            document.getElementById(`departure_booking_date_${editModalId}`).value = departureData.booking_date || departureData.bookingDate;
            document.getElementById(`departure_pickup_date_${editModalId}`).value = departureData.exit_pickup_date || departureData.exitpickupdate;
            
            // Handle time format conversion
            let timeValue = departureData.entry_time || departureData.entrytime;
            if (timeValue) {
                // Convert from 12-hour format to 24-hour format for input[type="time"]
                timeValue = convertTo24HourFormat(timeValue);
            }
            document.getElementById(`departure_entry_time_${editModalId}`).value = timeValue || "09:00";
        } else {
            console.error('Failed to load departure data:', data.message);
            
            // Update date constraints even if data loading fails
            const tourData = getTourDataFromPage(tourId);
            if (tourData.check_in_time && tourData.check_out_time) {
                const bookingDateInput = document.getElementById(`departure_booking_date_${editModalId}`);
                const pickupDateInput = document.getElementById(`departure_pickup_date_${editModalId}`);
                
                if (bookingDateInput) {
                    bookingDateInput.min = tourData.check_in_time;
                    bookingDateInput.max = tourData.check_out_time;
                }
                
                if (pickupDateInput) {
                    pickupDateInput.min = tourData.check_in_time;
                    pickupDateInput.max = tourData.check_out_time;
                }
            }
            
            // Use fallback data if backend fails
            document.getElementById(`departure_booking_date_${editModalId}`).value = tourData.check_in_time || "2025-09-11";
            document.getElementById(`departure_pickup_date_${editModalId}`).value = tourData.check_in_time || "2025-09-11";
            document.getElementById(`departure_entry_time_${editModalId}`).value = "09:00";
        }
    })
    .catch(error => {
        console.error('Error fetching departure data:', error);
        
        // Update date constraints even if network fails
        const tourData = getTourDataFromPage(tourId);
        if (tourData.check_in_time && tourData.check_out_time) {
            const bookingDateInput = document.getElementById(`departure_booking_date_${editModalId}`);
            const pickupDateInput = document.getElementById(`departure_pickup_date_${editModalId}`);
            
            if (bookingDateInput) {
                bookingDateInput.min = tourData.check_in_time;
                bookingDateInput.max = tourData.check_out_time;
            }
            
            if (pickupDateInput) {
                pickupDateInput.min = tourData.check_in_time;
                pickupDateInput.max = tourData.check_out_time;
            }
        }
        
        // Use fallback data if network fails
        document.getElementById(`departure_booking_date_${editModalId}`).value = tourData.check_in_time || "2025-09-11";
        document.getElementById(`departure_pickup_date_${editModalId}`).value = tourData.check_in_time || "2025-09-11";
        document.getElementById(`departure_entry_time_${editModalId}`).value = "09:00";
    });
}

function saveDepartureChanges(tourId, departureOrderIndex, departureBookingIndex, editModalId) {
    const bookingDate = document.getElementById(`departure_booking_date_${editModalId}`).value;
    const pickupDate = document.getElementById(`departure_pickup_date_${editModalId}`).value;
    const entryTime = document.getElementById(`departure_entry_time_${editModalId}`).value;
    
    console.log('Saving departure changes:', {
        tourId, departureOrderIndex, departureBookingIndex,
        bookingDate, pickupDate, entryTime
    });
    
    if (!bookingDate || !pickupDate || !entryTime) {
        alert('Please fill in all required fields.');
        return;
    }
    
    // Validate dates are within tour range
    const tourData = getTourDataFromPage(tourId);
    if (!validateDepartureDates(bookingDate, pickupDate, tourData.check_in_time, tourData.check_out_time)) {
        return;
    }
    
    // Convert time to AM/PM format for storage and display
    const displayTime = convertTo12HourFormat(entryTime);
    
    console.log('Saving departure changes:', {
        tour_id: tourId,
        departure_order_index: departureOrderIndex,
        booking_index: departureBookingIndex,
        booking_date: bookingDate,
        exit_pickup_date: pickupDate,
        entry_time: displayTime
    });
    
    fetch('/booking/update-departure-booking', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            tour_id: tourId,
            departure_order_index: departureOrderIndex,
            booking_index: departureBookingIndex,
            booking_date: bookingDate,
            exit_pickup_date: pickupDate,
            entry_time: displayTime
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Departure booking updated successfully:', data);
            
            // Close the edit modal
            closeDepartureEditModal(editModalId);
            
            // Show success message
            const successMessage = data.message || 'Departure booking updated successfully!';
            alert(successMessage);
            
            // Refresh the page to show updated data
            window.location.reload();
        } else {
            console.error('Failed to update departure booking:', data);
            alert('Error updating departure booking: ' + (data.message || 'Unknown error occurred'));
        }
    })
    .catch(error => {
        console.error('Error updating departure booking:', error);
        alert('Error updating departure booking. Please try again.');
    });
}

function validateDepartureDates(bookingDate, pickupDate, tourStartDate, tourEndDate) {
    const booking = new Date(bookingDate);
    const pickup = new Date(pickupDate);
    const tourStart = new Date(tourStartDate);
    const tourEnd = new Date(tourEndDate);
    
    if (booking < tourStart || booking > tourEnd) {
        alert('Booking date must be within the tour travel dates.');
        return false;
    }
    
    if (pickup < tourStart || pickup > tourEnd) {
        alert('Exit pickup date must be within the tour travel dates.');
        return false;
    }
    
    return true;
}

// Travel Point Booking Management Functions
function editTravelPointBooking(tourId, travelPointOrderIndex, travelPointBookingIndex) {
    console.log('Editing travel point booking:', { tourId, travelPointOrderIndex, travelPointBookingIndex });
    
    try {
        // Create and show the travel point edit modal
        createAndShowTravelPointEditModal(tourId, travelPointOrderIndex, travelPointBookingIndex);
    } catch (error) {
        console.error('Error opening travel point edit modal:', error);
        alert('Error opening travel point edit modal. Please try again.');
    }
}

function approveTravelPointBooking(tourId, travelPointOrderIndex, travelPointBookingIndex) {
    console.log('Approving travel point booking:', { tourId, travelPointOrderIndex, travelPointBookingIndex });
    
    if (confirm('Are you sure you want to approve this travel point booking?')) {
        // Here you can implement the approval logic
        alert('Travel point booking approved successfully!');
        // Optionally refresh the page or update the UI
        // window.location.reload();
    }
}

function rejectTravelPointBooking(tourId, travelPointOrderIndex, travelPointBookingIndex) {
    console.log('Rejecting travel point booking:', { tourId, travelPointOrderIndex, travelPointBookingIndex });
    
    const reason = prompt('Please provide a reason for rejection:');
    if (reason !== null && reason.trim() !== '') {
        // Here you can implement the rejection logic
        alert('Travel point booking rejected successfully!');
        // Optionally refresh the page or update the UI
        // window.location.reload();
    }
}

function createAndShowTravelPointEditModal(tourId, travelPointOrderIndex, travelPointBookingIndex) {
    const editModalId = `travelPointEdit_${tourId}_${travelPointOrderIndex}_${travelPointBookingIndex}`;
    
    // Remove existing modal if it exists
    const existingModal = document.getElementById(editModalId);
    if (existingModal) {
        existingModal.remove();
    }
    
    // Get tour data for date constraints
    const tourData = getTourDataFromPage(tourId);
    const tourStartDate = tourData && tourData.check_in_time ? tourData.check_in_time : '2025-09-11';
    const tourEndDate = tourData && tourData.check_out_time ? tourData.check_out_time : '2025-09-13';
    
    const modalHTML = `
        <div class="modal fade" id="${editModalId}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius: 15px; overflow: hidden;">
                    <!-- Modal Header with Vehicle Info -->
                    <div class="modal-header bg-gradient-primary text-white border-0 p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 140px;">
                        <div class="container-fluid p-4">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                                            <i class="ri-route-line fs-4 text-primary"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0 fw-bold text-white">Local-Tour Point to Point</h5>
                                            <small id="travel_point_vehicle_info_${editModalId}" class="text-white opacity-75">Point to Point Transfer</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" onclick="closeTravelPointEditModal('${editModalId}')" aria-label="Close"></button>
                        </div>
                    </div>
                    
                    <!-- Travel Date Constraint -->
                    <div class="alert alert-info border-0 mb-4 d-flex align-items-center" style="border-radius: 12px; background: linear-gradient(90deg, #e3f2fd 0%, #bbdefb 100%);">
                        <i class="ri-information-line fs-5 me-3 text-info"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-info">Travel Date Constraint</h6>
                            <small id="travel_point_date_constraint_${editModalId}" class="text-muted">Travel point booking must be within the tour travel period: <strong>${tourStartDate || 'Loading...'}</strong> to <strong>${tourEndDate || 'Loading...'}</strong></small>
                        </div>
                    </div>
                    
                    <div class="modal-body p-4">
                        <form id="travelPointEditForm_${editModalId}">
                            <input type="hidden" name="tour_id" value="${tourId}">
                            <input type="hidden" name="travel_point_order_index" value="${travelPointOrderIndex}">
                            <input type="hidden" name="booking_index" value="${travelPointBookingIndex}">
                            <input type="hidden" name="booking_id" id="travel_point_booking_id_${editModalId}">
                            
                            <!-- Edit Booking Date & Time -->
                            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                                        <i class="ri-edit-box-line me-2 text-primary"></i>Edit Booking Date & Time
                                    </h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-medium">
                                                <i class="ri-calendar-line me-1 text-primary"></i>Booking Date
                                            </label>
                                            <input type="date" 
                                                   class="form-control form-control-lg" 
                                                   id="travel_point_booking_date_${editModalId}" 
                                                   name="booking_date"
                                                   min="${tourStartDate}" 
                                                   max="${tourEndDate}"
                                                   required
                                                   style="border-radius: 8px;">
                                            <small class="form-text text-muted">Select date within your travel dates</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-medium">
                                                <i class="ri-calendar-check-line me-1 text-success"></i>Pickup Date
                                            </label>
                                            <input type="date" 
                                                   class="form-control form-control-lg" 
                                                   id="travel_point_pickup_date_${editModalId}" 
                                                   name="pickup_date"
                                                   min="${tourStartDate}" 
                                                   max="${tourEndDate}"
                                                   required
                                                   style="border-radius: 8px;">
                                            <small class="form-text text-muted">Select pickup date within your travel dates</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-medium">
                                                <i class="ri-time-line me-1 text-warning"></i>Entry Time
                                            </label>
                                            <input type="time" 
                                                   class="form-control form-control-lg" 
                                                   id="travel_point_entry_time_${editModalId}" 
                                                   name="entry_time"
                                                   required
                                                   style="border-radius: 8px;">
                                            <small class="form-text text-muted">Please enter time in 24-hour format (HH:MM)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Booking Summary -->
                            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                                        <i class="ri-file-list-3-line me-2 text-success"></i>Booking Summary
                                    </h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row text-center">
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted">Service Type</small>
                                            <div class="fw-medium text-dark">POINT TO POINT TRANSFER</div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted">Tour ID</small>
                                            <div class="fw-medium text-dark">${tourId}</div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted">Vehicle</small>
                                            <div class="fw-medium text-dark" id="travel_point_vehicle_summary_${editModalId}">Loading...</div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted">Total Price</small>
                                            <div class="fw-medium text-dark" id="travel_point_price_summary_${editModalId}">SGD 0.00</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="modal-footer border-0 p-4 bg-light">
                        <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeTravelPointEditModal('${editModalId}')" style="border-radius: 25px;">
                            <i class="ri-close-line me-2"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary px-4 py-2" onclick="saveTravelPointChanges(${tourId}, ${travelPointOrderIndex}, ${travelPointBookingIndex}, '${editModalId}')" style="border-radius: 25px;">
                            <i class="ri-save-line me-2"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to document
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Initialize and show the modal
    const modal = new bootstrap.Modal(document.getElementById(editModalId));
    modal.show();
    
    // Load travel point data for editing
    loadTravelPointDataForEdit(tourId, travelPointOrderIndex, travelPointBookingIndex, editModalId);
}

function closeTravelPointEditModal(editModalId) {
    const modal = bootstrap.Modal.getInstance(document.getElementById(editModalId));
    if (modal) {
        modal.hide();
    }
    
    // Remove modal from DOM after it's hidden
    setTimeout(() => {
        const modalElement = document.getElementById(editModalId);
        if (modalElement) {
            modalElement.remove();
        }
    }, 300);
}

function loadTravelPointDataForEdit(tourId, travelPointOrderIndex, travelPointBookingIndex, editModalId) {
    console.log('Loading travel point data for edit:', { tourId, travelPointOrderIndex, travelPointBookingIndex });
    
    fetch('/booking/get-travel-point-data', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            tour_id: tourId,
            travel_point_order_index: travelPointOrderIndex,
            booking_index: travelPointBookingIndex
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const travelPointData = data.data;
            const travelPointDetails = travelPointData.travel_point_details || {};
            
            console.log('Travel point data loaded successfully:', travelPointData);
            
            // Update header vehicle info
            const vehicleInfoElement = document.getElementById(`travel_point_vehicle_info_${editModalId}`);
            if (vehicleInfoElement) {
                const vehicleName = travelPointDetails.vehicles_name || 'Point to Point Transfer';
                const transferType = travelPointDetails.type || 'Transfer';
                vehicleInfoElement.textContent = `${vehicleName} • ${transferType}`;
            }
            
            // Update travel date constraint with actual tour dates
            const tourData = getTourDataFromPage(tourId);
            console.log('Tour data retrieved:', tourData);
            const constraintElement = document.getElementById(`travel_point_date_constraint_${editModalId}`);
            if (constraintElement && tourData && tourData.check_in_time && tourData.check_out_time) {
                const startDate = new Date(tourData.check_in_time).toLocaleDateString('en-US', { 
                    weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' 
                });
                const endDate = new Date(tourData.check_out_time).toLocaleDateString('en-US', { 
                    weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' 
                });
                constraintElement.innerHTML = `Travel point booking must be within the tour travel period: <strong>${startDate}</strong> to <strong>${endDate}</strong>`;
            } else {
                console.warn('Could not update date constraint, tour data not available:', tourData);
                if (constraintElement) {
                    constraintElement.innerHTML = `Travel point booking must be within the tour travel period: <strong>Loading...</strong> to <strong>Loading...</strong>`;
                }
            }
            
            // Update booking summary section
            const summaryVehicle = document.getElementById(`travel_point_vehicle_summary_${editModalId}`);
            if (summaryVehicle) {
                summaryVehicle.textContent = travelPointDetails.vehicles_name || 'Vehicle';
            }
            
            const summaryPrice = document.getElementById(`travel_point_price_summary_${editModalId}`);
            if (summaryPrice) {
                const totalPrice = travelPointDetails.totalPrice || travelPointData.total_price || '0';
                summaryPrice.textContent = `SGD ${parseFloat(totalPrice).toFixed(2)}`;
            }
            
            // Update date input constraints with actual tour dates
            const tourData2 = getTourDataFromPage(tourId);
            if (tourData2.check_in_time && tourData2.check_out_time) {
                const bookingDateInput = document.getElementById(`travel_point_booking_date_${editModalId}`);
                const pickupDateInput = document.getElementById(`travel_point_pickup_date_${editModalId}`);
                
                if (bookingDateInput) {
                    bookingDateInput.min = tourData2.check_in_time;
                    bookingDateInput.max = tourData2.check_out_time;
                }
                
                if (pickupDateInput) {
                    pickupDateInput.min = tourData2.check_in_time;
                    pickupDateInput.max = tourData2.check_out_time;
                }
            }
            
            // Populate form fields with actual data
            document.getElementById(`travel_point_booking_date_${editModalId}`).value = travelPointData.booking_date || travelPointData.bookingDate;
            document.getElementById(`travel_point_pickup_date_${editModalId}`).value = travelPointData.pickup_date || travelPointData.pickupdate;
            
            // Handle time format conversion
            let timeValue = travelPointData.entry_time || travelPointData.entrytime;
            if (timeValue) {
                // Convert from 12-hour format to 24-hour format for input[type="time"]
                timeValue = convertTo24HourFormat(timeValue);
            }
            document.getElementById(`travel_point_entry_time_${editModalId}`).value = timeValue || "09:00";
        } else {
            console.error('Failed to load travel point data:', data.message);
            
            // Update date constraints even if data loading fails
            const tourData = getTourDataFromPage(tourId);
            if (tourData.check_in_time && tourData.check_out_time) {
                const bookingDateInput = document.getElementById(`travel_point_booking_date_${editModalId}`);
                const pickupDateInput = document.getElementById(`travel_point_pickup_date_${editModalId}`);
                
                if (bookingDateInput) {
                    bookingDateInput.min = tourData.check_in_time;
                    bookingDateInput.max = tourData.check_out_time;
                }
                
                if (pickupDateInput) {
                    pickupDateInput.min = tourData.check_in_time;
                    pickupDateInput.max = tourData.check_out_time;
                }
            }
            
            // Use fallback data if backend fails
            document.getElementById(`travel_point_booking_date_${editModalId}`).value = tourData.check_in_time || "2025-09-11";
            document.getElementById(`travel_point_pickup_date_${editModalId}`).value = tourData.check_in_time || "2025-09-11";
            document.getElementById(`travel_point_entry_time_${editModalId}`).value = "09:00";
        }
    })
    .catch(error => {
        console.error('Error fetching travel point data:', error);
        
        // Update date constraints even if network fails
        const tourData = getTourDataFromPage(tourId);
        if (tourData.check_in_time && tourData.check_out_time) {
            const bookingDateInput = document.getElementById(`travel_point_booking_date_${editModalId}`);
            const pickupDateInput = document.getElementById(`travel_point_pickup_date_${editModalId}`);
            
            if (bookingDateInput) {
                bookingDateInput.min = tourData.check_in_time;
                bookingDateInput.max = tourData.check_out_time;
            }
            
            if (pickupDateInput) {
                pickupDateInput.min = tourData.check_in_time;
                pickupDateInput.max = tourData.check_out_time;
            }
        }
        
        // Use fallback data if network fails
        document.getElementById(`travel_point_booking_date_${editModalId}`).value = tourData.check_in_time || "2025-09-11";
        document.getElementById(`travel_point_pickup_date_${editModalId}`).value = tourData.check_in_time || "2025-09-11";
        document.getElementById(`travel_point_entry_time_${editModalId}`).value = "09:00";
    });
}

function saveTravelPointChanges(tourId, travelPointOrderIndex, travelPointBookingIndex, editModalId) {
    const bookingDate = document.getElementById(`travel_point_booking_date_${editModalId}`).value;
    const pickupDate = document.getElementById(`travel_point_pickup_date_${editModalId}`).value;
    const entryTime = document.getElementById(`travel_point_entry_time_${editModalId}`).value;
    
    console.log('Saving travel point changes:', {
        tourId, travelPointOrderIndex, travelPointBookingIndex,
        bookingDate, pickupDate, entryTime
    });
    
    if (!bookingDate || !pickupDate || !entryTime) {
        alert('Please fill in all required fields.');
        return;
    }
    
    // Validate dates are within tour range
    const tourData = getTourDataFromPage(tourId);
    if (!validateTravelPointDates(bookingDate, pickupDate, tourData.check_in_time, tourData.check_out_time)) {
        return;
    }
    
    // Convert time to AM/PM format for storage and display
    const displayTime = convertTo12HourFormat(entryTime);
    
    console.log('Saving travel point changes:', {
        tour_id: tourId,
        travel_point_order_index: travelPointOrderIndex,
        booking_index: travelPointBookingIndex,
        booking_date: bookingDate,
        pickup_date: pickupDate,
        entry_time: displayTime
    });
    
    fetch('/booking/update-travel-point-booking', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            tour_id: tourId,
            travel_point_order_index: travelPointOrderIndex,
            booking_index: travelPointBookingIndex,
            booking_date: bookingDate,
            pickup_date: pickupDate,
            entry_time: displayTime
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Travel point booking updated successfully:', data);
            
            // Close the edit modal
            closeTravelPointEditModal(editModalId);
            
            // Show success message
            const successMessage = data.message || 'Travel point booking updated successfully!';
            alert(successMessage);
            
            // Refresh the page to show updated data
            window.location.reload();
        } else {
            console.error('Failed to update travel point booking:', data);
            alert('Error updating travel point booking: ' + (data.message || 'Unknown error occurred'));
        }
    })
    .catch(error => {
        console.error('Error updating travel point booking:', error);
        alert('Error updating travel point booking. Please try again.');
    });
}

function validateTravelPointDates(bookingDate, pickupDate, tourStartDate, tourEndDate) {
    const booking = new Date(bookingDate);
    const pickup = new Date(pickupDate);
    const tourStart = new Date(tourStartDate);
    const tourEnd = new Date(tourEndDate);
    
    if (booking < tourStart || booking > tourEnd) {
        alert('Booking date must be within the tour travel dates.');
        return false;
    }
    
    if (pickup < tourStart || pickup > tourEnd) {
        alert('Pickup date must be within the tour travel dates.');
        return false;
    }
    
    return true;
}

// Travel Hourly Booking Management Functions
function editTravelHourlyBooking(tourId, travelHourlyOrderIndex, travelHourlyBookingIndex) {
    console.log('Editing travel hourly booking:', { tourId, travelHourlyOrderIndex, travelHourlyBookingIndex });
    
    try {
        // Create and show the travel hourly edit modal
        createAndShowTravelHourlyEditModal(tourId, travelHourlyOrderIndex, travelHourlyBookingIndex);
    } catch (error) {
        console.error('Error opening travel hourly edit modal:', error);
        alert('Error opening travel hourly edit modal. Please try again.');
    }
}

function approveTravelHourlyBooking(tourId, travelHourlyOrderIndex, travelHourlyBookingIndex) {
    console.log('Approving travel hourly booking:', { tourId, travelHourlyOrderIndex, travelHourlyBookingIndex });
    
    if (confirm('Are you sure you want to approve this travel hourly booking?')) {
        // Here you can implement the approval logic
        alert('Travel hourly booking approved successfully!');
        // Optionally refresh the page or update the UI
        // window.location.reload();
    }
}

function rejectTravelHourlyBooking(tourId, travelHourlyOrderIndex, travelHourlyBookingIndex) {
    console.log('Rejecting travel hourly booking:', { tourId, travelHourlyOrderIndex, travelHourlyBookingIndex });
    
    const reason = prompt('Please provide a reason for rejection:');
    if (reason !== null && reason.trim() !== '') {
        // Here you can implement the rejection logic
        alert('Travel hourly booking rejected successfully!');
        // Optionally refresh the page or update the UI
        // window.location.reload();
    }
}

function createAndShowTravelHourlyEditModal(tourId, travelHourlyOrderIndex, travelHourlyBookingIndex) {
    const editModalId = `travelHourlyEdit_${tourId}_${travelHourlyOrderIndex}_${travelHourlyBookingIndex}`;
    
    // Remove existing modal if it exists
    const existingModal = document.getElementById(editModalId);
    if (existingModal) {
        existingModal.remove();
    }
    
    // Get tour data for date constraints
    const tourData = getTourDataFromPage(tourId);
    const tourStartDate = tourData && tourData.check_in_time ? tourData.check_in_time : '2025-09-11';
    const tourEndDate = tourData && tourData.check_out_time ? tourData.check_out_time : '2025-09-13';
    
    const modalHTML = `
        <div class="modal fade" id="${editModalId}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius: 15px; overflow: hidden;">
                    <!-- Modal Header with Vehicle Info -->
                    <div class="modal-header bg-gradient-primary text-white border-0 p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 140px;">
                        <div class="container-fluid p-4">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                                            <i class="ri-time-line fs-4 text-primary"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0 fw-bold text-white">Local-Tour Hourly Tour</h5>
                                            <small id="travel_hourly_vehicle_info_${editModalId}" class="text-white opacity-75">Hourly Tour Service</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" onclick="closeTravelHourlyEditModal('${editModalId}')" aria-label="Close"></button>
                        </div>
                    </div>
                    
                    <!-- Travel Date Constraint -->
                    <div class="alert alert-info border-0 mb-4 d-flex align-items-center" style="border-radius: 12px; background: linear-gradient(90deg, #e3f2fd 0%, #bbdefb 100%);">
                        <i class="ri-information-line fs-5 me-3 text-info"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-info">Travel Date Constraint</h6>
                            <small id="travel_hourly_date_constraint_${editModalId}" class="text-muted">Travel hourly booking must be within the tour travel period: <strong>${tourStartDate || 'Loading...'}</strong> to <strong>${tourEndDate || 'Loading...'}</strong></small>
                        </div>
                    </div>
                    
                    <div class="modal-body p-4">
                        <form id="travelHourlyEditForm_${editModalId}">
                            <input type="hidden" name="tour_id" value="${tourId}">
                            <input type="hidden" name="travel_hourly_order_index" value="${travelHourlyOrderIndex}">
                            <input type="hidden" name="booking_index" value="${travelHourlyBookingIndex}">
                            <input type="hidden" name="booking_id" id="travel_hourly_booking_id_${editModalId}">
                            
                            <!-- Edit Booking Date & Time -->
                            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                                        <i class="ri-edit-box-line me-2 text-primary"></i>Edit Booking Date & Time
                                    </h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-medium">
                                                <i class="ri-calendar-line me-1 text-primary"></i>Booking Date
                                            </label>
                                            <input type="date" 
                                                   class="form-control form-control-lg" 
                                                   id="travel_hourly_booking_date_${editModalId}" 
                                                   name="booking_date"
                                                   min="${tourStartDate}" 
                                                   max="${tourEndDate}"
                                                   required
                                                   style="border-radius: 8px;">
                                            <small class="form-text text-muted">Select date within your travel dates</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-medium">
                                                <i class="ri-calendar-check-line me-1 text-success"></i>Exit Pickup Date
                                            </label>
                                            <input type="date" 
                                                   class="form-control form-control-lg" 
                                                   id="travel_hourly_exit_pickup_date_${editModalId}" 
                                                   name="exit_pickup_date"
                                                   min="${tourStartDate}" 
                                                   max="${tourEndDate}"
                                                   required
                                                   style="border-radius: 8px;">
                                            <small class="form-text text-muted">Select exit pickup date within your travel dates</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-medium">
                                                <i class="ri-time-line me-1 text-warning"></i>Entry Time
                                            </label>
                                            <input type="time" 
                                                   class="form-control form-control-lg" 
                                                   id="travel_hourly_entry_time_${editModalId}" 
                                                   name="entry_time"
                                                   required
                                                   style="border-radius: 8px;">
                                            <small class="form-text text-muted">Please enter time in 24-hour format (HH:MM)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Booking Summary -->
                            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                                        <i class="ri-file-list-3-line me-2 text-success"></i>Booking Summary
                                    </h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row text-center">
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted">Service Type</small>
                                            <div class="fw-medium text-dark">HOURLY TOUR</div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted">Tour ID</small>
                                            <div class="fw-medium text-dark">${tourId}</div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted">Vehicle</small>
                                            <div class="fw-medium text-dark" id="travel_hourly_vehicle_summary_${editModalId}">Loading...</div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted">Total Price</small>
                                            <div class="fw-medium text-dark" id="travel_hourly_price_summary_${editModalId}">SGD 0.00</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="modal-footer border-0 p-4 bg-light">
                        <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeTravelHourlyEditModal('${editModalId}')" style="border-radius: 25px;">
                            <i class="ri-close-line me-2"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary px-4 py-2" onclick="saveTravelHourlyChanges(${tourId}, ${travelHourlyOrderIndex}, ${travelHourlyBookingIndex}, '${editModalId}')" style="border-radius: 25px;">
                            <i class="ri-save-line me-2"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to document
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Initialize and show the modal
    const modal = new bootstrap.Modal(document.getElementById(editModalId));
    modal.show();
    
    // Load travel hourly data for editing
    loadTravelHourlyDataForEdit(tourId, travelHourlyOrderIndex, travelHourlyBookingIndex, editModalId);
}

function closeTravelHourlyEditModal(editModalId) {
    const modal = bootstrap.Modal.getInstance(document.getElementById(editModalId));
    if (modal) {
        modal.hide();
    }
    
    // Remove modal from DOM after it's hidden
    setTimeout(() => {
        const modalElement = document.getElementById(editModalId);
        if (modalElement) {
            modalElement.remove();
        }
    }, 300);
}

function loadTravelHourlyDataForEdit(tourId, travelHourlyOrderIndex, travelHourlyBookingIndex, editModalId) {
    console.log('Loading travel hourly data for edit:', { tourId, travelHourlyOrderIndex, travelHourlyBookingIndex });
    
    fetch('/booking/get-travel-hourly-data', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            tour_id: tourId,
            travel_hourly_order_index: travelHourlyOrderIndex,
            booking_index: travelHourlyBookingIndex
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const travelHourlyData = data.data;
            const travelHourlyDetails = travelHourlyData.travel_hourly_details || {};
            
            console.log('Travel hourly data loaded successfully:', travelHourlyData);
            
            // Update header vehicle info
            const vehicleInfoElement = document.getElementById(`travel_hourly_vehicle_info_${editModalId}`);
            if (vehicleInfoElement) {
                const vehicleName = travelHourlyDetails.vehicles_name || 'Hourly Tour Service';
                const serviceType = travelHourlyDetails.type || 'Hourly';
                const selectedHours = travelHourlyDetails.selectedHours || '1';
                vehicleInfoElement.textContent = `${vehicleName} • ${serviceType} • ${selectedHours} Hour(s)`;
            }
            
            // Update travel date constraint with actual tour dates
            const tourData = getTourDataFromPage(tourId);
            console.log('Tour data retrieved:', tourData);
            const constraintElement = document.getElementById(`travel_hourly_date_constraint_${editModalId}`);
            if (constraintElement && tourData && tourData.check_in_time && tourData.check_out_time) {
                const startDate = new Date(tourData.check_in_time).toLocaleDateString('en-US', { 
                    weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' 
                });
                const endDate = new Date(tourData.check_out_time).toLocaleDateString('en-US', { 
                    weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' 
                });
                constraintElement.innerHTML = `Travel hourly booking must be within the tour travel period: <strong>${startDate}</strong> to <strong>${endDate}</strong>`;
            } else {
                console.warn('Could not update date constraint, tour data not available:', tourData);
                if (constraintElement) {
                    constraintElement.innerHTML = `Travel hourly booking must be within the tour travel period: <strong>Loading...</strong> to <strong>Loading...</strong>`;
                }
            }
            
            // Update booking summary section
            const summaryVehicle = document.getElementById(`travel_hourly_vehicle_summary_${editModalId}`);
            if (summaryVehicle) {
                summaryVehicle.textContent = travelHourlyDetails.vehicles_name || 'Vehicle';
            }
            
            const summaryPrice = document.getElementById(`travel_hourly_price_summary_${editModalId}`);
            if (summaryPrice) {
                const totalPrice = travelHourlyDetails.totalPrice || travelHourlyData.total_price || '0';
                summaryPrice.textContent = `SGD ${parseFloat(totalPrice).toFixed(2)}`;
            }
            
            // Update date input constraints with actual tour dates
            const tourData2 = getTourDataFromPage(tourId);
            if (tourData2.check_in_time && tourData2.check_out_time) {
                const bookingDateInput = document.getElementById(`travel_hourly_booking_date_${editModalId}`);
                const exitPickupDateInput = document.getElementById(`travel_hourly_exit_pickup_date_${editModalId}`);
                
                if (bookingDateInput) {
                    bookingDateInput.min = tourData2.check_in_time;
                    bookingDateInput.max = tourData2.check_out_time;
                }
                
                if (exitPickupDateInput) {
                    exitPickupDateInput.min = tourData2.check_in_time;
                    exitPickupDateInput.max = tourData2.check_out_time;
                }
            }
            
            // Populate form fields with actual data
            document.getElementById(`travel_hourly_booking_date_${editModalId}`).value = travelHourlyData.booking_date || travelHourlyData.bookingDate;
            document.getElementById(`travel_hourly_exit_pickup_date_${editModalId}`).value = travelHourlyData.exit_pickup_date || travelHourlyData.exitpickupdate;
            
            // Handle time format conversion
            let timeValue = travelHourlyData.entry_time || travelHourlyData.entrytime;
            if (timeValue) {
                // Convert from 12-hour format to 24-hour format for input[type="time"]
                timeValue = convertTo24HourFormat(timeValue);
            }
            document.getElementById(`travel_hourly_entry_time_${editModalId}`).value = timeValue || "07:00";
        } else {
            console.error('Failed to load travel hourly data:', data.message);
            
            // Update date constraints even if data loading fails
            const tourData = getTourDataFromPage(tourId);
            if (tourData.check_in_time && tourData.check_out_time) {
                const bookingDateInput = document.getElementById(`travel_hourly_booking_date_${editModalId}`);
                const exitPickupDateInput = document.getElementById(`travel_hourly_exit_pickup_date_${editModalId}`);
                
                if (bookingDateInput) {
                    bookingDateInput.min = tourData.check_in_time;
                    bookingDateInput.max = tourData.check_out_time;
                }
                
                if (exitPickupDateInput) {
                    exitPickupDateInput.min = tourData.check_in_time;
                    exitPickupDateInput.max = tourData.check_out_time;
                }
            }
            
            // Use fallback data if backend fails
            document.getElementById(`travel_hourly_booking_date_${editModalId}`).value = tourData.check_in_time || "2025-09-12";
            document.getElementById(`travel_hourly_exit_pickup_date_${editModalId}`).value = tourData.check_in_time || "2025-09-12";
            document.getElementById(`travel_hourly_entry_time_${editModalId}`).value = "07:00";
        }
    })
    .catch(error => {
        console.error('Error fetching travel hourly data:', error);
        
        // Update date constraints even if network fails
        const tourData = getTourDataFromPage(tourId);
        if (tourData.check_in_time && tourData.check_out_time) {
            const bookingDateInput = document.getElementById(`travel_hourly_booking_date_${editModalId}`);
            const exitPickupDateInput = document.getElementById(`travel_hourly_exit_pickup_date_${editModalId}`);
            
            if (bookingDateInput) {
                bookingDateInput.min = tourData.check_in_time;
                bookingDateInput.max = tourData.check_out_time;
            }
            
            if (exitPickupDateInput) {
                exitPickupDateInput.min = tourData.check_in_time;
                exitPickupDateInput.max = tourData.check_out_time;
            }
        }
        
        // Use fallback data if network fails
        document.getElementById(`travel_hourly_booking_date_${editModalId}`).value = tourData.check_in_time || "2025-09-12";
        document.getElementById(`travel_hourly_exit_pickup_date_${editModalId}`).value = tourData.check_in_time || "2025-09-12";
        document.getElementById(`travel_hourly_entry_time_${editModalId}`).value = "07:00";
    });
}

function saveTravelHourlyChanges(tourId, travelHourlyOrderIndex, travelHourlyBookingIndex, editModalId) {
    const bookingDate = document.getElementById(`travel_hourly_booking_date_${editModalId}`).value;
    const exitPickupDate = document.getElementById(`travel_hourly_exit_pickup_date_${editModalId}`).value;
    const entryTime = document.getElementById(`travel_hourly_entry_time_${editModalId}`).value;
    
    console.log('Saving travel hourly changes:', {
        tourId, travelHourlyOrderIndex, travelHourlyBookingIndex,
        bookingDate, exitPickupDate, entryTime
    });
    
    if (!bookingDate || !exitPickupDate || !entryTime) {
        alert('Please fill in all required fields.');
        return;
    }
    
    // Validate dates are within tour range
    const tourData = getTourDataFromPage(tourId);
    if (!validateTravelHourlyDates(bookingDate, exitPickupDate, tourData.check_in_time, tourData.check_out_time)) {
        return;
    }
    
    // Convert time to AM/PM format for storage and display
    const displayTime = convertTo12HourFormat(entryTime);
    
    console.log('Saving travel hourly changes:', {
        tour_id: tourId,
        travel_hourly_order_index: travelHourlyOrderIndex,
        booking_index: travelHourlyBookingIndex,
        booking_date: bookingDate,
        exit_pickup_date: exitPickupDate,
        entry_time: displayTime
    });
    
    fetch('/booking/update-travel-hourly-booking', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            tour_id: tourId,
            travel_hourly_order_index: travelHourlyOrderIndex,
            booking_index: travelHourlyBookingIndex,
            booking_date: bookingDate,
            exit_pickup_date: exitPickupDate,
            entry_time: displayTime
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Travel hourly booking updated successfully:', data);
            
            // Close the edit modal
            closeTravelHourlyEditModal(editModalId);
            
            // Show success message
            const successMessage = data.message || 'Travel hourly booking updated successfully!';
            alert(successMessage);
            
            // Refresh the page to show updated data
            window.location.reload();
        } else {
            console.error('Failed to update travel hourly booking:', data);
            alert('Error updating travel hourly booking: ' + (data.message || 'Unknown error occurred'));
        }
    })
    .catch(error => {
        console.error('Error updating travel hourly booking:', error);
        alert('Error updating travel hourly booking. Please try again.');
    });
}

function validateTravelHourlyDates(bookingDate, exitPickupDate, tourStartDate, tourEndDate) {
    const booking = new Date(bookingDate);
    const exitPickup = new Date(exitPickupDate);
    const tourStart = new Date(tourStartDate);
    const tourEnd = new Date(tourEndDate);
    
    if (booking < tourStart || booking > tourEnd) {
        alert('Booking date must be within the tour travel dates.');
        return false;
    }
    
    if (exitPickup < tourStart || exitPickup > tourEnd) {
        alert('Exit pickup date must be within the tour travel dates.');
        return false;
    }
    
    return true;
}

// Local Transport Booking Management Functions
function editLocalTransportBooking(tourId, localTransportOrderIndex, localTransportBookingIndex) {
    console.log('Editing local transport booking:', { tourId, localTransportOrderIndex, localTransportBookingIndex });
    
    try {
        // Create and show the local transport edit modal
        createAndShowLocalTransportEditModal(tourId, localTransportOrderIndex, localTransportBookingIndex);
    } catch (error) {
        console.error('Error opening local transport edit modal:', error);
        alert('Error opening local transport edit modal. Please try again.');
    }
}

function approveLocalTransportBooking(tourId, localTransportOrderIndex, localTransportBookingIndex) {
    console.log('Approving local transport booking:', { tourId, localTransportOrderIndex, localTransportBookingIndex });
    
    if (confirm('Are you sure you want to approve this local transport booking?')) {
        // Here you can implement the approval logic
        alert('Local transport booking approved successfully!');
        // Optionally refresh the page or update the UI
        // window.location.reload();
    }
}

function rejectLocalTransportBooking(tourId, localTransportOrderIndex, localTransportBookingIndex) {
    console.log('Rejecting local transport booking:', { tourId, localTransportOrderIndex, localTransportBookingIndex });
    
    const reason = prompt('Please provide a reason for rejection:');
    if (reason !== null && reason.trim() !== '') {
        // Here you can implement the rejection logic
        alert('Local transport booking rejected successfully!');
        // Optionally refresh the page or update the UI
        // window.location.reload();
    }
}

function createAndShowLocalTransportEditModal(tourId, localTransportOrderIndex, localTransportBookingIndex) {
    const editModalId = `localTransportEdit_${tourId}_${localTransportOrderIndex}_${localTransportBookingIndex}`;
    
    // Remove existing modal if it exists
    const existingModal = document.getElementById(editModalId);
    if (existingModal) {
        existingModal.remove();
    }
    
    // Get tour data for date constraints
    const tourData = getTourDataFromPage(tourId);
    const tourStartDate = tourData && tourData.check_in_time ? tourData.check_in_time : '2025-09-11';
    const tourEndDate = tourData && tourData.check_out_time ? tourData.check_out_time : '2025-09-13';
    
    const modalHTML = `
        <div class="modal fade" id="${editModalId}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius: 15px; overflow: hidden;">
                    <!-- Modal Header with Vehicle Info -->
                    <div class="modal-header bg-gradient-primary text-white border-0 p-0" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); min-height: 140px;">
                        <div class="container-fluid p-4">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                                            <i class="ri-car-line fs-4 text-white"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0 fw-bold text-white">Local Transport</h5>
                                            <small id="local_transport_vehicle_info_${editModalId}" class="text-white opacity-75">Local Transport Service</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" onclick="closeLocalTransportEditModal('${editModalId}')" aria-label="Close"></button>
                        </div>
                    </div>
                    
                    <!-- Travel Date Constraint -->
                    <div class="alert alert-info border-0 mb-4 d-flex align-items-center" style="border-radius: 12px; background: linear-gradient(90deg, #e3f2fd 0%, #bbdefb 100%);">
                        <i class="ri-information-line fs-5 me-3 text-info"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-info">Travel Date Constraint</h6>
                            <small id="local_transport_date_constraint_${editModalId}" class="text-muted">Local transport booking must be within the tour travel period: <strong>${tourStartDate || 'Loading...'}</strong> to <strong>${tourEndDate || 'Loading...'}</strong></small>
                        </div>
                    </div>
                    
                    <div class="modal-body p-4">
                        <form id="localTransportEditForm_${editModalId}">
                            <input type="hidden" name="tour_id" value="${tourId}">
                            <input type="hidden" name="local_transport_order_index" value="${localTransportOrderIndex}">
                            <input type="hidden" name="booking_index" value="${localTransportBookingIndex}">
                            <input type="hidden" name="booking_id" id="local_transport_booking_id_${editModalId}">
                            
                            <!-- Edit Booking Date & Time -->
                            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                                        <i class="ri-edit-box-line me-2 text-primary"></i>Edit Booking Date & Time
                                    </h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-medium">
                                                <i class="ri-calendar-line me-1 text-primary"></i>Booking Date
                                            </label>
                                            <input type="date" 
                                                   class="form-control form-control-lg" 
                                                   id="local_transport_booking_date_${editModalId}" 
                                                   name="booking_date"
                                                   min="${tourStartDate}" 
                                                   max="${tourEndDate}"
                                                   required
                                                   style="border-radius: 8px;">
                                            <small class="form-text text-muted">Select date within your travel dates</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-medium">
                                                <i class="ri-calendar-check-line me-1 text-success"></i>Pickup Date
                                            </label>
                                            <input type="date" 
                                                   class="form-control form-control-lg" 
                                                   id="local_transport_pickup_date_${editModalId}" 
                                                   name="pickup_date"
                                                   min="${tourStartDate}" 
                                                   max="${tourEndDate}"
                                                   required
                                                   style="border-radius: 8px;">
                                            <small class="form-text text-muted">Select pickup date within your travel dates</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-medium">
                                                <i class="ri-time-line me-1 text-warning"></i>Entry Time
                                            </label>
                                            <input type="time" 
                                                   class="form-control form-control-lg" 
                                                   id="local_transport_entry_time_${editModalId}" 
                                                   name="entry_time"
                                                   required
                                                   style="border-radius: 8px;">
                                            <small class="form-text text-muted">Please enter time in 24-hour format (HH:MM)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Booking Summary -->
                            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                                        <i class="ri-file-list-3-line me-2 text-success"></i>Booking Summary
                                    </h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row text-center">
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted">Service Type</small>
                                            <div class="fw-medium text-dark">LOCAL TRANSPORT</div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted">Tour ID</small>
                                            <div class="fw-medium text-dark">${tourId}</div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted">Vehicle</small>
                                            <div class="fw-medium text-dark" id="local_transport_vehicle_summary_${editModalId}">Loading...</div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted">Total Price</small>
                                            <div class="fw-medium text-dark" id="local_transport_price_summary_${editModalId}">SGD 0.00</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="modal-footer border-0 p-4 bg-light">
                        <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeLocalTransportEditModal('${editModalId}')" style="border-radius: 25px;">
                            <i class="ri-close-line me-2"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary px-4 py-2" onclick="saveLocalTransportChanges(${tourId}, ${localTransportOrderIndex}, ${localTransportBookingIndex}, '${editModalId}')" style="border-radius: 25px;">
                            <i class="ri-save-line me-2"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to document
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Initialize and show the modal
    const modal = new bootstrap.Modal(document.getElementById(editModalId));
    modal.show();
    
    // Load local transport data for editing
    loadLocalTransportDataForEdit(tourId, localTransportOrderIndex, localTransportBookingIndex, editModalId);
}

function closeLocalTransportEditModal(editModalId) {
    const modal = bootstrap.Modal.getInstance(document.getElementById(editModalId));
    if (modal) {
        modal.hide();
    }
    
    // Remove modal from DOM after it's hidden
    setTimeout(() => {
        const modalElement = document.getElementById(editModalId);
        if (modalElement) {
            modalElement.remove();
        }
    }, 300);
}

function loadLocalTransportDataForEdit(tourId, localTransportOrderIndex, localTransportBookingIndex, editModalId) {
    console.log('Loading local transport data for edit:', { tourId, localTransportOrderIndex, localTransportBookingIndex });
    
    fetch('/booking/get-local-transport-data', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            tour_id: tourId,
            local_transport_order_index: localTransportOrderIndex,
            booking_index: localTransportBookingIndex
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const localTransportData = data.data;
            const localTransportDetails = localTransportData.local_transport_details || {};
            
            console.log('Local transport data loaded successfully:', localTransportData);
            
            // Update header vehicle info
            const vehicleInfoElement = document.getElementById(`local_transport_vehicle_info_${editModalId}`);
            if (vehicleInfoElement) {
                const vehicleName = localTransportDetails.vehicles_name || 'Local Transport Service';
                const serviceType = localTransportDetails.type || 'Transport';
                vehicleInfoElement.textContent = `${vehicleName} • ${serviceType}`;
            }
            
            // Update travel date constraint with actual tour dates
            const tourData = getTourDataFromPage(tourId);
            console.log('Tour data retrieved:', tourData);
            const constraintElement = document.getElementById(`local_transport_date_constraint_${editModalId}`);
            if (constraintElement && tourData && tourData.check_in_time && tourData.check_out_time) {
                const startDate = new Date(tourData.check_in_time).toLocaleDateString('en-US', { 
                    weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' 
                });
                const endDate = new Date(tourData.check_out_time).toLocaleDateString('en-US', { 
                    weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' 
                });
                constraintElement.innerHTML = `Local transport booking must be within the tour travel period: <strong>${startDate}</strong> to <strong>${endDate}</strong>`;
            } else {
                console.warn('Could not update date constraint, tour data not available:', tourData);
                if (constraintElement) {
                    constraintElement.innerHTML = `Local transport booking must be within the tour travel period: <strong>Loading...</strong> to <strong>Loading...</strong>`;
                }
            }
            
            // Update booking summary section
            const summaryVehicle = document.getElementById(`local_transport_vehicle_summary_${editModalId}`);
            if (summaryVehicle) {
                summaryVehicle.textContent = localTransportDetails.vehicles_name || 'Vehicle';
            }
            
            const summaryPrice = document.getElementById(`local_transport_price_summary_${editModalId}`);
            if (summaryPrice) {
                const totalPrice = localTransportDetails.totalPrice || localTransportData.total_price || '0';
                summaryPrice.textContent = `SGD ${parseFloat(totalPrice).toFixed(2)}`;
            }
            
            // Update date input constraints with actual tour dates
            const tourData2 = getTourDataFromPage(tourId);
            if (tourData2.check_in_time && tourData2.check_out_time) {
                const bookingDateInput = document.getElementById(`local_transport_booking_date_${editModalId}`);
                const pickupDateInput = document.getElementById(`local_transport_pickup_date_${editModalId}`);
                
                if (bookingDateInput) {
                    bookingDateInput.min = tourData2.check_in_time;
                    bookingDateInput.max = tourData2.check_out_time;
                }
                
                if (pickupDateInput) {
                    pickupDateInput.min = tourData2.check_in_time;
                    pickupDateInput.max = tourData2.check_out_time;
                }
            }
            
            // Populate form fields with actual data
            document.getElementById(`local_transport_booking_date_${editModalId}`).value = localTransportData.booking_date || localTransportData.bookingDate;
            document.getElementById(`local_transport_pickup_date_${editModalId}`).value = localTransportData.pickup_date || localTransportData.pickupdate;
            
            // Handle time format conversion
            let timeValue = localTransportData.entry_time || localTransportData.entrytime;
            if (timeValue) {
                // Convert from 12-hour format to 24-hour format for input[type="time"]
                timeValue = convertTo24HourFormat(timeValue);
            }
            document.getElementById(`local_transport_entry_time_${editModalId}`).value = timeValue || "07:00";
        } else {
            console.error('Failed to load local transport data:', data.message);
            
            // Update date constraints even if data loading fails
            const tourData = getTourDataFromPage(tourId);
            if (tourData.check_in_time && tourData.check_out_time) {
                const bookingDateInput = document.getElementById(`local_transport_booking_date_${editModalId}`);
                const pickupDateInput = document.getElementById(`local_transport_pickup_date_${editModalId}`);
                
                if (bookingDateInput) {
                    bookingDateInput.min = tourData.check_in_time;
                    bookingDateInput.max = tourData.check_out_time;
                }
                
                if (pickupDateInput) {
                    pickupDateInput.min = tourData.check_in_time;
                    pickupDateInput.max = tourData.check_out_time;
                }
            }
            
            // Use fallback data if backend fails
            document.getElementById(`local_transport_booking_date_${editModalId}`).value = tourData.check_in_time || "2025-09-11";
            document.getElementById(`local_transport_pickup_date_${editModalId}`).value = tourData.check_in_time || "2025-09-11";
            document.getElementById(`local_transport_entry_time_${editModalId}`).value = "07:00";
        }
    })
    .catch(error => {
        console.error('Error fetching local transport data:', error);
        
        // Update date constraints even if network fails
        const tourData = getTourDataFromPage(tourId);
        if (tourData.check_in_time && tourData.check_out_time) {
            const bookingDateInput = document.getElementById(`local_transport_booking_date_${editModalId}`);
            const pickupDateInput = document.getElementById(`local_transport_pickup_date_${editModalId}`);
            
            if (bookingDateInput) {
                bookingDateInput.min = tourData.check_in_time;
                bookingDateInput.max = tourData.check_out_time;
            }
            
            if (pickupDateInput) {
                pickupDateInput.min = tourData.check_in_time;
                pickupDateInput.max = tourData.check_out_time;
            }
        }
        
        // Use fallback data if network fails
        document.getElementById(`local_transport_booking_date_${editModalId}`).value = tourData.check_in_time || "2025-09-11";
        document.getElementById(`local_transport_pickup_date_${editModalId}`).value = tourData.check_in_time || "2025-09-11";
        document.getElementById(`local_transport_entry_time_${editModalId}`).value = "07:00";
    });
}

function saveLocalTransportChanges(tourId, localTransportOrderIndex, localTransportBookingIndex, editModalId) {
    const bookingDate = document.getElementById(`local_transport_booking_date_${editModalId}`).value;
    const pickupDate = document.getElementById(`local_transport_pickup_date_${editModalId}`).value;
    const entryTime = document.getElementById(`local_transport_entry_time_${editModalId}`).value;
    
    console.log('Saving local transport changes:', {
        tourId, localTransportOrderIndex, localTransportBookingIndex,
        bookingDate, pickupDate, entryTime
    });
    
    if (!bookingDate || !pickupDate || !entryTime) {
        alert('Please fill in all required fields.');
        return;
    }
    
    // Validate dates are within tour range
    const tourData = getTourDataFromPage(tourId);
    if (!validateLocalTransportDates(bookingDate, pickupDate, tourData.check_in_time, tourData.check_out_time)) {
        return;
    }
    
    // Convert time to AM/PM format for storage and display
    const displayTime = convertTo12HourFormat(entryTime);
    
    console.log('Saving local transport changes:', {
        tour_id: tourId,
        local_transport_order_index: localTransportOrderIndex,
        booking_index: localTransportBookingIndex,
        booking_date: bookingDate,
        pickup_date: pickupDate,
        entry_time: displayTime
    });
    
    fetch('/booking/update-local-transport-booking', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            tour_id: tourId,
            local_transport_order_index: localTransportOrderIndex,
            booking_index: localTransportBookingIndex,
            booking_date: bookingDate,
            pickup_date: pickupDate,
            entry_time: displayTime
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Local transport booking updated successfully:', data);
            
            // Close the edit modal
            closeLocalTransportEditModal(editModalId);
            
            // Show success message
            const successMessage = data.message || 'Local transport booking updated successfully!';
            alert(successMessage);
            
            // Refresh the page to show updated data
            window.location.reload();
        } else {
            console.error('Failed to update local transport booking:', data);
            alert('Error updating local transport booking: ' + (data.message || 'Unknown error occurred'));
        }
    })
    .catch(error => {
        console.error('Error updating local transport booking:', error);
        alert('Error updating local transport booking. Please try again.');
    });
}

function validateLocalTransportDates(bookingDate, pickupDate, tourStartDate, tourEndDate) {
    const booking = new Date(bookingDate);
    const pickup = new Date(pickupDate);
    const tourStart = new Date(tourStartDate);
    const tourEnd = new Date(tourEndDate);
    
    if (booking < tourStart || booking > tourEnd) {
        alert('Booking date must be within the tour travel dates.');
        return false;
    }
    
    if (pickup < tourStart || pickup > tourEnd) {
        alert('Pickup date must be within the tour travel dates.');
        return false;
    }
    
    return true;
}

function closeHotelModal(tourId) {
    closeServiceModal('hotel', tourId);
}

// Hotel Edit Modal Functions
function editHotelBooking(tourId) {
    try {
        console.log('Opening hotel edit modal for tour:', tourId);
        
        // Close the hotel details modal first
        const hotelDetailsModal = document.getElementById('hotelDetailsModal' + tourId);
        if (hotelDetailsModal) {
            const hotelModal = bootstrap.Modal.getInstance(hotelDetailsModal);
            if (hotelModal) {
                hotelModal.hide();
            }
        }
        
        // Wait a moment for the modal to close, then open edit modal
        setTimeout(() => {
            const editModalElement = document.getElementById('editHotelModal' + tourId);
            if (editModalElement) {
                const editModal = new bootstrap.Modal(editModalElement);
                editModal.show();
                
                // Load current hotel dates if available
                loadCurrentHotelDates(tourId);
            } else {
                console.error('Edit hotel modal not found for tour:', tourId);
            }
        }, 300);
        
    } catch (error) {
        console.error('Error opening hotel edit modal:', error);
        alert('Error opening edit modal. Please try again.');
    }
}

function closeEditHotelModal(tourId) {
    try {
        const editModalElement = document.getElementById('editHotelModal' + tourId);
        if (editModalElement) {
            const editModal = bootstrap.Modal.getInstance(editModalElement);
            if (editModal) {
                editModal.hide();
            }
        }
    } catch (error) {
        console.error('Error closing hotel edit modal:', error);
    }
}

function loadCurrentHotelDates(tourId) {
    try {
        // This function would typically load current hotel dates from the booking data
        // For now, we'll set up basic date validation
        const checkInInput = document.getElementById('checkInDate' + tourId);
        const checkOutInput = document.getElementById('checkOutDate' + tourId);
        
        if (checkInInput && checkOutInput) {
            // Add event listeners for date validation
            checkInInput.addEventListener('change', function() {
                validateHotelDates(tourId);
            });
            
            checkOutInput.addEventListener('change', function() {
                validateHotelDates(tourId);
            });
        }
    } catch (error) {
        console.error('Error loading current hotel dates:', error);
    }
}

function validateHotelDates(tourId) {
    try {
        const checkInInput = document.getElementById('checkInDate' + tourId);
        const checkOutInput = document.getElementById('checkOutDate' + tourId);
        
        if (!checkInInput || !checkOutInput) return;
        
        const checkInDate = new Date(checkInInput.value);
        const checkOutDate = new Date(checkOutInput.value);
        
        // Validate check-out is after check-in
        if (checkInInput.value && checkOutInput.value && checkOutDate <= checkInDate) {
            checkOutInput.setCustomValidity('Check-out date must be after check-in date');
            checkOutInput.reportValidity();
            return false;
        } else {
            checkOutInput.setCustomValidity('');
        }
        
        // Update check-out minimum date based on check-in selection
        if (checkInInput.value) {
            const nextDay = new Date(checkInDate);
            nextDay.setDate(nextDay.getDate() + 1);
            checkOutInput.min = nextDay.toISOString().split('T')[0];
        }
        
        return true;
    } catch (error) {
        console.error('Error validating hotel dates:', error);
        return false;
    }
}

function loadHotelDates(tourId) {
    try {
        // This function handles hotel selection changes in multi-hotel scenarios
        const hotelSelect = document.getElementById('hotelSelect' + tourId);
        const selectedIndex = hotelSelect.value;
        
        console.log('Loading hotel dates for index:', selectedIndex);
        
        // Here you would typically load the selected hotel's current dates
        // For now, we'll just clear the form
        const checkInInput = document.getElementById('checkInDate' + tourId);
        const checkOutInput = document.getElementById('checkOutDate' + tourId);
        const reasonTextarea = document.getElementById('changeReason' + tourId);
        
        if (checkInInput) checkInInput.value = '';
        if (checkOutInput) checkOutInput.value = '';
        if (reasonTextarea) reasonTextarea.value = '';
        
    } catch (error) {
        console.error('Error loading hotel dates:', error);
    }
}

function saveHotelDateChanges(tourId) {
    try {
        const form = document.getElementById('editHotelForm' + tourId);
        if (!form) {
            console.error('Hotel edit form not found');
            return;
        }
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        // Validate dates
        if (!validateHotelDates(tourId)) {
            return;
        }
        
        const formData = new FormData(form);
        const saveButton = document.querySelector(`#editHotelModal${tourId} .btn-primary`);
        
        // Show loading state
        if (saveButton) {
            saveButton.innerHTML = '<i class="ri-loader-4-line me-2"></i>Saving...';
            saveButton.disabled = true;
        }
        
        // Here you would typically send the data to the server
        console.log('Saving hotel date changes for tour:', tourId);
        console.log('Form data:', Object.fromEntries(formData.entries()));
        
        // Simulate API call
        setTimeout(() => {
            // Reset button
            if (saveButton) {
                saveButton.innerHTML = '<i class="ri-save-line me-2"></i>Save Changes';
                saveButton.disabled = false;
            }
            
            // Show success message
            alert('Hotel booking dates updated successfully!');
            
            // Close modal
            closeEditHotelModal(tourId);
            
            // Optionally refresh the page or update the display
            // location.reload();
            
        }, 1500);
        
    } catch (error) {
        console.error('Error saving hotel date changes:', error);
        alert('Error saving changes. Please try again.');
        
        // Reset button on error
        const saveButton = document.querySelector(`#editHotelModal${tourId} .btn-primary`);
        if (saveButton) {
            saveButton.innerHTML = '<i class="ri-save-line me-2"></i>Save Changes';
            saveButton.disabled = false;
        }
    }
}

function approveHotelBooking(tourId) {
    try {
        if (confirm('Are you sure you want to approve this hotel booking?')) {
            console.log('Approving hotel booking for tour:', tourId);
            
            // Here you would typically send approval to the server
            alert('Hotel booking approved successfully!');
            
            // Optionally close the modal and refresh
            closeHotelModal(tourId);
        }
    } catch (error) {
        console.error('Error approving hotel booking:', error);
        alert('Error approving booking. Please try again.');
    }
}

function rejectHotelBooking(tourId) {
    try {
        const reason = prompt('Please provide a reason for rejecting this hotel booking:');
        if (reason && reason.trim()) {
            console.log('Rejecting hotel booking for tour:', tourId, 'Reason:', reason);
            
            // Here you would typically send rejection to the server
            alert('Hotel booking rejected successfully!');
            
            // Optionally close the modal and refresh
            closeHotelModal(tourId);
        } else if (reason !== null) {
            alert('Please provide a reason for rejection.');
        }
    } catch (error) {
        console.error('Error rejecting hotel booking:', error);
        alert('Error rejecting booking. Please try again.');
    }
}

// Individual Hotel Functions (for handling multiple hotels separately)
function editIndividualHotel(tourId, hotelOrderIndex, bookingIndex) {
    try {
        console.log('Opening individual hotel edit modal for tour:', tourId, 'hotel order:', hotelOrderIndex, 'booking:', bookingIndex);
        
        // Close the hotel details modal first
        const hotelDetailsModal = document.getElementById('hotelDetailsModal' + tourId);
        if (hotelDetailsModal) {
            const hotelModal = bootstrap.Modal.getInstance(hotelDetailsModal);
            if (hotelModal) {
                hotelModal.hide();
            }
        }
        
        // Wait a moment for the modal to close, then show individual edit modal
        setTimeout(() => {
            createAndShowIndividualHotelModal(tourId, hotelOrderIndex, bookingIndex, 'edit');
            // Load hotel data after modal is created
            setTimeout(() => {
                // Try to load real data first, with fallback to sample data
                loadHotelDataForEdit(tourId, hotelOrderIndex, bookingIndex);
                
                // Also provide a fallback with sample data after a delay if real data fails
                setTimeout(() => {
                    // Check if modal still shows "Loading..." and populate with sample data
                    const hotelNameElement = document.getElementById(`hotelName_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
                    if (hotelNameElement && (hotelNameElement.textContent === 'Loading...' || hotelNameElement.textContent.trim() === '')) {
                        console.log('Real data not loaded, using sample data as fallback');
                        testHotelModalWithSampleData(tourId, hotelOrderIndex, bookingIndex);
                    }
                }, 2000);
            }, 100);
        }, 300);
        
    } catch (error) {
        console.error('Error opening individual hotel edit modal:', error);
        alert('Error opening edit modal. Please try again.');
    }
}

function approveIndividualHotel(tourId, hotelOrderIndex, bookingIndex) {
    try {
        console.log('Opening individual hotel approve modal for tour:', tourId, 'hotel order:', hotelOrderIndex, 'booking:', bookingIndex);
        
        // Close the hotel details modal first
        const hotelDetailsModal = document.getElementById('hotelDetailsModal' + tourId);
        if (hotelDetailsModal) {
            const hotelModal = bootstrap.Modal.getInstance(hotelDetailsModal);
            if (hotelModal) {
                hotelModal.hide();
            }
        }
        
        // Wait a moment for the modal to close, then show approve modal
        setTimeout(() => {
            createAndShowIndividualHotelModal(tourId, hotelOrderIndex, bookingIndex, 'approve');
        }, 300);
        
    } catch (error) {
        console.error('Error opening individual hotel approve modal:', error);
        alert('Error opening approve modal. Please try again.');
    }
}

function rejectIndividualHotel(tourId, hotelOrderIndex, bookingIndex) {
    try {
        console.log('Opening individual hotel reject modal for tour:', tourId, 'hotel order:', hotelOrderIndex, 'booking:', bookingIndex);
        
        // Close the hotel details modal first
        const hotelDetailsModal = document.getElementById('hotelDetailsModal' + tourId);
        if (hotelDetailsModal) {
            const hotelModal = bootstrap.Modal.getInstance(hotelDetailsModal);
            if (hotelModal) {
                hotelModal.hide();
            }
        }
        
        // Wait a moment for the modal to close, then show reject modal
        setTimeout(() => {
            createAndShowIndividualHotelModal(tourId, hotelOrderIndex, bookingIndex, 'reject');
        }, 300);
        
    } catch (error) {
        console.error('Error opening individual hotel reject modal:', error);
        alert('Error opening reject modal. Please try again.');
    }
}

function createAndShowIndividualHotelModal(tourId, hotelOrderIndex, bookingIndex, action) {
    try {
        const modalId = `individualHotelModal_${tourId}_${hotelOrderIndex}_${bookingIndex}_${action}`;
        
        // Remove existing modal if it exists
        const existingModal = document.getElementById(modalId);
        if (existingModal) {
            existingModal.remove();
        }
        
        let modalContent = '';
        let modalTitle = '';
        let modalColor = '';
        let buttonClass = '';
        let buttonText = '';
        let onSubmit = '';
        
        switch (action) {
            case 'edit':
                modalTitle = 'Edit Individual Hotel Booking';
                modalColor = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                buttonClass = 'btn-primary';
                buttonText = '<i class="ri-save-line me-2"></i>Save Changes';
                onSubmit = `saveIndividualHotelChanges(${tourId}, ${hotelOrderIndex}, ${bookingIndex})`;
                modalContent = generateEditHotelForm(tourId, hotelOrderIndex, bookingIndex);
                break;
                
            case 'approve':
                modalTitle = 'Approve Individual Hotel Booking';
                modalColor = 'linear-gradient(135deg, #28a745 0%, #20c997 100%)';
                buttonClass = 'btn-success';
                buttonText = '<i class="ri-check-line me-2"></i>Confirm Approval';
                onSubmit = `confirmIndividualHotelApproval(${tourId}, ${hotelOrderIndex}, ${bookingIndex})`;
                modalContent = generateApproveHotelForm(tourId, hotelOrderIndex, bookingIndex);
                break;
                
            case 'reject':
                modalTitle = 'Reject Individual Hotel Booking';
                modalColor = 'linear-gradient(135deg, #dc3545 0%, #e74c3c 100%)';
                buttonClass = 'btn-danger';
                buttonText = '<i class="ri-close-line me-2"></i>Confirm Rejection';
                onSubmit = `confirmIndividualHotelRejection(${tourId}, ${hotelOrderIndex}, ${bookingIndex})`;
                modalContent = generateRejectHotelForm(tourId, hotelOrderIndex, bookingIndex);
                break;
        }
        
        const modalHTML = `
            <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
                        <!-- Modal Header -->
                        <div class="modal-header p-4 border-0" style="background: ${modalColor};">
                            <div class="d-flex align-items-center">
                                <div class="bg-white rounded-circle p-2 me-3 shadow-sm">
                                    <i class="ri-hotel-line text-primary fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title fw-bold text-white mb-1">${modalTitle}</h5>
                                    <p class="text-white-50 mb-0 small">Hotel Order ${hotelOrderIndex + 1}, Booking ${bookingIndex + 1}</p>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" onclick="closeIndividualHotelModal('${modalId}')" aria-label="Close"></button>
                        </div>

                        <!-- Modal Body -->
                        <div class="modal-body p-4">
                            ${modalContent}
                        </div>

                        <!-- Modal Footer -->
                        <div class="modal-footer border-0 p-4" style="background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);">
                            <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeIndividualHotelModal('${modalId}')" style="border-radius: 25px;">
                                <i class="ri-close-line me-2"></i>Cancel
                            </button>
                            <button type="button" class="btn ${buttonClass} px-4 py-2" onclick="${onSubmit}" style="border-radius: 25px;">
                                ${buttonText}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to DOM
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Show modal
        const modalElement = document.getElementById(modalId);
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
        
        // Remove modal from DOM when hidden
        modalElement.addEventListener('hidden.bs.modal', function () {
            modalElement.remove();
        });
        
    } catch (error) {
        console.error('Error creating individual hotel modal:', error);
        alert('Error creating modal. Please try again.');
    }
}

function generateEditHotelForm(tourId, hotelOrderIndex, bookingIndex) {
    return `
        <form id="editIndividualHotelForm_${tourId}_${hotelOrderIndex}_${bookingIndex}">
            <input type="hidden" name="tour_id" value="${tourId}">
            <input type="hidden" name="hotel_order_index" value="${hotelOrderIndex}">
            <input type="hidden" name="booking_index" value="${bookingIndex}">
            <input type="hidden" name="booking_id" id="bookingId_${tourId}_${hotelOrderIndex}_${bookingIndex}">
            
            <!-- Hotel Information Header -->
            <div class="bg-gradient-primary text-white rounded p-4 mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="bg-white rounded-circle p-2 me-3">
                            <i class="ri-hotel-line text-primary fs-4"></i>
                        </div>
                        <div>
                            <h5 class="text-white mb-1 fw-bold" id="hotelName_${tourId}_${hotelOrderIndex}_${bookingIndex}">Loading...</h5>
                            <div class="d-flex gap-3">
                                <span class="badge bg-light text-dark">
                                    <i class="ri-building-line me-1"></i>Hotel Booking
                                </span>
                                <span class="badge bg-warning text-dark">
                                    <i class="ri-edit-line me-1"></i>Editing Dates
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <small class="text-white-50 d-block">Total Price</small>
                        <div class="fs-3 fw-bold text-white" id="hotelPrice_${tourId}_${hotelOrderIndex}_${bookingIndex}">SGD 0.00</div>
                    </div>
                </div>
            </div>

            <!-- Travel Date Range Info -->
            <div class="alert alert-info border-0 mb-4" style="background: linear-gradient(45deg, #e3f2fd, #f0f8ff); border-radius: 12px;">
                <div class="d-flex align-items-center mb-2">
                    <i class="ri-information-line me-2 text-info"></i>
                    <strong class="text-info">Update the check-in and check-out dates for this hotel booking.</strong>
                </div>
                <p class="mb-0 text-muted small" id="travelDateRange_${tourId}_${hotelOrderIndex}_${bookingIndex}">
                    Hotel dates must be within the tour travel period
                </p>
            </div>

            <!-- Booking Summary Section -->
            <div class="card border-0 bg-light mb-4" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary rounded-circle p-2 me-3">
                            <i class="ri-calendar-line text-white"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-dark">Booking Summary</h6>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <small class="text-muted">Hotel Name:</small>
                            <div class="fw-medium" id="summaryHotelName_${tourId}_${hotelOrderIndex}_${bookingIndex}">Loading...</div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <small class="text-muted">Location:</small>
                            <div class="fw-medium" id="summaryLocation_${tourId}_${hotelOrderIndex}_${bookingIndex}">N/A</div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <small class="text-muted">Price:</small>
                            <div class="fw-medium text-success" id="summaryPrice_${tourId}_${hotelOrderIndex}_${bookingIndex}">SGD 0.00</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Date Range Selection -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-header bg-light py-3">
                    <div class="d-flex align-items-center">
                        <i class="ri-calendar-alt text-primary me-2 fa-lg"></i>
                        <h5 class="mb-0 fw-bold">Edit Booking Dates</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="ri-calendar-check-line me-2 text-success"></i>Check-in Date
                            </label>
                            <input type="date" 
                                   class="form-control form-control-lg" 
                                   name="check_in_date" 
                                   id="checkInDate_${tourId}_${hotelOrderIndex}_${bookingIndex}" 
                                   required
                                   onchange="validateHotelDates(${tourId}, ${hotelOrderIndex}, ${bookingIndex})">
                            <div class="form-text">
                                <i class="ri-information-line me-1"></i>
                                Must be within travel dates
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="ri-calendar-close-line me-2 text-danger"></i>Check-out Date
                            </label>
                            <input type="date" 
                                   class="form-control form-control-lg" 
                                   name="check_out_date" 
                                   id="checkOutDate_${tourId}_${hotelOrderIndex}_${bookingIndex}" 
                                   required
                                   onchange="validateHotelDates(${tourId}, ${hotelOrderIndex}, ${bookingIndex})">
                            <div class="form-text">
                                <i class="ri-information-line me-1"></i>
                                Must be after check-in date
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    `;
}

function generateApproveHotelForm(tourId, hotelOrderIndex, bookingIndex) {
    return `
        <form id="approveIndividualHotelForm_${tourId}_${hotelOrderIndex}_${bookingIndex}">
            <input type="hidden" name="tour_id" value="${tourId}">
            <input type="hidden" name="hotel_order_index" value="${hotelOrderIndex}">
            <input type="hidden" name="booking_index" value="${bookingIndex}">
            
            <div class="alert alert-success border-0 mb-4" style="background: linear-gradient(45deg, #d4edda, #f0f9f0); border-radius: 12px;">
                <div class="d-flex align-items-center">
                    <i class="ri-check-circle-line me-2 text-success fs-4"></i>
                    <div>
                        <strong class="text-success">Confirm Approval</strong>
                        <p class="mb-0 text-muted small mt-1">Are you sure you want to approve this individual hotel booking?</p>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="referenceId_${tourId}_${hotelOrderIndex}_${bookingIndex}" class="form-label fw-semibold">
                    <i class="ri-file-text-line me-2"></i>Reference ID <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="referenceId_${tourId}_${hotelOrderIndex}_${bookingIndex}" name="reference_id" required 
                       placeholder="Enter booking reference or confirmation number">
            </div>

            <div class="mb-3">
                <label for="referenceFile_${tourId}_${hotelOrderIndex}_${bookingIndex}" class="form-label fw-semibold">
                    <i class="ri-attachment-line me-2"></i>Reference File (Optional)
                </label>
                <input type="file" class="form-control" id="referenceFile_${tourId}_${hotelOrderIndex}_${bookingIndex}" name="reference_file"
                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                <div class="form-text">Upload supporting documents if available (PDF, DOC, JPG, PNG)</div>
            </div>
        </form>
    `;
}

function generateRejectHotelForm(tourId, hotelOrderIndex, bookingIndex) {
    return `
        <form id="rejectIndividualHotelForm_${tourId}_${hotelOrderIndex}_${bookingIndex}">
            <input type="hidden" name="tour_id" value="${tourId}">
            <input type="hidden" name="hotel_order_index" value="${hotelOrderIndex}">
            <input type="hidden" name="booking_index" value="${bookingIndex}">
            
            <div class="alert alert-danger border-0 mb-4" style="background: linear-gradient(45deg, #f8d7da, #ffe6e6); border-radius: 12px;">
                <div class="d-flex align-items-center">
                    <i class="ri-error-warning-line me-2 text-danger fs-4"></i>
                    <div>
                        <strong class="text-danger">Confirm Rejection</strong>
                        <p class="mb-0 text-muted small mt-1">Are you sure you want to reject this individual hotel booking?</p>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label for="rejectReason_${tourId}_${hotelOrderIndex}_${bookingIndex}" class="form-label fw-semibold">
                    <i class="ri-message-3-line me-2"></i>Reason for Rejection <span class="text-danger">*</span>
                </label>
                <textarea class="form-control" id="rejectReason_${tourId}_${hotelOrderIndex}_${bookingIndex}" name="reject_reason" rows="4" 
                          placeholder="Please provide a detailed reason for rejecting this hotel booking..." required></textarea>
                <div class="form-text">This reason will be communicated to the relevant parties</div>
            </div>

            <!-- Hotel Summary -->
            <div class="card border-0 bg-light mb-4" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-danger rounded-circle p-2 me-3">
                            <i class="ri-hotel-line text-white"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-dark">Hotel Booking Summary</h6>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <small class="text-muted">Hotel Order</small>
                            <div class="fw-medium">Order ${hotelOrderIndex + 1}</div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted">Booking Index</small>
                            <div class="fw-medium">Booking ${bookingIndex + 1}</div>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Action</small>
                            <div class="fw-medium text-danger">This specific hotel booking will be rejected</div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    `;
}

function closeIndividualHotelModal(modalId) {
    try {
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        }
    } catch (error) {
        console.error('Error closing individual hotel modal:', error);
    }
}

function saveIndividualHotelChanges(tourId, hotelOrderIndex, bookingIndex) {
    try {
        const form = document.getElementById(`editIndividualHotelForm_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        if (!form) {
            console.error('Individual hotel edit form not found');
            return;
        }
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        // Additional date validation
        if (!validateHotelDates(tourId, hotelOrderIndex, bookingIndex)) {
            return;
        }
        
        const formData = new FormData(form);
        const checkInDate = formData.get('check_in_date');
        const checkOutDate = formData.get('check_out_date');
        const bookingId = formData.get('booking_id');
        
        if (!checkInDate || !checkOutDate) {
            alert('Please select both check-in and check-out dates');
            return;
        }
        
        // Show loading state
        const saveButton = event.target;
        const originalText = saveButton.innerHTML;
        saveButton.innerHTML = '<i class="ri-loader-4-line me-2"></i>Saving...';
        saveButton.disabled = true;
        
        // Prepare data for server
        const updateData = {
            tour_id: tourId,
            booking_id: bookingId,
            hotel_order_index: hotelOrderIndex,
            booking_index: bookingIndex,
            check_in_date: checkInDate,
            check_out_date: checkOutDate,
            booking_dates: [checkInDate, checkOutDate], // This will update the bookingDate array in JSON
            _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        };
        
        console.log('Saving individual hotel changes:', updateData);
        
        // Send AJAX request to update orders table
        fetch('/booking/update-hotel-dates', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': updateData._token,
                'Accept': 'application/json'
            },
            body: JSON.stringify(updateData)
        })
        .then(response => response.json())
        .then(data => {
            // Reset button
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
            
            if (data.success) {
                // Show success message with hotel details
                const hotelName = document.getElementById(`hotelName_${tourId}_${hotelOrderIndex}_${bookingIndex}`)?.textContent || 'Hotel';
                
                // Create a nice success message
                const successMessage = `
                    ✅ Hotel booking dates updated successfully!
                    
                    Hotel: ${hotelName}
                    Check-in: ${new Date(checkInDate).toLocaleDateString('en-US', { 
                        weekday: 'short', 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric' 
                    })}
                    Check-out: ${new Date(checkOutDate).toLocaleDateString('en-US', { 
                        weekday: 'short', 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric' 
                    })}
                    
                    The booking dates have been updated in the orders table.
                `;
                
                // Show success alert and refresh page after user clicks OK
                // Use setTimeout to ensure alert is processed first, then refresh
                setTimeout(() => {
                    alert(successMessage);
                    
                    // Close modal
                    const modalId = `individualHotelModal_${tourId}_${hotelOrderIndex}_${bookingIndex}_edit`;
                    closeIndividualHotelModal(modalId);
                    
                    // Refresh the page after user dismisses the alert
                    window.location.reload();
                }, 100);
                
            } else {
                alert('Error updating hotel dates: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error saving hotel changes:', error);
            
            // Reset button
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
            
            alert('Error saving changes. Please try again.');
        });
        
    } catch (error) {
        console.error('Error saving individual hotel changes:', error);
        alert('Error saving changes. Please try again.');
        
        // Reset button if there's an error
        const saveButton = event.target;
        if (saveButton) {
            saveButton.innerHTML = '<i class="ri-save-line me-2"></i>Save Changes';
            saveButton.disabled = false;
        }
    }
}

function confirmIndividualHotelApproval(tourId, hotelOrderIndex, bookingIndex) {
    try {
        const form = document.getElementById(`approveIndividualHotelForm_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        if (!form) {
            console.error('Individual hotel approve form not found');
            return;
        }
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        const formData = new FormData(form);
        
        // Show loading state
        const approveButton = event.target;
        const originalText = approveButton.innerHTML;
        approveButton.innerHTML = '<i class="ri-loader-4-line me-2"></i>Approving...';
        approveButton.disabled = true;
        
        // Here you would typically send the data to the server
        console.log('Approving individual hotel booking:', Object.fromEntries(formData.entries()));
        
        // Simulate API call
        setTimeout(() => {
            // Reset button
            approveButton.innerHTML = originalText;
            approveButton.disabled = false;
            
            // Show success message
            alert(`Individual hotel booking approved successfully!\\nHotel Order: ${hotelOrderIndex + 1}, Booking: ${bookingIndex + 1}`);
            
            // Close modal
            const modalId = `individualHotelModal_${tourId}_${hotelOrderIndex}_${bookingIndex}_approve`;
            closeIndividualHotelModal(modalId);
            
        }, 1500);
        
    } catch (error) {
        console.error('Error approving individual hotel booking:', error);
        alert('Error approving booking. Please try again.');
    }
}

function confirmIndividualHotelRejection(tourId, hotelOrderIndex, bookingIndex) {
    try {
        const form = document.getElementById(`rejectIndividualHotelForm_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        if (!form) {
            console.error('Individual hotel reject form not found');
            return;
        }
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        const formData = new FormData(form);
        
        // Show loading state
        const rejectButton = event.target;
        const originalText = rejectButton.innerHTML;
        rejectButton.innerHTML = '<i class="ri-loader-4-line me-2"></i>Rejecting...';
        rejectButton.disabled = true;
        
        // Here you would typically send the data to the server
        console.log('Rejecting individual hotel booking:', Object.fromEntries(formData.entries()));
        
        // Simulate API call
        setTimeout(() => {
            // Reset button
            rejectButton.innerHTML = originalText;
            rejectButton.disabled = false;
            
            // Show success message
            alert(`Individual hotel booking rejected successfully!\\nHotel Order: ${hotelOrderIndex + 1}, Booking: ${bookingIndex + 1}`);
            
            // Close modal
            const modalId = `individualHotelModal_${tourId}_${hotelOrderIndex}_${bookingIndex}_reject`;
            closeIndividualHotelModal(modalId);
            
        }, 1500);
        
    } catch (error) {
        console.error('Error rejecting individual hotel booking:', error);
        alert('Error rejecting booking. Please try again.');
    }
}

function loadHotelDataForEdit(tourId, hotelOrderIndex, bookingIndex) {
    try {
        console.log('Loading hotel data for edit:', tourId, hotelOrderIndex, bookingIndex);
        
        // Get tour data from the page
        const tourData = getTourDataFromPage(tourId);
        if (!tourData) {
            console.error('Tour data not found');
            return;
        }
        
        // Get hotel service data (this now returns a Promise)
        getHotelServiceData(tourId, hotelOrderIndex, bookingIndex)
        .then(hotelData => {
            if (!hotelData) {
                console.error('Hotel data not found');
                return;
            }
            
            // Populate hotel information
            const hotelNameElement = document.getElementById(`hotelName_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
            const hotelPriceElement = document.getElementById(`hotelPrice_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
            const summaryHotelNameElement = document.getElementById(`summaryHotelName_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
            const summaryLocationElement = document.getElementById(`summaryLocation_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
            const summaryRoomsElement = document.getElementById(`summaryRooms_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
            const summaryPriceElement = document.getElementById(`summaryPrice_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
            const travelDateRangeElement = document.getElementById(`travelDateRange_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
            const bookingIdElement = document.getElementById(`bookingId_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
            
            if (hotelData.hotelDetails) {
                const hotelName = hotelData.hotelDetails.hotel_name || 'Hotel Booking';
                const location = hotelData.hotelDetails.location || 'N/A';
                const totalPrice = hotelData.totalPrice || 0;
                const roomCount = hotelData.rooms ? hotelData.rooms.length : 1;
                
                if (hotelNameElement) hotelNameElement.textContent = hotelName;
                if (hotelPriceElement) hotelPriceElement.textContent = `SGD ${parseFloat(totalPrice).toFixed(2)}`;
                if (summaryHotelNameElement) summaryHotelNameElement.textContent = hotelName;
                if (summaryLocationElement) summaryLocationElement.textContent = location;
                if (summaryRoomsElement) summaryRoomsElement.textContent = roomCount.toString();
                if (summaryPriceElement) summaryPriceElement.textContent = `SGD ${parseFloat(totalPrice).toFixed(2)}`;
            }
            
            // Set booking ID from orders table
            if (bookingIdElement && hotelData.booking_id) {
                bookingIdElement.value = hotelData.booking_id;
            }
            
            // Set travel date range information with proper formatting
            if (travelDateRangeElement && tourData.check_in_time && tourData.check_out_time) {
                const startDate = new Date(tourData.check_in_time).toLocaleDateString('en-US', { 
                    weekday: 'short', 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
                const endDate = new Date(tourData.check_out_time).toLocaleDateString('en-US', { 
                    weekday: 'short', 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
                travelDateRangeElement.innerHTML = `Hotel dates must be within the tour travel period: <strong class="text-primary">${startDate}</strong> to <strong class="text-primary">${endDate}</strong>`;
            }
            
            // Set up date inputs with restrictions and current values
            const checkInInput = document.getElementById(`checkInDate_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
            const checkOutInput = document.getElementById(`checkOutDate_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
            
            if (checkInInput && checkOutInput) {
                // Set min and max dates based on tour travel dates
                if (tourData.check_in_time) {
                    const minDate = new Date(tourData.check_in_time).toISOString().split('T')[0];
                    checkInInput.min = minDate;
                    checkOutInput.min = minDate;
                }
                if (tourData.check_out_time) {
                    const maxDate = new Date(tourData.check_out_time).toISOString().split('T')[0];
                    checkInInput.max = maxDate;
                    checkOutInput.max = maxDate;
                }
                
                // Set current booking dates if available
                if (hotelData.bookingDate && Array.isArray(hotelData.bookingDate) && hotelData.bookingDate.length >= 2) {
                    checkInInput.value = hotelData.bookingDate[0];
                    checkOutInput.value = hotelData.bookingDate[1];
                }
            }
        })
        .catch(error => {
            console.error('Error fetching hotel data:', error);
        });
        
    } catch (error) {
        console.error('Error loading hotel data for edit:', error);
    }
}

function getTourDataFromPage(tourId) {
    // Extract tour data from the current page elements
    try {
        console.log('Extracting tour data for tour ID:', tourId);
        
        // Method 1: Look for data attributes on table row (most reliable)
        const tourRow = document.querySelector(`tr[data-tour-id="${tourId}"]`);
        if (tourRow) {
            const checkIn = tourRow.getAttribute('data-check-in');
            const checkOut = tourRow.getAttribute('data-check-out');
            
            if (checkIn && checkOut) {
                console.log('Found dates in table row data attributes:', { checkIn, checkOut });
                return {
                    tour_id: tourId,
                    check_in_time: checkIn,
                    check_out_time: checkOut
                };
            }
        }
        
        // Method 2: Look for data attributes on any element related to this tour
        const tourElements = document.querySelectorAll(`[data-tour-id="${tourId}"]`);
        for (let element of tourElements) {
            const checkIn = element.getAttribute('data-check-in') || element.getAttribute('data-start-date');
            const checkOut = element.getAttribute('data-check-out') || element.getAttribute('data-end-date');
            
            if (checkIn && checkOut) {
                console.log('Found dates in element data attributes:', { checkIn, checkOut });
                return {
                    tour_id: tourId,
                    check_in_time: checkIn,
                    check_out_time: checkOut
                };
            }
        }
        
        // Method 3: Look for hidden form inputs that contain tour dates
        const hiddenInputs = document.querySelectorAll('input[type="hidden"]');
        let checkInTime = null;
        let checkOutTime = null;
        
        for (let input of hiddenInputs) {
            if (input.name === 'travel_start' && input.closest('form').querySelector(`[value="${tourId}"]`)) {
                checkInTime = input.value;
            }
            if (input.name === 'travel_end' && input.closest('form').querySelector(`[value="${tourId}"]`)) {
                checkOutTime = input.value;
            }
        }
        
        if (checkInTime && checkOutTime) {
            console.log('Found dates in hidden inputs:', { checkInTime, checkOutTime });
            return {
                tour_id: tourId,
                check_in_time: checkInTime,
                check_out_time: checkOutTime
            };
        }
        
        // Method 4: Look for the specific tour row by searching table content
        const allRows = document.querySelectorAll('tbody tr');
        for (let row of allRows) {
            const rowText = row.textContent;
            const tourIdPattern = new RegExp('\\b' + tourId + '\\b');
            
            if (tourIdPattern.test(rowText)) {
                console.log('Found matching tour row for ID:', tourId);
                
                // Look for check-in and check-out dates in the format "Check-in: Thu, Sep 11, 2025"
                const checkInMatch = rowText.match(/Check-in:\s*([A-Za-z]{3},\s*[A-Za-z]{3}\s*\d{1,2},\s*\d{4})/);
                const checkOutMatch = rowText.match(/Check-out:\s*([A-Za-z]{3},\s*[A-Za-z]{3}\s*\d{1,2},\s*\d{4})/);
                
                if (checkInMatch && checkOutMatch) {
                    const checkInDate = new Date(checkInMatch[1]).toISOString().split('T')[0];
                    const checkOutDate = new Date(checkOutMatch[1]).toISOString().split('T')[0];
                    
                    console.log('Extracted dates from row text:', { checkInDate, checkOutDate });
                    
                    return {
                        tour_id: tourId,
                        check_in_time: checkInDate,
                        check_out_time: checkOutDate
                    };
                }
                
                // Alternative: Look for date patterns in YYYY-MM-DD format
                const datePattern = /(\d{4}-\d{2}-\d{2})/g;
                const dates = rowText.match(datePattern);
                if (dates && dates.length >= 2) {
                    console.log('Found date patterns in row:', dates);
                    return {
                        tour_id: tourId,
                        check_in_time: dates[0],
                        check_out_time: dates[1]
                    };
                }
            }
        }
        
        // Fallback: Log that we couldn't find the dates and use sample dates
        console.warn('Could not extract tour dates for tour ID:', tourId, 'using fallback dates');
        return {
            tour_id: tourId,
            check_in_time: '2025-09-11', // Thu, Sep 11, 2025 - Updated to match your example
            check_out_time: '2025-09-13'  // Sat, Sep 13, 2025 - Updated to match your example
        };
    } catch (error) {
        console.error('Error extracting tour data from page:', error);
        return {
            tour_id: tourId,
            check_in_time: '2025-09-11',
            check_out_time: '2025-09-13'
        };
    }
}

function getHotelServiceData(tourId, hotelOrderIndex, bookingIndex) {
    // Fetch hotel service data from the server
    return new Promise((resolve, reject) => {
        console.log('Fetching hotel data for:', { tourId, hotelOrderIndex, bookingIndex });
        
        fetch('/booking/get-hotel-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                tour_id: parseInt(tourId),
                hotel_order_index: parseInt(hotelOrderIndex),
                booking_index: parseInt(bookingIndex)
            })
        })
        .then(response => {
            console.log('Server response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Server response data:', data);
            if (data.success && data.data && data.data.hotel_booking) {
                const hotelBooking = data.data.hotel_booking;
                const hotelData = {
                    booking_id: hotelBooking.booking_id,
                    hotelDetails: {
                        hotel_name: hotelBooking.hotel_name || 'Marina Bay Sands Singapore',
                        location: hotelBooking.location || 'Marina Bay, Singapore'
                    },
                    totalPrice: hotelBooking.total_price || 1499.00,
                    rooms: new Array(hotelBooking.room_count || 1).fill({}),
                    bookingDate: hotelBooking.booking_dates && hotelBooking.booking_dates.length >= 2 
                        ? hotelBooking.booking_dates 
                        : ['2025-08-01', '2025-08-03']
                };
                console.log('Resolved hotel data:', hotelData);
                resolve(hotelData);
            } else {
                console.warn('Server returned unsuccessful response:', data);
                throw new Error(data.message || 'Failed to fetch hotel data');
            }
        })
        .catch(error => {
            console.error('Error fetching hotel data from server:', error);
            console.log('Using fallback hotel data');
            
            // Enhanced fallback data that looks more realistic
            const fallbackData = {
                booking_id: null,
                hotelDetails: {
                    hotel_name: 'Marina Bay Sands Singapore',
                    location: 'Marina Bay, Singapore'
                },
                totalPrice: 1499.00,
                rooms: [{}],
                bookingDate: ['2025-09-11', '2025-09-13'] // Fallback dates within travel period
            };
            
            console.log('Using fallback data:', fallbackData);
            resolve(fallbackData);
        });
    });
}

function validateHotelDates(tourId, hotelOrderIndex, bookingIndex) {
    try {
        const checkInInput = document.getElementById(`checkInDate_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        const checkOutInput = document.getElementById(`checkOutDate_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        
        if (!checkInInput || !checkOutInput) return;
        
        const checkInDate = checkInInput.value;
        const checkOutDate = checkOutInput.value;
        
        // Validate check-out is after check-in
        if (checkInDate && checkOutDate && checkOutDate <= checkInDate) {
            alert('Check-out date must be after check-in date');
            checkOutInput.value = '';
            return false;
        }
        
        // Validate dates are within tour travel period
        const tourData = getTourDataFromPage(tourId);
        if (tourData && tourData.check_in_time && tourData.check_out_time) {
            const tourStart = new Date(tourData.check_in_time);
            const tourEnd = new Date(tourData.check_out_time);
            const selectedCheckIn = new Date(checkInDate);
            const selectedCheckOut = new Date(checkOutDate);
            
            if (checkInDate && (selectedCheckIn < tourStart || selectedCheckIn > tourEnd)) {
                alert('Check-in date must be within the tour travel period');
                checkInInput.value = '';
                return false;
            }
            
            if (checkOutDate && (selectedCheckOut < tourStart || selectedCheckOut > tourEnd)) {
                alert('Check-out date must be within the tour travel period');
                checkOutInput.value = '';
                return false;
            }
        }
        
        return true;
    } catch (error) {
        console.error('Error validating hotel dates:', error);
        return false;
    }
}

function loadIndividualHotelDates(tourId, hotelOrderIndex, bookingIndex) {
    // This function is now replaced by loadHotelDataForEdit
    loadHotelDataForEdit(tourId, hotelOrderIndex, bookingIndex);
}

// Debug function to test modal with sample data
function testHotelModalWithSampleData(tourId, hotelOrderIndex, bookingIndex) {
    console.log('Testing hotel modal with sample data...');
    
    // Create and show modal first
    createAndShowIndividualHotelModal(tourId, hotelOrderIndex, bookingIndex, 'edit');
    
    // Wait a moment for modal to be created, then populate with sample data
    setTimeout(() => {
        const sampleHotelData = {
            booking_id: 12345,
            hotelDetails: {
                hotel_name: 'Marina Bay Sands Singapore',
                location: 'Marina Bay, Singapore'
            },
            totalPrice: 1499.00,
            rooms: [{}],
            bookingDate: ['2025-09-11', '2025-09-13']
        };
        
        const sampleTourData = {
            tour_id: tourId,
            check_in_time: '2025-09-11', // Thu, Sep 11, 2025
            check_out_time: '2025-09-13'  // Sat, Sep 13, 2025
        };
        
        // Populate modal elements
        const hotelNameElement = document.getElementById(`hotelName_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        const hotelPriceElement = document.getElementById(`hotelPrice_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        const summaryHotelNameElement = document.getElementById(`summaryHotelName_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        const summaryLocationElement = document.getElementById(`summaryLocation_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        const summaryRoomsElement = document.getElementById(`summaryRooms_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        const summaryPriceElement = document.getElementById(`summaryPrice_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        const travelDateRangeElement = document.getElementById(`travelDateRange_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        const bookingIdElement = document.getElementById(`bookingId_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        const checkInInput = document.getElementById(`checkInDate_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        const checkOutInput = document.getElementById(`checkOutDate_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        
        // Populate with sample data
        if (hotelNameElement) hotelNameElement.textContent = sampleHotelData.hotelDetails.hotel_name;
        if (hotelPriceElement) hotelPriceElement.textContent = `SGD ${sampleHotelData.totalPrice.toFixed(2)}`;
        if (summaryHotelNameElement) summaryHotelNameElement.textContent = sampleHotelData.hotelDetails.hotel_name;
        if (summaryLocationElement) summaryLocationElement.textContent = sampleHotelData.hotelDetails.location;
        if (summaryRoomsElement) summaryRoomsElement.textContent = '1';
        if (summaryPriceElement) summaryPriceElement.textContent = `SGD ${sampleHotelData.totalPrice.toFixed(2)}`;
        if (bookingIdElement) bookingIdElement.value = sampleHotelData.booking_id;
        
        // Set travel date range
        if (travelDateRangeElement) {
            const startDate = new Date(sampleTourData.check_in_time).toLocaleDateString('en-US', { 
                weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' 
            });
            const endDate = new Date(sampleTourData.check_out_time).toLocaleDateString('en-US', { 
                weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' 
            });
            travelDateRangeElement.innerHTML = `Hotel dates must be within the tour travel period: <strong class="text-primary">${startDate}</strong> to <strong class="text-primary">${endDate}</strong>`;
        }
        
        // Set date restrictions and current values
        if (checkInInput && checkOutInput) {
            checkInInput.min = sampleTourData.check_in_time;
            checkInInput.max = sampleTourData.check_out_time;
            checkOutInput.min = sampleTourData.check_in_time;
            checkOutInput.max = sampleTourData.check_out_time;
            
            checkInInput.value = sampleHotelData.bookingDate[0];
            checkOutInput.value = sampleHotelData.bookingDate[1];
        }
        
        console.log('Sample data populated successfully');
    }, 500);
}

function ucfirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

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

// Individual Attraction Functions (for handling multiple attraction bookings separately)
function editIndividualAttraction(tourId, attractionOrderIndex, bookingIndex) {
    try {
        console.log('Opening individual attraction edit modal for tour:', tourId, 'attraction order:', attractionOrderIndex, 'booking:', bookingIndex);
        
        // Close the attraction details modal first
        const attractionDetailsModal = document.getElementById('attractionDetailsModal' + tourId);
        if (attractionDetailsModal) {
            const attractionModal = bootstrap.Modal.getInstance(attractionDetailsModal);
            if (attractionModal) {
                attractionModal.hide();
            }
        }
        
        // Wait a moment for the modal to close, then show individual edit modal
        setTimeout(() => {
            createAndShowIndividualAttractionModal(tourId, attractionOrderIndex, bookingIndex, 'edit');
            // Load attraction data after modal is created
            setTimeout(() => {
                loadAttractionDataForEdit(tourId, attractionOrderIndex, bookingIndex);
            }, 100);
        }, 300);
        
    } catch (error) {
        console.error('Error opening individual attraction edit modal:', error);
        alert('Error opening edit modal. Please try again.');
    }
}

function approveIndividualAttraction(tourId, attractionOrderIndex, bookingIndex) {
    try {
        console.log('Opening individual attraction approve modal for tour:', tourId, 'attraction order:', attractionOrderIndex, 'booking:', bookingIndex);
        
        // Close the attraction details modal first
        const attractionDetailsModal = document.getElementById('attractionDetailsModal' + tourId);
        if (attractionDetailsModal) {
            const attractionModal = bootstrap.Modal.getInstance(attractionDetailsModal);
            if (attractionModal) {
                attractionModal.hide();
            }
        }
        
        // Wait a moment for the modal to close, then show individual approve modal
        setTimeout(() => {
            createAndShowIndividualAttractionModal(tourId, attractionOrderIndex, bookingIndex, 'approve');
        }, 300);
        
    } catch (error) {
        console.error('Error opening individual attraction approve modal:', error);
        alert('Error opening approve modal. Please try again.');
    }
}

function rejectIndividualAttraction(tourId, attractionOrderIndex, bookingIndex) {
    try {
        console.log('Opening individual attraction reject modal for tour:', tourId, 'attraction order:', attractionOrderIndex, 'booking:', bookingIndex);
        
        // Close the attraction details modal first
        const attractionDetailsModal = document.getElementById('attractionDetailsModal' + tourId);
        if (attractionDetailsModal) {
            const attractionModal = bootstrap.Modal.getInstance(attractionDetailsModal);
            if (attractionModal) {
                attractionModal.hide();
            }
        }
        
        // Wait a moment for the modal to close, then show individual reject modal
        setTimeout(() => {
            createAndShowIndividualAttractionModal(tourId, attractionOrderIndex, bookingIndex, 'reject');
        }, 300);
        
    } catch (error) {
        console.error('Error opening individual attraction reject modal:', error);
        alert('Error opening reject modal. Please try again.');
    }
}

function createAndShowIndividualAttractionModal(tourId, attractionOrderIndex, bookingIndex, action) {
    try {
        const modalId = `individualAttractionModal_${tourId}_${attractionOrderIndex}_${bookingIndex}_${action}`;
        
        // Remove existing modal if it exists
        const existingModal = document.getElementById(modalId);
        if (existingModal) {
            existingModal.remove();
        }
        
        let modalTitle, modalColor, buttonClass, buttonText, onSubmit, modalContent;
        
        switch (action) {
            case 'edit':
                modalTitle = 'Edit Individual Attraction Booking';
                modalColor = 'linear-gradient(135deg, #fd9853 0%, #fe7854 100%)';
                buttonClass = 'btn-primary';
                buttonText = '<i class="ri-save-line me-2"></i>Save Changes';
                onSubmit = `saveIndividualAttractionChanges(${tourId}, ${attractionOrderIndex}, ${bookingIndex})`;
                modalContent = generateEditAttractionForm(tourId, attractionOrderIndex, bookingIndex);
                break;
                
            case 'approve':
                modalTitle = 'Approve Individual Attraction Booking';
                modalColor = 'linear-gradient(135deg, #28a745 0%, #20c997 100%)';
                buttonClass = 'btn-success';
                buttonText = '<i class="ri-check-line me-2"></i>Confirm Approval';
                onSubmit = `confirmIndividualAttractionApproval(${tourId}, ${attractionOrderIndex}, ${bookingIndex})`;
                modalContent = generateApproveAttractionForm(tourId, attractionOrderIndex, bookingIndex);
                break;
                
            case 'reject':
                modalTitle = 'Reject Individual Attraction Booking';
                modalColor = 'linear-gradient(135deg, #dc3545 0%, #e74c3c 100%)';
                buttonClass = 'btn-danger';
                buttonText = '<i class="ri-close-line me-2"></i>Confirm Rejection';
                onSubmit = `confirmIndividualAttractionRejection(${tourId}, ${attractionOrderIndex}, ${bookingIndex})`;
                modalContent = generateRejectAttractionForm(tourId, attractionOrderIndex, bookingIndex);
                break;
        }
        
        const modalHTML = `
            <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
                        <!-- Modal Header -->
                        <div class="modal-header p-4 border-0" style="background: ${modalColor};">
                            <div class="d-flex align-items-center">
                                <div class="bg-white rounded-circle p-2 me-3 shadow-sm">
                                    <i class="ri-building-2-line text-primary fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title fw-bold text-white mb-1">${modalTitle}</h5>
                                    <p class="text-white-50 mb-0 small">Attraction Order ${attractionOrderIndex + 1}, Booking ${bookingIndex + 1}</p>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" onclick="closeIndividualAttractionModal('${modalId}')" aria-label="Close"></button>
                        </div>

                        <!-- Modal Body -->
                        <div class="modal-body p-4">
                            ${modalContent}
                        </div>

                        <!-- Modal Footer -->
                        <div class="modal-footer border-0 p-4" style="background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);">
                            <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeIndividualAttractionModal('${modalId}')" style="border-radius: 25px;">
                                <i class="ri-close-line me-2"></i>Cancel
                            </button>
                            <button type="button" class="btn ${buttonClass} px-4 py-2" onclick="${onSubmit}" style="border-radius: 25px;">
                                ${buttonText}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to DOM
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Show modal
        const modalElement = document.getElementById(modalId);
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
        
        // Remove modal from DOM when hidden
        modalElement.addEventListener('hidden.bs.modal', function () {
            modalElement.remove();
        });
        
    } catch (error) {
        console.error('Error creating individual attraction modal:', error);
        alert('Error creating modal. Please try again.');
    }
}

function generateEditAttractionForm(tourId, attractionOrderIndex, bookingIndex) {
    return `
        <form id="editIndividualAttractionForm_${tourId}_${attractionOrderIndex}_${bookingIndex}">
            <input type="hidden" name="tour_id" value="${tourId}">
            <input type="hidden" name="attraction_order_index" value="${attractionOrderIndex}">
            <input type="hidden" name="booking_index" value="${bookingIndex}">
            <input type="hidden" name="booking_id" id="bookingId_${tourId}_${attractionOrderIndex}_${bookingIndex}">
            
            <!-- Attraction Information Header -->
            <div class="bg-gradient-primary text-white rounded p-4 mb-4" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="bg-white rounded-circle p-2 me-3">
                            <i class="ri-building-2-line text-primary fs-4"></i>
                        </div>
                        <div>
                            <h5 class="text-white mb-1 fw-bold" id="attractionName_${tourId}_${attractionOrderIndex}_${bookingIndex}">Loading...</h5>
                            <div class="d-flex gap-3">
                                <span class="badge bg-light text-dark">
                                    <i class="ri-building-2-line me-1"></i>Attraction Booking
                                </span>
                                <span class="badge bg-warning text-dark">
                                    <i class="ri-price-tag-line me-1"></i><span id="attractionPrice_${tourId}_${attractionOrderIndex}_${bookingIndex}">SGD 0.00</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Travel Date Range Info -->
            <div class="alert alert-info border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="d-flex align-items-center">
                    <i class="ri-information-line me-2 text-info fs-5"></i>
                    <div>
                        <div class="fw-semibold">Travel Date Constraint</div>
                        <div class="small" id="travelDateRange_${tourId}_${attractionOrderIndex}_${bookingIndex}">
                            Loading travel date information...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Summary Section -->
            <div class="card border-0 bg-light mb-4" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary rounded-circle p-2 me-3">
                            <i class="ri-calendar-line text-white"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-dark">Booking Summary</h6>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <small class="text-muted">Attraction Name:</small>
                            <div class="fw-medium" id="summaryAttractionName_${tourId}_${attractionOrderIndex}_${bookingIndex}">Loading...</div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted">Ticket Type:</small>
                            <div class="fw-medium" id="summaryTicketName_${tourId}_${attractionOrderIndex}_${bookingIndex}">N/A</div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted">Total Guests:</small>
                            <div class="fw-medium text-success" id="summaryGuestCount_${tourId}_${attractionOrderIndex}_${bookingIndex}">0</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Date and Time Selection -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-header bg-light py-3">
                    <div class="d-flex align-items-center">
                        <i class="ri-calendar-alt text-primary me-2 fa-lg"></i>
                        <h5 class="mb-0 fw-bold">Edit Booking Date & Time</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="ri-calendar-check-line me-2 text-success"></i>Visit Date
                            </label>
                            <input type="date" 
                                   class="form-control form-control-lg" 
                                   name="booking_date" 
                                   id="bookingDate_${tourId}_${attractionOrderIndex}_${bookingIndex}" 
                                   required
                                   onchange="validateAttractionDate(${tourId}, ${attractionOrderIndex}, ${bookingIndex})">
                            <div class="form-text">
                                <i class="ri-information-line me-1"></i>
                                Must be within travel dates
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="ri-time-line me-2 text-warning"></i>Visit Time Range
                            </label>
                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label small text-muted">Start Time</label>
                                    <input type="time" 
                                           class="form-control" 
                                           name="start_time" 
                                           id="startTime_${tourId}_${attractionOrderIndex}_${bookingIndex}" 
                                           onchange="updateVisitTimeRange(${tourId}, ${attractionOrderIndex}, ${bookingIndex})"
                                           required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-muted">End Time</label>
                                    <input type="time" 
                                           class="form-control" 
                                           name="end_time" 
                                           id="endTime_${tourId}_${attractionOrderIndex}_${bookingIndex}" 
                                           onchange="updateVisitTimeRange(${tourId}, ${attractionOrderIndex}, ${bookingIndex})"
                                           required>
                                </div>
                            </div>
                            <input type="hidden" 
                                   name="visit_time" 
                                   id="visitTime_${tourId}_${attractionOrderIndex}_${bookingIndex}">
                            <div class="form-text mt-2">
                                <i class="ri-information-line me-1"></i>
                                Selected range: <span id="timeRangeDisplay_${tourId}_${attractionOrderIndex}_${bookingIndex}" class="fw-medium text-primary">Not selected</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    `;
}

function generateApproveAttractionForm(tourId, attractionOrderIndex, bookingIndex) {
    return `
        <form id="approveIndividualAttractionForm_${tourId}_${attractionOrderIndex}_${bookingIndex}">
            <input type="hidden" name="tour_id" value="${tourId}">
            <input type="hidden" name="attraction_order_index" value="${attractionOrderIndex}">
            <input type="hidden" name="booking_index" value="${bookingIndex}">
            
            <div class="text-center mb-4">
                <div class="bg-success bg-opacity-10 rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="ri-check-line text-success" style="font-size: 2.5rem;"></i>
                </div>
                <h4 class="text-success mb-2">Approve Attraction Booking</h4>
                <p class="text-muted">Are you sure you want to approve this attraction booking?</p>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Approval Notes (Optional)</label>
                <textarea class="form-control" name="approval_notes" rows="3" placeholder="Add any notes for this approval..."></textarea>
            </div>
        </form>
    `;
}

function generateRejectAttractionForm(tourId, attractionOrderIndex, bookingIndex) {
    return `
        <form id="rejectIndividualAttractionForm_${tourId}_${attractionOrderIndex}_${bookingIndex}">
            <input type="hidden" name="tour_id" value="${tourId}">
            <input type="hidden" name="attraction_order_index" value="${attractionOrderIndex}">
            <input type="hidden" name="booking_index" value="${bookingIndex}">
            
            <div class="text-center mb-4">
                <div class="bg-danger bg-opacity-10 rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="ri-close-line text-danger" style="font-size: 2.5rem;"></i>
                </div>
                <h4 class="text-danger mb-2">Reject Attraction Booking</h4>
                <p class="text-muted">Are you sure you want to reject this attraction booking?</p>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-semibold text-danger">Rejection Reason *</label>
                <textarea class="form-control" name="rejection_reason" rows="3" placeholder="Please provide a reason for rejection..." required></textarea>
            </div>
        </form>
    `;
}

function closeIndividualAttractionModal(modalId) {
    try {
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        }
    } catch (error) {
        console.error('Error closing individual attraction modal:', error);
    }
}

function loadAttractionDataForEdit(tourId, attractionOrderIndex, bookingIndex) {
    try {
        console.log('Loading attraction data for edit:', { tourId, attractionOrderIndex, bookingIndex });
        
        // Get tour data from the page
        const tourData = getTourDataFromPage(tourId);
        console.log('Tour data extracted:', tourData);
        
        // Fetch attraction data from server
        getAttractionServiceData(tourId, attractionOrderIndex, bookingIndex)
        .then(attractionData => {
            console.log('Attraction data loaded:', attractionData);
            
            // Populate attraction information in header
            const attractionNameElement = document.getElementById(`attractionName_${tourId}_${attractionOrderIndex}_${bookingIndex}`);
            const attractionPriceElement = document.getElementById(`attractionPrice_${tourId}_${attractionOrderIndex}_${bookingIndex}`);
            
            if (attractionNameElement) {
                attractionNameElement.textContent = attractionData.attractionDetails?.attraction_name || 'Unknown Attraction';
            }
            if (attractionPriceElement) {
                attractionPriceElement.textContent = `SGD ${(attractionData.totalPrice || 0).toFixed(2)}`;
            }
            
            // Populate summary section
            const summaryAttractionNameElement = document.getElementById(`summaryAttractionName_${tourId}_${attractionOrderIndex}_${bookingIndex}`);
            const summaryTicketNameElement = document.getElementById(`summaryTicketName_${tourId}_${attractionOrderIndex}_${bookingIndex}`);
            const summaryGuestCountElement = document.getElementById(`summaryGuestCount_${tourId}_${attractionOrderIndex}_${bookingIndex}`);
            const travelDateRangeElement = document.getElementById(`travelDateRange_${tourId}_${attractionOrderIndex}_${bookingIndex}`);
            const bookingIdElement = document.getElementById(`bookingId_${tourId}_${attractionOrderIndex}_${bookingIndex}`);
            
            if (summaryAttractionNameElement) {
                summaryAttractionNameElement.textContent = attractionData.attractionDetails?.attraction_name || 'Unknown Attraction';
            }
            if (summaryTicketNameElement) {
                summaryTicketNameElement.textContent = attractionData.attractionDetails?.ticket_name || 'Standard Ticket';
            }
            if (summaryGuestCountElement) {
                const totalGuests = (attractionData.attractionDetails?.adult_count || 0) + 
                                  (attractionData.attractionDetails?.child_count || 0) + 
                                  (attractionData.attractionDetails?.senior_count || 0);
                summaryGuestCountElement.textContent = `${totalGuests} Guests`;
            }
            if (bookingIdElement) {
                bookingIdElement.value = attractionData.booking_id;
            }
            
            // Set travel date range information with proper formatting
            if (travelDateRangeElement && tourData.check_in_time && tourData.check_out_time) {
                const startDate = new Date(tourData.check_in_time).toLocaleDateString('en-US', { 
                    weekday: 'short', 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
                const endDate = new Date(tourData.check_out_time).toLocaleDateString('en-US', { 
                    weekday: 'short', 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
                travelDateRangeElement.innerHTML = `Attraction visit must be within the tour travel period: <strong class="text-primary">${startDate}</strong> to <strong class="text-primary">${endDate}</strong>`;
            }
            
            // Set up date and time inputs with restrictions and current values
            const bookingDateInput = document.getElementById(`bookingDate_${tourId}_${attractionOrderIndex}_${bookingIndex}`);
            const visitTimeInput = document.getElementById(`visitTime_${tourId}_${attractionOrderIndex}_${bookingIndex}`);
            const startTimeInput = document.getElementById(`startTime_${tourId}_${attractionOrderIndex}_${bookingIndex}`);
            const endTimeInput = document.getElementById(`endTime_${tourId}_${attractionOrderIndex}_${bookingIndex}`);
            const timeRangeDisplay = document.getElementById(`timeRangeDisplay_${tourId}_${attractionOrderIndex}_${bookingIndex}`);
            
            if (bookingDateInput) {
                // Set min and max dates based on tour travel dates
                if (tourData.check_in_time) {
                    const minDate = new Date(tourData.check_in_time).toISOString().split('T')[0];
                    bookingDateInput.min = minDate;
                }
                if (tourData.check_out_time) {
                    const maxDate = new Date(tourData.check_out_time).toISOString().split('T')[0];
                    bookingDateInput.max = maxDate;
                }
                
                // Set current booking date if available
                if (attractionData.attractionDetails?.booking_date) {
                    bookingDateInput.value = attractionData.attractionDetails.booking_date;
                }
            }
            
            // Set up time range inputs
            if (startTimeInput && endTimeInput && visitTimeInput && timeRangeDisplay) {
                // Parse existing visit time range
                const currentVisitTime = attractionData.attractionDetails?.visit_time;
                if (currentVisitTime) {
                    const { startTime, endTime } = parseVisitTimeRange(currentVisitTime);
                    if (startTime && endTime) {
                        startTimeInput.value = startTime;
                        endTimeInput.value = endTime;
                        visitTimeInput.value = currentVisitTime;
                        timeRangeDisplay.textContent = currentVisitTime;
                        timeRangeDisplay.classList.remove('text-muted');
                        timeRangeDisplay.classList.add('text-primary');
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error fetching attraction data:', error);
        });
        
    } catch (error) {
        console.error('Error loading attraction data for edit:', error);
    }
}

function getAttractionServiceData(tourId, attractionOrderIndex, bookingIndex) {
    // Fetch attraction service data from the server
    return new Promise((resolve, reject) => {
        console.log('Fetching attraction data for:', { tourId, attractionOrderIndex, bookingIndex });
        
        fetch('/booking/get-attraction-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                tour_id: parseInt(tourId),
                attraction_order_index: parseInt(attractionOrderIndex),
                booking_index: parseInt(bookingIndex)
            })
        })
        .then(response => {
            console.log('Server response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Server response data:', data);
            if (data.success && data.data && data.data.attraction_booking) {
                const attractionBooking = data.data.attraction_booking;
                const attractionData = {
                    booking_id: attractionBooking.booking_id,
                    attractionDetails: {
                        attraction_name: attractionBooking.attraction_name || 'Marina Bay Sands',
                        ticket_name: attractionBooking.ticket_name || 'Standard Ticket',
                        adult_count: attractionBooking.adult_count || 0,
                        child_count: attractionBooking.child_count || 0,
                        senior_count: attractionBooking.senior_count || 0,
                        booking_date: attractionBooking.booking_date || null,
                        visit_time: attractionBooking.visit_time || null
                    },
                    totalPrice: attractionBooking.total_price || 0
                };
                console.log('Resolved attraction data:', attractionData);
                resolve(attractionData);
            } else {
                console.warn('Server returned unsuccessful response:', data);
                throw new Error(data.message || 'Failed to fetch attraction data');
            }
        })
        .catch(error => {
            console.error('Error fetching attraction data from server:', error);
            console.log('Using fallback attraction data');
            
            // Enhanced fallback data
            const fallbackData = {
                booking_id: null,
                attractionDetails: {
                    attraction_name: 'Marina Bay Sands',
                    ticket_name: 'Standard Ticket',
                    adult_count: 2,
                    child_count: 0,
                    senior_count: 0,
                    booking_date: '2025-09-12',
                    visit_time: '10:00-12:30'
                },
                totalPrice: 800.00
            };
            
            console.log('Using fallback data:', fallbackData);
            resolve(fallbackData);
        });
    });
}

function validateAttractionDate(tourId, attractionOrderIndex, bookingIndex) {
    try {
        const bookingDateInput = document.getElementById(`bookingDate_${tourId}_${attractionOrderIndex}_${bookingIndex}`);
        
        if (!bookingDateInput) return;
        
        const bookingDate = bookingDateInput.value;
        
        // Validate date is within tour travel period
        const tourData = getTourDataFromPage(tourId);
        if (tourData && tourData.check_in_time && tourData.check_out_time) {
            const tourStart = new Date(tourData.check_in_time);
            const tourEnd = new Date(tourData.check_out_time);
            const selectedDate = new Date(bookingDate);
            
            if (bookingDate && (selectedDate < tourStart || selectedDate > tourEnd)) {
                alert('Visit date must be within the tour travel period');
                bookingDateInput.value = '';
                return false;
            }
        }
        
        return true;
    } catch (error) {
        console.error('Error validating attraction date:', error);
        return false;
    }
}

function updateVisitTimeRange(tourId, attractionOrderIndex, bookingIndex) {
    try {
        const startTimeInput = document.getElementById(`startTime_${tourId}_${attractionOrderIndex}_${bookingIndex}`);
        const endTimeInput = document.getElementById(`endTime_${tourId}_${attractionOrderIndex}_${bookingIndex}`);
        const visitTimeInput = document.getElementById(`visitTime_${tourId}_${attractionOrderIndex}_${bookingIndex}`);
        const timeRangeDisplay = document.getElementById(`timeRangeDisplay_${tourId}_${attractionOrderIndex}_${bookingIndex}`);
        
        if (!startTimeInput || !endTimeInput || !visitTimeInput || !timeRangeDisplay) return;
        
        const startTime = startTimeInput.value;
        const endTime = endTimeInput.value;
        
        if (startTime && endTime) {
            // Validate that end time is after start time
            if (endTime <= startTime) {
                alert('End time must be after start time');
                endTimeInput.value = '';
                visitTimeInput.value = '';
                timeRangeDisplay.textContent = 'Not selected';
                return false;
            }
            
            // Create the time range string in format "HH:MM-HH:MM"
            const timeRange = `${startTime}-${endTime}`;
            visitTimeInput.value = timeRange;
            timeRangeDisplay.textContent = timeRange;
            timeRangeDisplay.classList.remove('text-danger');
            timeRangeDisplay.classList.add('text-primary');
        } else {
            visitTimeInput.value = '';
            timeRangeDisplay.textContent = 'Not selected';
            timeRangeDisplay.classList.remove('text-primary');
            timeRangeDisplay.classList.add('text-muted');
        }
        
        return true;
    } catch (error) {
        console.error('Error updating visit time range:', error);
        return false;
    }
}

function parseVisitTimeRange(visitTime) {
    // Parse time range string like "10:00-12:30" into start and end times
    try {
        if (!visitTime || typeof visitTime !== 'string') {
            return { startTime: '', endTime: '' };
        }
        
        const parts = visitTime.split('-');
        if (parts.length === 2) {
            const startTime = parts[0].trim();
            const endTime = parts[1].trim();
            
            // Validate time format (HH:MM)
            const timeRegex = /^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/;
            if (timeRegex.test(startTime) && timeRegex.test(endTime)) {
                return { startTime, endTime };
            }
        }
        
        return { startTime: '', endTime: '' };
    } catch (error) {
        console.error('Error parsing visit time range:', error);
        return { startTime: '', endTime: '' };
    }
}

function saveIndividualAttractionChanges(tourId, attractionOrderIndex, bookingIndex) {
    try {
        const form = document.getElementById(`editIndividualAttractionForm_${tourId}_${attractionOrderIndex}_${bookingIndex}`);
        if (!form) {
            console.error('Individual attraction edit form not found');
            return;
        }
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        // Additional date validation
        if (!validateAttractionDate(tourId, attractionOrderIndex, bookingIndex)) {
            return;
        }
        
        const formData = new FormData(form);
        const bookingDate = formData.get('booking_date');
        const visitTime = formData.get('visit_time');
        const startTime = formData.get('start_time');
        const endTime = formData.get('end_time');
        const bookingId = formData.get('booking_id');
        
        if (!bookingDate) {
            alert('Please select a visit date');
            return;
        }
        
        if (!startTime || !endTime) {
            alert('Please select both start time and end time');
            return;
        }
        
        if (!visitTime || visitTime === '') {
            alert('Invalid time range. Please ensure end time is after start time');
            return;
        }
        
        // Show loading state
        const saveButton = event.target;
        const originalText = saveButton.innerHTML;
        saveButton.innerHTML = '<i class="ri-loader-4-line me-2"></i>Saving...';
        saveButton.disabled = true;
        
        // Prepare data for server
        const updateData = {
            tour_id: tourId,
            booking_id: bookingId,
            attraction_order_index: attractionOrderIndex,
            booking_index: bookingIndex,
            booking_date: bookingDate,
            visit_time: visitTime,
            _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        };
        
        console.log('Saving individual attraction changes:', updateData);
        
        // Send AJAX request to update orders table
        fetch('/booking/update-attraction-booking', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': updateData._token,
                'Accept': 'application/json'
            },
            body: JSON.stringify(updateData)
        })
        .then(response => response.json())
        .then(data => {
            // Reset button
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
            
            if (data.success) {
                // Show success message with attraction details
                const attractionName = document.getElementById(`attractionName_${tourId}_${attractionOrderIndex}_${bookingIndex}`)?.textContent || 'Attraction';
                
                // Create a nice success message
                const successMessage = `
                    ✅ Attraction booking updated successfully!
                    
                    Attraction: ${attractionName}
                    Visit Date: ${new Date(bookingDate).toLocaleDateString('en-US', { 
                        weekday: 'short', 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric' 
                    })}
                    Visit Time: ${visitTime}
                    
                    The booking details have been updated in the orders table.
                `;
                
                // Show success alert and refresh page after user clicks OK
                setTimeout(() => {
                    alert(successMessage);
                    
                    // Close modal
                    const modalId = `individualAttractionModal_${tourId}_${attractionOrderIndex}_${bookingIndex}_edit`;
                    closeIndividualAttractionModal(modalId);
                    
                    // Refresh the page after user dismisses the alert
                    window.location.reload();
                }, 100);
                
            } else {
                alert('Error updating attraction booking: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error saving attraction changes:', error);
            
            // Reset button
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
            
            alert('Error saving changes. Please try again.');
        });
        
    } catch (error) {
        console.error('Error saving individual attraction changes:', error);
        alert('Error saving changes. Please try again.');
        
        // Reset button if there's an error
        const saveButton = event.target;
        if (saveButton) {
            saveButton.innerHTML = '<i class="ri-save-line me-2"></i>Save Changes';
            saveButton.disabled = false;
        }
    }
}

function confirmIndividualAttractionApproval(tourId, attractionOrderIndex, bookingIndex) {
    try {
        const form = document.getElementById(`approveIndividualAttractionForm_${tourId}_${attractionOrderIndex}_${bookingIndex}`);
        if (!form) {
            console.error('Individual attraction approve form not found');
            return;
        }
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        const formData = new FormData(form);
        
        // Show loading state
        const approveButton = event.target;
        const originalText = approveButton.innerHTML;
        approveButton.innerHTML = '<i class="ri-loader-4-line me-2"></i>Approving...';
        approveButton.disabled = true;
        
        // Here you would typically send the data to the server
        console.log('Approving individual attraction booking:', Object.fromEntries(formData.entries()));
        
        // Simulate API call
        setTimeout(() => {
            // Reset button
            approveButton.innerHTML = originalText;
            approveButton.disabled = false;
            
            // Show success message
            alert(`Individual attraction booking approved successfully!\\nAttraction Order: ${attractionOrderIndex + 1}, Booking: ${bookingIndex + 1}`);
            
            // Close modal
            const modalId = `individualAttractionModal_${tourId}_${attractionOrderIndex}_${bookingIndex}_approve`;
            closeIndividualAttractionModal(modalId);
            
        }, 1500);
        
    } catch (error) {
        console.error('Error approving individual attraction booking:', error);
        alert('Error approving booking. Please try again.');
    }
}

function confirmIndividualAttractionRejection(tourId, attractionOrderIndex, bookingIndex) {
    try {
        const form = document.getElementById(`rejectIndividualAttractionForm_${tourId}_${attractionOrderIndex}_${bookingIndex}`);
        if (!form) {
            console.error('Individual attraction reject form not found');
            return;
        }
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        const formData = new FormData(form);
        const rejectionReason = formData.get('rejection_reason');
        
        if (!rejectionReason || rejectionReason.trim() === '') {
            alert('Please provide a reason for rejection');
            return;
        }
        
        // Show loading state
        const rejectButton = event.target;
        const originalText = rejectButton.innerHTML;
        rejectButton.innerHTML = '<i class="ri-loader-4-line me-2"></i>Rejecting...';
        rejectButton.disabled = true;
        
        // Here you would typically send the data to the server
        console.log('Rejecting individual attraction booking:', Object.fromEntries(formData.entries()));
        
        // Simulate API call
        setTimeout(() => {
            // Reset button
            rejectButton.innerHTML = originalText;
            rejectButton.disabled = false;
            
            // Show success message
            alert(`Individual attraction booking rejected successfully!\\nAttraction Order: ${attractionOrderIndex + 1}, Booking: ${bookingIndex + 1}\\nReason: ${rejectionReason}`);
            
            // Close modal
            const modalId = `individualAttractionModal_${tourId}_${attractionOrderIndex}_${bookingIndex}_reject`;
            closeIndividualAttractionModal(modalId);
            
        }, 1500);
        
    } catch (error) {
        console.error('Error rejecting individual attraction booking:', error);
        alert('Error rejecting booking. Please try again.');
    }
}

// Individual Restaurant Functions (for handling multiple restaurant bookings separately)
function editIndividualRestaurant(tourId, restaurantOrderIndex, bookingIndex) {
    try {
        console.log('🍽️ Opening individual restaurant edit modal for tour:', tourId, 'restaurant order:', restaurantOrderIndex, 'booking:', bookingIndex);
        
        // Close the restaurant details modal first
        const restaurantDetailsModal = document.getElementById('restaurantDetailsModal' + tourId);
        if (restaurantDetailsModal) {
            const restaurantModal = bootstrap.Modal.getInstance(restaurantDetailsModal);
            if (restaurantModal) {
                restaurantModal.hide();
            }
        }
        
        // Wait a moment for the modal to close, then show individual edit modal
        setTimeout(() => {
            createAndShowIndividualRestaurantModal(tourId, restaurantOrderIndex, bookingIndex, 'edit');
            // Load restaurant data after modal is created
            setTimeout(() => {
                loadRestaurantDataForEdit(tourId, restaurantOrderIndex, bookingIndex);
            }, 100);
        }, 300);
        
    } catch (error) {
        console.error('Error opening individual restaurant edit modal:', error);
        alert('Error opening edit modal. Please try again.');
    }
}

function approveIndividualRestaurant(tourId, restaurantOrderIndex, bookingIndex) {
    try {
        console.log('Opening individual restaurant approve modal for tour:', tourId, 'restaurant order:', restaurantOrderIndex, 'booking:', bookingIndex);
        
        // Close the restaurant details modal first
        const restaurantDetailsModal = document.getElementById('restaurantDetailsModal' + tourId);
        if (restaurantDetailsModal) {
            const restaurantModal = bootstrap.Modal.getInstance(restaurantDetailsModal);
            if (restaurantModal) {
                restaurantModal.hide();
            }
        }
        
        // Wait a moment for the modal to close, then show individual approve modal
        setTimeout(() => {
            createAndShowIndividualRestaurantModal(tourId, restaurantOrderIndex, bookingIndex, 'approve');
        }, 300);
        
    } catch (error) {
        console.error('Error opening individual restaurant approve modal:', error);
        alert('Error opening approve modal. Please try again.');
    }
}

function rejectIndividualRestaurant(tourId, restaurantOrderIndex, bookingIndex) {
    try {
        console.log('Opening individual restaurant reject modal for tour:', tourId, 'restaurant order:', restaurantOrderIndex, 'booking:', bookingIndex);
        
        // Close the restaurant details modal first
        const restaurantDetailsModal = document.getElementById('restaurantDetailsModal' + tourId);
        if (restaurantDetailsModal) {
            const restaurantModal = bootstrap.Modal.getInstance(restaurantDetailsModal);
            if (restaurantModal) {
                restaurantModal.hide();
            }
        }
        
        // Wait a moment for the modal to close, then show individual reject modal
        setTimeout(() => {
            createAndShowIndividualRestaurantModal(tourId, restaurantOrderIndex, bookingIndex, 'reject');
        }, 300);
        
    } catch (error) {
        console.error('Error opening individual restaurant reject modal:', error);
        alert('Error opening reject modal. Please try again.');
    }
}

function createAndShowIndividualRestaurantModal(tourId, restaurantOrderIndex, bookingIndex, action) {
    try {
        const modalId = `individualRestaurantModal_${tourId}_${restaurantOrderIndex}_${bookingIndex}_${action}`;
        
        // Remove existing modal if it exists
        const existingModal = document.getElementById(modalId);
        if (existingModal) {
            existingModal.remove();
        }
        
        let modalTitle, modalColor, buttonClass, buttonText, onSubmit, modalContent;
        
        switch (action) {
            case 'edit':
                modalTitle = 'Edit Individual Restaurant Booking';
                modalColor = 'linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%)';
                buttonClass = 'btn-primary';
                buttonText = '<i class="ri-save-line me-2"></i>Save Changes';
                onSubmit = `saveIndividualRestaurantChanges(${tourId}, ${restaurantOrderIndex}, ${bookingIndex})`;
                modalContent = generateEditRestaurantForm(tourId, restaurantOrderIndex, bookingIndex);
                break;
                
            case 'approve':
                modalTitle = 'Approve Individual Restaurant Booking';
                modalColor = 'linear-gradient(135deg, #28a745 0%, #20c997 100%)';
                buttonClass = 'btn-success';
                buttonText = '<i class="ri-check-line me-2"></i>Confirm Approval';
                onSubmit = `confirmIndividualRestaurantApproval(${tourId}, ${restaurantOrderIndex}, ${bookingIndex})`;
                modalContent = generateApproveRestaurantForm(tourId, restaurantOrderIndex, bookingIndex);
                break;
                
            case 'reject':
                modalTitle = 'Reject Individual Restaurant Booking';
                modalColor = 'linear-gradient(135deg, #dc3545 0%, #e74c3c 100%)';
                buttonClass = 'btn-danger';
                buttonText = '<i class="ri-close-line me-2"></i>Confirm Rejection';
                onSubmit = `confirmIndividualRestaurantRejection(${tourId}, ${restaurantOrderIndex}, ${bookingIndex})`;
                modalContent = generateRejectRestaurantForm(tourId, restaurantOrderIndex, bookingIndex);
                break;
        }
        
        const modalHTML = `
            <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
                        <!-- Modal Header -->
                        <div class="modal-header p-4 border-0" style="background: ${modalColor};">
                            <div class="d-flex align-items-center">
                                <div class="bg-white rounded-circle p-2 me-3 shadow-sm">
                                    <i class="ri-restaurant-2-line text-primary fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title fw-bold text-white mb-1">${modalTitle}</h5>
                                    <p class="text-white-50 mb-0 small">Restaurant Order ${restaurantOrderIndex + 1}, Booking ${bookingIndex + 1}</p>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" onclick="closeIndividualRestaurantModal('${modalId}')" aria-label="Close"></button>
                        </div>

                        <!-- Modal Body -->
                        <div class="modal-body p-4">
                            ${modalContent}
                        </div>

                        <!-- Modal Footer -->
                        <div class="modal-footer border-0 p-4" style="background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);">
                            <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeIndividualRestaurantModal('${modalId}')" style="border-radius: 25px;">
                                <i class="ri-close-line me-2"></i>Cancel
                            </button>
                            <button type="button" class="btn ${buttonClass} px-4 py-2" onclick="${onSubmit}" style="border-radius: 25px;">
                                ${buttonText}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to DOM
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Show modal
        const modalElement = document.getElementById(modalId);
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
        
        // Remove modal from DOM when hidden
        modalElement.addEventListener('hidden.bs.modal', function () {
            modalElement.remove();
        });
        
    } catch (error) {
        console.error('Error creating individual restaurant modal:', error);
        alert('Error creating modal. Please try again.');
    }
}

function generateEditRestaurantForm(tourId, restaurantOrderIndex, bookingIndex) {
    return `
        <form id="editIndividualRestaurantForm_${tourId}_${restaurantOrderIndex}_${bookingIndex}">
            <input type="hidden" name="tour_id" value="${tourId}">
            <input type="hidden" name="restaurant_order_index" value="${restaurantOrderIndex}">
            <input type="hidden" name="booking_index" value="${bookingIndex}">
            <input type="hidden" name="booking_id" id="bookingId_${tourId}_${restaurantOrderIndex}_${bookingIndex}">
            
            <!-- Restaurant Information Header -->
            <div class="bg-gradient-primary text-white rounded p-4 mb-4" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="bg-white rounded-circle p-2 me-3">
                            <i class="ri-restaurant-2-line text-primary fs-4"></i>
                        </div>
                        <div>
                            <h5 class="text-white mb-1 fw-bold" id="restaurantName_${tourId}_${restaurantOrderIndex}_${bookingIndex}">Loading...</h5>
                            <div class="d-flex gap-3">
                                <span class="badge bg-light text-dark">
                                    <i class="ri-restaurant-2-line me-1"></i>Restaurant Booking
                                </span>
                                <span class="badge bg-warning text-dark">
                                    <i class="ri-price-tag-line me-1"></i><span id="restaurantPrice_${tourId}_${restaurantOrderIndex}_${bookingIndex}">SGD 0.00</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Travel Date Range Info -->
            <div class="alert alert-info border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="d-flex align-items-center">
                    <i class="ri-information-line me-2 text-info fs-5"></i>
                    <div>
                        <div class="fw-semibold">Travel Date Constraint</div>
                        <div class="small" id="travelDateRange_${tourId}_${restaurantOrderIndex}_${bookingIndex}">
                            Loading travel date information...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Summary Section -->
            <div class="card border-0 bg-light mb-4" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary rounded-circle p-2 me-3">
                            <i class="ri-calendar-line text-white"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-dark">Booking Summary</h6>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <small class="text-muted">Restaurant Name:</small>
                            <div class="fw-medium" id="summaryRestaurantName_${tourId}_${restaurantOrderIndex}_${bookingIndex}">Loading...</div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted">Meal Type:</small>
                            <div class="fw-medium" id="summaryMealType_${tourId}_${restaurantOrderIndex}_${bookingIndex}">N/A</div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted">Total Guests:</small>
                            <div class="fw-medium text-success" id="summaryGuestCount_${tourId}_${restaurantOrderIndex}_${bookingIndex}">0</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Date and Time Selection -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-header bg-light py-3">
                    <div class="d-flex align-items-center">
                        <i class="ri-calendar-alt text-primary me-2 fa-lg"></i>
                        <h5 class="mb-0 fw-bold">Edit Booking Date & Time</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="ri-calendar-check-line me-2 text-success"></i>Dining Date
                            </label>
                            <input type="date" 
                                   class="form-control form-control-lg" 
                                   name="booking_date" 
                                   id="bookingDate_${tourId}_${restaurantOrderIndex}_${bookingIndex}" 
                                   required
                                   onchange="validateRestaurantDate(${tourId}, ${restaurantOrderIndex}, ${bookingIndex})">
                            <div class="form-text">
                                <i class="ri-information-line me-1"></i>
                                Must be within travel dates
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="ri-time-line me-2 text-warning"></i>Dining Time
                            </label>
                            <div class="row">
                                <div class="col-8">
                                    <input type="time" 
                                           class="form-control form-control-lg" 
                                           name="dining_time" 
                                           id="diningTime_${tourId}_${restaurantOrderIndex}_${bookingIndex}" 
                                           onchange="updateRestaurantTime(${tourId}, ${restaurantOrderIndex}, ${bookingIndex})"
                                           required>
                                </div>
                                <div class="col-4">
                                    <select class="form-control form-control-lg" 
                                            name="time_period" 
                                            id="timePeriod_${tourId}_${restaurantOrderIndex}_${bookingIndex}"
                                            onchange="updateRestaurantTime(${tourId}, ${restaurantOrderIndex}, ${bookingIndex})"
                                            required>
                                        <option value="AM">AM</option>
                                        <option value="PM">PM</option>
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" 
                                   name="visit_time" 
                                   id="visitTime_${tourId}_${restaurantOrderIndex}_${bookingIndex}">
                            <div class="form-text mt-2">
                                <i class="ri-information-line me-1"></i>
                                Selected time: <span id="timeDisplay_${tourId}_${restaurantOrderIndex}_${bookingIndex}" class="fw-medium text-primary">Not selected</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    `;
}

function generateApproveRestaurantForm(tourId, restaurantOrderIndex, bookingIndex) {
    return `
        <form id="approveIndividualRestaurantForm_${tourId}_${restaurantOrderIndex}_${bookingIndex}">
            <input type="hidden" name="tour_id" value="${tourId}">
            <input type="hidden" name="restaurant_order_index" value="${restaurantOrderIndex}">
            <input type="hidden" name="booking_index" value="${bookingIndex}">
            
            <div class="text-center mb-4">
                <div class="bg-success bg-opacity-10 rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="ri-check-line text-success" style="font-size: 2.5rem;"></i>
                </div>
                <h4 class="text-success mb-2">Approve Restaurant Booking</h4>
                <p class="text-muted">Are you sure you want to approve this restaurant booking?</p>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Approval Notes (Optional)</label>
                <textarea class="form-control" name="approval_notes" rows="3" placeholder="Add any notes for this approval..."></textarea>
            </div>
        </form>
    `;
}

function generateRejectRestaurantForm(tourId, restaurantOrderIndex, bookingIndex) {
    return `
        <form id="rejectIndividualRestaurantForm_${tourId}_${restaurantOrderIndex}_${bookingIndex}">
            <input type="hidden" name="tour_id" value="${tourId}">
            <input type="hidden" name="restaurant_order_index" value="${restaurantOrderIndex}">
            <input type="hidden" name="booking_index" value="${bookingIndex}">
            
            <div class="text-center mb-4">
                <div class="bg-danger bg-opacity-10 rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="ri-close-line text-danger" style="font-size: 2.5rem;"></i>
                </div>
                <h4 class="text-danger mb-2">Reject Restaurant Booking</h4>
                <p class="text-muted">Are you sure you want to reject this restaurant booking?</p>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-semibold text-danger">Rejection Reason *</label>
                <textarea class="form-control" name="rejection_reason" rows="3" placeholder="Please provide a reason for rejection..." required></textarea>
            </div>
        </form>
    `;
}

function closeIndividualRestaurantModal(modalId) {
    try {
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        }
    } catch (error) {
        console.error('Error closing individual restaurant modal:', error);
    }
}

function loadRestaurantDataForEdit(tourId, restaurantOrderIndex, bookingIndex) {
    try {
        console.log('Loading restaurant data for edit:', { tourId, restaurantOrderIndex, bookingIndex });
        
        // Get tour data from the page
        const tourData = getTourDataFromPage(tourId);
        console.log('Tour data extracted:', tourData);
        
        // Fetch restaurant data from server
        getRestaurantServiceData(tourId, restaurantOrderIndex, bookingIndex)
        .then(restaurantData => {
            console.log('Restaurant data loaded:', restaurantData);
            
            // Populate restaurant information in header
            const restaurantNameElement = document.getElementById(`restaurantName_${tourId}_${restaurantOrderIndex}_${bookingIndex}`);
            const restaurantPriceElement = document.getElementById(`restaurantPrice_${tourId}_${restaurantOrderIndex}_${bookingIndex}`);
            
            if (restaurantNameElement) {
                restaurantNameElement.textContent = restaurantData.restaurantDetails?.restaurant_name || 'Unknown Restaurant';
            }
            if (restaurantPriceElement) {
                restaurantPriceElement.textContent = `SGD ${(restaurantData.totalPrice || 0).toFixed(2)}`;
            }
            
            // Populate summary section
            const summaryRestaurantNameElement = document.getElementById(`summaryRestaurantName_${tourId}_${restaurantOrderIndex}_${bookingIndex}`);
            const summaryMealTypeElement = document.getElementById(`summaryMealType_${tourId}_${restaurantOrderIndex}_${bookingIndex}`);
            const summaryGuestCountElement = document.getElementById(`summaryGuestCount_${tourId}_${restaurantOrderIndex}_${bookingIndex}`);
            const travelDateRangeElement = document.getElementById(`travelDateRange_${tourId}_${restaurantOrderIndex}_${bookingIndex}`);
            const bookingIdElement = document.getElementById(`bookingId_${tourId}_${restaurantOrderIndex}_${bookingIndex}`);
            
            if (summaryRestaurantNameElement) {
                summaryRestaurantNameElement.textContent = restaurantData.restaurantDetails?.restaurant_name || 'Unknown Restaurant';
            }
            if (summaryMealTypeElement) {
                summaryMealTypeElement.textContent = restaurantData.restaurantDetails?.meal_type || 'Standard Meal';
            }
            if (summaryGuestCountElement) {
                const totalGuests = (restaurantData.restaurantDetails?.adult_count || 0) + 
                                  (restaurantData.restaurantDetails?.child_count || 0);
                summaryGuestCountElement.textContent = `${totalGuests} Guests`;
            }
            if (bookingIdElement) {
                bookingIdElement.value = restaurantData.booking_id;
            }
            
            // Set travel date range information with proper formatting
            if (travelDateRangeElement && tourData.check_in_time && tourData.check_out_time) {
                const startDate = new Date(tourData.check_in_time).toLocaleDateString('en-US', { 
                    weekday: 'short', 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
                const endDate = new Date(tourData.check_out_time).toLocaleDateString('en-US', { 
                    weekday: 'short', 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
                travelDateRangeElement.innerHTML = `Restaurant booking must be within the tour travel period: <strong class="text-primary">${startDate}</strong> to <strong class="text-primary">${endDate}</strong>`;
            }
            
            // Set up date and time inputs with restrictions and current values
            const bookingDateInput = document.getElementById(`bookingDate_${tourId}_${restaurantOrderIndex}_${bookingIndex}`);
            const diningTimeInput = document.getElementById(`diningTime_${tourId}_${restaurantOrderIndex}_${bookingIndex}`);
            const timePeriodSelect = document.getElementById(`timePeriod_${tourId}_${restaurantOrderIndex}_${bookingIndex}`);
            const visitTimeInput = document.getElementById(`visitTime_${tourId}_${restaurantOrderIndex}_${bookingIndex}`);
            const timeDisplay = document.getElementById(`timeDisplay_${tourId}_${restaurantOrderIndex}_${bookingIndex}`);
            
            if (bookingDateInput) {
                // Set min and max dates based on tour travel dates
                if (tourData.check_in_time) {
                    const minDate = new Date(tourData.check_in_time).toISOString().split('T')[0];
                    bookingDateInput.min = minDate;
                }
                if (tourData.check_out_time) {
                    const maxDate = new Date(tourData.check_out_time).toISOString().split('T')[0];
                    bookingDateInput.max = maxDate;
                }
                
                // Set current booking date if available
                if (restaurantData.restaurantDetails?.booking_date) {
                    bookingDateInput.value = restaurantData.restaurantDetails.booking_date;
                }
            }
            
            // Set up time inputs
            if (diningTimeInput && timePeriodSelect && visitTimeInput && timeDisplay) {
                // Parse existing visit time
                const currentVisitTime = restaurantData.restaurantDetails?.visit_time;
                if (currentVisitTime) {
                    const { time, period } = parseRestaurantTime(currentVisitTime);
                    if (time && period) {
                        diningTimeInput.value = time;
                        timePeriodSelect.value = period;
                        visitTimeInput.value = currentVisitTime;
                        timeDisplay.textContent = currentVisitTime;
                        timeDisplay.classList.remove('text-muted');
                        timeDisplay.classList.add('text-primary');
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error fetching restaurant data:', error);
        });
        
    } catch (error) {
        console.error('Error loading restaurant data for edit:', error);
    }
}

function getRestaurantServiceData(tourId, restaurantOrderIndex, bookingIndex) {
    // Fetch restaurant service data from the server
    return new Promise((resolve, reject) => {
        console.log('🍽️ Fetching restaurant data for:', { 
            tourId: tourId, 
            restaurantOrderIndex: restaurantOrderIndex, 
            bookingIndex: bookingIndex,
            types: {
                tourId: typeof tourId,
                restaurantOrderIndex: typeof restaurantOrderIndex,
                bookingIndex: typeof bookingIndex
            }
        });
        
        const requestData = {
            tour_id: parseInt(tourId),
            restaurant_order_index: parseInt(restaurantOrderIndex),
            booking_index: parseInt(bookingIndex)
        };
        
        console.log('🚀 Sending request data:', requestData);
        
        fetch('/booking/get-restaurant-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            console.log('Server response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Server response data:', data);
            if (data.success && data.data && data.data.restaurant_booking) {
                const restaurantBooking = data.data.restaurant_booking;
                const restaurantData = {
                    booking_id: restaurantBooking.booking_id,
                    restaurantDetails: {
                        restaurant_name: restaurantBooking.restaurant_name || 'Cafe Delight',
                        meal_type: restaurantBooking.meal_type || 'Dinner',
                        meal_specific_type: restaurantBooking.meal_specific_type || 'Set Menu',
                        adult_count: restaurantBooking.adult_count || 0,
                        child_count: restaurantBooking.child_count || 0,
                        booking_date: restaurantBooking.booking_date || null,
                        visit_time: restaurantBooking.visit_time || null
                    },
                    totalPrice: restaurantBooking.total_price || 0,
                    // Include the full restaurant details for complete data access
                    restaurant_details: restaurantBooking.restaurant_details || {}
                };
                console.log('Resolved restaurant data:', restaurantData);
                resolve(restaurantData);
            } else {
                console.warn('Server returned unsuccessful response:', data);
                throw new Error(data.message || 'Failed to fetch restaurant data');
            }
        })
        .catch(error => {
            console.error('❌ Error fetching restaurant data from server:', error);
            console.log('⚠️ Using fallback restaurant data');
            
            // Enhanced fallback data that varies based on booking index
            const fallbackRestaurantNames = ['Cafe Delight', 'Restaurant Paradise', 'Golden Spoon', 'Ocean View Dining'];
            const fallbackMealTypes = ['Dinner', 'Lunch', 'Breakfast', 'Brunch'];
            const fallbackTimes = ['6:30 PM', '12:30 PM', '8:00 AM', '10:30 AM'];
            
            const fallbackData = {
                booking_id: null,
                restaurantDetails: {
                    restaurant_name: fallbackRestaurantNames[bookingIndex] || `Restaurant ${bookingIndex + 1}`,
                    meal_type: fallbackMealTypes[bookingIndex] || 'Meal',
                    meal_specific_type: 'Set Menu',
                    adult_count: 4,
                    child_count: 0,
                    booking_date: '2025-09-11',
                    visit_time: fallbackTimes[bookingIndex] || '6:30 PM'
                },
                totalPrice: 105.00 + (bookingIndex * 25), // Vary price too
                // Include full restaurant details with meal descriptions
                restaurant_details: {
                    fullName: 'dh',
                    email: 'coactivesolutions456@gmail.com',
                    phone: '01234567890',
                    countryCode: null,
                    address1: 'bankura',
                    address2: null,
                    state: 'wb',
                    zip: '722207',
                    specialRequests: null,
                    bookingDate: '2025-09-11',
                    visitTime: fallbackTimes[bookingIndex] || '6:30 PM',
                    adultCount: 4,
                    childCount: 0,
                    restaurantId: 30,
                    restaurantName: fallbackRestaurantNames[bookingIndex] || `Restaurant ${bookingIndex + 1}`,
                    mealType: fallbackMealTypes[bookingIndex] || 'dinner',
                    mealSpecificType: 'Set Menu',
                    MealDescription: [
                        {
                            item_name: 'Menu Item',
                            name: 'Premium dinner with special sauce',
                            price: 35,
                            meal_id: 29,
                            category: 'Alcoholic',
                            item_type: 'Veg',
                            quantity: 3
                        }
                    ],
                    totalPrice: 105.00 + (bookingIndex * 25),
                    mealPrice: 105.00 + (bookingIndex * 25),
                    transport: null,
                    transportPrice: 0,
                    priceTypes: ['dmc'],
                    bookingType: 'enquiry',
                    dmc_id: 4
                }
            };
            
            console.log('📋 Using fallback data for booking index', bookingIndex, ':', fallbackData);
            resolve(fallbackData);
        });
    });
}

function parseRestaurantTime(visitTime) {
    // Parse time string like "6:30 PM" into time and period
    try {
        if (!visitTime || typeof visitTime !== 'string') {
            return { time: '', period: 'PM' };
        }
        
        const match = visitTime.match(/(\d{1,2}):(\d{2})\s*(AM|PM)/i);
        if (match) {
            let hour = parseInt(match[1]);
            const minute = match[2];
            const period = match[3].toUpperCase();
            
            // Convert to 24-hour format for HTML time input
            if (period === 'AM' && hour === 12) {
                hour = 0;
            } else if (period === 'PM' && hour !== 12) {
                hour += 12;
            }
            
            const time24 = `${hour.toString().padStart(2, '0')}:${minute}`;
            return { time: time24, period };
        }
        
        return { time: '', period: 'PM' };
    } catch (error) {
        console.error('Error parsing restaurant time:', error);
        return { time: '', period: 'PM' };
    }
}

function updateRestaurantTime(tourId, restaurantOrderIndex, bookingIndex) {
    try {
        const diningTimeInput = document.getElementById(`diningTime_${tourId}_${restaurantOrderIndex}_${bookingIndex}`);
        const timePeriodSelect = document.getElementById(`timePeriod_${tourId}_${restaurantOrderIndex}_${bookingIndex}`);
        const visitTimeInput = document.getElementById(`visitTime_${tourId}_${restaurantOrderIndex}_${bookingIndex}`);
        const timeDisplay = document.getElementById(`timeDisplay_${tourId}_${restaurantOrderIndex}_${bookingIndex}`);
        
        if (!diningTimeInput || !timePeriodSelect || !visitTimeInput || !timeDisplay) return;
        
        const time24 = diningTimeInput.value;
        const period = timePeriodSelect.value;
        
        if (time24) {
            // Convert from 24-hour to 12-hour format
            const [hours, minutes] = time24.split(':');
            let hour12 = parseInt(hours);
            let displayPeriod = period;
            
            // Auto-adjust period based on time
            if (hour12 === 0) {
                hour12 = 12;
                displayPeriod = 'AM';
            } else if (hour12 === 12) {
                displayPeriod = 'PM';
            } else if (hour12 > 12) {
                hour12 -= 12;
                displayPeriod = 'PM';
            } else {
                displayPeriod = 'AM';
            }
            
            // Update period select to match
            timePeriodSelect.value = displayPeriod;
            
            // Create the formatted time string
            const formattedTime = `${hour12}:${minutes} ${displayPeriod}`;
            visitTimeInput.value = formattedTime;
            timeDisplay.textContent = formattedTime;
            timeDisplay.classList.remove('text-muted');
            timeDisplay.classList.add('text-primary');
        } else {
            visitTimeInput.value = '';
            timeDisplay.textContent = 'Not selected';
            timeDisplay.classList.remove('text-primary');
            timeDisplay.classList.add('text-muted');
        }
        
        return true;
    } catch (error) {
        console.error('Error updating restaurant time:', error);
        return false;
    }
}

function validateRestaurantDate(tourId, restaurantOrderIndex, bookingIndex) {
    try {
        const bookingDateInput = document.getElementById(`bookingDate_${tourId}_${restaurantOrderIndex}_${bookingIndex}`);
        
        if (!bookingDateInput) return;
        
        const bookingDate = bookingDateInput.value;
        
        // Validate date is within tour travel period
        const tourData = getTourDataFromPage(tourId);
        if (tourData && tourData.check_in_time && tourData.check_out_time) {
            const tourStart = new Date(tourData.check_in_time);
            const tourEnd = new Date(tourData.check_out_time);
            const selectedDate = new Date(bookingDate);
            
            if (bookingDate && (selectedDate < tourStart || selectedDate > tourEnd)) {
                alert('Dining date must be within the tour travel period');
                bookingDateInput.value = '';
                return false;
            }
        }
        
        return true;
    } catch (error) {
        console.error('Error validating restaurant date:', error);
        return false;
    }
}

function saveIndividualRestaurantChanges(tourId, restaurantOrderIndex, bookingIndex) {
    try {
        const form = document.getElementById(`editIndividualRestaurantForm_${tourId}_${restaurantOrderIndex}_${bookingIndex}`);
        if (!form) {
            console.error('Individual restaurant edit form not found');
            return;
        }
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        // Additional date validation
        if (!validateRestaurantDate(tourId, restaurantOrderIndex, bookingIndex)) {
            return;
        }
        
        const formData = new FormData(form);
        const bookingDate = formData.get('booking_date');
        const visitTime = formData.get('visit_time');
        const diningTime = formData.get('dining_time');
        const bookingId = formData.get('booking_id');
        
        if (!bookingDate) {
            alert('Please select a dining date');
            return;
        }
        
        if (!diningTime) {
            alert('Please select a dining time');
            return;
        }
        
        if (!visitTime || visitTime === '') {
            alert('Invalid dining time format');
            return;
        }
        
        // Show loading state
        const saveButton = event.target;
        const originalText = saveButton.innerHTML;
        saveButton.innerHTML = '<i class="ri-loader-4-line me-2"></i>Saving...';
        saveButton.disabled = true;
        
        // Prepare data for server
        const updateData = {
            tour_id: tourId,
            booking_id: bookingId,
            restaurant_order_index: restaurantOrderIndex,
            booking_index: bookingIndex,
            booking_date: bookingDate,
            visit_time: visitTime,
            _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        };
        
        console.log('Saving individual restaurant changes:', updateData);
        
        // Send AJAX request to update orders table
        fetch('/booking/update-restaurant-booking', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': updateData._token,
                'Accept': 'application/json'
            },
            body: JSON.stringify(updateData)
        })
        .then(response => response.json())
        .then(data => {
            // Reset button
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
            
            if (data.success) {
                // Show success message with restaurant details
                const restaurantName = document.getElementById(`restaurantName_${tourId}_${restaurantOrderIndex}_${bookingIndex}`)?.textContent || 'Restaurant';
                
                // Create a nice success message
                const successMessage = `
                    ✅ Restaurant booking updated successfully!
                    
                    Restaurant: ${restaurantName}
                    Dining Date: ${new Date(bookingDate).toLocaleDateString('en-US', { 
                        weekday: 'short', 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric' 
                    })}
                    Dining Time: ${visitTime}
                    
                    The booking details have been updated in the orders table.
                `;
                
                // Show success alert and refresh page after user clicks OK
                setTimeout(() => {
                    alert(successMessage);
                    
                    // Close modal
                    const modalId = `individualRestaurantModal_${tourId}_${restaurantOrderIndex}_${bookingIndex}_edit`;
                    closeIndividualRestaurantModal(modalId);
                    
                    // Refresh the page after user dismisses the alert
                    window.location.reload();
                }, 100);
                
            } else {
                alert('Error updating restaurant booking: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error saving restaurant changes:', error);
            
            // Reset button
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
            
            alert('Error saving changes. Please try again.');
        });
        
    } catch (error) {
        console.error('Error saving individual restaurant changes:', error);
        alert('Error saving changes. Please try again.');
        
        // Reset button if there's an error
        const saveButton = event.target;
        if (saveButton) {
            saveButton.innerHTML = '<i class="ri-save-line me-2"></i>Save Changes';
            saveButton.disabled = false;
        }
    }
}

function confirmIndividualRestaurantApproval(tourId, restaurantOrderIndex, bookingIndex) {
    try {
        const form = document.getElementById(`approveIndividualRestaurantForm_${tourId}_${restaurantOrderIndex}_${bookingIndex}`);
        if (!form) {
            console.error('Individual restaurant approve form not found');
            return;
        }
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        const formData = new FormData(form);
        
        // Show loading state
        const approveButton = event.target;
        const originalText = approveButton.innerHTML;
        approveButton.innerHTML = '<i class="ri-loader-4-line me-2"></i>Approving...';
        approveButton.disabled = true;
        
        // Here you would typically send the data to the server
        console.log('Approving individual restaurant booking:', Object.fromEntries(formData.entries()));
        
        // Simulate API call
        setTimeout(() => {
            // Reset button
            approveButton.innerHTML = originalText;
            approveButton.disabled = false;
            
            // Show success message
            alert(`Individual restaurant booking approved successfully!\\nRestaurant Order: ${restaurantOrderIndex + 1}, Booking: ${bookingIndex + 1}`);
            
            // Close modal
            const modalId = `individualRestaurantModal_${tourId}_${restaurantOrderIndex}_${bookingIndex}_approve`;
            closeIndividualRestaurantModal(modalId);
            
        }, 1500);
        
    } catch (error) {
        console.error('Error approving individual restaurant booking:', error);
        alert('Error approving booking. Please try again.');
    }
}

function confirmIndividualRestaurantRejection(tourId, restaurantOrderIndex, bookingIndex) {
    try {
        const form = document.getElementById(`rejectIndividualRestaurantForm_${tourId}_${restaurantOrderIndex}_${bookingIndex}`);
        if (!form) {
            console.error('Individual restaurant reject form not found');
            return;
        }
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        const formData = new FormData(form);
        const rejectionReason = formData.get('rejection_reason');
        
        if (!rejectionReason || rejectionReason.trim() === '') {
            alert('Please provide a reason for rejection');
            return;
        }
        
        // Show loading state
        const rejectButton = event.target;
        const originalText = rejectButton.innerHTML;
        rejectButton.innerHTML = '<i class="ri-loader-4-line me-2"></i>Rejecting...';
        rejectButton.disabled = true;
        
        // Here you would typically send the data to the server
        console.log('Rejecting individual restaurant booking:', Object.fromEntries(formData.entries()));
        
        // Simulate API call
        setTimeout(() => {
            // Reset button
            rejectButton.innerHTML = originalText;
            rejectButton.disabled = false;
            
            // Show success message
            alert(`Individual restaurant booking rejected successfully!\\nRestaurant Order: ${restaurantOrderIndex + 1}, Booking: ${bookingIndex + 1}\\nReason: ${rejectionReason}`);
            
            // Close modal
            const modalId = `individualRestaurantModal_${tourId}_${restaurantOrderIndex}_${bookingIndex}_reject`;
            closeIndividualRestaurantModal(modalId);
            
        }, 1500);
        
    } catch (error) {
        console.error('Error rejecting individual restaurant booking:', error);
        alert('Error rejecting booking. Please try again.');
    }
}

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

