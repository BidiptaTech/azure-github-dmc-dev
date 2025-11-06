@extends('layouts.layout')
@section('title', 'Actual Bookings')
@extends('layouts.datatablecss')

<!-- Date Range Picker CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<!-- Add SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<!-- Add SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-2">
                <span class="text-muted fw-light">Bookings /</span> Actual Bookings
            </h4>
            <p class="text-muted">Manage actual bookings with payment details and execution status</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-success fs-6">
                <i class="ri-check-circle-line me-1"></i>
                <span id="rangeCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</span>
                <span id="rangeLabel">{{ date('F') }}</span> Actual
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
                            <h5 class="card-title mb-1" id="statActualCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</h5>
                            <p class="text-muted mb-0" id="statActualLabel">{{ date('F') }} Actual</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-success rounded">
                                <i class="ri-bar-chart-line ri-24px"></i>
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
                                $totalRevenue = 0;
                                $currentMonthTours = $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth());
                                foreach($currentMonthTours as $tour) {
                                    if (!empty($tour->parsed_payment_details)) {
                                        foreach($tour->parsed_payment_details as $payment) {
                                            $totalRevenue += floatval($payment['amount'] ?? 0);
                                        }
                                    }
                                }
                            @endphp
                            <h5 class="card-title mb-1" id="statRevenueCount">${{ number_format($totalRevenue) }}</h5>
                            <p class="text-muted mb-0" id="statRevenueLabel">{{ date('F') }} Revenue</p>
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
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1" id="statActiveCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->where('check_in_time', '<', now())->where('check_out_time', '>', now())->count() }}</h5>
                            <p class="text-muted mb-0" id="statActiveLabel">{{ date('F') }} Active</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-warning rounded">
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
                            <h5 class="card-title mb-1" id="statCompletedCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->where('check_out_time', '<', now())->count() }}</h5>
                            <p class="text-muted mb-0" id="statCompletedLabel">{{ date('F') }} Completed</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-info rounded">
                                <i class="ri-checkbox-circle-line ri-24px"></i>
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
                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Tour ID, Display ID...">
                </div>
                {{-- <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="Active">Currently Active</option>
                        <option value="Completed">Completed</option>
                        <option value="Upcoming">Upcoming</option>
                    </select>
                </div> --}}
                <div class="col-md-2">
                    <label class="form-label">Payment Type</label>
                    <select class="form-select" id="paymentFilter">
                        <option value="">All Payments</option>
                        <option value="cash">Cash Payments</option>
                        <option value="card">Card Payments</option>
                        <option value="bank">Bank Transfer</option>
                    </select>
                </div>
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
            <h5 class="mb-0">Actual Bookings List</h5>
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
                            <th>Created At</th>
                            <th>Destination</th>
                            <th>Guests</th>
                            <th>Agent</th>
                            <th>Services</th>
                            <th>Travel Dates</th>
                            <th>Payment Details</th>
                            <th>Payment Status</th>
                            <th>Status</th>
                            <th>Actions</th>
                            <th>Created At</th>
                            <th>Auto Cancel Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tours as $key => $tour)
                        @php
                            $isActive = $tour->check_in_time && $tour->check_out_time && 
                                       \Carbon\Carbon::parse($tour->check_in_time)->isPast() && 
                                       \Carbon\Carbon::parse($tour->check_out_time)->isFuture();
                            $isCompleted = $tour->check_out_time && \Carbon\Carbon::parse($tour->check_out_time)->isPast();
                            $totalAmount = 0;
                            $paymentMethods = [];
                            
                            if (!empty($tour->parsed_payment_details)) {
                                foreach($tour->parsed_payment_details as $payment) {
                                    $totalAmount += floatval($payment['amount'] ?? 0);
                                    if (!empty($payment['payment_type']) && !in_array($payment['payment_type'], $paymentMethods)) {
                                        $paymentMethods[] = $payment['payment_type'];
                                    }
                                }
                            }
                            
                            // Calculate payment status - following confirmed/definite pattern
                            $tourTotalPrice = 0;
                            $orders = \App\Models\Order::where('tour_id', $tour->tour_id)->whereNull('deleted_at')->get();
                            foreach($orders as $order) {
                                if($order->data) {
                                    $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
                                    if(is_array($orderData)) {
                                        foreach($orderData as $item) {
                                            if(isset($item['totalPrice'])) {
                                                $tourTotalPrice += (float)$item['totalPrice'];
                                            }
                                        }
                                    }
                                }
                            }
                            
                            $enquiry = \App\Models\Enquiry::where('tour_id', $tour->tour_id)->where('status', 2)->first();
                            $discountAmount = $enquiry ? ($enquiry->actual_amount - $enquiry->amount) : 0;
                            
                            // Calculate base amount before tax (round up if decimal > 0.5, round down if < 0.5)
                            $baseAmount = round($tourTotalPrice) - $discountAmount;
                            
                            // Calculate tax amount using TaxHelper
                            $persons = ($tour->adult ?? 0) + ($tour->child ?? 0);
                            $days = \App\Helpers\TaxHelper::calculateDays($tour->check_in_time, $tour->check_out_time);
                            
                            $taxResult = \App\Helpers\TaxHelper::calculateTourTaxes($baseAmount, $tour->taxes, $persons, $days);
                            $taxAmount = $taxResult['total_tax'];
                            $taxBreakdown = $taxResult['breakdown'];
                            $finalAmount = $baseAmount + $taxAmount;
                            
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
                        <tr 
                            class="{{ $isActive ? 'table-warning' : ($isCompleted ? 'table-success' : '') }}"
                            data-created-at="{{ optional($tour->created_at)->toDateString() }}"
                            data-updated-at="{{ optional($tour->updated_at)->toDateString() }}"
                            data-revenue="{{ $totalAmount }}"
                            data-status="{{ $isActive ? 'Active' : ($isCompleted ? 'Completed' : 'Upcoming') }}"
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
                                    <span class="fw-medium">{{ \Carbon\Carbon::parse($tour->created_at)->format('D, M d, Y') }}</span>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($tour->created_at)->format('H:i A') }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ $tour->destination ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-3 align-items-center">
                                    <div class="d-flex align-items-center gap-1" title="Adults">
                                        <i class="ri-user-line text-success" style="font-size: 1.2rem;"></i>
                                        <span class="fw-medium">{{ $tour->adult ?? 0 }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1" title="Children">
                                        <i class="ri-user-smile-line text-warning" style="font-size: 1.2rem;"></i>
                                        <span class="fw-medium">{{ $tour->child ?? 0 }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1" title="Infants">
                                        <i class="ri-user-heart-line text-info" style="font-size: 1.2rem;"></i>
                                        <span class="fw-medium">{{ $tour->infant ?? 0 }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ $tour->agent_name ?? 'N/A' }}</span>
                                    <small class="text-muted">
                                        <i class="fas fa-building me-1"></i>
                                        {{ $tour->agent_company_name ?? 'N/A' }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    @php
                                        // Fetch orders for this tour to get actual service data
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
                                        
                                        foreach($orders as $order) {
                                            if(isset($svc[$order->type])) {
                                                $svc[$order->type]++;
                                                if(!isset($serviceData[$order->type])) {
                                                    $serviceData[$order->type] = [];
                                                }
                                                $serviceData[$order->type][] = $order;
                                            }
                                        }
                                        
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
                                        
                                        // For debugging
                                        $debugInfo = [
                                            'tour_id' => $tour->tour_id,
                                            'orders_count' => $orders->count(),
                                            'svc' => $svc,
                                            'serviceData_keys' => array_keys($serviceData)
                                        ];
                                    @endphp
                                    @foreach($svc as $key=>$count)
                                        @if(intval($count) > 0)
                                            @if(in_array($key, ['hotel', 'attraction', 'restaurant', 'guide', 'entry_port', 'exit_port', 'travel_hourly', 'travel_point', 'local_transport']))
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
                                <div class="d-flex flex-column">
                                    @if($tour->check_in_time)
                                        <small><strong>Start:</strong> {{ \Carbon\Carbon::parse($tour->check_in_time)->format('D, M d, Y') }}</small>
                                    @endif
                                    @if($tour->check_out_time)
                                        <small><strong>End:</strong> {{ \Carbon\Carbon::parse($tour->check_out_time)->format('D, M d, Y') }}</small>
                                    @endif
                                    @if($tour->check_in_time && $tour->check_out_time)
                                        <small class="text-muted">
                                            Duration: {{ \Carbon\Carbon::parse($tour->check_in_time)->diffInDays(\Carbon\Carbon::parse($tour->check_out_time)) + 1 }} days
                                        </small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    @if($totalAmount > 0)
                                        <strong class="text-success">${{ number_format($totalAmount) }}</strong>
                                        <div class="d-flex gap-1 mt-1">
                                            @foreach($paymentMethods as $method)
                                                <span class="badge bg-light text-dark">
                                                    @if($method == 'cash')
                                                        <i class="ri-money-dollar-line"></i> Cash
                                                    @elseif($method == 'card')
                                                        <i class="ri-bank-card-line"></i> Card
                                                    @elseif($method == 'bank')
                                                        <i class="ri-bank-line"></i> Bank
                                                    @else
                                                        {{ ucfirst($method) }}
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                        @if(!empty($tour->parsed_payment_details))
                                            <button class="btn btn-sm btn-outline-info mt-1" data-bs-toggle="modal" data-bs-target="#showPaymentModal{{ $tour->tour_id }}">
                                                <i class="ri-eye-line"></i> View
                                            </button>
                                        @endif
                                    @else
                                        <span class="text-muted">No payment details</span>
                                    @endif
                                </div>
                            </td>
                            <td>
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
                                @if($isActive)
                                    <span class="badge bg-warning">
                                        <i class="ri-play-circle-line me-1"></i>Active
                                    </span> 
                                @elseif($isCompleted)
                                    <span class="badge bg-success">
                                        <i class="ri-checkbox-circle-line me-1"></i>Completed
                                    </span>
                                @else
                                    <span class="badge bg-primary">
                                        <i class="ri-calendar-line me-1"></i>Upcoming
                                    </span>
                                @endif
                            </td>
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
                                            <a class="dropdown-item" href="#" onclick="showPaymentDetails('{{ $tour->tour_id }}')">
                                                <i class="ri-money-dollar-circle-line me-2"></i> Payment Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="viewItinerary('{{ $tour->tour_id }}')">
                                                <i class="ri-map-line me-2"></i> View Itinerary
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        @if($isCompleted)
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="requestFeedbackSingle('{{ $tour->tour_id }}')">
                                                <i class="ri-star-line me-2"></i> Request Feedback
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="generateInvoice('{{ $tour->tour_id }}')">
                                                <i class="ri-file-text-line me-2"></i> Generate Invoice
                                            </a>
                                        </li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="downloadDocuments('{{ $tour->tour_id }}')">
                                                <i class="ri-download-line me-2"></i> Download Documents
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="addPayment('{{ $tour->tour_id }}')">
                                                <i class="ri-add-line me-2"></i> Add Payment
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="sendReceipt('{{ $tour->tour_id }}')">
                                                <i class="ri-mail-send-line me-2"></i> Send Receipt
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
                                    
                                    @php
                                        $all_ids = [33, 34, 37, 38, 124, 125, 128, 129, 130, 132, 133, 134, 135, 136, 137, 138];
                                    @endphp
                                    @if(in_array(auth()->user()->role_id, $all_ids))
                                    <a href="{{ route('tour.itinerary', ['tourId' => Crypt::encrypt($tour->tour_id)]) }}" 
                                       class="btn btn-outline-success btn-sm rounded-pill"
                                       onclick="event.stopPropagation(); window.open(this.href, '_blank'); return false;"
                                       style="text-decoration:none; cursor:pointer; transition: all 0.2s ease;">
                                        <i class="fas fa-calendar-alt"></i> View Itinerary
                                    </a>
                                    @endif
                                    @if(auth()->user()->role_id == 33 ||auth()->user()->role_id == 11|| auth()->user()->role_id == 34 ||auth()->user()->role_id == 37 || auth()->user()->role_id == 38 ||auth()->user()->role_id == 124 || auth()->user()->role_id == 125)
                                    <a href="{{ route('tour.editpackage', Crypt::encrypt($tour->tour_id)) }}" 
                                       class="btn btn-outline-warning btn-sm rounded-pill">
                                        <i class="ri-settings-3-line"></i> Add/Remove Services
                                    </a>
                                    @endif

                                    @if(auth()->user()->role_id == 36 || auth()->user()->role_id == 126 || auth()->user()->role_id == 127 || auth()->user()->role_id == 124 || auth()->user()->role_id == 125)
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#showPaymentModal{{ $tour->tour_id }}">
                                            <i class="fas fa-history me-1"></i> Payment Details
                                        </button>
                                    @else  
                                        @if(!empty($paymentData))
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#showPaymentModal{{ $tour->tour_id }}">
                                                <i class="fas fa-history me-1"></i> Payment Details
                                            </button>
                                        @endif

                                        @if(in_array(auth()->user()->role_id, [11, 12, 24, 28, 33, 37, 38, 128, 129, 130, 135, 136, 138]))
                                            @if($remainingAmount > 0 && !$hasPendingPayments)
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal{{ $tour->tour_id }}" onclick="checkPendingPayments({{ $tour->tour_id }})">
                                                    <i class="fas fa-plus-circle me-1"></i> Add Payment
                                                </button>
                                            @endif
                                        @endif
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
                                <div class="d-flex flex-column">
                                    @if($tour->auto_cancel_date)
                                        <span class="fw-semibold">
                                            <i class="fas fa-calendar-times text-warning me-1"></i>
                                            {{ \Carbon\Carbon::parse($tour->auto_cancel_date)->format('D, M d, Y') }}
                                        </span>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($tour->auto_cancel_date)->format('h:i A') }}
                                        </small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        {{-- <tr>
                            <td colspan="11" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="ri-check-circle-line ri-48px text-muted mb-2"></i>
                                    <h6 class="text-muted">No actual bookings</h6>
                                    <p class="text-muted mb-0">All bookings are in other stages or there are no actual bookings yet.</p>
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

<!-- Service Modals for each tour -->
@foreach($tours as $tour)
    @php
        // Re-fetch orders for this tour for modal rendering
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
            if(isset($svc[$order->type])) {
                $svc[$order->type]++;
                if(!isset($serviceData[$order->type])) {
                    $serviceData[$order->type] = [];
                }
                $serviceData[$order->type][] = $order;
            }
        }
    @endphp

    <!-- Hotel Details Modal -->
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
                                <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('hotel', {{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                            </div>
                        </div>
                    @else
                        <div class="modal-header p-4 border-0" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                            <h5 class="modal-title fw-bold text-white">
                                <i class="ri-hotel-line me-2"></i>
                                Hotel Booking Details - Tour #{{ $tour->tour_id }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('hotel', {{ $tour->tour_id }})" aria-label="Close"></button>
                        </div>
                    @endif
                @else
                    <div class="modal-header p-4 border-0" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                        <h5 class="modal-title fw-bold text-white">
                            <i class="ri-hotel-line me-2"></i>
                            Hotel Booking Details - Tour #{{ $tour->tour_id }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('hotel', {{ $tour->tour_id }})" aria-label="Close"></button>
                    </div>
                    
                @endif
                <div class="modal-body p-4">
                    @if(isset($serviceData['hotel']) && count($serviceData['hotel']) > 0)
                        @foreach($serviceData['hotel'] as $index => $hotelOrder)
                        @php
                            $hotelData = is_string($hotelOrder->data) ? json_decode($hotelOrder->data, true) : $hotelOrder->data;
                        @endphp
                        
                        @if(is_array($hotelData))
                            @foreach($hotelData as $booking)
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
                    <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeServiceModal('hotel', {{ $tour->tour_id }})" style="border-radius: 25px;">
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
                             @foreach($attractionData as $booking)
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
                             @foreach($restaurantData as $booking)
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
                                                                     <i class="ri-restaurant-2-line me-2"></i>{{ 'Menu Item' }}
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
                            @foreach($entryData as $booking)
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
                                    </div>
                                </div>
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
                            @foreach($exitData as $booking)
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
                                    </div>
                                </div>
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

    <!-- Travel Hourly Details Modal -->
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

    <!-- Travel Point Details Modal -->
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
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Local Transport Details Modal -->
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
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

<!-- Payment Modals for each tour -->
@foreach($tours as $tour)
    @php
        // Calculate payment details - following confirmed/definite pattern
        $tourTotalPrice = 0;
        $orders = \App\Models\Order::where('tour_id', $tour->tour_id)->whereNull('deleted_at')->get();
        foreach($orders as $order) {
            if($order->data) {
                $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
                if(is_array($orderData)) {
                    foreach($orderData as $item) {
                        if(isset($item['totalPrice'])) {
                            $tourTotalPrice += (float)$item['totalPrice'];
                        }
                    }
                }
            }
        }
        
        $enquiry = \App\Models\Enquiry::where('tour_id', $tour->tour_id)->where('status', 2)->first();
        $discountAmount = $enquiry ? ($enquiry->actual_amount - $enquiry->amount) : 0;
        
        // Calculate base amount before tax (round up if decimal > 0.5, round down if < 0.5)
        $baseAmount = round($tourTotalPrice) - $discountAmount;
        
        // Calculate tax amount using TaxHelper
        $persons = ($tour->adult ?? 0) + ($tour->child ?? 0);
        $days = \App\Helpers\TaxHelper::calculateDays($tour->check_in_time, $tour->check_out_time);
        
        $taxResult = \App\Helpers\TaxHelper::calculateTourTaxes($baseAmount, $tour->taxes, $persons, $days);
        $taxAmount = $taxResult['total_tax'];
        $taxBreakdown = $taxResult['breakdown'];
        $finalAmount = $baseAmount + $taxAmount;
        
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

    <!-- Show Payment Modal -->
    <div class="modal fade" id="showPaymentModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="showPaymentModalLabel{{ $tour->tour_id }}" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content shadow-lg rounded">
                <div class="modal-header bg-gradient text-white d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 8px 8px 0 0;">
                    <h5 class="modal-title d-flex align-items-center fw-bold" id="showPaymentModalLabel{{ $tour->tour_id }}" style="margin: 0;">
                        <i class="fas fa-file-invoice-dollar me-2" style="font-size: 1.5rem; color: #ffd700;"></i> 
                        <span style="font-size: 1.2rem;">Payment Details - Tour #{{ $tour->tour_id }}</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                </div>
                <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                    @if(is_array($paymentData) && !empty($paymentData))
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-bordered table-hover table-sm">
                                <thead class="table-light sticky-top">
                                    <tr style="font-size: 0.85rem;">
                                        <th class="text-center" style="width: 10%; min-width: 90px;">Payment Date</th>
                                        <th class="text-center" style="width: 10%; min-width: 90px;">Record Date</th>
                                        <th class="text-center" style="width: 12%; min-width: 100px;">Amount (SGD)</th>
                                        <th class="text-center" style="width: 12%; min-width: 100px;">Original Amount</th>
                                        <th class="text-center" style="width: 7%; min-width: 60px;">Currency</th>
                                        <th class="text-center" style="width: 9%; min-width: 75px;">Exchange Rate</th>
                                        <th class="text-center" style="width: 10%; min-width: 80px;">Payment Mode</th>
                                        <th class="text-center" style="width: 12%; min-width: 100px;">Transaction ID</th>
                                        <th class="text-center" style="width: 10%; min-width: 80px;">Remarks</th>
                                        <th class="text-center" style="width: 8%; min-width: 70px;">Status</th>
                                        @if(auth()->user()->role_id == 36 || auth()->user()->role_id == 126 || auth()->user()->role_id == 127)
                                            <th class="text-center" style="width: 8%; min-width: 80px;">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paymentData as $index => $payment)
                                        <tr style="font-size: 0.8rem;">
                                            <td class="text-center py-2">{{ isset($payment['payment_date']) ? \Carbon\Carbon::parse($payment['payment_date'])->format('M d, Y') : 'N/A' }}</td>
                                            <td class="text-center py-2">{{ isset($payment['created_at']) ? \Carbon\Carbon::parse($payment['created_at'])->format('M d, Y') : 'N/A' }}</td>
                                            <td class="text-center py-2 fw-bold text-success">{{ isset($payment['amount']) ? number_format($payment['amount'], 2) : '0.00' }}</td>
                                            <td class="text-center py-2">{{ isset($payment['original_amount']) ? number_format($payment['original_amount'], 2) : number_format($payment['amount'] ?? 0, 2) }}</td>
                                            <td class="text-center py-2">{{ $payment['currency'] ?? 'SGD' }}</td>
                                            <td class="text-center py-2">{{ isset($payment['exchange_rate']) ? number_format($payment['exchange_rate'], 4) : '1.0000' }}</td>
                                            <td class="text-center py-2">
                                                <span class="badge bg-light text-dark" style="font-size: 0.7rem;">{{ ucfirst($payment['payment_type'] ?? 'N/A') }}</span>
                                            </td>
                                            <td class="text-center py-2" style="font-size: 0.75rem;" title="{{ $payment['transaction_id'] ?? 'N/A' }}">
                                                {{ Str::limit($payment['transaction_id'] ?? 'N/A', 15, '...') }}
                                            </td>
                                            <td class="text-center py-2" style="font-size: 0.75rem;" title="{{ $payment['remarks'] ?? 'N/A' }}">
                                                {{ Str::limit($payment['remarks'] ?? 'N/A', 12, '...') }}
                                            </td>
                                            <td class="text-center py-2">
                                                @if(isset($payment['status']))
                                                    @if($payment['status'] == 1)
                                                        <span class="badge bg-success text-white" style="font-size: 0.7rem;">
                                                            <i class="fas fa-check-circle me-1"></i>Verified
                                                        </span>
                                                    @elseif($payment['status'] == 2)
                                                        <span class="badge bg-danger text-white" style="font-size: 0.7rem;">
                                                            <i class="fas fa-times-circle me-1"></i>Declined
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning text-dark" style="font-size: 0.7rem;">
                                                            <i class="fas fa-hourglass-half me-1"></i>Pending
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary text-white" style="font-size: 0.7rem;">Unknown</span>
                                                @endif
                                            </td>
                                            @if(auth()->user()->role_id == 36 || auth()->user()->role_id == 126 || auth()->user()->role_id == 127)
                                                <td class="text-center py-2">
                                                    @if(!isset($payment['status']) || $payment['status'] == 0)
                                                        <div class="d-flex justify-content-center gap-1">
                                                            <button type="button" class="btn btn-xs btn-success" style="font-size: 0.7rem; padding: 2px 6px;" onclick="verifyPayment({{ $tour->tour_id }}, {{ $index }})">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-xs btn-danger" style="font-size: 0.7rem; padding: 2px 6px;" onclick="declinePayment({{ $tour->tour_id }}, {{ $index }})">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    @else
                                                        <span class="text-muted" style="font-size: 0.7rem;">-</span>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No payment records found for this tour.
                        </div>
                    @endif
                </div>
                <div class="modal-footer bg-light d-flex justify-content-end" style="padding: 10px 20px; border-radius: 0 0 8px 8px; flex-shrink: 0;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" style="font-size: 0.85rem;">
                        <i class="fas fa-times me-1"></i>Close
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
                                <!-- Pricing Breakdown -->
                                @if($discountAmount > 0)
                                <div class="row text-center mb-2">
                                    <div class="col-6">
                                        <small class="text-muted">Actual Price</small>
                                        <div class="fw-bold text-secondary">{{ number_format(round($tourTotalPrice), 2) }} SGD</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Discount</small>
                                        <div class="fw-bold text-success">- {{ number_format($discountAmount, 2) }} SGD</div>
                                    </div>
                                </div>
                                <hr class="my-2">
                                @endif
                                <div class="row text-center mb-2">
                                    <div class="col-4">
                                        <small class="text-muted">Base Amount</small>
                                        <div class="fw-bold text-dark">{{ number_format($baseAmount, 2) }} SGD</div>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted" 
                                            @if(!empty($taxBreakdown))
                                                title="{{ \App\Helpers\TaxHelper::formatTaxBreakdown($taxBreakdown) }}"
                                            @endif>
                                            Tax @if(!empty($taxBreakdown))({{ count($taxBreakdown) }})@endif
                                        </small>
                                        <div class="fw-bold text-warning">{{ number_format($taxAmount, 2) }} SGD</div>
                                        @if(!empty($taxBreakdown) && count($taxBreakdown) > 0)
                                            <div style="font-size: 0.7rem; margin-top: 2px;">
                                                @foreach($taxBreakdown as $taxName => $taxVal)
                                                    <div>{{ $taxName }}: {{ number_format($taxVal, 2) }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted">Total Amount</small>
                                        <div class="fw-bold text-primary">{{ number_format($finalAmount, 2) }} SGD</div>
                                    </div>
                                </div>
                                <hr class="my-2">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <small class="text-muted">Paid Amount</small>
                                        <div class="fw-bold text-success">{{ number_format($totalPaid, 2) }} SGD</div>
                                    </div>
                                    <div class="col-6">
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
// Service Modal Functions
function openServiceModal(serviceType, tourId, event) {
    console.log('Opening service modal:', serviceType, 'for tour:', tourId);
    
    if (event) {
        event.preventDefault();
    }
    
    // Construct modal ID
    const modalId = `${serviceType}DetailsModal${tourId}`;
    console.log('Looking for modal element:', modalId);
    
    // Find the modal element
    const modalElement = document.getElementById(modalId);
    console.log('Modal element found:', !!modalElement);
    
    if (!modalElement) {
        console.error('Modal element not found:', modalId);
        
        // Log available modals for debugging
        const availableModals = Array.from(document.querySelectorAll('[id*="DetailsModal"]')).map(el => el.id);
        console.log('Available service modals on page:', availableModals);
        
        // Show user-friendly error
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Modal Not Found',
                text: `Could not find ${serviceType} details modal for tour ${tourId}. Please refresh the page and try again.`,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            alert(`Could not find ${serviceType} details modal for tour ${tourId}. Please refresh the page and try again.`);
        }
        return;
    }
    
    try {
        // Method 1: Try Bootstrap 5 method
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            console.log('Using Bootstrap 5 modal method');
            const modal = new bootstrap.Modal(modalElement, {
                backdrop: 'static',
                keyboard: false
            });
            modal.show();
            console.log('Bootstrap 5 modal show called');
            return;
        }
        
        // Method 2: Try jQuery method (fallback)
        if (typeof $ !== 'undefined' && $.fn.modal) {
            console.log('Using jQuery modal method');
            $(modalElement).modal({
                backdrop: 'static',
                keyboard: false
            });
            $(modalElement).modal('show');
            console.log('jQuery modal show called');
            return;
        }
        
        // Method 3: Manual modal display (last resort)
        console.log('Using manual modal display');
        modalElement.style.display = 'block';
        modalElement.classList.add('show');
        modalElement.setAttribute('aria-hidden', 'false');
        
        // Add backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = `backdrop-${modalId}`;
        document.body.appendChild(backdrop);
        
        // Prevent body scroll
        document.body.classList.add('modal-open');
        
        console.log('Manual modal display applied');
        
    } catch (error) {
        console.error('Error opening modal:', error);
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error',
                text: 'An error occurred while opening the modal. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            alert('An error occurred while opening the modal. Please try again.');
        }
    }
}

function closeServiceModal(serviceType, tourId) {
    const modalId = `${serviceType}DetailsModal${tourId}`;
    const modalElement = document.getElementById(modalId);
    
    if (!modalElement) {
        console.error('Modal element not found for closing:', modalId);
        return;
    }
    
    try {
        // Method 1: Bootstrap 5
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
        
        // Method 2: jQuery
        if (typeof $ !== 'undefined' && $.fn.modal) {
            $(modalElement).modal('hide');
        }
        
        // Method 3: Manual
        modalElement.style.display = 'none';
        modalElement.classList.remove('show');
        modalElement.setAttribute('aria-hidden', 'true');
        
        // Remove backdrop
        const backdrop = document.getElementById(`backdrop-${modalId}`);
        if (backdrop) {
            backdrop.remove();
        }
        
        // Remove any orphaned backdrops
        const allBackdrops = document.querySelectorAll('.modal-backdrop');
        allBackdrops.forEach(backdrop => backdrop.remove());
        
        // Re-enable body scroll
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        
    } catch (error) {
        console.error('Error closing modal:', error);
    }
}

// Legacy function for backwards compatibility
function openHotelModal(tourId) {
    openServiceModal('hotel', tourId);
}

function closeHotelModal(tourId) {
    closeServiceModal('hotel', tourId);
}

// Create a simple data object for tours
const toursData = {
    @foreach($tours as $tour)
    '{{ $tour->tour_id }}': @json($tour->parsed_payment_details ?? []),
    @endforeach
};

// Make sure the function is globally accessible
window.showPaymentDetails = function(tourId) {
    console.log('showPaymentDetails called with tourId:', tourId);
    console.log('Available tours data:', toursData);
    
    try {
        const paymentDetails = toursData[tourId] || [];
        console.log('Found payment details for tour', tourId, ':', paymentDetails);
        
        let content = '<div class="row">';
        
        if (paymentDetails && paymentDetails.length > 0) {
            paymentDetails.forEach((payment, index) => {
                content += `
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Payment #${index + 1}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <strong>Amount:</strong><br>
                                        <span class="text-success fs-5">$${parseFloat(payment.amount || 0).toLocaleString()}</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <strong>Payment Type:</strong><br>
                                        <span class="badge bg-primary">${payment.payment_type || 'N/A'}</span>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <strong>Date:</strong><br>
                                        ${payment.payment_date
                                            ? new Date(payment.payment_date).toLocaleDateString('en-US', {
                                                weekday: 'short', // D
                                                month: 'short',   // M
                                                day: '2-digit',   // d
                                                year: 'numeric'   // Y
                                            })
                                            : 'N/A'}
                                    </div>
                                    <div class="col-sm-6">
                                        <strong>Status:</strong><br>
                                        <span class="badge ${payment.status == 1 ? 'bg-success' : 'bg-warning'}">
                                            ${payment.status == 1 ? 'Confirmed' : 'Pending'}
                                        </span>
                                    </div>
                                </div>
                                ${payment.transaction_id ? `
                                    <hr>
                                    <div>
                                        <strong>Transaction ID:</strong><br>
                                        <code>${payment.transaction_id}</code>
                                    </div>
                                ` : ''}
                                ${payment.remarks ? `
                                    <hr>
                                    <div>
                                        <strong>Remarks:</strong><br>
                                        <p class="text-muted mb-0">${payment.remarks}</p>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            content += '<div class="col-12 text-center"><p class="text-muted">No payment details available for this tour.</p></div>';
        }
        
        content += '</div>';
        
        // Make sure the modal elements exist
        const modalContent = document.getElementById('paymentDetailsContent');
        const modal = document.getElementById('paymentDetailsModal');
        
        if (modalContent && modal) {
            modalContent.innerHTML = content;
            const bootstrapModal = new bootstrap.Modal(modal);
            bootstrapModal.show();
            console.log('Modal should be visible now');
        } else {
            console.error('Modal elements not found:', {
                modalContent: !!modalContent,
                modal: !!modal
            });
            alert('Error: Payment details modal not found. Please refresh the page.');
        }
    } catch (error) {
        console.error('Error in showPaymentDetails:', error);
        alert('Error loading payment details. Please try again.');
    }
}

function printPaymentDetails() {
    const content = document.getElementById('paymentDetailsContent').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Payment Details</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>@media print { .no-print { display: none; } }</style>
            </head>
            <body>
                <div class="container mt-3">
                    <h4>Payment Details</h4>
                    ${content}
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

function viewItinerary(tourId) {
    console.log('Viewing itinerary for tour', tourId);
    // Implementation for viewing itinerary
}

function requestFeedbackSingle(tourId) {
    console.log('Requesting feedback for tour', tourId);
    // Implementation for requesting feedback
}

function generateInvoice(tourId) {
    console.log('Generating invoice for tour', tourId);
    // Implementation for invoice generation
}

function downloadDocuments(tourId) {
    console.log('Downloading documents for tour', tourId);
    // Implementation for document download
}

function addPayment(tourId) {
    console.log('Adding payment for tour', tourId);
    // Implementation for adding payment
}

function sendReceipt(tourId) {
    console.log('Sending receipt for tour', tourId);
    // Implementation for sending receipt
}

function requestFeedback() {
    const selectedTours = document.querySelectorAll('.row-checkbox:checked');
    if (selectedTours.length === 0) {
        alert('Please select at least one completed booking to request feedback.');
        return;
    }
    
    console.log('Requesting feedback for', selectedTours.length, 'bookings');
}

function generateReports() {
    const selectedTours = document.querySelectorAll('.row-checkbox:checked');
    if (selectedTours.length === 0) {
        alert('Please select at least one booking to generate reports.');
        return;
    }
    
    console.log('Generating reports for', selectedTours.length, 'bookings');
}

function filterTable() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    const paymentFilter = document.getElementById('paymentFilter')?.value || '';
    const destinationFilter = document.getElementById('destinationFilter')?.value || '';
    const agentFilter = document.getElementById('agentFilter')?.value || '';
    const timeFilter = document.getElementById('timeFilter')?.value || '';
    const dateStart = document.getElementById('dateRangeStart')?.value || '';
    const dateEnd = document.getElementById('dateRangeEnd')?.value || '';
    
    const rows = document.querySelectorAll('#toursTable tbody tr');
    table.rows('.dt-hasChild').every(function() {
        if (this.child.isShown()) this.child.hide();
        $(this.node()).removeClass('dt-hasChild');
    });
    rows.forEach(row => {
        if (row.cells.length === 1) return; // Skip empty state row
        
        const tourDetails = row.cells[1]?.textContent.toLowerCase() || '';
        const destination = row.cells[3]?.querySelector('.fw-medium')?.textContent || '';
        const agent = row.cells[5]?.querySelector('.fw-medium')?.textContent || '';
        const status = row.cells[9]?.querySelector('.badge')?.textContent.toLowerCase() || '';
        const travelDates = row.cells[7]?.textContent.toLowerCase() || '';
        const paymentBadges = row.cells[8]?.querySelectorAll('.badge') || [];
        const createdAt = row.getAttribute('data-created-at');
        const updatedAt = row.getAttribute('data-updated-at');
        
        let show = true;
        
        // Date range filtering (check both created_at and updated_at)
        if (dateStart && dateEnd && (createdAt || updatedAt)) {
            const s = new Date(dateStart + 'T00:00:00');
            const e = new Date(dateEnd + 'T23:59:59');
            let dateInRange = false;
            
            // Check created_at if available
            if (createdAt) {
                const createdDate = new Date(createdAt + 'T00:00:00');
                if (createdDate >= s && createdDate <= e) {
                    dateInRange = true;
                }
            }
            
            // Check updated_at if available and created_at didn't match
            if (!dateInRange && updatedAt) {
                const updatedDate = new Date(updatedAt + 'T00:00:00');
                if (updatedDate >= s && updatedDate <= e) {
                    dateInRange = true;
                }
            }
            
            if (!dateInRange) {
                show = false;
            }
        }
        
        if (searchTerm && !tourDetails.includes(searchTerm)) {
            show = false;
        }
        
        if (statusFilter && !status.includes(statusFilter.toLowerCase())) {
            show = false;
        }
        
        if (paymentFilter) {
            let hasPaymentType = false;
            paymentBadges.forEach(badge => {
                if (badge.textContent.toLowerCase().includes(paymentFilter)) {
                    hasPaymentType = true;
                }
            });
            if (!hasPaymentType) show = false;
        }
        
        if (destinationFilter && destination !== destinationFilter) {
            show = false;
        }
        
        if (agentFilter && agent !== agentFilter) {
            show = false;
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
    const totalRevenue = visibleRows.reduce((sum, r) => sum + parseFloat(r.getAttribute('data-revenue') || '0'), 0);
    const activeCount = visibleRows.filter(r => r.getAttribute('data-status') === 'Active').length;
    const completedCount = visibleRows.filter(r => r.getAttribute('data-status') === 'Completed').length;

    // Update counts and labels
    const countEl = document.getElementById('rangeCount');
    const labelEl = document.getElementById('rangeLabel');
    const statActual = document.getElementById('statActualCount');
    const statActualLabel = document.getElementById('statActualLabel');
    const statRevenue = document.getElementById('statRevenueCount');
    const statRevenueLabel = document.getElementById('statRevenueLabel');
    const statActive = document.getElementById('statActiveCount');
    const statActiveLabel = document.getElementById('statActiveLabel');
    const statCompleted = document.getElementById('statCompletedCount');
    const statCompletedLabel = document.getElementById('statCompletedLabel');

    if (countEl) countEl.textContent = rangeCount;
    if (statActual) statActual.textContent = rangeCount;
    if (statRevenue) statRevenue.textContent = '$' + Math.round(totalRevenue).toLocaleString();
    if (statActive) statActive.textContent = activeCount;
    if (statCompleted) statCompleted.textContent = completedCount;

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
        if (statActualLabel) statActualLabel.textContent = `Actual - ${label}`;
        if (statRevenueLabel) statRevenueLabel.textContent = `Revenue - ${label}`;
        if (statActiveLabel) statActiveLabel.textContent = `Active - ${label}`;
        if (statCompletedLabel) statCompletedLabel.textContent = `Completed - ${label}`;
    } else {
        const month = new Date().toLocaleString('default', { month: 'long' });
        if (labelEl) labelEl.textContent = month;
        if (statActualLabel) statActualLabel.textContent = `${month} Actual`;
        if (statRevenueLabel) statRevenueLabel.textContent = `${month} Revenue`;
        if (statActiveLabel) statActiveLabel.textContent = `${month} Active`;
        if (statCompletedLabel) statCompletedLabel.textContent = `${month} Completed`;
    }
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('paymentFilter').value = '';
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
    const statusFilter = document.getElementById('statusFilter');
    const paymentFilter = document.getElementById('paymentFilter');
    const destinationFilter = document.getElementById('destinationFilter');
    const agentFilter = document.getElementById('agentFilter');
    const timeFilter = document.getElementById('timeFilter');
    const dateRange = document.getElementById('dateRange');
    const dateRangeStart = document.getElementById('dateRangeStart');
    const dateRangeEnd = document.getElementById('dateRangeEnd');
    
    // Add event listeners
    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (statusFilter) statusFilter.addEventListener('change', filterTable);
    if (paymentFilter) paymentFilter.addEventListener('change', filterTable);
    if (destinationFilter) destinationFilter.addEventListener('change', filterTable);
    if (agentFilter) agentFilter.addEventListener('change', filterTable);
    if (timeFilter) timeFilter.addEventListener('change', filterTable);
    // Date range picker will be initialized in scripts section where jQuery is available
    
    // Apply initial filter on page load to show today's data
    filterTable();
});

// Test function to verify modal works
window.testModal = function() {
    const modal = document.getElementById('paymentDetailsModal');
    const modalContent = document.getElementById('paymentDetailsContent');
    
    if (modal && modalContent) {
        modalContent.innerHTML = '<div class="text-center"><h4>Test Modal</h4><p>Modal is working correctly!</p></div>';
        new bootstrap.Modal(modal).show();
        console.log('Test modal opened successfully');
    } else {
        console.error('Modal elements not found');
        alert('Modal elements not found!');
    }
}

// Log available tours data on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded. Tours data available:', Object.keys(toursData).length, 'tours');
    console.log('Modal element exists:', !!document.getElementById('paymentDetailsModal'));
    console.log('Modal content element exists:', !!document.getElementById('paymentDetailsContent'));
});

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
        exchangeRateInput.value = 1.00;
        currencySymbol.textContent = 'SGD';
        conversionInfoContainer.style.display = 'none';
    }
}

