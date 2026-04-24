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
                                            <div class="flex-grow-1" style="min-width: 140px;">
                                                <label class="form-label small mb-0">Bed Type</label>
                                                <select class="form-select form-select-sm" id="definition-bed-type-select">
                                                    <option value="">Select bed type</option>
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
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small mb-1">Select Attraction</label>
                                        <select class="form-select form-select-sm w-100" id="definition-attraction-select">
                                            <option value="">Select City First</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small mb-1">Select Ticket</label>
                                        <select class="form-select form-select-sm w-100" id="definition-attraction-ticket-select">
                                            <option value="">Select attraction first</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small mb-1">Adult Price</label>
                                        <input type="text" class="form-control form-control-sm w-100" id="definition-attraction-ticket-adult-price" value="—" readonly>
                                    </div>
                                </div>
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
                                                <div class="row g-2">
                                                    <div class="col-12 col-md-6">
                                                        <label class="form-label small mb-0">Select guide</label>
                                                        <select class="form-select form-select-sm" id="definition-attraction-config-guide">
                                                            <option value="">Select guide</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12 col-md-3">
                                                        <label class="form-label small mb-0">Hour</label>
                                                        <select class="form-select form-select-sm" id="definition-attraction-config-guide-hour">
                                                            <option value="">Select hour</option>
                                                            <option value="hourly">1 Hour</option>
                                                            <option value="two_hour">2 Hours</option>
                                                            <option value="four_hour">4 Hours</option>
                                                            <option value="six_hour">6 Hours</option>
                                                            <option value="eight_hour">8 Hours</option>
                                                            <option value="ten_hour">10 Hours</option>
                                                            <option value="twelve_hour">12 Hours</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12 col-md-3">
                                                        <label class="form-label small mb-0">Guide Price</label>
                                                        <input type="text" class="form-control form-control-sm" id="definition-attraction-config-guide-price" value="—" readonly>
                                                    </div>
                                                </div>
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
                                                    <div class="col-md-6">
                                                        <label class="form-label small mb-0">Transfer Price</label>
                                                        <input type="number" class="form-control form-control-sm" id="definition-attraction-config-transfer-price" min="0" step="0.01" value="0">
                                                        <small class="text-muted">Auto-fetched from zone mapping (editable).</small>
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
                    <h5 class="mb-0"><i class="ri-restaurant-line me-2 text-warning"></i><i class="ri-user-voice-line me-2 text-info"></i>Restaurants</h5>
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
                                        <h6 class="small fw-semibold mb-1">Meal</h6>
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-7">
                                                <label class="form-label small mb-0">Type</label>
                                                <select class="form-select form-select-sm" id="definition-restaurant-meal-type-select">
                                                    <option value="">Select meal type</option>
                                                </select>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label small mb-0">Adult Price</label>
                                                <input type="text" class="form-control form-control-sm" id="definition-restaurant-meal-adult-price" value="—" readonly>
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
                                                    <div class="col-md-6">
                                                        <label class="form-label small mb-0">Transfer Price</label>
                                                        <input type="number" class="form-control form-control-sm" id="definition-restaurant-config-transfer-price" min="0" step="0.01" value="0">
                                                        <small class="text-muted">Auto-fetched from zone mapping (editable).</small>
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
                        <!-- <div class="col-md-6">
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
                        </div> -->
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
                                            <div class="row g-2 mb-1">
                                                <div class="col-md-7">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="arrival_transfer_type" id="arrival-transfer-private" value="private" checked>
                                                        <label class="form-check-label small" for="arrival-transfer-private">Private</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="arrival_transfer_type" id="arrival-transfer-shared" value="shared">
                                                        <label class="form-check-label small" for="arrival-transfer-shared">Shared</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label small mb-0">Price</label>
                                                    <input type="number" class="form-control form-control-sm" id="arrival-transfer-price-input" min="0" step="0.01" value="0">
                                                </div>
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
                                            <div class="row g-2 mb-1">
                                                <div class="col-md-7">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="departure_transfer_type" id="departure-transfer-private" value="private" checked>
                                                        <label class="form-check-label small" for="departure-transfer-private">Private</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="departure_transfer_type" id="departure-transfer-shared" value="shared">
                                                        <label class="form-check-label small" for="departure-transfer-shared">Shared</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label small mb-0">Price</label>
                                                    <input type="number" class="form-control form-control-sm" id="departure-transfer-price-input" min="0" step="0.01" value="0">
                                                </div>
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

            <div class="card mb-4">
                <div class="card-header bg-light py-2">
                    <h5 class="mb-0"><i class="ri-money-dollar-circle-line me-2 text-success"></i>Price & Markup</h5>
                </div>
                <div class="card-body py-3">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Total Price</label>
                            <input type="number" class="form-control" id="definition-total-price" name="total_price" readonly value="0" step="0.01" min="0">
                            <small class="text-muted">Sum of hotels, attractions, restaurants, arrival and departure.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Markup Type</label>
                            <select class="form-select" id="definition-markup-type" name="markup_type">
                                <option value="">Select type</option>
                                <option value="percentage">Percentage</option>
                                <option value="flat">Flat</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Markup Amount</label>
                            <input type="number" class="form-control" id="definition-markup-amount" name="markup_amount" value="0" step="0.01" min="0">
                            <small class="text-muted">For percentage, enter % value. For flat, enter amount.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transport: Local Transfer (pickup & dropoff = hotels, attractions, restaurants, ports; dropoff excludes selected pickup) -->
            <!-- <div class="card mb-5">
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
            </div> -->

            <input type="hidden" name="price_data" id="price-data-hidden" value="[]">
            <div class="card mb-4" id="optional-services-summary-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ri-price-tag-3-line me-2 text-primary"></i>Optional &amp; Add-On Services – Price Summary</h5>
                </div>
                <div class="card-body py-3">
                    <p class="text-muted small mb-2">Listed prices come from services you marked as <strong>Optional</strong> or <strong>Add-On</strong> below.</p>
                    <div id="optional-services-summary-empty" class="alert alert-secondary py-2 small mb-0"><i class="ri-information-line me-1"></i>No optional or add-on priced lines yet. Mark a service as Optional or Add-On to include it here.</div>
                    <div id="optional-services-summary-table-wrap" class="table-responsive mt-2" style="display: none;">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Service</th>
                                    <th>Type</th>
                                    <th class="text-end">Price</th>
                                </tr>
                            </thead>
                            <tbody id="optional-services-summary-tbody"></tbody>
                            <tfoot>
                                <tr class="fw-semibold">
                                    <td colspan="2">Total</td>
                                    <td class="text-end" id="optional-services-total">—</td>
                                </tr>
                            </tfoot>
                        </table>
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
    const fetchAttractionTransferPricingUrl = '{{ route("fetch-attraction-transfer-pricing") }}';
    const fetchRestaurantTransferPricingUrl = '{{ route("fetch-restaurant-transfer-pricing") }}';

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
        restaurantMealsByRestaurant = {};
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
    let restaurantMealsByRestaurant = {};
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
            restaurantMealsByRestaurant = {};
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
    let definitionBedsByRoom = [];

    // Hotel → Room types (AJAX): populate Room Type select
    $('#definition-hotel-select').on('change', function() {
        const hotelId = $(this).val();
        const wrapper = $('#definition-rooms-wrapper');
        const roomTypeSelect = $('#definition-room-type-select');
        const bedTypeSelect = $('#definition-bed-type-select');
        roomTypeSelect.empty().append('<option value="">Select room type</option>');
        bedTypeSelect.empty().append('<option value="">Select bed type</option>');
        definitionPendingRooms = [];
        renderDefinitionPendingRooms();
        if (!hotelId) {
            wrapper.hide();
            definitionRoomTypesByHotel = [];
            definitionBedsByRoom = [];
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

    // Room type -> Bed types (AJAX): populate Bed Type select
    $('#definition-room-type-select').on('change', function() {
        const roomId = $(this).val();
        const bedTypeSelect = $('#definition-bed-type-select');
        bedTypeSelect.empty().append('<option value="">Select bed type</option>');
        definitionBedsByRoom = [];
        if (!roomId) return;

        $.get(baseUrl + '/beds-by-room/' + encodeURIComponent(roomId), function(res) {
            const beds = Array.isArray(res.beds) ? res.beds : [];
            definitionBedsByRoom = beds;
            if (beds.length === 0) {
                bedTypeSelect.append('<option value="" disabled>No bed types found</option>');
                return;
            }
            beds.forEach(function(b) {
                const label = b.room_type || 'Bed';
                const opt = new Option(label, label);
                $(opt).attr('data-extra-bed', b.extra_bed ? 1 : 0);
                $(opt).attr('data-extra-bed-type', b.extra_bed_type);
                $(opt).attr('data-bed-id', b.bed_id);
                bedTypeSelect.append(opt);
            });
        });
    });

    function renderDefinitionPendingRooms() {
        const el = $('#definition-pending-rooms');
        el.empty();
        if (definitionPendingRooms.length === 0) return;
        definitionPendingRooms.forEach(function(r, i) {
            const weekendLabel = r.weekend_price != null && !isNaN(parseFloat(r.weekend_price))
                ? ' · Weekend: ' + formatOptionalPrice(r.weekend_price)
                : '';
            el.append(`
                <div class="d-flex align-items-center justify-content-between border rounded px-2 py-1 mb-1 bg-white small">
                    <span>${escapeHtml(r.room_type_name)} · ${escapeHtml(r.bed_type || 'N/A')} × ${r.quantity}${weekendLabel}</span>
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
        const bedType = $('#definition-bed-type-select').val();
        if (!bedType) {
            alert('Please select a bed type.');
            return;
        }
        const extraBed = parseInt($('#definition-bed-type-select option:selected').attr('data-extra-bed') || '0', 10) === 1;
        const extraBedType = $('#definition-bed-type-select option:selected').attr('data-extra-bed-type');
        const bedId = $('#definition-bed-type-select option:selected').attr('data-bed-id');
        const qty = parseInt($('#definition-room-type-qty').val(), 10) || 1;
        if (qty < 1) return;
        const selectedRoomObj = (definitionRoomTypesByHotel || []).find(function(x) { return (x.id || x.room_id) == roomTypeId; });
        const weekendPrice = selectedRoomObj && selectedRoomObj.weekend_price != null && selectedRoomObj.weekend_price !== ''
            ? parseFloat(selectedRoomObj.weekend_price)
            : null;
        definitionPendingRooms.push({
            room_type_id: roomTypeId,
            room_type_name: roomTypeName,
            bed_type: bedType,
            extra_bed: extraBed,
            quantity: qty,
            extra_bed_type: extraBedType,
            bed_id: bedId,
            weekend_price: weekendPrice
        });
        renderDefinitionPendingRooms();
        $('#definition-room-type-qty').val(1);
    });

    function formatOptionalPrice(val) {
        if (val === '' || val == null || isNaN(parseFloat(val))) return '—';
        return 'SGD ' + parseFloat(val).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    function numPriceVal(v) {
        if (v === '' || v == null) return 0;
        const n = parseFloat(v);
        return isNaN(n) ? 0 : n;
    }
    function minFinite(nums) {
        if (!nums || nums.length === 0) return 0;
        let m = nums[0];
        for (let i = 1; i < nums.length; i++) {
            if (nums[i] < m) m = nums[i];
        }
        return m;
    }
    /** First hotel/restaurant sets allowed modes for rows 2+: 'compulsory' | 'optional' | 'addon' | null */
    function getFirstServiceTrack(list) {
        if (!list || list.length === 0) return null;
        const f = list[0];
        if (f.optional === true) return 'optional';
        if (f.addon === true) return 'addon';
        return 'compulsory';
    }
    function normalizeHotelModesByFirst() {
        if (!definitionHotels || definitionHotels.length <= 1) return;
        const track = getFirstServiceTrack(definitionHotels);
        if (track === 'addon') return;
        for (let i = 1; i < definitionHotels.length; i++) {
            const h = definitionHotels[i];
            if (track === 'compulsory') {
                if (h.optional === true) {
                    h.optional = false;
                    h.compulsory = true;
                    h.addon = false;
                    h.optional_price = '';
                    h.addon_price = '';
                }
            } else if (track === 'optional') {
                if (h.compulsory === true || (!h.optional && !h.addon)) {
                    h.compulsory = false;
                    h.optional = true;
                    h.addon = false;
                    if ((h.optional_price === '' || h.optional_price == null) && h.base_price != null && h.base_price !== '') {
                        h.optional_price = h.base_price;
                    }
                    h.addon_price = '';
                }
            }
        }
    }
    function normalizeRestaurantModesByFirst() {
        if (!definitionRestaurants || definitionRestaurants.length <= 1) return;
        const track = getFirstServiceTrack(definitionRestaurants);
        if (track === 'addon') return;
        for (let i = 1; i < definitionRestaurants.length; i++) {
            const r = definitionRestaurants[i];
            if (track === 'compulsory') {
                if (r.optional === true) {
                    r.optional = false;
                    r.compulsory = true;
                    r.addon = false;
                    r.optional_price = '';
                    r.addon_price = '';
                }
            } else if (track === 'optional') {
                if (r.compulsory === true || (!r.optional && !r.addon)) {
                    r.compulsory = false;
                    r.optional = true;
                    r.addon = false;
                    if ((r.optional_price === '' || r.optional_price == null) && r.base_price != null && r.base_price !== '') {
                        r.optional_price = r.base_price;
                    }
                    r.addon_price = '';
                }
            }
        }
    }
    /** Package total for hotels: sum compulsory + min optional; add-on ignored. */
    function computeHotelsTotalForPackage() {
        let compulsorySum = 0;
        const opts = [];
        (definitionHotels || []).forEach(function(h) {
            if (h.addon === true) return;
            const p = numPriceVal(h.base_price);
            if (h.optional === true) opts.push(p);
            else compulsorySum += p;
        });
        return compulsorySum + (opts.length ? minFinite(opts) : 0);
    }
    function restaurantLinePriceForTotal(r) {
        const base = numPriceVal(r && r.base_price);
        const transfer = (r && r.transfer) ? numPriceVal(r.transfer_price) : 0;
        return base + transfer;
    }
    function computeRestaurantsTotalForPackage() {
        let compulsorySum = 0;
        const opts = [];
        (definitionRestaurants || []).forEach(function(r) {
            if (r.addon === true) return;
            const p = restaurantLinePriceForTotal(r);
            if (r.optional === true) opts.push(p);
            else compulsorySum += p;
        });
        return compulsorySum + (opts.length ? minFinite(opts) : 0);
    }
    function attractionLineTotal(a) {
        const base = numPriceVal(a && a.base_price);
        const guide = numPriceVal(a && a.guide ? a.guide.price : 0);
        const transfer = (a && a.transfer) ? numPriceVal(a.transfer_price) : 0;
        return base + guide + transfer;
    }
    /**
     * Attractions: add-on ignored for package total.
     * Only compulsory: sum all compulsory line totals.
     * Only optional: minimum among optional line totals.
     * Both compulsory and optional: sum(compulsory) + min(optional).
     */
    function computeAttractionsTotalForPackage() {
        const compTotals = [];
        const optTotals = [];
        (definitionAttractions || []).forEach(function(a) {
            if (a.addon === true) return;
            const t = attractionLineTotal(a);
            if (a.optional === true) optTotals.push(t);
            else compTotals.push(t);
        });
        if (compTotals.length && optTotals.length) {
            let sumComp = 0;
            compTotals.forEach(function(x) { sumComp += x; });
            return sumComp + minFinite(optTotals);
        }
        if (compTotals.length) {
            let s = 0;
            compTotals.forEach(function(x) { s += x; });
            return s;
        }
        if (optTotals.length) return minFinite(optTotals);
        return 0;
    }
    function updateDefinitionTotalsAndMarkup() {
        const hotelsTotal = computeHotelsTotalForPackage();
        const attractionsTotal = computeAttractionsTotalForPackage();
        const restaurantsTotal = computeRestaurantsTotalForPackage();

        const arrivalTotal = (arrivalChosenVehicles || []).reduce(function(sum, v) {
            return sum + numPriceVal(v && v.selected_price);
        }, 0);
        const departureTotal = (departureChosenVehicles || []).reduce(function(sum, v) {
            return sum + numPriceVal(v && v.selected_price);
        }, 0);

        const subtotal = hotelsTotal + attractionsTotal + restaurantsTotal + arrivalTotal + departureTotal;
        $('#definition-total-price').val(subtotal.toFixed(2));
    }
    function renderOptionalServicesSummary() {
        function trimStr(s) { return (s || '').trim(); }
        const lines = [];
        function pushLine(name, type, rawPrice, fallback) {
            const p = numPriceVal(rawPrice !== '' && rawPrice != null ? rawPrice : fallback);
            if (p > 0 && name) lines.push({ name: String(name), type: type, price: p });
        }
        (definitionHotels || []).forEach(function(h) {
            const n = h.hotel_name || h.name || '';
            if (h.optional) pushLine(n, 'Hotel (Optional)', h.optional_price, h.base_price);
            if (h.addon) pushLine(n, 'Hotel (Add-on)', h.addon_price, h.base_price);
        });
        (definitionAttractions || []).forEach(function(a) {
            const n = a.name || '';
            if (a.optional) pushLine(n, 'Attraction (Optional)', a.optional_price, a.base_price);
            if (a.addon) pushLine(n, 'Attraction (Add-on)', a.addon_price, a.base_price);
        });
        (definitionRestaurants || []).forEach(function(r) {
            const n = r.restaurant_name || r.name || '';
            if (r.optional) pushLine(n, 'Restaurant (Optional)', r.optional_price, r.base_price);
            if (r.addon) pushLine(n, 'Restaurant (Add-on)', r.addon_price, r.base_price);
        });
        (definitionGuides || []).forEach(function(g) {
            const n = g.name || '';
            if (g.optional) pushLine(n, 'Guide (Optional)', g.optional_price, g.base_price);
            if (g.addon) pushLine(n, 'Guide (Add-on)', g.addon_price, g.base_price);
        });
        (localTransfersList || []).forEach(function(t) {
            const n = trimStr((t.pickup_label || '') + ' → ' + (t.dropoff_label || ''));
            if (t.optional) pushLine(n || 'Transfer', 'Transfer (Optional)', t.optional_price, t.base_price);
            if (t.addon) pushLine(n || 'Transfer', 'Transfer (Add-on)', t.addon_price, t.base_price);
        });
        const tbody = $('#optional-services-summary-tbody');
        const emptyEl = $('#optional-services-summary-empty');
        const wrap = $('#optional-services-summary-table-wrap');
        const totalEl = $('#optional-services-total');
        tbody.empty();
        if (lines.length === 0) {
            emptyEl.show();
            wrap.hide();
            totalEl.text('—');
            $('#price-data-hidden').val('[]');
            return;
        }
        emptyEl.hide();
        wrap.show();
        let sum = 0;
        lines.forEach(function(row) {
            sum += row.price;
            tbody.append(
                '<tr><td>' + escapeHtml(row.name) + '</td><td>' + escapeHtml(row.type) + '</td><td class="text-end">' + formatOptionalPrice(row.price) + '</td></tr>'
            );
        });
        totalEl.text(formatOptionalPrice(sum));
        const payload = lines.map(function(x) {
            return { name: x.name, type: x.type, price: x.price };
        });
        const markupType = $('#definition-markup-type').val();
        const markupAmount = numPriceVal($('#definition-markup-amount').val());
        if (markupType && markupAmount > 0) {
            payload.push({
                name: 'Markup',
                type: 'Markup',
                price: 0,
                markup_type: markupType,
                markup_amount: markupAmount
            });
        }
        $('#price-data-hidden').val(JSON.stringify(payload));
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
        // Base price from DB: sum (room weekend_price * quantity) * nights
        let dbPrice = 0;
        rooms.forEach(function(r) {
            const rt = (definitionRoomTypesByHotel || []).find(function(x) { return (x.id || x.room_id) == r.room_type_id; });
            if (rt && (rt.weekend_price != null || rt.weekday_price != null)) {
                const p = parseFloat(rt.weekend_price || rt.weekday_price || 0) || 0;
                dbPrice += p * (parseInt(r.quantity, 10) || 1);
            }
        });
        dbPrice = dbPrice * nights;
        const hotelBasePrice = dbPrice > 0 ? dbPrice : '';
        definitionHotels.push({
            hotel_id: hotelId,
            hotel_name: hotelName,
            nights,
            rooms,
            compulsory: true,
            optional: false,
            addon: false,
            optional_price: '',
            addon_price: '',
            base_price: hotelBasePrice
        });
        updateDefinitionHotelsInput();
        renderChosenHotels();
        resetDefinitionHotelForm();
    });

    function resetDefinitionHotelForm() {
        $('#definition-hotel-select').val('').trigger('change.select2');
        $('#definition-rooms-wrapper').hide();
        $('#definition-room-type-select').empty().append('<option value="">Select room type</option>');
        $('#definition-bed-type-select').empty().append('<option value="">Select bed type</option>');
        definitionBedsByRoom = [];
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
            renderOptionalServicesSummary();
            return;
        }
        normalizeHotelModesByFirst();
        updateDefinitionHotelsInput();
        const hotelTrack = getFirstServiceTrack(definitionHotels);
        placeholder.hide();
        countEl.text(definitionHotels.length);
        listEl.show().empty();
        definitionHotels.forEach(function(entry, idx) {
            const roomsText = entry.rooms.map(function(r) {
                return r.room_type_name + ' (' + (r.bed_type || 'N/A') + ') × ' + r.quantity;
            }).join(', ');
            const isCompulsory = entry.compulsory === true || (!entry.optional && !entry.addon);
            const isOptional = entry.optional === true;
            const isAddon = entry.addon === true;
            const baseRaw = entry.base_price != null && entry.base_price !== '' ? entry.base_price : '';
            const baseP = baseRaw !== '' ? parseFloat(baseRaw) : '';
            const optPriceRaw = entry.optional_price != null && entry.optional_price !== '' ? entry.optional_price : '';
            const optPrice = optPriceRaw !== '' ? parseFloat(optPriceRaw) : '';
            const addonPriceRaw = entry.addon_price != null && entry.addon_price !== '' ? entry.addon_price : '';
            const addonPrice = addonPriceRaw !== '' ? parseFloat(addonPriceRaw) : '';
            let priceDisplay = '';
            if (isOptional && optPrice !== '' && !isNaN(optPrice)) {
                priceDisplay = '<span class="badge bg-primary ms-2 optional-price-badge">Optional: ' + formatOptionalPrice(optPrice) + '</span>';
            } else if (isAddon && addonPrice !== '' && !isNaN(addonPrice)) {
                priceDisplay = '<span class="badge bg-secondary ms-2 optional-price-badge">Add-on: ' + formatOptionalPrice(addonPrice) + '</span>';
            } else if (baseP !== '' && !isNaN(baseP)) {
                priceDisplay = '<span class="badge bg-primary-subtle text-primary ms-2 optional-price-badge">Weekend: ' + formatOptionalPrice(baseP) + '</span>';
            }
            const showAllModes = idx === 0 || hotelTrack === 'addon';
            const showCompulsory = showAllModes || hotelTrack === 'compulsory';
            const showOptional = showAllModes || hotelTrack === 'optional';
            const radioCompulsory = showCompulsory ? `
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input chosen-hotel-mode" type="radio" name="hotel-mode-${idx}" id="hotel-comp-${idx}" data-idx="${idx}" value="compulsory" ${isCompulsory ? 'checked' : ''}>
                                    <label class="form-check-label small" for="hotel-comp-${idx}">Compulsory</label>
                                </div>` : '';
            const radioOptional = showOptional ? `
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input chosen-hotel-mode" type="radio" name="hotel-mode-${idx}" id="hotel-opt-${idx}" data-idx="${idx}" value="optional" ${isOptional ? 'checked' : ''}>
                                    <label class="form-check-label small" for="hotel-opt-${idx}">Optional</label>
                                </div>` : '';
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
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                ${radioCompulsory}
                                ${radioOptional}
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
            const h = definitionHotels[idx];
            if (h) {
                h.compulsory = mode === 'compulsory';
                h.optional = mode === 'optional';
                h.addon = mode === 'addon';
                if (mode === 'optional') {
                    if ((h.optional_price === '' || h.optional_price == null) && h.base_price != null && h.base_price !== '') h.optional_price = h.base_price;
                    h.addon_price = '';
                } else if (mode === 'addon') {
                    if ((h.addon_price === '' || h.addon_price == null) && h.base_price != null && h.base_price !== '') h.addon_price = h.base_price;
                    h.optional_price = '';
                } else {
                    h.optional_price = '';
                    h.addon_price = '';
                }
            }
            updateDefinitionHotelsInput();
            renderChosenHotels();
        });
        refreshTransferHotelDropdowns();
        renderOptionalServicesSummary();
        updateDefinitionTotalsAndMarkup();
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
        const ticketSel = $('#definition-attraction-ticket-select');
        const ticketAdultPriceEl = $('#definition-attraction-ticket-adult-price');
        ticketSel.empty().append('<option value="">Select ticket</option>');
        ticketAdultPriceEl.val('—');
        if (!val) {
            configEl.hide();
            ticketSel.empty().append('<option value="">Select attraction first</option>');
            return;
        }
        $.get(baseUrl + '/tickets-by-attraction/' + encodeURIComponent(val), function(res) {
            const tickets = Array.isArray(res.tickets) ? res.tickets : [];
            if (tickets.length === 0) {
                ticketSel.append('<option value="" disabled>No tickets found</option>');
                return;
            }
            tickets.forEach(function(t) {
                const ticketId = t.ticket_id || t.id;
                const label = t.name || ('Ticket ' + ticketId);
                const opt = new Option(label, ticketId);
                $(opt).data('ticket-data', t);
                ticketSel.append(opt);
            });
        });
        configEl.show();
        const guideSel = $('#definition-attraction-config-guide');
        guideSel.empty().append('<option value="">Select guide</option>');
        guidesByCity.forEach(function(g) {
            guideSel.append(new Option(g.name, g.guide_id));
        });
        $('#definition-attraction-config-guide-hour').val('');
        $('#definition-attraction-config-guide-price').val('—');
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

    $('#definition-attraction-ticket-select').on('change', function() {
        const ticketOpt = $(this).find('option:selected');
        const ticketData = ticketOpt.data('ticket-data') || null;
        const adultPrice = ticketData && ticketData.adult_price != null && ticketData.adult_price !== ''
            ? parseFloat(ticketData.adult_price)
            : null;
        $('#definition-attraction-ticket-adult-price').val(adultPrice != null && !isNaN(adultPrice) ? formatOptionalPrice(adultPrice) : '—');
    });
    $('#definition-attraction-config-need-guide').on('change', function() {
        const checked = $(this).is(':checked');
        $('#definition-attraction-config-guide-wrap').toggle(checked);
        if (!checked) {
            $('#definition-attraction-config-guide').val('');
            $('#definition-attraction-config-guide-hour').val('');
            $('#definition-attraction-config-guide-price').val('—');
        }
    });

    function updateAttractionGuidePrice() {
        const guideId = $('#definition-attraction-config-guide').val();
        const hourKey = $('#definition-attraction-config-guide-hour').val();
        const g = guideId ? guidesByCity.find(function(x) { return x.guide_id == guideId; }) : null;
        if (!g || !hourKey) {
            $('#definition-attraction-config-guide-price').val('—');
            return;
        }
        const priceKey = hourKey === 'hourly' ? 'hourly_price' : (hourKey + '_price');
        const price = g[priceKey] != null && g[priceKey] !== '' ? parseFloat(g[priceKey]) : null;
        $('#definition-attraction-config-guide-price').val(price != null && !isNaN(price) ? formatOptionalPrice(price) : '—');
    }

    $('#definition-attraction-config-guide').on('change', function() {
        const guideId = $(this).val();
        const g = guideId ? guidesByCity.find(function(x) { return x.guide_id == guideId; }) : null;
        const hourSel = $('#definition-attraction-config-guide-hour');
        hourSel.find('option').each(function() {
            const key = $(this).val();
            if (!key) { $(this).prop('disabled', false); return; }
            if (!g) { $(this).prop('disabled', true); return; }
            const priceKey = key === 'hourly' ? 'hourly_price' : (key + '_price');
            const hasPrice = g[priceKey] != null && g[priceKey] !== '' && !isNaN(parseFloat(g[priceKey]));
            $(this).prop('disabled', !hasPrice);
        });
        if (!g) hourSel.val('');
        updateAttractionGuidePrice();
    });

    $('#definition-attraction-config-guide-hour').on('change', function() {
        updateAttractionGuidePrice();
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
            $('#definition-attraction-config-transfer-price').val('0');
            fetchDefinitionAttractionTransferPrice();
        } else {
            $('#definition-attraction-config-transfer-price').val('0');
        }
    });

    function fetchDefinitionAttractionTransferPrice() {
        const transferEnabled = $('#definition-attraction-config-transfer').is(':checked');
        if (!transferEnabled) return;

        const vehicleId = $('#definition-attraction-config-vehicle').val();
        const attractionId = $('#definition-attraction-select').val();
        const pickupValue = $('#definition-attraction-config-pickup').val();
        const transferTypeRaw = $('input[name="definition_attr_transfer_type"]:checked').val() || 'private';
        if (!vehicleId || !attractionId || !pickupValue) {
            $('#definition-attraction-config-transfer-price').val('0');
            return;
        }

        const pickupParts = String(pickupValue).split('_');
        if (pickupParts.length < 2) {
            $('#definition-attraction-config-transfer-price').val('0');
            return;
        }
        const pickupTypeMap = { hotel: 'Hotel', attraction: 'Attraction', restaurant: 'Restaurant' };
        const pickupLocationType = pickupTypeMap[pickupParts[0]] || '';
        const pickupLocationId = pickupParts.slice(1).join('_');
        const transferType = transferTypeRaw === 'shared' ? 'Shared' : 'Private';
        if (!pickupLocationType || !pickupLocationId) {
            $('#definition-attraction-config-transfer-price').val('0');
            return;
        }

        $.ajax({
            url: fetchAttractionTransferPricingUrl,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                vehicle_id: vehicleId,
                attraction_id: attractionId,
                pickup_location_id: pickupLocationId,
                pickup_location_type: pickupLocationType,
                transfer_type: transferType,
                transfer_way: 'One Way'
            },
            success: function(res) {
                const p = res && res.success ? parseFloat(res.price || 0) : 0;
                $('#definition-attraction-config-transfer-price').val(!isNaN(p) && p > 0 ? p.toFixed(2) : '0');
            },
            error: function() {
                $('#definition-attraction-config-transfer-price').val('0');
            }
        });
    }
    $('#definition-attraction-config-vehicle, #definition-attraction-config-pickup').on('change', fetchDefinitionAttractionTransferPrice);
    $('input[name="definition_attr_transfer_type"]').on('change', fetchDefinitionAttractionTransferPrice);

    // Attractions: Add = current selection + config; preview = strip like hotel
    $('#definition-attraction-add-btn').on('click', function() {
        const opt = $('#definition-attraction-select').find('option:selected');
        const data = opt.data('attraction-data');
        if (!data || !opt.val()) {
            alert('Please select an attraction.');
            return;
        }
        const ticketOpt = $('#definition-attraction-ticket-select').find('option:selected');
        const ticketId = $('#definition-attraction-ticket-select').val();
        const ticketData = ticketOpt.data('ticket-data') || null;
        const needGuide = $('#definition-attraction-config-need-guide').is(':checked');
        const guideId = needGuide ? $('#definition-attraction-config-guide').val() : '';
        const guideHourKey = needGuide ? ($('#definition-attraction-config-guide-hour').val() || '') : '';
        const g = guideId ? guidesByCity.find(x => x.guide_id == guideId) : null;
        const durationLabels = { hourly: '1 Hour', two_hour: '2 Hours', four_hour: '4 Hours', six_hour: '6 Hours', eight_hour: '8 Hours', ten_hour: '10 Hours', twelve_hour: '12 Hours' };
        const guidePriceKey = guideHourKey ? (guideHourKey === 'hourly' ? 'hourly_price' : (guideHourKey + '_price')) : '';
        const guidePrice = (g && guidePriceKey && g[guidePriceKey] != null && g[guidePriceKey] !== '') ? parseFloat(g[guidePriceKey]) : null;
        if (needGuide && (!guideId || !guideHourKey)) {
            alert('Please select guide and hour.');
            return;
        }
        const transfer = $('#definition-attraction-config-transfer').is(':checked');
        const vehicleId = $('#definition-attraction-config-vehicle').val();
        const v = vehiclesByCity.find(x => x.vehicle_id == vehicleId);
        const transferType = $('input[name="definition_attr_transfer_type"]:checked').val() || 'private';
        const transferPriceRaw = $('#definition-attraction-config-transfer-price').val();
        const transferPrice = transfer ? (isNaN(parseFloat(transferPriceRaw)) ? 0 : parseFloat(transferPriceRaw)) : 0;
        const pickupId = $('#definition-attraction-config-pickup').val();
        const pickupName = $('#definition-attraction-config-pickup').find('option:selected').text();
        const dropoffVal = $('#definition-attraction-config-dropoff').val();
        const dropoffName = $('#definition-attraction-config-dropoff').find('option:selected').text();
        let attrPrice = '';
        if (ticketData && ticketData.adult_price != null && ticketData.adult_price !== '') {
            attrPrice = parseFloat(ticketData.adult_price);
        } else if (data.adult_price != null && data.adult_price !== '') {
            attrPrice = parseFloat(data.adult_price);
        }
        definitionAttractions.push({
            attraction_id: data.attraction_id,
            name: data.name,
            location: data.location,
            image: data.image || '',
            compulsory: true,
            optional: false,
            addon: false,
            optional_price: '',
            addon_price: '',
            base_price: attrPrice,
            ticket_id: ticketId || null,
            ticket_name: ticketData ? (ticketData.name || null) : null,
            ticket: ticketData ? {
                id: ticketData.id || null,
                ticket_id: ticketData.ticket_id || ticketId || null,
                name: ticketData.name || null
            } : null,
            guide: g ? {
                id: g.guide_id,
                name: g.name,
                languages: g.languages,
                contact_no: g.contact_no,
                duration_key: guideHourKey || null,
                duration_label: guideHourKey ? (durationLabels[guideHourKey] || null) : null,
                price: guidePrice != null && !isNaN(guidePrice) ? guidePrice : null
            } : null,
            transfer: transfer,
            vehicle_id: v ? v.vehicle_id : null,
            vehicle_name: v ? v.name : null,
            transfer_price: transferPrice,
            transfer_type: transferType,
            pickup_id: pickupId || null,
            pickup_name: pickupName || null,
            dropoff_id: dropoffVal || null,
            dropoff_name: dropoffName || data.name
        });
        updateDefinitionAttractionsInput();
        renderDefinitionAttractions();
        $('#definition-attraction-select').val('').trigger('change');
        $('#definition-attraction-ticket-select').empty().append('<option value="">Select attraction first</option>');
        $('#definition-attraction-ticket-adult-price').val('—');
        $('#definition-attraction-config').hide();
    });

    function renderDefinitionAttractions() {
        const emptyEl = $('#definition-attractions-empty');
        const container = $('#definition-attractions-list');
        container.empty();
        if (definitionAttractions.length === 0) {
            emptyEl.show();
            container.hide();
            renderOptionalServicesSummary();
            return;
        }
        emptyEl.hide();
        container.show();
        definitionAttractions.forEach(function(a, idx) {
            const basePriceNum = a.base_price != null && a.base_price !== '' && !isNaN(parseFloat(a.base_price)) ? parseFloat(a.base_price) : 0;
            const guidePriceNum = a.guide && a.guide.price != null && !isNaN(parseFloat(a.guide.price)) ? parseFloat(a.guide.price) : 0;
            const transferPriceNum = a.transfer && a.transfer_price != null && !isNaN(parseFloat(a.transfer_price)) ? parseFloat(a.transfer_price) : 0;
            const totalPriceNum = basePriceNum + guidePriceNum + transferPriceNum;

            const parts = [];
            if (a.ticket_name) parts.push('<i class="ri-coupon-3-line me-1" title="Ticket"></i>' + escapeHtml(a.ticket_name));
            if (a.guide && a.guide.name) {
                const guideMeta = [];
                if (a.guide.duration_label) guideMeta.push(a.guide.duration_label);
                if (a.guide.price != null && !isNaN(parseFloat(a.guide.price))) guideMeta.push(formatOptionalPrice(a.guide.price));
                const guideText = guideMeta.length ? (a.guide.name + ' (' + guideMeta.join(', ') + ')') : a.guide.name;
                parts.push('<i class="ri-user-line me-1" title="Guide"></i>' + escapeHtml(guideText));
            }
            if (a.transfer) {
                if (a.vehicle_name) parts.push('<i class="ri-car-line me-1" title="Vehicle"></i>' + escapeHtml(a.vehicle_name));
                parts.push('Transfer: ' + (a.transfer_type === 'shared' ? 'Shared' : 'Private'));
                if (a.transfer_price != null && !isNaN(parseFloat(a.transfer_price))) parts.push('Price: ' + formatOptionalPrice(a.transfer_price));
                const pickupName = a.pickup_name || a.pickup_hotel_name;
                if (pickupName && a.dropoff_name) parts.push(escapeHtml(pickupName) + ' → ' + escapeHtml(a.dropoff_name));
                else if (pickupName) parts.push(escapeHtml(pickupName) + ' → —');
                else if (a.dropoff_name) parts.push('— → ' + escapeHtml(a.dropoff_name));
            }
            parts.push('<span class="fw-semibold">Total: ' + formatOptionalPrice(totalPriceNum) + '</span>');
            const summaryHtml = parts.length ? parts.join(' <span class="text-muted">·</span> ') : '—';
            const isCompulsory = a.compulsory === true || (!a.optional && !a.addon);
            const isOptional = a.optional === true;
            const isAddonA = a.addon === true;
            const baseRawA = a.base_price != null && a.base_price !== '' ? a.base_price : '';
            const basePA = baseRawA !== '' ? parseFloat(baseRawA) : '';
            const optPriceRawA = a.optional_price != null && a.optional_price !== '' ? a.optional_price : '';
            const optPriceA = optPriceRawA !== '' ? parseFloat(optPriceRawA) : '';
            const addonPriceRawA = a.addon_price != null && a.addon_price !== '' ? a.addon_price : '';
            const addonPriceA = addonPriceRawA !== '' ? parseFloat(addonPriceRawA) : '';
            const totalBadge = '<span class="badge bg-success ms-2">Total: ' + formatOptionalPrice(totalPriceNum) + '</span>';
            container.append(`
                <div class="card mb-2 border shadow-sm chosen-hotel-card" data-idx="${idx}">
                    <div class="card-body py-2 px-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                                <span class="badge bg-success-subtle text-success rounded-circle p-2"><i class="ri-map-pin-line"></i></span>
                                <div>
                                    <strong class="d-block">${escapeHtml(a.name)}${totalBadge}</strong>
                                    <small class="text-muted">${summaryHtml}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input def-attraction-mode" type="radio" name="attr-mode-${idx}" id="attr-comp-${idx}" data-idx="${idx}" value="compulsory" ${isCompulsory ? 'checked' : ''}>
                                    <label class="form-check-label small" for="attr-comp-${idx}">Compulsory</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input def-attraction-mode" type="radio" name="attr-mode-${idx}" id="attr-opt-${idx}" data-idx="${idx}" value="optional" ${isOptional ? 'checked' : ''}>
                                    <label class="form-check-label small" for="attr-opt-${idx}">Optional</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input def-attraction-mode" type="radio" name="attr-mode-${idx}" id="attr-addon-${idx}" data-idx="${idx}" value="addon" ${isAddonA ? 'checked' : ''}>
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
            const a = definitionAttractions[idx];
            if (a) {
                a.compulsory = mode === 'compulsory';
                a.optional = mode === 'optional';
                a.addon = mode === 'addon';
                if (mode === 'optional') {
                    if ((a.optional_price === '' || a.optional_price == null) && a.base_price != null && a.base_price !== '') a.optional_price = a.base_price;
                    a.addon_price = '';
                } else if (mode === 'addon') {
                    if ((a.addon_price === '' || a.addon_price == null) && a.base_price != null && a.base_price !== '') a.addon_price = a.base_price;
                    a.optional_price = '';
                } else {
                    a.optional_price = '';
                    a.addon_price = '';
                }
            }
            updateDefinitionAttractionsInput();
            renderDefinitionAttractions();
        });
        renderOptionalServicesSummary();
        updateDefinitionTotalsAndMarkup();
    }

    function updateDefinitionAttractionsInput() {
        $('#definition-attractions-input').val(JSON.stringify(definitionAttractions));
        if (typeof refreshLocalTransferPickupDropoff === 'function') refreshLocalTransferPickupDropoff();
    }

    // Restaurant select: show config panel, load meal types, and populate vehicle
    $('#definition-restaurant-select').on('change', function() {
        const val = $(this).val();
        const configEl = $('#definition-restaurant-config');
        const mealsWrap = $('#definition-restaurant-meals-wrap');
        const mealTypeSel = $('#definition-restaurant-meal-type-select');
        const mealAdultPriceEl = $('#definition-restaurant-meal-adult-price');
        mealTypeSel.empty().append('<option value="">Select meal type</option>');
        mealAdultPriceEl.val('—');
        if (!val) {
            configEl.hide();
            mealsWrap.hide();
            return;
        }
        configEl.show();
        $.get(baseUrl + '/restaurant-meals/' + encodeURIComponent(val), function(res) {
            const meals = Array.isArray(res.meals) ? res.meals : [];
            restaurantMealsByRestaurant[String(val)] = meals;
            if (meals.length === 0) {
                mealTypeSel.append('<option value="" disabled>No meal types found</option>');
                mealsWrap.hide();
                return;
            }
            meals.forEach(function(m) {
                const label = m.type_label || ('Type ' + (m.type || ''));
                const opt = new Option(label, m.type);
                $(opt).data('meal-data', m);
                mealTypeSel.append(opt);
            });
            mealsWrap.show();
        });
        const vehicleSel = $('#definition-restaurant-config-vehicle');
        vehicleSel.empty().append('<option value="">Select vehicle</option>');
        vehiclesByCity.forEach(function(v) {
            vehicleSel.append(new Option(v.name + (v.vehicle_type ? ' (' + v.vehicle_type + ')' : ''), v.vehicle_id));
        });
        $('#definition-restaurant-config-transfer').prop('checked', false);
        $('#definition-restaurant-config-vehicle-wrap').hide();
        $('#definition-rest-transfer-private').prop('checked', true);
    });

    $('#definition-restaurant-meal-type-select').on('change', function() {
        const opt = $(this).find('option:selected');
        const meal = opt.data('meal-data') || null;
        const adult = meal && meal.adult_price != null && meal.adult_price !== '' ? parseFloat(meal.adult_price) : null;
        $('#definition-restaurant-meal-adult-price').val(adult != null && !isNaN(adult) ? formatOptionalPrice(adult) : '—');
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
            $('#definition-restaurant-config-transfer-price').val('0');
            fetchDefinitionRestaurantTransferPrice();
        } else {
            $('#definition-restaurant-config-transfer-price').val('0');
        }
    });

    function fetchDefinitionRestaurantTransferPrice() {
        const transferEnabled = $('#definition-restaurant-config-transfer').is(':checked');
        if (!transferEnabled) return;

        const vehicleId = $('#definition-restaurant-config-vehicle').val();
        const restaurantId = $('#definition-restaurant-select').val();
        const pickupValue = $('#definition-restaurant-config-pickup').val();
        const transferTypeRaw = $('input[name="definition_rest_transfer_type"]:checked').val() || 'private';
        if (!vehicleId || !restaurantId || !pickupValue) {
            $('#definition-restaurant-config-transfer-price').val('0');
            return;
        }

        const pickupParts = String(pickupValue).split('_');
        if (pickupParts.length < 2) {
            $('#definition-restaurant-config-transfer-price').val('0');
            return;
        }
        const pickupTypeMap = { hotel: 'Hotel', attraction: 'Attraction', restaurant: 'Restaurant' };
        const pickupLocationType = pickupTypeMap[pickupParts[0]] || '';
        const pickupLocationId = pickupParts.slice(1).join('_');
        const transferType = transferTypeRaw === 'shared' ? 'Shared' : 'Private';
        if (!pickupLocationType || !pickupLocationId) {
            $('#definition-restaurant-config-transfer-price').val('0');
            return;
        }

        $.ajax({
            url: fetchRestaurantTransferPricingUrl,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                vehicle_id: vehicleId,
                restaurant_id: restaurantId,
                pickup_location_id: pickupLocationId,
                pickup_location_type: pickupLocationType,
                transfer_type: transferType,
                transfer_way: 'One Way'
            },
            success: function(res) {
                const p = res && res.success ? parseFloat(res.price || 0) : 0;
                $('#definition-restaurant-config-transfer-price').val(!isNaN(p) && p > 0 ? p.toFixed(2) : '0');
            },
            error: function() {
                $('#definition-restaurant-config-transfer-price').val('0');
            }
        });
    }
    $('#definition-restaurant-config-vehicle, #definition-restaurant-config-pickup').on('change', fetchDefinitionRestaurantTransferPrice);
    $('input[name="definition_rest_transfer_type"]').on('change', fetchDefinitionRestaurantTransferPrice);

    // Restaurants: Add = current selection + config + selected meal type; preview = strip like hotel
    $('#definition-restaurant-add-btn').on('click', function() {
        const id = $('#definition-restaurant-select').val();
        const name = $('#definition-restaurant-select').find('option:selected').text();
        if (!id) {
            alert('Please select a restaurant.');
            return;
        }
        const mealTypeVal = $('#definition-restaurant-meal-type-select').val();
        const mealTypeOpt = $('#definition-restaurant-meal-type-select').find('option:selected');
        const selectedMeal = mealTypeOpt.data('meal-data') || null;
        const knownMeals = restaurantMealsByRestaurant[String(id)] || [];
        if (knownMeals.length > 0 && !selectedMeal) {
            alert('Please select a meal type.');
            return;
        }
        const transfer = $('#definition-restaurant-config-transfer').is(':checked');
        const vehicleId = $('#definition-restaurant-config-vehicle').val();
        const v = vehiclesByCity.find(x => x.vehicle_id == vehicleId);
        const transferType = $('input[name="definition_rest_transfer_type"]:checked').val() || 'private';
        const transferPriceRaw = $('#definition-restaurant-config-transfer-price').val();
        const transferPrice = transfer ? (isNaN(parseFloat(transferPriceRaw)) ? 0 : parseFloat(transferPriceRaw)) : 0;
        const pickupId = $('#definition-restaurant-config-pickup').val();
        const pickupName = $('#definition-restaurant-config-pickup').find('option:selected').text();
        const dropoffVal = $('#definition-restaurant-config-dropoff').val();
        const dropoffName = $('#definition-restaurant-config-dropoff').find('option:selected').text();
        const restPrice = selectedMeal && selectedMeal.adult_price != null
            ? parseFloat(selectedMeal.adult_price || 0) : '';
        definitionRestaurants.push({
            restaurant_id: id,
            restaurant_name: name,
            compulsory: true,
            optional: false,
            addon: false,
            optional_price: '',
            addon_price: '',
            base_price: restPrice,
            selected_meals: mealTypeVal ? [String(mealTypeVal)] : [],
            meal_type: selectedMeal ? (selectedMeal.type || null) : null,
            meal_type_label: selectedMeal ? (selectedMeal.type_label || null) : null,
            meal_adult_price: selectedMeal && selectedMeal.adult_price != null ? parseFloat(selectedMeal.adult_price) : null,
            meal_child_price: selectedMeal && selectedMeal.child_price != null ? parseFloat(selectedMeal.child_price) : null,
            transfer: transfer,
            vehicle_id: v ? v.vehicle_id : null,
            vehicle_name: v ? v.name : null,
            transfer_price: transferPrice,
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
            renderOptionalServicesSummary();
            return;
        }
        normalizeRestaurantModesByFirst();
        updateDefinitionRestaurantsInput();
        const restaurantTrack = getFirstServiceTrack(definitionRestaurants);
        emptyEl.hide();
        container.show();
        definitionRestaurants.forEach(function(r, idx) {
            const restaurantPriceNum = r.base_price != null && r.base_price !== '' && !isNaN(parseFloat(r.base_price)) ? parseFloat(r.base_price) : 0;
            const transferPriceNum = r.transfer && r.transfer_price != null && !isNaN(parseFloat(r.transfer_price)) ? parseFloat(r.transfer_price) : 0;
            const totalPriceNum = restaurantPriceNum + transferPriceNum;

            const parts = [];
            if (r.meal_type_label) {
                parts.push('<i class="ri-restaurant-2-line me-1" title="Meal Type"></i>' + escapeHtml(r.meal_type_label));
            }
            if (r.meal_adult_price != null && !isNaN(parseFloat(r.meal_adult_price))) {
                parts.push('Adult: ' + formatOptionalPrice(r.meal_adult_price));
            }
            if (r.transfer) {
                if (r.vehicle_name) parts.push('<i class="ri-car-line me-1" title="Vehicle"></i>' + escapeHtml(r.vehicle_name));
                parts.push('Transfer: ' + (r.transfer_type === 'shared' ? 'Shared' : 'Private'));
                if (r.transfer_price != null && !isNaN(parseFloat(r.transfer_price))) parts.push('Price: ' + formatOptionalPrice(r.transfer_price));
                const pickupName = r.pickup_name || r.pickup_hotel_name;
                if (pickupName && r.dropoff_name) parts.push(escapeHtml(pickupName) + ' → ' + escapeHtml(r.dropoff_name));
                else if (pickupName) parts.push(escapeHtml(pickupName) + ' → —');
                else if (r.dropoff_name) parts.push('— → ' + escapeHtml(r.dropoff_name));
            }
            parts.push('<span class="fw-semibold">Total: ' + formatOptionalPrice(totalPriceNum) + '</span>');
            const summaryHtml = parts.length ? parts.join(' <span class="text-muted">·</span> ') : '—';
            const isCompulsory = r.compulsory === true || (!r.optional && !r.addon);
            const isOptional = r.optional === true;
            const isAddonR = r.addon === true;
            const totalBadge = '<span class="badge bg-warning text-dark ms-2">Total: ' + formatOptionalPrice(totalPriceNum) + '</span>';
            const showAllRestModes = idx === 0 || restaurantTrack === 'addon';
            const showRestCompulsory = showAllRestModes || restaurantTrack === 'compulsory';
            const showRestOptional = showAllRestModes || restaurantTrack === 'optional';
            const restRadioCompulsory = showRestCompulsory ? `
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input def-restaurant-mode" type="radio" name="rest-mode-${idx}" id="rest-comp-${idx}" data-idx="${idx}" value="compulsory" ${isCompulsory ? 'checked' : ''}>
                                    <label class="form-check-label small" for="rest-comp-${idx}">Compulsory</label>
                                </div>` : '';
            const restRadioOptional = showRestOptional ? `
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input def-restaurant-mode" type="radio" name="rest-mode-${idx}" id="rest-opt-${idx}" data-idx="${idx}" value="optional" ${isOptional ? 'checked' : ''}>
                                    <label class="form-check-label small" for="rest-opt-${idx}">Optional</label>
                                </div>` : '';
            container.append(`
                <div class="card mb-2 border shadow-sm chosen-hotel-card" data-idx="${idx}">
                    <div class="card-body py-2 px-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                                <span class="badge bg-warning-subtle text-warning rounded-circle p-2"><i class="ri-restaurant-line"></i></span>
                                <div>
                                    <strong class="d-block">${escapeHtml(r.restaurant_name)}${totalBadge}</strong>
                                    <small class="text-muted">${summaryHtml}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                ${restRadioCompulsory}
                                ${restRadioOptional}
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input def-restaurant-mode" type="radio" name="rest-mode-${idx}" id="rest-addon-${idx}" data-idx="${idx}" value="addon" ${isAddonR ? 'checked' : ''}>
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
            const r = definitionRestaurants[idx];
            if (r) {
                r.compulsory = mode === 'compulsory';
                r.optional = mode === 'optional';
                r.addon = mode === 'addon';
                if (mode === 'optional') {
                    if ((r.optional_price === '' || r.optional_price == null) && r.base_price != null && r.base_price !== '') r.optional_price = r.base_price;
                    r.addon_price = '';
                } else if (mode === 'addon') {
                    if ((r.addon_price === '' || r.addon_price == null) && r.base_price != null && r.base_price !== '') r.addon_price = r.base_price;
                    r.optional_price = '';
                } else {
                    r.optional_price = '';
                    r.addon_price = '';
                }
            }
            updateDefinitionRestaurantsInput();
            renderDefinitionRestaurants();
        });
        renderOptionalServicesSummary();
        updateDefinitionTotalsAndMarkup();
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

    /** Match API vehicle row to select value (string/number/id aliases). */
    function findLocalTransferVehicleBySelectValue(vehicles, val) {
        if (val == null || val === '' || !Array.isArray(vehicles)) return null;
        const s = String(val);
        return vehicles.find(function(x) {
            if (!x) return false;
            const id = x.vehicle_id != null ? x.vehicle_id : x.id;
            return String(id) === s;
        }) || null;
    }

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
                localTransferVehiclesByZone = Array.isArray(res.vehicles) ? res.vehicles : [];
                const sel = $('#local-transfer-vehicle-select');
                sel.empty().append('<option value="">Select vehicle</option>');
                localTransferVehiclesByZone.forEach(function(v) {
                    const vid = v.vehicle_id != null ? v.vehicle_id : v.id;
                    const name = (v.vehicle_name || v.name) + (v.vehicle_type ? ' (' + v.vehicle_type + ')' : '');
                    const opt = new Option(name, vid);
                    $(opt).data('vehicle-row', v);
                    sel.append(opt);
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
        let v = findLocalTransferVehicleBySelectValue(localTransferVehiclesByZone, val);
        if (!v) {
            const opt = $('#local-transfer-vehicle-select').find('option:selected');
            v = opt.data('vehicle-row');
        }
        if (!v) return;
        const vid = v.vehicle_id != null ? v.vehicle_id : v.id;
        if (localTransferChosenVehicles.some(function(x) { return String(x.vehicle_id) === String(vid); })) return;
        const priv = parseFloat(v.private_price) || 0;
        const shared = parseFloat(v.shared_price) || 0;
        localTransferChosenVehicles.push({ vehicle_id: vid, vehicle_name: v.vehicle_name || v.name, vehicle_type: v.vehicle_type, private_price: priv, shared_price: shared });
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
        let vehiclesSnapshot = localTransferChosenVehicles.slice();
        const pendingVal = $('#local-transfer-vehicle-select').val();
        if (pendingVal) {
            const already = vehiclesSnapshot.some(function(x) { return String(x.vehicle_id) === String(pendingVal); });
            if (!already) {
                let pv = findLocalTransferVehicleBySelectValue(localTransferVehiclesByZone, pendingVal);
                if (!pv) {
                    const opt = $('#local-transfer-vehicle-select').find('option:selected');
                    pv = opt.data('vehicle-row');
                }
                if (pv) {
                    const pvid = pv.vehicle_id != null ? pv.vehicle_id : pv.id;
                    const priv = parseFloat(pv.private_price) || 0;
                    const shared = parseFloat(pv.shared_price) || 0;
                    vehiclesSnapshot.push({ vehicle_id: pvid, vehicle_name: pv.vehicle_name || pv.name, vehicle_type: pv.vehicle_type, private_price: priv, shared_price: shared });
                }
            }
        }
        const transferPrice = vehiclesSnapshot.reduce(function(sum, v) {
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
            vehicles: vehiclesSnapshot,
            compulsory: true,
            optional: false,
            addon: false,
            optional_price: '',
            addon_price: '',
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
            renderOptionalServicesSummary();
            return;
        }
        $('#local-transfer-empty').hide();
        const html = localTransfersList.map(function(t, i) {
            const vList = (t.vehicles || []).map(function(v) { return (v.vehicle_name || '') + (v.vehicle_type ? ' (' + v.vehicle_type + ')' : ''); }).join(', ');
            const isCompulsory = t.compulsory === true || (!t.optional && !t.addon);
            const isOptional = t.optional === true;
            const isAddonT = t.addon === true;
            const baseRawT = t.base_price != null && t.base_price !== '' ? t.base_price : '';
            const basePT = baseRawT !== '' ? parseFloat(baseRawT) : '';
            const optPriceRawT = t.optional_price != null && t.optional_price !== '' ? t.optional_price : '';
            const optPriceT = optPriceRawT !== '' ? parseFloat(optPriceRawT) : '';
            const addonPriceRawT = t.addon_price != null && t.addon_price !== '' ? t.addon_price : '';
            const addonPriceT = addonPriceRawT !== '' ? parseFloat(addonPriceRawT) : '';
            let priceDisplay = '';
            if (isOptional && optPriceT !== '' && !isNaN(optPriceT)) {
                priceDisplay = ' <span class="badge bg-secondary optional-price-badge">Optional: ' + formatOptionalPrice(optPriceT) + '</span>';
            } else if (isAddonT && addonPriceT !== '' && !isNaN(addonPriceT)) {
                priceDisplay = ' <span class="badge bg-dark optional-price-badge">Add-on: ' + formatOptionalPrice(addonPriceT) + '</span>';
            } else if (basePT !== '' && !isNaN(basePT)) {
                priceDisplay = ' <span class="badge bg-light text-dark optional-price-badge">' + formatOptionalPrice(basePT) + '</span>';
            }
            return '<div class="border rounded p-2 mb-2 small">' +
                '<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">' +
                '<div class="flex-grow-1">' +
                (t.pickup_label || '') + ' → ' + (t.dropoff_label || '') + priceDisplay + (vList ? ' <span class="text-muted">(' + vList + ')</span>' : '') +
                '</div>' +
                '<div class="d-flex align-items-center gap-2 flex-wrap">' +
                '<div class="form-check form-check-inline mb-0"><input class="form-check-input local-transfer-mode" type="radio" name="lt-mode-' + i + '" id="lt-comp-' + i + '" data-idx="' + i + '" value="compulsory" ' + (isCompulsory ? 'checked' : '') + '><label class="form-check-label small" for="lt-comp-' + i + '">Compulsory</label></div>' +
                '<div class="form-check form-check-inline mb-0"><input class="form-check-input local-transfer-mode" type="radio" name="lt-mode-' + i + '" id="lt-opt-' + i + '" data-idx="' + i + '" value="optional" ' + (isOptional ? 'checked' : '') + '><label class="form-check-label small" for="lt-opt-' + i + '">Optional</label></div>' +
                '<div class="form-check form-check-inline mb-0"><input class="form-check-input local-transfer-mode" type="radio" name="lt-mode-' + i + '" id="lt-addon-' + i + '" data-idx="' + i + '" value="addon" ' + (isAddonT ? 'checked' : '') + '><label class="form-check-label small" for="lt-addon-' + i + '">Add-On</label></div>' +
                '<button type="button" class="btn btn-outline-danger btn-sm local-transfer-remove" data-idx="' + i + '"><i class="ri-delete-bin-line"></i></button>' +
                '</div></div></div>';
        }).join('');
        $('#local-transfer-list').html(html).show();
        renderOptionalServicesSummary();
    }

    $(document).on('change', '.local-transfer-mode', function() {
        const idx = parseInt($(this).data('idx'), 10);
        const mode = $(this).val();
        const t = localTransfersList[idx];
        if (t) {
            t.compulsory = mode === 'compulsory';
            t.optional = mode === 'optional';
            t.addon = mode === 'addon';
            if (mode === 'optional') {
                if ((t.optional_price === '' || t.optional_price == null) && t.base_price != null && t.base_price !== '') t.optional_price = t.base_price;
                t.addon_price = '';
            } else if (mode === 'addon') {
                if ((t.addon_price === '' || t.addon_price == null) && t.base_price != null && t.base_price !== '') t.addon_price = t.base_price;
                t.optional_price = '';
            } else {
                t.optional_price = '';
                t.addon_price = '';
            }
            renderLocalTransfersList();
            $('#local-transfers-hidden').val(JSON.stringify(localTransfersList));
        }
    });

    $(document).on('click', '.local-transfer-remove', function(e) {
        e.preventDefault();
        const idx = parseInt($(this).data('idx'), 10);
        localTransfersList.splice(idx, 1);
        renderLocalTransfersList();
        $('#local-transfers-hidden').val(JSON.stringify(localTransfersList));
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
            compulsory: true,
            optional: false,
            addon: false,
            optional_price: '',
            addon_price: '',
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
            renderOptionalServicesSummary();
            return;
        }
        emptyEl.hide();
        countEl.text(definitionGuides.length);
        listEl.show().empty();
        definitionGuides.forEach(function(g, idx) {
            const isCompulsory = g.compulsory === true || (!g.optional && !g.addon);
            const isOptional = g.optional === true;
            const isAddonG = g.addon === true;
            const langText = g.languages && g.languages.length ? ' (' + g.languages.join(', ') + ')' : '';
            const durationText = g.duration_label ? ' <span class="text-muted">·</span> ' + escapeHtml(g.duration_label) : '';
            const baseRawG = g.base_price != null && g.base_price !== '' ? g.base_price : '';
            const basePG = baseRawG !== '' ? parseFloat(baseRawG) : '';
            const optPriceRawG = g.optional_price != null && g.optional_price !== '' ? g.optional_price : '';
            const optPriceG = optPriceRawG !== '' ? parseFloat(optPriceRawG) : '';
            const addonPriceRawG = g.addon_price != null && g.addon_price !== '' ? g.addon_price : '';
            const addonPriceG = addonPriceRawG !== '' ? parseFloat(addonPriceRawG) : '';
            let priceDisplay = '';
            if (isOptional && optPriceG !== '' && !isNaN(optPriceG)) {
                priceDisplay = '<span class="badge bg-info ms-2 optional-price-badge">Optional: ' + formatOptionalPrice(optPriceG) + '</span>';
            } else if (isAddonG && addonPriceG !== '' && !isNaN(addonPriceG)) {
                priceDisplay = '<span class="badge bg-secondary ms-2 optional-price-badge">Add-on: ' + formatOptionalPrice(addonPriceG) + '</span>';
            } else if (basePG !== '' && !isNaN(basePG)) {
                priceDisplay = '<span class="badge bg-info-subtle text-info ms-2 optional-price-badge">' + formatOptionalPrice(basePG) + '</span>';
            }
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
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input def-guide-mode" type="radio" name="guide-mode-${idx}" id="guide-comp-${idx}" data-idx="${idx}" value="compulsory" ${isCompulsory ? 'checked' : ''}>
                                    <label class="form-check-label small" for="guide-comp-${idx}">Compulsory</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input def-guide-mode" type="radio" name="guide-mode-${idx}" id="guide-opt-${idx}" data-idx="${idx}" value="optional" ${isOptional ? 'checked' : ''}>
                                    <label class="form-check-label small" for="guide-opt-${idx}">Optional</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input def-guide-mode" type="radio" name="guide-mode-${idx}" id="guide-addon-${idx}" data-idx="${idx}" value="addon" ${isAddonG ? 'checked' : ''}>
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
            const g = definitionGuides[idx];
            if (g) {
                g.compulsory = mode === 'compulsory';
                g.optional = mode === 'optional';
                g.addon = mode === 'addon';
                if (mode === 'optional') {
                    if ((g.optional_price === '' || g.optional_price == null) && g.base_price != null && g.base_price !== '') g.optional_price = g.base_price;
                    g.addon_price = '';
                } else if (mode === 'addon') {
                    if ((g.addon_price === '' || g.addon_price == null) && g.base_price != null && g.base_price !== '') g.addon_price = g.base_price;
                    g.optional_price = '';
                } else {
                    g.optional_price = '';
                    g.addon_price = '';
                }
            }
            updateDefinitionGuidesInput();
            renderChosenGuides();
        });
        renderOptionalServicesSummary();
    }

    function updateDefinitionGuidesInput() {
        $('#definition-independent-guide-input').val(JSON.stringify(definitionGuides));
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

    function formatTransferVehiclePricing(v) {
        const privatePrice = v && v.private_price != null && !isNaN(parseFloat(v.private_price)) ? parseFloat(v.private_price) : 0;
        const sharedPrice = v && v.shared_price != null && !isNaN(parseFloat(v.shared_price)) ? parseFloat(v.shared_price) : 0;
        return {
            private_price: privatePrice,
            shared_price: sharedPrice,
            label: 'Private: ' + formatOptionalPrice(privatePrice) + ' | Shared: ' + formatOptionalPrice(sharedPrice)
        };
    }

    function renderChosenVehiclesList(list, containerId) {
        const el = $('#' + containerId);
        el.empty();
        list.forEach(function(v, idx) {
            const selectedType = (v.selected_transfer_type || '').toLowerCase();
            const qty = selectedType === 'private'
                ? Math.max(1, parseInt(v.qty != null ? v.qty : 1, 10) || 1)
                : 1;
            const unitPrice = v.unit_price != null && !isNaN(parseFloat(v.unit_price))
                ? parseFloat(v.unit_price)
                : (v.selected_price != null && !isNaN(parseFloat(v.selected_price)) ? parseFloat(v.selected_price) : 0);
            const totalPrice = selectedType === 'private' ? (unitPrice * qty) : unitPrice;
            v.qty = qty;
            v.unit_price = unitPrice;
            v.selected_price = totalPrice;
            const selectedPrice = v.selected_price != null && !isNaN(parseFloat(v.selected_price)) ? parseFloat(v.selected_price) : null;
            const selectedBadge = selectedType
                ? '<span class="badge bg-secondary ms-1">' + (selectedType === 'shared' ? 'Shared' : 'Private') + (selectedPrice != null ? ': ' + formatOptionalPrice(selectedPrice) : '') + '</span>'
                : '';
            const qtyInput = selectedType === 'private'
                ? '<span class="d-inline-flex align-items-center gap-1 me-1"><input type="number" min="1" step="1" class="form-control form-control-sm transfer-vehicle-qty-input" style="width:62px;" data-list="' + containerId + '" data-idx="' + idx + '" value="' + qty + '"><span class="fw-semibold text-muted">×</span></span>'
                : '';
            el.append(`
                <div class="d-flex align-items-center justify-content-between border rounded px-2 py-1 mb-1 bg-white small">
                    <span>
                        ${qtyInput}
                        ${escapeHtml(v.vehicle_name)}${v.vehicle_type ? ' (' + escapeHtml(v.vehicle_type) + ')' : ''}
                        ${selectedBadge}
                    </span>
                    <button type="button" class="btn btn-link btn-sm p-0 text-danger transfer-vehicle-remove" data-list="${containerId}" data-idx="${idx}" title="Remove"><i class="ri-close-line"></i></button>
                </div>
            `);
        });
        updateDefinitionTotalsAndMarkup();
    }

    function updateArrivalTransferPriceInput() {
        const val = $('#arrival-vehicle-select').val();
        const selectedType = $('input[name="arrival_transfer_type"]:checked').val() || 'private';
        if (!val) {
            $('#arrival-transfer-price-input').val('0');
            return;
        }
        const v = arrivalVehiclesByZone.find(function(x) { return x.vehicle_id == val; });
        const pricing = formatTransferVehiclePricing(v || {});
        const p = selectedType === 'shared' ? pricing.shared_price : pricing.private_price;
        $('#arrival-transfer-price-input').val((p || 0).toFixed(2));
    }

    function updateDepartureTransferPriceInput() {
        const val = $('#departure-vehicle-select').val();
        const selectedType = $('input[name="departure_transfer_type"]:checked').val() || 'private';
        if (!val) {
            $('#departure-transfer-price-input').val('0');
            return;
        }
        const v = departureVehiclesByZone.find(function(x) { return x.vehicle_id == val; });
        const pricing = formatTransferVehiclePricing(v || {});
        const p = selectedType === 'shared' ? pricing.shared_price : pricing.private_price;
        $('#departure-transfer-price-input').val((p || 0).toFixed(2));
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

    $(document).on('input change', '.transfer-vehicle-qty-input', function() {
        const listName = $(this).data('list');
        const idx = parseInt($(this).data('idx'), 10);
        const qtyVal = Math.max(1, parseInt($(this).val(), 10) || 1);
        const list = listName === 'arrival-chosen-vehicles' ? arrivalChosenVehicles : departureChosenVehicles;
        if (!Array.isArray(list) || !list[idx]) return;
        const item = list[idx];
        if ((item.selected_transfer_type || '').toLowerCase() !== 'private') return;
        const unitPrice = item.unit_price != null && !isNaN(parseFloat(item.unit_price))
            ? parseFloat(item.unit_price)
            : (item.selected_price != null && !isNaN(parseFloat(item.selected_price)) ? parseFloat(item.selected_price) : 0);
        item.qty = qtyVal;
        item.unit_price = unitPrice;
        item.selected_price = unitPrice * qtyVal;
        renderChosenVehiclesList(list, listName);
        if (listName === 'arrival-chosen-vehicles') {
            $('#arrival-vehicles-hidden').val(JSON.stringify(arrivalChosenVehicles));
        } else {
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
                    const pricing = formatTransferVehiclePricing(v);
                    const name = (v.vehicle_name || v.name) + (v.vehicle_type ? ' (' + v.vehicle_type + ')' : '') + ' - ' + pricing.label;
                    sel.append(new Option(name, v.vehicle_id));
                });
                $('#arrival-vehicle-select-wrap').show();
                $('#arrival-transfer-private').prop('checked', true);
                updateArrivalTransferPriceInput();
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
                    const pricing = formatTransferVehiclePricing(v);
                    const name = (v.vehicle_name || v.name) + (v.vehicle_type ? ' (' + v.vehicle_type + ')' : '') + ' - ' + pricing.label;
                    sel.append(new Option(name, v.vehicle_id));
                });
                $('#departure-vehicle-select-wrap').show();
                $('#departure-transfer-private').prop('checked', true);
                updateDepartureTransferPriceInput();
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
        const pricing = formatTransferVehiclePricing(v);
        const selectedType = $('input[name="arrival_transfer_type"]:checked').val() || 'private';
        const manualPrice = parseFloat($('#arrival-transfer-price-input').val());
        const selectedPrice = !isNaN(manualPrice) ? manualPrice : (selectedType === 'shared' ? pricing.shared_price : pricing.private_price);
        arrivalChosenVehicles.push({
            vehicle_id: v.vehicle_id,
            vehicle_name: v.vehicle_name || v.name,
            vehicle_type: v.vehicle_type,
            private_price: pricing.private_price,
            shared_price: pricing.shared_price,
            selected_transfer_type: selectedType,
            unit_price: selectedPrice,
            qty: selectedType === 'private' ? 1 : 1,
            selected_price: selectedPrice
        });
        renderChosenVehiclesList(arrivalChosenVehicles, 'arrival-chosen-vehicles');
        $('#arrival-vehicles-hidden').val(JSON.stringify(arrivalChosenVehicles));
    });

    $('#departure-add-vehicle-btn').on('click', function() {
        const val = $('#departure-vehicle-select').val();
        if (!val) return;
        const v = departureVehiclesByZone.find(function(x) { return x.vehicle_id == val; });
        if (!v) return;
        if (departureChosenVehicles.some(function(x) { return x.vehicle_id == val; })) return;
        const pricing = formatTransferVehiclePricing(v);
        const selectedType = $('input[name="departure_transfer_type"]:checked').val() || 'private';
        const manualPrice = parseFloat($('#departure-transfer-price-input').val());
        const selectedPrice = !isNaN(manualPrice) ? manualPrice : (selectedType === 'shared' ? pricing.shared_price : pricing.private_price);
        departureChosenVehicles.push({
            vehicle_id: v.vehicle_id,
            vehicle_name: v.vehicle_name || v.name,
            vehicle_type: v.vehicle_type,
            private_price: pricing.private_price,
            shared_price: pricing.shared_price,
            selected_transfer_type: selectedType,
            unit_price: selectedPrice,
            qty: selectedType === 'private' ? 1 : 1,
            selected_price: selectedPrice
        });
        renderChosenVehiclesList(departureChosenVehicles, 'departure-chosen-vehicles');
        $('#departure-vehicles-hidden').val(JSON.stringify(departureChosenVehicles));
    });

    $('#arrival-vehicle-select').on('change', updateArrivalTransferPriceInput);
    $('input[name="arrival_transfer_type"]').on('change', updateArrivalTransferPriceInput);
    $('#departure-vehicle-select').on('change', updateDepartureTransferPriceInput);
    $('input[name="departure_transfer_type"]').on('change', updateDepartureTransferPriceInput);
    $('#definition-markup-type, #definition-markup-amount').on('change input', updateDefinitionTotalsAndMarkup);

    // Form submit: build full JSON for selected_hotels, selected_attractions, selected_restaurants
    $('#package-definition-form').on('submit', function(e) {
        if (!$(this).find('#main_image')[0].files || !$(this).find('#main_image')[0].files.length) {
            $('#main-image-required-msg').removeClass('d-none');
            e.preventDefault();
            return false;
        }
        $('#main-image-required-msg').addClass('d-none');
        updateDefinitionTotalsAndMarkup();
        renderOptionalServicesSummary();
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
                addon: !!h.addon,
                optional_price: h.optional_price != null && h.optional_price !== '' ? parseFloat(h.optional_price) : null,
                addon_price: h.addon_price != null && h.addon_price !== '' ? parseFloat(h.addon_price) : null,
                base_price: h.base_price != null && h.base_price !== '' ? parseFloat(h.base_price) : null
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
                addon: !!a.addon,
                optional_price: a.optional_price != null && a.optional_price !== '' ? parseFloat(a.optional_price) : null,
                addon_price: a.addon_price != null && a.addon_price !== '' ? parseFloat(a.addon_price) : null,
                base_price: a.base_price != null && a.base_price !== '' ? parseFloat(a.base_price) : null,
                ticket_id: a.ticket_id || null,
                ticket_name: a.ticket_name || null,
                ticket: a.ticket || null,
                guide: a.guide || null,
                transfer: !!a.transfer,
                vehicle_id: a.vehicle_id || null,
                vehicle_name: a.vehicle_name || null,
                transfer_price: a.transfer_price != null && a.transfer_price !== '' ? parseFloat(a.transfer_price) : 0,
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
                addon: !!r.addon,
                optional_price: r.optional_price != null && r.optional_price !== '' ? parseFloat(r.optional_price) : null,
                addon_price: r.addon_price != null && r.addon_price !== '' ? parseFloat(r.addon_price) : null,
                base_price: r.base_price != null && r.base_price !== '' ? parseFloat(r.base_price) : null,
                selected_meals: r.selected_meals || [],
                meal_type: r.meal_type || null,
                meal_type_label: r.meal_type_label || null,
                adult_price: r.meal_adult_price != null && r.meal_adult_price !== '' ? parseFloat(r.meal_adult_price) : null,
                child_price: r.meal_child_price != null && r.meal_child_price !== '' ? parseFloat(r.meal_child_price) : null,
                transfer: !!r.transfer,
                vehicle_id: r.vehicle_id || null,
                vehicle_name: r.vehicle_name || null,
                transfer_price: r.transfer_price != null && r.transfer_price !== '' ? parseFloat(r.transfer_price) : 0,
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
                addon: !!g.addon,
                optional_price: g.optional_price != null && g.optional_price !== '' ? parseFloat(g.optional_price) : null,
                addon_price: g.addon_price != null && g.addon_price !== '' ? parseFloat(g.addon_price) : null,
                base_price: g.base_price != null && g.base_price !== '' ? parseFloat(g.base_price) : null,
                duration_key: g.duration_key || 'hourly',
                duration_label: g.duration_label || ''
            };
        });
        $('#definition-independent-guide-input').val(JSON.stringify(selectedGuidesPayload));
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

    renderOptionalServicesSummary();
    updateDefinitionTotalsAndMarkup();
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


