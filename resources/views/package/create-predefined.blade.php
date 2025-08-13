@extends('layouts.layout')
@section('content')
<!-- Font Awesome for icons -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="ri-add-line me-2 text-primary"></i>Create New Package
                </h4>
                <p class="text-muted mb-0">Create a new predefined tour package</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary">
                    <i class="ri-arrow-left-line me-1"></i>Back to Packages
                </a>
            </div>
        </div>

        <div id="error-container"></div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('packages.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-information-line me-2 text-primary"></i>Basic Information</h5>
                </div>
                <x-alert />
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Package Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   name="title" value="{{ old('title') }}" required 
                                   placeholder="e.g., Singapore City Explorer">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Country/Destination <span class="text-danger">*</span></label>
                            <select class="form-select @error('destination') is-invalid @enderror" 
                                    id="country-select" name="destination" required>
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->name }}" {{ old('destination') == $country->name ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('destination')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <select class="form-select @error('city') is-invalid @enderror" 
                                    id="city-select" name="city" required disabled>
                                <option value="">Select Country First</option>
                            </select>
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('category') is-invalid @enderror" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Adventure" {{ old('category') == 'Adventure' ? 'selected' : '' }}>Adventure</option>
                                <option value="Cultural" {{ old('category') == 'Cultural' ? 'selected' : '' }}>Cultural</option>
                                <option value="City Tour" {{ old('category') == 'City Tour' ? 'selected' : '' }}>City Tour</option>
                                <option value="Beach" {{ old('category') == 'Beach' ? 'selected' : '' }}>Beach</option>
                                <option value="Heritage" {{ old('category') == 'Heritage' ? 'selected' : '' }}>Heritage</option>
                                <option value="Food & Culinary" {{ old('category') == 'Food & Culinary' ? 'selected' : '' }}>Food & Culinary</option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Duration (Days) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('duration_days') is-invalid @enderror" 
                                   name="duration_days" value="{{ old('duration_days') }}" min="1" required>
                            @error('duration_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Package Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('package_type') is-invalid @enderror" name="package_type" required>
                                <option value="">Select Package Type</option>
                                <option value="single" {{ old('package_type') == 'single' ? 'selected' : '' }}>Single</option>
                                <option value="couple" {{ old('package_type') == 'couple' ? 'selected' : '' }}>Couple</option>
                            </select>
                            @error('package_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      name="description" rows="3" 
                                      placeholder="Brief description of the package...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing & Capacity -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-money-dollar-circle-line me-2 text-success"></i>Pricing & Capacity</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label" id="adult-price-label">Adult Price (SGD) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">SGD</span>
                                <input type="number" class="form-control @error('price_adult') is-invalid @enderror" 
                                       name="price_adult" value="{{ old('price_adult') }}" min="0" step="0.01" required>
                            </div>
                            <div id="couple-price-note" class="mt-2 d-none">
                                <small class="text-info fw-medium">
                                    <i class="ri-information-line me-1"></i>
                                    Price is for two persons
                                </small>
                            </div>
                            @error('price_adult')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3" id="senior-price-section">
                            <label class="form-label">Senior Price (SGD)</label>
                            <div class="input-group">
                                <span class="input-group-text">SGD</span>
                                <input type="number" class="form-control @error('price_senior') is-invalid @enderror" 
                                       name="price_senior" value="{{ old('price_senior') }}" min="0" step="0.01">
                            </div>
                            @error('price_senior')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3" id="child-price-section">
                            <label class="form-label">Child Price (SGD)</label>
                            <div class="input-group">
                                <span class="input-group-text">SGD</span>
                                <input type="number" class="form-control @error('price_child') is-invalid @enderror" 
                                       name="price_child" value="{{ old('price_child') }}" min="0" step="0.01">
                            </div>
                            @error('price_child')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Maximum PAX <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('max_pax') is-invalid @enderror" 
                                   name="max_pax" value="{{ old('max_pax') }}" min="1" required id="max-pax-input">
                            @error('max_pax')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Dates -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-calendar-line me-2 text-primary"></i>Package Availability</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                   name="start_date" value="{{ old('start_date') }}" required 
                                   min="{{ date('Y-m-d') }}">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Expiry Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                   name="expiry_date" value="{{ old('expiry_date') }}" required 
                                   min="{{ date('Y-m-d') }}">
                            @error('expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hotels Selection with Day-wise Assignment -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-hotel-line me-2 text-primary"></i>Hotels Selection</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="card shadow-sm">
                                <div class="card-header bg-primary-subtle py-2">
                                    <div class="d-flex align-items-center">
                                        <i class="ri-hotel-line me-2 text-primary"></i>
                                        <h6 class="mb-0 fw-semibold">Select Hotels</h6>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <select class="form-select" id="hotel-select" multiple disabled>
                                        <option value="">Select Hotels</option>
                                    </select>
                                    <input type="hidden" name="selected_hotels" id="selected-hotels-input">
                                    <div class="mt-2 d-flex align-items-center">
                                        <i class="ri-information-line text-primary me-2"></i>
                                        <small class="text-muted">Select hotels from the dropdown, then assign them to specific days below</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="hotel-day-assignments" class="mt-4">
                        <!-- Hotel day assignments will be dynamically populated here -->
                    </div>
                </div>
            </div>
            
            <!-- Day-wise Itinerary Builder -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-calendar-check-line me-2 text-success"></i>Day-wise Itinerary</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        Plan your itinerary for each day of the tour. The number of days is based on the duration you selected.
                    </div>
                    
                    <div id="day-itinerary-accordion" class="accordion">
                        <!-- Day-wise accordion will be dynamically generated here -->
                    </div>
                    
                    <!-- Hidden inputs for day-wise data -->
                    <input type="hidden" name="day_wise_itinerary" id="day-wise-itinerary-input">
                    <input type="hidden" name="day_1_arrival_pickup" value="0">
                    <input type="hidden" name="day_1_departure_service" value="0">
                </div>
            </div>

          
            <!-- Inclusions & Exclusions -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-list-check me-2 text-success"></i>Inclusions & Exclusions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Inclusions</label>
                            <textarea class="form-control @error('inclusions') is-invalid @enderror" 
                                      name="inclusions" rows="5" 
                                      placeholder="What's included in this package...">{{ old('inclusions') }}</textarea>
                            @error('inclusions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Exclusions</label>
                            <textarea class="form-control @error('exclusions') is-invalid @enderror" 
                                      name="exclusions" rows="5" 
                                      placeholder="What's not included...">{{ old('exclusions') }}</textarea>
                            @error('exclusions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms & Conditions -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-file-text-line me-2 text-secondary"></i>Terms & Conditions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Terms & Conditions</label>
                            <textarea class="form-control @error('terms_conditions') is-invalid @enderror" 
                                      name="terms_conditions" rows="4" 
                                      placeholder="Terms and conditions for this package...">{{ old('terms_conditions') }}</textarea>
                            @error('terms_conditions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Images -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-image-line me-2 text-primary"></i>Package Images</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Main Image -->
                        <div class="col-md-6">
                            <label class="form-label">Main Image <span class="text-danger">*</span></label>
                            <div id="main-image-drop-area" class="form-control"
                                style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px; cursor: pointer;">
                                Drag & Drop your main image here or click to upload.
                                <input type="file" id="main_image" name="main_image" accept="image/jpeg,image/png,image/jpg,image/gif" 
                                       style="display: none;" required>
                            </div>
                            <small class="text-muted mt-1">
                                <i class="fas fa-info-circle"></i> 
                                Images will be automatically compressed for faster upload.
                            </small>
                            <div id="main-image-preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                style="max-width: 100%; overflow-x: auto; white-space: nowrap;"></div>
                            @error('main_image')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Gallery Images -->
                        <div class="col-md-6">
                            <label class="form-label">Gallery Images</label>
                            <div id="gallery-drop-area" class="form-control"
                                style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px; cursor: pointer;">
                                Drag & Drop your gallery images here or click to upload.
                                <input type="file" id="gallery_images" name="gallery_images[]" accept="image/jpeg,image/png,image/jpg,image/gif" 
                                       multiple style="display: none;">
                            </div>
                            <small class="text-muted mt-1">
                                <i class="fas fa-info-circle"></i> 
                                Images will be automatically compressed for faster upload and better performance.
                            </small>
                            <div id="gallery-preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                style="max-width: 100%; overflow-x: auto; white-space: nowrap;"></div>
                            @error('gallery_images')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Package Status -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-toggle-line me-2 text-primary"></i>Package Status</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" name="status" required>
                                <option value="0" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="1" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary">
                    <i class="ri-close-line me-1"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="ri-save-line me-1"></i>Create Package
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<!-- jQuery (required for Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('#country-select, #city-select').select2();
    $('#hotel-select').select2({
        multiple: true,
        placeholder: 'Select hotels'
    });
    $('#guide-select').select2({
        placeholder: 'Select guide'
    });
    
    // Package type change handler
    $('select[name="package_type"]').on('change', function() {
        const packageType = $(this).val();
        handlePackageTypeChange(packageType);
    });
    
    // Function to handle package type changes
    function handlePackageTypeChange(packageType) {
        const couplePriceNote = $('#couple-price-note');
        const adultPriceLabel = $('#adult-price-label');
        const seniorPriceSection = $('#senior-price-section');
        const childPriceSection = $('#child-price-section');
        const maxPaxInput = $('#max-pax-input');
        
        if (packageType === 'couple') {
            // Show couple price note
            couplePriceNote.removeClass('d-none');
            
            // Update adult price label
            adultPriceLabel.html('Price (SGD) <span class="text-danger">*</span>');
            
            // Hide senior and child price sections
            seniorPriceSection.addClass('d-none');
            childPriceSection.addClass('d-none');
            
            // Set max pax to 2 and make readonly
            maxPaxInput.val(2).prop('readonly', true);
            
            // Clear senior and child price values
            $('input[name="price_senior"]').val('');
            $('input[name="price_child"]').val('');
            
        } else if (packageType === 'single') {
            // Hide couple price note
            couplePriceNote.addClass('d-none');
            
            // Reset adult price label
            adultPriceLabel.html('Adult Price (SGD) <span class="text-danger">*</span>');
            
            // Show senior and child price sections
            seniorPriceSection.removeClass('d-none');
            childPriceSection.removeClass('d-none');
            
            // Reset max pax and make editable
            maxPaxInput.val('').prop('readonly', false);
            
        } else {
            // Default state - hide all notes and reset labels
            couplePriceNote.addClass('d-none');
            adultPriceLabel.html('Adult Price (SGD) <span class="text-danger">*</span>');
            seniorPriceSection.removeClass('d-none');
            childPriceSection.removeClass('d-none');
            maxPaxInput.val('').prop('readonly', false);
        }
    }
    
    // Initialize package type handling on page load
    const initialPackageType = $('select[name="package_type"]').val();
    if (initialPackageType) {
        handlePackageTypeChange(initialPackageType);
    }

    // Store selected hotels with their day assignments
    let selectedHotelsWithDays = [];
    
    // Store day-wise itinerary data
    let dayWiseItinerary = [];
    
    // Create hidden inputs for JSON data if they don't exist
    if (!$('#itinerary-json-data').length) {
        $('<input>').attr({
            type: 'hidden',
            id: 'itinerary-json-data',
            name: 'itinerary_json_data',
            value: '{}'
        }).appendTo('form');
    }
    
    if (!$('#hotel-json-data').length) {
        $('<input>').attr({
            type: 'hidden',
            id: 'hotel-json-data',
            name: 'hotel_json_data',
            value: '{}'
        }).appendTo('form');
    }
    
    if (!$('#day-wise-itinerary-input').length) {
        $('<input>').attr({
            type: 'hidden',
            id: 'day-wise-itinerary-input',
            name: 'day_wise_itinerary',
            value: '{}'
        }).appendTo('form');
    }

    // Duration change event to generate day-wise itinerary
    $('input[name="duration_days"]').on('change keyup', function() {
        const duration = parseInt($(this).val()) || 0;
        if (duration > 0) {
            generateDayWiseItinerary(duration);
            updateHotelDayAssignments();
            
            // If city is already selected, load attractions and guides for each day
            const city = $('#city-select').val();
            if (city) {
                for (let day = 1; day <= duration; day++) {
                    loadAttractionsForDay(city, day);
                    loadGuidesForDay(city, day);
                }
            }
        }
    });
    
    // Function to generate day-wise itinerary accordion
    function generateDayWiseItinerary(duration) {
        const accordionContainer = $('#day-itinerary-accordion');
        accordionContainer.empty();
        dayWiseItinerary = [];
        
        // Initialize day-wise itinerary data structure
        for (let day = 1; day <= duration; day++) {
            dayWiseItinerary.push({
                day: day,
                attractions: [],
                guide: null,
                arrival_pickup: day === 1 ? 0 : null,
                departure_service: day === duration ? 0 : null
            });
            
            // Create accordion item for each day
            const isFirstDay = day === 1;
            const isLastDay = day === duration;
            const dayId = `day-${day}`;
            const headerId = `heading-${dayId}`;
            const collapseId = `collapse-${dayId}`;
            
            const accordionItem = `
                <div class="accordion-item mb-3 shadow-sm">
                    <h2 class="accordion-header" id="${headerId}">
                        <button class="accordion-button ${day === 1 ? '' : 'collapsed'}" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#${collapseId}" 
                                aria-expanded="${day === 1 ? 'true' : 'false'}" aria-controls="${collapseId}">
                            <div class="d-flex align-items-center w-100">
                                <div class="flex-shrink-0">
                                    <span class="badge rounded-pill bg-primary-subtle text-primary p-2">
                                        <i class="ri-calendar-event-line me-1"></i>Day ${day}
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3 d-flex align-items-center">
                                    ${isFirstDay ? '<span class="badge bg-info-subtle text-info ms-2"><i class="ri-flight-land-line me-1"></i>Arrival Day</span>' : ''}
                                    ${isLastDay ? '<span class="badge bg-warning-subtle text-warning ms-2"><i class="ri-flight-takeoff-line me-1"></i>Departure Day</span>' : ''}
                                    <span class="ms-auto text-muted small" id="day-${day}-summary">No attractions selected</span>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="${collapseId}" class="accordion-collapse collapse ${day === 1 ? 'show' : ''}" 
                         aria-labelledby="${headerId}" data-bs-parent="#day-itinerary-accordion">
                        <div class="accordion-body bg-light-subtle">
                            <div class="row g-3">
                                ${isFirstDay ? `
                                <!-- Arrival Transfer Option (Only for Day 1) -->
                                <div class="col-12 mb-3">
                                    <div class="card border-info border-opacity-25 shadow-sm hover-shadow">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <span class="badge bg-info-subtle text-info p-2 rounded-circle">
                                                        <i class="ri-flight-land-line fs-5"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-0">Arrival Pickup Service</h6>
                                                    <small class="text-muted">Airport/port pickup on arrival</small>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input arrival-pickup-toggle" type="checkbox" 
                                                           id="arrival-pickup-day-${day}" data-day="${day}" value="1">
                                                    <label class="form-check-label" for="arrival-pickup-day-${day}">Include</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                ` : ''}
                                
                                ${isLastDay ? `
                                <!-- Departure Transfer Option (Only for Last Day) -->
                                <div class="col-12 mb-3">
                                    <div class="card border-warning border-opacity-25 shadow-sm hover-shadow">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <span class="badge bg-warning-subtle text-warning p-2 rounded-circle">
                                                        <i class="ri-flight-takeoff-line fs-5"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-0">Departure Service</h6>
                                                    <small class="text-muted">Airport/port dropoff on departure</small>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input departure-service-toggle" type="checkbox" 
                                                           id="departure-service-day-${day}" data-day="${day}" value="1">
                                                    <label class="form-check-label" for="departure-service-day-${day}">Include</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                ` : ''}
                                
                                <!-- Attractions Selection -->
                                <div class="col-md-12 mb-3">
                                    <div class="card shadow-sm">
                                        <div class="card-header bg-primary-subtle py-2">
                                            <div class="d-flex align-items-center">
                                                <i class="ri-map-pin-line me-2 text-primary"></i>
                                                <h6 class="mb-0 fw-semibold">Attractions for Day ${day}</h6>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <select class="form-select day-attraction-select" id="attraction-select-day-${day}" 
                                                    data-day="${day}" multiple>
                                                <option value="">Select Attractions</option>
                                            </select>
                                            <input type="hidden" name="day_${day}_attractions" id="day-${day}-attractions-input">
                                            <div class="attractions-note">
                                                <div class="d-flex align-items-start">
                                                    <i class="ri-information-line text-primary me-2 mt-1"></i>
                                                    <div>
                                                        <small class="text-muted fw-medium">Multiple Selection Allowed</small>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Selected Attractions with Service Toggles -->
                                <div class="col-12 mb-3">
                                    <div id="day-${day}-selected-attractions" class="selected-attractions-container">
                                        <!-- Selected attractions will be displayed here -->
                                    </div>
                                </div>
                                
                                <!-- Guide Selection -->
                                <div class="col-md-12 mb-3">
                                    <div class="card shadow-sm">
                                        <div class="card-header bg-success-subtle py-2">
                                            <div class="d-flex align-items-center">
                                                <i class="ri-user-voice-line me-2 text-success"></i>
                                                <h6 class="mb-0 fw-semibold">Guide for Day ${day}</h6>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <select class="form-select day-guide-select" id="guide-select-day-${day}" data-day="${day}">
                                                        <option value="">Select Guide (Optional)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <input type="hidden" name="day_${day}_guide" id="day-${day}-guide-input">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            accordionContainer.append(accordionItem);
            
            // Initialize Select2 for the day's attraction and guide selects
            $(`#attraction-select-day-${day}`).select2({
                placeholder: 'Select attractions for Day ' + day,
                multiple: true,
                width: '100%'
            });
            
            $(`#guide-select-day-${day}`).select2({
                placeholder: 'Select guide for Day ' + day,
                width: '100%'
            });
        }
        
        // Bind event handlers for day-wise elements
        bindDayWiseEventHandlers();
        
        // Bind accordion expand event to ensure Select2 works properly
        bindAccordionEvents();
        
        // Log the initialized dayWiseItinerary for debugging
        console.log('Initialized dayWiseItinerary:', dayWiseItinerary);
    }
    
    // Function to update hotel day assignments based on selected hotels
    function updateHotelDayAssignments() {
        const container = $('#hotel-day-assignments');
        container.empty();
        
        const selectedOptions = $('#hotel-select').find('option:selected');
        const duration = parseInt($('input[name="duration_days"]').val()) || 0;
        
        if (selectedOptions.length === 0 || duration === 0) {
            container.html(`
                <div class="alert alert-info d-flex align-items-center">
                    <i class="ri-information-line fs-4 me-2"></i>
                    <div>Please select hotels and specify duration to assign hotels to days.</div>
                </div>
            `);
            return;
        }
        
        // Exclude the last day (departure day) for hotel assignments
        // Ensure we have at least 1 day even if duration is 1
        const hotelDays = Math.max(1, duration > 1 ? duration - 1 : duration);
        
        let hotelAssignmentsHtml = `
            <div class="card mt-4 shadow-sm">
                <div class="card-header bg-light">
                    <div class="d-flex align-items-center">
                        <i class="ri-hotel-bed-line fs-4 me-2 text-primary"></i>
                        <h6 class="mb-0 fw-semibold">Assign Hotels to Days</h6>
                    </div>
                </div>
                <div class="card-body">
        `;
        
        if (duration > 1) {
            hotelAssignmentsHtml += `
                <div class="alert alert-info d-flex align-items-center mb-3">
                    <i class="ri-information-line me-2"></i>
                    <div>Last day (Day ${duration}) is not shown for hotel selection as it's the departure day.</div>
                </div>
            `;
        }
        
        hotelAssignmentsHtml += `
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 200px;">Hotel</th>
        `;
        
        // Generate day column headers
        for (let i = 0; i < hotelDays; i++) {
            hotelAssignmentsHtml += `
                <th class="text-center" style="min-width: 80px;">
                    <span class="badge rounded-pill bg-primary-subtle text-primary p-2">
                        <i class="ri-calendar-event-line me-1"></i>Day ${i+1}
                    </span>
                </th>`;
        }
        
        hotelAssignmentsHtml += `
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        selectedOptions.each(function() {
            const hotelData = $(this).data('hotel-data');
            if (hotelData) {
                const hotelId = hotelData.id;
                const hotelName = hotelData.name;
                
                hotelAssignmentsHtml += `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-secondary-subtle text-secondary p-2 rounded-circle me-2">
                                    <i class="ri-hotel-line"></i>
                                </span>
                                <div>
                                    <span class="fw-medium">${hotelName}</span>
                                    <small class="d-block text-muted">${hotelData.city}</small>
                                </div>
                            </div>
                        </td>
                `;
                
                // Generate day checkboxes for each hotel
                for (let i = 0; i < hotelDays; i++) {
                    const day = i + 1;
                    const isChecked = selectedHotelsWithDays.some(h => 
                        h.id === hotelId && h.days.includes(day)
                    );
                    
                    hotelAssignmentsHtml += `
                        <td class="text-center align-middle">
                            <div class="form-check d-flex justify-content-center">
                                <input class="form-check-input hotel-day-checkbox" type="checkbox" 
                                       id="hotel-${hotelId}-day-${day}" 
                                       data-hotel-id="${hotelId}" 
                                       data-hotel-name="${hotelName}"
                                       data-day="${day}"
                                       ${isChecked ? 'checked' : ''}>
                            </div>
                        </td>
                    `;
                }
                
                hotelAssignmentsHtml += `
                    </tr>
                `;
            }
        });
        
        hotelAssignmentsHtml += `
                    </tbody>
                </table>
            </div>
            <small class="text-muted d-flex align-items-center mt-2">
                <i class="ri-checkbox-circle-line me-2 text-primary"></i>
                Check the days when each hotel will be used for accommodation.
            </small>
                </div>
            </div>
        `;
        
        container.html(hotelAssignmentsHtml);
        
        // Bind event handler for hotel day checkboxes
        $('.hotel-day-checkbox').on('change', function() {
            updateSelectedHotelsWithDays();
        });
    }
    
    // Function to update the selected hotels with their day assignments
    function updateSelectedHotelsWithDays() {
        selectedHotelsWithDays = [];
        let hotelJsonData = {};
        
        // Get all selected hotels
        const selectedOptions = $('#hotel-select').find('option:selected');
        const duration = parseInt($('input[name="duration_days"]').val()) || 0;
        // Exclude the last day (departure day) for hotel assignments
        const hotelDays = duration > 1 ? duration - 1 : duration;
        
        selectedOptions.each(function() {
            const hotelData = $(this).data('hotel-data');
            if (hotelData) {
                const hotelId = hotelData.id;
                const hotelName = hotelData.name;
                const hotelCity = hotelData.city;
                const mainImage = hotelData.main_image || '';
                const images = hotelData.images || [];
                
                // Find all checked days for this hotel
                const checkedDays = [];
                $(`.hotel-day-checkbox[data-hotel-id="${hotelId}"]:checked`).each(function() {
                    const day = parseInt($(this).data('day'));
                    // Only include days up to hotelDays (excluding the last day)
                    if (day <= hotelDays) {
                        checkedDays.push(day);
                    }
                });
                
                // Add to selected hotels with days
                if (checkedDays.length > 0) {
                    const hotelData = $('#hotel-select').find(`option[value="${hotelId}"]`).data('hotel-data');
                    selectedHotelsWithDays.push({
                        id: hotelId,
                        name: hotelName,
                        city: hotelCity,
                        days: checkedDays,
                        main_image: hotelData ? hotelData.main_image : ''  // Changed from 'image' to 'main_image' for consistency
                    });
                    
                    // Add to hotel JSON data with hotel_unique_id as key
                    hotelJsonData[hotelId] = {
                        name: hotelName,
                        city: hotelCity,
                        main_image: mainImage,
                        images: images,
                        selected_days: checkedDays
                    };
                }
            }
        });
        
        // Update hidden input with JSON data for backward compatibility
        $('#selected-hotels-input').val(JSON.stringify(selectedHotelsWithDays));
        
        // Convert hotelJsonData object to JSON string
        const hotelJsonString = JSON.stringify(hotelJsonData);
        
        // Create or update the hotel JSON data hidden input
        if ($('#hotel-json-data').length) {
            $('#hotel-json-data').val(hotelJsonString);
        } else {
            $('<input>').attr({
                type: 'hidden',
                id: 'hotel-json-data',
                name: 'hotel_json_data',
                value: hotelJsonString
            }).appendTo('form');
        }
        
        // Debug log
        console.log('Updated hotel JSON data:', hotelJsonString);
    }
    
            // Function to bind accordion events for proper Select2 initialization
        function bindAccordionEvents() {
            // Handle accordion expand events to ensure Select2 works properly
            $('#day-itinerary-accordion').on('shown.bs.collapse', function (e) {
                const dayId = $(e.target).attr('id');
                const day = dayId.replace('collapse-day-', '');
                
                // Small delay to ensure accordion animation completes
                setTimeout(() => {
                    // Reinitialize Select2 for the expanded day to ensure proper width
                    if ($(`#attraction-select-day-${day}`).length) {
                        $(`#attraction-select-day-${day}`).select2('destroy');
                        $(`#attraction-select-day-${day}`).select2({
                            placeholder: 'Select attractions for Day ' + day,
                            multiple: true,
                            width: '100%'
                        });
                    }
                    
                    if ($(`#guide-select-day-${day}`).length) {
                        $(`#guide-select-day-${day}`).select2('destroy');
                        $(`#guide-select-day-${day}`).select2({
                            placeholder: 'Select guide for Day ' + day,
                            width: '100%'
                        });
                    }
                }, 150);
            });
        }
        
        // Function to bind event handlers for day-wise elements
        function bindDayWiseEventHandlers() {
        // Arrival pickup toggle
        $('.arrival-pickup-toggle').on('change', function() {
            const day = parseInt($(this).data('day'));
            const isChecked = $(this).is(':checked');
            
            // Update day-wise itinerary data
            if (day === 1 && dayWiseItinerary.length >= 1) {
                dayWiseItinerary[0].arrival_pickup = isChecked ? 1 : 0;
            }
            
            // Update hidden input for form submission
            $('input[name="day_1_arrival_pickup"]').val(isChecked ? 1 : 0);
        });
        
        // Departure service toggle
        $('.departure-service-toggle').on('change', function() {
            const day = parseInt($(this).data('day'));
            const isChecked = $(this).is(':checked');
            const duration = parseInt($('input[name="duration_days"]').val()) || 0;
            
            // Update day-wise itinerary data
            if (day === duration && dayWiseItinerary.length >= duration) {
                dayWiseItinerary[duration - 1].departure_service = isChecked ? 1 : 0;
            }
            
            // Update hidden input for form submission
            $(`input[name="day_${day}_departure_service"]`).val(isChecked ? 1 : 0);
        });
        
        // Day attraction select change
        $('.day-attraction-select').on('change', function() {
            const day = parseInt($(this).data('day'));
            const selectedAttractions = [];
            const selectedOptions = $(this).find('option:selected');
            
            // Get selected attractions data
            selectedOptions.each(function() {
                const attractionData = $(this).data('attraction-data');
                if (attractionData) {
                    console.log('Raw attraction data:', attractionData);
                    // Initialize attraction with proper structure
                    selectedAttractions.push({
                        attraction_id: attractionData.attraction_id,
                        name: attractionData.name,
                        location: attractionData.location,
                        transfer_available: 0,
                        transfer_type: 'none',
                        image: attractionData.image || ''
                    });
                    console.log('Pushed attraction with image:', selectedAttractions[selectedAttractions.length - 1]);
                }
            });
            console.log("selectedAttractions = ", selectedAttractions);
            
            // Update day-wise itinerary data
            if (dayWiseItinerary.length >= day) {
                // Preserve any existing transfer settings if the attraction is still selected
                const existingAttractions = dayWiseItinerary[day - 1].attractions || [];
                // Map new attractions, preserving transfer settings for existing ones
                const updatedAttractions = selectedAttractions.map(newAttraction => {
                    // Check if this attraction already exists with transfer settings
                    const existingAttraction = existingAttractions.find(
                        a => a.attraction_id === newAttraction.attraction_id
                    );
                    
                    if (existingAttraction) {
                        // Preserve existing transfer settings and add image
                        return {
                            ...newAttraction,
                            transfer_available: existingAttraction.transfer_available || 0,
                            transfer_type: existingAttraction.transfer_type || 'none',
                            image: newAttraction.image
                        };
                    }
                    
                    // Use default settings for new attractions
                    return {
                        ...newAttraction,
                        image: newAttraction.image
                    };
                });
                console.log("updatedAttractions = ", updatedAttractions);
                // Update the attractions array
                dayWiseItinerary[day - 1].attractions = updatedAttractions;
                
                console.log(`Day ${day} attractions updated from select:`, JSON.parse(JSON.stringify(updatedAttractions)));
            }
            
            // Update hidden input for form submission
            $(`#day-${day}-attractions-input`).val(JSON.stringify(dayWiseItinerary[day - 1].attractions));
            
            // Update the selected attractions display with service toggles
            updateSelectedAttractionsDisplay(day, dayWiseItinerary[day - 1].attractions);
            
            // Update the day summary in accordion header
            updateDaySummary(day, dayWiseItinerary[day - 1].attractions);
        });
        
        // Function to update the day summary in accordion header
        function updateDaySummary(day, attractions) {
            const summaryElement = $(`#day-${day}-summary`);
            
            if (attractions.length === 0) {
                summaryElement.html('<i class="ri-error-warning-line text-warning me-1"></i>No attractions selected');
                return;
            }
            
            if (attractions.length === 1) {
                summaryElement.html(`<i class="ri-map-pin-line text-success me-1"></i>${attractions[0].name}`);
            } else {
                summaryElement.html(`<i class="ri-map-pin-line text-success me-1"></i>${attractions.length} attractions selected`);
            }
        }
        
        // Day guide select change
        $('.day-guide-select').on('change', function() {
            const day = parseInt($(this).data('day'));
            const selectedOption = $(this).find('option:selected');
            const guideData = selectedOption.data('guide-data');
            
            let selectedGuide = null;
            
            if (guideData && selectedOption.val()) {
                selectedGuide = {
                    id: guideData.id,
                    name: guideData.name,
                    languages: guideData.languages,
                    contact_no: guideData.contact_no
                };
            }
            
            // Update day-wise itinerary data
            if (dayWiseItinerary.length >= day) {
                dayWiseItinerary[day - 1].guide = selectedGuide;
            }
            
            // Update hidden input for form submission
            $(`#day-${day}-guide-input`).val(selectedGuide ? JSON.stringify(selectedGuide) : '');
        });
    }
    
    // Function to update the selected attractions display with service toggles
    function updateSelectedAttractionsDisplay(day, attractions) {
        const container = $(`#day-${day}-selected-attractions`);
        container.empty();
        
        // Debug log for attractions data
        console.log(`updateSelectedAttractionsDisplay for Day ${day}:`, JSON.parse(JSON.stringify(attractions)));
        
        if (attractions.length === 0) {
            container.html('<div class="text-muted">No attractions selected for this day.</div>');
            return;
        }
        
        let attractionsHtml = '';
        
        attractions.forEach((attraction, index) => {
            // Set default values if not present
            attraction.transfer_available = attraction.transfer_available || 0;
            attraction.transfer_type = attraction.transfer_type || 'none';
            
            // Debug log for each attraction
            console.log(`Day ${day}, Attraction ${index}:`, {
                id: attraction.attraction_id,
                name: attraction.name,
                location: attraction.location,
                transfer_available: attraction.transfer_available,
                transfer_type: attraction.transfer_type
            });
            
            attractionsHtml += `
                <div class="card mb-3 border-light hover-shadow">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <span class="badge bg-info-subtle text-info p-2 rounded-circle">
                                    <i class="ri-map-pin-line fs-5"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 fw-semibold">${attraction.name}</h6>
                                <small class="text-muted"><i class="ri-building-line me-1"></i>${attraction.location}</small>
                            </div>
                        </div>
                        
                        <!-- Transfer Options -->
                        <div class="mt-2 pt-3 border-top border-light">
                            <div class="d-flex align-items-center mb-2">
                                <div class="flex-shrink-0 me-2">
                                    <span class="badge bg-primary-subtle text-primary p-1 rounded">
                                        <i class="ri-taxi-line"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="form-check-label fw-medium cursor-pointer" for="attraction-${attraction.attraction_id}-day-${day}-transfer">
                                        Transfer Service Available
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input attraction-transfer-toggle" type="checkbox" 
                                           id="attraction-${attraction.attraction_id}-day-${day}-transfer" 
                                           data-day="${day}" 
                                           data-attraction-id="${attraction.attraction_id}" 
                                           data-index="${index}"
                                           ${attraction.transfer_available === 1 ? 'checked' : ''}>
                                </div>
                            </div>
                            
                            <div class="transfer-options-${attraction.attraction_id}-day-${day} transfer-options-container ms-4" 
                                 style="${attraction.transfer_available === 1 ? '' : 'display: none;'}">
                                <div class="mb-2">
                                    <small class="text-muted fw-medium">Select transfer type:</small>
                                </div>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input attraction-transfer-type" type="radio" 
                                               name="transfer-type-${attraction.attraction_id}-day-${day}" 
                                               id="one-way-${attraction.attraction_id}-day-${day}" 
                                               value="one_way"
                                               data-day="${day}" 
                                               data-attraction-id="${attraction.attraction_id}" 
                                               data-index="${index}"
                                               ${attraction.transfer_type === 'one_way' ? 'checked' : ''}>
                                        <label class="form-check-label d-flex align-items-center" for="one-way-${attraction.attraction_id}-day-${day}">
                                            <i class="ri-arrow-right-line me-1 text-success"></i>
                                            One-way
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input attraction-transfer-type" type="radio" 
                                               name="transfer-type-${attraction.attraction_id}-day-${day}" 
                                               id="both-way-${attraction.attraction_id}-day-${day}" 
                                               value="both_way"
                                               data-day="${day}" 
                                               data-attraction-id="${attraction.attraction_id}" 
                                               data-index="${index}"
                                               ${attraction.transfer_type === 'both_way' ? 'checked' : ''}>
                                        <label class="form-check-label d-flex align-items-center" for="both-way-${attraction.attraction_id}-day-${day}">
                                            <i class="ri-arrow-left-right-line me-1 text-primary"></i>
                                            Round-trip
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.html(attractionsHtml);
        
        // Bind event handlers for attraction transfer toggles
        $(`.attraction-transfer-toggle[data-day="${day}"]`).on('change', function() {
            const index = parseInt($(this).data('index'));
            const attractionId = $(this).data('attraction-id');
            const isChecked = $(this).is(':checked');
            
            // Show/hide transfer options based on toggle
            $(`.transfer-options-${attractionId}-day-${day}`).toggle(isChecked);
            
            // Update attraction transfer status
            if (dayWiseItinerary.length >= day && dayWiseItinerary[day - 1].attractions.length > index) {
                dayWiseItinerary[day - 1].attractions[index].transfer_available = isChecked ? 1 : 0;
                
                // Reset transfer type if transfer is disabled
                if (!isChecked) {
                    dayWiseItinerary[day - 1].attractions[index].transfer_type = 'none';
                } else if (dayWiseItinerary[day - 1].attractions[index].transfer_type === 'none') {
                    // Default to one_way if none was selected before
                    dayWiseItinerary[day - 1].attractions[index].transfer_type = 'one_way';
                    $(`#one-way-${attractionId}-day-${day}`).prop('checked', true);
                }
                
                // Update hidden input
                $(`#day-${day}-attractions-input`).val(JSON.stringify(dayWiseItinerary[day - 1].attractions));
                
                // Log the updated data for debugging
                console.log(`Day ${day} attractions updated after toggle:`, JSON.parse(JSON.stringify(dayWiseItinerary[day - 1].attractions)));
            }
        });
        
        // Bind event handlers for attraction transfer type radios
        $(`.attraction-transfer-type[data-day="${day}"]`).on('change', function() {
            const index = parseInt($(this).data('index'));
            const transferType = $(this).val();
            
            // Update attraction transfer type
            if (dayWiseItinerary.length >= day && dayWiseItinerary[day - 1].attractions.length > index) {
                dayWiseItinerary[day - 1].attractions[index].transfer_type = transferType;
                
                // Update hidden input
                $(`#day-${day}-attractions-input`).val(JSON.stringify(dayWiseItinerary[day - 1].attractions));
                
                // Log the updated data for debugging
                console.log(`Day ${day} attractions updated after transfer type change:`, JSON.parse(JSON.stringify(dayWiseItinerary[day - 1].attractions)));
            }
        });
    }

    // Toggle card highlight when port transfer checkboxes change
    $('#entry-port').change(function() {
        if($(this).is(':checked')) {
            $(this).closest('.card').addClass('border-primary').removeClass('border-primary-subtle');
            $(this).closest('.card').find('.badge').addClass('bg-primary').removeClass('bg-primary-subtle');
        } else {
            $(this).closest('.card').removeClass('border-primary').addClass('border-primary-subtle');
            $(this).closest('.card').find('.badge').removeClass('bg-primary').addClass('bg-primary-subtle');
        }
    });

    $('#exit-port').change(function() {
        if($(this).is(':checked')) {
            $(this).closest('.card').addClass('border-danger').removeClass('border-danger-subtle');
            $(this).closest('.card').find('.badge').addClass('bg-danger').removeClass('bg-danger-subtle');
        } else {
            $(this).closest('.card').removeClass('border-danger').addClass('border-danger-subtle');
            $(this).closest('.card').find('.badge').removeClass('bg-danger').addClass('bg-danger-subtle');
        }
    });
    
    // Toggle meal option cards
    $('.meal-toggle').change(function() {
        const mealType = $(this).data('meal-type');
        const badgeColor = $(this).data('badge-color');
        
        if($(this).is(':checked')) {
            $(this).closest('.card').addClass('border-' + badgeColor).removeClass('border-' + badgeColor + '-opacity-25');
            $(this).closest('.card').find('.badge').addClass('bg-' + badgeColor).removeClass('bg-' + badgeColor + '-subtle');
        } else {
            $(this).closest('.card').removeClass('border-' + badgeColor).addClass('border-' + badgeColor + '-opacity-25');
            $(this).closest('.card').find('.badge').removeClass('bg-' + badgeColor).addClass('bg-' + badgeColor + '-subtle');
        }
    });

    // Country change event
    $('#country-select').on('change', function() {
        const country = $(this).val();
        const citySelect = $('#city-select');
        
        // Reset dependent dropdowns
        citySelect.empty().prop('disabled', true);
        $('#hotel-select').empty().prop('disabled', true);
        resetDayWiseSelects();
        
        if (country) {
            citySelect.prop('disabled', false);
            
            $.ajax({
                url: `{{ env('APP_URL') }}/cities/${encodeURIComponent(country)}`,
                method: 'GET',
                success: function(response) {
                    citySelect.empty().append('<option value="">Select City</option>');
                    response.forEach(function(city) {
                        citySelect.append(new Option(city.name, city.name));
                    });
                    citySelect.trigger('change');
                }
            });
        }
    });

    // City change event
    $('#city-select').on('change', function() {
        const city = $(this).val();
        const hotelSelect = $('#hotel-select');
        
        hotelSelect.empty().prop('disabled', true);
        resetDayWiseSelects();
        
        if (city) {
            // Load hotels
            $.ajax({
                url: `{{ env('APP_URL') }}/hotel-city/${encodeURIComponent(city)}`,
                method: 'GET',
                success: function(response) {
                    hotelSelect.prop('disabled', false);
                    response.forEach(function(hotel) {
                        const option = new Option(hotel.name, hotel.hotel_unique_id);
                        $(option).data('hotel-data', {
                            id: hotel.hotel_unique_id,
                            name: hotel.name,
                            city: hotel.city,
                            main_image: hotel.main_image || ''
                        });
                        hotelSelect.append(option);
                    });
                    
                    // Initialize hotel day assignments after loading hotels
                    if (parseInt($('input[name="duration_days"]').val()) > 0) {
                        updateHotelDayAssignments();
                    }
                }
            });

            // Load attractions for each day
            const duration = parseInt($('input[name="duration_days"]').val()) || 0;
            if (duration > 0) {
                for (let day = 1; day <= duration; day++) {
                    loadAttractionsForDay(city, day);
                    loadGuidesForDay(city, day);
                }
            }
        }
    });
    
    // Function to load attractions for a specific day
    function loadAttractionsForDay(city, day) {
        const attractionSelect = $(`#attraction-select-day-${day}`);
        attractionSelect.empty();
        
        $.ajax({
            url: `{{ env('APP_URL') }}/attractions/${encodeURIComponent(city)}`,
            method: 'GET',
            success: function(response) {
                response.forEach(function(attraction) {
                    const option = new Option(attraction.name, attraction.attraction_id);
                    $(option).data('attraction-data', {
                        attraction_id: attraction.attraction_id,
                        name: attraction.name,
                        location: attraction.location,
                        image: attraction.master_image || ''
                    });
                    attractionSelect.append(option);
                });
            }
        });
    }
    
    // Function to load guides for a specific day
    function loadGuidesForDay(city, day) {
        const guideSelect = $(`#guide-select-day-${day}`);
        guideSelect.empty().append('<option value="">Select Guide</option>');
        
        $.ajax({
            url: `{{ env('APP_URL') }}/guides/${encodeURIComponent(city)}`,
            method: 'GET',
            success: function(response) {
                response.forEach(function(guide) {
                    const option = new Option(`${guide.name} (${guide.languages})`, guide.guide_id);
                    $(option).data('guide-data', {
                        id: guide.guide_id,
                        name: guide.name,
                        languages: guide.languages,
                        contact_no: guide.contact_no,
                    });
                    guideSelect.append(option);
                });
            }
        });
    }
    
    // Function to reset all day-wise select elements
    function resetDayWiseSelects() {
        const duration = parseInt($('input[name="duration_days"]').val()) || 0;
        
        for (let day = 1; day <= duration; day++) {
            $(`#attraction-select-day-${day}`).empty();
            $(`#guide-select-day-${day}`).empty().append('<option value="">Select Guide</option>');
            $(`#day-${day}-selected-attractions`).empty();
        }
    }

    // Hotel selection handler
    $('#hotel-select').on('change', function() {
        console.log("Hotel selection changed");
        const selectedOptions = $(this).find('option:selected');
        console.log("Selected hotel options:", selectedOptions.length);
        updateHotelDayAssignments();
    });
    
    // Form submission handler to compile all day-wise itinerary data
    $('form').on('submit', function(e) {
        // Prevent default form submission
        e.preventDefault();
        
        // Create hierarchical itinerary JSON structure
        const itineraryJson = {};
        
                    // Get duration
            const duration = parseInt($('input[name="duration_days"]').val()) || 0;
            
            // Get selected hotels data
            const selectedHotels = {};
            $('#hotel-select option:selected').each(function() {
                const hotelData = $(this).data('hotel-data');
                if (hotelData) {
                    selectedHotels[hotelData.id] = hotelData;
                }
            });
            
            // Debug log the dayWiseItinerary array before processing
            console.log('dayWiseItinerary before processing:', JSON.parse(JSON.stringify(dayWiseItinerary)));
            console.log('Selected hotels data:', selectedHotels);
            
            // Process each day's data
            for (let day = 1; day <= duration; day++) {
                // Get hotels assigned to this day
                const dayHotels = selectedHotelsWithDays
                    .filter(hotel => hotel.days.includes(day))
                    .map(hotel => ({
                        id: hotel.id,
                        name: hotel.name,
                        city: hotel.city,
                        main_image: selectedHotels[hotel.id] ? selectedHotels[hotel.id].main_image : ''
                    }));
                
                // Initialize day data object
                itineraryJson[day] = {
                    attractions: [],
                    hotels: dayHotels,
                    guide: null,
                    arrival_pickup: day === 1 ? 0 : null,
                    departure_service: day === duration ? 0 : null
                };
            
            // Get attractions data
            if (dayWiseItinerary.length >= day && dayWiseItinerary[day - 1].attractions) {
                console.log(`Day ${day} attractions before mapping:`, JSON.parse(JSON.stringify(dayWiseItinerary[day - 1].attractions)));
                
                // Map attractions with the correct property names
                itineraryJson[day].attractions = dayWiseItinerary[day - 1].attractions.map(attraction => {
                    // Ensure we have the correct property names for the itinerary JSON
                                    console.log('Original attraction before mapping:', attraction);
                const mappedAttraction = {
                    ...attraction,  // Preserve all existing properties
                    id: attraction.attraction_id || attraction.id,
                    name: attraction.name,
                    city: attraction.location,
                    location: attraction.location,
                    transfer_available: attraction.transfer_available || 0,
                    transfer_type: attraction.transfer_type || 'none'
                    };
                    console.log(`Day ${day}, mapped attraction:`, mappedAttraction);
                    return mappedAttraction;
                });
            }
            
            // Get guide data
            if (dayWiseItinerary.length >= day && dayWiseItinerary[day - 1].guide) {
                itineraryJson[day].guide = dayWiseItinerary[day - 1].guide;
            }
            
            // Get arrival pickup data (only for day 1)
            if (day === 1) {
                itineraryJson[day].arrival_pickup = $('input[name="day_1_arrival_pickup"]').val() === '1' ? 1 : 0;
            }
            
            // Get departure service data (only for last day)
            if (day === duration) {
                itineraryJson[day].departure_service = $(`.departure-service-toggle[data-day="${day}"]`).is(':checked') ? 1 : 0;
            }
        }
        
        // Debug log the final itinerary JSON
        console.log('Final itinerary JSON:', itineraryJson);
        
        // Convert itineraryJson object to JSON string
        const itineraryJsonString = JSON.stringify(itineraryJson);
        
        // Create or update the itinerary JSON data hidden input
        if ($('#itinerary-json-data').length) {
            $('#itinerary-json-data').val(itineraryJsonString);
        } else {
            $('<input>').attr({
                type: 'hidden',
                id: 'itinerary-json-data',
                name: 'itinerary_json_data',
                value: itineraryJsonString
            }).appendTo('form');
        }
        
        // Process hotel JSON data
        const hotelJsonData = {};
        selectedHotelsWithDays.forEach(hotel => {
            // Get the original hotel data from the select option
            const hotelData = $('#hotel-select').find(`option[value="${hotel.id}"]`).data('hotel-data');
            console.log('Original hotel data:', hotelData);
            
            hotelJsonData[hotel.id] = {
                name: hotel.name,
                city: hotel.city,
                selected_days: hotel.days,
                main_image: hotelData ? hotelData.main_image : '',  // Using 'main_image' consistently
            };
        });
        
        // Convert hotelJsonData object to JSON string
        const hotelJsonString = JSON.stringify(hotelJsonData);
        
        // Create or update the hotel JSON data hidden input
        if ($('#hotel-json-data').length) {
            $('#hotel-json-data').val(hotelJsonString);
        } else {
            $('<input>').attr({
                type: 'hidden',
                id: 'hotel-json-data',
                name: 'hotel_json_data',
                value: hotelJsonString
            }).appendTo('form');
        }
        
        // Debug log the hotel JSON data
        console.log('Hotel JSON data:', hotelJsonString);
        
        // Prepare day-wise itinerary data for submission (keep for backward compatibility)
        const compiledData = {
            hotels: selectedHotelsWithDays,
            itinerary: dayWiseItinerary
        };
        console.log("compiledData = ", compiledData);
        
        // Update the hidden input with the compiled data
        $('#day-wise-itinerary-input').val(JSON.stringify(compiledData));
        
        // Debug log all form data being submitted
        const formData = new FormData(this);
        const formDataObj = {};
        formData.forEach((value, key) => {
            formDataObj[key] = value;
        });
        console.log('Form data being submitted:', formDataObj);
        
        // Now submit the form
        this.submit();
    });
    
    // Initialize day-wise itinerary if duration is already set
    const initialDuration = parseInt($('input[name="duration_days"]').val()) || 0;
    if (initialDuration > 0) {
        generateDayWiseItinerary(initialDuration);
        
        // If city is already selected, load hotels and update assignments
        const selectedCity = $('#city-select').val();
        if (selectedCity) {
            // Load hotels for the selected city
            $.ajax({
                url: `{{ env('APP_URL') }}/hotel-city/${encodeURIComponent(selectedCity)}`,
                method: 'GET',
                success: function(response) {
                    const hotelSelect = $('#hotel-select');
                    hotelSelect.prop('disabled', false);
                    
                    response.forEach(function(hotel) {
                        console.log('Raw hotel data:', hotel);
                        const option = new Option(hotel.name, hotel.hotel_unique_id);
                        $(option).data('hotel-data', {
                            id: hotel.hotel_unique_id,
                            name: hotel.name,
                            city: hotel.city,
                            main_image: hotel.main_image
                        });
                        hotelSelect.append(option);
                    });
                    
                    // Initialize hotel day assignments
                    updateHotelDayAssignments();
                    
                    // Load attractions and guides for each day
                    for (let day = 1; day <= initialDuration; day++) {
                        loadAttractionsForDay(selectedCity, day);
                        loadGuidesForDay(selectedCity, day);
                    }
                }
            });
        }
    }
});
</script>

