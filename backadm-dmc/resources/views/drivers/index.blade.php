@extends('layouts.layout')
@section('title', 'Drivers')
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
    #driversTable {
        table-layout: fixed;
        width: 100%;
        font-size: 0.8rem;
    }

    #driversTable thead th {
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

    #driversTable tbody td {
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
    #driversTable thead th:nth-child(1),
    #driversTable tbody td:nth-child(1) { /* No */
        width: 3%;
    }

    /* Email column - increased width */
    #driversTable thead th.email-column,
    #driversTable tbody td.email-column {
        width: 15%;
    }

    /* Driver name styling */
    #driversTable .driver-detail-title {
        font-weight: 600;
        font-size: 0.8rem;
        color: var(--table-body-text);
    }

    /* Calendar link styling */
    #driversTable .calendar-link {
        color: var(--table-link);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    #driversTable .calendar-link:hover {
        text-decoration: underline;
        color: var(--table-link);
    }

    #driversTable .calendar-link i {
        font-size: 0.875rem;
    }

    /* Status badge styling */
    #driversTable .badge {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.35rem 0.65rem;
        border-radius: 0.375rem;
    }

    #driversTable .badge.bg-success {
        background-color: var(--table-link) !important;
        color: #ffffff;
    }

    #driversTable .badge.bg-danger {
        background-color: #e5e7eb !important;
        color: #111827;
    }

    /* Subtle muted text */
    #driversTable .text-muted {
        color: var(--table-muted);
        font-size: 0.875rem;
    }

    /* Created date styling */
    #driversTable .d-flex.flex-column span {
        font-size: 0.875rem;
    }

    #driversTable .d-flex.flex-column small {
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
    #driversTable td.col-actions {
        white-space: normal;
        overflow: visible;
    }

    #driversTable th.col-actions-header,
    #driversTable td.col-actions {
        width: 11%;
    }

    #driversTable .actions-icons-wrap {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, auto));
        row-gap: 0.35rem;
        column-gap: 0.35rem;
        align-items: center;
        justify-content: start;
        max-width: 100%;
    }

    #driversTable .action-icon-badge {
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

    #driversTable .action-icon-badge:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        color: inherit;
    }

    #driversTable .action-icon-badge i {
        font-size: 1rem;
        color: var(--action-color, #475569);
    }

    #driversTable .action-icon-badge:hover i {
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
                        <h5 class="card-title mb-0">Drivers</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        {{-- @if($pendingDrivers->count() > 0 && ($user->user_type == 1)) --}}
                            {{-- <a href="#" data-bs-toggle="modal" data-bs-target="#approveModal" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="24" height="24"><path fill="#f5ad47" d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7 .2 40.1S486.3 480 472 480L40 480c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8 .2-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24l0 112c0 13.3 10.7 24 24 24s24-10.7 24-24l0-112c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/></svg>
                                <div data-i18n="Approve Driver">
                                    Approve Driver
                                </div>
                            </a> --}}
                        {{-- @endif --}}
                        <!-- Add New Driver Button -->
                        @if(hasPermission('create driver'))
                            <a href="{{ route('driver.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                                <i class="fas fa-plus"></i> Add New Driver
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
                <table class="datatables-basic table table-bordered" id="driversTable">
                    <thead>
                        <tr>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Serial Number">No</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Driver Name">Driver Name</th>
                            @php
                                $roleId = auth()->user()->role_id;
                            @endphp
                            @php
                                $hideRoles = [11, 20, 35, 130, 132, 133, 135, 136, 137, 138, 76, 111, 139, 140];
                            @endphp

                            @if($roleId == 10)
                                <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Destination Management Company">DMC</th>
                            @elseif(!in_array($roleId, $hideRoles))
                                <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Master DMC">Master</th>
                                <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Destination Management Company">DMC</th>
                            @endif
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="City">City</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Mobile Number">Mobile</th>
                            <th class="th-tooltip email-column" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Email Address">Email</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="License Number">License</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Status">Status</th>
                            @if(auth()->user()->role_id == 1 || auth()->user()->userId == 2 || auth()->user()->role_id == 46  || auth()->user()->role_id == 23 || auth()->user()->role_id == 76 || auth()->user()->role_id ==109 || auth()->user()->role_id == 111 || auth()->user()->role_id == 139 || auth()->user()->role_id == 140 || hasPermission('edit driver') || hasPermission('delete driver'))
                                <th class="th-tooltip col-actions-header" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Available Actions">Actions</th>
                            @endif
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Created Date and Time">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($drivers as $key => $driver)
                            <tr>
                                <td class="align-top">{{ ++$key }}</td>
                                <td class="align-top">
                                    <div class="d-flex flex-column">
                                        <span class="driver-detail-title">{{ $driver->name }}</span>
                                    </div>
                                </td>
                                @php
                                    $roleId = auth()->user()->role_id;
                                @endphp
                                @php
                                    $hideRoles = [11, 20, 35, 130, 132, 133, 135, 136, 137, 138, 76, 111, 139, 140];
                                @endphp

                                @if($roleId == 10)
                                    @php
                                        $dmcUser = App\Models\User::where('userId', $driver->dmc_id)->first();
                                    @endphp
                                    <td class="align-top">{{ $dmcUser ? $dmcUser->company_name : 'N/A' }}</td>
                                @elseif(!in_array($roleId, $hideRoles))
                                    @php
                                        $dmcUser = App\Models\User::where('userId', $driver->dmc_id)->first();
                                        $masterdmcUser = $dmcUser ? App\Models\User::where('userId', $dmcUser->master_dmc_id)->first() : null;
                                    @endphp
                                    <td class="align-top">{{ $masterdmcUser ? $masterdmcUser->company_name : 'N/A' }}</td>
                                    <td class="align-top">{{ $dmcUser ? $dmcUser->company_name : 'N/A' }}</td>
                                @endif
                                <td class="align-top">{{ $driver->city }}</td>
                                <td class="align-top">{{ $driver->phone }}</td>
                                <td class="align-top email-column">{{ $driver->email }}</td>
                                <td class="align-top">{{ $driver->license_no }}</td>
                                <td class="align-top">
                                    @if($driver->is_active == 1)
                                        <span class="badge bg-success">Available</span>
                                    @else
                                        <span class="badge bg-danger">Not Available</span>
                                    @endif
                                </td>
                                @if(hasPermission('edit driver') || hasPermission('delete driver'))
                                    @if($driver->status == 1)
                                        <td class="align-top col-actions">
                                            <div class="actions-icons-wrap">
                                                <!-- View Calendar Button -->
                                                <a href="{{ route('driver.calendar', Crypt::encrypt($driver->driver_id)) }}"
                                                   target="_blank"
                                                   class="action-icon-badge"
                                                   style="--action-color: #2563eb;"
                                                   data-tooltip="View Driver Calendar">
                                                    <i class="ri-calendar-line"></i>
                                                </a>

                                                <!-- Edit Button -->
                                                @if(hasPermission('edit driver'))
                                                <a href="{{ route('driver.edit', Crypt::encrypt($driver->driver_id)) }}"
                                                   class="action-icon-badge"
                                                   style="--action-color: #047857;"
                                                   data-tooltip="Edit Driver">
                                                    <i class="ri-pencil-line"></i>
                                                </a>
                                                @endif

                                                <!-- Delete Button -->
                                                @if(hasPermission('delete driver'))
                                                <button type="button"
                                                        class="action-icon-badge"
                                                        style="--action-color: #dc2626;"
                                                        data-tooltip="Delete Driver"
                                                        onclick="deleteDriver('{{ route('driver.destroy', Crypt::encrypt($driver->driver_id)) }}', {{ json_encode($driver->name) }})"
                                                        id="delete-btn-{{ $driver->driver_id }}">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    @else
                                        <td class="align-top">
                                            @if($driver->status == 5)
                                                <span>Your Driver, awaiting for Admin approval</span>
                                            @elseif($driver->status == 3)
                                                <span>Declined</span>
                                            @endif
                                        </td>
                                    @endif
                                @endif
                                <td class="align-top">
                                    <div class="d-flex flex-column">
                                        <span>{{ $driver->created_at->format('D,  M d, Y') }}</span>
                                        <small class="text-muted">{{ $driver->created_at->format('h:i A') }}</small>
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
    // Body-level tooltip for driver headers (use jQuery tooltip to avoid version issues)
    function initDriverHeaderTooltips() {
        if (typeof $ === 'undefined' || typeof $.fn.tooltip !== 'function') return;

        $('#driversTable thead .th-tooltip[data-bs-toggle="tooltip"]')
            .tooltip('dispose')
            .tooltip({
                container: 'body',
                trigger: 'hover focus'
            });
    }

    // Ensure modal can be closed with escape key and click outside, and init action tooltips
    $(document).ready(function() {
        initDriverHeaderTooltips();
        $('#driversTable').on('draw.dt', function() {
            initDriverHeaderTooltips();
        });

        // Global tooltip element for action icons (same pattern as Restaurants/Attractions)
        var $globalTooltip = $('#service-icon-global-tooltip');
        if (!$globalTooltip.length) {
            $globalTooltip = $('<div id="service-icon-global-tooltip" aria-hidden="true"></div>').appendTo('body');
        } else {
            $globalTooltip.appendTo('body');
        }

        // Show tooltip on hover over action icon badges in Drivers table
        $('#driversTable').on('mouseenter', '.action-icon-badge', function() {
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

        $('#driversTable').on('mouseleave', '.action-icon-badge', function() {
            $globalTooltip.hide();
        });
    });

    // Driver deletion function with SweetAlert (mirrors Restaurants delete behaviour)
    window.deleteDriver = function(deleteUrl, driverName) {
        Swal.fire({
            title: 'Delete Driver?',
            text: `Are you sure you want to delete "${driverName}"? This action cannot be undone.`,
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

<!--AJAX Call to approve driver-->

<script>
    $(document).ready(function() {
        $('#approveTable').DataTable({
            responsive: true,
            ordering: false,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            },
            lengthMenu: [10, 25, 50, 100],
        });
    
        $(".approve-btn, .decline-btn").click(function() {
            let driverId = $(this).data("id");
            let action = $(this).data("action");
    
            $.ajax({
                url: "/driver/approve-or-decline/" + driverId,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    action: action
                },
                success: function(response) {
                    if (response.success) {
                        let driverRow = $("#driver-" + driverId);
                        driverRow.find(".approve-btn, .decline-btn").remove();
                        driverRow.find(".status-message").text(action === 'approve' ? "Guide Approved!" : "Guide Declined!").css("color", action === 'approve' ? "green" : "red").show();
                    } else {
                        alert("Error processing request.");
                    }
                },
                error: function() {
                    alert("Something went wrong!");
                }
            });
        });
    });
    </script>
@endsection
