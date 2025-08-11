@extends('layouts.layout')
@section('title', 'Actual Bookings')
@extends('layouts.datatablecss')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-2">
                <span class="text-muted fw-light">Bookings /</span> Actual Bookings
            </h4>
            <p class="text-muted">Manage actual bookings with payment details and execution status</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-success fs-6">
                <i class="ri-check-circle-line me-1"></i>
                {{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }} {{ date('F') }} Actual
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
                            <p class="text-muted mb-0">{{ date('F') }} Actual</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-success rounded">
                                <i class="ri-bar-chart-line ri-24px"></i>
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
                            @php
                                $totalRevenue = 0;
                                $currentMonthTours = $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth());
                                foreach($currentMonthTours as $tour) {
                                    if (!empty($tour->parsed_payment_details)) {
                                        foreach($tour->parsed_payment_details as $payment) {
                                            $totalRevenue += floatval($payment['amount'] ?? 0);
                                        }
                                    }
                                }
                            @endphp
                            <h5 class="card-title mb-1">${{ number_format($totalRevenue) }}</h5>
                            <p class="text-muted mb-0">{{ date('F') }} Revenue</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-primary rounded">
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
                            <h5 class="card-title mb-1">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->where('check_in_time', '<', now())->where('check_out_time', '>', now())->count() }}</h5>
                            <p class="text-muted mb-0">{{ date('F') }} Active</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-warning rounded">
                                <i class="ri-play-circle-line ri-24px"></i>
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
                            <h5 class="card-title mb-1">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->where('check_out_time', '<', now())->count() }}</h5>
                            <p class="text-muted mb-0">{{ date('F') }} Completed</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-info rounded">
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
                    <label class="form-label">Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="Active">Currently Active</option>
                        <option value="Completed">Completed</option>
                        <option value="Upcoming">Upcoming</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Payment Type</label>
                    <select class="form-select" id="paymentFilter">
                        <option value="">All Payments</option>
                        <option value="cash">Cash Payments</option>
                        <option value="card">Card Payments</option>
                        <option value="bank">Bank Transfer</option>
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
                        <option value="next_week">Next Week</option>
                        <option value="this_month">This Month</option>
                        <option value="next_month">Next Month</option>
                    </select>
                </div> --}}
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
            <h5 class="mb-0">Actual Bookings List</h5>
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
                            <th>Guests</th>
                            <th>Agent</th>
                            <th>Services</th>
                            <th>Travel Dates</th>
                            <th>Payment Details</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tours as $key => $tour)
                        @php
                            $isActive = $tour->check_in_time && $tour->check_out_time && 
                                       \Carbon\Carbon::parse($tour->check_in_time)->isPast() && 
                                       \Carbon\Carbon::parse($tour->check_out_time)->isFuture();
                            $isCompleted = $tour->check_out_time && \Carbon\Carbon::parse($tour->check_out_time)->isPast();
                            $totalAmount = 0;
                            $paymentMethods = [];
                            
                            if (!empty($tour->parsed_payment_details)) {
                                foreach($tour->parsed_payment_details as $payment) {
                                    $totalAmount += floatval($payment['amount'] ?? 0);
                                    if (!empty($payment['payment_type']) && !in_array($payment['payment_type'], $paymentMethods)) {
                                        $paymentMethods[] = $payment['payment_type'];
                                    }
                                }
                            }
                        @endphp
                        <tr class="{{ $isActive ? 'table-warning' : ($isCompleted ? 'table-success' : '') }}">
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
                                    <small class="text-muted">Created: {{ \Carbon\Carbon::parse($tour->created_at)->format('D, M d, Y') }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ $tour->destination ?? 'N/A' }}</span>
                                    <small class="text-muted">{{ $tour->country ?? 'N/A' }}</small>
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
                                <div class="d-flex flex-column">
                                    @if($tour->check_in_time)
                                        <small><strong>Start:</strong> {{ \Carbon\Carbon::parse($tour->check_in_time)->format('D, M d, Y') }}</small>
                                    @endif
                                    @if($tour->check_out_time)
                                        <small><strong>End:</strong> {{ \Carbon\Carbon::parse($tour->check_out_time)->format('D, M d, Y') }}</small>
                                    @endif
                                    @if($tour->check_in_time && $tour->check_out_time)
                                        <small class="text-muted">
                                            Duration: {{ \Carbon\Carbon::parse($tour->check_in_time)->diffInDays(\Carbon\Carbon::parse($tour->check_out_time)) + 1 }} days
                                        </small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    @if($totalAmount > 0)
                                        <strong class="text-success">${{ number_format($totalAmount) }}</strong>
                                        <div class="d-flex gap-1 mt-1">
                                            @foreach($paymentMethods as $method)
                                                <span class="badge bg-light text-dark">
                                                    @if($method == 'cash')
                                                        <i class="ri-money-dollar-line"></i> Cash
                                                    @elseif($method == 'card')
                                                        <i class="ri-bank-card-line"></i> Card
                                                    @elseif($method == 'bank')
                                                        <i class="ri-bank-line"></i> Bank
                                                    @else
                                                        {{ ucfirst($method) }}
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                        @if(!empty($tour->parsed_payment_details))
                                            <button class="btn btn-sm btn-outline-info mt-1" onclick="showPaymentDetails('{{ $tour->tour_id }}')">
                                                <i class="ri-eye-line"></i> View
                                            </button>
                                        @endif
                                    @else
                                        <span class="text-muted">No payment details</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($isActive)
                                    <span class="badge bg-warning">
                                        <i class="ri-play-circle-line me-1"></i>Active
                                    </span> 
                                @elseif($isCompleted)
                                    <span class="badge bg-success">
                                        <i class="ri-checkbox-circle-line me-1"></i>Completed
                                    </span>
                                @else
                                    <span class="badge bg-primary">
                                        <i class="ri-calendar-line me-1"></i>Upcoming
                                    </span>
                                @endif
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
                                            <a class="dropdown-item" href="#" onclick="showPaymentDetails('{{ $tour->tour_id }}')">
                                                <i class="ri-money-dollar-circle-line me-2"></i> Payment Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="viewItinerary('{{ $tour->tour_id }}')">
                                                <i class="ri-map-line me-2"></i> View Itinerary
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        @if($isCompleted)
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="requestFeedbackSingle('{{ $tour->tour_id }}')">
                                                <i class="ri-star-line me-2"></i> Request Feedback
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="generateInvoice('{{ $tour->tour_id }}')">
                                                <i class="ri-file-text-line me-2"></i> Generate Invoice
                                            </a>
                                        </li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="downloadDocuments('{{ $tour->tour_id }}')">
                                                <i class="ri-download-line me-2"></i> Download Documents
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="addPayment('{{ $tour->tour_id }}')">
                                                <i class="ri-add-line me-2"></i> Add Payment
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="sendReceipt('{{ $tour->tour_id }}')">
                                                <i class="ri-mail-send-line me-2"></i> Send Receipt
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
                                    <i class="ri-check-circle-line ri-48px text-muted mb-2"></i>
                                    <h6 class="text-muted">No actual bookings</h6>
                                    <p class="text-muted mb-0">All bookings are in other stages or there are no actual bookings yet.</p>
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

