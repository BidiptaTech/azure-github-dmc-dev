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
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button" id="guestDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span id="guestSummary">4 adults (2 male, 2 female) -0 children -0 infants</span>
                                        </button>
                                        <div class="dropdown-menu p-3" style="min-width: 340px;" aria-labelledby="guestDropdown">
                                            <!-- Guest Summary Header -->
                                            <div class="mb-3 p-2 bg-light rounded">
                                                <small class="text-muted fw-bold d-block mb-1">
                                                    <i class="ri-team-line me-1"></i>GUESTS
                                                </small>
                                                <div id="guestDetailSummary" class="fw-semibold text-primary">
                                                    4 adults (2 male, 2 female) - 0 children - 0 infants
                                                </div>
                                            </div>

                                            <!-- Adults Section -->
                                            <div class="mb-3">
                                                <div class="fw-semibold mb-2">Adults</div>
                                                
                                                <!-- Male Adults -->
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-info me-2">M</span>
                                                        <span>Male</span>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <button type="button" class="btn btn-sm btn-outline-primary guest-btn-minus" data-target="male" onclick="event.stopPropagation();">-</button>
                                                        <span class="mx-3 fw-bold" id="male-count" style="min-width: 20px; text-align: center;">2</span>
                                                        <button type="button" class="btn btn-sm btn-outline-primary guest-btn-plus" data-target="male" onclick="event.stopPropagation();">+</button>
                                                    </div>
                                                </div>

                                                <!-- Female Adults -->
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-pink me-2" style="background-color: #e91e63 !important;">F</span>
                                                        <span>Female</span>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <button type="button" class="btn btn-sm btn-outline-danger guest-btn-minus" data-target="female" onclick="event.stopPropagation();">-</button>
                                                        <span class="mx-3 fw-bold" id="female-count" style="min-width: 20px; text-align: center;">2</span>
                                                        <button type="button" class="btn btn-sm btn-outline-danger guest-btn-plus" data-target="female" onclick="event.stopPropagation();">+</button>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr class="my-2">

                                            <!-- Children -->
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <strong>Children</strong>
                                                    <br><small class="text-muted">Ages 1 - 17</small>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <button type="button" class="btn btn-sm btn-outline-success guest-btn-minus" data-target="children" onclick="event.stopPropagation();">-</button>
                                                    <span class="mx-3 fw-bold" id="children-count" style="min-width: 20px; text-align: center;">0</span>
                                                    <button type="button" class="btn btn-sm btn-outline-success guest-btn-plus" data-target="children" onclick="event.stopPropagation();">+</button>
                                                </div>
                                            </div>

                                            <!-- Infants -->
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>Infants</strong>
                                                    <br><small class="text-muted">Younger than 1 year old</small>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <button type="button" class="btn btn-sm btn-outline-warning guest-btn-minus" data-target="infants" onclick="event.stopPropagation();">-</button>
                                                    <span class="mx-3 fw-bold" id="infants-count" style="min-width: 20px; text-align: center;">0</span>
                                                    <button type="button" class="btn btn-sm btn-outline-warning guest-btn-plus" data-target="infants" onclick="event.stopPropagation();">+</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Hidden Fields -->
                                    <input type="hidden" name="adults" id="adults" value="4">
                                    <input type="hidden" name="male" id="male" value="2">
                                    <input type="hidden" name="female" id="female" value="2">
                                    <input type="hidden" name="children" id="children" value="0">
                                    <input type="hidden" name="infants" id="infants" value="0">
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
                                    <label class="form-label fw-semibold">Select Hotel</label>
                                    <select class="form-select" id="hotelSelect">
                                        <option value="">Search hotels</option>
                                    </select>
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

            // Guest Counter Functionality
        const updateGuestCounter = (target, action) => {
            const countElement = document.getElementById(target + '-count');
            const hiddenInput = document.getElementById(target);
            let currentValue = parseInt(countElement.textContent);
            
            // Set minimum values
            let minValue = 0;
            if (target === 'male' || target === 'female') {
                // At least 1 adult (male or female) must be present
                const maleCount = parseInt(document.getElementById('male-count').textContent);
                const femaleCount = parseInt(document.getElementById('female-count').textContent);
                const totalAdults = maleCount + femaleCount;
                
                if (totalAdults <= 1 && action === 'decrease') {
                    minValue = 1; // Prevent reducing below 1 total adult
                }
            }
            
            if (action === 'increase') {
                currentValue++;
            } else if (action === 'decrease' && currentValue > minValue) {
                currentValue--;
            }
            
            countElement.textContent = currentValue;
            hiddenInput.value = currentValue;
            updateGuestSummary();
        };

        const updateGuestSummary = () => {
            const male = parseInt(document.getElementById('male').value);
            const female = parseInt(document.getElementById('female').value);
            const children = parseInt(document.getElementById('children').value);
            const infants = parseInt(document.getElementById('infants').value);
            
            // Calculate total adults
            const totalAdults = male + female;
            document.getElementById('adults').value = totalAdults;
            
            // Main summary text for button
            let summaryText = `${totalAdults} adults (${male} male, ${female} female) - ${children} children - ${infants} infants`;
            document.getElementById('guestSummary').textContent = summaryText;
            
            // Detailed summary in dropdown
            let detailSummary = `${totalAdults} adults (${male} male, ${female} female) - ${children} children - ${infants} infants`;
            document.getElementById('guestDetailSummary').textContent = detailSummary;
        };

    // Attach guest counter events
    document.querySelectorAll('.guest-btn-plus').forEach(button => {
        button.addEventListener('click', function() {
            const target = this.dataset.target;
            updateGuestCounter(target, 'increase');
        });
    });

    document.querySelectorAll('.guest-btn-minus').forEach(button => {
        button.addEventListener('click', function() {
            const target = this.dataset.target;
            updateGuestCounter(target, 'decrease');
        });
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
            alert('Please fill in all required fields before creating the tour package.');
            return;
        }
        
        // Ensure other sections are hidden initially
        document.getElementById('packageDetailsSection').style.display = 'none';
        document.getElementById('submitSection').style.display = 'none';
        
        // Set tour dates from form inputs
        tourStartDate = startDate;
        tourEndDate = endDate;
        
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
    };

    // Load hotels for city
    function loadHotelsForCity(cityName) {
        const hotelSelect = document.getElementById('hotelSelect');
        hotelSelect.innerHTML = '<option value="">Loading hotels...</option>';
        
        // You'll need to implement this API endpoint
        fetch(`/api/hotels-by-city?city=${encodeURIComponent(cityName)}`)
            .then(response => response.json())
            .then(data => {
                hotelSelect.innerHTML = '<option value="">Search hotels</option>';
                data.hotels.forEach(hotel => {
                    hotelSelect.innerHTML += `<option value="${hotel.id}">${hotel.name}</option>`;
                });
            })
            .catch(error => {
                console.error('Error loading hotels:', error);
                hotelSelect.innerHTML = '<option value="">Error loading hotels</option>';
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
             alert('Please select a hotel first.');
             return;
         }
         
         const selectedNights = document.querySelectorAll('.night-btn.active');
         if (selectedNights.length === 0) {
             alert('Please select at least one night for this hotel.');
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
         
         // Reset form
         hotelSelect.value = '';
         document.getElementById('roomTypeSelect').value = '';
         document.getElementById('bedTypeSelect').value = '';
         document.getElementById('mealPlanSelect').value = '';
         document.getElementById('numberOfRooms').value = '1';
         
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
                              <button type="button" class="btn btn-sm btn-outline-danger" onclick="addAttractionService(${day})">
                                  <i class="ri-add-line me-1"></i>Add Attraction
                              </button>
                          </div>
                          
                          <div class="card border-danger shadow-sm">
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
                                         <select class="form-select" name="day${day}_attraction">
                                             <option value="">Search Attraction</option>
                                         </select>
                                     </div>
                                     <div class="col-md-3">
                                         <label class="form-label fw-semibold">Select Guests</label>
                                         <div class="guest-selector">
                                             <div class="guest-display p-2 border rounded bg-light">
                                                 <div class="d-flex align-items-center justify-content-between">
                                                     <div class="guest-info">
                                                         <span id="day${day}_attraction_guest_summary" class="text-muted small">
                                                             1 adults (1 male), 0 children -0 infants
                                                         </span>
                                                     </div>
                                                     <button type="button" class="btn btn-sm btn-outline-primary" onclick="openGuestSelector('day${day}_attraction')">
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
                                         <input type="text" class="form-control" placeholder="Select Time Slot ID" name="day${day}_attraction_time">
                                     </div>
                                     <div class="col-md-3">
                                         <label class="form-label fw-semibold">Select Ticket</label>
                                         <select class="form-select" name="day${day}_attraction_ticket">
                                             <option value="">Select Ticket</option>
                                         </select>
                                     </div>
                                 </div>
                                 
                                                                   <div class="mt-4 pt-3 border-top">
                                      <div class="d-flex justify-content-between align-items-center">
                                          <small class="text-muted">
                                              <i class="ri-information-line me-1"></i>You can add multiple attractions for this day
                                          </small>
                                          <button type="button" class="btn btn-sm btn-outline-danger" onclick="addMoreAttractions(${day})">
                                              <i class="ri-add-line me-1"></i>Add Another Attraction
                                          </button>
                                      </div>
                                  </div>
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
                              <button type="button" class="btn btn-sm btn-outline-info" onclick="addGuideService(${day})">
                                  <i class="ri-add-line me-1"></i>Add Guide
                              </button>
                          </div>
                          
                          <div class="card border-info shadow-sm">
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
                                         <select class="form-select" name="day${day}_guide">
                                             <option value="">Search Guide</option>
                                         </select>
                                     </div>
                                     <div class="col-md-3">
                                         <label class="form-label fw-semibold">Select Guests</label>
                                         <div class="guest-selector">
                                             <div class="guest-display p-2 border rounded bg-light">
                                                 <div class="d-flex align-items-center justify-content-between">
                                                     <div class="guest-info">
                                                         <span id="day${day}_guide_guest_summary" class="text-muted small">
                                                             1 adults (1 male), 0 children -0 infants
                                                         </span>
                                                     </div>
                                                     <button type="button" class="btn btn-sm btn-outline-primary" onclick="openGuestSelector('day${day}_guide')">
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
                                         <input type="text" class="form-control" placeholder="Select Pick up Time" name="day${day}_guide_pickup_time">
                                     </div>
                                     <div class="col-md-3">
                                         <label class="form-label fw-semibold">Select Package</label>
                                         <select class="form-select" name="day${day}_guide_package">
                                             <option value="">Select Duration</option>
                                         </select>
                                     </div>
                                 </div>
                                 
                                                                   <div class="mt-4 pt-3 border-top">
                                      <div class="d-flex justify-content-between align-items-center">
                                          <small class="text-muted">
                                              <i class="ri-information-line me-1"></i>You can add multiple tour guides for this day
                                          </small>
                                          <button type="button" class="btn btn-sm btn-outline-info" onclick="addMoreGuides(${day})">
                                              <i class="ri-add-line me-1"></i>Add Another Guide
                                          </button>
                                      </div>
                                  </div>
                             </div>
                         </div>
                     </div>
                     
                     <!-- Restaurant Services -->
                     <div class="mb-4">
                         <div class="d-flex justify-content-between align-items-center mb-3">
                             <h6 class="text-success mb-0">
                                 <i class="ri-restaurant-line me-2"></i>Book Restaurant Services
                                 <small class="text-muted ms-2">Select restaurants and configure your dining experience</small>
                             </h6>
                             <button type="button" class="btn btn-sm btn-success" onclick="addRestaurant(${day})">
                                 <i class="ri-add-line me-1"></i>1 Booking
                             </button>
                         </div>
                         
                         <div class="card border-success">
                             <div class="card-body">
                                 <div class="d-flex align-items-center mb-3">
                                     <span class="badge bg-success me-2">Booking</span>
                                     <span class="badge bg-warning text-dark ms-2">1st Complete</span>
                                 </div>
                                 
                                 <div class="row g-3">
                                     <div class="col-md-3">
                                         <label class="form-label fw-semibold">Select Restaurant</label>
                                         <select class="form-select" name="day${day}_restaurant">
                                             <option value="">Search Restaurant</option>
                                         </select>
                                     </div>
                                     <div class="col-md-3">
                                         <label class="form-label fw-semibold">Select Guests</label>
                                         <div class="guest-selector">
                                             <div class="guest-display p-2 border rounded bg-light">
                                                 <div class="d-flex align-items-center justify-content-between">
                                                     <div class="guest-info">
                                                         <span id="day${day}_restaurant_guest_summary" class="text-muted small">
                                                             1 adults (1 male), 0 children -0 infants
                                                         </span>
                                                     </div>
                                                     <button type="button" class="btn btn-sm btn-outline-primary" onclick="openGuestSelector('day${day}_restaurant')">
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
                                         <label class="form-label fw-semibold">Meal Type</label>
                                         <select class="form-select" name="day${day}_meal_type">
                                             <option value="">Meal Type</option>
                                             <option value="breakfast">Breakfast</option>
                                             <option value="lunch">Lunch</option>
                                             <option value="dinner">Dinner</option>
                                         </select>
                                     </div>
                                     <div class="col-md-3">
                                         <label class="form-label fw-semibold">Specific Meal Type</label>
                                         <select class="form-select" name="day${day}_specific_meal_type">
                                             <option value="">Specific Meal Type</option>
                                         </select>
                                     </div>
                                 </div>
                                 
                                 <div class="row mt-3">
                                     <div class="col-md-12">
                                         <label class="form-label fw-semibold">
                                             <i class="ri-time-line me-1"></i>Time Slot
                                         </label>
                                         <input type="text" class="form-control" placeholder="Time Slot" name="day${day}_restaurant_time_slot">
                                     </div>
                                 </div>
                                 
                                 <div class="mt-3 text-center">
                                     <button type="button" class="btn btn-success">
                                         <i class="ri-add-line me-1"></i>Add More
                                     </button>
                                 </div>
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
         showNotification(`Additional attraction option added for Day ${day}`, 'info');
         console.log('Add more attractions for day', day);
         // Future implementation: Add another attraction form
     };
     
     window.addGuideService = function(day) {
         showNotification(`Tour guide service added for Day ${day}`, 'success');
         console.log('Add guide service for day', day);
         // Future implementation: Add dynamic form for tour guides
     };
     
     window.addMoreGuides = function(day) {
         showNotification(`Additional tour guide option added for Day ${day}`, 'info');
         console.log('Add more guides for day', day);
         // Future implementation: Add another guide form
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
                                                     <button type="button" class="btn btn-outline-primary btn-sm" onclick="updateServiceGuest('male', -1)">
                                                         <i class="ri-subtract-line"></i>
                                                     </button>
                                                     <span class="mx-3 fw-bold fs-5" id="serviceModalMale">0</span>
                                                     <button type="button" class="btn btn-outline-primary btn-sm" onclick="updateServiceGuest('male', 1)">
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
                                                     <button type="button" class="btn btn-outline-danger btn-sm" onclick="updateServiceGuest('female', -1)">
                                                         <i class="ri-subtract-line"></i>
                                                     </button>
                                                     <span class="mx-3 fw-bold fs-5" id="serviceModalFemale">0</span>
                                                     <button type="button" class="btn btn-outline-danger btn-sm" onclick="updateServiceGuest('female', 1)">
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
                                                     <button type="button" class="btn btn-outline-success btn-sm" onclick="updateServiceGuest('children', -1)">
                                                         <i class="ri-subtract-line"></i>
                                                     </button>
                                                     <span class="mx-3 fw-bold fs-5" id="serviceModalChildren">0</span>
                                                     <button type="button" class="btn btn-outline-success btn-sm" onclick="updateServiceGuest('children', 1)">
                                                         <i class="ri-add-line"></i>
                                                     </button>
                                                 </div>
                                             </div>
                                             <!-- Infants -->
                                             <div class="guest-counter">
                                                 <label class="form-label fw-semibold text-warning">
                                                     <i class="ri-user-heart-line me-1"></i>Infants
                                                     <small class="text-muted">(Under 1 year)</small>
                                                 </label>
                                                 <div class="d-flex align-items-center">
                                                     <button type="button" class="btn btn-outline-warning btn-sm" onclick="updateServiceGuest('infants', -1)">
                                                         <i class="ri-subtract-line"></i>
                                                     </button>
                                                     <span class="mx-3 fw-bold fs-5" id="serviceModalInfants">0</span>
                                                     <button type="button" class="btn btn-outline-warning btn-sm" onclick="updateServiceGuest('infants', 1)">
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
         const newValue = Math.max(0, currentValue + change);
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

     // Initialize
     updateGuestSummary();
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
</style>
@endsection 