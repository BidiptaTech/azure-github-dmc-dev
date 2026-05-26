@extends('layouts.layout')
@extends('layouts.datatablecss')

@section('title', $pageTitle ?? 'Package Bookings')

@section('content')
<style>
    .new-enq-header-bar { background: linear-gradient(135deg, #f8f9fc 0%, #fff 100%); border-radius: 0.5rem; border: 1px solid rgba(105, 108, 255, 0.08); }
    .new-enq-stat-item { transition: transform 0.15s ease, box-shadow 0.15s ease; min-height: 100%; }
    .new-enq-stat-item:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
    .new-enq-stat-item .stat-value { font-size: 1.25rem; font-weight: 600; letter-spacing: -0.02em; }
    .new-enq-stat-item .stat-label { display: block; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.04em; opacity: 0.85; margin-top: 0.15rem; }

    /* Compact table styles (match Tours tables) */
    #packageBookingsTable {
        font-size: 0.875rem;
        width: 100%;
        margin-bottom: 0;
        background-color: #fff;
    }

    /* Let DataTables Responsive handle small screens */
    #packageBookingsTable {
        table-layout: auto;
    }
    #packageBookingsTable thead th {
        padding: 0.5rem 0.5rem;
        font-size: 0.8125rem;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        background-color: #f8f9fa;
    }
    #packageBookingsTable tbody td {
        padding: 0.5rem 0.5rem;
        vertical-align: top;
        overflow: hidden;
        background-color: #fff;
    }
    #packageBookingsTable tbody tr {
        height: auto;
        min-height: 50px;
    }
    /* Actions column: soft-badge style */
    #packageBookingsTable td.col-actions {
        white-space: nowrap;
        overflow: visible !important;
        min-width: 140px;
    }
    #packageBookingsTable th:last-child { min-width: 140px; }
    #packageBookingsTable .action-icon-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        padding: 0;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        cursor: pointer;
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        text-decoration: none;
    }
    #packageBookingsTable .action-icon-badge:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        color: inherit;
    }
    #packageBookingsTable .action-icon-badge i {
        font-size: 1rem;
        color: var(--action-color, #475569);
        line-height: 1;
    }
    #packageBookingsTable button.action-icon-badge {
        appearance: none;
        -webkit-appearance: none;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    #packageBookingsTable button.action-icon-badge:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    /* DataTables footer alignment (info + pagination) */
    .dataTables_wrapper .dataTables_info {
        padding-top: 0.75rem !important;
        font-size: 0.8125rem;
        color: #64748b;
    }
    .dataTables_wrapper .dataTables_paginate {
        padding-top: 0.5rem !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.25rem 0.6rem !important;
        margin: 0 0.1rem !important;
        border-radius: 0.5rem !important;
    }

    /* Ensure SweetAlert is always above Bootstrap modals */
    .swal2-container {
        z-index: 20000 !important;
    }
</style>

