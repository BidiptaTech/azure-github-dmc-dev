@extends('layouts.layout')
@section('title', 'Package Bookings')
@extends('layouts.datatablecss')
@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection
@section('content')

<!-- Add SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<!-- Add SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

<style>
    .select2-container .select2-selection--single {
        height: 100% !important;
        line-height: 100% !important;
        padding: 8px 12px;
    }
    .select2-container .select2-results__option {
        padding: 12px 10px;
    }

    .booking-status {
        padding: 6px 14px;
        font-size: 10px;
        font-weight: bold;
        border-radius: 8px;
        display: inline-block;
        text-shadow: 1px 1px 2px rgba(253, 245, 245, 0.722);
        transition: all 0.3s ease-in-out;
        box-shadow: 2px 4px 6px rgba(0, 0, 0, 0.15);
    }

    .status-confirmed {
        background-color: #a3eea3 !important;
        color: #1b5e20 !important;
        box-shadow: 0px 0px 10px rgba(76, 175, 80, 0.5);
    }

    .status-definite {
        background-color: #4caf50 !important;
        color: #ffffff !important;
        box-shadow: 0px 0px 10px rgba(76, 175, 80, 0.5);
    }

    .status-actual {
        background-color: #2196f3 !important;
        color: #ffffff !important;
        box-shadow: 0px 0px 10px rgba(33, 150, 243, 0.5);
    }

    .status-cancelled {
        background-color: #e5a6ab !important;
        color: #a71d2a !important;
        box-shadow: 0px 0px 10px rgba(220, 53, 69, 0.5);
    }

    .status-refund-pending {
        background-color: #ffc107 !important;
        color: #000 !important;
        box-shadow: 0px 0px 10px rgba(255, 193, 7, 0.5);
    }

    .status-cancel-confirmed {
        background-color: #dc3545 !important;
        color: #fff !important;
        box-shadow: 0px 0px 10px rgba(220, 53, 69, 0.5);
    }

    .status-refunded {
        background-color: #81d334 !important;
        color: #141414 !important;
        box-shadow: 0px 0px 10px rgba(108, 117, 125, 0.5);
    }

    .status-complete {
        background-color: #28a745 !important;
        color: #ffffff !important;
        box-shadow: 0px 0px 10px rgba(40, 167, 69, 0.5);
    }

    .itinerary-day {
        background-color: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 15px;
        padding: 15px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .itinerary-day-header {
        background-color: #e9ecef;
        border-radius: 8px 8px 0 0;
        padding: 10px 15px;
        margin: -15px -15px 15px -15px;
    }

    .service-item {
        background-color: #ffffff;
        border-left: 4px solid #007bff;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .service-hotel {
        border-left-color: #28a745;
    }

    .service-attraction {
        border-left-color: #fd7e14;
    }

    .service-guide {
        border-left-color: #6f42c1;
    }

    .service-restaurant {
        border-left-color: #dc3545;
    }

    .service-transport {
        border-left-color: #17a2b8;
    }

    .service-icon {
        margin-right: 8px;
    }

    .modal-xl {
        max-width: 95%;
    }

    /* Payment amount input styling */
    .form-control.is-valid {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }

    .form-control.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .payment-info-text {
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    /* Warning message styling */
    .text-warning small {
        font-weight: 500;
    }
    
    /* SweetAlert z-index fix to appear above modals */
    .swal-z-index {
        z-index: 9999 !important;
    }
    
    .swal2-container {
        z-index: 9999 !important;
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Package Booking List</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
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
                <table class="datatables-basic table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Booking ID</th>
                            <th>Travel Dates</th>
                            <th>Duration</th>
                            <th>Pax</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Agent Name</th>
                            <th>Action</th>
                            @if(in_array(auth()->user()->role_id, [11, 33, 37, 38, 128, 131, 132, 134, 135, 137, 138]))
                                <th>Add Payment</th>
                            @elseif(auth()->user()->role_id == 36 || auth()->user()->role_id == 129 || auth()->user()->role_id == 131 || auth()->user()->role_id == 133 || auth()->user()->role_id == 134 || auth()->user()->role_id == 136 || auth()->user()->role_id == 137 || auth()->user()->role_id == 138 || auth()->user()->role_id == 126 || auth()->user()->role_id == 127)
                                <th>Confirm Payment </th>
                            @endif
                            <th>Created At</th>
                            <th>Auto Cancel Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($bookings) && count($bookings) > 0)
                            @foreach($bookings as $key => $booking)
                            @php
                                // Check if booking_details is already an array or needs to be decoded
                                $bookingDetails = is_array($booking->booking_details) ? $booking->booking_details : json_decode($booking->booking_details, true);
                                
                                // Calculate travel dates range
                                $travelDates = '';
                                if (!empty($bookingDetails['itinerary'])) {
                                    $firstDay = reset($bookingDetails['itinerary']);
                                    $lastDay = end($bookingDetails['itinerary']);
                                    
                                    if (isset($firstDay['date']) && isset($lastDay['date'])) {
                                        $travelDates = $firstDay['date'] . ' - ' . $lastDay['date'];
                                    }
                                }
                                
                                // Calculate duration
                                $duration = !empty($bookingDetails['itinerary']) ? count($bookingDetails['itinerary']) : 0;
                                
                                // Get pax info
                                $adultCount = $bookingDetails['adult_count'] ?? 0;
                                $childCount = $bookingDetails['child_count'] ?? 0;
                                $totalPax = $adultCount + $childCount;
                                
                                // Get price info (compute tax-inclusive total)
                                $totalPrice = $bookingDetails['total_price'] ?? 0;
                                $currency = $bookingDetails['currency'] ?? 'SGD';
                                $personsForList = $totalPax;
                                $daysForList = $duration ?: 1;
                                $bookingTaxesForList = is_array($booking->taxes) ? $booking->taxes : (is_string($booking->taxes) ? json_decode($booking->taxes, true) : []);
                                $taxResultForList = \App\Helpers\TaxHelper::calculateTourTaxes($totalPrice, $bookingTaxesForList, $personsForList, $daysForList);
                                $totalTaxForList = is_array($taxResultForList) ? ($taxResultForList['total_tax'] ?? 0) : 0;
                                $totalPriceInclTaxForList = $totalPrice + $totalTaxForList;
                            @endphp
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary rounded-pill px-3 py-2">
                                        {{ $booking->booking_id }}
                                    </span>
                                </td>                                
                                <td>{{ $travelDates }}</td>
                                <td>{{ $duration }} days</td>
                                <td>
                                    <span class="badge bg-primary">{{ $totalPax }} Pax</span>
                                    @if($adultCount > 0)
                                        <span class="badge bg-info">{{ $adultCount }} Adults</span>
                                    @endif
                                    @if($childCount > 0)
                                        <span class="badge bg-warning">{{ $childCount }} Children</span>
                                    @endif
                                </td>
                                <td> SGD {{ number_format($totalPriceInclTaxForList, 2) }}</td>
                                <td>
                                    @php
                                        $statusClass = '';
                                        switch($booking->status) {
                                            case '1':
                                                $statusClass = 'status-confirmed';
                                                break;
                                            case '2':
                                                $statusClass = 'status-definite';
                                                break;
                                            case '3':
                                                $statusClass = 'status-actual';
                                                break;
                                            case '4':
                                                $statusClass = 'status-cancelled';
                                                break;
                                            case '5':
                                                $statusClass = 'status-refund-pending';
                                                break;
                                            case '6':
                                                $statusClass = 'status-refunded';
                                                break;
                                            case '7':
                                                $statusClass = 'status-cancel-confirmed';
                                                break;
                                            case '8':
                                                $statusClass = 'status-complete';
                                                break;
                                            default:
                                                $statusClass = 'status-confirmed';
                                        }
                                    @endphp
                                    <span class="booking-status {{ $statusClass }}">
                                        @if($booking->status == '1')
                                            Confirmed
                                        @elseif($booking->status == '2')
                                            Definite
                                        @elseif($booking->status == '3')
                                            Actual
                                        @elseif($booking->status == '4')
                                            Cancelled
                                        @elseif($booking->status == '5')
                                            Refund - Pending
                                        @elseif($booking->status == '6')
                                            Refunded
                                        @elseif($booking->status == '7')
                                            Cancel - Confirmed
                                        @elseif($booking->status == '8')
                                            Complete
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    @if($booking->agent)
                                        <span class="badge bg-info">{{ $booking->agent->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                
                                <td style="display: inline-block; white-space: nowrap;">
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewBookingModal{{ $booking->id }}">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    
                                    @if(in_array(auth()->user()->role_id, [33,34, 37, 38, 124,125, 128, 129, 130,132,133, 134, 135, 136, 137,138]) && in_array($booking->status, ['1', '2']))
                                        <button type="button" class="btn btn-sm btn-danger ms-1" data-booking-id="{{ $booking->booking_id }}">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    @endif
                                    
                                    @if(in_array(auth()->user()->role_id, [36, 126, 127, 129, 131, 133, 134, 136, 137, 138]) && $booking->status == '5')
                                        <button type="button" class="btn btn-sm btn-warning ms-1" data-booking-id="{{ $booking->booking_id }}">
                                            <i class="fas fa-money-bill-wave"></i> Refund
                                        </button>
                                    @endif
                                </td>

                                @if(auth()->user()->role_id == 11 || auth()->user()->role_id == 33 || auth()->user()->role_id == 37 || auth()->user()->role_id == 38 || auth()->user()->role_id == 128 || auth()->user()->role_id == 129 || auth()->user()->role_id == 130 || auth()->user()->role_id == 134 || auth()->user()->role_id == 135 || auth()->user()->role_id == 136 || auth()->user()->role_id == 138)
                                    @php
                                        // Calculate payment totals
                                        $paidAmount = 0;
                                        $packageTotal = 0;
                                        $hasPendingPayment = false;
                                        
                                        // Get package total from booking_details
                                        if ($booking->booking_details) {
                                            $bookingDetails = is_array($booking->booking_details) ? $booking->booking_details : json_decode($booking->booking_details, true);
                                            $packageTotal = $bookingDetails['total_price'] ?? 0;
                                        }
                                        
                                        // Calculate paid amount and check for pending payments
                                        if ($booking->payment_details) {
                                            $paymentDetails = is_array($booking->payment_details) ? $booking->payment_details : (is_string($booking->payment_details) ? json_decode($booking->payment_details, true) : []);
                                            if ($paymentDetails) {
                                                foreach ($paymentDetails as $payment) {
                                                    if (isset($payment['status']) && $payment['status'] == 1) {
                                                        $paidAmount += $payment['payment_amount'];
                                                    } elseif (!isset($payment['status']) || $payment['status'] == 0) {
                                                        $hasPendingPayment = true; // There's a pending payment
                                                    }
                                                }
                                            }
                                        }
                                        $isPaymentComplete = $paidAmount >= $packageTotal && $packageTotal > 0;
                                    @endphp
                                    
                                    <td>
                                        <div style="margin-top: 5px;">
                                            @if($booking->status == '7')
                                                {{-- For Cancel - Confirmed bookings, hide payment options --}}
                                                <span class="text-muted">-</span>
                                            @elseif(in_array($booking->status, ['5', '6']))
                                                {{-- For refunded bookings, only show payment history --}}
                                                @if($booking->payment_details)
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#paymentHistoryModal{{ $booking->id }}">
                                                        <i class="fas fa-history"></i> Payment History
                                                    </button>
                                                @else
                                                    <span class="text-muted">No payment history</span>
                                                @endif
                                            @else
                                                {{-- For active bookings, show normal payment options --}}
                                                @if($isPaymentComplete || ($packageTotal - $paidAmount) <= 0)
                                                    <span class="badge bg-success">Payment Complete</span>
                                                @elseif($hasPendingPayment)
                                                    <span class="badge bg-warning">Pending Verification</span>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#addPaymentModal{{ $booking->id }}">
                                                        <i class="fas fa-plus"></i> Add Payment
                                                    </button>
                                                    @if($packageTotal > 0)
                                                        <small class="text-muted d-block">Due: ${{ number_format($packageTotal - $paidAmount, 2) }}</small>
                                                    @endif
                                                @endif
                                                
                                                @if($booking->payment_details)
                                                    <button type="button" class="btn btn-sm btn-outline-primary ms-1" data-bs-toggle="modal" data-bs-target="#paymentHistoryModal{{ $booking->id }}">
                                                        <i class="fas fa-history"></i> History
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                @elseif(auth()->user()->role_id == 36 || auth()->user()->role_id == 126 || auth()->user()->role_id == 127 || auth()->user()->role_id == 129 || auth()->user()->role_id == 131 || auth()->user()->role_id == 133 || auth()->user()->role_id == 134 || auth()->user()->role_id == 136 || auth()->user()->role_id == 137 || auth()->user()->role_id == 138)
                                    <td>
                                        @if($booking->status == '7')
                                            {{-- For Cancel - Confirmed bookings, hide payment history --}}
                                            <span class="text-muted">-</span>
                                        @else
                                            {{-- Finance users always need access to history for verification --}}
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#paymentHistoryModal{{ $booking->id }}">
                                                <i class="fas fa-history"></i> History
                                            </button>
                                        @endif
                                    </td>
                                @endif
                                <td>
                                    <div class="d-flex flex-column">
                                        <span>{{ $booking->created_at->format('D,  M d, Y') }}</span>
                                        <small class="text-muted">{{ $booking->created_at->format('h:i A') }}</small>
                                    </div>
                                </td>
                                <td>
                                    @if($booking->auto_cancel_date)
                                        <div class="d-flex flex-column">
                                            <span>{{ \Carbon\Carbon::parse($booking->auto_cancel_date)->format('D,  M d, Y') }}</span>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($booking->auto_cancel_date)->format('h:i A') }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View Booking Modals -->
@if(isset($bookings) && count($bookings) > 0)
    @foreach($bookings as $booking)
    @php
        // Check if booking_details is already an array or needs to be decoded
        $bookingDetails = is_array($booking->booking_details) ? $booking->booking_details : json_decode($booking->booking_details, true);
        
        // Calculate travel dates range
        $travelDates = '';
        if (!empty($bookingDetails['itinerary'])) {
            $firstDay = reset($bookingDetails['itinerary']);
            $lastDay = end($bookingDetails['itinerary']);
            
            if (isset($firstDay['date']) && isset($lastDay['date'])) {
                $travelDates = $firstDay['date'] . ' - ' . $lastDay['date'];
            }
        }
        
        // Calculate duration
        $duration = !empty($bookingDetails['itinerary']) ? count($bookingDetails['itinerary']) : 0;
        
        // Get pax info
        $adultCount = $bookingDetails['adult_count'] ?? 0;
        $childCount = $bookingDetails['child_count'] ?? 0;
        $maleCount = $bookingDetails['male_count'] ?? 0;
        $femaleCount = $bookingDetails['female_count'] ?? 0;
        $totalPax = $adultCount + $childCount;
        
        // Get price info
        $totalPrice = $bookingDetails['total_price'] ?? 0;
        $currency = $bookingDetails['currency'] ?? 'SGD';
    @endphp
    
    <div class="modal fade" id="viewBookingModal{{ $booking->id }}" tabindex="-1" aria-labelledby="viewBookingModalLabel{{ $booking->id }}" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="viewBookingModalLabel{{ $booking->id }}">
                        <i class="fas fa-info-circle me-2"></i>Booking Details - {{ $booking->booking_id }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Booking Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Booking ID</th>
                                            <td>{{ $booking->booking_id }}</td>
                                        </tr>
                                        <tr>
                                            <th>Travel Dates</th>
                                            <td>{{ $travelDates }}</td>
                                        </tr>
                                        <tr>
                                            <th>Duration</th>
                                            <td>{{ $duration }} days</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                @php
                                                    $statusClass = '';
                                                    switch($booking->status) {
                                                        case '1':
                                                            $statusClass = 'bg-success';
                                                            break;
                                                        case '2':
                                                            $statusClass = 'bg-primary';
                                                            break;
                                                        case '3':
                                                            $statusClass = 'bg-info';
                                                            break;
                                                        case '4':
                                                            $statusClass = 'bg-danger';
                                                            break;
                                                        case '5':
                                                            $statusClass = 'bg-warning text-dark';
                                                            break;
                                                        case '6':
                                                            $statusClass = 'bg-secondary';
                                                            break;
                                                        case '7':
                                                            $statusClass = 'bg-danger';
                                                            break;
                                                        case '8':
                                                            $statusClass = 'bg-success';
                                                            break;
                                                        default:
                                                            $statusClass = 'bg-success';
                                                    }
                                                @endphp
                                                <span class="badge {{ $statusClass }}">
                                                    @if($booking->status == '1')
                                                        Confirmed
                                                    @elseif($booking->status == '2')
                                                        Definite
                                                    @elseif($booking->status == '3')
                                                        Actual
                                                    @elseif($booking->status == '4')
                                                        Cancelled
                                                    @elseif($booking->status == '5')
                                                        Refund - Pending
                                                    @elseif($booking->status == '6')
                                                        Refunded
                                                    @elseif($booking->status == '7')
                                                        Cancel - Confirmed
                                                    @elseif($booking->status == '8')
                                                        Complete
                                                    @endif
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-users me-2"></i>Traveler Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Total Travelers</th>
                                            <td>{{ $totalPax }}</td>
                                        </tr>
                                        <tr>
                                            <th>Adults</th>
                                            <td>{{ $adultCount }}</td>
                                        </tr>
                                        <tr>
                                            <th>Children</th>
                                            <td>{{ $childCount }}</td>
                                        </tr>
                                        <tr>
                                            <th>Male</th>
                                            <td>{{ $maleCount }}</td>
                                        </tr>
                                        <tr>
                                            <th>Female</th>
                                            <td>{{ $femaleCount }}</td>
                                        </tr>
                                        <tr>
                                            <th>Total Price</th>
                                            @php
                                                $personsVB = $totalPax;
                                                $daysVB = $duration ?: 1;
                                                $taxesVB = is_array($booking->taxes) ? $booking->taxes : (is_string($booking->taxes) ? json_decode($booking->taxes, true) : []);
                                                $taxResVB = \App\Helpers\TaxHelper::calculateTourTaxes($totalPrice, $taxesVB, $personsVB, $daysVB);
                                                $taxAmtVB = is_array($taxResVB) ? ($taxResVB['total_tax'] ?? 0) : 0;
                                                $totalPriceInclVB = $totalPrice + $taxAmtVB;
                                            @endphp
                                            <td>{{ $currency }} {{ number_format($totalPriceInclVB, 2) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Itinerary</h6>
                        </div>
                        <div class="card-body">
                            @if(!empty($bookingDetails['itinerary']))
                                <div class="accordion" id="itineraryAccordion{{ $booking->id }}">
                                    @foreach($bookingDetails['itinerary'] as $index => $day)
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="heading{{ $booking->id }}_{{ $index }}">
                                                <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $booking->id }}_{{ $index }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $booking->id }}_{{ $index }}">
                                                    <strong>Day {{ $day['day'] }} - {{ $day['date'] }}</strong>
                                                </button>
                                            </h2>
                                            <div id="collapse{{ $booking->id }}_{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $booking->id }}_{{ $index }}" data-bs-parent="#itineraryAccordion{{ $booking->id }}">
                                                <div class="accordion-body">
                                                    @if(!empty($day['services']))
                                                        <div class="row">
                                                            @foreach($day['services'] as $service)
                                                                <div class="col-md-6 mb-3">
                                                                    <div class="service-item service-{{ $service['service_type'] }}">
                                                                        @php
                                                                            $icon = 'question-circle';
                                                                            $serviceTypeLabel = 'Service';
                                                                            
                                                                            switch($service['service_type']) {
                                                                                case 'hotel':
                                                                                    $icon = 'hotel';
                                                                                    $serviceTypeLabel = 'Hotel';
                                                                                    break;
                                                                                case 'attraction':
                                                                                    $icon = 'map-marked-alt';
                                                                                    $serviceTypeLabel = 'Attraction';
                                                                                    break;
                                                                                case 'guide':
                                                                                    $icon = 'user-tie';
                                                                                    $serviceTypeLabel = 'Guide';
                                                                                    break;
                                                                                case 'restaurant':
                                                                                    $icon = 'utensils';
                                                                                    $serviceTypeLabel = 'Restaurant';
                                                                                    break;
                                                                                case 'transport':
                                                                                    $icon = 'bus';
                                                                                    $serviceTypeLabel = 'Transport';
                                                                                    break;
                                                                            }
                                                                        @endphp
                                                                        
                                                                        <div class="d-flex align-items-center mb-2">
                                                                            <span class="badge bg-primary me-2">
                                                                                <i class="fas fa-{{ $icon }}"></i> {{ $serviceTypeLabel }}
                                                                            </span>
                                                                            <h6 class="mb-0">{{ $service['service_name'] }}</h6>
                                                                        </div>
                                                                        
                                                                        @if(!empty($service['details']))
                                                                            <div class="service-details mt-2">
                                                                                <div class="row">
                                                                                    @if(!empty($service['details']['image']))
                                                                                        <div class="col-md-4">
                                                                                            <img src="{{ $service['details']['image'] }}" alt="{{ $service['service_name'] }}" class="img-fluid rounded">
                                                                                        </div>
                                                                                    @endif
                                                                                    <div class="col">
                                                                                        @if(!empty($service['details']['city']))
                                                                                            <p class="mb-1"><strong>City:</strong> {{ $service['details']['city'] }}</p>
                                                                                        @endif
                                                                                        
                                                                                        @if($service['service_type'] === 'guide' && !empty($service['details']['language']))
                                                                                            <p class="mb-1"><strong>Languages:</strong> {{ $service['details']['language'] }}</p>
                                                                                        @endif
                                                                                        
                                                                                        @if($service['service_type'] === 'guide' && !empty($service['details']['experience']))
                                                                                            <p class="mb-1"><strong>Experience:</strong> {{ $service['details']['experience'] }} years</p>
                                                                                        @endif
                                                                                        
                                                                                        @if($service['service_type'] === 'guide' && !empty($service['details']['specialization']))
                                                                                            <p class="mb-1"><strong>Specialization:</strong> {{ $service['details']['specialization'] }}</p>
                                                                                        @endif
                                                                                        
                                                                                        @if($service['service_type'] === 'guide' && !empty($service['details']['rating']))
                                                                                            <p class="mb-1">
                                                                                                <strong>Rating:</strong> 
                                                                                                {{ $service['details']['rating'] }}
                                                                                                <span class="text-warning">
                                                                                                    @for($i = 1; $i <= 5; $i++)
                                                                                                        @if($i <= $service['details']['rating'])
                                                                                                            <i class="fas fa-star"></i>
                                                                                                        @elseif($i - 0.5 <= $service['details']['rating'])
                                                                                                            <i class="fas fa-star-half-alt"></i>
                                                                                                        @else
                                                                                                            <i class="far fa-star"></i>
                                                                                                        @endif
                                                                                                    @endfor
                                                                                                </span>
                                                                                                ({{ $service['details']['reviews'] ?? 0 }} reviews)
                                                                                            </p>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                        
                                                                        @if(!empty($service['entry_port']))
                                                                            <div class="alert alert-info mt-2 mb-0">
                                                                                <i class="fas fa-sign-in-alt me-2"></i> Entry Port: {{ $service['entry_port'] }}
                                                                            </div>
                                                                        @endif
                                                                        
                                                                        @if(!empty($service['exit_port']))
                                                                            <div class="alert alert-info mt-2 mb-0">
                                                                                <i class="fas fa-sign-out-alt me-2"></i> Exit Port: {{ $service['exit_port'] == 1 ? 'Pending' : 'Confirmed' }}
                                                                            </div>
                                                                        @endif
                                                                        
                                                                        @if(!empty($service['attraction_with_transfer']))
                                                                            <div class="alert alert-success mt-2 mb-0">
                                                                                <i class="fas fa-shuttle-van me-2"></i> Includes Transfer
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="alert alert-warning">No services scheduled for this day.</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning">No itinerary information available.</div>
                            @endif
                        </div>
                    </div>

                    @if(!empty($bookingDetails['entry_port_transfer']) || !empty($bookingDetails['exit_port_transfer']))
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-shuttle-van me-2"></i>Port Transfers</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @if(!empty($bookingDetails['entry_port_transfer']))
                                        <div class="col-md-6">
                                            <div class="card border-primary">
                                                <div class="card-header bg-primary text-white">
                                                    <h6 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Entry Port Transfer</h6>
                                                </div>
                                                <div class="card-body">
                                                    <pre class="bg-light p-3 rounded">{{ json_encode($bookingDetails['entry_port_transfer'], JSON_PRETTY_PRINT) == 1 ? 'Included' : 'Not Included' }}</pre>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if(!empty($bookingDetails['exit_port_transfer']))
                                        <div class="col-md-6">
                                            <div class="card border-danger">
                                                <div class="card-header bg-danger text-white">
                                                    <h6 class="mb-0"><i class="fas fa-sign-out-alt me-2"></i>Exit Port Transfer</h6>
                                                </div>
                                                <div class="card-body">
                                                    <pre class="bg-light p-3 rounded">{{ json_encode($bookingDetails['exit_port_transfer'], JSON_PRETTY_PRINT) == 1 ? 'Included' : 'Not Included' }}</pre>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Payment Modal -->
    @if(in_array(auth()->user()->role_id, [11, 33, 37, 38, 128, 131, 132, 134, 135, 137, 138]))
    <div class="modal fade" id="addPaymentModal{{ $booking->id }}" tabindex="-1" aria-labelledby="addPaymentModalLabel{{ $booking->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow-lg rounded">
                <div class="modal-header bg-primary text-white d-flex align-items-center justify-content-start" style="padding: 15px; border-radius: 8px;">
                    <h5 class="modal-title d-flex align-items-center" id="addPaymentModalLabel{{ $booking->id }}" style="margin: 0; font-weight: bold; color: white;">
                        <i class="fas fa-money-bill-wave me-2" style="color: #38ef7d; font-size: 1.4rem;"></i> 
                        <span style="color: white;">Payment Details for Package Booking #{{ $booking->booking_id }}</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="paymentForm{{ $booking->booking_id }}" action="{{ route('package.add-payment', $booking->booking_id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="booking_id" value="{{ $booking->booking_id }}">
                        
                        <!-- Payment Amount -->
                        <div class="mb-4">
                            @php
                                // Calculate due amount (including taxes)
                                $paidAmount = 0;
                                $packageTotal = 0;
                                
                                // Get package total and counts from booking_details
                                if ($booking->booking_details) {
                                    $bookingDetails = is_array($booking->booking_details) ? $booking->booking_details : json_decode($booking->booking_details, true);
                                    $packageTotal = $bookingDetails['total_price'] ?? 0;
                                    $adultCount = $bookingDetails['adult_count'] ?? 0;
                                    $childCount = $bookingDetails['child_count'] ?? 0;
                                } else {
                                    $bookingDetails = [];
                                    $adultCount = 0;
                                    $childCount = 0;
                                }
                                
                                // Calculate paid amount
                                if ($booking->payment_details) {
                                    $paymentDetails = is_array($booking->payment_details) ? $booking->payment_details : (is_string($booking->payment_details) ? json_decode($booking->payment_details, true) : []);
                                    if ($paymentDetails) {
                                        foreach ($paymentDetails as $payment) {
                                            if (isset($payment['status']) && $payment['status'] == 1) {
                                                $paidAmount += $payment['payment_amount'];
                                            }
                                        }
                                    }
                                }
                                
                                // Compute tax using stored booking taxes
                                $persons = (int) $adultCount + (int) $childCount;
                                $days = (!empty($bookingDetails['itinerary']) && is_array($bookingDetails['itinerary'])) ? count($bookingDetails['itinerary']) : 1;
                                $taxes = is_array($booking->taxes) ? $booking->taxes : (is_string($booking->taxes) ? json_decode($booking->taxes, true) : []);
                                $taxResult = \App\Helpers\TaxHelper::calculateTourTaxes($packageTotal, $taxes, $persons, $days);
                                $taxAmount = is_array($taxResult) ? ($taxResult['total_tax'] ?? 0) : 0;
                                $taxBreakdown = is_array($taxResult) ? ($taxResult['breakdown'] ?? []) : [];
                                $finalTotal = $packageTotal + $taxAmount;
                                
                                $dueAmount = $finalTotal - $paidAmount;
                            @endphp
                            
                            <label for="payment_amount{{ $booking->booking_id }}" class="form-label fw-bold d-flex align-items-center">
                                <i class="fas fa-money-bill-wave text-success me-2"></i>Payment Amount
                                <small class="text-muted ms-2">(Max: {{ $currency ?? 'SGD' }} {{ number_format($dueAmount, 2) }})</small>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currency ?? 'SGD' }}</span>
                                <input type="number" 
                                    class="form-control form-control-lg" 
                                    id="payment_amount{{ $booking->booking_id }}" 
                                    name="payment_amount" 
                                    step="0.01" 
                                    min="0.01" 
                                    max="{{ $dueAmount }}"
                                    value="{{ $dueAmount }}"
                                    data-max-amount="{{ $dueAmount }}"
                                    oninput="validateAmount(this, {{ $dueAmount }})"
                                    required>
                            </div>
                            <small class="text-info payment-info-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Total (Excl. Tax): {{ $currency ?? 'SGD' }} {{ number_format($packageTotal, 2) }} |
                                Tax: {{ $currency ?? 'SGD' }} {{ number_format($taxAmount, 2) }} |
                                Total (Incl. Tax): {{ $currency ?? 'SGD' }} {{ number_format($finalTotal, 2) }} |
                                Paid: {{ $currency ?? 'SGD' }} {{ number_format($paidAmount, 2) }} |
                                Due: {{ $currency ?? 'SGD' }} {{ number_format(max($dueAmount, 0), 2) }}
                            </small>
                            @if(!empty($taxBreakdown))
                                <div class="mt-2">
                                    <small class="text-muted d-block mb-1">Tax breakdown:</small>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($taxBreakdown as $taxName => $amount)
                                            <span class="badge bg-secondary">
                                                {{ $taxName }}: {{ $currency ?? 'SGD' }} {{ number_format($amount, 2) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            <div id="amountWarning{{ $booking->booking_id }}" class="text-warning mt-1" style="display: none;">
                                <small><i class="fas fa-exclamation-triangle me-1"></i>Amount adjusted to maximum allowed</small>
                            </div>
                        </div>
                        
                        <!-- Payment Date -->
                        <div class="mb-4">
                            <label for="payment_date{{ $booking->booking_id }}" class="form-label fw-bold">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>Payment Date
                            </label>
                            <input type="date" 
                                class="form-control form-control-lg" 
                                id="payment_date{{ $booking->booking_id }}" 
                                name="payment_date" 
                                value="{{ date('Y-m-d') }}"
                                required>
                        </div>

                        <!-- Payment Type -->
                        <div class="mb-4">
                            <label for="payment_type{{ $booking->booking_id }}" class="form-label fw-bold">
                                <i class="fas fa-credit-card text-primary me-2"></i>Payment Mode
                            </label>
                            <select class="form-select form-control-lg" 
                                id="payment_type{{ $booking->booking_id }}" 
                                name="payment_type" 
                                required>
                                <option value="">Select Payment Mode</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="cheque">Cheque</option>
                                <option value="online">Bank Transfer</option>
                            </select>
                        </div>
                        
                        <!-- Transaction ID -->
                        <div class="mb-4">
                            <label for="transaction_id{{ $booking->booking_id }}" class="form-label fw-bold">
                                <i class="fas fa-hashtag text-primary me-2"></i>Transaction ID
                            </label>
                            <input type="text" class="form-control form-control-lg" id="transaction_id{{ $booking->booking_id }}" name="transaction_id" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between" style="padding: 15px; border-radius: 8px;">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-success" id="savePaymentBtn{{ $booking->booking_id }}" data-booking-id="{{ $booking->booking_id }}">
                        <i class="fas fa-save me-2"></i>Save Payment Details
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endforeach
@endif

<!-- Cancel Booking Modals - Simplified -->
@if(isset($bookings) && count($bookings) > 0)
    @foreach($bookings as $booking)
        @if(in_array(auth()->user()->role_id, [33,34, 37, 38, 124,125, 128, 129, 130,132,133, 134, 135, 136, 137,138]) && in_array($booking->status, ['1', '2']))
        <form id="cancelBookingForm{{ $booking->booking_id }}" action="{{ route('package.cancel-booking', $booking->booking_id) }}" method="POST" style="display: none;">
            @csrf
            <input type="hidden" name="booking_id" value="{{ $booking->booking_id }}">
            <input type="hidden" name="cancel_reason" value="Cancelled by sales head">
        </form>
        @endif
        
        @if(in_array(auth()->user()->role_id, [33, 36, 128, 129, 130, 131, 133, 134, 135, 136, 137, 138]) && $booking->status == '5')
        <form id="processRefundForm{{ $booking->booking_id }}" action="{{ route('package.process-refund', $booking->booking_id) }}" method="POST" style="display: none;">
            @csrf
            <input type="hidden" name="booking_id" value="{{ $booking->booking_id }}">
            <input type="hidden" name="refund_reason" value="Refund processed">
        </form>
        @endif
    @endforeach
@endif

<!-- Payment History Modals -->
@if(isset($bookings))
    @foreach($bookings as $booking)
        @if($booking->payment_details)
            @php
                // Parse inputs
                $paymentDetails = is_array($booking->payment_details) ? $booking->payment_details : (is_string($booking->payment_details) ? json_decode($booking->payment_details, true) : []);
                $bookingDetails = is_array($booking->booking_details) ? $booking->booking_details : json_decode($booking->booking_details, true);
                $TotalPrice = $bookingDetails['total_price'] ?? 0;

                // Compute tax-inclusive total using stored taxes
                $persons = (int)($bookingDetails['adult_count'] ?? 0) + (int)($bookingDetails['child_count'] ?? 0);
                $days = (!empty($bookingDetails['itinerary']) && is_array($bookingDetails['itinerary'])) ? count($bookingDetails['itinerary']) : 1;
                $taxes = is_array($booking->taxes) ? $booking->taxes : (is_string($booking->taxes) ? json_decode($booking->taxes, true) : []);
                $taxResult = \App\Helpers\TaxHelper::calculateTourTaxes($TotalPrice, $taxes, $persons, $days);
                $taxAmount = is_array($taxResult) ? ($taxResult['total_tax'] ?? 0) : 0;
                $finalTotal = $TotalPrice + $taxAmount;

                // Sum paid and compute remaining
                $paidAmount = 0;
                if ($paymentDetails) {
                    foreach ($paymentDetails as $payment) {
                        if (isset($payment['status']) && $payment['status'] == 1) {
                            $paidAmount += $payment['payment_amount'];
                        }
                    }
                }
                $totalAmount = $finalTotal;
                $remainingAmount = $finalTotal - $paidAmount;
            @endphp
            
            <div class="modal fade" id="paymentHistoryModal{{ $booking->id }}" tabindex="-1" aria-labelledby="paymentHistoryModalLabel{{ $booking->id }}" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content shadow-lg rounded">
                        <div class="modal-header bg-primary text-white d-flex align-items-center justify-content-start" style="padding: 15px; border-radius: 8px;">
                            <h5 class="modal-title d-flex align-items-center" id="paymentHistoryModalLabel{{ $booking->id }}" style="margin: 0; font-weight: bold; color: white;">
                                <i class="fas fa-history me-2" style="color: #38ef7d; font-size: 1.4rem;"></i> 
                                <span style="color: white;">Payment History for Tour #{{ $booking->booking_id }}</span>
                            </h5>
                            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                        </div>
                        <div class="modal-body p-4">
                            @if($paymentDetails && count($paymentDetails) > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>PAYMENT DATE</th>
                                                <th>RECORD DATE</th>
                                                <th>PAID AMOUNT</th>
                                                <th>PAYMENT MODE</th>
                                                <th>STATUS</th>
                                                <th>ACTIONS</th>
                                                <th>TRANSACTION ID</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($paymentDetails as $index => $payment)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($payment['payment_date'])->format('M d, Y') }}</td>
                                                    <td>{{ isset($payment['created_at']) ? \Carbon\Carbon::parse($payment['created_at'])->format('M d, Y') : 'N/A' }}</td>
                                                    <td class="text-success fw-bold">${{ number_format($payment['payment_amount'], 2) }}</td>
                                                    <td>
                                                        <span class="badge bg-info">{{ $payment['payment_type'] }}</span>
                                                    </td>
                                                    <td>
                                                        @if(isset($payment['status']))
                                                            @if($payment['status'] == 1)
                                                                <span class="badge bg-success">✔ Verified</span>
                                                            @elseif($payment['status'] == 2)
                                                                <span class="badge bg-danger">✗ Declined</span>
                                                            @elseif($payment['status'] == 0)
                                                                <span class="badge bg-warning">⏳ Pending Approval</span>
                                                            @else
                                                                <span class="badge bg-secondary">Unknown</span>
                                                            @endif
                                                        @else
                                                            <span class="badge bg-warning">⏳ Pending Approval</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(!isset($payment['status']) || $payment['status'] == 0)
                                                            @if(in_array(auth()->user()->role_id, [36, 129, 131, 133, 134, 136, 137, 138, 126, 127]))
                                                                {{-- Finance roles can approve/decline payments --}}
                                                                <div class="btn-group" role="group">
                                                                    <button type="button" class="btn btn-sm btn-success" onclick="approvePayment('{{ $booking->booking_id }}', {{ $index }})" title="Approve Payment">
                                                                        <i class="fas fa-check"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm btn-danger" onclick="declinePayment('{{ $booking->booking_id }}', {{ $index }})" title="Decline Payment">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </div>
                                                            @else
                                                                {{-- Sales roles can only view pending payments --}}
                                                                <span class="badge bg-warning">⏳ Pending Verification</span>
                                                            @endif
                                                        @elseif($payment['status'] == 2 && isset($payment['decline_reason']))
                                                            <small class="text-muted" title="{{ $payment['decline_reason'] }}">
                                                                <i class="fas fa-info-circle"></i> Reason provided
                                                            </small>
                                                        @elseif($payment['status'] == 1)
                                                            <span class="text-success">
                                                                <i class="fas fa-check-circle"></i> Verified
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $payment['transaction_id'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Payment Summary -->
                                <div class="row mt-4">
                                    <div class="col-md-4">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Total Amount</h6>
                                                <h4 class="mb-0">${{ number_format($totalAmount, 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-success text-white">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Paid Amount</h6>
                                                <h4 class="mb-0">${{ number_format($paidAmount, 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-warning text-white">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Remaining Amount</h6>
                                                <h4 class="mb-0">${{ number_format($remainingAmount, 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Payment History</h5>
                                    <p class="text-muted">No payments have been recorded for this booking yet.</p>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endif

@endsection

@section('scripts')
<!-- DataTable JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<!-- DataTables Initialization Script -->
<script>
 $(document).ready(function() {
    // Initialize DataTable with export buttons
    $('.datatables-basic').DataTable({
        responsive: true,
        paging: true,
        lengthChange: true,
        info: true,
        searching: true,
        ordering: true,
        buttons: [
            'copy',
            'csv',
            'excel',
            'pdf',
            'print'
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search...",
        }
    });

    // Custom export button functionality
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

// Payment Modal Functions - Removed duplicate function

// Payment form validation and submission functions
function validateAmount(input, maxAmount) {
    const currentValue = parseFloat(input.value);
    const max = parseFloat(maxAmount);
    
    // Get booking ID from input ID
    const bookingId = input.id.replace('payment_amount', '');
    const warningElement = document.getElementById(`amountWarning${bookingId}`);
    
    if (currentValue > max) {
        input.value = maxAmount;
        
        // Show inline warning message
        if (warningElement) {
            warningElement.style.display = 'block';
            // Hide warning after 3 seconds
            setTimeout(() => {
                warningElement.style.display = 'none';
            }, 3000);
        }
    } else {
        // Hide warning if amount is valid
        if (warningElement) {
            warningElement.style.display = 'none';
        }
    }
    
    // Add visual feedback for valid amounts
    if (currentValue > 0 && currentValue <= max) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    } else if (currentValue > max) {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
    } else {
        input.classList.remove('is-valid', 'is-invalid');
    }
}

function validatePaymentAmountInput(bookingId) {
    const paymentAmount = document.getElementById(`payment_amount${bookingId}`);
    
    if (!paymentAmount) {
        Swal.fire({
            title: 'Validation Error',
            text: 'Payment amount field not found',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return false;
    }
    
    const currentValue = parseFloat(paymentAmount.value);
    const maxAmount = parseFloat(paymentAmount.getAttribute('data-max-amount'));
    
    if (currentValue <= 0) {
        Swal.fire({
            title: 'Validation Error',
            text: 'Payment amount must be greater than zero',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return false;
    }
    
    if (currentValue > maxAmount) {
        Swal.fire({
            title: 'Validation Error',
            text: `Payment amount cannot exceed SGD ${maxAmount.toFixed(2)}`,
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return false;
    }
    
    return true;
}

function submitPaymentForm(bookingId) {
    console.log('Submitting payment form for booking:', bookingId);
    
    if (validatePaymentAmountInput(bookingId)) {
        const form = document.getElementById(`paymentForm${bookingId}`);
        if (form) {
            form.submit();
        } else {
            console.error('Payment form not found for booking ID:', bookingId);
            Swal.fire({
                title: 'Error',
                text: 'Payment form not found. Please refresh the page and try again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    }
}

// Handle save payment button click
$(document).on('click', '[id^="savePaymentBtn"]', function() {
    const bookingId = $(this).data('booking-id');
    console.log('Save payment button clicked for booking:', bookingId);
    
    if (bookingId) {
        submitPaymentForm(bookingId);
    } else {
        console.error('Booking ID not found on save payment button');
        Swal.fire({
            title: 'Error',
            text: 'Booking ID not found. Please refresh the page and try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
});

// Handle cancel booking button click
$(document).ready(function() {
    // Use event delegation to handle clicks on cancel booking buttons
    $(document).on('click', '[data-booking-id]', function(e) {
        e.preventDefault();
        const bookingId = $(this).data('booking-id');
        const buttonText = $(this).text().trim();
        
        // Check if it's a cancel or refund button
        if (buttonText.includes('Cancel')) {
            Swal.fire({
                title: 'Cancel Booking',
                text: 'Are you sure you want to cancel this booking?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, cancel it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Processing...',
                        html: 'Please wait while we cancel the booking.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit the cancel form
                    const form = document.getElementById(`cancelBookingForm${bookingId}`);
                    
                    if (form) {
                        form.submit();
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'Form not found. Please refresh the page and try again.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        } else if (buttonText.includes('Refund')) {
            Swal.fire({
                title: 'Refund',
                text: 'Are you sure you want to process the refund for this booking?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, process refund!',
                cancelButtonText: 'No, cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Processing...',
                        html: 'Please wait while we process the refund.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit the refund form
                    const form = document.getElementById(`processRefundForm${bookingId}`);
                    
                    if (form) {
                        form.submit();
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'Form not found. Please refresh the page and try again.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        }
    });
});

// Handle confirm payment button click using event delegation
$(document).ready(function() {
    // Use event delegation to handle clicks on confirm payment buttons
    // This ensures the event handler works for dynamically created elements (pagination)
    $(document).on('click', '.confirm-payment-btn', function() {
        const bookingId = $(this).data('booking-id');
        
        Swal.fire({
            title: 'Confirm Payment',
            text: 'Are you sure you want to confirm this payment?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, confirm it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Processing...',
                    html: 'Please wait while we confirm the payment.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Send AJAX request to confirm payment
                const confirmPaymentUrl = `{{ route('package.confirm-payment', ['booking_id' => '__BOOKING_ID__']) }}`.replace('__BOOKING_ID__', bookingId);
                $.ajax({
                    url: confirmPaymentUrl,
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Payment has been confirmed successfully.',
                            icon: 'success'
                        }).then(() => {
                            // Reload the page to show updated status
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'An error occurred while confirming the payment.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            title: 'Error!',
                            text: errorMessage,
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });
});

// Payment History Modal Functions
function approvePayment(bookingId, paymentIndex) {
    Swal.fire({
        title: 'Approve Payment',
        text: 'Are you sure you want to approve this payment?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, approve it!',
        customClass: {
            popup: 'swal-z-index'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `{{ url('/package-booking') }}/${bookingId}/approve-payment`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    payment_index: paymentIndex
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message,
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    Swal.fire({
                        title: 'Error!',
                        text: response?.message || 'Failed to approve payment',
                        icon: 'error'
                    });
                }
            });
        }
    });
}

function declinePayment(bookingId, paymentIndex) {
    // Create custom modal HTML
    const modalHtml = `
        <div class="modal fade" id="declineModal" tabindex="-1" aria-labelledby="declineModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="declineModalLabel">Decline Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="declineReason" class="form-label">Please provide a reason for declining this payment:</label>
                            <textarea class="form-control" id="declineReason" rows="4" maxlength="500" 
                                      placeholder="Enter decline reason..." required></textarea>
                            <div class="form-text">Minimum 10 characters required</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDecline">Decline Payment</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#declineModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('declineModal'));
    modal.show();
    
    // Focus on textarea when modal is shown
    $('#declineModal').on('shown.bs.modal', function () {
        $('#declineReason').focus();
    });
    
    // Handle confirm button click
    $('#confirmDecline').on('click', function() {
        const reason = $('#declineReason').val().trim();
        
        if (!reason || reason.length < 10) {
            alert('Please provide a reason (at least 10 characters)');
            $('#declineReason').focus();
            return;
        }
        
        // Disable button to prevent double submission
        $(this).prop('disabled', true).text('Processing...');
        
        $.ajax({
            url: `{{ url('/package-booking') }}/${bookingId}/decline-payment`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                payment_index: paymentIndex,
                decline_reason: reason
            },
            success: function(response) {
                modal.hide();
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message,
                        icon: 'error'
                    });
                }
            },
            error: function(xhr) {
                modal.hide();
                const response = xhr.responseJSON;
                Swal.fire({
                    title: 'Error!',
                    text: response?.message || 'Failed to decline payment',
                    icon: 'error'
                });
            },
            complete: function() {
                // Re-enable button
                $('#confirmDecline').prop('disabled', false).text('Decline Payment');
            }
        });
    });
    
    // Clean up modal when hidden
    $('#declineModal').on('hidden.bs.modal', function () {
        $(this).remove();
    });
}
</script>
@endsection