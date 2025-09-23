@extends('layouts.layout')
@section('title', 'Cancelled Bookings')
@extends('layouts.datatablecss')

<!-- Date Range Picker CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-2">
                <i class="ri-close-circle-line me-2 text-danger"></i>
                <span class="text-muted fw-light">Bookings /</span> Cancelled Bookings
            </h4>
            <p class="text-muted">Manage cancelled bookings and track cancellation reasons</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-danger fs-6">
                <i class="ri-close-circle-line me-1"></i>
                <span id="rangeCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</span>
                <span id="rangeLabel">{{ date('F') }}</span> Cancelled
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
                            <h5 class="card-title mb-1" id="statCancelledCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</h5>
                            <p class="text-muted mb-0" id="statCancelledLabel">{{ date('F') }} Cancelled</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-danger rounded">
                                <i class="ri-close-circle-line ri-24px"></i>
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
                            <h5 class="card-title mb-1" id="statProspectCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->where('tour_status', 'LIKE', 'Cancel - Prospect')->count() }}</h5>
                            <p class="text-muted mb-0" id="statProspectLabel">{{ date('F') }} Prospect</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-primary rounded">
                                <i class="ri-eye-line ri-24px"></i>
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
                            <h5 class="card-title mb-1" id="statTentativeCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->where('tour_status', 'LIKE', 'Cancel - Tentative')->count() }}</h5>
                            <p class="text-muted mb-0" id="statTentativeLabel">{{ date('F') }} Tentative</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-secondary rounded">
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
                            <h5 class="card-title mb-1" id="statConfirmedCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->where('tour_status', 'LIKE', 'Cancel - Confirmed')->count() }}</h5>
                            <p class="text-muted mb-0" id="statConfirmedLabel">{{ date('F') }} Confirmed</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-success rounded">
                                <i class="ri-checkbox-circle-line ri-24px"></i>
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
                    <label class="form-label">Cancellation Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="Prospect">Cancel - Prospect</option>
                        <option value="Tentative">Cancel - Tentative</option>
                        <option value="New Enquiry">Cancel - New Enquiry</option>
                        <option value="Confirmed">Cancel - Confirmed</option>
                        <option value="Definite">Cancel - Definite</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Destination</label>
                    <select class="form-select" id="destinationFilter">
                        <option value="">All Destinations</option>
                        @foreach($tours->pluck('destination')->unique()->filter() as $destination)
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
                {{-- <div class="col-md-2">
                    <label class="form-label">Time Range</label>
                    <select class="form-select" id="timeFilter">
                        <option value="">All Time</option>
                        <option value="this_week">This Week</option>
                        <option value="last_week">Last Week</option>
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                    </select>
                </div> --}}
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <input type="text" class="form-control" id="dateRange" placeholder="Select date range" readonly>
                    <input type="hidden" id="dateRangeStart">
                    <input type="hidden" id="dateRangeEnd">
                </div>
            </div>
        </div>
    </div>

    <!-- Tours Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Cancelled Bookings List</h5>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-danger btn-sm dropdown-toggle" type="button" id="exportDropdown"
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
            <div class="table-responsive">
                <table class="datatables-basic table table-bordered" id="toursTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tour Details</th>
                            <th>Destination</th>
                            <th>Guests</th>
                            <th>Agent</th>
                            <th>Cancellation Status</th>
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
                            class="table-danger"
                            data-created-at="{{ optional($tour->created_at)->toDateString() }}"
                            data-updated-at="{{ optional($tour->updated_at)->toDateString() }}"
                            data-adult="{{ $tour->adult ?? 0 }}"
                            data-child="{{ $tour->child ?? 0 }}"
                            data-cancellation-status="{{ 
                                str_contains($tour->tour_status, 'Cancel - Definite') ? 'Definite' : 
                                (str_contains($tour->tour_status, 'Cancel - Prospect') ? 'Prospect' : 
                                (str_contains($tour->tour_status, 'Cancel - Tentative') ? 'Tentative' : 
                                (str_contains($tour->tour_status, 'Cancel - New Enquiry') ? 'New Enquiry' : 
                                (str_contains($tour->tour_status, 'Cancel - Confirmed') ? 'Confirmed' : 'Other'))))
                            }}"
                        >
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-danger">{{ $tour->display_id }}</strong>
                                    <small class="text-muted">Tour ID: #{{ $tour->tour_id }}</small>
                                    @if($tour->multi_enq_id)
                                        <small class="text-info">Multi: {{ $tour->multi_enq_id }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ $tour->destination ?? 'N/A' }}</span>
                                    <small class="text-muted">{{ $tour->city ?? 'N/A' }}</small>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex gap-2">
                                    @if($tour->adult > 0)
                                        <span class="badge bg-primary">{{ $tour->adult }} Adults</span>
                                    @endif
                                    @if($tour->child > 0)
                                        <span class="badge bg-warning">{{ $tour->child }} Children</span>
                                    @endif
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
                                @if(str_contains($tour->tour_status, 'Cancel - Prospect') || str_contains($tour->tour_status, 'Cancel-Prospect'))
                                    <span class="badge bg-primary">
                                        <i class="ri-eye-line me-1"></i>Prospect
                                    </span>
                                @elseif(str_contains($tour->tour_status, 'Cancel - Tentative') || str_contains($tour->tour_status, 'Cancel-Tentative'))
                                    <span class="badge bg-secondary">
                                        <i class="ri-time-line me-1"></i>Tentative
                                    </span>
                                @elseif(str_contains($tour->tour_status, 'Cancel - New Enquiry') || str_contains($tour->tour_status, 'Cancel-New Enquiry'))
                                    <span class="badge bg-dark">
                                        <i class="ri-file-list-line me-1"></i>New Enquiry
                                    </span>
                                @elseif(str_contains($tour->tour_status, 'Cancel - Confirmed') || str_contains($tour->tour_status, 'Cancel-Confirmed'))
                                    <span class="badge bg-success">
                                        <i class="ri-checkbox-circle-line me-1"></i>Confirmed
                                    </span>
                                @elseif(str_contains($tour->tour_status, 'Cancel - Definite') || str_contains($tour->tour_status, 'Cancel-Definite'))
                                    <span class="badge bg-danger">
                                        <i class="ri-checkbox-circle-line me-1"></i>Definite
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="ri-close-circle-line me-1"></i>{{ $tour->tour_status }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <small><strong>Cancelled:</strong> {{ \Carbon\Carbon::parse($tour->updated_at)->format('D, M d, Y') }}</small>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($tour->updated_at)->format('h:i A') }}</small>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('bookings.view-tour', Crypt::encrypt($tour->tour_id)) }}" 
                                   class="btn btn-outline-danger btn-sm rounded-pill">
                                    <i class="ri-eye-line"></i> View
                                </a>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $tour->created_at->format('D,  M d, Y') }}</span>
                                    <small class="text-muted">{{ $tour->created_at->format('h:i A') }}</small>
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
                        {{-- <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="ri-close-circle-line ri-48px text-muted mb-2"></i>
                                    <h6 class="text-muted">No cancelled bookings</h6>
                                    <p class="text-muted mb-0">All bookings are active or in other stages.</p>
                                </div>
                            </td>
                        </tr> --}}
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function filterTable() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    const destinationFilter = document.getElementById('destinationFilter')?.value || '';
    const agentFilter = document.getElementById('agentFilter')?.value || '';
    const timeFilter = document.getElementById('timeFilter')?.value || '';
    const dateStart = document.getElementById('dateRangeStart')?.value || '';
    const dateEnd = document.getElementById('dateRangeEnd')?.value || '';
    
    const rows = document.querySelectorAll('#toursTable tbody tr');
    
    rows.forEach(row => {
        if (row.cells.length === 1) return; // Skip empty state row
        
        const tourDetails = row.cells[1]?.textContent.toLowerCase() || '';
        const destination = row.cells[2]?.querySelector('.fw-medium')?.textContent || '';
        const agent = row.cells[4]?.querySelector('.fw-medium')?.textContent || '';
        const status = row.cells[5]?.querySelector('.badge')?.textContent.toLowerCase() || '';
        const cancelledDate = row.cells[6]?.textContent.toLowerCase() || '';
        const createdAt = row.getAttribute('data-created-at');
        const updatedAt = row.getAttribute('data-updated-at');
        
        let show = true;
        
        // Date range filtering (check both created_at and updated_at)
        if (dateStart && dateEnd && (createdAt || updatedAt)) {
            const s = new Date(dateStart + 'T00:00:00');
            const e = new Date(dateEnd + 'T23:59:59');
            let dateInRange = false;
            
            // Check created_at if available
            if (createdAt) {
                const createdDate = new Date(createdAt + 'T00:00:00');
                if (createdDate >= s && createdDate <= e) {
                    dateInRange = true;
                }
            }
            
            // Check updated_at if available and created_at didn't match
            if (!dateInRange && updatedAt) {
                const updatedDate = new Date(updatedAt + 'T00:00:00');
                if (updatedDate >= s && updatedDate <= e) {
                    dateInRange = true;
                }
            }
            
            if (!dateInRange) {
                show = false;
            }
        }
        
        if (searchTerm && !tourDetails.includes(searchTerm)) {
            show = false;
        }
        
        if (statusFilter && !status.includes(statusFilter.toLowerCase())) {
            show = false;
        }
        
        if (destinationFilter && destination !== destinationFilter) {
            show = false;
        }
        
        if (agentFilter && agent !== agentFilter) {
            show = false;
        }
        
        if (timeFilter) {
            const cancelledDateMatch = cancelledDate.match(/(\w+), (\w+) (\d+), (\d+)/);
            if (cancelledDateMatch) {
                const cancelledDateObj = new Date(cancelledDateMatch[0]);
                const now = new Date();
                const diffTime = Math.abs(now - cancelledDateObj);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                
                if (timeFilter === 'this_week' && diffDays > 7) {
                    show = false;
                } else if (timeFilter === 'last_week' && (diffDays <= 7 || diffDays > 14)) {
                    show = false;
                } else if (timeFilter === 'this_month' && diffDays > 30) {
                    show = false;
                } else if (timeFilter === 'last_month' && (diffDays <= 30 || diffDays > 60)) {
                    show = false;
                }
            }
        }
        
        row.style.display = show ? '' : 'none';
    });

    // Update header/cards counts based on visible rows
    const visibleRows = Array.from(document.querySelectorAll('#toursTable tbody tr')).filter(r => r.style.display !== 'none' && r.cells.length > 1);
    const rangeCount = visibleRows.length;
    const prospectCount = visibleRows.filter(r => r.getAttribute('data-cancellation-status') === 'Prospect').length;
    const tentativeCount = visibleRows.filter(r => r.getAttribute('data-cancellation-status') === 'Tentative').length;
    const newEnquiryCount = visibleRows.filter(r => r.getAttribute('data-cancellation-status') === 'New Enquiry').length;
    const confirmedCount = visibleRows.filter(r => r.getAttribute('data-cancellation-status') === 'Confirmed').length;
    const definiteCount = visibleRows.filter(r => r.getAttribute('data-cancellation-status') === 'Definite').length;

    // Update counts and labels
    const countEl = document.getElementById('rangeCount');
    const labelEl = document.getElementById('rangeLabel');
    const statCancelled = document.getElementById('statCancelledCount');
    const statCancelledLabel = document.getElementById('statCancelledLabel');
    const statProspect = document.getElementById('statProspectCount');
    const statProspectLabel = document.getElementById('statProspectLabel');
    const statTentative = document.getElementById('statTentativeCount');
    const statTentativeLabel = document.getElementById('statTentativeLabel');
    const statNewEnquiry = document.getElementById('statNewEnquiryCount');
    const statNewEnquiryLabel = document.getElementById('statNewEnquiryLabel');
    const statConfirmed = document.getElementById('statConfirmedCount');
    const statConfirmedLabel = document.getElementById('statConfirmedLabel');
    const statDefinite = document.getElementById('statDefiniteCount');
    const statDefiniteLabel = document.getElementById('statDefiniteLabel');

    if (countEl) countEl.textContent = rangeCount;
    if (statCancelled) statCancelled.textContent = rangeCount;
    if (statProspect) statProspect.textContent = prospectCount;
    if (statTentative) statTentative.textContent = tentativeCount;
    if (statNewEnquiry) statNewEnquiry.textContent = newEnquiryCount;
    if (statConfirmed) statConfirmed.textContent = confirmedCount;
    if (statDefinite) statDefinite.textContent = definiteCount;

    if (dateStart && dateEnd) {
        const start = new Date(dateStart);
        const end = new Date(dateEnd);
        
        // Format the date range label
        let label;
        if (start.getMonth() === end.getMonth() && start.getFullYear() === end.getFullYear()) {
            // Same month
            if (start.getDate() === 1 && end.getDate() === new Date(end.getFullYear(), end.getMonth() + 1, 0).getDate()) {
                // Full month
                label = start.toLocaleString('default', { month: 'long', year: 'numeric' });
            } else {
                label = `${start.getDate()}-${end.getDate()} ${start.toLocaleString('default', { month: 'short' })}, ${start.getFullYear()}`;
            }
        } else {
            label = `${start.toLocaleString('default', { month: 'short' })} ${start.getDate()} - ${end.toLocaleString('default', { month: 'short' })} ${end.getDate()}, ${end.getFullYear()}`;
        }
        
        if (labelEl) labelEl.textContent = label;
        if (statCancelledLabel) statCancelledLabel.textContent = `Cancelled - ${label}`;
        if (statProspectLabel) statProspectLabel.textContent = `Prospect - ${label}`;
        if (statTentativeLabel) statTentativeLabel.textContent = `Tentative - ${label}`;
        if (statNewEnquiryLabel) statNewEnquiryLabel.textContent = `New Enquiry - ${label}`;
        if (statConfirmedLabel) statConfirmedLabel.textContent = `Confirmed - ${label}`;
        if (statDefiniteLabel) statDefiniteLabel.textContent = `Definite - ${label}`;
    } else {
        const month = new Date().toLocaleString('default', { month: 'long' });
        if (labelEl) labelEl.textContent = month;
        if (statCancelledLabel) statCancelledLabel.textContent = `${month} Cancelled`;
        if (statProspectLabel) statProspectLabel.textContent = `${month} Prospect`;
        if (statTentativeLabel) statTentativeLabel.textContent = `${month} Tentative`;
        if (statNewEnquiryLabel) statNewEnquiryLabel.textContent = `${month} New Enquiry`;
        if (statConfirmedLabel) statConfirmedLabel.textContent = `${month} Confirmed`;
        if (statDefiniteLabel) statDefiniteLabel.textContent = `${month} Definite`;
    }
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('destinationFilter').value = '';
    document.getElementById('agentFilter').value = '';
    document.getElementById('timeFilter').value = '';
    const dr = document.getElementById('dateRange');
    const ds = document.getElementById('dateRangeStart');
    const de = document.getElementById('dateRangeEnd');
    if (dr) dr.value = '';
    if (ds) ds.value = '';
    if (de) de.value = '';
    filterTable();
}

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const destinationFilter = document.getElementById('destinationFilter');
    const agentFilter = document.getElementById('agentFilter');
    const timeFilter = document.getElementById('timeFilter');
    const dateRange = document.getElementById('dateRange');
    const dateRangeStart = document.getElementById('dateRangeStart');
    const dateRangeEnd = document.getElementById('dateRangeEnd');
    
    // Add event listeners
    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (statusFilter) statusFilter.addEventListener('change', filterTable);
    if (destinationFilter) destinationFilter.addEventListener('change', filterTable);
    if (agentFilter) agentFilter.addEventListener('change', filterTable);
    if (timeFilter) timeFilter.addEventListener('change', filterTable);
    // Date range picker will be initialized in scripts section where jQuery is available
    
    // Apply initial filter on page load to show today's data
    filterTable();
});
</script>
@endsection