<!-- Image Compression Scripts -->
<script>
    // Main Image Compression Logic
    const mainImageDropArea = document.getElementById('main-image-drop-area');
    const mainImageInput = document.getElementById('main_image');
    const mainImagePreviewContainer = document.getElementById('main-image-preview-container');
    let mainImageFile = null;

    // Open file picker on click
    mainImageDropArea.addEventListener('click', () => mainImageInput.click());

    // Handle drag events for main image
    mainImageDropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        mainImageDropArea.style.backgroundColor = '#e3f2fd';
    });

    mainImageDropArea.addEventListener('dragleave', () => {
        mainImageDropArea.style.backgroundColor = 'white';
    });

    mainImageDropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        mainImageDropArea.style.backgroundColor = 'white';
        mainHandleFiles(e.dataTransfer.files);
    });

    // Handle file input change for main image
    mainImageInput.addEventListener('change', () => {
        mainHandleFiles(mainImageInput.files);
    });

    // Process and display main image files
    async function mainHandleFiles(files) {
        // Show compression progress
        showCompressionProgress('main');
        
        for (const file of Array.from(files)) {
            if (file.type.startsWith('image/')) {
                try {
                    // Compress the image
                    const compressedFile = await compressImage(file);
                    
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        // If an image already exists, remove it before adding the new one
                        if (mainImageFile) {
                            mainImagePreviewContainer.innerHTML = ''; // Clear the existing preview
                            mainImageFile = null; // Reset the file
                        }
                        mainImageFile = compressedFile;
                        mainImagePreview(e.target.result);
                        
                        // Update the file input with compressed file
                        const dt = new DataTransfer();
                        dt.items.add(compressedFile);
                        mainImageInput.files = dt.files;
                    };
                    reader.readAsDataURL(compressedFile);
                } catch (error) {
                    console.error('Error compressing image:', error);
                    alert(`Error processing ${file.name}. Please try again.`);
                }
            } else {
                alert(`${file.name} is not a valid image file.`);
            }
        }
        
        // Hide compression progress
        hideCompressionProgress('main');
    }

    // Gallery Images Compression Logic
    const galleryDropArea = document.getElementById('gallery-drop-area');
    const galleryInput = document.getElementById('gallery_images');
    const galleryPreviewContainer = document.getElementById('gallery-preview-container');
    let galleryFiles = []; // Store all gallery files
    const MAX_VISIBLE_GALLERY_IMAGES = 3; // Maximum number of visible images
    let showAllGalleryImages = false; // Toggle for showing all images

    // Trigger file input on click
    galleryDropArea.addEventListener('click', () => galleryInput.click());

    // Handle file input change
    galleryInput.addEventListener('change', () => handleGalleryFiles(galleryInput.files));

    // Handle drag-and-drop events for gallery
    galleryDropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        galleryDropArea.style.borderColor = '#000';
    });

    galleryDropArea.addEventListener('dragleave', () => {
        galleryDropArea.style.borderColor = '#ccc';
    });

    galleryDropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        galleryDropArea.style.borderColor = '#ccc';
        handleGalleryFiles(e.dataTransfer.files);
    });

    async function handleGalleryFiles(newFiles) {
        // Show loading message
        const loadingDiv = document.createElement('div');
        loadingDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Compressing images for faster upload...</div>';
        galleryPreviewContainer.appendChild(loadingDiv);

        // Process files sequentially to avoid overwhelming the browser
        for (const file of Array.from(newFiles)) {
            if (file.type.startsWith('image/')) {
                try {
                    // Check file size before compression
                    const fileSizeMB = file.size / 1024 / 1024;
                    
                    // Only compress if file is larger than 1MB or if we already have many files
                    let finalFile = file;
                    if (fileSizeMB > 1 || galleryFiles.length >= 3) {
                        finalFile = await compressImage(file, 0.7, 1600, 1200); // More aggressive compression for multiple files
                    }
                    
                    // Check total size limit (keep under 80MB total)
                    const currentTotalSize = galleryFiles.reduce((total, f) => total + f.size, 0);
                    const totalSizeMB = (currentTotalSize + finalFile.size) / 1024 / 1024;
                    
                    if (totalSizeMB > 80) {
                        alert(`Total upload size would exceed 80MB limit. Please remove some images or upload in smaller batches.`);
                        break;
                    }
                    
                    galleryFiles.push(finalFile);
                } catch (error) {
                    console.error('Error processing image:', error);
                    alert(`Error processing ${file.name}. Please try again.`);
                }
            } else {
                alert(`${file.name} is not a valid image file.`);
            }
        }
        
        // Remove loading message and update display
        loadingDiv.remove();
        updateGalleryFileList();
    }

    // Image compression function
    function compressImage(file, quality = 0.8, maxWidth = 1920, maxHeight = 1080) {
        return new Promise((resolve, reject) => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();
            
            img.onload = function() {
                // Calculate new dimensions while maintaining aspect ratio
                let { width, height } = img;
                
                if (width > height) {
                    if (width > maxWidth) {
                        height = (height * maxWidth) / width;
                        width = maxWidth;
                    }
                } else {
                    if (height > maxHeight) {
                        width = (width * maxHeight) / height;
                        height = maxHeight;
                    }
                }
                
                canvas.width = width;
                canvas.height = height;
                
                // Draw and compress
                ctx.drawImage(img, 0, 0, width, height);
                
                canvas.toBlob((blob) => {
                    if (blob) {
                        // Create a new File object with the same name but compressed data
                        const compressedFile = new File([blob], file.name, {
                            type: file.type,
                            lastModified: Date.now()
                        });
                        
                        console.log(`Compressed ${file.name} from ${(file.size / 1024 / 1024).toFixed(2)}MB to ${(compressedFile.size / 1024 / 1024).toFixed(2)}MB`);
                        resolve(compressedFile);
                    } else {
                        reject(new Error('Compression failed'));
                    }
                }, file.type, quality);
            };
            
            img.onerror = () => reject(new Error('Failed to load image'));
            img.src = URL.createObjectURL(file);
        });
    }

    // Show compression progress
    function showCompressionProgress(container) {
        const progressId = container + '-progress';
        const existingProgress = document.getElementById(progressId);
        
        if (!existingProgress) {
            const progressDiv = document.createElement('div');
            progressDiv.id = progressId;
            progressDiv.className = 'compression-progress';
            progressDiv.innerHTML = `
                <div class="alert alert-info d-flex align-items-center mb-3" role="alert" style="border-radius: 8px; border: 1px solid #bee5eb;">
                    <div class="spinner-border spinner-border-sm me-2" role="status" style="color: #0c5460;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div style="color: #0c5460; font-weight: 500;">
                        <i class="fas fa-compress-alt me-1"></i>
                        Compressing images for faster upload...
                    </div>
                </div>
            `;
            
            if (container === 'main') {
                mainImagePreviewContainer.appendChild(progressDiv);
            } else if (container === 'gallery') {
                galleryPreviewContainer.appendChild(progressDiv);
            }
        }
    }

    // Hide compression progress
    function hideCompressionProgress(container) {
        const progressId = container + '-progress';
        const progressDiv = document.getElementById(progressId);
        if (progressDiv) {
            progressDiv.remove();
        }
    }

    // Add main image preview
    function mainImagePreview(imageSrc) {
        const imageWrapper = document.createElement('div');
        imageWrapper.style.position = 'relative';
        imageWrapper.style.width = '100px';
        imageWrapper.style.height = '100px';
        imageWrapper.style.margin = '5px';
        imageWrapper.style.overflow = 'hidden';
        imageWrapper.style.borderRadius = '5px';

        const img = document.createElement('img');
        img.src = imageSrc;
        img.style.width = '100%';
        img.style.height = '100%';
        img.style.objectFit = 'cover';

        const deleteButton = document.createElement('button');
        deleteButton.textContent = '×';
        deleteButton.style.position = 'absolute';
        deleteButton.style.top = '2px';
        deleteButton.style.right = '2px';
        deleteButton.style.background = 'rgba(255, 0, 0, 0.8)';
        deleteButton.style.color = 'white';
        deleteButton.style.border = 'none';
        deleteButton.style.borderRadius = '50%';
        deleteButton.style.cursor = 'pointer';
        deleteButton.style.width = '20px';
        deleteButton.style.height = '20px';
        deleteButton.style.fontSize = '12px';
        deleteButton.style.lineHeight = '16px';
        deleteButton.addEventListener('click', () => {
            mainImagePreviewContainer.removeChild(imageWrapper);
            mainImageFile = null;
            mainImageInput.value = '';
        });

        imageWrapper.appendChild(img);
        imageWrapper.appendChild(deleteButton);
        mainImagePreviewContainer.appendChild(imageWrapper);
    }

    function updateGalleryFileList() {
        // Clear file list display
        galleryPreviewContainer.innerHTML = '';
        const dataTransfer = new DataTransfer();

        // Decide how many files to display based on `showAllGalleryImages`
        const visibleFiles = showAllGalleryImages ? galleryFiles : galleryFiles.slice(0, MAX_VISIBLE_GALLERY_IMAGES);

        visibleFiles.forEach((file, index) => {
            // Create a wrapper for the image and delete button
            const imageWrapper = document.createElement('div');
            imageWrapper.style.position = 'relative';
            imageWrapper.style.display = 'inline-block';
            imageWrapper.style.margin = '10px';
            imageWrapper.style.width = '100px';
            imageWrapper.style.height = '100px';

            // Create an image element for preview
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file); // Create an object URL for the file
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';

            // Create a delete button
            const deleteButton = document.createElement('button');
            deleteButton.textContent = '×';
            deleteButton.style.position = 'absolute';
            deleteButton.style.top = '2px';
            deleteButton.style.right = '2px';
            deleteButton.style.background = 'rgba(255, 0, 0, 0.8)';
            deleteButton.style.color = 'white';
            deleteButton.style.border = 'none';
            deleteButton.style.borderRadius = '50%';
            deleteButton.style.cursor = 'pointer';
            deleteButton.style.width = '20px';
            deleteButton.style.height = '20px';
            deleteButton.style.fontSize = '12px';
            deleteButton.style.lineHeight = '16px';

            // Remove file and update list on delete
            deleteButton.addEventListener('click', () => {
                const fileIndex = galleryFiles.indexOf(file);
                if (fileIndex > -1) {
                    galleryFiles.splice(fileIndex, 1);
                }
                updateGalleryFileList();
            });

            // Append image and delete button to the wrapper
            imageWrapper.appendChild(img);
            imageWrapper.appendChild(deleteButton);
            galleryPreviewContainer.appendChild(imageWrapper);

            // Add the file to the DataTransfer object
            dataTransfer.items.add(file);
        });

        // Add all files to the gallery input
        galleryInput.files = dataTransfer.files;

        // Add a "More Images" badge if there are more files and not showing all images
        if (!showAllGalleryImages && galleryFiles.length > MAX_VISIBLE_GALLERY_IMAGES) {
            const moreBadge = document.createElement('div');
            moreBadge.textContent = `+${galleryFiles.length - MAX_VISIBLE_GALLERY_IMAGES} more`;
            moreBadge.style.margin = '10px';
            moreBadge.style.padding = '20px';
            moreBadge.style.backgroundColor = '#007bff';
            moreBadge.style.color = 'white';
            moreBadge.style.borderRadius = '5px';
            moreBadge.style.textAlign = 'center';
            moreBadge.style.fontSize = '14px';
            moreBadge.style.cursor = 'pointer';

            // Add click event to show all images
            moreBadge.addEventListener('click', () => {
                showAllGalleryImages = true;
                updateGalleryFileList(); // Re-render with all images
            });

            galleryPreviewContainer.appendChild(moreBadge);
        }

        // Show total size information
        if (galleryFiles.length > 0) {
            const totalSize = galleryFiles.reduce((total, f) => total + f.size, 0);
            const totalSizeMB = (totalSize / 1024 / 1024).toFixed(1);
            const sizeInfo = document.createElement('div');
            sizeInfo.innerHTML = `<small class="text-muted">Total: ${galleryFiles.length} images (${totalSizeMB}MB)</small>`;
            galleryPreviewContainer.appendChild(sizeInfo);
        }
    }

    // Form submission handler with upload progress and error handling
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Check if we have main image
                if (!mainImageFile) {
                    e.preventDefault();
                    alert('Please upload a main image for the package.');
                    return false;
                }

                // Check gallery files count
                const totalGalleryFiles = galleryFiles.length;
                if (totalGalleryFiles > 10) {
                    e.preventDefault();
                    alert('Please upload maximum 10 gallery images at a time to avoid server limits.');
                    return false;
                }

                // Check total upload size
                const mainImageSize = mainImageFile ? mainImageFile.size : 0;
                const galleryTotalSize = galleryFiles.reduce((total, f) => total + f.size, 0);
                const totalSizeMB = (mainImageSize + galleryTotalSize) / 1024 / 1024;
                
                if (totalSizeMB > 90) {
                    e.preventDefault();
                    alert('Total upload size is too large. Please reduce image sizes or upload fewer images.');
                    return false;
                }

                // Show upload progress
                const totalFiles = 1 + totalGalleryFiles; // main image + gallery images
                if (totalFiles > 0) {
                    const progressDiv = document.createElement('div');
                    progressDiv.innerHTML = `
                        <div class="alert alert-info" id="upload-progress">
                            <i class="fas fa-cloud-upload-alt"></i> Uploading ${totalFiles} images (${totalSizeMB.toFixed(1)}MB)...
                            <div class="progress mt-2">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                    `;
                    
                    // Insert progress indicator before the form
                    form.parentNode.insertBefore(progressDiv, form);
                    
                    // Simulate progress (since we can't get real upload progress easily)
                    const progressBar = progressDiv.querySelector('.progress-bar');
                    let progress = 0;
                    const interval = setInterval(() => {
                        progress += Math.random() * 15;
                        if (progress > 90) progress = 90;
                        progressBar.style.width = progress + '%';
                    }, 500);
                    
                    // Clear interval after form submission
                    setTimeout(() => clearInterval(interval), 30000);
                }
            });
        }
    });
