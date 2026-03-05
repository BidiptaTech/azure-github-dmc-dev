@extends('layouts.layout')
@section('title', 'Attractions & Experiences')
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
        /* Primary brand blue used for accents (calendar + status) */
        --table-link: #5c61e6;
    }

    #attractionsTable thead th {
        background: var(--table-head-bg);
        color: var(--table-head-text);
        font-weight: 600;
        font-size: 0.8125rem;
        padding: 0.5rem 0.75rem;
        border-color: var(--table-border);
        white-space: nowrap;
        line-height: 1.4;
    }

    #attractionsTable tbody td {
        color: var(--table-body-text);
        border-color: var(--table-border);
        vertical-align: top;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }

    /* Attraction name styling */
    #attractionsTable .hotel-detail-title {
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--table-body-text);
    }

    /* Calendar link styling - same as hotels page */
    #attractionsTable .calendar-link {
        color: var(--table-link);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    #attractionsTable .calendar-link:hover {
        text-decoration: underline;
        color: var(--table-link);
    }

    #attractionsTable .calendar-link i {
        font-size: 0.875rem;
    }

    /* Status badge styling - standardized colors */
    #attractionsTable .badge {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.35rem 0.65rem;
        border-radius: 0.375rem;
    }

    #attractionsTable .badge.bg-success {
        background-color: var(--table-link) !important;
        color: #ffffff;
    }

    #attractionsTable .badge.bg-danger {
        background-color: #e5e7eb !important;
        color: #111827;
    }

    /* Location and other text styling */
    #attractionsTable tbody td {
        line-height: 1.5;
    }

    /* Subtle muted text */
    #attractionsTable .text-muted {
        color: var(--table-muted);
        font-size: 0.875rem;
    }

    /* Created date styling */
    #attractionsTable .d-flex.flex-column span {
        font-size: 0.875rem;
    }

    #attractionsTable .d-flex.flex-column small {
        font-size: 0.75rem;
    }

    .th-tooltip {
        cursor: help;
    }

    /* Global tooltip (same style as Hotels/New Enquiries pages) */
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

    /* Actions column: same soft-badge design as Hotels page */
    #attractionsTable td.col-actions {
        white-space: nowrap;
        overflow: visible;
    }

    #attractionsTable .actions-icons-wrap {
        display: grid;
        grid-template-columns: repeat(3, auto);
        row-gap: 0.5rem;
        column-gap: 0.5rem;
        align-items: center;
        justify-content: start;
        max-width: 100%;
    }

    #attractionsTable .action-icon-badge {
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

    #attractionsTable .action-icon-badge:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        color: inherit;
    }

    #attractionsTable .action-icon-badge i {
        font-size: 1rem;
        color: var(--action-color, #475569);
    }

    #attractionsTable .action-icon-badge:hover i {
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
                        <h5 class="card-title mb-0">Attractions & Experienc</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        <!-- Add New Category Button -->
                        @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 20)
                        @if(hasPermission('create attraction'))
                        <a href="{{ route('attraction.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                            <i class="fas fa-plus"></i> Add New Attraction
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
                <hr>
                <table class="datatables-basic table table-bordered" id="attractionsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Serial Number">No</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Attraction Name">Name</th>
                            @php
                                $roleId = auth()->user()->role_id;
                            @endphp
                            @php
                                $hideRoles = [11, 20, 35, 130, 132, 133, 135, 136, 137, 138, 74, 93, 90, 139, 140];
                            @endphp

                            @if(in_array($roleId, [10, 19])) {{-- Master DMC or Virtual Master DMC --}}
                                <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Destination Management Company">DMC</th>
                            @elseif(!in_array($roleId, $hideRoles)) {{-- Not DMC or Virtual DMC --}}
                                <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Master DMC">Master Dmc</th>
                                <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Destination Management Company">DMC</th>
                            @endif

                            {{-- <th>Adult Price</th>
                            <th>Child Price</th> --}}
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Location">location</th>
                            
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Availability Calendar">Calendar</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Status">Status</th>
                            @if(auth()->user()->role_id == 1 || auth()->user()->userId == 2 || auth()->user()->role_id == 23  || auth()->user()->role_id == 35 || auth()->user()->role_id == 44 || auth()->user()->role_id == 74 || auth()->user()->role_id ==91 || auth()->user()->role_id == 93 || auth()->user()->role_id == 130 || auth()->user()->role_id == 132 || auth()->user()->role_id == 133 || auth()->user()->role_id == 135 || auth()->user()->role_id == 136 || auth()->user()->role_id == 137 || auth()->user()->role_id == 138 || hasPermission('edit attraction') || hasPermission('delete attraction'))
                                <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Available Actions">Action</th>
                            @endif
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Created Date and Time">Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attractions as $key => $attraction)
                            <tr>
                                <td class="align-top">{{ ++$key }}</td>
                                <td class="align-top">
                                    <div class="d-flex flex-column">
                                        <span class="hotel-detail-title">{{ $attraction->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                @php
                                    $roleId = auth()->user()->role_id;
                                @endphp
                                @php
                                    $hideRoles = [11, 20, 35, 130, 132, 133, 135, 136, 137, 138, 74, 93, 139, 140];
                                @endphp

                                @if(in_array($roleId, [10, 19])) {{-- Master DMC or Virtual Master DMC --}}
                                    @php
                                        $dmcIds = $attraction->getSelectedDmcIds(); // Get array of DMC IDs
                                        $dmcUsers = App\Models\User::whereIn('userId', $dmcIds)->get();
                                    @endphp
                                    <td class="align-top">
                                        @if($dmcUsers->count() > 0)
                                            {{ $dmcUsers->first()->company_name }}
                                            @if($dmcUsers->count() > 1)
                                                <br><a href="javascript:void(0)" 
                                                       class="btn btn-outline-secondary btn-sm"
                                                       onclick="showDmcModal('{{ $attraction->attraction_id }}', 'dmc', {{ $dmcUsers->toJson() }})">
                                                    <small>+{{ $dmcUsers->count() - 1 }} More</small>
                                                </a>
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                @elseif(!in_array($roleId, $hideRoles)) {{-- Not DMC or Virtual DMC --}}
                                    @php
                                        $dmcIds = $attraction->getSelectedDmcIds(); // Get array of DMC IDs
                                        $dmcUsers = App\Models\User::whereIn('userId', $dmcIds)->get();
                                        $masterDmcIds = $dmcUsers->pluck('master_dmc_id')->filter()->unique();
                                        $masterDmcUsers = App\Models\User::whereIn('userId', $masterDmcIds)->get();
                                    @endphp
                                    <td class="align-top">
                                        @if($masterDmcUsers->count() > 0)
                                            <span>{{ $masterDmcUsers->first()->company_name }}</span>
                                            @if($masterDmcUsers->count() > 1)
                                                <br><a href="javascript:void(0)" 
                                                       class="btn btn-primary btn-sm text-white" 
                                                       onclick="showDmcModal('{{ $attraction->attraction_id }}', 'master_dmc', {{ $masterDmcUsers->toJson() }})">
                                                    <small>+{{ $masterDmcUsers->count() - 1 }} More</small>
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-muted">No DMC assigned</span>
                                        @endif
                                    </td>
                                    <td class="align-top">
                                        @if($dmcUsers->count() > 0)
                                            <span>{{ $dmcUsers->first()->company_name }}</span>
                                            @if($dmcUsers->count() > 1)
                                                <br><a href="javascript:void(0)" 
                                                       class="btn btn-primary btn-sm text-white" 
                                                       onclick="showDmcModal('{{ $attraction->attraction_id }}', 'dmc', {{ $dmcUsers->toJson() }})">
                                                    <small>+{{ $dmcUsers->count() - 1 }} More</small>
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-muted">No DMC assigned</span>
                                        @endif
                                    </td>
                                @endif

                                {{-- <td>
                                    @if($attraction->adult_price)
                                        {{ $attraction->adult_price }}
                                    @else
                                        <span class="badge bg-danger">No details</span>
                                    @endif
                                </td>
                                <td class="align-top">
                                @if($attraction->child_price)
                                    {{$attraction->child_price}}
                                @else
                                    <span class="badge bg-danger">No details</span>
                                @endif
                                </td> --}}
                                <td class="align-top">
                                    <span>{{ $attraction->location ?: 'N/A' }}</span>
                                </td>
                                
                                <td class="align-top"> 
                                    <a href="{{ route('attraction.calendar', ['attraction_id' => Crypt::encrypt($attraction->attraction_id)]) }}" target="_blank" class="calendar-link">
                                        <i class="ri-calendar-line"></i>View Calendar
                                    </a>
                                </td>
                                <td class="align-top">
                                @if($attraction->is_active == 1)
                                    <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif  
                                </td>
                                @if(auth()->user()->role_id == 1 || auth()->user()->userId == 2 || auth()->user()->role_id == 23  || auth()->user()->role_id == 35 || auth()->user()->role_id == 44 || auth()->user()->role_id == 74 || auth()->user()->role_id ==91 || auth()->user()->role_id == 130 || auth()->user()->role_id == 132 || auth()->user()->role_id == 133 || auth()->user()->role_id == 135 || auth()->user()->role_id == 136 || auth()->user()->role_id == 137 || auth()->user()->role_id == 138 || auth()->user()->role_id == 93 || auth()->user()->role_id == 139 || auth()->user()->role_id == 140 || hasPermission('edit attraction') || hasPermission('delete attraction'))
                                @if($attraction->status == 1)
                                <td class="align-top col-actions">
                                    <div class="actions-icons-wrap">
                                        <!-- Edit Button -->
                                        @if(hasPermission('edit attraction'))
                                        <a href="{{ route('attraction.edit', Crypt::encrypt($attraction->attraction_id)) }}" 
                                           class="action-icon-badge"
                                           style="--action-color: #047857;"
                                           data-tooltip="Edit Attraction">
                                            <i class="ri-pencil-line"></i>
                                        </a>
                                        @endif

                                        <!-- Delete Button -->
                                        @if( Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                                        <button type="button"
                                                class="action-icon-badge"
                                                style="--action-color: #dc2626;"
                                                data-tooltip="Delete Attraction"
                                                onclick="deleteAttraction('{{ route('attraction.destroy', Crypt::encrypt($attraction->attraction_id)) }}', {{ json_encode($attraction->name) }})"
                                                id="delete-btn-{{ $attraction->attraction_id }}">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                                @else
                                    {{-- @if(Auth::user()->role_id == 11)
                                    <td>
                                        @if($attraction->status == 2)
                                            <span>Pending approval from the A.M</span>
                                        @elseif($attraction->status == 4)
                                            <span>Awaiting S.M approval</span>
                                        @elseif($attraction->status == 5)
                                            <span>Awaiting Admin approval</span>
                                        @elseif($attraction->status == 3)
                                            <span>Declined</span>
                                        @endif
                                    </td>
                                    @endif  
                                    @if(Auth::user()->role_id == 4)
                                        <td>
                                            @if($attraction->status == 4)
                                                <span>Awaiting S.M approval</span>
                                            @elseif($attraction->status == 5)
                                                <span>Awaiting Admin approval</span>
                                            @elseif($attraction->status == 3)
                                                <span>Declined</span>
                                            @endif
                                        </td>
                                    @endif

                                    @if(Auth::user()->role_id == 3)
                                        <td>
                                            @if($attraction->status == 5)
                                                <span>Awaiting Admin approval</span>
                                            @elseif($attraction->status == 3)
                                                <span>Declined</span>
                                            @endif
                                        </td>
                                    @endif --}}
                                    <td>
                                        @if($attraction->status == 5)
                                            <span>Your Attraction, awaiting for Admin approval</span>
                                        @elseif($attraction->status == 3)
                                            <span>Declined</span>
                                        @endif
                                    </td>
                                @endif
                            @endif
                            <td class="align-top">
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

<!-- DMC Companies Modal -->
<div class="modal fade" id="dmcCompaniesModal" tabindex="-1" role="dialog" aria-labelledby="dmcCompaniesModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dmcCompaniesModalLabel">DMC Companies</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeDmcModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="dmcCompaniesModalBody">
                <!-- Company names will be populated here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeDmcModal()">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Attraction Delete Modal -->
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
    // Body-level tooltip for attraction action icons and headers (same behaviour as Hotels page)
    function initAttractionHeaderTooltips() {
        if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;
        document.querySelectorAll('#attractionsTable thead .th-tooltip[data-bs-toggle="tooltip"]').forEach(function(el) {
            const existing = bootstrap.Tooltip.getInstance(el);
            if (existing) existing.dispose();
            new bootstrap.Tooltip(el, {
                container: 'body',
                trigger: 'hover focus'
            });
        });
    }

    $(document).ready(function() {
        initAttractionHeaderTooltips();
        $('#attractionsTable').on('draw.dt', function() {
            initAttractionHeaderTooltips();
        });

        var $globalTooltip = $('#service-icon-global-tooltip');
        if (!$globalTooltip.length) {
            $globalTooltip = $('<div id="service-icon-global-tooltip" aria-hidden="true"></div>').appendTo('body');
        } else {
            $globalTooltip.appendTo('body');
        }

        // Tooltips for action icon badges in Attractions table
        $(document).on('mouseenter', '#attractionsTable .action-icon-badge', function() {
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

        $(document).on('mouseleave', '#attractionsTable .action-icon-badge', function() {
            $globalTooltip.hide();
        });
    });

    // Attraction deletion function with SweetAlert (similar to Hotels page)
    window.deleteAttraction = function(deleteUrl, attractionName) {
        Swal.fire({
            title: 'Delete Attraction?',
            text: `Are you sure you want to delete "${attractionName}"? This action cannot be undone.`,
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

    // Existing modal-based delete helper (kept for backward compatibility if needed)
    function setDeleteForm(action) {
        document.getElementById('deleteForm').action = action;
    }
    
    function showDmcModal(attractionId, type, companies) {
        // Set modal title
        const modalTitle = document.getElementById('dmcCompaniesModalLabel');
        modalTitle.textContent = type === 'master_dmc' ? 'Master DMC Companies' : 'DMC Companies';
        
        // Clear and populate modal body
        const modalBody = document.getElementById('dmcCompaniesModalBody');
        modalBody.innerHTML = '';
        
        if (companies && companies.length > 0) {
            companies.forEach(function(company) {
                const companyDiv = document.createElement('div');
                companyDiv.className = 'mb-2 p-2 border-bottom';
                companyDiv.innerHTML = `
                    <strong>${company.company_name || 'N/A'}</strong>
                    ${company.name ? `<br><small class="text-muted">${company.name}</small>` : ''}
                `;
                modalBody.appendChild(companyDiv);
            });
        } else {
            modalBody.innerHTML = '<p class="text-muted">No companies found.</p>';
        }
        
        // Show modal using Bootstrap 4 syntax
        $('#dmcCompaniesModal').modal('show');
    }
    
    // Add explicit close functionality
    function closeDmcModal() {
        $('#dmcCompaniesModal').modal('hide');
    }
    
    // Ensure modal can be closed with escape key and click outside
    $(document).ready(function() {
        $('#dmcCompaniesModal').on('hidden.bs.modal', function () {
            // Clean up when modal is closed
            $('#dmcCompaniesModalBody').html('');
        });
    });
</script>
@endsection
