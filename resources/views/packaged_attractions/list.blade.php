@extends('layouts.layout')
@section('title', 'Packaged Attractions')
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

    /* Status badges */
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

    .btn-icon + .btn-icon {
        margin-left: 3px;
    }

    .btn-icon:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.10);
    }

    .btn-icon-view {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }

    .btn-icon-edit {
        background: #ecfdf5;
        border-color: #d1fae5;
    }

    .btn-icon-delete {
        background: #fef2f2;
        border-color: #fecaca;
    }

    /* Col sizing */
    #packagedAttractionsTable .col-action {
        width: 90px;
        min-width: 90px;
        white-space: nowrap;
    }

    #packagedAttractionsTable .col-created-at {
        font-size: 10.5px;
        white-space: nowrap;
    }

    .th-tooltip {
        cursor: help;
    }

    /* Loading spinner */
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
                        <h5 class="page-title">Packaged Attractions</h5>
                        <p class="page-subtitle">Manage attraction packages, pricing, and status</p>
                    </div>
                    <div class="toolbar-actions">
                        <a href="{{ route('packaged-attractions.create') }}" class="btn btn-primary btn-premium">
                            <i class="fas fa-plus"></i> Create New Package
                        </a>

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
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <div>{{ session('success') }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="table-shell">
                <table class="datatables-basic table table-bordered table-premium" id="packagedAttractionsTable">
                    <thead>
                        <tr>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Serial Number">No</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Package Name & ID">Package Name</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Adult Price">Adult Price</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Child Price">Child Price</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Senior Price">Senior Price</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Current Status">Status</th>
                            <th class="th-tooltip col-action" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Available Actions">Action</th>
                            <th class="th-tooltip col-created-at" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Created Date & Time">Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packagedAttractions as $key => $attraction)
                            <tr>
                                <td>{{ $attraction->id }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold">{{ $attraction->name }}</span>
                                        <small class="text-muted">ID: {{ $attraction->package_attraction_id ?? 'N/A' }}</small>
                                    </div>
                                </td>
                                <td>${{ number_format($attraction->adult_price, 2) }}</td>
                                <td>${{ number_format($attraction->child_price, 2) }}</td>
                                <td>${{ number_format($attraction->senior_citizen_price, 2) }}</td>
                                <td>
                                    @if($attraction->status == 1)
                                        <span class="badge-status active"><span class="dot"></span>Active</span>
                                    @else
                                        <span class="badge-status inactive"><span class="dot"></span>Inactive</span>
                                    @endif
                                </td>
                                <td class="col-action">
                                    <!-- View Button -->
                                    <a href="{{ route('packaged-attractions.show', Crypt::encrypt($attraction->package_attraction_id)) }}"
                                       class="btn btn-icon btn-icon-view th-tooltip"
                                       data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="View Package">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="15px" viewBox="0 -960 960 960" width="15px" fill="#059669">
                                            <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Z"/>
                                        </svg>
                                    </a>

                                    <!-- Edit Button -->
                                    <a href="{{ route('packaged-attractions.edit', Crypt::encrypt($attraction->package_attraction_id)) }}"
                                       class="btn btn-icon btn-icon-edit th-tooltip"
                                       data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit Package">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="15px" viewBox="0 -960 960 960" width="15px" fill="#059669">
                                            <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/>
                                        </svg>
                                    </a>

                                    <!-- Delete Button -->
                                    @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                                    <button type="button"
                                            class="btn btn-icon btn-icon-delete th-tooltip"
                                            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete Package"
                                            onclick="deletePackagedAttraction('{{ route('packaged-attractions.destroy', Crypt::encrypt($attraction->package_attraction_id)) }}', '{{ addslashes($attraction->name) }}')"
                                            id="delete-btn-{{ $attraction->package_attraction_id }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="15px" viewBox="0 -960 960 960" width="15px" fill="#dc2626">
                                            <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                        </svg>
                                    </button>
                                    @endif
                                </td>
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
@endsection

@section('scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<!-- DataTable JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('.datatables-basic').DataTable({
            responsive: true,
            dom: '<"row align-items-center mb-2"<"col-sm-6 col-12"l><"col-sm-6 col-12"f>>rt<"row align-items-center mt-2"<"col-sm-6 col-12"i><"col-sm-6 col-12"p>>',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            },
            lengthMenu: [10, 25, 50, 100],
            pagingType: 'simple_numbers',
        });

        // Export dropdown wiring
        $('#exportCopy').on('click', function() { $('.datatables-basic').DataTable().button('.buttons-copy').trigger(); });
        $('#exportCSV').on('click', function() { $('.datatables-basic').DataTable().button('.buttons-csv').trigger(); });
        $('#exportExcel').on('click', function() { $('.datatables-basic').DataTable().button('.buttons-excel').trigger(); });
        $('#exportPDF').on('click', function() { $('.datatables-basic').DataTable().button('.buttons-pdf').trigger(); });
        $('#exportPrint').on('click', function() { $('.datatables-basic').DataTable().button('.buttons-print').trigger(); });
    });

    // Initialise Bootstrap tooltips for headers + action buttons
    function initTooltips() {
        if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;
        document.querySelectorAll('#packagedAttractionsTable .th-tooltip[data-bs-toggle="tooltip"]').forEach(function(el) {
            const existing = bootstrap.Tooltip.getInstance(el);
            if (existing) existing.dispose();
            new bootstrap.Tooltip(el, { container: 'body', trigger: 'hover focus' });
        });
    }

    $(document).ready(function() {
        initTooltips();
        $('#packagedAttractionsTable').on('draw.dt', function() { initTooltips(); });
    });

    // SweetAlert2 delete
    window.deletePackagedAttraction = function(deleteUrl, packageName) {
        Swal.fire({
            title: 'Delete Package?',
            text: `Are you sure you want to delete "${packageName}"? This action cannot be undone.`,
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
</script>
@endsection
