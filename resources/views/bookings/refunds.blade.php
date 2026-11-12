@extends('layouts.layout')
@section('title', 'Refunds')
@extends('layouts.datatablecss')

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

    /* Compact table styles (shared with new-enquiries / follow-ups) */
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

    /* Page background - match cancelled */
    .refunds-bookings-page { background-color: #f8f9fa !important; min-height: 100vh; padding-bottom: 2rem !important; }
    .refunds-bookings-page .card { background-color: #fff; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }

    /* Compact table styles (aligned with cancelled) */
    #toursTable {
        font-size: 0.875rem;
        table-layout: auto;
        width: 100% !important;
        margin-bottom: 0;
        background-color: #fff;
    }
    .dataTables_wrapper .dataTables_scroll .dataTables_scrollBody #toursTable,
    .dataTables_wrapper #toursTable { width: 100% !important; table-layout: auto; }
    #toursTable th.col-refund-status,
    #toursTable td.col-refund-status {
        width: auto !important;
        min-width: max-content;
        max-width: none !important;
        overflow: visible;
    }
    #toursTable .col-refund-status .refund-status-stack {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.4rem;
    }
    #toursTable .col-refund-status .refund-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.72rem;
        font-weight: 600;
        line-height: 1.25;
        padding: 0.28rem 0.55rem;
        border-radius: 6px;
        border: 1px solid transparent;
    }
    #toursTable .col-refund-status .refund-status-badge.is-completed {
        color: #166534;
        background: #f0fdf4;
        border-color: #bbf7d0;
    }
    #toursTable .col-refund-status .refund-status-badge.is-pending {
        color: #9a3412;
        background: #fff7ed;
        border-color: #fed7aa;
    }
    #toursTable .action-icon-badge.is-waiting-admin {
        background: #fef3c7 !important;
        border-color: #fcd34d !important;
        cursor: not-allowed;
        opacity: 0.9;
    }
    #toursTable .action-icon-badge.is-waiting-admin i { color: #b45309 !important; }
    #toursTable .action-icon-badge.is-supplier-cancel-blocked {
        background: #fef2f2 !important;
        border-color: #fca5a5 !important;
        cursor: not-allowed;
        opacity: 0.95;
    }
    #toursTable .action-icon-badge.is-supplier-cancel-blocked i { color: #b91c1c !important; }
    #toursTable .action-icon-badge.is-admin-ready {
        background: #dbeafe !important;
        border-color: #93c5fd !important;
    }
    #toursTable .action-icon-badge.is-admin-ready i { color: #1d4ed8 !important; }
    #toursTable .action-icon-badge.is-admin-ready:hover { background: #bfdbfe !important; }
    #toursTable .refund-money-summary {
        display: inline-grid;
        grid-template-columns: max-content max-content;
        column-gap: 0.85rem;
        row-gap: 0.12rem;
        align-items: center;
        font-size: 0.82rem;
        line-height: 1.45;
        font-weight: 600;
        color: #334155;
        white-space: nowrap;
    }
    #toursTable .refund-money-summary .rms-paid {
        color: #15803d;
    }
    #toursTable .refund-money-summary .rms-due {
        color: #b91c1c;
    }
    #toursTable .refund-money-summary .rms-refunded {
        color: #166534;
    }
    #toursTable .refund-money-summary .rms-dmc-markup {
        color: #b45309;
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
    /* Tour Details, Agent, Actions, Created, Auto Cancel */
    #toursTable td:nth-child(2) { min-height: 72px; vertical-align: top; }
    #toursTable td.col-agent .agent-name-line { font-weight: 600; font-size: 0.875rem; color: #0d6efd; display: flex; align-items: center; gap: 0.35rem; }
    #toursTable td.col-agent .agent-company-line { font-size: 0.75rem; color: #6c757d; display: flex; align-items: center; gap: 0.35rem; margin-top: 0.2rem; }
    #toursTable td.col-agent .agent-empty { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; color: #6c757d; font-style: italic; }
    #toursTable td.col-agent .party-line { display: flex; align-items: flex-start; gap: 0.35rem; line-height: 1.35; font-size: 0.82rem; }
    #toursTable td.col-agent .party-line + .party-line { margin-top: 0.35rem; }
    #toursTable td.col-agent .party-label { flex: 0 0 auto; min-width: 3.2rem; color: #64748b; font-weight: 600; }
    #toursTable td.col-agent .party-value { color: #0f172a; font-weight: 600; word-break: break-word; }
    #toursTable td.col-agent .party-value.is-dmc { color: #0f766e; }
    #toursTable td.col-agent .party-value.is-agent { color: #0d6efd; }
    #toursTable td.col-agent .party-value.is-empty { color: #94a3b8; font-weight: 500; font-style: italic; }
    #toursTable td.col-created { white-space: normal; word-wrap: break-word; overflow-wrap: break-word; }
    #toursTable td.col-created .created-by-line, #toursTable td.col-created .created-at-line { display: flex; align-items: flex-start; gap: 0.35rem; line-height: 1.35; }
    #toursTable td.col-actions { min-height: 72px; min-width: 160px; overflow: visible; }
    #toursTable .actions-icons-wrap {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        row-gap: 0.5rem;
        column-gap: 0.5rem;
        align-items: center;
        justify-items: center;
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
        text-decoration: none;
        color: inherit;
    }
    #toursTable .action-icon-badge:hover { background: #f1f5f9; border-color: #cbd5e1; }
    #toursTable tr.refund-row-warning > td { background-color: #fff7ed !important; }
    #toursTable .action-icon-badge.is-refund-initiated,
    #toursTable .action-icon-badge.is-refunded {
        border: none !important;
        --action-color: #fff;
    }
    #toursTable .action-icon-badge.is-refund-initiated {
        background: #f59e0b !important;
    }
    #toursTable .action-icon-badge.is-refund-initiated:hover { background: #d97706 !important; }
    #toursTable .action-icon-badge.is-refunded {
        background: #15803d !important;
    }
    #toursTable .action-icon-badge.is-refunded:hover { background: #166534 !important; }
    #toursTable .action-icon-badge i { font-size: 1rem; color: var(--action-color, #475569); }
    #toursTable .action-icon-badge.is-refund-initiated i,
    #toursTable .action-icon-badge.is-refunded i { color: #fff !important; }
    #toursTable button.action-icon-badge { border: 1px solid #e2e8f0; background: #f8fafc; }
    #toursTable td.col-auto-cancel { font-size: 0.7rem; }
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

