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
                                <h4 class="mb-1 text-white">Create Single Tour Package</h4>
                                <p class="mb-0 opacity-75">Design personalized tour experiences for your clients</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                                    <select name="user_country" id="user_country" class="form-select" required>
                                        <option value="">Choose a country...</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->name }}">{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="country_id" id="country_id">
                                </div>

                                <!-- City Selection -->
                                <div class="col-md-2">
                                    <label for="city" class="form-label fw-semibold">
                                        <i class="ri-building-line me-1"></i>City
                                    </label>
                                    <select name="city" id="city" class="form-select" required disabled>
                                        <option value="">Select country first</option>
                                    </select>
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
                                    <input type="text" id="travel_dates" class="form-control" placeholder="Select dates" readonly>
                                    <input type="hidden" name="start_date" id="start_date">
                                    <input type="hidden" name="end_date" id="end_date">
                                </div>

                                <!-- Guests -->
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">
                                        <i class="ri-group-line me-1"></i>Guests
                                    </label>
                                    <div class="guest-selector">
                                        <div class="guest-display p-2 border rounded bg-light">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="guest-info">
                                                    <span id="mainGuestSummary" class="text-muted small">
                                                        1 adults (0 male, 0 female), 0 children - 0 infants
                                                    </span>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="openMainGuestSelector()">
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

                                    
                                    <!-- Hidden Fields -->
                                    <input type="hidden" name="adults" id="adults" value="1">
                                    <input type="hidden" name="male" id="male" value="0">
                                    <input type="hidden" name="female" id="female" value="0">
                                    <input type="hidden" name="children" id="children" value="0">
                                    <input type="hidden" name="infants" id="infants" value="0">
                                    <input type="hidden" name="child_ages" id="child_ages" value="[]">
                                </div>

                                <!-- Agent Selection -->
                                <div class="col-md-2">
                                    <label for="agent_id" class="form-label fw-semibold">
                                        <i class="ri-user-star-line me-1"></i>Agent
                                    </label>
                                    <select name="agent_id" id="agent_id" class="form-select" required>
                                        <option value="">Choose agent...</option>
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->agent_id }}">{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Create Button -->
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary w-100" onclick="createTourPackage()">
                                        <i class="ri-rocket-line me-1"></i>Create Amazing Tour Package
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
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Meal Plan</label>
                                    <select class="form-select" id="mealPlanSelect">
                                        <option value="">Select Meal Plans</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Number of Rooms</label>
                                    <input type="number" class="form-control" id="numberOfRooms" value="1" min="1">
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-success w-100" onclick="addHotel()">
                                        <i class="ri-add-line"></i>
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
                        </div>
                    </div>
                </div>
            </div>

            <!-- Package Details Section (Hidden Initially) -->
            <div class="row mb-4" id="packageDetailsSection" style="display: none;">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold text-secondary">
                                <i class="ri-file-text-line me-2"></i>Package Details
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="package_name" class="form-label fw-semibold">
                                        <i class="ri-bookmark-line me-1"></i>Package Name
                                    </label>
                                    <input type="text" name="package_name" id="package_name" class="form-control" placeholder="Enter package name..." required>
                                </div>
                                <div class="col-md-6">
                                    <label for="estimated_budget" class="form-label fw-semibold">
                                        <i class="ri-money-dollar-circle-line me-1"></i>Estimated Budget (SGD)
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">S$</span>
                                        <input type="number" name="estimated_budget" id="estimated_budget" class="form-control" placeholder="0.00" step="0.01" min="0">
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <label for="package_description" class="form-label fw-semibold">
                                        <i class="ri-file-list-line me-1"></i>Package Description
                                    </label>
                                    <textarea name="package_description" id="package_description" rows="4" class="form-control" placeholder="Describe the tour package details, inclusions, and highlights..."></textarea>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="form-check form-switch form-check-lg">
                                        <input class="form-check-input" type="checkbox" name="is_premium" id="is_premium">
                                        <label class="form-check-label fw-semibold text-warning" for="is_premium">
                                            <i class="ri-vip-crown-line me-1"></i>Premium Package
                                        </label>
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

            <!-- Final Submit Section (Hidden Initially) -->
            <div class="row mb-5" id="submitSection" style="display: none;">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body text-center">
                            <button type="submit" class="btn btn-success btn-lg px-5 me-3">
                                <i class="ri-save-line me-2"></i>Save Tour Package
                            </button>
                            <a href="{{ route('single-tour-package.index') }}" class="btn btn-outline-secondary btn-lg px-5">
                                <i class="ri-close-line me-2"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>



        </form>

    </div>