</script>

@endsection

@section('styles')
<style>
/* Consistent form styling */
.form-control, .form-select {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    border-radius: 0.375rem;
    border: 1px solid #d9dee3;
    background-color: #fff;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus, .form-select:focus {
    border-color: #696cff;
    box-shadow: 0 0 0.25rem 0.05rem rgba(105, 108, 255, 0.1);
}

.select2-container--default .select2-selection--single,
.select2-container--default .select2-selection--multiple {
    padding: 0.5rem;
    height: auto;
    min-height: 38px;
    border: 1px solid #d9dee3;
    border-radius: 0.375rem;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #696cff;
    border: none;
    color: #fff;
    padding: 0.25rem 0.5rem;
    margin: 0.25rem;
    border-radius: 0.25rem;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #fff;
    margin-right: 5px;
}

.select2-container--default.select2-container--focus .select2-selection--multiple,
.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #696cff;
    box-shadow: 0 0 0.25rem 0.05rem rgba(105, 108, 255, 0.1);
}

.select2-container--open .select2-dropdown {
    border-color: #696cff;
    box-shadow: 0 5px 10px rgba(0,0,0,0.1);
}

.card {
    border: none;
    box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 12px 0 rgba(67, 89, 113, 0.16);
}

.card-header {
    background-color: transparent;
    border-bottom: 1px solid #d9dee3;
    padding: 1.5rem;
}

