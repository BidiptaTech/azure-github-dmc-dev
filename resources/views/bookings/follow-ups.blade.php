@extends('layouts.layout')
@section('title', 'Follow Ups')
@extends('layouts.datatablecss')

<!-- Add SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<!-- Add SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Select2 Bootstrap Integration */
    .select2-container--default .select2-selection--single {
        height: 50px;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 50px;
        padding-left: 12px;
        padding-right: 50px; /* Space for clear button and arrow */
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 48px;
        right: 10px;
    }
    /* Style and position the clear button (X icon) */
    .select2-container--default .select2-selection--single .select2-selection__clear {
        position: absolute;
        right: 35px; /* Position it before the dropdown arrow */
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        font-size: 18px;
        color: #6c757d;
    }
    .select2-container--default .select2-selection--single .select2-selection__clear:hover {
        color: #dc3545;
    }

    /* Compact table styles (similar to new-enquiries) */
    #toursTable {
        font-size: 0.875rem;
    }

    #toursTable thead th {
        padding: 0.5rem 0.75rem;
        font-size: 0.8125rem;
        font-weight: 600;
        white-space: nowrap;
    }

    #toursTable tbody td {
        padding: 0.5rem 0.75rem;
        vertical-align: middle;
    }

    #toursTable tbody tr {
        height: auto;
        min-height: 50px;
    }

    /* Compact badges in Services / status columns */
    #toursTable .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        margin: 0.1rem 0.15rem;
        font-weight: 500;
    }

    /* Compact icons */
    #toursTable i {
        font-size: 1rem;
    }

    /* Compact text in cells */
    #toursTable .fw-medium,
    #toursTable .fw-bold {
        font-size: 0.875rem;
    }

    #toursTable small {
        font-size: 0.75rem;
    }

    /* Compact buttons in table (keep labels visible) */
    #toursTable .btn-sm {
        padding: 0.25rem 0.55rem;
        font-size: 0.78rem;
        height: auto;
        white-space: nowrap;
    }

    /* Compact guests icons section */
    #toursTable .d-flex.gap-3 {
        gap: 0.75rem !important;
    }

    /* Compact services badges container */
    #toursTable .d-flex.gap-2.flex-wrap {
        gap: 0.35rem !important;
    }

    /* Reduce spacing in tour details */
    #toursTable .d-flex.flex-column {
        gap: 0.15rem;
    }

    /* Compact muted text */
    #toursTable .text-muted {
        font-size: 0.7rem;
    }

    /* Compact check-in/check-out / last contact */
    #toursTable .d-flex.flex-column small {
        line-height: 1.3;
    }

    /* Compact Service Modals (same as new-enquiries) */
    .service-modal-compact .modal-dialog {
        max-width: 780px;
        width: 90%;
        margin: 1.25rem auto;
    }

    .service-modal-compact .modal-header {
        height: auto;
        min-height: 90px;
        padding: 0.5rem 0.9rem !important;
    }

    .service-modal-compact .modal-body {
        padding: 0.75rem 0.9rem !important;
    }

    .service-modal-compact .modal-footer {
        padding: 0.5rem 0.9rem !important;
    }

    .service-modal-compact h3 {
        font-size: 1.05rem;
        margin-bottom: 0.25rem;
    }

    .service-modal-compact h4,
    .service-modal-compact h5 {
        font-size: 0.95rem;
        margin-bottom: 0.2rem;
    }

    .service-modal-compact h6 {
        font-size: 0.85rem;
        margin-bottom: 0.15rem;
    }

    .service-modal-compact .card-header {
        padding: 0.45rem 0.75rem !important;
    }

    .service-modal-compact .card-body {
        padding: 0.6rem 0.75rem !important;
    }

    .service-modal-compact .row.mb-4 {
        margin-bottom: 0.55rem !important;
    }

    .service-modal-compact .bg-white.rounded.p-3,
    .service-modal-compact .bg-white.rounded-3.p-4,
    .service-modal-compact .bg-white.rounded.p-3.shadow-sm {
        padding: 0.6rem 0.75rem !important;
    }

    .service-modal-compact small {
        font-size: 0.7rem;
    }

    .service-modal-compact .fs-3,
    .service-modal-compact .fs-4 {
        font-size: 1rem !important;
    }

    .service-modal-compact .fs-5 {
        font-size: 0.9rem !important;
    }

    .service-modal-compact .fs-2 {
        font-size: 1.1rem !important;
    }

    .service-modal-compact .badge {
        padding: 0.2rem 0.45rem;
        font-size: 0.7rem;
    }

    .service-modal-compact .d-flex.align-items-center.mb-3 {
        margin-bottom: 0.4rem !important;
    }

    .service-modal-compact .mb-3 {
        margin-bottom: 0.45rem !important;
    }

    /* Loading spinner animation for cancel button */
    .spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>

@section('content')
@php
    if (!function_exists('extractOrderTotals')) {
        function extractOrderTotals($payload)
        {
            if (is_object($payload)) {
                $payload = (array) $payload;
            }

            if (!is_array($payload)) {
                return 0;
            }

            $priorityKeys = ['totalPrice', 'total_price', 'price', 'amount'];
            foreach ($priorityKeys as $key) {
                if (isset($payload[$key]) && is_numeric($payload[$key])) {
                    return (float) $payload[$key];
                }
            }

            $sum = 0;
            foreach ($payload as $value) {
                if (is_array($value) || is_object($value)) {
                    $sum += extractOrderTotals($value);
                }
            }

            return $sum;
        }
    }
@endphp
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: {!! json_encode(session('success')) !!},
                timer: 2500,
                showConfirmButton: false
            });
        });
    </script>
@endif
@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'error',
                title: 'Oops',
                text: {!! json_encode(session('error')) !!},
                timer: 3000,
                showConfirmButton: false
            });
        });
    </script>
