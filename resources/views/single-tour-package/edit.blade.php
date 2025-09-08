@extends('layouts.layout')
@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <x-alert />
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-gradient-primary text-white">
                        <div class="d-flex align-items-center">
                            <i class="ri-map-pin-line me-3 fs-4"></i>
                            <div>
                            <h4 class="mb-1 text-white">Add and Remove Tour Services</h4>
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
                <div class="alert alert-warning border-0">
                    <div class="d-flex align-items-center">
                        <i class="ri-edit-line me-3" style="font-size: 2rem;"></i>
                        <div>
                            <h5 class="mb-1">Add and Tour Services</h5>
                            <p class="mb-0">You are Add and services for tour <strong>{{ $tour->display_id ?? 'N/A' }}</strong>. Tour information cannot be modified, but you can edit all services including hotels, attractions, guides, restaurants, and transport.</p>
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
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="customerFullName" name="customer_full_name" placeholder="Enter full name" required value="{{ $customer_info['fullName'] ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" id="customerEmail" name="customer_email" placeholder="Enter email" required value="{{ $customer_info['email'] ?? '' }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Country Code</label>
                                    <input type="text" class="form-control" id="customerCountryCode" name="customer_country_code" placeholder="e.g. +91" required value="{{ $customer_info['countryCode'] ?? '' }}">
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="customerPhone" name="customer_phone" placeholder="Enter phone number" required value="{{ $customer_info['phone'] ?? '' }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address Line 1</label>
                                    <input type="text" class="form-control" id="customerAddress1" name="customer_address1" placeholder="Enter address line 1" required value="{{ $customer_info['address1'] ?? '' }}">
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
        

        <form id="singleTourPackageForm" method="POST" action="{{ route('single-tour-package.store') }}">
            @csrf
            
            <!-- Main Form Card - All in One Row -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-gradient-primary text-white">
                            <h6 class="mb-0 fw-bold">
                                <i class="ri-settings-3-line me-2"></i>Tour Information (Read-Only)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Tour ID Display -->
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">
                                        <i class="ri-hashtag me-1"></i>Tour ID
                                    </label>
                                    <input type="text" class="form-control" value="{{ $tour->display_id ?? 'N/A' }}" readonly>
                                    <input type="hidden" id="tour_id" name="tour_id" value="{{ $tour->tour_id ?? '' }}">
                                </div>

                                <!-- Country Display -->
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">
                                        <i class="ri-earth-line me-1"></i>Country
                                    </label>
                                    <input type="text" class="form-control" value="{{ $tour->destination ?? 'N/A' }}" readonly>
                                    <input type="hidden" name="user_country" id="user_country" value="{{ $tour->destination ?? '' }}">
                                </div>

                                <!-- City Display -->
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">
                                        <i class="ri-building-line me-1"></i>City
                                    </label>
                                    <input type="text" class="form-control" value="{{ $tour->city ?? 'N/A' }}" readonly>
                                    <input type="hidden" name="city" id="city" value="{{ $tour->city ?? '' }}">
                                </div>

                                <!-- Travel Dates Display -->
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">
                                        <i class="ri-calendar-line me-1"></i>Travel Dates
                                    </label>
                                    <input type="text" class="form-control" value="{{ 
                                        $tour->check_in_time && $tour->check_out_time 
                                            ? (is_string($tour->check_in_time) 
                                                ? date('M d, Y', strtotime($tour->check_in_time)) . ' - ' . date('M d, Y', strtotime($tour->check_out_time))
                                                : $tour->check_in_time->format('M d, Y') . ' - ' . $tour->check_out_time->format('M d, Y'))
                                            : 'N/A' 
                                    }}" readonly>
                                    <input type="hidden" name="start_date" id="start_date" value="{{ 
                                        $tour->check_in_time 
                                            ? (is_string($tour->check_in_time) ? date('Y-m-d', strtotime($tour->check_in_time)) : $tour->check_in_time->format('Y-m-d'))
                                            : '' 
                                    }}">
                                    <input type="hidden" name="end_date" id="end_date" value="{{ 
                                        $tour->check_out_time 
                                            ? (is_string($tour->check_out_time) ? date('Y-m-d', strtotime($tour->check_out_time)) : $tour->check_out_time->format('Y-m-d'))
                                            : '' 
                                    }}">
                                </div>

                                <!-- Guests Display -->
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">
                                        <i class="ri-group-line me-1"></i>Guests
                                    </label>
                                    <input type="text" class="form-control" value="{{ $tour->adult ?? 0 }} adults ({{ $tour->male_count ?? 0 }} male, {{ $tour->female_count ?? 0 }} female), {{ $tour->child ?? 0 }} children, {{ $tour->infant ?? 0 }} infants" readonly>
                                    <input type="hidden" name="adults" id="adults" value="{{ $tour->adult ?? 1 }}">
                                    <input type="hidden" name="male" id="male" value="{{ $tour->male_count ?? 0 }}">
                                    <input type="hidden" name="female" id="female" value="{{ $tour->female_count ?? 0 }}">
                                    <input type="hidden" name="children" id="children" value="{{ $tour->child ?? 0 }}">
                                    <input type="hidden" name="infants" id="infants" value="{{ $tour->infant ?? 0 }}">
                                    <input type="hidden" name="child_ages" id="child_ages" value="{{ $tour->child_ages ?? '' }}">
                                </div>

                                <!-- Agent Display -->
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">
                                        <i class="ri-user-star-line me-1"></i>Agent
                                    </label>
                                    <input type="text" class="form-control" value="{{ $agent_name ?? 'N/A' }}" readonly>
                                    <input type="hidden" name="agent_id" id="agent_id" value="{{ $tour->agent_id ?? '' }}">
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="ri-information-line me-2"></i>
                                        <strong>Note:</strong> Tour information is read-only to maintain data integrity. You can edit hotels, attractions, guides, restaurants, and other services below.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service Action Buttons -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-gradient-success text-white">
                            <h6 class="mb-0 fw-bold">
                                <i class="ri-add-circle-line me-2"></i>Add New Services
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-warning btn-lg w-100 h-100 d-flex flex-column align-items-center justify-content-center" onclick="addTransportService(); return false;">
                                        <i class="ri-car-line fs-1 mb-2"></i>
                                        <span class="fw-bold">Local Transport</span>
                                        <small class="opacity-75">Port Pickup Service</small>
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-primary btn-lg w-100 h-100 d-flex flex-column align-items-center justify-content-center" onclick="addHotelService()">
                                        <i class="ri-hotel-line fs-1 mb-2"></i>
                                        <span class="fw-bold">Hotel</span>
                                        <small class="opacity-75">Book Accommodation</small>
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-info btn-lg w-100 h-100 d-flex flex-column align-items-center justify-content-center" onclick="addGuideService()">
                                        <i class="ri-user-star-line fs-1 mb-2"></i>
                                        <span class="fw-bold">Guide</span>
                                        <small class="opacity-75">Book Tour Guide</small>
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-success btn-lg w-100 h-100 d-flex flex-column align-items-center justify-content-center" onclick="addRestaurantService()">
                                        <i class="ri-restaurant-2-line fs-1 mb-2"></i>
                                        <span class="fw-bold">Restaurant</span>
                                        <small class="opacity-75">Book Dining</small>
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-danger btn-lg w-100 h-100 d-flex flex-column align-items-center justify-content-center" onclick="addAttractionService(); return false;">
                                        <i class="ri-ticket-line fs-1 mb-2"></i>
                                        <span class="fw-bold">Attractions</span>
                                        <small class="opacity-75">Book Tickets</small>
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-info btn-lg w-100 h-100 d-flex flex-column align-items-center justify-content-center" onclick="addLocalTransferService(); return false;">
                                        <i class="ri-taxi-line fs-1 mb-2"></i>
                                        <span class="fw-bold">Local Transfer</span>
                                        <small class="opacity-75">Book Local Transport</small>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="alert alert-success border-0">
                                        <i class="ri-lightbulb-line me-2"></i>
                                        <strong>Quick Actions:</strong> Click any service button above to quickly add new services to your tour package. Each service will be configured based on your tour details.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Day-based Service Sections -->
            @if(isset($ordersByType) && count($ordersByType) > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-gradient-primary text-white">
                            <h6 class="mb-0 fw-bold">
                                <i class="ri-calendar-line me-2"></i>Edit Tour Services by Day
                            </h6>
                        </div>
                        <div class="card-body">
                            <!-- Day 1 Services -->
                            <div class="day-services mb-4">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="ri-calendar-check-line me-2"></i>Day 1 ({{ $tour->check_in_time ? $tour->check_in_time->format('l, jS F Y') : 'Tour Start Date' }})
                                </h6>
                                
                                <!-- Attractions Section -->
                                @if(isset($ordersByType['attraction']))
                                <div class="service-section mb-3">
                                    <div class="card border-danger shadow-sm">
                                        <div class="card-header bg-danger text-white">
                                            <div class="d-flex align-items-center">
                                                <span class="service-icon me-3">
                                                    <i class="ri-ticket-line fs-4"></i>
                                                </span>
                                                <div>
                                                    <h6 class="mb-0 fw-bold">Book Attraction Tickets</h6>
                                                    <small class="opacity-75">Select attractions and configure your perfect tour package</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            @foreach($ordersByType['attraction'] as $index => $order)
                                            <div class="attraction-item mb-3 p-3 border rounded">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0">Attraction Booking #{{ $index + 1 }}</h6>
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeAttractionService({{ $order->booking_id }})">
                                                            <i class="ri-delete-bin-line"></i> Remove
                                                        </button>
                                                    </div>
                                                </div>
                                                @php
                                                    $attractionData = $order->processed_data;
                                                    $attractionName = '';
                                                    $timeSlot = '';
                                                    $ticket = '';
                                                    $guestSummary = '';
                                                    
                                                    if (is_array($attractionData)) {
                                                        if (isset($attractionData[0])) {
                                                            $attractionName = $attractionData[0]['AttractionName'] ?? 'N/A';
                                                            $timeSlot = $attractionData[0]['visitTime'] ?? 'N/A';
                                                            $ticket = $attractionData[0]['ticketName'] ?? 'N/A';
                                                            $adultCount = $attractionData[0]['adultCount'] ?? 0;
                                                            $childCount = $attractionData[0]['childCount'] ?? 0;
                                                            $seniorCount = $attractionData[0]['seniorCount'] ?? 0;
                                                            $totalPrice = $attractionData[0]['totalPrice'] ?? 0;
                                                            $guestSummary = $adultCount . ' adults, ' . $childCount . ' children, ' . $seniorCount . ' seniors';
                                                        } else {
                                                            $attractionName = $attractionData['AttractionName'] ?? 'N/A';
                                                            $timeSlot = $attractionData['visitTime'] ?? 'N/A';
                                                            $ticket = $attractionData['ticketName'] ?? 'N/A';
                                                            $adultCount = $attractionData['adultCount'] ?? 0;
                                                            $childCount = $attractionData['childCount'] ?? 0;
                                                            $seniorCount = $attractionData['seniorCount'] ?? 0;
                                                            $totalPrice = $attractionData['totalPrice'] ?? 0;
                                                            $guestSummary = $adultCount . ' adults, ' . $childCount . ' children, ' . $seniorCount . ' seniors';
                                                        }
                                                    }
                                                @endphp
                                                <div class="row g-2">
                                                    <div class="col-md-2">
                                                        <label class="form-label small fw-semibold">Attraction</label>
                                                        <input type="text" class="form-control form-control-sm" value="{{ $attractionName }}" readonly>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label small fw-semibold">Time Slot</label>
                                                        <input type="text" class="form-control form-control-sm" value="{{ $timeSlot }}" readonly>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label small fw-semibold">Ticket</label>
                                                        <input type="text" class="form-control form-control-sm" value="{{ $ticket }}" readonly>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-semibold">Guests</label>
                                                        <input type="text" class="form-control form-control-sm" value="{{ $guestSummary }}" readonly>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-semibold">Total Price</label>
                                                        <input type="text" class="form-control form-control-sm" value="${{ number_format($totalPrice, 2) }}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Guide Services Section -->
                                @if(isset($ordersByType['guide']))
                                <div class="service-section mb-3">
                                    <div class="card border-info shadow-sm">
                                        <div class="card-header bg-info text-white">
                                            <div class="d-flex align-items-center">
                                                <span class="service-icon me-3">
                                                    <i class="ri-user-star-line fs-4"></i>
                                                </span>
                                                <div>
                                                    <h6 class="mb-0 fw-bold">Book Tour Guide Services</h6>
                                                    <small class="opacity-75">Select professional guides and configure your tour package</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            @foreach($ordersByType['guide'] as $index => $order)
                                            <div class="guide-item mb-3 p-3 border rounded">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0">Tour Guide Booking #{{ $index + 1 }}</h6>
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeGuideService({{ $order->booking_id }})">
                                                            <i class="ri-delete-bin-line"></i> Remove
                                                        </button>
                                                    </div>
                                                </div>
                                                @php
                                                    $guideData = $order->processed_data;
                                                    $guideName = '';
                                                    $package = '';
                                                    $pickupTime = '';
                                                    $guestSummary = '';
                                                    
                                                    if (is_array($guideData)) {
                                                        if (isset($guideData[0])) {
                                                            $guideName = $guideData[0]['guide_name'] ?? 'N/A';
                                                            $package = $guideData[0]['package'] ?? 'N/A';
                                                            $pickupTime = $guideData[0]['pickup_time'] ?? 'N/A';
                                                            $guestSummary = $guideData[0]['guest_summary'] ?? 'N/A';
                                                        } else {
                                                            $guideName = $guideData['guide_name'] ?? 'N/A';
                                                            $package = $guideData['package'] ?? 'N/A';
                                                            $pickupTime = $guideData['pickup_time'] ?? 'N/A';
                                                            $guestSummary = $guideData['guest_summary'] ?? 'N/A';
                                                        }
                                                    }
                                                @endphp
                                                <div class="row g-2">
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-semibold">Guide</label>
                                                        <input type="text" class="form-control form-control-sm" value="{{ $guideName }}" readonly>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-semibold">Package</label>
                                                        <input type="text" class="form-control form-control-sm" value="{{ $package }}" readonly>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-semibold">Pickup Time</label>
                                                        <input type="text" class="form-control form-control-sm" value="{{ $pickupTime }}" readonly>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-semibold">Guests</label>
                                                        <input type="text" class="form-control form-control-sm" value="{{ $guestSummary }}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Restaurant Services Section -->
                                @if(isset($ordersByType['restaurant']))
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
                                        <div class="card-body">
                                            @foreach($ordersByType['restaurant'] as $index => $order)
                                            <div class="restaurant-item mb-3 p-3 border rounded">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0">Restaurant Booking #{{ $index + 1 }}</h6>
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRestaurantService({{ $order->booking_id }})">
                                                            <i class="ri-delete-bin-line"></i> Remove
                                                        </button>
                                                    </div>
                                                </div>
                                                @php
                                                    $restaurantData = $order->processed_data;
                                                    $restaurantName = '';
                                                    $mealType = '';
                                                    $dishName = '';
                                                    $guestSummary = '';
                                                    
                                                    if (is_array($restaurantData)) {
                                                        if (isset($restaurantData[0])) {
                                                            $restaurantName = $restaurantData[0]['restaurantName'] ?? 'N/A';
                                                            $mealType = $restaurantData[0]['mealType'] ?? 'N/A';
                                                            $mealSpecificType = $restaurantData[0]['mealSpecificType'] ?? 'N/A';
                                                            $adultCount = $restaurantData[0]['adultCount'] ?? 0;
                                                            $childCount = $restaurantData[0]['childCount'] ?? 0;
                                                            $totalPrice = $restaurantData[0]['totalPrice'] ?? 0;
                                                            $guestSummary = $adultCount . ' adults, ' . $childCount . ' children';
                                                        } else {
                                                            $restaurantName = $restaurantData['restaurantName'] ?? 'N/A';
                                                            $mealType = $restaurantData['mealType'] ?? 'N/A';
                                                            $mealSpecificType = $restaurantData['mealSpecificType'] ?? 'N/A';
                                                            $adultCount = $restaurantData['adultCount'] ?? 0;
                                                            $childCount = $restaurantData['childCount'] ?? 0;
                                                            $totalPrice = $restaurantData['totalPrice'] ?? 0;
                                                            $guestSummary = $adultCount . ' adults, ' . $childCount . ' children';
                                                        }
                                                    }
                                                @endphp
                                                <div class="row g-2">
                                                    <div class="col-md-2">
                                                        <label class="form-label small fw-semibold">Restaurant</label>
                                                        <input type="text" class="form-control form-control-sm" value="{{ $restaurantName }}" readonly>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label small fw-semibold">Meal Type</label>
                                                        <input type="text" class="form-control form-control-sm" value="{{ $mealType }}" readonly>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label small fw-semibold">Meal Specific</label>
                                                        <input type="text" class="form-control form-control-sm" value="{{ $mealSpecificType }}" readonly>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-semibold">Guests</label>
                                                        <input type="text" class="form-control form-control-sm" value="{{ $guestSummary }}" readonly>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-semibold">Total Price</label>
                                                        <input type="text" class="form-control form-control-sm" value="${{ number_format($totalPrice, 2) }}" readonly>
                                                    </div>
                                                </div>
                                                
                                                <!-- Meal Details Display -->
                                                @if(isset($restaurantData[0]['MealDescription']) || isset($restaurantData['MealDescription']))
                                                <div class="row mt-2">
                                                    <div class="col-12">
                                                        <label class="form-label small fw-semibold">Meal Details:</label>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th>Item Name</th>
                                                                        <th>Description</th>
                                                                        <th>Category</th>
                                                                        <th>Type</th>
                                                                        <th>Quantity</th>
                                                                        <th>Price</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @php
                                                                        $mealDescription = isset($restaurantData[0]) ? $restaurantData[0]['MealDescription'] : $restaurantData['MealDescription'];
                                                                        if (is_array($mealDescription)) {
                                                                            foreach ($mealDescription as $meal) {
                                                                                echo '<tr>';
                                                                                echo '<td>' . ($meal['item_name'] ?? 'N/A') . '</td>';
                                                                                echo '<td>' . ($meal['name'] ?? 'N/A') . '</td>';
                                                                                echo '<td>' . ($meal['category'] ?? 'N/A') . '</td>';
                                                                                echo '<td>' . ($meal['item_type'] ?? 'N/A') . '</td>';
                                                                                echo '<td>' . ($meal['quantity'] ?? 'N/A') . '</td>';
                                                                                echo '<td>$' . number_format($meal['price'] ?? 0, 2) . '</td>';
                                                                                echo '</tr>';
                                                                            }
                                                                        }
                                                                    @endphp
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Transport Services Section -->
                                @if(isset($ordersByType['travel_hourly']) || isset($ordersByType['travel_point']) || isset($ordersByType['local_transport']))
                                <div class="service-section mb-3">
                                    <div class="card border-warning shadow-sm">
                                        <div class="card-header bg-warning text-dark">
                                            <div class="d-flex align-items-center">
                                                <span class="service-icon me-3">
                                                    <i class="ri-car-line fs-4"></i>
                                                </span>
                                                <div>
                                                    <h6 class="mb-0 fw-bold">Book Transport Services</h6>
                                                    <small class="opacity-75">Select professional transport and configure your tour package</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            @if(isset($ordersByType['travel_hourly']))
                                                @foreach($ordersByType['travel_hourly'] as $index => $order)
                                                <div class="transport-item mb-3 p-3 border rounded">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6 class="mb-0">Hourly Transport #{{ $index + 1 }}</h6>
                                                                                                                                                                <div class="d-flex gap-2">
                                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTransportService({{ $order->booking_id }})">
                                                                <i class="ri-delete-bin-line"></i> Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                    @php
                                                        $transportData = $order->processed_data;
                                                        $pickupLocation = '';
                                                        $dropoffLocation = '';
                                                        $pickupTime = '';
                                                        $vehicleType = '';
                                                        
                                                        if (is_array($transportData)) {
                                                            if (isset($transportData[0])) {
                                                                $pickupLocation = $transportData[0]['pickup_location'] ?? 'N/A';
                                                                $dropoffLocation = $transportData[0]['dropoff_location'] ?? 'N/A';
                                                                $pickupTime = $transportData[0]['pickup_time'] ?? 'N/A';
                                                                $vehicleType = $transportData[0]['vehicle_type'] ?? 'N/A';
                                                            } else {
                                                                $pickupLocation = $transportData['pickup_location'] ?? 'N/A';
                                                                $dropoffLocation = $transportData['dropoff_location'] ?? 'N/A';
                                                                $pickupTime = $transportData['pickup_time'] ?? 'N/A';
                                                                $vehicleType = $transportData['vehicle_type'] ?? 'N/A';
                                                            }
                                                        }
                                                    @endphp
                                                    <div class="row g-2">
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">Pickup Location</label>
                                                            <input type="text" class="form-control form-control-sm" value="{{ $pickupLocation }}" readonly>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">Dropoff Location</label>
                                                            <input type="text" class="form-control form-control-sm" value="{{ $dropoffLocation }}" readonly>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">Pickup Time</label>
                                                            <input type="text" class="form-control form-control-sm" value="{{ $pickupTime }}" readonly>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">Vehicle Type</label>
                                                            <input type="text" class="form-control form-control-sm" value="{{ $vehicleType }}" readonly>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            @endif

                                            @if(isset($ordersByType['travel_point']))
                                                @foreach($ordersByType['travel_point'] as $index => $order)
                                                <div class="transport-item mb-3 p-3 border rounded">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6 class="mb-0">Point-to-Point Transport #{{ $index + 1 }}</h6>
                                                                                                                                                                 <div class="d-flex gap-2">
                                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTransportService({{ $order->booking_id }})">
                                                                <i class="ri-delete-bin-line"></i> Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                    @php
                                                        $transportData = $order->processed_data;
                                                        $pickupLocation = '';
                                                        $dropoffLocation = '';
                                                        $pickupTime = '';
                                                        $vehicleType = '';
                                                        
                                                        if (is_array($transportData)) {
                                                            if (isset($transportData[0])) {
                                                                $pickupLocation = $transportData[0]['pickup_location'] ?? 'N/A';
                                                                $dropoffLocation = $transportData[0]['dropoff_location'] ?? 'N/A';
                                                                $pickupTime = $transportData[0]['pickup_time'] ?? 'N/A';
                                                                $vehicleType = $transportData[0]['vehicle_type'] ?? 'N/A';
                                                            } else {
                                                                $pickupLocation = $transportData['pickup_location'] ?? 'N/A';
                                                                $dropoffLocation = $transportData['dropoff_location'] ?? 'N/A';
                                                                $pickupTime = $transportData['pickup_time'] ?? 'N/A';
                                                                $vehicleType = $transportData['vehicle_type'] ?? 'N/A';
                                                            }
                                                        }
                                                    @endphp
                                                    <div class="row g-2">
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">Pickup Location</label>
                                                            <input type="text" class="form-control form-control-sm" value="{{ $pickupLocation }}" readonly>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">Dropoff Location</label>
                                                            <input type="text" class="form-control form-control-sm" value="{{ $dropoffLocation }}" readonly>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">Pickup Time</label>
                                                            <input type="text" class="form-control form-control-sm" value="{{ $pickupTime }}" readonly>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">Vehicle Type</label>
                                                            <input type="text" class="form-control form-control-sm" value="{{ $vehicleType }}" readonly>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            @endif

                                            @if(isset($ordersByType['local_transport']))
                                                @foreach($ordersByType['local_transport'] as $index => $order)
                                                <div class="transport-item mb-3 p-3 border rounded">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6 class="mb-0">Local Transport #{{ $index + 1 }}</h6>
                                                                                                                                                                 <div class="d-flex gap-2">
                                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTransportService({{ $order->booking_id }})">
                                                                <i class="ri-delete-bin-line"></i> Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                    @php
                                                        $transportData = $order->processed_data;
                                                        $pickupLocation = '';
                                                        $dropoffLocation = '';
                                                        $pickupTime = '';
                                                        $vehicleType = '';
                                                        
                                                        if (is_array($transportData)) {
                                                            if (isset($transportData[0])) {
                                                                $pickupLocation = $transportData[0]['pickup_location'] ?? 'N/A';
                                                                $dropoffLocation = $transportData[0]['dropoff_location'] ?? 'N/A';
                                                                $pickupTime = $transportData[0]['pickup_time'] ?? 'N/A';
                                                                $vehicleType = $transportData[0]['vehicle_type'] ?? 'N/A';
                                                            } else {
                                                                $pickupLocation = $transportData['pickup_location'] ?? 'N/A';
                                                                $dropoffLocation = $transportData['dropoff_location'] ?? 'N/A';
                                                                $pickupTime = $transportData['pickup_time'] ?? 'N/A';
                                                                $vehicleType = $transportData['vehicle_type'] ?? 'N/A';
                                                            }
                                                        }
                                                    @endphp
                                                    <div class="row g-2">
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">Pickup Location</label>
                                                            <input type="text" class="form-control form-control-sm" value="{{ $pickupLocation }}" readonly>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">Dropoff Location</label>
                                                            <input type="text" class="form-control form-control-sm" value="{{ $dropoffLocation }}" readonly>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">Pickup Time</label>
                                                            <input type="text" class="form-control form-control-sm" value="{{ $pickupTime }}" readonly>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">Vehicle Type</label>
                                                            <input type="text" class="form-control form-control-sm" value="{{ $vehicleType }}" readonly>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </form>
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

                    <!-- Hotel Selection -->
                    <div class="mb-3">
                        <label for="hotel_select" class="form-label fw-semibold">
                            <i class="ri-building-line me-1"></i>Select Hotel
                        </label>
                        <select class="form-select" id="hotel_select" name="hotel_id" required>
                            <option value="">Select a hotel in <span id="modal_city_display"></span></option>
                        </select>
                        <div class="form-text">
                            <i class="ri-check-line text-success me-1"></i>
                            <span id="hotel_count">0</span> hotels found in <span id="modal_city_display2"></span>
                        </div>
                    </div>

                    <!-- Room Details -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="room_type" class="form-label fw-semibold">Room Type</label>
                            <select class="form-select" id="room_type" name="room_type" required>
                                <option value="">Select hotel first</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="bed_type" class="form-label fw-semibold">Bed Type</label>
                            <select class="form-select" id="bed_type" name="bed_type" required>
                                <option value="">Select hotel first</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="meal_plan" class="form-label fw-semibold">Meal Plan</label>
                            <select class="form-select" id="meal_plan" name="meal_plan" required>
                                <option value="">Select hotel first</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-success" id="add_room_btn">
                                <i class="ri-add-line"></i>
                            </button>
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
                                <input type="date" class="form-control" id="check_in_date" name="check_in_date" required>
                            </div>
                            <div class="col-md-6">
                                <label for="check_out_date" class="form-label">Check-out Date</label>
                                <input type="date" class="form-control" id="check_out_date" name="check_out_date" required>
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
                    <div class="alert alert-info" id="no_nights_alert">
                        <i class="ri-information-line me-2"></i>
                        No nights selected. Click on the nights above to select hotel stay.
                    </div>
                    <div class="alert alert-info" id="no_hotels_alert">
                        <i class="ri-information-line me-2"></i>
                        No hotels selected yet. Choose your hotels above.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="proceed_hotel_btn" disabled>
                    <i class="ri-arrow-right-line me-1"></i>Proceed to Hotel Selection
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
                <button type="button" class="btn btn-success" id="confirm_restaurant_btn" disabled>
                    <i class="ri-check-line me-1"></i>Confirm Restaurant Selection
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
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Select Guests</label>
                            <div class="guest-selector">
                                <div class="guest-display p-2 border rounded bg-light">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="guest-info">
                                            <span id="modal_attraction_guest_summary" class="text-muted small">
                                                1 adults (1 male, 0 female), 0 children -0 infants
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openAttractionGuestSelector()">
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
                            
                            <!-- Hidden fields for attraction pricing -->
                            <input type="hidden" name="modal_attraction_total_price" id="modal_attraction_total_price" value="0">
                            <input type="hidden" name="modal_attraction_ticket_id" id="modal_attraction_ticket_id" value="">
                            <input type="hidden" name="modal_attraction_ticket_name" id="modal_attraction_ticket_name" value="">
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
                            <select class="form-select" name="modal_attraction_ticket" id="modal_attraction_ticket" onchange="updateAttractionPricing()">
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
                                            <i class="ri-map-pin-line text-success me-2"></i>Pick Up Location
                                        </label>
                                        <div class="position-relative">
                                            <select class="form-select pickup-zone-select border-2" id="modal_transport_pickup_zone" name="pickup_zone_id" style="padding-left: 45px;">
                                                <option value="">Select pickup location</option>
                                                @foreach($ports as $port)
                                                <option value="{{ $port->id }}" data-port="{{ json_encode($port) }}">{{ $port->port_name }}</option>
                                                @endforeach
                                            </select>
                                            <i class="ri-map-pin-fill position-absolute text-success" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
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
                                                <option value="hotel_{{ $hotel->id }}" data-hotel="{{ json_encode($hotel) }}">{{ $hotel->name }}</option>
                                                @endforeach
                                                </optgroup>
                                                
                                                <!-- Attractions -->
                                                <optgroup label="Attractions">
                                                @foreach($attractions as $attraction)
                                                <option value="attraction_{{ $attraction->id }}" data-attraction="{{ json_encode($attraction) }}">{{ $attraction->name }}</option>
                                                @endforeach
                                                </optgroup>
                                                
                                                <!-- Restaurants -->
                                                <optgroup label="Restaurants">
                                                @foreach($restaurants as $restaurant)
                                                <option value="restaurant_{{ $restaurant->id }}" data-restaurant="{{ json_encode($restaurant) }}">{{ $restaurant->name }}</option>
                                                @endforeach
                                                </optgroup>
                                            </select>
                                            <i class="ri-map-pin-fill position-absolute text-danger" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                            <button type="button" class="btn btn-sm position-absolute" style="right: 8px; top: 50%; transform: translateY(-50%); z-index: 5; border: none; background: none;" onclick="clearDropoffZone()">
                                                <i class="ri-close-line text-muted"></i>
                                            </button>
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
                                            <i class="ri-time-fill position-absolute text-warning" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label fw-semibold text-muted mb-2">
                                            <i class="ri-calendar-line text-primary me-2"></i>Pick Up Date
                                        </label>
                                        <div class="position-relative">
                                            <input type="date" class="form-control border-2" id="modal_transport_pickup_date" name="pickup_date" value="{{ \Carbon\Carbon::parse($tour->check_in_time)->format('Y-m-d') }}" readonly style="padding-left: 45px;">
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
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Vehicle</label>
                                            <select class="form-select vehicle-select" 
                                                    id="modal_transport_vehicle_id" 
                                                    name="vehicle_id" 
                                                    onchange="updateVehicleDetails()">
                                                <option value="">Choose vehicle</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
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
                                    </div>
                                    
                                    <!-- Guest Information -->
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold">Number of Passengers</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="ri-user-line"></i></span>
                                                    <input type="number" class="form-control" id="modal_transport_passengers" name="passengers" min="1" value="1" onchange="updatePricing()">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
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
                                    <div class="form-check">
                                        <input class="form-check-input transport-service-type" type="radio" name="service_type_selection" id="local_transfer_service_type_local" value="local_transfer" onchange="handleLocalTransferServiceTypeChange('local_transfer')" checked>
                                        <label class="form-check-label fw-semibold text-success" for="local_transfer_service_type_local">
                                            <i class="ri-car-line me-1"></i>Local Transfer
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row g-4 align-items-end">
                                <!-- Local Transfer Fields (Default) -->
                                <div class="col-12">
                                    <div id="local_transfer_fields" class="row g-4 align-items-end local-transfer-fields d-none">
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
                                                            <option value="{{ $port->id }}" data-port="{{ json_encode($port) }}">{{ $port->port_name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                        <optgroup label="Hotels">
                                                        @foreach($hotels as $hotel)
                                                        <option value="hotel_{{ $hotel->id }}" data-hotel="{{ json_encode($hotel) }}">{{ $hotel->name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                        <optgroup label="Attractions">
                                                        @foreach($attractions as $attraction)
                                                        <option value="attraction_{{ $attraction->id }}" data-attraction="{{ json_encode($attraction) }}">{{ $attraction->name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                        <optgroup label="Restaurants">
                                                        @foreach($restaurants as $restaurant)
                                                        <option value="restaurant_{{ $restaurant->id }}" data-restaurant="{{ json_encode($restaurant) }}">{{ $restaurant->name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                    </select>
                                                    <i class="ri-map-pin-fill position-absolute text-success" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold text-muted mb-2">
                                                    <i class="ri-map-pin-line text-danger me-2"></i>Drop Off Location
                                                </label>
                                                <div class="position-relative">
                                                    <select class="form-select dropoff-zone-select border-2" id="local_transfer_dropoff_zone" name="dropoff_zone_id" disabled style="padding-left: 45px; padding-right: 45px;">
                                                        <option value="">Select pickup location first</option>
                                                        <!-- Ports -->
                                                        <optgroup label="Ports">
                                                        @foreach($ports as $port)
                                                        <option value="{{ $port->id }}" data-port="{{ json_encode($port) }}">{{ $port->port_name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                        <!-- Hotels -->
                                                        <optgroup label="Hotels">
                                                        @foreach($hotels as $hotel)
                                                        <option value="hotel_{{ $hotel->id }}" data-hotel="{{ json_encode($hotel) }}">{{ $hotel->name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                        
                                                        <!-- Attractions -->
                                                        <optgroup label="Attractions">
                                                        @foreach($attractions as $attraction)
                                                        <option value="attraction_{{ $attraction->id }}" data-attraction="{{ json_encode($attraction) }}">{{ $attraction->name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                        
                                                        <!-- Restaurants -->
                                                        <optgroup label="Restaurants">
                                                        @foreach($restaurants as $restaurant)
                                                        <option value="restaurant_{{ $restaurant->id }}" data-restaurant="{{ json_encode($restaurant) }}">{{ $restaurant->name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                    </select>
                                                    <i class="ri-map-pin-fill position-absolute text-danger" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                    <button type="button" class="btn btn-sm position-absolute" style="right: 8px; top: 50%; transform: translateY(-50%); z-index: 5; border: none; background: none;" onclick="clearLocalTransferDropoffZone()">
                                                        <i class="ri-close-line text-muted"></i>
                                                    </button>
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
                                                    <i class="ri-time-fill position-absolute text-warning" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold text-muted mb-2">
                                                    <i class="ri-calendar-line text-primary me-2"></i>Pick Up Date
                                                </label>
                                                <div class="position-relative">
                                                    <input type="date" class="form-control border-2" id="local_transfer_pickup_date" name="pickup_date" value="{{ \Carbon\Carbon::parse($tour->check_in_time)->format('Y-m-d') }}" readonly style="padding-left: 45px;">
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
                                                    <select type="text" class="form-control border-2" id="local_transfer_point_pickup_location" name="point_pickup_location" placeholder="Search for pickup location..." style="padding-left: 45px;">
                                                        <option value="">Select pickup location</option>
                                                        <optgroup label="Hotels">
                                                        @foreach($hotels as $hotel)
                                                        <option value="{{ $hotel->hotel_unique_id }}" data-hotel="{{ json_encode($hotel) }}">{{ $hotel->name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                        <optgroup label="Attractions">
                                                        @foreach($attractions as $attraction)
                                                        <option value="{{ $attraction->attraction_id }}" data-attraction="{{ json_encode($attraction) }}">{{ $attraction->name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                        <optgroup label="Restaurants">
                                                        @foreach($restaurants as $restaurant)
                                                        <option value="{{ $restaurant->restaurant_id }}" data-restaurant="{{ json_encode($restaurant) }}">{{ $restaurant->name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                    </select>
                                                    <i class="ri-search-line position-absolute text-success location-icon"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 point-to-point-fields">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold text-muted mb-2">
                                                    <i class="ri-map-pin-line text-danger me-2"></i>Drop Off Location
                                                </label>
                                                <div class="position-relative location-input">
                                                <select type="text" class="form-control border-2" id="local_transfer_point_dropoff_location" name="point_dropoff_location" placeholder="Search for pickup location..." style="padding-left: 45px;">
                                                        <option value="">Select dropoff location</option>
                                                        <optgroup label="Hotels">
                                                        @foreach($hotels as $hotel)
                                                        <option value="{{ $hotel->hotel_unique_id }}" data-hotel="{{ json_encode($hotel) }}">{{ $hotel->name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                        <optgroup label="Attractions">
                                                        @foreach($attractions as $attraction)
                                                        <option value="{{ $attraction->attraction_id }}" data-attraction="{{ json_encode($attraction) }}">{{ $attraction->name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                        <optgroup label="Restaurants">
                                                        @foreach($restaurants as $restaurant)
                                                        <option value="{{ $restaurant->restaurant_id }}" data-restaurant="{{ json_encode($restaurant) }}">{{ $restaurant->name }}</option>
                                                        @endforeach
                                                        </optgroup>
                                                    </select>
                                                    <i class="ri-map-pin-fill position-absolute text-danger location-icon"></i>
                                                </div>
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
                                                    <i class="ri-time-fill position-absolute text-warning" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 point-to-point-fields">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold text-muted mb-2">
                                                    <i class="ri-calendar-line text-primary me-2"></i>Pick Up Date
                                                </label>
                                                <div class="position-relative">
                                                    <input type="date" class="form-control border-2" id="local_transfer_point_pickup_date" name="point_pickup_date" value="{{ \Carbon\Carbon::parse($tour->check_in_time)->format('Y-m-d') }}" style="padding-left: 45px;">
                                                    <i class="ri-calendar-fill position-absolute text-primary" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-primary w-100 py-2" onclick="searchLocalTransferVehicles()" id="local_transfer_point_to_point_search_btn" disabled>
                                                <i class="ri-search-line me-2"></i>Search Vehicles
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Hourly Fields (Hidden Initially) -->
                                <div class="col-12">
                                    <div id="hourly_fields" class="hourly-fields row g-4 col-12 align-items-end d-none">
                                        <div class="col-md-4 hourly-fields">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold text-muted mb-2">
                                                    <i class="ri-map-pin-line text-success me-2"></i>Pick Up Location
                                                </label>
                                                <div class="position-relative location-input">
                                                    <input type="text" class="form-control border-2" id="local_transfer_hourly_pickup_location" name="hourly_pickup_location" placeholder="Search for pickup location..." style="padding-left: 45px;">
                                                    <i class="ri-search-line position-absolute text-success location-icon"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 hourly-fields">
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
                                                    <i class="ri-time-fill position-absolute text-warning" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 hourly-fields">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold text-muted mb-2">
                                                    <i class="ri-calendar-line text-primary me-2"></i>Pick Up Date
                                                </label>
                                                <div class="position-relative">
                                                    <input type="date" class="form-control border-2" id="local_transfer_hourly_pickup_date" name="hourly_pickup_date" value="{{ \Carbon\Carbon::parse($tour->check_in_time)->format('Y-m-d') }}" style="padding-left: 45px;">
                                                    <i class="ri-calendar-fill position-absolute text-primary" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-primary w-100 py-2" onclick="searchLocalTransferVehicles()" id="local_transfer_hourly_search_btn" disabled>
                                                <i class="ri-search-line me-2"></i>Search Vehicles
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Vehicle Results Section (Hidden Initially) -->
                            <div class="row mt-4" id="local_transfer_vehicle_results" d-none>
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
                                        <div class="col-md-8">
                                            <label class="form-label fw-semibold">Vehicle</label>
                                            <select class="form-select vehicle-select" 
                                                    id="local_transfer_vehicle_id" 
                                                    name="vehicle_id" 
                                                    onchange="updateLocalTransferVehicleDetails()">
                                                <option value="">Choose vehicle</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
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
                                    </div>
                                    
                                    <!-- Guest Information -->
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="form-label fw-semibold">Number of Passengers</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="ri-user-line"></i></span>
                                                    <input type="number" class="form-control" id="local_transfer_passengers" name="passengers" min="1" value="1" onchange="updateLocalTransferPricing()">
                                                </div>
                                            </div>
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-info" onclick="confirmLocalTransferSelection()">
                            <i class="ri-check-line me-1"></i>Confirm Local Transfer Selection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End of Local Transfer Selection Modal -->

@endsection

@section('scripts')
<script>
    // Essential functions for edit page functionality
    // Show notification function
    function showNotification(message, type = 'info') {
        const alertClass = type === 'error' ? 'alert-danger' : 
                         type === 'success' ? 'alert-success' : 
                         type === 'warning' ? 'alert-warning' : 'alert-info';
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.container-xxl');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    }
    
    // Service addition functions
    function addHotelService() {
        const tourId = document.getElementById('tour_id').value;
        const country = document.getElementById('user_country').value;
        const city = document.getElementById('city').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (!tourId) {
            showNotification('Tour ID is required', 'error');
            return;
        }
        
        // Populate modal with tour data
        document.getElementById('modal_tour_id').value = tourId;
        document.getElementById('modal_user_country').value = country;
        document.getElementById('modal_city').value = city;
        document.getElementById('modal_tour_dates').textContent = `${startDate} to ${endDate}`;
        document.getElementById('modal_destination').textContent = `${city}, ${country}`;
        document.getElementById('modal_city_display').textContent = city;
        document.getElementById('modal_city_display2').textContent = city;
        
        // Set date range constraints
        const checkInDate = document.getElementById('check_in_date');
        const checkOutDate = document.getElementById('check_out_date');
        
        checkInDate.min = startDate;
        checkInDate.max = endDate;
        checkOutDate.min = startDate;
        checkOutDate.max = endDate;
        
        // Set default dates
        checkInDate.value = startDate;
        checkOutDate.value = endDate;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('hotelBookingModal'));
        modal.show();
        
        // Initialize modal functionality
        initializeHotelModal();
    }
    
    function addGuideService() {
        const tourId = document.getElementById('tour_id').value;
        const country = document.getElementById('user_country').value;
        const city = document.getElementById('city').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (!tourId) {
            showNotification('Tour ID is required', 'error');
            return;
        }
        
        // Show guide selection modal
        showGuideSelectionModal(tourId, country, city, startDate, endDate);
    }
    
    // Attraction Selection Modal Functions
    function showAttractionSelectionModal(tourId, country, city, startDate, endDate) {
        console.log('showAttractionSelectionModal called with:', { tourId, country, city, startDate, endDate });
        
        // Populate modal with tour data
        document.getElementById('modal_attraction_tour_dates').textContent = `${startDate} to ${endDate}`;
        document.getElementById('modal_attraction_destination').textContent = `${city}, ${country}`;
        document.getElementById('modal_attraction_city').textContent = city;
        
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
        
        // Initialize modal functionality after modal is shown
        setTimeout(() => {
            initializeAttractionModal();
        }, 100);
        
        // Load attractions for the city
        loadAttractionsForCity(city, country);
    }
    
    function initializeAttractionModal() {
        // Add event listeners only for elements that exist
        const attractionSelect = document.getElementById('modal_attraction_select');
        const timeSlotSelect = document.getElementById('modal_attraction_time_slot');
        const ticketSelect = document.getElementById('modal_attraction_ticket');
        const confirmBtn = document.getElementById('confirm_attraction_btn');
        
        if (attractionSelect) {
            attractionSelect.addEventListener('change', onAttractionSelection);
        }
        if (timeSlotSelect) {
            timeSlotSelect.addEventListener('change', validateAttractionForm);
        }
        if (ticketSelect) {
            ticketSelect.addEventListener('change', validateAttractionForm);
        }
        if (confirmBtn) {
            confirmBtn.addEventListener('click', confirmAttractionSelection);
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
            child_ages: document.getElementById('child_ages')?.value || ''
        };
    }
    
    function loadAttractionsForCity(city, country) {
        const attractionSelect = document.getElementById('modal_attraction_select');
        const attractionCount = document.getElementById('attraction_count');
        const timeSlotSelect = document.getElementById('modal_attraction_time_slot');
        const ticketSelect = document.getElementById('modal_attraction_ticket');
        
        // Clear existing options
        attractionSelect.innerHTML = '<option value="">Search Attraction</option>';
        
        // For demo purposes, show sample attractions
        // In production, this would fetch from API
        const attractions = @json($attractions ?? []);
        
        // Add attraction options
        attractions.forEach(attraction => {
            const option = document.createElement('option');
            option.value = attraction.attraction_id;
            option.textContent = `${attraction.name} - ${attraction.city}`;
            option.setAttribute('data-attraction', JSON.stringify(attraction));
            attractionSelect.appendChild(option);
            
        });
        
        attractionCount.textContent = attractions.length;
    }
    
    function onAttractionSelection() {
        const attractionSelect = document.getElementById('modal_attraction_select');
        const attractionDetailsContainer = document.getElementById('attraction_details_container');
        const selectedOption = attractionSelect.options[attractionSelect.selectedIndex];
        const timeSlotSelect = document.getElementById('modal_attraction_time_slot');
        const ticketSelect = document.getElementById('modal_attraction_ticket');
        
        // Clear dependent dropdowns
        timeSlotSelect.innerHTML = '<option value="">Select Time Slot</option>';
        ticketSelect.innerHTML = '<option value="">Select Ticket</option>';
        
        if (attractionSelect.value) {
            const attractionData = JSON.parse(selectedOption.getAttribute('data-attraction'));
            
            // Show attraction details
            document.getElementById('selected_attraction_image').src = attractionData.master_image || '/assets/images/default-attraction.png';
            document.getElementById('selected_attraction_name').textContent = attractionData.name;
            document.getElementById('selected_attraction_category').textContent = attractionData.category + ' Category';
            document.getElementById('selected_attraction_location').textContent = attractionData.location;
            document.getElementById('selected_attraction_rating').textContent = attractionData.rating;
            document.getElementById('selected_attraction_price_range').textContent = attractionData.price_range;
            console.log('attraction selected then, Attraction data:', attractionData);

            // Set Time Slot Options
            const timeSlots = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];
            timeSlots.forEach(time => {
                const timeOption = document.createElement('option');
                timeOption.value = time;
                timeOption.textContent = time;
                timeSlotSelect.appendChild(timeOption);
            });
            
            // Set Ticket Options (based on selected attraction)
            if (attractionData.tickets) {
                attractionData.tickets.forEach(ticket => {
                    const ticketOption = document.createElement('option');
                    ticketOption.value = ticket.ticket_id;
                    ticketOption.textContent = `${ticket.name}`;
                    ticketOption.setAttribute('data-ticket', JSON.stringify(ticket));
                    ticketSelect.appendChild(ticketOption);
                });
            }
            attractionDetailsContainer.style.display = 'block';
        } else {
            attractionDetailsContainer.style.display = 'none';
        }
        
        validateAttractionForm();
    }
    function onTicketSelection() {
        const ticketSelect = document.getElementById('modal_attraction_ticket');
        const ticketPriceDisplay = document.getElementById('modal_attraction_ticket_prices');
        const selectedOption = ticketSelect.options[ticketSelect.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            const ticketData = JSON.parse(selectedOption.getAttribute('data-ticket'));
            ticketPriceDisplay.textContent = `${ticketData.name} - $${ticketData.price}`;
            
            // Update pricing calculation when ticket changes
            updateAttractionPricing();
        }
    }
    
    function validateAttractionForm() {
        const attractionSelect = document.getElementById('modal_attraction_select');
        const timeSlotSelect = document.getElementById('modal_attraction_time_slot');
        const ticketSelect = document.getElementById('modal_attraction_ticket');
        const confirmBtn = document.getElementById('confirm_attraction_btn');
        
        let isValid = true;
        
        // Check required fields
        if (!attractionSelect.value) isValid = false;
        if (!timeSlotSelect.value) isValid = false;
        if (!ticketSelect.value) isValid = false;
        
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

    function updateAttractionGuestSummary() {
        const pax = parseInt(document.getElementById('attraction_modal_pax').value);
        const children = parseInt(document.getElementById('attraction_modal_children').value);
        const infants = parseInt(document.getElementById('attraction_modal_infants').value);
        const maleCount = parseInt(document.getElementById('attraction_modal_male_count').value);
        const femaleCount = parseInt(document.getElementById('attraction_modal_female_count').value);
        const adults = pax - children; // Calculate adults as pax - children

        const summary = `${pax} pax (${adults} adults, ${children} children) - ${maleCount} male, ${femaleCount} female -${infants} infants`;
        
        // Update summary if element exists
        const summaryElement = document.getElementById('modal_attraction_guest_summary');
        if (summaryElement) {
            summaryElement.textContent = summary;
        }

        // Update badges
        const badges = document.querySelectorAll('#attractionSelectionModal .guest-badges .badge');
        if (badges.length >= 3) {
            badges[0].textContent = adults;
            badges[1].textContent = children;
            badges[2].textContent = infants;
        }

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

        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('attractionGuestSelectorModal'));
        modal.hide();

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
    function showTransportSelectionModal(tourId, country, city, startDate, endDate) {
        console.log('Showing transport selection modal with data:', { tourId, country, city, startDate, endDate });
        
        // Set hidden fields
        document.getElementById('modal_transport_tour_id').value = tourId;
        document.getElementById('modal_transport_country').value = country;
        document.getElementById('modal_transport_city').value = city;
        document.getElementById('modal_transport_start_date').value = startDate;
        document.getElementById('modal_transport_end_date').value = endDate;
        // Pickup date is now hardcoded and readonly in the HTML
        
        // Initialize the modal
        const transportModal = new bootstrap.Modal(document.getElementById('transportSelectionModal'));
        transportModal.show();
        
        // Initialize the transport modal with a slight delay to ensure DOM is ready
        setTimeout(() => {
            initializeTransportModal();
        }, 100);
    }
    
    function showLocalTransferSelectionModal(tourId, country, city, startDate, endDate) {
        console.log('Showing local transfer selection modal with data:', { tourId, country, city, startDate, endDate });
        
        // Initialize the modal
        const localTransferModal = new bootstrap.Modal(document.getElementById('localTransferSelectionModal'));
        localTransferModal.show();
        
        // Set hidden fields
        document.getElementById('local_transfer_tour_id').value = tourId;
        document.getElementById('local_transfer_country').value = country;
        document.getElementById('local_transfer_city').value = city;
        document.getElementById('local_transfer_start_date').value = startDate;
        document.getElementById('local_transfer_end_date').value = endDate;
        
        // Initialize the local transfer modal with a slight delay to ensure DOM is ready
        setTimeout(() => {
            initializeLocalTransferModal();
        }, 100);
    }
    
    function initializeTransportModal() {
        // Load zones for pickup
        loadZonesForPickup();
        
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
    
    function initializeLocalTransferModal() {
        // Set up event listeners for local transfer modal
        const localTransferPickupZoneSelect = document.getElementById('local_transfer_pickup_zone');
        if (localTransferPickupZoneSelect) {
            localTransferPickupZoneSelect.addEventListener('change', handleLocalTransferPickupZoneChange);
        }
        
        const localTransferSearchBtn = document.getElementById('local_transfer_search_btn');
        if (localTransferSearchBtn) {
            localTransferSearchBtn.addEventListener('click', searchLocalTransferVehicles);
        }
        
        // Set default service type to 'local_transfer'
        const localTransferServiceType = document.getElementById('local_transfer_service_type_local');
        if (localTransferServiceType) {
            localTransferServiceType.checked = true;
            handleLocalTransferServiceTypeChange('local_transfer');
        }
    }
    
    function loadZonesForPickup() {
        // No need to load zones as they are already populated from backend
        console.log('Pickup zones already populated from backend');
        
        // We can filter the zones based on the selected country/city if needed
        const country = document.getElementById('modal_transport_country').value;
        const city = document.getElementById('modal_transport_city').value;
        
        console.log('Current country and city:', { country, city });
        
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
            // Enable/disable based on pickup selection
            if (pickupZoneId) {
                dropoffZoneSelect.disabled = false;
                
                // Enable the search button if both pickup and dropoff are selected
                const dropoffZoneId = dropoffZoneSelect.value;
                const searchBtn = document.getElementById('local_transfer_search_btn');
                if (searchBtn) {
                    searchBtn.disabled = !dropoffZoneId;
                }
            } else {
                dropoffZoneSelect.disabled = true;
                
                // Disable search button if pickup is not selected
                const searchBtn = document.getElementById('local_transfer_search_btn');
                if (searchBtn) {
                    searchBtn.disabled = true;
                }
            }
        }
    }
    
    function clearLocalTransferDropoffZone() {
        const dropoffZoneSelect = document.getElementById('local_transfer_dropoff_zone');
        if (dropoffZoneSelect) {
            dropoffZoneSelect.value = '';
            
            // Disable search button when dropoff is cleared
            const searchBtn = document.getElementById('local_transfer_search_btn');
            if (searchBtn) {
                searchBtn.disabled = true;
            }
        }
    }
    
    function handleLocalTransferServiceTypeChange(serviceType) {
        // Get all field containers using querySelectorAll to get collections
        const local_transfer_fields = document.getElementById('local_transfer_fields');
        const point_to_point_fields = document.getElementById('point_to_point_fields');
        const hourly_fields = document.getElementById('hourly_fields');
        
        // Hide vehicle results section
        const vehicleResultsSection = document.getElementById('local_transfer_vehicle_results');
        if (vehicleResultsSection) {
            vehicleResultsSection.style.display = 'none';
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
        
        // Show the vehicle results section
        const vehicleResultsSection = document.getElementById('local_transfer_vehicle_results');
        if (vehicleResultsSection) {
            vehicleResultsSection.style.display = 'block';
        }
        
        // Load vehicles
        const vehicleSelect = document.getElementById('local_transfer_vehicle_id');
        if (vehicleSelect) {
            // Clear existing options
            vehicleSelect.innerHTML = '<option value="">Choose vehicle</option>';
            
            // Add vehicles from backend data
            const vehicles = @json($vehicles);
            
            vehicles.forEach(vehicle => {
                const option = document.createElement('option');
                option.value = vehicle.vehicle_id;
                option.textContent = `${vehicle.vehicle_name} (${vehicle.seating_capacity} seats)`;
                option.setAttribute('data-vehicle', JSON.stringify(vehicle));
                vehicleSelect.appendChild(option);
            });
        }
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
        
        // Show the vehicle results section
        const vehicleResultsSection = document.getElementById('local_transfer_vehicle_results');
        if (vehicleResultsSection) {
            vehicleResultsSection.style.display = 'block';
        }
        
        // Load vehicles
        const vehicleSelect = document.getElementById('local_transfer_vehicle_id');
        if (vehicleSelect) {
            // Clear existing options
            vehicleSelect.innerHTML = '<option value="">Choose vehicle</option>';
            
            // Add vehicles from backend data
            const vehicles = @json($vehicles);
            
            vehicles.forEach(vehicle => {
                const option = document.createElement('option');
                option.value = vehicle.vehicle_id;
                option.textContent = `${vehicle.vehicle_name} (${vehicle.seating_capacity} seats)`;
                option.setAttribute('data-vehicle', JSON.stringify(vehicle));
                vehicleSelect.appendChild(option);
            });
        }
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
        const pickupZoneId = document.getElementById('modal_transport_pickup_zone').value;
        const dropoffZoneId = document.getElementById('modal_transport_dropoff_zone').value;
        const pickupTime = document.getElementById('modal_transport_pickup_time').value;
        const pickupDate = document.getElementById('modal_transport_pickup_date').value;
        
        if (!pickupZoneId || !dropoffZoneId || !pickupTime || !pickupDate) {
            showNotification('Please fill in all required fields', 'warning');
            return;
        }
        
        console.log('Searching vehicles for:', { pickupZoneId, dropoffZoneId, pickupTime, pickupDate });
        
        // Show the vehicle results section
        const vehicleResultsSection = document.getElementById('transport_vehicle_results');
        if (vehicleResultsSection) {
            vehicleResultsSection.style.display = 'block';
        }
        
        // Load sample vehicles (replace with actual API call)
        const vehicleSelect = document.getElementById('modal_transport_vehicle_id');
        if (vehicleSelect) {
            // Clear existing options
            vehicleSelect.innerHTML = '<option value="">Choose vehicle</option>';
            
            // Add sample vehicles (replace with actual data from API)
            const vehicles = @json($vehicles);
            
            vehicles.forEach(vehicle => {
                const option = document.createElement('option');
                option.value = vehicle.vehicle_id;
                option.textContent = `${vehicle.vehicle_name} (${vehicle.seating_capacity} seats)`;
                option.setAttribute('data-vehicle', JSON.stringify(vehicle));
                vehicleSelect.appendChild(option);
            });
        }
    }
    
    function searchLocalTransferVehicles() {
        const pickupZoneId = document.getElementById('local_transfer_pickup_zone').value;
        const dropoffZoneId = document.getElementById('local_transfer_dropoff_zone').value;
        const pickupTime = document.getElementById('local_transfer_pickup_time').value;
        const pickupDate = document.getElementById('local_transfer_pickup_date').value;
        
        if (!pickupZoneId || !dropoffZoneId || !pickupTime || !pickupDate) {
            showNotification('Please fill in all required fields', 'warning');
            return;
        }
        
        console.log('Searching vehicles for local transfer:', { pickupZoneId, dropoffZoneId, pickupTime, pickupDate });
        
        // Show the vehicle results section
        const vehicleResultsSection = document.getElementById('local_transfer_vehicle_results');
        if (vehicleResultsSection) {
            vehicleResultsSection.style.display = 'block';
        }
        
        // Load vehicles
        const vehicleSelect = document.getElementById('local_transfer_vehicle_id');
        if (vehicleSelect) {
            // Clear existing options
            vehicleSelect.innerHTML = '<option value="">Choose vehicle</option>';
            
            // Add vehicles from backend data
            const vehicles = @json($vehicles);
            
            vehicles.forEach(vehicle => {
                const option = document.createElement('option');
                option.value = vehicle.vehicle_id;
                option.textContent = `${vehicle.vehicle_name} (${vehicle.seating_capacity} seats)`;
                option.setAttribute('data-vehicle', JSON.stringify(vehicle));
                vehicleSelect.appendChild(option);
            });
        }
    }
    
    function updateVehicleDetails() {
        const vehicleSelect = document.getElementById('modal_transport_vehicle_id');
        const serviceTypeSelect = document.getElementById('modal_transport_service_type');
        
        if (vehicleSelect && vehicleSelect.value && serviceTypeSelect) {
            serviceTypeSelect.disabled = false;
            updatePricing();
        }
    }
    
    function updateLocalTransferVehicleDetails() {
        const vehicleSelect = document.getElementById('local_transfer_vehicle_id');
        const serviceTypeSelect = document.getElementById('local_transfer_service_type');
        
        if (vehicleSelect && vehicleSelect.value && serviceTypeSelect) {
            serviceTypeSelect.disabled = false;
            updateLocalTransferPricing();
        }
    }
    
    function updatePricing() {
        const vehicleSelect = document.getElementById('modal_transport_vehicle_id');
        const serviceTypeSelect = document.getElementById('modal_transport_service_type');
        const passengersInput = document.getElementById('modal_transport_passengers');
        const priceDisplay = document.getElementById('transport_price_display');
        const priceDetails = document.getElementById('transport_price_details');
        
        if (!vehicleSelect || !serviceTypeSelect || !passengersInput || !priceDisplay || !priceDetails) {
            return;
        }
        
        if (vehicleSelect.value && serviceTypeSelect.value) {
            const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
            const vehicleData = JSON.parse(selectedOption.getAttribute('data-vehicle'));
            const serviceType = serviceTypeSelect.value;
            const passengers = parseInt(passengersInput.value) || 1;
            
            // Calculate base price
            let basePrice = vehicleData.base_price;
            
            // Apply service type multiplier
            const serviceMultiplier = serviceType === 'Private' ? 1.5 : 1.0;
            
            // Calculate total price
            const totalPrice = basePrice * serviceMultiplier;
            
            // Format price details
            priceDetails.innerHTML = `
                <div class="row">
                    <div class="col-md-4">Base Price: $${basePrice.toFixed(2)}</div>
                    <div class="col-md-4">Service: ${serviceType} (${serviceType === 'Private' ? '+50%' : 'Standard'})</div>
                    <div class="col-md-4"><strong>Total: $${totalPrice.toFixed(2)}</strong></div>
                </div>
                <div class="small mt-2">
                    <i class="ri-information-line me-1"></i>
                    Vehicle: ${vehicleData.vehicle_name} (${vehicleData.seating_capacity} seats) - ${passengers} passengers
                </div>
            `;
            
            // Update hidden fields
            document.getElementById('modal_transport_base_price').value = basePrice.toFixed(2);
            document.getElementById('modal_transport_total_price').value = totalPrice.toFixed(2);
            
            priceDisplay.style.display = 'block';
        } else {
            priceDisplay.style.display = 'none';
        }
    }
    
    function updateLocalTransferPricing() {
        const vehicleSelect = document.getElementById('local_transfer_vehicle_id');
        const serviceTypeSelect = document.getElementById('local_transfer_service_type');
        const passengersInput = document.getElementById('local_transfer_passengers');
        const priceDisplay = document.getElementById('local_transfer_price_display');
        const priceDetails = document.getElementById('local_transfer_price_details');
        
        if (!vehicleSelect || !serviceTypeSelect || !passengersInput || !priceDisplay || !priceDetails) {
            return;
        }
        
        if (vehicleSelect.value && serviceTypeSelect.value) {
            const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
            const vehicleData = JSON.parse(selectedOption.getAttribute('data-vehicle'));
            const serviceType = serviceTypeSelect.value;
            const passengers = parseInt(passengersInput.value) || 1;
            
            // Calculate base price
            let basePrice = vehicleData.base_price;
            
            // Apply service type multiplier
            const serviceMultiplier = serviceType === 'Private' ? 1.5 : 1.0;
            
            // Calculate total price
            const totalPrice = basePrice * serviceMultiplier;
            
            // Format price details
            priceDetails.innerHTML = `
                <div class="row">
                    <div class="col-md-4">Base Price: $${basePrice.toFixed(2)}</div>
                    <div class="col-md-4">Service: ${serviceType} (${serviceType === 'Private' ? '+50%' : 'Standard'})</div>
                    <div class="col-md-4"><strong>Total: $${totalPrice.toFixed(2)}</strong></div>
                </div>
                <div class="small mt-2">
                    <i class="ri-information-line me-1"></i>
                    Vehicle: ${vehicleData.vehicle_name} (${vehicleData.seating_capacity} seats) - ${passengers} passengers
                </div>
            `;
            
            // Update hidden fields
            document.getElementById('local_transfer_base_price').value = basePrice.toFixed(2);
            document.getElementById('local_transfer_total_price').value = totalPrice.toFixed(2);
            
            priceDisplay.style.display = 'block';
        } else {
            priceDisplay.style.display = 'none';
        }
    }
    
    function confirmTransportSelection() {
        const formData = new FormData(document.getElementById('transportSelectionForm'));
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
        
        console.log('Customer info:', customer_info);
        
        // Get selected vehicle details
        const vehicleSelect = document.getElementById('modal_transport_vehicle_id');
        const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
        const vehicleData = selectedOption ? JSON.parse(selectedOption.getAttribute('data-vehicle')) : {};
        
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
        
        // Get location details
        const pickupZoneSelect = document.getElementById('modal_transport_pickup_zone');
        const dropoffZoneSelect = document.getElementById('modal_transport_dropoff_zone');
        const pickupZoneName = pickupZoneSelect.options[pickupZoneSelect.selectedIndex].text;
        const dropoffZoneName = dropoffZoneSelect.options[dropoffZoneSelect.selectedIndex].text;
        
        // Determine location types based on the value prefix
        const dropoffValue = dropoffZoneSelect.value;
        let dropoffLocationType = 'port';
        
        if (dropoffValue.startsWith('hotel_')) {
            dropoffLocationType = 'hotel';
        } else if (dropoffValue.startsWith('attraction_')) {
            dropoffLocationType = 'attraction';
        } else if (dropoffValue.startsWith('restaurant_')) {
            dropoffLocationType = 'restaurant';
        }
        
        // Check if this is a local transfer or transport service
        const isLocalTransfer = document.getElementById('transportSelectionModalLabel').innerHTML.includes('Local Transfer');
        const serviceTypeLabel = isLocalTransfer ? 'local_transfer' : 'travel_point';
        
        // Build the transport booking data
        const transportData = {
            id: Date.now().toString(), // Generate a unique ID
            travel_type: serviceTypeLabel,
            type: serviceTypeLabel,
            vehicles_id: vehicleId,
            vehicles_name: vehicleData.name || 'Vehicle',
            entrypickup: pickupZoneName,
            entrydropoff: dropoffZoneName,
            pickupdate: pickupDate,
            pickuptime: pickupTime,
            adults: passengers,
            children: '0',
            infants: '0',
            PickupPlaceid: pickupZoneId,
            DropoffPlaceid: dropoffZoneId,
            dropoff_location_type: dropoffLocationType,
            basePrice: vehicleData.base_price || 0,
            totalPrice: totalPrice,
            service_type: serviceType,
            bookingType: 'enquiry',
            dmc_id: 4, // Replace with actual DMC ID
            agent_id: document.getElementById('agent_id').value,
            fullName: customer_info.fullName,
            email: customer_info.email,
            phone: customer_info.phone,
            countryCode: customer_info.countryCode,
            address1: customer_info.address1,
            address2: customer_info.address2,
            state: customer_info.state,
            zip: customer_info.zip,
            specialRequests: customer_info.specialRequests,
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
        };
        
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
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('transportSelectionModal'));
        modal.hide();
        
        // Show success message
        const serviceLabel = isLocalTransfer ? 'Local transfer' : 'Transport';
        showNotification(`${serviceLabel} service booked successfully! From: ${pickupZoneName} To: ${dropoffZoneName}`, 'success');
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
        const vehicleData = selectedOption ? JSON.parse(selectedOption.getAttribute('data-vehicle')) : {};
        
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
        
        // Determine location types based on the value prefix
        const dropoffValue = dropoffZoneSelect.value;
        let dropoffLocationType = 'port';
        
        if (dropoffValue.startsWith('hotel_')) {
            dropoffLocationType = 'hotel';
        } else if (dropoffValue.startsWith('attraction_')) {
            dropoffLocationType = 'attraction';
        } else if (dropoffValue.startsWith('restaurant_')) {
            dropoffLocationType = 'restaurant';
        }
        
        // Build the transport booking data
        const transportData = {
            id: Date.now().toString(), // Generate a unique ID
            travel_type: 'local_transfer', // Local transfer
            type: 'local_transfer',
            vehicles_id: vehicleId,
            vehicles_name: vehicleData.name || 'Vehicle',
            entrypickup: pickupZoneName,
            entrydropoff: dropoffZoneName,
            pickupdate: pickupDate,
            pickuptime: pickupTime,
            adults: passengers,
            children: '0',
            infants: '0',
            PickupPlaceid: pickupZoneId,
            DropoffPlaceid: dropoffZoneId,
            dropoff_location_type: dropoffLocationType,
            basePrice: vehicleData.base_price || 0,
            totalPrice: totalPrice,
            service_type: serviceType,
            bookingType: 'enquiry',
            dmc_id: 4, // Replace with actual DMC ID
            agent_id: document.getElementById('agent_id').value,
            fullName: customer_info.fullName,
            email: customer_info.email,
            phone: customer_info.phone,
            countryCode: customer_info.countryCode,
            address1: customer_info.address1,
            address2: customer_info.address2,
            state: customer_info.state,
            zip: customer_info.zip,
            specialRequests: customer_info.specialRequests,
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
        };
        
        console.log('Local transfer booking data:', transportData);
        
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
            transport_type: 'local_transfer' // Specify local transfer type
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
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('localTransferSelectionModal'));
        modal.hide();
        
        // Show success message
        showNotification(`Local transfer service booked successfully! From: ${pickupZoneName} To: ${dropoffZoneName}`, 'success');
    }
    
    const attractionBaseUrl = "{{ route('orders.attractions.select') }}";
    function confirmAttractionSelection() {
        const formData = new FormData(document.getElementById('attractionSelectionForm'));
        const attractionId = formData.get('attraction_id');
        const timeSlot = formData.get('time_slot');
        const ticketId = formData.get('modal_attraction_ticket');
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
        const city = document.getElementById('city').value;
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
            bookingDate: startDate,
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
            adults: guestData.adults,
            children: guestData.children,
            infants: guestData.infants,
            male_count: guestData.male_count,
            female_count: guestData.female_count,
            child_ages: guestData.child_ages,
            country: country,
            city: city,
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

        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('attractionSelectionModal'));
        modal.hide();
        
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
        const city = document.getElementById('city').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        console.log('Tour data:', { tourId, country, city, startDate, endDate });
        
        if (!tourId) {
            showNotification('Tour ID is required', 'error');
            return false;
        }
        
        // Show attraction selection modal
        showAttractionSelectionModal(tourId, country, city, startDate, endDate);
        return false;
    }
    
    function addTransportService() {
        console.log('addTransportService called');
        
        const tourId = document.getElementById('tour_id').value;
        const country = document.getElementById('user_country').value;
        const city = document.getElementById('city').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        console.log('Tour data for transport:', { tourId, country, city, startDate, endDate });
        
        if (!tourId) {
            showNotification('Tour ID is required', 'error');
            return false;
        }
        
        // Show transport selection modal
        showTransportSelectionModal(tourId, country, city, startDate, endDate);
        return false;
    }
    
    function addLocalTransferService() {
        console.log('addLocalTransferService called');
        
        const tourId = document.getElementById('tour_id').value;
        const country = document.getElementById('user_country').value;
        const city = document.getElementById('city').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        console.log('Tour data for local transfer:', { tourId, country, city, startDate, endDate });
        
        if (!tourId) {
            showNotification('Tour ID is required', 'error');
            return false;
        }
        
        // Show local transfer selection modal (using the same modal as transport)
        showLocalTransferSelectionModal(tourId, country, city, startDate, endDate);
        return false;
    }

    function addRestaurantService() {
        const tourId = document.getElementById('tour_id').value;
        const country = document.getElementById('user_country').value;
        const city = document.getElementById('city').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (!tourId) {
            showNotification('Tour ID is required', 'error');
            return;
        }
        
        // Show restaurant selection modal
        showRestaurantSelectionModal(tourId, country, city, startDate, endDate);
    }
    

    
    // Hotel Modal Functions
    function initializeHotelModal() {
        // Load hotels for the city
        loadHotelsForCity();
        
        // Add event listeners
        document.getElementById('check_in_date').addEventListener('change', updateNightsDisplay);
        document.getElementById('check_out_date').addEventListener('change', updateNightsDisplay);
        document.getElementById('hotel_select').addEventListener('change', onHotelSelection);
        document.getElementById('proceed_hotel_btn').addEventListener('click', proceedToHotelSelection);
    }
    
    function loadHotelsForCity() {
        const city = document.getElementById('modal_city').value;
        const country = document.getElementById('modal_user_country').value;
        
        // Get actual hotels data from the view
        const actualHotels = @json($hotels);
        const hotelSelect = document.getElementById('hotel_select');
        const hotelCount = document.getElementById('hotel_count');
        
        // Clear existing options
        hotelSelect.innerHTML = '<option value="">Select a hotel in ' + city + '</option>';
        
        if (actualHotels && actualHotels.length > 0) {
            // Add actual hotels from database
            actualHotels.forEach(hotel => {
                const option = document.createElement('option');
                option.value = hotel.hotel_unique_id;
                option.textContent = hotel.name || hotel.hotel_name || 'Hotel ' + hotel.id;
                option.setAttribute('data-hotel', JSON.stringify(hotel));
                hotelSelect.appendChild(option);
            });
            
            hotelCount.textContent = actualHotels.length;
        } else {
            // Fallback if no hotels found
            hotelCount.textContent = '0';
            hotelSelect.innerHTML = '<option value="">No hotels found in ' + city + '</option>';
        }
    }
    
    function onHotelSelection() {
        const hotelId = document.getElementById('hotel_select').value;
        const roomType = document.getElementById('room_type');
        const bedType = document.getElementById('bed_type');
        const mealPlan = document.getElementById('meal_plan');
        
        if (hotelId) {
            // Get the selected hotel data
            const hotelSelect = document.getElementById('hotel_select');
            const selectedOption = hotelSelect.querySelector(`option[value="${hotelId}"]`);
            const hotelData = JSON.parse(selectedOption.getAttribute('data-hotel'));
            
            // Load room options for selected hotel
            loadRoomOptions(hotelData, roomType, bedType, mealPlan);
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
         fetch(`/fetch-beds-by-room?room_id=${roomId}`)
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
        const checkIn = document.getElementById('check_in_date').value;
        const checkOut = document.getElementById('check_out_date').value;
        const nightsDisplay = document.getElementById('selected_nights_display');
        const nightsList = document.getElementById('nights_list');
        const noNightsAlert = document.getElementById('no_nights_alert');
        const proceedBtn = document.getElementById('proceed_hotel_btn');
        
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
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('hotelBookingModal'));
        modal.hide();
        
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
        const selectedMealPlan = mealPlan.value || 'Room Only';
        
        // Get bed data from the selected bed option
        let bedData = {};
        if (selectedBedOption && selectedBedOption.getAttribute('data-bed')) {
            try {
                bedData = JSON.parse(selectedBedOption.getAttribute('data-bed'));
            } catch (e) {
                console.log('Error parsing bed data:', e);
            }
        }
        
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
            rooms: [
                {
                    room_id: parseInt(roomId) || 2,
                    room_type: selectedRoomType,
                    beds: [
                        {
                            bed_id: parseInt(bedId) || 1,
                            bed_type: selectedBedType,
                            max_occupancy: parseInt(maxOccupancy) || 1,
                            mealTypes: [selectedMealPlan],
                            selectedMeals: {
                                meal_1: {
                                    type: selectedMealPlan,
                                    price: parseFloat(bedData.price) || 190
                                }
                            },
                            head_count: parseInt(maxOccupancy) || 1,
                            price: parseFloat(bedData.price) || 190,
                            baby_cot: parseInt(bedData.baby_cot) || 0,
                            room_type: selectedRoomType
                        }
                    ]
                }
            ],
            bookingType: "booking",
            totalPrice: parseFloat(bedData.price) || 190,
            priceMode: "dmc",
            priceModeId: parseInt(hotelData.dmc_id) || 4,
            hotelDetails: {
                hotel_id: hotelId,
                hotel_name: hotelData.name || "Hotel",
                location: hotelData.location || "Location",
                image: hotelData.image || "",
                cancellation_charge: []
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
    function showGuideSelectionModal(tourId, country, city, startDate, endDate) {
        // Populate modal with tour data
        document.getElementById('modal_guide_tour_dates').textContent = `${startDate} to ${endDate}`;
        document.getElementById('modal_guide_destination').textContent = `${city}, ${country}`;
        document.getElementById('modal_guide_city').textContent = city;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('guideSelectionModal'));
        modal.show();
        
        // Initialize modal functionality
        initializeGuideModal();
        
        // Load guides for the city
        loadGuidesForCity(city, country);
    }
    
    function initializeGuideModal() {
        // Add event listeners
        document.getElementById('modal_guide_select').addEventListener('change', onGuideSelection);
        document.getElementById('modal_guide_duration').addEventListener('change', onDurationSelection);
        document.getElementById('modal_guide_custom_hours').addEventListener('input', validateCustomHours);
        document.getElementById('modal_guide_pickup_time').addEventListener('change', validateForm);
        document.getElementById('confirm_guide_btn').addEventListener('click', confirmGuideSelection);
        // Set default pickup time to 9:00 AM
        document.getElementById('modal_guide_pickup_time').value = '09:00';
    }
    
    function loadGuidesForCity(city, country) {
        const guideSelect = document.getElementById('modal_guide_select');
        const guideCount = document.getElementById('guide_count');
        
        // Clear existing options
        guideSelect.innerHTML = '<option value="">Search Guide</option>';
        
        // For demo purposes, show sample guides
        // In production, this would fetch from API
        const guides = @json($guides);
        
        // Add guide options
        guides.forEach(guide => {
            const option = document.createElement('option');
            option.value = guide.guide_id;
            option.textContent = `${guide.name} - ${guide.specialty} (${guide.rating}★)`;
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
        const guideSelect = document.getElementById('modal_guide_select');
        const durationSelect = document.getElementById('modal_guide_duration');
        const customHours = document.getElementById('modal_guide_custom_hours');
        const pickupTime = document.getElementById('modal_guide_pickup_time');
        const confirmBtn = document.getElementById('confirm_guide_btn');
        
        let isValid = true;
        
        // Check required fields
        if (!guideSelect.value) isValid = false;
        if (!durationSelect.value) isValid = false;
        if (durationSelect.value === 'custom' && (!customHours.value || customHours.value < 1 || customHours.value > 24)) isValid = false;
        if (!pickupTime.value) isValid = false;
        
        confirmBtn.disabled = !isValid;
    }

    const guideBaseUrl = "{{ route('orders.guides.select') }}";
    function confirmGuideSelection() {
        
        const formData = new FormData(document.getElementById('guideSelectionForm'));
        const guideId = formData.get('guide_id');
        const duration = formData.get('duration');
        const customHours = formData.get('custom_hours');
        const pickupTime = formData.get('pickup_time');
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
        const city = document.getElementById('city').value;
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
            bookingDate: startDate,
            guide_id: guideId,
            guide_name: guideData.name || "Guide Name",
            image: guideData.image || "",
            dmc_Id: guideData.dmc_id || "11",
            Mode: "dmc",
            entrypickup: `${city}, (${country})`,
            PickupPlaceid: null,
            DropoffPlaceid: null,
            pickupdate: startDate,
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
            adults: adults,
            children: children,
            country: country,
            city: city,
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

        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('guideSelectionModal'));
        modal.hide();
        
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
    function showRestaurantSelectionModal(tourId, country, city, startDate, endDate) {
        // Populate modal with tour data
        document.getElementById('modal_restaurant_tour_dates').textContent = `${startDate} to ${endDate}`;
        document.getElementById('modal_restaurant_destination').textContent = `${city}, ${country}`;
        document.getElementById('modal_restaurant_city').textContent = city;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('restaurantSelectionModal'));
        modal.show();
        
        // Initialize modal functionality after modal is shown
        setTimeout(() => {
        initializeRestaurantModal();
        }, 100);
        
        // Load restaurants for the city
        loadRestaurantsForCity(city, country);
    }
    
    function initializeRestaurantModal() {
        // Add event listeners only for elements that exist
        const restaurantSelect = document.getElementById('modal_restaurant_select');
        const mealTypeSelect = document.getElementById('modal_restaurant_meal_type');
        const dishSelect = document.getElementById('modal_restaurant_dish');
        const timeSlotSelect = document.getElementById('modal_restaurant_time_slot');
        const confirmBtn = document.getElementById('confirm_restaurant_btn');
        
        if (restaurantSelect) {
            restaurantSelect.addEventListener('change', onRestaurantSelection);
        }
        if (mealTypeSelect) {
            mealTypeSelect.addEventListener('change', validateRestaurantForm);
        }
        if (dishSelect) {
            dishSelect.addEventListener('change', validateRestaurantForm);
        }
        if (timeSlotSelect) {
            timeSlotSelect.addEventListener('change', validateRestaurantForm);
        }
        if (confirmBtn) {
            confirmBtn.addEventListener('click', confirmRestaurantSelection);
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
            child_ages: document.getElementById('child_ages')?.value || ''
        };
    }
    
    function loadRestaurantsForCity(city, country) {
        const restaurantSelect = document.getElementById('modal_restaurant_select');
        const restaurantCount = document.getElementById('restaurant_count');
        const mealSelect = document.getElementById('modal_restaurant_meal_type');
        const dishSelect = document.getElementById('modal_restaurant_dish');
        // Clear existing options
        restaurantSelect.innerHTML = '<option value="">Search Restaurant</option>';
        
        // For demo purposes, show sample restaurants
        // In production, this would fetch from API
        const restaurants = @json($restaurants);
        
        // Add restaurant options
        restaurants.forEach(restaurant => {
            const option = document.createElement('option');
            option.value = restaurant.restaurant_id;
            option.textContent = `${restaurant.name} - ${restaurant.city}`;
            option.setAttribute('data-restaurant', JSON.stringify(restaurant));
            restaurantSelect.appendChild(option);
            
        });
        
        restaurantCount.textContent = restaurants.length;
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
        const restaurantDetailsContainer = document.getElementById('restaurant_details_container');
        const selectedOption = restaurantSelect.options[restaurantSelect.selectedIndex];
        const mealSelect = document.getElementById('modal_restaurant_meal_type');
        const dishSelect = document.getElementById('modal_restaurant_dish');
        const timeSlotSelect = document.getElementById('modal_restaurant_time_slot');
        
        
        
        // Clear dependent dropdowns
        mealSelect.innerHTML = '<option value="">Select Meal</option>';
        dishSelect.innerHTML = '<option value="">Select Dish</option>';
        timeSlotSelect.innerHTML = '<option value="">Select Time Slot</option>';
        
        if (restaurantSelect.value) {
            const restaurantData = JSON.parse(selectedOption.getAttribute('data-restaurant'));
            
            // Show restaurant details
            document.getElementById('selected_restaurant_image').src = restaurantData.master_image || '/assets/images/default-restaurant.png';
            document.getElementById('selected_restaurant_name').textContent = restaurantData.name;
            document.getElementById('selected_restaurant_cuisine').textContent = restaurantData.cuisine + ' Cuisine';
            document.getElementById('selected_restaurant_location').textContent = restaurantData.location;
            document.getElementById('selected_restaurant_rating').textContent = restaurantData.rating;
            document.getElementById('selected_restaurant_price_range').textContent = restaurantData.price_range;
            console.log('restaurant selected then, Restaurant data:', restaurantData);

            // Set Meal Options
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
                console.log('mealName:', mealName);
                const mealOption = document.createElement('option');
                mealOption.value = meal.meal_id;
                mealOption.textContent = mealName;
                mealOption.setAttribute('data-meal', JSON.stringify(meal));
                mealSelect.appendChild(mealOption);
            });
            
            // Set Dish Options (based on selected meal)
            mealSelect.addEventListener('change', function() {
                const selectedMealOption = mealSelect.options[mealSelect.selectedIndex];
                const mealData = JSON.parse(selectedMealOption.getAttribute('data-meal'));
                const pax = getPax();
                const adultPrice = mealData.adult_price * (pax.maleCount + pax.femaleCount);
                const childPrice = mealData.child_price * pax.children;
                const totalPrice = adultPrice + childPrice;
                
                const mealPriceSection = document.getElementById('meal-price-section');
                mealPriceSection.textContent = 'Adult Price: '+adultPrice + ' - ' + 'Child Price: '+childPrice+', Total Price: '+totalPrice;

                if (selectedMealOption.value) {
                    dishSelect.innerHTML = '<option value="">Select Dish</option>';
                    // Add dish options based on meal data
                    const dishOption = document.createElement('option');
                    dishOption.value = mealData.meal_id;
                    dishOption.textContent = mealData.type == 1 ? 'Buffet' : mealData.type == 2 ? 'Set Menu' : mealData.type == 3 ? 'A-La-Carte' : '...';
                    dishOption.setAttribute('data-dish', JSON.stringify(mealData));
                    dishSelect.appendChild(dishOption);
                    
                    // Set time slots (you can customize this based on your business logic)
                    timeSlotSelect.innerHTML = '<option value="">Select Time Slot</option>';
                    const timeSlots = ['07:00', '08:00', '09:00', '12:00', '13:00', '14:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00'];
                    timeSlots.forEach(time => {
                        const timeOption = document.createElement('option');
                        timeOption.value = time;
                        timeOption.textContent = time;
                        timeSlotSelect.appendChild(timeOption);
                    });
                }
            });
            
            restaurantDetailsContainer.style.display = 'block';
        } else {
            restaurantDetailsContainer.style.display = 'none';
        }
        
        validateRestaurantForm();
    }
    
    function validateRestaurantForm() {
        const restaurantSelect = document.getElementById('modal_restaurant_select');
        const mealType = document.getElementById('modal_restaurant_meal_type');
        const dishSelect = document.getElementById('modal_restaurant_dish');
        const timeSlotSelect = document.getElementById('modal_restaurant_time_slot');
        const confirmBtn = document.getElementById('confirm_restaurant_btn');
        
        let isValid = true;
        
        // Check required fields
        if (!restaurantSelect.value) isValid = false;
        if (!mealType.value) isValid = false;
        if (!dishSelect.value) isValid = false;
        if (!timeSlotSelect.value) isValid = false;
        
        confirmBtn.disabled = !isValid;
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

        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalGuestSelectorModal'));
        modal.hide();

        showNotification('Guest selection updated successfully', 'success');
    }
    
    const restaurantBaseUrl = "{{ route('orders.restaurants.select') }}";
    
    function confirmRestaurantSelection() {
        const formData = new FormData(document.getElementById('restaurantSelectionForm'));
        const restaurantId = formData.get('restaurant_id');
        const mealType = formData.get('meal_type');
        const dishId = formData.get('modal_restaurant_dish');
        const timeSlot = formData.get('modal_restaurant_time_slot');
        const customer_info = getCustomerInfo();
        
        const agentId = document.getElementById('agent_id').value;
        
        
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
        const city = document.getElementById('city').value;
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
            bookingDate: startDate,
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
            dmc_id: restaurantData.dmc_id || "",
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
            adults: guestData.adults,
            children: guestData.children,
            infants: guestData.infants,
            male_count: guestData.male_count,
            female_count: guestData.female_count,
            child_ages: guestData.child_ages,
            country: country,
            city: city,
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

        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('restaurantSelectionModal'));
        modal.hide();
        
        // Show success message
        showNotification(`Restaurant ${restaurantData.name} selected successfully! Meal: ${mealData.meal_period == 1 ? 'Breakfast' : mealData.meal_period == 2 ? 'Lunch' : 'Dinner'} at ${timeSlot} for ${guestData.adults} adults, ${guestData.children} children`, 'success');
        
        // Here you can add logic to update the restaurant fields in your form
        
    }
    
    // Order management functions
    window.cancelExistingOrder = function(orderId) {
        if (confirm('Are you sure you want to cancel this service?')) {
            showNotification('Cancelling service...', 'info');
            
            fetch(`/api/orders/${orderId}/cancel`, {
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
            
            console.log('Making request to:', `/api/orders/${orderId}/cancel`);
            console.log('CSRF Token:', csrfToken);
            
            fetch(`/api/orders/${orderId}/cancel`, {
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
</script>
@endsection

