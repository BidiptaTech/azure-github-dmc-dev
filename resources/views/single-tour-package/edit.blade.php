@extends('layouts.layout')
@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        
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
                                    <input type="hidden" name="child_ages" id="child_ages" value="{{ $tour->child_ages ?? '[]' }}">
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
                                    <button type="button" class="btn btn-danger btn-lg w-100 h-100 d-flex flex-column align-items-center justify-content-center" onclick="addAttractionService()">
                                        <i class="ri-ticket-line fs-1 mb-2"></i>
                                        <span class="fw-bold">Attractions</span>
                                        <small class="opacity-75">Book Tickets</small>
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
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeAttractionService({{ $order->id }})">
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
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeGuideService({{ $order->id }})">
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
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRestaurantService({{ $order->id }})">
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
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTransportService({{ $order->id }})">
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
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTransportService({{ $order->id }})">
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
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTransportService({{ $order->id }})">
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
    
    function addRestaurantService() {
        const tourId = document.getElementById('tour_id').value;
        const country = document.getElementById('user_country').value;
        const city = document.getElementById('city').value;
        
        if (!tourId) {
            showNotification('Tour ID is required', 'error');
            return;
        }
        
        // Redirect to restaurant booking page with tour context
        const url = `/restaurants?tour_id=${tourId}&country=${encodeURIComponent(country)}&city=${encodeURIComponent(city)}`;
        window.location.href = url;
    }
    
    function addAttractionService() {
        const tourId = document.getElementById('tour_id').value;
        const country = document.getElementById('user_country').value;
        const city = document.getElementById('city').value;
        
        if (!tourId) {
            showNotification('Tour ID is required', 'error');
            return;
        }
        
        // Redirect to attraction booking page with tour context
        const url = `/attractions?tour_id=${tourId}&country=${encodeURIComponent(country)}&city=${encodeURIComponent(city)}`;
        window.location.href = url;
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
                option.value = hotel.id;
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
        
        // Load room types from hotel data
        if (hotelData.rooms && hotelData.rooms.length > 0) {
            // Get unique room types from rooms
            const roomTypes = [...new Set(hotelData.rooms.map(room => room.room_type || room.type || 'Standard Room'))];
            roomTypes.forEach(type => {
                const option = document.createElement('option');
                option.value = type.toLowerCase().replace(/\s+/g, '_');
                option.textContent = type;
                option.setAttribute('data-room-type', type);
                roomType.appendChild(option);
            });
            
            // Add event listener for room type selection to update bed types
            roomType.addEventListener('change', function() {
                updateBedTypesForRoom(this.value, hotelData, bedType);
            });
            
            // Initially populate bed types with a default message
            bedType.innerHTML = '<option value="">Select room type first</option>';
        
            // Load bed types from the bed field in rooms
            if (hotelData.rooms && hotelData.rooms.length > 0) {
                  // Collect all beds from all rooms using the bed field (singular)
                  const allBeds = [];
                  hotelData.rooms.forEach(room => {
                      if (room.bed && room.bed.id) {
                          // Add room context to bed data
                          const bedData = {
                              ...room.bed,
                              room_type: room.room_type || room.type,
                              hotel_room_id: room.room_id || room.id
                          };
                          allBeds.push(bedData);
                      }
                  });
                  
                  console.log('Collected beds from bed field:', allBeds);
                  
                  if (allBeds.length > 0) {
                      // Get unique bed types with detailed information from bed data
                      const bedTypes = [...new Set(allBeds.map(bed => bed.room_type || bed.bed_type || 'Standard Bed'))];
                      console.log('Unique bed types found:', bedTypes);
                      
                      bedTypes.forEach(type => {
                          const option = document.createElement('option');
                          option.value = type.toLowerCase().replace(/\s+/g, '_');
                          
                          // Create descriptive bed type text
                          let bedTypeText = type;
                          
                          // Find beds of this type to get additional info
                          const bedsOfType = allBeds.filter(bed => 
                              (bed.room_type || bed.bed_type) === type
                          );
                          
                          if (bedsOfType.length > 0) {
                              const firstBed = bedsOfType[0];
                              
                              // Add room count if available
                              if (bedsOfType.length > 1) {
                                  bedTypeText += ` (${bedsOfType.length} available)`;
                              }
                              
                              // Add occupancy info if available
                              if (firstBed.max_occupancy) {
                                  bedTypeText += ` - Max ${firstBed.max_occupancy} guests`;
                              }
                              
                              // Add adult/child info if available
                              if (firstBed.adult_count && firstBed.child_count) {
                                  bedTypeText += ` (${firstBed.adult_count}A+${firstBed.child_count}C)`;
                              }
                              
                              // Add extra bed info if available
                              if (firstBed.extra_bed) {
                                  bedTypeText += ` + Extra Bed`;
                                  if (firstBed.extra_bed_price) {
                                      bedTypeText += ` ($${firstBed.extra_bed_price})`;
                                  }
                              }
                              
                              // Add baby cot info if available
                              if (firstBed.baby_cot) {
                                  bedTypeText += ` + Baby Cot`;
                                  if (firstBed.baby_cot_price) {
                                      bedTypeText += ` ($${firstBed.baby_cot_price})`;
                                  }
                              }
                          }
                          
                          option.textContent = bedTypeText;
                          option.setAttribute('data-bed-type', type);
                          bedType.appendChild(option);
                          console.log('Added bed type option:', bedTypeText);
                      });
                  } else {
                      // No beds found in bed field - show no data message
                      console.log('No beds found in bed field');
                      bedType.innerHTML = '<option value="">No bed types available</option>';
                  }
            }
            
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
        const params = new URLSearchParams({
            tour_id: tourId,
            hotel_id: hotelId,
            check_in: checkIn,
            check_out: checkOut,
            room_type: formData.get('room_type'),
            bed_type: formData.get('bed_type'),
            meal_plan: formData.get('meal_plan')
        });
        
        window.location.href = `/hotels/select?${params.toString()}`;
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
        const sampleGuides = [
            { id: 1, name: 'John Smith', specialty: 'Cultural Tours', experience: '8 years', rating: '4.8', rate: '$25/hour', image: '/assets/images/guide1.jpg' },
            { id: 2, name: 'Maria Garcia', specialty: 'Historical Tours', experience: '5 years', rating: '4.9', rate: '$30/hour', image: '/assets/images/guide2.jpg' },
            { id: 3, name: 'David Chen', specialty: 'Food Tours', experience: '6 years', rating: '4.7', rate: '$28/hour', image: '/assets/images/guide3.jpg' },
            { id: 4, name: 'Sarah Johnson', specialty: 'Adventure Tours', experience: '7 years', rating: '4.6', rate: '$32/hour', image: '/assets/images/guide4.jpg' }
        ];
        
        // Add guide options
        sampleGuides.forEach(guide => {
            const option = document.createElement('option');
            option.value = guide.id;
            option.textContent = `${guide.name} - ${guide.specialty} (${guide.rating}★)`;
            option.setAttribute('data-guide', JSON.stringify(guide));
            guideSelect.appendChild(option);
        });
        
        guideCount.textContent = sampleGuides.length;
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
    
    function confirmGuideSelection() {
        
        const formData = new FormData(document.getElementById('guideSelectionForm'));
        const guideId = formData.get('guide_id');
        const duration = formData.get('duration');
        const customHours = formData.get('custom_hours');
        const pickupTime = formData.get('pickup_time');
        
        // Get selected guide details
        const guideSelect = document.getElementById('modal_guide_select');
        const selectedOption = guideSelect.options[guideSelect.selectedIndex];
        const guideData = JSON.parse(selectedOption.getAttribute('data-guide'));
        
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
        console.log('removeAttractionService called with orderId:', orderId);
        removeService(orderId, 'attraction');
    };
    
    window.removeGuideService = function(orderId) {
        console.log('removeGuideService called with orderId:', orderId);
        removeService(orderId, 'guide');
    };
    
    window.removeRestaurantService = function(orderId) {
        console.log('removeRestaurantService called with orderId:', orderId);
        removeService(orderId, 'restaurant');
    };
    
    window.removeTransportService = function(orderId) {
        console.log('removeTransportService called with orderId:', orderId);
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
</script>
@endsection