@endif
<style>
    .new-enq-header-bar { background: linear-gradient(135deg, #f8f9fc 0%, #fff 100%); border-radius: 0.5rem; border: 1px solid rgba(105, 108, 255, 0.08); }
    .new-enq-stat-item { transition: transform 0.15s ease, box-shadow 0.15s ease; min-height: 72px; padding: 0.65rem 0.75rem !important; }
    .new-enq-stat-item:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
    .new-enq-stat-item .stat-value { font-size: 1.25rem; font-weight: 600; letter-spacing: -0.02em; line-height: 1; display: block; min-height: 1.5rem; }
    .new-enq-stat-item .stat-label { display: block; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.04em; opacity: 0.85; margin-top: 0.15rem; line-height: 1.3; }
    .new-enq-stats-grid .col { display: flex; }
    .new-enq-stats-grid .col > div { width: 100%; }
    .new-enq-filter-bar { background: #fff; border-radius: 0.5rem; border: 1px solid #e7e9ed; }
    .new-enq-filter-bar .form-control, .new-enq-filter-bar .form-control-sm,
    .new-enq-filter-bar .form-select, .new-enq-filter-bar .form-select.form-select-sm { font-size: 0.8125rem; height: 38px; }
    .new-enq-filter-bar .select2-container--default .select2-selection--single { height: 38px !important; min-height: 38px !important; border-radius: 0.375rem; }
    .new-enq-filter-bar .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px !important; padding-left: 10px; padding-right: 32px; }
    .new-enq-filter-bar .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; right: 8px; }
    .new-enq-filter-bar .select2-container--default .select2-selection--single .select2-selection__clear { right: 32px; }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Compact Header + Stats Bar -->
    <div class="new-enq-header-bar p-3 mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <h4 class="fw-bold mb-0" style="font-size: 1.25rem;">
                    <span class="text-muted fw-light">Bookings /</span> Follow Ups
                </h4>
                <span class="text-muted d-none d-md-inline" style="font-size: 0.875rem;">Manage prospect enquiries, tentative bookings and follow up communications</span>
                <span class="badge bg-light text-info border border-info border-opacity-25 px-2 py-1" style="font-size: 0.75rem;">
                    <i class="ri-phone-line me-1"></i><span id="rangeCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</span> <span id="rangeLabel">{{ date('F') }}</span>
                </span>
            </div>
            <div class="row g-2 new-enq-stats-grid flex-grow-1">
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-info rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-phone-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statFollowUpsCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</span><span class="stat-label text-muted" id="statFollowUpsLabel">{{ date('F') }} Follow Ups</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-primary rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-user-search-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statProspectsCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->where('tour_status', 'Prospect')->count() }}</span><span class="stat-label text-muted" id="statProspectsLabel">{{ date('F') }} Prospects</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-warning rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-bookmark-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statTentativeCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->where('tour_status', 'Tentative')->count() }}</span><span class="stat-label text-muted" id="statTentativeLabel">{{ date('F') }} Tentative</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-danger rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-alarm-warning-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statOverdueCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->where('updated_at', '<', now()->subDays(7))->count() }}</span><span class="stat-label text-muted" id="statOverdueLabel">{{ date('F') }} Overdue</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compact Filters -->
    <div class="new-enq-filter-bar card mb-3 border-0 shadow-sm">
        <div class="card-body py-2 px-3">
            <div class="row g-2 align-items-end">
                <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                    <span class="text-muted fw-medium d-flex align-items-center gap-1" style="font-size: 0.8rem;"><i class="ri-filter-3-line"></i> Filters</span>
                    <button class="btn btn-sm btn-outline-secondary py-1 px-2" onclick="resetFilters()" title="Reset filters">
                        <i class="ri-refresh-line me-1"></i> Reset
                    </button>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <label class="form-label mb-0 small text-muted">Search</label>
                    <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Tour ID, Display ID...">
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <label class="form-label mb-0 small text-muted">Status</label>
                    <select class="form-select form-select-sm" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="Prospect">Prospect</option>
                        <option value="Tentative">Tentative</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <label class="form-label mb-0 small text-muted">Destination</label>
                    <select class="form-select form-select-sm" id="destinationFilter">
                        <option value="">All Destinations</option>
                        @php
                            $allDestinations = [];
                            foreach($tours as $tour) {
                                if($tour->destination) {
                                    $destinations = array_map('trim', explode(',', $tour->destination));
                                    $allDestinations = array_merge($allDestinations, $destinations);
                                }
                            }
                            $uniqueDestinations = array_unique(array_filter($allDestinations));
                            sort($uniqueDestinations);
                        @endphp
                        @foreach($uniqueDestinations as $destination)
                            <option value="{{ $destination }}">{{ $destination }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <label class="form-label mb-0 small text-muted">Agent</label>
                    <select class="form-select form-select-sm" id="agentFilter">
                        <option value="">All Agents</option>
                        @foreach($tours->where('agent_name', '!=', null)->pluck('agent_name', 'agent_id')->unique() as $agentId => $agentName)
                            <option value="{{ $agentName }}">{{ $agentName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <label class="form-label mb-0 small text-muted">Start Date</label>
                    <input type="date" class="form-control form-control-sm" id="startDateFilter" max="{{ now()->toDateString() }}" value="{{ now()->startOfMonth()->toDateString() }}">
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <label class="form-label mb-0 small text-muted">End Date</label>
                    <input type="date" class="form-control form-control-sm" id="endDateFilter" max="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}">
                </div>
            </div>
        </div>
    </div>

    <!-- Tours Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Follow Up List <span id="filterResultsBadge" class="badge bg-primary ms-2" style="display: none;"></span></h5>
            <div class="d-flex gap-2">
                {{-- <button class="btn btn-sm btn-outline-warning" onclick="scheduleFollowUp()">
                    <i class="ri-calendar-schedule-line me-1"></i> Schedule Follow Up
                </button> --}}
                <div class="dropdown">
                    <button class="btn btn-warning btn-sm dropdown-toggle" type="button" id="exportDropdown"
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
                            <th>Travel Dates</th>
                            <th>Guests</th>
                            <th>Services</th>
                            <th>Agent</th>
                            <th>Created By</th>
                            <th>Status</th>
                            <th>Follow Up Status</th>
                            <th>Last Contact</th>
                            @php
                                $role = [11, 33, 37, 38, 128, 129, 130, 134, 135, 136, 138];
                            @endphp
                            @if(in_array(auth()->user()->role_id, $role))
                                <th>Agent Negotiation</th>
                                <th>Negotiation</th>
                            @endif
                            <th>Actions</th>
                            <th>Created At</th>
                            <th>Auto Cancel Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tours as $key => $tour)
                        <tr 
                            class="{{ $tour->updated_at < now()->subDays(7) ? 'table-warning' : '' }}"
                            data-updated-at="{{ optional($tour->updated_at)->toDateString() }}"
                            data-created-at="{{ optional($tour->created_at)->toDateString() }}"
                            data-tour-status="{{ $tour->tour_status ?? '' }}"
                        >
                            {{-- <td>
                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $tour->tour_id }}">
                            </td> --}}
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-primary">{{ $tour->display_id }}</strong>
                                    <small class="text-muted">Tour ID: #{{ $tour->tour_id }}</small>
                                    @if($tour->multi_enq_id)
                                        <small class="text-info">Multi: {{ $tour->multi_enq_id }}</small>
                                    @endif
                                    @if($tour->tour_type)
                                        <small class="text-white" style="display: inline-block; padding: 2px 8px; background: #3b82f6; border-radius: 4px; font-weight: 500;">
                                            {{ $tour->tour_type }}
                                        </small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ $tour->destination ?? 'N/A' }}</span>
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
                                    @elseif(!$tour->check_in_time && !$tour->check_out_time)
                                        <span class="text-muted">Not scheduled</span>
                                    @endif
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
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    @php
                                        // Fetch orders for this tour with bookingType = enquiry
                                        $orders = \App\Models\Order::where('tour_id', $tour->tour_id)->where('bookingType', 'enquiry')->get();
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
                                        $ordersTotalAmount = 0;
                                        
                                        foreach($orders as $order) {
                                            if(isset($svc[$order->type])) {
                                                $svc[$order->type]++;
                                                if(!isset($serviceData[$order->type])) {
                                                    $serviceData[$order->type] = [];
                                                }
                                                $serviceData[$order->type][] = $order;
                                            }
                                            $orderPayload = is_string($order->data) ? json_decode($order->data, true) : $order->data;
                                            $ordersTotalAmount += extractOrderTotals($orderPayload);
                                        }
                                        $ordersTotalAmount = round($ordersTotalAmount, 2);
                                        
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
                                            <span class="badge bg-light text-dark border" style="cursor: pointer;" 
                                                  onclick="openServiceModal('{{ $key }}', {{ $tour->tour_id }}, event)">
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
                                    @endforeach
                                    @if(array_sum(array_map('intval', $svc)) === 0)
                                        <span class="text-muted">No services</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    @if($tour->agent_name)
                                        <span class="fw-medium">{{ $tour->agent_name }}</span>
                                        <small class="text-muted">
                                            <i class="fas fa-building me-1"></i>
                                            {{ $tour->agent_company_name ?? 'N/A' }}
                                        </small>
                                    @else
                                        <span class="text-muted">No agent assigned</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ $tour->created_by_name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td>
                                @if($tour->tour_status == 'Prospect')
                                    <span class="badge bg-info">
                                        <i class="ri-user-search-line me-1"></i>Prospect
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="ri-bookmark-line me-1"></i>Tentative
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($tour->updated_at < now()->subDays(7))
                                    <span class="badge bg-danger">
                                        <i class="ri-alarm-warning-line me-1"></i>Overdue
                                    </span>
                                @elseif($tour->updated_at < now()->subDays(3))
                                    <span class="badge bg-warning">
                                        <i class="ri-time-line me-1"></i>Due Soon
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="ri-check-line me-1"></i>On Track
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $tour->updated_at->format('D, M d, Y') }}</span>
                                    <small class="text-muted">{{ $tour->updated_at->diffForHumans() }}</small>
                                </div>
                            </td>
                            @php
                                $latestCommentAmount = $tour->enquiry_comment_amount ?? null;
                                $latestCommentRemark = $tour->enquiry_comment ?? '';
                                $hasAgentComment = $tour->enquiry_comment && strtolower($tour->enquiry_comment_sender_type ?? '') === 'agent';
                                
                                // Get first enquiry for discount calculation
                                $frstenquiry = \App\Models\Enquiry::where('tour_id', $tour->tour_id)->first();
                                $first_enquiry_actual_amount = $frstenquiry->actual_amount ?? 0;
                                
                                // Get latest enquiry
                                $enquiry = \App\Models\Enquiry::where('tour_id', $tour->tour_id)->latest()->first();
                                
                                // Calculate total tour price from ALL bookings with status 1 or 3
                                // Includes: base price + transfer price + guide price (for attractions)
                                $tourTotalPrice = 0;
                                foreach ($tour->booking as $booking) {
                                    if (in_array($booking->status, [1, 3])) {
                                        $data = is_string($booking->data) ? json_decode($booking->data, true) : $booking->data;
                                        if (is_array($data)) {
                                            foreach ($data as $item) {
                                                $itemPrice = (float) ($item['totalPrice'] ?? $item['price'] ?? 0);
                                                
                                                // Add transfer price if exists
                                                $transferPrice = 0;
                                                if (isset($item['transfer_options']['cost']) && $item['transfer_options']['cost'] > 0) {
                                                    $transferPrice = (float) $item['transfer_options']['cost'];
                                                }
                                                
                                                // Add guide price if exists (for attractions)
                                                $guidePrice = 0;
                                                if (isset($item['guide_options']['total_price']) && $item['guide_options']['total_price'] > 0) {
                                                    $guidePrice = (float) $item['guide_options']['total_price'];
                                                }
                                                
                                                $tourTotalPrice += $itemPrice + $transferPrice + $guidePrice;
                                            }
                                        }
                                    }
                                }
                                
                                // Calculate discount from enquiry
                                $enquiry_amount = $enquiry->amount ?? 0;
                                $discount = $first_enquiry_actual_amount - $enquiry_amount;
                                
                                // Actual Amount = Total of all booking prices (updates when service added)
                                $currentActualAmount = ceil($tourTotalPrice);
                                
                                // Negotiated Amount = Total booking prices - discount
                                $settlementAmount = ceil($tourTotalPrice) - $discount;
                                
                                $lastAgentAmount = $hasAgentComment ? $settlementAmount : null;
                                $lastOfferAmount = $lastAgentAmount ?? $settlementAmount;
                                $lastOfferRemark = $latestCommentRemark;
                            @endphp
                            @if(in_array(auth()->user()->role_id, $role))
                            <td>
                                <button 
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    data-tour-id="{{ $tour->tour_id }}"
                                    data-display-id="{{ e($tour->display_id) }}"
                                    data-actual="{{ $currentActualAmount ?? 0 }}"
                                    data-last-amount="{{ $lastOfferAmount ?? '' }}"
                                    data-last-comment="{{ e($lastOfferRemark) }}"
                                    data-tour-status="{{ e($tour->tour_status) }}"
                                    data-negotiation-locked="{{ $hasAgentComment ? '1' : '0' }}"
                                    onclick="openAgentNegotiationModal(this)"
                                    {{ $hasAgentComment ? 'disabled' : '' }}
                                >
                                    Negotiate by Agent
                                </button>
                            </td>
                            <td>
                                @if($hasAgentComment)
                                    <button 
                                        type="button"
                                        class="btn btn-sm btn-warning"
                                        data-tour-id="{{ $tour->tour_id }}"
                                        data-enquiry-id="{{ $enquiry->enquiry_id ?? '' }}"
                                        data-price="{{ $settlementAmount }}"
                                        data-actual="{{ $currentActualAmount }}"
                                        data-discount="{{ $discount }}"
                                        data-comment="{{ e($tour->enquiry_comment ?? '') }}"
                                        onclick="openFollowupModal(this, '{{ route('update-price-comment') }}')"
                                    >
                                        Check Negotiation
                                    </button>
                                @elseif($tour->enquiry_comment && strtolower($tour->enquiry_comment_sender_type ?? '') === "om")
                                    <span class="badge bg-warning">Waiting for agent response</span>
                                @else
                                    <span class="text-muted">No negotiation</span>
                                @endif
                            </td>
                            @endif
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
                                            <a class="dropdown-item" href="#" onclick="followUpNow('{{ $tour->tour_id }}')">
                                                <i class="ri-phone-line me-2"></i> Follow Up Now
                                            </a>
                                        </li>
                                        @if($tour->tour_status == 'Prospect')
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="convertToTentative('{{ $tour->tour_id }}')">
                                                <i class="ri-bookmark-line me-2"></i> Mark as Tentative
                                            </a>
                                        </li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="convertToConfirmed('{{ $tour->tour_id }}')">
                                                <i class="ri-check-double-line me-2"></i> Mark as Confirmed
                                            </a>
                                        </li>
                                        @if($tour->tour_status == 'Tentative')
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="requestPayment('{{ $tour->tour_id }}')">
                                                <i class="ri-money-dollar-circle-line me-2"></i> Request Payment
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="extendDeadline('{{ $tour->tour_id }}')">
                                                <i class="ri-calendar-schedule-line me-2"></i> Extend Deadline
                                            </a>
                                        </li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="scheduleCallback('{{ $tour->tour_id }}')">
                                                <i class="ri-calendar-schedule-line me-2"></i> Schedule Callback
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="markAsLost('{{ $tour->tour_id }}')">
                                                <i class="ri-close-line me-2"></i> Mark as Lost
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td> --}}
                            <td>
                                <div class="d-flex gap-2">
                                    @if(auth()->user()->role_id == 33 ||auth()->user()->role_id == 11 || auth()->user()->role_id == 34 ||auth()->user()->role_id == 37 || auth()->user()->role_id == 38 ||auth()->user()->role_id == 124 || auth()->user()->role_id == 125 || in_array(auth()->user()->role_id, [128, 129, 130, 131, 132, 134, 135, 136, 137, 138]))
                                    <a href="{{ route('single-tour-package.edit', Crypt::encrypt($tour->tour_id)) }}"
                                       class="btn btn-outline-success btn-sm rounded-pill">
                                        <i class="ri-pencil-line"></i> Edit
                                    </a>
                                    @endif
                                    <a href="{{ route('bookings.view-tour', Crypt::encrypt($tour->tour_id)) }}" 
                                       class="btn btn-outline-primary btn-sm rounded-pill">
                                        <i class="ri-eye-line"></i> Audit Trail
                                    </a>
                                    <a href="{{ route('tour.itinerary.pdf', ['tourId' => $tour->tour_id]) }}" 
                                       class="btn btn-outline-secondary btn-sm rounded-pill"
                                       target="_blank">
                                        <i class="ri-file-download-line me-1"></i> Download Quotation
                                    </a>
                                    @if($tour->tour_status == 'Tentative')
                                        @php
                                            $proformaInvoice = \App\Models\Invoice::where('tour_id', $tour->tour_id)
                                                ->where('invoice_type', 'proforma')
                                                ->whereNull('deleted_at')
                                                ->first();
                                            $finalInvoice = \App\Models\Invoice::where('tour_id', $tour->tour_id)
                                                ->where('invoice_type', 'final')
                                                ->whereNull('deleted_at')
                                                ->first();
                                        @endphp
                                        @if($proformaInvoice)
                                            <a href="{{ route('invoices.download', Crypt::encrypt($proformaInvoice->invoice_id)) }}" 
                                               class="btn btn-outline-info btn-sm rounded-pill"
                                               target="_blank"
                                               title="Download Proforma Invoice (Price Breakup)">
                                                <i class="ri-file-paper-line me-1"></i> Proforma Invoice(Price Breakup)
                                            </a>
                                            <a href="{{ route('invoices.download-price-only', Crypt::encrypt($proformaInvoice->invoice_id)) }}" 
                                               class="btn btn-outline-primary btn-sm rounded-pill"
                                               target="_blank"
                                               title="Download Proforma Invoice (Package Price Only)">
                                                <i class="ri-file-download-line me-1"></i> Proforma Invoice(Package Price Only)
                                            </a>
                                        @else
                                            <form action="{{ route('invoices.generate-proforma', $tour->tour_id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-outline-info btn-sm rounded-pill"
                                                        title="Generate Proforma Invoice">
                                                    <i class="ri-file-add-line me-1"></i> Generate Proforma
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                    <button onclick="cancelTour('{{ Crypt::encrypt($tour->tour_id) }}', '{{ $tour->display_id }}')" 
                                            class="btn btn-outline-danger btn-sm rounded-pill" 
                                            id="cancel-btn-{{ $tour->tour_id }}">
                                        <i class="ri-delete-bin-line"></i> Cancel
                                    </button>
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
                            <td colspan="12" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="ri-phone-line ri-48px text-muted mb-2"></i>
                                    <h6 class="text-muted">No follow-ups required</h6>
                                    <p class="text-muted mb-0">All prospects have been contacted or converted.</p>
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
    
    <!-- Update Price Modal (Follow Ups) -->
    <div class="modal fade" id="followupUpdateModal" tabindex="-1" aria-labelledby="followupUpdateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="followupUpdateModalLabel">Update Price & Comment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="followupUpdateForm" method="POST" action="">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="enquiry_id" id="followup_modal_enquiry_id" />
                        
                        <!-- Current details display -->
                        <div class="border rounded p-3 bg-light mb-3">
                            <div class="row g-3">
                                <div class="col-4">
                                    <small class="text-muted d-block">Actual Amount</small>
                                    <div class="fw-semibold" id="followup_display_actual">—</div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Discount</small>
                                    <div class="fw-semibold text-danger" id="followup_display_discount">—</div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Previous Negotiated Amount</small>
                                    <div class="fw-semibold text-success" id="followup_display_price">—</div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">Last Comment</small>
                                    <div class="fw-semibold" id="followup_display_comment">—</div>
                                </div>
                            </div>
                        </div>

                        <!-- New update inputs -->
                        <div class="mb-3">
                            <label for="followup_current_price" class="form-label">New Price</label>
                            <input id="followup_current_price" type="number" name="price" class="form-control" placeholder="Enter new price" onkeyup="validateFollowupPrice(this)" required />
                            <div id="followup-warning-message" class="alert alert-warning mt-2 py-2 px-3 d-none">
                                Enquiry price cannot exceed the actual amount.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="followup_comment" class="form-label">New Comment</label>
                            <textarea id="followup_comment" name="comment" rows="3" class="form-control" placeholder="Enter new comment" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="followup_cancel_btn">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="followup_submit_btn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Negotiate by Agent Modal -->
    <div class="modal fade" id="agentNegotiationModal" tabindex="-1" aria-labelledby="agentNegotiationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" id="agentNegotiationForm" method="POST" action="{{ route('tours.agent-negotiation') }}">
                @csrf
                <input type="hidden" name="tour_id" id="agent_negotiation_tour_id">
                <input type="hidden" name="action" id="agent_negotiation_action" value="negotiate">
                <input type="hidden" name="actual_amount" id="agent_negotiation_actual_amount">
                <div class="modal-header">
                    <h5 class="modal-title" id="agentNegotiationModalLabel">Negotiate by Agent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="border rounded p-3 bg-light mb-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Tour</small>
                                <div class="fw-semibold" id="agentNegotiationDisplayId">—</div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <small class="text-muted d-block">Current Amount</small>
                                <div class="fw-semibold text-primary" id="agentNegotiationCurrentAmount">—</div>
                            </div>
                            <div class="col-12">
                                <small class="text-muted d-block">Last Agent Offer</small>
                                <div class="fw-semibold text-warning" id="agentNegotiationLastAmount">—</div>
                            </div>
                            <div class="col-12">
                                <small class="text-muted d-block">Last Remarks</small>
                                <div class="text-muted" id="agentNegotiationLastRemark">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="agentNegotiationAmount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="agentNegotiationAmount" name="amount" min="0" step="0.01" placeholder="Enter negotiated amount">
                        <div class="form-text text-primary fw-semibold" id="agentNegotiationMaxMessage">Maximum allowed amount: <span id="agentNegotiationMaxValue">—</span></div>
                    </div>
                    <div class="mb-3">
                        <label for="agentNegotiationRemark" class="form-label">Remarks</label>
                        <textarea class="form-control" id="agentNegotiationRemark" name="comment" rows="3" placeholder="Add remarks for this negotiation"></textarea>
                    </div>
                    <div class="alert alert-warning py-2 px-3 d-none" id="agentNegotiationWarning">
                        Negotiated amount cannot exceed the current amount.
                    </div>
                </div>
                <div class="modal-footer justify-content-between flex-wrap gap-2">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-danger" id="agentNegotiationCancelBtn" onclick="submitAgentNegotiation('cancel')">
                            Cancel Tour
                        </button>
                        <button type="button" class="btn btn-outline-success" id="agentNegotiationConfirmBtn" onclick="submitAgentNegotiation('confirm')">
                            Confirm
                        </button>
                    </div>
                    <button type="button" class="btn btn-primary" id="agentNegotiationSubmitBtn" onclick="submitAgentNegotiation('negotiate')">
                        Negotiate
                    </button>
                </div>
            </form>
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
    <div class="modal fade service-modal-compact" id="hotelDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="hotelDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                @php
                    $firstHotelOrder = $serviceData['hotel'][0] ?? null;
                    $firstHotelData = $firstHotelOrder ? (is_string($firstHotelOrder->data) ? json_decode($firstHotelOrder->data, true) : $firstHotelOrder->data) : null;
                    $firstBooking = ($firstHotelData && is_array($firstHotelData) && isset($firstHotelData[0])) ? $firstHotelData[0] : ($firstHotelData && is_array($firstHotelData) ? $firstHotelData : null);
                @endphp
                <!-- Compact Header -->
                <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="text-white">
                            <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                <i class="ri-hotel-line me-1" style="font-size: 0.9rem;"></i>Hotel Enquiries - Tour #{{ $tour->tour_id }}
                            </h6>
                            @if($firstBooking && isset($firstBooking['bookingDate']) && is_array($firstBooking['bookingDate']) && count($firstBooking['bookingDate']) > 0)
                                <small class="opacity-90" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($firstBooking['bookingDate'][0])->format('M d') }} - {{ \Carbon\Carbon::parse(end($firstBooking['bookingDate']))->format('M d, Y') }}</small>
                            @endif
                        </div>
                        <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('hotel', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                    </div>
                </div>
                <div class="modal-body p-2" style="background-color: #f8f9fa;">
                    @if(isset($serviceData['hotel']) && count($serviceData['hotel']) > 0)
                        @foreach($serviceData['hotel'] as $index => $hotelOrder)
                        @php
                            $hotelData = is_string($hotelOrder->data) ? json_decode($hotelOrder->data, true) : $hotelOrder->data;
                        @endphp
                        
                        @if(is_array($hotelData))
                            @foreach($hotelData as $booking)
                                @php
                                    $hotelPrice = $booking['price'] ?? $booking['totalPrice'] ?? 0;
                                    $transferPrice = isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0 ? $booking['transfer_options']['cost'] : 0;
                                    $guidePrice = isset($booking['guide_options']['total_price']) && $booking['guide_options']['total_price'] > 0 ? $booking['guide_options']['total_price'] : 0;
                                    $grandTotal = $hotelPrice + $transferPrice + $guidePrice;
                                @endphp
                                <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #74b9ff !important;">
                                    <!-- Compact Card Header -->
                                    <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #74b9ff 0%, #0984e3 100%);">
                                        <div class="row align-items-center g-1">
                                            <div class="col-md-8">
                                                <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                    <i class="ri-hotel-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['hotelDetails']['hotel_name'] ?? 'Hotel Bookings' }}
                                                </h6>
                                                <small class="text-white opacity-90" style="font-size: 0.7rem;">Enquiry {{ $index + 1 }} • {{ ucfirst($booking['bookingType'] ?? 'Standard') }}</small>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                    SGD {{ ceil($grandTotal) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body p-2" style="background-color: #ffffff;">
                                        <!-- Stay Information & Hotel Details -->
                                        <div class="row mb-2 g-2">
                                            <div class="col-md-6">
                                                <div class="bg-light rounded p-2 h-100">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="ri-calendar-check-line text-white" style="font-size: 0.8rem;"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Stay Schedule</h6>
                                                    </div>
                                                    <div class="row g-1">
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Check-in</small>
                                                            <div class="fw-bold text-success" style="font-size: 0.75rem;">
                                                                @if(isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 0)
                                                                    {{ \Carbon\Carbon::parse($booking['bookingDate'][0])->format('M d, Y') }}
                                                                @else
                                                                    N/A
                                                                @endif
                                                            </div>
                                                            @if(isset($booking['hotelDetails']['checkInTime']))
                                                                <small class="text-primary fw-medium" style="font-size: 0.65rem;">{{ $booking['hotelDetails']['checkInTime'] }}</small>
                                                            @endif
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Check-out</small>
                                                            <div class="fw-bold text-danger" style="font-size: 0.75rem;">
                                                                @if(isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 1)
                                                                    {{ \Carbon\Carbon::parse(end($booking['bookingDate']))->format('M d, Y') }}
                                                                @else
                                                                    N/A
                                                                @endif
                                                            </div>
                                                            @if(isset($booking['hotelDetails']['checkOutTime']))
                                                                <small class="text-danger fw-medium" style="font-size: 0.65rem;">{{ $booking['hotelDetails']['checkOutTime'] }}</small>
                                                            @endif
                                                        </div>
                                                        <div class="col-12">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Total Nights</small>
                                                            @if(isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 1)
                                                                @php
                                                                    $checkIn = \Carbon\Carbon::parse($booking['bookingDate'][0]);
                                                                    $checkOut = \Carbon\Carbon::parse(end($booking['bookingDate']));
                                                                    $nights = $checkIn->diffInDays($checkOut);
                                                                @endphp
                                                                <span class="badge bg-info" style="font-size: 0.65rem; padding: 2px 6px;">{{ $nights }} Night{{ $nights > 1 ? 's' : '' }}</span>
                                                            @else
                                                                <span class="badge bg-secondary" style="font-size: 0.65rem; padding: 2px 6px;">Duration TBD</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-light rounded p-2 h-100">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="ri-building-line text-white" style="font-size: 0.8rem;"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Hotel Details</h6>
                                                    </div>
                                                    <div class="row g-1">
                                                        <div class="col-12">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Location</small>
                                                            <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['hotelDetails']['location'] ?? 'Location not specified' }}</div>
                                                        </div>
                                                        @if(isset($booking['hotelDetails']['cancellation_charge']) && !empty($booking['hotelDetails']['cancellation_charge']))
                                                        <div class="col-12">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Cancellation</small>
                                                            <div class="fw-medium text-warning" style="font-size: 0.7rem;">{{ $booking['hotelDetails']['cancellation_charge'] }}</div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                    @if(isset($booking['hotelDetails']['image']))
                                                        <div class="mt-1">
                                                            <img src="{{ $booking['hotelDetails']['image'] }}" alt="{{ $booking['hotelDetails']['hotel_name'] ?? 'Hotel' }}" class="img-fluid rounded shadow-sm" style="height: 60px; width: 100%; object-fit: cover;">
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Room & Accommodation Details -->
                                        @if(isset($booking['rooms']) && is_array($booking['rooms']))
                                            <div class="bg-light rounded p-1 mb-2">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-door-line text-white" style="font-size: 0.7rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Room & Accommodation Details</h6>
                                                </div>
                                                @foreach($booking['rooms'] as $roomIndex => $room)
                                                    @php
                                                        $numberOfRooms = $room['number_of_rooms'] ?? 1;
                                                        $bedPrice = 0;
                                                        $mealCount = 0;
                                                        if(isset($room['beds']) && is_array($room['beds']) && count($room['beds']) > 0) {
                                                            $bedPrice = $room['beds'][0]['price'] ?? 0;
                                                            if(isset($room['beds'][0]['selectedMeals'])) {
                                                                $selectedMeals = $room['beds'][0]['selectedMeals'];
                                                                if(is_array($selectedMeals) || is_object($selectedMeals)) {
                                                                    $mealCount = count($selectedMeals);
                                                                }
                                                            }
                                                        }
                                                        $roomTotalPrice = $bedPrice * $numberOfRooms * ($mealCount > 0 ? $mealCount : 1);
                                                    @endphp
                                                    <div class="bg-white rounded p-1 mb-1 border" style="border-color: #74b9ff !important;">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <div>
                                                                <small class="fw-bold text-dark" style="font-size: 0.75rem;">Room {{ $roomIndex + 1 }}: {{ $room['room_type'] ?? 'Standard Room' }}</small>
                                                                <div><small class="text-muted" style="font-size: 0.65rem;">{{ $numberOfRooms }} Room{{ $numberOfRooms > 1 ? 's' : '' }}</small></div>
                                                            </div>
                                                            <span class="badge bg-success" style="font-size: 0.7rem;">SGD {{ ceil($roomTotalPrice) }}</span>
                                                        </div>
                                                        @if(isset($room['beds']) && is_array($room['beds']))
                                                            @foreach($room['beds'] as $bedIndex => $bed)
                                                                <div class="bg-light rounded p-1 mb-1">
                                                                    <div class="row g-1">
                                                                        <div class="col-6">
                                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">{{ $bed['bed_type'] ?? 'Bed' }}</small>
                                                                            <small class="text-muted" style="font-size: 0.6rem;">Guests: {{ $bed['head_count'] ?? 0 }} • Max: {{ $bed['max_occupancy'] ?? 'N/A' }}</small>
                                                                        </div>
                                                                        <div class="col-3">
                                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Price</small>
                                                                            <div class="fw-bold text-success" style="font-size: 0.7rem;">SGD {{ ceil($bed['price'] ?? 0) }}</div>
                                                                        </div>
                                                                        @if(isset($bed['selectedMeals']) && is_array($bed['selectedMeals']) && count($bed['selectedMeals']) > 0)
                                                                        <div class="col-12">
                                                                            <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Meals:</small>
                                                                            @foreach($bed['selectedMeals'] as $mealKey => $meal)
                                                                                <span class="badge bg-success me-1" style="font-size: 0.6rem;">{{ $meal['type'] ?? 'Meal' }}</span>
                                                                            @endforeach
                                                                        </div>
                                                                        @endif
                                                                        @if(isset($bed['mealTypes']) && is_array($bed['mealTypes']))
                                                                        <div class="col-12">
                                                                            <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Options:</small>
                                                                            @foreach($bed['mealTypes'] as $mealType)
                                                                                <span class="badge bg-secondary me-1" style="font-size: 0.6rem;">{{ $mealType }}</span>
                                                                            @endforeach
                                                                        </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                @endforeach
                                                <div class="bg-white rounded p-1 mt-1 border" style="border-color: #74b9ff !important;">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <small class="fw-bold text-dark" style="font-size: 0.75rem;">Hotel Booking Summary</small>
                                                            @php $totalRooms = collect($booking['rooms'])->sum('number_of_rooms'); @endphp
                                                            <div><small class="text-muted" style="font-size: 0.65rem;">{{ $totalRooms }} room(s) • {{ ucfirst($booking['bookingType'] ?? 'Standard') }}</small></div>
                                                        </div>
                                                        <div class="text-end">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Total Amount</small>
                                                            <div class="fw-bold" style="font-size: 0.9rem; color: #74b9ff;">SGD {{ ceil($booking['totalPrice'] ?? 0) }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Transfer Options -->
                                        @if(isset($booking['transfer_options']) && is_array($booking['transfer_options']) && isset($booking['transfer_options']['transfer_required']) && ($booking['transfer_options']['transfer_required'] === true || $booking['transfer_options']['transfer_required'] === 'true' || $booking['transfer_options']['transfer_required'] === 'Yes'))
                                            <div class="bg-light rounded p-1 mb-2">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-car-line text-white" style="font-size: 0.7rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Transfer Details</h6>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-md-6">
                                                        <div class="bg-white rounded p-1">
                                                            <div class="row g-1">
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Type</small>
                                                                    <span class="badge bg-primary" style="font-size: 0.65rem;">{{ $booking['transfer_options']['type'] ?? 'N/A' }}</span>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Way</small>
                                                                    <span class="badge bg-info" style="font-size: 0.65rem;">{{ $booking['transfer_options']['way'] ?? 'N/A' }}</span>
                                                                </div>
                                                                @if(isset($booking['transfer_options']['destination_name']) && !empty($booking['transfer_options']['destination_name']))
                                                                <div class="col-12">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Destination</small>
                                                                    <div class="fw-medium text-primary" style="font-size: 0.75rem;">
                                                                        <i class="ri-map-pin-line me-1"></i>{{ $booking['transfer_options']['destination_name'] }}
                                                                    </div>
                                                                </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="bg-white rounded p-1">
                                                            @if(isset($booking['transfer_options']['vehicle_details']) && is_array($booking['transfer_options']['vehicle_details']))
                                                                <div class="row g-1">
                                                                    <div class="col-12">
                                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Vehicle</small>
                                                                        <div class="fw-medium" style="font-size: 0.75rem;">
                                                                            <i class="ri-car-line me-1"></i>{{ $booking['transfer_options']['vehicle_details']['vehicle_name'] ?? 'N/A' }}
                                                                        </div>
                                                                        @if(isset($booking['transfer_options']['vehicle_details']['vehicle_type']))
                                                                            <small class="text-muted" style="font-size: 0.6rem;">Type: {{ $booking['transfer_options']['vehicle_details']['vehicle_type'] }}</small>
                                                                        @endif
                                                                    </div>
                                                                    @if(isset($booking['transfer_options']['vehicle_details']['seating_capacity']))
                                                                    <div class="col-12">
                                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Capacity</small>
                                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['transfer_options']['vehicle_details']['seating_capacity'] }} passengers</div>
                                                                    </div>
                                                                    @endif
                                                                    @if(isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0)
                                                                    <div class="col-12">
                                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Cost</small>
                                                                        <div class="fw-bold text-success" style="font-size: 0.8rem;">SGD {{ number_format($booking['transfer_options']['cost'], 2) }}</div>
                                                                    </div>
                                                                    @endif
                                                                </div>
                                                            @elseif(isset($booking['transfer_options']['vehicle_id']))
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Vehicle ID</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['transfer_options']['vehicle_id'] }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @if(isset($booking['transfer_options']['pickup_location_name']) && !empty($booking['transfer_options']['pickup_location_name']))
                                                    <div class="col-12">
                                                        <div class="bg-info bg-opacity-10 rounded p-1 mt-1">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Location</small>
                                                            <div class="fw-medium text-info" style="font-size: 0.75rem;">{{ $booking['transfer_options']['pickup_location_name'] }}</div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endforeach
                @else
                    <div class="text-center py-3">
                        <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="ri-hotel-line text-muted" style="font-size: 1.5rem;"></i>
                        </div>
                        <h6 class="text-dark mb-1" style="font-size: 0.9rem;">No Hotel Data Available</h6>
                        <p class="text-muted mb-2" style="font-size: 0.75rem;">Hotel services are booked but detailed information is not available.</p>
                        <div class="alert alert-primary border-0 shadow-sm py-2 px-2" style="max-width: 360px; margin: 0 auto; font-size: 0.8rem;">
                            <div class="d-flex align-items-center">
                                <i class="ri-information-line text-primary me-2"></i>
                                <div><strong>Note:</strong> {{ $svc['hotel'] }} hotel service(s) are associated with this tour.</div>
                            </div>
                        </div>
                    </div>
                @endif
                </div>
                <!-- Compact Footer -->
                <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                    <div class="d-flex gap-2 w-100 justify-content-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('hotel', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                            <i class="ri-close-line me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

