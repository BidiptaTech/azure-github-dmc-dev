@extends('layouts.layout')
@section('title', 'Agency List')
@extends('layouts.datatablecss')

@section('content')
<style>
    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .header-icon {
        font-size: 2rem;
        opacity: 0.9;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        justify-content: flex-end;
    }
    
    .action-btn {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        color: #333;
        background: #fff;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .action-btn .btn-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background: rgba(0, 0, 0, 0.05);
        border-radius: 8px;
        margin-right: 0.5rem;
    }
    
    .import-btn {
        background: #e7fdf1;
        color: #15803d;
    }
    
    .import-btn:hover {
        background: #dcfce7;
    }
    
    .import-btn .btn-icon {
        background: rgba(21, 128, 61, 0.1);
    }
    
    .add-btn {
        background: #f1f5f9;
    }
    
    .add-btn:hover {
        background: #e2e8f0;
    }

    /* Statistics Cards */
    .stats-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border: 1px solid #e3e6f0;
    }

    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .stats-icon {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: white;
        margin-bottom: 1rem;
    }

    .stats-icon.primary { background: #667eea; }
    .stats-icon.success { background: #28a745; }
    .stats-icon.warning { background: #ffc107; }
    .stats-icon.info { background: #17a2b8; }

    /* Search and Filter Container */
    .search-filter-container {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
        border: 1px solid #e3e6f0;
    }

    .form-control, .form-select {
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        padding: 0.6rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }

    /* Clear Button */
    .btn-clear {
        background: #667eea;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.6rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-clear:hover {
        background: #5a6fd8;
        color: white;
        transform: translateY(-1px);
    }

    /* Table Container */
    .table-container {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: 1px solid #e3e6f0;
    }

    .table-header {
        background: #f8f9fa;
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #dee2e6;
    }

    /* Compact Table Styling with visible borders */
    .table.table-bordered {
        border: 1px solid #dee2e6 !important;
    }

    .table.table-bordered thead th {
        background-color: #667eea;
        color: white;
        border-left: 1px solid #dee2e6 !important;
        border-right: 1px solid #dee2e6 !important;
        border-top: 1px solid #dee2e6 !important;
        border-bottom: 2px solid #dee2e6 !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 0.5rem 0.75rem;
    }

    .table.table-bordered tbody td {
        padding: 0.5rem 0.75rem;
        vertical-align: middle;
        border-left: 1px solid #dee2e6 !important;
        border-right: 1px solid #dee2e6 !important;
        border-top: 1px solid #dee2e6 !important;
        border-bottom: 1px solid #dee2e6 !important;
        font-size: 0.875rem;
    }

    .table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }

    /* DataTables specific border fixes - ensure all cells have borders */
    #agenciesTable.table-bordered {
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    #agenciesTable.table-bordered thead th,
    #agenciesTable.table-bordered tbody td {
        border-left: 1px solid #dee2e6 !important;
        border-right: 1px solid #dee2e6 !important;
        border-top: 1px solid #dee2e6 !important;
        border-bottom: 1px solid #dee2e6 !important;
    }

    #agenciesTable.table-bordered thead th {
        border-bottom: 2px solid #dee2e6 !important;
    }

    /* Ensure vertical lines are visible between columns for datatables-basic */
    .datatables-basic.table-bordered th,
    .datatables-basic.table-bordered td {
        border-left: 1px solid #dee2e6 !important;
        border-right: 1px solid #dee2e6 !important;
        border-top: 1px solid #dee2e6 !important;
        border-bottom: 1px solid #dee2e6 !important;
    }

    .datatables-basic.table-bordered thead th {
        border-bottom: 2px solid #dee2e6 !important;
    }

    /* Override any DataTables inline styles that might remove borders */
    table.dataTable thead th,
    table.dataTable tbody td {
        border-left: 1px solid #dee2e6 !important;
        border-right: 1px solid #dee2e6 !important;
    }

    /* Toggle Button Styling */
    .toggle-row {
        cursor: pointer;
        border: none;
        background: none;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s ease;
    }

    .toggle-row:hover {
        opacity: 0.7;
    }

    .toggle-icon {
        transition: transform 0.2s ease;
        font-size: 0.75rem;
    }

    .toggle-icon.expanded {
        transform: rotate(90deg);
    }

    /* Child row styling */
    .child-row-details {
        background-color: #f8f9fa;
        padding: 1rem;
        border-left: 3px solid #667eea;
    }

    .child-row-details .detail-item {
        margin-bottom: 0.5rem;
    }

    .child-row-details .detail-label {
        font-weight: 600;
        color: #495057;
        margin-right: 0.5rem;
    }

    /* Badge Styling */
    .badge {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.375rem 0.75rem;
    }

    /* Avatar Styling */
    .avatar-initial {
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
    }

    /* Dropdown Styling */
    .dropdown-toggle::after {
        display: none;
    }

    .dropdown-menu {
        min-width: 140px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .dropdown-item {
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
    }

    /* DataTable Enhancements */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        margin-bottom: 1rem;
    }

    .dataTables_wrapper .dataTables_paginate {
        text-align: right !important;
    }

    .dataTables_wrapper .dataTables_info {
        text-align: left !important;
    }

    .dataTables_wrapper .dataTables_filter {
        text-align: right;
    }

    .dataTables_wrapper .dataTables_filter input {
        margin-left: 0.5rem;
        display: inline-block;
        width: auto;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
            text-align: center;
        }
        
        .stats-card {
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            text-align: center !important;
            margin-bottom: 0.5rem;
        }
        
        .action-buttons {
            justify-content: center;
        }
        
        .action-btn {
            width: 100%;
            justify-content: center;
            margin-bottom: 0.5rem;
        }
    }
    
    @media (max-width: 576px) {
        .header-icon {
            font-size: 1.5rem;
        }
        
        .page-header h2 {
            font-size: 1.5rem;
        }
        
        .action-btn {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
        
        .action-btn .btn-icon {
            width: 24px;
            height: 24px;
            font-size: 0.875rem;
        }
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="d-flex align-items-center">
                        <div class="header-icon me-3">
                            <i class="ri-building-line"></i>
                        </div>
                        <div>
                            <h2 class="mb-2">Agency Management</h2>
                            <p class="mb-0 opacity-90">Manage all agencies and their branches from here. Add new agencies or edit existing ones.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    <div class="action-buttons">
                        @php
                            // Define roles that can access different functions
                            $allowedRoles = [1, 2, 3, 4, 19, 20];
                            $userRoleId = auth()->user()->role_id;
                            $canManageAgencies = in_array($userRoleId, $allowedRoles);
                        @endphp

                        @if($canManageAgencies)
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('agencies.import') }}" class="action-btn import-btn me-2">
                                    <div class="d-flex align-items-center">
                                        <div class="btn-icon">
                                            <i class="ri-file-upload-line"></i>
                                        </div>
                                        <span>Import Agencies</span>
                                    </div>
                                </a>
                                <a href="{{ route('agencies.create') }}" class="action-btn add-btn">
                                    <div class="d-flex align-items-center">
                                        <div class="btn-icon">
                                            <i class="ri-add-line"></i>
                                        </div>
                                        <span>Add New Agency</span>
                                    </div>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon primary">
                        <i class="ri-building-line"></i>
                    </div>
                    <h4 class="mb-1">{{ $agencies->count() }}</h4>
                    <p class="text-muted mb-0">Total Agencies</p>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon success">
                        <i class="ri-checkbox-circle-line"></i>
                    </div>
                    <h4 class="mb-1">{{ $agencies->where('status', 1)->count() }}</h4>
                    <p class="text-muted mb-0">Active Agencies</p>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon warning">
                        <i class="ri-building-2-line"></i>
                    </div>
                    <h4 class="mb-1">{{ $agencies->sum('total_branches') }}</h4>
                    <p class="text-muted mb-0">Total Offices</p>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon info">
                        <i class="ri-global-line"></i>
                    </div>
                    <h4 class="mb-1">{{ $agencies->unique('country')->count() }}</h4>
                    <p class="text-muted mb-0">Countries</p>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="search-filter-container">
            <div class="row align-items-center">
                <div class="col-md-4 mb-3 mb-md-0">
                    <label class="form-label">Quick Search</label>
                    <input type="text" class="form-control" id="quickSearch" placeholder="Search agencies...">
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <label class="form-label">Filter by Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <label class="form-label">Filter by Country</label>
                    <select class="form-select" id="countryFilter">
                        <option value="">All Countries</option>
                        @foreach($agencies->unique('country') as $agency)
                            <option value="{{ $agency->country }}">{{ $agency->country }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-clear w-100" id="clearFilters">
                        <i class="ri-refresh-line me-1"></i> Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- Agencies List -->
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">All Agencies</h5>
                    </div>
                </div>
                <x-alert />
                <hr>

                <table class="datatables-basic table table-bordered" id="agenciesTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Agency Name</th>
                            <th>Contact No</th>
                            <th>Email</th>
                            <th>Location</th>
                            @php
                                $roleId = auth()->user()->role_id;
                                $hideRoles = [11, 20, 35, 130, 132, 133, 135, 136, 137, 138, 77, 84, 139, 140];
                            @endphp
                            @if($roleId == 10 || $roleId == 19)
                                <th>DMC</th>
                            @elseif(!in_array($roleId, $hideRoles))
                                <th>Master Dmc</th>
                                <th>DMC</th>
                            @endif
                            <th>Offices</th>
                            <th>Status</th>
                            <th>Actions</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agencies as $key => $agency)
                        <tr data-status="{{ $agency->status ? 'active' : 'inactive' }}" 
                            data-country="{{ $agency->country }}"
                            data-agency-name="{{ $agency->agency_name }}"
                            data-phone="{{ $agency->phone }}"
                            data-email="{{ $agency->email }}"
                            data-location="{{ $agency->city }}, {{ $agency->country }}"
                            data-offices="{{ $agency->total_branches }}"
                            data-created-at="{{ $agency->created_at->format('D, M d, Y h:i A') }}">
                            <td>
                                <!-- <button type="button" class="btn btn-sm btn-link p-0 toggle-row" style="text-decoration: none; color: inherit;"> -->
                                    <!-- <i class="fas fa-chevron-right toggle-icon"></i> -->
                                    <span class="ms-1">{{ ++$key }}</span>
                                <!-- </button> -->
                            </td>
                            <td class="agency-name">{{ $agency->agency_name }}</td>
                            <td>
                                @if($agency->phone)
                                    {{ $agency->phone }}
                                @else
                                    <span class="badge bg-danger">No details</span>
                                @endif
                            </td>
                            <td>
                                @if($agency->email)
                                    <a href="mailto:{{ $agency->email }}">{{ $agency->email }}</a>
                                @else
                                    <span class="badge bg-danger">No details</span>
                                @endif
                            </td>
                            <td>
                                {{ $agency->city }}, {{ $agency->country }}
                            </td>

                            @php
                                $roleId = auth()->user()->role_id;
                                $hideRoles = [11, 20, 35, 130, 132, 133, 135, 136, 137, 138, 77, 84, 139, 140];
                            @endphp

                            @if($roleId == 10 || $roleId == 19)
                                @php
                                    $dmcIds = [];
                                    if (!empty($agency->dmc_id)) {
                                        $dmcIds = is_array($agency->dmc_id) ? $agency->dmc_id : json_decode($agency->dmc_id, true);
                                        $dmcIds = is_array($dmcIds) ? $dmcIds : [];
                                    }
                                    $dmcUsers = !empty($dmcIds) ? App\Models\User::whereIn('userId', $dmcIds)->get() : collect();
                                @endphp
                                <td>
                                    @if($dmcUsers->count() > 0)
                                        {{ $dmcUsers->first()->company_name }}
                                        @if($dmcUsers->count() > 1)
                                            <br><a href="javascript:void(0)" 
                                                   class="text-primary" 
                                                   onclick="showDmcModal('{{ $agency->agency_id }}', 'dmc', {{ $dmcUsers->toJson() }})">
                                                <small>+{{ $dmcUsers->count() - 1 }} More</small>
                                            </a>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                            @elseif(!in_array($roleId, $hideRoles))
                                @php
                                    $dmcIds = [];
                                    if (!empty($agency->dmc_id)) {
                                        $dmcIds = is_array($agency->dmc_id) ? $agency->dmc_id : json_decode($agency->dmc_id, true);
                                        $dmcIds = is_array($dmcIds) ? $dmcIds : [];
                                    }
                                    $dmcUsers = !empty($dmcIds) ? App\Models\User::whereIn('userId', $dmcIds)->get() : collect();
                                    $masterDmcIds = $dmcUsers->pluck('master_dmc_id')->filter()->unique();
                                    $masterDmcUsers = App\Models\User::whereIn('userId', $masterDmcIds)->get();
                                @endphp
                                <td>
                                    @if($masterDmcUsers->count() > 0)
                                        {{ $masterDmcUsers->first()->company_name }}
                                        @if($masterDmcUsers->count() > 1)
                                            <br><a href="javascript:void(0)" 
                                                   class="text-primary" 
                                                   onclick="showDmcModal('{{ $agency->agency_id }}', 'master_dmc', {{ $masterDmcUsers->toJson() }})">
                                                <small>+{{ $masterDmcUsers->count() - 1 }} More</small>
                                            </a>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($dmcUsers->count() > 0)
                                        {{ $dmcUsers->first()->company_name }}
                                        @if($dmcUsers->count() > 1)
                                            <br><a href="javascript:void(0)" 
                                                   class="text-primary" 
                                                   onclick="showDmcModal('{{ $agency->agency_id }}', 'dmc', {{ $dmcUsers->toJson() }})">
                                                <small>+{{ $dmcUsers->count() - 1 }} More</small>
                                            </a>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                            @endif
                            <td>
                                {{ $agency->total_branches }} {{ $agency->total_branches == 1 ? 'Office' : 'Offices' }}
                            </td>
                            <td>
                                @if($agency->status == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td style="display: inline-block; white-space: nowrap;">
                                <a href="{{ route('agencies.show', Crypt::encrypt($agency->agency_id)) }}"
                                    class="btn btn-primary btn-sm rounded-circle waves-effect waves-light"
                                    style="min-width: 28px; min-height: 28px; padding: 0;" title="View">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960"
                                        width="16px" fill="#ffffff">
                                        <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Zm0-300Zm0 220q113 0 207.5-59.5T840-500q-50-101-144.5-160.5T480-720q-113 0-207.5 59.5T128-500q50 101 144.5 160.5T480-280Z"/>
                                    </svg>
                                </a>
                                @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 2 || auth()->user()->role_id == 3 || auth()->user()->role_id == 4 || auth()->user()->role_id == 19 || auth()->user()->role_id == 20)
                                <a href="{{ route('agencies.edit', Crypt::encrypt($agency->agency_id)) }}"
                                    class="btn btn-primary btn-sm rounded-circle waves-effect waves-light"
                                    style="min-width: 28px; min-height: 28px; padding: 0;" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960"
                                        width="16px" fill="#ffffff">
                                        <path
                                            d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z">
                                        </path>
                                    </svg>
                                </a>
                                <button type="button"
                                    class="btn btn-danger btn-sm rounded-circle waves-effect waves-light"
                                    style="min-width: 28px; min-height: 28px; padding: 0;" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal"
                                    onclick="setDeleteForm('{{ route('agencies.destroy', $agency->agency_id) }}')"
                                    title="Delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960"
                                        width="16px" fill="#ffffff">
                                        <path
                                            d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z">
                                        </path>
                                    </svg>
                                </button>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $agency->created_at->format('D,  M d, Y') }}</span>
                                    <small class="text-muted">{{ $agency->created_at->format('h:i A') }}</small>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- DMC Modal -->
        <div class="modal fade" id="dmcModal" tabindex="-1" aria-labelledby="dmcModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="dmcModalLabel">DMC List</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeDmcModal()"></button>
                    </div>
                    <div class="modal-body">
                        <div id="dmcList"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="closeDmcModal()">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Agency Delete Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirmation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeDeleteModal()"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure want to delete?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="closeDeleteModal()">Close</button>
                        <form id="deleteForm" action="" method="POST" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')

