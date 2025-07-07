@extends('layouts.layout')
@section('title', 'Attraction Package')
{{-- @extends('layouts.datatablecss') --}}

@section('content')
@extends('layouts.datatablecss')
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css" rel="stylesheet">

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header with animated background -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white shadow-lg" style="background-image: linear-gradient(135deg, #6B73FF 10%, #000DFF 100%);">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="fw-bold mb-1">Packaged Attractions</h3>
                                <p class="mb-0">Manage your attraction packages</p>
                            </div>
                            <a href="{{ route('packaged-attractions.create') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="mdi mdi-plus me-1"></i> Create New Package
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-start border-success border-3" role="alert">
                <div class="d-flex">
                    <i class="mdi mdi-check-circle-outline me-2 fs-4"></i>
                    <div>
                        <h6 class="alert-heading mb-0">Success!</h6>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Data card with subtle shadow -->
        <div class="card shadow-sm" >
            <div class="card-header bg-light py-3" >
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-package-variant me-2 text-primary"></i>All Packages
                        </h5>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-primary rounded-pill">{{ count($packagedAttractions) }} Total</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="packaged-attractions-table">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">#</th>
                                <th>Package Name</th>
                                <th class="text-end">Adult Price</th>
                                <th class="text-end">Child Price</th>
                                <th class="text-end">Senior Price</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($packagedAttractions as $attraction)
                                <tr class="align-middle">
                                    <td class="text-center fw-bold">{{ $attraction->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-md me-2 bg-light rounded">
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    {{ substr($attraction->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $attraction->name }}</h6>
                                                <small class="text-muted">ID: {{ $attraction->package_attraction_id ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end fw-semibold">${{ number_format($attraction->adult_price, 2) }}</td>
                                    <td class="text-end fw-semibold">${{ number_format($attraction->child_price, 2) }}</td>
                                    <td class="text-end fw-semibold">${{ number_format($attraction->senior_citizen_price, 2) }}</td>
                                    <td class="text-center">
                                        @if($attraction->status == 1)
                                            <span class="badge bg-success bg-glow">Active</span>
                                        @else
                                            <span class="badge bg-danger bg-glow">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('packaged-attractions.show', $attraction->id) }}" class="btn btn-sm btn-primary rounded-pill" data-bs-toggle="tooltip" data-bs-placement="top" title="View Details">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                            <a href="{{ route('packaged-attractions.edit', $attraction->id) }}" class="btn btn-sm btn-info rounded-pill" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Package">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <form action="{{ route('packaged-attractions.destroy', $attraction->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger rounded-pill" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Package" onclick="return confirm('Are you sure you want to delete this packaged attraction?')">
                                                    <i class="mdi mdi-trash-can"></i>
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
</div>

@section('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Initialize DataTable with enhanced styling
        $('#packaged-attractions-table').DataTable({
            "order": [[0, "desc"]],
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "language": {
                "search": "<i class='mdi mdi-magnify'></i> Search:",
                "lengthMenu": "_MENU_ records per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries"
            },
            "dom": '<"row mb-3"<"col-md-6"l><"col-md-6"f>><"row"<"col-md-12"rt>><"row"<"col-md-6"i><"col-md-6"p>>',
            "drawCallback": function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
            }
        });
        
        // Add hover effect to rows
        $('#packaged-attractions-table tbody tr').hover(
            function() { $(this).addClass('bg-light'); },
            function() { $(this).removeClass('bg-light'); }
        );
    });
</script>

<style>
    .bg-glow {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        font-size: 1rem;
    }
    .avatar-initial {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .table th {
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    .btn-primary, .bg-primary {
        background-image: linear-gradient(135deg, #6B73FF 10%, #000DFF 100%);
        border: none;
    }
    .btn-info {
        background-image: linear-gradient(135deg, #49C4E5 10%, #1A9CC7 100%);
        border: none;
    }
    .btn-danger {
        background-image: linear-gradient(135deg, #FF6B6B 10%, #FF0000 100%);
        border: none;
    }
    .badge.bg-success {
        background-image: linear-gradient(135deg, #4CAF50 10%, #2E7D32 100%) !important;
    }
    .badge.bg-danger {
        background-image: linear-gradient(135deg, #FF5252 10%, #D32F2F 100%) !important;
    }
    .rounded-pill {
        border-radius: 50rem !important;
    }
</style>
@endsection
