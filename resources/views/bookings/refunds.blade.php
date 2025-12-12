@extends('layouts.layout')
@section('title', 'Refunds')
@extends('layouts.datatablecss')

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
    /* Select2 Bootstrap Integration */
    .select2-container--default .select2-selection--single {
        height: 50px;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 50px;
        padding-left: 12px;
        padding-right: 50px; /* Space for clear button and arrow */
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 48px;
        right: 10px;
    }
    /* Style and position the clear button (X icon) */
    .select2-container--default .select2-selection--single .select2-selection__clear {
        position: absolute;
        right: 35px; /* Position it before the dropdown arrow */
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        font-size: 18px;
        color: #6c757d;
    }
    .select2-container--default .select2-selection--single .select2-selection__clear:hover {
        color: #dc3545;
    }
</style>

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-2">
                <i class="ri-money-dollar-circle-line me-2 text-success"></i>
                <span class="text-muted fw-light">Bookings /</span> Refunds
            </h4>
            <p class="text-muted">Manage refunds for cancelled definite bookings</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-success fs-6">
                <i class="ri-money-dollar-circle-line me-1"></i>
                <span id="rangeCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</span>
                <span id="rangeLabel">{{ date('F') }}</span> Refunds
            </span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1" id="statRefundsCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</h5>
                            <p class="text-muted mb-0" id="statRefundsLabel">{{ date('F') }} Refunds</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-success rounded">
                                <i class="ri-money-dollar-circle-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1" id="statTotalCount">{{ $tours->count() }}</h5>
                            <p class="text-muted mb-0" id="statTotalLabel">Total Refunds</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-info rounded">
                                <i class="ri-funds-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1" id="statPendingCount">{{ $tours->where('tour_status', 'Refund - Pending')->count() }}</h5>
                            <p class="text-muted mb-0" id="statPendingLabel">Pending Refunds</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-danger rounded">
                                <i class="ri-time-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1" id="statCompletedCount">{{ $tours->where('tour_status', 'Refunded')->count() }}</h5>
                            <p class="text-muted mb-0" id="statCompletedLabel">Completed Refunds</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-success rounded ri-arrow-go-back-line">
                                <i class="ri-check-circle-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Filters</h5>
            <button class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
                <i class="ri-refresh-line me-1"></i> Reset
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Tour ID, Display ID...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Destination</label>
                    <select class="form-select" id="destinationFilter">
                        <option value="">All Destinations</option>
                        @php
                            $allDestinations = [];
                            foreach($tours as $tour) {
                                if($tour->destination) {
                                    // Split by comma to get individual destinations
                                    $destinations = array_map('trim', explode(',', $tour->destination));
                                    $allDestinations = array_merge($allDestinations, $destinations);
                                }
                            }
                            // Get unique destinations
                            $uniqueDestinations = array_unique(array_filter($allDestinations));
                            sort($uniqueDestinations);
                        @endphp
                        @foreach($uniqueDestinations as $destination)
                            <option value="{{ $destination }}">{{ $destination }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Agent</label>
                    <select class="form-select" id="agentFilter">
                        <option value="">All Agents</option>
                        @foreach($tours->where('agent_name', '!=', null)->pluck('agent_name', 'agent_id')->unique() as $agentId => $agentName)
                            <option value="{{ $agentName }}">{{ $agentName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Refund Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="Refund - Pending">Pending</option>
                        <option value="Refunded">Refunded</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="startDateFilter" max="{{ now()->toDateString() }}" value="{{ now()->startOfMonth()->toDateString() }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" id="endDateFilter" max="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}">
                </div>
            </div>
        </div>
    </div>

    <!-- Tours Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Refunds List</h5>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-success btn-sm dropdown-toggle" type="button" id="exportDropdown"
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
        <div class="card-body">
            @if($tours->count() > 0)
            <div class="table-responsive">
                <table class="datatables-basic table table-bordered" id="toursTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tour Details</th>
                            <th>Destination</th>
                            <th>Guests</th>
                            <th>Agent</th>
                            <th>Travel Dates</th>
                            <th>Refund Status</th>
                            <th>Cancelled Date</th>
                            <th>Actions</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Auto Cancel Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tours as $key => $tour)
                        <tr 
                            class="{{ $tour->tour_status === 'Refund - Pending' ? 'table-danger' : 'table-success' }}"
                            data-created-at="{{ optional($tour->created_at)->toDateString() }}"
                            data-updated-at="{{ optional($tour->updated_at)->toDateString() }}"
                            data-adult="{{ $tour->adult ?? 0 }}"
                            data-child="{{ $tour->child ?? 0 }}"
                            data-tour-status="{{ $tour->tour_status }}"
                            data-agent="{{ $tour->agent_name }}"
                            data-destination="{{ $tour->destination }}"
                        >
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-success">{{ $tour->display_id }}</strong>
                                    <small class="text-muted">Tour ID: #{{ $tour->tour_id }}</small>
                                    @if($tour->multi_enq_id)
                                        <small class="text-info">Multi: {{ $tour->multi_enq_id }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ $tour->destination ?? 'N/A' }}</span>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex gap-3 align-items-center">
                                    <div class="d-flex align-items-center gap-1" title="Adults">
                                        <i class="ri-user-line text-success" style="font-size: 1.2rem;"></i>
                                        <span class="fw-medium">{{ $tour->adult ?? 0 }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1" title="Children">
                                        <i class="ri-user-smile-line text-warning" style="font-size: 1.2rem;"></i>
                                        <span class="fw-medium">{{ $tour->child ?? 0 }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1" title="Infants">
                                        <i class="ri-user-heart-line text-info" style="font-size: 1.2rem;"></i>
                                        <span class="fw-medium">{{ $tour->infant ?? 0 }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ $tour->agent_name ?? 'N/A' }}</span>
                                    <small class="text-muted">
                                        <i class="fas fa-building me-1"></i>
                                        {{ $tour->agent_company_name ?? 'N/A' }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    @if($tour->check_in_time && $tour->check_out_time)
                                        <span class="fw-medium">{{ \Carbon\Carbon::parse($tour->check_in_time)->format('M d, Y') }}</span>
                                        <small class="text-muted">to {{ \Carbon\Carbon::parse($tour->check_out_time)->format('M d, Y') }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($tour->tour_status === 'Refund - Pending')
                                    <span class="badge bg-danger">
                                        <i class="ri-time-line me-1"></i>
                                        Pending
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="ri-check-circle-line me-1"></i>
                                        Refunded
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ optional($tour->updated_at)->format('M d, Y') }}</span>
                                    <small class="text-muted">{{ optional($tour->updated_at)->format('h:i A') }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('bookings.view-tour', ['tourId' => \Crypt::encrypt($tour->tour_id)]) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="Audit Trail">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    @if($tour->tour_status === 'Refund - Pending')
                                        <button type="button" 
                                                class="btn btn-sm btn-success" 
                                                onclick="processRefund({{ $tour->tour_id }})"
                                                title="Process Refund">
                                            <i class="ri-money-dollar-circle-line"></i>
                                        </button>
                                    @else
                                        <span class="badge bg-success" title="Already Refunded">
                                            ✓ Refunded
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ optional($tour->created_at)->format('D, M d, Y') }}</span>
                                    <small class="text-muted">{{ optional($tour->created_at)->format('h:i A') }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ optional($tour->updated_at)->format('D, M d, Y') }}</span>
                                    <small class="text-muted">{{ optional($tour->updated_at)->format('h:i A') }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    @if($tour->auto_cancel_date)
                                        <span class="fw-semibold">
                                            <i class="fas fa-calendar-times text-warning me-1"></i>
                                            {{ \Carbon\Carbon::parse($tour->auto_cancel_date)->format('D, M d, Y') }}
                                        </span>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($tour->auto_cancel_date)->format('h:i A') }}
                                        </small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="ri-money-dollar-circle-line ri-48px text-muted mb-3"></i>
                                    <h6 class="text-muted">No refunds found</h6>
                                    <p class="text-muted small mb-0">No tours with 'Refund - Pending' status found for refund processing.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            {{-- @if($tours->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $tours->links() }}
            </div>
            @endif --}} 
            @else
            <!-- Empty State -->
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="ri-money-dollar-circle-line ri-64px text-muted"></i>
                </div>
                <h4 class="text-muted mb-3 text-center fw-bold">No Refunds Available</h4>
                <p class="text-muted mb-4 small text-center">Currently, there are no tours with 'Refund - Pending' status that require refund processing.</p>
                <p class="text-muted small text-center">Refunds will appear here when tours are cancelled and marked as definite.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const destinationFilter = document.getElementById('destinationFilter');
        const agentFilter = document.getElementById('agentFilter');
        const statusFilter = document.getElementById('statusFilter');
        const startDateFilter = document.getElementById('startDateFilter');
        const endDateFilter = document.getElementById('endDateFilter');
        const today = new Date().toISOString().split('T')[0];

        if (searchInput) searchInput.addEventListener('input', filterTable);
        // Note: destinationFilter and agentFilter event listeners are handled by Select2 initialization
        // They will trigger filterTable when changed via Select2's change event
        if (statusFilter) statusFilter.addEventListener('change', filterTable);

        if (startDateFilter) {
            startDateFilter.setAttribute('max', today);
            startDateFilter.addEventListener('change', function() {
                if (endDateFilter) {
                    if (this.value) {
                        if (endDateFilter.value && endDateFilter.value < this.value) {
                            endDateFilter.value = this.value;
                        }
                        endDateFilter.setAttribute('min', this.value);
                    } else {
                        endDateFilter.removeAttribute('min');
                    }
                }
                filterTable();
            });
        }

        if (endDateFilter) {
            endDateFilter.setAttribute('max', today);
            endDateFilter.addEventListener('change', function() {
                if (startDateFilter) {
                    if (this.value) {
                        if (startDateFilter.value && startDateFilter.value > this.value) {
                            startDateFilter.value = this.value;
                        }
                        startDateFilter.setAttribute('max', this.value);
                    } else {
                        startDateFilter.setAttribute('max', today);
                    }
                }
                filterTable();
            });
        }
    });

    $(document).ready(function() {
        setTimeout(function() {
            initializeSelect2();
            initializeDataTable();
            filterTable();
        }, 200);
    });
    
    function initializeSelect2() {
        // Initialize Select2 for Destination filter
        $('#destinationFilter').select2({
            placeholder: 'All Destinations',
            allowClear: true,
            width: '100%'
        });
        
        // Initialize Select2 for Agent filter
        $('#agentFilter').select2({
            placeholder: 'All Agents',
            allowClear: true,
            width: '100%'
        });
        
        // Trigger filterTable when Select2 values change (including when cleared)
        $('#destinationFilter, #agentFilter').on('change', function() {
            // When cleared, the value will be empty string, which shows all results
            filterTable();
        });
    }

    function filterTable() {
        const searchTerm = document.getElementById('searchInput')?.value.toLowerCase().trim() || '';
        const destinationFilter = document.getElementById('destinationFilter')?.value || '';
        const agentFilter = document.getElementById('agentFilter')?.value || '';
        const statusFilter = document.getElementById('statusFilter')?.value || '';
        const startDateValue = document.getElementById('startDateFilter')?.value || '';
        const endDateValue = document.getElementById('endDateFilter')?.value || '';

        const rows = document.querySelectorAll('#toursTable tbody tr');

        if (typeof table !== 'undefined' && table && typeof table.rows === 'function') {
            table.rows('.dt-hasChild').every(function() {
                if (this.child.isShown()) this.child.hide();
                $(this.node()).removeClass('dt-hasChild');
            });
        }

        let visibleCount = 0;
        let pendingCount = 0;
        let refundedCount = 0;

        rows.forEach(row => {
            if (row.cells.length <= 1) {
                return;
            }

            const rowText = row.textContent.toLowerCase();
            const destination = row.getAttribute('data-destination') || '';
            const agent = row.getAttribute('data-agent') || '';
            const rowStatus = row.getAttribute('data-tour-status') || '';
            const createdAt = row.getAttribute('data-created-at');
            const updatedAt = row.getAttribute('data-updated-at');

            let show = true;

            if (searchTerm && !rowText.includes(searchTerm)) {
                show = false;
            }

            // Destination filter - use LIKE operator logic (contains)
            // This works for multi-country destinations like "India, Singapore"
            if (destinationFilter) {
                // Split destination by comma and trim spaces
                const destinationCountries = destination.split(',').map(c => c.trim());
                // Check if the selected destination is in the destination list
                if (!destinationCountries.includes(destinationFilter)) {
                    show = false;
                }
            }

            if (agentFilter && agent !== agentFilter) {
                show = false;
            }

            if (statusFilter && rowStatus !== statusFilter) {
                show = false;
            }

            if (startDateValue || endDateValue) {
                if (createdAt || updatedAt) {
                    const startDate = startDateValue ? new Date(startDateValue + 'T00:00:00') : null;
                    const endDate = endDateValue ? new Date(endDateValue + 'T23:59:59') : null;
                    let dateInRange = false;

                    if (createdAt) {
                        const createdDate = new Date(createdAt + 'T00:00:00');
                        if ((!startDate || createdDate >= startDate) && (!endDate || createdDate <= endDate)) {
                            dateInRange = true;
                        }
                    }

                    if (!dateInRange && updatedAt) {
                        const updatedDate = new Date(updatedAt + 'T00:00:00');
                        if ((!startDate || updatedDate >= startDate) && (!endDate || updatedDate <= endDate)) {
                            dateInRange = true;
                        }
                    }

                    if (!dateInRange) {
                        show = false;
                    }
                } else {
                    show = false;
                }
            }

            row.style.display = show ? '' : 'none';

            if (show) {
                visibleCount++;
                if (rowStatus === 'Refund - Pending') {
                    pendingCount++;
                }
                if (rowStatus === 'Refunded') {
                    refundedCount++;
                }
            }
        });

        const countEl = document.getElementById('rangeCount');
        const labelEl = document.getElementById('rangeLabel');
        const statRefunds = document.getElementById('statRefundsCount');
        const statRefundsLabel = document.getElementById('statRefundsLabel');
        const statTotal = document.getElementById('statTotalCount');
        const statTotalLabel = document.getElementById('statTotalLabel');
        const statPending = document.getElementById('statPendingCount');
        const statPendingLabel = document.getElementById('statPendingLabel');
        const statCompleted = document.getElementById('statCompletedCount');
        const statCompletedLabel = document.getElementById('statCompletedLabel');

        if (countEl) countEl.textContent = visibleCount;
        if (statRefunds) statRefunds.textContent = visibleCount;
        if (statTotal) statTotal.textContent = visibleCount;
        if (statPending) statPending.textContent = pendingCount;
        if (statCompleted) statCompleted.textContent = refundedCount;

        if (startDateValue || endDateValue) {
            const start = startDateValue ? new Date(startDateValue) : null;
            const end = endDateValue ? new Date(endDateValue) : null;
            let label = '';

            if (start && end) {
                if (start.getTime() === end.getTime()) {
                    label = start.toLocaleString('default', { month: 'short', day: '2-digit', year: 'numeric' });
                } else if (start.getMonth() === end.getMonth() && start.getFullYear() === end.getFullYear()) {
                    if (start.getDate() === 1 && end.getDate() === new Date(end.getFullYear(), end.getMonth() + 1, 0).getDate()) {
                        label = start.toLocaleString('default', { month: 'long', year: 'numeric' });
                    } else {
                        label = `${start.getDate()}-${end.getDate()} ${start.toLocaleString('default', { month: 'short' })}, ${start.getFullYear()}`;
                    }
                } else {
                    label = `${start.toLocaleString('default', { month: 'short' })} ${start.getDate()} - ${end.toLocaleString('default', { month: 'short' })} ${end.getDate()}, ${end.getFullYear()}`;
                }
            } else if (start) {
                label = `From ${start.toLocaleString('default', { month: 'short', day: '2-digit', year: 'numeric' })}`;
            } else if (end) {
                label = `Up to ${end.toLocaleString('default', { month: 'short', day: '2-digit', year: 'numeric' })}`;
            }

            if (!label) {
                label = 'Custom Range';
            }

            if (labelEl) labelEl.textContent = label;
            if (statRefundsLabel) statRefundsLabel.textContent = `Refunds - ${label}`;
            if (statTotalLabel) statTotalLabel.textContent = `Total Refunds - ${label}`;
            if (statPendingLabel) statPendingLabel.textContent = `Pending Refunds - ${label}`;
            if (statCompletedLabel) statCompletedLabel.textContent = `Completed Refunds - ${label}`;
        } else {
            const month = new Date().toLocaleString('default', { month: 'long' });
            if (labelEl) labelEl.textContent = month;
            if (statRefundsLabel) statRefundsLabel.textContent = `${month} Refunds`;
            if (statTotalLabel) statTotalLabel.textContent = 'Total Refunds';
            if (statPendingLabel) statPendingLabel.textContent = 'Pending Refunds';
            if (statCompletedLabel) statCompletedLabel.textContent = 'Completed Refunds';
        }
    }

    function resetFilters() {
        const searchInput = document.getElementById('searchInput');
        const destinationSelect = document.getElementById('destinationFilter');
        const agentSelect = document.getElementById('agentFilter');
        const statusSelect = document.getElementById('statusFilter');
        const startDateInput = document.getElementById('startDateFilter');
        const endDateInput = document.getElementById('endDateFilter');
        const today = new Date().toISOString().split('T')[0];

        if (searchInput) searchInput.value = '';
        
        // Reset Select2 dropdowns properly
        if (destinationSelect && $('#destinationFilter').hasClass('select2-hidden-accessible')) {
            $('#destinationFilter').val(null).trigger('change');
        } else if (destinationSelect) {
            destinationSelect.value = '';
        }
        
        if (agentSelect && $('#agentFilter').hasClass('select2-hidden-accessible')) {
            $('#agentFilter').val(null).trigger('change');
        } else if (agentSelect) {
            agentSelect.value = '';
        }
        
        if (statusSelect) statusSelect.value = '';

        if (startDateInput) {
            startDateInput.value = '';
            startDateInput.setAttribute('max', today);
            startDateInput.removeAttribute('min');
        }
        if (endDateInput) {
            endDateInput.value = '';
            endDateInput.setAttribute('max', today);
            endDateInput.removeAttribute('min');
        }

        filterTable();
    }

    var table;
    function initializeDataTable() {
        if ($.fn.DataTable.isDataTable('.datatables-basic')) {
            $('.datatables-basic').DataTable().destroy();
        }

        table = $('.datatables-basic').DataTable({
            responsive: true,
            dom: 'lrtip',
            buttons: [
                'copy',
                'csv',
                'excel',
                'pdf',
                'print'
            ],
            searching: false,
            language: {
                search: "DataTable Search:",
                searchPlaceholder: "Search all columns...",
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
            lengthMenu: [10, 25, 50, 100],
            pageLength: 25,
            columnDefs: [
                {
                    targets: [8], // Actions column
                    orderable: false,
                    searchable: false
                },
                {
                    targets: [3], // Guests column
                    orderable: false
                },
                {
                    targets: [6], // Refund Status column
                    orderable: false
                }
            ],
            initComplete: function() {
                console.log('DataTable initialized successfully');
            }
        });

        $('#exportCopy').on('click', function() {
            if (table && typeof table.button === 'function') {
                table.button('.buttons-copy').trigger();
            } else {
                alert('Copy export is not available.');
            }
        });

        $('#exportCSV').on('click', function() {
            if (table && typeof table.button === 'function') {
                table.button('.buttons-csv').trigger();
            } else {
                alert('CSV export is not available.');
            }
        });

        $('#exportExcel').on('click', function() {
            if (table && typeof table.button === 'function') {
                table.button('.buttons-excel').trigger();
            } else {
                alert('Excel export is not available.');
            }
        });

        $('#exportPDF').on('click', function() {
            if (table && typeof table.button === 'function') {
                table.button('.buttons-pdf').trigger();
            } else {
                alert('PDF export is not available.');
            }
        });

        $('#exportPrint').on('click', function() {
            if (table && typeof table.button === 'function') {
                table.button('.buttons-print').trigger();
            } else {
                window.print();
            }
        });
    }

// Process refund function
function processRefund(tourId) {
    // Create advanced confirmation modal
    showConfirmationModal(
        'Process Refund Confirmation',
        'Are you sure you want to process the refund for this tour?<br><small class="text-muted">This action cannot be undone.</small>',
        'warning',
        function() {
            // Show loading modal
            showLoadingModal('Processing Refund', 'Please wait while we process the refund...');
            
            const button = event.target.closest('button');
            const originalContent = button.innerHTML;
            button.innerHTML = '<i class="ri-loader-line spinner-border spinner-border-sm me-1"></i>';
            button.disabled = true;

            $.ajax({
                url: '{{ route("bookings.process-refund") }}',
                method: 'POST',
                data: {
                    tour_id: tourId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    hideModal();
                    if (response.success) {
                        showSuccessModal(
                            'Refund Processed Successfully!',
                            'The refund has been processed and the tour status has been updated.',
                            function() {
                                location.reload();
                            }
                        );
                    } else {
                        showErrorModal('Error Processing Refund', response.message || 'An error occurred while processing the refund.');
                        // Restore button state
                        button.innerHTML = originalContent;
                        button.disabled = false;
                    }
                },
                error: function(xhr) {
                    hideModal();
                    let errorMessage = 'Error processing refund. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showErrorModal('Error Processing Refund', errorMessage);
                    // Restore button state
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }
            });
        }
    );
}

// Advanced Modal Functions
function showConfirmationModal(title, message, type, confirmCallback) {
    const modalHtml = `
        <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                    <div class="modal-body text-center py-5 px-4">
                        <!-- Refund Icon with Animation -->
                        <div class="refund-icon-wrapper mb-4">
                            <div class="refund-circle">
                                <i class="ri-money-dollar-circle-line ri-32px text-white"></i>
                            </div>
                        </div>
                        
                        <!-- Refund Title -->
                        <h4 class="fw-bold text-primary mb-3" id="confirmationModalLabel">Process Refund</h4>
                        
                        <!-- Refund Process Message -->
                        <div class="refund-message mb-4">
                            <p class="text-dark mb-3 fs-6">
                                <strong>Tour ID:</strong> <span class="text-primary">#${getTourIdFromButton()}</span>
                            </p>
                            <p class="text-muted mb-3">
                                You are about to process a refund for this tour booking. 
                                This will update the tour status and initiate the refund process.
                            </p>
                            <div class="alert alert-info border-0" style="background-color: #e3f2fd;">
                                <i class="ri-information-line me-2 text-info"></i>
                                <strong>Note:</strong> This action will mark the tour as "Refunded" and cannot be undone.
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-flex gap-3 justify-content-center">
                            <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-dismiss="modal">
                                <i class="ri-close-line me-2"></i>Cancel
                            </button>
                            <button type="button" class="btn btn-primary px-4 py-2" id="confirmButton">
                                <i class="ri-check-line me-2"></i>Process Refund
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#confirmationModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Add custom CSS for enhanced styling
    if (!$('#confirmationModalStyles').length) {
        $('head').append(`
            <style id="confirmationModalStyles">
                .refund-icon-wrapper {
                    position: relative;
                    display: inline-block;
                }
                
                .refund-circle {
                    width: 80px;
                    height: 80px;
                    background: linear-gradient(135deg, #007bff, #0056b3);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    box-shadow: 0 8px 25px rgba(0, 123, 255, 0.3);
                    animation: refundPulse 0.6s ease-out;
                }
                
                @keyframes refundPulse {
                    0% { transform: scale(0.8); opacity: 0; }
                    50% { transform: scale(1.1); }
                    100% { transform: scale(1); opacity: 1; }
                }
                
                .refund-circle i {
                    font-size: 2.5rem;
                    animation: iconAppear 0.8s ease-out 0.3s both;
                }
                
                @keyframes iconAppear {
                    0% { transform: scale(0) rotate(-45deg); opacity: 0; }
                    100% { transform: scale(1) rotate(0deg); opacity: 1; }
                }
                
                .refund-message {
                    max-width: 400px;
                    margin: 0 auto;
                }
                
                #confirmationModal .modal-content {
                    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                }
                
                #confirmationModal .modal-dialog {
                    max-width: 500px;
                }
                
                #confirmationModal .btn {
                    border-radius: 8px;
                    font-weight: 500;
                    transition: all 0.3s ease;
                }
                
                #confirmationModal .btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                }
                
                #confirmationModal .alert {
                    border-radius: 8px;
                    font-size: 0.9rem;
                }
            </style>
        `);
    }
    
    // Show modal
    $('#confirmationModal').modal('show');
    
    // Handle confirm button click
    $('#confirmButton').off('click').on('click', function() {
        $('#confirmationModal').modal('hide');
        if (confirmCallback) {
            confirmCallback();
        }
    });
    
    // Clean up when modal is hidden
    $('#confirmationModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

// Helper function to get tour ID from the button
function getTourIdFromButton() {
    const button = event.target.closest('button');
    if (button && button.onclick) {
        const onclickStr = button.onclick.toString();
        const match = onclickStr.match(/processRefund\((\d+)\)/);
        return match ? match[1] : 'N/A';
    }
    return 'N/A';
}

function showLoadingModal(title, message) {
    const modalHtml = `
        <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-body text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5 class="fw-bold mb-2">${title}</h5>
                        <p class="text-muted mb-0">${message}</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#loadingModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Show modal
    $('#loadingModal').modal('show');
}

