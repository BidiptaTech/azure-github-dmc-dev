@extends('layouts.layout')
@section('title', 'Actual Bookings')
@extends('layouts.datatablecss')

<!-- Add SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<!-- Add SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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

    /* Compact table styles (aligned with confirmed) */
    #toursTable {
        font-size: 0.875rem;
        table-layout: fixed;
        width: 100% !important;
        margin-bottom: 0;
        background-color: #fff;
    }
    .dataTables_wrapper .dataTables_scroll .dataTables_scrollBody #toursTable,
    .dataTables_wrapper #toursTable {
        width: 100% !important;
        table-layout: fixed;
    }
    #toursTable thead th {
        padding: 0.5rem 0.5rem;
        font-size: 0.8125rem;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        background-color: #f8f9fa;
    }
    #toursTable tbody td {
        padding: 0.5rem 0.5rem;
        vertical-align: top;
        overflow: hidden;
        background-color: #fff;
    }

    #toursTable tbody tr {
        height: auto;
        min-height: 50px;
    }

    /* Compact badges in table */
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

    /* Compact buttons in table */
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

    /* Compact date / status text */
    #toursTable .d-flex.flex-column small {
        line-height: 1.3;
    }

    /* Page background - match confirmed (scoped to actual bookings) */
    .actual-bookings-page { background-color: #f8f9fa !important; min-height: 100vh; padding-bottom: 2rem !important; }
    .actual-bookings-page .card { background-color: #fff; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
    .actual-bookings-page .card-body { background-color: #fff; }

    /* Compact header + stats + filter bar (same as confirmed) */
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

    /* Tour Details column */
    #toursTable td:nth-child(2) {
        min-height: 72px;
        vertical-align: top;
    }
    /* Services column: professional soft-badge style (same as confirmed) */
    #toursTable thead th:nth-child(4),
    #toursTable td:nth-child(4) {
        min-width: 140px;
    }
    #toursTable td:nth-child(4) {
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
        overflow: visible !important;
    }
    #toursTable td:nth-child(4) .services-icons-wrap {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        row-gap: 0.35rem;
        column-gap: 0.35rem;
        align-items: stretch;
        max-width: 100%;
    }
    #toursTable td:nth-child(4) .service-icon-wrapper {
        min-width: 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    #toursTable .service-icon-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        padding: 0;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        cursor: pointer;
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        flex-shrink: 0;
    }
    #toursTable .service-icon-badge:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    }
    #toursTable .service-icon-badge i {
        font-size: 1.05rem;
        color: var(--service-color, #475569);
        flex-shrink: 0;
        line-height: 1;
    }
    #toursTable .service-icon-badge[data-clickable="false"] {
        cursor: default;
    }
    #toursTable .service-icon-badge[data-clickable="false"]:hover {
        background: #f8fafc;
        border-color: #e2e8f0;
        box-shadow: none;
    }
    #toursTable .service-icon-wrapper {
        position: relative;
        display: inline-flex;
        z-index: 1;
        margin: 3px;
    }
    #toursTable .service-icon-wrapper:hover {
        z-index: 10;
    }
    /* Hide inline tooltip - use global tooltip only (icons only, no service name text) */
    #toursTable .service-icon-tooltip {
        display: none !important;
    }
    /* Agent column */
    #toursTable td.col-agent .agent-name-line {
        font-weight: 600;
        font-size: 0.875rem;
        color: #0d6efd;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }
    #toursTable td.col-agent .agent-company-line {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 0.2rem;
    }
    #toursTable td.col-agent .agent-empty {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.8rem;
        color: #6c757d;
        font-style: italic;
    }
    /* Created column */
    #toursTable td.col-created {
        white-space: normal;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    #toursTable td.col-created .created-by-line,
    #toursTable td.col-created .created-at-line {
        display: flex;
        align-items: flex-start;
        gap: 0.35rem;
        line-height: 1.35;
    }
    /* Payment Details merged column - professional layout */
    #toursTable td.col-payment-details {
        min-width: 0;
        max-width: 140px;
        padding: 0.4rem 0.5rem;
        vertical-align: top;
    }
    #toursTable .payment-details-cell {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }
    #toursTable .payment-details-status {
        line-height: 1.2;
    }
    #toursTable .payment-details-info {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }
    #toursTable .payment-details-amount {
        font-size: 0.85rem;
    }
    #toursTable .payment-details-methods {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    #toursTable .payment-method-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        padding: 0.15rem 0.4rem;
        font-size: 0.65rem;
        font-weight: 500;
        background: #f1f5f9;
        color: #475569;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
    }
    #toursTable .payment-method-badge i {
        font-size: 0.7rem;
    }
    #toursTable .payment-details-view-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.5rem;
        font-size: 0.7rem;
        background: transparent;
        border: 1px solid #0ea5e9;
        color: #0ea5e9;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
        width: fit-content;
        margin-top: 0.15rem;
    }
    #toursTable .payment-details-view-btn:hover {
        background: #0ea5e9;
        color: #fff;
    }
    #toursTable .payment-details-empty {
        font-size: 0.75rem;
    }
    /* Payment status badges (used inside payment-details-cell) */
    #toursTable td.col-payment-details .payment-status-badge,
    #toursTable td.col-payment-status .payment-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.28rem 0.5rem;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 600;
        border: 1px solid transparent;
    }
    #toursTable td.col-payment-details .payment-status-badge.status-not-started,
    #toursTable td.col-payment-status .payment-status-badge.status-not-started {
        background: #fef3c7;
        color: #b45309;
    }
    #toursTable td.col-payment-details .payment-status-badge.status-pending,
    #toursTable td.col-payment-status .payment-status-badge.status-pending {
        background: #f1f5f9;
        color: #475569;
    }
    #toursTable td.col-payment-details .payment-status-badge.status-partial,
    #toursTable td.col-payment-status .payment-status-badge.status-partial {
        background: #e0f2fe;
        color: #0369a1;
    }
    #toursTable td.col-payment-details .payment-status-badge.status-paid,
    #toursTable td.col-payment-status .payment-status-badge.status-paid {
        background: #d1fae5;
        color: #047857;
    }
    /* Actions column - same as confirmed */
    #toursTable td.col-actions {
        min-height: 72px;
        min-width: 160px;
        white-space: nowrap;
        overflow: visible;
    }
    #toursTable .actions-icons-wrap {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        row-gap: 0.5rem;
        column-gap: 0.5rem;
        align-items: center;
        justify-items: center;
        max-width: 100%;
    }
    #toursTable .actions-icons-wrap > a,
    #toursTable .actions-icons-wrap > form {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    #toursTable .actions-icons-wrap form {
        margin: 0;
    }
    #toursTable .action-icon-badge {
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
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        flex-shrink: 0;
        text-decoration: none;
        color: inherit;
    }
    #toursTable .action-icon-badge:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        color: inherit;
    }
    #toursTable .action-icon-badge i {
        font-size: 1rem;
        color: var(--action-color, #475569);
    }
    #toursTable .action-icon-badge:hover i {
        color: var(--action-color, #475569);
    }
    #toursTable button.action-icon-badge {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    #toursTable button.action-icon-badge:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    /* Auto Cancel column - compact for reduced width */
    #toursTable td.col-auto-cancel {
        min-width: 70px;
        font-size: 0.7rem;
    }
    #toursTable td.col-auto-cancel .fw-semibold,
    #toursTable td.col-auto-cancel small,
    #toursTable td.col-auto-cancel .text-muted {
        font-size: 0.7rem !important;
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
</style>

