@extends('layouts.layout')
@section('title', 'BookingList')
@extends('layouts.datatablecss')

@php
    /**
     * Status System Documentation:
     * 1 = Complete   - Service/booking is completed successfully
     * 2 = Pending    - Service/booking is awaiting processing (default)
     * 3 = Confirm    - Service/booking is confirmed but not yet complete
     * 4 = Cancelled  - Service/booking has been cancelled
     */
    
    // Status configuration array for consistent status display
    $statusConfig = [
        1 => ['label' => 'Complete', 'color' => 'success', 'icon' => 'fas fa-check-circle'],
        2 => ['label' => 'Pending', 'color' => 'warning', 'icon' => 'fas fa-clock'],
        3 => ['label' => 'Confirm', 'color' => 'info', 'icon' => 'fas fa-check'],
        4 => ['label' => 'Cancelled', 'color' => 'danger', 'icon' => 'fas fa-ban']
    ];
@endphp

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
    
    /* Itinerary link styling */
    .itinerary-link {
        position: relative;
        overflow: hidden;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.3s ease;
        z-index: 10;
    }
    
    .itinerary-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        background-color: #3a4db5 !important;
    }
    
    .itinerary-link:active {
        transform: translateY(0);
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
    
    /* Loading button styles */
    .generate-coupon.loading {
        opacity: 0.7;
        cursor: not-allowed;
        position: relative;
    }
    
    .generate-coupon.loading i.fa-spinner {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .generate-coupon.loading:hover {
        transform: none !important;
        box-shadow: none !important;
    }
    
    /* Status Badge Styles */
    .badge.bg-success {
        background-color: #28a745 !important;
    }
    .badge.bg-warning {
        background-color: #ffc107 !important;
        color: #212529 !important;
    }
    .badge.bg-info {
        background-color: #17a2b8 !important;
    }
    .badge.bg-danger {
        background-color: #dc3545 !important;
    }
    
    /* Status badge hover effects */
    .badge:hover {
        transform: scale(1.05);
        transition: transform 0.2s ease;
    }
    
    /* Status badge specific styling for better visibility */
    .service-item .badge {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
        font-weight: 600;
        letter-spacing: 0.025em;
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
                                    'is_approve' =>  $tourDetails->is_approve,
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

                        // Calculate starting number for pagination
                        $currentPage = request()->get('page', 1);
                        $perPage = 10;
                        $startingNumber = ($currentPage - 1) * $perPage;
                    @endphp
                    
                    @forelse ($groupedBookings as $index => $tour)
                        @php
                            // Calculate tour number with pagination
                            $tourNumber = $startingNumber + $loop->iteration;
                            
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
                                        <!-- ADDED: Tour numbering badge -->
                                        @php
                                            $numberColor = match(true) {
                                                $tourNumber <= 5 => 'bg-dark',
                                                $tourNumber <= 10 => 'bg-dark', 
                                                $tourNumber <= 15 => 'bg-dark',
                                                default => 'bg-dark'
                                            };
                                        @endphp

                                        <span class="badge {{ $numberColor }} rounded-pill me-2" style="font-size: 0.9rem; padding: 0.5rem 0.75rem;">
                                            #{{ $tourNumber }}
                                        </span>
                                        <span class="badge bg-primary me-2">Tour #{{ $tour['tour_id'] }}</span>
                                        <span class="badge bg-secondary">{{ count($tour['types']) }} Types</span>
                                        <span class="badge bg-info">
                                            {{ array_sum(array_map(function($type) { return count($type['services']); }, $tour['types'])) }} Services
                                        </span>
                                        @php
                                            // Calculate status summary for this tour
                                            $statusCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
                                            foreach($tour['types'] as $typeGroup) {
                                                foreach($typeGroup['services'] as $service) {
                                                    $status = $service->status ?? 2;
                                                    if(isset($statusCounts[$status])) {
                                                        $statusCounts[$status]++;
                                                    }
                                                }
                                            }
                                            
                                            // Find the most common status for the tour summary
                                            $dominantStatus = array_search(max($statusCounts), $statusCounts);
                                            $dominantStatusInfo = $statusConfig[$dominantStatus] ?? $statusConfig[2];
                                        @endphp
                                        
                                        <!-- Status Summary Badge -->
                                        <span class="badge bg-{{ $dominantStatusInfo['color'] }}" title="Complete: {{ $statusCounts[1] }}, Pending: {{ $statusCounts[2] }}, Confirm: {{ $statusCounts[3] }}, Cancelled: {{ $statusCounts[4] }}">
                                            <i class="{{ $dominantStatusInfo['icon'] }} me-1"></i>{{ $dominantStatusInfo['label'] }}
                                        </span>
                                        
                                        <span class="badge bg-danger">SGD {{ number_format($overall_price, 2) }}</span>
                                        <span class="mt-1">

                                        <a 
                                            href="{{ route('tour.itinerary', ['tourId' => $tour['tour_id']]) }}" 
                                            class="badge bg-primary"
                                            onclick="event.stopPropagation(); window.open(this.href, '_blank'); return false;"
                                            style="text-decoration:none; cursor:pointer; transition: all 0.2s ease;">
                                            <i class="fas fa-calendar-alt"></i> View Itinerary
                                        </a>
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
                                                                            @php
                                                                                $currentStatus = $service->status ?? 2; // Default to pending if no status
                                                                                $statusInfo = $statusConfig[$currentStatus] ?? $statusConfig[2];
                                                                            @endphp
                                                                            
                                                                            <!-- Status Badge -->
                                                                            <span class="badge bg-{{ $statusInfo['color'] }} me-2">
                                                                                <i class="{{ $statusInfo['icon'] }} me-1"></i>
                                                                                {{ $statusInfo['label'] }}
                                                                            </span>
                                                                            
                                                                            @if(isset($service->status) && $service->status == 4)
                                                                                <!-- Cancelled service - show disabled buttons -->
                                                                            @else
                                                                                <button type="button" class="btn btn-sm btn-outline-primary view-details" 
                                                                                        data-id="{{ $service->id }}"
                                                                                        data-type="{{ strtolower(str_replace(' ', '_', $service->type)) }}"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#detailsModal"
                                                                                        data-details="{{ htmlspecialchars(json_encode($service->data_decoded)) }}">
                                                                                    <i class="fas fa-eye"></i> View
                                                                                </button>
                                                                                
                                                                                @if(strtolower(str_replace(' ', '_', $service->type)) === 'hotel')
                                                                                <button type="button" class="btn btn-sm btn-outline-info mail-preview" 
                                                                                        data-id="{{ $service->id }}"
                                                                                        data-type="{{ strtolower(str_replace(' ', '_', $service->type)) }}"
                                                                                        data-tour-id="{{ $tour['tour_id'] }}"
                                                                                        data-booking-id="{{ $service->booking_id }}"
                                                                                        data-agent-name="{{ $tour['agent_name'] ?? 'N/A' }}"
                                                                                        data-dmc-company="{{ $tour['dmc_company'] ?? 'N/A' }}"
                                                                                        data-destination="{{ $tour['destination'] }}"
                                                                                        data-display-id="{{ $tour_date->display_id ?? 'N/A' }}"
                                                                                        data-check-in="{{ $tour_date->check_in_time ?? '' }}"
                                                                                        data-check-out="{{ $tour_date->check_out_time ?? '' }}"
                                                                                        data-total-pax="{{ $totalPax }}"
                                                                                        data-adults="{{ $tour['adult'] ?? 0 }}"
                                                                                        data-children="{{ $tour['child'] ?? 0 }}"
                                                                                        data-infants="{{ $tour['infant'] ?? 0 }}"
                                                                                        data-males="{{ $tour['male_count'] ?? 0 }}"
                                                                                        data-females="{{ $tour['female_count'] ?? 0 }}"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#mailPreviewModal"
                                                                                        data-details="{{ htmlspecialchars(json_encode($service->data_decoded)) }}">
                                                                                    <i class="fas fa-envelope"></i> Mail Preview
                                                                                </button>
                                                                                @endif

                                                                                @if(strtolower(str_replace(' ', '_', $service->type)) === 'restaurant')
                                                                                @php
                                                                                    $voucherImage = $service->voucher_image ?? null;
                                                                                    if (!$voucherImage && isset($service->data_decoded['voucher_image'])) {
                                                                                        $voucherImage = $service->data_decoded['voucher_image'];
                                                                                    }
                                                                                @endphp
                                                                                @if($voucherImage == null)
                                                                                <button type="button" class="btn btn-sm btn-outline-success generate-coupon" 
                                                                                        data-id="{{ $service->id }}"
                                                                                        data-type="{{ strtolower(str_replace(' ', '_', $service->type)) }}"
                                                                                        data-tour-id="{{ $tour['tour_id'] }}"
                                                                                        data-booking-id="{{ $service->booking_id }}"
                                                                                        data-agent-name="{{ $tour['agent_name'] ?? 'N/A' }}"
                                                                                        data-dmc-company="{{ $tour['dmc_company'] ?? 'N/A' }}"
                                                                                        data-destination="{{ $tour['destination'] }}"
                                                                                        data-display-id="{{ $tour_date->display_id ?? 'N/A' }}"
                                                                                        data-check-in="{{ $tour_date->check_in_time ?? '' }}"
                                                                                        data-check-out="{{ $tour_date->check_out_time ?? '' }}"
                                                                                        data-total-pax="{{ $totalPax }}"
                                                                                        data-adults="{{ $tour['adult'] ?? 0 }}"
                                                                                        data-children="{{ $tour['child'] ?? 0 }}"
                                                                                        data-infants="{{ $tour['infant'] ?? 0 }}"
                                                                                        data-males="{{ $tour['male_count'] ?? 0 }}"
                                                                                        data-females="{{ $tour['female_count'] ?? 0 }}"
                                                                                        data-details="{{ htmlspecialchars(json_encode($service->data_decoded)) }}">
                                                                                    <i class="fas fa-ticket-alt"></i> Generate Coupon
                                                                                </button>
                                                                                @else
                                                                                <a href="{{ route('view.voucher.image', ['booking_id' => $service->booking_id, 'tour_id' => $service->tour_id]) }}" target="_blank" class="btn btn-sm btn-outline-success">
                                                                                    <i class="fas fa-eye"></i> View Voucher
                                                                                </a>
                                                                                @endif
                                                                                @endif
                                                                              @if(in_array(Auth::user()->role_id, $allowedRoles) && $tour['is_approve'] == 1)
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
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-circle text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-muted">No bookings found</h5>
                            <p class="text-muted">There are no tour bookings to display at the moment.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination (keep existing) -->
                @if(isset($pagination) && $pagination->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <p class="text-sm text-gray-700 leading-5">
                            Showing {{ $pagination->firstItem() }} to {{ $pagination->lastItem() }} of {{ $pagination->total() }} tours
                        </p>
                    </div>
                    <div>
                        {{ $pagination->links('pagination::bootstrap-4') }}
                    </div>
                </div>
                @endif
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
@php
    $allowedRoles = [1, 11, 34, 64, 65, 66, 67, 68, 81, 90, 99, 108, 117, 125, 128, 131, 132, 134, 135, 137, 138];
@endphp
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
                    <div class="mb-3">
                        <label for="actual_due_date" class="form-label">Actual Due Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="actual_due_date" name="actual_due_date" required>
                        <div class="form-text">Select the actual due date for this booking</div>
                    </div>
                    <div class="mb-3">
                        <label for="display_days_before" class="form-label">Display Due Date (Days Before) <span class="text-danger">*</span></label>
                        <select class="form-control" id="display_days_before" name="display_days_before" required>
                            <option value="">Select days before actual due date</option>
                            <option value="1">1 day before</option>
                            <option value="2">2 days before</option>
                            <option value="3">3 days before</option>
                            <option value="4">4 days before</option>
                            <option value="5">5 days before</option>
                            <option value="6">6 days before</option>
                        </select>
                        <div class="form-text">Select how many days before the actual due date to display</div>
                    </div>
                    <div class="mb-3">
                        <label for="display_due_date" class="form-label">Display Due Date</label>
                        <input type="date" class="form-control" id="display_due_date" name="display_due_date" readonly>
                        <div class="form-text">This will be calculated automatically based on your selection above</div>
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

<!-- Mail Preview Modal -->
<div class="modal fade" id="mailPreviewModal" tabindex="-1" aria-labelledby="mailPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mailPreviewModalLabel">
                    <i class="fas fa-envelope me-2"></i>Email Preview - Booking Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Subject:</label>
                        <input type="text" id="emailSubject" class="form-control" readonly style="background-color: #f8f9fa;">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" class="btn btn-success w-100" id="copyEmailBtn">
                            <i class="fas fa-copy me-1"></i> Copy Email Content
                        </button>
                    </div>
                </div>
                
                <div class="border rounded p-3" style="background-color: #f8f9fa;">
                    <pre id="emailContent" style="white-space: pre-wrap; font-family: 'Courier New', monospace; margin: 0; color: #333;"></pre>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Instructions:</strong> Click the "Copy Email Content" button to copy the subject and message to your clipboard. Then paste it into your email client.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="copyEmailBtn2">
                    <i class="fas fa-copy me-1"></i> Copy to Clipboard
                </button>
            </div>
        </div>
    </div>
</div>

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
            </div>
        </div>
    </div>
</div>
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

        // Generate Coupon Button Click Handler
        $('.generate-coupon').on('click', function() {
            const btn = $(this);
            
            // Prevent multiple clicks
            if (btn.hasClass('loading')) {
                return;
            }
            
            let encodedDetails = btn.attr('data-details');
            
            // Decode and parse restaurant details
            let restaurantData;
            try {
                let decodedDetails = $('<div/>').html(encodedDetails).text();
                console.log("Decoded details:", decodedDetails); // Debug log
                restaurantData = JSON.parse(decodedDetails);
                console.log("Parsed restaurant data:", restaurantData); // Debug log
            } catch (e) {
                console.error("Error parsing restaurant details:", e);
                console.error("Raw encoded details:", encodedDetails);
                console.error("Decoded details:", $('<div/>').html(encodedDetails).text());
                showToast('Error parsing restaurant data. Please try again.', 'error');
                return;
            }
            
            const bookingId = btn.data('booking-id');
            const tourId = btn.data('tour-id');
            const displayId = btn.data('display-id');
            const destination = btn.data('destination');
            const checkInDate = btn.data('check-in');
            const totalPax = btn.data('total-pax');
            const adults = btn.data('adults');
            const children = btn.data('children');
            const agentName = btn.data('agent-name');
            const dmcCompany = btn.data('dmc-company');
            
            // Store original button content
            const originalContent = btn.html();
            
            // Show loading state
            btn.addClass('loading').prop('disabled', true);
            btn.html('<i class="fas fa-spinner fa-spin"></i> Generating...');
            
            // Generate voucher silently without opening new tab
            generateVoucherSilently(restaurantData, {
                bookingId: bookingId,
                tourId: tourId,
                displayId: displayId,
                destination: destination,
                checkInDate: checkInDate,
                totalPax: totalPax,
                adults: adults,
                children: children,
                agentName: agentName,
                dmcCompany: dmcCompany
            }, btn, originalContent);
        });

        // Function to generate voucher silently without opening new tab
        function generateVoucherSilently(restaurantData, bookingData, button, originalContent) {
            // Show loading toast
            showToast('Generating and saving voucher image...', 'info');
            
            // Make AJAX call to get the HTML from the existing blade template
            $.ajax({
                url: "{{ route('generate.restaurant.coupon') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    restaurant_data: restaurantData,
                    booking_id: bookingData.bookingId,
                    tour_id: bookingData.tourId,
                    display_id: bookingData.displayId,
                    destination: bookingData.destination,
                    check_in_date: bookingData.checkInDate,
                    total_pax: bookingData.totalPax,
                    adults: bookingData.adults,
                    children: bookingData.children,
                    agent_name: bookingData.agentName,
                    dmc_company: bookingData.dmcCompany,
                    action: 'render_html_only'
                },
                success: function(response) {
                    if (response.success && response.html) {
                        // Capture the voucher using html2canvas
                        captureVoucherFromHtml(response.html, bookingData, button, originalContent);
                    } else {
                        // Reset button state on error
                        resetButtonState(button, originalContent);
                        showToast(response.message || 'Failed to generate voucher HTML', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    // Reset button state on error
                    resetButtonState(button, originalContent);
                    showToast('Something went wrong. Please try again later.', 'error');
                }
            });
        }

        // Function to capture voucher from HTML without opening new tab
        function captureVoucherFromHtml(html, bookingData, button, originalContent) {
            // Create a hidden iframe
            const iframe = document.createElement('iframe');
            iframe.style.cssText = 'position: absolute; left: -9999px; top: -9999px; width: 600px; height: 300px; border: none;';
            document.body.appendChild(iframe);
            
            // Write HTML to iframe
            iframe.contentDocument.write(html);
            iframe.contentDocument.close();
            
            // Wait for iframe to load and then capture
            iframe.onload = function() {
                setTimeout(function() {
                    // Load HTML2Canvas in iframe
                    const script = iframe.contentDocument.createElement('script');
                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
                    script.onload = function() {
                        // Capture the voucher using the iframe's html2canvas
                        const voucherElement = iframe.contentDocument.querySelector('.voucher');
                        if (voucherElement) {
                            // Use the iframe's html2canvas instance
                            iframe.contentWindow.html2canvas(voucherElement, {
                                scale: 2,
                                useCORS: true,
                                backgroundColor: null,
                                width: 600,
                                height: 300,
                                logging: false
                            }).then(canvas => {
                                canvas.toBlob(function(blob) {
                                    const reader = new FileReader();
                                    reader.onloadend = function() {
                                        const base64data = reader.result;
                                        
                                        // Store in database via AJAX
                                        fetch('{{ route('generate.restaurant.coupon') }}', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                            },
                                            body: JSON.stringify({
                                                booking_id: bookingData.bookingId,
                                                tour_id: bookingData.tourId,
                                                action: 'store_image',
                                                image_data: base64data
                                            })
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            console.log('Image save response:', data);
                                            // Remove iframe
                                            document.body.removeChild(iframe);
                                            
                                            if (data.success) {
                                                console.log('Image saved successfully, replacing button...');
                                                showToast('Voucher image generated and saved successfully!', 'success');
                                                // Replace the button with "View Voucher" button
                                                replaceWithViewVoucherButton(button, bookingData);
                                            } else {
                                                console.log('Image save failed:', data.message);
                                                resetButtonState(button, originalContent);
                                                showToast('Failed to save image to database: ' + data.message, 'error');
                                            }
                                        })
                                        .catch(error => {
                                            document.body.removeChild(iframe);
                                            resetButtonState(button, originalContent);
                                            console.error('Error storing voucher image:', error);
                                            showToast('Error saving image to database', 'error');
                                        });
                                    };
                                    reader.readAsDataURL(blob);
                                }, 'image/png');
                            }).catch(error => {
                                document.body.removeChild(iframe);
                                resetButtonState(button, originalContent);
                                console.error('Error capturing voucher:', error);
                                showToast('Error capturing voucher image', 'error');
                            });
                        } else {
                            document.body.removeChild(iframe);
                            resetButtonState(button, originalContent);
                            showToast('Voucher element not found', 'error');
                        }
                    };
                    iframe.contentDocument.head.appendChild(script);
                }, 1000); // Wait for fonts and styling to load
            };
        }

        // Function to reset button state
        function resetButtonState(button, originalContent) {
            button.removeClass('loading').prop('disabled', false);
            button.html(originalContent);
        }

        // Function to replace Generate Coupon button with View Voucher button
        function replaceWithViewVoucherButton(button, bookingData) {
            console.log('Replacing button with View Voucher button', { button, bookingData });
            
            // Remove loading state
            button.removeClass('loading').prop('disabled', false);
            
            // Create the View Voucher button using the route helper
            const viewVoucherButton = $(`
                <a href="{{ route('view.voucher.image', ['booking_id' => ':bookingId', 'tour_id' => ':tourId']) }}" target="_blank" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-eye"></i> View Voucher
                </a>
            `.replace(':bookingId', bookingData.bookingId).replace(':tourId', bookingData.tourId));
            
            console.log('Created view voucher button:', viewVoucherButton);
            
            // Replace the original button with the new one
            button.replaceWith(viewVoucherButton);
            
            console.log('Button replacement completed');
        }

        // Function to generate voucher in specified format (kept for potential future use)
        function generateVoucher(restaurantData, bookingData, format) {
            // Show loading toast
            showToast('Generating voucher...', 'info');
            
            // Make AJAX call to generate voucher
            $.ajax({
                url: "{{ route('generate.restaurant.coupon') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    restaurant_data: restaurantData,
                    booking_id: bookingData.bookingId,
                    tour_id: bookingData.tourId,
                    display_id: bookingData.displayId,
                    destination: bookingData.destination,
                    check_in_date: bookingData.checkInDate,
                    total_pax: bookingData.totalPax,
                    adults: bookingData.adults,
                    children: bookingData.children,
                    agent_name: bookingData.agentName,
                    dmc_company: bookingData.dmcCompany,
                    format: format
                },
                success: function(response) {
                    if (response.success) {
                        if (format === 'html') {
                            // Open HTML in new window - this will trigger the HTML2Canvas capture
                            const newWindow = window.open('', '_blank');
                            newWindow.document.write(response.html);
                            newWindow.document.close();
                            showToast('Voucher opened in new tab. Image will be automatically saved to database.', 'success');
                        } else if (format === 'image') {
                            if (response.method === 'html2canvas') {
                                // Open HTML with image generation capability
                                const newWindow = window.open('', '_blank');
                                newWindow.document.write(response.html);
                                newWindow.document.close();
                                showToast('Voucher opened with image generation capability. Click the "Download as Image" button.', 'info');
                            } else {
                                // Server-generated image
                                if (response.image) {
                                    // Create and download image
                                    const link = document.createElement('a');
                                    link.href = 'data:image/png;base64,' + response.image;
                                    link.download = response.filename;
                                    link.click();
                                    showToast('Image voucher downloaded successfully!', 'success');
                                } else {
                                    showToast('Image generated successfully!', 'success');
                                }
                            }
                        }
                    } else {
                        showToast(response.message || 'Failed to generate voucher', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showToast('Something went wrong. Please try again later.', 'error');
                }
            });
        }

        // Mail Preview Button Click Handler
        $('.mail-preview').on('click', function() {
            let tourId = $(this).data('tour-id');
            let bookingId = $(this).data('booking-id');
            let agentName = $(this).data('agent-name');
            let dmcCompany = $(this).data('dmc-company');
            let destination = $(this).data('destination');
            let displayId = $(this).data('display-id');
            let checkIn = $(this).data('check-in');
            let checkOut = $(this).data('check-out');
            let totalPax = $(this).data('total-pax');
            let adults = $(this).data('adults');
            let children = $(this).data('children');
            let infants = $(this).data('infants');
            let males = $(this).data('males');
            let females = $(this).data('females');
            let type = $(this).data('type');
            let encodedDetails = $(this).data('details');
            
            // Decode and parse service details
            let serviceDetails;
            try {
                let decodedDetails = $('<div/>').html(encodedDetails).text();
                serviceDetails = JSON.parse(decodedDetails);
            } catch (e) {
                console.error("Error parsing service details:", e);
                serviceDetails = {};
            }
            
            // Generate email subject
            let subject = `Booking Confirmation - ${displayId} - ${dmcCompany}`;
            $('#emailSubject').val(subject);
            
            // Generate email content
            let emailContent = generateEmailContent({
                tourId: tourId,
                bookingId: bookingId,
                agentName: agentName,
                dmcCompany: dmcCompany,
                destination: destination,
                displayId: displayId,
                checkIn: checkIn,
                checkOut: checkOut,
                totalPax: totalPax,
                adults: adults,
                children: children,
                infants: infants,
                males: males,
                females: females,
                type: type,
                serviceDetails: serviceDetails
            });
            
            $('#emailContent').text(emailContent);
        });

        // Copy email content to clipboard
        $('#copyEmailBtn, #copyEmailBtn2').on('click', function() {
            let subject = $('#emailSubject').val();
            let content = $('#emailContent').text();
            let fullEmail = subject + '\n\n' + content;
            
            // Try to copy to clipboard
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(fullEmail).then(function() {
                    // Show success message
                    showToast('Email content copied to clipboard!', 'success');
                }).catch(function(err) {
                    console.error('Failed to copy: ', err);
                    fallbackCopyTextToClipboard(fullEmail);
                });
            } else {
                // Fallback for older browsers
                fallbackCopyTextToClipboard(fullEmail);
            }
        });

        // Fallback copy function for older browsers
        function fallbackCopyTextToClipboard(text) {
            let textArea = document.createElement("textarea");
            textArea.value = text;
            
            // Avoid scrolling to bottom
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";
            
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                let successful = document.execCommand('copy');
                let msg = successful ? 'successful' : 'unsuccessful';
                console.log('Fallback: Copying text command was ' + msg);
                showToast('Email content copied to clipboard!', 'success');
            } catch (err) {
                console.error('Fallback: Oops, unable to copy', err);
                showToast('Failed to copy to clipboard', 'error');
            }
            
            document.body.removeChild(textArea);
        }

        // Show toast notification
        function showToast(message, type) {
            // Create toast element
            let toast = $(`
                <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `);
            
            $('body').append(toast);
            let bsToast = new bootstrap.Toast(toast[0]);
            bsToast.show();
            
            // Remove toast after hiding
            toast.on('hidden.bs.toast', function() {
                $(this).remove();
            });
        }

        // Helper function to pad text to the right
        function padRight(text, length) {
            if (!text) text = '';
            text = text.toString();
            if (text.length > length) {
                return text.substring(0, length);
            }
            return text + ' '.repeat(length - text.length);
        }

        // Helper to center text
        function centerText(text, width) {
            const totalPadding = width - text.length;
            const left = Math.floor(totalPadding / 2);
            const right = totalPadding - left;
            return ' '.repeat(left) + text + ' '.repeat(right);
        }

        // Generate email content based on booking data
        function generateEmailContent(data) {
            const WIDTH = 66;
            const border = '┌' + '─'.repeat(WIDTH - 2) + '┐';
            const sectionBorder = '├' + '─'.repeat(WIDTH - 2) + '┤';
            const endBorder = '└' + '─'.repeat(WIDTH - 2) + '┘';
            const header = '╔' + '═'.repeat(WIDTH - 2) + '╗';
            const headerEnd = '╚' + '═'.repeat(WIDTH - 2) + '╝';
            const sectionHeader = (title) => `│${centerText(title, WIDTH - 2)}│`;
            const row = (label, value) => `│ ${padRight(label, 16)} │ ${padRight(value, 43)}│`;
            const fullRow = (text) => `│ ${padRight(text, WIDTH - 4)} │`;
            let content = `Dear Valued Partner,\n\nWe are pleased to confirm your booking request. Please find the details below:\n\n${header}\n║${centerText('=== BOOKING CONFIRMATION ===', WIDTH - 2)}║\n${headerEnd}\n`;
            content += `${border}\n${sectionHeader('BOOKING INFORMATION')}\n${sectionBorder}\n`;
            content += `${row('Reference ID', data.displayId)}\n`;
            content += `${row('Tour ID', data.tourId.toString())}\n`;
            content += `${row('Booking ID', data.bookingId.toString())}\n`;
            content += `${row('DMC Company', data.dmcCompany)}\n`;
            content += `${endBorder}\n\n`;
            content += `${border}\n${sectionHeader('TOUR DETAILS')}\n${sectionBorder}\n`;
            content += `${row('Destination', data.destination)}\n`;
            content += `${row('Check-in Date', formatEmailDate(data.checkIn))}\n`;
            content += `${row('Check-out Date', formatEmailDate(data.checkOut))}\n`;
            content += `${endBorder}\n\n`;
            content += `${border}\n${sectionHeader('PASSENGER BREAKDOWN')}\n${sectionBorder}\n`;
            content += `${row('Total Passengers', data.totalPax.toString() + ' people')}\n`;
            if (data.adults > 0) {
                content += `${row('Adults', data.adults.toString())}\n`;
            }
            if (data.children > 0) {
                content += `${row('Children', data.children.toString())}\n`;
            }
            if (data.infants > 0) {
                content += `${row('Infants', data.infants.toString())}\n`;
            }
            content += `${row('Male Passengers', data.males.toString())}\n`;
            content += `${row('Female Passengers', data.females.toString())}\n`;
            content += `${endBorder}\n\n`;
            content += `${border}\n${sectionHeader('SERVICE DETAILS')}\n${sectionBorder}\n`;
            content += `${row('Service Type', data.type.charAt(0).toUpperCase() + data.type.slice(1).replace('_', ' '))}\n`;
            if (data.type === 'hotel' && data.serviceDetails && Array.isArray(data.serviceDetails) && data.serviceDetails.length > 0) {
                const service = data.serviceDetails[0];
                const hotelName = service.hotelDetails?.hotel_name;
                const roomType = service.rooms?.[0]?.room_type;
                const bedType = service.rooms?.[0]?.beds?.[0]?.bed_type;
                const occupancy = service.rooms?.[0]?.beds?.[0]?.head_count;
                const numberOfRooms = service.rooms?.length;
                const mealPlan = service.rooms?.[0]?.beds?.[0]?.selectedMeals?.meal_1?.type;
                if (hotelName) content += `${row('Hotel Name', hotelName)}\n`;
                if (roomType) content += `${row('Room Type', roomType)}\n`;
                if (bedType) content += `${row('Bed Type', bedType)}\n`;
                if (occupancy) content += `${row('Room Occupancy', occupancy + ' person(s)')}\n`;
                if (numberOfRooms) content += `${row('Number of Rooms', numberOfRooms.toString())}\n`;
                if (mealPlan) content += `${row('Meal Plan', mealPlan)}\n`;
            }
            content += `${endBorder}\n\n`;
            content += `${border}\n${sectionHeader('CUSTOMER DETAILS')}\n${sectionBorder}\n`;
            let hasCustomerData = false;
            if (data.type === 'hotel' && data.serviceDetails && Array.isArray(data.serviceDetails) && data.serviceDetails.length > 0) {
                const service = data.serviceDetails[0];
                const customerName = service.fullName;
                const customerEmail = service.email;
                const customerPhone = service.countryCode && service.phone ? service.countryCode + ' ' + service.phone : service.phone;
                const customerAddress = service.address1;
                const customerCity = service.city;
                const customerState = service.state;
                const specialRequests = service.specialRequests;
                if (customerName) { hasCustomerData = true; content += `${row('Customer Name', customerName)}\n`; }
                if (customerEmail) { hasCustomerData = true; content += `${row('Email Address', customerEmail)}\n`; }
                if (customerPhone) { hasCustomerData = true; content += `${row('Phone Number', customerPhone)}\n`; }
                if (customerAddress) { hasCustomerData = true; content += `${row('Address', customerAddress)}\n`; }
                if (customerCity) { hasCustomerData = true; content += `${row('City', customerCity)}\n`; }
                if (customerState) { hasCustomerData = true; content += `${row('State/Province', customerState)}\n`; }
                if (specialRequests) { hasCustomerData = true; content += `${row('Special Requests', specialRequests)}\n`; }
            }
            if (!hasCustomerData) {
                content += `${row('Note', 'Customer details to be provided')}\n`;
            }
            content += `${endBorder}\n\n`;
            content += `${border}\n${sectionHeader('IMPORTANT NOTES')}\n${sectionBorder}\n`;
            content += `${fullRow('• Please confirm this booking within 24 hours')}\n`;
            content += `${fullRow('• All timings are local time')}\n`;
            content += `${fullRow('• Prices are subject to availability and confirmation')}\n`;
            content += `${fullRow('• Terms and conditions apply')}\n`;
            content += `${fullRow('')}\n`;
            content += `${fullRow('For any queries or modifications, please contact us immediately.')}\n`;
            content += `${endBorder}\n\nBest regards,\n${data.dmcCompany}`;
            return content;
        }

        // Format date for email display
        function formatEmailDate(dateString) {
            if (!dateString) return 'N/A';
            try {
                let date = new Date(dateString);
                return date.toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
            } catch (e) {
                return dateString;
            }
        }

        // View Details Button Click Handler
        $('.view-details').on('click', function() {
            let type = $(this).data('type');
            let encodedDetails = $(this).data('details');
            let modalBody = $('#detailsModalBody');
            
            // Clear previous content
            modalBody.empty();
            
            // First decode HTML entities
            let decodedDetails = $('<div/>').html(encodedDetails).text();
            
            // Parse the details safely
            let details;
            
            try {
                details = JSON.parse(decodedDetails);
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
                            <div class="card-header bg-primary text-white py-3">
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
                                           <h1 class="display-6 fw-bold text-dark mb-0">SGD ${parseFloat(item.totalPrice || 0).toFixed(2)}</h1>
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
                            <div class="card-header bg-primary text-white py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-money-bill-wave me-2 fa-lg"></i>
                                    <h5 class="mb-0 fw-bold">Payment Details</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="text-center p-4 bg-light rounded mb-4 mt-3">
                                    <p class="text-muted mb-1">Total Amount</p>
                                    <h1 class="display-6 text-dark mb-0">SGD ${parseFloat(item.totalPrice || 0).toFixed(2)}</h1>
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
                                <div class="card-header bg-primary text-white py-3">
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
                                                <h1 class="display-6 text-dark mb-0">SGD ${parseFloat(item.totalPrice || 0).toFixed(2)}</h1>
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
                            <div class="card-header bg-primary text-white py-3">
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
                                            <h1 class="display-6 text-dark mb-0">SGD ${parseFloat(item.totalPrice || 0).toFixed(2)}</h1>
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
                            <div class="card-header bg-primary text-white py-3">
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
                                            <h1 class="display-6 text-dark mb-0">SGD ${parseFloat(item.totalPrice || 0).toFixed(2)}</h1>
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
                            <div class="card-header bg-primary text-white py-3">
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
                                            <h1 class="display-6 text-dark mb-0">SGD ${parseFloat(item.totalPrice || 0).toFixed(2)}</h1>
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
                container.html('<div class="alert alert-warning">No departure details available</div>');
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
                                            <h5 class="mb-0 fw-bold">Departure Information</h5>
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
                                    <div class="card-header bg-primary text-white py-3">
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
                                                    <h1 class="display-6 text-dark mb-0">SGD ${parseFloat(item.totalPrice || 0).toFixed(2)}</h1>
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
                                    <div class="card-header bg-primary text-white py-3">
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
                                                    <h1 class="display-6 text-dark mb-0">SGD ${parseFloat(item.totalPrice || 0).toFixed(2)}</h1>
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

// Enhanced View Itinerary Button Click Handler
$('.view-itinerary').on('click', function() {
    let tourId = $(this).data('tour-id');
    let modalBody = $('#itineraryModalBody');
    
    // Get tour details from the DOM
    let tourStartDate = $(this).data('tour-start') || '';
    let tourEndDate = $(this).data('tour-end') || '';
    
    // Format dates for display
    let dateRangeText = '';
    if (tourStartDate && tourEndDate) {
        dateRangeText = `<span class="text-muted">(${formatDate(tourStartDate)} to ${formatDate(tourEndDate)})</span>`;
    }
    
    // Update modal title with tour ID and date range
    $('#itineraryModalLabel').html(`Tour #${tourId} Itinerary ${dateRangeText}`);
    
    // Show loading spinner
    modalBody.html(`
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading itinerary...</p>
        </div>
    `);
    
    // Get all services for this tour from the page
    // This method keeps everything client-side without additional AJAX calls
    let services = [];
    
    console.log("Getting services for tour ID:", tourId);
    
    // Loop through all accordion panels to find services for this specific tour
    $('#collapse' + tourId + ' .accordion-item').each(function() {
        let type = $(this).find('.accordion-button .fw-bold').text().trim().toLowerCase();
        console.log("Found type:", type);
        
        // Get each service within this type
        $(this).find('.service-item').each(function() {
            // Extract booking ID from the badge with ID
            let bookingId = '';
            $(this).find('.badge').each(function() {
                if ($(this).text().includes('ID:')) {
                    bookingId = $(this).text().replace('ID:', '').trim();
                }
            });
            
            // Initialize service data
            let serviceData = {
                type: type,
                bookingId: bookingId,
                details: $(this).find('button.view-details').data('details'),
                isEntryPort: type === 'entry port' || type === 'entry_port',
                isExitPort: type === 'exit port' || type === 'exit_port'
            };
            
            // Extract the booking date from the details - optimized
             if (serviceData.details) {
                 try {
                     // Decode the details efficiently
                     const decodedDetails = $('<div/>').html(serviceData.details).text();
                     const details = JSON.parse(decodedDetails);
                     
                     // Store the raw data for view modal consistency
                     serviceData.rawData = details;
                     
                     // Optimized date extraction to ensure View Modal and Itinerary consistency
                     const isHotel = serviceData.type.toLowerCase() === 'hotel';
                     
                     // Get date information from the most appropriate source
                     if (details.bookingDate) {
                         // HOTEL HANDLING - Use the exact original date range
                         if (isHotel && Array.isArray(details.bookingDate)) {
                             serviceData.date = details.bookingDate[0]; // Use first date for sorting
                             serviceData.dateRange = [...details.bookingDate]; // Store full range
                             serviceData.isHotel = true;
                             serviceData.icon = "fa-hotel";
                             serviceData.color = "#3F7CAD";  // Blue color for hotels
                         } 
                         // Regular booking date handling
                         else {
                             serviceData.date = Array.isArray(details.bookingDate) 
                                 ? details.bookingDate[0] 
                                 : details.bookingDate;
                         }
                     } 
                     // Alternative date sources in priority order
                     else if (details.pickupdate) {
                         serviceData.date = details.pickupdate;
                     } 
                     else if (details.exitpickupdate) {
                         serviceData.date = details.exitpickupdate;
                     }
                     
                     // Preserve original date string for exact matching with view modal
                     serviceData.dateOriginal = serviceData.date;
                 } catch (e) {
                     console.error("Error extracting date from details:", e);
                 }
             } else {
                 console.log("No details available for this service");
             }
            
            // If no booking date in details, try to get it from the badge
            if (!serviceData.date) {
                $(this).find('.badge').each(function() {
                    let badgeText = $(this).text().trim();
                    if ($(this).find('i.fas.fa-calendar-alt').length > 0) {
                        let dateParts = badgeText.match(/(\d{1,2}\s+[a-zA-Z]{3}\s+\d{4})/);
                        if (dateParts && dateParts[1]) {
                            serviceData.date = dateParts[1];
                            serviceData.dateObj = new Date(dateParts[1]);
                        }
                    }
                });
            }
            
            // Optimized service details processing - avoid duplicate parsing
            if (!serviceData.rawData && serviceData.details) {
                try {
                    // Parse details if not already done above
                    const decodedDetails = $('<div/>').html(serviceData.details).text();
                    const details = JSON.parse(decodedDetails);
                    serviceData.rawData = details;
                    
                    // Extract service name and other properties
                    if (details) {
                        // Hotel special handling
                        if (details.hotelDetails && details.hotelDetails.hotel_name) {
                            serviceData.name = details.hotelDetails.hotel_name;
                            
                            // Set up hotel properties if not already done
                            if (!serviceData.isHotel && details.bookingDate && Array.isArray(details.bookingDate)) {
                                serviceData.isHotel = true;
                                serviceData.dateRange = [...details.bookingDate];
                                serviceData.date = details.bookingDate[0];
                                serviceData.dateOriginal = details.bookingDate[0];
                                serviceData.displayName = serviceData.name;
                                serviceData.icon = "fa-hotel";
                                serviceData.color = "#3F7CAD";
                            } 
                            // Alternative hotel date sources
                            else if (!serviceData.date && details.hotelDetails.check_in) {
                                serviceData.date = details.hotelDetails.check_in;
                                
                                // Create date range if check-out exists
                                if (details.hotelDetails.check_out) {
                                    serviceData.isHotel = true;
                                    serviceData.dateRange = [details.hotelDetails.check_in, details.hotelDetails.check_out];
                                    serviceData.icon = "fa-hotel";
                                    serviceData.color = "#3F7CAD";
                                }
                            }
                        }
                        
                        // For other service types - set dates if not already set
                        if (!serviceData.date && details.bookingDate) {
                            serviceData.date = Array.isArray(details.bookingDate) 
                                ? details.bookingDate[0] 
                                : details.bookingDate;
                            serviceData.dateOriginal = serviceData.date;
                        }
                        
                        // Set a default name if none was found
                        if (!serviceData.name) {
                            serviceData.name = details.name || details.guide_name || 
                                (type.charAt(0).toUpperCase() + type.slice(1) + ' Service');
                        }
                    }
                } catch (e) {
                    console.error("Error processing service details:", e);
                }
            } 
            // Set a default name if no details available
            else if (!serviceData.name) {
                serviceData.name = type.charAt(0).toUpperCase() + type.slice(1) + ' Service';
            }
            
            // Make sure we have a valid date for sorting
            if (!serviceData.date) {
                // Use today's date as fallback for items without dates
                const today = new Date();
                serviceData.date = today.toISOString().split('T')[0];
                serviceData.dateObj = today;
                serviceData.missingDate = true;
            } else {
                // Create date object for sorting if not already set
                if (!serviceData.dateObj) {
                    serviceData.dateObj = new Date(serviceData.date);
                }
            }
            
            // Add the service to our list
            services.push(serviceData);
        });
    });
    
    // Optimized services sorting by date
    services.sort((a, b) => {
        // Primary sort: Missing dates go at the end
        if (a.missingDate !== b.missingDate) {
            return a.missingDate ? 1 : -1;
        }
        
        // Secondary sort: Compare date objects for chronological order
        return a.dateObj - b.dateObj;
    });
    
    // Check if we have services to display
    if (services.length === 0) {
        modalBody.html(`
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No services found for this tour.
            </div>
        `);
        return;
    }
    
    console.log("Total services found:", services.length);
    
    // Special handling for entry_port and exit_port
    // Move entry_port to the beginning and exit_port to the end regardless of date
    let entryPorts = services.filter(s => s.isEntryPort);
    let exitPorts = services.filter(s => s.isExitPort);
    let otherServices = services.filter(s => !s.isEntryPort && !s.isExitPort);
    
    // Make sure entry ports are sorted by date (earliest first)
    entryPorts.sort((a, b) => {
        if (a.dateObj && b.dateObj) return a.dateObj - b.dateObj;
        return 0;
    });
    
    // Make sure exit ports are sorted by date (latest last)
    exitPorts.sort((a, b) => {
        if (a.dateObj && b.dateObj) return a.dateObj - b.dateObj;
        return 0;
    });
    
    // Reassemble services with entry ports first, then other services, then exit ports
    services = [...entryPorts, ...otherServices, ...exitPorts];
    
    console.log("Entry ports:", entryPorts.length);
    console.log("Regular services:", otherServices.length);
    console.log("Exit ports:", exitPorts.length);
    
    // Limit the number of services shown if there are too many
    const MAX_SERVICES = 100; // Set a reasonable limit
    let hasMoreServices = false;
    
    if (services.length > MAX_SERVICES) {
        console.log("Limiting services shown to", MAX_SERVICES);
        hasMoreServices = true;
        services = services.slice(0, MAX_SERVICES);
    }
    
    // Helper function to count services on a given display date
    const getServiceCountForDate = (allServices, dateStr) => {
        return allServices.filter(s => {
            const svcDate = s.isHotel && s.dateRange && s.dateRange.length >= 1 
                ? formatDate(s.dateRange[0]) 
                : (s.rawData && s.rawData.bookingDate 
                    ? formatDate(Array.isArray(s.rawData.bookingDate) 
                        ? s.rawData.bookingDate[0] 
                        : s.rawData.bookingDate) 
                    : formatDate(s.date || ''));
            return svcDate === dateStr;
        }).length;
    };
    
    // Get the tour's date range for reference
    let tourDateRangeStart = tourStartDate ? new Date(tourStartDate) : null;
    let tourDateRangeEnd = tourEndDate ? new Date(tourEndDate) : null;
    
    // Helper function to check if a service date is within the tour date range
    const isWithinTourRange = (dateObj) => {
        if (!tourDateRangeStart || !tourDateRangeEnd) return true; // If tour dates not available, always show
        return dateObj >= tourDateRangeStart && dateObj <= tourDateRangeEnd;
    };
    
    // Get all the unique dates that have services
    const allServiceDates = services.map(s => {
        const dateObj = s.dateObj || new Date(s.date);
        return {
            dateObj,
            formattedDate: formatDate(s.date),
            isInTourRange: isWithinTourRange(dateObj)
        };
    });
    
    // Generate the HTML for the itinerary timeline with tour date range context
    let itineraryHTML = `
        <div class="container-fluid">
            <!-- Itinerary Summary -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                Itinerary Overview
                            </h5>
                            
                            <!-- Tour date range info -->
                            ${tourStartDate && tourEndDate ? `
                            <div class="alert alert-info mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-plane-departure fa-2x text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Tour Date Range</h6>
                                        <p class="mb-0">
                                            <strong>${formatDate(tourStartDate)}</strong> to <strong>${formatDate(tourEndDate)}</strong>
                                            <span class="badge bg-secondary ms-2">${Math.ceil((new Date(tourEndDate) - new Date(tourStartDate)) / (1000 * 60 * 60 * 24)) || 1} day(s)</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            ` : ''}
                            
                            <!-- Service statistics -->
                            <div class="d-flex flex-wrap justify-content-between">
                                <div class="border rounded p-3 mb-2 me-2 flex-grow-1 bg-light">
                                    <h6 class="mb-2"><i class="fas fa-calendar-check text-success me-2"></i>Services</h6>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>Total services:</div>
                                        <span class="badge bg-primary">${services.length}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <div>Unique dates:</div>
                                        <span class="badge bg-info">${Array.from(new Set(services.map(s => formatDate(s.date)))).length}</span>
                                    </div>
                                </div>
                                
                                <!-- Booking dates vs tour dates -->
                                <div class="border rounded p-3 mb-2 flex-grow-1 bg-light">
                                    <h6 class="mb-2"><i class="fas fa-calendar-alt text-primary me-2"></i>Booking Dates</h6>
                                    <div class="small text-muted mb-2">Services are displayed chronologically by booking date:</div>
                                    <ul class="list-unstyled mb-0 small">
                                        <li>
                                            <i class="fas fa-check-circle text-success me-1"></i> 
                                            First service: <strong>${services.length > 0 ? formatDate(services[0].date) : 'N/A'}</strong>
                                        </li>
                                        <li>
                                            <i class="fas fa-check-circle text-danger me-1"></i> 
                                            Last service: <strong>${services.length > 0 ? formatDate(services[services.length-1].date) : 'N/A'}</strong>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="timeline-container">
    `;
    
    let currentDate = '';
    
    // Add each service to the timeline
    services.forEach(function(service, index) {
        // Optimized date formatting for timeline display
        // Choose the best date source for display - same logic as view modal
        let displayDate = "Date not available";
        
        // Priority order for date sources
        if (service.isHotel && service.dateRange && service.dateRange.length >= 1) {
            // For hotels with date ranges, always use check-in date
            displayDate = formatDate(service.dateRange[0]);
        }
        else if (service.rawData && service.rawData.bookingDate) {
            // Direct access to raw data for consistency with view modal
            const bookingDate = service.rawData.bookingDate;
            displayDate = formatDate(Array.isArray(bookingDate) ? bookingDate[0] : bookingDate);
        }
        else if (service.dateOriginal) {
            // Use original preserved date string if available
            displayDate = formatDate(service.dateOriginal);
        }
        else if (service.date) {
            // Fallback to regular date field
            displayDate = formatDate(service.date);
        }
        
        // Add date header if this is a new date
        if (displayDate !== currentDate) {
            // Determine if this date is the first or last in the itinerary
            const isFirstDate = displayDate === formatDate(services[0].date);
            const isLastDate = displayDate === formatDate(services[services.length-1].date);
            
            // Add special styling for first/last dates
            const dateHighlight = isFirstDate ? 'first-date' : (isLastDate ? 'last-date' : '');
            const specialLabel = isFirstDate ? 
                '<span class="badge bg-success ms-2">First Day</span>' : 
                (isLastDate ? '<span class="badge bg-danger ms-2">Last Day</span>' : '');
            
            itineraryHTML += `
                <div class="timeline-date ${dateHighlight}">
                    <div class="date-badge" title="${service.isHotel ? 'Hotel check-in date' : 'Service booking date'}">
                        <i class="fas fa-calendar-day me-2"></i>
                        ${displayDate}
                        ${specialLabel}
                    </div>
                    <div class="date-info text-muted small mt-1 ms-2">
                        <strong>${getServiceCountForDate(services, displayDate)}</strong> service(s) scheduled for this date
                    </div>
                </div>
            `;
            currentDate = displayDate;
        }
        
        // Add the service item
        // Get appropriate icon and color based on service type
        const getServiceTypeIcon = (type) => {
            const typeStr = (type || '').toLowerCase();
            if (typeStr.includes('hotel')) return { icon: 'fa-hotel', color: '#3F7CAD' };
            if (typeStr.includes('attraction')) return { icon: 'fa-ticket-alt', color: '#28a745' };
            if (typeStr.includes('guide')) return { icon: 'fa-user-tie', color: '#6B4F4F' };
            if (typeStr.includes('restaurant')) return { icon: 'fa-utensils', color: '#dc3545' };
            if (typeStr.includes('travel')) return { icon: 'fa-car', color: '#fd7e14' }; 
            if (typeStr.includes('port')) return { icon: 'fa-plane', color: '#6f42c1' };
            return { icon: 'fa-tag', color: '#757575' };
        };
        
        // Use predefined icons if not set
        const serviceType = getServiceTypeIcon(service.type);
        const icon = service.icon || serviceType.icon;
        const color = service.color || serviceType.color;
        
        // Format the booking time if available
        let timeDisplay = 'No time specified';
        if (service.rawData) {
            if (service.rawData.visitTime) timeDisplay = service.rawData.visitTime;
            else if (service.rawData.entrytime) timeDisplay = service.rawData.entrytime;
        }
        
        itineraryHTML += `
            <div class="timeline-item">
                <div class="timeline-point" style="background-color: ${color}">
                    <i class="fas ${icon}"></i>
                </div>
                <div class="timeline-content">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title d-flex align-items-center flex-wrap">
                                <span class="badge me-2" style="background-color: ${color}">
                                    <i class="fas ${icon} me-1"></i>
                                    ${(service.type || '').replace('_', ' ').toUpperCase()}
                                </span>
                                <span>${service.displayName || service.name || 'Service #' + service.bookingId}</span>
                            </h5>
                            
                            ${service.isHotel && service.dateRange ? `
                            <div class="alert alert-info mb-3 py-2">
                                <strong><i class="fas fa-hotel me-1"></i> Hotel Stay Period:</strong> 
                                <span class="ms-2"><i class="fas fa-calendar-alt me-1"></i> ${formatDate(service.dateRange[0])} to ${formatDate(service.dateRange[1])}</span>
                            </div>
                            ` : ''}
                            
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="me-2 text-primary">
                                            <i class="fas fa-calendar-day"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Booking Date</small>
                                            <strong>${displayDate}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="me-2 text-primary">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Time</small>
                                            <strong>${timeDisplay}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            ${service.isCheckIn ? `
                            <div class="mb-2">
                                <span class="badge bg-success">
                                    <i class="fas fa-calendar-check me-1"></i> Check-in Day
                                </span>
                            </div>
                            ` : service.isCheckOut ? `
                            <div class="mb-2">
                                <span class="badge bg-danger">
                                    <i class="fas fa-calendar-times me-1"></i> Check-out Day
                                </span>
                            </div>
                            ` : ''}
                            
                            <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                                <span class="text-muted">
                                    <small>Booking ID: ${service.bookingId || 'N/A'}</small>
                                </span>
                                ${service.missingDate ? `
                                <span class="text-warning">
                                    <small><i class="fas fa-exclamation-circle me-1"></i> No date available</small>
                                </span>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    itineraryHTML += `
                    </div>
                    ${hasMoreServices ? `
                    <div class="text-center mt-4 pt-3 border-top">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Showing ${MAX_SERVICES} services. ${services.length + (services.length - MAX_SERVICES)} more services are not displayed.
                        </div>
                    </div>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
    
    // Add the CSS for the timeline styling
    let timelineCSS = `
        <style>
            .timeline-container {
                position: relative;
                padding: 20px 0;
            }
            
            .timeline-container::before {
                content: '';
                position: absolute;
                top: 0;
                bottom: 0;
                left: 20px;
                width: 4px;
                background: #e9ecef;
                border-radius: 2px;
            }
            
            .timeline-date {
                position: relative;
                margin-bottom: 20px;
                padding-left: 45px;
            }
            
            /* First and last date styling */
            .timeline-date.first-date .date-badge {
                background-color: #28a745;
                box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
            }
            
            .timeline-date.last-date .date-badge {
                background-color: #dc3545;
                box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
            }
            
            .date-badge {
                display: inline-block;
                padding: 8px 16px;
                background-color: #435ebe;
                color: white;
                border-radius: 20px;
                font-weight: bold;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            
            .timeline-item {
                position: relative;
                margin-bottom: 30px;
                padding-left: 45px;
            }
            
            .timeline-point {
                position: absolute;
                width: 30px;
                height: 30px;
                left: 7px;
                top: 15px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                z-index: 1;
            }
            
            .timeline-content {
                padding-bottom: 10px;
            }
            
            .timeline-content .card {
                border-radius: 8px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                border: none;
            }
            
            .timeline-content .card-title {
                font-size: 1.1rem;
                margin-bottom: 0.75rem;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .timeline-content .badge {
                font-size: 0.75rem;
                padding: 0.4rem 0.6rem;
                border-radius: 4px;
            }
        </style>
    `;
    
    // Set the modal content
    modalBody.html(timelineCSS + itineraryHTML);
}

// Optimized helper function to format dates consistently across the entire application
function formatDate(dateString) {
    // Quick validation
    if (!dateString) return 'Unknown Date';
    
    try {
        let date;
        
        // Cache for repeated date strings to improve performance
        const dateCache = {};
        const cacheKey = typeof dateString === 'string' ? dateString : String(dateString);
        
        if (dateCache[cacheKey]) {
            return dateCache[cacheKey];
        }
        
        // Parse different date formats efficiently
        if (typeof dateString === 'string') {
            // Most common format: YYYY-MM-DD (ISO format)
            if (dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
                const [year, month, day] = dateString.split('-').map(Number);
                date = new Date(year, month - 1, day);
            }
            // Handle slash format: MM/DD/YYYY or DD/MM/YYYY
            else if (dateString.includes('/')) {
                const parts = dateString.split('/');
                if (parts.length === 3) {
                    const first = parseInt(parts[0], 10);
                    const second = parseInt(parts[1], 10);
                    const year = parseInt(parts[2], 10);
                    
                    // Smart detection: If first part > 12, it must be a day (DD/MM/YYYY)
                    date = first > 12 
                        ? new Date(year, second - 1, first)  // DD/MM/YYYY
                        : new Date(year, first - 1, second); // MM/DD/YYYY
                } else {
                    date = new Date(dateString);
                }
            }
            // All other formats, use native parsing
            else {
                date = new Date(dateString);
            }
        } else if (dateString instanceof Date) {
            date = dateString;
        } else {
            date = new Date(dateString);
        }
        
        // Validate the parsed date
        if (isNaN(date.getTime())) {
            return "Date not available";
        }
        
        // Format consistently across the entire application
        // Format: "Thursday, May 1, 2025"
        const options = { 
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        
        // Get formatted result
        const result = date.toLocaleDateString('en-US', options);
        
        // Cache the result for future use
        dateCache[cacheKey] = result;
        
        return result;
    } catch (e) {
        return "Date not available";
    }
}

// Calculate display due date function
function calculateDisplayDueDate() {
    const actualDueDate = document.getElementById('actual_due_date').value;
    const daysBefore = document.getElementById('display_days_before').value;
    const displayDueDateField = document.getElementById('display_due_date');
    
    if (actualDueDate && daysBefore) {
        const actualDate = new Date(actualDueDate);
        const displayDate = new Date(actualDate);
        displayDate.setDate(actualDate.getDate() - parseInt(daysBefore));
        
        // Format date as YYYY-MM-DD for input field
        const formattedDate = displayDate.toISOString().split('T')[0];
        displayDueDateField.value = formattedDate;
    } else {
        displayDueDateField.value = '';
    }
}

// Add event listeners for due date calculation
$(document).ready(function() {
    // Add event listeners for the approve modal due date calculations
    $('#actual_due_date').on('change', calculateDisplayDueDate);
    $('#display_days_before').on('change', calculateDisplayDueDate);
    
    // Clear fields when modal is closed/opened
    $('#approveModal').on('hidden.bs.modal', function () {
        $('#actual_due_date').val('');
        $('#display_days_before').val('');
        $('#display_due_date').val('');
        $('#reference_id').val('');
        $('#reference_file').val('');
    });
});
</script>
@endsection