function showSuccessModal(title, message, callback) {
    const modalHtml = `
        <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                    <div class="modal-body text-center py-5 px-4">
                        <!-- Success Icon with Animation -->
                        <div class="success-icon-wrapper mb-4">
                            <div class="success-circle">
                                <i class="ri-check-line ri-32px text-white"></i>
                            </div>
                        </div>
                        
                        <!-- Success Title -->
                        <h4 class="fw-bold text-success mb-3" id="successModalLabel">${title}</h4>
                        
                        <!-- Success Message -->
                        <p class="text-muted mb-4 fs-6">${message}</p>
                        
                        <!-- Progress Bar -->
                        <div class="progress-wrapper mb-3">
                            <div class="progress" style="height: 6px; border-radius: 3px;">
                                <div class="progress-bar bg-success" id="successProgressBar" role="progressbar" style="width: 0%; transition: width 0.1s ease;"></div>
                            </div>
                        </div>
                        
                        <!-- Auto-close Timer -->
                        <p class="text-muted small mb-0">
                            <i class="ri-time-line me-1"></i>
                            Auto-closing in <span id="countdownTimer" class="fw-bold text-success">3</span> seconds
                        </p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#successModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Show modal
    $('#successModal').modal('show');
    
    // Add custom CSS for enhanced styling
    if (!$('#successModalStyles').length) {
        $('head').append(`
            <style id="successModalStyles">
                .success-icon-wrapper {
                    position: relative;
                    display: inline-block;
                }
                
                .success-circle {
                    width: 80px;
                    height: 80px;
                    background: linear-gradient(135deg, #28a745, #20c997);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
                    animation: successPulse 0.6s ease-out;
                }
                
                @keyframes successPulse {
                    0% { transform: scale(0.8); opacity: 0; }
                    50% { transform: scale(1.1); }
                    100% { transform: scale(1); opacity: 1; }
                }
                
                .success-circle i {
                    font-size: 2.5rem;
                    animation: checkmarkAppear 0.8s ease-out 0.3s both;
                }
                
                @keyframes checkmarkAppear {
                    0% { transform: scale(0) rotate(-45deg); opacity: 0; }
                    100% { transform: scale(1) rotate(0deg); opacity: 1; }
                }
                
                .progress-wrapper {
                    max-width: 300px;
                    margin: 0 auto;
                }
                
                .progress {
                    background-color: #e9ecef;
                    overflow: visible;
                }
                
                .progress-bar {
                    background: linear-gradient(90deg, #28a745, #20c997);
                    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
                }
                
                #successModal .modal-content {
                    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                }
                
                #successModal .modal-dialog {
                    max-width: 450px;
                }
            </style>
        `);
    }
    
    // Start countdown timer and progress bar
    let countdown = 3;
    const countdownElement = document.getElementById('countdownTimer');
    const progressBar = document.getElementById('successProgressBar');
    
    const timer = setInterval(() => {
        countdown--;
        if (countdownElement) countdownElement.textContent = countdown;
        if (progressBar) progressBar.style.width = ((3 - countdown) / 3 * 100) + '%';
        
        if (countdown <= 0) {
            clearInterval(timer);
            // Auto-close modal
            $('#successModal').modal('hide');
            // Wait for modal to close, then refresh page
            setTimeout(() => {
                if (callback) {
                    callback();
                }
            }, 300);
        }
    }, 1000);
    
    // Clean up when modal is hidden
    $('#successModal').on('hidden.bs.modal', function() {
        clearInterval(timer);
        $(this).remove();
    });
}

function showErrorModal(title, message) {
    const modalHtml = `
        <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-0 pb-2">
                        <div class="d-flex align-items-center">
                            <div class="icon-wrapper me-3">
                                <i class="ri-error-warning-line ri-24px text-danger"></i>
                            </div>
                            <h5 class="modal-title fw-bold text-danger" id="errorModalLabel">${title}</h5>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <p class="mb-0">${message}</p>
                    </div>
                    <div class="modal-footer border-0 pt-2">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#errorModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Show modal
    $('#errorModal').modal('show');
    
    // Clean up when modal is hidden
    $('#errorModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

function hideModal() {
    // Hide all custom modals
    $('#loadingModal, #confirmationModal, #successModal, #errorModal').modal('hide');
}
</script>
@endsection

@extends('layouts.datatablejs')