<!-- DataTable JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<!-- DataTables Initialization Script -->
<script>
    $(document).ready(function() {
        @php
            $roleId = auth()->user()->role_id;
            $hideRoles = [11, 20, 35, 130, 132, 133, 135, 136, 137, 138, 77, 84, 139, 140];
            
            // Determine column indices based on role
            if ($roleId == 10 || $roleId == 19) {
                $actionsColumnIndex = 9;
            } elseif (!in_array($roleId, $hideRoles)) {
                $actionsColumnIndex = 10;
            } else {
                $actionsColumnIndex = 8;
            }
        @endphp

        // Initialize DataTable with compact settings
        const table = $('#agenciesTable').DataTable({
            responsive: true,
            ordering: false,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            },
            lengthMenu: [10, 25, 50, 100],
            columnDefs: [
                { targets: [{{ $actionsColumnIndex }}], orderable: false }
            ],
            pageLength: 10,
            drawCallback: function() {
                // Ensure borders are visible after DataTables renders
                $('#agenciesTable thead th, #agenciesTable tbody td').css({
                    'border-left': '1px solid #dee2e6',
                    'border-right': '1px solid #dee2e6',
                    'border-top': '1px solid #dee2e6',
                    'border-bottom': '1px solid #dee2e6'
                });
                $('#agenciesTable thead th').css('border-bottom', '2px solid #dee2e6');
            }
        });

        // Toggle row details functionality
        $('#agenciesTable tbody').on('click', '.toggle-row', function() {
            const tr = $(this).closest('tr');
            const icon = $(this).find('.toggle-icon');
            const row = table.row(tr);

            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
                icon.removeClass('expanded');
            } else {
                // Open this row - get data from data attributes
                const agencyName = tr.data('agency-name') || tr.find('.agency-name').text().trim();
                const phone = tr.data('phone') || 'N/A';
                const email = tr.data('email') || 'N/A';
                const location = tr.data('location') || 'N/A';
                const offices = tr.data('offices') || '0';
                const createdAt = tr.data('created-at') || tr.find('td:last').text().trim();
                const status = tr.find('td').filter(function() {
                    return $(this).find('.badge').length > 0 && ($(this).find('.badge').text().includes('Active') || $(this).find('.badge').text().includes('Inactive'));
                }).first().find('.badge').text().trim() || 'N/A';
                
                // Create child row content with compact layout
                let childContent = '<div class="child-row-details">';
                childContent += '<div class="row">';
                childContent += '<div class="col-md-6">';
                childContent += '<div class="detail-item"><span class="detail-label">Agency Name:</span>' + (agencyName || 'N/A') + '</div>';
                childContent += '<div class="detail-item"><span class="detail-label">Phone:</span>' + (phone || 'N/A') + '</div>';
                childContent += '<div class="detail-item"><span class="detail-label">Email:</span>' + (email || 'N/A') + '</div>';
                childContent += '</div>';
                childContent += '<div class="col-md-6">';
                childContent += '<div class="detail-item"><span class="detail-label">Location:</span>' + (location || 'N/A') + '</div>';
                childContent += '<div class="detail-item"><span class="detail-label">Offices:</span>' + offices + ' ' + (offices == 1 ? 'Office' : 'Offices') + '</div>';
                childContent += '<div class="detail-item"><span class="detail-label">Status:</span>' + (status || 'N/A') + '</div>';
                childContent += '<div class="detail-item"><span class="detail-label">Created At:</span>' + (createdAt || 'N/A') + '</div>';
                childContent += '</div>';
                childContent += '</div>';
                childContent += '</div>';

                row.child(childContent).show();
                tr.addClass('shown');
                icon.addClass('expanded');
            }
        });

        // Quick Search functionality
        $('#quickSearch').on('keyup', function() {
            table.search(this.value).draw();
        });

        // Status Filter
        $('#statusFilter').on('change', function() {
            const selectedStatus = this.value;
            
            if (selectedStatus === '') {
                $.fn.dataTable.ext.search.pop();
            } else {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'agenciesTable') {
                        return true;
                    }
                    
                    const row = table.row(dataIndex).node();
                    const rowStatus = $(row).data('status');
                    
                    if (selectedStatus === 'active') {
                        return rowStatus === 'active';
                    } else if (selectedStatus === 'inactive') {
                        return rowStatus === 'inactive';
                    }
                    return true;
                });
            }
            table.draw();
        });

        // Country Filter
        $('#countryFilter').on('change', function() {
            const selectedCountry = this.value;
            table.column(4).search(selectedCountry).draw();
        });

        // Clear Filters
        $('#clearFilters').on('click', function() {
            $('#quickSearch').val('');
            $('#statusFilter').val('');
            $('#countryFilter').val('');
            
            $.fn.dataTable.ext.search.pop();
            table.search('').columns().search('').draw();
        });

        // Ensure modals close properly - handle both Bootstrap 4 and 5
        $('#dmcModal, #deleteModal').on('hidden.bs.modal', function () {
            $(this).removeData('bs.modal');
        });
    });

    // Set delete form action
    function setDeleteForm(action) {
        document.getElementById('deleteForm').action = action;
    }

    // Close DMC Modal function
    function closeDmcModal() {
        const modalElement = document.getElementById('dmcModal');
        if (modalElement) {
            // Try Bootstrap 5 first
            if (typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                } else {
                    const newModal = new bootstrap.Modal(modalElement);
                    newModal.hide();
                }
            } 
            // Fallback to jQuery/Bootstrap 4
            else if (typeof $ !== 'undefined') {
                $('#dmcModal').modal('hide');
            }
            // Last resort - manual hide
            else {
                modalElement.classList.remove('show');
                modalElement.style.display = 'none';
                document.body.classList.remove('modal-open');
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
            }
        }
    }

    // Close Delete Modal function
    function closeDeleteModal() {
        const modalElement = document.getElementById('deleteModal');
        if (modalElement) {
            // Try Bootstrap 5 first
            if (typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                } else {
                    const newModal = new bootstrap.Modal(modalElement);
                    newModal.hide();
                }
            } 
            // Fallback to jQuery/Bootstrap 4
            else if (typeof $ !== 'undefined') {
                $('#deleteModal').modal('hide');
            }
            // Last resort - manual hide
            else {
                modalElement.classList.remove('show');
                modalElement.style.display = 'none';
                document.body.classList.remove('modal-open');
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
            }
        }
    }

    // Show DMC modal with details
    function showDmcModal(agencyId, type, users) {
        let listHtml = '<ul class="list-group">';
        users.forEach(function(user) {
            listHtml += `<li class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${user.company_name}</strong>
                        <br><small>${user.email || 'No email'}</small>
                        ${user.phone ? `<br><small>${user.phone}</small>` : ''}
                    </div>
                </div>
            </li>`;
        });
        listHtml += '</ul>';
        
        $('#dmcList').html(listHtml);
        $('#dmcModalLabel').text(type === 'dmc' ? 'DMC List' : 'Master DMC List');
        
        // Use Bootstrap 5 modal show method
        if (typeof bootstrap !== 'undefined') {
            var myModal = new bootstrap.Modal(document.getElementById('dmcModal'), {
                backdrop: true,
                keyboard: true
            });
            myModal.show();
        } 
        // Fallback to jQuery/Bootstrap 4
        else if (typeof $ !== 'undefined') {
            $('#dmcModal').modal('show');
        }
        
        // Ensure modal closes properly when clicking backdrop or pressing ESC
        $('#dmcModal').on('hidden.bs.modal', function () {
            $(this).removeData('bs.modal');
        });
    }
</script>
<!-- End DataTable JS -->

@endsection 