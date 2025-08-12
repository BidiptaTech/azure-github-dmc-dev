@extends('layouts.layout')
@section('title', 'Follow Ups')
@extends('layouts.datatablecss')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-2">
                <span class="text-muted fw-light">Bookings /</span> Follow Ups
            </h4>
            <p class="text-muted">Manage prospect enquiries, tentative bookings and follow up communications</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-info fs-6">
                <i class="ri-phone-line me-1"></i>
                {{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }} {{ date('F') }} Follow Ups
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
                            <h5 class="card-title mb-1">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</h5>
                            <p class="text-muted mb-0">{{ date('F') }} Follow Ups</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-info rounded">
                                <i class="ri-phone-line ri-24px"></i>
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
                            <h5 class="card-title mb-1">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->where('tour_status', 'Prospect')->count() }}</h5>
                            <p class="text-muted mb-0">{{ date('F') }} Prospects</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-primary rounded">
                                <i class="ri-user-search-line ri-24px"></i>
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
                            <h5 class="card-title mb-1">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->where('tour_status', 'Tentative')->count() }}</h5>
                            <p class="text-muted mb-0">{{ date('F') }} Tentative</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-warning rounded">
                                <i class="ri-bookmark-line ri-24px"></i>
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
                            <h5 class="card-title mb-1">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->where('updated_at', '<', now()->subDays(7))->count() }}</h5>
                            <p class="text-muted mb-0">{{ date('F') }} Overdue</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-danger rounded">
                                <i class="ri-alarm-warning-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Priority Actions -->
    {{-- <div class="card mb-4 border-warning">
        <div class="card-header bg-warning-subtle">
            <h5 class="mb-0 text-warning-emphasis">
                <i class="ri-alarm-line me-2"></i>Priority Follow Ups
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                @php
                    $overdueFollowUps = $tours->where('updated_at', '<', now()->subDays(7))->take(3);
                @endphp
                @forelse($overdueFollowUps as $tour)
                <div class="col-md-4 mb-3">
                    <div class="alert alert-warning mb-0">
                        <h6 class="alert-heading">{{ $tour->display_id }}</h6>
                        <p class="mb-2">{{ $tour->destination }}, {{ $tour->city }}</p>
                        <small class="text-muted">Last updated: {{ $tour->updated_at->diffForHumans() }}</small>
                        <div class="mt-2">
                            <button class="btn btn-sm btn-warning" onclick="followUpNow('{{ $tour->tour_id }}')">
                                Follow Up Now
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="text-center">
                        <i class="ri-check-circle-line ri-48px text-success mb-2"></i>
                        <h6 class="text-success">All caught up!</h6>
                        <p class="text-muted mb-0">No overdue follow-ups at the moment.</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div> --}}

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
                    <label class="form-label">Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="Prospect">Prospect</option>
                        <option value="Tentative">Tentative</option>
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
                <div class="col-md-2">
                    <label class="form-label">Follow Up Status</label>
                    <select class="form-select" id="followUpFilter">
                        <option value="">All</option>
                        <option value="overdue">Overdue</option>
                        <option value="due_soon">Due Soon</option>
                        <option value="on_track">On Track</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date Range</label>
                    <input type="date" class="form-control" id="dateFilter" 
                           value="{{ date('Y-m-d') }}" 
                           min="{{ date('Y-m-01') }}" 
                           max="{{ date('Y-m-t') }}">
                </div>
            </div>
        </div>
    </div>

    <!-- Tours Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Follow Up List</h5>
            <div class="d-flex gap-2">
                {{-- <button class="btn btn-sm btn-outline-warning" onclick="scheduleFollowUp()">
                    <i class="ri-calendar-schedule-line me-1"></i> Schedule Follow Up
                </button> --}}
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
        <div class="card-body">
            <div class="table-responsive">
                <table class="datatables-basic table table-bordered" id="toursTable">
                    <thead class="table-light">
                        <tr>
                            {{-- <th>
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th> --}}
                            <th>#</th>
                            <th>Tour Details</th>
                            <th>Destination</th>
                            <th>Services</th>
                            <th>Guests</th>
                            <th>Agent</th>
                            <th>Status</th>
                            <th>Follow Up Status</th>
                            <th>Last Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tours as $key => $tour)
                        <tr class="{{ $tour->updated_at < now()->subDays(7) ? 'table-warning' : '' }}">
                            {{-- <td>
                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $tour->tour_id }}">
                            </td> --}}
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-primary">{{ $tour->display_id }}</strong>
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
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    @php
                                        $svc = [
                                            'hotel' => $tour->hotel ?? 0,
                                            'attraction' => $tour->attraction ?? 0,
                                            'restaurent' => $tour->restaurent ?? 0,
                                            'travel' => $tour->travel ?? 0,
                                            'guide' => $tour->guide ?? 0,
                                            'port' => $tour->port ?? 0,
                                        ];
                                        $icons = [
                                            'hotel' => 'ri-hotel-line',
                                            'attraction' => 'ri-building-2-line',
                                            'restaurent' => 'ri-restaurant-2-line',
                                            'travel' => 'ri-bus-2-line',
                                            'guide' => 'ri-user-voice-line',
                                            'port' => 'ri-ship-line',
                                        ];
                                    @endphp
                                    @foreach($svc as $key=>$count)
                                        @if(intval($count) > 0)
                                            <span class="badge bg-light text-dark border">
                                                <i class="{{ $icons[$key] }} me-1"></i>{{ ucfirst($key) }}: {{ $count }}
                                            </span>
                                        @endif
                                    @endforeach
                                    @if(array_sum(array_map('intval', $svc)) === 0)
                                        <span class="text-muted">No services</span>
                                    @endif
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
                                    @if($tour->agent_name)
                                        <span class="fw-medium">{{ $tour->agent_name }}</span>
                                        <small class="text-muted">ID: {{ $tour->agent_id }}</small>
                                    @else
                                        <span class="text-muted">No agent assigned</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($tour->tour_status == 'Prospect')
                                    <span class="badge bg-info">
                                        <i class="ri-user-search-line me-1"></i>Prospect
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="ri-bookmark-line me-1"></i>Tentative
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($tour->updated_at < now()->subDays(7))
                                    <span class="badge bg-danger">
                                        <i class="ri-alarm-warning-line me-1"></i>Overdue
                                    </span>
                                @elseif($tour->updated_at < now()->subDays(3))
                                    <span class="badge bg-warning">
                                        <i class="ri-time-line me-1"></i>Due Soon
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="ri-check-line me-1"></i>On Track
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $tour->updated_at->format('D, M d, Y') }}</span>
                                    <small class="text-muted">{{ $tour->updated_at->diffForHumans() }}</small>
                                </div>
                            </td>
                            {{-- <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('bookings.view-tour', $tour->tour_id) }}">
                                                <i class="ri-eye-line me-2"></i> View Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="followUpNow('{{ $tour->tour_id }}')">
                                                <i class="ri-phone-line me-2"></i> Follow Up Now
                                            </a>
                                        </li>
                                        @if($tour->tour_status == 'Prospect')
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="convertToTentative('{{ $tour->tour_id }}')">
                                                <i class="ri-bookmark-line me-2"></i> Mark as Tentative
                                            </a>
                                        </li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="convertToConfirmed('{{ $tour->tour_id }}')">
                                                <i class="ri-check-double-line me-2"></i> Mark as Confirmed
                                            </a>
                                        </li>
                                        @if($tour->tour_status == 'Tentative')
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="requestPayment('{{ $tour->tour_id }}')">
                                                <i class="ri-money-dollar-circle-line me-2"></i> Request Payment
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="extendDeadline('{{ $tour->tour_id }}')">
                                                <i class="ri-calendar-schedule-line me-2"></i> Extend Deadline
                                            </a>
                                        </li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="scheduleCallback('{{ $tour->tour_id }}')">
                                                <i class="ri-calendar-schedule-line me-2"></i> Schedule Callback
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="markAsLost('{{ $tour->tour_id }}')">
                                                <i class="ri-close-line me-2"></i> Mark as Lost
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td> --}}
                            <td>
                                <a href="{{ route('bookings.view-tour', $tour->tour_id) }}" 
                                   class="btn btn-outline-primary btn-sm rounded-pill">
                                    <i class="ri-eye-line"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        {{-- <tr>
                            <td colspan="10" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="ri-phone-line ri-48px text-muted mb-2"></i>
                                    <h6 class="text-muted">No follow-ups required</h6>
                                    <p class="text-muted mb-0">All prospects have been contacted or converted.</p>
                                </div>
                            </td>
                        </tr> --}}
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            {{-- <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <p class="text-muted mb-0">
                        Showing {{ $tours->firstItem() ?? 0 }} to {{ $tours->lastItem() ?? 0 }} of {{ $tours->total() }} results
                    </p>
                </div>
                <div>
                    {{ $tours->links() }}
                </div>
            </div> --}}
        </div>
    </div>
