@extends('layouts.layout')
@section('title', 'Attractions & Experiences')
@extends('layouts.datatablecss')

@section('css')
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<style>
    /* Premium Wrapper */
    .container-p-y > .card {
        border: 1px solid #d0d7e2;
        border-radius: 0.75rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06), 0 0 1px rgba(0, 0, 0, 0.08);
        background: linear-gradient(180deg, #ffffff 0%, #fbfcfe 100%);
        overflow: hidden;
    }

    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%);
        border-bottom: 1px solid #d0d7e2;
        margin-bottom: 0;
    }

    .page-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
        letter-spacing: -0.01em;
    }

    .page-subtitle {
        font-size: 12.5px;
        color: #64748b;
        margin: 0.15rem 0 0;
        font-weight: 500;
    }

    /* Toolbar */
    .page-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.85rem 1.25rem;
        background: #f8fafc;
        border-bottom: 1px solid #e9edf3;
    }

    .toolbar-actions {
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
    }

    .btn-premium {
        border-radius: 10px;
        font-weight: 700;
        font-size: 12.5px;
        padding: 0.45rem 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
    }

    .btn-premium.btn-primary {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        border: none;
    }

    .btn-premium.btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(99, 102, 241, 0.25);
    }

    .btn-premium.btn-warning {
        background: linear-gradient(135deg, #f59e0b, #f97316);
        border: none;
        color: #fff;
    }

    .btn-premium.btn-warning:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(249, 115, 22, 0.22);
        color: #fff;
    }

    /* Table polish */
    .table-shell {
        padding: 1rem 1.25rem 1.25rem;
        background: #fff;
    }

    .table-premium {
        border-collapse: separate !important;
        border-spacing: 0;
        overflow: hidden;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
        background: #fff;
    }

    .table thead th {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        color: #334155;
        font-weight: 800;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 0.55rem 0.75rem !important;
    }

    .table tbody td {
        vertical-align: middle;
        font-size: 12.5px;
        color: #334155;
        padding: 0.55rem 0.75rem !important;
    }

    /* Softer dividers + zebra + hover */
    .table-premium tbody tr {
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .table-premium tbody tr:nth-child(even) {
        background: #fbfdff;
    }

    .table-premium tbody tr:hover {
        background: #f4f7ff;
    }

    .table-premium > :not(caption) > * > * {
        border-bottom-color: #eef2f7;
    }

    /* Remove harsh outer borders from bootstrap bordered tables */
    .table-premium.table-bordered > :not(caption) > * {
        border-color: transparent;
    }

    /* DataTables controls - standard layout */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info {
        color: #64748b;
        font-size: 12.5px;
        font-weight: 600;
    }

    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
        width: 100%;
    }

    /* Left: Entries */
    .dataTables_wrapper .dataTables_length {
        display: flex;
        align-items: center;
        justify-content: flex-start;
    }

    .dataTables_wrapper .dataTables_length select {
        width: auto;
        min-width: 80px;
        padding: 0.32rem 0.55rem;
    }

    /* Right: Search */
    .dataTables_wrapper .dataTables_filter {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        text-align: right;
    }

    .dataTables_wrapper .dataTables_filter label {
        justify-content: flex-end;
    }

    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #dbe3ee;
        border-radius: 10px;
        padding: 0.35rem 0.6rem;
        font-size: 12.5px;
        outline: none;
        box-shadow: none;
    }

    .dataTables_wrapper .dataTables_filter input {
        width: 260px;
        max-width: 100%;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 10px !important;
        padding: 0.25rem 0.6rem !important;
        margin: 0 2px !important;
        border: 1px solid transparent !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
        color: #fff !important;
        border-color: transparent !important;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.25);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #eef2ff !important;
        color: #4338ca !important;
        border-color: #c7d2fe !important;
    }

    /* Hide default DataTables buttons (we use the custom Export dropdown) */
    .dataTables_wrapper .dt-buttons {
        display: none !important;
    }

    /* Status badges - standard subtle style */
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.28rem 0.55rem;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 800;
        letter-spacing: 0.02em;
        border: 1px solid transparent;
    }

    .badge-status .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }

    .badge-status.active {
        background: #ecfdf5;
        color: #065f46;
        border-color: #a7f3d0;
    }

    .badge-status.active .dot {
        background: #10b981;
    }

    .badge-status.inactive {
        background: #fef2f2;
        color: #7f1d1d;
        border-color: #fecaca;
    }

    .badge-status.inactive .dot {
        background: #ef4444;
    }

    /* Action buttons */
    .btn-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
        transition: all 0.2s ease;
    }

    .btn-icon:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.10);
    }

    .btn-icon-calendar {
        background: #eef2ff;
        border-color: #c7d2fe;
    }

    .btn-icon-edit {
        background: #ecfdf5;
        border-color: #d1fae5;
    }

    .btn-icon-delete {
        background: #fef2f2;
        border-color: #fecaca;
    }

    /* Calendar link */
    .calendar-link {
        color: #6366f1;
        text-decoration: none;
        font-weight: 600;
        font-size: 12.5px;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .calendar-link:hover {
        text-decoration: underline;
        color: #4f46e5;
    }

    .calendar-link i {
        font-size: 0.875rem;
    }

    /* Column width control */
    #attractionsTable th:nth-child(2),
    #attractionsTable td:nth-child(2) {
        width: 120px;
        min-width: 100px;
        max-width: 140px;
    }

    /* Master DMC & DMC columns - narrower */
    #attractionsTable .col-master-dmc,
    #attractionsTable .col-dmc {
        min-width: 100px;
        max-width: 130px;
    }

    /* Action column - compact, just enough for icons */
    #attractionsTable .col-action {
        min-width: 90px;
        width: 90px;
        white-space: nowrap;
    }

    /* Tighter gap between action icons */
    #attractionsTable .col-action,
    #attractionsTable td.col-action {
        display: table-cell;
    }

    #attractionsTable td.col-action .btn-icon + .btn-icon {
        margin-left: 3px;
    }

    /* Slightly smaller icon buttons in action column */
    #attractionsTable td.col-action .btn-icon {
        width: 28px;
        height: 28px;
    }

    /* Created At header - single line, slightly smaller */
    #attractionsTable thead th.col-created-at {
        font-size: 10.5px;
        white-space: nowrap;
    }

    /* Header tooltip */
    .th-tooltip {
        cursor: help;
    }


    /* Loading spinner animation for delete button */
    .spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    @media (max-width: 768px) {
        .page-header { padding: 0.9rem 1rem; }
        .page-toolbar { padding: 0.75rem 1rem; }
        .page-title { font-size: 1.2rem; }
    }
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <!-- Header -->
                <div class="page-header">
                    <div>
                        <h5 class="page-title">Attractions & Experiences</h5>
                        <p class="page-subtitle">Manage attractions, availability calendars, and status</p>
                    </div>
                    <div class="toolbar-actions">
                        @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 20)
                        @if(hasPermission('create attraction'))
                        <a href="{{ route('attraction.create') }}" class="btn btn-primary btn-premium">
                            <i class="fas fa-plus"></i> Add New Attraction
                        </a>
                        @endif
                        @endif

                        <div class="dropdown">
                            <button class="btn btn-warning btn-premium dropdown-toggle" type="button" id="exportDropdown"
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

                <!-- Toolbar (alerts only) -->
                <div class="page-toolbar">
                    <div style="width: 100%;">
                        <x-alert />
                    </div>
                </div>

                <div class="table-shell">
                <table class="datatables-basic table table-bordered table-premium" id="attractionsTable">
                    <thead>
                        <tr>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Serial Number">No</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Attraction Name">Name</th>
                            @php
                                $roleId = auth()->user()->role_id;
                            @endphp
                            @php
                                $hideRoles = [11, 20, 35, 130, 132, 133, 135, 136, 137, 138, 74, 93, 90, 139, 140];
                            @endphp

                            @if(in_array($roleId, [10, 19])) {{-- Master DMC or Virtual Master DMC --}}
                                <th class="th-tooltip col-dmc" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Destination Management Company">DMC</th>
                            @elseif(!in_array($roleId, $hideRoles)) {{-- Not DMC or Virtual DMC --}}
                                <th class="th-tooltip col-master-dmc" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Master DMC">Master DMC</th>
                                <th class="th-tooltip col-dmc" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Destination Management Company">DMC</th>
                            @endif

                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Location">Location</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Status">Status</th>
                            @if(auth()->user()->role_id == 1 || auth()->user()->userId == 2 || auth()->user()->role_id == 23 || auth()->user()->role_id == 35 || auth()->user()->role_id == 44 || auth()->user()->role_id == 74 || auth()->user()->role_id == 91 || auth()->user()->role_id == 93 || auth()->user()->role_id == 130 || auth()->user()->role_id == 132 || auth()->user()->role_id == 133 || auth()->user()->role_id == 135 || auth()->user()->role_id == 136 || auth()->user()->role_id == 137 || auth()->user()->role_id == 138 || hasPermission('edit attraction') || hasPermission('delete attraction'))
                                <th class="th-tooltip col-action" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Available Actions">Action</th>
                            @endif
                            <th class="th-tooltip col-created-at" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Created Date and Time">Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attractions as $key => $attraction)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $attraction->name ?? 'N/A' }}</span>
                                </td>
                                @php
                                    $roleId = auth()->user()->role_id;
                                @endphp
                                @php
                                    $hideRoles = [11, 20, 35, 130, 132, 133, 135, 136, 137, 138, 74, 93, 139, 140];
                                @endphp

                                @if(in_array($roleId, [10, 19])) {{-- Master DMC or Virtual Master DMC --}}
                                    @php
                                        $dmcIds = $attraction->getSelectedDmcIds();
                                        $dmcUsers = App\Models\User::whereIn('userId', $dmcIds)->get();
                                    @endphp
                                    <td class="col-dmc">
                                        @if($dmcUsers->count() > 0)
                                            {{ $dmcUsers->first()->company_name }}
                                            @if($dmcUsers->count() > 1)
                                                <br><a href="javascript:void(0)"
                                                       class="btn btn-outline-secondary btn-sm"
                                                       onclick="showDmcModal('{{ $attraction->attraction_id }}', 'dmc', {{ $dmcUsers->toJson() }})">
                                                    <small>+{{ $dmcUsers->count() - 1 }} More</small>
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                @elseif(!in_array($roleId, $hideRoles)) {{-- Not DMC or Virtual DMC --}}
                                    @php
                                        $dmcIds = $attraction->getSelectedDmcIds();
                                        $dmcUsers = App\Models\User::whereIn('userId', $dmcIds)->get();
                                        $masterDmcIds = $dmcUsers->pluck('master_dmc_id')->filter()->unique();
                                        $masterDmcUsers = App\Models\User::whereIn('userId', $masterDmcIds)->get();
                                    @endphp
                                    <td class="col-master-dmc">
                                        @if($masterDmcUsers->count() > 0)
                                            <span>{{ $masterDmcUsers->first()->company_name }}</span>
                                            @if($masterDmcUsers->count() > 1)
                                                <br><a href="javascript:void(0)"
                                                       class="btn btn-primary btn-sm text-white"
                                                       onclick="showDmcModal('{{ $attraction->attraction_id }}', 'master_dmc', {{ $masterDmcUsers->toJson() }})">
                                                    <small>+{{ $masterDmcUsers->count() - 1 }} More</small>
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-muted">No DMC assigned</span>
                                        @endif
                                    </td>
                                    <td class="col-dmc">
                                        @if($dmcUsers->count() > 0)
                                            <span>{{ $dmcUsers->first()->company_name }}</span>
                                            @if($dmcUsers->count() > 1)
                                                <br><a href="javascript:void(0)"
                                                       class="btn btn-primary btn-sm text-white"
                                                       onclick="showDmcModal('{{ $attraction->attraction_id }}', 'dmc', {{ $dmcUsers->toJson() }})">
                                                    <small>+{{ $dmcUsers->count() - 1 }} More</small>
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-muted">No DMC assigned</span>
                                        @endif
                                    </td>
                                @endif

                                <td>
                                    <span>{{ $attraction->location ?: 'N/A' }}</span>
                                </td>

                                <td>
                                    @if($attraction->is_active == 1)
                                        <span class="badge-status active"><span class="dot"></span>Active</span>
                                    @else
                                        <span class="badge-status inactive"><span class="dot"></span>Inactive</span>
                                    @endif
                                </td>

                                @if(auth()->user()->role_id == 1 || auth()->user()->userId == 2 || auth()->user()->role_id == 23 || auth()->user()->role_id == 35 || auth()->user()->role_id == 44 || auth()->user()->role_id == 74 || auth()->user()->role_id == 91 || auth()->user()->role_id == 130 || auth()->user()->role_id == 132 || auth()->user()->role_id == 133 || auth()->user()->role_id == 135 || auth()->user()->role_id == 136 || auth()->user()->role_id == 137 || auth()->user()->role_id == 138 || auth()->user()->role_id == 93 || auth()->user()->role_id == 139 || auth()->user()->role_id == 140 || hasPermission('edit attraction') || hasPermission('delete attraction'))
                                @if($attraction->status == 1)
                                <td class="col-action">
                                    <!-- Calendar Button -->
                                    <a href="{{ route('attraction.calendar', ['attraction_id' => Crypt::encrypt($attraction->attraction_id)]) }}"
                                       target="_blank"
                                       class="btn btn-icon btn-icon-calendar th-tooltip"
                                       data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="View Calendar">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#4f46e5">
                                            <path d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Zm0-480h560v-80H200v80Zm0 0v-80 80Zm280 240q-17 0-28.5-11.5T440-440q0-17 11.5-28.5T480-480q17 0 28.5 11.5T520-440q0 17-11.5 28.5T480-400Zm-160 0q-17 0-28.5-11.5T280-440q0-17 11.5-28.5T320-480q17 0 28.5 11.5T360-440q0 17-11.5 28.5T320-400Zm320 0q-17 0-28.5-11.5T600-440q0-17 11.5-28.5T640-480q17 0 28.5 11.5T680-440q0 17-11.5 28.5T640-400ZM480-240q-17 0-28.5-11.5T440-280q0-17 11.5-28.5T480-320q17 0 28.5 11.5T520-280q0 17-11.5 28.5T480-240Zm-160 0q-17 0-28.5-11.5T280-280q0-17 11.5-28.5T320-320q17 0 28.5 11.5T360-280q0 17-11.5 28.5T320-240Zm320 0q-17 0-28.5-11.5T600-280q0-17 11.5-28.5T640-320q17 0 28.5 11.5T680-280q0 17-11.5 28.5T640-240Z"/>
                                        </svg>
                                    </a>

                                    <!-- Edit Button -->
                                    @if(hasPermission('edit attraction'))
                                    <a href="{{ route('attraction.edit', Crypt::encrypt($attraction->attraction_id)) }}"
                                       class="btn btn-icon btn-icon-edit th-tooltip"
                                       data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit Attraction">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#059669">
                                            <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/>
                                        </svg>
                                    </a>
                                    @endif

                                    <!-- Delete Button -->
                                    @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                                    <button type="button"
                                            class="btn btn-icon btn-icon-delete th-tooltip"
                                            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete Attraction"
                                            onclick="deleteAttraction('{{ route('attraction.destroy', Crypt::encrypt($attraction->attraction_id)) }}', {{ json_encode($attraction->name) }})"
                                            id="delete-btn-{{ $attraction->attraction_id }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#dc2626">
                                            <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                        </svg>
                                    </button>
                                    @endif
                                </td>
                                @else
                                    <td>
                                        <!-- Calendar Button always visible -->
                                        <a href="{{ route('attraction.calendar', ['attraction_id' => Crypt::encrypt($attraction->attraction_id)]) }}"
                                           target="_blank"
                                           class="btn btn-icon btn-icon-calendar th-tooltip"
                                           data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="View Calendar">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#4f46e5">
                                                <path d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Zm0-480h560v-80H200v80Zm0 0v-80 80Zm280 240q-17 0-28.5-11.5T440-440q0-17 11.5-28.5T480-480q17 0 28.5 11.5T520-440q0 17-11.5 28.5T480-400Zm-160 0q-17 0-28.5-11.5T280-440q0-17 11.5-28.5T320-480q17 0 28.5 11.5T360-440q0 17-11.5 28.5T320-400Zm320 0q-17 0-28.5-11.5T600-440q0-17 11.5-28.5T640-480q17 0 28.5 11.5T680-440q0 17-11.5 28.5T640-400ZM480-240q-17 0-28.5-11.5T440-280q0-17 11.5-28.5T480-320q17 0 28.5 11.5T520-280q0 17-11.5 28.5T480-240Zm-160 0q-17 0-28.5-11.5T280-280q0-17 11.5-28.5T320-320q17 0 28.5 11.5T360-280q0 17-11.5 28.5T320-240Zm320 0q-17 0-28.5-11.5T600-280q0-17 11.5-28.5T640-320q17 0 28.5 11.5T680-280q0 17-11.5 28.5T640-240Z"/>
                                            </svg>
                                        </a>
                                        @if($attraction->status == 5)
                                            <span class="text-muted" style="font-size:11.5px;">Awaiting Admin approval</span>
                                        @elseif($attraction->status == 3)
                                            <span class="text-muted" style="font-size:11.5px;">Declined</span>
                                        @endif
                                    </td>
                                @endif
                                @endif

                                <td>
                                    <div class="d-flex flex-column">
                                        <span>{{ $attraction->created_at->format('D,  M d, Y') }}</span>
                                        <small class="text-muted">{{ $attraction->created_at->format('h:i A') }}</small>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DMC Companies Modal -->