@section('content')
<div class="container-xxl flex-grow-1 container-p-y actual-bookings-page">
    <!-- Compact Header + Stats Bar -->
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
    <div class="new-enq-header-bar p-3 mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <h4 class="fw-bold mb-0" style="font-size: 1.25rem;">
                    <span class="text-muted fw-light">Bookings /</span> Actual Bookings
                </h4>
                <span class="text-muted d-none d-md-inline" style="font-size: 0.875rem;">Manage actual bookings with payment details and execution status</span>
                <span class="badge bg-light text-success border border-success border-opacity-25 px-2 py-1" style="font-size: 0.75rem;">
                    <i class="ri-check-circle-line me-1"></i><span id="rangeCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</span> <span id="rangeLabel">{{ date('F') }}</span>
                </span>
            </div>
            <div class="row g-2 new-enq-stats-grid flex-grow-1">
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-success rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-bar-chart-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statActualCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</span><span class="stat-label text-muted" id="statActualLabel">{{ date('F') }} Actual</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-primary rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-money-dollar-circle-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statRevenueCount">${{ number_format($totalRevenue) }}</span><span class="stat-label text-muted" id="statRevenueLabel">{{ date('F') }} Revenue</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-warning rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-play-circle-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statActiveCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->where('check_in_time', '<', now())->where('check_out_time', '>', now())->count() }}</span><span class="stat-label text-muted" id="statActiveLabel">{{ date('F') }} Active</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-info rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-checkbox-circle-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statCompletedCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->where('check_out_time', '<', now())->count() }}</span><span class="stat-label text-muted" id="statCompletedLabel">{{ date('F') }} Completed</span></div>
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
                    <label class="form-label mb-0 small text-muted">Payment Type</label>
                    <select class="form-select form-select-sm" id="paymentFilter">
                        <option value="">All Payments</option>
                        <option value="cash">Cash Payments</option>
                        <option value="card">Card Payments</option>
                        <option value="bank">Bank Transfer</option>
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
                    <colgroup>
                        <col style="width: 2%">
                        <col style="width: 13%">
                        <col style="width: 8%">
                        <col style="width: 13%">
                        <col style="width: 11%">
                        <col style="width: 7%">
                        <col style="width: 14%">
                        <col style="width: 7%">
                        <col style="width: 7%">
                    </colgroup>
                    <thead class="table-light">
                        <tr>
                            <th class="th-tooltip" data-tooltip="#">#</th>
                            <th class="th-tooltip" data-tooltip="Tour Details">Tour Details</th>
                            <th class="th-tooltip" data-tooltip="Agent">Agent</th>
                            <th class="th-tooltip" data-tooltip="Manage Services">Services</th>
                            <th class="th-tooltip" data-tooltip="Payment Details & Status">Payment Details</th>
                            <th class="th-tooltip" data-tooltip="Status">Status</th>
                            <th class="th-tooltip" data-tooltip="Actions">Actions</th>
                            <th class="th-tooltip" data-tooltip="Created">Created</th>
                            <th class="th-tooltip" data-tooltip="Auto Cancel Date">Auto Cancel Date</th>
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
                            // Includes: base price + transfer price + guide price (for attractions)
                            $tourTotalPrice = 0;
                            $orders = \App\Models\Order::where('tour_id', $tour->tour_id)->whereNull('deleted_at')->get();
                            foreach($orders as $order) {
                                if($order->data) {
                                    $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
                                    if(is_array($orderData)) {
                                        // Handle both single item and array of items
                                        $items = isset($orderData[0]) && is_array($orderData[0]) ? $orderData : [$orderData];
                                        foreach($items as $item) {
                                            if(!is_array($item)) continue;
                                            
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
                            
                            $enquiry = \App\Models\Enquiry::where('tour_id', $tour->tour_id)->where('status', 2)->first();
                            $enquiry_amount = $enquiry ? ($enquiry->amount ?? 0) : 0;
                            $frstenquiry = \App\Models\Enquiry::where('tour_id', $tour->tour_id)->first();
                            $first_enquiry_amount = $frstenquiry->actual_amount ?? 0;
                            $discountAmount = $frstenquiry ? ($frstenquiry->actual_amount - $enquiry_amount) : 0;
                            
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
                            <td class="align-top">
                                <div class="d-flex flex-column gap-1">
                                    <strong class="text-primary">{{ $tour->display_id }}</strong>
                                    <small class="text-muted">Tour ID: #{{ $tour->tour_id }}</small>
                                    @if($tour->multi_enq_id)
                                        <small class="text-info">Multi: {{ $tour->multi_enq_id }}</small>
                                    @endif
                                    @if($tour->tour_type)
                                        <span class="text-white d-inline-block px-2 py-0 rounded" style="background: #3b82f6; font-weight: 500; font-size: 0.75rem;">{{ $tour->tour_type }}</span>
                                    @endif
                                    <span class="fw-medium mt-1"><i class="ri-map-pin-line me-1"></i>{{ $tour->destination ?? 'N/A' }}</span>
                                    <div class="d-flex align-items-center gap-2 flex-nowrap">
                                        <span title="Adults"><i class="ri-user-line text-success"></i> {{ $tour->adult ?? 0 }}</span>
                                        <span title="Children"><i class="ri-user-smile-line text-warning"></i> {{ $tour->child ?? 0 }}</span>
                                        <span title="Infants"><i class="ri-user-heart-line text-info"></i> {{ $tour->infant ?? 0 }}</span>
                                    </div>
                                    @if($tour->check_in_time || $tour->check_out_time)
                                        <small>
                                            @if($tour->check_in_time)<span><strong>In:</strong> {{ \Carbon\Carbon::parse($tour->check_in_time)->format('M d, Y') }}</span>@endif
                                            @if($tour->check_out_time)<span class="ms-1"><strong>Out:</strong> {{ \Carbon\Carbon::parse($tour->check_out_time)->format('M d, Y') }}</span>@endif
                                        </small>
                                    @else
                                        <small class="text-muted">Check-in/out: Not specified</small>
                                    @endif
                                </div>
                            </td>
                            <td class="col-agent">
                                <div class="d-flex flex-column">
                                    @if($tour->agent_name)
                                        <span class="agent-name-line">
                                            <i class="ri-user-line"></i>
                                            <span>{{ $tour->agent_name }}</span>
                                        </span>
                                        <span class="agent-company-line">
                                            <i class="ri-building-line"></i>
                                            <span>{{ $tour->agent_company_name ?? 'N/A' }}</span>
                                        </span>
                                    @else
                                        <span class="agent-empty">
                                            <i class="ri-user-unfollow-line"></i>
                                            <span>No agent assigned</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="align-top">
                                @php
                                    $orders = \App\Models\Order::where('tour_id', $tour->tour_id)->where('bookingType', 'booking')->whereNull('deleted_at')->get();
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
                                        'hotel' => 'ri-hotel-bed-line',
                                        'attraction' => 'ri-camera-line',
                                        'restaurant' => 'ri-restaurant-2-line',
                                        'guide' => 'ri-user-voice-line',
                                        'entry_port' => 'ri-flight-land-line',
                                        'exit_port' => 'ri-flight-takeoff-line',
                                        'travel_hourly' => 'ri-time-line',
                                        'travel_point' => 'ri-route-line',
                                        'local_transport' => 'ri-car-line',
                                    ];
                                    $serviceLabels = [
                                        'hotel' => 'Hotel',
                                        'attraction' => 'Attraction',
                                        'restaurant' => 'Restaurant',
                                        'guide' => 'Guide',
                                        'entry_port' => 'Arrival',
                                        'exit_port' => 'Departure',
                                        'travel_hourly' => 'Local-Tour Hourly',
                                        'travel_point' => 'Local-Tour Point to Point',
                                        'local_transport' => 'Local Transport',
                                    ];
                                    $serviceColors = [
                                        'hotel' => '#4338ca',
                                        'attraction' => '#0f766e',
                                        'restaurant' => '#c2410c',
                                        'guide' => '#475569',
                                        'entry_port' => '#047857',
                                        'exit_port' => '#0369a1',
                                        'travel_hourly' => '#b45309',
                                        'travel_point' => '#5b21b6',
                                        'local_transport' => '#334155',
                                    ];
                                    $debugInfo = [
                                        'tour_id' => $tour->tour_id,
                                        'orders_count' => $orders->count(),
                                        'svc' => $svc,
                                        'serviceData_keys' => array_keys($serviceData)
                                    ];
                                @endphp
                                <div class="services-icons-wrap">
                                @foreach($svc as $svcKey => $count)
                                    @if(intval($count) > 0)
                                        @php
                                            $label = $serviceLabels[$svcKey] ?? ucfirst($svcKey);
                                            $tooltipText = $label . ': ' . $count;
                                            $bgColor = $serviceColors[$svcKey] ?? '#6c757d';
                                            $clickable = in_array($svcKey, ['hotel', 'attraction', 'restaurant', 'guide', 'entry_port', 'exit_port', 'travel_hourly', 'travel_point', 'local_transport']);
                                        @endphp
                                        @if($clickable)
                                            <span class="service-icon-wrapper" data-tooltip="{{ $tooltipText }}">
                                                <span class="service-icon-badge"
                                                      style="--service-color: {{ $bgColor }};"
                                                      data-clickable="true"
                                                      onclick="openServiceModal('{{ $svcKey }}', {{ $tour->tour_id }}, event)"
                                                      data-debug-info="{{ json_encode($debugInfo) }}"
                                                      role="button"
                                                      tabindex="0">
                                                    <i class="{{ $icons[$svcKey] }}"></i>
                                                </span>
                                                <span class="service-icon-tooltip">{{ $tooltipText }}</span>
                                            </span>
                                        @else
                                            <span class="service-icon-wrapper" data-tooltip="{{ $tooltipText }}">
                                                <span class="service-icon-badge"
                                                      style="--service-color: {{ $bgColor }};"
                                                      data-clickable="false"
                                                      role="img">
                                                    <i class="{{ $icons[$svcKey] }}"></i>
                                                </span>
                                                <span class="service-icon-tooltip">{{ $tooltipText }}</span>
                                            </span>
                                        @endif
                                    @endif
                                @endforeach
                                @if(array_sum(array_map('intval', $svc)) === 0)
                                    <span class="text-muted small">No services</span>
                                @endif
                                </div>
                            </td>
                            <td class="align-top col-payment-details">
                                <div class="payment-details-cell">
                                    {{-- Status (primary info) --}}
                                    <div class="payment-details-status">
                                        @if(empty($paymentData))
                                            <span class="payment-status-badge status-not-started" title="Payment not started"><i class="ri-alert-line"></i> Not Started</span>
                                        @elseif($hasPendingPayments && $totalPaid == 0)
                                            <span class="payment-status-badge status-pending" title="Pending approval"><i class="ri-time-line"></i> Pending</span>
                                        @elseif($remainingAmount > 0)
                                            <span class="payment-status-badge status-partial" title="Partial: {{ number_format($totalPaid, 2) }} paid{{ $hasPendingPayments ? ' + pending' : '' }}"><i class="ri-bank-card-line"></i> Partial{{ $hasPendingPayments ? '+' : '' }} ({{ number_format($totalPaid, 0) }})</span>
                                        @else
                                            <span class="payment-status-badge status-paid" title="Fully paid: {{ number_format($totalPaid, 2) }}"><i class="ri-checkbox-circle-fill"></i> Paid ({{ number_format($totalPaid, 0) }})</span>
                                        @endif
                                    </div>
                                    {{-- Amount & methods --}}
                                    <div class="payment-details-info">
                                        @if($totalAmount > 0)
                                            <div class="payment-details-amount"><strong class="text-success">${{ number_format($totalAmount) }}</strong></div>
                                            @if(!empty($paymentMethods))
                                                <div class="payment-details-methods">
                                                    @foreach($paymentMethods as $method)
                                                        <span class="payment-method-badge">
                                                            @if($method == 'cash')<i class="ri-money-dollar-line"></i>
                                                            @elseif($method == 'card')<i class="ri-bank-card-line"></i>
                                                            @elseif($method == 'bank')<i class="ri-bank-line"></i>
                                                            @else<i class="ri-wallet-line"></i>
                                                            @endif
                                                            {{ ucfirst($method) }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @if(!empty($tour->parsed_payment_details))
                                                <button class="payment-details-view-btn" data-bs-toggle="modal" data-bs-target="#showPaymentModal{{ $tour->tour_id }}" title="View payment details">
                                                    <i class="ri-eye-line"></i> View
                                                </button>
                                            @endif
                                        @else
                                            <span class="text-muted payment-details-empty">—</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($isActive)
                                    <span class="badge bg-warning"><i class="ri-play-circle-line me-1"></i>Active</span>
                                @elseif($isCompleted)
                                    <span class="badge bg-success"><i class="ri-checkbox-circle-line me-1"></i>Completed</span>
                                @else
                                    <span class="badge bg-primary"><i class="ri-calendar-line me-1"></i>Upcoming</span>
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
                            <td class="align-top col-actions">
                                <div class="actions-icons-wrap">
                                    <a href="{{ route('bookings.view-tour', Crypt::encrypt($tour->tour_id)) }}"
                                       class="action-icon-badge" style="--action-color: #0369a1;" data-tooltip="Audit Trail">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    <a href="{{ route('tour.itinerary.pdf', ['tourId' => $tour->tour_id]) }}"
                                       class="action-icon-badge" style="--action-color: #0f766e;" data-tooltip="Download Quotation" target="_blank">
                                        <i class="ri-file-download-line"></i>
                                    </a>
                                    @php
                                        $all_ids = [11, 33, 34, 37, 38, 124, 125, 128, 129, 130, 132, 133, 134, 135, 136, 137, 138];
                                        $finalInvoice = \App\Models\Invoice::where('tour_id', $tour->tour_id)
                                            ->where('invoice_type', 'final')
                                            ->whereNull('deleted_at')
                                            ->first();
                                        $proformaInvoice = \App\Models\Invoice::where('tour_id', $tour->tour_id)
                                            ->where('invoice_type', 'proforma')
                                            ->whereNull('deleted_at')
                                            ->first();
                                    @endphp
                                    
                                    @if($finalInvoice)
                                        <a href="{{ route('invoices.download', Crypt::encrypt($finalInvoice->invoice_id)) }}"
                                           class="action-icon-badge" style="--action-color: #0e7490;" data-tooltip="Final Invoice (Price Breakup)" target="_blank">
                                            <i class="ri-file-paper-2-line"></i>
                                        </a>
                                        <a href="{{ route('invoices.download-price-only', Crypt::encrypt($finalInvoice->invoice_id)) }}"
                                           class="action-icon-badge" style="--action-color: #7c3aed;" data-tooltip="Final Invoice (Price Only)" target="_blank">
                                            <i class="ri-file-download-line"></i>
                                        </a>
                                    @elseif($proformaInvoice)
                                        <a href="{{ route('invoices.download', Crypt::encrypt($proformaInvoice->invoice_id)) }}"
                                           class="action-icon-badge" style="--action-color: #0e7490;" data-tooltip="Proforma Invoice (Price Breakup)" target="_blank">
                                            <i class="ri-file-paper-line"></i>
                                        </a>
                                        <a href="{{ route('invoices.download-price-only', Crypt::encrypt($proformaInvoice->invoice_id)) }}"
                                           class="action-icon-badge" style="--action-color: #7c3aed;" data-tooltip="Proforma Invoice (Price Only)" target="_blank">
                                            <i class="ri-file-download-line"></i>
                                        </a>
                                        <form action="{{ route('invoices.convert-to-final', $proformaInvoice->invoice_id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="action-icon-badge" style="--action-color: #b45309;" data-tooltip="Convert to Final"
                                                    onclick="return confirm('Are you sure you want to convert this proforma invoice to final invoice? This action cannot be undone.');">
                                                <i class="ri-file-edit-line"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('invoices.generate-final', $tour->tour_id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="action-icon-badge" style="--action-color: #0e7490;" data-tooltip="Generate Final Invoice">
                                                <i class="ri-file-add-line"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if(in_array(auth()->user()->role_id, $all_ids))
                                        <a href="{{ route('tour.itinerary', ['tourId' => Crypt::encrypt($tour->tour_id)]) }}"
                                           class="action-icon-badge" style="--action-color: #047857;" data-tooltip="View Itinerary"
                                           onclick="event.stopPropagation(); window.open(this.href, '_blank'); return false;">
                                            <i class="ri-calendar-line"></i>
                                        </a>
                                    @endif
                                    @if(auth()->user()->role_id == 33 || auth()->user()->role_id == 11 || auth()->user()->role_id == 34 || auth()->user()->role_id == 37 || auth()->user()->role_id == 38 || auth()->user()->role_id == 124 || auth()->user()->role_id == 125 || in_array(auth()->user()->role_id, [128, 129, 130, 131, 132, 134, 135, 136, 137, 138]))
                                        <a href="{{ route('tour.editpackage', Crypt::encrypt($tour->tour_id)) }}"
                                           class="action-icon-badge" style="--action-color: #b45309;" data-tooltip="Add/Remove Services">
                                            <i class="ri-settings-3-line"></i>
                                        </a>
                                        <a href="{{ route('guests.index', ['tour_id' => Crypt::encrypt($tour->tour_id)]) }}"
                                           class="action-icon-badge" style="--action-color: #0e7490;" data-tooltip="Add Guests">
                                            <i class="ri-user-add-line"></i>
                                        </a>
                                    @endif
                                    @if(auth()->user()->role_id == 36 || auth()->user()->role_id == 126 || auth()->user()->role_id == 127 || auth()->user()->role_id == 124 || auth()->user()->role_id == 125)
                                        <button type="button" class="action-icon-badge" style="--action-color: #0369a1;" data-tooltip="Payment Details" data-bs-toggle="modal" data-bs-target="#showPaymentModal{{ $tour->tour_id }}">
                                            <i class="ri-history-line"></i>
                                        </button>
                                        @if(hasPermission('add payment'))
                                            @if($remainingAmount > 0 && !$hasPendingPayments)
                                                <button type="button" class="action-icon-badge" style="--action-color: #047857;" data-tooltip="Add Payment" data-bs-toggle="modal" data-bs-target="#addPaymentModal{{ $tour->tour_id }}" onclick="checkPendingPayments({{ $tour->tour_id }})">
                                                    <i class="ri-add-circle-line"></i>
                                                </button>
                                            @endif
                                        @endif
                                    @else
                                        @if(!empty($paymentData))
                                            <button type="button" class="action-icon-badge" style="--action-color: #0369a1;" data-tooltip="Payment Details" data-bs-toggle="modal" data-bs-target="#showPaymentModal{{ $tour->tour_id }}">
                                                <i class="ri-history-line"></i>
                                            </button>
                                        @endif
                                        @if(hasPermission('add payment'))
                                            @if($remainingAmount > 0 && !$hasPendingPayments)
                                                <button type="button" class="action-icon-badge" style="--action-color: #047857;" data-tooltip="Add Payment" data-bs-toggle="modal" data-bs-target="#addPaymentModal{{ $tour->tour_id }}" onclick="checkPendingPayments({{ $tour->tour_id }})">
                                                    <i class="ri-add-circle-line"></i>
                                                </button>
                                            @endif
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td class="col-created align-top">
                                <div class="d-flex flex-column">
                                    <span class="created-by-line fw-medium" title="Created by">
                                        <i class="ri-user-line"></i>
                                        <span>{{ $tour->created_by_name ?? 'N/A' }}</span>
                                    </span>
                                    <span class="created-at-line" title="Created at">
                                        <i class="ri-calendar-line"></i>
                                        <span>
                                            {{ $tour->created_at->timezone(auth()->user()->timezone ?? 'UTC')->format('D, M d, Y') }}
                                            ·
                                            {{ $tour->created_at->timezone(auth()->user()->timezone ?? 'UTC')->format('h:i A') }}
                                        </span>
                                    </span>
                                </div>
                            </td>
                            <td class="col-auto-cancel">
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
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                <!-- Compact Header -->
                <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="text-white">
                            <h5 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                <i class="ri-hotel-line me-1" style="font-size: 0.9rem;"></i>Hotel Bookings
                            </h5>
                        </div>
                        <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('hotel', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                    </div>
                </div>
                
                <div class="modal-body p-3" style="background-color: #f8f9fa;">
                    @if(isset($serviceData['hotel']) && count($serviceData['hotel']) > 0)
                        @foreach($serviceData['hotel'] as $index => $hotelOrder)
                        @php
                            $hotelData = is_string($hotelOrder->data) ? json_decode($hotelOrder->data, true) : $hotelOrder->data;
                        @endphp
                        
                        @if(is_array($hotelData))
                            @foreach($hotelData as $booking)
                                @php
                                    // Calculate check-in and check-out dates
                                    $checkInDate = null;
                                    $checkOutDate = null;
                                    $nights = null;
                                    $checkInTime = null;
                                    
                                    if(isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 0) {
                                        $checkInDate = \Carbon\Carbon::parse($booking['bookingDate'][0])->format('D, M d, Y');
                                        if(count($booking['bookingDate']) > 1) {
                                            $checkOutDate = \Carbon\Carbon::parse(end($booking['bookingDate']))->format('D, M d, Y');
                                            $checkIn = \Carbon\Carbon::parse($booking['bookingDate'][0]);
                                            $checkOut = \Carbon\Carbon::parse(end($booking['bookingDate']));
                                            $nights = $checkIn->diffInDays($checkOut);
                                        }
                                    }
                                    
                                    if(isset($booking['hotelDetails']['checkInTime'])) {
                                        $checkInTime = $booking['hotelDetails']['checkInTime'];
                                    }
                                    
                                    // Get room information
                                    $roomCount = isset($booking['rooms']) && is_array($booking['rooms']) ? count($booking['rooms']) : 1;
                                    $roomType = null;
                                    $bedType = null;
                                    $mealPlan = 'Room Only';
                                    
                                    if(isset($booking['rooms']) && is_array($booking['rooms']) && count($booking['rooms']) > 0) {
                                        $firstRoom = $booking['rooms'][0];
                                        $roomType = $firstRoom['room_type'] ?? 'Standard';
                                        
                                        if(isset($firstRoom['beds']) && is_array($firstRoom['beds']) && count($firstRoom['beds']) > 0) {
                                            $firstBed = $firstRoom['beds'][0];
                                            $bedType = $firstBed['bed_type'] ?? 'N/A';
                                            
                                            if(isset($firstBed['selectedMeals']) && is_array($firstBed['selectedMeals']) && count($firstBed['selectedMeals']) > 0) {
                                                $mealPlan = $firstBed['selectedMeals'][0]['type'] ?? 'Room Only';
                                            }
                                        }
                                    }
                                    
                                    // Transfer options
                                    $transferOptions = $booking['transfer_options'] ?? null;
                                    $hasTransfer = $transferOptions && (($transferOptions['transfer_required'] ?? false) === true || ($transferOptions['transfer_required'] ?? false) === 'true' || ($transferOptions['transfer_required'] ?? false) === 'Yes');
                                @endphp
                                <div class="card mb-3 shadow-sm border-0" style="border-radius: 10px; overflow: hidden; border-left: 4px solid #667eea !important;">
                                    <!-- Compact Card Header -->
                                    <div class="card-header border-0 py-2 px-3" style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);">
                                        <div class="row align-items-center g-2">
                                            <div class="col-md-8">
                                                <div class="d-flex align-items-center">
                                                    @if(isset($booking['hotelDetails']['image']) && !empty($booking['hotelDetails']['image']))
                                                        <img src="{{ $booking['hotelDetails']['image'] }}" 
                                                             alt="{{ $booking['hotelDetails']['hotel_name'] ?? 'Hotel' }}" 
                                                             class="rounded-circle me-2"
                                                             style="width: 40px; height: 40px; object-fit: cover; border: 2px solid rgba(255,255,255,0.3);">
                                                    @else
                                                        <div class="rounded-circle me-2 bg-white bg-opacity-20 d-flex align-items-center justify-content-center"
                                                             style="width: 40px; height: 40px; border: 2px solid rgba(255,255,255,0.3);">
                                                            <i class="ri-hotel-line text-white" style="font-size: 1.2rem;"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-white">
                                                            <i class="ri-hotel-line me-1"></i>{{ $booking['hotelDetails']['hotel_name'] ?? 'Hotel Accommodation' }}
                                                        </h6>
                                                        <small class="text-white opacity-90">{{ $booking['hotelDetails']['location'] ?? 'Location' }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <span class="badge bg-white text-success px-3 py-2" style="font-size: 0.95rem;">
                                                    SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body p-3" style="background-color: #ffffff;">

                                        <!-- Booking Schedule & Room Information -->
                                        <div class="row mb-3 g-3">
                                            <div class="col-md-6">
                                                <div class="bg-light rounded p-2 h-100">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="ri-calendar-line text-white" style="font-size: 0.9rem;"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Booking Schedule</h6>
                                                    </div>
                                                    <div class="row g-2">
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.7rem;">Check-in</small>
                                                            <div class="fw-bold text-success" style="font-size: 0.85rem;">{{ $checkInDate ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.7rem;">Check-out</small>
                                                            <div class="fw-bold text-danger" style="font-size: 0.85rem;">{{ $checkOutDate ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.7rem;">Nights</small>
                                                            <div class="fw-medium" style="font-size: 0.85rem;">{{ $nights ? $nights . ' Night' . ($nights > 1 ? 's' : '') : 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.7rem;">Check-in Time</small>
                                                            <div class="fw-medium" style="font-size: 0.85rem;">{{ $checkInTime ?? 'N/A' }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-light rounded p-2 h-100">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="ri-home-line text-white" style="font-size: 0.9rem;"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Room Information</h6>
                                                    </div>
                                                    <div class="row g-2">
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.7rem;">Rooms</small>
                                                            <div class="fw-medium" style="font-size: 0.85rem;">{{ $roomCount }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.7rem;">Room Type</small>
                                                            <div class="fw-medium" style="font-size: 0.85rem;">{{ $roomType ?? 'Standard' }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.7rem;">Bed Type</small>
                                                            <div class="fw-medium" style="font-size: 0.85rem;">{{ $bedType ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.7rem;">Meal Plan</small>
                                                            <div><span class="badge bg-primary px-2 py-1" style="font-size: 0.7rem;">{{ $mealPlan }}</span></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Transfer Options -->
                                        @if($hasTransfer)
                                        <div class="bg-light rounded p-2 mb-3">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ri-car-line text-white" style="font-size: 0.9rem;"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Transfer Details</h6>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <div class="bg-white rounded p-2">
                                                        <small class="text-muted d-block" style="font-size: 0.7rem;">Transfer Type</small>
                                                        <div class="fw-medium" style="font-size: 0.8rem;">
                                                            <span class="badge bg-primary" style="font-size: 0.7rem;">{{ $transferOptions['type'] ?? 'N/A' }}</span>
                                                        </div>
                                                        @if(isset($transferOptions['destination_name']))
                                                        <div class="mt-1">
                                                            <small class="text-muted d-block" style="font-size: 0.7rem;">Destination</small>
                                                            <div class="fw-medium text-primary" style="font-size: 0.8rem;">{{ $transferOptions['destination_name'] }}</div>
                                                        </div>
                                                        @endif
                                                        @if(isset($transferOptions['pickup_location_name']))
                                                        <div class="mt-1">
                                                            <small class="text-muted d-block" style="font-size: 0.7rem;">Pickup</small>
                                                            <div class="fw-medium text-info" style="font-size: 0.8rem;">{{ $transferOptions['pickup_location_name'] }}</div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="bg-white rounded p-2">
                                                        @if(isset($transferOptions['vehicle_details']))
                                                            <small class="text-muted d-block" style="font-size: 0.7rem;">Vehicle</small>
                                                            <div class="fw-medium" style="font-size: 0.8rem;">{{ $transferOptions['vehicle_details']['vehicle_name'] ?? 'N/A' }}</div>
                                                            @if(isset($transferOptions['vehicle_details']['seating_capacity']))
                                                            <small class="text-muted" style="font-size: 0.65rem;">Capacity: {{ $transferOptions['vehicle_details']['seating_capacity'] }}</small>
                                                            @endif
                                                        @elseif(isset($transferOptions['vehicle_id']))
                                                            <small class="text-muted d-block" style="font-size: 0.7rem;">Vehicle ID</small>
                                                            <div class="fw-medium" style="font-size: 0.8rem;">{{ $transferOptions['vehicle_id'] }}</div>
                                                        @endif
                                                        @if(isset($transferOptions['cost']) && $transferOptions['cost'] > 0)
                                                        <div class="mt-1">
                                                            <small class="text-muted d-block" style="font-size: 0.7rem;">Cost</small>
                                                            <div class="fw-bold text-success" style="font-size: 0.9rem;">SGD {{ number_format($transferOptions['cost'], 2) }}</div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Pricing Overview -->
                                        <div class="bg-light rounded p-2 mb-3">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ri-money-dollar-circle-line text-white" style="font-size: 0.9rem;"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Pricing Overview</h6>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-md-12">
                                                    <div class="text-center p-2 border rounded bg-white" style="border-color: #28a745 !important;">
                                                        <small class="text-muted d-block" style="font-size: 0.7rem;">Hotel Price</small>
                                                        <div class="fw-bold text-success" style="font-size: 0.8rem;">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</div>
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
                        <div class="text-center py-4">
                            <div class="bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="ri-hotel-line text-muted" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="text-dark mb-2" style="font-size: 1.1rem;">No Hotel Data Available</h5>
                            <p class="text-muted mb-0" style="font-size: 0.9rem;">Hotel services are booked but detailed information is not available at this moment.</p>
                        </div>
                    @endif
                </div>
                
                <!-- Compact Footer -->
                <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('hotel', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                        <i class="ri-close-line me-1" style="font-size: 0.7rem;"></i>Close
                    </button>
                </div>
            </div>
        </div>
     </div>
    @endif

     <!-- Attraction Details Modal -->
     @if(isset($svc['attraction']) && $svc['attraction'] > 0)
      <div class="modal fade" id="attractionDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="attractionDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
         <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
             <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                 <!-- Compact Header -->
                 <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%);">
                     <div class="d-flex align-items-center justify-content-between w-100">
                         <div class="text-white">
                             <h5 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                 <i class="ri-building-2-line me-1" style="font-size: 0.9rem;"></i>Attraction Bookings 
                             </h5>
                         </div>
                         <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('attraction', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                     </div>
                 </div>
                 
                 <div class="modal-body p-3" style="background-color: #f8f9fa;">
                     @if(isset($serviceData['attraction']) && count($serviceData['attraction']) > 0)
                         @foreach($serviceData['attraction'] as $index => $attractionOrder)
                         @php
                             $attractionData = is_string($attractionOrder->data) ? json_decode($attractionOrder->data, true) : $attractionOrder->data;
                         @endphp
                         
                         @if(is_array($attractionData))
                             @foreach($attractionData as $booking)
                                 @php
                                     $attractionPrice = $booking['totalPrice'] ?? $booking['price'] ?? 0;
                                     $transferPrice = isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0 ? $booking['transfer_options']['cost'] : 0;
                                     $guidePrice = isset($booking['guide_options']['total_price']) && $booking['guide_options']['total_price'] > 0 ? $booking['guide_options']['total_price'] : 0;
                                     $grandTotal = $attractionPrice + $transferPrice + $guidePrice;
                                 @endphp
                                 <div class="card mb-3 shadow-sm border-0" style="border-radius: 10px; overflow: hidden; border-left: 4px solid #fd9853 !important;">
                                     <div class="card-header border-0 py-2 px-3" style="background: linear-gradient(90deg, #fd9853 0%, #fe7854 100%);">
                                         <div class="row align-items-center g-2">
                                             <div class="col-md-8">
                                                 <h6 class="mb-0 fw-bold text-white">
                                                     <i class="ri-building-2-line me-1"></i>{{ $booking['AttractionName'] ?? 'Attraction Booking' }}
                                                 </h6>
                                                 <small class="text-white opacity-90">{{ $booking['ticketName'] ?? 'Standard Ticket' }} • Booking {{ $index + 1 }}</small>
                                             </div>
                                             <div class="col-md-4 text-end">
                                                 <span class="badge bg-white text-success px-3 py-2" style="font-size: 0.95rem;">
                                                     SGD {{ number_format($grandTotal, 2) }}
                                                 </span>
                                             </div>
                                         </div>
                                     </div>
                                     
                                     <div class="card-body p-3" style="background-color: #ffffff;">
                                      
 
                                         <!-- Visit & Booking Information -->
                                         <div class="row mb-3 g-3">
                                             <div class="col-md-12">
                                                 <div class="bg-light rounded p-2 h-100">
                                                     <div class="d-flex align-items-center mb-2">
                                                         <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                             <i class="ri-calendar-line text-white" style="font-size: 0.9rem;"></i>
                                                         </div>
                                                         <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Visit Schedule</h6>
                                                     </div>
                                                     <div class="mb-1">
                                                         <small class="text-muted d-block" style="font-size: 0.75rem;">Visit Date</small>
                                                         <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('D, M d, Y') }}</div>
                                                     </div>
                                                     <div class="mb-1">
                                                         <small class="text-muted d-block" style="font-size: 0.75rem;">Visit Time</small>
                                                         <div class="fw-medium text-primary" style="font-size: 0.85rem;">{{ $booking['visitTime'] ?? 'Full Day Access' }}</div>
                                                     </div>
                                                     <div class="mb-1">
                                                         <small class="text-muted d-block" style="font-size: 0.75rem;">Selection Type</small>
                                                         <div><span class="badge bg-info" style="font-size: 0.7rem;">{{ ucfirst($booking['Selection'] ?? 'Standard') }}</span></div>
                                                     </div>
                                                     <div class="row g-1 mb-1 mt-2">
                                                         <div class="col-4 text-center">
                                                             <div class="bg-white rounded p-1 border" style="border-color: #fd9853 !important;">
                                                                 <div class="fw-bold text-success" style="font-size: 1rem;">{{ $booking['adultCount'] ?? 0 }}</div>
                                                                 <small class="text-muted" style="font-size: 0.6rem;">Adults</small>
                                                             </div>
                                                         </div>
                                                         <div class="col-4 text-center">
                                                             <div class="bg-white rounded p-1 border" style="border-color: #fd9853 !important;">
                                                                 <div class="fw-bold text-warning" style="font-size: 1rem;">{{ $booking['childCount'] ?? 0 }}</div>
                                                                 <small class="text-muted" style="font-size: 0.6rem;">Children</small>
                                                             </div>
                                                         </div>
                                                         <div class="col-4 text-center">
                                                             <div class="bg-white rounded p-1 border" style="border-color: #fd9853 !important;">
                                                                 <div class="fw-bold text-info" style="font-size: 1rem;">{{ $booking['seniorCount'] ?? 0 }}</div>
                                                                 <small class="text-muted" style="font-size: 0.6rem;">Seniors</small>
                                                             </div>
                                                         </div>
                                                     </div>
                                                     <div class="text-center">
                                                         <span class="badge" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); color: white; font-size: 0.8rem; padding: 2px 6px;">
                                                             Total: {{ ($booking['adultCount'] ?? 0) + ($booking['childCount'] ?? 0) + ($booking['seniorCount'] ?? 0) }} Guests
                                                         </span>
                                                     </div>
                                                 </div>
                                             </div>
                                         </div>
 
 
                                         <!-- Ticket & Pricing Details -->
                                         @if(isset($booking['ticket_details']))
                                         <div class="bg-light rounded p-2 mb-3">
                                             <div class="d-flex align-items-center mb-2">
                                                 <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                     <i class="ri-ticket-line text-white" style="font-size: 0.9rem;"></i>
                                                 </div>
                                                 <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Ticket & Pricing Information</h6>
                                             </div>
                                             
                                             <!-- Pricing Cards -->
                                             <div class="row g-2 mb-2">
                                                 <div class="col-md-4">
                                                     <div class="border rounded p-2 text-center bg-white" style="border-color: #28a745 !important;">
                                                         <div class="text-success mb-1">
                                                             <i class="ri-user-line" style="font-size: 1.2rem;"></i>
                                                         </div>
                                                         <h6 class="fw-bold text-success mb-1" style="font-size: 0.8rem;">Adult Ticket</h6>
                                                         <div class="fw-bold text-success" style="font-size: 0.9rem;">SGD {{ number_format($booking['ticket_details']['adult_price'] ?? 0, 2) }}</div>
                                                         <small class="text-muted" style="font-size: 0.65rem;">Per person</small>
                                                     </div>
                                                 </div>
                                                 <div class="col-md-4">
                                                     <div class="border rounded p-2 text-center bg-white" style="border-color: #ffc107 !important;">
                                                         <div class="text-warning mb-1">
                                                             <i class="ri-user-smile-line" style="font-size: 1.2rem;"></i>
                                                         </div>
                                                         <h6 class="fw-bold text-warning mb-1" style="font-size: 0.8rem;">Child Ticket</h6>
                                                         <div class="fw-bold text-warning" style="font-size: 0.9rem;">SGD {{ number_format($booking['ticket_details']['child_price'] ?? 0, 2) }}</div>
                                                         <small class="text-muted" style="font-size: 0.65rem;">Per child</small>
                                                     </div>
                                                 </div>
                                                 <div class="col-md-4">
                                                     <div class="border rounded p-2 text-center bg-white" style="border-color: #17a2b8 !important;">
                                                         <div class="text-info mb-1">
                                                             <i class="ri-user-star-line" style="font-size: 1.2rem;"></i>
                                                         </div>
                                                         <h6 class="fw-bold text-info mb-1" style="font-size: 0.8rem;">Senior Ticket</h6>
                                                         <div class="fw-bold text-info" style="font-size: 0.9rem;">SGD {{ number_format($booking['ticket_details']['senior_price'] ?? 0, 2) }}</div>
                                                         <small class="text-muted" style="font-size: 0.65rem;">Per senior</small>
                                                     </div>
                                                 </div>
                                             </div>

                                             <!-- Booking Summary -->
                                             <div class="bg-white rounded p-2 mb-2">
                                                 <div class="row align-items-center">
                                                     <div class="col-md-8">
                                                         <h6 class="fw-bold text-dark mb-1" style="font-size: 0.85rem;">Booking Summary</h6>
                                                         <div class="d-flex gap-2 flex-wrap">
                                                             @if($booking['adultCount'] ?? 0 > 0)
                                                                 <span class="badge bg-success" style="font-size: 0.7rem;">{{ $booking['adultCount'] }} × SGD {{ number_format($booking['ticket_details']['adult_price'] ?? 0, 2) }}</span>
                                                             @endif
                                                             @if($booking['childCount'] ?? 0 > 0)
                                                                 <span class="badge bg-warning" style="font-size: 0.7rem;">{{ $booking['childCount'] }} × SGD {{ number_format($booking['ticket_details']['child_price'] ?? 0, 2) }}</span>
                                                             @endif
                                                             @if($booking['seniorCount'] ?? 0 > 0)
                                                                 <span class="badge bg-info" style="font-size: 0.7rem;">{{ $booking['seniorCount'] }} × SGD {{ number_format($booking['ticket_details']['senior_price'] ?? 0, 2) }}</span>
                                                             @endif
                                                         </div>
                                                     </div>
                                                     <div class="col-md-4 text-end">
                                                         <small class="text-muted d-block" style="font-size: 0.7rem;">Ticket Total</small>
                                                         <div class="fw-bold text-primary" style="font-size: 1rem;">SGD {{ number_format($attractionPrice, 2) }}</div>
                                                     </div>
                                                 </div>
                                             </div>

                                             @if(isset($booking['ticket_details']['description']) && !empty($booking['ticket_details']['description']))
                                             <!-- Ticket Description -->
                                             <div class="border-start border-3 border-primary ps-2 mt-2">
                                                 <h6 class="fw-bold text-dark mb-1" style="font-size: 0.85rem;">Ticket Information</h6>
                                                 <div class="text-muted" style="font-size: 0.8rem;">{!! $booking['ticket_details']['description'] !!}</div>
                                             </div>
                                             @endif
                                         </div>
                                         @endif

                                         <!-- Transfer Options -->
                                         @php
                                             $transferOptions = $booking['transfer_options'] ?? null;
                                             $hasTransfer = $transferOptions && isset($transferOptions['cost']) && $transferOptions['cost'] > 0;
                                         @endphp
                                         @if($hasTransfer)
                                         <div class="bg-light rounded p-2 mb-3">
                                             <div class="d-flex align-items-center mb-2">
                                                 <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                     <i class="ri-car-line text-white" style="font-size: 0.9rem;"></i>
                                                 </div>
                                                 <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Transfer Details</h6>
                                             </div>
                                             <div class="row g-2">
                                                 <div class="col-md-6">
                                                     <div class="bg-white rounded p-2">
                                                         <small class="text-muted d-block" style="font-size: 0.7rem;">Transfer Type</small>
                                                         <div class="fw-medium" style="font-size: 0.8rem;">
                                                             <span class="badge bg-primary" style="font-size: 0.7rem;">{{ $transferOptions['type'] ?? 'N/A' }}</span>
                                                         </div>
                                                         @if(isset($transferOptions['pickup_location_name']))
                                                         <div class="mt-1">
                                                             <small class="text-muted d-block" style="font-size: 0.7rem;">Pickup</small>
                                                             <div class="fw-medium text-primary" style="font-size: 0.8rem;">{{ $transferOptions['pickup_location_name'] }}</div>
                                                         </div>
                                                         @endif
                                                     </div>
                                                 </div>
                                                 <div class="col-md-6">
                                                     <div class="bg-white rounded p-2">
                                                         @if(isset($transferOptions['vehicle_details']))
                                                             <small class="text-muted d-block" style="font-size: 0.7rem;">Vehicle</small>
                                                             <div class="fw-medium" style="font-size: 0.8rem;">{{ $transferOptions['vehicle_details']['vehicle_name'] ?? 'N/A' }}</div>
                                                             @if(isset($transferOptions['vehicle_details']['seating_capacity']))
                                                             <small class="text-muted" style="font-size: 0.65rem;">Capacity: {{ $transferOptions['vehicle_details']['seating_capacity'] }}</small>
                                                             @endif
                                                         @elseif(isset($transferOptions['vehicle_id']))
                                                             <small class="text-muted d-block" style="font-size: 0.7rem;">Vehicle ID</small>
                                                             <div class="fw-medium" style="font-size: 0.8rem;">{{ $transferOptions['vehicle_id'] }}</div>
                                                         @endif
                                                         @if(isset($transferOptions['cost']))
                                                         <div class="mt-1">
                                                             <small class="text-muted d-block" style="font-size: 0.7rem;">Cost</small>
                                                             <div class="fw-bold text-success" style="font-size: 0.9rem;">SGD {{ number_format($transferOptions['cost'], 2) }}</div>
                                                         </div>
                                                         @endif
                                                     </div>
                                                 </div>
                                             </div>
                                         </div>
                                         @endif

                                         <!-- Guide Options -->
                                         @php
                                             $guideOptions = $booking['guide_options'] ?? null;
                                             $hasGuide = $guideOptions && isset($guideOptions['total_price']) && $guideOptions['total_price'] > 0;
                                         @endphp
                                         @if($hasGuide)
                                         <div class="bg-light rounded p-2 mb-3">
                                             <div class="d-flex align-items-center mb-2">
                                                 <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                     <i class="ri-user-voice-line text-white" style="font-size: 0.9rem;"></i>
                                                 </div>
                                                 <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Guide Details</h6>
                                             </div>
                                             <div class="row g-2">
                                                 <div class="col-md-6">
                                                     <div class="bg-white rounded p-2">
                                                         @if(isset($guideOptions['guide_name']))
                                                         <small class="text-muted d-block" style="font-size: 0.7rem;">Guide Name</small>
                                                         <div class="fw-medium" style="font-size: 0.8rem;">{{ $guideOptions['guide_name'] }}</div>
                                                         @endif
                                                         @if(isset($guideOptions['hours']))
                                                         <div class="mt-1">
                                                             <small class="text-muted d-block" style="font-size: 0.7rem;">Service Hours</small>
                                                             <div class="fw-medium text-primary" style="font-size: 0.8rem;">{{ $guideOptions['hours'] }} Hours</div>
                                                         </div>
                                                         @endif
                                                     </div>
                                                 </div>
                                                 <div class="col-md-6">
                                                     <div class="bg-white rounded p-2">
                                                         @if(isset($guideOptions['base_price']))
                                                         <small class="text-muted d-block" style="font-size: 0.7rem;">Base Price</small>
                                                         <div class="fw-medium" style="font-size: 0.8rem;">SGD {{ number_format($guideOptions['base_price'], 2) }}</div>
                                                         @endif
                                                         @if(isset($guideOptions['total_price']))
                                                         <div class="mt-1">
                                                             <small class="text-muted d-block" style="font-size: 0.7rem;">Total Price</small>
                                                             <div class="fw-bold text-success" style="font-size: 0.9rem;">SGD {{ number_format($guideOptions['total_price'], 2) }}</div>
                                                         </div>
                                                         @endif
                                                     </div>
                                                 </div>
                                             </div>
                                         </div>
                                         @endif

                                         <!-- Pricing Overview -->
                                         <div class="bg-light rounded p-2 mb-3">
                                             <div class="d-flex align-items-center mb-2">
                                                 <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                     <i class="ri-money-dollar-circle-line text-white" style="font-size: 0.9rem;"></i>
                                                 </div>
                                                 <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Pricing Overview</h6>
                                             </div>
                                             <div class="row g-2">
                                                 <div class="col-md-4">
                                                     <div class="text-center p-2 border rounded bg-white" style="border-color: #28a745 !important;">
                                                         <small class="text-muted d-block" style="font-size: 0.7rem;">Attraction Price</small>
                                                         <div class="fw-bold text-success" style="font-size: 0.8rem;">SGD {{ number_format($attractionPrice, 2) }}</div>
                                                     </div>
                                                 </div>
                                                 <div class="col-md-4">
                                                     <div class="text-center p-2 border rounded bg-white" style="border-color: #17a2b8 !important;">
                                                         <small class="text-muted d-block" style="font-size: 0.7rem;">Transfer Price</small>
                                                         <div class="fw-bold text-info" style="font-size: 0.8rem;">SGD {{ number_format($transferPrice, 2) }}</div>
                                                     </div>
                                                 </div>
                                                 <div class="col-md-4">
                                                     <div class="text-center p-2 border rounded bg-white" style="border-color: #6f42c1 !important;">
                                                         <small class="text-muted d-block" style="font-size: 0.7rem;">Guide Price</small>
                                                         <div class="fw-bold" style="font-size: 0.8rem; color: #6f42c1;">SGD {{ number_format($guidePrice, 2) }}</div>
                                                     </div>
                                                 </div>
                                             </div>
                                             <div class="row g-2 mt-2">
                                                 <div class="col-12">
                                                     <div class="text-center p-2 border rounded bg-white" style="border-color: #fd9853 !important; background: linear-gradient(135deg, rgba(253,152,83,0.1) 0%, rgba(254,120,84,0.1) 100%) !important;">
                                                         <small class="text-muted d-block" style="font-size: 0.7rem;">Grand Total</small>
                                                         <div class="fw-bold" style="font-size: 1.1rem; color: #fd9853;">SGD {{ number_format($grandTotal, 2) }}</div>
                                                     </div>
                                                 </div>
                                             </div>
                                             <div class="mt-2 text-center">
                                                 <small class="text-muted" style="font-size: 0.75rem;">
                                                     <i class="ri-information-line me-1"></i>
                                                     Total Price includes Attraction Price + Transfer Price + Guide Price
                                                 </small>
                                             </div>
                                         </div>

                                         <!-- Special Requests -->
                                         @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                             <div class="bg-light rounded p-2">
                                                 <div class="d-flex align-items-center mb-2">
                                                     <div class="rounded-circle p-1 me-2" style="background-color: #6f42c1; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                         <i class="ri-message-line text-white" style="font-size: 0.9rem;"></i>
                                                     </div>
                                                     <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Special Requests</h6>
                                                 </div>
                                                 <div class="bg-white rounded p-2">
                                                     <p class="mb-0 text-dark" style="font-size: 0.85rem;">{{ $booking['specialRequests'] }}</p>
                                                 </div>
                                             </div>
                                         @endif
                                     </div>
                                 </div>
                             @endforeach
                         @endif
                         @endforeach
                     @else
                         <div class="text-center py-4">
                             <div class="bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                 <i class="ri-building-2-line text-muted" style="font-size: 2rem;"></i>
                             </div>
                             <h5 class="text-dark mb-2" style="font-size: 1.1rem;">No Attraction Data Available</h5>
                             <p class="text-muted mb-0" style="font-size: 0.9rem;">Attraction services are booked but detailed information is not available.</p>
                         </div>
                     @endif
                 </div>
                 
                 <!-- Compact Footer -->
                 <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                     <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('attraction', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                         <i class="ri-close-line me-1" style="font-size: 0.7rem;"></i>Close
                     </button>
                 </div>
             </div>
         </div>
      </div>
     @endif

     <!-- Restaurant Details Modal -->
     @if(isset($svc['restaurant']) && $svc['restaurant'] > 0)
      <div class="modal fade" id="restaurantDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="restaurantDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
         <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
             <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                 <!-- Compact Header -->
                 <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%);">
                     <div class="d-flex align-items-center justify-content-between w-100">
                         <div class="text-white">
                             <h5 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                 <i class="ri-restaurant-2-line me-1" style="font-size: 0.9rem;"></i>Restaurant Bookings 
                             </h5>
                         </div>
                         <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('restaurant', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                     </div>
                 </div>
                 
                 <div class="modal-body p-3" style="background-color: #f8f9fa;">
                     @if(isset($serviceData['restaurant']) && count($serviceData['restaurant']) > 0)
                         @foreach($serviceData['restaurant'] as $index => $restaurantOrder)
                         @php
                             $restaurantData = is_string($restaurantOrder->data) ? json_decode($restaurantOrder->data, true) : $restaurantOrder->data;
                         @endphp
                         
                         @if(is_array($restaurantData))
                             @foreach($restaurantData as $booking)
                                 <div class="card mb-3 shadow-sm border-0" style="border-radius: 10px; overflow: hidden; border-left: 4px solid #fd79a8 !important;">
                                     <div class="card-header border-0 py-2 px-3" style="background: linear-gradient(90deg, #fd79a8 0%, #fdcb6e 100%);">
                                         <div class="row align-items-center g-2">
                                             <div class="col-md-8">
                                                 <h6 class="mb-0 fw-bold text-white">
                                                     <i class="ri-restaurant-2-line me-1"></i>{{ $booking['restaurantName'] ?? 'Restaurant Booking' }}
                                                 </h6>
                                                 <small class="text-white opacity-90">{{ ucfirst($booking['mealType'] ?? 'Meal') }} • {{ $booking['mealSpecificType'] ?? 'Standard' }}</small>
                                             </div>
                                             <div class="col-md-4 text-end">
                                                 <span class="badge bg-white text-success px-3 py-2" style="font-size: 0.95rem;">
                                                     SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}
                                                 </span>
                                             </div>
                                         </div>
                                     </div>
                                     
                                     <div class="card-body p-3" style="background-color: #ffffff;">
                                         <!-- Reservation Details -->
                                         <div class="row mb-3 g-3">
                                             <div class="col-md-12">
                                                 <div class="bg-light rounded p-2 h-100">
                                                     <div class="d-flex align-items-center mb-2">
                                                         <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                             <i class="ri-calendar-line text-white" style="font-size: 0.9rem;"></i>
                                                         </div>
                                                         <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Reservation Details</h6>
                                                     </div>
                                                     <div class="mb-1">
                                                         <small class="text-muted d-block" style="font-size: 0.75rem;">Dining Date</small>
                                                         <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('D, M d, Y') }}</div>
                                                     </div>
                                                     <div class="mb-1">
                                                         <small class="text-muted d-block" style="font-size: 0.75rem;">Dining Time</small>
                                                         <div class="fw-medium text-primary" style="font-size: 0.85rem;">{{ $booking['visitTime'] ?? 'TBC' }}</div>
                                                     </div>
                                                     <div class="row g-1 mb-1">
                                                         <div class="col-6 text-center">
                                                             <div class="bg-white rounded p-1 border" style="border-color: #fd79a8 !important;">
                                                                 <div class="fw-bold text-success" style="font-size: 1rem;">{{ $booking['adultCount'] ?? 0 }}</div>
                                                                 <small class="text-muted" style="font-size: 0.6rem;">Adults</small>
                                                             </div>
                                                         </div>
                                                         <div class="col-6 text-center">
                                                             <div class="bg-white rounded p-1 border" style="border-color: #fd79a8 !important;">
                                                                 <div class="fw-bold text-warning" style="font-size: 1rem;">{{ $booking['childCount'] ?? 0 }}</div>
                                                                 <small class="text-muted" style="font-size: 0.6rem;">Children</small>
                                                             </div>
                                                         </div>
                                                     </div>
                                                     <div class="text-center">
                                                         <span class="badge" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); color: white; font-size: 0.8rem; padding: 2px 6px;">
                                                             Party: {{ ($booking['adultCount'] ?? 0) + ($booking['childCount'] ?? 0) }}
                                                         </span>
                                                     </div>
                                                 </div>
                                             </div>
                                         </div>

                                         <!-- Transfer Options -->
                                         @php
                                             $transferOptions = $booking['transferOptions'] ?? $booking['transfer_options'] ?? null;
                                             $transferRequired = false;
                                             if ($transferOptions) {
                                                 $transferRequired = isset($transferOptions['transfer_required']) && (
                                                     $transferOptions['transfer_required'] === true || 
                                                     $transferOptions['transfer_required'] === 'true' || 
                                                     $transferOptions['transfer_required'] === 'Yes' || 
                                                     $transferOptions['transfer_required'] == 1
                                                 );
                                             }
                                         @endphp
                                         @if($transferRequired)
                                         <div class="bg-light rounded p-2 mb-3">
                                             <div class="d-flex align-items-center mb-2">
                                                 <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                     <i class="ri-car-line text-white" style="font-size: 0.9rem;"></i>
                                                 </div>
                                                 <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Transfer Details</h6>
                                             </div>
                                             <div class="row g-2">
                                                 <div class="col-md-6">
                                                     <div class="bg-white rounded p-2">
                                                         <small class="text-muted d-block" style="font-size: 0.7rem;">Transfer Type</small>
                                                         <div class="fw-medium" style="font-size: 0.8rem;">
                                                             <span class="badge bg-primary" style="font-size: 0.7rem;">{{ $transferOptions['type'] ?? 'N/A' }}</span>
                                                         </div>
                                                         @if(isset($transferOptions['pickup_location_name']))
                                                         <div class="mt-1">
                                                             <small class="text-muted d-block" style="font-size: 0.7rem;">Pickup</small>
                                                             <div class="fw-medium text-primary" style="font-size: 0.8rem;">{{ $transferOptions['pickup_location_name'] }}</div>
                                                         </div>
                                                         @endif
                                                     </div>
                                                 </div>
                                                 <div class="col-md-6">
                                                     <div class="bg-white rounded p-2">
                                                         @if(isset($transferOptions['vehicle_details']))
                                                             <small class="text-muted d-block" style="font-size: 0.7rem;">Vehicle</small>
                                                             <div class="fw-medium" style="font-size: 0.8rem;">{{ $transferOptions['vehicle_details']['vehicle_name'] ?? 'N/A' }}</div>
                                                             @if(isset($transferOptions['vehicle_details']['seating_capacity']))
                                                             <small class="text-muted" style="font-size: 0.65rem;">Capacity: {{ $transferOptions['vehicle_details']['seating_capacity'] }}</small>
                                                             @endif
                                                         @elseif(isset($transferOptions['vehicle_id']))
                                                             <small class="text-muted d-block" style="font-size: 0.7rem;">Vehicle ID</small>
                                                             <div class="fw-medium" style="font-size: 0.8rem;">{{ $transferOptions['vehicle_id'] }}</div>
                                                         @endif
                                                         @if(isset($transferOptions['cost']))
                                                         <div class="mt-1">
                                                             <small class="text-muted d-block" style="font-size: 0.7rem;">Cost</small>
                                                             <div class="fw-bold text-success" style="font-size: 0.9rem;">SGD {{ number_format($transferOptions['cost'], 2) }}</div>
                                                         </div>
                                                         @endif
                                                     </div>
                                                 </div>
                                             </div>
                                         </div>
                                         @endif

                                         <!-- Pricing Overview -->
                                         @php
                                             $mealPrice = $booking['mealPrice'] ?? $booking['totalPrice'] ?? 0;
                                             $transportPrice = $transferOptions['cost'] ?? $booking['transportPrice'] ?? 0;
                                             $grandTotal = $mealPrice + $transportPrice;
                                         @endphp
                                         <div class="bg-light rounded p-2 mb-3">
                                             <div class="d-flex align-items-center mb-2">
                                                 <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                     <i class="ri-money-dollar-circle-line text-white" style="font-size: 0.9rem;"></i>
                                                 </div>
                                                 <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Pricing Overview</h6>
                                             </div>
                                             <div class="row g-2">
                                                 <div class="col-md-4">
                                                     <div class="text-center p-2 border rounded bg-white" style="border-color: #28a745 !important;">
                                                         <small class="text-muted d-block" style="font-size: 0.7rem;">Meal Price</small>
                                                         <div class="fw-bold text-success" style="font-size: 0.8rem;">SGD {{ number_format($mealPrice, 2) }}</div>
                                                     </div>
                                                 </div>
                                                 <div class="col-md-4">
                                                     <div class="text-center p-2 border rounded bg-white" style="border-color: #17a2b8 !important;">
                                                         <small class="text-muted d-block" style="font-size: 0.7rem;">Vehicle Price</small>
                                                         <div class="fw-bold text-info" style="font-size: 0.8rem;">SGD {{ number_format($transportPrice, 2) }}</div>
                                                     </div>
                                                 </div>
                                                 <div class="col-md-4">
                                                     <div class="text-center p-2 border rounded bg-white" style="border-color: #fd79a8 !important; background: linear-gradient(135deg, rgba(253,121,168,0.1) 0%, rgba(253,203,110,0.1) 100%) !important;">
                                                         <small class="text-muted d-block" style="font-size: 0.7rem;">Grand Total</small>
                                                         <div class="fw-bold" style="font-size: 1.1rem; color: #fd79a8;">SGD {{ number_format($grandTotal, 2) }}</div>
                                                     </div>
                                                 </div>
                                             </div>
                                             <div class="mt-2 text-center">
                                                 <small class="text-muted" style="font-size: 0.75rem;">
                                                     <i class="ri-information-line me-1"></i>
                                                     Total Price includes Restaurant Price + Vehicle Price
                                                 </small>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             @endforeach
                         @endif
                         @endforeach
                     @else
                         <div class="text-center py-4">
                             <div class="bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                 <i class="ri-restaurant-2-line text-muted" style="font-size: 2rem;"></i>
                             </div>
                             <h5 class="text-dark mb-2" style="font-size: 1.1rem;">No Restaurant Data Available</h5>
                             <p class="text-muted mb-0" style="font-size: 0.9rem;">Restaurant services are booked but detailed information is not available.</p>
                         </div>
                     @endif
                 </div>
                 
                 <!-- Compact Footer -->
                 <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                     <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('restaurant', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                         <i class="ri-close-line me-1" style="font-size: 0.7rem;"></i>Close
                     </button>
                 </div>
             </div>
         </div>
      </div>
     @endif
 

    <!-- Guide Details Modal -->
    @if(isset($svc['guide']) && $svc['guide'] > 0)
     <div class="modal fade" id="guideDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="guideDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                <!-- Compact Header -->
                <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%);">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="text-white">
                            <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                <i class="ri-user-voice-line me-1" style="font-size: 0.9rem;"></i>Guide Bookings
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
                            @php $actualBookingIndex = 0; @endphp
                            @foreach($guideData as $booking)
                                @php $bookingIndex = $actualBookingIndex; $actualBookingIndex++; @endphp
                                <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #00cec9 !important;">
                                    <!-- Compact Card Header -->
                                    <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #00cec9 0%, #55a3ff 100%);">
                                        <div class="row align-items-center g-1">
                                            <div class="col-md-8">
                                                <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                    <i class="ri-user-voice-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['guide_name'] ?? 'Guide Booking' }}
                                                </h6>
                                                <small class="text-white opacity-90" style="font-size: 0.7rem;">Guide Service • {{ $booking['hours'] ?? 'N/A' }}H</small>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                    SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body p-2" style="background-color: #ffffff;">
                                        <!-- Guide Information & Image -->
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
                                                            <div class="fw-medium text-success" style="font-size: 0.75rem;">SGD {{ number_format($booking['basePrice'] ?? 0, 2) }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Surcharge</small>
                                                            <div class="fw-medium text-warning" style="font-size: 0.75rem;">SGD {{ number_format($booking['surcharge'] ?? 0, 2) }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                @if(isset($booking['image']) && !empty($booking['image']))
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <img src="{{ $booking['image'] }}" 
                                                             alt="{{ $booking['guide_name'] ?? 'Guide' }}" 
                                                             class="rounded-circle shadow-sm" 
                                                             style="width: 80px; height: 80px; object-fit: cover; border: 2px solid #00cec9; display: block; margin: 0; padding: 0;"
                                                             onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm\\' style=\\'width: 80px; height: 80px; border: 2px solid #e9ecef;\\'><i class=\\'ri-user-voice-line text-muted\\' style=\\'font-size: 2rem;\\'></i></div>';">
                                                    </div>
                                                @else
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px; border: 2px solid #e9ecef;">
                                                            <i class="ri-user-voice-line text-muted" style="font-size: 2rem;"></i>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Service Schedule & Group Information -->
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
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Date</small>
                                                            <div class="fw-medium" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Time</small>
                                                            <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'TBC' }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Duration</small>
                                                            <span class="badge" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); color: white; font-size: 0.65rem; padding: 2px 4px;">{{ $booking['hours'] ?? 'N/A' }}H</span>
                                                        </div>
                                                    </div>
                                                    @if(($booking['Night_Start_Time'] ?? false) && ($booking['Night_End_Time'] ?? false))
                                                    <div class="bg-white rounded p-1 mt-1">
                                                        <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Night Service</small>
                                                        <div class="fw-medium text-warning" style="font-size: 0.75rem;">{{ $booking['Night_Start_Time'] }} - {{ $booking['Night_End_Time'] }}</div>
                                                    </div>
                                                    @endif
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
                                                    <div class="row g-1 mb-1">
                                                        <div class="col-6 text-center">
                                                            <div class="bg-white rounded p-1 border" style="border-color: #00cec9 !important;">
                                                                <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['adults'] ?? 0 }}</div>
                                                                <small class="text-muted" style="font-size: 0.55rem;">Adults</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 text-center">
                                                            <div class="bg-white rounded p-1 border" style="border-color: #00cec9 !important;">
                                                                <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['children'] ?? 0 }}</div>
                                                                <small class="text-muted" style="font-size: 0.55rem;">Children</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-center">
                                                        <span class="badge" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); color: white; font-size: 0.7rem; padding: 2px 4px;">
                                                            Total: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} Guests
                                                        </span>
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
                                                <div class="col-md-4">
                                                    <div class="text-center p-1 border rounded bg-white" style="border-color: #28a745 !important;">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Base Price</small>
                                                        <div class="fw-bold text-success" style="font-size: 0.8rem;">SGD {{ number_format($booking['basePrice'] ?? 0, 2) }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-center p-1 border rounded bg-white" style="border-color: #ffc107 !important;">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Surcharge</small>
                                                        <div class="fw-bold text-warning" style="font-size: 0.8rem;">SGD {{ number_format($booking['surcharge'] ?? 0, 2) }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-center p-1 border rounded bg-white" style="border-color: #00cec9 !important; background: linear-gradient(135deg, rgba(0,206,201,0.1) 0%, rgba(85,163,255,0.1) 100%) !important;">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Total Amount</small>
                                                        <div class="fw-bold" style="font-size: 0.9rem; color: #00cec9;">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Address & Location -->
                                        @if(($booking['entrypickup'] ?? false) || ($booking['address1'] ?? false) || ($booking['address2'] ?? false) || ($booking['state'] ?? false))
                                        <div class="bg-light rounded p-2 mb-2">
                                            <div class="d-flex align-items-center mb-1">
                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ri-map-pin-line text-white" style="font-size: 0.8rem;"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Address & Location</h6>
                                            </div>
                                            <div class="bg-white rounded p-1">
                                                @if($booking['entrypickup'] ?? false)
                                                    <div class="mb-1">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Location</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrypickup'] }}</div>
                                                    </div>
                                                @endif
                                                @if($booking['address1'] ?? false || $booking['address2'] ?? false || $booking['state'] ?? false)
                                                    <div>
                                                        <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Address</small>
                                                        <div class="text-muted" style="font-size: 0.75rem;">
                                                            @if($booking['address1'] ?? false)
                                                                <div>{{ $booking['address1'] }}</div>
                                                            @endif
                                                            @if($booking['address2'] ?? false)
                                                                <div>{{ $booking['address2'] }}</div>
                                                            @endif
                                                            @if($booking['state'] ?? false)
                                                                <div>{{ $booking['state'] }} {{ $booking['zip'] ?? '' }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Special Requests -->
                                        @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                        <div class="bg-light rounded p-2 mb-2">
                                            <div class="d-flex align-items-center mb-1">
                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ri-message-2-line text-white" style="font-size: 0.8rem;"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Special Requests</h6>
                                            </div>
                                            <div class="bg-white rounded p-1">
                                                <p class="text-muted mb-0" style="font-size: 0.75rem;">{{ $booking['specialRequests'] }}</p>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <div class="bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="ri-user-voice-line text-muted" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="text-dark mb-2" style="font-size: 1.1rem;">No Guide Data Available</h5>
                            <p class="text-muted mb-0" style="font-size: 0.9rem;">Guide services are booked but detailed information is not available.</p>
                        </div>
                    @endif
                </div>
                
                <!-- Compact Footer -->
                <div class="modal-footer border-0 p-1" style="background: #f8f9fa;">
                    <div class="d-flex gap-1 w-100 justify-content-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" onclick="closeServiceModal('guide', {{ $tour->tour_id }})" style="border-radius: 6px; font-size: 0.75rem;">
                            <i class="ri-close-line me-1" style="font-size: 0.7rem;"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
     </div>
    @endif

    <!-- Entry Port (Arrival) Details Modal -->
    @if(isset($svc['entry_port']) && $svc['entry_port'] > 0)
     <div class="modal fade" id="entry_portDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="entry_portDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                <!-- Compact Header -->
                <div class="modal-header border-0 p-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%);">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="text-white">
                            <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                <i class="ri-flight-land-line me-1" style="font-size: 0.9rem;"></i>Arrival Transfer
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
                            @php $actualBookingIndex = 0; @endphp
                            @foreach($entryData as $originalKey => $booking)
                                @php $bookingIndex = $actualBookingIndex; @endphp
                                <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #00b894 !important;">
                                    <!-- Compact Card Header -->
                                    <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #00b894 0%, #55a3ff 100%);">
                                        <div class="row align-items-center g-1">
                                            <div class="col-md-8">
                                                <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                    <i class="ri-car-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['vehicles_name'] ?? 'Vehicle Transfer' }}
                                                </h6>
                                                <small class="text-white opacity-90" style="font-size: 0.7rem;">Arrival {{ $index + 1 }} • {{ ucfirst($booking['type'] ?? 'Standard') }}</small>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                    SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body p-2" style="background-color: #ffffff;">
                                        <!-- Service Schedule & Group Information -->
                                        <div class="row mb-2 g-2">
                                            <div class="col-md-6">
                                                <div class="bg-light rounded p-2 h-100">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="ri-calendar-line text-white" style="font-size: 0.8rem;"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Service Schedule</h6>
                                                    </div>
                                                    <div class="row g-1">
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Date</small>
                                                            <div class="fw-medium" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Time</small>
                                                            <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'TBC' }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Type</small>
                                                            <div><span class="badge bg-warning px-1 py-0" style="font-size: 0.65rem;">{{ ucfirst($booking['type'] ?? 'Standard') }}</span></div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Transfer</small>
                                                            <div><span class="badge bg-info px-1 py-0" style="font-size: 0.65rem;">Arrival</span></div>
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
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Group Information</h6>
                                                    </div>
                                                    <div class="row g-1 mb-1">
                                                        <div class="col-6 text-center">
                                                            <div class="bg-white rounded p-1 border" style="border-color: #00b894 !important;">
                                                                <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['adults'] ?? 0 }}</div>
                                                                <small class="text-muted" style="font-size: 0.55rem;">Adults</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 text-center">
                                                            <div class="bg-white rounded p-1 border" style="border-color: #00b894 !important;">
                                                                <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['children'] ?? 0 }}</div>
                                                                <small class="text-muted" style="font-size: 0.55rem;">Children</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-center">
                                                        <span class="badge" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); color: white; font-size: 0.7rem; padding: 2px 4px;">
                                                            Total: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} Guests
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Route Information -->
                                        <div class="bg-light rounded p-2 mb-2">
                                            <div class="d-flex align-items-center mb-1">
                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ri-route-line text-white" style="font-size: 0.8rem;"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Route Information</h6>
                                            </div>
                                            <div class="row g-1 mb-1">
                                                <div class="col-md-6">
                                                    <div class="bg-white rounded p-1">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup</small>
                                                        <div class="fw-medium d-flex align-items-center" style="font-size: 0.75rem;">
                                                            <i class="ri-map-pin-line text-success me-1" style="font-size: 0.7rem;"></i>
                                                            <span class="text-truncate">{{ $booking['entrypickup'] ?? 'N/A' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="bg-white rounded p-1">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Dropoff</small>
                                                        <div class="fw-medium d-flex align-items-center" style="font-size: 0.75rem;">
                                                            <i class="ri-map-pin-2-line text-danger me-1" style="font-size: 0.7rem;"></i>
                                                            <span class="text-truncate">{{ $booking['entrydropoff'] ?? 'N/A' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Compact Route Direction Visual -->
                                            <div class="d-flex align-items-center justify-content-center p-1 bg-white rounded">
                                                <span class="badge bg-success me-1" style="font-size: 0.65rem; padding: 2px 4px;">{{ Str::limit($booking['entrypickup'] ?? 'Pickup', 15) }}</span>
                                                <i class="ri-arrow-right-line text-primary mx-1" style="font-size: 0.8rem;"></i>
                                                <span class="badge bg-danger" style="font-size: 0.65rem; padding: 2px 4px;">{{ Str::limit($booking['entrydropoff'] ?? 'Dropoff', 15) }}</span>
                                            </div>
                                        </div>

                                        <!-- Vehicle & Location Information -->
                                        <div class="row mb-2 g-2">
                                            <div class="col-md-6">
                                                <div class="bg-light rounded p-2 h-100" style="overflow: hidden;">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                            <i class="ri-car-line text-white" style="font-size: 0.8rem;"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Vehicle Details</h6>
                                                    </div>
                                                    <div class="row g-1 mb-2">
                                                        <div class="col-6">
                                                            <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Vehicle</small>
                                                            <div class="fw-medium text-truncate" style="font-size: 0.75rem;" title="{{ $booking['vehicles_name'] ?? 'N/A' }}">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Service</small>
                                                            <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['type'] ?? 'N/A' }}</div>
                                                        </div>
                                                    </div>
                                                    <!-- Compact Vehicle Image Display -->
                                                    <div class="d-flex justify-content-center align-items-center" style="min-height: 80px; width: 100%; overflow: hidden; position: relative;">
                                                        @if(isset($booking['image']) && !empty($booking['image']))
                                                            <div class="position-relative" style="width: 80px; height: 80px; flex-shrink: 0; overflow: hidden;">
                                                                <img src="{{ $booking['image'] }}" 
                                                                     alt="Vehicle Image" 
                                                                     class="rounded-circle shadow-sm" 
                                                                     style="width: 80px; height: 80px; object-fit: cover; object-position: center; border: 2px solid #00b894; display: block; margin: 0; padding: 0; background: #f8f9fa;"
                                                                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm\\' style=\\'width: 80px; height: 80px; border: 2px solid #e9ecef;\\'><i class=\\'ri-car-line text-muted\\' style=\\'font-size: 2rem;\\'></i></div>';">
                                                            </div>
                                                        @else
                                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px; border: 2px solid #e9ecef; flex-shrink: 0;">
                                                                <i class="ri-car-line text-muted" style="font-size: 2rem;"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-light rounded p-2 h-100">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="ri-map-pin-line text-white" style="font-size: 0.8rem;"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Location Information</h6>
                                                    </div>
                                                    <div class="row g-1 mb-1">
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">City</small>
                                                            <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['city'] ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Country</small>
                                                            <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['country'] ?? 'N/A' }}</div>
                                                        </div>
                                                    </div>
                                                    <!-- Compact Pricing Details -->
                                                    <div class="bg-white rounded p-1 mt-1">
                                                        <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Total Price</small>
                                                        <div class="fw-bold text-success" style="font-size: 0.9rem;">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Special Requests -->
                                        @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                        <div class="bg-light rounded p-2 mb-2">
                                            <div class="d-flex align-items-center mb-1">
                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ri-message-2-line text-white" style="font-size: 0.8rem;"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Special Requests</h6>
                                            </div>
                                            <div class="bg-white rounded p-1">
                                                <p class="text-muted mb-0" style="font-size: 0.75rem;">{{ $booking['specialRequests'] }}</p>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @php $actualBookingIndex++; @endphp
                            @endforeach
                        @endif
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="ri-flight-land-line text-muted" style="font-size: 1.5rem;"></i>
                            </div>
                            <h6 class="text-dark mb-1">No Arrival Transfer Data Available</h6>
                            <p class="text-muted mb-0" style="font-size: 0.85rem;">Entry port services are booked but detailed information is not available.</p>
                        </div>
                    @endif
                </div>
                
                <!-- Compact Footer -->
                <div class="modal-footer border-0 p-1" style="background: #f8f9fa;">
                    <div class="d-flex gap-1 w-100 justify-content-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" onclick="closeServiceModal('entry_port', {{ $tour->tour_id }})" style="border-radius: 6px; font-size: 0.75rem;">
                            <i class="ri-close-line me-1" style="font-size: 0.7rem;"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
     </div>
    @endif

    <!-- Exit Port (Departure) Details Modal -->
    @if(isset($svc['exit_port']) && $svc['exit_port'] > 0)
     <div class="modal fade" id="exit_portDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="exit_portDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                <!-- Compact Header -->
                <div class="modal-header border-0 p-2" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%);">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="text-white">
                            <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                <i class="ri-flight-takeoff-line me-1" style="font-size: 0.9rem;"></i>Departure Transfer
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
                            @php $actualBookingIndex = 0; @endphp
                            @foreach($exitData as $bookingIndex => $booking)
                                <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #fd7f6f !important;">
                                    <!-- Compact Card Header -->
                                    <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #fd7f6f 0%, #feb47b 100%);">
                                        <div class="row align-items-center g-1">
                                            <div class="col-md-8">
                                                <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                    <i class="ri-car-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['vehicles_name'] ?? 'Vehicle Transfer' }}
                                                </h6>
                                                <small class="text-white opacity-90" style="font-size: 0.7rem;">Departure {{ $index + 1 }} • {{ ucfirst($booking['type'] ?? 'Standard') }}</small>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                    SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body p-2" style="background-color: #ffffff;">
                                        <!-- Service Schedule & Group Information -->
                                        <div class="row mb-2 g-2">
                                            <div class="col-md-6">
                                                <div class="bg-light rounded p-2 h-100">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="ri-calendar-line text-white" style="font-size: 0.8rem;"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Service Schedule</h6>
                                                    </div>
                                                    <div class="row g-1">
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Date</small>
                                                            <div class="fw-medium" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Time</small>
                                                            <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'TBC' }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Type</small>
                                                            <div><span class="badge bg-warning px-1 py-0" style="font-size: 0.65rem;">{{ ucfirst($booking['type'] ?? 'Standard') }}</span></div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Transfer</small>
                                                            <div><span class="badge bg-info px-1 py-0" style="font-size: 0.65rem;">Departure</span></div>
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
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Group Information</h6>
                                                    </div>
                                                    <div class="row g-1 mb-1">
                                                        <div class="col-6 text-center">
                                                            <div class="bg-white rounded p-1 border" style="border-color: #fd7f6f !important;">
                                                                <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['adults'] ?? 0 }}</div>
                                                                <small class="text-muted" style="font-size: 0.55rem;">Adults</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 text-center">
                                                            <div class="bg-white rounded p-1 border" style="border-color: #fd7f6f !important;">
                                                                <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['children'] ?? 0 }}</div>
                                                                <small class="text-muted" style="font-size: 0.55rem;">Children</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-center">
                                                        <span class="badge" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%); color: white; font-size: 0.7rem; padding: 2px 4px;">
                                                            Total: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} Guests
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Route Information -->
                                        <div class="bg-light rounded p-2 mb-2">
                                            <div class="d-flex align-items-center mb-1">
                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ri-route-line text-white" style="font-size: 0.8rem;"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Route Information</h6>
                                            </div>
                                            <div class="row g-1 mb-1">
                                                <div class="col-md-6">
                                                    <div class="bg-white rounded p-1">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup</small>
                                                        <div class="fw-medium d-flex align-items-center" style="font-size: 0.75rem;">
                                                            <i class="ri-map-pin-line text-success me-1" style="font-size: 0.7rem;"></i>
                                                            <span class="text-truncate">{{ $booking['exitpickup'] ?? 'N/A' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="bg-white rounded p-1">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Dropoff</small>
                                                        <div class="fw-medium d-flex align-items-center" style="font-size: 0.75rem;">
                                                            <i class="ri-map-pin-2-line text-danger me-1" style="font-size: 0.7rem;"></i>
                                                            <span class="text-truncate">{{ $booking['exitdropoff'] ?? 'N/A' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Compact Route Direction Visual -->
                                            <div class="d-flex align-items-center justify-content-center p-1 bg-white rounded">
                                                <span class="badge bg-success me-1" style="font-size: 0.65rem; padding: 2px 4px;">{{ Str::limit($booking['exitpickup'] ?? 'Pickup', 15) }}</span>
                                                <i class="ri-arrow-right-line text-primary mx-1" style="font-size: 0.8rem;"></i>
                                                <span class="badge bg-danger" style="font-size: 0.65rem; padding: 2px 4px;">{{ Str::limit($booking['exitdropoff'] ?? 'Dropoff', 15) }}</span>
                                            </div>
                                        </div>

                                        <!-- Vehicle & Location Information -->
                                        <div class="row mb-2 g-2">
                                            <div class="col-md-6">
                                                <div class="bg-light rounded p-2 h-100" style="overflow: hidden;">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                            <i class="ri-car-line text-white" style="font-size: 0.8rem;"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Vehicle Details</h6>
                                                    </div>
                                                    <div class="row g-1 mb-2">
                                                        <div class="col-6">
                                                            <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Vehicle</small>
                                                            <div class="fw-medium text-truncate" style="font-size: 0.75rem;" title="{{ $booking['vehicles_name'] ?? 'N/A' }}">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Service</small>
                                                            <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['type'] ?? 'N/A' }}</div>
                                                        </div>
                                                    </div>
                                                    <!-- Compact Vehicle Image Display -->
                                                    <div class="d-flex justify-content-center align-items-center" style="min-height: 80px; width: 100%; overflow: hidden; position: relative;">
                                                        @if(isset($booking['image']) && !empty($booking['image']))
                                                            <div class="position-relative" style="width: 80px; height: 80px; flex-shrink: 0; overflow: hidden;">
                                                                <img src="{{ $booking['image'] }}" 
                                                                     alt="Vehicle Image" 
                                                                     class="rounded-circle shadow-sm" 
                                                                     style="width: 80px; height: 80px; object-fit: cover; object-position: center; border: 2px solid #fd7f6f; display: block; margin: 0; padding: 0; background: #f8f9fa;"
                                                                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm\\' style=\\'width: 80px; height: 80px; border: 2px solid #e9ecef;\\'><i class=\\'ri-car-line text-muted\\' style=\\'font-size: 2rem;\\'></i></div>';">
                                                            </div>
                                                        @else
                                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px; border: 2px solid #e9ecef; flex-shrink: 0;">
                                                                <i class="ri-car-line text-muted" style="font-size: 2rem;"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-light rounded p-2 h-100">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="ri-map-pin-line text-white" style="font-size: 0.8rem;"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Location Information</h6>
                                                    </div>
                                                    <div class="row g-1 mb-1">
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">City</small>
                                                            <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['city'] ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Country</small>
                                                            <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['country'] ?? 'N/A' }}</div>
                                                        </div>
                                                    </div>
                                                    <!-- Compact Pricing Details -->
                                                    <div class="bg-white rounded p-1 mt-1">
                                                        <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Total Price</small>
                                                        <div class="fw-bold text-success" style="font-size: 0.9rem;">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Special Requests -->
                                        @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                        <div class="bg-light rounded p-2 mb-2">
                                            <div class="d-flex align-items-center mb-1">
                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ri-message-2-line text-white" style="font-size: 0.8rem;"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Special Requests</h6>
                                            </div>
                                            <div class="bg-white rounded p-1">
                                                <p class="text-muted mb-0" style="font-size: 0.75rem;">{{ $booking['specialRequests'] }}</p>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @php $actualBookingIndex++; @endphp
                            @endforeach
                        @endif
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="ri-flight-takeoff-line text-muted" style="font-size: 1.5rem;"></i>
                            </div>
                            <h6 class="text-dark mb-1">No Departure Transfer Data Available</h6>
                            <p class="text-muted mb-0" style="font-size: 0.85rem;">Exit port services are booked but detailed information is not available.</p>
                        </div>
                    @endif
                </div>
                
                <!-- Compact Footer -->
                <div class="modal-footer border-0 p-1" style="background: #f8f9fa;">
                    <div class="d-flex gap-1 w-100 justify-content-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" onclick="closeServiceModal('exit_port', {{ $tour->tour_id }})" style="border-radius: 6px; font-size: 0.75rem;">
                            <i class="ri-close-line me-1" style="font-size: 0.7rem;"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
     </div>
    @endif

    <!-- Travel Hourly Details Modal -->
    @if(isset($svc['travel_hourly']) && $svc['travel_hourly'] > 0)
        <div class="modal fade" id="travel_hourlyDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="travel_hourlyModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                    <!-- Compact Header -->
                    <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="text-white">
                                <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                    <i class="ri-time-line me-1" style="font-size: 0.9rem;"></i>Hourly Tour Details
                                </h6>
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
                                        <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #667eea !important;">
                                            <!-- Compact Card Header -->
                                            <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);">
                                                <div class="row align-items-center g-1">
                                                    <div class="col-md-8">
                                                        <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                            <i class="ri-time-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['vehicles_name'] ?? 'Hourly Tour Service' }}
                                                        </h6>
                                                        <small class="text-white opacity-90" style="font-size: 0.7rem;">Hourly Tour {{ $index + 1 }} • {{ $booking['type'] ?? 'Standard' }}</small>
                                                    </div>
                                                    <div class="col-md-4 text-end">
                                                        <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                            SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="card-body p-2" style="background-color: #ffffff;">
                                                <!-- Service Schedule & Group Information -->
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
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Date</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') : 'N/A' }}</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Time</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'TBC' }}</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Hours</small>
                                                                    <span class="badge bg-info px-1 py-0" style="font-size: 0.65rem;">{{ $booking['selectedHours'] ?? 'N/A' }}H</span>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Type</small>
                                                                    <span class="badge bg-warning px-1 py-0" style="font-size: 0.65rem;">{{ $booking['type'] ?? 'Standard' }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="bg-light rounded p-2 h-100">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                    <i class="ri-group-line text-white" style="font-size: 0.8rem;"></i>
                                                                </div>
                                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Group Information</h6>
                                                            </div>
                                                            <div class="row g-1 mb-1">
                                                                <div class="col-6 text-center">
                                                                    <div class="bg-white rounded p-1 border" style="border-color: #667eea !important;">
                                                                        <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['adults'] ?? 0 }}</div>
                                                                        <small class="text-muted" style="font-size: 0.55rem;">Adults</small>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6 text-center">
                                                                    <div class="bg-white rounded p-1 border" style="border-color: #667eea !important;">
                                                                        <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['children'] ?? 0 }}</div>
                                                                        <small class="text-muted" style="font-size: 0.55rem;">Children</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="text-center">
                                                                <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 0.7rem; padding: 2px 4px;">
                                                                    Total: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} Guests
                                                                </span>
                                                            </div>
                                                            @if(isset($booking['Night_Start_Time']) && isset($booking['Night_End_Time']))
                                                            <div class="bg-white rounded p-1 mt-1">
                                                                <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Night Service</small>
                                                                <div class="fw-medium text-warning" style="font-size: 0.75rem;">{{ $booking['Night_Start_Time'] }} - {{ $booking['Night_End_Time'] }}</div>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Pickup Location & Vehicle Information -->
                                                <div class="row mb-2 g-2">
                                                    <div class="col-md-6">
                                                        <div class="bg-light rounded p-2 h-100">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                    <i class="ri-map-pin-line text-white" style="font-size: 0.8rem;"></i>
                                                                </div>
                                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Pickup Location</h6>
                                                            </div>
                                                            <div class="row g-1">
                                                                <div class="col-12">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Point</small>
                                                                    <div class="fw-medium text-truncate" style="font-size: 0.75rem;" title="{{ $booking['entrypickup'] ?? 'N/A' }}">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
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
                                                        <div class="bg-light rounded p-2 h-100" style="overflow: hidden;">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                                    <i class="ri-car-line text-white" style="font-size: 0.8rem;"></i>
                                                                </div>
                                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Vehicle Details</h6>
                                                            </div>
                                                            <div class="row g-1 mb-2">
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Vehicle</small>
                                                                    <div class="fw-medium text-truncate" style="font-size: 0.75rem;" title="{{ $booking['vehicles_name'] ?? 'N/A' }}">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Service</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['type'] ?? 'N/A' }}</div>
                                                                </div>
                                                            </div>
                                                            <!-- Compact Vehicle Image Display -->
                                                            <div class="d-flex justify-content-center align-items-center" style="min-height: 80px; width: 100%; overflow: hidden; position: relative;">
                                                                @if(isset($booking['image']) && !empty($booking['image']))
                                                                    <div class="position-relative" style="width: 80px; height: 80px; flex-shrink: 0; overflow: hidden;">
                                                                        <img src="{{ $booking['image'] }}" 
                                                                             alt="Vehicle Image" 
                                                                             class="rounded-circle shadow-sm" 
                                                                             style="width: 80px; height: 80px; object-fit: cover; object-position: center; border: 2px solid #667eea; display: block; margin: 0; padding: 0; background: #f8f9fa;"
                                                                             onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm\\' style=\\'width: 80px; height: 80px; border: 2px solid #e9ecef;\\'><i class=\\'ri-car-line text-muted\\' style=\\'font-size: 2rem;\\'></i></div>';">
                                                                    </div>
                                                                @else
                                                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px; border: 2px solid #e9ecef; flex-shrink: 0;">
                                                                        <i class="ri-car-line text-muted" style="font-size: 2rem;"></i>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Pricing Details -->
                                                <div class="bg-light rounded p-2 mb-2">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="ri-money-dollar-circle-line text-white" style="font-size: 0.8rem;"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Pricing Details</h6>
                                                    </div>
                                                    <div class="bg-white rounded p-1">
                                                        <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Total Price</small>
                                                        <div class="fw-bold text-success" style="font-size: 0.9rem;">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</div>
                                                    </div>
                                                </div>

                                                <!-- Special Requests -->
                                                @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                                <div class="bg-light rounded p-2 mb-2">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="ri-message-2-line text-white" style="font-size: 0.8rem;"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Special Requests</h6>
                                                    </div>
                                                    <div class="bg-white rounded p-1">
                                                        <p class="text-muted mb-0" style="font-size: 0.75rem;">{{ $booking['specialRequests'] }}</p>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <div class="bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="ri-time-line text-muted" style="font-size: 2rem;"></i>
                                </div>
                                <h5 class="text-muted mb-0" style="font-size: 1.1rem;">No hourly tour data available</h5>
                            </div>
                        @endif
                    </div>

                    <!-- Compact Footer -->
                    <div class="modal-footer border-0 p-1" style="background: #f8f9fa;">
                        <div class="d-flex gap-1 w-100 justify-content-end">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" onclick="closeServiceModal('travel_hourly', {{ $tour->tour_id }})" style="border-radius: 6px; font-size: 0.75rem;">
                                <i class="ri-close-line me-1" style="font-size: 0.7rem;"></i>Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Travel Point Details Modal -->
    @if(isset($svc['travel_point']) && $svc['travel_point'] > 0)
        <div class="modal fade" id="travel_pointDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="travel_pointModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                    <!-- Compact Header -->
                    <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="text-white">
                                <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                    <i class="ri-route-line me-1" style="font-size: 0.9rem;"></i>Local-Tour Point to Point Details
                                </h6>
                            </div>
                            <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('travel_point', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body p-2" style="background: #f8f9fa;">
                        @if(isset($serviceData['travel_point']) && count($serviceData['travel_point']) > 0)
                            @foreach($serviceData['travel_point'] as $index => $pointOrder)
                                @php
                                    $pointData = is_string($pointOrder->data) ? json_decode($pointOrder->data, true) : $pointOrder->data;
                                @endphp
                                
                                @if(is_array($pointData))
                                    @foreach($pointData as $bookingIndex => $booking)
                                        <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #667eea !important;">
                                            <!-- Compact Card Header -->
                                            <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);">
                                                <div class="row align-items-center g-1">
                                                    <div class="col-md-8">
                                                        <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                            <i class="ri-route-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['vehicles_name'] ?? 'Point to Point Service' }}
                                                        </h6>
                                                        <small class="text-white opacity-90" style="font-size: 0.7rem;">Local-Tour Point to Point {{ $index + 1 }} • {{ $booking['type'] ?? 'Standard' }} Service</small>
                                                    </div>
                                                    <div class="col-md-4 text-end">
                                                        <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                            SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="card-body p-2" style="background-color: #ffffff;">
                                                <!-- Service Schedule & Group Information -->
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
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Date</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') : 'N/A' }}</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Time</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'TBC' }}</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Adults</small>
                                                                    <div><span class="badge bg-info px-1 py-0" style="font-size: 0.65rem;">{{ $booking['adults'] ?? '0' }}</span></div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Children</small>
                                                                    <div><span class="badge bg-warning px-1 py-0" style="font-size: 0.65rem;">{{ $booking['children'] ?? '0' }}</span></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="bg-light rounded p-2 h-100">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                    <i class="ri-map-pin-line text-white" style="font-size: 0.8rem;"></i>
                                                                </div>
                                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Location Details</h6>
                                                            </div>
                                                            <div class="row g-1">
                                                                <div class="col-12">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Location</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrypickup'] ?? $booking['pickup_location'] ?? 'N/A' }}</div>
                                                                </div>
                                                                <div class="col-12">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Drop Location</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrydropoff'] ?? $booking['drop_location'] ?? 'N/A' }}</div>
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
                                                </div>

                                                <!-- Vehicle Image & Information -->
                                                <div class="row mb-2 g-2">
                                                    <div class="col-md-6">
                                                        <div class="bg-light rounded p-2 h-100" style="overflow: hidden;">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                                    <i class="ri-car-line text-white" style="font-size: 0.8rem;"></i>
                                                                </div>
                                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Vehicle Information</h6>
                                                            </div>
                                                            <div class="text-center">
                                                                @if(isset($booking['image']) && !empty($booking['image']))
                                                                    <div class="position-relative d-inline-block">
                                                                        <img src="{{ $booking['image'] }}" 
                                                                             alt="Vehicle" 
                                                                             class="img-fluid rounded shadow-sm" 
                                                                             style="max-height: 120px; width: 100%; object-fit: cover; border-radius: 8px;">
                                                                    </div>
                                                                @else
                                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 120px; border-radius: 8px;">
                                                                        <div class="text-center">
                                                                            <i class="ri-car-line text-muted mb-2" style="font-size: 2rem;"></i>
                                                                            <div class="text-muted" style="font-size: 0.7rem;">No Vehicle Image</div>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="bg-light rounded p-2 h-100">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                    <i class="ri-money-dollar-circle-line text-white" style="font-size: 0.8rem;"></i>
                                                                </div>
                                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Pricing & Customer</h6>
                                                            </div>
                                                            <div class="row g-1">
                                                                <div class="col-12">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Total Price</small>
                                                                    <div class="fw-bold text-success" style="font-size: 0.9rem;">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</div>
                                                                </div>
                                                                <div class="col-12">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Customer Name</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Email</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['email'] ?? 'N/A' }}</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Phone</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['phone'] ?? 'N/A' }}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Special Requests -->
                                                @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                                <div class="bg-light rounded p-2 mb-2">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="ri-message-2-line text-white" style="font-size: 0.8rem;"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Special Requests</h6>
                                                    </div>
                                                    <div class="bg-white rounded p-1">
                                                        <p class="text-muted mb-0" style="font-size: 0.75rem;">{{ $booking['specialRequests'] }}</p>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <div class="bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="ri-route-line text-muted" style="font-size: 2rem;"></i>
                                </div>
                                <h5 class="text-muted mb-0" style="font-size: 1.1rem;">No point to point transfer data available</h5>
                            </div>
                        @endif
                    </div>

                    <!-- Compact Footer -->
                    <div class="modal-footer border-0 p-1" style="background: #f8f9fa;">
                        <div class="d-flex gap-1 w-100 justify-content-end">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" onclick="closeServiceModal('travel_point', {{ $tour->tour_id }})" style="border-radius: 6px; font-size: 0.75rem;">
                                <i class="ri-close-line me-1" style="font-size: 0.7rem;"></i>Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Local Transport Details Modal -->
    @if(isset($svc['local_transport']) && $svc['local_transport'] > 0)
        <div class="modal fade" id="local_transportDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="local_transportModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                    <!-- Compact Header -->
                    <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="text-white">
                                <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                    <i class="ri-car-line me-1" style="font-size: 0.9rem;"></i>Local Transport Details
                                </h6>
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
                                        <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #667eea !important;">
                                            <!-- Compact Card Header -->
                                            <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);">
                                                <div class="row align-items-center g-1">
                                                    <div class="col-md-8">
                                                        <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                            <i class="ri-car-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['vehicles_name'] ?? 'Local Transport Service' }}
                                                        </h6>
                                                        <small class="text-white opacity-90" style="font-size: 0.7rem;">Local Transport {{ $index + 1 }} • {{ $booking['type'] ?? 'Standard' }}</small>
                                                    </div>
                                                    <div class="col-md-4 text-end">
                                                        <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                            SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="card-body p-2" style="background-color: #ffffff;">
                                                <!-- Service Schedule & Group Information -->
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
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Date</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') : 'N/A' }}</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Time</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'TBC' }}</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Type</small>
                                                                    <div><span class="badge bg-warning px-1 py-0" style="font-size: 0.65rem;">{{ $booking['type'] ?? 'Standard' }}</span></div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Transport</small>
                                                                    <div><span class="badge bg-info px-1 py-0" style="font-size: 0.65rem;">Local</span></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="bg-light rounded p-2 h-100">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                    <i class="ri-group-line text-white" style="font-size: 0.8rem;"></i>
                                                                </div>
                                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Group Information</h6>
                                                            </div>
                                                            <div class="row g-1 mb-1">
                                                                <div class="col-6 text-center">
                                                                    <div class="bg-white rounded p-1 border" style="border-color: #667eea !important;">
                                                                        <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['adults'] ?? 0 }}</div>
                                                                        <small class="text-muted" style="font-size: 0.55rem;">Adults</small>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6 text-center">
                                                                    <div class="bg-white rounded p-1 border" style="border-color: #667eea !important;">
                                                                        <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['children'] ?? 0 }}</div>
                                                                        <small class="text-muted" style="font-size: 0.55rem;">Children</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="text-center">
                                                                <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 0.7rem; padding: 2px 4px;">
                                                                    Total: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} Guests
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Route Information -->
                                                <div class="bg-light rounded p-2 mb-2">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="ri-route-line text-white" style="font-size: 0.8rem;"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Route Information</h6>
                                                    </div>
                                                    <div class="row g-1 mb-1">
                                                        <div class="col-md-6">
                                                            <div class="bg-white rounded p-1">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup</small>
                                                                <div class="fw-medium d-flex align-items-center" style="font-size: 0.75rem;">
                                                                    <i class="ri-map-pin-line text-success me-1" style="font-size: 0.7rem;"></i>
                                                                    <span class="text-truncate">{{ $booking['entrypickup'] ?? 'N/A' }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="bg-white rounded p-1">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Dropoff</small>
                                                                <div class="fw-medium d-flex align-items-center" style="font-size: 0.75rem;">
                                                                    <i class="ri-map-pin-2-line text-danger me-1" style="font-size: 0.7rem;"></i>
                                                                    <span class="text-truncate">{{ $booking['dropoffLocation'] ?? 'N/A' }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Compact Route Direction Visual -->
                                                    <div class="d-flex align-items-center justify-content-center p-1 bg-white rounded">
                                                        <span class="badge bg-success me-1" style="font-size: 0.65rem; padding: 2px 4px;">{{ Str::limit($booking['entrypickup'] ?? 'Pickup', 15) }}</span>
                                                        <i class="ri-arrow-right-line text-primary mx-1" style="font-size: 0.8rem;"></i>
                                                        <span class="badge bg-danger" style="font-size: 0.65rem; padding: 2px 4px;">{{ Str::limit($booking['dropoffLocation'] ?? 'Dropoff', 15) }}</span>
                                                    </div>
                                                </div>

                                                <!-- Vehicle & Location Information -->
                                                <div class="row mb-2 g-2">
                                                    <div class="col-md-6">
                                                        <div class="bg-light rounded p-2 h-100" style="overflow: hidden;">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                                    <i class="ri-car-line text-white" style="font-size: 0.8rem;"></i>
                                                                </div>
                                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Vehicle Details</h6>
                                                            </div>
                                                            <div class="row g-1 mb-2">
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Vehicle</small>
                                                                    <div class="fw-medium text-truncate" style="font-size: 0.75rem;" title="{{ $booking['vehicles_name'] ?? 'N/A' }}">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Service</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['type'] ?? 'N/A' }}</div>
                                                                </div>
                                                            </div>
                                                            <!-- Compact Vehicle Image Display -->
                                                            <div class="d-flex justify-content-center align-items-center" style="min-height: 80px; width: 100%; overflow: hidden; position: relative;">
                                                                @if(isset($booking['image']) && !empty($booking['image']))
                                                                    <div class="position-relative" style="width: 80px; height: 80px; flex-shrink: 0; overflow: hidden;">
                                                                        <img src="{{ $booking['image'] }}" 
                                                                             alt="Vehicle Image" 
                                                                             class="rounded-circle shadow-sm" 
                                                                             style="width: 80px; height: 80px; object-fit: cover; object-position: center; border: 2px solid #667eea; display: block; margin: 0; padding: 0; background: #f8f9fa;"
                                                                             onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm\\' style=\\'width: 80px; height: 80px; border: 2px solid #e9ecef;\\'><i class=\\'ri-car-line text-muted\\' style=\\'font-size: 2rem;\\'></i></div>';">
                                                                    </div>
                                                                @else
                                                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px; border: 2px solid #e9ecef; flex-shrink: 0;">
                                                                        <i class="ri-car-line text-muted" style="font-size: 2rem;"></i>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="bg-light rounded p-2 h-100">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                    <i class="ri-map-pin-line text-white" style="font-size: 0.8rem;"></i>
                                                                </div>
                                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Location Information</h6>
                                                            </div>
                                                            <div class="row g-1 mb-1">
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">City</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['city'] ?? 'N/A' }}</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Country</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['country'] ?? 'N/A' }}</div>
                                                                </div>
                                                            </div>
                                                            <!-- Compact Pricing Details -->
                                                            <div class="bg-white rounded p-1 mt-1">
                                                                <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Total Price</small>
                                                                <div class="fw-bold text-success" style="font-size: 0.9rem;">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</div>
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
                            <div class="text-center py-4">
                                <div class="bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="ri-car-line text-muted" style="font-size: 2rem;"></i>
                                </div>
                                <h5 class="text-muted mb-0" style="font-size: 1.1rem;">No local transport data available</h5>
                            </div>
                        @endif
                    </div>

                    <!-- Compact Footer -->
                    <div class="modal-footer border-0 p-1" style="background: #f8f9fa;">
                        <div class="d-flex gap-1 w-100 justify-content-end">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" onclick="closeServiceModal('local_transport', {{ $tour->tour_id }})" style="border-radius: 6px; font-size: 0.75rem;">
                                <i class="ri-close-line me-1" style="font-size: 0.7rem;"></i>Close
                            </button>
                        </div>
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
        // Includes: base price + transfer price + guide price (for attractions)
        $tourTotalPrice = 0;
        $orders = \App\Models\Order::where('tour_id', $tour->tour_id)->whereNull('deleted_at')->get();
        foreach($orders as $order) {
            if($order->data) {
                $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
                if(is_array($orderData)) {
                    // Handle both single item and array of items
                    $items = isset($orderData[0]) && is_array($orderData[0]) ? $orderData : [$orderData];
                    foreach($items as $item) {
                        if(!is_array($item)) continue;
                        
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
        
        $enquiry = \App\Models\Enquiry::where('tour_id', $tour->tour_id)->where('status', 2)->first();
        $enquiry_amount = $enquiry->amount ?? 0;
        $frstenquiry = \App\Models\Enquiry::where('tour_id', $tour->tour_id)->first();
        $first_enquiry_amount = $frstenquiry->actual_amount ?? 0;
        $discountAmount = $frstenquiry ? ($frstenquiry->actual_amount - $enquiry_amount) : 0;
        
        // Calculate base amount before tax (round up if decimal > 0.5, round down if < 0.5)
        $baseAmount = round($tourTotalPrice) - $discountAmount;
        
        // Calculate tax amount using TaxHelper
        $persons = ($tour->adult ?? 0) + ($tour->child ?? 0);
        $days = \App\Helpers\TaxHelper::calculateDays($tour->check_in_time, $tour->check_out_time);
        
        // Debug: Log the taxes data for this tour
        \Log::info('Tour #' . $tour->tour_id . ' Taxes Data:', ['taxes' => $tour->taxes, 'persons' => $persons, 'days' => $days, 'baseAmount' => $baseAmount]);
        
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

    <!-- Payment Details Modal -->
    <style>
        @media (max-width: 768px) {
            #showPaymentModal{{ $tour->tour_id }} .modal-dialog {
                max-width: 98% !important;
                margin: 0.5rem auto !important;
            }
            #showPaymentModal{{ $tour->tour_id }} .modal-content {
                height: 90vh !important;
            }
            #showPaymentModal{{ $tour->tour_id }} .table-responsive {
                max-height: 300px !important;
            }
        }
        
        #showPaymentModal{{ $tour->tour_id }} .table th,
        #showPaymentModal{{ $tour->tour_id }} .table td {
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
            max-width: 150px;
        }
        
        #showPaymentModal{{ $tour->tour_id }} .table td[title] {
            cursor: help;
        }
    </style>
    <div class="modal fade" id="showPaymentModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="showPaymentModalLabel{{ $tour->tour_id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable" style="max-width: 95%; max-height: 90vh;">
            <div class="modal-content shadow-lg rounded" style="height: 85vh; min-height: 600px;">
                <div class="modal-header bg-primary text-white d-flex align-items-center justify-content-start" style="padding: 12px 20px; border-radius: 8px 8px 0 0; flex-shrink: 0;">
                    <h5 class="modal-title d-flex align-items-center" id="showPaymentModalLabel{{ $tour->tour_id }}" style="margin: 0; font-weight: bold; color: white; font-size: 1.1rem;">
                        <i class="fas fa-history me-2" style="color: #38ef7d; font-size: 1.2rem;"></i> 
                        <span style="color: white;">Payment Details for Tour #{{ $tour->tour_id }}</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                </div>
                <div class="modal-body p-3" style="overflow-y: auto; flex: 1;">
                    @if(!empty($paymentData))
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
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
                                        @if(auth()->user()->role_id == 36 || 33 || 37 || 38 || auth()->user()->role_id == 126 || auth()->user()->role_id == 127 || auth()->user()->role_id == 128 || auth()->user()->role_id == 129 || auth()->user()->role_id == 130 || auth()->user()->role_id == 131 || auth()->user()->role_id == 133 || auth()->user()->role_id == 134 || auth()->user()->role_id == 135 || auth()->user()->role_id == 136 || auth()->user()->role_id == 137 || auth()->user()->role_id == 138)
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
                                                            <i class="fas fa-clock me-1"></i>Pending
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary text-white" style="font-size: 0.7rem;">Unknown</span>
                                                @endif
                                            </td>
                                            @php
                                                $financeRoles = [36, 33, 37, 38, 126, 127, 128, 129, 130, 131, 133, 134, 135, 136, 137, 138];
                                            @endphp
                                            @if(in_array(auth()->user()->role_id, $financeRoles))
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
                        
                        <!-- Payment Summary -->
                        <div class="row mt-3 g-2">
                            <div class="col-md-3">
                                <div class="card bg-secondary text-white" style="border-radius: 10px;">
                                    <div class="card-body text-center py-2 px-3">
                                        <h6 class="card-title mb-1" style="font-size: 0.85rem; font-weight: 600;">Base Amount</h6>
                                        <h5 class="mb-0" style="font-size: 1.2rem; font-weight: bold;">{{ number_format($baseAmount, 2) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white" style="border-radius: 10px;">
                                    <div class="card-body text-center py-2 px-3">
                                        <h6 class="card-title mb-1" style="font-size: 0.85rem; font-weight: 600;" 
                                            @if(!empty($taxBreakdown))
                                                title="{{ \App\Helpers\TaxHelper::formatTaxBreakdown($taxBreakdown) }}"
                                            @endif>
                                            Tax @if(!empty($taxBreakdown))({{ count($taxBreakdown) }} taxes)@endif
                                        </h6>
                                        <h5 class="mb-0" style="font-size: 1.2rem; font-weight: bold;">{{ number_format($taxAmount, 2) }}</h5>
                                        @if(!empty($taxBreakdown) && count($taxBreakdown) > 0)
                                            <small style="font-size: 0.65rem; opacity: 0.9;">
                                                @foreach($taxBreakdown as $taxName => $taxVal)
                                                    {{ $taxName }}: {{ number_format($taxVal, 2) }}@if(!$loop->last), @endif
                                                @endforeach
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-primary text-white" style="border-radius: 10px;">
                                    <div class="card-body text-center py-2 px-3">
                                        <h6 class="card-title mb-1" style="font-size: 0.85rem; font-weight: 600;">Total</h6>
                                        <h5 class="mb-0" style="font-size: 1.2rem; font-weight: bold;">{{ number_format($finalAmount, 2) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-success text-white" style="border-radius: 10px;">
                                    <div class="card-body text-center py-2 px-3">
                                        <h6 class="card-title mb-1" style="font-size: 0.85rem; font-weight: 600;">Paid</h6>
                                        <h5 class="mb-0" style="font-size: 1.2rem; font-weight: bold;">{{ number_format($totalPaid, 2) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-warning text-white" style="border-radius: 10px;">
                                    <div class="card-body text-center py-2 px-3">
                                        <h6 class="card-title mb-1" style="font-size: 0.85rem; font-weight: 600;">Remaining</h6>
                                        <h5 class="mb-0" style="font-size: 1.2rem; font-weight: bold;">{{ number_format($remainingAmount, 2) }}</h5>
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
                                        <div class="fw-bold text-success">- {{ number_format(round($discountAmount), 2) }} SGD</div>
                                    </div>
                                </div>
                                <hr class="my-2">
                                @endif
                                <div class="row text-center mb-2">
                                    <div class="col-4">
                                        <small class="text-muted">Base Amount</small>
                                        <div class="fw-bold text-dark">{{ number_format(round($baseAmount), 2) }} SGD</div>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted" 
                                            @if(!empty($taxBreakdown))
                                                title="{{ \App\Helpers\TaxHelper::formatTaxBreakdown($taxBreakdown) }}"
                                            @endif>
                                            Tax @if(!empty($taxBreakdown))({{ count($taxBreakdown) }})@endif
                                        </small>
                                        <div class="fw-bold text-warning">{{ number_format(round($taxAmount), 2) }} SGD</div>
                                        @if(!empty($taxBreakdown) && count($taxBreakdown) > 0)
                                            <div style="font-size: 0.7rem; margin-top: 2px;">
                                                @foreach($taxBreakdown as $taxName => $taxVal)
                                                    <div>{{ $taxName }}: {{ number_format(round($taxVal), 2) }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted">Total Amount</small>
                                        <div class="fw-bold text-primary">{{ number_format(round($finalAmount), 2) }} SGD</div>
                                    </div>
                                </div>
                                <hr class="my-2">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <small class="text-muted">Paid Amount</small>
                                        <div class="fw-bold text-success">{{ number_format(round($totalPaid), 2) }} SGD</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Remaining</small>
                                        <div class="fw-bold text-danger">{{ number_format(round($remainingAmount), 2) }} SGD</div>
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
                                    Amount in SGD: {{ number_format(round($remainingAmount), 2) }}
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

// generateInvoice removed (invoice generation is handled via backend routes/buttons)

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
    const startDateValue = document.getElementById('startDateFilter')?.value || '';
    const endDateValue = document.getElementById('endDateFilter')?.value || '';
    
    const rows = document.querySelectorAll('#toursTable tbody tr');
    if (typeof table !== 'undefined' && table && table.rows) {
        table.rows('.dt-hasChild').every(function() {
            if (this.child.isShown()) this.child.hide();
            $(this.node()).removeClass('dt-hasChild');
        });
    }
    rows.forEach(row => {
        if (row.cells.length === 1) return; // Skip empty state row
        
        const tourDetails = row.cells[1]?.textContent.toLowerCase() || '';
        const destination = row.cells[3]?.querySelector('.fw-medium')?.textContent || '';
        const agent = row.cells[5]?.querySelector('.fw-medium')?.textContent || '';
        const createdBy = row.cells[6]?.querySelector('.fw-medium')?.textContent || '';
        const status = row.cells[10]?.querySelector('.badge')?.textContent.toLowerCase() || '';
        const travelDates = row.cells[8]?.textContent.toLowerCase() || '';
        const paymentBadges = row.cells[9]?.querySelectorAll('.badge') || [];
        const createdAt = row.getAttribute('data-created-at');
        const updatedAt = row.getAttribute('data-updated-at');
        
        let show = true;
        
        // Date filtering (check both created_at and updated_at)
        if ((startDateValue || endDateValue) && (createdAt || updatedAt)) {
            const startDate = startDateValue ? new Date(startDateValue + 'T00:00:00') : null;
            const endDate = endDateValue ? new Date(endDateValue + 'T23:59:59') : null;
            let dateInRange = false;
            
            // Check created_at if available
            if (createdAt) {
                const createdDate = new Date(createdAt + 'T00:00:00');
                if ((!startDate || createdDate >= startDate) && (!endDate || createdDate <= endDate)) {
                    dateInRange = true;
                }
            }
            
            // Check updated_at if available and created_at didn't match
            if (!dateInRange && updatedAt) {
                const updatedDate = new Date(updatedAt + 'T00:00:00');
                if ((!startDate || updatedDate >= startDate) && (!endDate || updatedDate <= endDate)) {
                    dateInRange = true;
                }
            }
            
            if (!dateInRange) {
                show = false;
            }
        } else if (startDateValue || endDateValue) {
            // If dates are selected but row lacks timestamps, hide it
            show = false;
        }
        
        if (searchTerm && 
            !tourDetails.includes(searchTerm) && 
            !destination.toLowerCase().includes(searchTerm) && 
            !agent.toLowerCase().includes(searchTerm) &&
            !createdBy.toLowerCase().includes(searchTerm)) {
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

    if (startDateValue || endDateValue) {
        const start = startDateValue ? new Date(startDateValue) : null;
        const end = endDateValue ? new Date(endDateValue) : null;
        
        // Format the date range label
        let label = '';
        if (start && end) {
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
        } else if (start) {
            label = `From ${start.toLocaleString('default', { month: 'short' })} ${start.getDate()}, ${start.getFullYear()}`;
        } else if (end) {
            label = `Up to ${end.toLocaleString('default', { month: 'short' })} ${end.getDate()}, ${end.getFullYear()}`;
        }

        if (!label) {
            label = 'Custom Range';
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
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const paymentFilter = document.getElementById('paymentFilter');
    const destinationSelect = document.getElementById('destinationFilter');
    const agentSelect = document.getElementById('agentFilter');
    const timeFilter = document.getElementById('timeFilter');
    const startDateInput = document.getElementById('startDateFilter');
    const endDateInput = document.getElementById('endDateFilter');
    const today = new Date().toISOString().split('T')[0];

    if (searchInput) searchInput.value = '';
    if (statusFilter) statusFilter.value = '';
    if (paymentFilter) paymentFilter.value = '';
    
    // Reset Select2 dropdowns properly
    if (destinationSelect && $('#destinationFilter').hasClass('select2-hidden-accessible')) {
        $('#destinationFilter').val(null).trigger('change');
    } else if (destinationSelect) {
        destinationSelect.value = '';
    }
    
    if (agentSelect && $('#agentFilter').hasClass('select2-hidden-accessible')) {
        $('#agentFilter').val(null).trigger('change');
    } else if (agentSelect) {
        agentSelect.value = '';
    }
    
    if (timeFilter) timeFilter.value = '';
    
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

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const paymentFilter = document.getElementById('paymentFilter');
    const destinationFilter = document.getElementById('destinationFilter');
    const agentFilter = document.getElementById('agentFilter');
    const timeFilter = document.getElementById('timeFilter');
    const startDateFilter = document.getElementById('startDateFilter');
    const endDateFilter = document.getElementById('endDateFilter');
    const today = new Date().toISOString().split('T')[0];
    
    // Add event listeners
    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (statusFilter) statusFilter.addEventListener('change', filterTable);
    if (paymentFilter) paymentFilter.addEventListener('change', filterTable);
    // Note: destinationFilter and agentFilter event listeners are handled by Select2 initialization
    // They will trigger filterTable when changed via Select2's change event
    if (timeFilter) timeFilter.addEventListener('change', filterTable);
    if (startDateFilter) {
        startDateFilter.setAttribute('max', today);
        startDateFilter.addEventListener('change', function() {
            if (this.value) {
                if (endDateFilter) {
                    if (endDateFilter.value && endDateFilter.value < this.value) {
                        endDateFilter.value = this.value;
                    }
                    endDateFilter.setAttribute('min', this.value);
                }
            } else if (endDateFilter) {
                endDateFilter.removeAttribute('min');
            }
            filterTable();
        });
    }
    if (endDateFilter) {
        endDateFilter.setAttribute('max', today);
        endDateFilter.addEventListener('change', function() {
            if (this.value) {
                if (startDateFilter) {
                    if (startDateFilter.value && startDateFilter.value > this.value) {
                        startDateFilter.value = this.value;
                    }
                    startDateFilter.setAttribute('max', this.value);
                }
            } else if (startDateFilter) {
                startDateFilter.setAttribute('max', today);
            }
            filterTable();
        });
    }
    
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
                    targets: [6], // Actions column (index 6)
                    orderable: false,
                    searchable: false
                },
                {
                    targets: [3], // Services column (index 3)
                    orderable: false
                },
                {
                    targets: [4, 5], // Payment Details (merged) and Status columns (index 4, 5)
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

    // Global tooltip for table headers and action/service icons - viewport-relative positioning
    $(document).ready(function() {
        var $globalTooltip = $('#service-icon-global-tooltip');
        if (!$globalTooltip.length) {
            $globalTooltip = $('<div id="service-icon-global-tooltip" aria-hidden="true"></div>').appendTo('body');
        }
        // Table header: tooltip above the header (centered)
        $(document).on('mouseenter', '#toursTable thead .th-tooltip', function() {
            var txt = $(this).attr('data-tooltip') || $(this).attr('title') || $(this).text();
            if (!txt) return;
            var rect = this.getBoundingClientRect();
            $globalTooltip.css({
                display: 'block',
                left: (rect.left + rect.width / 2) + 'px',
                top: (rect.top - 6) + 'px',
                transform: 'translate(-50%, -100%)'
            }).text(txt);
        });
        $(document).on('mouseleave', '#toursTable thead .th-tooltip', function() {
            $globalTooltip.hide();
        });
        // Action icons: tooltip above (centered)
        $(document).on('mouseenter', '#toursTable .action-icon-badge', function() {
            var txt = $(this).attr('data-tooltip') || $(this).attr('title') || '';
            if (!txt) return;
            var rect = this.getBoundingClientRect();
            $globalTooltip.css({
                display: 'block',
                left: (rect.left + rect.width / 2) + 'px',
                top: (rect.top - 6) + 'px',
                transform: 'translate(-50%, -100%)'
            }).text(txt);
        });
        $(document).on('mouseleave', '#toursTable .action-icon-badge', function() {
            $globalTooltip.hide();
        });
        // Service icons: tooltip above (centered)
        $(document).on('mouseenter', '#toursTable .service-icon-wrapper', function() {
            var text = $(this).attr('data-tooltip') || $(this).find('.service-icon-tooltip').text();
            if (!text) return;
            var rect = this.getBoundingClientRect();
            $globalTooltip.css({
                display: 'block',
                left: (rect.left + rect.width / 2) + 'px',
                top: (rect.top - 6) + 'px',
                transform: 'translate(-50%, -100%)'
            }).text(text);
        });
        $(document).on('mouseleave', '#toursTable .service-icon-wrapper', function() {
            $globalTooltip.hide();
        });
    });
</script>
@endsection

@extends('layouts.datatablejs')