</div>

<script>
function followUpNow(tourId) {
    // Implementation for immediate follow up
    console.log('Following up tour', tourId);
    // Open follow up modal or redirect to communication page
}

function convertToTentative(tourId) {
    if (confirm('Are you sure you want to mark this prospect as Tentative?')) {
        console.log('Converting tour', tourId, 'to Tentative status');
    }
}

function convertToConfirmed(tourId) {
    if (confirm('Are you sure you want to mark this prospect as Confirmed?')) {
        console.log('Converting tour', tourId, 'to Confirmed status');
    }
}

function scheduleCallback(tourId) {
    // Implementation for scheduling callback
    console.log('Scheduling callback for tour', tourId);
}

function markAsLost(tourId) {
    if (confirm('Are you sure you want to mark this prospect as lost? This will remove it from follow-ups.')) {
        console.log('Marking tour', tourId, 'as lost');
    }
}

function scheduleFollowUp() {
    const selectedTours = document.querySelectorAll('.row-checkbox:checked');
    if (selectedTours.length === 0) {
        alert('Please select at least one prospect to schedule follow-up.');
        return;
    }
    console.log('Scheduling follow-up for', selectedTours.length, 'prospects');
}

function requestPayment(tourId) {
    console.log('Requesting payment for tour', tourId);
    // Implementation for payment request
}