.card-body {
    padding: 1.5rem;
}

.input-group-text {
    background-color: #f5f5f9;
    border: 1px solid #d9dee3;
}

/* Badge styling for selected items */
.selected-item {
    background: #f5f5f9;
    border: 1px solid #d9dee3;
    border-radius: 0.375rem;
    padding: 0.5rem;
    margin: 0.25rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.selected-item .remove-btn {
    background: none;
    border: none;
    color: #dc3545;
    padding: 0;
    font-size: 1.1rem;
    line-height: 1;
    cursor: pointer;
}

/* Consistent spacing */
.form-label {
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #566a7f;
}

.btn {
    padding: 0.5rem 1rem;
    font-weight: 500;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
}

.btn-primary {
    background-color: #696cff;
    border-color: #696cff;
}

.btn-primary:hover {
    background-color: #5f61e6;
    border-color: #5f61e6;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(105, 108, 255, 0.2);
}

/* File input styling */
.file-input-wrapper {
    position: relative;
    overflow: hidden;
    display: inline-block;
}

.file-input-wrapper input[type="file"] {
    font-size: 100px;
    position: absolute;
    left: 0;
    top: 0;
    opacity: 0;
}

/* Required field indicator */
.required-field::after {
    content: "*";
    color: #ff3e1d;
    margin-left: 4px;
}

.hover-shadow {
    transition: all 0.3s ease;
}
.hover-shadow:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

/* Custom larger toggle switch */
.form-check-input[type="checkbox"].form-switch {
    height: 1.25rem;
    width: 2.25rem;
    cursor: pointer;
}

.form-check-input:checked {
    background-color: #696cff;
    border-color: #696cff;
}

/* Custom toggle colors */
#entry-port:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

#exit-port:checked {
    background-color: #dc3545;
    border-color: #dc3545;
}