@section('content')
<div class="container-xxl flex-grow-1 container-p-y refunds-bookings-page">
    @include('bookings.partials.booking-type-tabs', [
        'type' => 'tours',
        'toursUrl' => route('bookings.refunds'),
        'packagesUrl' => route('package-bookings.refunds'),
    ])
    <!-- Header -->
    <!-- Compact Header + Stats Bar -->
    <div class="new-enq-header-bar p-3 mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <h4 class="fw-bold mb-0" style="font-size: 1.25rem;">
                    <i class="ri-money-dollar-circle-line me-2 text-success"></i>
                    <span class="text-muted fw-light">Bookings /</span> Refunds
                </h4>
                <span class="text-muted d-none d-md-inline" style="font-size: 0.875rem;">Manage refunds for cancelled definite bookings</span>
                <span class="badge bg-light text-success border border-success border-opacity-25 px-2 py-1" style="font-size: 0.75rem;">
                    <i class="ri-money-dollar-circle-line me-1"></i><span id="rangeCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</span> <span id="rangeLabel">{{ date('F') }}</span>
                </span>
            </div>
            <div class="row g-2 new-enq-stats-grid flex-grow-1">
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-success rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-money-dollar-circle-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statRefundsCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</span><span class="stat-label text-muted" id="statRefundsLabel">{{ date('F') }} Refunds</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-info rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-funds-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statTotalCount">{{ $tours->count() }}</span><span class="stat-label text-muted" id="statTotalLabel">Total Refunds</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-danger rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-time-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statPendingCount">{{ $tours->where('tour_status', 'Refund - Pending')->count() }}</span><span class="stat-label text-muted" id="statPendingLabel">Pending Refunds</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-success rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-check-circle-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statCompletedCount">{{ $tours->where('tour_status', 'Refunded')->count() }}</span><span class="stat-label text-muted" id="statCompletedLabel">Completed Refunds</span></div>
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
                    <label class="form-label mb-0 small text-muted">Refund Status</label>
                    <select class="form-select form-select-sm" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="refunded">Refunded</option>
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
            <h5 class="mb-0">Refunds List</h5>
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
            @if($tours->count() > 0)
            <div class="table-responsive">
                <table class="datatables-basic table table-bordered" id="toursTable">
                    @php
                        $showDmcColumn = in_array((int) (auth()->user()->role_id ?? 0), [1, 2, 3, 4], true);
                    @endphp
                    <colgroup>
                        <col style="width: 2%">
                        <col style="width: 16%">
                        <col style="width: {{ $showDmcColumn ? '14%' : '10%' }}">
                        <col class="col-refund-status">
                        <col style="width: 12%">
                        <col style="width: 10%">
                        <col style="width: 14%">
                        <col style="width: 8%">
                    </colgroup>
                    <thead class="table-light">
                        <tr>
                            <th class="th-tooltip" data-tooltip="#">#</th>
                            <th class="th-tooltip" data-tooltip="Tour Details">Tour Details</th>
                            <th class="th-tooltip" data-tooltip="{{ $showDmcColumn ? 'DMC / Agent' : 'Agent' }}">{{ $showDmcColumn ? 'DMC / Agent' : 'Agent' }}</th>
                            <th class="th-tooltip col-refund-status" data-tooltip="Refund Status">Refund Status</th>
                            <th class="th-tooltip" data-tooltip="Cancelled Date">Cancelled Date</th>
                            <!-- <th class="th-tooltip" data-tooltip="Refund Services">Cancel Services</th> -->
                            <th class="th-tooltip" data-tooltip="Actions">Actions</th>
                            <th class="th-tooltip" data-tooltip="Created">Created</th>
                            <th class="th-tooltip" data-tooltip="Auto Cancel">Auto Cancel</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tours as $key => $tour)
                        @php
                            $rowRefundOrders = collect($tour->booking ?? [])->filter(function ($order) use ($showDmcColumn) {
                                if ((int) ($order->is_refund ?? 0) !== 1) {
                                    return false;
                                }
                                // TravClicks: online services only
                                if ($showDmcColumn) {
                                    return strtolower(trim((string) ($order->order_type ?? ''))) === 'online';
                                }
                                return true;
                            });
                            // TravClicks tracks wallet credit to DMC; DMC tracks refund to agent.
                            $orderRefundDone = function ($order) use ($showDmcColumn) {
                                if ($showDmcColumn) {
                                    return (bool) ($order->travclicks_refunded ?? false);
                                }
                                return (bool) ($order->refunded ?? false);
                            };
                            $rowHasInitiatedRefund = ! $showDmcColumn && $rowRefundOrders->contains(function ($order) {
                                return (int) ($order->is_verify ?? 0) === 2 && ! ((bool) ($order->refunded ?? false));
                            });
                            $rowWaitingAdminApproval = ! $showDmcColumn && $rowRefundOrders->contains(function ($order) {
                                return strtolower(trim((string) ($order->order_type ?? ''))) === 'online'
                                    && ! ((bool) ($order->travclicks_refunded ?? false));
                            });
                            $rowAdminRefundedReady = ! $showDmcColumn && $rowRefundOrders->contains(function ($order) {
                                return strtolower(trim((string) ($order->order_type ?? ''))) === 'online'
                                    && (bool) ($order->travclicks_refunded ?? false)
                                    && ! ((bool) ($order->refunded ?? false));
                            });
                            $isTourCancel = in_array($tour->tour_status, ['Refund - Pending', 'Refunded'], true);
                            if ($isTourCancel && ! $showDmcColumn) {
                                // Full tour cancel (DMC view): status alone decides pending vs refunded.
                                $hasPendingRefund = $tour->tour_status === 'Refund - Pending';
                                $hasRefunded = $tour->tour_status === 'Refunded';
                            } else {
                                // TravClicks / service cancel: unfinished online (or DMC service) refunds keep row Pending.
                                $hasPendingRefund = $rowRefundOrders->contains(function ($order) use ($orderRefundDone) {
                                    return ! $orderRefundDone($order);
                                });
                                $hasCompletedOrderRefund = $rowRefundOrders->contains(function ($order) use ($orderRefundDone) {
                                    return $orderRefundDone($order);
                                });
                                $hasRefunded = $hasCompletedOrderRefund && ! $hasPendingRefund;
                            }
                        @endphp
                        <tr 
                            class="{{ ($rowHasInitiatedRefund || $rowWaitingAdminApproval || $rowAdminRefundedReady) ? 'refund-row-warning' : ($tour->tour_status === 'Refund - Pending' ? 'table-danger' : 'table-success') }}"
                            data-created-at="{{ optional($tour->destination_created_at ?? $tour->created_at)->toDateString() }}"
                            data-updated-at="{{ optional($tour->updated_at)->toDateString() }}"
                            data-adult="{{ $tour->adult ?? 0 }}"
                            data-child="{{ $tour->child ?? 0 }}"
                            data-tour-status="{{ $tour->tour_status }}"
                            data-has-pending="{{ $hasPendingRefund ? '1' : '0' }}"
                            data-has-refunded="{{ $hasRefunded ? '1' : '0' }}"
                            data-waiting-admin="{{ $rowWaitingAdminApproval ? '1' : '0' }}"
                            data-admin-ready="{{ $rowAdminRefundedReady ? '1' : '0' }}"
                            data-agent="{{ $tour->agent_name }}"
                            data-dmc="{{ $tour->dmc_company_name ?: ($tour->dmc_name ?? '') }}"
                            data-destination="{{ $tour->destination }}"
                        >
                            <td>{{ $key + 1 }}</td>
                            <td class="align-top">
                                <div class="d-flex flex-column gap-1">
                                    <strong class="text-success">{{ $tour->display_id }}</strong>
                                    @if($tour->reference_id)
                                        <small class="text-dark">Ref: {{ $tour->reference_id }}</small>
                                    @endif
                                    <small class="text-muted">Tour ID: #{{ $tour->tour_id }}</small>

                                   

                                    @if($tour->multi_enq_id)
                                        <small class="text-info">Multi: {{ $tour->multi_enq_id }}</small>
                                    @endif
                                    @include('bookings.partials.tour-detail-badges', ['tour' => $tour])
                                    <span class="fw-medium mt-1"><i class="ri-map-pin-line me-1"></i>{{ $tour->destination ?? 'N/A' }}</span>
                                    <div class="d-flex align-items-center gap-2 flex-nowrap">
                                        <span title="Adults"><i class="ri-user-line text-success"></i> {{ $tour->adult ?? 0 }}</span>
                                        <span title="Children"><i class="ri-user-smile-line text-warning"></i> {{ $tour->child ?? 0 }}</span>
                                        <span title="Infants"><i class="ri-user-heart-line text-info"></i> {{ $tour->infant ?? 0 }}</span>
                                    </div>
                                    @if($tour->check_in_time || $tour->check_out_time)
                                        <small>
                                            @if($tour->check_in_time)<span><strong>In:</strong> {{ \Carbon\Carbon::parse($tour->check_in_time)->format('M d, Y') }}</span><br>
                                            @endif
                                            @if($tour->check_out_time)<span class=""><strong>Out:</strong> {{ \Carbon\Carbon::parse($tour->check_out_time)->format('M d, Y') }}</span>@endif
                                        </small>
                                    @endif
                                    @php
                                        $mainGuest = $tour->mainguest;
                                        if (is_string($mainGuest)) {
                                            $mainGuest = json_decode($mainGuest, true) ?: [];
                                        }

                                        $leadGuestName = null;
                                        if (is_array($mainGuest)) {
                                            $salutation = trim($mainGuest['salutation'] ?? '');
                                            $fullName   = trim($mainGuest['full_name'] ?? '');
                                            $firstName  = trim($mainGuest['first_name'] ?? '');
                                            $lastName   = trim($mainGuest['last_name'] ?? '');

                                            if (!empty($fullName)) {
                                                $leadGuestName = trim($salutation . ' ' . $fullName);
                                            } else {
                                                $leadGuestName = trim($salutation . ' ' . $firstName . ' ' . $lastName);
                                            }
                                        }

                                        if (empty($leadGuestName) && !empty($tour->customer_name)) {
                                            $leadGuestName = $tour->customer_name;
                                        }
                                    @endphp

                                    @if(!empty($leadGuestName))
                                        @php
                                            $tourTypeLower = strtolower($tour->tour_type ?? '');
                                            $bgColor = $tourTypeLower === 'group' ? '#7c3aed' : '#059669';
                                            $textColor = '#ffffff';
                                        @endphp
                                        <small>
                                            <i class="ri-user-line me-1"></i>
                                            <span class="d-inline-block px-2 py-1 rounded" style="background: {{ $bgColor }}; color: {{ $textColor }}; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.3px;">
                                                {{ $leadGuestName }}
                                            </span>
                                        </small>
                                    @endif
                                </div>
                            </td>
                            <td class="col-agent">
                                @php
                                    $dmcLabel = trim((string) ($tour->dmc_company_name ?: ($tour->dmc_name ?? '')));
                                    $agentLabel = trim((string) ($tour->agent_name ?? ''));
                                    $agentCompany = trim((string) ($tour->agent_company_name ?? ''));
                                @endphp
                                <div class="d-flex flex-column">
                                    @if($showDmcColumn)
                                        <span class="party-line">
                                            <span class="party-label">DMC</span>
                                            <span class="party-value {{ $dmcLabel !== '' ? 'is-dmc' : 'is-empty' }}">{{ $dmcLabel !== '' ? $dmcLabel : 'N/A' }}</span>
                                        </span>
                                        <span class="party-line">
                                            <span class="party-label">Agent</span>
                                            <span class="party-value {{ $agentLabel !== '' ? 'is-agent' : 'is-empty' }}">{{ $agentLabel !== '' ? $agentLabel : 'N/A' }}</span>
                                        </span>
                                    @elseif($agentLabel !== '')
                                        <span class="agent-name-line"><i class="ri-user-line"></i><span>{{ $agentLabel }}</span></span>
                                        <span class="agent-company-line"><i class="ri-building-line"></i><span>{{ $agentCompany !== '' ? $agentCompany : 'N/A' }}</span></span>
                                    @else
                                        <span class="agent-empty"><i class="ri-user-unfollow-line"></i><span>No agent assigned</span></span>
                                    @endif
                                </div>
                            </td>
                            @php
                                // TravClicks: always use per-service online refund UI (same as order cancel).
                                // DMC: full-tour cancel badge when there are no service-level refund rows.
                                $useDmcTourCancelRefundUi = ! $showDmcColumn
                                    && in_array($tour->tour_status, ['Refund - Pending', 'Refunded'], true)
                                    && $rowRefundOrders->isEmpty();
                            @endphp
                            @if($useDmcTourCancelRefundUi)
                            <td class="col-refund-status align-top">
                                <div class="refund-status-stack">
                                @if($tour->tour_status === 'Refund - Pending')
                                    <span class="refund-status-badge is-pending text-start">
                                        <i class="ri-time-line"></i>
                                        <span>
                                            <span class="d-block">Cancel Tour</span>
                                            <span class="d-block">Refund Pending</span>
                                        </span>
                                    </span>
                                @else
                                    <span class="refund-status-badge is-completed">
                                        <i class="ri-check-circle-line"></i>
                                        Refund completed
                                    </span>
                                @endif
                                @include('bookings.partials.refund-money-summary', [
                                    'tour' => $tour,
                                    'currency' => $currency ?? '',
                                    'isTravclicksView' => $showDmcColumn,
                                ])
                                </div>
                            </td>
                            @else
                            <td class="col-refund-status align-top">
                                @php
                                    $refundServiceIcons = [
                                        'hotel' => ['icon' => 'ri-hotel-bed-line', 'label' => 'Hotel'],
                                        'attraction' => ['icon' => 'ri-camera-line', 'label' => 'Attraction'],
                                        'restaurant' => ['icon' => 'ri-restaurant-2-line', 'label' => 'Restaurant'],
                                        'guide' => ['icon' => 'ri-user-voice-line', 'label' => 'Guide'],
                                        'entry_port' => ['icon' => 'ri-flight-land-line', 'label' => 'Arrival Transfer'],
                                        'exit_port' => ['icon' => 'ri-flight-takeoff-line', 'label' => 'Departure Transfer'],
                                        'travel_hourly' => ['icon' => 'ri-time-line', 'label' => 'Hourly Transfer'],
                                        'travel_point' => ['icon' => 'ri-route-line', 'label' => 'Point To Point'],
                                        'local_transport' => ['icon' => 'ri-car-line', 'label' => 'Local Transport'],
                                        'miscellaneous' => ['icon' => 'ri-list-check-2', 'label' => 'Miscellaneous'],
                                    ];
                                    $refundOrders = collect($tour->booking ?? [])
                                        ->filter(function ($order) use ($showDmcColumn) {
                                            if ((int) ($order->is_refund ?? 0) !== 1) {
                                                return false;
                                            }
                                            // TravClicks: online cancelled services only (never full-tour / offline)
                                            if ($showDmcColumn) {
                                                return strtolower(trim((string) ($order->order_type ?? ''))) === 'online';
                                            }
                                            return true;
                                        })
                                        ->values();

                                    $isOnlineRefundOrder = static function ($order): bool {
                                        return strtolower(trim((string) ($order->order_type ?? ''))) === 'online';
                                    };
                                    $hasPendingOrderRefund = $refundOrders->contains(function ($order) use ($showDmcColumn) {
                                        if ($showDmcColumn) {
                                            return ! ((bool) ($order->travclicks_refunded ?? false));
                                        }
                                        return ! ((bool) ($order->refunded ?? false));
                                    });
                                    $serviceRefundCompleted = $refundOrders->isNotEmpty() && ! $hasPendingOrderRefund;
                                @endphp
                                <div class="refund-status-stack">
                                @if($serviceRefundCompleted)
                                    <span class="refund-status-badge is-completed">
                                        <i class="ri-check-circle-line"></i>
                                        {{ $showDmcColumn ? 'Refunded to DMC' : 'Refund completed' }}
                                    </span>
                                @endif
                                <div class="d-flex flex-wrap gap-2">
                                    @forelse($refundOrders as $refundOrder)
                                        @php
                                            $refundType = $refundOrder->type ?? null;
                                            $serviceMeta = $refundServiceIcons[$refundType] ?? ['icon' => 'ri-service-line', 'label' => ucfirst(str_replace('_', ' ', (string)$refundType))];
                                            $isOnlineServiceOrder = $isOnlineRefundOrder($refundOrder);
                                            $adminRefundedToDmc = (bool) ($refundOrder->travclicks_refunded ?? false);
                                            $isWaitingAdminApproval = ! $showDmcColumn
                                                && $isOnlineServiceOrder
                                                && ! $adminRefundedToDmc;
                                            $isAdminReadyForDmc = ! $showDmcColumn
                                                && $isOnlineServiceOrder
                                                && $adminRefundedToDmc
                                                && ! ((bool) ($refundOrder->refunded ?? false));
                                            $isRefundCompleteForOrder = $showDmcColumn
                                                ? $adminRefundedToDmc
                                                : (bool) ($refundOrder->refunded ?? false);
                                            $isRefundInitiated = ! $showDmcColumn
                                                && ! $isWaitingAdminApproval
                                                && (int) ($refundOrder->is_verify ?? 0) === 2
                                                && ! $isRefundCompleteForOrder;
                                            // Always use primary key for action endpoints to avoid
                                            // mismatching records that share booking_id.
                                            $orderActionId = $refundOrder->id ?? null;
                                            $orderIdentifier = $refundOrder->booking_id ?? $refundOrder->id ?? null;
                                            $refundPriceMax = (float) ($refundOrder->refund_price_with_markup ?? 0);
                                            $refundPriceCurrency = trim((string) ($refundOrder->refund_price_currency ?? ($currency ?? '')));
                                            // DMC view: order price stays in order currency; refund input uses DMC base currency.
                                            $refundOrderCurrency = ! $showDmcColumn
                                                ? strtoupper(trim((string) (
                                                    $refundOrder->refund_order_currency
                                                    ?? $refundOrder->currency
                                                    ?? $tour->user_currency
                                                    ?? $tour->money_currency
                                                    ?? ''
                                                )))
                                                : '';
                                            $refundOrderAmount = ! $showDmcColumn && isset($refundOrder->refund_order_amount)
                                                ? (float) $refundOrder->refund_order_amount
                                                : null;
                                            $savedRefundAmount = $showDmcColumn
                                                ? ($refundOrder->travclicks_refund_amount ?? null)
                                                : ($refundOrder->refund_amount ?? null);
                                            $hotelSuggested = isset($refundOrder->refund_suggested_amount)
                                                ? (float) $refundOrder->refund_suggested_amount
                                                : null;
                                            $hotelCharge = isset($refundOrder->hotel_cancellation_charge)
                                                ? (float) $refundOrder->hotel_cancellation_charge
                                                : null;
                                            $hotelSummary = trim((string) ($refundOrder->hotel_cancellation_summary ?? ''));
                                            $hotelRefundPolicy = trim((string) ($refundOrder->hotel_refund_policy ?? ''));
                                            $hotelRefundPolicyName = trim((string) ($refundOrder->hotel_refund_policy_name ?? ''));
                                            $hotelRefundPolicyB64 = $hotelRefundPolicy !== ''
                                                ? base64_encode($hotelRefundPolicy)
                                                : '';
                                            $onlineDmcMarkup = $showDmcColumn && isset($refundOrder->online_dmc_markup)
                                                ? (float) $refundOrder->online_dmc_markup
                                                : null;
                                            $onlineDmcMarkupLabel = $showDmcColumn
                                                ? trim((string) ($refundOrder->online_dmc_markup_label ?? ''))
                                                : '';
                                            $fullOrderPrice = $showDmcColumn && isset($refundOrder->refund_full_price_with_markup)
                                                ? (float) $refundOrder->refund_full_price_with_markup
                                                : (($onlineDmcMarkup !== null && $onlineDmcMarkup > 0)
                                                    ? round($refundPriceMax + $onlineDmcMarkup, 2)
                                                    : $refundPriceMax);
                                            $dmcWalletCurrency = $showDmcColumn
                                                ? strtoupper(trim((string) ($refundOrder->dmc_wallet_currency ?? $tour->dmc_wallet_currency ?? '')))
                                                : '';
                                            $dmcWalletSgdRate = $showDmcColumn && $refundOrder->dmc_wallet_sgd_rate !== null
                                                ? (float) $refundOrder->dmc_wallet_sgd_rate
                                                : ($showDmcColumn && ($tour->dmc_wallet_sgd_rate ?? null) !== null
                                                    ? (float) $tour->dmc_wallet_sgd_rate
                                                    : null);
                                            $sourceSgdRate = $showDmcColumn && $refundOrder->refund_source_sgd_rate !== null
                                                ? (float) $refundOrder->refund_source_sgd_rate
                                                : null;
                                            $supplierCancelStatus = strtolower(trim((string) ($refundOrder->supplier_cancel_status ?? '')));
                                            $supplierCancelBlocksWallet = $showDmcColumn
                                                && ! $adminRefundedToDmc
                                                && (int) ($refundOrder->supplier_cancel_blocks_wallet ?? 0) === 1;
                                            $supplierCancelError = trim((string) (
                                                $refundOrder->supplier_cancel_last_error
                                                ?? $refundOrder->supplier_cancel_message
                                                ?? ''
                                            ));
                                            if ($supplierCancelBlocksWallet) {
                                                $statusPhrase = 'Supplier cancel '
                                                    . ($supplierCancelStatus !== '' ? strtoupper($supplierCancelStatus) : 'PENDING')
                                                    . ' — retry or mark resolved before wallet credit';
                                            } elseif ($isWaitingAdminApproval) {
                                                $statusPhrase = 'Admin not refunded yet — waiting for admin approval';
                                            } elseif ($isAdminReadyForDmc) {
                                                $statusPhrase = 'Admin refunded — you may proceed';
                                            } elseif ($isRefundCompleteForOrder) {
                                                $statusPhrase = $showDmcColumn ? 'Refunded to DMC' : 'Refunded to agent';
                                            } elseif ($isRefundInitiated) {
                                                $statusPhrase = 'Refund initiated — click to verify';
                                            } else {
                                                $statusPhrase = $showDmcColumn ? 'Refund to DMC wallet' : 'Initiate refund';
                                            }
                                            $serviceTooltip = $serviceMeta['label']
                                                . ' ('
                                                . $statusPhrase
                                                . ')'
                                                . ($orderIdentifier ? (' · Order: ' . $orderIdentifier) : '')
                                                . ($savedRefundAmount !== null && $savedRefundAmount !== ''
                                                    ? (' · Refund ' . $refundPriceCurrency . ' ' . number_format((float) $savedRefundAmount, 2))
                                                    : '')
                                                . ($onlineDmcMarkup !== null && $onlineDmcMarkup > 0
                                                    ? (' · DMC markup ' . $refundPriceCurrency . ' ' . number_format($onlineDmcMarkup, 2)
                                                        . ($onlineDmcMarkupLabel !== '' ? ' (' . $onlineDmcMarkupLabel . ')' : '')
                                                        . ' deducted')
                                                    : '')
                                                . (!empty($refundOrder->refund_transaction_id) ? (' · Txn: ' . $refundOrder->refund_transaction_id) : '')
                                                . (!empty($refundOrder->refund_remarks) ? (' · ' . \Illuminate\Support\Str::limit((string) $refundOrder->refund_remarks, 80)) : '')
                                                . ($hotelSummary !== '' ? (' · ' . $hotelSummary) : '')
                                                . ($supplierCancelBlocksWallet && $supplierCancelError !== ''
                                                    ? (' · ' . \Illuminate\Support\Str::limit($supplierCancelError, 100))
                                                    : '');
                                            $iconStateClass = $isRefundCompleteForOrder
                                                ? 'is-refunded'
                                                : ($supplierCancelBlocksWallet
                                                    ? 'is-supplier-cancel-blocked'
                                                    : ($isWaitingAdminApproval
                                                        ? 'is-waiting-admin'
                                                        : ($isAdminReadyForDmc
                                                            ? 'is-admin-ready'
                                                            : ($isRefundInitiated ? 'is-refund-initiated' : ''))));
                                            $iconColor = ($isRefundCompleteForOrder || $isRefundInitiated)
                                                ? '#ffffff'
                                                : ($supplierCancelBlocksWallet
                                                    ? '#b91c1c'
                                                    : ($isWaitingAdminApproval
                                                        ? '#b45309'
                                                        : ($isAdminReadyForDmc ? '#1d4ed8' : '#7c3aed')));
                                        @endphp
                                        <div class="d-flex flex-column align-items-center gap-1">
                                        <button type="button"
                                                class="action-icon-badge {{ $iconStateClass }}"
                                                style="--action-color: {{ $iconColor }};"
                                                data-tooltip="{{ $serviceTooltip }}"
                                                data-tour-id="{{ $tour->tour_id }}"
                                                data-service-type="{{ $refundType }}"
                                                data-order-id="{{ $orderActionId }}"
                                                data-refund-initiated="{{ $isRefundInitiated ? '1' : '0' }}"
                                                data-waiting-admin="{{ $isWaitingAdminApproval ? '1' : '0' }}"
                                                data-supplier-cancel-blocked="{{ $supplierCancelBlocksWallet ? '1' : '0' }}"
                                                data-supplier-cancel-status="{{ e($supplierCancelStatus) }}"
                                                data-refund-max="{{ number_format($refundPriceMax, 2, '.', '') }}"
                                                data-refund-full="{{ number_format($fullOrderPrice, 2, '.', '') }}"
                                                data-refund-currency="{{ $refundPriceCurrency }}"
                                                @if(! $showDmcColumn && $refundOrderCurrency !== '')
                                                    data-order-currency="{{ $refundOrderCurrency }}"
                                                    data-order-amount="{{ number_format($refundOrderAmount !== null ? $refundOrderAmount : $refundPriceMax, 2, '.', '') }}"
                                                @endif
                                                data-refund-current="{{ ($savedRefundAmount !== null && $savedRefundAmount !== '') ? number_format((float) $savedRefundAmount, 2, '.', '') : '' }}"
                                                data-refund-txn="{{ e(trim((string) ($refundOrder->refund_transaction_id ?? ''))) }}"
                                                data-refund-remarks="{{ e(preg_replace('/\s+/u', ' ', trim((string) ($refundOrder->refund_remarks ?? '')))) }}"
                                                data-refund-suggested="{{ $hotelSuggested !== null ? number_format($hotelSuggested, 2, '.', '') : '' }}"
                                                data-cancel-charge="{{ $hotelCharge !== null ? number_format($hotelCharge, 2, '.', '') : '' }}"
                                                data-cancel-summary="{{ e($hotelSummary) }}"
                                                data-hotel-name="{{ e($hotelRefundPolicyName) }}"
                                                data-refund-policy-b64="{{ $hotelRefundPolicyB64 }}"
                                                data-dmc-markup="{{ ($onlineDmcMarkup !== null && $onlineDmcMarkup > 0) ? number_format($onlineDmcMarkup, 2, '.', '') : '' }}"
                                                data-dmc-markup-label="{{ e($onlineDmcMarkupLabel) }}"
                                                @if($showDmcColumn && $dmcWalletCurrency !== '')
                                                    data-dmc-wallet-currency="{{ $dmcWalletCurrency }}"
                                                    data-dmc-wallet-sgd-rate="{{ $dmcWalletSgdRate !== null ? number_format($dmcWalletSgdRate, 8, '.', '') : '' }}"
                                                    data-source-sgd-rate="{{ $sourceSgdRate !== null ? number_format($sourceSgdRate, 8, '.', '') : '' }}"
                                                @endif
                                                @if($isRefundCompleteForOrder || $isWaitingAdminApproval || $supplierCancelBlocksWallet)
                                                    disabled
                                                    aria-disabled="true"
                                                @else
                                                    onclick="processSingleOrderRefund({{ $tour->tour_id }}, {{ (int)($orderActionId ?? 0) }}, event)"
                                                @endif>
                                            <i class="{{ $serviceMeta['icon'] }}"></i>
                                        </button>
                                        @if($supplierCancelBlocksWallet)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.65rem;">
                                                {{ strtoupper($supplierCancelStatus !== '' ? $supplierCancelStatus : 'pending') }}
                                            </span>
                                            <div class="d-flex gap-1">
                                                <button type="button"
                                                        class="btn btn-xs btn-outline-primary py-0 px-1"
                                                        style="font-size: 0.65rem; line-height: 1.2;"
                                                        onclick="retrySupplierCancellation({{ $tour->tour_id }}, {{ (int)($orderActionId ?? 0) }})">
                                                    Retry
                                                </button>
                                                <button type="button"
                                                        class="btn btn-xs btn-outline-secondary py-0 px-1"
                                                        style="font-size: 0.65rem; line-height: 1.2;"
                                                        onclick="resolveSupplierCancellation({{ $tour->tour_id }}, {{ (int)($orderActionId ?? 0) }})">
                                                    Resolve
                                                </button>
                                            </div>
                                        @endif
                                        </div>
                                    @empty
                                        <span class="text-muted small">N/A</span>
                                    @endforelse
                                </div>
                                @include('bookings.partials.refund-money-summary', [
                                    'tour' => $tour,
                                    'currency' => $currency ?? '',
                                    'isTravclicksView' => $showDmcColumn,
                                ])
                                </div>
                            </td>
                            @endif
                            <td>
                                <div class="d-flex flex-column">
                                    <small><strong>Cancelled:</strong> {{ \Carbon\Carbon::parse($tour->updated_at)->format('D, M d, Y') }}</small>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($tour->updated_at)->format('h:i A') }}</small>
                                    @if($tour->tour_status === 'Refunded')
                                        @if(!empty($tour->refund_transaction_id))
                                            <small class="text-muted mt-1" title="Refund transaction ID">
                                                <i class="ri-hashtag"></i> {{ $tour->refund_transaction_id }}
                                            </small>
                                        @endif
                                        @if(!empty($tour->refund_remarks))
                                            <small class="text-muted" title="Refund remarks">
                                                <i class="ri-chat-1-line"></i> {{ \Illuminate\Support\Str::limit((string) $tour->refund_remarks, 60) }}
                                            </small>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            
                            <td class="align-top col-actions">
                                <div class="actions-icons-wrap">
                                    <a href="{{ route('bookings.view-tour', ['tourId' => \Crypt::encrypt($tour->tour_id)]) }}"
                                       class="action-icon-badge" style="--action-color: #0369a1;" data-tooltip="Audit Trail">
                                        <i class="ri-eye-line"></i>
                                    </a>
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
                                        @php
                                            $createdAt = $tour->destination_created_at ?? $tour->created_at;
                                        @endphp
                                        <span>{{ $createdAt->format('D, M d, Y') }} · {{ $createdAt->format('h:i A') }}</span>
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
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="ri-money-dollar-circle-line ri-48px text-muted mb-3"></i>
                                    <h6 class="text-muted">No refunds found</h6>
                                    <p class="text-muted small mb-0">No tours with 'Refund - Pending' status found for refund processing.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            {{-- @if($tours->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $tours->links() }}
            </div>
            @endif --}} 
            @else
            <!-- Empty State -->
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="ri-money-dollar-circle-line ri-64px text-muted"></i>
                </div>
                <h4 class="text-muted mb-3 text-center fw-bold">No Refunds Available</h4>
                <p class="text-muted mb-4 small text-center">Currently, there are no tours with 'Refund - Pending' status that require refund processing.</p>
                <p class="text-muted small text-center">Refunds will appear here when tours are cancelled and marked as definite.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const destinationFilter = document.getElementById('destinationFilter');
        const agentFilter = document.getElementById('agentFilter');
        const statusFilter = document.getElementById('statusFilter');
        const startDateFilter = document.getElementById('startDateFilter');
        const endDateFilter = document.getElementById('endDateFilter');
        const today = new Date().toISOString().split('T')[0];

        if (searchInput) searchInput.addEventListener('input', filterTable);
        // Note: destinationFilter and agentFilter event listeners are handled by Select2 initialization
        // They will trigger filterTable when changed via Select2's change event
        if (statusFilter) statusFilter.addEventListener('change', filterTable);

        if (startDateFilter) {
            startDateFilter.setAttribute('max', today);
            startDateFilter.addEventListener('change', function() {
                if (endDateFilter) {
                    if (this.value) {
                        if (endDateFilter.value && endDateFilter.value < this.value) {
                            endDateFilter.value = this.value;
                        }
                        endDateFilter.setAttribute('min', this.value);
                    } else {
                        endDateFilter.removeAttribute('min');
                    }
                }
                filterTable();
            });
        }

        if (endDateFilter) {
            endDateFilter.setAttribute('max', today);
            endDateFilter.addEventListener('change', function() {
                if (startDateFilter) {
                    if (this.value) {
                        if (startDateFilter.value && startDateFilter.value > this.value) {
                            startDateFilter.value = this.value;
                        }
                        startDateFilter.setAttribute('max', this.value);
                    } else {
                        startDateFilter.setAttribute('max', today);
                    }
                }
                filterTable();
            });
        }
    });

    $(document).ready(function() {
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

    function filterTable() {
        const searchTerm = document.getElementById('searchInput')?.value.toLowerCase().trim() || '';
        const destinationFilter = document.getElementById('destinationFilter')?.value || '';
        const agentFilter = document.getElementById('agentFilter')?.value || '';
        const statusFilter = document.getElementById('statusFilter')?.value || '';
        const startDateValue = document.getElementById('startDateFilter')?.value || '';
        const endDateValue = document.getElementById('endDateFilter')?.value || '';

        const rows = document.querySelectorAll('#toursTable tbody tr');

        if (typeof table !== 'undefined' && table && typeof table.rows === 'function') {
            table.rows('.dt-hasChild').every(function() {
                if (this.child.isShown()) this.child.hide();
                $(this.node()).removeClass('dt-hasChild');
            });
        }

        let visibleCount = 0;
        let pendingCount = 0;
        let refundedCount = 0;

        rows.forEach(row => {
            if (row.cells.length <= 1) {
                return;
            }

            const rowText = row.textContent.toLowerCase();
            const destination = row.getAttribute('data-destination') || '';
            const agent = row.getAttribute('data-agent') || '';
            const rowStatus = row.getAttribute('data-tour-status') || '';
            const hasPending = row.getAttribute('data-has-pending') === '1';
            const hasRefunded = row.getAttribute('data-has-refunded') === '1';
            const createdAt = row.getAttribute('data-created-at');
            const updatedAt = row.getAttribute('data-updated-at');

            let show = true;

            if (searchTerm && !rowText.includes(searchTerm)) {
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

            // Pending: any unfinished tour/service refund.
            // Refunded: completed only — a row with any pending service cancel stays out.
            if (statusFilter === 'pending' && !hasPending) {
                show = false;
            }
            if (statusFilter === 'refunded' && (!hasRefunded || hasPending)) {
                show = false;
            }

            if (startDateValue || endDateValue) {
                if (createdAt || updatedAt) {
                    const startDate = startDateValue ? new Date(startDateValue + 'T00:00:00') : null;
                    const endDate = endDateValue ? new Date(endDateValue + 'T23:59:59') : null;
                    let dateInRange = false;

                    if (createdAt) {
                        const createdDate = new Date(createdAt + 'T00:00:00');
                        if ((!startDate || createdDate >= startDate) && (!endDate || createdDate <= endDate)) {
                            dateInRange = true;
                        }
                    }

                    if (!dateInRange && updatedAt) {
                        const updatedDate = new Date(updatedAt + 'T00:00:00');
                        if ((!startDate || updatedDate >= startDate) && (!endDate || updatedDate <= endDate)) {
                            dateInRange = true;
                        }
                    }

                    if (!dateInRange) {
                        show = false;
                    }
                } else {
                    show = false;
                }
            }

            row.style.display = show ? '' : 'none';

            if (show) {
                visibleCount++;
                if (hasPending) {
                    pendingCount++;
                }
                if (hasRefunded) {
                    refundedCount++;
                }
            }
        });

        const countEl = document.getElementById('rangeCount');
        const labelEl = document.getElementById('rangeLabel');
        const statRefunds = document.getElementById('statRefundsCount');
        const statRefundsLabel = document.getElementById('statRefundsLabel');
        const statTotal = document.getElementById('statTotalCount');
        const statTotalLabel = document.getElementById('statTotalLabel');
        const statPending = document.getElementById('statPendingCount');
        const statPendingLabel = document.getElementById('statPendingLabel');
        const statCompleted = document.getElementById('statCompletedCount');
        const statCompletedLabel = document.getElementById('statCompletedLabel');

        if (countEl) countEl.textContent = visibleCount;
        if (statRefunds) statRefunds.textContent = visibleCount;
        if (statTotal) statTotal.textContent = visibleCount;
        if (statPending) statPending.textContent = pendingCount;
        if (statCompleted) statCompleted.textContent = refundedCount;

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

            if (!label) {
                label = 'Custom Range';
            }

            if (labelEl) labelEl.textContent = label;
            if (statRefundsLabel) statRefundsLabel.textContent = `Refunds - ${label}`;
            if (statTotalLabel) statTotalLabel.textContent = `Total Refunds - ${label}`;
            if (statPendingLabel) statPendingLabel.textContent = `Pending Refunds - ${label}`;
            if (statCompletedLabel) statCompletedLabel.textContent = `Completed Refunds - ${label}`;
        } else {
            const month = new Date().toLocaleString('default', { month: 'long' });
            if (labelEl) labelEl.textContent = month;
            if (statRefundsLabel) statRefundsLabel.textContent = `${month} Refunds`;
            if (statTotalLabel) statTotalLabel.textContent = 'Total Refunds';
            if (statPendingLabel) statPendingLabel.textContent = 'Pending Refunds';
            if (statCompletedLabel) statCompletedLabel.textContent = 'Completed Refunds';
        }
    }

    function resetFilters() {
        const searchInput = document.getElementById('searchInput');
        const destinationSelect = document.getElementById('destinationFilter');
        const agentSelect = document.getElementById('agentFilter');
        const statusSelect = document.getElementById('statusFilter');
        const startDateInput = document.getElementById('startDateFilter');
        const endDateInput = document.getElementById('endDateFilter');
        const today = new Date().toISOString().split('T')[0];

        if (searchInput) searchInput.value = '';
        
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
        
        if (statusSelect) statusSelect.value = '';

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
                const headerTexts = $('#toursTable thead th').map(function() {
                    return $(this).text().trim();
                }).get();
                const colIndex = (name) => headerTexts.findIndex(t => t === name);
                const actionsIdx = colIndex('Actions');
                const statusIdx = colIndex('Refund Status');

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
            if (table && typeof table.button === 'function') {
                table.button('.buttons-copy').trigger();
            } else {
                alert('Copy export is not available.');
            }
        });

        $('#exportCSV').on('click', function() {
            if (table && typeof table.button === 'function') {
                table.button('.buttons-csv').trigger();
            } else {
                alert('CSV export is not available.');
            }
        });

        $('#exportExcel').on('click', function() {
            if (table && typeof table.button === 'function') {
                table.button('.buttons-excel').trigger();
            } else {
                alert('Excel export is not available.');
            }
        });

        $('#exportPDF').on('click', function() {
            if (table && typeof table.button === 'function') {
                table.button('.buttons-pdf').trigger();
            } else {
                alert('PDF export is not available.');
            }
        });

        $('#exportPrint').on('click', function() {
            if (table && typeof table.button === 'function') {
                table.button('.buttons-print').trigger();
            } else {
                window.print();
            }
        });
    }

    // Global tooltip for table headers and action icons
    $(document).ready(function() {
        var $globalTooltip = $('#service-icon-global-tooltip');
        if (!$globalTooltip.length) {
            $globalTooltip = $('<div id="service-icon-global-tooltip" aria-hidden="true"></div>').appendTo('body');
        }
        $(document).on('mouseenter', '#toursTable thead .th-tooltip', function() {
            var txt = $(this).attr('data-tooltip') || $(this).attr('title') || $(this).text();
            if (!txt) return;
            var rect = this.getBoundingClientRect();
            $globalTooltip.css({ display: 'block', left: (rect.left + rect.width / 2) + 'px', top: (rect.top - 6) + 'px', transform: 'translate(-50%, -100%)' }).text(txt);
        });
        $(document).on('mouseleave', '#toursTable thead .th-tooltip', function() { $globalTooltip.hide(); });
        $(document).on('mouseenter', '#toursTable .action-icon-badge', function() {
            var txt = $(this).attr('data-tooltip') || $(this).attr('title') || '';
            if (!txt) return;
            var rect = this.getBoundingClientRect();
            $globalTooltip.css({ display: 'block', left: (rect.left + rect.width / 2) + 'px', top: (rect.top - 6) + 'px', transform: 'translate(-50%, -100%)' }).text(txt);
        });
        $(document).on('mouseleave', '#toursTable .action-icon-badge', function() { $globalTooltip.hide(); });
    });

