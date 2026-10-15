@extends('layouts.layout')
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

    /* Alerts spacing */
    .page-toolbar {
        padding: 0.75rem 1.25rem;
        background: #f8fafc;
        border-bottom: 1px solid #e9edf3;
    }

    /* Table shell + premium table */
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

    .table-premium thead th {
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

    .table-premium tbody td {
        vertical-align: middle;
        font-size: 12.5px;
        color: #334155;
        padding: 0.55rem 0.75rem !important;
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

    /* Status pill button */
    .btn-status {
        border-radius: 999px;
        font-weight: 800;
        font-size: 11.5px;
        padding: 0.28rem 0.6rem;
        border: 1px solid transparent;
        letter-spacing: 0.02em;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .btn-status .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }

    .btn-status.btn-success {
        background: #ecfdf5 !important;
        border-color: #a7f3d0 !important;
        color: #065f46 !important;
    }

    .btn-status.btn-success .dot {
        background: #10b981;
    }

    .btn-status.btn-danger {
        background: #fef2f2 !important;
        border-color: #fecaca !important;
        color: #7f1d1d !important;
    }

    .btn-status.btn-danger .dot {
        background: #ef4444;
    }

    /* Action buttons */
    .btn-icon {
        width: 32px;
        height: 32px;
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

    .btn-icon-view {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1d4ed8;
    }

    .btn-icon-edit {
        background: #eef2ff;
        border-color: #c7d2fe;
        color: #4f46e5;
    }

    .btn-icon-delete {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
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

    .dataTables_wrapper .dataTables_filter {
        display: flex;
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

    .dataTables_wrapper .dataTables_paginate {
        display: none !important;
    }

    .ports-infinite-footer {
        padding: 1rem 0 0.25rem;
        text-align: center;
    }

    .ports-infinite-footer .ports-scroll-loader {
        display: none;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-size: 12.5px;
        font-weight: 600;
        color: #64748b;
    }

    .ports-infinite-footer.is-loading .ports-scroll-loader {
        display: inline-flex;
    }

    .ports-infinite-footer .ports-scroll-end {
        display: none;
        font-size: 12px;
        font-weight: 600;
        color: #94a3b8;
    }

    .ports-infinite-footer.is-end .ports-scroll-end {
        display: block;
    }

    .spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Hide default DataTables buttons (we use custom Export dropdown) */
    .dataTables_wrapper .dt-buttons {
        display: none !important;
    }

    @media (max-width: 768px) {
        .page-header { padding: 0.9rem 1rem; }
        .page-title { font-size: 1.2rem; }
        .page-toolbar { padding: 0.7rem 1rem; }
        .table-shell { padding: 0.85rem 1rem 1rem; }
    }
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <!-- Header -->
            <div class="page-header">
                <div>
                    <h5 class="page-title">Port Listing</h5>
                    <p class="page-subtitle">Manage ports, locations, status, and actions</p>
                </div>
                <div class="toolbar-actions">
                    @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                    <a href="{{ route('ports.create') }}" class="btn btn-primary btn-premium">
                        <i class="mdi mdi-plus"></i> Add New Port
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

            <!-- Toolbar (alerts only) -->
            <div class="page-toolbar">
                <x-alert />
            </div>

            <div class="card-body p-0">
                {{-- @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif --}}

                {{-- <div class="table-responsive text-nowrap"> --}}
                    {{-- <table class="table table-hover" > --}}
                <div class="table-shell">
                        <table class="datatables-basic table table-bordered table-premium" id="portsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Port Name</th>
                                <th>Country</th>
                                <th>City</th>
                                <th>Coordinates</th>
                                {{-- <th>Distance (miles)</th> --}}
                                <th>Status</th>
                                <th>Actions</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach($ports as $index => $port)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <span>{{ $port->port_name }}</span>
                                        <div>
                                            @php
                                                $portTypeRaw = $port->type ?? '';
                                                $portTypeKey = strtolower(preg_replace('/\s+/', '', trim($portTypeRaw)));

                                                switch ($portTypeKey) {
                                                    case 'airport':
                                                        $portTypeBadge = 'primary';
                                                        break;
                                                    case 'landport':
                                                        $portTypeBadge = 'success';
                                                        break;
                                                    case 'railway':
                                                    case 'railwayport':
                                                        $portTypeBadge = 'warning';
                                                        break;
                                                    case 'seaport':
                                                        $portTypeBadge = 'info';
                                                        break;
                                                    default:
                                                        $portTypeBadge = 'secondary';
                                                        break;
                                                }
                                            @endphp

                                            <span class="badge bg-label-{{ $portTypeBadge }}">
                                                {{ $port->type ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $port->country ?? 'N/A' }}</td>
                                <td>{{ $port->city->name ?? 'N/A' }}</td>
                                <td>
                                    <div style="white-space: nowrap;">
                                        <strong>Lat:</strong> {{ $port->latitude ?? 'N/A' }}
                                       <br>
                                        <strong>Lng:</strong> {{ $port->longitude ?? 'N/A' }}
                                    </div>
                                </td>
                                {{-- <td>{{ $port->distance }}</td> --}}
                                <td>
                                    <form action="{{ route('port.toggle-status', Crypt::encrypt($port->port_id)) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-status btn-sm {{ $port->status ? 'btn-success' : 'btn-danger' }}">
                                            <span class="dot"></span>{{ $port->status ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <!-- View -->
                                        <a href="{{ route('ports.show', Crypt::encrypt($port->port_id)) }}" 
                                           class="btn btn-icon btn-icon-view"
                                           title="View">
                                            <i class="ri-eye-line" style="font-size: 16px;"></i>
                                        </a>

                                        <!-- Edit -->
                                        <a href="{{ route('ports.edit', Crypt::encrypt($port->port_id)) }}" 
                                           class="btn btn-icon btn-icon-edit"
                                           title="Edit">
                                            <i class="ri-pencil-line" style="font-size: 16px;"></i>
                                        </a>

                                        <!-- Delete -->
                                        <form action="{{ route('ports.destroy', Crypt::encrypt($port->port_id)) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    class="btn btn-icon btn-icon-delete"
                                                    title="Delete"
                                                    onclick="deletePort(this)">
                                                <span class="d-none port-name">{{ addslashes($port->port_name) }}</span>
                                                <span class="d-none delete-url">{{ route('ports.destroy', Crypt::encrypt($port->port_id)) }}</span>
                                                <i class="ri-delete-bin-line" style="font-size: 16px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span>{{ $port->created_at->format('D,  M d, Y') }}</span>
                                        <small class="text-muted">{{ $port->created_at->format('h:i A') }}</small>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                <div id="portsInfiniteFooter" class="ports-infinite-footer">
                    <div class="ports-scroll-loader">
                        <i class="ri-loader-4-line spin"></i>
                        <span>Loading more ports…</span>
                    </div>
                    <div class="ports-scroll-end">All ports loaded</div>
                </div>
                </div>
                {{-- </div> --}}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<!-- DataTable JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<!-- DataTables Initialization Script -->
<script>
    $(document).ready(function() {
        var portsBatchSize = 25;
        var portsTable = $('.datatables-basic').DataTable({
            responsive: false,
            autoWidth: false,
            dom: '<"row align-items-center mb-2"<"col-sm-6 col-12"l><"col-sm-6 col-12"f>>rt<"row align-items-center mt-2"<"col-sm-12"i>>',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            },
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: portsBatchSize,
            paging: true,
            pagingType: 'simple_numbers',
        });

        var $infiniteFooter = $('#portsInfiniteFooter');
        var portsInfiniteLoading = false;
        var portsInfiniteGuard = false;

        function updatePortsInfiniteFooter() {
            var info = portsTable.page.info();
            var allVisible = info.recordsDisplay <= info.length;
            $infiniteFooter.toggleClass('is-end', allVisible && info.recordsDisplay > 0);
            $infiniteFooter.removeClass('is-loading');
            portsInfiniteLoading = false;
        }

        function loadMorePortsIfNeeded() {
            if (portsInfiniteLoading) return;
            var info = portsTable.page.info();
            if (info.recordsDisplay <= info.length) {
                updatePortsInfiniteFooter();
                return;
            }
            var scrollBottom = $(window).scrollTop() + $(window).height();
            if (scrollBottom < $(document).height() - 120) return;
            portsInfiniteLoading = true;
            $infiniteFooter.addClass('is-loading').removeClass('is-end');
            setTimeout(function() {
                var currentInfo = portsTable.page.info();
                portsTable.page.len(Math.min(currentInfo.length + portsBatchSize, currentInfo.recordsDisplay)).draw(false);
                updatePortsInfiniteFooter();
            }, 150);
        }

        portsTable.on('length.dt', function(e, settings, len) {
            portsBatchSize = len;
            updatePortsInfiniteFooter();
        });
        portsTable.on('search.dt', function() {
            if (portsInfiniteGuard) return;
            portsInfiniteGuard = true;
            portsTable.page.len(portsBatchSize).draw(false);
            portsInfiniteGuard = false;
            updatePortsInfiniteFooter();
        });
        portsTable.on('draw.dt', function() {
            if (!portsInfiniteGuard) updatePortsInfiniteFooter();
        });
        $(window).on('scroll.portsInfinite resize.portsInfinite', loadMorePortsIfNeeded);
        updatePortsInfiniteFooter();
        loadMorePortsIfNeeded();

        $('#exportCopy').on('click', function() { portsTable.button('.buttons-copy').trigger(); });
        $('#exportCSV').on('click', function() { portsTable.button('.buttons-csv').trigger(); });
        $('#exportExcel').on('click', function() { portsTable.button('.buttons-excel').trigger(); });
        $('#exportPDF').on('click', function() { portsTable.button('.buttons-pdf').trigger(); });
        $('#exportPrint').on('click', function() { portsTable.button('.buttons-print').trigger(); });
    });
</script>
<!-- End DataTable JS -->
 
<script>
    window.deletePort = function(btn) {
        const wrap = btn.closest('form');
        const portName = btn.querySelector('.port-name')?.textContent || 'this port';
        const deleteUrl = btn.querySelector('.delete-url')?.textContent;

        Swal.fire({
            title: 'Delete Port?',
            text: `Are you sure you want to delete \"${portName}\"? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // submit the existing form to preserve csrf/method
                if (wrap) wrap.submit();
            }
        });
    }
</script>
{{-- <script>
    $(document).ready(function() {
        $('#portsTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [10, 25, 50, 100],
            "ordering": true,
            "info": true,
            "searching": true,
            "responsive": true
        });
    });
</script> --}}
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
@endsection 