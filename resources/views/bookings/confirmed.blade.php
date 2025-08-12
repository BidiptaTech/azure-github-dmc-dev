@extends('layouts.layout')
@section('title', 'Confirmed Bookings')
@extends('layouts.datatablecss')

<!-- Date Range Picker CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-2">
                <span class="text-muted fw-light">Bookings /</span> Confirmed Bookings
            </h4>
            <p class="text-muted">Manage confirmed bookings ready for processing</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-success fs-6">
                <i class="ri-check-double-line me-1"></i>
                <span id="rangeCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</span>
                <span id="rangeLabel">{{ date('F') }}</span> Confirmed
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
                            <h5 class="card-title mb-1" id="statConfirmedCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</h5>
                            <p class="text-muted mb-0" id="statConfirmedLabel">{{ date('F') }} Confirmed</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-success rounded">
                                <i class="ri-check-double-line ri-24px"></i>
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
                            <h5 class="card-title mb-1" id="statTodayCount">{{ $tours->where('created_at', '>=', now()->today())->count() }}</h5>
                            <p class="text-muted mb-0">Today's Confirmed</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-success rounded">
                                <i class="ri-calendar-line ri-24px"></i>
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
                            <h5 class="card-title mb-1" id="statAdultsCount">{{ $tours->where('created_at', '>=', now()->startOfMonth())->where('created_at', '<=', now()->endOfMonth())->where('adult', '>', 0)->sum('adult') }}</h5>
                            <p class="text-muted mb-0" id="statAdultsLabel">{{ date('F') }} Adults</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-info rounded">
                                <i class="ri-user-line ri-24px"></i>
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
                            <h5 class="card-title mb-1" id="statChildrenCount">{{ $tours->where('created_at', '>=', now()->startOfMonth())->where('created_at', '<=', now()->endOfMonth())->where('child', '>', 0)->sum('child') }}</h5>
                            <p class="text-muted mb-0" id="statChildrenLabel">{{ date('F') }} Children</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-warning rounded">
                                <i class="ri-user-smile-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {{-- </div> --}}
        {{-- <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">${{ number_format(($tours->where('adult', '>', 0)->sum('adult') + $tours->where('child', '>', 0)->sum('child'))) }}</h5>
                            <p class="text-muted mb-0">Confirmed Revenue</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-warning rounded">
                                <i class="ri-money-dollar-circle-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>

         <!-- Upcoming Tours Alert -->
     {{-- @php
         $upcomingTours = $tours->where('check_in_time', '>=', now())->where('check_in_time', '<=', now()->addDays(7));
         $upcomingCount = $upcomingTours->count();
     @endphp
     @if($upcomingCount > 0)
     <div class="alert alert-info mb-4">
         <div class="d-flex align-items-center">
             <i class="ri-calendar-event-line ri-24px me-3"></i>
             <div>
                 <h6 class="alert-heading mb-1">Upcoming Tours Next Week</h6>
                 <p class="mb-0">{{ $upcomingCount }} {{ $upcomingCount == 1 ? 'on hold booking is' : 'on hold bookings are' }} scheduled to start within the next 7 days.</p>
             </div>
             <button class="btn btn-info ms-auto" onclick="showUpcomingTours()">
                 <i class="ri-eye-line me-1"></i> View All
             </button>
         </div>
     </div>
     @endif --}}

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
                {{-- <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="On Hold">On Hold</option>
                        <option value="Starting Soon">Starting Soon</option>
                        <option value="In Progress">In Progress</option>
                    </select>
                </div> --}}
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
                        <option value="next_week">Next Week</option>
                        <option value="this_month">This Month</option>
                        <option value="next_month">Next Month</option>
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
            <h5 class="mb-0">Confirmed Bookings List</h5>
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
                            <th>Travel Dates</th>
                            <th>Confirmation Date</th>
                            {{-- <th>Status</th> --}}
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tours as $key => $tour)
                        <tr 
                            class="{{ $tour->check_in_time && \Carbon\Carbon::parse($tour->check_in_time)->diffInDays(now(), false) <= 7 && \Carbon\Carbon::parse($tour->check_in_time)->diffInDays(now(), false) >= 0 ? 'table-info' : '' }}"
                            data-updated-at="{{ optional($tour->updated_at)->toDateString() }}"
                            data-created-at="{{ optional($tour->created_at)->toDateString() }}"
                            data-adult="{{ (int)($tour->adult ?? 0) }}"
                            data-child="{{ (int)($tour->child ?? 0) }}"
                        >
                            {{-- <td>
                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $tour->tour_id }}">
                            </td> --}}
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
                                    <span class="fw-medium">{{ $tour->agent_name ?? 'N/A' }}</span>
                                    <small class="text-muted">ID: {{ $tour->agent_id ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    @if($tour->check_in_time)
                                        <small><strong>Check-in:</strong> {{ \Carbon\Carbon::parse($tour->check_in_time)->format('D, M d, Y') }}</small>
                                    @endif
                            
                                    @if($tour->check_out_time)
                                        <small><strong>Check-out:</strong> {{ \Carbon\Carbon::parse($tour->check_out_time)->format('D, M d, Y') }}</small>
                                    @endif
                            
                                    @if($tour->check_in_time)
                                        @php
                                            $checkIn = \Carbon\Carbon::parse($tour->check_in_time);
                                            $daysUntilTravel = floor(now()->floatDiffInDays($checkIn, false)); // Floor to get whole number
                                        @endphp
                            
                                        @if($daysUntilTravel > 0)
                                            <span class="badge bg-primary mt-1">{{ $daysUntilTravel }} days to go</span>
                                        @elseif($daysUntilTravel === 0)
                                            <span class="badge bg-success mt-1">Starting Today</span>
                                        @else
                                            <span class="badge bg-secondary mt-1">Started {{ abs($daysUntilTravel) }} days ago</span>
                                        @endif
                                    @endif
                                </div>
                            </td>                                                       
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $tour->updated_at->format('D, M d, Y') }}</span>
                                    <small class="text-muted">{{ $tour->updated_at->diffForHumans() }}</small>
                                </div>
                            </td>
                            {{-- <td>
                                 @if($tour->check_in_time && \Carbon\Carbon::parse($tour->check_in_time)->diffInDays(now(), false) <= 3 && \Carbon\Carbon::parse($tour->check_in_time)->diffInDays(now(), false) >= 0)
                                     <span class="badge bg-warning">
                                         <i class="ri-time-line me-1"></i>Starting Soon
                                     </span>
                                 @elseif($tour->check_in_time && \Carbon\Carbon::parse($tour->check_in_time)->diffInDays(now(), false) < 0)
                                     <span class="badge bg-danger">
                                         <i class="ri-calendar-event-line me-1"></i>In Progress
                                     </span>
                                 @else
                                     <span class="badge bg-success">
                                         <i class="ri-check-double-line me-1"></i>On Hold
                                     </span>
                                 @endif
                             </td> --}}
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
                                            <a class="dropdown-item text-primary" href="#" onclick="makeDefinite('{{ $tour->tour_id }}')">
                                                <i class="ri-arrow-right-line me-2"></i> Make Definite
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="generateVoucher('{{ $tour->tour_id }}')">
                                                <i class="ri-file-text-line me-2"></i> Generate Voucher
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="sendConfirmation('{{ $tour->tour_id }}')">
                                                <i class="ri-mail-send-line me-2"></i> Send Confirmation
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="sendItinerary('{{ $tour->tour_id }}')">
                                                <i class="ri-map-line me-2"></i> Send Itinerary
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="modifyBooking('{{ $tour->tour_id }}')">
                                                <i class="ri-edit-line me-2"></i> Modify Booking
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="cancelConfirmed('{{ $tour->tour_id }}')">
                                                <i class="ri-close-line me-2"></i> Cancel Booking
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
                            <td colspan="8" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="ri-check-double-line ri-48px text-muted mb-2"></i>
                                    <h6 class="text-muted">No confirmed bookings</h6>
                                    <p class="text-muted mb-0">All bookings are in other stages or there are no confirmed bookings yet.</p>
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
function makeDefinite(tourId) {
    if (confirm('Are you sure you want to make this booking definite? This will move it to the definite bookings section.')) {
        console.log('Making booking definite', tourId);
        // Add AJAX call here
    }
}