// Process refund function
function processRefund(tourId, event) {
    const button = event?.target?.closest('button');
    const maxAmount = parseFloat(button?.dataset.refundMax || '0');
    const currency = button?.dataset.refundCurrency || '';
    const tourMax = isFinite(maxAmount) ? maxAmount : 0;

    showConfirmationModal(
        'Process Refund Confirmation',
        'Are you sure you want to process the refund for this tour?',
        'warning',
        function(refundAmount, meta) {
            showLoadingModal('Processing Refund', 'Please wait while we process the refund...');

            const originalContent = button ? button.innerHTML : '';
            if (button) {
                button.innerHTML = '<i class="ri-loader-line spinner-border spinner-border-sm me-1"></i>';
                button.disabled = true;
            }

            $.ajax({
                url: '{{ route("bookings.process-refund") }}',
                method: 'POST',
                data: {
                    tour_id: tourId,
                    refund_amount: refundAmount,
                    transaction_id: meta?.transaction_id || '',
                    remarks: meta?.remarks || '',
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    hideModal();
                    if (response.success) {
                        showSuccessModal(
                            'Refund Processed Successfully!',
                            'The refund has been processed and the tour status has been updated.',
                            function() {
                                location.reload();
                            }
                        );
                    } else {
                        showErrorModal('Error Processing Refund', response.message || 'An error occurred while processing the refund.');
                        if (button) {
                            button.innerHTML = originalContent;
                            button.disabled = false;
                        }
                    }
                },
                error: function(xhr) {
                    hideModal();
                    let errorMessage = 'Error processing refund. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showErrorModal('Error Processing Refund', errorMessage);
                    if (button) {
                        button.innerHTML = originalContent;
                        button.disabled = false;
                    }
                }
            });
        },
        {
            title: 'Process Refund Confirmation',
            confirmLabel: 'Process Refund',
            priceLabel: 'Invoice total (after discount + tax)',
            helpText: 'Same total the agent paid. You can refund this amount or less.',
            note: 'This action will mark the tour as "Refunded" and cannot be undone.',
            maxAmount: tourMax,
            currency: currency,
            amount: tourMax
        }
    );
}

