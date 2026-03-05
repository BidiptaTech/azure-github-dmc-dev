@extends('layouts.layout')
@section('title', 'Hotel Listing')

@section('css')
<!-- DataTable CSS -->
<link rel="stylesheet" href="{{ env('APP_URL') . '/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css' }}" />
<link rel="stylesheet" href="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css' }}" />
<link rel="stylesheet" href="{{ env('APP_URL') . '/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css' }}" />
<link rel="stylesheet" href="{{ env('APP_URL') . '/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css' }}" />
<link rel="stylesheet" href="{{ env('APP_URL') . '/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css' }}" />
<link rel="stylesheet" href="{{ env('APP_URL') . '/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css' }}" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
<!-- Add SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<style>
    /* Ensure user profile dropdown is visible on hotels page */
    .topbar-item {
        display: block !important;
        visibility: visible !important;
    }

    .topbar-link {
        display: flex !important;
        visibility: visible !important;
    }

    .navbar-nav {
        display: flex !important;
    }

    /* Force show the dropdown arrow */
    .topbar-link .ri-arrow-down-s-line {
        display: flex !important;
        visibility: visible !important;
    }

    /* Ensure ONLY the user profile dropdown menu is properly positioned */
    .topbar-item .dropdown-menu {
        z-index: 9999 !important;
        display: none;
    }

    .topbar-item .dropdown-menu.show {
        display: block !important;
    }

    /* Ensure Export dropdown works normally */
    #exportDropdown + .dropdown-menu {
        z-index: 1000 !important;
        display: none;
    }

    #exportDropdown + .dropdown-menu.show {
        display: block !important;
    }

    :root {
        --table-border: #e2e8f0;
        --table-head-bg: #f8fafc;
        --table-head-text: #334155;
        --table-body-text: #0f172a;
        --table-muted: #64748b;
        --table-link: #0f766e;
    }

    #hotelsTable thead th {
        background: var(--table-head-bg);
        color: var(--table-head-text);
        font-weight: 600;
        font-size: 0.8125rem;
        border-color: var(--table-border);
        white-space: nowrap;
    }

    #hotelsTable tbody td {
        color: var(--table-body-text);
        border-color: var(--table-border);
        vertical-align: top;
    }

    #hotelsTable .hotel-detail-title {
        font-weight: 600;
    }

    #hotelsTable .hotel-detail-meta {
        color: var(--table-muted);
    }

    #hotelsTable .hotel-main-image {
        width: 52px;
        height: 52px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid var(--table-border);
    }

    #hotelsTable .calendar-link {
        color: var(--table-link);
        text-decoration: none;
        font-weight: 500;
    }

    #hotelsTable .calendar-link:hover {
        text-decoration: underline;
    }

    .th-tooltip {
        cursor: help;
    }

    /* Global tooltip (same style as New Enquiries page) */
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

    /* Actions column: same soft-badge design as New Enquiries */
    #hotelsTable td.col-actions {
        white-space: nowrap;
        overflow: visible;
    }

    #hotelsTable .actions-icons-wrap {
        display: grid;
        grid-template-columns: repeat(3, auto);
        row-gap: 0.5rem;
        column-gap: 0.5rem;
        align-items: center;
        justify-content: start;
        max-width: 100%;
    }

    #hotelsTable .action-icon-badge {
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
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        flex-shrink: 0;
        text-decoration: none;
        color: inherit;
    }

    #hotelsTable .action-icon-badge:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        color: inherit;
    }

    #hotelsTable .action-icon-badge i {
        font-size: 1rem;
        color: var(--action-color, #475569);
    }

    #hotelsTable .action-icon-badge:hover i {
        color: var(--action-color, #0f766e);
    }

    /* Loading spinner animation for delete button */
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
                        <h5 class="card-title mb-0">Hotels & Accommodations</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        <!-- Add New Hotel Button -->
                        @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 20)
                        <a href="{{ route('hotels.create') }}"
                            class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                            <i class="fas fa-plus"></i> Add New Hotel
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

                <table class="datatables-basic table table-bordered" id="hotelsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Serial Number">No</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Hotel Name, Phone and Email">Hotel Details</th>
                            {{-- <th>Master Dmc</th> --}}
                            @php
                                $roleId = auth()->user()->role_id;
                            @endphp
                            @php
                                $hideRoles = [11, 20, 35, 130, 132, 133, 135, 136, 137, 138, 77, 84, 139, 140];
                            @endphp

                            @if($roleId == 10 || $roleId == 19)
                                <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Destination Management Company">DMC</th>
                            @elseif(!in_array($roleId, $hideRoles))
                                <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Master DMC">Master Dmc</th>
                                <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Destination Management Company">DMC</th>
                            @endif
                            {{-- <th>Master Dmc</th>
                            <th>Dmc</th> --}}
                            
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Hotel Main Image">Image</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Availability Calendar">Calendar</th>
                            @if(auth()->user()->role_id == 1 || auth()->user()->userId == 2 || auth()->user()->role_id == 23  || auth()->user()->role_id == 35 || auth()->user()->role_id == 47 || auth()->user()->role_id == 77 || auth()->user()->role_id ==82 || auth()->user()->role_id == 84 || auth()->user()->role_id == 139 || auth()->user()->role_id == 140 || hasPermission('edit hotel') || hasPermission('delete hotel'))
                            @if(hasPermission('edit hotel') || hasPermission('delete hotel'))
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Available Actions">Action</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Created Date and Time">Created At</th>
                            @endif
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hotels as $key => $hotel)
                        <tr>
                            {{-- {{ dd($hotel->dmc->name) }} --}}
                            <td class="align-top">{{ ++$key }}</td>
                            <td class="align-top">
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
                                </div>
                            </td>
                            {{-- <td>
                                @php
                                    $dmcUser = App\Models\User::where('userId', $hotel->dmc_id)->first();
                                @endphp
                                {{ $hotel->dmc && $hotel->dmc->masterDmc ? $hotel->dmc->masterDmc->company_name : 'N/A' }}</td>
                            </td> --}}

                            @php
                                $roleId = auth()->user()->role_id;
                            @endphp
                            @php
                                $hideRoles = [11, 20, 35, 130, 132, 133, 135, 136, 137, 138, 77, 84, 139, 140];
                            @endphp

                            @if($roleId == 10 || $roleId == 19) {{-- Master DMC or Virtual Master DMC --}}
                                @php
                                    $dmcIds = $hotel->getSelectedDmcIds(); // Get array of DMC IDs
                                    $dmcUsers = App\Models\User::whereIn('userId', $dmcIds)->get();
                                @endphp
                                <td class="align-top">
                                    @if($dmcUsers->count() > 0)
                                        {{ $dmcUsers->first()->company_name }}
                                        @if($dmcUsers->count() > 1)
                                            <br><a href="javascript:void(0)" 
                                                   class="text-primary" 
                                                   onclick="showDmcModal('{{ $hotel->hotel_unique_id }}', 'dmc', {{ $dmcUsers->toJson() }})">
                                                <small>+{{ $dmcUsers->count() - 1 }} More</small>
                                            </a>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                            @elseif(!in_array($roleId, $hideRoles)) {{-- Not DMC or Virtual DMC --}}
                                @php
                                    $dmcIds = $hotel->getSelectedDmcIds(); // Get array of DMC IDs
                                    $dmcUsers = App\Models\User::whereIn('userId', $dmcIds)->get();
                                    $masterDmcIds = $dmcUsers->pluck('master_dmc_id')->filter()->unique();
                                    $masterDmcUsers = App\Models\User::whereIn('userId', $masterDmcIds)->get();
                                @endphp
                                <td class="align-top">
                                    @if($masterDmcUsers->count() > 0)
                                        <span class="text-primary">{{ $masterDmcUsers->first()->company_name }}</span>
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
                                <td>
                                    @if($dmcUsers->count() > 0)
                                        <span class="text-primary">{{ $dmcUsers->first()->company_name }}</span>
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

                            {{-- <td>
                                @php
                                    $dmcUser = App\Models\User::where('userId', $hotel->dmc_id)->first();
                                    $masterdmcUser = App\Models\User::where('userId', $dmcUser->master_dmc_id)->first();
                                @endphp
                                {{ $masterdmcUser ? $masterdmcUser->company_name : 'N/A' }}
                                
                            </td>
                            <td>{{ $dmcUser ? $dmcUser->company_name : 'N/A' }}</td> --}}
                           
                            <td class="align-top">
                                <img src="{{ $hotel->main_image }}" alt="Hotel Image" class="hotel-main-image">
                            </td>
                            <td class="align-top">
                                <a href="{{ route('hotels.viewcalendar', $hotel->hotel_unique_id) }}" target="_blank" class="calendar-link">
                                    <i class="ri-calendar-line me-1"></i>View Calendar
                                </a>
                            </td>
                            @if(auth()->user()->role_id == 1 || auth()->user()->userId == 2 || auth()->user()->role_id == 23  || auth()->user()->role_id == 35 || auth()->user()->role_id == 47 || auth()->user()->role_id == 77 || auth()->user()->role_id ==82 || auth()->user()->role_id == 84 || in_array(auth()->user()->role_id, [130, 132, 133, 135, 136, 137, 138, 139, 140]) || hasPermission('edit hotel') || hasPermission('delete hotel'))
                                @if($hotel->status == 1)
                                    <td class="align-top col-actions">
                                        <div class="actions-icons-wrap">
                                            @if(hasPermission('edit hotel'))
                                            <a href="{{ route('hotels.edit', $hotel->hotel_unique_id) }}"
                                               class="action-icon-badge"
                                               style="--action-color: #047857;"
                                               data-tooltip="Edit Hotel">
                                                <i class="ri-pencil-line"></i>
                                            </a>
                                            @endif
                                            @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                                            <button type="button"
                                                    class="action-icon-badge"
                                                    style="--action-color: #dc2626;"
                                                    data-tooltip="Delete Hotel"
                                                    onclick="deleteHotel('{{ route('hotels.destroy', $hotel->hotel_unique_id) }}', {{ json_encode($hotel->name) }})"
                                                    id="delete-btn-{{ $hotel->hotel_unique_id }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                @else
                                    <!-- @if(Auth::user()->role_id == 11)
                                        <td>
                                            @if($hotel->status == 2)
                                                <span>Pending approval from the A.M</span>
                                            @elseif($hotel->status == 4)
                                                <span>Approved by the A.M, awaiting S.M approval</span>
                                            @elseif($hotel->status == 5)
                                                <span>Approved by the S.M, awaiting Admin approval</span>
                                            @elseif($hotel->status == 3)
                                                <span>Declined</span>
                                            @endif
                                        </td>
                                    @endif -->
                                    <!-- @if(Auth::user()->role_id == 4)
                                        <td>
                                            @if($hotel->status == 4)
                                                <span>Approved by the A.M, awaiting S.M approval</span>
                                            @elseif($hotel->status == 5)
                                                <span>Approved by the S.M, awaiting Admin approval</span>
                                            @elseif($hotel->status == 3)
                                                <span>Declined</span>
                                            @endif
                                        </td>
                                    @endif -->
                                    <td class="align-top">
                                        @if($hotel->status == 5)
                                            <span>Your Hotel, awaiting for Admin approval</span>
                                        @elseif($hotel->status == 3)
                                            <span>Declined</span>
                                        @endif
                                    </td>
                                @endif
                            @endif
                            <td class="align-top">
                                <div class="d-flex flex-column">
                                    <span>{{ $hotel->created_at->format('D,  M d, Y') }}</span>
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

    <!-- Hotel Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <form id="deleteForm" action="" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- DMC Modal -->
    <div class="modal fade" id="dmcModal" tabindex="-1" role="dialog" aria-labelledby="dmcModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dmcModalLabel">DMC Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="dmcList"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        function initHeaderTooltips() {
            if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;
            document.querySelectorAll('#hotelsTable thead .th-tooltip[data-bs-toggle="tooltip"]').forEach(function(el) {
                const existing = bootstrap.Tooltip.getInstance(el);
                if (existing) existing.dispose();
                new bootstrap.Tooltip(el, {
                    container: 'body',
                    trigger: 'hover focus'
                });
            });
        }

        // Initialize DataTable with export buttons
        const hotelTable = $('.datatables-basic').DataTable({
            responsive: true,
            buttons: [
                'copy',
                'csv',
                'excel',
                'pdf',
                'print' // Enable copy, CSV, Excel, PDF, and Print buttons
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            },
            lengthMenu: [10, 25, 50, 100], // Customize number of entries per page
        });

        initHeaderTooltips();
        hotelTable.on('draw', function() {
            initHeaderTooltips();
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
 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script>
    function initHotelHeaderTooltips() {
        if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;
        document.querySelectorAll('#hotelsTable thead .th-tooltip[data-bs-toggle="tooltip"]').forEach(function(el) {
            const existing = bootstrap.Tooltip.getInstance(el);
            if (existing) existing.dispose();
            new bootstrap.Tooltip(el, {
                container: 'body',
                trigger: 'hover focus'
            });
        });
    }

    $(document).ready(function() {
        initHotelHeaderTooltips();
        $('#hotelsTable').on('draw.dt', function() {
            initHotelHeaderTooltips();
        });

        // Shared body-level tooltip for action icons (same behaviour as New Enquiries page)
        var $globalTooltip = $('#service-icon-global-tooltip');
        if (!$globalTooltip.length) {
            $globalTooltip = $('<div id="service-icon-global-tooltip" aria-hidden="true"></div>').appendTo('body');
        } else {
            $globalTooltip.appendTo('body');
        }

        // Tooltips for action icon badges in Hotels table
        $(document).on('mouseenter', '#hotelsTable .action-icon-badge', function() {
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

        $(document).on('mouseleave', '#hotelsTable .action-icon-badge', function() {
            $globalTooltip.hide();
        });
    });

    // Hotel deletion function with SweetAlert (same style as cancelTour in new-enquiries)
    window.deleteHotel = function(deleteUrl, hotelName) {
        // Show SweetAlert confirmation dialog
        Swal.fire({
            title: 'Delete Hotel?',
            text: `Are you sure you want to delete "${hotelName}"? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, keep it'
        }).then((result) => {
            if (result.isConfirmed) {
                // Find the button that triggered this
                const button = document.querySelector(`[onclick*="${deleteUrl}"]`);
                const originalContent = button ? button.innerHTML : '';
                
                // Show loading state
                if (button) {
                    button.innerHTML = '<i class="ri-loader-4-line spin"></i> Deleting...';
                    button.disabled = true;
                }
                
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = deleteUrl;
                
                // Add CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken.getAttribute('content');
                    form.appendChild(csrfInput);
                }
                
                // Add method spoofing for DELETE
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
                
                // Append form to body and submit
                document.body.appendChild(form);
                form.submit();
            }
        });
    };

    // Set form action for delete (kept for backward compatibility if modal is used elsewhere)
    function setDeleteForm(url) {
        document.getElementById('deleteForm').action = url;
    }

    // Show DMC modal with details
    function showDmcModal(itemId, type, users) {
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
        var myModal = new bootstrap.Modal(document.getElementById('dmcModal'));
        myModal.show();
    }
    </script>

<!--AJAX Call to approve guide-->
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
            let hotelId = $(this).data("id");
            let action = $(this).data("action");
            var url = "{{ env('APP_URL') }}" + "/hotel/approve-or-decline/" + hotelId;
            $.ajax({
                url: url,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    action: action
                },
                success: function(response) {
                    if (response.success) {
                        let guideRow = $("#guide-" + hotelId);
                        guideRow.find(".approve-btn, .decline-btn").remove();
                        guideRow.find(".status-message").text(action === 'approve' ? "Guide Approved!" : "Guide Declined!").css("color", action === 'approve' ? "green" : "red").show();
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

<!-- Hotels Page Specific Dropdown Fix -->
<script>
$(document).ready(function() {
    // Ensure user profile dropdown works on hotels page
    setTimeout(function() {
        // Target only the user profile dropdown, not the export dropdown
        const dropdownToggle = $('.topbar-item .topbar-link.dropdown-toggle');
        const dropdownMenu = $('.topbar-item .dropdown-menu');
        
        if (dropdownToggle.length && dropdownMenu.length) {
            // Remove any existing event handlers to avoid conflicts
            dropdownToggle.off('click.profile-dropdown-fix');
            
            // Add click event handler
            dropdownToggle.on('click.profile-dropdown-fix', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Close any open export dropdowns first
                $('#exportDropdown').attr('aria-expanded', 'false');
                $('#exportDropdown').next('.dropdown-menu').removeClass('show');
                
                // Toggle user profile dropdown
                if (dropdownMenu.hasClass('show')) {
                    dropdownMenu.removeClass('show');
                    dropdownToggle.attr('aria-expanded', 'false');
                } else {
                    dropdownMenu.addClass('show');
                    dropdownToggle.attr('aria-expanded', 'true');
                }
            });
            
            // Close user profile dropdown when clicking outside
            $(document).on('click.profile-dropdown-fix', function(e) {
                if (!dropdownToggle.is(e.target) && dropdownToggle.has(e.target).length === 0 && 
                    !dropdownMenu.is(e.target) && dropdownMenu.has(e.target).length === 0) {
                    dropdownMenu.removeClass('show');
                    dropdownToggle.attr('aria-expanded', 'false');
                }
            });
            
            console.log('Hotels page user profile dropdown fix applied');
        } else {
            console.log('User profile dropdown elements not found on hotels page');
        }
    }, 1000); // Wait 1 second for all scripts to load
});
</script>

@endsection