/* Meal toggle colors */
#breakfast-included:checked {
    background-color: #ffc107;
    border-color: #ffc107;
}

#lunch-included:checked {
    background-color: #0dcaf0;
    border-color: #0dcaf0;
}

#dinner-included:checked {
    background-color: #6f42c1;
    border-color: #6f42c1;
}

/* Purple color for dinner */
.text-purple {
    color: #6f42c1;
}

.bg-purple-subtle {
    background-color: rgba(111, 66, 193, 0.1);
}

.border-purple {
    border-color: #6f42c1 !important;
}

/* Highlight card when toggle is checked */
#entry-port:checked ~ .card {
    border-color: #0d6efd !important;
    background-color: rgba(13, 110, 253, 0.05);
}

#exit-port:checked ~ .card {
    border-color: #dc3545 !important;
    background-color: rgba(220, 53, 69, 0.05);
}

/* Day-wise Itinerary Styling */
.accordion-item {
    border: 1px solid rgba(0,0,0,.125);
    margin-bottom: 0.75rem;
    border-radius: 0.5rem;
    overflow: hidden;
    transition: all 0.3s ease;
}

.accordion-item:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.accordion-button {
    background-color: #f8f9fa;
    font-weight: 500;
    padding: 1rem 1.25rem;
}

.accordion-button:not(.collapsed) {
    background-color: #e7f1ff;
    color: #0d6efd;
    box-shadow: none;
}

