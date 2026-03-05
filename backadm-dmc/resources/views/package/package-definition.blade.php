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
                                   name="title" value="{{ old('title') }}" required placeholder="e.g., Singapore Explorer">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Country/Destination <span class="text-danger">*</span></label>
                            <select class="form-select @error('destination') is-invalid @enderror" id="country-select" name="destination" required>
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->name }}" {{ old('destination') == $country->name ? 'selected' : '' }}>{{ $country->name }}</option>
                                @endforeach
                            </select>
                            @error('destination')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <select class="form-select @error('city') is-invalid @enderror" id="city-select" name="city" required disabled>
                                <option value="">Select Country First</option>
                            </select>
                            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Duration (Days) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('duration_days') is-invalid @enderror"
                                   name="duration_days" value="{{ old('duration_days') }}" min="1" required>
                            @error('duration_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3" placeholder="Brief description...">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                                <input type="number" class="form-control" name="price_adult" value="{{ old('price_adult') }}" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Senior Citizen Price (SGD)</label>
                            <div class="input-group">
                                <span class="input-group-text">SGD</span>
                                <input type="number" class="form-control" name="price_senior" value="{{ old('price_senior') }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Child Price (SGD)</label>
                            <div class="input-group">
                                <span class="input-group-text">SGD</span>
                                <input type="number" class="form-control" name="price_child" value="{{ old('price_child') }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Child Max Age</label>
                            <input type="number" class="form-control" name="child_max_age" value="{{ old('child_max_age') }}" min="1" id="child-max-age-input">
                            <small class="text-muted">Child below this age will be free of charge</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Availability -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-calendar-line me-2 text-primary"></i>Package Availability</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="start_date" value="{{ old('start_date') }}" required min="{{ date('Y-m-d') }}" id="start-date-input">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Expiry Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="expiry_date" value="{{ old('expiry_date') }}" required min="{{ date('Y-m-d') }}" id="expiry-date-input">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hotel: select hotel → rooms (AJAX) → select rooms & qty → nights → Add → chosen hotels preview -->
            <div class="card mb-4">
                <div class="card-header bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0"><i class="ri-hotel-line me-2 text-primary"></i>Hotel Accommodations</h5>
                        <p class="text-muted small mb-0 mt-1">Manage hotel bookings and room configurations</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Select Hotel</label>
                            <select class="form-select" id="definition-hotel-select" name="definition_hotel_id">
                                <option value="">Select City First</option>
                            </select>
                        </div>
                        <div class="col-md-12" id="definition-rooms-wrapper" style="display: none;">
                            <div class="card shadow-sm border-primary border-opacity-25">
                                <div class="card-header bg-primary-subtle py-2">
                                    <h6 class="mb-0 fw-semibold"><i class="ri-hotel-bed-line me-2"></i>Room Types</h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-2">Select room type(s) and quantity, then set nights and click <strong>Add</strong>.</p>
                                    <div id="definition-rooms-list" class="definition-rooms-list row"></div>
                                    <div class="mt-3 d-flex align-items-end gap-3 flex-wrap">
                                        <div>
                                            <label class="form-label">Number of Nights <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="definition-nights" min="1" value="1" style="max-width: 100px;">
                                        </div>
                                        <div class="mb-0">
                                            <button type="button" class="btn btn-primary" id="definition-hotel-add-btn">
                                                <i class="ri-add-line me-1"></i> Add
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chosen hotels preview -->
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="fw-semibold mb-2"><i class="ri-list-check me-2 text-success"></i>Chosen Hotels</h6>
                        <div id="definition-chosen-hotels" class="mb-0">
                            <div class="alert alert-info py-3 mb-0 d-flex align-items-center">
                                <i class="ri-information-line me-2 fs-5"></i>
                                <span>No hotels selected yet. Choose your hotels above and click <strong>Add</strong>.</span>
                            </div>
                        </div>
                        <div id="definition-chosen-hotels-list" class="mt-2" style="display: none;"></div>
                        <p class="text-muted small mt-2 mb-0">
                            <i class="ri-hotel-line me-1"></i> Total Hotels: <span id="definition-total-hotels-count">0</span>
                        </p>
                    </div>

                    <input type="hidden" name="selected_hotels" id="definition-hotels-input" value="[]">
                </div>
            </div>

            <!-- Attractions: select → configure guide/transfer → Add → preview strip (like hotel) -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-map-pin-line me-2 text-success"></i>Attractions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Select Attraction</label>
                            <select class="form-select" id="definition-attraction-select">
                                <option value="">Select City First</option>
                            </select>
                        </div>
                    </div>
                    <div id="definition-attraction-config" class="card shadow-sm border-success border-opacity-25 mb-3" style="display: none;">
                        <div class="card-header bg-success-subtle py-2">
                            <h6 class="mb-0 fw-semibold">Configure guide & transfer, then click Add</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small">Guide (optional)</label>
                                    <select class="form-select form-select-sm" id="definition-attraction-config-guide">
                                        <option value="">No guide</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Transfer</label>
                                    <div class="form-check form-check-inline mt-2">
                                        <input class="form-check-input" type="checkbox" id="definition-attraction-config-transfer">
                                        <label class="form-check-label">Include</label>
                                    </div>
                                </div>
                                <div class="col-12" id="definition-attraction-config-vehicle-wrap" style="display: none;">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label small">Vehicle</label>
                                            <select class="form-select form-select-sm" id="definition-attraction-config-vehicle">
                                                <option value="">Select vehicle</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Pickup location</label>
                                            <select class="form-select form-select-sm" id="definition-attraction-config-pickup">
                                                <option value="">Add hotels first</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Dropoff location</label>
                                            <select class="form-select form-select-sm" id="definition-attraction-config-dropoff">
                                                <option value="">—</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-check form-check-inline mt-2">
                                        <input class="form-check-input" type="radio" name="definition_attr_transfer_type" value="private" id="definition-attr-transfer-private"> <label class="form-check-label" for="definition-attr-transfer-private">Private</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="definition_attr_transfer_type" value="shared" id="definition-attr-transfer-shared"> <label class="form-check-label" for="definition-attr-transfer-shared">Shared</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-primary btn-sm" id="definition-attraction-add-btn">
                                    <i class="ri-add-line me-1"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="border-top pt-3">
                        <h6 class="fw-semibold mb-2">Chosen Attractions</h6>
                        <div id="definition-attractions-empty" class="alert alert-info py-3 mb-0 d-flex align-items-center">
                            <i class="ri-information-line me-2 fs-5"></i>
                            <span>No attractions added yet. Select an attraction, set guide/transfer above, then click <strong>Add</strong>.</span>
                        </div>
                        <div id="definition-attractions-list" class="mt-2" style="display: none;"></div>
                    </div>
                    <input type="hidden" name="selected_attractions" id="definition-attractions-input" value="[]">
                </div>
            </div>

            <!-- Restaurants: select → configure transfer → Add → preview strip (like hotel) -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-restaurant-line me-2 text-warning"></i>Restaurants</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Select Restaurant</label>
                            <select class="form-select" id="definition-restaurant-select">
                                <option value="">Select City First</option>
                            </select>
                        </div>
                    </div>
                    <div id="definition-restaurant-config" class="card shadow-sm border-warning border-opacity-25 mb-3" style="display: none;">
                        <div class="card-header bg-warning-subtle py-2">
                            <h6 class="mb-0 fw-semibold">Configure transfer, then click Add</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="definition-restaurant-config-transfer">
                                        <label class="form-check-label">Include transfer</label>
                                    </div>
                                </div>
                                <div class="col-12" id="definition-restaurant-config-vehicle-wrap" style="display: none;">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label small">Vehicle</label>
                                            <select class="form-select form-select-sm" id="definition-restaurant-config-vehicle">
                                                <option value="">Select vehicle</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Pickup location</label>
                                            <select class="form-select form-select-sm" id="definition-restaurant-config-pickup">
                                                <option value="">Add hotels first</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Dropoff location</label>
                                            <select class="form-select form-select-sm" id="definition-restaurant-config-dropoff">
                                                <option value="">—</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-check form-check-inline mt-2">
                                        <input class="form-check-input" type="radio" name="definition_rest_transfer_type" value="private" id="definition-rest-transfer-private"> <label class="form-check-label" for="definition-rest-transfer-private">Private</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="definition_rest_transfer_type" value="shared" id="definition-rest-transfer-shared"> <label class="form-check-label" for="definition-rest-transfer-shared">Shared</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-primary btn-sm" id="definition-restaurant-add-btn">
                                    <i class="ri-add-line me-1"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="border-top pt-3">
                        <h6 class="fw-semibold mb-2">Chosen Restaurants</h6>
                        <div id="definition-restaurants-empty" class="alert alert-info py-3 mb-0 d-flex align-items-center">
                            <i class="ri-information-line me-2 fs-5"></i>
                            <span>No restaurants added yet. Select a restaurant, set transfer above, then click <strong>Add</strong>.</span>
                        </div>
                        <div id="definition-restaurants-list" class="mt-2" style="display: none;"></div>
                    </div>
                    <input type="hidden" name="selected_restaurants" id="definition-restaurants-input" value="[]">
                </div>
            </div>

            <!-- Independent Guide -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-user-voice-line me-2 text-info"></i>Independent Guide (Optional)</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <label class="form-label">Select Guide</label>
                            <select class="form-select" id="definition-guide-select" name="definition_guide_id">
                                <option value="">Select City First / No guide</option>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="definition_independent_guide" id="definition-independent-guide-input" value="">
                </div>
            </div>

            <!-- Arrival Pickup & Departure Service -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-flight-land-line me-2 text-primary"></i>Transfers</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-info border-opacity-25">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-info-subtle text-info p-2 rounded-circle me-3"><i class="ri-flight-land-line"></i></span>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">Arrival Pickup</h6>
                                            <small class="text-muted">Airport/port pickup</small>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="arrival-pickup-def" value="1">
                                            <label class="form-check-label" for="arrival-pickup-def">Include</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-warning border-opacity-25">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-warning-subtle text-warning p-2 rounded-circle me-3"><i class="ri-flight-takeoff-line"></i></span>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">Departure Service</h6>
                                            <small class="text-muted">Airport/port dropoff</small>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="departure-service-def" value="1">
                                            <label class="form-check-label" for="departure-service-def">Include</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="arrival_pickup" id="arrival-pickup-hidden" value="0">
                    <input type="hidden" name="departure_service" id="departure-service-hidden" value="0">
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
                            <textarea class="form-control" name="inclusions" rows="5" placeholder="What's included...">{{ old('inclusions') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Exclusions</label>
                            <textarea class="form-control" name="exclusions" rows="5" placeholder="What's not included...">{{ old('exclusions') }}</textarea>
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
                    <textarea class="form-control" name="terms_conditions" rows="4" placeholder="Terms and conditions...">{{ old('terms_conditions') }}</textarea>
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

            <!-- Status -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-toggle-line me-2 text-primary"></i>Status</h5>
                </div>
                <div class="card-body">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select" name="status" required style="max-width: 200px;">
                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Draft</option>
                        <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                    </select>
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
        if (!country) return;
        citySelect.prop('disabled', false);
        $.get(baseUrl + '/cities-by-country/' + encodeURIComponent(country), function(response) {
            citySelect.empty().append('<option value="">Select City</option>');
            response.forEach(function(c) { citySelect.append(new Option(c.name, c.name)); });
        });
    });

    let definitionHotels = [];
    let definitionAttractions = [];
    let definitionRestaurants = [];
    let vehiclesByCity = [];
    let guidesByCity = [];

    // City → Hotel, Attractions, Restaurants, Guides, Vehicles
    $('#city-select').on('change', function() {
        const city = $(this).val();
        if (!city) return;

        $.get(baseUrl + '/hotel-city/' + encodeURIComponent(city), function(response) {
            const sel = $('#definition-hotel-select');
            sel.empty().append('<option value="">Select Hotel</option>');
            response.forEach(function(h) {
                sel.append(new Option(h.name, h.hotel_unique_id));
            });
        });

        $.get(baseUrl + '/attractions/' + encodeURIComponent(city), function(response) {
            const sel = $('#definition-attraction-select');
            sel.empty().append('<option value="">Select Attraction</option>');
            response.forEach(function(a) {
                const opt = new Option(a.name, a.attraction_id);
                $(opt).data('attraction-data', { attraction_id: a.attraction_id, name: a.name, location: a.location, image: a.master_image || '' });
                sel.append(opt);
            });
        });

        $.get(baseUrl + '/restaurants/' + encodeURIComponent(city), function(response) {
            const sel = $('#definition-restaurant-select');
            sel.empty().append('<option value="">Select Restaurant</option>');
            const list = response.restaurants || response;
            list.forEach(function(r) {
                sel.append(new Option(r.name, r.restaurant_id));
            });
        });

        $.get(baseUrl + '/guides/' + encodeURIComponent(city), function(response) {
            guidesByCity = response;
            const sel = $('#definition-guide-select');
            sel.empty().append('<option value="">No guide</option>');
            response.forEach(function(g) {
                const opt = new Option(g.name + (g.languages && g.languages.length ? ' (' + g.languages.join(', ') + ')' : ''), g.guide_id);
                $(opt).data('guide-data', g);
                sel.append(opt);
            });
        });

        $.get(baseUrl + '/get-transport/' + encodeURIComponent(city), function(response) {
            vehiclesByCity = Array.isArray(response) ? response : [];
        });
    });

    // Hotel → Room types (AJAX)
    $('#definition-hotel-select').on('change', function() {
        const hotelId = $(this).val();
        const wrapper = $('#definition-rooms-wrapper');
        const list = $('#definition-rooms-list');
        list.empty();
        if (!hotelId) {
            wrapper.hide();
            return;
        }
        $.get(baseUrl + '/room-types-by-hotel/' + encodeURIComponent(hotelId), function(res) {
            const roomTypes = res.room_types || [];
            if (roomTypes.length === 0) {
                list.html('<p class="text-muted">No rooms found for this hotel.</p>');
            } else {
                roomTypes.forEach(function(rt) {
                    list.append(`
                        <div class="col-md-3 col-sm-6 mb-1">
                            <div class="border rounded px-2 py-1 d-flex align-items-center justify-content-between small">
                                <div class="form-check m-0">
                                    <input class="form-check-input definition-room-check"
                                        type="checkbox"
                                        id="rt-${rt.id}"
                                        data-room-type-id="${rt.id}"
                                        data-room-name="${escapeHtml(rt.name)}">

                                    <label class="form-check-label ms-1" for="rt-${rt.id}">
                                        ${escapeHtml(rt.name)}
                                    </label>
                                </div>

                                <input type="number"
                                    class="form-control form-control-sm definition-room-qty"
                                    style="width: 70px;"
                                    min="1"
                                    placeholder="Qty"
                                    data-room-type-id="${rt.id}"
                                    value="1"
                                    disabled>
                            </div>
                        </div>
                    `);
                });
                $('.definition-room-check').on('change', function() {
                    const id = $(this).data('room-type-id');
                    $(`.definition-room-qty[data-room-type-id="${id}"]`).prop('disabled', !$(this).is(':checked')).val($(this).is(':checked') ? 1 : '');
                });
            }
            wrapper.show();
        });
    });

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Add hotel entry to chosen list
    $('#definition-hotel-add-btn').on('click', function() {
        const hotelId = $('#definition-hotel-select').val();
        const hotelName = $('#definition-hotel-select').find('option:selected').text();
        const nights = parseInt($('#definition-nights').val()) || 1;
        if (!hotelId) {
            alert('Please select a hotel.');
            return;
        }
        const rooms = [];
        $('.definition-room-check:checked').each(function() {
            const id = $(this).data('room-type-id');
            const name = $(this).data('room-name');
            const qty = parseInt($(`.definition-room-qty[data-room-type-id="${id}"]`).val()) || 1;
            if (qty > 0) rooms.push({ room_type_id: id, room_type_name: name, quantity: qty });
        });
        if (rooms.length === 0) {
            alert('Please select at least one room type and set quantity.');
            return;
        }
        if (nights < 1) {
            alert('Please set number of nights to at least 1.');
            return;
        }
        definitionHotels.push({ hotel_id: hotelId, hotel_name: hotelName, nights, rooms, compulsory: false });
        updateDefinitionHotelsInput();
        renderChosenHotels();
        resetDefinitionHotelForm();
    });

    function resetDefinitionHotelForm() {
        $('#definition-hotel-select').val('').trigger('change.select2');
        $('#definition-rooms-wrapper').hide();
        $('#definition-rooms-list').empty();
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
            listEl.append(`
                <div class="card mb-2 border shadow-sm chosen-hotel-card" data-idx="${idx}">
                    <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 flex-grow-1">
                            <span class="badge bg-primary-subtle text-primary rounded-circle p-2"><i class="ri-hotel-line"></i></span>
                            <div>
                                <strong class="d-block">${escapeHtml(entry.hotel_name)}</strong>
                                <small class="text-muted">${entry.nights} night(s) · ${escapeHtml(roomsText)}</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input chosen-hotel-compulsory" type="checkbox" id="hotel-comp-${idx}" data-idx="${idx}" ${isCompulsory ? 'checked' : ''}>
                                <label class="form-check-label small" for="hotel-comp-${idx}">Compulsory</label>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-chosen-hotel" data-idx="${idx}" title="Remove">
                                <i class="ri-delete-bin-line"></i>
                            </button>
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
        $('.chosen-hotel-compulsory').on('change', function() {
            const idx = parseInt($(this).data('idx'));
            if (definitionHotels[idx]) definitionHotels[idx].compulsory = $(this).is(':checked');
            updateDefinitionHotelsInput();
        });
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
        guideSel.empty().append('<option value="">No guide</option>');
        guidesByCity.forEach(function(g) {
            guideSel.append(new Option(g.name, g.guide_id));
        });
        const vehicleSel = $('#definition-attraction-config-vehicle');
        vehicleSel.empty().append('<option value="">Select vehicle</option>');
        vehiclesByCity.forEach(function(v) {
            vehicleSel.append(new Option(v.name + (v.vehicle_type ? ' (' + v.vehicle_type + ')' : ''), v.vehicle_id));
        });
        $('#definition-attraction-config-transfer').prop('checked', false);
        $('#definition-attraction-config-vehicle-wrap').hide();
        $('#definition-attr-transfer-private').prop('checked', true);
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
        const guideId = $('#definition-attraction-config-guide').val();
        const g = guidesByCity.find(x => x.guide_id == guideId);
        const transfer = $('#definition-attraction-config-transfer').is(':checked');
        const vehicleId = $('#definition-attraction-config-vehicle').val();
        const v = vehiclesByCity.find(x => x.vehicle_id == vehicleId);
        const transferType = $('input[name="definition_attr_transfer_type"]:checked').val() || 'private';
        const pickupId = $('#definition-attraction-config-pickup').val();
        const pickupName = $('#definition-attraction-config-pickup').find('option:selected').text();
        const dropoffVal = $('#definition-attraction-config-dropoff').val();
        const dropoffName = $('#definition-attraction-config-dropoff').find('option:selected').text();
        definitionAttractions.push({
            attraction_id: data.attraction_id,
            name: data.name,
            location: data.location,
            image: data.image || '',
            compulsory: false,
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
            container.append(`
                <div class="card mb-2 border shadow-sm chosen-hotel-card" data-idx="${idx}">
                    <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 flex-grow-1">
                            <span class="badge bg-success-subtle text-success rounded-circle p-2"><i class="ri-map-pin-line"></i></span>
                            <div>
                                <strong class="d-block">${escapeHtml(a.name)}</strong>
                                <small class="text-muted">${summaryHtml}</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input def-attraction-compulsory" type="checkbox" id="attr-comp-${idx}" data-idx="${idx}" ${isCompulsory ? 'checked' : ''}>
                                <label class="form-check-label small" for="attr-comp-${idx}">Compulsory</label>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-def-attraction" data-idx="${idx}" title="Remove"><i class="ri-delete-bin-line"></i></button>
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
        $('.def-attraction-compulsory').on('change', function() {
            const idx = parseInt($(this).data('idx'));
            if (definitionAttractions[idx]) definitionAttractions[idx].compulsory = $(this).is(':checked');
            updateDefinitionAttractionsInput();
        });
    }

    function updateDefinitionAttractionsInput() {
        $('#definition-attractions-input').val(JSON.stringify(definitionAttractions));
    }

    // Restaurant select: show config panel and populate vehicle
    $('#definition-restaurant-select').on('change', function() {
        const val = $(this).val();
        const configEl = $('#definition-restaurant-config');
        if (!val) {
            configEl.hide();
            return;
        }
        configEl.show();
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

    // Restaurants: Add = current selection + config; preview = strip like hotel
    $('#definition-restaurant-add-btn').on('click', function() {
        const id = $('#definition-restaurant-select').val();
        const name = $('#definition-restaurant-select').find('option:selected').text();
        if (!id) {
            alert('Please select a restaurant.');
            return;
        }
        const transfer = $('#definition-restaurant-config-transfer').is(':checked');
        const vehicleId = $('#definition-restaurant-config-vehicle').val();
        const v = vehiclesByCity.find(x => x.vehicle_id == vehicleId);
        const transferType = $('input[name="definition_rest_transfer_type"]:checked').val() || 'private';
        const pickupId = $('#definition-restaurant-config-pickup').val();
        const pickupName = $('#definition-restaurant-config-pickup').find('option:selected').text();
        const dropoffVal = $('#definition-restaurant-config-dropoff').val();
        const dropoffName = $('#definition-restaurant-config-dropoff').find('option:selected').text();
        definitionRestaurants.push({
            restaurant_id: id,
            restaurant_name: name,
            compulsory: false,
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
            let summaryHtml = '—';
            if (r.transfer) {
                const parts = [];
                if (r.vehicle_name) parts.push('<i class="ri-car-line me-1" title="Vehicle"></i>' + escapeHtml(r.vehicle_name));
                parts.push('Transfer: ' + (r.transfer_type === 'shared' ? 'Shared' : 'Private'));
                const pickupName = r.pickup_name || r.pickup_hotel_name;
                if (pickupName && r.dropoff_name) parts.push(escapeHtml(pickupName) + ' → ' + escapeHtml(r.dropoff_name));
                else if (pickupName) parts.push(escapeHtml(pickupName) + ' → —');
                else if (r.dropoff_name) parts.push('— → ' + escapeHtml(r.dropoff_name));
                summaryHtml = parts.join(' <span class="text-muted">·</span> ');
            }
            const isCompulsory = r.compulsory === true;
            container.append(`
                <div class="card mb-2 border shadow-sm chosen-hotel-card" data-idx="${idx}">
                    <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 flex-grow-1">
                            <span class="badge bg-warning-subtle text-warning rounded-circle p-2"><i class="ri-restaurant-line"></i></span>
                            <div>
                                <strong class="d-block">${escapeHtml(r.restaurant_name)}</strong>
                                <small class="text-muted">${summaryHtml}</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input def-restaurant-compulsory" type="checkbox" id="rest-comp-${idx}" data-idx="${idx}" ${isCompulsory ? 'checked' : ''}>
                                <label class="form-check-label small" for="rest-comp-${idx}">Compulsory</label>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-def-restaurant" data-idx="${idx}" title="Remove"><i class="ri-delete-bin-line"></i></button>
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
        $('.def-restaurant-compulsory').on('change', function() {
            const idx = parseInt($(this).data('idx'));
            if (definitionRestaurants[idx]) definitionRestaurants[idx].compulsory = $(this).is(':checked');
            updateDefinitionRestaurantsInput();
        });
    }

    function updateDefinitionRestaurantsInput() {
        $('#definition-restaurants-input').val(JSON.stringify(definitionRestaurants));
    }

    // Independent guide
    $('#definition-guide-select').on('change', function() {
        const opt = $(this).find('option:selected');
        const data = opt.data('guide-data');
        const val = data ? JSON.stringify({ id: data.guide_id, name: data.name, languages: data.languages, contact_no: data.contact_no }) : '';
        $('#definition-independent-guide-input').val(val);
    });

    // Arrival / Departure: sync hidden inputs
    $('#arrival-pickup-def').on('change', function() {
        $('#arrival-pickup-hidden').val($(this).is(':checked') ? 1 : 0);
    });
    $('#departure-service-def').on('change', function() {
        $('#departure-service-hidden').val($(this).is(':checked') ? 1 : 0);
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
                compulsory: !!h.compulsory
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
        const guideOpt = $('#definition-guide-select').find('option:selected');
        const guideData = guideOpt.data('guide-data');
        $('#definition-independent-guide-input').val(guideData ? JSON.stringify({ id: guideData.guide_id, name: guideData.name, languages: guideData.languages, contact_no: guideData.contact_no }) : '');
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
.form-control, .form-select { padding: 0.5rem 1rem; border-radius: 0.375rem; }
.card { border: none; box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12); border-radius: 0.5rem; }
.card-header { border-bottom: 1px solid #d9dee3; padding: 1rem 1.5rem; }
.select2-container--default .select2-selection--multiple { min-height: 38px; border-radius: 0.375rem; }
.bg-primary-subtle { background-color: rgba(105, 108, 255, 0.1) !important; }
.bg-success-subtle { background-color: rgba(32, 201, 151, 0.1) !important; }
.bg-info-subtle { background-color: rgba(13, 202, 240, 0.1) !important; }
.bg-warning-subtle { background-color: rgba(253, 126, 20, 0.1) !important; }
</style>
@endsection
