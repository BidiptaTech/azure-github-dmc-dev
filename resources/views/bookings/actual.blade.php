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
                {{ $tours->total() }} Actual
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
                            <h5 class="card-title mb-1">{{ $tours->total() }}</h5>
                            <p class="text-muted mb-0">Total Actual</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-success rounded">
                                <i class="ri-check-circle-line ri-24px"></i>
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
                                foreach($tours as $tour) {
                                    if (!empty($tour->parsed_payment_details)) {
                                        foreach($tour->parsed_payment_details as $payment) {
                                            $totalRevenue += floatval($payment['amount'] ?? 0);
                                        }
                                    }
                                }
                            @endphp
                            <h5 class="card-title mb-1">${{ number_format($totalRevenue) }}</h5>
                            <p class="text-muted mb-0">Total Revenue</p>
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
                            <h5 class="card-title mb-1">{{ $tours->where('check_in_time', '<', now())->where('check_out_time', '>', now())->count() }}</h5>
                            <p class="text-muted mb-0">Currently Active</p>
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
                            <h5 class="card-title mb-1">{{ $tours->where('check_out_time', '<', now())->count() }}</h5>
                            <p class="text-muted mb-0">Completed</p>
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

    <!-- Tours Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Actual Bookings List</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-success" onclick="requestFeedback()">
                    <i class="ri-star-line me-1"></i> Request Feedback
                </button>
                <button class="btn btn-sm btn-outline-info" onclick="generateReports()">
                    <i class="ri-file-chart-line me-1"></i> Generate Reports
                </button>
                <button class="btn btn-sm btn-outline-primary" onclick="exportData()">
                    <i class="ri-download-line me-1"></i> Export
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter Options -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search by Tour ID, Display ID...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Currently Active</option>
                        <option value="completed">Completed</option>
                        <option value="upcoming">Upcoming</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="paymentFilter">
                        <option value="">All Payments</option>
                        <option value="cash">Cash Payments</option>
                        <option value="card">Card Payments</option>
                        <option value="bank">Bank Transfer</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                        <i class="ri-refresh-line me-1"></i> Reset Filters
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="datatables-basic table table-bordered" id="toursTable">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>#</th>
                            <th>Tour Details</th>
                            <th>Destination</th>
                            <th>Guests</th>
                            <th>Agent</th>
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
                            <td>
                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $tour->tour_id }}">
                            </td>
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
                                                <i class="ri-eye-line"></i> View Details
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
                            <td>
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
                                        {{-- <li>
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
                                        </li> --}}
                                    </ul>
                                </div>
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
                                        ${payment.payment_date || 'N/A'}
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

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('paymentFilter').value = '';
    filterTable();
}

function filterTable() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const paymentFilter = document.getElementById('paymentFilter').value;
    
    const rows = document.querySelectorAll('#toursTable tbody tr');
    
    rows.forEach(row => {
        if (row.cells.length === 1) return; // Skip empty state row
        
        let show = true;
        
        // Search filter
        if (searchTerm) {
            const tourDetails = row.cells[2].textContent.toLowerCase(); // Tour Details column
            const destination = row.cells[3].textContent.toLowerCase(); // Destination column
            const agent = row.cells[5].textContent.toLowerCase(); // Agent column
            if (!tourDetails.includes(searchTerm) && !destination.includes(searchTerm) && !agent.includes(searchTerm)) {
                show = false;
            }
        }
        
        // Status filter
        if (statusFilter) {
            const statusBadge = row.cells[8].querySelector('.badge'); // Status column (0-based: checkbox, #, tour, dest, guests, agent, travel, payment, status, actions)
            const statusText = statusBadge ? statusBadge.textContent.toLowerCase() : '';
            
            if (statusFilter === 'active' && !statusText.includes('active')) show = false;
            if (statusFilter === 'completed' && !statusText.includes('completed')) show = false;
            if (statusFilter === 'upcoming' && !statusText.includes('upcoming')) show = false;
        }
        
        // Payment filter
        if (paymentFilter) {
            const paymentBadges = row.cells[7].querySelectorAll('.badge'); // Payment Details column
            let hasPaymentType = false;
            paymentBadges.forEach(badge => {
                if (badge.textContent.toLowerCase().includes(paymentFilter)) {
                    hasPaymentType = true;
                }
            });
            if (!hasPaymentType) show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

function exportData() {
    console.log('Exporting actual bookings data...');
}

// Initialize filters
document.getElementById('searchInput').addEventListener('input', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);
document.getElementById('paymentFilter').addEventListener('change', filterTable);

// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
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

@extends('layouts.datatablejs')