.accordion-button:focus {
    box-shadow: none;
    border-color: rgba(0,0,0,.125);
}

.accordion-button::after {
    background-size: 1.25rem;
    transition: all 0.3s ease;
}

.selected-attractions-container {
    max-height: 300px;
    overflow-y: auto;
    padding-right: 5px;
}

.selected-attractions-container::-webkit-scrollbar {
    width: 5px;
}

.selected-attractions-container::-webkit-scrollbar-thumb {
    background: #d9dee3;
    border-radius: 10px;
}

.hotel-day-checkbox {
    width: 1.2rem;
    height: 1.2rem;
    cursor: pointer;
}

.hotel-day-checkbox:checked {
    background-color: #696cff;
    border-color: #696cff;
}

/* Attraction service toggle styling */
.attraction-transfer-toggle:checked {
    background-color: #20c997;
    border-color: #20c997;
}

.arrival-pickup-toggle:checked {
    background-color: #0dcaf0;
    border-color: #0dcaf0;
}

.departure-service-toggle:checked {
    background-color: #fd7e14;
    border-color: #fd7e14;
}

/* Transfer options styling */
.transfer-options-container {
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    padding: 0.75rem;
    margin-top: 0.5rem;
    border-left: 3px solid #20c997;
    transition: all 0.3s ease;
}