{{-- Ensure SweetAlert2 is available for professional prompts --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-xxl flex-grow-1 container-p-y">
    @include('bookings.partials.booking-type-tabs', [
        'type' => 'packages',
        'toursUrl' => $toursUrl,
        'packagesUrl' => $packagesUrl,
    ])

    <div class="new-enq-header-bar p-3 mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <h4 class="fw-bold mb-0" style="font-size: 1.25rem;">
                    <span class="text-muted fw-light">Bookings /</span> {{ $pageHeading ?? ($pageTitle ?? 'Package Bookings') }}
                </h4>
                <span class="badge bg-light text-primary border border-primary border-opacity-25 px-2 py-1" style="font-size: 0.75rem;">
                    <i class="ri-file-list-line me-1"></i>{{ $bookings->count() }} Records
                </span>
            </div>
            <div class="row g-2 flex-grow-1">
                <div class="col-12 col-md-4">
                    <div class="new-enq-stat-item d-flex align-items-center gap-2 px-3 py-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-primary rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-box-3-line text-white"></i></div>
                        <div class="min-w-0">
                            <span class="stat-value d-block lh-1">{{ $bookings->count() }}</span>
                            <span class="stat-label text-muted">Total</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="new-enq-stat-item d-flex align-items-center gap-2 px-3 py-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-success rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-calendar-line text-white"></i></div>
                        <div class="min-w-0">
                            <span class="stat-value d-block lh-1">{{ $bookings->where('created_at', '>=', now()->today())->count() }}</span>
                            <span class="stat-label text-muted">Today</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="new-enq-stat-item d-flex align-items-center gap-2 px-3 py-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-info rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-time-line text-white"></i></div>
                        <div class="min-w-0">
                            <span class="stat-value d-block lh-1">{{ $bookings->where('created_at', '>=', now()->startOfMonth())->where('created_at', '<=', now()->endOfMonth())->count() }}</span>
                            <span class="stat-label text-muted">{{ date('F') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters (DataTables) -->
    <div class="new-enq-filter-bar card mb-3 border-0 shadow-sm">
        <div class="card-body py-2 px-3">
            <div class="row g-2 align-items-end">
                <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                    <span class="text-muted fw-medium d-flex align-items-center gap-1" style="font-size: 0.8rem;"><i class="ri-filter-3-line"></i> Filters</span>
                    <button class="btn btn-sm btn-outline-secondary py-1 px-2" onclick="resetPackageFilters()" title="Reset filters">
                        <i class="ri-refresh-line me-1"></i> Reset
                    </button>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <label class="form-label mb-0 small text-muted">Search</label>
                    <input type="text" class="form-control form-control-sm" id="pkgSearchInput" placeholder="Booking ID, package, customer, agent...">
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <label class="form-label mb-0 small text-muted">Agent</label>
                    <select class="form-select form-select-sm" id="pkgAgentFilter">
                        <option value="">All Agents</option>
                        @foreach(($bookings->pluck('agent.name')->filter()->unique()->values()) as $agentName)
                            <option value="{{ $agentName }}">{{ $agentName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <label class="form-label mb-0 small text-muted">Start Date</label>
                    <input type="date" class="form-control form-control-sm" id="pkgStartDateFilter" max="{{ now()->toDateString() }}" value="{{ now()->startOfMonth()->toDateString() }}">
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <label class="form-label mb-0 small text-muted">End Date</label>
                    <input type="date" class="form-control form-control-sm" id="pkgEndDateFilter" max="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}">
                </div>
                @if(!empty($showBookingStatusColumn))
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <label class="form-label mb-0 small text-muted">Booking status</label>
                    <select class="form-select form-select-sm" id="pkgBookingStatusFilter">
                        <option value="">All statuses</option>
                        @foreach(($bookings->map(fn ($row) => data_get($row, $statusColumn))->filter()->unique()->sort()->values()) as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $tableTitle ?? 'Package Bookings' }}</h5>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-warning btn-sm dropdown-toggle" type="button" id="pkgExportDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="pkgExportDropdown">
                        <li><a class="dropdown-item" href="javascript:void(0);" id="pkgExportCopy">Copy</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" id="pkgExportCSV">CSV</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" id="pkgExportExcel">Excel</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" id="pkgExportPDF">PDF</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" id="pkgExportPrint">Print</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body">
            @include('package-bookings._table', [
                'bookings' => $bookings,
                'statusColumn' => $statusColumn,
                'packageComments' => $packageComments ?? collect([]),
                'showBookingStatusColumn' => !empty($showBookingStatusColumn),
                'showPackagePaymentColumn' => !empty($showPackagePaymentColumn),
                'showNegotiationColumn' => array_key_exists('showNegotiationColumn', get_defined_vars()) ? (bool) $showNegotiationColumn : true,
                'hideEditAction' => !empty($hideEditAction),
            ])
        </div>
    </div>
</div>

{{-- Service modals must live outside <table> for DataTables --}}
@include('package-bookings._service-modals', ['bookings' => $bookings])

@if(!empty($showPackagePaymentColumn))
    @include('package-bookings._payment-modals', [
        'bookings' => $bookings,
        'packageComments' => $packageComments ?? collect([]),
        'pkgCurrency' => \App\Helpers\CommonHelper::getDmcCurrencyByCountry(),
    ])
@endif

<!-- Package Update Negotiation Modal -->
<div class="modal fade" id="packageNegotiationUpdateModal" tabindex="-1" aria-labelledby="packageNegotiationUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="packageNegotiationUpdateModalLabel">Update Price & Comment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="packageNegotiationUpdateForm" method="POST" action="{{ route('package-bookings.update-negotiation') }}">
                @csrf
                <input type="hidden" name="package_inquiry_id" id="pkg_modal_package_inquiry_id" />
                <input type="hidden" name="actual_amount" id="pkg_modal_actual_amount" />
                <input type="hidden" name="discount" id="pkg_modal_discount" />
                <div class="modal-body">
                    <div class="border rounded p-3 bg-light mb-3">
                        <div class="row g-3">
                            <div class="col-4">
                                <small class="text-muted d-block">Actual Amount</small>
                                <div class="fw-semibold" id="pkg_display_actual">—</div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Discount</small>
                                <div class="fw-semibold text-danger" id="pkg_display_discount">—</div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Previous Negotiated Amount</small>
                                <div class="fw-semibold text-success" id="pkg_display_price">—</div>
                            </div>
                            <div class="col-12">
                                <small class="text-muted d-block">Last Comment</small>
                                <div class="fw-semibold" id="pkg_display_comment">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="pkg_current_price" class="form-label">New Price</label>
                        <input id="pkg_current_price" type="number" name="price" class="form-control" placeholder="Enter new price" required />
                        <div id="pkg-warning-message" class="alert alert-warning mt-2 py-2 px-3 d-none">
                            Enquiry price cannot exceed the actual amount.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="pkg_comment" class="form-label">New Comment</label>
                        <textarea id="pkg_comment" name="comment" rows="3" class="form-control" placeholder="Enter new comment" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="pkg_submit_btn">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Package Agent Negotiation Modal -->
<div class="modal fade" id="packageAgentNegotiationModal" tabindex="-1" aria-labelledby="packageAgentNegotiationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" id="packageAgentNegotiationForm" method="POST" action="{{ route('package-bookings.agent-negotiation') }}">
            @csrf
            <input type="hidden" name="booking_id" id="pkg_agent_booking_id">
            <input type="hidden" name="action" id="pkg_agent_action" value="negotiate">
            <input type="hidden" name="actual_amount" id="pkg_agent_actual_amount">
            <div class="modal-header">
                <h5 class="modal-title" id="packageAgentNegotiationModalLabel">Negotiate by Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="border rounded p-3 bg-light mb-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Booking</small>
                            <div class="fw-semibold" id="pkgAgentDisplayId">—</div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <small class="text-muted d-block">Current Amount</small>
                            <div class="fw-semibold text-primary" id="pkgAgentCurrentAmount">—</div>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Last Agent Offer</small>
                            <div class="fw-semibold text-warning" id="pkgAgentLastAmount">—</div>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Last Remarks</small>
                            <div class="text-muted" id="pkgAgentLastRemark">—</div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="pkgAgentAmount" class="form-label">Amount</label>
                    <input type="number" class="form-control" id="pkgAgentAmount" name="amount" min="0" step="0.01" placeholder="Enter negotiated amount">
                    <div class="form-text text-primary fw-semibold">Maximum allowed amount: <span id="pkgAgentMaxValue">—</span></div>
                </div>
                <div class="mb-3">
                    <label for="pkgAgentRemark" class="form-label">Remarks <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="pkgAgentRemark" name="comment" rows="3" placeholder="Add remarks for this negotiation" required></textarea>
                    <div class="invalid-feedback d-none" id="pkgAgentRemarkError">Please fill the input.</div>
                </div>
                <div class="alert alert-warning py-2 px-3 d-none" id="pkgAgentWarning">
                    Negotiated amount cannot exceed the current amount.
                </div>
            </div>
            <div class="modal-footer border-0 pt-2 pb-3 px-3 px-md-4 d-flex flex-nowrap align-items-center justify-content-end gap-2" style="background: #f8f9fa;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline-success" onclick="submitPackageAgentNegotiation('confirm')">Confirm Package</button>
                <button type="button" class="btn btn-outline-danger" onclick="submitPackageAgentNegotiation('cancel')">Cancel Package</button>
                <button type="button" class="btn btn-primary" onclick="submitPackageAgentNegotiation('negotiate')">Negotiate</button>
            </div>
        </form>
    </div>
</div>

@php
    $__pkgShowBookingStatus = !empty($showBookingStatusColumn);
    $__pkgShowPayments = !empty($showPackagePaymentColumn);
    $__pkgShowNegotiation = array_key_exists('showNegotiationColumn', get_defined_vars()) ? (bool) $showNegotiationColumn : true;

    // Column indexes:
    // 0:#, 1:Booking Details, 2:Package Details, 3:Created, 4:Agent,
    // 5:(optional Booking status),
    // next: Services, (optional Negotiation), (optional Payments), last: Actions
    $__pkgSvcIdx = $__pkgShowBookingStatus ? 6 : 5;
    $__pkgNegIdx = $__pkgShowNegotiation ? ($__pkgSvcIdx + 1) : null;
    $__pkgPayIdx = $__pkgShowPayments
        ? (($__pkgShowNegotiation ? $__pkgNegIdx : $__pkgSvcIdx) + 1)
        : null;
    $__pkgActIdx = ($__pkgShowPayments ? $__pkgPayIdx : ($__pkgShowNegotiation ? $__pkgNegIdx : $__pkgSvcIdx)) + 1;

    $__pkgNoOrder = [$__pkgSvcIdx];
    if ($__pkgShowNegotiation) $__pkgNoOrder[] = $__pkgNegIdx;
    if ($__pkgShowPayments) $__pkgNoOrder[] = $__pkgPayIdx;
    $__pkgNoOrder[] = $__pkgActIdx;
@endphp

<script>
    let pkgTable = null;

    function pkgFormatMoney(value) {
        const n = parseFloat(value);
        if (!Number.isFinite(n)) return '—';
        return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function pkgAlert(options) {
        // SweetAlert2 is used in Tours; Package pages may not always include it.
        if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
            const openModal = document.querySelector('.modal.show');
            const target = openModal || document.body;
            return Swal.fire({ target, ...options });
        }
        const title = options?.title ? String(options.title) : 'Notice';
        const text = options?.text ? String(options.text) : '';
        if (options?.icon === 'warning' || options?.icon === 'error' || options?.icon === 'info' || options?.icon === 'question') {
            // best-effort native fallback
            if (text) alert(`${title}\n\n${text}`);
            else alert(title);
        } else {
            if (text) alert(`${title}\n\n${text}`);
            else alert(title);
        }
        return Promise.resolve({ isConfirmed: true });
    }

    function pkgConfirm(options) {
        if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
            const openModal = document.querySelector('.modal.show');
            const target = openModal || document.body;
            return Swal.fire({ target, showCancelButton: true, ...options });
        }
        const title = options?.title ? String(options.title) : 'Are you sure?';
        const text = options?.text ? String(options.text) : '';
        const ok = confirm(text ? `${title}\n\n${text}` : title);
        return Promise.resolve({ isConfirmed: ok });
    }

    // Flash messages (professional success UX after redirects like "Verify Payment")
    document.addEventListener('DOMContentLoaded', function () {
        const flashSuccess = @json(session('success'));
        const flashError = @json(session('error'));
        if (flashSuccess) {
            pkgAlert({
                icon: 'success',
                title: 'Done',
                text: String(flashSuccess),
                timer: 2200,
                showConfirmButton: false,
            });
        } else if (flashError) {
            pkgAlert({
                icon: 'error',
                title: 'Could not complete',
                text: String(flashError),
            });
        }
    });

    function initializePackageDataTable() {
        if (typeof $ === 'undefined' || !$.fn?.DataTable) return;

        if ($.fn.DataTable.isDataTable('#packageBookingsTable')) {
            $('#packageBookingsTable').DataTable().destroy();
        }

        pkgTable = $('#packageBookingsTable').DataTable({
            dom: 'lrtip',
            responsive: true,
            scrollX: false,
            autoWidth: false,
            buttons: [
                { extend: 'copy', className: 'buttons-copy' },
                { extend: 'csv', className: 'buttons-csv' },
                { extend: 'excel', className: 'buttons-excel' },
                { extend: 'pdf', className: 'buttons-pdf' },
                { extend: 'print', className: 'buttons-print' }
            ],
            columnDefs: [
                { orderable: false, targets: @json($__pkgNoOrder) },
                { searchable: false, targets: @json($__pkgNoOrder) }
            ],
            pageLength: 10,
            lengthMenu: [[10,25,50,100,-1],[10,25,50,100,"All"]]
        });

        // Ensure columns are aligned (especially when scrollX is off)
        setTimeout(function () {
            try { pkgTable.columns.adjust(); } catch (e) {}
        }, 50);

        $(window).on('resize.pkgTable', function () {
            if (!pkgTable) return;
            try { pkgTable.columns.adjust(); } catch (e) {}
        });

        // External search
        $('#pkgSearchInput').on('input', function () {
            pkgTable.search(this.value || '').draw();
        });

        // Agent filter (column index 4 after removing travel dates)
        $('#pkgAgentFilter').on('change', function () {
            const v = this.value || '';
            // Agent cell contains name + company on separate lines; use substring match.
            pkgTable.column(4).search(v, false, true).draw();
        });

        // Date range filter by data-created-at (YYYY-MM-DD)
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (settings.nTable.id !== 'packageBookingsTable') return true;

            const start = document.getElementById('pkgStartDateFilter')?.value || '';
            const end = document.getElementById('pkgEndDateFilter')?.value || '';
            if (!start && !end) return true;

            const rowNode = pkgTable.row(dataIndex).node();
            const createdAt = rowNode?.getAttribute('data-created-at') || '';
            if (!createdAt) return false;

            if (start && createdAt < start) return false;
            if (end && createdAt > end) return false;
            return true;
        });

        $('#pkgStartDateFilter, #pkgEndDateFilter').on('change', function () {
            pkgTable.draw();
        });

        @if(!empty($showBookingStatusColumn))
        $('#pkgBookingStatusFilter').on('change', function () {
            const v = this.value || '';
            pkgTable.column(5).search(v, false, true).draw();
        });
        @endif

        // Export dropdown triggers
        $('#pkgExportCopy').on('click', function() { pkgTable.button('.buttons-copy').trigger(); });
        $('#pkgExportCSV').on('click', function() { pkgTable.button('.buttons-csv').trigger(); });
        $('#pkgExportExcel').on('click', function() { pkgTable.button('.buttons-excel').trigger(); });
        $('#pkgExportPDF').on('click', function() { pkgTable.button('.buttons-pdf').trigger(); });
        $('#pkgExportPrint').on('click', function() { pkgTable.button('.buttons-print').trigger(); });
    }

    // Body-level tooltip (same UX as Tours tables)
    let pkgTooltipsInitialized = false;
    function initPkgTooltips() {
        if (pkgTooltipsInitialized) return;
        if (typeof $ === 'undefined') return;
        pkgTooltipsInitialized = true;

        const $globalTooltip = $('#pkg-global-tooltip');
        const $tip = $globalTooltip.length
            ? $globalTooltip
            : $('<div id="pkg-global-tooltip"></div>').css({
                position: 'fixed',
                zIndex: 9999,
                padding: '8px 10px',
                background: '#0f172a',
                color: '#fff',
                borderRadius: '8px',
                fontSize: '12px',
                maxWidth: '280px',
                boxShadow: '0 10px 25px rgba(0,0,0,0.18)',
                display: 'none',
                pointerEvents: 'none',
                lineHeight: '1.2'
            }).appendTo('body');

        const showAt = (el, text) => {
            if (!text) return;
            const rect = el.getBoundingClientRect();
            const x = rect.left + rect.width / 2;
            const y = rect.top - 10;
            $tip.text(text).css({
                left: Math.max(10, Math.min(window.innerWidth - 10, x)) + 'px',
                top: Math.max(10, y) + 'px',
                transform: 'translate(-50%, -100%)'
            }).show();
        };
        const hide = () => $tip.hide();

        // Use delegated events so it works after DataTables redraws
        $(document).on('mouseenter', '#packageBookingsTable .action-icon-badge', function() {
            const text = $(this).attr('data-tooltip') || $(this).attr('title') || '';
            if (!text) return;
            showAt(this, text);
        });
        $(document).on('mouseleave', '#packageBookingsTable .action-icon-badge', hide);

        $(document).on('mouseenter', '#packageBookingsTable thead .th-tooltip', function() {
            const text = $(this).attr('data-tooltip') || $(this).attr('title') || '';
            if (!text) return;
            showAt(this, text);
        });
        $(document).on('mouseleave', '#packageBookingsTable thead .th-tooltip', hide);
    }

    // Ensure tooltip bindings are attached after DOM is ready
    document.addEventListener('DOMContentLoaded', function () {
        initPkgTooltips();
    });

    function resetPackageFilters() {
        const s = document.getElementById('pkgSearchInput');
        const a = document.getElementById('pkgAgentFilter');
        const st = document.getElementById('pkgBookingStatusFilter');
        const sd = document.getElementById('pkgStartDateFilter');
        const ed = document.getElementById('pkgEndDateFilter');
        if (s) s.value = '';
        if (a) a.value = '';
        if (st) st.value = '';
        if (sd) sd.value = '{{ now()->startOfMonth()->toDateString() }}';
        if (ed) ed.value = '{{ now()->toDateString() }}';
        if (pkgTable) {
            pkgTable.search('').columns().search('');
            pkgTable.draw();
        }
    }

    function openPackageNegotiationUpdateModal(btn) {
        const inquiryId = btn?.getAttribute('data-package-inquiry-id') || '';
        const price = btn?.getAttribute('data-price') || '';
        const actual = btn?.getAttribute('data-actual') || '';
        const discount = btn?.getAttribute('data-discount') || '';
        const comment = btn?.getAttribute('data-comment') || '';

        document.getElementById('pkg_modal_package_inquiry_id').value = inquiryId;
        document.getElementById('pkg_modal_actual_amount').value = actual;
        document.getElementById('pkg_modal_discount').value = discount;

        document.getElementById('pkg_display_actual').textContent = actual !== '' ? actual : '—';
        document.getElementById('pkg_display_discount').textContent = discount !== '' ? discount : '—';
        document.getElementById('pkg_display_price').textContent = price !== '' ? price : '—';
        document.getElementById('pkg_display_comment').textContent = comment !== '' ? comment : '—';

        const modalEl = document.getElementById('packageNegotiationUpdateModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    function openPackageAgentNegotiationModal(btn) {
        const bookingId = btn?.getAttribute('data-booking-id') || '';
        const displayId = btn?.getAttribute('data-display-id') || bookingId;
        const actual = btn?.getAttribute('data-actual') || '0';
        const lastAmountRaw = btn?.getAttribute('data-last-amount');
        const lastComment = btn?.getAttribute('data-last-comment') || '—';
        const amountInput = document.getElementById('pkgAgentAmount');

        const ceiling = parseFloat(actual || '0');
        const lastAgentNum = lastAmountRaw !== null && lastAmountRaw !== '' ? parseFloat(lastAmountRaw) : NaN;

        document.getElementById('pkg_agent_booking_id').value = bookingId;
        document.getElementById('pkg_agent_actual_amount').value = Number.isFinite(ceiling) && ceiling > 0 ? String(ceiling) : actual;
        document.getElementById('pkgAgentDisplayId').textContent = displayId || '—';
        document.getElementById('pkgAgentCurrentAmount').textContent = pkgFormatMoney(ceiling > 0 ? ceiling : actual);
        document.getElementById('pkgAgentLastAmount').textContent = Number.isFinite(lastAgentNum) && lastAgentNum > 0 ? pkgFormatMoney(lastAgentNum) : '—';
        document.getElementById('pkgAgentLastRemark').textContent = lastComment && lastComment !== '' ? lastComment : '—';

        if (Number.isFinite(ceiling) && ceiling > 0) {
            document.getElementById('pkgAgentMaxValue').textContent = pkgFormatMoney(ceiling);
            amountInput.setAttribute('max', String(ceiling));
        } else {
            document.getElementById('pkgAgentMaxValue').textContent = '—';
            amountInput.removeAttribute('max');
        }

        // Default the amount field to the current/max amount (editable downwards).
        // Users can enter less, but should not exceed the max.
        if (Number.isFinite(ceiling) && ceiling > 0) {
            amountInput.value = String(ceiling);
        } else if (Number.isFinite(lastAgentNum) && lastAgentNum > 0) {
            amountInput.value = String(lastAgentNum);
        } else {
            amountInput.value = '';
        }

        // Clamp to max on input (extra safety beyond HTML max attribute)
        amountInput.oninput = function () {
            const max = parseFloat(this.getAttribute('max') || '0');
            const v = parseFloat(this.value || '0');
            if (max > 0 && Number.isFinite(v) && v > max) {
                this.value = String(max);
            }
        };
        document.getElementById('pkgAgentRemark').value = '';
        document.getElementById('pkgAgentWarning').classList.add('d-none');
        document.getElementById('pkgAgentRemarkError').classList.add('d-none');

        const modalEl = document.getElementById('packageAgentNegotiationModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    function submitPackageAgentNegotiation(action) {
        const form = document.getElementById('packageAgentNegotiationForm');
        const amountInput = document.getElementById('pkgAgentAmount');
        const remarkInput = document.getElementById('pkgAgentRemark');
        const warning = document.getElementById('pkgAgentWarning');
        const remarkError = document.getElementById('pkgAgentRemarkError');
        const modalEl = document.getElementById('packageAgentNegotiationModal');

        document.getElementById('pkg_agent_action').value = action;

        const actual = parseFloat(document.getElementById('pkg_agent_actual_amount').value || '0');
        const amount = parseFloat(amountInput.value || '0');

        warning.classList.add('d-none');
        remarkError.classList.add('d-none');
        remarkInput.classList.remove('is-invalid');

        if (action === 'negotiate') {
            const amountValue = parseFloat(amountInput.value);
            if (isNaN(amountValue) || amountValue <= 0) {
                pkgAlert({
                    icon: 'warning',
                    title: 'Amount required',
                    text: 'Please enter a valid negotiation amount.'
                });
                return;
            }
            if (!remarkInput.value || remarkInput.value.trim() === '') {
                remarkInput.classList.add('is-invalid');
                remarkError.classList.remove('d-none');
                return;
            }
            if (actual > 0 && amount > actual) {
                warning.classList.remove('d-none');
                return;
            }
            form.submit();
            return;
        }

        // Cancel / Confirm: remarks required + confirm prompt (match Tours)
        if (!remarkInput.value || remarkInput.value.trim() === '') {
            remarkInput.classList.add('is-invalid');
            remarkError.classList.remove('d-none');
            return;
        }

        const prompts = {
            cancel: {
                title: 'Cancel this package?',
                text: 'Status will be updated to a cancelled state.',
                icon: 'warning',
                confirmButtonText: 'Yes, cancel it',
                confirmButtonColor: '#d33',
                cancelButtonText: 'Keep package'
            },
            confirm: {
                title: 'Confirm this package?',
                text: 'This will move the booking to Confirmed status.',
                icon: 'question',
                confirmButtonText: 'Yes, confirm it',
                confirmButtonColor: '#198754',
                cancelButtonText: 'Review again'
            }
        };

        const prompt = prompts[action];
        if (!prompt) return;

        // Hide bootstrap modal before SweetAlert (prevents backdrop/scroll issues)
        const instance = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
        if (instance) instance.hide();

        pkgConfirm({
            title: prompt.title,
            text: prompt.text,
            icon: prompt.icon,
            confirmButtonText: prompt.confirmButtonText,
            confirmButtonColor: prompt.confirmButtonColor,
            cancelButtonText: prompt.cancelButtonText
        }).then((result) => {
            if (!result.isConfirmed) {
                // Re-open modal if user cancelled the prompt
                if (modalEl) new bootstrap.Modal(modalEl).show();
                return;
            }
            document.getElementById('pkg_agent_action').value = action;
            form.submit();
        });
    }

    function packageCancelBooking(bookingId) {
        pkgConfirm({
            title: 'Cancel this booking?',
            text: 'Are you sure you want to cancel this package booking?',
            icon: 'warning',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, cancel it',
            cancelButtonText: 'No'
        }).then((result) => {
            if (!result.isConfirmed) return;
            fetch("{{ url('/package-bookings/cancel') }}/" + encodeURIComponent(bookingId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data?.success) {
                    pkgAlert({ title: 'Cancelled!', text: data.message || 'Booking cancelled.', icon: 'success' })
                        .then(() => window.location.reload());
                } else {
                    pkgAlert({ title: 'Error', text: data?.message || 'Failed to cancel booking.', icon: 'error' });
                }
            })
            .catch(() => pkgAlert({ title: 'Error', text: 'Failed to cancel booking.', icon: 'error' }));
        });
    }

    function packageProcessRefund(bookingId) {
        pkgConfirm({
            title: 'Process refund?',
            text: 'This will mark the package booking as Refunded and record refund details.',
            icon: 'warning',
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, process refund',
            cancelButtonText: 'Not now'
        }).then(async (result) => {
            if (!result.isConfirmed) return;

            let formValues = null;
            if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                const res = await Swal.fire({
                    title: 'Refund details',
                    html: `
                        <div class="text-start">
                            <label class="form-label">Refund mode</label>
                            <select id="swal_refund_mode" class="form-select mb-2">
                                <option value="">Select</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Card Refund">Card Refund</option>
                                <option value="Cash">Cash</option>
                                <option value="Credit Note">Credit Note</option>
                            </select>
                            <label class="form-label">Refund reference</label>
                            <input id="swal_refund_reference" class="form-control mb-2" placeholder="Transaction / reference ID (optional)">
                            <label class="form-label">Remark</label>
                            <textarea id="swal_refund_remark" class="form-control" rows="3" placeholder="Internal note (optional)"></textarea>
                        </div>
                    `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'Submit refund',
                    confirmButtonColor: '#16a34a',
                    cancelButtonText: 'Cancel',
                    preConfirm: () => {
                        const refund_mode = document.getElementById('swal_refund_mode').value;
                        const refund_reference = document.getElementById('swal_refund_reference').value;
                        const remark = document.getElementById('swal_refund_remark').value;
                        return { refund_mode, refund_reference, remark };
                    }
                });
                formValues = res?.value || null;
            } else {
                // fallback (should be rare now that SweetAlert2 is injected)
                const refund_mode = prompt('Refund mode (optional):', '') || '';
                const refund_reference = prompt('Refund reference (optional):', '') || '';
                const remark = prompt('Remark (optional):', '') || '';
                formValues = { refund_mode, refund_reference, remark };
            }

            if (!formValues) return;

            if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we update the booking.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            }

            fetch("{{ route('package-bookings.process-refund') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    booking_id: bookingId,
                    refund_mode: formValues.refund_mode,
                    refund_reference: formValues.refund_reference,
                    remark: formValues.remark
                })
            })
            .then(r => r.json().then(j => ({ ok: r.ok, json: j })))
            .then(({ ok, json }) => {
                if (ok && json?.success) {
                    pkgAlert({ title: 'Refund updated', text: json.message || 'Refund processed successfully.', icon: 'success' })
                        .then(() => window.location.reload());
                } else {
                    const msg = json?.message || 'Failed to process refund.';
                    pkgAlert({ title: 'Error', text: msg, icon: 'error' });
                }
            })
            .catch(() => pkgAlert({ title: 'Error', text: 'Failed to process refund.', icon: 'error' }));
        });
    }

    function packageConfirmBooking(bookingId, agreedActualAmount) {
        pkgConfirm({
            title: 'Confirm this booking?',
            text: 'This will move the booking to Confirmed status.',
            icon: 'question',
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, confirm',
            cancelButtonText: 'No'
        }).then((result) => {
            if (!result.isConfirmed) return;
            const amt = (typeof agreedActualAmount === 'number' && Number.isFinite(agreedActualAmount) && agreedActualAmount > 0)
                ? agreedActualAmount
                : 0;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('package-bookings.agent-negotiation') }}";
            form.innerHTML = `
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="booking_id" value="${bookingId}">
                <input type="hidden" name="action" value="confirm">
                <input type="hidden" name="comment" value="Confirmed from list">
                <input type="hidden" name="actual_amount" value="${amt}">
            `;
            document.body.appendChild(form);
            form.submit();
        });
    }

    function openPackageServiceModal(serviceType, bookingId) {
        const map = {
            hotel: 'hotelDetailsModal',
            attraction: 'attractionDetailsModal',
            restaurant: 'restaurantDetailsModal',
            arrival: 'arrivalDetailsModal',
            departure: 'departureDetailsModal'
        };
        const prefix = map[serviceType];
        if (!prefix) return;
        const modalId = `${prefix}${bookingId}`;
        const modalEl = document.getElementById(modalId);
        if (!modalEl) return;
        const modal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
        modal.show();
    }

    @if(!empty($showPackagePaymentColumn))
    function pkgCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    }

    function pkgApprovePackagePayment(bookingId, paymentIndex, ev) {
        pkgConfirm({
            title: 'Approve payment?',
            text: 'This will mark the payment as verified.',
            icon: 'question',
            confirmButtonText: 'Approve',
            confirmButtonColor: '#16a34a',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (!result.isConfirmed) return;
            fetch(`{{ url('/package-booking') }}/${encodeURIComponent(bookingId)}/approve-payment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': pkgCsrf(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ payment_index: paymentIndex })
            })
            .then(r => r.json())
            .then(data => {
                if (data?.success) {
                    pkgAlert({ title: 'Payment approved', text: data.message || 'The payment was approved successfully.', icon: 'success', timer: 1800, showConfirmButton: false }).then(() => location.reload());
                } else {
                    pkgAlert({ title: 'Error', text: data?.message || 'Failed.', icon: 'error' });
                }
            })
            .catch(() => pkgAlert({ title: 'Error', text: 'Request failed.', icon: 'error' }));
        });
    }

    function pkgDeclinePackagePayment(bookingId, paymentIndex, ev) {
        // Use SweetAlert input so it doesn't appear behind modals
        pkgConfirm({
            title: 'Reject payment?',
            text: 'Please provide a decline reason (min 10 characters).',
            icon: 'warning',
            confirmButtonText: 'Reject',
            confirmButtonColor: '#dc2626',
            cancelButtonText: 'Cancel',
            input: 'textarea',
            inputPlaceholder: 'Enter decline reason...',
            inputAttributes: { 'aria-label': 'Decline reason' },
            preConfirm: (value) => {
                const v = String(value || '').trim();
                if (v.length < 10) {
                    if (typeof Swal !== 'undefined') Swal.showValidationMessage('Please enter at least 10 characters.');
                    return false;
                }
                return v;
            }
        }).then((result) => {
            if (!result.isConfirmed) return;
            const reason = String(result.value || '').trim();
            if (reason.length < 10) return;

            fetch(`{{ url('/package-booking') }}/${encodeURIComponent(bookingId)}/decline-payment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': pkgCsrf(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ payment_index: paymentIndex, decline_reason: reason })
            })
            .then(r => r.json())
            .then(data => {
                if (data?.success) {
                    pkgAlert({ title: 'Payment rejected', text: data.message || 'The payment was rejected successfully.', icon: 'success', timer: 1800, showConfirmButton: false }).then(() => location.reload());
                } else {
                    pkgAlert({ title: 'Error', text: data?.message || 'Failed.', icon: 'error' });
                }
            })
            .catch(() => pkgAlert({ title: 'Error', text: 'Request failed.', icon: 'error' }));
        });

        return;

        fetch(`{{ url('/package-booking') }}/${encodeURIComponent(bookingId)}/decline-payment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': pkgCsrf(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ payment_index: paymentIndex, decline_reason: reason.trim() })
        })
        .then(r => r.json())
        .then(data => {
            if (data?.success) {
                pkgAlert({ title: 'Payment rejected', text: data.message || 'The payment was rejected successfully.', icon: 'success', timer: 1800, showConfirmButton: false }).then(() => location.reload());
            } else {
                pkgAlert({ title: 'Error', text: data?.message || 'Failed.', icon: 'error' });
            }
        })
        .catch(() => pkgAlert({ title: 'Error', text: 'Request failed.', icon: 'error' }));
    }

    function pkgOpenEditPackagePayment(bookingId, rowId, paymentIndex, btn) {
        // Keep Payment History modal open; edit via SweetAlert form (no nested Bootstrap modals)
        const amt0 = btn?.getAttribute('data-amt') || '';
        const dt0 = btn?.getAttribute('data-pdate') || '';
        const typ0 = (btn?.getAttribute('data-ptype') || 'cash').toLowerCase();
        const txn0 = btn?.getAttribute('data-txn') || '';

        pkgConfirm({
            title: 'Edit payment',
            icon: 'info',
            confirmButtonText: 'Save',
            confirmButtonColor: '#2563eb',
            cancelButtonText: 'Cancel',
            html: `
                <div class="text-start">
                    <label class="form-label mb-1">Amount</label>
                    <input id="swal_pkg_pay_amt" type="number" step="0.01" min="0.01" class="form-control mb-2" value="${String(amt0).replace(/"/g,'&quot;')}">
                    <label class="form-label mb-1">Date</label>
                    <input id="swal_pkg_pay_date" type="date" class="form-control mb-2" value="${String(dt0).replace(/"/g,'&quot;')}">
                    <label class="form-label mb-1">Mode</label>
                    <select id="swal_pkg_pay_type" class="form-select mb-2">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="cheque">Cheque</option>
                        <option value="online">Bank transfer</option>
                    </select>
                    <label class="form-label mb-1">Transaction ID</label>
                    <input id="swal_pkg_pay_txn" type="text" class="form-control" value="${String(txn0).replace(/"/g,'&quot;')}">
                </div>
            `,
            didOpen: () => {
                const sel = document.getElementById('swal_pkg_pay_type');
                if (sel) sel.value = typ0 || 'cash';
            },
            preConfirm: () => {
                const amt = parseFloat(document.getElementById('swal_pkg_pay_amt')?.value || '0');
                const dt = document.getElementById('swal_pkg_pay_date')?.value || '';
                const typ = document.getElementById('swal_pkg_pay_type')?.value || '';
                const txn = document.getElementById('swal_pkg_pay_txn')?.value || '';
                if (!amt || amt <= 0) {
                    if (typeof Swal !== 'undefined') Swal.showValidationMessage('Please enter a valid amount.');
                    return false;
                }
                if (!dt) {
                    if (typeof Swal !== 'undefined') Swal.showValidationMessage('Please select a date.');
                    return false;
                }
                if (!typ) {
                    if (typeof Swal !== 'undefined') Swal.showValidationMessage('Please select a payment mode.');
                    return false;
                }
                return { amt, dt, typ, txn };
            }
        }).then((result) => {
            if (!result.isConfirmed) return;
            const v = result.value || {};
            fetch(`{{ url('/package-booking') }}/${encodeURIComponent(bookingId)}/update-payment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': pkgCsrf(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    payment_index: parseInt(paymentIndex, 10),
                    payment_amount: parseFloat(v.amt),
                    payment_date: v.dt,
                    payment_type: v.typ,
                    transaction_id: v.txn
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data?.success) {
                    pkgAlert({ title: 'Payment updated', text: data.message || 'The payment was updated successfully.', icon: 'success', timer: 1800, showConfirmButton: false }).then(() => location.reload());
                } else {
                    pkgAlert({ title: 'Error', text: data?.message || 'Failed.', icon: 'error' });
                }
            })
            .catch(() => pkgAlert({ title: 'Error', text: 'Request failed.', icon: 'error' }));
        });
    }

    function pkgSubmitEditPackagePayment(bookingId, rowId) {
        const idx = document.getElementById('pkgEditPayIdx' + rowId)?.value;
        const amt = document.getElementById('pkgEditPayAmt' + rowId)?.value;
        const dt = document.getElementById('pkgEditPayDate' + rowId)?.value;
        const typ = document.getElementById('pkgEditPayType' + rowId)?.value;
        const txn = document.getElementById('pkgEditPayTxn' + rowId)?.value;
        fetch(`{{ url('/package-booking') }}/${encodeURIComponent(bookingId)}/update-payment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': pkgCsrf(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                payment_index: parseInt(idx, 10),
                payment_amount: parseFloat(amt),
                payment_date: dt,
                payment_type: typ,
                transaction_id: txn
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data?.success) {
                pkgAlert({ title: 'Payment updated', text: data.message || 'The payment was updated successfully.', icon: 'success', timer: 1800, showConfirmButton: false }).then(() => location.reload());
            } else {
                pkgAlert({ title: 'Error', text: data?.message || 'Failed.', icon: 'error' });
            }
        })
        .catch(() => pkgAlert({ title: 'Error', text: 'Request failed.', icon: 'error' }));
    }

    function pkgDeletePackagePayment(bookingId, paymentIndex, ev) {
        pkgConfirm({
            title: 'Delete this payment?',
            text: 'Only pending payments can be removed.',
            icon: 'warning',
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (!result.isConfirmed) return;
            fetch(`{{ url('/package-booking') }}/${encodeURIComponent(bookingId)}/delete-payment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': pkgCsrf(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ payment_index: paymentIndex })
            })
            .then(r => r.json())
            .then(data => {
                if (data?.success) {
                    pkgAlert({ title: 'Payment deleted', text: data.message || 'The payment was deleted successfully.', icon: 'success', timer: 1800, showConfirmButton: false }).then(() => location.reload());
                } else {
                    pkgAlert({ title: 'Error', text: data?.message || 'Failed.', icon: 'error' });
                }
            })
            .catch(() => pkgAlert({ title: 'Error', text: 'Request failed.', icon: 'error' }));
        });
    }
    @endif

    document.addEventListener('DOMContentLoaded', function () {
        initializePackageDataTable();
    });
</script>
@endsection

