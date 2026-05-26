@extends('layouts.layout')
@section('title', 'Package Bookings')
@extends('layouts.datatablecss')
@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Add SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<!-- Add SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

@section('content')

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
        padding-right: 50px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 48px;
        right: 10px;
    }
    .select2-container--default .select2-selection--single .select2-selection__clear {
        position: absolute;
        right: 35px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        font-size: 18px;
        color: #6c757d;
    }
    .select2-container--default .select2-selection--single .select2-selection__clear:hover {
        color: #dc3545;
    }

    #bookingsTable tbody tr {
        height: auto;
        min-height: 50px;
    }

    #bookingsTable .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        margin: 0.1rem 0.15rem;
        font-weight: 500;
    }

    #bookingsTable i {
        font-size: 1rem;
    }

    #bookingsTable .fw-medium,
    #bookingsTable .fw-bold {
        font-size: 0.875rem;
    }

    #bookingsTable small {
        font-size: 0.75rem;
    }

    #bookingsTable .btn-sm {
        padding: 0.25rem 0.55rem;
        font-size: 0.78rem;
        height: auto;
        white-space: nowrap;
    }

    #bookingsTable .d-flex.gap-3 {
        gap: 0.75rem !important;
    }

    #bookingsTable .d-flex.gap-2.flex-wrap {
        gap: 0.35rem !important;
    }

    #bookingsTable .d-flex.flex-column {
        gap: 0.15rem;
    }

    #bookingsTable .text-muted {
        font-size: 0.7rem;
    }

    #bookingsTable .d-flex.flex-column small {
        line-height: 1.3;
    }

    .package-bookings-page { 
        background-color: #f8f9fa !important; 
        min-height: 100vh; 
        padding-bottom: 2rem !important; 
    }
    .package-bookings-page .card { 
        background-color: #fff; 
        border-radius: 0.5rem; 
        box-shadow: 0 1px 3px rgba(0,0,0,0.06); 
    }

    #bookingsTable {
        font-size: 0.875rem;
        table-layout: fixed;
        width: 100% !important;
        margin-bottom: 0;
        background-color: #fff;
    }
    .dataTables_wrapper .dataTables_scroll .dataTables_scrollBody #bookingsTable,
    .dataTables_wrapper #bookingsTable { 
        width: 100% !important; 
        table-layout: fixed; 
    }
    #bookingsTable thead th {
        padding: 0.5rem 0.5rem;
        font-size: 0.8125rem;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        background-color: #f8f9fa;
    }
    #bookingsTable tbody td {
        padding: 0.5rem 0.5rem;
        vertical-align: top;
        overflow: hidden;
        background-color: #fff;
    }
    
    #bookingsTable td:nth-child(2) { 
        min-height: 72px; 
        vertical-align: top; 
    }
    #bookingsTable td.col-agent .agent-name-line { 
        font-weight: 600; 
        font-size: 0.875rem; 
        color: #0d6efd; 
        display: flex; 
        align-items: center; 
        gap: 0.35rem; 
    }
    #bookingsTable td.col-agent .agent-company-line { 
        font-size: 0.75rem; 
        color: #6c757d; 
        display: flex; 
        align-items: center; 
        gap: 0.35rem; 
        margin-top: 0.2rem; 
    }
    #bookingsTable td.col-agent .agent-empty { 
        display: inline-flex; 
        align-items: center; 
        gap: 0.35rem; 
        font-size: 0.8rem; 
        color: #6c757d; 
        font-style: italic; 
    }
    #bookingsTable td.col-created { 
        white-space: normal; 
        word-wrap: break-word; 
        overflow-wrap: break-word; 
    }
    #bookingsTable td.col-status {
        white-space: normal;
        overflow: visible;
        word-break: break-word;
    }
    #bookingsTable td.col-status .badge {
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        white-space: normal;
        text-align: left;
    }
    #bookingsTable td.col-created .created-by-line, 
    #bookingsTable td.col-created .created-at-line { 
        display: flex; 
        align-items: flex-start; 
        gap: 0.35rem; 
        line-height: 1.35; 
    }
    #bookingsTable td.col-actions { 
        min-height: 72px; 
        min-width: 160px; 
        overflow: visible; 
    }
    #bookingsTable .actions-icons-wrap {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        row-gap: 0.5rem;
        column-gap: 0.5rem;
        align-items: center;
        justify-items: center;
    }
    #bookingsTable .action-icon-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 32px;
        min-width: 32px;
        padding: 0.35rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        cursor: pointer;
        text-decoration: none;
        color: inherit;
    }
    #bookingsTable .action-icon-badge:hover { 
        background: #f1f5f9; 
        border-color: #cbd5e1; 
    }
    #bookingsTable .action-icon-badge i { 
        font-size: 1rem; 
        color: var(--action-color, #475569); 
    }

    #service-icon-global-tooltip {
        position: fixed;
        padding: 0.4rem 0.65rem;
        background: #2d3748;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 500;
        white-space: nowrap;
        border-radius: 0.375rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        z-index: 1100;
        pointer-events: none;
        display: none;
        left: 0;
        top: 0;
        transform-origin: bottom center;
    }

    /* Compact header + stats + filter bar */
    .new-enq-header-bar { 
        background: linear-gradient(135deg, #f8f9fc 0%, #fff 100%); 
        border-radius: 0.5rem; 
        border: 1px solid rgba(105, 108, 255, 0.08); 
    }
    .new-enq-stat-item { 
        transition: transform 0.15s ease, box-shadow 0.15s ease; 
        min-height: 72px; 
        padding: 0.65rem 0.75rem !important; 
    }
    .new-enq-stat-item:hover { 
        transform: translateY(-1px); 
        box-shadow: 0 4px 12px rgba(0,0,0,0.06); 
    }
    .new-enq-stat-item .stat-value { 
        font-size: 1.25rem; 
        font-weight: 600; 
        letter-spacing: -0.02em; 
        line-height: 1; 
        display: block; 
        min-height: 1.5rem; 
    }
    .new-enq-stat-item .stat-label { 
        display: block; 
        font-size: 0.7rem; 
        text-transform: uppercase; 
        letter-spacing: 0.04em; 
        opacity: 0.85; 
        margin-top: 0.15rem; 
        line-height: 1.3; 
    }
    .new-enq-stats-grid .col { 
        display: flex; 
    }
    .new-enq-stats-grid .col > div { 
        width: 100%; 
    }
    .new-enq-filter-bar { 
        background: #fff; 
        border-radius: 0.5rem; 
        border: 1px solid #e7e9ed; 
    }
    .new-enq-filter-bar .form-control, 
    .new-enq-filter-bar .form-control-sm,
    .new-enq-filter-bar .form-select, 
    .new-enq-filter-bar .form-select.form-select-sm { 
        font-size: 0.8125rem; 
        height: 38px; 
    }
    .new-enq-filter-bar .select2-container--default .select2-selection--single { 
        height: 38px !important; 
        min-height: 38px !important; 
        border-radius: 0.375rem; 
    }
    .new-enq-filter-bar .select2-container--default .select2-selection--single .select2-selection__rendered { 
        line-height: 36px !important; 
        padding-left: 10px; 
        padding-right: 32px; 
    }
    .new-enq-filter-bar .select2-container--default .select2-selection--single .select2-selection__arrow { 
        height: 36px !important; 
        right: 8px; 
    }
    .new-enq-filter-bar .select2-container--default .select2-selection--single .select2-selection__clear { 
        right: 32px; 
    }
    .new-enq-filter-bar .status-filter-col #statusFilter {
        white-space: normal;
        line-height: 1.2;
        height: auto;
        min-height: 38px;
        padding-top: 0.35rem;
        padding-bottom: 0.35rem;
    }
    .new-enq-filter-bar .status-filter-col #statusFilter option {
        white-space: normal;
    }

    .booking-status {
        padding: 6px 14px;
        font-size: 10px;
        font-weight: bold;
        border-radius: 8px;
        display: inline-block;
        text-shadow: 1px 1px 2px rgba(253, 245, 245, 0.722);
        transition: all 0.3s ease-in-out;
        box-shadow: 2px 4px 6px rgba(0, 0, 0, 0.15);
    }

    .status-confirmed {
        background-color: #a3eea3 !important;
        color: #1b5e20 !important;
        box-shadow: 0px 0px 10px rgba(76, 175, 80, 0.5);
    }

    .status-definite {
        background-color: #4caf50 !important;
        color: #ffffff !important;
        box-shadow: 0px 0px 10px rgba(76, 175, 80, 0.5);
    }

    .status-actual {
        background-color: #2196f3 !important;
        color: #ffffff !important;
        box-shadow: 0px 0px 10px rgba(33, 150, 243, 0.5);
    }

    .status-cancelled {
        background-color: #e5a6ab !important;
        color: #a71d2a !important;
        box-shadow: 0px 0px 10px rgba(220, 53, 69, 0.5);
    }

    .status-refund-pending {
        background-color: #ffc107 !important;
        color: #000 !important;
        box-shadow: 0px 0px 10px rgba(255, 193, 7, 0.5);
    }

    .status-cancel-confirmed {
        background-color: #dc3545 !important;
        color: #fff !important;
        box-shadow: 0px 0px 10px rgba(220, 53, 69, 0.5);
    }

    .status-refunded {
        background-color: #81d334 !important;
        color: #141414 !important;
        box-shadow: 0px 0px 10px rgba(108, 117, 125, 0.5);
    }

    .status-complete {
        background-color: #28a745 !important;
        color: #ffffff !important;
        box-shadow: 0px 0px 10px rgba(40, 167, 69, 0.5);
    }

    .itinerary-day {
        background-color: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 15px;
        padding: 15px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .itinerary-day-header {
        background-color: #e9ecef;
        border-radius: 8px 8px 0 0;
        padding: 10px 15px;
        margin: -15px -15px 15px -15px;
    }

    .service-item {
        background-color: #ffffff;
        border-left: 4px solid #007bff;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .service-hotel {
        border-left-color: #28a745;
    }

    .service-attraction {
        border-left-color: #fd7e14;
    }

    .service-guide {
        border-left-color: #6f42c1;
    }

    .service-restaurant {
        border-left-color: #dc3545;
    }

    .service-transport {
        border-left-color: #17a2b8;
    }

    .service-icon {
        margin-right: 8px;
    }

    .modal-xl {
        max-width: 95%;
    }

    .form-control.is-valid {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }

    .form-control.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .payment-info-text {
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .text-warning small {
        font-weight: 500;
    }
    
    .swal-z-index {
        z-index: 9999 !important;
    }
    
    .swal2-container {
        z-index: 9999 !important;
    }

</style>

<div class="container-xxl flex-grow-1 container-p-y package-bookings-page">
    <!-- Header -->
    <!-- Compact Header + Stats Bar -->
    <div class="new-enq-header-bar p-3 mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <h4 class="fw-bold mb-0" style="font-size: 1.25rem;">
                    <i class="ri-briefcase-line me-2 text-primary"></i>
                    <span class="text-muted fw-light">Bookings /</span> Package Bookings
                </h4>
                <span class="text-muted d-none d-md-inline" style="font-size: 0.875rem;">Manage and track all package bookings</span>
                <span class="badge bg-light text-primary border border-primary border-opacity-25 px-2 py-1" style="font-size: 0.75rem;">
                    <i class="ri-briefcase-line me-1"></i><span id="rangeCount">{{ isset($bookings) ? count($bookings) : 0 }}</span> <span id="rangeLabel">{{ date('F') }}</span>
                </span>
            </div>
            <div class="row g-2 new-enq-stats-grid flex-grow-1">
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-primary rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-briefcase-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statTotalCount">{{ isset($bookings) ? count($bookings) : 0 }}</span><span class="stat-label text-muted" id="statTotalLabel">Total Bookings</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-success rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-checkbox-circle-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statConfirmedCount">{{ isset($bookings) ? $bookings->where('status', '1')->count() : 0 }}</span><span class="stat-label text-muted" id="statConfirmedLabel">Confirmed</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-warning rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-time-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statDefiniteCount">{{ isset($bookings) ? $bookings->where('status', '2')->count() : 0 }}</span><span class="stat-label text-muted" id="statDefiniteLabel">Definite</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-danger rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-close-circle-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statCancelledCount">{{ isset($bookings) ? $bookings->whereIn('status', ['4', '7'])->count() : 0 }}</span><span class="stat-label text-muted" id="statCancelledLabel">Cancelled</span></div>
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
                    <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Booking ID, Travel Dates...">
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg status-filter-col">
                    <label class="form-label mb-0 small text-muted">Status</label>
                    <select class="form-select form-select-sm" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="1">Confirmed</option>
                        <option value="2">Definite</option>
                        <option value="3">Actual</option>
                        <option value="4">Cancelled</option>
                        <option value="5">Refund - Pending</option>
                        <option value="6">Refunded</option>
                        <option value="7">Cancel - Confirmed</option>
                        <option value="8">Complete</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <label class="form-label mb-0 small text-muted">Agent</label>
                    <select class="form-select form-select-sm" id="agentFilter">
                        <option value="">All Agents</option>
                        @php
                            $agents = [];
                            if (isset($bookings)) {
                                foreach ($bookings as $booking) {
                                    if ($booking->agent && !in_array($booking->agent->name, $agents)) {
                                        $agents[] = $booking->agent->name;
                                    }
                                }
                                sort($agents);
                            }
                        @endphp
                        @foreach($agents as $agent)
                            <option value="{{ $agent }}">{{ $agent }}</option>
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

    <!-- Bookings Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Package Bookings List</h5>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="exportDropdown"
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
                <table class="datatables-basic table table-bordered" id="bookingsTable">
                    <colgroup>
                        <col style="width: 2%">
                        <col style="width: 18%">
                        <col style="width: 10%">
                        <col style="width: 12%">
                        <col style="width: 12%">
                        <col style="width: 14%">
                        <col style="width: 8%">
                        <col style="width: 8%">
                    </colgroup>
                    <thead class="table-light">
                        <tr>
                            <th class="th-tooltip" data-tooltip="#">#</th>
                            <th class="th-tooltip" data-tooltip="Booking Details">Booking Details</th>
                            <th class="th-tooltip" data-tooltip="Travel Dates">Travel Dates</th>
                            <th class="th-tooltip" data-tooltip="Status">Status</th>
                            <th class="th-tooltip" data-tooltip="Agent">Agent</th>
                            <th class="th-tooltip" data-tooltip="Actions">Actions</th>
                            <th class="th-tooltip" data-tooltip="Created">Created</th>
                            <th class="th-tooltip" data-tooltip="Auto Cancel Date">Auto Cancel Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($bookings) && count($bookings) > 0)
                            @foreach($bookings as $key => $booking)
                            @php
                                // Check if booking_details is already an array or needs to be decoded
                                $bookingDetails = is_array($booking->booking_details) ? $booking->booking_details : json_decode($booking->booking_details, true);
                                
                                // Calculate travel dates range
                                $travelDates = '';
                                if (!empty($bookingDetails['itinerary'])) {
                                    $firstDay = reset($bookingDetails['itinerary']);
                                    $lastDay = end($bookingDetails['itinerary']);
                                    
                                    if (isset($firstDay['date']) && isset($lastDay['date'])) {
                                        $travelDates = $firstDay['date'] . ' - ' . $lastDay['date'];
                                    }
                                }
                                
                                // Calculate duration
                                $duration = !empty($bookingDetails['itinerary']) ? count($bookingDetails['itinerary']) : 0;
                                
                                // Get pax info
                                $adultCount = $bookingDetails['adult_count'] ?? 0;
                                $childCount = $bookingDetails['child_count'] ?? 0;
                                $totalPax = $adultCount + $childCount;
                                
                                // Get price info
                                $totalPrice = $bookingDetails['total_price'] ?? 0;
                                $currency = $bookingDetails['currency'] ?? 'SGD';
                                $personsForList = $totalPax;
                                $daysForList = $duration ?: 1;
                                $bookingTaxesForList = is_array($booking->taxes) ? $booking->taxes : (is_string($booking->taxes) ? json_decode($booking->taxes, true) : []);
                            @endphp
                            <tr 
                                data-created-at="{{ optional($booking->created_at)->toDateString() }}"
                                data-booking-status="{{ $booking->status }}"
                            >
                                <td>{{ $key + 1 }}</td>
                                <td class="align-top">
                                    <div class="d-flex flex-column gap-1">
                                        <strong class="text-primary">{{ $booking->booking_id }}</strong>
                                        <small class="text-muted">{{ $travelDates }}</small>
                                        <span class="text-white d-inline-block px-2 py-0 rounded" style="background: #3b82f6; font-weight: 500; font-size: 0.75rem; width: fit-content;">{{ $duration }} Days</span>
                                        <div class="d-flex align-items-center gap-2 flex-nowrap">
                                            <span title="Adults"><i class="ri-user-line text-success"></i> {{ $adultCount }}</span>
                                            <span title="Children"><i class="ri-user-smile-line text-warning"></i> {{ $childCount }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-top">
                                    <small>{{ $travelDates }}</small>
                                </td>
                                <td class="col-status">
                                    @php
                                        $statusClass = '';
                                        $statusIcon = '';
                                        $statusText = '';
                                        switch($booking->status) {
                                            case '1':
                                                $statusClass = 'bg-success';
                                                $statusIcon = 'ri-checkbox-circle-line';
                                                $statusText = 'Confirmed';
                                                break;
                                            case '2':
                                                $statusClass = 'bg-warning';
                                                $statusIcon = 'ri-time-line';
                                                $statusText = 'Definite';
                                                break;
                                            case '3':
                                                $statusClass = 'bg-info';
                                                $statusIcon = 'ri-check-line';
                                                $statusText = 'Actual';
                                                break;
                                            case '4':
                                                $statusClass = 'bg-danger';
                                                $statusIcon = 'ri-close-circle-line';
                                                $statusText = 'Cancelled';
                                                break;
                                            case '5':
                                                $statusClass = 'bg-warning';
                                                $statusIcon = 'ri-time-line';
                                                $statusText = 'Refund - Pending';
                                                break;
                                            case '6':
                                                $statusClass = 'bg-success';
                                                $statusIcon = 'ri-checkbox-circle-line';
                                                $statusText = 'Refunded';
                                                break;
                                            case '7':
                                                $statusClass = 'bg-danger';
                                                $statusIcon = 'ri-close-circle-line';
                                                $statusText = 'Cancel - Confirmed';
                                                break;
                                            case '8':
                                                $statusClass = 'bg-success';
                                                $statusIcon = 'ri-checkbox-circle-line';
                                                $statusText = 'Complete';
                                                break;
                                            default:
                                                $statusClass = 'bg-secondary';
                                                $statusIcon = 'ri-question-line';
                                                $statusText = 'Unknown';
                                        }
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        <i class="{{ $statusIcon }} me-1"></i>{{ $statusText }}
                                    </span>
                                </td>
                                <td class="col-agent">
                                    <div class="d-flex flex-column">
                                        @if($booking->agent)
                                            <span class="agent-name-line"><i class="ri-user-line"></i><span>{{ $booking->agent->name }}</span></span>
                                            <span class="agent-company-line"><i class="ri-building-line"></i><span>{{ $booking->agent->company ?? 'N/A' }}</span></span>
                                        @else
                                            <span class="agent-empty"><i class="ri-user-unfollow-line"></i><span>No agent assigned</span></span>
                                        @endif
                                    </div>
                                </td>
                                <td class="align-top col-actions">
                                    <div class="actions-icons-wrap">
                                        <!-- <a href="javascript:void(0);" class="action-icon-badge" style="--action-color: #0369a1;" data-toggle="modal" data-target="#viewBookingModal{{ $booking->id }}" data-bs-toggle="modal" data-bs-target="#viewBookingModal{{ $booking->id }}" data-tooltip="View">
                                            <i class="ri-eye-line"></i>
                                        </a> -->
                                        <a href="{{ route('package.booking.details', $booking->booking_id) }}" class="action-icon-badge" style="--action-color: #0369a1;" data-tooltip="View details">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        @if(!in_array($booking->status, ['5', '6', '7']))
                                            <a href="{{ route('package.booking.edit', $booking->booking_id) . '?return_url=' . urlencode(url()->full()) }}" class="action-icon-badge" style="--action-color: #7c3aed;" data-tooltip="Edit / add available add-ons">
                                                <i class="ri-edit-2-line"></i>
                                            </a>
                                        @endif
                                        @if(in_array(auth()->user()->role_id, [11, 33, 37, 38, 128, 131, 132, 134, 135, 137, 138]) && !in_array($booking->status, ['5', '6', '7']))
                                            <a href="javascript:void(0);" class="action-icon-badge" style="--action-color: #16a34a;" data-bs-toggle="modal" data-bs-target="#addPaymentModal{{ $booking->id }}" data-tooltip="Add Payment">
                                                <i class="ri-add-line"></i>
                                            </a>
                                        @endif
                                        @if(in_array(auth()->user()->role_id, [33,34, 37, 38, 124,125, 128, 129, 130,132,133, 134, 135, 136, 137,138]) && in_array($booking->status, ['1', '2']))
                                            <a href="javascript:void(0);" class="action-icon-badge" style="--action-color: #dc2626;" data-booking-id="{{ $booking->booking_id }}" onclick="cancelBooking(this)" data-tooltip="Cancel">
                                                <i class="ri-close-circle-line"></i>
                                            </a>
                                        @endif
                                        @if(in_array(auth()->user()->role_id, [36, 126, 127, 129, 131, 133, 134, 136, 137, 138]) && $booking->status == '5')
                                            <a href="javascript:void(0);" class="action-icon-badge" style="--action-color: #ea580c;" data-booking-id="{{ $booking->booking_id }}" onclick="processRefund(this)" data-tooltip="Process Refund">
                                                <i class="ri-wallet-2-line"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="col-created align-top">
                                    <div class="d-flex flex-column">
                                        <span class="created-by-line fw-medium" title="Created at">
                                            <i class="ri-calendar-line"></i>
                                            <span>{{ $booking->created_at->format('D, M d, Y') }}</span>
                                        </span>
                                        <span class="created-at-line" title="Created at">
                                            <i class="ri-time-line"></i>
                                            <span>{{ $booking->created_at->format('h:i A') }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="col-auto-cancel">
                                    <div class="d-flex flex-column">
                                        @if($booking->auto_cancel_date)
                                            <span class="fw-semibold">
                                                <i class="fas fa-calendar-times text-warning me-1"></i>
                                                {{ \Carbon\Carbon::parse($booking->auto_cancel_date)->format('D, M d, Y') }}
                                            </span>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($booking->auto_cancel_date)->format('h:i A') }}
                                            </small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ri-briefcase-line ri-48px text-muted mb-2"></i>
                                        <h6 class="text-muted">No bookings</h6>
                                        <p class="text-muted mb-0">No package bookings available.</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="service-icon-global-tooltip" aria-hidden="true"></div>

<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>

<script>
    // Load Select2 dynamically after jQuery is stable
    // This ensures Select2 attaches to the final jQuery instance (after footer loads)
    var select2Loaded = false;
    
    function loadSelect2IfNeeded(callback) {
        // Check if Select2 script already exists
        var existingScript = document.querySelector('script[src*="select2"]');
        if (existingScript && select2Loaded) {
            // Script already loaded, just wait for attachment
            waitForSelect2Attachment(callback);
            return;
        }
        
        // Wait for jQuery to be stable (after footer scripts load)
        function waitForStableJQuery() {
            var $ = window.jQuery || window.$;
            if (typeof $ === 'undefined') {
                setTimeout(waitForStableJQuery, 100);
                return;
            }
            
            // jQuery is available, now load Select2
            if (!existingScript) {
                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js';
                script.onload = function() {
                    select2Loaded = true;
                    // Wait a moment for Select2 to attach to jQuery
                    setTimeout(function() {
                        waitForSelect2Attachment(callback);
                    }, 200);
                };
                script.onerror = function() {
                    console.error('Failed to load Select2 script');
                };
                document.head.appendChild(script);
            } else {
                // Script exists, wait for it to load and attach
                waitForSelect2Attachment(callback);
            }
        }
        
        // Wait a bit for footer scripts to finish loading
        setTimeout(waitForStableJQuery, 500);
    }
    
    function waitForSelect2Attachment(callback, maxAttempts) {
        maxAttempts = maxAttempts || 30;
        var attempts = 0;
        
        function check() {
            attempts++;
            var $ = window.jQuery || window.$;
            
            if (typeof $ === 'undefined') {
                if (attempts < maxAttempts) {
                    setTimeout(check, 100);
                }
                return;
            }
            
            if (typeof $.fn !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                callback($);
            } else if (attempts < maxAttempts) {
                setTimeout(check, 100);
            } else {
                console.error('Select2 did not attach to jQuery after ' + maxAttempts + ' attempts');
                console.log('jQuery version:', $.fn ? $.fn.jquery : 'unknown');
            }
        }
        
        check();
    }
    
    // Initialize when DOM is ready
    (function() {
        function init() {
            // Wait for jQuery to be available first
            if (typeof window.jQuery === 'undefined' && typeof window.$ === 'undefined') {
                setTimeout(init, 50);
                return;
            }
            
            var $ = window.jQuery || window.$;
            
            $(document).ready(function() {
                setTimeout(function() {
                    initializeDataTable();
                    
                    // Load Select2 and wait for it to attach to jQuery
                    loadSelect2IfNeeded(function($) {
                        initializeSelect2($);
                    });
                    
                    filterTable();
                }, 300);
            });
        }
        
        // Start initialization
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
    
    function initializeSelect2($) {
        // Use the jQuery instance passed in
        $ = $ || (window.jQuery || window.$);
        
        // Final check
        if (typeof $ === 'undefined' || typeof $.fn === 'undefined' || typeof $.fn.select2 === 'undefined') {
            console.error('Select2 not available. jQuery:', typeof $ !== 'undefined', 'Select2:', typeof $ !== 'undefined' && typeof $.fn !== 'undefined' && typeof $.fn.select2 !== 'undefined');
            return;
        }
        
        // Check if element exists
        var $agentFilter = $('#agentFilter');
        if ($agentFilter.length === 0) {
            console.warn('agentFilter element not found');
            return;
        }
        
        // Check if already initialized
        if ($agentFilter.hasClass('select2-hidden-accessible')) {
            return;
        }
        
        try {
            $agentFilter.select2({
                placeholder: 'All Agents',
                allowClear: true,
                width: '100%'
            });
            
            $agentFilter.on('change', function() {
                filterTable();
            });
            
            console.log('Select2 initialized successfully on element:', $agentFilter.attr('id'));
        } catch (error) {
            console.error('Error initializing Select2:', error);
            console.error('Error stack:', error.stack);
        }
    }

    var table;
    function initializeDataTable() {
        if ($.fn.DataTable.isDataTable('.datatables-basic')) {
            $('.datatables-basic').DataTable().destroy();
        }
        
        table = $('.datatables-basic').DataTable({
            responsive: true,
            dom: 'lrtip',
            buttons: [
                'copy',
                'csv',
                'excel',
                'pdf',
                'print'
            ],
            searching: false,
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
            lengthMenu: [10, 25, 50, 100],
            pageLength: 25,
            columnDefs: (function() {
                const headerTexts = $('#bookingsTable thead th').map(function() {
                    return $(this).text().trim();
                }).get();
                const colIndex = (name) => headerTexts.findIndex(t => t === name);
                const actionsIdx = colIndex('Actions');
                const statusIdx = colIndex('Status');

                return [
                    {
                        targets: [actionsIdx].filter(i => i >= 0),
                        orderable: false,
                        searchable: false
                    },
                    {
                        targets: [statusIdx].filter(i => i >= 0),
                        orderable: false
                    }
                ];
            })(),
            initComplete: function() {
                console.log('DataTable initialized successfully');
            }
        });

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

    function filterTable() {
        const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
        const statusFilter = document.getElementById('statusFilter')?.value || '';
        const agentFilter = document.getElementById('agentFilter')?.value || '';
        const startDateValue = document.getElementById('startDateFilter')?.value || '';
        const endDateValue = document.getElementById('endDateFilter')?.value || '';
        
        const rows = document.querySelectorAll('#bookingsTable tbody tr');
        
        rows.forEach(row => {
            if (row.cells.length === 1) return;
            
            const bookingDetails = row.cells[1]?.textContent.toLowerCase() || '';
            const travelDates = row.cells[2]?.textContent.toLowerCase() || '';
            const agent = (row.cells[4]?.querySelector('.agent-name-line span')?.textContent?.trim() || row.cells[4]?.textContent || '').toLowerCase();
            const status = row.getAttribute('data-booking-status') || '';
            const createdAt = row.getAttribute('data-created-at');
            
            let show = true;
            
            if (searchTerm && !bookingDetails.includes(searchTerm) && !travelDates.includes(searchTerm) && !agent.includes(searchTerm)) {
                show = false;
            }
            
            if ((startDateValue || endDateValue) && createdAt) {
                const startDate = startDateValue ? new Date(startDateValue + 'T00:00:00') : null;
                const endDate = endDateValue ? new Date(endDateValue + 'T23:59:59') : null;
                const createdDate = new Date(createdAt + 'T00:00:00');
                
                if ((!startDate || createdDate >= startDate) && (!endDate || createdDate <= endDate)) {
                    // Date is in range
                } else {
                    show = false;
                }
            }
            
            if (statusFilter && status !== statusFilter) {
                show = false;
            }
            
            if (agentFilter && agent !== agentFilter.toLowerCase().trim()) {
                show = false;
            }
            
            row.style.display = show ? '' : 'none';
        });

        const visibleRows = Array.from(document.querySelectorAll('#bookingsTable tbody tr')).filter(r => r.style.display !== 'none' && r.cells.length > 1);
        const rangeCount = visibleRows.length;
        const totalCount = visibleRows.length;
        const confirmedCount = visibleRows.filter(r => r.getAttribute('data-booking-status') === '1').length;
        const definiteCount = visibleRows.filter(r => r.getAttribute('data-booking-status') === '2').length;
        const cancelledCount = visibleRows.filter(r => ['4', '7'].includes(r.getAttribute('data-booking-status'))).length;

        const countEl = document.getElementById('rangeCount');
        const statTotal = document.getElementById('statTotalCount');
        const statConfirmed = document.getElementById('statConfirmedCount');
        const statDefinite = document.getElementById('statDefiniteCount');
        const statCancelled = document.getElementById('statCancelledCount');
        const statTotalLabel = document.getElementById('statTotalLabel');

        if (countEl) countEl.textContent = rangeCount;
        if (statTotal) statTotal.textContent = totalCount;
        if (statConfirmed) statConfirmed.textContent = confirmedCount;
        if (statDefinite) statDefinite.textContent = definiteCount;
        if (statCancelled) statCancelled.textContent = cancelledCount;

        if (startDateValue || endDateValue) {
            const start = startDateValue ? new Date(startDateValue) : null;
            const end = endDateValue ? new Date(endDateValue) : null;
            
            let label = '';
            if (start && end) {
                if (start.getMonth() === end.getMonth() && start.getFullYear() === end.getFullYear()) {
                    if (start.getDate() === 1 && end.getDate() === new Date(end.getFullYear(), end.getMonth() + 1, 0).getDate()) {
                        label = start.toLocaleString('default', { month: 'long', year: 'numeric' });
                    } else {
                        label = `${start.getDate()}-${end.getDate()} ${start.toLocaleString('default', { month: 'short' })}, ${start.getFullYear()}`;
                    }
                } else {
                    label = `${start.toLocaleString('default', { month: 'short' })} ${start.getDate()} - ${end.toLocaleString('default', { month: 'short' })} ${end.getDate()}, ${end.getFullYear()}`;
                }
            } else if (start) {
                label = `From ${start.toLocaleString('default', { month: 'short' })} ${start.getDate()}, ${start.getFullYear()}`;
            } else if (end) {
                label = `Up to ${end.toLocaleString('default', { month: 'short' })} ${end.getDate()}, ${end.getFullYear()}`;
            }

            if (!label) label = 'Custom Range';
            
            const labelEl = document.getElementById('rangeLabel');
            if (labelEl) labelEl.textContent = label;
            if (statTotalLabel) statTotalLabel.textContent = `Total - ${label}`;
        }
    }

    function resetFilters() {
        const searchInput = document.getElementById('searchInput');
        const statusSelect = document.getElementById('statusFilter');
        const agentSelect = document.getElementById('agentFilter');
        const startDateInput = document.getElementById('startDateFilter');
        const endDateInput = document.getElementById('endDateFilter');
        const today = new Date().toISOString().split('T')[0];

        if (searchInput) searchInput.value = '';
        if (statusSelect) statusSelect.value = '';
        
        if (agentSelect && $('#agentFilter').hasClass('select2-hidden-accessible')) {
            $('#agentFilter').val(null).trigger('change');
        } else if (agentSelect) {
            agentSelect.value = '';
        }

        if (startDateInput) {
            startDateInput.value = '';
            startDateInput.setAttribute('max', today);
            startDateInput.removeAttribute('min');
        }

        if (endDateInput) {
            endDateInput.value = '';
            endDateInput.setAttribute('max', today);
            endDateInput.removeAttribute('min');
        }

        filterTable();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const agentFilter = document.getElementById('agentFilter');
        const startDateFilter = document.getElementById('startDateFilter');
        const endDateFilter = document.getElementById('endDateFilter');
        const today = new Date().toISOString().split('T')[0];
        
        if (searchInput) searchInput.addEventListener('input', filterTable);
        if (statusFilter) statusFilter.addEventListener('change', filterTable);
        if (startDateFilter) {
            startDateFilter.setAttribute('max', today);
            startDateFilter.addEventListener('change', function() {
                if (this.value && endDateFilter) {
                    if (endDateFilter.value && endDateFilter.value < this.value) {
                        endDateFilter.value = this.value;
                    }
                    endDateFilter.setAttribute('min', this.value);
                } else if (endDateFilter) {
                    endDateFilter.removeAttribute('min');
                }
                filterTable();
            });
        }
        if (endDateFilter) {
            endDateFilter.setAttribute('max', today);
            endDateFilter.addEventListener('change', function() {
                if (this.value && startDateFilter) {
                    if (startDateFilter.value && startDateFilter.value > this.value) {
                        startDateFilter.value = this.value;
                    }
                    startDateFilter.setAttribute('max', this.value);
                } else if (startDateFilter) {
                    startDateFilter.setAttribute('max', today);
                }
                filterTable();
            });
        }
        
        filterTable();
    });

    $(document).ready(function() {
        var $globalTooltip = $('#service-icon-global-tooltip');
        if (!$globalTooltip.length) {
            $globalTooltip = $('<div id="service-icon-global-tooltip" aria-hidden="true"></div>').appendTo('body');
        }
        $(document).on('mouseenter', '#bookingsTable thead .th-tooltip', function() {
            var txt = $(this).attr('data-tooltip') || $(this).attr('title') || $(this).text();
            if (!txt) return;
            var rect = this.getBoundingClientRect();
            $globalTooltip.css({ display: 'block', left: (rect.left + rect.width / 2) + 'px', top: (rect.top - 6) + 'px', transform: 'translate(-50%, -100%)' }).text(txt);
        });
        $(document).on('mouseleave', '#bookingsTable thead .th-tooltip', function() { $globalTooltip.hide(); });
        $(document).on('mouseenter', '#bookingsTable .action-icon-badge', function() {
            var txt = $(this).attr('data-tooltip') || $(this).attr('title') || '';
            if (!txt) return;
            var rect = this.getBoundingClientRect();
            $globalTooltip.css({ display: 'block', left: (rect.left + rect.width / 2) + 'px', top: (rect.top - 6) + 'px', transform: 'translate(-50%, -100%)' }).text(txt);
        });
        $(document).on('mouseleave', '#bookingsTable .action-icon-badge', function() { $globalTooltip.hide(); });
    });

    function cancelBooking(element) {
        const bookingId = element.getAttribute('data-booking-id');
        Swal.fire({
            title: 'Cancel Booking',
            text: 'Are you sure you want to cancel this booking?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Cancel',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                // Handle cancel booking logic here
                console.log('Cancelling booking:', bookingId);
            }
        });
    }
</script>

<!-- View Booking Modals -->
@if(isset($bookings) && count($bookings) > 0)
    @foreach($bookings as $booking)
    @php
        // Check if booking_details is already an array or needs to be decoded
        $bookingDetails = is_array($booking->booking_details) ? $booking->booking_details : json_decode($booking->booking_details, true);
        
        // Calculate travel dates range
        $travelDates = '';
        if (!empty($bookingDetails['itinerary'])) {
            $firstDay = reset($bookingDetails['itinerary']);
            $lastDay = end($bookingDetails['itinerary']);
            
            if (isset($firstDay['date']) && isset($lastDay['date'])) {
                $travelDates = $firstDay['date'] . ' - ' . $lastDay['date'];
            }
        }
        
        // Calculate duration
        $duration = !empty($bookingDetails['itinerary']) ? count($bookingDetails['itinerary']) : 0;
        
        // Get pax info
        $adultCount = $bookingDetails['adult_count'] ?? 0;
        $childCount = $bookingDetails['child_count'] ?? 0;
        $maleCount = $bookingDetails['male_count'] ?? 0;
        $femaleCount = $bookingDetails['female_count'] ?? 0;
        $totalPax = $adultCount + $childCount;
        
        // Get price info
        $totalPrice = $bookingDetails['total_price'] ?? 0;
        $currency = $bookingDetails['currency'] ?? 'SGD';
    @endphp
    
    <div class="modal fade" id="viewBookingModal{{ $booking->id }}" tabindex="-1" aria-labelledby="viewBookingModalLabel{{ $booking->id }}" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="viewBookingModalLabel{{ $booking->id }}">
                        <i class="fas fa-info-circle me-2"></i>Booking Details - {{ $booking->booking_id }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Booking Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Booking ID</th>
                                            <td>{{ $booking->booking_id }}</td>
                                        </tr>
                                        <tr>
                                            <th>Travel Dates</th>
                                            <td>{{ $travelDates }}</td>
                                        </tr>
                                        <tr>
                                            <th>Duration</th>
                                            <td>{{ $duration }} days</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                @php
                                                    $statusClass = '';
                                                    switch($booking->status) {
                                                        case '1':
                                                            $statusClass = 'bg-success';
                                                            break;
                                                        case '2':
                                                            $statusClass = 'bg-primary';
                                                            break;
                                                        case '3':
                                                            $statusClass = 'bg-info';
                                                            break;
                                                        case '4':
                                                            $statusClass = 'bg-danger';
                                                            break;
                                                        case '5':
                                                            $statusClass = 'bg-warning text-dark';
                                                            break;
                                                        case '6':
                                                            $statusClass = 'bg-secondary';
                                                            break;
                                                        case '7':
                                                            $statusClass = 'bg-danger';
                                                            break;
                                                        case '8':
                                                            $statusClass = 'bg-success';
                                                            break;
                                                        default:
                                                            $statusClass = 'bg-success';
                                                    }
                                                @endphp
                                                <span class="badge {{ $statusClass }}">
                                                    @if($booking->status == '1')
                                                        Confirmed
                                                    @elseif($booking->status == '2')
                                                        Definite
                                                    @elseif($booking->status == '3')
                                                        Actual
                                                    @elseif($booking->status == '4')
                                                        Cancelled
                                                    @elseif($booking->status == '5')
                                                        Refund - Pending
                                                    @elseif($booking->status == '6')
                                                        Refunded
                                                    @elseif($booking->status == '7')
                                                        Cancel - Confirmed
                                                    @elseif($booking->status == '8')
                                                        Complete
                                                    @endif
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-users me-2"></i>Traveler Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Total Travelers</th>
                                            <td>{{ $totalPax }}</td>
                                        </tr>
                                        <tr>
                                            <th>Adults</th>
                                            <td>{{ $adultCount }}</td>
                                        </tr>
                                        <tr>
                                            <th>Children</th>
                                            <td>{{ $childCount }}</td>
                                        </tr>
                                        <tr>
                                            <th>Male</th>
                                            <td>{{ $maleCount }}</td>
                                        </tr>
                                        <tr>
                                            <th>Female</th>
                                            <td>{{ $femaleCount }}</td>
                                        </tr>
                                        <tr>
                                            <th>Total Price</th>
                                            @php
                                                $personsVB = $totalPax;
                                                $daysVB = $duration ?: 1;
                                                $taxesVB = is_array($booking->taxes) ? $booking->taxes : (is_string($booking->taxes) ? json_decode($booking->taxes, true) : []);
                                                $calcTaxes = function($baseAmount, $taxesArr, $persons, $days) {
                                                    $taxesArr = is_array($taxesArr) ? $taxesArr : [];
                                                    $computedById = [];
                                                    $breakdown = [];
                                                    $totalTax = 0.0;
                                                    foreach ($taxesArr as $tax) {
                                                        $taxId = $tax['tax_id'] ?? null;
                                                        $taxName = $tax['tax_name'] ?? 'Tax';
                                                        $taxType = strtolower($tax['tax_type'] ?? 'percentage');
                                                        $taxValue = (float) ($tax['tax_value'] ?? 0);
                                                        $calculateOn = $tax['calculate_on'] ?? 'total';
                                                        $ifFixed = $tax['if_fixed'] ?? null;
                                                        $baseForThis = $baseAmount;
                                                        if (is_numeric($calculateOn)) {
                                                            $refId = (int) $calculateOn;
                                                            $refAmount = $computedById[$refId] ?? 0;
                                                            $baseForThis = $baseAmount + $refAmount;
                                                        } elseif (strtolower($calculateOn) === 'total') {
                                                            $baseForThis = $baseAmount;
                                                        }
                                                        $amount = 0.0;
                                                        if ($taxType === 'percentage') {
                                                            $amount = ($baseForThis * $taxValue) / 100;
                                                        } else {
                                                            if ($ifFixed === 'person' || $ifFixed === 'per_person') {
                                                                $amount = $taxValue * max(0, (int) $persons);
                                                            } elseif ($ifFixed === 'per_tour_per_day') {
                                                                $amount = $taxValue * max(1, (int) $days);
                                                            } elseif ($ifFixed === 'per_person_per_day') {
                                                                $amount = $taxValue * max(0, (int) $persons) * max(1, (int) $days);
                                                            } else {
                                                                $amount = $taxValue;
                                                            }
                                                        }
                                                        $amount = ceil($amount);
                                                        $breakdown[$taxName] = ($breakdown[$taxName] ?? 0) + $amount;
                                                        if ($taxId !== null) {
                                                            $computedById[$taxId] = ($computedById[$taxId] ?? 0) + $amount;
                                                        }
                                                        $totalTax += $amount;
                                                    }
                                                    return ['breakdown' => $breakdown, 'total_tax' => $totalTax];
                                                };
                                                $taxResVB = $calcTaxes($totalPrice, $taxesVB, $personsVB, $daysVB);
                                                $taxAmtVB = is_array($taxResVB) ? ($taxResVB['total_tax'] ?? 0) : 0;
                                                $totalPriceInclVB = $totalPrice + $taxAmtVB;
                                            @endphp
                                            <td>{{ $currency }} {{ number_format($totalPriceInclVB, 2) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Itinerary</h6>
                        </div>
                        <div class="card-body">
                            @if(!empty($bookingDetails['itinerary']))
                                <div class="accordion" id="itineraryAccordion{{ $booking->id }}">
                                    @foreach($bookingDetails['itinerary'] as $index => $day)
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="heading{{ $booking->id }}_{{ $index }}">
                                                <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $booking->id }}_{{ $index }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $booking->id }}_{{ $index }}">
                                                    <strong>Day {{ $day['day'] }} - {{ $day['date'] }}</strong>
                                                </button>
                                            </h2>
                                            <div id="collapse{{ $booking->id }}_{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $booking->id }}_{{ $index }}" data-bs-parent="#itineraryAccordion{{ $booking->id }}">
                                                <div class="accordion-body">
                                                    @if(!empty($day['services']))
                                                        <div class="row">
                                                            @foreach($day['services'] as $service)
                                                                <div class="col-md-6 mb-3">
                                                                    <div class="service-item service-{{ $service['service_type'] }}">
                                                                        @php
                                                                            $icon = 'question-circle';
                                                                            $serviceTypeLabel = 'Service';
                                                                            
                                                                            switch($service['service_type']) {
                                                                                case 'hotel':
                                                                                    $icon = 'hotel';
                                                                                    $serviceTypeLabel = 'Hotel';
                                                                                    break;
                                                                                case 'attraction':
                                                                                    $icon = 'map-marked-alt';
                                                                                    $serviceTypeLabel = 'Attraction';
                                                                                    break;
                                                                                case 'guide':
                                                                                    $icon = 'user-tie';
                                                                                    $serviceTypeLabel = 'Guide';
                                                                                    break;
                                                                                case 'restaurant':
                                                                                    $icon = 'utensils';
                                                                                    $serviceTypeLabel = 'Restaurant';
                                                                                    break;
                                                                                case 'transport':
                                                                                    $icon = 'bus';
                                                                                    $serviceTypeLabel = 'Transport';
                                                                                    break;
                                                                            }
                                                                        @endphp
                                                                        
                                                                        <div class="d-flex align-items-center mb-2">
                                                                            <span class="badge bg-primary me-2">
                                                                                <i class="fas fa-{{ $icon }}"></i> {{ $serviceTypeLabel }}
                                                                            </span>
                                                                            <h6 class="mb-0">{{ $service['service_name'] }}</h6>
                                                                        </div>
                                                                        
                                                                        @if(!empty($service['details']))
                                                                            <div class="service-details mt-2">
                                                                                <div class="row">
                                                                                    @if(!empty($service['details']['image']))
                                                                                        <div class="col-md-4">
                                                                                            <img src="{{ $service['details']['image'] }}" alt="{{ $service['service_name'] }}" class="img-fluid rounded">
                                                                                        </div>
                                                                                    @endif
                                                                                    <div class="col">
                                                                                        @if(!empty($service['details']['city']))
                                                                                            <p class="mb-1"><strong>City:</strong> {{ $service['details']['city'] }}</p>
                                                                                        @endif
                                                                                        
                                                                                        @if($service['service_type'] === 'guide' && !empty($service['details']['language']))
                                                                                            <p class="mb-1"><strong>Languages:</strong> {{ $service['details']['language'] }}</p>
                                                                                        @endif
                                                                                        
                                                                                        @if($service['service_type'] === 'guide' && !empty($service['details']['experience']))
                                                                                            <p class="mb-1"><strong>Experience:</strong> {{ $service['details']['experience'] }} years</p>
                                                                                        @endif
                                                                                        
                                                                                        @if($service['service_type'] === 'guide' && !empty($service['details']['specialization']))
                                                                                            <p class="mb-1"><strong>Specialization:</strong> {{ $service['details']['specialization'] }}</p>
                                                                                        @endif
                                                                                        
                                                                                        @if($service['service_type'] === 'guide' && !empty($service['details']['rating']))
                                                                                            <p class="mb-1">
                                                                                                <strong>Rating:</strong> 
                                                                                                {{ $service['details']['rating'] }}
                                                                                                <span class="text-warning">
                                                                                                    @for($i = 1; $i <= 5; $i++)
                                                                                                        @if($i <= $service['details']['rating'])
                                                                                                            <i class="fas fa-star"></i>
                                                                                                        @elseif($i - 0.5 <= $service['details']['rating'])
                                                                                                            <i class="fas fa-star-half-alt"></i>
                                                                                                        @else
                                                                                                            <i class="far fa-star"></i>
                                                                                                        @endif
                                                                                                    @endfor
                                                                                                </span>
                                                                                                ({{ $service['details']['reviews'] ?? 0 }} reviews)
                                                                                            </p>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                        
                                                                        @if(!empty($service['entry_port']))
                                                                            <div class="alert alert-info mt-2 mb-0">
                                                                                <i class="fas fa-sign-in-alt me-2"></i> Entry Port: {{ $service['entry_port'] }}
                                                                            </div>
                                                                        @endif
                                                                        
                                                                        @if(!empty($service['exit_port']))
                                                                            <div class="alert alert-info mt-2 mb-0">
                                                                                <i class="fas fa-sign-out-alt me-2"></i> Exit Port: {{ $service['exit_port'] == 1 ? 'Pending' : 'Confirmed' }}
                                                                            </div>
                                                                        @endif
                                                                        
                                                                        @if(!empty($service['attraction_with_transfer']))
                                                                            <div class="alert alert-success mt-2 mb-0">
                                                                                <i class="fas fa-shuttle-van me-2"></i> Includes Transfer
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="alert alert-warning">No services scheduled for this day.</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning">No itinerary information available.</div>
                            @endif
                        </div>
                    </div>

                    @if(!empty($bookingDetails['entry_port_transfer']) || !empty($bookingDetails['exit_port_transfer']))
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-shuttle-van me-2"></i>Port Transfers</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @if(!empty($bookingDetails['entry_port_transfer']))
                                        <div class="col-md-6">
                                            <div class="card border-primary">
                                                <div class="card-header bg-primary text-white">
                                                    <h6 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Entry Port Transfer</h6>
                                                </div>
                                                <div class="card-body">
                                                    <pre class="bg-light p-3 rounded">{{ json_encode($bookingDetails['entry_port_transfer'], JSON_PRETTY_PRINT) == 1 ? 'Included' : 'Not Included' }}</pre>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if(!empty($bookingDetails['exit_port_transfer']))
                                        <div class="col-md-6">
                                            <div class="card border-danger">
                                                <div class="card-header bg-danger text-white">
                                                    <h6 class="mb-0"><i class="fas fa-sign-out-alt me-2"></i>Exit Port Transfer</h6>
                                                </div>
                                                <div class="card-body">
                                                    <pre class="bg-light p-3 rounded">{{ json_encode($bookingDetails['exit_port_transfer'], JSON_PRETTY_PRINT) == 1 ? 'Included' : 'Not Included' }}</pre>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Payment Modal -->
    @if(in_array(auth()->user()->role_id, [11, 33, 37, 38, 128, 131, 132, 134, 135, 137, 138]))
    <div class="modal fade" id="addPaymentModal{{ $booking->id }}" tabindex="-1" aria-labelledby="addPaymentModalLabel{{ $booking->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow-lg rounded">
                <div class="modal-header bg-primary text-white d-flex align-items-center justify-content-start" style="padding: 15px; border-radius: 8px;">
                    <h5 class="modal-title d-flex align-items-center" id="addPaymentModalLabel{{ $booking->id }}" style="margin: 0; font-weight: bold; color: white;">
                        <i class="fas fa-money-bill-wave me-2" style="color: #38ef7d; font-size: 1.4rem;"></i> 
                        <span style="color: white;">Payment Details for Package Booking #{{ $booking->booking_id }}</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="paymentForm{{ $booking->booking_id }}" action="{{ route('package.add-payment', $booking->booking_id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="booking_id" value="{{ $booking->booking_id }}">
                        
                        <!-- Payment Amount -->
                        <div class="mb-4">
                            @php
                                // Calculate due amount (including taxes)
                                $paidAmount = 0;
                                $packageTotal = 0;
                                
                                // Get package total and counts from booking_details
                                if ($booking->booking_details) {
                                    $bookingDetails = is_array($booking->booking_details) ? $booking->booking_details : json_decode($booking->booking_details, true);
                                    $packageTotal = $bookingDetails['total_price'] ?? 0;
                                    $adultCount = $bookingDetails['adult_count'] ?? 0;
                                    $childCount = $bookingDetails['child_count'] ?? 0;
                                } else {
                                    $bookingDetails = [];
                                    $adultCount = 0;
                                    $childCount = 0;
                                }
                                
                                // Calculate paid amount
                                if ($booking->payment_details) {
                                    $paymentDetails = is_array($booking->payment_details) ? $booking->payment_details : (is_string($booking->payment_details) ? json_decode($booking->payment_details, true) : []);
                                    if ($paymentDetails) {
                                        foreach ($paymentDetails as $payment) {
                                            if (isset($payment['status']) && $payment['status'] == 1) {
                                                $paidAmount += $payment['payment_amount'];
                                            }
                                        }
                                    }
                                }
                                
                                
                                // Compute tax using stored booking taxes
                                $persons = (int) $adultCount + (int) $childCount;
                                $days = (!empty($bookingDetails['itinerary']) && is_array($bookingDetails['itinerary'])) ? count($bookingDetails['itinerary']) : 1;
                                $taxes = is_array($booking->taxes) ? $booking->taxes : (is_string($booking->taxes) ? json_decode($booking->taxes, true) : []);
                                $calcTaxes = function($baseAmount, $taxesArr, $persons, $days) {
                                    $taxesArr = is_array($taxesArr) ? $taxesArr : [];
                                    $computedById = [];
                                    $breakdown = [];
                                    $totalTax = 0.0;
                                    foreach ($taxesArr as $tax) {
                                        $taxId = $tax['tax_id'] ?? null;
                                        $taxName = $tax['tax_name'] ?? 'Tax';
                                        $taxType = strtolower($tax['tax_type'] ?? 'percentage');
                                        $taxValue = (float) ($tax['tax_value'] ?? 0);
                                        $calculateOn = $tax['calculate_on'] ?? 'total';
                                        $ifFixed = $tax['if_fixed'] ?? null;
                                        $baseForThis = $baseAmount;
                                        if (is_numeric($calculateOn)) {
                                            $refId = (int) $calculateOn;
                                            $refAmount = $computedById[$refId] ?? 0;
                                            $baseForThis = $baseAmount + $refAmount;
                                        } elseif (strtolower($calculateOn) === 'total') {
                                            $baseForThis = $baseAmount;
                                        }
                                        $amount = 0.0;
                                        if ($taxType === 'percentage') {
                                            $amount = ($baseForThis * $taxValue) / 100;
                                        } else {
                                            if ($ifFixed === 'person' || $ifFixed === 'per_person') {
                                                $amount = $taxValue * max(0, (int) $persons);
                                            } elseif ($ifFixed === 'per_tour_per_day') {
                                                $amount = $taxValue * max(1, (int) $days);
                                            } elseif ($ifFixed === 'per_person_per_day') {
                                                $amount = $taxValue * max(0, (int) $persons) * max(1, (int) $days);
                                            } else {
                                                $amount = $taxValue;
                                            }
                                        }
                                        $amount = ceil($amount);
                                        $breakdown[$taxName] = ($breakdown[$taxName] ?? 0) + $amount;
                                        if ($taxId !== null) {
                                            $computedById[$taxId] = ($computedById[$taxId] ?? 0) + $amount;
                                        }
                                        $totalTax += $amount;
                                    }
                                    return ['breakdown' => $breakdown, 'total_tax' => $totalTax];
                                };
                                $taxResult = $calcTaxes($packageTotal, $taxes, $persons, $days);
                                $taxAmount = is_array($taxResult) ? ($taxResult['total_tax'] ?? 0) : 0;
                                $taxBreakdown = is_array($taxResult) ? ($taxResult['breakdown'] ?? []) : [];
                                $finalTotal = $packageTotal + $taxAmount;
                                
                                $dueAmount = $finalTotal - $paidAmount;
                            @endphp
                            
                            <label for="payment_amount{{ $booking->booking_id }}" class="form-label fw-bold d-flex align-items-center">
                                <i class="fas fa-money-bill-wave text-success me-2"></i>Payment Amount
                                <small class="text-muted ms-2">(Max: {{ $currency ?? 'SGD' }} {{ number_format($dueAmount, 2) }})</small>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currency ?? 'SGD' }}</span>
                                <input type="number" 
                                    class="form-control form-control-lg" 
                                    id="payment_amount{{ $booking->booking_id }}" 
                                    name="payment_amount" 
                                    step="0.01" 
                                    min="0.01" 
                                    max="{{ $dueAmount }}"
                                    value="{{ $dueAmount }}"
                                    data-max-amount="{{ $dueAmount }}"
                                    oninput="validateAmount(this, {{ $dueAmount }})"
                                    required>
                            </div>
                            <small class="text-info payment-info-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Total (Excl. Tax): {{ $currency ?? 'SGD' }} {{ number_format($packageTotal, 2) }} |
                                Tax: {{ $currency ?? 'SGD' }} {{ number_format($taxAmount, 2) }} |
                                Total (Incl. Tax): {{ $currency ?? 'SGD' }} {{ number_format($finalTotal, 2) }} |
                                Paid: {{ $currency ?? 'SGD' }} {{ number_format($paidAmount, 2) }} |
                                Due: {{ $currency ?? 'SGD' }} {{ number_format(max($dueAmount, 0), 2) }}
                            </small>
                            @if(!empty($taxBreakdown))
                                <div class="mt-2">
                                    <small class="text-muted d-block mb-1">Tax breakdown:</small>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($taxBreakdown as $taxName => $amount)
                                            <span class="badge bg-secondary">
                                                {{ $taxName }}: {{ $currency ?? 'SGD' }} {{ number_format($amount, 2) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            <div id="amountWarning{{ $booking->booking_id }}" class="text-warning mt-1" style="display: none;">
                                <small><i class="fas fa-exclamation-triangle me-1"></i>Amount adjusted to maximum allowed</small>
                            </div>
                        </div>
                        
                        <!-- Payment Date -->
                        <div class="mb-4">
                            <label for="payment_date{{ $booking->booking_id }}" class="form-label fw-bold">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>Payment Date
                            </label>
                            <input type="date" 
                                class="form-control form-control-lg" 
                                id="payment_date{{ $booking->booking_id }}" 
                                name="payment_date" 
                                value="{{ date('Y-m-d') }}"
                                required>
                        </div>

                        <!-- Payment Type -->
                        <div class="mb-4">
                            <label for="payment_type{{ $booking->booking_id }}" class="form-label fw-bold">
                                <i class="fas fa-credit-card text-primary me-2"></i>Payment Mode
                            </label>
                            <select class="form-select form-control-lg" 
                                id="payment_type{{ $booking->booking_id }}" 
                                name="payment_type" 
                                required>
                                <option value="">Select Payment Mode</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="cheque">Cheque</option>
                                <option value="online">Bank Transfer</option>
                            </select>
                        </div>
                        
                        <!-- Transaction ID -->
                        <div class="mb-4">
                            <label for="transaction_id{{ $booking->booking_id }}" class="form-label fw-bold">
                                <i class="fas fa-hashtag text-primary me-2"></i>Transaction ID
                            </label>
                            <input type="text" class="form-control form-control-lg" id="transaction_id{{ $booking->booking_id }}" name="transaction_id" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between" style="padding: 15px; border-radius: 8px;">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-success" id="savePaymentBtn{{ $booking->booking_id }}" data-booking-id="{{ $booking->booking_id }}">
                        <i class="fas fa-save me-2"></i>Save Payment Details
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endforeach
@endif

<!-- Cancel Booking Modals - Simplified -->
@if(isset($bookings) && count($bookings) > 0)
    @foreach($bookings as $booking)
        @if(in_array(auth()->user()->role_id, [33,34, 37, 38, 124,125, 128, 129, 130,132,133, 134, 135, 136, 137,138]) && in_array($booking->status, ['1', '2']))
        <form id="cancelBookingForm{{ $booking->booking_id }}" action="{{ route('package.cancel-booking', $booking->booking_id) }}" method="POST" style="display: none;">
            @csrf
            <input type="hidden" name="booking_id" value="{{ $booking->booking_id }}">
            <input type="hidden" name="cancel_reason" value="Cancelled by sales head">
        </form>
        @endif
        
        @if(in_array(auth()->user()->role_id, [33, 36, 128, 129, 130, 131, 133, 134, 135, 136, 137, 138]) && $booking->status == '5')
        <form id="processRefundForm{{ $booking->booking_id }}" action="{{ route('package.process-refund', $booking->booking_id) }}" method="POST" style="display: none;">
            @csrf
            <input type="hidden" name="booking_id" value="{{ $booking->booking_id }}">
            <input type="hidden" name="refund_reason" value="Refund processed">
        </form>
        @endif
    @endforeach
@endif

<!-- Payment History Modals -->
@if(isset($bookings))
    @foreach($bookings as $booking)
        @if($booking->payment_details)
            @php
                // Parse inputs
                $paymentDetails = is_array($booking->payment_details) ? $booking->payment_details : (is_string($booking->payment_details) ? json_decode($booking->payment_details, true) : []);
                $bookingDetails = is_array($booking->booking_details) ? $booking->booking_details : json_decode($booking->booking_details, true);
                $TotalPrice = $bookingDetails['total_price'] ?? 0;

                // Compute tax-inclusive total using stored taxes
                $persons = (int)($bookingDetails['adult_count'] ?? 0) + (int)($bookingDetails['child_count'] ?? 0);
                $days = (!empty($bookingDetails['itinerary']) && is_array($bookingDetails['itinerary'])) ? count($bookingDetails['itinerary']) : 1;
                $taxes = is_array($booking->taxes) ? $booking->taxes : (is_string($booking->taxes) ? json_decode($booking->taxes, true) : []);
                $calcTaxes = function($baseAmount, $taxesArr, $persons, $days) {
                    $taxesArr = is_array($taxesArr) ? $taxesArr : [];
                    $computedById = [];
                    $breakdown = [];
                    $totalTax = 0.0;
                    foreach ($taxesArr as $tax) {
                        $taxId = $tax['tax_id'] ?? null;
                        $taxName = $tax['tax_name'] ?? 'Tax';
                        $taxType = strtolower($tax['tax_type'] ?? 'percentage');
                        $taxValue = (float) ($tax['tax_value'] ?? 0);
                        $calculateOn = $tax['calculate_on'] ?? 'total';
                        $ifFixed = $tax['if_fixed'] ?? null;
                        $baseForThis = $baseAmount;
                        if (is_numeric($calculateOn)) {
                            $refId = (int) $calculateOn;
                            $refAmount = $computedById[$refId] ?? 0;
                            $baseForThis = $baseAmount + $refAmount;
                        } elseif (strtolower($calculateOn) === 'total') {
                            $baseForThis = $baseAmount;
                        }
                        $amount = 0.0;
                        if ($taxType === 'percentage') {
                            $amount = ($baseForThis * $taxValue) / 100;
                        } else {
                            if ($ifFixed === 'person' || $ifFixed === 'per_person') {
                                $amount = $taxValue * max(0, (int) $persons);
                            } elseif ($ifFixed === 'per_tour_per_day') {
                                $amount = $taxValue * max(1, (int) $days);
                            } elseif ($ifFixed === 'per_person_per_day') {
                                $amount = $taxValue * max(0, (int) $persons) * max(1, (int) $days);
                            } else {
                                $amount = $taxValue;
                            }
                        }
                        $amount = ceil($amount);
                        $breakdown[$taxName] = ($breakdown[$taxName] ?? 0) + $amount;
                        if ($taxId !== null) {
                            $computedById[$taxId] = ($computedById[$taxId] ?? 0) + $amount;
                        }
                        $totalTax += $amount;
                    }
                    return ['breakdown' => $breakdown, 'total_tax' => $totalTax];
                };
                $taxResult = $calcTaxes($TotalPrice, $taxes, $persons, $days);
                $taxAmount = is_array($taxResult) ? ($taxResult['total_tax'] ?? 0) : 0;
                $finalTotal = $TotalPrice + $taxAmount;

                // Sum paid and compute remaining
                $paidAmount = 0;
                if ($paymentDetails) {
                    foreach ($paymentDetails as $payment) {
                        if (isset($payment['status']) && $payment['status'] == 1) {
                            $paidAmount += $payment['payment_amount'];
                        }
                    }
                }
                $totalAmount = $finalTotal;
                $remainingAmount = $finalTotal - $paidAmount;
            @endphp
            
            <div class="modal fade" id="paymentHistoryModal{{ $booking->id }}" tabindex="-1" aria-labelledby="paymentHistoryModalLabel{{ $booking->id }}" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content shadow-lg rounded">
                        <div class="modal-header bg-primary text-white d-flex align-items-center justify-content-start" style="padding: 15px; border-radius: 8px;">
                            <h5 class="modal-title d-flex align-items-center" id="paymentHistoryModalLabel{{ $booking->id }}" style="margin: 0; font-weight: bold; color: white;">
                                <i class="fas fa-history me-2" style="color: #38ef7d; font-size: 1.4rem;"></i> 
                                <span style="color: white;">Payment History for Tour #{{ $booking->booking_id }}</span>
                            </h5>
                            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                        </div>
                        <div class="modal-body p-4">
                            @if($paymentDetails && count($paymentDetails) > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>PAYMENT DATE</th>
                                                <th>RECORD DATE</th>
                                                <th>PAID AMOUNT</th>
                                                <th>PAYMENT MODE</th>
                                                <th>STATUS</th>
                                                <th>ACTIONS</th>
                                                <th>TRANSACTION ID</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($paymentDetails as $index => $payment)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($payment['payment_date'])->format('M d, Y') }}</td>
                                                    <td>{{ isset($payment['created_at']) ? \Carbon\Carbon::parse($payment['created_at'])->format('M d, Y') : 'N/A' }}</td>
                                                    <td class="text-success fw-bold">${{ number_format($payment['payment_amount'], 2) }}</td>
                                                    <td>
                                                        <span class="badge bg-info">{{ $payment['payment_type'] }}</span>
                                                    </td>
                                                    <td>
                                                        @if(isset($payment['status']))
                                                            @if($payment['status'] == 1)
                                                                <span class="badge bg-success">✔ Verified</span>
                                                            @elseif($payment['status'] == 2)
                                                                <span class="badge bg-danger">✗ Declined</span>
                                                            @elseif($payment['status'] == 0)
                                                                <span class="badge bg-warning">⏳ Pending Approval</span>
                                                            @else
                                                                <span class="badge bg-secondary">Unknown</span>
                                                            @endif
                                                        @else
                                                            <span class="badge bg-warning">⏳ Pending Approval</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(!isset($payment['status']) || $payment['status'] == 0)
                                                            @if(in_array(auth()->user()->role_id, [36, 129, 131, 133, 134, 136, 137, 138, 126, 127]))
                                                                {{-- Finance roles can approve/decline payments --}}
                                                                <div class="btn-group" role="group">
                                                                    <button type="button" class="btn btn-sm btn-success" onclick="approvePayment('{{ $booking->booking_id }}', {{ $index }})" title="Approve Payment">
                                                                        <i class="fas fa-check"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm btn-danger" onclick="declinePayment('{{ $booking->booking_id }}', {{ $index }})" title="Decline Payment">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </div>
                                                            @else
                                                                {{-- Sales roles can only view pending payments --}}
                                                                <span class="badge bg-warning">⏳ Pending Verification</span>
                                                            @endif
                                                        @elseif($payment['status'] == 2 && isset($payment['decline_reason']))
                                                            <small class="text-muted" title="{{ $payment['decline_reason'] }}">
                                                                <i class="fas fa-info-circle"></i> Reason provided
                                                            </small>
                                                        @elseif($payment['status'] == 1)
                                                            <span class="text-success">
                                                                <i class="fas fa-check-circle"></i> Verified
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $payment['transaction_id'] }}</td>
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
                                                <h4 class="mb-0">${{ number_format($totalAmount, 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-success text-white">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Paid Amount</h6>
                                                <h4 class="mb-0">${{ number_format($paidAmount, 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-warning text-white">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Remaining Amount</h6>
                                                <h4 class="mb-0">${{ number_format($remainingAmount, 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Payment History</h5>
                                    <p class="text-muted">No payments have been recorded for this booking yet.</p>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endif

@endsection

@section('scripts')
<!-- DataTable JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<!-- DataTables Initialization Script -->
<script>
 $(document).ready(function() {
    // Initialize DataTable with export buttons
    $('.datatables-basic').DataTable({
        responsive: true,
        paging: true,
        lengthChange: true,
        info: true,
        searching: true,
        ordering: true,
        buttons: [
            'copy',
            'csv',
            'excel',
            'pdf',
            'print'
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search...",
        }
    });

    // Custom export button functionality
    $('#exportCopy').on('click', function() {
        $('.datatables-basic').DataTable().button('.buttons-copy').trigger();
    });

    $('#exportCSV').on('click', function() {
        $('.datatables-basic').DataTable().button('.buttons-csv').trigger();
    });

    $('#exportExcel').on('click', function() {
        $('.datatables-basic').DataTable().button('.buttons-excel').trigger();
    });

    $('#exportPDF').on('click', function() {
        $('.datatables-basic').DataTable().button('.buttons-pdf').trigger();
    });

    $('#exportPrint').on('click', function() {
        $('.datatables-basic').DataTable().button('.buttons-print').trigger();
    });
 });

// Payment Modal Functions - Removed duplicate function

// Payment form validation and submission functions
function validateAmount(input, maxAmount) {
    const currentValue = parseFloat(input.value);
    const max = parseFloat(maxAmount);
    
    // Get booking ID from input ID
    const bookingId = input.id.replace('payment_amount', '');
    const warningElement = document.getElementById(`amountWarning${bookingId}`);
    
    if (currentValue > max) {
        input.value = maxAmount;
        
        // Show inline warning message
        if (warningElement) {
            warningElement.style.display = 'block';
            // Hide warning after 3 seconds
            setTimeout(() => {
                warningElement.style.display = 'none';
            }, 3000);
        }
    } else {
        // Hide warning if amount is valid
        if (warningElement) {
            warningElement.style.display = 'none';
        }
    }
    
    // Add visual feedback for valid amounts
    if (currentValue > 0 && currentValue <= max) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    } else if (currentValue > max) {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
    } else {
        input.classList.remove('is-valid', 'is-invalid');
    }
}

function validatePaymentAmountInput(bookingId) {
    const paymentAmount = document.getElementById(`payment_amount${bookingId}`);
    
    if (!paymentAmount) {
        Swal.fire({
            title: 'Validation Error',
            text: 'Payment amount field not found',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return false;
    }
    
    const currentValue = parseFloat(paymentAmount.value);
    const maxAmount = parseFloat(paymentAmount.getAttribute('data-max-amount'));
    
    if (currentValue <= 0) {
        Swal.fire({
            title: 'Validation Error',
            text: 'Payment amount must be greater than zero',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return false;
    }
    
    if (currentValue > maxAmount) {
        Swal.fire({
            title: 'Validation Error',
            text: `Payment amount cannot exceed SGD ${maxAmount.toFixed(2)}`,
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return false;
    }
    
    return true;
}

function submitPaymentForm(bookingId) {
    console.log('Submitting payment form for booking:', bookingId);
    
    if (validatePaymentAmountInput(bookingId)) {
        const form = document.getElementById(`paymentForm${bookingId}`);
        if (form) {
            form.submit();
        } else {
            console.error('Payment form not found for booking ID:', bookingId);
            Swal.fire({
                title: 'Error',
                text: 'Payment form not found. Please refresh the page and try again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    }
}

// Handle save payment button click
$(document).on('click', '[id^="savePaymentBtn"]', function() {
    const bookingId = $(this).data('booking-id');
    console.log('Save payment button clicked for booking:', bookingId);
    
    if (bookingId) {
        submitPaymentForm(bookingId);
    } else {
        console.error('Booking ID not found on save payment button');
        Swal.fire({
            title: 'Error',
            text: 'Booking ID not found. Please refresh the page and try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
});

// Handle cancel booking button click
$(document).ready(function() {
    // Use event delegation to handle clicks on cancel booking buttons
    $(document).on('click', '[data-booking-id]', function(e) {
        e.preventDefault();
        const bookingId = $(this).data('booking-id');
        const buttonText = $(this).text().trim();
        
        // Check if it's a cancel or refund button
        if (buttonText.includes('Cancel')) {
            Swal.fire({
                title: 'Cancel Booking',
                text: 'Are you sure you want to cancel this booking?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, cancel it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Processing...',
                        html: 'Please wait while we cancel the booking.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit the cancel form
                    const form = document.getElementById(`cancelBookingForm${bookingId}`);
                    
                    if (form) {
                        form.submit();
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'Form not found. Please refresh the page and try again.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        } else if (buttonText.includes('Refund')) {
            Swal.fire({
                title: 'Refund',
                text: 'Are you sure you want to process the refund for this booking?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, process refund!',
                cancelButtonText: 'No, cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Processing...',
                        html: 'Please wait while we process the refund.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit the refund form
                    const form = document.getElementById(`processRefundForm${bookingId}`);
                    
                    if (form) {
                        form.submit();
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'Form not found. Please refresh the page and try again.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        }
    });
});

// Handle confirm payment button click using event delegation
$(document).ready(function() {
    // Use event delegation to handle clicks on confirm payment buttons
    // This ensures the event handler works for dynamically created elements (pagination)
    $(document).on('click', '.confirm-payment-btn', function() {
        const bookingId = $(this).data('booking-id');
        
        Swal.fire({
            title: 'Confirm Payment',
            text: 'Are you sure you want to confirm this payment?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, confirm it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Processing...',
                    html: 'Please wait while we confirm the payment.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Send AJAX request to confirm payment
                const confirmPaymentUrl = `{{ route('package.confirm-payment', ['booking_id' => '__BOOKING_ID__']) }}`.replace('__BOOKING_ID__', bookingId);
                $.ajax({
                    url: confirmPaymentUrl,
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Payment has been confirmed successfully.',
                            icon: 'success'
                        }).then(() => {
                            // Reload the page to show updated status
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'An error occurred while confirming the payment.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            title: 'Error!',
                            text: errorMessage,
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });
});

// Payment History Modal Functions
function approvePayment(bookingId, paymentIndex) {
    Swal.fire({
        title: 'Approve Payment',
        text: 'Are you sure you want to approve this payment?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, approve it!',
        customClass: {
            popup: 'swal-z-index'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `{{ url('/package-booking') }}/${bookingId}/approve-payment`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    payment_index: paymentIndex
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message,
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    Swal.fire({
                        title: 'Error!',
                        text: response?.message || 'Failed to approve payment',
                        icon: 'error'
                    });
                }
            });
        }
    });
}

function declinePayment(bookingId, paymentIndex) {
    // Create custom modal HTML
    const modalHtml = `
        <div class="modal fade" id="declineModal" tabindex="-1" aria-labelledby="declineModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="declineModalLabel">Decline Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="declineReason" class="form-label">Please provide a reason for declining this payment:</label>
                            <textarea class="form-control" id="declineReason" rows="4" maxlength="500" 
                                      placeholder="Enter decline reason..." required></textarea>
                            <div class="form-text">Minimum 10 characters required</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDecline">Decline Payment</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#declineModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('declineModal'));
    modal.show();
    
    // Focus on textarea when modal is shown
    $('#declineModal').on('shown.bs.modal', function () {
        $('#declineReason').focus();
    });
    
    // Handle confirm button click
    $('#confirmDecline').on('click', function() {
        const reason = $('#declineReason').val().trim();
        
        if (!reason || reason.length < 10) {
            alert('Please provide a reason (at least 10 characters)');
            $('#declineReason').focus();
            return;
        }
        
        // Disable button to prevent double submission
        $(this).prop('disabled', true).text('Processing...');
        
        $.ajax({
            url: `{{ url('/package-booking') }}/${bookingId}/decline-payment`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                payment_index: paymentIndex,
                decline_reason: reason
            },
            success: function(response) {
                modal.hide();
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message,
                        icon: 'error'
                    });
                }
            },
            error: function(xhr) {
                modal.hide();
                const response = xhr.responseJSON;
                Swal.fire({
                    title: 'Error!',
                    text: response?.message || 'Failed to decline payment',
                    icon: 'error'
                });
            },
            complete: function() {
                // Re-enable button
                $('#confirmDecline').prop('disabled', false).text('Decline Payment');
            }
        });
    });
    
    // Clean up modal when hidden
    $('#declineModal').on('hidden.bs.modal', function () {
        $(this).remove();
    });
}
</script>
@endsection