// Mark refund-eligible services as refunded (orders.refunded = true)
function processOrderRefund(tourId, event) {
    showConfirmationModal(
        'Mark Service Refunded',
        'Mark all refund services for this tour as refunded?<br><small class="text-muted">This updates orders with is_refund = 1.</small>',
        'warning',
        function() {
            showLoadingModal('Updating Service Refund', 'Please wait while we update the refunded status...');

            const button = event?.target?.closest('button');
            const originalContent = button ? button.innerHTML : '';
            if (button) {
                button.innerHTML = '<i class="ri-loader-line spinner-border spinner-border-sm me-1"></i>';
                button.disabled = true;
            }

            $.ajax({
                url: '{{ route("bookings.process-order-refund") }}',
                method: 'POST',
                data: {
                    tour_id: tourId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    hideModal();
                    if (response.success) {
                        showSuccessModal(
                            'Service Refund Updated',
                            response.message || 'Refunded status updated successfully.',
                            function() {
                                location.reload();
                            }
                        );
                    } else {
                        showErrorModal('Error Updating Service Refund', response.message || 'Unable to update refunded status.');
                        if (button) {
                            button.innerHTML = originalContent;
                            button.disabled = false;
                        }
                    }
                },
                error: function(xhr) {
                    hideModal();
                    let errorMessage = 'Error updating service refund. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showErrorModal('Error Updating Service Refund', errorMessage);
                    if (button) {
                        button.innerHTML = originalContent;
                        button.disabled = false;
                    }
                }
            });
        }
    );
}