<!-- Attraction Details Modal -->
   @if(isset($svc['attraction']) && $svc['attraction'] > 0)
   <div class="modal fade service-modal-compact" id="attractionDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="attractionDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
       <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
           <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
               <!-- Compact Header -->
               <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%);">
                   <div class="d-flex align-items-center justify-content-between w-100">
                       <div class="text-white">
                           <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                               <i class="ri-building-2-line me-1" style="font-size: 0.9rem;"></i>Attraction Enquiries
                           </h6>
                       </div>
                       <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('attraction', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                   </div>
               </div>
               
               <div class="modal-body p-2" style="background-color: #f8f9fa;">
                   @if(isset($serviceData['attraction']) && count($serviceData['attraction']) > 0)
                       @foreach($serviceData['attraction'] as $index => $attractionOrder)
                       @php
                           $attractionData = is_string($attractionOrder->data) ? json_decode($attractionOrder->data, true) : $attractionOrder->data;
                       @endphp
                       
                       @if(is_array($attractionData))
                           @foreach($attractionData as $booking)
                               @php
                                   $attractionPrice = $booking['price'] ?? $booking['totalPrice'] ?? 0;
                                   $transferPrice = isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0 ? $booking['transfer_options']['cost'] : 0;
                                   $guidePrice = isset($booking['guide_options']['total_price']) && $booking['guide_options']['total_price'] > 0 ? $booking['guide_options']['total_price'] : 0;
                                   $grandTotal = $attractionPrice + $transferPrice + $guidePrice;
                               @endphp
                               <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #fd9853 !important;">
                                   <!-- Compact Card Header -->
                                   <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #fd9853 0%, #fe7854 100%);">
                                       <div class="row align-items-center g-1">
                                           <div class="col-md-8">
                                               <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                   <i class="ri-building-2-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['AttractionName'] ?? 'Attraction Booking' }}
                                               </h6>
                                               <small class="text-white opacity-90" style="font-size: 0.7rem;">{{ $booking['ticketName'] ?? 'Standard Ticket' }} • Enquiry {{ $index + 1 }}</small>
                                           </div>
                                           <div class="col-md-4 text-end">
                                               <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                   SGD {{ ceil($grandTotal) }}
                                               </span>
                                           </div>
                                       </div>
                                   </div>
                                   
                                   <div class="card-body p-2" style="background-color: #ffffff;">
                                       <!-- Visit & Guest Information -->
                                       <div class="row mb-2 g-2">
                                           <div class="col-md-6">
                                               <div class="bg-light rounded p-2 h-100">
                                                   <div class="d-flex align-items-center mb-1">
                                                       <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                           <i class="ri-calendar-line text-white" style="font-size: 0.8rem;"></i>
                                                       </div>
                                                       <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Visit Schedule</h6>
                                                   </div>
                                                   <div class="row g-1">
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Visit Date</small>
                                                           <div class="fw-bold text-success" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') }}</div>
                                                       </div>
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Visit Time</small>
                                                           <div class="fw-medium text-primary" style="font-size: 0.75rem;">{{ $booking['visitTime'] ?? 'Full Day' }}</div>
                                                       </div>
                                                       <div class="col-12">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Selection Type</small>
                                                           <span class="badge bg-info" style="font-size: 0.65rem; padding: 2px 6px;">{{ ucfirst($booking['Selection'] ?? 'Standard') }}</span>
                                                       </div>
                                                   </div>
                                               </div>
                                           </div>
                                           <div class="col-md-6">
                                               <div class="bg-light rounded p-2 h-100">
                                                   <div class="d-flex align-items-center mb-1">
                                                       <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                           <i class="ri-group-line text-white" style="font-size: 0.8rem;"></i>
                                                       </div>
                                                       <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Guest Information</h6>
                                                   </div>
                                                   <div class="row g-1">
                                                       <div class="col-4 text-center">
                                                           <div class="bg-white rounded p-1">
                                                               <div class="fw-bold text-success" style="font-size: 0.85rem;">{{ $booking['adultCount'] ?? 0 }}</div>
                                                               <small class="text-muted" style="font-size: 0.6rem;">Adults</small>
                                                           </div>
                                                       </div>
                                                       <div class="col-4 text-center">
                                                           <div class="bg-white rounded p-1">
                                                               <div class="fw-bold text-warning" style="font-size: 0.85rem;">{{ $booking['childCount'] ?? 0 }}</div>
                                                               <small class="text-muted" style="font-size: 0.6rem;">Children</small>
                                                           </div>
                                                       </div>
                                                       <div class="col-4 text-center">
                                                           <div class="bg-white rounded p-1">
                                                               <div class="fw-bold text-info" style="font-size: 0.85rem;">{{ $booking['seniorCount'] ?? 0 }}</div>
                                                               <small class="text-muted" style="font-size: 0.6rem;">Seniors</small>
                                                           </div>
                                                       </div>
                                                   </div>
                                                   <div class="text-center mt-1">
                                                       <span class="badge" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); color: white; font-size: 0.65rem; padding: 2px 6px;">
                                                           Total: {{ ($booking['adultCount'] ?? 0) + ($booking['childCount'] ?? 0) + ($booking['seniorCount'] ?? 0) }} Guests
                                                       </span>
                                                   </div>
                                               </div>
                                           </div>
                                       </div>

                                       <!-- Attraction Details -->
                                       <div class="bg-light rounded p-1 mb-2">
                                           <div class="d-flex align-items-center mb-1">
                                               <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                   <i class="ri-building-2-line text-white" style="font-size: 0.7rem;"></i>
                                               </div>
                                               <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Attraction Details</h6>
                                           </div>
                                           <div class="bg-white rounded p-1">
                                               <div class="row g-1">
                                                   <div class="col-md-6">
                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Attraction Name</small>
                                                       <div class="fw-medium text-primary" style="font-size: 0.75rem;">{{ $booking['AttractionName'] ?? 'N/A' }}</div>
                                                   </div>
                                                   <div class="col-md-6">
                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Ticket Type</small>
                                                       <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['ticketName'] ?? 'Standard Ticket' }}</div>
                                                   </div>
                                                   <div class="col-12">
                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">NRI Status</small>
                                                       <span class="badge bg-info" style="font-size: 0.65rem;">{{ ucfirst($booking['nri'] ?? 'N/A') }}</span>
                                                   </div>
                                               </div>
                                           </div>
                                       </div>

                                       <!-- Ticket & Pricing Details -->
                                       @if(isset($booking['ticket_details']))
                                       <div class="bg-light rounded p-1 mb-2">
                                           <div class="d-flex align-items-center mb-1">
                                               <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                   <i class="ri-ticket-line text-white" style="font-size: 0.7rem;"></i>
                                               </div>
                                               <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Ticket & Pricing</h6>
                                           </div>
                                           
                                           <!-- Pricing Cards -->
                                           <div class="row g-1 mb-1">
                                               <div class="col-4">
                                                   <div class="bg-white border rounded p-1 text-center" style="border-color: #28a745 !important;">
                                                       <small class="text-success fw-bold d-block" style="font-size: 0.7rem;">Adult</small>
                                                       <div class="fw-bold text-success" style="font-size: 0.75rem;">SGD {{ ceil($booking['ticket_details']['adult_price'] ?? 0) }}</div>
                                                   </div>
                                               </div>
                                               <div class="col-4">
                                                   <div class="bg-white border rounded p-1 text-center" style="border-color: #ffc107 !important;">
                                                       <small class="text-warning fw-bold d-block" style="font-size: 0.7rem;">Child</small>
                                                       <div class="fw-bold text-warning" style="font-size: 0.75rem;">SGD {{ ceil($booking['ticket_details']['child_price'] ?? 0) }}</div>
                                                   </div>
                                               </div>
                                               <div class="col-4">
                                                   <div class="bg-white border rounded p-1 text-center" style="border-color: #17a2b8 !important;">
                                                       <small class="text-info fw-bold d-block" style="font-size: 0.7rem;">Senior</small>
                                                       <div class="fw-bold text-info" style="font-size: 0.75rem;">SGD {{ ceil($booking['ticket_details']['senior_price'] ?? 0) }}</div>
                                                   </div>
                                               </div>
                                           </div>

                                           <!-- Booking Summary -->
                                           <div class="bg-white rounded p-1 border" style="border-color: #fd9853 !important;">
                                               <div class="d-flex justify-content-between align-items-center">
                                                   <div>
                                                       <small class="fw-bold text-dark" style="font-size: 0.75rem;">Booking Summary</small>
                                                       <div class="d-flex gap-1 flex-wrap">
                                                           @if($booking['adultCount'] ?? 0 > 0)
                                                               <span class="badge bg-success" style="font-size: 0.6rem;">{{ $booking['adultCount'] }} × {{ ceil($booking['ticket_details']['adult_price'] ?? 0) }}</span>
                                                           @endif
                                                           @if($booking['childCount'] ?? 0 > 0)
                                                               <span class="badge bg-warning" style="font-size: 0.6rem;">{{ $booking['childCount'] }} × {{ ceil($booking['ticket_details']['child_price'] ?? 0) }}</span>
                                                           @endif
                                                           @if($booking['seniorCount'] ?? 0 > 0)
                                                               <span class="badge bg-info" style="font-size: 0.6rem;">{{ $booking['seniorCount'] }} × {{ ceil($booking['ticket_details']['senior_price'] ?? 0) }}</span>
                                                           @endif
                                                       </div>
                                                   </div>
                                                   <div class="text-end">
                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Total</small>
                                                       <div class="fw-bold" style="font-size: 0.9rem; color: #fd9853;">SGD {{ ceil($booking['totalPrice'] ?? 0) }}</div>
                                                   </div>
                                               </div>
                                           </div>

                                           @if(isset($booking['ticket_details']['description']) && !empty($booking['ticket_details']['description']))
                                           <!-- Ticket Description -->
                                           <div class="bg-white rounded p-1 mt-1 border-start border-3" style="border-color: #fd9853 !important;">
                                               <small class="fw-bold text-dark d-block" style="font-size: 0.75rem;">Ticket Info</small>
                                               <div class="text-muted" style="font-size: 0.7rem;">{!! $booking['ticket_details']['description'] !!}</div>
                                           </div>
                                           @endif
                                       </div>
                                       @endif

                                       <!-- Transfer Options -->
                                       @if(isset($booking['transfer_options']) && is_array($booking['transfer_options']) && isset($booking['transfer_options']['transfer_required']) && ($booking['transfer_options']['transfer_required'] === true || $booking['transfer_options']['transfer_required'] === 'true' || $booking['transfer_options']['transfer_required'] === 'Yes'))
                                           <div class="bg-light rounded p-1 mb-2">
                                               <div class="d-flex align-items-center mb-1">
                                                   <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                       <i class="ri-car-line text-white" style="font-size: 0.7rem;"></i>
                                                   </div>
                                                   <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Transfer Details</h6>
                                               </div>
                                               <div class="row g-1">
                                                   <div class="col-md-6">
                                                       <div class="bg-white rounded p-1">
                                                           <div class="row g-1">
                                                               <div class="col-6">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Type</small>
                                                                   <span class="badge bg-primary" style="font-size: 0.65rem;">{{ $booking['transfer_options']['type'] ?? 'N/A' }}</span>
                                                               </div>
                                                               <div class="col-6">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Way</small>
                                                                   <span class="badge bg-info" style="font-size: 0.65rem;">{{ $booking['transfer_options']['way'] ?? 'N/A' }}</span>
                                                               </div>
                                                               @if(isset($booking['transfer_options']['pickup_location_name']) && !empty($booking['transfer_options']['pickup_location_name']))
                                                               <div class="col-12">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Location</small>
                                                                   <div class="fw-medium text-primary" style="font-size: 0.75rem;">
                                                                       <i class="ri-map-pin-line me-1"></i>{{ $booking['transfer_options']['pickup_location_name'] }}
                                                                   </div>
                                                               </div>
                                                               @endif
                                                           </div>
                                                       </div>
                                                   </div>
                                                   <div class="col-md-6">
                                                       <div class="bg-white rounded p-1">
                                                           @if(isset($booking['transfer_options']['vehicle_details']) && is_array($booking['transfer_options']['vehicle_details']))
                                                               <div class="row g-1">
                                                                   <div class="col-12">
                                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Vehicle</small>
                                                                       <div class="fw-medium" style="font-size: 0.75rem;">
                                                                           <i class="ri-car-line me-1"></i>{{ $booking['transfer_options']['vehicle_details']['vehicle_name'] ?? 'N/A' }}
                                                                       </div>
                                                                       @if(isset($booking['transfer_options']['vehicle_details']['vehicle_type']))
                                                                           <small class="text-muted" style="font-size: 0.6rem;">Type: {{ $booking['transfer_options']['vehicle_details']['vehicle_type'] }}</small>
                                                                       @endif
                                                                   </div>
                                                                   @if(isset($booking['transfer_options']['vehicle_details']['seating_capacity']))
                                                                   <div class="col-12">
                                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Capacity</small>
                                                                       <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['transfer_options']['vehicle_details']['seating_capacity'] }} passengers</div>
                                                                   </div>
                                                                   @endif
                                                                   @if(isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0)
                                                                   <div class="col-12">
                                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Cost</small>
                                                                       <div class="fw-bold text-success" style="font-size: 0.8rem;">SGD {{ number_format($booking['transfer_options']['cost'], 2) }}</div>
                                                                   </div>
                                                                   @endif
                                                               </div>
                                                           @elseif(isset($booking['transfer_options']['vehicle_id']))
                                                               <small class="text-muted d-block" style="font-size: 0.65rem;">Vehicle ID</small>
                                                               <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['transfer_options']['vehicle_id'] }}</div>
                                                           @endif
                                                       </div>
                                                   </div>
                                               </div>
                                           </div>
                                       @endif

                                       <!-- Guide Options -->
                                       @if(isset($booking['guide_options']) && is_array($booking['guide_options']) && isset($booking['guide_options']['guide_required']) && ($booking['guide_options']['guide_required'] === true || $booking['guide_options']['guide_required'] === 'true' || $booking['guide_options']['guide_required'] === 'Yes'))
                                           <div class="bg-light rounded p-1 mb-2">
                                               <div class="d-flex align-items-center mb-1">
                                                   <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                       <i class="ri-user-star-line text-white" style="font-size: 0.7rem;"></i>
                                                   </div>
                                                   <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Guide Details</h6>
                                               </div>
                                               <div class="row g-1">
                                                   <div class="col-md-6">
                                                       <div class="bg-white rounded p-1">
                                                           <div class="row g-1">
                                                               <div class="col-12">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Guide Name</small>
                                                                   <div class="fw-medium text-primary" style="font-size: 0.75rem;">
                                                                       <i class="ri-user-line me-1"></i>{{ $booking['guide_options']['guide_name'] ?? 'N/A' }}
                                                                   </div>
                                                               </div>
                                                               <div class="col-6">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Duration</small>
                                                                   <span class="badge bg-info" style="font-size: 0.65rem;">{{ $booking['guide_options']['package_hours'] ?? 'N/A' }} Hrs</span>
                                                               </div>
                                                               @if(isset($booking['guide_options']['pickup_time']) && !empty($booking['guide_options']['pickup_time']))
                                                               <div class="col-6">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Time</small>
                                                                   <div class="fw-medium text-success" style="font-size: 0.75rem;">
                                                                       @php
                                                                           $pickupTime = $booking['guide_options']['pickup_time'];
                                                                           if (strpos($pickupTime, ' - ') !== false) {
                                                                               $pickupTime = trim(explode(' - ', $pickupTime)[0]);
                                                                           }
                                                                           $formattedPickupTime = $pickupTime;
                                                                           if (!empty($pickupTime)) {
                                                                               try {
                                                                                   $timeObj = \Carbon\Carbon::createFromFormat('H:i', $pickupTime);
                                                                                   $formattedPickupTime = $timeObj->format('h:i A');
                                                                               } catch (\Exception $e) {
                                                                                   try {
                                                                                       $timeObj = \Carbon\Carbon::createFromFormat('h:i A', $pickupTime);
                                                                                       $formattedPickupTime = $timeObj->format('h:i A');
                                                                                   } catch (\Exception $e2) {
                                                                                       try {
                                                                                           $timeObj = \Carbon\Carbon::parse($pickupTime);
                                                                                           $formattedPickupTime = $timeObj->format('h:i A');
                                                                                       } catch (\Exception $e3) {
                                                                                           $formattedPickupTime = $pickupTime;
                                                                                       }
                                                                                   }
                                                                               }
                                                                           }
                                                                       @endphp
                                                                       <i class="ri-time-line me-1"></i>{{ $formattedPickupTime }}
                                                                   </div>
                                                               </div>
                                                               @endif
                                                           </div>
                                                       </div>
                                                   </div>
                                                   <div class="col-md-6">
                                                       <div class="bg-white rounded p-1">
                                                           <div class="row g-1">
                                                               <div class="col-6">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Base Price</small>
                                                                   <div class="fw-medium text-primary" style="font-size: 0.75rem;">SGD {{ ceil($booking['guide_options']['base_price'] ?? 0) }}</div>
                                                               </div>
                                                               @if(isset($booking['guide_options']['surcharge']) && $booking['guide_options']['surcharge'] > 0)
                                                               <div class="col-6">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Surcharge</small>
                                                                   <div class="fw-medium text-warning" style="font-size: 0.75rem;">SGD {{ ceil($booking['guide_options']['surcharge']) }}</div>
                                                               </div>
                                                               @endif
                                                               @if(isset($booking['guide_options']['total_price']) && $booking['guide_options']['total_price'] > 0)
                                                               <div class="col-12">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Guide Total</small>
                                                                   <div class="fw-bold text-success" style="font-size: 0.8rem;">SGD {{ ceil($booking['guide_options']['total_price']) }}</div>
                                                               </div>
                                                               @endif
                                                           </div>
                                                       </div>
                                                   </div>
                                               </div>
                                           </div>
                                       @endif

                                   </div>
                               </div>
                           @endforeach
                       @endif
                   @endforeach
               @else
                   <div class="text-center py-3">
                       <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                           <i class="ri-building-2-line text-muted" style="font-size: 1.5rem;"></i>
                       </div>
                       <h6 class="text-dark mb-1" style="font-size: 0.9rem;">No Attraction Data Available</h6>
                       <p class="text-muted mb-0" style="font-size: 0.75rem;">Attraction services are booked but detailed information is not available.</p>
                   </div>
               @endif
               </div>
               <!-- Compact Footer -->
               <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                   <div class="d-flex gap-2 w-100 justify-content-end">
                       <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('attraction', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                           <i class="ri-close-line me-1"></i>Close
                       </button>
                   </div>
               </div>
           </div>
       </div>
   </div>
   @endif

<!-- Restaurant Details Modal -->
   @if(isset($svc['restaurant']) && $svc['restaurant'] > 0)
   <div class="modal fade service-modal-compact" id="restaurantDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="restaurantDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
       <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
           <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
               <!-- Compact Header -->
               <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%);">
                   <div class="d-flex align-items-center justify-content-between w-100">
                       <div class="text-white">
                           <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                               <i class="ri-restaurant-2-line me-1" style="font-size: 0.9rem;"></i>Restaurant Enquiries 
                           </h6>
                       </div>
                       <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('restaurant', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                   </div>
               </div>
               
               <div class="modal-body p-2" style="background-color: #f8f9fa;">
                   @if(isset($serviceData['restaurant']) && count($serviceData['restaurant']) > 0)
                       @foreach($serviceData['restaurant'] as $index => $restaurantOrder)
                       @php
                           $restaurantData = is_string($restaurantOrder->data) ? json_decode($restaurantOrder->data, true) : $restaurantOrder->data;
                       @endphp
                       
                       @if(is_array($restaurantData))
                           @foreach($restaurantData as $booking)
                               @php
                                   $restaurantPrice = $booking['totalPrice'] ?? $booking['mealPrice'] ?? 0;
                                   $transferPrice = isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0 ? $booking['transfer_options']['cost'] : 0;
                                   $restaurantGrandTotal = round($restaurantPrice + $transferPrice);
                               @endphp
                               <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #fd79a8 !important;">
                                   <!-- Compact Card Header -->
                                   <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #fd79a8 0%, #fdcb6e 100%);">
                                       <div class="row align-items-center g-1">
                                           <div class="col-md-8">
                                               <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                   <i class="ri-restaurant-2-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['restaurantName'] ?? 'Restaurant Booking' }}
                                               </h6>
                                               <small class="text-white opacity-90" style="font-size: 0.7rem;">{{ ucfirst($booking['mealType'] ?? 'Meal') }} • {{ $booking['mealSpecificType'] ?? 'Standard' }}</small>
                                           </div>
                                           <div class="col-md-4 text-end">
                                               <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                   SGD {{ ceil($restaurantGrandTotal) }}
                                               </span>
                                           </div>
                                       </div>
                                   </div>
                                   
                                   <div class="card-body p-2" style="background-color: #ffffff;">
                                       <!-- Reservation Details -->
                                       <div class="row mb-2 g-2">
                                           <div class="col-md-6">
                                               <div class="bg-light rounded p-2 h-100">
                                                   <div class="d-flex align-items-center mb-1">
                                                       <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                           <i class="ri-calendar-line text-white" style="font-size: 0.8rem;"></i>
                                                       </div>
                                                       <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Reservation Details</h6>
                                                   </div>
                                                   <div class="row g-1">
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Dining Date</small>
                                                           <div class="fw-bold text-success" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') }}</div>
                                                       </div>
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Dining Time</small>
                                                           <div class="fw-medium text-primary" style="font-size: 0.75rem;">{{ $booking['visitTime'] ?? 'TBC' }}</div>
                                                       </div>
                                                       <div class="col-6 text-center">
                                                           <div class="bg-white rounded p-1">
                                                               <div class="fw-bold text-success" style="font-size: 0.85rem;">{{ $booking['adultCount'] ?? 0 }}</div>
                                                               <small class="text-muted" style="font-size: 0.6rem;">Adults</small>
                                                           </div>
                                                       </div>
                                                       <div class="col-6 text-center">
                                                           <div class="bg-white rounded p-1">
                                                               <div class="fw-bold text-warning" style="font-size: 0.85rem;">{{ $booking['childCount'] ?? 0 }}</div>
                                                               <small class="text-muted" style="font-size: 0.6rem;">Children</small>
                                                           </div>
                                                       </div>
                                                       <div class="col-12 text-center mt-1">
                                                           <span class="badge" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); color: white; font-size: 0.65rem; padding: 2px 6px;">
                                                               Party of {{ ($booking['adultCount'] ?? 0) + ($booking['childCount'] ?? 0) }}
                                                           </span>
                                                       </div>
                                                   </div>
                                               </div>
                                           </div>
                                           <div class="col-md-6">
                                               <div class="bg-light rounded p-2 h-100">
                                                   <div class="d-flex align-items-center mb-1">
                                                       <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                           <i class="ri-information-line text-white" style="font-size: 0.8rem;"></i>
                                                       </div>
                                                       <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Restaurant Overview</h6>
                                                   </div>
                                                   <div class="row g-1">
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Meal Price</small>
                                                           <div class="fw-medium text-success" style="font-size: 0.8rem;">SGD {{ ceil($booking['mealPrice'] ?? 0) }}</div>
                                                       </div>
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Transfer</small>
                                                           <div class="fw-medium text-info" style="font-size: 0.8rem;">SGD {{ ceil($transferPrice) }}</div>
                                                       </div>
                                                       <div class="col-12 mt-1 pt-1 border-top">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Total Price</small>
                                                           <div class="fw-bold" style="font-size: 0.95rem; color: #fd79a8;">SGD {{ ceil($restaurantGrandTotal) }}</div>
                                                       </div>
                                                   </div>
                                               </div>
                                           </div>
                                       </div>

                                       <!-- Transfer Options -->
                                       @if(isset($booking['transfer_options']) && is_array($booking['transfer_options']) && isset($booking['transfer_options']['transfer_required']) && ($booking['transfer_options']['transfer_required'] === true || $booking['transfer_options']['transfer_required'] === 'true' || $booking['transfer_options']['transfer_required'] === 'Yes'))
                                           <div class="bg-light rounded p-1 mb-2">
                                               <div class="d-flex align-items-center mb-1">
                                                   <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                       <i class="ri-car-line text-white" style="font-size: 0.7rem;"></i>
                                                   </div>
                                                   <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Transfer Details</h6>
                                               </div>
                                               <div class="row g-1">
                                                   <div class="col-md-6">
                                                       <div class="bg-white rounded p-1">
                                                           <div class="row g-1">
                                                               <div class="col-6">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Type</small>
                                                                   <span class="badge bg-primary" style="font-size: 0.65rem;">{{ $booking['transfer_options']['type'] ?? 'N/A' }}</span>
                                                               </div>
                                                               <div class="col-6">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Way</small>
                                                                   <span class="badge bg-info" style="font-size: 0.65rem;">{{ $booking['transfer_options']['way'] ?? 'N/A' }}</span>
                                                               </div>
                                                               @if(isset($booking['transfer_options']['pickup_location_name']) && !empty($booking['transfer_options']['pickup_location_name']))
                                                               <div class="col-12">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Location</small>
                                                                   <div class="fw-medium text-primary" style="font-size: 0.75rem;">
                                                                       <i class="ri-map-pin-line me-1"></i>{{ $booking['transfer_options']['pickup_location_name'] }}
                                                                   </div>
                                                               </div>
                                                               @endif
                                                           </div>
                                                       </div>
                                                   </div>
                                                   <div class="col-md-6">
                                                       <div class="bg-white rounded p-1">
                                                           @if(isset($booking['transfer_options']['vehicle_details']) && is_array($booking['transfer_options']['vehicle_details']))
                                                               <div class="row g-1">
                                                                   <div class="col-12">
                                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Vehicle</small>
                                                                       <div class="fw-medium" style="font-size: 0.75rem;">
                                                                           <i class="ri-car-line me-1"></i>{{ $booking['transfer_options']['vehicle_details']['vehicle_name'] ?? 'N/A' }}
                                                                       </div>
                                                                       @if(isset($booking['transfer_options']['vehicle_details']['vehicle_type']))
                                                                           <small class="text-muted" style="font-size: 0.6rem;">Type: {{ $booking['transfer_options']['vehicle_details']['vehicle_type'] }}</small>
                                                                       @endif
                                                                   </div>
                                                                   @if(isset($booking['transfer_options']['vehicle_details']['seating_capacity']))
                                                                   <div class="col-12">
                                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Capacity</small>
                                                                       <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['transfer_options']['vehicle_details']['seating_capacity'] }} passengers</div>
                                                                   </div>
                                                                   @endif
                                                                   @if(isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0)
                                                                   <div class="col-12">
                                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Cost</small>
                                                                       <div class="fw-bold text-success" style="font-size: 0.8rem;">SGD {{ number_format($booking['transfer_options']['cost'], 2) }}</div>
                                                                   </div>
                                                                   @endif
                                                               </div>
                                                           @elseif(isset($booking['transfer_options']['vehicle_id']))
                                                               <small class="text-muted d-block" style="font-size: 0.65rem;">Vehicle ID</small>
                                                               <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['transfer_options']['vehicle_id'] }}</div>
                                                           @endif
                                                       </div>
                                                   </div>
                                               </div>
                                           </div>
                                       @endif

                                   </div>
                               </div>
                           @endforeach
                       @endif
                   @endforeach
               @else
                   <div class="text-center py-3">
                       <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                           <i class="ri-restaurant-2-line text-muted" style="font-size: 1.5rem;"></i>
                       </div>
                       <h6 class="text-dark mb-1" style="font-size: 0.9rem;">No Restaurant Data Available</h6>
                       <p class="text-muted mb-0" style="font-size: 0.75rem;">Restaurant services are booked but detailed information is not available.</p>
                   </div>
               @endif
               </div>
               <!-- Compact Footer -->
               <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                   <div class="d-flex gap-2 w-100 justify-content-end">
                       <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('restaurant', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                           <i class="ri-close-line me-1"></i>Close
                       </button>
                   </div>
               </div>
           </div>
       </div>
   </div>
   @endif


<!-- Guide Details Modal -->
   @if(isset($svc['guide']) && $svc['guide'] > 0)
   <div class="modal fade service-modal-compact" id="guideDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="guideDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
       <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
           <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
               <!-- Compact Header -->
               <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%);">
                   <div class="d-flex align-items-center justify-content-between w-100">
                       <div class="text-white">
                           <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                               <i class="ri-user-voice-line me-1" style="font-size: 0.9rem;"></i>Guide Enquiries - Tour #{{ $tour->tour_id }}
                           </h6>
                       </div>
                       <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('guide', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                   </div>
               </div>
               
               <div class="modal-body p-2" style="background-color: #f8f9fa;">
                   @if(isset($serviceData['guide']) && count($serviceData['guide']) > 0)
                       @foreach($serviceData['guide'] as $index => $guideOrder)
                       @php
                           $guideData = is_string($guideOrder->data) ? json_decode($guideOrder->data, true) : $guideOrder->data;
                       @endphp
                       
                       @if(is_array($guideData))
                           @foreach($guideData as $booking)
                               <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #00cec9 !important;">
                                   <!-- Compact Card Header -->
                                   <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #00cec9 0%, #55a3ff 100%);">
                                       <div class="row align-items-center g-1">
                                           <div class="col-md-8">
                                               <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                   <i class="ri-user-voice-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['guide_name'] ?? 'Guide Booking' }}
                                               </h6>
                                               <small class="text-white opacity-90" style="font-size: 0.7rem;">{{ $booking['hours'] ?? 'N/A' }} Hours Service</small>
                                           </div>
                                           <div class="col-md-4 text-end">
                                               <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                   SGD {{ ceil($booking['totalPrice'] ?? 0) }}
                                               </span>
                                           </div>
                                       </div>
                                   </div>
                                   
                                   <div class="card-body p-2" style="background-color: #ffffff;">
                                       <!-- Guide Information with Image -->
                                       <div class="row mb-2 g-2">
                                           <div class="col-md-8">
                                               <div class="bg-light rounded p-2 h-100">
                                                   <div class="d-flex align-items-center mb-1">
                                                       <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                           <i class="ri-user-voice-line text-white" style="font-size: 0.8rem;"></i>
                                                       </div>
                                                       <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Guide Information</h6>
                                                   </div>
                                                   <div class="row g-1">
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Guide Name</small>
                                                           <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['guide_name'] ?? 'N/A' }}</div>
                                                       </div>
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Base Price</small>
                                                           <div class="fw-medium text-success" style="font-size: 0.75rem;">SGD {{ ceil($booking['basePrice'] ?? 0) }}</div>
                                                       </div>
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Surcharge</small>
                                                           <div class="fw-medium text-warning" style="font-size: 0.75rem;">SGD {{ ceil($booking['surcharge'] ?? 0) }}</div>
                                                       </div>
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Total</small>
                                                           <div class="fw-bold" style="font-size: 0.8rem; color: #00cec9;">SGD {{ ceil($booking['totalPrice'] ?? 0) }}</div>
                                                       </div>
                                                   </div>
                                               </div>
                                           </div>
                                           <div class="col-md-4">
                                               @if(isset($booking['image']))
                                                   <img src="{{ $booking['image'] }}" 
                                                       alt="{{ $booking['guide_name'] ?? 'Guide' }}" 
                                                       class="img-fluid rounded shadow-sm" 
                                                       style="height: 100px; width: 100%; object-fit: cover;">
                                               @else
                                                   <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 100px;">
                                                       <i class="ri-user-voice-line text-muted" style="font-size: 2rem;"></i>
                                                   </div>
                                               @endif
                                           </div>
                                       </div>

                                       <!-- Service Schedule & Group Info -->
                                       <div class="row mb-2 g-2">
                                           <div class="col-md-6">
                                               <div class="bg-light rounded p-2 h-100">
                                                   <div class="d-flex align-items-center mb-1">
                                                       <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                           <i class="ri-calendar-line text-white" style="font-size: 0.8rem;"></i>
                                                       </div>
                                                       <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Service Schedule</h6>
                                                   </div>
                                                   <div class="row g-1">
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Service Date</small>
                                                           <div class="fw-bold text-success" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') }}</div>
                                                       </div>
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Start Time</small>
                                                           <div class="fw-medium text-primary" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'TBC' }}</div>
                                                       </div>
                                                       <div class="col-12">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Duration</small>
                                                           <span class="badge bg-info" style="font-size: 0.65rem; padding: 2px 6px;">{{ $booking['hours'] ?? 'N/A' }} Hours</span>
                                                       </div>
                                                       @if(($booking['Night_Start_Time'] ?? false) && ($booking['Night_End_Time'] ?? false))
                                                       <div class="col-12">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Night Service</small>
                                                           <div class="fw-medium text-warning" style="font-size: 0.7rem;">{{ $booking['Night_Start_Time'] }} - {{ $booking['Night_End_Time'] }}</div>
                                                       </div>
                                                       @endif
                                                   </div>
                                               </div>
                                           </div>
                                           <div class="col-md-6">
                                               <div class="bg-light rounded p-2 h-100">
                                                   <div class="d-flex align-items-center mb-1">
                                                       <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                           <i class="ri-group-line text-white" style="font-size: 0.8rem;"></i>
                                                       </div>
                                                       <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Group Information</h6>
                                                   </div>
                                                   <div class="row g-1">
                                                       <div class="col-6 text-center">
                                                           <div class="bg-white rounded p-1">
                                                               <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['adults'] ?? 0 }}</div>
                                                               <small class="text-muted" style="font-size: 0.6rem;">Adults</small>
                                                           </div>
                                                       </div>
                                                       <div class="col-6 text-center">
                                                           <div class="bg-white rounded p-1">
                                                               <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['children'] ?? 0 }}</div>
                                                               <small class="text-muted" style="font-size: 0.6rem;">Children</small>
                                                           </div>
                                                       </div>
                                                       <div class="col-12 text-center mt-1">
                                                           <span class="badge" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); color: white; font-size: 0.65rem; padding: 2px 6px;">
                                                               Group: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} People
                                                           </span>
                                                       </div>
                                                   </div>
                                               </div>
                                           </div>
                                       </div>

                                       <!-- Pricing Breakdown -->
                                       <div class="bg-light rounded p-1 mb-2">
                                           <div class="d-flex align-items-center mb-1">
                                               <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                   <i class="ri-money-dollar-circle-line text-white" style="font-size: 0.7rem;"></i>
                                               </div>
                                               <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Pricing Breakdown</h6>
                                           </div>
                                           <div class="row g-1">
                                               <div class="col-4">
                                                   <div class="bg-white border rounded p-1 text-center" style="border-color: #28a745 !important;">
                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Base Price</small>
                                                       <div class="fw-bold text-success" style="font-size: 0.75rem;">SGD {{ ceil($booking['basePrice'] ?? 0) }}</div>
                                                   </div>
                                               </div>
                                               <div class="col-4">
                                                   <div class="bg-white border rounded p-1 text-center" style="border-color: #ffc107 !important;">
                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Surcharge</small>
                                                       <div class="fw-bold text-warning" style="font-size: 0.75rem;">SGD {{ ceil($booking['surcharge'] ?? 0) }}</div>
                                                   </div>
                                               </div>
                                               <div class="col-4">
                                                   <div class="bg-white border rounded p-1 text-center" style="border-color: #00cec9 !important;">
                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Total</small>
                                                       <div class="fw-bold" style="font-size: 0.8rem; color: #00cec9;">SGD {{ ceil($booking['totalPrice'] ?? 0) }}</div>
                                                   </div>
                                               </div>
                                           </div>
                                       </div>

                                   </div>
                               </div>
                           @endforeach
                       @endif
                       @endforeach
                   @else
                       <div class="text-center py-3">
                           <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                               <i class="ri-user-voice-line text-muted" style="font-size: 1.5rem;"></i>
                           </div>
                           <h6 class="text-dark mb-1" style="font-size: 0.9rem;">No Guide Data Available</h6>
                           <p class="text-muted mb-0" style="font-size: 0.75rem;">Guide services are booked but detailed information is not available.</p>
                       </div>
                   @endif
               </div>
               <!-- Compact Footer -->
               <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                   <div class="d-flex gap-2 w-100 justify-content-end">
                       <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('guide', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                           <i class="ri-close-line me-1"></i>Close
                       </button>
                   </div>
               </div>
           </div>
       </div>
   </div>
   @endif

<!-- Entry Port (Arrival) Details Modal -->
   @if(isset($svc['entry_port']) && $svc['entry_port'] > 0)
   <div class="modal fade service-modal-compact" id="entry_portDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="entry_portDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
       <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
           <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
               <!-- Compact Header -->
               <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%);">
                   <div class="d-flex align-items-center justify-content-between w-100">
                       <div class="text-white">
                           <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                               <i class="ri-flight-land-line me-1" style="font-size: 0.9rem;"></i>Arrival Transfer - Tour #{{ $tour->tour_id }}
                           </h6>
                       </div>
                       <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('entry_port', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                   </div>
               </div>
               
               <div class="modal-body p-2" style="background-color: #f8f9fa;">
                   @if(isset($serviceData['entry_port']) && count($serviceData['entry_port']) > 0)
                       @foreach($serviceData['entry_port'] as $index => $entryOrder)
                       @php
                           $entryData = is_string($entryOrder->data) ? json_decode($entryOrder->data, true) : $entryOrder->data;
                       @endphp
                       
                       @if(is_array($entryData))
                           @foreach($entryData as $booking)
                               <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #00b894 !important;">
                                   <!-- Compact Card Header -->
                                   <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #00b894 0%, #55a3ff 100%);">
                                       <div class="row align-items-center g-1">
                                           <div class="col-md-8">
                                               <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                   <i class="ri-car-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['vehicles_name'] ?? 'Vehicle Transfer' }}
                                               </h6>
                                               <small class="text-white opacity-90" style="font-size: 0.7rem;">{{ ucfirst($booking['type'] ?? 'Standard') }} Transfer • Enquiry {{ $index + 1 }}</small>
                                           </div>
                                           <div class="col-md-4 text-end">
                                               <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                   SGD {{ ceil($booking['totalPrice'] ?? 0) }}
                                               </span>
                                           </div>
                                       </div>
                                   </div>
                                   
                                   <div class="card-body p-2" style="background-color: #ffffff;">
                                       <!-- Transfer Schedule & Passengers -->
                                       <div class="row mb-2 g-2">
                                           <div class="col-md-6">
                                               <div class="bg-light rounded p-2 h-100">
                                                   <div class="d-flex align-items-center mb-1">
                                                       <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                           <i class="ri-calendar-line text-white" style="font-size: 0.8rem;"></i>
                                                       </div>
                                                       <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Transfer Schedule</h6>
                                                   </div>
                                                   <div class="row g-1">
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Arrival Date</small>
                                                           <div class="fw-bold text-success" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') }}</div>
                                                       </div>
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Time</small>
                                                           <div class="fw-medium text-primary" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'TBC' }}</div>
                                                       </div>
                                                       <div class="col-12">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Transfer Type</small>
                                                           <span class="badge bg-info" style="font-size: 0.65rem; padding: 2px 6px;">{{ ucfirst($booking['type'] ?? 'Standard') }}</span>
                                                       </div>
                                                   </div>
                                               </div>
                                           </div>
                                           <div class="col-md-6">
                                               <div class="bg-light rounded p-2 h-100">
                                                   <div class="d-flex align-items-center mb-1">
                                                       <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                           <i class="ri-group-line text-white" style="font-size: 0.8rem;"></i>
                                                       </div>
                                                       <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Passengers</h6>
                                                   </div>
                                                   <div class="row g-1">
                                                       <div class="col-6 text-center">
                                                           <div class="bg-white rounded p-1">
                                                               <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['adults'] ?? 0 }}</div>
                                                               <small class="text-muted" style="font-size: 0.6rem;">Adults</small>
                                                           </div>
                                                       </div>
                                                       <div class="col-6 text-center">
                                                           <div class="bg-white rounded p-1">
                                                               <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['children'] ?? 0 }}</div>
                                                               <small class="text-muted" style="font-size: 0.6rem;">Children</small>
                                                           </div>
                                                       </div>
                                                       <div class="col-12 text-center mt-1">
                                                           <span class="badge" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); color: white; font-size: 0.65rem; padding: 2px 6px;">
                                                               Total: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} Passengers
                                                           </span>
                                                       </div>
                                                   </div>
                                               </div>
                                           </div>
                                       </div>

                                       <!-- Route Information -->
                                       <div class="bg-light rounded p-1 mb-2">
                                           <div class="d-flex align-items-center mb-1">
                                               <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                   <i class="ri-route-line text-white" style="font-size: 0.7rem;"></i>
                                               </div>
                                               <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Route Details</h6>
                                           </div>
                                           <div class="bg-white rounded p-1">
                                               <div class="row g-1">
                                                   <div class="col-md-6">
                                                       <div class="d-flex align-items-start">
                                                           <div class="rounded-circle p-1 me-2 mt-1" style="background-color: #28a745; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">
                                                               <i class="ri-map-pin-line text-white" style="font-size: 0.6rem;"></i>
                                                           </div>
                                                           <div>
                                                               <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Location</small>
                                                               <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
                                                               <small class="text-success" style="font-size: 0.6rem;">Origin</small>
                                                           </div>
                                                       </div>
                                                   </div>
                                                   <div class="col-md-6">
                                                       <div class="d-flex align-items-start">
                                                           <div class="rounded-circle p-1 me-2 mt-1" style="background-color: #dc3545; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">
                                                               <i class="ri-flag-line text-white" style="font-size: 0.6rem;"></i>
                                                           </div>
                                                           <div>
                                                               <small class="text-muted d-block" style="font-size: 0.65rem;">Drop-off Location</small>
                                                               <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrydropoff'] ?? 'N/A' }}</div>
                                                               <small class="text-danger" style="font-size: 0.6rem;">Destination</small>
                                                           </div>
                                                       </div>
                                                   </div>
                                                   <div class="col-6">
                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">City</small>
                                                       <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['city'] ?? 'N/A' }}</div>
                                                   </div>
                                                   <div class="col-6">
                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Country</small>
                                                       <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['country'] ?? 'N/A' }}</div>
                                                   </div>
                                               </div>
                                           </div>
                                       </div>

                                       <!-- Vehicle Information -->
                                       <div class="row mb-2 g-2">
                                           <div class="col-md-8">
                                               <div class="bg-light rounded p-2 h-100">
                                                   <div class="d-flex align-items-center mb-1">
                                                       <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                           <i class="ri-car-line text-white" style="font-size: 0.8rem;"></i>
                                                       </div>
                                                       <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Vehicle Details</h6>
                                                   </div>
                                                   <div class="row g-1">
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Vehicle Name</small>
                                                           <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                       </div>
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Service Type</small>
                                                           <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['type'] ?? 'N/A' }} Transfer</div>
                                                       </div>
                                                   </div>
                                               </div>
                                           </div>
                                           <div class="col-md-4">
                                               @if(isset($booking['image']))
                                                   <img src="{{ $booking['image'] }}" 
                                                       alt="{{ $booking['vehicles_name'] ?? 'Vehicle' }}" 
                                                       class="img-fluid rounded shadow-sm" 
                                                       style="height: 80px; width: 100%; object-fit: cover;">
                                               @else
                                                   <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 80px;">
                                                       <i class="ri-car-line text-muted" style="font-size: 2rem;"></i>
                                                   </div>
                                               @endif
                                           </div>
                                       </div>

                                   </div>
                               </div>
                           @endforeach
                       @endif
                       @endforeach
                   @else
                       <div class="text-center py-3">
                           <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                               <i class="ri-flight-land-line text-muted" style="font-size: 1.5rem;"></i>
                           </div>
                           <h6 class="text-dark mb-1" style="font-size: 0.9rem;">No Arrival Transfer Data Available</h6>
                           <p class="text-muted mb-0" style="font-size: 0.75rem;">Entry port services are booked but detailed information is not available.</p>
                       </div>
                   @endif
               </div>
               <!-- Compact Footer -->
               <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                   <div class="d-flex gap-2 w-100 justify-content-end">
                       <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('entry_port', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                           <i class="ri-close-line me-1"></i>Close
                       </button>
                   </div>
               </div>
           </div>
       </div>
   </div>
   @endif

<!-- Exit Port (Departure) Details Modal -->
   @if(isset($svc['exit_port']) && $svc['exit_port'] > 0)
   <div class="modal fade service-modal-compact" id="exit_portDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="exit_portDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
       <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
           <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
               <!-- Compact Header -->
               <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%);">
                   <div class="d-flex align-items-center justify-content-between w-100">
                       <div class="text-white">
                           <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                               <i class="ri-flight-takeoff-line me-1" style="font-size: 0.9rem;"></i>Departure Transfer - Tour #{{ $tour->tour_id }}
                           </h6>
                       </div>
                       <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('exit_port', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                   </div>
               </div>
               
               <div class="modal-body p-2" style="background-color: #f8f9fa;">
                   @if(isset($serviceData['exit_port']) && count($serviceData['exit_port']) > 0)
                       @foreach($serviceData['exit_port'] as $index => $exitOrder)
                       @php
                           $exitData = is_string($exitOrder->data) ? json_decode($exitOrder->data, true) : $exitOrder->data;
                       @endphp
                       
                       @if(is_array($exitData))
                           @foreach($exitData as $booking)
                               <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #fd7f6f !important;">
                                   <!-- Compact Card Header -->
                                   <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #fd7f6f 0%, #feb47b 100%);">
                                       <div class="row align-items-center g-1">
                                           <div class="col-md-8">
                                               <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                   <i class="ri-car-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['vehicles_name'] ?? 'Vehicle Transfer' }}
                                               </h6>
                                               <small class="text-white opacity-90" style="font-size: 0.7rem;">{{ ucfirst($booking['type'] ?? 'Standard') }} Transfer • Enquiry {{ $index + 1 }}</small>
                                           </div>
                                           <div class="col-md-4 text-end">
                                               <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                   SGD {{ ceil($booking['totalPrice'] ?? 0) }}
                                               </span>
                                           </div>
                                       </div>
                                   </div>
                                   
                                   <div class="card-body p-2" style="background-color: #ffffff;">
                                       <!-- Transfer Schedule & Passengers -->
                                       <div class="row mb-2 g-2">
                                           <div class="col-md-6">
                                               <div class="bg-light rounded p-2 h-100">
                                                   <div class="d-flex align-items-center mb-1">
                                                       <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                           <i class="ri-calendar-line text-white" style="font-size: 0.8rem;"></i>
                                                       </div>
                                                       <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Transfer Schedule</h6>
                                                   </div>
                                                   <div class="row g-1">
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Departure Date</small>
                                                           <div class="fw-bold text-danger" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') }}</div>
                                                       </div>
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Time</small>
                                                           <div class="fw-medium text-primary" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'TBC' }}</div>
                                                       </div>
                                                       <div class="col-12">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Transfer Type</small>
                                                           <span class="badge bg-warning" style="font-size: 0.65rem; padding: 2px 6px;">{{ ucfirst($booking['type'] ?? 'Standard') }}</span>
                                                       </div>
                                                   </div>
                                               </div>
                                           </div>
                                           <div class="col-md-6">
                                               <div class="bg-light rounded p-2 h-100">
                                                   <div class="d-flex align-items-center mb-1">
                                                       <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                           <i class="ri-group-line text-white" style="font-size: 0.8rem;"></i>
                                                       </div>
                                                       <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Passengers</h6>
                                                   </div>
                                                   <div class="row g-1">
                                                       <div class="col-6 text-center">
                                                           <div class="bg-white rounded p-1">
                                                               <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['adults'] ?? 0 }}</div>
                                                               <small class="text-muted" style="font-size: 0.6rem;">Adults</small>
                                                           </div>
                                                       </div>
                                                       <div class="col-6 text-center">
                                                           <div class="bg-white rounded p-1">
                                                               <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['children'] ?? 0 }}</div>
                                                               <small class="text-muted" style="font-size: 0.6rem;">Children</small>
                                                           </div>
                                                       </div>
                                                       <div class="col-12 text-center mt-1">
                                                           <span class="badge" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%); color: white; font-size: 0.65rem; padding: 2px 6px;">
                                                               Total: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} Passengers
                                                           </span>
                                                       </div>
                                                   </div>
                                               </div>
                                           </div>
                                       </div>

                                       <!-- Route Information -->
                                       <div class="bg-light rounded p-1 mb-2">
                                           <div class="d-flex align-items-center mb-1">
                                               <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                   <i class="ri-route-line text-white" style="font-size: 0.7rem;"></i>
                                               </div>
                                               <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Route Details</h6>
                                           </div>
                                           <div class="bg-white rounded p-1">
                                               <div class="row g-1">
                                                   <div class="col-md-6">
                                                       <div class="d-flex align-items-start">
                                                           <div class="rounded-circle p-1 me-2 mt-1" style="background-color: #28a745; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">
                                                               <i class="ri-map-pin-line text-white" style="font-size: 0.6rem;"></i>
                                                           </div>
                                                           <div>
                                                               <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Location</small>
                                                               <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['exitpickup'] ?? 'N/A' }}</div>
                                                               <small class="text-success" style="font-size: 0.6rem;">Origin</small>
                                                           </div>
                                                       </div>
                                                   </div>
                                                   <div class="col-md-6">
                                                       <div class="d-flex align-items-start">
                                                           <div class="rounded-circle p-1 me-2 mt-1" style="background-color: #dc3545; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">
                                                               <i class="ri-flag-line text-white" style="font-size: 0.6rem;"></i>
                                                           </div>
                                                           <div>
                                                               <small class="text-muted d-block" style="font-size: 0.65rem;">Drop-off Location</small>
                                                               <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['exitdropoff'] ?? 'N/A' }}</div>
                                                               <small class="text-danger" style="font-size: 0.6rem;">Destination</small>
                                                           </div>
                                                       </div>
                                                   </div>
                                                   <div class="col-6">
                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">City</small>
                                                       <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['city'] ?? 'N/A' }}</div>
                                                   </div>
                                                   <div class="col-6">
                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Country</small>
                                                       <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['country'] ?? 'N/A' }}</div>
                                                   </div>
                                               </div>
                                           </div>
                                       </div>

                                       <!-- Vehicle Information -->
                                       <div class="row mb-2 g-2">
                                           <div class="col-md-8">
                                               <div class="bg-light rounded p-2 h-100">
                                                   <div class="d-flex align-items-center mb-1">
                                                       <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                           <i class="ri-car-line text-white" style="font-size: 0.8rem;"></i>
                                                       </div>
                                                       <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Vehicle Details</h6>
                                                   </div>
                                                   <div class="row g-1">
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Vehicle Name</small>
                                                           <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                       </div>
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Service Type</small>
                                                           <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['type'] ?? 'N/A' }} Transfer</div>
                                                       </div>
                                                   </div>
                                               </div>
                                           </div>
                                           <div class="col-md-4">
                                               @if(isset($booking['image']))
                                                   <img src="{{ $booking['image'] }}" 
                                                       alt="{{ $booking['vehicles_name'] ?? 'Vehicle' }}" 
                                                       class="img-fluid rounded shadow-sm" 
                                                       style="height: 80px; width: 100%; object-fit: cover;">
                                               @else
                                                   <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 80px;">
                                                       <i class="ri-car-line text-muted" style="font-size: 2rem;"></i>
                                                   </div>
                                               @endif
                                           </div>
                                       </div>

                                   </div>
                               </div>
                           @endforeach
                       @endif
                       @endforeach
                   @else
                       <div class="text-center py-3">
                           <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                               <i class="ri-flight-takeoff-line text-muted" style="font-size: 1.5rem;"></i>
                           </div>
                           <h6 class="text-dark mb-1" style="font-size: 0.9rem;">No Departure Transfer Data Available</h6>
                           <p class="text-muted mb-0" style="font-size: 0.75rem;">Exit port services are booked but detailed information is not available.</p>
                       </div>
                   @endif
               </div>
               <!-- Compact Footer -->
               <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                   <div class="d-flex gap-2 w-100 justify-content-end">
                       <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('exit_port', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                           <i class="ri-close-line me-1"></i>Close
                       </button>
                   </div>
               </div>
           </div>
       </div>
   </div>
   @endif

    <!-- Travel Hourly Details Modal -->
    @if(isset($svc['travel_hourly']) && $svc['travel_hourly'] > 0)
        <div class="modal fade service-modal-compact" id="travel_hourlyDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="travel_hourlyModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                    @php
                        $firstOrder = $serviceData['travel_hourly'][0] ?? null;
                        $firstBookingData = null;
                        if ($firstOrder) {
                            $firstBookingData = is_string($firstOrder->data) ? json_decode($firstOrder->data, true) : $firstOrder->data;
                            $firstBookingData = is_array($firstBookingData) && isset($firstBookingData[0]) ? $firstBookingData[0] : $firstBookingData;
                        }
                    @endphp
                    
                    <!-- Compact Header -->
                    <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="text-white">
                                <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                    <i class="ri-time-line me-1" style="font-size: 0.9rem;"></i>Local-Tour Hourly - Tour #{{ $tour->tour_id }}
                                </h6>
                                <small class="opacity-90" style="font-size: 0.75rem;">{{ $firstBookingData['city'] ?? 'Location not specified' }} • {{ isset($firstBookingData['bookingDate']) ? \Carbon\Carbon::parse($firstBookingData['bookingDate'])->format('M d, Y') : 'Date TBC' }}</small>
                            </div>
                            <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('travel_hourly', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body p-2" style="background: #f8f9fa;">
                        @if(isset($serviceData['travel_hourly']) && count($serviceData['travel_hourly']) > 0)
                            @foreach($serviceData['travel_hourly'] as $index => $hourlyOrder)
                                @php
                                    $hourlyData = is_string($hourlyOrder->data) ? json_decode($hourlyOrder->data, true) : $hourlyOrder->data;
                                @endphp
                                
                                @if(is_array($hourlyData))
                                    @foreach($hourlyData as $bookingIndex => $booking)
                                        @if($index > 0 || $bookingIndex > 0)
                                            <hr class="my-2">
                                        @endif
                                
                                        <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #667eea !important;">
                                            <!-- Compact Card Header -->
                                            <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);">
                                                <div class="row align-items-center g-1">
                                                    <div class="col-md-8">
                                                        <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                            <i class="ri-car-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['vehicles_name'] ?? 'Hourly Tour Booking' }}
                                                        </h6>
                                                        <small class="text-white opacity-90" style="font-size: 0.7rem;">{{ $booking['selectedHours'] ?? 'N/A' }} Hour(s) • {{ $booking['type'] ?? 'Standard' }}</small>
                                                    </div>
                                                    <div class="col-md-4 text-end">
                                                        <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                            SGD {{ ceil($booking['totalPrice'] ?? 0) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card-body p-2" style="background-color: #ffffff;">
                                                <!-- Service Schedule & Pricing -->
                                                <div class="row mb-2 g-2">
                                                    <div class="col-md-6">
                                                        <div class="bg-light rounded p-2 h-100">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                    <i class="ri-calendar-line text-white" style="font-size: 0.8rem;"></i>
                                                                </div>
                                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Service Schedule</h6>
                                                            </div>
                                                            <div class="row g-1">
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Booking Date</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') : 'N/A' }}</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Time</small>
                                                                    <div class="fw-medium text-primary" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'N/A' }}</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Hours</small>
                                                                    <span class="badge bg-info" style="font-size: 0.65rem; padding: 2px 6px;">{{ $booking['selectedHours'] ?? 'N/A' }} Hr(s)</span>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Type</small>
                                                                    <span class="badge bg-warning" style="font-size: 0.65rem; padding: 2px 6px;">{{ $booking['type'] ?? 'Standard' }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="bg-light rounded p-2 h-100 d-flex align-items-center justify-content-center">
                                                            <div class="text-center">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Total Price</small>
                                                                <div class="fw-bold" style="font-size: 1rem; color: #667eea;">SGD {{ ceil($booking['totalPrice'] ?? 0) }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Pickup Location & Vehicle -->
                                                <div class="row mb-2 g-2">
                                                    <div class="col-md-6">
                                                        <div class="bg-light rounded p-2 h-100">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                                    <i class="ri-map-pin-line text-white" style="font-size: 0.7rem;"></i>
                                                                </div>
                                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Pickup Location</h6>
                                                            </div>
                                                            <div class="row g-1">
                                                                <div class="col-12">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Point</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">City</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['city'] ?? 'N/A' }}</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Country</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['country'] ?? 'N/A' }}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="bg-light rounded p-2 h-100">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                                    <i class="ri-car-line text-white" style="font-size: 0.7rem;"></i>
                                                                </div>
                                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Vehicle Details</h6>
                                                            </div>
                                                            <div class="row g-1 align-items-center">
                                                                <div class="col-8">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Vehicle Name</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                                    <small class="text-muted" style="font-size: 0.6rem;">{{ $booking['type'] ?? 'N/A' }} Transfer</small>
                                                                </div>
                                                                <div class="col-4">
                                                                    @if(isset($booking['image']) && !empty($booking['image']))
                                                                        <img src="{{ $booking['image'] }}" alt="{{ $booking['vehicles_name'] ?? 'Vehicle' }}" class="img-fluid rounded shadow-sm" style="height: 60px; width: 100%; object-fit: cover;">
                                                                    @else
                                                                        <div class="bg-white rounded d-flex align-items-center justify-content-center" style="height: 60px;">
                                                                            <i class="ri-car-line text-muted" style="font-size: 1.5rem;"></i>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    @endforeach
                                @endif
                            @endforeach
                        @else
                            <div class="text-center py-3">
                                <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="ri-time-line text-muted" style="font-size: 1.5rem;"></i>
                                </div>
                                <h6 class="text-dark mb-1" style="font-size: 0.9rem;">No Hourly Tour Data Available</h6>
                                <p class="text-muted mb-0" style="font-size: 0.75rem;">Hourly tour services are booked but detailed information is not available.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Compact Footer -->
                    <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                        <div class="d-flex gap-2 w-100 justify-content-end">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('travel_hourly', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                                <i class="ri-close-line me-1"></i>Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Travel Point Details Modal -->
    @if(isset($svc['travel_point']) && $svc['travel_point'] > 0)
        <div class="modal fade service-modal-compact" id="travel_pointDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="travel_pointModalLabel{{ $tour->tour_id }}" aria-hidden="true">
            <div class="modal-dialog">
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
                                <p class="mb-0 opacity-75">Tour #{{ $tour->tour_id }} Point to Point Transfer</p>
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
                                                <div class="row align-items-center">
                                                    <div class="col-md-8">
                                                        <h5 class="card-title mb-0 fw-bold">
                                                            <i class="ri-car-line me-2"></i>{{ $booking['vehicles_name'] ?? 'Point to Point Transfer' }}
                                                        </h5>
                                                    </div>
                                                    <div class="col-md-4 text-end">
                                                        <div class="bg-white rounded-pill px-3 py-2 d-inline-block">
                                                            <span class="text-success fw-bold fs-5">SGD {{ ceil($booking['totalPrice'] ?? 0) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transfer Schedule -->
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
       <div class="modal fade service-modal-compact" id="local_transportDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="local_transportModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
           <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
               <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                   @php
                       $firstOrder = $serviceData['local_transport'][0] ?? null;
                       $firstBookingData = null;
                       $headerFromZone = 'N/A';
                       $headerToZone = 'N/A';
                       
                       if ($firstOrder) {
                           $firstBookingData = is_string($firstOrder->data) ? json_decode($firstOrder->data, true) : $firstOrder->data;
                           $firstBookingData = is_array($firstBookingData) && isset($firstBookingData[0]) ? $firstBookingData[0] : $firstBookingData;
                           
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
                   
                   <!-- Compact Header -->
                   <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                       <div class="d-flex align-items-center justify-content-between w-100">
                           <div class="text-white">
                               <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                   <i class="ri-car-line me-1" style="font-size: 0.9rem;"></i>Local Transport - Tour #{{ $tour->tour_id }}
                               </h6>
                               <small class="opacity-90" style="font-size: 0.75rem;">{{ isset($firstBookingData['bookingDate']) ? \Carbon\Carbon::parse($firstBookingData['bookingDate'])->format('M d, Y') : 'Date not specified' }}</small>
                           </div>
                           <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('local_transport', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                       </div>
                   </div>

                   <!-- Modal Body -->
                   <div class="modal-body p-2" style="background: #f8f9fa;">
                       @if(isset($serviceData['local_transport']) && count($serviceData['local_transport']) > 0)
                           @foreach($serviceData['local_transport'] as $index => $transportOrder)
                               @php
                                   $transportData = is_string($transportOrder->data) ? json_decode($transportOrder->data, true) : $transportOrder->data;
                               @endphp
                               
                               @if(is_array($transportData))
                                   @foreach($transportData as $bookingIndex => $booking)
                                       @php
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
                                           <hr class="my-2">
                                       @endif
                               
                                       <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #4facfe !important;">
                                           <!-- Compact Card Header -->
                                           <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);">
                                               <div class="row align-items-center g-1">
                                                   <div class="col-md-8">
                                                       <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                           <i class="ri-car-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['vehicles_name'] ?? 'Local Transport Service' }}
                                                       </h6>
                                                       <small class="text-white opacity-90" style="font-size: 0.7rem;">{{ $booking['distance'] ?? 'N/A' }} km • {{ $booking['type'] ?? 'Standard' }}</small>
                                                   </div>
                                                   <div class="col-md-4 text-end">
                                                       <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                           SGD {{ ceil($booking['totalPrice'] ?? 0) }}
                                                       </span>
                                                   </div>
                                               </div>
                                           </div>

                                           <div class="card-body p-2" style="background-color: #ffffff;">
                                               <!-- Transport Schedule & Passengers -->
                                               <div class="row mb-2 g-2">
                                                   <div class="col-md-6">
                                                       <div class="bg-light rounded p-2 h-100">
                                                           <div class="d-flex align-items-center mb-1">
                                                               <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                   <i class="ri-calendar-line text-white" style="font-size: 0.8rem;"></i>
                                                               </div>
                                                               <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Transport Schedule</h6>
                                                           </div>
                                                           <div class="row g-1">
                                                               <div class="col-6">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Date</small>
                                                                   <div class="fw-medium" style="font-size: 0.75rem;">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') : 'N/A' }}</div>
                                                               </div>
                                                               <div class="col-6">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Time</small>
                                                                   <div class="fw-medium text-primary" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'N/A' }}</div>
                                                               </div>
                                                               <div class="col-6">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Distance</small>
                                                                   <span class="badge bg-info" style="font-size: 0.65rem; padding: 2px 6px;">{{ $booking['distance'] ?? 'N/A' }} km</span>
                                                               </div>
                                                               <div class="col-6">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Type</small>
                                                                   <span class="badge bg-warning" style="font-size: 0.65rem; padding: 2px 6px;">{{ $booking['type'] ?? 'Standard' }}</span>
                                                               </div>
                                                           </div>
                                                       </div>
                                                   </div>
                                                   <div class="col-md-6">
                                                       <div class="bg-light rounded p-2 h-100">
                                                           <div class="d-flex align-items-center mb-1">
                                                               <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                   <i class="ri-group-line text-white" style="font-size: 0.8rem;"></i>
                                                               </div>
                                                               <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Passengers</h6>
                                                           </div>
                                                           <div class="row g-1">
                                                               <div class="col-6 text-center">
                                                                   <div class="bg-white rounded p-1">
                                                                       <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['adults'] ?? 0 }}</div>
                                                                       <small class="text-muted" style="font-size: 0.6rem;">Adults</small>
                                                                   </div>
                                                               </div>
                                                               <div class="col-6 text-center">
                                                                   <div class="bg-white rounded p-1">
                                                                       <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['children'] ?? 0 }}</div>
                                                                       <small class="text-muted" style="font-size: 0.6rem;">Children</small>
                                                                   </div>
                                                               </div>
                                                               <div class="col-12 text-center mt-1">
                                                                   <span class="badge" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; font-size: 0.65rem; padding: 2px 6px;">
                                                                       Total: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} Passenger{{ (($booking['adults'] ?? 0) + ($booking['children'] ?? 0)) == 1 ? '' : 's' }}
                                                                   </span>
                                                               </div>
                                                           </div>
                                                       </div>
                                                   </div>
                                               </div>

                                               <!-- Route Details -->
                                               <div class="bg-light rounded p-1 mb-2">
                                                   <div class="d-flex align-items-center mb-1">
                                                       <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                           <i class="ri-direction-line text-white" style="font-size: 0.7rem;"></i>
                                                       </div>
                                                       <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Route Details</h6>
                                                   </div>
                                                   <div class="bg-white rounded p-1">
                                                       <div class="row g-1">
                                                           <div class="col-md-6">
                                                               <div class="d-flex align-items-start">
                                                                   <div class="rounded-circle p-1 me-2 mt-1" style="background-color: #28a745; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">
                                                                       <i class="ri-play-circle-line text-white" style="font-size: 0.6rem;"></i>
                                                                   </div>
                                                                   <div>
                                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Location</small>
                                                                       <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
                                                                       <small class="text-success" style="font-size: 0.6rem;">Origin</small>
                                                                   </div>
                                                               </div>
                                                           </div>
                                                           <div class="col-md-6">
                                                               <div class="d-flex align-items-start">
                                                                   <div class="rounded-circle p-1 me-2 mt-1" style="background-color: #dc3545; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">
                                                                       <i class="ri-flag-line text-white" style="font-size: 0.6rem;"></i>
                                                                   </div>
                                                                   <div>
                                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Drop-off Location</small>
                                                                       <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['dropoffLocation'] ?? 'N/A' }}</div>
                                                                       <small class="text-danger" style="font-size: 0.6rem;">Destination</small>
                                                                   </div>
                                                               </div>
                                                           </div>
                                                           <div class="col-6">
                                                               <small class="text-muted d-block" style="font-size: 0.65rem;">City</small>
                                                               <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['city'] ?? 'N/A' }}</div>
                                                           </div>
                                                           <div class="col-6">
                                                               <small class="text-muted d-block" style="font-size: 0.65rem;">Country</small>
                                                               <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['country'] ?? 'N/A' }}</div>
                                                           </div>
                                                       </div>
                                                   </div>
                                               </div>

                                               <!-- Vehicle Information -->
                                               <div class="row mb-2 g-2">
                                                   <div class="col-md-8">
                                                       <div class="bg-light rounded p-2 h-100">
                                                           <div class="d-flex align-items-center mb-1">
                                                               <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                   <i class="ri-car-line text-white" style="font-size: 0.8rem;"></i>
                                                               </div>
                                                               <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Vehicle Details</h6>
                                                           </div>
                                                           <div class="row g-1">
                                                               <div class="col-6">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Vehicle Name</small>
                                                                   <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                               </div>
                                                               <div class="col-6">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Service Type</small>
                                                                   <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['type'] ?? 'N/A' }} Transport</div>
                                                               </div>
                                                           </div>
                                                       </div>
                                                   </div>
                                                   <div class="col-md-4">
                                                       @if(isset($booking['image']))
                                                           <img src="{{ $booking['image'] }}" alt="{{ $booking['vehicles_name'] ?? 'Vehicle' }}" class="img-fluid rounded shadow-sm" style="height: 80px; width: 100%; object-fit: cover;">
                                                       @else
                                                           <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 80px;">
                                                               <i class="ri-car-line text-muted" style="font-size: 2rem;"></i>
                                                           </div>
                                                       @endif
                                                   </div>
                                               </div>
                                           </div>
                                       </div>

                                   @endforeach
                               @endif
                           @endforeach
                       @else
                           <div class="text-center py-3">
                               <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                   <i class="ri-car-line text-muted" style="font-size: 1.5rem;"></i>
                               </div>
                               <h6 class="text-dark mb-1" style="font-size: 0.9rem;">No Local Transport Data Available</h6>
                               <p class="text-muted mb-0" style="font-size: 0.75rem;">Local transport services are booked but detailed information is not available.</p>
                           </div>
                       @endif
                   </div>

                   <!-- Compact Footer -->
                   <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                       <div class="d-flex gap-2 w-100 justify-content-end">
                           <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('local_transport', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                               <i class="ri-close-line me-1"></i>Close
                           </button>
                       </div>
                   </div>
               </div>
           </div>
       </div>
   @endif
@endforeach
</div>

<script>
function followUpNow(tourId) {
    // Implementation for immediate follow up
    console.log('Following up tour', tourId);
    // Open follow up modal or redirect to communication page
}

function convertToTentative(tourId) {
    if (confirm('Are you sure you want to mark this prospect as Tentative?')) {
        console.log('Converting tour', tourId, 'to Tentative status');
    }
}

function convertToConfirmed(tourId) {
    if (confirm('Are you sure you want to mark this prospect as Confirmed?')) {
        console.log('Converting tour', tourId, 'to Confirmed status');
    }
}

function scheduleCallback(tourId) {
    // Implementation for scheduling callback
    console.log('Scheduling callback for tour', tourId);
}

function markAsLost(tourId) {
    if (confirm('Are you sure you want to mark this prospect as lost? This will remove it from follow-ups.')) {
        console.log('Marking tour', tourId, 'as lost');
    }
}

window.cancelTour = function(encryptedTourId, displayId) {
    // Show SweetAlert confirmation dialog
    Swal.fire({
        title: 'Cancel Tour?',
        text: `Are you sure you want to cancel tour ${displayId}? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, cancel it!',
        cancelButtonText: 'No, keep it'
    }).then((result) => {
        if (result.isConfirmed) {
            // Extract tour ID from the encrypted string to match the button ID
            const tourIdMatch = document.querySelector(`[onclick*="${encryptedTourId}"]`);
            const button = tourIdMatch;
            const originalContent = button.innerHTML;
            
            // Show loading state
            button.innerHTML = '<i class="ri-loader-4-line spin"></i> Cancelling...';
            button.disabled = true;
            
            // Send AJAX request to cancel tour
            fetch(`{{ route('bookings.cancel-tour', '') }}/${encryptedTourId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    Swal.fire({
                        title: 'Cancelled!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                    
                    // Update button to show cancelled state
                    button.innerHTML = '<i class="ri-check-line"></i> Cancelled';
                    button.classList.remove('btn-outline-danger');
                    button.classList.add('btn-success');
                    button.disabled = true;
                    
                    // Refresh the page after a short delay to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    // Show error message
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to cancel tour',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    
                    // Restore button state
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error cancelling tour:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while cancelling the tour. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                
                // Restore button state
                button.innerHTML = originalContent;
                button.disabled = false;
            });
        }
    });
};

function scheduleFollowUp() {
    const selectedTours = document.querySelectorAll('.row-checkbox:checked');
    if (selectedTours.length === 0) {
        alert('Please select at least one prospect to schedule follow-up.');
        return;
    }
    console.log('Scheduling follow-up for', selectedTours.length, 'prospects');
}

function requestPayment(tourId) {
    console.log('Requesting payment for tour', tourId);
    // Implementation for payment request
}

function extendDeadline(tourId) {
    const newDeadline = prompt('Enter new deadline (YYYY-MM-DD):');
    if (newDeadline) {
        console.log('Extending deadline for tour', tourId, 'to', newDeadline);
    }
}

function exportData() {
    console.log('Exporting follow-up data...');
}

// Service Modal Functions
function openServiceModal(serviceType, tourId, event) {
    console.log('Opening service modal:', serviceType, 'for tour:', tourId);
    
    if (event) {
        event.preventDefault();
        event.stopPropagation();
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

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const destinationFilter = document.getElementById('destinationFilter');
    const agentFilter = document.getElementById('agentFilter');
    // const followUpFilter = document.getElementById('followUpFilter'); // Commented out since element is not available
    const startDateFilter = document.getElementById('startDateFilter');
    const endDateFilter = document.getElementById('endDateFilter');
    const today = new Date().toISOString().split('T')[0];
    
    // Add event listeners
    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (statusFilter) statusFilter.addEventListener('change', filterTable);
    if (destinationFilter) destinationFilter.addEventListener('change', filterTable);
    if (agentFilter) agentFilter.addEventListener('change', filterTable);
    // if (followUpFilter) followUpFilter.addEventListener('change', filterTable); // Commented out since element is not available
    if (startDateFilter) {
        startDateFilter.setAttribute('max', today);
        startDateFilter.addEventListener('change', function() {
            if (endDateFilter) {
                if (startDateFilter.value) {
                    endDateFilter.setAttribute('min', startDateFilter.value);
                    if (endDateFilter.value && endDateFilter.value < startDateFilter.value) {
                        endDateFilter.value = startDateFilter.value;
                    }
                } else {
                    endDateFilter.removeAttribute('min');
                }
            }
            filterTable();
        });
    }
    if (endDateFilter) {
        endDateFilter.setAttribute('max', today);
        if (startDateFilter && startDateFilter.value) {
            endDateFilter.setAttribute('min', startDateFilter.value);
        }
        endDateFilter.addEventListener('change', function() {
            if (startDateFilter && endDateFilter.value && startDateFilter.value && endDateFilter.value < startDateFilter.value) {
                startDateFilter.value = endDateFilter.value;
                startDateFilter.dispatchEvent(new Event('change'));
                return;
            }
            filterTable();
        });
    }
    
    // Select all functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
    
    // Apply initial filter on page load to show today's data
    filterTable();
});

function filterTable() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    const destinationFilter = document.getElementById('destinationFilter')?.value || '';
    const agentFilter = document.getElementById('agentFilter')?.value || '';
    const startDateValue = document.getElementById('startDateFilter')?.value || '';
    const endDateValue = document.getElementById('endDateFilter')?.value || '';

    const rows = document.querySelectorAll('#toursTable tbody tr');
    const totalRows = Array.from(rows).filter(r => r.cells.length > 1).length;
    if (typeof table !== 'undefined' && table && typeof table.rows === 'function') {
        table.rows('.dt-hasChild').every(function() {
            if (this.child.isShown()) this.child.hide();
            $(this.node()).removeClass('dt-hasChild');
        });
    }

    let visibleCount = 0;

    rows.forEach(row => {
        if (row.cells.length === 1) return; // Skip empty state row

        const tourDetails = row.cells[1]?.textContent.toLowerCase() || '';
        const destination = row.cells[2]?.querySelector('.fw-medium')?.textContent || '';
        const agent = row.cells[6]?.querySelector('.fw-medium')?.textContent || '';
        const createdBy = row.cells[7]?.querySelector('.fw-medium')?.textContent || '';
        const status = row.cells[8]?.querySelector('.badge')?.textContent || '';
        const updatedAt = row.getAttribute('data-updated-at');
        const createdAt = row.getAttribute('data-created-at');

        let show = true;

        if (searchTerm &&
            !tourDetails.includes(searchTerm) &&
            !destination.toLowerCase().includes(searchTerm) &&
            !agent.toLowerCase().includes(searchTerm) &&
            !createdBy.toLowerCase().includes(searchTerm)) {
            show = false;
        }

        if (statusFilter && !status.toLowerCase().includes(statusFilter.toLowerCase())) {
            show = false;
        }

        // Destination filter - use LIKE operator logic (contains)
        // This works for multi-country destinations like "India, Singapore"
        if (destinationFilter) {
            // Split destination by comma and trim spaces
            const destinationCountries = destination.split(',').map(c => c.trim());
            // Check if the selected destination is in the destination list
            if (!destinationCountries.includes(destinationFilter)) {
                show = false;
            }
        }

        if (agentFilter && agent !== agentFilter) {
            show = false;
        }

        if ((startDateValue || endDateValue) && (updatedAt || createdAt)) {
            const startDate = startDateValue ? new Date(startDateValue + 'T00:00:00') : null;
            const endDate = endDateValue ? new Date(endDateValue + 'T23:59:59') : null;
            let dateInRange = false;

            if (updatedAt) {
                const updatedDate = new Date(updatedAt + 'T00:00:00');
                if ((!startDate || updatedDate >= startDate) && (!endDate || updatedDate <= endDate)) {
                    dateInRange = true;
                }
            }

            if (!dateInRange && createdAt) {
                const createdDate = new Date(createdAt + 'T00:00:00');
                if ((!startDate || createdDate >= startDate) && (!endDate || createdDate <= endDate)) {
                    dateInRange = true;
                }
            }

            if (!dateInRange) {
                show = false;
            }
        } else if (startDateValue || endDateValue) {
            // If dates are selected but no timestamps available, hide row
            show = false;
        }

        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });

    const visibleRows = Array.from(document.querySelectorAll('#toursTable tbody tr')).filter(r => r.style.display !== 'none' && r.cells.length > 1);
    const rangeCount = visibleCount;
    const prospectCount = visibleRows.filter(r => r.getAttribute('data-tour-status') === 'Prospect').length;
    const tentativeCount = visibleRows.filter(r => r.getAttribute('data-tour-status') === 'Tentative').length;

    const sevenDaysAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    const overdueCount = visibleRows.filter(r => {
        const updated = r.getAttribute('data-updated-at');
        return updated && updated < sevenDaysAgo;
    }).length;

    const countEl = document.getElementById('rangeCount');
    const labelEl = document.getElementById('rangeLabel');
    const statFollowUps = document.getElementById('statFollowUpsCount');
    const statFollowUpsLabel = document.getElementById('statFollowUpsLabel');
    const statProspects = document.getElementById('statProspectsCount');
    const statProspectsLabel = document.getElementById('statProspectsLabel');
    const statTentative = document.getElementById('statTentativeCount');
    const statTentativeLabel = document.getElementById('statTentativeLabel');
    const statOverdue = document.getElementById('statOverdueCount');
    const statOverdueLabel = document.getElementById('statOverdueLabel');

    if (countEl) countEl.textContent = rangeCount;
    if (statFollowUps) statFollowUps.textContent = rangeCount;
    if (statProspects) statProspects.textContent = prospectCount;
    if (statTentative) statTentative.textContent = tentativeCount;
    if (statOverdue) statOverdue.textContent = overdueCount;

    updateFilterResults(visibleCount, totalRows);

    if (startDateValue || endDateValue) {
        const start = startDateValue ? new Date(startDateValue) : null;
        const end = endDateValue ? new Date(endDateValue) : null;
        let label = '';

        if (start && end) {
            if (start.getTime() === end.getTime()) {
                label = start.toLocaleString('default', { month: 'short', day: '2-digit', year: 'numeric' });
            } else if (start.getMonth() === end.getMonth() && start.getFullYear() === end.getFullYear()) {
                if (start.getDate() === 1 && end.getDate() === new Date(end.getFullYear(), end.getMonth() + 1, 0).getDate()) {
                    label = start.toLocaleString('default', { month: 'long', year: 'numeric' });
                } else {
                    label = `${start.getDate()}-${end.getDate()} ${start.toLocaleString('default', { month: 'short' })}, ${start.getFullYear()}`;
                }
            } else {
                label = `${start.toLocaleString('default', { month: 'short' })} ${start.getDate()} - ${end.toLocaleString('default', { month: 'short' })} ${end.getDate()}, ${end.getFullYear()}`;
            }
        } else if (start) {
            label = `From ${start.toLocaleString('default', { month: 'short', day: '2-digit', year: 'numeric' })}`;
        } else if (end) {
            label = `Up to ${end.toLocaleString('default', { month: 'short', day: '2-digit', year: 'numeric' })}`;
        }

        if (labelEl && label) labelEl.textContent = label;
        if (statFollowUpsLabel && label) statFollowUpsLabel.textContent = `Follow Ups - ${label}`;
        if (statProspectsLabel && label) statProspectsLabel.textContent = `Prospects - ${label}`;
        if (statTentativeLabel && label) statTentativeLabel.textContent = `Tentative - ${label}`;
        if (statOverdueLabel && label) statOverdueLabel.textContent = `Overdue - ${label}`;
    } else {
        const month = new Date().toLocaleString('default', { month: 'long' });
        if (labelEl) labelEl.textContent = month;
        if (statFollowUpsLabel) statFollowUpsLabel.textContent = `${month} Follow Ups`;
        if (statProspectsLabel) statProspectsLabel.textContent = `${month} Prospects`;
        if (statTentativeLabel) statTentativeLabel.textContent = `${month} Tentative`;
        if (statOverdueLabel) statOverdueLabel.textContent = `${month} Overdue`;
    }
}

