@extends('layouts.layout')

@section('title', 'Edit Package')

@section('css')
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/css/select2.min.css" rel="stylesheet" />
<style>
.selected-items {
    min-height: 60px;
    border: 2px dashed #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    background-color: #fafafa;
}

.selected-item {
    display: inline-block;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 8px 12px;
    margin: 4px;
    border-radius: 20px;
    font-size: 14px;
    position: relative;
    animation: slideIn 0.3s ease-out;
}

.selected-item .remove-btn {
    background: rgba(255, 255, 255, 0.3);
    border: none;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    margin-left: 8px;
    cursor: pointer;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s;
}

.selected-item .remove-btn:hover {
    background: rgba(255, 255, 255, 0.5);
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.empty-state {
    text-align: center;
    color: #999;
    font-style: italic;
    padding: 20px;
}
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <span class="text-muted fw-light">Packages /</span> Edit Package
                </h4>
                <p class="text-muted">Update package details and settings</p>
            </div>
            <div>
                <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary me-2">
                    <i class="ri-arrow-left-line me-1"></i>Back to Packages
                </a>
                <a href="{{ route('packages.show', $package->package_id) }}" class="btn btn-outline-info">
                    <i class="ri-eye-line me-1"></i>Preview
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-check-circle-line me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Package Edit Form -->
        <form action="{{ route('packages.update', $package->package_id) }}" method="POST" enctype="multipart/form-data" id="packageForm">
            @csrf
            @method('PUT')
            
            <div class="row">
                <!-- Main Form -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-information-line me-2"></i>Package Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Package Title -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Package Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           name="title" value="{{ old('title', $package->title) }}" required 
                                           placeholder="Enter package title">
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Country/Destination -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Country/Destination <span class="text-danger">*</span></label>
                                    <select class="form-select @error('destination') is-invalid @enderror" 
                                            name="destination" id="country-select" required>
                                        <option value="">Select Country</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->name }}" 
                                                {{ old('destination', $package->destination) == $country->name ? 'selected' : '' }}>
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('destination')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Category -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select @error('category') is-invalid @enderror" 
                                            name="category" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category }}" 
                                                {{ old('category', $package->category) == $category ? 'selected' : '' }}>
                                                {{ $category }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Duration -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Duration (Days) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('duration_days') is-invalid @enderror" 
                                           name="duration_days" value="{{ old('duration_days', $package->duration_days) }}" 
                                           min="1" max="30" required>
                                    @error('duration_days')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Max Pax -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Maximum Pax <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('max_pax') is-invalid @enderror" 
                                           name="max_pax" value="{{ old('max_pax', $package->max_pax) }}" 
                                           min="1" max="50" required>
                                    @error('max_pax')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              name="description" rows="4" 
                                              placeholder="Enter package description">{{ old('description', $package->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-money-dollar-circle-line me-2"></i>Pricing Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Adult Price (SGD) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('price_adult') is-invalid @enderror" 
                                           name="price_adult" value="{{ old('price_adult', $package->price_adult) }}" 
                                           step="0.01" min="0" required>
                                    @error('price_adult')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Senior Price (SGD)</label>
                                    <input type="number" class="form-control @error('price_senior') is-invalid @enderror" 
                                           name="price_senior" value="{{ old('price_senior', $package->price_senior) }}" 
                                           step="0.01" min="0">
                                    @error('price_senior')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Child Price (SGD)</label>
                                    <input type="number" class="form-control @error('price_child') is-invalid @enderror" 
                                           name="price_child" value="{{ old('price_child', $package->price_child) }}" 
                                           step="0.01" min="0">
                                    @error('price_child')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Package Availability -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-calendar-line me-2"></i>Package Availability
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                           name="start_date" value="{{ old('start_date', $package->start_date ? date('Y-m-d', strtotime($package->start_date)) : '') }}" 
                                           required min="{{ date('Y-m-d') }}">
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Expiry Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                           name="expiry_date" value="{{ old('expiry_date', $package->expire_date ? date('Y-m-d', strtotime($package->expire_date)) : '') }}" 
                                           required min="{{ date('Y-m-d') }}">
                                    @error('expiry_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hotels Selection -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="ri-hotel-line me-2"></i>Hotels Selection
                            </h5>
                            <div class="d-flex align-items-center">
                                <label class="form-label me-2 mb-0">Max Hotels:</label>
                                <input type="number" class="form-control form-control-sm" 
                                       id="hotel-select-count" name="hotel-select-count" 
                                       value="{{ old('hotel-select-count', $package->max_hotels ?? 5) }}" 
                                       min="1" max="20" style="width: 80px;">
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-10">
                                    <select class="form-select" id="hotel-select">
                                        <option value="">Select a hotel to add</option>
                                        @foreach($hotels as $hotel)
                                            <option value="{{ $hotel->hotel_unique_id }}" data-city="{{ $hotel->city }}">{{ $hotel->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary w-100" onclick="addHotel()">
                                        <i class="ri-add-line"></i> Add
                                    </button>
                                </div>
                            </div>
                            <div id="selected-hotels" class="selected-items">
                                <!-- Selected hotels will be displayed here -->
                                @foreach($package->selected_hotels as $hotel)
                                    <div class="selected-item">
                                        {{ $hotel['name'] }}
                                        <button class="remove-btn" onclick="removeHotel('{{ $hotel['id'] }}')">
                                            <i class="ri-close-line"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                

                    <!-- Attractions Selection -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="ri-map-pin-line me-2"></i>Attractions Selection
                            </h5>
                            <div class="d-flex align-items-center">
                                <label class="form-label me-2 mb-0">Max Attractions:</label>
                                <input type="number" class="form-control form-control-sm" 
                                       id="attraction-select-count" name="attraction-select-count" 
                                       value="{{ old('attraction-select-count', $package->max_attractions ?? 5) }}" 
                                       min="1" max="20" style="width: 80px;">
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-10">
                                    <select class="form-select" id="attraction-select">
                                        <option value="">Select an attraction to add</option>
                                        @foreach($attractions as $attraction)
                                            <option value="{{ $attraction->attraction_id }}" data-city="{{ $attraction->location }}">{{ $attraction->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary w-100" onclick="addAttraction()">
                                        <i class="ri-add-line"></i> Add
                                    </button>
                                </div>
                            </div>
                            <div id="selected-attractions" class="selected-items">
                                <!-- Selected attractions will be displayed here -->
                            </div>
                        </div>
                    </div>

                    <!-- Guide Selection -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="ri-map-pin-line me-2"></i>Guide Selection
                            </h5>
                            
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-10">
                                    <select class="form-select" id="guide-select">
                                        <option value="">Select a guide to add</option>
                                        @foreach($guides as $guide)
                                            <option value="{{ $guide->guide_id }}" data-languages="{{ json_encode($guide->languages) }}" data-contact="{{ $guide->contact_no }}">{{ $guide->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary w-100" onclick="addGuide()">
                                        <i class="ri-add-line"></i> Add
                                    </button>
                                </div>
                            </div>
                            <div id="selected-guides" class="selected-items">
                                <!-- Selected guide will be displayed here -->
                            </div>
                        </div>
                    </div>

                    <!-- Restaurant Selection -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="ri-map-pin-line me-2"></i>Restaurant Selection
                            </h5>
                            <div class="d-flex align-items-center">
                                <label class="form-label me-2 mb-0">Max Attractions:</label>
                                <input type="number" class="form-control form-control-sm" 
                                       id="restaurant-select-count" name="restaurant-select-count" 
                                       value="{{ old('restaurant-select-count', $package->max_restaurants ?? 5) }}" 
                                       min="1" max="20" style="width: 80px;">
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-10">
                                    <select class="form-select" id="restaurant-select">
                                        <option value="">Select a restaurant to add</option>
                                        @foreach($restaurants as $restaurant)
                                            <option value="{{ $restaurant->restaurant_id }}" data-city="{{ $restaurant->city }}" data-cuisine="{{ $restaurant->cuisine }}">{{ $restaurant->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary w-100" onclick="addRestaurant()">
                                        <i class="ri-add-line"></i> Add
                                    </button>
                                </div>
                            </div>
                            <div id="selected-restaurants" class="selected-items">
                                <!-- Selected restaurants will be displayed here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Package Status -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-settings-line me-2"></i>Package Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" name="status" required>
                                    <option value="0" {{ old('status', $package->status) == 0 ? 'selected' : '' }}>Draft</option>
                                    <option value="1" {{ old('status', $package->status) ==  1 ? 'selected' : '' }}>Active</option>
                                    
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_featured" 
                                       id="is_featured" value="1" 
                                       {{ old('is_featured', $package->is_featured) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">
                                    Featured Package
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Images -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-image-line me-2"></i>Package Images
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Current Main Image -->
                            @if($package->main_image)
                            <div class="mb-3">
                                <label class="form-label">Current Main Image</label>
                                <div class="border rounded p-2">
                                    <img src="{{ $package->main_image }}" alt="Current main image" 
                                         class="img-fluid rounded" style="max-height: 150px;">
                                </div>
                            </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label">Main Package Image</label>
                                <input type="file" class="form-control @error('main_image') is-invalid @enderror" 
                                       name="main_image" accept="image/*">
                                <small class="text-muted">Leave empty to keep current image. Max size: 5MB</small>
                                @error('main_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Additional Images</label>
                                <input type="file" class="form-control @error('gallery_images.*') is-invalid @enderror" 
                                       name="gallery_images[]" accept="image/*" multiple>
                                <small class="text-muted">Select multiple images. Max size: 5MB each</small>
                                @error('gallery_images.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Current Gallery Images -->
                            @if($package->gallery_images && count($package->gallery_images) > 0)
                            <div class="mb-3">
                                <label class="form-label">Current Gallery Images</label>
                                <div class="row g-2">
                                    @foreach($package->gallery_images as $image)
                                    <div class="col-6">
                                        <img src="{{ $image }}" alt="Gallery image" 
                                             class="img-fluid rounded" style="height: 80px; object-fit: cover;">
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Additional Details -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-file-text-line me-2"></i>Additional Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Inclusions</label>
                                <textarea class="form-control" name="inclusions" rows="3" 
                                          placeholder="What's included in this package">{{ old('inclusions', $package->inclusions) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Exclusions</label>
                                <textarea class="form-control" name="exclusions" rows="3" 
                                          placeholder="What's not included">{{ old('exclusions', $package->exclusions) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Terms & Conditions</label>
                                <textarea class="form-control" name="terms_conditions" rows="3" 
                                          placeholder="Terms and conditions">{{ old('terms_conditions', $package->terms_conditions) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i>Update Package
                                </button>
                                <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary">
                                    <i class="ri-close-line me-1"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('#country-select, #city-select, #hotel-select, #attraction-select, #restaurant-select').select2();

    // Initialize selected items with proper structure
    selectedHotels = @json($package->selected_hotels ?? []).map(hotel => ({
        id: hotel.id || hotel.name,  // Fallback to name if id is not present
        name: hotel.name,
        country: hotel.country
    }));

    selectedAttractions = @json($package->selected_attractions ?? []).map(attraction => ({
        id: attraction.id || attraction.attraction_id || attraction.name,  // Try multiple ID fields
        name: attraction.name,
        city: attraction.city || ''
    }));
   

    // Update displays
    updateHotelsDisplay();
    updateAttractionsDisplay();

    // Initialize selected guides if any exist
    selectedGuides = [];
    const guideData = @json($package->selected_guide ?? null);
    if (guideData) {
        if (Array.isArray(guideData)) {
            selectedGuides = guideData.map(guide => ({
                id: guide.id || guide.guide_id || guide.name,  // Try multiple ID fields
                name: guide.name,
                languages: guide.languages || [],
                contact_no: guide.contact_no || ''
            }));
        } else {
            // Handle single guide object
            selectedGuides = [{
                id: guideData.id || guideData.guide_id || guideData.name,
                name: guideData.name,
                languages: guideData.languages || [],
                contact_no: guideData.contact_no || ''
            }];
        }
        
    }
    updateGuidesDisplay();
    
    // Initialize selected restaurants if any exist
    selectedRestaurants = [];
    const restaurantData = @json($package->selected_restaurants ?? null);
    if (restaurantData) {
        if (Array.isArray(restaurantData)) {
            selectedRestaurants = restaurantData.map(restaurant => ({
                id: restaurant.id || restaurant.restaurant_id || restaurant.name, // Try multiple ID fields
                name: restaurant.name,
                cuisine: restaurant.cuisine || '',
                city: restaurant.city || ''
            }));
        } else {
            // Handle single restaurant object
            selectedRestaurants = [{
                id: restaurantData.id || restaurantData.restaurant_id || restaurantData.name,
                name: restaurantData.name,
                cuisine: restaurantData.cuisine || '',
                city: restaurantData.city || ''
            }];
        }
        
    }
    updateRestaurantsDisplay();
});

// Filter hotels and attractions by country
async function filterByCountry() {
    const country = document.getElementById('country-select').value;
    
    if (!country) {
        document.getElementById('hotel-select').innerHTML = '<option value="">Select a hotel to add</option>';
        document.getElementById('attraction-select').innerHTML = '<option value="">Select an attraction to add</option>';
        return;
    }

    try {
        // Show loading state
        $('#hotel-select, #attraction-select').prop('disabled', true);
        
        // Load hotels
        const hotelsResponse = await fetch(`/api/hotels-by-country?country=${encodeURIComponent(country)}`);
        if (!hotelsResponse.ok) throw new Error('Failed to load hotels');
        const hotelsData = await hotelsResponse.json();
        
        updateHotelOptions(hotelsData.hotels || []);

        // Load attractions
        const attractionsResponse = await fetch(`/api/attractions-by-country?country=${encodeURIComponent(country)}`);
        if (!attractionsResponse.ok) throw new Error('Failed to load attractions');
        const attractionsData = await attractionsResponse.json();
        
        updateAttractionOptions(attractionsData.attractions || []);
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load data. Please try again.');
    } finally {
        $('#hotel-select, #attraction-select').prop('disabled', false);
    }
}

// Hotel selection functions
function addHotel() {
    try {
        const select = document.getElementById('hotel-select');
        const maxSelect = document.getElementById('hotel-select-count');
        
        // Validate select element exists
        if (!select) {
            console.error('Hotel select element not found');
            return;
        }

        // Validate selection
        if (!select.value) {
            alert('Please select a hotel first');
            return;
        }

        // Get max count with fallback
        const maxCount = parseInt(maxSelect?.value) || 5;
        
        // Parse the selected value
        const selectedOption = select.options[select.selectedIndex];
        const hotelData = {
            'id': selectedOption.value,
            'name': selectedOption.text,
            'country': selectedOption.getAttribute('data-country') || ''
        };

        // Validate if hotel is already selected
        if (selectedHotels.some(hotel => hotel['id'] === hotelData['id'])) {
            alert('This hotel is already selected');
            return;
        }

        // Check max limit
        if (selectedHotels.length >= maxCount) {
            alert(`Maximum ${maxCount} hotel(s) can be selected`);
            return;
        }

        // Add hotel to selection
        selectedHotels.push(hotelData);
        updateHotelsDisplay();

        // Reset selection
        select.value = '';
        $(select).trigger('change'); // Trigger change for Select2

    } catch (error) {
        console.error('Error in addHotel:', error);
        alert('An error occurred while adding the hotel. Please try again.');
    }
}

function updateHotelsDisplay() {
    try {
        const container = document.getElementById('selected-hotels');
        
        if (!container) {
            console.error('Selected hotels container not found');
            return;
        }

        if (!Array.isArray(selectedHotels) || selectedHotels.length === 0) {
            container.innerHTML = '<div class="empty-state">No hotels selected</div>';
            return;
        }

        container.innerHTML = selectedHotels.map(hotel => `
            <div class="selected-item">
                <strong>${hotel['name'] || 'Unnamed Hotel'}</strong>
                <small class="ms-1">(${hotel['id']})</small>
                <button type="button" class="remove-btn" onclick="removeHotel('${hotel['id']}')">×</button>
                <input type="hidden" name="selected_hotels[]" value='${JSON.stringify(hotel)}'>
            </div>
        `).join('');

    } catch (error) {
        console.error('Error in updateHotelsDisplay:', error);
    }
}

function removeHotel(id) {
    try {
        if (!id) {
            console.error('No hotel ID provided for removal');
            return;
        }

        selectedHotels = selectedHotels.filter(hotel => hotel['id'] !== id);
        updateHotelsDisplay();
        
    } catch (error) {
        console.error('Error in removeHotel:', error);
        alert('An error occurred while removing the hotel. Please try again.');
    }
}

// Attraction selection functions
function addAttraction() {
    const select = document.getElementById('attraction-select');
    const maxSelect = document.getElementById('attraction-select-count');
    const maxCount = parseInt(maxSelect.value) || 999;
    
    if (select.value) {
        const selectedOption = select.options[select.selectedIndex];
        const id = select.value;
        const name = selectedOption.text;
        const city = selectedOption.getAttribute('data-city') || '';
        
        // Convert to string for consistent comparison
        const newId = String(id);
        
        // Check if attraction already exists using string comparison
        if (!selectedAttractions.some(a => String(a.id) === newId)) {
            if (selectedAttractions.length >= maxCount) {
                alert(`Maximum ${maxCount} attraction(s) can be selected`);
                return;
            }
            selectedAttractions.push({id, name, city});
            updateAttractionsDisplay();
            select.value = '';
            $(select).trigger('change'); // For Select2
        } else {
            alert('This attraction is already selected');
        }
    }
}

function removeAttraction(id) {
    // Convert to string for consistent comparison
    const idToRemove = String(id);
    
    // Filter using string comparison
    selectedAttractions = selectedAttractions.filter(a => String(a.id) !== idToRemove);
    
    // Update the display
    updateAttractionsDisplay();
}

function updateAttractionsDisplay() {
    const container = document.getElementById('selected-attractions');
    
    if (!Array.isArray(selectedAttractions) || selectedAttractions.length === 0) {
        container.innerHTML = '<div class="empty-state">No attractions selected</div>';
        return;
    }
    
    container.innerHTML = selectedAttractions.map(attraction => `
        <div class="selected-item">
            <strong>${attraction.name}</strong>
            <small class="ms-1">${attraction.city ? `(${attraction.city})` : ''}</small>
            <button type="button" class="remove-btn" onclick="removeAttraction('${attraction.id}')">×</button>
            <input type="hidden" name="selected_attractions[]" value='${JSON.stringify(attraction)}'>
        </div>
    `).join('');
}

// Guide selection functions
let selectedGuides = [];

function addGuide() {
    const select = document.getElementById('guide-select');
    
    if (select.value) {
        const selectedOption = select.options[select.selectedIndex];
        const guideData = {
            id: select.value, // Keep as string to match with removeGuide
            name: selectedOption.text,
            languages: selectedOption.getAttribute('data-languages') ? JSON.parse(selectedOption.getAttribute('data-languages')) : [],
            contact_no: selectedOption.getAttribute('data-contact')
        };
        
        // Convert to string for consistent comparison
        const newId = String(guideData.id);
        
        // Check if guide already exists using string comparison
        if (!selectedGuides.some(g => String(g.id) === newId)) {
            selectedGuides.push(guideData);
            updateGuidesDisplay();
            select.value = '';
            $(select).trigger('change'); // For Select2
        } else {
            alert('This guide is already selected');
        }
    }
}

function removeGuide(id) {
    // Convert to string for consistent comparison
    const idToRemove = String(id);
    
    // Filter using string comparison
    selectedGuides = selectedGuides.filter(g => String(g.id) !== idToRemove);
    
    // Update the display
    updateGuidesDisplay();
}

function updateGuidesDisplay() {
    const container = document.getElementById('selected-guides');
    
    if (!Array.isArray(selectedGuides) || selectedGuides.length === 0) {
        container.innerHTML = '<div class="empty-state">No guides selected</div>';
        return;
    }
    
    container.innerHTML = selectedGuides.map(guide => {
        // Debug log to see the structure
        
        
        const languageNames = Array.isArray(guide.languages) 
            ? guide.languages.map(lang => {
                
                return typeof lang === 'object' ? lang.language : lang;
            }).join(', ')
            : guide.languages;
            
        return `
            <div class="selected-item">
                <strong>${guide.name}</strong><br>
                <small>Languages: ${languageNames} | Contact: ${guide.contact_no}</small>
                <button type="button" class="remove-btn" onclick="removeGuide('${guide.id}')">×</button>
                <input type="hidden" name="selected_guide[]" value='${JSON.stringify(guide)}'>
            </div>
        `;
    }).join('');
}

// Restaurant selection functions
function addRestaurant() {
    const select = document.getElementById('restaurant-select');
    const maxSelect = document.getElementById('restaurant-select-count');
    const maxCount = parseInt(maxSelect?.value) || 5;
    
    if (select.value) {
        const selectedOption = select.options[select.selectedIndex];
        const restaurantData = {
            id: select.value, // Keep as string to match with removeRestaurant
            name: selectedOption.text,
            cuisine: selectedOption.getAttribute('data-cuisine') || '',
            city: selectedOption.getAttribute('data-city') || ''
        };
        
        // Convert to string for consistent comparison
        const newId = String(restaurantData.id);
        
        // Check if restaurant already exists using string comparison
        if (!selectedRestaurants.some(r => String(r.id) === newId)) {
            if (selectedRestaurants.length >= maxCount) {
                alert(`Maximum ${maxCount} restaurant(s) can be selected`);
                return;
            }
            selectedRestaurants.push(restaurantData);
            updateRestaurantsDisplay();
            select.value = '';
            $(select).trigger('change'); // For Select2
        } else {
            alert('This restaurant is already selected');
        }
    }
}

function removeRestaurant(id) {
    // Convert to string for consistent comparison
    const idToRemove = String(id);
    
    // Filter using string comparison
    selectedRestaurants = selectedRestaurants.filter(r => String(r.id) !== idToRemove);
    
    // Update the display
    updateRestaurantsDisplay();
}

function updateRestaurantsDisplay() {
    const container = document.getElementById('selected-restaurants');
    
    if (!container) {
        console.error('Selected restaurants container not found');
        return;
    }
    
    if (!Array.isArray(selectedRestaurants) || selectedRestaurants.length === 0) {
        container.innerHTML = '<div class="empty-state">No restaurants selected</div>';
        return;
    }
    
    container.innerHTML = selectedRestaurants.map(restaurant => `
        <div class="selected-item">
            <strong>${restaurant.name}</strong>
            <small class="ms-1">${restaurant.cuisine ? `(${restaurant.cuisine})` : ''} ${restaurant.city ? `- ${restaurant.city}` : ''}</small>
            <button type="button" class="remove-btn" onclick="removeRestaurant('${restaurant.id}')">×</button>
            <input type="hidden" name="selected_restaurants[]" value='${JSON.stringify(restaurant)}'>
        </div>
    `).join('');
}
</script>
@endsection 