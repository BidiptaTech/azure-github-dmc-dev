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
        table-layout: fixed;
        width: 100% !important;
        margin-bottom: 0;
        background-color: #fff;
    }
    .dataTables_wrapper .dataTables_scroll .dataTables_scrollBody #toursTable,
    .dataTables_wrapper #toursTable { width: 100% !important; table-layout: fixed; }
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
    #toursTable .action-icon-badge i { font-size: 1rem; color: var(--action-color, #475569); }
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
                        <option value="Refund - Pending">Pending</option>
                        <option value="Refunded">Refunded</option>
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
                    <colgroup>
                        <col style="width: 2%">
                        <col style="width: 16%">
                        <col style="width: 10%">
                        <col style="width: 10%">
                        <col style="width: 12%">
                        <col style="width: 10%">
                        <col style="width: 14%">
                        <col style="width: 8%">
                        <col style="width: 8%">
                    </colgroup>
                    <thead class="table-light">
                        <tr>
                            <th class="th-tooltip" data-tooltip="#">#</th>
                            <th class="th-tooltip" data-tooltip="Tour Details">Tour Details</th>
                            <th class="th-tooltip" data-tooltip="Agent">Agent</th>
                            <th class="th-tooltip" data-tooltip="Refund Status">Refund Status</th>
                            <th class="th-tooltip" data-tooltip="Cancelled Date">Cancelled Date</th>
                            <!-- <th class="th-tooltip" data-tooltip="Refund Services">Cancel Services</th> -->
                            <th class="th-tooltip" data-tooltip="Actions">Actions</th>
                            <th class="th-tooltip" data-tooltip="Created">Created</th>
                            <th class="th-tooltip" data-tooltip="Auto Cancel Date">Auto Cancel Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tours as $key => $tour)
                        <tr 
                            class="{{ $tour->tour_status === 'Refund - Pending' ? 'table-danger' : 'table-success' }}"
                            data-created-at="{{ optional($tour->created_at)->toDateString() }}"
                            data-updated-at="{{ optional($tour->updated_at)->toDateString() }}"
                            data-adult="{{ $tour->adult ?? 0 }}"
                            data-child="{{ $tour->child ?? 0 }}"
                            data-tour-status="{{ $tour->tour_status }}"
                            data-agent="{{ $tour->agent_name }}"
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
                                <div class="d-flex flex-column">
                                    @if($tour->agent_name)
                                        <span class="agent-name-line"><i class="ri-user-line"></i><span>{{ $tour->agent_name }}</span></span>
                                        <span class="agent-company-line"><i class="ri-building-line"></i><span>{{ $tour->agent_company_name ?? 'N/A' }}</span></span>
                                    @else
                                        <span class="agent-empty"><i class="ri-user-unfollow-line"></i><span>No agent assigned</span></span>
                                    @endif
                                </div>
                            </td>
                            @if($tour->tour_status === 'Refund - Pending' || $tour->tour_status === 'Refunded')
                            <td>
                                @if($tour->tour_status === 'Refund - Pending')
                                    <span class="badge bg-danger text-start">
                                        <i class="ri-time-line me-1"></i>
                                        <span class="d-block">Cancel Tour</span>
                                        <span class="d-block">Refund Pending</span>
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="ri-check-circle-line me-1"></i>
                                        Refunded
                                    </span>
                                @endif
                            </td>
                            @else
                            <td class="align-top">
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
                                        ->filter(function ($order) {
                                            return (int)($order->is_refund ?? 0) === 1;
                                        })
                                        ->values();

                                    $hasPendingOrderRefund = $refundOrders->contains(function ($order) {
                                        return !((bool)($order->refunded ?? false));
                                    });
                                @endphp
                                <div class="d-flex flex-wrap gap-1">
                                    <!-- @if($hasPendingOrderRefund)
                                        <button type="button"
                                                class="action-icon-badge"
                                                style="--action-color: #16a34a;"
                                                data-tooltip="Mark Service Refunded"
                                                data-tour-id="{{ $tour->tour_id }}"
                                                onclick="processOrderRefund({{ $tour->tour_id }}, event)">
                                            <i class="ri-refund-2-line"></i>
                                        </button>
                                    @endif -->
                                    @forelse($refundOrders as $refundOrder)
                                        @php
                                            $refundType = $refundOrder->type ?? null;
                                            $serviceMeta = $refundServiceIcons[$refundType] ?? ['icon' => 'ri-service-line', 'label' => ucfirst(str_replace('_', ' ', (string)$refundType))];
                                            $isRefundCompleteForOrder = (bool)($refundOrder->refunded ?? false);
                                            $isOnHoldForFinance = (int)($refundOrder->is_verify ?? 0) === 2;
                                            $currentRoleId = (int)(auth()->user()->role_id ?? 0);
                                            $holdRoleIds = [33, 12, 37, 38];
                                            $isHoldLockedForRole33 = in_array($currentRoleId, $holdRoleIds, true) && $isOnHoldForFinance;
                                            // Always use primary key for action endpoints to avoid
                                            // mismatching records that share booking_id.
                                            $orderActionId = $refundOrder->id ?? null;
                                            $orderIdentifier = $refundOrder->booking_id ?? $refundOrder->id ?? null;
                                            $serviceTooltip = $serviceMeta['label']
                                                . ' ('
                                                . ($isRefundCompleteForOrder ? 'Refunded' : ($isOnHoldForFinance ? 'On Hold Payment' : 'Pending'))
                                                . ')'
                                                . ($orderIdentifier ? (' · Order: ' . $orderIdentifier) : '');
                                        @endphp
                                        <button type="button"
                                                class="action-icon-badge"
                                                style="--action-color: {{ $isRefundCompleteForOrder ? '#dc2626' : ($isOnHoldForFinance ? '#f59e0b' : '#7c3aed') }}; {{ $isRefundCompleteForOrder ? 'background:#fee2e2;border-color:#fecaca;' : ($isOnHoldForFinance ? 'background:#fff7ed;border-color:#fed7aa;' : '') }}"
                                                data-tooltip="{{ $serviceTooltip }}"
                                                data-tour-id="{{ $tour->tour_id }}"
                                                data-service-type="{{ $refundType }}"
                                                data-order-id="{{ $orderActionId }}"
                                                @if($isRefundCompleteForOrder || $isHoldLockedForRole33)
                                                    disabled
                                                    aria-disabled="true"
                                                @else
                                                    onclick="processSingleOrderRefund({{ $tour->tour_id }}, {{ (int)($orderActionId ?? 0) }}, event)"
                                                @endif>
                                            <i class="{{ $serviceMeta['icon'] }}"></i>
                                        </button>
                                    @empty
                                        <span class="text-muted small">N/A</span>
                                    @endforelse
                                </div>
                            </td>
                            @endif
                            <td>
                                <div class="d-flex flex-column">
                                    <small><strong>Cancelled:</strong> {{ \Carbon\Carbon::parse($tour->updated_at)->format('D, M d, Y') }}</small>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($tour->updated_at)->format('h:i A') }}</small>
                                </div>
                            </td>
                            
                            <td class="align-top col-actions">
                                <div class="actions-icons-wrap">
                                    <a href="{{ route('bookings.view-tour', ['tourId' => \Crypt::encrypt($tour->tour_id)]) }}"
                                       class="action-icon-badge" style="--action-color: #0369a1;" data-tooltip="Audit Trail">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    @if($tour->tour_status === 'Refund - Pending')
                                        <button type="button"
                                                class="action-icon-badge" style="--action-color: #047857;"
                                                onclick="processRefund({{ $tour->tour_id }})"
                                                data-tooltip="Process Refund">
                                            <i class="ri-money-dollar-circle-line"></i>
                                        </button>
                                    @else
                                        <!-- <span class="badge bg-success" title="Already Refunded">✓ Refunded</span> -->
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
                                        <span>{{ $tour->created_at->format('D, M d, Y') }} · {{ $tour->created_at->format('h:i A') }}</span>
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
                            <td colspan="9" class="text-center py-4">
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

            if (statusFilter && rowStatus !== statusFilter) {
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
                if (rowStatus === 'Refund - Pending') {
                    pendingCount++;
                }
                if (rowStatus === 'Refunded') {
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
function processRefund(tourId) {
    // Create advanced confirmation modal
    showConfirmationModal(
        'Process Refund Confirmation',
        'Are you sure you want to process the refund for this tour?<br><small class="text-muted">This action cannot be undone.</small>',
        'warning',
        function() {
            // Show loading modal
            showLoadingModal('Processing Refund', 'Please wait while we process the refund...');
            
            const button = event.target.closest('button');
            const originalContent = button.innerHTML;
            button.innerHTML = '<i class="ri-loader-line spinner-border spinner-border-sm me-1"></i>';
            button.disabled = true;

            $.ajax({
                url: '{{ route("bookings.process-refund") }}',
                method: 'POST',
                data: {
                    tour_id: tourId,
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
                        // Restore button state
                        button.innerHTML = originalContent;
                        button.disabled = false;
                    }
                },
                error: function(xhr) {
                    hideModal();
                    let errorMessage = 'Error processing refund. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showErrorModal('Error Processing Refund', errorMessage);
                    // Restore button state
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }
            });
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

// Mark a single refund order as refunded (orders.refunded = 1 for that order only)
function processSingleOrderRefund(tourId, orderId, event) {
    if (!orderId) return;

    showConfirmationModal(
        'Mark Service Refunded',
        `Mark this service (Order: <strong>#${orderId}</strong>) as refunded?<br><small class="text-muted">Only this order will be updated.</small>`,
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
                url: '{{ route("bookings.process-order-refund-by-order") }}',
                method: 'POST',
                data: {
                    tour_id: tourId,
                    order_id: orderId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
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
        }
    );
}

// Advanced Modal Functions
function showConfirmationModal(title, message, type, confirmCallback) {
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
                        <h4 class="fw-bold text-primary mb-3" id="confirmationModalLabel">Process Refund</h4>
                        
                        <!-- Refund Process Message -->
                        <div class="refund-message mb-4">
                            
                            <p class="text-muted mb-3">
                                You are about to process a refund for this tour booking. 
                                This will update the tour status and initiate the refund process.
                            </p>
                            <div class="alert alert-info border-0" style="background-color: #e3f2fd;">
                                <i class="ri-information-line me-2 text-info"></i>
                                <strong>Note:</strong> This action will mark the tour as "Refunded" and cannot be undone.
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-flex gap-3 justify-content-center">
                            <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-dismiss="modal">
                                <i class="ri-close-line me-2"></i>Cancel
                            </button>
                            <button type="button" class="btn btn-primary px-4 py-2" id="confirmButton">
                                <i class="ri-check-line me-2"></i>Process Refund
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
                    max-width: 400px;
                    margin: 0 auto;
                }
                
                #confirmationModal .modal-content {
                    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                }
                
                #confirmationModal .modal-dialog {
                    max-width: 500px;
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
    
    // Handle confirm button click
    $('#confirmButton').off('click').on('click', function() {
        $('#confirmationModal').modal('hide');
        if (confirmCallback) {
            confirmCallback();
        }
    });
    
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