function extendDeadline(tourId) {
    const newDeadline = prompt('Enter new deadline (YYYY-MM-DD):');
    if (newDeadline) {
        console.log('Extending deadline for tour', tourId, 'to', newDeadline);
    }
}

function exportData() {
    console.log('Exporting follow-up data...');
}

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const destinationFilter = document.getElementById('destinationFilter');
    const agentFilter = document.getElementById('agentFilter');
    const followUpFilter = document.getElementById('followUpFilter');
    const dateFilter = document.getElementById('dateFilter');
    
    // Add event listeners
    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (statusFilter) statusFilter.addEventListener('change', filterTable);
    if (destinationFilter) destinationFilter.addEventListener('change', filterTable);
    if (agentFilter) agentFilter.addEventListener('change', filterTable);
    if (followUpFilter) followUpFilter.addEventListener('change', filterTable);
    if (dateFilter) dateFilter.addEventListener('change', filterTable);
    
    // Select all functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
    
    // Apply initial filter on page load to show today's data
    filterTable();
});

function filterTable() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    const destinationFilter = document.getElementById('destinationFilter')?.value || '';
    const agentFilter = document.getElementById('agentFilter')?.value || '';
    const followUpFilter = document.getElementById('followUpFilter')?.value || '';
    const dateFilter = document.getElementById('dateFilter')?.value || '';
    
    const rows = document.querySelectorAll('#toursTable tbody tr');
    
    rows.forEach(row => {
        if (row.cells.length === 1) return; // Skip empty state row
        
        const tourDetails = row.cells[1]?.textContent.toLowerCase() || '';
        const destination = row.cells[2]?.querySelector('.fw-medium')?.textContent || '';
        const agent = row.cells[5]?.querySelector('.fw-medium')?.textContent || '';
        const status = row.cells[6]?.querySelector('.badge')?.textContent.toLowerCase() || '';
        const followUpStatus = row.cells[7]?.querySelector('.badge')?.textContent.toLowerCase() || '';
        const lastContactText = row.cells[8]?.textContent || '';
        
        let show = true;
        
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
        
        if (followUpFilter) {
            if (followUpFilter === 'overdue' && !followUpStatus.includes('overdue')) {
                show = false;
            } else if (followUpFilter === 'due_soon' && !followUpStatus.includes('due soon')) {
                show = false;
            } else if (followUpFilter === 'on_track' && !followUpStatus.includes('on track')) {
                show = false;
            }
        }
        
        // Date filtering
        if (dateFilter && lastContactText) {
            const selectedDate = new Date(dateFilter);
            
            // Extract the date from "Last Contact" cell - assuming format like "Mon, Dec 23, 2024"
            const dateMatch = lastContactText.match(/\w+,\s+\w+\s+\d+,\s+\d+/);
            if (dateMatch) {
                const contactDate = new Date(dateMatch[0]);
                const contactDateOnly = new Date(contactDate.getFullYear(), contactDate.getMonth(), contactDate.getDate());
                const selectedDateOnly = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate.getDate());
                
                if (contactDateOnly.getTime() !== selectedDateOnly.getTime()) {
                    show = false;
                }
            }
        }
        
        row.style.display = show ? '' : 'none';
    });
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('destinationFilter').value = '';
    document.getElementById('agentFilter').value = '';
    document.getElementById('followUpFilter').value = '';
    // Reset date filter to today's date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('dateFilter').value = today;
    filterTable();
}
</script>
@endsection

@section('scripts')
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<script>
    $(document).ready(function() {
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
            // order: [[7, 'desc']], // Sort by Last Contact column (index 7) in descending order
            columnDefs: [
                {
                    targets: [8], // Actions column (index 8)
                    orderable: false,
                    searchable: false
                },
                {
                    targets: [3], // Guests column (index 3)
                    orderable: false
                },
                {
                    targets: [5, 6], // Status and Follow Up Status columns (index 5, 6)
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
    });
</script>
@endsection

@extends('layouts.datatablejs')