/* Day badges */
.badge {
    font-weight: 500;
    letter-spacing: 0.3px;
}

.badge.bg-info-subtle {
    background-color: rgba(13, 202, 240, 0.18) !important;
    color: #0aa2c0 !important;
}

.badge.bg-warning-subtle {
    background-color: rgba(253, 126, 20, 0.18) !important;
    color: #cc6510 !important;
}

.badge.bg-primary-subtle {
    background-color: rgba(105, 108, 255, 0.18) !important;
    color: #5659cc !important;
}

.badge.bg-success-subtle {
    background-color: rgba(32, 201, 151, 0.18) !important;
    color: #18a47c !important;
}

/* Table styling for hotel day assignments */
.table-bordered {
    border-color: #d9dee3;
}

.table-bordered th {
    background-color: #f8f9fa;
    font-weight: 500;
}

.table-bordered td, .table-bordered th {
    border-color: #d9dee3;
    padding: 0.75rem;
    vertical-align: middle;
}

.table-hover tbody tr:hover {
    background-color: rgba(105, 108, 255, 0.04);
}

/* Animation for accordion */
.accordion-collapse {
    transition: all 0.3s ease;
}

/* Improved alert styling */
.alert {
    border: none;
    border-radius: 0.5rem;
    padding: 1rem;
}