function retrySupplierCancellation(tourId, orderId) {
    if (!tourId || !orderId) return;
    if (!window.confirm('Retry supplier cancellation for this online service? Wallet credit stays blocked until cancel is confirmed.')) {
        return;
    }

    showLoadingModal('Retrying Supplier Cancel', 'Please wait...');
    $.ajax({
        url: '{{ route("bookings.retry-supplier-cancellation") }}',
        method: 'POST',
        data: {
            tour_id: tourId,
            order_id: orderId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideModal();
            if (response.success) {
                showSuccessModal('Supplier Cancel Updated', response.message || 'Cancellation confirmed.', function() {
                    location.reload();
                });
            } else {
                showErrorModal('Supplier Cancel Still Pending', response.message || 'Retry failed. You can try again or mark resolved.');
            }
        },
        error: function(xhr) {
            hideModal();
            let errorMessage = 'Failed to retry supplier cancellation.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            // 422 soft-fail still returns useful status — treat like still pending.
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message) {
                showErrorModal('Supplier Cancel Still Pending', errorMessage);
                return;
            }
            showErrorModal('Supplier Cancel Retry Failed', errorMessage);
        }
    });
}

function resolveSupplierCancellation(tourId, orderId) {
    if (!tourId || !orderId) return;

    const reason = window.prompt(
        'Enter the audit reason for marking supplier cancellation as resolved (required). This unlocks TravClicks wallet credit.',
        ''
    );
    if (reason === null) {
        return;
    }
    const trimmed = String(reason || '').trim();
    if (trimmed.length < 5) {
        showErrorModal('Reason Required', 'Please enter at least 5 characters explaining why this is resolved.');
        return;
    }

    showLoadingModal('Marking Resolved', 'Please wait...');
    $.ajax({
        url: '{{ route("bookings.resolve-supplier-cancellation") }}',
        method: 'POST',
        data: {
            tour_id: tourId,
            order_id: orderId,
            reason: trimmed,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideModal();
            if (response.success) {
                showSuccessModal('Supplier Cancel Resolved', response.message || 'Wallet credit is now allowed.', function() {
                    location.reload();
                });
            } else {
                showErrorModal('Resolve Failed', response.message || 'Unable to mark supplier cancellation as resolved.');
            }
        },
        error: function(xhr) {
            hideModal();
            let errorMessage = 'Failed to resolve supplier cancellation.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showErrorModal('Resolve Failed', errorMessage);
        }
    });
}