<!-- Payment Details Modal -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="paymentDetailsContent">
                <!-- Payment details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printPaymentDetails()">
                    <i class="ri-printer-line me-1"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Create a simple data object for tours
const toursData = {
    @foreach($tours as $tour)
    '{{ $tour->tour_id }}': @json($tour->parsed_payment_details ?? []),
    @endforeach
};

// Make sure the function is globally accessible
window.showPaymentDetails = function(tourId) {
    console.log('showPaymentDetails called with tourId:', tourId);
    console.log('Available tours data:', toursData);
    
    try {
        const paymentDetails = toursData[tourId] || [];
        console.log('Found payment details for tour', tourId, ':', paymentDetails);
        
        let content = '<div class="row">';
        
        if (paymentDetails && paymentDetails.length > 0) {
            paymentDetails.forEach((payment, index) => {
                content += `
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Payment #${index + 1}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <strong>Amount:</strong><br>
                                        <span class="text-success fs-5">$${parseFloat(payment.amount || 0).toLocaleString()}</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <strong>Payment Type:</strong><br>
                                        <span class="badge bg-primary">${payment.payment_type || 'N/A'}</span>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <strong>Date:</strong><br>
                                        ${payment.payment_date
                                            ? new Date(payment.payment_date).toLocaleDateString('en-US', {
                                                weekday: 'short', // D
                                                month: 'short',   // M
                                                day: '2-digit',   // d
                                                year: 'numeric'   // Y
                                            })
                                            : 'N/A'}
                                    </div>
                                    <div class="col-sm-6">
                                        <strong>Status:</strong><br>
                                        <span class="badge ${payment.status == 1 ? 'bg-success' : 'bg-warning'}">
                                            ${payment.status == 1 ? 'Confirmed' : 'Pending'}
                                        </span>
                                    </div>
                                </div>
                                ${payment.transaction_id ? `
                                    <hr>
                                    <div>
                                        <strong>Transaction ID:</strong><br>
                                        <code>${payment.transaction_id}</code>
                                    </div>
                                ` : ''}
                                ${payment.remarks ? `
                                    <hr>
                                    <div>
                                        <strong>Remarks:</strong><br>
                                        <p class="text-muted mb-0">${payment.remarks}</p>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            content += '<div class="col-12 text-center"><p class="text-muted">No payment details available for this tour.</p></div>';
        }
        
        content += '</div>';
        
        // Make sure the modal elements exist
        const modalContent = document.getElementById('paymentDetailsContent');
        const modal = document.getElementById('paymentDetailsModal');
        
        if (modalContent && modal) {
            modalContent.innerHTML = content;
            const bootstrapModal = new bootstrap.Modal(modal);
            bootstrapModal.show();
            console.log('Modal should be visible now');
        } else {
            console.error('Modal elements not found:', {
                modalContent: !!modalContent,
                modal: !!modal
            });
            alert('Error: Payment details modal not found. Please refresh the page.');
        }
    } catch (error) {
        console.error('Error in showPaymentDetails:', error);
        alert('Error loading payment details. Please try again.');
    }
}

function printPaymentDetails() {
    const content = document.getElementById('paymentDetailsContent').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Payment Details</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>@media print { .no-print { display: none; } }</style>
            </head>
            <body>
                <div class="container mt-3">
                    <h4>Payment Details</h4>
                    ${content}
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

function viewItinerary(tourId) {
    console.log('Viewing itinerary for tour', tourId);
    // Implementation for viewing itinerary
}

function requestFeedbackSingle(tourId) {
    console.log('Requesting feedback for tour', tourId);
    // Implementation for requesting feedback
}

function generateInvoice(tourId) {
    console.log('Generating invoice for tour', tourId);
    // Implementation for invoice generation
}

function downloadDocuments(tourId) {
    console.log('Downloading documents for tour', tourId);
    // Implementation for document download
}

function addPayment(tourId) {
    console.log('Adding payment for tour', tourId);
    // Implementation for adding payment
}

function sendReceipt(tourId) {
    console.log('Sending receipt for tour', tourId);
    // Implementation for sending receipt
}

function requestFeedback() {
    const selectedTours = document.querySelectorAll('.row-checkbox:checked');
    if (selectedTours.length === 0) {
        alert('Please select at least one completed booking to request feedback.');
        return;
    }
    
    console.log('Requesting feedback for', selectedTours.length, 'bookings');
}

function generateReports() {
    const selectedTours = document.querySelectorAll('.row-checkbox:checked');
    if (selectedTours.length === 0) {
        alert('Please select at least one booking to generate reports.');
        return;
    }
    
    console.log('Generating reports for', selectedTours.length, 'bookings');
}

function filterTable() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    const paymentFilter = document.getElementById('paymentFilter')?.value || '';
    const destinationFilter = document.getElementById('destinationFilter')?.value || '';
    const agentFilter = document.getElementById('agentFilter')?.value || '';
    const timeFilter = document.getElementById('timeFilter')?.value || '';
    const dateFilter = document.getElementById('dateFilter')?.value || '';
    
    const rows = document.querySelectorAll('#toursTable tbody tr');
    
    rows.forEach(row => {
        if (row.cells.length === 1) return; // Skip empty state row
        
        const tourDetails = row.cells[1]?.textContent.toLowerCase() || '';
        const destination = row.cells[2]?.querySelector('.fw-medium')?.textContent || '';
        const agent = row.cells[4]?.querySelector('.fw-medium')?.textContent || '';
        const status = row.cells[7]?.querySelector('.badge')?.textContent.toLowerCase() || '';
        const travelDates = row.cells[5]?.textContent.toLowerCase() || '';
        const paymentBadges = row.cells[6]?.querySelectorAll('.badge') || [];
        
        let show = true;
        
        // Date filtering - using created date for actual bookings
        if (dateFilter && tourDetails) {
            const selectedDate = new Date(dateFilter);
            
            // Extract the date from tour details - looking for "Created: Mon, Dec 23, 2024" format
            const dateMatch = tourDetails.match(/created:\s*\w+,\s+\w+\s+\d+,\s+\d+/i);
            if (dateMatch) {
                const createdDateText = dateMatch[0].replace(/created:\s*/i, '');
                const createdDate = new Date(createdDateText);
                const createdDateOnly = new Date(createdDate.getFullYear(), createdDate.getMonth(), createdDate.getDate());
                const selectedDateOnly = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate.getDate());
                
                if (createdDateOnly.getTime() !== selectedDateOnly.getTime()) {
                    show = false;
                }
            }
        }
        
        if (searchTerm && !tourDetails.includes(searchTerm)) {
            show = false;
        }
        
        if (statusFilter && !status.includes(statusFilter.toLowerCase())) {
            show = false;
        }
        
        if (paymentFilter) {
            let hasPaymentType = false;
            paymentBadges.forEach(badge => {
                if (badge.textContent.toLowerCase().includes(paymentFilter)) {
                    hasPaymentType = true;
                }
            });
            if (!hasPaymentType) show = false;
        }
        
        if (destinationFilter && destination !== destinationFilter) {
            show = false;
        }
        
        if (agentFilter && agent !== agentFilter) {
            show = false;
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
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('paymentFilter').value = '';
    document.getElementById('destinationFilter').value = '';
    document.getElementById('agentFilter').value = '';
    document.getElementById('timeFilter').value = '';
    // Reset date filter to today's date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('dateFilter').value = today;
    filterTable();
}

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const paymentFilter = document.getElementById('paymentFilter');
    const destinationFilter = document.getElementById('destinationFilter');
    const agentFilter = document.getElementById('agentFilter');
    const timeFilter = document.getElementById('timeFilter');
    const dateFilter = document.getElementById('dateFilter');
    
    // Add event listeners
    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (statusFilter) statusFilter.addEventListener('change', filterTable);
    if (paymentFilter) paymentFilter.addEventListener('change', filterTable);
    if (destinationFilter) destinationFilter.addEventListener('change', filterTable);
    if (agentFilter) agentFilter.addEventListener('change', filterTable);
    if (timeFilter) timeFilter.addEventListener('change', filterTable);
    if (dateFilter) dateFilter.addEventListener('change', filterTable);
    
    // Apply initial filter on page load to show today's data
    filterTable();
});

// Test function to verify modal works
window.testModal = function() {
    const modal = document.getElementById('paymentDetailsModal');
    const modalContent = document.getElementById('paymentDetailsContent');
    
    if (modal && modalContent) {
        modalContent.innerHTML = '<div class="text-center"><h4>Test Modal</h4><p>Modal is working correctly!</p></div>';
        new bootstrap.Modal(modal).show();
        console.log('Test modal opened successfully');
    } else {
        console.error('Modal elements not found');
        alert('Modal elements not found!');
    }
}

// Log available tours data on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded. Tours data available:', Object.keys(toursData).length, 'tours');
    console.log('Modal element exists:', !!document.getElementById('paymentDetailsModal'));
    console.log('Modal content element exists:', !!document.getElementById('paymentDetailsContent'));
});
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
            // order: [[5, 'desc']], // Sort by Travel Dates column (index 5) in descending order
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
                    targets: [6, 7], // Payment Details and Status columns (index 6, 7)
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

