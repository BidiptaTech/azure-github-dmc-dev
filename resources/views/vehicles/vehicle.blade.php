@extends('layouts.layout')
@section('title', 'Vehicle')
@extends('layouts.datatablecss')

@section('css')
<!-- Add SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<style>
    :root {
        --table-border: #e2e8f0;
        --table-head-bg: #f8fafc;
        --table-head-text: #334155;
        --table-body-text: #0f172a;
        --table-muted: #64748b;
        --table-link: #5c61e6;
    }

    /* Make the table compact and avoid horizontal scrolling */
    #vehiclesTable {
        table-layout: fixed;
        width: 100%;
        font-size: 0.8rem;
    }

    #vehiclesTable thead th {
        background: var(--table-head-bg);
        color: var(--table-head-text);
        font-weight: 600;
        font-size: 0.78rem;
        padding: 0.35rem 0.4rem;
        border-color: var(--table-border);
        white-space: normal;
        word-wrap: break-word;
        line-height: 1.3;
    }

    #vehiclesTable tbody td {
        color: var(--table-body-text);
        border-color: var(--table-border);
        vertical-align: top;
        padding: 0.35rem 0.4rem;
        font-size: 0.8rem;
        line-height: 1.4;
        white-space: normal;
        word-wrap: break-word;
    }

    /* Column width adjustments */
    #vehiclesTable thead th:nth-child(1),
    #vehiclesTable tbody td:nth-child(1) { /* No */
        width: 3%;
    }

    #vehiclesTable thead th:nth-child(2),
    #vehiclesTable tbody td:nth-child(2) { /* Vehicle Name */
        width: 12%;
    }

    #vehiclesTable thead th:nth-child(3),
    #vehiclesTable tbody td:nth-child(3) { /* DMC Company */
        width: 15%;
    }

    /* Availability column - increased width */
    #vehiclesTable thead th:nth-child(8),
    #vehiclesTable tbody td:nth-child(8) { /* Availability */
        width: 12%;
    }

    /* Vehicle name styling */
    #vehiclesTable .vehicle-detail-title {
        font-weight: 600;
        font-size: 0.8rem;
        color: var(--table-body-text);
    }

    /* Status badge styling */
    #vehiclesTable .badge {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.35rem 0.65rem;
        border-radius: 0.375rem;
    }

    #vehiclesTable .badge.bg-success {
        background-color: var(--table-link) !important;
        color: #ffffff;
    }

    #vehiclesTable .badge.bg-danger {
        background-color: #e5e7eb !important;
        color: #111827;
    }

    /* Subtle muted text */
    #vehiclesTable .text-muted {
        color: var(--table-muted);
        font-size: 0.875rem;
    }

    /* Created date styling */
    #vehiclesTable .d-flex.flex-column span {
        font-size: 0.875rem;
    }

    #vehiclesTable .d-flex.flex-column small {
        font-size: 0.75rem;
    }

    .th-tooltip {
        cursor: help;
    }

    /* Global tooltip */
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
        transform: translate(-50%, -100%);
    }

    /* Actions column */
    #vehiclesTable td.col-actions {
        white-space: normal;
        overflow: visible;
    }

    #vehiclesTable th.col-actions-header,
    #vehiclesTable td.col-actions {
        width: 11%;
    }

    #vehiclesTable .actions-icons-wrap {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, auto));
        row-gap: 0.35rem;
        column-gap: 0.35rem;
        align-items: center;
        justify-content: start;
        max-width: 100%;
    }

    #vehiclesTable .action-icon-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        min-width: 28px;
        padding: 0.25rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        cursor: pointer;
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        flex-shrink: 0;
        text-decoration: none;
        color: inherit;
    }

    #vehiclesTable .action-icon-badge:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        color: inherit;
    }

    #vehiclesTable .action-icon-badge i {
        font-size: 1rem;
        color: var(--action-color, #475569);
    }

    #vehiclesTable .action-icon-badge:hover i {
        color: var(--action-color, #0f766e);
    }

    /* Loading spinner animation */
    .spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Vehicles</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        <!-- Add New Vehicle Button -->
                        @if(hasPermission('create vehicle'))
                            <a href="{{ route('vehicle.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                                <i class="fas fa-plus"></i> Add New Vehicle
                            </a>
                        @endif

                        <!-- Export Dropdown Button -->
                        <div class="dropdown">
                            <button class="btn btn-warning btn-sm dropdown-toggle" type="button" id="exportDropdown"
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
                <x-alert />
                <hr>
                
                <table class="datatables-basic table table-bordered" id="vehiclesTable">
                    <thead>
                        <tr>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Serial Number">No</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Vehicle Name">Vehicle Name</th>
                            @php
                                $roleId = auth()->user()->role_id;
                            @endphp

                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Destination Management Company">DMC Company</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Vehicle Type">Type</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Vehicle Model">Model</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Model Year">Year</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Seating Capacity">Capacity</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Availability Status">Availability</th>
                            @if(hasPermission('edit vehicle') || hasPermission('delete vehicle'))
                                <th class="th-tooltip col-actions-header" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Available Actions">Actions</th>
                            @endif
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Created Date and Time">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vehicles as $key => $vehicle)
                            <tr>
                                <td class="align-top">{{ ++$key }}</td>
                                <td class="align-top">
                                    <div class="d-flex flex-column">
                                        <span class="vehicle-detail-title">{{ $vehicle->vehicle_name }}</span>
                                    </div>
                                </td>
                                @php
                                    $roleId = auth()->user()->role_id;
                                @endphp

                                <td class="align-top">
                                    {{ $vehicle->dmc ? $vehicle->dmc->company_name : 'N/A' }}
                                </td>
                                <td class="align-top">{{ $vehicle->vehicle_type }}</td>
                                <td class="align-top">{{ $vehicle->vehicle_model }}</td>
                                <td class="align-top">{{ $vehicle->model_year }}</td>
                                <td class="align-top">{{ $vehicle->seating_capacity }}</td>
                                <td class="align-top">
                                    @if($vehicle->is_available == 1)
                                        <span class="badge bg-success">Available</span>
                                    @else
                                        <span class="badge bg-danger">Not Available</span>
                                    @endif
                                </td>
                                @if(hasPermission('edit vehicle') || hasPermission('delete vehicle'))
                                <td class="align-top col-actions">
                                    <div class="actions-icons-wrap">
                                        <!-- Edit Button -->
                                        @if(hasPermission('edit vehicle'))
                                        <a href="{{ route('vehicle.edit', Crypt::encrypt($vehicle->vehicle_id)) }}"
                                           class="action-icon-badge"
                                           style="--action-color: #047857;"
                                           data-tooltip="Edit Vehicle">
                                            <i class="ri-pencil-line"></i>
                                        </a>
                                        @endif

                                        <!-- Delete Button -->
                                        @if(hasPermission('delete vehicle'))
                                        <button type="button"
                                                class="action-icon-badge"
                                                style="--action-color: #dc2626;"
                                                data-tooltip="Delete Vehicle"
                                                onclick="deleteVehicle('{{ route('vehicle.destroy', Crypt::encrypt($vehicle->vehicle_id)) }}', {{ json_encode($vehicle->vehicle_name) }})"
                                                id="delete-btn-{{ $vehicle->vehicle_id }}">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                                @endif
                                <td class="align-top">
                                    <div class="d-flex flex-column">
                                        <span>{{ $vehicle->created_at->format('D,  M d, Y') }}</span>
                                        <small class="text-muted">{{ $vehicle->created_at->format('h:i A') }}</small>
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