function generateVoucher(tourId) {
    console.log('Generating voucher for tour', tourId);
    // Implementation for voucher generation
}

function sendConfirmation(tourId) {
    console.log('Sending confirmation email for tour', tourId);
    // Implementation for sending confirmation
}

function sendItinerary(tourId) {
    console.log('Sending itinerary for tour', tourId);
    // Implementation for sending itinerary
}

function modifyBooking(tourId) {
    console.log('Modifying booking', tourId);
    // Redirect to modification page
}

function cancelConfirmed(tourId) {
    if (confirm('Are you sure you want to cancel this confirmed booking? This may require refund processing.')) {
        console.log('Cancelling confirmed booking', tourId);
    }
}

function bulkMakeDefinite() {
    const selectedTours = document.querySelectorAll('.row-checkbox:checked');
    if (selectedTours.length === 0) {
        alert('Please select at least one booking to make definite.');
        return;
    }
    
    if (confirm(`Are you sure you want to make ${selectedTours.length} bookings definite?`)) {
        console.log('Bulk making definite', selectedTours.length, 'bookings');
    }
}

function generateVouchers() {
    const selectedTours = document.querySelectorAll('.row-checkbox:checked');
    if (selectedTours.length === 0) {
        alert('Please select at least one booking to generate vouchers.');
        return;
    }
    
    console.log('Generating vouchers for', selectedTours.length, 'bookings');
}

