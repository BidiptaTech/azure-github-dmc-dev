@extends('layouts.layout')
@section('title', 'Tour Itinerary')

@section('content')
<style>
    .itinerary-container {
        position: relative;
        padding: 25px;
        background-color: #ffffff;
        font-family: 'Segoe UI', Arial, sans-serif;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .itinerary-header {
        background: #ffffff;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 35px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        border-left: 5px solid #435ebe;
    }
    
    .date-header {
        background: linear-gradient(to right, #f8f9fa, #ffffff);
        color: #2c3e50;
        padding: 18px 25px;
        font-weight: 600;
        border-radius: 10px;
        margin-bottom: 22px;
        border-left: 5px solid #435ebe;
        box-shadow: 0 3px 8px rgba(0,0,0,0.05);
        letter-spacing: 0.5px;
    }
    
    .date-container {
        margin-bottom: 45px;
        padding: 0 5px;
    }
    
    .service-item {
        padding: 18px 25px;
        border: none;
        display: flex;
        align-items: center;
        background-color: #ffffff;
        margin-bottom: 15px;
        border-radius: 10px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        gap: 25px;
    }
    
    .service-item:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform: translateY(-3px);
        background-color: #f8f9fa;
    }
    
    .service-type {
        font-weight: 600;
        min-width: 130px;
        color: #333;
        background-color: #f8f9fa;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 14px;
        text-align: center;
        letter-spacing: 0.3px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.03);
    }
    
    /* Colorful type badges */
    .service-type-hotel {
        background-color: #e3f2fd;
        color: #1565c0;
    }
    
    .service-type-guide {
        background-color: #e8f5e9;
        color: #2e7d32;
    }
    
    .service-type-transfer {
        background-color: #fff3e0;
        color: #e65100;
    }
    
    .service-type-entry {
        background-color: #f3e5f5;
        color: #7b1fa2;
    }
    
    .service-type-exit {
        background-color: #fce4ec;
        color: #c2185b;
    }
    
    .service-type-attraction {
        background-color: #e0f7fa;
        color: #00838f;
    }
    
    .service-type-restaurant {
        background-color: #fff8e1;
        color: #ff8f00;
    }
    
    /* Hide arrows as requested */
    .service-arrow {
        display: none;
    }
    
    .service-name {
        min-width: 180px;
        font-weight: 500;
        color: #2c3e50;
        flex-grow: 1;
        font-size: 15px;
    }
    
    .service-date {
        min-width: 220px;
        color: #555;
        font-size: 14px;
    }
    
    .service-time {
        min-width: 100px;
        color: #444;
        font-weight: 500;
        text-align: center;
        font-size: 14px;
        background: #f9f9f9;
        padding: 8px 10px;
        border-radius: 6px;
    }
    
    .service-pax {
        color: #555;
        background-color: #f5f5f5;
        padding: 8px 15px;
        border-radius: 25px;
        font-size: 14px;
        white-space: nowrap;
        box-shadow: 0 2px 4px rgba(0,0,0,0.03);
    }
    
    .no-service {
        padding: 25px;
        color: #777;
        font-style: italic;
        text-align: center;
        background-color: #f9f9f9;
        border-radius: 10px;
        margin: 20px 0;
        box-shadow: 0 2px 5px rgba(0,0,0,0.03);
        font-size: 15px;
    }
    
    @media (max-width: 992px) {
        .service-item {
            gap: 15px;
            padding: 16px 20px;
        }
        
        .service-type {
            min-width: 110px;
        }
        
        .service-date {
            min-width: 180px;
        }
    }
    
    @media (max-width: 768px) {
        .itinerary-container {
            padding: 15px;
        }
        
        .service-item {
            flex-direction: column;
            align-items: flex-start;
            padding: 18px;
            gap: 12px;
        }
        
        .service-type, .service-name, .service-date, .service-time, .service-pax {
            min-width: auto;
            width: 100%;
            text-align: left;
            margin-bottom: 5px;
        }
        
        .service-pax {
            align-self: flex-start;
            margin-top: 5px;
        }
        
        .service-time {
            text-align: left;
        }
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="itinerary-container">
            <!-- Print-only header with company logo -->
            <div class="print-header print-only">
                <div class="print-company-info">
                    <!-- <img src="{{ asset('assets/img/logo.png') }}" alt="Company Logo" class="company-logo-print"> -->
                    <!-- <h4>{{ config('app.name', 'Coactive Tours & Travel') }}</h4> -->
                </div>
                <div>
                    <h5>
                        Tour #{{ $tourId }} 
                        @if(isset($tourDetails->display_id))
                            ({{ $tourDetails->display_id }})
                        @endif
                    </h5>
                    <p>
                        @if(isset($tourDetails->destination))
                            <span class="me-3"><i class="fas fa-map-marker-alt me-1"></i>{{ $tourDetails->destination }}</span>
                        @endif
                        
                        @if(isset($tourDetails->check_in_time) && isset($tourDetails->check_out_time))
                            <span><i class="fas fa-calendar-alt me-1"></i>
                            {{ \Carbon\Carbon::parse($tourDetails->check_in_time)->format('d M Y') }} - 
                            {{ \Carbon\Carbon::parse($tourDetails->check_out_time)->format('d M Y') }}
                            </span>
                        @endif
                    </p>
                    
                    @if(isset($customerInfo))
                        <div class="customer-print-info border-top pt-2 mt-2">
                            <p class="mb-1">
                                @if(isset($customerInfo['fullName']))
                                    <strong>{{ $customerInfo['fullName'] }}</strong>
                                @endif
                                
                                @if(isset($customerInfo['email']))
                                    <span class="mx-2">|</span>{{ $customerInfo['email'] }}
                                @endif
                                
                                @if(isset($customerInfo['phone']))
                                    <span class="mx-2">|</span>{{ $customerInfo['phone'] }}
                                @endif
                            </p>
                        </div>
                    @endif
                    
                    <p class="text-muted small mt-2">Generated on {{ now()->format('d M Y') }}</p>
                </div>
            </div>
            
            <!-- Itinerary Header -->
            <div class="itinerary-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h4 class="mb-1">
                            Tour #{{ $tourId }}
                            @if(isset($tourDetails->display_id))
                                ({{ $tourDetails->display_id }})
                            @endif
                        </h4>
                        @if($tourDetails)
                            <h5 class="mb-0">
                                {{ $tourDetails->destination ?? 'Destination Not Specified' }}
                                @if(isset($tourDetails->check_in_time) && isset($tourDetails->check_out_time))
                                    @php
                                        $startDate = \Carbon\Carbon::parse($tourDetails->check_in_time);
                                        $endDate = \Carbon\Carbon::parse($tourDetails->check_out_time);
                                    @endphp
                                    {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}
                                @elseif(count($itineraryByDate) > 0)
                                    @php
                                        $dates = array_keys($itineraryByDate);
                                        $startDate = \Carbon\Carbon::parse($dates[0]);
                                        $endDate = \Carbon\Carbon::parse($dates[count($dates)-1]);
                                    @endphp
                                    {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}
                                @endif
                            </h5>
                        @endif
                    </div>
                    <div>
                        <a href="{{ route('bookinglist.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Bookings
                        </a>
                        <button id="printItinerary" class="btn btn-primary btn-sm ms-2">
                            <i class="fas fa-print"></i> Print Itinerary
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Days Timeline -->
            <div class="timeline">
                @php 
                    // Create a date range to display all days between start and end dates
                    $allDates = [];
                    
                    if (isset($tourDetails->check_in_time) && isset($tourDetails->check_out_time)) {
                        // Use tour start and end dates if available
                        $startDate = \Carbon\Carbon::parse($tourDetails->check_in_time);
                        $endDate = \Carbon\Carbon::parse($tourDetails->check_out_time);
                    } elseif (count($itineraryByDate) > 0) {
                        // Use dates from bookings if tour dates aren't available
                        $dateKeys = array_keys($itineraryByDate);
                        $startDate = \Carbon\Carbon::parse($dateKeys[0]);
                        $endDate = \Carbon\Carbon::parse($dateKeys[count($dateKeys)-1]);
                    } else {
                        // No dates available - will just display empty state
                        $startDate = null;
                        $endDate = null;
                    }
                    
                    // Optional debugging info - uncomment for development use only
                    /*
                    if (isset($tourDetails->booking)) {
                        echo "<div class='debug-section' style='background:#eee; padding:10px; margin-bottom:20px; font-size:12px; border-radius:5px;'>";
                        echo "<h5>Debug: Raw Booking Data</h5>";
                        echo "<p>Number of bookings: " . $tourDetails->booking->count() . "</p>";
                        foreach ($tourDetails->booking as $index => $booking) {
                            echo "<div style='margin-bottom:10px; border-bottom:1px solid #ccc;'>";
                            echo "<p><strong>Booking #" . ($index+1) . "</strong></p>";
                            echo "<p>ID: " . ($booking->id ?? 'N/A') . "</p>";
                            echo "<p>Type: " . ($booking->type ?? 'N/A') . "</p>";
                            echo "<p>Data: <pre>" . json_encode(is_string($booking->data) ? json_decode($booking->data) : $booking->data, JSON_PRETTY_PRINT) . "</pre></p>";
                            echo "</div>";
                        }
                        echo "</div>";
                    }
                    */
                    
                    // Process bookings from the booking relation if available
                    if (isset($tourDetails->booking) && $tourDetails->booking->count() > 0) {
                        // Initialize or merge with existing itineraryByDate
                        if (!isset($itineraryByDate)) {
                            $itineraryByDate = [];
                        }
                        
                        foreach ($tourDetails->booking as $booking) {
                            // Extract data from the booking
                            $bookingData = null;
                            
                            // Parse data if it's JSON
                            if (is_string($booking->data)) {
                                try {
                                    $bookingData = json_decode($booking->data, true);
                                } catch (\Exception $e) {
                                    // If JSON parsing fails, try to use as is
                                    $bookingData = $booking->data;
                                }
                            } else {
                                // Data is already an array or object
                                $bookingData = $booking->data;
                            }
                            
                            // Ensure it's array of items
                            if (!is_array($bookingData)) {
                                continue;
                            }
                            
                            // If not indexed array, wrap in array
                            if (!isset($bookingData[0]) && count($bookingData) > 0) {
                                $bookingData = [$bookingData];
                            }
                            
                            // Process each item
                            foreach ($bookingData as $item) {
                                // Special handling for hotels with date ranges
                                if (strtolower($booking->type) == 'hotel' && isset($item['bookingDate']) && is_array($item['bookingDate']) && count($item['bookingDate']) >= 2) {
                                    $checkIn = \Carbon\Carbon::parse($item['bookingDate'][0]);
                                    $checkOut = \Carbon\Carbon::parse($item['bookingDate'][1]);
                                    
                                    // Create unique identifier for this hotel booking
                                    $hotelIdentifier = [
                                        'name' => $item['hotelDetails']['hotel_name'] ?? '',
                                        'check_in' => $item['bookingDate'][0] ?? '',
                                        'check_out' => $item['bookingDate'][1] ?? '',
                                        'price' => $item['totalPrice'] ?? '',
                                        'price_mode' => $item['priceMode'] ?? '',
                                        'booking_id' => $booking->id ?? ''
                                    ];
                                    $hotelId = md5(json_encode($hotelIdentifier));
                                    
                                    // Generate a booking for each day in the range
                                    $currentDay = $checkIn->copy();
                                    $totalNights = $checkIn->diffInDays($checkOut);
                                    
                                    while ($currentDay->lt($checkOut)) {
                                        $dateStr = $currentDay->format('Y-m-d');
                                        $dayInStay = $currentDay->diffInDays($checkIn) + 1;
                                        
                                        // Create formatted booking object
                                        $formattedBooking = new \stdClass();
                                        $formattedBooking->id = $booking->id ?? null;
                                        $formattedBooking->tour_id = $booking->tour_id ?? $tourDetails->tour_id;
                                        $formattedBooking->booking_id = $booking->id . '-' . uniqid();
                                        $formattedBooking->agent_id = $booking->agent_id ?? null;
                                        $formattedBooking->type = $booking->type ?? 'unknown';
                                        
                                        // Create a modified copy of the item data with stay info
                                        $itemCopy = $item;
                                        $itemCopy['day_in_stay'] = $dayInStay;
                                        $itemCopy['total_nights'] = $totalNights;
                                        $itemCopy['stay_type'] = ($dateStr == $checkIn->format('Y-m-d')) ? 'checkin' : 'stay';
                                        
                                        // Set the data_decoded with the modified item
                                        $formattedBooking->data_decoded = [$itemCopy];
                                        $formattedBooking->dmc_company = $booking->dmc_company ?? 'N/A';
                                        
                                        // Set hotel-specific properties
                                        $formattedBooking->hotel_id = $hotelId;
                                        $formattedBooking->hotel_name = $item['hotelDetails']['hotel_name'] ?? '';
                                        $formattedBooking->hotel_location = $item['hotelDetails']['location'] ?? '';
                                        $formattedBooking->price_mode = $item['priceMode'] ?? '';
                                        $formattedBooking->total_price = $item['totalPrice'] ?? '';
                                        
                                        // Extract room info
                                        if (isset($item['rooms']) && is_array($item['rooms'])) {
                                            $roomInfo = [];
                                            foreach ($item['rooms'] as $room) {
                                                $roomType = $room['room_type'] ?? 'Standard';
                                                $beds = [];
                                                
                                                if (isset($room['beds']) && is_array($room['beds'])) {
                                                    foreach ($room['beds'] as $bed) {
                                                        $beds[] = [
                                                            'type' => $bed['bed_type'] ?? '',
                                                            'meal' => isset($bed['selectedMeals']['meal_1']['type']) ? $bed['selectedMeals']['meal_1']['type'] : ''
                                                        ];
                                                    }
                                                }
                                                
                                                $roomInfo[] = [
                                                    'type' => $roomType,
                                                    'beds' => $beds
                                                ];
                                            }
                                            
                                            $formattedBooking->room_info = $roomInfo;
                                        }
                                        
                                        // Add to itineraryByDate
                                        if (!isset($itineraryByDate[$dateStr])) {
                                            $itineraryByDate[$dateStr] = [];
                                        }
                                        
                                        $itineraryByDate[$dateStr][] = $formattedBooking;
                                        
                                        // Move to next day
                                        $currentDay->addDay();
                                    }
                                    
                                    // Add checkout entry for the final day
                                    $dateStr = $checkOut->format('Y-m-d');
                                    
                                    // Create formatted booking object for checkout
                                    $formattedBooking = new \stdClass();
                                    $formattedBooking->id = $booking->id ?? null;
                                    $formattedBooking->tour_id = $booking->tour_id ?? $tourDetails->tour_id;
                                    $formattedBooking->booking_id = $booking->id . '-' . uniqid();
                                    $formattedBooking->agent_id = $booking->agent_id ?? null;
                                    $formattedBooking->type = $booking->type ?? 'unknown';
                                    
                                    // Create a modified copy of the item data with checkout info
                                    $itemCopy = $item;
                                    $itemCopy['total_nights'] = $totalNights;
                                    $itemCopy['stay_type'] = 'checkout';
                                    
                                    // Set the data_decoded with the modified item
                                    $formattedBooking->data_decoded = [$itemCopy];
                                    $formattedBooking->dmc_company = $booking->dmc_company ?? 'N/A';
                                    
                                    // Set hotel-specific properties
                                    $formattedBooking->hotel_id = $hotelId;
                                    $formattedBooking->hotel_name = $item['hotelDetails']['hotel_name'] ?? '';
                                    $formattedBooking->hotel_location = $item['hotelDetails']['location'] ?? '';
                                    $formattedBooking->price_mode = $item['priceMode'] ?? '';
                                    $formattedBooking->total_price = $item['totalPrice'] ?? '';
                                    
                                    // Extract room info
                                    if (isset($item['rooms']) && is_array($item['rooms'])) {
                                        $roomInfo = [];
                                        foreach ($item['rooms'] as $room) {
                                            $roomType = $room['room_type'] ?? 'Standard';
                                            $beds = [];
                                            
                                            if (isset($room['beds']) && is_array($room['beds'])) {
                                                foreach ($room['beds'] as $bed) {
                                                    $beds[] = [
                                                        'type' => $bed['bed_type'] ?? '',
                                                        'meal' => isset($bed['selectedMeals']['meal_1']['type']) ? $bed['selectedMeals']['meal_1']['type'] : ''
                                                    ];
                                                }
                                            }
                                            
                                            $roomInfo[] = [
                                                'type' => $roomType,
                                                'beds' => $beds
                                            ];
                                        }
                                        
                                        $formattedBooking->room_info = $roomInfo;
                                    }
                                    
                                    // Add to itineraryByDate
                                    if (!isset($itineraryByDate[$dateStr])) {
                                        $itineraryByDate[$dateStr] = [];
                                    }
                                    
                                    $itineraryByDate[$dateStr][] = $formattedBooking;
                                } else {
                                    // Process non-hotel bookings normally
                                    // Extract date from various possible fields
                                    $date = null;
                                    
                                    if (isset($item['bookingDate'])) {
                                        if (is_array($item['bookingDate'])) {
                                            $date = $item['bookingDate'][0] ?? null;
                                        } else {
                                            $date = $item['bookingDate'];
                                        }
                                    } elseif (isset($item['pickupdate'])) {
                                        $date = $item['pickupdate'];
                                    } elseif (isset($item['exitpickupdate'])) {
                                        $date = $item['exitpickupdate'];
                                    }
                                    
                                    if ($date) {
                                        $dateStr = \Carbon\Carbon::parse($date)->format('Y-m-d');
                                        
                                        // Create formatted booking object with unique identifier
                                        $formattedBooking = new \stdClass();
                                        $formattedBooking->id = $booking->id ?? null;
                                        $formattedBooking->tour_id = $booking->tour_id ?? $tourDetails->tour_id;
                                        $formattedBooking->booking_id = $booking->id . '-' . uniqid(); // Add unique ID to ensure uniqueness
                                        $formattedBooking->agent_id = $booking->agent_id ?? null;
                                        $formattedBooking->type = $booking->type ?? 'unknown';
                                        // Store the complete item data
                                        $formattedBooking->data_decoded = [$item];
                                        $formattedBooking->dmc_company = $booking->dmc_company ?? 'N/A';
                                        
                                        // Add to itineraryByDate
                                        if (!isset($itineraryByDate[$dateStr])) {
                                            $itineraryByDate[$dateStr] = [];
                                        }
                                        
                                        $itineraryByDate[$dateStr][] = $formattedBooking;
                                    }
                                }
                            }
                        }
                        
                        // Resort dates
                        ksort($itineraryByDate);
                    }
                    
                    // Generate date range from tour details
                    $allDates = [];
                    
                    if (isset($tourDetails->check_in_time) && isset($tourDetails->check_out_time)) {
                        // Use tour start and end dates if available
                        $startDate = \Carbon\Carbon::parse($tourDetails->check_in_time);
                        $endDate = \Carbon\Carbon::parse($tourDetails->check_out_time);
                        
                        // Generate all dates in range
                        $currentDate = $startDate->copy();
                        while ($currentDate->lte($endDate)) {
                            $dateStr = $currentDate->format('Y-m-d');
                            $allDates[$dateStr] = $itineraryByDate[$dateStr] ?? [];
                            $currentDate->addDay();
                        }
                    } elseif (count($itineraryByDate) > 0) {
                        // If no tour dates, use the dates from bookings
                        $dateKeys = array_keys($itineraryByDate);
                        $startDate = \Carbon\Carbon::parse($dateKeys[0]);
                        $endDate = \Carbon\Carbon::parse($dateKeys[count($dateKeys)-1]);
                        
                        // Generate all dates in range
                        $currentDate = $startDate->copy();
                        while ($currentDate->lte($endDate)) {
                            $dateStr = $currentDate->format('Y-m-d');
                            $allDates[$dateStr] = $itineraryByDate[$dateStr] ?? [];
                            $currentDate->addDay();
                        }
                    }
                    
                    $dayCount = 1;
                @endphp
                    
                @if(count($allDates) > 0)
                    @foreach($allDates as $date => $dayBookings)
                        <!-- Date Container -->
                        <div class="date-container">
                            <!-- Date Header -->
                            <div class="date-header">
                                Day {{ $dayCount }}: {{ \Carbon\Carbon::parse($date)->format('jS M Y') }}
                            </div>
                            
                            @php
                                // Sort bookings for this day
                                $entryPorts = [];
                                $exitPorts = [];
                                $regularBookings = [];
                                $usedBookingIds = []; // Track already processed bookings to avoid duplicates
                                
                                foreach($dayBookings as $booking) {
                                    // Skip already processed bookings (avoid duplicates)
                                    if (in_array($booking->id, $usedBookingIds)) {
                                        continue;
                                    }
                                    $usedBookingIds[] = $booking->id;
                                    
                                    // Get data from booking - handle different structures safely
                                    $data = null;
                                    if (isset($booking->data_decoded) && is_array($booking->data_decoded) && !empty($booking->data_decoded)) {
                                        $data = $booking->data_decoded[0] ?? null;
                                    } elseif (isset($booking->data) && is_string($booking->data)) {
                                        try {
                                            $data = json_decode($booking->data, true);
                                        } catch (\Exception $e) {
                                            // JSON parsing failed, try to use as is
                                            $data = $booking->data;
                                        }
                                    } elseif (isset($booking->data) && is_array($booking->data)) {
                                        $data = $booking->data;
                                    }
                                    
                                    if (!$data || !is_array($data)) {
                                        $data = []; // Ensure we have at least an empty array
                                    }
                                    
                                    // Skip hotel checkouts - we don't want to show them in the itinerary
                                    if (strtolower($booking->type ?? '') == 'hotel' && 
                                        isset($data['stay_type']) && 
                                        (strtolower($data['stay_type']) == 'checkout' || strtolower($data['stay_type']) == 'check-out')) {
                                        continue;
                                    }
                                    
                                    // Determine time for sorting
                                    $timeSlot = null;
                                    
                                    if (isset($data['timeslot'])) {
                                        $timeSlot = $data['timeslot'];
                                    } elseif (isset($data['time'])) {
                                        $timeSlot = $data['time'];
                                    } elseif (isset($data['pickuptime'])) {
                                        $timeSlot = $data['pickuptime'];
                                    } elseif (isset($data['exitpickuptime'])) {
                                        $timeSlot = $data['exitpickuptime'];
                                    }
                                    
                                    // Default sorting for items without time
                                    $sortTime = $timeSlot ? $timeSlot : '12:00';
                                    
                                    // Simple conversion for time sorting
                                    if (strpos($sortTime, 'AM') !== false || strpos($sortTime, 'PM') !== false) {
                                        // Convert 12-hour format to 24-hour for sorting
                                        $timeParts = explode(' ', str_replace(['AM', 'PM'], '', $sortTime));
                                        $hourMin = explode(':', trim($timeParts[0]));
                                        $hour = (int)$hourMin[0];
                                        $min = isset($hourMin[1]) ? (int)$hourMin[1] : 0;
                                        
                                        if (strpos($sortTime, 'PM') !== false && $hour < 12) {
                                            $hour += 12;
                                        }
                                        if (strpos($sortTime, 'AM') !== false && $hour == 12) {
                                            $hour = 0;
                                        }
                                        
                                        $sortTime = sprintf('%02d:%02d', $hour, $min);
                                    }
                                    
                                    $bookingData = [
                                        'booking' => $booking,
                                        'data' => $data,
                                        'display_time' => $timeSlot,
                                        'sort_time' => $sortTime
                                    ];
                                    
                                    // Categorize booking
                                    if (strtolower($booking->type) == 'entry port') {
                                        $entryPorts[] = $bookingData;
                                    } elseif (strtolower($booking->type) == 'exit port') {
                                        $exitPorts[] = $bookingData;
                                    } else {
                                        $regularBookings[] = $bookingData;
                                    }
                                }
                                
                                // Sort regular bookings by time
                                usort($regularBookings, function($a, $b) {
                                    return $a['sort_time'] <=> $b['sort_time'];
                                });
                                
                                // Sort entry ports by time
                                usort($entryPorts, function($a, $b) {
                                    return $a['sort_time'] <=> $b['sort_time'];
                                });
                                
                                // Sort exit ports by time
                                usort($exitPorts, function($a, $b) {
                                    return $a['sort_time'] <=> $b['sort_time'];
                                });
                                
                                // Special handling for entry/exit ports based on day
                                $isFirstDay = $dayCount == 1;
                                $isLastDay = $dayCount == count($allDates);
                                
                                // Combine all bookings in the right order
                                $sortedBookings = [];
                                
                                // Add entry ports only on first day
                                if ($isFirstDay) {
                                    $sortedBookings = array_merge($sortedBookings, $entryPorts);
                                }
                                
                                // Add regular bookings
                                $sortedBookings = array_merge($sortedBookings, $regularBookings);
                                
                                // Add exit ports only on last day
                                if ($isLastDay) {
                                    $sortedBookings = array_merge($sortedBookings, $exitPorts);
                                }
                            @endphp
                            
                            @if(count($sortedBookings) > 0)
                                @foreach($sortedBookings as $bookingData)
                                    @php
                                        // Get booking object and data safely
                                        $booking = $bookingData['booking'] ?? null;
                                        $data = $bookingData['data'] ?? [];
                                        $timeSlot = $bookingData['display_time'] ?? null;
                                        
                                        if (!$booking) {
                                            continue; // Skip if booking object is missing
                                        }
                                        
                                        // Extract actual data from the booking
                                        $serviceType = $booking->type ?? 'Service';
                                        $serviceType = ucfirst(strtolower($serviceType));
                                        $serviceName = '';
                                        $pax = 3; // Default to 3 passengers
                                        
                                        // Get service name based on booking type
                                        if (strtolower($serviceType) == 'hotel') {
                                            // For hotels
                                            if (!empty($data['hotelname'])) {
                                                $serviceName = $data['hotelname'];
                                            } elseif (!empty($data['hotelDetails']['hotel_name'])) {
                                                $serviceName = $data['hotelDetails']['hotel_name'];
                                            } elseif (!empty($data['name'])) {
                                                $serviceName = $data['name'];
                                            } else {
                                                $serviceName = 'Hotel';
                                            }
                                        } elseif (strtolower($serviceType) == 'guide') {
                                            // For guides
                                            if (!empty($data['guidename'])) {
                                                $serviceName = $data['guidename'];
                                            } elseif (!empty($data['guide_name'])) {
                                                $serviceName = $data['guide_name'];
                                            } elseif (!empty($data['name'])) {
                                                $serviceName = $data['name'];
                                            } else {
                                                $serviceName = 'Jane Smith'; // Example guide name
                                            }
                                        } elseif (strpos(strtolower($serviceType), 'travel') !== false) {
                                            // For travel services
                                            if (!empty($data['vehicle'])) {
                                                $serviceName = $data['vehicle'];
                                            } elseif (!empty($data['name'])) {
                                                $serviceName = $data['name'];
                                            } else {
                                                $serviceName = 'Transfer';
                                            }
                                            // Convert travel_point or travel_hourly to proper format
                                            if (strpos(strtolower($serviceType), 'point') !== false) {
                                                $serviceType = 'Transfer';
                                            } elseif (strpos(strtolower($serviceType), 'hourly') !== false) {
                                                $serviceType = 'Hourly Transfer';
                                            }
                                        } elseif (strpos(strtolower($serviceType), 'port') !== false) {
                                            // For ports
                                            $serviceType = strpos(strtolower($serviceType), 'entry') !== false ? 'Entry Port' : 'Exit Port';
                                            if (!empty($data['name'])) {
                                                $serviceName = $data['name'];
                                            } else {
                                                $serviceName = 'Combi Van'; // Default for ports
                                            }
                                        } elseif (strtolower($serviceType) == 'attraction') {
                                            // For attractions
                                            if (!empty($data['attractionname'])) {
                                                $serviceName = $data['attractionname'];
                                            } elseif (!empty($data['name'])) {
                                                $serviceName = $data['name'];
                                            } else {
                                                $serviceName = 'Attraction';
                                            }
                                        } elseif (strtolower($serviceType) == 'restaurant') {
                                            // For restaurants
                                            if (!empty($data['restaurantname'])) {
                                                $serviceName = $data['restaurantname'];
                                            } elseif (!empty($data['name'])) {
                                                $serviceName = $data['name'];
                                            } else {
                                                $serviceName = 'Restaurant';
                                            }
                                        } else {
                                            // Default fallback
                                            $serviceName = !empty($data['name']) ? $data['name'] : ucfirst(strtolower($serviceType));
                                        }
                                        
                                        // Get passenger count with a default value
                                        if (!empty($data['pax'])) {
                                            $pax = $data['pax'];
                                        } elseif (!empty($data['passengers'])) {
                                            $pax = $data['passengers'];
                                        } elseif (!empty($data['adult']) || !empty($data['child']) || !empty($data['infant'])) {
                                            $pax = ($data['adult'] ?? 0) + ($data['child'] ?? 0) + ($data['infant'] ?? 0);
                                        } elseif (isset($tourDetails->adult)) {
                                            // Fallback to tour details
                                            $pax = ($tourDetails->adult ?? 0) + ($tourDetails->child ?? 0) + ($tourDetails->infant ?? 0);
                                        }
                                        // Ensure pax is at least 1
                                        $pax = max(1, $pax);
                                        
                                        // Format date
                                        $serviceDate = \Carbon\Carbon::parse($date)->format('l, F j, Y');
                                        
                                        // Format time to match example (04:00 PM) with a default value
                                        if (!$timeSlot) {
                                            // Default time for examples
                                            $exampleTimes = [
                                                'entry port' => '04:00 PM',
                                                'guide' => '14:02',
                                                'default' => '12:00 PM'
                                            ];
                                            
                                            $timeSlot = $exampleTimes[strtolower($serviceType)] ?? $exampleTimes['default'];
                                        } elseif (strpos($timeSlot, 'AM') === false && strpos($timeSlot, 'PM') === false) {
                                            // Convert 24-hour format to 12-hour format if needed
                                            $timeParts = explode(':', $timeSlot);
                                            $hour = (int)$timeParts[0];
                                            $min = isset($timeParts[1]) ? (int)$timeParts[1] : 0;
                                            
                                            $suffix = ($hour >= 12) ? 'PM' : 'AM';
                                            $hour = ($hour > 12) ? $hour - 12 : $hour;
                                            $hour = ($hour == 0) ? 12 : $hour; // Handle midnight
                                            
                                            $timeSlot = sprintf('%02d:%02d %s', $hour, $min, $suffix);
                                        }
                                        
                                        // Determine service type class for styling
                                        $serviceTypeClass = '';
                                        if (strtolower($serviceType) == 'hotel') {
                                            $serviceTypeClass = 'service-type-hotel';
                                        } elseif (strtolower($serviceType) == 'guide') {
                                            $serviceTypeClass = 'service-type-guide';
                                        } elseif (strpos(strtolower($serviceType), 'transfer') !== false) {
                                            $serviceTypeClass = 'service-type-transfer';
                                        } elseif (strpos(strtolower($serviceType), 'entry port') !== false) {
                                            $serviceTypeClass = 'service-type-entry';
                                        } elseif (strpos(strtolower($serviceType), 'exit port') !== false) {
                                            $serviceTypeClass = 'service-type-exit';
                                        } elseif (strtolower($serviceType) == 'attraction') {
                                            $serviceTypeClass = 'service-type-attraction';
                                        } elseif (strtolower($serviceType) == 'restaurant') {
                                            $serviceTypeClass = 'service-type-restaurant';
                                        }
                                    @endphp
                                    
                                    <div class="service-item">
                                        <div class="service-type {{ $serviceTypeClass }}">{{ $serviceType }}</div>
                                        <div class="service-name">{{ $serviceName }}</div>
                                        <div class="service-date">{{ $serviceDate }}</div>
                                        <div class="service-time">{{ $timeSlot }}</div>
                                        <div class="service-pax">Pax {{ $pax }}</div>
                                    </div>
                                @endforeach
                            @else
                                <div class="no-service">
                                    No Service booked
                                </div>
                            @endif
                        </div>
                        
                        @php $dayCount++; @endphp
                    @endforeach
                @else
                    <div class="no-service">
                        No itinerary available for this tour.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Print button for mobile -->
<button id="printItineraryMobile" class="btn btn-primary print-btn d-md-none">
    <i class="fas fa-print"></i>
</button>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Print functionality
        document.getElementById('printItinerary').addEventListener('click', function() {
            preparePrint();
            setTimeout(function() {
                window.print();
            }, 300);
        });
        
        document.getElementById('printItineraryMobile').addEventListener('click', function() {
            preparePrint();
            setTimeout(function() {
                window.print();
            }, 300);
        });
        
        function preparePrint() {
            // Add any pre-print preparations here
            // For example, you could expand all collapsed items, etc.
            
            // Show any additional details for print
            document.querySelectorAll('.print-only').forEach(function(el) {
                el.style.display = 'block';
            });
        }
        
        // After print, restore the UI
        window.onafterprint = function() {
            document.querySelectorAll('.print-only').forEach(function(el) {
                el.style.display = 'none';
            });
        };
    });
</script>
@endsection 