@extends('layouts.layout')
@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="ri-file-list-3-line me-2 text-primary"></i>Create Package Definition
                </h4>
                <p class="text-muted mb-0">Define package services without day-wise itinerary</p>
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
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('packages.definition.store') }}" method="POST" enctype="multipart/form-data" id="package-definition-form">
            @csrf

            <!-- Basic Details: info, availability & pricing in one card -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-file-list-3-line me-2 text-primary"></i>Basic Details</h5>
                </div>
                <x-alert />
                <div class="card-body">
                    <div class="row g-3 two-col-row">
                        <div class="col-md-6">
                            <label class="form-label">Package Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   name="title" value="{{ old('title') }}" required placeholder="e.g., Singapore Explorer">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Package Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="start_date" value="{{ old('start_date') }}" required min="{{ date('Y-m-d') }}" id="start-date-input">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Package Expiry Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="expiry_date" value="{{ old('expiry_date') }}" required min="{{ date('Y-m-d') }}" id="expiry-date-input">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tour Duration (Days) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('duration_days') is-invalid @enderror"
                                   name="duration_days" value="{{ old('duration_days') }}" min="1" required placeholder="e.g. 3">
                            @error('duration_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Country/Destination <span class="text-danger">*</span></label>
                            <select class="form-select w-100 @error('destination') is-invalid @enderror" id="country-select" name="destination" required>
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->name }}" {{ old('destination') == $country->name ? 'selected' : '' }}>{{ $country->name }}</option>
                                @endforeach
                            </select>
                            @error('destination')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <select class="form-select w-100 @error('city') is-invalid @enderror" id="city-select" name="city" required disabled>
                                <option value="">Select Country First</option>
                            </select>
                            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select w-100 @error('category') is-invalid @enderror" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Adventure" {{ old('category') == 'Adventure' ? 'selected' : '' }}>Adventure</option>
                                <option value="Cultural" {{ old('category') == 'Cultural' ? 'selected' : '' }}>Cultural</option>
                                <option value="City Tour" {{ old('category') == 'City Tour' ? 'selected' : '' }}>City Tour</option>
                                <option value="Beach" {{ old('category') == 'Beach' ? 'selected' : '' }}>Beach</option>
                                <option value="Heritage" {{ old('category') == 'Heritage' ? 'selected' : '' }}>Heritage</option>
                                <option value="Food & Culinary" {{ old('category') == 'Food & Culinary' ? 'selected' : '' }}>Food & Culinary</option>
                            </select>
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Adult Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="price_adult" value="{{ old('price_adult') }}" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Senior Citizen Price</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="price_senior" value="{{ old('price_senior') }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Child Price</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="price_child" value="{{ old('price_child') }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Child Max Age</label>
                            <input type="number" class="form-control" name="child_max_age" value="{{ old('child_max_age') }}" min="1" id="child-max-age-input">
                        </div>
                        <!-- Images -->
                        <div class="card mb-4">
                            
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Main Image <span class="text-danger">*</span></label>
                                        <input type="file" id="main_image" name="main_image" accept="image/*" class="d-none">
                                        <div id="main-image-drop-area" class="form-control" style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px; cursor: pointer;">
                                            Drag & Drop or click to upload.
                                        </div>
                                        <small class="text-danger d-none" id="main-image-required-msg">Main image is required.</small>
                                        <div id="main-image-preview-container" class="mt-2"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Gallery Images</label>
                                        <div id="gallery-drop-area" class="form-control" style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px; cursor: pointer;">
                                            Drag & Drop or click to upload.
                                            <input type="file" id="gallery_images" name="gallery_images[]" accept="image/*" multiple style="display: none;">
                                        </div>
                                        <div id="gallery-preview-container" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3" placeholder="Brief description...">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hotels & Attractions: two side-by-side boxes -->
            <div class="card mb-4">
                <div class="card-header bg-light py-2">
                    <h5 class="mb-0"><i class="ri-hotel-line me-2 text-primary"></i><i class="ri-map-pin-line me-2 text-success"></i>Hotels & Attractions</h5>
                </div>
                <div class="card-body py-3">
                    <div class="row g-3">
                        <!-- Hotel box (col-md-6) -->
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100 hotel-attraction-box">
                                <h6 class="fw-semibold mb-2 text-primary"><i class="ri-hotel-line me-1"></i>Hotel</h6>
                                <label class="form-label small mb-1">Select Hotel</label>
                                <select class="form-select form-select-sm w-100 mb-2" id="definition-hotel-select" name="definition_hotel_id">
                                    <option value="">Select City First</option>
                                </select>
                                <div id="definition-rooms-wrapper" style="display: none;">
                                    <div class="bg-primary-subtle rounded p-2 mb-2">
                                        <h6 class="small fw-semibold mb-1"><i class="ri-hotel-bed-line me-1"></i>Room Type</h6>
                                        <div class="d-flex align-items-end gap-2 flex-wrap mb-2">
                                            <div class="flex-grow-1" style="min-width: 140px;">
                                                <label class="form-label small mb-0">Room Type</label>
                                                <select class="form-select form-select-sm" id="definition-room-type-select">
                                                    <option value="">Select room type</option>
                                                </select>
                                            </div>
                                            <div style="width: 70px;">
                                                <label class="form-label small mb-0">Qty</label>
                                                <input type="number" class="form-control form-control-sm" id="definition-room-type-qty" min="1" value="1">
                                            </div>
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="definition-room-add-line"><i class="ri-add-line me-1"></i>Add room</button>
                                        </div>
                                        <div id="definition-pending-rooms" class="small mb-2"></div>
                                        <div class="d-flex align-items-end gap-2 flex-wrap">
                                            <div>
                                                <label class="form-label small mb-0">Nights</label>
                                                <input type="number" class="form-control form-control-sm" id="definition-nights" min="1" value="1" style="width: 70px;">
                                            </div>
                                            <button type="button" class="btn btn-primary btn-sm" id="definition-hotel-add-btn"><i class="ri-add-line me-1"></i>Add</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="pt-2 border-top mt-2">
                                    <h6 class="small fw-semibold mb-1">Chosen Hotels <span class="text-muted fw-normal">(<span id="definition-total-hotels-count">0</span>)</span></h6>
                                    <div id="definition-chosen-hotels" class="mb-0">
                                        <div class="alert alert-info py-2 small mb-0"><i class="ri-information-line me-1"></i>Select hotel, add rooms, then Add.</div>
                                    </div>
                                    <div id="definition-chosen-hotels-list" class="mt-1" style="display: none;"></div>
                                </div>
                            </div>
                        </div>
                        <!-- Attraction box (col-md-6) -->
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100 hotel-attraction-box">
                                <h6 class="fw-semibold mb-2 text-success"><i class="ri-map-pin-line me-1"></i>Attraction</h6>
                                <label class="form-label small mb-1">Select Attraction</label>
                                <select class="form-select form-select-sm w-100 mb-2" id="definition-attraction-select">
                                    <option value="">Select City First</option>
                                </select>
                                <div id="definition-attraction-config" style="display: none;">
                                    <div class="bg-success-subtle rounded p-2 mb-2">
                                        <h6 class="small fw-semibold mb-1">Guide & transfer</h6>
                                        <div class="row g-1">
                                            <div class="col-12">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="definition-attraction-config-need-guide">
                                                    <label class="form-check-label small" for="definition-attraction-config-need-guide">Need guide</label>
                                                </div>
                                            </div>
                                            <div class="col-12" id="definition-attraction-config-guide-wrap" style="display: none;">
                                                <label class="form-label small mb-0">Select guide</label>
                                                <select class="form-select form-select-sm" id="definition-attraction-config-guide">
                                                    <option value="">Select guide</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="definition-attraction-config-transfer">
                                                    <label class="form-check-label small" for="definition-attraction-config-transfer">Include transfer</label>
                                                </div>
                                            </div>
                                            <div class="col-12" id="definition-attraction-config-vehicle-wrap" style="display: none;">
                                                <div class="row g-2 mb-2">
                                                    <div class="col-md-6">
                                                        <label class="form-label small mb-0">Vehicle</label>
                                                        <select class="form-select form-select-sm" id="definition-attraction-config-vehicle"><option value="">Select vehicle</option></select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small mb-0">Pickup (hotels / restaurants)</label>
                                                        <select class="form-select form-select-sm" id="definition-attraction-config-pickup"><option value="">Add hotels or restaurants first</option></select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small mb-0">Dropoff (e.g. attraction)</label>
                                                        <select class="form-select form-select-sm" id="definition-attraction-config-dropoff"><option value="">—</option></select>
                                                    </div>
                                                </div>
                                                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="definition_attr_transfer_type" value="private" id="definition-attr-transfer-private"><label class="form-check-label small" for="definition-attr-transfer-private">Private</label></div>
                                                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="definition_attr_transfer_type" value="shared" id="definition-attr-transfer-shared"><label class="form-check-label small" for="definition-attr-transfer-shared">Shared</label></div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-success btn-sm mt-1" id="definition-attraction-add-btn"><i class="ri-add-line me-1"></i>Add</button>
                                    </div>
                                </div>
                                <div class="pt-2 border-top mt-2">
                                    <h6 class="small fw-semibold mb-1">Chosen Attractions</h6>
                                    <div id="definition-attractions-empty" class="alert alert-info py-2 small mb-0"><i class="ri-information-line me-1"></i>Select attraction, set options, then Add.</div>
                                    <div id="definition-attractions-list" class="mt-1" style="display: none;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="selected_hotels" id="definition-hotels-input" value="[]">
                    <input type="hidden" name="selected_attractions" id="definition-attractions-input" value="[]">
                </div>
            </div>

            <!-- Restaurants & Guide: two side-by-side boxes (same as Hotels & Attractions) -->
            <div class="card mb-4">
                <div class="card-header bg-light py-2">
                    <h5 class="mb-0"><i class="ri-restaurant-line me-2 text-warning"></i><i class="ri-user-voice-line me-2 text-info"></i>Restaurants & Guide</h5>
                </div>
                <div class="card-body py-3">
                    <div class="row g-3">
                        <!-- Restaurant box (col-md-6) -->
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100 hotel-attraction-box">
                                <h6 class="fw-semibold mb-2 text-warning"><i class="ri-restaurant-line me-1"></i>Restaurant</h6>
                                <label class="form-label small mb-1">Select Restaurant</label>
                                <select class="form-select form-select-sm w-100 mb-2" id="definition-restaurant-select">
                                    <option value="">Select City First</option>
                                </select>
                                <div id="definition-restaurant-config" style="display: none;">
                                    <div id="definition-restaurant-meals-wrap" class="bg-warning-subtle rounded p-2 mb-2" style="display: none;">
                                        <h6 class="small fw-semibold mb-1">Meals</h6>
                                        <div class="d-flex flex-wrap gap-2">
                                            <div class="form-check form-check-inline mb-0" id="definition-rest-meal-breakfast-wrap" style="display: none;">
                                                <input class="form-check-input" type="checkbox" id="definition-restaurant-meal-breakfast" value="breakfast">
                                                <label class="form-check-label small" for="definition-restaurant-meal-breakfast">Breakfast</label>
                                            </div>
                                            <div class="form-check form-check-inline mb-0" id="definition-rest-meal-lunch-wrap" style="display: none;">
                                                <input class="form-check-input" type="checkbox" id="definition-restaurant-meal-lunch" value="lunch">
                                                <label class="form-check-label small" for="definition-restaurant-meal-lunch">Lunch</label>
                                            </div>
                                            <div class="form-check form-check-inline mb-0" id="definition-rest-meal-dinner-wrap" style="display: none;">
                                                <input class="form-check-input" type="checkbox" id="definition-restaurant-meal-dinner" value="dinner">
                                                <label class="form-check-label small" for="definition-restaurant-meal-dinner">Dinner</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-warning-subtle rounded p-2 mb-2">
                                        <h6 class="small fw-semibold mb-1">Transfer</h6>
                                        <div class="form-check form-check-inline mb-1">
                                            <input class="form-check-input" type="checkbox" id="definition-restaurant-config-transfer">
                                            <label class="form-check-label small">Include transfer</label>
                                        </div>
                                        <div id="definition-restaurant-config-vehicle-wrap" style="display: none;">
                                                <div class="row g-2 mb-2">
                                                    <div class="col-md-6">
                                                        <label class="form-label small mb-0">Vehicle</label>
                                                        <select class="form-select form-select-sm" id="definition-restaurant-config-vehicle"><option value="">Select vehicle</option></select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small mb-0">Pickup (hotels / attractions)</label>
                                                        <select class="form-select form-select-sm" id="definition-restaurant-config-pickup"><option value="">Add hotels or attractions first</option></select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small mb-0">Dropoff (restaurant)</label>
                                                        <select class="form-select form-select-sm" id="definition-restaurant-config-dropoff"><option value="">—</option></select>
                                                    </div>
                                                </div>
                                                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="definition_rest_transfer_type" value="private" id="definition-rest-transfer-private"><label class="form-check-label small" for="definition-rest-transfer-private">Private</label></div>
                                                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="definition_rest_transfer_type" value="shared" id="definition-rest-transfer-shared"><label class="form-check-label small" for="definition-rest-transfer-shared">Shared</label></div>
                                            </div>
                                        <button type="button" class="btn btn-warning btn-sm mt-1" id="definition-restaurant-add-btn"><i class="ri-add-line me-1"></i>Add</button>
                                    </div>
                                </div>
                                <div class="pt-2 border-top mt-2">
                                    <h6 class="small fw-semibold mb-1">Chosen Restaurants</h6>
                                    <div id="definition-restaurants-empty" class="alert alert-info py-2 small mb-0"><i class="ri-information-line me-1"></i>Select restaurant, set transfer, then Add.</div>
                                    <div id="definition-restaurants-list" class="mt-1" style="display: none;"></div>
                                </div>
                            </div>
                        </div>
                        <!-- Guide box (col-md-6) -->
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100 hotel-attraction-box">
                                <h6 class="fw-semibold mb-2 text-info"><i class="ri-user-voice-line me-1"></i>Guide</h6>
                                <label class="form-label small mb-1">Select Guide</label>
                                <div class="d-flex gap-2 mb-2">
                                    <select class="form-select form-select-sm flex-grow-1" id="definition-guide-select">
                                        <option value="">Select City First / No guide</option>
                                    </select>
                                    <button type="button" class="btn btn-info btn-sm align-self-end" id="definition-guide-add-btn"><i class="ri-add-line me-1"></i>Add</button>
                                </div>
                                <div id="definition-guide-duration-wrap" class="bg-info-subtle rounded p-2 mb-2" style="display: none;">
                                    <h6 class="small fw-semibold mb-1">Duration (time)</h6>
                                    <select class="form-select form-select-sm" id="definition-guide-duration">
                                        <option value="">Select duration</option>
                                        <option value="hourly">1 Hour</option>
                                        <option value="two_hour">2 Hours</option>
                                        <option value="four_hour">4 Hours</option>
                                        <option value="six_hour">6 Hours</option>
                                        <option value="eight_hour">8 Hours</option>
                                        <option value="ten_hour">10 Hours</option>
                                        <option value="twelve_hour">12 Hours</option>
                                    </select>
                                    <small class="text-muted d-block mt-1">Price is taken from guide's rate for selected duration.</small>
                                </div>
                                <div class="pt-2 border-top mt-2">
                                    <h6 class="small fw-semibold mb-1">Chosen Guides <span class="text-muted fw-normal">(<span id="definition-total-guides-count">0</span>)</span></h6>
                                    <div id="definition-guides-empty" class="alert alert-info py-2 small mb-0"><i class="ri-information-line me-1"></i>Select guide, then Add.</div>
                                    <div id="definition-guides-list" class="mt-1" style="display: none;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="selected_restaurants" id="definition-restaurants-input" value="[]">
                    <input type="hidden" name="definition_independent_guide" id="definition-independent-guide-input" value="[]">
                </div>
            </div>

            <!-- Transfers: Arrival (port → hotel) & Departure (hotel → port), search & choose vehicle -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-flight-land-line me-2 text-primary"></i>Arrival & Departure</h5>
                </div>
                <div class="card-body" style="margin-top: 12px;">
                    <div class="row g-3">
                        <!-- Arrival Pickup: port (pickup) → hotel (dropoff), then search vehicle & choose -->
                        <div class="col-md-6">
                            <div class="card border-info border-opacity-25">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-info-subtle text-info p-2 rounded-circle me-3"><i class="ri-flight-land-line"></i></span>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">Arrival Pickup</h6>
                                            <small class="text-muted">Port → Hotel</small>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="arrival-pickup-def" value="1">
                                            <label class="form-check-label" for="arrival-pickup-def">Include</label>
                                        </div>
                                    </div>
                                    <div id="arrival-pickup-config" class="mt-2 pt-2 border-top" style="display: none;">
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <label class="form-label small mb-0">Pickup (Port)</label>
                                                <select class="form-select form-select-sm" id="arrival-pickup-port">
                                                    <option value="">Select country first</option>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small mb-0">Dropoff (Hotel)</label>
                                                <select class="form-select form-select-sm" id="arrival-dropoff-hotel">
                                                    <option value="">Add hotels first</option>
                                                </select>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-info btn-sm mb-2" id="arrival-search-vehicle-btn"><i class="ri-search-line me-1"></i>Search vehicle</button>
                                        <div id="arrival-vehicle-select-wrap" style="display: none;">
                                            <label class="form-label small mb-0">Choose vehicle (zone-based)</label>
                                            <div class="d-flex gap-2 mb-1">
                                                <select class="form-select form-select-sm flex-grow-1" id="arrival-vehicle-select">
                                                    <option value="">Select vehicle</option>
                                                </select>
                                                <button type="button" class="btn btn-info btn-sm" id="arrival-add-vehicle-btn"><i class="ri-add-line"></i> Add</button>
                                            </div>
                                            <div id="arrival-chosen-vehicles" class="small mt-1"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Departure Service: hotel (pickup) → port (dropoff), then search vehicle & choose -->
                        <div class="col-md-6">
                            <div class="card border-warning border-opacity-25">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-warning-subtle text-warning p-2 rounded-circle me-3"><i class="ri-flight-takeoff-line"></i></span>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">Departure Service</h6>
                                            <small class="text-muted">Hotel → Port</small>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="departure-service-def" value="1">
                                            <label class="form-check-label" for="departure-service-def">Include</label>
                                        </div>
                                    </div>
                                    <div id="departure-service-config" class="mt-2 pt-2 border-top" style="display: none;">
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <label class="form-label small mb-0">Pickup (Hotel)</label>
                                                <select class="form-select form-select-sm" id="departure-pickup-hotel">
                                                    <option value="">Add hotels first</option>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small mb-0">Dropoff (Port)</label>
                                                <select class="form-select form-select-sm" id="departure-dropoff-port">
                                                    <option value="">Select country first</option>
                                                </select>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-warning btn-sm mb-2" id="departure-search-vehicle-btn"><i class="ri-search-line me-1"></i>Search vehicle</button>
                                        <div id="departure-vehicle-select-wrap" style="display: none;">
                                            <label class="form-label small mb-0">Choose vehicle (zone-based)</label>
                                            <div class="d-flex gap-2 mb-1">
                                                <select class="form-select form-select-sm flex-grow-1" id="departure-vehicle-select">
                                                    <option value="">Select vehicle</option>
                                                </select>
                                                <button type="button" class="btn btn-warning btn-sm" id="departure-add-vehicle-btn"><i class="ri-add-line"></i> Add</button>
                                            </div>
                                            <div id="departure-chosen-vehicles" class="small mt-1"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="arrival_pickup" id="arrival-pickup-hidden" value="0">
                    <input type="hidden" name="arrival_pickup_port_id" id="arrival-pickup-port-hidden" value="">
                    <input type="hidden" name="arrival_dropoff_hotel_id" id="arrival-dropoff-hotel-hidden" value="">
                    <input type="hidden" name="arrival_vehicles" id="arrival-vehicles-hidden" value="[]">
                    <input type="hidden" name="departure_service" id="departure-service-hidden" value="0">
                    <input type="hidden" name="departure_pickup_hotel_id" id="departure-pickup-hotel-hidden" value="">
                    <input type="hidden" name="departure_dropoff_port_id" id="departure-dropoff-port-hidden" value="">
                    <input type="hidden" name="departure_vehicles" id="departure-vehicles-hidden" value="[]">
                </div>
            </div>

            <!-- Transport: Local Transfer (pickup & dropoff = hotels, attractions, restaurants, ports; dropoff excludes selected pickup) -->
            <div class="card mb-5">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-car-line me-2 text-secondary"></i>Transport</h5>
                </div>
                <div class="card-body" style="margin-top: 12px;">
                    <p class="text-muted small mb-3">Pickup and dropoff can be any hotel, attraction, restaurant or port. Dropoff will not show the selected pickup location.</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <label class="form-label">Pickup location</label>
                            <select class="form-select" id="local-transfer-pickup">
                                <option value="">Select location</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Dropoff location</label>
                            <select class="form-select" id="local-transfer-dropoff">
                                <option value="">Select location (excludes pickup)</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Search vehicle</label>
                            <button type="button" class="btn btn-secondary btn-sm w-100" id="local-transfer-search-vehicle-btn"><i class="ri-search-line me-1"></i>Search vehicle (zone-based)</button>
                            
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div id="local-transfer-vehicle-wrap" class="mt-2" style="display: none;">
                            <label class="form-label small mb-1">Choose vehicle(s)</label>
                            <div class="d-flex gap-2 mb-1">
                                <select class="form-select form-select-sm flex-grow-1" id="local-transfer-vehicle-select">
                                    <option value="">Select vehicle</option>
                                </select>
                                <button type="button" class="btn btn-secondary btn-sm" id="local-transfer-add-vehicle-btn"><i class="ri-add-line"></i> Add</button>
                            </div>
                            <div id="local-transfer-chosen-vehicles" class="small"></div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" id="local-transfer-add-btn"><i class="ri-add-line me-1"></i>Add this transfer</button>
                    <div class="mt-3 pt-3 border-top">
                        <h6 class="small fw-semibold mb-2">Chosen local transfers</h6>
                        <div id="local-transfer-empty" class="alert alert-secondary py-2 small mb-0"><i class="ri-information-line me-1"></i>Add pickup/dropoff and vehicles above, then click Add this transfer.</div>
                        <div id="local-transfer-list" style="display: none;"></div>
                    </div>
                    <input type="hidden" name="local_transfers" id="local-transfers-hidden" value="[]">
                </div>
            </div>

            <!-- Add-On services aggregated price data (JSON) -->
            <input type="hidden" name="price_data" id="price-data-hidden" value="[]">

            <!-- Add-On Services Summary: list and total (shown when any add-on service exists) -->
            <div class="card mb-4 border-success border-opacity-25" id="optional-services-summary-card" style="display: none;">
                <div class="card-header bg-success-subtle py-2">
                    <h5 class="mb-0"><i class="ri-price-tag-3-line me-2 text-success"></i>Add-On Services – Price Summary</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Services marked as Add-On and their prices. Total is the sum of all add-on service prices.</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" id="optional-services-summary-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-nowrap">#</th>
                                    <th>Service</th>
                                    <th class="text-nowrap">Type</th>
                                    <th class="text-end text-nowrap">Price</th>
                                </tr>
                            </thead>
                            <tbody id="optional-services-summary-tbody"></tbody>
                            <tfoot class="table-light fw-semibold">
                                <tr>
                                    <td colspan="3" class="text-end">Total (add-on services)</td>
                                    <td class="text-end" id="optional-services-total-cell">—</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div id="optional-services-summary-empty" class="alert alert-light border text-muted py-3 mb-0 mt-2 text-center" style="display: none;">
                        <i class="ri-information-line me-1"></i>No add-on services added yet. Mark any hotel, attraction, restaurant, guide or transfer as <strong>Add-On</strong> to see their prices from the database here.
                    </div>
                </div>
            </div>

            <!-- Inclusions, Exclusions, Terms & Conditions, Status - one box -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-list-check me-2 text-success"></i><i class="ri-file-text-line me-2 text-secondary"></i><i class="ri-toggle-line me-2 text-primary"></i>Inclusions, Exclusions, Terms & Status</h5>
                </div>
                <div class="card-body" style="margin-top: 12px;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Inclusions</label>
                            <textarea class="form-control" name="inclusions" rows="4" placeholder="What's included...">{{ old('inclusions') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Exclusions</label>
                            <textarea class="form-control" name="exclusions" rows="4" placeholder="What's not included...">{{ old('exclusions') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Terms & Conditions</label>
                            <textarea class="form-control" name="terms_conditions" rows="3" placeholder="Terms and conditions...">{{ old('terms_conditions') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required style="max-width: 200px;">
                                <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary"><i class="ri-close-line me-1"></i>Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i>Create Package Definition</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    const baseUrl = '{{ url("/") }}';

    $('#country-select, #city-select').select2();
    $('#definition-hotel-select').select2({ placeholder: 'Select hotel' });
    $('#definition-attraction-select').select2({ placeholder: 'Select attraction' });
    $('#definition-restaurant-select').select2({ placeholder: 'Select restaurant' });
    $('#definition-guide-select').select2({ placeholder: 'Select guide (optional)' });

    // Date validation
    $('#start-date-input').on('change', function() {
        const start = $(this).val();
        if (start) {
            const d = new Date(start);
            d.setDate(d.getDate() + 1);
            $('#expiry-date-input').attr('min', d.toISOString().split('T')[0]);
        }
    });

    // Country → City
    $('#country-select').on('change', function() {
        const country = $(this).val();
        const citySelect = $('#city-select');
        citySelect.empty().prop('disabled', true);
        $('#definition-hotel-select').empty().append('<option value="">Select City First</option>');
        $('#definition-attraction-select').empty().append('<option value="">Select City First</option>');
        $('#definition-attraction-config').hide();
        $('#definition-restaurant-select').empty().append('<option value="">Select City First</option>');
        $('#definition-restaurant-config').hide();
        $('#definition-guide-select').empty().append('<option value="">Select City First</option>');
        $('#definition-rooms-wrapper').hide();
        $('#arrival-pickup-port').empty().append('<option value="">Select country first</option>');
        $('#departure-dropoff-port').empty().append('<option value="">Select country first</option>');
        portsByCountry = [];
        allHotelsForLocalTransfer = [];
        allAttractionsForLocalTransfer = [];
        allRestaurantsForLocalTransfer = [];
        if (typeof refreshLocalTransferPickupDropoff === 'function') refreshLocalTransferPickupDropoff();
        if (typeof definitionGuides !== 'undefined') { definitionGuides = []; if (typeof updateDefinitionGuidesInput === 'function') updateDefinitionGuidesInput(); if (typeof renderChosenGuides === 'function') renderChosenGuides(); }
        if (!country) return;
        $.get(baseUrl + '/ports-by-country/' + encodeURIComponent(country), function(ports) {
            portsByCountry = ports || [];
            const arr = $('#arrival-pickup-port'), dep = $('#departure-dropoff-port');
            arr.empty().append('<option value="">Select port</option>');
            dep.empty().append('<option value="">Select port</option>');
            portsByCountry.forEach(function(p) {
                const portId = p.port_id || p.id;
                const name = p.port_name || p.name;
                if (portId && name) {
                    arr.append(new Option(name, portId));
                    dep.append(new Option(name, portId));
                }
            });
            if (typeof refreshLocalTransferPickupDropoff === 'function') refreshLocalTransferPickupDropoff();
        });
        citySelect.prop('disabled', false);
        $.get(baseUrl + '/cities-by-country/' + encodeURIComponent(country), function(response) {
            citySelect.empty().append('<option value="">Select City</option>');
            response.forEach(function(c) { citySelect.append(new Option(c.name, c.name)); });
        });
    });

    let portsByCountry = [];
    let allHotelsForLocalTransfer = [];
    let allAttractionsForLocalTransfer = [];
    let allRestaurantsForLocalTransfer = [];
    let definitionHotels = [];
    let definitionAttractions = [];
    let definitionRestaurants = [];
    let vehiclesByCity = [];
    let guidesByCity = [];

    // City → Hotel, Attractions, Restaurants, Guides, Vehicles
    $('#city-select').on('change', function() {
        const city = $(this).val();
        if (!city) return;
        if (typeof definitionGuides !== 'undefined') {
            definitionGuides = [];
            if (typeof updateDefinitionGuidesInput === 'function') updateDefinitionGuidesInput();
            if (typeof renderChosenGuides === 'function') renderChosenGuides();
        }

        $.get(baseUrl + '/hotel-city/' + encodeURIComponent(city), function(response) {
            allHotelsForLocalTransfer = Array.isArray(response) ? response : [];
            const sel = $('#definition-hotel-select');
            sel.empty().append('<option value="">Select Hotel</option>');
            allHotelsForLocalTransfer.forEach(function(h) {
                sel.append(new Option(h.name, h.hotel_unique_id));
            });
            if (typeof refreshLocalTransferPickupDropoff === 'function') refreshLocalTransferPickupDropoff();
        });

        $.get(baseUrl + '/attractions/' + encodeURIComponent(city), function(response) {
            allAttractionsForLocalTransfer = Array.isArray(response) ? response : [];
            const sel = $('#definition-attraction-select');
            sel.empty().append('<option value="">Select Attraction</option>');
            allAttractionsForLocalTransfer.forEach(function(a) {
                const opt = new Option(a.name, a.attraction_id);
                $(opt).data('attraction-data', { attraction_id: a.attraction_id, name: a.name, location: a.location, image: a.master_image || '', adult_price: a.adult_price, child_price: a.child_price });
                sel.append(opt);
            });
            if (typeof refreshLocalTransferPickupDropoff === 'function') refreshLocalTransferPickupDropoff();
        });

        $.get(baseUrl + '/restaurants/' + encodeURIComponent(city), function(response) {
            const list = response.restaurants || response;
            allRestaurantsForLocalTransfer = Array.isArray(list) ? list : [];
            const sel = $('#definition-restaurant-select');
            sel.empty().append('<option value="">Select Restaurant</option>');
            allRestaurantsForLocalTransfer.forEach(function(r) {
                sel.append(new Option(r.name, r.restaurant_id));
            });
            if (typeof refreshLocalTransferPickupDropoff === 'function') refreshLocalTransferPickupDropoff();
        });

        $.get(baseUrl + '/guides/' + encodeURIComponent(city), function(response) {
            guidesByCity = response;
            const sel = $('#definition-guide-select');
            sel.empty().append('<option value="">Select City First / No guide</option>');
            response.forEach(function(g) {
                const opt = new Option(g.name + (g.languages && g.languages.length ? ' (' + g.languages.join(', ') + ')' : ''), g.guide_id);
                $(opt).data('guide-data', g);
                sel.append(opt);
            });
        });

    // Guide select: show duration dropdown when a guide is selected; enable only options that have a price
    $('#definition-guide-select').on('change', function() {
        const val = $(this).val();
        const wrap = $('#definition-guide-duration-wrap');
        $('#definition-guide-duration').val('');
        if (!val) {
            wrap.hide();
            return;
        }
        const opt = $(this).find('option:selected');
        const data = opt.data('guide-data');
        if (data) {
            wrap.show();
            const durationSel = $('#definition-guide-duration');
            durationSel.find('option').each(function() {
                const key = $(this).val();
                if (!key) { $(this).prop('disabled', false); return; }
                const priceKey = key === 'hourly' ? 'hourly_price' : key + '_price';
                const hasPrice = data[priceKey] != null && data[priceKey] !== '' && !isNaN(parseFloat(data[priceKey]));
                $(this).prop('disabled', !hasPrice);
            });
        } else {
            wrap.hide();
        }
    });

        $.get(baseUrl + '/get-transport/' + encodeURIComponent(city), function(response) {
            vehiclesByCity = Array.isArray(response) ? response : [];
        });
    });

    let definitionPendingRooms = [];
    let definitionRoomTypesByHotel = [];

    // Hotel → Room types (AJAX): populate Room Type select
    $('#definition-hotel-select').on('change', function() {
        const hotelId = $(this).val();
        const wrapper = $('#definition-rooms-wrapper');
        const roomTypeSelect = $('#definition-room-type-select');
        roomTypeSelect.empty().append('<option value="">Select room type</option>');
        definitionPendingRooms = [];
        renderDefinitionPendingRooms();
        if (!hotelId) {
            wrapper.hide();
            definitionRoomTypesByHotel = [];
            return;
        }
        $.get(baseUrl + '/room-types-by-hotel/' + encodeURIComponent(hotelId), function(res) {
            const roomTypes = res.room_types || [];
            definitionRoomTypesByHotel = roomTypes;
            if (roomTypes.length === 0) {
                roomTypeSelect.append('<option value="" disabled>No rooms found</option>');
            } else {
                roomTypes.forEach(function(rt) {
                    const opt = new Option(rt.name, rt.id);
                    roomTypeSelect.append(opt);
                });
            }
            wrapper.show();
        });
    });

    function renderDefinitionPendingRooms() {
        const el = $('#definition-pending-rooms');
        el.empty();
        if (definitionPendingRooms.length === 0) return;
        definitionPendingRooms.forEach(function(r, i) {
            el.append(`
                <div class="d-flex align-items-center justify-content-between border rounded px-2 py-1 mb-1 bg-white small">
                    <span>${escapeHtml(r.room_type_name)} × ${r.quantity}</span>
                    <button type="button" class="btn btn-link btn-sm p-0 text-danger definition-pending-room-remove" data-i="${i}" title="Remove"><i class="ri-close-line"></i></button>
                </div>
            `);
        });
        $('.definition-pending-room-remove').on('click', function() {
            const i = parseInt($(this).data('i'), 10);
            definitionPendingRooms.splice(i, 1);
            renderDefinitionPendingRooms();
        });
    }

    $('#definition-room-add-line').on('click', function() {
        const roomTypeId = $('#definition-room-type-select').val();
        if (!roomTypeId) {
            alert('Please select a room type.');
            return;
        }
        const roomTypeName = $('#definition-room-type-select').find('option:selected').text();
        const qty = parseInt($('#definition-room-type-qty').val(), 10) || 1;
        if (qty < 1) return;
        definitionPendingRooms.push({ room_type_id: roomTypeId, room_type_name: roomTypeName, quantity: qty });
        renderDefinitionPendingRooms();
        $('#definition-room-type-qty').val(1);
    });

    function formatOptionalPrice(val) {
        if (val === '' || val == null || isNaN(parseFloat(val))) return '—';
        return '₹' + parseFloat(val).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Add hotel entry to chosen list (uses pending rooms from select + Add room)
    $('#definition-hotel-add-btn').on('click', function() {
        const hotelId = $('#definition-hotel-select').val();
        const hotelName = $('#definition-hotel-select').find('option:selected').text();
        const nights = parseInt($('#definition-nights').val()) || 1;
        if (!hotelId) {
            alert('Please select a hotel.');
            return;
        }
        const rooms = definitionPendingRooms.slice();
        if (rooms.length === 0) {
            alert('Please add at least one room type (select room type, set quantity, then click Add room).');
            return;
        }
        if (nights < 1) {
            alert('Please set number of nights to at least 1.');
            return;
        }
        // Optional price from DB: sum (room weekday_price * quantity) * nights
        let dbPrice = 0;
        rooms.forEach(function(r) {
            const rt = (definitionRoomTypesByHotel || []).find(function(x) { return (x.id || x.room_id) == r.room_type_id; });
            if (rt && (rt.weekday_price != null || rt.weekend_price != null)) {
                const p = parseFloat(rt.weekday_price || rt.weekend_price || 0) || 0;
                dbPrice += p * (parseInt(r.quantity, 10) || 1);
            }
        });
        dbPrice = dbPrice * nights;
        const hotelBasePrice = dbPrice > 0 ? dbPrice : '';
        definitionHotels.push({ hotel_id: hotelId, hotel_name: hotelName, nights, rooms, compulsory: false, optional: false, addon: false, optional_price: '', addon_price: hotelBasePrice, base_price: hotelBasePrice });
        updateDefinitionHotelsInput();
        renderChosenHotels();
        resetDefinitionHotelForm();
    });

    function resetDefinitionHotelForm() {
        $('#definition-hotel-select').val('').trigger('change.select2');
        $('#definition-rooms-wrapper').hide();
        $('#definition-room-type-select').empty().append('<option value="">Select room type</option>');
        definitionPendingRooms = [];
        renderDefinitionPendingRooms();
        $('#definition-room-type-qty').val(1);
        $('#definition-nights').val(1);
    }

    function renderChosenHotels() {
        const placeholder = $('#definition-chosen-hotels');
        const listEl = $('#definition-chosen-hotels-list');
        const countEl = $('#definition-total-hotels-count');
        if (definitionHotels.length === 0) {
            placeholder.show().html('<div class="alert alert-info py-3 mb-0 d-flex align-items-center"><i class="ri-information-line me-2 fs-5"></i><span>No hotels selected yet. Choose your hotels above and click <strong>Add</strong>.</span></div>');
            listEl.hide().empty();
            countEl.text('0');
            return;
        }
        placeholder.hide();
        countEl.text(definitionHotels.length);
        listEl.show().empty();
        definitionHotels.forEach(function(entry, idx) {
            const roomsText = entry.rooms.map(function(r) { return r.room_type_name + ' × ' + r.quantity; }).join(', ');
            const isCompulsory = entry.compulsory === true;
            const isOptional = entry.optional === true;
            const isAddon = entry.addon === true;
            const addonPriceRaw = entry.addon_price != null && entry.addon_price !== '' ? entry.addon_price : (entry.base_price != null && entry.base_price !== '' ? entry.base_price : '');
            const addonPrice = addonPriceRaw !== '' ? parseFloat(addonPriceRaw) : '';
            const priceDisplay = isAddon && addonPrice !== '' && !isNaN(addonPrice) ? '<span class="badge bg-primary ms-2 optional-price-badge">' + formatOptionalPrice(addonPrice) + '</span>' : '';
            listEl.append(`
                <div class="card mb-2 border shadow-sm chosen-hotel-card" data-idx="${idx}">
                    <div class="card-body py-2 px-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                                <span class="badge bg-primary-subtle text-primary rounded-circle p-2"><i class="ri-hotel-line"></i></span>
                                <div>
                                    <strong class="d-block">${escapeHtml(entry.hotel_name)}${priceDisplay}</strong>
                                    <small class="text-muted">${entry.nights} night(s) · ${escapeHtml(roomsText)}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input chosen-hotel-mode" type="radio" name="hotel-mode-${idx}" id="hotel-comp-${idx}" data-idx="${idx}" value="compulsory" ${isCompulsory ? 'checked' : ''}>
                                    <label class="form-check-label small" for="hotel-comp-${idx}">Compulsory</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input chosen-hotel-mode" type="radio" name="hotel-mode-${idx}" id="hotel-opt-${idx}" data-idx="${idx}" value="optional" ${isOptional ? 'checked' : ''}>
                                    <label class="form-check-label small" for="hotel-opt-${idx}">Optional</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input chosen-hotel-mode" type="radio" name="hotel-mode-${idx}" id="hotel-addon-${idx}" data-idx="${idx}" value="addon" ${isAddon ? 'checked' : ''}>
                                    <label class="form-check-label small" for="hotel-addon-${idx}">Add-On</label>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-chosen-hotel" data-idx="${idx}" title="Remove">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });
        $('.remove-chosen-hotel').on('click', function() {
            const idx = parseInt($(this).data('idx'));
            definitionHotels.splice(idx, 1);
            updateDefinitionHotelsInput();
            renderChosenHotels();
        });
        $('.chosen-hotel-mode').on('change', function() {
            const idx = parseInt($(this).data('idx'));
            const mode = $(this).val();
            if (definitionHotels[idx]) {
                definitionHotels[idx].compulsory = mode === 'compulsory';
                definitionHotels[idx].optional = mode === 'optional';
                definitionHotels[idx].addon = mode === 'addon';
                definitionHotels[idx].optional_price = '';
                if (definitionHotels[idx].addon) {
                    if ((definitionHotels[idx].addon_price === '' || definitionHotels[idx].addon_price == null) && definitionHotels[idx].base_price != null && definitionHotels[idx].base_price !== '') {
                        definitionHotels[idx].addon_price = definitionHotels[idx].base_price;
                    }
                } else {
                    definitionHotels[idx].addon_price = '';
                }
            }
            updateDefinitionHotelsInput();
            renderChosenHotels();
            if (typeof renderOptionalServicesSummary === 'function') renderOptionalServicesSummary();
        });
        refreshTransferHotelDropdowns();
        if (typeof renderOptionalServicesSummary === 'function') renderOptionalServicesSummary();
    }

    function refreshTransferHotelDropdowns() {
        const arrivalDropoff = $('#arrival-dropoff-hotel'), departurePickup = $('#departure-pickup-hotel');
        arrivalDropoff.empty().append('<option value="">Add hotels first</option>');
        departurePickup.empty().append('<option value="">Add hotels first</option>');
        definitionHotels.forEach(function(h) {
            arrivalDropoff.append(new Option(h.hotel_name, h.hotel_id));
            departurePickup.append(new Option(h.hotel_name, h.hotel_id));
        });
        if (typeof refreshLocalTransferPickupDropoff === 'function') refreshLocalTransferPickupDropoff();
    }

    function updateDefinitionHotelsInput() {
        $('#definition-hotels-input').val(JSON.stringify(definitionHotels));
    }

    // Attraction select: show config panel and populate guide/vehicle
    $('#definition-attraction-select').on('change', function() {
        const val = $(this).val();
        const configEl = $('#definition-attraction-config');
        if (!val) {
            configEl.hide();
            return;
        }
        configEl.show();
        const guideSel = $('#definition-attraction-config-guide');
        guideSel.empty().append('<option value="">Select guide</option>');
        guidesByCity.forEach(function(g) {
            guideSel.append(new Option(g.name, g.guide_id));
        });
        const vehicleSel = $('#definition-attraction-config-vehicle');
        vehicleSel.empty().append('<option value="">Select vehicle</option>');
        vehiclesByCity.forEach(function(v) {
            vehicleSel.append(new Option(v.name + (v.vehicle_type ? ' (' + v.vehicle_type + ')' : ''), v.vehicle_id));
        });
        $('#definition-attraction-config-need-guide').prop('checked', false);
        $('#definition-attraction-config-guide-wrap').hide();
        $('#definition-attraction-config-transfer').prop('checked', false);
        $('#definition-attraction-config-vehicle-wrap').hide();
        $('#definition-attr-transfer-private').prop('checked', true);
    });
    $('#definition-attraction-config-need-guide').on('change', function() {
        const checked = $(this).is(':checked');
        $('#definition-attraction-config-guide-wrap').toggle(checked);
    });
    $('#definition-attraction-config-transfer').on('change', function() {
        const checked = $(this).is(':checked');
        $('#definition-attraction-config-vehicle-wrap').toggle(checked);
        if (checked) {
            const pickupSel = $('#definition-attraction-config-pickup');
            pickupSel.empty().append('<option value="">Add hotels or restaurants first</option>');
            definitionHotels.forEach(function(h) {
                pickupSel.append(new Option(h.hotel_name, 'hotel_' + h.hotel_id));
            });
            definitionRestaurants.forEach(function(r) {
                pickupSel.append(new Option(r.restaurant_name, 'restaurant_' + r.restaurant_id));
            });
            const firstOpt = definitionHotels.length > 0 ? ('hotel_' + definitionHotels[0].hotel_id) : (definitionRestaurants.length > 0 ? ('restaurant_' + definitionRestaurants[0].restaurant_id) : null);
            if (firstOpt) pickupSel.val(firstOpt);
            const dropoffSel = $('#definition-attraction-config-dropoff');
            const opt = $('#definition-attraction-select').find('option:selected');
            const data = opt.data('attraction-data');
            dropoffSel.empty();
            if (data) {
                dropoffSel.append(new Option(data.name, 'attraction_' + data.attraction_id));
                dropoffSel.val('attraction_' + data.attraction_id);
            } else {
                dropoffSel.append('<option value="">—</option>');
            }
        }
    });

    // Attractions: Add = current selection + config; preview = strip like hotel
    $('#definition-attraction-add-btn').on('click', function() {
        const opt = $('#definition-attraction-select').find('option:selected');
        const data = opt.data('attraction-data');
        if (!data || !opt.val()) {
            alert('Please select an attraction.');
            return;
        }
        const needGuide = $('#definition-attraction-config-need-guide').is(':checked');
        const guideId = needGuide ? $('#definition-attraction-config-guide').val() : '';
        const g = guideId ? guidesByCity.find(x => x.guide_id == guideId) : null;
        const transfer = $('#definition-attraction-config-transfer').is(':checked');
        const vehicleId = $('#definition-attraction-config-vehicle').val();
        const v = vehiclesByCity.find(x => x.vehicle_id == vehicleId);
        const transferType = $('input[name="definition_attr_transfer_type"]:checked').val() || 'private';
        const pickupId = $('#definition-attraction-config-pickup').val();
        const pickupName = $('#definition-attraction-config-pickup').find('option:selected').text();
        const dropoffVal = $('#definition-attraction-config-dropoff').val();
        const dropoffName = $('#definition-attraction-config-dropoff').find('option:selected').text();
        const attrPrice = (data.adult_price != null && data.adult_price !== '') ? parseFloat(data.adult_price) : '';
        definitionAttractions.push({
            attraction_id: data.attraction_id,
            name: data.name,
            location: data.location,
            image: data.image || '',
            compulsory: false,
            optional: false,
            addon: false,
            optional_price: '',
            addon_price: attrPrice,
            base_price: attrPrice,
            guide: g ? { id: g.guide_id, name: g.name, languages: g.languages, contact_no: g.contact_no } : null,
            transfer: transfer,
            vehicle_id: v ? v.vehicle_id : null,
            vehicle_name: v ? v.name : null,
            transfer_type: transferType,
            pickup_id: pickupId || null,
            pickup_name: pickupName || null,
            dropoff_id: dropoffVal || null,
            dropoff_name: dropoffName || data.name
        });
        updateDefinitionAttractionsInput();
        renderDefinitionAttractions();
        $('#definition-attraction-select').val('').trigger('change');
        $('#definition-attraction-config').hide();
    });

    function renderDefinitionAttractions() {
        const emptyEl = $('#definition-attractions-empty');
        const container = $('#definition-attractions-list');
        container.empty();
        if (definitionAttractions.length === 0) {
            emptyEl.show();
            container.hide();
            return;
        }
        emptyEl.hide();
        container.show();
        definitionAttractions.forEach(function(a, idx) {
            const parts = [];
            if (a.guide && a.guide.name) parts.push('<i class="ri-user-line me-1" title="Guide"></i>' + escapeHtml(a.guide.name));
            if (a.transfer) {
                if (a.vehicle_name) parts.push('<i class="ri-car-line me-1" title="Vehicle"></i>' + escapeHtml(a.vehicle_name));
                parts.push('Transfer: ' + (a.transfer_type === 'shared' ? 'Shared' : 'Private'));
                const pickupName = a.pickup_name || a.pickup_hotel_name;
                if (pickupName && a.dropoff_name) parts.push(escapeHtml(pickupName) + ' → ' + escapeHtml(a.dropoff_name));
                else if (pickupName) parts.push(escapeHtml(pickupName) + ' → —');
                else if (a.dropoff_name) parts.push('— → ' + escapeHtml(a.dropoff_name));
            }
            const summaryHtml = parts.length ? parts.join(' <span class="text-muted">·</span> ') : '—';
            const isCompulsory = a.compulsory === true;
            const isOptional = a.optional === true;
            const isAddon = a.addon === true;
            const addonPriceRawA = a.addon_price != null && a.addon_price !== '' ? a.addon_price : (a.base_price != null && a.base_price !== '' ? a.base_price : '');
            const addonPriceA = addonPriceRawA !== '' ? parseFloat(addonPriceRawA) : '';
            const priceDisplay = isAddon && addonPriceA !== '' && !isNaN(addonPriceA) ? '<span class="badge bg-success ms-2 optional-price-badge">' + formatOptionalPrice(addonPriceA) + '</span>' : '';
            container.append(`
                <div class="card mb-2 border shadow-sm chosen-hotel-card" data-idx="${idx}">
                    <div class="card-body py-2 px-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                                <span class="badge bg-success-subtle text-success rounded-circle p-2"><i class="ri-map-pin-line"></i></span>
                                <div>
                                    <strong class="d-block">${escapeHtml(a.name)}${priceDisplay}</strong>
                                    <small class="text-muted">${summaryHtml}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input def-attraction-mode" type="radio" name="attr-mode-${idx}" id="attr-comp-${idx}" data-idx="${idx}" value="compulsory" ${isCompulsory ? 'checked' : ''}>
                                    <label class="form-check-label small" for="attr-comp-${idx}">Compulsory</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input def-attraction-mode" type="radio" name="attr-mode-${idx}" id="attr-opt-${idx}" data-idx="${idx}" value="optional" ${isOptional ? 'checked' : ''}>
                                    <label class="form-check-label small" for="attr-opt-${idx}">Optional</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input def-attraction-mode" type="radio" name="attr-mode-${idx}" id="attr-addon-${idx}" data-idx="${idx}" value="addon" ${isAddon ? 'checked' : ''}>
                                    <label class="form-check-label small" for="attr-addon-${idx}">Add-On</label>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-def-attraction" data-idx="${idx}" title="Remove"><i class="ri-delete-bin-line"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });
        $('.remove-def-attraction').on('click', function() {
            const idx = parseInt($(this).data('idx'));
            definitionAttractions.splice(idx, 1);
            renderDefinitionAttractions();
            updateDefinitionAttractionsInput();
        });
        $('.def-attraction-mode').on('change', function() {
            const idx = parseInt($(this).data('idx'));
            const mode = $(this).val();
            if (definitionAttractions[idx]) {
                definitionAttractions[idx].compulsory = mode === 'compulsory';
                definitionAttractions[idx].optional = mode === 'optional';
                definitionAttractions[idx].addon = mode === 'addon';
                definitionAttractions[idx].optional_price = '';
                if (definitionAttractions[idx].addon) {
                    if ((definitionAttractions[idx].addon_price === '' || definitionAttractions[idx].addon_price == null) && definitionAttractions[idx].base_price != null && definitionAttractions[idx].base_price !== '') {
                        definitionAttractions[idx].addon_price = definitionAttractions[idx].base_price;
                    }
                } else {
                    definitionAttractions[idx].addon_price = '';
                }
            }
            updateDefinitionAttractionsInput();
            renderDefinitionAttractions();
            if (typeof renderOptionalServicesSummary === 'function') renderOptionalServicesSummary();
        });
        if (typeof renderOptionalServicesSummary === 'function') renderOptionalServicesSummary();
    }

    function updateDefinitionAttractionsInput() {
        $('#definition-attractions-input').val(JSON.stringify(definitionAttractions));
        if (typeof refreshLocalTransferPickupDropoff === 'function') refreshLocalTransferPickupDropoff();
    }

    // Restaurant select: show config panel, meal checkboxes (by availability), and populate vehicle
    $('#definition-restaurant-select').on('change', function() {
        const val = $(this).val();
        const configEl = $('#definition-restaurant-config');
        if (!val) {
            configEl.hide();
            $('#definition-restaurant-meals-wrap').hide();
            return;
        }
        configEl.show();
        const rest = allRestaurantsForLocalTransfer.find(function(r) { return r.restaurant_id == val || r.restaurant_id === val; });
        const mealsWrap = $('#definition-restaurant-meals-wrap');
        $('#definition-restaurant-meal-breakfast, #definition-restaurant-meal-lunch, #definition-restaurant-meal-dinner').prop('checked', false);
        if (rest && (rest.breakfast_available == 1 || rest.lunch_available == 1 || rest.dinner_available == 1)) {
            mealsWrap.show();
            $('#definition-rest-meal-breakfast-wrap').toggle(rest.breakfast_available == 1);
            $('#definition-rest-meal-lunch-wrap').toggle(rest.lunch_available == 1);
            $('#definition-rest-meal-dinner-wrap').toggle(rest.dinner_available == 1);
        } else {
            mealsWrap.hide();
        }
        const vehicleSel = $('#definition-restaurant-config-vehicle');
        vehicleSel.empty().append('<option value="">Select vehicle</option>');
        vehiclesByCity.forEach(function(v) {
            vehicleSel.append(new Option(v.name + (v.vehicle_type ? ' (' + v.vehicle_type + ')' : ''), v.vehicle_id));
        });
        $('#definition-restaurant-config-transfer').prop('checked', false);
        $('#definition-restaurant-config-vehicle-wrap').hide();
        $('#definition-rest-transfer-private').prop('checked', true);
    });
    $('#definition-restaurant-config-transfer').on('change', function() {
        const checked = $(this).is(':checked');
        $('#definition-restaurant-config-vehicle-wrap').toggle(checked);
        if (checked) {
            const pickupSel = $('#definition-restaurant-config-pickup');
            pickupSel.empty().append('<option value="">Add hotels or attractions first</option>');
            definitionHotels.forEach(function(h) {
                pickupSel.append(new Option(h.hotel_name, 'hotel_' + h.hotel_id));
            });
            definitionAttractions.forEach(function(a) {
                pickupSel.append(new Option(a.name, 'attraction_' + a.attraction_id));
            });
            const firstOpt = definitionHotels.length > 0 ? ('hotel_' + definitionHotels[0].hotel_id) : (definitionAttractions.length > 0 ? ('attraction_' + definitionAttractions[0].attraction_id) : null);
            if (firstOpt) pickupSel.val(firstOpt);
            const dropoffSel = $('#definition-restaurant-config-dropoff');
            const restId = $('#definition-restaurant-select').val();
            const restName = $('#definition-restaurant-select').find('option:selected').text();
            dropoffSel.empty();
            if (restId) {
                dropoffSel.append(new Option(restName, 'restaurant_' + restId));
                dropoffSel.val('restaurant_' + restId);
            } else {
                dropoffSel.append('<option value="">—</option>');
            }
        }
    });

    // Restaurants: Add = current selection + config + selected meals; preview = strip like hotel
    $('#definition-restaurant-add-btn').on('click', function() {
        const id = $('#definition-restaurant-select').val();
        const name = $('#definition-restaurant-select').find('option:selected').text();
        if (!id) {
            alert('Please select a restaurant.');
            return;
        }
        const selectedMeals = [];
        if ($('#definition-restaurant-meal-breakfast').is(':checked')) selectedMeals.push('breakfast');
        if ($('#definition-restaurant-meal-lunch').is(':checked')) selectedMeals.push('lunch');
        if ($('#definition-restaurant-meal-dinner').is(':checked')) selectedMeals.push('dinner');
        const transfer = $('#definition-restaurant-config-transfer').is(':checked');
        const vehicleId = $('#definition-restaurant-config-vehicle').val();
        const v = vehiclesByCity.find(x => x.vehicle_id == vehicleId);
        const transferType = $('input[name="definition_rest_transfer_type"]:checked').val() || 'private';
        const pickupId = $('#definition-restaurant-config-pickup').val();
        const pickupName = $('#definition-restaurant-config-pickup').find('option:selected').text();
        const dropoffVal = $('#definition-restaurant-config-dropoff').val();
        const dropoffName = $('#definition-restaurant-config-dropoff').find('option:selected').text();
        const rest = allRestaurantsForLocalTransfer.find(function(r) { return r.restaurant_id == id || r.restaurant_id === id; });
        const restPrice = rest && (rest.lunch_price != null || rest.bf_price != null || rest.dinner_price != null)
            ? parseFloat(rest.lunch_price || rest.bf_price || rest.dinner_price || 0) : '';
        definitionRestaurants.push({
            restaurant_id: id,
            restaurant_name: name,
            compulsory: false,
            optional: false,
            addon: false,
            optional_price: '',
            addon_price: restPrice,
            base_price: restPrice,
            selected_meals: selectedMeals,
            transfer: transfer,
            vehicle_id: v ? v.vehicle_id : null,
            vehicle_name: v ? v.name : null,
            transfer_type: transferType,
            pickup_id: pickupId || null,
            pickup_name: pickupName || null,
            dropoff_id: dropoffVal || null,
            dropoff_name: dropoffName || name
        });
        updateDefinitionRestaurantsInput();
        renderDefinitionRestaurants();
        $('#definition-restaurant-select').val('').trigger('change');
        $('#definition-restaurant-config').hide();
    });

    function renderDefinitionRestaurants() {
        const emptyEl = $('#definition-restaurants-empty');
        const container = $('#definition-restaurants-list');
        container.empty();
        if (definitionRestaurants.length === 0) {
            emptyEl.show();
            container.hide();
            return;
        }
        emptyEl.hide();
        container.show();
        definitionRestaurants.forEach(function(r, idx) {
            const parts = [];
            if (r.selected_meals && r.selected_meals.length) {
                parts.push('<i class="ri-restaurant-2-line me-1" title="Meals"></i>' + r.selected_meals.map(function(m) { return m.charAt(0).toUpperCase() + m.slice(1); }).join(', '));
            }
            if (r.transfer) {
                if (r.vehicle_name) parts.push('<i class="ri-car-line me-1" title="Vehicle"></i>' + escapeHtml(r.vehicle_name));
                parts.push('Transfer: ' + (r.transfer_type === 'shared' ? 'Shared' : 'Private'));
                const pickupName = r.pickup_name || r.pickup_hotel_name;
                if (pickupName && r.dropoff_name) parts.push(escapeHtml(pickupName) + ' → ' + escapeHtml(r.dropoff_name));
                else if (pickupName) parts.push(escapeHtml(pickupName) + ' → —');
                else if (r.dropoff_name) parts.push('— → ' + escapeHtml(r.dropoff_name));
            }
            const summaryHtml = parts.length ? parts.join(' <span class="text-muted">·</span> ') : '—';
            const isCompulsory = r.compulsory === true;
            const isOptional = r.optional === true;
            const isAddon = r.addon === true;
            const addonPriceRawR = r.addon_price != null && r.addon_price !== '' ? r.addon_price : (r.base_price != null && r.base_price !== '' ? r.base_price : '');
            const addonPriceR = addonPriceRawR !== '' ? parseFloat(addonPriceRawR) : '';
            const priceDisplay = isAddon && addonPriceR !== '' && !isNaN(addonPriceR) ? '<span class="badge bg-warning text-dark ms-2 optional-price-badge">' + formatOptionalPrice(addonPriceR) + '</span>' : '';
            container.append(`
                <div class="card mb-2 border shadow-sm chosen-hotel-card" data-idx="${idx}">
                    <div class="card-body py-2 px-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                                <span class="badge bg-warning-subtle text-warning rounded-circle p-2"><i class="ri-restaurant-line"></i></span>
                                <div>
                                    <strong class="d-block">${escapeHtml(r.restaurant_name)}${priceDisplay}</strong>
                                    <small class="text-muted">${summaryHtml}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input def-restaurant-mode" type="radio" name="rest-mode-${idx}" id="rest-comp-${idx}" data-idx="${idx}" value="compulsory" ${isCompulsory ? 'checked' : ''}>
                                    <label class="form-check-label small" for="rest-comp-${idx}">Compulsory</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input def-restaurant-mode" type="radio" name="rest-mode-${idx}" id="rest-opt-${idx}" data-idx="${idx}" value="optional" ${isOptional ? 'checked' : ''}>
                                    <label class="form-check-label small" for="rest-opt-${idx}">Optional</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input def-restaurant-mode" type="radio" name="rest-mode-${idx}" id="rest-addon-${idx}" data-idx="${idx}" value="addon" ${isAddon ? 'checked' : ''}>
                                    <label class="form-check-label small" for="rest-addon-${idx}">Add-On</label>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-def-restaurant" data-idx="${idx}" title="Remove"><i class="ri-delete-bin-line"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });
        $('.remove-def-restaurant').on('click', function() {
            const idx = parseInt($(this).data('idx'));
            definitionRestaurants.splice(idx, 1);
            renderDefinitionRestaurants();
            updateDefinitionRestaurantsInput();
        });
        $('.def-restaurant-mode').on('change', function() {
            const idx = parseInt($(this).data('idx'));
            const mode = $(this).val();
            if (definitionRestaurants[idx]) {
                definitionRestaurants[idx].compulsory = mode === 'compulsory';
                definitionRestaurants[idx].optional = mode === 'optional';
                definitionRestaurants[idx].addon = mode === 'addon';
                definitionRestaurants[idx].optional_price = '';
                if (definitionRestaurants[idx].addon) {
                    if ((definitionRestaurants[idx].addon_price === '' || definitionRestaurants[idx].addon_price == null) && definitionRestaurants[idx].base_price != null && definitionRestaurants[idx].base_price !== '') {
                        definitionRestaurants[idx].addon_price = definitionRestaurants[idx].base_price;
                    }
                } else {
                    definitionRestaurants[idx].addon_price = '';
                }
            }
            updateDefinitionRestaurantsInput();
            renderDefinitionRestaurants();
            if (typeof renderOptionalServicesSummary === 'function') renderOptionalServicesSummary();
        });
        if (typeof renderOptionalServicesSummary === 'function') renderOptionalServicesSummary();
    }

    function updateDefinitionRestaurantsInput() {
        $('#definition-restaurants-input').val(JSON.stringify(definitionRestaurants));
        if (typeof refreshLocalTransferPickupDropoff === 'function') refreshLocalTransferPickupDropoff();
    }

    // --- Transport: Local Transfer (pickup/dropoff = all hotels, attractions, restaurants, ports from DB; dropoff excludes pickup) ---
    function buildLocalTransferLocationList() {
        const list = [];
        (allHotelsForLocalTransfer || []).forEach(function(h) {
            const id = h.hotel_unique_id || h.hotel_id;
            const name = h.name || h.hotel_name || '';
            if (id && name) list.push({ value: 'hotel_' + id, label: name + ' (Hotel)', type: 'Hotel', zoneId: id });
        });
        (allAttractionsForLocalTransfer || []).forEach(function(a) {
            const id = a.attraction_id || a.id;
            const name = a.name || '';
            if (id && name) list.push({ value: 'attraction_' + id, label: name + ' (Attraction)', type: 'Attraction', zoneId: id });
        });
        (allRestaurantsForLocalTransfer || []).forEach(function(r) {
            const id = r.restaurant_id || r.id;
            const name = r.name || r.restaurant_name || '';
            if (id && name) list.push({ value: 'restaurant_' + id, label: name + ' (Restaurant)', type: 'Restaurant', zoneId: id });
        });
        (portsByCountry || []).forEach(function(p) {
            const portId = p.port_id || p.id;
            const name = p.port_name || p.name;
            if (portId && name) list.push({ value: 'port_' + portId, label: name + ' (Port)', type: 'Port', zoneId: portId });
        });
        return list;
    }

    function refreshLocalTransferPickupDropoff() {
        const list = buildLocalTransferLocationList();
        const pickupSel = $('#local-transfer-pickup');
        const dropoffSel = $('#local-transfer-dropoff');
        const currentPickup = pickupSel.val();
        pickupSel.empty().append('<option value="">Select location</option>');
        list.forEach(function(item) {
            pickupSel.append(new Option(item.label, item.value));
        });
        if (currentPickup && list.some(function(x) { return x.value === currentPickup; })) pickupSel.val(currentPickup);
        dropoffSel.empty().append('<option value="">Select location (excludes pickup)</option>');
        list.forEach(function(item) {
            if (item.value !== pickupSel.val()) dropoffSel.append(new Option(item.label, item.value));
        });
        var curDrop = dropoffSel.val();
        if (curDrop && list.some(function(x) { return x.value === curDrop && x.value !== pickupSel.val(); })) dropoffSel.val(curDrop);
    }

    let localTransferVehiclesByZone = [];
    let localTransferChosenVehicles = [];
    let localTransfersList = [];

    $(document).ready(function() {
        refreshLocalTransferPickupDropoff();
    });

    $('#local-transfer-pickup').on('change', function() {
        const list = buildLocalTransferLocationList();
        const dropoffSel = $('#local-transfer-dropoff');
        const pickupVal = $(this).val();
        dropoffSel.empty().append('<option value="">Select location (excludes pickup)</option>');
        list.forEach(function(item) {
            if (item.value !== pickupVal) dropoffSel.append(new Option(item.label, item.value));
        });
    });

    $('#local-transfer-search-vehicle-btn').on('click', function() {
        const pickupVal = $('#local-transfer-pickup').val();
        const dropoffVal = $('#local-transfer-dropoff').val();
        const city = $('#city-select').val();
        if (!pickupVal || !dropoffVal) { alert('Select pickup and dropoff first.'); return; }
        if (!city) { alert('Select city first.'); return; }
        const list = buildLocalTransferLocationList();
        const fromItem = list.find(function(x) { return x.value === pickupVal; });
        const toItem = list.find(function(x) { return x.value === dropoffVal; });
        if (!fromItem || !toItem) { alert('Invalid pickup or dropoff.'); return; }
        const btn = $(this).prop('disabled', true);
        $.ajax({
            url: fetchVehiclesByZonesUrl,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                from_zone_id: fromItem.zoneId,
                to_zone_id: toItem.zoneId,
                from_zone_type: fromItem.type,
                to_zone_type: toItem.type,
                city: city,
                zone_status: 1
            },
            success: function(res) {
                localTransferVehiclesByZone = res.vehicles || [];
                const sel = $('#local-transfer-vehicle-select');
                sel.empty().append('<option value="">Select vehicle</option>');
                localTransferVehiclesByZone.forEach(function(v) {
                    const name = (v.vehicle_name || v.name) + (v.vehicle_type ? ' (' + v.vehicle_type + ')' : '');
                    sel.append(new Option(name, v.vehicle_id));
                });
                $('#local-transfer-vehicle-wrap').show();
            },
            error: function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'No vehicles found for this route.';
                alert(msg);
            }
        }).always(function() { btn.prop('disabled', false); });
    });

    $('#local-transfer-add-vehicle-btn').on('click', function() {
        const val = $('#local-transfer-vehicle-select').val();
        if (!val) return;
        const v = localTransferVehiclesByZone.find(function(x) { return x.vehicle_id == val; });
        if (!v) return;
        if (localTransferChosenVehicles.some(function(x) { return x.vehicle_id == val; })) return;
        const priv = parseFloat(v.private_price) || 0;
        const shared = parseFloat(v.shared_price) || 0;
        localTransferChosenVehicles.push({ vehicle_id: v.vehicle_id, vehicle_name: v.vehicle_name || v.name, vehicle_type: v.vehicle_type, private_price: priv, shared_price: shared });
        const html = localTransferChosenVehicles.map(function(x, i) {
            return '<span class="badge bg-secondary me-1 mb-1">' + (x.vehicle_name || '') + (x.vehicle_type ? ' (' + x.vehicle_type + ')' : '') + ' <a href="#" class="text-white local-transfer-remove-vehicle" data-idx="' + i + '">×</a></span>';
        }).join('');
        $('#local-transfer-chosen-vehicles').html(html || '—');
    });

    $(document).on('click', '.local-transfer-remove-vehicle', function(e) {
        e.preventDefault();
        const idx = parseInt($(this).data('idx'), 10);
        localTransferChosenVehicles.splice(idx, 1);
        const html = localTransferChosenVehicles.map(function(x, i) {
            return '<span class="badge bg-secondary me-1 mb-1">' + (x.vehicle_name || '') + (x.vehicle_type ? ' (' + x.vehicle_type + ')' : '') + ' <a href="#" class="text-white local-transfer-remove-vehicle" data-idx="' + i + '">×</a></span>';
        }).join('');
        $('#local-transfer-chosen-vehicles').html(html || '—');
    });

    $('#local-transfer-add-btn').on('click', function() {
        const pickupVal = $('#local-transfer-pickup').val();
        const dropoffVal = $('#local-transfer-dropoff').val();
        if (!pickupVal || !dropoffVal) { alert('Select pickup and dropoff first.'); return; }
        const list = buildLocalTransferLocationList();
        const fromItem = list.find(function(x) { return x.value === pickupVal; });
        const toItem = list.find(function(x) { return x.value === dropoffVal; });
        if (!fromItem || !toItem) return;
        const transferPrice = localTransferChosenVehicles.reduce(function(sum, v) {
            return sum + (parseFloat(v.private_price) || parseFloat(v.shared_price) || 0);
        }, 0);
        const transferBasePrice = transferPrice > 0 ? transferPrice : '';
        localTransfersList.push({
            pickup_value: pickupVal,
            pickup_label: fromItem.label,
            pickup_type: fromItem.type,
            pickup_zone_id: fromItem.zoneId,
            dropoff_value: dropoffVal,
            dropoff_label: toItem.label,
            dropoff_type: toItem.type,
            dropoff_zone_id: toItem.zoneId,
            vehicles: localTransferChosenVehicles.slice(),
            compulsory: false,
            optional: false,
            addon: false,
            optional_price: '',
            addon_price: transferBasePrice,
            base_price: transferBasePrice
        });
        localTransferChosenVehicles = [];
        $('#local-transfer-chosen-vehicles').html('—');
        $('#local-transfer-vehicle-wrap').hide();
        $('#local-transfer-vehicle-select').empty().append('<option value="">Select vehicle</option>');
        renderLocalTransfersList();
        $('#local-transfers-hidden').val(JSON.stringify(localTransfersList));
    });

    function renderLocalTransfersList() {
        if (localTransfersList.length === 0) {
            $('#local-transfer-empty').show();
            $('#local-transfer-list').hide().empty();
            return;
        }
        $('#local-transfer-empty').hide();
        const html = localTransfersList.map(function(t, i) {
            const vList = (t.vehicles || []).map(function(v) { return (v.vehicle_name || '') + (v.vehicle_type ? ' (' + v.vehicle_type + ')' : ''); }).join(', ');
            const isCompulsory = t.compulsory === true;
            const isOptional = t.optional === true;
            const isAddon = t.addon === true;
            const addonPriceRawT = t.addon_price != null && t.addon_price !== '' ? t.addon_price : (t.base_price != null && t.base_price !== '' ? t.base_price : '');
            const addonPriceT = addonPriceRawT !== '' ? parseFloat(addonPriceRawT) : '';
            const priceDisplay = isAddon && addonPriceT !== '' && !isNaN(addonPriceT) ? ' <span class="badge bg-secondary optional-price-badge">' + formatOptionalPrice(addonPriceT) + '</span>' : '';
            return '<div class="border rounded p-2 mb-2 small">' +
                '<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">' +
                '<div class="flex-grow-1">' +
                (t.pickup_label || '') + ' → ' + (t.dropoff_label || '') + priceDisplay + (vList ? ' <span class="text-muted">(' + vList + ')</span>' : '') +
                '</div>' +
                '<div class="d-flex align-items-center gap-3">' +
                '<div class="form-check form-check-inline mb-0"><input class="form-check-input local-transfer-mode" type="radio" name="lt-mode-' + i + '" id="lt-comp-' + i + '" data-idx="' + i + '" value="compulsory" ' + (isCompulsory ? 'checked' : '') + '><label class="form-check-label small" for="lt-comp-' + i + '">Compulsory</label></div>' +
                '<div class="form-check form-check-inline mb-0"><input class="form-check-input local-transfer-mode" type="radio" name="lt-mode-' + i + '" id="lt-opt-' + i + '" data-idx="' + i + '" value="optional" ' + (isOptional ? 'checked' : '') + '><label class="form-check-label small" for="lt-opt-' + i + '">Optional</label></div>' +
                '<div class="form-check form-check-inline mb-0"><input class="form-check-input local-transfer-mode" type="radio" name="lt-mode-' + i + '" id="lt-addon-' + i + '" data-idx="' + i + '" value="addon" ' + (isAddon ? 'checked' : '') + '><label class="form-check-label small" for="lt-addon-' + i + '">Add-On</label></div>' +
                '<button type="button" class="btn btn-outline-danger btn-sm local-transfer-remove" data-idx="' + i + '"><i class="ri-delete-bin-line"></i></button>' +
                '</div></div></div>';
        }).join('');
        $('#local-transfer-list').html(html).show();
        if (typeof renderOptionalServicesSummary === 'function') renderOptionalServicesSummary();
    }

    $(document).on('change', '.local-transfer-mode', function() {
        const idx = parseInt($(this).data('idx'), 10);
        const mode = $(this).val();
        if (localTransfersList[idx]) {
            localTransfersList[idx].compulsory = mode === 'compulsory';
            localTransfersList[idx].optional = mode === 'optional';
            localTransfersList[idx].addon = mode === 'addon';
            localTransfersList[idx].optional_price = '';
            if (localTransfersList[idx].addon) {
                if ((localTransfersList[idx].addon_price === '' || localTransfersList[idx].addon_price == null) && localTransfersList[idx].base_price != null && localTransfersList[idx].base_price !== '') {
                    localTransfersList[idx].addon_price = localTransfersList[idx].base_price;
                }
            } else {
                localTransfersList[idx].addon_price = '';
            }
            renderLocalTransfersList();
            $('#local-transfers-hidden').val(JSON.stringify(localTransfersList));
            if (typeof renderOptionalServicesSummary === 'function') renderOptionalServicesSummary();
        }
    });

    $(document).on('click', '.local-transfer-remove', function(e) {
        e.preventDefault();
        const idx = parseInt($(this).data('idx'), 10);
        localTransfersList.splice(idx, 1);
        renderLocalTransfersList();
        $('#local-transfers-hidden').val(JSON.stringify(localTransfersList));
        if (typeof renderOptionalServicesSummary === 'function') renderOptionalServicesSummary();
    });

    // Independent guides: multiple with Compulsory/Optional (mutually exclusive)
    let definitionGuides = [];

    $('#definition-guide-add-btn').on('click', function() {
        const opt = $('#definition-guide-select').find('option:selected');
        const data = opt.data('guide-data');
        if (!data || !opt.val()) {
            alert('Please select a guide.');
            return;
        }
        const durationKey = $('#definition-guide-duration').val();
        const priceKey = durationKey === 'hourly' ? 'hourly_price' : (durationKey ? durationKey + '_price' : 'hourly_price');
        let guidePrice = '';
        if (durationKey && data[priceKey] != null && data[priceKey] !== '') {
            guidePrice = parseFloat(data[priceKey]);
        } else if (data.hourly_price != null && data.hourly_price !== '') {
            guidePrice = parseFloat(data.hourly_price);
        }
        const durationLabels = { hourly: '1 Hour', two_hour: '2 Hours', four_hour: '4 Hours', six_hour: '6 Hours', eight_hour: '8 Hours', ten_hour: '10 Hours', twelve_hour: '12 Hours' };
        const durationLabel = durationKey ? (durationLabels[durationKey] || '') : '';
        definitionGuides.push({
            id: data.guide_id,
            name: data.name,
            languages: data.languages || [],
            contact_no: data.contact_no || '',
            compulsory: false,
            optional: false,
            addon: false,
            optional_price: '',
            addon_price: guidePrice,
            base_price: guidePrice,
            duration_key: durationKey || 'hourly',
            duration_label: durationLabel
        });
        updateDefinitionGuidesInput();
        renderChosenGuides();
        $('#definition-guide-select').val('').trigger('change.select2');
        $('#definition-guide-duration').val('');
        $('#definition-guide-duration-wrap').hide();
    });

    function renderChosenGuides() {
        const emptyEl = $('#definition-guides-empty');
        const listEl = $('#definition-guides-list');
        const countEl = $('#definition-total-guides-count');
        if (definitionGuides.length === 0) {
            emptyEl.show();
            listEl.hide().empty();
            countEl.text('0');
            return;
        }
        emptyEl.hide();
        countEl.text(definitionGuides.length);
        listEl.show().empty();
        definitionGuides.forEach(function(g, idx) {
            const isCompulsory = g.compulsory === true;
            const isOptional = g.optional === true;
            const isAddon = g.addon === true;
            const langText = g.languages && g.languages.length ? ' (' + g.languages.join(', ') + ')' : '';
            const durationText = g.duration_label ? ' <span class="text-muted">·</span> ' + escapeHtml(g.duration_label) : '';
            const addonPriceRawG = g.addon_price != null && g.addon_price !== '' ? g.addon_price : (g.base_price != null && g.base_price !== '' ? g.base_price : '');
            const addonPriceG = addonPriceRawG !== '' ? parseFloat(addonPriceRawG) : '';
            const priceDisplay = isAddon && addonPriceG !== '' && !isNaN(addonPriceG) ? '<span class="badge bg-info ms-2 optional-price-badge">' + formatOptionalPrice(addonPriceG) + '</span>' : '';
            listEl.append(`
                <div class="card mb-2 border shadow-sm" data-idx="${idx}">
                    <div class="card-body py-2 px-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                                <span class="badge bg-info-subtle text-info rounded-circle p-2"><i class="ri-user-voice-line"></i></span>
                                <div>
                                    <strong class="d-block">${escapeHtml(g.name)}${priceDisplay}</strong>
                                    <small class="text-muted">${escapeHtml(langText)}${durationText}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input def-guide-mode" type="radio" name="guide-mode-${idx}" id="guide-comp-${idx}" data-idx="${idx}" value="compulsory" ${isCompulsory ? 'checked' : ''}>
                                    <label class="form-check-label small" for="guide-comp-${idx}">Compulsory</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input def-guide-mode" type="radio" name="guide-mode-${idx}" id="guide-opt-${idx}" data-idx="${idx}" value="optional" ${isOptional ? 'checked' : ''}>
                                    <label class="form-check-label small" for="guide-opt-${idx}">Optional</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input def-guide-mode" type="radio" name="guide-mode-${idx}" id="guide-addon-${idx}" data-idx="${idx}" value="addon" ${isAddon ? 'checked' : ''}>
                                    <label class="form-check-label small" for="guide-addon-${idx}">Add-On</label>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-def-guide" data-idx="${idx}" title="Remove"><i class="ri-delete-bin-line"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });
        $('.remove-def-guide').on('click', function() {
            const idx = parseInt($(this).data('idx'));
            definitionGuides.splice(idx, 1);
            renderChosenGuides();
            updateDefinitionGuidesInput();
        });
        $('.def-guide-mode').on('change', function() {
            const idx = parseInt($(this).data('idx'));
            const mode = $(this).val();
            if (definitionGuides[idx]) {
                definitionGuides[idx].compulsory = mode === 'compulsory';
                definitionGuides[idx].optional = mode === 'optional';
                definitionGuides[idx].addon = mode === 'addon';
                definitionGuides[idx].optional_price = '';
                if (definitionGuides[idx].addon) {
                    if ((definitionGuides[idx].addon_price === '' || definitionGuides[idx].addon_price == null) && definitionGuides[idx].base_price != null && definitionGuides[idx].base_price !== '') {
                        definitionGuides[idx].addon_price = definitionGuides[idx].base_price;
                    }
                } else {
                    definitionGuides[idx].addon_price = '';
                }
            }
            updateDefinitionGuidesInput();
            renderChosenGuides();
            if (typeof renderOptionalServicesSummary === 'function') renderOptionalServicesSummary();
        });
        if (typeof renderOptionalServicesSummary === 'function') renderOptionalServicesSummary();
    }

    function updateDefinitionGuidesInput() {
        $('#definition-independent-guide-input').val(JSON.stringify(definitionGuides));
    }

    // Optional Services Summary: collect all optional items and render table + total
    function renderOptionalServicesSummary() {
        const items = [];
        (definitionHotels || []).forEach(function(h) {
            if (h.addon === true) {
                const price = parseOptionalPrice(h.addon_price);
                items.push({ name: h.hotel_name || '', type: 'Hotel', price: price });
            }
        });
        (definitionAttractions || []).forEach(function(a) {
            if (a.addon === true) {
                const price = parseOptionalPrice(a.addon_price);
                items.push({ name: a.name || '', type: 'Attraction', price: price });
            }
        });
        (definitionRestaurants || []).forEach(function(r) {
            if (r.addon === true) {
                const price = parseOptionalPrice(r.addon_price);
                items.push({ name: r.restaurant_name || '', type: 'Restaurant', price: price });
            }
        });
        (definitionGuides || []).forEach(function(g) {
            if (g.addon === true) {
                const price = parseOptionalPrice(g.addon_price);
                items.push({ name: g.name || '', type: 'Guide', price: price });
            }
        });
        (localTransfersList || []).forEach(function(t) {
            if (t.addon === true) {
                const price = parseOptionalPrice(t.addon_price);
                const label = (t.pickup_label || '') + ' → ' + (t.dropoff_label || '');
                items.push({ name: label, type: 'Transfer', price: price });
            }
        });
        function parseOptionalPrice(v) {
            if (v === '' || v == null) return 0;
            const n = parseFloat(v);
            return isNaN(n) ? 0 : n;
        }
        const card = $('#optional-services-summary-card');
        const tbody = $('#optional-services-summary-tbody');
        const totalCell = $('#optional-services-total-cell');
        const emptyEl = $('#optional-services-summary-empty');
        const tableWrap = $('#optional-services-summary-table').closest('.table-responsive');
        if (items.length === 0) {
            card.hide();
            tbody.empty();
            totalCell.text('—');
            $('#price-data-hidden').val('[]');
            return;
        }
        emptyEl.hide();
        tableWrap.show();
        let total = 0;
        tbody.empty();
        items.forEach(function(row, i) {
            total += row.price;
            tbody.append(
                '<tr><td class="text-muted">' + (i + 1) + '</td><td>' + escapeHtml(row.name) + '</td><td><span class="badge bg-light text-dark">' + escapeHtml(row.type) + '</span></td><td class="text-end">' + formatOptionalPrice(row.price) + '</td></tr>'
            );
        });
        totalCell.text(formatOptionalPrice(total));
        card.show();

        // Persist the same array to backend (packages.price_data as JSON)
        // Shape: [{ name: string, type: string, price: number }]
        $('#price-data-hidden').val(JSON.stringify(items));
    }

    // Arrival Pickup: toggle config, sync hidden; Search vehicle → show vehicle dropdown
    $('#arrival-pickup-def').on('change', function() {
        const checked = $(this).is(':checked');
        $('#arrival-pickup-hidden').val(checked ? 1 : 0);
        $('#arrival-pickup-config').toggle(checked);
        if (!checked) {
            $('#arrival-vehicle-select-wrap').hide();
            arrivalChosenVehicles = [];
            renderChosenVehiclesList(arrivalChosenVehicles, 'arrival-chosen-vehicles');
            $('#arrival-vehicles-hidden').val('[]');
        }
    });
    $('#departure-service-def').on('change', function() {
        const checked = $(this).is(':checked');
        $('#departure-service-hidden').val(checked ? 1 : 0);
        $('#departure-service-config').toggle(checked);
        if (!checked) {
            $('#departure-vehicle-select-wrap').hide();
            departureChosenVehicles = [];
            renderChosenVehiclesList(departureChosenVehicles, 'departure-chosen-vehicles');
            $('#departure-vehicles-hidden').val('[]');
        }
    });

    // Transfer vehicles: multiple selection, zone-based search
    let arrivalChosenVehicles = [];
    let departureChosenVehicles = [];
    let arrivalVehiclesByZone = [];
    let departureVehiclesByZone = [];
    const fetchVehiclesByZonesUrl = '{{ route("fetch-vehicles-by-zones") }}';

    function renderChosenVehiclesList(list, containerId) {
        const el = $('#' + containerId);
        el.empty();
        list.forEach(function(v, idx) {
            el.append(`
                <div class="d-flex align-items-center justify-content-between border rounded px-2 py-1 mb-1 bg-white small">
                    <span>${escapeHtml(v.vehicle_name)}${v.vehicle_type ? ' (' + escapeHtml(v.vehicle_type) + ')' : ''}</span>
                    <button type="button" class="btn btn-link btn-sm p-0 text-danger transfer-vehicle-remove" data-list="${containerId}" data-idx="${idx}" title="Remove"><i class="ri-close-line"></i></button>
                </div>
            `);
        });
    }

    $(document).on('click', '.transfer-vehicle-remove', function() {
        const listName = $(this).data('list');
        const idx = parseInt($(this).data('idx'), 10);
        if (listName === 'arrival-chosen-vehicles') {
            arrivalChosenVehicles.splice(idx, 1);
            renderChosenVehiclesList(arrivalChosenVehicles, 'arrival-chosen-vehicles');
            $('#arrival-vehicles-hidden').val(JSON.stringify(arrivalChosenVehicles));
        } else {
            departureChosenVehicles.splice(idx, 1);
            renderChosenVehiclesList(departureChosenVehicles, 'departure-chosen-vehicles');
            $('#departure-vehicles-hidden').val(JSON.stringify(departureChosenVehicles));
        }
    });

    $('#arrival-search-vehicle-btn').on('click', function() {
        const portId = $('#arrival-pickup-port').val();
        const hotelId = $('#arrival-dropoff-hotel').val();
        const city = $('#city-select').val();
        if (!portId || !hotelId) { alert('Select pickup port and dropoff hotel first.'); return; }
        if (!city) { alert('Select city first.'); return; }
        const btn = $(this).prop('disabled', true);
        $.ajax({
            url: fetchVehiclesByZonesUrl,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                from_zone_id: portId,
                to_zone_id: hotelId,
                from_zone_type: 'Port',
                to_zone_type: 'Hotel',
                city: city,
                zone_status: 1
            },
            success: function(res) {
                arrivalVehiclesByZone = res.vehicles || [];
                const sel = $('#arrival-vehicle-select');
                sel.empty().append('<option value="">Select vehicle</option>');
                arrivalVehiclesByZone.forEach(function(v) {
                    const name = (v.vehicle_name || v.name) + (v.vehicle_type ? ' (' + v.vehicle_type + ')' : '');
                    sel.append(new Option(name, v.vehicle_id));
                });
                $('#arrival-vehicle-select-wrap').show();
            },
            error: function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'No vehicles found for this route (zone).';
                alert(msg);
            }
        }).always(function() { btn.prop('disabled', false); });
    });

    $('#departure-search-vehicle-btn').on('click', function() {
        const hotelId = $('#departure-pickup-hotel').val();
        const portId = $('#departure-dropoff-port').val();
        const city = $('#city-select').val();
        if (!hotelId || !portId) { alert('Select pickup hotel and dropoff port first.'); return; }
        if (!city) { alert('Select city first.'); return; }
        const btn = $(this).prop('disabled', true);
        $.ajax({
            url: fetchVehiclesByZonesUrl,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                from_zone_id: hotelId,
                to_zone_id: portId,
                from_zone_type: 'Hotel',
                to_zone_type: 'Port',
                city: city,
                zone_status: 1
            },
            success: function(res) {
                departureVehiclesByZone = res.vehicles || [];
                const sel = $('#departure-vehicle-select');
                sel.empty().append('<option value="">Select vehicle</option>');
                departureVehiclesByZone.forEach(function(v) {
                    const name = (v.vehicle_name || v.name) + (v.vehicle_type ? ' (' + v.vehicle_type + ')' : '');
                    sel.append(new Option(name, v.vehicle_id));
                });
                $('#departure-vehicle-select-wrap').show();
            },
            error: function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'No vehicles found for this route (zone).';
                alert(msg);
            }
        }).always(function() { btn.prop('disabled', false); });
    });

    $('#arrival-add-vehicle-btn').on('click', function() {
        const val = $('#arrival-vehicle-select').val();
        if (!val) return;
        const v = arrivalVehiclesByZone.find(function(x) { return x.vehicle_id == val; });
        if (!v) return;
        if (arrivalChosenVehicles.some(function(x) { return x.vehicle_id == val; })) return;
        arrivalChosenVehicles.push({ vehicle_id: v.vehicle_id, vehicle_name: v.vehicle_name || v.name, vehicle_type: v.vehicle_type });
        renderChosenVehiclesList(arrivalChosenVehicles, 'arrival-chosen-vehicles');
        $('#arrival-vehicles-hidden').val(JSON.stringify(arrivalChosenVehicles));
    });

    $('#departure-add-vehicle-btn').on('click', function() {
        const val = $('#departure-vehicle-select').val();
        if (!val) return;
        const v = departureVehiclesByZone.find(function(x) { return x.vehicle_id == val; });
        if (!v) return;
        if (departureChosenVehicles.some(function(x) { return x.vehicle_id == val; })) return;
        departureChosenVehicles.push({ vehicle_id: v.vehicle_id, vehicle_name: v.vehicle_name || v.name, vehicle_type: v.vehicle_type });
        renderChosenVehiclesList(departureChosenVehicles, 'departure-chosen-vehicles');
        $('#departure-vehicles-hidden').val(JSON.stringify(departureChosenVehicles));
    });

    // Form submit: build full JSON for selected_hotels, selected_attractions, selected_restaurants
    $('#package-definition-form').on('submit', function(e) {
        if (!$(this).find('#main_image')[0].files || !$(this).find('#main_image')[0].files.length) {
            $('#main-image-required-msg').removeClass('d-none');
            e.preventDefault();
            return false;
        }
        $('#main-image-required-msg').addClass('d-none');
        // Hotels: full data with id/name for API compatibility
        const selectedHotelsPayload = definitionHotels.map(function(h) {
            return {
                id: h.hotel_id,
                name: h.hotel_name,
                hotel_id: h.hotel_id,
                hotel_name: h.hotel_name,
                nights: h.nights,
                rooms: h.rooms,
                compulsory: !!h.compulsory,
                optional: !!h.optional,
                "add-on": !!h.addon,
                "add-on-price": h.addon_price != null && h.addon_price !== '' ? parseFloat(h.addon_price) : null
            };
        });
        // Attractions: full data including guide, transfer, pickup/dropoff, compulsory
        const selectedAttractionsPayload = definitionAttractions.map(function(a) {
            return {
                id: a.attraction_id,
                name: a.name,
                attraction_id: a.attraction_id,
                location: a.location || '',
                image: a.image || '',
                compulsory: !!a.compulsory,
                optional: !!a.optional,
                "add-on": !!a.addon,
                "add-on-price": a.addon_price != null && a.addon_price !== '' ? parseFloat(a.addon_price) : null,
                guide: a.guide || null,
                transfer: !!a.transfer,
                vehicle_id: a.vehicle_id || null,
                vehicle_name: a.vehicle_name || null,
                transfer_type: a.transfer_type || 'private',
                pickup_id: a.pickup_id || null,
                pickup_name: a.pickup_name || null,
                dropoff_id: a.dropoff_id || null,
                dropoff_name: a.dropoff_name || null
            };
        });
        // Restaurants: full data including transfer, pickup/dropoff, compulsory
        const selectedRestaurantsPayload = definitionRestaurants.map(function(r) {
            return {
                id: r.restaurant_id,
                name: r.restaurant_name,
                restaurant_id: r.restaurant_id,
                restaurant_name: r.restaurant_name,
                compulsory: !!r.compulsory,
                optional: !!r.optional,
                "add-on": !!r.addon,
                "add-on-price": r.addon_price != null && r.addon_price !== '' ? parseFloat(r.addon_price) : null,
                selected_meals: r.selected_meals || [],
                transfer: !!r.transfer,
                vehicle_id: r.vehicle_id || null,
                vehicle_name: r.vehicle_name || null,
                transfer_type: r.transfer_type || 'private',
                pickup_id: r.pickup_id || null,
                pickup_name: r.pickup_name || null,
                dropoff_id: r.dropoff_id || null,
                dropoff_name: r.dropoff_name || null
            };
        });
        $('#definition-hotels-input').val(JSON.stringify(selectedHotelsPayload));
        $('#definition-attractions-input').val(JSON.stringify(selectedAttractionsPayload));
        $('#definition-restaurants-input').val(JSON.stringify(selectedRestaurantsPayload));
        $('#arrival-pickup-hidden').val($('#arrival-pickup-def').is(':checked') ? 1 : 0);
        $('#departure-service-hidden').val($('#departure-service-def').is(':checked') ? 1 : 0);
        $('#arrival-pickup-port-hidden').val($('#arrival-pickup-port').val() || '');
        $('#arrival-dropoff-hotel-hidden').val($('#arrival-dropoff-hotel').val() || '');
        $('#arrival-vehicles-hidden').val(JSON.stringify(arrivalChosenVehicles || []));
        $('#departure-pickup-hotel-hidden').val($('#departure-pickup-hotel').val() || '');
        $('#departure-dropoff-port-hidden').val($('#departure-dropoff-port').val() || '');
        $('#departure-vehicles-hidden').val(JSON.stringify(departureChosenVehicles || []));
        $('#local-transfers-hidden').val(JSON.stringify(localTransfersList || []));
        const selectedGuidesPayload = definitionGuides.map(function(g) {
            return {
                id: g.id,
                name: g.name,
                languages: g.languages || [],
                contact_no: g.contact_no || '',
                compulsory: !!g.compulsory,
                optional: !!g.optional,
                "add-on": !!g.addon,
                "add-on-price": g.addon_price != null && g.addon_price !== '' ? parseFloat(g.addon_price) : null,
                duration_key: g.duration_key || 'hourly',
                duration_label: g.duration_label || ''
            };
        });
        $('#definition-independent-guide-input').val(JSON.stringify(selectedGuidesPayload));

        // Ensure price_data is set even if the summary card isn't visible
        if (typeof renderOptionalServicesSummary === 'function') renderOptionalServicesSummary();
    });

    // Main image preview
    $('#main-image-drop-area').on('click', function() {
        $('#main_image')[0].click();
    });
    $('#main_image').on('change', function() {
        $('#main-image-required-msg').addClass('d-none');
        const f = this.files[0];
        if (f && f.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                $('#main-image-preview-container').html('<img src="' + ev.target.result + '" style="max-height:100px;border-radius:8px;">');
            };
            reader.readAsDataURL(f);
        }
    });
    // Gallery (simple)
    $('#gallery-drop-area').on('click', function() { $('#gallery_images').click(); });
});
</script>
@endsection

@section('styles')
<style>
.form-control, .form-select { padding: 0.5rem 1rem; border-radius: 0.375rem; min-height: 42px; }
.card { border: none; box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12); border-radius: 0.5rem; }
.card-header { border-bottom: 1px solid #d9dee3; padding: 1rem 1.5rem; }
.select2-container--default .select2-selection--multiple { min-height: 42px; border-radius: 0.375rem; }
/* Two per row: same width and height for every input/select in the row */
.two-col-row .col-md-6 .form-control,
.two-col-row .col-md-6 .form-select { width: 100%; min-height: 42px; }
.two-col-row .col-md-6 .select2-container { width: 100% !important; }
.two-col-row .col-md-6 .select2-container--default .select2-selection--single { min-height: 42px; border-radius: 0.375rem; }
.two-col-row .col-md-6 .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 2.25; }
.bg-primary-subtle { background-color: rgba(105, 108, 255, 0.1) !important; }
.bg-success-subtle { background-color: rgba(32, 201, 151, 0.1) !important; }
.bg-info-subtle { background-color: rgba(13, 202, 240, 0.1) !important; }
.bg-warning-subtle { background-color: rgba(253, 126, 20, 0.1) !important; }
.hotel-attraction-box { min-height: 280px; }
.hotel-attraction-box .form-select-sm, .hotel-attraction-box .form-control-sm { min-height: 36px; }
</style>
@endsection