function showUpcomingTours() {
    // Filter to show only upcoming tours
    document.getElementById('timeFilter').value = 'this_week';
    filterTable();
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('timeFilter').value = '';
    document.getElementById('destinationFilter').value = '';
    const dr = document.getElementById('dateRange');
    const ds = document.getElementById('dateRangeStart');
    const de = document.getElementById('dateRangeEnd');
    if (dr) dr.value = '';
    if (ds) ds.value = '';
    if (de) de.value = '';
    filterTable();
}

function filterTable() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    // const statusFilter = document.getElementById('statusFilter')?.value || '';
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
        const agent = row.cells[5]?.querySelector('.fw-medium')?.textContent || '';
        const status = row.cells[8]?.querySelector('.badge')?.textContent.toLowerCase() || '';
        const travelDates = row.cells[6]?.textContent.toLowerCase() || '';
        const confirmationDateText = row.cells[7]?.textContent || '';
        const updatedAt = row.getAttribute('data-updated-at');
        
        let show = true;
        
        if (searchTerm && !tourDetails.includes(searchTerm)) {
            show = false;
        }
        
        // if (statusFilter && !status.includes(statusFilter.toLowerCase())) {
        //     show = false;
        // }
        
        if (destinationFilter && destination !== destinationFilter) {
            show = false;
        }
        
        if (agentFilter && agent !== agentFilter) {
            show = false;
        }
        
        // Date range filtering (check both created_at and updated_at)
        if (dateStart && dateEnd && (updatedAt || row.getAttribute('data-created-at'))) {
            const createdAt = row.getAttribute('data-created-at');
            const s = new Date(dateStart + 'T00:00:00');
            const e = new Date(dateEnd + 'T23:59:59');
            let dateInRange = false;
            
            // Check updated_at if available
            if (updatedAt) {
                const updatedDate = new Date(updatedAt + 'T00:00:00');
                if (updatedDate >= s && updatedDate <= e) {
                    dateInRange = true;
                }
            }
            
            // Check created_at if available and updated_at didn't match
            if (!dateInRange && createdAt) {
                const createdDate = new Date(createdAt + 'T00:00:00');
                if (createdDate >= s && createdDate <= e) {
                    dateInRange = true;
                }
            }
            
            if (!dateInRange) {
                show = false;
            }
        }
        
        if (timeFilter) {
             const daysToGoMatch = travelDates.match(/(\d+) days to go/);
             const daysToGo = daysToGoMatch ? parseInt(daysToGoMatch[1]) : null;
             const isStartingToday = travelDates.includes('starting today');
             const isInProgress = travelDates.includes('started') || travelDates.includes('days ago');
             
             if (timeFilter === 'this_week') {
                 // Show tours starting within 7 days or starting today
                 if (!((daysToGo !== null && daysToGo <= 7) || isStartingToday)) {
                     show = false;
                 }
             } else if (timeFilter === 'next_week') {
                 // Show tours starting in 8-14 days
                 if (!(daysToGo !== null && daysToGo >= 8 && daysToGo <= 14)) {
                     show = false;
                 }
             } else if (timeFilter === 'this_month') {
                 // Show tours starting within 30 days
                 if (!((daysToGo !== null && daysToGo <= 30) || isStartingToday)) {
                     show = false;
                 }
             } else if (timeFilter === 'next_month') {
                 // Show tours starting in 31-60 days
                 if (!(daysToGo !== null && daysToGo >= 31 && daysToGo <= 60)) {
                     show = false;
                 }
             }
         }
        
        row.style.display = show ? '' : 'none';
    });

    // Update header/cards counts based on visible rows
    const visibleRows = Array.from(document.querySelectorAll('#toursTable tbody tr')).filter(r => r.style.display !== 'none' && r.cells.length > 1);
    const rangeCount = visibleRows.length;
    const adults = visibleRows.reduce((sum, r) => sum + parseInt(r.getAttribute('data-adult') || '0', 10), 0);
    const children = visibleRows.reduce((sum, r) => sum + parseInt(r.getAttribute('data-child') || '0', 10), 0);
    
    // Count today's bookings from visible rows
    const today = new Date().toISOString().split('T')[0];
    const todayCount = visibleRows.filter(r => {
        const createdAt = r.getAttribute('data-created-at');
        return createdAt === today;
    }).length;

    const countEl = document.getElementById('rangeCount');
    const labelEl = document.getElementById('rangeLabel');
    const statConfirmed = document.getElementById('statConfirmedCount');
    const statConfirmedLabel = document.getElementById('statConfirmedLabel');
    const statAdults = document.getElementById('statAdultsCount');
    const statAdultsLabel = document.getElementById('statAdultsLabel');
    const statChildren = document.getElementById('statChildrenCount');
    const statChildrenLabel = document.getElementById('statChildrenLabel');
    const statToday = document.getElementById('statTodayCount');

    if (countEl) countEl.textContent = rangeCount;
    if (statConfirmed) statConfirmed.textContent = rangeCount;
    if (statAdults) statAdults.textContent = adults;
    if (statChildren) statChildren.textContent = children;
    if (statToday) statToday.textContent = todayCount;

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
        if (statConfirmedLabel) statConfirmedLabel.textContent = `Confirmed - ${label}`;
        if (statAdultsLabel) statAdultsLabel.textContent = `Adults - ${label}`;
        if (statChildrenLabel) statChildrenLabel.textContent = `Childrens - ${label}`;
    } else {
        const month = new Date().toLocaleString('default', { month: 'long' });
        if (labelEl) labelEl.textContent = month;
        if (statConfirmedLabel) statConfirmedLabel.textContent = `${month} Confirmed`;
        if (statAdultsLabel) statAdultsLabel.textContent = `${month} Adults`;
        if (statChildrenLabel) statChildrenLabel.textContent = `${month} Children`;
    }
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    // document.getElementById('statusFilter').value = '';
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
    // const statusFilter = document.getElementById('statusFilter');
    const destinationFilter = document.getElementById('destinationFilter');
    const agentFilter = document.getElementById('agentFilter');
    const timeFilter = document.getElementById('timeFilter');
    const dateRange = document.getElementById('dateRange');
    const dateRangeStart = document.getElementById('dateRangeStart');
    const dateRangeEnd = document.getElementById('dateRangeEnd');
    
    // Add event listeners
    if (searchInput) searchInput.addEventListener('input', filterTable);
    // if (statusFilter) statusFilter.addEventListener('change', filterTable);
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
            //  order: [[7, 'desc']], // Sort by Confirmation Date column (index 7) in descending order
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
                    targets: [8], // Status column (index 8)
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