<div class="modal fade" id="dmcCompaniesModal" tabindex="-1" role="dialog" aria-labelledby="dmcCompaniesModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dmcCompaniesModalLabel">DMC Companies</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeDmcModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="dmcCompaniesModalBody">
                <!-- Company names will be populated here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeDmcModal()">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Attraction Delete Modal (kept for backward compatibility) -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog"
        aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmation</h5>
            </div>
            <div class="modal-body">
                Are you sure want to delete?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <form id="deleteForm" action="" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<!-- DataTable JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<!-- DataTables Initialization Script -->
<script>
    $(document).ready(function() {
        // Initialize DataTable with export buttons
        $('.datatables-basic').DataTable({
            responsive: true,
            dom: '<"row align-items-center mb-2"<"col-sm-6 col-12"l><"col-sm-6 col-12"f>>rt<"row align-items-center mt-2"<"col-sm-6 col-12"i><"col-sm-6 col-12"p>>',
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
            },
            lengthMenu: [10, 25, 50, 100],
            pagingType: 'simple_numbers',
        });

        // Custom export button functionality (for the dropdown)
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
</script>
<!-- End DataTable JS -->

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
    // Initialise all Bootstrap tooltips in the table (headers + action buttons)
    function initAttractionTooltips() {
        if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;
        document.querySelectorAll('#attractionsTable .th-tooltip[data-bs-toggle="tooltip"]').forEach(function(el) {
            const existing = bootstrap.Tooltip.getInstance(el);
            if (existing) existing.dispose();
            new bootstrap.Tooltip(el, {
                container: 'body',
                trigger: 'hover focus'
            });
        });
    }

    $(document).ready(function() {
        initAttractionTooltips();
        $('#attractionsTable').on('draw.dt', function() {
            initAttractionTooltips();
        });
    });

    // Attraction deletion with SweetAlert2
    window.deleteAttraction = function(deleteUrl, attractionName) {
        Swal.fire({
            title: 'Delete Attraction?',
            text: `Are you sure you want to delete "${attractionName}"? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const button = document.querySelector(`[onclick*="${deleteUrl}"]`);

                if (button) {
                    button.innerHTML = '<i class="ri-loader-4-line spin"></i>';
                    button.disabled = true;
                }

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = deleteUrl;

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken.getAttribute('content');
                    form.appendChild(csrfInput);
                }

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    };

    // Backward-compatible modal delete helper
    function setDeleteForm(action) {
        document.getElementById('deleteForm').action = action;
    }

    function showDmcModal(attractionId, type, companies) {
        const modalTitle = document.getElementById('dmcCompaniesModalLabel');
        modalTitle.textContent = type === 'master_dmc' ? 'Master DMC Companies' : 'DMC Companies';

        const modalBody = document.getElementById('dmcCompaniesModalBody');
        modalBody.innerHTML = '';

        if (companies && companies.length > 0) {
            companies.forEach(function(company) {
                const companyDiv = document.createElement('div');
                companyDiv.className = 'mb-2 p-2 border-bottom';
                companyDiv.innerHTML = `
                    <strong>${company.company_name || 'N/A'}</strong>
                    ${company.name ? `<br><small class="text-muted">${company.name}</small>` : ''}
                `;
                modalBody.appendChild(companyDiv);
            });
        } else {
            modalBody.innerHTML = '<p class="text-muted">No companies found.</p>';
        }

        $('#dmcCompaniesModal').modal('show');
    }

    function closeDmcModal() {
        $('#dmcCompaniesModal').modal('hide');
    }

    $(document).ready(function() {
        $('#dmcCompaniesModal').on('hidden.bs.modal', function () {
            $('#dmcCompaniesModalBody').html('');
        });
    });
</script>
@endsection