.alert-info {
    background-color: rgba(13, 202, 240, 0.1);
    color: #087990;
}

/* Improved form check styling */
.form-check-label {
    cursor: pointer;
}

.form-check-input {
    cursor: pointer;
}

/* Card header subtle backgrounds */
.bg-primary-subtle {
    background-color: rgba(105, 108, 255, 0.1) !important;
}

.bg-success-subtle {
    background-color: rgba(32, 201, 151, 0.1) !important;
}

.bg-warning-subtle {
    background-color: rgba(253, 126, 20, 0.1) !important;
}

.bg-info-subtle {
    background-color: rgba(13, 202, 240, 0.1) !important;
}

.bg-light-subtle {
    background-color: #f9fafb !important;
}

/* Couple package styling */
#couple-price-note {
    background-color: rgba(13, 202, 240, 0.1);
    border-left: 3px solid #0dcaf0;
    padding: 0.5rem;
    border-radius: 0.375rem;
}

#couple-price-note .text-info {
    color: #0aa2c0 !important;
}

/* Hidden sections styling */
.d-none {
    display: none !important;
}

/* Day-wise select field styling */
.day-guide-select,
.day-attraction-select {
    width: 100% !important;
    min-width: 300px;
}

/* Ensure Select2 containers have consistent width */
.select2-container--default .select2-selection--single,
.select2-container--default .select2-selection--multiple {
    width: 100% !important;
}

/* Attractions selection note styling */
.attractions-note {
    background-color: rgba(105, 108, 255, 0.05);
    border-left: 3px solid #696cff;
    padding: 0.75rem;
    border-radius: 0.375rem;
    margin-top: 0.75rem;
}

.attractions-note .text-muted {
    color: #6c757d !important;
    font-weight: 500;
}
</style>
@endsection 