function resetFilters() {
    const searchInput = document.getElementById('searchInput');
    const statusSelect = document.getElementById('statusFilter');
    const destinationSelect = document.getElementById('destinationFilter');
    const agentSelect = document.getElementById('agentFilter');
    const startDateInput = document.getElementById('startDateFilter');
    const endDateInput = document.getElementById('endDateFilter');

    if (searchInput) searchInput.value = '';
    if (statusSelect) statusSelect.value = '';
    // Reset Select2 dropdowns properly
    if (destinationSelect && $('#destinationFilter').hasClass('select2-hidden-accessible')) {
        $('#destinationFilter').val('').trigger('change');
    } else if (destinationSelect) {
        destinationSelect.value = '';
    }
    if (agentSelect && $('#agentFilter').hasClass('select2-hidden-accessible')) {
        $('#agentFilter').val('').trigger('change');
    } else if (agentSelect) {
        agentSelect.value = '';
    }
    if (startDateInput) startDateInput.value = '';
    if (endDateInput) {
        endDateInput.value = '';
        endDateInput.removeAttribute('min');
    }
    filterTable();
    
    // Show success message
    showFilterResetMessage();
}

function updateFilterResults(visibleCount, totalCount) {
    const filterResultsBadge = document.getElementById('filterResultsBadge');
    if (filterResultsBadge) {
        // Only show badge if there are meaningful results and filters are actually applied
        const hasActiveFilters = checkActiveFilters();
        if (hasActiveFilters && visibleCount < totalCount && totalCount > 1) {
            filterResultsBadge.textContent = `${visibleCount} of ${totalCount} shown`;
            filterResultsBadge.style.display = 'inline-block';
        } else {
            filterResultsBadge.style.display = 'none';
        }
    }
}