function fetchExchangeRate(currency, tourId) {
    // This would normally call your exchange rate API
    fetch(`/api/exchange-rate?from=SGD&to=${currency}`)
        .then(response => response.json())
        .then(data => {
            const exchangeRateInput = document.getElementById(`exchange_rate${tourId}`);
            const rateSourceText = document.getElementById(`rateSourceText${tourId}`);
            if (data.rate) {
                exchangeRateInput.value = data.rate.toFixed(4);
                rateSourceText.textContent = 'API';
            } else {
                rateSourceText.textContent = 'Manual';
            }
        })
        .catch(error => {
            console.error('Error fetching exchange rate:', error);
            const rateSourceText = document.getElementById(`rateSourceText${tourId}`);
            rateSourceText.textContent = 'Manual';
        });
}

function recalculateFromExchangeRate(tourId) {
    const exchangeRate = parseFloat(document.getElementById(`exchange_rate${tourId}`).value) || 1;
    const paymentAmount = parseFloat(document.getElementById(`payment_amount${tourId}`).value) || 0;
    const remainingAmount = parseFloat(document.getElementById(`amount${tourId}`).value) || 0;
    
    const equivalentSGD = paymentAmount / exchangeRate;
    const conversionInfo = document.getElementById(`conversionInfo${tourId}`);
    
    if (conversionInfo) {
        conversionInfo.innerHTML = `<i class="fas fa-info-circle me-1"></i>Amount in SGD: ${equivalentSGD.toFixed(2)}`;
    }
    
    validatePaymentAmountInput(tourId);
}

