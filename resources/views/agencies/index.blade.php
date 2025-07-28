@extends('layouts.layout')

@section('title', 'Agency List')

@section('content')
<style>
    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
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

    /* Table Styling */
    .table thead th {
        background-color: #667eea;
        color: white;
        border: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 1rem 0.75rem;
    }

    .table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }

    .table tbody td {
        padding: 0.75rem;
        vertical-align: middle;
        border-top: 1px solid #dee2e6;
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
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2">
                        <i class="ri-building-line me-2"></i>
                        Agency Management
                    </h2>
                    <p class="mb-0 opacity-90">Manage all agencies and their branches from here. Add new agencies or edit existing ones.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="{{ route('agencies.create') }}" class="btn btn-light">
                        <i class="ri-add-line me-1"></i> Add New Agency
                    </a>
                </div>
            </div>
        </div>

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
        <div class="table-container">
            <div class="table-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">All Agencies</h5>
                        <small class="text-muted">Manage your agency network</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <small class="text-muted">
                            <i class="ri-database-line me-1"></i>
                            {{ $agencies->count() }} agencies found
                        </small>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table id="agenciesTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>AGENCY DETAILS</th>
                            <th>CONTACT INFO</th>
                            <th>LOCATION</th>
                            <th>OFFICES</th>
                            {{-- <th>STATUS</th>
                            <th>CREATED BY</th> --}}
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agencies as $key => $agency)
                        <tr data-status="{{ $agency->status ? 'active' : 'inactive' }}" data-country="{{ $agency->country }}">
                            <td>
                                <span class="badge bg-primary">{{ ++$key}}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                        <div class="avatar-initial bg-label-info rounded-circle">
                                            {{ strtoupper(substr($agency->agency_name, 0, 2)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $agency->agency_name }}</h6>
                                        <small class="text-muted">{{ $agency->created_at->format('M d, Y') }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <small class="d-block">
                                        <i class="ri-mail-line me-1 text-primary"></i>
                                        <a href="mailto:{{ $agency->email }}" class="text-primary">{{ $agency->email }}</a>
                                    </small>
                                    <small class="d-block">
                                        <i class="ri-phone-line me-1 text-success"></i>
                                        {{ $agency->phone }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <small class="d-block fw-semibold">{{ $agency->city }}</small>
                                    <small class="text-muted">{{ $agency->country }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-label-{{ $agency->hasBranches() ? 'success' : 'secondary' }} rounded-pill">
                                    {{ $agency->total_branches }} 
                                    {{ $agency->total_branches == 1 ? 'OFFICE' : 'OFFICES' }}
                                </span>
                            </td>
                            {{-- <td>
                                <span class="badge bg-label-{{ $agency->status ? 'success' : 'danger' }}">
                                    {{ $agency->status ? 'ACTIVE' : 'INACTIVE' }}
                                </span>
                            </td> --}}
                            {{-- <td>
                                <small class="text-muted">
                                    <i class="ri-user-line me-1"></i>
                                    {{ $agency->creator ? $agency->creator->name : 'System' }}
                                </small>
                            </td> --}}
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('agencies.show', $agency->agency_id) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    <a href="{{ route('agencies.edit', $agency->agency_id) }}" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="ri-pencil-line"></i>
                                    </a>
                                    <form action="{{ route('agencies.destroy', $agency->agency_id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete"
                                                onclick="return confirm('Are you sure you want to delete this agency?')">
                                            <i class="ri-delete-bin-7-line"></i>
                                        </button>
                                    </form>
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
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#agenciesTable').DataTable({
        responsive: true,
        order: [[0, 'asc']],
        pageLength: 10,
        columnDefs: [
            { targets: [5], orderable: false },
            { targets: [0, 4, 5], className: 'text-center' }
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search in table...",
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
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        drawCallback: function(settings) {
            // Ensure pagination is properly aligned
            $('.dataTables_paginate').addClass('d-flex justify-content-end');
            $('.dataTables_info').addClass('d-flex align-items-center');
        }
    });

    // Quick Search functionality
    $('#quickSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Status Filter (using data attributes since status column is hidden)
    $('#statusFilter').on('change', function() {
        const selectedStatus = this.value;
        
        if (selectedStatus === '') {
            table.search('').draw();
        } else {
            // Filter by data-status attribute
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
            table.draw();
        }
    });

    // Country Filter
    $('#countryFilter').on('change', function() {
        const selectedCountry = this.value;
        table.column(3).search(selectedCountry).draw();
    });

    // Clear Filters
    $('#clearFilters').on('click', function() {
        $('#quickSearch').val('');
        $('#statusFilter').val('');
        $('#countryFilter').val('');
        
        // Clear custom search functions
        $.fn.dataTable.ext.search.pop();
        
        table.search('').columns().search('').draw();
    });

    // Form submission confirmation
    $('form[method="DELETE"]').on('submit', function(e) {
        e.preventDefault();
        const agencyName = $(this).closest('tr').find('h6').text();
        
        if (confirm(`Are you sure you want to delete "${agencyName}"? This action cannot be undone.`)) {
            this.submit();
        }
    });
});
</script>
@endsection 