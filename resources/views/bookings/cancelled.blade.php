@extends('layouts.layout')
@section('title', 'Cancelled Bookings')
@extends('layouts.datatablecss')

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
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

    /* Page background - match actual */
    .cancelled-bookings-page { background-color: #f8f9fa !important; min-height: 100vh; padding-bottom: 2rem !important; }
    .cancelled-bookings-page .card { background-color: #fff; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }

    /* Compact table styles (aligned with actual) */
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
<div class="container-xxl flex-grow-1 container-p-y cancelled-bookings-page">
    <!-- Header -->
    <!-- Compact Header + Stats Bar -->
    <div class="new-enq-header-bar p-3 mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <h4 class="fw-bold mb-0" style="font-size: 1.25rem;">
                    <i class="ri-close-circle-line me-2 text-danger"></i>
                    <span class="text-muted fw-light">Bookings /</span> Cancelled Bookings
                </h4>
                <span class="text-muted d-none d-md-inline" style="font-size: 0.875rem;">Manage cancelled bookings and track cancellation reasons</span>
                <span class="badge bg-light text-danger border border-danger border-opacity-25 px-2 py-1" style="font-size: 0.75rem;">
                    <i class="ri-close-circle-line me-1"></i><span id="rangeCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</span> <span id="rangeLabel">{{ date('F') }}</span>
                </span>
            </div>
            <div class="row g-2 new-enq-stats-grid flex-grow-1">
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-danger rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-close-circle-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statCancelledCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</span><span class="stat-label text-muted" id="statCancelledLabel">{{ date('F') }} Cancelled</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-primary rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-eye-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statProspectCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->where('tour_status', 'LIKE', 'Cancel - Prospect')->count() }}</span><span class="stat-label text-muted" id="statProspectLabel">{{ date('F') }} Prospect</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-secondary rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-time-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statTentativeCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->where('tour_status', 'LIKE', 'Cancel - Tentative')->count() }}</span><span class="stat-label text-muted" id="statTentativeLabel">{{ date('F') }} Tentative</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-success rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-checkbox-circle-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statConfirmedCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->where('tour_status', 'LIKE', 'Cancel - Confirmed')->count() }}</span><span class="stat-label text-muted" id="statConfirmedLabel">{{ date('F') }} Confirmed</span></div>
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
                    <label class="form-label mb-0 small text-muted">Cancellation Status</label>
                    <select class="form-select form-select-sm" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="Prospect">Cancel - Prospect</option>
                        <option value="Tentative">Cancel - Tentative</option>
                        <option value="New Enquiry">Cancel - New Enquiry</option>
                        <option value="Confirmed">Cancel - Confirmed</option>
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
            <h5 class="mb-0">Cancelled Bookings List</h5>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-danger btn-sm dropdown-toggle" type="button" id="exportDropdown"
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
                        <col style="width: 16%">
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
                            <th class="th-tooltip" data-tooltip="Tour Details">Tour Details</th>
                            <th class="th-tooltip" data-tooltip="Agent">Agent</th>
                            <th class="th-tooltip" data-tooltip="Cancellation Status">Cancellation Status</th>
                            <th class="th-tooltip" data-tooltip="Cancelled Date">Cancelled Date</th>
                            <th class="th-tooltip" data-tooltip="Actions">Actions</th>
                            <th class="th-tooltip" data-tooltip="Created">Created</th>
                            <th class="th-tooltip" data-tooltip="Auto Cancel Date">Auto Cancel Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tours as $key => $tour)
                        <tr 
                            class="table-danger"
                            data-created-at="{{ optional($tour->created_at)->toDateString() }}"
                            data-updated-at="{{ optional($tour->updated_at)->toDateString() }}"
                            data-adult="{{ $tour->adult ?? 0 }}"
                            data-child="{{ $tour->child ?? 0 }}"
                            data-cancellation-status="{{ 
                                str_contains($tour->tour_status, 'Cancel - Definite') ? 'Definite' : 
                                (str_contains($tour->tour_status, 'Cancel - Prospect') ? 'Prospect' : 
                                (str_contains($tour->tour_status, 'Cancel - Tentative') ? 'Tentative' : 
                                (str_contains($tour->tour_status, 'Cancel - New Enquiry') ? 'New Enquiry' : 
                                (str_contains($tour->tour_status, 'Cancel - Confirmed') ? 'Confirmed' : 'Other'))))
                            }}"
                        >
                            <td>{{ $key + 1 }}</td>
                            <td class="align-top">
                                <div class="d-flex flex-column gap-1">
                                    <strong class="text-danger">{{ $tour->display_id }}</strong>
                                    <small class="text-muted">Tour ID: #{{ $tour->tour_id }}</small>
                                    @if($tour->multi_enq_id)
                                        <small class="text-info">Multi: {{ $tour->multi_enq_id }}</small>
                                    @endif
                                    @if($tour->tour_type)
                                        @php
                                            $tourTypeLower = strtolower($tour->tour_type);
                                            $bgColor = $tourTypeLower === 'group' ? '#7c3aed' : '#059669';
                                            $textColor = '#ffffff';
                                            $badgeWidth = $tourTypeLower === 'group' ? '60px' : '40px';
                                        @endphp
                                        <span class="d-inline-block px-2 py-1 rounded"
                                              style="background: {{ $bgColor }}; color: {{ $textColor }}; font-weight: 600; font-size: 0.7rem; text-align: left; letter-spacing: 0.3px; text-transform: uppercase; width: {{ $badgeWidth }}; display: inline-block;">
                                            {{ $tour->tour_type }}
                                        </span>
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
                                            @if($tour->check_out_time)<span class="ms-1"><strong>
                                            <br>    
                                           Out:</strong> {{ \Carbon\Carbon::parse($tour->check_out_time)->format('M d, Y') }}</span>@endif
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
                            <td>
                                @if(str_contains($tour->tour_status, 'Cancel - Prospect') || str_contains($tour->tour_status, 'Cancel-Prospect'))
                                    <span class="badge bg-primary">
                                        <i class="ri-eye-line me-1"></i>Prospect
                                    </span>
                                @elseif(str_contains($tour->tour_status, 'Cancel - Tentative') || str_contains($tour->tour_status, 'Cancel-Tentative'))
                                    <span class="badge bg-secondary">
                                        <i class="ri-time-line me-1"></i>Tentative
                                    </span>
                                @elseif(str_contains($tour->tour_status, 'Cancel - New Enquiry') || str_contains($tour->tour_status, 'Cancel-New Enquiry'))
                                    <span class="badge bg-dark">
                                        <i class="ri-file-list-line me-1"></i>New Enquiry
                                    </span>
                                @elseif(str_contains($tour->tour_status, 'Cancel - Confirmed') || str_contains($tour->tour_status, 'Cancel-Confirmed'))
                                    <span class="badge bg-success">
                                        <i class="ri-checkbox-circle-line me-1"></i>Confirmed
                                    </span>
                                @elseif(str_contains($tour->tour_status, 'Cancel - Definite') || str_contains($tour->tour_status, 'Cancel-Definite'))
                                    <span class="badge bg-danger">
                                        <i class="ri-checkbox-circle-line me-1"></i>Definite
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="ri-close-circle-line me-1"></i>{{ $tour->tour_status }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <small><strong>Cancelled:</strong> {{ \Carbon\Carbon::parse($tour->updated_at)->format('D, M d, Y') }}</small>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($tour->updated_at)->format('h:i A') }}</small>
                                </div>
                            </td>
                            <td class="align-top col-actions">
                                <div class="actions-icons-wrap">
                                    <a href="{{ route('bookings.view-tour', Crypt::encrypt($tour->tour_id)) }}"
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
                        {{-- <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="ri-close-circle-line ri-48px text-muted mb-2"></i>
                                    <h6 class="text-muted">No cancelled bookings</h6>
                                    <p class="text-muted mb-0">All bookings are active or in other stages.</p>
                                </div>
                            </td>
                        </tr> --}}
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function filterTable() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
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
        const destination = (row.cells[1]?.textContent || '').toLowerCase();
        const agent = (row.cells[2]?.querySelector('.agent-name-line span')?.textContent?.trim() || row.cells[2]?.textContent || '').toLowerCase();
        const createdBy = (row.cells[6]?.querySelector('.created-by-line span')?.textContent?.trim() || row.cells[6]?.textContent || '').toLowerCase();
        const status = row.cells[3]?.querySelector('.badge')?.textContent.toLowerCase() || '';
        const cancelledDate = row.cells[4]?.textContent.toLowerCase() || '';
        const createdAt = row.getAttribute('data-created-at');
        const updatedAt = row.getAttribute('data-updated-at');
        
        let show = true;
        
        // Search filter - check tour details, destination, agent, and created by
        if (searchTerm && !tourDetails.includes(searchTerm) && 
            !destination.toLowerCase().includes(searchTerm) &&
            !agent.toLowerCase().includes(searchTerm) &&
            !createdBy.toLowerCase().includes(searchTerm)) {
            show = false;
        }
        
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
            // If dates selected but timestamps missing, hide row
            show = false;
        }
        
        if (statusFilter && !status.includes(statusFilter.toLowerCase())) {
            show = false;
        }
        
        // Destination filter - Tour Details (cells[1]) contains destination
        if (destinationFilter) {
            const destText = (row.cells[1]?.textContent || '').toLowerCase();
            if (!destText.includes(destinationFilter.toLowerCase())) {
                show = false;
            }
        }
        
        if (agentFilter && agent !== agentFilter.toLowerCase().trim()) {
            show = false;
        }
        
        if (timeFilter) {
            const cancelledDateMatch = cancelledDate.match(/(\w+), (\w+) (\d+), (\d+)/);
            if (cancelledDateMatch) {
                const cancelledDateObj = new Date(cancelledDateMatch[0]);
                const now = new Date();
                const diffTime = Math.abs(now - cancelledDateObj);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                
                if (timeFilter === 'this_week' && diffDays > 7) {
                    show = false;
                } else if (timeFilter === 'last_week' && (diffDays <= 7 || diffDays > 14)) {
                    show = false;
                } else if (timeFilter === 'this_month' && diffDays > 30) {
                    show = false;
                } else if (timeFilter === 'last_month' && (diffDays <= 30 || diffDays > 60)) {
                    show = false;
                }
            }
        }
        
        row.style.display = show ? '' : 'none';
    });

    // Update header/cards counts based on visible rows
    const visibleRows = Array.from(document.querySelectorAll('#toursTable tbody tr')).filter(r => r.style.display !== 'none' && r.cells.length > 1);
    const rangeCount = visibleRows.length;
    const prospectCount = visibleRows.filter(r => r.getAttribute('data-cancellation-status') === 'Prospect').length;
    const tentativeCount = visibleRows.filter(r => r.getAttribute('data-cancellation-status') === 'Tentative').length;
    const newEnquiryCount = visibleRows.filter(r => r.getAttribute('data-cancellation-status') === 'New Enquiry').length;
    const confirmedCount = visibleRows.filter(r => r.getAttribute('data-cancellation-status') === 'Confirmed').length;
    const definiteCount = visibleRows.filter(r => r.getAttribute('data-cancellation-status') === 'Definite').length;

    // Update counts and labels
    const countEl = document.getElementById('rangeCount');
    const labelEl = document.getElementById('rangeLabel');
    const statCancelled = document.getElementById('statCancelledCount');
    const statCancelledLabel = document.getElementById('statCancelledLabel');
    const statProspect = document.getElementById('statProspectCount');
    const statProspectLabel = document.getElementById('statProspectLabel');
    const statTentative = document.getElementById('statTentativeCount');
    const statTentativeLabel = document.getElementById('statTentativeLabel');
    const statNewEnquiry = document.getElementById('statNewEnquiryCount');
    const statNewEnquiryLabel = document.getElementById('statNewEnquiryLabel');
    const statConfirmed = document.getElementById('statConfirmedCount');
    const statConfirmedLabel = document.getElementById('statConfirmedLabel');
    const statDefinite = document.getElementById('statDefiniteCount');
    const statDefiniteLabel = document.getElementById('statDefiniteLabel');

    if (countEl) countEl.textContent = rangeCount;
    if (statCancelled) statCancelled.textContent = rangeCount;
    if (statProspect) statProspect.textContent = prospectCount;
    if (statTentative) statTentative.textContent = tentativeCount;
    if (statNewEnquiry) statNewEnquiry.textContent = newEnquiryCount;
    if (statConfirmed) statConfirmed.textContent = confirmedCount;
    if (statDefinite) statDefinite.textContent = definiteCount;

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
        if (statCancelledLabel) statCancelledLabel.textContent = `Cancelled - ${label}`;
        if (statProspectLabel) statProspectLabel.textContent = `Prospect - ${label}`;
        if (statTentativeLabel) statTentativeLabel.textContent = `Tentative - ${label}`;
        if (statNewEnquiryLabel) statNewEnquiryLabel.textContent = `New Enquiry - ${label}`;
        if (statConfirmedLabel) statConfirmedLabel.textContent = `Confirmed - ${label}`;
        if (statDefiniteLabel) statDefiniteLabel.textContent = `Definite - ${label}`;
    } else {
        const month = new Date().toLocaleString('default', { month: 'long' });
        if (labelEl) labelEl.textContent = month;
        if (statCancelledLabel) statCancelledLabel.textContent = `${month} Cancelled`;
        if (statProspectLabel) statProspectLabel.textContent = `${month} Prospect`;
        if (statTentativeLabel) statTentativeLabel.textContent = `${month} Tentative`;
        if (statNewEnquiryLabel) statNewEnquiryLabel.textContent = `${month} New Enquiry`;
        if (statConfirmedLabel) statConfirmedLabel.textContent = `${month} Confirmed`;
        if (statDefiniteLabel) statDefiniteLabel.textContent = `${month} Definite`;
    }
}

