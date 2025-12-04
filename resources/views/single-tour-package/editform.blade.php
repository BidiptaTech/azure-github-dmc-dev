@extends('layouts.layout')
<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    #toast-container { z-index: 999999 !important; }
    #toast-container.toast-top-right { top: 70px; right: 12px; }
    #toast-container .toast { 
        padding: 12px 15px !important;
        border-radius: 4px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2) !important;
        color: #fff !important;
        background-image: none !important;
        padding-left: 15px !important;
    }
    #toast-container .toast-success { background-color: #28a745 !important; background-image: none !important; }
    #toast-container .toast-error { background-color: #dc3545 !important; background-image: none !important; }
    #toast-container .toast-info { background-color: #17a2b8 !important; background-image: none !important; }
    #toast-container .toast-warning { background-color: #ffc107 !important; color: #212529 !important; background-image: none !important; }
</style>
@section('content')
    @php
        // Get current user information
        $currentUser = auth()->user();
        $currentUserId = $currentUser->userId;
        $currentUserRole = $currentUser->role_id;
        
        // Determine DMC ID based on role hierarchy (same as controller logic)
        $dmcId = null;
        $dmcUser = null;
        $isPointToPoint = false;
        
        if($currentUserRole == 11){
            $dmcId = $currentUser->userId;
        }elseif(in_array($currentUserRole, [33, 34])){
            $user = \App\Models\User::where('userId', $currentUser->userId)->first();
            $dmcId = $user->created_by;
        }elseif(in_array($currentUserRole, [37, 124])){
            $dmcIds = $currentUser->created_by;
            $user = \App\Models\User::where('userId', $dmcIds)->first();
            if($user) {
                $dmcId = $user->created_by;
            } else {
                // Fallback: if user not found, try direct created_by
                $dmcId = $currentUser->created_by;
            }
        }elseif(in_array($currentUserRole, [38, 125])){
            $dmcIds = $currentUser->created_by;
            $user = \App\Models\User::where('userId', $dmcIds)->first();
            if($user) {
                $dmcIdss = $user->created_by;
                $user = \App\Models\User::where('userId', $dmcIdss)->first();
                if($user) {
                    $dmcId = $user->created_by;
                } else {
                    // Fallback: if second user not found, use first user's created_by
                    $dmcId = $dmcIds;
                }
            } else {
                // Fallback: if first user not found, use direct created_by
                $dmcId = $currentUser->created_by;
            }
        }else{
            $dmcId = null;
        }
        
        // Get DMC user information
        if ($dmcId) {
            $dmcUser = \App\Models\User::where('userId', $dmcId)->first();
            if ($dmcUser) {
                // Check if zone_id = 0 for Point-to-Point functionality
                if (isset($dmcUser->zone_on) && $dmcUser->zone_on == 0) {
                    $isPointToPoint = true;
                }
            }
        }
        
        // Final DMC ID for the form
        $finalDmcId = $dmcId;
        
        // Determine created_by based on role hierarchy (for backward compatibility)
        $createdBy = null;
        if ($currentUserRole == 34) { // Operation Head
            $createdBy = $currentUser->created_by; // DMC
        } elseif ($currentUserRole == 124) { // OM (Operation Manager)
            $createdBy = $currentUserId; // Operation Head is the current user
        } elseif ($currentUserRole == 125) { // AOM (Assistant Operation Manager)
            $createdBy = $currentUserId; // Operation Manager is the current user
        }
    @endphp
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Google Maps API Script -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCLzISM9kkNCKKmQs7BcpSll4emFw1yicw&libraries=places"></script>
    
    <style>
        /* Google Maps Autocomplete Styling */
        .pac-container {
            z-index: 9999;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border: 1px solid #e0e0e0;
        }

        .pac-item {
            padding: 12px 16px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        .pac-item:hover {
            background-color: #f8f9fa;
        }

        .pac-item-selected {
            background-color: #e3f2fd;
        }

        .location-input {
            position: relative;
        }

        .location-icon {
            left: 15px; 
            top: 50%; 
            transform: translateY(-50%); 
            z-index: 5;
        }

        /* Alert z-index fixes */
        .alert {
            z-index: 1050 !important;
            position: relative;
        }

        .alert.fixed-alert {
            position: fixed !important;
            top: 20px !important;
            right: 20px !important;
            z-index: 9999 !important;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* Ensure x-alert component appears on top */
        .container-xxl .alert:first-child {
            z-index: 1050 !important;
            position: relative;
        }
        
        /* Spinning loader animation */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .spin {
            animation: spin 1s linear infinite;
            display: inline-block;
        }
    </style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div style="z-index: 1050; position: relative;">
            <x-alert />
        </div>
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-gradient-primary text-white">
                        <div class="d-flex align-items-center">
                            <i class="ri-map-pin-line me-3 fs-4"></i>
                            <div>
                            <h4 class="mb-1 text-white">Edit Tour Services</h4>
                            <p class="mb-0 opacity-75">Manage and add services to existing tour: {{ $tour->display_id ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tour Edit Header -->
        @if(isset($tour))
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-warning border-0" style="z-index: 1050; position: relative;">
                    <div class="d-flex align-items-center">
                        <i class="ri-edit-line me-3" style="font-size: 2rem;"></i>
                        <div>
                            <h5 class="mb-1">Edit Tour Services</h5>
                            <p class="mb-0">You edit services for tour <strong>{{ $tour->display_id ?? 'N/A' }}</strong>. Tour information can be modified here, but you can edit all services including hotels, attractions, guides, restaurants, and transport.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(!isset($customer_info))
            <!-- Customer Information Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-gradient-primary text-white">
                            <h6 class="mb-0 fw-bold">
                                <i class="ri-user-line me-2"></i>Customer Information
                            </h6>
                        </div>
                        <div class="card-body mt-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="customerFullName" name="customer_full_name" placeholder="Enter full name" value="{{ $customer_info['fullName'] ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" id="customerEmail" name="customer_email" placeholder="Enter email" value="{{ $customer_info['email'] ?? '' }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Country Code</label>
                                    <input type="text" class="form-control" id="customerCountryCode" name="customer_country_code" placeholder="e.g. +91" value="{{ $customer_info['countryCode'] ?? '' }}">
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="customerPhone" name="customer_phone" placeholder="Enter phone number" value="{{ $customer_info['phone'] ?? '' }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address Line 1</label>
                                    <input type="text" class="form-control" id="customerAddress1" name="customer_address1" placeholder="Enter address line 1" value="{{ $customer_info['address1'] ?? '' }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address Line 2</label>
                                    <input type="text" class="form-control" id="customerAddress2" name="customer_address2" placeholder="Enter address line 2" value="{{ $customer_info['address2'] ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">State</label>
                                    <input type="text" class="form-control" id="customerState" name="customer_state" placeholder="Enter state" value="{{ $customer_info['state'] ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">ZIP Code</label>
                                    <input type="text" class="form-control" id="customerZip" name="customer_zip" placeholder="Enter ZIP code" value="{{ $customer_info['zip'] ?? '' }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Special Requests</label>
                                    <textarea class="form-control" id="customerSpecialRequests" name="customer_special_requests" rows="3" placeholder="Enter any special requests or notes">{{ $customer_info['specialRequests'] ?? '' }}</textarea>
                                </div>  
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        

        <form id="singleTourPackageForm" method="POST" action="{{ route('single-tour-package.store') }}" data-update-info-url="{{ isset($tour) ? route('single-tour-package.update-info', $tour->tour_id) : '' }}">
            @csrf
            
            <!-- Main Form Card - All in One Row -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-gradient-primary text-white">
                            <h6 class="mb-0 fw-bold">
                                <i class="ri-settings-3-line me-2"></i>Tour Information
                            </h6>
                        </div>
                        <div class="card-body mt-3">
                            <div class="row g-3">
                                <!-- Tour ID -->
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">
                                        <i class="ri-hashtag me-1"></i>Tour ID
                                    </label>
                                    <input type="text" class="form-control" name="display_id" id="display_id" value="{{ $tour->display_id ?? '' }}" placeholder="Enter tour reference" disabled>
                                    <input type="hidden" id="tour_id" name="tour_id" value="{{ $tour->tour_id ?? '' }}">
                                    
                                    <!-- DMC Information -->
                                    <input type="hidden" id="dmc_id" name="dmc_id" value="{{ $finalDmcId }}">
                                    <input type="hidden" id="current_user_id" name="current_user_id" value="{{ $currentUserId }}">
                                    <input type="hidden" id="current_user_role" name="current_user_role" value="{{ $currentUserRole }}">
                                    <input type="hidden" id="created_by" name="created_by" value="{{ $createdBy }}">
                                    <input type="hidden" id="is_point_to_point" name="is_point_to_point" value="{{ $isPointToPoint ? 1 : 0 }}">
                                </div>

                                <!-- Country -->
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">
                                        <i class="ri-earth-line me-1"></i>Country
                                    </label>
                                    <select class="form-select" name="user_country" id="user_country" required disabled>
                                        <option value="">Select Country</option >
                                        @foreach($countries as $country)
                                            <option value="{{ $country->name }}" {{ ($tour->destination ?? '') == $country->name ? 'selected' : '' }}>
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Travel Dates -->
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        <i class="ri-calendar-line me-1"></i>Travel Dates
                                    </label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <input type="date" class="form-control" name="start_date" id="start_date" 
                                                value="{{ 
                                                    $tour->check_in_time 
                                                        ? (is_string($tour->check_in_time) ? date('Y-m-d', strtotime($tour->check_in_time)) : $tour->check_in_time->format('Y-m-d'))
                                                        : '' 
                                                }}"
                                                min="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="col-6">
                                            <input type="date" class="form-control" name="end_date" id="end_date" 
                                                value="{{ 
                                                    $tour->check_out_time 
                                                        ? (is_string($tour->check_out_time) ? date('Y-m-d', strtotime($tour->check_out_time)) : $tour->check_out_time->format('Y-m-d'))
                                                        : '' 
                                                }}"
                                                min="{{ $tour->check_in_time ? (is_string($tour->check_in_time) ? date('Y-m-d', strtotime($tour->check_in_time)) : $tour->check_in_time->format('Y-m-d')) : date('Y-m-d') }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Guests -->
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        <i class="ri-group-line me-1"></i>Guests
                                    </label>
                                    <div class="guest-selector">
                                        <div class="guest-display p-2 border rounded bg-light">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="guest-info">
                                                    <span id="tour_guest_summary" class="text-muted small">
                                                        @php
                                                            $adultCount = $tour->adult ?? 1;
                                                            $childCount = $tour->child ?? 0;
                                                            $infantCount = $tour->infant ?? 0;
                                                            $maleCount = isset($tour->male_count) ? $tour->male_count : $adultCount;
                                                            $femaleCount = isset($tour->female_count) ? $tour->female_count : 0;
                                                        @endphp
                                                        {{ $adultCount }} adults ({{ $maleCount }} male, {{ $femaleCount }} female), {{ $childCount }} children, {{ $infantCount }} infants
                                                    </span>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="openTourGuestSelector()">
                                                    <i class="ri-edit-line"></i> Select
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Hidden fields for form submission -->
                                    <input type="hidden" name="adults" id="adults" value="{{ $tour->adult ?? 1 }}">
                                    <input type="hidden" name="children" id="children" value="{{ $tour->child ?? 0 }}">
                                    <input type="hidden" name="infants" id="infants" value="{{ $tour->infant ?? 0 }}">
                                    <input type="hidden" name="male_count" id="male_count" value="{{ isset($tour->male_count) ? $tour->male_count : ($tour->adult ?? 1) }}">
                                    <input type="hidden" name="female_count" id="female_count" value="{{ isset($tour->female_count) ? $tour->female_count : 0 }}">
                                    <input type="hidden" name="child_ages" id="child_ages" value="{{ $tour->child_ages ?? '' }}">
                                </div>

                                <!-- Agent -->
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">
                                        <i class="ri-user-star-line me-1"></i>Agent
                                    </label>
                                    <select class="form-select" name="agent_id" id="agent_id">
                                        <option value="">Select agent</option>
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->agent_id }}" {{ ($tour->agent_id ?? null) == $agent->agent_id ? 'selected' : '' }}>
                                                {{ $agent->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end align-items-center gap-3 mt-3">
                                <div class="text-muted small" id="tour_info_feedback"></div>
                                <button type="button" class="btn btn-primary d-flex align-items-center gap-2" onclick="UpdateTourInformation(event)">
                                    <span class="spinner-border spinner-border-sm d-none" id="tour_info_spinner"></span>
                                    <span>Update Tour Information</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!-- End of main tour information form -->

            <!-- Service Action Buttons -->

            <!-- Hotel Accommodation Section -->
            @if(isset($hotelOrders) && count($hotelOrders) > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-gradient-warning text-white">
                            <h6 class="mb-0 fw-bold">
                                <i class="ri-hotel-line me-2"></i>Hotel Accommodations
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($hotelOrders as $hotelOrder)
                                @php
                                    $hotelData = $hotelOrder->processed_data;
                                    $hotelInfo = [];
                                    if (is_array($hotelData)) {
                                        if (isset($hotelData[0])) {
                                            $hotelInfo = $hotelData[0];
                                        } else {
                                            $hotelInfo = $hotelData;
                                        }
                                    }
                                    
                                    $hotelDetails = $hotelInfo['hotelDetails'] ?? [];
                                    $bookingDates = $hotelInfo['bookingDate'] ?? [];
                                    $checkIn = is_array($bookingDates) ? ($bookingDates[0] ?? '') : $bookingDates;
                                    $checkOut = is_array($bookingDates) ? ($bookingDates[1] ?? '') : '';
                                    // Calculate which days this hotel covers
                                    $hotelDays = [];
                                    if ($checkIn && $checkOut) {
                                        $startDate = \Carbon\Carbon::parse($checkIn);
                                        $endDate = \Carbon\Carbon::parse($checkOut);
                                        $tourStart = \Carbon\Carbon::parse($tour->check_in_time);
                                        
                                        while ($startDate < $endDate) {
                                            $dayNumber = $tourStart->diffInDays($startDate) + 1;
                                            $hotelDays[] = "Day " . $dayNumber;
                                            $startDate->addDay();
                                        }
                                    }
                                @endphp
                                @php
                                    $checkInValue = $checkIn ? \Carbon\Carbon::parse($checkIn)->format('Y-m-d') : '';
                                    $checkOutValue = $checkOut ? \Carbon\Carbon::parse($checkOut)->format('Y-m-d') : '';
                                    $roomsJson = isset($hotelInfo['rooms']) ? json_encode($hotelInfo['rooms'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '';
                                    
                                    // Extract room details from rooms array
                                    $rooms = $hotelInfo['rooms'] ?? [];
                                    $numberOfRooms = 0;
                                    $roomType = '';
                                    $bedType = '';
                                    $mealPlan = '';
                                    $numberOfPersons = 0;
                                    $hotelId = $hotelDetails['hotel_id'] ?? '';
                                    $totalPrice = $hotelInfo['totalPrice'] ?? $hotelInfo['price'] ?? 0;
                                    
                                    if (!empty($rooms) && is_array($rooms)) {
                                        $firstRoom = $rooms[0] ?? [];
                                        $numberOfRooms = count($rooms);
                                        $roomType = $firstRoom['room_type'] ?? '';
                                        
                                        // Get bed details from first room
                                        $beds = $firstRoom['beds'] ?? [];
                                        if (!empty($beds) && is_array($beds)) {
                                            $firstBed = $beds[0] ?? [];
                                            $bedType = $firstBed['bed_type'] ?? '';
                                            $numberOfPersons = $firstBed['head_count'] ?? $firstBed['max_occupancy'] ?? 0;
                                            
                                            // Get meal plan
                                            $mealTypes = $firstBed['mealTypes'] ?? [];
                                            if (!empty($mealTypes) && is_array($mealTypes)) {
                                                $mealPlan = $mealTypes[0] ?? '';
                                            } elseif (isset($firstBed['selectedMeals']) && is_array($firstBed['selectedMeals'])) {
                                                $firstMeal = reset($firstBed['selectedMeals']);
                                                $mealPlan = $firstMeal['type'] ?? '';
                                            }
                                        }
                                    }
                                @endphp
                                <div class="col-12 mb-4">
                                    <div class="border border-warning rounded-3 p-4 shadow-sm hotel-edit-form" data-update-url="{{ route('edit-tour.update-hotel', $hotelOrder->booking_id) }}">
                                        @csrf
                                        <input type="hidden" name="type" value="hotel">
                                        <input type="hidden" name="hotel_id" id="hotel_id_{{ $hotelOrder->booking_id }}" value="{{ $hotelId }}">
                                        <input type="hidden" name="original_rooms_json" id="original_rooms_json_{{ $hotelOrder->booking_id }}" value="{{ $roomsJson }}">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h6 class="card-title mb-1 fw-bold text-warning"><i class="ri-hotel-line me-2"></i>Hotel Booking #{{ $loop->iteration }}</h6>
                                                <small class="text-muted">Booking ID: {{ $hotelOrder->booking_id }}</small>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeHotelService({{ $hotelOrder->booking_id }})">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Location</label>
                                                <input type="text" class="form-control" value="{{ $hotelDetails['location'] ?? '' }}" placeholder="City / Area" disabled>
                                                <input type="hidden" name="hotel_location" value="{{ $hotelDetails['location'] ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Hotel Name</label>
                                                @php
                                                    $currentLocation = $hotelDetails['location'] ?? '';
                                                    $currentHotelName = $hotelDetails['hotel_name'] ?? '';
                                                    $currentHotelId = $hotelDetails['hotel_id'] ?? '';
                                                    
                                                    // Filter hotels by location (city)
                                                    $locationFilteredHotels = $hotels->filter(function($hotel) use ($currentLocation) {
                                                        if (empty($currentLocation)) {
                                                            return false;
                                                        }
                                                        $hotelCity = strtolower(trim($hotel->city ?? ''));
                                                        $hotelAddress = strtolower(trim($hotel->address ?? ''));
                                                        $locationLower = strtolower(trim($currentLocation));
                                                        
                                                        return $hotelCity === $locationLower || $hotelAddress === $locationLower;
                                                    });
                                                @endphp
                                                <select class="form-select" name="hotel_name" id="hotel_name_{{ $hotelOrder->booking_id }}" onchange="updateHotelId_{{ $hotelOrder->booking_id }}(this.value)" required>
                                                    <option value="">Select Hotel</option>
                                                    @foreach($locationFilteredHotels as $hotel)
                                                        <option value="{{ $hotel->name }}" 
                                                            data-hotel-id="{{ $hotel->hotel_unique_id }}"
                                                            {{ ($hotel->name == $currentHotelName || $hotel->hotel_unique_id == $currentHotelId) ? 'selected' : '' }}>
                                                            {{ $hotel->name }}
                                                        </option>
                                                    @endforeach
                                                    @if($currentHotelName && !$locationFilteredHotels->contains('name', $currentHotelName))
                                                        <option value="{{ $currentHotelName }}" selected>{{ $currentHotelName }}</option>
                                                    @endif
                                                </select>
                                                <script>
                                                    // Store room data for this hotel booking
                                                    window.roomData_{{ $hotelOrder->booking_id }} = null;
                                                    
                                                    function updateHotelId_{{ $hotelOrder->booking_id }}(selectedValue) {
                                                        const select = document.getElementById('hotel_name_{{ $hotelOrder->booking_id }}');
                                                        const selectedOption = select.options[select.selectedIndex];
                                                        const hotelIdInput = document.getElementById('hotel_id_{{ $hotelOrder->booking_id }}');
                                                        
                                                        if (selectedOption && selectedOption.dataset.hotelId) {
                                                            hotelIdInput.value = selectedOption.dataset.hotelId;
                                                            // Load rooms for the selected hotel
                                                            loadRoomsForHotel_{{ $hotelOrder->booking_id }}(selectedOption.dataset.hotelId);
                                                        } else {
                                                            hotelIdInput.value = '';
                                                            // Reset room and bed options
                                                            resetHotelFormFields_{{ $hotelOrder->booking_id }}();
                                                        }
                                                    }
                                                    
                                                    function loadRoomsForHotel_{{ $hotelOrder->booking_id }}(hotelId) {
                                                        const roomTypeSelect = document.getElementById('room_type_{{ $hotelOrder->booking_id }}');
                                                        const bedTypeSelect = document.getElementById('bed_type_{{ $hotelOrder->booking_id }}');
                                                        const mealPlanSelect = document.getElementById('meal_plan_{{ $hotelOrder->booking_id }}');
                                                        
                                                        if (!hotelId) {
                                                            resetHotelFormFields_{{ $hotelOrder->booking_id }}();
                                                            return;
                                                        }
                                                        
                                                        console.log('Loading rooms for hotel:', hotelId, 'Booking ID:', {{ $hotelOrder->booking_id }});
                                                        
                                                        // Show loading state
                                                        roomTypeSelect.innerHTML = '<option value="">Loading rooms...</option>';
                                                        roomTypeSelect.disabled = true;
                                                        bedTypeSelect.innerHTML = '<option value="">Select room type first</option>';
                                                        bedTypeSelect.disabled = true;
                                                        mealPlanSelect.innerHTML = '<option value="">Select room type first</option>';
                                                        mealPlanSelect.disabled = true;
                                                        
                                                        // Get DMC ID
                                                        const dmcIdInput = document.getElementById('dmc_id');
                                                        const currentDmcId = dmcIdInput ? dmcIdInput.value : '';
                                                        
                                                        // Fetch rooms for the selected hotel
                                                        fetch(`{{ route('fetch-rooms-by-hotel') }}?hotel_id=${encodeURIComponent(hotelId)}&dmc_id=${currentDmcId}`)
                                                            .then(response => {
                                                                if (!response.ok) {
                                                                    throw new Error('Network response was not ok');
                                                                }
                                                                return response.json();
                                                            })
                                                            .then(response => {
                                                                console.log('Rooms API Response for booking {{ $hotelOrder->booking_id }}:', response);
                                                                
                                                                roomTypeSelect.innerHTML = '<option value="">Select room type</option>';
                                                                
                                                                if (response.success && response.rooms && response.rooms.length > 0) {
                                                                    // Filter rooms by DMC ID
                                                                    let dmcFilteredRooms = response.rooms.filter(room => {
                                                                        const roomDmcId = room.created_by;
                                                                        return roomDmcId && roomDmcId == currentDmcId;
                                                                    });
                                                                    
                                                                    if (dmcFilteredRooms.length === 0) {
                                                                        roomTypeSelect.innerHTML = '<option value="">No rooms available for your DMC</option>';
                                                                        return;
                                                                    }
                                                                    
                                                                    // Store room data for this booking
                                                                    window.roomData_{{ $hotelOrder->booking_id }} = dmcFilteredRooms;
                                                                    
                                                                    // Extract unique room types
                                                                    const roomTypes = [...new Set(dmcFilteredRooms.map(room => room.room_type).filter(Boolean))];
                                                                    
                                                                    // Get existing room type value to preserve selection
                                                                    const existingRoomType = roomTypeSelect.value || '{{ $roomType ?? "" }}';
                                                                    
                                                                    // Populate room types with pricing information
                                                                    roomTypes.forEach(roomType => {
                                                                        const sampleRoom = dmcFilteredRooms.find(room => room.room_type === roomType);
                                                                        
                                                                        if (sampleRoom) {
                                                                            // Get number of persons for pricing
                                                                            const numberOfPersonsInput = document.getElementById('number_of_persons_{{ $hotelOrder->booking_id }}');
                                                                            const numberOfPersons = numberOfPersonsInput ? parseInt(numberOfPersonsInput.value) || 1 : 1;
                                                                            const isSingleOccupancy = numberOfPersons <= 1;
                                                                            
                                                                            // Determine price based on occupancy
                                                                            let price = 0;
                                                                            if (isSingleOccupancy) {
                                                                                price = parseFloat(sampleRoom.weekday_price || 0);
                                                                            } else {
                                                                                price = parseFloat(sampleRoom.double_weekday_price || sampleRoom.weekday_price || 0);
                                                                            }
                                                                            
                                                                            const option = document.createElement('option');
                                                                            option.value = roomType;
                                                                            option.textContent = price > 0 ? `${roomType} - $${price.toFixed(2)}` : roomType;
                                                                            option.dataset.roomId = sampleRoom.room_id;
                                                                            option.dataset.weekdayPrice = sampleRoom.weekday_price || 0;
                                                                            option.dataset.doubleWeekdayPrice = sampleRoom.double_weekday_price || 0;
                                                                            // Preserve existing selection if it matches
                                                                            if (roomType === existingRoomType) {
                                                                                option.selected = true;
                                                                                // Trigger bed loading if room type was already selected
                                                                                setTimeout(() => {
                                                                                    loadBedTypesForRoom_{{ $hotelOrder->booking_id }}(roomType);
                                                                                    updateHotelPrice_{{ $hotelOrder->booking_id }}();
                                                                                }, 100);
                                                                            }
                                                                            roomTypeSelect.appendChild(option);
                                                                        }
                                                                    });
                                                                    
                                                                    roomTypeSelect.disabled = false;
                                                                    console.log(`Loaded ${roomTypes.length} room types for hotel ${hotelId}`);
                                                                } else {
                                                                    roomTypeSelect.innerHTML = '<option value="">No rooms available</option>';
                                                                }
                                                            })
                                                            .catch(error => {
                                                                console.error('Error loading rooms:', error);
                                                                roomTypeSelect.innerHTML = '<option value="">Error loading rooms</option>';
                                                            });
                                                    }
                                                    
                                                    function loadBedTypesForRoom_{{ $hotelOrder->booking_id }}(roomType) {
                                                        const bedTypeSelect = document.getElementById('bed_type_{{ $hotelOrder->booking_id }}');
                                                        const mealPlanSelect = document.getElementById('meal_plan_{{ $hotelOrder->booking_id }}');
                                                        
                                                        if (!roomType) {
                                                            bedTypeSelect.disabled = true;
                                                            bedTypeSelect.innerHTML = '<option value="">Select room type first</option>';
                                                            mealPlanSelect.disabled = true;
                                                            mealPlanSelect.innerHTML = '<option value="">Select room type first</option>';
                                                            return;
                                                        }
                                                        
                                                        console.log('Loading beds for room type:', roomType, 'Booking ID:', {{ $hotelOrder->booking_id }});
                                                        
                                                        // Show loading state
                                                        bedTypeSelect.innerHTML = '<option value="">Loading bed types...</option>';
                                                        bedTypeSelect.disabled = true;
                                                        mealPlanSelect.innerHTML = '<option value="">Select bed type first</option>';
                                                        mealPlanSelect.disabled = true;
                                                        
                                                        // Find rooms of the selected type
                                                        const roomData = window.roomData_{{ $hotelOrder->booking_id }};
                                                        if (!roomData) {
                                                            bedTypeSelect.innerHTML = '<option value="">No room data available</option>';
                                                            return;
                                                        }
                                                        
                                                        const selectedRooms = roomData.filter(room => room.room_type === roomType);
                                                        
                                                        if (selectedRooms.length === 0) {
                                                            bedTypeSelect.innerHTML = '<option value="">No rooms of this type</option>';
                                                            return;
                                                        }
                                                        
                                                        // Get the first room ID to fetch beds
                                                        const firstRoom = selectedRooms[0];
                                                        const roomId = firstRoom.room_id;
                                                        
                                                        // Fetch beds from API
                                                        fetch(`{{ route('fetch-beds-by-room') }}?room_id=${roomId}`)
                                                            .then(response => {
                                                                if (!response.ok) {
                                                                    throw new Error('Network response was not ok');
                                                                }
                                                                return response.json();
                                                            })
                                                            .then(data => {
                                                                console.log('Beds API Response for booking {{ $hotelOrder->booking_id }}:', data);
                                                                
                                                                bedTypeSelect.innerHTML = '<option value="">Select bed type</option>';
                                                                
                                                                if (data.success && data.beds && data.beds.length > 0) {
                                                                    data.beds.forEach(bed => {
                                                                        let bedTypeText = bed.room_type || bed.bed_type || 'Standard Bed';
                                                                        
                                                                        if (bed.max_occupancy) {
                                                                            bedTypeText += ` - Max ${bed.max_occupancy} guests`;
                                                                        }
                                                                        
                                                                        if (bed.adult_count && bed.child_count) {
                                                                            bedTypeText += ` (${bed.adult_count}A+${bed.child_count}C)`;
                                                                        }
                                                                        
                                                                        if (bed.extra_bed) {
                                                                            bedTypeText += ` + Extra Bed`;
                                                                            if (bed.extra_bed_price) {
                                                                                bedTypeText += ` ($${bed.extra_bed_price})`;
                                                                            }
                                                                        }
                                                                        
                                                                        if (bed.baby_cot) {
                                                                            bedTypeText += ` + Baby Cot`;
                                                                            if (bed.baby_cot_price) {
                                                                                bedTypeText += ` ($${bed.baby_cot_price})`;
                                                                            }
                                                                        }
                                                                        
                                                                        const option = document.createElement('option');
                                                                        option.value = bed.bed_type || bed.room_type || bed.bed_id;
                                                                        option.textContent = bedTypeText;
                                                                        option.setAttribute('data-bed', JSON.stringify(bed));
                                                                        option.setAttribute('data-bed-id', bed.bed_id);
                                                                        option.setAttribute('data-room-id', bed.room_id);
                                                                        
                                                                        // Preserve existing bed selection if it matches
                                                                        const existingBedType = '{{ $bedType ?? "" }}';
                                                                        if (existingBedType) {
                                                                            // More flexible matching: check bed_type, room_type, or if the value/text contains the existing bed type
                                                                            const bedTypeMatch = bed.bed_type === existingBedType || 
                                                                                                bed.room_type === existingBedType ||
                                                                                                (bed.bed_type && bed.bed_type.toLowerCase().includes(existingBedType.toLowerCase())) ||
                                                                                                (bed.room_type && bed.room_type.toLowerCase().includes(existingBedType.toLowerCase())) ||
                                                                                                (option.value && option.value.toLowerCase().includes(existingBedType.toLowerCase()));
                                                                            if (bedTypeMatch) {
                                                                                option.selected = true;
                                                                            }
                                                                        }
                                                                        
                                                                        bedTypeSelect.appendChild(option);
                                                                    });
                                                                    
                                                                    bedTypeSelect.disabled = false;
                                                                    console.log(`Loaded ${data.beds.length} bed types for room type ${roomType}`);
                                                                    
                                                                    // If a bed type was selected, trigger meal plan loading
                                                                    const selectedBedOption = bedTypeSelect.options[bedTypeSelect.selectedIndex];
                                                                    if (selectedBedOption && selectedBedOption.value && selectedBedOption.dataset.roomId) {
                                                                        const roomId = selectedBedOption.dataset.roomId;
                                                                        const roomData = window.roomData_{{ $hotelOrder->booking_id }} || [];
                                                                        const room = roomData.find(r => r.room_id == roomId);
                                                                        if (room) {
                                                                            setTimeout(() => {
                                                                                loadMealPlansForBed_{{ $hotelOrder->booking_id }}(room);
                                                                            }, 100);
                                                                        } else {
                                                                            // Try to get room from room type
                                                                            const roomTypeSelect = document.getElementById('room_type_{{ $hotelOrder->booking_id }}');
                                                                            if (roomTypeSelect && roomTypeSelect.value) {
                                                                                const selectedRooms = roomData.filter(r => r.room_type === roomTypeSelect.value);
                                                                                if (selectedRooms.length > 0) {
                                                                                    setTimeout(() => {
                                                                                        loadMealPlansForBed_{{ $hotelOrder->booking_id }}(selectedRooms[0]);
                                                                                    }, 100);
                                                                                }
                                                                            }
                                                                        }
                                                                    } else {
                                                                        // Load meal plans based on room data
                                                                        loadMealPlansForBed_{{ $hotelOrder->booking_id }}(selectedRooms[0]);
                                                                    }
                                                                } else {
                                                                    bedTypeSelect.innerHTML = '<option value="">No bed types available</option>';
                                                                }
                                                            })
                                                            .catch(error => {
                                                                console.error('Error fetching beds:', error);
                                                                bedTypeSelect.innerHTML = '<option value="">Error loading bed types</option>';
                                                            });
                                                    }
                                                    
                                                    function loadMealPlansForBed_{{ $hotelOrder->booking_id }}(bedDataOrRoom) {
                                                        const mealPlanSelect = document.getElementById('meal_plan_{{ $hotelOrder->booking_id }}');
                                                        const roomData = window.roomData_{{ $hotelOrder->booking_id }} || [];
                                                        const bedTypeSelect = document.getElementById('bed_type_{{ $hotelOrder->booking_id }}');
                                                        
                                                        console.log('Loading meal plans for booking {{ $hotelOrder->booking_id }}', bedDataOrRoom);
                                                        
                                                        // Get room from selected bed or passed room data
                                                        let room = null;
                                                        let maxOccupancy = null;
                                                        
                                                        // First, try to get room from selected bed
                                                        if (bedTypeSelect && bedTypeSelect.value) {
                                                            const selectedBedOption = bedTypeSelect.options[bedTypeSelect.selectedIndex];
                                                            if (selectedBedOption && selectedBedOption.dataset.roomId) {
                                                                const roomId = selectedBedOption.dataset.roomId;
                                                                room = roomData.find(r => r.room_id == roomId);
                                                                
                                                                // Get max occupancy from bed data
                                                                const bedData = JSON.parse(selectedBedOption.dataset.bed || '{}');
                                                                maxOccupancy = bedData.max_occupancy || null;
                                                            }
                                                        }
                                                        
                                                        // If no room from bed, try from passed parameter
                                                        if (!room && bedDataOrRoom) {
                                                            if (bedDataOrRoom.room_id) {
                                                                room = roomData.find(r => r.room_id == bedDataOrRoom.room_id);
                                                            } else if (bedDataOrRoom.room_type) {
                                                                room = bedDataOrRoom;
                                                            }
                                                        }
                                                        
                                                        // If still no room, try to get from selected room type
                                                        if (!room && roomData.length > 0) {
                                                            const roomTypeSelect = document.getElementById('room_type_{{ $hotelOrder->booking_id }}');
                                                            if (roomTypeSelect && roomTypeSelect.value) {
                                                                const selectedRooms = roomData.filter(r => r.room_type === roomTypeSelect.value);
                                                                if (selectedRooms.length > 0) {
                                                                    room = selectedRooms[0];
                                                                }
                                                            }
                                                        }
                                                        
                                                        if (!room) {
                                                            console.log('No room found for meal plans');
                                                            mealPlanSelect.innerHTML = '<option value="">Select room type first</option>';
                                                            mealPlanSelect.disabled = true;
                                                            return;
                                                        }
                                                        
                                                        console.log('Room found for meal plans:', room);
                                                        console.log('Room meal data:', {
                                                            breakfast: room.breakfast,
                                                            lunch: room.lunch,
                                                            dinner: room.dinner,
                                                            breakfast_included: room.breakfast_included,
                                                            lunch_included: room.lunch_included,
                                                            dinner_included: room.dinner_included
                                                        });
                                                        
                                                        mealPlanSelect.innerHTML = '<option value="">Select meal plan</option>';
                                                        
                                                        // Check meal availability - check actual room fields (0/1 or boolean)
                                                        const hasBreakfast = room.breakfast == 1 || room.breakfast === true || room.breakfast === '1' || 
                                                                             room.breakfast_included == 1 || room.breakfast_included === true;
                                                        const hasLunch = room.lunch == 1 || room.lunch === true || room.lunch === '1' || 
                                                                         room.lunch_included == 1 || room.lunch_included === true;
                                                        const hasDinner = room.dinner == 1 || room.dinner === true || room.dinner === '1' || 
                                                                         room.dinner_included == 1 || room.dinner_included === true;
                                                        
                                                        console.log('Meal availability check result:', { 
                                                            hasBreakfast, 
                                                            hasLunch, 
                                                            hasDinner,
                                                            maxOccupancy
                                                        });
                                                        
                                                        // Get pax info - try from bed first, then room
                                                        const paxInfo = maxOccupancy ? ` (Max ${maxOccupancy} pax)` : '';
                                                        
                                                        const mealPlans = [];
                                                        
                                                        // Always add Room Only
                                                        mealPlans.push({ value: 'room_only', text: `Room Only${paxInfo}` });
                                                        
                                                        // Add meal plans only if available in room data
                                                        if (hasBreakfast) {
                                                            mealPlans.push({ value: 'bed_&_breakfast', text: `Bed & Breakfast${paxInfo}` });
                                                            mealPlans.push({ value: 'breakfast_only', text: `Breakfast Only${paxInfo}` });
                                                        }
                                                        if (hasLunch) {
                                                            mealPlans.push({ value: 'lunch_only', text: `Lunch Only${paxInfo}` });
                                                        }
                                                        if (hasDinner) {
                                                            mealPlans.push({ value: 'dinner_only', text: `Dinner Only${paxInfo}` });
                                                        }
                                                        if (hasBreakfast && hasLunch) {
                                                            mealPlans.push({ value: 'half_board_breakfast_lunch', text: `Half Board (Breakfast + Lunch)${paxInfo}` });
                                                        }
                                                        if (hasBreakfast && hasDinner) {
                                                            mealPlans.push({ value: 'half_board_breakfast_dinner', text: `Half Board (Breakfast + Dinner)${paxInfo}` });
                                                        }
                                                        if (hasLunch && hasDinner) {
                                                            mealPlans.push({ value: 'half_board_lunch_dinner', text: `Half Board (Lunch + Dinner)${paxInfo}` });
                                                        }
                                                        if (hasBreakfast && hasLunch && hasDinner) {
                                                            mealPlans.push({ value: 'full_board_all_meals', text: `Full Board (All Meals)${paxInfo}` });
                                                            mealPlans.push({ value: 'all_inclusive', text: `All Inclusive${paxInfo}` });
                                                        }
                                                        
                                                        // Populate meal plans dynamically
                                                        if (mealPlans.length > 0) {
                                                            const existingMealPlan = '{{ $mealPlan ?? "" }}';
                                                            let mealPlanSelected = false;
                                                            
                                                            mealPlans.forEach(plan => {
                                                                const option = document.createElement('option');
                                                                option.value = plan.value;
                                                                option.textContent = plan.text;
                                                                
                                                                // Preserve existing meal plan selection if it matches
                                                                if (existingMealPlan && !mealPlanSelected) {
                                                                    // Normalize both values for comparison
                                                                    const existingValue = existingMealPlan.toLowerCase().trim();
                                                                    const planValue = plan.value.toLowerCase().trim();
                                                                    const planText = plan.text.toLowerCase().trim();
                                                                    
                                                                    // Try multiple matching strategies
                                                                    const match1 = planValue === existingValue;
                                                                    const match2 = planValue.includes(existingValue.replace(/\s+/g, '_'));
                                                                    const match3 = planText.includes(existingValue);
                                                                    const match4 = existingValue.includes(planValue);
                                                                    const match5 = existingValue.replace(/\s+/g, '_') === planValue;
                                                                    const match6 = existingValue.replace(/\s+/g, '') === planValue.replace(/_/g, '');
                                                                    
                                                                    // Also check common meal plan variations
                                                                    const mealPlanVariations = {
                                                                        'room only': ['room_only'],
                                                                        'bed & breakfast': ['bed_&_breakfast', 'bed_and_breakfast'],
                                                                        'breakfast only': ['breakfast_only'],
                                                                        'lunch only': ['lunch_only'],
                                                                        'dinner only': ['dinner_only'],
                                                                        'half board': ['half_board_breakfast_lunch', 'half_board_breakfast_dinner', 'half_board_lunch_dinner'],
                                                                        'full board': ['full_board_all_meals'],
                                                                        'all inclusive': ['all_inclusive']
                                                                    };
                                                                    
                                                                    let variationMatch = false;
                                                                    for (const [key, values] of Object.entries(mealPlanVariations)) {
                                                                        if (existingValue.includes(key) && values.includes(planValue)) {
                                                                            variationMatch = true;
                                                                            break;
                                                                        }
                                                                    }
                                                                    
                                                                    if (match1 || match2 || match3 || match4 || match5 || match6 || variationMatch) {
                                                                        option.selected = true;
                                                                        mealPlanSelected = true;
                                                                    }
                                                                }
                                                                
                                                                mealPlanSelect.appendChild(option);
                                                            });
                                                            
                                                            mealPlanSelect.disabled = false;
                                                            console.log(`Loaded ${mealPlans.length} meal plan options dynamically from room data with pax info`);
                                                            if (existingMealPlan && mealPlanSelected) {
                                                                console.log(`Meal plan "${existingMealPlan}" was automatically selected`);
                                                            }
                                                        } else {
                                                            mealPlanSelect.innerHTML = '<option value="">No meal plans available for this room</option>';
                                                            mealPlanSelect.disabled = true;
                                                        }
                                                    }
                                                    
                                                    function onBedTypeChange_{{ $hotelOrder->booking_id }}(bedTypeValue) {
                                                        const bedTypeSelect = document.getElementById('bed_type_{{ $hotelOrder->booking_id }}');
                                                        const selectedOption = bedTypeSelect.options[bedTypeSelect.selectedIndex];
                                                        
                                                        // Update pax info when bed is selected
                                                        if (selectedOption) {
                                                            const bedData = JSON.parse(selectedOption.dataset.bed || '{}');
                                                            const maxOccupancy = bedData.max_occupancy;
                                                            const paxInfoEl = document.getElementById('pax_info_{{ $hotelOrder->booking_id }}');
                                                            
                                                            if (maxOccupancy && paxInfoEl) {
                                                                paxInfoEl.textContent = `Max occupancy: ${maxOccupancy} pax`;
                                                                paxInfoEl.style.color = '#198754';
                                                            }
                                                        }
                                                        
                                                        // Update price when bed type changes
                                                        updateHotelPrice_{{ $hotelOrder->booking_id }}(true);
                                                        
                                                        // Load meal plans
                                                        if (selectedOption && selectedOption.dataset.roomId) {
                                                            const roomId = selectedOption.dataset.roomId;
                                                            const roomData = window.roomData_{{ $hotelOrder->booking_id }} || [];
                                                            const room = roomData.find(r => r.room_id == roomId);
                                                            
                                                            if (room) {
                                                                loadMealPlansForBed_{{ $hotelOrder->booking_id }}(room);
                                                            } else {
                                                                // Try to get room from room type
                                                                const roomTypeSelect = document.getElementById('room_type_{{ $hotelOrder->booking_id }}');
                                                                if (roomTypeSelect && roomTypeSelect.value) {
                                                                    const selectedRooms = roomData.filter(r => r.room_type === roomTypeSelect.value);
                                                                    if (selectedRooms.length > 0) {
                                                                        loadMealPlansForBed_{{ $hotelOrder->booking_id }}(selectedRooms[0]);
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            // Fallback: try to get room from room type
                                                            const roomTypeSelect = document.getElementById('room_type_{{ $hotelOrder->booking_id }}');
                                                            const roomData = window.roomData_{{ $hotelOrder->booking_id }} || [];
                                                            if (roomTypeSelect && roomTypeSelect.value && roomData.length > 0) {
                                                                const selectedRooms = roomData.filter(r => r.room_type === roomTypeSelect.value);
                                                                if (selectedRooms.length > 0) {
                                                                    loadMealPlansForBed_{{ $hotelOrder->booking_id }}(selectedRooms[0]);
                                                                }
                                                            }
                                                        }
                                                    }
                                                    
                                                    function updatePaxInfo_{{ $hotelOrder->booking_id }}(paxValue) {
                                                        const bedTypeSelect = document.getElementById('bed_type_{{ $hotelOrder->booking_id }}');
                                                        const paxInfoEl = document.getElementById('pax_info_{{ $hotelOrder->booking_id }}');
                                                        
                                                        if (bedTypeSelect && bedTypeSelect.value && paxInfoEl) {
                                                            const selectedOption = bedTypeSelect.options[bedTypeSelect.selectedIndex];
                                                            if (selectedOption) {
                                                                const bedData = JSON.parse(selectedOption.dataset.bed || '{}');
                                                                const maxOccupancy = bedData.max_occupancy;
                                                                
                                                                if (maxOccupancy) {
                                                                    if (parseInt(paxValue) > parseInt(maxOccupancy)) {
                                                                        paxInfoEl.textContent = `Warning: Exceeds max occupancy of ${maxOccupancy} pax`;
                                                                        paxInfoEl.style.color = '#dc3545';
                                                                    } else {
                                                                        paxInfoEl.textContent = `Max occupancy: ${maxOccupancy} pax`;
                                                                        paxInfoEl.style.color = '#198754';
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                    
                                                    function resetHotelFormFields_{{ $hotelOrder->booking_id }}() {
                                                        const roomTypeSelect = document.getElementById('room_type_{{ $hotelOrder->booking_id }}');
                                                        const bedTypeSelect = document.getElementById('bed_type_{{ $hotelOrder->booking_id }}');
                                                        const mealPlanSelect = document.getElementById('meal_plan_{{ $hotelOrder->booking_id }}');
                                                        
                                                        if (roomTypeSelect) {
                                                            roomTypeSelect.innerHTML = '<option value="">Select hotel first</option>';
                                                            roomTypeSelect.disabled = true;
                                                        }
                                                        if (bedTypeSelect) {
                                                            bedTypeSelect.innerHTML = '<option value="">Select room type first</option>';
                                                            bedTypeSelect.disabled = true;
                                                        }
                                                        if (mealPlanSelect) {
                                                            mealPlanSelect.innerHTML = '<option value="">Select bed type first</option>';
                                                            mealPlanSelect.disabled = true;
                                                        }
                                                        
                                                        window.roomData_{{ $hotelOrder->booking_id }} = null;
                                                    }
                                                    
                                                    // Function to update hotel price based on room type and number of rooms
                                                    function updateHotelPrice_{{ $hotelOrder->booking_id }}(forceUpdate = false) {
                                                        const roomTypeSelect = document.getElementById('room_type_{{ $hotelOrder->booking_id }}');
                                                        const numberOfRoomsInput = document.getElementById('number_of_rooms_{{ $hotelOrder->booking_id }}');
                                                        const priceInput = document.getElementById('total_price_{{ $hotelOrder->booking_id }}');
                                                        const numberOfPersonsInput = document.getElementById('number_of_persons_{{ $hotelOrder->booking_id }}');
                                                        
                                                        if (!roomTypeSelect || !numberOfRoomsInput || !priceInput) {
                                                            return;
                                                        }
                                                        
                                                        // Check if price was manually edited - if so, don't auto-update (unless forced)
                                                        if (priceInput.dataset.manualEdit === 'true' && !forceUpdate) {
                                                            return;
                                                        }
                                                        
                                                        // Preserve existing price value on initial load if it's already set and valid (from database)
                                                        // Only auto-calculate if the current value is 0 or empty, or if forceUpdate is true
                                                        const currentPrice = parseFloat(priceInput.value) || 0;
                                                        if (!forceUpdate && currentPrice > 0 && priceInput.dataset.preservedFromDb !== 'true') {
                                                            // Mark as preserved from database to prevent overwriting on initial load
                                                            priceInput.dataset.preservedFromDb = 'true';
                                                            return;
                                                        }
                                                        
                                                        // If forceUpdate is true (user made a change), clear the preserved flag to allow recalculation
                                                        if (forceUpdate) {
                                                            priceInput.dataset.preservedFromDb = 'false';
                                                        }
                                                        
                                                        const selectedRoomType = roomTypeSelect.value;
                                                        const numberOfRooms = parseInt(numberOfRoomsInput.value) || 1;
                                                        const numberOfPersons = parseInt(numberOfPersonsInput.value) || 1;
                                                        
                                                        // Get room data
                                                        const roomData = window.roomData_{{ $hotelOrder->booking_id }};
                                                        if (!roomData || !selectedRoomType) {
                                                            return;
                                                        }
                                                        
                                                        // Find the selected room type
                                                        const selectedRoom = roomData.find(room => room.room_type === selectedRoomType);
                                                        if (!selectedRoom) {
                                                            return;
                                                        }
                                                        
                                                        // Calculate price based on occupancy (single or double)
                                                        const isSingleOccupancy = numberOfPersons <= 1;
                                                        let pricePerNight = 0;
                                                        
                                                        if (isSingleOccupancy) {
                                                            pricePerNight = parseFloat(selectedRoom.weekday_price || 0);
                                                        } else {
                                                            pricePerNight = parseFloat(selectedRoom.double_weekday_price || selectedRoom.weekday_price || 0);
                                                        }
                                                        
                                                        // Calculate number of nights - use the specific form context
                                                        const formDiv = document.querySelector('.hotel-edit-form[data-update-url*="{{ $hotelOrder->booking_id }}"]');
                                                        let numberOfNights = 1;
                                                        if (formDiv) {
                                                            const checkInInput = formDiv.querySelector('input[name="check_in_date"]');
                                                            const checkOutInput = formDiv.querySelector('input[name="check_out_date"]');
                                                            
                                                            if (checkInInput && checkOutInput && checkInInput.value && checkOutInput.value) {
                                                                const checkIn = new Date(checkInInput.value);
                                                                const checkOut = new Date(checkOutInput.value);
                                                                numberOfNights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
                                                                if (numberOfNights <= 0) numberOfNights = 1;
                                                            }
                                                        }
                                                        
                                                        // Calculate total price: price per night * number of nights * number of rooms
                                                        const totalPrice = pricePerNight * numberOfNights * numberOfRooms;
                                                        
                                                        // Update price input if calculated price is valid
                                                        if (totalPrice > 0) {
                                                            priceInput.value = totalPrice.toFixed(2);
                                                        } else if (currentPrice === 0) {
                                                            // Only clear if current value is 0 (don't overwrite saved values)
                                                            priceInput.value = '0.00';
                                                        }
                                                    }
                                                    
                                                    // Track manual price edits - attach event listener immediately
                                                    (function() {
                                                        const priceInput = document.getElementById('total_price_{{ $hotelOrder->booking_id }}');
                                                        if (priceInput) {
                                                            // Mark as manually edited when user types
                                                            priceInput.addEventListener('input', function() {
                                                                this.dataset.manualEdit = 'true';
                                                            });
                                                            
                                                            // Also mark on change event (for cases where input event doesn't fire)
                                                            priceInput.addEventListener('change', function() {
                                                                this.dataset.manualEdit = 'true';
                                                            });
                                                        }
                                                    })();
                                                    
                                                    // Initialize hotel_id and load rooms on page load if hotel is already selected
                                                    document.addEventListener('DOMContentLoaded', function() {
                                                        const hotelSelect = document.getElementById('hotel_name_{{ $hotelOrder->booking_id }}');
                                                        if (hotelSelect && hotelSelect.value) {
                                                            updateHotelId_{{ $hotelOrder->booking_id }}(hotelSelect.value);
                                                        }
                                                    });
                                                </script>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Check-in Date</label>
                                                <input type="date" class="form-control" name="check_in_date" value="{{ $checkInValue }}" required onchange="updateHotelPrice_{{ $hotelOrder->booking_id }}(true);">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Check-out Date</label>
                                                <input type="date" class="form-control" name="check_out_date" value="{{ $checkOutValue }}" required onchange="updateHotelPrice_{{ $hotelOrder->booking_id }}(true);">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Number of Rooms</label>
                                                <input type="number" class="form-control" name="number_of_rooms" id="number_of_rooms_{{ $hotelOrder->booking_id }}" value="{{ $numberOfRooms }}" min="1" placeholder="e.g. 1" onchange="updateHotelPrice_{{ $hotelOrder->booking_id }}(true);">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Room Type</label>
                                                <select class="form-select" name="room_type" id="room_type_{{ $hotelOrder->booking_id }}" onchange="loadBedTypesForRoom_{{ $hotelOrder->booking_id }}(this.value); updateHotelPrice_{{ $hotelOrder->booking_id }}(true);">
                                                    <option value="">Select Room Type</option>
                                                    @if($roomType)
                                                        <option value="{{ $roomType }}" selected>{{ $roomType }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Bed Type</label>
                                                <select class="form-select" name="bed_type" id="bed_type_{{ $hotelOrder->booking_id }}" onchange="onBedTypeChange_{{ $hotelOrder->booking_id }}(this.value)">
                                                    <option value="">Select Bed Type</option>
                                                    @if($bedType)
                                                        <option value="{{ $bedType }}" selected>{{ $bedType }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Meal Plan</label>
                                                <select class="form-select" name="meal_plan" id="meal_plan_{{ $hotelOrder->booking_id }}">
                                                    <option value="">Select Meal Plan</option>
                                                    @if($mealPlan)
                                                        <option value="{{ $mealPlan }}" selected>{{ $mealPlan }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Number of Persons (Pax)</label>
                                                <input type="number" class="form-control" name="number_of_persons" id="number_of_persons_{{ $hotelOrder->booking_id }}" value="{{ $numberOfPersons }}" min="1" placeholder="e.g. 2" onchange="updatePaxInfo_{{ $hotelOrder->booking_id }}(this.value); updateHotelPrice_{{ $hotelOrder->booking_id }}(true);">
                                                <small class="text-muted" id="pax_info_{{ $hotelOrder->booking_id }}"></small>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="ri-money-dollar-circle-line me-1 text-success"></i>Total Price
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" class="form-control" name="total_price" id="total_price_{{ $hotelOrder->booking_id }}" step="0.01" min="0" value="{{ number_format((float)$totalPrice, 2, '.', '') }}" placeholder="0.00" data-manual-edit="false">
                                                </div>
                                                <small class="text-muted">Price per room type and number of rooms</small>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end align-items-center gap-3 mt-4">
                                            <div class="text-muted small" id="hotel_feedback_{{ $hotelOrder->booking_id }}"></div>
                                            <button type="button" class="btn btn-sm btn-primary d-flex align-items-center gap-2" onclick="updateExistingHotel(event, {{ $hotelOrder->booking_id }})">
                                                <span class="spinner-border spinner-border-sm d-none" id="hotel_spinner_{{ $hotelOrder->booking_id }}"></span>
                                                <span> Save Changes </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            @endif
            <div class="card-footer bg-light">
                <div class="text-center py-3">
                    <button type="button" class="btn btn-gradient-primary btn-lg shadow-sm px-5 py-3" onclick="addHotelService()" style="
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        border: none;
                        color: white;
                        font-weight: 600;
                        letter-spacing: 0.5px;
                        transition: all 0.3s ease;
                        border-radius: 8px;
                        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.6)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.4)';">
                        <i class="ri-add-circle-line me-2" style="font-size: 1.2em;"></i>Add More Hotels
                    </button>
                </div>
            </div>
            
            <!-- Day-based Service Sections -->
                @php
                    // Check if any day has services
                    $hasAnyServices = false;
                    foreach($tourDays as $dayInfo) {
                        if(count($dayInfo['orders']) > 0) {
                            $hasAnyServices = true;
                            break;
                        }
                    }
                @endphp
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body mb-4">
                            @php
                                // Collect all attractions, guides, and transport services from all days
                                $allAttractions = [];
                                $allGuides = [];
                                $allTransportHourly = [];
                                $allTransportPoint = [];
                                $allLocalTransport = [];
                                foreach($tourDays as $dateKey => $dayInfo) {
                                    foreach($dayInfo['orders'] as $order) {
                                        if ($order->type === 'attraction') {
                                            $allAttractions[] = $order;
                                        } elseif ($order->type === 'guide') {
                                            $allGuides[] = $order;
                                        } elseif ($order->type === 'travel_hourly') {
                                            $allTransportHourly[] = $order;
                                        } elseif ($order->type === 'travel_point') {
                                            $allTransportPoint[] = $order;
                                        } elseif ($order->type === 'local_transport') {
                                            $allLocalTransport[] = $order;
                                        }
                                    }
                                }
                            @endphp
                            @foreach($tourDays as $dateKey => $dayInfo)
                                @if(count($dayInfo['orders']) > 0)
                            <!-- Day {{ $dayInfo['day_number'] }} Services -->
                            <div class="day-services mb-4">
                                <!-- <h6 class="fw-bold text-primary mb-3">
                                    <i class="ri-calendar-check-line me-2"></i>Day {{ $dayInfo['day_number'] }} ({{ $dayInfo['date']->format('l, jS F Y') }})
                                </h6> -->
                                    @php
                                        // Group this day's orders by type (excluding attractions and guides)
                                        $dayOrdersByType = [];
                                        foreach($dayInfo['orders'] as $order) {
                                            $type = $order->type;
                                            // Skip attractions and guides as they will be shown separately
                                            if ($type === 'attraction' || $type === 'guide') {
                                                continue;
                                            }
                                            if (!isset($dayOrdersByType[$type])) {
                                                $dayOrdersByType[$type] = [];
                                            }
                                            $dayOrdersByType[$type][] = $order;
                                        }
                                    @endphp
                                    
                                <!-- Arrival Transport Services Section -->
                                @if(isset($dayOrdersByType['entry_port']))
                                <div class="service-section mb-3">
                                    <div class="card border-success shadow-sm">
                                        <div class="card-header bg-success text-white">
                                            <div class="d-flex align-items-center">
                                                <span class="service-icon me-3">
                                                    <i class="ri-login-circle-line fs-4"></i>
                                                </span>
                                                <div>
                                                    <h6 class="mb-0 fw-bold">Arrival Transport Services</h6>
                                                    <small class="opacity-75">Edit entry port transfers</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body mt-3">
                                            @foreach($dayOrdersByType['entry_port'] as $index => $order)
                                                @php
                                                    $transportData = is_array($order->processed_data) ? $order->processed_data : json_decode($order->processed_data, true);
                                                    if (isset($transportData[0])) {
                                                        $transportData = $transportData[0];
                                                    }
                                                    $cityValue = $transportData['city'] ?? '';
                                                    $pickupLocation = $transportData['entrypickup'] ?? $transportData['pickup'] ?? ($transportData['exitpickup'] ?? '');
                                                    $dropoffLocation = $transportData['entrydropoff'] ?? $transportData['dropoff'] ?? ($transportData['exitdropoff'] ?? '');
                                                    $pickupTime = $transportData['entrytime'] ?? $transportData['time'] ?? '';
                                                    $vehicleName = $transportData['vehicles_name'] ?? '';
                                                    $vehicleType = $transportData['type'] ?? '';
                                                    $passengers = $transportData['passengers'] ?? '';
                                                    $availableVehicles = $vehicles ?? collect();
                                                @endphp
                                                <form class="service-item mb-3 p-3 border rounded shadow-sm bg-white transport-edit-form" data-form-type="entry_port" data-update-url="{{ route('edit-tour.update-transport', $order->booking_id) }}" onsubmit="updateExistingTransport(event, {{ $order->booking_id }})">
                                                    @csrf
                                                    <input type="hidden" name="type" value="entry_port">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <h6 class="mb-0 fw-bold text-primary"><i class="ri-login-circle-line me-2"></i>Entry Port Transfer #{{ $index + 1 }}</h6>
                                                        <div class="d-flex gap-2">
                                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTransportService({{ $order->booking_id }})">
                                                                <i class="ri-delete-bin-line"></i> Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="row g-3 align-items-end">
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-map-pin-line me-1 text-success"></i>City</label>
                                                            <select class="form-select border-2" name="city">
                                                                <option value="">Select city</option>
                                                                @foreach($cities as $city)
                                                                    <option value="{{ $city->name }}" {{ $city->name == $cityValue ? 'selected' : '' }}>{{ $city->name }}</option>
                                                                @endforeach
                                                                @if($cityValue && !$cities->contains('name', $cityValue))
                                                                    <option value="{{ $cityValue }}" selected>{{ $cityValue }}</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-map-pin-line me-1 text-success"></i>Pick Up Location</label>
                                                            <select class="form-select border-2" name="pickup_location">
                                                                <option value="">Select pickup port</option>
                                                                @foreach($ports as $port)
                                                                    <option value="{{ $port->port_name }}" {{ $port->port_name == $pickupLocation ? 'selected' : '' }}>
                                                                        {{ $port->port_name }}
                                                                    </option>
                                                                @endforeach
                                                                @if($pickupLocation && !$ports->contains('port_name', $pickupLocation))
                                                                    <option value="{{ $pickupLocation }}" selected>{{ $pickupLocation }}</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-map-pin-line me-1 text-danger"></i>Drop Off Location</label>
                                                            <select class="form-select border-2" name="dropoff_location">
                                                                <option value="">Select dropoff</option>
                                                                @foreach($hotels as $hotel)
                                                                    <option value="{{ $hotel->name }}" {{ $hotel->name == $dropoffLocation ? 'selected' : '' }}>
                                                                        {{ $hotel->name }}
                                                                    </option>
                                                                @endforeach
                                                                @if($dropoffLocation && !$hotels->contains('name', $dropoffLocation))
                                                                    <option value="{{ $dropoffLocation }}" selected>{{ $dropoffLocation }}</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-time-line me-1 text-warning"></i>Pick Up Time</label>
                                                            @php
                                                                $time24 = $pickupTime ? date('H:i', strtotime($pickupTime)) : '';
                                                            @endphp
                                                            <input type="time" class="form-control border-2" name="pickup_time" value="{{ $time24 }}" required>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-car-line me-1 text-info"></i>Vehicle</label>
                                                            @php $vehicleMatched = false; @endphp
                                                            <select class="form-select border-2" name="vehicle_name">
                                                                <option value="">{{ $vehicleName ? 'Select vehicle' : 'Select vehicle' }}</option>
                                                                @foreach($availableVehicles as $vehicleOption)
                                                                    @php
                                                                        $vehicleDisplayName = $vehicleOption->vehicle_name ?? $vehicleOption->vehicle_id;
                                                                        $isSelected = $vehicleDisplayName && strcasecmp($vehicleDisplayName, $vehicleName ?? '') === 0;
                                                                        $vehicleMatched = $vehicleMatched || $isSelected;
                                                                    @endphp
                                                                    <option value="{{ $vehicleDisplayName }}" {{ $isSelected ? 'selected' : '' }}>
                                                                        {{ $vehicleDisplayName }}
                                                                        @if(!empty($vehicleOption->vehicle_type))
                                                                            ({{ $vehicleOption->vehicle_type }})
                                                                        @endif
                                                                    </option>
                                                                @endforeach
                                                                @if($vehicleName && !$vehicleMatched)
                                                                    <option value="{{ $vehicleName }}" selected>{{ $vehicleName }}</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label fw-semibold text-muted mb-1">Service Type</label>
                                                            <select class="form-select border-2" name="vehicle_type">
                                                                <option value="">Select type</option>
                                                                <option value="Private" {{ strtolower($vehicleType) === 'private' ? 'selected' : '' }}>Private</option>
                                                                <option value="Shared" {{ strtolower($vehicleType) === 'shared' ? 'selected' : '' }}>Shared</option>
                                                            </select>
                                                        </div>
                                                        <!-- <div class="col-md-2">
                                                            <label class="form-label fw-semibold text-muted mb-1">Passengers</label>
                                                            <input type="number" class="form-control border-2" name="passenger_count" min="1" value="{{ $passengers }}" placeholder="Count">
                                                        </div> -->
                                                    </div>
                                                    <div class="d-flex justify-content-end align-items-center gap-3 mt-3">
                                                        <div class="text-muted small" id="transport_feedback_{{ $order->booking_id }}_entry_port"></div>
                                                        <button type="submit" class="btn btn-sm btn-primary d-flex align-items-center gap-2">
                                                            <span class="spinner-border spinner-border-sm d-none" id="transport_spinner_{{ $order->booking_id }}_entry_port"></span>
                                                            <span>Save Changes</span>
                                                        </button>
                                                    </div>
                                                </form>
                                            @endforeach
                                        </div>
                                        <!-- Arrival Services Add More Section (Static) -->
                                        <div class="card-footer bg-light">
                                            <div class="text-center py-3">
                                                <button type="button" class="btn btn-gradient-primary btn-lg shadow-sm px-5 py-3" onclick="addArrivalService()" style="
                                                    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
                                                    border: none;
                                                    color: white;
                                                    font-weight: 600;
                                                    letter-spacing: 0.5px;
                                                    transition: all 0.3s ease;
                                                    border-radius: 8px;
                                                    box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4);
                                                " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(17, 153, 142, 0.6)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(17, 153, 142, 0.4)';">
                                                    <i class="ri-add-circle-line me-2" style="font-size: 1.2em;"></i>Add More Arrival Services
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Restaurant Services Section -->
                                @if(isset($dayOrdersByType['restaurant']))
                                <div class="service-section mb-3">
                                    <div class="card border-success shadow-sm">
                                        <div class="card-header bg-success text-white">
                                            <div class="d-flex align-items-center">
                                                <span class="service-icon me-3">
                                                    <i class="ri-restaurant-2-line fs-4"></i>
                                                </span>
                                                <div>
                                                    <h6 class="mb-0 fw-bold">Book Restaurant Services</h6>
                                                    <small class="opacity-75">Select restaurants and configure your dining experience</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body mt-3">
                                            @foreach($dayOrdersByType['restaurant'] as $index => $order)
                                            @php
                                                $restaurantData = $order->processed_data;
                                                $payload = [];
                                                if (is_array($restaurantData)) {
                                                    $payload = isset($restaurantData[0]) ? $restaurantData[0] : $restaurantData;
                                                }
                                                $restaurantName = $payload['restaurantName'] ?? 'N/A';
                                                $mealType = $payload['mealType'] ?? '';
                                                $mealSpecificType = $payload['mealSpecificType'] ?? '';
                                                $timeSlot = $payload['visitTime'] ?? '';
                                                $adultCount = $payload['adultCount'] ?? 0;
                                                $childCount = $payload['childCount'] ?? 0;
                                                $totalPrice = $payload['totalPrice'] ?? 0;
                                                $mealDescription = $payload['MealDescription'] ?? null;
                                                $restaurantNotes = $payload['notes'] ?? '';

                                                if ($totalPrice <= 0 && is_array($mealDescription)) {
                                                    $calculatedTotal = 0;
                                                    foreach ($mealDescription as $meal) {
                                                        $mealPrice = $meal['price'] ?? 0;
                                                        $quantity = $meal['quantity'] ?? 1;
                                                        $calculatedTotal += $mealPrice * $quantity;
                                                    }
                                                    if ($calculatedTotal > 0) {
                                                        $totalPrice = $calculatedTotal;
                                                    }
                                                }
                                            @endphp
                                            <form class="service-item mb-3 p-3 border rounded shadow-sm bg-white restaurant-edit-form" data-update-url="{{ route('edit-tour.update-restaurant', $order->booking_id) }}" onsubmit="updateExistingRestaurant(event, {{ $order->booking_id }})">
                                                @csrf
                                                <input type="hidden" name="type" value="restaurant">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="mb-0 fw-bold text-success"><i class="ri-restaurant-line me-2"></i>Restaurant Booking #{{ $index + 1 }}</h6>
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRestaurantService({{ $order->booking_id }})">
                                                            <i class="ri-delete-bin-line"></i> Remove
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold text-muted mb-1"><i class="ri-restaurant-line me-1 text-primary"></i>Restaurant Name</label>
                                                        <select class="form-select border-2" name="restaurant_name" id="restaurant_name_{{ $order->booking_id }}" onchange="loadRestaurantMealsForEdit({{ $order->booking_id }})" required>
                                                            <option value="">Select Restaurant</option>
                                                            @php
                                                                $tourCountry = $tour->destination ?? '';
                                                                $filteredRestaurants = collect($restaurants ?? [])->filter(function($restaurant) use ($tourCountry) {
                                                                    // Check if restaurant has country field directly
                                                                    if (isset($restaurant->country) && $restaurant->country == $tourCountry) {
                                                                        return true;
                                                                    }
                                                                    // If no country filter available, include all restaurants
                                                                    return empty($tourCountry);
                                                                });
                                                            @endphp
                                                            @foreach($filteredRestaurants as $restaurant)
                                                                <option value="{{ $restaurant->name }}" {{ $restaurantName == $restaurant->name ? 'selected' : '' }} 
                                                                    data-restaurant-id="{{ $restaurant->restaurant_id ?? '' }}"
                                                                    data-restaurant-data="{{ json_encode($restaurant) }}">
                                                                    {{ $restaurant->name }}
                                                                    @if(isset($restaurant->city))
                                                                        - {{ $restaurant->city }}
                                                                    @endif
                                                                    @if(isset($restaurant->cuisine))
                                                                        ({{ $restaurant->cuisine }})
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                            @if($restaurantName && !$filteredRestaurants->pluck('name')->contains($restaurantName))
                                                                <option value="{{ $restaurantName }}" selected>{{ $restaurantName }}</option>
                                                            @endif
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fw-semibold text-muted mb-1"><i class="ri-cup-line me-1 text-warning"></i>Meal Type</label>
                                                        <select class="form-select border-2" name="meal_type" id="meal_type_{{ $order->booking_id }}" onchange="loadDishTypesForEdit({{ $order->booking_id }})" required>
                                                            <option value="">Select Restaurant First</option>
                                                            @if($mealType)
                                                                <option value="{{ $mealType }}" selected>{{ $mealType }}</option>
                                                            @endif
                                                        </select>
                                                        <input type="hidden" id="current_meal_type_{{ $order->booking_id }}" value="{{ $mealType }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fw-semibold text-muted mb-1"><i class="ri-cup-line me-1 text-info"></i>Dish Type</label>
                                                        <select class="form-select border-2" name="meal_specific_type" id="meal_specific_type_{{ $order->booking_id }}" required>
                                                            <option value="">Select Meal Type First</option>
                                                            @if($mealSpecificType)
                                                                <option value="{{ $mealSpecificType }}" selected>{{ $mealSpecificType }}</option>
                                                            @endif
                                                        </select>
                                                        <input type="hidden" id="current_dish_type_{{ $order->booking_id }}" value="{{ $mealSpecificType }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fw-semibold text-muted mb-1"><i class="ri-time-line me-1 text-warning"></i>Time Slot</label>
                                                        <input type="text" class="form-control border-2" name="time_slot" value="{{ $timeSlot }}" placeholder="e.g. 07:00 AM">
                                                        <small class="text-muted">Available time slots</small>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fw-semibold text-muted mb-1"><i class="ri-user-line me-1 text-secondary"></i>Adults</label>
                                                        <input type="number" class="form-control border-2" name="adult_count" min="0" value="{{ $adultCount }}" required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fw-semibold text-muted mb-1"><i class="ri-user-smile-line me-1 text-secondary"></i>Children</label>
                                                        <input type="number" class="form-control border-2" name="child_count" min="0" value="{{ $childCount }}" required>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end align-items-center gap-3 mt-3">
                                                    <div class="text-muted small" id="restaurant_feedback_{{ $order->booking_id }}"></div>
                                                    <button type="submit" class="btn btn-sm btn-primary d-flex align-items-center gap-2">
                                                        <span class="spinner-border spinner-border-sm d-none" id="restaurant_spinner_{{ $order->booking_id }}"></span>
                                                        <span>Save Changes</span>
                                                    </button>
                                                </div>
                                            </form>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                                @endif
                            @endforeach
                            
                            
                            
                            <!-- Restaurant Services Add More Section (Static) -->
                            <div class="card-footer bg-light">
                                <div class="text-center py-3">
                                    <button type="button" class="btn btn-gradient-primary btn-lg shadow-sm px-5 py-3" onclick="addRestaurantService()" style="
                                        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
                                        border: none;
                                        color: white;
                                        font-weight: 600;
                                        letter-spacing: 0.5px;
                                        transition: all 0.3s ease;
                                        border-radius: 8px;
                                        box-shadow: 0 4px 15px rgba(67, 233, 123, 0.4);
                                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(67, 233, 123, 0.6)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(67, 233, 123, 0.4)';">
                                        <i class="ri-add-circle-line me-2" style="font-size: 1.2em;"></i>Add More Restaurants
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Other Transport Services Section (Unified) -->
                            @if(count($allTransportHourly) > 0 || count($allTransportPoint) > 0 || count($allLocalTransport) > 0)
                            <div class="service-section mb-3">
                                <div class="card border-warning shadow-sm">
                                    <div class="card-header bg-warning text-dark">
                                        <div class="d-flex align-items-center">
                                            <span class="service-icon me-3">
                                                <i class="ri-car-line fs-4"></i>
                                            </span>
                                            <div>
                                                <h6 class="mb-0 fw-bold">Other Transport Services</h6>
                                                <small class="opacity-75">Local transfers and other transport services from all days</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body mt-3">
                                        @if(count($allTransportHourly) > 0)
                                            @foreach($allTransportHourly as $index => $order)
                                                @php
                                                    $transportData = is_array($order->processed_data) ? $order->processed_data : json_decode($order->processed_data, true);
                                                    if (isset($transportData[0])) {
                                                        $transportData = $transportData[0];
                                                    }
                                                    $pickupLocation = $transportData['entrypickup'] ?? $transportData['pickupLocation'] ?? '';
                                                    $dropoffLocation = $transportData['entrydropoff'] ?? $transportData['dropoffLocation'] ?? '';
                                                    $pickupTime = $transportData['entrytime'] ?? '';
                                                    $pickupTimeValue = $pickupTime ? date('H:i', strtotime($pickupTime)) : '';
                                                    $pickupDateRaw = $transportData['pickupdate'] ?? $transportData['bookingDate'] ?? '';
                                                    $pickupDate = '';
                                                    if ($pickupDateRaw) {
                                                        try {
                                                            $pickupDate = \Carbon\Carbon::parse($pickupDateRaw)->format('Y-m-d');
                                                        } catch (\Exception $exception) {
                                                            $pickupDate = $pickupDateRaw;
                                                        }
                                                    }
                                                    $vehicleName = $transportData['vehicles_name'] ?? '';
                                                    $vehicleType = $transportData['type'] ?? '';
                                                    $selectedHours = $transportData['selectedHours'] ?? $transportData['hours'] ?? '';
                                                    $totalPrice = $transportData['totalPrice'] ?? $transportData['price'] ?? 0;
                                                    $adultCount = $transportData['adultCount'] ?? $transportData['adults'] ?? 0;
                                                    $childCount = $transportData['childCount'] ?? $transportData['children'] ?? 0;
                                                    $notes = $transportData['notes'] ?? $transportData['specialRequests'] ?? '';
                                                    $availableVehicles = $vehicles ?? collect();
                                                @endphp
                                                <form class="service-item mb-3 p-3 border rounded shadow-sm bg-white transport-edit-form" data-form-type="travel_hourly" data-update-url="{{ route('edit-tour.update-transport', $order->booking_id) }}" onsubmit="updateExistingTransport(event, {{ $order->booking_id }})">
                                                    @csrf
                                                    <input type="hidden" name="type" value="travel_hourly">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <h6 class="mb-0 fw-bold text-warning"><i class="ri-time-line me-2"></i>Hourly Transport #{{ $index + 1 }}</h6>
                                                        <div class="d-flex gap-2">
                                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTransportService({{ $order->booking_id }})">
                                                                <i class="ri-delete-bin-line"></i> Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-map-pin-line me-1 text-primary"></i>Pickup Location</label>
                                                            <div class="position-relative">
                                                                <input type="text" class="form-control border-2 google-maps-autocomplete" name="pickup_location" value="{{ $pickupLocation }}" placeholder="Search pickup location" style="padding-left: 45px;" required>
                                                                <i class="ri-map-pin-line position-absolute text-primary" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                            </div>
                                                            <input type="hidden" name="pickup_latitude" value="{{ $transportData['pickup_latitude'] ?? '' }}">
                                                            <input type="hidden" name="pickup_longitude" value="{{ $transportData['pickup_longitude'] ?? '' }}">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-calendar-line me-1 text-secondary"></i>Pickup Date</label>
                                                            <input type="date" class="form-control border-2" name="pickup_date" value="{{ $pickupDate }}">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-time-line me-1 text-warning"></i>Pickup Time</label>
                                                            <input type="time" class="form-control border-2" name="pickup_time" value="{{ $pickupTimeValue }}" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-car-line me-1 text-info"></i>Vehicle</label>
                                                            @php $vehicleMatched = false; @endphp
                                                            <select class="form-select border-2 js-hourly-vehicle-select" name="vehicle_name" id="hourly_vehicle_name_{{ $order->booking_id }}">
                                                                <option value="">{{ $vehicleName ? 'Select vehicle' : 'Select vehicle' }}</option>
                                                                @foreach($availableVehicles as $vehicleOption)
                                                                    @php
                                                                        $vehicleDisplayName = $vehicleOption->vehicle_name ?? $vehicleOption->vehicle_id;
                                                                        $isSelected = false;
                                                                        if ($vehicleDisplayName) {
                                                                            $isSelected = strcasecmp($vehicleDisplayName, $vehicleName ?? '') === 0;
                                                                        }
                                                                        $vehicleMatched = $vehicleMatched || $isSelected;
                                                                    @endphp
                                                                    <option value="{{ $vehicleDisplayName }}"
                                                                        data-vehicle-id="{{ $vehicleOption->vehicle_id }}"
                                                                        data-vehicle-name="{{ $vehicleDisplayName }}"
                                                                        data-vehicle-type="{{ $vehicleOption->vehicle_type ?? '' }}">
                                                                        {{ $vehicleDisplayName }}
                                                                        @if(!empty($vehicleOption->vehicle_type))
                                                                            ({{ $vehicleOption->vehicle_type }})
                                                                        @endif
                                                                    </option>
                                                                @endforeach
                                                                @if($vehicleName && !$vehicleMatched)
                                                                    <option value="{{ $vehicleName }}" selected>{{ $vehicleName }}</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-user-settings-line me-1 text-secondary"></i>Service Type</label>
                                                            <select class="form-select border-2" name="vehicle_type">
                                                                <option value="">Select type</option>
                                                                <option value="Private" {{ strtolower($vehicleType) === 'private' ? 'selected' : '' }}>Private</option>
                                                                <option value="Shared" {{ strtolower($vehicleType) === 'shared' ? 'selected' : '' }}>Shared</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-hourglass-line me-1 text-info"></i>Hours</label>
                                                            <input type="number" class="form-control border-2" name="selected_hours" min="1" value="{{ $selectedHours }}" placeholder="e.g. 4">
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-end align-items-center gap-3 mt-3">
                                                        <div class="text-muted small" id="transport_feedback_{{ $order->booking_id }}_travel_hourly"></div>
                                                        <button type="submit" class="btn btn-sm btn-primary d-flex align-items-center gap-2">
                                                            <span class="spinner-border spinner-border-sm d-none" id="transport_spinner_{{ $order->booking_id }}_travel_hourly"></span>
                                                            <span>Save Changes</span>
                                                        </button>
                                                    </div>
                                                </form>
                                            @endforeach
                                        @endif

                                        @if(count($allTransportPoint) > 0)
                                            @foreach($allTransportPoint as $index => $order)
                                                @php
                                                    $transportData = is_array($order->processed_data) ? $order->processed_data : json_decode($order->processed_data, true);
                                                    if (isset($transportData[0])) {
                                                        $transportData = $transportData[0];
                                                    }
                                                    $pickupLocation = $transportData['entrypickup'] ?? $transportData['pickupLocation'] ?? '';
                                                    $dropoffLocation = $transportData['entrydropoff'] ?? $transportData['dropoffLocation'] ?? '';
                                                    $pickupTime = $transportData['entrytime'] ?? '';
                                                    $pickupTimeValue = $pickupTime ? date('H:i', strtotime($pickupTime)) : '';
                                                    $pickupDateRaw = $transportData['pickupdate'] ?? $transportData['bookingDate'] ?? '';
                                                    $pickupDate = '';
                                                    if ($pickupDateRaw) {
                                                        try {
                                                            $pickupDate = \Carbon\Carbon::parse($pickupDateRaw)->format('Y-m-d');
                                                        } catch (\Exception $exception) {
                                                            $pickupDate = $pickupDateRaw;
                                                        }
                                                    }
                                                    $vehicleName = $transportData['vehicles_name'] ?? '';
                                                    $vehicleType = $transportData['type'] ?? '';
                                                    $totalPrice = $transportData['totalPrice'] ?? $transportData['price'] ?? 0;
                                                    $adultCount = $transportData['adultCount'] ?? $transportData['adults'] ?? 0;
                                                    $childCount = $transportData['childCount'] ?? $transportData['children'] ?? 0;
                                                    $distance = $transportData['distance'] ?? 0;
                                                    $notes = $transportData['notes'] ?? $transportData['specialRequests'] ?? '';
                                                    $pickupLatitude = $transportData['pickup_latitude'] ?? $transportData['pickup_lat'] ?? '';
                                                    $pickupLongitude = $transportData['pickup_longitude'] ?? $transportData['pickup_lng'] ?? '';
                                                    $dropoffLatitude = $transportData['dropoff_latitude'] ?? $transportData['dropoff_lat'] ?? '';
                                                    $dropoffLongitude = $transportData['dropoff_longitude'] ?? $transportData['dropoff_lng'] ?? '';
                                                    $pickupPlaceId = $transportData['pickup_place_id'] ?? '';
                                                    $dropoffPlaceId = $transportData['dropoff_place_id'] ?? '';
                                                    $availableVehicles = $vehicles ?? collect();
                                                @endphp
                                                <form class="service-item mb-3 p-3 border rounded shadow-sm bg-white transport-edit-form" data-form-type="travel_point" data-update-url="{{ route('edit-tour.update-transport', $order->booking_id) }}" onsubmit="updateExistingTransport(event, {{ $order->booking_id }})">
                                                    @csrf
                                                    <input type="hidden" name="type" value="travel_point">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <h6 class="mb-0 fw-bold text-info"><i class="ri-map-pin-2-line me-2"></i>Point-to-Point Transport #{{ $index + 1 }}</h6>
                                                        <div class="d-flex gap-2">
                                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTransportService({{ $order->booking_id }})">
                                                                <i class="ri-delete-bin-line"></i> Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-map-pin-line me-1 text-primary"></i>Pickup Location</label>
                                                            <div class="position-relative">
                                                                <input 
                                                                    type="text" 
                                                                    class="form-control border-2 google-maps-autocomplete" 
                                                                    id="point_pickup_location_{{ $order->booking_id }}"
                                                                    name="pickup_location" 
                                                                    value="{{ $pickupLocation }}" 
                                                                    placeholder="Search pickup location" 
                                                                    style="padding-left: 45px;" 
                                                                    required>
                                                                <i class="ri-map-pin-line position-absolute text-primary" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                                <input type="hidden" name="pickup_latitude" id="point_pickup_lat_{{ $order->booking_id }}" value="{{ $pickupLatitude }}">
                                                                <input type="hidden" name="pickup_longitude" id="point_pickup_lng_{{ $order->booking_id }}" value="{{ $pickupLongitude }}">
                                                                <input type="hidden" name="pickup_place_id" id="point_pickup_place_id_{{ $order->booking_id }}" value="{{ $pickupPlaceId }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-map-pin-2-line me-1 text-success"></i>Dropoff Location</label>
                                                            <div class="position-relative">
                                                                <input 
                                                                    type="text" 
                                                                    class="form-control border-2 google-maps-autocomplete" 
                                                                    id="point_dropoff_location_{{ $order->booking_id }}"
                                                                    name="dropoff_location" 
                                                                    value="{{ $dropoffLocation }}" 
                                                                    placeholder="Search dropoff location" 
                                                                    style="padding-left: 45px;" 
                                                                    required>
                                                                <i class="ri-map-pin-2-line position-absolute text-success" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                                <input type="hidden" name="dropoff_latitude" id="point_dropoff_lat_{{ $order->booking_id }}" value="{{ $dropoffLatitude }}">
                                                                <input type="hidden" name="dropoff_longitude" id="point_dropoff_lng_{{ $order->booking_id }}" value="{{ $dropoffLongitude }}">
                                                                <input type="hidden" name="dropoff_place_id" id="point_dropoff_place_id_{{ $order->booking_id }}" value="{{ $dropoffPlaceId }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-calendar-line me-1 text-secondary"></i>Pickup Date</label>
                                                            <input type="date" class="form-control border-2" name="pickup_date" value="{{ $pickupDate }}">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-time-line me-1 text-warning"></i>Pickup Time</label>
                                                            <input type="time" class="form-control border-2" name="pickup_time" value="{{ $pickupTimeValue }}" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-car-line me-1 text-info"></i>Vehicle Name</label>
                                                            @php $vehicleMatched = false; @endphp
                                                            <select class="form-select border-2" name="vehicle_name">
                                                                <option value="">{{ $vehicleName ? 'Select vehicle' : 'Select vehicle' }}</option>
                                                                @foreach($availableVehicles as $vehicleOption)
                                                                    @php
                                                                        $vehicleDisplayName = $vehicleOption->vehicle_name ?? $vehicleOption->vehicle_id;
                                                                        $isSelected = $vehicleDisplayName && strcasecmp($vehicleDisplayName, $vehicleName ?? '') === 0;
                                                                        $vehicleMatched = $vehicleMatched || $isSelected;
                                                                    @endphp
                                                                    <option value="{{ $vehicleDisplayName }}" {{ $isSelected ? 'selected' : '' }}>
                                                                        {{ $vehicleDisplayName }}
                                                                        @if(!empty($vehicleOption->vehicle_type))
                                                                            ({{ $vehicleOption->vehicle_type }})
                                                                        @endif
                                                                    </option>
                                                                @endforeach
                                                                @if($vehicleName && !$vehicleMatched)
                                                                    <option value="{{ $vehicleName }}" selected>{{ $vehicleName }}</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-user-settings-line me-1 text-secondary"></i>Service Type</label>
                                                            <select class="form-select border-2" name="vehicle_type">
                                                                <option value="">Select type</option>
                                                                <option value="Private" {{ strtolower($vehicleType) === 'private' ? 'selected' : '' }}>Private</option>
                                                                <option value="Shared" {{ strtolower($vehicleType) === 'shared' ? 'selected' : '' }}>Shared</option>
                                                            </select>
                                                        </div>
                                                        <!-- <div class="col-md-2">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-roadster-line me-1 text-info"></i>Distance (km)</label>
                                                            <input type="number" class="form-control border-2" name="distance" step="0.01" min="0" value="{{ $distance }}" placeholder="0">
                                                        </div> -->
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-money-dollar-circle-line me-1 text-success"></i>Total Price</label>
                                                            <input type="number" class="form-control border-2" name="total_price" step="0.01" min="0" value="{{ number_format((float) $totalPrice, 2, '.', '') }}" placeholder="0.00">
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-end align-items-center gap-3 mt-3">
                                                        <div class="text-muted small" id="transport_feedback_{{ $order->booking_id }}_travel_point"></div>
                                                        <button type="submit" class="btn btn-sm btn-primary d-flex align-items-center gap-2">
                                                            <span class="spinner-border spinner-border-sm d-none" id="transport_spinner_{{ $order->booking_id }}_travel_point"></span>
                                                            <span>Save Changes</span>
                                                        </button>
                                                    </div>
                                                </form>
                                            @endforeach
                                        @endif

                                        @if(count($allLocalTransport) > 0)
                                            @foreach($allLocalTransport as $index => $order)
                                                @php
                                                    $transportData = is_array($order->processed_data) ? $order->processed_data : json_decode($order->processed_data, true);
                                                    if (isset($transportData[0])) {
                                                        $transportData = $transportData[0];
                                                    }
                                                    $pickupLocation = $transportData['entrypickup'] ?? $transportData['pickupLocation'] ?? '';
                                                    $dropoffLocation = $transportData['entrydropoff'] ?? $transportData['dropoffLocation'] ?? '';
                                                    $pickupTime = $transportData['entrytime'] ?? $transportData['time'] ?? '';
                                                    $pickupTimeValue = $pickupTime ? date('H:i', strtotime($pickupTime)) : '';
                                                    $pickupDateRaw = $transportData['pickupdate'] ?? $transportData['bookingDate'] ?? '';
                                                    $pickupDate = '';
                                                    if ($pickupDateRaw) {
                                                        try {
                                                            $pickupDate = \Carbon\Carbon::parse($pickupDateRaw)->format('Y-m-d');
                                                        } catch (\Exception $exception) {
                                                            $pickupDate = $pickupDateRaw;
                                                        }
                                                    }
                                                    $vehicleName = $transportData['vehicles_name'] ?? '';
                                                    $vehicleId = $transportData['vehicles_id'] ?? $transportData['vehicle_id'] ?? '';
                                                    $vehicleType = $transportData['type'] ?? '';
                                                    $totalPrice = $transportData['totalPrice'] ?? $transportData['price'] ?? 0;
                                                    $adultCount = $transportData['adultCount'] ?? $transportData['adults'] ?? 0;
                                                    $childCount = $transportData['childCount'] ?? $transportData['children'] ?? 0;
                                                    $notes = $transportData['notes'] ?? $transportData['specialRequests'] ?? '';
                                                    $pickupZoneId = $transportData['pickup_zone_id'] ?? $transportData['pickupZoneId'] ?? '';
                                                    $dropoffZoneId = $transportData['dropoff_zone_id'] ?? $transportData['dropoffZoneId'] ?? '';
                                                    $pickupLocationType = $transportData['pickup_location_type'] ?? '';
                                                    $dropoffLocationType = $transportData['dropoff_location_type'] ?? '';
                                                    $availablePorts = $ports ?? collect();
                                                    $availableHotels = $hotels ?? collect();
                                                    $availableAttractions = $attractions ?? collect();
                                                    $availableRestaurants = $restaurants ?? collect();
                                                    $availableVehicles = $vehicles ?? collect();
                                                @endphp
                                                <form
                                                    class="service-item mb-3 p-3 border rounded shadow-sm bg-white transport-edit-form"
                                                    data-form-type="local_transport"
                                                    data-update-url="{{ route('edit-tour.update-transport', $order->booking_id) }}"
                                                    data-booking-id="{{ $order->booking_id }}"
                                                    data-fetch-url="{{ route('fetch-vehicles-by-zones') }}"
                                                    data-zone-status="{{ $UserDmc->zone_on ?? 0 }}"
                                                    data-city="{{ $tour->city ?? $tour->destination ?? '' }}"
                                                    data-initial-vehicle-id="{{ $vehicleId }}"
                                                    data-initial-vehicle-name="{{ $vehicleName }}"
                                                    data-initial-service-type="{{ $vehicleType }}"
                                                    data-initial-total-price="{{ number_format((float) $totalPrice, 2, '.', '') }}"
                                                    data-initial-private-price="{{ $transportData['private_price'] ?? $transportData['price'] ?? $totalPrice }}"
                                                    data-initial-shared-price="{{ $transportData['shared_price'] ?? '' }}"
                                                    data-initial-vehicle-sharable="{{ $transportData['sharable'] ?? $transportData['sharable_option'] ?? '' }}"
                                                    onsubmit="updateExistingTransport(event, {{ $order->booking_id }})">
                                                    @csrf
                                                    <input type="hidden" name="type" value="local_transport">
                                                    <input type="hidden" name="pickup_location_reference" id="pickup_location_reference_{{ $order->booking_id }}" value="{{ $pickupZoneId }}">
                                                    <input type="hidden" name="pickup_location_type" id="pickup_location_type_{{ $order->booking_id }}" value="{{ $pickupLocationType }}">
                                                    <input type="hidden" name="dropoff_location_reference" id="dropoff_location_reference_{{ $order->booking_id }}" value="{{ $dropoffZoneId }}">
                                                    <input type="hidden" name="dropoff_location_type" id="dropoff_location_type_{{ $order->booking_id }}" value="{{ $dropoffLocationType }}">
                                                    <input type="hidden" name="vehicle_id" id="vehicle_id_{{ $order->booking_id }}" value="{{ $vehicleId }}">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <h6 class="mb-0 fw-bold text-secondary"><i class="ri-taxi-line me-2"></i>Local Transport #{{ $index + 1 }}</h6>
                                                        <div class="d-flex gap-2">
                                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTransportService({{ $order->booking_id }})">
                                                                <i class="ri-delete-bin-line"></i> Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-map-pin-line me-1 text-primary"></i>Pickup Zone/Location</label>
                                                            @php $pickupMatched = false; @endphp
                                                            <select class="form-select border-2 js-local-pickup-select" name="pickup_location" id="pickup_location_{{ $order->booking_id }}" data-booking-id="{{ $order->booking_id }}" required>
                                                                <option value="">{{ $pickupLocation ? 'Select pickup location' : 'Select pickup location' }}</option>
                                                                <optgroup label="Ports">
                                                                    @foreach($availablePorts as $port)
                                                                        @php $isSelected = strcasecmp($port->port_name ?? '', $pickupLocation ?? '') === 0; $pickupMatched = $pickupMatched || $isSelected; @endphp
                                                                        <option value="{{ $port->port_name }}" data-location-type="Port" data-location-id="{{ $port->port_id }}" {{ $isSelected ? 'selected' : '' }}>
                                                                            {{ $port->port_name }}
                                                                        </option>
                                                                    @endforeach
                                                                </optgroup>
                                                                <optgroup label="Hotels">
                                                                    @foreach($availableHotels as $hotelOption)
                                                                        @php $isSelected = strcasecmp($hotelOption->name ?? '', $pickupLocation ?? '') === 0; $pickupMatched = $pickupMatched || $isSelected; @endphp
                                                                        <option value="{{ $hotelOption->name }}" data-location-type="Hotel" data-location-id="{{ $hotelOption->hotel_unique_id }}" {{ $isSelected ? 'selected' : '' }}>
                                                                            {{ $hotelOption->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </optgroup>
                                                                <optgroup label="Attractions">
                                                                    @foreach($availableAttractions as $attractionOption)
                                                                        @php $isSelected = strcasecmp($attractionOption->name ?? '', $pickupLocation ?? '') === 0; $pickupMatched = $pickupMatched || $isSelected; @endphp
                                                                        <option value="{{ $attractionOption->name }}" data-location-type="Attraction" data-location-id="{{ $attractionOption->attraction_id }}" {{ $isSelected ? 'selected' : '' }}>
                                                                            {{ $attractionOption->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </optgroup>
                                                                <optgroup label="Restaurants">
                                                                    @foreach($availableRestaurants as $restaurantOption)
                                                                        @php $isSelected = strcasecmp($restaurantOption->name ?? '', $pickupLocation ?? '') === 0; $pickupMatched = $pickupMatched || $isSelected; @endphp
                                                                        <option value="{{ $restaurantOption->name }}" data-location-type="Restaurant" data-location-id="{{ $restaurantOption->restaurant_id }}" {{ $isSelected ? 'selected' : '' }}>
                                                                            {{ $restaurantOption->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </optgroup>
                                                                @if($pickupLocation && !$pickupMatched)
                                                                    <option value="{{ $pickupLocation }}" selected
                                                                        data-location-id="{{ $pickupZoneId }}"
                                                                        data-location-type="{{ $pickupLocationType }}">
                                                                        {{ $pickupLocation }}
                                                                    </option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-map-pin-2-line me-1 text-success"></i>Dropoff Zone/Location</label>
                                                            @php $dropoffMatched = false; @endphp
                                                            <select class="form-select border-2 js-local-dropoff-select" name="dropoff_location" id="dropoff_location_{{ $order->booking_id }}" data-booking-id="{{ $order->booking_id }}" required>
                                                                <option value="">{{ $dropoffLocation ? 'Select dropoff location' : 'Select dropoff location' }}</option>
                                                                <optgroup label="Ports">
                                                                    @foreach($availablePorts as $port)
                                                                        @php $isSelected = strcasecmp($port->port_name ?? '', $dropoffLocation ?? '') === 0; $dropoffMatched = $dropoffMatched || $isSelected; @endphp
                                                                        <option value="{{ $port->port_name }}" data-location-type="Port" data-location-id="{{ $port->port_id }}" {{ $isSelected ? 'selected' : '' }}>
                                                                            {{ $port->port_name }}
                                                                        </option>
                                                                    @endforeach
                                                                </optgroup>
                                                                <optgroup label="Hotels">
                                                                    @foreach($availableHotels as $hotelOption)
                                                                        @php $isSelected = strcasecmp($hotelOption->name ?? '', $dropoffLocation ?? '') === 0; $dropoffMatched = $dropoffMatched || $isSelected; @endphp
                                                                        <option value="{{ $hotelOption->name }}" data-location-type="Hotel" data-location-id="{{ $hotelOption->hotel_unique_id }}" {{ $isSelected ? 'selected' : '' }}>
                                                                            {{ $hotelOption->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </optgroup>
                                                                <optgroup label="Attractions">
                                                                    @foreach($availableAttractions as $attractionOption)
                                                                        @php $isSelected = strcasecmp($attractionOption->name ?? '', $dropoffLocation ?? '') === 0; $dropoffMatched = $dropoffMatched || $isSelected; @endphp
                                                                        <option value="{{ $attractionOption->name }}" data-location-type="Attraction" data-location-id="{{ $attractionOption->attraction_id }}" {{ $isSelected ? 'selected' : '' }}>
                                                                            {{ $attractionOption->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </optgroup>
                                                                <optgroup label="Restaurants">
                                                                    @foreach($availableRestaurants as $restaurantOption)
                                                                        @php $isSelected = strcasecmp($restaurantOption->name ?? '', $dropoffLocation ?? '') === 0; $dropoffMatched = $dropoffMatched || $isSelected; @endphp
                                                                        <option value="{{ $restaurantOption->name }}" data-location-type="Restaurant" data-location-id="{{ $restaurantOption->restaurant_id }}" {{ $isSelected ? 'selected' : '' }}>
                                                                            {{ $restaurantOption->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </optgroup>
                                                                @if($dropoffLocation && !$dropoffMatched)
                                                                    <option value="{{ $dropoffLocation }}" selected
                                                                        data-location-id="{{ $dropoffZoneId }}"
                                                                        data-location-type="{{ $dropoffLocationType }}">
                                                                        {{ $dropoffLocation }}
                                                                    </option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-calendar-line me-1 text-secondary"></i>Pickup Date</label>
                                                            <input type="date" class="form-control border-2" name="pickup_date" value="{{ $pickupDate }}">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-time-line me-1 text-warning"></i>Pickup Time</label>
                                                            <input type="time" class="form-control border-2" name="pickup_time" value="{{ $pickupTimeValue }}" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-car-line me-1 text-info"></i>Vehicle Name</label>
                                                            @php $vehicleMatched = false; @endphp
                                                            <select class="form-select border-2 js-local-vehicle-select" name="vehicle_name" id="vehicle_name_{{ $order->booking_id }}" data-booking-id="{{ $order->booking_id }}">
                                                                <option value="">{{ $vehicleName ? 'Select vehicle' : 'Select vehicle' }}</option>
                                                                @foreach($availableVehicles as $vehicleOption)
                                                                    @php
                                                                        $vehicleDisplayName = $vehicleOption->vehicle_name ?? $vehicleOption->vehicle_id;
                                                                        $isSelected = false;
                                                                        if ($vehicleDisplayName) {
                                                                            $isSelected = strcasecmp($vehicleDisplayName, $vehicleName ?? '') === 0;
                                                                        }
                                                                        if (!$isSelected && $vehicleId) {
                                                                            $isSelected = (string)$vehicleId === (string)($vehicleOption->vehicle_id ?? '');
                                                                        }
                                                                        $vehicleMatched = $vehicleMatched || $isSelected;
                                                                    @endphp
                                                                    <option value="{{ $vehicleDisplayName }}"
                                                                        data-vehicle-id="{{ $vehicleOption->vehicle_id }}"
                                                                        data-vehicle-name="{{ $vehicleDisplayName }}"
                                                                        data-vehicle-type="{{ $vehicleOption->vehicle_type }}"
                                                                        data-seating-capacity="{{ $vehicleOption->seating_capacity ?? '' }}"
                                                                        data-private-price="{{ $vehicleOption->private_price ?? '' }}"
                                                                        data-shared-price="{{ $vehicleOption->shared_price ?? '' }}"
                                                                        data-cost-per-hour="{{ $vehicleOption->cost_per_hour ?? '' }}"
                                                                        data-sharable-cost-per-hour="{{ $vehicleOption->sharable_cost_per_hour ?? '' }}"
                                                                        data-sharable="{{ $vehicleOption->sharable ?? '' }}"
                                                                        data-service-type="{{ $vehicleOption->service_type ?? '' }}"
                                                                        data-vehicle='@json($vehicleOption)'
                                                                        {{ $isSelected ? 'selected' : '' }}>
                                                                        {{ $vehicleDisplayName }}
                                                                        @if(!empty($vehicleOption->vehicle_type))
                                                                            ({{ $vehicleOption->vehicle_type }})
                                                                        @endif
                                                                    </option>
                                                                @endforeach
                                                                @if($vehicleName && !$vehicleMatched)
                                                                    <option value="{{ $vehicleName }}" selected
                                                                        data-vehicle-id="{{ $vehicleId }}"
                                                                        data-vehicle-name="{{ $vehicleName }}"
                                                                        data-vehicle-type="{{ $vehicleType }}"
                                                                        data-seating-capacity="{{ $transportData['seating_capacity'] ?? '' }}"
                                                                        data-private-price="{{ $transportData['price'] ?? $totalPrice }}"
                                                                        data-shared-price="{{ $transportData['shared_price'] ?? '' }}"
                                                                        data-cost-per-hour="{{ $transportData['cost_per_hour'] ?? '' }}"
                                                                        data-sharable-cost-per-hour="{{ $transportData['sharable_cost_per_hour'] ?? '' }}"
                                                                        data-sharable="{{ $transportData['sharable'] ?? '' }}"
                                                                        data-service-type="{{ $transportData['service_type'] ?? '' }}"
                                                                        data-vehicle='@json($transportData)'>
                                                                        {{ $vehicleName }}
                                                                    </option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-user-settings-line me-1 text-secondary"></i>Service Type</label>
                                                            <select class="form-select border-2 js-local-service-type" name="vehicle_type" id="vehicle_type_{{ $order->booking_id }}" data-booking-id="{{ $order->booking_id }}">
                                                                <option value="">Select type</option>
                                                                <option value="Private" {{ strtolower($vehicleType) === 'private' ? 'selected' : '' }}>Private</option>
                                                                <option value="Shared" {{ strtolower($vehicleType) === 'shared' ? 'selected' : '' }}>Shared</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-end align-items-center gap-3 mt-3">
                                                        <div class="text-muted small" id="transport_feedback_{{ $order->booking_id }}_local_transport"></div>
                                                        <button type="submit" class="btn btn-sm btn-primary d-flex align-items-center gap-2">
                                                            <span class="spinner-border spinner-border-sm d-none" id="transport_spinner_{{ $order->booking_id }}_local_transport"></span>
                                                            <span>Save Changes</span>
                                                        </button>
                                                    </div>
                                                </form>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                            <!-- Other Transport Services Add More Section (Static) -->
                            <div class="card-footer bg-light">
                                <div class="text-center py-3">
                                    <button type="button" class="btn btn-gradient-primary btn-lg shadow-sm px-5 py-3" onclick="addMoreTransportService()" style="
                                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                        border: none;
                                        color: white;
                                        font-weight: 600;
                                        letter-spacing: 0.5px;
                                        transition: all 0.3s ease;
                                        border-radius: 8px;
                                        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
                                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.6)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.4)';">
                                        <i class="ri-add-circle-line me-2" style="font-size: 1.2em;"></i>Add More Transport Service
                                    </button>
                                </div>
                            </div>
                            
                            <!-- All Attractions Section (Unified) -->
                            @if(count($allAttractions) > 0)
                            <div class="service-section mb-3">
                                <div class="card border-danger shadow-sm">
                                    <div class="card-header bg-danger text-white">
                                        <div class="d-flex align-items-center">
                                            <span class="service-icon me-3">
                                                <i class="ri-ticket-line fs-4"></i>
                                            </span>
                                            <div>
                                                <h6 class="mb-0 fw-bold">All Attraction Tickets</h6>
                                                <small class="opacity-75">All attractions from all days in one place</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body mt-3">
                                        @foreach($allAttractions as $index => $order)
                                        @php
                                            $attractionData = $order->processed_data;
                                            $payload = [];
                                            if (is_array($attractionData)) {
                                                $payload = isset($attractionData[0]) ? $attractionData[0] : $attractionData;
                                            }
                                            $attractionName = $payload['AttractionName'] ?? 'N/A';
                                            $timeSlot = $payload['visitTime'] ?? 'N/A';
                                            $ticket = $payload['ticketName'] ?? 'N/A';
                                            $adultCount = $payload['adultCount'] ?? 0;
                                            $childCount = $payload['childCount'] ?? 0;
                                            $seniorCount = $payload['seniorCount'] ?? 0;
                                            $totalPrice = $payload['totalPrice'] ?? 0;
                                            $attractionNotes = $payload['notes'] ?? '';
                                            // Additional pax details
                                            $maleCount = $order->male_count ?? 0;
                                            $femaleCount = $order->female_count ?? 0;
                                            $infantsCount = $order->infants ?? 0;
                                            $totalPax = $adultCount + $childCount;
                                        @endphp
                                        <form class="service-item mb-3 p-3 border rounded shadow-sm bg-white attraction-edit-form" data-update-url="{{ route('edit-tour.update-attraction', $order->booking_id) }}" onsubmit="updateExistingAttraction(event, {{ $order->booking_id }})">
                                            @csrf
                                            <input type="hidden" name="type" value="attraction">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0 fw-bold text-danger"><i class="ri-ticket-line me-2"></i>Attraction Booking #{{ $index + 1 }}</h6>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeAttractionService({{ $order->booking_id }})">
                                                        <i class="ri-delete-bin-line"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold text-muted mb-1"><i class="ri-map-pin-line me-1 text-primary"></i>Attraction Name</label>
                                                    <select class="form-select border-2" name="attraction_name" id="attraction_name_{{ $order->booking_id }}" onchange="populateTicketFromAttraction(this, 'ticket_name_{{ $order->booking_id }}')" required>
                                                        <option value="">Select Attraction</option>
                                                        @php
                                                            $tourCountry = $tour->destination ?? '';
                                                            $filteredAttractions = collect($attractions ?? [])->filter(function($attraction) use ($tourCountry) {
                                                                // Check if attraction has country field directly
                                                                if (isset($attraction->country) && $attraction->country == $tourCountry) {
                                                                    return true;
                                                                }
                                                                // If no country filter available, include all attractions
                                                                return empty($tourCountry);
                                                            });
                                                        @endphp
                                                        @foreach($filteredAttractions as $attraction)
                                                            <option value="{{ $attraction->name }}" {{ $attractionName == $attraction->name ? 'selected' : '' }} 
                                                                data-attraction-id="{{ $attraction->attraction_id ?? '' }}"
                                                                data-attraction-data="{{ json_encode($attraction) }}">
                                                                {{ $attraction->name }}
                                                                @if(isset($attraction->location))
                                                                    - {{ $attraction->location }}
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                        @if($attractionName && !$filteredAttractions->pluck('name')->contains($attractionName))
                                                            <option value="{{ $attractionName }}" selected>{{ $attractionName }}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold text-muted mb-1"><i class="ri-ticket-line me-1 text-info"></i>Ticket</label>
                                                    <select class="form-select border-2" name="ticket_name" id="ticket_name_{{ $order->booking_id }}" required>
                                                        <option value="">Select Ticket</option>
                                                        @if($ticket && $ticket != 'N/A')
                                                            <option value="{{ $ticket }}" selected>{{ $ticket }}</option>
                                                        @endif
                                                    </select>
                                                    <small class="text-muted">Select an attraction to see available tickets</small>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold text-muted mb-1"><i class="ri-time-line me-1 text-warning"></i>Time Slot</label>
                                                    <select class="form-select border-2" name="visit_time" id="visit_time_{{ $order->booking_id }}" required>
                                                        <option value="">Select Time Slot</option>
                                                        @php
                                                            // Find the selected attraction to populate time slots from database
                                                            $selectedAttraction = null;
                                                            $timeSlotsFound = false;
                                                            if ($attractionName) {
                                                                $selectedAttraction = collect($filteredAttractions)->first(function($attraction) use ($attractionName) {
                                                                    return $attraction->name == $attractionName;
                                                                });
                                                            }
                                                        @endphp
                                                        @if($selectedAttraction && isset($selectedAttraction->time_slots) && is_array($selectedAttraction->time_slots) && count($selectedAttraction->time_slots) > 0)
                                                            @php $timeSlotsFound = true; @endphp
                                                            @foreach($selectedAttraction->time_slots as $slotData)
                                                                @php
                                                                    $slotValue = $slotData['slot'] ?? ($slotData['open'] ?? '');
                                                                    $slotText = $slotData['slot'] ?? ($slotData['open'] . (isset($slotData['close']) ? ' - ' . $slotData['close'] : ''));
                                                                    $isSelected = ($slotValue == $timeSlot || $slotText == $timeSlot);
                                                                @endphp
                                                                <option value="{{ $slotValue }}" {{ $isSelected ? 'selected' : '' }}>
                                                                    {{ $slotText }}
                                                                </option>
                                                            @endforeach
                                                        @elseif($selectedAttraction && $selectedAttraction->open_time && $selectedAttraction->close_time)
                                                            @php $timeSlotsFound = true; @endphp
                                                            @php
                                                                // Parse open_time from database (can be JSON array or string)
                                                                $openTimes = [];
                                                                if (is_array($selectedAttraction->open_time)) {
                                                                    $openTimes = $selectedAttraction->open_time;
                                                                } elseif (is_string($selectedAttraction->open_time)) {
                                                                    $decoded = json_decode($selectedAttraction->open_time, true);
                                                                    $openTimes = is_array($decoded) ? $decoded : [$selectedAttraction->open_time];
                                                                }
                                                                
                                                                // Parse close_time from database (can be JSON array or string)
                                                                $closeTimes = [];
                                                                if (is_array($selectedAttraction->close_time)) {
                                                                    $closeTimes = $selectedAttraction->close_time;
                                                                } elseif (is_string($selectedAttraction->close_time)) {
                                                                    $decoded = json_decode($selectedAttraction->close_time, true);
                                                                    $closeTimes = is_array($decoded) ? $decoded : [$selectedAttraction->close_time];
                                                                }
                                                            @endphp
                                                            @if(!empty($openTimes) && !empty($closeTimes))
                                                                @foreach($openTimes as $index => $openTime)
                                                                    @php
                                                                        $closeTime = $closeTimes[$index] ?? ($closeTimes[0] ?? '');
                                                                        if ($openTime && $closeTime) {
                                                                            $slotValue = $openTime . ' - ' . $closeTime;
                                                                            $isSelected = ($slotValue == $timeSlot || str_contains($slotValue, $timeSlot));
                                                                    } else {
                                                                        continue;
                                                                    }
                                                                    @endphp
                                                                    <option value="{{ $slotValue }}" {{ $isSelected ? 'selected' : '' }}>
                                                                        {{ $slotValue }}
                                                                    </option>
                                                                @endforeach
                                                            @endif
                                                        @endif
                                                        @if(!$timeSlotsFound)
                                                            <option value="" disabled>Select an attraction to see available time slots</option>
                                                        @endif
                                                        @php
                                                            // Add current time slot as option if it doesn't match any existing options
                                                            if ($timeSlot && $timeSlot != 'N/A') {
                                                                $timeSlotExists = false;
                                                                // Check if time slot already exists in options
                                                                if ($selectedAttraction && isset($selectedAttraction->time_slots) && is_array($selectedAttraction->time_slots)) {
                                                                    foreach($selectedAttraction->time_slots as $slotData) {
                                                                        $slotValue = $slotData['slot'] ?? ($slotData['open'] ?? '');
                                                                        $slotText = $slotData['slot'] ?? ($slotData['open'] . (isset($slotData['close']) ? ' - ' . $slotData['close'] : ''));
                                                                        if ($slotValue == $timeSlot || $slotText == $timeSlot) {
                                                                            $timeSlotExists = true;
                                                                            break;
                                                                        }
                                                                    }
                                                                } elseif ($selectedAttraction && $selectedAttraction->open_time && $selectedAttraction->close_time) {
                                                                    // Parse open_time and close_time from database
                                                                    $openTimes = [];
                                                                    if (is_array($selectedAttraction->open_time)) {
                                                                        $openTimes = $selectedAttraction->open_time;
                                                                    } elseif (is_string($selectedAttraction->open_time)) {
                                                                        $decoded = json_decode($selectedAttraction->open_time, true);
                                                                        $openTimes = is_array($decoded) ? $decoded : [$selectedAttraction->open_time];
                                                                    }
                                                                    
                                                                    $closeTimes = [];
                                                                    if (is_array($selectedAttraction->close_time)) {
                                                                        $closeTimes = $selectedAttraction->close_time;
                                                                    } elseif (is_string($selectedAttraction->close_time)) {
                                                                        $decoded = json_decode($selectedAttraction->close_time, true);
                                                                        $closeTimes = is_array($decoded) ? $decoded : [$selectedAttraction->close_time];
                                                                    }
                                                                    
                                                                    foreach($openTimes as $index => $openTime) {
                                                                        $closeTime = $closeTimes[$index] ?? ($closeTimes[0] ?? '');
                                                                        if ($openTime && $closeTime) {
                                                                            $slotValue = $openTime . ' - ' . $closeTime;
                                                                            if ($slotValue == $timeSlot || str_contains($slotValue, $timeSlot)) {
                                                                                $timeSlotExists = true;
                                                                                break;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                // If current time slot doesn't exist in database options, add it to preserve the value
                                                                if (!$timeSlotExists) {
                                                                    echo '<option value="' . htmlspecialchars($timeSlot) . '" selected>' . htmlspecialchars($timeSlot) . '</option>';
                                                                }
                                                            }
                                                        @endphp
                                                    </select>
                                                    <small class="text-muted">Available time slots from database</small>
                                                </div>
                                                
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold text-muted mb-1"><i class="ri-user-line me-1 text-secondary"></i>Adults</label>
                                                    <input type="number" class="form-control border-2" name="adult_count" min="0" value="{{ $adultCount }}" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold text-muted mb-1"><i class="ri-user-smile-line me-1 text-secondary"></i>Children</label>
                                                    <input type="number" class="form-control border-2" name="child_count" min="0" value="{{ $childCount }}" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold text-muted mb-1"><i class="ri-user-heart-line me-1 text-secondary"></i>Seniors</label>
                                                    <input type="number" class="form-control border-2" name="senior_count" min="0" value="{{ $seniorCount }}" required>
                                                </div>
                                                
                                                <!-- Additional Pax Details -->
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold text-muted mb-1"><i class="ri-user-heart-line me-1 text-warning"></i>Infants</label>
                                                    <input type="number" class="form-control border-2" name="infants" min="0" value="{{ $infantsCount }}">
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-end align-items-center gap-3 mt-3">
                                                <div class="text-muted small" id="attraction_feedback_{{ $order->booking_id }}"></div>
                                                <button type="submit" class="btn btn-sm btn-primary d-flex align-items-center gap-2">
                                                    <span class="spinner-border spinner-border-sm d-none" id="attraction_spinner_{{ $order->booking_id }}"></span>
                                                    <span>Save Changes</span>
                                                </button>
                                            </div>
                                        </form>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif
                            <!-- All Attractions Add More Section (Static) -->
                            <div class="card-footer bg-light">
                                <div class="text-center py-3">
                                    <button type="button" class="btn btn-gradient-primary btn-lg shadow-sm px-5 py-3" onclick="addAttractionService()" style="
                                        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
                                        border: none;
                                        color: white;
                                        font-weight: 600;
                                        letter-spacing: 0.5px;
                                        transition: all 0.3s ease;
                                        border-radius: 8px;
                                        box-shadow: 0 4px 15px rgba(250, 112, 154, 0.4);
                                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(250, 112, 154, 0.6)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(250, 112, 154, 0.4)';">
                                        <i class="ri-add-circle-line me-2" style="font-size: 1.2em;"></i>Add More Attractions
                                    </button>
                                </div>
                            </div>

                            <!-- All Guides Section (Unified) -->
                            @if(count($allGuides) > 0)
                            <div class="service-section mb-3">
                                <div class="card border-info shadow-sm">
                                    <div class="card-header bg-info text-white">
                                        <div class="d-flex align-items-center">
                                            <span class="service-icon me-3">
                                                <i class="ri-user-star-line fs-4"></i>
                                            </span>
                                            <div>
                                                <h6 class="mb-0 fw-bold">All Tour Guide Services</h6>
                                                <small class="opacity-75">All guides from all days in one place</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body mt-3">
                                        @foreach($allGuides as $index => $order)
                                        @php
                                            $guideData = $order->processed_data;
                                            $payload = [];
                                            if (is_array($guideData)) {
                                                $payload = isset($guideData[0]) ? $guideData[0] : $guideData;
                                            }
                                            $guideName = $payload['guide_name'] ?? 'N/A';
                                            $packageHours = $payload['hours'] ?? '';
                                            $pickupTime = $payload['entrytime'] ?? '';
                                            $guestSummary = $payload['fullName'] ?? '';
                                            $guideNotes = $payload['notes'] ?? '';
                                            // Convert pickup time to AM/PM format for display
                                            $pickupTimeAMPM = '';
                                            if ($pickupTime) {
                                                try {
                                                    $timeObj = \Carbon\Carbon::createFromFormat('H:i', $pickupTime);
                                                    $pickupTimeAMPM = $timeObj->format('h:i A');
                                                } catch (\Exception $e) {
                                                    try {
                                                        $timeObj = \Carbon\Carbon::parse($pickupTime);
                                                        $pickupTimeAMPM = $timeObj->format('h:i A');
                                                    } catch (\Exception $e2) {
                                                        $pickupTimeAMPM = $pickupTime; // Keep original if parsing fails
                                                    }
                                                }
                                            }
                                        @endphp
                                        <form class="service-item mb-3 p-3 border rounded shadow-sm bg-white guide-edit-form" data-update-url="{{ route('edit-tour.update-guide', $order->booking_id) }}" onsubmit="updateExistingGuide(event, {{ $order->booking_id }})">
                                            @csrf
                                            <input type="hidden" name="type" value="guide">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0 fw-bold text-info"><i class="ri-user-star-line me-2"></i>Tour Guide Booking #{{ $index + 1 }}</h6>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeGuideService({{ $order->booking_id }})">
                                                        <i class="ri-delete-bin-line"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold text-muted mb-1"><i class="ri-user-star-line me-1 text-primary"></i>Guide Name</label>
                                                    <select class="form-select border-2" name="guide_name" id="guide_name_{{ $order->booking_id }}" required>
                                                        <option value="">Select Guide</option>
                                                        @php
                                                            $tourCountry = $tour->destination ?? '';
                                                            $filteredGuides = collect($guides ?? [])->filter(function($guide) use ($tourCountry) {
                                                                // Check if guide has country field directly
                                                                if (isset($guide->country) && $guide->country == $tourCountry) {
                                                                    return true;
                                                                }
                                                                // Check if guide has city relationship with country
                                                                if (isset($guide->city) && is_object($guide->city) && isset($guide->city->country)) {
                                                                    return $guide->city->country == $tourCountry;
                                                                }
                                                                // If no country filter available, include all guides
                                                                return empty($tourCountry);
                                                            });
                                                        @endphp
                                                        @foreach($filteredGuides as $guide)
                                                            @php
                                                                $guideLanguages = [];
                                                                if (isset($guide->languages) && is_iterable($guide->languages)) {
                                                                    foreach ($guide->languages as $lang) {
                                                                        if (is_object($lang) && isset($lang->language)) {
                                                                            $guideLanguages[] = $lang->language;
                                                                        } elseif (is_array($lang) && isset($lang['language'])) {
                                                                            $guideLanguages[] = $lang['language'];
                                                                        }
                                                                    }
                                                                }
                                                                $languagesText = !empty($guideLanguages) ? ' - ' . implode(', ', $guideLanguages) : '';
                                                            @endphp
                                                            <option value="{{ $guide->name }}" {{ $guideName == $guide->name ? 'selected' : '' }} 
                                                                data-guide-id="{{ $guide->guide_id ?? '' }}"
                                                                data-guide-data="{{ json_encode($guide) }}">
                                                                {{ $guide->name }}{{ $languagesText }}
                                                            </option>
                                                        @endforeach
                                                        @if($guideName && !$filteredGuides->pluck('name')->contains($guideName))
                                                            <option value="{{ $guideName }}" selected>{{ $guideName }}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold text-muted mb-1"><i class="ri-time-line me-1 text-warning"></i>Package (Hours)</label>
                                                    <input type="text" class="form-control border-2" name="package_hours" value="{{ $packageHours }}" placeholder="e.g. 4">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold text-muted mb-1"><i class="ri-time-line me-1 text-info"></i>Pickup Time</label>
                                                    <select class="form-select border-2" name="pickup_time" id="guide_pickup_time_{{ $order->booking_id }}" required>
                                                        <option value="">Select Guide First</option>
                                                        @if($pickupTimeAMPM)
                                                            <option value="{{ $pickupTimeAMPM }}" selected>{{ $pickupTimeAMPM }}</option>
                                                        @endif
                                                    </select>
                                                    <small class="text-muted">Available times from selected guide</small>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-end align-items-center gap-3 mt-3">
                                                <div class="text-muted small" id="guide_feedback_{{ $order->booking_id }}"></div>
                                                <button type="submit" class="btn btn-sm btn-primary d-flex align-items-center gap-2">
                                                    <span class="spinner-border spinner-border-sm d-none" id="guide_spinner_{{ $order->booking_id }}"></span>
                                                    <span>Save Changes</span>
                                                </button>
                                            </div>
                                        </form>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif
                            <!-- All Guides Add More Section (Static) -->
                            <div class="card-footer bg-light">
                                <div class="text-center py-3">
                                    <button type="button" class="btn btn-gradient-primary btn-lg shadow-sm px-5 py-3" onclick="addGuideService()" style="
                                        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
                                        border: none;
                                        color: white;
                                        font-weight: 600;
                                        letter-spacing: 0.5px;
                                        transition: all 0.3s ease;
                                        border-radius: 8px;
                                        box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4);
                                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(79, 172, 254, 0.6)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(79, 172, 254, 0.4)';">
                                        <i class="ri-add-circle-line me-2" style="font-size: 1.2em;"></i>Add More Guides
                                    </button>
                                </div>
                            </div>

                            <!-- Departure Transport Services Section -->
                            @if(isset($dayOrdersByType['exit_port']))
                                <div class="service-section mb-3">
                                    <div class="card border-danger shadow-sm">
                                        <div class="card-header bg-danger text-white">
                                            <div class="d-flex align-items-center">
                                                <span class="service-icon me-3">
                                                    <i class="ri-logout-circle-line fs-4"></i>
                                                </span>
                                                <div>
                                                    <h6 class="mb-0 fw-bold">Departure Transport Services</h6>
                                                    <small class="opacity-75">Edit exit port transfers</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body mt-3">
                                            @foreach($dayOrdersByType['exit_port'] as $index => $order)
                                                @php
                                                    $transportData = is_array($order->processed_data) ? $order->processed_data : json_decode($order->processed_data, true);
                                                    if (isset($transportData[0])) {
                                                        $transportData = $transportData[0];
                                                    }
                                                    $cityValue = $transportData['city'] ?? '';
                                                    $pickupLocation = $transportData['exitpickup'] ?? $transportData['entrypickup'] ?? '';
                                                    $dropoffLocation = $transportData['exitdropoff'] ?? $transportData['entrydropoff'] ?? '';
                                                    $pickupTime = $transportData['exitpickupdate'] ?? $transportData['entrytime'] ?? '';
                                                    $vehicleName = $transportData['vehicles_name'] ?? '';
                                                    $vehicleType = $transportData['type'] ?? '';
                                                    $passengers = $transportData['passengers'] ?? '';
                                                    $availableVehicles = $vehicles ?? collect();
                                                @endphp
                                                <form class="service-item mb-3 p-3 border rounded shadow-sm bg-white transport-edit-form" data-form-type="exit_port" data-update-url="{{ route('edit-tour.update-transport', $order->booking_id) }}" onsubmit="updateExistingTransport(event, {{ $order->booking_id }})">
                                                    @csrf
                                                    <input type="hidden" name="type" value="exit_port">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <h6 class="mb-0 fw-bold text-danger"><i class="ri-logout-circle-line me-2"></i>Departure Transfer #{{ $index + 1 }}</h6>
                                                        <div class="d-flex gap-2">
                                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTransportService({{ $order->booking_id }})">
                                                                <i class="ri-delete-bin-line"></i> Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="row g-3 align-items-end">
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-map-pin-line me-1 text-success"></i>City</label>
                                                            <select class="form-select border-2" name="city">
                                                                <option value="">Select city</option>
                                                                @foreach($cities as $city)
                                                                    <option value="{{ $city->name }}" {{ $city->name == $cityValue ? 'selected' : '' }}>{{ $city->name }}</option>
                                                                @endforeach
                                                                @if($cityValue && !$cities->contains('name', $cityValue))
                                                                    <option value="{{ $cityValue }}" selected>{{ $cityValue }}</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-map-pin-line me-1 text-success"></i>Pick Up Location</label>
                                                            <select class="form-select border-2" name="pickup_location">
                                                                <option value="">Select pickup location</option>
                                                                @foreach($hotels as $hotel)
                                                                    <option value="{{ $hotel->name }}" {{ $hotel->name == $pickupLocation ? 'selected' : '' }}>
                                                                        {{ $hotel->name }}
                                                                    </option>
                                                                @endforeach
                                                                @if($pickupLocation && !$hotels->contains('name', $pickupLocation))
                                                                    <option value="{{ $pickupLocation }}" selected>{{ $pickupLocation }}</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-map-pin-line me-1 text-danger"></i>Drop Off Location</label>
                                                            <select class="form-select border-2" name="dropoff_location">
                                                                <option value="">Select dropoff port</option>
                                                                @foreach($ports as $port)
                                                                    <option value="{{ $port->port_name }}" {{ $port->port_name == $dropoffLocation ? 'selected' : '' }}>
                                                                        {{ $port->port_name }}
                                                                    </option>
                                                                @endforeach
                                                                @if($dropoffLocation && !$ports->contains('port_name', $dropoffLocation))
                                                                    <option value="{{ $dropoffLocation }}" selected>{{ $dropoffLocation }}</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-time-line me-1 text-warning"></i>Departure Time</label>
                                                            @php
                                                                $departureTimeAMPM = '';
                                                                if ($pickupTime) {
                                                                    try {
                                                                        $timeObj = \Carbon\Carbon::createFromFormat('H:i', $pickupTime);
                                                                        $departureTimeAMPM = $timeObj->format('h:i A');
                                                                    } catch (\Exception $e) {
                                                                        try {
                                                                            $timeObj = \Carbon\Carbon::parse($pickupTime);
                                                                            $departureTimeAMPM = $timeObj->format('h:i A');
                                                                        } catch (\Exception $e2) {
                                                                            $departureTimeAMPM = $pickupTime; // Keep original if parsing fails
                                                                        }
                                                                    }
                                                                }
                                                            @endphp
                                                            <select class="form-select border-2" name="pickup_time" id="departure_time_{{ $order->booking_id }}" required>
                                                                <option value="">Select Time</option>
                                                                @php
                                                                    // Generate time slots from 12:00 AM to 11:30 PM in 30-minute intervals
                                                                    $timeSlots = [];
                                                                    for ($hour = 0; $hour < 24; $hour++) {
                                                                        for ($minute = 0; $minute < 60; $minute += 30) {
                                                                            $timeObj = \Carbon\Carbon::createFromTime($hour, $minute, 0);
                                                                            $timeSlots[] = $timeObj->format('h:i A');
                                                                        }
                                                                    }
                                                                @endphp
                                                                @foreach($timeSlots as $timeSlot)
                                                                    <option value="{{ $timeSlot }}" {{ $departureTimeAMPM == $timeSlot ? 'selected' : '' }}>{{ $timeSlot }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-semibold text-muted mb-1"><i class="ri-car-line me-1 text-info"></i>Vehicle</label>
                                                            @php $vehicleMatched = false; @endphp
                                                            <select class="form-select border-2" name="vehicle_name">
                                                                <option value="">{{ $vehicleName ? 'Select vehicle' : 'Select vehicle' }}</option>
                                                                @foreach($availableVehicles as $vehicleOption)
                                                                    @php
                                                                        $vehicleDisplayName = $vehicleOption->vehicle_name ?? $vehicleOption->vehicle_id;
                                                                        $isSelected = $vehicleDisplayName && strcasecmp($vehicleDisplayName, $vehicleName ?? '') === 0;
                                                                        $vehicleMatched = $vehicleMatched || $isSelected;
                                                                    @endphp
                                                                    <option value="{{ $vehicleDisplayName }}" {{ $isSelected ? 'selected' : '' }}>
                                                                        {{ $vehicleDisplayName }}
                                                                        @if(!empty($vehicleOption->vehicle_type))
                                                                            ({{ $vehicleOption->vehicle_type }})
                                                                        @endif
                                                                    </option>
                                                                @endforeach
                                                                @if($vehicleName && !$vehicleMatched)
                                                                    <option value="{{ $vehicleName }}" selected>{{ $vehicleName }}</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label fw-semibold text-muted mb-1">Service Type</label>
                                                            <select class="form-select border-2" name="vehicle_type">
                                                                <option value="">Select type</option>
                                                                <option value="Private" {{ strtolower($vehicleType) === 'private' ? 'selected' : '' }}>Private</option>
                                                                <option value="Shared" {{ strtolower($vehicleType) === 'shared' ? 'selected' : '' }}>Shared</option>
                                                            </select>
                                                        </div>
                                                        <!-- <div class="col-md-2">
                                                            <label class="form-label fw-semibold text-muted mb-1">Passengers</label>
                                                            <input type="number" class="form-control border-2" name="passenger_count" min="1" value="{{ $passengers }}" placeholder="Count">
                                                        </div> -->
                                                    </div>
                                                    <div class="d-flex justify-content-end align-items-center gap-3 mt-3">
                                                        <div class="text-muted small" id="transport_feedback_{{ $order->booking_id }}_exit_port"></div>
                                                        <button type="submit" class="btn btn-sm btn-primary d-flex align-items-center gap-2">
                                                            <span class="spinner-border spinner-border-sm d-none" id="transport_spinner_{{ $order->booking_id }}_exit_port"></span>
                                                            <span>Save Changes</span>
                                                        </button>
                                                    </div>
                                                </form>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif
                            
                            <!-- Departure Transport Services Add More Section (Static) -->
                            <div class="card-footer bg-light">
                                <div class="text-center py-3">
                                    <button type="button" class="btn btn-gradient-primary btn-lg shadow-sm px-5 py-3" onclick="addDepartureService()" style="
                                        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                                        border: none;
                                        color: white;
                                        font-weight: 600;
                                        letter-spacing: 0.5px;
                                        transition: all 0.3s ease;
                                        border-radius: 8px;
                                        box-shadow: 0 4px 15px rgba(245, 87, 108, 0.4);
                                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(245, 87, 108, 0.6)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(245, 87, 108, 0.4)';">
                                        <i class="ri-add-circle-line me-2" style="font-size: 1.2em;"></i>Add More Departure Services
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>

<!-- Guide Selection Modal -->
<div class="modal fade" id="guideSelectionModal" tabindex="-1" aria-labelledby="guideSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-info text-white">
                <h5 class="modal-title" id="guideSelectionModalLabel">
                    <i class="ri-user-star-line me-2"></i>Select Tour Guide
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Tour Info Display -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="ri-calendar-line me-2 text-primary"></i>
                            <span class="fw-semibold">Tour Dates: <span id="modal_guide_tour_dates" class="text-primary"></span></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="ri-map-pin-line me-2 text-primary"></i>
                            <span class="fw-semibold">Destination: <span id="modal_guide_destination" class="text-primary"></span></span>
                        </div>
                    </div>
                </div>

                <!-- Guide Selection Form -->
                <form id="guideSelectionForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label fw-semibold text-muted mb-2">
                                    <i class="ri-map-pin-line text-success me-2"></i>City
                                </label>
                                <div class="position-relative">
                                    <select class="form-select border-2" id="modal_guide_city_select" name="city" style="padding-left: 45px;" onchange="loadGuidesForCity(this.value, this.dataset.country)">
                                        <option value="">Select city</option>
                                        @foreach($cities as $city)
                                        <option value="{{ $city->name }}" data-city="{{ json_encode($city) }}" data-country="{{ $city->country }}">{{ $city->name }}</option>
                                        @endforeach
                                    </select>
                                    <i style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                </div>
                            </div>
                        </div>
                        <!-- Guide Selection -->
                        <div class="col-md-6">
                            <label for="modal_guide_select" class="form-label fw-semibold">
                                <i class="ri-user-star-line me-1"></i>Select Guide
                            </label>
                            <select class="form-select" id="modal_guide_select" name="guide_id" required>
                                <option value="">Search Guide</option>
                            </select>
                            <div class="form-text">
                                <i class="ri-information-line text-info me-1"></i>
                                <span id="guide_count">0</span> guides available in <span id="modal_guide_city"></span>
                            </div>
                        </div>

                        <!-- Service Date Selection -->
                        <div class="col-md-6">
                            <label for="modal_guide_service_date" class="form-label fw-semibold">
                                <i class="ri-calendar-line me-1"></i>Service Date
                            </label>
                            <input type="date" class="form-control" id="modal_guide_service_date" name="service_date" required>
                            <small class="text-muted">Select the date for guide service</small>
                        </div>

                        <!-- Duration Selection -->
                        <div class="col-md-6">
                            <label for="modal_guide_duration" class="form-label fw-semibold">
                                <i class="ri-time-line me-1"></i>Select Duration
                            </label>
                            <select class="form-select" id="modal_guide_duration" name="duration" required>
                                <option value="">Select Duration</option>
                                <option value="half_day">Half Day (4 hours)</option>
                                <option value="full_day">Full Day (8 hours)</option>
                                <option value="custom">Custom Hours</option>
                            </select>
                        </div>

                        <!-- Custom Hours (shown when custom duration selected) -->
                        <div class="col-md-6" id="custom_hours_container" style="display: none;">
                            <label for="modal_guide_custom_hours" class="form-label fw-semibold">
                                <i class="ri-clock-line me-1"></i>Custom Hours
                            </label>
                            <input type="number" class="form-control" id="modal_guide_custom_hours" name="custom_hours" min="1" max="24" placeholder="Enter hours (1-24)">
                        </div>

                        <!-- Pickup Time -->
                        <div class="col-md-6">
                            <label for="modal_guide_pickup_time" class="form-label fw-semibold">
                                <i class="ri-time-line me-1"></i>Pickup Time
                            </label>
                            <input type="time" class="form-control" id="modal_guide_pickup_time" name="pickup_time" required>
                        </div>

                        <!-- Guide Details Display -->
                        <div class="col-12" id="guide_details_container" style="display: none;">
                            <div class="card border-info bg-light">
                                <div class="card-body p-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <img id="selected_guide_image" src="" alt="Guide" class="rounded-circle" style="width: 60px; height: 60px; object-fit: cover;">
                                        </div>
                                        <div class="col-md-7">
                                            <h6 id="selected_guide_name" class="mb-1 fw-bold"></h6>
                                            <p id="selected_guide_specialty" class="mb-1 text-muted small"></p>
                                            <p id="selected_guide_experience" class="mb-0 text-muted small"></p>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <div class="guide-rating mb-1">
                                                <i class="ri-star-fill text-warning"></i>
                                                <span id="selected_guide_rating" class="fw-semibold"></span>
                                            </div>
                                            <div class="guide-rate">
                                                <span id="selected_guide_rate" class="fw-bold text-primary"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm_guide_btn" disabled>
                    <i class="ri-check-line me-1"></i>Confirm Guide Selection
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hotel Booking Modal -->
<div class="modal fade" id="hotelBookingModal" tabindex="-1" aria-labelledby="hotelBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title" id="hotelBookingModalLabel">
                    <i class="ri-hotel-line me-2"></i>Let's Book Your Hotels!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="hotelBookingForm">
                    @csrf
                    <input type="hidden" id="modal_tour_id" name="tour_id">
                    <input type="hidden" id="modal_user_country" name="user_country">
                    <input type="hidden" id="modal_city" name="city">
                    
                    <!-- Tour Info Display -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="ri-calendar-line me-2 text-primary"></i>
                                <span class="fw-semibold">Tour Dates: <span id="modal_tour_dates" class="text-primary"></span></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="ri-map-pin-line me-2 text-primary"></i>
                                <span class="fw-semibold">Destination: <span id="modal_destination" class="text-primary"></span></span>
                            </div>
                        </div>
                    </div>

                    <!-- City Selection -->
                    <div class="mb-3">
                        <label for="modal_city_select" class="form-label fw-semibold">
                            <i class="ri-map-pin-line me-1"></i>City
                        </label>
                        <select class="form-select" id="modal_city_select" name="city" onchange="loadHotelsForSelectedCity(this.value)">
                            <option value="">Select City</option>
                            @foreach($cities as $city)
                                @if($city->country == $tour->destination)
                                    <option value="{{ $city->name }}">{{ $city->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <div class="form-text">
                            <i class="ri-check-line text-success me-1"></i>
                            <span id="hotel_count">0</span> hotels found in <span id="modal_city_display2">No City</span>
                        </div>
                    </div>

                    <!-- Hotel Selection -->
                    <div class="mb-3">
                        <label for="hotel_select" class="form-label fw-semibold">
                            <i class="ri-building-line me-1"></i>Select Hotel
                        </label>
                        <select class="form-select" id="hotel_select" name="hotel_id" onchange="loadRoomsForSelectedHotel(this.value)" disabled>
                            <option value="">Select city first to load hotels</option>
                        </select>
                        <small class="text-muted" id="hotel_loading_status">
                            <span id="hotel_count_display">0</span> hotels found
                        </small>
                    </div>

                    <!-- Room Details -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="room_type" class="form-label fw-semibold">Room Type</label>
                            <select class="form-select" id="room_type" name="room_type" onchange="loadBedsForSelectedRoom(this.value); updateHotelModalPrice();" disabled>
                                <option value="">Select hotel first</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="bed_type" class="form-label fw-semibold">Bed Type</label>
                            <select class="form-select" id="bed_type" name="bed_type" onchange="updateBedPricingAndMealPlans(); updateHotelModalPrice();" disabled>
                                <option value="">Select room type first</option>
                            </select>
                            <div class="small text-success mt-1">
                                <span id="bed_occupancy_info">Max Occupancy: 2</span>
                            </div>
                        </div>
                        
                        <!-- Number of Persons -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Number of Persons</label>
                            <select class="form-select" id="person_count_select" name="person_count" data-no-select2="true" onchange="selectPersonCount(this.value); updateHotelModalPrice();">
                                <!-- Options will be generated dynamically based on room max_occupancy -->
                            </select>
                            <small class="text-muted">Max Occupancy: 2</small>
                        </div>

                        <div class="col-md-3">
                            <label for="meal_plan" class="form-label fw-semibold">Meal Plan</label>
                            <select class="form-select" id="meal_plan" name="meal_plan" onchange="updateMealPricing()" disabled>
                                <option value="">Select bed type first</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Number of Rooms and Price -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="number_of_rooms_modal" class="form-label fw-semibold">Number of Rooms</label>
                            <input type="number" class="form-control" id="number_of_rooms_modal" name="number_of_rooms" min="1" value="1" placeholder="e.g. 1" onchange="updateHotelModalPrice();">
                        </div>
                        <div class="col-md-3">
                            <label for="total_price_modal" class="form-label fw-semibold">
                                <i class="ri-money-dollar-circle-line me-1 text-success"></i>Total Price
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="total_price_modal" name="total_price" step="0.01" min="0" value="0.00" placeholder="0.00">
                            </div>
                            <small class="text-muted">Price per room type and number of rooms</small>
                        </div>
                    </div>

                    <!-- Date Range Selection -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="ri-calendar-line me-1"></i>Select Hotel Nights
                        </label>
                        <p class="form-text mb-2">(Choose nights for this hotel - consecutive nights will be automatically selected)</p>
                        
                        <!-- Night Selection Guide -->
                        <div class="d-flex gap-3 mb-3">
                                <div class="d-flex align-items-center">
                                <div class="bg-success text-white rounded me-2" style="width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                    <i class="ri-check-line fs-6"></i>
                                </div>
                                    <small>Manually Selected</small>
                                </div>
                                <div class="d-flex align-items-center">
                                <div class="bg-warning text-dark rounded me-2" style="width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                    <i class="ri-flashlight-line fs-6"></i>
                                </div>
                                    <small>Auto-Required (for consecutive nights)</small>
                            </div>
                        </div>
                        
                        <!-- Date Range Input -->
                        <div class="row">
                            <div class="col-md-6">
                                <label for="check_in_date" class="form-label">Check-in Date</label>
                                <input type="date" class="form-control" id="check_in_date" name="check_in_date" required onchange="updateHotelModalPrice();">
                            </div>
                            <div class="col-md-6">
                                <label for="check_out_date" class="form-label">Check-out Date</label>
                                <input type="date" class="form-control" id="check_out_date" name="check_out_date" required onchange="updateHotelModalPrice();">
                            </div>
                        </div>
                        
                        <!-- Selected Nights Display -->
                        <div class="mt-3">
                            <div id="selected_nights_display" class="d-none">
                                <label class="form-label fw-semibold">Selected Nights:</label>
                                <div id="nights_list" class="d-flex flex-wrap gap-2"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Information Alerts -->
                    <div class="alert alert-info" id="no_nights_alert" style="z-index: 1050; position: relative;">
                        <i class="ri-information-line me-2"></i>
                        No nights selected. Click on the nights above to select hotel stay.
                    </div>
                    <div class="alert alert-info" id="no_hotels_alert" style="z-index: 1050; position: relative;">
                        <i class="ri-information-line me-2"></i>
                        No hotels selected yet. Choose your hotels above.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="proceed_hotel_btn" onclick="proceedWithHotelBooking()" disabled>
                    <i class="ri-check-line me-1"></i>Let's Book Your Hotels!
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Restaurant Selection Modal -->
<div class="modal fade" id="restaurantSelectionModal" tabindex="-1" aria-labelledby="restaurantSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-success text-white">
                <h5 class="modal-title" id="restaurantSelectionModalLabel">
                    <i class="ri-restaurant-2-line me-2"></i>Select Restaurant & Dining Options
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Tour Info Display -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="ri-calendar-line me-2 text-primary"></i>
                            <span class="fw-semibold">Tour Dates: <span id="modal_restaurant_tour_dates" class="text-primary"></span></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="ri-map-pin-line me-2 text-primary"></i>
                            <span class="fw-semibold">Destination: <span id="modal_restaurant_destination" class="text-primary"></span></span>
                        </div>
                    </div>
                </div>

                <!-- Restaurant Selection Form -->
                <form id="restaurantSelectionForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label fw-semibold text-muted mb-2">
                                    <i class="ri-map-pin-line text-success me-2"></i>City
                                </label>
                                <div class="position-relative">
                                    <select class="form-select border-2" id="modal_restaurant_city_select" name="city" style="padding-left: 45px;" onchange="loadRestaurantsForCity(this.value, this.dataset.country)">
                                        <option value="">Select city</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->name }}" data-city="{{ json_encode($city) }}" data-country="{{ $city->country }}">{{ $city->name }}</option>
                                        @endforeach
                                    </select>
                                    <i style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                </div>
                            </div>
                        </div>
                        <!-- Restaurant Selection -->
                        <div class="col-md-6">
                            <label for="modal_restaurant_select" class="form-label fw-semibold">
                                <i class="ri-restaurant-2-line me-1"></i>Select Restaurant
                            </label>
                            <select class="form-select" id="modal_restaurant_select" name="restaurant_id" required>
                                <option value="">Search Restaurant</option>
                            </select>
                            <div class="form-text">
                                <i class="ri-information-line text-info me-1"></i>
                                <span id="restaurant_count">0</span> restaurants available in <span id="modal_restaurant_city"></span>
                            </div>
                        </div>

                        <!-- Guest Selector -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Select Guests</label>
                            <div class="guest-selector">
                                <div class="guest-display p-2 border rounded bg-light">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="guest-info">
                                            <span id="modal_restaurant_guest_summary" class="text-muted small">
                                                1 adults (1 male, 0 female), 0 children -0 infants
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openModalGuestSelector()">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                    </div>
                                    <div class="guest-badges mt-1">
                                        <span class="badge bg-primary">1</span>
                                        <span class="badge bg-success">0</span>
                                        <span class="badge bg-warning text-dark">0</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden fields for restaurant pricing -->
                            <input type="hidden" name="modal_restaurant_total_price" id="modal_restaurant_total_price" value="0">
                            <input type="hidden" name="modal_restaurant_meal_id" id="modal_restaurant_meal_id" value="">
                            <input type="hidden" name="modal_restaurant_dish_name" id="modal_restaurant_dish_name" value="">
                        </div>

                        <!-- Dining Date Selection -->
                        <div class="col-md-6">
                            <label for="modal_restaurant_dining_date" class="form-label fw-semibold">
                                <i class="ri-calendar-line me-1"></i>Dining Date
                            </label>
                            <input type="date" class="form-control" id="modal_restaurant_dining_date" name="dining_date" required>
                            <small class="text-muted">Select the date for dining</small>
                        </div>

                        <!-- Meal Type Selection -->
                        <div class="col-md-6">
                            <label for="modal_restaurant_meal_type" class="form-label fw-semibold">
                                <i class="ri-time-line me-1"></i>Meal Type
                            </label>
                            <select class="form-select" id="modal_restaurant_meal_type" name="meal_type" required>
                                <option value="">Select Restaurant First</option>
                            </select>
                            <small id="meal-price-section" class="text-muted"></small>
                        </div>

                        <!-- Select Dish -->
                        <div class="col-md-6">
                            <label for="modal_restaurant_dish" class="form-label fw-semibold">Select Dish</label>
                            <select class="form-select" name="modal_restaurant_dish" id="modal_restaurant_dish">
                                <option value="">Select Dish</option>
                            </select>
                            <small class="text-muted">Buffet or Set Menu options</small>
                        </div>

                        <!-- Time Slot -->
                        <div class="col-md-6">
                            <label for="modal_restaurant_time_slot" class="form-label fw-semibold">Time Slot</label>
                            <select class="form-select" name="modal_restaurant_time_slot" id="modal_restaurant_time_slot">
                                <option value="">Select Time Slot</option>
                            </select>
                            <small class="text-muted">Available time slots</small>
                        </div>

                        <!-- Restaurant Details Display -->
                        <div class="col-12" id="restaurant_details_container" style="display: none;">
                            <div class="card border-success bg-light">
                                <div class="card-body p-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <img id="selected_restaurant_image" src="" alt="Restaurant" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                        </div>
                                        <div class="col-md-7">
                                            <h6 id="selected_restaurant_name" class="mb-1 fw-bold"></h6>
                                            <p id="selected_restaurant_cuisine" class="mb-1 text-muted small"></p>
                                            <p id="selected_restaurant_location" class="mb-0 text-muted small"></p>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <div class="restaurant-rating mb-1">
                                                <i class="ri-star-fill text-warning"></i>
                                                <span id="selected_restaurant_rating" class="fw-semibold"></span>
                                            </div>
                                            <div class="restaurant-price-range">
                                                <span id="selected_restaurant_price_range" class="fw-bold text-success"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirm_restaurant_btn">
                    <i class="ri-check-line me-1"></i>Confirm Restaurant Selection
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Select Tour Guests Modal -->
<div class="modal fade" id="tourGuestSelectorModal" tabindex="-1" aria-labelledby="tourGuestSelectorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="tourGuestSelectorModalLabel">Select Tour Guests</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <!-- Left Section: Adults -->
                    <div class="col-md-6">
                        <div class="border rounded" style="border-color: #6366f1 !important; border-width: 2px !important;">
                            <div class="p-3 rounded-top" style="background-color: #4f46e5; color: white;">
                                <h6 class="mb-0 fw-semibold">
                                    <i class="ri-user-line me-2"></i>Adults
                                </h6>
                            </div>
                            <div class="p-3">
                                <!-- Male Sub-section -->
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="ri-user-line me-2" style="color: #6366f1; font-size: 1.2rem;"></i>
                                        <label class="form-label mb-0 fw-semibold">Male</label>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <button type="button" class="btn btn-sm" onclick="decrementTourCount('tour_male_count')" style="background-color: #e0e7ff; color: #6366f1; width: 40px; height: 40px; border: none; border-radius: 4px; font-weight: bold;">
                                            <i class="ri-subtract-line"></i>
                                        </button>
                                        <input type="number" class="form-control text-center mx-2" id="tour_male_count" name="tour_male_count" value="{{ isset($tour->male_count) ? $tour->male_count : ($tour->adult ?? 1) }}" min="0" max="20" readonly style="width: 60px; height: 40px; border: 1px solid #ddd; background-color: white;">
                                        <button type="button" class="btn btn-sm" onclick="incrementTourCount('tour_male_count')" style="background-color: #6366f1; color: white; width: 40px; height: 40px; border: none; border-radius: 4px; font-weight: bold;">
                                            <i class="ri-add-line"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Female Sub-section -->
                                <div>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="ri-user-line me-2" style="color: #ef4444; font-size: 1.2rem;"></i>
                                        <label class="form-label mb-0 fw-semibold">Female</label>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <button type="button" class="btn btn-sm" onclick="decrementTourCount('tour_female_count')" style="background-color: #fee2e2; color: #ef4444; width: 40px; height: 40px; border: none; border-radius: 4px; font-weight: bold;">
                                            <i class="ri-subtract-line"></i>
                                        </button>
                                        <input type="number" class="form-control text-center mx-2" id="tour_female_count" name="tour_female_count" value="{{ isset($tour->female_count) ? $tour->female_count : 0 }}" min="0" max="20" readonly style="width: 60px; height: 40px; border: 1px solid #ddd; background-color: white;">
                                        <button type="button" class="btn btn-sm" onclick="incrementTourCount('tour_female_count')" style="background-color: #ef4444; color: white; width: 40px; height: 40px; border: none; border-radius: 4px; font-weight: bold;">
                                            <i class="ri-add-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Section: Children & Infants -->
                    <div class="col-md-6">
                        <div class="border rounded" style="border-color: #10b981 !important; border-width: 2px !important;">
                            <div class="p-3 rounded-top" style="background-color: #059669; color: white;">
                                <h6 class="mb-0 fw-semibold">
                                    <i class="ri-user-smile-line me-2"></i>Children & Infants
                                </h6>
                            </div>
                            <div class="p-3">
                                <!-- Children Sub-section -->
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="ri-user-smile-line me-2" style="color: #10b981; font-size: 1.2rem;"></i>
                                        <label class="form-label mb-0 fw-semibold">Children (Ages 1-17)</label>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <button type="button" class="btn btn-sm" onclick="decrementTourCount('tour_children_count')" style="background-color: #d1fae5; color: #10b981; width: 40px; height: 40px; border: none; border-radius: 4px; font-weight: bold;">
                                            <i class="ri-subtract-line"></i>
                                        </button>
                                        <input type="number" class="form-control text-center mx-2" id="tour_children_count" name="tour_children_count" value="{{ $tour->child ?? 0 }}" min="0" max="20" readonly style="width: 60px; height: 40px; border: 1px solid #ddd; background-color: white;">
                                        <button type="button" class="btn btn-sm" onclick="incrementTourCount('tour_children_count')" style="background-color: #10b981; color: white; width: 40px; height: 40px; border: none; border-radius: 4px; font-weight: bold;">
                                            <i class="ri-add-line"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Child Ages Selection -->
                                <div id="tour_child_ages_container" class="mb-3" style="display: none;">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="ri-user-line me-2" style="color: #10b981; font-size: 1.1rem;"></i>
                                        <label class="form-label mb-0 fw-semibold">Select Ages for Children</label>
                                    </div>
                                    <div id="tour_child_ages_list">
                                        <!-- Dynamic child age select boxes will be inserted here -->
                                    </div>
                                </div>
                                
                                <!-- Infants Sub-section -->
                                <div>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="ri-baby-carriage-line me-2" style="color: #f97316; font-size: 1.2rem;"></i>
                                        <label class="form-label mb-0 fw-semibold">Infants (Under 1 year)</label>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <button type="button" class="btn btn-sm" onclick="decrementTourCount('tour_infants_count')" style="background-color: #fed7aa; color: #f97316; width: 40px; height: 40px; border: none; border-radius: 4px; font-weight: bold;">
                                            <i class="ri-subtract-line"></i>
                                        </button>
                                        <input type="number" class="form-control text-center mx-2" id="tour_infants_count" name="tour_infants_count" value="{{ $tour->infant ?? 0 }}" min="0" max="10" readonly style="width: 60px; height: 40px; border: 1px solid #ddd; background-color: white;">
                                        <button type="button" class="btn btn-sm" onclick="incrementTourCount('tour_infants_count')" style="background-color: #f97316; color: white; width: 40px; height: 40px; border: none; border-radius: 4px; font-weight: bold;">
                                            <i class="ri-add-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" onclick="confirmTourGuestSelection()" style="background-color: #6366f1; color: white;">
                    <i class="ri-check-line me-1"></i>Apply Selection
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Guest Selector Modal for Restaurant -->
<div class="modal fade" id="modalGuestSelectorModal" tabindex="-1" aria-labelledby="modalGuestSelectorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalGuestSelectorModalLabel">
                    <i class="ri-group-line me-2"></i>Select Guests for Restaurant
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="modalGuestSelectorForm">
                    <div class="row g-3">
                        <!-- Pax -->
                        <div class="col-md-6">
                            <label for="modal_pax" class="form-label fw-semibold">Pax</label>
                            <div class="input-group">
                                
                                <input type="number" class="form-control text-center" id="modal_pax" name="modal_pax" value="1" min="1" max="20" readonly>
                                
                            </div>
                            <small class="text-muted">Total persons (adults + children)</small>
                        </div>

                        <!-- Children -->
                        <div class="col-md-6">
                            <label for="modal_children" class="form-label fw-semibold">Children</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" onclick="decrementCount('modal_children')">-</button>
                                <input type="number" class="form-control text-center" id="modal_children" name="modal_children" value="0" min="0" max="20">
                                <button type="button" class="btn btn-outline-secondary" onclick="incrementCount('modal_children')">+</button>
                            </div>
                        </div>

                        <!-- Male Count -->
                        <div class="col-md-6">
                            <label for="modal_male_count" class="form-label fw-semibold">Male</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" onclick="decrementCount('modal_male_count')">-</button>
                                <input type="number" class="form-control text-center" id="modal_male_count" name="modal_male_count" value="1" min="0" max="20">
                                <button type="button" class="btn btn-outline-secondary" onclick="incrementCount('modal_male_count')">+</button>
                            </div>
                        </div>

                        <!-- Female Count -->
                        <div class="col-md-6">
                            <label for="modal_female_count" class="form-label fw-semibold">Female</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" onclick="decrementCount('modal_female_count')">-</button>
                                <input type="number" class="form-control text-center" id="modal_female_count" name="modal_female_count" value="0" min="0" max="20">
                                <button type="button" class="btn btn-outline-secondary" onclick="incrementCount('modal_female_count')">+</button>
                            </div>
                        </div>

                        <!-- Infants -->
                        <div class="col-md-6">
                            <label for="modal_infants" class="form-label fw-semibold">Infants</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" onclick="decrementCount('modal_infants')">-</button>
                                <input type="number" class="form-control text-center" id="modal_infants" name="modal_infants" value="0" min="0" max="10">
                                <button type="button" class="btn btn-outline-secondary" onclick="incrementCount('modal_infants')">+</button>
                            </div>
                        </div>

                        <!-- Child Ages -->
                        <div class="col-md-6">
                            <label for="modal_child_ages" class="form-label fw-semibold">Child Ages</label>
                            <input type="text" class="form-control" id="modal_child_ages" name="modal_child_ages" placeholder="e.g., 5,8,12" disabled>
                            <small class="text-muted">Comma separated ages (only if children > 0)</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmModalGuestSelection()">
                    <i class="ri-check-line me-1"></i>Confirm Guest Selection
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Attraction Selection Modal -->
<div class="modal fade" id="attractionSelectionModal" tabindex="-1" aria-labelledby="attractionSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-danger text-white">
                <h5 class="modal-title" id="attractionSelectionModalLabel">
                    <i class="ri-ticket-2-line me-2"></i>Select Attraction & Ticket Options
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Tour Info Display -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="ri-calendar-line me-2 text-primary"></i>
                            <span class="fw-semibold">Tour Dates: <span id="modal_attraction_tour_dates" class="text-primary"></span></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="ri-map-pin-line me-2 text-primary"></i>
                            <span class="fw-semibold">Destination: <span id="modal_attraction_destination" class="text-primary"></span></span>
                        </div>
                    </div>
                </div>

                <!-- Attraction Selection Form -->
                <form id="attractionSelectionForm" onsubmit="return false;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label fw-semibold text-muted mb-2">
                                    <i class="ri-map-pin-line text-success me-2"></i>Attraction City
                                </label>
                                <div class="position-relative">
                                    <select class="form-select border-2" id="modal_attraction_city_select" name="city" style="padding-left: 45px;">
                                        <option value="">Select city</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->name }}" data-city="{{ json_encode($city) }}" data-country="{{ $city->country }}">{{ $city->name }}</option>
                                        @endforeach
                                    </select>
                                    <i style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                </div>
                            </div>
                        </div>
                        <!-- Attraction Selection -->
                        <div class="col-md-6">
                            <label for="modal_attraction_select" class="form-label fw-semibold">
                                <i class="ri-ticket-2-line me-1"></i>Select Attraction
                            </label>
                            <select class="form-select" id="modal_attraction_select" name="attraction_id" required>
                                <option value="">Search Attraction</option>
                            </select>
                            <div class="form-text">
                                <i class="ri-information-line text-info me-1"></i>
                                <span id="attraction_count">0</span> attractions available in <span id="modal_attraction_city"></span>
                            </div>
                        </div>

                        <!-- Guest Selector -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Select Guests</label>
                            <div class="guest-selector">
                                <div class="guest-display p-3 border rounded bg-light shadow-sm">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="flex-grow-1">
                                            <div class="mb-2">
                                                <span id="modal_attraction_guest_summary" class="text-dark">
                                                    1 pax (1 adults, 0 children) - 1 male, 0 female - 0 infants
                                                </span>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <span class="badge bg-primary" id="modal_badge_adults">1</span>
                                                <span class="badge bg-success" id="modal_badge_children">0</span>
                                                <span class="badge bg-warning text-dark" id="modal_badge_infants">0</span>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openAttractionGuestSelector()">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden fields for attraction pricing -->
                            <input type="hidden" name="modal_attraction_total_price" id="modal_attraction_total_price" value="0">
                            <input type="hidden" name="modal_attraction_ticket_id" id="modal_attraction_ticket_id" value="">
                            <input type="hidden" name="modal_attraction_ticket_name" id="modal_attraction_ticket_name" value="">
                        </div>

                        <!-- Visit Date Selection -->
                        <div class="col-md-6">
                            <label for="modal_attraction_visit_date" class="form-label fw-semibold">
                                <i class="ri-calendar-line me-1"></i>Visit Date
                            </label>
                            <input type="date" class="form-control" id="modal_attraction_visit_date" name="visit_date" required>
                            <small class="text-muted">Select the date for attraction visit</small>
                        </div>

                        <!-- Time Slot Selection -->
                        <div class="col-md-6">
                            <label for="modal_attraction_time_slot" class="form-label fw-semibold">
                                <i class="ri-time-line me-1"></i>Time Slot
                            </label>
                            <select class="form-select" id="modal_attraction_time_slot" name="time_slot" required>
                                <option value="">Select Attraction First</option>
                            </select>
                            <small class="text-muted">Available time slots for the attraction</small>
                        </div>

                        <!-- Ticket Selection -->
                        <div class="col-md-6">
                            <label for="modal_attraction_ticket" class="form-label fw-semibold">Select Ticket</label>
                            <select class="form-select" name="modal_attraction_ticket" id="modal_attraction_ticket" onchange="onTicketSelection(); updateAttractionPricing();">
                                <option value="">Select Ticket</option>
                            </select>
                            <small id="modal_attraction_ticket_prices" class="text-muted"></small>
                        </div>

                        <!-- Attraction Details Display -->
                        <div class="col-12" id="attraction_details_container" style="display: none;">
                            <div class="card border-danger bg-light">
                                <div class="card-body p-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <img id="selected_attraction_image" src="" alt="Attraction" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                        </div>
                                        <div class="col-md-7">
                                            <h6 id="selected_attraction_name" class="mb-1 fw-bold"></h6>
                                            <p id="selected_attraction_category" class="mb-1 text-muted small"></p>
                                            <p id="selected_attraction_location" class="mb-0 text-muted small"></p>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <div class="attraction-rating mb-1">
                                                <i class="ri-star-fill text-warning"></i>
                                                <span id="selected_attraction_rating" class="fw-semibold"></span>
                                            </div>
                                            <div class="attraction-price-range">
                                                <span id="selected_attraction_price_range" class="fw-bold text-danger"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Attraction Price Display -->
                        <div class="col-12" id="attraction_price_display" style="display: none;">
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <i class="ri-money-dollar-circle-line me-2 fs-4"></i>
                                    <div>
                                        <strong>Attraction Pricing</strong>
                                        <div id="attraction_price_details" class="small">Select an attraction and configure guests to see pricing</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm_attraction_btn" disabled>
                    <i class="ri-check-line me-1"></i>Confirm Attraction Selection
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Guest Selector Modal for Attraction -->
<div class="modal fade" id="attractionGuestSelectorModal" tabindex="-1" aria-labelledby="attractionGuestSelectorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="attractionGuestSelectorModalLabel">
                    <i class="ri-group-line me-2"></i>Select Guests for Attraction
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="attractionGuestSelectorForm" onsubmit="return false;">
                    <div class="row g-3">
                        <!-- Pax -->
                        <div class="col-md-6">
                            <label for="attraction_modal_pax" class="form-label fw-semibold">Pax</label>
                            <div class="input-group">
                                <input type="number" class="form-control text-center" id="attraction_modal_pax" name="attraction_modal_pax" value="1" min="1" max="20" readonly>
                            </div>
                            <small class="text-muted">Total persons (adults + children)</small>
                        </div>

                        <!-- Children -->
                        <div class="col-md-6">
                            <label for="attraction_modal_children" class="form-label fw-semibold">Children</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" onclick="decrementAttractionCount('attraction_modal_children')">-</button>
                                <input type="number" class="form-control text-center" id="attraction_modal_children" name="attraction_modal_children" value="0" min="0" max="20">
                                <button type="button" class="btn btn-outline-secondary" onclick="incrementAttractionCount('attraction_modal_children')">+</button>
                            </div>
                        </div>

                        <!-- Male Count -->
                        <div class="col-md-6">
                            <label for="attraction_modal_male_count" class="form-label fw-semibold">Male</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" onclick="decrementAttractionCount('attraction_modal_male_count')">-</button>
                                <input type="number" class="form-control text-center" id="attraction_modal_male_count" name="attraction_modal_male_count" value="1" min="0" max="20">
                                <button type="button" class="btn btn-outline-secondary" onclick="incrementAttractionCount('attraction_modal_male_count')">+</button>
                            </div>
                        </div>

                        <!-- Female Count -->
                        <div class="col-md-6">
                            <label for="attraction_modal_female_count" class="form-label fw-semibold">Female</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" onclick="decrementAttractionCount('attraction_modal_female_count')">-</button>
                                <input type="number" class="form-control text-center" id="attraction_modal_female_count" name="attraction_modal_female_count" value="0" min="0" max="20">
                                <button type="button" class="btn btn-outline-secondary" onclick="incrementAttractionCount('attraction_modal_female_count')">+</button>
                            </div>
                        </div>

                        <!-- Infants -->
                        <div class="col-md-6">
                            <label for="attraction_modal_infants" class="form-label fw-semibold">Infants</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" onclick="decrementAttractionCount('attraction_modal_infants')">-</button>
                                <input type="number" class="form-control text-center" id="attraction_modal_infants" name="attraction_modal_infants" value="0" min="0" max="10">
                                <button type="button" class="btn btn-outline-secondary" onclick="incrementAttractionCount('attraction_modal_infants')">+</button>
                            </div>
                        </div>

                        <!-- Child Ages -->
                        <div class="col-md-6">
                            <label for="attraction_modal_child_ages" class="form-label fw-semibold">Child Ages</label>
                            <input type="text" class="form-control" id="attraction_modal_child_ages" name="attraction_modal_child_ages" placeholder="e.g., 5,8,12" disabled>
                            <small class="text-muted">Comma separated ages (only if children > 0)</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmAttractionGuestSelection()">
                    <i class="ri-check-line me-1"></i>Confirm Guest Selection
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Transport Selection Modal -->
<div class="modal fade" id="transportSelectionModal" tabindex="-1" aria-labelledby="transportSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="transportSelectionModalLabel">
                    <i class="ri-car-line me-2"></i>Transport Service Selection
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="transportSelectionForm" onsubmit="return false;">
                    <input type="hidden" id="modal_transport_tour_id" name="tour_id">
                    <input type="hidden" id="modal_transport_country" name="country">
                    <input type="hidden" id="modal_transport_city" name="city">
                    <input type="hidden" id="modal_transport_start_date" name="start_date">
                    <input type="hidden" id="modal_transport_end_date" name="end_date">
                    <input type="hidden" id="modal_transport_type" name="transport_type" value="entry_port">
                    
                    <div class="card border-primary shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <div class="d-flex align-items-center">
                                <span class="service-icon me-3">
                                    <i class="ri-login-circle-line fs-4"></i>
                                </span>
                                <div>
                                    <h6 class="mb-0 fw-bold">Transport Services</h6>
                                    <small class="opacity-75">Configure pickup and dropoff locations</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body bg-white">
                            <div class="row g-4 align-items-end">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label fw-semibold text-muted mb-2">
                                            <i class="ri-map-pin-line text-success me-2"></i>City
                                        </label>
                                        <div class="position-relative">
                                            <select class="form-select border-2" id="modal_entryport_transport_city" name="city" style="padding-left: 45px;">
                                                <option value="">Select city</option>
                                                @foreach($cities as $city)
                                                <option value="{{ $city->name }}" data-city="{{ json_encode($city) }}">{{ $city->name }}</option>
                                                @endforeach
                                            </select>
                                            <i style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label fw-semibold text-muted mb-2">
                                            <i class="ri-map-pin-line text-success me-2"></i>Pick Up Location
                                        </label>
                                        <div class="position-relative">
                                            <select class="form-select pickup-zone-select border-2" id="modal_transport_pickup_zone" name="pickup_zone_id" style="padding-left: 45px;">
                                                <option value="">Select pickup location</option>
                                                @foreach($ports as $port)
                                                    <option data-type="Port" value="{{ $port->port_id }}" data-port="{{ json_encode($port) }}">{{ $port->port_name }}</option>
                                                @endforeach
                                            </select>
                                            <i style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label fw-semibold text-muted mb-2">
                                            <i class="ri-map-pin-line text-danger me-2"></i>Drop Off Location
                                        </label>
                                        <div class="position-relative">
                                            <select class="form-select dropoff-zone-select border-2" id="modal_transport_dropoff_zone" name="dropoff_zone_id" style="padding-left: 45px; padding-right: 45px;">
                                                <option value="">Select dropoff location</option>
                                                
                                                <!-- Hotels -->
                                                <optgroup label="Hotels">
                                                @foreach($hotels as $hotel)
                                                    <option data-type="Hotel" value="{{ $hotel->hotel_unique_id }}" data-hotel="{{ json_encode($hotel) }}">{{ $hotel->name }}</option>
                                                @endforeach
                                                </optgroup>
                                                
                                                <!-- Attractions -->
                                                <optgroup label="Attractions">
                                                @foreach($attractions as $attraction)
                                                    <option data-type="Attraction" value="{{ $attraction->attraction_id }}" data-attraction="{{ json_encode($attraction) }}">{{ $attraction->name }}</option>
                                                @endforeach
                                                </optgroup>
                                                
                                                <!-- Restaurants -->
                                                <optgroup label="Restaurants">
                                                @foreach($restaurants as $restaurant)
                                                    <option data-type="Restaurant" value="{{ $restaurant->restaurant_id }}" data-restaurant="{{ json_encode($restaurant) }}">{{ $restaurant->name }}</option>
                                                @endforeach
                                                </optgroup>
                                            </select>
                                            <i style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label fw-semibold text-muted mb-2">
                                            <i class="ri-time-line text-warning me-2"></i>Pick Up Time
                                        </label>
                                        <div class="position-relative">
                                            <select class="form-select border-2" id="modal_transport_pickup_time" name="pickup_time" style="padding-left: 45px;">
                                                <option value="">Select The Time</option>
                                                <option value="12:00 AM">12:00 AM</option>
                                                <option value="01:00 AM">01:00 AM</option>
                                                <option value="02:00 AM">02:00 AM</option>
                                                <option value="03:00 AM">03:00 AM</option>
                                                <option value="04:00 AM">04:00 AM</option>
                                                <option value="05:00 AM">05:00 AM</option>
                                                <option value="06:00 AM">06:00 AM</option>
                                                <option value="07:00 AM">07:00 AM</option>
                                                <option value="08:00 AM">08:00 AM</option>
                                                <option value="09:00 AM">09:00 AM</option>
                                                <option value="10:00 AM">10:00 AM</option>
                                                <option value="11:00 AM">11:00 AM</option>
                                                <option value="12:00 PM">12:00 PM</option>
                                                <option value="01:00 PM">01:00 PM</option>
                                                <option value="02:00 PM">02:00 PM</option>
                                                <option value="03:00 PM">03:00 PM</option>
                                                <option value="04:00 PM">04:00 PM</option>
                                                <option value="05:00 PM">05:00 PM</option>
                                                <option value="06:00 PM">06:00 PM</option>
                                                <option value="07:00 PM">07:00 PM</option>
                                                <option value="08:00 PM">08:00 PM</option>
                                                <option value="09:00 PM">09:00 PM</option>
                                                <option value="10:00 PM">10:00 PM</option>
                                                <option value="11:00 PM">11:00 PM</option>
                                            </select>
                                            <i style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label fw-semibold text-muted mb-2">
                                            <i class="ri-calendar-line text-primary me-2"></i>Pick Up Date
                                        </label>
                                        <div class="position-relative">
                                            <input type="date" class="form-control border-2" id="modal_transport_pickup_date" name="pickup_date" value="{{ \Carbon\Carbon::parse($tour->check_in_time)->format('Y-m-d') }}" readonly disabled style="padding-left: 45px;">
                                            <i class="ri-calendar-fill position-absolute text-primary" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary w-100 py-2" onclick="searchVehicles()" id="transport_search_btn">
                                        <i class="ri-search-line me-2"></i>Search Vehicles
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Vehicle Results Section (Hidden Initially) -->
                            <div class="row mt-4" id="transport_vehicle_results" style="display: none;">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <div class="d-flex align-items-center">
                                            <i class="ri-car-line me-2 fs-4"></i>
                                            <div>
                                                <strong>Available Vehicles</strong>
                                                <div class="small text-muted">Select your preferred vehicle and service type below</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Vehicle + Service Type in one row -->
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Vehicle</label>
                                            <select class="form-select vehicle-select" 
                                                    id="modal_transport_vehicle_id" 
                                                    name="vehicle_id" 
                                                    onchange="updateVehicleDetails()">
                                                <option value="">Choose vehicle</option>
                                            </select>
                                        </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Service Type</label>
                                    <select class="form-select service-type-select" 
                                            id="modal_transport_service_type" 
                                            name="service_type" 
                                            onchange="updatePricing()">
                                        <option value="">Select service type</option>
                                        <option value="Shared">Shared</option>
                                        <option value="Private">Private</option>
                                    </select>
                                </div>

                                <!-- Manual Price Input (Available for both Zone On and Point-to-Point) -->
                                @if(isset($UserDmc->zone_on) && $UserDmc->zone_on == 0)
                                <div class="col-md-3" id="transport_manual_price_field_container" style="display: none;">
                                    <label class="form-label fw-semibold">
                                        <i class="ri-money-dollar-circle-line text-success me-1"></i>Manual Price (Optional)
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" 
                                               class="form-control" 
                                               id="modal_transport_manual_price" 
                                               name="manual_price" 
                                               step="0.01" 
                                               min="0" 
                                               placeholder="0.00"
                                               onchange="updatePricing()"
                                               oninput="updatePricing()">
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="ri-information-line me-1"></i>
                                        Override vehicle price with custom amount
                                    </small>
                                </div>
                                @endif
                                <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold">Number of Passengers</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="ri-user-line"></i></span>
                                                    <input type="number" class="form-control" id="modal_transport_passengers" name="passengers" min="1" max="{{ ($tour->adult ?? 0) + ($tour->child ?? 0) }}" value="" onkeyup="updatePricing()" onchange="updatePricing()">
                                                </div>
                                                <small class="form-text text-muted">
                                                    Maximum passengers: {{ ($tour->adult ?? 0) + ($tour->child ?? 0) }} ({{ $tour->adult ?? 0 }} adults + {{ $tour->child ?? 0 }} children)
                                                </small>
                                            </div>
                                        </div>
                            </div>
                                    
                                    <!-- Guest Information -->
                                    <!-- <div class="row mt-3">
                                        
                                    </div> -->
                                    
                                    <!-- Price Display for Transport -->
                                    <div class="col-12 mt-3">
                                        <div id="transport_price_display" class="alert alert-success" style="display: none;">
                                            <div class="d-flex align-items-center">
                                                <i class="ri-money-dollar-circle-line me-2 fs-4"></i>
                                                <div>
                                                    <strong>Price Information</strong>
                                                    <div id="transport_price_details" class="small">Select a vehicle and service type to see pricing</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Hidden fields for transport pricing -->
                                        <input type="hidden" name="transport_base_price" id="modal_transport_base_price" value="0">
                                        <input type="hidden" name="transport_total_price" id="modal_transport_total_price" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning" onclick="confirmTransportSelection()">
                            <i class="ri-check-line me-1"></i>Confirm Transport Selection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End of Transport Selection Modal -->

<!-- Local Transfer Selection Modal -->
<div class="modal fade" id="localTransferSelectionModal" tabindex="-1" aria-labelledby="localTransferSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="localTransferSelectionModalLabel">
                    <i class="ri-taxi-line me-2"></i>Local Transfer Service Selection
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="localTransferSelectionForm" onsubmit="return false;">
                    <input type="hidden" id="local_transfer_tour_id" name="tour_id">
                    <input type="hidden" id="local_transfer_country" name="country">
                    <input type="hidden" id="local_transfer_city" name="city">
                    <input type="hidden" id="local_transfer_start_date" name="start_date">
                    <input type="hidden" id="local_transfer_end_date" name="end_date">
                    
                    <div class="card border-info shadow-sm mb-4">
                        <div class="card-header bg-info text-white">
                            <div class="d-flex align-items-center">
                                <span class="service-icon me-3">
                                    <i class="ri-login-circle-line fs-4"></i>
                                </span>
                                <div>
                                    <h6 class="mb-0 fw-bold">Local Transfer Services</h6>
                                    <small class="opacity-75">Configure pickup and dropoff locations</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input transport-service-type" type="radio" name="service_type_selection" id="local_transfer_service_type_point" value="point_to_point" onchange="handleLocalTransferServiceTypeChange('point_to_point')">
                                        <label class="form-check-label fw-semibold" for="local_transfer_service_type_point">
                                            <i class="ri-route-line me-1"></i>Point To Point
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input transport-service-type" type="radio" name="service_type_selection" id="local_transfer_service_type_hourly" value="hourly" onchange="handleLocalTransferServiceTypeChange('hourly')">
                                        <label class="form-check-label fw-semibold" for="local_transfer_service_type_hourly">
                                            <i class="ri-time-line me-1"></i>Hourly
                                        </label>
                                    </div>
                                    @if(isset($dmcUser->zone_on) && $dmcUser->zone_on != 0)
                                    <div class="form-check">
                                        <input class="form-check-input transport-service-type" type="radio" name="service_type_selection" id="local_transfer_service_type_local" value="local_transfer" onchange="handleLocalTransferServiceTypeChange('local_transfer')" checked>
                                        <label class="form-check-label fw-semibold text-success" for="local_transfer_service_type_local">
                                            <i class="ri-car-line me-1"></i>Local Transfer
                                        </label>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="row g-4 align-items-end">

                                <!-- Local Transfer Fields (Default) -->
                                <div class="col-12">
                                    
                                    <div id="local_transfer_fields" class="row g-4 align-items-end local-transfer-fields d-none">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold text-muted mb-2">
                                                    <i class="ri-map-pin-line text-success me-2"></i>City
                                                </label>
                                                <div class="position-relative">
                                                    <select class="form-select border-2" id="modal_local_transfer_city" name="city" style="padding-left: 45px;">
                                                        <option value="">Select city</option>
                                                        @foreach($cities as $city)
                                                            <option value="{{ $city->name }}" data-city="{{ json_encode($city) }}" data-country="{{ $city->country }}">{{ $city->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <i style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold text-muted mb-2">
                                                    <i class="ri-map-pin-line text-success me-2"></i>Pick Up Location
                                                </label>
                                                <div class="position-relative">
                                                    <select class="form-select pickup-zone-select border-2" id="local_transfer_pickup_zone" name="pickup_zone_id" style="padding-left: 45px;">
                                                        <option value="">Select pickup location</option>
                                                        <optgroup label="Ports">
                                                        @foreach($ports as $port)
                                                            <option data-type="Port" value="{{ $port->port_id }}" data-port="{{ json_encode($port) }}">{{ $port->port_name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                        <optgroup label="Hotels">
                                                        @foreach($hotels as $hotel)
                                                        <option data-type="Hotel" value="{{ $hotel->hotel_unique_id }}" data-hotel="{{ json_encode($hotel) }}">{{ $hotel->name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                        <optgroup label="Attractions">
                                                        @foreach($attractions as $attraction)
                                                        <option data-type="Attraction" value="{{ $attraction->attraction_id }}" data-attraction="{{ json_encode($attraction) }}">{{ $attraction->name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                        <optgroup label="Restaurants">
                                                        @foreach($restaurants as $restaurant)
                                                        <option data-type="Restaurant" value="{{ $restaurant->restaurant_id }}" data-restaurant="{{ json_encode($restaurant) }}">{{ $restaurant->name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                    </select>
                                                    <i style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold text-muted mb-2">
                                                    <i class="ri-map-pin-line text-danger me-2"></i>Drop Off Location
                                                </label>
                                                <div class="position-relative">
                                                    <select class="form-select dropoff-zone-select border-2" id="local_transfer_dropoff_zone" name="dropoff_zone_id" style="padding-left: 45px; padding-right: 45px;">
                                                        <option value="">Select dropoff location</option>
                                                        <!-- Ports -->
                                                        <optgroup label="Ports">
                                                        @foreach($ports as $port)
                                                        <option data-type="Port" value="{{ $port->port_id }}" data-port="{{ json_encode($port) }}">{{ $port->port_name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                        <!-- Hotels -->
                                                        <optgroup label="Hotels">
                                                        @foreach($hotels as $hotel)
                                                            <option data-type="Hotel" value="{{ $hotel->hotel_unique_id }}" data-hotel="{{ json_encode($hotel) }}">{{ $hotel->name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                        
                                                        <!-- Attractions -->
                                                        <optgroup label="Attractions">
                                                        @foreach($attractions as $attraction)
                                                        <option data-type="Attraction" value="{{ $attraction->attraction_id }}" data-attraction="{{ json_encode($attraction) }}">{{ $attraction->name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                        
                                                        <!-- Restaurants -->
                                                        <optgroup label="Restaurants">
                                                        @foreach($restaurants as $restaurant)
                                                        <option data-type="Restaurant" value="{{ $restaurant->restaurant_id }}" data-restaurant="{{ json_encode($restaurant) }}">{{ $restaurant->name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                    </select>
                                                    <i style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold text-muted mb-2">
                                                    <i class="ri-time-line text-warning me-2"></i>Pick Up Time
                                                </label>
                                                <div class="position-relative">
                                                    <select class="form-select border-2" id="local_transfer_pickup_time" name="pickup_time" style="padding-left: 45px;">
                                                        <option value="">Select The Time</option>
                                                        <option value="12:00 AM">12:00 AM</option>
                                                        <option value="01:00 AM">01:00 AM</option>
                                                        <option value="02:00 AM">02:00 AM</option>
                                                        <option value="03:00 AM">03:00 AM</option>
                                                        <option value="04:00 AM">04:00 AM</option>
                                                        <option value="05:00 AM">05:00 AM</option>
                                                        <option value="06:00 AM">06:00 AM</option>
                                                        <option value="07:00 AM">07:00 AM</option>
                                                        <option value="08:00 AM">08:00 AM</option>
                                                        <option value="09:00 AM">09:00 AM</option>
                                                        <option value="10:00 AM">10:00 AM</option>
                                                        <option value="11:00 AM">11:00 AM</option>
                                                        <option value="12:00 PM">12:00 PM</option>
                                                        <option value="01:00 PM">01:00 PM</option>
                                                        <option value="02:00 PM">02:00 PM</option>
                                                        <option value="03:00 PM">03:00 PM</option>
                                                        <option value="04:00 PM">04:00 PM</option>
                                                        <option value="05:00 PM">05:00 PM</option>
                                                        <option value="06:00 PM">06:00 PM</option>
                                                        <option value="07:00 PM">07:00 PM</option>
                                                        <option value="08:00 PM">08:00 PM</option>
                                                        <option value="09:00 PM">09:00 PM</option>
                                                        <option value="10:00 PM">10:00 PM</option>
                                                        <option value="11:00 PM">11:00 PM</option>
                                                    </select>
                                                    <i style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold text-muted mb-2">
                                                    <i class="ri-calendar-line text-primary me-2"></i>Pick Up Date
                                                </label>
                                                <div class="position-relative">
                                                    <input type="date" class="form-control border-2" id="local_transfer_pickup_date" name="pickup_date" value="" placeholder="dd-mm-yyyy" min="{{ \Carbon\Carbon::parse($tour->check_in_time)->format('Y-m-d') }}" max="{{ \Carbon\Carbon::parse($tour->check_out_time)->format('Y-m-d') }}" style="padding-left: 45px;">
                                                    <i class="ri-calendar-fill position-absolute text-primary" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-primary w-100 py-2" onclick="searchLocalTransferVehicles()" id="local_transfer_search_btn" disabled>
                                                <i class="ri-search-line me-2"></i>Search Vehicles
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Point To Point Fields (Hidden Initially) -->
                                <div class="col-12">
                                    <div id="point_to_point_fields" class="row g-4 align-items-end point-to-point-fields d-none">
                                        <div class="col-md-3 point-to-point-fields">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold text-muted mb-2">
                                                    <i class="ri-map-pin-line text-success me-2"></i>Pick Up Location
                                                </label>
                                                <div class="position-relative location-input">
                                                    <input type="text" class="form-control border-2 google-maps-autocomplete" id="local_transfer_point_pickup_location" name="point_pickup_location" placeholder="Search for pickup location..." style="padding-left: 45px;">
                                                    <i class="ri-search-line position-absolute text-success location-icon"></i>
                                                    <input type="hidden" name="point_pickup_lat" id="local_transfer_point_pickup_lat">
                                                    <input type="hidden" name="point_pickup_lng" id="local_transfer_point_pickup_lng">
                                                    <input type="hidden" name="point_pickup_place_id" id="local_transfer_point_pickup_place_id">
                                                </div>
                                                <!-- Keep the original select as backup -->
                                            </div>
                                        </div>
                                        <div class="col-md-3 point-to-point-fields">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold text-muted mb-2">
                                                    <i class="ri-map-pin-line text-danger me-2"></i>Drop Off Location
                                                </label>
                                                <div class="position-relative location-input">
                                                <input type="text" class="form-control border-2 google-maps-autocomplete" id="local_transfer_point_dropoff_location" name="point_dropoff_location" placeholder="Search for dropoff location..." style="padding-left: 45px;">
                                                    <i class="ri-map-pin-fill position-absolute text-danger location-icon"></i>
                                                    <input type="hidden" name="point_dropoff_lat" id="local_transfer_point_dropoff_lat">
                                                    <input type="hidden" name="point_dropoff_lng" id="local_transfer_point_dropoff_lng">
                                                    <input type="hidden" name="point_dropoff_place_id" id="local_transfer_point_dropoff_place_id">
                                                </div>
                                                <!-- Keep the original select as backup -->
                                                
                                            </div>
                                        </div>
                                        <div class="col-md-3 point-to-point-fields">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold text-muted mb-2">
                                                    <i class="ri-time-line text-warning me-2"></i>Pick Up Time
                                                </label>
                                                <div class="position-relative">
                                                    <select class="form-select border-2" id="local_transfer_point_pickup_time" name="point_pickup_time" style="padding-left: 45px;">
                                                        <option value="">Select time</option>
                                                        <option value="12:00 AM">12:00 AM</option>
                                                        <option value="01:00 AM">01:00 AM</option>
                                                        <option value="02:00 AM">02:00 AM</option>
                                                        <option value="03:00 AM">03:00 AM</option>
                                                        <option value="04:00 AM">04:00 AM</option>
                                                        <option value="05:00 AM">05:00 AM</option>
                                                        <option value="06:00 AM">06:00 AM</option>
                                                        <option value="07:00 AM">07:00 AM</option>
                                                        <option value="08:00 AM">08:00 AM</option>
                                                        <option value="09:00 AM">09:00 AM</option>
                                                        <option value="10:00 AM">10:00 AM</option>
                                                        <option value="11:00 AM">11:00 AM</option>
                                                        <option value="12:00 PM">12:00 PM</option>
                                                        <option value="01:00 PM">01:00 PM</option>
                                                        <option value="02:00 PM">02:00 PM</option>
                                                        <option value="03:00 PM">03:00 PM</option>
                                                        <option value="04:00 PM">04:00 PM</option>
                                                        <option value="05:00 PM">05:00 PM</option>
                                                        <option value="06:00 PM">06:00 PM</option>
                                                        <option value="07:00 PM">07:00 PM</option>
                                                        <option value="08:00 PM">08:00 PM</option>
                                                        <option value="09:00 PM">09:00 PM</option>
                                                        <option value="10:00 PM">10:00 PM</option>
                                                        <option value="11:00 PM">11:00 PM</option>
                                                    </select>
                                                    <i style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 point-to-point-fields">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold text-muted mb-2">
                                                    <i class="ri-calendar-line text-primary me-2"></i>Pick Up Date
                                                </label>
                                                <div class="position-relative">
                                                    <input type="date" class="form-control border-2" id="local_transfer_point_pickup_date" name="point_pickup_date" value="{{ \Carbon\Carbon::parse($tour->check_in_time)->format('Y-m-d') }}" min="{{ \Carbon\Carbon::parse($tour->check_in_time)->format('Y-m-d') }}" max="{{ \Carbon\Carbon::parse($tour->check_out_time)->format('Y-m-d') }}" style="padding-left: 45px;">
                                                    <i class="ri-calendar-fill position-absolute text-primary" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-primary w-100 py-2" onclick="searchPointToPointVehicles()" id="local_transfer_point_to_point_search_btn" disabled>
                                                <i class="ri-search-line me-2"></i>Search Vehicles
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Hourly Fields (Hidden Initially) -->
                                <div class="col-12">
                                    <div id="hourly_fields" class="hourly-fields row g-4 col-12 align-items-end d-none">
                                        <div class="col-md-3 hourly-fields">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold text-muted mb-2">
                                                    <i class="ri-map-pin-line text-success me-2"></i>Pick Up Location
                                                </label>
                                                <div class="position-relative location-input">
                                                    <input type="text" class="form-control border-2 google-maps-autocomplete" id="local_transfer_hourly_pickup_location" name="hourly_pickup_location" placeholder="Search for pickup location..." style="padding-left: 45px;">
                                                    <i class="ri-search-line position-absolute text-success location-icon"></i>
                                                    <input type="hidden" name="hourly_pickup_lat" id="local_transfer_hourly_pickup_lat">
                                                    <input type="hidden" name="hourly_pickup_lng" id="local_transfer_hourly_pickup_lng">
                                                    <input type="hidden" name="hourly_pickup_place_id" id="local_transfer_hourly_pickup_place_id">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 hourly-fields">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold text-muted mb-2">
                                                    <i class="ri-time-line text-warning me-2"></i>Pick Up Time
                                                </label>
                                                <div class="position-relative">
                                                    <select class="form-select border-2" id="local_transfer_hourly_pickup_time" name="hourly_pickup_time" style="padding-left: 45px;">
                                                        <option value="">Select time</option>
                                                        <option value="12:00 AM">12:00 AM</option>
                                                        <option value="01:00 AM">01:00 AM</option>
                                                        <option value="02:00 AM">02:00 AM</option>
                                                        <option value="03:00 AM">03:00 AM</option>
                                                        <option value="04:00 AM">04:00 AM</option>
                                                        <option value="05:00 AM">05:00 AM</option>
                                                        <option value="06:00 AM">06:00 AM</option>
                                                        <option value="07:00 AM">07:00 AM</option>
                                                        <option value="08:00 AM">08:00 AM</option>
                                                        <option value="09:00 AM">09:00 AM</option>
                                                        <option value="10:00 AM">10:00 AM</option>
                                                        <option value="11:00 AM">11:00 AM</option>
                                                        <option value="12:00 PM">12:00 PM</option>
                                                        <option value="01:00 PM">01:00 PM</option>
                                                        <option value="02:00 PM">02:00 PM</option>
                                                        <option value="03:00 PM">03:00 PM</option>
                                                        <option value="04:00 PM">04:00 PM</option>
                                                        <option value="05:00 PM">05:00 PM</option>
                                                        <option value="06:00 PM">06:00 PM</option>
                                                        <option value="07:00 PM">07:00 PM</option>
                                                        <option value="08:00 PM">08:00 PM</option>
                                                        <option value="09:00 PM">09:00 PM</option>
                                                        <option value="10:00 PM">10:00 PM</option>
                                                        <option value="11:00 PM">11:00 PM</option>
                                                    </select>
                                                    <i style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 hourly-fields">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold text-muted mb-2">
                                                    <i class="ri-calendar-line text-primary me-2"></i>Pick Up Date
                                                </label>
                                                <div class="position-relative">
                                                    <input type="date" class="form-control border-2" id="local_transfer_hourly_pickup_date" name="hourly_pickup_date" value="" placeholder="dd-mm-yyyy" min="{{ \Carbon\Carbon::parse($tour->check_in_time)->format('Y-m-d') }}" max="{{ \Carbon\Carbon::parse($tour->check_out_time)->format('Y-m-d') }}" style="padding-left: 45px;">
                                                    <i class="ri-calendar-fill position-absolute text-primary" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 hourly-fields">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold text-muted mb-2">
                                                    <i class="ri-time-line text-info me-2"></i>Number of Hours
                                                </label>
                                                <div class="position-relative">
                                                    <select class="form-select border-2" id="local_transfer_hourly_hours" name="hourly_hours" style="padding-left: 45px;">
                                                        <option value="">Select hours</option>
                                                        <option value="1">1 Hour</option>
                                                        <option value="2">2 Hours</option>
                                                        <option value="3">3 Hours</option>
                                                        <option value="4">4 Hours</option>
                                                        <option value="5">5 Hours</option>
                                                        <option value="6">6 Hours</option>
                                                        <option value="7">7 Hours</option>
                                                        <option value="8">8 Hours</option>
                                                        <option value="9">9 Hours</option>
                                                        <option value="10">10 Hours</option>
                                                        <option value="11">11 Hours</option>
                                                        <option value="12">12 Hours</option>
                                                        <option value="24">24 Hours</option>
                                                    </select>
                                                    <i class="ri-hourglass-fill position-absolute text-info" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-primary w-100 py-2" onclick="searchHourlyVehicles()" id="local_transfer_hourly_search_btn" disabled>
                                                <i class="ri-search-line me-2"></i>Search Vehicles
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Vehicle Results Section (Hidden Initially) -->
                            <div class="row mt-4" id="local_transfer_vehicle_results" style="display: none;">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <div class="d-flex align-items-center">
                                            <i class="ri-car-line me-2 fs-4"></i>
                                            <div>
                                                <strong>Available Vehicles</strong>
                                                <div class="small text-muted">Select your preferred vehicle and service type below</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Vehicle + Service Type in one row -->
                                <div class="col-12">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Vehicle</label>
                                            <select class="form-select vehicle-select" 
                                                    id="local_transfer_vehicle_id" 
                                                    name="vehicle_id" 
                                                    onchange="updateLocalTransferVehicleDetails()">
                                                <option value="">Choose vehicle</option>
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Service Type</label>
                                            <select class="form-select" 
                                                    id="local_transfer_service_type" 
                                                    name="service_type" 
                                                    onchange="updateLocalTransferPricing()">
                                                <option value="">Select service type</option>
                                                <option value="Shared">Shared</option>
                                                <option value="Private">Private</option>
                                            </select>
                                        </div>

                                        <!-- Manual Price Input (Only available for Point-to-Point) -->
                                        <div class="col-md-3" id="manual_price_field_container" style="display: none;">
                                            <label class="form-label fw-semibold">
                                                <i class="ri-money-dollar-circle-line text-success me-1"></i>Manual Price (Optional)
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="local_transfer_manual_price" 
                                                       name="manual_price" 
                                                       step="0.01" 
                                                       min="0" 
                                                       placeholder="0.00"
                                                       onchange="updateLocalTransferPricing()"
                                                       oninput="updateLocalTransferPricing()">
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="ri-information-line me-1"></i>
                                                Override vehicle price with custom amount
                                            </small>
                                        </div>

                                        <!-- Number of Passengers -->
                                        <div class="col-md-2" style="display: none;">
                                            <label class="form-label fw-semibold">Passengers1</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="ri-user-line"></i></span>
                                                <input type="number" class="form-control" id="local_transfer_passengers" name="passengers" min="1" max="{{ ($tour->adult ?? 0) + ($tour->child ?? 0) }}" value="1" onkeyup="updateLocalTransferPricing()" onchange="updateLocalTransferPricing()">
                                            </div>
                                            <small class="form-text text-muted">
                                                Max: {{ ($tour->adult ?? 0) + ($tour->child ?? 0) }}
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <!-- Price Display for Local Transfer -->
                                    <div class="col-12 mt-3">
                                        <div id="local_transfer_price_display" class="alert alert-success" style="display: none;">
                                            <div class="d-flex align-items-center">
                                                <i class="ri-money-dollar-circle-line me-2 fs-4"></i>
                                                <div>
                                                    <strong>Price Information</strong>
                                                    <div id="local_transfer_price_details" class="small">Select a vehicle and service type to see pricing</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Hidden fields for local transfer pricing -->
                                        <input type="hidden" name="local_transfer_base_price" id="local_transfer_base_price" value="0">
                                        <input type="hidden" name="local_transfer_total_price" id="local_transfer_total_price" value="0">
                                        <input type="hidden" name="local_transfer_manual_price_submitted" id="local_transfer_manual_price_submitted" value="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-info" onclick="confirmSelectedLocalTransferService()">
                            <i class="ri-check-line me-1"></i>Confirm Local Transfer Selection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End of Local Transfer Selection Modal -->

<!-- Dropoff Transport Selection Modal -->
<div class="modal fade" id="dropoffTransportSelectionModal" tabindex="-1" aria-labelledby="dropoffTransportSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="dropoffTransportSelectionModalLabel">
                    <i class="ri-logout-circle-line me-2"></i>Dropoff Transport Service Selection
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="dropoffTransportSelectionForm" onsubmit="return false;">
                    <input type="hidden" id="modal_dropoff_transport_tour_id" name="tour_id">
                    <input type="hidden" id="modal_dropoff_transport_country" name="country">
                    <input type="hidden" id="modal_dropoff_transport_city" name="city">
                    <input type="hidden" id="modal_dropoff_transport_start_date" name="start_date">
                    <input type="hidden" id="modal_dropoff_transport_end_date" name="end_date">
                    
                    <div class="card border-success shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <div class="d-flex align-items-center">
                                <span class="service-icon me-3">
                                    <i class="ri-logout-circle-line fs-4"></i>
                                </span>
                                <div>
                                    <h6 class="mb-0 fw-bold">Dropoff Transport Services</h6>
                                    <small class="opacity-75">Configure pickup and dropoff locations</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body bg-white">
                            <div class="row g-4 align-items-end">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label fw-semibold text-muted mb-2">
                                            <i class="ri-map-pin-line text-success me-2"></i>City
                                        </label>
                                        <div class="position-relative">
                                            <select class="form-select border-2" id="modal_exitport_transport_city" name="city" style="padding-left: 45px;">
                                                <option value="">Select city</option>
                                                @foreach($cities as $city)
                                                <option value="{{ $city->name }}" data-city="{{ json_encode($city) }}">{{ $city->name }}</option>
                                                @endforeach
                                            </select>
                                            <i style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label fw-semibold text-muted mb-2">
                                            <i class="ri-map-pin-line text-success me-2"></i>Pick Up Location
                                        </label>
                                        <div class="position-relative">
                                            <select class="form-select pickup-zone-select border-2" id="modal_dropoff_transport_pickup_zone" name="pickup_zone_id" style="padding-left: 45px;">
                                                <option value="">Select pickup location</option>
                                                <!-- Hotels -->
                                                <optgroup label="Hotels">
                                                @foreach($hotels as $hotel)
                                                    <option value="{{ $hotel->hotel_unique_id }}" data-hotel="{{ json_encode($hotel) }}">{{ $hotel->name }}</option>
                                                @endforeach
                                                </optgroup>
                                                
                                                <!-- Attractions -->
                                                <optgroup label="Attractions">
                                                @foreach($attractions as $attraction)
                                                <option value="{{ $attraction->attraction_id }}" data-attraction="{{ json_encode($attraction) }}">{{ $attraction->name }}</option>
                                                @endforeach
                                                </optgroup>
                                                
                                                <!-- Restaurants -->
                                                <optgroup label="Restaurants">
                                                @foreach($restaurants as $restaurant)
                                                <option value="{{ $restaurant->restaurant_id }}" data-restaurant="{{ json_encode($restaurant) }}">{{ $restaurant->name }}</option>
                                                @endforeach
                                                </optgroup>
                                            </select>
                                            <i style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label fw-semibold text-muted mb-2">
                                            <i class="ri-flag-line text-danger me-2"></i>Drop Off Location
                                        </label>
                                        <div class="position-relative">
                                            <select class="form-select dropoff-zone-select border-2" id="modal_dropoff_transport_dropoff_zone" name="dropoff_zone_id" style="padding-left: 45px;">
                                                <option value="">Select dropoff location</option>
                                                @foreach($ports as $port)
                                                    <option value="{{ $port->port_id }}" data-port="{{ json_encode($port) }}">{{ $port->port_name }}</option>
                                                @endforeach
                                            </select>
                                            <i class="ri-flag-fill position-absolute text-danger" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label fw-semibold text-muted mb-2">
                                            <i class="ri-time-line text-warning me-2"></i>Pick Up Time
                                        </label>
                                        <div class="position-relative">
                                            <input type="time" class="form-control border-2" id="modal_dropoff_transport_pickup_time" name="pickup_time" style="padding-left: 45px;">
                                            <i style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label fw-semibold text-muted mb-2">
                                            <i class="ri-calendar-line text-primary me-2"></i>Pick Up Date
                                        </label>
                                        <div class="position-relative">
                                            <input type="date" class="form-control border-2" id="modal_dropoff_transport_pickup_date" name="pickup_date" value="{{ \Carbon\Carbon::parse($tour->check_out_time)->format('Y-m-d') }}" min="{{ \Carbon\Carbon::parse($tour->check_in_time)->format('Y-m-d') }}" max="{{ \Carbon\Carbon::parse($tour->check_out_time)->format('Y-m-d') }}" disabled style="padding-left: 45px;">
                                            <i class="ri-calendar-fill position-absolute text-primary" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-success w-100 py-2" onclick="searchDropoffVehicles()" id="dropoff_transport_search_btn" disabled>
                                        <i class="ri-search-line me-2"></i>Search Vehicles
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Vehicle Results Section (Hidden Initially) -->
                    <div class="row" id="dropoff_vehicle_results" style="display: none;">
                        <div class="col-12">
                            <div class="alert alert-success">
                                <div class="d-flex align-items-center">
                                    <i class="ri-car-line me-2 fs-4"></i>
                                    <div>
                                        <strong>Available Vehicles</strong>
                                        <div class="small text-muted">Select your preferred vehicle and service type below</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vehicle + Service Type in one row -->
                        <div class="col-12">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">Vehicle</label>
                                    <select class="form-select vehicle-select" 
                                            id="modal_dropoff_transport_vehicle_id" 
                                            name="vehicle_id" 
                                            onchange="updateDropoffVehicleDetails()">
                                        <option value="">Choose vehicle</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Service Type</label>
                                    <select class="form-select" 
                                            id="modal_dropoff_transport_service_type" 
                                            name="service_type" 
                                            onchange="updateDropoffPricing()" disabled>
                                        <option value="">Select service type</option>
                                        
                                    </select>
                                </div>

                                <!-- Manual Price Input (Available for both Zone On and Point-to-Point) -->
                                @if(isset($UserDmc->zone_on) && $UserDmc->zone_on == 0)
                                <div class="col-md-4" id="dropoff_transport_manual_price_field_container" style="display: none;">
                                    <label class="form-label fw-semibold">
                                        <i class="ri-money-dollar-circle-line text-success me-1"></i>Manual Price (Optional)
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" 
                                               class="form-control" 
                                               id="modal_dropoff_transport_manual_price" 
                                               name="manual_price" 
                                               step="0.01" 
                                               min="0" 
                                               placeholder="0.00"
                                               onchange="updateDropoffPricing()"
                                               oninput="updateDropoffPricing()">
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="ri-information-line me-1"></i>
                                        Override vehicle price with custom amount
                                    </small>
                                </div>
                                @endif
                            </div>
                            
                            <!-- Guest Information -->
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label fw-semibold">Number of Passengers</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-user-line"></i></span>
                                            <input type="number" class="form-control" id="modal_dropoff_transport_passengers" name="passengers" min="1" max="{{ ($tour->adult ?? 0) + ($tour->child ?? 0) }}" value="1" onkeyup="updateDropoffPricing()" onchange="updateDropoffPricing()">
                                        </div>
                                        <small class="form-text text-muted">
                                            Maximum passengers: {{ ($tour->adult ?? 0) + ($tour->child ?? 0) }} ({{ $tour->adult ?? 0 }} adults + {{ $tour->child ?? 0 }} children)
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Price Display for Dropoff Transport -->
                            <div class="col-12 mt-3">
                                <div id="dropoff_transport_price_display" class="alert alert-success" style="display: none;">
                                    <div class="d-flex align-items-center">
                                        <i class="ri-money-dollar-circle-line me-2 fs-4"></i>
                                        <div>
                                            <strong>Price Information</strong>
                                            <div id="dropoff_transport_price_details" class="small">Select a vehicle and service type to see pricing</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Hidden fields for dropoff transport pricing -->
                                <input type="hidden" name="dropoff_transport_base_price" id="dropoff_transport_base_price" value="0">
                                <input type="hidden" name="dropoff_transport_total_price" id="dropoff_transport_total_price" value="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" onclick="confirmDropoffTransportSelection()">
                            <i class="ri-check-line me-1"></i>Confirm Dropoff Transport Selection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End of Dropoff Transport Selection Modal -->

@endsection

@section('scripts')
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // Initialize toastr with default options
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 3000,
            extendedTimeOut: 1000,
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut',
            preventDuplicates: true,
            tapToDismiss: true
        };
    }

    // Initialize Select2 for all select boxes with class 'form-select' and 'form-control'
    // This makes all select boxes searchable without breaking existing functionality
    $(document).ready(function() {
        initializeAllSelect2();
        initializeTravelDateValidation();
    });

    // Initialize travel date validation
    function initializeTravelDateValidation() {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        
        if (!startDateInput || !endDateInput) return;
        
        // Set minimum date for start_date to today if not already set
        if (!startDateInput.min) {
            const today = new Date().toISOString().split('T')[0];
            startDateInput.min = today;
        }
        
        // Update end_date minimum when start_date changes
        startDateInput.addEventListener('change', function() {
            const startDateValue = this.value;
            if (startDateValue) {
                // Set end_date minimum to the selected start_date
                endDateInput.min = startDateValue;
                
                // If end_date is before start_date, update it to start_date
                if (endDateInput.value && endDateInput.value < startDateValue) {
                    endDateInput.value = startDateValue;
                }
            } else {
                // If start_date is cleared, set end_date min to today
                const today = new Date().toISOString().split('T')[0];
                endDateInput.min = today;
            }
        });
        
        // Validate end_date when it changes
        endDateInput.addEventListener('change', function() {
            const startDateValue = startDateInput.value;
            const endDateValue = this.value;
            
            if (startDateValue && endDateValue && endDateValue < startDateValue) {
                // If end_date is before start_date, reset it to start_date
                this.value = startDateValue;
                showToastr('error', 'End date cannot be before start date.');
            }
        });
        
        // Initialize end_date min on page load based on current start_date value
        const today = new Date().toISOString().split('T')[0];
        if (startDateInput.value) {
            // Use the later of start_date or today as minimum
            endDateInput.min = startDateInput.value > today ? startDateInput.value : today;
        } else {
            endDateInput.min = today;
        }
        
        // Also ensure start_date min is at least today (even if it has an old date)
        startDateInput.min = today;
    }

    // Function to initialize Select2 on all select boxes
    function initializeAllSelect2(container = null) {
        const $container = container || $(document);
        
        // Find all select boxes with form-select or form-control class that are not already initialized
        $container.find('select.form-select, select.form-control').each(function() {
            const $select = $(this);
            
            // Skip if data-no-select2 attribute is present
            if ($select.attr('data-no-select2') === 'true') {
                return;
            }
            
            // Skip if already initialized with Select2
            if ($select.data('select2')) {
                return;
            }
            
            // Get placeholder from first option if it exists
            const firstOption = $select.find('option:first');
            let placeholder = 'Select an option';
            if (firstOption.length && firstOption.val() === '') {
                placeholder = firstOption.text() || 'Select an option';
            }
            
            // Check if it's a multiple select
            const isMultiple = $select.prop('multiple');
            
            // Get the closest modal for dropdown parent (fixes z-index issues in modals)
            const $modal = $select.closest('.modal');
            const dropdownParent = $modal.length ? $modal : $('body');
            
            // Initialize Select2
            // Note: Select2 automatically triggers native 'change' events, so onchange handlers will work
            $select.select2({
                theme: 'bootstrap-5',
                placeholder: placeholder,
                allowClear: !isMultiple && !$select.prop('required'),
                width: '100%',
                closeOnSelect: isMultiple ? false : true,
                dropdownParent: dropdownParent
            });
            
            // Mark as initialized to avoid re-initialization
            $select.attr('data-select2-initialized', 'true');
        });
    }

    // Reinitialize Select2 when modals are shown (for dynamically loaded content)
    $(document).on('shown.bs.modal', '.modal', function() {
        const $modal = $(this);
        // Small delay to ensure content is fully rendered
        setTimeout(function() {
            initializeAllSelect2($modal);
        }, 100);
    });

    // Reinitialize Select2 when select boxes are enabled (they might be disabled initially)
    $(document).on('DOMSubtreeModified', function() {
        // This is a fallback for very dynamic content
        // Use MutationObserver in modern browsers for better performance
    });

    // Use MutationObserver for better performance (modern browsers)
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    $(mutation.addedNodes).find('select.form-select, select.form-control').each(function() {
                        const $select = $(this);
                        if (!$select.data('select2') && !$select.attr('data-select2-initialized') && $select.attr('data-no-select2') !== 'true') {
                            setTimeout(function() {
                                initializeAllSelect2($select.parent());
                            }, 50);
                        }
                    });
                }
            });
        });
        
        // Start observing
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Make initializeAllSelect2 available globally so it can be called manually when needed
    window.initializeAllSelect2 = initializeAllSelect2;
    
    // Helper function to refresh Select2 after options are updated (call this after innerHTML changes)
    window.refreshSelect2 = function(selectElementOrId) {
        const $select = typeof selectElementOrId === 'string' 
            ? $('#' + selectElementOrId) 
            : $(selectElementOrId);
        
        if ($select.length && $select.data('select2')) {
            // Destroy existing Select2
            $select.select2('destroy');
            $select.removeAttr('data-select2-initialized');
            // Reinitialize
            initializeAllSelect2($select.parent());
        } else if ($select.length) {
            // Just initialize if not already initialized
            initializeAllSelect2($select.parent());
        }
    };
    
    // Watch for selects that become enabled (check periodically)
    setInterval(function() {
        $('select.form-select:not([data-select2-initialized]):not(:disabled):not([data-no-select2]), select.form-control:not([data-select2-initialized]):not(:disabled):not([data-no-select2])').each(function() {
            const $select = $(this);
            if (!$select.data('select2')) {
                initializeAllSelect2($select.parent());
                $select.attr('data-select2-initialized', 'true');
            }
        });
    }, 1000);
</script>
<script>
    // Accessibility and Focus Management Utilities
    function handleModalClose(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        // Remove focus from any focused elements inside the modal before closing
        const focusedElement = modal.querySelector(':focus');
        if (focusedElement) {
            focusedElement.blur();
        }
        
        // Add event listener for when modal is being hidden
        modal.addEventListener('hide.bs.modal', function() {
            // Remove focus from any elements that might still have it
            const stillFocused = this.querySelector(':focus');
            if (stillFocused) {
                stillFocused.blur();
            }
        });
        
        // Add event listener for when modal is completely hidden
        modal.addEventListener('hidden.bs.modal', function() {
            // Clean up any remaining focus issues
            const anyFocused = this.querySelector(':focus');
            if (anyFocused) {
                anyFocused.blur();
            }
            // Return focus to body or triggering element
            document.body.focus();
        });
    }
    
    // Initialize modal accessibility for all modals
    function initializeModalAccessibility() {
        const modalIds = [
            'hotelBookingModal',
            'guideSelectionModal', 
            'restaurantSelectionModal',
            'attractionSelectionModal',
            'localTransferSelectionModal',
            'dropoffTransportSelectionModal',
            'transportSelectionModal'
        ];
        
        modalIds.forEach(modalId => {
            handleModalClose(modalId);
        });
    }
    
    // Safe modal close function that handles focus properly
    function safeCloseModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        // Remove focus from any focused elements inside the modal
        const focusedElement = modal.querySelector(':focus');
        if (focusedElement) {
            focusedElement.blur();
        }
        
        // Get the Bootstrap modal instance and hide it
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) {
            modalInstance.hide();
        } else {
            // Fallback: create new instance and hide
            const newModalInstance = new bootstrap.Modal(modal);
            newModalInstance.hide();
        }
    }
    
    // Safe modal open function that handles focus properly
    function safeOpenModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return null;
        
        // Create or get existing modal instance
        let modalInstance = bootstrap.Modal.getInstance(modal);
        if (!modalInstance) {
            modalInstance = new bootstrap.Modal(modal);
        }
        
        // Show the modal
        modalInstance.show();
        
        return modalInstance;
    }

    // Function to update service type options based on vehicle sharable property
    function updateServiceTypeOptions(vehicleData, serviceTypeSelectId) {
        const serviceTypeSelect = document.getElementById(serviceTypeSelectId);
        if (!serviceTypeSelect) {
            console.log('Service type select not found for ID:', serviceTypeSelectId);
            return;
        }

        console.log('updateServiceTypeOptions called with:', serviceTypeSelectId);
        console.log('Vehicle data updateServiceTypeOptions:', vehicleData);
        console.log('vehicleData.sharable:', vehicleData.sharable);
        
        // Check if this is for local transfer and what type
        const currentServiceType = window.currentLocalTransferServiceType || 'local_transfer';
        console.log('Current service type:', currentServiceType);
        
        // Clear existing options
        serviceTypeSelect.innerHTML = '<option value="">Select service type</option>';
        console.log('Cleared service type options');
        
        // For point-to-point and hourly, only show Private option
        if (currentServiceType === 'point_to_point' || currentServiceType === 'hourly') {
            console.log('Point-to-point or Hourly mode: Adding Private option only');
            const privateOption = document.createElement('option');
            privateOption.value = 'Private';
            privateOption.textContent = 'Private';
            serviceTypeSelect.appendChild(privateOption);
            
            // Auto-select Private since it's the only option
            serviceTypeSelect.value = 'Private';
            serviceTypeSelect.disabled = false;
            console.log('Service type options updated (Private only for point-to-point/hourly) and auto-selected');
            
            // Trigger pricing update after auto-selection
            if (serviceTypeSelectId === 'local_transfer_service_type') {
                setTimeout(() => updateLocalTransferPricing(), 100);
            }
            
            return;
        }
        
        // For local transfer (zone-based), use sharable property
        // Based on sharable field: 1=Private only, 2=Shared only, 3=Both
        if (vehicleData.sharable == 1) {
            // Private only
            console.log('Adding Private option only (sharable == 1)');
            const privateOption = document.createElement('option');
            privateOption.value = 'Private';
            privateOption.textContent = 'Private';
            serviceTypeSelect.appendChild(privateOption);
        }
        else if (vehicleData.sharable == 2) {
            // Shared only
            console.log('Adding Shared option only (sharable == 2)');
            const sharedOption = document.createElement('option');
            sharedOption.value = 'Shared';
            sharedOption.textContent = 'Shared';
            serviceTypeSelect.appendChild(sharedOption);
        }
        else if (vehicleData.sharable == 3) {
            // Both Private and Shared
            console.log('Adding both Private and Shared options (sharable == 3)');
            const privateOption = document.createElement('option');
            privateOption.value = 'Private';
            privateOption.textContent = 'Private';
            serviceTypeSelect.appendChild(privateOption);

            const sharedOption = document.createElement('option');
            sharedOption.value = 'Shared';
            sharedOption.textContent = 'Shared';
            serviceTypeSelect.appendChild(sharedOption);
        }
        else {
            // Default: show both options if sharable value is not set or invalid
            console.log('Adding both options as default (sharable not 1, 2, or 3)');
            const privateOption = document.createElement('option');
            privateOption.value = 'Private';
            privateOption.textContent = 'Private';
            serviceTypeSelect.appendChild(privateOption);

            const sharedOption = document.createElement('option');
            sharedOption.value = 'Shared';
            sharedOption.textContent = 'Shared';
            serviceTypeSelect.appendChild(sharedOption);
        }
        serviceTypeSelect.disabled = false;
        console.log('Service type options updated for local transfer');
    }

    // Initialize on DOM content loaded
    document.addEventListener('DOMContentLoaded', function() {
        initializeModalAccessibility();
        // Initialize person selector with default max occupancy of 2 (no extra bed by default)
        generatePersonSelector(2, false);
        // Initialize restaurant edit forms
        initializeRestaurantEditForms();
        initializeLocalTransportEditForms();
        // Initialize attraction time slot selects for existing forms
        initializeExistingAttractionTimeSlots();
    });
    
    // Function to populate time slot select from attraction data
    function populateTimeSlotFromAttraction(attractionSelect, timeSlotSelect, currentValue = '') {
        if (!attractionSelect || !timeSlotSelect) return;
        
        const selectedOption = attractionSelect.options[attractionSelect.selectedIndex];
        if (!selectedOption || !selectedOption.getAttribute('data-attraction-data')) {
            timeSlotSelect.innerHTML = '<option value="">Select Time Slot</option>';
            return;
        }
        
        try {
            const attractionData = JSON.parse(selectedOption.getAttribute('data-attraction-data'));
            timeSlotSelect.innerHTML = '<option value="">Select Time Slot</option>';
            
            // Use time_slots from database if available
            if (attractionData.time_slots && Array.isArray(attractionData.time_slots) && attractionData.time_slots.length > 0) {
                attractionData.time_slots.forEach(timeSlot => {
                    const timeOption = document.createElement('option');
                    const slotValue = timeSlot.slot || timeSlot.open || '';
                    const slotText = timeSlot.slot || (timeSlot.open + (timeSlot.close ? ' - ' + timeSlot.close : ''));
                    timeOption.value = slotValue;
                    timeOption.textContent = slotText;
                    if (currentValue && (slotValue === currentValue || slotText === currentValue)) {
                        timeOption.selected = true;
                    }
                    timeSlotSelect.appendChild(timeOption);
                });
            } else if (attractionData.open_time && attractionData.close_time) {
                // Fallback: generate time slot from open_time and close_time
                let openTimes = [];
                let closeTimes = [];
                
                // Parse open_time
                if (Array.isArray(attractionData.open_time)) {
                    openTimes = attractionData.open_time;
                } else if (typeof attractionData.open_time === 'string') {
                    try {
                        const parsed = JSON.parse(attractionData.open_time);
                        openTimes = Array.isArray(parsed) ? parsed : [attractionData.open_time];
                    } catch {
                        openTimes = [attractionData.open_time];
                    }
                }
                
                // Parse close_time
                if (Array.isArray(attractionData.close_time)) {
                    closeTimes = attractionData.close_time;
                } else if (typeof attractionData.close_time === 'string') {
                    try {
                        const parsed = JSON.parse(attractionData.close_time);
                        closeTimes = Array.isArray(parsed) ? parsed : [attractionData.close_time];
                    } catch {
                        closeTimes = [attractionData.close_time];
                    }
                }
                
                // Generate time slots
                if (openTimes.length > 0 && closeTimes.length > 0) {
                    openTimes.forEach((openTime, index) => {
                        const closeTime = closeTimes[index] || closeTimes[0];
                        const timeOption = document.createElement('option');
                        const slotValue = openTime + ' - ' + closeTime;
                        timeOption.value = slotValue;
                        timeOption.textContent = slotValue;
                        if (currentValue && (slotValue === currentValue || slotValue.includes(currentValue))) {
                            timeOption.selected = true;
                        }
                        timeSlotSelect.appendChild(timeOption);
                    });
                }
            }
            
            // If no time slots found from database, show message
            if (timeSlotSelect.options.length === 1) {
                const noSlotOption = document.createElement('option');
                noSlotOption.value = '';
                noSlotOption.textContent = 'No time slots available for this attraction';
                noSlotOption.disabled = true;
                timeSlotSelect.appendChild(noSlotOption);
            }
        } catch (error) {
            console.error('Error parsing attraction data:', error);
            timeSlotSelect.innerHTML = '<option value="">Error loading time slots</option>';
        }
    }
    
    // Function to populate ticket select from attraction data
    function populateTicketFromAttraction(attractionSelect, ticketSelectId, currentValue = '') {
        if (!attractionSelect) return;
        
        const ticketSelect = typeof ticketSelectId === 'string' ? document.getElementById(ticketSelectId) : ticketSelectId;
        if (!ticketSelect) return;
        
        const selectedOption = attractionSelect.options[attractionSelect.selectedIndex];
        if (!selectedOption || !selectedOption.getAttribute('data-attraction-data')) {
            ticketSelect.innerHTML = '<option value="">Select Ticket</option>';
            return;
        }
        
        try {
            const attractionData = JSON.parse(selectedOption.getAttribute('data-attraction-data'));
            ticketSelect.innerHTML = '<option value="">Select Ticket</option>';
            
            // Populate tickets from attraction data
            if (attractionData.tickets && Array.isArray(attractionData.tickets) && attractionData.tickets.length > 0) {
                attractionData.tickets.forEach(ticket => {
                    const ticketOption = document.createElement('option');
                    // Use ticket name as value, or ticket_id if name is not available
                    const ticketValue = ticket.name || ticket.ticket_name || ticket.ticket_id || '';
                    const ticketText = ticket.name || ticket.ticket_name || ticketValue;
                    ticketOption.value = ticketValue;
                    ticketOption.textContent = ticketText;
                    
                    // Set selected if it matches current value
                    if (currentValue && (ticketValue === currentValue || ticketText === currentValue)) {
                        ticketOption.selected = true;
                    }
                    
                    ticketSelect.appendChild(ticketOption);
                });
            } else {
                // If no tickets found, show message
                const noTicketOption = document.createElement('option');
                noTicketOption.value = '';
                noTicketOption.textContent = 'No tickets available for this attraction';
                noTicketOption.disabled = true;
                ticketSelect.appendChild(noTicketOption);
            }
        } catch (error) {
            console.error('Error parsing attraction data for tickets:', error);
            ticketSelect.innerHTML = '<option value="">Error loading tickets</option>';
        }
    }
    
    // Initialize time slot selects for existing attraction forms
    function initializeExistingAttractionTimeSlots() {
        // Find all attraction select boxes in existing forms
        const attractionSelects = document.querySelectorAll('select[id^="attraction_name_"]');
        
        attractionSelects.forEach(attractionSelect => {
            const bookingId = attractionSelect.id.replace('attraction_name_', '');
            // Try both visit_time and time_slot field names
            const timeSlotSelect = document.getElementById(`visit_time_${bookingId}`) || document.getElementById(`time_slot_${bookingId}`);
            const ticketSelect = document.getElementById(`ticket_name_${bookingId}`);
            
            if (timeSlotSelect) {
                // Populate on page load if attraction is already selected
                if (attractionSelect.value) {
                    const currentTimeSlot = timeSlotSelect.value || '';
                    populateTimeSlotFromAttraction(attractionSelect, timeSlotSelect, currentTimeSlot);
                }
                
                // Add change event listener
                attractionSelect.addEventListener('change', function() {
                    populateTimeSlotFromAttraction(attractionSelect, timeSlotSelect);
                });
            }
            
            // Initialize tickets on page load if attraction is already selected
            if (ticketSelect && attractionSelect.value) {
                const currentTicket = ticketSelect.value || '';
                populateTicketFromAttraction(attractionSelect, ticketSelect, currentTicket);
            }
        });
    }
    

    // Essential functions for edit page functionality
    // Show notification function
    function showNotification(message, type = 'info') {
        const alertClass = type === 'error' ? 'alert-danger' : 
                         type === 'success' ? 'alert-success' : 
                         type === 'warning' ? 'alert-warning' : 'alert-info';
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert ${alertClass} alert-dismissible fade show fixed-alert`;
        alertDiv.style.cssText = 'z-index: 9999 !important; position: fixed !important; top: 20px !important; right: 20px !important; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Add to body instead of container for fixed positioning
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
        
        // Also remove when manually dismissed
        alertDiv.addEventListener('closed.bs.alert', function() {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        });
    }
    
    // Service addition functions
    function addHotelService() {
        const tourId = document.getElementById('tour_id').value;
        const country = document.getElementById('user_country').value;
        
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (!tourId) {
            showNotification('Tour ID is required', 'error');
            return;
        }
        
        // Show modal first to ensure elements are in DOM
        const modal = safeOpenModal('hotelBookingModal');
        
        // Use setTimeout to ensure modal is fully rendered before accessing elements
        setTimeout(() => {
            // Populate modal with tour data - with null checks
            const modalTourId = document.getElementById('modal_tour_id');
            const modalUserCountry = document.getElementById('modal_user_country');
            const modalTourDates = document.getElementById('modal_tour_dates');
            const modalDestination = document.getElementById('modal_destination');
            
            if (modalTourId) modalTourId.value = tourId;
            if (modalUserCountry) modalUserCountry.value = country;
            if (modalTourDates) modalTourDates.textContent = `${startDate} to ${endDate}`;
            // Set date range constraints - with null checks
            const checkInDate = document.getElementById('check_in_date');
            const checkOutDate = document.getElementById('check_out_date');
            
            if (checkInDate && checkOutDate) {
                checkInDate.min = startDate;
                checkInDate.max = endDate;
                checkOutDate.min = startDate;
                checkOutDate.max = endDate;
                
                // Set default dates
                checkInDate.value = startDate;
                checkOutDate.value = endDate;
            }
            
        // Initialize modal functionality
        initializeHotelModal();
        
            // Auto-load hotels for the tour destination
        setTimeout(() => {
                // Hotels will be loaded based on destination country
            }, 300);
    }, 100); // Small delay to ensure modal is rendered
    }
    
    function addGuideService() {
        const tourId = document.getElementById('tour_id').value;
        const country = document.getElementById('user_country').value;
        
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (!tourId) {
            showNotification('Tour ID is required', 'error');
            return;
        }
        
        // Show guide selection modal
        showGuideSelectionModal(tourId, country, startDate, endDate);
    }
    
    // Attraction Selection Modal Functions
    function showAttractionSelectionModal(tourId, country, startDate, endDate) {
        console.log('showAttractionSelectionModal called with:', { tourId, country, startDate, endDate });
        
        // Populate modal with tour data
        document.getElementById('modal_attraction_tour_dates').textContent = `${startDate} to ${endDate}`;
        document.getElementById('modal_attraction_destination').textContent = `${country}`;
        // City display removed
        
        // Show modal
        const modalElement = document.getElementById('attractionSelectionModal');
        if (!modalElement) {
            console.error('Attraction modal element not found!');
            showNotification('Modal element not found', 'error');
            return;
        }
        
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
        
        console.log('Modal shown successfully');
        
        // Initialize modal functionality after modal is shown and Select2 is initialized
        setTimeout(() => {
            initializeAttractionModal();
            
            // Ensure city select change works with Select2
            const citySelect = document.getElementById('modal_attraction_city_select');
            if (citySelect) {
                const $citySelect = $(citySelect);
                if ($citySelect.data('select2')) {
                    $citySelect.off('select2:select select2:change').on('select2:select select2:change', function(e) {
                        const city = $(this).val();
                        const country = $(this).find('option:selected').data('country') || '';
                        loadAttractionsForCity(city, country);
                    });
                }
            }
            
            // Ensure event listeners are attached after Select2 initialization
            // Select2 is initialized in shown.bs.modal event with 100ms delay, so we wait a bit more
            setTimeout(() => {
                const attractionSelect = document.getElementById('modal_attraction_select');
                if (attractionSelect) {
                    const $attractionSelect = $(attractionSelect);
                    // Re-attach event listeners in case Select2 was initialized after our first attempt
                    if ($attractionSelect.data('select2')) {
                        $attractionSelect.off('select2:select select2:change').on('select2:select select2:change', function(e) {
                            onAttractionSelection();
                        });
                    }
                }
            }, 200);
        }, 100);
        
        // Load attractions for the city
        // loadAttractionsForCity(city, country);
    }
    
    function initializeAttractionModal() {
        // Check if already initialized to prevent duplicate event listeners
        if (window.attractionModalInitialized) {
            // Just update the guest data
            const adults = parseInt(document.getElementById('adults')?.value) || 1;
            const children = parseInt(document.getElementById('children')?.value) || 0;
            const infants = parseInt(document.getElementById('infants')?.value) || 0;
            const maleCount = parseInt(document.getElementById('male_count')?.value) || 1;
            const femaleCount = parseInt(document.getElementById('female_count')?.value) || 0;
            const pax = adults + children;
            
            window.attractionModalGuestData = {
                pax: pax.toString(),
                adults: adults.toString(),
                children: children.toString(),
                infants: infants.toString(),
                male_count: maleCount.toString(),
                female_count: femaleCount.toString(),
                child_ages: (function() {
                    const hiddenField = document.getElementById('child_ages');
                    if (hiddenField && hiddenField.value) {
                        return hiddenField.value;
                    }
                    const children = parseInt(document.getElementById('children')?.value) || 0;
                    const childAgeValues = [];
                    for (let i = 1; i <= children; i++) {
                        const ageSelect = document.getElementById(`tour_child_age_${i}`);
                        if (ageSelect && ageSelect.value) {
                            childAgeValues.push(ageSelect.value);
                        }
                    }
                    return childAgeValues.join(',');
                })()
            };
            
            // Update the modal display with current guest data
            updateModalGuestDisplay();
            validateAttractionForm();
            return;
        }
        
        // Mark as initialized
        window.attractionModalInitialized = true;
        
        // Add event listeners only for elements that exist
        const citySelect = document.getElementById('modal_attraction_city_select');
        const attractionSelect = document.getElementById('modal_attraction_select');
        const timeSlotSelect = document.getElementById('modal_attraction_time_slot');
        const ticketSelect = document.getElementById('modal_attraction_ticket');
        const visitDateSelect = document.getElementById('modal_attraction_visit_date');
        const confirmBtn = document.getElementById('confirm_attraction_btn');
        
        // City select event handler - works with both native and Select2
        if (citySelect) {
            const $citySelect = $(citySelect);
            $citySelect.off('select2:select select2:change change');
            $citySelect.on('select2:select select2:change', function(e) {
                const city = $(this).val();
                const country = $(this).find('option:selected').data('country') || '';
                loadAttractionsForCity(city, country);
            });
            citySelect.addEventListener('change', function() {
                const city = this.value;
                const country = this.options[this.selectedIndex].dataset.country || '';
                loadAttractionsForCity(city, country);
            });
        }
        
        if (attractionSelect) {
            // Use jQuery Select2 event for Select2 dropdowns, fallback to native change
            const $attractionSelect = $(attractionSelect);
            // Remove any existing event listeners to avoid duplicates
            $attractionSelect.off('select2:select select2:change change');
            // Attach Select2 event (works with Select2)
            $attractionSelect.on('select2:select select2:change', function(e) {
                onAttractionSelection();
            });
            // Also attach native change event as fallback
            attractionSelect.addEventListener('change', function() {
                onAttractionSelection();
            });
        }
        if (timeSlotSelect) {
            timeSlotSelect.addEventListener('change', validateAttractionForm);
        }
        if (ticketSelect) {
            // Use jQuery Select2 event for Select2 dropdowns, fallback to native change
            const $ticketSelect = $(ticketSelect);
            // Remove any existing event listeners to avoid duplicates
            $ticketSelect.off('select2:select change');
            // Attach Select2 event (works with Select2)
            $ticketSelect.on('select2:select', function(e) {
                onTicketSelection();
                validateAttractionForm();
            });
            // Also attach native change event as fallback
            ticketSelect.addEventListener('change', function() {
                onTicketSelection();
                validateAttractionForm();
            });
        }
        if (visitDateSelect) {
            visitDateSelect.addEventListener('change', validateAttractionForm);
        }
        if (confirmBtn) {
            confirmBtn.addEventListener('click', confirmAttractionSelection);
        }
        
        // Set date restrictions and default value
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (startDate && endDate && visitDateSelect) {
            visitDateSelect.min = startDate;
            visitDateSelect.max = endDate;
            visitDateSelect.value = startDate; // Default to start date
        }
        
        // Initialize guest data from tour data
        const adults = parseInt(document.getElementById('adults')?.value) || 1;
        const children = parseInt(document.getElementById('children')?.value) || 0;
        const infants = parseInt(document.getElementById('infants')?.value) || 0;
        const maleCount = parseInt(document.getElementById('male_count')?.value) || 1;
        const femaleCount = parseInt(document.getElementById('female_count')?.value) || 0;
        const pax = adults + children; // Calculate pax as adults + children
        
        // Set default guest data
        window.attractionModalGuestData = {
            pax: pax.toString(),
            adults: adults.toString(),
            children: children.toString(),
            infants: infants.toString(),
            male_count: maleCount.toString(),
            female_count: femaleCount.toString(),
            child_ages: (function() {
                const hiddenField = document.getElementById('child_ages');
                if (hiddenField && hiddenField.value) {
                    return hiddenField.value;
                }
                // Fallback: collect from individual tour_child_age_X selects if available
                const children = parseInt(document.getElementById('children')?.value) || 0;
                const childAgeValues = [];
                for (let i = 1; i <= children; i++) {
                    const ageSelect = document.getElementById(`tour_child_age_${i}`);
                    if (ageSelect && ageSelect.value) {
                        childAgeValues.push(ageSelect.value);
                    }
                }
                return childAgeValues.join(',');
            })()
        };
        
        // Update the modal display with initial guest data
        updateModalGuestDisplay();
    }
    
    function loadAttractionsForCity(city, country) {
        const attractionSelect = document.getElementById('modal_attraction_select');
        const attractionCount = document.getElementById('attraction_count');
        const timeSlotSelect = document.getElementById('modal_attraction_time_slot');
        const ticketSelect = document.getElementById('modal_attraction_ticket');
        
        if (!city) {
            // Clear if no city selected
            attractionSelect.innerHTML = '<option value="">Search Attraction</option>';
            attractionCount.textContent = '0';
            if (window.refreshSelect2) {
                window.refreshSelect2(attractionSelect);
            }
            return;
        }
        
        // Clear existing options
        attractionSelect.innerHTML = '<option value="">Search Attraction</option>';
        
        // For demo purposes, show sample attractions
        // In production, this would fetch from API
        const all_attractions = @json($attractions ?? []);
        console.log('All Attractions:', all_attractions);
        const attractions = all_attractions.filter(attraction => attraction.location == city);
        
        console.log('Attractions:', attractions);
        // Add attraction options
        attractions.forEach(attraction => {
            const option = document.createElement('option');
            option.value = attraction.attraction_id;
            option.textContent = `${attraction.name} - ${attraction.location}`;
            option.setAttribute('data-attraction', JSON.stringify(attraction));
            attractionSelect.appendChild(option);
        });
        
        attractionCount.textContent = attractions.length;
        document.getElementById('modal_attraction_city').textContent = city;
        
        // Refresh Select2 if it's initialized
        if (window.refreshSelect2) {
            window.refreshSelect2(attractionSelect);
        }
        
        // Clear time slot and ticket when city changes
        timeSlotSelect.innerHTML = '<option value="">Select Attraction First</option>';
        ticketSelect.innerHTML = '<option value="">Select Ticket</option>';
        document.getElementById('attraction_details_container').style.display = 'none';
    }
    
    function onAttractionSelection() {
        const attractionSelect = document.getElementById('modal_attraction_select');
        if (!attractionSelect) return;
        
        const attractionDetailsContainer = document.getElementById('attraction_details_container');
        const timeSlotSelect = document.getElementById('modal_attraction_time_slot');
        const ticketSelect = document.getElementById('modal_attraction_ticket');
        
        // Get selected value - works with both native select and Select2
        const selectedValue = attractionSelect.value;
        const selectedOption = attractionSelect.options[attractionSelect.selectedIndex];
        
        // Clear dependent dropdowns
        if (timeSlotSelect) {
            timeSlotSelect.innerHTML = '<option value="">Select Time Slot</option>';
        }
        if (ticketSelect) {
            ticketSelect.innerHTML = '<option value="">Select Ticket</option>';
        }
        
        // Clear ticket price display
        const ticketPriceDisplay = document.getElementById('modal_attraction_ticket_prices');
        if (ticketPriceDisplay) {
            ticketPriceDisplay.textContent = '';
        }
        
        if (selectedValue && selectedOption && selectedOption.getAttribute('data-attraction')) {
            try {
                const attractionData = JSON.parse(selectedOption.getAttribute('data-attraction'));
                
                // Show attraction details
                const imageEl = document.getElementById('selected_attraction_image');
                const nameEl = document.getElementById('selected_attraction_name');
                const categoryEl = document.getElementById('selected_attraction_category');
                const locationEl = document.getElementById('selected_attraction_location');
                const ratingEl = document.getElementById('selected_attraction_rating');
                const priceRangeEl = document.getElementById('selected_attraction_price_range');
                
                if (imageEl) imageEl.src = attractionData.master_image || '/assets/images/default-attraction.png';
                if (nameEl) nameEl.textContent = attractionData.name;
                if (categoryEl) categoryEl.textContent = (attractionData.category || 'General') + ' Category';
                if (locationEl) locationEl.textContent = attractionData.location || '';
                if (ratingEl) ratingEl.textContent = attractionData.rating || 'N/A';
                if (priceRangeEl) priceRangeEl.textContent = attractionData.price_range || 'Price on request';
                
                console.log('Attraction selected:', attractionData);

                // Set Time Slot Options from attraction data
                if (timeSlotSelect) {
                    timeSlotSelect.innerHTML = '<option value="">Select Time Slot</option>';
                    
                    // Use time_slots from database if available
                    if (attractionData.time_slots && Array.isArray(attractionData.time_slots) && attractionData.time_slots.length > 0) {
                        attractionData.time_slots.forEach(timeSlot => {
                            const timeOption = document.createElement('option');
                            const slotValue = timeSlot.slot || (timeSlot.open + (timeSlot.close ? ' - ' + timeSlot.close : ''));
                            const slotText = timeSlot.slot || (timeSlot.open + (timeSlot.close ? ' - ' + timeSlot.close : ''));
                            timeOption.value = slotValue;
                            timeOption.textContent = slotText;
                            timeSlotSelect.appendChild(timeOption);
                        });
                    } else if (attractionData.open_time && attractionData.close_time) {
                        // Generate time slots from open_time and close_time arrays
                        let openTimes = [];
                        let closeTimes = [];
                        
                        // Parse open_time
                        if (Array.isArray(attractionData.open_time)) {
                            openTimes = attractionData.open_time;
                        } else if (typeof attractionData.open_time === 'string') {
                            try {
                                const parsed = JSON.parse(attractionData.open_time);
                                openTimes = Array.isArray(parsed) ? parsed : [attractionData.open_time];
                            } catch {
                                openTimes = [attractionData.open_time];
                            }
                        }
                        
                        // Parse close_time
                        if (Array.isArray(attractionData.close_time)) {
                            closeTimes = attractionData.close_time;
                        } else if (typeof attractionData.close_time === 'string') {
                            try {
                                const parsed = JSON.parse(attractionData.close_time);
                                closeTimes = Array.isArray(parsed) ? parsed : [attractionData.close_time];
                            } catch {
                                closeTimes = [attractionData.close_time];
                            }
                        }
                        
                        // Generate time slot ranges from database
                        if (openTimes.length > 0 && closeTimes.length > 0) {
                            openTimes.forEach((openTime, index) => {
                                const closeTime = closeTimes[index] || closeTimes[0];
                                if (openTime && closeTime) {
                                    const timeOption = document.createElement('option');
                                    const slotValue = openTime + ' - ' + closeTime;
                                    timeOption.value = slotValue;
                                    timeOption.textContent = slotValue;
                                    timeSlotSelect.appendChild(timeOption);
                                }
                            });
                        }
                    }
                    
                    // If no time slots found, show message
                    if (timeSlotSelect.options.length === 1) {
                        const noSlotOption = document.createElement('option');
                        noSlotOption.value = '';
                        noSlotOption.textContent = 'No time slots available for this attraction';
                        noSlotOption.disabled = true;
                        timeSlotSelect.appendChild(noSlotOption);
                    }
                    
                    // Refresh Select2 if initialized
                    if (window.refreshSelect2) {
                        window.refreshSelect2(timeSlotSelect);
                    }
                }
                
                // Set Ticket Options (based on selected attraction)
                if (ticketSelect && attractionData.tickets && attractionData.tickets.length > 0) {
                    attractionData.tickets.forEach(ticket => {
                        const ticketOption = document.createElement('option');
                        ticketOption.value = ticket.ticket_id;
                        ticketOption.textContent = `${ticket.name}`;
                        ticketOption.setAttribute('data-ticket', JSON.stringify(ticket));
                        ticketSelect.appendChild(ticketOption);
                    });
                    
                    // Refresh Select2 if initialized
                    if (window.refreshSelect2) {
                        window.refreshSelect2(ticketSelect);
                    }
                }
                
                if (attractionDetailsContainer) {
                    attractionDetailsContainer.style.display = 'block';
                }
            } catch (error) {
                console.error('Error parsing attraction data:', error);
                if (attractionDetailsContainer) {
                    attractionDetailsContainer.style.display = 'none';
                }
            }
        } else {
            if (attractionDetailsContainer) {
                attractionDetailsContainer.style.display = 'none';
            }
        }
        
        validateAttractionForm();
    }
    function onTicketSelection() {
        const ticketSelect = document.getElementById('modal_attraction_ticket');
        const ticketPriceDisplay = document.getElementById('modal_attraction_ticket_prices');
        const selectedOption = ticketSelect.options[ticketSelect.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            try {
                const ticketData = JSON.parse(selectedOption.getAttribute('data-ticket'));
                
                // Get price - use adult_price if available, otherwise try price, or default to 0
                const price = ticketData.adult_price || ticketData.price || 0;
                const priceText = price > 0 ? `$${parseFloat(price).toFixed(2)}` : 'Price on request';
                
                ticketPriceDisplay.textContent = `${ticketData.name} - ${priceText}`;
            } catch (error) {
                console.error('Error parsing ticket data:', error);
                ticketPriceDisplay.textContent = '';
            }
        } else {
            ticketPriceDisplay.textContent = '';
        }
    }
    
    function validateAttractionForm() {
        const attractionSelect = document.getElementById('modal_attraction_select');
        const timeSlotSelect = document.getElementById('modal_attraction_time_slot');
        const ticketSelect = document.getElementById('modal_attraction_ticket');
        const visitDateSelect = document.getElementById('modal_attraction_visit_date');
        const confirmBtn = document.getElementById('confirm_attraction_btn');
        
        let isValid = true;
        
        // Check required fields
        if (!attractionSelect.value) isValid = false;
        if (!timeSlotSelect.value) isValid = false;
        if (!ticketSelect.value) isValid = false;
        if (!visitDateSelect.value) isValid = false;
        
        confirmBtn.disabled = !isValid;
    }

    // Guest selector functions for attraction modal
    function openAttractionGuestSelector() {
        // Initialize guest selector modal with current values
        const guestData = window.attractionModalGuestData || {
            pax: '1',
            children: '0',
            infants: '0',
            male_count: '1',
            female_count: '0',
            child_ages: ''
        };
        
        // Set current values in the modal
        document.getElementById('attraction_modal_pax').value = guestData.pax;
        document.getElementById('attraction_modal_children').value = guestData.children;
        document.getElementById('attraction_modal_infants').value = guestData.infants;
        document.getElementById('attraction_modal_male_count').value = guestData.male_count;
        document.getElementById('attraction_modal_female_count').value = guestData.female_count;
        document.getElementById('attraction_modal_child_ages').value = guestData.child_ages;
        
        // Add event listeners for the modal inputs
        const modalInputs = ['attraction_modal_pax', 'attraction_modal_children', 'attraction_modal_infants', 'attraction_modal_male_count', 'attraction_modal_female_count', 'attraction_modal_child_ages'];
        modalInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('input', updateAttractionGuestSummary);
            }
        });
        
        // Update the summary display with current values
        updateAttractionGuestSummary();
        
        const modal = new bootstrap.Modal(document.getElementById('attractionGuestSelectorModal'));
        modal.show();
    }

    function incrementAttractionCount(fieldId) {
        const field = document.getElementById(fieldId);
        const currentValue = parseInt(field.value);
        const maxValue = parseInt(field.max);
        
        if (fieldId === 'attraction_modal_pax') {
            // For pax, just increment normally
            if (currentValue < maxValue) {
                field.value = currentValue + 1;
                updateAttractionGuestSummary();
            }
        } else {
            // For other fields, check if incrementing would exceed pax
            const paxValue = parseInt(document.getElementById('attraction_modal_pax').value);
            const childrenValue = parseInt(document.getElementById('attraction_modal_children').value);
            const maleValue = parseInt(document.getElementById('attraction_modal_male_count').value);
            const femaleValue = parseInt(document.getElementById('attraction_modal_female_count').value);
            
            let newValue = currentValue;
            if (fieldId === 'attraction_modal_children') {
                newValue = childrenValue + 1;
            } else if (fieldId === 'attraction_modal_male_count') {
                newValue = maleValue + 1;
            } else if (fieldId === 'attraction_modal_female_count') {
                newValue = femaleValue + 1;
            }
            
            // Check if the new total would exceed pax
            const totalAfterIncrement = newValue + (fieldId === 'attraction_modal_children' ? maleValue + femaleValue : 
                                                   fieldId === 'attraction_modal_male_count' ? childrenValue + femaleValue : 
                                                   childrenValue + maleValue);
            
            if (totalAfterIncrement <= paxValue && currentValue < maxValue) {
                field.value = currentValue + 1;
                updateAttractionGuestSummary();
            } else if (totalAfterIncrement > paxValue) {
                showNotification('Total of children, males, and females cannot exceed pax count', 'warning');
            }
        }
    }

    function decrementAttractionCount(fieldId) {
        const field = document.getElementById(fieldId);
        const currentValue = parseInt(field.value);
        const minValue = parseInt(field.min);
        
        if (fieldId === 'attraction_modal_pax') {
            // For pax, check if decrementing would make it less than the sum of other fields
            const childrenValue = parseInt(document.getElementById('attraction_modal_children').value);
            const maleValue = parseInt(document.getElementById('attraction_modal_male_count').value);
            const femaleValue = parseInt(document.getElementById('attraction_modal_female_count').value);
            const totalOthers = childrenValue + maleValue + femaleValue;
            
            if (currentValue > totalOthers && currentValue > minValue) {
                field.value = currentValue - 1;
                updateAttractionGuestSummary();
            } else if (currentValue <= totalOthers) {
                showNotification('Pax cannot be less than the sum of children, males, and females', 'warning');
            }
        } else {
            // For other fields, just decrement normally
            if (currentValue > minValue) {
                field.value = currentValue - 1;
                updateAttractionGuestSummary();
            }
        }
    }

    function updateModalGuestDisplay() {
        // Update the main modal guest display using data from window.attractionModalGuestData
        const guestData = window.attractionModalGuestData || {
            pax: '1',
            adults: '1',
            children: '0',
            infants: '0',
            male_count: '1',
            female_count: '0'
        };
        
        const pax = parseInt(guestData.pax);
        const adults = parseInt(guestData.adults);
        const children = parseInt(guestData.children);
        const infants = parseInt(guestData.infants);
        const maleCount = parseInt(guestData.male_count);
        const femaleCount = parseInt(guestData.female_count);
        
        const summary = `${pax} pax (${adults} adults, ${children} children) - ${maleCount} male, ${femaleCount} female - ${infants} infants`;
        
        // Update summary
        const summaryElement = document.getElementById('modal_attraction_guest_summary');
        if (summaryElement) {
            summaryElement.textContent = summary;
        }

        // Update badges
        const adultBadge = document.getElementById('modal_badge_adults');
        const childBadge = document.getElementById('modal_badge_children');
        const infantBadge = document.getElementById('modal_badge_infants');
        
        if (adultBadge) adultBadge.textContent = adults;
        if (childBadge) childBadge.textContent = children;
        if (infantBadge) infantBadge.textContent = infants;
    }
    
    function updateAttractionGuestSummary() {
        const pax = parseInt(document.getElementById('attraction_modal_pax').value);
        const children = parseInt(document.getElementById('attraction_modal_children').value);
        const infants = parseInt(document.getElementById('attraction_modal_infants').value);
        const maleCount = parseInt(document.getElementById('attraction_modal_male_count').value);
        const femaleCount = parseInt(document.getElementById('attraction_modal_female_count').value);
        const adults = pax - children; // Calculate adults as pax - children

        const summary = `${pax} pax (${adults} adults, ${children} children) - ${maleCount} male, ${femaleCount} female - ${infants} infants`;
        
        // Update summary if element exists
        const summaryElement = document.getElementById('modal_attraction_guest_summary');
        if (summaryElement) {
            summaryElement.textContent = summary;
        }

        // Update badges with IDs
        const adultBadge = document.getElementById('modal_badge_adults');
        const childBadge = document.getElementById('modal_badge_children');
        const infantBadge = document.getElementById('modal_badge_infants');
        
        if (adultBadge) adultBadge.textContent = adults;
        if (childBadge) childBadge.textContent = children;
        if (infantBadge) infantBadge.textContent = infants;

        // Enable/disable child ages field
        const childAgesField = document.getElementById('attraction_modal_child_ages');
        if (childAgesField) {
            if (children > 0) {
                childAgesField.disabled = false;
                childAgesField.required = true;
            } else {
                childAgesField.disabled = true;
                childAgesField.required = false;
                childAgesField.value = '';
            }
        }
        
        // Validate total doesn't exceed pax
        const total = children + maleCount + femaleCount;
        if (total > pax) {
            showNotification('Total of children, males, and females exceeds pax count', 'warning');
        }
        
        // Update the attraction modal guest data for pricing calculations
        window.attractionModalGuestData = {
            adults: adults.toString(),
            children: children.toString(),
            infants: infants.toString(),
            maleCount: maleCount.toString(),
            femaleCount: femaleCount.toString()
        };
        
        // Update pricing based on new guest counts
        updateAttractionPricing();
    }

    function confirmAttractionGuestSelection() {
        const pax = document.getElementById('attraction_modal_pax').value;
        const children = document.getElementById('attraction_modal_children').value;
        const infants = document.getElementById('attraction_modal_infants').value;
        const maleCount = document.getElementById('attraction_modal_male_count').value;
        const femaleCount = document.getElementById('attraction_modal_female_count').value;
        const childAges = document.getElementById('attraction_modal_child_ages').value;
        const adults = parseInt(pax) - parseInt(children); // Calculate adults

        // Store the values for use in attraction booking
        window.attractionModalGuestData = {
            pax: pax,
            adults: adults.toString(),
            children: children,
            infants: infants,
            male_count: maleCount,
            female_count: femaleCount,
            child_ages: childAges
        };

        updateAttractionGuestSummary();

        // Close modal safely
        safeCloseModal('attractionGuestSelectorModal');

        showNotification('Guest selection updated successfully', 'success');
    }

    function updateAttractionPricing() {
        const ticketSelect = document.getElementById('modal_attraction_ticket');
        if (!ticketSelect) return;
        
        const selectedOption = ticketSelect.options[ticketSelect.selectedIndex];
        const priceDisplay = document.getElementById('attraction_price_display');
        const priceDetails = document.getElementById('attraction_price_details');
        
        if (!priceDisplay || !priceDetails) return;
        
        if (selectedOption && selectedOption.value) {
            try {
                const ticketData = JSON.parse(selectedOption.getAttribute('data-ticket'));
                
                // Get the most current guest data
                // First try to get from the form inputs directly
                let adults = 0;
                let children = 0;
                
                if (window.attractionModalGuestData) {
                    adults = parseInt(window.attractionModalGuestData.adults) || 0;
                    children = parseInt(window.attractionModalGuestData.children) || 0;
                } else {
                    // Fallback to calculating from the form fields
                    const paxElem = document.getElementById('attraction_modal_pax');
                    const childrenElem = document.getElementById('attraction_modal_children');
                    
                    if (paxElem && childrenElem) {
                        const pax = parseInt(paxElem.value) || 0;
                        children = parseInt(childrenElem.value) || 0;
                        adults = pax - children;
                    } else {
                        adults = 1; // Default fallback
                        children = 0;
                    }
                }
                
                // Calculate prices
                const adultPrice = parseFloat(ticketData.adult_price || 0) * adults;
                const childPrice = parseFloat(ticketData.child_price || 0) * children;
                const totalPrice = adultPrice + childPrice;
                
                // Format prices to 2 decimal places
                const formattedAdultPrice = adultPrice.toFixed(2);
                const formattedChildPrice = childPrice.toFixed(2);
                const formattedTotalPrice = totalPrice.toFixed(2);
                
                priceDetails.innerHTML = `
                    <div class="row">
                        <div class="col-md-4">Adult Price: $${(ticketData.adult_price || 0).toFixed(2)} × ${adults} = $${formattedAdultPrice}</div>
                        <div class="col-md-4">Child Price: $${(ticketData.child_price || 0).toFixed(2)} × ${children} = $${formattedChildPrice}</div>
                        <div class="col-md-4"><strong>Total: $${formattedTotalPrice}</strong></div>
                    </div>
                `;
                
                // Update hidden fields
                const totalPriceField = document.getElementById('modal_attraction_total_price');
                const ticketIdField = document.getElementById('modal_attraction_ticket_id');
                const ticketNameField = document.getElementById('modal_attraction_ticket_name');
                
                if (totalPriceField) totalPriceField.value = formattedTotalPrice;
                if (ticketIdField) ticketIdField.value = ticketData.ticket_id;
                if (ticketNameField) ticketNameField.value = ticketData.name;
                
                priceDisplay.style.display = 'block';
            } catch (error) {
                console.error('Error updating attraction pricing:', error);
                priceDisplay.style.display = 'none';
            }
        } else {
            priceDisplay.style.display = 'none';
        }
    }
    
    // Transport Modal Functions
    function showTransportSelectionModal(tourId, country, startDate, endDate, transportType = 'entry_port') {
        console.log('Showing transport selection modal with data:', { tourId, country, startDate, endDate, transportType });
        
        // Set hidden fields
        document.getElementById('modal_transport_tour_id').value = tourId;
        document.getElementById('modal_transport_country').value = country;
        document.getElementById('modal_transport_type').value = transportType;
        // City field removed
        document.getElementById('modal_transport_start_date').value = startDate;
        document.getElementById('modal_transport_end_date').value = endDate;
        // Pickup date is now hardcoded and readonly in the HTML
        
        // Update modal title based on type
        const modalTitle = document.getElementById('transportSelectionModalLabel');
        if (transportType === 'exit_port') {
            modalTitle.innerHTML = '<i class="ri-logout-circle-line me-2"></i>Departure Transport Service Selection';
            modalTitle.closest('.modal-header').className = 'modal-header bg-danger text-white';
        } else {
            modalTitle.innerHTML = '<i class="ri-login-circle-line me-2"></i>Arrival Transport Service Selection';
            modalTitle.closest('.modal-header').className = 'modal-header bg-success text-white';
        }
        
        // Initialize the modal
        const transportModal = new bootstrap.Modal(document.getElementById('transportSelectionModal'));
        transportModal.show();
        
        // Initialize the transport modal with a slight delay to ensure DOM is ready
        setTimeout(() => {
            initializeTransportModal();
        }, 100);
    }
    
    // Function to add arrival service
    function addArrivalService() {
        const tourId = document.getElementById('tour_id').value;
        const country = document.getElementById('user_country').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (!tourId) {
            showNotification('Tour ID is required', 'error');
            return;
        }
        
        showTransportSelectionModal(tourId, country, startDate, endDate, 'entry_port');
    }
    
    // Function to add departure service
    function addDepartureService() {
        const tourId = document.getElementById('tour_id').value;
        const country = document.getElementById('user_country').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (!tourId) {
            showNotification('Tour ID is required', 'error');
            return;
        }
        
        showTransportSelectionModal(tourId, country, startDate, endDate, 'exit_port');
    }
    
    function showLocalTransferSelectionModal(tourId, country, startDate, endDate, serviceType = null) {
        console.log('Showing local transfer selection modal with data:', { tourId, country, startDate, endDate, serviceType });
        
        // Get the city value from the form
        const cityElement = document.getElementById('modal_local_transfer_city');
        const city = cityElement ? cityElement.value : '';
        
        // Initialize the modal
        const localTransferModal = new bootstrap.Modal(document.getElementById('localTransferSelectionModal'));
        localTransferModal.show();
        
        // Set hidden fields
        document.getElementById('local_transfer_tour_id').value = tourId;
        document.getElementById('local_transfer_country').value = country;
        if (cityElement) {
            document.getElementById('local_transfer_city').value = city;
        }
        document.getElementById('local_transfer_start_date').value = startDate;
        document.getElementById('local_transfer_end_date').value = endDate;
        
        // Pre-select service type if provided
        if (serviceType) {
            setTimeout(() => {
                let radioId = '';
                if (serviceType === 'travel_hourly' || serviceType === 'hourly') {
                    radioId = 'local_transfer_service_type_hourly';
                } else if (serviceType === 'travel_point' || serviceType === 'point_to_point') {
                    radioId = 'local_transfer_service_type_point';
                } else if (serviceType === 'local_transport' || serviceType === 'local_transfer') {
                    radioId = 'local_transfer_service_type_local';
                }
                
                if (radioId) {
                    const radioButton = document.getElementById(radioId);
                    if (radioButton) {
                        radioButton.checked = true;
                        // Trigger the change event to update the form
                        if (serviceType === 'travel_hourly' || serviceType === 'hourly') {
                            handleLocalTransferServiceTypeChange('hourly');
                        } else if (serviceType === 'travel_point' || serviceType === 'point_to_point') {
                            handleLocalTransferServiceTypeChange('point_to_point');
                        } else if (serviceType === 'local_transport' || serviceType === 'local_transfer') {
                            handleLocalTransferServiceTypeChange('local_transfer');
                        }
                    }
                }
            }, 200);
        }
        
        // Initialize the local transfer modal with a slight delay to ensure DOM is ready
        setTimeout(() => {
            initializeLocalTransferModal();
        }, 100);
    }
    
    // Function to add more transport service (opens local transfer modal)
    function addMoreTransportService() {
        const tourId = document.getElementById('tour_id').value;
        const country = document.getElementById('user_country').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (!tourId) {
            showNotification('Tour ID is required', 'error');
            return;
        }
        
        showLocalTransferSelectionModal(tourId, country, startDate, endDate);
    }

    function showDropoffTransportSelectionModal(tourId, country, startDate, endDate) {
        console.log('Showing dropoff transport selection modal with data:', { tourId, country, startDate, endDate });
        
        // Get the city value from the form
        const city = document.getElementById('modal_exitport_transport_city').value;
        
        // Initialize the modal
        const dropoffTransportModal = new bootstrap.Modal(document.getElementById('dropoffTransportSelectionModal'));
        dropoffTransportModal.show();
        
        // Set hidden fields
        document.getElementById('modal_dropoff_transport_tour_id').value = tourId;
        document.getElementById('modal_dropoff_transport_country').value = country;
        document.getElementById('modal_dropoff_transport_city').value = city;
        document.getElementById('modal_dropoff_transport_start_date').value = startDate;
        document.getElementById('modal_dropoff_transport_end_date').value = endDate;
        
        // Initialize the dropoff transport modal with a slight delay to ensure DOM is ready
        setTimeout(() => {
            initializeDropoffTransportModal();
        }, 100);
    }
    
    function initializeTransportModal() {
        // Check if Point-to-Point functionality should be enabled
        const isPointToPointElement = document.getElementById('is_point_to_point');
        const isPointToPoint = isPointToPointElement && isPointToPointElement.value === '1';
        
        console.log('Transport modal initialization - Point-to-Point check:', {
            element: !!isPointToPointElement,
            value: isPointToPointElement ? isPointToPointElement.value : 'not found',
            isEnabled: isPointToPoint
        });
        
        if (isPointToPoint) {
            console.log('Point-to-Point functionality enabled for transport modal - converting to Google Maps inputs');
            enablePointToPointForTransportModal();
        } else {
            console.log('Point-to-Point functionality disabled - using zone-based selects');
            // Load zones for pickup
            loadZonesForPickup();
        }
        
        // Disable service type select initially
        const serviceTypeSelect = document.getElementById('modal_transport_service_type');
        if (serviceTypeSelect) {
            serviceTypeSelect.disabled = true;
        }
        
        // Add event listeners
        const pickupZoneSelect = document.getElementById('modal_transport_pickup_zone');
        if (pickupZoneSelect) {
            pickupZoneSelect.addEventListener('change', onPickupZoneChange);
        }
        
        const searchBtn = document.getElementById('transport_search_btn');
        if (searchBtn) {
            searchBtn.addEventListener('click', searchVehicles);
        }
    }
    
    // Function to enable Point-to-Point functionality for transport modal
    function enablePointToPointForTransportModal() {
        console.log('Enabling Point-to-Point functionality for transport modal...');
        
        // Convert pickup zone select to Google Maps location input
        const pickupZoneSelect = document.getElementById('modal_transport_pickup_zone');
        if (pickupZoneSelect) {
            convertSelectToLocationInput(pickupZoneSelect, 'modal_transport', 'pickup');
        }
        
        // Convert dropoff zone select to Google Maps location input
        const dropoffZoneSelect = document.getElementById('modal_transport_dropoff_zone');
        if (dropoffZoneSelect) {
            convertSelectToLocationInput(dropoffZoneSelect, 'modal_transport', 'dropoff');
        }
        
        // Initialize Google Maps autocomplete for the new location inputs
        setTimeout(() => {
            initializeGoogleMapsAutocomplete();
            console.log('Google Maps autocomplete initialized for transport modal');
        }, 500);
        
        console.log('Point-to-Point functionality enabled successfully for transport modal');
    }
    
    // Function to convert zone select to Google Maps location input
    function convertSelectToLocationInput(selectElement, prefix, direction) {
        const container = selectElement.closest('.position-relative') || selectElement.parentElement;
        
        if (!container) {
            console.warn(`Container not found for select: ${selectElement.id}`);
            return;
        }
        
        // Create Google Maps location input HTML
        const locationInputHtml = `
            <input type="text" 
                   class="form-control border-2 google-maps-autocomplete" 
                   name="${prefix}_${direction}_location" 
                   id="${prefix}_${direction}_location" 
                   placeholder="Search for ${direction} location..." 
                   style="padding-left: 45px;">
            <i class="ri-map-pin-line position-absolute text-${direction === 'pickup' ? 'success' : 'danger'} location-icon" 
               style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
            <input type="hidden" name="${prefix}_${direction}_lat" id="${prefix}_${direction}_lat">
            <input type="hidden" name="${prefix}_${direction}_lng" id="${prefix}_${direction}_lng">
            <input type="hidden" name="${prefix}_${direction}_place_id" id="${prefix}_${direction}_place_id">
        `;
        
        // Replace the select with the new input
        container.innerHTML = locationInputHtml;
        container.classList.add('location-input');
        
        console.log(`Converted ${prefix} ${direction} select to Google Maps location input`);
    }
    
    function initializeLocalTransferModal() {
        // Set up event listeners for local transfer modal
        const localTransferPickupZoneSelect = document.getElementById('local_transfer_pickup_zone');
        if (localTransferPickupZoneSelect) {
            localTransferPickupZoneSelect.addEventListener('change', handleLocalTransferPickupZoneChange);
        }
        
        // Ensure dropoff zone is enabled
        const dropoffZoneSelect = document.getElementById('local_transfer_dropoff_zone');
        if (dropoffZoneSelect) {
            dropoffZoneSelect.disabled = false;
        }
        
        // Disable service type select initially
        const serviceTypeSelect = document.getElementById('local_transfer_service_type');
        if (serviceTypeSelect) {
            serviceTypeSelect.disabled = true;
        }
        
        // Local transfer search button is handled by onclick attribute
        
        // Set default service type to 'local_transfer'
        const localTransferServiceType = document.getElementById('local_transfer_service_type_local');
        if (localTransferServiceType) {
            localTransferServiceType.checked = true;
            handleLocalTransferServiceTypeChange('local_transfer');
        } else {
            // If local transfer radio doesn't exist, set default to point_to_point
            window.currentLocalTransferServiceType = 'point_to_point';
        }
        
        // Initialize Google Maps autocomplete for the modal
        if (typeof google !== 'undefined' && google.maps && google.maps.places) {
            setTimeout(() => {
                initializeGoogleMapsAutocomplete();
            }, 200);
        }
        
        // Initial form completion check
        setTimeout(() => {
            checkLocalTransferZoneFormCompletion();
        }, 300);
    }

    function initializeDropoffTransportModal() {
        // Check if Point-to-Point functionality should be enabled
        const isPointToPointElement = document.getElementById('is_point_to_point');
        const isPointToPoint = isPointToPointElement && isPointToPointElement.value === '1';
        
        console.log('Dropoff transport modal initialization - Point-to-Point check:', {
            element: !!isPointToPointElement,
            value: isPointToPointElement ? isPointToPointElement.value : 'not found',
            isEnabled: isPointToPoint
        });
        
        if (isPointToPoint) {
            console.log('Point-to-Point functionality enabled for dropoff transport modal - converting to Google Maps inputs');
            enablePointToPointForDropoffTransportModal();
            
            // Enable search button for point-to-point mode
            const searchBtn = document.getElementById('dropoff_transport_search_btn');
            if (searchBtn) {
                searchBtn.disabled = false;
                searchBtn.classList.remove('btn-secondary');
                searchBtn.classList.add('btn-success');
                console.log('Dropoff search button enabled for Point-to-Point mode');
            }
        } else {
            console.log('Point-to-Point functionality disabled - using zone-based selects');
        }
        
        // Disable service type select initially
        const serviceTypeSelect = document.getElementById('modal_dropoff_transport_service_type');
        if (serviceTypeSelect) {
            serviceTypeSelect.disabled = true;
        }
        
        // Add event listeners
        const pickupZoneSelect = document.getElementById('modal_dropoff_transport_pickup_zone');
        if (pickupZoneSelect) {
            pickupZoneSelect.addEventListener('change', onDropoffPickupZoneChange);
        }
        
        const dropoffZoneSelect = document.getElementById('modal_dropoff_transport_dropoff_zone');
        if (dropoffZoneSelect) {
            dropoffZoneSelect.addEventListener('change', checkDropoffFormCompletion);
        }
        
        const pickupTimeInput = document.getElementById('modal_dropoff_transport_pickup_time');
        if (pickupTimeInput) {
            pickupTimeInput.addEventListener('change', checkDropoffFormCompletion);
        }
        
        const pickupDateInput = document.getElementById('modal_dropoff_transport_pickup_date');
        if (pickupDateInput) {
            pickupDateInput.addEventListener('change', checkDropoffFormCompletion);
        }
        
        
        
        // Initial form completion check
        setTimeout(() => {
            checkDropoffFormCompletion();
        }, 100);
    }
    
    // Function to enable Point-to-Point functionality for dropoff transport modal
    function enablePointToPointForDropoffTransportModal() {
        console.log('Enabling Point-to-Point functionality for dropoff transport modal...');
        
        // Convert pickup zone select to Google Maps location input
        const pickupZoneSelect = document.getElementById('modal_dropoff_transport_pickup_zone');
        if (pickupZoneSelect) {
            convertSelectToLocationInput(pickupZoneSelect, 'modal_dropoff_transport', 'pickup');
        }
        
        // Convert dropoff zone select to Google Maps location input
        const dropoffZoneSelect = document.getElementById('modal_dropoff_transport_dropoff_zone');
        if (dropoffZoneSelect) {
            convertSelectToLocationInput(dropoffZoneSelect, 'modal_dropoff_transport', 'dropoff');
        }
        
        // Initialize Google Maps autocomplete for the new location inputs
        setTimeout(() => {
            initializeGoogleMapsAutocomplete();
            console.log('Google Maps autocomplete initialized for dropoff transport modal');
        }, 500);
        
        console.log('Point-to-Point functionality enabled successfully for dropoff transport modal');
    }
    
    function loadZonesForPickup() {
        // No need to load zones as they are already populated from backend
        console.log('Pickup zones already populated from backend');
        
        // We can filter the zones based on the selected country if needed
        const country = document.getElementById('modal_transport_country').value;
        
        console.log('Current country:', { country });
        
        // If you want to filter the existing options based on country/city:
        // const pickupZoneSelect = document.getElementById('modal_transport_pickup_zone');
        // if (pickupZoneSelect) {
        //     Array.from(pickupZoneSelect.options).forEach(option => {
        //         if (option.value) {
        //             const portData = JSON.parse(option.getAttribute('data-port'));
        //             option.style.display = (portData.country === country) ? '' : 'none';
        //         }
        //     });
        // }
    }
    
    function handleLocalTransferPickupZoneChange() {
        const pickupZoneId = document.getElementById('local_transfer_pickup_zone').value;
        const dropoffZoneSelect = document.getElementById('local_transfer_dropoff_zone');
        
        if (dropoffZoneSelect) {
            // Keep dropoff field enabled at all times
                dropoffZoneSelect.disabled = false;
            
            // Only clear dropoff value if pickup is cleared (optional behavior)
            // Uncomment the next two lines if you want to clear dropoff when pickup is cleared
            // if (!pickupZoneId) {
            //     dropoffZoneSelect.value = '';
            // }
        }
        
        // Check form completion to enable/disable search button
        checkLocalTransferZoneFormCompletion();
    }
    
    function clearLocalTransferDropoffZone() {
        const dropoffZoneSelect = document.getElementById('local_transfer_dropoff_zone');
        if (dropoffZoneSelect) {
            dropoffZoneSelect.value = '';
            
            // Check form completion to enable/disable search button
            checkLocalTransferZoneFormCompletion();
        }
    }
    
    function handleLocalTransferServiceTypeChange(serviceType) {
        // Get all field containers using querySelectorAll to get collections
        const local_transfer_fields = document.getElementById('local_transfer_fields');
        const point_to_point_fields = document.getElementById('point_to_point_fields');
        const hourly_fields = document.getElementById('hourly_fields');
        
        // Store the current service type for service type options filtering
        window.currentLocalTransferServiceType = serviceType;
        console.log('Service type changed to:', serviceType);
        
        // Hide vehicle results section when switching service types
        const vehicleResultsSection = document.getElementById('local_transfer_vehicle_results');
        if (vehicleResultsSection) {
            vehicleResultsSection.style.display = 'none';
        }
        
        // Clear vehicle dropdown when switching service types
        const vehicleSelect = document.getElementById('local_transfer_vehicle_id');
        if (vehicleSelect) {
            vehicleSelect.innerHTML = '<option value="">Choose vehicle</option>';
            vehicleSelect.disabled = true;
        }
        
        // Clear service type dropdown when switching service types
        const serviceTypeSelect = document.getElementById('local_transfer_service_type');
        if (serviceTypeSelect) {
            serviceTypeSelect.value = '';
            serviceTypeSelect.disabled = true;
        }
        
        // Reset passengers to default value when switching service types
        const passengersInput = document.getElementById('local_transfer_passengers');
        if (passengersInput) {
            passengersInput.value = '1';
        }
        
        // Hide price display when switching service types
        const priceDisplay = document.getElementById('local_transfer_price_display');
        if (priceDisplay) {
            priceDisplay.style.display = 'none';
        }
        
        // Hide manual price field when switching service types
        const manualPriceContainer = document.getElementById('manual_price_field_container');
        const manualPriceInput = document.getElementById('local_transfer_manual_price');
        if (manualPriceContainer) {
            manualPriceContainer.style.display = 'none';
        }
        if (manualPriceInput) {
            manualPriceInput.value = '';
        }
        
        // Show fields based on selected service type
        if (serviceType === 'local_transfer') {
            local_transfer_fields.classList.remove('d-none');
            point_to_point_fields.classList.add('d-none');
            hourly_fields.classList.add('d-none');

        } else if (serviceType === 'point_to_point') {

            point_to_point_fields.classList.remove('d-none');
            local_transfer_fields.classList.add('d-none');
            hourly_fields.classList.add('d-none');
            
        } else if (serviceType === 'hourly') {

            hourly_fields.classList.remove('d-none');
            local_transfer_fields.classList.add('d-none');
            point_to_point_fields.classList.add('d-none');
        }
    }
    
    function searchPointToPointVehicles() {
        const pickupLocation = document.getElementById('local_transfer_point_pickup_location').value;
        const dropoffLocation = document.getElementById('local_transfer_point_dropoff_location').value;
        const pickupTime = document.getElementById('local_transfer_point_pickup_time').value;
        const pickupDate = document.getElementById('local_transfer_point_pickup_date').value;
        
        if (!pickupLocation || !dropoffLocation || !pickupTime || !pickupDate) {
            showNotification('Please fill in all required fields', 'warning');
            return;
        }
        
        console.log('Searching vehicles for point to point:', { pickupLocation, dropoffLocation, pickupTime, pickupDate });
        
        // For point-to-point, we don't need city validation as we show all vehicles
        const city = document.getElementById('local_transfer_city').value;
        const country = document.getElementById('user_country').value;
        const searchBtn = document.getElementById('local_transfer_point_to_point_search_btn');
        
        // Show loading state
        if (searchBtn) {
            searchBtn.innerHTML = '<i class="ri-loader-4-line spin me-2"></i>Searching...';
            searchBtn.disabled = true;
        }
        
        // Store that this is a point-to-point search
        window.currentLocalTransferServiceType = 'point_to_point';
        
        // Fetch vehicles from API - for point-to-point, show all vehicles regardless of city
        fetch(`{{ route('fetch-vehicles-by-city-dmc') }}?show_all=1`)
            .then(response => response.json())
            .then(data => {
                console.log('Vehicle search response (all vehicles for point-to-point):', data);
                
                const vehicleResultsSection = document.getElementById('local_transfer_vehicle_results');
                const vehicleSelect = document.getElementById('local_transfer_vehicle_id');
                
                if (data.success && data.vehicles && data.vehicles.length > 0) {
                    console.log('Success: Found vehicles for point-to-point, showing results section');
                    
                    // Show the vehicle results section
                    if (vehicleResultsSection) {
                        vehicleResultsSection.style.display = 'block';
                        vehicleResultsSection.style.visibility = 'visible';
                        console.log('Vehicle results section shown for point-to-point');
                        vehicleResultsSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    } else {
                        console.error('Vehicle results section not found!');
                    }
                    
                    // Populate vehicle dropdown
                    if (vehicleSelect) {
                        vehicleSelect.innerHTML = '<option value="">Choose your vehicle</option>';
                        console.log('Starting to populate vehicle dropdown for point-to-point with', data.vehicles.length, 'vehicles');
                        
                        data.vehicles.forEach((vehicle, index) => {
                            const vehicleInfo = `${vehicle.vehicle_name} (${vehicle.vehicle_type}) - ${vehicle.seating_capacity} seats`;
                            
                            // Debug logging for vehicle data
                            console.log(`Vehicle ${index + 1}:`, vehicle);
                            
                            try {
                                const vehicleDataString = JSON.stringify(vehicle);
                                vehicleSelect.innerHTML += `<option value="${vehicle.vehicle_id}" 
                                    data-vehicle-name="${vehicle.vehicle_name}" 
                                    data-vehicle-type="${vehicle.vehicle_type}" 
                                    data-seating-capacity="${vehicle.seating_capacity}"
                                    data-private-price="${vehicle.private_price || ''}" 
                                    data-shared-price="${vehicle.shared_price || ''}" 
                                    data-service-type="${vehicle.service_type || ''}" 
                                    data-cost-per-hour="${vehicle.cost_per_hour || ''}" 
                                    data-sharable-cost-per-hour="${vehicle.sharable_cost_per_hour || ''}" 
                                    data-sharable="${vehicle.sharable || '0'}" 
                                    data-vehicle="${vehicleDataString}">
                                    ${vehicleInfo}
                                </option>`;
                            } catch (error) {
                                console.error('Error stringifying vehicle data:', error);
                                console.log('Problematic vehicle object:', vehicle);
                                // Fallback: create option without data-vehicle attribute
                                vehicleSelect.innerHTML += `<option value="${vehicle.vehicle_id}">
                                    ${vehicleInfo}
                                </option>`;
                            }
                        });
                        
                        // Enable the vehicle select
                        vehicleSelect.disabled = false;
                        console.log('Vehicle dropdown populated successfully for point-to-point');
                        
                        // Update vehicle details and pricing
                        updateLocalTransferVehicleDetails();
                    } else {
                        console.error('Vehicle select dropdown not found!');
                    }
                    
                    console.log(`Populated ${data.vehicles.length} vehicles in dropdown for point-to-point`);
                } else {
                    showNotification('No vehicles available. Please contact support.', 'warning');
                    if (vehicleResultsSection) {
                        vehicleResultsSection.style.display = 'none';
                    }
                }
                
                // Reset search button
                if (searchBtn) {
                    searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search Vehicles';
                    searchBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error searching vehicles:', error);
                showNotification('Error searching vehicles. Please try again.', 'error');
                
                // Reset search button
                if (searchBtn) {
                    searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search Vehicles';
                    searchBtn.disabled = false;
                }
            });
    }
    
    function searchHourlyVehicles() {
        const pickupLocation = document.getElementById('local_transfer_hourly_pickup_location').value;
        const pickupTime = document.getElementById('local_transfer_hourly_pickup_time').value;
        const pickupDate = document.getElementById('local_transfer_hourly_pickup_date').value;
        
        if (!pickupLocation || !pickupTime || !pickupDate) {
            showNotification('Please fill in all required fields', 'warning');
            return;
        }
        
        console.log('Searching vehicles for hourly:', { pickupLocation, pickupTime, pickupDate });
        
        // For hourly service, we don't need city validation as we show all vehicles
        const city = document.getElementById('local_transfer_city').value;
        const country = document.getElementById('user_country').value;
        const searchBtn = document.getElementById('local_transfer_hourly_search_btn');
        
        // Show loading state
        if (searchBtn) {
            searchBtn.innerHTML = '<i class="ri-loader-4-line spin me-2"></i>Searching...';
            searchBtn.disabled = true;
        }
        
        // Store that this is a hourly search
        window.currentLocalTransferServiceType = 'hourly';
        
        // Fetch vehicles from API - for hourly service, show all vehicles regardless of city
        fetch(`{{ route('fetch-vehicles-by-city-dmc') }}?show_all=1`)
            .then(response => response.json())
            .then(data => {
                console.log('Vehicle search response (all vehicles for hourly):', data);
                
                const vehicleResultsSection = document.getElementById('local_transfer_vehicle_results');
                const vehicleSelect = document.getElementById('local_transfer_vehicle_id');
                
                if (data.success && data.vehicles && data.vehicles.length > 0) {
                    console.log('Success: Found vehicles for hourly service, showing results section');
                    
                    // Filter vehicles for hourly service - only show sharable = 1 or 3
                    const hourlyVehicles = data.vehicles.filter(vehicle => {
                        const sharable = parseInt(vehicle.sharable) || 0;
                        return sharable === 1 || sharable === 3;
                    });
                    
                    console.log('Filtered vehicles for hourly service:', hourlyVehicles.length, 'out of', data.vehicles.length);
                    
                    if (hourlyVehicles.length > 0) {
                        // Show the vehicle results section
                        if (vehicleResultsSection) {
                            vehicleResultsSection.style.display = 'block';
                            vehicleResultsSection.style.visibility = 'visible';
                            console.log('Vehicle results section shown for hourly service');
                            vehicleResultsSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        } else {
                            console.error('Vehicle results section not found for hourly service!');
                        }
                        
                        // Populate vehicle dropdown
                        if (vehicleSelect) {
                            vehicleSelect.innerHTML = '<option value="">Choose your vehicle</option>';
                            console.log('Starting to populate vehicle dropdown for hourly service with', hourlyVehicles.length, 'vehicles');
                            
                            hourlyVehicles.forEach((vehicle, index) => {
                            const vehicleInfo = `${vehicle.vehicle_name} (${vehicle.vehicle_type}) - ${vehicle.seating_capacity} seats`;
                            
                            // Debug logging for vehicle data
                            console.log(`Hourly Vehicle ${index + 1}:`, vehicle);
                            
                            try {
                                const vehicleDataString = JSON.stringify(vehicle);
                                vehicleSelect.innerHTML += `<option value="${vehicle.vehicle_id}" 
                                    data-vehicle-name="${vehicle.vehicle_name}" 
                                    data-vehicle-type="${vehicle.vehicle_type}" 
                                    data-seating-capacity="${vehicle.seating_capacity}"
                                    data-private-price="${vehicle.private_price || ''}" 
                                    data-shared-price="${vehicle.shared_price || ''}" 
                                    data-service-type="${vehicle.service_type || ''}" 
                                    data-cost-per-hour="${vehicle.cost_per_hour || ''}" 
                                    data-sharable-cost-per-hour="${vehicle.sharable_cost_per_hour || ''}" 
                                    data-sharable="${vehicle.sharable || '0'}" 
                                    data-vehicle="${vehicleDataString}">
                                    ${vehicleInfo}
                                </option>`;
                            } catch (error) {
                                console.error('Error stringifying vehicle data for hourly service:', error);
                                console.log('Problematic vehicle object:', vehicle);
                                // Fallback: create option without data-vehicle attribute
                                vehicleSelect.innerHTML += `<option value="${vehicle.vehicle_id}">
                                    ${vehicleInfo}
                                </option>`;
                            }
                            });
                            
                            // Enable the vehicle select
                            vehicleSelect.disabled = false;
                            console.log('Vehicle dropdown populated successfully for hourly service');
                            
                            // Update vehicle details and pricing
                            updateLocalTransferVehicleDetails();
                        } else {
                            console.error('Vehicle select dropdown not found for hourly service!');
                        }
                        
                        console.log(`Populated ${hourlyVehicles.length} vehicles in dropdown for hourly service`);
                    } else {
                        showNotification('No vehicles available for hourly service. Only vehicles with sharable option enabled are available.', 'warning');
                        if (vehicleResultsSection) {
                            vehicleResultsSection.style.display = 'none';
                        }
                    }
                } else {
                    showNotification('No vehicles available. Please contact support.', 'warning');
                    if (vehicleResultsSection) {
                        vehicleResultsSection.style.display = 'none';
                    }
                }
                
                // Reset search button
                if (searchBtn) {
                    searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search Vehicles';
                    searchBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error searching vehicles:', error);
                showNotification('Error searching vehicles. Please try again.', 'error');
                
                // Reset search button
                if (searchBtn) {
                    searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search Vehicles';
                    searchBtn.disabled = false;
                }
            });
    }
    
    function onPickupZoneChange() {
        const pickupZoneId = document.getElementById('modal_transport_pickup_zone').value;
        const dropoffZoneSelect = document.getElementById('modal_transport_dropoff_zone');
        
        if (dropoffZoneSelect) {
            // We don't need to clear or repopulate the dropdown as it's already populated from the backend
            // Just enable/disable based on pickup selection
            
            if (pickupZoneId) {
                dropoffZoneSelect.disabled = false;
                
                // Optionally, we could filter out the pickup location from dropoff options
                // But we'll keep all options available for now
                
                // If you want to filter out the pickup location, uncomment this code:
                /*
                Array.from(dropoffZoneSelect.options).forEach(option => {
                    if (option.value === pickupZoneId) {
                        option.disabled = true;
                    } else {
                        option.disabled = false;
                    }
                });
                */
            } else {
                dropoffZoneSelect.disabled = true;
            }
        }
    }
    
    function clearDropoffZone() {
        const dropoffZoneSelect = document.getElementById('modal_transport_dropoff_zone');
        if (dropoffZoneSelect) {
            dropoffZoneSelect.value = '';
        }
    }
    
    function searchVehicles() {
        console.log('searchVehicles called for transport modal');
        const selectedCity = document.getElementById('modal_entryport_transport_city').value;
        // Check if Point-to-Point mode is enabled
        const isPointToPointElement = document.getElementById('is_point_to_point');
        const isPointToPoint = isPointToPointElement && isPointToPointElement.value === '1';
        
        if (isPointToPoint) {
            console.log('Point-to-Point mode detected for transport modal - using city-based vehicle search');
            searchVehiclesForTransportModalPointToPoint();
            return;
        }
        
        // Zone-based mode
        console.log('Using zone-based vehicle search for transport modal');

        const pickupOption = document.getElementById('modal_transport_pickup_zone');
        const dropoffOption = document.getElementById('modal_transport_dropoff_zone');
        
        const pickupZoneId = pickupOption?.value;
        const dropoffZoneId = dropoffOption?.value;
        const pickupZoneType = pickupOption?.dataset?.type;
        const dropoffZoneType = dropoffOption?.dataset?.type;
        const pickupTime = document.getElementById('modal_transport_pickup_time').value;
        const pickupDate = document.getElementById('modal_transport_pickup_date').value;
        
        if (!pickupZoneId || !dropoffZoneId || !pickupTime || !pickupDate || !selectedCity) {
            showNotification('Please fill in all required fields', 'warning');
            return;
        }
        
        console.log('Searching vehicles for zone-based transport:', { pickupZoneId, dropoffZoneId, pickupTime, pickupDate, selectedCity });
        
        const searchBtn = document.getElementById('transport_search_btn');
        const vehicleResultsSection = document.getElementById('transport_vehicle_results');
        const vehicleSelect = document.getElementById('modal_transport_vehicle_id');
        
        // Show loading state
        if (searchBtn) {
            searchBtn.innerHTML = '<i class="ri-loader-4-line spin me-2"></i>Searching...';
            searchBtn.disabled = true;
        }
        const user_dmc = @json($UserDmc);
        const zone_status = user_dmc.zone_on;
        if(zone_status == 1){
            fromZoneType = pickupZoneType || 'Port';
            toZoneType = dropoffZoneType || 'Hotel';
        }
        else{
            fromZoneType = '';
            toZoneType = '';
        }
        // Make API call to fetch vehicles by zones
        fetch(`{{ route('fetch-vehicles-by-zones') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                from_zone_id: pickupZoneId,
                to_zone_id: dropoffZoneId,
                from_zone_type: zone_status == 1 ? fromZoneType : '',
                to_zone_type: zone_status == 1 ? toZoneType : '',
                zone_status: zone_status,
                city: selectedCity
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Zone-based vehicle search response:', data);
            
            if (data.success && data.vehicles && data.vehicles.length > 0) {
                // Show the vehicle results section
                if (vehicleResultsSection) {
                    vehicleResultsSection.style.display = 'block';
                }
                
                // Populate vehicle dropdown
                if (vehicleSelect) {
                    vehicleSelect.innerHTML = '<option value="">Choose vehicle</option>';
                    data.vehicles.forEach(vehicle => {
                        const vehicleInfo = `${vehicle.vehicle_name} (${vehicle.vehicle_type}) - ${vehicle.seating_capacity} seats`;
                        vehicleSelect.innerHTML += `<option value="${vehicle.vehicle_id}" 
                            data-vehicle-name="${vehicle.vehicle_name}" 
                            data-vehicle-type="${vehicle.vehicle_type}" 
                            data-seating-capacity="${vehicle.seating_capacity}"
                            data-private-price="${vehicle.private_price || ''}"
                            data-shared-price="${vehicle.shared_price || ''}"
                            data-service-type="${vehicle.service_type || ''}"
                            data-sharable="${vehicle.sharable || ''}"
                            data-vehicle="${JSON.stringify(vehicle)}">
                            ${vehicleInfo}
                        </option>`;
                    });
                    vehicleSelect.disabled = false;
                }
                
                console.log(`Populated ${data.vehicles.length} vehicles in dropdown (zone-based transport)`);
            } else {
                alert('No vehicles available for this route. Please try different locations.');
            }
            
            // Reset search button
            if (searchBtn) {
                searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search Vehicles';
                searchBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error searching vehicles:', error);
            alert('Error searching vehicles. Please try again.');
            if (searchBtn) {
                searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search Vehicles';
                searchBtn.disabled = false;
            }
        });
    }
    
    // Function to search vehicles for transport modal in Point-to-Point mode
    function searchVehiclesForTransportModalPointToPoint() {
        console.log('Searching Point-to-Point vehicles for transport modal');
        
        // For point-to-point transport modal, we show all vehicles regardless of city
        const city = document.getElementById('modal_entryport_transport_city').value;
        const country = document.getElementById('modal_transport_country').value;
        const searchBtn = document.getElementById('transport_search_btn');
        const vehicleResultsSection = document.getElementById('transport_vehicle_results');
        const vehicleSelect = document.getElementById('modal_transport_vehicle_id');
        const serviceTypeSelect = document.getElementById('modal_transport_service_type');
        
        console.log('Using Point-to-Point endpoint for transport modal - showing all vehicles');

        // Show loading state
        if (searchBtn) {
            searchBtn.innerHTML = '<i class="ri-loader-4-line spin me-2"></i>Searching...';
            searchBtn.disabled = true;
        }

        fetch(`{{ route('fetch-vehicles-by-city-dmc') }}?show_all=1`)
            .then(response => response.json())
            .then(data => {
                console.log('Point-to-Point vehicle search response for transport modal (all vehicles):', data);
                
                if (data.success && data.vehicles && data.vehicles.length > 0) {
                    // Show the vehicle results section
                    if (vehicleResultsSection) {
                        vehicleResultsSection.style.display = 'block';
                    }
                    
                    // Populate vehicle dropdown
                    if (vehicleSelect) {
                        vehicleSelect.innerHTML = '<option value="">Choose vehicle</option>';
                        data.vehicles.forEach(vehicle => {
                            const vehicleInfo = `${vehicle.vehicle_name} (${vehicle.vehicle_type}) - ${vehicle.seating_capacity} seats`;
                            
                            // Debug: Log vehicle data before stringifying
                            console.log('Vehicle data for entry point-to-point:', vehicle);
                            
                            try {
                                const vehicleDataString = JSON.stringify(vehicle);
                                vehicleSelect.innerHTML += `<option value="${vehicle.vehicle_id}" 
                                    data-vehicle-name="${vehicle.vehicle_name}" 
                                    data-vehicle-type="${vehicle.vehicle_type}" 
                                    data-seating-capacity="${vehicle.seating_capacity}"
                                    data-private-price="${vehicle.private_price || ''}"
                                    data-shared-price="${vehicle.shared_price || ''}"
                                    data-service-type="${vehicle.service_type || ''}"
                                    data-cost-per-hour="${vehicle.cost_per_hour || ''}"
                                    data-sharable-cost-per-hour="${vehicle.sharable_cost_per_hour || ''}"
                                    data-sharable="${vehicle.sharable || ''}"
                                    data-vehicle="${vehicleDataString}">
                                    ${vehicleInfo}
                                </option>`;
                            } catch (error) {
                                console.error('Error stringifying vehicle data:', error);
                                console.log('Problematic vehicle object:', vehicle);
                                // Fallback: create option without data-vehicle attribute
                                vehicleSelect.innerHTML += `<option value="${vehicle.vehicle_id}">
                                    ${vehicleInfo}
                                </option>`;
                            }
                        });
                        vehicleSelect.disabled = false;
                        
                        // Enable service type select
                        if (serviceTypeSelect) {
                            serviceTypeSelect.disabled = false;
                        }
                        
                        // Add event listener for vehicle selection to update service type options
                        vehicleSelect.addEventListener('change', function() {
                            updateServiceTypeOptionsForTransport();
                        });
                        
                        console.log(`Populated ${data.vehicles.length} vehicles for Point-to-Point transport modal`);
                    }
                } else {
                    alert('No vehicles available. Please contact support.');
                }
                
                // Reset search button
                if (searchBtn) {
                    searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search Vehicles';
                    searchBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error searching Point-to-Point vehicles:', error);
                console.error('Error details:', error.message);
                showNotification('Error searching vehicles. Please try again.', 'error');
                if (searchBtn) {
                    searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search Vehicles';
                    searchBtn.disabled = false;
                }
            });
    }
    
    // Function to update service type options for transport modal
    function updateServiceTypeOptionsForTransport() {
        const vehicleSelect = document.getElementById('modal_transport_vehicle_id');
        const serviceTypeSelect = document.getElementById('modal_transport_service_type');
        
        if (!vehicleSelect || !serviceTypeSelect) return;
        
        const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            serviceTypeSelect.innerHTML = '<option value="">Select service type</option>';
            serviceTypeSelect.disabled = true;
            return;
        }
        
        // Clear existing options
        serviceTypeSelect.innerHTML = '<option value="">Select service type</option>';
        
        // Always add Private option
        
        
        // Add Shared option if vehicle supports it
        const vehicleData = {
            id: selectedOption.value,
            name: selectedOption.dataset.vehicleName,
            type: selectedOption.dataset.vehicleType,
            seatingCapacity: selectedOption.dataset.seatingCapacity,
            privatePrice: selectedOption.dataset.privatePrice,
            sharedPrice: selectedOption.dataset.sharedPrice,
            serviceType: selectedOption.dataset.serviceType,
            sharable: selectedOption.dataset.sharable,
        };        
        console.log('Vehicle data from updateServiceTypeOptionsForTransport:', vehicleData);
        
        // Based on sharable field: 1=Private only, 2=Shared only, 3=Both
        if(vehicleData.sharable == 1){
            // Private only
            const privateOption = document.createElement('option');
            privateOption.value = 'Private';
            privateOption.textContent = 'Private';
            serviceTypeSelect.appendChild(privateOption);
        }
        else if(vehicleData.sharable == 2){
            // Shared only
            const sharedOption = document.createElement('option');
            sharedOption.value = 'Shared';
            sharedOption.textContent = 'Shared';
            serviceTypeSelect.appendChild(sharedOption);
        }
        else if(vehicleData.sharable == 3){
            // Both Private and Shared
            const privateOption = document.createElement('option');
            privateOption.value = 'Private';
            privateOption.textContent = 'Private';
            serviceTypeSelect.appendChild(privateOption);

            const sharedOption = document.createElement('option');
            sharedOption.value = 'Shared';
            sharedOption.textContent = 'Shared';
            serviceTypeSelect.appendChild(sharedOption);
        }
        else{
            // Default: show both options if sharable value is not set or invalid
            const privateOption = document.createElement('option');
            privateOption.value = 'Private';
            privateOption.textContent = 'Private';
            serviceTypeSelect.appendChild(privateOption);

            const sharedOption = document.createElement('option');
            sharedOption.value = 'Shared';
            sharedOption.textContent = 'Shared';
            serviceTypeSelect.appendChild(sharedOption);
        }
        
        serviceTypeSelect.disabled = false;
        console.log('Service type options updated for transport modal');
    }
    
    function searchLocalTransferVehicles() {
        const pickupZoneSelect = document.getElementById('local_transfer_pickup_zone');
        const dropoffZoneSelect = document.getElementById('local_transfer_dropoff_zone');
        const pickupTime = document.getElementById('local_transfer_pickup_time').value;
        const pickupDate = document.getElementById('local_transfer_pickup_date').value;
        
        if (!pickupZoneSelect || !dropoffZoneSelect || !pickupZoneSelect.value || !dropoffZoneSelect.value || !pickupTime || !pickupDate) {
            showNotification('Please fill in all required fields', 'warning');
            return;
        }
        
        const fromZoneId = pickupZoneSelect.value;
        const toZoneId = dropoffZoneSelect.value;
        const searchBtn = document.getElementById('local_transfer_search_btn');
        
        // Store that this is a local transfer (zone-based) search
        window.currentLocalTransferServiceType = 'local_transfer';
        
        console.log('Searching vehicles for local transfer:', { fromZoneId, toZoneId, pickupTime, pickupDate });
        
        // Get location types from the selected options
        let fromZoneType, toZoneType;
        
        // Determine zone types based on option attributes
        const pickupOption = pickupZoneSelect.options[pickupZoneSelect.selectedIndex];
        const dropoffOption = dropoffZoneSelect.options[dropoffZoneSelect.selectedIndex];
        
        fromZoneType = pickupOption?.dataset?.type || 'Port';
        toZoneType = dropoffOption?.dataset?.type || 'Hotel';
        
        // Get the actual zone IDs - use the selected values directly
        let actualFromZoneId = fromZoneId;
        let actualToZoneId = toZoneId;
        
        console.log('Zone mapping:', {
            originalFromZoneId: fromZoneId,
            originalToZoneId: toZoneId,
            actualFromZoneId: actualFromZoneId,
            actualToZoneId: actualToZoneId,
            fromZoneType: fromZoneType,
            toZoneType: toZoneType
        });
        
        // Show loading state
        if (searchBtn) {
            searchBtn.innerHTML = '<i class="ri-loader-4-line spin me-2"></i>Searching...';
            searchBtn.disabled = true;
        }
        const selectedCity = document.getElementById('modal_local_transfer_city').value;
        
        // Get zone status from UserDmc
        const user_dmc = @json($UserDmc);
        const zone_status = user_dmc.zone_on;
        
        const params = {
            from_zone_id: actualFromZoneId,
            to_zone_id: actualToZoneId,
            from_zone_type: fromZoneType,
            to_zone_type: toZoneType,
            city: selectedCity,
            zone_status: zone_status
        };

        console.log('API Request Parameters:', {
            from_zone_id: actualFromZoneId,
            to_zone_id: actualToZoneId,
            from_zone_type: fromZoneType,
            to_zone_type: toZoneType,
            city: selectedCity,
            zone_status: zone_status
        });
        
        // Fetch vehicles from API using zone-based endpoint
        fetch(`{{ route('fetch-vehicles-by-zones') }}`, {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            method: 'POST',
            body: JSON.stringify(params)
        })
            .then(response => response.json())
            .then(data => {
                console.log('Vehicle search response (zone-based):', data);
                
                const vehicleResultsSection = document.getElementById('local_transfer_vehicle_results');
                const vehicleSelect = document.getElementById('local_transfer_vehicle_id');
                
                if (data.success && data.vehicles && data.vehicles.length > 0) {
                    // Show the vehicle results section
                    if (vehicleResultsSection) {
                        vehicleResultsSection.style.display = 'block';
                        vehicleResultsSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                    
                    // Populate vehicle dropdown
                    if (vehicleSelect) {
                        vehicleSelect.innerHTML = '<option value="">Choose your vehicle</option>';
                        data.vehicles.forEach(vehicle => {
                            const vehicleInfo = `${vehicle.vehicle_name} (${vehicle.vehicle_type}) - ${vehicle.seating_capacity} seats`;
                            
                            // Debug logging for vehicle data
                            console.log('Vehicle:', vehicle);
                            
                            const option = document.createElement('option');
                            option.value = vehicle.vehicle_id;
                            option.textContent = vehicleInfo;
                            option.setAttribute('data-private-price', vehicle.private_price || '');
                            option.setAttribute('data-shared-price', vehicle.shared_price || '');
                            option.setAttribute('data-service-type', vehicle.service_type || '');
                            option.setAttribute('data-cost-per-hour', vehicle.cost_per_hour || '');
                            option.setAttribute('data-sharable-cost-per-hour', vehicle.sharable_cost_per_hour || '');
                            option.setAttribute('data-sharable', vehicle.sharable || '0');
                            option.setAttribute('data-vehicle', JSON.stringify(vehicle));
                            vehicleSelect.appendChild(option);
                        });
                        
                        // Enable the vehicle select
                        vehicleSelect.disabled = false;
                        console.log('Vehicle dropdown populated successfully for local transfer (zone-based)');
                        
                        // Update vehicle details and pricing
                        updateLocalTransferVehicleDetails();
                    }
                    
                    console.log(`Populated ${data.vehicles.length} vehicles in dropdown`);
                } else {
                    showNotification('No vehicles available for the selected zones. Please try different pickup/dropoff locations.', 'warning');
                    if (vehicleResultsSection) {
                        vehicleResultsSection.style.display = 'none';
                    }
                }
                
                // Reset search button
                if (searchBtn) {
                    searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search Vehicles';
                    searchBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error searching vehicles:', error);
                showNotification('Error searching vehicles. Please try again.', 'error');
                
                // Reset search button
                if (searchBtn) {
                    searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search Vehicles';
                    searchBtn.disabled = false;
                }
            });
    }
    
    function updateVehicleDetails() {
        const vehicleSelect = document.getElementById('modal_transport_vehicle_id');
        const serviceTypeSelect = document.getElementById('modal_transport_service_type');
        const manualPriceContainer = document.getElementById('transport_manual_price_field_container');
        
        if (vehicleSelect && vehicleSelect.value && serviceTypeSelect) {
            // Get selected vehicle data
            const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];

            const vehicleData = {
                id: selectedOption.value,
                name: selectedOption.dataset.vehicleName,
                type: selectedOption.dataset.vehicleType,
                seatingCapacity: selectedOption.dataset.seatingCapacity,
                privatePrice: selectedOption.dataset.privatePrice,
                sharedPrice: selectedOption.dataset.sharedPrice,
                serviceType: selectedOption.dataset.serviceType
            };
            
            // Update service type options based on vehicle sharable property
            updateServiceTypeOptions(vehicleData, 'modal_transport_service_type');
            
            serviceTypeSelect.disabled = false;
            
            // Always show manual price field for both zone on and zone off modes
            if (manualPriceContainer) {
                manualPriceContainer.style.display = 'block';
            }
            
            updatePricing();
        }
    }
    
    function updateLocalTransferVehicleDetails() {
        const vehicleSelect = document.getElementById('local_transfer_vehicle_id');
        const serviceTypeSelect = document.getElementById('local_transfer_service_type');
        const manualPriceContainer = document.getElementById('manual_price_field_container');
        
        console.log('updateLocalTransferVehicleDetails called');
        console.log('vehicleSelect:', vehicleSelect);
        console.log('serviceTypeSelect:', serviceTypeSelect);
        console.log('vehicleSelect.value:', vehicleSelect ? vehicleSelect.value : 'null');
        
        // Only show manual price field for Point to Point mode
        if (manualPriceContainer && vehicleSelect && vehicleSelect.value && window.currentLocalTransferServiceType === 'point_to_point') {
            manualPriceContainer.style.display = 'block';
        } else if (manualPriceContainer) {
            manualPriceContainer.style.display = 'none';
        }
        
        if (vehicleSelect && vehicleSelect.value && serviceTypeSelect) {
            // Get selected vehicle data
            const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
            console.log('selectedOption:', selectedOption);
            console.log('data-vehicle attribute:', selectedOption.getAttribute('data-vehicle'));
            
            let vehicleData = {};
            try {
                const dataVehicleAttr = selectedOption.getAttribute('data-vehicle');
                console.log('Raw data-vehicle attribute length:', dataVehicleAttr ? dataVehicleAttr.length : 'null');
                console.log('Raw data-vehicle attribute first 100 chars:', dataVehicleAttr ? dataVehicleAttr.substring(0, 100) : 'null');
                
                if (dataVehicleAttr && dataVehicleAttr.trim() !== '') {
                    vehicleData = JSON.parse(dataVehicleAttr);
                    console.log('Successfully parsed data-vehicle attribute');
                } else {
                    console.log('data-vehicle attribute is empty, using fallback');
                    // Fallback: try to get data from individual data attributes
                    vehicleData = {
                        id: selectedOption.value,
                        vehicle_name: selectedOption.dataset.vehicleName || '',
                        vehicle_type: selectedOption.dataset.vehicleType || '',
                        seating_capacity: selectedOption.dataset.seatingCapacity || '',
                        base_price: selectedOption.dataset.privatePrice || '',
                        sharable_base_price: selectedOption.dataset.sharedPrice || '',
                        service_type: selectedOption.dataset.serviceType || '',
                        sharable: selectedOption.dataset.sharable || '',
                        cost_per_hour: selectedOption.dataset.costPerHour || '',
                        sharable_cost_per_hour: selectedOption.dataset.sharableCostPerHour || ''
                    };
                }
            } catch (error) {
                console.error('Error parsing vehicle data:', error);
                console.log('Raw data-vehicle attribute:', selectedOption.getAttribute('data-vehicle'));
                console.log('Using fallback method with individual data attributes');
                
                // Fallback: get data from individual data attributes
                vehicleData = {
                    id: selectedOption.value,
                    vehicle_name: selectedOption.dataset.vehicleName || '',
                    vehicle_type: selectedOption.dataset.vehicleType || '',
                    seating_capacity: selectedOption.dataset.seatingCapacity || '',
                    base_price: selectedOption.dataset.privatePrice || '',
                    sharable_base_price: selectedOption.dataset.sharedPrice || '',
                    service_type: selectedOption.dataset.serviceType || '',
                    sharable: selectedOption.dataset.sharable || ''
                };
            }
            
            console.log('parsed vehicleData:', vehicleData);
            console.log('vehicleData.sharable:', vehicleData.sharable);
            console.log('vehicleData.sharable type:', typeof vehicleData.sharable);
            
            // Ensure sharable is a number for comparison
            if (vehicleData.sharable) {
                vehicleData.sharable = parseInt(vehicleData.sharable) || 0;
            }
            console.log('converted vehicleData.sharable:', vehicleData.sharable);
            
            // Update service type options based on vehicle sharable property
            updateServiceTypeOptions(vehicleData, 'local_transfer_service_type');
            
            serviceTypeSelect.disabled = false;
            updateLocalTransferPricing();
        } else {
            console.log('Conditions not met for updateLocalTransferVehicleDetails');
        }
    }

    // Dropoff service functions (simple approach like pickup services)
    function onDropoffPickupZoneChange() {
        const pickupZoneId = document.getElementById('modal_dropoff_transport_pickup_zone').value;
        const dropoffZoneSelect = document.getElementById('modal_dropoff_transport_dropoff_zone');
        
        if (dropoffZoneSelect) {
            // Enable/disable based on pickup selection
            if (pickupZoneId) {
                dropoffZoneSelect.disabled = false;
            } else {
                dropoffZoneSelect.disabled = true;
                dropoffZoneSelect.value = ''; // Clear dropoff when pickup is cleared
            }
        }
        
        // Check form completion to enable/disable search button
        checkDropoffFormCompletion();
    }


    function checkDropoffFormCompletion() {
        const pickupZone = document.getElementById('modal_dropoff_transport_pickup_zone').value;
        const dropoffZone = document.getElementById('modal_dropoff_transport_dropoff_zone').value;
        const pickupTime = document.getElementById('modal_dropoff_transport_pickup_time').value;
        const pickupDate = document.getElementById('modal_dropoff_transport_pickup_date').value;
        const searchBtn = document.getElementById('dropoff_transport_search_btn');
        
        if (pickupZone && dropoffZone && pickupTime && pickupDate && searchBtn) {
            searchBtn.disabled = false;
            searchBtn.classList.remove('btn-secondary');
            searchBtn.classList.add('btn-success');
        } else if (searchBtn) {
            searchBtn.disabled = true;
            searchBtn.classList.remove('btn-success');
            searchBtn.classList.add('btn-secondary');
        }
    }

    function searchDropoffVehicles() {
        console.log('searchDropoffVehicles called');
        
        // Check if Point-to-Point mode is enabled
        const isPointToPointElement = document.getElementById('is_point_to_point');
        const isPointToPoint = isPointToPointElement && isPointToPointElement.value === '1';
        // Note: City selection removed

        
        if (isPointToPoint) {
            console.log('Point-to-Point mode detected for dropoff transport modal - using city-based vehicle search');
            searchDropoffVehiclesForPointToPoint();
            return;
        }
        
        // Zone-based mode
        console.log('Using zone-based vehicle search for dropoff transport modal');
        
        const pickupZoneId = document.getElementById('modal_dropoff_transport_pickup_zone').value;
        const dropoffZoneId = document.getElementById('modal_dropoff_transport_dropoff_zone').value;
        const pickupTime = document.getElementById('modal_dropoff_transport_pickup_time').value;
        const pickupDate = document.getElementById('modal_dropoff_transport_pickup_date').value;
        
        if (!pickupZoneId || !dropoffZoneId || !pickupTime || !pickupDate) {
            showNotification('Please fill in all required fields', 'warning');
            return;
        }
        
        console.log('Searching vehicles for dropoff service (zone-based):', { pickupZoneId, dropoffZoneId, pickupTime, pickupDate });
        
        const searchBtn = document.getElementById('dropoff_transport_search_btn');
        const vehicleResultsSection = document.getElementById('dropoff_vehicle_results');
        const vehicleSelect = document.getElementById('modal_dropoff_transport_vehicle_id');
        const serviceTypeSelect = document.getElementById('modal_dropoff_transport_service_type');
        
        // Show loading state
        if (searchBtn) {
            searchBtn.innerHTML = '<i class="ri-loader-4-line spin me-2"></i>Searching...';
            searchBtn.disabled = true;
        }
        const user_dmc = @json($UserDmc);
        const zone_status = user_dmc.zone_on;
        // Make API call to fetch vehicles by zones
        fetch(`{{ route('fetch-vehicles-by-zones') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                from_zone_id: pickupZoneId,
                to_zone_id: dropoffZoneId,
                from_zone_type: 'zone',
                to_zone_type: 'zone',
                zone_status: zone_status,
                // city parameter removed
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Zone-based dropoff vehicle search response:', data);
            
            if (data.success && data.vehicles && data.vehicles.length > 0) {
                    // Show the vehicle results section
                    if (vehicleResultsSection) {
                        vehicleResultsSection.style.display = 'block';
                        vehicleResultsSection.style.visibility = 'visible';
                        vehicleResultsSection.classList.remove('d-none');
                        vehicleResultsSection.classList.add('d-block');
                        console.log('Dropoff vehicle results section shown (zone-based)');
                    } else {
                        console.error('Dropoff vehicle results section not found!');
                    }
                
                // Populate vehicle dropdown
                if (vehicleSelect) {
                    vehicleSelect.innerHTML = '<option value="">Choose vehicle</option>';
                    data.vehicles.forEach(vehicle => {
                        const vehicleInfo = `${vehicle.vehicle_name} (${vehicle.vehicle_type}) - ${vehicle.seating_capacity} seats`;
                        vehicleSelect.innerHTML += `<option value="${vehicle.vehicle_id}" 
                            data-vehicle-name="${vehicle.vehicle_name}" 
                            data-vehicle-type="${vehicle.vehicle_type}" 
                            data-seating-capacity="${vehicle.seating_capacity}"
                            data-private-price="${vehicle.private_price || ''}"
                            data-shared-price="${vehicle.shared_price || ''}"
                            data-service-type="${vehicle.service_type || ''}"
                            data-vehicle='${JSON.stringify(vehicle)}'
                            data-sharable="${vehicle.sharable || '0'}">
                            ${vehicleInfo}
                        </option>`;
                    });
                    vehicleSelect.disabled = false;
                    
                    // Enable service type select
                    if (serviceTypeSelect) {
                        serviceTypeSelect.disabled = false;
                    }
                    
                    // Add event listener for vehicle selection
                    vehicleSelect.addEventListener('change', function() {
                        updateDropoffVehicleDetails();
                    });
                    
                    console.log(`Populated ${data.vehicles.length} vehicles in dropdown (zone-based dropoff)`);
                } else {
                    console.error('Dropoff vehicle select not found!');
                }
            } else {
                alert('No vehicles available for this route. Please try different locations.');
            }
            
            // Reset search button
            if (searchBtn) {
                searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search Vehicles';
                searchBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error searching dropoff vehicles:', error);
            alert('Error searching vehicles. Please try again.');
            if (searchBtn) {
                searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search Vehicles';
                searchBtn.disabled = false;
            }
        });
    }
    
    // Function to search dropoff vehicles for Point-to-Point mode
    function searchDropoffVehiclesForPointToPoint() {
        console.log('Searching Point-to-Point vehicles for dropoff transport modal');
        
        // Get the selected city from the dropdown
        const city = document.getElementById('modal_exitport_transport_city').value;
        const country = document.getElementById('modal_dropoff_transport_country').value;
        const searchBtn = document.getElementById('dropoff_transport_search_btn');
        const vehicleResultsSection = document.getElementById('dropoff_vehicle_results');
        const vehicleSelect = document.getElementById('modal_dropoff_transport_vehicle_id');
        const serviceTypeSelect = document.getElementById('modal_dropoff_transport_service_type');
        
        console.log('Using Point-to-Point endpoint for city:', city, 'country:', country);
        
        // Check if city is selected
        if (!city || city.trim() === '') {
            showNotification('Please select a city first', 'warning');
            return;
        }

        // Show loading state
        if (searchBtn) {
            searchBtn.innerHTML = '<i class="ri-loader-4-line spin me-2"></i>Searching...';
            searchBtn.disabled = true;
        }

        const user_dmc = @json($UserDmc);
        const zone_status = user_dmc.zone_on;

        fetch(`{{ route('fetch-vehicles-by-city-dmc') }}?city=${encodeURIComponent(city)}&zone_status=${zone_status}`)
            .then(response => response.json())
            .then(data => {
                console.log('Point-to-Point dropoff vehicle search response:', data);
                
                if (data.success && data.vehicles && data.vehicles.length > 0) {
                    // Show the vehicle results section
                    if (vehicleResultsSection) {
                        vehicleResultsSection.style.display = 'block';
                        vehicleResultsSection.style.visibility = 'visible';
                        vehicleResultsSection.classList.remove('d-none');
                        vehicleResultsSection.classList.add('d-block');
                        console.log('Dropoff vehicle results section shown (point-to-point)');
                    } else {
                        console.error('Dropoff vehicle results section not found!');
                    }
                    
                    // Populate vehicle dropdown
                    if (vehicleSelect) {
                        vehicleSelect.innerHTML = '<option value="">Choose vehicle</option>';
                        data.vehicles.forEach(vehicle => {
                            const vehicleInfo = `${vehicle.vehicle_name} (${vehicle.vehicle_type}) - ${vehicle.seating_capacity} seats`;
                            
                            // Debug: Log vehicle data before stringifying
                            console.log('Vehicle data for dropoff point-to-point:', vehicle);
                            
                            try {
                                const vehicleDataString = JSON.stringify(vehicle);
                                vehicleSelect.innerHTML += `<option value="${vehicle.vehicle_id}" 
                                    data-vehicle-name="${vehicle.vehicle_name}" 
                                    data-vehicle-type="${vehicle.vehicle_type}" 
                                    data-seating-capacity="${vehicle.seating_capacity}"
                                    data-private-price="${vehicle.private_price || ''}"
                                    data-shared-price="${vehicle.shared_price || ''}"
                                    data-service-type="${vehicle.service_type || ''}"
                                    data-cost-per-hour="${vehicle.cost_per_hour || ''}"
                                    data-sharable-cost-per-hour="${vehicle.sharable_cost_per_hour || ''}"
                                    data-vehicle="${vehicleDataString}"
                                    data-sharable="${vehicle.sharable || '0'}">
                                    ${vehicleInfo}
                                </option>`;
                            } catch (error) {
                                console.error('Error stringifying vehicle data:', error);
                                console.log('Problematic vehicle object:', vehicle);
                                // Fallback: create option without data-vehicle attribute
                                vehicleSelect.innerHTML += `<option value="${vehicle.vehicle_id}">
                                    ${vehicleInfo}
                                </option>`;
                            }
                        });
                        vehicleSelect.disabled = false;
                        
                        // Enable service type select
                        if (serviceTypeSelect) {
                            serviceTypeSelect.disabled = false;
                        }
                        
                        // Add event listener for vehicle selection
                        vehicleSelect.addEventListener('change', function() {
                            updateDropoffVehicleDetails();
                        });
                        
                        console.log(`Populated ${data.vehicles.length} vehicles for Point-to-Point dropoff transport modal`);
                    } else {
                        console.error('Dropoff vehicle select not found!');
                    }
                } else {
                    alert('No vehicles available for this city. Please try a different city.');
                }
                
                // Reset search button
                if (searchBtn) {
                    searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search Vehicles';
                    searchBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error searching Point-to-Point dropoff vehicles:', error);
                alert('Error searching vehicles. Please try again.');
                if (searchBtn) {
                    searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search Vehicles';
                    searchBtn.disabled = false;
                }
            });
    }

    function updateDropoffVehicleDetails() {
        const vehicleSelect = document.getElementById('modal_dropoff_transport_vehicle_id');
        const serviceTypeSelect = document.getElementById('modal_dropoff_transport_service_type');
        const manualPriceContainer = document.getElementById('dropoff_transport_manual_price_field_container');
        
        if (vehicleSelect && vehicleSelect.value && serviceTypeSelect) {
            // Get selected vehicle data
            const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
            console.log('Dropoff vehicle details - raw data:', selectedOption.getAttribute('data-vehicle'));
            
            // Try both data access methods to ensure compatibility
            let vehicleData;
            try {
                vehicleData = JSON.parse(selectedOption.getAttribute('data-vehicle') || '{}');
            } catch (e) {
                console.log('JSON parse failed, using dataset fallback:', e);
                // Fallback to dataset method like pickup transport
                vehicleData = {
                    id: selectedOption.value,
                    vehicle_name: selectedOption.dataset.vehicleName,
                    vehicle_type: selectedOption.dataset.vehicleType,
                    seating_capacity: selectedOption.dataset.seatingCapacity,
                    base_price: selectedOption.dataset.privatePrice,
                    sharable_base_price: selectedOption.dataset.sharedPrice,
                    service_type: selectedOption.dataset.serviceType,
                    sharable: selectedOption.dataset.sharable
                };
            }
            
            console.log('Dropoff vehicle details - parsed data:', vehicleData);
            
            // Update service type options based on vehicle sharable property
            updateServiceTypeOptions(vehicleData, 'modal_dropoff_transport_service_type');
            
            serviceTypeSelect.disabled = false;
            
            // Always show manual price field for both zone on and zone off modes
            if (manualPriceContainer) {
                manualPriceContainer.style.display = 'block';
            }
            
            updateDropoffPricing();
        }
    }

    function updateDropoffPricing() {
        const vehicleSelect = document.getElementById('modal_dropoff_transport_vehicle_id');
        const serviceTypeSelect = document.getElementById('modal_dropoff_transport_service_type');
        const passengersInput = document.getElementById('modal_dropoff_transport_passengers');
        const manualPriceInput = document.getElementById('modal_dropoff_transport_manual_price');
        const priceDisplay = document.getElementById('dropoff_transport_price_display');
        const priceDetails = document.getElementById('dropoff_transport_price_details');
        
        if (!vehicleSelect || !serviceTypeSelect || !passengersInput || !priceDisplay || !priceDetails) {
            console.log('Missing required elements for dropoff pricing update');
            return;
        }
        
        // Validate passenger count
        const passengers = parseInt(passengersInput.value) || 1;
        const maxPassengers = parseInt(passengersInput.getAttribute('max')) || 1;
        
        if (passengers > maxPassengers) {
            passengersInput.value = maxPassengers;
            showNotification(`Number of passengers cannot exceed ${maxPassengers} (total adults + children in tour)`, 'warning');
            return;
        }
        
        if (vehicleSelect.value || serviceTypeSelect.value) {
            console.log('Dropoff vehicle and service type selected');
            const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
            //console.log('dropoff vehicle data:', selectedOption.getAttribute('data-vehicle'));

            // Try both data access methods to ensure compatibility
            let vehicleData;
            try {
                vehicleData = JSON.parse(selectedOption.getAttribute('data-vehicle') || '{}');
            } catch (e) {
                // Fallback to dataset method like pickup transport
                
            }
            vehicleData = {
                    id: selectedOption.value,
                    vehicle_name: selectedOption.dataset.vehicleName,
                    vehicle_type: selectedOption.dataset.vehicleType,
                    seating_capacity: selectedOption.dataset.seatingCapacity,
                    base_price: selectedOption.dataset.privatePrice,
                    sharable_base_price: selectedOption.dataset.sharedPrice,
                    service_type: selectedOption.dataset.serviceType,
                    sharable: selectedOption.dataset.sharable
                };
            
            console.log('dropoff vehicle data:', vehicleData);
            const serviceType = serviceTypeSelect.value;
            const passengers = parseInt(passengersInput.value) || 1;
            
            // Check if manual price is provided (for zone off mode)
            const manualPrice = manualPriceInput ? parseFloat(manualPriceInput.value) : 0;
            const isManualPriceUsed = manualPriceInput && manualPriceInput.value && manualPrice > 0;
            
            // Get correct price based on service type or use manual price
            let basePrice = 0;
            let totalPrice = 0;
            
            if (isManualPriceUsed) {
                // Use manual price for zone off mode
                basePrice = manualPrice;
                totalPrice = manualPrice;
            } else {
                // Use vehicle's default pricing
                if (serviceType === 'Private') {
                    basePrice = parseFloat(vehicleData.base_price) || 0;
                    totalPrice = basePrice;
                } else if (serviceType === 'Shared') {
                    basePrice = parseFloat(vehicleData.sharable_base_price) || 0;
                    totalPrice = basePrice * passengers; // Fix: multiply by passengers for shared service
                }
            }
            
            // Format price details
            const priceSource = isManualPriceUsed ? 'Manual Price' : 'Vehicle Price';
            const priceSourceIcon = isManualPriceUsed ? 'ri-edit-line' : 'ri-car-line';
            
            priceDetails.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <i class="${priceSourceIcon} me-1"></i>
                        ${priceSource}: $${basePrice.toFixed(2)}
                        ${isManualPriceUsed ? '<span class="badge bg-info ms-1">Custom</span>' : ''}
                    </div>
                    <div class="col-md-4">Service: ${serviceType}</div>
                    <div class="col-md-4"><strong>Total: $${totalPrice.toFixed(2)}</strong></div>
                </div>
                <div class="small mt-2">
                    <i class="ri-information-line me-1"></i>
                    Vehicle: ${vehicleData.vehicle_name} (${vehicleData.seating_capacity} seats) - ${passengers} passengers
                    ${isManualPriceUsed ? '<br><i class="ri-edit-line me-1"></i>Using custom manual price override' : ''}
                </div>
            `;
            
            // Update hidden fields
            document.getElementById('dropoff_transport_base_price').value = basePrice.toFixed(2);
            document.getElementById('dropoff_transport_total_price').value = totalPrice.toFixed(2);
            
            priceDisplay.style.display = 'block';
        } else {
            console.log('Dropoff vehicle and service type not selected');
            priceDisplay.style.display = 'none';
        }
    }

    function confirmDropoffTransportSelection() {
        const formData = new FormData(document.getElementById('dropoffTransportSelectionForm'));
        const pickupTime = formData.get('pickup_time');
        const vehicleId = formData.get('vehicle_id');
        const serviceType = formData.get('service_type');
        const customer_info = getCustomerInfo();
        
        // Check if Point-to-Point mode is enabled
        const isPointToPointElement = document.getElementById('is_point_to_point');
        const isPointToPoint = isPointToPointElement && isPointToPointElement.value === '1';
        
        let pickupZoneId, dropoffZoneId, pickupZoneName, dropoffZoneName;
        
        if (isPointToPoint) {
            // Point-to-Point mode: get data from Google Maps inputs
            const pickupLocation = document.getElementById('modal_dropoff_transport_pickup_location');
            const dropoffLocation = document.getElementById('modal_dropoff_transport_dropoff_location');
            const pickupLat = document.getElementById('modal_dropoff_transport_pickup_lat');
            const pickupLng = document.getElementById('modal_dropoff_transport_pickup_lng');
            const dropoffLat = document.getElementById('modal_dropoff_transport_dropoff_lat');
            const dropoffLng = document.getElementById('modal_dropoff_transport_dropoff_lng');
            if (!pickupLocation || !dropoffLocation || !pickupTime || !vehicleId || !serviceType) {
                showNotification('Please complete all required fields', 'warning');
                return;
            }
            
            pickupZoneId = pickupLat.value + ',' + pickupLng.value;
            dropoffZoneId = dropoffLat.value + ',' + dropoffLng.value;
            pickupZoneName = pickupLocation.value;
            dropoffZoneName = dropoffLocation.value;
            
            console.log('Point-to-Point dropoff transport data:', {
                pickupLocation: pickupLocation.value,
                dropoffLocation: dropoffLocation.value,
                pickupCoords: pickupZoneId,
                dropoffCoords: dropoffZoneId
            });
        } else {
            // Zone-based mode: get data from form
            pickupZoneId = formData.get('pickup_zone_id');
            dropoffZoneId = formData.get('dropoff_zone_id');
            
            if (!pickupZoneId || !dropoffZoneId || !pickupTime || !vehicleId || !serviceType) {
                showNotification('Please complete all required fields', 'warning');
                return;
            }
            
            // Get zone names for display
            const pickupZoneSelect = document.getElementById('modal_dropoff_transport_pickup_zone');
            const dropoffZoneSelect = document.getElementById('modal_dropoff_transport_dropoff_zone');
            pickupZoneName = pickupZoneSelect.options[pickupZoneSelect.selectedIndex].text;
            dropoffZoneName = dropoffZoneSelect.options[dropoffZoneSelect.selectedIndex].text;
        }
        
        console.log('Customer info for dropoff transport:', customer_info);
        
        // Get selected vehicle details
        const vehicleSelect = document.getElementById('modal_dropoff_transport_vehicle_id');
        const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
        console.log('Raw vehicle data string:', selectedOption.getAttribute('data-vehicle'));

        
        const vehicleData = {
            id: selectedOption.value,
            name: selectedOption.dataset.vehicleName,
            type: selectedOption.dataset.vehicleType,
            capacity: selectedOption.dataset.seatingCapacity,
            privatePrice: selectedOption.dataset.privatePrice,
            sharedPrice: selectedOption.dataset.sharedPrice,
            serviceType: selectedOption.dataset.serviceType,
            sharable: selectedOption.dataset.sharable
        };

        console.log('Vehicle data from:', vehicleData);
        // Get tour details
        const tourId = document.getElementById('modal_dropoff_transport_tour_id').value;
        const pickupDate = document.getElementById('modal_dropoff_transport_pickup_date').value;
        const passengers = document.getElementById('modal_dropoff_transport_passengers').value;
        const totalPrice = document.getElementById('dropoff_transport_total_price').value;
        
        // Get the pickup and dropoff values (values are already the IDs)
        const pickupValue = pickupZoneId;
        const dropoffValue = dropoffZoneId;
        
        // Get the actual pickup and dropoff place IDs (values are already the IDs)
        const actualPickupPlaceId = pickupValue || null;
        const actualDropoffPlaceId = dropoffValue || null;

        // Build the transport booking data (updated format)
        const transportData = {
            fullName: customer_info.fullName,
            email: customer_info.email,
            phone: customer_info.phone,
            countryCode: customer_info.countryCode,
            address1: customer_info.address1,
            address2: customer_info.address2,
            state: customer_info.state,
            zip: customer_info.zip,
            specialRequests: customer_info.specialRequests,
            vehicles_id: vehicleId,
            image: vehicleData.image || "",
            dmc_id: vehicleData.dmc_id || "",
            vehicles_name: vehicleData.name || 'Vehicle',
            Mode: "dmc",
            type: serviceType,
            entrypickup: pickupZoneName,
            entrydropoff: dropoffZoneName,
            bookingDate: pickupDate,
            pickupdate: pickupDate,
            entrytime: pickupTime,
            PickupPlaceid: actualPickupPlaceId,
            DropoffPlaceid: actualDropoffPlaceId,
            adults: parseInt(passengers),
            children: 0,
            totalPrice: parseFloat(totalPrice),
            Tax: 0,
            distance: 0,
            Night_Start_Time: null,
            Night_End_Time: null,
            city: vehicleData.city || "Singapore",
            country: vehicleData.country || "Singapore",
            id: `entry-${Date.now()}`,
            vehicle_type: vehicleData.vehicleType || "",
            vehicle_model: vehicleData.vehicleModel || "",
            model_year: vehicleData.modelYear || null,
            seating_capacity: vehicleData.seatingCapacity || 0,
            booking_id: null
        };
        
        console.log('Dropoff Transport booking data:', transportData);
        
        // Create a form to submit the transport data (exact same as pickup service)
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('orders.transport.select') }}";
        
        // Add CSRF token
        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = "{{ csrf_token() }}";
        form.appendChild(token);
        
        // Add the transport data as JSON
        const transportDataInput = document.createElement('input');
        transportDataInput.type = 'hidden';
        transportDataInput.name = 'transport_data';
        transportDataInput.value = JSON.stringify([transportData]); // Wrap in array
        form.appendChild(transportDataInput);
        
        // Add basic form fields (same as pickup service)
        const basicData = {
            tour_id: tourId,
            type: "exit_port",
            agent_id: document.getElementById('agent_id').value,
            pickup_zone_id: pickupZoneId,
            dropoff_zone_id: dropoffZoneId,
            pickup_time: pickupTime,
            pickup_date: pickupDate,
            vehicle_id: vehicleId,
            service_type: serviceType,
            passengers: passengers
        };
        
        Object.keys(basicData).forEach(key => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = basicData[key];
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
        
        // Close modal safely
        safeCloseModal('dropoffTransportSelectionModal');
        
        // Show success message
        showNotification(`Dropoff transport service booked successfully! From: ${pickupZoneName} To: ${dropoffZoneName}`, 'success');
    }
    
    function updatePricing() {
        const vehicleSelect = document.getElementById('modal_transport_vehicle_id');
        const serviceTypeSelect = document.getElementById('modal_transport_service_type');
        const passengersInput = document.getElementById('modal_transport_passengers');
        const manualPriceInput = document.getElementById('modal_transport_manual_price');
        const priceDisplay = document.getElementById('transport_price_display');
        const priceDetails = document.getElementById('transport_price_details');
        
        if (!vehicleSelect || !serviceTypeSelect || !passengersInput || !priceDisplay || !priceDetails) {
            console.log('Missing required elements for pricing update');
            return;
        }
        
        // Validate passenger count
        const passengers = parseInt(passengersInput.value) || 1;
        const maxPassengers = parseInt(passengersInput.getAttribute('max')) || 1;
        
        if (passengers > maxPassengers) {
            passengersInput.value = maxPassengers;
            showNotification(`Number of passengers cannot exceed ${maxPassengers} (total adults + children in tour)`, 'warning');
            return;
        }
        
        if (vehicleSelect.value || serviceTypeSelect.value) {
            console.log('Vehicle and service type selected');
            const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
            console.log('vehicle data:', selectedOption.getAttribute('data-vehicle'));

            const vehicleData = {
                id: selectedOption.value,
                name: selectedOption.dataset.vehicleName,
                type: selectedOption.dataset.vehicleType,
                seatingCapacity: selectedOption.dataset.seatingCapacity,
                privatePrice: selectedOption.dataset.privatePrice,
                sharedPrice: selectedOption.dataset.sharedPrice,
                serviceType: selectedOption.dataset.serviceType,
                sharable: selectedOption.dataset.sharable
            };
            console.log('vehicle data:', vehicleData);
            const serviceType = serviceTypeSelect.value;
            const validatedPassengers = parseInt(passengersInput.value) || 1;
            
            // Check if manual price is provided (for zone off mode)
            const manualPrice = manualPriceInput ? parseFloat(manualPriceInput.value) : 0;
            const isManualPriceUsed = manualPriceInput && manualPriceInput.value && manualPrice > 0;
            
            // Get correct price based on service type or use manual price
            let basePrice = 0;
            let totalPrice = 0;
            
            if (isManualPriceUsed) {
                // Use manual price for zone off mode
                basePrice = manualPrice;
                totalPrice = manualPrice;
            } else {
                // Use vehicle's default pricing
                if (serviceType === 'Private') {
                    basePrice = parseFloat(vehicleData.privatePrice) || 0;
                    totalPrice = basePrice;
                } else if (serviceType === 'Shared') {
                    basePrice = parseFloat(vehicleData.sharedPrice) || 0;
                    totalPrice = basePrice * validatedPassengers;
                }
            }
            
            // Format price details
            const priceSource = isManualPriceUsed ? 'Manual Price' : 'Vehicle Price';
            const priceSourceIcon = isManualPriceUsed ? 'ri-edit-line' : 'ri-car-line';
            
            priceDetails.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <i class="${priceSourceIcon} me-1"></i>
                        <span style="color: #26c6f9;">${priceSource}: $${basePrice.toFixed(2)}</span>
                        ${isManualPriceUsed ? '<span class="badge bg-info ms-1">Custom</span>' : ''}
                    </div>
                    <div class="col-md-4"><span style="color: #26c6f9;">Service: ${serviceType}</span></div>
                    <div class="col-md-4"><strong style="color: #26c6f9;">Total: $${totalPrice.toFixed(2)}</strong></div>
                </div>
                <div class="small mt-2">
                    <i class="ri-information-line me-1"></i>
                    <span style="color: #26c6f9;">Vehicle: ${vehicleData.name} (${vehicleData.seatingCapacity} seats) - ${validatedPassengers} passengers</span>
                    ${isManualPriceUsed ? '<br><i class="ri-edit-line me-1"></i><span style="color: #26c6f9;">Using custom manual price override</span>' : ''}
                </div>
            `;
            
            // Update hidden fields
            document.getElementById('modal_transport_base_price').value = basePrice.toFixed(2);
            document.getElementById('modal_transport_total_price').value = totalPrice.toFixed(2);
            
            priceDisplay.style.display = 'block';
        } else {
            console.log('Vehicle and service type not selected');
            priceDisplay.style.display = 'none';
        }
    }
    
    function updateLocalTransferPricing() {
        const vehicleSelect = document.getElementById('local_transfer_vehicle_id');
        const serviceTypeSelect = document.getElementById('local_transfer_service_type');
        const passengersInput = document.getElementById('local_transfer_passengers');
        const manualPriceInput = document.getElementById('local_transfer_manual_price');
        const priceDisplay = document.getElementById('local_transfer_price_display');
        const priceDetails = document.getElementById('local_transfer_price_details');
        const hoursSelect = document.getElementById('local_transfer_hourly_hours');
        
        if (!vehicleSelect || !serviceTypeSelect || !passengersInput || !priceDisplay || !priceDetails) {
            return;
        }
        
        // Validate passenger count
        const passengers = parseInt(passengersInput.value) || 1;
        const maxPassengers = parseInt(passengersInput.getAttribute('max')) || 1;
        
        if (passengers > maxPassengers) {
            passengersInput.value = maxPassengers;
            showNotification(`Number of passengers cannot exceed ${maxPassengers} (total adults + children in tour)`, 'warning');
            return;
        }
        
        if (vehicleSelect.value && serviceTypeSelect.value) {
            const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
            const vehicleData = {
                id: selectedOption.value,
                name: selectedOption.dataset.vehicleName,
                type: selectedOption.dataset.vehicleType,
                seatingCapacity: selectedOption.dataset.seatingCapacity,
                privatePrice: selectedOption.dataset.privatePrice,
                sharedPrice: selectedOption.dataset.sharedPrice,
                serviceType: selectedOption.dataset.serviceType,
                costPerHour: selectedOption.dataset.costPerHour,
                sharableCostPerHour: selectedOption.dataset.sharableCostPerHour,
            };
            console.log('vehicle data:', vehicleData);
            const serviceType = serviceTypeSelect.value;
            const validatedPassengers = parseInt(passengersInput.value) || 1;
            
            // Check if manual price is provided (for point-to-point)
            const manualPrice = manualPriceInput ? parseFloat(manualPriceInput.value) : 0;
            const isManualPriceUsed = manualPriceInput && manualPriceInput.value && manualPrice > 0;
            
            // Check if this is hourly service
            const selectedServiceType = document.querySelector('input[name="service_type_selection"]:checked');
            const isHourlyService = selectedServiceType && selectedServiceType.value === 'hourly';
            const selectedHours = hoursSelect ? parseInt(hoursSelect.value) || 0 : 0;
            
            // Get correct price based on service type or use manual price
            let basePrice = 0;
            let totalPrice = 0;
            let priceMultiplier = 1;
            let priceMultiplierText = '';
            
            if (isManualPriceUsed) {
                // Use manual price for point-to-point
                basePrice = manualPrice;
                totalPrice = manualPrice;
            } else {
                // Use vehicle's default pricing
                if (serviceType == 'Private') {
                    basePrice = parseFloat(vehicleData.privatePrice) || 0;
                    
                    // Apply hourly calculation if this is hourly service
                    if (isHourlyService && selectedHours > 0) {
                        const costPerHour = parseFloat(vehicleData.costPerHour) || 0;
                        priceMultiplier = selectedHours;
                        totalPrice = basePrice + (selectedHours * costPerHour);
                        priceMultiplierText = ` + (${selectedHours} hrs × $${costPerHour.toFixed(2)})`;
                    } else {
                        totalPrice = basePrice;
                    }
                } else if (serviceType == 'Shared') {
                    basePrice = parseFloat(vehicleData.sharedPrice) || 0;
                    
                    // Apply hourly calculation if this is hourly service
                    if (isHourlyService && selectedHours > 0) {
                        const sharableCostPerHour = parseFloat(vehicleData.sharableCostPerHour) || 0;
                        priceMultiplier = selectedHours;
                        totalPrice = basePrice + (selectedHours * sharableCostPerHour * validatedPassengers);
                        priceMultiplierText = ` + (${selectedHours} hrs × $${sharableCostPerHour.toFixed(2)} × ${validatedPassengers} pax)`;
                    } else {
                        totalPrice = basePrice * validatedPassengers;
                    }
                }
            }
            
            // Format price details
            const priceSource = isManualPriceUsed ? 'Manual Price' : 'Vehicle Price';
            const priceSourceIcon = isManualPriceUsed ? 'ri-edit-line' : 'ri-car-line';
            
            // Format price calculation for better readability
            let priceCalculationText = '';
            if (isHourlyService && selectedHours > 0 && !isManualPriceUsed) {
                if (serviceType == 'Private') {
                    const costPerHour = parseFloat(vehicleData.costPerHour) || 0;
                    priceCalculationText = `$${basePrice.toFixed(2)} + (${selectedHours} hrs × $${costPerHour.toFixed(2)}) = $${totalPrice.toFixed(2)}`;
                } else if (serviceType == 'Shared') {
                    const sharableCostPerHour = parseFloat(vehicleData.sharableCostPerHour) || 0;
                    priceCalculationText = `$${basePrice.toFixed(2)} + (${selectedHours} hrs × $${sharableCostPerHour.toFixed(2)} × ${validatedPassengers} pax) = $${totalPrice.toFixed(2)}`;
                }
            } else {
                priceCalculationText = `$${basePrice.toFixed(2)}${priceMultiplierText}`;
            }
            const costPerHour = parseFloat(vehicleData.costPerHour) || 0;

            priceDetails.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <i class="${priceSourceIcon} me-1"></i>
                        ${isHourlyService && selectedHours > 0 ? `<span class="badge bg-warning ms-1">Hourly Rate: $${costPerHour.toFixed(2)}</span>` : ''}
                        <span style="white-space: nowrap; color: #26c6f9;">${priceSource}: ${priceCalculationText}</span>
                        ${isManualPriceUsed ? '<span class="badge bg-info ms-1">Custom</span>' : ''}
                        ${isHourlyService && selectedHours > 0 ? '<span class="badge bg-warning ms-1">Hourly</span>' : ''}
                    </div>
                    <div class="col-md-3"><span style="color: #26c6f9;">Service: ${serviceType}</span></div>
                    <div class="col-md-3"><strong style="color: #26c6f9;">Total: $${totalPrice.toFixed(2)}</strong></div>
                </div>
                <div class="small mt-2">
                    <i class="ri-information-line me-1"></i>
                    <span style="color: #26c6f9;">Vehicle: ${vehicleData.name} (${vehicleData.seatingCapacity} seats) - ${passengers} passengers</span>
                    ${isHourlyService && selectedHours > 0 ? `<br><i class="ri-time-line me-1"></i><span style="color: #26c6f9;">Duration: ${selectedHours} hour${selectedHours > 1 ? 's' : ''}</span>` : ''}
                    ${isManualPriceUsed ? '<br><i class="ri-edit-line me-1"></i><span style="color: #26c6f9;">Using custom manual price override</span>' : ''}
                </div>
            `;
            
            // Update hidden fields
            document.getElementById('local_transfer_base_price').value = basePrice.toFixed(2);
            document.getElementById('local_transfer_total_price').value = totalPrice.toFixed(2);
            
            // Update manual price hidden field
            const manualPriceHidden = document.getElementById('local_transfer_manual_price_submitted');
            if (manualPriceHidden) {
                manualPriceHidden.value = isManualPriceUsed ? manualPrice.toFixed(2) : '';
            }
            
            priceDisplay.style.display = 'block';
        } else {
            priceDisplay.style.display = 'none';
        }
    }


    function confirmTransportSelection() {
        const formData = new FormData(document.getElementById('transportSelectionForm'));
        const pickupTime = formData.get('pickup_time');
        const vehicleId = formData.get('vehicle_id');
        const serviceType = formData.get('service_type');
        const customer_info = getCustomerInfo();
        
        // Check if Point-to-Point mode is enabled
        const isPointToPointElement = document.getElementById('is_point_to_point');
        const isPointToPoint = isPointToPointElement && isPointToPointElement.value === '1';
        
        let pickupZoneId, dropoffZoneId, pickupZoneName, dropoffZoneName;
        
        if (isPointToPoint) {
            // Point-to-Point mode: get data from Google Maps inputs
            const pickupLocation = document.getElementById('modal_transport_pickup_location');
            const dropoffLocation = document.getElementById('modal_transport_dropoff_location');
            const pickupLat = document.getElementById('modal_transport_pickup_lat');
            const pickupLng = document.getElementById('modal_transport_pickup_lng');
            const dropoffLat = document.getElementById('modal_transport_dropoff_lat');
            const dropoffLng = document.getElementById('modal_transport_dropoff_lng');
            
            if (!pickupLocation || !dropoffLocation || !pickupTime || !vehicleId || !serviceType) {
                showNotification('Please complete all required fields', 'warning');
                return;
            }
            
            pickupZoneId = pickupLat.value + ',' + pickupLng.value;
            dropoffZoneId = dropoffLat.value + ',' + dropoffLng.value;
            pickupZoneName = pickupLocation.value;
            dropoffZoneName = dropoffLocation.value;
            
            console.log('Point-to-Point entry transport data:', {
                pickupLocation: pickupLocation.value,
                dropoffLocation: dropoffLocation.value,
                pickupCoords: pickupZoneId,
                dropoffCoords: dropoffZoneId
            });
        } else {
            // Zone-based mode: get data from form
            pickupZoneId = formData.get('pickup_zone_id');
            dropoffZoneId = formData.get('dropoff_zone_id');
            
            if (!pickupZoneId || !dropoffZoneId || !pickupTime || !vehicleId || !serviceType) {
                showNotification('Please complete all required fields', 'warning');
                return;
            }
            
            // Get location details
            const pickupZoneSelect = document.getElementById('modal_transport_pickup_zone');
            const dropoffZoneSelect = document.getElementById('modal_transport_dropoff_zone');
            pickupZoneName = pickupZoneSelect.options[pickupZoneSelect.selectedIndex].text;
            dropoffZoneName = dropoffZoneSelect.options[dropoffZoneSelect.selectedIndex].text;
        }
        
        console.log('Customer info:', customer_info);
        
        // Get selected vehicle details
        const vehicleSelect = document.getElementById('modal_transport_vehicle_id');
        const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
        let vehicleData = {};
        if (selectedOption) {
            try {
                const vehicleDataString = selectedOption.getAttribute('data-vehicle');
                vehicleData = vehicleDataString ? JSON.parse(vehicleDataString) : {};
            } catch (error) {
                console.error('Error parsing vehicle data:', error);
                console.log('Raw vehicle data string:', selectedOption.getAttribute('data-vehicle'));
                vehicleData = {};
            }
        }
        
        console.log('Vehicle data:', vehicleData);
        
        // Get tour details
        const tourId = document.getElementById('modal_transport_tour_id').value;
        const country = document.getElementById('modal_transport_country').value;
        const city = document.getElementById('modal_transport_city').value;
        const startDate = document.getElementById('modal_transport_start_date').value;
        const endDate = document.getElementById('modal_transport_end_date').value;
        const pickupDate = document.getElementById('modal_transport_pickup_date').value;
        const passengers = document.getElementById('modal_transport_passengers').value;
        const totalPrice = document.getElementById('modal_transport_total_price').value;
        
        // Get the pickup and dropoff values (values are already the IDs)
        const pickupValue = pickupZoneId;
        const dropoffValue = dropoffZoneId;
        
        // Check if this is a local transfer or transport service
        const isLocalTransfer = document.getElementById('transportSelectionModalLabel').innerHTML.includes('Local Transfer');
        const serviceTypeLabel = isLocalTransfer ? 'local_transfer' : 'travel_point';
        
        // Get the actual pickup and dropoff place IDs (values are already the IDs)
        const actualPickupPlaceId = pickupValue || null;
        const actualDropoffPlaceId = dropoffValue || null;

        // Get transport type from hidden field (entry_port or exit_port)
        const transportType = document.getElementById('modal_transport_type').value || 'entry_port';
        
        // Build the transport booking data (updated format)
        const transportData = {
            fullName: customer_info.fullName,
            email: customer_info.email,
            phone: customer_info.phone,
            countryCode: customer_info.countryCode,
            address1: customer_info.address1,
            address2: customer_info.address2,
            state: customer_info.state,
            zip: customer_info.zip,
            specialRequests: customer_info.specialRequests,
            vehicles_id: vehicleId,
            image: vehicleData.image || "",
            dmc_id: vehicleData.dmc_id || "",
            vehicles_name: vehicleData.vehicle_name || 'Vehicle',
            Mode: "dmc",
            type: serviceType,
            bookingDate: pickupDate,
            pickupdate: pickupDate,
            PickupPlaceid: actualPickupPlaceId,
            DropoffPlaceid: actualDropoffPlaceId,
            adults: parseInt(passengers),
            children: 0,
            totalPrice: parseFloat(totalPrice),
            Tax: 0,
            distance: 0,
            Night_Start_Time: null,
            Night_End_Time: null,
            city: vehicleData.city || "Singapore",
            country: vehicleData.country || "Singapore",
            vehicle_type: vehicleData.vehicle_type || "",
            vehicle_model: vehicleData.vehicle_model || "",
            model_year: vehicleData.model_year || null,
            seating_capacity: vehicleData.seating_capacity || 0,
            booking_id: null
        };
        
        // Update transport data based on type (entry_port or exit_port)
        if (transportType === 'exit_port') {
            transportData.exitpickup = pickupZoneName;
            transportData.exitdropoff = dropoffZoneName;
            transportData.exitpickupdate = pickupTime;
            transportData.id = `exit-${Date.now()}`;
        } else {
            transportData.entrypickup = pickupZoneName;
            transportData.entrydropoff = dropoffZoneName;
            transportData.entrytime = pickupTime;
            transportData.id = `entry-${Date.now()}`;
        }
        
        console.log('Transport booking data:', transportData);
        
        // Create a form to submit the transport data
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('orders.transport.select') }}";
        
        // Add CSRF token
        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = "{{ csrf_token() }}";
        form.appendChild(token);
        
        // Add the transport data as JSON
        const transportDataInput = document.createElement('input');
        transportDataInput.type = 'hidden';
        transportDataInput.name = 'transport_data';
        transportDataInput.value = JSON.stringify([transportData]); // Wrap in array
        form.appendChild(transportDataInput);
        
        // Add basic form fields
        const basicData = {
            tour_id: tourId,
            type: transportType,
            agent_id: document.getElementById('agent_id').value,
            pickup_zone_id: pickupZoneId,
            dropoff_zone_id: dropoffZoneId,
            pickup_time: pickupTime,
            pickup_date: pickupDate,
            vehicle_id: vehicleId,
            service_type: serviceType,
            passengers: passengers,
            country: country,
            city: city,
            transport_type: serviceTypeLabel // Add transport type to differentiate between transport and local transfer
        };
        
        for (const [key, value] of Object.entries(basicData)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }
        
        document.body.appendChild(form);
        form.submit();
        
        // Close modal safely
        safeCloseModal('transportSelectionModal');
        
        // Show success message
        const serviceLabel = isLocalTransfer ? 'Local transfer' : 'Transport';
        showNotification(`${serviceLabel} service booked successfully! From: ${pickupZoneName} To: ${dropoffZoneName}`, 'success');
    }

    // Confirmation function for Point-to-Point service
    function confirmPointToPointSelection() {
        const pickupLocation = document.getElementById('local_transfer_point_pickup_location').value;
        const dropoffLocation = document.getElementById('local_transfer_point_dropoff_location').value;
        const pickupTime = document.getElementById('local_transfer_point_pickup_time').value;
        const pickupDate = document.getElementById('local_transfer_point_pickup_date').value;
        const vehicleId = document.getElementById('local_transfer_vehicle_id').value;
        const serviceType = document.getElementById('local_transfer_service_type').value;
        const passengers = document.getElementById('local_transfer_passengers').value;
        const totalPrice = document.getElementById('local_transfer_total_price').value;
        
        if (!pickupLocation || !dropoffLocation || !pickupTime || !pickupDate || !vehicleId || !serviceType) {
            showNotification('Please complete all required fields', 'warning');
            return;
        }
        
        const customer_info = getCustomerInfo();
        
        // Get selected vehicle details
        const vehicleSelect = document.getElementById('local_transfer_vehicle_id');
        const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
        let vehicleData = {};
        
        if (selectedOption) {
            const dataVehicleAttr = selectedOption.getAttribute('data-vehicle');
            if (dataVehicleAttr && dataVehicleAttr.trim() !== '') {
                try {
                    vehicleData = JSON.parse(dataVehicleAttr);
                } catch (error) {
                    console.warn('Invalid JSON in data-vehicle attribute:', dataVehicleAttr);
                    vehicleData = {};
                }
            }
        }
        
        // Get tour details
        const tourId = document.getElementById('local_transfer_tour_id').value;
        const country = document.getElementById('local_transfer_country').value;
        const city = document.getElementById('local_transfer_city').value;
        
        // Get coordinates from hidden fields
        const pickupLat = document.getElementById('local_transfer_point_pickup_lat').value;
        const pickupLng = document.getElementById('local_transfer_point_pickup_lng').value;
        const dropoffLat = document.getElementById('local_transfer_point_dropoff_lat').value;
        const dropoffLng = document.getElementById('local_transfer_point_dropoff_lng').value;

        const dmcUser = @json($UserDmc);
        
        // Build the booking data in required format
        const bookingData = [{
            bookingDate: pickupDate,
            vehicles_id: vehicleId,
            vehicles_name: vehicleData.vehicle_name || 'Vehicle',
            image: vehicleData.vehicle_image || '',
            dmc_id: dmcUser.userId || '',
            Mode: 'dmc',
            type: serviceType,
            entrypickup: pickupLocation,
            PickupPlaceid: {
                lat: pickupLat || '',
                lng: pickupLng || ''
            },
            dropoffLocation: dropoffLocation,
            DropoffPlaceid: {
                lat: dropoffLat || '',
                lng: dropoffLng || ''
            },
            exitpickupdate: pickupDate,
            entrytime: pickupTime,
            adults: passengers || '1',
            children: '0',
            selectedHours: '1',
            totalPrice: totalPrice || '0.00',
            Tax: '7.00',
            Night_Start_Time: '22:00:00',
            Night_End_Time: '06:00:00',
            // city parameter removed,
            country: country,
            fullName: customer_info.fullName,
            email: customer_info.email,
            phone: customer_info.phone,
            countryCode: customer_info.countryCode || null,
            address1: customer_info.address1,
            address2: customer_info.address2,
            state: customer_info.state,
            zip: customer_info.zip,
            specialRequests: customer_info.specialRequests || null,
            userInfo: {
                fullName: customer_info.fullName,
                email: customer_info.email,
                phone: customer_info.phone,
                address1: customer_info.address1,
                address2: customer_info.address2,
                state: customer_info.state,
                zip: customer_info.zip
            },
            bookingType: 'booking',
            service_category: 'point_to_point',
            tour_id: tourId
        }];
        
        console.log('Point-to-Point booking data:', bookingData);
        
        // Send data to controller
        submitLocalTransferBooking(bookingData, 'Point-to-Point transfer booked successfully!', 'travel_point');
    }
    
    // Confirmation function for Hourly service
    function confirmHourlySelection() {
        const pickupLocation = document.getElementById('local_transfer_hourly_pickup_location').value;
        const pickupTime = document.getElementById('local_transfer_hourly_pickup_time').value;
        const pickupDate = document.getElementById('local_transfer_hourly_pickup_date').value;
        const vehicleId = document.getElementById('local_transfer_vehicle_id').value;
        const serviceType = document.getElementById('local_transfer_service_type').value;
        const passengers = document.getElementById('local_transfer_passengers').value;
        const totalPrice = document.getElementById('local_transfer_total_price').value;
        
        if (!pickupLocation || !pickupTime || !pickupDate || !vehicleId || !serviceType) {
            showNotification('Please complete all required fields', 'warning');
            return;
        }
        
        const customer_info = getCustomerInfo();
        
        // Get selected vehicle details
        const vehicleSelect = document.getElementById('local_transfer_vehicle_id');
        const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
        let vehicleData = {};
        
        if (selectedOption) {
            const dataVehicleAttr = selectedOption.getAttribute('data-vehicle');
            if (dataVehicleAttr && dataVehicleAttr.trim() !== '') {
                try {
                    vehicleData = JSON.parse(dataVehicleAttr);
                } catch (error) {
                    console.warn('Invalid JSON in data-vehicle attribute:', dataVehicleAttr);
                    vehicleData = {};
                }
            }
        }
        
        // Get tour details
        const tourId = document.getElementById('local_transfer_tour_id').value;
        const country = document.getElementById('local_transfer_country').value;
        const city = document.getElementById('local_transfer_city').value;
        
        // Get coordinates from hidden fields
        const pickupLat = document.getElementById('local_transfer_hourly_pickup_lat').value;
        const pickupLng = document.getElementById('local_transfer_hourly_pickup_lng').value;

        const dmcUser = @json($UserDmc);
        // Build the booking data in required format
        const bookingData = [{
            bookingDate: pickupDate,
            vehicles_id: vehicleId,
            vehicles_name: vehicleData.vehicle_name || 'Vehicle',
            image: vehicleData.vehicle_image || '',
            dmc_id: dmcUser.userId || '',
            Mode: 'dmc',
            type: serviceType,
            entrypickup: pickupLocation,
            PickupPlaceid: {
                lat: pickupLat || '',
                lng: pickupLng || ''
            },
            exitpickupdate: pickupDate,
            entrytime: pickupTime,
            adults: passengers || '1',
            children: '0',
            selectedHours: '8', // Default 8 hours for hourly service
            totalPrice: totalPrice || '0.00',
            Tax: '7.00',
            Night_Start_Time: '22:00:00',
            Night_End_Time: '06:00:00',
            // city parameter removed,
            country: country,
            fullName: customer_info.fullName,
            email: customer_info.email,
            phone: customer_info.phone,
            countryCode: customer_info.countryCode || null,
            address1: customer_info.address1,
            address2: customer_info.address2,
            state: customer_info.state,
            zip: customer_info.zip,
            specialRequests: customer_info.specialRequests || null,
            userInfo: {
                fullName: customer_info.fullName,
                email: customer_info.email,
                phone: customer_info.phone,
                address1: customer_info.address1,
                address2: customer_info.address2,
                state: customer_info.state,
                zip: customer_info.zip
            },
            bookingType: 'booking',
            service_category: 'hourly',
            tour_id: tourId
        }];
        
        console.log('Hourly booking data:', bookingData);
        
        // Send data to controller
        submitLocalTransferBooking(bookingData, 'Hourly transfer booked successfully!', 'travel_hourly');
    }
    
    // Common function to submit booking data
    function submitLocalTransferBooking(bookingData, successMessage, serviceType) {
        // Send data to controller via AJAX
        fetch("{{ route('orders.local-transfer.select') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                booking_data: JSON.stringify(bookingData),
                type: serviceType
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal safely
                safeCloseModal('localTransferSelectionModal');
                
                // Show success message
                showNotification(data.message || successMessage, 'success');
                
                // Refresh the page to show the new service in the listing
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(data.message || 'Booking failed. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error submitting booking:', error);
            showNotification('An error occurred. Please try again.', 'error');
        });
    }

    // Function to determine which confirmation function to call based on selected service type
    function confirmSelectedLocalTransferService() {
        // Get the selected service type
        const pointToPointRadio = document.getElementById('local_transfer_service_type_point');
        const hourlyRadio = document.getElementById('local_transfer_service_type_hourly');
        const localTransferRadio = document.getElementById('local_transfer_service_type_local');
        
        if (pointToPointRadio && pointToPointRadio.checked) {
            confirmPointToPointSelection();
        } else if (hourlyRadio && hourlyRadio.checked) {
            confirmHourlySelection();
        } else if (localTransferRadio && localTransferRadio.checked) {
            confirmLocalTransferSelection();
        } else {
            showNotification('Please select a service type', 'warning');
        }
    }

    function confirmLocalTransferSelection() {
        const formData = new FormData(document.getElementById('localTransferSelectionForm'));
        const pickupZoneId = formData.get('pickup_zone_id');
        const dropoffZoneId = formData.get('dropoff_zone_id');
        const pickupTime = formData.get('pickup_time');
        const vehicleId = formData.get('vehicle_id');
        const serviceType = formData.get('service_type');
        const customer_info = getCustomerInfo();
        
        if (!pickupZoneId || !dropoffZoneId || !pickupTime || !vehicleId || !serviceType) {
            showNotification('Please complete all required fields', 'warning');
            return;
        }
        
        console.log('Customer info for local transfer:', customer_info);
        
        // Get selected vehicle details
        const vehicleSelect = document.getElementById('local_transfer_vehicle_id');
        const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
        let vehicleData = {};
        
        if (selectedOption) {
            const dataVehicleAttr = selectedOption.getAttribute('data-vehicle');
            if (dataVehicleAttr && dataVehicleAttr.trim() !== '') {
                try {
                    vehicleData = JSON.parse(dataVehicleAttr);
                } catch (error) {
                    console.warn('Invalid JSON in data-vehicle attribute:', dataVehicleAttr);
                    vehicleData = {};
                }
            }
        }
        
        console.log('Vehicle data for local transfer:', vehicleData);
        
        // Get tour details
        const tourId = document.getElementById('local_transfer_tour_id').value;
        const country = document.getElementById('local_transfer_country').value;
        const city = document.getElementById('local_transfer_city').value;
        const startDate = document.getElementById('local_transfer_start_date').value;
        const endDate = document.getElementById('local_transfer_end_date').value;
        const pickupDate = document.getElementById('local_transfer_pickup_date').value;
        const passengers = document.getElementById('local_transfer_passengers').value;
        const totalPrice = document.getElementById('local_transfer_total_price').value;
        
        // Get location details
        const pickupZoneSelect = document.getElementById('local_transfer_pickup_zone');
        const dropoffZoneSelect = document.getElementById('local_transfer_dropoff_zone');
        const pickupZoneName = pickupZoneSelect.options[pickupZoneSelect.selectedIndex].text;
        const dropoffZoneName = dropoffZoneSelect.options[dropoffZoneSelect.selectedIndex].text;
        
        // Get the pickup and dropoff values (values are already the IDs)
        const pickupValue = pickupZoneSelect.value;
        const dropoffValue = dropoffZoneSelect.value;

        const dmcUser = @json($UserDmc);
        
        // Build the booking data in required format
        const bookingData = [{
            bookingDate: pickupDate,
            vehicles_id: vehicleId,
            vehicles_name: vehicleData.vehicle_name || 'Vehicle',
            image: vehicleData.vehicle_image || '',
            dmc_id: dmcUser.userId || '',
            Mode: 'dmc',
            type: serviceType,
            entrypickup: pickupZoneName,
            PickupPlaceid: {
                lat: '', // Zone-based transfers don't have specific coordinates
                lng: ''
            },
            dropoffLocation: dropoffZoneName,
            DropoffPlaceid: {
                lat: '',
                lng: ''
            },
            exitpickupdate: pickupDate,
            entrytime: pickupTime,
            adults: passengers || '1',
            children: '0',
            selectedHours: '1',
            totalPrice: totalPrice || '0.00',
            Tax: '0',
            Night_Start_Time: '22:00:00',
            Night_End_Time: '06:00:00',
            // city parameter removed,
            country: country,
            fullName: customer_info.fullName,
            email: customer_info.email,
            phone: customer_info.phone,
            countryCode: customer_info.countryCode || null,
            address1: customer_info.address1,
            address2: customer_info.address2,
            state: customer_info.state,
            zip: customer_info.zip,
            specialRequests: customer_info.specialRequests || null,
            userInfo: {
                fullName: customer_info.fullName,
                email: customer_info.email,
                phone: customer_info.phone,
                address1: customer_info.address1,
                address2: customer_info.address2,
                state: customer_info.state,
                zip: customer_info.zip
            },
            bookingType: 'booking',
            service_category: 'local_transfer',
            tour_id: tourId,
            pickup_zone_id: pickupZoneId,
            dropoff_zone_id: dropoffZoneId
        }];
        
        console.log('Local Transfer booking data:', bookingData);
        
        // Send data to controller
        submitLocalTransferBooking(bookingData, `Local transfer service booked successfully! From: ${pickupZoneName} To: ${dropoffZoneName}`, 'local_transport');
    }
    
    const attractionBaseUrl = "{{ route('orders.attractions.select') }}";
    function confirmAttractionSelection() {
        const formData = new FormData(document.getElementById('attractionSelectionForm'));
        const attractionId = formData.get('attraction_id');
        const timeSlot = formData.get('time_slot');
        const ticketId = formData.get('modal_attraction_ticket');
        const visitDate = formData.get('visit_date');
        
        // Validate required fields before submission
        if (!attractionId) {
            showNotification('Please select an attraction', 'error');
            return;
        }
        if (!timeSlot) {
            showNotification('Please select a time slot', 'error');
            return;
        }
        if (!ticketId) {
            showNotification('Please select a ticket', 'error');
            return;
        }
        if (!visitDate) {
            showNotification('Please select a visit date', 'error');
            return;
        }
        
        const customer_info = getCustomerInfo();
        console.log('Customer info:', customer_info);
        const agentId = document.getElementById('agent_id').value;
        console.log('Agent id:', agentId);
        
        // Get selected attraction details
        const attractionSelect = document.getElementById('modal_attraction_select');
        const selectedOption = attractionSelect.options[attractionSelect.selectedIndex];
        const attractionData = selectedOption ? JSON.parse(selectedOption.getAttribute('data-attraction')) : {};
        console.log('Attraction data:', attractionData);
        
        const ticketSelect = document.getElementById('modal_attraction_ticket');
        const selectedTicketOption = ticketSelect.options[ticketSelect.selectedIndex];
        const ticketData = selectedTicketOption ? JSON.parse(selectedTicketOption.getAttribute('data-ticket')) : {};
        console.log('Ticket data:', ticketData);
        
        // Get guest data from modal
        const guestData = window.attractionModalGuestData || {
            adults: '1',
            children: '0',
            infants: '0',
            male_count: '1',
            female_count: '0',
            child_ages: ''
        };
        
        // Get tour details
        const tourId = document.getElementById('tour_id').value;
        const country = document.getElementById('user_country').value;
        
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        // Calculate pricing based on guest data
        const adultPrice = parseFloat(ticketData.adult_price || '0');
        const childPrice = parseFloat(ticketData.child_price || '0');
        const basePrice = adultPrice * parseInt(guestData.adults) + childPrice * parseInt(guestData.children);
        const totalPrice = basePrice;
        
        // Build the complex booking data structure in required format
        const bookingData = [{
            fullName: customer_info.fullName,
            email: customer_info.email,
            phone: customer_info.phone,
            countryCode: customer_info.countryCode || "65",
            address1: customer_info.address1,
            address2: customer_info.address2,
            state: customer_info.state,
            zip: customer_info.zip,
            specialRequests: customer_info.specialRequests,
            bookingDate: visitDate,
            visitTime: timeSlot,
            adultCount: parseInt(guestData.adults),
            childCount: parseInt(guestData.children),
            seniorCount: 0,
            AttractionId: parseInt(attractionId),
            AttractionName: attractionData.name || "",
            ticketId: parseInt(ticketId),
            ticketName: ticketData.name || "",
            ticket_details: {
                adult_price: parseFloat(ticketData.adult_price || 0),
                child_price: parseFloat(ticketData.child_price || 0),
                senior_price: parseFloat(ticketData.senior_price || 0),
                description: ticketData.description || "",
                nri: ticketData.nri || "residential"
            },
            transport: null,
            Selection: "withoutTransport",
            mode: "dmc",
            totalPrice: totalPrice,
            nri: ticketData.nri || "residential",
            bookingType: "enquiry",
            package_type: 0,
            package_attraction_id: attractionData.package_attraction_id || 0,
            dmc_id: Array.isArray(attractionData.dmc_id) ? attractionData.dmc_id[0] : attractionData.dmc_id
        }];

        console.log('Attraction booking data to be sent:', bookingData);

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = attractionBaseUrl;
        
        // Add CSRF token
        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = "{{ csrf_token() }}";
        form.appendChild(token);

        // Add the complex booking data as JSON
        const bookingDataInput = document.createElement('input');
        bookingDataInput.type = 'hidden';
        bookingDataInput.name = 'booking_data';
        bookingDataInput.value = JSON.stringify(bookingData);
        form.appendChild(bookingDataInput);

        // Add basic form fields for backward compatibility
        const basicData = {
            agent_id: agentId,
            tour_id: tourId,
            attraction_id: attractionId,
            time_slot: timeSlot,
            ticket_id: ticketId,
            visit_date: visitDate,
            adults: guestData.adults,
            children: guestData.children,
            infants: guestData.infants,
            male_count: guestData.male_count,
            female_count: guestData.female_count,
            child_ages: guestData.child_ages,
            country: country,
            // city parameter removed,
            start_date: startDate,
            end_date: endDate
        };

        for (const [key, value] of Object.entries(basicData)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }

        // Add customer_info fields for backward compatibility
        for (const [key, value] of Object.entries(customer_info)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `customer_info[${key}]`;
            input.value = value;
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();

        // Close modal safely
        safeCloseModal('attractionSelectionModal');
        
        // Show success message
        showNotification(`Attraction ${attractionData.name} selected successfully! Time: ${timeSlot}, Ticket: ${ticketData.name}`, 'success');
        
        // Here you can add logic to update the attraction fields in your form
        console.log('Selected attraction:', {
            id: attractionId,
            name: attractionData.name,
            timeSlot: timeSlot,
            ticket: ticketData.name
        });
    }

    function addAttractionService() {
        console.log('addAttractionService called');
        
        const tourId = document.getElementById('tour_id').value;
        const country = document.getElementById('user_country').value;
        
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        console.log('Tour data:', { tourId, country, startDate, endDate });
        
        if (!tourId) {
            showNotification('Tour ID is required', 'error');
            return false;
        }
        
        // Show attraction selection modal
        showAttractionSelectionModal(tourId, country, startDate, endDate);
        return false;
    }
    
    function addTransportService() {
        console.log('addTransportService called');
        
        const tourId = document.getElementById('tour_id').value;
        const country = document.getElementById('user_country').value;
        
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        console.log('Tour data for transport:', { tourId, country, startDate, endDate });
        
        if (!tourId) {
            showNotification('Tour ID is required', 'error');
            return false;
        }
        
        // Show transport selection modal (default to entry_port for arrival)
        showTransportSelectionModal(tourId, country, startDate, endDate, 'entry_port');
        return false;
    }
    
    function addLocalTransferService() {
        console.log('addLocalTransferService called');
        
        const tourId = document.getElementById('tour_id').value;
        const country = document.getElementById('user_country').value;
        
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        console.log('Tour data for local transfer:', { tourId, country, startDate, endDate });
        
        if (!tourId) {
            showNotification('Tour ID is required', 'error');
            return false;
        }
        
        // Show local transfer selection modal (using the same modal as transport)
        showLocalTransferSelectionModal(tourId, country, startDate, endDate);
        return false;
    }

    function addDropoffService() {
        console.log('addDropoffService called');
        
        const tourId = document.getElementById('tour_id').value;
        const country = document.getElementById('user_country').value;
        
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        console.log('Tour data for dropoff transport:', { tourId, country, startDate, endDate });
        
        if (!tourId) {
            showNotification('Tour ID is required', 'error');
            return false;
        }
        
        // Show dropoff transport selection modal
        showDropoffTransportSelectionModal(tourId, country, startDate, endDate);
        return false;
    }

    function addRestaurantService() {
        const tourId = document.getElementById('tour_id').value;
        const country = document.getElementById('user_country').value;
        
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (!tourId) {
            showNotification('Tour ID is required', 'error');
            return;
        }
        
        // Show restaurant selection modal
        showRestaurantSelectionModal(tourId, country, startDate, endDate);
    }
    
    // Hotel Modal Functions
    function initializeHotelModal() {
        // Note: Hotels will be loaded via addHotelService function
        
        // Initialize person selector with default max occupancy
        generatePersonSelector(2, false);
        
        // Add event listeners with null checks
        const checkInDate = document.getElementById('check_in_date');
        const checkOutDate = document.getElementById('check_out_date');
        const hotelSelect = document.getElementById('hotel_select');
        const proceedBtn = document.getElementById('proceed_hotel_btn');
        
        if (checkInDate) {
            checkInDate.addEventListener('change', updateNightsDisplay);
        }
        if (checkOutDate) {
            checkOutDate.addEventListener('change', updateNightsDisplay);
        }
        if (hotelSelect) {
            hotelSelect.addEventListener('change', onHotelSelection);
        }
        if (proceedBtn) {
            proceedBtn.addEventListener('click', proceedToHotelSelection);
        }
    }
    
    // Hotel Modal Functions - Chain-dependent dropdowns like create.blade.php
    function loadHotelsForSelectedCity(cityName) {
        const hotelSelect = document.getElementById('hotel_select');
        const hotelCount = document.getElementById('hotel_count');
        const cityDisplay = document.getElementById('modal_city_display2');
        const hotelLoadingStatus = document.getElementById('hotel_loading_status');
        
        if (!cityName) {
            // Reset to default state when no city selected
            hotelSelect.disabled = true;
            hotelSelect.innerHTML = '<option value="">Select city first to load hotels</option>';
            hotelCount.textContent = '0';
            if (cityDisplay) cityDisplay.textContent = 'No City';
            resetHotelModalFields();
            return;
        }
        
        // Show loading state
        hotelSelect.innerHTML = '<option value="">Loading hotels in ' + cityName + '...</option>';
        hotelSelect.disabled = true;
        if (hotelLoadingStatus) {
            hotelLoadingStatus.innerHTML = '<i class="ri-loader-2-line spin me-1"></i>Loading hotels...';
            hotelLoadingStatus.style.color = '#0d6efd';
        }
        
        // Reset dependent dropdowns
        resetHotelModalFields();
        
        // Get current user's DMC ID for hotel filtering
        const currentDmcId = document.getElementById('dmc_id').value;
        console.log('Loading hotels for city:', cityName, 'DMC ID:', currentDmcId);
        
        // Fetch hotels from API using DMC-specific endpoint (same as create.blade.php)
        fetch(`{{ route('fetch-hotels-by-dmc') }}?city=${encodeURIComponent(cityName)}&dmc_id=${currentDmcId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(response => {
                console.log('Hotel API Response:', response);
                
                hotelSelect.innerHTML = '<option value="">Select a hotel in ' + cityName + '</option>';
                hotelSelect.disabled = false;
                
                if (response.success && response.hotels && response.hotels.length > 0) {
                    // Store hotel data globally for room fetching
                    window.hotelData = response.hotels;
                    
                    response.hotels.forEach(hotel => {
                        const starInfo = hotel.hotel_star_rating ? ` (${hotel.hotel_star_rating}⭐)` : '';
                const option = document.createElement('option');
                option.value = hotel.hotel_unique_id;
                        option.textContent = hotel.name + starInfo;
                option.setAttribute('data-hotel', JSON.stringify(hotel));
                hotelSelect.appendChild(option);
            });
            
                    hotelCount.textContent = response.hotels.length;
                    if (hotelLoadingStatus) {
                        hotelLoadingStatus.innerHTML = `<i class="ri-check-line me-1 text-success"></i>${response.hotels.length} hotels found in ${cityName}`;
                        hotelLoadingStatus.style.color = '#198754';
                    }
                    console.log(`Loaded ${response.hotels.length} hotels for ${cityName}`);
                    
                    // Validate fields after hotels are loaded
                    validateHotelModalFields();
        } else {
                    window.hotelData = [];
                    hotelSelect.innerHTML = '<option value="">No hotels found in ' + cityName + '</option>';
            hotelCount.textContent = '0';
                    if (hotelLoadingStatus) {
                        hotelLoadingStatus.innerHTML = `<i class="ri-information-line me-1 text-warning"></i>No hotels found in ${cityName}`;
                        hotelLoadingStatus.style.color = '#fd7e14';
                    }
                }
                
                if (cityDisplay) {
                    cityDisplay.textContent = cityName;
                }
            })
            .catch(error => {
                console.error('Error loading hotels:', error);
                window.hotelData = [];
                hotelSelect.innerHTML = '<option value="">Error loading hotels</option>';
                hotelSelect.disabled = true;
                hotelCount.textContent = '0';
                if (hotelLoadingStatus) {
                    hotelLoadingStatus.innerHTML = '<i class="ri-error-warning-line me-1 text-danger"></i>Error loading hotels';
                    hotelLoadingStatus.style.color = '#dc3545';
                }
            });
    }
    
    function loadHotelsForCity() {
        // Legacy function for backward compatibility - now calls the new function
        const modalCitySelect = document.getElementById('modal_city_select');
        if (modalCitySelect && modalCitySelect.value) {
            loadHotelsForSelectedCity(modalCitySelect.value);
        }
    }
    
    function loadRoomsForSelectedHotel(hotelId) {
        const roomTypeSelect = document.getElementById('room_type');
        const bedTypeSelect = document.getElementById('bed_type');
        const mealPlanSelect = document.getElementById('meal_plan');
        
        if (!hotelId) {
            // Reset to default state when no hotel selected
            resetHotelModalFields();
            return;
        }
        
        console.log('Loading rooms for hotel:', hotelId);
        
        // Show loading state
        roomTypeSelect.innerHTML = '<option value="">Loading rooms...</option>';
        roomTypeSelect.disabled = true;
        bedTypeSelect.innerHTML = '<option value="">Loading rooms...</option>';
        bedTypeSelect.disabled = true;
        mealPlanSelect.innerHTML = '<option value="">Loading rooms...</option>';
        mealPlanSelect.disabled = true;
        
        // Get current user's DMC ID for room filtering
        const currentDmcId = document.getElementById('dmc_id').value;
        
        // Fetch rooms for the selected hotel with DMC filtering (same as create.blade.php)
        fetch(`{{ route('fetch-rooms-by-hotel') }}?hotel_id=${encodeURIComponent(hotelId)}&dmc_id=${currentDmcId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(response => {
                console.log('Rooms API Response:', response);
                
                // Clear dropdowns
                roomTypeSelect.innerHTML = '<option value="">Select room type</option>';
                bedTypeSelect.innerHTML = '<option value="">Select room type first</option>';
                mealPlanSelect.innerHTML = '<option value="">Select room type first</option>';
                
                if (response.success && response.rooms && response.rooms.length > 0) {
                    // Filter rooms by DMC ID using created_by field
                    let dmcFilteredRooms = response.rooms.filter(room => {
                        const roomDmcId = room.created_by;
                        return roomDmcId && roomDmcId == currentDmcId;
                    });
                    
                    console.log('Rooms after DMC filtering:', dmcFilteredRooms.length);
                    
                    if (dmcFilteredRooms.length === 0) {
                        console.warn(`No rooms found for DMC ${currentDmcId} in hotel ${hotelId}`);
                        roomTypeSelect.innerHTML = '<option value="">No rooms available for your DMC</option>';
                        bedTypeSelect.innerHTML = '<option value="">No rooms available for your DMC</option>';
                        mealPlanSelect.innerHTML = '<option value="">No rooms available for your DMC</option>';
                        return;
                    }
                    
                    // Store room data globally for bed fetching
                    window.roomData = dmcFilteredRooms;
                    
                    // Extract unique room types
                    const roomTypes = [...new Set(dmcFilteredRooms.map(room => room.room_type).filter(Boolean))];
                    console.log('Available room types:', roomTypes);
                    
                            // Populate room types with pricing information
                            roomTypes.forEach(roomType => {
                                const sampleRoom = dmcFilteredRooms.find(room => room.room_type === roomType);
                                
                                if (sampleRoom) {
                                    // Get guest count for pricing - use person_count_select from modal
                                    const personCountSelect = document.getElementById('person_count_select');
                                    const numberOfPersons = personCountSelect ? parseInt(personCountSelect.value) || 1 : 1;
                                    
                                    // Determine pricing based on occupancy
                                    const isSingleOccupancy = numberOfPersons <= 1;
                                    
                                    let price = 0;
                                    let priceText = '';
                                    
                                    if (isSingleOccupancy) {
                                        price = parseFloat(sampleRoom.weekday_price) || 0;
                                        priceText = ` - $${price}`;
                                    } else {
                                        price = parseFloat(sampleRoom.double_weekday_price) || 0;
                                        priceText = ` - $${price}`;
                                    }
                                    
                                    const option = document.createElement('option');
                                    option.value = roomType;
                                    option.textContent = `${roomType}${priceText}`;
                                    
                                    // Store room data in dataset for later use
                                    option.dataset.roomType = roomType;
                                    option.dataset.weekdayPrice = sampleRoom.weekday_price || 0;
                                    option.dataset.weekendPrice = sampleRoom.weekend_price || 0;
                                    option.dataset.doubleWeekdayPrice = sampleRoom.double_weekday_price || 0;
                                    option.dataset.doubleWeekendPrice = sampleRoom.double_weekend_price || 0;
                                    option.dataset.roomId = sampleRoom.room_id;
                                    option.dataset.breakfastPrice = sampleRoom.breakfast_price || 0;
                                    option.dataset.lunchPrice = sampleRoom.lunch_price || 0;
                                    option.dataset.dinnerPrice = sampleRoom.dinner_price || 0;
                                    option.dataset.breakfast = sampleRoom.breakfast || 0;
                                    option.dataset.lunch = sampleRoom.lunch || 0;
                                    option.dataset.dinner = sampleRoom.dinner || 0;
                                    
                                    roomTypeSelect.appendChild(option);
                                    console.log(`Added room type: ${roomType} with price $${price}`);
                                }
                            });
                            
                            // Update price after rooms are loaded
                            setTimeout(() => {
                                updateHotelModalPrice();
                            }, 100);
                    
                    roomTypeSelect.disabled = false;
                    console.log(`Loaded ${roomTypes.length} room types for hotel ${hotelId}`);
                    
                    // Validate fields after rooms are loaded
                    validateHotelModalFields();
                } else {
                    console.log('No rooms found for hotel:', hotelId);
                    roomTypeSelect.innerHTML = '<option value="">No rooms available</option>';
                }
            })
            .catch(error => {
                console.error('Error loading rooms:', error);
                roomTypeSelect.innerHTML = '<option value="">Error loading rooms</option>';
                bedTypeSelect.innerHTML = '<option value="">Error loading rooms</option>';
                mealPlanSelect.innerHTML = '<option value="">Error loading rooms</option>';
            });
    }
    
    function loadBedsForSelectedRoom(roomType) {
        const bedTypeSelect = document.getElementById('bed_type');
        const mealPlanSelect = document.getElementById('meal_plan');
        if (!roomType) {
            // Reset to default state when no room type selected
            bedTypeSelect.disabled = true;
            bedTypeSelect.innerHTML = '<option value="">Select room type first</option>';
            mealPlanSelect.disabled = true;
            mealPlanSelect.innerHTML = '<option value="">Select room type first</option>';
            return;
        }
        
        console.log('Loading beds for room type:', roomType);
        
        // Show loading state
        bedTypeSelect.innerHTML = '<option value="">Loading bed types...</option>';
        bedTypeSelect.disabled = true;
        mealPlanSelect.innerHTML = '<option value="">Loading meal plans...</option>';
        mealPlanSelect.disabled = true;
        
        // Find rooms of the selected type from stored room data
        if (!window.roomData) {
            console.error('No room data available');
            bedTypeSelect.innerHTML = '<option value="">No room data available</option>';
            mealPlanSelect.innerHTML = '<option value="">No room data available</option>';
            return;
        }
        
        const selectedRooms = window.roomData.filter(room => room.room_type === roomType);
        
        if (selectedRooms.length === 0) {
            bedTypeSelect.innerHTML = '<option value="">No rooms of this type</option>';
            mealPlanSelect.innerHTML = '<option value="">No rooms of this type</option>';
            return;
        }
        
        // Get the first room ID to fetch beds from beds table
        const firstRoom = selectedRooms[0];
        const roomId = firstRoom.room_id;
        
        console.log('Fetching beds for room ID:', roomId);
        
        // Fetch beds from the beds table using API endpoint (same as create.blade.php)
        fetch(`{{ route('fetch-beds-by-room') }}?room_id=${roomId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Beds API Response:', data);
                
                bedTypeSelect.innerHTML = '<option value="">Select bed type</option>';
                
                if (data.success && data.beds && data.beds.length > 0) {
                    // Populate bed types from the beds table
                    data.beds.forEach(bed => {
                        let bedTypeText = bed.room_type || bed.bed_type || 'Standard Bed';
                        
                        // Add occupancy info if available
                        if (bed.max_occupancy) {
                            bedTypeText += ` - Max ${bed.max_occupancy} guests`;
                        }
                        
                        // Add adult/child info if available
                        if (bed.adult_count && bed.child_count) {
                            bedTypeText += ` (${bed.adult_count}A+${bed.child_count}C)`;
                        }
                        
                        // Add extra bed info if available
                        if (bed.extra_bed) {
                            bedTypeText += ` + Extra Bed`;
                            if (bed.extra_bed_price) {
                                bedTypeText += ` ($${bed.extra_bed_price})`;
                            }
                        }
                        
                        // Add baby cot info if available
                        if (bed.baby_cot) {
                            bedTypeText += ` + Baby Cot`;
                            if (bed.baby_cot_price) {
                                bedTypeText += ` ($${bed.baby_cot_price})`;
                            }
                        }
                        
                        const option = document.createElement('option');
                        option.value = bed.bed_id;
                        option.textContent = bedTypeText;
                        option.setAttribute('data-bed', JSON.stringify(bed));
                        option.setAttribute('data-bed-id', bed.bed_id);
                        option.setAttribute('data-room-id', bed.room_id);
                        option.setAttribute('data-bed-max-occupancy', bed.max_occupancy);
                        option.setAttribute('data-bed-type', bed.room_type || bed.bed_type);
                        bedTypeSelect.appendChild(option);
                    });
                    
                    bedTypeSelect.disabled = false;
                    console.log(`Loaded ${data.beds.length} bed types for room type ${roomType}`);
                    
                    // Validate fields after beds are loaded
                    validateHotelModalFields();
                    
                    // Initialize meal plans if bed type is already selected
                    initializeMealPlansForExistingData();
                } else {
                    console.log('No beds found for room type:', roomType);
                    bedTypeSelect.innerHTML = '<option value="">No bed types available</option>';
                }
                
                // Load meal plans based on room data
                loadMealPlansForBed(selectedRooms[0]);
            })
            .catch(error => {
                console.error('Error fetching beds:', error);
                bedTypeSelect.innerHTML = '<option value="">Error loading bed types</option>';
                mealPlanSelect.innerHTML = '<option value="">Error loading bed types</option>';
            });
    }
    
    function updateBedPricingAndMealPlans() {
        const bedTypeSelect = document.getElementById('bed_type');
        const mealPlanSelect = document.getElementById('meal_plan');
        const selectedOption = bedTypeSelect.options[bedTypeSelect.selectedIndex];
        
        if (!selectedOption || !selectedOption.value) {
            mealPlanSelect.disabled = true;
            mealPlanSelect.innerHTML = '<option value="">Select bed type first</option>';
            return;
        }
        
        // Update bed occupancy info
        const bedData = JSON.parse(selectedOption.getAttribute('data-bed') || '{}');
        const maxOccupancy = bedData.max_occupancy || 2;
        
        const occupancyInfo = document.getElementById('bed_occupancy_info');
        if (occupancyInfo) {
            occupancyInfo.textContent = `Max Occupancy: ${maxOccupancy}`;
        }
        
        // Update price when bed type changes
        updateHotelModalPrice();
        
        // Update person selector dynamically based on max occupancy and extra bed availability
        generatePersonSelector(maxOccupancy, bedData.extra_bed || false);
        // Store selected bed info globally
        window.selectedBedInfo = {
            bedId: bedData.bed_id,
            roomId: bedData.room_id,
            maxOccupancy: maxOccupancy,
            extraBedPrice: bedData.extra_bed_price || 0,
            babyCotPrice: bedData.baby_cot_price || 0,
            extraBedAvailable: bedData.extra_bed || false
        };
        // Store extra bed availability globally for validation
        window.extraBedAvailable = bedData.extra_bed || false;
        
        console.log('Bed selected:', bedData);
        
        // Validate fields after bed selection
        validateHotelModalFields();
    }
    
    function loadMealPlansForBed(bedData) {
        const mealPlanSelect = document.getElementById('meal_plan');
        
        // Get room data to check meal availability
        const roomData = window.roomData || [];
        const roomId = bedData.room_id;
        const room = roomData.find(r => r.room_id === roomId);
        
        if (!room) {
            mealPlanSelect.innerHTML = '<option value="">No meal plans available</option>';
            return;
        }
        
        // Check meal availability for this specific room
        const hasBreakfast = room.breakfast == 1 || room.breakfast === true;
        const hasLunch = room.lunch == 1 || room.lunch === true;
        const hasDinner = room.dinner == 1 || room.dinner === true;
        
        // Generate meal plan options in the format "1 x room with/only"
        const mealPlans = [];
        const roomText = "room";
        
        // Add "Room Only" option first
        mealPlans.push(`${roomText} only`);
        
        // Add specific meal options based on availability
        if (hasBreakfast) {
            mealPlans.push(`${roomText} with breakfast`);
        }
        if (hasLunch) {
            mealPlans.push(`${roomText} with lunch`);
        }
        if (hasDinner) {
            mealPlans.push(`${roomText} with dinner`);
        }
        
        // Add combination meal options
        if (hasBreakfast && hasLunch) {
            mealPlans.push(`${roomText} with breakfast + lunch`);
        }
        if (hasBreakfast && hasDinner) {
            mealPlans.push(`${roomText} with breakfast + dinner`);
        }
        if (hasLunch && hasDinner) {
            mealPlans.push(`${roomText} with lunch + dinner`);
        }
        if (hasBreakfast && hasLunch && hasDinner) {
            mealPlans.push(`${roomText} with all meals (breakfast + lunch + dinner)`);
        }
        
        // Populate meal plans
        mealPlanSelect.innerHTML = '<option value="">Select meal plan</option>';
        mealPlans.forEach(plan => {
            const option = document.createElement('option');
            option.value = plan.toLowerCase().replace(/\s+/g, '_');
            option.textContent = plan;
            // Store meal prices in dataset
            option.dataset.breakfastPrice = room.breakfast_price || 0;
            option.dataset.lunchPrice = room.lunch_price || 0;
            option.dataset.dinnerPrice = room.dinner_price || 0;
            mealPlanSelect.appendChild(option);
        });
        
        mealPlanSelect.disabled = false;
        console.log('Meal plans loaded for selected bed');
        
        // Check if all required fields are selected to enable proceed button
        validateHotelModalFields();
    }
    
    function validateHotelModalFields() {
        const citySelect = document.getElementById('modal_city_select');
        const hotelSelect = document.getElementById('hotel_select');
        const roomTypeSelect = document.getElementById('room_type');
        const bedTypeSelect = document.getElementById('bed_type');
        const mealPlanSelect = document.getElementById('meal_plan');
        const proceedBtn = document.getElementById('proceed_hotel_btn');
        
        // Check if all required fields are selected
        const isValid = citySelect && citySelect.value &&
                        hotelSelect && hotelSelect.value &&
                        roomTypeSelect && roomTypeSelect.value &&
                        bedTypeSelect && bedTypeSelect.value &&
                        mealPlanSelect && mealPlanSelect.value;
        
        if (proceedBtn) {
            proceedBtn.disabled = !isValid;
            if (isValid) {
                proceedBtn.classList.remove('btn-secondary');
                proceedBtn.classList.add('btn-success');
            } else {
                proceedBtn.classList.remove('btn-success');
                proceedBtn.classList.add('btn-secondary');
            }
        }
        
        console.log('Hotel modal validation:', isValid);
    }
    
    function generatePersonSelector(maxOccupancy, extraBedAvailable = false) {
        const personSelect = document.getElementById('person_count_select');
        const maxOccupancyText = document.querySelector('#person_count_select').nextElementSibling;
        
        if (!personSelect) return;
        
        // Store current selected value
        const currentValue = personSelect.value;
        
        // Get tour pax (total guests from the form)
        const adults = parseInt(document.getElementById('adults').value) || 0;
        const children = parseInt(document.getElementById('children').value) || 0;
        const tourPax = adults + children;
        
        // Clear existing options
        personSelect.innerHTML = '';
        
        // Apply the person selection rules:
        // Selection range = min(tour pax, max occupancy [+ extra bed if available])
        const maxWithExtraBed = extraBedAvailable ? maxOccupancy + 1 : maxOccupancy;
        const maxSelectable = Math.min(tourPax, maxWithExtraBed);
        
        console.log(`Person selection rules applied: tour pax=${tourPax}, max occupancy=${maxOccupancy}, extra bed=${extraBedAvailable}, max selectable=${maxSelectable}`);
        
        // Generate dropdown options based on calculated max selectable
        for (let i = 1; i <= maxSelectable; i++) {
            const option = document.createElement('option');
            option.value = i;
            if (i > maxOccupancy && extraBedAvailable) {
                option.textContent = `${i} Persons (Extra Bed)`;
            } else {
                option.textContent = i === 1 ? '1 Person' : `${i} Persons`;
            }
            personSelect.appendChild(option);
        }
        
        // Restore previous selection or set to appropriate default
        const defaultSelection = tourPax > 0 ? Math.min(tourPax, maxWithExtraBed) : 1;
        if (currentValue && parseInt(currentValue) <= maxSelectable) {
            personSelect.value = currentValue;
        } else {
            personSelect.value = defaultSelection.toString();
        }
        
        // Update max occupancy text to show the rules applied
        if (maxOccupancyText) {
            if (maxSelectable < maxWithExtraBed) {
                maxOccupancyText.textContent = `Max Selectable: ${maxSelectable} (Limited by tour pax: ${tourPax})`;
            } else {
                maxOccupancyText.textContent = `Max Occupancy: ${maxOccupancy}${extraBedAvailable ? ' (+1 extra bed)' : ''}`;
            }
        }
        
        // Store max occupancy and selected count
        window.maxOccupancy = maxOccupancy;
        window.selectedPersonCount = parseInt(personSelect.value);
    }
    
    function selectPersonCount(count) {
        const numPersons = parseInt(count);
        
        // Get tour pax (total guests from the form)
        const adults = parseInt(document.getElementById('adults').value) || 0;
        const children = parseInt(document.getElementById('children').value) || 0;
        const tourPax = adults + children;
        
        // Get bed information for validation
        const maxOccupancy = window.maxOccupancy || 0;
        const extraBedAvailable = window.extraBedAvailable || false;
        
        // Apply the person selection rules: min(tour pax, max occupancy [+ extra bed if available])
        const maxWithExtraBed = extraBedAvailable ? maxOccupancy + 1 : maxOccupancy;
        const maxAllowed = Math.min(tourPax, maxWithExtraBed);
        
        // Validate against the rules
        if (numPersons > maxAllowed) {
            if (numPersons > tourPax) {
                showNotification(`Cannot select more than ${tourPax} persons (tour pax limit).`, 'error');
                // Reset to previous valid value
                const personSelect = document.getElementById('person_count_select');
                if (personSelect && window.selectedPersonCount) {
                    personSelect.value = window.selectedPersonCount;
                }
                return;
            } else if (numPersons > maxWithExtraBed) {
                showNotification(`Maximum occupancy is ${maxOccupancy}${extraBedAvailable ? ' (+1 extra bed)' : ''} persons.`, 'error');
                // Reset to previous valid value
                const personSelect = document.getElementById('person_count_select');
                if (personSelect && window.selectedPersonCount) {
                    personSelect.value = window.selectedPersonCount;
                }
                return;
            }
        }
        
        console.log(`Person selection validated: ${numPersons} persons (tour pax: ${tourPax}, max occupancy: ${maxOccupancy}, extra bed: ${extraBedAvailable}, max allowed: ${maxAllowed})`);
        
        // Store the selected count in a global variable
        window.selectedPersonCount = numPersons;
        
        // Update pricing if needed
        updateMealPricing();
    }
    
    
    function initializeMealPlansForExistingData() {
        const bedTypeSelect = document.getElementById('bed_type');
        const mealPlanSelect = document.getElementById('meal_plan');
        
        // Check if bed type is already selected and meal plan needs initialization
        if (bedTypeSelect && bedTypeSelect.value && mealPlanSelect) {
            console.log('Initializing meal plans for existing bed selection');
            
            // Get the selected bed data
            const selectedOption = bedTypeSelect.options[bedTypeSelect.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const bedData = JSON.parse(selectedOption.getAttribute('data-bed') || '{}');
                console.log('Loading meal plans for bed data:', bedData);
                
                // Initialize person count to 1 by default
                if (!window.selectedPersonCount) {
                    window.selectedPersonCount = 1;
                    
                    // Update dropdown to reflect selected person count
                    const personSelect = document.getElementById('person_count_select');
                    if (personSelect) {
                        personSelect.value = window.selectedPersonCount;
                    }
                }
                
                // Load meal plans for the selected bed
                loadMealPlansForBed(bedData);
            }
        }
    }
    
    function updateMealPricing() {
        // Get selected meal plan
        const mealPlanSelect = document.getElementById('meal_plan');
        const selectedOption = mealPlanSelect.options[mealPlanSelect.selectedIndex];
        
        if (!selectedOption || !selectedOption.value) {
            return;
        }
        
        // Get meal prices from the selected option's dataset
        const breakfastPrice = parseFloat(selectedOption.dataset.breakfastPrice) || 0;
        const lunchPrice = parseFloat(selectedOption.dataset.lunchPrice) || 0;
        const dinnerPrice = parseFloat(selectedOption.dataset.dinnerPrice) || 0;
        
        // Get selected person count
        const personCount = window.selectedPersonCount || 1;
        
        // Calculate total meal price based on selected meal plan and person count
        let totalMealPrice = 0;
        const mealPlan = selectedOption.value.toLowerCase();
        
        if (mealPlan.includes('breakfast')) {
            totalMealPrice += breakfastPrice * personCount;
        }
        if (mealPlan.includes('lunch')) {
            totalMealPrice += lunchPrice * personCount;
        }
        if (mealPlan.includes('dinner')) {
            totalMealPrice += dinnerPrice * personCount;
        }
        
        // Update the UI with the calculated price if needed
        console.log(`Meal pricing for ${personCount} persons: $${totalMealPrice.toFixed(2)}`);
        
        // Validate fields after meal plan selection
        validateHotelModalFields();
    }
    
    function resetHotelModalFields() {
        const roomTypeSelect = document.getElementById('room_type');
        const bedTypeSelect = document.getElementById('bed_type');
        const mealPlanSelect = document.getElementById('meal_plan');
        const proceedBtn = document.getElementById('proceed_hotel_btn');
        
        if (roomTypeSelect) {
            roomTypeSelect.disabled = true;
            roomTypeSelect.innerHTML = '<option value="">Select hotel first</option>';
        }
        if (bedTypeSelect) {
            bedTypeSelect.disabled = true;
            bedTypeSelect.innerHTML = '<option value="">Select room type first</option>';
        }
        if (mealPlanSelect) {
            mealPlanSelect.disabled = true;
            mealPlanSelect.innerHTML = '<option value="">Select bed type first</option>';
        }
        if (proceedBtn) {
            proceedBtn.disabled = true;
            proceedBtn.classList.remove('btn-success');
            proceedBtn.classList.add('btn-secondary');
        }
        
        // Reset person selector to default (max occupancy 2, no extra bed)
        generatePersonSelector(2, false);
        
        // Clear stored data
        window.roomData = null;
        window.selectedBedInfo = null;
    }
    
    function onHotelSelection() {
        const hotelSelect = document.getElementById('hotel_select');
        const roomType = document.getElementById('room_type');
        const bedType = document.getElementById('bed_type');
        const mealPlan = document.getElementById('meal_plan');
        
        if (!hotelSelect || !roomType || !bedType || !mealPlan) {
            console.warn('Hotel selection elements not found');
            return;
        }
        
        const hotelId = hotelSelect.value;
        
        if (hotelId) {
            // Get the selected hotel data
            const selectedOption = hotelSelect.querySelector(`option[value="${hotelId}"]`);
            if (selectedOption) {
                const hotelData = JSON.parse(selectedOption.getAttribute('data-hotel'));
                
                // Load room options for selected hotel
                loadRoomOptions(hotelData, roomType, bedType, mealPlan);
            }
        } else {
            // Reset room options
            resetRoomOptions();
        }
    }
    
    function loadRoomOptions(hotelData, roomType, bedType, mealPlan) {
        // Debug logging
        console.log('Loading room options for hotel:', hotelData);
        console.log('Hotel rooms:', hotelData.rooms);
        
        // Clear existing options
        roomType.innerHTML = '<option value="">Select Room Type</option>';
        bedType.innerHTML = '<option value="">Select Bed Type</option>';
        mealPlan.innerHTML = '<option value="">Select Meal Plan</option>';
        let bedTypes = [];
        
        // Load room types from hotel data
        if (hotelData.rooms && hotelData.rooms.length > 0) {
            // Get unique room types from rooms
            const roomTypes = [...new Set(hotelData.rooms.map(room => room.room_type || room.type || 'Standard Room'))];
            roomTypes.forEach((type, index) => {
                const option = document.createElement('option');
                option.value = type.toLowerCase().replace(/\s+/g, '_');
                option.textContent = type;
                option.setAttribute('data-room-type', type);
                option.setAttribute('data-room-id', hotelData.rooms[index].room_id);
                roomType.appendChild(option);
                if (type.bed && type.bed.room_type) {
                    console.log('Added room tydgf edgf esd gfwef wefwe pe option = :', type.bed.room_type);
                }
            });
            if(hotelData.rooms && hotelData.rooms.length > 0){
                hotelData.rooms.forEach(room => {
                    // Add bed type if available
                    if (room.bed && room.bed.room_type) {
                        console.log('Addeddfgds fdefwe we bed type option = :', room.bed.room_type);
                        
                        const option = document.createElement('option');
                        option.value = room.bed.room_type.toLowerCase().replace(/\s+/g, '_');
                        option.textContent = room.bed.room_type;
                        option.setAttribute('data-bed-type', room.bed.room_type);
                        option.setAttribute('data-bed-id', room.bed.bed_id);
                        option.setAttribute('data-bed-max-occupancy', room.bed.max_occupancy);
                        bedType.appendChild(option);
                        console.log('Added bed type option = :', room.bed.room_type);
                    }
                });
            }
            else{
                console.log('No bed types available');
            }
            
            // Add event listener for room type selection to update bed types
            // roomType.addEventListener('change', function() {
            //     updateBedTypesForRoom(this.value, hotelData, bedType);
            // });
            
            // Initially populate bed types with a default message
            // bedType.innerHTML = '<option value="">Select room type first</option>';
        
            // // Load bed types from the bed field in rooms
            // if (hotelData.rooms && hotelData.rooms.length > 0) {
            //       // Collect all beds from all rooms using the bed field (singular)
            //       const allBeds = [];
            //       hotelData.rooms.forEach(room => {
            //           if (room.bed && room.bed.id) {
            //               // Add room context to bed data
            //               const bedData = {
            //                   ...room.bed,
            //                   room_type: room.room_type || room.type,
            //                   hotel_room_id: room.room_id || room.id
            //               };
            //               allBeds.push(bedData);
            //           }
            //       });
                  
            //       console.log('Collected beds from bed field:', allBeds);
                  
            //       if (allBeds.length > 0) {
            //           // Get unique bed types with detailed information from bed data
            //           const bedTypes = [...new Set(allBeds.map(bed => bed.room_type || bed.bed_type || 'Standard Bed'))];
            //           console.log('Unique bed types found:', bedTypes);
                      
            //           bedTypes.forEach(type => {
            //               const option = document.createElement('option');
            //               option.value = type.toLowerCase().replace(/\s+/g, '_');
                          
            //               // Create descriptive bed type text
            //               let bedTypeText = type;
                          
            //               // Find beds of this type to get additional info
            //               const bedsOfType = allBeds.filter(bed => 
            //                   (bed.room_type || bed.bed_type) === type
            //               );
                          
            //               if (bedsOfType.length > 0) {
            //                   const firstBed = bedsOfType[0];
                              
            //                   // Add room count if available
            //                   if (bedsOfType.length > 1) {
            //                       bedTypeText += ` (${bedsOfType.length} available)`;
            //                   }
                              
            //                   // Add occupancy info if available
            //                   if (firstBed.max_occupancy) {
            //                       bedTypeText += ` - Max ${firstBed.max_occupancy} guests`;
            //                   }
                              
            //                   // Add adult/child info if available
            //                   if (firstBed.adult_count && firstBed.child_count) {
            //                       bedTypeText += ` (${firstBed.adult_count}A+${firstBed.child_count}C)`;
            //                   }
                              
            //                   // Add extra bed info if available
            //                   if (firstBed.extra_bed) {
            //                       bedTypeText += ` + Extra Bed`;
            //                       if (firstBed.extra_bed_price) {
            //                           bedTypeText += ` ($${firstBed.extra_bed_price})`;
            //                       }
            //                   }
                              
            //                   // Add baby cot info if available
            //                   if (firstBed.baby_cot) {
            //                       bedTypeText += ` + Baby Cot`;
            //                       if (firstBed.baby_cot_price) {
            //                           bedTypeText += ` ($${firstBed.baby_cot_price})`;
            //                       }
            //                   }
            //               }
                          
            //               option.textContent = bedTypeText;
            //               option.setAttribute('data-bed-type', type);
            //               bedType.appendChild(option);
            //               console.log('Added bed type option:', bedTypeText);
            //           });
            //       } else {
            //           // No beds found in bed field - show no data message
            //           console.log('No beds found in bed field');
            //           bedType.innerHTML = '<option value="">No bed types available</option>';
            //       }
            // }
            
            // Load meal plans based on room meal availability
            if (hotelData.rooms && hotelData.rooms.length > 0) {
                // Check what meals are available across all rooms
                const hasBreakfast = hotelData.rooms.some(room => 
                    room.breakfast_included || room.breakfast || room.breakfast_available
                );
                const hasLunch = hotelData.rooms.some(room => 
                    room.lunch_included || room.lunch || room.lunch_available
                );
                const hasDinner = hotelData.rooms.some(room => 
                    room.dinner_included || room.dinner || room.dinner_available
                );
                
                // Generate meal plan options
                const mealPlans = new Set();
                
                // Add "Room Only" option first
                mealPlans.add('Room Only');
                
                // Add specific meal options
                if (hasBreakfast) {
                    mealPlans.add('Bed & Breakfast');
                    mealPlans.add('Breakfast Only');
                }
                if (hasLunch) {
                    mealPlans.add('Lunch Only');
                }
                if (hasDinner) {
                    mealPlans.add('Dinner Only');
                }
                
                // Add combination meal options
                if (hasBreakfast && hasLunch) {
                    mealPlans.add('Half Board (Breakfast + Lunch)');
                }
                if (hasBreakfast && hasDinner) {
                    mealPlans.add('Half Board (Breakfast + Dinner)');
                }
                if (hasLunch && hasDinner) {
                    mealPlans.add('Half Board (Lunch + Dinner)');
                }
                if (hasBreakfast && hasLunch && hasDinner) {
                    mealPlans.add('Full Board (All Meals)');
                    mealPlans.add('All Inclusive');
                }
                
                // Populate meal plans
                [...mealPlans].forEach(plan => {
                    const option = document.createElement('option');
                    option.value = plan.toLowerCase().replace(/\s+/g, '_');
                    option.textContent = plan;
                    mealPlan.appendChild(option);
                });
            } else {
                // No meal data available
                mealPlan.innerHTML = '<option value="">No meal plans available</option>';
            }
        } else {
            // No rooms data available - show no data messages
            console.log('No rooms data available');
            roomType.innerHTML = '<option value="">No room types available</option>';
            bedType.innerHTML = '<option value="">No bed types available</option>';
            mealPlan.innerHTML = '<option value="">No meal plans available</option>';
        }
    }
    
    function updateBedTypesForRoom(selectedRoomType, hotelData, bedTypeSelect) {
         if (!selectedRoomType || !hotelData || !hotelData.rooms) {
             bedTypeSelect.innerHTML = '<option value="">Select room type first</option>';
             return;
         }
         
         // Find rooms of the selected type
         const selectedRooms = hotelData.rooms.filter(room => 
             (room.room_type || room.type) === selectedRoomType
         );
         
         if (selectedRooms.length === 0) {
             bedTypeSelect.innerHTML = '<option value="">No rooms of this type</option>';
             return;
         }
         
         // Get the first room ID to fetch beds (since all rooms of same type should have similar bed options)
         const firstRoom = selectedRooms[0];
         const roomId = firstRoom.room_id || firstRoom.id;
         
         console.log('Fetching beds for room ID:', roomId);
         
         // Clear dropdown and show loading
         bedTypeSelect.innerHTML = '<option value="">Loading bed types...</option>';
         
         // Fetch beds from the beds table using the existing API endpoint
         const url = route('fetch-beds-by-room', roomId);
         fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ room_id: roomId })
         })
             .then(response => {
                 if (!response.ok) {
                     throw new Error('Network response was not ok');
                 }
                 return response.json();
             })
             .then(data => {
                 console.log('Beds API Response:', data);
                 
                 bedTypeSelect.innerHTML = '<option value="">Select bed type</option>';
                 
                 if (data.success && data.beds && data.beds.length > 0) {
                     // Populate bed types from the beds table
                     data.beds.forEach(bed => {
                         // Create descriptive bed type text based on beds table structure
                         let bedTypeText = bed.room_type || bed.bed_type || 'Standard Bed';
                         
                         // Add room count if available
                         if (bed.no_of_rooms) {
                             bedTypeText += ` (${bed.no_of_rooms} available)`;
                         }
                         
                         // Add occupancy info if available
                         if (bed.max_occupancy) {
                             bedTypeText += ` - Max ${bed.max_occupancy} guests`;
                         }
                         
                         // Add adult/child info if available
                         if (bed.adult_count && bed.child_count) {
                             bedTypeText += ` (${bed.adult_count}A+${bed.child_count}C)`;
                         }
                         
                         // Add extra bed info if available
                         if (bed.extra_bed) {
                             bedTypeText += ` + Extra Bed`;
                             if (bed.extra_bed_price) {
                                 bedTypeText += ` ($${bed.extra_bed_price})`;
                             }
                         }
                         
                         // Add baby cot info if available
                         if (bed.baby_cot) {
                             bedTypeText += ` + Baby Cot`;
                             if (bed.baby_cot_price) {
                                 bedTypeText += ` ($${bed.baby_cot_price})`;
                             }
                         }
                         
                         // Store bed data in option for later use
                         const option = document.createElement('option');
                         option.value = bed.bed_id || bed.id;
                         option.textContent = bedTypeText;
                         option.setAttribute('data-bed', JSON.stringify(bed));
                         bedTypeSelect.appendChild(option);
                     });
                 } else {
                     // No beds found in API - show no data message
                     console.log('No beds found in API response');
                     bedTypeSelect.innerHTML = '<option value="">No bed types available</option>';
                 }
             })
             .catch(error => {
                 console.error('Error fetching beds:', error);
                 // Show no data message on error
                 bedTypeSelect.innerHTML = '<option value="">No bed types available</option>';
             });
     }
     
     function resetRoomOptions() {
        const roomType = document.getElementById('room_type');
        const bedType = document.getElementById('bed_type');
        const mealPlan = document.getElementById('meal_plan');
        
        roomType.innerHTML = '<option value="">Select hotel first</option>';
        bedType.innerHTML = '<option value="">Select hotel first</option>';
        mealPlan.innerHTML = '<option value="">Select hotel first</option>';
    }
    
    function updateNightsDisplay() {
        const checkInEl = document.getElementById('check_in_date');
        const checkOutEl = document.getElementById('check_out_date');
        const nightsDisplay = document.getElementById('selected_nights_display');
        const nightsList = document.getElementById('nights_list');
        const noNightsAlert = document.getElementById('no_nights_alert');
        const proceedBtn = document.getElementById('proceed_hotel_btn');
        
        // Check if elements exist
        if (!checkInEl || !checkOutEl || !nightsDisplay || !nightsList || !noNightsAlert || !proceedBtn) {
            console.warn('Some hotel modal elements not found');
            return;
        }
        
        const checkIn = checkInEl.value;
        const checkOut = checkOutEl.value;
        
        if (checkIn && checkOut) {
            const startDate = new Date(checkIn);
            const endDate = new Date(checkOut);
            const nights = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
            
            if (nights > 0) {
                nightsDisplay.classList.remove('d-none');
                noNightsAlert.classList.add('d-none');
                proceedBtn.disabled = false;
                
                // Generate night labels
                nightsList.innerHTML = '';
                for (let i = 0; i < nights; i++) {
                    const nightDate = new Date(startDate);
                    nightDate.setDate(startDate.getDate() + i);
                    const nightLabel = document.createElement('span');
                    nightLabel.className = 'badge bg-primary border-0';
                    nightLabel.textContent = `Night ${i + 1} (${nightDate.toLocaleDateString()})`;
                    nightsList.appendChild(nightLabel);
                }
            } else {
                nightsDisplay.classList.add('d-none');
                noNightsAlert.classList.remove('d-none');
                proceedBtn.disabled = true;
            }
        } else {
            nightsDisplay.classList.add('d-none');
            noNightsAlert.classList.remove('d-none');
            proceedBtn.disabled = true;
        }
        
        // Update price when dates change
        updateHotelModalPrice();
    }
    
    // Function to update hotel modal price based on room type, number of rooms, and dates
    function updateHotelModalPrice() {
        const roomTypeSelect = document.getElementById('room_type');
        const numberOfRoomsInput = document.getElementById('number_of_rooms_modal');
        const priceInput = document.getElementById('total_price_modal');
        const numberOfPersonsSelect = document.getElementById('person_count_select');
        const checkInInput = document.getElementById('check_in_date');
        const checkOutInput = document.getElementById('check_out_date');
        
        if (!roomTypeSelect || !numberOfRoomsInput || !priceInput) {
            return;
        }
        
        const selectedRoomType = roomTypeSelect.value;
        const numberOfRooms = parseInt(numberOfRoomsInput.value) || 1;
        const numberOfPersons = numberOfPersonsSelect ? parseInt(numberOfPersonsSelect.value) || 1 : 1;
        
        // Get room data
        const roomData = window.roomData;
        if (!roomData || !selectedRoomType) {
            return;
        }
        
        // Find the selected room type
        const selectedRoom = roomData.find(room => room.room_type === selectedRoomType);
        if (!selectedRoom) {
            return;
        }
        
        // Calculate price based on occupancy (single or double)
        const isSingleOccupancy = numberOfPersons <= 1;
        let pricePerNight = 0;
        
        if (isSingleOccupancy) {
            pricePerNight = parseFloat(selectedRoom.weekday_price || 0);
        } else {
            pricePerNight = parseFloat(selectedRoom.double_weekday_price || selectedRoom.weekday_price || 0);
        }
        
        // Calculate number of nights
        let numberOfNights = 1;
        if (checkInInput && checkOutInput && checkInInput.value && checkOutInput.value) {
            const checkIn = new Date(checkInInput.value);
            const checkOut = new Date(checkOutInput.value);
            numberOfNights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
            if (numberOfNights <= 0) numberOfNights = 1;
        }
        
        // Calculate total price: price per night * number of nights * number of rooms
        const totalPrice = pricePerNight * numberOfNights * numberOfRooms;
        
        // Update price input if calculated price is valid
        if (totalPrice > 0) {
            priceInput.value = totalPrice.toFixed(2);
        }
    }
    
    function proceedToHotelSelection() {
        const formData = new FormData(document.getElementById('hotelBookingForm'));
        const tourId = formData.get('tour_id');
        const hotelId = formData.get('hotel_id');
        const checkIn = formData.get('check_in_date');
        const checkOut = formData.get('check_out_date');

        
        
        if (!hotelId || !checkIn || !checkOut) {
            showNotification('Please fill in all required fields', 'error');
            return;
        }
        
        // Close modal safely
        safeCloseModal('hotelBookingModal');
        
        // Redirect to hotel selection with form data
        goToHotel(tourId, hotelId, checkIn, checkOut, formData);
    }
    let baseUrl = "{{ route('orders.hotels.select') }}";
    function goToHotel(tourId, hotelId, checkIn, checkOut, formData) {
        // Get customer info
        const customer_info = getCustomerInfo();
        console.log('Customer info:', customer_info);
        
        // Get selected hotel data
        const hotelSelect = document.getElementById('hotel_select');
        const selectedHotelOption = hotelSelect.options[hotelSelect.selectedIndex];
        const hotelData = selectedHotelOption ? JSON.parse(selectedHotelOption.getAttribute('data-hotel') || '{}') : {};
        
        // Get country from tour form (fallback if hotel location is not available)
        const country = document.getElementById('user_country') ? document.getElementById('user_country').value : '';
        
        // Get selected room and bed data
        const roomType = document.getElementById('room_type');
        const bedType = document.getElementById('bed_type');
        const mealPlan = document.getElementById('meal_plan');
        
        const selectedRoomOption = roomType.options[roomType.selectedIndex];
        const selectedBedOption = bedType.options[bedType.selectedIndex];
        
        const roomId = selectedRoomOption ? selectedRoomOption.getAttribute('data-room-id') : null;
        const bedId = selectedBedOption ? selectedBedOption.getAttribute('data-bed-id') : null;
        const maxOccupancy = selectedBedOption ? selectedBedOption.getAttribute('data-bed-max-occupancy') : 1;
        const selectedRoomType = selectedRoomOption ? selectedRoomOption.getAttribute('data-room-type') : 'Standard';
        const selectedBedType = selectedBedOption ? selectedBedOption.getAttribute('data-bed-type') : 'King Bed';
        const selectedMealPlan = mealPlan.value || 'room_only';
        
        // Get bed data from the selected bed option
        let bedData = {};
        if (selectedBedOption && selectedBedOption.getAttribute('data-bed')) {
            try {
                bedData = JSON.parse(selectedBedOption.getAttribute('data-bed'));
            } catch (e) {
                console.log('Error parsing bed data:', e);
            }
        }
        
        // Get room data for pricing
        const roomData = window.roomData ? window.roomData.find(r => r.room_id == roomId) : null;
        
        // Calculate number of nights
        const checkInDate = new Date(checkIn);
        const checkOutDate = new Date(checkOut);
        const numberOfNights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
        
        // Get the selected number of persons
        const personCountSelect = document.getElementById('person_count_select');
        const headCount = personCountSelect ? parseInt(personCountSelect.value) : 1;
        
        // Get number of rooms from modal
        const numberOfRoomsInput = document.getElementById('number_of_rooms_modal');
        const numberOfRooms = numberOfRoomsInput ? parseInt(numberOfRoomsInput.value) || 1 : 1;
        
        // Get total price from modal (or calculate it)
        const totalPriceInput = document.getElementById('total_price_modal');
        let totalPrice = 0;
        if (totalPriceInput && totalPriceInput.value) {
            totalPrice = parseFloat(totalPriceInput.value) || 0;
        }
        
        // If price not set in modal, calculate it
        if (totalPrice <= 0) {
            // Determine pricing based on occupancy
            const isSingleOccupancy = headCount <= 1;
            let pricePerNight = 0;
            
            if (roomData) {
                if (isSingleOccupancy) {
                    pricePerNight = parseFloat(roomData.weekday_price || bedData.price || 0);
                } else {
                    pricePerNight = parseFloat(roomData.double_weekday_price || bedData.price || 0);
                }
            } else {
                pricePerNight = parseFloat(bedData.price || 190);
            }
            
            // Calculate total price: price per night * number of nights * number of rooms
            totalPrice = pricePerNight * numberOfNights * numberOfRooms;
        }
        
        // Calculate price per night for room structure
        const pricePerNight = totalPrice / (numberOfNights * numberOfRooms);
        
        // Generate selectedMeals object for each night
        const selectedMeals = {};
        for (let i = 1; i <= numberOfNights; i++) {
            selectedMeals[`meal_${i}`] = {
                type: selectedMealPlan.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
                price: pricePerNight
            };
        }
        
        // Get check-in and check-out times from room data or hotel data
        const checkInTime = roomData?.check_in_time || hotelData.check_in_time || "15:00:00";
        const checkOutTime = roomData?.check_out_time || hotelData.check_out_time || "12:00:00";
        
        // Build the complex data structure
        const bookingData = {
            fullName: customer_info.fullName,
            email: customer_info.email,
            phone: customer_info.phone,
            countryCode: customer_info.countryCode,
            address1: customer_info.address1,
            address2: customer_info.address2,
            state: customer_info.state,
            zip: customer_info.zip,
            specialRequests: customer_info.specialRequests,
            rooms: (() => {
                // Create array of rooms based on number of rooms
                const rooms = [];
                for (let i = 0; i < numberOfRooms; i++) {
                    rooms.push({
                        room_id: parseInt(roomId) || 2,
                        room_type: selectedRoomType,
                        beds: [
                            {
                                bed_id: parseInt(bedId) || 1,
                                bed_type: selectedBedType,
                                max_occupancy: parseInt(maxOccupancy) || 1,
                                mealTypes: [selectedMealPlan.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())],
                                selectedMeals: selectedMeals,
                                head_count: headCount,
                                price: pricePerNight,
                                baby_cot: parseInt(bedData.baby_cot) || 0,
                                room_type: selectedRoomType
                            }
                        ]
                    });
                }
                return rooms;
            })(),
            bookingType: "booking",
            totalPrice: totalPrice,
            priceMode: "dmc",
            priceModeId: parseInt(hotelData.dmc_id) || 4,
            hotelDetails: {
                hotel_id: hotelId,
                hotel_name: hotelData.name || "Hotel",
                checkInTime: checkInTime,
                checkOutTime: checkOutTime,
                location: hotelData.location || hotelData.city || country || null,
                image: hotelData.master_image || hotelData.image || "",
                cancellation_charge: null
            },
            bookingDate: [checkIn, checkOut]
        };
        
        console.log('Booking data to be sent:', bookingData);
        
        // Create a form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = baseUrl;

        // Add CSRF token
        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = "{{ csrf_token() }}";
        form.appendChild(token);

        // Add the complex booking data as JSON
        const bookingDataInput = document.createElement('input');
        bookingDataInput.type = 'hidden';
        bookingDataInput.name = 'booking_data';
        bookingDataInput.value = JSON.stringify(bookingData);
        form.appendChild(bookingDataInput);

        // Add basic form fields for backward compatibility
        const basicData = {
            tour_id: tourId,
            hotel_id: hotelId,
            check_in: checkIn,
            check_out: checkOut,
            room_type: formData.get('room_type'),
            bed_type: formData.get('bed_type'),
            room_id: roomId || 99999,
            bed_id: bedId || 99999,
            meal_plan: formData.get('meal_plan'),
            number_of_rooms: numberOfRooms,
            total_price: totalPrice,
        };

        for (const [key, value] of Object.entries(basicData)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }

        // Add customer_info fields for backward compatibility
        for (const [key, value] of Object.entries(customer_info)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `customer_info[${key}]`;
            input.value = value;
            form.appendChild(input);
        }

        // Append and submit
        document.body.appendChild(form);
        form.submit();
    }
    
    // Guide Selection Modal Functions
    function showGuideSelectionModal(tourId, country, startDate, endDate) {
        // Populate modal with tour data
        document.getElementById('modal_guide_tour_dates').textContent = `${startDate} to ${endDate}`;
        document.getElementById('modal_guide_destination').textContent = `${country}`;
        // City display removed
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('guideSelectionModal'));
        modal.show();
        
        // Initialize modal functionality
        initializeGuideModal();
        
        // Load guides for the city
        // loadGuidesForCity(city, country);
    }
    
    function initializeGuideModal() {
        // Add event listeners
        const citySelect = document.getElementById('modal_guide_city_select');
        if (citySelect) {
            citySelect.addEventListener('change', function() {
                validateForm();
            });
        }
        document.getElementById('modal_guide_select').addEventListener('change', onGuideSelection);
        document.getElementById('modal_guide_duration').addEventListener('change', onDurationSelection);
        document.getElementById('modal_guide_custom_hours').addEventListener('input', validateCustomHours);
        document.getElementById('modal_guide_pickup_time').addEventListener('change', validateForm);
        document.getElementById('modal_guide_service_date').addEventListener('change', validateForm);
        document.getElementById('confirm_guide_btn').addEventListener('click', confirmGuideSelection);
        
        // Set default pickup time to 9:00 AM
        document.getElementById('modal_guide_pickup_time').value = '09:00';
        
        // Set date restrictions and default value
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const serviceDateInput = document.getElementById('modal_guide_service_date');
        
        if (startDate && endDate) {
            serviceDateInput.min = startDate;
            serviceDateInput.max = endDate;
            serviceDateInput.value = startDate; // Default to start date
        }
        
        // Initial validation
        validateForm();
    }
    
    function loadGuidesForCity(city, country) {
        const guideSelect = document.getElementById('modal_guide_select');
        const guideCount = document.getElementById('guide_count');
        
        // Clear existing options
        guideSelect.innerHTML = '<option value="">Search Guide</option>';
        
        // For demo purposes, show sample guides
        // In production, this would fetch from API
        const all_guides = @json($guides);
        const guides = all_guides.filter(guide => guide.city == city);
        console.log('Guides:', guides.languages);
        // Add guide options
        guides.forEach(guide => {
            const option = document.createElement('option');
            option.value = guide.guide_id;
            option.textContent = `${guide.name} - ${guide.languages.map(language => language.language).join(', ')} `;
            option.setAttribute('data-guide', JSON.stringify(guide));
            guideSelect.appendChild(option);
        });
        
        guideCount.textContent = guides.length;
    }
    
    function onGuideSelection() {
        const guideSelect = document.getElementById('modal_guide_select');
        const guideDetailsContainer = document.getElementById('guide_details_container');
        const selectedOption = guideSelect.options[guideSelect.selectedIndex];
        
        if (guideSelect.value) {
            const guideData = JSON.parse(selectedOption.getAttribute('data-guide'));
            
            // Show guide details
            document.getElementById('selected_guide_image').src = guideData.image || '/assets/images/default-avatar.png';
            document.getElementById('selected_guide_name').textContent = guideData.name;
            document.getElementById('selected_guide_specialty').textContent = guideData.specialty;
            document.getElementById('selected_guide_experience').textContent = `${guideData.experience} experience`;
            document.getElementById('selected_guide_rating').textContent = guideData.rating;
            document.getElementById('selected_guide_rate').textContent = guideData.rate;
            
            guideDetailsContainer.style.display = 'block';
        } else {
            guideDetailsContainer.style.display = 'none';
        }
        
        validateForm();
    }
    
    function onDurationSelection() {
        const durationSelect = document.getElementById('modal_guide_duration');
        const customHoursContainer = document.getElementById('custom_hours_container');
        const customHoursInput = document.getElementById('modal_guide_custom_hours');
        
        if (durationSelect.value === 'custom') {
            customHoursContainer.style.display = 'block';
            customHoursInput.required = true;
        } else {
            customHoursContainer.style.display = 'none';
            customHoursInput.required = false;
            customHoursInput.value = '';
        }
        
        validateForm();
    }
    
    function validateCustomHours() {
        const customHours = document.getElementById('modal_guide_custom_hours').value;
        const input = document.getElementById('modal_guide_custom_hours');
        
        if (customHours < 1 || customHours > 24) {
            input.setCustomValidity('Hours must be between 1 and 24');
        } else {
            input.setCustomValidity('');
        }
        
        validateForm();
    }
    
    function validateForm() {
        const citySelect = document.getElementById('modal_guide_city_select');
        const guideSelect = document.getElementById('modal_guide_select');
        const durationSelect = document.getElementById('modal_guide_duration');
        const customHours = document.getElementById('modal_guide_custom_hours');
        const pickupTime = document.getElementById('modal_guide_pickup_time');
        const serviceDate = document.getElementById('modal_guide_service_date');
        const confirmBtn = document.getElementById('confirm_guide_btn');
        
        let isValid = true;
        
        // Check required fields
        if (!citySelect || !citySelect.value) isValid = false;
        if (!guideSelect.value) isValid = false;
        if (!durationSelect.value) isValid = false;
        if (durationSelect.value === 'custom' && (!customHours.value || customHours.value < 1 || customHours.value > 24)) isValid = false;
        if (!pickupTime.value) isValid = false;
        if (!serviceDate.value) isValid = false;
        
        confirmBtn.disabled = !isValid;
    }

    const guideBaseUrl = "{{ route('orders.guides.select') }}";
    function confirmGuideSelection() {
        
        const formData = new FormData(document.getElementById('guideSelectionForm'));
        const guideId = formData.get('guide_id');
        const duration = formData.get('duration');
        const customHours = formData.get('custom_hours');
        const pickupTime = formData.get('pickup_time');
        const serviceDate = formData.get('service_date');
        const customer_info = getCustomerInfo();
        console.log('Customer info:', customer_info);
        const agentId = document.getElementById('agent_id').value;
        console.log('Agent id:', agentId);
        
        // Get selected guide details
        const guideSelect = document.getElementById('modal_guide_select');
        const selectedOption = guideSelect.options[guideSelect.selectedIndex];
        const guideData = selectedOption ? JSON.parse(selectedOption.getAttribute('data-guide')) : {};
        console.log('Guide data:', guideData);
        // Get tour details
        const tourId = document.getElementById('tour_id').value;
        const country = document.getElementById('user_country').value;
        const city = '{{ $tour->city ?? "" }}';
        
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const adults = document.getElementById('adults').value || '1';
        const children = document.getElementById('children').value || '0';
        
        // Calculate hours
        const hours = duration === 'custom' ? customHours : 
                     duration === 'half_day' ? '4' : 
                     duration === 'full_day' ? '8' : '4';
        
        // Calculate pricing based on guide data
        let basePrice = 0;
        const hoursNum = parseInt(hours);
        
        // Calculate base price based on hours
        if (hoursNum <= 2) {
            basePrice = parseFloat(guideData.two_hour_price || '30.00');
        } else if (hoursNum <= 4) {
            basePrice = parseFloat(guideData.four_hour_price || '60.00');
        } else if (hoursNum <= 6) {
            basePrice = parseFloat(guideData.six_hour_price || '180.00');
        } else if (hoursNum <= 8) {
            basePrice = parseFloat(guideData.eight_hour_price || '240.00');
        } else if (hoursNum <= 10) {
            basePrice = parseFloat(guideData.ten_hour_price || '300.00');
        } else if (hoursNum <= 12) {
            basePrice = parseFloat(guideData.twelve_hour_price || '360.00');
        } else {
            // For custom hours beyond 12, calculate using hourly rate
            basePrice = parseFloat(guideData.hourly_price || '15.00') * hoursNum;
        }
        
        // Calculate night surcharge if pickup time is within night hours
        let surcharge = 0;
        const pickupHour = parseInt(pickupTime.split(':')[0]);
        const nightStartHour = parseInt(guideData.night_start_time?.split(':')[0] || '0');
        const nightEndHour = parseInt(guideData.night_end_time?.split(':')[0] || '8');
        
        // Check if pickup time falls within night hours
        if (pickupHour >= nightStartHour || pickupHour < nightEndHour) {
            surcharge = parseFloat(guideData.night_surcharge || '20.00');
        }
        
        const totalPrice = basePrice + surcharge;
        const tax = (totalPrice * 0.07).toFixed(2); // 7% tax
        
        // Build the complex booking data structure in required format
        const bookingData = [{
            bookingDate: serviceDate,
            guide_id: guideId,
            guide_name: guideData.name || "Guide Name",
            image: guideData.image || "",
            dmc_Id: guideData.dmc_id || "11",
            Mode: "dmc",
            entrypickup: `${city}, (${country})`,
            PickupPlaceid: null,
            DropoffPlaceid: null,
            pickupdate: serviceDate,
            entrytime: pickupTime,
            adults: adults,
            children: children,
            hours: hours,
            basePrice: basePrice.toFixed(2),
            surcharge: surcharge.toFixed(2),
            totalPrice: totalPrice.toFixed(2),
            Tax: tax,
            Night_Start_Time: guideData.night_start_time,
            Night_End_Time: guideData.night_end_time,
            fullName: customer_info.fullName,
            email: customer_info.email,
            phone: customer_info.phone,
            address1: customer_info.address1,
            address2: customer_info.address2,
            state: customer_info.state,
            zip: customer_info.zip,
            specialRequests: customer_info.specialRequests,
            countryCode: customer_info.countryCode,
            bookingType: "enquiry",
            agent_id: agentId,
            userInfo: {
                fullName: customer_info.fullName,
                email: customer_info.email,
                phone: customer_info.phone,
                countryCode: customer_info.countryCode,
                address1: customer_info.address1,
                address2: customer_info.address2,
                state: customer_info.state,
                zip: customer_info.zip
            }
        }];

        console.log('Guide booking data to be sent:', bookingData);

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = guideBaseUrl;
        
        // Add CSRF token
        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = "{{ csrf_token() }}";
        form.appendChild(token);

        // Add the complex booking data as JSON
        const bookingDataInput = document.createElement('input');
        bookingDataInput.type = 'hidden';
        bookingDataInput.name = 'booking_data';
        bookingDataInput.value = JSON.stringify(bookingData);
        form.appendChild(bookingDataInput);

        // Add basic form fields for backward compatibility
        const basicData = {
            agent_id: agentId,
            tour_id: tourId,
            guide_id: guideId,
            duration: duration,
            custom_hours: customHours,
            pickup_time: pickupTime,
            service_date: serviceDate,
            adults: adults,
            children: children,
            country: country,
            // city parameter removed,
            start_date: startDate,
            end_date: endDate
        };

        for (const [key, value] of Object.entries(basicData)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }

        // Add customer_info fields for backward compatibility
        for (const [key, value] of Object.entries(customer_info)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `customer_info[${key}]`;
            input.value = value;
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();

        // Close modal safely
        safeCloseModal('guideSelectionModal');
        
        // Show success message
        showNotification(`Guide ${guideData.name} selected successfully! Duration: ${duration === 'custom' ? customHours + ' hours' : duration}, Pickup: ${pickupTime}`, 'success');
        
        // Here you can add logic to update the guide fields in your form
        console.log('Selected guide:', {
            id: guideId,
            name: guideData.name,
            duration: duration,
            customHours: customHours,
            pickupTime: pickupTime
        });
    }
    
    // Restaurant Selection Modal Functions
    function showRestaurantSelectionModal(tourId, country, startDate, endDate) {
        // Populate modal with tour data
        document.getElementById('modal_restaurant_tour_dates').textContent = `${startDate} to ${endDate}`;
        document.getElementById('modal_restaurant_destination').textContent = `${country}`;
        // City display removed
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('restaurantSelectionModal'));
        modal.show();
        
        // Initialize modal functionality after modal is shown and Select2 is initialized
        setTimeout(() => {
            initializeRestaurantModal();
            // Ensure event listeners are attached after Select2 initialization
            // Select2 is initialized in shown.bs.modal event with 100ms delay, so we wait a bit more
            setTimeout(() => {
                const restaurantSelect = document.getElementById('modal_restaurant_select');
                if (restaurantSelect) {
                    const $restaurantSelect = $(restaurantSelect);
                    // Re-attach event listeners in case Select2 was initialized after our first attempt
                    if ($restaurantSelect.data('select2')) {
                        console.log('Re-attaching restaurant Select2 event listener');
                        $restaurantSelect.off('select2:select select2:selecting').on('select2:select', function(e) {
                            console.log('Restaurant Select2 select event triggered (re-attached)');
                            const selectedValue = e.params.data.id;
                            const selectedOption = restaurantSelect.querySelector(`option[value="${selectedValue}"]`);
                            if (selectedOption) {
                                console.log('Selected restaurant:', selectedOption.textContent);
                                // Ensure the native select value matches Select2's value
                                restaurantSelect.value = selectedValue;
                            }
                            onRestaurantSelection();
                        });
                    } else {
                        console.log('Select2 not initialized on restaurant select, using native change');
                        // Ensure native change event is attached
                        restaurantSelect.removeEventListener('change', onRestaurantSelection);
                        restaurantSelect.addEventListener('change', function() {
                            console.log('Restaurant native change event triggered (re-attached)');
                            onRestaurantSelection();
                        });
                    }
                }
                
                // Run validation after everything is initialized
                validateRestaurantForm();
            }, 200);
        }, 100);
        
        // Load restaurants for the city
        // loadRestaurantsForCity(city, country);
    }
    
    function initializeRestaurantModal() {
        // Check if already initialized to prevent duplicate event listeners
        if (window.restaurantModalInitialized) {
            // Just update the guest data and summary
            const adults = parseInt(document.getElementById('adults')?.value) || 1;
            const children = parseInt(document.getElementById('children')?.value) || 0;
            const infants = parseInt(document.getElementById('infants')?.value) || 0;
            const maleCount = parseInt(document.getElementById('male_count')?.value) || 1;
            const femaleCount = parseInt(document.getElementById('female_count')?.value) || 0;
            const pax = adults + children;
            
            window.modalGuestData = {
                pax: pax.toString(),
                adults: adults.toString(),
                children: children.toString(),
                infants: infants.toString(),
                male_count: maleCount.toString(),
                female_count: femaleCount.toString(),
                child_ages: (function() {
                    const hiddenField = document.getElementById('child_ages');
                    if (hiddenField && hiddenField.value) {
                        return hiddenField.value;
                    }
                    const children = parseInt(document.getElementById('children')?.value) || 0;
                    const childAgeValues = [];
                    for (let i = 1; i <= children; i++) {
                        const ageSelect = document.getElementById(`tour_child_age_${i}`);
                        if (ageSelect && ageSelect.value) {
                            childAgeValues.push(ageSelect.value);
                        }
                    }
                    return childAgeValues.join(',');
                })()
            };
            
            updateModalGuestSummaryDisplay();
            validateRestaurantForm();
            return;
        }
        
        // Mark as initialized
        window.restaurantModalInitialized = true;
        
        // Add event listeners only for elements that exist
        const citySelect = document.getElementById('modal_restaurant_city_select');
        const restaurantSelect = document.getElementById('modal_restaurant_select');
        const mealTypeSelect = document.getElementById('modal_restaurant_meal_type');
        const dishSelect = document.getElementById('modal_restaurant_dish');
        const timeSlotSelect = document.getElementById('modal_restaurant_time_slot');
        const diningDateSelect = document.getElementById('modal_restaurant_dining_date');
        const confirmBtn = document.getElementById('confirm_restaurant_btn');
        
        if (citySelect) {
            citySelect.addEventListener('change', function() {
                validateRestaurantForm();
            });
        }
        if (restaurantSelect) {
            // Use jQuery Select2 event for Select2 dropdowns, fallback to native change
            const $restaurantSelect = $(restaurantSelect);
            // Remove any existing event listeners to avoid duplicates
            $restaurantSelect.off('select2:select select2:selecting change');
            // Attach Select2 event (works with Select2)
            $restaurantSelect.on('select2:select', function(e) {
                console.log('Restaurant Select2 select event triggered');
                const selectedValue = e.params.data.id;
                const selectedOption = restaurantSelect.querySelector(`option[value="${selectedValue}"]`);
                if (selectedOption) {
                    console.log('Selected restaurant:', selectedOption.textContent);
                    // Ensure Select2 displays the selected value (it should already be set by Select2)
                    // Just ensure the native select value matches
                    restaurantSelect.value = selectedValue;
                }
                onRestaurantSelection();
            });
            // Also attach native change event as fallback
            restaurantSelect.addEventListener('change', function() {
                console.log('Restaurant native change event triggered');
                onRestaurantSelection();
            });
        }
        if (mealTypeSelect) {
            // Use jQuery Select2 event for Select2 dropdowns, fallback to native change
            const $mealTypeSelect = $(mealTypeSelect);
            // Remove any existing event listeners to avoid duplicates
            $mealTypeSelect.off('select2:select select2:change change');
            // Attach Select2 event (works with Select2)
            $mealTypeSelect.on('select2:select', function(e) {
                console.log('Meal Type Select2 select event triggered');
                onMealTypeSelection();
                validateRestaurantForm();
            });
            $mealTypeSelect.on('select2:change', function(e) {
                console.log('Meal Type Select2 change event triggered');
                onMealTypeSelection();
                validateRestaurantForm();
            });
            // Also attach native change event as fallback
            mealTypeSelect.addEventListener('change', function() {
                console.log('Meal Type native change event triggered');
                onMealTypeSelection();
                validateRestaurantForm();
            });
        }
        if (dishSelect) {
            dishSelect.addEventListener('change', validateRestaurantForm);
        }
        if (timeSlotSelect) {
            timeSlotSelect.addEventListener('change', validateRestaurantForm);
        }
        if (diningDateSelect) {
            // Date change event listener (optional - validation will still work with default date)
            diningDateSelect.addEventListener('change', function() {
                console.log('Dining date changed to:', diningDateSelect.value);
                validateRestaurantForm();
            });
            // Also validate on input event for better responsiveness
            diningDateSelect.addEventListener('input', validateRestaurantForm);
        }
        if (confirmBtn) {
            confirmBtn.addEventListener('click', confirmRestaurantSelection);
        }
        
        // Set date restrictions and default value
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (startDate && endDate && diningDateSelect) {
            diningDateSelect.min = startDate;
            diningDateSelect.max = endDate;
            diningDateSelect.value = startDate; // Default to start date
            
            // Mark that date has a default value so validation knows it's filled
            console.log('Default dining date set to:', startDate);
            
            // Trigger validation after setting default date
            // Use setTimeout to ensure the value is set in the DOM first
            setTimeout(() => {
                validateRestaurantForm();
            }, 50);
        }
        
        // Initialize guest data from tour data
        const adults = parseInt(document.getElementById('adults')?.value) || 1;
        const children = parseInt(document.getElementById('children')?.value) || 0;
        const infants = parseInt(document.getElementById('infants')?.value) || 0;
        const maleCount = parseInt(document.getElementById('male_count')?.value) || 1;
        const femaleCount = parseInt(document.getElementById('female_count')?.value) || 0;
        const pax = adults + children; // Calculate pax as adults + children
        
        // Set default guest data
        window.modalGuestData = {
            pax: pax.toString(),
            adults: adults.toString(),
            children: children.toString(),
            infants: infants.toString(),
            male_count: maleCount.toString(),
            female_count: femaleCount.toString(),
            child_ages: (function() {
                const hiddenField = document.getElementById('child_ages');
                if (hiddenField && hiddenField.value) {
                    return hiddenField.value;
                }
                // Fallback: collect from individual tour_child_age_X selects if available
                const children = parseInt(document.getElementById('children')?.value) || 0;
                const childAgeValues = [];
                for (let i = 1; i <= children; i++) {
                    const ageSelect = document.getElementById(`tour_child_age_${i}`);
                    if (ageSelect && ageSelect.value) {
                        childAgeValues.push(ageSelect.value);
                    }
                }
                return childAgeValues.join(',');
            })()
        };
        
        // Update the guest summary display immediately after setting the data
        updateModalGuestSummaryDisplay();
        
        // Initial validation
        validateRestaurantForm();
    }
    
    // Function to populate time slots based on meal period and restaurant timing
    function populateTimeSlotsBasedOnMealPeriod(mealPeriod, restaurantData) {
        const timeSlotSelect = document.getElementById('modal_restaurant_time_slot');
        if (!timeSlotSelect) return;
        
        timeSlotSelect.innerHTML = '<option value="">Select Time Slot</option>';
        
        let openTime, closeTime;
        
        // Get opening and closing times based on meal period
        switch(mealPeriod) {
            case 1: // Breakfast
                openTime = restaurantData.opening_time_bf;
                closeTime = restaurantData.closing_time_bf;
                break;
            case 2: // Lunch
                openTime = restaurantData.opening_time_lunch;
                closeTime = restaurantData.closing_time_lunch;
                break;
            case 3: // Dinner
                openTime = restaurantData.opening_time_dinner;
                closeTime = restaurantData.closing_time_dinner;
                break;
            default:
                console.log('Unknown meal period:', mealPeriod);
                return;
        }
        
        if (!openTime || !closeTime) {
            console.log('No timing data available for meal period:', mealPeriod);
            // Fallback to default time slots
            const defaultTimeSlots = ['07:00', '08:00', '09:00', '12:00', '13:00', '14:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00'];
            defaultTimeSlots.forEach(time => {
                const timeOption = document.createElement('option');
                timeOption.value = time;
                timeOption.textContent = time;
                timeSlotSelect.appendChild(timeOption);
            });
            return;
        }
        
        console.log('Generating time slots for meal period:', mealPeriod, 'from', openTime, 'to', closeTime);
        
        // Parse open and close times
        const startTime = parseTime(openTime);
        const endTime = parseTime(closeTime);
        
        if (!startTime || !endTime) {
            console.log('Failed to parse times:', openTime, closeTime);
            return;
        }
        
        // Generate 30-minute intervals
        let currentTime = new Date(startTime);
        
        while (currentTime <= endTime) {
            const timeValue = formatTime24(currentTime);
            const timeDisplay = formatTime12(currentTime);
            
            const option = document.createElement('option');
            option.value = timeValue;
            option.textContent = timeDisplay;
            timeSlotSelect.appendChild(option);
            
            // Add 30 minutes
            currentTime.setMinutes(currentTime.getMinutes() + 30);
        }
        
        // Validate form after time slots are populated
        console.log('Time slots populated, running validation');
        setTimeout(() => {
            validateRestaurantForm();
        }, 50);
    }
    
    // Parse time string (handles various formats)
    function parseTime(timeStr) {
        if (!timeStr) return null;
        
        try {
            console.log('Parsing time string:', timeStr);
            
            // Handle "HH:MM AM/PM" format
            if (timeStr.includes('AM') || timeStr.includes('PM')) {
                const today = new Date();
                const [time, period] = timeStr.split(' ');
                const [hours, minutes] = time.split(':');
                let hour = parseInt(hours);
                
                if (period === 'PM' && hour !== 12) hour += 12;
                if (period === 'AM' && hour === 12) hour = 0;
                
                today.setHours(hour, parseInt(minutes) || 0, 0, 0);
                console.log('Parsed 12-hour time:', today);
                return today;
            }
            
            // Handle "HH:MM:SS" or "HH:MM" format (24-hour)
            const timeMatch = timeStr.match(/(\d{1,2}):(\d{2})(?::(\d{2}))?/);
            if (timeMatch) {
                const today = new Date();
                const hour = parseInt(timeMatch[1]);
                const minute = parseInt(timeMatch[2]);
                const second = timeMatch[3] ? parseInt(timeMatch[3]) : 0;
                
                today.setHours(hour, minute, second, 0);
                console.log('Parsed 24-hour time:', today);
                return today;
            }
            
            console.log('Failed to parse time:', timeStr);
            return null;
        } catch (error) {
            console.error('Error parsing time:', timeStr, error);
            return null;
        }
    }
    
    // Format time to 24-hour format (HH:MM)
    function formatTime24(date) {
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        return `${hours}:${minutes}`;
    }
    
    // Format time to 12-hour format (HH:MM AM/PM)
    function formatTime12(date) {
        let hours = date.getHours();
        const minutes = date.getMinutes().toString().padStart(2, '0');
        const period = hours >= 12 ? 'PM' : 'AM';
        
        if (hours === 0) hours = 12;
        else if (hours > 12) hours -= 12;
        
        return `${hours}:${minutes} ${period}`;
    }

    function loadRestaurantsForCity(city, country) {
        const restaurantSelect = document.getElementById('modal_restaurant_select');
        const restaurantCount = document.getElementById('restaurant_count');
        const modalRestaurantCity = document.getElementById('modal_restaurant_city');
        const mealSelect = document.getElementById('modal_restaurant_meal_type');
        const dishSelect = document.getElementById('modal_restaurant_dish');
        
        // Update city name display
        if (modalRestaurantCity) {
            modalRestaurantCity.textContent = city || '';
        }
        
        // Clear existing options
        restaurantSelect.innerHTML = '<option value="">Search Restaurant</option>';
        console.log('City:', city);
        console.log('Country:', country);
        // For demo purposes, show sample restaurants
        // In production, this would fetch from API
        const all_restaurants = @json($restaurants);
        console.log('All Restaurants:', all_restaurants);
        
        // Convert object to array if needed
        const restaurantsArray = Array.isArray(all_restaurants) ? all_restaurants : Object.values(all_restaurants || {});
        console.log('Restaurants Array:', restaurantsArray);
        
        const restaurants = restaurantsArray.filter(restaurant => restaurant.city == city);
        console.log('Restaurants:', restaurants);
        
        // Add restaurant options
        restaurants.forEach(restaurant => {
            const option = document.createElement('option');
            option.value = restaurant.restaurant_id;
            option.textContent = `${restaurant.name} - ${restaurant.city}`;
            option.setAttribute('data-restaurant', JSON.stringify(restaurant));
            restaurantSelect.appendChild(option);
        });
        
        restaurantCount.textContent = restaurants.length;
        
        // Refresh Select2 if it's initialized on restaurant select to show new options
        const $restaurantSelect = $(restaurantSelect);
        if ($restaurantSelect.data('select2')) {
            console.log('Refreshing Select2 on restaurant select after loading restaurants');
            // Reset to empty value first to clear any previous selection
            $restaurantSelect.val(null).trigger('change.select2');
            // Force Select2 to update its internal cache of options
            setTimeout(function() {
                $restaurantSelect.select2('destroy');
                const $modal = $restaurantSelect.closest('.modal');
                const dropdownParent = $modal.length ? $modal : $('body');
                const firstOption = $restaurantSelect.find('option:first');
                let placeholder = 'Search Restaurant';
                if (firstOption.length && firstOption.val() === '') {
                    placeholder = firstOption.text() || 'Search Restaurant';
                }
                $restaurantSelect.select2({
                    theme: 'bootstrap-5',
                    placeholder: placeholder,
                    allowClear: true,
                    width: '100%',
                    closeOnSelect: true,
                    dropdownParent: dropdownParent
                });
                // Re-attach event listener after reinitialization
                $restaurantSelect.off('select2:select').on('select2:select', function(e) {
                    console.log('Restaurant Select2 select event triggered (after reinit)');
                    const selectedValue = e.params.data.id;
                    const selectedOption = restaurantSelect.querySelector(`option[value="${selectedValue}"]`);
                    if (selectedOption) {
                        console.log('Selected restaurant:', selectedOption.textContent);
                        restaurantSelect.value = selectedValue;
                    }
                    onRestaurantSelection();
                });
            }, 100);
        }
        
        // Clear dependent fields when city changes
        if (mealSelect) {
            mealSelect.innerHTML = '<option value="">Select Restaurant First</option>';
        }
        if (dishSelect) {
            dishSelect.innerHTML = '<option value="">Select Dish</option>';
        }
    }
    function onMealTypeSelection() {
        const mealSelect = document.getElementById('modal_restaurant_meal_type');
        const dishSelect = document.getElementById('modal_restaurant_dish');
        const timeSlotSelect = document.getElementById('modal_restaurant_time_slot');
        const restaurantSelect = document.getElementById('modal_restaurant_select');
        
        if (!mealSelect || !mealSelect.value) {
            dishSelect.innerHTML = '<option value="">Select Dish</option>';
            timeSlotSelect.innerHTML = '<option value="">Select Time Slot</option>';
            return;
        }
        
        const selectedMealOption = mealSelect.options[mealSelect.selectedIndex];
        if (!selectedMealOption || !selectedMealOption.getAttribute('data-meal')) {
            return;
        }
        
        const mealData = JSON.parse(selectedMealOption.getAttribute('data-meal'));
        const pax = getPax();
        const adultPrice = mealData.adult_price * (pax.maleCount + pax.femaleCount);
        const childPrice = mealData.child_price * pax.children;
        const totalPrice = adultPrice + childPrice;
        
        const mealPriceSection = document.getElementById('meal-price-section');
        if (mealPriceSection) {
            mealPriceSection.textContent = 'Adult Price: '+adultPrice + ' - ' + 'Child Price: '+childPrice+', Total Price: '+totalPrice;
        }

        dishSelect.innerHTML = '<option value="">Select Dish</option>';
        // Add dish options based on meal data
        const dishOption = document.createElement('option');
        dishOption.value = mealData.meal_id;
        dishOption.textContent = mealData.type == 1 ? 'Buffet' : mealData.type == 2 ? 'Set Menu' : mealData.type == 3 ? 'A-La-Carte' : '...';
        dishOption.setAttribute('data-dish', JSON.stringify(mealData));
        dishSelect.appendChild(dishOption);
        
        // Auto-select the first (and only) dish option
        if (dishSelect.options.length > 1) {
            dishSelect.selectedIndex = 1; // Select the dish (skip "Select Dish" option)
            console.log('Auto-selected dish:', dishOption.textContent);
        }
        
        // Get restaurant data to populate time slots
        if (restaurantSelect && restaurantSelect.value) {
            const selectedRestaurantOption = restaurantSelect.options[restaurantSelect.selectedIndex];
            if (selectedRestaurantOption && selectedRestaurantOption.getAttribute('data-restaurant')) {
                const restaurantData = JSON.parse(selectedRestaurantOption.getAttribute('data-restaurant'));
                // Set time slots based on restaurant's opening/closing times for the selected meal period
                populateTimeSlotsBasedOnMealPeriod(mealData.meal_period, restaurantData);
            }
        }
        
        // Validate form after meal type selection
        // Use setTimeout to ensure all DOM updates are complete
        setTimeout(() => {
            console.log('Running validation after meal type selection and dish/time slot population');
            validateRestaurantForm();
        }, 100);
    }
    
    function getPax() {
        const maleInput = document.getElementById('modal_male_count');
        const femaleInput = document.getElementById('modal_female_count');
        const childrenInput = document.getElementById('modal_children');

        function calculatePax() {
            const maleCount = parseInt(maleInput.value) || 0;
            const femaleCount = parseInt(femaleInput.value) || 0;
            const children = parseInt(childrenInput.value) || 0;

            const pax = maleCount + femaleCount;
            const result = {
                pax: pax,
                maleCount: maleCount,
                femaleCount: femaleCount,
                children: children
            };
            console.log("Total Pax:", pax);
            return result;
        }

        // Attach event listeners
        maleInput.addEventListener('change', calculatePax);
        femaleInput.addEventListener('change', calculatePax);
        childrenInput.addEventListener('change', calculatePax);

        // Initial calculation
        return calculatePax();
    }

    
    function onRestaurantSelection() {
        const restaurantSelect = document.getElementById('modal_restaurant_select');
        if (!restaurantSelect) {
            console.error('Restaurant select element not found');
            return;
        }
        
        const restaurantDetailsContainer = document.getElementById('restaurant_details_container');
        const mealSelect = document.getElementById('modal_restaurant_meal_type');
        const dishSelect = document.getElementById('modal_restaurant_dish');
        const timeSlotSelect = document.getElementById('modal_restaurant_time_slot');
        
        // Get selected option - handle both Select2 and native select
        let selectedOption = null;
        let selectedValue = null;
        const $restaurantSelect = $(restaurantSelect);
        if ($restaurantSelect.data('select2')) {
            // Select2 is initialized, get selected option using jQuery
            selectedValue = $restaurantSelect.val();
            if (selectedValue) {
                selectedOption = restaurantSelect.querySelector(`option[value="${selectedValue}"]`);
                // Ensure Select2 displays the selected value properly by re-setting it
                if (selectedOption) {
                    // Re-set the value to ensure Select2 displays the correct text
                    $restaurantSelect.val(selectedValue).trigger('change.select2');
                }
            }
        } else {
            // Native select
            selectedValue = restaurantSelect.value;
            selectedOption = restaurantSelect.options[restaurantSelect.selectedIndex];
        }
        
        // Clear dependent dropdowns
        if (mealSelect) {
            mealSelect.innerHTML = '<option value="">Select Meal</option>';
        }
        if (dishSelect) {
            dishSelect.innerHTML = '<option value="">Select Dish</option>';
        }
        if (timeSlotSelect) {
            timeSlotSelect.innerHTML = '<option value="">Select Time Slot</option>';
        }
        
        if (restaurantSelect.value && selectedOption && selectedOption.getAttribute('data-restaurant')) {
            try {
                const restaurantData = JSON.parse(selectedOption.getAttribute('data-restaurant'));
                console.log('Restaurant selected, data:', restaurantData);
                
                // Show restaurant details
                const imageEl = document.getElementById('selected_restaurant_image');
                const nameEl = document.getElementById('selected_restaurant_name');
                const cuisineEl = document.getElementById('selected_restaurant_cuisine');
                const locationEl = document.getElementById('selected_restaurant_location');
                const ratingEl = document.getElementById('selected_restaurant_rating');
                const priceRangeEl = document.getElementById('selected_restaurant_price_range');
                
                if (imageEl) imageEl.src = restaurantData.master_image || '/assets/images/default-restaurant.png';
                if (nameEl) nameEl.textContent = restaurantData.name || '';
                if (cuisineEl) cuisineEl.textContent = (restaurantData.cuisine || '') + ' Cuisine';
                if (locationEl) locationEl.textContent = restaurantData.location || '';
                if (ratingEl) ratingEl.textContent = restaurantData.rating || '';
                if (priceRangeEl) priceRangeEl.textContent = restaurantData.price_range || '';

                // Set Meal Options
                if (mealSelect && restaurantData.meals && Array.isArray(restaurantData.meals) && restaurantData.meals.length > 0) {
                    console.log('Populating meal options, count:', restaurantData.meals.length);
                    restaurantData.meals.forEach(meal => {
                        let mealType = meal.type == 1 
                            ? 'Buffet' 
                            : meal.type == 2 
                                ? 'Set Menu' 
                                : meal.type == 3 
                                    ? 'A-La-Carte' 
                                    : '...';
                        let mealPeriod = meal.meal_period == 1 ? 'Breakfast' : meal.meal_period == 2 ? 'Lunch' : meal.meal_period == 3 ? 'Dinner' : '...';

                        let mealCategory = meal.category == 1 ? 'Alcoholic' : meal.category == 2 ? 'Non Alcoholic' : 'No Beverage';

                        let mealItemType = meal.item_type == 1 ? 'Vegetarian' : meal.item_type == 2 ? 'Non Vegetarian' : '...';

                        let mealName = mealPeriod + ' - ' + mealType + ' - ' + mealCategory + ' - ' + mealItemType;
                        console.log('Adding meal option:', mealName);
                        const mealOption = document.createElement('option');
                        mealOption.value = meal.meal_id;
                        mealOption.textContent = mealName;
                        mealOption.setAttribute('data-meal', JSON.stringify(meal));
                        mealSelect.appendChild(mealOption);
                    });
                    
                    // Refresh Select2 to show the new meal options
                    const $mealSelect = $(mealSelect);
                    if ($mealSelect.data('select2')) {
                        console.log('Refreshing Select2 on meal select after adding options');
                        // Destroy and reinitialize Select2 to ensure new options are visible
                        $mealSelect.select2('destroy');
                        $mealSelect.removeAttr('data-select2-initialized');
                    }
                    // Initialize or reinitialize Select2
                    setTimeout(function() {
                        const $modal = $mealSelect.closest('.modal');
                        const dropdownParent = $modal.length ? $modal : $('body');
                        const firstOption = $mealSelect.find('option:first');
                        let placeholder = 'Select Meal Type';
                        if (firstOption.length && firstOption.val() === '') {
                            placeholder = firstOption.text() || 'Select Meal Type';
                        }
                        $mealSelect.select2({
                            theme: 'bootstrap-5',
                            placeholder: placeholder,
                            allowClear: true,
                            width: '100%',
                            closeOnSelect: true,
                            dropdownParent: dropdownParent
                        });
                        $mealSelect.attr('data-select2-initialized', 'true');
                        console.log('Select2 reinitialized on meal select with', mealSelect.options.length, 'options');
                    }, 50);
                } else {
                    console.warn('No meals found in restaurant data, attempting to fetch from API', {
                        mealSelect: !!mealSelect,
                        hasMeals: restaurantData.meals && Array.isArray(restaurantData.meals),
                        mealsLength: restaurantData.meals ? restaurantData.meals.length : 0,
                        restaurantId: restaurantData.restaurant_id
                    });
                    
                    // Fallback: Fetch meals from API if not in restaurant data
                    if (restaurantData.restaurant_id && mealSelect) {
                        fetchMealsForRestaurant(restaurantData.restaurant_id, mealSelect);
                    }
                }
                
                // Note: Meal type selection is now handled by onMealTypeSelection() function
                // which is set up in initializeRestaurantModal() to avoid duplicate event listeners
                
                if (restaurantDetailsContainer) {
                    restaurantDetailsContainer.style.display = 'block';
                }
            } catch (error) {
                console.error('Error in onRestaurantSelection:', error);
                if (restaurantDetailsContainer) {
                    restaurantDetailsContainer.style.display = 'none';
                }
            }
        } else {
            if (restaurantDetailsContainer) {
                restaurantDetailsContainer.style.display = 'none';
            }
        }
        
        // Validate form after restaurant selection
        validateRestaurantForm();
        
        validateRestaurantForm();
    }
    
    // Function to fetch meals from API for restaurant modal
    async function fetchMealsForRestaurant(restaurantId, mealSelect) {
        try {
            const response = await fetch(`{{ route('fetch-meals-by-restaurant') }}?restaurant_id=${restaurantId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            if (!response.ok) {
                throw new Error('Failed to fetch meals');
            }
            
            const data = await response.json();
            
            if (data.success && data.meals && Array.isArray(data.meals) && data.meals.length > 0) {
                console.log('Fetched meals from API, count:', data.meals.length);
                
                // Clear existing options except the first placeholder
                mealSelect.innerHTML = '<option value="">Select Meal Type</option>';
                
                // Add meal options
                data.meals.forEach(meal => {
                    let mealType = meal.type == 1 
                        ? 'Buffet' 
                        : meal.type == 2 
                            ? 'Set Menu' 
                            : meal.type == 3 
                                ? 'A-La-Carte' 
                                : '...';
                    let mealPeriod = meal.meal_period == 1 ? 'Breakfast' : meal.meal_period == 2 ? 'Lunch' : meal.meal_period == 3 ? 'Dinner' : '...';
                    let mealCategory = meal.category == 1 ? 'Alcoholic' : meal.category == 2 ? 'Non Alcoholic' : 'No Beverage';
                    let mealItemType = meal.item_type == 1 ? 'Vegetarian' : meal.item_type == 2 ? 'Non Vegetarian' : '...';
                    let mealName = mealPeriod + ' - ' + mealType + ' - ' + mealCategory + ' - ' + mealItemType;
                    
                    const mealOption = document.createElement('option');
                    mealOption.value = meal.meal_id;
                    mealOption.textContent = mealName;
                    mealOption.setAttribute('data-meal', JSON.stringify(meal));
                    mealSelect.appendChild(mealOption);
                });
                
                // Refresh Select2 to show the new meal options
                const $mealSelect = $(mealSelect);
                if ($mealSelect.data('select2')) {
                    $mealSelect.select2('destroy');
                    $mealSelect.removeAttr('data-select2-initialized');
                }
                // Initialize or reinitialize Select2
                setTimeout(function() {
                    const $modal = $mealSelect.closest('.modal');
                    const dropdownParent = $modal.length ? $modal : $('body');
                    const firstOption = $mealSelect.find('option:first');
                    let placeholder = 'Select Meal Type';
                    if (firstOption.length && firstOption.val() === '') {
                        placeholder = firstOption.text() || 'Select Meal Type';
                    }
                    $mealSelect.select2({
                        theme: 'bootstrap-5',
                        placeholder: placeholder,
                        allowClear: true,
                        width: '100%',
                        closeOnSelect: true,
                        dropdownParent: dropdownParent
                    });
                    $mealSelect.attr('data-select2-initialized', 'true');
                    console.log('Select2 reinitialized on meal select with', mealSelect.options.length, 'options (from API)');
                }, 50);
            } else {
                console.warn('No meals returned from API for restaurant:', restaurantId);
                if (mealSelect) {
                    mealSelect.innerHTML = '<option value="">No meals available</option>';
                }
            }
        } catch (error) {
            console.error('Error fetching meals from API:', error);
            if (mealSelect) {
                mealSelect.innerHTML = '<option value="">Error loading meals</option>';
            }
        }
    }
    
    function validateRestaurantForm() {
        const citySelect = document.getElementById('modal_restaurant_city_select');
        const restaurantSelect = document.getElementById('modal_restaurant_select');
        const mealType = document.getElementById('modal_restaurant_meal_type');
        const dishSelect = document.getElementById('modal_restaurant_dish');
        const timeSlotSelect = document.getElementById('modal_restaurant_time_slot');
        const diningDateSelect = document.getElementById('modal_restaurant_dining_date');
        const confirmBtn = document.getElementById('confirm_restaurant_btn');
        
        // Always enable the confirm button
        if (confirmBtn) {
            confirmBtn.disabled = false;
        }
    }

    // Tour Guest Selector Functions
    function openTourGuestSelector() {
        // Get current values from hidden fields
        const adults = parseInt(document.getElementById('adults').value) || 1;
        const children = parseInt(document.getElementById('children').value) || 0;
        const infants = parseInt(document.getElementById('infants').value) || 0;
        const maleCount = parseInt(document.getElementById('male_count').value) || adults;
        const femaleCount = parseInt(document.getElementById('female_count').value) || 0;
        
        // Get existing child ages from hidden field (comma-separated string)
        const childAgesHidden = document.getElementById('child_ages');
        let existingAges = [];
        if (childAgesHidden && childAgesHidden.value) {
            // Split comma-separated string into array
            existingAges = childAgesHidden.value.split(',').filter(age => age && age.trim() !== '');
        }
        
        // Set values in modal
        document.getElementById('tour_male_count').value = maleCount;
        document.getElementById('tour_female_count').value = femaleCount;
        document.getElementById('tour_children_count').value = children;
        document.getElementById('tour_infants_count').value = infants;
        
        // Update child age selects
        updateChildAgeSelects(children, existingAges);
        
        // Update summary in modal
        updateTourGuestSummary();
        
        // Open modal
        const modal = new bootstrap.Modal(document.getElementById('tourGuestSelectorModal'));
        modal.show();
    }
    
    function incrementTourCount(fieldId) {
        const field = document.getElementById(fieldId);
        const currentValue = parseInt(field.value) || 0;
        const maxValue = parseInt(field.max) || 20;
        
        if (currentValue < maxValue) {
            field.value = currentValue + 1;
            
            // If this is the children count field, update child age selects
            if (fieldId === 'tour_children_count') {
                updateChildAgeSelects(currentValue + 1);
            }
            
            updateTourGuestSummary();
        }
    }
    
    function decrementTourCount(fieldId) {
        const field = document.getElementById(fieldId);
        const currentValue = parseInt(field.value) || 0;
        const minValue = parseInt(field.min) || 0;
        
        if (currentValue > minValue) {
            field.value = currentValue - 1;
            
            // If this is the children count field, update child age selects
            if (fieldId === 'tour_children_count') {
                updateChildAgeSelects(currentValue - 1);
            }
            
            updateTourGuestSummary();
        }
    }
    
    function updateChildAgeSelects(childrenCount, existingAges = []) {
        const container = document.getElementById('tour_child_ages_container');
        const listContainer = document.getElementById('tour_child_ages_list');
        
        if (!container || !listContainer) return;
        
        // If existingAges is not provided, try to get from current selects
        if (existingAges.length === 0) {
            const currentCount = parseInt(document.getElementById('tour_children_count').value) || 0;
            for (let i = 1; i <= currentCount; i++) {
                const ageSelect = document.getElementById(`tour_child_age_${i}`);
                if (ageSelect && ageSelect.value) {
                    existingAges.push(ageSelect.value);
                } else {
                    existingAges.push('');
                }
            }
        }
        
        // Show/hide container based on children count
        if (childrenCount > 0) {
            container.style.display = 'block';
            
            // Clear existing selects
            listContainer.innerHTML = '';
            
            // Create select boxes for each child
            for (let i = 1; i <= childrenCount; i++) {
                const selectWrapper = document.createElement('div');
                selectWrapper.className = 'mb-2';
                
                const label = document.createElement('label');
                label.className = 'form-label mb-1 small fw-semibold';
                label.textContent = `Child ${i}:`;
                label.setAttribute('for', `tour_child_age_${i}`);
                
                const select = document.createElement('select');
                select.className = 'form-select form-select-sm';
                select.id = `tour_child_age_${i}`;
                select.name = `tour_child_age_${i}`;
                
                // Add default option
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Select age';
                select.appendChild(defaultOption);
                
                // Add age options (1-17)
                for (let age = 1; age <= 17; age++) {
                    const option = document.createElement('option');
                    option.value = age;
                    option.textContent = age;
                    // Select existing age if available
                    if (existingAges.length >= i && existingAges[i - 1] == age) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                }
                
                selectWrapper.appendChild(label);
                selectWrapper.appendChild(select);
                listContainer.appendChild(selectWrapper);
            }
        } else {
            container.style.display = 'none';
            listContainer.innerHTML = '';
        }
    }
    
    function updateTourGuestSummary() {
        const maleCount = parseInt(document.getElementById('tour_male_count').value) || 0;
        const femaleCount = parseInt(document.getElementById('tour_female_count').value) || 0;
        const children = parseInt(document.getElementById('tour_children_count').value) || 0;
        const infants = parseInt(document.getElementById('tour_infants_count').value) || 0;
        const adults = maleCount + femaleCount;
        
        const summary = `${adults} adults (${maleCount} male, ${femaleCount} female), ${children} children, ${infants} infants`;
        const summaryElement = document.getElementById('tour_guest_summary');
        if (summaryElement) {
            summaryElement.textContent = summary;
        }
    }
    
    function confirmTourGuestSelection() {
        const maleCount = parseInt(document.getElementById('tour_male_count').value) || 0;
        const femaleCount = parseInt(document.getElementById('tour_female_count').value) || 0;
        const children = parseInt(document.getElementById('tour_children_count').value) || 0;
        const infants = parseInt(document.getElementById('tour_infants_count').value) || 0;
        const adults = maleCount + femaleCount;
        
        // Collect child ages
        const childAges = [];
        for (let i = 1; i <= children; i++) {
            const ageSelect = document.getElementById(`tour_child_age_${i}`);
            if (ageSelect && ageSelect.value) {
                childAges.push(ageSelect.value);
            }
        }
        
        // Update hidden fields
        document.getElementById('adults').value = adults;
        document.getElementById('children').value = children;
        document.getElementById('infants').value = infants;
        document.getElementById('male_count').value = maleCount;
        document.getElementById('female_count').value = femaleCount;
        
        // Update child_ages hidden field with comma-separated values
        const childAgesHidden = document.getElementById('child_ages');
        if (childAgesHidden) {
            childAgesHidden.value = childAges.join(',');
        }
        
        // Update summary display
        updateTourGuestSummary();
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('tourGuestSelectorModal'));
        if (modal) {
            modal.hide();
        }
        
        // Show success notification
        if (typeof showNotification === 'function') {
            showNotification('Guest selection updated successfully', 'success');
        }
    }

    // Guest selector functions for restaurant modal
    function openModalGuestSelector() {
        // Initialize guest selector modal with current values
        const guestData = window.modalGuestData || {
            pax: '1',
            children: '0',
            infants: '0',
            male_count: '1',
            female_count: '0',
            child_ages: ''
        };
        
        // Set current values in the modal
        document.getElementById('modal_pax').value = guestData.pax;
        document.getElementById('modal_children').value = guestData.children;
        document.getElementById('modal_infants').value = guestData.infants;
        document.getElementById('modal_male_count').value = guestData.male_count;
        document.getElementById('modal_female_count').value = guestData.female_count;
        document.getElementById('modal_child_ages').value = guestData.child_ages;
        
        // Add event listeners for the modal inputs
        const modalInputs = ['modal_pax', 'modal_children', 'modal_infants', 'modal_male_count', 'modal_female_count', 'modal_child_ages'];
        modalInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('input', updateModalGuestSummary);
            }
        });
        
        const modal = new bootstrap.Modal(document.getElementById('modalGuestSelectorModal'));
        modal.show();
    }

    function incrementCount(fieldId) {
        const field = document.getElementById(fieldId);
        const currentValue = parseInt(field.value);
        const maxValue = parseInt(field.max);
        
        if (fieldId === 'modal_pax') {
            // For pax, just increment normally
            if (currentValue < maxValue) {
                field.value = currentValue + 1;
                updateModalGuestSummary();
            }
        } else {
            // For other fields, check if incrementing would exceed pax
            const paxValue = parseInt(document.getElementById('modal_pax').value);
            const childrenValue = parseInt(document.getElementById('modal_children').value);
            const maleValue = parseInt(document.getElementById('modal_male_count').value);
            const femaleValue = parseInt(document.getElementById('modal_female_count').value);
            
            let newValue = currentValue;
            if (fieldId === 'modal_children') {
                newValue = childrenValue + 1;
            } else if (fieldId === 'modal_male_count') {
                newValue = maleValue + 1;
            } else if (fieldId === 'modal_female_count') {
                newValue = femaleValue + 1;
            }
            
            // Check if the new total would exceed pax
            const totalAfterIncrement = newValue + (fieldId === 'modal_children' ? maleValue + femaleValue : 
                                                   fieldId === 'modal_male_count' ? childrenValue + femaleValue : 
                                                   childrenValue + maleValue);
            
            if (totalAfterIncrement <= paxValue && currentValue < maxValue) {
                field.value = currentValue + 1;
                updateModalGuestSummary();
            } else if (totalAfterIncrement > paxValue) {
                showNotification('Total of children, males, and females cannot exceed pax count', 'warning');
            }
        }
    }

    function decrementCount(fieldId) {
        const field = document.getElementById(fieldId);
        const currentValue = parseInt(field.value);
        const minValue = parseInt(field.min);
        
        if (fieldId === 'modal_pax') {
            // For pax, check if decrementing would make it less than the sum of other fields
            const childrenValue = parseInt(document.getElementById('modal_children').value);
            const maleValue = parseInt(document.getElementById('modal_male_count').value);
            const femaleValue = parseInt(document.getElementById('modal_female_count').value);
            const totalOthers = childrenValue + maleValue + femaleValue;
            
            if (currentValue > totalOthers && currentValue > minValue) {
                field.value = currentValue - 1;
                updateModalGuestSummary();
            } else if (currentValue <= totalOthers) {
                showNotification('Pax cannot be less than the sum of children, males, and females', 'warning');
            }
        } else {
            // For other fields, just decrement normally
            if (currentValue > minValue) {
                field.value = currentValue - 1;
                updateModalGuestSummary();
            }
        }
    }

    function updateModalGuestSummary() {
        const pax = parseInt(document.getElementById('modal_pax').value);
        const children = parseInt(document.getElementById('modal_children').value);
        const infants = parseInt(document.getElementById('modal_infants').value);
        const maleCount = parseInt(document.getElementById('modal_male_count').value);
        const femaleCount = parseInt(document.getElementById('modal_female_count').value);
        const adults = pax - children; // Calculate adults as pax - children

        const summary = `${pax} pax (${adults} adults, ${children} children) - ${maleCount} male, ${femaleCount} female -${infants} infants`;
        
        // Update summary if element exists
        const summaryElement = document.getElementById('modal_restaurant_guest_summary');
        if (summaryElement) {
            summaryElement.textContent = summary;
        }

        // Update badges
        const badges = document.querySelectorAll('.guest-badges .badge');
        if (badges.length >= 3) {
            badges[0].textContent = adults;
            badges[1].textContent = children;
            badges[2].textContent = infants;
        }

        // Enable/disable child ages field
        const childAgesField = document.getElementById('modal_child_ages');
        if (childAgesField) {
            if (children > 0) {
                childAgesField.disabled = false;
                childAgesField.required = true;
            } else {
                childAgesField.disabled = true;
                childAgesField.required = false;
                childAgesField.value = '';
            }
        }
        
        // Validate total doesn't exceed pax
        const total = children + maleCount + femaleCount;
        if (total > pax) {
            showNotification('Total of children, males, and females exceeds pax count', 'warning');
        }
    }

    function updateModalGuestSummaryDisplay() {
        // Update guest summary display from window.modalGuestData
        if (!window.modalGuestData) return;
        
        const pax = parseInt(window.modalGuestData.pax) || 1;
        const children = parseInt(window.modalGuestData.children) || 0;
        const infants = parseInt(window.modalGuestData.infants) || 0;
        const maleCount = parseInt(window.modalGuestData.male_count) || 1;
        const femaleCount = parseInt(window.modalGuestData.female_count) || 0;
        const adults = parseInt(window.modalGuestData.adults) || (pax - children);

        const summary = `${pax} pax (${adults} adults, ${children} children) - ${maleCount} male, ${femaleCount} female - ${infants} infants`;
        
        // Update summary if element exists
        const summaryElement = document.getElementById('modal_restaurant_guest_summary');
        if (summaryElement) {
            summaryElement.textContent = summary;
        }

        // Update badges in restaurant modal
        const badgesContainer = document.querySelector('#restaurantSelectionModal .guest-badges');
        if (badgesContainer) {
            const badges = badgesContainer.querySelectorAll('.badge');
            if (badges.length >= 3) {
                badges[0].textContent = adults;
                badges[1].textContent = children;
                badges[2].textContent = infants;
            }
        }
    }

    function confirmModalGuestSelection() {
        const pax = document.getElementById('modal_pax').value;
        const children = document.getElementById('modal_children').value;
        const infants = document.getElementById('modal_infants').value;
        const maleCount = document.getElementById('modal_male_count').value;
        const femaleCount = document.getElementById('modal_female_count').value;
        const childAges = document.getElementById('modal_child_ages').value;
        const adults = parseInt(pax) - parseInt(children); // Calculate adults

        // Store the values for use in restaurant booking
        window.modalGuestData = {
            pax: pax,
            adults: adults.toString(),
            children: children,
            infants: infants,
            male_count: maleCount,
            female_count: femaleCount,
            child_ages: childAges
        };

        updateModalGuestSummary();

        // Close modal safely
        safeCloseModal('modalGuestSelectorModal');

        showNotification('Guest selection updated successfully', 'success');
    }
    
    const restaurantBaseUrl = "{{ route('orders.restaurants.select') }}";
    
    function confirmRestaurantSelection() {
        const formData = new FormData(document.getElementById('restaurantSelectionForm'));
        const restaurantId = formData.get('restaurant_id');
        const mealType = formData.get('meal_type');
        const dishId = formData.get('modal_restaurant_dish');
        const timeSlot = formData.get('modal_restaurant_time_slot');
        const diningDate = formData.get('dining_date');
        
        // Validate required fields before submission
        if (!restaurantId) {
            showNotification('Please select a restaurant', 'error');
            return;
        }
        if (!mealType) {
            showNotification('Please select a meal type', 'error');
            return;
        }
        if (!dishId) {
            showNotification('Please select a dish', 'error');
            return;
        }
        if (!timeSlot) {
            showNotification('Please select a time slot', 'error');
            return;
        }
        if (!diningDate) {
            showNotification('Please select a dining date', 'error');
            return;
        }
        
        const customer_info = getCustomerInfo();
        
        const agentId = document.getElementById('agent_id').value;
        const dmcUser = @json($UserDmc);
        
        // Get selected restaurant details
        const restaurantSelect = document.getElementById('modal_restaurant_select');
        const selectedOption = restaurantSelect.options[restaurantSelect.selectedIndex];
        const restaurantData = selectedOption ? JSON.parse(selectedOption.getAttribute('data-restaurant')) : {};
        
        
        const mealSelect = document.getElementById('modal_restaurant_meal_type');
        const selectedOptionMeal = mealSelect.options[mealSelect.selectedIndex];
        const mealData = selectedOptionMeal ? JSON.parse(selectedOptionMeal.getAttribute('data-meal')) : {};
        console.log('Meal data:', mealData);
        
        const dishSelect = document.getElementById('modal_restaurant_dish');
        const selectedOptionDish = dishSelect.options[dishSelect.selectedIndex];
        const dishData = selectedOptionDish ? JSON.parse(selectedOptionDish.getAttribute('data-dish')) : {};
        //console.log('Dish data:', dishData);
        
        // Get guest data from modal
        const guestData = window.modalGuestData || {
            adults: '1',
            children: '0',
            infants: '0',
            male_count: '1',
            female_count: '0',
            child_ages: ''
        };
        
        // Get tour details
        const tourId = document.getElementById('tour_id').value;
        const country = document.getElementById('user_country').value;
        
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        // Calculate pricing based on guest data
        const adultPrice = parseFloat(mealData.adult_price || '0');
        const childPrice = parseFloat(mealData.child_price || '0');
        const basePrice = adultPrice * parseInt(guestData.adults) + childPrice * parseInt(guestData.children);
        const totalPrice = basePrice;
        
        // Build the complex booking data structure in required format
        const bookingData = [{
            fullName: customer_info.fullName,
            email: customer_info.email,
            phone: customer_info.phone,
            countryCode: customer_info.countryCode,
            address1: customer_info.address1,
            address2: customer_info.address2,
            state: customer_info.state,
            zip: customer_info.zip,
            specialRequests: customer_info.specialRequests,
            bookingDate: diningDate,
            visitTime: timeSlot,
            adultCount: parseInt(guestData.adults),
            childCount: parseInt(guestData.children),
            restaurantId: parseInt(restaurantId),
            restaurantName: restaurantData.name || "Restaurant Name",
            mealType: mealData.meal_period == 1 ? 'Breakfast' : mealData.meal_period == 2 ? 'Lunch' : mealData.meal_period == 3 ? 'Dinner' : '...',
            mealSpecificType: mealData.type == 1 ? 'Buffet' : mealData.type == 2 ? 'Set Menu' : mealData.type == 3 ? 'A-La-Carte' : '...',
            MealDescription: [
                {
                    item_name: dishData.name,
                    name: dishData.item_description,
                    price: parseFloat(dishData.price || '0'),
                    meal_id: parseInt(dishId),
                    category: dishData.category == 1 ? 'Alcoholic' : dishData.category == 2 ? 'Non Alcoholic' : 'No Beverage',
                    item_type: dishData.item_type == 1 ? 'Vegetarian' : dishData.item_type == 2 ? 'Non Vegetarian' : '...',
                    quantity: parseInt(guestData.adults) + parseInt(guestData.children)
                }
            ],
            totalPrice: totalPrice,
            priceTypes: ["dmc"],
            dmc_id: dmcUser.userId || "",
            bookingType: "enquiry"
        }];

        //console.log('Restaurant booking data to be sent:', bookingData);

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = restaurantBaseUrl;
        
        // Add CSRF token
        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = "{{ csrf_token() }}";
        form.appendChild(token);

        // Add the complex booking data as JSON
        const bookingDataInput = document.createElement('input');
        bookingDataInput.type = 'hidden';
        bookingDataInput.name = 'booking_data';
        bookingDataInput.value = JSON.stringify(bookingData);
        form.appendChild(bookingDataInput);

        // Add basic form fields for backward compatibility
        const basicData = {
            agent_id: agentId,
            tour_id: tourId,
            restaurant_id: restaurantId,
            meal_type: mealType,
            dish_id: dishId,
            time_slot: timeSlot,
            dining_date: diningDate,
            adults: guestData.adults,
            children: guestData.children,
            infants: guestData.infants,
            male_count: guestData.male_count,
            female_count: guestData.female_count,
            child_ages: guestData.child_ages,
            country: country,
            // city parameter removed,
            start_date: startDate,
            end_date: endDate
        };

        for (const [key, value] of Object.entries(basicData)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }

        // Add customer_info fields for backward compatibility
        for (const [key, value] of Object.entries(customer_info)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `customer_info[${key}]`;
            input.value = value;
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();

        // Close modal safely
        safeCloseModal('restaurantSelectionModal');
        
        // Show success message
        showNotification(`Restaurant ${restaurantData.name} selected successfully! Meal: ${mealData.meal_period == 1 ? 'Breakfast' : mealData.meal_period == 2 ? 'Lunch' : 'Dinner'} at ${timeSlot} for ${guestData.adults} adults, ${guestData.children} children`, 'success');
        
        // Here you can add logic to update the restaurant fields in your form
        
    }
    
    // Order management functions
    window.cancelExistingOrder = function(orderId) {
        if (confirm('Are you sure you want to cancel this service?')) {
            showNotification('Cancelling service...', 'info');
            
            const url = route('api.orders.cancel', orderId);
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Service cancelled successfully', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification('Failed to cancel service: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error cancelling service:', error);
                showNotification('Error cancelling service', 'error');
            });
        }
    };
    
    // Remove service functions
    window.removeAttractionService = function(orderId) {
        
        removeService(orderId, 'attraction');
    };

    window.removeHotelService = function(orderId) {
        
        removeService(orderId, 'hotel');
    };
    
    window.removeGuideService = function(orderId) {
        
        removeService(orderId, 'guide');
    };
    
    window.removeRestaurantService = function(orderId) {
        
        removeService(orderId, 'restaurant');
    };
    
    window.removeTransportService = function(orderId) {
        
        removeService(orderId, 'transport');
    };
    
    function removeService(orderId, serviceType) {
        console.log(`removeService called with orderId: ${orderId}, serviceType: ${serviceType}`);
        
        if (confirm(`Are you sure you want to remove this ${serviceType} service?`)) {
            showNotification(`Removing ${serviceType} service...`, 'info');
            
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                console.error('CSRF token not found');
                showNotification('CSRF token not found. Please refresh the page.', 'error');
                return;
            }
            
            console.log('CSRF Token:', csrfToken);

            const url = "{{ route('api.orders.cancel', ':orderId') }}".replace(':orderId', orderId);
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    showNotification(`${serviceType} service removed successfully`, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(`Failed to remove ${serviceType} service: ` + data.message, 'error');
                }
            })
            .catch(error => {
                console.error(`Error removing ${serviceType} service:`, error);
                showNotification(`Error removing ${serviceType} service: ${error.message}`, 'error');
            });
        }
    }
    
    // Initialize page functionality
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Edit page initialized');
        
        // Check if Bootstrap is properly loaded
        if (typeof bootstrap === 'undefined') {
            console.error('Bootstrap JS is not loaded properly!');
        } else {
            console.log('Bootstrap version:', bootstrap.Modal.VERSION);
        }
        
        // Initialize meal plans when hotel modal is shown with existing data
        document.addEventListener('shown.bs.modal', function(e) {
            if (e.target.id === 'hotelSelectionModal') {
                console.log('Hotel modal opened, checking for meal plan initialization');
                setTimeout(() => {
                    initializeMealPlansForExistingData();
                }, 100);
            }
        });
        
        // Add passenger validation for local transfer
        const passengersInput = document.getElementById('local_transfer_passengers');
        if (passengersInput) {
            passengersInput.addEventListener('input', function() {
                const passengers = parseInt(this.value) || 1;
                const maxPassengers = parseInt(this.getAttribute('max')) || 1;
                
                if (passengers > maxPassengers) {
                    this.value = maxPassengers;
                    showNotification(`Number of passengers cannot exceed ${maxPassengers} (total adults + children in tour)`, 'warning');
                }
            });
        }

        // Add passenger validation for transport
        const transportPassengersInput = document.getElementById('modal_transport_passengers');
        if (transportPassengersInput) {
            transportPassengersInput.addEventListener('input', function() {
                const passengers = parseInt(this.value) || 1;
                const maxPassengers = parseInt(this.getAttribute('max')) || 1;
                
                if (passengers > maxPassengers) {
                    this.value = maxPassengers;
                    showNotification(`Number of passengers cannot exceed ${maxPassengers} (total adults + children in tour)`, 'warning');
                }
            });
        }

        // Add passenger validation for dropoff transport
        const dropoffTransportPassengersInput = document.getElementById('modal_dropoff_transport_passengers');
        if (dropoffTransportPassengersInput) {
            dropoffTransportPassengersInput.addEventListener('input', function() {
                const passengers = parseInt(this.value) || 1;
                const maxPassengers = parseInt(this.getAttribute('max')) || 1;
                
                if (passengers > maxPassengers) {
                    this.value = maxPassengers;
                    showNotification(`Number of passengers cannot exceed ${maxPassengers} (total adults + children in tour)`, 'warning');
                }
            });
        }
    });

    function getCustomerInfo(){
            const fullName = document.getElementById('customerFullName')?.value || '';
        const email = document.getElementById('customerEmail')?.value || '';
        const phone = document.getElementById('customerPhone')?.value || '';
        const countryCode = document.getElementById('customerCountryCode')?.value || '';
        const address1 = document.getElementById('customerAddress1')?.value || '';
        const address2 = document.getElementById('customerAddress2')?.value || '';
        const state = document.getElementById('customerState')?.value || '';
        const zip = document.getElementById('customerZip')?.value || '';
        const specialRequests = document.getElementById('customerSpecialRequests')?.value || '';
        let customer_info = @json($customer_info);
        if(Object.keys(customer_info).length === 0){
            customer_info = {
                    fullName: fullName,
                email: email,
                phone: phone,
                countryCode: countryCode,
                address1: address1,
                address2: address2,
                state: state,
                zip: zip,
                specialRequests: specialRequests
            };
        }
        return customer_info;
    }

    // Google Maps Autocomplete Functionality for Local Transfer Point-to-Point
    window.initializeGoogleMapsAutocomplete = function() {
        console.log('Initializing Google Maps Autocomplete for Local Transfer Point-to-Point...');
        
        // Get selected country and city for location bias
        const selectedCountry = '{{ $tour->country ?? "" }}';
        const selectedCity = '{{ $tour->city ?? "" }}';
        
        // Create location bias for better search results
        let locationBias = null;
        if (selectedCountry && selectedCity) {
            locationBias = selectedCity + ', ' + selectedCountry;
        } else if (selectedCountry) {
            locationBias = selectedCountry;
        }
        
        // Initialize autocomplete for local transfer location inputs
        document.querySelectorAll('.google-maps-autocomplete').forEach(input => {
            if (input && !input.hasAttribute('data-autocomplete-initialized')) {
                console.log('Initializing autocomplete for:', input.id);
                
                // Create autocomplete instance
                const autocomplete = new google.maps.places.Autocomplete(input, {
                    types: ['establishment', 'geocode'],
                    componentRestrictions: { country: getCountryCode(selectedCountry) },
                    fields: ['place_id', 'geometry', 'formatted_address', 'name', 'address_components']
                });
                
                // Add place_changed event listener
                autocomplete.addListener('place_changed', function() {
                    const place = autocomplete.getPlace();
                    
                    if (!place.geometry) {
                        console.log('No geometry found for selected place');
                        return;
                    }
                    
                    console.log('Place selected:', place);
                    
                    // Extract coordinates
                    const lat = place.geometry.location.lat();
                    const lng = place.geometry.location.lng();
                    
                    // Update hidden fields based on input ID
                    const inputId = input.id;
                    
                    // Handle local transfer point-to-point fields
                    if (inputId.includes('pickup')) {
                        const latField = document.getElementById(inputId.replace('_location', '_lat'));
                        const lngField = document.getElementById(inputId.replace('_location', '_lng'));
                        const placeIdField = document.getElementById(inputId.replace('_location', '_place_id'));
                        
                        if (latField) latField.value = lat;
                        if (lngField) lngField.value = lng;
                        if (placeIdField) placeIdField.value = place.place_id;
                        
                        console.log(`Updated pickup coordinates: ${lat}, ${lng}`);
                    } else if (inputId.includes('dropoff')) {
                        const latField = document.getElementById(inputId.replace('_location', '_lat'));
                        const lngField = document.getElementById(inputId.replace('_location', '_lng'));
                        const placeIdField = document.getElementById(inputId.replace('_location', '_place_id'));
                        
                        if (latField) latField.value = lat;
                        if (lngField) lngField.value = lng;
                        if (placeIdField) placeIdField.value = place.place_id;
                        
                        console.log(`Updated dropoff coordinates: ${lat}, ${lng}`);
                    }
                    
                    // Update input value with formatted address
                    input.value = place.formatted_address || place.name || input.value;
                    
                    // Enable search button if both pickup and dropoff are filled
                    checkLocalTransferFormCompletion();
                });
                
                // Mark as initialized
                input.setAttribute('data-autocomplete-initialized', 'true');
            }
        });
    };
    
    // Helper function to get country code from country name
    window.getCountryCode = function(countryName) {
        const countryCodes = {
            'India': 'IN',
            'United States': 'US',
            'United Kingdom': 'GB',
            'Canada': 'CA',
            'Australia': 'AU',
            'Germany': 'DE',
            'France': 'FR',
            'Italy': 'IT',
            'Spain': 'ES',
            'Netherlands': 'NL',
            'Belgium': 'BE',
            'Switzerland': 'CH',
            'Austria': 'AT',
            'Sweden': 'SE',
            'Norway': 'NO',
            'Denmark': 'DK',
            'Finland': 'FI',
            'Poland': 'PL',
            'Czech Republic': 'CZ',
            'Hungary': 'HU',
            'Slovakia': 'SK',
            'Slovenia': 'SI',
            'Croatia': 'HR',
            'Serbia': 'RS',
            'Bosnia and Herzegovina': 'BA',
            'Montenegro': 'ME',
            'Albania': 'AL',
            'North Macedonia': 'MK',
            'Bulgaria': 'BG',
            'Romania': 'RO',
            'Greece': 'GR',
            'Turkey': 'TR',
            'Cyprus': 'CY',
            'Malta': 'MT',
            'Portugal': 'PT',
            'Ireland': 'IE',
            'Iceland': 'IS',
            'Luxembourg': 'LU',
            'Japan': 'JP',
            'South Korea': 'KR',
            'China': 'CN',
            'Singapore': 'SG',
            'Thailand': 'TH',
            'Malaysia': 'MY',
            'Indonesia': 'ID',
            'Philippines': 'PH',
            'Vietnam': 'VN',
            'Cambodia': 'KH',
            'Brazil': 'BR',
            'Argentina': 'AR',
            'Mexico': 'MX',
            // Add more countries as needed
        };
        
        return countryCodes[countryName] || '';
    };
    
    // Check if local transfer form is complete and enable search button
    function checkLocalTransferFormCompletion() {
        const pickupLocation = document.getElementById('local_transfer_point_pickup_location').value;
        const dropoffLocation = document.getElementById('local_transfer_point_dropoff_location').value;
        const pickupTime = document.getElementById('local_transfer_point_pickup_time').value;
        const pickupDate = document.getElementById('local_transfer_point_pickup_date').value;
        const searchBtn = document.getElementById('local_transfer_point_to_point_search_btn');
        
        if (pickupLocation && dropoffLocation && pickupTime && pickupDate && searchBtn) {
            searchBtn.disabled = false;
            searchBtn.classList.remove('btn-secondary');
            searchBtn.classList.add('btn-primary');
        } else if (searchBtn) {
            searchBtn.disabled = true;
            searchBtn.classList.remove('btn-primary');
            searchBtn.classList.add('btn-secondary');
        }
    }
    
    // Check if hourly form is complete and enable search button
    function checkHourlyFormCompletion() {
        const pickupLocation = document.getElementById('local_transfer_hourly_pickup_location').value;
        const pickupTime = document.getElementById('local_transfer_hourly_pickup_time').value;
        const pickupDate = document.getElementById('local_transfer_hourly_pickup_date').value;
        const searchBtn = document.getElementById('local_transfer_hourly_search_btn');
        
        if (pickupLocation && pickupTime && pickupDate && searchBtn) {
            searchBtn.disabled = false;
            searchBtn.classList.remove('btn-secondary');
            searchBtn.classList.add('btn-primary');
        } else if (searchBtn) {
            searchBtn.disabled = true;
            searchBtn.classList.remove('btn-primary');
            searchBtn.classList.add('btn-secondary');
        }
    }
    
    // Check if local transfer (zone-based) form is complete and enable search button
    function checkLocalTransferZoneFormCompletion() {
        const pickupZone = document.getElementById('local_transfer_pickup_zone').value;
        const dropoffZone = document.getElementById('local_transfer_dropoff_zone').value;
        const pickupTime = document.getElementById('local_transfer_pickup_time').value;
        const pickupDate = document.getElementById('local_transfer_pickup_date').value;
        const searchBtn = document.getElementById('local_transfer_search_btn');
        
        if (pickupZone && dropoffZone && pickupTime && pickupDate && searchBtn) {
            searchBtn.disabled = false;
            searchBtn.classList.remove('btn-secondary');
            searchBtn.classList.add('btn-primary');
        } else if (searchBtn) {
            searchBtn.disabled = true;
            searchBtn.classList.remove('btn-primary');
            searchBtn.classList.add('btn-secondary');
        }
    }

    
    // Google Maps Autocomplete Functionality
    window.initializeGoogleMapsAutocomplete = function() {
        console.log('Initializing Google Maps Autocomplete...');
        
        // Get selected country and city for location bias
        const selectedCountry = document.getElementById('user_country')?.value || '';
        const selectedCity = document.getElementById('city')?.value || '';
        
        // Create location bias for better search results
        let locationBias = null;
        if (selectedCountry && selectedCity) {
            // Use the city as the center point for location bias
            locationBias = selectedCity + ', ' + selectedCountry;
        } else if (selectedCountry) {
            locationBias = selectedCountry;
        }
        
        // Initialize autocomplete for all transport location inputs
        document.querySelectorAll('.google-maps-autocomplete').forEach(input => {
            if (input && !input.hasAttribute('data-autocomplete-initialized')) {
                console.log('Initializing autocomplete for:', input.id);
                
                // Create autocomplete instance
                const autocomplete = new google.maps.places.Autocomplete(input, {
                    types: ['establishment', 'geocode'],
                    componentRestrictions: { country: getCountryCode(selectedCountry) },
                    fields: ['place_id', 'geometry', 'formatted_address', 'name', 'address_components']
                });
                
                // Add place_changed event listener
                autocomplete.addListener('place_changed', function() {
                    const place = autocomplete.getPlace();
                    
                    if (!place.geometry) {
                        console.log('No geometry found for selected place');
                        return;
                    }
                    
                    console.log('Place selected:', place);
                    
                    // Extract coordinates
                    const lat = place.geometry.location.lat();
                    const lng = place.geometry.location.lng();
                    
                    // Update hidden fields based on input ID
                    const inputId = input.id;
                    
                    // Update the corresponding hidden fields
                    if (inputId.includes('pickup')) {
                        const latField = document.getElementById(inputId.replace('_location', '_lat'));
                        const lngField = document.getElementById(inputId.replace('_location', '_lng'));
                        const placeIdField = document.getElementById(inputId.replace('_location', '_place_id'));
                        
                        if (latField) latField.value = lat;
                        if (lngField) lngField.value = lng;
                        if (placeIdField) placeIdField.value = place.place_id;
                    } else if (inputId.includes('dropoff')) {
                        const latField = document.getElementById(inputId.replace('_location', '_lat'));
                        const lngField = document.getElementById(inputId.replace('_location', '_lng'));
                        const placeIdField = document.getElementById(inputId.replace('_location', '_place_id'));
                        
                        if (latField) latField.value = lat;
                        if (lngField) lngField.value = lng;
                        if (placeIdField) placeIdField.value = place.place_id;
                    }
                    
                    // Enable search button if all fields are filled
                    checkLocalTransferFormCompletion();
                    checkHourlyFormCompletion();
                    
                    console.log('Updated location:', {
                        lat: lat,
                        lng: lng,
                        placeId: place.place_id,
                        address: place.formatted_address
                    });
                });
                
                // Mark as initialized
                input.setAttribute('data-autocomplete-initialized', 'true');
            }
        });
    };
    
    // Function to reinitialize autocomplete when modal opens
    window.reinitializeAutocomplete = function() {
        console.log('Reinitializing autocomplete for local transfer modal...');
        
        // Clear existing initialization flags
        document.querySelectorAll('.google-maps-autocomplete').forEach(input => {
            input.removeAttribute('data-autocomplete-initialized');
        });
        
        // Reinitialize after a short delay
        setTimeout(() => {
            initializeGoogleMapsAutocomplete();
        }, 500);
    };
    
    // Initialize autocomplete when page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Wait for Google Maps API to load
        if (typeof google !== 'undefined' && google.maps && google.maps.places) {
            initializeGoogleMapsAutocomplete();
        } else {
            // Wait for Google Maps API to load
            window.addEventListener('load', function() {
                if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                    initializeGoogleMapsAutocomplete();
                }
            });
        }
    });

    // Reinitialize when local transfer modal opens
    document.addEventListener('shown.bs.modal', function(e) {
        if (e.target.id === 'localTransferSelectionModal') {
            setTimeout(() => {
                initializeGoogleMapsAutocomplete();
            }, 100);
        }
    });

    // Initialize guide pickup time dropdowns
    function initializeGuidePickupTimeDropdowns() {
        // Find all guide name select elements
        const guideNameSelects = document.querySelectorAll('select[id^="guide_name_"]');
        
        guideNameSelects.forEach(guideSelect => {
            // Extract booking ID from the select ID
            const bookingId = guideSelect.id.replace('guide_name_', '');
            
            // Add change event listener (use once to avoid duplicates)
            guideSelect.addEventListener('change', function() {
                populateGuidePickupTimes(this, bookingId);
            });
            
            // If a guide is already selected, populate times on load
            if (guideSelect.value) {
                // Use setTimeout to ensure DOM is ready
                setTimeout(() => {
                    populateGuidePickupTimes(guideSelect, bookingId);
                }, 100);
            }
        });
    }

    // Add event listeners to form fields for completion check
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize guide pickup time dropdowns
        initializeGuidePickupTimeDropdowns();
        
        // Point to Point fields
        const pickupTimeField = document.getElementById('local_transfer_point_pickup_time');
        const pickupDateField = document.getElementById('local_transfer_point_pickup_date');
        const pickupLocationField = document.getElementById('local_transfer_point_pickup_location');
        const dropoffLocationField = document.getElementById('local_transfer_point_dropoff_location');
        
        if (pickupTimeField) {
            pickupTimeField.addEventListener('change', checkLocalTransferFormCompletion);
        }
        if (pickupDateField) {
            pickupDateField.addEventListener('change', checkLocalTransferFormCompletion);
        }
        if (pickupLocationField) {
            pickupLocationField.addEventListener('input', checkLocalTransferFormCompletion);
        }
        if (dropoffLocationField) {
            dropoffLocationField.addEventListener('input', checkLocalTransferFormCompletion);
        }
        
        // Hourly fields
        const hourlyPickupTimeField = document.getElementById('local_transfer_hourly_pickup_time');
        const hourlyPickupDateField = document.getElementById('local_transfer_hourly_pickup_date');
        const hourlyPickupLocationField = document.getElementById('local_transfer_hourly_pickup_location');
        const hourlyHoursField = document.getElementById('local_transfer_hourly_hours');
        
        if (hourlyPickupTimeField) {
            hourlyPickupTimeField.addEventListener('change', checkHourlyFormCompletion);
        }
        if (hourlyPickupDateField) {
            hourlyPickupDateField.addEventListener('change', checkHourlyFormCompletion);
        }
        if (hourlyPickupLocationField) {
            hourlyPickupLocationField.addEventListener('input', checkHourlyFormCompletion);
        }
        if (hourlyHoursField) {
            hourlyHoursField.addEventListener('change', function() {
                checkHourlyFormCompletion();
                updateLocalTransferPricing(); // Update pricing when hours change
            });
        }
        
        // Local Transfer (zone-based) fields
        const localTransferPickupZoneField = document.getElementById('local_transfer_pickup_zone');
        const localTransferDropoffZoneField = document.getElementById('local_transfer_dropoff_zone');
        const localTransferPickupTimeField = document.getElementById('local_transfer_pickup_time');
        const localTransferPickupDateField = document.getElementById('local_transfer_pickup_date');
        
        if (localTransferPickupZoneField) {
            // Already has change listener in initializeLocalTransferModal, but add form completion check
            localTransferPickupZoneField.addEventListener('change', checkLocalTransferZoneFormCompletion);
        }
        if (localTransferDropoffZoneField) {
            localTransferDropoffZoneField.addEventListener('change', checkLocalTransferZoneFormCompletion);
        }
        if (localTransferPickupTimeField) {
            localTransferPickupTimeField.addEventListener('change', checkLocalTransferZoneFormCompletion);
        }
        if (localTransferPickupDateField) {
            localTransferPickupDateField.addEventListener('change', checkLocalTransferZoneFormCompletion);
        }
    });

    async function updateExistingHotel(event, bookingId) {
        event.preventDefault();
        const button = event.target.closest('button');
        const formDiv = button.closest('.hotel-edit-form');
        const url = formDiv.dataset.updateUrl;
        const feedback = document.getElementById(`hotel_feedback_${bookingId}`);
        const spinner = document.getElementById(`hotel_spinner_${bookingId}`);
        const submitButton = button;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Get form values
        const checkInDate = formDiv.querySelector('input[name="check_in_date"]')?.value || '';
        const checkOutDate = formDiv.querySelector('input[name="check_out_date"]')?.value || '';
        const roomType = formDiv.querySelector('select[name="room_type"]')?.value || '';
        const bedType = formDiv.querySelector('select[name="bed_type"]')?.value || '';
        const mealPlan = formDiv.querySelector('select[name="meal_plan"]')?.value || '';
        const numberOfRooms = parseInt(formDiv.querySelector('input[name="number_of_rooms"]')?.value || '1');
        const numberOfPersons = parseInt(formDiv.querySelector('input[name="number_of_persons"]')?.value || '1');
        const hotelId = formDiv.querySelector('input[name="hotel_id"]')?.value || '';
        const originalRoomsJson = formDiv.querySelector('input[name="original_rooms_json"]')?.value || '[]';
        
        // Try to parse original rooms to preserve structure
        let originalRooms = [];
        try {
            originalRooms = JSON.parse(originalRoomsJson);
        } catch (e) {
            console.warn('Could not parse original rooms JSON:', e);
        }

        // Construct rooms_json in the correct format
        let roomsJson = '[]';
        if (checkInDate && checkOutDate && roomType && bedType && mealPlan) {
            const checkIn = new Date(checkInDate);
            const checkOut = new Date(checkOutDate);
            const numberOfNights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
            
            // Get room_id and bed_id from original rooms if available
            let roomId = 0;
            let bedId = 0;
            if (originalRooms.length > 0 && originalRooms[0]) {
                roomId = originalRooms[0].room_id || 0;
                if (originalRooms[0].beds && originalRooms[0].beds.length > 0) {
                    bedId = originalRooms[0].beds[0].bed_id || 0;
                }
            }
            
            // Get bed data from select option if available
            const bedTypeSelect = formDiv.querySelector('select[name="bed_type"]');
            if (bedTypeSelect && bedTypeSelect.selectedIndex >= 0) {
                const selectedOption = bedTypeSelect.options[bedTypeSelect.selectedIndex];
                if (selectedOption && selectedOption.dataset.bed) {
                    try {
                        const bedData = JSON.parse(selectedOption.dataset.bed);
                        if (bedData.bed_id) bedId = bedData.bed_id;
                    } catch (e) {
                        console.warn('Could not parse bed data:', e);
                    }
                }
            }
            
            // Construct selectedMeals object for each night
            const selectedMeals = {};
            for (let i = 1; i <= numberOfNights; i++) {
                // Try to preserve price from original selectedMeals if available
                let mealPrice = 0;
                if (originalRooms.length > 0 && originalRooms[0].beds && originalRooms[0].beds[0]) {
                    const originalSelectedMeals = originalRooms[0].beds[0].selectedMeals || {};
                    const originalMeal = originalSelectedMeals[`meal_${i}`];
                    if (originalMeal && originalMeal.type === mealPlan) {
                        mealPrice = originalMeal.price || 0;
                    }
                }
                
                selectedMeals[`meal_${i}`] = {
                    type: mealPlan,
                    price: mealPrice
                };
            }
            
            // Construct mealTypes array
            const mealTypes = [mealPlan];
            
            // Build room structure
            const roomStructure = {
                room_id: roomId,
                room_type: roomType,
                beds: [{
                    bed_id: bedId,
                    bed_type: bedType,
                    max_occupancy: numberOfPersons,
                    mealTypes: mealTypes,
                    selectedMeals: selectedMeals,
                    head_count: numberOfPersons,
                    price: 0, // Will be calculated server-side if needed
                    room_type: roomType
                }]
            };
            
            // Create array of rooms (duplicate if multiple rooms)
            const rooms = [];
            for (let i = 0; i < numberOfRooms; i++) {
                rooms.push(JSON.parse(JSON.stringify(roomStructure))); // Deep copy
            }
            
            roomsJson = JSON.stringify(rooms);
        } else if (originalRooms.length > 0) {
            // If form is incomplete, preserve original rooms
            roomsJson = originalRoomsJson;
        }

        // Create FormData from all inputs in the hotel form div
        const formData = new FormData();
        const inputs = formDiv.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (input.type === 'checkbox' || input.type === 'radio') {
                if (input.checked) {
                    formData.append(input.name, input.value);
                }
            } else if (input.name !== 'original_rooms_json') {
                formData.append(input.name, input.value);
            }
        });
        
        // Explicitly ensure total_price is captured with its current value
        const totalPriceInput = formDiv.querySelector('input[name="total_price"]');
        if (totalPriceInput) {
            const priceValue = totalPriceInput.value || '0';
            formData.set('total_price', priceValue);
        }
        
        // Explicitly ensure number_of_rooms is captured with its current value
        const numberOfRoomsInput = formDiv.querySelector('input[name="number_of_rooms"]');
        if (numberOfRoomsInput) {
            const roomsValue = numberOfRoomsInput.value || '1';
            formData.set('number_of_rooms', roomsValue);
        }
        
        // Add rooms_json to form data
        formData.append('rooms_json', roomsJson);

        feedback.textContent = '';
        feedback.classList.remove('text-success', 'text-danger');

        try {
            submitButton.disabled = true;
            spinner?.classList.remove('d-none');

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                const errorMessage = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Unable to save changes right now.');
                throw new Error(errorMessage);
            }

            feedback.textContent = data.message || 'Hotel service updated successfully.';
            feedback.classList.add('text-success');
            
            // Show success toastr notification
            if (typeof showToastr !== 'undefined') {
                showToastr('success', data.message || 'Hotel service updated successfully.');
            }
        } catch (error) {
            const errorMessage = error.message || 'Failed to update hotel service. Please try again.';
            feedback.textContent = errorMessage;
            feedback.classList.add('text-danger');
            
            // Show error toastr notification
            if (typeof showToastr !== 'undefined') {
                showToastr('error', errorMessage);
            }
        } finally {
            submitButton.disabled = false;
            spinner?.classList.add('d-none');
        }
    }

    async function updateExistingTransport(event, bookingId) {
        event.preventDefault();
        const form = event.target;
        const formType = form.dataset.formType || form.querySelector('input[name="type"]').value;
        const url = form.dataset.updateUrl;
        const feedback = document.getElementById(`transport_feedback_${bookingId}_${formType}`);
        const spinner = document.getElementById(`transport_spinner_${bookingId}_${formType}`);
        const submitButton = form.querySelector('button[type="submit"]');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const formData = new FormData(form);
        formData.set('type', formType);

        feedback.textContent = '';
        feedback.classList.remove('text-success', 'text-danger');

        try {
            submitButton.disabled = true;
            spinner?.classList.remove('d-none');

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                const errorMessage = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Unable to save changes right now.');
                throw new Error(errorMessage);
            }

            feedback.textContent = data.message || 'Transport service updated successfully.';
            feedback.classList.add('text-success');
            
            // Show success toastr notification
            if (typeof showToastr !== 'undefined') {
                showToastr('success', data.message || 'Transport service updated successfully.');
            }
        } catch (error) {
            const errorMessage = error.message || 'Failed to update transport service. Please try again.';
            feedback.textContent = errorMessage;
            feedback.classList.add('text-danger');
            
            // Show error toastr notification
            if (typeof showToastr !== 'undefined') {
                showToastr('error', errorMessage);
            }
        } finally {
            submitButton.disabled = false;
            spinner?.classList.add('d-none');
        }
    }

    // Function to populate pickup times from guide data
    function populateGuidePickupTimes(guideSelect, bookingId) {
        const pickupTimeSelect = document.getElementById(`guide_pickup_time_${bookingId}`);
        if (!pickupTimeSelect) return;
        
        const selectedOption = guideSelect.options[guideSelect.selectedIndex];
        // Get current value from the select or from the first option that has a value
        let currentValue = pickupTimeSelect.value;
        if (!currentValue && pickupTimeSelect.options.length > 1) {
            // Check if there's a selected option with a value
            for (let i = 0; i < pickupTimeSelect.options.length; i++) {
                if (pickupTimeSelect.options[i].selected && pickupTimeSelect.options[i].value) {
                    currentValue = pickupTimeSelect.options[i].value;
                    break;
                }
            }
        }
        
        // Clear existing options
        pickupTimeSelect.innerHTML = '<option value="">Select Pickup Time</option>';
        
        if (!selectedOption || !selectedOption.value) {
            return;
        }
        
        // Get guide data from data attribute
        const guideDataAttr = selectedOption.getAttribute('data-guide-data');
        if (!guideDataAttr) {
            // If no guide data, use default time slots
            const defaultTimes = generateDefaultTimeSlots();
            
            // Convert current value to AM/PM if needed
            let convertedCurrentValue = currentValue;
            if (currentValue && !currentValue.match(/\d{1,2}:\d{2}\s*(AM|PM)/i)) {
                convertedCurrentValue = convertToAMPM(currentValue);
            }
            
            defaultTimes.forEach(time => {
                const option = document.createElement('option');
                option.value = time;
                option.textContent = time;
                if (time === convertedCurrentValue) option.selected = true;
                pickupTimeSelect.appendChild(option);
            });
            return;
        }
        
        try {
            const guideData = JSON.parse(guideDataAttr);
            let availableTimes = [];
            
            // Check if guide has available_times field (could be array or JSON string)
            if (guideData.available_times) {
                if (Array.isArray(guideData.available_times)) {
                    availableTimes = guideData.available_times;
                } else if (typeof guideData.available_times === 'string') {
                    try {
                        availableTimes = JSON.parse(guideData.available_times);
                    } catch (e) {
                        // If not JSON, treat as comma-separated string
                        availableTimes = guideData.available_times.split(',').map(t => t.trim());
                    }
                }
            }
            
            // If no available_times, check for other time-related fields or use defaults
            if (availableTimes.length === 0) {
                // Check for night_start_time and night_end_time to generate range
                if (guideData.night_start_time && guideData.night_end_time) {
                    availableTimes = generateTimeSlotsFromRange(guideData.night_start_time, guideData.night_end_time);
                } else {
                    // Use default time slots (every hour from 08:00 to 20:00)
                    availableTimes = generateDefaultTimeSlots();
                }
            }
            
            // Convert times to AM/PM format if they're in 24-hour format
            const convertedTimes = availableTimes.map(time => {
                // Check if time is already in AM/PM format
                if (time.match(/\d{1,2}:\d{2}\s*(AM|PM)/i)) {
                    return time;
                }
                // Convert from 24-hour to AM/PM
                return convertToAMPM(time);
            });
            
            // Convert current value to AM/PM if needed
            let convertedCurrentValue = currentValue;
            if (currentValue && !currentValue.match(/\d{1,2}:\d{2}\s*(AM|PM)/i)) {
                convertedCurrentValue = convertToAMPM(currentValue);
            }
            
            // Populate the select box
            convertedTimes.forEach(time => {
                const option = document.createElement('option');
                option.value = time;
                option.textContent = time;
                if (time === convertedCurrentValue) option.selected = true;
                pickupTimeSelect.appendChild(option);
            });
            
            // If current value is not in the list, add it
            if (convertedCurrentValue && !convertedTimes.includes(convertedCurrentValue)) {
                const option = document.createElement('option');
                option.value = convertedCurrentValue;
                option.textContent = convertedCurrentValue;
                option.selected = true;
                pickupTimeSelect.appendChild(option);
            }
        } catch (error) {
            console.error('Error parsing guide data:', error);
            // Fallback to default time slots
            const defaultTimes = generateDefaultTimeSlots();
            
            // Convert current value to AM/PM if needed
            let convertedCurrentValue = currentValue;
            if (currentValue && !currentValue.match(/\d{1,2}:\d{2}\s*(AM|PM)/i)) {
                convertedCurrentValue = convertToAMPM(currentValue);
            }
            
            defaultTimes.forEach(time => {
                const option = document.createElement('option');
                option.value = time;
                option.textContent = time;
                if (time === convertedCurrentValue) option.selected = true;
                pickupTimeSelect.appendChild(option);
            });
        }
    }
    
    // Convert 24-hour time to AM/PM format
    function convertToAMPM(time24) {
        if (!time24) return '';
        try {
            const parts = time24.toString().split(':');
            if (parts.length >= 2) {
                let hours = parseInt(parts[0], 10);
                const minutes = parseInt(parts[1], 10);
                if (isNaN(hours) || isNaN(minutes)) return time24;
                
                const period = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12; // 0 should be 12
                const minutesStr = minutes.toString().padStart(2, '0');
                return `${hours}:${minutesStr} ${period}`;
            }
        } catch (e) {
            console.error('Error converting time to AM/PM:', e);
        }
        return time24;
    }
    
    // Convert AM/PM time to 24-hour format
    function convertTo24Hour(timeAMPM) {
        if (!timeAMPM) return '';
        try {
            const timeStr = timeAMPM.toString().trim();
            const match = timeStr.match(/(\d{1,2}):(\d{2})\s*(AM|PM)/i);
            if (match) {
                let hours = parseInt(match[1], 10);
                const minutes = parseInt(match[2], 10);
                const period = match[3].toUpperCase();
                
                if (period === 'PM' && hours !== 12) {
                    hours += 12;
                } else if (period === 'AM' && hours === 12) {
                    hours = 0;
                }
                
                return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
            }
        } catch (e) {
            console.error('Error converting time to 24-hour:', e);
        }
        return timeAMPM;
    }
    
    // Generate default time slots (08:00 AM to 08:00 PM, every hour) in AM/PM format
    function generateDefaultTimeSlots() {
        const times = [];
        for (let hour = 8; hour <= 20; hour++) {
            const period = hour >= 12 ? 'PM' : 'AM';
            let displayHour = hour % 12;
            displayHour = displayHour ? displayHour : 12; // 0 should be 12
            const timeStr = `${displayHour}:00 ${period}`;
            times.push(timeStr);
        }
        return times;
    }
    
    // Generate time slots from a time range in AM/PM format
    function generateTimeSlotsFromRange(startTime, endTime) {
        const times = [];
        try {
            const start = parseTime(startTime);
            const end = parseTime(endTime);
            
            if (start && end) {
                let current = start;
                while (current <= end) {
                    const hours = Math.floor(current / 60);
                    const minutes = current % 60;
                    const period = hours >= 12 ? 'PM' : 'AM';
                    let displayHour = hours % 12;
                    displayHour = displayHour ? displayHour : 12; // 0 should be 12
                    const timeStr = `${displayHour}:${minutes.toString().padStart(2, '0')} ${period}`;
                    times.push(timeStr);
                    current += 60; // Add 1 hour
                }
            }
        } catch (e) {
            console.error('Error generating time slots from range:', e);
        }
        
        return times.length > 0 ? times : generateDefaultTimeSlots();
    }
    
    // Parse time string (HH:MM or HH:MM:SS or hh:mm AM/PM) to minutes
    function parseTime(timeStr) {
        if (!timeStr) return null;
        
        // Try to parse AM/PM format first
        const ampmMatch = timeStr.toString().match(/(\d{1,2}):(\d{2})\s*(AM|PM)/i);
        if (ampmMatch) {
            let hours = parseInt(ampmMatch[1], 10);
            const minutes = parseInt(ampmMatch[2], 10);
            const period = ampmMatch[3].toUpperCase();
            
            if (period === 'PM' && hours !== 12) {
                hours += 12;
            } else if (period === 'AM' && hours === 12) {
                hours = 0;
            }
            
            if (!isNaN(hours) && !isNaN(minutes)) {
                return hours * 60 + minutes;
            }
        }
        
        // Fallback to 24-hour format
        const parts = timeStr.toString().split(':');
        if (parts.length >= 2) {
            const hours = parseInt(parts[0], 10);
            const minutes = parseInt(parts[1], 10);
            if (!isNaN(hours) && !isNaN(minutes)) {
                return hours * 60 + minutes;
            }
        }
        return null;
    }

    async function updateExistingGuide(event, bookingId) {
        event.preventDefault();
        const form = event.target;
        const url = form.dataset.updateUrl;
        const feedback = document.getElementById(`guide_feedback_${bookingId}`);
        const spinner = document.getElementById(`guide_spinner_${bookingId}`);
        const submitButton = form.querySelector('button[type="submit"]');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const formData = new FormData(form);

        feedback.textContent = '';
        feedback.classList.remove('text-success', 'text-danger');

        try {
            submitButton.disabled = true;
            spinner?.classList.remove('d-none');

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                const errorMessage = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Unable to save changes right now.');
                throw new Error(errorMessage);
            }

            feedback.textContent = data.message || 'Guide service updated successfully.';
            feedback.classList.add('text-success');
            
            // Show success toastr notification
            if (typeof showToastr !== 'undefined') {
                showToastr('success', data.message || 'Guide service updated successfully.');
            }
        } catch (error) {
            const errorMessage = error.message || 'Failed to update guide service. Please try again.';
            feedback.textContent = errorMessage;
            feedback.classList.add('text-danger');
            
            // Show error toastr notification
            if (typeof showToastr !== 'undefined') {
                showToastr('error', errorMessage);
            }
        } finally {
            submitButton.disabled = false;
            spinner?.classList.add('d-none');
        }
    }

    // Functions to load meals and dish types for restaurant edit form
    function loadRestaurantMealsForEdit(bookingId) {
        const restaurantSelect = document.getElementById(`restaurant_name_${bookingId}`);
        const mealTypeSelect = document.getElementById(`meal_type_${bookingId}`);
        const dishTypeSelect = document.getElementById(`meal_specific_type_${bookingId}`);
        
        if (!restaurantSelect || !mealTypeSelect) {
            console.error('Restaurant or meal type select not found for booking:', bookingId);
            return;
        }
        
        // Get current values to preserve them
        const currentMealType = document.getElementById(`current_meal_type_${bookingId}`)?.value || '';
        const currentDishType = document.getElementById(`current_dish_type_${bookingId}`)?.value || '';
        
        // Clear dependent dropdowns
        mealTypeSelect.innerHTML = '<option value="">Select Restaurant First</option>';
        dishTypeSelect.innerHTML = '<option value="">Select Meal Type First</option>';
        
        const selectedOption = restaurantSelect.options[restaurantSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            return;
        }
        
        // Try to get restaurant data from data attribute
        const restaurantDataStr = selectedOption.getAttribute('data-restaurant-data');
        const restaurantId = selectedOption.getAttribute('data-restaurant-id');
        
        if (restaurantDataStr) {
            try {
                const restaurantData = JSON.parse(restaurantDataStr);
                
                // If restaurant has meals in data, use them directly
                if (restaurantData.meals && Array.isArray(restaurantData.meals) && restaurantData.meals.length > 0) {
                    populateMealTypesForEdit(mealTypeSelect, restaurantData.meals, currentMealType);
                    return;
                }
            } catch (error) {
                console.error('Error parsing restaurant data:', error);
            }
        }
        
        // Try to fetch meals from API
        if (restaurantId) {
            fetchMealsForRestaurantEdit(restaurantId, mealTypeSelect, bookingId, currentMealType, currentDishType);
        }
    }
    
    function populateMealTypesForEdit(mealTypeSelect, meals, currentMealType = '') {
        mealTypeSelect.innerHTML = '<option value="">Select Meal Type</option>';
        
        meals.forEach(meal => {
            const mealPeriod = meal.meal_period == 1 ? 'Breakfast' : (meal.meal_period == 2 ? 'Lunch' : (meal.meal_period == 3 ? 'Dinner' : ''));
            const mealTypeText = meal.type == 1 ? 'Buffet' : (meal.type == 2 ? 'Set Menu' : (meal.type == 3 ? 'A-La-Carte' : ''));
            const mealCategory = meal.category == 1 ? 'Alcoholic' : (meal.category == 2 ? 'Non Alcoholic' : 'No Beverage');
            const mealItemType = meal.item_type == 1 ? 'Vegetarian' : (meal.item_type == 2 ? 'Non Vegetarian' : '');
            const mealName = mealPeriod + ' - ' + mealTypeText + ' - ' + mealCategory + ' - ' + mealItemType;
            
            // Store meal period as value (matching backend expectation for meal_type)
            // Backend expects text like "Lunch", "Dinner", etc.
            const mealValue = mealPeriod;
            
            // Check if this matches the current meal type
            const isSelected = currentMealType && (
                mealName.includes(currentMealType) || 
                currentMealType.includes(mealPeriod) ||
                currentMealType.includes(mealTypeText) ||
                mealPeriod === currentMealType
            );
            
            const mealOption = document.createElement('option');
            mealOption.value = mealValue;
            mealOption.textContent = mealName;
            mealOption.setAttribute('data-meal', JSON.stringify(meal));
            mealOption.setAttribute('data-meal-id', meal.meal_id);
            if (isSelected) {
                mealOption.selected = true;
            }
            mealTypeSelect.appendChild(mealOption);
        });
        
        // If a meal was selected, trigger dish type loading
        if (mealTypeSelect.value) {
            const bookingId = mealTypeSelect.id.replace('meal_type_', '');
            loadDishTypesForEdit(bookingId);
        }
    }
    
    async function fetchMealsForRestaurantEdit(restaurantId, mealTypeSelect, bookingId = null, currentMealType = '', currentDishType = '') {
        try {
            const dmcId = document.getElementById('dmc_id')?.value;
            if (!dmcId) {
                console.error('DMC ID not found');
                return;
            }
            
            const response = await fetch(`{{ route('api.restaurant.details') }}?restaurantId=${restaurantId}&dmc_id=${dmcId}`);
            const data = await response.json();
            
            if (data.success && data.meals && Array.isArray(data.meals)) {
                // Convert API format to expected format
                const meals = data.meals.map(meal => ({
                    meal_id: meal.meal_id,
                    type: meal.type === 'Buffet' ? 1 : (meal.type === 'Set Menu' ? 2 : (meal.type === 'A la carte' || meal.type === 'A-La-Carte' ? 3 : null)),
                    meal_period: meal.meal_period === 'Breakfast' ? 1 : (meal.meal_period === 'Lunch' ? 2 : (meal.meal_period === 'Dinner' ? 3 : null)),
                    category: meal.category === 'Alcoholic' ? 1 : (meal.category === 'Non Alcoholic' ? 2 : null),
                    item_type: meal.item_type === 'Veg' ? 1 : (meal.item_type === 'Non Veg' ? 2 : null),
                    adult_price: meal.adult_price,
                    child_price: meal.child_price
                }));
                populateMealTypesForEdit(mealTypeSelect, meals, currentMealType);
            }
        } catch (error) {
            console.error('Error fetching meals for restaurant:', error);
        }
    }
    
    function loadDishTypesForEdit(bookingId) {
        const mealTypeSelect = document.getElementById(`meal_type_${bookingId}`);
        const dishTypeSelect = document.getElementById(`meal_specific_type_${bookingId}`);
        const currentDishType = document.getElementById(`current_dish_type_${bookingId}`)?.value || '';
        
        if (!mealTypeSelect || !dishTypeSelect) {
            console.error('Meal type or dish type select not found for booking:', bookingId);
            return;
        }
        
        dishTypeSelect.innerHTML = '<option value="">Select Meal Type First</option>';
        
        const selectedOption = mealTypeSelect.options[mealTypeSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            return;
        }
        
        const mealDataStr = selectedOption.getAttribute('data-meal');
        if (!mealDataStr) {
            // Try to match by meal name or ID
            const mealText = selectedOption.textContent;
            const dishTypeMatch = mealText.match(/(Buffet|Set Menu|A-La-Carte)/);
            if (dishTypeMatch) {
                const dishOption = document.createElement('option');
                dishOption.value = dishTypeMatch[1];
                dishOption.textContent = dishTypeMatch[1];
                if (currentDishType && (dishTypeMatch[1] === currentDishType || currentDishType.includes(dishTypeMatch[1]))) {
                    dishOption.selected = true;
                }
                dishTypeSelect.appendChild(dishOption);
                if (!dishTypeSelect.value && currentDishType) {
                    dishTypeSelect.value = dishTypeMatch[1];
                }
            }
            return;
        }
        
        try {
            const mealData = JSON.parse(mealDataStr);
            const dishTypeText = mealData.type == 1 ? 'Buffet' : (mealData.type == 2 ? 'Set Menu' : (mealData.type == 3 ? 'A-La-Carte' : ''));
            
            const dishOption = document.createElement('option');
            dishOption.value = dishTypeText;
            dishOption.textContent = dishTypeText;
            dishOption.setAttribute('data-dish', mealDataStr);
            
            // Select if it matches current dish type
            if (currentDishType && (dishTypeText === currentDishType || currentDishType.includes(dishTypeText))) {
                dishOption.selected = true;
            }
            
            dishTypeSelect.appendChild(dishOption);
            
            // Set value if not already selected
            if (!dishTypeSelect.value && currentDishType) {
                dishTypeSelect.value = dishTypeText;
            } else if (!dishTypeSelect.value) {
                dishTypeSelect.value = dishTypeText;
            }
        } catch (error) {
            console.error('Error parsing meal data:', error);
        }
    }
    
    // Initialize restaurant forms on page load
    function initializeRestaurantEditForms() {
        // Find all restaurant edit forms
        const restaurantSelects = document.querySelectorAll('select[id^="restaurant_name_"]');
        restaurantSelects.forEach(restaurantSelect => {
            const bookingId = restaurantSelect.id.replace('restaurant_name_', '');
            
            // If a restaurant is already selected, load its meals
            if (restaurantSelect.value) {
                // Small delay to ensure DOM is ready
                setTimeout(() => {
                    loadRestaurantMealsForEdit(bookingId);
                }, 100);
            }
        });
    }
    
    const fetchVehiclesByZonesUrl = "{{ route('fetch-vehicles-by-zones') }}";
    const userDmcZoneStatus = {{ (int) ($UserDmc->zone_on ?? 0) }};
    const defaultLocalTransferCity = @json($tour->city ?? $tour->destination ?? '');
    
    function initializeLocalTransportEditForms() {
        const localTransportForms = document.querySelectorAll('.transport-edit-form[data-form-type="local_transport"]');
        localTransportForms.forEach(form => setupLocalTransportEditForm(form));
    }
    
    function syncLocalTransportLocation(select, type) {
        if (!select) return;
        const bookingId = select.dataset.bookingId;
        if (!bookingId) return;
        const selectedOption = select.options[select.selectedIndex];
        const referenceInput = document.getElementById(`${type}_location_reference_${bookingId}`);
        const typeInput = document.getElementById(`${type}_location_type_${bookingId}`);
        if (referenceInput) {
            referenceInput.value = selectedOption ? (selectedOption.getAttribute('data-location-id') || '') : '';
        }
        if (typeInput) {
            typeInput.value = selectedOption ? (selectedOption.getAttribute('data-location-type') || '') : '';
        }
    }
    
    function syncLocalTransportVehicle(select) {
        if (!select) return;
        const bookingId = select.dataset.bookingId;
        if (!bookingId) return;
        const selectedOption = select.options[select.selectedIndex];
        const vehicleIdInput = document.getElementById(`vehicle_id_${bookingId}`);
        if (vehicleIdInput) {
            vehicleIdInput.value = selectedOption ? (selectedOption.getAttribute('data-vehicle-id') || '') : '';
        }
    }
    
    function setupLocalTransportEditForm(form) {
        if (!form || form.dataset.localEditInitialized === '1') return;
        form.dataset.localEditInitialized = '1';
        
        const pickupSelect = form.querySelector('.js-local-pickup-select');
        const dropoffSelect = form.querySelector('.js-local-dropoff-select');
        const vehicleSelect = form.querySelector('.js-local-vehicle-select');
        const serviceTypeSelect = form.querySelector('.js-local-service-type');
        
        const triggerVehicleRefresh = (immediate = false) => {
            scheduleLocalTransportVehicleRefresh(form, { immediate });
        };
        
        if (pickupSelect) {
            pickupSelect.addEventListener('change', () => {
                syncLocalTransportLocation(pickupSelect, 'pickup');
                triggerVehicleRefresh();
            });
            syncLocalTransportLocation(pickupSelect, 'pickup');
        }
        
        if (dropoffSelect) {
            dropoffSelect.addEventListener('change', () => {
                syncLocalTransportLocation(dropoffSelect, 'dropoff');
                triggerVehicleRefresh();
            });
            syncLocalTransportLocation(dropoffSelect, 'dropoff');
        }
        
        if (vehicleSelect) {
            vehicleSelect.addEventListener('change', () => {
                syncLocalTransportVehicle(vehicleSelect);
                updateLocalTransportEditServiceTypeOptions(form);
                updateLocalTransportEditPrice(form);
            });
            syncLocalTransportVehicle(vehicleSelect);
        }
        
        if (serviceTypeSelect) {
            serviceTypeSelect.addEventListener('change', () => {
                updateLocalTransportEditPrice(form);
            });
        }
        
        triggerVehicleRefresh(true);
    }
    
    function scheduleLocalTransportVehicleRefresh(form, options = {}) {
        if (!form) return;
        const delay = options.immediate ? 0 : 200;
        if (form._localVehicleRefreshTimeout) {
            clearTimeout(form._localVehicleRefreshTimeout);
        }
        form._localVehicleRefreshTimeout = setTimeout(() => {
            refreshLocalTransportVehicleOptions(form);
        }, delay);
    }
    
    async function refreshLocalTransportVehicleOptions(form) {
        if (!form || form.dataset.loadingVehicles === '1') return;
        
        const bookingId = form.dataset.bookingId;
        if (!bookingId) return;
        
        const pickupReferenceInput = document.getElementById(`pickup_location_reference_${bookingId}`);
        const dropoffReferenceInput = document.getElementById(`dropoff_location_reference_${bookingId}`);
        const pickupTypeInput = document.getElementById(`pickup_location_type_${bookingId}`);
        const dropoffTypeInput = document.getElementById(`dropoff_location_type_${bookingId}`);
        const vehicleSelect = form.querySelector('.js-local-vehicle-select');
        
        if (!vehicleSelect) return;
        
        const pickupReference = pickupReferenceInput?.value;
        const dropoffReference = dropoffReferenceInput?.value;
        
        if (!pickupReference || !dropoffReference) {
            vehicleSelect.innerHTML = '<option value="">Select pickup & dropoff first</option>';
            vehicleSelect.disabled = true;
            return;
        }
        
        if (!fetchVehiclesByZonesUrl) {
            console.warn('Fetch vehicles URL not configured');
            return;
        }
        
        const payload = {
            from_zone_id: pickupReference,
            to_zone_id: dropoffReference,
            from_zone_type: pickupTypeInput?.value || 'zone',
            to_zone_type: dropoffTypeInput?.value || 'zone',
            city: form.dataset.city || defaultLocalTransferCity || '',
            zone_status: parseInt(form.dataset.zoneStatus || userDmcZoneStatus || 0, 10)
        };
        
        form.dataset.loadingVehicles = '1';
        vehicleSelect.disabled = true;
        
        try {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
            const response = await fetch(fetchVehiclesByZonesUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {})
                },
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (data.success && Array.isArray(data.vehicles) && data.vehicles.length > 0) {
                populateLocalTransportVehicleSelect(form, data.vehicles, true);
            } else {
                populateLocalTransportVehicleSelect(form, [], true);
            }
        } catch (error) {
            console.error('Error fetching vehicles for local transport edit form:', error);
            populateLocalTransportVehicleSelect(form, [], true);
        } finally {
            delete form.dataset.loadingVehicles;
        }
    }
    
    function populateLocalTransportVehicleSelect(form, vehicles, preserveSelection = true) {
        const vehicleSelect = form.querySelector('.js-local-vehicle-select');
        if (!vehicleSelect) return;
        const bookingId = form.dataset.bookingId;
        const vehicleIdInput = document.getElementById(`vehicle_id_${bookingId}`);
        const currentVehicleId = vehicleIdInput?.value || form.dataset.initialVehicleId || '';
        const currentVehicleName = form.dataset.initialVehicleName || '';
        
        vehicleSelect.innerHTML = '<option value="">' + (vehicles.length ? 'Select vehicle' : 'No vehicles available for selected zones') + '</option>';
        
        let matchedSelection = false;
        
        vehicles.forEach(vehicle => {
            const option = document.createElement('option');
            option.value = vehicle.vehicle_name || vehicle.vehicle_id;
            option.textContent = formatVehicleOptionLabel(vehicle);
            option.setAttribute('data-vehicle-id', vehicle.vehicle_id || '');
            option.setAttribute('data-vehicle-name', vehicle.vehicle_name || '');
            option.setAttribute('data-vehicle-type', vehicle.vehicle_type || '');
            option.setAttribute('data-seating-capacity', vehicle.seating_capacity || '');
            option.setAttribute('data-private-price', vehicle.private_price || '');
            option.setAttribute('data-shared-price', vehicle.shared_price || '');
            option.setAttribute('data-cost-per-hour', vehicle.cost_per_hour || '');
            option.setAttribute('data-sharable-cost-per-hour', vehicle.sharable_cost_per_hour || '');
            option.setAttribute('data-sharable', vehicle.sharable || '');
            option.setAttribute('data-service-type', vehicle.service_type || '');
            option.setAttribute('data-vehicle', JSON.stringify(vehicle));
            
            if (preserveSelection && currentVehicleId && vehicle.vehicle_id && String(currentVehicleId) === String(vehicle.vehicle_id)) {
                option.selected = true;
                matchedSelection = true;
            }
            vehicleSelect.appendChild(option);
        });
        
        if (!matchedSelection && (currentVehicleId || currentVehicleName)) {
            const fallbackOption = document.createElement('option');
            fallbackOption.value = currentVehicleName || currentVehicleId || 'selected-vehicle';
            fallbackOption.textContent = currentVehicleName || 'Current vehicle';
            fallbackOption.setAttribute('data-vehicle-id', currentVehicleId || '');
            fallbackOption.setAttribute('data-vehicle-name', currentVehicleName || '');
            fallbackOption.setAttribute('data-vehicle-type', form.dataset.initialServiceType || '');
            fallbackOption.setAttribute('data-seating-capacity', '');
            fallbackOption.setAttribute('data-private-price', form.dataset.initialPrivatePrice || form.dataset.initialTotalPrice || '');
            fallbackOption.setAttribute('data-shared-price', form.dataset.initialSharedPrice || '');
            fallbackOption.setAttribute('data-cost-per-hour', '');
            fallbackOption.setAttribute('data-sharable-cost-per-hour', '');
            fallbackOption.setAttribute('data-sharable', form.dataset.initialVehicleSharable || '');
            fallbackOption.setAttribute('data-service-type', form.dataset.initialServiceType || '');
            fallbackOption.setAttribute('data-vehicle', '{}');
            fallbackOption.selected = true;
            vehicleSelect.appendChild(fallbackOption);
        }
        
        vehicleSelect.disabled = false;
        syncLocalTransportVehicle(vehicleSelect);
        updateLocalTransportEditServiceTypeOptions(form);
        updateLocalTransportEditPrice(form);
    }
    
    function formatVehicleOptionLabel(vehicle) {
        const name = vehicle.vehicle_name || vehicle.vehicle_id || 'Vehicle';
        const typeText = vehicle.vehicle_type ? ` (${vehicle.vehicle_type})` : '';
        const seatsText = vehicle.seating_capacity ? ` - ${vehicle.seating_capacity} seats` : '';
        return `${name}${typeText}${seatsText}`;
    }
    
    function updateLocalTransportEditServiceTypeOptions(form) {
        const serviceTypeSelect = form.querySelector('.js-local-service-type');
        const vehicleSelect = form.querySelector('.js-local-vehicle-select');
        if (!serviceTypeSelect || !vehicleSelect) return;
        
        const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
        const previousValue = serviceTypeSelect.value || form.dataset.initialServiceType || '';
        serviceTypeSelect.innerHTML = '<option value="">Select type</option>';
        
        if (!selectedOption || !selectedOption.value) {
            serviceTypeSelect.value = '';
            serviceTypeSelect.disabled = true;
            return;
        }
        
        let sharable = parseInt(selectedOption.getAttribute('data-sharable') || 0, 10);
        const defaultServiceType = (selectedOption.getAttribute('data-service-type') || '').toLowerCase();
        
        if (!sharable) {
            if (defaultServiceType === 'shared') {
                sharable = 2;
            } else if (defaultServiceType === 'private') {
                sharable = 1;
            }
        }
        
        const addOption = (value) => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            serviceTypeSelect.appendChild(option);
        };
        
        if (sharable === 1) {
            addOption('Private');
        } else if (sharable === 2) {
            addOption('Shared');
        } else if (sharable === 3) {
            addOption('Private');
            addOption('Shared');
        } else {
            addOption('Private');
            if (selectedOption.getAttribute('data-shared-price')) {
                addOption('Shared');
            }
        }
        
        serviceTypeSelect.disabled = false;
        
        if (previousValue && serviceTypeSelect.querySelector(`option[value="${previousValue}"]`)) {
            serviceTypeSelect.value = previousValue;
        } else if (serviceTypeSelect.options.length > 1) {
            serviceTypeSelect.selectedIndex = 1;
        }
        
        form.dataset.initialServiceType = serviceTypeSelect.value;
    }
    
    function updateLocalTransportEditPrice(form) {
        const vehicleSelect = form.querySelector('.js-local-vehicle-select');
        const serviceTypeSelect = form.querySelector('.js-local-service-type');
        const totalPriceInput = form.querySelector('input[name="total_price"]');
        
        if (!vehicleSelect || !serviceTypeSelect || !totalPriceInput) return;
        
        const selectedVehicle = vehicleSelect.options[vehicleSelect.selectedIndex];
        const serviceType = serviceTypeSelect.value;
        
        if (!selectedVehicle || !serviceType) {
            if (form.dataset.initialTotalPrice) {
                totalPriceInput.value = parseFloat(form.dataset.initialTotalPrice).toFixed(2);
            }
            return;
        }
        
        const priceAttr = serviceType.toLowerCase() === 'shared' ? 'data-shared-price' : 'data-private-price';
        const rawPrice = selectedVehicle.getAttribute(priceAttr) || selectedVehicle.getAttribute('data-private-price');
        const priceNumber = parseFloat(rawPrice);
        
        if (!isNaN(priceNumber)) {
            totalPriceInput.value = priceNumber.toFixed(2);
        } else if (form.dataset.initialTotalPrice) {
            totalPriceInput.value = parseFloat(form.dataset.initialTotalPrice).toFixed(2);
        }
    }

    async function updateExistingRestaurant(event, bookingId) {
        event.preventDefault();
        const form = event.target;
        const url = form.dataset.updateUrl;
        const feedback = document.getElementById(`restaurant_feedback_${bookingId}`);
        const spinner = document.getElementById(`restaurant_spinner_${bookingId}`);
        const submitButton = form.querySelector('button[type="submit"]');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const formData = new FormData(form);

        feedback.textContent = '';
        feedback.classList.remove('text-success', 'text-danger');

        try {
            submitButton.disabled = true;
            spinner?.classList.remove('d-none');

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                const errorMessage = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Unable to save changes right now.');
                throw new Error(errorMessage);
            }

            feedback.textContent = data.message || 'Restaurant service updated successfully.';
            feedback.classList.add('text-success');
            
            // Show success toastr notification
            if (typeof showToastr !== 'undefined') {
                showToastr('success', data.message || 'Restaurant service updated successfully.');
            }
        } catch (error) {
            const errorMessage = error.message || 'Failed to update restaurant service. Please try again.';
            feedback.textContent = errorMessage;
            feedback.classList.add('text-danger');
            
            // Show error toastr notification
            if (typeof showToastr !== 'undefined') {
                showToastr('error', errorMessage);
            }
        } finally {
            submitButton.disabled = false;
            spinner?.classList.add('d-none');
        }
    }

    async function updateExistingAttraction(event, bookingId) {
        event.preventDefault();
        const form = event.target;
        const url = form.dataset.updateUrl;
        const feedback = document.getElementById(`attraction_feedback_${bookingId}`);
        const spinner = document.getElementById(`attraction_spinner_${bookingId}`);
        const submitButton = form.querySelector('button[type="submit"]');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const formData = new FormData(form);

        feedback.textContent = '';
        feedback.classList.remove('text-success', 'text-danger');

        try {
            submitButton.disabled = true;
            spinner?.classList.remove('d-none');

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                const errorMessage = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Unable to save changes right now.');
                throw new Error(errorMessage);
            }

            feedback.textContent = data.message || 'Attraction service updated successfully.';
            feedback.classList.add('text-success');
            
            // Show success toastr notification
            if (typeof showToastr !== 'undefined') {
                showToastr('success', data.message || 'Attraction service updated successfully.');
            }
        } catch (error) {
            const errorMessage = error.message || 'Failed to update attraction service. Please try again.';
            feedback.textContent = errorMessage;
            feedback.classList.add('text-danger');
            
            // Show error toastr notification
            if (typeof showToastr !== 'undefined') {
                showToastr('error', errorMessage);
            }
        } finally {
            submitButton.disabled = false;
            spinner?.classList.add('d-none');
        }
    }

    /**
     * Show alert for affected services and get user confirmation
     */
    async function showAffectedServicesAlert(affectedServices, newStartDate, newEndDate) {
        return new Promise((resolve) => {
            // Validate inputs
            if (!affectedServices || !Array.isArray(affectedServices) || affectedServices.length === 0) {
                console.error('Invalid affectedServices:', affectedServices);
                resolve(false);
                return;
            }
            
            newStartDate = newStartDate || '';
            newEndDate = newEndDate || '';
            
            // Build message for confirmation dialog
            const formattedStartDate = formatDate(newStartDate);
            const formattedEndDate = formatDate(newEndDate);
            
            // Build service list
            const servicesList = affectedServices.map((service, i) => {
                if (!service) return '';
                const serviceType = service.type || 'Unknown';
                const serviceName = service.name || 'N/A';
                let datesStr = 'N/A';
                
                if (service.dates) {
                    if (Array.isArray(service.dates)) {
                        datesStr = service.dates.filter(d => d).join(', ');
                    } else if (typeof service.dates === 'string') {
                        datesStr = service.dates;
                    }
                }
                
                const serviceTypeName = getServiceTypeName(serviceType);
                return `${i + 1}. ${serviceTypeName}: ${serviceName} (Dates: ${datesStr})`;
            }).filter(item => item !== '').join('\n');
            
            // Use browser's confirm dialog
            const message = `Warning: ${affectedServices.length} service(s) are outside the new tour date range (${formattedStartDate} to ${formattedEndDate}).\n\nThese services will be deleted if you proceed:\n\n${servicesList}\n\nDo you want to proceed and delete these services?`;
            
            const confirmed = confirm(message);
            resolve(confirmed);
        });
    }

    /**
     * Get service type display name
     */
    function getServiceTypeName(type) {
        if (!type || typeof type !== 'string') {
            return 'Unknown Service';
        }
        
        const typeNames = {
            'hotel': 'Hotel',
            'attraction': 'Attraction',
            'guide': 'Guide',
            'restaurant': 'Restaurant',
            'entry_port': 'Entry Port Transfer',
            'exit_port': 'Exit Port Transfer',
            'travel_hourly': 'Hourly Transport',
            'travel_point': 'Point-to-Point Transport',
            'local_transport': 'Local Transport'
        };
        return typeNames[type] || type || 'Unknown Service';
    }

    /**
     * Format date for display
     */
    function formatDate(dateStr) {
        if (!dateStr || dateStr === 'undefined' || dateStr === 'null') {
            return 'N/A';
        }
        
        try {
            const date = new Date(dateStr);
            // Check if date is valid
            if (isNaN(date.getTime())) {
                return String(dateStr);
            }
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        } catch (e) {
            console.error('Error formatting date:', e, dateStr);
            return String(dateStr || 'N/A');
        }
    }

    async function UpdateTourInformation(event) {
        event.preventDefault();
        
        const form = document.getElementById('singleTourPackageForm');
        if (!form) {
            console.error('Form element not found');
            showToastr('error', 'Form not found. Please refresh the page.');
            return;
        }
        
        const url = form.dataset.updateInfoUrl;
        const feedback = document.getElementById('tour_info_feedback');
        const spinner = document.getElementById('tour_info_spinner');
        const submitButton = event.target.closest('button');
        
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfMeta) {
            console.error('CSRF token meta tag not found');
            showToastr('error', 'Security token not found. Please refresh the page.');
            return;
        }
        const csrfToken = csrfMeta.getAttribute('content');

        if (!url) {
            if (feedback) {
                feedback.textContent = 'Tour update URL not found.';
                feedback.classList.add('text-danger');
            }
            showToastr('error', 'Tour update URL not found.');
            return;
        }

        // Collect form data
        const formData = new FormData();
        const displayIdEl = document.getElementById('display_id');
        const userCountryEl = document.getElementById('user_country');
        const startDateEl = document.getElementById('start_date');
        const endDateEl = document.getElementById('end_date');
        const adultsEl = document.getElementById('adults');
        const childrenEl = document.getElementById('children');
        const infantsEl = document.getElementById('infants');
        const maleCountEl = document.getElementById('male_count');
        const femaleCountEl = document.getElementById('female_count');
        const agentIdEl = document.getElementById('agent_id');
        
        formData.append('display_id', displayIdEl ? displayIdEl.value || '' : '');
        formData.append('user_country', userCountryEl ? userCountryEl.value : '');
        formData.append('start_date', startDateEl ? startDateEl.value : '');
        formData.append('end_date', endDateEl ? endDateEl.value : '');
        formData.append('adults', adultsEl ? adultsEl.value : '1');
        formData.append('children', childrenEl ? childrenEl.value : '0');
        formData.append('infants', infantsEl ? infantsEl.value : '0');
        formData.append('male', maleCountEl ? maleCountEl.value : '0');
        formData.append('female', femaleCountEl ? femaleCountEl.value : '0');
        formData.append('agent_id', agentIdEl ? agentIdEl.value : '');
        
        // Collect child ages - try multiple sources
        let childAges = '';
        
        // First, try to get from hidden field (updated by confirmTourGuestSelection)
        const childAgesHidden = document.getElementById('child_ages');
        if (childAgesHidden && childAgesHidden.value) {
            childAges = childAgesHidden.value;
        } else {
            // Fallback: collect from individual tour_child_age_X select elements
            const children = parseInt(childrenEl ? childrenEl.value : 0);
            const childAgeValues = [];
            for (let i = 1; i <= children; i++) {
                const ageSelect = document.getElementById(`tour_child_age_${i}`);
                if (ageSelect && ageSelect.value) {
                    childAgeValues.push(ageSelect.value);
                }
            }
            childAges = childAgeValues.join(',');
        }
        
        formData.append('child_ages', childAges);

        // Clear previous feedback
        feedback.textContent = '';
        feedback.classList.remove('text-success', 'text-danger');

        try {
            submitButton.disabled = true;
            spinner?.classList.remove('d-none');

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            });

            let data;
            try {
                const responseText = await response.text();
                if (!responseText) {
                    throw new Error('Empty response from server');
                }
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('Error parsing response:', parseError);
                throw new Error('Invalid response from server. Please try again.');
            }

            // Ensure data is an object
            if (!data || typeof data !== 'object') {
                throw new Error('Invalid response format from server.');
            }

            // Check if confirmation is required for affected services
            if (data.requires_confirmation === true && data.affected_services && Array.isArray(data.affected_services) && data.affected_services.length > 0) {
                submitButton.disabled = false;
                spinner?.classList.add('d-none');
                
                // Show confirmation dialog with affected services
                const confirmed = await showAffectedServicesAlert(data.affected_services, data.new_start_date, data.new_end_date);
                
                if (confirmed) {
                    // User confirmed, proceed with deletion
                    formData.append('delete_affected_services', '1');
                    
                    // Call update again with delete flag
                    submitButton.disabled = true;
                    spinner?.classList.remove('d-none');
                    
                    const confirmResponse = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData
                    });
                    
                    let confirmData;
                    try {
                        const confirmResponseText = await confirmResponse.text();
                        if (!confirmResponseText) {
                            throw new Error('Empty response from server');
                        }
                        confirmData = JSON.parse(confirmResponseText);
                    } catch (parseError) {
                        console.error('Error parsing confirm response:', parseError);
                        throw new Error('Invalid response from server. Please try again.');
                    }

                    // Ensure confirmData is an object
                    if (!confirmData || typeof confirmData !== 'object') {
                        throw new Error('Invalid response format from server.');
                    }
                    
                    if (!confirmResponse.ok || !confirmData.success) {
                        let errorMessage = confirmData.message || 'Unable to update tour information right now.';
                        
                        // Safely handle errors object
                        if (confirmData.errors && typeof confirmData.errors === 'object') {
                            try {
                                const errorValues = Object.values(confirmData.errors);
                                if (Array.isArray(errorValues) && errorValues.length > 0) {
                                    const flatErrors = errorValues.flat().filter(e => e);
                                    if (flatErrors.length > 0) {
                                        errorMessage = flatErrors.join(', ');
                                    }
                                }
                            } catch (e) {
                                console.error('Error processing errors:', e);
                            }
                        }
                        
                        throw new Error(errorMessage);
                    }
                    
                    // Show success feedback with deleted services count
                    const deletedCount = confirmData.deleted_services_count || 0;
                    const successMsg = confirmData.message || 'Tour information updated successfully.';
                    const finalMessage = deletedCount > 0 
                        ? `${successMsg} ${deletedCount} service(s) outside the new date range have been removed.`
                        : successMsg;
                    
                    feedback.textContent = finalMessage;
                    feedback.classList.add('text-success');
                    
                    // Show success toastr notification
                    showToastr('success', finalMessage);
                    
                    // Reload page to reflect deleted services
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    // User cancelled, don't update
                    feedback.textContent = 'Tour update cancelled. No changes were made.';
                    feedback.classList.add('text-warning');
                    showToastr('info', 'Tour update cancelled. No changes were made.');
                }
                return;
            }

            if (!response.ok || !data.success) {
                let errorMessage = data.message || 'Unable to update tour information right now.';
                
                // Safely handle errors object
                if (data.errors && typeof data.errors === 'object') {
                    try {
                        const errorValues = Object.values(data.errors);
                        if (Array.isArray(errorValues) && errorValues.length > 0) {
                            const flatErrors = errorValues.flat().filter(e => e);
                            if (flatErrors.length > 0) {
                                errorMessage = flatErrors.join(', ');
                            }
                        }
                    } catch (e) {
                        console.error('Error processing errors:', e);
                    }
                }
                
                throw new Error(errorMessage);
            }

            // Show success feedback
            feedback.textContent = data.message || 'Tour information updated successfully.';
            feedback.classList.add('text-success');
            
            // Show success toastr notification
            showToastr('success', data.message || 'Tour information updated successfully.');
        } catch (error) {
            const errorMessage = error.message || 'Failed to update tour information. Please try again.';
            feedback.textContent = errorMessage;
            feedback.classList.add('text-danger');
            
            // Show error toastr notification
            showToastr('error', errorMessage);
        } finally {
            submitButton.disabled = false;
            spinner?.classList.add('d-none');
        }
    }

    // Toastr notification helper function
    function showToastr(type, message) {
        // Check if toastr is available
        if (typeof toastr !== 'undefined') {
            if (type === 'success') {
                toastr.success(message, 'Success', {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 3000,
                    positionClass: 'toast-top-right'
                });
            } else if (type === 'error') {
                toastr.error(message, 'Error', {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 5000,
                    positionClass: 'toast-top-right'
                });
            } else if (type === 'warning') {
                toastr.warning(message, 'Warning', {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 4000,
                    positionClass: 'toast-top-right'
                });
            } else {
                toastr.info(message, 'Info', {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 3000,
                    positionClass: 'toast-top-right'
                });
            }
        } else {
            // Fallback to showNotification if toastr is not available
            if (typeof showNotification === 'function') {
                showNotification(message, type);
            } else {
                // Final fallback to alert
                alert(message);
            }
        }
    }
</script>
@endsection

