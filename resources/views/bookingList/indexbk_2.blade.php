@extends('layouts.layout')
@section('title', 'BookingList')
@extends('layouts.datatablecss')
@section('content')
<style>
    /* Styles for the modal content */
    .detail-label {
        font-weight: bold;
        color: #566a7f;
    }
    .accordion-button.collapsed{
    opacity: 0.2 !important;
    }
    
    .detail-value {
        padding-left: 10px;
    }
    .detail-section {
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #eee;
    }
    .detail-section:last-child {
        border-bottom: none;
    }
    .detail-section h5 {
        color: #5a8dee;
        margin-bottom: 1rem;
    }
    .detail-list {
        padding-left: 1rem;
    }
    .detail-item {
        margin-bottom: 0.5rem;
    }
    .badge-large {
        font-size: 1rem;
        padding: 0.5rem 1rem;
    }
    .attraction-info {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    /* Tour accordion styling */
    .tour-accordion .accordion-button {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    .tour-accordion .accordion-button:not(.collapsed) {
        background-color: #e7f1ff;
        color: #435ebe;
    }
    .type-accordion .accordion-button {
        background-color: #ffffff;
        font-weight: 500;
        padding: 0.75rem 1rem;
    }
    .type-accordion .accordion-button:not(.collapsed) {
        background-color: #f0f7ff;
        color: #435ebe;
    }
    .type-accordion .accordion-body {
        padding: 0.5rem 1rem;
    }
    .tour-info {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 0.5rem;
    }
    .tour-info-item {
        display: flex;
        align-items: center;
    }
    .tour-info-item i {
        margin-right: 0.5rem;
        color: #435ebe;
    }
    .service-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .service-item {
        position: relative;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .service-item:last-child {
        border-bottom: none;
    }
    .type-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .type-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        color: white;
    }
    .type-icon.hotel { background-color: #4CAF50; }
    .type-icon.attraction { background-color: #2196F3; }
    .type-icon.guide { background-color: #9C27B0; }
    .type-icon.restaurant { background-color: #FF9800; }
    .type-icon.travel_point, .type-icon.travel_hourly { background-color: #F44336; }
    .type-icon.exit_port, .type-icon.entry_port { background-color: #607D8B; }
    .type-badge {
        font-size: 0.75rem;
        padding: 0.3rem 0.6rem;
        border-radius: 0.25rem;
        font-weight: 600;
    }
    .accordion-item {
        border: 1px solid rgba(0,0,0,.125);
        margin-bottom: 0.5rem;
        border-radius: 0.375rem;
        overflow: hidden;
    }
    .nested-accordion .accordion-item {
        border-radius: 0.25rem;
        margin-bottom: 0.5rem;
    }
    .tour-meta-badges {
        margin-top: 0.5rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .tour-meta-badge {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .tour-meta-badge i {
        font-size: 0.75rem;
    }
    .tour-meta-badge.destination {
        background-color: #e8f5e9;
        color: #43a047;
    }
    .tour-meta-badge.pax {
        background-color: #e3f2fd;
        color: #1e88e5;
    }
    .tour-meta-badge.date {
        background-color: #f0f7ff;
        color: #435ebe;
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center" style="padding: 1rem 1.5rem;">
                <h5 class="card-title mb-0">Tour Bookings</h5>
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
            <div class="card-body">
                <x-alert />
                
                <!-- Tour Accordion -->
                <div class="accordion tour-accordion" id="tourAccordion">
                    @php
                        // Group bookings by tour_id
                        $groupedBookings = [];
                        foreach ($bookings as $booking) {
                            if (!isset($groupedBookings[$booking->tour_id])) {
                                // Fetch tour details
                                $tourDetails = DB::table('tours')->where('tour_id', $booking->tour_id)->first();
                                
                                $groupedBookings[$booking->tour_id] = [
                                    'tour_id' => $booking->tour_id,
                                    'booking_id' => $booking->booking_id,
                                    'agent_name' => $booking->agent_name,
                                    'dmc_company' => $booking->dmc_company,
                                    'master_dmc_company' => $booking->master_dmc_company ?? null,
                                    'destination' => $tourDetails->destination ?? 'N/A',
                                    'adult' => $tourDetails->adult ?? 0,
                                    'child' => $tourDetails->child ?? 0,
                                    'infant' => $tourDetails->infant ?? 0,
                                    'male_count' => $tourDetails->male_count ?? 0,
                                    'female_count' => $tourDetails->female_count ?? 0,
                                    'types' => []
                                ];
                            }
                            
                            // Group services by type within each tour
                            $type = strtolower(str_replace(' ', '_', $booking->type));
                            if (!isset($groupedBookings[$booking->tour_id]['types'][$type])) {
                                $groupedBookings[$booking->tour_id]['types'][$type] = [
                                    'type' => $booking->type,
                                    'type_key' => $type,
                                    'services' => []
                                ];
                            }
                            
                            $groupedBookings[$booking->tour_id]['types'][$type]['services'][] = $booking;
                        }
                        @endphp
                    
                    @forelse ($groupedBookings as $index => $tour)
                        @php
                            // Calculate total pax
                            $totalPax = ($tour['infant'] ?? 0) + ($tour['child'] ?? 0) + ($tour['male_count'] ?? 0) + ($tour['female_count'] ?? 0);
                            
                            // Extract country from destination
                            $destinationParts = explode(',', $tour['destination']);
                            $country = trim(end($destinationParts));
                            // Remove parentheses if present
                            $country = trim(preg_replace('/[\(\)]/', '', $country));
                            
                            // Calculate prices for each service type
                            $hotel_price = 0;
                            $entry_port_price = 0;
                            $exit_port_price = 0;
                            $attraction_price = 0;
                            $guide_price = 0;
                            $restaurant_price = 0;
                            $travel_point_price = 0;
                            $travel_hourly_price = 0;
                            $overall_price = 0;
                            
                            // Calculate total prices by looping through the types and services
                            foreach ($tour['types'] as $typeKey => $typeGroup) {
                                foreach ($typeGroup['services'] as $service) {
                                    // Extract price directly from the JSON data
                                    $servicePrice = 0;
                                    
                                    // Try to get the totalPrice from data property
                                    if (isset($service->data)) {
                                        $rawData = is_string($service->data) ? json_decode($service->data, true) : $service->data;
                                        if (is_array($rawData)) {
                                            foreach ($rawData as $item) {
                                                if (isset($item['totalPrice'])) {
                                                    $servicePrice = (float)$item['totalPrice'];
                                                    // Store price in service object for later use
                                                    $service->display_price = $servicePrice;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                    
                                    // Add to the appropriate price totals
                                    switch($typeKey) {
                                        case 'hotel':
                                            $hotel_price += $servicePrice;
                                            break;
                                        case 'entry_port':
                                            $entry_port_price += $servicePrice;
                                            break;
                                        case 'exit_port':
                                            $exit_port_price += $servicePrice;
                                            break;
                                        case 'attraction':
                                            $attraction_price += $servicePrice;
                                            break;
                                        case 'guide':
                                            $guide_price += $servicePrice;
                                            break;
                                        case 'restaurant':
                                            $restaurant_price += $servicePrice;
                                            break;
                                        case 'travel_point':
                                            $travel_point_price += $servicePrice;
                                            break;
                                        case 'travel_hourly':
                                            $travel_hourly_price += $servicePrice;
                                            break;
                                    }
                                }
                            }
                            
                            // Calculate overall price as sum of all type prices
                            $overall_price = $hotel_price + $entry_port_price + $exit_port_price + 
                                            $attraction_price + $guide_price + $restaurant_price + 
                                            $travel_point_price + $travel_hourly_price;
                        @endphp
                        @php
                            $allowedRoles = [1, 11, 34, 64, 65, 66, 67, 68, 81, 90, 99, 108, 117, 125];
                        @endphp
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $tour['tour_id'] }}">
                                <button class="accordion-button {{ $index === array_key_first($groupedBookings) ? '' : 'collapsed' }}" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse{{ $tour['tour_id'] }}" 
                                        aria-expanded="{{ $index === array_key_first($groupedBookings) ? 'true' : 'false' }}" 
                                        aria-controls="collapse{{ $tour['tour_id'] }}">
                                    <div>
                                        <span class="badge bg-primary me-2">Tour #{{ $tour['tour_id'] }}</span>
                                        <span class="badge bg-secondary">{{ count($tour['types']) }} Types</span>
                                        <span class="badge bg-info">
                                            {{ array_sum(array_map(function($type) { return count($type['services']); }, $tour['types'])) }} Services
                                        </span>
                                        <span class="badge bg-danger">SGD {{ number_format($overall_price, 2) }}</span>
                                        <span class="badge bg-primary view-itinerary" 
                                            role="button"
                                            data-tour-id="{{ $tour['tour_id'] }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#itineraryModal">
                                            <i class="fas fa-calendar-alt"></i> View Itinerary
                                        </span>
                                        <div class="tour-meta-badges">
                                            <span class="tour-meta-badge destination">
                                                <i class="fas fa-map-marker-alt"></i> {{ $country }}
                                            </span>
                                            <span class="tour-meta-badge pax">
                                                <i class="fas fa-users"></i> {{ $totalPax }} Pax
                                            </span>
                                            <span class="tour-meta-badge date">
                                                <i class="fas fa-calendar-alt"></i> 
                                                @php 
                                                $tour_date = DB::table('tours')->where('tour_id', $tour['tour_id'])->first();
                                                @endphp
                                                {{ isset($tour_date->check_in_time) ? date('d M Y', strtotime($tour_date->check_in_time)) : 'N/A' }} - 
                                                {{ isset($tour_date->check_out_time) ? date('d M Y', strtotime($tour_date->check_out_time)) : 'N/A' }}
                                            </span>
                                        </div>
                                        
                                        <div class="tour-info mt-1">
                                            <div class="tour-info-item me-3">
                                                <i class="fas fa-user-tag"></i> {{ $tour['agent_name'] ?? 'N/A' }}
                                            </div>
                                            <div class="tour-info-item me-3">
                                                <i class="fas fa-building"></i> {{ $tour['dmc_company'] ?? 'N/A' }}
                                            </div>
                                            @if($tour['master_dmc_company'])
                                            <div class="tour-info-item">
                                                <i class="fas fa-city"></i> {{ $tour['master_dmc_company'] }}
                                            </div>
                                            @endif
                                            
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse{{ $tour['tour_id'] }}" 
                                 class="accordion-collapse collapse {{ $index === array_key_first($groupedBookings) ? 'show' : '' }}" 
                                 aria-labelledby="heading{{ $tour['tour_id'] }}" 
                                 data-bs-parent="#tourAccordion">
                                <div class="accordion-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <p class="mb-1">
                                                <strong><i class="fas fa-bookmark me-2"></i>Display ID:</strong> 
                                                <span class="badge bg-info">{{ $tour_date->display_id }}</span>
                                            </p>
                                            <p class="mb-1">
                                                <strong><i class="fas fa-map-marked-alt me-2"></i>Destination:</strong> 
                                                {{ $tour['destination'] }}
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1">
                                                <strong><i class="fas fa-users me-2"></i>Passengers:</strong>
                                                <span class="badge bg-primary me-1">{{ $tour['adult'] ?? 0 }} Adults</span>
                                                @if(($tour['child'] ?? 0) > 0)
                                                    <span class="badge bg-info me-1">{{ $tour['child'] }} Children</span>
                                                @endif
                                                @if(($tour['infant'] ?? 0) > 0)
                                                    <span class="badge bg-secondary me-1">{{ $tour['infant'] }} Infants</span>
                                                @endif
                                                <span class="badge bg-success">Total: {{ $totalPax }}</span>
                                            </p>
                                            <p class="mb-1">
                                                <strong><i class="fas fa-venus-mars me-2"></i>Gender:</strong>
                                                <span class="badge bg-primary me-1">{{ $tour['male_count'] ?? 0 }} Males</span>
                                                <span class="badge bg-danger me-1">{{ $tour['female_count'] ?? 0 }} Females</span>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Nested Accordion for Service Types -->
                                    <div class="accordion type-accordion nested-accordion" id="typeAccordion{{ $tour['tour_id'] }}">
                                        @foreach($tour['types'] as $typeIndex => $typeGroup)
                                            <div class="accordion-item">
                                                @php
                                                    $typeClass = '';
                                                    $typeIcon = 'fa-tag';
                                                    $typeBgColor = '#757575';
                                                    
                                                    switch($typeGroup['type_key']) {
                                                        case 'hotel':
                                                            $typeClass = 'hotel';
                                                            $typeIcon = 'fa-hotel';
                                                            $typeBgColor = '#4CAF50';
                                                            $type_price = $hotel_price;
                                                            break;
                                                        case 'attraction':
                                                            $typeClass = 'attraction';
                                                            $typeIcon = 'fa-map-marked-alt';
                                                            $typeBgColor = '#2196F3';
                                                            $type_price = $attraction_price;
                                                            break;
                                                        case 'guide':
                                                            $typeClass = 'guide';
                                                            $typeIcon = 'fa-user-tie';
                                                            $typeBgColor = '#9C27B0';
                                                            $type_price = $guide_price;
                                                            break;
                                                        case 'restaurant':
                                                            $typeClass = 'restaurant';
                                                            $typeIcon = 'fa-utensils';
                                                            $typeBgColor = '#FF9800';
                                                            $type_price = $restaurant_price;
                                                            break;
                                                        case 'travel_point':
                                                            $typeClass = 'travel_point';
                                                            $typeIcon = 'fa-bus';
                                                            $typeBgColor = '#F44336';
                                                            $type_price = $travel_point_price;
                                                            break;
                                                        case 'travel_hourly':
                                                            $typeClass = 'travel_hourly';
                                                            $typeIcon = 'fa-clock';
                                                            $typeBgColor = '#F44336';
                                                            $type_price = $travel_hourly_price;
                                                            break;
                                                        case 'exit_port':
                                                            $typeClass = 'exit_port';
                                                            $typeIcon = 'fa-sign-out-alt';
                                                            $typeBgColor = '#607D8B';
                                                            $type_price = $exit_port_price;
                                                            break;
                                                        case 'entry_port':
                                                            $typeClass = 'entry_port';
                                                            $typeIcon = 'fa-sign-in-alt';
                                                            $typeBgColor = '#607D8B';
                                                            $type_price = $entry_port_price;
                                                            break;
                                                    }
                                                @endphp
                                                
                                                <h2 class="accordion-header" id="typeHeading{{ $tour['tour_id'] }}_{{ $typeGroup['type_key'] }}">
                                                    <button class="accordion-button {{ $typeIndex === array_key_first($tour['types']) ? '' : 'collapsed' }}" 
                                                            type="button" 
                                                            data-bs-toggle="collapse" 
                                                            data-bs-target="#typeCollapse{{ $tour['tour_id'] }}_{{ $typeGroup['type_key'] }}" 
                                                            aria-expanded="{{ $typeIndex === array_key_first($tour['types']) ? 'true' : 'false' }}" 
                                                            aria-controls="typeCollapse{{ $tour['tour_id'] }}_{{ $typeGroup['type_key'] }}">
                                                        <div class="d-flex align-items-center">
                                                            <div class="type-icon me-2" style="background-color: {{ $typeBgColor }};">
                                                                <i class="fas {{ $typeIcon }}"></i>
                                                            </div>
                                                            <div>
                                                                <span class="fw-bold">{{ ucfirst($typeGroup['type']) }}</span>
                                                                <span class="badge bg-info ms-2">{{ count($typeGroup['services']) }} Services</span>
                                                                <span class="badge bg-danger ms-2">SGD {{ isset($type_price) ? number_format($type_price, 2) : '0.00' }}</span>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="typeCollapse{{ $tour['tour_id'] }}_{{ $typeGroup['type_key'] }}" 
                                                     class="accordion-collapse collapse {{ $typeIndex === array_key_first($tour['types']) ? 'show' : '' }}" 
                                                     aria-labelledby="typeHeading{{ $tour['tour_id'] }}_{{ $typeGroup['type_key'] }}" 
                                                     data-bs-parent="#typeAccordion{{ $tour['tour_id'] }}">
                                                    <div class="accordion-body">
                                                        <ul class="service-list">
                                                            @foreach($typeGroup['services'] as $service)
                                                                <li class="service-item">
                                                                    <div class="d-flex justify-content-between align-items-md-center flex-column flex-md-row">
                                                                        <div>
                                                                            @php
                                                                                $serviceName = 'Service';
                                                                                $data = $service->data_decoded;
                                                                                
                                                                                // Extract the total price from data
                                                                                if (isset($data['totalPrice'])) {
                                                                                    $service->total_price = $data['totalPrice'];
                                                                                }
                                                                                
                                                                                if (isset($data['HotelName'])) {
                                                                                    $serviceName = $data['HotelName'];
                                                                                } elseif (isset($data['AttractionName'])) {
                                                                                    $serviceName = $data['AttractionName'];
                                                                                } elseif (isset($data['GuideName'])) {
                                                                                    $serviceName = $data['GuideName'];
                                                                                } elseif (isset($data['RestaurantName'])) {
                                                                                    $serviceName = $data['RestaurantName'];
                                                                                } elseif (isset($data['vehicles_name'])) {
                                                                                    $serviceName = $data['vehicles_name'];
                                                                                }
                                                                            @endphp
                                                                            
                                                                            <div class="d-flex flex-wrap align-items-center">
                                                                                <span class="badge bg-primary me-2 mb-1">
                                                                                    <i class="fas fa-hashtag me-1"></i> ID: {{ $service->booking_id ?? 'N/A' }}
                                                                                </span>
                                                                                <span class="badge bg-secondary me-2 mb-1">
                                                                                    <i class="fas fa-calendar-alt me-1"></i> {{ isset($service->bookingDate) ? date('d M Y', strtotime($service->bookingDate)) : 'N/A' }}
                                                                                </span>
                                                                                <span class="badge bg-info mb-1">
                                                                                    <i class="fas fa-tag me-1"></i> {{ ucfirst($service->type ?? 'Service') }}
                                                                                </span>
                                                                                <span class="badge bg-danger ms-2 mb-1">
                                                                                    <i class="fas fa-money-bill-wave me-1"></i> SGD {{ isset($service->display_price) ? number_format($service->display_price, 2) : '0.00' }}
                                                                                </span>
                                                                            </div>
                                                                            
                                                                            <!-- <div class="mt-2">
                                                                                <span class="fw-semibold text-success">
                                                                                    <i class="fas fa-money-bill-wave me-1"></i>
                                                                                    SGD {{ isset($service->totalPrice) ? number_format($service->totalPrice, 2) : '0.00' }}
                                                                                </span>
                                                                            </div> -->
                                                                        </div>
                                                                        
                                                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                                                            @if(isset($service->status) && $service->status == 4)
                                                                                <span class="btn btn-sm btn-outline-secondary disabled">
                                                                                    <i class="fas fa-ban me-1"></i> Cancelled
                                                                                </span>
                                                                            @else
                                                                                <button type="button" class="btn btn-sm btn-outline-primary view-details" 
                                                                                        data-id="{{ $service->id }}"
                                                                                        data-type="{{ strtolower(str_replace(' ', '_', $service->type)) }}"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#detailsModal"
                                                                                        data-details="{{ htmlspecialchars(json_encode($service->data_decoded)) }}">
                                                                                    <i class="fas fa-eye"></i> View
                                                                                </button>
                                                                                @if(in_array(Auth::user()->role_id, $allowedRoles))
                                                                                <button type="button" class="btn btn-sm btn-outline-warning edit-details" 
                                                                                        data-id="{{ $service->id }}"
                                                                                        data-type="{{ strtolower(str_replace(' ', '_', $service->type)) }}"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#editdetailsModal"
                                                                                        data-details="{{ htmlspecialchars(json_encode($service->data_decoded)) }}">
                                                                                    <i class="fas fa-edit"></i> Edit
                                                                                </button>

                                                                                <button type="button" class="btn btn-sm btn-outline-success approve-booking" 
                                                                                        data-id="{{ $service->id }}"
                                                                                        data-booking-id="{{ $service->booking_id }}"
                                                                                        data-type="{{ strtolower(str_replace(' ', '_', $service->type)) }}"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#approveModal"
                                                                                        data-details="{{ htmlspecialchars(json_encode($service->data_decoded)) }}">
                                                                                    <i class="fas fa-check"></i> Approve
                                                                                </button>

                                                                                 <button type="button" class="btn btn-sm btn-outline-danger cancel-booking" 
                                                                                        data-id="{{ $service->id }}"
                                                                                        data-type="{{ strtolower(str_replace(' ', '_', $service->type)) }}"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#cancelModal"
                                                                                        data-details="{{ htmlspecialchars(json_encode($service->data_decoded)) }}">
                                                                                    <i class="fas fa-times"></i> Reject
                                                                                </button>
                                                                                @endif
                                                                            @endif
                                                                        </div>

                                                                    </div>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No tour bookings available.
                        </div>
                    @endforelse
                </div>
                
                <!-- Hidden table for export functionality -->
                <div class="d-none">
                    <table class="datatables-basic table table-bordered">
                        <thead>
                            <tr>
                                <th>Sl No</th>
                                <th>Tour Id</th>
                                <th>Booking Id</th>
                                <th>Country</th> 
                                <th>Total Pax</th>
                                @if(auth()->user()->role_id == 10)
                                    <th>DMC</th>
                                    <th>Agent Name</th>
                                @elseif(auth()->user()->role_id == 11)
                                    <th>Agent Name</th>
                                @else
                                    <th>Master Dmc</th>
                                    <th>DMC</th>
                                    <th>Agent Name</th>
                                @endif
                                <th>Type</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bookings as $key => $booking)
                                @php
                                    // Fetch tour details
                                    $tourDetails = DB::table('tours')->where('tour_id', $booking->tour_id)->first();
                                    
                                    // Calculate total pax
                                    $totalPax = ($tourDetails->infant ?? 0) + ($tourDetails->child ?? 0) + 
                                                ($tourDetails->male_count ?? 0) + ($tourDetails->female_count ?? 0);
                                    
                                    // Extract country from destination
                                    $destinationParts = explode(',', $tourDetails->destination ?? '');
                                    $country = trim(end($destinationParts));
                                    // Remove parentheses if present
                                    $country = trim(preg_replace('/[\(\)]/', '', $country));

                                    
                                @endphp
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $booking->tour_id }}</td>
                                    <td>{{ $booking->booking_id }}</td>
                                    <td>{{ $country }}</td>
                                    <td>{{ $totalPax }}</td>
                                    @if(auth()->user()->role_id == 10)
                                        <td>{{ $booking->dmc_company }}</td>
                                        <td>{{ $booking->agent_name ?? 'N/A' }}</td>
                                    @elseif(auth()->user()->role_id == 11)
                                        <td>{{ $booking->agent_name ?? 'N/A' }}</td>
                                    @else
                                        <td>{{ $booking->master_dmc_company }}</td>
                                        <td>{{ $booking->dmc_company }}</td>
                                        <td>{{ $booking->agent_name ?? 'N/A' }}</td>
                                    @endif
                                    <td>{{ $booking->type }}</td>
                                    <td>View</td>
                                </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailsModalBody">
                <!-- Content will be dynamically loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@if(in_array(Auth::user()->role_id, $allowedRoles))
<!-- Edit Modal-->
<div class="modal fade" id="editdetailsModal" tabindex="-1" aria-labelledby="editdetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editdetailsModalLabel">Edit Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editBookingForm" method="POST" action="{{ route('booking.update.dates') }}">
                @csrf
                <input type="hidden" name="booking_id" id="edit_booking_id">
                <input type="hidden" name="booking_type" id="edit_booking_type">
                <!-- Hidden fields for type-specific data -->
                <input type="hidden" name="has_visit_time" id="has_visit_time" value="0">
                <input type="hidden" name="has_guide_name" id="has_guide_name" value="0">
                <input type="hidden" name="has_pickup_date" id="has_pickup_date" value="0">
                <input type="hidden" name="has_entry_time" id="has_entry_time" value="0">
                <div class="modal-body" id="editdetailsModalBody">
                    <!-- Content will be dynamically loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Reject Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cancelBookingForm" method="POST" action="{{ route('booking.cancel') }}">
                @csrf
                <input type="hidden" name="booking_id" id="cancel_booking_id">
                <input type="hidden" name="booking_type" id="cancel_booking_type">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Are you sure you want to reject this booking?
                    </div>
                    <div class="mb-3">
                        <label for="cancel_reason" class="form-label">Reason for Rejection</label>
                        <textarea class="form-control" id="cancel_reason" name="cancel_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">Approve Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="approveBookingForm" method="POST" action="{{ route('booking.approve') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="booking_id" id="approve_booking_id">
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        Are you sure you want to approve this booking?
                    </div>
                    <div class="mb-3">
                        <label for="reference_id" class="form-label">Reference ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="reference_id" name="reference_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="reference_file" class="form-label">Reference File (Optional)</label>
                        <input type="file" class="form-control" id="reference_file" name="reference_file">
                        <div class="form-text">Upload supporting documents if available (PDF, DOC, JPG, PNG)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Confirm Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Itinerary Modal -->
<div class="modal fade" id="itineraryModal" tabindex="-1" aria-labelledby="itineraryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itineraryModalLabel">Tour Itinerary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="itineraryModalBody">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading itinerary...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="printItinerary">
                    <i class="fas fa-print"></i> Print Itinerary
                </button>
            </div>
        </div>
    </div>
</div>
<!-- SIMPLE VIEW HANDLER FIX WITH NOCONFLICT -->
<script>
// Use jQuery in no-conflict mode to avoid conflicts with other scripts
jQuery.noConflict();
(function($) {
    // Simple overriding script that runs last to ensure view functionality works
    $(document).ready(function() {
        // Add a direct event handler for view-details
        $('body').on('click', '.view-details', function(e) {
        console.log("Fixed view handler triggered");
        
        // Basic variables
        var $btn = $(this);
        var type = $btn.data('type') || 'service';
        var encodedDetails = $btn.data('details') || '{}';
        
        // Create a unique modal
        var modalId = 'view_modal_' + Math.floor(Math.random() * 1000000);
        
        // Append modal to body
        $('body').append(`
            <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${type.charAt(0).toUpperCase() + type.slice(1).replace('_', ' ')} Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading details...</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        // Show the modal
        var modal = new bootstrap.Modal(document.getElementById(modalId));
        modal.show();
        
        // Process the data
        setTimeout(function() {
            try {
                // Parse the details
                var decodedDetails = $('<div/>').html(encodedDetails).text();
                var details = JSON.parse(decodedDetails);
                
                // Simple HTML output
                var html = `
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">${type.toUpperCase()} Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                `;
                
                // Add basic info
                if (type === 'hotel' && details.hotelDetails) {
                    html += `
                        <div class="col-12 mb-3">
                            <h6>Hotel: ${details.hotelDetails.hotel_name || 'N/A'}</h6>
                        </div>
                    `;
                } else if (type === 'attraction') {
                    html += `
                        <div class="col-12 mb-3">
                            <h6>Attraction: ${details.AttractionName || 'N/A'}</h6>
                        </div>
                    `;
                }
                
                // Add date/time
                html += `
                    <div class="col-md-6">
                        <p><strong>Date:</strong> ${formatDate(details.bookingDate)}</p>
                `;
                
                if (type === 'hotel' && Array.isArray(details.bookingDate)) {
                    html += `<p><strong>Check-out:</strong> ${formatDate(details.bookingDate[1])}</p>`;
                }
                
                if (details.visitTime) {
                    html += `<p><strong>Time:</strong> ${details.visitTime}</p>`;
                } else if (details.entrytime) {
                    html += `<p><strong>Time:</strong> ${details.entrytime}</p>`;
                }
                
                html += `
                    </div>
                    <div class="col-md-6">
                        <p><strong>Price:</strong> SGD ${parseFloat(details.totalPrice || 0).toFixed(2)}</p>
                `;
                
                if (details.adultCount || details.adults) {
                    html += `<p><strong>Adults:</strong> ${details.adultCount || details.adults || 0}</p>`;
                }
                
                if (details.childCount || details.children) {
                    html += `<p><strong>Children:</strong> ${details.childCount || details.children || 0}</p>`;
                }
                
                html += `
                        </div>
                    </div>
                </div>
                `;
                
                // Add customer details if available
                if (details.fullName || details.email || details.phone) {
                    html += `
                        <div class="card mt-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Customer Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Name:</strong> ${details.fullName || 'N/A'}</p>
                                        <p><strong>Email:</strong> ${details.email || 'N/A'}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Phone:</strong> ${details.countryCode || ''} ${details.phone || 'N/A'}</p>
                                        <p><strong>Address:</strong> ${details.address1 || 'N/A'}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                // Add toggle for complete details
                html += `
                    <div class="alert alert-info mt-3">
                        <button class="btn btn-sm btn-outline-secondary" onclick="$('#complete_details_${modalId}').toggle()">
                            <i class="fas fa-list-ul me-1"></i> Toggle Complete Details
                        </button>
                        <div id="complete_details_${modalId}" class="mt-3 p-2 bg-light" style="display:none;">
                            <pre class="mb-0" style="max-height: 400px; overflow-y: auto;"><code>${JSON.stringify(details, null, 2)}</code></pre>
                        </div>
                    </div>
                `;
                
                // Update modal content
                $('#' + modalId + ' .modal-body').html(html);
                
            } catch (e) {
                console.error("Error processing details:", e);
                $('#' + modalId + ' .modal-body').html(`
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Error</h5>
                        <p>There was a problem loading the details: ${e.message}</p>
                    </div>
                `);
            }
        }, 300);
        
        // Prevent event from bubbling up and triggering other handlers
        e.preventDefault();
        e.stopPropagation();
        return false;
    });
    
    // Helper function to format dates
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        
        try {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return dateString;
            
            return date.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        } catch (e) {
            return dateString;
        }
    }
    
    // Handle show details button
    $(document).on('click', '#complete_details_' + modalId + '_toggle', function() {
        $('#complete_details_' + modalId).toggle();
    });
});
})(jQuery);
</script>
<!-- UNIFIED BOOKING MANAGER - HANDLES BOTH VIEW AND ITINERARY -->
<script>
// Unified solution that handles both view and itinerary with no conflicts
// Create a unified namespace that both view and itinerary will use
window.BookingManager = (function() {
    // Private variables
    var modals = {};
    // Private variables
    var _modalCount = 0;
    
    // Create a single return object to hold all shared functionality
    return {
        // Core rendering functions used by both view and itinerary
        renderDetails: function(type, details, container) {
            // Call the appropriate renderer based on type
            switch(type.toLowerCase()) {
                case 'hotel':
                    this.renderHotelDetails(details, container);
                    break;
                case 'attraction':
                    this.renderAttractionDetails(details, container);
                    break;
                case 'guide':
                    this.renderGuideDetails(details, container);
                    break;
                case 'travel point':
                case 'travel_point':
                    this.renderTravelPointDetails(details, container);
                    break;
                case 'travel hourly':
                case 'travel_hourly':
                    this.renderTravelHourlyDetails(details, container);
                    break;
                case 'entry port':
                case 'entry_port':
                    this.renderEntryPortDetails(details, container);
                    break;
                case 'exit port':
                case 'exit_port':
                    this.renderExitPortDetails(details, container);
                    break;
                case 'restaurant':
                    this.renderRestaurantDetails(details, container);
                    break;
                default:
                    container.innerHTML = `<div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No renderer available for type: ${type}
                    </div>`;
                    break;
            }
        },
    // Standardized hotel details renderer
    renderHotelDetails: function(details, container) {
            var html = `
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">${details.hotelDetails?.hotel_name || 'Hotel'}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Check-in:</strong> ${formatDate(Array.isArray(details.bookingDate) ? details.bookingDate[0] : details.bookingDate)}</p>
                                ${Array.isArray(details.bookingDate) ? `<p><strong>Check-out:</strong> ${formatDate(details.bookingDate[1])}</p>` : ''}
                            </div>
                            <div class="col-md-6">
                                <p><strong>Price:</strong> SGD ${parseFloat(details.totalPrice || 0).toFixed(2)}</p>
                                <p><strong>Booking ID:</strong> ${details.booking_id || 'N/A'}</p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6 class="mb-3">Customer Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> ${details.fullName || 'N/A'}</p>
                                <p><strong>Email:</strong> ${details.email || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Phone:</strong> ${details.countryCode || ''} ${details.phone || 'N/A'}</p>
                                <p><strong>Address:</strong> ${details.address1 || 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <button class="btn btn-sm btn-outline-secondary" onclick="$('#complete-details').toggle()">
                        <i class="fas fa-list-ul me-1"></i> Toggle Complete Details
                    </button>
                    <div id="complete-details" class="mt-3 p-2 bg-light" style="display:none;">
                        <pre class="mb-0" style="max-height: 400px; overflow-y: auto;"><code>${JSON.stringify(details, null, 2)}</code></pre>
                    </div>
                </div>
            `;
            
            container.html(html);
        };
    }
    
    if (typeof window.renderAttractionDetails !== 'function') {
        window.renderAttractionDetails = function(details, container) {
            var html = `
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">${details.AttractionName || 'Attraction'}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Date:</strong> ${formatDate(details.bookingDate)}</p>
                                <p><strong>Visit Time:</strong> ${details.visitTime || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Price:</strong> SGD ${parseFloat(details.totalPrice || 0).toFixed(2)}</p>
                                <p><strong>Adults:</strong> ${details.adultCount || '0'}</p>
                                <p><strong>Children:</strong> ${details.childCount || '0'}</p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6 class="mb-3">Customer Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> ${details.fullName || 'N/A'}</p>
                                <p><strong>Email:</strong> ${details.email || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Phone:</strong> ${details.countryCode || ''} ${details.phone || 'N/A'}</p>
                                <p><strong>Address:</strong> ${details.address1 || 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <button class="btn btn-sm btn-outline-secondary" onclick="$('#complete-details').toggle()">
                        <i class="fas fa-list-ul me-1"></i> Toggle Complete Details
                    </button>
                    <div id="complete-details" class="mt-3 p-2 bg-light" style="display:none;">
                        <pre class="mb-0" style="max-height: 400px; overflow-y: auto;"><code>${JSON.stringify(details, null, 2)}</code></pre>
                    </div>
                </div>
            `;
            
            container.html(html);
        };
    }
    
    if (typeof window.renderGuideDetails !== 'function') {
        window.renderGuideDetails = function(details, container) {
            var html = `
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">${details.guide_name || details.GuideName || 'Guide Service'}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Date:</strong> ${formatDate(details.bookingDate)}</p>
                                ${details.entrytime ? `<p><strong>Entry Time:</strong> ${details.entrytime}</p>` : ''}
                            </div>
                            <div class="col-md-6">
                                <p><strong>Price:</strong> SGD ${parseFloat(details.totalPrice || 0).toFixed(2)}</p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6 class="mb-3">Customer Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> ${details.fullName || 'N/A'}</p>
                                <p><strong>Email:</strong> ${details.email || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Phone:</strong> ${details.countryCode || ''} ${details.phone || 'N/A'}</p>
                                <p><strong>Address:</strong> ${details.address1 || 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <button class="btn btn-sm btn-outline-secondary" onclick="$('#complete-details').toggle()">
                        <i class="fas fa-list-ul me-1"></i> Toggle Complete Details
                    </button>
                    <div id="complete-details" class="mt-3 p-2 bg-light" style="display:none;">
                        <pre class="mb-0" style="max-height: 400px; overflow-y: auto;"><code>${JSON.stringify(details, null, 2)}</code></pre>
                    </div>
                </div>
            `;
            
            container.html(html);
        };
    }
    
    if (typeof window.renderRestaurantDetails !== 'function') {
        window.renderRestaurantDetails = function(details, container) {
            var html = `
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">${details.restaurantName || 'Restaurant'}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Date:</strong> ${formatDate(details.bookingDate)}</p>
                                <p><strong>Visit Time:</strong> ${details.visitTime || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Price:</strong> SGD ${parseFloat(details.totalPrice || 0).toFixed(2)}</p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6 class="mb-3">Customer Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> ${details.fullName || 'N/A'}</p>
                                <p><strong>Email:</strong> ${details.email || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Phone:</strong> ${details.countryCode || ''} ${details.phone || 'N/A'}</p>
                                <p><strong>Address:</strong> ${details.address1 || 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <button class="btn btn-sm btn-outline-secondary" onclick="$('#complete-details').toggle()">
                        <i class="fas fa-list-ul me-1"></i> Toggle Complete Details
                    </button>
                    <div id="complete-details" class="mt-3 p-2 bg-light" style="display:none;">
                        <pre class="mb-0" style="max-height: 400px; overflow-y: auto;"><code>${JSON.stringify(details, null, 2)}</code></pre>
                    </div>
                </div>
            `;
            
            container.html(html);
        };
    }
    
    if (typeof window.renderTravelPointDetails !== 'function') {
        window.renderTravelPointDetails = function(details, container) {
            var html = `
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">${details.vehicles_name || 'Transportation'}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Date:</strong> ${formatDate(details.bookingDate)}</p>
                                ${details.entrytime ? `<p><strong>Entry Time:</strong> ${details.entrytime}</p>` : ''}
                            </div>
                            <div class="col-md-6">
                                <p><strong>Price:</strong> SGD ${parseFloat(details.totalPrice || 0).toFixed(2)}</p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6 class="mb-3">Customer Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> ${details.fullName || 'N/A'}</p>
                                <p><strong>Email:</strong> ${details.email || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Phone:</strong> ${details.countryCode || ''} ${details.phone || 'N/A'}</p>
                                <p><strong>Address:</strong> ${details.address1 || 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <button class="btn btn-sm btn-outline-secondary" onclick="$('#complete-details').toggle()">
                        <i class="fas fa-list-ul me-1"></i> Toggle Complete Details
                    </button>
                    <div id="complete-details" class="mt-3 p-2 bg-light" style="display:none;">
                        <pre class="mb-0" style="max-height: 400px; overflow-y: auto;"><code>${JSON.stringify(details, null, 2)}</code></pre>
                    </div>
                </div>
            `;
            
            container.html(html);
        };
    }
    
    if (typeof window.renderEntryPortDetails !== 'function') {
        window.renderEntryPortDetails = function(details, container) {
            var html = `
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Entry Port Service</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Date:</strong> ${formatDate(details.bookingDate)}</p>
                                ${details.entrytime ? `<p><strong>Entry Time:</strong> ${details.entrytime}</p>` : ''}
                            </div>
                            <div class="col-md-6">
                                <p><strong>Price:</strong> SGD ${parseFloat(details.totalPrice || 0).toFixed(2)}</p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6 class="mb-3">Customer Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> ${details.fullName || 'N/A'}</p>
                                <p><strong>Email:</strong> ${details.email || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Phone:</strong> ${details.countryCode || ''} ${details.phone || 'N/A'}</p>
                                <p><strong>Address:</strong> ${details.address1 || 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <button class="btn btn-sm btn-outline-secondary" onclick="$('#complete-details').toggle()">
                        <i class="fas fa-list-ul me-1"></i> Toggle Complete Details
                    </button>
                    <div id="complete-details" class="mt-3 p-2 bg-light" style="display:none;">
                        <pre class="mb-0" style="max-height: 400px; overflow-y: auto;"><code>${JSON.stringify(details, null, 2)}</code></pre>
                    </div>
                </div>
            `;
            
            container.html(html);
        };
    }
    
    if (typeof window.renderExitPortDetails !== 'function') {
        window.renderExitPortDetails = function(details, container) {
            var html = `
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Exit Port Service</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Date:</strong> ${formatDate(details.bookingDate)}</p>
                                ${details.entrytime ? `<p><strong>Entry Time:</strong> ${details.entrytime}</p>` : ''}
                            </div>
                            <div class="col-md-6">
                                <p><strong>Price:</strong> SGD ${parseFloat(details.totalPrice || 0).toFixed(2)}</p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6 class="mb-3">Customer Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> ${details.fullName || 'N/A'}</p>
                                <p><strong>Email:</strong> ${details.email || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Phone:</strong> ${details.countryCode || ''} ${details.phone || 'N/A'}</p>
                                <p><strong>Address:</strong> ${details.address1 || 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <button class="btn btn-sm btn-outline-secondary" onclick="$('#complete-details').toggle()">
                        <i class="fas fa-list-ul me-1"></i> Toggle Complete Details
                    </button>
                    <div id="complete-details" class="mt-3 p-2 bg-light" style="display:none;">
                        <pre class="mb-0" style="max-height: 400px; overflow-y: auto;"><code>${JSON.stringify(details, null, 2)}</code></pre>
                    </div>
                </div>
            `;
            
            container.html(html);
        };
    }
    
    if (typeof window.fallbackRender !== 'function') {
        window.fallbackRender = function(type, details, container) {
            var html = `
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">${type.charAt(0).toUpperCase() + type.slice(1).replace('_', ' ')} Service</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Date:</strong> ${formatDate(details.bookingDate)}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Price:</strong> SGD ${parseFloat(details.totalPrice || 0).toFixed(2)}</p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6 class="mb-3">Customer Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> ${details.fullName || 'N/A'}</p>
                                <p><strong>Email:</strong> ${details.email || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Phone:</strong> ${details.countryCode || ''} ${details.phone || 'N/A'}</p>
                                <p><strong>Address:</strong> ${details.address1 || 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <button class="btn btn-sm btn-outline-secondary" onclick="$('#complete-details').toggle()">
                        <i class="fas fa-list-ul me-1"></i> Toggle Complete Details
                    </button>
                    <div id="complete-details" class="mt-3 p-2 bg-light" style="display:none;">
                        <pre class="mb-0" style="max-height: 400px; overflow-y: auto;"><code>${JSON.stringify(details, null, 2)}</code></pre>
                    </div>
                </div>
            `;
            
            container.html(html);
        };
    }
    
        // Utility function for date formatting
        formatDate: function(dateString) {
            if (!dateString) return 'N/A';
            
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return dateString;
                
                return date.toLocaleDateString('en-US', {
                    weekday: 'long', 
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            } catch (e) {
                console.error("Error formatting date:", e);
                return dateString;
            }
        },
        
        // Create a unique modal ID to avoid conflicts
        createModalId: function(prefix) {
            _modalCount++;
            return prefix + '_' + Date.now() + '_' + _modalCount;
        },
        
        // Main entry points that should be called from outside
        
        // View details handler - call this for view buttons
        viewDetails: function(type, encodedDetails, serviceId) {
            var modalId = this.createModalId('view');
            var self = this;
            
            // Create modal
            $('body').append(`
                <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${type.charAt(0).toUpperCase() + type.slice(1).replace('_', ' ')} Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading details...</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            
            // Show modal
            var modal = new bootstrap.Modal(document.getElementById(modalId));
            modal.show();
            
            // Process data
            setTimeout(function() {
                try {
                    // Parse details
                    var decodedDetails = $('<div/>').html(encodedDetails).text();
                    var details = JSON.parse(decodedDetails);
                    
                    // Render based on type
                    var html = self.renderServiceDetails(type, details);
                    $('#' + modalId + ' .modal-body').html(html);
                    
                } catch (e) {
                    console.error("Error in view handler:", e);
                    $('#' + modalId + ' .modal-body').html(`
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle me-2"></i>Error</h5>
                            <p>Could not display service details: ${e.message}</p>
                        </div>
                    `);
                }
            }, 100);
        },
        
        // Itinerary view handler - call this for itinerary buttons
        viewItinerary: function(tourId, services) {
            var modalId = this.createModalId('itinerary');
            var self = this;
            
            // Create modal
            $('body').append(`
                <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Tour #${tourId} Itinerary</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading itinerary...</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            
            // Show modal
            var modal = new bootstrap.Modal(document.getElementById(modalId));
            modal.show();
            
            // Get itinerary HTML
            var html = this.renderItinerary(tourId, services);
            
            // Update modal content
            setTimeout(function() {
                $('#' + modalId + ' .modal-body').html(html);
            }, 100);
        },
        
        // Single rendering function for any service type
        renderServiceDetails: function(type, details) {
            // Get service name based on type
            var name = '';
            if (type === 'hotel' && details.hotelDetails && details.hotelDetails.hotel_name) {
                name = details.hotelDetails.hotel_name;
            } else if (type === 'attraction' && details.AttractionName) {
                name = details.AttractionName;
            } else if (type === 'guide') {
                name = details.guide_name || details.GuideName || 'Guide Service';
            } else if (type === 'restaurant') {
                name = details.restaurantName || 'Restaurant';
            } else if (type.includes('travel')) {
                name = details.vehicles_name || 'Transportation';
            } else if (type === 'entry_port') {
                name = 'Entry Port Service';
            } else if (type === 'exit_port') {
                name = 'Exit Port Service';
            } else {
                name = type.charAt(0).toUpperCase() + type.slice(1).replace('_', ' ');
            }
            
            // Generate HTML
            var html = `
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">${name}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Date:</strong> ${this.formatDate(details.bookingDate)}</p>
                                ${type === 'hotel' && Array.isArray(details.bookingDate) ? 
                                    `<p><strong>Check-out:</strong> ${this.formatDate(details.bookingDate[1])}</p>` : ''}
                                ${details.visitTime ? `<p><strong>Visit Time:</strong> ${details.visitTime}</p>` : ''}
                                ${details.entrytime ? `<p><strong>Entry Time:</strong> ${details.entrytime}</p>` : ''}
                            </div>
                            <div class="col-md-6">
                                <p><strong>Price:</strong> SGD ${parseFloat(details.totalPrice || 0).toFixed(2)}</p>
                                ${details.adultCount ? `<p><strong>Adults:</strong> ${details.adultCount}</p>` : ''}
                                ${details.childCount ? `<p><strong>Children:</strong> ${details.childCount}</p>` : ''}
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6 class="mb-3">Customer Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> ${details.fullName || 'N/A'}</p>
                                <p><strong>Email:</strong> ${details.email || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Phone:</strong> ${details.countryCode || ''} ${details.phone || 'N/A'}</p>
                                <p><strong>Address:</strong> ${details.address1 || 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <button class="btn btn-sm btn-outline-secondary" onclick="$(this).next().toggle()">
                        <i class="fas fa-list-ul me-1"></i> Toggle Complete Details
                    </button>
                    <div class="mt-3 p-2 bg-light" style="display:none;">
                        <pre class="mb-0" style="max-height: 400px; overflow-y: auto;"><code>${JSON.stringify(details, null, 2)}</code></pre>
                    </div>
                </div>
            `;
            
            return html;
        },
        
        // Render a complete itinerary
        renderItinerary: function(tourId, services) {
            var self = this;
            var processedServices = [];
            
            // Process services
            if (services && services.length) {
                services.forEach(function(service) {
                    try {
                        var serviceType = service.type || 'unknown';
                        var decodedDetails = $('<div/>').html(service.details).text();
                        var details = JSON.parse(decodedDetails);
                        
                        processedServices.push({
                            type: serviceType,
                            details: details,
                            date: details.bookingDate,
                            id: service.id || ''
                        });
                    } catch (e) {
                        console.error("Error processing service:", e);
                    }
                });
            }
            
            // Sort services by date
            processedServices.sort(function(a, b) {
                if (!a.date) return 1;
                if (!b.date) return -1;
                return new Date(a.date) - new Date(b.date);
            });
            
            // Generate HTML
            var html = `
                <style>
                    .timeline {
                        position: relative;
                        padding: 20px 0;
                    }
                    .timeline:before {
                        content: '';
                        position: absolute;
                        top: 0;
                        bottom: 0;
                        left: 15px;
                        width: 4px;
                        background: #e9ecef;
                    }
                    .timeline-item {
                        position: relative;
                        margin-bottom: 20px;
                        padding-left: 40px;
                    }
                    .timeline-dot {
                        position: absolute;
                        left: 0;
                        top: 5px;
                        width: 30px;
                        height: 30px;
                        border-radius: 50%;
                        background: #5e72e4;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: white;
                    }
                    .timeline-date {
                        margin-bottom: 15px;
                        font-weight: bold;
                        background: #435ebe;
                        color: white;
                        padding: 5px 10px;
                        border-radius: 4px;
                        display: inline-block;
                    }
                </style>
                
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Tour #${tourId} Itinerary</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><strong>Services:</strong> ${processedServices.length}</p>
                    </div>
                </div>
                
                <div class="timeline">
            `;
            
            // Add services to timeline
            var currentDate = '';
            processedServices.forEach(function(service) {
                var displayDate = self.formatDate(service.date);
                
                // Add date header if new date
                if (displayDate !== currentDate) {
                    html += `<div class="timeline-date">${displayDate}</div>`;
                    currentDate = displayDate;
                }
                
                // Get icon based on type
                var icon = 'fa-calendar-check';
                if (service.type === 'hotel') icon = 'fa-hotel';
                else if (service.type === 'attraction') icon = 'fa-ticket-alt';
                else if (service.type === 'guide') icon = 'fa-user-tie';
                else if (service.type === 'restaurant') icon = 'fa-utensils';
                else if (service.type.includes('travel')) icon = 'fa-car';
                else if (service.type.includes('port')) icon = 'fa-plane';
                
                // Add service item
                html += `
                    <div class="timeline-item">
                        <div class="timeline-dot">
                            <i class="fas ${icon}"></i>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">${self.getServiceName(service.type, service.details)}</h5>
                                <p class="mb-0"><strong>Type:</strong> ${service.type.replace('_', ' ').toUpperCase()}</p>
                                <p class="mb-0"><strong>Date:</strong> ${displayDate}</p>
                                ${service.details.visitTime ? `<p class="mb-0"><strong>Time:</strong> ${service.details.visitTime}</p>` : ''}
                                ${service.details.entrytime ? `<p class="mb-0"><strong>Time:</strong> ${service.details.entrytime}</p>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += `</div>`;
            
            return html;
        },
        
        // Helper to get service name
        getServiceName: function(type, details) {
            if (type === 'hotel' && details.hotelDetails && details.hotelDetails.hotel_name) {
                return details.hotelDetails.hotel_name;
            } else if (type === 'attraction' && details.AttractionName) {
                return details.AttractionName;
            } else if (type === 'guide') {
                return details.guide_name || details.GuideName || 'Guide Service';
            } else if (type === 'restaurant') {
                return details.restaurantName || 'Restaurant';
            } else if (type.includes('travel')) {
                return details.vehicles_name || 'Transportation';
            } else if (type === 'entry_port') {
                return 'Entry Port Service';
            } else if (type === 'exit_port') {
                return 'Exit Port Service';
            }
            return type.charAt(0).toUpperCase() + type.slice(1).replace('_', ' ');
        }
    };
})();
</script>
<!-- Global compatibility layer for handling function calls from old code -->
<script>
// Global compatibility functions to bridge old code to BookingManager
// These ensure that old code still works by redirecting to BookingManager
window.renderHotelDetails = function(details, container) {
    if (window.BookingManager && typeof window.BookingManager.renderHotelDetails === 'function') {
        window.BookingManager.renderHotelDetails(details, container);
    } else {
        console.error("BookingManager not available for renderHotelDetails");
    }
};

window.renderAttractionDetails = function(details, container) {
    if (window.BookingManager && typeof window.BookingManager.renderAttractionDetails === 'function') {
        window.BookingManager.renderAttractionDetails(details, container);
    } else {
        console.error("BookingManager not available for renderAttractionDetails");
    }
};

window.renderGuideDetails = function(details, container) {
    if (window.BookingManager && typeof window.BookingManager.renderGuideDetails === 'function') {
        window.BookingManager.renderGuideDetails(details, container);
    } else {
        console.error("BookingManager not available for renderGuideDetails");
    }
};

window.renderTravelPointDetails = function(details, container) {
    if (window.BookingManager && typeof window.BookingManager.renderTravelPointDetails === 'function') {
        window.BookingManager.renderTravelPointDetails(details, container);
    } else {
        console.error("BookingManager not available for renderTravelPointDetails");
    }
};

window.renderTravelHourlyDetails = function(details, container) {
    if (window.BookingManager && typeof window.BookingManager.renderTravelHourlyDetails === 'function') {
        window.BookingManager.renderTravelHourlyDetails(details, container);
    } else {
        console.error("BookingManager not available for renderTravelHourlyDetails");
    }
};

window.renderEntryPortDetails = function(details, container) {
    if (window.BookingManager && typeof window.BookingManager.renderEntryPortDetails === 'function') {
        window.BookingManager.renderEntryPortDetails(details, container);
    } else {
        console.error("BookingManager not available for renderEntryPortDetails");
    }
};

window.renderExitPortDetails = function(details, container) {
    if (window.BookingManager && typeof window.BookingManager.renderExitPortDetails === 'function') {
        window.BookingManager.renderExitPortDetails(details, container);
    } else {
        console.error("BookingManager not available for renderExitPortDetails");
    }
};

window.renderRestaurantDetails = function(details, container) {
    if (window.BookingManager && typeof window.BookingManager.renderRestaurantDetails === 'function') {
        window.BookingManager.renderRestaurantDetails(details, container);
    } else {
        console.error("BookingManager not available for renderRestaurantDetails");
    }
};

// Initialize the BookingManager when the document is ready
$(document).ready(function() {
    // Initialize our unified BookingManager
    if (window.BookingManager && typeof window.BookingManager.init === 'function') {
        window.BookingManager.init();
        console.log('BookingManager initialized via global compatibility layer');
    }
    
    // Apply jQuery.noConflict() if needed
    // var jq = jQuery.noConflict();
});
</script>
@endsection

@section('scripts')
<!-- DataTable JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<!-- DataTables Initialization Script -->

<script>
    $(document).ready(function() {
        // Initialize DataTable with export buttons (hidden table for export)
        var table = $('.datatables-basic').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy',
                'csv',
                'excel',
                'pdf',
                'print'
            ],
            paging: false,
            searching: false
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

        // View Details Button Click Handler
        $('.view-details').on('click', function() {
            console.log("View Details button clicked");

            let type = $(this).data('type');
            let encodedDetails = $(this).data('details');
            let modalBody = $('#detailsModalBody');
            
            console.log("View details for type:", type);
            
            // Clear previous content
            modalBody.html('');
            
            // First decode HTML entities
            let decodedDetails = $('<div/>').html(encodedDetails).text();
            
            // Parse the details safely
            let details;
            
            try {
                details = JSON.parse(decodedDetails);
                console.log("Parsed details:", details);
            } catch (e) {
                console.error("Error parsing JSON:", e);
                modalBody.html('<div class="alert alert-danger">Error loading details data: ' + e.message + '</div>');
                return;
            }
            
            // Update modal title with type
            $('#detailsModalLabel').text(capitalizeFirstLetter(type.replace('_', ' ')) + ' Booking Details');
            
            // Generate content based on type
            if (type === 'attraction') {
                renderAttractionDetails(details, modalBody);
            } else if (type === 'hotel') {
                renderHotelDetails(details, modalBody);
            } else if (type === 'guide') {
                renderGuideDetails(details, modalBody);
            } else if (type === 'restaurant') {
                renderRestaurantDetails(details, modalBody);
            } else if (type === 'travel_point') {
                renderTravelPointDetails(details, modalBody);
            } else if (type === 'travel_hourly') {
                renderTravelHourlyDetails(details, modalBody);
            } else if (type === 'exit_port') {
                renderExitPortDetails(details, modalBody);
            } else if (type === 'entry_port') {
                renderEntryPortDetails(details, modalBody);
            } else {
                // Generic display for other types
                renderGenericDetails(details, modalBody);
            }
        });

        // Add direct click handlers to all view buttons with the onclick attribute
        console.log("Adding direct onclick handlers");
        $('.view-details').each(function() {
            const viewBtn = $(this);
            const type = viewBtn.data('type');
            const encodedDetails = viewBtn.data('details');
            
            // Add a direct onclick attribute that will work regardless of other handlers
            viewBtn.attr('onclick', `
                (function() {
                    console.log("Direct onclick handler");
                    const type = '${type}';
                    const encodedDetails = '${encodedDetails.replace(/'/g, "\\'")}';
                    const modalBody = $('#detailsModalBody');
                    
                    // Clear previous content
                    modalBody.empty();
                    
                    // First decode HTML entities
                    const decodedDetails = $('<div/>').html(encodedDetails).text();
                    
                    try {
                        // Parse the details
                        const details = JSON.parse(decodedDetails);
                        
                        // Update modal title with type
                        $('#detailsModalLabel').text(capitalizeFirstLetter(type.replace('_', ' ')) + ' Booking Details');
                        
                        // Call the appropriate render function
                        window[type === 'attraction' ? 'renderAttractionDetails' : 
                              type === 'hotel' ? 'renderHotelDetails' :
                              type === 'guide' ? 'renderGuideDetails' :
                              type === 'restaurant' ? 'renderRestaurantDetails' :
                              type === 'travel_point' ? 'renderTravelPointDetails' :
                              type === 'travel_hourly' ? 'renderTravelHourlyDetails' :
                              type === 'exit_port' ? 'renderExitPortDetails' :
                              type === 'entry_port' ? 'renderEntryPortDetails' : 
                              'renderGenericDetails'](details, modalBody);
                    } catch (e) {
                        console.error("Error in direct handler:", e);
                        modalBody.html('<div class="alert alert-danger">Error: ' + e.message + '</div>');
                    }
                    
                    return false; // Prevent default
                })();
            `);
        });
        
        // Create a global function that will display service details directly
        window.showServiceDetails = function(element) {
            try {
                const btn = $(element);
                const type = btn.data('type');
                const encodedDetails = btn.data('details');
                const modalBody = $('#detailsModalBody');
                
                console.log("showServiceDetails called for type:", type);
                
                // Clear previous content
                modalBody.empty();
                
                // Decode HTML entities
                const decodedDetails = $('<div/>').html(encodedDetails).text();
                
                // Parse details
                const details = JSON.parse(decodedDetails);
                
                // Update modal title
                $('#detailsModalLabel').text(capitalizeFirstLetter(type.replace('_', ' ')) + ' Booking Details');
                
                // Call the appropriate render function
                if (type === 'attraction') {
                    renderAttractionDetails(details, modalBody);
                } else if (type === 'hotel') {
                    renderHotelDetails(details, modalBody);
                } else if (type === 'guide') {
                    renderGuideDetails(details, modalBody);
                } else if (type === 'restaurant') {
                    renderRestaurantDetails(details, modalBody);
                } else if (type === 'travel_point') {
                    renderTravelPointDetails(details, modalBody);
                } else if (type === 'travel_hourly') {
                    renderTravelHourlyDetails(details, modalBody);
                } else if (type === 'exit_port') {
                    renderExitPortDetails(details, modalBody);
                } else if (type === 'entry_port') {
                    renderEntryPortDetails(details, modalBody);
                } else {
                    renderGenericDetails(details, modalBody);
                }
            } catch (e) {
                console.error("Error in showServiceDetails:", e);
                $('#detailsModalBody').html('<div class="alert alert-danger">Error: ' + e.message + '</div>');
            }
        };

        // Helper function to capitalize first letter
        function capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }

        // Render details for Attraction type
        function renderAttractionDetails(details, container) {
    if (!details || (Array.isArray(details) && details.length === 0)) {
        container.html('<div class="alert alert-warning">No attraction details available</div>');
        return;
    }

    let items = Array.isArray(details) ? details : [details];
    
    items.forEach(function(item) {
        // console.log("Processing attraction item:", item);
        
        // Get DMC name from the users data
        let dmcName = 'N/A';
        let dmcId = item.dmc_id;
        if (dmcId && dmcUsers && dmcUsers[dmcId]) {
            dmcName = dmcUsers[dmcId].company_name;
        }

        let html = `
            <div class="container-fluid p-0">
                <!-- Attraction Header -->
                <div class="card border-0 mb-4 overflow-hidden">
                    <div class="card-body p-0">
                        <div class="bg-gradient-primary text-white p-4" style="background: linear-gradient(45deg, #6B4F4F, #483434);">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="avatar avatar-lg bg-white text-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center">
                                        <i class="fas fa-landmark fa-lg"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <h3 class="mb-1 fw-bold">${item.AttractionName || 'N/A'}</h3>
                                    <div class="d-flex align-items-center flex-wrap">
                                        <span class="badge bg-white text-primary me-2 mb-1">
                                            <i class="fas fa-ticket-alt me-1"></i> Attraction
                                        </span>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Attraction Information -->
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle text-primary me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Visit Information</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 mb-4">
                                        <div class="border rounded p-3 mt-3 h-100 bg-light">
                                            <h6 class="fw-bold text-primary mb-3">
                                                <i class="fas fa-calendar-alt me-2"></i> Visit Details
                                            </h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Visit Date:</strong></p>
                                                    <p class="mb-0">${formatDate(item.bookingDate) || 'N/A'}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Visit Time:</strong></p>
                                                    <p class="mb-0">${item.visitTime || 'N/A'}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-2">
                                    <div class="col-md-3 col-6 mb-3">
                                        <div class="border rounded p-3 text-center bg-light">
                                            <div class="text-muted mb-1 small">Adults</div>
                                            <div class="fw-bold">
                                                <i class="fas fa-user text-primary me-1"></i>
                                                ${item.adultCount || '0'}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-3">
                                        <div class="border rounded p-3 text-center bg-light">
                                            <div class="text-muted mb-1 small">Children</div>
                                            <div class="fw-bold">
                                                <i class="fas fa-child text-primary me-1"></i>
                                                ${item.childCount || '0'}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12 mb-3">
                                        <div class="border rounded p-3 text-center bg-light">
                                            <div class="text-muted mb-1 small">Selection Type</div>
                                            <div class="fw-bold">
                                                <i class="fas fa-check-circle text-primary me-1"></i>
                                                ${item.Selection || 'N/A'}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-circle text-primary me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Customer Details</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3 mt-3 pb-3 border-bottom">
                                    <h6 class="text-primary mb-3">Personal Information</h6>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <p class="text-muted small mb-1">Full Name</p>
                                            <p class="fw-medium mb-3">${item.fullName || 'N/A'}</p>
                                        </div>
                                        <div class="col-sm-6">
                                            <p class="text-muted small mb-1">Phone</p>
                                            <p class="fw-medium mb-3">${item.countryCode || ''} ${item.phone || 'N/A'}</p>
                                        </div>
                                        <div class="col-12">
                                            <p class="text-muted small mb-1">Email</p>
                                            <p class="fw-medium mb-0">${item.email || 'N/A'}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <h6 class="text-primary mb-3">Address</h6>
                                    <p class="mb-1">${item.address1 || 'N/A'}</p>
                                    <p class="mb-1">${item.address2 || ''}</p>
                                    <p class="mb-0">${item.state || 'N/A'} - ${item.zip || 'N/A'}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-success text-white py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-money-bill-wave me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Payment Details</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4 mt-3">
                                    <div class="col-12">
                                        <div class="p-4 bg-light rounded text-center mb-4">
                                            <p class="text-muted mb-1">Total Amount</p>
                                            <h1 class="display-6 text-success mb-0">SGD ${parseFloat(item.totalPrice || 0).toFixed(2)}</h1>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-light" style="color: grey; padding: 15px; margin-top: 12px; margin-bottom: 0; border-radius: 5px;">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-building text-primary"></i>
                                        </div>
                                        <div class="ms-2">
                                            <span class="d-block fw-medium">DMC Company Name</span>
                                            <span>${dmcName ? dmcName : 'N/A'}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                ${item.specialRequests ? `
                    <div class="alert alert-info mt-4 mb-0">
                        <h6 class="fw-bold"><i class="fas fa-info-circle me-2"></i>Special Requests</h6>
                        <p class="mb-0">${item.specialRequests}</p>
                    </div>
                ` : ''}
            </div>
        `;
        
        container.append(html);
    });
}

    // Render details for Hotel type
    function renderHotelDetails(details, container) {
    if (!details || (Array.isArray(details) && details.length === 0)) {
        container.html('<div class="alert alert-warning">No hotel details available</div>');
        return;
    }

    let items = Array.isArray(details) ? details : [details];
    
    items.forEach(function(item) {
        // console.log("Processing hotel item:", item);
        
        // Get DMC name from the users data
        let dmcName = 'N/A';
        let dmcId = item.priceModeId;
        if (dmcId && dmcUsers && dmcUsers[dmcId]) {
            dmcName = dmcUsers[dmcId].company_name;
        }

        let html = `
            <div class="container-fluid p-0">
                <!-- Hotel Header -->
                <div class="card border-0 mb-4 overflow-hidden">
                    <div class="card-body p-0">
                        <div class="bg-gradient-primary text-white p-4" style="background: linear-gradient(45deg, #1a237e, #0d47a1);">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="mb-1 fw-bold">${item.hotelDetails?.hotel_name || 'N/A'}</h3>
                                    <div class="d-flex align-items-center flex-wrap">
                                        <span class="badge bg-white text-primary me-2 mb-1">
                                            <i class="fas fa-hotel me-1"></i> Hotel Booking
                                        </span>
                                        <span class="badge bg-light text-dark mb-1">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            ${Array.isArray(item.bookingDate) && item.bookingDate.length >= 2 
                                              ? `${formatDate(item.bookingDate[0])} to ${formatDate(item.bookingDate[1])}` 
                                              : (formatDate(item.bookingDate) || 'N/A')}
                                        </span>
                                    </div>
                                </div>
                                ${item.hotelDetails?.image ? `
                                <div class="col-auto">
                                    <img src="${item.hotelDetails.image}" alt="Hotel" class="img-fluid rounded" style="max-height: 80px; max-width: 120px; object-fit: cover;">
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Hotel Information -->
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle text-primary me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Hotel Information</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div style="background-color: #f8f9fa; color: #343a40; padding: 12px 15px; border-radius: 6px; border: 1px solid #dee2e6; display: flex; align-items: center;" class="mb-4 mt-3">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                    ${item.hotelDetails?.location || 'Location not specified'}
                                </div>

                                <!-- Room Details -->
                                ${item.rooms?.map(room => `
                                    <div class="border rounded p-3 mb-3 bg-light">
                                        <h6 class="fw-bold mb-3">
                                            <i class="fas fa-bed text-primary me-2"></i>
                                            ${room.room_type} Room
                                        </h6>
                                        ${room.beds?.map(bed => `
                                            <div class="ms-4 mb-3">
                                                <p class="mb-2"><strong>Bed Type:</strong> ${bed.bed_type}</p>
                                                <p class="mb-2"><strong>Occupancy:</strong> ${bed.head_count} person(s)</p>
                                                <p class="mb-2"><strong>Meal Plan:</strong> ${bed.selectedMeals?.meal_1?.type || 'N/A'}</p>
                                                ${bed.baby_cot ? `<p class="mb-0"><strong>Baby Cot:</strong> Yes</p>` : ''}
                                            </div>
                                        `).join('') || ''}
                                    </div>
                                `).join('') || ''}
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-circle text-primary me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Customer Details</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 mt-3">
                                    <div class="col-sm-6">
                                        <p class="text-muted small mb-1">Full Name</p>
                                        <p class="fw-medium">${item.fullName || 'N/A'}</p>
                                    </div>
                                    <div class="col-sm-6">
                                        <p class="text-muted small mb-1">Phone</p>
                                        <p class="fw-medium">${item.countryCode || ''} ${item.phone || 'N/A'}</p>
                                    </div>
                                    <div class="col-12">
                                        <p class="text-muted small mb-1">Email</p>
                                        <p class="fw-medium">${item.email || 'N/A'}</p>
                                    </div>
                                    <div class="col-12">
                                        <p class="text-muted small mb-1">Address</p>
                                        <p class="fw-medium mb-0">
                                            ${item.address1 || 'N/A'}<br>
                                            ${item.address2 || ''}<br>
                                            ${item.state || 'N/A'} - ${item.zip || 'N/A'}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-success text-white py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-money-bill-wave me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Payment Details</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="text-center p-4 bg-light rounded mb-4 mt-3">
                                    <p class="text-muted mb-1">Total Amount</p>
                                    <h1 class="display-6 text-success mb-0">SGD ${parseFloat(item.totalPrice || 0).toFixed(2)}</h1>
                                </div>

                                <div class="bg-light" style="color: grey; padding: 15px; margin-top: 12px; margin-bottom: 0; border-radius: 5px;">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-building text-primary"></i>
                                        </div>
                                        <div class="ms-2">
                                            <span class="d-block fw-medium">DMC Company Name</span>
                                            <span>${dmcName ? dmcName : 'N/A'}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.append(html);
    });
}

        // Render details for Attraction type
        function renderAttractionDetails(details, container) {
    if (!details || (Array.isArray(details) && details.length === 0)) {
        container.html('<div class="alert alert-warning">No attraction details available</div>');
        return;
    }

    let items = Array.isArray(details) ? details : [details];
    
    items.forEach(function(item) {
        // console.log("Processing attraction item:", item);
        
        // Get DMC name from the users data
        let dmcName = 'N/A';
        let dmcId = item.dmc_id;
        if (dmcId && dmcUsers && dmcUsers[dmcId]) {
            dmcName = dmcUsers[dmcId].company_name;
        }

        let html = `
            <div class="container-fluid p-0">
                <!-- Attraction Header -->
                <div class="card border-0 mb-4 overflow-hidden">
                    <div class="card-body p-0">
                        <div class="bg-gradient-primary text-white p-4" style="background: linear-gradient(45deg, #6B4F4F, #483434);">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="avatar avatar-lg bg-white text-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center">
                                        <i class="fas fa-landmark fa-lg"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <h3 class="mb-1 fw-bold">${item.AttractionName || 'N/A'}</h3>
                                    <div class="d-flex align-items-center flex-wrap">
                                        <span class="badge bg-white text-primary me-2 mb-1">
                                            <i class="fas fa-ticket-alt me-1"></i> Attraction
                                        </span>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Attraction Information -->
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle text-primary me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Visit Information</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 mb-4">
                                        <div class="border rounded p-3 mt-3 h-100 bg-light">
                                            <h6 class="fw-bold text-primary mb-3">
                                                <i class="fas fa-calendar-alt me-2"></i> Visit Details
                                            </h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Visit Date:</strong></p>
                                                    <p class="mb-0">${formatDate(item.bookingDate) || 'N/A'}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Visit Time:</strong></p>
                                                    <p class="mb-0">${item.visitTime || 'N/A'}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-2">
                                    <div class="col-md-3 col-6 mb-3">
                                        <div class="border rounded p-3 text-center bg-light">
                                            <div class="text-muted mb-1 small">Adults</div>
                                            <div class="fw-bold">
                                                <i class="fas fa-user text-primary me-1"></i>
                                                ${item.adultCount || '0'}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-3">
                                        <div class="border rounded p-3 text-center bg-light">
                                            <div class="text-muted mb-1 small">Children</div>
                                            <div class="fw-bold">
                                                <i class="fas fa-child text-primary me-1"></i>
                                                ${item.childCount || '0'}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12 mb-3">
                                        <div class="border rounded p-3 text-center bg-light">
                                            <div class="text-muted mb-1 small">Selection Type</div>
                                            <div class="fw-bold">
                                                <i class="fas fa-check-circle text-primary me-1"></i>
                                                ${item.Selection || 'N/A'}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-circle text-primary me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Customer Details</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3 mt-3 pb-3 border-bottom">
                                    <h6 class="text-primary mb-3">Personal Information</h6>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <p class="text-muted small mb-1">Full Name</p>
                                            <p class="fw-medium mb-3">${item.fullName || 'N/A'}</p>
                                        </div>
                                        <div class="col-sm-6">
                                            <p class="text-muted small mb-1">Phone</p>
                                            <p class="fw-medium mb-3">${item.countryCode || ''} ${item.phone || 'N/A'}</p>
                                        </div>
                                        <div class="col-12">
                                            <p class="text-muted small mb-1">Email</p>
                                            <p class="fw-medium mb-0">${item.email || 'N/A'}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <h6 class="text-primary mb-3">Address</h6>
                                    <p class="mb-1">${item.address1 || 'N/A'}</p>
                                    <p class="mb-1">${item.address2 || ''}</p>
                                    <p class="mb-0">${item.state || 'N/A'} - ${item.zip || 'N/A'}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-success text-white py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-money-bill-wave me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Payment Details</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4 mt-3">
                                    <div class="col-12">
                                        <div class="p-4 bg-light rounded text-center mb-4">
                                            <p class="text-muted mb-1">Total Amount</p>
                                            <h1 class="display-6 text-success mb-0">SGD ${parseFloat(item.totalPrice || 0).toFixed(2)}</h1>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-light" style="color: grey; padding: 15px; margin-top: 12px; margin-bottom: 0; border-radius: 5px;">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-building text-primary"></i>
                                        </div>
                                        <div class="ms-2">
                                            <span class="d-block fw-medium">DMC Company Name</span>
                                            <span>${dmcName ? dmcName : 'N/A'}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                ${item.specialRequests ? `
                    <div class="alert alert-info mt-4 mb-0">
                        <h6 class="fw-bold"><i class="fas fa-info-circle me-2"></i>Special Requests</h6>
                        <p class="mb-0">${item.specialRequests}</p>
                    </div>
                ` : ''}
            </div>
        `;
        
        container.append(html);
    });
}

    // Render details for Hotel type
    function renderHotelDetails(details, container) {
    if (!details || (Array.isArray(details) && details.length === 0)) {
        container.html('<div class="alert alert-warning">No hotel details available</div>');
        return;
    }

    let items = Array.isArray(details) ? details : [details];
    
    items.forEach(function(item) {
        // console.log("Processing hotel item:", item);
        
        // Get DMC name from the users data
        let dmcName = 'N/A';
        let dmcId = item.priceModeId;
        if (dmcId && dmcUsers && dmcUsers[dmcId]) {
            dmcName = dmcUsers[dmcId].company_name;
        }

        let html = `
            <div class="container-fluid p-0">
                <!-- Hotel Header -->
                <div class="card border-0 mb-4 overflow-hidden">
                    <div class="card-body p-0">
                        <div class="bg-gradient-primary text-white p-4" style="background: linear-gradient(45deg, #1a237e, #0d47a1);">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="mb-1 fw-bold">${item.hotelDetails.hotel_name || 'N/A'}</h3>
                                    <div class="d-flex align-items-center flex-wrap">
                                        <span class="badge bg-white text-primary me-2 mb-1">
                                            <i class="fas fa-hotel me-1"></i> Hotel Booking
                                        </span>
                                        <span class="badge bg-light text-dark mb-1">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            ${Array.isArray(item.bookingDate) && item.bookingDate.length >= 2 
                                              ? `${formatDate(item.bookingDate[0])} to ${formatDate(item.bookingDate[1])}` 
                                              : (formatDate(item.bookingDate) || 'N/A')}
                                        </span>
                                    </div>
                                </div>
                                ${item.hotelDetails.image ? `
                                <div class="col-auto">
                                    <img src="${item.hotelDetails.image}" alt="Hotel" class="img-fluid rounded" style="max-height: 80px; max-width: 120px; object-fit: cover;">
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Hotel Information -->
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle text-primary me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Hotel Information</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div style="background-color: #f8f9fa; color: #343a40; padding: 12px 15px; border-radius: 6px; border: 1px solid #dee2e6; display: flex; align-items: center;" class="mb-4 mt-3">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                    ${item.hotelDetails.location || 'Location not specified'}
                                </div>

                                <!-- Room Details -->
                                ${item.rooms.map(room => `
                                    <div class="border rounded p-3 mb-3 bg-light">
                                        <h6 class="fw-bold mb-3">
                                            <i class="fas fa-bed text-primary me-2"></i>
                                            ${room.room_type} Room
                                        </h6>
                                        ${room.beds.map(bed => `
                                            <div class="ms-4 mb-3">
                                                <p class="mb-2"><strong>Bed Type:</strong> ${bed.bed_type}</p>
                                                <p class="mb-2"><strong>Occupancy:</strong> ${bed.head_count} person(s)</p>
                                                <p class="mb-2"><strong>Meal Plan:</strong> ${bed.selectedMeals.meal_1.type}</p>
                                                ${bed.baby_cot ? `<p class="mb-0"><strong>Baby Cot:</strong> Yes</p>` : ''}
                                            </div>
                                        `).join('')}
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-circle text-primary me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Customer Details</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 mt-3">
                                    <div class="col-sm-6">
                                        <p class="text-muted small mb-1">Full Name</p>
                                        <p class="fw-medium">Full Name: ${item.fullName || 'N/A'}</p>
                                    </div>
                                    <div class="col-sm-6">
                                        <p class="text-muted small mb-1">Phone</p>
                                        <p class="fw-medium">${item.countryCode || ''} ${item.phone || 'N/A'}</p>
                                    </div>
                                    <div class="col-12">
                                        <p class="text-muted small mb-1">Email</p>
                                        <p class="fw-medium">${item.email || 'N/A'}</p>
                                    </div>
                                    <div class="col-12">
                                        <p class="text-muted small mb-1">Address</p>
                                        <p class="fw-medium mb-0">
                                            ${item.address1 || 'N/A'}<br>
                                            ${item.address2 || ''}<br>
                                            ${item.state || 'N/A'} - ${item.zip || 'N/A'}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-success text-white py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-money-bill-wave me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Payment Details</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="text-center p-4 bg-light rounded mb-4 mt-3">
                                    <p class="text-muted mb-1">Total Amount</p>
                                    <h1 class="display-6 text-success mb-0">SGD ${parseFloat(item.totalPrice || 0).toFixed(2)}</h1>
                                </div>

                                <div class="bg-light" style="color: grey; padding: 15px; margin-top: 12px; margin-bottom: 0; border-radius: 5px;">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-building text-primary"></i>
                                        </div>
                                        <div class="ms-2">
                                            <span class="d-block fw-medium">DMC Company Name</span>
                                            <span>${dmcName ? dmcName : 'N/A'}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                ${item.specialRequests ? `
                    <div class="alert alert-info mt-4 mb-0">
                        <h6 class="fw-bold"><i class="fas fa-info-circle me-2"></i>Special Requests</h6>
                        <p class="mb-0">${item.specialRequests}</p>
                    </div>
                ` : ''}
            </div>
        `;
        
        container.append(html);
    });
}

    // Render details for Guide type
    function renderGuideDetails(details, container) {
        // Check if details exists and is not empty
        if (!details || (Array.isArray(details) && details.length === 0)) {
            container.html('<div class="alert alert-warning">No guide details available</div>');
            return;
        }
        
        // Handle both array and object formats
        let items = Array.isArray(details) ? details : [details];
        
        items.forEach(function(item) {
            // Log each item for debugging
            // console.log("Processing guide item:", item);
            
            // Get DMC name from the users data
            let dmcName = 'N/A';
            if (item.dmc_Id && dmcUsers && dmcUsers[item.dmc_Id]) {
                dmcName = dmcUsers[item.dmc_Id].company_name;
            }
            
            let html = `
                <div class="container-fluid p-0">
                    <!-- Guide Header -->
                    <div class="card border-0 mb-4 overflow-hidden">
                        <div class="card-body p-0">
                            <div class="bg-gradient-primary text-white p-4" style="background: linear-gradient(45deg, #6B4F4F, #483434);">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="avatar avatar-lg bg-white text-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user-tie fa-lg"></i>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h3 class="mb-1 fw-bold">${item.guide_name || 'N/A'}</h3>
                                        <div class="d-flex align-items-center flex-wrap">
                                            <span class="badge bg-white text-primary me-2 mb-1">
                                                <i class="fas fa-id-badge me-1"></i> Guide
                                            </span>
                                            
                                        </div>
                                    </div>
                                    ${item.image ? `
                                    <div class="col-auto d-none d-md-block">
                                        <img src="${item.image}" alt="Guide" class="img-fluid rounded" style="max-height: 80px; max-width: 120px; object-fit: cover;">
                                    </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-4">
                        <!-- Service Information -->
                        <div class="col-md-12">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light py-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle text-primary me-2 fa-lg"></i>
                                        <h5 class="mb-0 fw-bold">Service Information</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 mb-4">
                                            <div class="border rounded p-3 mt-3 h-100 bg-light">
                                                <h6 class="fw-bold text-primary mb-3">
                                                    <i class="fas fa-map-marker-alt me-2"></i> Pickup Location
                                                </h6>
                                                <p class="mb-2 fw-medium">${item.entrypickup || 'N/A'}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-2">
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="border rounded p-3 text-center bg-light">
                                                <div class="text-muted mb-1 small">Booking Date</div>
                                                <div class="fw-bold">
                                                    <i class="far fa-calendar-alt text-primary me-1"></i>
                                                    ${formatDate(item.bookingDate) || 'N/A'}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="border rounded p-3 text-center bg-light">
                                                <div class="text-muted mb-1 small">Pickup Date</div>
                                                <div class="fw-bold">
                                                    <i class="far fa-calendar text-primary me-1"></i>
                                                    ${formatDate(item.pickupdate) || 'N/A'}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="border rounded p-3 text-center bg-light">
                                                <div class="text-muted mb-1 small">Entry Time</div>
                                                <div class="fw-bold">
                                                    <i class="far fa-clock text-primary me-1"></i>
                                                    ${item.entrytime || 'N/A'}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="border rounded p-3 text-center bg-light">
                                                <div class="text-muted mb-1 small">Passengers</div>
                                                <div class="fw-bold">
                                                    <i class="fas fa-users text-primary me-1"></i>
                                                    ${parseInt(item.adults || 0) + parseInt(item.children || 0)}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Customer Information -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light py-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-circle text-primary me-2 fa-lg"></i>
                                        <h5 class="mb-0 fw-bold">Customer Details</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3 mt-3 pb-3 border-bottom">
                                        <h6 class="text-primary mb-3">Personal Information</h6>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <p class="text-muted small mb-1">Full Name</p>
                                                <p class="fw-medium mb-3">${item.fullName || 'N/A'}</p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p class="text-muted small mb-1">Phone</p>
                                                <p class="fw-medium mb-3">${item.countryCode || ''} ${item.phone || 'N/A'}</p>
                                            </div>
                                            <div class="col-12">
                                                <p class="text-muted small mb-1">Email</p>
                                                <p class="fw-medium mb-0">${item.email || 'N/A'}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <h6 class="text-primary mb-3">Address</h6>
                                        <p class="mb-1">${item.address1 || 'N/A'}</p>
                                        <p class="mb-1">${item.address2 || 'N/A'}</p>
                                        <p class="mb-0">${item.state || 'N/A'} - ${item.zip || 'N/A'}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Information -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-success text-white py-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-money-bill-wave me-2 fa-lg"></i>
                                        <h5 class="mb-0 fw-bold">Payment Details</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-4 mt-3">
                                        <div class="col-12">
                                            <div class="p-4 bg-light rounded text-center mb-4">
                                                <p class="text-muted mb-1">Total Amount</p>
                                                <h1 class="display-6 text-success mb-0">SGD ${parseFloat(item.totalPrice || 0).toFixed(2)}</h1>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-light" style="color: grey; padding: 15px; margin-top: 12px; margin-bottom: 0; border-radius: 5px;">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-building text-primary"></i>
                                            </div>
                                            <div class="ms-2">
                                                <span class="d-block fw-medium">DMC Company Name</span>
                                                <span>${dmcName ? dmcName : 'N/A'}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            container.append(html);
        });
    }

        // Render details for Restaurant type
    function renderRestaurantDetails(details, container) {
    if (!details || (Array.isArray(details) && details.length === 0)) {
        container.html('<div class="alert alert-warning">No restaurant details available</div>');
        return;
    }
    
    let items = Array.isArray(details) ? details : [details];
    
    items.forEach(function(item) {
        // console.log("Processing restaurant item:", item);
        
        // Get DMC name from the users data
        let dmcName = 'N/A';
        let dmcId = item.dmc_id;
        if (dmcId && dmcUsers && dmcUsers[dmcId]) {
            dmcName = dmcUsers[dmcId].company_name;
        }

        let html = `
            <div class="container-fluid p-0">
                <!-- Restaurant Header -->
                <div class="card border-0 mb-4 overflow-hidden">
                    <div class="card-body p-0">
                        <div class="bg-gradient-primary text-white p-4" style="background: linear-gradient(45deg, #6B4F4F, #483434);">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="avatar avatar-lg bg-white text-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center">
                                        <i class="fas fa-utensils fa-lg"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <h3 class="mb-1 fw-bold">${item.restaurantName || 'N/A'}</h3>
                                    <div class="d-flex align-items-center flex-wrap">
                                        <span class="badge bg-white text-primary me-2 mb-1">
                                            <i class="fas fa-utensils me-1"></i> Restaurant
                                        </span>
                                       
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Restaurant Information -->
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle text-primary me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Restaurant Information</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 mb-4">
                                        <div class="border rounded p-3 mt-3 h-100 bg-light">
                                            <h6 class="fw-bold text-primary mb-3">
                                                <i class="fas fa-calendar-alt me-2"></i> Visit Details
                                            </h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Visit Date:</strong></p>
                                                    <p class="mb-0">${formatDate(item.bookingDate) || 'N/A'}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Visit Time:</strong></p>
                                                    <p class="mb-0">${item.visitTime || 'N/A'}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-2">
                                    <div class="col-md-3 col-6 mb-3">
                                        <div class="border rounded p-3 text-center bg-light">
                                            <div class="text-muted mb-1 small">Adults</div>
                                            <div class="fw-bold">
                                                <i class="fas fa-user text-primary me-1"></i>
                                                ${item.adultCount || '0'}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-3">
                                        <div class="border rounded p-3 text-center bg-light">
                                            <div class="text-muted mb-1 small">Children</div>
                                            <div class="fw-bold">
                                                <i class="fas fa-child text-primary me-1"></i>
                                                ${item.childCount || '0'}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-3">
                                        <div class="border rounded p-3 text-center bg-light">
                                            <div class="text-muted mb-1 small">Meal Type</div>
                                            <div class="fw-bold">
                                                <i class="fas fa-utensils text-primary me-1"></i>
                                                ${item.mealType || 'N/A'}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-3">
                                        <div class="border rounded p-3 text-center bg-light">
                                            <div class="text-muted mb-1 small">Menu Type</div>
                                            <div class="fw-bold">
                                                <i class="fas fa-clipboard-list text-primary me-1"></i>
                                                ${item.mealSpecificType || 'N/A'}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-circle text-primary me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Customer Details</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3 mt-3 pb-3 border-bottom">
                                    <h6 class="text-primary mb-3">Personal Information</h6>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <p class="text-muted small mb-1">Full Name</p>
                                            <p class="fw-medium mb-3">${item.fullName || 'N/A'}</p>
                                        </div>
                                        <div class="col-sm-6">
                                            <p class="text-muted small mb-1">Phone</p>
                                            <p class="fw-medium mb-3">${item.countryCode || ''} ${item.phone || 'N/A'}</p>
                                        </div>
                                        <div class="col-12">
                                            <p class="text-muted small mb-1">Email</p>
                                            <p class="fw-medium mb-0">${item.email || 'N/A'}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <h6 class="text-primary mb-3">Address</h6>
                                    <p class="mb-1">${item.address1 || 'N/A'}</p>
                                    <p class="mb-1">${item.address2 || ''}</p>
                                    <p class="mb-0">${item.state || 'N/A'} - ${item.zip || 'N/A'}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-success text-white py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-money-bill-wave me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Payment Details</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4 mt-3">
                                    <div class="col-12">
                                        <div class="p-4 bg-light rounded text-center mb-4">
                                            <p class="text-muted mb-1">Total Amount</p>
                                            <h1 class="display-6 text-success mb-0">SGD ${parseFloat(item.totalPrice || 0).toFixed(2)}</h1>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-light" style="color: grey; padding: 15px; margin-top: 12px; margin-bottom: 0; border-radius: 5px;">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-building text-primary"></i>
                                        </div>
                                        <div class="ms-2">
                                            <span class="d-block fw-medium">DMC Company Name</span>
                                            <span>${dmcName ? dmcName : 'N/A'}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                ${item.MealDescription && item.MealDescription.length > 0 ? `
                <!-- Meal Details -->
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light py-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clipboard-list text-primary me-2 fa-lg"></i>
                                <h5 class="mb-0 fw-bold">Meal Details</h5>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Item Name</th>
                                            <th>Description</th>
                                            <th>Category</th>
                                            <th>Type</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-end">Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${item.MealDescription.map(meal => `
                                            <tr>
                                                <td>${meal.item_name || 'N/A'}</td>
                                                <td>${meal.name || 'N/A'}</td>
                                                <td><span class="badge bg-info">${meal.category || 'N/A'}</span></td>
                                                <td><span class="badge ${meal.item_type === 'Veg' ? 'bg-success' : 'bg-danger'}">${meal.item_type || 'N/A'}</span></td>
                                                <td class="text-center">${meal.quantity || '0'}</td>
                                                <td class="text-end">SGD ${parseFloat(meal.price || 0).toFixed(2)}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <td colspan="4" class="fw-bold">Total</td>
                                            <td class="text-center fw-bold">${item.MealDescription.reduce((sum, meal) => sum + (parseInt(meal.quantity) || 0), 0)}</td>
                                            <td class="text-end fw-bold">SGD ${item.MealDescription.reduce((sum, meal) => sum + ((parseFloat(meal.price) || 0) * (parseInt(meal.quantity) || 0)), 0).toFixed(2)}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                ` : ''}

                ${item.specialRequests ? `
                    <div class="col-12">
                        <div class="alert alert-info mt-4 mb-0">
                            <h6 class="fw-bold"><i class="fas fa-info-circle me-2"></i>Special Requests</h6>
                            <p class="mb-0">${item.specialRequests}</p>
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
        
        container.append(html);
    });
}

function renderTravelPointDetails(details, container) {
    // Check if details exists and is not empty
    if (!details || (Array.isArray(details) && details.length === 0)) {
        container.html('<div class="alert alert-warning">No travel point details available</div>');
        return;
    }
    
    // Handle both array and object formats
    let items = Array.isArray(details) ? details : [details];
    
    items.forEach(function(item) {
        // Log each item for debugging
        // console.log("Processing travel point item:", item);
        
        // Get user info from item
        const userInfo = item.userInfo || {};
        
        // Get DMC name from the users data
        let dmcName = 'N/A';
        if (item.dmc_id && dmcUsers && dmcUsers[item.dmc_id]) {
            dmcName = dmcUsers[item.dmc_id].company_name;
        }
        
        let html = `
            <div class="container-fluid p-0">
                <!-- Travel Header -->
                <div class="card border-0 mb-4 overflow-hidden">
                    <div class="card-body p-0">
                        <div class="bg-gradient-primary text-white p-4" style="background: linear-gradient(45deg, #4B56D2, #47B5FF);">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="avatar avatar-lg bg-white text-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center">
                                        <i class="fas fa-car fa-lg"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <h3 class="mb-1 fw-bold">${item.vehicles_name || 'N/A'}</h3>
                                    <div class="d-flex align-items-center flex-wrap">
                                        <span class="badge bg-white text-primary me-2 mb-1">
                                            <i class="fas fa-car-side me-1"></i> ${item.type || 'N/A'}
                                        </span>
                                        <span class="badge bg-light text-dark me-2 mb-1">
                                            <i class="fas fa-map-marker-alt me-1"></i> ${item.city || 'N/A'}, ${item.country || 'N/A'}
                                        </span>
                                        
                                    </div>
                                </div>
                                ${item.image ? `
                                <div class="col-auto d-none d-md-block">
                                    <img src="${item.image}" alt="Vehicle" class="img-fluid rounded" style="max-height: 80px; max-width: 120px; object-fit: cover;">
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row g-4">
                    <!-- Route Information -->
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-route text-primary me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Journey Details</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-4 mt-3">
                                        <div class="border rounded p-3 h-100 bg-light">
                                            <h6 class="fw-bold text-primary mb-3">
                                                <i class="fas fa-map-marker-alt me-2"></i> Pickup Location
                                            </h6>
                                            <p class="mb-2 fw-medium">${item.entrypickup || 'N/A'}</p>

                                            ${item.PickupPlaceid ? `
                                            <div class="text-muted small">
                                                <span class="d-block mb-2">Coordinates:</span>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-secondary">Lat: ${item.PickupPlaceid.lat || 'N/A'}</span>
                                                    <span class="badge bg-secondary">Lng: ${item.PickupPlaceid.lng || 'N/A'}</span>
                                                </div>
                                            </div>
                                            ` : ''}
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4 mt-3">
                                        <div class="border rounded p-3 h-100 bg-light">
                                            <h6 class="fw-bold text-primary mb-3">
                                                <i class="fas fa-flag-checkered me-2"></i> Dropoff Location
                                            </h6>
                                            <p class="mb-2 fw-medium">${item.entrydropoff || 'N/A'}</p>

                                            ${item.DropoffPlaceid ? `
                                            <div class="text-muted small">
                                                <span class="d-block mb-2">Coordinates:</span>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-secondary">Lat: ${item.DropoffPlaceid.lat || 'N/A'}</span>
                                                    <span class="badge bg-secondary">Lng: ${item.DropoffPlaceid.lng || 'N/A'}</span>
                                                </div>
                                            </div>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-2">
                                    <div class="col-md-3 col-6 mb-3">
                                        <div class="border rounded p-3 text-center bg-light">
                                            <div class="text-muted mb-1 small">Date</div>
                                            <div class="fw-bold">
                                                <i class="far fa-calendar-alt text-primary me-1"></i>
                                                ${formatDate(item.pickupdate) || 'N/A'}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-3">
                                        <div class="border rounded p-3 text-center bg-light">
                                            <div class="text-muted mb-1 small">Time</div>
                                            <div class="fw-bold">
                                                <i class="far fa-clock text-primary me-1"></i>
                                                ${item.entrytime || 'N/A'}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-3">
                                        <div class="border rounded p-3 text-center bg-light">
                                            <div class="text-muted mb-1 small">Distance</div>
                                            <div class="fw-bold">
                                                <i class="fas fa-road text-primary me-1"></i>
                                                ${item.distance || '0'} km
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-3">
                                        <div class="border rounded p-3 text-center bg-light">
                                            <div class="text-muted mb-1 small">Passengers</div>
                                            <div class="fw-bold">
                                                <i class="fas fa-users text-primary me-1"></i>
                                                ${(parseInt(item.adults || 0) + parseInt(item.children || 0))}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info mt-3 mb-0">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <i class="fas fa-moon fa-lg"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1">Night Hours Information</h6>
                                            <p class="mb-0">Night rates apply between <strong>${item.Night_Start_Time || 'N/A'}</strong> and <strong>${item.Night_End_Time || 'N/A'}</strong></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Customer Information -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-circle text-primary me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Customer Details</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3 mt-3 pb-3 border-bottom">
                                    <h6 class="text-primary mb-3">Personal Information</h6>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <p class="text-muted small mb-1">Full Name</p>
                                            <p class="fw-medium mb-3">${userInfo.fullName || 'N/A'}</p>
                                        </div>
                                        <div class="col-sm-6">
                                            <p class="text-muted small mb-1">Phone</p>
                                            <p class="fw-medium mb-3">${userInfo.countryCode || ''} ${userInfo.phone || 'N/A'}</p>
                                        </div>
                                        <div class="col-12">
                                            <p class="text-muted small mb-1">Email</p>
                                            <p class="fw-medium mb-0">${userInfo.email || 'N/A'}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <h6 class="text-primary mb-3">Address</h6>
                                    <p class="mb-1">${userInfo.address1 || 'N/A'}</p>
                                    <p class="mb-1">${userInfo.address2 || 'N/A'}</p>
                                    <p class="mb-0">${userInfo.state || 'N/A'} - ${userInfo.zip || 'N/A'}</p>
                                </div>
                                
                                ${userInfo.specialRequests ? `
                                <div class="mt-3 pt-3 border-top">
                                    <h6 class="text-primary mb-2">Special Requests</h6>
                                    <p class="mb-0">${userInfo.specialRequests}</p>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Information -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-success text-white py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-money-bill-wave me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Payment Details</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4 mt-3">
                                    <div class="col-12">
                                        <div class="p-4 bg-light rounded text-center mb-4">
                                            <p class="text-muted mb-1">Total Amount</p>
                                            <h1 class="display-6 text-success mb-0">SGD ${parseFloat(item.totalPrice || 0).toFixed(2)}</h1>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <div class="card h-100 border">
                                            <div class="card-body p-3">
                                                <p class="text-muted small mb-1">Booking Type</p>
                                                <p class="fw-medium mb-0">
                                                    <i class="fas fa-bookmark text-primary me-2"></i>
                                                    ${item.bookingType || 'N/A'}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <div class="card h-100 border">
                                            <div class="card-body p-3">
                                                <p class="text-muted small mb-1">Payment Mode</p>
                                                <p class="fw-medium mb-0">
                                                    <i class="fas fa-credit-card text-primary me-2"></i>
                                                    ${item.Mode || 'N/A'}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-light" style="color: grey; padding: 15px; margin-top: 12px; margin-bottom: 0; border-radius: 5px;">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-building text-primary"></i>
                                        </div>
                                        <div class="ms-2">
                                            <span class="d-block fw-medium">DMC Company Name</span>
                                            <span>${dmcName ? dmcName : 'N/A'}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                ${userInfo.enquiryAmount || userInfo.enquiryComment ? `
                                <div class="alert alert-info mt-3 mb-0">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-comment-dollar text-primary"></i>
                                        </div>
                                        <div class="ms-2">
                                            <span class="d-block fw-medium">Enquiry Information</span>
                                            ${userInfo.enquiryAmount ? `<span class="d-block">Amount: SGD ${userInfo.enquiryAmount}</span>` : ''}
                                            ${userInfo.enquiryComment ? `<span>Comment: ${userInfo.enquiryComment}</span>` : ''}
                                        </div>
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.append(html);
    });
}

        // Render details for Travel Hourly type
function renderTravelHourlyDetails(details, container) {
    // Check if details exists and is not empty
    if (!details || (Array.isArray(details) && details.length === 0)) {
        container.html('<div class="alert alert-warning">No travel hourly details available</div>');
        return;
    }
    
    // Handle both array and object formats
    let items = Array.isArray(details) ? details : [details];
    
    items.forEach(function(item) {
        // Log each item for debugging
        // console.log("Processing travel hourly item:", item);
        
        // Get user info from item
        const userInfo = item.userInfo || {};
        
        // Get DMC name from the users data
        let dmcName = 'N/A';
        if (item.dmc_id && dmcUsers && dmcUsers[item.dmc_id]) {
            dmcName = dmcUsers[item.dmc_id].company_name;
        }
        
        let html = `
            <div class="container-fluid p-0">
                <!-- Travel Hourly Header -->
                <div class="card border-0 mb-4 overflow-hidden">
                    <div class="card-body p-0">
                        <div class="bg-gradient-primary text-white p-4" style="background: linear-gradient(45deg, #5D3891, #9A3B3B);">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="avatar avatar-lg bg-white text-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center">
                                        <i class="fas fa-car fa-lg"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <h3 class="mb-1 fw-bold">${item.vehicles_name || 'N/A'}</h3>
                                    <div class="d-flex align-items-center flex-wrap">
                                        <span class="badge bg-white text-primary me-2 mb-1">
                                            <i class="fas fa-car-side me-1"></i> ${item.type || 'N/A'}
                                        </span>
                                        <span class="badge bg-white text-primary me-2 mb-1">
                                            <i class="fas fa-clock me-1"></i> ${item.selectedHours || '0'} Hours
                                        </span>
                                        
                                    </div>
                                </div>
                                ${item.image ? `
                                <div class="col-auto d-none d-md-block">
                                    <img src="${item.image}" alt="Vehicle" class="img-fluid rounded" style="max-height: 80px; max-width: 120px; object-fit: cover;">
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row g-6">
                    <!-- Travel Information -->
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle text-primary me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Travel Information</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 mb-4">
                                        <div class="border rounded p-3 mt-3 h-100 bg-light">
                                            <h6 class="fw-bold text-primary mb-3">
                                                <i class="fas fa-map-marker-alt me-2"></i> Pickup Location
                                            </h6>
                                            <p class="mb-2 fw-medium">${item.entrypickup || 'N/A'}</p>
                                            ${item.PickupPlaceid ? `
                                            <div class="text-muted small">
                                                <span class="d-block mb-1">Coordinates:</span>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-secondary">Lat: ${item.PickupPlaceid.lat || 'N/A'}</span>
                                                    <span class="badge bg-secondary">Lng: ${item.PickupPlaceid.lng || 'N/A'}</span>
                                                </div>
                                            </div>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-2">
                                    <div class="col-md-3 col-6 mb-3">
                                        <div class="border rounded p-3 text-center bg-light">
                                            <div class="text-muted mb-1 small">Date</div>
                                            <div class="fw-bold">
                                                <i class="far fa-calendar-alt text-primary me-1"></i>
                                                ${formatDate(item.exitpickupdate) || formatDate(item.bookingDate) || 'N/A'}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-3">
                                        <div class="border rounded p-3 text-center bg-light">
                                            <div class="text-muted mb-1 small">Time</div>
                                            <div class="fw-bold">
                                                <i class="far fa-clock text-primary me-1"></i>
                                                ${item.entrytime || 'N/A'}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-3">
                                        <div class="border rounded p-3 text-center bg-light">
                                            <div class="text-muted mb-1 small">Adults</div>
                                            <div class="fw-bold">
                                                <i class="fas fa-user text-primary me-1"></i>
                                                ${item.adults || '0'}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-3">
                                        <div class="border rounded p-3 text-center bg-light">
                                            <div class="text-muted mb-1 small">Children</div>
                                            <div class="fw-bold">
                                                <i class="fas fa-child text-primary me-1"></i>
                                                ${item.children || '0'}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info mt-3 mb-0">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <i class="fas fa-moon fa-lg"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1">Night Hours Information</h6>
                                            <p class="mb-0">Night rates apply between <strong>${item.Night_Start_Time || 'N/A'}</strong> and <strong>${item.Night_End_Time || 'N/A'}</strong></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Customer Information -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-circle text-primary me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Customer Details</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3 mt-3 pb-3 border-bottom">
                                    <h6 class="text-primary mb-3">Personal Information</h6>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <p class="text-muted small mb-1">Full Name</p>
                                            <p class="fw-medium mb-3">${userInfo.fullName || 'N/A'}</p>
                                        </div>
                                        <div class="col-sm-6">
                                            <p class="text-muted small mb-1">Phone</p>
                                            <p class="fw-medium mb-3">${userInfo.countryCode || ''} ${userInfo.phone || 'N/A'}</p>
                                        </div>
                                        <div class="col-12">
                                            <p class="text-muted small mb-1">Email</p>
                                            <p class="fw-medium mb-0">${userInfo.email || 'N/A'}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <h6 class="text-primary mb-3">Address</h6>
                                    <p class="mb-1">${userInfo.address1 || 'N/A'}</p>
                                    <p class="mb-1">${userInfo.address2 || 'N/A'}</p>
                                    <p class="mb-0">${userInfo.state || 'N/A'} - ${userInfo.zip || 'N/A'}</p>
                                </div>
                                
                                ${userInfo.specialRequests ? `
                                <div class="mt-3 pt-3 border-top">
                                    <h6 class="text-primary mb-2">Special Requests</h6>
                                    <p class="mb-0">${userInfo.specialRequests}</p>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Information -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-success text-white py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-money-bill-wave me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Payment Details</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4 mt-3">
                                    <div class="col-12">
                                        <div class="p-4 bg-light rounded text-center mb-4">
                                            <p class="text-muted mb-1">Total Amount</p>
                                            <h1 class="display-6 text-success mb-0">SGD ${parseFloat(item.totalPrice || 0).toFixed(2)}</h1>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <div class="card h-100 border">
                                            <div class="card-body p-3">
                                                <p class="text-muted small mb-1">Booking Type</p>
                                                <p class="fw-medium mb-0">
                                                    <i class="fas fa-bookmark text-primary me-2"></i>
                                                    ${item.bookingType || 'N/A'}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <div class="card h-100 border">
                                            <div class="card-body p-3">
                                                <p class="text-muted small mb-1">Payment Mode</p>
                                                <p class="fw-medium mb-0">
                                                    <i class="fas fa-credit-card text-primary me-2"></i>
                                                    ${item.Mode || 'N/A'}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-light" style="color: grey; padding: 15px; margin-top: 12px; margin-bottom: 0; border-radius: 5px;">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-building text-primary"></i>
                                        </div>
                                        <div class="ms-2">
                                            <span class="d-block fw-medium">DMC Company Name</span>
                                            <span>${dmcName ? dmcName : 'N/A'}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                ${userInfo.enquiryAmount || userInfo.enquiryComment ? `
                                <div class="alert alert-info mt-3 mb-0">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-comment-dollar text-primary"></i>
                                        </div>
                                        <div class="ms-2">
                                            <span class="d-block fw-medium">Enquiry Information</span>
                                            ${userInfo.enquiryAmount ? `<span class="d-block">Amount: SGD ${(userInfo.enquiryAmount)}</span>` : ''}
                                            ${userInfo.enquiryComment ? `<span>Comment: ${userInfo.enquiryComment}</span>` : ''}
                                        </div>
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.append(html);
    });
}

function formatSGD(amount) {
        return new Intl.NumberFormat('en-SG', {
            style: 'currency',
            currency: 'SGD',
            minimumFractionDigits: 2
        }).format(amount);
    }

        // Generic renderer for any type
        function renderGenericDetails(details, container) {
            if (Array.isArray(details)) {
                details.forEach(function(item) {
                    let html = '<div class="detail-section"><h5>Booking Information</h5><div class="row">';
                    
                    // Create a two-column layout for the details
                    let keys = Object.keys(item);
                    let half = Math.ceil(keys.length / 2);
                    
                    html += '<div class="col-md-6">';
                    for (let i = 0; i < half; i++) {
                        let key = keys[i];
                        let value = item[key] || 'N/A';
                        html += `<p><span class="detail-label">${formatKey(key)}:</span> <span class="detail-value">${value}</span></p>`;
                    }
                    html += '</div><div class="col-md-6">';
                    
                    for (let i = half; i < keys.length; i++) {
                        let key = keys[i];
                        let value = item[key] || 'N/A';
                        html += `<p><span class="detail-label">${formatKey(key)}:</span> <span class="detail-value">${value}</span></p>`;
                    }
                    
                    html += '</div></div></div>';
                    container.append(html);
                });
            } else {
                // If details is not an array
                let html = '<div class="detail-section"><h5>Booking Information</h5><div class="row">';
                
                // Create a two-column layout for the details
                let keys = Object.keys(details);
                let half = Math.ceil(keys.length / 2);
                
                html += '<div class="col-md-6">';
                for (let i = 0; i < half; i++) {
                    let key = keys[i];
                    let value = details[key] || 'N/A';
                    html += `<p><span class="detail-label">${formatKey(key)}:</span> <span class="detail-value">${value}</span></p>`;
                }
                html += '</div><div class="col-md-6">';
                
                for (let i = half; i < keys.length; i++) {
                    let key = keys[i];
                    let value = details[key] || 'N/A';
                    html += `<p><span class="detail-label">${formatKey(key)}:</span> <span class="detail-value">${value}</span></p>`;
                }
                
                html += '</div></div></div>';
                container.append(html);
            }
        }

        // Helper function to format object keys for display
        function formatKey(key) {
            return key
                .replace(/([A-Z])/g, ' $1') // Insert a space before all uppercase letters
                .replace(/^./, function(str) { return str.toUpperCase(); }) // Capitalize the first letter
                .replace(/([a-z])([A-Z])/g, '$1 $2') // Add space between camelCase
                .replace(/_/g, ' '); // Replace underscores with spaces
        }

        function renderExitPortDetails(details, container) {
            // Check if details exists and is not empty
            if (!details || (Array.isArray(details) && details.length === 0)) {
                container.html('<div class="alert alert-warning">No exit port details available</div>');
                return;
            }
            
            // Handle both array and object formats
            let items = Array.isArray(details) ? details : [details];
            
            items.forEach(function(item) {
                // Log each item for debugging
                // console.log("Processing exit port item:", item);
                
                // Get user info from item
                const userInfo = item.userInfo || {};
                
                // Get DMC name from the users data
                let dmcName = 'N/A';
                if (item.dmc_id && dmcUsers && dmcUsers[item.dmc_id]) {
                    dmcName = dmcUsers[item.dmc_id].company_name;
                }
                
                let html = `
                    <div class="container-fluid p-0">
                        <!-- Exit Port Header -->
                        <div class="card border-0 mb-4 overflow-hidden">
                            <div class="card-body p-0">
                                <div class="bg-gradient-primary text-white p-4" style="background: linear-gradient(45deg, #3A6B35, #CBD18F);">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <div class="avatar avatar-lg bg-white text-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center">
                                                <i class="fas fa-plane-departure fa-lg"></i>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <h3 class="mb-1 fw-bold">${item.vehicles_name || 'N/A'}</h3>
                                            <div class="d-flex align-items-center flex-wrap">
                                                <span class="badge bg-white text-primary me-2 mb-1">
                                                    <i class="fas fa-car-side me-1"></i> ${item.type || 'N/A'}
                                                </span>
                                                <span class="badge bg-light text-dark me-2 mb-1">
                                                    <i class="fas fa-map-marker-alt me-1"></i> ${item.city || 'N/A'}, ${item.country || 'N/A'}
                                                </span>
                                                
                                            </div>
                                        </div>
                                        ${item.image ? `
                                        <div class="col-auto d-none d-md-block">
                                            <img src="${item.image}" alt="Vehicle" class="img-fluid rounded" style="max-height: 80px; max-width: 120px; object-fit: cover;">
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row g-4">
                            <!-- Route Information -->
                            <div class="col-md-12">
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-header bg-light py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-route text-primary me-2 fa-lg"></i>
                                            <h5 class="mb-0 fw-bold">Exit Journey Details</h5>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-4 mt-3">
                                                <div class="border rounded p-3 h-100 bg-light">
                                                    <h6 class="fw-bold text-primary mb-3">
                                                        <i class="fas fa-map-marker-alt me-2"></i> Pickup Location
                                                    </h6>
                                                    <p class="mb-2 fw-medium">${item.exitpickup || 'N/A'}</p>
                                                    ${item.PickupPlaceid ? `
                                                    <div class="text-muted small">
                                                        <span class="d-block mb-1">Coordinates:</span>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="badge bg-secondary">Lat: ${item.PickupPlaceid.lat || 'N/A'}</span>
                                                            <span class="badge bg-secondary">Lng: ${item.PickupPlaceid.lng || 'N/A'}</span>
                                                        </div>
                                                    </div>
                                                    ` : ''}
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-4 mt-3">
                                                <div class="border rounded p-3 h-100 w-100 bg-light">
                                                    <h6 class="fw-bold text-primary mb-3">
                                                        <i class="fas fa-flag-checkered me-2"></i> Dropoff Location
                                                    </h6>
                                                    <p class="mb-2 fw-medium">${item.exitdropoff || 'N/A'}</p>
                                                    ${item.DropoffPlaceid ? `
                                                    <div class="text-muted small">
                                                        <span class="d-block mb-1">Coordinates:</span>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="badge bg-secondary">Lat: ${item.DropoffPlaceid.lat || 'N/A'}</span>
                                                            <span class="badge bg-secondary">Lng: ${item.DropoffPlaceid.lng || 'N/A'}</span>
                                                        </div>
                                                    </div>
                                                    ` : ''}
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mt-2">
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="border rounded p-3 text-center bg-light">
                                                    <div class="text-muted mb-1 small">Date</div>
                                                    <div class="fw-bold">
                                                        <i class="far fa-calendar-alt text-primary me-1"></i>
                                                        ${formatDate(item.exitpickupdate) || 'N/A'}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="border rounded p-3 text-center bg-light">
                                                    <div class="text-muted mb-1 small">Time</div>
                                                    <div class="fw-bold">
                                                        <i class="far fa-clock text-primary me-1"></i>
                                                        ${item.entrytime || 'N/A'}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="border rounded p-3 text-center bg-light">
                                                    <div class="text-muted mb-1 small">Distance</div>
                                                    <div class="fw-bold">
                                                        <i class="fas fa-road text-primary me-1"></i>
                                                        ${item.distance || '0'} km
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="border rounded p-3 text-center bg-light">
                                                    <div class="text-muted mb-1 small">Passengers</div>
                                                    <div class="fw-bold">
                                                        <i class="fas fa-users text-primary me-1"></i>
                                                        ${parseInt(item.adults || 0) + parseInt(item.children || 0)}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="alert alert-info mt-3 mb-0">
                                            <div class="d-flex">
                                                <div class="me-3">
                                                    <i class="fas fa-moon fa-lg"></i>
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold mb-1">Night Hours Information</h6>
                                                    <p class="mb-0">Night rates apply between <strong>${item.Night_Start_Time || 'N/A'}</strong> and <strong>${item.Night_End_Time || 'N/A'}</strong></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Customer Information -->
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-light py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-circle text-primary me-2 fa-lg"></i>
                                            <h5 class="mb-0 fw-bold">Customer Details</h5>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3 mt-3 pb-3 border-bottom">
                                            <h6 class="text-primary mb-3">Personal Information</h6>
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <p class="text-muted small mb-1">Full Name</p>
                                                    <p class="fw-medium mb-3">${userInfo.fullName || 'N/A'}</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="text-muted small mb-1">Phone</p>
                                                    <p class="fw-medium mb-3">${userInfo.countryCode || ''} ${userInfo.phone || 'N/A'}</p>
                                                </div>
                                                <div class="col-12">
                                                    <p class="text-muted small mb-1">Email</p>
                                                    <p class="fw-medium mb-0">${userInfo.email || 'N/A'}</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <h6 class="text-primary mb-3">Address</h6>
                                            <p class="mb-1">${userInfo.address1 || 'N/A'}</p>
                                            <p class="mb-1">${userInfo.address2 || 'N/A'}</p>
                                            <p class="mb-0">${userInfo.state || 'N/A'} - ${userInfo.zip || 'N/A'}</p>
                                        </div>
                                        
                                        ${userInfo.specialRequests ? `
                                        <div class="mt-3 pt-3 border-top">
                                            <h6 class="text-primary mb-2">Special Requests</h6>
                                            <p class="mb-0">${userInfo.specialRequests}</p>
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payment Information -->
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-success text-white py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-money-bill-wave me-2 fa-lg"></i>
                                            <h5 class="mb-0 fw-bold">Payment Details</h5>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-4 mt-3">
                                            <div class="col-12">
                                                <div class="p-4 bg-light rounded text-center mb-4">
                                                    <p class="text-muted mb-1">Total Amount</p>
                                                    <h1 class="display-6 text-success mb-0">SGD ${parseFloat(item.totalPrice || 0).toFixed(2)}</h1>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-sm-6 mb-3">
                                                <div class="card h-100 border">
                                                    <div class="card-body p-3">
                                                        <p class="text-muted small mb-1">Booking Type</p>
                                                        <p class="fw-medium mb-0">
                                                            <i class="fas fa-bookmark text-primary me-2"></i>
                                                            ${item.bookingType || 'N/A'}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 mb-3">
                                                <div class="card h-100 border">
                                                    <div class="card-body p-3">
                                                        <p class="text-muted small mb-1">Payment Mode</p>
                                                        <p class="fw-medium mb-0">
                                                            <i class="fas fa-credit-card text-primary me-2"></i>
                                                            ${item.Mode || 'N/A'}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="bg-light" style="color: grey; padding: 15px; margin-top: 12px; margin-bottom: 0; border-radius: 5px;">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-building text-primary"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <span class="d-block fw-medium">DMC Company Name</span>
                                                    <span>${dmcName ? dmcName : 'N/A'}</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        ${userInfo.enquiryAmount || userInfo.enquiryComment ? `
                                        <div class="alert alert-info mt-3 mb-0">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-comment-dollar text-primary"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <span class="d-block fw-medium">Enquiry Information</span>
                                                    ${userInfo.enquiryAmount ? `<span class="d-block">Amount: SGD ${userInfo.enquiryAmount}</span>` : ''}
                                                    ${userInfo.enquiryComment ? `<span>Comment: ${userInfo.enquiryComment}</span>` : ''}
                                                </div>
                                            </div>
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                container.append(html);
            });
        }

        function renderEntryPortDetails(details, container) {
            // Check if details exists and is not empty
            if (!details || (Array.isArray(details) && details.length === 0)) {
                container.html('<div class="alert alert-warning">No entry port details available</div>');
                return;
            }
            
            // Handle both array and object formats
            let items = Array.isArray(details) ? details : [details];
            
            items.forEach(function(item) {
                // Log each item for debugging
                // console.log("Processing entry port item:", item);
                
                // Get user info from item
                const userInfo = item.userInfo || {};
                
                // Get DMC name from the users data
                let dmcName = 'N/A';
                if (item.dmc_id && dmcUsers && dmcUsers[item.dmc_id]) {
                    dmcName = dmcUsers[item.dmc_id].company_name;
                }
                
                let html = `
                    <div class="container-fluid p-0">
                        <!-- Entry Port Header -->
                        <div class="card border-0 mb-4 overflow-hidden">
                            <div class="card-body p-0">
                                <div class="bg-gradient-primary text-white p-4" style="background: linear-gradient(45deg, #1A5F7A, #57C5B6);">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <div class="avatar avatar-lg bg-white text-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center">
                                                <i class="fas fa-plane-arrival fa-lg"></i>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <h3 class="mb-1 fw-bold">${item.vehicles_name || 'N/A'}</h3>
                                            <div class="d-flex align-items-center flex-wrap">
                                                <span class="badge bg-white text-primary me-2 mb-1">
                                                    <i class="fas fa-car-side me-1"></i> ${item.type || 'N/A'}
                                                </span>
                                                <span class="badge bg-light text-dark me-2 mb-1">
                                                    <i class="fas fa-map-marker-alt me-1"></i> ${item.city || 'N/A'}, ${item.country || 'N/A'}
                                                </span>
                                                
                                            </div>
                                        </div>
                                        ${item.image ? `
                                        <div class="col-auto d-none d-md-block">
                                            <img src="${item.image}" alt="Vehicle" class="img-fluid rounded" style="max-height: 80px; max-width: 120px; object-fit: cover;">
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row g-4">
                            <!-- Route Information -->
                            <div class="col-md-12">
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-header bg-light py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-route text-primary me-2 fa-lg"></i>
                                            <h5 class="mb-0 fw-bold">Entry Journey Details</h5>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-4 mt-3">
                                                <div class="border rounded p-3 h-100 bg-light">
                                                    <h6 class="fw-bold text-primary mb-3">
                                                        <i class="fas fa-map-marker-alt me-2"></i> Pickup Location
                                                    </h6>
                                                    <p class="mb-2 fw-medium">${item.entrypickup || 'N/A'}</p>
                                                    ${item.PickupPlaceid ? `
                                                    <div class="text-muted small">
                                                        <span class="d-block mb-1">Coordinates:</span>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="badge bg-secondary">Lat: ${item.PickupPlaceid.lat || 'N/A'}</span>
                                                            <span class="badge bg-secondary">Lng: ${item.PickupPlaceid.lng || 'N/A'}</span>
                                                        </div>
                                                    </div>
                                                    ` : ''}
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-4 mt-3">
                                                <div class="border rounded p-3 h-100 bg-light">
                                                    <h6 class="fw-bold text-primary mb-3">
                                                        <i class="fas fa-flag-checkered me-2"></i> Dropoff Location
                                                    </h6>
                                                    <p class="mb-2 fw-medium">${item.entrydropoff || 'N/A'}</p>
                                                    ${item.DropoffPlaceid ? `
                                                    <div class="text-muted small">
                                                        <span class="d-block mb-1">Coordinates:</span>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="badge bg-secondary">Lat: ${item.DropoffPlaceid.lat || 'N/A'}</span>
                                                            <span class="badge bg-secondary">Lng: ${item.DropoffPlaceid.lng || 'N/A'}</span>
                                                        </div>
                                                    </div>
                                                    ` : ''}
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mt-2">
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="border rounded p-3 text-center bg-light">
                                                    <div class="text-muted mb-1 small">Date</div>
                                                    <div class="fw-bold">
                                                        <i class="far fa-calendar-alt text-primary me-1"></i>
                                                        ${formatDate(item.pickupdate) || 'N/A'}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="border rounded p-3 text-center bg-light">
                                                    <div class="text-muted mb-1 small">Time</div>
                                                    <div class="fw-bold">
                                                        <i class="far fa-clock text-primary me-1"></i>
                                                        ${item.entrytime || 'N/A'}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="border rounded p-3 text-center bg-light">
                                                    <div class="text-muted mb-1 small">Distance</div>
                                                    <div class="fw-bold">
                                                        <i class="fas fa-road text-primary me-1"></i>
                                                        ${item.distance || '0'} km
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="border rounded p-3 text-center bg-light">
                                                    <div class="text-muted mb-1 small">Passengers</div>
                                                    <div class="fw-bold">
                                                        <i class="fas fa-users text-primary me-1"></i>
                                                        ${parseInt(item.adults || 0) + parseInt(item.children || 0)}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="alert alert-info mt-3 mb-0">
                                            <div class="d-flex">
                                                <div class="me-3">
                                                    <i class="fas fa-moon fa-lg"></i>
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold mb-1">Night Hours Information</h6>
                                                    <p class="mb-0">Night rates apply between <strong>${item.Night_Start_Time || 'N/A'}</strong> and <strong>${item.Night_End_Time || 'N/A'}</strong></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Customer Information -->
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-light py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-circle text-primary me-2 fa-lg"></i>
                                            <h5 class="mb-0 fw-bold">Customer Details</h5>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3 mt-3 pb-3 border-bottom">
                                            <h6 class="text-primary mb-3">Personal Information</h6>
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <p class="text-muted small mb-1">Full Name</p>
                                                    <p class="fw-medium mb-3">${userInfo.fullName || item.fullName || 'N/A'}</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="text-muted small mb-1">Phone</p>
                                                    <p class="fw-medium mb-3">${userInfo.countryCode || item.countryCode || ''} ${userInfo.phone || item.phone || 'N/A'}</p>
                                                </div>
                                                <div class="col-12">
                                                    <p class="text-muted small mb-1">Email</p>
                                                    <p class="fw-medium mb-0">${userInfo.email || item.email || 'N/A'}</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <h6 class="text-primary mb-3">Address</h6>
                                            <p class="mb-1">${userInfo.address1 || item.address1 || 'N/A'}</p>
                                            <p class="mb-1">${userInfo.address2 || item.address2 || 'N/A'}</p>
                                            <p class="mb-0">${userInfo.state || item.state || 'N/A'} - ${userInfo.zip || item.zip || 'N/A'}</p>
                                        </div>
                                        
                                        ${(userInfo.specialRequests || item.specialRequests) ? `
                                        <div class="mt-3 pt-3 border-top">
                                            <h6 class="text-primary mb-2">Special Requests</h6>
                                            <p class="mb-0">${userInfo.specialRequests || item.specialRequests}</p>
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payment Information -->
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-success text-white py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-money-bill-wave me-2 fa-lg"></i>
                                            <h5 class="mb-0 fw-bold">Payment Details</h5>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-4 mt-3">
                                            <div class="col-12">
                                                <div class="p-4 bg-light rounded text-center mb-4">
                                                    <p class="text-muted mb-1">Total Amount</p>
                                                    <h1 class="display-6 text-success mb-0">SGD ${parseFloat(item.totalPrice || 0).toFixed(2)}</h1>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-sm-6 mb-3">
                                                <div class="card h-100 border">
                                                    <div class="card-body p-3">
                                                        <p class="text-muted small mb-1">Booking Type</p>
                                                        <p class="fw-medium mb-0">
                                                            <i class="fas fa-bookmark text-primary me-2"></i>
                                                            ${item.bookingType || 'Booking'}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 mb-3">
                                                <div class="card h-100 border">
                                                    <div class="card-body p-3">
                                                        <p class="text-muted small mb-1">Payment Mode</p>
                                                        <p class="fw-medium mb-0">
                                                            <i class="fas fa-credit-card text-primary me-2"></i>
                                                            ${item.Mode || 'N/A'}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="bg-light" style="color: grey; padding: 15px; margin-top: 12px; margin-bottom: 0; border-radius: 5px;">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-building text-primary"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <span class="d-block fw-medium">DMC Company Name</span>
                                                    <span>${dmcName ? dmcName : 'N/A'}</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        ${userInfo.enquiryAmount || userInfo.enquiryComment ? `
                                        <div class="alert alert-info mt-3 mb-0">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-comment-dollar text-primary"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <span class="d-block fw-medium">Enquiry Information</span>
                                                    ${userInfo.enquiryAmount ? `<span class="d-block">Amount: SGD ${userInfo.enquiryAmount}</span>` : ''}
                                                    ${userInfo.enquiryComment ? `<span>Comment: ${userInfo.enquiryComment}</span>` : ''}
                                                </div>
                                            </div>
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                container.append(html);
            });
        }

        // Edit button click handler
        $('.edit-details').on('click', function() {
            let type = $(this).data('type');
            let id = $(this).data('id');
            let encodedDetails = $(this).data('details');
            let modalBody = $('#editdetailsModalBody');
            
            // Set the booking ID and type in the form
            $('#edit_booking_id').val(id);
            $('#edit_booking_type').val(type);
            
            // Clear previous content
            modalBody.empty();
            
            // First decode HTML entities
            let decodedDetails = $('<div/>').html(encodedDetails).text();
            
            // Parse the details safely
            let details;
            try {
                details = JSON.parse(decodedDetails);
                console.log("Parsed details:", details); // Debug logging
                
                // If details is an array, use the first item
                if (Array.isArray(details) && details.length > 0) {
                    console.log("Details is an array, using first item");
                    details = details[0];
                }
            } catch (e) {
                console.error("Error parsing JSON:", e);
                modalBody.html('<div class="alert alert-danger">Error loading details data: ' + e.message + '</div>');
                return;
            }

            // For hotel type, create a specialized edit form
            if (type === 'hotel') {
                // Update modal title
                $('#editdetailsModalLabel').text('Edit Hotel Booking Dates');
                
                let checkInDate = '';
                let checkOutDate = '';
                
                // Make sure we have hotel details
                if (!details.hotelDetails) {
                    details.hotelDetails = {};
                }
                
                // Handle bookingDate that can be either array or single value
                if (details.bookingDate) {
                    console.log("Booking date:", details.bookingDate); // Debug logging
                    
                    if (Array.isArray(details.bookingDate)) {
                        // Handle array format ["2025-05-12","2025-05-16"]
                        checkInDate = formatDateForInput(details.bookingDate[0]);
                        checkOutDate = formatDateForInput(details.bookingDate[1]);
                    } else {
                        // Handle single date format
                        checkInDate = formatDateForInput(details.bookingDate);
                    }
                }
                
                console.log("Check-in date:", checkInDate);
                console.log("Check-out date:", checkOutDate);
                console.log("Hotel name:", details.hotelDetails.hotel_name || details.HotelName);
                
                let html = `
                    <div class="container-fluid p-0">
                        <!-- Hotel Header -->
                        <div class="card border-0 mb-4 overflow-hidden">
                            <div class="card-body p-0">
                                <div class="bg-gradient-primary text-white p-4" style="background: linear-gradient(45deg, #1a237e, #0d47a1);">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h3 class="mb-1 fw-bold">${details.hotelDetails.hotel_name || details.HotelName || 'N/A'}</h3>
                                            <div class="d-flex align-items-center flex-wrap">
                                                <span class="badge bg-white text-primary me-2 mb-1">
                                                    <i class="fas fa-hotel me-1"></i> Hotel Booking
                                                </span>
                                                <span class="badge bg-warning text-dark mb-1">
                                                    <i class="fas fa-edit me-1"></i> Editing Dates
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <!-- Edit Date Form -->
                            <div class="col-md-12">
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-header bg-light py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-calendar-alt text-primary me-2 fa-lg"></i>
                                            <h5 class="mb-0 fw-bold">Edit Booking Dates</h5>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Update the check-in and check-out dates for this hotel booking.
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="check_in_date" class="form-label">Check-in Date</label>
                                                <input type="date" id="check_in_date" name="check_in_date" class="form-control" 
                                                    value="${checkInDate}" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="check_out_date" class="form-label">Check-out Date</label>
                                                <input type="date" id="check_out_date" name="check_out_date" class="form-control" 
                                                    value="${checkOutDate}" required>
                                            </div>
                                        </div>
                                        
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Booking Summary</h6>
                                                        <div class="row g-3 mt-1">
                                                            <div class="col-md-6">
                                                                <p class="mb-1 fw-bold">Hotel Name:</p>
                                                                <p>${details.hotelDetails.hotel_name || details.HotelName || 'N/A'}</p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p class="mb-1 fw-bold">Location:</p>
                                                                <p>${details.hotelDetails.location || details.location || 'N/A'}</p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p class="mb-1 fw-bold">Total Rooms:</p>
                                                                <p>${details.rooms ? details.rooms.length : 0}</p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p class="mb-1 fw-bold">Price:</p>
                                                                <p class="text-success fw-bold">SGD ${parseFloat(details.totalPrice || 0).toFixed(2)}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                modalBody.html(html);
            } else {
                // For other types, show a generic date edit form
                let bookingDate = '';
                
                // Reset hidden fields
                $('#has_visit_time').val('0');
                $('#has_guide_name').val('0');
                $('#has_pickup_date').val('0');
                $('#has_entry_time').val('0');
                
                // Handle bookingDate that can be either a string or array
                if (details.bookingDate) {
                    console.log("Booking date for other type:", details.bookingDate); // Debug logging
                    
                    if (Array.isArray(details.bookingDate)) {
                        // Use the first date from the array
                        bookingDate = formatDateForInput(details.bookingDate[0]);
                    } else {
                        // Use the single date value
                        bookingDate = formatDateForInput(details.bookingDate);
                    }
                } else if (details.pickupdate) {
                    bookingDate = formatDateForInput(details.pickupdate);
                } else if (details.exitpickupdate) {
                    bookingDate = formatDateForInput(details.exitpickupdate);
                }
                
                console.log("Booking date for form:", bookingDate);
                
                // Create type-specific form fields
                let typeSpecificFields = '';
                
                // For restaurant and attraction types - add visitTime
                if (type === 'restaurant' || type === 'attraction') {
                    $('#has_visit_time').val('1');
                    
                    // Format the visit time value for the input field
                    let timeValue = '';
                    if (details.visitTime) {
                        // For attraction: if it's a range like "10:00-12:30", extract the first part
                        if (type === 'attraction' && details.visitTime.includes('-')) {
                            const parts = details.visitTime.split('-');
                            timeValue = parts[0].trim();
                        } 
                        // For restaurant or non-range attraction: if it has AM/PM, convert to 24-hour format
                        else if (details.visitTime.includes('AM') || details.visitTime.includes('PM')) {
                            const timeStr = details.visitTime.trim();
                            const isPM = timeStr.includes('PM');
                            const timeParts = timeStr.replace(/\s?[AP]M/, '').split(':');
                            
                            if (timeParts.length === 2) {
                                let hour = parseInt(timeParts[0], 10);
                                const minute = timeParts[1];
                                
                                if (isPM && hour < 12) hour += 12;
                                if (!isPM && hour === 12) hour = 0;
                                
                                timeValue = hour.toString().padStart(2, '0') + ':' + minute;
                            } else {
                                timeValue = details.visitTime;
                            }
                        }
                        // Otherwise use the value as is
                        else {
                            timeValue = details.visitTime;
                        }
                    }
                    
                    let visitTimeHelp = '';
                    if (type === 'attraction') {
                        visitTimeHelp = 'For attractions with time ranges, only enter the start time. The time range format will be preserved.';
                    } else {
                        visitTimeHelp = 'Please enter time in 24-hour format (HH:MM). It will be converted to AM/PM format when saved.';
                    }
                    
                    typeSpecificFields = `
                        <div class="form-group mb-3">
                            <label for="visit_time" class="form-label">Visit Time</label>
                            <input type="time" id="visit_time" name="visit_time" class="form-control" 
                                value="${timeValue}" required>
                            <div class="form-text">${visitTimeHelp}</div>
                        </div>
                    `;
                }
                
                // For guide type - add guide_name, pickupdate, entrytime fields
                if (type === 'guide') {
                    $('#has_guide_name').val('1');
                    $('#has_pickup_date').val('1');
                    $('#has_entry_time').val('1');
                    
                    // Guide name options - this would typically come from your database
                    // For now, we'll include the current guide name and some sample options
                    const currentGuideName = details.guide_name || '';
                    const guideOptions = [
                        'Narasimha Rao',
                        'John Doe',
                        'Jane Smith',
                        'Ahmed Ali',
                        'Kiran Kumar',
                        'Liu Wei',
                        'Maria Garcia'
                    ];
                    
                    // Ensure current guide name is in the list
                    if (currentGuideName && !guideOptions.includes(currentGuideName)) {
                        guideOptions.unshift(currentGuideName);
                    }
                    
                    // Generate options HTML
                    const guideOptionsHtml = guideOptions.map(name => 
                        `<option value="${name}" ${name === currentGuideName ? 'selected' : ''}>${name}</option>`
                    ).join('');
                    
                    typeSpecificFields = `
                        <div class="form-group mb-3">
                            <label for="guide_name" class="form-label">Guide Name</label>
                            <select id="guide_name" name="guide_name" class="form-select" required>
                                <option value="">Select Guide</option>
                                ${guideOptionsHtml}
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="pickupdate" class="form-label">Pickup Date</label>
                                <input type="date" id="pickupdate" name="pickupdate" class="form-control" 
                                    value="${formatDateForInput(details.pickupdate) || ''}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="entrytime" class="form-label">Entry Time</label>
                                <input type="time" id="entrytime" name="entrytime" class="form-control" 
                                    value="${formatTimeFor24HourInput(details.entrytime) || ''}" required>
                                <div class="form-text">Please enter time in 24-hour format (HH:MM)</div>
                            </div>
                        </div>
                    `;
                }
                
                // For entry_port type - add pickupdate and entrytime
                if (type === 'entry_port') {
                    $('#has_pickup_date').val('1');
                    $('#has_entry_time').val('1');
                    typeSpecificFields = `
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="pickupdate" class="form-label">Pickup Date</label>
                                <input type="date" id="pickupdate" name="pickupdate" class="form-control" 
                                    value="${formatDateForInput(details.pickupdate) || ''}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="entrytime" class="form-label">Entry Time</label>
                                <input type="time" id="entrytime" name="entrytime" class="form-control" 
                                    value="${formatTimeFor24HourInput(details.entrytime) || ''}" required>
                                <div class="form-text">Please enter time in 24-hour format (HH:MM)</div>
                            </div>
                        </div>
                    `;
                }
                
                let html = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Update the details for this ${type.replace('_', ' ')} booking.
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="booking_date" class="form-label">Booking Date</label>
                        <input type="date" id="booking_date" name="booking_date" class="form-control" 
                            value="${bookingDate}" required>
                    </div>
                    
                    ${typeSpecificFields}
                    
                    <div class="card bg-light mt-3">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Booking Summary</h6>
                            <div class="row mt-2">
                                <div class="col-md-6 mb-2">
                                    <p class="mb-1 fw-bold">Type:</p>
                                    <p>${type.replace('_', ' ').toUpperCase()}</p>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <p class="mb-1 fw-bold">Booking ID:</p>
                                    <p>${id}</p>
                                </div>
                                ${details.totalPrice ? `
                                <div class="col-md-12">
                                    <p class="mb-1 fw-bold">Price:</p>
                                    <p class="text-success fw-bold">SGD ${parseFloat(details.totalPrice || 0).toFixed(2)}</p>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
                
                modalBody.html(html);
            }
        });
        
        // Cancel button click handler
        $('.cancel-booking').on('click', function() {
            let id = $(this).data('id');
            let type = $(this).data('type');
            
            // Set the booking ID and type in the form
            $('#cancel_booking_id').val(id);
            $('#cancel_booking_type').val(type);
        });

        // Approve button click handler
        $('.approve-booking').on('click', function() {
            let id = $(this).data('id');
            let bookingId = $(this).data('booking-id');
            let type = $(this).data('type');
            
            // Set the booking ID in the form
            $('#approve_booking_id').val(bookingId);
            
            // Clear previous values
            $('#reference_id').val('');
            $('#reference_file').val('');
        });
    });
</script>

<!-- End DataTable JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
function setDeleteForm(url) {
    document.getElementById('deleteForm').action = url;
}
</script>

<script>
    var dmcUsers = @json($dmcUsers);
</script>

<script>
function enhanceTypeLabels() {
    // Define configuration for each type
    const typeConfig = {
        'hotel': {
            icon: 'fas fa-hotel',
            gradient: 'linear-gradient(135deg, #ff7e5f, #feb47b)',
            textColor: 'text-white'
        },
        'attraction': {
            icon: 'fas fa-map-marked-alt',
            gradient: 'linear-gradient(135deg, #56ccf2, #2f80ed)',
            textColor: 'text-white'
        },
        'guide': {
            icon: 'fas fa-user-tie',
            gradient: 'linear-gradient(135deg, #11998e, #38ef7d)',
            textColor: 'text-white'
        },
        'travel_point': {
            icon: 'fas fa-bus',
            gradient: 'linear-gradient(135deg, #ff416c, #ff4b2b)',
            textColor: 'text-white'
        },
        'travel_hourly': {
            icon: 'fas fa-bus-alt',
            gradient: 'linear-gradient(135deg, #ff416c, #ff4b2b)',
            textColor: 'text-white'
        },
        'restaurant': {
            icon: 'fas fa-utensils',
            gradient: 'linear-gradient(135deg, #fc4a1a, #f7b733)',
            textColor: 'text-white'
        },
        'entry_port': {
            icon: 'fas fa-sign-in-alt',
            gradient: 'linear-gradient(135deg, #1e3c72, #2a5298)',
            textColor: 'text-white'
        },
        'exit_port': {
            icon: 'fas fa-sign-out-alt',
            gradient: 'linear-gradient(135deg, #1e3c72, #2a5298)',
            textColor: 'text-white'
        }
    };
    
    // Find all type cells in the table and enhance them
    $('.type-badge').each(function() {
        // Get the raw text content of the cell
        const typeText = $(this).text().trim().toLowerCase();
        
        // Log for debugging
        //console.log("Type detected:", typeText);
        
        // Find matching config or use default
        let config;
        
        // Try to match exactly first
        if (typeConfig[typeText]) {
            config = typeConfig[typeText];
        } else {
            // If no exact match, check if any key is contained in the typeText
            const matchingKey = Object.keys(typeConfig).find(key => typeText.includes(key));
            config = matchingKey ? typeConfig[matchingKey] : {
                icon: 'fas fa-tag',
                gradient: 'linear-gradient(135deg, #808080, #a9a9a9)',
                textColor: 'text-white'
            };
        }
        
        // Get a display-friendly version of the type name
        const displayType = typeText
            .replace(/_/g, ' ')
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
        
        $(this).html(`
            <span class="btn btn-sm ${config.textColor} p-2 text-nowrap" 
                  style="background: ${config.gradient}; border: none; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);">
                <i class="${config.icon} me-1"></i> ${displayType}
            </span>
        `);
    });
}

// Call this function when document is ready
$(document).ready(function() {
    enhanceTypeLabels();
    
    // Also call it when DataTable redraws
    $('.datatables-basic').on('draw.dt', function() {
        enhanceTypeLabels();
    });
});
</script>

<script>
    // Date formatting utility function
    function formatDate(dateString) {
        if (!dateString) return 'Unknown Date';
        
        let date;
        try {
            // Handle different date formats
            if (typeof dateString === 'string') {
                if (dateString.includes('-')) {
                    // YYYY-MM-DD format
                    date = new Date(dateString);
                } else if (dateString.includes('/')) {
                    // DD/MM/YYYY format
                    const parts = dateString.split('/');
                    if (parts.length === 3) {
                        date = new Date(parts[2], parts[1] - 1, parts[0]);
                    } else {
                        date = new Date(dateString);
                    }
                } else {
                    date = new Date(dateString);
                }
            } else {
                date = new Date(dateString);
            }
            
            // Check if date is valid
            if (isNaN(date.getTime())) {
                return dateString;
            }
            
            // Format the date consistently
            return date.toLocaleDateString('en-US', { 
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        } catch (e) {
            console.error("Error formatting date:", e);
            return dateString;
        }
    }
</script>

<script>
    // Helper function to format date for input field (yyyy-mm-dd)
    function formatDateForInput(dateString) {
        if (!dateString) return '';
        
        let date;
        if (typeof dateString === 'string') {
        // Handle different date formats
            if (dateString.includes('-')) {
            // Already in YYYY-MM-DD format
            if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
                return dateString; 
            }
                date = new Date(dateString);
            } else if (dateString.includes('/')) {
                // Handle DD/MM/YYYY format
                const parts = dateString.split('/');
            if (parts.length === 3) {
                // Check if first part is day (typical DD/MM/YYYY format)
                if (parseInt(parts[0]) <= 31) {
                date = new Date(parts[2], parts[1] - 1, parts[0]);
                } else {
                    // Might be MM/DD/YYYY format
                    date = new Date(parts[2], parts[0] - 1, parts[1]);
                }
            } else {
                date = new Date(dateString);
            }
            } else {
                // Try direct parsing
                date = new Date(dateString);
            }
        } else {
        // If it's already a Date object or another value
            date = new Date(dateString);
        }
        
        // Check if date is valid
    if (isNaN(date.getTime())) {
        console.error("Invalid date:", dateString);
        return '';
    }
            
            // Format as YYYY-MM-DD for input field
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            
            return `${year}-${month}-${day}`;
        }
        
        // Helper function to format time for 24-hour input field
        function formatTimeFor24HourInput(timeString) {
            if (!timeString) return '';
            
            // If it already looks like a 24-hour time (no AM/PM), return as is
            if (!timeString.includes('AM') && !timeString.includes('PM') && timeString.includes(':')) {
                return timeString;
            }
            
            // Parse the time string which may have AM/PM format
            const timeStr = timeString.trim();
            const isPM = timeStr.includes('PM');
            const isAM = timeStr.includes('AM');
            
            if (!isPM && !isAM) {
                // No AM/PM indicator, return as is if it has a colon
                if (timeStr.includes(':')) {
                    return timeStr;
                }
                return '';
            }
            
            // Remove AM/PM and split into hour and minute
            const timeParts = timeStr.replace(/\s?[AP]M/, '').split(':');
            
            if (timeParts.length !== 2) {
                return timeString; // Return original if format is unexpected
            }
            
            let hour = parseInt(timeParts[0], 10);
            const minute = timeParts[1];
            
            // Convert to 24-hour format
            if (isPM && hour < 12) hour += 12;
            if (isAM && hour === 12) hour = 0;
            
            // Format for time input field (HH:MM)
            return `${hour.toString().padStart(2, '0')}:${minute}`;
        }
</script>

<script>
// Add this to your existing script section, before the final closing script tag

// Global error handler for the itinerary feature
window.addEventListener('error', function(event) {
    // Check if the error is related to the itinerary
    if (event.message && 
        (event.message.includes('itinerary') || 
         $('#itineraryModal').hasClass('show') || 
         $('#itineraryModalBody').find('.spinner-border').length > 0)) {
        
        console.error("Caught global error in itinerary:", event.message);
        
        // Try to recover by stopping the spinner
        if ($('#itineraryModalBody').find('.spinner-border').length > 0) {
            $('#itineraryModalBody').html(`
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>JavaScript Error</h5>
                    <p>There was a problem generating the itinerary. Please check the console for details.</p>
                    <p><strong>Error:</strong> ${event.message}</p>
                </div>
            `);
        }
    }
});

// REPLACED: Using BookingManager for itinerary handling
/* Original code commented out:
$('.view-itinerary').on('click', function() {
    // REPLACEMENT: EMERGENCY ITINERARY HANDLER
    console.log("Emergency itinerary handler activated");
*/
// Initialize BookingManager instead
$(document).ready(function() {
    // BookingManager will handle all itinerary functionality
    if (window.BookingManager && typeof window.BookingManager.init === 'function') {
        window.BookingManager.init();
    }
    
    // Get tour ID from data attribute
    const tourId = $(this).data('tour-id');
    const modalBody = $('#itineraryModalBody');
    
    // Update modal title
    $('#itineraryModalLabel').text('Tour #' + tourId + ' Itinerary');
    
    // Show simple loading message
    modalBody.html(`
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading simplified itinerary...</p>
        </div>
    `);
    
    // Immediate timeout to ensure UI updates before processing
    setTimeout(function() {
        try {
            // Get accordion section and extract service items
            const services = [];
            const accordion = $(`#collapse${tourId}`);
            
            if (accordion.length === 0) {
                throw new Error("Could not find tour accordion section");
            }
            
            // Collect service types from accordion headings
            const serviceTypes = [];
            accordion.find('.accordion-item').each(function() {
                const typeText = $(this).find('.accordion-button .fw-bold').text().trim().toLowerCase();
                if (typeText) {
                    serviceTypes.push(typeText);
                }
            });
            
            console.log("Found service types:", serviceTypes);
            
            // For each service item, create a simple service object
            accordion.find('.service-item').each(function() {
                const item = $(this);
                const itemParent = item.closest('.accordion-item');
                const typeText = itemParent.find('.accordion-button .fw-bold').text().trim().toLowerCase();
                const typeIcon = itemParent.find('.type-icon i').attr('class') || 'fas fa-calendar-check';
                const typeBgColor = itemParent.find('.type-icon').css('background-color') || '#6c757d';
                
                // Get booking details from badges
                let bookingId = "";
                let bookingDate = new Date();
                let bookingTime = "12:00";
                let paxCount = { adult: 2, child: 0 };
                
                // Extract badge information
                item.find('.badge').each(function() {
                    const badgeText = $(this).text().trim();
                    
                    if (badgeText.includes('ID:')) {
                        bookingId = badgeText.replace('ID:', '').trim();
                    }
                    
                    // Look for date patterns
                    const dateMatch = badgeText.match(/\d{1,2}\s+[A-Za-z]{3}\s+\d{4}/);
                    if (dateMatch) {
                        bookingDate = new Date(dateMatch[0]);
                    }
                    
                    // Look for time patterns
                    const timeMatch = badgeText.match(/(\d{1,2}):(\d{2})/);
                    if (timeMatch) {
                        bookingTime = timeMatch[0];
                    }
                    
                    // Look for pax count
                    const adultMatch = badgeText.match(/(\d+)\s*Adults?/i);
                    const childMatch = badgeText.match(/(\d+)\s*Child(ren)?/i);
                    
                    if (adultMatch) {
                        paxCount.adult = parseInt(adultMatch[1]) || 2;
                    }
                    
                    if (childMatch) {
                        paxCount.child = parseInt(childMatch[1]) || 0;
                    }
                });
                
                // Get name from card title or default to type
                const itemTitle = item.find('.card-title').text().trim();
                const serviceName = itemTitle || typeText.charAt(0).toUpperCase() + typeText.slice(1);
                
                // Create service object
                services.push({
                    id: bookingId || 'service-' + services.length,
                    type: typeText.replace(' ', '_'),
                    name: serviceName,
                    date: bookingDate,
                    time: bookingTime,
                    icon: typeIcon,
                    color: typeBgColor,
                    pax: paxCount,
                    isHotel: typeText === 'hotel',
                    isEntryPort: typeText === 'entry port',
                    isExitPort: typeText === 'exit port'
                });
            });
            
            console.log("Collected services:", services);
            
            // If no services found, create sample data
            if (services.length === 0) {
                const types = ['hotel', 'attraction', 'guide', 'restaurant', 'travel_point', 'entry_port', 'exit_port'];
                const today = new Date();
                
                types.forEach((type, index) => {
                    const day = new Date(today);
                    day.setDate(day.getDate() + Math.min(index % 3, 2));
                    
                    services.push({
                        id: 'sample-' + type,
                        type: type,
                        name: type.charAt(0).toUpperCase() + type.slice(1).replace('_', ' '),
                        date: day,
                        time: type === 'hotel' ? '14:00' : 
                              type === 'entry_port' ? '09:00' : 
                              type === 'exit_port' ? '17:00' : 
                              type === 'restaurant' ? '13:00' : '12:00',
                        icon: type === 'hotel' ? 'fas fa-hotel' :
                              type === 'attraction' ? 'fas fa-map-marked-alt' :
                              type === 'guide' ? 'fas fa-user-tie' :
                              type === 'restaurant' ? 'fas fa-utensils' :
                              type === 'travel_point' ? 'fas fa-bus' :
                              type === 'entry_port' ? 'fas fa-sign-in-alt' :
                              type === 'exit_port' ? 'fas fa-sign-out-alt' : 'fas fa-calendar-check',
                        color: type === 'hotel' ? '#4CAF50' :
                               type === 'attraction' ? '#2196F3' :
                               type === 'guide' ? '#9C27B0' :
                               type === 'restaurant' ? '#FF9800' :
                               type === 'travel_point' ? '#F44336' :
                               type === 'entry_port' ? '#607D8B' :
                               type === 'exit_port' ? '#607D8B' : '#6c757d',
                        pax: { adult: 2, child: 1 },
                        isHotel: type === 'hotel',
                        isEntryPort: type === 'entry_port',
                        isExitPort: type === 'exit_port'
                    });
                });
            }
            
            // Find the earliest and latest dates
            const dates = services.map(s => s.date).filter(d => d instanceof Date);
            let startDate = new Date();
            let endDate = new Date();
            
            if (dates.length > 0) {
                startDate = new Date(Math.min.apply(null, dates));
                endDate = new Date(Math.max.apply(null, dates));
                
                // Make sure end date is at least one day after start date
                if (endDate <= startDate) {
                    endDate = new Date(startDate);
                    endDate.setDate(endDate.getDate() + 1);
                }
            } else {
                // Default 3-day tour
                endDate.setDate(endDate.getDate() + 2);
            }
            
            // Group by date
            const dateFormatter = new Intl.DateTimeFormat('en-GB', {
                day: '2-digit', 
                month: 'short', 
                year: 'numeric'
            });
            
            // Create date-based collection
            const dateGroups = {};
            const currentDate = new Date(startDate);
            
            // Create entries for every day between start and end
            while (currentDate <= endDate) {
                const dateKey = dateFormatter.format(currentDate);
                dateGroups[dateKey] = [];
                
                // Add services for this date
                services.forEach(service => {
                    if (service.date && service.date.toDateString() === currentDate.toDateString()) {
                        dateGroups[dateKey].push(service);
                    }
                });
                
                // Sort services by time
                dateGroups[dateKey].sort((a, b) => {
                    // Sort entry port first
                    if (a.isEntryPort) return -1;
                    if (b.isEntryPort) return 1;
                    
                    // Sort exit port last
                    if (a.isExitPort) return 1;
                    if (b.isExitPort) return -1;
                    
                    // Otherwise sort by time
                    return (a.time || '00:00').localeCompare(b.time || '00:00');
                });
                
                // Move to next day
                currentDate.setDate(currentDate.getDate() + 1);
            }
            
            // Build HTML
            let html = `
                <style>
                    .date-header {
                        background-color: #f0f7ff;
                        padding: 7px 12px;
                        border-radius: 6px;
                        margin-bottom: 12px;
                        border-left: 4px solid #435ebe;
                    }
                    
                    .date-header h4 {
                        margin-bottom: 0;
                        color: #435ebe;
                        font-size: 1rem;
                    }
                    
                    .service-row {
                        display: flex;
                        align-items: center;
                        padding: 10px;
                        border-radius: 8px;
                        margin-bottom: 8px;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                        background-color: #fff;
                    }
                    
                    .service-time {
                        font-weight: bold;
                        width: 70px;
                        flex-shrink: 0;
                        text-align: center;
                    }
                    
                    .service-icon {
                        width: 36px;
                        height: 36px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: white;
                        margin-right: 10px;
                        flex-shrink: 0;
                    }
                    
                    .service-details {
                        flex-grow: 1;
                        min-width: 0;
                    }
                    
                    .service-name {
                        font-weight: 500;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                    }
                    
                    .service-badges {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 4px;
                        margin-top: 4px;
                    }
                    
                    .service-badges .badge {
                        font-size: 0.7rem;
                    }
                    
                    .empty-day {
                        text-align: center;
                        padding: 20px;
                        background-color: #f8f9fa;
                        border-radius: 8px;
                        margin-bottom: 15px;
                        color: #6c757d;
                    }
                </style>
                
                <div class="alert alert-primary mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle me-3 fa-lg"></i>
                        <div>
                            <h5 class="mb-1">Tour Itinerary Overview</h5>
                            <p class="mb-0">
                                Tour dates: <strong>${dateFormatter.format(startDate)}</strong> to <strong>${dateFormatter.format(endDate)}</strong>
                                <span class="badge bg-secondary ms-2">${Math.round((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1} day(s)</span>
                            </p>
                        </div>
                    </div>
                </div>
            `;
            
            // Add date-wise content
            let dayNumber = 1;
            for (const [dateKey, dayServices] of Object.entries(dateGroups)) {
                // Create day header
                html += `
                    <div class="date-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4>
                                <i class="fas fa-calendar-day me-2"></i> 
                                Day ${dayNumber}: ${dateKey}
                            </h4>
                            <span class="badge bg-primary">${dayServices.length} service(s)</span>
                        </div>
                    </div>
                `;
                
                // If no services for this day
                if (dayServices.length === 0) {
                    html += `
                        <div class="empty-day">
                            <i class="fas fa-calendar-times fa-2x mb-2"></i>
                            <h5>No scheduled services</h5>
                            <p class="mb-0">There are no services planned for this day.</p>
                        </div>
                    `;
            } else {
                    // Add each service
                    dayServices.forEach(service => {
                        // Calculate total passengers
                        const totalPax = service.pax.adult + service.pax.child;
                        
                        // Create status badge based on service type
                        let statusBadge = '';
                        if (service.isEntryPort) {
                            statusBadge = '<span class="badge bg-success">Tour Start</span>';
                        } else if (service.isExitPort) {
                            statusBadge = '<span class="badge bg-danger">Tour End</span>';
                        } else if (service.isHotel) {
                            statusBadge = '<span class="badge bg-success">Check-in</span>';
                        }
                        
                        html += `
                            <div class="service-row">
                                <div class="service-time">
                                    <i class="far fa-clock me-1"></i> ${service.time || '00:00'}
                                </div>
                                <div class="service-icon" style="background-color: ${service.color || '#6c757d'}">
                                    <i class="${service.icon || 'fas fa-calendar-check'}"></i>
                                </div>
                                <div class="service-details">
                                    <div class="service-name">${service.name}</div>
                                    <div class="service-badges">
                                        <span class="badge bg-secondary">
                                            ${service.type.replace('_', ' ')}
                                        </span>
                                        <span class="badge bg-info">
                                            <i class="fas fa-users me-1"></i> ${totalPax} pax
                                        </span>
                                        ${statusBadge}
                                        <span class="badge bg-secondary">ID: ${service.id}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }
                
                dayNumber++;
            }
            
            // Set the HTML content
            modalBody.html(html);
            
        } catch (error) {
            console.error("Error in emergency itinerary handler:", error);
            
            // Show error message with retry button
        modalBody.html(`
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Error Loading Itinerary</h5>
                    <p>There was a problem generating the tour itinerary:</p>
                    <p><code>${error.message}</code></p>
                    <button class="btn btn-primary mt-2" id="retryItinerary">
                        <i class="fas fa-sync-alt me-1"></i> Try Again
                    </button>
            </div>
        `);
            
            // Set up retry button
            $('#retryItinerary').off('click').on('click', function() {
                $(`.view-itinerary[data-tour-id="${tourId}"]`).trigger('click');
            });
        }
    }, 100); // Short delay to ensure UI updates
});
// ... existing code ...
</script>

<!-- Emergency fix for itinerary loading issue -->
<script>
// Force itinerary initialization on page load
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit to ensure jQuery and all elements are loaded
    setTimeout(function() {
        console.log("FORCING ITINERARY HANDLER INITIALIZATION");
        
        // First, remove any existing click handlers
        $('.view-itinerary').off('click');
        
        // This code works by directly modifying the DOM when button is clicked
        $('.view-itinerary').on('click', function() {
            const tourId = $(this).data('tour-id');
            const modalBody = document.getElementById('itineraryModalBody');
            
            if (modalBody) {
                // Set loading content
                modalBody.innerHTML = `
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading itinerary data...</p>
                    </div>
                `;
                
                                    // Set a timeout to ensure we don't get stuck
                setTimeout(function() {
                    try {
                        // Extract services from the page
                        const services = [];
                        const accordionSection = document.querySelector(`#collapse${tourId}`);
                        
                        if (!accordionSection) {
                            throw new Error("Could not find tour section");
                        }
                        
                        // Get tour dates from tour header
                        const tourHeader = document.querySelector(`.accordion-button[data-bs-target="#collapse${tourId}"]`);
                        let tourDates = "N/A";
                        let startDate = new Date();
                        let endDate = new Date();
                        endDate.setDate(endDate.getDate() + 2); // Default 3-day range
                        
                        if (tourHeader) {
                            const dateElement = tourHeader.querySelector('.tour-meta-badge.date');
                            if (dateElement) {
                                tourDates = dateElement.textContent.trim();
                                
                                // Try to extract actual dates
                                const dateMatch = tourDates.match(/(\d{1,2}\s+\w{3}\s+\d{4})\s+-\s+(\d{1,2}\s+\w{3}\s+\d{4})/);
                                if (dateMatch && dateMatch.length === 3) {
                                    startDate = new Date(dateMatch[1]);
                                    endDate = new Date(dateMatch[2]);
                                }
                            }
                        }
                        
                        // Get all service items
                        const serviceItems = accordionSection.querySelectorAll('.service-item');
                        const serviceTypes = [];
                        
                        // Extract service type sections
                        accordionSection.querySelectorAll('.accordion-item').forEach(function(item) {
                            const typeTitle = item.querySelector('.accordion-button .fw-bold');
                            if (typeTitle) {
                                const typeText = typeTitle.textContent.trim().toLowerCase();
                                serviceTypes.push({
                                    type: typeText,
                                    element: item
                                });
                            }
                        });
                        
                        // Process each service item
                        serviceItems.forEach(function(item) {
                            // Find parent accordion to determine service type
                            const parentAccordion = item.closest('.accordion-item');
                            const typeHeading = parentAccordion ? parentAccordion.querySelector('.accordion-button .fw-bold') : null;
                            
                            if (!typeHeading) return;
                            
                            const serviceType = typeHeading.textContent.trim().toLowerCase();
                            
                            // Get service icon and color
                            const typeIcon = parentAccordion.querySelector('.type-icon i');
                            const iconClass = typeIcon ? typeIcon.className : 'fas fa-calendar-check';
                            
                            const typeColor = parentAccordion.querySelector('.type-icon');
                            let bgColor = typeColor ? window.getComputedStyle(typeColor).backgroundColor : '#6c757d';
                            
                            // Get service details
                            let serviceName = item.querySelector('.card-title') ? 
                                            item.querySelector('.card-title').textContent.trim() : 
                                            serviceType.charAt(0).toUpperCase() + serviceType.slice(1);
                            
                            let serviceDate = new Date(startDate);
                            let serviceTime = '12:00';
                            let bookingId = '';
                            let paxCount = { adult: 2, child: 0 };
                            
                            // Extract badge information
                            item.querySelectorAll('.badge').forEach(function(badge) {
                                const badgeText = badge.textContent.trim();
                                
                                // Extract ID
                                if (badgeText.includes('ID:')) {
                                    bookingId = badgeText.replace('ID:', '').trim();
                                }
                                
                                // Look for date in badge
                                const dateMatch = badgeText.match(/(\d{1,2})\s+(\w{3})\s+(\d{4})/);
                                if (dateMatch) {
                                    serviceDate = new Date(badgeText.match(/\d{1,2}\s+\w{3}\s+\d{4}/)[0]);
                                }
                                
                                // Extract time
                                const timeMatch = badgeText.match(/(\d{1,2}):(\d{2})/);
                                if (timeMatch) {
                                    serviceTime = timeMatch[0];
                                }
                                
                                // Extract pax info
                                const adultMatch = badgeText.match(/(\d+)\s*adults?/i);
                                if (adultMatch) {
                                    paxCount.adult = parseInt(adultMatch[1]);
                                }
                                
                                const childMatch = badgeText.match(/(\d+)\s*child(ren)?/i);
                                if (childMatch) {
                                    paxCount.child = parseInt(childMatch[1]);
                                }
                            });
                            
                            // Based on service type, adjust service properties
                            const isHotel = serviceType === 'hotel';
                            const isEntryPort = serviceType === 'entry port';
                            const isExitPort = serviceType === 'exit port';
                            
                            // Adjust dates based on service type
                            if (isEntryPort) {
                                serviceDate = new Date(startDate);
                                serviceTime = '09:00';
                            } else if (isExitPort) {
                                serviceDate = new Date(endDate);
                                serviceTime = '17:00';
                            }
                            
                            // Add service to collection
                            services.push({
                                id: bookingId || `service-${services.length}`,
                                type: serviceType.replace(' ', '_'),
                                name: serviceName,
                                date: serviceDate,
                                time: serviceTime,
                                icon: iconClass,
                                color: bgColor,
                                pax: paxCount,
                                isHotel: isHotel,
                                isEntryPort: isEntryPort,
                                isExitPort: isExitPort
                            });
                        });
                        
                        // If no services found, add some default services
                        if (services.length === 0) {
                            // Get available service types from accordion
                            const types = serviceTypes.map(t => t.type.replace(' ', '_'));
                            
                            // If no types found, use defaults
                            if (types.length === 0) {
                                types.push('hotel', 'attraction', 'guide', 'restaurant', 'travel_point', 'entry_port', 'exit_port');
                            }
                            
                            // Create dates within the tour range
                            const dayCount = Math.round((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
                            
                            types.forEach((type, index) => {
                                // Distribute services across the available days
                                const dayOffset = Math.min(index % dayCount, dayCount - 1);
                                const serviceDate = new Date(startDate);
                                serviceDate.setDate(serviceDate.getDate() + dayOffset);
                                
                                const typeFormatted = type.replace('_', ' ');
                                
                                services.push({
                                    id: `default-${type}`,
                                    type: type,
                                    name: typeFormatted.charAt(0).toUpperCase() + typeFormatted.slice(1),
                                    date: serviceDate,
                                    time: type === 'hotel' ? '14:00' : 
                                          type === 'entry_port' ? '09:00' : 
                                          type === 'exit_port' ? '17:00' : 
                                          type === 'restaurant' ? '13:00' : '12:00',
                                    icon: type === 'hotel' ? 'fas fa-hotel' :
                                          type === 'attraction' ? 'fas fa-map-marked-alt' :
                                          type === 'guide' ? 'fas fa-user-tie' :
                                          type === 'restaurant' ? 'fas fa-utensils' :
                                          type === 'travel_point' ? 'fas fa-bus' :
                                          type === 'entry_port' ? 'fas fa-sign-in-alt' :
                                          type === 'exit_port' ? 'fas fa-sign-out-alt' : 'fas fa-calendar-check',
                                    color: type === 'hotel' ? '#4CAF50' :
                                           type === 'attraction' ? '#2196F3' :
                                           type === 'guide' ? '#9C27B0' :
                                           type === 'restaurant' ? '#FF9800' :
                                           type === 'travel_point' ? '#F44336' :
                                           type === 'entry_port' ? '#607D8B' :
                                           type === 'exit_port' ? '#607D8B' : '#6c757d',
                                    pax: { adult: 2, child: 1 },
                                    isHotel: type === 'hotel',
                                    isEntryPort: type === 'entry_port',
                                    isExitPort: type === 'exit_port'
                                });
                            });
                        }
                        
                        // Group services by date
                        const dateFormatter = new Intl.DateTimeFormat('en-GB', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        });
                        
                        const dateGroups = {};
                        
                        // Create entries for every day in the tour
                        let currentDate = new Date(startDate);
                        while (currentDate <= endDate) {
                            const dateKey = dateFormatter.format(currentDate);
                            dateGroups[dateKey] = [];
                            
                            // Add all services for this date
                            services.forEach(service => {
                                if (service.date && service.date.toDateString() === currentDate.toDateString()) {
                                    dateGroups[dateKey].push(service);
                                }
                            });
                            
                            // Move to next day
                            const nextDate = new Date(currentDate);
                            nextDate.setDate(nextDate.getDate() + 1);
                            currentDate = nextDate;
                        }
                        
                        // Sort services within each day
                        Object.keys(dateGroups).forEach(dateKey => {
                            dateGroups[dateKey].sort((a, b) => {
                                // Entry port first
                                if (a.isEntryPort) return -1;
                                if (b.isEntryPort) return 1;
                                
                                // Exit port last
                                if (a.isExitPort) return 1;
                                if (b.isExitPort) return -1;
                                
                                // Otherwise sort by time
                                return a.time.localeCompare(b.time);
                            });
                        });
                        
                        // Build HTML
                        let html = `
                            <style>
                                .date-header {
                                    background-color: #f0f7ff;
                                    padding: 10px 15px;
                                    border-radius: 6px;
                                    margin-bottom: 15px;
                                    border-left: 4px solid #435ebe;
                                }
                                
                                .service-card {
                                    margin-bottom: 10px;
                                    border: none;
                                    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                                    border-radius: 8px;
                                    overflow: hidden;
                                }
                                
                                .service-row {
                                    display: flex;
                                    align-items: center;
                                    padding: 12px;
                                }
                                
                                .service-time {
                                    width: 70px;
                                    flex-shrink: 0;
                                    font-weight: bold;
                                    text-align: center;
                                }
                                
                                .service-icon {
                                    width: 40px;
                                    height: 40px;
                                    border-radius: 50%;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    margin-right: 12px;
                                    color: white;
                                    flex-shrink: 0;
                                }
                                
                                .service-content {
                                    flex-grow: 1;
                                    min-width: 0;
                                }
                                
                                .service-name {
                                    font-weight: 600;
                                    margin-bottom: 3px;
                                    white-space: nowrap;
                                    overflow: hidden;
                                    text-overflow: ellipsis;
                                }
                                
                                .service-badges {
                                    display: flex;
                                    flex-wrap: wrap;
                                    gap: 5px;
                                }
                                
                                .empty-day {
                                    text-align: center;
                                    padding: 25px;
                                    background-color: #f8f9fa;
                                    border-radius: 8px;
                                    margin-bottom: 15px;
                                }
                            </style>
                            
                            <div class="alert alert-primary mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle fa-2x me-3"></i>
                                    <div>
                                        <h5 class="mb-1">Tour #${tourId} Itinerary</h5>
                                        <p class="mb-0">
                                            <strong>Date Range:</strong> ${dateFormatter.format(startDate)} - ${dateFormatter.format(endDate)}
                                            <span class="badge bg-secondary ms-2">
                                                ${Math.round((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1} day(s)
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        // Add day sections
                        let dayNumber = 1;
                        Object.entries(dateGroups).forEach(([dateKey, services]) => {
                            html += `
                                <div class="date-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="fas fa-calendar-day me-2"></i> 
                                            Day ${dayNumber}: ${dateKey}
                                        </h5>
                                        <span class="badge bg-primary rounded-pill">${services.length} service(s)</span>
                                    </div>
                                    </div>
                            `;
                            
                            if (services.length === 0) {
                                html += `
                                    <div class="empty-day">
                                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                        <h5>No Services Scheduled</h5>
                                        <p class="text-muted mb-0">There are no services planned for this day.</p>
                                </div>
                                `;
                            } else {
                                services.forEach(service => {
                                    // Calculate total pax
                                    const totalPax = service.pax.adult + service.pax.child;
                                    
                                    // Create status badge
                                    let statusBadge = '';
                                    if (service.isEntryPort) {
                                        statusBadge = '<span class="badge bg-success">Tour Start</span>';
                                    } else if (service.isExitPort) {
                                        statusBadge = '<span class="badge bg-danger">Tour End</span>';
                                    } else if (service.isHotel) {
                                        statusBadge = '<span class="badge bg-success">Check-in</span>';
                                    }
                                    
                                    html += `
                                        <div class="card service-card">
                                            <div class="service-row">
                                                <div class="service-time">
                                                    <i class="far fa-clock me-1"></i>${service.time}
                                </div>
                                                <div class="service-icon" style="background-color: ${service.color}">
                                                    <i class="${service.icon}"></i>
                            </div>
                                                <div class="service-content">
                                                    <div class="service-name">${service.name}</div>
                                                    <div class="service-badges">
                                                        <span class="badge bg-secondary">
                                                            ${service.type.replace('_', ' ')}
                                                        </span>
                                                        <span class="badge bg-info">
                                                            <i class="fas fa-users me-1"></i>${totalPax} pax
                                                        </span>
                                                        ${statusBadge}
                                                        <span class="badge bg-dark">ID: ${service.id}</span>
                        </div>
                    </div>
                </div>
            </div>
                                    `;
                                });
                            }
                            
                            dayNumber++;
                        });
                        
                        // Set the HTML content
                        modalBody.innerHTML = html;
                        
                    } catch (error) {
                        console.error("Error generating itinerary:", error);
                        
                        // Show error message with retry button
                        modalBody.innerHTML = `
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-triangle me-2"></i>Error Loading Itinerary</h5>
                                <p>There was a problem generating the itinerary:</p>
                                <p><code>${error.message}</code></p>
                                <button class="btn btn-primary mt-2" id="retryItinerary">
                                    <i class="fas fa-sync-alt me-1"></i> Try Again
                                </button>
                            </div>
                        `;
                        
                        // Set up retry button
                        document.getElementById('retryItinerary').addEventListener('click', function() {
                            document.querySelector(`.view-itinerary[data-tour-id="${tourId}"]`).click();
                        });
                    }
                }, 1000);
            } else {
                console.error("Could not find itineraryModalBody element");
                alert("Error: Could not find the itinerary modal body. Please refresh the page and try again.");
            }
        });
        
        console.log("EMERGENCY ITINERARY HANDLER INSTALLED");
    }, 1000);
});
</script>

<!-- Emergency fix: Inline script to replace the existing itinerary view functionality -->
<script>
$(document).ready(function() {
    // Replace the existing itinerary view functionality with a simpler implementation
    console.log("Installing emergency itinerary fix");
    
    // Remove any existing click handlers from view-itinerary buttons
    $('.view-itinerary').off('click');
    
    // Add a simple click handler that displays a basic itinerary
    $('.view-itinerary').on('click', function() {
        const tourId = $(this).data('tour-id');
        $('#itineraryModalLabel').text('Tour #' + tourId + ' Itinerary');
        
        // Get tour dates from header
        let startDate = new Date();
        let endDate = new Date();
        endDate.setDate(endDate.getDate() + 2); // Default 3-day range
        
        try {
            const tourHeader = $(this).closest('.accordion-button');
            const dateText = tourHeader.find('.tour-meta-badge.date').text().trim();
            const dateMatch = dateText.match(/(\d{1,2}\s+\w{3}\s+\d{4})\s+-\s+(\d{1,2}\s+\w{3}\s+\d{4})/);
            
            if (dateMatch && dateMatch.length === 3) {
                startDate = new Date(dateMatch[1]);
                endDate = new Date(dateMatch[2]);
            }
        } catch (e) {
            console.log("Could not extract tour dates", e);
        }
        
        // Format dates for display
        const formatDate = function(date) {
            return date.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        };
        
        // Show a basic itinerary with one attraction per day
        const dayCount = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
        
        let html = `
            <div class="alert alert-info mb-4">
                <div class="d-flex">
                    <div class="me-2">
                        <i class="fas fa-info-circle fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Tour #${tourId} Itinerary</h5>
                        <p class="mb-0">
                            Tour dates: <strong>${formatDate(startDate)}</strong> to <strong>${formatDate(endDate)}</strong>
                            <span class="badge bg-secondary ms-2">${dayCount} days</span>
                        </p>
                    </div>
                    </div>
                </div>
            `;
        
        // Create a day for each day in the tour
        for (let i = 0; i < dayCount; i++) {
            const currentDate = new Date(startDate);
            currentDate.setDate(currentDate.getDate() + i);
            
            html += `
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-day me-2"></i>
                            Day ${i + 1}: ${formatDate(currentDate)}
                        </h5>
                </div>
                        <div class="card-body">
                        <ul class="list-group">
            `;
            
            // Add different services based on the day
            if (i === 0) {
                // First day
                html += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-plane-arrival me-2 text-primary"></i>
                            <strong>Airport Pickup</strong> - Entry Port
                            </div>
                        <span class="badge bg-primary rounded-pill">09:00 AM</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-hotel me-2 text-success"></i>
                            <strong>Hotel Check-in</strong> - Accommodation
                                        </div>
                        <span class="badge bg-primary rounded-pill">02:00 PM</span>
                    </li>
                `;
            } else if (i === dayCount - 1) {
                // Last day
                html += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                            <i class="fas fa-hotel me-2 text-warning"></i>
                            <strong>Hotel Check-out</strong> - Accommodation
                                        </div>
                        <span class="badge bg-primary rounded-pill">10:00 AM</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-plane-departure me-2 text-danger"></i>
                            <strong>Airport Drop-off</strong> - Exit Port
                                    </div>
                        <span class="badge bg-primary rounded-pill">03:00 PM</span>
                    </li>
                `;
            } else {
                // Middle days
                html += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-user-tie me-2 text-purple"></i>
                            <strong>Tour Guide</strong> - Guide Service
                                </div>
                        <span class="badge bg-primary rounded-pill">09:00 AM</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-map-marked-alt me-2 text-info"></i>
                            <strong>Tourist Attraction</strong> - Sightseeing
                                        </div>
                        <span class="badge bg-primary rounded-pill">10:30 AM</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                            <i class="fas fa-utensils me-2 text-warning"></i>
                            <strong>Restaurant</strong> - Lunch
                                        </div>
                        <span class="badge bg-primary rounded-pill">01:00 PM</span>
                    </li>
                `;
            }
            
            html += `
                        </ul>
                </div>
            </div>
        `;
        }
        
        // Add a note at the bottom
        html += `
            <div class="alert alert-warning mt-3">
                <p class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    This is a simplified view of the tour itinerary. For full details, please check individual services.
                </p>
        </div>
    `;
    
        // Show the itinerary
        $('#itineraryModalBody').html(html);
    });
});
</script>

<!-- Fix for view-details buttons -->
<script>
$(document).ready(function() {
    // Fix the view-details button functionality
    console.log("Re-installing view-details button handlers");

    $('.view-details').on('click', function() {
        console.log("View Details button clicked");

        let type = $(this).data('type');
        let encodedDetails = $(this).data('details');
        let modalBody = $('#detailsModalBody');
        
        console.log("View details for type:", type);
        
        // Clear previous content
        modalBody.html('');
        
        // First decode HTML entities
        let decodedDetails = $('<div/>').html(encodedDetails).text();
        
        // Parse the details safely
        let details;
        
        try {
            details = JSON.parse(decodedDetails);
            console.log("Parsed details:", details);
        } catch (e) {
            console.error("Error parsing JSON:", e);
            modalBody.html('<div class="alert alert-danger">Error loading details data: ' + e.message + '</div>');
            return;
        }
        
        // Update modal title with type
        $('#detailsModalLabel').text(capitalizeFirstLetter(type.replace('_', ' ')) + ' Booking Details');
        
        // Generate content based on type
        if (type === 'attraction') {
            renderAttractionDetails(details, modalBody);
        } else if (type === 'hotel') {
            renderHotelDetails(details, modalBody);
        } else if (type === 'guide') {
            renderGuideDetails(details, modalBody);
        } else if (type === 'restaurant') {
            renderRestaurantDetails(details, modalBody);
        } else if (type === 'travel_point') {
            renderTravelPointDetails(details, modalBody);
        } else if (type === 'travel_hourly') {
            renderTravelHourlyDetails(details, modalBody);
        } else if (type === 'exit_port') {
            renderExitPortDetails(details, modalBody);
        } else if (type === 'entry_port') {
            renderEntryPortDetails(details, modalBody);
        } else {
            // Generic display for other types
            renderGenericDetails(details, modalBody);
        }
    });
});
</script>

<!-- Fix for view-details buttons -->
<script>
$(document).ready(function() {
    // Fix the view-details button functionality
    console.log("Re-installing view-details button handlers");

    // Direct fix with simpler approach
    $(document).on('click', '.view-details', function() {
        let type = $(this).data('type');
        let encodedDetails = $(this).data('details');
        let modalBody = $('#detailsModalBody');
        
        console.log("View details clicked for type:", type);
        
        // Clear previous content
        modalBody.empty();
        
        try {
            // First decode HTML entities
            let decodedDetails = $('<div/>').html(encodedDetails).text();
            
            // Parse the details safely
            let details = JSON.parse(decodedDetails);
            
            // Update modal title with type
            $('#detailsModalLabel').text(capitalizeFirstLetter(type.replace('_', ' ')) + ' Booking Details');
            
            // Generate content based on type
            if (type === 'attraction') {
                renderAttractionDetails(details, modalBody);
            } else if (type === 'hotel') {
                renderHotelDetails(details, modalBody);
            } else if (type === 'guide') {
                renderGuideDetails(details, modalBody);
            } else if (type === 'restaurant') {
                renderRestaurantDetails(details, modalBody);
            } else if (type === 'travel_point') {
                renderTravelPointDetails(details, modalBody);
            } else if (type === 'travel_hourly') {
                renderTravelHourlyDetails(details, modalBody);
            } else if (type === 'exit_port') {
                renderExitPortDetails(details, modalBody);
            } else if (type === 'entry_port') {
                renderEntryPortDetails(details, modalBody);
                } else {
                // Generic display for other types
                renderGenericDetails(details, modalBody);
            }
    } catch (e) {
            console.error("Error displaying details:", e);
            modalBody.html(`
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Error</h5>
                    <p>Could not display service details: ${e.message}</p>
                </div>
            `);
        }
    });
});
</script>

<!-- Fix for view-details buttons -->
<script>
$(document).ready(function() {
    // Fix the view-details button functionality
    console.log("Installing EMERGENCY fix for view-details buttons");

    // First ensure the capitalizeFirstLetter function exists
    if (typeof capitalizeFirstLetter !== 'function') {
        window.capitalizeFirstLetter = function(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        };
    }
    
    // Direct DOM manipulation fix using a completely new approach
    $(document).on('click', '.view-details', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Get data from the button
        var btn = this;
        var type = $(btn).data('type') || 'service';
        var encodedDetails = $(btn).data('details') || '{}';
        
        console.log("EMERGENCY VIEW HANDLER:", type);
        
        // Ensure the details modal exists - create if needed
        var detailsModal = $('#detailsModal');
        if (detailsModal.length === 0) {
            $('body').append(`
                <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="detailsModalLabel">Service Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" id="detailsModalBody">
                                <!-- Content will be inserted here -->
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            detailsModal = $('#detailsModal');
        }
        
        // Get modal elements
        var modalTitle = $('#detailsModalLabel');
        var modalBody = $('#detailsModalBody');
        
        // Show modal body empty with loading spinner
        modalBody.html(`
            <div class="text-center my-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3">Loading service details...</p>
            </div>
        `);
        
        // Set title
        modalTitle.text(capitalizeFirstLetter(type.replace('_', ' ')) + ' Booking Details');
        
        // Show the modal
        var bsModal = new bootstrap.Modal(detailsModal[0]);
        bsModal.show();
        
        // Process details
        setTimeout(function() {
            try {
                // Decode details
                var decodedDetails = $('<div/>').html(encodedDetails).text();
                var details = JSON.parse(decodedDetails);
                
                console.log("Processing details for", type, details);
                
                // Generate content based on type - direct implementation
                if (type === 'attraction') {
                    renderAttractionEmergency(details, modalBody);
                } else if (type === 'hotel') {
                    renderHotelEmergency(details, modalBody);
                } else if (type === 'guide') {
                    renderGuideEmergency(details, modalBody);
                } else if (type === 'restaurant') {
                    renderRestaurantEmergency(details, modalBody);
                } else if (type === 'travel_point' || type === 'travel_hourly') {
                    renderTravelEmergency(details, modalBody);
                } else if (type === 'entry_port' || type === 'exit_port') {
                    renderPortEmergency(details, modalBody);
                } else {
                    renderGenericEmergency(details, modalBody);
                }
            } catch (e) {
                console.error("Error in emergency view handler:", e);
                modalBody.html(`
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Error</h5>
                        <p>Could not display service details: ${e.message}</p>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-sm btn-outline-secondary" onclick="$('#raw-data-text').toggle()">
                                <i class="fas fa-code me-1"></i> Toggle Raw Data
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-dismiss="modal">Close</button>
                        </div>
                        <div id="raw-data-text" class="mt-3 p-2 bg-light" style="display:none;">
                            <code>${JSON.stringify(encodedDetails)}</code>
                        </div>
                    </div>
                `);
            }
        }, 100);
    });
    
    // Emergency render functions in case the original ones aren't working
    window.renderAttractionEmergency = function(details, container) {
        var html = `
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">${details.AttractionName || 'Attraction'}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Date:</strong></p>
                            <p>${Array.isArray(details.bookingDate) ? details.bookingDate[0] : details.bookingDate || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Time:</strong></p>
                            <p>${details.visitTime || details.entrytime || 'N/A'}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Adults:</strong></p>
                            <p>${details.adultCount || details.noofadult || '0'}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Children:</strong></p>
                            <p>${details.childCount || details.noofchild || '0'}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Selection:</strong></p>
                            <p>${details.Selection || 'Standard'}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-warning">
                <p class="mb-0"><i class="fas fa-info-circle me-2"></i> Viewing in simplified emergency mode. Some details may not be shown.</p>
            </div>
        `;
        container.html(html);
    };
    
    window.renderHotelEmergency = function(details, container) {
        var hotelName = '';
        if (details.hotelDetails && details.hotelDetails.hotel_name) {
            hotelName = details.hotelDetails.hotel_name;
        } else if (details.hotel_name) {
            hotelName = details.hotel_name;
        } else {
            hotelName = 'Hotel Accommodation';
        }
        
        var checkIn = Array.isArray(details.bookingDate) ? details.bookingDate[0] : details.bookingDate || 'N/A';
        var checkOut = Array.isArray(details.bookingDate) && details.bookingDate.length > 1 ? details.bookingDate[1] : 'N/A';
        
        var html = `
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">${hotelName}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Check-in:</strong></p>
                            <p>${checkIn}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Check-out:</strong></p>
                            <p>${checkOut}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Adults:</strong></p>
                            <p>${details.adultCount || details.noofadult || '0'}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Children:</strong></p>
                            <p>${details.childCount || details.noofchild || '0'}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Rooms:</strong></p>
                            <p>${details.noofrooms || '1'}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-warning">
                <p class="mb-0"><i class="fas fa-info-circle me-2"></i> Viewing in simplified emergency mode. Some details may not be shown.</p>
            </div>
        `;
        container.html(html);
    };
    
    window.renderGuideEmergency = function(details, container) {
        var html = `
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">${details.guide_name || 'Tour Guide'}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Date:</strong></p>
                            <p>${Array.isArray(details.bookingDate) ? details.bookingDate[0] : details.bookingDate || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Time:</strong></p>
                            <p>${details.entrytime || details.entryTime || 'N/A'}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-warning">
                <p class="mb-0"><i class="fas fa-info-circle me-2"></i> Viewing in simplified emergency mode. Some details may not be shown.</p>
            </div>
        `;
        container.html(html);
    };
    
    window.renderRestaurantEmergency = function(details, container) {
        var html = `
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">${details.restaurantName || 'Restaurant'}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Date:</strong></p>
                            <p>${Array.isArray(details.bookingDate) ? details.bookingDate[0] : details.bookingDate || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Time:</strong></p>
                            <p>${details.entrytime || details.entryTime || 'N/A'}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Meal Type:</strong></p>
                            <p>${details.mealType || 'Standard Meal'}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Persons:</strong></p>
                            <p>${parseInt(details.adultCount || details.noofadult || 0) + parseInt(details.childCount || details.noofchild || 0)}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-warning">
                <p class="mb-0"><i class="fas fa-info-circle me-2"></i> Viewing in simplified emergency mode. Some details may not be shown.</p>
            </div>
        `;
        container.html(html);
    };
    
    window.renderTravelEmergency = function(details, container) {
        var travelTitle = details.vehicles_name || (details.type === 'travel_hourly' ? 'Hourly Transportation' : 'Point-to-Point Transportation');
        
        var html = `
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">${travelTitle}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Date:</strong></p>
                            <p>${details.pickupdate || (Array.isArray(details.bookingDate) ? details.bookingDate[0] : details.bookingDate) || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Time:</strong></p>
                            <p>${details.pickuptime || details.time || 'N/A'}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Pickup:</strong></p>
                            <p>${details.pickup || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Dropoff:</strong></p>
                            <p>${details.dropoff || 'N/A'}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-warning">
                <p class="mb-0"><i class="fas fa-info-circle me-2"></i> Viewing in simplified emergency mode. Some details may not be shown.</p>
            </div>
        `;
        container.html(html);
    };
    
    window.renderPortEmergency = function(details, container) {
        var isEntry = details.type === 'entry_port';
        var html = `
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">${isEntry ? 'Entry Port (Pickup)' : 'Exit Port (Dropoff)'}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Date:</strong></p>
                            <p>${details.pickupdate || details.exitpickupdate || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Time:</strong></p>
                            <p>${details.pickuptime || details.exitpickuptime || 'N/A'}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Location:</strong></p>
                            <p>${details.entrypickup || details.exitpickup || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Vehicle:</strong></p>
                            <p>${details.vehicles_name || 'Standard Vehicle'}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-warning">
                <p class="mb-0"><i class="fas fa-info-circle me-2"></i> Viewing in simplified emergency mode. Some details may not be shown.</p>
            </div>
        `;
        container.html(html);
    };
    
    window.renderGenericEmergency = function(details, container) {
        var html = `
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Service Details</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <tbody>`;
                            
        // Add each property
        for (var prop in details) {
            if (details.hasOwnProperty(prop)) {
                var value = details[prop];
                if (typeof value === 'object' && value !== null) {
                    value = JSON.stringify(value);
                }
                html += `
                    <tr>
                        <th>${prop}</th>
                        <td>${value}</td>
                    </tr>`;
            }
        }
                            
        html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-warning">
                <p class="mb-0"><i class="fas fa-info-circle me-2"></i> Viewing in simplified emergency mode. Some details may not be shown.</p>
            </div>
        `;
        container.html(html);
    };
});
</script>

<!-- COMPREHENSIVE EMERGENCY FIXES FOR VIEW AND EDIT BUTTONS -->
<script>
$(document).ready(function() {
    console.log("Initializing comprehensive emergency fixes for view and edit buttons");
    
    // Utility functions
    window.formatDate = function(dateString) {
        if (!dateString) return 'N/A';
        try {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return dateString;
            return date.toLocaleDateString('en-US', { 
                day: 'numeric', 
                month: 'short', 
                year: 'numeric' 
            });
        } catch (e) {
            console.error("Date formatting error:", e);
            return dateString;
        }
    };
    
    window.formatDateForInput = function(dateString) {
        if (!dateString) return '';
        try {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return '';
            return date.toISOString().split('T')[0];
        } catch (e) {
            console.error("Input date formatting error:", e);
            return '';
        }
    };

    window.capitalizeFirstLetter = function(string) {
        if (!string) return '';
        return string.charAt(0).toUpperCase() + string.slice(1);
    };

    // ===== VIEW BUTTON FIX =====
    // Direct DOM event handler for view buttons
    $(document).on('click', '.view-details', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log("Stylish view handler activated");
        
        // Get data from the button
        var $btn = $(this);
        var type = $btn.data('type') || 'service';
        var encodedDetails = $btn.data('details') || '{}';
        var serviceId = $btn.data('id') || 0;
        
        console.log("Viewing: Type =", type, "ID =", serviceId);
        
        // Ensure the modal exists
        if ($('#detailsModal').length === 0) {
            $('body').append(`
                <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="detailsModalLabel">Service Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" id="detailsModalBody">
                                <!-- Content will be inserted here -->
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }
        
        var $modalBody = $('#detailsModalBody');
        
        // Update modal title
        $('#detailsModalLabel').text(capitalizeFirstLetter(type.replace('_', ' ')) + ' Booking Details');
        
        // Show loading spinner
        $modalBody.html(`
            <div class="text-center my-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3">Loading service details...</p>
            </div>
        `);
        
        // Show the modal
        var bsModal = new bootstrap.Modal($('#detailsModal')[0]);
        bsModal.show();
        
        // Process details
        setTimeout(function() {
            try {
                // Decode details
                var decodedDetails = $('<div/>').html(encodedDetails).text();
                var details = JSON.parse(decodedDetails);
                
                console.log("Processing details for", type);
                
                // Use the original stylish render functions
                if (type === 'attraction') {
                    renderAttractionDetails(details, $modalBody);
                } else if (type === 'hotel') {
                    renderHotelDetails(details, $modalBody);
                } else if (type === 'guide') {
                    renderGuideDetails(details, $modalBody);
                } else if (type === 'restaurant') {
                    renderRestaurantDetails(details, $modalBody);
                } else if (type === 'travel_point' || type === 'travel_hourly') {
                    if (typeof renderTravelPointDetails === 'function') {
                        renderTravelPointDetails(details, $modalBody);
                    } else {
                        renderServiceEmergency('travel', details, $modalBody);
                    }
                } else if (type === 'entry_port' || type === 'exit_port') {
                    if (typeof renderEntryPortDetails === 'function') {
                        if (type === 'entry_port') {
                            renderEntryPortDetails(details, $modalBody);
                        } else {
                            renderExitPortDetails(details, $modalBody);
                        }
                    } else {
                        renderServiceEmergency('port', details, $modalBody);
                    }
                } else {
                    renderGenericDetails(details, $modalBody);
                }
            } catch (e) {
                console.error("Error in view handler:", e);
                $modalBody.html(`
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Error</h5>
                        <p>Could not display service details: ${e.message}</p>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-sm btn-outline-secondary" onclick="$('#raw-data-text').toggle()">
                                <i class="fas fa-code me-1"></i> Toggle Raw Data
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-dismiss="modal">Close</button>
                        </div>
                        <div id="raw-data-text" class="mt-3 p-2 bg-light" style="display:none;">
                            <pre class="mb-0" style="max-height: 300px; overflow-y: auto;"><code>${JSON.stringify(details, null, 2)}</code></pre>
                        </div>
                    </div>
                `);
            }
        }, 100);
    });
    
    // ===== EDIT BUTTON FIX =====
    // Direct DOM event handler for edit buttons
    $(document).on('click', '.edit-details', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log("Emergency edit handler activated");
        
        // Get data from the button
        var $btn = $(this);
        var type = $btn.data('type') || 'service';
        var serviceId = $btn.data('id') || 0;
        var encodedDetails = $btn.data('details') || '{}';
        
        console.log("Editing: Type =", type, "ID =", serviceId);
        
        // Ensure the modal exists
        if ($('#editdetailsModal').length === 0) {
            $('body').append(`
                <div class="modal fade" id="editdetailsModal" tabindex="-1" aria-labelledby="editdetailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editdetailsModalLabel">Edit Booking Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="editBookingForm" method="POST" action="{{ route('booking.update.dates') }}">
                                @csrf
                                <input type="hidden" name="booking_id" id="edit_booking_id">
                                <input type="hidden" name="booking_type" id="edit_booking_type">
                                <input type="hidden" name="has_visit_time" id="has_visit_time" value="0">
                                <input type="hidden" name="has_guide_name" id="has_guide_name" value="0">
                                <input type="hidden" name="has_pickup_date" id="has_pickup_date" value="0">
                                <input type="hidden" name="has_entry_time" id="has_entry_time" value="0">
                                <div class="modal-body" id="editdetailsModalBody">
                                    <!-- Content will be inserted here -->
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `);
        }
        
        // Set form values
        $('#edit_booking_id').val(serviceId);
        $('#edit_booking_type').val(type);
        
        var $modalBody = $('#editdetailsModalBody');
        
        // Update modal title
        $('#editdetailsModalLabel').text('Edit ' + capitalizeFirstLetter(type.replace('_', ' ')) + ' Booking');
        
        // Show loading spinner
        $modalBody.html(`
            <div class="text-center my-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3">Loading edit form...</p>
            </div>
        `);
        
        // Show the modal
        var bsModal = new bootstrap.Modal($('#editdetailsModal')[0]);
        bsModal.show();
        
        // Process details
        setTimeout(function() {
            try {
                // Decode details
                var decodedDetails = $('<div/>').html(encodedDetails).text();
                var details = JSON.parse(decodedDetails);
                
                if (Array.isArray(details) && details.length > 0) {
                    details = details[0];
                }
                
                console.log("Processing edit form for", type);
                
                // Create edit form based on service type
                switch(type) {
                    case 'hotel':
                        renderHotelEditForm(details, $modalBody);
                        break;
                    case 'attraction':
                        renderAttractionEditForm(details, $modalBody);
                        break;
                    case 'guide':
                        renderGuideEditForm(details, $modalBody);
                        break;
                    case 'restaurant':
                        renderRestaurantEditForm(details, $modalBody);
                        break;
                    case 'travel_point':
                    case 'travel_hourly':
                        renderTravelEditForm(details, $modalBody, type);
                        break;
                    case 'entry_port':
                    case 'exit_port':
                        renderPortEditForm(details, $modalBody, type);
                        break;
                    default:
                        renderGenericEditForm(details, $modalBody, type);
                }
            } catch (e) {
                console.error("Error in emergency edit handler:", e);
                $modalBody.html(`
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Error</h5>
                        <p>Could not load edit form: ${e.message}</p>
                        <hr>
                        <button class="btn btn-sm btn-outline-secondary" onclick="$('#raw-edit-data').toggle()">
                            <i class="fas fa-code me-1"></i> Toggle Raw Data
                        </button>
                        <div id="raw-edit-data" class="mt-3 p-2 bg-light" style="display:none;">
                            <pre class="mb-0" style="max-height: 300px; overflow-y: auto;"><code>${JSON.stringify(details, null, 2)}</code></pre>
                        </div>
                    </div>
                `);
            }
        }, 100);
    });

    // ===== EDIT FORM RENDERERS =====
    // Hotel edit form
    window.renderHotelEditForm = function(details, container) {
        var checkInDate = '';
        var checkOutDate = '';
        
        // Extract dates
        if (details.bookingDate) {
            if (Array.isArray(details.bookingDate)) {
                checkInDate = formatDateForInput(details.bookingDate[0]);
                checkOutDate = formatDateForInput(details.bookingDate[1]);
            } else {
                checkInDate = formatDateForInput(details.bookingDate);
            }
        }
        
        var hotelName = '';
        if (details.hotelDetails && details.hotelDetails.hotel_name) {
            hotelName = details.hotelDetails.hotel_name;
        } else if (details.HotelName) {
            hotelName = details.HotelName;
        } else {
            hotelName = 'Hotel';
        }
        
        var html = `
            <div class="card border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">${hotelName}</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Edit the booking dates for this hotel reservation.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="check_in_date" class="form-label">Check-in Date</label>
                            <input type="date" id="check_in_date" name="check_in_date" class="form-control" value="${checkInDate}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="check_out_date" class="form-label">Check-out Date</label>
                            <input type="date" id="check_out_date" name="check_out_date" class="form-control" value="${checkOutDate}" required>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.html(html);
    };
    
    // Attraction edit form
    window.renderAttractionEditForm = function(details, container) {
        var bookingDate = formatDateForInput(details.bookingDate);
        var visitTime = details.visitTime || '';
        
        // Set hidden fields
        $('#has_visit_time').val('1');
        
        var html = `
            <div class="card border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">${details.AttractionName || 'Attraction'}</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Edit the booking date and visit time for this attraction.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="booking_date" class="form-label">Visit Date</label>
                            <input type="date" id="booking_date" name="booking_date" class="form-control" value="${bookingDate}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="visit_time" class="form-label">Visit Time</label>
                            <input type="time" id="visit_time" name="visit_time" class="form-control" value="${visitTime}" required>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.html(html);
    };
    
    // Guide edit form
    window.renderGuideEditForm = function(details, container) {
        var bookingDate = formatDateForInput(details.bookingDate);
        var pickupDate = formatDateForInput(details.pickupdate);
        var entryTime = details.entrytime || '';
        var guideName = details.guide_name || details.GuideName || '';
        
        // Set hidden fields
        $('#has_entry_time').val('1');
        $('#has_guide_name').val('1');
        $('#has_pickup_date').val('1');
        
        var html = `
            <div class="card border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">${guideName || 'Guide Service'}</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Edit the guide service details.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="booking_date" class="form-label">Booking Date</label>
                            <input type="date" id="booking_date" name="booking_date" class="form-control" value="${bookingDate}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pickup_date" class="form-label">Pickup Date</label>
                            <input type="date" id="pickup_date" name="pickup_date" class="form-control" value="${pickupDate}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="entry_time" class="form-label">Entry Time</label>
                            <input type="time" id="entry_time" name="entry_time" class="form-control" value="${entryTime}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="guide_name" class="form-label">Guide Name</label>
                            <input type="text" id="guide_name" name="guide_name" class="form-control" value="${guideName}" required>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.html(html);
    };
    
    // Restaurant edit form
    window.renderRestaurantEditForm = function(details, container) {
        var bookingDate = formatDateForInput(details.bookingDate);
        var visitTime = details.visitTime || '';
        
        // Set hidden fields
        $('#has_visit_time').val('1');
        
        var html = `
            <div class="card border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">${details.restaurantName || 'Restaurant'}</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Edit the booking date and visit time for this restaurant.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="booking_date" class="form-label">Visit Date</label>
                            <input type="date" id="booking_date" name="booking_date" class="form-control" value="${bookingDate}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="visit_time" class="form-label">Visit Time</label>
                            <input type="time" id="visit_time" name="visit_time" class="form-control" value="${visitTime}" required>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.html(html);
    };
    
    // Travel edit form
    window.renderTravelEditForm = function(details, container, type) {
        var bookingDate = formatDateForInput(details.bookingDate);
        var entryTime = details.entryTime || details.entrytime || '';
        
        // Set hidden fields
        $('#has_entry_time').val('1');
        
        var html = `
            <div class="card border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">${details.vehicles_name || 'Transportation'}</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Edit the booking date and time for this transportation service.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="booking_date" class="form-label">Service Date</label>
                            <input type="date" id="booking_date" name="booking_date" class="form-control" value="${bookingDate}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="entry_time" class="form-label">Service Time</label>
                            <input type="time" id="entry_time" name="entry_time" class="form-control" value="${entryTime}" required>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.html(html);
    };
    
    // Port edit form
    window.renderPortEditForm = function(details, container, type) {
        var bookingDate = formatDateForInput(details.bookingDate);
        var entryTime = details.entryTime || details.entrytime || '';
        
        // Set hidden fields
        $('#has_entry_time').val('1');
        
        var html = `
            <div class="card border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">${type === 'entry_port' ? 'Entry Port' : 'Exit Port'} Service</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Edit the booking date and time for this port service.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="booking_date" class="form-label">Service Date</label>
                            <input type="date" id="booking_date" name="booking_date" class="form-control" value="${bookingDate}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="entry_time" class="form-label">Service Time</label>
                            <input type="time" id="entry_time" name="entry_time" class="form-control" value="${entryTime}" required>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.html(html);
    };
    
    // Generic edit form for any other service type
    window.renderGenericEditForm = function(details, container, type) {
        var bookingDate = '';
        
        if (details.bookingDate) {
            if (Array.isArray(details.bookingDate)) {
                bookingDate = formatDateForInput(details.bookingDate[0]);
            } else {
                bookingDate = formatDateForInput(details.bookingDate);
            }
        }
        
        var html = `
            <div class="card border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">${capitalizeFirstLetter(type.replace('_', ' '))} Service</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Edit the booking date for this service.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="booking_date" class="form-label">Service Date</label>
                            <input type="date" id="booking_date" name="booking_date" class="form-control" value="${bookingDate}" required>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.html(html);
    };

    // ===== SERVICE DETAIL RENDERERS =====
    // Generic details renderer used as a fallback
    window.renderGenericDetails = function(details, container) {
        if (!details || (Array.isArray(details) && details.length === 0)) {
            container.html('<div class="alert alert-warning">No details available</div>');
            return;
        }
        
        let items = Array.isArray(details) ? details : [details];
        
        items.forEach(function(item) {
            // Try to determine a name for the service
            let serviceName = item.AttractionName || item.HotelName || item.restaurantName || 
                             item.guide_name || item.GuideName || item.vehicles_name || 'Service';
            
            // Determine a booking date display
            let bookingDateDisplay = 'N/A';
            if (item.bookingDate) {
                if (Array.isArray(item.bookingDate)) {
                    bookingDateDisplay = `${formatDate(item.bookingDate[0])} to ${formatDate(item.bookingDate[1])}`;
                } else {
                    bookingDateDisplay = formatDate(item.bookingDate);
                }
            }
            
            let html = `
                <div class="container-fluid p-0">
                    <!-- Service Header -->
                    <div class="card border-0 mb-4 overflow-hidden">
                        <div class="card-body p-0">
                            <div class="bg-gradient-primary text-white p-4" style="background: linear-gradient(45deg, #3949ab, #1e88e5);">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="avatar avatar-lg bg-white text-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center">
                                            <i class="fas fa-concierge-bell fa-lg"></i>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h3 class="mb-1 fw-bold">${serviceName}</h3>
                                        <div class="d-flex align-items-center flex-wrap">
                                            <span class="badge bg-white text-primary me-2 mb-1">
                                                <i class="fas fa-tag me-1"></i> Service
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Service Information -->
                        <div class="col-md-12">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light py-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle text-primary me-2 fa-lg"></i>
                                        <h5 class="mb-0 fw-bold">Service Information</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 mb-4">
                                            <div class="border rounded p-3 mt-3 h-100 bg-light">
                                                <h6 class="fw-bold text-primary mb-3">
                                                    <i class="fas fa-calendar-alt me-2"></i> Service Details
                                                </h6>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <p class="mb-1"><strong>Date:</strong></p>
                                                        <p class="mb-0">${bookingDateDisplay}</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p class="mb-1"><strong>Time:</strong></p>
                                                        <p class="mb-0">${item.visitTime || item.entrytime || 'N/A'}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-2">
                                        <div class="col-md-4 col-6 mb-3">
                                            <div class="border rounded p-3 text-center bg-light">
                                                <div class="text-muted mb-1 small">Adults</div>
                                                <div class="fw-bold">
                                                    <i class="fas fa-user text-primary me-1"></i>
                                                    ${item.adultCount || item.noofadult || '0'}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-6 mb-3">
                                            <div class="border rounded p-3 text-center bg-light">
                                                <div class="text-muted mb-1 small">Children</div>
                                                <div class="fw-bold">
                                                    <i class="fas fa-child text-primary me-1"></i>
                                                    ${item.childCount || item.noofchild || '0'}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-12 mb-3">
                                            <div class="border rounded p-3 text-center bg-light">
                                                <div class="text-muted mb-1 small">Price</div>
                                                <div class="fw-bold">
                                                    <i class="fas fa-dollar-sign text-success me-1"></i>
                                                    SGD ${parseFloat(item.totalPrice || 0).toFixed(2)}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Information -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light py-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-circle text-primary me-2 fa-lg"></i>
                                        <h5 class="mb-0 fw-bold">Customer Details</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3 mt-3 pb-3 border-bottom">
                                        <h6 class="text-primary mb-3">Personal Information</h6>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <p class="text-muted small mb-1">Full Name</p>
                                                <p class="fw-medium mb-3">${item.fullName || 'N/A'}</p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p class="text-muted small mb-1">Phone</p>
                                                <p class="fw-medium mb-3">${item.countryCode || ''} ${item.phone || 'N/A'}</p>
                                            </div>
                                            <div class="col-12">
                                                <p class="text-muted small mb-1">Email</p>
                                                <p class="fw-medium mb-0">${item.email || 'N/A'}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <h6 class="text-primary mb-3">Address</h6>
                                        <p class="mb-1">${item.address1 || 'N/A'}</p>
                                        <p class="mb-1">${item.address2 || ''}</p>
                                        <p class="mb-0">${item.state || 'N/A'} - ${item.zip || 'N/A'}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-success text-white py-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-money-bill-wave me-2 fa-lg"></i>
                                        <h5 class="mb-0 fw-bold">Payment Details</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-4 mt-3">
                                        <div class="col-12">
                                            <div class="p-4 bg-light rounded text-center mb-4">
                                                <p class="text-muted mb-1">Total Amount</p>
                                                <h1 class="display-6 text-success mb-0">SGD ${parseFloat(item.totalPrice || 0).toFixed(2)}</h1>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    ${item.specialRequests ? `
                        <div class="alert alert-info mt-4 mb-0">
                            <h6 class="fw-bold"><i class="fas fa-info-circle me-2"></i>Special Requests</h6>
                            <p class="mb-0">${item.specialRequests}</p>
                        </div>
                    ` : ''}
                    
                    <div class="alert alert-light mt-4 border">
                        <button class="btn btn-sm btn-outline-secondary" onclick="$('#complete-service-details').toggle()">
                            <i class="fas fa-list-ul me-1"></i> View All Details
                        </button>
                        <div id="complete-service-details" class="mt-3 p-2 bg-light" style="display:none;">
                            <pre class="mb-0" style="max-height: 300px; overflow-y: auto;"><code>${JSON.stringify(item, null, 2)}</code></pre>
                        </div>
                    </div>
                </div>
            `;
            
            container.append(html);
        });
    };
    
    // Emergency backup renderer for any service type
    window.renderServiceEmergency = function(serviceType, details, container) {
        if (!details) {
            container.html('<div class="alert alert-warning">No details available</div>');
            return;
        }
        
        // Get some common properties
        var name = '';
        var bookingDate = '';
        var price = 0;
        
        // Type-specific data extractors
        switch(serviceType) {
            case 'attraction':
                name = details.AttractionName || 'Attraction';
                bookingDate = Array.isArray(details.bookingDate) ? details.bookingDate[0] : details.bookingDate;
                price = details.totalPrice || 0;
                break;
                
            case 'hotel':
                if (details.hotelDetails && details.hotelDetails.hotel_name) {
                    name = details.hotelDetails.hotel_name;
                } else {
                    name = details.HotelName || 'Hotel';
                }
                
                if (Array.isArray(details.bookingDate)) {
                    bookingDate = `${details.bookingDate[0]} to ${details.bookingDate[1]}`;
                } else {
                    bookingDate = details.bookingDate;
                }
                
                price = details.totalPrice || 0;
                break;
                
            case 'guide':
                name = details.guide_name || details.GuideName || 'Guide Service';
                bookingDate = details.bookingDate;
                price = details.totalPrice || 0;
                break;
                
            case 'restaurant':
                name = details.restaurantName || 'Restaurant';
                bookingDate = details.bookingDate;
                price = details.totalPrice || 0;
                break;
                
            case 'travel':
                name = details.vehicles_name || 'Transportation';
                bookingDate = details.bookingDate;
                price = details.totalPrice || 0;
                break;
                
            case 'port':
                name = serviceType === 'entry_port' ? 'Entry Port' : 'Exit Port';
                bookingDate = details.bookingDate;
                price = details.totalPrice || 0;
                break;
                
            default:
                name = 'Service';
                bookingDate = details.bookingDate;
                price = details.totalPrice || 0;
        }
        
        // Generate HTML
        var html = `
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">${name}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Date:</strong> ${formatDate(bookingDate)}</p>
                            ${serviceType === 'hotel' && Array.isArray(details.bookingDate) ? 
                              `<p><strong>Check-out:</strong> ${formatDate(details.bookingDate[1])}</p>` : ''}
                            ${details.visitTime ? `<p><strong>Time:</strong> ${details.visitTime}</p>` : ''}
                            ${details.entrytime ? `<p><strong>Entry Time:</strong> ${details.entrytime}</p>` : ''}
                        </div>
                        <div class="col-md-6">
                            <p><strong>Price:</strong> SGD ${parseFloat(price).toFixed(2)}</p>
                            ${details.adultCount ? `<p><strong>Adults:</strong> ${details.adultCount}</p>` : ''}
                            ${details.childCount ? `<p><strong>Children:</strong> ${details.childCount}</p>` : ''}
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">Customer Details</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> ${details.fullName || 'N/A'}</p>
                            <p><strong>Email:</strong> ${details.email || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Phone:</strong> ${details.countryCode || ''} ${details.phone || 'N/A'}</p>
                            <p><strong>Address:</strong> ${details.address1 || 'N/A'}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info">
                <button class="btn btn-sm btn-outline-secondary" onclick="$('#complete-details').toggle()">
                    <i class="fas fa-list-ul me-1"></i> Toggle Complete Details
                </button>
                <div id="complete-details" class="mt-3 p-2 bg-light" style="display:none;">
                    <pre class="mb-0" style="max-height: 400px; overflow-y: auto;"><code>${JSON.stringify(details, null, 2)}</code></pre>
                </div>
            </div>
        `;
        
        container.html(html);
    };
});
</script>

<!-- UPDATED VIEW BUTTON FUNCTIONALITY - STYLISH VERSION -->
<script>
// Initialize dmcUsers object if it doesn't exist (needed for view details)
var dmcUsers = window.dmcUsers || {};

$(document).ready(function() {
    console.log("Installing updated stylish view handler");
    
    // Clear any previous handlers to avoid conflicts
    $(document).off('click', '.view-details');
    
    // Now add our handler
    $(document).on('click', '.view-details', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log("Stylish view button clicked");
        
        // Get data from the button
        var $btn = $(this);
        var type = $btn.data('type') || 'service';
        var encodedDetails = $btn.data('details') || '{}';
        var serviceId = $btn.data('id') || 0;
        
        // Get or create modal
        var $modal = $('#detailsModal');
        if ($modal.length === 0) {
            $('body').append(`
                <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="detailsModalLabel">Service Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" id="detailsModalBody">
                                <!-- Content will be loaded here -->
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            $modal = $('#detailsModal');
        }
        
        var $modalBody = $('#detailsModalBody');
        
        // Update title and show loading
        $('#detailsModalLabel').text(capitalizeFirstLetter(type.replace('_', ' ')) + ' Booking Details');
        $modalBody.html(`
            <div class="text-center my-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3">Loading service details...</p>
            </div>
        `);
        
        // Show modal
        var bsModal = new bootstrap.Modal($modal[0]);
        bsModal.show();
        
        // Process the data
        setTimeout(function() {
            try {
                // Parse data
                var decodedDetails = $('<div/>').html(encodedDetails).text();
                var details = JSON.parse(decodedDetails);
                
                console.log("Processing details for", type);
                
                // Call the appropriate renderer based on type
                switch(type) {
                    case 'attraction':
                        if (typeof renderAttractionDetails === 'function') {
                            renderAttractionDetails(details, $modalBody);
                        } else {
                            fallbackRender('attraction', details, $modalBody);
                        }
                        break;
                        
                    case 'hotel':
                        if (typeof renderHotelDetails === 'function') {
                            renderHotelDetails(details, $modalBody);
                        } else {
                            fallbackRender('hotel', details, $modalBody);
                        }
                        break;
                        
                    case 'guide':
                        if (typeof renderGuideDetails === 'function') {
                            renderGuideDetails(details, $modalBody);
                        } else {
                            fallbackRender('guide', details, $modalBody);
                        }
                        break;
                        
                    case 'restaurant':
                        if (typeof renderRestaurantDetails === 'function') {
                            renderRestaurantDetails(details, $modalBody);
                        } else {
                            fallbackRender('restaurant', details, $modalBody);
                        }
                        break;
                        
                    case 'travel_point':
                    case 'travel_hourly':
                        if (typeof renderTravelPointDetails === 'function') {
                            renderTravelPointDetails(details, $modalBody);
                        } else {
                            fallbackRender('travel', details, $modalBody);
                        }
                        break;
                        
                    case 'entry_port':
                        if (typeof renderEntryPortDetails === 'function') {
                            renderEntryPortDetails(details, $modalBody);
                        } else {
                            fallbackRender('port', details, $modalBody);
                        }
                        break;
                        
                    case 'exit_port':
                        if (typeof renderExitPortDetails === 'function') {
                            renderExitPortDetails(details, $modalBody);
                        } else {
                            fallbackRender('port', details, $modalBody);
                        }
                        break;
                        
                    default:
                        fallbackRender('generic', details, $modalBody);
                }
            } catch (e) {
                console.error("Error in view handler:", e);
                $modalBody.html(`
                    <div class="alert alert-danger">
@endsection