<!-- Add SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<!-- DataTable JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<!-- DataTables Initialization Script -->
<script>
    $(document).ready(function() {
        // Initialize DataTable with export buttons
        $('.datatables-basic').DataTable({
            // Keep column widths stable (avoid jump after initialization)
            responsive: false,
            autoWidth: false,
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
    // Body-level tooltip for vehicle headers (use jQuery tooltip to avoid version issues)
    function initVehicleHeaderTooltips() {
        if (typeof $ === 'undefined' || typeof $.fn.tooltip !== 'function') return;

        $('#vehiclesTable thead .th-tooltip[data-bs-toggle="tooltip"]')
            .tooltip('dispose')
            .tooltip({
                container: 'body',
                trigger: 'hover focus'
            });
    }

    // Ensure modal can be closed with escape key and click outside, and init action tooltips
    $(document).ready(function() {
        initVehicleHeaderTooltips();
        $('#vehiclesTable').on('draw.dt', function() {
            initVehicleHeaderTooltips();
        });

        // Global tooltip element for action icons (same pattern as Restaurants/Attractions)
        var $globalTooltip = $('#service-icon-global-tooltip');
        if (!$globalTooltip.length) {
            $globalTooltip = $('<div id="service-icon-global-tooltip" aria-hidden="true"></div>').appendTo('body');
        } else {
            $globalTooltip.appendTo('body');
        }

        // Show tooltip on hover over action icon badges in Vehicles table
        $('#vehiclesTable').on('mouseenter', '.action-icon-badge', function() {
            var $w = $(this);
            var text = $w.attr('data-tooltip') || $w.attr('title') || '';
            if (!text) return;
            var el = this;
            var rect = el.getBoundingClientRect();
            $globalTooltip.css({
                display: 'block',
                left: (rect.left + rect.width / 2) + 'px',
                top: (rect.top - 6) + 'px',
                transform: 'translate(-50%, -100%)'
            }).text(text);
        });

        $('#vehiclesTable').on('mouseleave', '.action-icon-badge', function() {
            $globalTooltip.hide();
        });
    });

    // Vehicle deletion function with SweetAlert (mirrors Restaurants delete behaviour)
    window.deleteVehicle = function(deleteUrl, vehicleName) {
        Swal.fire({
            title: 'Delete Vehicle?',
            text: `Are you sure you want to delete "${vehicleName}"? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, keep it'
        }).then((result) => {
            if (result.isConfirmed) {
                const button = document.querySelector(`[onclick*="${deleteUrl}"]`);
                const originalContent = button ? button.innerHTML : '';

                if (button) {
                    button.innerHTML = '<i class="ri-loader-4-line spin"></i> Deleting...';
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
