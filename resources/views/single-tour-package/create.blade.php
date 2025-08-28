@extends('layouts.layout')
@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- 
        DMC-Based Pricing System:
        - Hotels and rooms are filtered by the current user's DMC ID
        - Only rooms created by the current DMC are shown
        - Pricing is calculated based on DMC-specific room prices
        - All services include DMC tracking information
    --}}
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
                                <h4 class="mb-1 text-white">Create Single Tour Package</h4>
                                <p class="mb-0 opacity-75">Design personalized tour experiences for your clients</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Form Column -->
            <div class="col-lg-{{ $enquiry ? '8' : '12' }}">
                <form id="singleTourPackageForm" method="POST" action="{{ route('single-tour-package.store') }}">
                    @csrf
            <!-- Main Form Card - All in One Row -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-gradient-primary text-white">
                            <h6 class="mb-0 fw-bold">
                                <i class="ri-settings-3-line me-2"></i>Tour Package Configuration
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Country Selection -->
                                <div class="col-md-2">
                                    <label for="user_country" class="form-label fw-semibold">
                                        <i class="ri-earth-line me-1"></i>Country
                                    </label>
                                    <select name="user_country" id="user_country" class="form-select" required {{ ($enquiry && $enquiry->country) ? 'disabled' : '' }}>
                                        <option value="">Choose a country...</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->name }}" {{ ($enquiry && $enquiry->country == $country->name) ? 'selected' : '' }}>{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                    @if($enquiry && $enquiry->country)
                                        <input type="hidden" name="user_country" value="{{ $enquiry->country }}">
                                    @endif
                                    <input type="hidden" name="country_id" id="country_id">
                                </div>

                                <!-- City Selection -->
                                <div class="col-md-2">
                                    <label for="city" class="form-label fw-semibold">
                                        <i class="ri-building-line me-1"></i>City
                                    </label>
                                    <select name="city" id="city" class="form-select" required {{ ($enquiry && $enquiry->city) ? 'disabled' : ($enquiry ? '' : 'disabled') }}>
                                        @if($enquiry && $enquiry->city)
                                            <option value="{{ $enquiry->city }}" selected>{{ $enquiry->city }}</option>
                                        @else
                                            <option value="">Select country first</option>
                                        @endif
                                    </select>
                                    @if($enquiry && $enquiry->city)
                                        <input type="hidden" name="city" value="{{ $enquiry->city }}">
                                    @endif
                                    <input type="hidden" name="city_id" id="city_id">
                                    <div id="cityLoader" class="text-center mt-1" style="display: none;">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    </div>
                                </div>

                                <!-- Travel Dates -->
                                <div class="col-md-2">
                                    <label for="travel_dates" class="form-label fw-semibold">
                                        <i class="ri-calendar-line me-1"></i>Travel Dates
                                    </label>
                                    <input type="text" id="travel_dates" class="form-control" placeholder="Select dates" readonly
                                           value="@if($enquiry && $enquiry->check_in_time && $enquiry->check_out_time){{ \Carbon\Carbon::parse($enquiry->check_in_time)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($enquiry->check_out_time)->format('M d, Y') }}@endif"
                                           {{ ($enquiry && $enquiry->check_in_time && $enquiry->check_out_time) ? 'style=background-color:#f8f9fa;cursor:not-allowed;pointer-events:none;' : '' }}
                                           {{ ($enquiry && $enquiry->check_in_time && $enquiry->check_out_time) ? 'data-locked=true' : 'data-locked=false' }}>
                                    <input type="hidden" name="start_date" id="start_date" value="{{ $enquiry && $enquiry->check_in_time ? \Carbon\Carbon::parse($enquiry->check_in_time)->format('Y-m-d') : '' }}">
                                    <input type="hidden" name="end_date" id="end_date" value="{{ $enquiry && $enquiry->check_out_time ? \Carbon\Carbon::parse($enquiry->check_out_time)->format('Y-m-d') : '' }}">
                                </div>

                                <!-- Guests -->
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">
                                        <i class="ri-group-line me-1"></i>Guests
                                    </label>
                                    <div class="guest-selector">
                                        <div class="guest-display p-2 border rounded {{ $enquiry ? 'bg-light' : 'bg-light' }}" {{ $enquiry ? 'style=cursor:not-allowed;opacity:0.8;' : '' }}>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="guest-info">
                                                    <span id="mainGuestSummary" class="text-muted small">
                                                        @if($enquiry)
                                                            {{ $enquiry->adult ?? 1 }} adults ({{ $enquiry->male_count ?? 0 }} male, {{ $enquiry->female_count ?? 0 }} female), {{ $enquiry->child ?? 0 }} children - {{ $enquiry->infant ?? 0 }} infants
                                                        @else
                                                            1 adults (0 male, 0 female), 0 children - 0 infants
                                                        @endif
                                                    </span>
                                                </div>
                                                @if(!$enquiry)
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="openMainGuestSelector()">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                                @else
                                                <span class="text-muted small">
                                                    <i class="ri-lock-line"></i>
                                                </span>
                                                @endif
                                    </div>
                                            <div class="guest-badges mt-1">
                                                <span class="badge bg-primary">{{ $enquiry ? ($enquiry->adult ?? 1) : 1 }}</span>
                                                <span class="badge bg-success">{{ $enquiry ? ($enquiry->child ?? 0) : 0 }}</span>
                                                <span class="badge bg-warning text-dark">{{ $enquiry ? ($enquiry->infant ?? 0) : 0 }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <!-- Hidden Fields -->
                                    <input type="hidden" name="adults" id="adults" value="{{ $enquiry ? ($enquiry->adult ?? 1) : 1 }}">
                                    <input type="hidden" name="male" id="male" value="{{ $enquiry ? ($enquiry->male_count ?? 0) : 0 }}">
                                    <input type="hidden" name="female" id="female" value="{{ $enquiry ? ($enquiry->female_count ?? 0) : 0 }}">
                                    <input type="hidden" name="children" id="children" value="{{ $enquiry ? ($enquiry->child ?? 0) : 0 }}">
                                    <input type="hidden" name="infants" id="infants" value="{{ $enquiry ? ($enquiry->infant ?? 0) : 0 }}">
                                    <input type="hidden" name="child_ages" id="child_ages" value="{{ $enquiry && $enquiry->child_ages ? $enquiry->child_ages : '[]' }}">
                                </div>

                                <!-- Agent Selection -->
                                <div class="col-md-2">
                                    <label for="agent_id" class="form-label fw-semibold">
                                        <i class="ri-user-star-line me-1"></i>Agent
                                    </label>
                                    <select name="agent_id" id="agent_id" class="form-select" required {{ ($enquiry && $enquiry->agent_id) ? 'disabled' : '' }}>
                                        <option value="">Choose agent...</option>
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->agent_id }}" {{ ($enquiry && $enquiry->agent_id == $agent->agent_id) ? 'selected' : '' }}>{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                    @if($enquiry && $enquiry->agent_id)
                                        <input type="hidden" name="agent_id" value="{{ $enquiry->agent_id }}">
                                    @endif
                                </div>

                                <!-- Create Button -->
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary w-100" onclick="createTourPackage()">
                                        <i class="ri-rocket-line me-1"></i>Create Tour
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Hotel Selection Section (Hidden Initially) -->
            <div class="row mb-4" id="hotelSection" style="display: none;">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-gradient-success text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">
                                    <i class="ri-hotel-line me-2"></i>Let's Book Your Hotels! 🏨
                                </h6>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-light text-dark me-2" id="tourDates">Aug 03 - Aug 07, 2025</span>
                                    <span class="badge bg-warning text-dark" id="hotelNights">4 Nights Selected</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Hotel Selection Controls -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        <i class="ri-hotel-line me-1"></i>Select Hotel
                                    </label>
                                    <select class="form-select" id="hotelSelect">
                                        <option value="">Select a city first to load hotels</option>
                                    </select>
                                    <small class="text-muted" id="hotelLoadingStatus"></small>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Room Type</label>
                                    <select class="form-select" id="roomTypeSelect">
                                        <option value="">Room Type</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Bed Type</label>
                                    <select class="form-select" id="bedTypeSelect">
                                        <option value="">Bed Type</option>
                                    </select>
                                    <div id="bedPriceDisplay" class="text-success small mt-1" style="display: none;">
                                        Price: <span class="fw-bold">$0.00</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Meal Plan</label>
                                    <select class="form-select" id="mealPlanSelect">
                                        <option value="">Select Meal Plans</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold" hidden>Number of Rooms</label>
                                    <input type="number" class="form-control" id="numberOfRooms" value="1" min="1" hidden> 
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-success w-100" onclick="addHotel()">
                                        <i class="ri-add-line"></i>
                                    </button>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-info btn-sm" onclick="testMealPricing()" title="Test meal pricing calculation">
                                        <i class="ri-test-tube-line"></i>
                                    </button>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-warning btn-sm" onclick="debugRoomData()" title="Debug room data and meal prices">
                                        <i class="ri-bug-line"></i>
                                    </button>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="testMealPriceFetching()" title="Test meal price fetching from options">
                                        <i class="ri-price-tag-line"></i>
                                    </button>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-success btn-sm" onclick="testMealPricing()" title="Test corrected meal pricing with rooms">
                                        <i class="ri-calculator-line"></i>
                                    </button>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-info btn-sm" onclick="testCurrentMealPricing()" title="Test current meal prices from dataset">
                                        <i class="ri-test-tube-line"></i>
                                    </button>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-warning btn-sm" onclick="testGuidePricing()" title="Test guide pricing calculation">
                                        <i class="ri-user-star-line"></i>
                                    </button>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="ensureGuideHiddenFields()" title="Ensure guide hidden fields exist">
                                        <i class="ri-settings-line"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Night Selection -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold mb-3">
                                    <i class="ri-calendar-check-line me-1"></i>Select Hotel Nights
                                    <small class="text-muted">(Choose nights for this hotel - consecutive nights will be automatically selected)</small>
                                </label>
                                
                                <!-- Color Legend -->
                                <div class="mb-3 p-2 bg-light rounded">
                                    <small class="fw-bold text-muted d-block mb-1">Night Selection Guide:</small>
                                    <div class="d-flex gap-3">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-success me-1">●</span>
                                            <small>Manually Selected</small>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-warning text-dark me-1">⚡</span>
                                            <small>Auto-Required (for consecutive nights)</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="nightSelection" class="d-flex flex-wrap gap-2 mb-3">
                                    <!-- Night options will be populated by JavaScript -->
                                </div>
                                <div id="nightSelectionSummary">
                                    <div class="alert alert-info">
                                        <i class="ri-information-line me-2"></i>
                                        <small>No nights selected. Click on the nights above to select hotel stay.</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Selected Hotels Display -->
                            <div id="selectedHotels">
                                <div class="alert alert-info d-flex align-items-center">
                                    <i class="ri-information-line me-2"></i>
                                    <span>No hotels selected yet. Choose your hotels above.</span>
                                </div>
                            </div>

                            <!-- Hotel Summary -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <i class="ri-hotel-bed-line me-2"></i>Total Hotels: <span id="totalHotels">0</span>
                                                    </h6>
                                                    <small class="text-muted">Consecutive hotel nights selected - applies to all rooms in this hotel</small>
                                                </div>
                                                <div class="text-end">
                                                    <div class="badge bg-primary fs-6" id="totalNights">0 Nights</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            

                            <!-- Package Total Price Summary -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="card bg-success text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <i class="ri-money-dollar-circle-line me-2"></i>Package Total Price
                                                    </h6>
                                                    <small class="text-white-50">Total cost for all selected services</small>
                                                </div>
                                                <div class="text-end">
                                                    <div class="h4 mb-0" id="packageTotalPrice">$0.00</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Price Breakdown -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">
                                                <i class="ri-list-check-2 me-2"></i>Price Breakdown
                                            </h6>
                                            <small class="text-muted">Detailed breakdown of all service costs</small>
                                        </div>
                                        <div class="card-body">
                                            <div id="priceBreakdown">
                                                <p class="text-muted">No services added yet</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            

            <!-- Transports and Other Services Section (Hidden Initially) -->
            <div class="row mb-4" id="transportSection" style="display: none;">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-gradient-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">
                                    <i class="ri-car-line me-2"></i>Transports and Other Services
                                </h6>
                                <span class="badge bg-light text-dark" id="transportDayCount">4 Days</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div id="dailyServicesContainer">
                                <!-- Daily services will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden Fields for Storing Booking Data -->
            <input type="hidden" id="tour_id" name="tour_id" value="">
            <input type="hidden" id="hotelBookings" name="hotel_bookings" value="[]">
            <input type="hidden" id="guideBookings" name="guide_bookings" value="[]">
            <input type="hidden" id="vehicleBookings" name="vehicle_bookings" value="[]">
                        <input type="hidden" id="attractionBookings" name="attraction_bookings" value="[]">
            
            <!-- DMC Information -->
            <input type="hidden" id="dmc_id" name="dmc_id" value="{{ auth()->user()->created_by }}">
            <input type="hidden" id="current_user_id" name="current_user_id" value="{{ auth()->user()->userId }}">
            <input type="hidden" id="current_user_role" name="current_user_role" value="{{ auth()->user()->role_id }}">
            
            <!-- JSON Data Fields for Service Orders -->
            <input type="hidden" id="hotel_data" name="hotel_data" value="">
            <input type="hidden" id="attraction_data" name="attraction_data" value="">
            <input type="hidden" id="restaurant_data" name="restaurant_data" value="">
            <input type="hidden" id="guide_data" name="guide_data" value="">
            <input type="hidden" id="transport_data" name="transport_data" value="">
            <input type="hidden" id="entry_port_data" name="entry_port_data" value="">
            <input type="hidden" id="exit_port_data" name="exit_port_data" value="">

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
                                    <input type="text" class="form-control" id="customerFullName" name="customer_full_name" placeholder="Enter full name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" id="customerEmail" name="customer_email" placeholder="Enter email" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Country Code</label>
                                    <input type="text" class="form-control" id="customerCountryCode" name="customer_country_code" placeholder="e.g. +91" required>
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="customerPhone" name="customer_phone" placeholder="Enter phone number" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address Line 1</label>
                                    <input type="text" class="form-control" id="customerAddress1" name="customer_address1" placeholder="Enter address line 1" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address Line 2</label>
                                    <input type="text" class="form-control" id="customerAddress2" name="customer_address2" placeholder="Enter address line 2">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">State</label>
                                    <input type="text" class="form-control" id="customerState" name="customer_state" placeholder="Enter state">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">ZIP Code</label>
                                    <input type="text" class="form-control" id="customerZip" name="customer_zip" placeholder="Enter ZIP code">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Special Requests</label>
                                    <textarea class="form-control" id="customerSpecialRequests" name="customer_special_requests" rows="3" placeholder="Enter any special requests or notes"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Final Submit Section -->
            <div class="row mb-5" id="submitSection">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h6 class="mb-1">Current Bookings</h6>
                                    <p class="text-muted mb-0" id="bookingsSummary">No bookings added yet</p>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-success btn-lg px-5 me-3" onclick="saveAllBookings()">
                                        <i class="ri-save-line me-2"></i>Save Tour Package
                                    </button>
                                    <a href="{{ route('single-tour-package.index') }}" class="btn btn-outline-secondary btn-lg px-5">
                                        <i class="ri-close-line me-2"></i>Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // Service Management Functions
                function addPortService(day, portType) {
                    const data = {
                        type: portType,
                        date: document.querySelector(`[name="day${day}_${portType}_port_date"]`).value,
                        // Add other port service specific data
                    };
                    addServiceToPackage('port', data);
                    updateBookingsSummary();
                }

                function addAttractionService(day) {
                    const data = {
                        date: document.querySelector(`[name="day${day}_attraction_date"]`).value,
                        // Add attraction specific data
                    };
                    addServiceToPackage('attraction', data);
                    updateBookingsSummary();
                }

                function addGuideService(day) {
                    const data = {
                        date: document.querySelector(`[name="day${day}_guide_date"]`).value,
                        // Add guide specific data
                    };
                    addServiceToPackage('guide', data);
                    updateBookingsSummary();
                }

                function addRestaurantService(day) {
                    const data = {
                        date: document.querySelector(`[name="day${day}_restaurant_date"]`).value,
                        // Add restaurant specific data
                    };
                    addServiceToPackage('restaurant', data);
                    updateBookingsSummary();
                }

                function addTransportService(day) {
                    const data = {
                        date: document.querySelector(`[name="day${day}_transport_date"]`).value,
                        // Add transport specific data
                    };
                    addServiceToPackage('transport', data);
                    updateBookingsSummary();
                }

                function addServiceToPackage(type, data) {
                    // Add customer information to the data
                    const customerData = getCustomerData();
                    const serviceData = {
                        ...data,
                        ...customerData,
                        bookingType: 'booking'
                    };

                    // Store in the appropriate hidden field
                    const fieldId = `${type}Bookings`;
                    const bookings = JSON.parse(document.getElementById(fieldId).value || '[]');
                    bookings.push(serviceData);
                    document.getElementById(fieldId).value = JSON.stringify(bookings);

                    // Update the summary display
                    updateBookingsSummary();

                    // Show success message
                    showToast(`${type.charAt(0).toUpperCase() + type.slice(1)} added to package successfully!`);
                }

                function showToast(message) {
                    // You can implement a nice toast notification here
                    alert(message);
                }

                // Function to show notifications
                function showNotification(message, type = 'info') {
                    // Create notification element
                    const notification = document.createElement('div');
                    notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show position-fixed`;
                    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                    notification.innerHTML = `
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    
                    // Add to body
                    document.body.appendChild(notification);
                    
                    // Auto remove after 5 seconds
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.remove();
                        }
                    }, 5000);
                }























                // Function to calculate meal costs based on meal plan, guest count, and number of rooms
                // IMPORTANT: This function needs to be updated to multiply by numRooms for all meal calculations
                // Current issue: meal costs are not being multiplied by number of rooms
                function calculateMealCosts(mealPlan, numNights, adults, children, mealPrices = null, numRooms = 1) {
                    if (!mealPlan || mealPlan === 'Not specified' || mealPlan.includes('only')) {
                        return 0; // No meals included
                    }
                    
                    let totalMealCost = 0;
                    const totalGuests = adults + children;
                    
                    // If we have meal prices, use the actual prices from the database
                    if (mealPrices && typeof mealPrices === 'object') {
                        // Check if meals are available and calculate costs
                        if (mealPlan.includes('breakfast') || mealPlan.includes('bf')) {
                            const breakfastPrice = parseFloat(mealPrices.breakfast_price) || 0;
                            if (breakfastPrice > 0) {
                                totalMealCost += breakfastPrice * totalGuests * numNights;
                                console.log(`Breakfast: $${breakfastPrice} × ${totalGuests} guests × ${numNights} nights = $${breakfastPrice * totalGuests * numNights}`);
                            }
                        }
                        
                        if (mealPlan.includes('lunch')) {
                            const lunchPrice = parseFloat(mealPrices.lunch_price) || 0;
                            if (lunchPrice > 0) {
                                totalMealCost += lunchPrice * totalGuests * numNights;
                                console.log(`Lunch: $${lunchPrice} × ${totalGuests} guests × ${numNights} nights = $${lunchPrice * totalGuests * numNights}`);
                            }
                        }
                        
                        if (mealPlan.includes('dinner')) {
                            const dinnerPrice = parseFloat(mealPrices.dinner_price) || 0;
                            if (dinnerPrice > 0) {
                                totalMealCost += dinnerPrice * totalGuests * numNights;
                                console.log(`Dinner: $${dinnerPrice} × ${totalGuests} guests × ${numNights} nights = $${dinnerPrice * totalGuests * numNights}`);
                            }
                        }
                    } else {
                        // Fallback to default prices if no meal prices available
                        const defaultMealPrices = {
                            breakfast: 30.00,  // Default breakfast price
                            lunch: 60.00,      // Default lunch price
                            dinner: 80.00      // Default dinner price
                        };
                        
                        if (mealPlan.includes('breakfast') || mealPlan.includes('bf')) {
                            totalMealCost += defaultMealPrices.breakfast * totalGuests * numNights;
                        }
                        
                        if (mealPlan.includes('lunch')) {
                            totalMealCost += defaultMealPrices.lunch * totalGuests * numNights;
                        }
                        
                        if (mealPlan.includes('dinner')) {
                            totalMealCost += defaultMealPrices.dinner * totalGuests * numNights;
                        }
                    }
                    
                    console.log(`Meal cost calculation: Plan: ${mealPlan}, Guests: ${totalGuests}, Nights: ${numNights}, Rooms: ${numRooms}, Total Cost: $${totalMealCost}`);
                    return totalMealCost;
                }

                // Test function to verify meal pricing calculation
                function testMealPricing() {
                    console.log('=== TESTING MEAL PRICING ===');
                    
                    // Test with sample data
                    const testMealPrices = {
                        breakfast_price: 30.00,
                        lunch_price: 60.00,
                        dinner_price: 80.00
                    };
                    
                    const testCases = [
                        { mealPlan: '1 room with breakfast', guests: 2, nights: 3, rooms: 1, expected: 30 * 2 * 3 * 1 },
                        { mealPlan: '2 rooms with breakfast + lunch', guests: 2, nights: 3, rooms: 2, expected: (30 + 60) * 2 * 3 * 2 },
                        { mealPlan: '1 room with all meals (breakfast + lunch + dinner)', guests: 2, nights: 3, rooms: 1, expected: (30 + 60 + 80) * 2 * 3 * 1 },
                        { mealPlan: '3 rooms with breakfast', guests: 2, nights: 3, rooms: 3, expected: 30 * 2 * 3 * 3 },
                        { mealPlan: '1 room only', guests: 2, nights: 3, rooms: 1, expected: 0 }
                    ];
                    
                    testCases.forEach((testCase, index) => {
                        const result = calculateMealCosts(testCase.mealPlan, testCase.nights, testCase.guests, 0, testMealPrices, testCase.rooms);
                        const passed = Math.abs(result - testCase.expected) < 0.01;
                        console.log(`Test ${index + 1}: ${passed ? 'PASS' : 'FAIL'}`);
                        console.log(`  Plan: ${testCase.mealPlan}, Guests: ${testCase.guests}, Nights: ${testCase.nights}, Rooms: ${testCase.rooms}`);
                        console.log(`  Expected: $${testCase.expected}, Got: $${result}`);
                    });
                    
                    showNotification('Meal pricing test completed. Check console for results.', 'info');
                }

                // Function to debug room data and meal prices
                function debugRoomData() {
                    console.log('=== DEBUGGING ROOM DATA ===');
                    
                    if (window.roomData) {
                        console.log('Available room data:', window.roomData);
                        
                        // Show structure of first room
                        if (window.roomData.length > 0) {
                            const firstRoom = window.roomData[0];
                            console.log('First room structure:', firstRoom);
                            console.log('Available fields:', Object.keys(firstRoom));
                            
                            // Check meal price fields
                            console.log('Meal price fields:');
                            console.log('  breakfast_price:', firstRoom.breakfast_price);
                            console.log('  lunch_price:', firstRoom.lunch_price);
                            console.log('  dinner_price:', firstRoom.dinner_price);
                            console.log('  breakfast:', firstRoom.breakfast);
                            console.log('  lunch:', firstRoom.lunch);
                            console.log('  dinner:', firstRoom.dinner);
                        }
                        
                        // Show all room types
                        const roomTypes = [...new Set(window.roomData.map(room => room.room_type))];
                        console.log('Available room types:', roomTypes);
                        
                        // Show all hotel IDs
                        const hotelIds = [...new Set(window.roomData.map(room => room.hotel_unique_id))];
                        console.log('Available hotel IDs:', hotelIds);
                        
                    } else {
                        console.log('No room data available (window.roomData is null/undefined)');
                    }
                    
                    // Also check current form values
                    const hotelSelect = document.getElementById('hotelSelect');
                    const roomTypeSelect = document.getElementById('roomTypeSelect');
                    const mealPlanSelect = document.getElementById('mealPlanSelect');
                    
                    console.log('Current form values:');
                    console.log('  Selected hotel:', hotelSelect ? hotelSelect.value : 'N/A');
                    console.log('  Selected room type:', roomTypeSelect ? roomTypeSelect.value : 'N/A');
                    console.log('  Selected meal plan:', mealPlanSelect ? mealPlanSelect.value : 'N/A');
                    
                    showNotification('Room data debug completed. Check console for details.', 'info');
                }

                // Function to test meal price fetching from room type options
                function testMealPriceFetching() {
                    console.log('=== TESTING MEAL PRICE FETCHING ===');
                    
                    const roomTypeSelect = document.getElementById('roomTypeSelect');
                    if (!roomTypeSelect) {
                        console.log('Room type select not found');
                        return;
                    }
                    
                    console.log('Room type select options:', roomTypeSelect.options.length);
                    
                    Array.from(roomTypeSelect.options).forEach((option, index) => {
                        if (option.value) {
                            console.log(`Option ${index + 1}: ${option.value}`);
                            console.log('  Dataset:', {
                                breakfastPrice: option.dataset.breakfastPrice,
                                lunchPrice: option.dataset.lunchPrice,
                                dinnerPrice: option.dataset.dinnerPrice,
                                breakfast: option.dataset.breakfast,
                                lunch: option.dataset.lunch,
                                dinner: option.dataset.dinner
                            });
                        }
                    });
                    
                    showNotification('Meal price fetching test completed. Check console for details.', 'info');
                }

                // Function to manually calculate correct meal costs (override current logic)
                function calculateCorrectMealCosts(mealPlan, numNights, adults, children, mealPrices, numRooms) {
                    if (!mealPlan || mealPlan === 'Not specified' || mealPlan.includes('only')) {
                        return 0;
                    }
                    
                    let totalMealCost = 0;
                    const totalGuests = adults + children;
                    
                    if (mealPrices && typeof mealPrices === 'object') {
                        if (mealPlan.includes('breakfast') || mealPlan.includes('bf')) {
                            const breakfastPrice = parseFloat(mealPrices.breakfast_price) || 0;
                            if (breakfastPrice > 0) {
                                const breakfastCost = breakfastPrice * totalGuests * numNights * numRooms;
                                totalMealCost += breakfastCost;
                                console.log(`CORRECTED Breakfast: $${breakfastPrice} × ${totalGuests} guests × ${numNights} nights × ${numRooms} rooms = $${breakfastCost}`);
                            }
                        }
                        
                        if (mealPlan.includes('lunch')) {
                            const lunchPrice = parseFloat(mealPrices.lunch_price) || 0;
                            if (lunchPrice > 0) {
                                const lunchCost = lunchPrice * totalGuests * numNights * numRooms;
                                totalMealCost += lunchCost;
                                console.log(`CORRECTED Lunch: $${lunchPrice} × ${totalGuests} guests × ${numNights} nights × ${numRooms} rooms = $${lunchCost}`);
                            }
                        }
                        
                        if (mealPlan.includes('dinner')) {
                            const dinnerPrice = parseFloat(mealPrices.dinner_price) || 0;
                            if (dinnerPrice > 0) {
                                const dinnerCost = dinnerPrice * totalGuests * numNights * numRooms;
                                totalMealCost += dinnerCost;
                                console.log(`CORRECTED Dinner: $${dinnerPrice} × ${totalGuests} guests × ${numNights} nights × ${numRooms} rooms = $${dinnerCost}`);
                            }
                        }
                    }
                    
                    console.log(`CORRECTED Meal cost: Plan: ${mealPlan}, Guests: ${totalGuests}, Nights: ${numNights}, Rooms: ${numRooms}, Total: $${totalMealCost}`);
                    return totalMealCost;
                }

                // Function to ensure all guide forms have hidden fields
                function ensureGuideHiddenFields() {
                    console.log('=== ENSURING GUIDE HIDDEN FIELDS ===');
                    
                    document.querySelectorAll('.guide-select').forEach((select, index) => {
                        const dayMatch = select.name.match(/day(\d+)_guide_(\d+)/);
                        if (dayMatch) {
                            const day = dayMatch[1];
                            const guideIndex = dayMatch[2];
                            
                            // Check if hidden fields exist
                            const basePriceField = document.getElementById(`day${day}_guide_${guideIndex}_base_price`);
                            const hoursField = document.getElementById(`day${day}_guide_${guideIndex}_hours`);
                            const surchargeField = document.getElementById(`day${day}_guide_${guideIndex}_surcharge`);
                            const totalPriceField = document.getElementById(`day${day}_guide_${guideIndex}_total_price`);
                            
                            console.log(`Guide ${index + 1} (Day ${day}, Index ${guideIndex}):`);
                            console.log('  Base Price Field:', basePriceField ? 'EXISTS' : 'MISSING');
                            console.log('  Hours Field:', hoursField ? 'EXISTS' : 'MISSING');
                            console.log('  Surcharge Field:', surchargeField ? 'EXISTS' : 'MISSING');
                            console.log('  Total Price Field:', totalPriceField ? 'EXISTS' : 'MISSING');
                            
                            // If any field is missing, create it
                            if (!basePriceField || !hoursField || !surchargeField || !totalPriceField) {
                                console.log('  Creating missing hidden fields...');
                                
                                const packageSelect = document.getElementById(`day${day}_guide_${guideIndex}_package`);
                                if (packageSelect) {
                                    // Insert hidden fields after the package select
                                    const hiddenFieldsHTML = `
                                        <input type="hidden" id="day${day}_guide_${guideIndex}_base_price" name="day${day}_guide_${guideIndex}_base_price" value="0">
                                        <input type="hidden" id="day${day}_guide_${guideIndex}_hours" name="day${day}_guide_${guideIndex}_hours" value="0">
                                        <input type="hidden" id="day${day}_guide_${guideIndex}_surcharge" name="day${day}_guide_${guideIndex}_surcharge" value="0">
                                        <input type="hidden" id="day${day}_guide_${guideIndex}_total_price" name="day${day}_guide_${guideIndex}_total_price" value="0">
                                    `;
                                    packageSelect.insertAdjacentHTML('afterend', hiddenFieldsHTML);
                                    console.log('  Hidden fields created successfully');
                                }
                            }
                        }
                    });
                    
                    showNotification('Guide hidden fields check completed. Check console for details.', 'info');
                }

                // Function to test guide pricing calculation
                function testGuidePricing() {
                    console.log('=== TESTING GUIDE PRICING ===');
                    
                    // Test with the current guide selection
                    const guideSelects = document.querySelectorAll('.guide-select');
                    guideSelects.forEach((select, index) => {
                        if (select.value) {
                            const dayMatch = select.name.match(/day(\d+)_guide_(\d+)/);
                            if (dayMatch) {
                                const day = dayMatch[1];
                                const guideIndex = dayMatch[2];
                                
                                const selectedOption = select.options[select.selectedIndex];
                                const packageSelect = document.getElementById(`day${day}_guide_${guideIndex}_package`);
                                
                                console.log(`\n--- Guide ${index + 1} (Day ${day}, Index ${guideIndex}) ---`);
                                console.log('Guide:', selectedOption.text);
                                console.log('Guide dataset:', selectedOption.dataset);
                                
                                if (packageSelect && packageSelect.value) {
                                    const selectedPackage = packageSelect.options[packageSelect.selectedIndex];
                                    console.log('Selected package:', selectedPackage.text);
                                    console.log('Package dataset:', selectedPackage.dataset);
                                    
                                    // Get current pricing values
                                    const basePrice = parseFloat(document.getElementById(`day${day}_guide_${guideIndex}_base_price`)?.value || 0);
                                    const hours = parseFloat(document.getElementById(`day${day}_guide_${guideIndex}_hours`)?.value || 0);
                                    const surcharge = parseFloat(document.getElementById(`day${day}_guide_${guideIndex}_surcharge`)?.value || 0);
                                    const totalPrice = parseFloat(document.getElementById(`day${day}_guide_${guideIndex}_total_price`)?.value || 0);
                                    
                                    console.log('Current pricing values:');
                                    console.log('  Base Price:', basePrice);
                                    console.log('  Hours:', hours);
                                    console.log('  Surcharge:', surcharge);
                                    console.log('  Total Price:', totalPrice);
                                    
                                    // Calculate expected total
                                    const expectedTotal = basePrice + surcharge;
                                    console.log('Expected total (basePrice + surcharge):', expectedTotal);
                                    console.log('Price calculation correct:', Math.abs(expectedTotal - totalPrice) < 0.01);
                                } else {
                                    console.log('No package selected');
                                }
                            }
                        }
                    });
                    
                    showNotification('Guide pricing test completed. Check console for details.', 'info');
                }

                // Function to test current meal prices and calculation
                function testCurrentMealPricing() {
                    console.log('=== TESTING CURRENT MEAL PRICING ===');
                    
                    // Test with the current room type selection
                    const roomTypeSelect = document.getElementById('roomTypeSelect');
                    if (!roomTypeSelect || !roomTypeSelect.value) {
                        console.log('No room type selected');
                        return;
                    }
                    
                    const selectedOption = roomTypeSelect.options[roomTypeSelect.selectedIndex];
                    console.log('Selected room option:', selectedOption);
                    console.log('Selected room dataset:', selectedOption.dataset);
                    
                    // Get meal prices from dataset
                    const mealPrices = {
                        breakfast_price: parseFloat(selectedOption.dataset.breakfastPrice) || 0,
                        lunch_price: parseFloat(selectedOption.dataset.lunchPrice) || 0,
                        dinner_price: parseFloat(selectedOption.dataset.dinnerPrice) || 0
                    };
                    
                    console.log('Meal prices from dataset:', mealPrices);
                    
                    // Test calculation with sample data
                    const testCases = [
                        { mealPlan: '2 rooms with breakfast + lunch', guests: 2, nights: 1, rooms: 2 },
                        { mealPlan: '1 room with breakfast', guests: 2, nights: 1, rooms: 1 },
                        { mealPlan: '3 rooms with all meals (breakfast + lunch + dinner)', guests: 2, nights: 1, rooms: 3 }
                    ];
                    
                    testCases.forEach((testCase, index) => {
                        console.log(`\n--- Test Case ${index + 1} ---`);
                        console.log(`Meal Plan: ${testCase.mealPlan}`);
                        console.log(`Guests: ${testCase.guests}, Nights: ${testCase.nights}, Rooms: ${testCase.rooms}`);
                        
                        const result = calculateCorrectMealCosts(
                            testCase.mealPlan, 
                            testCase.nights, 
                            testCase.guests, 
                            0, 
                            mealPrices, 
                            testCase.rooms
                        );
                        
                        console.log(`Result: $${result}`);
                    });
                    
                    showNotification('Current meal pricing test completed. Check console for details.', 'info');
                }

                // Function to collect hotel data when hotels are selected
                function updateHotelDataField() {
                    // Safety check: ensure selectedHotels is an array
                    if (!Array.isArray(selectedHotels)) {
                        console.warn('selectedHotels is not an array, initializing as empty array');
                        selectedHotels = [];
                    }
                    
                    // Get current DMC information
                    const dmcInfo = {
                        dmc_id: '{{ auth()->user()->created_by }}',
                        user_id: '{{ auth()->user()->userId }}',
                        role_id: '{{ auth()->user()->role_id }}'
                    };
                    
                    console.log('=== UPDATING HOTEL DATA FIELD ===');
                    console.log('Total hotels to process:', selectedHotels.length);
                    console.log('Selected hotels array:', selectedHotels);
                    
                    const hotelDataArray = selectedHotels.map((hotel, index) => {
                        // Debug: Log the hotel object to see its structure
                        console.log(`Processing hotel ${index + 1}:`, hotel);
                        console.log(`Hotel ${index + 1} stored price:`, hotel.price);
                        console.log(`Hotel ${index + 1} room type:`, hotel.roomType);
                        console.log(`Hotel ${index + 1} nights:`, hotel.totalNights);
                        console.log(`Hotel ${index + 1} rooms:`, hotel.numberOfRooms);
                        
                        // Get current guest information from main form
                        const adults = parseInt(document.getElementById('adults').value) || 0;
                        const male = parseInt(document.getElementById('male').value) || 0;
                        const female = parseInt(document.getElementById('female').value) || 0;
                        const children = parseInt(document.getElementById('children').value) || 0;
                        const infants = parseInt(document.getElementById('infants').value) || 0;
                        
                        // Get customer information from the Customer Information form
                        const customerData = getCustomerData();
                        
                        // Find the selected hotel data from hotelData array
                        const selectedHotelInfo = hotelData.find(h => h.hotel_unique_id == hotel.id);
                        
                        return {
                            // Customer Information (from Customer Information form)
                            fullName: customerData.fullName,
                            email: customerData.email,
                            phone: customerData.phone,
                            countryCode: customerData.countryCode,
                            address1: customerData.address1,
                            address2: customerData.address2,
                            state: customerData.state,
                            zip: customerData.zip,
                            specialRequests: customerData.specialRequests,
                            
                            // Hotel Information
                            hotel_id: hotel.id,
                            hotel_name: hotel.name,
                            hotel_unique_id: hotel.id,
                            room_type: hotel.roomType,
                            bed_type: hotel.bedType,
                            meal_plan: hotel.mealPlan,
                            number_of_rooms: parseInt(hotel.numberOfRooms) || 1,
                            nights: hotel.nights,
                            check_in_date: hotel.checkInDate,
                            check_out_date: hotel.checkOutDate,
                            total_nights: hotel.totalNights,
                            
                            // Booking Dates (required for the backend) - Format as YYYY-MM-DD
                            bookingDate: [
                                hotel.checkInDate ? new Date(hotel.checkInDate).toISOString().split('T')[0] : new Date().toISOString().split('T')[0],
                                hotel.checkOutDate ? new Date(hotel.checkOutDate).toISOString().split('T')[0] : new Date().toISOString().split('T')[0]
                            ],
                            
                            // Hotel Details
                            hotelDetails: selectedHotelInfo ? {
                                hotel_id: selectedHotelInfo.hotel_unique_id,
                                hotel_name: selectedHotelInfo.name,
                                image: selectedHotelInfo.main_image,
                                location: selectedHotelInfo.city,
                                checkInTime: selectedHotelInfo.check_in_time || "",
                                checkOutTime: selectedHotelInfo.check_out_time || "",
                                cancellation_charge: null
                            } : {
                                hotel_id: hotel.id,
                                hotel_name: hotel.name,
                                image: "",
                                location: "Location not specified",
                                checkInTime: hotel.check_in_time || "",
                                checkOutTime: hotel.check_out_time || "",
                                cancellation_charge: null
                            },
                            
                            // Rooms Array - Include the selected room data
                            rooms: [{
                                room_id: hotel.roomId || hotel.id || hotel.room_id || "room_" + Date.now(),
                                room_type: hotel.roomType || hotel.room_type || "",
                                beds: [{
                                    bed_id: hotel.bedId || hotel.id || hotel.bed_id || "bed_" + Date.now(),
                                    bed_type: hotel.bedType || hotel.bed_type || "",
                                    baby_cot: parseInt(hotel.baby_cot) || 0,
                                    head_count: adults + children,
                                    max_occupancy: parseInt(hotel.max_occupancy) || (adults + children),
                                    available_rooms: parseInt(hotel.availableRooms) || 0,
                                    extra_bed_price: parseFloat(hotel.extraBedPrice) || 0,
                                    baby_cot_price: parseFloat(hotel.babyCotPrice) || 0,
                                    price: parseFloat(hotel.price) || 0,
                                    mealTypes: [hotel.mealPlan || hotel.meal_plan || ""],
                                    selectedMeals: {
                                        meal_1: {
                                            type: hotel.mealPlan || hotel.meal_plan || "",
                                            price: parseFloat(hotel.meal_price) || 0
                                        }
                                    }
                                }]
                            }],
                            
                            // Guest Information
                            guest_info: {
                                adults: adults,
                                male: male,
                                female: female,
                                children: children,
                                infants: infants,
                                total_guests: adults + children + infants
                            },
                            
                            // Booking Information
                            booking_type: 'hotel',
                            created_at: new Date().toISOString(),
                            
                            // Additional fields for compatibility
                            priceMode: hotel.priceMode || 'dmc',
                            priceModeId: parseInt(hotel.priceModeId) || 0,
                            // Calculate total price: room price × number of rooms × nights + meal costs
                            totalPrice: (() => {
                                // Use the stored price from when the hotel was added
                                let roomPrice = parseFloat(hotel.price) || 0;
                                
                                // If no stored price, try to get from room type selection as fallback
                                if (roomPrice === 0) {
                                    const roomTypeSelect = document.getElementById('roomTypeSelect');
                                    if (roomTypeSelect && roomTypeSelect.value === hotel.roomType) {
                                        const selectedOption = roomTypeSelect.options[roomTypeSelect.selectedIndex];
                                        if (selectedOption && selectedOption.dataset) {
                                            // Get guest count to determine single vs double occupancy
                                            const adults = parseInt(document.getElementById('adults').value) || 0;
                                            const children = parseInt(document.getElementById('children').value) || 0;
                                            const totalGuests = adults + children;
                                            
                                            // Determine if single or double occupancy
                                            const isSingleOccupancy = totalGuests <= 1;
                                            
                                            // Determine if it's weekend based on the hotel's check-in date
                                            let isWeekend = false;
                                            if (hotel.checkInDate) {
                                                const checkInDate = moment(hotel.checkInDate, 'MMM DD');
                                                const dayOfWeek = checkInDate.day(); // 0 = Sunday, 6 = Saturday
                                                isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
                                            }
                                            
                                            if (isSingleOccupancy) {
                                                if (isWeekend) {
                                                    roomPrice = parseFloat(selectedOption.dataset.weekendPrice) || 0;
                                                } else {
                                                    roomPrice = parseFloat(selectedOption.dataset.weekdayPrice) || 0;
                                                }
                                            } else {
                                                if (isWeekend) {
                                                    roomPrice = parseFloat(selectedOption.dataset.doubleWeekendPrice) || 0;
                                                } else {
                                                    roomPrice = parseFloat(selectedOption.dataset.doubleWeekdayPrice) || 0;
                                                }
                                            }
                                            
                                            console.log(`Using fallback price from room type selection: $${roomPrice} (${isSingleOccupancy ? 'Single' : 'Double'} ${isWeekend ? 'Weekend' : 'Weekday'})`);
                                        }
                                    }
                                }
                                
                                const numRooms = parseInt(hotel.numberOfRooms) || 1;
                                const numNights = parseInt(hotel.totalNights) || 1;
                                
                                // Calculate room cost
                                const roomCost = roomPrice * numRooms * numNights;
                                
                                // Calculate meal costs based on meal plan, guest count, and number of rooms
                                // Use stored meal prices from when hotel was added
                                // Use the corrected function that properly multiplies by number of rooms
                                const mealCost = calculateCorrectMealCosts(hotel.mealPlan, numNights, adults, children, hotel.mealPrices, numRooms);
                                
                                const total = roomCost + mealCost;
                                console.log(`Hotel pricing for ${hotel.name}: Room price: $${roomPrice}, Rooms: ${numRooms}, Nights: ${numNights}, Room cost: $${roomCost}, Meal cost: $${mealCost}, Total: $${total}`);
                                return total;
                            })(),
                            tour_id: parseInt(hotel.tour_id) || 0,
                            
                            // DMC Information
                            dmc_id: dmcInfo.dmc_id,
                            created_by_dmc: dmcInfo.dmc_id,
                            user_id: dmcInfo.user_id,
                            user_role: dmcInfo.role_id
                        };
                    });
                    
                    // Update the hidden field with JSON string
                    const hotelDataField = document.getElementById('hotel_data');
                    if (hotelDataField) {
                        hotelDataField.value = JSON.stringify(hotelDataArray);
                        console.log('Hotel data updated:', hotelDataArray);
                        
                        // Log the final calculated prices for each hotel
                        console.log('=== FINAL HOTEL PRICING SUMMARY ===');
                        hotelDataArray.forEach((hotel, index) => {
                            console.log(`Hotel ${index + 1} (${hotel.hotel_name}):`);
                            console.log(`  - Stored price: $${hotel.price}`);
                            console.log(`  - Calculated totalPrice: $${hotel.totalPrice}`);
                            console.log(`  - Room type: ${hotel.room_type}`);
                            console.log(`  - Nights: ${hotel.total_nights}`);
                            console.log(`  - Rooms: ${hotel.number_of_rooms}`);
                        });
                    }
                    
                    // Update package total price display
                    updatePackageTotalPriceDisplay();
                }

                // Function to fetch attraction details dynamically
                async function fetchAttractionDetails(attractionId, ticketId) {
                    try {
                        // Fetch attraction details
                        const attractionResponse = await fetch(`/api/attractions/${attractionId}`);
                        const attractionData = await attractionResponse.json();
                        
                        // Fetch ticket details
                        const ticketResponse = await fetch(`/api/tickets/${ticketId}`);
                        const ticketData = await ticketResponse.json();
                        
                        // Combine the data
                        return {
                            adult_price: ticketData.adult_price || 0,
                            child_price: ticketData.child_price || 0,
                            senior_price: ticketData.senior_price || 0,
                            description: ticketData.description || "",
                            nri: ticketData.nri || "",
                            package_attraction_id: attractionData.package_attraction_id || null
                        };
                    } catch (error) {
                        console.error('Error fetching attraction details:', error);
                        // Return default values if API fails
                        return {
                            adult_price: 0,
                            child_price: 0,
                            senior_price: 0,
                            description: "",
                            nri: "",
                            package_attraction_id: null
                        };
                    }
                }

                // Function to collect attraction data
                function updateAttractionDataField() {
                    const attractionDataArray = [];
                    
                    // Get customer information from the Customer Information form
                    const customerData = getCustomerData();
                    
                    // Get current DMC information
                    const dmcInfo = {
                        dmc_id: '{{ auth()->user()->created_by }}',
                        user_id: '{{ auth()->user()->userId }}',
                        role_id: '{{ auth()->user()->role_id }}'
                    };
                    
                    // Get all attraction selections from all days
                    document.querySelectorAll('.attraction-select').forEach(select => {
                        if (select.value) {
                            const dayMatch = select.name.match(/day(\d+)_attraction_(\d+)/);
                            if (dayMatch) {
                                const day = dayMatch[1];
                                const index = dayMatch[2];
                                
                                const timeSlot = document.getElementById(`day${day}_attraction_${index}_time`)?.value || '';
                                const ticket = document.getElementById(`day${day}_attraction_${index}_ticket`)?.value || '';
                                const guestSummary = document.getElementById(`day${day}_attraction_${index}_guest_summary`)?.textContent || '';
                                
                                // Parse guest info from summary text
                                const guestInfo = parseGuestSummary(guestSummary);
                                
                                // Get attraction details from the selected option and fetch dynamic data
                                const selectedOption = select.options[select.selectedIndex];
                                const attractionId = select.value;
                                const ticketId = ticket;
                                
                                // Get attraction details and calculate prices
                                // Get prices from the selected attraction option data attributes
                                const adultPrice = parseFloat(selectedOption.dataset.adultPrice || selectedOption.dataset.adult_price || 0);
                                const childPrice = parseFloat(selectedOption.dataset.childPrice || selectedOption.dataset.child_price || 0);
                                const seniorPrice = parseFloat(selectedOption.dataset.seniorPrice || selectedOption.dataset.senior_price || 0);
                                
                                // If no prices in data attributes, try to get from form fields as fallback
                                const fallbackAdultPrice = parseFloat(document.getElementById(`day${day}_attraction_${index}_adult_price`)?.value || 0);
                                const fallbackChildPrice = parseFloat(document.getElementById(`day${day}_attraction_${index}_child_price`)?.value || 0);
                                const fallbackSeniorPrice = parseFloat(document.getElementById(`day${day}_attraction_${index}_senior_price`)?.value || 0);
                                
                                // Use data attributes if available, otherwise use form fields
                                const finalAdultPrice = adultPrice || fallbackAdultPrice;
                                const finalChildPrice = childPrice || fallbackChildPrice;
                                const finalSeniorPrice = seniorPrice || fallbackSeniorPrice;
                                
                                // Calculate total price dynamically
                                const totalPrice = (guestInfo.adults * finalAdultPrice) + 
                                                  (guestInfo.children * finalChildPrice) + 
                                                  (guestInfo.seniors * finalSeniorPrice);
                                
                                console.log(`Attraction pricing: ${selectedOption.text} - Adult: $${finalAdultPrice} × ${guestInfo.adults}, Child: $${finalChildPrice} × ${guestInfo.children}, Senior: $${finalSeniorPrice} × ${guestInfo.seniors}, Total: $${totalPrice}`);
                                
                                attractionDataArray.push({
                                    // Customer Information (from Customer Information form)
                                    fullName: customerData.fullName,
                                    email: customerData.email,
                                    phone: customerData.phone,
                                    countryCode: customerData.countryCode,
                                    address1: customerData.address1,
                                    address2: customerData.address2,
                                    state: customerData.state,
                                    zip: customerData.zip,
                                    specialRequests: customerData.specialRequests,
                                    
                                    // Attraction Information
                                    bookingDate: document.getElementById(`day${day}_attraction_${index}_date`)?.value || new Date().toISOString().split('T')[0],
                                    visitTime: timeSlot || "10:00-00:00",
                                    adultCount: guestInfo.adults || 0,
                                    childCount: guestInfo.children || 0,
                                    seniorCount: guestInfo.seniors || 0,
                                    AttractionId: parseInt(attractionId),
                                    AttractionName: selectedOption.text,
                                    ticketId: parseInt(ticketId) || 10000001,
                                    ticketName: ticketId ? `Ticket ${ticketId}` : "Ticket 1",
                                    
                                    // Ticket Details (with proper pricing)
                                    ticket_details: {
                                        adult_price: adultPrice,
                                        child_price: childPrice,
                                        senior_price: seniorPrice,
                                        description: document.getElementById(`day${day}_attraction_${index}_description`)?.value || "",
                                        nri: document.getElementById(`day${day}_attraction_${index}_nri`)?.value || "residential"
                                    },
                                    
                                    // Transport and Mode
                                    transport: document.getElementById(`day${day}_attraction_${index}_transport`)?.value || null,
                                    Selection: document.getElementById(`day${day}_attraction_${index}_transport_selection`)?.value || "withoutTransport",
                                    mode: "dmc",
                                    
                                    // Pricing (calculated dynamically - NOT null)
                                    totalPrice: totalPrice,
                                    nri: document.getElementById(`day${day}_attraction_${index}_nri`)?.value || "residential",
                                    price: totalPrice,
                                    prices: {
                                        price: totalPrice
                                    },
                                    
                                    // DMC and Package Info
                                    dmc_id: dmcInfo.dmc_id,
                                    created_by_dmc: dmcInfo.dmc_id,
                                    user_id: dmcInfo.user_id,
                                    user_role: dmcInfo.role_id,
                                    bookingType: document.getElementById(`day${day}_attraction_${index}_booking_type`)?.value || "booking",
                                    package_type: parseInt(document.getElementById(`day${day}_attraction_${index}_package_type`)?.value || 0),
                                    package_attraction_id: parseInt(document.getElementById(`day${day}_attraction_${index}_package_attraction_id`)?.value || null)
                                });
                            }
                        }
                    });
                    
                    // Update the data field after processing all attractions
                    const attractionDataField = document.getElementById('attraction_data');
                    if (attractionDataField) {
                        attractionDataField.value = JSON.stringify(attractionDataArray);
                        console.log('Attraction data updated:', attractionDataArray);
                    }
                    
                    // Update package total price display
                    updatePackageTotalPriceDisplay();
                }

                // Function to collect guide data
                function updateGuideDataField() {
                    const guideDataArray = [];
                    
                    // Get customer information from the Customer Information form
                    const customerData = getCustomerData();
                    
                    // Get current DMC information
                    const dmcInfo = {
                        dmc_id: '{{ auth()->user()->created_by }}',
                        user_id: '{{ auth()->user()->userId }}',
                        role_id: '{{ auth()->user()->role_id }}'
                    };
                    
                    console.log('=== UPDATING GUIDE DATA FIELD ===');
                    
                    document.querySelectorAll('.guide-select').forEach(select => {
                        if (select.value) {
                            const dayMatch = select.name.match(/day(\d+)_guide_(\d+)/);
                            if (dayMatch) {
                                const day = dayMatch[1];
                                const index = dayMatch[2];
                                
                                const pickupTime = document.getElementById(`day${day}_guide_${index}_pickup_time`)?.value || '';
                                const packageType = document.getElementById(`day${day}_guide_${index}_package`)?.value || '';
                                const guestSummary = document.getElementById(`day${day}_guide_${index}_guest_summary`)?.textContent || '';
                                
                                // Parse guest info from summary text
                                const guestInfo = parseGuestSummary(guestSummary);
                                
                                // Get guide details from the selected option
                                const selectedOption = select.options[select.selectedIndex];
                                const guideId = select.value;
                                
                                // Get guide date from the form
                                const guideDate = document.getElementById(`day${day}_guide_${index}_date`)?.value || new Date().toISOString().split('T')[0];
                                
                                // Calculate pricing directly from package selection
                                let basePrice = 0;
                                let hours = 0;
                                let surcharge = 0;
                                let totalPrice = 0;
                                
                                if (packageType) {
                                    const packageSelect = document.getElementById(`day${day}_guide_${index}_package`);
                                    if (packageSelect && packageSelect.value) {
                                        const selectedPackage = packageSelect.options[packageSelect.selectedIndex];
                                        if (selectedPackage && selectedPackage.dataset) {
                                            basePrice = parseFloat(selectedPackage.dataset.price) || 0;
                                            hours = parseInt(selectedPackage.dataset.hours) || 0;
                                            
                                            // Calculate surcharge based on pickup time
                                            if (pickupTime) {
                                                const pickupHour = parseInt(pickupTime.split(':')[0]);
                                                const nightStartTime = selectedOption.dataset.nightStartTime;
                                                const nightEndTime = selectedOption.dataset.nightEndTime;
                                                
                                                if (nightStartTime && nightEndTime) {
                                                    const nightStart = parseInt(nightStartTime.split(':')[0]);
                                                    const nightEnd = parseInt(nightEndTime.split(':')[0]) - 1;
                                                    
                                                    // Check if pickup time is in night range
                                                    const isNightTime = (pickupHour >= nightStart && pickupHour <= nightEnd) || 
                                                                       (nightStart > nightEnd && (pickupHour >= nightStart || pickupHour <= nightEnd));
                                                    
                                                    if (isNightTime) {
                                                        surcharge = parseFloat(selectedOption.dataset.nightSurcharge) || 0;
                                                    }
                                                }
                                            }
                                            
                                            totalPrice = basePrice + surcharge;
                                            
                                            console.log(`Calculated pricing for Day ${day}, Index ${index}:`, {
                                                packageType: packageType,
                                                basePrice: basePrice,
                                                hours: hours,
                                                surcharge: surcharge,
                                                totalPrice: totalPrice,
                                                pickupTime: pickupTime,
                                                isNightTime: surcharge > 0
                                            });
                                        }
                                    }
                                }
                                
                                // Debug: Log the final pricing values
                                console.log(`Final guide pricing for Day ${day}, Index ${index}:`, {
                                    basePrice: basePrice,
                                    hours: hours,
                                    surcharge: surcharge,
                                    totalPrice: totalPrice,
                                    packageType: packageType,
                                    guideId: guideId,
                                    guideName: selectedOption.text
                                });
                                
                                guideDataArray.push({
                                    // Guide Information
                                    bookingDate: guideDate,
                                    guide_id: guideId,
                                    guide_name: selectedOption.text,
                                    image: selectedOption.dataset.image || "",
                                    dmc_Id: dmcInfo.dmc_id,
                                    created_by_dmc: dmcInfo.dmc_id,
                                    user_id: dmcInfo.user_id,
                                    user_role: dmcInfo.role_id,
                                    Mode: "dmc",
                                    
                                    // Pickup Information
                                    entrypickup: document.getElementById(`day${day}_guide_${index}_pickup_location`)?.value || "",
                                    PickupPlaceid: null,
                                    DropoffPlaceid: null,
                                    pickupdate: guideDate,
                                    entrytime: pickupTime || "",
                                    
                                    // Guest Information
                                    adults: (guestInfo.adults || 0).toString(),
                                    children: (guestInfo.children || 0).toString(),
                                    
                                    // Service Details
                                    hours: hours.toString(),
                                    basePrice: basePrice.toFixed(2),
                                    surcharge: surcharge.toFixed(2),
                                    totalPrice: totalPrice.toFixed(2),
                                    Tax: document.getElementById(`day${day}_guide_${index}_tax`)?.value || "0.00",
                                    Night_Start_Time: document.getElementById(`day${day}_guide_${index}_night_start_time`)?.value || "",
                                    Night_End_Time: document.getElementById(`day${day}_guide_${index}_night_end_time`)?.value || "",
                                    
                                    // Customer Information (from Customer Information form)
                                    fullName: customerData.fullName,
                                    email: customerData.email,
                                    phone: customerData.phone,
                                    countryCode: customerData.countryCode,
                                    address1: customerData.address1,
                                    address2: customerData.address2,
                                    state: customerData.state,
                                    zip: customerData.zip,
                                    specialRequests: customerData.specialRequests,
                                    
                                    // User Info (duplicate for compatibility)
                                    userInfo: {
                                        fullName: customerData.fullName,
                                        email: customerData.email,
                                        phone: customerData.phone,
                                        countryCode: customerData.countryCode,
                                        address1: customerData.address1,
                                        address2: customerData.address2,
                                        state: customerData.state,
                                        zip: customerData.zip,
                                        specialRequests: customerData.specialRequests
                                    },
                                    
                                    bookingType: document.getElementById(`day${day}_guide_${index}_booking_type`)?.value || "booking"
                                });
                            }
                        }
                    });
                    
                    const guideDataField = document.getElementById('guide_data');
                    if (guideDataField) {
                        guideDataField.value = JSON.stringify(guideDataArray);
                        console.log('Guide data updated:', guideDataArray);
                    }
                    
                    // Update package total price display
                    updatePackageTotalPriceDisplay();
                }

                // Function to collect restaurant data
                function updateRestaurantDataField() {
                    const restaurantDataArray = [];
                    
                    // Get customer information from the Customer Information form
                    const customerData = getCustomerData();
                    
                    // Get current DMC information
                    const dmcInfo = {
                        dmc_id: '{{ auth()->user()->created_by }}',
                        user_id: '{{ auth()->user()->userId }}',
                        role_id: '{{ auth()->user()->role_id }}'
                    };
                    
                    document.querySelectorAll('.restaurant-select').forEach(select => {
                        if (select.value) {
                            const dayMatch = select.name.match(/day(\d+)_restaurant_(\d+)/);
                            if (dayMatch) {
                                const day = dayMatch[1];
                                const index = dayMatch[2];
                                
                                const mealType = document.getElementById(`day${day}_meal_type_${index}`)?.value || '';
                                const timeSlot = document.getElementById(`day${day}_time_slot_${index}`)?.value || '';
                                const guestSummary = document.getElementById(`day${day}_restaurant_${index}_guest_summary`)?.textContent || '';
                                
                                // Parse guest info from summary text
                                const guestInfo = parseGuestSummary(guestSummary);
                                
                                // Get restaurant details from the selected option
                                const selectedOption = select.options[select.selectedIndex];
                                const restaurantId = select.value;
                                
                                // Get pricing data from hidden fields (this is the correct way)
                                const totalPrice = parseFloat(document.getElementById(`day${day}_restaurant_${index}_total_price`)?.value || 0);
                                const mealId = document.getElementById(`day${day}_restaurant_${index}_meal_id`)?.value || '';
                                const dishName = document.getElementById(`day${day}_restaurant_${index}_dish_name`)?.value || '';
                                
                                console.log(`Restaurant pricing for day ${day}, index ${index}:`);
                                console.log(`- Total Price: $${totalPrice}`);
                                console.log(`- Meal ID: ${mealId}`);
                                console.log(`- Dish Name: ${dishName}`);
                                console.log(`- Guest Info: ${guestInfo.adults} adults, ${guestInfo.children} children`);
                                
                                restaurantDataArray.push({
                                    // Customer Information (from Customer Information form)
                                    fullName: customerData.fullName,
                                    email: customerData.email,
                                    phone: customerData.phone,
                                    countryCode: customerData.countryCode,
                                    address1: customerData.address1,
                                    address2: customerData.address2,
                                    state: customerData.state,
                                    zip: customerData.zip,
                                    specialRequests: customerData.specialRequests,
                                    
                                    // Restaurant Information
                                    bookingDate: document.getElementById(`day${day}_restaurant_${index}_date`)?.value || new Date().toISOString().split('T')[0],
                                    visitTime: timeSlot || "2:00 PM",
                                    adultCount: guestInfo.adults || 0,
                                    childCount: guestInfo.children || 0,
                                    restaurantId: parseInt(restaurantId),
                                    restaurantName: selectedOption.text,
                                    
                                    // Meal Information
                                    mealType: mealType || "",
                                    mealSpecificType: document.getElementById(`day${day}_meal_specific_type_${index}`)?.value || "",
                                    
                                    // Meal Description (array of meal items)
                                    MealDescription: [{
                                        item_name: dishName || null,
                                        name: dishName || "",
                                        price: totalPrice,
                                        meal_id: parseInt(mealId) || parseInt(restaurantId),
                                        category: document.getElementById(`day${day}_meal_category_${index}`)?.value || "",
                                        item_type: document.getElementById(`day${day}_meal_item_type_${index}`)?.value || ""
                                    }],
                                    
                                    // Pricing
                                    totalPrice: totalPrice,
                                    mealPrice: totalPrice,
                                    
                                    // Transport
                                    transport: document.getElementById(`day${day}_restaurant_transport_${index}`)?.value || null,
                                    transportPrice: parseFloat(document.getElementById(`day${day}_restaurant_transport_price_${index}`)?.value || 0),
                                    
                                    // Price Types and DMC
                                    priceTypes: [document.getElementById(`day${day}_restaurant_price_type_${index}`)?.value || "dmc"],
                                    dmc_id: document.getElementById('dmc_id')?.value || "4",
                                    bookingType: document.getElementById(`day${day}_restaurant_booking_type_${index}`)?.value || "booking"
                                });
                            }
                        }
                    });
                    
                    const restaurantDataField = document.getElementById('restaurant_data');
                    if (restaurantDataField) {
                        restaurantDataField.value = JSON.stringify(restaurantDataArray);
                        console.log('Restaurant data updated:', restaurantDataArray);
                    }
                    
                    // Update package total price display
                    updatePackageTotalPriceDisplay();
                }

                // Function to collect transport data (including entry/exit ports)
                function updateTransportDataField() {
                    const transportDataArray = [];
                    const entryPortArray = [];
                    const exitPortArray = [];
                    
                    // Get customer information from the Customer Information form
                    const customerData = getCustomerData();
                    
                    // Get current DMC information
                    const dmcInfo = {
                        dmc_id: '{{ auth()->user()->created_by }}',
                        user_id: '{{ auth()->user()->userId }}',
                        role_id: '{{ auth()->user()->role_id }}'
                    };
                    
                    // Get all transport selections
                    document.querySelectorAll('select[name*="_pickup_zone_id"]').forEach(pickupSelect => {
                        if (pickupSelect.value) {
                            const nameMatch = pickupSelect.name.match(/day(\d+)_(\w+)_pickup_zone_id/);
                            if (nameMatch) {
                                const day = nameMatch[1];
                                const section = nameMatch[2]; // entry, exit, transport
                                
                                const dropoffSelect = document.querySelector(`select[name="day${day}_${section}_dropoff_zone_id"]`);
                                const vehicleSelect = document.querySelector(`select[name="day${day}_${section}_vehicle_id"]`);
                                const serviceTypeSelect = document.querySelector(`select[name="day${day}_${section}_service_type"]`);
                                const timeSelect = document.querySelector(`select[name="day${day}_${section}_pickup_time"], select[name="day${day}_${section}_time"]`);
                                const dateInput = document.querySelector(`input[name="day${day}_${section}_date"], input[name="day${day}_${section}_pickup_date"]`);
                                
                                if (dropoffSelect?.value && vehicleSelect?.value && serviceTypeSelect?.value) {
                                    // Get zone details for pickup and dropoff
                                    const pickupZone = pickupSelect.options[pickupSelect.selectedIndex];
                                    const dropoffZone = dropoffSelect.options[dropoffSelect.selectedIndex];
                                    const vehicle = vehicleSelect.options[vehicleSelect.selectedIndex];
                                    
                                    // Get guest counts from the form
                                    const adultCount = parseInt(document.getElementById('adult_count')?.value || 0);
                                    const childCount = parseInt(document.getElementById('child_count')?.value || 0);
                                    
                                    // Get pricing data from hidden fields (this is the correct way)
                                    const basePrice = parseFloat(document.getElementById(`day${day}_${section}_base_price`)?.value || 0);
                                    const totalPrice = parseFloat(document.getElementById(`day${day}_${section}_total_price`)?.value || 0);
                                    const serviceType = document.getElementById(`day${day}_${section}_service_type`)?.value || '';
                                    
                                    console.log(`Transport pricing for day ${day}, section ${section}:`);
                                    console.log(`- Base Price: $${basePrice}`);
                                    console.log(`- Total Price: $${totalPrice}`);
                                    console.log(`- Service Type: ${serviceType}`);
                                    console.log(`- Guest Count: ${adultCount + childCount}`);
                                    
                                    // Get pickup and dropoff coordinates (these should come from zone data)
                                    const pickupCoords = {
                                        lat: pickupZone.dataset.latitude || "",
                                        lng: pickupZone.dataset.longitude || ""
                                    };
                                    const dropoffCoords = {
                                        lat: dropoffZone.dataset.latitude || "",
                                        lng: dropoffZone.dataset.longitude || ""
                                    };
                                    
                                    // Create transport data based on section type
                                    let transportData;
                                    
                                    if (section === 'entry') {
                                        // Entry Port Data Structure
                                        transportData = {
                                            // Transport Information
                                            bookingDate: dateInput?.value || new Date().toISOString().split('T')[0],
                                            vehicles_id: vehicleSelect.value,
                                            image: vehicle.dataset.image || "",
                                            dmc_id: document.getElementById('dmc_id')?.value || "4",
                                            vehicles_name: vehicle.text,
                                            Mode: "dmc",
                                            type: serviceTypeSelect.value || "",
                                            
                                            // Pickup and Dropoff Information
                                            entrypickup: pickupZone.text,
                                            entrydropoff: dropoffZone.text,
                                            PickupPlaceid: pickupCoords,
                                            DropoffPlaceid: dropoffCoords,
                                            pickupdate: dateInput?.value || new Date().toISOString().split('T')[0],
                                            entrytime: timeSelect?.value || "",
                                            
                                            // Guest Information
                                            adults: adultCount.toString(),
                                            children: childCount.toString(),
                                            
                                            // Pricing and Details
                                            totalPrice: totalPrice.toString(),
                                            Tax: document.getElementById(`day${day}_${section}_tax`)?.value || "0.00",
                                            distance: document.getElementById(`day${day}_${section}_distance`)?.value || "0",
                                            Night_Start_Time: document.getElementById(`day${day}_${section}_night_start_time`)?.value || "",
                                            Night_End_Time: document.getElementById(`day${day}_${section}_night_end_time`)?.value || "",
                                            city: pickupZone.dataset.city || "",
                                            country: pickupZone.dataset.country || "",
                                            
                                            // Customer Information (from Customer Information form)
                                            fullName: customerData.fullName,
                                            email: customerData.email,
                                            phone: customerData.phone,
                                            countryCode: customerData.countryCode,
                                            address1: customerData.address1,
                                            address2: customerData.address2,
                                            state: customerData.state,
                                            zip: customerData.zip,
                                            specialRequests: customerData.specialRequests,
                                            
                                            // User Info (duplicate for compatibility)
                                            userInfo: {
                                                fullName: customerData.fullName,
                                                email: customerData.email,
                                                phone: customerData.phone,
                                                countryCode: customerData.countryCode,
                                                address1: customerData.address1,
                                                address2: customerData.address2,
                                                state: customerData.state,
                                                zip: customerData.zip,
                                                specialRequests: customerData.specialRequests
                                            },
                                            
                                            bookingType: document.getElementById(`day${day}_${section}_booking_type`)?.value || "booking"
                                        };
                                    } else if (section === 'exit') {
                                        // Exit Port Data Structure
                                        transportData = {
                                            // Transport Information
                                            bookingDate: dateInput?.value || new Date().toISOString().split('T')[0],
                                            vehicles_id: vehicleSelect.value,
                                            vehicles_name: vehicle.text,
                                            dmc_id: document.getElementById('dmc_id')?.value || "4",
                                            Mode: "dmc",
                                            type: serviceTypeSelect.value || "",
                                            image: vehicle.dataset.image || "",
                                            
                                            // Pickup and Dropoff Information
                                            exitpickup: pickupZone.text,
                                            exitdropoff: dropoffZone.text,
                                            PickupPlaceid: null,
                                            DropoffPlaceid: null,
                                            exitpickupdate: dateInput?.value || new Date().toISOString().split('T')[0],
                                            entrytime: timeSelect?.value || "",
                                            
                                            // Guest Information
                                            adults: adultCount.toString(),
                                            children: childCount.toString(),
                                            
                                            // Pricing and Details
                                            totalPrice: totalPrice.toString(),
                                            Tax: document.getElementById(`day${day}_${section}_tax`)?.value || "0.00",
                                            distance: document.getElementById(`day${day}_${section}_distance`)?.value || "0",
                                            Night_Start_Time: document.getElementById(`day${day}_${section}_night_start_time`)?.value || "",
                                            Night_End_Time: document.getElementById(`day${day}_${section}_night_end_time`)?.value || "",
                                            city: pickupZone.dataset.city || "",
                                            country: pickupZone.dataset.country || "",
                                            
                                            // Customer Information (from Customer Information form)
                                            fullName: customerData.fullName,
                                            email: customerData.email,
                                            phone: customerData.phone,
                                            countryCode: customerData.countryCode,
                                            address1: customerData.address1,
                                            address2: customerData.address2,
                                            state: customerData.state,
                                            zip: customerData.zip,
                                            specialRequests: customerData.specialRequests,
                                            
                                            // User Info (duplicate for compatibility)
                                            userInfo: {
                                                fullName: customerData.fullName,
                                                email: customerData.email,
                                                phone: customerData.phone,
                                                address1: customerData.address1,
                                                address2: customerData.address2,
                                                state: customerData.state,
                                                zip: customerData.zip,
                                                specialRequests: customerData.specialRequests
                                            },
                                            
                                            bookingType: document.getElementById(`day${day}_${section}_booking_type`)?.value || "enquiry"
                                        };
                                    } else {
                                        // Regular Transport Data Structure
                                        transportData = {
                                            // Transport Information
                                            bookingDate: dateInput?.value || new Date().toISOString().split('T')[0],
                                            vehicles_id: vehicleSelect.value,
                                            image: vehicle.dataset.image || "",
                                            dmc_id: document.getElementById('dmc_id')?.value || "4",
                                            vehicles_name: vehicle.text,
                                            Mode: "dmc",
                                            type: serviceTypeSelect.value || "",
                                            
                                            // Pickup and Dropoff Information
                                            entrypickup: pickupZone.text,
                                            entrydropoff: dropoffZone.text,
                                            PickupPlaceid: pickupCoords,
                                            DropoffPlaceid: dropoffCoords,
                                            pickupdate: dateInput?.value || new Date().toISOString().split('T')[0],
                                            entrytime: timeSelect?.value || "",
                                            
                                            // Guest Information
                                            adults: adultCount.toString(),
                                            children: childCount.toString(),
                                            
                                            // Pricing and Details
                                            totalPrice: totalPrice.toString(),
                                            Tax: document.getElementById(`day${day}_${section}_tax`)?.value || "0.00",
                                            distance: document.getElementById(`day${day}_${section}_distance`)?.value || "0",
                                            Night_Start_Time: document.getElementById(`day${day}_${section}_night_start_time`)?.value || "",
                                            Night_End_Time: document.getElementById(`day${day}_${section}_night_end_time`)?.value || "",
                                            city: pickupZone.dataset.city || "",
                                            country: pickupZone.dataset.country || "",
                                            
                                            // Customer Information (from Customer Information form)
                                            fullName: customerData.fullName,
                                            email: customerData.email,
                                            phone: customerData.phone,
                                            countryCode: customerData.countryCode,
                                            address1: customerData.address1,
                                            address2: customerData.address2,
                                            state: customerData.state,
                                            zip: customerData.zip,
                                            specialRequests: customerData.specialRequests,
                                            
                                            // User Info (duplicate for compatibility)
                                            userInfo: {
                                                fullName: customerData.fullName,
                                                email: customerData.email,
                                                phone: customerData.phone,
                                                countryCode: customerData.countryCode,
                                                address1: customerData.address1,
                                                address2: customerData.address2,
                                                state: customerData.state,
                                                zip: customerData.zip,
                                                specialRequests: customerData.specialRequests
                                            },
                                            
                                            bookingType: document.getElementById(`day${day}_${section}_booking_type`)?.value || "booking"
                                        };
                                    }
                                    
                                    if (section === 'entry') {
                                        entryPortArray.push(transportData);
                                    } else if (section === 'exit') {
                                        exitPortArray.push(transportData);
                                    } else {
                                        transportDataArray.push(transportData);
                                    }
                                }
                            }
                        }
                    });
                    
                    // Update all transport-related fields
                    const transportDataField = document.getElementById('transport_data');
                    if (transportDataField) {
                        transportDataField.value = JSON.stringify(transportDataArray);
                    }
                    
                    const entryPortDataField = document.getElementById('entry_port_data');
                    if (entryPortDataField) {
                        entryPortDataField.value = JSON.stringify(entryPortArray);
                    }
                    
                    const exitPortDataField = document.getElementById('exit_port_data');
                    if (exitPortDataField) {
                        exitPortDataField.value = JSON.stringify(exitPortArray);
                    }
                    
                    console.log('Transport data updated:', {
                        transport: transportDataArray,
                        entry_port: entryPortArray,
                        exit_port: exitPortArray
                    });
                    
                    // Update package total price display
                    updatePackageTotalPriceDisplay();
                }

                // Helper function to parse guest summary text
                function parseGuestSummary(summaryText) {
                    console.log('Parsing guest summary:', summaryText);
                    
                    const adultMatch = summaryText.match(/(\d+)\s+adults/);
                    const maleMatch = summaryText.match(/(\d+)\s+male/);
                    const femaleMatch = summaryText.match(/(\d+)\s+female/);
                    const childMatch = summaryText.match(/(\d+)\s+children/);
                    const infantMatch = summaryText.match(/(\d+)\s+infants/);
                    
                    const adults = adultMatch ? parseInt(adultMatch[1]) : 0;
                    
                    // For now, assume seniors are part of adults (this can be enhanced later with age-based logic)
                    // In a real implementation, you might want to add a senior age input field
                    const seniors = 0; // Placeholder - can be enhanced with age-based calculation
                    const regularAdults = adults - seniors;
                    
                    const result = {
                        adults: regularAdults,
                        male: maleMatch ? parseInt(maleMatch[1]) : 0,
                        female: femaleMatch ? parseInt(femaleMatch[1]) : 0,
                        children: childMatch ? parseInt(childMatch[1]) : 0,
                        infants: infantMatch ? parseInt(infantMatch[1]) : 0,
                        seniors: seniors
                    };
                    
                    console.log('Parsed guest info:', result);
                    return result;
                }

                // Function to calculate total price for all services
                function calculateTotalPackagePrice() {
                    let totalPrice = 0;
                    
                    try {
                        // Calculate hotel prices
                        const hotelData = document.getElementById('hotel_data')?.value;
                        if (hotelData) {
                            const hotels = JSON.parse(hotelData);
                            console.log('=== CALCULATING HOTEL PRICES ===');
                            console.log('Hotels data:', hotels);
                            hotels.forEach((hotel, index) => {
                                if (hotel.totalPrice && !isNaN(parseFloat(hotel.totalPrice))) {
                                    const hotelPrice = parseFloat(hotel.totalPrice);
                                    totalPrice += hotelPrice;
                                    console.log(`Hotel ${index + 1} (${hotel.hotel_name || 'Unknown'}): $${hotelPrice} added to total`);
                                } else {
                                    console.warn(`Hotel ${index + 1} (${hotel.hotel_name || 'Unknown'}): Invalid or missing totalPrice:`, hotel.totalPrice);
                                }
                            });
                            console.log('Total after hotels:', totalPrice);
                        }
                        
                        // Calculate attraction prices
                        const attractionData = document.getElementById('attraction_data')?.value;
                        if (attractionData) {
                            const attractions = JSON.parse(attractionData);
                            attractions.forEach(attraction => {
                                if (attraction.totalPrice && !isNaN(parseFloat(attraction.totalPrice))) {
                                    totalPrice += parseFloat(attraction.totalPrice);
                                }
                            });
                        }
                        
                        // Calculate restaurant prices
                        const restaurantData = document.getElementById('restaurant_data')?.value;
                        if (restaurantData) {
                            const restaurants = JSON.parse(restaurantData);
                            restaurants.forEach(restaurant => {
                                if (restaurant.totalPrice && !isNaN(parseFloat(restaurant.totalPrice))) {
                                    totalPrice += parseFloat(restaurant.totalPrice);
                                }
                            });
                        }
                        
                        // Calculate guide prices
                        const guideData = document.getElementById('guide_data')?.value;
                        if (guideData) {
                            const guides = JSON.parse(guideData);
                            guides.forEach(guide => {
                                if (guide.totalPrice && !isNaN(parseFloat(guide.totalPrice))) {
                                    totalPrice += parseFloat(guide.totalPrice);
                                }
                            });
                        }
                        
                        // Calculate transport prices
                        const transportData = document.getElementById('transport_data')?.value;
                        if (transportData) {
                            const transports = JSON.parse(transportData);
                            transports.forEach(transport => {
                                if (transport.totalPrice && !isNaN(parseFloat(transport.totalPrice))) {
                                    totalPrice += parseFloat(transport.totalPrice);
                                }
                            });
                        }
                        
                        // Calculate entry port prices
                        const entryPortData = document.getElementById('entry_port_data')?.value;
                        if (entryPortData) {
                            const entryPorts = JSON.parse(entryPortData);
                            entryPorts.forEach(entryPort => {
                                if (entryPort.totalPrice && !isNaN(parseFloat(entryPort.totalPrice))) {
                                    totalPrice += parseFloat(entryPort.totalPrice);
                                }
                            });
                        }
                        
                        // Calculate exit port prices
                        const exitPortData = document.getElementById('exit_port_data')?.value;
                        if (exitPortData) {
                            const exitPorts = JSON.parse(exitPortData);
                            exitPorts.forEach(exitPort => {
                                if (exitPort.totalPrice && !isNaN(parseFloat(exitPort.totalPrice))) {
                                    totalPrice += parseFloat(exitPort.totalPrice);
                                }
                            });
                        }
                        
                        console.log('Total package price calculated:', totalPrice);
                        return totalPrice.toFixed(2);
                        
                    } catch (error) {
                        console.error('Error calculating total price:', error);
                        return '0.00';
                    }
                }

                // Function to update the package total price display
                function updatePackageTotalPriceDisplay() {
                    const totalPrice = calculateTotalPackagePrice();
                    const totalPriceElement = document.getElementById('packageTotalPrice');
                    if (totalPriceElement) {
                        totalPriceElement.textContent = `$${totalPrice}`;
                        console.log('Package total price display updated:', totalPrice);
                    }
                    
                    // Also update the price breakdown display
                    updatePriceBreakdownDisplay();
                }

                // Function to display price breakdown for each service
                function updatePriceBreakdownDisplay() {
                    let breakdownHTML = '';
                    let totalCalculated = 0;
                    
                    try {
                        // Hotel prices
                        const hotelData = document.getElementById('hotel_data')?.value;
                        if (hotelData) {
                            const hotels = JSON.parse(hotelData);
                            hotels.forEach((hotel, index) => {
                                if (hotel.totalPrice && !isNaN(parseFloat(hotel.totalPrice))) {
                                    const price = parseFloat(hotel.totalPrice);
                                    totalCalculated += price;
                                    const roomInfo = hotel.rooms && hotel.rooms[0] ? 
                                        `${hotel.rooms[0].room_type} (${hotel.number_of_rooms || 1} room${hotel.number_of_rooms > 1 ? 's' : ''} × ${hotel.total_nights || 1} night${hotel.total_nights > 1 ? 's' : ''})` : '';
                                    breakdownHTML += `
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span><i class="ri-hotel-bed-line me-2"></i>Hotel ${index + 1}: ${hotel.hotel_name || 'Hotel'}</span>
                                            <span class="badge bg-primary">$${price.toFixed(2)}</span>
                                        </div>
                                        <small class="text-muted ms-4">${roomInfo}</small>
                                    `;
                                }
                            });
                        }
                        
                        // Attraction prices
                        const attractionData = document.getElementById('attraction_data')?.value;
                        if (attractionData) {
                            const attractions = JSON.parse(attractionData);
                            attractions.forEach((attraction, index) => {
                                if (attraction.totalPrice && !isNaN(parseFloat(attraction.totalPrice))) {
                                    const price = parseFloat(attraction.totalPrice);
                                    totalCalculated += price;
                                    const ticketInfo = attraction.ticket_details ? 
                                        `Ticket: ${attraction.ticketName} (${attraction.adultCount} adults, ${attraction.childCount} children, ${attraction.seniorCount} seniors)` : '';
                                    breakdownHTML += `
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span><i class="ri-camera-3-line me-2"></i>Attraction ${index + 1}: ${attraction.AttractionName || 'Attraction'}</span>
                                            <span class="badge bg-info">$${price.toFixed(2)}</span>
                                        </div>
                                        <small class="text-muted ms-4">${ticketInfo}</small>
                                    `;
                                }
                            });
                        }
                        
                        // Restaurant prices
                        const restaurantData = document.getElementById('restaurant_data')?.value;
                        if (restaurantData) {
                            const restaurants = JSON.parse(restaurantData);
                            restaurants.forEach((restaurant, index) => {
                                if (restaurant.totalPrice && !isNaN(parseFloat(restaurant.totalPrice))) {
                                    const price = parseFloat(restaurant.totalPrice);
                                    totalCalculated += price;
                                    const mealInfo = restaurant.MealDescription && restaurant.MealDescription[0] ? 
                                        `Meal: ${restaurant.MealDescription[0].name} (${restaurant.adultCount} adults, ${restaurant.childCount} children)` : '';
                                    breakdownHTML += `
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span><i class="ri-restaurant-2-line me-2"></i>Restaurant ${index + 1}: ${restaurant.restaurantName || 'Restaurant'}</span>
                                            <span class="badge bg-warning text-dark">$${price.toFixed(2)}</span>
                                        </div>
                                        <small class="text-muted ms-4">${mealInfo}</small>
                                    `;
                                }
                            });
                        }
                        
                        // Guide prices
                        const guideData = document.getElementById('guide_data')?.value;
                        if (guideData) {
                            const guides = JSON.parse(guideData);
                            guides.forEach((guide, index) => {
                                if (guide.totalPrice && !isNaN(parseFloat(guide.totalPrice))) {
                                    const price = parseFloat(guide.totalPrice);
                                    totalCalculated += price;
                                    breakdownHTML += `
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span><i class="ri-map-pin-user-line me-2"></i>Guide ${index + 1}: ${guide.guide_name || 'Guide'}</span>
                                            <span class="badge bg-success">$${price.toFixed(2)}</span>
                                        </div>
                                    `;
                                }
                            });
                        }
                        
                        // Transport prices
                        const transportData = document.getElementById('transport_data')?.value;
                        if (transportData) {
                            const transports = JSON.parse(transportData);
                            transports.forEach((transport, index) => {
                                if (transport.totalPrice && !isNaN(parseFloat(transport.totalPrice))) {
                                    const price = parseFloat(transport.totalPrice);
                                    totalCalculated += price;
                                    breakdownHTML += `
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span><i class="ri-car-line me-2"></i>Transport ${index + 1}: ${transport.vehicles_name || 'Transport'}</span>
                                            <span class="badge bg-secondary">$${price.toFixed(2)}</span>
                                        </div>
                                    `;
                                }
                            });
                        }
                        
                        // Entry port prices
                        const entryPortData = document.getElementById('entry_port_data')?.value;
                        if (entryPortData) {
                            const entryPorts = JSON.parse(entryPortData);
                            entryPorts.forEach((entryPort, index) => {
                                if (entryPort.totalPrice && !isNaN(parseFloat(entryPort.totalPrice))) {
                                    const price = parseFloat(entryPort.totalPrice);
                                    totalCalculated += price;
                                    breakdownHTML += `
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span><i class="ri-map-pin-line me-2"></i>Entry Port ${index + 1}: ${entryPort.vehicles_name || 'Entry Transport'}</span>
                                            <span class="badge bg-info">$${price.toFixed(2)}</span>
                                        </div>
                                    `;
                                }
                            });
                        }
                        
                        // Exit port prices
                        const exitPortData = document.getElementById('exit_port_data')?.value;
                        if (exitPortData) {
                            const exitPorts = JSON.parse(exitPortData);
                            exitPorts.forEach((exitPort, index) => {
                                if (exitPort.totalPrice && !isNaN(parseFloat(exitPort.totalPrice))) {
                                    const price = parseFloat(exitPort.totalPrice);
                                    totalCalculated += price;
                                    breakdownHTML += `
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span><i class="ri-map-pin-line me-2"></i>Exit Port ${index + 1}: ${exitPort.vehicles_name || 'Exit Transport'}</span>
                                            <span class="badge bg-info">$${price.toFixed(2)}</span>
                                        </div>
                                    `;
                                }
                            });
                        }
                        
                        // Add total at the bottom
                        if (breakdownHTML) {
                            breakdownHTML += `
                                <hr class="my-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold"><i class="ri-money-dollar-circle-line me-2"></i>Total Package Price:</span>
                                    <span class="h5 text-success mb-0">$${totalCalculated.toFixed(2)}</span>
                                </div>
                            `;
                        } else {
                            breakdownHTML = '<p class="text-muted">No services added yet</p>';
                        }
                        
                        // Update the breakdown display
                        const breakdownElement = document.getElementById('priceBreakdown');
                        if (breakdownElement) {
                            breakdownElement.innerHTML = breakdownHTML;
                        }
                        
                    } catch (error) {
                        console.error('Error updating price breakdown:', error);
                    }
                }

                async function saveAllBookings() {
                    if (!validateCustomerInfo()) {
                        return false;
                    }

                    // Get tour and agent IDs
                    const agentId = document.getElementById('agent_id').value;
                    const tourId = window.currentTourId;

                    if (!tourId) {
                        alert('Tour ID not found. Please create a tour first.');
                        return false;
                    }

                    // Manually calculate guide pricing before updating data fields
                    calculateAllGuidePricing();
                    
                    // Update all service data fields before sending
                    updateHotelDataField();
                    updateAttractionDataField();
                    updateGuideDataField();
                    updateRestaurantDataField();
                    updateTransportDataField();

                    // Get all service data from hidden fields (with null checks)
                    const hotelData = document.getElementById('hotel_data')?.value || '';
                    const attractionData = document.getElementById('attraction_data')?.value || '';
                    const restaurantData = document.getElementById('restaurant_data')?.value || '';
                    const guideData = document.getElementById('guide_data')?.value || '';
                    const transportData = document.getElementById('transport_data')?.value || '';
                    const entryPortData = document.getElementById('entry_port_data')?.value || '';
                    const exitPortData = document.getElementById('exit_port_data')?.value || '';

                    // Check if at least one service has data
                    if (!hotelData && !attractionData && !restaurantData && !guideData && 
                        !transportData && !entryPortData && !exitPortData) {
                        alert('Please add at least one service (hotel, attraction, guide, restaurant, or transport)');
                        return false;
                    }

                    // Calculate total price for all services
                    const totalPrice = calculateTotalPackagePrice();

                    try {
                        // Prepare form data
                        const formData = new FormData();
                        formData.append('_token', document.querySelector('input[name="_token"]').value);
                        formData.append('tour_id', tourId);
                        formData.append('agent_id', agentId);
                        formData.append('hotel_data', hotelData || '');
                        formData.append('attraction_data', attractionData || '');
                        formData.append('restaurant_data', restaurantData || '');
                        formData.append('guide_data', guideData || '');
                        formData.append('transport_data', transportData || '');
                        formData.append('entry_port_data', entryPortData || '');
                        formData.append('exit_port_data', exitPortData || '');
                        formData.append('total_price', totalPrice);

                        // Send request to store orders
                        const storeOrdersUrl = '{{ route('single-tour-package.store-orders') }}';
                        console.log('Store orders URL:', storeOrdersUrl);
                 
                        const response = await fetch(storeOrdersUrl, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: formData
                        });

                        if (!response.ok) {
                            console.error('HTTP Error:', response.status, response.statusText);
                            const errorText = await response.text();
                            console.error('Error Response:', errorText);
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }

                        const result = await response.json();
                        
                        if (result.success) {
                            console.log('Service orders saved:', result);
                            
                            // Store data in session and redirect to thank you page
                            if (result.redirect_url) {
                                // Use a form to pass data via session
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action = result.redirect_url;
                                
                                // Add CSRF token
                                const csrfInput = document.createElement('input');
                                csrfInput.type = 'hidden';
                                csrfInput.name = '_token';
                                csrfInput.value = document.querySelector('input[name="_token"]').value;
                                form.appendChild(csrfInput);
                                
                                // Add tour details
                                const tourDetailsInput = document.createElement('input');
                                tourDetailsInput.type = 'hidden';
                                tourDetailsInput.name = 'tour_details';
                                tourDetailsInput.value = JSON.stringify(result.tour_details);
                                form.appendChild(tourDetailsInput);
                                
                                // Add created orders
                                const ordersInput = document.createElement('input');
                                ordersInput.type = 'hidden';
                                ordersInput.name = 'created_orders';
                                ordersInput.value = JSON.stringify(result.created_orders);
                                form.appendChild(ordersInput);
                                
                                document.body.appendChild(form);
                                form.submit();
                            } else {
                                // Fallback to staying on same page
                                showNotification('All service orders saved successfully!', 'success');
                            }
                        } else {
                            alert('Error saving orders: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Error saving orders:', error);
                        alert('Error saving orders: ' + error.message);
                    }
                }
            </script>
                </form>
            </div>
            
            <!-- Enquiry Details Sidebar -->
            @if($enquiry)
            <div class="col-lg-4">
                <div class="sticky-top" style="top: 20px;">
                    <!-- Enquiry Overview Card -->
                    <div class="card border-0 shadow-lg mb-4 enquiry-sidebar">
                        <div class="card-header bg-gradient-info text-white">
                            <div class="d-flex align-items-center">
                                <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                                    <i class="ri-file-list-3-line fs-5 text-white"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 text-white fw-bold">Enquiry Details</h5>
                                    <p class="mb-0 opacity-75 small">{{ $enquiry->display_id ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <!-- Basic Info Section -->
                            <div class="border-bottom p-3">
                                <h6 class="text-primary fw-bold mb-3">
                                    <i class="ri-information-line me-2"></i>Basic Information
                                </h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="bg-light rounded p-2">
                                            <small class="text-muted d-block">Destination</small>
                                            <strong class="text-dark">{{ $enquiry->country ?? 'N/A' }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-light rounded p-2">
                                            <small class="text-muted d-block">City</small>
                                            <strong class="text-dark">{{ $enquiry->city ?? 'N/A' }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-primary bg-opacity-10 rounded p-2">
                                            <small class="text-primary d-block">Check-in</small>
                                            <strong class="text-dark">{{ $enquiry->check_in_time ? \Carbon\Carbon::parse($enquiry->check_in_time)->format('M d, Y') : 'N/A' }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-warning bg-opacity-10 rounded p-2">
                                            <small class="text-warning d-block">Check-out</small>
                                            <strong class="text-dark">{{ $enquiry->check_out_time ? \Carbon\Carbon::parse($enquiry->check_out_time)->format('M d, Y') : 'N/A' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Guests Section -->
                            <div class="border-bottom p-3">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="ri-group-line me-2"></i>Guest Information
                                </h6>
                                <div class="row g-2">
                                    <div class="col-3">
                                        <div class="text-center bg-success bg-opacity-10 rounded p-2">
                                            <div class="fw-bold text-success fs-5">{{ $enquiry->adult ?? 0 }}</div>
                                            <small class="text-muted">Adults</small>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="text-center bg-info bg-opacity-10 rounded p-2">
                                            <div class="fw-bold text-info fs-5">{{ $enquiry->child ?? 0 }}</div>
                                            <small class="text-muted">Children</small>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="text-center bg-warning bg-opacity-10 rounded p-2">
                                            <div class="fw-bold text-warning fs-5">{{ $enquiry->infant ?? 0 }}</div>
                                            <small class="text-muted">Infants</small>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="text-center bg-purple bg-opacity-10 rounded p-2">
                                            <div class="fw-bold text-purple fs-5">${{ number_format($enquiry->approx_price ?? 0) }}</div>
                                            <small class="text-muted">Budget</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mt-2">
                                    <div class="col-6">
                                        <div class="bg-light rounded p-2">
                                            <small class="text-muted d-block">Male</small>
                                            <strong class="text-dark">{{ $enquiry->male_count ?? 0 }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-light rounded p-2">
                                            <small class="text-muted d-block">Female</small>
                                            <strong class="text-dark">{{ $enquiry->female_count ?? 0 }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Services Section -->
                            @if($hotels->count() > 0 || $attractions->count() > 0 || $guides->count() > 0 || $vehicles->count() > 0 || $meals->count() > 0)
                            <div class="p-3">
                                <h6 class="text-warning fw-bold mb-3">
                                    <i class="ri-service-line me-2"></i>Selected Services
                                </h6>
                                
                                @if($hotels->count() > 0)
                                <div class="service-item mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-primary rounded-circle p-1 me-2">
                                            <i class="ri-hotel-line text-white small"></i>
                                        </div>
                                        <strong class="text-primary">Hotels ({{ $hotels->count() }})</strong>
                                    </div>
                                    @foreach($hotels as $hotel)
                                    <div class="bg-light rounded p-2 mb-1">
                                        <div class="small fw-semibold text-dark">{{ $hotel->name ?? 'Hotel Name' }}</div>
                                        <div class="text-muted small">{{ $hotel->location ?? 'Location' }}</div>
                                    </div>
                                    @endforeach
                                    @if($enquiry->hotel_remarks)
                                    <div class="small text-muted mt-1">
                                        <i class="ri-chat-quote-line me-1"></i>{{ $enquiry->hotel_remarks }}
                                    </div>
                                    @endif
                                </div>
                                @endif

                                @if($attractions->count() > 0)
                                <div class="service-item mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-success rounded-circle p-1 me-2">
                                            <i class="ri-map-pin-line text-white small"></i>
                                        </div>
                                        <strong class="text-success">Attractions ({{ $attractions->count() }})</strong>
                                    </div>
                                    @foreach($attractions as $attraction)
                                    <div class="bg-light rounded p-2 mb-1">
                                        <div class="small fw-semibold text-dark">{{ $attraction->name ?? 'Attraction Name' }}</div>
                                        <div class="text-muted small">{{ $attraction->location ?? 'Location' }}</div>
                                    </div>
                                    @endforeach
                                    @if($enquiry->attraction_remarks)
                                    <div class="small text-muted mt-1">
                                        <i class="ri-chat-quote-line me-1"></i>{{ $enquiry->attraction_remarks }}
                                    </div>
                                    @endif
                                </div>
                                @endif

                                @if($meals->count() > 0)
                                <div class="service-item mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-warning rounded-circle p-1 me-2">
                                            <i class="ri-restaurant-line text-white small"></i>
                                        </div>
                                        <strong class="text-warning">Restaurants ({{ $meals->count() }})</strong>
                                    </div>
                                    @foreach($meals as $meal)
                                    <div class="bg-light rounded p-2 mb-1">
                                        <div class="small fw-semibold text-dark">{{ $meal->name ?? 'Restaurant Name' }}</div>
                                        <div class="text-muted small">{{ $meal->location ?? 'Location' }}</div>
                                    </div>
                                    @endforeach
                                    @if($enquiry->restaurant_remarks)
                                    <div class="small text-muted mt-1">
                                        <i class="ri-chat-quote-line me-1"></i>{{ $enquiry->restaurant_remarks }}
                                    </div>
                                    @endif
                                </div>
                                @endif

                                @if($vehicles->count() > 0)
                                <div class="service-item mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-info rounded-circle p-1 me-2">
                                            <i class="ri-car-line text-white small"></i>
                                        </div>
                                        <strong class="text-info">Vehicles ({{ $vehicles->count() }})</strong>
                                    </div>
                                    @foreach($vehicles as $vehicle)
                                    <div class="bg-light rounded p-2 mb-1">
                                        <div class="small fw-semibold text-dark">{{ $vehicle->vehicle_name ?? 'Vehicle Name' }}</div>
                                        <div class="text-muted small">{{ $vehicle->seating_capacity ?? 'N/A' }} seats</div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif

                                @if($guides->count() > 0)
                                <div class="service-item mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-purple rounded-circle p-1 me-2">
                                            <i class="ri-user-line text-white small"></i>
                                        </div>
                                        <strong class="text-purple">Guides ({{ $guides->count() }})</strong>
                                    </div>
                                    @foreach($guides as $guide)
                                    <div class="bg-light rounded p-2 mb-1">
                                        <div class="small fw-semibold text-dark">{{ $guide->name ?? 'Guide Name' }}</div>
                                        <div class="text-muted small">{{ $guide->experience ?? 'N/A' }} years exp.</div>
                                    </div>
                                    @endforeach
                                    @if($enquiry->guide_remarks)
                                    <div class="small text-muted mt-1">
                                        <i class="ri-chat-quote-line me-1"></i>{{ $enquiry->guide_remarks }}
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Custom Styles for Enquiry Sidebar -->
<style>
    .enquiry-sidebar {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important;
        overflow: hidden;
    }
    
    .enquiry-sidebar .card-header {
        background: linear-gradient(135deg, #36d1dc 0%, #5b86e5 100%) !important;
        border: none;
        position: relative;
    }
    
    .enquiry-sidebar .card-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        opacity: 0.3;
    }
    
    .enquiry-sidebar .card-body {
        background: #ffffff;
    }
    
    .service-item {
        border-left: 4px solid transparent;
        padding-left: 12px;
        transition: all 0.3s ease;
    }
    
    .service-item:hover {
        transform: translateX(5px);
        border-left-color: #667eea;
    }
    
    .bg-purple {
        background-color: #8b5cf6 !important;
    }
    
    .text-purple {
        color: #8b5cf6 !important;
    }
    
    .bg-purple.bg-opacity-10 {
        background-color: rgba(139, 92, 246, 0.1) !important;
    }
    
    .sticky-top {
        transition: all 0.3s ease;
    }
    
    .enquiry-sidebar .service-item .bg-light {
        background: linear-gradient(135deg, #f8f9ff 0%, #e8eaff 100%) !important;
        border: 1px solid rgba(102, 126, 234, 0.1);
        transition: all 0.3s ease;
    }
    
    .enquiry-sidebar .service-item .bg-light:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
    }
    
    /* Responsive adjustments */
    @media (max-width: 991.98px) {
        .sticky-top {
            position: relative !important;
            top: auto !important;
        }
        
        .col-lg-4 {
            margin-top: 20px;
        }
    }
    
    /* Animation for cards */
    .enquiry-sidebar {
        animation: slideInRight 0.6s ease-out;
    }
    
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    /* Hover effects for info boxes */
    .enquiry-sidebar .bg-light,
    .enquiry-sidebar .bg-primary.bg-opacity-10,
    .enquiry-sidebar .bg-warning.bg-opacity-10,
    .enquiry-sidebar .bg-success.bg-opacity-10,
    .enquiry-sidebar .bg-info.bg-opacity-10,
    .enquiry-sidebar .bg-purple.bg-opacity-10 {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .enquiry-sidebar .bg-light:hover {
        background: linear-gradient(135deg, #e8eaff 0%, #d1d5ff 100%) !important;
    }
    
    .enquiry-sidebar .bg-primary.bg-opacity-10:hover {
        background-color: rgba(13, 110, 253, 0.2) !important;
        transform: scale(1.02);
    }
    
    .enquiry-sidebar .bg-warning.bg-opacity-10:hover {
        background-color: rgba(255, 193, 7, 0.2) !important;
        transform: scale(1.02);
    }
    
    .enquiry-sidebar .bg-success.bg-opacity-10:hover {
        background-color: rgba(25, 135, 84, 0.2) !important;
        transform: scale(1.02);
    }
    
    .enquiry-sidebar .bg-info.bg-opacity-10:hover {
        background-color: rgba(13, 202, 240, 0.2) !important;
        transform: scale(1.02);
    }
    
    .enquiry-sidebar .bg-purple.bg-opacity-10:hover {
        background-color: rgba(139, 92, 246, 0.2) !important;
        transform: scale(1.02);
    }
    
    /* Add pulse animation to budget */
    .enquiry-sidebar .text-purple.fs-5 {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }
</style>

<!-- jQuery (required for date range picker and AJAX) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Moment.js (required for date range picker) -->
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>

<!-- Date Range Picker CSS and JS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<!-- Bootstrap 5 JS (for dropdown functionality) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Guest Dropdown Functions - Define them globally first

    
    // Main Guest Selector - Uses the same modal pattern as attraction booking
    window.openMainGuestSelector = function() {
        console.log('openMainGuestSelector called');
        
        // Get current values from hidden inputs
        const male = parseInt(document.getElementById('male').value) || 0;
        const female = parseInt(document.getElementById('female').value) || 0;
        const children = parseInt(document.getElementById('children').value) || 0;
        const infants = parseInt(document.getElementById('infants').value) || 0;
        const adults = male + female;
        
        console.log('Current guest values:', {adults, male, female, children, infants});
        
        // Create modal if it doesn't exist
        if (!document.getElementById('mainGuestSelectorModal')) {
            createMainGuestSelectorModal();
        }
        
        // Set modal values
        document.getElementById('mainModalMale').textContent = male;
        document.getElementById('mainModalFemale').textContent = female;
        document.getElementById('mainModalChildren').textContent = children;
        document.getElementById('mainModalInfants').textContent = infants;
        
        // Show modal
        const modal = document.getElementById('mainGuestSelectorModal');
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();
    };
    
    function createMainGuestSelectorModal() {
        const modalHTML = `
            <div class="modal fade" id="mainGuestSelectorModal" tabindex="-1" aria-labelledby="mainGuestSelectorModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="mainGuestSelectorModalLabel">
                                <i class="ri-group-line me-2"></i>Select Tour Guests
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-4">
                                <!-- Adults Section -->
                                <div class="col-md-6">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i class="ri-user-line me-2"></i>Adults</h6>
                                        </div>
                                        <div class="card-body">
                                            <!-- Male -->
                                            <div class="guest-counter mb-3">
                                                <label class="form-label fw-semibold text-primary">
                                                    <i class="ri-user-3-line me-1"></i>Male
                                                </label>
                                                <div class="d-flex align-items-center">
                                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="updateMainGuest('male', -1)">
                                                        <i class="ri-subtract-line"></i>
                                                    </button>
                                                    <span class="mx-3 fw-bold fs-5" id="mainModalMale">0</span>
                                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="updateMainGuest('male', 1)">
                                                        <i class="ri-add-line"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <!-- Female -->
                                            <div class="guest-counter">
                                                <label class="form-label fw-semibold text-danger">
                                                    <i class="ri-user-4-line me-1"></i>Female
                                                </label>
                                                <div class="d-flex align-items-center">
                                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="updateMainGuest('female', -1)">
                                                        <i class="ri-subtract-line"></i>
                                                    </button>
                                                    <span class="mx-3 fw-bold fs-5" id="mainModalFemale">0</span>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="updateMainGuest('female', 1)">
                                                        <i class="ri-add-line"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Children & Infants Section -->
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0"><i class="ri-user-smile-line me-2"></i>Children & Infants</h6>
                                        </div>
                                        <div class="card-body">
                                            <!-- Children -->
                                            <div class="guest-counter mb-3">
                                                <label class="form-label fw-semibold text-success">
                                                    <i class="ri-user-smile-line me-1"></i>Children
                                                    <small class="text-muted">(Ages 1-17)</small>
                                                </label>
                                                <div class="d-flex align-items-center">
                                                    <button type="button" class="btn btn-outline-success btn-sm" onclick="updateMainGuest('children', -1)">
                                                        <i class="ri-subtract-line"></i>
                                                    </button>
                                                    <span class="mx-3 fw-bold fs-5" id="mainModalChildren">0</span>
                                                    <button type="button" class="btn btn-outline-success btn-sm" onclick="updateMainGuest('children', 1)">
                                                        <i class="ri-add-line"></i>
                                                    </button>
                                                </div>
                                                <!-- Child Ages Section -->
                                                <div id="childAgesSection" class="mt-3" style="display: none;">
                                                    <label class="form-label fw-semibold text-success mb-2">
                                                        <i class="ri-user-settings-line me-1"></i>Select Ages for Children
                                                    </label>
                                                    <div id="childAgeDropdowns" class="d-flex flex-column gap-2">
                                                        <!-- Child age dropdowns will be added here dynamically -->
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Infants -->
                                            <div class="guest-counter">
                                                <label class="form-label fw-semibold text-warning">
                                                    <i class="ri-user-heart-line me-1"></i>Infants
                                                    <small class="text-muted">(Under 1 year)</small>
                                                </label>
                                                <div class="d-flex align-items-center">
                                                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="updateMainGuest('infants', -1)">
                                                        <i class="ri-subtract-line"></i>
                                                    </button>
                                                    <span class="mx-3 fw-bold fs-5" id="mainModalInfants">0</span>
                                                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="updateMainGuest('infants', 1)">
                                                        <i class="ri-add-line"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" onclick="applyMainGuestSelection()">
                                <i class="ri-check-line me-1"></i>Apply Selection
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
    
    window.updateMainGuest = function(type, change) {
        const element = document.getElementById('mainModal' + type.charAt(0).toUpperCase() + type.slice(1));
        const currentValue = parseInt(element.textContent) || 0;
        let newValue = Math.max(0, currentValue + change);
        
        // For adults, ensure at least 1 adult is selected in total
        if ((type === 'male' || type === 'female') && change < 0) {
            const maleEl = document.getElementById('mainModalMale');
            const femaleEl = document.getElementById('mainModalFemale');
            const maleCount = maleEl ? parseInt(maleEl.textContent) || 0 : 0;
            const femaleCount = femaleEl ? parseInt(femaleEl.textContent) || 0 : 0;
            
            const totalAdults = (type === 'male' ? newValue : maleCount) + (type === 'female' ? newValue : femaleCount);
            
            if (totalAdults < 1) {
                return; // Don't allow reducing to 0 adults
            }
        }
        
        element.textContent = newValue;
        
        // Handle child age dropdowns
        if (type === 'children') {
            updateChildAgeDropdowns(newValue);
        }
    };
    
    // Function to create/update child age dropdowns
    function updateChildAgeDropdowns(childCount) {
        const childAgesSection = document.getElementById('childAgesSection');
        const childAgeDropdowns = document.getElementById('childAgeDropdowns');
        
        if (!childAgesSection || !childAgeDropdowns) return;
        
        // Show/hide the child ages section
        if (childCount > 0) {
            childAgesSection.style.display = 'block';
        } else {
            childAgesSection.style.display = 'none';
            childAgeDropdowns.innerHTML = '';
            return;
        }
        
        // Clear existing dropdowns
        childAgeDropdowns.innerHTML = '';
        
        // Create age options (1-17 years)
        const ageOptions = [];
        for (let i = 1; i <= 17; i++) {
            ageOptions.push(`<option value="${i}">${i} year${i > 1 ? 's' : ''}</option>`);
        }
        
        // Create dropdowns for each child
        for (let i = 1; i <= childCount; i++) {
            const dropdownHTML = `
                <div class="d-flex align-items-center mb-2">
                    <label class="me-2 text-success fw-semibold" style="min-width: 80px;">Child ${i}:</label>
                    <select class="form-select form-select-sm child-age-select" data-child-index="${i}">
                        <option value="">Select age</option>
                        ${ageOptions.join('')}
                    </select>
                </div>
            `;
            childAgeDropdowns.insertAdjacentHTML('beforeend', dropdownHTML);
        }
    }
    
    window.applyMainGuestSelection = function() {
        console.log('applyMainGuestSelection called');
        
        const male = parseInt(document.getElementById('mainModalMale').textContent) || 0;
        const female = parseInt(document.getElementById('mainModalFemale').textContent) || 0;
        const children = parseInt(document.getElementById('mainModalChildren').textContent) || 0;
        const infants = parseInt(document.getElementById('mainModalInfants').textContent) || 0;
        const adults = male + female;
        
        // Collect child ages
        const childAges = [];
        const childAgeSelects = document.querySelectorAll('.child-age-select');
        childAgeSelects.forEach(select => {
            if (select.value) {
                childAges.push(parseInt(select.value));
            }
        });
        
        console.log('Modal values:', {adults, male, female, children, infants, childAges});
        
        // Validate child ages if children are selected
        if (children > 0 && childAges.length !== children) {
            alert('Please select ages for all children before applying.');
            return;
        }
        
        // Update hidden inputs
        const adultsInput = document.getElementById('adults');
        const maleInput = document.getElementById('male');
        const femaleInput = document.getElementById('female');
        const childrenInput = document.getElementById('children');
        const infantsInput = document.getElementById('infants');
        const childAgesInput = document.getElementById('child_ages');
        
        if (adultsInput) adultsInput.value = adults;
        if (maleInput) maleInput.value = male;
        if (femaleInput) femaleInput.value = female;
        if (childrenInput) childrenInput.value = children;
        if (infantsInput) infantsInput.value = infants;
        if (childAgesInput) childAgesInput.value = JSON.stringify(childAges);
        
        // Update summary display with child ages
        const guestSummary = document.getElementById('mainGuestSummary');
        console.log('Guest summary element:', guestSummary);
        
        if (guestSummary) {
            let summaryText = `${adults} adults (${male} male, ${female} female), ${children} children`;
            if (children > 0 && childAges.length > 0) {
                summaryText += ` (ages: ${childAges.join(', ')})`;
            }
            summaryText += ` - ${infants} infants`;
            guestSummary.textContent = summaryText;
            console.log('Updated guest summary text');
        }
        
        // Update badges
        const badgeContainer = guestSummary.closest('.guest-display').querySelector('.guest-badges');
        if (badgeContainer) {
            const badges = badgeContainer.querySelectorAll('.badge');
            if (badges.length >= 3) {
                badges[0].textContent = adults; // Total adults
                badges[1].textContent = children; // Children
                badges[2].textContent = infants; // Infants
            }
        }
        
        // Refresh meal plans if a hotel is already selected
        const hotelSelect = document.getElementById('hotelSelect');
        if (hotelSelect && hotelSelect.value) {
            updateHotelDependentDropdowns(hotelSelect.value);
        }
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('mainGuestSelectorModal'));
        modal.hide();
        
        console.log('applyMainGuestSelection completed successfully');
    };

    // Ensure Bootstrap is properly loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Check if Bootstrap is properly loaded
        if (typeof bootstrap === 'undefined') {
            console.error('Bootstrap JS is not loaded properly!');
        } else {
            console.log('Bootstrap version:', bootstrap.Modal.VERSION);
        }
    });
</script>

<script>
// Function to save customer information
function saveCustomerInfo() {
    const customerData = {
        fullName: document.getElementById('customerFullName').value,
        email: document.getElementById('customerEmail').value,
        phone: document.getElementById('customerPhone').value,
        countryCode: document.getElementById('customerCountryCode').value,
        address1: document.getElementById('customerAddress1').value,
        address2: document.getElementById('customerAddress2').value,
        state: document.getElementById('customerState').value,
        zip: document.getElementById('customerZip').value,
        specialRequests: document.getElementById('customerSpecialRequests').value
    };

    // Validate required fields
    const requiredFields = ['fullName', 'email', 'phone', 'countryCode', 'address1'];
    const missingFields = requiredFields.filter(field => !customerData[field]);

    if (missingFields.length > 0) {
        alert('Please fill in all required fields: ' + missingFields.join(', '));
        return;
    }

    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(customerData.email)) {
        alert('Please enter a valid email address');
        return;
    }

    // Store the customer data in a hidden input for form submission
    const customerDataInput = document.getElementById('customerDataInput') || document.createElement('input');
    customerDataInput.type = 'hidden';
    customerDataInput.name = 'customer_data';
    customerDataInput.id = 'customerDataInput';
    customerDataInput.value = JSON.stringify(customerData);
    document.querySelector('form').appendChild(customerDataInput);

    // Show success message
    alert('Customer information saved successfully!');
}

// AJAX handler function
async function saveServiceOrder(type, data, agentId, tourId) {
    try {
        const response = await fetch('/api/save-service', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                agent_id: agentId,
                tour_id: tourId,
                type: type,
                data: data
            })
        });

        const result = await response.json();
        if (!result.success) {
            throw new Error(result.message || 'Failed to save service');
        }
        return result;
    } catch (error) {
        console.error('Error saving service:', error);
        throw error;
    }
}

// Booking Data Management Functions
function addHotelBooking(hotelData) {
    const customerData = getCustomerData();
    const bookingData = {
        ...hotelData,
        ...customerData,
        bookingType: 'booking'
    };

    const bookings = JSON.parse(document.getElementById('hotelBookings').value || '[]');
    bookings.push(bookingData);
    document.getElementById('hotelBookings').value = JSON.stringify(bookings);
    updateBookingsSummary();
}

function addGuideBooking(guideData) {
    const customerData = getCustomerData();
    const bookingData = {
        ...guideData,
        ...customerData,
        bookingType: 'booking'
    };

    const bookings = JSON.parse(document.getElementById('guideBookings').value || '[]');
    bookings.push(bookingData);
    document.getElementById('guideBookings').value = JSON.stringify(bookings);
    updateBookingsSummary();
}

function addVehicleBooking(vehicleData) {
    const customerData = getCustomerData();
    const bookingData = {
        ...vehicleData,
        ...customerData,
        bookingType: 'booking'
    };

    const bookings = JSON.parse(document.getElementById('vehicleBookings').value || '[]');
    bookings.push(bookingData);
    document.getElementById('vehicleBookings').value = JSON.stringify(bookings);
    updateBookingsSummary();
}

function addAttractionBooking(attractionData) {
    const customerData = getCustomerData();
    const bookingData = {
        ...attractionData,
        ...customerData,
        bookingType: 'booking'
    };

    const bookings = JSON.parse(document.getElementById('attractionBookings').value || '[]');
    bookings.push(bookingData);
    document.getElementById('attractionBookings').value = JSON.stringify(bookings);
    updateBookingsSummary();
}

// Function to update bookings summary
function updateBookingsSummary() {
    const hotelBookings = JSON.parse(document.getElementById('hotelBookings').value || '[]');
    const guideBookings = JSON.parse(document.getElementById('guideBookings').value || '[]');
    const vehicleBookings = JSON.parse(document.getElementById('vehicleBookings').value || '[]');
    const attractionBookings = JSON.parse(document.getElementById('attractionBookings').value || '[]');

    const summary = [];
    if (hotelBookings.length > 0) summary.push(`${hotelBookings.length} Hotel(s)`);
    if (guideBookings.length > 0) summary.push(`${guideBookings.length} Guide(s)`);
    if (vehicleBookings.length > 0) summary.push(`${vehicleBookings.length} Vehicle(s)`);
    if (attractionBookings.length > 0) summary.push(`${attractionBookings.length} Attraction(s)`);

    document.getElementById('bookingsSummary').textContent = summary.length > 0 ? 
        summary.join(', ') : 'No bookings added yet';
}

// Helper function to get customer data
function getCustomerData() {
    return {
        fullName: document.getElementById('customerFullName').value,
        email: document.getElementById('customerEmail').value,
        phone: document.getElementById('customerPhone').value,
        countryCode: document.getElementById('customerCountryCode').value,
        address1: document.getElementById('customerAddress1').value,
        address2: document.getElementById('customerAddress2').value,
        state: document.getElementById('customerState').value,
        zip: document.getElementById('customerZip').value,
        specialRequests: document.getElementById('customerSpecialRequests').value
    };
}

// Function to validate customer information
function validateCustomerInfo() {
    const requiredFields = ['customerFullName', 'customerEmail', 'customerPhone', 'customerCountryCode', 'customerAddress1'];
    const missingFields = requiredFields.filter(field => !document.getElementById(field).value);
    
    if (missingFields.length > 0) {
        alert('Please fill in all required customer information fields');
        return false;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(document.getElementById('customerEmail').value)) {
        alert('Please enter a valid email address');
        return false;
    }

    return true;
}

// Global variables (move outside DOMContentLoaded)
let tourStartDate = null;
let tourEndDate = null;
let selectedHotels = [];
let tourNights = 0;
let hotelData = [];

document.addEventListener('DOMContentLoaded', function() {

    // Country and City Cascade (following agent controller pattern)
    const userCountrySelect = document.getElementById('user_country');
    const citySelect = document.getElementById('city');
    const cityLoader = document.getElementById('cityLoader');

    userCountrySelect.addEventListener('change', function() {
        const selectedCountry = this.value;
        
        if (selectedCountry) {
            citySelect.disabled = true;
            cityLoader.style.display = 'block';
            citySelect.innerHTML = '<option value="">Loading cities...</option>';
            
                         // Use jQuery AJAX for country-city loading
            setTimeout(function() {
                $.ajax({
                    url: "{{ route('fetch-cities-by-country-single-tour') }}",
                    type: "GET",
                    data: { country: selectedCountry },
                    dataType: 'json',
                    success: function(response) {
                    citySelect.innerHTML = '<option value="">Select city...</option>';
                        
                    if (response.cities && response.cities.length > 0) {
                        // Set country_id for the hidden field (we'll use country name for now)
                        document.getElementById('country_id').value = selectedCountry;
                        
                        response.cities.forEach(function(city) {
                            citySelect.innerHTML += `<option value="${city.name}" data-id="${city.id}">${city.name}</option>`;
                        });
                    } else {
                        citySelect.innerHTML += '<option disabled>No cities found</option>';
                    }
                        
                        citySelect.disabled = false;
                        cityLoader.style.display = 'none';
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading cities:', error);
                        citySelect.innerHTML = '<option disabled>Error loading cities</option>';
                        citySelect.disabled = false;
                        cityLoader.style.display = 'none';
                    }
                });
            }, 300); // Small delay to ensure smooth UX
        } else {
            citySelect.innerHTML = '<option value="">Select country first</option>';
            citySelect.disabled = true;
        }
    });

    // City selection handler
    citySelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.dataset.id) {
            document.getElementById('city_id').value = selectedOption.dataset.id;
        }
        
        // Load hotels for the selected city
        const selectedCity = this.value;
        if (selectedCity) {
            loadHotelsForCity(selectedCity);
        } else {
            // Clear hotel selection if no city is selected
            const hotelSelect = document.getElementById('hotelSelect');
            const hotelLoadingStatus = document.getElementById('hotelLoadingStatus');
            hotelSelect.innerHTML = '<option value="">Select a city first to load hotels</option>';
            hotelLoadingStatus.innerHTML = '';
        }
    });

         // Wait for all dependencies to load
    $(document).ready(function() {
        // Date Range Picker Initialization - only if not locked
        const travelDatesField = $('#travel_dates');
        if (travelDatesField.attr('data-locked') !== 'true') {
            travelDatesField.daterangepicker({
                opens: 'left',
                autoUpdateInput: false,
                minDate: moment(),
                locale: {
                    format: 'MMM DD, YYYY',
                    cancelLabel: 'Clear'
                }
            });
        }

        // Only attach event handlers if field is not locked
        if (travelDatesField.attr('data-locked') !== 'true') {
            $('#travel_dates').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MMM DD') + ' - ' + picker.endDate.format('MMM DD, YYYY'));
                
                // Set hidden date fields
                document.getElementById('start_date').value = picker.startDate.format('YYYY-MM-DD');
                document.getElementById('end_date').value = picker.endDate.format('YYYY-MM-DD');
                
                // Update global variables
                tourStartDate = picker.startDate;
                tourEndDate = picker.endDate;
                tourNights = picker.endDate.diff(picker.startDate, 'days');
                
                // Update the hotel section date display
                document.getElementById('tourDates').textContent = picker.startDate.format('MMM DD') + ' - ' + picker.endDate.format('MMM DD, YYYY');
                document.getElementById('hotelNights').textContent = tourNights + ' Nights Selected';
                
                        // Generate night selection buttons
                generateNightSelection();
                // Initialize night display
                updateNightDisplay();
            });

            $('#travel_dates').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                document.getElementById('start_date').value = '';
                document.getElementById('end_date').value = '';
                tourStartDate = null;
                tourEndDate = null;
                tourNights = 0;
            });
        } else {
            // If field is locked with enquiry data, set the global variables
            @if($enquiry && $enquiry->check_in_time && $enquiry->check_out_time)
            tourStartDate = moment('{{ \Carbon\Carbon::parse($enquiry->check_in_time)->format('Y-m-d') }}');
            tourEndDate = moment('{{ \Carbon\Carbon::parse($enquiry->check_out_time)->format('Y-m-d') }}');
            tourNights = tourEndDate.diff(tourStartDate, 'days');
            
            // Update the hotel section date display
            if (document.getElementById('tourDates')) {
                document.getElementById('tourDates').textContent = '{{ \Carbon\Carbon::parse($enquiry->check_in_time)->format('M d') }} - {{ \Carbon\Carbon::parse($enquiry->check_out_time)->format('M d, Y') }}';
            }
            if (document.getElementById('hotelNights')) {
                document.getElementById('hotelNights').textContent = tourNights + ' Nights Selected';
            }
            
            // Generate night selection buttons for enquiry data
            generateNightSelection();
            updateNightDisplay();
            @endif
        }
    });

        // Update total adults count and hidden field
        const updateAdultsCount = () => {
            const maleInput = document.getElementById('male');
            const femaleInput = document.getElementById('female');
            const adultsInput = document.getElementById('adults');
            
            if (maleInput && femaleInput && adultsInput) {
                const maleCount = parseInt(maleInput.value) || 0;
                const femaleCount = parseInt(femaleInput.value) || 0;
                const totalAdults = maleCount + femaleCount;
                
                adultsInput.value = totalAdults;
            }
        };

        const updateGuestSummary = () => {
            const male = parseInt(document.getElementById('male').value) || 0;
            const female = parseInt(document.getElementById('female').value) || 0;
            const children = parseInt(document.getElementById('children').value) || 0;
            const infants = parseInt(document.getElementById('infants').value) || 0;
            
            // Calculate total adults
            const totalAdults = male + female;
            const adultsInput = document.getElementById('adults');
            if (adultsInput) {
                adultsInput.value = totalAdults;
            }
            
            // Main summary text for button
            const guestSummary = document.getElementById('guestSummary');
            if (guestSummary) {
            let summaryText = `${totalAdults} adults (${male} male, ${female} female) - ${children} children - ${infants} infants`;
                guestSummary.textContent = summaryText;
            }
        };

    // Prevent dropdown from closing when clicking inside
    document.addEventListener('click', function(event) {
        const dropdownMenu = event.target.closest('.dropdown-menu');
        if (dropdownMenu && dropdownMenu.getAttribute('aria-labelledby') === 'guestDropdown') {
            event.stopPropagation();
        }
    });

         // Generate night selection based on date range
    function generateNightSelection() {
         const nightSelectionDiv = document.getElementById('nightSelection');
         nightSelectionDiv.innerHTML = '';
         
         if (tourNights > 0) {
             for (let i = 1; i <= tourNights; i++) {
                 const startDate = moment(tourStartDate).add(i-1, 'days');
                 const endDate = moment(tourStartDate).add(i, 'days');
                 
                 const nightButton = document.createElement('button');
                 nightButton.type = 'button';
                 nightButton.className = 'btn btn-outline-primary btn-sm night-btn me-2 mb-2';
                 nightButton.dataset.night = i;
                 nightButton.style.minWidth = '120px';
                 nightButton.innerHTML = `
                     <div class="d-flex flex-column align-items-center">
                         <strong>Night ${i}</strong>
                         <small>${startDate.format('MMM DD')} - ${endDate.format('MMM DD')}</small>
                     </div>
                 `;
                 
                 nightButton.addEventListener('click', function() {
                     handleNightSelection(parseInt(this.dataset.night));
                 });
                 
                 nightSelectionDiv.appendChild(nightButton);
             }
         }
    }

     // Handle night selection with automatic consecutive filling
    function handleNightSelection(selectedNight) {
         const allNightButtons = document.querySelectorAll('.night-btn');
         let manuallySelectedNights = [];
         
         // Get currently manually selected nights (not auto-filled)
         allNightButtons.forEach(btn => {
             if (btn.classList.contains('manually-selected')) {
                 manuallySelectedNights.push(parseInt(btn.dataset.night));
             }
         });
         
         // Toggle the clicked night
         if (manuallySelectedNights.includes(selectedNight)) {
             // If clicking on manually selected night, remove it
             manuallySelectedNights = manuallySelectedNights.filter(night => night !== selectedNight);
         } else {
             // Add the new night to manually selected
             manuallySelectedNights.push(selectedNight);
         }
         
         // Fill gaps and update selection
         const allConsecutiveNights = fillConsecutiveNights(manuallySelectedNights);
         updateConsecutiveSelectionWithColors(manuallySelectedNights, allConsecutiveNights);
         updateNightDisplay();
    }

     // Fill gaps to make consecutive nights
    function fillConsecutiveNights(nights) {
         if (nights.length === 0) return nights;
         
         nights.sort((a, b) => a - b);
         const min = Math.min(...nights);
         const max = Math.max(...nights);
         
         const consecutiveNights = [];
         for (let i = min; i <= max; i++) {
             consecutiveNights.push(i);
         }
         
         return consecutiveNights;
    }

     // Update visual selection of nights with color coding
    function updateConsecutiveSelectionWithColors(manuallySelected, allConsecutive) {
         const allNightButtons = document.querySelectorAll('.night-btn');
         
         allNightButtons.forEach(btn => {
             const nightNumber = parseInt(btn.dataset.night);
             
             // Reset all classes
             btn.classList.remove('active', 'manually-selected', 'auto-selected', 'btn-success', 'btn-warning');
             btn.classList.add('btn-outline-primary');
             
             if (allConsecutive.includes(nightNumber)) {
                 btn.classList.add('active');
                 btn.classList.remove('btn-outline-primary');
                 
                 if (manuallySelected.includes(nightNumber)) {
                     // Manually selected nights - Green
                     btn.classList.add('manually-selected', 'btn-success');
                 } else {
                     // Auto-filled nights - Orange/Warning
                     btn.classList.add('auto-selected', 'btn-warning');
                 }
             }
         });
    }

     // Legacy function for backward compatibility
    function updateConsecutiveSelection(selectedNights) {
         updateConsecutiveSelectionWithColors(selectedNights, selectedNights);
    }

     // Update night display summary
    function updateNightDisplay() {
         const selectedNights = [];
         const manualNights = [];
         const autoNights = [];
         
         document.querySelectorAll('.night-btn.active').forEach(btn => {
             const nightNum = parseInt(btn.dataset.night);
             selectedNights.push(nightNum);
             
             if (btn.classList.contains('manually-selected')) {
                 manualNights.push(nightNum);
             } else if (btn.classList.contains('auto-selected')) {
                 autoNights.push(nightNum);
             }
         });
         
         selectedNights.sort((a, b) => a - b);
         manualNights.sort((a, b) => a - b);
         autoNights.sort((a, b) => a - b);
         
         if (selectedNights.length > 0) {
             const startNight = Math.min(...selectedNights);
             const endNight = Math.max(...selectedNights);
             const startDate = moment(tourStartDate).add(startNight-1, 'days');
             const endDate = moment(tourStartDate).add(endNight, 'days');
             
             let summaryHTML = `
                 <div class="alert alert-success">
                     <i class="ri-calendar-check-line me-2"></i>
                     <strong>Hotel booked for ${selectedNights.length} nights</strong><br>
                     <small>${startDate.format('MMM DD')} - ${endDate.format('MMM DD, YYYY')}</small><br>
                     <small class="text-muted">Consecutive hotel nights selected - applies to all rooms in this hotel</small>
             `;
             
             // Add legend if there are auto-selected nights
             if (autoNights.length > 0) {
                 summaryHTML += `
                     <hr class="my-2">
                     <div class="d-flex justify-content-between align-items-center">
                         <div>
                             <span class="badge bg-success me-2">${manualNights.length}</span>
                             <small>Manually Selected: ${manualNights.join(', ')}</small>
                         </div>
                         <div>
                             <span class="badge bg-warning me-2">${autoNights.length}</span>
                             <small>Auto-Required: ${autoNights.join(', ')}</small>
                         </div>
                     </div>
                 `;
             }
             
             summaryHTML += '</div>';
             document.getElementById('nightSelectionSummary').innerHTML = summaryHTML;
         } else {
             document.getElementById('nightSelectionSummary').innerHTML = `
                 <div class="alert alert-info">
                     <i class="ri-information-line me-2"></i>
                     <small>No nights selected. Click on the nights above to select hotel stay.</small>
                 </div>
             `;
         }
    }

    // Create Tour Package Function
    window.createTourPackage = function() {
        // Validation
        const country = document.getElementById('user_country').value;
        const city = document.getElementById('city').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const agent = document.getElementById('agent_id').value;
        
        if (!country || !city || !startDate || !endDate || !agent) {
            alert('Please fill in all required fields (Country, City, Travel Dates, Agent, and Guests) before creating the tour package.');
            return;
        }
        
        // Additional validation for guests
        const adults = parseInt(document.getElementById('adults').value) || 0;
        const male = parseInt(document.getElementById('male').value) || 0;
        const female = parseInt(document.getElementById('female').value) || 0;
        const children = parseInt(document.getElementById('children').value) || 0;
        const infants = parseInt(document.getElementById('infants').value) || 0;
        const childAgesData = document.getElementById('child_ages').value;
        
        if (adults < 1) {
            alert('At least 1 adult is required for the tour package.');
            return;
        }
        
        if ((male + female) !== adults) {
            alert('Total male and female count must equal total adults.');
            return;
        }
        
        // Validate child ages if children are selected
        if (children > 0) {
            try {
                const childAges = JSON.parse(childAgesData);
                if (childAges.length !== children) {
                    alert('Please select ages for all children in the guest selector.');
                    return;
                }
            } catch (e) {
                alert('Invalid child ages data. Please reselect children ages.');
                return;
            }
        }
        
        // Show loading state
        const createButton = event.target;
        const originalText = createButton.innerHTML;
        createButton.innerHTML = '<i class="ri-loader-2-line spin me-1"></i>Creating Tour Package...';
        createButton.disabled = true;
        
        // Prepare form data
        const formData = new FormData();
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        formData.append('user_country', country);
        formData.append('city', city);
        formData.append('start_date', startDate);
        formData.append('end_date', endDate);
        formData.append('adults', adults);
        formData.append('male', male);
        formData.append('female', female);
        formData.append('children', children);
        formData.append('infants', infants);
        formData.append('child_ages', childAgesData);
        formData.append('agent_id', agent);
        
        // Send AJAX request to create tour
        fetch('{{ route('single-tour-package.store') }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Tour creation response:', data); // Debug log
            if (data.success) {
                // Reset button state
                createButton.innerHTML = originalText;
                createButton.disabled = false;
                
                // Show success message
                showNotification(data.message + ' Tour ID: ' + data.display_id, 'success');
                
                // Set tour dates for hotel section
        tourStartDate = startDate;
        tourEndDate = endDate;
                
                // Store tour info globally
                window.currentTourId = data.tour_id;
                window.currentDisplayId = data.display_id;
        
        // Show hotel selection section
        document.getElementById('hotelSection').style.display = 'block';
        
        // Show transport section with day-wise itinerary
        document.getElementById('transportSection').style.display = 'block';
        
        // Generate daily services based on tour dates
        generateDailyServices();
        
        // Load zones for the newly created transport sections
        const selectedCity = document.getElementById('city').value;
        if (selectedCity) {
            fetchZonesForAllTransportSections(selectedCity);
        }
        
        // Scroll to hotel section
        document.getElementById('hotelSection').scrollIntoView({ 
            behavior: 'smooth' 
        });
        
        // Load hotels for the selected city
        loadHotelsForCity(city);
                
                // Disable the create button since tour is created
                createButton.innerHTML = '<i class="ri-check-line me-1"></i>Tour Created (' + data.display_id + ')';
                createButton.classList.remove('btn-primary');
                createButton.classList.add('btn-success');
                createButton.disabled = true;
                
                // Disable main configuration fields after successful tour creation
                disableMainConfigurationFields();
                
            } else {
                // Reset button state
                createButton.innerHTML = originalText;
                createButton.disabled = false;
                
                // Show error message
                showNotification(data.message || 'Failed to create tour package', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Reset button state
            createButton.innerHTML = originalText;
            createButton.disabled = false;
            
            // Show error message
            showNotification('Failed to create tour package. Please try again.', 'error');
        });
    };
    
    // Function to disable main configuration fields after tour creation
    function disableMainConfigurationFields() {
        console.log('Disabling main configuration fields after tour creation');
        
        // Disable country selection
        const countrySelect = document.getElementById('user_country');
        if (countrySelect) {
            countrySelect.disabled = true;
            countrySelect.style.backgroundColor = '#f8f9fa';
            countrySelect.style.cursor = 'not-allowed';
            countrySelect.style.opacity = '0.7';
        }
        
        // Disable city selection
        const citySelect = document.getElementById('city');
        if (citySelect) {
            citySelect.disabled = true;
            citySelect.style.backgroundColor = '#f8f9fa';
            citySelect.style.cursor = 'not-allowed';
            citySelect.style.opacity = '0.7';
        }
        
        // Disable travel dates
        const travelDates = document.getElementById('travel_dates');
        if (travelDates) {
            travelDates.disabled = true;
            travelDates.style.backgroundColor = '#f8f9fa';
            travelDates.style.cursor = 'not-allowed';
            travelDates.style.opacity = '0.7';
        }
        
        // Disable agent selection
        const agentSelect = document.getElementById('agent_id');
        if (agentSelect) {
            agentSelect.disabled = true;
            agentSelect.style.backgroundColor = '#f8f9fa';
            agentSelect.style.cursor = 'not-allowed';
            agentSelect.style.opacity = '0.7';
        }
        
        // Disable guest selector
        const guestEditButton = document.querySelector('.guest-display .btn-outline-primary');
        if (guestEditButton) {
            guestEditButton.disabled = true;
            guestEditButton.classList.remove('btn-outline-primary');
            guestEditButton.classList.add('btn-outline-secondary');
            guestEditButton.innerHTML = '<i class="ri-lock-line"></i>';
            guestEditButton.style.cursor = 'not-allowed';
            guestEditButton.style.opacity = '0.7';
            guestEditButton.onclick = null; // Remove click handler
        }
        
        // Add readonly styling to guest display
        const guestDisplay = document.querySelector('.guest-display');
        if (guestDisplay) {
            guestDisplay.classList.add('disabled');
        }
        
        // Add visual indicator
        addConfigurationLockedAlert();
        
        console.log('Main configuration fields disabled successfully');
    }
    
    // Function to add configuration locked alert
    function addConfigurationLockedAlert() {
        const mainFormCard = document.querySelector('.card-body');
        if (mainFormCard) {
            // Create lock indicator
            const lockAlert = document.createElement('div');
            lockAlert.className = 'alert alert-warning alert-dismissible fade show mt-3';
            lockAlert.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="ri-lock-line me-2 fs-4"></i>
                    <div>
                        <strong>Configuration Locked!</strong>
                        <br>
                        <small>The core tour configuration (Country, City, Dates, Guests, Agent) is now locked to maintain data integrity. You can still modify hotels, attractions, guides, and other services.</small>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Insert after the form row
            const formRow = mainFormCard.querySelector('.row');
            if (formRow) {
                formRow.parentNode.insertBefore(lockAlert, formRow.nextSibling);
            }
        }
    }

    // Store hotel data globally for reference (already declared globally)
    
    // Load hotels for city
    function loadHotelsForCity(cityName) {
        const hotelSelect = document.getElementById('hotelSelect');
        const hotelLoadingStatus = document.getElementById('hotelLoadingStatus');
        const roomTypeSelect = document.getElementById('roomTypeSelect');
        const bedTypeSelect = document.getElementById('bedTypeSelect');
        const mealPlanSelect = document.getElementById('mealPlanSelect');
        
        // Show loading state
        hotelSelect.innerHTML = '<option value="">Loading hotels in ' + cityName + '...</option>';
        hotelSelect.disabled = true;
        hotelLoadingStatus.innerHTML = '<i class="ri-loader-2-line spin me-1"></i>Loading comprehensive hotel data...';
        hotelLoadingStatus.style.color = '#0d6efd';
        
        // Clear dependent dropdowns
        roomTypeSelect.innerHTML = '<option value="">Select hotel first</option>';
        bedTypeSelect.innerHTML = '<option value="">Select hotel first</option>';
        mealPlanSelect.innerHTML = '<option value="">Select hotel first</option>';
        
        // Clear bed price display
        const bedPriceDisplay = document.getElementById('bedPriceDisplay');
        if (bedPriceDisplay) {
            bedPriceDisplay.style.display = 'none';
        }
        
        // Get current user's DMC ID from authentication
        const currentDmcId = '{{ auth()->user()->created_by }}';
        console.log('Current DMC ID:', currentDmcId);
        
        // Show DMC info in loading status
        hotelLoadingStatus.innerHTML = `<i class="ri-loader-2-line spin me-1"></i>Loading hotels for DMC ${currentDmcId} in ${cityName}...`;
        
        // Fetch hotels from API using DMC-specific endpoint
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
                
                // Handle response format
                if (response.success && response.hotels && response.hotels.length > 0) {
                    // Store hotel data globally
                    hotelData = response.hotels;
                    
                    response.hotels.forEach(hotel => {
                        const starInfo = hotel.hotel_star_rating ? ` (${hotel.hotel_star_rating}⭐)` : '';
                        hotelSelect.innerHTML += `<option value="${hotel.hotel_unique_id}">${hotel.name}${starInfo}</option>`;
                    });
                    
                    hotelLoadingStatus.innerHTML = `<i class="ri-check-line me-1 text-success"></i>${response.hotels.length} hotels found in ${cityName}`;
                    hotelLoadingStatus.style.color = '#198754';
                    console.log(`Loaded ${response.hotels.length} hotels for ${cityName}`);
                    
                    // Add change event listener to hotel select
                    hotelSelect.onchange = function() {
                        updateHotelDependentDropdowns(this.value);
                    };
                } 
                else {
                    hotelData = [];
                    hotelSelect.innerHTML = '<option value="">No hotels found in ' + cityName + '</option>';
                    hotelLoadingStatus.innerHTML = `<i class="ri-information-line me-1 text-warning"></i>No hotels found in ${cityName}`;
                    hotelLoadingStatus.style.color = '#fd7e14';
                    console.log(`No hotels found for ${cityName}`);
                }
            })
            .catch(error => {
                console.error('Error loading hotels:', error);
                hotelData = [];
                hotelSelect.innerHTML = '<option value="">Error loading hotels</option>';
                hotelSelect.disabled = true;
                hotelLoadingStatus.innerHTML = '<i class="ri-error-warning-line me-1 text-danger"></i>Error loading hotels';
                hotelLoadingStatus.style.color = '#dc3545';
            });
    }
    
    // Update hotel dependent dropdowns by fetching rooms
    function updateHotelDependentDropdowns(hotelId) {
        const roomTypeSelect = document.getElementById('roomTypeSelect');
        const bedTypeSelect = document.getElementById('bedTypeSelect');
        const mealPlanSelect = document.getElementById('mealPlanSelect');
        
        if (!hotelId) {
            // Clear dropdowns if no hotel selected
            if (roomTypeSelect) roomTypeSelect.innerHTML = '<option value="">Select hotel first</option>';
            if (bedTypeSelect) bedTypeSelect.innerHTML = '<option value="">Select hotel first</option>';
            if (mealPlanSelect) mealPlanSelect.innerHTML = '<option value="">Select hotel first</option>';
            return;
        }
        
        // Find selected hotel
        const selectedHotel = hotelData.find(h => h.hotel_unique_id == hotelId);
        if (!selectedHotel) {
            console.error('Selected hotel not found in hotel data');
            return;
        }
        
        console.log('Selected hotel data:', selectedHotel);
        
        // Get current user's DMC ID for room filtering
        const currentDmcId = '{{ auth()->user()->created_by }}';
        
        // Show loading state with DMC info
        if (roomTypeSelect) roomTypeSelect.innerHTML = '<option value="">Loading rooms for DMC...</option>';
        if (bedTypeSelect) bedTypeSelect.innerHTML = '<option value="">Loading rooms for DMC...</option>';
        if (mealPlanSelect) mealPlanSelect.innerHTML = '<option value="">Loading rooms for DMC...</option>';
        
        // Fetch rooms for the selected hotel with DMC filtering
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
                if (roomTypeSelect) roomTypeSelect.innerHTML = '<option value="">Select room type</option>';
                if (bedTypeSelect) bedTypeSelect.innerHTML = '<option value="">Select bed type</option>';
                if (mealPlanSelect) mealPlanSelect.innerHTML = '<option value="">Select meal plan</option>';
        
        // Clear bed price display
        const bedPriceDisplay = document.getElementById('bedPriceDisplay');
        if (bedPriceDisplay) {
            bedPriceDisplay.style.display = 'none';
        }
                
                if (response.success && response.rooms && response.rooms.length > 0) {
                    console.log('Room data structure:', response.rooms[0]); // Debug: log first room structure
                    console.log('Total rooms received:', response.rooms.length);
                    console.log('Current DMC ID for filtering:', currentDmcId);
                    
                    // Filter rooms by DMC ID using only created_by field
                    let dmcFilteredRooms = response.rooms.filter(room => {
                        // Only check created_by field for DMC filtering
                        const roomDmcId = room.created_by;
                        
                        if (roomDmcId) {
                            const isMatch = roomDmcId == currentDmcId;
                            console.log(`Room ${room.room_id}: created_by = ${roomDmcId}, Expected DMC = ${currentDmcId}, Match = ${isMatch}`);
                            return isMatch;
                        } else {
                            // If no created_by info, restrict access for security
                            console.warn(`Room ${room.room_id} has no created_by information - RESTRICTING ACCESS for security`);
                            return false; // Don't show rooms without created_by info
                        }
                    });
                    
                    console.log('Rooms after DMC filtering:', dmcFilteredRooms.length);
                    console.log('DMC filtered rooms:', dmcFilteredRooms);
                    
                    // Use filtered rooms - if no rooms match DMC, show none
                    const roomsToUse = dmcFilteredRooms;
                    
                    // Check if no rooms were found for this DMC
                    if (roomsToUse.length === 0) {
                        console.warn(`No rooms found for DMC ${currentDmcId} in hotel ${hotelId}`);
                        showNotification(`No rooms available for your DMC (ID: ${currentDmcId}) in this hotel. Please contact your administrator.`, 'warning');
                        
                        // Clear dropdowns and show no rooms message
                        if (roomTypeSelect) roomTypeSelect.innerHTML = '<option value="">No rooms available for your DMC</option>';
                        if (bedTypeSelect) bedTypeSelect.innerHTML = '<option value="">No rooms available for your DMC</option>';
                        if (mealPlanSelect) mealPlanSelect.innerHTML = '<option value="">No rooms available for your DMC</option>';
                        
                        // Update DMC filtering status
                        const dmcFilteringStatus = document.getElementById('dmcFilteringStatus');
                        const filteringResults = document.getElementById('filteringResults');
                        if (dmcFilteringStatus && filteringResults) {
                            dmcFilteringStatus.style.display = 'block';
                            filteringResults.textContent = 'No rooms available';
                        }
                        
                        return; // Exit early
                    }
                    
                    // Extract unique values from filtered rooms
                    const roomTypes = [...new Set(roomsToUse.map(room => room.room_type).filter(Boolean))];
                    console.log('Available room types after DMC filtering:', roomTypes);
                    
                    // Create meal plan options based on room types and meal availability
                    const mealPlans = new Set();
                    
                    // Get current guest count
                    const adults = parseInt(document.getElementById('adults').value) || 0;
                    const children = parseInt(document.getElementById('children').value) || 0;
                    const infants = parseInt(document.getElementById('infants').value) || 0;
                    const totalGuests = adults + children; // Infants usually don't count for meals
                    
                    // Group rooms by room type (star rating)
                    const roomsByType = {};
                    roomsToUse.forEach(room => {
                        if (!roomsByType[room.room_type]) {
                            roomsByType[room.room_type] = [];
                        }
                        roomsByType[room.room_type].push(room);
                    });
                    
                    // Filter rooms based on guest count if needed (example: 3 guests = show only 1* rooms)
                    let availableRoomTypes = Object.keys(roomsByType);
                    if (totalGuests >= 3) {
                        // For 3+ guests, filter to show only rooms that can accommodate them
                        availableRoomTypes = availableRoomTypes.filter(roomType => {
                            const rooms = roomsByType[roomType];
                            return rooms.some(room => {
                                // Check if room can accommodate the guests (you may need to adjust this logic)
                                const maxCapacity = room.max_occupancy || room.adult_count || 4; // fallback to 4
                                return maxCapacity >= totalGuests;
                            });
                        });
                        
                        // If guest count is exactly 3 and 1* rooms exist, prioritize them
                        if (totalGuests === 3 && availableRoomTypes.some(type => type.includes('1'))) {
                            availableRoomTypes = availableRoomTypes.filter(type => type.includes('1'));
                        }
                    }
                    
                    // Check what meals are available across all room types
                    const hasBreakfast = roomsToUse.some(room => room.breakfast);
                    const hasLunch = roomsToUse.some(room => room.lunch);
                    const hasDinner = roomsToUse.some(room => room.dinner);
                    
                    // Generate room quantity options based on guest count
                    // For 3 guests: show 1, 2, 3 rooms options
                    const maxRooms = Math.min(totalGuests, 3); // Max 3 rooms shown, or guest count if less
                    const minRooms = 1;
                    
                    for (let roomCount = minRooms; roomCount <= maxRooms; roomCount++) {
                        const roomText = roomCount === 1 ? `${roomCount} room` : `${roomCount} rooms`;
                        
                        // Add "Room Only" option first
                        mealPlans.add(`${roomText} only`);
                        
                        // Add specific meal options
                        if (hasBreakfast) {
                            mealPlans.add(`${roomText} with breakfast`);
                        }
                        if (hasLunch) {
                            mealPlans.add(`${roomText} with lunch`);
                        }
                        if (hasDinner) {
                            mealPlans.add(`${roomText} with dinner`);
                        }
                        
                        // Add combination meal options
                        if (hasBreakfast && hasLunch) {
                            mealPlans.add(`${roomText} with breakfast + lunch`);
                        }
                        if (hasBreakfast && hasDinner) {
                            mealPlans.add(`${roomText} with breakfast + dinner`);
                        }
                        if (hasLunch && hasDinner) {
                            mealPlans.add(`${roomText} with lunch + dinner`);
                        }
                        if (hasBreakfast && hasLunch && hasDinner) {
                            mealPlans.add(`${roomText} with all meals (breakfast + lunch + dinner)`);
                        }
                        
                        // Add abbreviated versions for common combinations
                        if (hasBreakfast) {
                            mealPlans.add(`${roomText} with bf`);
                        }
                    }
                    
                    // Populate room types with pricing information
                    roomTypes.forEach(roomType => {
                        if (roomTypeSelect) {
                            // Find a room of this type to get pricing information
                            const sampleRoom = roomsToUse.find(room => room.room_type === roomType);
                            
                            if (sampleRoom) {
                                // Get the appropriate price based on guest count
                                const adults = parseInt(document.getElementById('adults').value) || 0;
                                const children = parseInt(document.getElementById('children').value) || 0;
                                const totalGuests = adults + children;
                                
                                // Determine if single or double occupancy
                                const isSingleOccupancy = totalGuests <= 1;
                                
                                // For now, default to weekday pricing (you can add weekend logic later)
                                const isWeekend = false;
                                
                                let price = 0;
                                let priceText = '';
                                
                                if (isSingleOccupancy) {
                                    if (isWeekend) {
                                        price = parseFloat(sampleRoom.weekend_price) || 0;
                                        priceText = ` - Weekend: $${price}`;
                                    } else {
                                        price = parseFloat(sampleRoom.weekday_price) || 0;
                                        priceText = ` - Weekday: $${price}`;
                                    }
                                } else {
                                    if (isWeekend) {
                                        price = parseFloat(sampleRoom.double_weekend_price) || 0;
                                        priceText = ` - Double Weekend: $${price}`;
                                    } else {
                                        price = parseFloat(sampleRoom.double_weekday_price) || 0;
                                        priceText = ` - Double Weekday: $${price}`;
                                    }
                                }
                                
                                // Create option with price information
                                const option = document.createElement('option');
                                option.value = roomType;
                                option.textContent = `${roomType}${priceText}`;
                                
                                // Store pricing data in dataset
                                option.dataset.roomType = roomType;
                                option.dataset.weekdayPrice = sampleRoom.weekday_price || 0;
                                option.dataset.weekendPrice = sampleRoom.weekend_price || 0;
                                option.dataset.doubleWeekdayPrice = sampleRoom.double_weekday_price || 0;
                                option.dataset.doubleWeekendPrice = sampleRoom.double_weekend_price || 0;
                                option.dataset.roomId = sampleRoom.room_id;
                                
                                // Store meal prices in dataset
                                option.dataset.breakfastPrice = sampleRoom.breakfast_price || 0;
                                option.dataset.lunchPrice = sampleRoom.lunch_price || 0;
                                option.dataset.dinnerPrice = sampleRoom.dinner_price || 0;
                                option.dataset.breakfast = sampleRoom.breakfast || 0;
                                option.dataset.lunch = sampleRoom.lunch || 0;
                                option.dataset.dinner = sampleRoom.dinner || 0;
                                
                                roomTypeSelect.appendChild(option);
                                
                                console.log(`Added room type option: ${roomType} with price $${price} (${isSingleOccupancy ? 'Single' : 'Double'} ${isWeekend ? 'Weekend' : 'Weekday'})`);
                                console.log(`Meal prices: Breakfast: $${sampleRoom.breakfast_price || 0}, Lunch: $${sampleRoom.lunch_price || 0}, Dinner: $${sampleRoom.dinner_price || 0}`);
                                console.log(`Sample room data for ${roomType}:`, sampleRoom);
                                console.log(`Dataset stored for ${roomType}:`, {
                                    breakfastPrice: option.dataset.breakfastPrice,
                                    lunchPrice: option.dataset.lunchPrice,
                                    dinnerPrice: option.dataset.dinnerPrice
                                });
                            } else {
                                // Fallback if no sample room found
                            roomTypeSelect.innerHTML += `<option value="${roomType}">${roomType}</option>`;
                            }
                        }
                    });
                    
                    // Add event listener for room type selection
                    if (roomTypeSelect) {
                        roomTypeSelect.onchange = function() {
                            updateBedTypesForRoom(this.value);
                        };
                    }
                    
                    // Store room data globally for bed fetching (use filtered rooms)
                    window.roomData = roomsToUse;
                    
                    // Debug: Log the room data structure
                    console.log('=== ROOM DATA STORED ===');
                    console.log('Total rooms stored:', roomsToUse.length);
                    if (roomsToUse.length > 0) {
                        console.log('First room sample:', roomsToUse[0]);
                        console.log('Meal prices in first room:', {
                            breakfast_price: roomsToUse[0].breakfast_price,
                            lunch_price: roomsToUse[0].lunch_price,
                            dinner_price: roomsToUse[0].dinner_price
                        });
                    }
                    
                    // For bed types, we'll populate them when a room type is selected
                    if (bedTypeSelect) {
                        bedTypeSelect.innerHTML = '<option value="">Select room type first</option>';
                    }
                    
                    // Populate meal plans
                    [...mealPlans].forEach(mealPlan => {
                        if (mealPlanSelect) {
                            mealPlanSelect.innerHTML += `<option value="${mealPlan}">${mealPlan}</option>`;
                        }
                    });
                    
                    console.log(`Loaded ${roomsToUse.length} rooms for hotel ${hotelId} (filtered by DMC ${currentDmcId})`);
                    
                    // Show notification about DMC filtering
                    if (dmcFilteredRooms.length !== response.rooms.length) {
                        const filteredCount = dmcFilteredRooms.length;
                        const totalCount = response.rooms.length;
                        showNotification(`DMC Filtering (created_by): Showing ${filteredCount} rooms out of ${totalCount} total rooms for DMC ${currentDmcId}`, 'info');
                        
                        // Update DMC filtering status display
                        const dmcFilteringStatus = document.getElementById('dmcFilteringStatus');
                        const filteringResults = document.getElementById('filteringResults');
                        if (dmcFilteringStatus && filteringResults) {
                            dmcFilteringStatus.style.display = 'block';
                            filteringResults.textContent = `${filteredCount}/${totalCount} rooms shown`;
                        }
                    } else {
                        // Hide DMC filtering status if no filtering occurred
                        const dmcFilteringStatus = document.getElementById('dmcFilteringStatus');
                        if (dmcFilteringStatus) {
                            dmcFilteringStatus.style.display = 'none';
                        }
                    }
                } else {
                    console.log('No rooms found for hotel:', hotelId);
                    if (roomTypeSelect) roomTypeSelect.innerHTML = '<option value="">No rooms available</option>';
                    if (bedTypeSelect) bedTypeSelect.innerHTML = '<option value="">No rooms available</option>';
                    if (mealPlanSelect) mealPlanSelect.innerHTML = '<option value="">No rooms available</option>';
                }
            })
            .catch(error => {
                console.error('Error loading rooms:', error);
                if (roomTypeSelect) roomTypeSelect.innerHTML = '<option value="">Error loading rooms</option>';
                if (bedTypeSelect) bedTypeSelect.innerHTML = '<option value="">Error loading rooms</option>';
                if (mealPlanSelect) mealPlanSelect.innerHTML = '<option value="">Error loading rooms</option>';
            });
        
        // Display hotel information
        displayHotelInfo(selectedHotel);
    }
    
    // Display hotel information
    function displayHotelInfo(hotel) {
        const hotelLoadingStatus = document.getElementById('hotelLoadingStatus');
        
        if (hotelLoadingStatus) {
            let infoHTML = `<div class="mt-2 p-2 bg-light rounded">
                <strong>${hotel.name}</strong> - ${hotel.category || 'Standard'} Category<br>
                <small class="text-muted">
                    📍 ${hotel.address || hotel.city}<br>
                    🏨 ${hotel.total_rooms || 0} rooms available<br>
                    💰 From $${hotel.base_price || 0} per night<br>
                    ⭐ ${hotel.hotel_star_rating || 3} star rating
                </small>
            </div>`;
            
            hotelLoadingStatus.innerHTML = infoHTML;
        }
    }
    
    // Update bed types for selected room type
    function updateBedTypesForRoom(roomType) {
        const bedTypeSelect = document.getElementById('bedTypeSelect');
        const roomTypeSelect = document.getElementById('roomTypeSelect');
        
        if (!roomType || !window.roomData) {
            if (bedTypeSelect) bedTypeSelect.innerHTML = '<option value="">Select room type first</option>';
            return;
        }
        
        // Get the selected room type option to access pricing data
        const selectedRoomOption = roomTypeSelect.options[roomTypeSelect.selectedIndex];
        if (!selectedRoomOption) {
            console.error('No room type option selected');
            return;
        }
        
        console.log('Selected room type option:', selectedRoomOption);
        console.log('Room type dataset:', selectedRoomOption.dataset);
        
        // Find rooms of the selected type
        const selectedRooms = window.roomData.filter(room => room.room_type === roomType);
        
        if (selectedRooms.length === 0) {
            if (bedTypeSelect) bedTypeSelect.innerHTML = '<option value="">No rooms of this type</option>';
            return;
        }
        
        console.log('Selected rooms for bed types:', selectedRooms);
        
        // Clear dropdown and show loading
        if (bedTypeSelect) bedTypeSelect.innerHTML = '<option value="">Loading bed types...</option>';
        
        // Get the first room ID to fetch beds (since all rooms of same type should have similar bed options)
        const firstRoom = selectedRooms[0];
        const roomId = firstRoom.room_id;
        
        console.log('Fetching beds for room ID:', roomId);
        
        // Fetch beds from the beds table using the existing API endpoint
        fetch(`{{ route('fetch-beds-by-room') }}?room_id=${roomId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Beds API Response:', data);
                
                if (bedTypeSelect) bedTypeSelect.innerHTML = '<option value="">Select bed type</option>';
                
                if (data.success && data.beds && data.beds.length > 0) {
                    // Populate bed types from the beds table
                    data.beds.forEach(bed => {
                        if (bedTypeSelect) {
                            // Create descriptive bed type text based on beds table structure
                            let bedTypeText = bed.room_type || 'Standard Bed';
                            
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
                            option.value = bed.bed_id;
                            option.textContent = bedTypeText;
                            option.dataset.bedId = bed.bed_id;
                            option.dataset.roomId = bed.room_id;
                            option.dataset.maxOccupancy = bed.max_occupancy || '';
                            option.dataset.noOfRooms = bed.no_of_rooms || '';
                            option.dataset.extraBedPrice = bed.extra_bed_price || '';
                            option.dataset.babyCotPrice = bed.baby_cot_price || '';
                            
                            bedTypeSelect.appendChild(option);
                        }
                    });
                    
                    console.log(`Loaded ${data.beds.length} bed types for room type ${roomType} from beds table`);
                    
                    // Show bed price display
                    const bedPriceDisplay = document.getElementById('bedPriceDisplay');
                    if (bedPriceDisplay) {
                        bedPriceDisplay.style.display = 'block';
                    }
                    
                } else {
                    // No beds found in beds table, show fallback options
                    console.log('No beds found in beds table, showing fallback options');
                    
                    if (bedTypeSelect) bedTypeSelect.innerHTML = '<option value="">No bed types available</option>';
                    
                    // Hide bed price display
                    const bedPriceDisplay = document.getElementById('bedPriceDisplay');
                    if (bedPriceDisplay) {
                        bedPriceDisplay.style.display = 'none';
                    }
                }
                
                // Add event listener for bed type selection
                if (bedTypeSelect) {
                    bedTypeSelect.onchange = function() {
                        updatePricingForBed(this.value);
                    };
                }
            })
            .catch(error => {
                console.error('Error fetching beds:', error);
                if (bedTypeSelect) bedTypeSelect.innerHTML = '<option value="">Error loading bed types</option>';
                
                // Hide bed price display
                const bedPriceDisplay = document.getElementById('bedPriceDisplay');
                if (bedPriceDisplay) {
                    bedPriceDisplay.style.display = 'none';
                }
            });
    }

    // Update pricing when bed type is selected
    function updatePricingForBed(bedTypeValue) {
        const bedTypeSelect = document.getElementById('bedTypeSelect');
        const selectedOption = bedTypeSelect.options[bedTypeSelect.selectedIndex];
        const priceDisplay = document.getElementById('bedPriceDisplay');
        
        if (selectedOption && selectedOption.dataset.bedId) {
            console.log('Selected bed option:', selectedOption);
            console.log('Bed dataset:', selectedOption.dataset);
            
            // Get bed information from dataset
            const bedId = selectedOption.dataset.bedId;
            const roomId = selectedOption.dataset.roomId;
            const maxOccupancy = selectedOption.dataset.maxOccupancy;
            const noOfRooms = selectedOption.dataset.noOfRooms;
            const extraBedPrice = parseFloat(selectedOption.dataset.extraBedPrice) || 0;
            const babyCotPrice = parseFloat(selectedOption.dataset.babyCotPrice) || 0;
            
            // Create detailed price display
            let priceText = '';
            let totalPrice = 0;
            
            if (extraBedPrice > 0) {
                priceText += `Extra Bed: $${extraBedPrice.toFixed(2)}`;
                totalPrice += extraBedPrice;
            }
            
            if (babyCotPrice > 0) {
                if (priceText) priceText += ' | ';
                priceText += `Baby Cot: $${babyCotPrice.toFixed(2)}`;
                totalPrice += babyCotPrice;
            }
            
            if (totalPrice === 0) {
                priceText = 'No additional charges';
            }
            
            console.log(`Selected bed type: ${selectedOption.textContent}`);
            console.log(`Bed ID: ${bedId}, Room ID: ${roomId}`);
            console.log(`Max Occupancy: ${maxOccupancy}, Available Rooms: ${noOfRooms}`);
            console.log(`Total additional price: $${totalPrice.toFixed(2)}`);
            
            // Update price display
            if (priceDisplay) {
                priceDisplay.style.display = 'block';
                priceDisplay.innerHTML = `
                    <div class="small">
                        <div><strong>Bed ID:</strong> ${bedId}</div>
                        <div><strong>Room ID:</strong> ${roomId}</div>
                        <div><strong>Max Occupancy:</strong> ${maxOccupancy || 'N/A'}</div>
                        <div><strong>Available:</strong> ${noOfRooms || 'N/A'} rooms</div>
                        <div class="text-success"><strong>Additional Charges:</strong> ${priceText}</div>
                    </div>
                `;
            }
            
            // Store the selected bed information for later use
            window.selectedBedInfo = {
                bedId: bedId,
                roomId: roomId,
                maxOccupancy: maxOccupancy,
                noOfRooms: noOfRooms,
                extraBedPrice: extraBedPrice,
                babyCotPrice: babyCotPrice,
                totalPrice: totalPrice
            };
        } else {
            // Hide price display if no bed selected
            if (priceDisplay) {
                priceDisplay.style.display = 'none';
            }
            window.selectedBedInfo = null;
        }
    }

         // Add Hotel Function
    window.addHotel = function() {
         const hotelSelect = document.getElementById('hotelSelect');
         const roomType = document.getElementById('roomTypeSelect').value;
         const bedType = document.getElementById('bedTypeSelect').value;
         const mealPlan = document.getElementById('mealPlanSelect').value;
         // Extract room count from meal plan selection
         let numberOfRooms = 1; // default
         if (mealPlan) {
             const roomMatch = mealPlan.match(/(\d+)\s*rooms?/i);
             if (roomMatch) {
                 numberOfRooms = parseInt(roomMatch[1]);
             }
         }
         console.log(`Extracted room count from meal plan "${mealPlan}": ${numberOfRooms} rooms`);
         
         if (!hotelSelect.value) {
             showNotification('Please select a hotel first.', 'warning');
             return;
         }
         
         const selectedNights = document.querySelectorAll('.night-btn.active');
         if (selectedNights.length === 0) {
             showNotification('Please select at least one night for this hotel.', 'warning');
             return;
         }
         
         // Get selected night numbers and dates
         const nightNumbers = Array.from(selectedNights).map(btn => parseInt(btn.dataset.night));
         nightNumbers.sort((a, b) => a - b);
         
         const startNight = Math.min(...nightNumbers);
         const endNight = Math.max(...nightNumbers);
         const checkInDate = moment(tourStartDate).add(startNight-1, 'days');
         const checkOutDate = moment(tourStartDate).add(endNight, 'days');
         
         // Get bed information if available
         const bedInfo = window.selectedBedInfo || {};
         
         // Get price information from the selected room type
         const roomTypeSelect = document.getElementById('roomTypeSelect');
         let roomPrice = 0;
         let priceType = '';
         
         if (roomTypeSelect && roomTypeSelect.value === roomType) {
             const selectedOption = roomTypeSelect.options[roomTypeSelect.selectedIndex];
             if (selectedOption && selectedOption.dataset) {
                 // Get guest count to determine single vs double occupancy
                 const adults = parseInt(document.getElementById('adults').value) || 0;
                 const children = parseInt(document.getElementById('children').value) || 0;
                 const totalGuests = adults + children;
                 
                 // Determine if single or double occupancy
                 const isSingleOccupancy = totalGuests <= 1;
                 
                 // Determine if it's weekend based on the check-in date
                 const checkInDate = moment(tourStartDate).add(startNight-1, 'days');
                 const dayOfWeek = checkInDate.day(); // 0 = Sunday, 6 = Saturday
                 const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
                 
                 if (isSingleOccupancy) {
                     if (isWeekend) {
                         roomPrice = parseFloat(selectedOption.dataset.weekendPrice) || 0;
                         priceType = 'Single Weekend';
                     } else {
                         roomPrice = parseFloat(selectedOption.dataset.weekdayPrice) || 0;
                         priceType = 'Single Weekday';
                     }
                 } else {
                     if (isWeekend) {
                         roomPrice = parseFloat(selectedOption.dataset.doubleWeekendPrice) || 0;
                         priceType = 'Double Weekend';
                     } else {
                         roomPrice = parseFloat(selectedOption.dataset.doubleWeekdayPrice) || 0;
                         priceType = 'Double Weekday';
                     }
                 }
                 
                 console.log(`Hotel ${roomType} price: $${roomPrice} (${priceType})`);
             }
         }
         
         // Validate that we have a valid price
         if (roomPrice <= 0) {
             showNotification('Warning: Room price is $0.00. Please check room type selection.', 'warning');
             console.warn('Room price is 0 or invalid:', roomPrice);
         }
         
         // Get meal price information from the selected room type
         let mealPrices = {
             breakfast_price: 0,
             lunch_price: 0,
             dinner_price: 0
         };
         
         console.log('=== MEAL PRICE FETCHING DEBUG ===');
         console.log('Room type select element:', roomTypeSelect);
         console.log('Room type value:', roomType);
         console.log('Room type select value:', roomTypeSelect ? roomTypeSelect.value : 'N/A');
         
         if (roomTypeSelect && roomTypeSelect.value === roomType) {
             const selectedOption = roomTypeSelect.options[roomTypeSelect.selectedIndex];
             console.log('Selected option:', selectedOption);
             console.log('Selected option dataset:', selectedOption ? selectedOption.dataset : 'N/A');
             
             if (selectedOption && selectedOption.dataset) {
                                 // Get meal prices directly from the room type option dataset
                // If API doesn't return meal prices, use default values for testing
                mealPrices = {
                    breakfast_price: parseFloat(selectedOption.dataset.breakfastPrice) || 100, // Default $100
                    lunch_price: parseFloat(selectedOption.dataset.lunchPrice) || 200, // Default $200
                    dinner_price: parseFloat(selectedOption.dataset.dinnerPrice) || 300 // Default $300
                };
                 
                 console.log('Meal prices from room type option:', mealPrices);
                 console.log('Dataset values:', {
                     breakfastPrice: selectedOption.dataset.breakfastPrice,
                     lunchPrice: selectedOption.dataset.lunchPrice,
                     dinnerPrice: selectedOption.dataset.dinnerPrice
                 });
                 
                 // Also log the meal availability flags
                 console.log('Meal availability flags:', {
                     breakfast: selectedOption.dataset.breakfast,
                     lunch: selectedOption.dataset.lunch,
                     dinner: selectedOption.dataset.dinner
                 });
             } else {
                 console.warn('No selected option or dataset available');
             }
         } else {
             console.warn('Room type select not found or value mismatch');
         }
         
         const hotelData = {
             id: hotelSelect.value,
             name: hotelSelect.options[hotelSelect.selectedIndex].text,
             roomType: roomType || 'Standard',
             bedType: bedType || 'Standard',
             bedId: bedInfo.bedId || null,
             maxOccupancy: bedInfo.maxOccupancy || null,
             availableRooms: bedInfo.noOfRooms || null,
             extraBedPrice: bedInfo.extraBedPrice || 0,
             babyCotPrice: bedInfo.babyCotPrice || 0,
             price: roomPrice, // Store the correct room price
             priceType: priceType, // Store the price type for reference
             mealPlan: mealPlan || 'Not specified',
             mealPrices: mealPrices, // Store meal prices for calculation
             numberOfRooms: numberOfRooms,
             nights: nightNumbers,
             checkInDate: checkInDate.format('MMM DD'),
             checkOutDate: checkOutDate.format('MMM DD'),
             totalNights: nightNumbers.length
         };
         
         console.log('=== ADDING HOTEL ===');
         console.log('Hotel data being added:', hotelData);
         console.log('Stored price:', hotelData.price);
         console.log('Price type:', hotelData.priceType);
         console.log('Room type:', hotelData.roomType);
         console.log('Nights:', hotelData.totalNights);
         console.log('Rooms:', hotelData.numberOfRooms);
         console.log('Meal plan:', hotelData.mealPlan);
         console.log('Meal prices:', hotelData.mealPrices);
         
         selectedHotels.push(hotelData);
         displaySelectedHotels();
         
         // Update hotel_data JSON field
         updateHotelDataField();
         
         // Show success notification
         showNotification(`Hotel "${hotelData.name}" added successfully for ${hotelData.totalNights} nights!`, 'success');
         
         // Reset form
         hotelSelect.value = '';
         document.getElementById('roomTypeSelect').value = '';
         document.getElementById('bedTypeSelect').value = '';
         document.getElementById('mealPlanSelect').value = '';
         document.getElementById('numberOfRooms').value = '1';
         
         // Clear hotel loading status
         const hotelLoadingStatus = document.getElementById('hotelLoadingStatus');
         if (hotelLoadingStatus) {
             hotelLoadingStatus.innerHTML = '';
         }
         
         // Clear night selection
         updateConsecutiveSelection([]);
         updateNightDisplay();
         
                 // Show package details and submit sections if hotels are added
        if (selectedHotels.length > 0) {
            document.getElementById('packageDetailsSection').style.display = 'block';
            document.getElementById('submitSection').style.display = 'block';
            
            // Ensure transport section is visible (it should already be visible from createTourPackage)
            if (document.getElementById('transportSection').style.display !== 'block') {
                document.getElementById('transportSection').style.display = 'block';
                generateDailyServices();
                
                // Load zones for transport sections if city is selected
                const selectedCity = document.getElementById('city').value;
                if (selectedCity) {
                    fetchZonesForAllTransportSections(selectedCity);
                }
            }
        }

        
    };

         // Display selected hotels
    function displaySelectedHotels() {
         const container = document.getElementById('selectedHotels');
         
         if (selectedHotels.length === 0) {
             container.innerHTML = `
                 <div class="alert alert-info d-flex align-items-center">
                     <i class="ri-information-line me-2"></i>
                     <span>No hotels selected yet. Choose your hotels above.</span>
                 </div>
             `;
         } else {
             let hotelsHtml = '';
             selectedHotels.forEach((hotel, index) => {
                 hotelsHtml += `
                     <div class="card mb-3 border-success">
                         <div class="card-header bg-light">
                             <div class="d-flex justify-content-between align-items-center">
                                 <h6 class="mb-0 fw-bold text-success">
                                     <i class="ri-hotel-line me-2"></i>${hotel.name}
                                 </h6>
                                 <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeHotel(${index})">
                                     <i class="ri-delete-bin-line"></i> Remove
                                 </button>
                             </div>
                         </div>
                         <div class="card-body">
                             <div class="row">
                                 <div class="col-md-6">
                                     <div class="mb-2">
                                         <span class="badge bg-primary me-1">${hotel.numberOfRooms} Room(s)</span>
                                         <span class="badge bg-info me-1">${hotel.roomType}</span>
                                         <span class="badge bg-success me-1">${hotel.bedType}</span>
                                     </div>
                                     <small class="text-muted d-block">
                                         <i class="ri-restaurant-line me-1"></i>Meal Plan: ${hotel.mealPlan}
                                     </small>
                                 </div>
                                 <div class="col-md-6">
                                     <div class="alert alert-success mb-0 py-2">
                                         <div class="d-flex justify-content-between align-items-center">
                                             <div>
                                                 <strong>Hotel booked for ${hotel.totalNights} nights</strong><br>
                                                 <small>${hotel.checkInDate} - ${hotel.checkOutDate}, 2025</small><br>
                                                 <small class="text-muted">Consecutive hotel nights selected</small>
                                             </div>
                                             <div class="badge bg-warning text-dark fs-6">${hotel.totalNights} nights</div>
                                         </div>
                                     </div>
                                     
                                     <!-- Meal Costs Breakdown -->
                                     ${hotel.mealPlan && !hotel.mealPlan.includes('only') ? `
                                         <div class="mt-2 p-2 bg-light rounded">
                                             <small class="text-muted d-block mb-1">
                                                 <i class="ri-restaurant-line me-1"></i>Meal Costs Breakdown:
                                             </small>
                                             ${hotel.mealPrices ? `
                                                 <div class="small">
                                                     ${hotel.mealPlan.includes('breakfast') || hotel.mealPlan.includes('bf') ? `
                                                         <div class="d-flex justify-content-between">
                                                             <span>Breakfast:</span>
                                                             <span>$${hotel.mealPrices.breakfast_price || 0} × ${hotel.totalNights} nights</span>
                                                         </div>
                                                     ` : ''}
                                                     ${hotel.mealPlan.includes('lunch') ? `
                                                         <div class="d-flex justify-content-between">
                                                             <span>Lunch:</span>
                                                             <span>$${hotel.mealPrices.lunch_price || 0} × ${hotel.totalNights} nights</span>
                                                         </div>
                                                     ` : ''}
                                                     ${hotel.mealPlan.includes('dinner') ? `
                                                         <div class="d-flex justify-content-between">
                                                             <span>Dinner:</span>
                                                             <span>$${hotel.mealPrices.dinner_price || 0} × ${hotel.totalNights} nights</span>
                                                         </div>
                                                     ` : ''}
                                                 </div>
                                             ` : '<small class="text-muted">Meal prices not available</small>'}
                                         </div>
                                     ` : ''}
                                     
                                     <!-- Cost Summary -->
                                     <div class="mt-2 p-2 bg-info text-white rounded">
                                         <small class="d-block mb-1">
                                             <i class="ri-calculator-line me-1"></i>Cost Summary:
                                         </small>
                                         <div class="small">
                                             <div class="d-flex justify-content-between">
                                                 <span>Room Cost:</span>
                                                 <span>$${hotel.price || 0} × ${hotel.numberOfRooms} × ${hotel.totalNights} = $${(hotel.price || 0) * hotel.numberOfRooms * hotel.totalNights}</span>
                                             </div>
                                             ${hotel.mealPlan && !hotel.mealPlan.includes('only') ? `
                                                 <div class="d-flex justify-content-between">
                                                     <span>Meal Cost:</span>
                                                     <span>$${calculateMealCosts(hotel.mealPlan, hotel.totalNights, parseInt(document.getElementById('adults').value) || 0, parseInt(document.getElementById('children').value) || 0, hotel.mealPrices)}</span>
                                                 </div>
                                             ` : ''}
                                             <hr class="my-1">
                                             <div class="d-flex justify-content-between fw-bold">
                                                 <span>Total:</span>
                                                 <span>$${hotel.price * hotel.numberOfRooms * hotel.totalNights + calculateMealCosts(hotel.mealPlan, hotel.totalNights, parseInt(document.getElementById('adults').value) || 0, parseInt(document.getElementById('children').value) || 0, hotel.mealPrices)}</span>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>
                 `;
             });
             container.innerHTML = hotelsHtml;
         }
         
         // Update summary
         document.getElementById('totalHotels').textContent = selectedHotels.length;
         const totalNights = selectedHotels.reduce((sum, hotel) => sum + hotel.totalNights, 0);
         document.getElementById('totalNights').textContent = totalNights + ' Nights';
    }

         // Generate daily services based on tour dates
    function generateDailyServices() {
         const container = document.getElementById('dailyServicesContainer');
         
         // Ensure we have valid tour dates
         if (!tourStartDate || !tourEndDate) {
             console.log('Tour dates not set, cannot generate daily services');
             return;
         }
         
         const totalDays = moment(tourEndDate).diff(moment(tourStartDate), 'days') + 1;
         
         // Update day count
         document.getElementById('transportDayCount').textContent = totalDays + ' Days';
         
         let servicesHTML = '';
         
         for (let day = 1; day <= totalDays; day++) {
             const currentDate = moment(tourStartDate).add(day-1, 'days');
             const isFirstDay = day === 1;
             const isLastDay = day === totalDays;
             
                         servicesHTML += `
                <div class="daily-service-section border-bottom" id="day${day}">
                    <div class="day-header p-3 bg-gradient-primary text-white">
                         <div class="d-flex justify-content-between align-items-center">
                             <div>
                                 <h5 class="mb-1 fw-bold">
                                     <i class="ri-calendar-line me-2"></i>Day ${day}
                                 </h5>
                                 <p class="mb-0 opacity-75">
                                     ${currentDate.format('dddd, Do MMMM YYYY')}
                                 </p>
                             </div>
                             <div class="text-end">
                                 <span class="badge bg-light text-dark px-3 py-2">
                                     <i class="ri-time-line me-1"></i>Full Day Activities
                                 </span>
                             </div>
                         </div>
                     </div>
                     <div class="day-content p-4 bg-light">
             `;
             
            

             
            // Entry Port Services (Only on Day 1)
              if (day === 1) {
                                   servicesHTML += `
                      <div class="service-card mb-4">
                          <div class="service-header d-flex justify-content-between align-items-center mb-3 p-3 bg-white rounded-top border-bottom border-primary">
                              <div>
                                  <h6 class="text-primary mb-1 fw-bold">
                                      <i class="ri-ship-line me-2"></i>Port Transport Services
                                  </h6>
                                  <small class="text-muted">Configure entry and exit port transportation services</small>
                              </div>
                          </div>
                          
                          <div class="card border-primary shadow-sm">
                              <div class="card-header bg-primary text-white">
                                  <div class="d-flex align-items-center">
                                      <span class="service-icon me-3">
                                          <i class="ri-login-circle-line fs-4"></i>
                                      </span>
                                      <div>
                                          <h6 class="mb-0 fw-bold">Entry Port Services</h6>
                                          <small class="opacity-75">Arrival transportation</small>
                                      </div>
                                      <span class="badge bg-success ms-auto">
                                          <i class="ri-check-line me-1"></i>Active
                                      </span>
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
                                                 <select class="form-select pickup-zone-select border-2" name="day${day}_entry_pickup_zone_id" style="padding-left: 45px;">
                                                     <option value="">Select pickup zone</option>
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
                                                 <select class="form-select dropoff-zone-select border-2" name="day${day}_entry_dropoff_zone_id" disabled style="padding-left: 45px; padding-right: 45px;">
                                                     <option value="">Select pickup zone first</option>
                                                 </select>
                                                 <i class="ri-map-pin-fill position-absolute text-danger" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                 <button type="button" class="btn btn-sm position-absolute" style="right: 8px; top: 50%; transform: translateY(-50%); z-index: 5; border: none; background: none;" onclick="clearDropoffZone(${day}, 'entry')">
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
                                                 <select class="form-select border-2" name="day${day}_entry_pickup_time" style="padding-left: 45px;">
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
                                                 <input type="date" class="form-control border-2" name="day${day}_entry_pickup_date" value="${currentDate.format('YYYY-MM-DD')}" style="padding-left: 45px;">
                                                 <i class="ri-calendar-fill position-absolute text-primary" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                             </div>
                                         </div>
                                     </div>
                                     <div class="col-md-2">
                                         <button type="button" class="btn btn-primary w-100 py-2" onclick="searchVehicles(${day}, 'entry')" id="day${day}_entry_search_btn" disabled>
                                             <i class="ri-search-line me-2"></i>Search Vehicles
                                         </button>
                                     </div>
                                 </div>
                                 
                                 <!-- Vehicle Results Section (Hidden Initially) -->
                                 <div class="row mt-4" id="day${day}_entry_vehicle_results" style="display: none;">
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
                                                        name="day${day}_entry_vehicle_id" 
                                                        onchange="updateVehicleDetails(${day}, 'entry')">
                                                    <option value="">Choose vehicle</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Service Type</label>
                                                <select class="form-select service-type-select" 
                                                        name="day${day}_entry_service_type" 
                                                        onchange="updatePricing(${day}, 'entry')">
                                                    <option value="">Select service type</option>
                                                    <option value="Shared">Shared</option>
                                                    <option value="Private">Private</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <!-- Price Display for Entry Port -->
                                        <div class="col-12 mt-3">
                                            <div id="day${day}_entry_price_display" class="alert alert-success" style="display: none;">
                                                <div class="d-flex align-items-center">
                                                    <i class="ri-money-dollar-circle-line me-2 fs-4"></i>
                                                    <div>
                                                        <strong>Price Information</strong>
                                                        <div class="small">Select a vehicle and service type to see pricing</div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Hidden fields for entry port pricing -->
                                            <input type="hidden" name="day${day}_entry_base_price" id="day${day}_entry_base_price" value="0">
                                            <input type="hidden" name="day${day}_entry_total_price" id="day${day}_entry_total_price" value="0">
                                            <input type="hidden" name="day${day}_entry_service_type" id="day${day}_entry_service_type" value="">
                                            <input type="hidden" name="day${day}_entry_guest_count" id="day${day}_entry_guest_count" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Container for additional entry port vehicles -->
                        <div class="entry-ports-container"></div>
                        
                        <!-- Add More Button - Positioned below additional vehicles -->
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-light border" onclick="addMoreEntryPorts(${day})" style="color: #0c63e4; border-color: #b8daff !important; background-color: #f8f9fa;">
                                <i class="ri-add-line me-2"></i>Add More Vehicles
                            </button>
                        </div>
                     </div>
                 `;
             }
             
                           // Exit Port Services (Only on last day)
              if (day === totalDays) {
                                   servicesHTML += `
                      <div class="service-card mb-4">
                          <div class="service-header d-flex justify-content-between align-items-center mb-3 p-3 bg-white rounded-top border-bottom border-danger">
                              <div>
                                  <h6 class="text-danger mb-1 fw-bold">
                                      <i class="ri-ship-line me-2"></i>Port Transport Services
                                  </h6>
                                  <small class="text-muted">Configure entry and exit port transportation services</small>
                              </div>
                          </div>
                          
                          <div class="card border-danger shadow-sm">
                              <div class="card-header bg-danger text-white">
                                  <div class="d-flex align-items-center">
                                      <span class="service-icon me-3">
                                          <i class="ri-logout-circle-line fs-4"></i>
                                      </span>
                                      <div>
                                          <h6 class="mb-0 fw-bold">Exit Port Services</h6>
                                          <small class="opacity-75">Departure transportation</small>
                                      </div>
                                      <span class="badge bg-warning ms-auto">
                                          <i class="ri-plane-line me-1"></i>Departure
                                      </span>
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
                                                 <select class="form-select pickup-zone-select border-2" name="day${day}_exit_pickup_zone_id" style="padding-left: 45px;">
                                                     <option value="">Select pickup zone</option>
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
                                                 <select class="form-select dropoff-zone-select border-2" name="day${day}_exit_dropoff_zone_id" disabled style="padding-left: 45px; padding-right: 45px;">
                                                     <option value="">Select pickup zone first</option>
                                                 </select>
                                                 <i class="ri-map-pin-fill position-absolute text-danger" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                 <button type="button" class="btn btn-sm position-absolute" style="right: 8px; top: 50%; transform: translateY(-50%); z-index: 5; border: none; background: none;" onclick="clearDropoffZone(${day}, 'exit')">
                                                     <i class="ri-close-line text-muted"></i>
                                                 </button>
                                             </div>
                                         </div>
                                     </div>
                                     <div class="col-md-2">
                                         <div class="form-group">
                                             <label class="form-label fw-semibold text-muted mb-2">
                                                 <i class="ri-time-line text-warning me-2"></i>Exit Time
                                             </label>
                                             <div class="position-relative">
                                                 <select class="form-select border-2" name="day${day}_exit_time" style="padding-left: 45px;">
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
                                                 <i class="ri-calendar-line text-primary me-2"></i>Exit Date
                                             </label>
                                             <div class="position-relative">
                                                 <input type="date" class="form-control border-2" name="day${day}_exit_date" value="${currentDate.format('YYYY-MM-DD')}" style="padding-left: 45px;">
                                                 <i class="ri-calendar-fill position-absolute text-primary" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                             </div>
                                         </div>
                                     </div>
                                     <div class="col-md-2">
                                         <button type="button" class="btn btn-primary w-100 py-2" onclick="searchVehicles(${day}, 'exit')" id="day${day}_exit_search_btn" disabled>
                                             <i class="ri-search-line me-2"></i>Search Vehicles
                                         </button>
                                     </div>
                                 </div>
                                 
                                                                 <!-- Vehicle Results Section (Hidden Initially) -->
                                <div class="row mt-4" id="day${day}_exit_vehicle_results" style="display: none;">
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
                                                        name="day${day}_exit_vehicle_id" 
                                                        onchange="updateVehicleDetails(${day}, 'exit')">
                                                    <option value="">Choose vehicle</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Service Type</label>
                                                <select class="form-select service-type-select" 
                                                        name="day${day}_exit_service_type" 
                                                        onchange="updatePricing(${day}, 'exit')">
                                                    <option value="">Select service type</option>
                                                    <option value="Shared">Shared</option>
                                                    <option value="Private">Private</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <!-- Price Display for Exit Port -->
                                        <div class="col-12 mt-3">
                                            <div id="day${day}_exit_price_display" class="alert alert-success" style="display: none;">
                                                <div class="d-flex align-items-center">
                                                    <i class="ri-money-dollar-circle-line me-2 fs-4"></i>
                                                    <div>
                                                        <strong>Price Information</strong>
                                                        <div class="small">Select a vehicle and service type to see pricing</div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Hidden fields for exit port pricing -->
                                            <input type="hidden" name="day${day}_exit_base_price" id="day${day}_exit_base_price" value="0">
                                            <input type="hidden" name="day${day}_exit_total_price" id="day${day}_exit_total_price" value="0">
                                            <input type="hidden" name="day${day}_exit_service_type" id="day${day}_exit_service_type" value="">
                                            <input type="hidden" name="day${day}_exit_guest_count" id="day${day}_exit_guest_count" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Container for additional exit port vehicles -->
                        <div class="exit-ports-container"></div>
                        
                        <!-- Add More Button - Positioned below additional vehicles -->
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-light border" onclick="addMoreExitPorts(${day})" style="color: #dc3545; border-color: #f5c2c7 !important; background-color: #f8f9fa;">
                                <i class="ri-add-line me-2"></i>Add More Vehicles
                            </button>
                        </div>
                     </div>
                 `;
             }
             
                           // Other Services (All days)
              servicesHTML += `
                  <div class="services-container">
                      <!-- Attraction Tickets -->
                      <div class="service-card mb-4">
                          <div class="service-header d-flex justify-content-between align-items-center mb-3 p-3 bg-white rounded-top border-bottom border-danger">
                              <div>
                                  <h6 class="text-danger mb-1 fw-bold">
                                      <i class="ri-ticket-line me-2"></i>Book Attraction Tickets
                                  </h6>
                                  <small class="text-muted">Select attractions and configure your perfect tour package</small>
                              </div>
                              <button type="button" class="btn btn-sm btn-outline-danger" onclick="updateAllAttractionPricing()" title="Refresh All Attraction Pricing">
                                  <i class="ri-refresh-line me-1"></i>Refresh Pricing
                              </button>
                          </div>
                          
                          <div class="attractions-container" id="day${day}_attractions_container">
                              <div class="card border-danger shadow-sm attraction-item mb-3" data-attraction-index="1">
                              <div class="card-header bg-danger text-white">
                                  <div class="d-flex align-items-center">
                                      <span class="service-icon me-3">
                                          <i class="ri-ticket-line fs-4"></i>
                                      </span>
                                      <div>
                                          <h6 class="mb-0 fw-bold">Attraction Booking #1</h6>
                                          <small class="opacity-75">Select your preferred attractions</small>
                                      </div>
                                      <span class="badge bg-warning ms-auto">
                                          <i class="ri-edit-line me-1"></i>Configure
                                      </span>
                                  </div>
                              </div>
                              <div class="card-body bg-white">
                                 
                                 <div class="row g-3">
                                     <div class="col-md-3">
                                         <label class="form-label fw-semibold">Select Attraction</label>
                                         <select class="form-select attraction-select" name="day${day}_attraction_1" id="day${day}_attraction_1" onchange="loadAttractionDetails(${day}, this.value, 1)">
                                             <option value="">Search Attraction</option>
                                         </select>
                                     </div>
                                                                         <div class="col-md-3">
                                        <label class="form-label fw-semibold">Select Guests</label>
                                        <div class="guest-selector">
                                            <div class="guest-display p-2 border rounded bg-light">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="guest-info">
                                                        <span id="day${day}_attraction_1_guest_summary" class="text-muted small">
                                                            1 adults (1 male, 0 female), 0 children -0 infants
                                                        </span>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="openGuestSelector('day${day}_attraction_1')">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                </div>
                                                <div class="guest-badges mt-1">
                                                    <span class="badge bg-primary">4</span>
                                                    <span class="badge bg-success">0</span>
                                                    <span class="badge bg-warning text-dark">0</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                     <div class="col-md-3">
                                         <label class="form-label fw-semibold">Select Time Slot</label>
                                         <select class="form-select" name="day${day}_attraction_1_time" id="day${day}_attraction_1_time">
                                             <option value="">Select Time Slot</option>
                                         </select>
                                     </div>
                                     <div class="col-md-3">
                                         <label class="form-label fw-semibold">Select Ticket</label>
                                         <select class="form-select" name="day${day}_attraction_1_ticket" id="day${day}_attraction_1_ticket" onchange="updateAttractionPricing(${day}, 1)">
                                             <option value="">Select Ticket</option>
                                         </select>
                                     </div>
                                 </div>
                                 
                                 <!-- Attraction Price Display -->
                                 <div class="col-12 mt-3">
                                     <div id="day${day}_attraction_1_price_display" class="alert alert-info" style="display: none;">
                                         <div class="d-flex align-items-center justify-content-between">
                                             <div class="d-flex align-items-center">
                                                 <i class="ri-money-dollar-circle-line me-2 fs-4"></i>
                                                 <div>
                                                     <strong>Attraction Pricing</strong>
                                                     <div class="small">Select an attraction and configure guests to see pricing</div>
                                                 </div>
                                             </div>
                                             <button type="button" class="btn btn-sm btn-outline-primary" onclick="forceUpdateAttractionPricing(${day}, 1)" title="Refresh Pricing">
                                                 <i class="ri-refresh-line"></i>
                                             </button>
                                         </div>
                                     </div>
                                 </div>
                                 
                                 </div>
                              </div>
                          </div>
                          
                                                   <div class="mt-3 text-center">
                                         <button type="button" class="btn btn-sm btn-outline-danger" onclick="addMoreAttractions(${day})">
                                             <i class="ri-add-line me-1"></i>Add More Attraction
                                         </button>
                            </div>
                         </div>
                     </div>
                     
                                           <!-- Tour Guide Services -->
                      <div class="service-card mb-4">
                          <div class="service-header d-flex justify-content-between align-items-center mb-3 p-3 bg-white rounded-top border-bottom border-info">
                              <div>
                                  <h6 class="text-info mb-1 fw-bold">
                                      <i class="ri-user-star-line me-2"></i>Book Tour Guide Services
                                  </h6>
                                  <small class="text-muted">Select professional guides and configure your tour package</small>
                              </div>
                          </div>
                          
                          <div class="guides-container" id="day${day}_guides_container">
                              <div class="card border-info shadow-sm guide-item mb-3" data-guide-index="1">
                              <div class="card-header bg-info text-white">
                                  <div class="d-flex align-items-center">
                                      <span class="service-icon me-3">
                                          <i class="ri-user-star-line fs-4"></i>
                                      </span>
                                      <div>
                                          <h6 class="mb-0 fw-bold">Tour Guide Booking #1</h6>
                                          <small class="opacity-75">Professional guide services</small>
                                      </div>
                                      <span class="badge bg-warning ms-auto">
                                          <i class="ri-edit-line me-1"></i>Configure
                                      </span>
                                  </div>
                              </div>
                              <div class="card-body bg-white">
                                 
                                 <div class="row g-3">
                                     <div class="col-md-3">
                                         <label class="form-label fw-semibold">Select Guide</label>
                                         <select class="form-select guide-select" name="day${day}_guide_1" id="day${day}_guide_1" onchange="loadGuideDetails(${day}, this.value, 1)">
                                             <option value="">Search Guide</option>
                                         </select>
                                     </div>
                                     <div class="col-md-3">
                                         <label class="form-label fw-semibold">Select Guests</label>
                                         <div class="guest-selector">
                                             <div class="guest-display p-2 border rounded bg-light">
                                                 <div class="d-flex align-items-center justify-content-between">
                                                     <div class="guest-info">
                                                         <span id="day${day}_guide_1_guest_summary" class="text-muted small">
                                                             1 adults (1 male, 0 female), 0 children -0 infants
                                                         </span>
                                                     </div>
                                                     <button type="button" class="btn btn-sm btn-outline-primary" onclick="openGuestSelector('day${day}_guide_1')">
                                                         <i class="ri-edit-line"></i>
                                                     </button>
                                                 </div>
                                                 <div class="guest-badges mt-1">
                                                     <span class="badge bg-primary">4</span>
                                                     <span class="badge bg-success">0</span>
                                                     <span class="badge bg-warning text-dark">0</span>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                     <div class="col-md-3">
                                         <label class="form-label fw-semibold">Pickup Time</label>
                                         <div class="dropdown">
                                             <button class="form-control dropdown-toggle text-start" type="button" id="day${day}_guide_1_pickup_time_btn" data-bs-toggle="dropdown" aria-expanded="false">
                                                 Select Pick-up Time
                                             </button>
                                             <div class="dropdown-menu p-3 pickup-time-dropdown" id="day${day}_guide_1_pickup_time_dropdown" style="width: 300px; max-height: 400px; overflow-y: auto;">
                                                 <h6 class="dropdown-header">Select Pick-up Time</h6>
                                                 <div id="day${day}_guide_1_pickup_time_options">
                                                     <p class="text-muted text-center p-3">Please select a guide first</p>
                                                 </div>
                                             </div>
                                             <input type="hidden" name="day${day}_guide_1_pickup_time" id="day${day}_guide_1_pickup_time">
                                         </div>
                                     </div>
                                     <div class="col-md-3">
                                         <label class="form-label fw-semibold">Select Package</label>
                                         <select class="form-select" name="day${day}_guide_1_package" id="day${day}_guide_1_package">
                                             <option value="">Select Duration</option>
                                         </select>
                                     </div>
                                 </div>
                                 
                                 </div>
                              </div>
                          </div>
                          
                                                   <div class="mt-3 text-center">
                                         <button type="button" class="btn btn-sm btn-outline-info" onclick="addMoreGuides(${day})">
                                             <i class="ri-add-line me-1"></i>Add More Guide
                                         </button>
                            </div>
                         </div>
                     </div>
                     
                     <!-- Restaurant Services -->
                     <div class="mb-4">
                         <div class="service-header d-flex justify-content-between align-items-center mb-3 p-3 bg-white rounded-top border-bottom border-success">
                             <div>
                                 <h6 class="text-success mb-1 fw-bold">
                                 <i class="ri-restaurant-line me-2"></i>Book Restaurant Services
                             </h6>
                                 <small class="text-muted">Select restaurants and configure your dining experience</small>
                             </div>
                         </div>
                         
                         <div class="restaurants-container" id="day${day}_restaurants_container">
                             <div class="card border-success shadow-sm restaurant-item mb-3" data-restaurant-index="1">
                                 <div class="card-header bg-success text-white">
                                     <div class="d-flex align-items-center">
                                         <span class="service-icon me-3">
                                             <i class="ri-restaurant-line fs-4"></i>
                                         </span>
                                         <div>
                                             <h6 class="mb-0 fw-bold">Restaurant Booking #1</h6>
                                             <small class="opacity-75">Select your dining experience</small>
                                 </div>
                                         <span class="badge bg-warning ms-auto">
                                             <i class="ri-edit-line me-1"></i>Configure
                                         </span>
                                     </div>
                                 </div>
                                 <div class="card-body bg-white">
                                 
                                 <div class="row g-3">
                                     <div class="col-md-3">
                                         <label class="form-label fw-semibold">Select Restaurant</label>
                                         <select class="form-select restaurant-select" name="day${day}_restaurant_1" id="day${day}_restaurant_1" onchange="loadRestaurantDetails(${day}, this.value, 1)">
                                             <option value="">Search Restaurant</option>
                                         </select>
                                     </div>
                                     <div class="col-md-3">
                                         <label class="form-label fw-semibold">Select Guests</label>
                                         <div class="guest-selector">
                                             <div class="guest-display p-2 border rounded bg-light">
                                                 <div class="d-flex align-items-center justify-content-between">
                                                     <div class="guest-info">
                                                         <span id="day${day}_restaurant_1_guest_summary" class="text-muted small">
                                                             1 adults (1 male, 0 female), 0 children -0 infants
                                                         </span>
                                                     </div>
                                                     <button type="button" class="btn btn-sm btn-outline-primary" onclick="openGuestSelector('day${day}_restaurant_1')">
                                                         <i class="ri-edit-line"></i>
                                                     </button>
                                                 </div>
                                                 <div class="guest-badges mt-1">
                                                     <span class="badge bg-primary">4</span>
                                                     <span class="badge bg-success">0</span>
                                                     <span class="badge bg-warning text-dark">0</span>
                                                 </div>
                                             </div>
                                         </div>
                                         
                                         <!-- Hidden fields for restaurant pricing -->
                                         <input type="hidden" name="day${day}_restaurant_1_total_price" id="day${day}_restaurant_1_total_price" value="0">
                                         <input type="hidden" name="day${day}_restaurant_1_meal_id" id="day${day}_restaurant_1_meal_id" value="">
                                         <input type="hidden" name="day${day}_restaurant_1_dish_name" id="day${day}_restaurant_1_dish_name" value="">
                                     </div>
                                     <div class="col-md-2">
                                         <label class="form-label fw-semibold">Meal Type</label>
                                         <select class="form-select" name="day${day}_meal_type_1" id="day${day}_meal_type_1">
                                             <option value="">Select Meal Type</option>
                                         </select>
                                         <small class="text-muted">Available meal types with timings</small>
                                     </div>
                                     <div class="col-md-2">
                                         <label class="form-label fw-semibold">Select Dish</label>
                                         <select class="form-select" name="day${day}_dish_1" id="day${day}_dish_1">
                                             <option value="">Select Dish</option>
                                         </select>
                                         <small class="text-muted">Buffet or Set Menu options</small>
                                     </div>
                                     <div class="col-md-2">
                                         <label class="form-label fw-semibold">Time Slot</label>
                                         <select class="form-select" name="day${day}_time_slot_1" id="day${day}_time_slot_1">
                                             <option value="">Select Time Slot</option>
                                         </select>
                                         <small class="text-muted">Available time slots</small>
                                     </div>
                                 </div>
                                 
                                 </div>
                                     </div>
                                 </div>
                                 
                                                                 <div class="mt-3 text-center">
                             <button type="button" class="btn btn-sm btn-outline-success" onclick="addMoreRestaurants(${day})">
                                             <i class="ri-add-line me-1"></i>Add More Restaurant
                                         </button>
                            </div>
                         </div>
                     </div>
                     
                     <!-- Transport Services -->
                     <div class="service-card mb-4">
                         <div class="service-header d-flex justify-content-between align-items-center mb-3 p-3 bg-white rounded-top border-bottom border-warning">
                             <div>
                                 <h6 class="text-warning mb-1 fw-bold">
                                     <i class="ri-car-line me-2"></i>Book Transport Services
                                 </h6>
                                 <small class="text-muted">Select professional transport and configure your tour package</small>
                             </div>
                         </div>
                         
                         <div class="transports-container" id="day${day}_transports_container">
                            <div class="card border-warning shadow-sm transport-item mb-3" data-transport-index="1">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-danger">Point To Point</span>
                                        <span class="badge bg-primary">Hourly</span>
                                    </div>
                                    <div class="d-flex gap-2">
                                         <input type="date" class="form-control" value="${currentDate.format('YYYY-MM-DD')}" name="day${day}_transport_date">
                                     </div>
                                 </div>
                                 
                                 <div class="row g-4 align-items-end">
                                     <div class="col-md-3">
                                         <div class="form-group">
                                             <label class="form-label fw-semibold text-muted mb-2">
                                                 <i class="ri-map-pin-line text-success me-2"></i>Pick Up Location
                                             </label>
                                             <div class="position-relative">
                                                 <select class="form-select pickup-zone-select border-2" name="day${day}_transport_pickup_zone_id" style="padding-left: 45px;">
                                                     <option value="">Select pickup zone</option>
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
                                                 <select class="form-select dropoff-zone-select border-2" name="day${day}_transport_dropoff_zone_id" disabled style="padding-left: 45px; padding-right: 45px;">
                                                     <option value="">Select pickup zone first</option>
                                                 </select>
                                                 <i class="ri-map-pin-fill position-absolute text-danger" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                                 <button type="button" class="btn btn-sm position-absolute" style="right: 8px; top: 50%; transform: translateY(-50%); z-index: 5; border: none; background: none;" onclick="clearDropoffZone(${day}, 'transport')">
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
                                                 <select class="form-select border-2" name="day${day}_transport_pickup_time" style="padding-left: 45px;">
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
                                                <i class="ri-calendar-line text-primary me-2"></i>Transport Date
                                            </label>
                                            <div class="position-relative">
                                                <input type="date" class="form-control border-2" name="day${day}_transport_date" value="${currentDate.format('YYYY-MM-DD')}" style="padding-left: 45px;">
                                                <i class="ri-calendar-fill position-absolute text-primary" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                            </div>
                                        </div>
                                     </div>
                                     <div class="col-md-2">
                                         <button type="button" class="btn btn-primary w-100 py-2" onclick="searchVehicles(${day}, 'transport')" id="day${day}_transport_search_btn" disabled>
                                             <i class="ri-search-line me-2"></i>Search Vehicles
                                         </button>
                                     </div>
                                 </div>
                                 
                                 <!-- Vehicle Results Section (Hidden Initially) -->
                                 <div class="row mt-4" id="day${day}_transport_vehicle_results" style="display: none;">
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
                                                        name="day${day}_transport_vehicle_id" 
                                                        onchange="updateVehicleDetails(${day}, 'transport')">
                                                    <option value="">Choose vehicle</option>
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Service Type</label>
                                                <select class="form-select" 
                                                        name="day${day}_transport_service_type" 
                                                        onchange="updatePricing(${day}, 'transport')">
                                                    <option value="">Select service type</option>
                                                    <option value="Shared">Shared</option>
                                                    <option value="Private">Private</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <!-- Price Display for Transport -->
                                        <div class="col-12 mt-3">
                                            <div id="day${day}_transport_price_display" class="alert alert-success" style="display: none;">
                                                <div class="d-flex align-items-center">
                                                    <i class="ri-money-dollar-circle-line me-2 fs-4"></i>
                                                    <div>
                                                        <strong>Price Information</strong>
                                                        <div class="small">Select a vehicle and service type to see pricing</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Add More Vehicles button -->
                                    <div class="col-12 mt-3">
                                        <button type="button" class="btn btn-success w-100 py-2" onclick="addMoreTransports(${day})">
                                            <i class="ri-add-line me-2"></i>Add More Vehicles
                                        </button>
                                    </div>
                                </div>
                             </div>
                         </div>
                     </div>
                     
                     <div class="mt-3 text-center">
                         <button type="button" class="btn btn-sm btn-outline-warning" onclick="addMoreTransports(${day})">
                             <i class="ri-add-line me-1"></i>Add More Transport
                         </button>
                     </div>
                 </div>
             </div>
                 
             </div>
         </div>
             `;
         }
         
         container.innerHTML = servicesHTML;
         
         // Initialize guest summaries for all services
         initializeServiceGuestSummaries();
        
        // Load attractions for all attraction dropdowns
        loadAttractionsForAllDays();
        
        // Load guides for all guide dropdowns
        loadGuidesForAllDays();
        
        // Load restaurants for all restaurant dropdowns
        loadRestaurantsForAllDays();
    }
    
    // Load attractions for all days
    function loadAttractionsForAllDays() {
        const attractionSelects = document.querySelectorAll('.attraction-select');
        attractionSelects.forEach(select => {
            loadAttractionsDropdown(select);
        });
    }
    
    // Load attractions into dropdown
    function loadAttractionsDropdown(selectElement) {
        selectElement.innerHTML = '<option value="">Loading attractions...</option>';
        
        fetch('{{ route('fetch-attractions-by-dmc') }}')
            .then(response => response.json())
            .then(data => {
                selectElement.innerHTML = '<option value="">Search Attraction</option>';
                
                if (data.success && data.attractions) {
                    data.attractions.forEach(attraction => {
                        const option = document.createElement('option');
                        option.value = attraction.attraction_id;
                        option.textContent = attraction.name + (attraction.location ? ' - ' + attraction.location : '');
                        option.dataset.timeSlots = JSON.stringify(attraction.time_slots);
                        // Set pricing data attributes
                        option.dataset.adultPrice = attraction.adult_price || 0;
                        option.dataset.childPrice = attraction.child_price || 0;
                        option.dataset.seniorPrice = attraction.senior_price || 0;
                        selectElement.appendChild(option);
                    });
                } else {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = data.message || 'No attractions available';
                    option.disabled = true;
                    selectElement.appendChild(option);
                }
            })
            .catch(error => {
                console.error('Error loading attractions:', error);
                selectElement.innerHTML = '<option value="">Error loading attractions</option>';
            });
    }
    
    // Load attraction details (time slots and tickets)
    window.loadAttractionDetails = function(day, attractionId, index = 1) {
        if (!attractionId) {
            // Clear time slots and tickets if no attraction selected
            document.getElementById('day' + day + '_attraction_' + index + '_time').innerHTML = '<option value="">Select Time Slot</option>';
            document.getElementById('day' + day + '_attraction_' + index + '_ticket').innerHTML = '<option value="">Select Ticket</option>';
            // Hide price display
            const priceDisplay = document.getElementById(`day${day}_attraction_${index}_price_display`);
            if (priceDisplay) {
                priceDisplay.style.display = 'none';
            }
            return;
        }
        
        // Load time slots from selected option data
        const attractionSelect = document.getElementById('day' + day + '_attraction_' + index);
        const selectedOption = attractionSelect.options[attractionSelect.selectedIndex];
        const timeSlots = JSON.parse(selectedOption.dataset.timeSlots || '[]');
        
        const timeSlotSelect = document.getElementById('day' + day + '_attraction_' + index + '_time');
        timeSlotSelect.innerHTML = '<option value="">Select Time Slot</option>';
        
        timeSlots.forEach(slot => {
            const option = document.createElement('option');
            option.value = slot.slot;
            option.textContent = slot.slot;
            timeSlotSelect.appendChild(option);
        });
        
        // Load tickets for the selected attraction
        loadTicketsForAttraction(day, attractionId, index);
        
        // Update attraction pricing display
        updateAttractionPricing(day, index);
    };
    
    // Update attraction pricing display
    window.updateAttractionPricing = function(day, index = 1) {
        console.log(`=== UPDATING ATTRACTION PRICING: Day ${day}, Index ${index} ===`);
        
        const attractionSelect = document.getElementById(`day${day}_attraction_${index}`);
        const ticketSelect = document.getElementById(`day${day}_attraction_${index}_ticket`);
        const priceDisplay = document.getElementById(`day${day}_attraction_${index}_price_display`);
        
        console.log('Elements found:', {
            attractionSelect: !!attractionSelect,
            ticketSelect: !!ticketSelect,
            priceDisplay: !!priceDisplay
        });
        
        if (!attractionSelect || !priceDisplay) {
            console.log('Required elements not found for attraction pricing update');
            return;
        }
        
        const selectedAttraction = attractionSelect.options[attractionSelect.selectedIndex];
        const selectedTicket = ticketSelect ? ticketSelect.options[ticketSelect.selectedIndex] : null;
        
        console.log('Selected values:', {
            attractionValue: selectedAttraction?.value,
            attractionText: selectedAttraction?.text,
            ticketValue: selectedTicket?.value,
            ticketText: selectedTicket?.text
        });
        
        if (!selectedAttraction.value) {
            console.log('No attraction selected, hiding price display');
            priceDisplay.style.display = 'none';
            return;
        }
        
        // Check if ticket is selected (required for pricing)
        if (!selectedTicket || !selectedTicket.value) {
            console.log('No ticket selected, showing info message');
            priceDisplay.style.display = 'block';
            priceDisplay.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="ri-information-line me-2 fs-4"></i>
                    <div>
                        <strong>Attraction Selected: ${selectedAttraction.text}</strong>
                        <div class="small text-muted">Please select a ticket to see pricing information</div>
                    </div>
                </div>
            `;
            return;
        }
        
        // Get pricing data from the selected ticket option
        let adultPrice = parseFloat(selectedTicket.dataset.adultPrice) || 0;
        let childPrice = parseFloat(selectedTicket.dataset.childPrice) || 0;
        let seniorPrice = parseFloat(selectedTicket.dataset.seniorPrice) || 0;
        
        // If no pricing data in dataset, try to extract from ticket text content
        if (adultPrice === 0 && childPrice === 0 && seniorPrice === 0) {
            console.log('No pricing data in dataset, extracting from ticket text...');
            const ticketText = selectedTicket.textContent;
            
            // Extract adult price
            const adultMatch = ticketText.match(/Adult:\s*\$(\d+(?:\.\d+)?)/);
            if (adultMatch) {
                adultPrice = parseFloat(adultMatch[1]);
                console.log('Extracted adult price from text:', adultPrice);
            }
            
            // Extract child price
            const childMatch = ticketText.match(/Child:\s*\$(\d+(?:\.\d+)?)/);
            if (childMatch) {
                childPrice = parseFloat(childMatch[1]);
                console.log('Extracted child price from text:', childPrice);
            }
            
            // Extract senior price
            const seniorMatch = ticketText.match(/Senior:\s*\$(\d+(?:\.\d+)?)/);
            if (seniorMatch) {
                seniorPrice = parseFloat(seniorMatch[1]);
                console.log('Extracted senior price from text:', seniorPrice);
            }
        }
        
        // Debug logging for ticket pricing
        console.log('=== TICKET PRICING DEBUG ===');
        console.log('Selected ticket option:', selectedTicket);
        console.log('Ticket dataset:', selectedTicket.dataset);
        console.log('Raw adult_price:', selectedTicket.dataset.adultPrice);
        console.log('Raw child_price:', selectedTicket.dataset.childPrice);
        console.log('Raw senior_price:', selectedTicket.dataset.seniorPrice);
        console.log('Final parsed prices:', { adultPrice, childPrice, seniorPrice });
        
        // Get current guest counts from the guest summary
        const guestSummaryElement = document.getElementById(`day${day}_attraction_${index}_guest_summary`);
        if (!guestSummaryElement) {
            priceDisplay.style.display = 'none';
            return;
        }
        
        const guestInfo = parseGuestSummary(guestSummaryElement.textContent);
        
        if (guestInfo.adults === 0 && guestInfo.children === 0 && guestInfo.seniors === 0) {
            priceDisplay.style.display = 'none';
            return;
        }
        
        // Calculate total price
        const totalPrice = (guestInfo.adults * adultPrice) + 
                          (guestInfo.children * childPrice) + 
                          (guestInfo.seniors * seniorPrice);
        
        if (totalPrice > 0) {
            priceDisplay.style.display = 'block';
            priceDisplay.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="ri-money-dollar-circle-line me-2 fs-4"></i>
                    <div>
                        <strong>Ticket Pricing: ${selectedTicket.text}</strong>
                        <div class="small">
                            <strong>Adult Price:</strong> $${adultPrice.toFixed(2)} × ${guestInfo.adults} = $${(adultPrice * guestInfo.adults).toFixed(2)}<br>
                            <strong>Child Price:</strong> $${childPrice.toFixed(2)} × ${guestInfo.children} = $${(childPrice * guestInfo.children).toFixed(2)}<br>
                            <strong>Senior Price:</strong> $${seniorPrice.toFixed(2)} × ${guestInfo.seniors} = $${(seniorPrice * guestInfo.seniors).toFixed(2)}<br>
                            <strong>Total Price:</strong> <span class="text-success fw-bold">$${totalPrice.toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            `;
            
            console.log(`Ticket pricing updated for day ${day}, index ${index}: Total: $${totalPrice}`);
        } else {
            priceDisplay.style.display = 'none';
        }
    }
    
    // Load tickets for specific attraction
    function loadTicketsForAttraction(day, attractionId, index = 1) {
        const ticketSelect = document.getElementById('day' + day + '_attraction_' + index + '_ticket');
        ticketSelect.innerHTML = '<option value="">Loading tickets...</option>';
        
        fetch('{{ route('fetch-tickets-by-attraction') }}?attraction_id=' + attractionId)
            .then(response => response.json())
            .then(data => {
                ticketSelect.innerHTML = '<option value="">Select Ticket</option>';
                
                if (data.success && data.tickets) {
                    data.tickets.forEach(ticket => {
                        const option = document.createElement('option');
                        option.value = ticket.ticket_id;
                        option.textContent = ticket.name + 
                            (ticket.adult_price ? ' - Adult: $' + ticket.adult_price : '') + 
                            (ticket.child_price ? ' Child: $' + ticket.child_price : '') + 
                            (ticket.senior_adult_price ? ' Senior: $' + ticket.senior_adult_price : '');
                        
                        // Set pricing data attributes for the ticket
                        option.dataset.adultPrice = ticket.adult_price || 0;
                        option.dataset.childPrice = ticket.child_price || 0;
                        option.dataset.seniorPrice = ticket.senior_adult_price || 0;
                        option.dataset.description = ticket.description || '';
                        
                        ticketSelect.appendChild(option);
                    });
                    
                    if (data.tickets.length === 0) {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'No tickets available for this attraction';
                        option.disabled = true;
                        ticketSelect.appendChild(option);
                    }
                    
                    // Update pricing display after loading tickets
                    updateAttractionPricing(day, index);
                } else {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = data.message || 'No tickets available';
                    option.disabled = true;
                    ticketSelect.appendChild(option);
                }
            })
            .catch(error => {
                console.error('Error loading tickets:', error);
                ticketSelect.innerHTML = '<option value="">Error loading tickets</option>';
            });
    }
     
    function initializeServiceGuestSummaries() {
         // Get current guest values from main form
         const male = parseInt(document.getElementById('male').value) || 0;
         const female = parseInt(document.getElementById('female').value) || 0;
         const children = parseInt(document.getElementById('children').value) || 0;
         const infants = parseInt(document.getElementById('infants').value) || 0;
         
         const adults = male + female;
         
         // Create formatted text like your image
         const summaryText = `${adults} adults (${male} male), ${children} children -${infants} infants`;
         
         // Find all guest summary text elements and update them
         const summaryElements = document.querySelectorAll('[id$="_guest_summary"]');
         summaryElements.forEach(element => {
             element.textContent = summaryText;
         });
         
         // Update badge displays
         const guestBadges = document.querySelectorAll('.guest-badges');
         guestBadges.forEach(badgeContainer => {
             const badges = badgeContainer.querySelectorAll('.badge');
             if (badges.length >= 3) {
                 badges[0].textContent = adults; // Total adults
                 badges[1].textContent = children; // Children
                 badges[2].textContent = infants; // Infants
             }
         });
    }

     // Remove hotel function
    window.removeHotel = function(index) {
         console.log(`Removing hotel at index ${index}:`, selectedHotels[index]);
         
         selectedHotels.splice(index, 1);
         displaySelectedHotels();
         
         // Update hotel_data JSON field after removal
         updateHotelDataField();
         
         // Ensure total price is updated after removal
         updatePackageTotalPriceDisplay();
         
         console.log(`Hotel removed. Remaining hotels: ${selectedHotels.length}`);
         
         if (selectedHotels.length === 0) {
            document.getElementById('packageDetailsSection').style.display = 'none';
            document.getElementById('transportSection').style.display = 'none';
            document.getElementById('submitSection').style.display = 'none';
        }
    };

         // Service management functions with improved UI feedback
    window.addPortService = function(day) {
         showNotification(`Port service added for Day ${day}`, 'success');
         console.log('Add port service for day', day);
         // Future implementation: Add dynamic form for port services
    };
     
    window.addAttractionService = function(day) {
         showNotification(`Attraction service added for Day ${day}`, 'success');
         console.log('Add attraction service for day', day);
         // Future implementation: Add dynamic form for attractions
    };
     
    window.addMoreAttractions = function(day) {
        const container = document.getElementById(`day${day}_attractions_container`);
        const existingAttractions = container.querySelectorAll('.attraction-item');
        const newIndex = existingAttractions.length + 1;
        
        const newAttractionHTML = `
            <div class="card border-danger shadow-sm attraction-item mb-3" data-attraction-index="${newIndex}">
                <div class="card-header bg-danger text-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <span class="service-icon me-3">
                                <i class="ri-ticket-line fs-4"></i>
                            </span>
                            <div>
                                <h6 class="mb-0 fw-bold">Attraction Booking #${newIndex}</h6>
                                <small class="opacity-75">Select your preferred attractions</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="removeAttraction(this, ${day}, ${newIndex})">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body bg-white">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Select Attraction</label>
                            <select class="form-select attraction-select" name="day${day}_attraction_${newIndex}" id="day${day}_attraction_${newIndex}" onchange="loadAttractionDetails(${day}, this.value, ${newIndex})">
                                <option value="">Search Attraction</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Select Guests</label>
                            <div class="guest-selector">
                                <div class="guest-display p-2 border rounded bg-light">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="guest-info">
                                            <span id="day${day}_attraction_${newIndex}_guest_summary" class="text-muted small">
                                                1 adults (1 male, 0 female), 0 children -0 infants
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openGuestSelector('day${day}_attraction_${newIndex}')">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                    </div>
                                    <div class="guest-badges mt-1">
                                        <span class="badge bg-primary">4</span>
                                        <span class="badge bg-success">0</span>
                                        <span class="badge bg-warning text-dark">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Select Time Slot</label>
                            <select class="form-select" name="day${day}_attraction_${newIndex}_time" id="day${day}_attraction_${newIndex}_time">
                                <option value="">Select Time Slot</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Select Ticket</label>
                            <select class="form-select" name="day${day}_attraction_${newIndex}_ticket" id="day${day}_attraction_${newIndex}_ticket" onchange="updateAttractionPricing(${day}, ${newIndex})">
                                <option value="">Select Ticket</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Attraction Price Display -->
                    <div class="col-12 mt-3">
                        <div id="day${day}_attraction_${newIndex}_price_display" class="alert alert-info" style="display: none;">
                            <div class="d-flex align-items-center">
                                <i class="ri-money-dollar-circle-line me-2 fs-4"></i>
                                <div>
                                    <strong>Attraction Pricing</strong>
                                    <div class="small">Select an attraction and configure guests to see pricing</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', newAttractionHTML);
        
        // Load attractions for the new dropdown
        const newSelect = document.getElementById(`day${day}_attraction_${newIndex}`);
        if (newSelect) {
            loadAttractionsDropdown(newSelect);
        }
        
        // Update guest summary for the new attraction with current main guest selection
        const mainMale = parseInt(document.getElementById('male')?.value) || 0;
        const mainFemale = parseInt(document.getElementById('female')?.value) || 0;
        const mainChildren = parseInt(document.getElementById('children')?.value) || 0;
        const mainInfants = parseInt(document.getElementById('infants')?.value) || 0;
        
        const summaryElement = document.getElementById(`day${day}_attraction_${newIndex}_guest_summary`);
        if (summaryElement) {
            const adults = mainMale + mainFemale;
            summaryElement.textContent = `${adults} adults (${mainMale} male, ${mainFemale} female), ${mainChildren} children -${mainInfants} infants`;
        }
        
        showNotification(`Attraction Booking #${newIndex} added for Day ${day}`, 'success');
    };
    
    window.removeAttraction = function(button, day, index) {
        const attractionItem = button.closest('.attraction-item');
        attractionItem.remove();
        showNotification(`Attraction Booking #${index} removed from Day ${day}`, 'info');
    };
    
    // Load restaurants for all days
    function loadRestaurantsForAllDays() {
        const restaurantSelects = document.querySelectorAll('.restaurant-select');
        restaurantSelects.forEach(select => {
            loadRestaurantsDropdown(select);
        });
    }
    
    // Load restaurants into dropdown
    function loadRestaurantsDropdown(selectElement) {
        selectElement.innerHTML = '<option value="">Loading restaurants...</option>';
        
        fetch('{{ route('fetch-restaurants-by-dmc') }}')
            .then(response => response.json())
            .then(data => {
                selectElement.innerHTML = '<option value="">Search Restaurant</option>';
                
                if (data.success && data.restaurants) {
                    data.restaurants.forEach(restaurant => {
                        const option = document.createElement('option');
                        option.value = restaurant.restaurant_id;
                        option.textContent = restaurant.name;
                        option.dataset.mealTypes = JSON.stringify(restaurant.meal_types);
                        selectElement.appendChild(option);
                    });
                } else {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = data.message || 'No restaurants available';
                    option.disabled = true;
                    selectElement.appendChild(option);
                }
            })
            .catch(error => {
                console.error('Error loading restaurants:', error);
                selectElement.innerHTML = '<option value="">Error loading restaurants</option>';
            });
    }
    
    // Load restaurant details and meal types
    window.loadRestaurantDetails = function(day, restaurantId, index = 1) {
        console.log('loadRestaurantDetails called:', day, restaurantId, index);
        
        if (!restaurantId) {
            // Clear meal type and dish dropdowns if no restaurant selected
            const mealTypeSelect = document.getElementById('day' + day + '_meal_type_' + index);
            const dishSelect = document.getElementById('day' + day + '_dish_' + index);
            
            if (mealTypeSelect) {
                mealTypeSelect.innerHTML = '<option value="">Select Meal Type</option>';
            }
            if (dishSelect) {
                dishSelect.innerHTML = '<option value="">Select Dish</option>';
            }
            return;
        }
        
        // Get meal types from selected restaurant
        const restaurantSelect = document.getElementById('day' + day + '_restaurant_' + index);
        if (!restaurantSelect) {
            console.error('Restaurant select not found:', 'day' + day + '_restaurant_' + index);
            return;
        }
        
        const selectedOption = restaurantSelect.options[restaurantSelect.selectedIndex];
        const mealTypes = JSON.parse(selectedOption.dataset.mealTypes || '[]');
        
        console.log('Available meal types:', mealTypes);
        
        // Update meal type dropdown
        updateMealTypeDropdown(day, index, mealTypes);
        
        // Load all dishes for the selected restaurant (initially without meal period filter)
        loadDishesForRestaurant(day, restaurantId, index);
    };
    
    // Update meal type dropdown with available options and timings
    function updateMealTypeDropdown(day, index, mealTypes) {
        const mealTypeSelect = document.getElementById('day' + day + '_meal_type_' + index);
        if (!mealTypeSelect) return;
        
        let optionsHTML = '<option value="">Select Meal Type</option>';
        
        mealTypes.forEach(mealType => {
            const timing = mealType.open_time && mealType.close_time 
                ? ` - ${mealType.open_time} to ${mealType.close_time}`
                : '';
            
            // Map meal type to meal_period number and add icons
            let mealPeriod = '';
            let icon = '';
            switch(mealType.type.toLowerCase()) {
                case 'breakfast':
                    mealPeriod = '1';
                    icon = '🌅 ';
                    break;
                case 'lunch':
                    mealPeriod = '2';
                    icon = '☀️ ';
                    break;
                case 'dinner':
                    mealPeriod = '3';
                    icon = '🌙 ';
                    break;
            }
            
            optionsHTML += `<option value="${mealType.type}" data-meal-period="${mealPeriod}" data-open-time="${mealType.open_time}" data-close-time="${mealType.close_time}">${icon}${mealType.label}${timing}</option>`;
        });
        
        mealTypeSelect.innerHTML = optionsHTML;
        
        // Add event listener for meal type change to filter dishes
        mealTypeSelect.removeEventListener('change', mealTypeChangeHandler); // Remove existing listener
        mealTypeSelect.addEventListener('change', mealTypeChangeHandler);
        
        function mealTypeChangeHandler() {
            const selectedOption = this.options[this.selectedIndex];
            const mealPeriod = selectedOption.dataset.mealPeriod;
            
            // Get restaurant ID from the restaurant dropdown
            const restaurantSelect = document.getElementById('day' + day + '_restaurant_' + index);
            const restaurantId = restaurantSelect ? restaurantSelect.value : null;
            
            if (restaurantId && mealPeriod) {
                loadDishesForRestaurant(day, restaurantId, index, mealPeriod);
                populateTimeSlots(day, index, selectedOption.dataset.openTime, selectedOption.dataset.closeTime);
            } else if (restaurantId) {
                loadDishesForRestaurant(day, restaurantId, index);
            }
        }
        
        console.log('Updated meal type dropdown with', mealTypes.length, 'options');
    }
    
    // Populate time slots with 30-minute intervals
    function populateTimeSlots(day, index, openTime, closeTime) {
        const timeSlotSelect = document.getElementById('day' + day + '_time_slot_' + index);
        if (!timeSlotSelect || !openTime || !closeTime) return;
        
        timeSlotSelect.innerHTML = '<option value="">Select Time Slot</option>';
        
        // Parse open and close times
        const startTime = parseTime(openTime);
        const endTime = parseTime(closeTime);
        
        if (!startTime || !endTime) return;
        
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
    }
    
    // Parse time string (handles various formats)
    function parseTime(timeStr) {
        if (!timeStr) return null;
        
        try {
            // Handle "HH:MM AM/PM" format
            if (timeStr.includes('AM') || timeStr.includes('PM')) {
                const today = new Date();
                const [time, period] = timeStr.split(' ');
                const [hours, minutes] = time.split(':');
                let hour = parseInt(hours);
                
                if (period === 'PM' && hour !== 12) hour += 12;
                if (period === 'AM' && hour === 12) hour = 0;
                
                today.setHours(hour, parseInt(minutes) || 0, 0, 0);
                return today;
            }
            
            // Handle "HH:MM" 24-hour format
            const [hours, minutes] = timeStr.split(':');
            const today = new Date();
            today.setHours(parseInt(hours), parseInt(minutes) || 0, 0, 0);
            return today;
        } catch (e) {
            console.error('Error parsing time:', timeStr, e);
            return null;
        }
    }
    
    // Format time to 24-hour format
    function formatTime24(date) {
        return date.toTimeString().substring(0, 5); // "HH:MM"
    }
    
    // Format time to 12-hour format
    function formatTime12(date) {
        return date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }
    
    // Open dish selection modal
    window.openDishModal = function(day, index, selectedOption) {
        console.log('openDishModal called with:', {day, index, selectedOption});
        
        const dishType = selectedOption.dataset.typeLabel;
        const mealId = selectedOption.dataset.mealId;
        const mealName = selectedOption.dataset.mealName;
        const price = parseFloat(selectedOption.dataset.price || 0);
        const adultPrice = parseFloat(selectedOption.dataset.adultPrice || 0);
        const childPrice = parseFloat(selectedOption.dataset.childPrice || 0);
        const mealType = parseInt(selectedOption.dataset.mealType);
        
        console.log('Modal data:', {dishType, mealId, mealName, price, adultPrice, childPrice, mealType});
        
        // Update modal title
        const modalLabel = document.getElementById('dishSelectionModalLabel');
        if (modalLabel) {
            modalLabel.textContent = `Select ${dishType}`;
        } else {
            console.error('Modal label not found!');
        }
        
        // Get guest counts from the main form
        const maleCountEl = document.getElementById('male-count');
        const femaleCountEl = document.getElementById('female-count');
        const childrenCountEl = document.getElementById('children-count');
        
        console.log('Guest count elements:', {maleCountEl, femaleCountEl, childrenCountEl});
        
        const adultCount = (maleCountEl ? parseInt(maleCountEl.textContent) || 0 : 0) + 
                          (femaleCountEl ? parseInt(femaleCountEl.textContent) || 0 : 0);
        const childCount = childrenCountEl ? parseInt(childrenCountEl.textContent) || 0 : 0;
        
        console.log('Guest counts calculated:', {adultCount, childCount});
        
        if (mealType === 1) { // Buffet
            setupBuffetModal(mealName, adultPrice, childPrice, adultCount, childCount);
        } else if (mealType === 2) { // Set Menu
            setupSetMenuModal(mealName, price);
        }
        
        // Store modal data for confirmation
        window.currentDishSelection = {
            day: day,
            index: index,
            mealId: mealId,
            dishType: dishType,
            mealType: mealType
        };
        
        // Show modal
        const modalElement = document.getElementById('dishSelectionModal');
        console.log('Modal element found:', modalElement);
        
        if (!modalElement) {
            console.error('Modal element not found!');
            return;
        }
        
        try {
            // Try to get existing modal instance first
            let modal = bootstrap.Modal.getInstance(modalElement);
            if (!modal) {
                modal = new bootstrap.Modal(modalElement);
                console.log('New Bootstrap modal created:', modal);
            } else {
                console.log('Using existing Bootstrap modal:', modal);
            }
            
            console.log('About to show modal...');
            modal.show();
            console.log('Modal show() called');
            
        } catch (error) {
            console.error('Error showing modal:', error);
            // Fallback: try to show modal using jQuery if available
            if (typeof $ !== 'undefined') {
                console.log('Trying jQuery fallback...');
                $(modalElement).modal('show');
            }
        }
    }
    
    // Setup Buffet Modal
    window.setupBuffetModal = function(mealName, adultPrice, childPrice, adultCount, childCount) {
        const totalPrice = (adultPrice * adultCount) + (childPrice * childCount);
        
        const content = `
            <div class="card border-light">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <input type="radio" class="form-check-input me-3" checked disabled>
                        <div>
                            <h6 class="mb-1">${mealName}</h6>
                            <small class="text-muted">
                                Adult: $${adultPrice.toFixed(2)} × ${adultCount} = $${(adultPrice * adultCount).toFixed(2)}
                                ${childCount > 0 ? `<br>Child: $${childPrice.toFixed(2)} × ${childCount} = $${(childPrice * childCount).toFixed(2)}` : ''}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const modalContent = document.getElementById('dishModalContent');
        const modalTotalPrice = document.getElementById('modalTotalPrice');
        const modalGuestInfo = document.getElementById('modalGuestInfo');
        const confirmButton = document.getElementById('confirmDishSelection');
        
        if (modalContent) {
            modalContent.innerHTML = content;
        } else {
            console.error('Modal content element not found!');
        }
        
        if (modalTotalPrice) {
            modalTotalPrice.textContent = `$${totalPrice.toFixed(2)}`;
        } else {
            console.error('Modal total price element not found!');
        }
        
        if (modalGuestInfo) {
            modalGuestInfo.textContent = `${adultCount} Adults${childCount > 0 ? `, ${childCount} Children` : ''}`;
        } else {
            console.error('Modal guest info element not found!');
        }
        
        if (confirmButton) {
            confirmButton.disabled = false;
        } else {
            console.error('Confirm button not found!');
        }
    }
    
    // Setup Set Menu Modal
    window.setupSetMenuModal = function(mealName, unitPrice) {
        let quantity = 1;
        
        function updateSetMenuPrice() {
            const totalPrice = unitPrice * quantity;
            const modalTotalPrice = document.getElementById('modalTotalPrice');
            const modalGuestInfo = document.getElementById('modalGuestInfo');
            const confirmButton = document.getElementById('confirmDishSelection');
            
            if (modalTotalPrice) {
                modalTotalPrice.textContent = `$${totalPrice.toFixed(2)}`;
            }
            if (modalGuestInfo) {
                modalGuestInfo.textContent = `Quantity: ${quantity}`;
            }
            if (confirmButton) {
                confirmButton.disabled = quantity === 0;
            }
        }
        
        const content = `
            <div class="card border-light">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <input type="radio" class="form-check-input me-3" checked disabled>
                            <div>
                                <h6 class="mb-1">${mealName}</h6>
                                <small class="text-muted">$${unitPrice.toFixed(2)}</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="decreaseQty">-</button>
                            <span class="mx-3 fw-bold" id="quantityDisplay">${quantity}</span>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="increaseQty">+</button>
                            <span class="ms-3 text-success fw-bold">= $${(unitPrice * quantity).toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const modalContent = document.getElementById('dishModalContent');
        if (modalContent) {
            modalContent.innerHTML = content;
            updateSetMenuPrice();
        } else {
            console.error('Modal content element not found for Set Menu!');
        }
        
        // Add event listeners for quantity buttons
        document.getElementById('decreaseQty').addEventListener('click', function() {
            if (quantity > 0) {
                quantity--;
                document.getElementById('quantityDisplay').textContent = quantity;
                document.querySelector('.text-success.fw-bold').textContent = `= $${(unitPrice * quantity).toFixed(2)}`;
                window.currentDishSelection.quantity = quantity;
                updateSetMenuPrice();
            }
        });
        
        document.getElementById('increaseQty').addEventListener('click', function() {
            quantity++;
            document.getElementById('quantityDisplay').textContent = quantity;
            document.querySelector('.text-success.fw-bold').textContent = `= $${(unitPrice * quantity).toFixed(2)}`;
            window.currentDishSelection.quantity = quantity;
            updateSetMenuPrice();
        });
        
        // Store initial quantity
        window.currentDishSelection.quantity = quantity;
    }
    
    // Simplified modal function
    window.showDishSelectionModal = function(meal, day, index) {
        console.log('Showing modal for meal:', meal);
        
                 // Get guest counts from the service-specific selection, not main form
         const serviceGuestSummary = document.getElementById(`day${day}_restaurant_${index}_guest_summary`);
         let adultCount = 1;
         let childCount = 0;
         
         if (serviceGuestSummary) {
             const summaryText = serviceGuestSummary.textContent;
             // Parse text like "3 adults (1 male), 0 children -0 infants"
             const adultMatch = summaryText.match(/(\d+)\s+adults/);
             const childMatch = summaryText.match(/(\d+)\s+children/);
             
             adultCount = adultMatch ? parseInt(adultMatch[1]) : 1;
             childCount = childMatch ? parseInt(childMatch[1]) : 0;
         } else {
             // Fallback to main form if service selection not found
             const maleCountEl = document.getElementById('male-count');
             const femaleCountEl = document.getElementById('female-count');
             const childrenCountEl = document.getElementById('children-count');
             
             adultCount = (maleCountEl ? parseInt(maleCountEl.textContent) || 0 : 0) + 
                         (femaleCountEl ? parseInt(femaleCountEl.textContent) || 0 : 0);
             childCount = childrenCountEl ? parseInt(childrenCountEl.textContent) || 0 : 0;
         }
        
        let modalHTML = '';
        let totalPrice = 0;
        
        if (meal.type == 1) { // Buffet
            const adultPrice = parseFloat(meal.adult_price) || 0;
            const childPrice = parseFloat(meal.child_price) || 0;
            totalPrice = (adultPrice * adultCount) + (childPrice * childCount);
            
            modalHTML = `
                <div class="modal fade" id="tempDishModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Select ${meal.display_name}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="card border-light">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <input type="radio" class="form-check-input me-3" checked disabled>
                                            <div>
                                                <h6 class="mb-1">${meal.name}</h6>
                                                <small class="text-muted">
                                                    Adult: $${adultPrice.toFixed(2)} × ${adultCount} = $${(adultPrice * adultCount).toFixed(2)}
                                                    ${childCount > 0 ? `<br>Child: $${childPrice.toFixed(2)} × ${childCount} = $${(childPrice * childCount).toFixed(2)}` : ''}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-shopping-cart text-success me-2"></i>
                                            <span class="fw-bold">Total Price:</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <span class="h4 text-success">$${totalPrice.toFixed(2)}</span>
                                        <br>
                                        <small class="text-muted">${adultCount} Adults${childCount > 0 ? `, ${childCount} Children` : ''}</small>
                                    </div>
                                </div>
                            </div>
                                                         <div class="modal-footer">
                                 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCEL</button>
                                 <button type="button" class="btn btn-success" id="confirmBtn" onclick="confirmDishSelection(${meal.meal_id}, '${meal.display_name}', ${day}, ${index}, ${totalPrice.toFixed(2)})" ${totalPrice > 0 ? '' : 'disabled'}>
                                     <i class="fas fa-check me-2"></i>CONFIRM SELECTION
                                 </button>
                             </div>
                         </div>
                     </div>
                 </div>
             `;
         } else if (meal.type == 2) { // Set Menu
            const unitPrice = parseFloat(meal.price) || 0;
            totalPrice = unitPrice;
            
            modalHTML = `
                <div class="modal fade" id="tempDishModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Select ${meal.display_name}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="card border-light">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <input type="radio" class="form-check-input me-3" checked disabled>
                                                <div>
                                                    <h6 class="mb-1">${meal.name}</h6>
                                                    <small class="text-muted">$${unitPrice.toFixed(2)}</small>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="updateQuantity(-1)">-</button>
                                                <span class="mx-3 fw-bold" id="quantityDisplay">1</span>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="updateQuantity(1)">+</button>
                                                <span class="ms-3 text-success fw-bold" id="priceDisplay">= $${unitPrice.toFixed(2)}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-shopping-cart text-success me-2"></i>
                                            <span class="fw-bold">Total Price:</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <span class="h4 text-success" id="totalPriceDisplay">$${totalPrice.toFixed(2)}</span>
                                        <br>
                                        <small class="text-muted" id="quantityInfo">Quantity: 1</small>
                                    </div>
                                </div>
                            </div>
                                                         <div class="modal-footer">
                                 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCEL</button>
                                 <button type="button" class="btn btn-success" id="confirmBtn" onclick="confirmDishSelection(${meal.meal_id}, '${meal.display_name}', ${day}, ${index}, ${unitPrice.toFixed(2)})" disabled>
                                     <i class="fas fa-check me-2"></i>CONFIRM SELECTION
                                 </button>
                             </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Remove any existing temp modal
        const existingModal = document.getElementById('tempDishModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to page
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('tempDishModal'));
        modal.show();
        
        // Store current selection data
        window.currentDishData = {
            meal: meal,
            day: day,
            index: index,
            quantity: 1,
            unitPrice: parseFloat(meal.price) || 0
        };
    };
    
         // Quantity update function for Set Menu
    window.updateQuantity = function(change) {
        const currentData = window.currentDishData;
        if (!currentData) return;
        
        currentData.quantity = Math.max(0, currentData.quantity + change);
        
        const quantityDisplay = document.getElementById('quantityDisplay');
        const priceDisplay = document.getElementById('priceDisplay');
        const totalPriceDisplay = document.getElementById('totalPriceDisplay');
        const quantityInfo = document.getElementById('quantityInfo');
        const confirmBtn = document.getElementById('confirmBtn');
        
        const totalPrice = currentData.unitPrice * currentData.quantity;
        
        if (quantityDisplay) quantityDisplay.textContent = currentData.quantity;
        if (priceDisplay) priceDisplay.textContent = `= $${totalPrice.toFixed(2)}`;
        if (totalPriceDisplay) totalPriceDisplay.textContent = `$${totalPrice.toFixed(2)}`;
        if (quantityInfo) quantityInfo.textContent = `Quantity: ${currentData.quantity}`;
        
        // Enable/disable confirm button based on quantity
        if (confirmBtn) {
            confirmBtn.disabled = currentData.quantity === 0;
            // Update onclick with current total price
            confirmBtn.setAttribute('onclick', `confirmDishSelection(${currentData.meal.meal_id}, '${currentData.meal.display_name}', ${currentData.day}, ${currentData.index}, ${totalPrice.toFixed(2)})`);
        }
    };
    
         // Confirm selection function
    window.confirmDishSelection = function(mealId, dishName, day, index, totalPrice) {
        console.log('Dish confirmed:', dishName, 'Price:', totalPrice);
        
        // Store pricing data in hidden fields
        const totalPriceField = document.getElementById(`day${day}_restaurant_${index}_total_price`);
        const mealIdField = document.getElementById(`day${day}_restaurant_${index}_meal_id`);
        const dishNameField = document.getElementById(`day${day}_restaurant_${index}_dish_name`);
        
        if (totalPriceField) totalPriceField.value = totalPrice;
        if (mealIdField) mealIdField.value = mealId;
        if (dishNameField) dishNameField.value = dishName;
        
        console.log(`Restaurant pricing stored for day ${day}, index ${index}:`);
        console.log(`- Total Price: $${totalPrice}`);
        console.log(`- Meal ID: ${mealId}`);
        console.log(`- Dish Name: ${dishName}`);
        
        // Mark the selected dish button as selected and show price
        const dishContainer = document.getElementById(`day${day}_dish_container_${index}`);
        if (dishContainer) {
            dishContainer.querySelectorAll('.dish-option-btn').forEach(btn => {
                btn.classList.remove('selected');
                if (btn.dataset.mealId == mealId) {
                    btn.classList.add('selected');
                    // Update button text to show price
                    const icon = btn.innerHTML.split(' ')[0]; // Get the icon (🍽️ or 📋)
                    btn.innerHTML = `${icon} ${dishName} - $${totalPrice}`;
                }
            });
        }
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('tempDishModal'));
        if (modal) modal.hide();
        
        // Remove temp modal
        const tempModal = document.getElementById('tempDishModal');
        if (tempModal) tempModal.remove();
    };
    
    // Main guest selection modal functions


    // Function to update all service guest summaries
    function updateAllServiceGuestSummaries(male, female, children, infants) {
        const adults = male + female;
        const summaryText = `${adults} adults (${male} male, ${female} female), ${children} children -${infants} infants`;
        
        console.log('Updating all service guest summaries:', summaryText);
        
        // Find all guest summary elements across all service sections
        const guestSummaryElements = document.querySelectorAll('[id$="_guest_summary"]');
        
        guestSummaryElements.forEach(function(element) {
            element.textContent = summaryText;
            console.log('Updated guest summary for:', element.id);
        });
        
        // Also update any badge displays (the colored badges showing numbers)
        const badgeContainers = document.querySelectorAll('.guest-badges');
        badgeContainers.forEach(function(container) {
            const badges = container.querySelectorAll('.badge');
            if (badges.length >= 3) {
                badges[0].textContent = adults; // Primary badge (adults)
                badges[1].textContent = children; // Success badge (children)
                badges[2].textContent = infants; // Warning badge (infants)
            }
        });
        
        console.log('Updated all service guest summaries and badges');
    }

    // Test main guest modal
    window.testMainModal = function() {
        console.log('Testing main guest modal...');
        const modalElement = document.getElementById('mainGuestSelectorModal');
        if (modalElement) {
            console.log('Modal found, showing...');
            try {
                // Try to get existing modal instance first
                let modal = bootstrap.Modal.getInstance(modalElement);
                if (!modal) {
                    // Create new modal if one doesn't exist
                    modal = new bootstrap.Modal(modalElement);
                }
                modal.show();
            } catch (error) {
                console.error('Error showing modal:', error);
                // Fallback to jQuery if available
                if (typeof $ !== 'undefined') {
                    $(modalElement).modal('show');
                }
            }
        } else {
            console.error('Main guest modal element not found!');
        }
    };

    // Test function to check if modal works
    window.testModal = function() {
        console.log('Testing modal...');
        const modalElement = document.getElementById('dishSelectionModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            console.error('Modal element not found for testing');
        }
    };
    
    // Handle confirm selection
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('confirmDishSelection').addEventListener('click', function() {
            if (window.currentDishSelection) {
                const { day, index, mealId, dishType } = window.currentDishSelection;
                
                // Mark the selected dish button as selected
                const dishContainer = document.getElementById(`day${day}_dish_container_${index}`);
                if (dishContainer) {
                    // Remove previous selection
                    dishContainer.querySelectorAll('.dish-option-btn').forEach(btn => {
                        btn.classList.remove('selected');
                    });
                    
                    // Mark current selection
                    dishContainer.querySelectorAll('.dish-option-btn').forEach(btn => {
                        if (btn.dataset.mealId === mealId) {
                            btn.classList.add('selected');
                        }
                    });
                }
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('dishSelectionModal'));
                modal.hide();
                
                // Reset selection
                window.currentDishSelection = null;
            }
        });
    });
    
    // Load dishes for specific restaurant
    function loadDishesForRestaurant(day, restaurantId, index, mealPeriod = null) {
        const dishSelect = document.getElementById('day' + day + '_dish_' + index);
        if (!dishSelect) return;
        
        dishSelect.innerHTML = '<option value="">Loading dishes...</option>';
        
        let url = '{{ route('fetch-meals-by-restaurant') }}?restaurant_id=' + restaurantId;
        if (mealPeriod) {
            url += '&meal_period=' + mealPeriod;
        }
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                dishSelect.innerHTML = '<option value="">Select Dish</option>';
                
                if (data.success && data.meals) {
                    // Clear the select and replace with clickable options
                    dishSelect.style.display = 'none';
                    
                    // Create container for clickable dish options
                    let dishContainer = document.getElementById('day' + day + '_dish_container_' + index);
                    if (dishContainer) {
                        dishContainer.remove();
                    }
                    
                    dishContainer = document.createElement('div');
                    dishContainer.id = 'day' + day + '_dish_container_' + index;
                    dishContainer.className = 'dish-options-container';
                    
                    data.meals.forEach(meal => {
                        // Add icon based on dish type
                        let icon = '';
                        if (meal.type == 1) { // Buffet
                            icon = '🍽️ ';
                        } else if (meal.type == 2) { // Set Menu
                            icon = '📋 ';
                        }
                        
                        const dishButton = document.createElement('button');
                        dishButton.type = 'button';
                        dishButton.className = 'btn btn-outline-primary btn-sm me-2 mb-2 dish-option-btn';
                        dishButton.innerHTML = icon + meal.display_name;
                        dishButton.dataset.mealType = meal.type;
                        dishButton.dataset.typeLabel = meal.type_label;
                        dishButton.dataset.price = meal.price;
                        dishButton.dataset.adultPrice = meal.adult_price || 0;
                        dishButton.dataset.childPrice = meal.child_price || 0;
                        dishButton.dataset.mealId = meal.meal_id;
                        dishButton.dataset.mealName = meal.name;
                        
                        // Add backup onclick attribute
                        dishButton.setAttribute('onclick', `openDishModal(${day}, ${index}, this)`);
                        
                        // Add click event to open modal directly
                        dishButton.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            console.log('Dish button clicked:', meal.display_name);
                            
                            // Directly show modal with content
                            showDishSelectionModal(meal, day, index);
                        });
                        
                        dishContainer.appendChild(dishButton);
                    });
                    
                    // Insert the container after the hidden select
                    dishSelect.parentNode.appendChild(dishContainer);
                    
                    if (data.meals.length === 0) {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'No dishes available for this restaurant';
                        option.disabled = true;
                        dishSelect.appendChild(option);
                    }
                } else {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = data.message || 'No dishes available';
                    option.disabled = true;
                    dishSelect.appendChild(option);
                }
            })
            .catch(error => {
                console.error('Error loading dishes:', error);
                dishSelect.innerHTML = '<option value="">Error loading dishes</option>';
            });
    }
    
    window.addMoreRestaurants = function(day) {
        const container = document.getElementById(`day${day}_restaurants_container`);
        const existingRestaurants = container.querySelectorAll('.restaurant-item');
        const newIndex = existingRestaurants.length + 1;
        
        const newRestaurantHTML = `
            <div class="card border-success shadow-sm restaurant-item mb-3" data-restaurant-index="${newIndex}">
                <div class="card-header bg-success text-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <span class="service-icon me-3">
                                <i class="ri-restaurant-line fs-4"></i>
                            </span>
                            <div>
                                <h6 class="mb-0 fw-bold">Restaurant Booking #${newIndex}</h6>
                                <small class="opacity-75">Select your dining experience</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="removeRestaurant(this, ${day}, ${newIndex})">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body bg-white">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Select Restaurant</label>
                            <select class="form-select restaurant-select" name="day${day}_restaurant_${newIndex}" id="day${day}_restaurant_${newIndex}" onchange="loadRestaurantDetails(${day}, this.value, ${newIndex})">
                                <option value="">Search Restaurant</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Select Guests</label>
                            <div class="guest-selector">
                                <div class="guest-display p-2 border rounded bg-light">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="guest-info">
                                            <span id="day${day}_restaurant_${newIndex}_guest_summary" class="text-muted small">
                                                1 adults (1 male, 0 female), 0 children -0 infants
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openGuestSelector('day${day}_restaurant_${newIndex}')">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                    </div>
                                    <div class="guest-badges mt-1">
                                        <span class="badge bg-primary">4</span>
                                        <span class="badge bg-success">0</span>
                                        <span class="badge bg-warning text-dark">0</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden fields for restaurant pricing -->
                            <input type="hidden" name="day${day}_restaurant_${newIndex}_total_price" id="day${day}_restaurant_${newIndex}_total_price" value="0">
                            <input type="hidden" name="day${day}_restaurant_${newIndex}_meal_id" id="day${day}_restaurant_${newIndex}_meal_id" value="">
                            <input type="hidden" name="day${day}_restaurant_${newIndex}_dish_name" id="day${day}_restaurant_${newIndex}_dish_name" value="">
                        </div>
                                                 <div class="col-md-2">
                             <label class="form-label fw-semibold">Meal Type</label>
                             <select class="form-select" name="day${day}_meal_type_${newIndex}" id="day${day}_meal_type_${newIndex}">
                                 <option value="">Select Meal Type</option>
                             </select>
                             <small class="text-muted">Available meal types with timings</small>
                         </div>
                         <div class="col-md-2">
                             <label class="form-label fw-semibold">Select Dish</label>
                             <select class="form-select" name="day${day}_dish_${newIndex}" id="day${day}_dish_${newIndex}">
                                 <option value="">Select Dish</option>
                             </select>
                             <small class="text-muted">Buffet or Set Menu options</small>
                         </div>
                         <div class="col-md-2">
                             <label class="form-label fw-semibold">Time Slot</label>
                             <select class="form-select" name="day${day}_time_slot_${newIndex}" id="day${day}_time_slot_${newIndex}">
                                 <option value="">Select Time Slot</option>
                             </select>
                             <small class="text-muted">Available time slots</small>
                         </div>
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', newRestaurantHTML);
        
        // Load restaurants for the new dropdown
        const newSelect = document.getElementById(`day${day}_restaurant_${newIndex}`);
        if (newSelect) {
            loadRestaurantsDropdown(newSelect);
        }
        
        // Update guest summary for the new restaurant with current main guest selection
        const mainMale = parseInt(document.getElementById('male')?.value) || 0;
        const mainFemale = parseInt(document.getElementById('female')?.value) || 0;
        const mainChildren = parseInt(document.getElementById('children')?.value) || 0;
        const mainInfants = parseInt(document.getElementById('infants')?.value) || 0;
        
        const summaryElement = document.getElementById(`day${day}_restaurant_${newIndex}_guest_summary`);
        if (summaryElement) {
            const adults = mainMale + mainFemale;
            summaryElement.textContent = `${adults} adults (${mainMale} male, ${mainFemale} female), ${mainChildren} children -${mainInfants} infants`;
        }
        
        showNotification(`Restaurant Booking #${newIndex} added for Day ${day}`, 'success');
    };
    
    window.removeRestaurant = function(button, day, index) {
        const restaurantItem = button.closest('.restaurant-item');
        restaurantItem.remove();
        showNotification(`Restaurant Booking #${index} removed from Day ${day}`, 'info');
    };
    
    // Load guides for all days
    function loadGuidesForAllDays() {
        const guideSelects = document.querySelectorAll('.guide-select');
        guideSelects.forEach(select => {
            loadGuidesDropdown(select);
        });
    }
    
    // Load guides into dropdown
    function loadGuidesDropdown(selectElement) {
        selectElement.innerHTML = '<option value="">Loading guides...</option>';
        
        fetch('{{ route('fetch-guides-by-dmc') }}')
            .then(response => response.json())
            .then(data => {
                selectElement.innerHTML = '<option value="">Search Guide</option>';
                
                                 if (data.success && data.guides) {
                     data.guides.forEach(guide => {
                         const option = document.createElement('option');
                         option.value = guide.guide_id;
                         option.textContent = guide.name;
                         option.dataset.nightStartTime = guide.night_start_time;
                         option.dataset.nightEndTime = guide.night_end_time;
                         option.dataset.dayRate = guide.day_rate;
                         option.dataset.nightSurcharge = guide.night_surcharge;
                         option.dataset.hourlyPrice = guide.hourly_price;
                         option.dataset.twoHourPrice = guide.two_hour_price;
                         option.dataset.fourHourPrice = guide.four_hour_price;
                         option.dataset.sixHourPrice = guide.six_hour_price;
                         option.dataset.eightHourPrice = guide.eight_hour_price;
                         option.dataset.tenHourPrice = guide.ten_hour_price;
                         option.dataset.twelveHourPrice = guide.twelve_hour_price;
                         selectElement.appendChild(option);
                     });
                } else {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = data.message || 'No guides available';
                    option.disabled = true;
                    selectElement.appendChild(option);
                }
            })
            .catch(error => {
                console.error('Error loading guides:', error);
                selectElement.innerHTML = '<option value="">Error loading guides</option>';
            });
    }
    
    // Update package prices based on selected pickup time
    window.updatePackagePricesForTime = function(day, index, selectedTimeValue) {
        if (!selectedTimeValue) return;
        
        // Extract hour from time value (HH:MM:SS format)
        const selectedHour = parseInt(selectedTimeValue.split(':')[0]);
        
        // Get guide data
        const guideSelect = document.getElementById('day' + day + '_guide_' + index);
        if (!guideSelect || !guideSelect.selectedOptions[0]) return;
        
        const selectedOption = guideSelect.selectedOptions[0];
        const nightStartTime = selectedOption.dataset.nightStartTime;
        const nightEndTime = selectedOption.dataset.nightEndTime;
        
        // Determine if selected time is in night range
        let nightStart = null;
        let nightEnd = null;
        
        if (nightStartTime && nightStartTime !== '00:00:00') {
            nightStart = parseInt(nightStartTime.split(':')[0]);
        }
        if (nightEndTime && nightEndTime !== '00:00:00') {
            nightEnd = parseInt(nightEndTime.split(':')[0]) - 1; // Subtract 1 hour from end time
        }
        
        const isNightTime = nightStart !== null && nightEnd !== null && 
                           isTimeInNightRange(selectedHour, nightStart, nightEnd);
        
        // Update package prices
        updatePackagePrices(day, index, isNightTime);
    }
    
    // Update package dropdown with calculated prices
    window.updatePackagePrices = function(day, index, isNightTime) {
        const packageSelect = document.getElementById('day' + day + '_guide_' + index + '_package');
        const guideSelect = document.getElementById('day' + day + '_guide_' + index);
        
        if (!packageSelect || !guideSelect || !guideSelect.selectedOptions[0]) return;
        
        const selectedOption = guideSelect.selectedOptions[0];
        const dayRate = parseFloat(selectedOption.dataset.dayRate) || 0;
        const nightSurcharge = parseFloat(selectedOption.dataset.nightSurcharge) || 0;
        const hourlyPrice = parseFloat(selectedOption.dataset.hourlyPrice) || 0;
        const twoHourPrice = parseFloat(selectedOption.dataset.twoHourPrice) || 0;
        const fourHourPrice = parseFloat(selectedOption.dataset.fourHourPrice) || 0;
        const sixHourPrice = parseFloat(selectedOption.dataset.sixHourPrice) || 0;
        const eightHourPrice = parseFloat(selectedOption.dataset.eightHourPrice) || 0;
        const tenHourPrice = parseFloat(selectedOption.dataset.tenHourPrice) || 0;
        const twelveHourPrice = parseFloat(selectedOption.dataset.twelveHourPrice) || 0;
        
        // Calculate base rate (day rate or night surcharge)
        const baseRate = isNightTime ? nightSurcharge : dayRate;
        
        // Calculate total prices for each package
        const packages = [
            { value: '1_hour', label: '1 Hour Package', price: baseRate + hourlyPrice, hours: 1, basePrice: hourlyPrice },
            { value: '2_hour', label: '2 Hour Package', price: baseRate + twoHourPrice, hours: 2, basePrice: twoHourPrice },
            { value: '4_hour', label: '4 Hour Package', price: baseRate + fourHourPrice, hours: 4, basePrice: fourHourPrice },
            { value: '6_hour', label: '6 Hour Package', price: baseRate + sixHourPrice, hours: 6, basePrice: sixHourPrice },
            { value: '8_hour', label: '8 Hour Package', price: baseRate + eightHourPrice, hours: 8, basePrice: eightHourPrice },
            { value: '10_hour', label: '10 Hour Package', price: baseRate + tenHourPrice, hours: 10, basePrice: tenHourPrice },
            { value: '12_hour', label: '12 Hour Package', price: baseRate + twelveHourPrice, hours: 12, basePrice: twelveHourPrice }
        ];
        
        // Build package options HTML
        let optionsHTML = '<option value="">Select Duration Package</option>';
        
        packages.forEach(pkg => {
            const priceDisplay = pkg.price > 0 ? ` - ${pkg.price.toFixed(2)} SGD` : '';
            optionsHTML += `<option value="${pkg.value}" data-price="${pkg.price}" data-hours="${pkg.hours}" data-base-price="${pkg.basePrice}">${pkg.label}${priceDisplay}</option>`;
        });
        
        packageSelect.innerHTML = optionsHTML;
        
        console.log('Updated package prices:', isNightTime ? 'Night Time' : 'Day Time', packages);
    }
    
    // Function to update guide pricing when package is selected
    window.updateGuidePricing = function(day, index) {
        console.log(`updateGuidePricing called for Day ${day}, Index ${index}`);
        
        const packageSelect = document.getElementById(`day${day}_guide_${index}_package`);
        const guideSelect = document.getElementById(`day${day}_guide_${index}`);
        
        console.log('Package select found:', !!packageSelect);
        console.log('Guide select found:', !!guideSelect);
        console.log('Package value:', packageSelect?.value);
        console.log('Guide value:', guideSelect?.value);
        
        if (!packageSelect || !guideSelect || !packageSelect.value) {
            // Clear pricing if no package selected
            document.getElementById(`day${day}_guide_${index}_base_price`).value = '0';
            document.getElementById(`day${day}_guide_${index}_hours`).value = '0';
            document.getElementById(`day${day}_guide_${index}_surcharge`).value = '0';
            document.getElementById(`day${day}_guide_${index}_total_price`).value = '0';
            
            // Hide price display
            const priceDisplay = document.getElementById(`day${day}_guide_${index}_price_display`);
            if (priceDisplay) {
                priceDisplay.style.display = 'none';
            }
            return;
        }
        
        const selectedPackage = packageSelect.options[packageSelect.selectedIndex];
        const selectedGuide = guideSelect.options[guideSelect.selectedIndex];
        
        if (!selectedPackage || !selectedGuide) return;
        
        // Get package pricing data
        const packagePrice = parseFloat(selectedPackage.dataset.price) || 0;
        const hours = parseInt(selectedPackage.dataset.hours) || 0;
        const basePrice = parseFloat(selectedPackage.dataset.basePrice) || 0;
        // Store the total package price, not just the hourly rate
        const totalPackagePrice = packagePrice;
        
        console.log('Package pricing data:', {
            packagePrice: packagePrice,
            hours: hours,
            basePrice: basePrice,
            totalPackagePrice: totalPackagePrice
        });
        
        // Get guide data for surcharge calculation
        const dayRate = parseFloat(selectedGuide.dataset.dayRate) || 0;
        const nightSurcharge = parseFloat(selectedGuide.dataset.nightSurcharge) || 0;
        
        // Calculate surcharge based on pickup time
        const pickupTime = document.getElementById(`day${day}_guide_${index}_pickup_time`)?.value || '';
        let surcharge = 0;
        
        if (pickupTime) {
            const pickupHour = parseInt(pickupTime.split(':')[0]);
            const nightStartTime = selectedGuide.dataset.nightStartTime;
            const nightEndTime = selectedGuide.dataset.nightEndTime;
            
            if (nightStartTime && nightEndTime) {
                const nightStart = parseInt(nightStartTime.split(':')[0]);
                const nightEnd = parseInt(nightEndTime.split(':')[0]) - 1;
                
                const isNightTime = isTimeInNightRange(pickupHour, nightStart, nightEnd);
                surcharge = isNightTime ? nightSurcharge : 0;
            }
        }
        
        // Update hidden fields
        const basePriceField = document.getElementById(`day${day}_guide_${index}_base_price`);
        const hoursField = document.getElementById(`day${day}_guide_${index}_hours`);
        const surchargeField = document.getElementById(`day${day}_guide_${index}_surcharge`);
        const totalPriceField = document.getElementById(`day${day}_guide_${index}_total_price`);
        
        if (basePriceField) basePriceField.value = totalPackagePrice.toFixed(2);
        if (hoursField) hoursField.value = hours.toString();
        if (surchargeField) surchargeField.value = surcharge.toFixed(2);
        if (totalPriceField) totalPriceField.value = (totalPackagePrice + surcharge).toFixed(2);
        
        console.log('Hidden fields updated:', {
            basePriceField: basePriceField ? 'EXISTS' : 'MISSING',
            hoursField: hoursField ? 'EXISTS' : 'MISSING',
            surchargeField: surchargeField ? 'EXISTS' : 'MISSING',
            totalPriceField: totalPriceField ? 'EXISTS' : 'MISSING',
            basePriceValue: basePriceField?.value,
            hoursValue: hoursField?.value,
            surchargeValue: surchargeField?.value,
            totalPriceValue: totalPriceField?.value
        });
        
        // Update price display
        const priceDisplay = document.getElementById(`day${day}_guide_${index}_price_display`);
        if (priceDisplay) {
            priceDisplay.style.display = 'block';
            priceDisplay.querySelector('span').textContent = `$${(totalPackagePrice + surcharge).toFixed(2)}`;
        }
        
        console.log(`Guide pricing updated for Day ${day}, Index ${index}:`, {
            basePrice: basePrice.toFixed(2),
            hours: hours,
            surcharge: surcharge.toFixed(2),
            totalPackagePrice: totalPackagePrice.toFixed(2),
            finalTotalPrice: (totalPackagePrice + surcharge).toFixed(2)
        });
    }
    
    // Load guide details and setup pickup time
    window.loadGuideDetails = function(day, guideId, index = 1) {
        console.log('loadGuideDetails called:', day, guideId, index);
        
        if (!guideId) {
            // Clear package dropdown if no guide selected
            const packageSelect = document.getElementById('day' + day + '_guide_' + index + '_package');
            if (packageSelect) {
                packageSelect.innerHTML = '<option value="">Select Duration</option>';
            }
            return;
        }
        
        // Get night hours from selected guide
        const guideSelect = document.getElementById('day' + day + '_guide_' + index);
        if (!guideSelect) {
            console.error('Guide select not found:', 'day' + day + '_guide_' + index);
            return;
        }
        
        const selectedOption = guideSelect.options[guideSelect.selectedIndex];
        const nightStartTime = selectedOption.dataset.nightStartTime || null;
        const nightEndTime = selectedOption.dataset.nightEndTime || null;
        
        console.log('Night hours:', nightStartTime, 'to', nightEndTime);
        
        // Setup pickup time dropdown with night hours highlighting
        setupPickupTimeDropdown(day, index, nightStartTime, nightEndTime);
        
        // Initialize dropdown functionality
        initializePickupTimeDropdown(day, index);
        
        // Initialize package options with day rates (default)
        updatePackagePrices(day, index, false);
    };
    
    // Setup pickup time dropdown with night hours highlighting
    function setupPickupTimeDropdown(day, index, nightStartTime, nightEndTime) {
        const timeOptionsContainer = document.getElementById('day' + day + '_guide_' + index + '_pickup_time_options');
        if (!timeOptionsContainer) {
            console.error('Time options container not found:', 'day' + day + '_guide_' + index + '_pickup_time_options');
            return;
        }
        
        timeOptionsContainer.innerHTML = '';
        
        // Parse night hours from HH:MM:SS format
        let nightStart = null;
        let nightEnd = null;
        let nightEndDisplay = null; // For display purposes
        
        if (nightStartTime && nightStartTime !== '00:00:00') {
            nightStart = parseInt(nightStartTime.split(':')[0]);
        }
        if (nightEndTime && nightEndTime !== '00:00:00') {
            const originalNightEnd = parseInt(nightEndTime.split(':')[0]);
            nightEnd = originalNightEnd - 1; // Subtract 1 hour from end time for logic
            nightEndDisplay = nightEnd; // Use the adjusted time for display too
        }
        
        // Add night hours info at top if night hours exist
        if (nightStart !== null && nightEndDisplay !== null && nightEndDisplay >= 0) {
            const nightInfo = document.createElement('div');
            nightInfo.className = 'alert alert-warning py-2 mb-2';
            nightInfo.innerHTML = `
                <i class="ri-moon-line me-1"></i>
                <strong>Night Hours:</strong> ${formatTo12Hour(nightStart)} - ${formatTo12Hour(nightEndDisplay)}
                <br><small>Night surcharge applies during these hours</small>
            `;
            timeOptionsContainer.appendChild(nightInfo);
        }
        
        // Generate all hours from 12:00 AM to 11:00 PM (24 hours with 1 hour intervals)
        for (let hour = 0; hour < 24; hour++) {
            const time12Hour = formatTo12Hour(hour);
            const time24Hour = hour.toString().padStart(2, '0') + ':00:00';
            
            // Check if this hour is in night range
            const isNightHour = nightStart !== null && nightEnd !== null && 
                               isTimeInNightRange(hour, nightStart, nightEnd);
            
            const timeButton = document.createElement('div');
            timeButton.className = `pickup-time-option p-2 mb-1 border rounded ${isNightHour ? 'bg-danger text-white' : 'bg-light'}`;
            timeButton.style.cursor = 'pointer';
            
            timeButton.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="ri-${isNightHour ? 'moon' : 'sun'}-line me-2"></i>
                    <div>
                        <div class="fw-bold">${time12Hour}</div>
                        <small class="opacity-75">${isNightHour ? 'Night surcharge applies' : 'Standard rate applies'}</small>
                    </div>
                </div>
            `;
            
            // Add click event listener
            timeButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                selectPickupTime(day, index, time24Hour, time12Hour);
            });
            
            timeOptionsContainer.appendChild(timeButton);
        }
        
        // Prevent dropdown from closing when clicking inside
        const dropdown = document.getElementById('day' + day + '_guide_' + index + '_pickup_time_dropdown');
        if (dropdown) {
            dropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    }
    
    // Check if time is in night range
    function isTimeInNightRange(hour, nightStart, nightEnd) {
        if (nightStart === null || nightEnd === null || nightEnd < 0) {
            return false;
        }
        
        if (nightEnd >= nightStart) {
            // Same day range (e.g., 12:00 to 14:00 or 22:00 to 23:00)
            return hour >= nightStart && hour <= nightEnd;
        } else {
            // Cross midnight range (e.g., 22:00 to 02:00)
            return hour >= nightStart || hour <= nightEnd;
        }
    }
    
    // Format hour to 12-hour format
    function formatTo12Hour(hour) {
        if (hour === 0) return '12:00 AM';
        if (hour < 12) return hour.toString().padStart(2, '0') + ':00 AM';
        if (hour === 12) return '12:00 PM';
        return (hour - 12).toString().padStart(2, '0') + ':00 PM';
    }
    
    // Select pickup time
    function selectPickupTime(day, index, timeValue, timeDisplay) {
        const hiddenInput = document.getElementById('day' + day + '_guide_' + index + '_pickup_time');
        const button = document.getElementById('day' + day + '_guide_' + index + '_pickup_time_btn');
        
        if (hiddenInput) {
            hiddenInput.value = timeValue;
        }
        if (button) {
            button.textContent = timeDisplay;
            button.setAttribute('aria-expanded', 'false');
        }
        
        // Close dropdown
        const dropdownMenu = document.getElementById('day' + day + '_guide_' + index + '_pickup_time_dropdown');
        if (dropdownMenu) {
            dropdownMenu.classList.remove('show');
        }
        
        // Remove backdrop if exists
        const backdrop = document.querySelector('.dropdown-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
        
        // Update package prices based on selected time
        updatePackagePricesForTime(day, index, timeValue);
        
        console.log('Selected pickup time:', timeDisplay, 'Value:', timeValue);
    }
    
    // Initialize pickup time dropdown functionality
    function initializePickupTimeDropdown(day, index) {
        const button = document.getElementById('day' + day + '_guide_' + index + '_pickup_time_btn');
        const dropdown = document.getElementById('day' + day + '_guide_' + index + '_pickup_time_dropdown');
        
        if (!button || !dropdown) {
            console.error('Pickup time elements not found:', day, index);
            return;
        }
        
        // Remove any existing click listeners
        button.removeEventListener('click', handleDropdownToggle);
        
        // Add click handler for dropdown button
        function handleDropdownToggle(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close all other open dropdowns
            document.querySelectorAll('.pickup-time-dropdown.show').forEach(dd => {
                if (dd !== dropdown) {
                    dd.classList.remove('show');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('show');
            button.setAttribute('aria-expanded', dropdown.classList.contains('show'));
        }
        
        button.addEventListener('click', handleDropdownToggle);
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!button.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('show');
                button.setAttribute('aria-expanded', 'false');
            }
        });
    }
     
    window.addGuideService = function(day) {
        showNotification(`Tour guide service added for Day ${day}`, 'success');
        console.log('Add guide service for day', day);
        // Future implementation: Add dynamic form for tour guides
    };
     
    window.addMoreGuides = function(day) {
        const container = document.getElementById(`day${day}_guides_container`);
        const existingGuides = container.querySelectorAll('.guide-item');
        const newIndex = existingGuides.length + 1;
        
        const newGuideHTML = `
            <div class="card border-info shadow-sm guide-item mb-3" data-guide-index="${newIndex}">
                <div class="card-header bg-info text-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <span class="service-icon me-3">
                                <i class="ri-user-star-line fs-4"></i>
                            </span>
                            <div>
                                <h6 class="mb-0 fw-bold">Tour Guide Booking #${newIndex}</h6>
                                <small class="opacity-75">Professional guide services</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="removeGuide(this, ${day}, ${newIndex})">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body bg-white">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Select Guide</label>
                            <select class="form-select guide-select" name="day${day}_guide_${newIndex}" id="day${day}_guide_${newIndex}" onchange="loadGuideDetails(${day}, this.value, ${newIndex})">
                                <option value="">Search Guide</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Select Guests</label>
                            <div class="guest-selector">
                                <div class="guest-display p-2 border rounded bg-light">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="guest-info">
                                            <span id="day${day}_guide_${newIndex}_guest_summary" class="text-muted small">
                                                1 adults (1 male, 0 female), 0 children -0 infants
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openGuestSelector('day${day}_guide_${newIndex}')">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                    </div>
                                    <div class="guest-badges mt-1">
                                        <span class="badge bg-primary">4</span>
                                        <span class="badge bg-success">0</span>
                                        <span class="badge bg-warning text-dark">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                                                 <div class="col-md-3">
                             <label class="form-label fw-semibold">Pickup Time</label>
                             <div class="dropdown">
                                 <button class="form-control dropdown-toggle text-start" type="button" id="day${day}_guide_${newIndex}_pickup_time_btn" data-bs-toggle="dropdown" aria-expanded="false">
                                     Select Pick-up Time
                                 </button>
                                 <div class="dropdown-menu p-3 pickup-time-dropdown" id="day${day}_guide_${newIndex}_pickup_time_dropdown" style="width: 300px; max-height: 400px; overflow-y: auto;">
                                     <h6 class="dropdown-header">Select Pick-up Time</h6>
                                     <div id="day${day}_guide_${newIndex}_pickup_time_options">
                                         <p class="text-muted text-center p-3">Please select a guide first</p>
                                     </div>
                                 </div>
                                 <input type="hidden" name="day${day}_guide_${newIndex}_pickup_time" id="day${day}_guide_${newIndex}_pickup_time">
                             </div>
                         </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Select Package</label>
                            <select class="form-select" name="day${day}_guide_${newIndex}_package" id="day${day}_guide_${newIndex}_package" onchange="updateGuidePricing(${day}, ${newIndex})">
                                <option value="">Select Duration</option>
                            </select>
                            <div id="day${day}_guide_${newIndex}_price_display" class="text-success small mt-1" style="display: none;">
                                Price: <span class="fw-bold">$0.00</span>
                            </div>
                            <!-- Hidden fields for pricing -->
                            <input type="hidden" id="day${day}_guide_${newIndex}_base_price" name="day${day}_guide_${newIndex}_base_price" value="0">
                            <input type="hidden" id="day${day}_guide_${newIndex}_hours" name="day${day}_guide_${newIndex}_hours" value="0">
                            <input type="hidden" id="day${day}_guide_${newIndex}_surcharge" name="day${day}_guide_${newIndex}_surcharge" value="0">
                            <input type="hidden" id="day${day}_guide_${newIndex}_total_price" name="day${day}_guide_${newIndex}_total_price" value="0">
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', newGuideHTML);
        
        // Load guides for the new dropdown
        const newSelect = document.getElementById(`day${day}_guide_${newIndex}`);
        if (newSelect) {
            loadGuidesDropdown(newSelect);
        }
        
        // Update guest summary for the new guide with current main guest selection
        const mainMale = parseInt(document.getElementById('male')?.value) || 0;
        const mainFemale = parseInt(document.getElementById('female')?.value) || 0;
        const mainChildren = parseInt(document.getElementById('children')?.value) || 0;
        const mainInfants = parseInt(document.getElementById('infants')?.value) || 0;
        
        const summaryElement = document.getElementById(`day${day}_guide_${newIndex}_guest_summary`);
        if (summaryElement) {
            const adults = mainMale + mainFemale;
            summaryElement.textContent = `${adults} adults (${mainMale} male, ${mainFemale} female), ${mainChildren} children -${mainInfants} infants`;
        }
        
        showNotification(`Tour Guide Booking #${newIndex} added for Day ${day}`, 'success');
    };
    
    window.removeGuide = function(button, day, index) {
        const guideItem = button.closest('.guide-item');
        guideItem.remove();
        showNotification(`Tour Guide Booking #${index} removed from Day ${day}`, 'info');
     };
     
     window.addRestaurantService = function(day) {
         showNotification(`Restaurant service added for Day ${day}`, 'success');
         console.log('Add restaurant service for day', day);
         // Future implementation: Add dynamic form for restaurants
     };
     
     window.addTransportService = function(day) {
         showNotification(`Transport service added for Day ${day}`, 'success');
         console.log('Add transport service for day', day);
         // Future implementation: Add dynamic form for transport
     };
     
     window.addMoreTransports = function(day) {
         const container = document.getElementById(`day${day}_transports_container`);
         const existingTransports = container.querySelectorAll('.transport-item');
         const newIndex = existingTransports.length + 1;
         
         const newTransportHTML = `
             <div class="card border-warning shadow-sm transport-item mb-3" data-transport-index="${newIndex}">
                 <div class="card-header bg-warning text-white">
                     <div class="d-flex align-items-center justify-content-between">
                         <div class="d-flex align-items-center">
                             <span class="service-icon me-3">
                                 <i class="ri-car-line fs-4"></i>
                             </span>
                             <div>
                                 <h6 class="mb-0 fw-bold">Transport Booking #${newIndex}</h6>
                                 <small class="opacity-75">Configure transport service</small>
                             </div>
                         </div>
                         <button type="button" class="btn btn-sm btn-outline-light" onclick="removeTransport(this, ${day}, ${newIndex})">
                             <i class="ri-close-line"></i>
                         </button>
                     </div>
                 </div>
                 <div class="card-body">
                     <div class="d-flex justify-content-between align-items-center mb-3">
                         <div class="d-flex gap-2">
                             <span class="badge bg-danger">Point To Point</span>
                             <span class="badge bg-primary">Hourly</span>
                         </div>
                         <div class="d-flex gap-2">
                             <input type="date" class="form-control" value="${getCurrentDate()}" name="day${day}_transport_${newIndex}_date">
                         </div>
                     </div>
                     
                     <div class="row g-4 align-items-end">
                         <div class="col-md-3">
                             <div class="form-group">
                                 <label class="form-label fw-semibold text-muted mb-2">
                                     <i class="ri-map-pin-line text-success me-2"></i>Pick Up Location
                                 </label>
                                 <div class="position-relative">
                                     <select class="form-select pickup-zone-select border-2" name="day${day}_transport_${newIndex}_pickup_zone_id" style="padding-left: 45px;">
                                         <option value="">Select pickup zone</option>
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
                                     <select class="form-select dropoff-zone-select border-2" name="day${day}_transport_${newIndex}_dropoff_zone_id" disabled style="padding-left: 45px; padding-right: 45px;">
                                         <option value="">Select pickup zone first</option>
                                     </select>
                                     <i class="ri-map-pin-fill position-absolute text-danger" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                     <button type="button" class="btn btn-sm position-absolute" style="right: 8px; top: 50%; transform: translateY(-50%); z-index: 5; border: none; background: none;" onclick="clearDropoffZone(${day}, 'transport_${newIndex}')">
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
                                     <select class="form-select border-2" name="day${day}_transport_${newIndex}_pickup_time" style="padding-left: 45px;">
                                         <option value="">Select The Time</option>
                                         ${generateTimeOptions()}
                                     </select>
                                     <i class="ri-time-fill position-absolute text-warning" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-2">
                             <div class="form-group">
                                 <label class="form-label fw-semibold text-muted mb-2">
                                     <i class="ri-calendar-line text-primary me-2"></i>Transport Date
                                 </label>
                                 <div class="position-relative">
                                     <input type="date" class="form-control border-2" name="day${day}_transport_${newIndex}_date" value="${getCurrentDate()}" style="padding-left: 45px;">
                                     <i class="ri-calendar-fill position-absolute text-primary" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 5;"></i>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-2">
                             <button type="button" class="btn btn-primary w-100 py-2" onclick="searchVehicles(${day}, 'transport_${newIndex}')" id="day${day}_transport_${newIndex}_search_btn" disabled>
                                 <i class="ri-search-line me-2"></i>Search Vehicles
                             </button>
                         </div>
                     </div>
                     
                     <!-- Vehicle Results Section (Hidden Initially) -->
                     <div class="row mt-4" id="day${day}_transport_${newIndex}_vehicle_results" style="display: none;">
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
                         <div class="col-md-6">
                             <label class="form-label fw-semibold">Vehicle</label>
                             <select class="form-select vehicle-select" name="day${day}_transport_${newIndex}_vehicle_id" onchange="updateVehicleDetails(${day}, 'transport_${newIndex}')">
                                 <option value="">Choose vehicle</option>
                             </select>
                         </div>
                         <div class="col-md-6">
                             <label class="form-label fw-semibold">Service Type</label>
                             <select class="form-select" name="day${day}_transport_${newIndex}_service_type" onchange="updatePricing(${day}, 'transport_${newIndex}')">
                                 <option value="">Select service type</option>
                                 <option value="Shared">Shared</option>
                                 <option value="Private">Private</option>
                             </select>
                         </div>
                         <div class="col-12 mt-3">
                             <div id="day${day}_transport_${newIndex}_price_display" class="alert alert-success" style="display: none;">
                                 <div class="d-flex align-items-center">
                                     <i class="ri-money-dollar-circle-line me-2 fs-4"></i>
                                     <div>
                                         <strong>Price Information</strong>
                                         <div class="small">Select a vehicle and service type to see pricing</div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                         <div class="col-12 mt-3">
                             <button type="button" class="btn btn-success w-100 py-2" onclick="addMoreTransports(${day})">
                                 <i class="ri-add-line me-2"></i>Add More Vehicles
                             </button>
                         </div>
                     </div>
                 </div>
             </div>
         `;
         
         container.insertAdjacentHTML('beforeend', newTransportHTML);
         
         // Load zones for the new transport
         const newPickupSelect = container.querySelector(`[name="day${day}_transport_${newIndex}_pickup_zone_id"]`);
         if (newPickupSelect) {
             loadZonesForTransport(newPickupSelect);
         }
         
         showNotification(`Transport Booking #${newIndex} added for Day ${day}`, 'success');
     };
     
     window.removeTransport = function(button, day, index) {
         const transportItem = button.closest('.transport-item');
         transportItem.remove();
         showNotification(`Transport Booking #${index} removed from Day ${day}`, 'info');
     };
     
     // Entry Port Functions
     window.addMoreEntryPorts = function(day) {
         console.log('addMoreEntryPorts called for day:', day);
         
         // Find the service card container for entry ports (contains primary/blue card)
         const dayContainer = document.getElementById(`day${day}`);
         if (!dayContainer) {
             console.error('Day container not found for day', day);
             return;
         }
         
         // Find the service card that contains the primary border card (entry port)
         const serviceCards = dayContainer.querySelectorAll('.service-card');
         let entryPortServiceCard = null;
         
         serviceCards.forEach(card => {
             if (card.querySelector('.card.border-primary.shadow-sm')) {
                 entryPortServiceCard = card;
             }
         });
         
                 if (!entryPortServiceCard) {
            showNotification(`Entry port service card not found for Day ${day}. Entry ports are only available on the first day of the tour.`, 'warning');
            console.error('Entry port service card not found for day', day);
            return;
        }
         
                 // Find existing entry port items container (it's outside the service card)
        let container = entryPortServiceCard.parentElement.querySelector('.entry-ports-container');
        if (!container) {
            console.error('Entry ports container not found for day', day);
            return;
        }
         
         const existingPorts = container.querySelectorAll('.entry-port-item');
         const newIndex = existingPorts.length + 1;
         
                 const newPortHTML = `
            <div class="card border-primary shadow-sm entry-port-item mb-3" data-entry-port-index="${newIndex}" style="border-color: #b8daff !important;">
                <div class="card-header text-primary" style="background-color: #e7f3ff; border-bottom: 1px solid #b8daff;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <span class="service-icon me-3" style="background-color: rgba(0,123,255,0.1); color: #0c63e4;">
                                <i class="ri-login-circle-line fs-4"></i>
                            </span>
                            <div>
                                <h6 class="mb-0 fw-bold text-primary">Available Vehicles #${newIndex}</h6>
                                <small class="text-muted">Arrival transportation option</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="removeEntryPort(this, ${day}, ${newIndex})" title="Remove this vehicle option">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body bg-white">
                    <div class="alert alert-light mb-3" style="background-color: #f8f9fa; border-color: #e9ecef; color: #6c757d;">
                        <div class="d-flex align-items-center">
                            <i class="ri-car-line me-2 fs-4 text-primary"></i>
                            <div>
                                <strong class="text-dark">Available Vehicles #${newIndex}</strong>
                                <div class="small text-muted">Select your preferred vehicle and service type below</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vehicle</label>
                            <select class="form-select vehicle-select" name="day${day}_entry_${newIndex}_vehicle_id" onchange="updateVehicleDetails(${day}, 'entry_${newIndex}')">
                                <option value="">Choose vehicle</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Service Type</label>
                            <select class="form-select service-type-select" name="day${day}_entry_${newIndex}_service_type" onchange="updatePricing(${day}, 'entry_${newIndex}')">
                                <option value="">Select service type</option>
                                <option value="Shared">Shared</option>
                                <option value="Private">Private</option>
                            </select>
                        </div>
                        <div class="col-12 mt-3">
                            <div id="day${day}_entry_${newIndex}_price_display" class="alert alert-success" style="display: none;">
                                <div class="d-flex align-items-center">
                                    <i class="ri-money-dollar-circle-line me-2 fs-4"></i>
                                    <div>
                                        <strong>Price Information</strong>
                                        <div class="small">Select a vehicle and service type to see pricing</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
         
                 container.insertAdjacentHTML('beforeend', newPortHTML);
        
        // Load vehicles for the new dropdown (similar to main vehicle dropdown)
        const newVehicleSelect = container.querySelector(`[name="day${day}_entry_${newIndex}_vehicle_id"]`);
        if (newVehicleSelect) {
            // Copy options from the main vehicle dropdown if available
            const mainVehicleSelect = dayContainer.querySelector(`[name="day${day}_entry_vehicle_id"]`);
            if (mainVehicleSelect && mainVehicleSelect.options.length > 1) {
                // Clear and copy options
                newVehicleSelect.innerHTML = '';
                for (let i = 0; i < mainVehicleSelect.options.length; i++) {
                    const option = mainVehicleSelect.options[i].cloneNode(true);
                    newVehicleSelect.appendChild(option);
                }
            }
        }
        
        showNotification(`Entry Port Vehicle #${newIndex} added for Day ${day}`, 'success');
     };
     
     window.removeEntryPort = function(button, day, index) {
         const portItem = button.closest('.entry-port-item');
         portItem.remove();
         showNotification(`Entry Port Vehicle #${index} removed from Day ${day}`, 'info');
     };

     
         // Exit Port Functions
    window.addMoreExitPorts = function(day) {
        console.log('addMoreExitPorts called for day:', day);
        
        // Find the day container
        const dayContainer = document.getElementById(`day${day}`);
        if (!dayContainer) {
            console.error('Day container not found for day', day);
            return;
        }
        
        // Find the service card that contains the danger border card (exit port)
        const serviceCards = dayContainer.querySelectorAll('.service-card');
        let exitPortServiceCard = null;
        
        serviceCards.forEach(card => {
            if (card.querySelector('.card.border-danger.shadow-sm')) {
                exitPortServiceCard = card;
            }
        });
        
        if (!exitPortServiceCard) {
            showNotification(`Exit port services are only available on the last day of the tour. This is Day ${day}.`, 'warning');
            console.error('Exit port service card not found for day', day);
            return;
        }
         
                 // Find existing exit port items container (it's outside the service card)
        console.log('Looking for exit ports container for day', day);
        console.log('Exit port service card:', exitPortServiceCard);
        console.log('Service card parent:', exitPortServiceCard.parentElement);
        
        // Try multiple approaches to find the container
        let container = exitPortServiceCard.parentElement.querySelector('.exit-ports-container');
        
        if (!container) {
            // Try looking in the day container directly
            container = dayContainer.querySelector('.exit-ports-container');
            console.log('Trying day container approach:', container);
        }
        
        if (!container) {
            // Try looking in all service cards' parents
            const allServiceCards = dayContainer.querySelectorAll('.service-card');
            allServiceCards.forEach(card => {
                if (!container) {
                    const potentialContainer = card.parentElement.querySelector('.exit-ports-container');
                    if (potentialContainer) {
                        container = potentialContainer;
                        console.log('Found container via service card parent search:', container);
                    }
                }
            });
        }
        
        if (!container) {
            console.error('Exit ports container not found for day', day);
            console.log('Available elements in day container:', dayContainer.innerHTML);
            showNotification(`Exit ports container not found for Day ${day}. Please refresh the page and try again.`, 'error');
            return;
        }
        
        console.log('Successfully found exit ports container:', container);
         
         const existingPorts = container.querySelectorAll('.exit-port-item');
         const newIndex = existingPorts.length + 1;
         
                 const newPortHTML = `
            <div class="card border-danger shadow-sm exit-port-item mb-3" data-exit-port-index="${newIndex}" style="border-color: #f5c2c7 !important;">
                <div class="card-header text-danger" style="background-color: #f8d7da; border-bottom: 1px solid #f5c2c7;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <span class="service-icon me-3" style="background-color: rgba(220,53,69,0.1); color: #dc3545;">
                                <i class="ri-logout-circle-line fs-4"></i>
                            </span>
                            <div>
                                <h6 class="mb-0 fw-bold text-danger">Available Vehicles #${newIndex}</h6>
                                <small class="text-muted">Departure transportation option</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeExitPort(this, ${day}, ${newIndex})" title="Remove this vehicle option">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body bg-white">
                    <div class="alert alert-light mb-3" style="background-color: #f8f9fa; border-color: #e9ecef; color: #6c757d;">
                        <div class="d-flex align-items-center">
                            <i class="ri-car-line me-2 fs-4 text-danger"></i>
                            <div>
                                <strong class="text-dark">Available Vehicles #${newIndex}</strong>
                                <div class="small text-muted">Select your preferred vehicle and service type below</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vehicle</label>
                            <select class="form-select vehicle-select" name="day${day}_exit_${newIndex}_vehicle_id" onchange="updateVehicleDetails(${day}, 'exit_${newIndex}')">
                                <option value="">Choose vehicle</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Service Type</label>
                            <select class="form-select service-type-select" name="day${day}_exit_${newIndex}_service_type" onchange="updatePricing(${day}, 'exit_${newIndex}')">
                                <option value="">Select service type</option>
                                <option value="Shared">Shared</option>
                                <option value="Private">Private</option>
                            </select>
                        </div>
                        <div class="col-12 mt-3">
                            <div id="day${day}_exit_${newIndex}_price_display" class="alert alert-success" style="display: none;">
                                <div class="d-flex align-items-center">
                                    <i class="ri-money-dollar-circle-line me-2 fs-4"></i>
                                    <div>
                                        <strong>Price Information</strong>
                                        <div class="small">Select a vehicle and service type to see pricing</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
         
                 container.insertAdjacentHTML('beforeend', newPortHTML);
        
        // Load vehicles for the new dropdown (similar to main vehicle dropdown)
        const newVehicleSelect = container.querySelector(`[name="day${day}_exit_${newIndex}_vehicle_id"]`);
        if (newVehicleSelect) {
            // Copy options from the main vehicle dropdown if available
            const mainVehicleSelect = dayContainer.querySelector(`[name="day${day}_exit_vehicle_id"]`);
            if (mainVehicleSelect && mainVehicleSelect.options.length > 1) {
                // Clear and copy options
                newVehicleSelect.innerHTML = '';
                for (let i = 0; i < mainVehicleSelect.options.length; i++) {
                    const option = mainVehicleSelect.options[i].cloneNode(true);
                    newVehicleSelect.appendChild(option);
                }
            }
        }
        
        showNotification(`Exit Port Vehicle #${newIndex} added for Day ${day}`, 'success');
     };
     
     window.removeExitPort = function(button, day, index) {
         const portItem = button.closest('.exit-port-item');
         portItem.remove();
         showNotification(`Exit Port Vehicle #${index} removed from Day ${day}`, 'info');
     };

     
     function generateTimeOptions() {
         let options = '';
         for (let hour = 0; hour < 24; hour++) {
             const hourStr = hour === 0 ? '12' : (hour > 12 ? hour - 12 : hour);
             const ampm = hour < 12 ? 'AM' : 'PM';
             const formattedTime = `${hourStr.toString().padStart(2, '0')}:00 ${ampm}`;
             options += `<option value="${formattedTime}">${formattedTime}</option>`;
         }
         return options;
     }
     
     function getCurrentDate() {
         const today = new Date();
         const year = today.getFullYear();
         const month = (today.getMonth() + 1).toString().padStart(2, '0');
         const day = today.getDate().toString().padStart(2, '0');
         return `${year}-${month}-${day}`;
     }
     
     // Guest selector functionality
         window.openGuestSelector = function(serviceId) {
        const modal = document.getElementById('guestSelectorModal');
        if (modal) {
            modal.setAttribute('data-service-id', serviceId);
            const modalInstance = new bootstrap.Modal(modal);
            modalInstance.show();
        } else {
            // Create modal if it doesn't exist
            createGuestSelectorModal(serviceId);
        }
    };

    function createGuestSelectorModal(serviceId) {
        // Get limits from main form
        const maxMale = parseInt(document.getElementById('male').value) || 0;
        const maxFemale = parseInt(document.getElementById('female').value) || 0;
        const maxChildren = parseInt(document.getElementById('children').value) || 0;
        const maxInfants = parseInt(document.getElementById('infants').value) || 0;
        const maxAdults = maxMale + maxFemale;
        
        const modalHTML = `
            <div class="modal fade" id="guestSelectorModal" tabindex="-1" aria-labelledby="guestSelectorModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="guestSelectorModalLabel">
                                <i class="ri-group-line me-2"></i>Select Guests for Service
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                        <!-- Service Limits Notice -->
                        <div class="alert alert-info mb-4">
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="alert-heading mb-2">
                                        <i class="ri-information-line me-2"></i>Available Guests (Based on main selection)
                                    </h6>
                                    <div class="d-flex gap-3 flex-wrap">
                                        <span class="badge bg-primary">Adults: ${maxAdults} (${maxMale}M + ${maxFemale}F)</span>
                                        <span class="badge bg-success">Children: ${maxChildren}</span>
                                        <span class="badge bg-warning text-dark">Infants: ${maxInfants}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                            <div class="row g-4">
                                <!-- Adults Section -->
                                <div class="col-md-6">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="ri-user-line me-2"></i>Adults (Max: ${maxAdults})</h6>
                                        </div>
                                        <div class="card-body">
                                            <!-- Male -->
                                            <div class="guest-counter mb-3">
                                                <label class="form-label fw-semibold text-primary">
                                                <i class="ri-user-3-line me-1"></i>Male (Max: ${maxMale})
                                                </label>
                                                <div class="d-flex align-items-center">
                                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="updateServiceGuest('male', -1)" ${maxMale === 0 ? 'disabled' : ''}>
                                                        <i class="ri-subtract-line"></i>
                                                    </button>
                                                    <span class="mx-3 fw-bold fs-5" id="serviceModalMale">0</span>
                                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="updateServiceGuest('male', 1)" ${maxMale === 0 ? 'disabled' : ''}>
                                                        <i class="ri-add-line"></i>
                                                    </button>
                                                </div>
                                            ${maxMale === 0 ? '<small class="text-muted">No male adults selected in main form</small>' : ''}
                                            </div>
                                            <!-- Female -->
                                            <div class="guest-counter">
                                                <label class="form-label fw-semibold text-danger">
                                                <i class="ri-user-4-line me-1"></i>Female (Max: ${maxFemale})
                                                </label>
                                                <div class="d-flex align-items-center">
                                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="updateServiceGuest('female', -1)" ${maxFemale === 0 ? 'disabled' : ''}>
                                                        <i class="ri-subtract-line"></i>
                                                    </button>
                                                    <span class="mx-3 fw-bold fs-5" id="serviceModalFemale">0</span>
                                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="updateServiceGuest('female', 1)" ${maxFemale === 0 ? 'disabled' : ''}>
                                                        <i class="ri-add-line"></i>
                                                    </button>
                                                </div>
                                            ${maxFemale === 0 ? '<small class="text-muted">No female adults selected in main form</small>' : ''}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Children & Infants Section -->
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0"><i class="ri-user-smile-line me-2"></i>Children & Infants</h6>
                                        </div>
                                        <div class="card-body">
                                            <!-- Children -->
                                            <div class="guest-counter mb-3">
                                                <label class="form-label fw-semibold text-success">
                                                <i class="ri-user-smile-line me-1"></i>Children (Max: ${maxChildren})
                                                    <small class="text-muted">(Ages 1-17)</small>
                                                </label>
                                                <div class="d-flex align-items-center">
                                                <button type="button" class="btn btn-outline-success btn-sm" onclick="updateServiceGuest('children', -1)" ${maxChildren === 0 ? 'disabled' : ''}>
                                                        <i class="ri-subtract-line"></i>
                                                    </button>
                                                    <span class="mx-3 fw-bold fs-5" id="serviceModalChildren">0</span>
                                                <button type="button" class="btn btn-outline-success btn-sm" onclick="updateServiceGuest('children', 1)" ${maxChildren === 0 ? 'disabled' : ''}>
                                                        <i class="ri-add-line"></i>
                                                    </button>
                                                </div>
                                            ${maxChildren === 0 ? '<small class="text-muted">No children selected in main form</small>' : ''}
                                            </div>
                                            <!-- Infants -->
                                            <div class="guest-counter">
                                                <label class="form-label fw-semibold text-warning">
                                                <i class="ri-user-heart-line me-1"></i>Infants (Max: ${maxInfants})
                                                    <small class="text-muted">(Under 1 year)</small>
                                                </label>
                                                <div class="d-flex align-items-center">
                                                <button type="button" class="btn btn-outline-warning btn-sm" onclick="updateServiceGuest('infants', -1)" ${maxInfants === 0 ? 'disabled' : ''}>
                                                        <i class="ri-subtract-line"></i>
                                                    </button>
                                                    <span class="mx-3 fw-bold fs-5" id="serviceModalInfants">0</span>
                                                <button type="button" class="btn btn-outline-warning btn-sm" onclick="updateServiceGuest('infants', 1)" ${maxInfants === 0 ? 'disabled' : ''}>
                                                        <i class="ri-add-line"></i>
                                                    </button>
                                                </div>
                                            ${maxInfants === 0 ? '<small class="text-muted">No infants selected in main form</small>' : ''}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" onclick="applyGuestSelection()">
                                <i class="ri-check-line me-1"></i>Apply Selection
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
         
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Initialize modal values from main form
        initializeModalGuestValues();
        
        // Show modal
        const modal = document.getElementById('guestSelectorModal');
        modal.setAttribute('data-service-id', serviceId);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();
    }
     
    function initializeModalGuestValues() {
         // Get values from main form
         const male = parseInt(document.getElementById('male').value) || 0;
         const female = parseInt(document.getElementById('female').value) || 0;
         const children = parseInt(document.getElementById('children').value) || 0;
         const infants = parseInt(document.getElementById('infants').value) || 0;
         
         // Set modal values
         document.getElementById('serviceModalMale').textContent = male;
         document.getElementById('serviceModalFemale').textContent = female;
         document.getElementById('serviceModalChildren').textContent = children;
         document.getElementById('serviceModalInfants').textContent = infants;
    }
     
    window.updateServiceGuest = function(type, change) {
         const element = document.getElementById('serviceModal' + type.charAt(0).toUpperCase() + type.slice(1));
         const currentValue = parseInt(element.textContent) || 0;
        
        // Get maximum allowed from main form
        let maxAllowed = 0;
        if (type === 'male') {
            maxAllowed = parseInt(document.getElementById('male').value) || 0;
        } else if (type === 'female') {
            maxAllowed = parseInt(document.getElementById('female').value) || 0;
        } else if (type === 'children') {
            maxAllowed = parseInt(document.getElementById('children').value) || 0;
        } else if (type === 'infants') {
            maxAllowed = parseInt(document.getElementById('infants').value) || 0;
        }
        
        // Calculate new value within bounds
        let newValue = currentValue + change;
        newValue = Math.max(0, Math.min(maxAllowed, newValue));
        
        // For adults, ensure at least 1 adult is selected in total
        if ((type === 'male' || type === 'female') && change < 0) {
            const maleEl = document.getElementById('serviceModalMale');
            const femaleEl = document.getElementById('serviceModalFemale');
            const maleCount = maleEl ? parseInt(maleEl.textContent) || 0 : 0;
            const femaleCount = femaleEl ? parseInt(femaleEl.textContent) || 0 : 0;
            
            const totalAdults = (type === 'male' ? newValue : maleCount) + (type === 'female' ? newValue : femaleCount);
            
            if (totalAdults < 1) {
                return; // Don't allow reducing to 0 adults
            }
        }
        
         element.textContent = newValue;
    };
     
    window.applyGuestSelection = function() {
         const serviceId = document.getElementById('guestSelectorModal').getAttribute('data-service-id');
         const male = parseInt(document.getElementById('serviceModalMale').textContent) || 0;
         const female = parseInt(document.getElementById('serviceModalFemale').textContent) || 0;
         const children = parseInt(document.getElementById('serviceModalChildren').textContent) || 0;
         const infants = parseInt(document.getElementById('serviceModalInfants').textContent) || 0;
         
         const adults = male + female;
         const total = adults + children + infants;
         
         // Update the service guest summary text
         const summaryElement = document.getElementById(serviceId + '_guest_summary');
         if (summaryElement) {
             summaryElement.textContent = `${adults} adults (${male} male), ${children} children -${infants} infants`;
         }
         
         // Update the badges for this specific service
         const serviceContainer = summaryElement.closest('.guest-display');
         if (serviceContainer) {
             const badges = serviceContainer.querySelectorAll('.guest-badges .badge');
             if (badges.length >= 3) {
                 badges[0].textContent = adults; // Total adults
                 badges[1].textContent = children; // Children
                 badges[2].textContent = infants; // Infants
             }
         }
         
         // Close modal
         const modal = bootstrap.Modal.getInstance(document.getElementById('guestSelectorModal'));
         modal.hide();
         
         // Update attraction pricing if this is an attraction service
         if (serviceId.includes('attraction')) {
             const dayMatch = serviceId.match(/day(\d+)_attraction_(\d+)/);
             if (dayMatch) {
                 const day = dayMatch[1];
                 const index = dayMatch[2];
                 updateAttractionPricing(day, index);
             }
         }
         
         showNotification(`Guest selection updated: ${total} total guests`, 'success');
    };
     
     // Notification helper function
    window.showNotification = function(message, type = 'info') {
         const alertClass = type === 'success' ? 'alert-success' : 
                           type === 'error' ? 'alert-danger' : 'alert-info';
         
         const notification = `
             <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                  style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
                 <i class="ri-information-line me-2"></i>${message}
                 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
             </div>
         `;
         
         document.body.insertAdjacentHTML('afterbegin', notification);
         
         // Auto-remove after 3 seconds
         setTimeout(() => {
             const alert = document.querySelector('.alert');
             if (alert) {
                 alert.remove();
             }
         }, 3000);
    }

     // Initialize main guest summary
    function updateMainGuestSummary() {
        const male = parseInt(document.getElementById('male').value) || 0;
        const female = parseInt(document.getElementById('female').value) || 0;
        const children = parseInt(document.getElementById('children').value) || 0;
        const infants = parseInt(document.getElementById('infants').value) || 0;
        const adults = male + female;
        
        const guestSummary = document.getElementById('mainGuestSummary');
        if (guestSummary) {
            guestSummary.textContent = `${adults} adults (${male} male, ${female} female), ${children} children - ${infants} infants`;
        }
        
        // Update badges
        const badgeContainer = guestSummary?.closest('.guest-display')?.querySelector('.guest-badges');
        if (badgeContainer) {
            const badges = badgeContainer.querySelectorAll('.badge');
            if (badges.length >= 3) {
                badges[0].textContent = adults; // Total adults
                badges[1].textContent = children; // Children
                badges[2].textContent = infants; // Infants
            }
        }
        
        // Refresh meal plans if a hotel is already selected
        const hotelSelect = document.getElementById('hotelSelect');
        if (hotelSelect && hotelSelect.value) {
            updateHotelDependentDropdowns(hotelSelect.value);
        }
    }
    // Initialize attraction pricing for existing selections
    function initializeAttractionPricing() {
        // Find all attraction containers and update pricing for each
        const attractionContainers = document.querySelectorAll('[id^="day"][id*="_attractions_container"]');
        attractionContainers.forEach(container => {
            const dayMatch = container.id.match(/day(\d+)_attractions_container/);
            if (dayMatch) {
                const day = dayMatch[1];
                const attractionItems = container.querySelectorAll('.attraction-item');
                attractionItems.forEach((item, index) => {
                    const attractionIndex = index + 1;
                    // Check if attraction and ticket are already selected
                    const attractionSelect = item.querySelector(`select[name="day${day}_attraction_${attractionIndex}"]`);
                    const ticketSelect = item.querySelector(`select[name="day${day}_attraction_${attractionIndex}_ticket"]`);
                    
                    if (attractionSelect && ticketSelect && attractionSelect.value && ticketSelect.value) {
                        // Both are selected, update pricing
                        setTimeout(() => {
                            updateAttractionPricing(day, attractionIndex);
                        }, 100); // Small delay to ensure DOM is ready
                    }
                });
            }
        });
    }
    
    // Manual function to refresh all attraction pricing (useful for debugging)
    window.refreshAttractionPricing = function() {
        console.log('Manually refreshing attraction pricing...');
        initializeAttractionPricing();
    };
    
    // Function to force update pricing for a specific attraction
    window.forceUpdateAttractionPricing = function(day, index) {
        console.log(`Force updating pricing for day ${day}, index ${index}`);
        updateAttractionPricing(day, index);
    };
    
    // Function to update all attraction pricing immediately
    window.updateAllAttractionPricing = function() {
        console.log('Updating all attraction pricing...');
        const attractionContainers = document.querySelectorAll('[id^="day"][id*="_attractions_container"]');
        attractionContainers.forEach(container => {
            const dayMatch = container.id.match(/day(\d+)_attractions_container/);
            if (dayMatch) {
                const day = dayMatch[1];
                const attractionItems = container.querySelectorAll('.attraction-item');
                attractionItems.forEach((item, index) => {
                    const attractionIndex = index + 1;
                    console.log(`Updating pricing for day ${day}, attraction ${attractionIndex}`);
                    updateAttractionPricing(day, attractionIndex);
                });
            }
        });
    };
    
    //Initialize
    updateMainGuestSummary();
    updateGuestSummary();
    updateAdultsCount();
    
    // Load zones when city is selected
    loadZonesForCity();
    
    // Setup new transportation handlers
    setupNewTransportationHandlers();
    
    // Setup search button listeners
    
    // Initialize attraction pricing after page loads
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            initializeAttractionPricing();
        }, 500); // Delay to ensure all elements are loaded
    });
    setupSearchButtonListeners();
 });

 // Zone and Vehicle Management Functions
 function loadZonesForCity() {
     const citySelect = document.getElementById('city');
     if (citySelect) {
         citySelect.addEventListener('change', function() {
             const selectedCity = this.value;
             if (selectedCity) {
                 fetchZonesForAllTransportSections(selectedCity);
             }
         });
     }
 }
 
 // Function to enable search button when pickup time changes
 function enableSearchButton(day, section) {
     const searchBtn = document.getElementById(`day${day}_${section}_search_btn`);
     if (searchBtn) {
         searchBtn.disabled = false;
         searchBtn.classList.remove('btn-secondary');
         searchBtn.classList.add('btn-primary');
     }
 }
 
 // Add event listeners to pickup time fields for entry port and transport sections
 function setupSearchButtonListeners() {
     // For each day, set up listeners for pickup time fields
     for (let day = 1; day <= 7; day++) {
         // Entry port pickup time
         const entryPickupTime = document.querySelector(`select[name="day${day}_entry_pickup_time"]`);
         const entrySearchBtn = document.getElementById(`day${day}_entry_search_btn`);
         
         // Transport pickup time
         const transportPickupTime = document.querySelector(`select[name="day${day}_transport_pickup_time"]`);
         const transportSearchBtn = document.getElementById(`day${day}_transport_search_btn`);
         
         // Add event listener for entry port pickup time
         if (entryPickupTime) {
             entryPickupTime.addEventListener('change', () => enableSearchButton(day, 'entry'));
         }
         
         // Add event listener for transport pickup time
         if (transportPickupTime) {
             transportPickupTime.addEventListener('change', () => enableSearchButton(day, 'transport'));
         }
         
         // Initially disable search buttons
         if (entrySearchBtn) {
             entrySearchBtn.disabled = true;
             entrySearchBtn.classList.remove('btn-primary');
             entrySearchBtn.classList.add('btn-secondary');
         }
         if (transportSearchBtn) {
             transportSearchBtn.disabled = true;
             transportSearchBtn.classList.remove('btn-primary');
             transportSearchBtn.classList.add('btn-secondary');
         }
     }
 }

 function fetchZonesForAllTransportSections(city) {
     console.log('Fetching zones for city:', city);
     fetch(`{{ route('fetch-zones-by-dmc') }}?city=${encodeURIComponent(city)}`)
         .then(response => response.json())
         .then(data => {
             console.log('Zones data received:', data);
             if (data.success && data.zones) {
                 // Store zones data globally for use in other functions
                 window.allZonesData = data.zones;
                 
                 // Update all pickup zone selects
                 const pickupZoneSelects = document.querySelectorAll('.pickup-zone-select');
                 console.log('Found pickup zone selects:', pickupZoneSelects.length);
                 
                 pickupZoneSelects.forEach((select, index) => {
                     console.log(`Updating pickup zone select ${index}:`, select.name);
                     select.innerHTML = '<option value="">Select pickup zone</option>';
                     data.zones.forEach(zone => {
                         select.innerHTML += `<option value="${zone.zone_id}">${zone.zone_name} (${zone.zone_type})</option>`;
                     });
                     select.disabled = false;
                 });
                 
                 // Update all dropoff zone selects with all zones initially
                 const dropoffZoneSelects = document.querySelectorAll('.dropoff-zone-select');
                 console.log('Found dropoff zone selects:', dropoffZoneSelects.length);
                 
                 dropoffZoneSelects.forEach((select, index) => {
                     console.log(`Updating dropoff zone select ${index}:`, select.name);
                     select.innerHTML = '<option value="">Select pickup zone first</option>';
                     data.zones.forEach(zone => {
                         select.innerHTML += `<option value="${zone.zone_id}">${zone.zone_name} (${zone.zone_type})</option>`;
                     });
                     select.disabled = true;
                     console.log(`Dropoff select ${index} has ${data.zones.length} zones but is disabled`);
                 });
                 
                const vehicleSelects = document.querySelectorAll('.vehicle-select');
                vehicleSelects.forEach(select => {
                    select.innerHTML = '<option value="">Select zones first</option>';
                    select.disabled = true;
                });
                 
                const serviceTypeSelects = document.querySelectorAll('.service-type-select');
                serviceTypeSelects.forEach(select => {
                    // Keep hardcoded options, just disable and reset selection
                    select.disabled = true;
                    select.value = "";
                });
             }
         })
         .catch(error => {
             console.error('Error fetching zones:', error);
         });
 }

 function loadDropoffZones(day, section) {
     const pickupZoneSelect = document.querySelector(`select[name="day${day}_${section}_pickup_zone_id"]`);
     const dropoffZoneSelect = document.querySelector(`select[name="day${day}_${section}_dropoff_zone_id"]`);
     
     if (!pickupZoneSelect || !dropoffZoneSelect) return;
     
     const pickupZoneId = pickupZoneSelect.value;
     
     if (pickupZoneId) {
         const citySelect = document.getElementById('city');
         const city = citySelect ? citySelect.value : '';
         
         fetch(`{{ route('fetch-zones-by-dmc') }}?city=${encodeURIComponent(city)}`)
             .then(response => response.json())
             .then(data => {
                 if (data.success && data.zones) {
                    dropoffZoneSelect.innerHTML = '<option value="">Select dropoff zone</option>';
                    // Show all zones except the selected pickup zone
                    data.zones.forEach(zone => {
                        if (zone.zone_id !== pickupZoneId) {
                            dropoffZoneSelect.innerHTML += `<option value="${zone.zone_id}">${zone.zone_name} (${zone.zone_type})</option>`;
                        }
                    });
                    dropoffZoneSelect.disabled = false;
                }
            })
             .catch(error => {
                 console.error('Error fetching dropoff zones:', error);
             });
     } else {
         dropoffZoneSelect.innerHTML = '<option value="">Select pickup zone first</option>';
         dropoffZoneSelect.disabled = true;
     }
     
     // Reset vehicle and service type selects
     const vehicleSelect = document.querySelector(`select[name="day${day}_${section}_vehicle_id"]`);
     const serviceTypeSelect = document.querySelector(`select[name="day${day}_${section}_service_type"]`);
     
     if (vehicleSelect) {
         vehicleSelect.innerHTML = '<option value="">Select zones first</option>';
         vehicleSelect.disabled = true;
     }
     
     if (serviceTypeSelect) {
         // Keep the hardcoded options, just disable and reset selection
         serviceTypeSelect.disabled = true;
         serviceTypeSelect.value = "";
         serviceTypeSelect.disabled = true;
     }
 }

 // New function to handle pickup zone changes with section-specific exclusion
 function handlePickupZoneChange(day, section) {
     console.log(`handlePickupZoneChange called for day ${day}, section ${section}`);
     const pickupZoneSelect = document.querySelector(`select[name="day${day}_${section}_pickup_zone_id"]`);
     const dropoffZoneSelect = document.querySelector(`select[name="day${day}_${section}_dropoff_zone_id"]`);
     
     console.log('Pickup zone select found:', !!pickupZoneSelect);
     console.log('Dropoff zone select found:', !!dropoffZoneSelect);
     
     if (!pickupZoneSelect || !dropoffZoneSelect) return;
     
     const pickupZoneId = pickupZoneSelect.value;
     console.log('Selected pickup zone ID:', pickupZoneId);
     
     if (pickupZoneId) {
         // Use the stored zones data instead of reading from dropdown
         const allZones = window.allZonesData || [];
         console.log('Using stored zones data:', allZones.length, 'zones');
         console.log('Zones to exclude:', pickupZoneId);
         
         // Update dropoff select with all zones except the selected pickup zone
         dropoffZoneSelect.innerHTML = '<option value="">Select dropoff zone</option>';
         allZones.forEach(zone => {
             if (zone.zone_id != pickupZoneId) {
                 dropoffZoneSelect.innerHTML += `<option value="${zone.zone_id}">${zone.zone_name} (${zone.zone_type})</option>`;
             }
         });
         
         dropoffZoneSelect.disabled = false;
         console.log('Dropoff select enabled and updated with', allZones.length - 1, 'zones');
     } else {
         dropoffZoneSelect.innerHTML = '<option value="">Select pickup zone first</option>';
         dropoffZoneSelect.disabled = true;
     }
     
     // Reset vehicle and service type selects
     const vehicleSelect = document.querySelector(`select[name="day${day}_${section}_vehicle_id"]`);
     const serviceTypeSelect = document.querySelector(`select[name="day${day}_${section}_service_type"]`);
     
     if (vehicleSelect) {
         vehicleSelect.innerHTML = '<option value="">Select zones first</option>';
         vehicleSelect.disabled = true;
     }
     
     if (serviceTypeSelect) {
         // Keep the hardcoded options, just disable and reset selection
         serviceTypeSelect.disabled = true;
         serviceTypeSelect.value = "";
         serviceTypeSelect.disabled = true;
     }
     
     // Reset search button and hide vehicle results
     const searchBtn = document.getElementById(`day${day}_${section}_search_btn`);
     const vehicleResultsDiv = document.getElementById(`day${day}_${section}_vehicle_results`);
     
     if (searchBtn) {
         searchBtn.disabled = true;
         searchBtn.classList.remove('btn-primary');
         searchBtn.classList.add('btn-secondary');
     }
     if (vehicleResultsDiv) {
         vehicleResultsDiv.style.display = 'none';
     }
     
     // Check if search button should be enabled after zone selection
     setTimeout(() => enableSearchButton(day, section), 100);
 }

 function loadVehiclesForZones(day, section) {
     const pickupZoneSelect = document.querySelector(`select[name="day${day}_${section}_pickup_zone_id"]`);
     const dropoffZoneSelect = document.querySelector(`select[name="day${day}_${section}_dropoff_zone_id"]`);
     const vehicleSelect = document.querySelector(`select[name="day${day}_${section}_vehicle_id"]`);
     
     if (!pickupZoneSelect || !dropoffZoneSelect || !vehicleSelect) return;
     
     const fromZoneId = pickupZoneSelect.value;
     const toZoneId = dropoffZoneSelect.value;
     
     if (fromZoneId && toZoneId) {
         vehicleSelect.innerHTML = '<option value="">Loading vehicles...</option>';
         vehicleSelect.disabled = true;
         
         fetch(`{{ route('fetch-vehicles-by-zones') }}?from_zone_id=${fromZoneId}&to_zone_id=${toZoneId}`)
             .then(response => response.json())
             .then(data => {
                 if (data.success && data.vehicles && data.vehicles.length > 0) {
                     vehicleSelect.innerHTML = '<option value="">Select vehicle</option>';
                     
                     data.vehicles.forEach(vehicle => {
                         const vehicleInfo = `${vehicle.vehicle_name} (${vehicle.vehicle_type}) - ${vehicle.seating_capacity} seats`;
                         
                         // Debug logging for vehicle data
                         console.log('=== VEHICLE DATA DEBUG ===');
                         console.log('Vehicle:', vehicle);
                         console.log('Private price:', vehicle.private_price);
                         console.log('Shared price:', vehicle.shared_price);
                         
                         vehicleSelect.innerHTML += `<option value="${vehicle.vehicle_id}" 
                             data-private-price="${vehicle.private_price}" 
                             data-shared-price="${vehicle.shared_price}"
                             data-service-type="${vehicle.service_type}"
                             data-mapping-id="${vehicle.mapping_id}">
                             ${vehicleInfo}
                         </option>`;
                     });
                     
                     vehicleSelect.disabled = false;
                     
                     // Reset service type select and price display when vehicles are loaded
                     updateVehicleDetails(day, section);
                 } else {
                     vehicleSelect.innerHTML = '<option value="">No vehicles available for this route</option>';
                     vehicleSelect.disabled = true;
                     
                     // Reset service type select and price display when no vehicles
                     updateVehicleDetails(day, section);
                 }
             })
             .catch(error => {
                 console.error('Error fetching vehicles:', error);
                 vehicleSelect.innerHTML = '<option value="">Error loading vehicles</option>';
                 vehicleSelect.disabled = true;
             });
     } else {
         vehicleSelect.innerHTML = '<option value="">Select zones first</option>';
         vehicleSelect.disabled = true;
     }
     
     // Reset service type select
     const serviceTypeSelect = document.querySelector(`select[name="day${day}_${section}_service_type"]`);
     if (serviceTypeSelect) {
         // Keep the hardcoded options, just disable and reset selection
         serviceTypeSelect.disabled = true;
         serviceTypeSelect.value = "";
         serviceTypeSelect.disabled = true;
     }
 }

     window.updateVehicleDetails = function(day, section) {
     const vehicleSelect = document.querySelector(`select[name="day${day}_${section}_vehicle_id"]`);
     const serviceTypeSelect = document.querySelector(`select[name="day${day}_${section}_service_type"]`);
     const priceDisplay = document.getElementById(`day${day}_${section}_price_display`);
     
     if (!vehicleSelect || !serviceTypeSelect) return;
     
     const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
     
     if (selectedOption.value) {
         // Enable the service type select
         serviceTypeSelect.disabled = false;
         
         // Clear any existing price display
         if (priceDisplay) {
             priceDisplay.style.display = 'none';
         }
         
         // Reset service type selection to trigger pricing update
         serviceTypeSelect.value = "";
     } else {
         // Reset to default state
         serviceTypeSelect.disabled = true;
         serviceTypeSelect.value = "";
         
         // Hide price display
         if (priceDisplay) {
             priceDisplay.style.display = 'none';
         }
     }
 }

     window.updatePricing = function(day, section) {
    const serviceTypeSelect = document.querySelector(`select[name="day${day}_${section}_service_type"]`);
    const vehicleSelect = document.querySelector(`select[name="day${day}_${section}_vehicle_id"]`);
    const priceDisplay = document.getElementById(`day${day}_${section}_price_display`);
    
    if (!serviceTypeSelect || !vehicleSelect || !priceDisplay) {
        console.log('Required elements not found for pricing update');
        return;
    }
    
    const selectedServiceType = serviceTypeSelect.value;
    const selectedVehicleOption = vehicleSelect.options[vehicleSelect.selectedIndex];
    
    if (!selectedServiceType || !selectedVehicleOption.value) {
        priceDisplay.style.display = 'none';
        return;
    }
    
    // Get pricing data from the selected vehicle option
    const privatePrice = parseFloat(selectedVehicleOption.dataset.privatePrice) || 0;
    const sharedPrice = parseFloat(selectedVehicleOption.dataset.sharedPrice) || 0;
    
    // Debug logging to verify prices
    console.log('=== PRICING DEBUG ===');
    console.log('Selected vehicle option:', selectedVehicleOption);
    console.log('Vehicle dataset:', selectedVehicleOption.dataset);
    console.log('Raw private_price:', selectedVehicleOption.dataset.privatePrice);
    console.log('Raw shared_price:', selectedVehicleOption.dataset.sharedPrice);
    console.log('Parsed privatePrice:', privatePrice);
    console.log('Parsed sharedPrice:', sharedPrice);
    console.log('Selected service type:', selectedServiceType);
    
    let displayPrice = 0;
    let priceType = '';
    
    if (selectedServiceType === 'Private') {
        displayPrice = privatePrice;
        priceType = 'Private';
        console.log('Private service selected, price:', displayPrice);
    } else if (selectedServiceType === 'Shared') {
        displayPrice = sharedPrice;
        priceType = 'Shared';
        console.log('Shared service selected, price:', displayPrice);
    }
    
    if (displayPrice > 0) {
        // Get guest count for total price calculation
        const adults = parseInt(document.getElementById('adults').value) || 0;
        const children = parseInt(document.getElementById('children').value) || 0;
        const totalGuests = adults + children;
        
        let totalPrice = 0;
        let pricingDescription = '';
        
        if (selectedServiceType === 'Private') {
            // For private service: price is per vehicle (not per person)
            totalPrice = displayPrice;
            pricingDescription = `
                <strong>Vehicle Price:</strong> $${displayPrice.toFixed(2)} (per vehicle)<br>
                <strong>Total Guests:</strong> ${totalGuests} (${adults} adults, ${children} children)<br>
                <strong>Total Price:</strong> <span class="text-success fw-bold">$${totalPrice.toFixed(2)}</span><br>
                <small class="text-info">Private vehicle price is fixed regardless of guest count</small>
            `;
        } else if (selectedServiceType === 'Shared') {
            // For shared service: price is per person
            totalPrice = displayPrice * totalGuests;
            pricingDescription = `
                <strong>Base Price:</strong> $${displayPrice.toFixed(2)} per person<br>
                <strong>Total Guests:</strong> ${totalGuests} (${adults} adults, ${children} children)<br>
                <strong>Total Price:</strong> <span class="text-success fw-bold">$${totalPrice.toFixed(2)}</span>
            `;
        }
        
        priceDisplay.style.display = 'block';
        priceDisplay.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="ri-money-dollar-circle-line me-2 fs-4"></i>
                <div>
                    <strong>${priceType} Service Pricing</strong>
                    <div class="small">
                        ${pricingDescription}
                    </div>
                </div>
            </div>
        `;
        
        console.log(`${priceType} service selected for day ${day}, section ${section}: $${displayPrice} ${selectedServiceType === 'Private' ? 'per vehicle' : 'per person'}, Total: $${totalPrice}`);
        
        // Store pricing data in hidden fields
        const basePriceField = document.getElementById(`day${day}_${section}_base_price`);
        const totalPriceField = document.getElementById(`day${day}_${section}_total_price`);
        const serviceTypeField = document.getElementById(`day${day}_${section}_service_type`);
        const guestCountField = document.getElementById(`day${day}_${section}_guest_count`);
        
        if (basePriceField) basePriceField.value = displayPrice.toFixed(2);
        if (totalPriceField) totalPriceField.value = totalPrice.toFixed(2);
        if (serviceTypeField) serviceTypeField.value = selectedServiceType;
        if (guestCountField) guestCountField.value = totalGuests;
        
        console.log(`Pricing data stored in hidden fields for day ${day}, section ${section}:`);
        console.log(`- Base Price: $${displayPrice.toFixed(2)}`);
        console.log(`- Total Price: $${totalPrice.toFixed(2)}`);
        console.log(`- Service Type: ${selectedServiceType}`);
        console.log(`- Guest Count: ${totalGuests}`);
        
    } else {
        priceDisplay.style.display = 'none';
        console.log('No pricing information available for the selected vehicle and service type');
        
        // Clear hidden fields when no pricing
        const basePriceField = document.getElementById(`day${day}_${section}_base_price`);
        const totalPriceField = document.getElementById(`day${day}_${section}_total_price`);
        const serviceTypeField = document.getElementById(`day${day}_${section}_service_type`);
        const guestCountField = document.getElementById(`day${day}_${section}_guest_count`);
        
        if (basePriceField) basePriceField.value = '0';
        if (totalPriceField) totalPriceField.value = '0';
        if (serviceTypeField) serviceTypeField.value = '';
        if (guestCountField) guestCountField.value = '0';
    }
}

// Save service data to the backend
window.saveService = function(day, type) {
    // Get tour ID
    const tourId = window.currentTourId;
    if (!tourId) {
        showNotification('Tour ID not found. Please create a tour first.', 'error');
        return;
    }
    
    // Get agent ID
    const agentId = document.getElementById('agent_id').value;
    if (!agentId) {
        showNotification('Agent ID not found.', 'error');
        return;
    }
    
    // Prepare data based on service type
    let data = {};
    let section = '';
    
    if (type === 'entry_port') {
        section = 'entry';
        const pickupZoneId = document.querySelector(`select[name="day${day}_entry_pickup_zone_id"]`).value;
        const dropoffZoneId = document.querySelector(`select[name="day${day}_entry_dropoff_zone_id"]`).value;
        const pickupTime = document.querySelector(`select[name="day${day}_entry_pickup_time"]`).value;
        const pickupDate = document.querySelector(`input[name="day${day}_entry_pickup_date"]`).value;
        const vehicleId = document.querySelector(`select[name="day${day}_entry_vehicle_id"]`).value;
        const serviceType = document.querySelector(`select[name="day${day}_entry_service_type"]`).value;
        
        // Validate required fields
        if (!pickupZoneId || !dropoffZoneId || !pickupTime || !pickupDate || !vehicleId || !serviceType) {
            showNotification('Please fill in all required fields for entry port service.', 'error');
            return;
        }
        
        // Get pricing data from hidden fields
        const basePrice = document.getElementById(`day${day}_entry_base_price`).value || '0';
        const totalPrice = document.getElementById(`day${day}_entry_total_price`).value || '0';
        const guestCount = document.getElementById(`day${day}_entry_guest_count`).value || '0';
        
        console.log(`Entry port pricing data for day ${day}:`);
        console.log(`- Base Price: $${basePrice}`);
        console.log(`- Total Price: $${totalPrice}`);
        console.log(`- Guest Count: ${guestCount}`);
        
        // Create data object
        data = {
            from_zone_id: pickupZoneId,
            to_zone_id: dropoffZoneId,
            pickup_time: pickupTime,
            pickup_date: pickupDate,
            vehicle_id: vehicleId,
            service_type: serviceType,
            price: parseFloat(totalPrice),
            base_price: parseFloat(basePrice),
            guest_count: parseInt(guestCount),
            day: day
        };
    } 
    else if (type === 'exit_port') {
        section = 'exit';
        const pickupZoneId = document.querySelector(`select[name="day${day}_exit_pickup_zone_id"]`).value;
        const dropoffZoneId = document.querySelector(`select[name="day${day}_exit_dropoff_zone_id"]`).value;
        const pickupTime = document.querySelector(`select[name="day${day}_exit_time"]`).value;
        const pickupDate = document.querySelector(`input[name="day${day}_exit_date"]`).value;
        const vehicleId = document.querySelector(`select[name="day${day}_exit_vehicle_id"]`).value;
        const serviceType = document.querySelector(`select[name="day${day}_exit_service_type"]`).value;
        
        // Validate required fields
        if (!pickupZoneId || !dropoffZoneId || !pickupTime || !pickupDate || !vehicleId || !serviceType) {
            showNotification('Please fill in all required fields for exit port service.', 'error');
            return;
        }
        
        // Get pricing data from hidden fields
        const basePrice = document.getElementById(`day${day}_exit_base_price`).value || '0';
        const totalPrice = document.getElementById(`day${day}_exit_total_price`).value || '0';
        const guestCount = document.getElementById(`day${day}_exit_guest_count`).value || '0';
        
        console.log(`Exit port pricing data for day ${day}:`);
        console.log(`- Base Price: $${basePrice}`);
        console.log(`- Total Price: $${totalPrice}`);
        console.log(`- Guest Count: ${guestCount}`);
        
        // Create data object
        data = {
            from_zone_id: pickupZoneId,
            to_zone_id: dropoffZoneId,
            pickup_time: pickupTime,
            pickup_date: pickupDate,
            vehicle_id: vehicleId,
            service_type: serviceType,
            price: parseFloat(totalPrice),
            base_price: parseFloat(basePrice),
            guest_count: parseInt(guestCount),
            day: day
        };
    }
    else if (type === 'transport') {
        section = 'transport';
        const pickupZoneId = document.querySelector(`select[name="day${day}_transport_pickup_zone_id"]`).value;
        const dropoffZoneId = document.querySelector(`select[name="day${day}_transport_dropoff_zone_id"]`).value;
        const pickupTime = document.querySelector(`select[name="day${day}_transport_pickup_time"]`).value;
        const pickupDate = document.querySelector(`input[name="day${day}_transport_date"]`).value;
        const vehicleId = document.querySelector(`select[name="day${day}_transport_vehicle_id"]`).value;
        const serviceType = document.querySelector(`select[name="day${day}_transport_service_type"]`).value;
        
        // Validate required fields
        if (!pickupZoneId || !dropoffZoneId || !pickupTime || !pickupDate || !vehicleId || !serviceType) {
            showNotification('Please fill in all required fields for transport service.', 'error');
            return;
        }
        
        // Create data object
        data = {
            from_zone_id: pickupZoneId,
            to_zone_id: dropoffZoneId,
            pickup_time: pickupTime,
            pickup_date: pickupDate,
            vehicle_id: vehicleId,
            service_type: serviceType,
            day: day
        };
    }
    else if (type === 'guide') {
        section = 'guide';
        const guideId = document.querySelector(`select[name="day${day}_guide_1"]`).value;
        const packageType = document.querySelector(`select[name="day${day}_guide_1_package"]`).value;
        const pickupTime = document.querySelector(`input[name="day${day}_guide_1_pickup_time"]`).value;
        
        // Validate required fields
        if (!guideId || !packageType || !pickupTime) {
            showNotification('Please fill in all required fields for guide service.', 'error');
            return;
        }
        
        // Create data object
        data = {
            guide_id: guideId,
            package_type: packageType,
            pickup_time: pickupTime,
            day: day
        };
    }
    else if (type === 'attraction') {
        section = 'attraction';
        const attractionId = document.querySelector(`select[name="day${day}_attraction_1"]`).value;
        const ticketId = document.querySelector(`select[name="day${day}_attraction_1_ticket"]`).value;
        const timeSlot = document.querySelector(`select[name="day${day}_attraction_1_time"]`).value;
        
        // Validate required fields
        if (!attractionId || !ticketId || !timeSlot) {
            showNotification('Please fill in all required fields for attraction service.', 'error');
            return;
        }
        
        // Create data object
        data = {
            attraction_id: attractionId,
            ticket_id: ticketId,
            time_slot: timeSlot,
            day: day
        };
    }
    else if (type === 'restaurant') {
        section = 'restaurant';
        const restaurantId = document.querySelector(`select[name="day${day}_restaurant_1"]`).value;
        const mealType = document.querySelector(`select[name="day${day}_restaurant_1_meal_type"]`).value;
        const mealTime = document.querySelector(`select[name="day${day}_restaurant_1_time"]`).value;
        
        // Validate required fields
        if (!restaurantId || !mealType || !mealTime) {
            showNotification('Please fill in all required fields for restaurant service.', 'error');
            return;
        }
        
        // Create data object
        data = {
            restaurant_id: restaurantId,
            meal_type: mealType,
            meal_time: mealTime,
            day: day
        };
    }
    
    // Prepare request data
    const requestData = {
        agent_id: agentId,
        tour_id: tourId,
        type: type,
        data: data
    };
    
    // Show loading state
    const saveBtn = document.querySelector(`button[onclick="saveService(${day}, '${type}')"]`);
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="ri-loader-4-line spin me-2"></i>Saving...';
    saveBtn.disabled = true;
    
         // Send data to backend
     fetch('{{ route("save-service") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`${type.replace('_', ' ').toUpperCase()} service saved successfully!`, 'success');
            
            // Reset form fields
            if (type === 'entry_port' || type === 'exit_port' || type === 'transport') {
                // Reset dropoff zone
                const dropoffZoneSelect = document.querySelector(`select[name="day${day}_${section}_dropoff_zone_id"]`);
                if (dropoffZoneSelect) {
                    dropoffZoneSelect.value = '';
                    dropoffZoneSelect.disabled = true;
                }
                
                // Reset vehicle and service type
                const vehicleSelect = document.querySelector(`select[name="day${day}_${section}_vehicle_id"]`);
                const serviceTypeSelect = document.querySelector(`select[name="day${day}_${section}_service_type"]`);
                
                if (vehicleSelect) {
                    vehicleSelect.value = '';
                }
                
                if (serviceTypeSelect) {
                    serviceTypeSelect.value = '';
                }
                
                // Hide vehicle results
                const vehicleResultsDiv = document.getElementById(`day${day}_${section}_vehicle_results`);
                if (vehicleResultsDiv) {
                    vehicleResultsDiv.style.display = 'none';
                }
            }
        } else {
            showNotification(data.message || 'Failed to save service.', 'error');
        }
        
        // Reset button state
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    })
    .catch(error => {
        console.error('Error saving service:', error);
        showNotification('An error occurred while saving the service.', 'error');
        
        // Reset button state
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

// New functions for the search-based interface
 function searchVehicles(day, section) {
     console.log(`Searching vehicles for day ${day}, section ${section}`);
     
     const pickupZoneSelect = document.querySelector(`select[name="day${day}_${section}_pickup_zone_id"]`);
     const dropoffZoneSelect = document.querySelector(`select[name="day${day}_${section}_dropoff_zone_id"]`);
     const vehicleResultsDiv = document.getElementById(`day${day}_${section}_vehicle_results`);
     const vehicleSelect = document.querySelector(`select[name="day${day}_${section}_vehicle_id"]`);
     const serviceTypeSelect = document.querySelector(`select[name="day${day}_${section}_service_type"]`);
     const searchBtn = document.getElementById(`day${day}_${section}_search_btn`);
     
     console.log('Elements found:', {
         pickupZoneSelect: !!pickupZoneSelect,
         dropoffZoneSelect: !!dropoffZoneSelect,
         vehicleResultsDiv: !!vehicleResultsDiv,
         vehicleSelect: !!vehicleSelect,
         serviceTypeSelect: !!serviceTypeSelect,
         searchBtn: !!searchBtn
     });
     
     if (vehicleSelect) {
         console.log('Vehicle select element:', vehicleSelect.name, vehicleSelect.id);
     } else {
         console.error('Vehicle select not found for day', day, 'section', section);
         console.log('Looking for selector: select[name="day${day}_${section}_vehicle_id"]');
     }

     // Check if search button is enabled
     if (!searchBtn || searchBtn.disabled) {
         alert('Please fill in all required fields before searching for vehicles');
         return;
     }

     if (!pickupZoneSelect || !dropoffZoneSelect || !pickupZoneSelect.value || !dropoffZoneSelect.value) {
         alert('Please select both pickup and dropoff zones');
         return;
     }

     const fromZoneId = pickupZoneSelect.value;
     const toZoneId = dropoffZoneSelect.value;

     // Show loading state
     searchBtn.innerHTML = '<i class="ri-loader-4-line spin me-2"></i>Searching...';
     searchBtn.disabled = true;

     fetch(`{{ route('fetch-vehicles-by-zones') }}?from_zone_id=${fromZoneId}&to_zone_id=${toZoneId}`)
         .then(response => response.json())
         .then(data => {
             console.log('Vehicle search response:', data);
             if (data.success && data.vehicles && data.vehicles.length > 0) {
                 // Populate vehicle dropdown
                 if (vehicleSelect) {
                     vehicleSelect.innerHTML = '<option value="">Choose your vehicle</option>';
                     data.vehicles.forEach(vehicle => {
                         const vehicleInfo = `${vehicle.vehicle_name} (${vehicle.vehicle_type}) - ${vehicle.seating_capacity} seats`;
                         
                         // Debug logging for vehicle data
                         console.log('=== VEHICLE DATA DEBUG (searchVehicles) ===');
                         console.log('Vehicle:', vehicle);
                         console.log('Private price:', vehicle.private_price);
                         console.log('Shared price:', vehicle.shared_price);
                         
                         vehicleSelect.innerHTML += `<option value="${vehicle.vehicle_id}" 
                             data-private-price="${vehicle.private_price || ''}" 
                             data-shared-price="${vehicle.shared_price || ''}"
                             data-service-type="${vehicle.service_type || ''}"
                             data-mapping-id="${vehicle.mapping_id || ''}">
                             ${vehicleInfo}
                         </option>`;
                     });
                     
                     // Enable the vehicle select
                     vehicleSelect.disabled = false;
                     console.log('Vehicle dropdown populated successfully');
                     
                     // Reset service type select and price display when vehicles are loaded
                     updateVehicleDetails(day, section);
                 } else {
                     console.error('Vehicle select element not found!');
                 }

                 // Show results section
                 vehicleResultsDiv.style.display = 'block';
                 vehicleResultsDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                 // Reset search button
                 searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search Vehicles';
                 searchBtn.disabled = false;
                 
                 console.log(`Populated ${data.vehicles.length} vehicles in dropdown`);
             } else {
                 alert('No vehicles available for this route. Please try different zones.');
                 searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search Vehicles';
                 searchBtn.disabled = false;
             }
         })
         .catch(error => {
             console.error('Error searching vehicles:', error);
             alert('Error searching vehicles. Please try again.');
             searchBtn.innerHTML = '<i class="ri-search-line me-2"></i>Search Vehicles';
             searchBtn.disabled = false;
         });
 }

  function clearDropoffZone(day, section) {
     const dropoffZoneSelect = document.querySelector(`select[name="day${day}_${section}_dropoff_zone_id"]`);
     const vehicleResultsDiv = document.getElementById(`day${day}_${section}_vehicle_results`);
     const searchBtn = document.getElementById(`day${day}_${section}_search_btn`);

     if (dropoffZoneSelect) {
         dropoffZoneSelect.value = '';
         dropoffZoneSelect.innerHTML = '<option value="">Select pickup zone first</option>';
         dropoffZoneSelect.disabled = true;
     }

     if (vehicleResultsDiv) {
         vehicleResultsDiv.style.display = 'none';
     }

     if (searchBtn) {
         searchBtn.disabled = true;
         searchBtn.classList.remove('btn-primary');
         searchBtn.classList.add('btn-secondary');
     }
     
     // Check if search button should be enabled
     enableSearchButton(day, section);
 }

 // Update the existing loadDropoffZones function for the new interface
 function loadDropoffZonesNew(day, section) {
     const pickupZoneSelect = document.querySelector(`select[name="day${day}_${section}_pickup_zone_id"]`);
     const dropoffZoneSelect = document.querySelector(`select[name="day${day}_${section}_dropoff_zone_id"]`);
     const searchBtn = document.getElementById(`day${day}_${section}_search_btn`);
     const vehicleResultsDiv = document.getElementById(`day${day}_${section}_vehicle_results`);

     if (!pickupZoneSelect || !dropoffZoneSelect) return;

     const pickupZoneId = pickupZoneSelect.value;

     if (pickupZoneId) {
         const citySelect = document.getElementById('city');
         const city = citySelect ? citySelect.value : '';

         fetch(`{{ route('fetch-zones-by-dmc') }}?city=${encodeURIComponent(city)}`)
             .then(response => response.json())
             .then(data => {
                 if (data.success && data.zones) {
                     dropoffZoneSelect.innerHTML = '<option value="">Select dropoff zone</option>';

                     // Show all zones except the selected pickup zone
                     data.zones.forEach(zone => {
                         if (zone.zone_id !== pickupZoneId) {
                             dropoffZoneSelect.innerHTML += `<option value="${zone.zone_id}">${zone.zone_name} (${zone.zone_type})</option>`;
                         }
                     });

                     dropoffZoneSelect.disabled = false;
                 }
             })
             .catch(error => {
                 console.error('Error fetching dropoff zones:', error);
             });
     } else {
         dropoffZoneSelect.innerHTML = '<option value="">Select pickup zone first</option>';
         dropoffZoneSelect.disabled = true;
         if (searchBtn) searchBtn.disabled = true;
         if (vehicleResultsDiv) vehicleResultsDiv.style.display = 'none';
     }
 }

 // Update dropdown change handlers for new interface
 function setupNewTransportationHandlers() {
     console.log('Setting up transportation handlers');
     
     // Setup pickup zone change handlers using event delegation
     document.addEventListener('change', function(event) {
         if (event.target.classList.contains('pickup-zone-select')) {
             console.log('Pickup zone change detected:', event.target.name);
             const [day, section] = extractDayAndSection(event.target.name);
             console.log('Extracted day:', day, 'section:', section);
             if (day && section) {
                 handlePickupZoneChange(day, section);
             }
         }
     });

     // Setup dropoff zone change handlers
     const dropoffZoneSelects = document.querySelectorAll('.dropoff-zone-select');
     dropoffZoneSelects.forEach(select => {
         select.addEventListener('change', function() {
             const [day, section] = extractDayAndSection(this.name);
             const searchBtn = document.getElementById(`day${day}_${section}_search_btn`);
             
             if (this.value && searchBtn) {
                 searchBtn.disabled = false;
             } else if (searchBtn) {
                 searchBtn.disabled = true;
             }
         });
     });
 }

 function extractDayAndSection(fieldName) {
     // Extract day and section from field names like "day1_entry_pickup_zone_id"
     // We want to extract: day=1, section=entry from "day1_entry_pickup_zone_id"
     const match = fieldName.match(/day(\d+)_(\w+)_/);
     if (match) {
         const day = match[1];
         // Extract the section (entry, exit, transport) from the field name
         // Use a more specific regex to get just the section part
         const sectionMatch = fieldName.match(/day\d+_([^_]+)_/);
         const section = sectionMatch ? sectionMatch[1] : null;
         console.log(`Extracted from "${fieldName}": day=${day}, section=${section}`);
         return [day, section];
     }
     console.log(`No match found for field name: "${fieldName}"`);
     return [null, null];
 }
</script>
@endsection

@section('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.text-purple {
    color: #8b5cf6 !important;
}

.guest-counter .btn-counter {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.guest-counter input {
    border: 2px solid #e9ecef;
    font-weight: 600;
    font-size: 1.1rem;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.input-group-text {
    font-weight: 600;
}

.btn-lg {
    padding: 12px 30px;
    font-weight: 600;
    border-radius: 8px;
}

.alert {
    border-radius: 10px;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.card {
    animation: fadeIn 0.6s ease-out;
}

.badge {
    font-size: 1.2em !important;
    padding: 8px 12px;
}

/* Guest dropdown styles */
.dropdown-menu {
    border-radius: 10px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    border: none;
}

.guest-btn-plus, .guest-btn-minus {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

/* Badge styles for male/female */
.badge.bg-pink {
    background-color: #e91e63 !important;
}

/* Guest summary section */
.bg-light {
    background-color: #f8f9fa !important;
}

/* Guest counter alignment */




.guest-btn-plus:hover, .guest-btn-minus:hover {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}

/* Night selection buttons */
.night-btn {
    border-radius: 10px;
    transition: all 0.3s ease;
    border: 2px solid #e9ecef;
    font-size: 0.85rem;
}

.night-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.night-btn.active {
    transform: translateY(-2px) scale(1.02);
    border-width: 2px;
}

/* Manually selected nights - Green */
.night-btn.manually-selected {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
    box-shadow: 0 6px 12px rgba(40, 167, 69, 0.3);
}

.night-btn.manually-selected:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

/* Auto-selected nights - Orange/Warning */
.night-btn.auto-selected {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #212529;
    box-shadow: 0 6px 12px rgba(255, 193, 7, 0.3);
    position: relative;
}

.night-btn.auto-selected:hover {
    background-color: #ffb300;
    border-color: #ff8f00;
}

/* Add a small icon to auto-selected nights */
.night-btn.auto-selected::after {
    content: "⚡";
    position: absolute;
    top: 2px;
    right: 4px;
    font-size: 0.7rem;
    opacity: 0.8;
}

.night-btn small {
    font-size: 0.7rem;
    opacity: 0.9;
}

/* Date range picker styles */
.daterangepicker {
    border-radius: 10px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

/* Prevent dropdown from closing when clicking inside */
.dropdown-menu {
    cursor: default;
}

.dropdown-menu button {
    cursor: pointer;
}

/* Loading animation */
.spinner-border-sm {
    width: 0.75rem;
    height: 0.75rem;
}

/* Daily Services Styling */
.daily-service-section {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.daily-service-section:last-child {
    border-bottom: none !important;
    margin-bottom: 0;
}

.day-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
    border-radius: 12px 12px 0 0;
}

.day-content {
    background: #f8f9fa !important;
    min-height: 200px;
}

.service-card {
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
    margin-bottom: 1.5rem;
}

.service-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.service-header {
    background: #fff !important;
    border-radius: 8px 8px 0 0;
}

.service-icon {
    width: 45px;
    height: 45px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.services-container {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
}

/* Guest Display Styling */
.guest-display {
    background: #f8f9fa !important;
    border: 1px solid #e0e0e0 !important;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.guest-display:hover {
    border-color: #007bff !important;
    background: #fff !important;
}

.guest-info span {
    font-size: 0.85rem;
    color: #6c757d;
    line-height: 1.2;
}

.guest-badges {
    gap: 4px;
}

.guest-badges .badge {
    font-size: 0.75rem;
    padding: 0.25em 0.5em;
}

.guest-selector .btn {
    border-radius: 4px;
    padding: 0.25rem 0.5rem;
}

/* Service type specific borders */
.border-primary {
    border-color: #007bff !important;
}

.border-danger {
    border-color: #dc3545 !important;
}

.border-info {
    border-color: #17a2b8 !important;
}

.border-success {
    border-color: #28a745 !important;
}

.border-warning {
    border-color: #ffc107 !important;
}

/* Badge styling for service status */
.badge {
    font-size: 0.75rem;
    padding: 0.25em 0.5em;
}

/* Form styling in services */
.daily-service-section .form-control,
.daily-service-section .form-select {
    border-radius: 6px;
    border: 1px solid #e0e0e0;
    font-size: 0.9rem;
}

.daily-service-section .form-control:focus,
.daily-service-section .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Button styling in services */
.daily-service-section .btn {
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.daily-service-section .btn:hover {
    transform: translateY(-1px);
}

/* Day header styling */
.daily-service-section .bg-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
}

/* Guest Selection Dropdown Styles */
.guest-section {
    padding: 1rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.guest-section:last-child {
    border-bottom: none;
}

.section-header h6 {
    color: #333;
    font-weight: 600;
    margin-bottom: 0;
}

.guest-item {
    padding: 0.5rem 0;
}

.guest-icon {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.guest-label {
    color: #333;
    font-weight: 500;
}

.guest-section .btn-light {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.guest-section .btn-light:hover {
    background: #e9ecef;
    border-color: #dee2e6;
}

.guest-section .btn-light:active,
.guest-section .btn-light:focus {
    background: #dee2e6;
    border-color: #adb5bd;
    box-shadow: none;
}

.guest-section .btn-light i {
    font-size: 0.75rem;
    color: #6c757d;
}

/* Guest dropdown menu styling */
.dropdown-menu {
    border: 1px solid #e0e0e0;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-radius: 8px;
}



/* Loading spinner animation */
        .spin {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .pickup-time-dropdown {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .pickup-time-option {
            transition: all 0.15s ease-in-out;
        }
        
        .pickup-time-option:hover {
            background-color: #e9ecef !important;
            border-color: #0d6efd !important;
        }
        
        .pickup-time-option.bg-danger:hover {
            background-color: #dc3545 !important;
            opacity: 0.9;
        }
        
        .dish-options-container {
            margin-top: 8px;
        }
        
        .dish-option-btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .dish-option-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .dish-option-btn.selected {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

/* Disabled field styles */
.form-select:disabled,
.form-control:disabled {
    background-color: #f8f9fa !important;
    cursor: not-allowed !important;
    opacity: 0.7 !important;
    border-color: #dee2e6 !important;
}

.form-select:disabled option {
    color: #6c757d;
}

/* Disabled button styles */
.btn:disabled {
    opacity: 0.7 !important;
    cursor: not-allowed !important;
}

.btn-outline-secondary:disabled {
    background-color: #f8f9fa !important;
    border-color: #dee2e6 !important;
    color: #6c757d !important;
}

/* Guest display disabled state */
.guest-display.disabled {
    background-color: #f8f9fa !important;
    cursor: not-allowed !important;
    opacity: 0.7;
}

/* Loading spinner animation */
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* New transportation section styles */
.form-group label {
    font-size: 0.875rem;
    font-weight: 600;
}

.form-select.border-2,
.form-control.border-2 {
    border-width: 2px !important;
    border-color: #e0e0e0 !important;
    transition: all 0.3s ease;
}

.form-select.border-2:focus,
.form-control.border-2:focus {
    border-color: #007bff !important;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15) !important;
}

.form-select:disabled.border-2 {
    background-color: #f8f9fa !important;
    border-color: #dee2e6 !important;
    opacity: 0.7;
}

.position-relative .ri-map-pin-fill,
.position-relative .ri-time-fill,
.position-relative .ri-calendar-fill {
    pointer-events: none;
}

.alert-info {
    background-color: #e7f3ff !important;
    border-color: #b8daff !important;
    color: #0c63e4 !important;
}

/* Hotel loading status styles */
#hotelLoadingStatus {
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block;
}

#hotelLoadingStatus i {
    font-size: 1rem;
}
</style>

<!-- Dish Selection Modal -->
<div class="modal fade" id="dishSelectionModal" tabindex="-1" aria-labelledby="dishSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dishSelectionModalLabel">Select Dish</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="dishModalContent">
                    <!-- Dynamic content will be loaded here -->
                </div>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shopping-cart text-success me-2"></i>
                            <span class="fw-bold">Total Price:</span>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <span id="modalTotalPrice" class="h4 text-success">$0.00</span>
                        <br>
                        <small id="modalGuestInfo" class="text-muted"></small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCEL</button>
                <button type="button" class="btn btn-success" id="confirmDishSelection" disabled>
                    <i class="fas fa-check me-2"></i>CONFIRM SELECTION
                </button>
            </div>
        </div>
    </div>


</div>





@endsection 

<script>
    // Initialize package total price display when page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Update total price display initially
        updatePackageTotalPriceDisplay();
        
        // Also update when any service data changes
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' || mutation.type === 'attributes') {
                    // Update total price when DOM changes
                    setTimeout(updatePackageTotalPriceDisplay, 100);
                }
            });
        });
        
        // Observe changes to the service data fields
        const serviceDataFields = [
            'hotel_data',
            'attraction_data', 
            'restaurant_data',
            'guide_data',
            'transport_data',
            'entry_port_data',
            'exit_port_data'
        ];
        
        serviceDataFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                observer.observe(field, {
                    attributes: true,
                    childList: true,
                    subtree: true
                });
            }
        });
    });

    // Function to check and fix guide hidden fields
    window.fixGuideHiddenFields = function() {
        console.log('=== FIXING GUIDE HIDDEN FIELDS ===');
        
        document.querySelectorAll('.guide-select').forEach((select, index) => {
            const nameMatch = select.name.match(/day(\d+)_guide_(\d+)/);
            if (nameMatch) {
                const day = nameMatch[1];
                const guideIndex = nameMatch[2];
                
                console.log(`Checking guide ${index + 1} (Day ${day}, Index ${guideIndex})`);
                
                // Check if hidden fields exist
                const basePriceField = document.getElementById(`day${day}_guide_${guideIndex}_base_price`);
                const hoursField = document.getElementById(`day${day}_guide_${guideIndex}_hours`);
                const surchargeField = document.getElementById(`day${day}_guide_${guideIndex}_surcharge`);
                const totalPriceField = document.getElementById(`day${day}_guide_${guideIndex}_total_price`);
                
                console.log('Hidden fields status:', {
                    basePriceField: !!basePriceField,
                    hoursField: !!hoursField,
                    surchargeField: !!surchargeField,
                    totalPriceField: !!totalPriceField
                });
                
                // If any field is missing, create it
                if (!basePriceField || !hoursField || !surchargeField || !totalPriceField) {
                    console.log('Creating missing hidden fields...');
                    
                    const packageSelect = document.getElementById(`day${day}_guide_${guideIndex}_package`);
                    if (packageSelect) {
                        // Remove any existing hidden fields first
                        const existingHiddenFields = packageSelect.parentNode.querySelectorAll('input[type="hidden"][id*="_guide_"]');
                        existingHiddenFields.forEach(field => field.remove());
                        
                        // Add hidden fields after the package select
                        const hiddenFieldsHTML = `
                            <input type="hidden" id="day${day}_guide_${guideIndex}_base_price" name="day${day}_guide_${guideIndex}_base_price" value="0">
                            <input type="hidden" id="day${day}_guide_${guideIndex}_hours" name="day${day}_guide_${guideIndex}_hours" value="0">
                            <input type="hidden" id="day${day}_guide_${guideIndex}_surcharge" name="day${day}_guide_${guideIndex}_surcharge" value="0">
                            <input type="hidden" id="day${day}_guide_${guideIndex}_total_price" name="day${day}_guide_${guideIndex}_total_price" value="0">
                        `;
                        packageSelect.insertAdjacentHTML('afterend', hiddenFieldsHTML);
                        console.log('Hidden fields created successfully');
                    }
                }
            }
        });
        
        console.log('Guide hidden fields fix completed');
    };

    // Simple function to test guide pricing - call this from console
    window.testGuidePricing = function(day = 1, index = 1) {
        console.log(`Testing guide pricing for Day ${day}, Index ${index}`);
        
        const packageSelect = document.getElementById(`day${day}_guide_${index}_package`);
        const guideSelect = document.getElementById(`day${day}_guide_${index}`);
        
        console.log('Elements found:', {
            packageSelect: !!packageSelect,
            guideSelect: !!guideSelect,
            packageValue: packageSelect?.value,
            guideValue: guideSelect?.value
        });
        
        if (packageSelect && guideSelect && packageSelect.value && guideSelect.value) {
            console.log('Calling updateGuidePricing...');
            updateGuidePricing(day, index);
        } else {
            console.log('Cannot test - missing elements or values');
        }
    };

    // Test function to check multiple restaurant pricing
    window.testMultipleRestaurantPricing = function() {
        console.log('=== TESTING MULTIPLE RESTAURANT PRICING ===');
        
        // Check all restaurant forms for hidden fields
        const restaurantItems = document.querySelectorAll('.restaurant-item');
        console.log(`Found ${restaurantItems.length} restaurant forms`);
        
        restaurantItems.forEach((item, index) => {
            const restaurantIndex = index + 1;
            const totalPriceField = document.getElementById(`day1_restaurant_${restaurantIndex}_total_price`);
            const mealIdField = document.getElementById(`day1_restaurant_${restaurantIndex}_meal_id`);
            const dishNameField = document.getElementById(`day1_restaurant_${restaurantIndex}_dish_name`);
            
            console.log(`Restaurant ${restaurantIndex} hidden fields:`);
            console.log(`- Total Price: $${totalPriceField?.value || 'Not found'}`);
            console.log(`- Meal ID: ${mealIdField?.value || 'Not found'}`);
            console.log(`- Dish Name: ${dishNameField?.value || 'Not found'}`);
        });
        
        // Test the data collection function
        console.log('Calling updateRestaurantDataField()...');
        updateRestaurantDataField();
        
        // Check the collected data
        const restaurantData = document.getElementById('restaurant_data')?.value;
        
        console.log('Collected restaurant data:');
        if (restaurantData) {
            const parsedData = JSON.parse(restaurantData);
            console.log('Restaurant Data:', parsedData);
            
            // Check if all restaurants have correct pricing
            parsedData.forEach((restaurant, index) => {
                console.log(`Restaurant ${index + 1}:`);
                console.log(`- Total Price: $${restaurant.totalPrice}`);
                console.log(`- Meal Price: $${restaurant.mealPrice}`);
                console.log(`- Meal Description Price: $${restaurant.MealDescription[0]?.price || 'N/A'}`);
            });
        } else {
            console.log('No restaurant data found');
        }
    };

    // Test function to check restaurant data collection
    window.testRestaurantDataCollection = function() {
        console.log('=== TESTING RESTAURANT DATA COLLECTION ===');
        
        // Check if hidden fields exist and have values
        const totalPriceField = document.getElementById('day1_restaurant_1_total_price');
        const mealIdField = document.getElementById('day1_restaurant_1_meal_id');
        const dishNameField = document.getElementById('day1_restaurant_1_dish_name');
        
        console.log('Restaurant hidden fields:');
        console.log(`- Total Price: $${totalPriceField?.value || 'Not found'}`);
        console.log(`- Meal ID: ${mealIdField?.value || 'Not found'}`);
        console.log(`- Dish Name: ${dishNameField?.value || 'Not found'}`);
        
        // Test the data collection function
        console.log('Calling updateRestaurantDataField()...');
        updateRestaurantDataField();
        
        // Check the collected data
        const restaurantData = document.getElementById('restaurant_data')?.value;
        
        console.log('Collected restaurant data:');
        if (restaurantData) {
            console.log('Restaurant Data:', JSON.parse(restaurantData));
        } else {
            console.log('No restaurant data found');
        }
    };

    // Test function to check transport data collection
    window.testTransportDataCollection = function() {
        console.log('=== TESTING TRANSPORT DATA COLLECTION ===');
        
        // Check if hidden fields exist and have values
        const sections = ['entry', 'exit', 'transport'];
        
        sections.forEach(section => {
            const basePriceField = document.getElementById(`day1_${section}_base_price`);
            const totalPriceField = document.getElementById(`day1_${section}_total_price`);
            const serviceTypeField = document.getElementById(`day1_${section}_service_type`);
            
            if (basePriceField && totalPriceField && serviceTypeField) {
                console.log(`${section.toUpperCase()} PORT - Hidden fields:`);
                console.log(`- Base Price: $${basePriceField.value}`);
                console.log(`- Total Price: $${totalPriceField.value}`);
                console.log(`- Service Type: ${serviceTypeField.value}`);
            } else {
                console.log(`${section.toUpperCase()} PORT - Hidden fields not found`);
            }
        });
        
        // Test the data collection function
        console.log('Calling updateTransportDataField()...');
        updateTransportDataField();
        
        // Check the collected data
        const entryPortData = document.getElementById('entry_port_data')?.value;
        const exitPortData = document.getElementById('exit_port_data')?.value;
        const transportData = document.getElementById('transport_data')?.value;
        
        console.log('Collected data:');
        if (entryPortData) {
            console.log('Entry Port Data:', JSON.parse(entryPortData));
        }
        if (exitPortData) {
            console.log('Exit Port Data:', JSON.parse(exitPortData));
        }
        if (transportData) {
            console.log('Transport Data:', JSON.parse(transportData));
        }
    };

    // Test function to check entry port pricing
    window.testEntryPortPricing = function(day = 1) {
        console.log(`=== TESTING ENTRY PORT PRICING FOR DAY ${day} ===`);
        
        // Check if hidden fields exist
        const basePriceField = document.getElementById(`day${day}_entry_base_price`);
        const totalPriceField = document.getElementById(`day${day}_entry_total_price`);
        const serviceTypeField = document.getElementById(`day${day}_entry_service_type`);
        const guestCountField = document.getElementById(`day${day}_entry_guest_count`);
        
        console.log('Hidden fields found:', {
            basePriceField: !!basePriceField,
            totalPriceField: !!totalPriceField,
            serviceTypeField: !!serviceTypeField,
            guestCountField: !!guestCountField
        });
        
        if (basePriceField && totalPriceField && serviceTypeField && guestCountField) {
            console.log('Current hidden field values:');
            console.log(`- Base Price: $${basePriceField.value}`);
            console.log(`- Total Price: $${totalPriceField.value}`);
            console.log(`- Service Type: ${serviceTypeField.value}`);
            console.log(`- Guest Count: ${guestCountField.value}`);
        }
        
        // Check if vehicle and service type are selected
        const vehicleSelect = document.querySelector(`select[name="day${day}_entry_vehicle_id"]`);
        const serviceTypeSelect = document.querySelector(`select[name="day${day}_entry_service_type"]`);
        
        if (vehicleSelect && serviceTypeSelect) {
            console.log('Current selections:');
            console.log(`- Vehicle: ${vehicleSelect.value} (${vehicleSelect.options[vehicleSelect.selectedIndex]?.text || 'None'})`);
            console.log(`- Service Type: ${serviceTypeSelect.value}`);
            
            if (vehicleSelect.value && serviceTypeSelect.value) {
                console.log('✅ Vehicle and service type selected - pricing should be calculated');
                // Trigger pricing update
                updatePricing(day, 'entry');
            } else {
                console.log('❌ Please select both vehicle and service type first');
            }
        } else {
            console.log('❌ Vehicle or service type selectors not found');
        }
    };

    // Test function to check if all required functions are available
    window.testAllFunctions = function() {
        console.log('=== TESTING ALL REQUIRED FUNCTIONS ===');
        
        const requiredFunctions = [
            'updateGuidePricing',
            'updateAttractionPricing', 
            'loadGuideDetails',
            'loadAttractionDetails',
            'loadRestaurantDetails',
            'updateVehicleDetails',
            'updatePricing',
            'updatePackagePrices',
            'updatePackagePricesForTime',
            'calculateAllGuidePricing',
            'fixGuideHiddenFields',
            'quickFixGuidePricing'
        ];
        
        const results = {};
        
        requiredFunctions.forEach(funcName => {
            const isAvailable = typeof window[funcName] === 'function';
            results[funcName] = isAvailable;
            console.log(`${funcName}: ${isAvailable ? '✅ AVAILABLE' : '❌ MISSING'}`);
        });
        
        const missingFunctions = Object.keys(results).filter(func => !results[func]);
        
        if (missingFunctions.length === 0) {
            console.log('🎉 ALL FUNCTIONS ARE AVAILABLE!');
        } else {
            console.log('❌ MISSING FUNCTIONS:', missingFunctions);
        }
        
        return results;
    };

    // Quick fix function - run this to immediately calculate all guide pricing
    window.quickFixGuidePricing = function() {
        console.log('=== QUICK FIX: CALCULATING ALL GUIDE PRICING ===');
        
        // First, ensure hidden fields exist
        fixGuideHiddenFields();
        
        // Then calculate pricing for all guides
        calculateAllGuidePricing();
        
        // Finally, update the guide data field
        updateGuideDataField();
        
        console.log('Quick fix completed! Check the guide_data field for updated pricing.');
    };

    // Simple function to test guide pricing - call this from console
    window.testGuidePricing = function(day = 1, index = 1) {
        console.log(`Testing guide pricing for Day ${day}, Index ${index}`);
        
        const packageSelect = document.getElementById(`day${day}_guide_${index}_package`);
        const guideSelect = document.getElementById(`day${day}_guide_${index}`);
        
        console.log('Elements found:', {
            packageSelect: !!packageSelect,
            guideSelect: !!guideSelect,
            packageValue: packageSelect?.value,
            guideValue: guideSelect?.value
        });
        
        if (packageSelect && guideSelect && packageSelect.value && guideSelect.value) {
            console.log('Calling updateGuidePricing...');
            updateGuidePricing(day, index);
        } else {
            console.log('Cannot test - missing elements or values');
        }
    };

                 // Function to calculate pricing for all guides and store in hidden fields
                 window.calculateAllGuidePricing = function() {
                     console.log('=== CALCULATING ALL GUIDE PRICING ===');
                     
                     document.querySelectorAll('.guide-select').forEach((select, index) => {
                         if (select.value) {
                             const nameMatch = select.name.match(/day(\d+)_guide_(\d+)/);
                             if (nameMatch) {
                                 const day = nameMatch[1];
                                 const guideIndex = nameMatch[2];
                                 
                                 const packageSelect = document.getElementById(`day${day}_guide_${guideIndex}_package`);
                                 if (packageSelect && packageSelect.value) {
                                     console.log(`Calculating pricing for Day ${day}, Guide ${guideIndex}`);
                                     
                                     const selectedPackage = packageSelect.options[packageSelect.selectedIndex];
                                     const selectedGuide = select.options[select.selectedIndex];
                                     
                                     if (selectedPackage && selectedGuide && selectedPackage.dataset) {
                                         const basePrice = parseFloat(selectedPackage.dataset.price) || 0;
                                         const hours = parseInt(selectedPackage.dataset.hours) || 0;
                                         
                                         // Calculate surcharge based on pickup time
                                         const pickupTime = document.getElementById(`day${day}_guide_${guideIndex}_pickup_time`)?.value || '';
                                         let surcharge = 0;
                                         
                                         if (pickupTime) {
                                             const pickupHour = parseInt(pickupTime.split(':')[0]);
                                             const nightStartTime = selectedGuide.dataset.nightStartTime;
                                             const nightEndTime = selectedGuide.dataset.nightEndTime;
                                             
                                             if (nightStartTime && nightEndTime) {
                                                 const nightStart = parseInt(nightStartTime.split(':')[0]);
                                                 const nightEnd = parseInt(nightEndTime.split(':')[0]) - 1;
                                                 
                                                 const isNightTime = (pickupHour >= nightStart && pickupHour <= nightEnd) || 
                                                                    (nightStart > nightEnd && (pickupHour >= nightStart || pickupHour <= nightEnd));
                                                 
                                                 if (isNightTime) {
                                                     surcharge = parseFloat(selectedGuide.dataset.nightSurcharge) || 0;
                                                 }
                                             }
                                         }
                                         
                                         const totalPrice = basePrice + surcharge;
                                         
                                         // Update hidden fields
                                         const basePriceField = document.getElementById(`day${day}_guide_${guideIndex}_base_price`);
                                         const hoursField = document.getElementById(`day${day}_guide_${guideIndex}_hours`);
                                         const surchargeField = document.getElementById(`day${day}_guide_${guideIndex}_surcharge`);
                                         const totalPriceField = document.getElementById(`day${day}_guide_${guideIndex}_total_price`);
                                         
                                         if (basePriceField) basePriceField.value = basePrice.toFixed(2);
                                         if (hoursField) hoursField.value = hours.toString();
                                         if (surchargeField) surchargeField.value = surcharge.toFixed(2);
                                         if (totalPriceField) totalPriceField.value = totalPrice.toFixed(2);
                                         
                                         console.log(`Pricing calculated for Day ${day}, Guide ${guideIndex}:`, {
                                             basePrice: basePrice.toFixed(2),
                                             hours: hours,
                                             surcharge: surcharge.toFixed(2),
                                             totalPrice: totalPrice.toFixed(2)
                                         });
                                     }
                                 }
                             }
                         }
                     });
                     
                     console.log('All guide pricing calculated');
                 }

                 // Function to manually trigger pricing for all guides
                 window.triggerAllGuidePricing = function() {
                     console.log('=== TRIGGERING ALL GUIDE PRICING ===');
        
                     document.querySelectorAll('.guide-select').forEach((select, index) => {
                        if (select.value) {
                            const nameMatch = select.name.match(/day(\d+)_guide_(\d+)/);
                            if (nameMatch) {
                                const day = nameMatch[1];
                                const guideIndex = nameMatch[2];
                                 
                                const packageSelect = document.getElementById(`day${day}_guide_{guideIndex}_package`);
                                if (packageSelect && packageSelect.value) {
                                    console.log(`Triggering pricing for Day ${day}, Guide ${guideIndex}`);
                                    updateGuidePricing(day, guideIndex);
                                }
                            }
                        }
                     });
                     
                     console.log('All guide pricing triggered');
                 };
</script> 