function validatePaymentAmountInput(tourId) {
    const paymentAmount = parseFloat(document.getElementById(`payment_amount${tourId}`).value) || 0;
    const maxSGDAmount = parseFloat(document.getElementById(`amount${tourId}`).value) || 0;
    const selectedCurrency = document.getElementById(`currency${tourId}`).value;
    const exchangeRate = parseFloat(document.getElementById(`exchange_rate${tourId}`).value) || 1;
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

// Define base URL using Laravel's URL helper
const BASE_URL = "{{ url('/') }}";
console.log('Base URL:', BASE_URL);

function closePaymentModal(tourId) {
    console.log('Attempting to close payment modal for tour:', tourId);
    
    // Method 1: Close using showPaymentModal
    const modal = document.getElementById(`showPaymentModal${tourId}`);
    console.log('Modal element found:', !!modal);
    
    if (modal) {
        // Method 1a: Bootstrap 5
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
        
        // Use jQuery AJAX with proper CSRF token handling
        $.ajax({
            url: `${BASE_URL}/tour/${tourId}/verify-payment`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                payment_index: paymentIndex
            },
            success: function(response) {
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
        
        // Use jQuery AJAX with proper CSRF token handling
        $.ajax({
            url: `${BASE_URL}/tour/${tourId}/decline-payment`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                payment_index: paymentIndex
            },
            success: function(response) {
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
            const currencySelect = document.getElementById(`currency${tourId}`);
            if (currencySelect) {
                currencySelect.value = 'SGD';
                updatePaymentAmountEnhanced(tourId, 'SGD');
            }
            
            // Hide validation errors
            const validationError = document.getElementById(`paymentValidationError${tourId}`);
            if (validationError) {
                validationError.style.display = 'none';
            }
        });
    });
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
    var table;
    function initializeDataTable() {
        // Check if DataTable is already initialized
        if ($.fn.DataTable.isDataTable('.datatables-basic')) {
            $('.datatables-basic').DataTable().destroy();
        }
        
        // Initialize DataTable with export buttons
        table = $('.datatables-basic').DataTable({
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
            // order: [[7, 'desc']], // Sort by Travel Dates column (index 7) in descending order
            columnDefs: [
                {
                    targets: [11], // Actions column (index 11)
                    orderable: false,
                    searchable: false
                },
                {
                    targets: [4], // Guests column (index 4)
                    orderable: false
                },
                {
                    targets: [8, 9, 10], // Payment Details, Payment Status and Status columns (index 8, 9, 10)
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