// Mark a single refund order as refunded (orders.refunded = 1 for that order only)
function processSingleOrderRefund(tourId, orderId, event) {
    if (!orderId) return;

    const button = event?.target?.closest('button');
    if (button?.dataset.waitingAdmin === '1') {
        showErrorModal(
            'Waiting for Admin Approval',
            'Admin has not refunded yet. You can refund this online service after TravClicks credits the DMC wallet.'
        );
        return;
    }
    if (button?.dataset.supplierCancelBlocked === '1') {
        showErrorModal(
            'Supplier Cancellation Pending',
            'Supplier cancel is '
                + String(button.dataset.supplierCancelStatus || 'pending').toUpperCase()
                + '. Use Retry or Resolve before crediting the DMC wallet.'
        );
        return;
    }
    const initiated = button?.dataset.refundInitiated === '1';
    const isTravclicksRefund = {{ in_array((int) (auth()->user()->role_id ?? 0), [1, 2, 3, 4], true) ? 'true' : 'false' }};
    const maxAmount = parseFloat(button?.dataset.refundMax || '0');
    const fullOrderPrice = parseFloat(button?.dataset.refundFull || maxAmount || '0');
    const currency = button?.dataset.refundCurrency || '';
    const orderCurrency = (button?.dataset.orderCurrency || '').toUpperCase();
    const orderAmountRaw = parseFloat(button?.dataset.orderAmount || '');
    const savedAmount = button?.dataset.refundCurrent;
    const suggestedRaw = button?.dataset.refundSuggested;
    const cancelCharge = button?.dataset.cancelCharge;
    const cancelSummary = button?.dataset.cancelSummary || '';
    const hotelName = button?.dataset.hotelName || '';
    const dmcMarkupRaw = button?.dataset.dmcMarkup || '';
    const dmcMarkupLabel = button?.dataset.dmcMarkupLabel || '';
    const dmcMarkupAmount = dmcMarkupRaw !== '' ? parseFloat(dmcMarkupRaw) : NaN;
    let hotelRefundPolicyHtml = '';
    try {
        const policyB64 = button?.dataset.refundPolicyB64 || '';
        if (policyB64) {
            hotelRefundPolicyHtml = decodeURIComponent(escape(atob(policyB64)));
        }
    } catch (e) {
        hotelRefundPolicyHtml = '';
    }
    const dmcWalletCurrency = (button?.dataset.dmcWalletCurrency || '').toUpperCase();
    const dmcWalletSgdRate = parseFloat(button?.dataset.dmcWalletSgdRate || '');
    const sourceSgdRate = parseFloat(button?.dataset.sourceSgdRate || '');
    const suggestedAmount = (suggestedRaw !== undefined && suggestedRaw !== '')
        ? parseFloat(suggestedRaw)
        : NaN;
    const startingAmount = (savedAmount !== undefined && savedAmount !== '')
        ? Math.min(parseFloat(savedAmount) || 0, isFinite(maxAmount) ? maxAmount : 0)
        : (isFinite(suggestedAmount)
            ? Math.min(suggestedAmount, isFinite(maxAmount) ? maxAmount : suggestedAmount)
            : (isFinite(maxAmount) ? maxAmount : 0));

    // TravClicks: refund amount is always SGD; convert to DMC wallet currency below.
    // DMC: refund amount is DMC base currency; order price stays in order currency.
    const refundCurrency = isTravclicksRefund ? 'SGD' : currency;
    const displayOrderCurrency = (!isTravclicksRefund && orderCurrency)
        ? orderCurrency
        : refundCurrency;
    const displayOrderAmount = (!isTravclicksRefund && isFinite(orderAmountRaw))
        ? orderAmountRaw
        : (isFinite(fullOrderPrice) ? fullOrderPrice : maxAmount);
    const refundSourceSgdRate = isTravclicksRefund ? 1 : sourceSgdRate;
    const hasDmcMarkup = isTravclicksRefund && isFinite(dmcMarkupAmount) && dmcMarkupAmount > 0;
    const dmcCurrencyDiffers = !isTravclicksRefund
        && !!displayOrderCurrency
        && !!refundCurrency
        && displayOrderCurrency !== refundCurrency;

    let initiateMessage = isTravclicksRefund
        ? 'Enter the refund amount in <strong>SGD</strong>. The converted DMC-currency amount below is credited to the DMC wallet.'
        : (dmcCurrencyDiffers
            ? ('Order price is in <strong>' + displayOrderCurrency + '</strong>. Enter the refund amount in your DMC currency (<strong>'
                + refundCurrency + '</strong>).')
            : 'Enter the refund amount for this service. You can use the full price or a lower amount.');
    if (!initiated && (cancelSummary || hasDmcMarkup || dmcCurrencyDiffers)) {
        const chargeVal = isFinite(parseFloat(cancelCharge)) ? Number(cancelCharge).toFixed(2) : '0.00';
        const suggestedVal = isFinite(suggestedAmount) ? suggestedAmount.toFixed(2) : startingAmount.toFixed(2);
        initiateMessage = (cancelSummary ? 'Hotel cancellation policy applied.' : 'Refund breakdown.')
            + '<br><strong>Order price:</strong> '
            + (displayOrderCurrency ? displayOrderCurrency + ' ' : '')
            + (isFinite(displayOrderAmount) ? displayOrderAmount.toFixed(2) : maxAmount.toFixed(2));
        if (hasDmcMarkup) {
            initiateMessage += '<br><strong>DMC markup'
                + (dmcMarkupLabel ? ' (' + dmcMarkupLabel + ')' : '')
                + ':</strong> −'
                + (refundCurrency ? refundCurrency + ' ' : '')
                + dmcMarkupAmount.toFixed(2)
                + ' <span class="small text-muted">(already paid to DMC — deducted)</span>';
        }
        if (cancelSummary) {
            initiateMessage += '<br><strong>Charge:</strong> '
                + (refundCurrency ? refundCurrency + ' ' : '')
                + chargeVal;
        }
        initiateMessage += '<br><strong>Suggested refund:</strong> '
            + (refundCurrency ? refundCurrency + ' ' : '')
            + suggestedVal;
        if (cancelSummary) {
            initiateMessage += '<br><span class="small">' + cancelSummary + '</span>';
        }
        if (isTravclicksRefund) {
            initiateMessage += '<br><br>Amounts are in SGD. The converted DMC-currency amount below is credited to the DMC wallet.';
        } else if (dmcCurrencyDiffers) {
            initiateMessage += '<br><br>Refund amount below is in <strong>' + refundCurrency
                + '</strong> (DMC base currency), converted from the order currency.';
        }
    }

    const savedTxnId = button?.dataset.refundTxn || '';
    const savedRemarks = button?.dataset.refundRemarks || '';

    const modalOptions = initiated
        ? {
            title: isTravclicksRefund ? 'Complete Refund to DMC' : 'Verify Refund',
            confirmLabel: isTravclicksRefund ? 'Credit DMC Wallet' : 'Verify Refund',
            verifyOnly: true,
            currency: refundCurrency,
            amount: isFinite(parseFloat(savedAmount)) ? parseFloat(savedAmount) : 0,
            transactionId: savedTxnId,
            remarks: savedRemarks,
            note: isTravclicksRefund
                ? 'This will complete the refund and credit the converted amount to the DMC wallet.'
                : null
        }
        : {
            title: isTravclicksRefund ? 'Refund to DMC Wallet' : 'Initiate Refund',
            confirmLabel: isTravclicksRefund ? 'Refund to DMC' : 'Initiate Refund',
            maxAmount: isFinite(maxAmount) ? maxAmount : 0,
            fullOrderPrice: isFinite(fullOrderPrice) ? fullOrderPrice : (isFinite(maxAmount) ? maxAmount : 0),
            currency: refundCurrency,
            amount: isFinite(startingAmount) ? startingAmount : 0,
            transactionId: savedTxnId,
            remarks: savedRemarks,
            priceLabel: isTravclicksRefund
                ? (hasDmcMarkup ? 'Max refundable to DMC (SGD)' : 'Order price with markup (SGD)')
                : 'Order price with markup',
            // DMC base-currency refund: order price is already in the message; hide the extra max box.
            hideMaxPriceDisplay: !isTravclicksRefund && dmcCurrencyDiffers,
            dmcMarkupAmount: hasDmcMarkup ? dmcMarkupAmount : null,
            dmcMarkupLabel: hasDmcMarkup ? dmcMarkupLabel : '',
            helpText: isTravclicksRefund
                ? (hasDmcMarkup
                    ? 'Enter SGD. DMC markup is deducted because it was already paid to the DMC. You can lower the amount, but not above this max.'
                    : (cancelSummary
                        ? 'Enter SGD. Default is the policy suggested refund. You can lower it, but not above the SGD max.'
                        : 'Enter SGD. You can refund this amount or less. It cannot be higher than the SGD order price with markup.'))
                : (dmcCurrencyDiffers
                    ? ('Enter ' + refundCurrency + '. Converted from order currency '
                        + displayOrderCurrency + '. You can lower it, but not above this max.')
                    : (cancelSummary
                        ? 'Default is the policy suggested refund. You can lower it, but not above the order price with markup.'
                        : 'You can refund this amount or less. It cannot be higher than the order price with markup.')),
            note: isTravclicksRefund
                ? 'This credits the DMC wallet only (TravClicks → DMC). DMC still refunds the agent separately.'
                : (dmcCurrencyDiffers
                    ? ('Refund is recorded in ' + refundCurrency + ' (DMC base currency).')
                    : null),
            showDmcWalletConversion: isTravclicksRefund && !!dmcWalletCurrency,
            dmcWalletCurrency: dmcWalletCurrency,
            dmcWalletSgdRate: isFinite(dmcWalletSgdRate) ? dmcWalletSgdRate : null,
            sourceSgdRate: isFinite(refundSourceSgdRate) ? refundSourceSgdRate : null,
            hotelName: hotelName,
            refundPolicyHtml: hotelRefundPolicyHtml
        };

    showConfirmationModal(
        modalOptions.title,
        initiated
            ? (isTravclicksRefund
                ? 'Complete this refund and credit the amount to the DMC wallet.'
                : 'This refund is already initiated. Verify it to complete the service refund.')
            : initiateMessage,
        'warning',
        function(refundAmount, meta) {
            showLoadingModal(
                initiated
                    ? (isTravclicksRefund ? 'Crediting DMC Wallet' : 'Verifying Refund')
                    : (isTravclicksRefund ? 'Refunding to DMC Wallet' : 'Initiating Refund'),
                'Please wait...'
            );

            const originalContent = button ? button.innerHTML : '';
            if (button) {
                button.innerHTML = '<i class="ri-loader-line spinner-border spinner-border-sm me-1"></i>';
                button.disabled = true;
            }

            const payload = {
                tour_id: tourId,
                order_id: orderId,
                action: initiated ? 'verify' : 'initiate',
                transaction_id: meta?.transaction_id || '',
                remarks: meta?.remarks || '',
                _token: $('meta[name="csrf-token"]').attr('content')
            };
            if (!initiated) {
                payload.refund_amount = refundAmount;
            }

            $.ajax({
                url: '{{ route("bookings.process-order-refund-by-order") }}',
                method: 'POST',
                data: payload,
                success: function(response) {
                    hideModal();
                    if (response.success) {
                        showSuccessModal(
                            'Service Refund Updated',
                            response.message || 'Selected service marked as refunded.',
                            function() {
                                location.reload();
                            }
                        );
                    } else {
                        showErrorModal('Error Updating Service Refund', response.message || 'Unable to update refunded status.');
                        if (button) {
                            button.innerHTML = originalContent;
                            button.disabled = false;
                        }
                    }
                },
                error: function(xhr) {
                    hideModal();
                    let errorMessage = 'Error updating service refund. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showErrorModal('Error Updating Service Refund', errorMessage);
                    if (button) {
                        button.innerHTML = originalContent;
                        button.disabled = false;
                    }
                }
            });
        },
        modalOptions
    );
}