@section('scripts')
<!-- Date Range Picker JS - Load after jQuery -->
<script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<script>
    // Wait for all scripts to load before initializing
    $(document).ready(function() {
        // Small delay to ensure all scripts are loaded
        setTimeout(function() {
            initializeDateRangePicker();
            initializeDataTable();
        }, 200);
    });
    
    function initializeDateRangePicker() {
        // Initialize date range picker first
        const dateRange = document.getElementById('dateRange');
        const dateRangeStart = document.getElementById('dateRangeStart');
        const dateRangeEnd = document.getElementById('dateRangeEnd');
        
        if (dateRange && typeof moment !== 'undefined' && typeof $.fn.daterangepicker !== 'undefined') {
            // Set default to current month
            const startOfMonth = moment().startOf('month');
            const endOfMonth = moment().endOf('month');
            
            $(dateRange).daterangepicker({
                opens: 'left',
                autoUpdateInput: true,
                maxDate: moment(), // No future dates
                startDate: startOfMonth,
                endDate: endOfMonth,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'MMM DD, YYYY'
                }
            });

            // Set initial values for current month
            $(dateRange).val(startOfMonth.format('MMM DD') + ' - ' + endOfMonth.format('MMM DD, YYYY'));
            if (dateRangeStart) dateRangeStart.value = startOfMonth.format('YYYY-MM-DD');
            if (dateRangeEnd) dateRangeEnd.value = endOfMonth.format('YYYY-MM-DD');

            $(dateRange).on('apply.daterangepicker', function(ev, picker) {
                const start = picker.startDate.clone().startOf('day');
                const end = picker.endDate.clone().endOf('day');
                $(this).val(start.format('MMM DD') + ' - ' + end.format('MMM DD, YYYY'));
                if (dateRangeStart) dateRangeStart.value = start.format('YYYY-MM-DD');
                if (dateRangeEnd) dateRangeEnd.value = end.format('YYYY-MM-DD');
                filterTable();
            });

            $(dateRange).on('cancel.daterangepicker', function() {
                $(this).val('');
                if (dateRangeStart) dateRangeStart.value = '';
                if (dateRangeEnd) dateRangeEnd.value = '';
                filterTable();
            });
            
            // Apply initial filter with current month data
            setTimeout(function() {
                filterTable();
            }, 100);
        } else {
            console.error('Date range picker could not be initialized. Missing dependencies:', {
                dateRange: !!dateRange,
                moment: typeof moment !== 'undefined',
                daterangepicker: typeof $.fn.daterangepicker !== 'undefined',
                jquery: typeof $ !== 'undefined'
            });
            
            // Fallback: still set initial date values for current month
            if (dateRange && typeof moment !== 'undefined') {
                const startOfMonth = moment().startOf('month');
                const endOfMonth = moment().endOf('month');
                if (dateRangeStart) dateRangeStart.value = startOfMonth.format('YYYY-MM-DD');
                if (dateRangeEnd) dateRangeEnd.value = endOfMonth.format('YYYY-MM-DD');
                setTimeout(function() {
                    filterTable();
                }, 100);
            }
        }
    }
    
    function initializeDataTable() {
        // Check if DataTable is already initialized
        if ($.fn.DataTable.isDataTable('.datatables-basic')) {
            $('.datatables-basic').DataTable().destroy();
        }
        
        // Initialize DataTable with export buttons
        var table = $('.datatables-basic').DataTable({
            responsive: true,
            dom: 'lrtip', // Removed 'B' to hide the buttons, keeping l=length, r=processing, t=table, i=info, p=pagination
            buttons: [
                'copy',
                'csv',
                'excel',
                'pdf',
                'print' // Keep buttons for functionality but don't show them
            ],
            searching: false, // Disable built-in searching since we use custom filters
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
            lengthMenu: [10, 25, 50, 100], // Customize number of entries per page
            pageLength: 25,
            // order: [[5, 'desc']], // Sort by Cancelled Date column (index 5) in descending order
            columnDefs: [
                {
                    targets: [6], // Actions column (index 6)
                    orderable: false,
                    searchable: false
                },
                {
                    targets: [3], // Guests column (index 3)
                    orderable: false
                },
                {
                    targets: [4], // Cancellation Status column (index 4)
                    orderable: false
                }
            ],
            initComplete: function() {
                console.log('DataTable initialized successfully');
            }
        });

        // Custom export button functionality (for the dropdown)
        $('#exportCopy').on('click', function() {
            table.button('.buttons-copy').trigger();
        });

        $('#exportCSV').on('click', function() {
            table.button('.buttons-csv').trigger();
        });

        $('#exportExcel').on('click', function() {
            table.button('.buttons-excel').trigger();
        });

        $('#exportPDF').on('click', function() {
            table.button('.buttons-pdf').trigger();
        });

        $('#exportPrint').on('click', function() {
            table.button('.buttons-print').trigger();
        });
    }
</script>
@endsection

@extends('layouts.datatablejs')
