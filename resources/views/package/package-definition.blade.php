@extends('layouts.layout')
@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        @php
            $isEdit = isset($mode) && $mode === 'edit' && !empty($package);
            $initial = isset($initialDefinition) ? $initialDefinition : null;
        @endphp

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="ri-file-list-3-line me-2 text-primary"></i>{{ $isEdit ? 'Edit Package Definition' : 'Create Package Definition' }}
                </h4>
                <p class="text-muted mb-0">
                    {{ $isEdit ? 'Update package definition services and pricing' : 'Define package services without day-wise itinerary' }}
                </p>
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

        <form action="{{ $isEdit ? route('packages.definition.update', ['package_id' => Crypt::encrypt($package->package_id)]) : route('packages.definition.store') }}"
              method="POST" enctype="multipart/form-data" id="package-definition-form">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

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
                                   name="title" value="{{ old('title', $isEdit ? ($package->title ?? '') : '') }}" required placeholder="e.g., Singapore Explorer">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Package Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="start_date" value="{{ old('start_date', $isEdit ? optional($package->start_date)->format('Y-m-d') : '') }}" required min="{{ date('Y-m-d') }}" id="start-date-input">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Package Expiry Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="expiry_date" value="{{ old('expiry_date', $isEdit ? optional($package->expire_date)->format('Y-m-d') : '') }}" required min="{{ date('Y-m-d') }}" id="expiry-date-input">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tour Duration (Days) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('duration_days') is-invalid @enderror"
                                   name="duration_days" value="{{ old('duration_days', $isEdit ? ($package->duration_days ?? 1) : '') }}" min="1" required placeholder="e.g. 3">
                            @error('duration_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Country/Destination <span class="text-danger">*</span></label>
                            <select class="form-select w-100 @error('destination') is-invalid @enderror" id="country-select" name="destination" required>
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->name }}" {{ old('destination', $isEdit ? ($package->destination ?? '') : '') == $country->name ? 'selected' : '' }}>{{ $country->name }}</option>
                                @endforeach
                            </select>
                            @error('destination')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <select class="form-select w-100 @error('city') is-invalid @enderror" id="city-select" name="city[]" required multiple disabled>
                                <option value="">Select Country First</option>
                            </select>
                            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select w-100 @error('category') is-invalid @enderror" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Adventure" {{ old('category', $isEdit ? ($package->category ?? '') : '') == 'Adventure' ? 'selected' : '' }}>Adventure</option>
                                <option value="Cultural" {{ old('category', $isEdit ? ($package->category ?? '') : '') == 'Cultural' ? 'selected' : '' }}>Cultural</option>
                                <option value="City Tour" {{ old('category', $isEdit ? ($package->category ?? '') : '') == 'City Tour' ? 'selected' : '' }}>City Tour</option>
                                <option value="Beach" {{ old('category', $isEdit ? ($package->category ?? '') : '') == 'Beach' ? 'selected' : '' }}>Beach</option>
                                <option value="Heritage" {{ old('category', $isEdit ? ($package->category ?? '') : '') == 'Heritage' ? 'selected' : '' }}>Heritage</option>
                                <option value="Food & Culinary" {{ old('category', $isEdit ? ($package->category ?? '') : '') == 'Food & Culinary' ? 'selected' : '' }}>Food & Culinary</option>
                            </select>
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        
                        <!-- <div class="col-md-3">
                            <label class="form-label">Child Max Age</label>
                            <input type="number" class="form-control" name="child_max_age" value="{{ old('child_max_age') }}" min="1" id="child-max-age-input">
                        </div> -->
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
                                        @if($isEdit && !empty($package->main_image))
                                            <div class="small text-muted mt-1">Current main image:</div>
                                            <div class="mb-2">
                                                <img src="{{ $package->main_image }}" alt="Current main image" style="max-height:100px;border-radius:8px;">
                                            </div>
                                        @endif
                                        <div id="main-image-preview-container" class="mt-2"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Gallery Images</label>
                                        <div id="gallery-drop-area" class="form-control" style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px; cursor: pointer;">
                                            Drag & Drop or click to upload.
                                            <input type="file" id="gallery_images" name="gallery_images[]" accept="image/*" multiple style="display: none;">
                                        </div>
                                        @if($isEdit && !empty($package->gallery_images) && is_array($package->gallery_images) && count($package->gallery_images))
                                            <div class="small text-muted mt-1">Current gallery images:</div>
                                            <div class="mb-2">
                                                @foreach($package->gallery_images as $img)
                                                    <img src="{{ $img }}" alt="Gallery image" style="max-height:100px;border-radius:8px;margin:0 8px 8px 0;">
                                                @endforeach
                                            </div>
                                        @endif
                                        <div id="gallery-preview-container" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3" placeholder="Brief description...">{{ old('description', $isEdit ? ($package->description ?? '') : '') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-light py-2">
                    <h5 class="mb-0"><i class="ri-calendar-event-line me-2 text-primary"></i>Day-wise City Planner</h5>
                </div>
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <p class="text-muted small mb-0">Add city plans (city + day range). Services will open for selected city plan.</p>
                        <button type="button" class="btn btn-sm btn-primary" id="definition-add-city-plan-btn">
                            <i class="ri-add-line me-1"></i>Add City Plan
                        </button>
                    </div>
                    <div id="definition-city-plan-list" class="row g-2"></div>
                    <input type="hidden" name="day_city_plan" id="definition-day-city-plan" value="[]">
                </div>
            </div>

            <!-- Hotels & Attractions: two side-by-side boxes -->
            <div class="card mb-4 definition-service-card">
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
                                                <input type="number" class="form-control form-control-sm" id="definition-room-type-qty" min="1" value="1" readonly>
                                            </div>
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="definition-room-add-line"><i class="ri-add-line me-1"></i>Add room</button>
                                        </div>
                                        <div id="definition-pending-rooms" class="small mb-2"></div>
                                        <div class="d-flex align-items-end gap-2 flex-wrap">
                                            <div>
                                                <label class="form-label small mb-0">Nights</label>
                                                <input type="number" class="form-control form-control-sm" id="definition-nights" min="1" value="1" style="width: 70px;">
                                            </div>
                                            <div>
                                                <label class="form-label small mb-0">Start Day</label>
                                                <select class="form-select form-select-sm" id="definition-hotel-day" style="min-width: 120px;">
                                                    <option value="1">Day 1</option>
                                                </select>
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
                                        <label class="form-label small mb-1">Price</label>
                                        <input type="number" class="form-control form-control-sm w-100" id="definition-attraction-ticket-adult-price" value="" min="0" step="0.01" placeholder="0.00">
                                        <small class="text-muted">Editable (ticket/attraction base price).</small>
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
                                                        <input type="number" class="form-control form-control-sm" id="definition-attraction-config-guide-price" value="" min="0" step="0.01" placeholder="0.00">
                                                        <small class="text-muted">Editable.</small>
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
                                        <div class="d-flex align-items-end gap-2 mt-1 flex-wrap">
                                            <div>
                                                <label class="form-label small mb-0">Day</label>
                                                <select class="form-select form-select-sm" id="definition-attraction-day" style="min-width: 120px;">
                                                    <option value="1">Day 1</option>
                                                </select>
                                            </div>
                                            <button type="button" class="btn btn-success btn-sm" id="definition-attraction-add-btn"><i class="ri-add-line me-1"></i>Add</button>
                                        </div>
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
                    <input type="hidden" name="day_wise_itinerary" id="definition-day-wise-itinerary" value="[]">
                </div>
            </div>

            <!-- Restaurants & Guide: two side-by-side boxes (same as Hotels & Attractions) -->
            <div class="card mb-4 definition-service-card">
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
                                                <label class="form-label small mb-0">Price</label>
                                                <input type="number" class="form-control form-control-sm" id="definition-restaurant-meal-adult-price" value="" min="0" step="0.01" placeholder="0.00">
                                                <small class="text-muted">Editable.</small>
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
                                        <div class="d-flex align-items-end gap-2 mt-1 flex-wrap">
                                            <div>
                                                <label class="form-label small mb-0">Day</label>
                                                <select class="form-select form-select-sm" id="definition-restaurant-day" style="min-width: 120px;">
                                                    <option value="1">Day 1</option>
                                                </select>
                                            </div>
                                            <button type="button" class="btn btn-warning btn-sm" id="definition-restaurant-add-btn"><i class="ri-add-line me-1"></i>Add</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="pt-2 border-top mt-2">
                                    <h6 class="small fw-semibold mb-1">Chosen Restaurants</h6>
                                    <div id="definition-restaurants-empty" class="alert alert-info py-2 small mb-0"><i class="ri-information-line me-1"></i>Select restaurant, set transfer, then Add.</div>
                                    <div id="definition-restaurants-list" class="mt-1" style="display: none;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="selected_restaurants" id="definition-restaurants-input" value="[]">
                </div>
            </div>

            <!-- Transfers: Arrival (port → hotel) & Departure (hotel → port), search & choose vehicle -->
            <div class="card mb-4 definition-service-card">
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
                                            <div class="col-12 col-md-6">
                                                <label class="form-label small mb-0">Arrival Day</label>
                                                <select class="form-select form-select-sm" id="arrival-day-select">
                                                    <option value="1">Day 1</option>
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
                                            <div class="col-12 col-md-6">
                                                <label class="form-label small mb-0">Departure Day</label>
                                                <select class="form-select form-select-sm" id="departure-day-select">
                                                    <option value="">Last Day</option>
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

            <!-- City plan itinerary (2-by-2 grid) -->
            <div class="card mb-4">
                <div class="card-header bg-light py-2">
                    <h5 class="mb-0"><i class="ri-route-line me-2 text-primary"></i>City Plan Itinerary</h5>
                </div>
                <div class="card-body py-3">
                    <div id="definition-city-plan-service-sections" class="row g-3"></div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-light py-2">
                    <h5 class="mb-0"><i class="ri-money-dollar-circle-line me-2 text-success"></i>Price & Markup</h5>
                </div>
                <div class="card-body py-3">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Total Price</label>
                            <input type="number" class="form-control" id="definition-total-price" name="total_price" readonly value="0" step="0.01" min="0">
                            <small class="text-muted">Sum of hotels, attractions, restaurants, arrival and departure.</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Markup Type</label>
                            <select class="form-select" id="definition-markup-type" name="markup_type">
                                <option value="">Select type</option>
                                <option value="percentage">Percentage</option>
                                <option value="flat">Flat</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Markup Amount</label>
                            <input type="number" class="form-control" id="definition-markup-amount" name="markup_amount" value="0" step="0.01" min="0">
                            <small class="text-muted">For percentage, enter % value. For flat, enter amount.</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Final Price</label>
                            <input type="number" class="form-control" id="definition-final-price" readonly value="0" step="0.01" min="0">
                            <small class="text-muted">Total + markup (percentage of total or flat).</small>
                        </div>
                    </div>
                    <input type="hidden" name="price_data" id="definition-price-data" value="{}">
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
                            <textarea class="form-control" name="inclusions" rows="4" placeholder="What's included...">{{ old('inclusions', $isEdit ? ($package->inclusions ?? '') : '') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Exclusions</label>
                            <textarea class="form-control" name="exclusions" rows="4" placeholder="What's not included...">{{ old('exclusions', $isEdit ? ($package->exclusions ?? '') : '') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Terms & Conditions</label>
                            <textarea class="form-control" name="terms_conditions" rows="3" placeholder="Terms and conditions...">{{ old('terms_conditions', $isEdit ? ($package->terms_conditions ?? '') : '') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required style="max-width: 200px;">
                                <option value="1" {{ old('status', $isEdit ? (string) ($package->status ?? '1') : '') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', $isEdit ? (string) ($package->status ?? '1') : '') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary"><i class="ri-close-line me-1"></i>Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i>{{ $isEdit ? 'Update Package Definition' : 'Create Package Definition' }}</button>
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
    const restaurantMealsUrlTemplate = '{{ route("restaurant-meals", ["restaurantId" => "__RESTAURANT_ID__"]) }}';

    $('#country-select').select2();
    $('#city-select').select2({ placeholder: 'Select City(s)' });
    $('#definition-hotel-select').select2({ placeholder: 'Select hotel' });
    $('#definition-attraction-select').select2({ placeholder: 'Select attraction' });
    $('#definition-restaurant-select').select2({ placeholder: 'Select restaurant' });

    // Date validation
    $('#start-date-input').on('change', function() {
        const start = $(this).val();
        if (start) {
            const d = new Date(start);
            d.setDate(d.getDate() + 1);
            $('#expiry-date-input').attr('min', d.toISOString().split('T')[0]);
        }
    });

    function getSelectedCities() {
        const selected = $('#city-select').val();
        return Array.isArray(selected) ? selected.filter(Boolean) : (selected ? [selected] : []);
    }

    function getPrimarySelectedCity() {
        const cities = getSelectedCities();
        return cities.length ? cities[0] : '';
    }

    let definitionCityPlans = [];
    let activeCityPlanId = null;

    function toggleServiceCards(show) {
        $('.definition-service-card').toggle(!!show);
    }

    // Mount the single service editor into the active city-plan section.
    // This avoids duplicating IDs/select2 instances while still rendering per-plan containers.
    function mountServicesEditorToPlan(planId) {
        const editor = $('#definition-services-editor');
        const stash = $('#definition-services-editor-stash');
        if (!editor.length || !stash.length) return;

        if (!planId) {
            stash.append(editor);
            editor.addClass('d-none');
            return;
        }

        const mount = $('#definition-city-plan-service-sections').find('[data-editor-mount="' + planId + '"]');
        if (!mount.length) return;

        mount.append(editor);
        editor.removeClass('d-none');
    }

    function getActiveCityPlan() {
        return definitionCityPlans.find(function(p) { return p.id === activeCityPlanId; }) || null;
    }

    function getDayCityPlanMap() {
        const map = {};
        definitionCityPlans.forEach(function(plan) {
            const from = parseInt(plan.day_from, 10) || 1;
            const to = parseInt(plan.day_to, 10) || from;
            for (let day = from; day <= to; day++) {
                map[day] = plan.city || '';
            }
        });
        return map;
    }

    function normalizeCityPlansWithinDuration() {
        const totalDays = parseInt($('input[name="duration_days"]').val(), 10) > 0
            ? parseInt($('input[name="duration_days"]').val(), 10)
            : 1;
        definitionCityPlans = definitionCityPlans.map(function(plan) {
            let from = Math.min(Math.max(parseInt(plan.day_from, 10) || 1, 1), totalDays);
            let to = Math.min(Math.max(parseInt(plan.day_to, 10) || from, from), totalDays);
            return Object.assign({}, plan, { day_from: from, day_to: to });
        });
    }

    function renderDefinitionCityPlans() {
        normalizeCityPlansWithinDuration();
        const selectedCities = getSelectedCities();
        const wrap = $('#definition-city-plan-list');
        wrap.empty();

        if (!definitionCityPlans.length) {
            wrap.append('<div class="col-12"><div class="alert alert-info py-2 small mb-0"><i class="ri-information-line me-1"></i>Select multi city first, then click <strong>Add City Plan</strong>.</div></div>');
        }

        definitionCityPlans.forEach(function(plan) {
            const options = selectedCities.length
                ? selectedCities.map(function(city) {
                    return '<option value="' + escapeHtml(city) + '" ' + (plan.city === city ? 'selected' : '') + '>' + escapeHtml(city) + '</option>';
                }).join('')
                : '<option value="">Select city first</option>';

            wrap.append(
                '<div class="col-md-6">' +
                    '<div class="border rounded p-2 ' + (plan.id === activeCityPlanId ? 'border-primary bg-primary-subtle' : 'bg-light-subtle') + '">' +
                        '<div class="d-flex justify-content-between align-items-center mb-2">' +
                            '<span class="small fw-semibold">City Plan</span>' +
                            '<div class="d-flex gap-1">' +
                                '<button type="button" class="btn btn-sm ' + (plan.id === activeCityPlanId ? 'btn-primary' : 'btn-outline-primary') + ' definition-select-city-plan-btn" data-id="' + plan.id + '">Use</button>' +
                                '<button type="button" class="btn btn-sm btn-outline-danger definition-remove-city-plan-btn" data-id="' + plan.id + '"><i class="ri-delete-bin-line"></i></button>' +
                            '</div>' +
                        '</div>' +
                        '<div class="row g-2">' +
                            '<div class="col-md-5">' +
                                '<label class="form-label small mb-0">City</label>' +
                                '<select class="form-select form-select-sm definition-city-plan-city" data-id="' + plan.id + '">' + options + '</select>' +
                            '</div>' +
                            '<div class="col-md-3">' +
                                '<label class="form-label small mb-0">From(Day Start)</label>' +
                                '<input type="number" min="1" class="form-control form-control-sm definition-city-plan-from" data-id="' + plan.id + '" value="' + plan.day_from + '">' +
                            '</div>' +
                            '<div class="col-md-3">' +
                                '<label class="form-label small mb-0">To(Day End)</label>' +
                                '<input type="number" min="1" class="form-control form-control-sm definition-city-plan-to" data-id="' + plan.id + '" value="' + plan.day_to + '">' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>'
            );
        });

        $('#definition-day-city-plan').val(JSON.stringify(definitionCityPlans));
        toggleServiceCards(!!getActiveCityPlan());
        renderCityPlanServiceSections();
    }

    function renderCityPlanServiceSections() {
        const container = $('#definition-city-plan-service-sections');
        container.empty();
        if (!definitionCityPlans.length) {
            mountServicesEditorToPlan(null);
            return;
        }

        definitionCityPlans.forEach(function(plan) {
            const isActive = plan.id === activeCityPlanId;
            const collapseId = 'def-plan-editor-' + plan.id;
            container.append(
                '<div class="col-12 col-md-6">' +
                    '<div class="card h-100 border-' + (isActive ? 'primary' : 'secondary') + '">' +
                        '<div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">' +
                            '<h6 class="mb-0">' + escapeHtml(plan.city || 'City') + ' (Day ' + plan.day_from + ' - ' + plan.day_to + ')</h6>' +
                            '<div class="d-flex gap-2">' +
                                '<button type="button" class="btn btn-sm ' + (isActive ? 'btn-primary' : 'btn-outline-primary') + ' definition-select-city-plan-btn" data-id="' + plan.id + '">' +
                                    (isActive ? 'Editing' : 'Edit Services') +
                                '</button>' +
                                '<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#' + collapseId + '" aria-expanded="' + (isActive ? 'true' : 'false') + '">' +
                                    '<i class="ri-edit-2-line me-1"></i>Open Editor' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                        '<div class="card-body py-3">' +
                            '<div class="border rounded p-3 bg-light-subtle">' +
                                '<div data-itinerary="' + plan.id + '"></div>' +
                            '</div>' +
                        '</div>' +
                        '<div id="' + collapseId + '" class="collapse ' + (isActive ? 'show' : '') + '">' +
                            '<div class="card-body pt-0">' +
                                '<div data-editor-mount="' + plan.id + '"></div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>'
            );
        });

        // Mount single editor into active plan (or stash if none)
        if (activeCityPlanId) mountServicesEditorToPlan(activeCityPlanId);
        else mountServicesEditorToPlan(null);

        // Render itineraries for all city plans
        definitionCityPlans.forEach(function(p) { renderDayWiseItinerary(p.id); });
    }

    function loadServicesForActivePlan() {
        const plan = getActiveCityPlan();
        if (!plan || !plan.city) {
            return;
        }

        const city = plan.city;
        $.get(baseUrl + '/hotel-city/' + encodeURIComponent(city), function(response) {
            const hotels = Array.isArray(response) ? response : [];
            hotelsByCity = hotels;
            const sel = $('#definition-hotel-select');
            sel.empty().append('<option value="">Select Hotel</option>');
            hotels.forEach(function(h) {
                const opt = new Option(h.name, h.hotel_unique_id);
                $(opt).data('hotel-data', h);
                sel.append(opt);
            });
        });

        $.get(baseUrl + '/attractions/' + encodeURIComponent(city), function(response) {
            const attractions = Array.isArray(response) ? response : [];
            const sel = $('#definition-attraction-select');
            sel.empty().append('<option value="">Select Attraction</option>');
            attractions.forEach(function(a) {
                const opt = new Option(a.name, a.attraction_id);
                $(opt).data('attraction-data', { attraction_id: a.attraction_id, name: a.name, location: a.location, image: a.master_image || '', adult_price: a.adult_price, child_price: a.child_price });
                sel.append(opt);
            });
        });

        $.get(baseUrl + '/restaurants/' + encodeURIComponent(city), function(response) {
            const list = response.restaurants || response;
            const restaurants = Array.isArray(list) ? list : [];
            restaurantMealsByRestaurant = {};
            const sel = $('#definition-restaurant-select');
            sel.empty().append('<option value="">Select Restaurant</option>');
            restaurants.forEach(function(r) {
                sel.append(new Option(r.name, r.restaurant_id));
            });
        });

        $.get(baseUrl + '/guides/' + encodeURIComponent(city), function(response) {
            guidesByCity = Array.isArray(response) ? response : [];
        });

        $.get(baseUrl + '/get-transport/' + encodeURIComponent(city), function(response) {
            vehiclesByCity = Array.isArray(response) ? response : [];
        });

        loadTransferStateForActivePlan();
        renderDefinitionDaySelectors();
        refreshTransferHotelDropdowns();
    }

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
        $('#definition-rooms-wrapper').hide();
        $('#arrival-pickup-port').empty().append('<option value="">Select country first</option>');
        $('#departure-dropoff-port').empty().append('<option value="">Select country first</option>');
        portsByCountry = [];
        restaurantMealsByRestaurant = {};
        // In edit preload mode we should NOT wipe existing city plans / selections.
        if (!isEditPreloading) {
            definitionCityPlans = [];
            activeCityPlanId = null;
            renderDefinitionCityPlans();
        }
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
        });
        citySelect.prop('disabled', false);
        $.get(baseUrl + '/cities-by-country/' + encodeURIComponent(country), function(response) {
            citySelect.empty();
            response.forEach(function(c) { citySelect.append(new Option(c.name, c.name)); });
            if (!isEditPreloading) {
                citySelect.val(null).trigger('change');
            }
        });
    });

    let portsByCountry = [];
    let definitionHotels = [];
    let definitionAttractions = [];
    let definitionRestaurants = [];
    let restaurantMealsByRestaurant = {};
    let hotelsByCity = [];
    let vehiclesByCity = [];
    let guidesByCity = [];
    let transferStateByPlan = {};
    let isEditPreloading = false;

    function getPlanTransferState(planId) {
        if (!planId) return null;
        if (!transferStateByPlan[planId]) {
            transferStateByPlan[planId] = {
                arrival_enabled: false,
                departure_enabled: false,
                arrival_day: '',
                departure_day: '',
                arrival_pickup_port_id: '',
                arrival_dropoff_hotel_id: '',
                departure_pickup_hotel_id: '',
                departure_dropoff_port_id: '',
                arrival_vehicles: [],
                departure_vehicles: []
            };
        }
        return transferStateByPlan[planId];
    }

    function refreshTransferDayDropdowns() {
        const totalDays = getDurationDays();
        const activePlan = getActiveCityPlan();

        const arr = $('#arrival-day-select');
        const dep = $('#departure-day-select');
        if (!arr.length || !dep.length) return;

        // Default range = full tour, but if a plan is active use that plan range.
        const fromDay = activePlan ? Math.max(1, parseInt(activePlan.day_from, 10) || 1) : 1;
        const toDay = activePlan ? Math.min(totalDays, parseInt(activePlan.day_to, 10) || totalDays) : totalDays;

        const prevArr = parseInt(arr.val(), 10) || fromDay;
        const prevDep = parseInt(dep.val(), 10) || toDay;

        arr.empty();
        for (let d = fromDay; d <= toDay; d++) arr.append(new Option('Day ' + d, String(d)));
        if (!arr.find('option').length) arr.append(new Option('Day 1', '1'));

        dep.empty();
        for (let d = fromDay; d <= toDay; d++) dep.append(new Option('Day ' + d, String(d)));
        if (!dep.find('option').length) dep.append(new Option('Day ' + toDay, String(toDay)));

        arr.val(String(Math.min(Math.max(prevArr, fromDay), toDay)));
        dep.val(String(Math.min(Math.max(prevDep, fromDay), toDay)));
    }

    function persistTransferStateForActivePlan() {
        const activePlan = getActiveCityPlan();
        if (!activePlan) return;
        const state = getPlanTransferState(activePlan.id);
        if (!state) return;
        state.arrival_enabled = $('#arrival-pickup-def').is(':checked');
        state.departure_enabled = $('#departure-service-def').is(':checked');
        state.arrival_day = $('#arrival-day-select').val() || '';
        state.departure_day = $('#departure-day-select').val() || '';
        state.arrival_pickup_port_id = $('#arrival-pickup-port').val() || '';
        state.arrival_dropoff_hotel_id = $('#arrival-dropoff-hotel').val() || '';
        state.departure_pickup_hotel_id = $('#departure-pickup-hotel').val() || '';
        state.departure_dropoff_port_id = $('#departure-dropoff-port').val() || '';
        state.arrival_vehicles = Array.isArray(arrivalChosenVehicles) ? arrivalChosenVehicles.slice() : [];
        state.departure_vehicles = Array.isArray(departureChosenVehicles) ? departureChosenVehicles.slice() : [];
        transferStateByPlan[activePlan.id] = state;
        renderCityPlanServiceSections();
        refreshCityItinerary(activePlan.id);
    }

    function loadTransferStateForActivePlan() {
        const activePlan = getActiveCityPlan();
        if (!activePlan) {
            arrivalChosenVehicles = [];
            departureChosenVehicles = [];
            arrivalVehiclesByZone = [];
            departureVehiclesByZone = [];
            $('#arrival-vehicle-select-wrap').hide();
            $('#departure-vehicle-select-wrap').hide();
            $('#arrival-vehicle-select').empty().append('<option value="">Select vehicle</option>');
            $('#departure-vehicle-select').empty().append('<option value="">Select vehicle</option>');
            renderChosenVehiclesList(arrivalChosenVehicles, 'arrival-chosen-vehicles');
            renderChosenVehiclesList(departureChosenVehicles, 'departure-chosen-vehicles');
            return;
        }
        const state = getPlanTransferState(activePlan.id);
        $('#arrival-pickup-def').prop('checked', !!state.arrival_enabled);
        $('#departure-service-def').prop('checked', !!state.departure_enabled);
        $('#arrival-pickup-config').toggle(!!state.arrival_enabled);
        $('#departure-service-config').toggle(!!state.departure_enabled);
        refreshTransferDayDropdowns();
        if (state.arrival_day) $('#arrival-day-select').val(String(state.arrival_day));
        if (state.departure_day) $('#departure-day-select').val(String(state.departure_day));
        $('#arrival-pickup-port').val(state.arrival_pickup_port_id || '');
        $('#arrival-dropoff-hotel').val(state.arrival_dropoff_hotel_id || '');
        $('#departure-pickup-hotel').val(state.departure_pickup_hotel_id || '');
        $('#departure-dropoff-port').val(state.departure_dropoff_port_id || '');
        arrivalChosenVehicles = Array.isArray(state.arrival_vehicles) ? state.arrival_vehicles.slice() : [];
        departureChosenVehicles = Array.isArray(state.departure_vehicles) ? state.departure_vehicles.slice() : [];

        // Reset search-result UI per plan to avoid leaking previous plan results.
        // Only show the select-wrap if this plan already has chosen vehicles.
        arrivalVehiclesByZone = [];
        departureVehiclesByZone = [];
        $('#arrival-vehicle-select').empty().append('<option value="">Select vehicle</option>');
        $('#departure-vehicle-select').empty().append('<option value="">Select vehicle</option>');
        if (Array.isArray(arrivalChosenVehicles) && arrivalChosenVehicles.length) {
            $('#arrival-vehicle-select-wrap').show();
        } else {
            $('#arrival-vehicle-select-wrap').hide();
        }
        if (Array.isArray(departureChosenVehicles) && departureChosenVehicles.length) {
            $('#departure-vehicle-select-wrap').show();
        } else {
            $('#departure-vehicle-select-wrap').hide();
        }

        renderChosenVehiclesList(arrivalChosenVehicles, 'arrival-chosen-vehicles');
        renderChosenVehiclesList(departureChosenVehicles, 'departure-chosen-vehicles');
        renderCityPlanServiceSections();
    }

    // City multi-select controls available city plans; services load by active plan city.
    $('#city-select').on('change', function() {
        const cities = getSelectedCities();
        if (!cities.length) {
            definitionCityPlans = [];
            activeCityPlanId = null;
        } else {
            definitionCityPlans = definitionCityPlans.map(function(plan) {
                const nextCity = cities.includes(plan.city) ? plan.city : cities[0];
                return Object.assign({}, plan, { city: nextCity });
            });
        }
        renderDefinitionCityPlans();
        if (!cities.length) {
            $('#definition-hotel-select').empty().append('<option value="">Select City First</option>');
            $('#definition-attraction-select').empty().append('<option value="">Select City First</option>');
            $('#definition-restaurant-select').empty().append('<option value="">Select City First</option>');
            hotelsByCity = [];
            guidesByCity = [];
            vehiclesByCity = [];
            return;
        }
        // Do not auto-open services; user must click "Use" on a city plan.
    });

    $('#definition-add-city-plan-btn').on('click', function() {
        const cities = getSelectedCities();
        if (!cities.length) {
            alert('Please select at least one city first.');
            return;
        }
        const totalDays = parseInt($('input[name="duration_days"]').val(), 10) > 0
            ? parseInt($('input[name="duration_days"]').val(), 10)
            : 1;
        const lastTo = definitionCityPlans.length
            ? Math.max.apply(null, definitionCityPlans.map(function(p) { return parseInt(p.day_to, 10) || 1; }))
            : 0;
        const from = Math.min(lastTo + 1, totalDays);
        const to = from;
        const id = 'plan_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
        definitionCityPlans.push({ id: id, city: cities[0], day_from: from, day_to: to });
        // Auto-open services for the new plan, but reset editor UI so it doesn't keep stale
        // dropdown/config values from the previously active plan.
        activeCityPlanId = id;
        resetDefinitionHotelForm();
        $('#definition-attraction-config').hide();
        $('#definition-restaurant-config').hide();
        $('#definition-attraction-ticket-select').empty().append('<option value="">Select attraction first</option>');
        renderDefinitionCityPlans();
        renderDefinitionDaySelectors();
        loadServicesForActivePlan();
        renderChosenHotels();
        renderDefinitionAttractions();
        renderDefinitionRestaurants();
    });

    $(document).on('click', '.definition-select-city-plan-btn', function() {
        activeCityPlanId = $(this).data('id');
        // Reset editor UI so we don't carry over stale dropdown/config state between city plans.
        // Selected services are stored per-plan in arrays and will render correctly after switching.
        resetDefinitionHotelForm();
        $('#definition-attraction-config').hide();
        $('#definition-restaurant-config').hide();
        $('#definition-attraction-ticket-select').empty().append('<option value="">Select attraction first</option>');
        renderDefinitionCityPlans();
        renderDefinitionDaySelectors();
        loadServicesForActivePlan();
        renderChosenHotels();
        renderDefinitionAttractions();
        renderDefinitionRestaurants();
    });

    $(document).on('click', '.definition-remove-city-plan-btn', function() {
        const id = $(this).data('id');
        definitionCityPlans = definitionCityPlans.filter(function(p) { return p.id !== id; });
        if (activeCityPlanId === id) {
            activeCityPlanId = null;
        }
        renderDefinitionCityPlans();
        renderDefinitionDaySelectors();
        if (activeCityPlanId) loadServicesForActivePlan();
        renderChosenHotels();
        renderDefinitionAttractions();
        renderDefinitionRestaurants();
    });

    $(document).on('change', '.definition-city-plan-city, .definition-city-plan-from, .definition-city-plan-to', function() {
        const id = $(this).data('id');
        definitionCityPlans = definitionCityPlans.map(function(plan) {
            if (plan.id !== id) return plan;
            return Object.assign({}, plan, {
                city: $('.definition-city-plan-city[data-id="' + id + '"]').val() || '',
                day_from: parseInt($('.definition-city-plan-from[data-id="' + id + '"]').val(), 10) || 1,
                day_to: parseInt($('.definition-city-plan-to[data-id="' + id + '"]').val(), 10) || 1
            });
        });
        normalizeCityPlansWithinDuration();
        renderDefinitionCityPlans();
        if (activeCityPlanId === id) {
            loadServicesForActivePlan();
            renderDefinitionDaySelectors();
        }
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
                $(opt).attr('data-extra-bed-price', b.extra_bed_price != null ? b.extra_bed_price : 0);
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
        const extraBedPriceRaw = $('#definition-bed-type-select option:selected').attr('data-extra-bed-price');
        const extraBedPrice = extraBedPriceRaw != null && extraBedPriceRaw !== '' && !isNaN(parseFloat(extraBedPriceRaw))
            ? parseFloat(extraBedPriceRaw)
            : 0;
        const bedId = $('#definition-bed-type-select option:selected').attr('data-bed-id');
        const qty = parseInt($('#definition-room-type-qty').val(), 10) || 1;
        if (qty < 1) return;
        const selectedRoomObj = (definitionRoomTypesByHotel || []).find(function(x) { return (x.id || x.room_id) == roomTypeId; });
        const weekendPrice = selectedRoomObj && selectedRoomObj.weekend_price != null && selectedRoomObj.weekend_price !== ''
            ? parseFloat(selectedRoomObj.weekend_price)
            : null;
        const weekdayPrice = selectedRoomObj && selectedRoomObj.weekday_price != null && selectedRoomObj.weekday_price !== ''
            ? parseFloat(selectedRoomObj.weekday_price)
            : null;
        definitionPendingRooms.push({
            room_type_id: roomTypeId,
            room_type_name: roomTypeName,
            bed_type: bedType,
            extra_bed: extraBed,
            quantity: qty,
            extra_bed_type: extraBedType,
            extra_bed_price: extraBedPrice,
            bed_id: bedId,
            weekend_price: weekendPrice,
            weekday_price: weekdayPrice
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
        // Some packages select the same restaurant on multiple days.
        // Pricing rule: count each unique restaurant once for the package total.
        // - compulsory: take the MAX line price for that restaurant_id
        // - optional: take the MIN line price for that restaurant_id
        const compById = {};
        const optById = {};
        (definitionRestaurants || []).forEach(function(r) {
            if (r.addon === true) return;
            const rid = (r && (r.restaurant_id || r.id)) ? String(r.restaurant_id || r.id) : '';
            if (!rid) return;
            const p = restaurantLinePriceForTotal(r);
            if (r.optional === true) {
                if (optById[rid] == null) optById[rid] = p;
                else optById[rid] = Math.min(optById[rid], p);
            } else {
                if (compById[rid] == null) compById[rid] = p;
                else compById[rid] = Math.max(compById[rid], p);
            }
        });
        let compulsorySum = 0;
        Object.keys(compById).forEach(function(k) { compulsorySum += compById[k]; });
        const opts = Object.keys(optById).map(function(k) { return optById[k]; });
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

        // IMPORTANT: totals must include transfers across ALL city plans,
        // not only the currently active plan (arrivalChosenVehicles/departureChosenVehicles).
        const arrivalTotal = (definitionCityPlans || []).reduce(function(sum, plan) {
            const st = plan && plan.id ? getPlanTransferState(plan.id) : null;
            const vehicles = st && Array.isArray(st.arrival_vehicles) ? st.arrival_vehicles : [];
            const s = vehicles.reduce(function(acc, v) { return acc + numPriceVal(v && v.selected_price); }, 0);
            return sum + s;
        }, 0);
        const departureTotal = (definitionCityPlans || []).reduce(function(sum, plan) {
            const st = plan && plan.id ? getPlanTransferState(plan.id) : null;
            const vehicles = st && Array.isArray(st.departure_vehicles) ? st.departure_vehicles : [];
            const s = vehicles.reduce(function(acc, v) { return acc + numPriceVal(v && v.selected_price); }, 0);
            return sum + s;
        }, 0);

        const rawSubtotal = hotelsTotal + attractionsTotal + restaurantsTotal + arrivalTotal + departureTotal;
        // Round UP to the nearest multiple of 5 for display and persistence.
        const ceilToFive = function(n) {
            const num = Number(n);
            if (!isFinite(num) || num <= 0) return 0;
            return Math.ceil(num / 5) * 5;
        };
        const subtotal = ceilToFive(rawSubtotal);
        $('#definition-total-price').val(subtotal.toFixed(2));

        const markupTypeRaw = ($('#definition-markup-type').val() || '').toString().trim();
        const markupType = markupTypeRaw !== '' ? markupTypeRaw : null;
        const markupAmountNum = parseFloat($('#definition-markup-amount').val());
        const markupAmount = isNaN(markupAmountNum) ? 0 : markupAmountNum;

        // Final price rule (applied to the ceil-rounded total):
        //   flat       => final = total + markupAmount
        //   percentage => final = total + (total * markupAmount / 100)
        //   none       => final = total
        let finalPriceRaw = subtotal;
        if (markupType === 'flat') {
            finalPriceRaw = subtotal + markupAmount;
        } else if (markupType === 'percentage') {
            finalPriceRaw = subtotal + (subtotal * markupAmount / 100);
        }
        const finalPrice = ceilToFive(finalPriceRaw);
        $('#definition-final-price').val(finalPrice.toFixed(2));

        const priceData = {
            total_price: parseFloat(subtotal.toFixed(2)),
            markup_type: markupType,
            markup_amount: markupType ? parseFloat(markupAmount.toFixed(2)) : null,
            final_price: parseFloat(finalPrice.toFixed(2))
        };
        $('#definition-price-data').val(JSON.stringify(priceData));
    }
    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
    function normalizeWeekendDays(val) {
        if (Array.isArray(val)) {
            return val.map(function(day) { return (day == null ? '' : String(day)).trim(); }).filter(Boolean);
        }
        if (typeof val === 'string') {
            const trimmed = val.trim();
            if (!trimmed) return [];
            try {
                const parsed = JSON.parse(trimmed);
                if (Array.isArray(parsed)) {
                    return parsed.map(function(day) { return (day == null ? '' : String(day)).trim(); }).filter(Boolean);
                }
            } catch (e) {}
            return trimmed.split(',').map(function(day) { return day.trim(); }).filter(Boolean);
        }
        return [];
    }

    function getDurationDays() {
        const raw = parseInt($('input[name="duration_days"]').val(), 10);
        return !isNaN(raw) && raw > 0 ? raw : 1;
    }

    function clampDay(day) {
        const total = getDurationDays();
        const parsed = parseInt(day, 10) || 1;
        return Math.min(Math.max(parsed, 1), total);
    }

    // ---------------- Day-wise itinerary (city-plan scoped) ----------------
    function formatTransferVehiclesMeta(list) {
        const vehicles = Array.isArray(list) ? list : [];
        if (!vehicles.length) return '';
        return vehicles.map(function(v) {
            const name = v && (v.vehicle_name || v.name) ? String(v.vehicle_name || v.name) : 'Vehicle';
            const vType = v && v.vehicle_type ? String(v.vehicle_type) : '';
            const tRaw = v && v.selected_transfer_type ? String(v.selected_transfer_type) : '';
            const t = tRaw === 'shared' ? 'Shared' : (tRaw === 'private' ? 'Private' : '');
            const label = name + (vType ? ' (' + vType + ')' : '');
            return t ? (label + ' · ' + t) : label;
        }).join(', ');
    }

    function addServiceToDay(dayMap, day, service) {
        if (!dayMap[day]) dayMap[day] = [];
        const key = service && service.key
            ? service.key
            : ((service.type || 'service') + ':' + String(service.id || service.name || ''));
        if (dayMap[day].some(function(x) { return x.key === key; })) return;
        dayMap[day].push(Object.assign({ key: key }, service));
    }

    function removeServiceFromDay(dayMap, day, key) {
        if (!dayMap[day]) return;
        dayMap[day] = dayMap[day].filter(function(x) { return x.key !== key; });
    }

    function getItineraryServiceUi(service) {
        const t = service && service.type ? String(service.type) : '';
        const rawName = service && service.name ? String(service.name) : '';
        const stripPrefix = function(prefix) {
            const re = new RegExp('^' + prefix.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\s*', 'i');
            return rawName.replace(re, '').trim();
        };

        if (t === 'hotel') return { icon: 'ri-hotel-line', klass: 'hotel', short: 'H', label: stripPrefix('Hotel:') || 'Hotel' };
        if (t === 'attraction') return { icon: 'ri-map-pin-line', klass: 'attraction', short: 'A', label: stripPrefix('Attraction:') || 'Attraction' };
        if (t === 'restaurant') return { icon: 'ri-restaurant-line', klass: 'restaurant', short: 'R', label: stripPrefix('Restaurant:') || 'Restaurant' };
        if (t === 'arrival') return { icon: 'ri-flight-land-line', klass: 'arrival', short: 'Arr', label: 'Arrival' };
        if (t === 'departure') return { icon: 'ri-flight-takeoff-line', klass: 'departure', short: 'Dep', label: 'Departure' };
        return { icon: 'ri-checkbox-circle-line', klass: 'service', short: '', label: rawName || 'Service' };
    }

    function renderItineraryChips(services) {
        const list = Array.isArray(services) ? services : [];
        if (!list.length) {
            return '<span class="def-it-empty">No services</span>';
        }
        const max = 4;
        const chips = list.slice(0, max).map(function(s) {
            const ui = getItineraryServiceUi(s);
            return (
                '<span class="def-it-chip def-it-chip--' + ui.klass + '">' +
                    '<i class="' + ui.icon + '"></i>' +
                    '<span class="def-it-chip-t">' + escapeHtml(ui.label) + '</span>' +
                '</span>'
            );
        }).join('');
        const more = list.length > max
            ? ('<span class="def-it-chip def-it-chip--more">+' + (list.length - max) + '</span>')
            : '';
        return '<div class="def-it-chips">' + chips + more + '</div>';
    }

    function buildCityItinerary(planId) {
        const plan = definitionCityPlans.find(function(p) { return p.id === planId; }) || null;
        if (!plan) return [];
        const fromDay = Math.max(1, parseInt(plan.day_from, 10) || 1);
        const toDay = Math.min(getDurationDays(), parseInt(plan.day_to, 10) || fromDay);

        const dayMap = {};
        for (let d = fromDay; d <= toDay; d++) dayMap[d] = [];

        // Arrival / Departure (selectable day within plan range)
        const transfer = getPlanTransferState(planId) || {};
        if (transfer.arrival_enabled && Array.isArray(transfer.arrival_vehicles) && transfer.arrival_vehicles.length) {
            const arrivalDay = transfer.arrival_day ? parseInt(transfer.arrival_day, 10) : fromDay;
            const safeArrivalDay = Math.min(toDay, Math.max(fromDay, arrivalDay || fromDay));
            addServiceToDay(dayMap, safeArrivalDay, {
                type: 'arrival',
                id: planId + ':arrival',
                name: 'Arrival Pickup',
                meta: formatTransferVehiclesMeta(transfer.arrival_vehicles)
            });
        }
        if (transfer.departure_enabled && Array.isArray(transfer.departure_vehicles) && transfer.departure_vehicles.length) {
            const departureDay = transfer.departure_day ? parseInt(transfer.departure_day, 10) : toDay;
            const safeDepartureDay = Math.min(toDay, Math.max(fromDay, departureDay || toDay));
            addServiceToDay(dayMap, safeDepartureDay, {
                type: 'departure',
                id: planId + ':departure',
                name: 'Departure Transfer',
                meta: formatTransferVehiclesMeta(transfer.departure_vehicles)
            });
        }

        // Hotels repeat across their stay days
        (definitionHotels || [])
            .filter(function(h) { return h.city_plan_id === planId; })
            .forEach(function(h) {
                const start = Math.max(fromDay, parseInt(h.start_day, 10) || fromDay);
                const nights = Math.max(1, parseInt(h.nights, 10) || 1);
                const end = Math.min(toDay, start + nights - 1);
                for (let d = start; d <= end; d++) {
                    addServiceToDay(dayMap, d, {
                        type: 'hotel',
                        id: h.hotel_id || h.hotel_name,
                        name: 'Hotel: ' + (h.hotel_name || 'Hotel')
                    });
                }
            });

        // Attractions on selected day (with ticket/guide/transfer meta)
        (definitionAttractions || [])
            .filter(function(a) { return a.city_plan_id === planId; })
            .forEach(function(a) {
                const d = Math.min(toDay, Math.max(fromDay, parseInt(a.day, 10) || fromDay));
                const meta = [];
                if (a.ticket_name) meta.push('Ticket: ' + a.ticket_name);
                if (a.guide && a.guide.name) meta.push('Guide: ' + a.guide.name);
                if (a.transfer) {
                    meta.push('Transfer: ' + (a.transfer_type === 'shared' ? 'Shared' : 'Private'));
                    if (a.vehicle_name) meta.push('Vehicle: ' + a.vehicle_name);
                }
                addServiceToDay(dayMap, d, {
                    type: 'attraction',
                    id: a.attraction_id || a.name,
                    name: 'Attraction: ' + (a.name || 'Attraction'),
                    meta: meta.join(' · ')
                });
            });

        // Restaurants on selected day (with meal/transfer meta)
        (definitionRestaurants || [])
            .filter(function(r) { return r.city_plan_id === planId; })
            .forEach(function(r) {
                const d = Math.min(toDay, Math.max(fromDay, parseInt(r.day, 10) || fromDay));
                const meta = [];
                if (r.meal_type_label) meta.push(r.meal_type_label);
                if (r.transfer) {
                    meta.push('Transfer: ' + (r.transfer_type === 'shared' ? 'Shared' : 'Private'));
                    if (r.vehicle_name) meta.push('Vehicle: ' + r.vehicle_name);
                }
                addServiceToDay(dayMap, d, {
                    type: 'restaurant',
                    id: r.restaurant_id || r.restaurant_name,
                    name: 'Restaurant: ' + (r.restaurant_name || 'Restaurant'),
                    meta: meta.join(' · ')
                });
            });

        const days = [];
        for (let d = fromDay; d <= toDay; d++) {
            days.push({ day: d, services: dayMap[d] || [] });
        }
        return days;
    }

    function renderDayWiseItinerary(planId) {
        const wrap = $('[data-itinerary="' + planId + '"]');
        if (!wrap.length) return;
        const plan = definitionCityPlans.find(function(p) { return p.id === planId; }) || null;
        if (!plan) return;
        const days = buildCityItinerary(planId);

        const accordId = 'itinerary-acc-' + String(planId).replace(/[^a-zA-Z0-9_-]/g, '');
        const html = days.map(function(dayRow, idx) {
            const hasServices = dayRow.services && dayRow.services.length > 0;
            const collapseId = accordId + '-day-' + dayRow.day;
            const show = hasServices && idx === 0; // open first non-empty day only
            const chips = renderItineraryChips(dayRow.services);
            const badge = hasServices
                ? ('<span class="badge bg-primary-subtle text-primary border">' + dayRow.services.length + '</span>')
                : ('<span class="badge bg-light text-muted border">0</span>');

            const items = hasServices
                ? dayRow.services.map(function(s) {
                    const ui = getItineraryServiceUi(s);
                    const meta = s.meta ? ('<div class="def-it-meta">' + escapeHtml(s.meta) + '</div>') : '';
                    return '<div class="def-it-item def-it-item--' + ui.klass + '">' +
                        '<div class="def-it-name"><i class="' + ui.icon + ' me-1 text-muted"></i>' + escapeHtml(s.name) + '</div>' +
                        meta +
                    '</div>';
                }).join('')
                : '<div class="small text-muted">No services selected</div>';

            return (
                '<div class="accordion-item def-it-day">' +
                    '<h2 class="accordion-header" id="' + collapseId + '-h">' +
                        '<button class="accordion-button ' + (show ? '' : 'collapsed') + ' def-it-btn" type="button" data-bs-toggle="collapse" data-bs-target="#' + collapseId + '" aria-expanded="' + (show ? 'true' : 'false') + '" aria-controls="' + collapseId + '">' +
                            '<div class="def-it-btn-left">' +
                                '<span class="def-it-day-label">Day ' + dayRow.day + '</span>' +
                                '<span class="def-it-summary">' + chips + '</span>' +
                            '</div>' +
                            '<div class="def-it-btn-right">' + badge + '</div>' +
                        '</button>' +
                    '</h2>' +
                    '<div id="' + collapseId + '" class="accordion-collapse collapse ' + (show ? 'show' : '') + '" aria-labelledby="' + collapseId + '-h" data-bs-parent="#' + accordId + '">' +
                        '<div class="accordion-body def-it-body">' + items + '</div>' +
                    '</div>' +
                '</div>'
            );
        }).join('');

        wrap.html(
            '<div class="d-flex justify-content-between align-items-center mb-2">' +
                '<div class="fw-semibold">Day-wise Itinerary</div>' +
                '<div class="small text-muted">' + escapeHtml(plan.city || 'City') + ' (Day ' + plan.day_from + ' - ' + plan.day_to + ')</div>' +
            '</div>' +
            '<div class="def-itinerary-wrap">' +
                '<div class="accordion def-it-accordion" id="' + accordId + '">' +
                    html +
                '</div>' +
            '</div>'
        );
    }

    function refreshCityItinerary(planId) {
        if (!planId) return;
        renderDayWiseItinerary(planId);
    }

    function renderDefinitionDaySelectors() {
        const totalDays = getDurationDays();
        const activePlan = getActiveCityPlan();
        const startDay = activePlan ? Math.max(1, parseInt(activePlan.day_from, 10) || 1) : 1;
        const endDay = activePlan ? Math.min(totalDays, parseInt(activePlan.day_to, 10) || totalDays) : totalDays;
        const selectors = ['#definition-hotel-day', '#definition-attraction-day', '#definition-restaurant-day'];
        selectors.forEach(function(selector) {
            const el = $(selector);
            if (!el.length) return;
            const prev = parseInt(el.val(), 10) || startDay;
            el.empty();
            for (let day = startDay; day <= endDay; day++) {
                el.append(new Option('Day ' + day, String(day)));
            }
            if (!el.find('option').length) {
                el.append(new Option('Day 1', '1'));
            }
            const safeValue = Math.min(Math.max(prev, startDay), endDay);
            el.val(String(safeValue > 0 ? safeValue : startDay));
        });
    }

    function ensureActiveCityPlanForService() {
        const plan = getActiveCityPlan();
        if (!plan) {
            alert('Please add and select a city plan first.');
            return null;
        }
        if (!plan.city) {
            alert('Please select a city in the active city plan.');
            return null;
        }
        return plan;
    }

    function buildDefinitionDayWiseItineraryPayload() {
        const totalDays = getDurationDays();
        const cityPlan = getDayCityPlanMap();
        const dayWise = [];
        for (let day = 1; day <= totalDays; day++) {
            dayWise.push({
                day: day,
                label: 'Day ' + day,
                city: cityPlan[day] || '',
                arrival_pickup: 0,
                departure_service: 0,
                arrival: null,
                departure: null,
                hotels: [],
                attractions: [],
                restaurants: []
            });
        }

        definitionCityPlans.forEach(function(plan) {
            const state = getPlanTransferState(plan.id);
            if (!state) return;
            const fromDay = Math.max(1, parseInt(plan.day_from, 10) || 1);
            const toDay = Math.min(totalDays, parseInt(plan.day_to, 10) || fromDay);
            const arrivalDay = state.arrival_day ? parseInt(state.arrival_day, 10) : fromDay;
            const departureDay = state.departure_day ? parseInt(state.departure_day, 10) : toDay;
            const safeArrivalDay = Math.min(toDay, Math.max(fromDay, arrivalDay || fromDay));
            const safeDepartureDay = Math.min(toDay, Math.max(fromDay, departureDay || toDay));

            if (state.arrival_enabled && Array.isArray(state.arrival_vehicles) && state.arrival_vehicles.length && dayWise[safeArrivalDay - 1]) {
                dayWise[safeArrivalDay - 1].arrival_pickup = 1;
                dayWise[safeArrivalDay - 1].arrival_vehicles = state.arrival_vehicles || [];
                dayWise[safeArrivalDay - 1].arrival = {
                    city_plan_id: plan.id,
                    day: safeArrivalDay,
                    pickup_port_id: state.arrival_pickup_port_id || null,
                    dropoff_hotel_id: state.arrival_dropoff_hotel_id || null,
                    vehicles: state.arrival_vehicles || []
                };
            }
            if (state.departure_enabled && Array.isArray(state.departure_vehicles) && state.departure_vehicles.length && dayWise[safeDepartureDay - 1]) {
                dayWise[safeDepartureDay - 1].departure_service = 1;
                dayWise[safeDepartureDay - 1].departure_vehicles = state.departure_vehicles || [];
                dayWise[safeDepartureDay - 1].departure = {
                    city_plan_id: plan.id,
                    day: safeDepartureDay,
                    pickup_hotel_id: state.departure_pickup_hotel_id || null,
                    dropoff_port_id: state.departure_dropoff_port_id || null,
                    vehicles: state.departure_vehicles || []
                };
            }
        });

        definitionHotels.forEach(function(h) {
            const startDay = clampDay(h.start_day);
            const nights = parseInt(h.nights, 10) || 1;
            const endDay = Math.min(totalDays, startDay + Math.max(nights, 1) - 1);
            for (let day = startDay; day <= endDay; day++) {
                if (day >= 1 && day <= totalDays) {
                    dayWise[day - 1].hotels.push({
                        hotel_id: h.hotel_id,
                        hotel_name: h.hotel_name,
                        city_plan_id: h.city_plan_id || null,
                        city_plan_city: h.city_plan_city || null,
                        stay_start_day: startDay,
                        stay_end_day: endDay,
                        nights: nights,
                        rooms: Array.isArray(h.rooms) ? h.rooms : [],
                        weekend_days: Array.isArray(h.weekend_days) ? h.weekend_days : [],
                        compulsory: h.compulsory === true || (!h.optional && !h.addon),
                        optional: h.optional === true,
                        addon: h.addon === true,
                        optional_price: h.optional_price != null ? h.optional_price : '',
                        addon_price: h.addon_price != null ? h.addon_price : '',
                        base_price: h.base_price != null && h.base_price !== '' ? parseFloat(h.base_price) : 0
                    });
                }
            }
        });

        definitionAttractions.forEach(function(a) {
            const day = clampDay(a.day);
            if (day >= 1 && day <= totalDays) {
                dayWise[day - 1].attractions.push({
                    attraction_id: a.attraction_id,
                    name: a.name,
                    location: a.location || null,
                    city_plan_id: a.city_plan_id || null,
                    city_plan_city: a.city_plan_city || null,
                    day: day,
                    ticket_id: a.ticket_id || null,
                    ticket_name: a.ticket_name || null,
                    ticket: a.ticket || null,
                    guide: a.guide || null,
                    transfer: !!a.transfer,
                    transfer_type: a.transfer_type || null,
                    transfer_price: a.transfer_price != null && !isNaN(parseFloat(a.transfer_price)) ? parseFloat(a.transfer_price) : 0,
                    vehicle_id: a.vehicle_id || null,
                    vehicle_name: a.vehicle_name || null,
                    pickup_id: a.pickup_id || null,
                    pickup_name: a.pickup_name || null,
                    dropoff_id: a.dropoff_id || null,
                    dropoff_name: a.dropoff_name || null,
                    compulsory: a.compulsory === true || (!a.optional && !a.addon),
                    optional: a.optional === true,
                    addon: a.addon === true,
                    optional_price: a.optional_price != null ? a.optional_price : '',
                    addon_price: a.addon_price != null ? a.addon_price : '',
                    base_price: a.base_price != null && a.base_price !== '' ? parseFloat(a.base_price) : 0,
                    adult_price: a.adult_price != null && a.adult_price !== '' && !isNaN(parseFloat(a.adult_price)) ? parseFloat(a.adult_price) : null,
                    child_price: a.child_price != null && a.child_price !== '' && !isNaN(parseFloat(a.child_price)) ? parseFloat(a.child_price) : null
                });
            }
        });

        definitionRestaurants.forEach(function(r) {
            const day = clampDay(r.day);
            if (day >= 1 && day <= totalDays) {
                dayWise[day - 1].restaurants.push({
                    restaurant_id: r.restaurant_id,
                    restaurant_name: r.restaurant_name,
                    city_plan_id: r.city_plan_id || null,
                    city_plan_city: r.city_plan_city || null,
                    day: day,
                    selected_meals: Array.isArray(r.selected_meals) ? r.selected_meals : [],
                    meal_type: r.meal_type || null,
                    meal_type_label: r.meal_type_label || null,
                    meal_adult_price: r.meal_adult_price != null && !isNaN(parseFloat(r.meal_adult_price)) ? parseFloat(r.meal_adult_price) : null,
                    meal_child_price: r.meal_child_price != null && !isNaN(parseFloat(r.meal_child_price)) ? parseFloat(r.meal_child_price) : null,
                    transfer: !!r.transfer,
                    transfer_type: r.transfer_type || null,
                    transfer_price: r.transfer_price != null && !isNaN(parseFloat(r.transfer_price)) ? parseFloat(r.transfer_price) : 0,
                    vehicle_id: r.vehicle_id || null,
                    vehicle_name: r.vehicle_name || null,
                    pickup_id: r.pickup_id || null,
                    pickup_name: r.pickup_name || null,
                    dropoff_id: r.dropoff_id || null,
                    dropoff_name: r.dropoff_name || null,
                    compulsory: r.compulsory === true || (!r.optional && !r.addon),
                    optional: r.optional === true,
                    addon: r.addon === true,
                    optional_price: r.optional_price != null ? r.optional_price : '',
                    addon_price: r.addon_price != null ? r.addon_price : '',
                    base_price: r.base_price != null && r.base_price !== '' ? parseFloat(r.base_price) : 0
                });
            }
        });

        return dayWise;
    }

    // ---- Edit mode preload (existing package definition) ----
    const __initialDefinition = @json($initial);
    if (__initialDefinition && typeof __initialDefinition === 'object') {
        isEditPreloading = true;
        // Preload city plans
        if (Array.isArray(__initialDefinition.day_city_plan)) {
            definitionCityPlans = __initialDefinition.day_city_plan.map(function(p) {
                const fromRaw = p.day_from ?? p.city_day_from ?? p.from_day ?? p.from ?? p.start_day;
                const toRaw = p.day_to ?? p.city_day_to ?? p.to_day ?? p.to ?? p.end_day;
                const fromNum = parseInt(fromRaw, 10) || 1;
                const toNum = parseInt(toRaw, 10) || fromNum;
                return {
                    id: String(p.id || p.city_plan_id || p.plan_id || ''),
                    city: p.city || p.city_plan_city || '',
                    day_from: fromNum,
                    day_to: toNum,
                };
            }).filter(function(p) { return p.id && p.city; });
        }

        // If no plans were saved, derive plans from selected services (best effort)
        if (!definitionCityPlans.length) {
            const planMap = {};
            const consume = function(row) {
                if (!row || typeof row !== 'object') return;
                const pid = row.city_plan_id;
                const city = row.city_plan_city || row.city;
                const day = parseInt(row.day, 10);
                if (!pid || !city || !day) return;
                if (!planMap[pid]) planMap[pid] = { id: String(pid), city: String(city), day_from: day, day_to: day };
                planMap[pid].day_from = Math.min(planMap[pid].day_from, day);
                planMap[pid].day_to = Math.max(planMap[pid].day_to, day);
            };
            (Array.isArray(__initialDefinition.selected_hotels) ? __initialDefinition.selected_hotels : []).forEach(function(h) {
                // Hotels have strong day range hints: city_day_from/city_day_to or start_day + nights
                const pid = h && h.city_plan_id ? h.city_plan_id : null;
                const city = h && (h.city_plan_city || h.city) ? (h.city_plan_city || h.city) : null;
                if (!pid || !city) return;
                const from = parseInt(h.city_day_from, 10) || parseInt(h.start_day, 10) || 1;
                const nights = parseInt(h.nights, 10) || 1;
                const to = parseInt(h.city_day_to, 10) || (from + Math.max(1, nights) - 1);
                consume({ city_plan_id: pid, city_plan_city: city, day: from });
                consume({ city_plan_id: pid, city_plan_city: city, day: to });
            });
            (Array.isArray(__initialDefinition.selected_attractions) ? __initialDefinition.selected_attractions : []).forEach(consume);
            (Array.isArray(__initialDefinition.selected_restaurants) ? __initialDefinition.selected_restaurants : []).forEach(consume);
            definitionCityPlans = Object.values(planMap).sort(function(a,b){return a.day_from-b.day_from;});
        }

        // Preload services
        definitionHotels = Array.isArray(__initialDefinition.selected_hotels) ? __initialDefinition.selected_hotels : [];
        definitionAttractions = Array.isArray(__initialDefinition.selected_attractions) ? __initialDefinition.selected_attractions : [];
        definitionRestaurants = Array.isArray(__initialDefinition.selected_restaurants) ? __initialDefinition.selected_restaurants : [];

        // Hidden inputs for submit
        $('#definition-hotels-input').val(JSON.stringify(definitionHotels));
        $('#definition-attractions-input').val(JSON.stringify(definitionAttractions));
        $('#definition-restaurants-input').val(JSON.stringify(definitionRestaurants));
        if (Array.isArray(__initialDefinition.day_city_plan)) {
            $('#definition-day-city-plan').val(JSON.stringify(__initialDefinition.day_city_plan));
        }
        if (Array.isArray(__initialDefinition.day_wise_itinerary)) {
            $('#definition-day-wise-itinerary').val(JSON.stringify(__initialDefinition.day_wise_itinerary));
        }

        // Preload markup/price (best effort; totals will be recomputed)
        if (__initialDefinition.price_data && typeof __initialDefinition.price_data === 'object') {
            const mt = __initialDefinition.price_data.markup_type || '';
            const ma = __initialDefinition.price_data.markup_amount != null ? __initialDefinition.price_data.markup_amount : 0;
            if (mt) $('#definition-markup-type').val(mt);
            if (ma != null && !isNaN(parseFloat(ma))) $('#definition-markup-amount').val(parseFloat(ma));
        }

        // Preload arrival/departure into per-plan transfer state
        const ensureState = function(planId) {
            activeCityPlanId = activeCityPlanId || planId;
            return getPlanTransferState(planId);
        };
        const aItems = (__initialDefinition.arrival_data && Array.isArray(__initialDefinition.arrival_data.items)) ? __initialDefinition.arrival_data.items : [];
        aItems.forEach(function(item) {
            const pid = item.city_plan_id;
            if (!pid) return;
            const st = ensureState(pid);
            st.arrival_enabled = true;
            st.arrival_day = String(item.day || '');
            st.arrival_pickup_port_id = String(item.pickup_port_id || '');
            st.arrival_dropoff_hotel_id = String(item.dropoff_hotel_id || '');
            st.arrival_vehicles = Array.isArray(item.vehicles) ? item.vehicles : [];
        });
        const dItems = (__initialDefinition.departure_data && Array.isArray(__initialDefinition.departure_data.items)) ? __initialDefinition.departure_data.items : [];
        dItems.forEach(function(item) {
            const pid = item.city_plan_id;
            if (!pid) return;
            const st = ensureState(pid);
            st.departure_enabled = true;
            st.departure_day = String(item.day || '');
            st.departure_pickup_hotel_id = String(item.pickup_hotel_id || '');
            st.departure_dropoff_port_id = String(item.dropoff_port_id || '');
            st.departure_vehicles = Array.isArray(item.vehicles) ? item.vehicles : [];
        });

        // Ensure an active plan for rendering (first plan)
        if (!activeCityPlanId && definitionCityPlans.length) activeCityPlanId = definitionCityPlans[0].id;

        toggleServiceCards(!!definitionCityPlans.length);

        // Trigger country/city loading and then apply selected cities once options exist
        if (__initialDefinition.destination) {
            $('#country-select').val(__initialDefinition.destination).trigger('change');
        }
        const initialCities = Array.isArray(__initialDefinition.cities) ? __initialDefinition.cities : [];
        if (initialCities.length) {
            const applyCities = function() {
                const citySel = $('#city-select');
                const loaded = citySel.find('option').length > 0 && citySel.find('option[value="' + initialCities[0] + '"]').length > 0;
                if (!loaded) return false;
                citySel.val(initialCities).trigger('change');
                return true;
            };
            let tries = 0;
            const t = setInterval(function() {
                tries++;
                if (applyCities() || tries > 25) clearInterval(t);
            }, 200);
        }

        // After we’ve applied initial values, unlock normal behavior and recompute totals.
        setTimeout(function() {
            isEditPreloading = false;
            renderDefinitionDaySelectors();
            renderDefinitionCityPlans();
            renderDefinitionHotels();
            renderDefinitionAttractions();
            renderDefinitionRestaurants();
            updateDefinitionTotalsAndMarkup();
            renderCityPlanServiceSections();
            if (activeCityPlanId) {
                loadServicesForActivePlan();
                loadTransferStateForActivePlan();
            }
        }, 1200);
    }

        // In edit mode, the preload setTimeout will render everything.
        // For create mode, do the default initial render below.
    $('input[name="duration_days"]').on('change input', function() {
        renderDefinitionDaySelectors();
        renderDefinitionCityPlans();
    });

    // Add hotel entry to chosen list (uses pending rooms from select + Add room)
    $('#definition-hotel-add-btn').on('click', function() {
        const activePlan = ensureActiveCityPlanForService();
        if (!activePlan) return;
        const hotelId = $('#definition-hotel-select').val();
        const hotelName = $('#definition-hotel-select').find('option:selected').text();
        const hotelData = $('#definition-hotel-select').find('option:selected').data('hotel-data')
            || (hotelsByCity || []).find(function(h) { return (h.hotel_unique_id || h.id) == hotelId; })
            || null;
        const weekendDays = normalizeWeekendDays(hotelData ? hotelData.weekend_days : null);
        const nights = parseInt($('#definition-nights').val()) || 1;
        const startDay = clampDay($('#definition-hotel-day').val());
        const totalDays = getDurationDays();
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
        if (startDay > totalDays) {
            alert('Selected start day exceeds tour duration.');
            return;
        }
        const planFrom = parseInt(activePlan.day_from, 10) || 1;
        const planTo = parseInt(activePlan.day_to, 10) || planFrom;
        if (startDay < planFrom || startDay > planTo) {
            alert('Hotel start day must be inside selected city plan day range.');
            return;
        }
        const stayEnd = startDay + Math.max(nights, 1) - 1;
        if (stayEnd > planTo) {
            alert('Hotel nights exceed selected city plan day range.');
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
            weekend_days: weekendDays,
            nights,
            start_day: startDay,
            city_plan_id: activePlan.id,
            city_plan_city: activePlan.city,
            city_day_from: planFrom,
            city_day_to: planTo,
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
        const activePlan = getActiveCityPlan();
        const hotelsToRender = definitionHotels
            .map(function(entry, idx) { return { entry: entry, idx: idx }; })
            .filter(function(row) { return !activePlan || row.entry.city_plan_id === activePlan.id; });
        if (hotelsToRender.length === 0) {
            placeholder.show().html('<div class="alert alert-info py-3 mb-0 d-flex align-items-center"><i class="ri-information-line me-2 fs-5"></i><span>No hotels selected yet. Choose your hotels above and click <strong>Add</strong>.</span></div>');
            listEl.hide().empty();
            countEl.text('0');
            return;
        }
        normalizeHotelModesByFirst();
        updateDefinitionHotelsInput();
        const hotelTrack = getFirstServiceTrack(definitionHotels);
        placeholder.hide();
        countEl.text(hotelsToRender.length);
        listEl.show().empty();
        hotelsToRender.forEach(function(row) {
            const entry = row.entry;
            const idx = row.idx;
            const roomsText = entry.rooms.map(function(r) {
                return r.room_type_name + ' (' + (r.bed_type || 'N/A') + ') × ' + r.quantity;
            }).join(', ');
            const stayFromDay = parseInt(entry.start_day, 10) || 1;
            const stayUntilDay = stayFromDay + Math.max((parseInt(entry.nights, 10) || 1) - 1, 0);
            const isCompulsory = entry.compulsory === true || (!entry.optional && !entry.addon);
            const isOptional = entry.optional === true;
            const isAddon = entry.addon === true;
            const baseRaw = entry.base_price != null && entry.base_price !== '' ? entry.base_price : '';
            const baseP = baseRaw !== '' ? parseFloat(baseRaw) : '';
            const optPriceRaw = entry.optional_price != null && entry.optional_price !== '' ? entry.optional_price : '';
            const optPrice = optPriceRaw !== '' ? parseFloat(optPriceRaw) : '';
            const addonPriceRaw = entry.addon_price != null && entry.addon_price !== '' ? entry.addon_price : '';
            const addonPrice = addonPriceRaw !== '' ? parseFloat(addonPriceRaw) : '';
            const editableHotelPrice = baseP !== '' && !isNaN(baseP) ? baseP : 0;
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
            const radioCompulsory = showCompulsory ? `
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input chosen-hotel-mode" type="radio" name="hotel-mode-${idx}" id="hotel-comp-${idx}" data-idx="${idx}" value="compulsory" ${isCompulsory ? 'checked' : ''}>
                                    <label class="form-check-label small" for="hotel-comp-${idx}">Compulsory</label>
                                </div>` : '';
            // Temporarily hidden in UI:
            // const showOptional = showAllModes || hotelTrack === 'optional';
            // const radioOptional = showOptional ? `
            //                     <div class="form-check form-check-inline mb-0">
            //                         <input class="form-check-input chosen-hotel-mode" type="radio" name="hotel-mode-${idx}" id="hotel-opt-${idx}" data-idx="${idx}" value="optional" ${isOptional ? 'checked' : ''}>
            //                         <label class="form-check-label small" for="hotel-opt-${idx}">Optional</label>
            //                     </div>` : '';
            const radioOptional = '';
            // Temporarily hidden in UI:
            // const radioAddon = `
            //                     <div class="form-check form-check-inline mb-0">
            //                         <input class="form-check-input chosen-hotel-mode" type="radio" name="hotel-mode-${idx}" id="hotel-addon-${idx}" data-idx="${idx}" value="addon" ${isAddon ? 'checked' : ''}>
            //                         <label class="form-check-label small" for="hotel-addon-${idx}">Add-On</label>
            //                     </div>`;
            const radioAddon = '';
            listEl.append(`
                <div class="card mb-2 border shadow-sm chosen-hotel-card" data-idx="${idx}">
                    <div class="card-body py-2 px-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                                <span class="badge bg-primary-subtle text-primary rounded-circle p-2"><i class="ri-hotel-line"></i></span>
                                <div>
                                    <strong class="d-block">${escapeHtml(entry.hotel_name)}${priceDisplay}</strong>
                                    <small class="text-muted">Day ${stayFromDay} to Day ${stayUntilDay} · ${entry.nights} night(s) · ${escapeHtml(roomsText)}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="d-flex align-items-center gap-1">
                                    <label class="small text-muted mb-0">Price</label>
                                    <input type="number" min="0" step="0.01" class="form-control form-control-sm hotel-calculated-price-input" style="width: 120px;" data-idx="${idx}" value="${editableHotelPrice}">
                                </div>
                                ${radioCompulsory}
                                ${radioOptional}
                                ${radioAddon}
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
                // Always mirror the current base_price so the badge stays in sync
                if (mode === 'optional') {
                    h.optional_price = h.base_price != null && h.base_price !== '' ? h.base_price : '';
                    h.addon_price = '';
                } else if (mode === 'addon') {
                    h.addon_price = h.base_price != null && h.base_price !== '' ? h.base_price : '';
                    h.optional_price = '';
                } else {
                    h.optional_price = '';
                    h.addon_price = '';
                }
            }
            updateDefinitionHotelsInput();
            renderChosenHotels();
        });
        $('.hotel-calculated-price-input').on('input', function() {
            const idx = parseInt($(this).data('idx'), 10);
            const h = definitionHotels[idx];
            if (!h) return;
            const entered = parseFloat($(this).val());
            const nextBase = !isNaN(entered) && entered >= 0 ? entered : 0;
            h.base_price = nextBase;
            if (h.optional === true) {
                h.optional_price = nextBase;
                h.addon_price = '';
            } else if (h.addon === true) {
                h.addon_price = nextBase;
                h.optional_price = '';
            } else {
                h.optional_price = '';
                h.addon_price = '';
            }
            // Update the price badge in place so we keep input focus while typing
            const card = $(this).closest('.chosen-hotel-card');
            const strong = card.find('strong.d-block').first();
            strong.find('.optional-price-badge').remove();
            let badgeHtml = '';
            if (h.optional === true) {
                badgeHtml = '<span class="badge bg-primary ms-2 optional-price-badge">Optional: ' + formatOptionalPrice(nextBase) + '</span>';
            } else if (h.addon === true) {
                badgeHtml = '<span class="badge bg-secondary ms-2 optional-price-badge">Add-on: ' + formatOptionalPrice(nextBase) + '</span>';
            } else if (!isNaN(nextBase) && nextBase > 0) {
                badgeHtml = '<span class="badge bg-primary-subtle text-primary ms-2 optional-price-badge">Weekend: ' + formatOptionalPrice(nextBase) + '</span>';
            }
            if (badgeHtml) strong.append(badgeHtml);
            updateDefinitionHotelsInput();
            updateDefinitionTotalsAndMarkup();
            if (activePlan) refreshCityItinerary(activePlan.id);
        });
        $('.hotel-calculated-price-input').on('change', function() {
            // Full re-render on blur so every derived label stays consistent
            renderChosenHotels();
        });
        refreshTransferHotelDropdowns();
        updateDefinitionTotalsAndMarkup();
        renderCityPlanServiceSections();
        if (activePlan) refreshCityItinerary(activePlan.id);
    }

    function refreshTransferHotelDropdowns() {
        const arrivalDropoff = $('#arrival-dropoff-hotel'), departurePickup = $('#departure-pickup-hotel');
        arrivalDropoff.empty().append('<option value="">Add hotels first</option>');
        departurePickup.empty().append('<option value="">Add hotels first</option>');
        const activePlan = getActiveCityPlan();
        definitionHotels
        .filter(function(h) { return !activePlan || h.city_plan_id === activePlan.id; })
        .forEach(function(h) {
            arrivalDropoff.append(new Option(h.hotel_name, h.hotel_id));
            departurePickup.append(new Option(h.hotel_name, h.hotel_id));
        });
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
        ticketAdultPriceEl.val('');
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
        $('#definition-attraction-config-guide-price').val('');
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
        $('#definition-attraction-ticket-adult-price').val(adultPrice != null && !isNaN(adultPrice) ? adultPrice.toFixed(2) : '');
    });
    $('#definition-attraction-config-need-guide').on('change', function() {
        const checked = $(this).is(':checked');
        $('#definition-attraction-config-guide-wrap').toggle(checked);
        if (!checked) {
            $('#definition-attraction-config-guide').val('');
            $('#definition-attraction-config-guide-hour').val('');
            $('#definition-attraction-config-guide-price').val('');
        }
    });

    function updateAttractionGuidePrice() {
        const guideId = $('#definition-attraction-config-guide').val();
        const hourKey = $('#definition-attraction-config-guide-hour').val();
        const g = guideId ? guidesByCity.find(function(x) { return x.guide_id == guideId; }) : null;
        if (!g || !hourKey) {
            $('#definition-attraction-config-guide-price').val('');
            return;
        }
        const priceKey = hourKey === 'hourly' ? 'hourly_price' : (hourKey + '_price');
        const price = g[priceKey] != null && g[priceKey] !== '' ? parseFloat(g[priceKey]) : null;
        $('#definition-attraction-config-guide-price').val(price != null && !isNaN(price) ? price.toFixed(2) : '');
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
        const activePlan = ensureActiveCityPlanForService();
        if (!activePlan) return;
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
        const guidePriceAuto = (g && guidePriceKey && g[guidePriceKey] != null && g[guidePriceKey] !== '') ? parseFloat(g[guidePriceKey]) : null;
        const guidePriceInputRaw = $('#definition-attraction-config-guide-price').val();
        const guidePriceOverride = guidePriceInputRaw !== '' && !isNaN(parseFloat(guidePriceInputRaw)) ? parseFloat(guidePriceInputRaw) : null;
        const guidePrice = guidePriceOverride != null ? guidePriceOverride : guidePriceAuto;
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
        const day = clampDay($('#definition-attraction-day').val());
        const planFrom = parseInt(activePlan.day_from, 10) || 1;
        const planTo = parseInt(activePlan.day_to, 10) || planFrom;
        if (day < planFrom || day > planTo) {
            alert('Attraction day must be inside selected city plan day range.');
            return;
        }
        const pickupId = $('#definition-attraction-config-pickup').val();
        const pickupName = $('#definition-attraction-config-pickup').find('option:selected').text();
        const dropoffVal = $('#definition-attraction-config-dropoff').val();
        const dropoffName = $('#definition-attraction-config-dropoff').find('option:selected').text();
        let attrAdultPrice = '';
        const attrPriceInputRaw = $('#definition-attraction-ticket-adult-price').val();
        if (attrPriceInputRaw !== '' && !isNaN(parseFloat(attrPriceInputRaw))) {
            attrAdultPrice = parseFloat(attrPriceInputRaw);
        } else if (ticketData && ticketData.adult_price != null && ticketData.adult_price !== '') {
            attrAdultPrice = parseFloat(ticketData.adult_price);
        } else if (data.adult_price != null && data.adult_price !== '') {
            attrAdultPrice = parseFloat(data.adult_price);
        }
        let attrChildPrice = '';
        if (ticketData && ticketData.child_price != null && ticketData.child_price !== '') {
            attrChildPrice = parseFloat(ticketData.child_price);
        } else if (data.child_price != null && data.child_price !== '') {
            attrChildPrice = parseFloat(data.child_price);
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
            base_price: attrAdultPrice,
            adult_price: attrAdultPrice,
            child_price: attrChildPrice,
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
            day: day,
            city_plan_id: activePlan.id,
            city_plan_city: activePlan.city,
            pickup_id: pickupId || null,
            pickup_name: pickupName || null,
            dropoff_id: dropoffVal || null,
            dropoff_name: dropoffName || data.name
        });
        updateDefinitionAttractionsInput();
        renderDefinitionAttractions();
        $('#definition-attraction-select').val('').trigger('change');
        $('#definition-attraction-ticket-select').empty().append('<option value="">Select attraction first</option>');
        $('#definition-attraction-ticket-adult-price').val('');
        $('#definition-attraction-config').hide();
    });

    function renderDefinitionAttractions() {
        const emptyEl = $('#definition-attractions-empty');
        const container = $('#definition-attractions-list');
        container.empty();
        const activePlan = getActiveCityPlan();
        const attractionsToRender = definitionAttractions
            .map(function(entry, idx) { return { entry: entry, idx: idx }; })
            .filter(function(row) { return !activePlan || row.entry.city_plan_id === activePlan.id; });
        if (attractionsToRender.length === 0) {
            emptyEl.show();
            container.hide();
            return;
        }
        emptyEl.hide();
        container.show();
        attractionsToRender.forEach(function(row) {
            const a = row.entry;
            const idx = row.idx;
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
            parts.push('Day ' + (parseInt(a.day, 10) || 1));
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
            const editableAttractionPrice = totalPriceNum;
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
                                <div class="d-flex align-items-center gap-1">
                                    <label class="small text-muted mb-0">Price</label>
                                    <input type="number" min="0" step="0.01" class="form-control form-control-sm attraction-base-price-input" style="width: 120px;" data-idx="${idx}" value="${editableAttractionPrice}">
                                </div>
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
            updateDefinitionAttractionsInput();
            renderDefinitionAttractions();
            if (activePlan) refreshCityItinerary(activePlan.id);
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
                    a.optional_price = a.base_price != null && a.base_price !== '' ? a.base_price : '';
                    a.addon_price = '';
                } else if (mode === 'addon') {
                    a.addon_price = a.base_price != null && a.base_price !== '' ? a.base_price : '';
                    a.optional_price = '';
                } else {
                    a.optional_price = '';
                    a.addon_price = '';
                }
            }
            updateDefinitionAttractionsInput();
            renderDefinitionAttractions();
            if (activePlan) refreshCityItinerary(activePlan.id);
        });
        $('.attraction-base-price-input').on('input', function() {
            const idx = parseInt($(this).data('idx'), 10);
            const a = definitionAttractions[idx];
            if (!a) return;
            const entered = parseFloat($(this).val());
            const totalEdited = !isNaN(entered) && entered >= 0 ? entered : 0;
            const guidePart = numPriceVal(a && a.guide ? a.guide.price : 0);
            const transferPart = (a && a.transfer) ? numPriceVal(a.transfer_price) : 0;
            // Editable field is "service total", so base = total - (guide + transfer)
            const nextBase = Math.max(0, totalEdited - guidePart - transferPart);
            a.base_price = nextBase;
            a.adult_price = nextBase;
            if (a.optional === true) {
                a.optional_price = nextBase;
                a.addon_price = '';
            } else if (a.addon === true) {
                a.addon_price = nextBase;
                a.optional_price = '';
            } else {
                a.optional_price = '';
                a.addon_price = '';
            }
            // Update the visible total badges in place so the input keeps focus while typing
            const card = $(this).closest('.chosen-hotel-card');
            card.find('strong.d-block .badge.bg-success').html('Total: ' + formatOptionalPrice(totalEdited));
            const summaryTotalSpan = card.find('small.text-muted .fw-semibold').last();
            if (summaryTotalSpan.length) {
                summaryTotalSpan.html('Total: ' + formatOptionalPrice(totalEdited));
            }
            updateDefinitionAttractionsInput();
            updateDefinitionTotalsAndMarkup();
            if (activePlan) refreshCityItinerary(activePlan.id);
        });
        $('.attraction-base-price-input').on('change', function() {
            // Full re-render on blur so sub-summary lines (guide/transfer/adult) stay in sync
            renderDefinitionAttractions();
        });
        updateDefinitionTotalsAndMarkup();
        renderCityPlanServiceSections();
        if (activePlan) refreshCityItinerary(activePlan.id);
    }

    function updateDefinitionAttractionsInput() {
        $('#definition-attractions-input').val(JSON.stringify(definitionAttractions));
    }

    // Restaurant select: show config panel, load meal types, and populate vehicle
    $('#definition-restaurant-select').on('change', function() {
        const val = $(this).val();
        const configEl = $('#definition-restaurant-config');
        const mealsWrap = $('#definition-restaurant-meals-wrap');
        const mealTypeSel = $('#definition-restaurant-meal-type-select');
        const mealAdultPriceEl = $('#definition-restaurant-meal-adult-price');
        mealTypeSel.empty().append('<option value="">Select meal type</option>');
        mealAdultPriceEl.val('');
        if (!val) {
            configEl.hide();
            mealsWrap.hide();
            return;
        }
        configEl.show();
        const mealsUrl = restaurantMealsUrlTemplate.replace('__RESTAURANT_ID__', encodeURIComponent(val));
        $.get(mealsUrl, function(res) {
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
        $('#definition-restaurant-meal-adult-price').val(adult != null && !isNaN(adult) ? adult.toFixed(2) : '');
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
        const activePlan = ensureActiveCityPlanForService();
        if (!activePlan) return;
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
        const day = clampDay($('#definition-restaurant-day').val());
        const planFrom = parseInt(activePlan.day_from, 10) || 1;
        const planTo = parseInt(activePlan.day_to, 10) || planFrom;
        if (day < planFrom || day > planTo) {
            alert('Restaurant day must be inside selected city plan day range.');
            return;
        }
        const pickupId = $('#definition-restaurant-config-pickup').val();
        const pickupName = $('#definition-restaurant-config-pickup').find('option:selected').text();
        const dropoffVal = $('#definition-restaurant-config-dropoff').val();
        const dropoffName = $('#definition-restaurant-config-dropoff').find('option:selected').text();
        const mealPriceInputRaw = $('#definition-restaurant-meal-adult-price').val();
        const mealPriceOverride = mealPriceInputRaw !== '' && !isNaN(parseFloat(mealPriceInputRaw)) ? parseFloat(mealPriceInputRaw) : null;
        const restPrice = mealPriceOverride != null
            ? mealPriceOverride
            : (selectedMeal && selectedMeal.adult_price != null ? parseFloat(selectedMeal.adult_price || 0) : '');
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
            meal_adult_price: restPrice !== '' && restPrice != null && !isNaN(parseFloat(restPrice)) ? parseFloat(restPrice) : (selectedMeal && selectedMeal.adult_price != null ? parseFloat(selectedMeal.adult_price) : null),
            meal_child_price: selectedMeal && selectedMeal.child_price != null ? parseFloat(selectedMeal.child_price) : null,
            transfer: transfer,
            vehicle_id: v ? v.vehicle_id : null,
            vehicle_name: v ? v.name : null,
            transfer_price: transferPrice,
            transfer_type: transferType,
            day: day,
            city_plan_id: activePlan.id,
            city_plan_city: activePlan.city,
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
        const activePlan = getActiveCityPlan();
        const restaurantsToRender = definitionRestaurants
            .map(function(entry, idx) { return { entry: entry, idx: idx }; })
            .filter(function(row) { return !activePlan || row.entry.city_plan_id === activePlan.id; });
        if (restaurantsToRender.length === 0) {
            emptyEl.show();
            container.hide();
            return;
        }
        normalizeRestaurantModesByFirst();
        updateDefinitionRestaurantsInput();
        const restaurantTrack = getFirstServiceTrack(definitionRestaurants);
        emptyEl.hide();
        container.show();
        restaurantsToRender.forEach(function(row) {
            const r = row.entry;
            const idx = row.idx;
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
            parts.push('Day ' + (parseInt(r.day, 10) || 1));
            parts.push('<span class="fw-semibold">Total: ' + formatOptionalPrice(totalPriceNum) + '</span>');
            const summaryHtml = parts.length ? parts.join(' <span class="text-muted">·</span> ') : '—';
            const isCompulsory = r.compulsory === true || (!r.optional && !r.addon);
            const isOptional = r.optional === true;
            const isAddonR = r.addon === true;
            const totalBadge = '<span class="badge bg-warning text-dark ms-2">Total: ' + formatOptionalPrice(totalPriceNum) + '</span>';
            const editableRestaurantPrice = totalPriceNum;
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
                                <div class="d-flex align-items-center gap-1">
                                    <label class="small text-muted mb-0">Price</label>
                                    <input type="number" min="0" step="0.01" class="form-control form-control-sm restaurant-base-price-input" style="width: 120px;" data-idx="${idx}" value="${editableRestaurantPrice}">
                                </div>
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
            updateDefinitionRestaurantsInput();
            renderDefinitionRestaurants();
            if (activePlan) refreshCityItinerary(activePlan.id);
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
                    r.optional_price = r.base_price != null && r.base_price !== '' ? r.base_price : '';
                    r.addon_price = '';
                } else if (mode === 'addon') {
                    r.addon_price = r.base_price != null && r.base_price !== '' ? r.base_price : '';
                    r.optional_price = '';
                } else {
                    r.optional_price = '';
                    r.addon_price = '';
                }
            }
            updateDefinitionRestaurantsInput();
            renderDefinitionRestaurants();
            if (activePlan) refreshCityItinerary(activePlan.id);
        });
        $('.restaurant-base-price-input').on('input', function() {
            const idx = parseInt($(this).data('idx'), 10);
            const r = definitionRestaurants[idx];
            if (!r) return;
            const entered = parseFloat($(this).val());
            const totalEdited = !isNaN(entered) && entered >= 0 ? entered : 0;
            const transferPart = (r && r.transfer) ? numPriceVal(r.transfer_price) : 0;
            // Editable field is "service total", so base = total - transfer
            const nextBase = Math.max(0, totalEdited - transferPart);
            r.base_price = nextBase;
            r.meal_adult_price = nextBase;
            if (r.optional === true) {
                r.optional_price = nextBase;
                r.addon_price = '';
            } else if (r.addon === true) {
                r.addon_price = nextBase;
                r.optional_price = '';
            } else {
                r.optional_price = '';
                r.addon_price = '';
            }
            // Update visible total badges in place so the input keeps focus while typing
            const card = $(this).closest('.chosen-hotel-card');
            card.find('strong.d-block .badge.bg-warning').html('Total: ' + formatOptionalPrice(totalEdited));
            const summaryTotalSpan = card.find('small.text-muted .fw-semibold').last();
            if (summaryTotalSpan.length) {
                summaryTotalSpan.html('Total: ' + formatOptionalPrice(totalEdited));
            }
            updateDefinitionRestaurantsInput();
            updateDefinitionTotalsAndMarkup();
            if (activePlan) refreshCityItinerary(activePlan.id);
        });
        $('.restaurant-base-price-input').on('change', function() {
            // Full re-render on blur so the "Adult: ..." summary line stays in sync
            renderDefinitionRestaurants();
        });
        updateDefinitionTotalsAndMarkup();
        renderCityPlanServiceSections();
        if (activePlan) refreshCityItinerary(activePlan.id);
    }

    function updateDefinitionRestaurantsInput() {
        $('#definition-restaurants-input').val(JSON.stringify(definitionRestaurants));
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
        persistTransferStateForActivePlan();
    });
    $('#arrival-day-select').on('change', function() {
        persistTransferStateForActivePlan();
    });
    $('#arrival-pickup-port, #arrival-dropoff-hotel').on('change', function() {
        persistTransferStateForActivePlan();
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
        persistTransferStateForActivePlan();
    });
    $('#departure-day-select').on('change', function() {
        persistTransferStateForActivePlan();
    });
    $('#departure-pickup-hotel, #departure-dropoff-port').on('change', function() {
        persistTransferStateForActivePlan();
    });

    // Transfer vehicles: multiple selection, zone-based search
    let arrivalChosenVehicles = [];
    let departureChosenVehicles = [];
    let arrivalVehiclesByZone = [];
    let departureVehiclesByZone = [];
    const fetchVehiclesByZonesUrl = '{{ route("fetch-vehicles-by-zones") }}';

    // Normalize the `sharable` flag coming from the backend.
    //   1 -> private only, 2 -> shared only, 3 -> both. Anything else defaults to both.
    function parseSharable(v) {
        const n = v != null ? parseInt(v, 10) : NaN;
        if (n === 1 || n === 2 || n === 3) return n;
        return 3;
    }

    // Show / hide the Private / Shared radio labels for arrival or departure
    // based on the selected vehicle's `sharable` flag.
    function applyTransferSharableConstraints(section) {
        const listBySection = section === 'arrival' ? arrivalVehiclesByZone : departureVehiclesByZone;
        const val = $('#' + section + '-vehicle-select').val();
        const vehicle = val ? listBySection.find(function(x) { return x.vehicle_id == val; }) : null;
        const sharable = vehicle ? parseSharable(vehicle.sharable) : 3;

        const privateRadio = $('#' + section + '-transfer-private');
        const sharedRadio = $('#' + section + '-transfer-shared');
        const privateWrap = privateRadio.closest('.form-check');
        const sharedWrap = sharedRadio.closest('.form-check');

        if (sharable === 1) {
            privateWrap.show();
            sharedWrap.hide();
            privateRadio.prop('checked', true);
        } else if (sharable === 2) {
            privateWrap.hide();
            sharedWrap.show();
            sharedRadio.prop('checked', true);
        } else {
            privateWrap.show();
            sharedWrap.show();
            if (!privateRadio.is(':checked') && !sharedRadio.is(':checked')) {
                privateRadio.prop('checked', true);
            }
        }
    }

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
        persistTransferStateForActivePlan();
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
        persistTransferStateForActivePlan();
    });

    $('#arrival-search-vehicle-btn').on('click', function() {
        const activePlan = getActiveCityPlan();
        if (!activePlan) { alert('Please select a city plan first.'); return; }
        const portId = $('#arrival-pickup-port').val();
        const hotelId = $('#arrival-dropoff-hotel').val();
        const city = activePlan.city || getPrimarySelectedCity();
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
                applyTransferSharableConstraints('arrival');
                updateArrivalTransferPriceInput();
            },
            error: function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'No vehicles found for this route (zone).';
                alert(msg);
            }
        }).always(function() { btn.prop('disabled', false); });
    });

    $('#departure-search-vehicle-btn').on('click', function() {
        const activePlan = getActiveCityPlan();
        if (!activePlan) { alert('Please select a city plan first.'); return; }
        const hotelId = $('#departure-pickup-hotel').val();
        const portId = $('#departure-dropoff-port').val();
        const city = activePlan.city || getPrimarySelectedCity();
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
                applyTransferSharableConstraints('departure');
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
            sharable: parseSharable(v.sharable),
            seating_capacity: v.seating_capacity != null ? parseInt(v.seating_capacity, 10) || 0 : 0,
            city_tour_seating_capacity: v.city_tour_seating_capacity != null ? parseInt(v.city_tour_seating_capacity, 10) || 0 : 0,
            private_price: pricing.private_price,
            shared_price: pricing.shared_price,
            selected_transfer_type: selectedType,
            unit_price: selectedPrice,
            qty: 1,
            selected_price: selectedPrice
        });
        renderChosenVehiclesList(arrivalChosenVehicles, 'arrival-chosen-vehicles');
        $('#arrival-vehicles-hidden').val(JSON.stringify(arrivalChosenVehicles));
        persistTransferStateForActivePlan();
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
            sharable: parseSharable(v.sharable),
            seating_capacity: v.seating_capacity != null ? parseInt(v.seating_capacity, 10) || 0 : 0,
            city_tour_seating_capacity: v.city_tour_seating_capacity != null ? parseInt(v.city_tour_seating_capacity, 10) || 0 : 0,
            private_price: pricing.private_price,
            shared_price: pricing.shared_price,
            selected_transfer_type: selectedType,
            unit_price: selectedPrice,
            qty: 1,
            selected_price: selectedPrice
        });
        renderChosenVehiclesList(departureChosenVehicles, 'departure-chosen-vehicles');
        $('#departure-vehicles-hidden').val(JSON.stringify(departureChosenVehicles));
        persistTransferStateForActivePlan();
    });

    $('#arrival-vehicle-select').on('change', function() {
        applyTransferSharableConstraints('arrival');
        updateArrivalTransferPriceInput();
    });
    $('input[name="arrival_transfer_type"]').on('change', updateArrivalTransferPriceInput);
    $('#departure-vehicle-select').on('change', function() {
        applyTransferSharableConstraints('departure');
        updateDepartureTransferPriceInput();
    });
    $('input[name="departure_transfer_type"]').on('change', updateDepartureTransferPriceInput);
    $('#definition-markup-type, #definition-markup-amount').on('change input', updateDefinitionTotalsAndMarkup);

    // Form submit: build full JSON for selected_hotels, selected_attractions, selected_restaurants
    $('#package-definition-form').on('submit', function(e) {
        if (!definitionCityPlans.length) {
            alert('Please add at least one city plan before creating package.');
            e.preventDefault();
            return false;
        }
        persistTransferStateForActivePlan();
        if (!$(this).find('#main_image')[0].files || !$(this).find('#main_image')[0].files.length) {
            $('#main-image-required-msg').removeClass('d-none');
            e.preventDefault();
            return false;
        }
        $('#main-image-required-msg').addClass('d-none');
        updateDefinitionTotalsAndMarkup();
        // Hotels: full data with id/name for API compatibility
        const selectedHotelsPayload = definitionHotels.map(function(h) {
            const basePriceNum = h.base_price != null && h.base_price !== '' && !isNaN(parseFloat(h.base_price)) ? parseFloat(h.base_price) : 0;
            return {
                id: h.hotel_id,
                name: h.hotel_name,
                hotel_id: h.hotel_id,
                hotel_name: h.hotel_name,
                weekend_days: normalizeWeekendDays(h.weekend_days),
                nights: h.nights,
                rooms: (h.rooms || []).map(function(r) {
                    return Object.assign({}, r, {
                        weekday_price: r.weekday_price != null && r.weekday_price !== '' ? parseFloat(r.weekday_price) : null,
                        weekend_price: r.weekend_price != null && r.weekend_price !== '' ? parseFloat(r.weekend_price) : null
                    });
                }),
                compulsory: !!h.compulsory,
                optional: !!h.optional,
                addon: !!h.addon,
                optional_price: h.optional_price != null && h.optional_price !== '' ? parseFloat(h.optional_price) : null,
                addon_price: h.addon_price != null && h.addon_price !== '' ? parseFloat(h.addon_price) : null,
                base_price: basePriceNum,
                start_day: clampDay(h.start_day),
                city_plan_id: h.city_plan_id || null,
                city_plan_city: h.city_plan_city || null,
                city_day_from: h.city_day_from != null ? parseInt(h.city_day_from, 10) : null,
                city_day_to: h.city_day_to != null ? parseInt(h.city_day_to, 10) : null,
                // Hotels have no guide/transfer at definition time, so final price == base price
                final_price: basePriceNum,
                total_price: basePriceNum
            };
        });
        // Attractions: full data including guide, transfer, pickup/dropoff, compulsory
        const selectedAttractionsPayload = definitionAttractions.map(function(a) {
            const basePriceNum = a.base_price != null && a.base_price !== '' && !isNaN(parseFloat(a.base_price)) ? parseFloat(a.base_price) : 0;
            const guidePriceNum = a.guide && a.guide.price != null && a.guide.price !== '' && !isNaN(parseFloat(a.guide.price)) ? parseFloat(a.guide.price) : 0;
            const transferPriceNum = a.transfer && a.transfer_price != null && a.transfer_price !== '' && !isNaN(parseFloat(a.transfer_price)) ? parseFloat(a.transfer_price) : 0;
            // Final per-head price = edited service total (base + guide + transfer)
            const finalPriceNum = basePriceNum + guidePriceNum + transferPriceNum;
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
                base_price: basePriceNum,
                final_price: finalPriceNum,
                total_price: finalPriceNum,
                adult_price: a.adult_price != null && a.adult_price !== '' ? parseFloat(a.adult_price) : null,
                child_price: a.child_price != null && a.child_price !== '' ? parseFloat(a.child_price) : null,
                ticket_id: a.ticket_id || null,
                ticket_name: a.ticket_name || null,
                ticket: a.ticket || null,
                guide: a.guide || null,
                transfer: !!a.transfer,
                vehicle_id: a.vehicle_id || null,
                vehicle_name: a.vehicle_name || null,
                transfer_price: transferPriceNum,
                transfer_type: a.transfer_type || 'private',
                day: clampDay(a.day),
                city_plan_id: a.city_plan_id || null,
                city_plan_city: a.city_plan_city || null,
                pickup_id: a.pickup_id || null,
                pickup_name: a.pickup_name || null,
                dropoff_id: a.dropoff_id || null,
                dropoff_name: a.dropoff_name || null
            };
        });
        // Restaurants: full data including transfer, pickup/dropoff, compulsory
        const selectedRestaurantsPayload = definitionRestaurants.map(function(r) {
            const basePriceNum = r.base_price != null && r.base_price !== '' && !isNaN(parseFloat(r.base_price)) ? parseFloat(r.base_price) : 0;
            const transferPriceNum = r.transfer && r.transfer_price != null && r.transfer_price !== '' && !isNaN(parseFloat(r.transfer_price)) ? parseFloat(r.transfer_price) : 0;
            // Final per-head price = edited service total (base + transfer)
            const finalPriceNum = basePriceNum + transferPriceNum;
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
                base_price: basePriceNum,
                final_price: finalPriceNum,
                total_price: finalPriceNum,
                selected_meals: r.selected_meals || [],
                meal_type: r.meal_type || null,
                meal_type_label: r.meal_type_label || null,
                adult_price: r.meal_adult_price != null && r.meal_adult_price !== '' ? parseFloat(r.meal_adult_price) : null,
                child_price: r.meal_child_price != null && r.meal_child_price !== '' ? parseFloat(r.meal_child_price) : null,
                transfer: !!r.transfer,
                vehicle_id: r.vehicle_id || null,
                vehicle_name: r.vehicle_name || null,
                transfer_price: transferPriceNum,
                transfer_type: r.transfer_type || 'private',
                day: clampDay(r.day),
                city_plan_id: r.city_plan_id || null,
                city_plan_city: r.city_plan_city || null,
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
        $('#arrival-vehicles-hidden').val(JSON.stringify(transferStateByPlan || {}));
        $('#departure-pickup-hotel-hidden').val($('#departure-pickup-hotel').val() || '');
        $('#departure-dropoff-port-hidden').val($('#departure-dropoff-port').val() || '');
        $('#departure-vehicles-hidden').val(JSON.stringify(transferStateByPlan || {}));
        $('#definition-day-city-plan').val(JSON.stringify(getDayCityPlanMap()));
        $('#definition-day-wise-itinerary').val(JSON.stringify(buildDefinitionDayWiseItineraryPayload()));
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
    // Gallery (simple): avoid recursive click bubbling from hidden input
    $('#gallery-drop-area').on('click', function(e) {
        if ($(e.target).is('#gallery_images')) return;
        $('#gallery_images')[0].click();
    });
    $('#gallery_images').on('click', function(e) {
        e.stopPropagation();
    });
    $('#gallery_images').prop('multiple', true);
    let gallerySelectedFiles = [];
    function getGalleryFileKey(file) {
        return [file.name, file.size, file.lastModified].join('__');
    }
    function mergeGalleryFiles(existingFiles, newFiles) {
        const merged = [];
        const seen = {};
        Array.from(existingFiles || []).concat(Array.from(newFiles || [])).forEach(function(file) {
            if (!file || !file.type || !file.type.startsWith('image/')) return;
            const key = getGalleryFileKey(file);
            if (seen[key]) return;
            seen[key] = true;
            merged.push(file);
        });
        return merged;
    }
    function syncGalleryInputFiles(files) {
        try {
            const dt = new DataTransfer();
            Array.from(files || []).forEach(function(file) { dt.items.add(file); });
            $('#gallery_images')[0].files = dt.files;
            return true;
        } catch (err) {
            return false;
        }
    }
    function renderGalleryPreview(files) {
        const previewContainer = $('#gallery-preview-container');
        previewContainer.empty();
        const imageFiles = Array.from(files || []).filter(function(file) {
            return file && file.type && file.type.startsWith('image/');
        });

        if (!imageFiles.length) return;

        imageFiles.forEach(function(file) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                previewContainer.append(
                    '<img src="' + ev.target.result + '" style="max-height:100px;border-radius:8px;margin:0 8px 8px 0;" alt="Gallery image preview">'
                );
            };
            reader.readAsDataURL(file);
        });
    }
    $('#gallery_images').on('change', function() {
        gallerySelectedFiles = mergeGalleryFiles(gallerySelectedFiles, this.files);
        syncGalleryInputFiles(gallerySelectedFiles);
        renderGalleryPreview(gallerySelectedFiles);
    });
    $('#gallery-drop-area').on('dragenter dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('gallery-drop-active');
    });
    $('#gallery-drop-area').on('dragleave dragend drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('gallery-drop-active');
    });
    $('#gallery-drop-area').on('drop', function(e) {
        const droppedFiles = e.originalEvent.dataTransfer ? e.originalEvent.dataTransfer.files : null;
        if (!droppedFiles || !droppedFiles.length) return;
        gallerySelectedFiles = mergeGalleryFiles(gallerySelectedFiles, droppedFiles);
        if (syncGalleryInputFiles(gallerySelectedFiles)) {
            $('#gallery_images').trigger('change');
            return;
        }
        renderGalleryPreview(gallerySelectedFiles);
    });

    updateDefinitionTotalsAndMarkup();
});
</script>
@endsection

@section('styles')
<style>
.form-control, .form-select { padding: 0.5rem 1rem; border-radius: 0.375rem; min-height: 42px; }
.card { border: none; box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12); border-radius: 0.5rem; }
.card-header { border-bottom: 1px solid #d9dee3; padding: 1rem 1.5rem; }
.select2-container { width: 100% !important; }
.select2-container .select2-selection--single,
.select2-container--default .select2-selection--multiple { min-height: 42px; border-radius: 0.375rem; }
.select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 40px; padding-right: 32px; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
.gallery-drop-active { border-color: #0056b3 !important; background-color: rgba(0, 123, 255, 0.05); }
#gallery-preview-container img { max-height: 100px !important; width: auto !important; border-radius: 8px; margin: 0 8px 8px 0; display: inline-block; vertical-align: top; }
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

/* More compact service sections */
.definition-service-card .card-body { margin-top: 8px !important; padding-top: 12px !important; padding-bottom: 12px !important; }
.definition-service-card .card-header { padding-top: 0.75rem; padding-bottom: 0.75rem; }
.definition-service-card .hotel-attraction-box { padding: 0.75rem !important; }
.definition-service-card .border.rounded.p-3 { padding: 0.75rem !important; }
.definition-service-card .bg-primary-subtle,
.definition-service-card .bg-success-subtle,
.definition-service-card .bg-info-subtle,
.definition-service-card .bg-warning-subtle { padding: 0.6rem !important; }

/* Compact Day-wise itinerary */
.def-itinerary-wrap { max-height: 320px; overflow: auto; padding-right: 4px; }
.def-it-accordion .accordion-item { border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; margin-bottom: 8px; }
.def-it-accordion .accordion-button { padding: 10px 12px; background: #fff; }
.def-it-accordion .accordion-button:not(.collapsed) { background: rgba(105, 108, 255, 0.06); }
.def-it-btn { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.def-it-btn-left { display: flex; align-items: baseline; gap: 10px; min-width: 0; }
.def-it-day-label { font-weight: 700; font-size: 0.85rem; white-space: nowrap; }
.def-it-summary { font-size: 0.82rem; color: #6b7280; min-width: 0; flex: 1; }
.def-it-empty { color: #9ca3af; font-size: 0.82rem; }
.def-it-chips { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; min-width: 0; }
.def-it-chip { display: inline-flex; align-items: center; gap: 6px; padding: 3px 8px; border-radius: 999px; border: 1px solid #e5e7eb; background: #fff; font-size: 0.78rem; color: #374151; max-width: 240px; }
.def-it-chip i { font-size: 0.9rem; opacity: 0.85; }
.def-it-chip-t { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.def-it-chip--hotel { background: rgba(13, 110, 253, 0.06); border-color: rgba(13, 110, 253, 0.18); }
.def-it-chip--attraction { background: rgba(25, 135, 84, 0.06); border-color: rgba(25, 135, 84, 0.18); }
.def-it-chip--restaurant { background: rgba(253, 126, 20, 0.06); border-color: rgba(253, 126, 20, 0.18); }
.def-it-chip--arrival { background: rgba(13, 202, 240, 0.08); border-color: rgba(13, 202, 240, 0.22); }
.def-it-chip--departure { background: rgba(255, 193, 7, 0.10); border-color: rgba(255, 193, 7, 0.28); }
.def-it-chip--more { background: #f3f4f6; border-color: #e5e7eb; color: #6b7280; }
.def-it-btn-right .badge { font-size: 0.75rem; }
.def-it-body { padding: 10px 12px; background: #fff; display: flex; flex-direction: column; gap: 10px; }
.def-it-item { padding: 8px 10px; border: 1px dashed #eef2f7; border-radius: 10px; background: #fff; }
.def-it-name { font-size: 0.85rem; font-weight: 600; color: #111827; }
.def-it-meta { font-size: 0.78rem; color: #6b7280; }
.def-it-item--hotel { background: rgba(13, 110, 253, 0.03); border-color: rgba(13, 110, 253, 0.14); }
.def-it-item--attraction { background: rgba(25, 135, 84, 0.03); border-color: rgba(25, 135, 84, 0.14); }
.def-it-item--restaurant { background: rgba(253, 126, 20, 0.03); border-color: rgba(253, 126, 20, 0.14); }
.def-it-item--arrival { background: rgba(13, 202, 240, 0.04); border-color: rgba(13, 202, 240, 0.18); }
.def-it-item--departure { background: rgba(255, 193, 7, 0.05); border-color: rgba(255, 193, 7, 0.20); }
@media (max-width: 991.98px) {
    .hotel-attraction-box { min-height: auto; }
    .card-header { padding: 0.75rem 1rem; }
}
@media (max-width: 767.98px) {
    .form-control, .form-select { min-height: 38px; padding: 0.45rem 0.75rem; }
    .select2-container .select2-selection--single,
    .select2-container--default .select2-selection--multiple { min-height: 38px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px; font-size: 0.9rem; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
    .def-it-chip { max-width: 160px; }
}
</style>
@endsection