function checkActiveFilters() {
    const searchInput = document.getElementById('searchInput')?.value || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    const destinationFilter = document.getElementById('destinationFilter')?.value || '';
    const agentFilter = document.getElementById('agentFilter')?.value || '';
    const dateStart = document.getElementById('startDateFilter')?.value || '';
    const dateEnd = document.getElementById('endDateFilter')?.value || '';
    
    return searchInput || statusFilter || destinationFilter || agentFilter || dateStart || dateEnd;
}

function showFilterResetMessage() {
    // Create a temporary success message
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
    alertDiv.innerHTML = `
        <i class="ri-check-circle-line me-2"></i>
        <strong>Filters Reset!</strong> All filters have been cleared successfully.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 3000);
}
</script>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<script>
    // Wait for all scripts to load before initializing
    $(document).ready(function() {
        // Small delay to ensure all scripts are loaded
        setTimeout(function() {
            initializeSelect2();
            initializeDataTable();
            filterTable();
        }, 200);
    });
    
    function initializeSelect2() {
        // Initialize Select2 for Destination filter
        $('#destinationFilter').select2({
            placeholder: 'All Destinations',
            allowClear: true,
            width: '100%'
        });
        
        // Initialize Select2 for Agent filter
        $('#agentFilter').select2({
            placeholder: 'All Agents',
            allowClear: true,
            width: '100%'
        });
        
        // Trigger filterTable when Select2 values change (including when cleared)
        $('#destinationFilter, #agentFilter').on('change', function() {
            // When cleared, the value will be empty string, which shows all results
            filterTable();
        });
    }
    
    var table;
    function initializeDataTable() {
        // Check if DataTable is already initialized
        if ($.fn.DataTable.isDataTable('.datatables-basic')) {
            $('.datatables-basic').DataTable().destroy();
        }
        
        const headerTexts = $('#toursTable thead th').map(function() {
            return $(this).text().trim();
        }).get();
        const colIndex = (name) => headerTexts.findIndex(t => t === name);

        const guestsIdx = colIndex('Guests');
        const servicesIdx = colIndex('Services');
        const statusIdx = colIndex('Status');
        const followUpStatusIdx = colIndex('Follow Up Status');
        const agentNegotiationIdx = colIndex('Agent Negotiation');
        const negotiationIdx = colIndex('Negotiation');
        const actionsIdx = colIndex('Actions');

        const nonOrderableTargets = [guestsIdx, servicesIdx, statusIdx, followUpStatusIdx, agentNegotiationIdx, negotiationIdx, actionsIdx].filter(i => i >= 0);
        const nonSearchableTargets = [agentNegotiationIdx, negotiationIdx, actionsIdx].filter(i => i >= 0);

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
            // order: [[8, 'desc']], // Sort by Last Contact column (index 8) in descending order
            columnDefs: [
                {
                    targets: nonOrderableTargets,
                    orderable: false,
                },
                {
                    targets: nonSearchableTargets,
                    searchable: false,
                },
                {
                    targets: [guestsIdx, servicesIdx].filter(i => i >= 0),
                    orderable: false,
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
        
        // Modal helper functions for Update Price
        window.openFollowupModal = function(button, route) {
            var modalEl = document.getElementById('followupUpdateModal');
            var form = document.getElementById('followupUpdateForm');
            var priceInput = document.getElementById('followup_current_price');
            var commentInput = document.getElementById('followup_comment');
            var idInput = document.getElementById('followup_modal_enquiry_id');
            var displayActual = document.getElementById('followup_display_actual');
            var displayPrice = document.getElementById('followup_display_price');
            var displayDiscount = document.getElementById('followup_display_discount');
            var displayComment = document.getElementById('followup_display_comment');

            form.action = route || '';
            idInput.value = button.getAttribute('data-enquiry-id') || '';
            var actual = button.getAttribute('data-actual') || '';
            var prevPrice = button.getAttribute('data-price') || '';
            var discount = button.getAttribute('data-discount') || '';
            var prevComment = button.getAttribute('data-comment') || '';

            // Set displays
            displayActual.textContent = actual !== '' ? actual : '—';
            displayDiscount.textContent = discount !== '' ? discount : '—';
            displayPrice.textContent = prevPrice !== '' ? prevPrice : '—';
            displayComment.textContent = prevComment !== '' ? prevComment : '—';

            // Prefill price with previous negotiated amount; comment left blank
            priceInput.value = prevPrice;
            commentInput.value = '';
            if (actual !== '') priceInput.setAttribute('max', actual); else priceInput.removeAttribute('max');

            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        };

        window.validateFollowupPrice = function(input) {
            var maxValue = parseFloat(input.getAttribute('max'));
            var currentValue = parseFloat(input.value);
            var warningMessage = document.getElementById('followup-warning-message');
            
            if (!isNaN(maxValue) && !isNaN(currentValue) && currentValue > maxValue) {
                input.value = maxValue; // Reset to maximum allowed value
                warningMessage.classList.remove('d-none');
                
                setTimeout(function() {
                    warningMessage.classList.add('d-none');
                }, 3000);
            }
        };

        // Add form submission handler with loader
        $(document).ready(function() {
            $('#followupUpdateForm').on('submit', function(e) {
                const submitBtn = document.getElementById('followup_submit_btn');
                const cancelBtn = document.getElementById('followup_cancel_btn');
                
                // Show loader
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="ri-loader-4-line spin"></i> Submitting...';
                submitBtn.disabled = true;
                cancelBtn.disabled = true;
                
                // Form will submit naturally, no need to prevent default
            });
        });

        let agentNegotiationModalInstance = null;
        let agentNegotiationActionsDisabled = false;

        function toggleAgentNegotiationActions(disabled) {
            agentNegotiationActionsDisabled = !!disabled;
            const buttons = [
                document.getElementById('agentNegotiationCancelBtn'),
                document.getElementById('agentNegotiationConfirmBtn'),
                document.getElementById('agentNegotiationSubmitBtn')
            ];
            buttons.forEach(btn => {
                if (btn) {
                    btn.disabled = agentNegotiationActionsDisabled;
                    btn.classList.toggle('disabled', agentNegotiationActionsDisabled);
                }
            });
        }

        window.openAgentNegotiationModal = function(button) {
            const modalEl = document.getElementById('agentNegotiationModal');
            if (!modalEl) return;

            if (!agentNegotiationModalInstance) {
                agentNegotiationModalInstance = new bootstrap.Modal(modalEl);
            }

            const form = document.getElementById('agentNegotiationForm');
            const tourIdInput = document.getElementById('agent_negotiation_tour_id');
            const actionInput = document.getElementById('agent_negotiation_action');
            const actualInput = document.getElementById('agent_negotiation_actual_amount');
            const amountInput = document.getElementById('agentNegotiationAmount');
            const remarkInput = document.getElementById('agentNegotiationRemark');
            const warning = document.getElementById('agentNegotiationWarning');
            const displayEl = document.getElementById('agentNegotiationDisplayId');
            const currentAmountEl = document.getElementById('agentNegotiationCurrentAmount');
            const lastAmountEl = document.getElementById('agentNegotiationLastAmount');
            const lastRemarkEl = document.getElementById('agentNegotiationLastRemark');
            const maxValueEl = document.getElementById('agentNegotiationMaxValue');

            const tourId = button.getAttribute('data-tour-id');
            const displayId = button.getAttribute('data-display-id') || '—';
            const tourStatus = button.getAttribute('data-tour-status') || '';
            const actualAttr = button.getAttribute('data-actual');
            const lastAttr = button.getAttribute('data-last-amount');
            const isLocked = button.getAttribute('data-negotiation-locked') === '1';
            const actualAmount = actualAttr !== null && actualAttr !== '' ? parseFloat(actualAttr) : null;
            const lastAmount = lastAttr !== null && lastAttr !== '' ? parseFloat(lastAttr) : null;
            const lastRemark = button.getAttribute('data-last-comment') || '';

            form.dataset.currentStatus = tourStatus;
            tourIdInput.value = tourId;
            actualInput.value = Number.isFinite(actualAmount) ? actualAmount : '';
            actionInput.value = 'negotiate';
            displayEl.textContent = displayId;
            warning.classList.add('d-none');

            // Determine the maximum allowed amount
            // If there's a last negotiated amount, use that as max; otherwise use current amount
            let maxAllowedAmount = null;
            if (Number.isFinite(lastAmount) && lastAmount > 0) {
                maxAllowedAmount = lastAmount;
            } else if (Number.isFinite(actualAmount) && actualAmount > 0) {
                maxAllowedAmount = actualAmount;
            }

            // Set max attribute and display max value
            if (maxAllowedAmount !== null && maxAllowedAmount > 0) {
                amountInput.setAttribute('max', maxAllowedAmount);
                maxValueEl.textContent = formatNegotiationAmount(maxAllowedAmount);
            } else {
                amountInput.removeAttribute('max');
                maxValueEl.textContent = '—';
            }

            // Display current amount
            if (Number.isFinite(actualAmount) && actualAmount > 0) {
                currentAmountEl.textContent = formatNegotiationAmount(actualAmount);
            } else {
                currentAmountEl.textContent = '—';
            }

            // Set last amount value and display
            if (Number.isFinite(lastAmount) && lastAmount > 0) {
                amountInput.value = lastAmount;
                lastAmountEl.textContent = formatNegotiationAmount(lastAmount);
            } else {
                amountInput.value = '';
                lastAmountEl.textContent = '—';
            }

            remarkInput.value = '';
            lastRemarkEl.textContent = lastRemark || '—';
            toggleAgentNegotiationActions(isLocked);

            // Add real-time validation for amount input (remove old listener first)
            const oldHandler = amountInput.oninput;
            amountInput.oninput = null;
            amountInput.addEventListener('input', function validateAmount() {
                const enteredValue = parseFloat(this.value);
                const maxValue = parseFloat(this.getAttribute('max'));
                
                if (!isNaN(enteredValue) && !isNaN(maxValue) && enteredValue > maxValue) {
                    warning.classList.remove('d-none');
                    warning.textContent = `Negotiated amount cannot exceed ${formatNegotiationAmount(maxValue)}.`;
                } else {
                    warning.classList.add('d-none');
                }
            });
            
            // Auto-revert to max value when user leaves the field (blur event)
            amountInput.addEventListener('blur', function revertToMax() {
                const enteredValue = parseFloat(this.value);
                const maxValue = parseFloat(this.getAttribute('max'));
                
                if (!isNaN(enteredValue) && !isNaN(maxValue) && enteredValue > maxValue) {
                    this.value = maxValue;
                    warning.classList.add('d-none');
                }
            });

            agentNegotiationModalInstance.show();
        };

        window.submitAgentNegotiation = function(action) {
            if (agentNegotiationActionsDisabled) {
                Swal.fire({
                    icon: 'info',
                    title: 'Negotiation locked',
                    text: 'Please respond via Check Negotiation.'
                });
                return;
            }

            const form = document.getElementById('agentNegotiationForm');
            const actionInput = document.getElementById('agent_negotiation_action');
            const amountInput = document.getElementById('agentNegotiationAmount');
            const remarkInput = document.getElementById('agentNegotiationRemark');
            const warning = document.getElementById('agentNegotiationWarning');
            const cancelBtn = document.getElementById('agentNegotiationCancelBtn');
            const confirmBtn = document.getElementById('agentNegotiationConfirmBtn');
            const submitBtn = document.getElementById('agentNegotiationSubmitBtn');
            warning.classList.add('d-none');

            if (action === 'negotiate') {
                const amountValue = parseFloat(amountInput.value);
                if (isNaN(amountValue) || amountValue <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Amount required',
                        text: 'Please enter a valid negotiation amount.'
                    });
                    return;
                }

                const max = parseFloat(amountInput.getAttribute('max'));
                if (!isNaN(max) && max > 0 && amountValue > max) {
                    warning.classList.remove('d-none');
                    warning.textContent = `Negotiated amount cannot exceed ${formatNegotiationAmount(max)}.`;
                    return;
                }

                // Show loader on negotiate button
                const originalSubmitText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="ri-loader-4-line spin"></i> Submitting...';
                submitBtn.disabled = true;
                cancelBtn.disabled = true;
                confirmBtn.disabled = true;

                actionInput.value = action;
                form.submit();
                return;
            }

            // For Cancel and Confirm actions - no validation needed for amount/remarks
            const prompts = {
                cancel: {
                    title: 'Cancel this tour?',
                    text: 'Status will be updated to a cancelled state.',
                    icon: 'warning',
                    confirmButtonText: 'Yes, cancel it',
                    confirmButtonColor: '#d33',
                    cancelButtonText: 'Keep tour'
                },
                confirm: {
                    title: 'Confirm this tour?',
                    text: 'This will move the tour to Confirmed status.',
                    icon: 'question',
                    confirmButtonText: 'Yes, confirm it',
                    confirmButtonColor: '#198754',
                    cancelButtonText: 'Review again'
                }
            };

            const prompt = prompts[action];
            if (!prompt) return;

            if (agentNegotiationModalInstance) {
                agentNegotiationModalInstance.hide();
            }

            Swal.fire({
                ...prompt,
                showCancelButton: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        // Clear amount and remarks fields if they're empty for cancel/confirm
                        // This ensures backend doesn't validate empty fields
                        if (!amountInput.value.trim()) {
                            amountInput.removeAttribute('name');
                        }
                        if (!remarkInput.value.trim()) {
                            remarkInput.removeAttribute('name');
                        }
                        
                        actionInput.value = action;
                        form.submit();
                        resolve();
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then(result => {
                if (!result.isConfirmed && agentNegotiationModalInstance) {
                    // Restore name attributes if user cancels
                    amountInput.setAttribute('name', 'amount');
                    remarkInput.setAttribute('name', 'comment');
                    agentNegotiationModalInstance.show();
                }
            });
        };

        function formatNegotiationAmount(value) {
            if (isNaN(value)) {
                return '—';
            }
            return new Intl.NumberFormat(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        }
    };
</script>
@endsection

@extends('layouts.datatablejs')