// Advanced Modal Functions
function showConfirmationModal(title, message, type, confirmCallback, options) {
    options = options || {};
    const modalTitle = options.title || title || 'Process Refund';
    const confirmLabel = options.confirmLabel || 'Process Refund';
    const verifyOnly = !!options.verifyOnly;
    const hasAmount = !verifyOnly && options.maxAmount !== undefined && options.maxAmount !== null;
    const maxAmount = hasAmount ? (parseFloat(options.maxAmount) || 0) : 0;
    const amountValue = (hasAmount || verifyOnly) ? (parseFloat(options.amount) || 0) : 0;
    const amountCurrency = options.currency || '';
    const priceLabel = options.priceLabel || 'Order price with markup';
    const helpText = options.helpText || 'You can refund this amount or less. It cannot be higher than the order price with markup.';
    const showDmcWalletConversion = !!options.showDmcWalletConversion && !!(options.dmcWalletCurrency || '');
    const dmcWalletCurrency = (options.dmcWalletCurrency || '').toUpperCase();
    const dmcWalletSgdRate = options.dmcWalletSgdRate != null ? parseFloat(options.dmcWalletSgdRate) : NaN;
    const sourceSgdRate = options.sourceSgdRate != null ? parseFloat(options.sourceSgdRate) : NaN;
    const dmcConversionBlock = showDmcWalletConversion ? `
                                <label class="form-label fw-semibold mb-1 mt-3">Amount to DMC wallet</label>
                                <div class="input-group">
                                    <span class="input-group-text">${dmcWalletCurrency}</span>
                                    <input type="text" class="form-control bg-light" id="dmcWalletCreditPreview" readonly tabindex="-1" value="">
                                </div>
                                <div class="form-text" id="dmcWalletCreditHelp">Converted country-wise via countries.current_rate. This amount is credited to the DMC wallet.</div>
    ` : '';
    const refundPolicyRaw = String(options.refundPolicyHtml || '').trim();
    const refundPolicyHotel = String(options.hotelName || '').trim();
    const refundPolicyCollapseId = 'refundPolicyCollapse_' + Date.now();
    const refundPolicyBlock = refundPolicyRaw ? `
                            <div class="text-start mb-3 refund-policy-compact">
                                <button type="button"
                                    class="btn btn-sm btn-outline-secondary w-100 d-flex justify-content-between align-items-center"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#${refundPolicyCollapseId}"
                                    aria-expanded="true"
                                    aria-controls="${refundPolicyCollapseId}">
                                    <span class="text-start">
                                        <i class="ri-file-list-3-line me-1"></i>
                                        <strong>Hotel refund policy</strong>
                                        ${refundPolicyHotel ? `<span class="text-muted"> · ${$('<div>').text(refundPolicyHotel).html()}</span>` : ''}
                                    </span>
                                    <i class="ri-arrow-down-s-line ms-2"></i>
                                </button>
                                <div class="collapse show" id="${refundPolicyCollapseId}">
                                    <div class="border rounded mt-2 p-2 bg-light refund-policy-body"
                                         style="max-height: 150px; overflow-y: auto; font-size: 11.5px; line-height: 1.45; text-align: left;">
                                        ${refundPolicyRaw}
                                    </div>
                                </div>
                            </div>
    ` : '';
    const prefillTxnId = $('<div>').text(String(options.transactionId || '')).html();
    const prefillRemarks = $('<div>').text(String(options.remarks || '')).html();
    const metaFieldsBlock = `
                            <div class="text-start mb-3">
                                <label for="refundTransactionIdInput" class="form-label fw-semibold mb-1">
                                    Transaction ID <span class="text-muted fw-normal">(optional)</span>
                                </label>
                                <input type="text"
                                    class="form-control"
                                    id="refundTransactionIdInput"
                                    maxlength="191"
                                    placeholder="Bank / payment reference"
                                    value="${prefillTxnId}">
                            </div>
                            <div class="text-start mb-3">
                                <label for="refundRemarksInput" class="form-label fw-semibold mb-1">
                                    Remarks <span class="text-muted fw-normal">(optional)</span>
                                </label>
                                <textarea class="form-control"
                                    id="refundRemarksInput"
                                    rows="2"
                                    maxlength="2000"
                                    placeholder="Refund remarks...">${prefillRemarks}</textarea>
                            </div>
    `;
    const fullOrderPrice = options.fullOrderPrice != null ? parseFloat(options.fullOrderPrice) : NaN;
    const dmcMarkupAmount = options.dmcMarkupAmount != null ? parseFloat(options.dmcMarkupAmount) : NaN;
    const dmcMarkupLabel = String(options.dmcMarkupLabel || '').trim();
    const hideMaxPriceDisplay = !!options.hideMaxPriceDisplay;
    const showMarkupBreakdown = hasAmount
        && !hideMaxPriceDisplay
        && isFinite(dmcMarkupAmount)
        && dmcMarkupAmount > 0
        && isFinite(fullOrderPrice);
    const markupBreakdownBlock = showMarkupBreakdown ? `
                                <div class="border rounded p-2 mb-3 bg-light text-start small">
                                    <div class="d-flex justify-content-between gap-2">
                                        <span>Order price with markup</span>
                                        <span>${amountCurrency ? amountCurrency + ' ' : ''}${fullOrderPrice.toFixed(2)}</span>
                                    </div>
                                    <div class="d-flex justify-content-between gap-2 text-danger">
                                        <span>DMC markup${dmcMarkupLabel ? ' (' + $('<div>').text(dmcMarkupLabel).html() + ')' : ''} · already paid to DMC</span>
                                        <span>−${amountCurrency ? amountCurrency + ' ' : ''}${dmcMarkupAmount.toFixed(2)}</span>
                                    </div>
                                    <div class="d-flex justify-content-between gap-2 fw-semibold mt-1 pt-1 border-top">
                                        <span>${priceLabel}</span>
                                        <span>${amountCurrency ? amountCurrency + ' ' : ''}${maxAmount.toFixed(2)}</span>
                                    </div>
                                </div>
    ` : (hideMaxPriceDisplay ? '' : `
                                <label class="form-label fw-semibold mb-1">${priceLabel}</label>
                                <div class="form-control bg-light mb-3">${amountCurrency ? amountCurrency + ' ' : ''}${maxAmount.toFixed(2)}</div>
    `);
    const amountBlock = verifyOnly ? `
                            <div class="alert alert-warning border-0 text-start mb-3" style="background-color: #fff7ed;">
                                <strong>Refund already initiated</strong>
                                <div class="mt-1">${amountCurrency ? amountCurrency + ' ' : ''}${amountValue.toFixed(2)}</div>
                                <div class="small mt-1">The amount stays as entered. This step only verifies the refund.</div>
                            </div>
                            ${metaFieldsBlock}
    ` : (hasAmount ? `
                            <div class="text-start mb-3">
                                ${markupBreakdownBlock}
                                <label for="refundAmountInput" class="form-label fw-semibold mb-1">Refund amount</label>
                                <div class="input-group">
                                    ${amountCurrency ? `<span class="input-group-text">${amountCurrency}</span>` : ''}
                                    <input type="number" class="form-control" id="refundAmountInput" min="0" max="${maxAmount.toFixed(2)}" step="0.01" value="${Math.min(amountValue, maxAmount).toFixed(2)}" inputmode="decimal">
                                </div>
                                <div class="form-text">${helpText}</div>
                                ${dmcConversionBlock}
                                <div class="text-danger small mt-1 d-none" id="refundAmountError"></div>
                            </div>
                            ${metaFieldsBlock}
    ` : '');

    const modalHtml = `
        <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                    <div class="modal-body text-center py-5 px-4">
                        <!-- Refund Icon with Animation -->
                        <div class="refund-icon-wrapper mb-4">
                            <div class="refund-circle">
                                <i class="ri-money-dollar-circle-line ri-32px text-white"></i>
                            </div>
                        </div>
                        
                        <!-- Refund Title -->
                        <h4 class="fw-bold ${verifyOnly ? 'text-warning' : 'text-primary'} mb-3" id="confirmationModalLabel">${modalTitle}</h4>
                        
                        <!-- Refund Process Message -->
                        <div class="refund-message mb-4">
                            
                            <p class="text-muted mb-3">${message || 'You are about to process a refund for this tour booking. This will update the tour status and initiate the refund process.'}</p>
                            ${refundPolicyBlock}
                            ${amountBlock}
                            <div class="alert ${verifyOnly ? 'alert-warning' : 'alert-info'} border-0" style="background-color: ${verifyOnly ? '#fff7ed' : '#e3f2fd'};">
                                <i class="ri-information-line me-2"></i>
                                <strong>Note:</strong> ${options.note
                                    ? options.note
                                    : (verifyOnly
                                    ? 'Verification does not ask for the amount again.'
                                    : (hasAmount
                                    ? 'After you initiate, this amount dialog will not open again.'
                                    : 'This action will mark the tour as "Refunded" and cannot be undone.'))}
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-flex gap-3 justify-content-center">
                            <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-dismiss="modal">
                                <i class="ri-close-line me-2"></i>Cancel
                            </button>
                            <button type="button" class="btn ${verifyOnly ? 'btn-warning' : 'btn-primary'} px-4 py-2" id="confirmButton">
                                <i class="ri-check-line me-2"></i>${confirmLabel}
                            </button>
                            @if(
                                in_array(auth()->user()->role_id, [36, 126, 127])
                            )
                            <button type="button" class="btn btn-primary px-4 py-2" id="declineButton">
                                    <i class="ri-check-line me-2"></i>Decline
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#confirmationModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Add custom CSS for enhanced styling
    if (!$('#confirmationModalStyles').length) {
        $('head').append(`
            <style id="confirmationModalStyles">
                .refund-icon-wrapper {
                    position: relative;
                    display: inline-block;
                }
                
                .refund-circle {
                    width: 80px;
                    height: 80px;
                    background: linear-gradient(135deg, #007bff, #0056b3);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    box-shadow: 0 8px 25px rgba(0, 123, 255, 0.3);
                    animation: refundPulse 0.6s ease-out;
                }
                
                @keyframes refundPulse {
                    0% { transform: scale(0.8); opacity: 0; }
                    50% { transform: scale(1.1); }
                    100% { transform: scale(1); opacity: 1; }
                }
                
                .refund-circle i {
                    font-size: 2.5rem;
                    animation: iconAppear 0.8s ease-out 0.3s both;
                }
                
                @keyframes iconAppear {
                    0% { transform: scale(0) rotate(-45deg); opacity: 0; }
                    100% { transform: scale(1) rotate(0deg); opacity: 1; }
                }
                
                .refund-message {
                    max-width: 440px;
                    margin: 0 auto;
                }
                
                #confirmationModal .modal-content {
                    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                }
                
                #confirmationModal .modal-dialog {
                    max-width: 540px;
                }

                .refund-policy-compact .refund-policy-body p {
                    margin-bottom: 0.35rem;
                }
                .refund-policy-compact .refund-policy-body ol,
                .refund-policy-compact .refund-policy-body ul {
                    padding-left: 1.1rem;
                    margin-bottom: 0.35rem;
                }
                .refund-policy-compact .refund-policy-body img {
                    max-width: 100%;
                    height: auto;
                }
                .refund-policy-compact .refund-policy-body * {
                    max-width: 100%;
                    word-break: break-word;
                }
                
                #confirmationModal .btn {
                    border-radius: 8px;
                    font-weight: 500;
                    transition: all 0.3s ease;
                }
                
                #confirmationModal .btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                }
                
                #confirmationModal .alert {
                    border-radius: 8px;
                    font-size: 0.9rem;
                }
            </style>
        `);
    }
    
    // Show modal
    $('#confirmationModal').modal('show');

    const convertRefundToDmcWallet = function(sourceAmount) {
        const amount = Math.max(0, parseFloat(sourceAmount) || 0);
        const sourceCur = (amountCurrency || 'SGD').toUpperCase();
        const targetCur = dmcWalletCurrency;
        if (!targetCur) {
            return { ok: false, text: '—', help: 'DMC wallet currency is not configured.' };
        }
        if (sourceCur === targetCur) {
            return {
                ok: true,
                text: amount.toFixed(2),
                help: sourceCur === 'SGD'
                    ? 'SGD refund — same as DMC wallet currency. This amount is credited to the DMC wallet.'
                    : 'Same currency as the refund amount. This amount is credited to the DMC wallet.'
            };
        }
        if (!isFinite(sourceSgdRate) || sourceSgdRate <= 0) {
            return {
                ok: false,
                text: 'Rate unavailable',
                help: 'No countries.current_rate for ' + sourceCur + '. Sync currency rates before refunding.'
            };
        }
        if (!isFinite(dmcWalletSgdRate) || dmcWalletSgdRate <= 0) {
            return {
                ok: false,
                text: 'Rate unavailable',
                help: 'No countries.current_rate for DMC currency ' + targetCur + '. Sync currency rates before refunding.'
            };
        }
        const sgdAmount = sourceCur === 'SGD' ? amount : (amount / sourceSgdRate);
        const creditAmount = targetCur === 'SGD' ? sgdAmount : (sgdAmount * dmcWalletSgdRate);
        const pathLabel = sourceCur === 'SGD'
            ? ('SGD → ' + targetCur)
            : (targetCur === 'SGD' ? (sourceCur + ' → SGD') : (sourceCur + ' → SGD → ' + targetCur));
        return {
            ok: true,
            text: creditAmount.toFixed(2),
            help: 'Converted via countries.current_rate ('
                + pathLabel
                + '). This amount is credited to the DMC wallet.'
        };
    };

    const refreshDmcWalletPreview = function() {
        if (!showDmcWalletConversion) return;
        const input = document.getElementById('refundAmountInput');
        const preview = document.getElementById('dmcWalletCreditPreview');
        const help = document.getElementById('dmcWalletCreditHelp');
        if (!preview) return;
        const result = convertRefundToDmcWallet(input ? input.value : amountValue);
        preview.value = result.text;
        if (help) help.textContent = result.help;
    };
    
    // Handle confirm button click
    $('#confirmButton').off('click').on('click', function() {
        let refundAmount = null;
        if (hasAmount) {
            const input = document.getElementById('refundAmountInput');
            const error = document.getElementById('refundAmountError');
            const entered = input ? parseFloat(input.value) : NaN;
            const showAmountError = function(text) {
                if (!error) return;
                error.textContent = text;
                error.classList.remove('d-none');
            };
            if (!isFinite(entered) || entered < 0) {
                showAmountError('Enter a refund amount of 0 or more.');
                return;
            }
            if (entered - maxAmount > 0.009) {
                if (input) input.value = maxAmount.toFixed(2);
                showAmountError('Refund amount cannot be higher than the order price with markup.');
                return;
            }
            if (showDmcWalletConversion) {
                const preview = convertRefundToDmcWallet(entered);
                if (!preview.ok) {
                    showAmountError(preview.help);
                    return;
                }
            }
            if (error) error.classList.add('d-none');
            refundAmount = entered.toFixed(2);
        }
        const meta = {
            transaction_id: (document.getElementById('refundTransactionIdInput')?.value || '').trim(),
            remarks: (document.getElementById('refundRemarksInput')?.value || '').trim(),
        };
        $('#confirmationModal').modal('hide');
        if (confirmCallback) {
            confirmCallback(refundAmount, meta);
        }
    });

    if (hasAmount) {
        $('#refundAmountInput').on('input', function() {
            const entered = parseFloat(this.value);
            if (isFinite(entered) && entered - maxAmount > 0.009) {
                this.value = maxAmount.toFixed(2);
            }
            refreshDmcWalletPreview();
        });
        refreshDmcWalletPreview();
    }
    
    // Clean up when modal is hidden
    $('#confirmationModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

// Helper function to get tour ID from the button
function getTourIdFromButton() {
    const button = event.target.closest('button');
    if (button && button.onclick) {
        const onclickStr = button.onclick.toString();
        const match = onclickStr.match(/processRefund\((\d+)\)|processOrderRefund\((\d+),/);
        if (match) {
            return match[1] || match[2] || 'N/A';
        }
    }
    return 'N/A';
}

function showLoadingModal(title, message) {
    const modalHtml = `
        <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-body text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5 class="fw-bold mb-2">${title}</h5>
                        <p class="text-muted mb-0">${message}</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#loadingModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Show modal
    $('#loadingModal').modal('show');
}

