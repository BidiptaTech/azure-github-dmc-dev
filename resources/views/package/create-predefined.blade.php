@extends('layouts.layout')
@section('content')
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
                                <option value="single" {{ old('package_type') == 'single' ? 'selected' : '' }}>Single Person</option>
                                <option value="double" {{ old('package_type') == 'double' ? 'selected' : '' }}>Two Person</option>
                                
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
                            <label class="form-label">Adult Price (SGD) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">SGD</span>
                                <input type="number" class="form-control @error('price_adult') is-invalid @enderror" 
                                       name="price_adult" value="{{ old('price_adult') }}" min="0" step="0.01" required>
                            </div>
                            @error('price_adult')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
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
                        <div class="col-md-3">
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
                                   name="max_pax" value="{{ old('max_pax') }}" min="1" required>
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

            <!-- Hotels Selection -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-hotel-line me-2 text-primary"></i>Hotels Selection</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-9">
                            <label class="form-label">Select Hotels</label>
                            <select class="form-select" id="hotel-select" multiple disabled>
                                <option value="">Select Hotels</option>
                            </select>
                            <input type="hidden" name="selected_hotels">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">
                                Max Select Hotels 
                            </label>
                            <select class="form-select" id="hotel-select-count" name="hotel-select-count">
                                <option value="">Choose Max...</option>
                                <option value="1">1 </option>
                                <option value="2">2 </option>
                                <option value="3">3 </option>
                                <option value="4">4 </option>
                                <option value="5">5 </option>
                            </select>
                        </div>
                    </div>
                    <div id="selected-hotels" class="mt-3"></div>
                </div>
            </div>

            <!-- Attractions Selection -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-map-pin-line me-2 text-info"></i>Attractions Selection</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-9">
                            <label class="form-label">Select Attractions</label>
                            <select class="form-select" id="attraction-select" multiple disabled>
                                <option value="">Select Attractions</option>
                            </select>
                            <input type="hidden" name="selected_attractions">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">
                                Max Select Attraction 
                            </label>
                            <select class="form-select" id="attraction-select-count" name="attraction-select-count">
                                <option value="">Choose Max...</option>
                                <option value="1">1 </option>
                                <option value="2">2 </option>
                                <option value="3">3 </option>
                                <option value="4">4 </option>
                                <option value="5">5 </option>
                            </select>
                        </div>
                    </div>
                    <div id="selected-attractions" class="mt-3"></div>
                </div>
            </div>

            <div class="row g-3">

                <!-- Guide Selection -->
                <div class="col-md-6 card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="ri-user-line me-2 text-warning"></i>Guide Selection</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Select Guide (Optional)</label>
                                <select class="form-select" id="guide-select" disabled>
                                    <option value="">Select Guide</option>
                                </select>
                                <input type="hidden" name="selected_guide">
                                <small class="text-muted">Choose one guide for this package</small>
                            </div>
                        </div>
                        <div id="selected-guide" class="mt-3"></div>
                    </div>
                </div>

                <!-- Restaurants Selection -->
                <div class="col-md-6 card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="ri-restaurant-line me-2 text-success"></i>Restaurants Selection</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Select Restaurants (Optional)</label>
                                <select class="form-select" id="restaurant-select" multiple disabled>
                                    <option value="">Select Restaurants</option>
                                </select>
                                <input type="hidden" name="selected_restaurants">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">
                                    Max Select Restaurants 
                                </label>
                                <select class="form-select" id="restaurant-select-count" name="restaurant-select-count">
                                    <option value="">Choose Max...</option>
                                    <option value="1">1 </option>
                                    <option value="2">2 </option>
                                    <option value="3">3 </option>
                                    <option value="4">4 </option>
                                    <option value="5">5 </option>
                                </select>
                            </div>
                        </div>
                        <div id="selected-restaurants" class="mt-3"></div>
                    </div>
                </div>

            </div>

            <!-- Transport Selection -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-bus-line me-2 text-primary"></i>Transport Selection</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Transport</label>
                            <select class="form-select" id="transport-select" disabled>
                                <option value="">Select Transport</option>
                            </select>
                            <input type="hidden" name="selected_transport">
                        </div>
                    </div>
                </div>
                <div id="selected-transport" class="mt-3"></div>
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
                        <div class="col-md-6">
                            <label class="form-label">Main Image <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('main_image') is-invalid @enderror" 
                                   name="main_image" accept="image/jpeg,image/png,image/jpg,image/gif" required>
                            <small class="text-muted">Max file size: 5MB. Allowed formats: JPEG, PNG, JPG, GIF</small>
                            @error('main_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gallery Images</label>
                            <input type="file" class="form-control @error('gallery_images') is-invalid @enderror" 
                                   name="gallery_images[]" accept="image/jpeg,image/png,image/jpg,image/gif" multiple>
                            <small class="text-muted">Max file size per image: 5MB. Allowed formats: JPEG, PNG, JPG, GIF</small>
                            @error('gallery_images')
                                <div class="invalid-feedback">{{ $message }}</div>
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
    $('#hotel-select, #attraction-select, #restaurant-select, #transport-select').select2({
        multiple: true,
        placeholder: 'Select options'
    });
    $('#guide-select').select2({
        placeholder: 'Select guide'
    });

    // Country change event
    $('#country-select').on('change', function() {
        const country = $(this).val();
        const citySelect = $('#city-select');
        
        // Reset dependent dropdowns
        citySelect.empty().prop('disabled', true);
        $('#hotel-select').empty().prop('disabled', true);
        $('#attraction-select').empty().prop('disabled', true);
        $('#guide-select').empty().prop('disabled', true);
        $('#restaurant-select').empty().prop('disabled', true);
        
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
        const attractionSelect = $('#attraction-select');
        const guideSelect = $('#guide-select');
        const restaurantSelect = $('#restaurant-select');
        const transportSelect = $('#transport-select');

        hotelSelect.empty().prop('disabled', true);
        attractionSelect.empty().prop('disabled', true);
        guideSelect.empty().prop('disabled', true);
        restaurantSelect.empty().prop('disabled', true);
        transportSelect.empty().prop('disabled', true);
        
        if (city) {
            // Load hotels
            $.ajax({
                url: `{{ env('APP_URL') }}/hotel-city/${encodeURIComponent(city)}`,
                method: 'GET',
                success: function(response) {
                    console.log("hotel = ", response);
                    hotelSelect.prop('disabled', false);
                    response.forEach(function(hotel) {
                        const option = new Option(hotel.name, hotel.hotel_unique_id, hotel.city);
                        $(option).data('hotel-data', {
                            id: hotel.hotel_unique_id,
                            name: hotel.name,
                            city: hotel.city
                        });
                        hotelSelect.append(option);
                    });
                }
            });

            // Load attractions
            $.ajax({
                url: `{{ env('APP_URL') }}/attractions/${encodeURIComponent(city)}`,
                method: 'GET',
                success: function(response) {
                    attractionSelect.prop('disabled', false);
                    response.forEach(function(attraction) {
                        const option = new Option(attraction.name, attraction.attraction_id, attraction.location);
                        $(option).data('attraction-data', {
                            id: attraction.attraction_id,
                            name: attraction.name,
                            city: attraction.location
                        });
                        attractionSelect.append(option);
                    });
                }
            });

            // Load guides
            $.ajax({
                url: `{{ env('APP_URL') }}/guides/${encodeURIComponent(city)}`,
                method: 'GET',
                success: function(response) {
                    guideSelect.prop('disabled', false);
                    guideSelect.append('<option value="">Select Guide</option>');
                    response.forEach(function(guide) {
                        const option = new Option(`${guide.name} (${guide.languages})`, guide.guide_id);
                        $(option).data('guide-data', {
                            id: guide.guide_id,
                            name: guide.name,
                            languages: guide.languages,
                            contact_no: guide.contact_no
                        });
                        guideSelect.append(option);
                    });
                }
            });

            // Load restaurants
            $.ajax({
                url: `{{ env('APP_URL') }}/restaurants/${encodeURIComponent(city)}`,
                method: 'GET',
                success: function(response) {
                    restaurantSelect.prop('disabled', false);
                    response.restaurants.forEach(function(restaurant) {
                        const restaurantName = `${restaurant.name} (${restaurant.cuisine})`;
                        const option = new Option(restaurantName, restaurant.restaurant_id);
                        $(option).data('restaurant-data', {
                            id: restaurant.restaurant_id,
                            name: restaurant.name,
                            cuisine: restaurant.cuisine,
                            city: restaurant.city
                        });
                        restaurantSelect.append(option);
                    });
                }
            });

            // Load transport
            $.ajax({
                url: `{{ env('APP_URL') }}/get-transport/${encodeURIComponent(city)}`,
                method: 'GET',
                success: function(response) {
                    transportSelect.prop('disabled', false);
                    response.forEach(function(transport) {
                        const option = new Option(transport.name, transport.transport_id);
                        $(option).data('transport-data', {
                            id: transport.transport_id,
                            name: transport.name
                        });
                        transportSelect.append(option);
                    });
                }
            });
        }
    });

    // Hotel selection handler
    $('#hotel-select').on('change', function() {
        const selectedOptions = $(this).find('option:selected');
        const selectedHotels = [];
        
        selectedOptions.each(function() {
            const hotelData = $(this).data('hotel-data');
            if (hotelData) {
                selectedHotels.push({
                    id: hotelData.id,
                    name: hotelData.name,
                    city: hotelData.city
                });
            }
        });

        // Store the selected hotels data in a hidden input
        $('input[name="selected_hotels"]').val(JSON.stringify(selectedHotels));
    });

    // Attraction selection handler
    $('#attraction-select').on('change', function() {
        const selectedOptions = $(this).find('option:selected');
        const selectedAttractions = [];
        
        selectedOptions.each(function() {
            const attractionData = $(this).data('attraction-data');
            if (attractionData) {
                selectedAttractions.push({
                    id: attractionData.id,
                    name: attractionData.name,
                    city: attractionData.city
                });
            }
        });

        // Store the selected attractions data in a hidden input
        $('input[name="selected_attractions"]').val(JSON.stringify(selectedAttractions));
    });

    // Guide selection handler
    $('#guide-select').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const guideData = selectedOption.data('guide-data');
        
        if (guideData && selectedOption.val()) {
            const selectedGuide = {
                id: guideData.id,
                name: guideData.name,
                languages: guideData.languages,
                contact_no: guideData.contact_no
            };
            
            // Store the selected guide data in a hidden input
            $('input[name="selected_guide"]').val(JSON.stringify(selectedGuide));
            
            // Display selected guide info
            $('#selected-guide').html(`
                <div class="selected-item">
                    <strong>${guideData.name}</strong><br>
                    <small>Languages: ${guideData.languages} | Contact: ${guideData.contact_no}</small>
                </div>
            `);
        } else {
            $('input[name="selected_guide"]').val('');
            $('#selected-guide').html('');
        }
    });

    // Restaurant selection handler
    $('#restaurant-select').on('change', function() {
        const selectedOptions = $(this).find('option:selected');
        const selectedRestaurants = [];
        
        selectedOptions.each(function() {
            const restaurantData = $(this).data('restaurant-data');
            if (restaurantData) {
                selectedRestaurants.push({
                    id: restaurantData.id,
                    name: restaurantData.name,
                    cuisine: restaurantData.cuisine,
                    city: restaurantData.city
                });
            }
        });

        // Store the selected restaurants data in a hidden input
        $('input[name="selected_restaurants"]').val(JSON.stringify(selectedRestaurants));
    });
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

.card {
    border: none;
    box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
    border-radius: 0.5rem;
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
}

.btn-primary {
    background-color: #696cff;
    border-color: #696cff;
}

.btn-primary:hover {
    background-color: #5f61e6;
    border-color: #5f61e6;
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
</style>
@endsection 