</div>
@endsection

@section('scripts')
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
document.addEventListener('DOMContentLoaded', function() {
    // Global variables
    let tourStartDate = null;
    let tourEndDate = null;
    let selectedHotels = [];
    let tourNights = 0;

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
         // Date Range Picker Initialization
         $('#travel_dates').daterangepicker({
             opens: 'left',
             autoUpdateInput: false,
             minDate: moment(),
             locale: {
                 format: 'MMM DD, YYYY',
                 cancelLabel: 'Clear'
             }
         });

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

    // Store hotel data globally for reference
    let hotelData = [];
    
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
        
        // Fetch hotels from API using DMC-specific endpoint
        fetch(`/fetch-hotels-by-dmc?city=${encodeURIComponent(cityName)}`)
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
        
        // Show loading state
        if (roomTypeSelect) roomTypeSelect.innerHTML = '<option value="">Loading rooms...</option>';
        if (bedTypeSelect) bedTypeSelect.innerHTML = '<option value="">Loading rooms...</option>';
        if (mealPlanSelect) mealPlanSelect.innerHTML = '<option value="">Loading rooms...</option>';
        
        // Fetch rooms for the selected hotel
        fetch(`/fetch-rooms-by-hotel?hotel_id=${encodeURIComponent(hotelId)}`)
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
                
                if (response.success && response.rooms && response.rooms.length > 0) {
                    // Extract unique values from rooms
                    const roomTypes = [...new Set(response.rooms.map(room => room.room_type).filter(Boolean))];
                    
                    // Create meal plan options based on available meals and guest count
                    const mealPlans = new Set();
                    
                    // Get current guest count
                    const adults = parseInt(document.getElementById('adults').value) || 0;
                    const children = parseInt(document.getElementById('children').value) || 0;
                    const infants = parseInt(document.getElementById('infants').value) || 0;
                    const totalGuests = adults + children; // Infants usually don't count for meals
                    
                    response.rooms.forEach(room => {
                        let mealOptions = [];
                        let mealCosts = [];
                        
                        if (room.breakfast) {
                            mealOptions.push('Breakfast');
                            if (room.breakfast_price) {
                                mealCosts.push(`Breakfast ($${(room.breakfast_price * totalGuests).toFixed(2)} for ${totalGuests} guests)`);
                            }
                        }
                        if (room.lunch) {
                            mealOptions.push('Lunch');
                            if (room.lunch_price) {
                                mealCosts.push(`Lunch ($${(room.lunch_price * totalGuests).toFixed(2)} for ${totalGuests} guests)`);
                            }
                        }
                        if (room.dinner) {
                            mealOptions.push('Dinner');
                            if (room.dinner_price) {
                                mealCosts.push(`Dinner ($${(room.dinner_price * totalGuests).toFixed(2)} for ${totalGuests} guests)`);
                            }
                        }
                        
                        // Add meal plan combinations with guest-based pricing
                        if (mealOptions.length > 0) {
                            if (mealOptions.length === 1) {
                                const totalCost = mealCosts.length > 0 ? ` - ${mealCosts[0]}` : '';
                                mealPlans.add(`${mealOptions[0]} Only (${totalGuests} guests)${totalCost}`);
                            } else if (mealOptions.length === 2) {
                                const totalCost = mealCosts.length > 1 ? 
                                    ` - Total: $${((room.breakfast_price || 0) + (room.lunch_price || 0) + (room.dinner_price || 0)) * totalGuests} for ${totalGuests} guests` : '';
                                mealPlans.add(`${mealOptions.join(' + ')} (${totalGuests} guests)${totalCost}`);
                            } else if (mealOptions.length === 3) {
                                const totalCost = mealCosts.length > 2 ? 
                                    ` - Total: $${((room.breakfast_price || 0) + (room.lunch_price || 0) + (room.dinner_price || 0)) * totalGuests} for ${totalGuests} guests` : '';
                                mealPlans.add(`Full Board - All Meals (${totalGuests} guests)${totalCost}`);
                            }
                        }
                        
                        // Also add individual meal types with guest count
                        if (room.breakfast_type) {
                            const cost = room.breakfast_price ? ` - $${(room.breakfast_price * totalGuests).toFixed(2)}` : '';
                            mealPlans.add(`${room.breakfast_type} Breakfast (${totalGuests} guests)${cost}`);
                        }
                        if (room.lunch_type) {
                            const cost = room.lunch_price ? ` - $${(room.lunch_price * totalGuests).toFixed(2)}` : '';
                            mealPlans.add(`${room.lunch_type} Lunch (${totalGuests} guests)${cost}`);
                        }
                        if (room.dinner_type) {
                            const cost = room.dinner_price ? ` - $${(room.dinner_price * totalGuests).toFixed(2)}` : '';
                            mealPlans.add(`${room.dinner_type} Dinner (${totalGuests} guests)${cost}`);
                        }
                    });
                    
                    // Populate room types
                    roomTypes.forEach(roomType => {
                        if (roomTypeSelect) {
                            roomTypeSelect.innerHTML += `<option value="${roomType}">${roomType}</option>`;
                        }
                    });
                    
                    // Add event listener for room type selection
                    if (roomTypeSelect) {
                        roomTypeSelect.onchange = function() {
                            updateBedTypesForRoom(this.value);
                        };
                    }
                    
                    // Store room data globally for bed fetching
                    window.roomData = response.rooms;
                    
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
                    
                    console.log(`Loaded ${response.rooms.length} rooms for hotel ${hotelId}`);
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
        
        if (!roomType || !window.roomData) {
            if (bedTypeSelect) bedTypeSelect.innerHTML = '<option value="">Select room type first</option>';
            return;
        }
        
        // Find rooms of the selected type
        const selectedRooms = window.roomData.filter(room => room.room_type === roomType);
        
        if (selectedRooms.length === 0) {
            if (bedTypeSelect) bedTypeSelect.innerHTML = '<option value="">No rooms of this type</option>';
            return;
        }
        
        // Show loading state
        if (bedTypeSelect) bedTypeSelect.innerHTML = '<option value="">Loading beds...</option>';
        
        // Get the first room of this type to fetch beds
        const roomId = selectedRooms[0].room_id;
        
        // Fetch beds for the selected room
        fetch(`/fetch-beds-by-room?room_id=${encodeURIComponent(roomId)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(response => {
                console.log('Beds API Response:', response);
                
                // Clear dropdown
                if (bedTypeSelect) bedTypeSelect.innerHTML = '<option value="">Select bed type</option>';
                
                if (response.success && response.beds && response.beds.length > 0) {
                    // Populate bed types
                    response.beds.forEach(bed => {
                        if (bedTypeSelect) {
                            const roomInfo = bed.no_of_rooms > 1 ? ` (${bed.no_of_rooms} rooms)` : '';
                            const occupancyInfo = bed.max_occupancy ? ` - Max ${bed.max_occupancy} guests` : '';
                            const adultChild = bed.adult_count && bed.child_count ? ` (${bed.adult_count}A+${bed.child_count}C)` : '';
                            const extraBed = bed.extra_bed ? ' + Extra Bed Available' : '';
                            const babyCot = bed.baby_cot ? ' + Baby Cot Available' : '';
                            
                            bedTypeSelect.innerHTML += `<option value="${bed.bed_id}">${bed.room_type}${roomInfo}${occupancyInfo}${adultChild}${extraBed}${babyCot}</option>`;
                        }
                    });
                    
                    console.log(`Loaded ${response.beds.length} beds for room type ${roomType}`);
                } else {
                    console.log('No beds found for room type:', roomType);
                    if (bedTypeSelect) bedTypeSelect.innerHTML = '<option value="">No beds available</option>';
                }
            })
            .catch(error => {
                console.error('Error loading beds:', error);
                if (bedTypeSelect) bedTypeSelect.innerHTML = '<option value="">Error loading beds</option>';
            });
    }

         // Add Hotel Function
     window.addHotel = function() {
         const hotelSelect = document.getElementById('hotelSelect');
         const roomType = document.getElementById('roomTypeSelect').value;
         const bedType = document.getElementById('bedTypeSelect').value;
         const mealPlan = document.getElementById('mealPlanSelect').value;
         const numberOfRooms = document.getElementById('numberOfRooms').value;
         
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
         
         const hotelData = {
             id: hotelSelect.value,
             name: hotelSelect.options[hotelSelect.selectedIndex].text,
             roomType: roomType || 'Standard',
             bedType: bedType || 'Standard',
             mealPlan: mealPlan || 'Not specified',
             numberOfRooms: numberOfRooms,
             nights: nightNumbers,
             checkInDate: checkInDate.format('MMM DD'),
             checkOutDate: checkOutDate.format('MMM DD'),
             totalNights: nightNumbers.length
         };
         
         selectedHotels.push(hotelData);
         displaySelectedHotels();
         
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
                 <div class="daily-service-section border-bottom">
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
                              <button type="button" class="btn btn-sm btn-outline-primary" onclick="addPortService(${day})">
                                  <i class="ri-add-line me-1"></i>Add Port Service
                              </button>
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
                                 
                                 <div class="row g-3">
                                     <div class="col-md-3">
                                         <label class="form-label fw-semibold">Pick Up Location</label>
                                         <input type="text" class="form-control" placeholder="Suntec City" name="day${day}_pickup_location">
                                     </div>
                                     <div class="col-md-3">
                                         <label class="form-label fw-semibold">Drop Off Location</label>
                                         <input type="text" class="form-control" placeholder="Serangoon MRT Station (CC13)" name="day${day}_dropoff_location">
                                     </div>
                                     <div class="col-md-3">
                                         <label class="form-label fw-semibold">Pick Up Time</label>
                                         <select class="form-select" name="day${day}_pickup_time">
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
                                             <option value="12:00 PM" selected>12:00 PM</option>
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
                                     </div>
                                     <div class="col-md-3">
                                         <label class="form-label fw-semibold">Pick Up Date</label>
                                         <input type="date" class="form-control" value="${currentDate.format('YYYY-MM-DD')}" name="day${day}_pickup_date">
                                     </div>
                                 </div>
                                 
                                 <div class="mt-3 text-center">
                                     <button type="button" class="btn btn-primary">
                                         <i class="ri-search-line me-1"></i>Search
                                     </button>
                                 </div>
                             </div>
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
                              <button type="button" class="btn btn-sm btn-outline-danger" onclick="addPortService(${day})">
                                  <i class="ri-add-line me-1"></i>Add Port Service
                              </button>
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
                                 
                                 <div class="row g-3">
                                     <div class="col-md-4">
                                         <label class="form-label fw-semibold">Pick Up Location</label>
                                         <input type="text" class="form-control" placeholder="Where is your pick up?" name="day${day}_exit_pickup_location">
                                     </div>
                                     <div class="col-md-4">
                                         <label class="form-label fw-semibold">Drop Off Location</label>
                                         <input type="text" class="form-control" placeholder="Where is your drop off?" name="day${day}_exit_dropoff_location">
                                     </div>
                                     <div class="col-md-4">
                                         <label class="form-label fw-semibold">Exit Time</label>
                                         <select class="form-select" name="day${day}_exit_time">
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
                                     </div>
                                 </div>
                                 
                                 <div class="row mt-3">
                                     <div class="col-md-12">
                                         <label class="form-label fw-semibold">Exit Date</label>
                                         <input type="date" class="form-control" value="${currentDate.format('YYYY-MM-DD')}" name="day${day}_exit_date">
                                     </div>
                                 </div>
                                 
                                 <div class="mt-3 text-center">
                                     <button type="button" class="btn btn-primary">
                                         <i class="ri-search-line me-1"></i>Search
                                     </button>
                                 </div>
                             </div>
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
                                         <select class="form-select" name="day${day}_attraction_1_ticket" id="day${day}_attraction_1_ticket">
                                             <option value="">Select Ticket</option>
                                         </select>
                                     </div>
                                 </div>
                                 
                                 </div>
                              </div>
                          </div>
                          
                          <div class="mt-3 text-center">
                                          <button type="button" class="btn btn-sm btn-outline-danger" onclick="addMoreAttractions(${day})">
                                              <i class="ri-add-line me-1"></i>Add Another Attraction
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
                                              <i class="ri-add-line me-1"></i>Add Another Guide
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
                                  <i class="ri-add-line me-1"></i>Add Another Restaurant
                                     </button>
                             </div>
                         </div>
                     </div>
                     
                     <!-- Transport Services -->
                     <div class="mb-4">
                         <div class="d-flex justify-content-between align-items-center mb-3">
                             <h6 class="text-warning mb-0">
                                 <i class="ri-car-line me-2"></i>Book Transport Services
                                 <small class="text-muted ms-2">Select professional transport and configure your tour package</small>
                             </h6>
                             <button type="button" class="btn btn-sm btn-warning" onclick="addTransport(${day})">
                                 <i class="ri-add-line me-1"></i>Search Transport
                             </button>
                         </div>
                         
                         <div class="card border-warning">
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
                                 
                                 <div class="row g-3">
                                     <div class="col-md-4">
                                         <label class="form-label fw-semibold">Pick Up Location</label>
                                         <input type="text" class="form-control" placeholder="Where is your pick up?" name="day${day}_transport_pickup">
                                     </div>
                                     <div class="col-md-4">
                                         <label class="form-label fw-semibold">Drop Off Location</label>
                                         <input type="text" class="form-control" placeholder="Where is your drop off?" name="day${day}_transport_dropoff">
                                     </div>
                                     <div class="col-md-4">
                                         <label class="form-label fw-semibold">Select the Pick up Time</label>
                                         <select class="form-select" name="day${day}_transport_pickup_time">
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
                                     </div>
                                 </div>
                                 
                                 <div class="mt-3 text-center">
                                     <button type="button" class="btn btn-warning">
                                         <i class="ri-search-line me-1"></i>Search
                                     </button>
                                 </div>
                             </div>
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
    };
    
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
                            (ticket.price ? ' - ' + ticket.currency + ' ' + ticket.price : '') + 
                            (ticket.ticket_type ? ' (' + ticket.ticket_type + ')' : '');
                        ticketSelect.appendChild(option);
                    });
                    
                    if (data.tickets.length === 0) {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'No tickets available for this attraction';
                        option.disabled = true;
                        ticketSelect.appendChild(option);
                    }
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
         selectedHotels.splice(index, 1);
         displaySelectedHotels();
         
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
                            <select class="form-select" name="day${day}_attraction_${newIndex}_ticket" id="day${day}_attraction_${newIndex}_ticket">
                                <option value="">Select Ticket</option>
                            </select>
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
    function updatePackagePricesForTime(day, index, selectedTimeValue) {
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
    function updatePackagePrices(day, index, isNightTime) {
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
            { value: '1_hour', label: '1 Hour Package', price: baseRate + hourlyPrice },
            { value: '2_hour', label: '2 Hour Package', price: baseRate + twoHourPrice },
            { value: '4_hour', label: '4 Hour Package', price: baseRate + fourHourPrice },
            { value: '6_hour', label: '6 Hour Package', price: baseRate + sixHourPrice },
            { value: '8_hour', label: '8 Hour Package', price: baseRate + eightHourPrice },
            { value: '10_hour', label: '10 Hour Package', price: baseRate + tenHourPrice },
            { value: '12_hour', label: '12 Hour Package', price: baseRate + twelveHourPrice }
        ];
        
        // Build package options HTML
        let optionsHTML = '<option value="">Select Duration Package</option>';
        
        packages.forEach(pkg => {
            const priceDisplay = pkg.price > 0 ? ` - ${pkg.price.toFixed(2)} SGD` : '';
            optionsHTML += `<option value="${pkg.value}">${pkg.label}${priceDisplay}</option>`;
        });
        
        packageSelect.innerHTML = optionsHTML;
        
        console.log('Updated package prices:', isNightTime ? 'Night Time' : 'Day Time', packages);
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
                            <select class="form-select" name="day${day}_guide_${newIndex}_package" id="day${day}_guide_${newIndex}_package">
                                <option value="">Select Duration</option>
                            </select>
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
         
         showNotification(`Guest selection updated: ${total} total guests`, 'success');
     };
     
     // Notification helper function
     function showNotification(message, type = 'info') {
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

     // Initialize
    updateMainGuestSummary();
     updateGuestSummary();
    updateAdultsCount();
 });
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