function showSuccessModal(title, message, callback) {
    const modalHtml = `
        <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                    <div class="modal-body text-center py-5 px-4">
                        <!-- Success Icon with Animation -->
                        <div class="success-icon-wrapper mb-4">
                            <div class="success-circle">
                                <i class="ri-check-line ri-32px text-white"></i>
                            </div>
                        </div>
                        
                        <!-- Success Title -->
                        <h4 class="fw-bold text-success mb-3" id="successModalLabel">${title}</h4>
                        
                        <!-- Success Message -->
                        <p class="text-muted mb-4 fs-6">${message}</p>
                        
                        <!-- Progress Bar -->
                        <div class="progress-wrapper mb-3">
                            <div class="progress" style="height: 6px; border-radius: 3px;">
                                <div class="progress-bar bg-success" id="successProgressBar" role="progressbar" style="width: 0%; transition: width 0.1s ease;"></div>
                            </div>
                        </div>
                        
                        <!-- Auto-close Timer -->
                        <p class="text-muted small mb-0">
                            <i class="ri-time-line me-1"></i>
                            Auto-closing in <span id="countdownTimer" class="fw-bold text-success">3</span> seconds
                        </p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#successModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Show modal
    $('#successModal').modal('show');
    
    // Add custom CSS for enhanced styling
    if (!$('#successModalStyles').length) {
        $('head').append(`
            <style id="successModalStyles">
                .success-icon-wrapper {
                    position: relative;
                    display: inline-block;
                }
                
                .success-circle {
                    width: 80px;
                    height: 80px;
                    background: linear-gradient(135deg, #28a745, #20c997);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
                    animation: successPulse 0.6s ease-out;
                }
                
                @keyframes successPulse {
                    0% { transform: scale(0.8); opacity: 0; }
                    50% { transform: scale(1.1); }
                    100% { transform: scale(1); opacity: 1; }
                }
                
                .success-circle i {
                    font-size: 2.5rem;
                    animation: checkmarkAppear 0.8s ease-out 0.3s both;
                }
                
                @keyframes checkmarkAppear {
                    0% { transform: scale(0) rotate(-45deg); opacity: 0; }
                    100% { transform: scale(1) rotate(0deg); opacity: 1; }
                }
                
                .progress-wrapper {
                    max-width: 300px;
                    margin: 0 auto;
                }
                
                .progress {
                    background-color: #e9ecef;
                    overflow: visible;
                }
                
                .progress-bar {
                    background: linear-gradient(90deg, #28a745, #20c997);
                    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
                }
                
                #successModal .modal-content {
                    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                }
                
                #successModal .modal-dialog {
                    max-width: 450px;
                }
            </style>
        `);
    }
    
    // Start countdown timer and progress bar
    let countdown = 3;
    const countdownElement = document.getElementById('countdownTimer');
    const progressBar = document.getElementById('successProgressBar');
    
    const timer = setInterval(() => {
        countdown--;
        if (countdownElement) countdownElement.textContent = countdown;
        if (progressBar) progressBar.style.width = ((3 - countdown) / 3 * 100) + '%';
        
        if (countdown <= 0) {
            clearInterval(timer);
            // Auto-close modal
            $('#successModal').modal('hide');
            // Wait for modal to close, then refresh page
            setTimeout(() => {
                if (callback) {
                    callback();
                }
            }, 300);
        }
    }, 1000);
    
    // Clean up when modal is hidden
    $('#successModal').on('hidden.bs.modal', function() {
        clearInterval(timer);
        $(this).remove();
    });
}

function showErrorModal(title, message) {
    const modalHtml = `
        <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-0 pb-2">
                        <div class="d-flex align-items-center">
                            <div class="icon-wrapper me-3">
                                <i class="ri-error-warning-line ri-24px text-danger"></i>
                            </div>
                            <h5 class="modal-title fw-bold text-danger" id="errorModalLabel">${title}</h5>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <p class="mb-0">${message}</p>
                    </div>
                    <div class="modal-footer border-0 pt-2">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#errorModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Show modal
    $('#errorModal').modal('show');
    
    // Clean up when modal is hidden
    $('#errorModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

function hideModal() {
    // Hide all custom modals
    $('#loadingModal, #confirmationModal, #successModal, #errorModal').modal('hide');
}
</script>
@endsection

@extends('layouts.datatablejs')