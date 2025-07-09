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

    .status-pending {
        background-color: #fff3cd !important;
        color: #856404 !important;
        box-shadow: 0px 0px 10px rgba(255, 193, 7, 0.5);
    }

    .status-confirmed {
        background-color: #a3eea3 !important;
        color: #1b5e20 !important;
        box-shadow: 0px 0px 10px rgba(76, 175, 80, 0.5);
    }

    .status-cancelled {
        background-color: #e5a6ab !important;
        color: #a71d2a !important;
        box-shadow: 0px 0px 10px rgba(220, 53, 69, 0.5);
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
                            <th>Action</th>
                            @if(in_array(auth()->user()->role_id, [11, 33]))
                                <th>Add Payment</th>
                            @elseif(auth()->user()->role_id == 36)
                                <th>Confirm Payment </th>
                            @endif
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
                                
                                // Get price info
                                $totalPrice = $bookingDetails['total_price'] ?? 0;
                                $currency = $bookingDetails['currency'] ?? 'SGD';
                            @endphp
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $booking->booking_id }}</td>
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
                                <td> SGD {{ number_format($totalPrice, 2) }}</td>
                                <td>
                                    @php
                                        $statusClass = '';
                                        switch($booking->status) {
                                            case 'pending':
                                                $statusClass = 'status-pending';
                                                break;
                                            case 'confirmed':
                                                $statusClass = 'status-confirmed';
                                                break;
                                            case 'cancelled':
                                                $statusClass = 'status-cancelled';
                                                break;
                                            default:
                                                $statusClass = 'status-pending';
                                        }
                                    @endphp
                                    <span class="booking-status {{ $statusClass }}">
                                        @if($booking->status == '1')
                                            Pending
                                        @elseif($booking->status == '2')
                                            Confirmed
                                        @elseif($booking->status == '3')
                                            On Hold
                                        @elseif($booking->status == '4')
                                            Cancelled
                                        @endif
                                    </span>
                                </td>
                                
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewBookingModal{{ $booking->id }}">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>

                                @if(auth()->user()->role_id == 11 || auth()->user()->role_id == 33)
                                <td>
                                    @if($booking->status == '1')
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#addPaymentModal{{ $booking->id }}">
                                            <i class="fas fa-plus"></i> Add Payment
                                        </button>
                                    
                                    @elseif($booking->status == '2')
                                        <span class="badge bg-success">Payment Done</span>
                                    @elseif($booking->status == '3')
                                        <span class="badge bg-warning">On Hold</span>
                                    @elseif($booking->status == '4')
                                        <span class="badge bg-danger">Cancelled</span>
                                    @endif
                                    </td>
                                @elseif(auth()->user()->role_id == 36)
                                    <td>
                                        @if($booking->status == '3')
                                            <button type="button" class="btn btn-sm btn-info confirm-payment-btn" data-booking-id="{{ $booking->id }}">
                                                <i class="fas fa-check"></i> Confirm Payment
                                            </button>
                                        @elseif($booking->status == '2')
                                            <span class="badge bg-success">Confirmed</span>
                                        @elseif($booking->status == '4')
                                            <span class="badge bg-danger">Cancelled</span>
                                        @else
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </td>
                                @endif
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
                                                            $statusClass = 'bg-warning text-dark';
                                                            break;
                                                        case '2':
                                                            $statusClass = 'bg-success';
                                                            break;
                                                        case '4':
                                                            $statusClass = 'bg-danger';
                                                            break;
                                                        default:
                                                            $statusClass = 'bg-warning text-dark';
                                                    }
                                                @endphp
                                                <span class="badge {{ $statusClass }}">
                                                    @if($booking->status == '1')
                                                        Pending
                                                    @elseif($booking->status == '2')
                                                        Confirmed
                                                    @elseif($booking->status == '3')
                                                        On Hold
                                                    @elseif($booking->status == '4')
                                                        Cancelled
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
                                            <td>{{ $currency }} {{ number_format($totalPrice, 2) }}</td>
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
    @if(in_array(auth()->user()->role_id, [11, 33]))
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
                    <form id="paymentForm{{ $booking->id }}" action="{{ route('package.add-payment', $booking->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                        
                        <!-- Payment Amount -->
                        <div class="mb-4">
                            <label for="payment_amount{{ $booking->id }}" class="form-label fw-bold d-flex align-items-center">
                                <i class="fas fa-money-bill-wave text-success me-2"></i>Payment Amount
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currency ?? 'SGD' }}</span>
                                <input type="number" 
                                    class="form-control form-control-lg" 
                                    id="payment_amount{{ $booking->id }}" 
                                    name="payment_amount" 
                                    step="0.01" 
                                    min="0.01" 
                                    required>
                            </div>
                        </div>
                        
                        <!-- Payment Date -->
                        <div class="mb-4">
                            <label for="payment_date{{ $booking->id }}" class="form-label fw-bold">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>Payment Date
                            </label>
                            <input type="date" 
                                class="form-control form-control-lg" 
                                id="payment_date{{ $booking->id }}" 
                                name="payment_date" 
                                value="{{ date('Y-m-d') }}"
                                required>
                        </div>

                        <!-- Payment Type -->
                        <div class="mb-4">
                            <label for="payment_type{{ $booking->id }}" class="form-label fw-bold">
                                <i class="fas fa-credit-card text-primary me-2"></i>Payment Mode
                            </label>
                            <select class="form-select form-control-lg" 
                                id="payment_type{{ $booking->id }}" 
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
                            <label for="transaction_id{{ $booking->id }}" class="form-label fw-bold">
                                <i class="fas fa-hashtag text-primary me-2"></i>Transaction ID
                            </label>
                            <input type="text" class="form-control form-control-lg" id="transaction_id{{ $booking->id }}" name="transaction_id" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between" style="padding: 15px; border-radius: 8px;">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-success" id="savePaymentBtn{{ $booking->id }}" onclick="submitPaymentForm({{ $booking->id }})">
                        <i class="fas fa-save me-2"></i>Save Payment Details
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

// Payment Modal Functions
function validateAmount(input, maxAmount) {
    if (parseFloat(input.value) > maxAmount) {
        input.value = maxAmount;
    }
}

// These functions are no longer needed with the simplified payment form
// Keeping a simplified version of validateAmount for reference
function validateAmount(input, maxAmount) {
    if (parseFloat(input.value) > maxAmount) {
        input.value = maxAmount;
    }
}

function validatePaymentAmountInput(bookingId) {
    const paymentAmount = document.getElementById(`payment_amount${bookingId}`);
    
    if (!paymentAmount || parseFloat(paymentAmount.value) <= 0) {
        // Show error using SweetAlert instead of DOM elements
        Swal.fire({
            title: 'Validation Error',
            text: 'Payment amount must be greater than zero',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return false;
    }
    
    return true;
}

function submitPaymentForm(bookingId) {
    if (validatePaymentAmountInput(bookingId)) {
        document.getElementById(`paymentForm${bookingId}`).submit();
    }
}

// Handle confirm payment button click
$(document).ready(function() {
    $('.confirm-payment-btn').on('click', function() {
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
                $.ajax({
                    url: `/package-booking/${bookingId}/confirm-payment`,
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
</script>
@endsection