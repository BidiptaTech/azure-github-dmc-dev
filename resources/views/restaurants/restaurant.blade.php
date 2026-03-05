@extends('layouts.layout')
@section('title', 'Restaurant')
@extends('layouts.datatablecss')

@section('css')
<!-- Add SweetAlert2 CSS (for consistent delete confirmation styling like Attractions) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<style>
    :root {
        --table-border: #e2e8f0;
        --table-head-bg: #f8fafc;
        --table-head-text: #334155;
        --table-body-text: #0f172a;
        --table-muted: #64748b;
        /* Primary brand blue used for accents (calendar + status) */
        --table-link: #5c61e6;
    }

    /* Make the table compact and avoid horizontal scrolling */
    #restaurantsTable {
        table-layout: fixed;
        width: 100%;
        font-size: 0.8rem;
    }

    #restaurantsTable thead th {
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

    #restaurantsTable tbody td {
        color: var(--table-body-text);
        border-color: var(--table-border);
        vertical-align: top;
        padding: 0.35rem 0.4rem;
        font-size: 0.8rem;
        line-height: 1.4;
        white-space: normal;
        word-wrap: break-word;
    }

    /* Slightly smaller text for meals rows, with clear vertical spacing and end-aligned status */
    #restaurantsTable td.meals-cell div {
        font-size: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.15rem;
    }

    #restaurantsTable td.meals-cell div:last-child {
        margin-bottom: 0;
    }

    /* Column width hints to keep all columns within screen */
    #restaurantsTable thead th:nth-child(1),
    #restaurantsTable tbody td:nth-child(1) { /* No */
        width: 3%;
    }

    #restaurantsTable thead th:nth-child(2),
    #restaurantsTable tbody td:nth-child(2) { /* Restaurant Name */
        width: 9%;
    }

    /* DMC / Master DMC columns will share a bit more width for readability */
    #restaurantsTable thead th:nth-child(3),
    #restaurantsTable tbody td:nth-child(3) {
        width: 12%;
    }

    #restaurantsTable thead th:nth-child(4),
    #restaurantsTable tbody td:nth-child(4) {
        width: 12%;
    }

    /* Cuisine */
    #restaurantsTable thead th:nth-child(5),
    #restaurantsTable tbody td:nth-child(5) {
        width: 9%;
    }

    /* Meals */
    #restaurantsTable thead th:nth-child(6),
    #restaurantsTable tbody td:nth-child(6) {
        width: 18%;
    }

    /* Owned By */
    #restaurantsTable thead th:nth-child(7),
    #restaurantsTable tbody td:nth-child(7) {
        width: 9%;
    }

    /* Status column */
    #restaurantsTable thead th:nth-child(8),
    #restaurantsTable tbody td:nth-child(8) {
        width: 7%;
    }

    /* Status / Action / Created At share remaining width */

    /* Created (last) column: keep compact */
    #restaurantsTable thead th:last-child,
    #restaurantsTable tbody td:last-child {
        width: 7%;
    }

    /* Restaurant name styling */
    #restaurantsTable .restaurant-detail-title {
        font-weight: 600;
        font-size: 0.8rem;
        color: var(--table-body-text);
    }

    /* Calendar link styling - same as attractions/hotels page */
    #restaurantsTable .calendar-link {
        color: var(--table-link);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    #restaurantsTable .calendar-link:hover {
        text-decoration: underline;
        color: var(--table-link);
    }

    #restaurantsTable .calendar-link i {
        font-size: 0.875rem;
    }

    /* Status badge styling - standardized colors (Active / Inactive) */
    #restaurantsTable .badge {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.35rem 0.65rem;
        border-radius: 0.375rem;
    }

    #restaurantsTable .badge.bg-success {
        background-color: var(--table-link) !important;
        color: #ffffff;
    }

    #restaurantsTable .badge.bg-danger {
        background-color: #e5e7eb !important;
        color: #111827;
    }

    /* Subtle muted text */
    #restaurantsTable .text-muted {
        color: var(--table-muted);
        font-size: 0.875rem;
    }

    /* Created date styling */
    #restaurantsTable .d-flex.flex-column span {
        font-size: 0.875rem;
    }

    #restaurantsTable .d-flex.flex-column small {
        font-size: 0.75rem;
    }

    .th-tooltip {
        cursor: help;
    }

    /* Global tooltip (same style as Hotels/Attractions pages) */
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

    /* Actions column: same soft-badge design as Attractions page, allow wrapping */
    #restaurantsTable td.col-actions {
        white-space: normal;
        overflow: visible;
    }

    /* Give space to the Action column but keep it compact */
    #restaurantsTable th.col-actions-header,
    #restaurantsTable td.col-actions {
        width: 11%;
    }

    #restaurantsTable .actions-icons-wrap {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, auto));
        row-gap: 0.35rem;
        column-gap: 0.35rem;
        align-items: center;
        justify-content: start;
        max-width: 100%;
    }

    #restaurantsTable .action-icon-badge {
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

    #restaurantsTable .action-icon-badge:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        color: inherit;
    }

    #restaurantsTable .action-icon-badge i {
        font-size: 1rem;
        color: var(--action-color, #475569);
    }

    #restaurantsTable .action-icon-badge:hover i {
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

    /* DMC modal refinements */
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
        background: #f3f4f6;
        border-color: #d1d5db;
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
                        <h5 class="card-title mb-0">Restaurant & Dining</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        <!-- Add New Restaurant Button -->
                        @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 20)
                        @if(hasPermission('create restaurant'))
                            <a href="{{ route('restaurant.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                                <i class="fas fa-plus"></i> Add New Restaurant
                            </a>
                        @endif
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
                    <table class="datatables-basic table table-bordered" id="restaurantsTable">
                        <thead>
                            <tr>
                                <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Serial Number">No</th>
                                <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Restaurant Name">Name</th>
                                @php
                                    $roleId = auth()->user()->role_id;
                                @endphp
                                @php
                                    $hideRoles = [11, 20, 35, 130, 132, 133, 135, 136, 137, 138, 78, 120, 139, 140];
                                @endphp

                                @if($roleId == 10 || $roleId == 19)
                                    <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Destination Management Company">DMC</th>
                                @elseif(!in_array($roleId, $hideRoles))
                                    <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Master DMC">Master</th>
                                    <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Destination Management Company">DMC</th>
                                @endif
                                <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Cuisine Type">Cuisine</th>
                                <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Meals Availability">Meals</th>
                                <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Property / Ownership">Owned By</th>
                                <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Status">Status</th>
                                @if(auth()->user()->role_id == 1 || auth()->user()->userId == 2 || auth()->user()->role_id == 48  || auth()->user()->role_id == 23 || auth()->user()->role_id == 78 || auth()->user()->role_id ==120 || auth()->user()->role_id == 118 || hasPermission('edit restaurant') || hasPermission('delete restaurant'))
                                    <th class="th-tooltip col-actions-header" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Available Actions">Actions</th>
                                @endif
                                <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Created Date and Time">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($restaurants as $key => $restaurant)
                                <tr>
                                    <td class="align-top">{{ ++$key }}</td>
                                    <td class="align-top">
                                        <div class="d-flex flex-column">
                                            <span class="restaurant-detail-title">{{ $restaurant->name }}</span>
                                        </div>
                                    </td>
                                    @php
                                        $roleId = auth()->user()->role_id;
                                    @endphp
                                    @php
                                        $hideRoles = [11, 20, 35, 130, 132, 133, 135, 136, 137, 138, 78, 120, 139, 140];
                                    @endphp

                                    @if($roleId == 10 || $roleId == 19)
                                        <td class="align-top">
                                            @if($restaurant->dmcUsers->count() > 0)
                                                <span class="text-primary">{{ $restaurant->dmcUsers->first()->company_name }}</span>
                                                @if($restaurant->dmcUsers->count() > 1)
                                                    <a href="javascript:void(0)" 
                                                       class="btn btn-primary btn-sm text-white ms-1" 
                                                       onclick="showDmcModal('{{ $restaurant->restaurant_id }}', 'dmc', {{ $restaurant->dmcUsers->toJson() }})">
                                                        <small>+{{ $restaurant->dmcUsers->count() - 1 }} More</small>
                                                    </a>
                                                @endif
                                            @else
                                                <span class="text-muted">No DMC assigned</span>
                                            @endif
                                        </td>
                                    @elseif(!in_array($roleId, $hideRoles))
                                        <td class="align-top">
                                            @if($restaurant->masterDmcUsers->count() > 0)
                                                <span class="text-primary">{{ $restaurant->masterDmcUsers->first()->company_name }}</span>
                                                @if($restaurant->masterDmcUsers->count() > 1)
                                                    <a href="javascript:void(0)" 
                                                       class="btn btn-primary btn-sm text-white ms-1" 
                                                       onclick="showDmcModal('{{ $restaurant->restaurant_id }}', 'master_dmc', {{ $restaurant->masterDmcUsers->toJson() }})">
                                                        <small>+{{ $restaurant->masterDmcUsers->count() - 1 }} More</small>
                                                    </a>
                                                @endif
                                            @else
                                                <span class="text-muted">No DMC assigned</span>
                                            @endif
                                        </td>
                                        <td class="align-top">
                                            @if($restaurant->dmcUsers->count() > 0)
                                                <span class="text-primary">{{ $restaurant->dmcUsers->first()->company_name }}</span>
                                                @if($restaurant->dmcUsers->count() > 1)
                                                    <a href="javascript:void(0)" 
                                                       class="btn btn-primary btn-sm text-white ms-1" 
                                                       onclick="showDmcModal('{{ $restaurant->restaurant_id }}', 'dmc', {{ $restaurant->dmcUsers->toJson() }})">
                                                        <small>+{{ $restaurant->dmcUsers->count() - 1 }} More</small>
                                                    </a>
                                                @endif
                                            @else
                                                <span class="text-muted">No DMC assigned</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="align-top">
                                        {{ $restaurant->cuisine }}
                                    </td>
                                    <td class="align-top meals-cell">
                                        <div>
                                            Breakfast: 
                                            @if($restaurant->breakfast_available == 1)
                                                <span class="badge bg-success">Available</span>
                                            @else
                                                <span class="badge bg-danger">Not Available</span>
                                            @endif
                                        </div>
                                        <div>
                                            Lunch: 
                                            @if($restaurant->lunch_available == 1)
                                                <span class="badge bg-success">Available</span>
                                            @else
                                                <span class="badge bg-danger">Not Available</span>
                                            @endif
                                        </div>
                                        <div>
                                            Dinner: 
                                            @if($restaurant->dinner_available == 1)
                                                <span class="badge bg-success">Available</span>
                                            @else
                                                <span class="badge bg-danger">Not Available</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="align-top">
                                        <p>{{$restaurant->property}}</p>
                                    </td>
                                    <td class="align-top">
                                        @if($restaurant->is_active == 1)
                                            <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        @if(hasPermission('edit restaurant') || hasPermission('delete restaurant'))
                                            @if($restaurant->status == 1)
                                                <td class="align-top col-actions">
                                                    <div class="actions-icons-wrap">
                                                        <!-- View Calendar Button -->
                                                        <a href="{{ route('restaurant.calendar', Crypt::encrypt($restaurant->restaurant_id)) }}"
                                                           target="_blank"
                                                           class="action-icon-badge"
                                                           style="--action-color: #2563eb;"
                                                           data-tooltip="View Restaurant Calendar">
                                                            <i class="ri-calendar-line"></i>
                                                        </a>

                                                        <!-- Edit Button -->
                                                        @if(hasPermission('edit restaurant'))
                                                        <a href="{{ route('restaurant.edit', Crypt::encrypt($restaurant->restaurant_id)) }}"
                                                           class="action-icon-badge"
                                                           style="--action-color: #047857;"
                                                           data-tooltip="Edit Restaurant">
                                                            <i class="ri-pencil-line"></i>
                                                        </a>
                                                        @endif

                                                        <!-- Delete Button -->
                                                        @if( Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                                                        <button type="button"
                                                                class="action-icon-badge"
                                                                style="--action-color: #dc2626;"
                                                                data-tooltip="Delete Restaurant"
                                                                onclick="deleteRestaurant('{{ route('restaurant.destroy', Crypt::encrypt($restaurant->restaurant_id)) }}', {{ json_encode($restaurant->name) }})"
                                                                id="delete-btn-{{ $restaurant->restaurant_id }}">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                        @endif
                                                    </div>
                                                </td>

                                                @else

                                                {{-- @if(Auth::user()->role_id == 11)
                                                <td>
                                                    @if($restaurant->status == 2)
                                                        <span>Pending approval from the A.M</span>
                                                    @elseif($restaurant->status == 4)
                                                        <span>Awaiting S.M approval</span>
                                                    @elseif($restaurant->status == 5)
                                                        <span>Awaiting Admin approval</span>
                                                    @elseif($restaurant->status == 3)
                                                        <span>Declined</span>
                                                    @endif
                                                </td>
                                                @endif  
                                                @if(Auth::user()->role_id == 4)
                                                    <td>
                                                        @if($restaurant->status == 4)
                                                            <span>Awaiting S.M approval</span>
                                                        @elseif($restaurant->status == 5)
                                                            <span>Awaiting Admin approval</span>
                                                        @elseif($restaurant->status == 3)
                                                            <span>Declined</span>
                                                        @endif
                                                    </td>
                                                @endif

                                                @if(Auth::user()->role_id == 3)
                                                    <td>
                                                        @if($restaurant->status == 5)
                                                            <span>Awaiting Admin approval</span>
                                                        @elseif($restaurant->status == 3)
                                                            <span>Declined</span>
                                                        @endif
                                                    </td>
                                                @endif --}}

                                                <td class="align-top">
                                                    @if($restaurant->status == 5)
                                                        <span>Your Restaurant, awaiting for Admin approval</span>
                                                    @elseif($restaurant->status == 3)
                                                        <span>Declined</span>
                                                    @endif
                                                </td>
                                            @endif
                                        @endif
                                    <td class="align-top">
                                        <div class="d-flex flex-column">
                                            <span>{{ $restaurant->created_at->format('D,  M d, Y') }}</span>
                                            <small class="text-muted">{{ $restaurant->created_at->format('h:i A') }}</small>
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
        <!-- Restaurant Delete Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" Category="dialog" 
             aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog" Category="document">
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
        <!-- End Modal -->

<!-- DMC Companies Modal (standard, refined UI) -->
<div class="modal fade" id="dmcCompaniesModal" tabindex="-1" role="dialog" aria-labelledby="dmcCompaniesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex flex-column">
                    <h5 class="modal-title fw-semibold mb-1" id="dmcCompaniesModalLabel">DMC Companies</h5>
                    <small class="text-muted" id="dmcCompaniesModalSubtitle">Linked companies for this item</small>
                </div>
            </div>
            <div class="modal-body pt-2">
                <div id="dmcCompaniesModalBody" class="dmc-modal-body">
                    <!-- Company cards will be injected here -->
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 d-flex justify-content-between align-items-center">
                <small class="text-muted mb-0" id="dmcCompaniesCountText"></small>
                <button type="button" class="btn btn-light border" data-dismiss="modal" onclick="closeDmcModal()">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')

<!-- Add SweetAlert2 JS (for delete confirmation like Attractions page) -->
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
                'print' // Enable copy, CSV, Excel, PDF, and Print buttons
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            },
            lengthMenu: [10, 25, 50, 100], // Customize number of entries per page
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
    // Body-level tooltip for restaurant headers (use jQuery tooltip to avoid version issues)
    function initRestaurantHeaderTooltips() {
        if (typeof $ === 'undefined' || typeof $.fn.tooltip !== 'function') return;

        $('#restaurantsTable thead .th-tooltip[data-bs-toggle="tooltip"]')
            .tooltip('dispose')
            .tooltip({
                container: 'body',
                trigger: 'hover focus'
            });
    }

    function showDmcModal(restaurantId, type, companies) {
        // Set modal title and subtitle
        const modalTitle = document.getElementById('dmcCompaniesModalLabel');
        const modalSubtitle = document.getElementById('dmcCompaniesModalSubtitle');
        const isMaster = (type === 'master_dmc');

        modalTitle.textContent = isMaster ? 'Master DMC Companies' : 'DMC Companies';
        modalSubtitle.textContent = isMaster
            ? 'Master DMCs linked to this restaurant'
            : 'DMC partners linked to this restaurant';
        
        // Clear and populate modal body
        const modalBody = document.getElementById('dmcCompaniesModalBody');
        const countTextEl = document.getElementById('dmcCompaniesCountText');
        modalBody.innerHTML = '';
        
        if (companies && companies.length > 0) {
            companies.forEach(function(company) {
                const companyDiv = document.createElement('div');
                companyDiv.className = 'dmc-company-card mb-2';
                companyDiv.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold text-body mb-1">
                                ${company.company_name || 'N/A'}
                            </div>
                            ${company.name ? `<div class="text-muted small mb-1">${company.name}</div>` : ''}
                            ${company.email ? `<div class="text-muted small"><i class="ri-mail-line me-1"></i>${company.email}</div>` : ''}
                            ${company.phone ? `<div class="text-muted small"><i class="ri-phone-line me-1"></i>${company.phone}</div>` : ''}
                        </div>
                        <span class="badge bg-light text-muted text-uppercase small">
                            ${isMaster ? 'MASTER DMC' : 'DMC'}
                        </span>
                    </div>
                `;
                modalBody.appendChild(companyDiv);
            });

            if (countTextEl) {
                countTextEl.textContent = `${companies.length} compan${companies.length === 1 ? 'y' : 'ies'} linked`;
            }
        } else {
            modalBody.innerHTML = '<p class="text-muted mb-0">No companies found for this restaurant.</p>';
            if (countTextEl) {
                countTextEl.textContent = '';
            }
        }
        
        // Show modal using Bootstrap 4 syntax
        $('#dmcCompaniesModal').modal('show');
    }
    
    // Add explicit close functionality
    function closeDmcModal() {
        $('#dmcCompaniesModal').modal('hide');
    }
    
    // Ensure modal can be closed with escape key and click outside, and init action tooltips
    $(document).ready(function() {
        initRestaurantHeaderTooltips();
        $('#restaurantsTable').on('draw.dt', function() {
            initRestaurantHeaderTooltips();
        });

        $('#dmcCompaniesModal').on('hidden.bs.modal', function () {
            // Clean up when modal is closed
            $('#dmcCompaniesModalBody').html('');
        });

        // Global tooltip element for action icons (same pattern as Attractions)
        var $globalTooltip = $('#service-icon-global-tooltip');
        if (!$globalTooltip.length) {
            $globalTooltip = $('<div id="service-icon-global-tooltip" aria-hidden="true"></div>').appendTo('body');
        } else {
            $globalTooltip.appendTo('body');
        }

        // Show tooltip on hover over action icon badges in Restaurants table
        $('#restaurantsTable').on('mouseenter', '.action-icon-badge', function() {
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

        $('#restaurantsTable').on('mouseleave', '.action-icon-badge', function() {
            $globalTooltip.hide();
        });
    });

    // Restaurant deletion function with SweetAlert (mirrors Attractions delete behaviour)
    window.deleteRestaurant = function(deleteUrl, restaurantName) {
        Swal.fire({
            title: 'Delete Restaurant?',
            text: `Are you sure you want to delete "${restaurantName}"? This action cannot be undone.`,
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