function resetFilters() {
    const searchInput = document.getElementById('searchInput');
    const statusSelect = document.getElementById('statusFilter');
    const destinationSelect = document.getElementById('destinationFilter');
    const agentSelect = document.getElementById('agentFilter');
    const timeSelect = document.getElementById('timeFilter');
    const startDateInput = document.getElementById('startDateFilter');
    const endDateInput = document.getElementById('endDateFilter');
    const today = new Date().toISOString().split('T')[0];

    if (searchInput) searchInput.value = '';
    if (statusSelect) statusSelect.value = '';
    
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
    
    if (timeSelect) timeSelect.value = '';

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
    const destinationFilter = document.getElementById('destinationFilter');
    const agentFilter = document.getElementById('agentFilter');
    const timeFilter = document.getElementById('timeFilter');
    const startDateFilter = document.getElementById('startDateFilter');
    const endDateFilter = document.getElementById('endDateFilter');
    const today = new Date().toISOString().split('T')[0];
    
    // Add event listeners
    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (statusFilter) statusFilter.addEventListener('change', filterTable);
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
            // order: [[5, 'desc']], // Sort by Cancelled Date column (index 5) in descending order
            columnDefs: (function() {
                const headerTexts = $('#toursTable thead th').map(function() {
                    return $(this).text().trim();
                }).get();
                const colIndex = (name) => headerTexts.findIndex(t => t === name);
                const actionsIdx = colIndex('Actions');
                const guestsIdx = colIndex('Guests');
                const statusIdx = colIndex('Cancellation Status');

                return [
                    {
                        targets: [actionsIdx].filter(i => i >= 0),
                        orderable: false,
                        searchable: false
                    },
                    {
                        targets: [guestsIdx, statusIdx].filter(i => i >= 0),
                        orderable: false
                    }
                ];
            })(),
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

    // Global tooltip for table headers and action/service icons
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
</script>
@endsection

@extends('layouts.datatablejs')
