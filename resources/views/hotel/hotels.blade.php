@extends('layouts.layout')
@section('title', 'Hotel Listing')
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

    /* Table shell */
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
        white-space: nowrap;
    }

    .table tbody td {
        vertical-align: middle;
        font-size: 12.5px;
        color: #334155;
        padding: 0.55rem 0.75rem !important;
    }

    .table-premium tbody tr {
        transition: background 0.2s ease;
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

    .table-premium.table-bordered > :not(caption) > * {
        border-color: transparent;
    }

    /* DataTables controls */
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

    .dataTables_wrapper .dt-buttons {
        display: none !important;
    }

    /* Hotel detail cell */
    .hotel-detail-title {
        font-weight: 700;
        font-size: 12.5px;
        color: #1e293b;
    }

    .hotel-detail-meta {
        font-size: 11.5px;
        color: #64748b;
    }

    /* Hotel image */
    .hotel-main-image {
        width: 46px;
        height: 46px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 4px rgba(0,0,0,0.07);
    }

    /* Action buttons */
    .btn-icon {
        width: 28px;
        height: 28px;
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
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.10);
    }

    .btn-icon-calendar {
        background: #eef2ff;
        border-color: #c7d2fe;
    }

    .btn-icon-calendar:hover {
        background: #e0e7ff;
        border-color: #a5b4fc;
    }

    .btn-icon-edit {
        background: #ecfdf5;
        border-color: #d1fae5;
    }

    .btn-icon-delete {
        background: #fef2f2;
        border-color: #fecaca;
    }

    .th-tooltip { cursor: help; }

    /* Column widths */
    #hotelsTable { table-layout: fixed; width: 100%; }
    #hotelsTable .col-no          { width: 38px;  min-width: 38px; }
    #hotelsTable .col-details     { width: 170px; min-width: 150px; }
    #hotelsTable .col-master-dmc,
    #hotelsTable .col-dmc         { width: 110px; min-width: 95px; }
    #hotelsTable .col-image       { width: 65px;  min-width: 60px; }
    #hotelsTable .col-action      { width: 105px; min-width: 105px; }
    #hotelsTable .col-created-at  { width: 100px; min-width: 90px; font-size: 10.5px; }

    /* Action icons — always horizontal */
    #hotelsTable td.col-action .action-wrap {
        display: flex;
        flex-wrap: nowrap;
        gap: 3px;
        align-items: center;
    }

    /* DMC modal */
    #dmcCompaniesModal .modal-content {
        border-radius: 0.75rem;
        background-color: #ffffff !important;
        color: #0f172a !important;
        box-shadow: 0 14px 35px rgba(15, 23, 42, 0.22);
    }

    #dmcCompaniesModal .dmc-company-card {
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        padding: 0.75rem 0.9rem;
        background: #ffffff !important;
    }

    #dmcCompaniesModal .dmc-company-card:hover {
        background: #f3f4f6 !important;
        border-color: #d1d5db;
    }

    /* Loading spinner */
    .spin { animation: spin 1s linear infinite; }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to   { transform: rotate(360deg); }
    }

    @media (max-width: 768px) {
        .page-header  { padding: 0.9rem 1rem; }
        .page-toolbar { padding: 0.75rem 1rem; }
        .page-title   { font-size: 1.2rem; }
    }

    /* Ensure user profile dropdown is visible */
    .topbar-item { display: block !important; visibility: visible !important; }
    .topbar-link { display: flex !important; visibility: visible !important; }
    .navbar-nav  { display: flex !important; }
    .topbar-link .ri-arrow-down-s-line { display: flex !important; visibility: visible !important; }
    .topbar-item .dropdown-menu { z-index: 9999 !important; display: none; }
    .topbar-item .dropdown-menu.show { display: block !important; }
    #exportDropdown + .dropdown-menu { z-index: 1000 !important; display: none; }
    #exportDropdown + .dropdown-menu.show { display: block !important; }
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
                        <h5 class="page-title">Hotels & Accommodations</h5>
                        <p class="page-subtitle">Manage hotels, images, availability, and status</p>
                    </div>
                    <div class="toolbar-actions">
                        @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 20)
                        <a href="{{ route('hotels.create') }}" class="btn btn-primary btn-premium">
                            <i class="fas fa-plus"></i> Add New Hotel
                        </a>
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

                <!-- Toolbar (alerts) -->
                <div class="page-toolbar">
                    <div style="width: 100%;">
                        <x-alert />
                    </div>
                </div>

                <div class="table-shell">
                <table class="datatables-basic table table-bordered table-premium" id="hotelsTable">
                    <thead>
                        <tr>
                            <th class="th-tooltip col-no" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Serial Number">No</th>
                            <th class="th-tooltip col-details" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Hotel Name, Phone and Email">Hotel Details</th>
                            @php
                                $roleId = auth()->user()->role_id;
                                $hideRoles = [11, 20, 35, 130, 132, 133, 135, 136, 137, 138, 77, 84, 139, 140];
                            @endphp
                            @if($roleId == 10 || $roleId == 19)
                                <th class="th-tooltip col-dmc" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Destination Management Company">DMC</th>
                            @elseif(!in_array($roleId, $hideRoles))
                                <th class="th-tooltip col-master-dmc" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Master DMC">Master DMC</th>
                                <th class="th-tooltip col-dmc" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Destination Management Company">DMC</th>
                            @endif
                            <th class="th-tooltip col-image" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Hotel Main Image">Image</th>
                            @if(auth()->user()->role_id == 1 || auth()->user()->userId == 2 || auth()->user()->role_id == 23 || auth()->user()->role_id == 35 || auth()->user()->role_id == 47 || auth()->user()->role_id == 77 || auth()->user()->role_id == 82 || auth()->user()->role_id == 84 || auth()->user()->role_id == 139 || auth()->user()->role_id == 140 || hasPermission('edit hotel') || hasPermission('delete hotel'))
                            @if(hasPermission('edit hotel') || hasPermission('delete hotel'))
                            <th class="th-tooltip col-action" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Available Actions">Action</th>
                            @endif
                            @endif
                            <th class="th-tooltip col-created-at" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Created Date and Time">Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hotels as $key => $hotel)
                        <tr>
                            <td class="col-no">{{ ++$key }}</td>
                            <td class="col-details">
                                <div class="d-flex flex-column gap-1">
                                    <span class="hotel-detail-title">
                                        <i class="ri-hotel-bed-line text-primary me-1"></i>{{ $hotel->name ?? 'N/A' }}
                                    </span>
                                    <small class="hotel-detail-meta">
                                        <i class="ri-phone-line me-1"></i>{{ $hotel->phone ?: 'N/A' }}
                                    </small>
                                    <small class="hotel-detail-meta">
                                        <i class="ri-mail-line me-1"></i>{{ $hotel->email ?: 'N/A' }}
                                    </small>
                                    <small class="hotel-detail-meta">
                                        <i class="ri-map-pin-line me-1"></i>{{ $hotel->country ?: 'N/A' }}, {{ $hotel->city ?: 'N/A' }}
                                    </small>
                                </div>
                            </td>

                            @php
                                $roleId = auth()->user()->role_id;
                                $hideRoles = [11, 20, 35, 130, 132, 133, 135, 136, 137, 138, 77, 84, 139, 140];
                            @endphp

                            @if($roleId == 10 || $roleId == 19)
                                @php
                                    $dmcIds   = $hotel->getSelectedDmcIds();
                                    $dmcUsers = App\Models\User::whereIn('userId', $dmcIds)->get();
                                @endphp
                                <td class="col-dmc">
                                    @if($dmcUsers->count() > 0)
                                        {{ $dmcUsers->first()->company_name }}
                                        @if($dmcUsers->count() > 1)
                                            <br><a href="javascript:void(0)"
                                                   class="btn btn-primary btn-sm text-white"
                                                   onclick="showDmcModal('{{ $hotel->hotel_unique_id }}', 'dmc', {{ $dmcUsers->toJson() }})">
                                                <small>+{{ $dmcUsers->count() - 1 }} More</small>
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-muted">No DMC assigned</span>
                                    @endif
                                </td>
                            @elseif(!in_array($roleId, $hideRoles))
                                @php
                                    $dmcIds        = $hotel->getSelectedDmcIds();
                                    $dmcUsers      = App\Models\User::whereIn('userId', $dmcIds)->get();
                                    $masterDmcIds  = $dmcUsers->pluck('master_dmc_id')->filter()->unique();
                                    $masterDmcUsers = App\Models\User::whereIn('userId', $masterDmcIds)->get();
                                @endphp
                                <td class="col-master-dmc">
                                    @if($masterDmcUsers->count() > 0)
                                        {{ $masterDmcUsers->first()->company_name }}
                                        @if($masterDmcUsers->count() > 1)
                                            <br><a href="javascript:void(0)"
                                                   class="btn btn-primary btn-sm text-white"
                                                   onclick="showDmcModal('{{ $hotel->hotel_unique_id }}', 'master_dmc', {{ $masterDmcUsers->toJson() }})">
                                                <small>+{{ $masterDmcUsers->count() - 1 }} More</small>
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-muted">No DMC assigned</span>
                                    @endif
                                </td>
                                <td class="col-dmc">
                                    @if($dmcUsers->count() > 0)
                                        {{ $dmcUsers->first()->company_name }}
                                        @if($dmcUsers->count() > 1)
                                            <br><a href="javascript:void(0)"
                                                   class="btn btn-primary btn-sm text-white"
                                                   onclick="showDmcModal('{{ $hotel->hotel_unique_id }}', 'dmc', {{ $dmcUsers->toJson() }})">
                                                <small>+{{ $dmcUsers->count() - 1 }} More</small>
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-muted">No DMC assigned</span>
                                    @endif
                                </td>
                            @endif

                            <td class="col-image">
                                <img src="{{ $hotel->main_image }}" alt="Hotel Image" class="hotel-main-image">
                            </td>

                            @if(auth()->user()->role_id == 1 || auth()->user()->userId == 2 || auth()->user()->role_id == 23 || auth()->user()->role_id == 35 || auth()->user()->role_id == 47 || auth()->user()->role_id == 77 || auth()->user()->role_id == 82 || auth()->user()->role_id == 84 || in_array(auth()->user()->role_id, [130, 132, 133, 135, 136, 137, 138, 139, 140]) || hasPermission('edit hotel') || hasPermission('delete hotel'))
                            @if(hasPermission('edit hotel') || hasPermission('delete hotel'))
                                @if($hotel->status == 1)
                                <td class="col-action">
                                    <div class="action-wrap">
                                        <!-- Calendar Button -->
                                        <a href="{{ route('hotels.viewcalendar', $hotel->hotel_unique_id) }}"
                                           target="_blank"
                                           class="btn btn-icon btn-icon-calendar th-tooltip"
                                           data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="View Calendar">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="15px" viewBox="0 -960 960 960" width="15px" fill="#4f46e5">
                                                <path d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Zm0-480h560v-80H200v80Zm0 0v-80 80Zm280 240q-17 0-28.5-11.5T440-440q0-17 11.5-28.5T480-480q17 0 28.5 11.5T520-440q0 17-11.5 28.5T480-400Zm-160 0q-17 0-28.5-11.5T280-440q0-17 11.5-28.5T320-480q17 0 28.5 11.5T360-440q0 17-11.5 28.5T320-400Zm320 0q-17 0-28.5-11.5T600-440q0-17 11.5-28.5T640-480q17 0 28.5 11.5T680-440q0 17-11.5 28.5T640-400ZM480-240q-17 0-28.5-11.5T440-280q0-17 11.5-28.5T480-320q17 0 28.5 11.5T520-280q0 17-11.5 28.5T480-240Zm-160 0q-17 0-28.5-11.5T280-280q0-17 11.5-28.5T320-320q17 0 28.5 11.5T360-280q0 17-11.5 28.5T320-240Zm320 0q-17 0-28.5-11.5T600-280q0-17 11.5-28.5T640-320q17 0 28.5 11.5T680-280q0 17-11.5 28.5T640-240Z"/>
                                            </svg>
                                        </a>

                                        <!-- Edit Button -->
                                        @if(hasPermission('edit hotel'))
                                        <a href="{{ route('hotels.edit', $hotel->hotel_unique_id) }}"
                                           class="btn btn-icon btn-icon-edit th-tooltip"
                                           data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit Hotel">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="15px" viewBox="0 -960 960 960" width="15px" fill="#059669">
                                                <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/>
                                            </svg>
                                        </a>
                                        @endif

                                        <!-- Delete Button -->
                                        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                                        <button type="button"
                                                class="btn btn-icon btn-icon-delete th-tooltip"
                                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete Hotel"
                                                onclick="deleteHotel('{{ route('hotels.destroy', $hotel->hotel_unique_id) }}', {{ json_encode($hotel->name) }})"
                                                id="delete-btn-{{ $hotel->hotel_unique_id }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="15px" viewBox="0 -960 960 960" width="15px" fill="#dc2626">
                                                <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                            </svg>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                                @else
                                <td class="col-action">
                                    <div class="action-wrap">
                                        <!-- Calendar always visible -->
                                        <a href="{{ route('hotels.viewcalendar', $hotel->hotel_unique_id) }}"
                                           target="_blank"
                                           class="btn btn-icon btn-icon-calendar th-tooltip"
                                           data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="View Calendar">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="15px" viewBox="0 -960 960 960" width="15px" fill="#4f46e5">
                                                <path d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Zm0-480h560v-80H200v80Zm0 0v-80 80Zm280 240q-17 0-28.5-11.5T440-440q0-17 11.5-28.5T480-480q17 0 28.5 11.5T520-440q0 17-11.5 28.5T480-400Zm-160 0q-17 0-28.5-11.5T280-440q0-17 11.5-28.5T320-480q17 0 28.5 11.5T360-440q0 17-11.5 28.5T320-400Zm320 0q-17 0-28.5-11.5T600-440q0-17 11.5-28.5T640-480q17 0 28.5 11.5T680-440q0 17-11.5 28.5T640-400ZM480-240q-17 0-28.5-11.5T440-280q0-17 11.5-28.5T480-320q17 0 28.5 11.5T520-280q0 17-11.5 28.5T480-240Zm-160 0q-17 0-28.5-11.5T280-280q0-17 11.5-28.5T320-320q17 0 28.5 11.5T360-280q0 17-11.5 28.5T320-240Zm320 0q-17 0-28.5-11.5T600-280q0-17 11.5-28.5T640-320q17 0 28.5 11.5T680-280q0 17-11.5 28.5T640-240Z"/>
                                            </svg>
                                        </a>
                                        @if($hotel->status == 5)
                                            <span class="text-muted" style="font-size:11px;">Awaiting approval</span>
                                        @elseif($hotel->status == 3)
                                            <span class="text-muted" style="font-size:11px;">Declined</span>
                                        @endif
                                    </div>
                                </td>
                                @endif
                            @endif
                            @endif

                            <td class="col-created-at">
                                <div class="d-flex flex-column">
                                    <span>{{ $hotel->created_at->format('D, M d, Y') }}</span>
                                    <small class="text-muted">{{ $hotel->created_at->format('h:i A') }}</small>
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

    <!-- DMC Companies Modal -->
    <div class="modal fade" id="dmcCompaniesModal" tabindex="-1" role="dialog" aria-labelledby="dmcCompaniesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex flex-column">
                        <h5 class="modal-title fw-semibold mb-1" id="dmcCompaniesModalLabel">DMC Companies</h5>
                        <small class="text-muted" id="dmcCompaniesModalSubtitle">Linked companies for this hotel</small>
                    </div>
                </div>
                <div class="modal-body pt-2">
                    <div id="dmcCompaniesModalBody" class="dmc-modal-body"></div>
                </div>
                <div class="modal-footer border-0 pt-0 d-flex justify-content-between align-items-center">
                    <small class="text-muted mb-0" id="dmcCompaniesCountText"></small>
                    <button type="button" class="btn btn-light border" data-dismiss="modal" onclick="closeDmcModal()">Close</button>
                </div>
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
<script>
    $(document).ready(function() {
        $('.datatables-basic').DataTable({
            responsive: false,
            autoWidth: false,
            dom: '<"row align-items-center mb-2"<"col-sm-6 col-12"l><"col-sm-6 col-12"f>>rt<"row align-items-center mt-2"<"col-sm-6 col-12"i><"col-sm-6 col-12"p>>',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            },
            lengthMenu: [10, 25, 50, 100],
            pagingType: 'simple_numbers',
            columnDefs: [
                { targets: '.col-no',         width: '38px'  },
                { targets: '.col-details',    width: '170px' },
                { targets: '.col-image',      width: '65px'  },
                { targets: '.col-action',     width: '105px' },
                { targets: '.col-created-at', width: '100px' },
            ],
        });

        $('#exportCopy').on('click',  function() { $('.datatables-basic').DataTable().button('.buttons-copy').trigger(); });
        $('#exportCSV').on('click',   function() { $('.datatables-basic').DataTable().button('.buttons-csv').trigger(); });
        $('#exportExcel').on('click', function() { $('.datatables-basic').DataTable().button('.buttons-excel').trigger(); });
        $('#exportPDF').on('click',   function() { $('.datatables-basic').DataTable().button('.buttons-pdf').trigger(); });
        $('#exportPrint').on('click', function() { $('.datatables-basic').DataTable().button('.buttons-print').trigger(); });

        $('#dmcCompaniesModal').on('hidden.bs.modal', function() {
            $('#dmcCompaniesModalBody').html('');
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script>
    // Bootstrap tooltips for headers + action buttons
    function initHotelTooltips() {
        if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;
        document.querySelectorAll('#hotelsTable .th-tooltip[data-bs-toggle="tooltip"]').forEach(function(el) {
            const existing = bootstrap.Tooltip.getInstance(el);
            if (existing) existing.dispose();
            new bootstrap.Tooltip(el, { container: 'body', trigger: 'hover focus' });
        });
    }

    $(document).ready(function() {
        initHotelTooltips();
        $('#hotelsTable').on('draw.dt', function() { initHotelTooltips(); });
    });

    // DMC Modal
    function showDmcModal(itemId, type, users) {
        const isMaster = (type === 'master_dmc');
        document.getElementById('dmcCompaniesModalLabel').textContent = isMaster ? 'Master DMC Companies' : 'DMC Companies';
        document.getElementById('dmcCompaniesModalSubtitle').textContent = isMaster
            ? 'Master DMCs linked to this hotel'
            : 'DMC partners linked to this hotel';

        const modalBody = document.getElementById('dmcCompaniesModalBody');
        const countTextEl = document.getElementById('dmcCompaniesCountText');
        modalBody.innerHTML = '';

        if (users && users.length > 0) {
            users.forEach(function(user) {
                const div = document.createElement('div');
                div.className = 'dmc-company-card mb-2';
                div.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold text-body mb-1">${user.company_name || 'N/A'}</div>
                            ${user.name  ? `<div class="text-muted small mb-1">${user.name}</div>`  : ''}
                            ${user.email ? `<div class="text-muted small"><i class="ri-mail-line me-1"></i>${user.email}</div>` : ''}
                            ${user.phone ? `<div class="text-muted small"><i class="ri-phone-line me-1"></i>${user.phone}</div>` : ''}
                        </div>
                        <span class="badge bg-light text-muted text-uppercase small">${isMaster ? 'MASTER DMC' : 'DMC'}</span>
                    </div>`;
                modalBody.appendChild(div);
            });
            if (countTextEl) countTextEl.textContent = `${users.length} compan${users.length === 1 ? 'y' : 'ies'} linked`;
        } else {
            modalBody.innerHTML = '<p class="text-muted mb-0">No companies found for this hotel.</p>';
            if (countTextEl) countTextEl.textContent = '';
        }

        $('#dmcCompaniesModal').modal('show');
    }

    function closeDmcModal() {
        $('#dmcCompaniesModal').modal('hide');
    }

    // SweetAlert2 delete
    window.deleteHotel = function(deleteUrl, hotelName) {
        Swal.fire({
            title: 'Delete Hotel?',
            text: `Are you sure you want to delete "${hotelName}"? This action cannot be undone.`,
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

    // User profile dropdown fix (hotels page specific)
    $(document).ready(function() {
        setTimeout(function() {
            const dropdownToggle = $('.topbar-item .topbar-link.dropdown-toggle');
            const dropdownMenu   = $('.topbar-item .dropdown-menu');
            if (dropdownToggle.length && dropdownMenu.length) {
                dropdownToggle.off('click.profile-dropdown-fix');
                dropdownToggle.on('click.profile-dropdown-fix', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $('#exportDropdown').attr('aria-expanded', 'false');
                    $('#exportDropdown').next('.dropdown-menu').removeClass('show');
                    if (dropdownMenu.hasClass('show')) {
                        dropdownMenu.removeClass('show');
                        dropdownToggle.attr('aria-expanded', 'false');
                    } else {
                        dropdownMenu.addClass('show');
                        dropdownToggle.attr('aria-expanded', 'true');
                    }
                });
                $(document).on('click.profile-dropdown-fix', function(e) {
                    if (!dropdownToggle.is(e.target) && dropdownToggle.has(e.target).length === 0 &&
                        !dropdownMenu.is(e.target) && dropdownMenu.has(e.target).length === 0) {
                        dropdownMenu.removeClass('show');
                        dropdownToggle.attr('aria-expanded', 'false');
                    }
                });
            }
        }, 1000);
    });
</script>
@endsection
