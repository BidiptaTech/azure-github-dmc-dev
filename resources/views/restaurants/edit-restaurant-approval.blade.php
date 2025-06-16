@extends('layouts.layout')
@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<style>
    .select2-container .select2-selection--single {
        height: 100% !important; /* Adjust as needed */
        line-height: 100% !important;
        padding: 8px 12px;
    }
    /* Increase the height of the dropdown items */
    .select2-container .select2-results__option {
        padding: 12px 10px;
    }

    /* Flatpickr container styling */
    /* .flatpickr-input {
        height: 40px !important;
        width: 100% !important;
        padding: 0.375rem 0.75rem !important;
        font-size: 0.9375rem !important;
        font-weight: 400 !important;
        line-height: 1.53 !important;
        background-color: #fff !important;
        background-clip: padding-box !important;
        border: 1px solid #d9dee3 !important;
        border-radius: 0.375rem !important;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
    }

    .flatpickr-calendar {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
        border-radius: 0.375rem !important;
    }

    .flatpickr-time {
        height: 40px !important;
        line-height: 40px !important;
        border-radius: 0 0 0.375rem 0.375rem !important;
    }

    .flatpickr-time input {
        height: 40px !important;
        font-size: 0.9375rem !important;
    }

    .flatpickr-input:hover {
        border-color: #697a8d !important;
    }

    .flatpickr-input:focus {
        border-color: #696cff !important;
        box-shadow: 0 0 0.25rem rgba(105, 108, 255, 0.1) !important;
        outline: none !important;
    } */

    .flatpickr-time {
        height: 38px; /* match Bootstrap input height */
        line-height: 38px;
    }

    /* Reduce width of the time dropdown */
    .flatpickr-calendar.open {
        width: auto !important;
        min-width: 120px;
    }

    /* Make time inputs (hour & minute) smaller */
    .flatpickr-time input {
        width: 40px;
        height: 30px;
        padding: 0;
        text-align: center;
        font-size: 14px;
    }

    .flatpickr-input:hover {
        border-color: #697a8d !important;
    }

    .flatpickr-input:focus {
        border-color: #696cff !important;
        box-shadow: 0 0 0.25rem rgba(105, 108, 255, 0.1) !important;
        outline: none !important;
    }
</style>

<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Approve Restaurant

                <a href="{{ route('restaurants.approval') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
                @if($same_restaurant)
                <!-- Same Hotel popup Modal -->
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    Already Exist Same Restaurant !
                </button>
                @endif
            </h5>
            <form id="restaurantForm" method="POST" action="{{ route('restaurant.update.approval', $restaurant->restaurant_id) }}" enctype="multipart/form-data" class="card-body">
                @csrf
                @method('PUT')
                <!-- Hidden Fields -->

                <div id="restaurantDetailsContainer">
                    <div class="restaurant-form">
                        <div class="row">
                            <!-- Restaurant Country -->
                            <div class="mb-3 col-md-3">
                                <label for="country" class="form-label"><strong>Country</strong>
                                    <span style="color: red; font-weight: bold;">*</span>
                                </label>
                                <input name="country" class="form-control" type="text" value="{{$restaurant->country}}" readonly>
                                @error('country')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Restaurant City -->
                            <div class="col-md-3 mb-3">
                                <label for="city" class="form-label"><strong>City</strong><span class="text-danger">*</span></label>
                                <select name="city" id="citySelect" class="form-control" required>
                                    <option value="{{ $restaurant->city }}">{{ $restaurant->city }}</option>
                                    @foreach($city as $c)
                                        @if($c->name != $restaurant->city)
                                            <option value="{{ $c->name }}">{{ $c->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('city')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Restaurant Name -->
                            <div class="mb-3 col-md-3">
                                <label for="input35" class="form-label"><strong>Restaurant Name</strong>
                                    <span style="color: red; font-weight: bold;">*</span>
                                </label>
                                {{-- <span class="text-danger">(This restaurant name cannot be changed.)</span> --}}
                                @if($same_restaurant)
                                <input type="text" class="form-control" id="input35" name="name"
                                value="{{ old('name', $same_restaurant->name) }}" placeholder="Enter Restaurant Name" readonly required>
                                <span></span>
                                @else
                                <input value="{{$restaurant->name}}" type="text" class="form-control" name="name"
                                placeholder="Enter Restaurant Name" required>
                                @endif

                                @error('name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- <div class="col-md-3 mb-3">
                                <label for="name" class="form-label"><strong>Restaurant Name</strong><span class="text-danger">*</span></label>
                                <input value="{{$restaurant->name}}" type="text" class="form-control" name="name" placeholder="Enter Restaurant Name" required>
                                @error('name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div> --}}

                                {{-- <!-- Restaurant City -->
                                <div class="col-md-3 mb-3">
                                    <label for="city" class="form-label"><strong>City</strong><span
                                            class="text-danger">*</span></label>
                                    <input value="{{$restaurant->city}}" type="text" class="form-control" name="city"
                                        placeholder="Enter City" required>
                                    @error('city')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Restaurant Country -->
                                <div class="col-md-3 mb-3">
                                    <label for="country" class="form-label"><strong>country</strong><span
                                            class="text-danger">*</span></label>
                                    <input value="{{$restaurant->country}}" type="text" class="form-control" name="country"
                                        placeholder="Enter Country" required>
                                    @error('country')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div> --}}

                                <!-- Latitude -->
                                <div class="col-md-3 mb-3">
                                    <label for="latitude" class="form-label">
                                        <strong>Latitude</strong><span class="text-danger">*</span>
                                    </label>
                                    <input name="latitude" type="number" step="0.000001" value="{{$restaurant->latitude}}" class="form-control"
                                        placeholder="Enter Latitude" required></input>
                                    @error('latitude')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Longitude -->
                            <div class="col-md-3 mb-3">
                                <label for="longitude" class="form-label">
                                    <strong>Longitude</strong><span class="text-danger">*</span>
                                </label>
                                <input name="longitude" type="number" step="0.000001" value="{{$restaurant->longitude}}" class="form-control"
                                    placeholder="Enter Longitude" required></input>
                                @error('longitude')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cuisine Type -->
                            <div class="col-md-3 mb-3">
                                <label for="cuisine" class="form-label"><strong>Cuisine</strong><span class="text-danger">*</span></label>
                                <input value="{{$restaurant->cuisine}}" type="text" class="form-control" name="cuisine" placeholder="Enter Cuisine Type" required>
                                @error('cuisine')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Ownership -->
                            <div class="col-md-3 mb-3">
                                <label for="owned_by" class="form-label"><strong>Ownership</strong><span class="text-danger">*</span></label>
                                <select name="owned_by" class="form-select" required>
                                    <option value="" {{ is_null($restaurant->owned_by) ? 'selected' : '' }}>Select</option>
                                    <option value="0" {{ $restaurant->owned_by === "0" ? 'selected' : '' }}>Third Party</option>
                                    @foreach($hotels as $hotel)
                                        <option  value="{{ $hotel->hotel_unique_id }}" {{ $restaurant->owned_by == $hotel->hotel_unique_id ? 'selected' : '' }}>
                                            {{ $hotel->name }} - {{ $hotel->display_id }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('owned_by')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="property" class="form-label"><strong>Property</strong><span class="text-danger">*</span></label>
                                <select name="property" class="form-select">
                                    <option value="">Select</option>
                                    <option value="third_party" {{ old('property', $restaurant->property) == 'third_party' ? 'selected' : '' }}>Third Party</option>
                                    <option value="owner" {{ old('property', $restaurant->property) == 'owner' ? 'selected' : '' }}>Ownership</option>
                                </select>
                                @error('property')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Meal Availability -->
                        <div class="row">
                            <!-- Breakfast -->
                            {{-- <div class="col-md-12 mb-3">
                                <label class="form-label"><strong>Breakfast Availability</strong></label>
                                <div class="form-check form-switch">
                                    <input id="breakfastToggle" {{ $restaurant->breakfast_available == 1 ? 'checked' : '' }} class="form-check-input" type="checkbox" name="breakfast_available" value="1">
                                    <label class="form-check-label">Available</label>
                                </div>
                                <div class="row mt-2 d-none" id="breakfastFields">
                                    <div class="col-md-3">
                                        <label for="opening_time_bf" class="form-label"><strong>Opening Time</strong></label>
                                        <input value="{{$restaurant->opening_time_bf}}" type="time" class="form-control" name="opening_time_bf">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="closing_time_bf" class="form-label"><strong>Closing Time</strong></label>
                                        <input value="{{$restaurant->closing_time_bf}}" type="time" class="form-control" name="closing_time_bf">
                                    </div>
                                    <!-- Breakfast Price -->
                                    <div class="col-md-3 mb-3">
                                        <label for="breakfast_price" class="form-label"><strong>Breakfast Price</strong><span class="text-danger">*</span></label>
                                        <input value = "{{$restaurant->bf_price}}" type="number" class="form-control" name="breakfast_price" placeholder="Enter Breakfast Price">
                                        @error('breakfast_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Lunch -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label"><strong>Lunch Availability</strong></label>
                                <div class="form-check form-switch">
                                    <input id="lunchToggle" {{ $restaurant->lunch_available == 1 ? 'checked' : '' }} class="form-check-input" type="checkbox" name="lunch_available" value="1">
                                    <label class="form-check-label">Available</label>
                                </div>
                                <div class="row mt-2 d-none" id="lunchFields">
                                    <div class="col-md-3">
                                        <label for="opening_time_lunch" class="form-label"><strong>Opening Time</strong></label>
                                        <input value="{{$restaurant->opening_time_lunch}}" type="time" class="form-control" name="opening_time_lunch">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="closing_time_lunch" class="form-label"><strong>Closing Time</strong></label>
                                        <input  value="{{$restaurant->closing_time_lunch}}" type="time" class="form-control" name="closing_time_lunch">
                                    </div>
                                    <!-- Lucnh Price -->
                                    <div class="col-md-3 mb-3">
                                        <label for="lucnh_price" class="form-label"><strong>Lucnh Price</strong><span class="text-danger">*</span></label>
                                        <input value = "{{$restaurant->lunch_price}}" type="number" class="form-control" name="lucnh_price" placeholder="Enter Lucnh Price">
                                        @error('lucnh_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Dinner -->
                            <div class="col-md-12">
                                <label class="form-label"><strong>Dinner Availability</strong></label>
                                <div class="form-check form-switch">
                                    <input id="dinnerToggle" {{ $restaurant->dinner_available == 1 ? 'checked' : '' }} class="form-check-input" type="checkbox" name="dinner_available" value="1">
                                    <label class="form-check-label">Available</label>
                                </div>
                                <div class="row mt-2 d-none" id="dinnerFields" >
                                    <div class="col-md-3">
                                        <label for="opening_time_dinner" class="form-label"><strong>Opening Time</strong></label>
                                        <input  value="{{$restaurant->opening_time_dinner}}" type="time" class="form-control" name="opening_time_dinner">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="closing_time_dinner" class="form-label"><strong>Closing Time</strong></label>
                                        <input  value="{{$restaurant->closing_time_dinner}}" type="time" class="form-control" name="closing_time_dinner">
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="dinner_price" class="form-label"><strong>Dinner Price</strong><span class="text-danger">*</span></label>
                                        <input value = "{{$restaurant->dinner_price}}" type="number" class="form-control" name="dinner_price" placeholder="Enter Dinner Price">
                                        @error('dinner_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                </div>
                            </div> --}}

                            <!-- Breakfast -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label"><strong>Breakfast Availability</strong></label>
                                <div class="form-check form-switch">
                                    <input id="breakfastToggle" {{ $restaurant->breakfast_available == 1 ? 'checked' : '' }} class="form-check-input" type="checkbox" name="breakfast_available" value="1">
                                    <label class="form-check-label">Available</label>
                                </div>
                                <div class="row mt-2 d-none" id="breakfastFields">
                                    <div class="col-md-3">
                                        <label for="opening_time_bf" class="form-label"><strong>Opening Time</strong></label>
                                        <input value="{{$restaurant->opening_time_bf}}" type="text" class="form-control time-picker" id="opening_time_bf" name="opening_time_bf" placeholder="Select opening time">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="closing_time_bf" class="form-label"><strong>Closing Time</strong></label>
                                        <input value="{{$restaurant->closing_time_bf}}" type="text" class="form-control time-picker" id="closing_time_bf" name="closing_time_bf" placeholder="Select closing time">
                                    </div>
                                    <!-- Breakfast Price -->
                                    {{-- <div class="col-md-3 mb-3">
                                        <label for="breakfast_price" class="form-label"><strong>Breakfast Price</strong><span class="text-danger">*</span></label>
                                        <input value="{{$restaurant->bf_price}}" type="text" class="form-control" id="breakfast_price" name="breakfast_price" 
                                            placeholder="Enter Breakfast Price" oninput="validateNumericPrice(this)">
                                        <small class="validation-message text-danger" id="breakfast_price-validation-message"></small>
                                        @error('breakfast_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div> --}}
                                </div>
                            </div>

                            <!-- Lunch -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label"><strong>Lunch Availability</strong></label>
                                <div class="form-check form-switch">
                                    <input id="lunchToggle" {{ $restaurant->lunch_available == 1 ? 'checked' : '' }} class="form-check-input" type="checkbox" name="lunch_available" value="1">
                                    <label class="form-check-label">Available</label>
                                </div>
                                <div class="row mt-2 d-none" id="lunchFields">
                                    <div class="col-md-3">
                                        <label for="opening_time_lunch" class="form-label"><strong>Opening Time</strong></label>
                                        <input value="{{$restaurant->opening_time_lunch}}" type="text" class="form-control time-picker" id="opening_time_lunch" name="opening_time_lunch" placeholder="Select opening time">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="closing_time_lunch" class="form-label"><strong>Closing Time</strong></label>
                                        <input value="{{$restaurant->closing_time_lunch}}" type="text" class="form-control time-picker" id="closing_time_lunch" name="closing_time_lunch" placeholder="Select closing time">
                                    </div>
                                    <!-- Lunch Price -->
                                    {{-- <div class="col-md-3 mb-3">
                                        <label for="lunch_price" class="form-label"><strong>Lunch Price</strong><span class="text-danger">*</span></label>
                                        <input value="{{$restaurant->lunch_price}}" type="text" class="form-control" id="lunch_price" name="lunch_price" 
                                            placeholder="Enter Lunch Price" oninput="validateNumericPrice(this)">
                                        <small class="validation-message text-danger" id="lunch_price-validation-message"></small>
                                        @error('lunch_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div> --}}
                                </div>
                            </div>

                            <!-- Dinner -->
                            <div class="col-md-12">
                                <label class="form-label"><strong>Dinner Availability</strong></label>
                                <div class="form-check form-switch">
                                    <input id="dinnerToggle" {{ $restaurant->dinner_available == 1 ? 'checked' : '' }} class="form-check-input" type="checkbox" name="dinner_available" value="1">
                                    <label class="form-check-label">Available</label>
                                </div>
                                <div class="row mt-2 d-none" id="dinnerFields">
                                    <div class="col-md-3">
                                        <label for="opening_time_dinner" class="form-label"><strong>Opening Time</strong></label>
                                        <input value="{{$restaurant->opening_time_dinner}}" type="text" class="form-control time-picker" id="opening_time_dinner" name="opening_time_dinner" placeholder="Select opening time">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="closing_time_dinner" class="form-label"><strong>Closing Time</strong></label>
                                        <input value="{{$restaurant->closing_time_dinner}}" type="text" class="form-control time-picker" id="closing_time_dinner" name="closing_time_dinner" placeholder="Select closing time">
                                    </div>
                                    {{-- <div class="col-md-3 mb-3">
                                        <label for="dinner_price" class="form-label"><strong>Dinner Price</strong><span class="text-danger">*</span></label>
                                        <input value="{{$restaurant->dinner_price}}" type="text" class="form-control" id="dinner_price" name="dinner_price" 
                                            placeholder="Enter Dinner Price" oninput="validateNumericPrice(this)">
                                        <small class="validation-message text-danger" id="dinner_price-validation-message"></small>
                                        @error('dinner_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div> --}}
                                </div>
                            </div>
                        </div>

                        <div class="row col-md-12">
                            <!-- Master image -->
                            <div class="mt-3 mb-3 col-md-4">
                                <div>
                                    <label for="master_image" class="form-label"><strong>Master
                                            Image</strong></label>
                                    <div id="master-drop-area" class="form-control"
                                        style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px;">
                                        Drag & Drop your files here or click to upload.
                                        <input type="file" id="master_image" name="master_image" multiple
                                            style="display: none;">
                                    </div>
                                </div>
                                <div id="master-preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                    style="max-width: 30%; overflow-x: auto; white-space: nowrap;"></div>

                                @if($restaurant->master_image)
                                <div class="image-preview-container d-flex flex-wrap gap-2">
                                    <div class="image-preview-wrapper position-relative">
                                        <img src="{{$restaurant->master_image}}" alt="Room Master Image"
                                            style="max-width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                                        <button
                                            class="delete-image-btn position-absolute top-0 end-0 btn btn-sm btn-danger"
                                            data-image="{{ $restaurant->master_image }}"
                                            style="width: 20px; height: 20px; line-height: 18px; padding: 0; text-align: center; font-size: 14px; z-index: 1;">
                                            &times;
                                        </button>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Additional Image drop -->
                            <div class="mt-3 mb-3 col-md-8">
                                <div>
                                    <label for="images" class="form-label"><strong>Additional
                                            Images</strong></label>
                                    <div id="drop-area" class="form-control"
                                        style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px;">
                                        Drag & Drop your files here or click to upload.
                                        <input type="file" id="images" name="images[]" multiple
                                            style="display: none;">
                                    </div>

                                    <div id="preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                        style="max-width: 100%; overflow-x: auto; white-space: nowrap;"></div>
                                </div>

                                <!-- Existing Image Section -->
                                <div class="existing-image-preview-container d-flex flex-wrap gap-2">
                                    @php
                                    $images = json_decode($restaurant->images, true);
                                    @endphp
                                    
                                    @if($images)
                                    @foreach($images as $img)
                                    <!-- Hidden input to hold existing image path -->

                                    <div class="existing-image-preview-wrapper position-relative">
                                        <input type="hidden" name="existing_images[]" value="{{ $img }}">
                                        <img src="{{ asset($img) }}" alt="Facility Image"
                                            style="max-width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                                        <button
                                            class="delete-image-btn position-absolute top-0 end-0 btn btn-sm btn-danger"
                                            data-image="{{ $img }}"
                                            style="width: 20px; height: 20px; line-height: 18px; padding: 0; text-align: center; font-size: 14px; z-index: 1;">
                                            &times;
                                        </button>
                                    </div>
                                    @endforeach
                                    @endif
                                </div>
                                <input type="file" name="all_images[]" id="all-images" style="display: none;">

                                @error('images')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                                <div id="preview-container" class="mt-3 d-flex flex-wrap gap-2"
                                    style="max-width: 100%; overflow-x: auto; white-space: nowrap;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="col-md-12 mb-3">
                    <label for="description" class="form-label"><strong>Description</strong><span
                            style="color: red;">*</span></label>
                    <textarea id="summernote" name="description" class="form-control" rows="10">{{ old('description', $restaurant->description) }}</textarea required>
                    @error('description')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remarks -->
                <div class="col-md-12 mb-3">
                    <label for="remarks" class="form-label"><strong>Remarks</strong></label>
                    <textarea id="remarks" name="remarks" class="form-control" rows="4" placeholder="Enter any remarks or notes (optional)">{{ old('remarks', $restaurant->remarks) }}</textarea>
                    @error('remarks')
                    <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Terms & Conditions -->
                <div class="col-md-12 mb-3">
                    <label for="terms_conditions" class="form-label"><strong>Terms & Conditions</strong><span class="text-danger">*</span></label>
                    <textarea id="terms_conditions" name="terms_conditions" class="form-control" rows="6" placeholder="Enter terms and conditions..." required>{{ old('terms_conditions', $restaurant->terms_conditions) }}</textarea>
                    @error('terms_conditions')
                    <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Status -->
                <div class="mt-2 form-check form-switch">
                    <label for="restaurant_status" class="form-label"><strong>Status</strong></label>
                    <span style="color: red; font-weight: bold;">*</span>
                    <input {{$restaurant->is_active == 1 ? 'checked' : ''}} class="form-check-input" name="restaurant_status" type="checkbox" id="restaurant_status"
                        value="1">
                    <label class="form-check-label"></label>
                    @error('restaurant_status')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="d-flex align-items-center gap-3">
                    <button type="submit" class="btn btn-primary px-4" name="approval_status" value="">
                        <i class="fas fa-check-circle me-2"></i> Approve and Save
                    </button>
                    
                    <button type="submit" class="btn btn-danger px-4" name="decline_status" value="">
                        <i class="fas fa-times-circle me-2"></i> Decline
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End of the form -->

@if($same_restaurant)
<!-- SAME HOTEL MODAL -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">{{ $same_restaurant->name }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="hotel-details">
                    <p><strong>Restaurant Name:</strong> {{ $same_restaurant->name }}</p>
                    <p><strong>Latitude:</strong> {{ $same_restaurant->latitude }}</p>
                    <p><strong>Longitude:</strong> {{ $same_restaurant->longitude }}</p>
                    <p><strong>City:</strong> {{ $same_restaurant->city }}</p>
                    <p><strong>Country:</strong> {{ $same_restaurant->country }}</p>
                    <!-- Optionally, you can display a hotel image -->
                    {{-- @if($same_restaurant->master_image)
                        <img src="{{ asset('storage/'.$same_restaurant->master_image) }}" alt="{{ $same_restaurant->master_image }}" class="img-fluid rounded">
                    @endif --}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
@section('scripts') 
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    $(document).ready(function() {
        $('#summernote').summernote({
            height: 200,      
            minHeight: 200,   
            maxHeight: 500,   
            placeholder: 'Enter your content here...', 
        });
        $('#remarks').summernote({
            height: 200,      
            minHeight: 200,   
            maxHeight: 500,   
            placeholder: 'Enter any remarks or notes (optional)...', 
        });
        $('#terms_conditions').summernote({
            height: 200,      
            minHeight: 200,   
            maxHeight: 500,   
            placeholder: 'Enter terms and conditions...', 
        });
        // Initialize Select2 for city
        $('#citySelect').select2({
            placeholder: "Search and Select a City",
            allowClear: true,
            tags: true,
            width: '100%'
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Function to initialize flatpickr for time inputs
        function initializeTimePickers(container) {
            container.querySelectorAll('.time-picker').forEach(function(input) {
                if (!input._flatpickr) { // Only initialize if not already initialized
                    flatpickr(input, {
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i", // 24-hour format
                        time_24hr: true,
                        minuteIncrement: 15
                    });
                }
            });
        }
        
        // Initialize all time pickers when the page loads
        initializeTimePickers(document);
        
        // Configure toggle visibility with flatpickr initialization
        const toggleVisibility = (checkboxId, fieldId) => {
            const checkbox = document.getElementById(checkboxId);
            const fieldsDiv = document.getElementById(fieldId);
            
            const updateVisibility = () => {
                fieldsDiv.classList.toggle('d-none', !checkbox.checked);
                
                // Reinitialize flatpickr for newly visible time inputs
                if (checkbox.checked) {
                    initializeTimePickers(fieldsDiv);
                }
            };
            
            // Add event listener for checkbox changes
            checkbox.addEventListener('change', updateVisibility);
            
            // Initial visibility setup
            updateVisibility();
        };
        
        // Set up toggle functionality for meal sections
        toggleVisibility('breakfastToggle', 'breakfastFields');
        toggleVisibility('lunchToggle', 'lunchFields');
        toggleVisibility('dinnerToggle', 'dinnerFields');
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleVisibility = (checkboxId, fieldId) => {
            const checkbox = document.getElementById(checkboxId);
            const field = document.getElementById(fieldId);
            const updateVisibility = () => {
                field.classList.toggle('d-none', !checkbox.checked);
            };
            checkbox.addEventListener('change', updateVisibility);
            updateVisibility();
        };

        toggleVisibility('breakfastToggle', 'breakfastFields');
        toggleVisibility('lunchToggle', 'lunchFields');
        toggleVisibility('dinnerToggle', 'dinnerFields');
    });
</script>

<!-- Additional Image drop down -->
<script>
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('images');
    const fileList = document.getElementById('preview-container');
    const allImagesInput = document.getElementById('all-images'); // Hidden input
    let files = []; // Store all files manually
    const MAX_VISIBLE_IMAGES = 3; // Maximum number of visible images
    let showAllImages = false; // Toggle for showing all images

    // Trigger file input on click
    dropArea.addEventListener('click', () => fileInput.click());

    // Handle file input change
    fileInput.addEventListener('change', () => handleFiles(fileInput.files));

    // Handle drag-and-drop events
    dropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropArea.style.borderColor = '#000';
    });

    dropArea.addEventListener('dragleave', () => {
        dropArea.style.borderColor = '#ccc';
    });

    dropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        dropArea.style.borderColor = '#ccc';
        handleFiles(e.dataTransfer.files);
    });

    function handleFiles(newFiles) {
        // Append new files to the list
        files = [...files, ...Array.from(newFiles)];
        updateFileList();
    }

    function updateFileList() {
        // Clear file list display
        fileList.innerHTML = '';
        const dataTransfer = new DataTransfer();

        // Decide how many files to display based on `showAllImages`
        const visibleFiles = showAllImages ? files : files.slice(0, MAX_VISIBLE_IMAGES);

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
                const fileIndex = files.indexOf(file);
                if (fileIndex > -1) {
                    files.splice(fileIndex, 1);
                }
                updateFileList();
            });

            // Append image and delete button to the wrapper
            imageWrapper.appendChild(img);
            imageWrapper.appendChild(deleteButton);
            fileList.appendChild(imageWrapper);

            // Add the file to the DataTransfer object
            dataTransfer.items.add(file);
        });

        // Add all files to the hidden input `all-images`
        const hiddenDataTransfer = new DataTransfer();
        files.forEach(file => hiddenDataTransfer.items.add(file));
        allImagesInput.files = hiddenDataTransfer.files;

        // Add a "More Images" badge if there are more files and not showing all images
        if (!showAllImages && files.length > MAX_VISIBLE_IMAGES) {
            const moreBadge = document.createElement('div');
            moreBadge.textContent = `+${files.length - MAX_VISIBLE_IMAGES} more`;
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
                showAllImages = true;
                updateFileList(); // Re-render with all images
            });

            fileList.appendChild(moreBadge);
        }
    }
</script>

<!-- delete existing Image -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Use event delegation for dynamically added elements
        document.querySelector('.existing-image-preview-container').addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-image-btn')) {
                e.preventDefault(); // Prevent form submission
                e.stopPropagation(); // Stop event propagation
                const button = e.target;

                // Find the image preview wrapper
                const imageWrapper = button.closest('.existing-image-preview-wrapper');
                if (imageWrapper) {
                    // Find and remove the associated hidden input field for the image
                    const hiddenInput = imageWrapper.querySelector('input[type="hidden"]');
                    if (hiddenInput) {
                        hiddenInput.remove(); // Remove the hidden input
                    }

                    // Remove the image wrapper (image and button)
                    imageWrapper.remove();
                }
            }
        });
    });
</script>

<!-- delete existing Master Image -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Use event delegation for dynamically added elements
        document.querySelector('.image-preview-container').addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-image-btn')) {
                e.preventDefault(); // Prevent form submission
                e.stopPropagation(); // Stop event propagation
                const button = e.target;

                // Find the image preview wrapper
                const imageWrapper = button.closest('.image-preview-wrapper');
                if (imageWrapper) {
                    // Find and remove the associated hidden input field for the image
                    const hiddenInput = imageWrapper.querySelector('input[type="hidden"]');
                    if (hiddenInput) {
                        hiddenInput.remove(); // Remove the hidden input
                    }

                    // Remove the image wrapper (image and button)
                    imageWrapper.remove();
                }
            }
        });
    });
</script>

<!-- Master Image drop down -->
<script>
    const masterDropArea = document.getElementById('master-drop-area');
    const masterFileInput = document.getElementById('master_image');
    const masterPreviewContainer = document.getElementById('master-preview-container');
    let masterFileCounter = 0; // Track total uploaded files
    const MASTER_MAX_VISIBLE_IMAGES = 1; // Show only 1 image

    // Open file picker on click
    masterDropArea.addEventListener('click', () => masterFileInput.click());

    // Handle drag events
    masterDropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        masterDropArea.style.backgroundColor = '#e3f2fd';
    });

    masterDropArea.addEventListener('dragleave', () => {
        masterDropArea.style.backgroundColor = 'white';
    });

    masterDropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        masterDropArea.style.backgroundColor = 'white';
        masterHandleFiles(e.dataTransfer.files);
    });

    // Handle file input change
    masterFileInput.addEventListener('change', () => {
        masterHandleFiles(masterFileInput.files);
    });

    // Process and display files
    function masterHandleFiles(files) {
        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    // If an image already exists, remove it before adding the new one
                    if (masterFileCounter > 0) {
                        masterPreviewContainer.innerHTML = ''; // Clear the existing preview
                        masterFileCounter = 0; // Reset the file counter
                    }
                    masterFileCounter++;
                    masterImagePreview(e.target.result);
                };
                reader.readAsDataURL(file);
            } else {
                alert(`${file.name} is not a valid image file.`);
            }
        });
    }

    // Add image preview with limited visibility and a "more" badge
    function masterImagePreview(imageSrc) {
        console.log("master image preview");

        const imageWrapper = document.createElement('div');
        imageWrapper.style.position = 'relative';
        imageWrapper.style.width = '70px';
        imageWrapper.style.height = '70px';
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
            masterPreviewContainer.removeChild(imageWrapper);
            masterFileCounter--;
            updateMoreBadge();
        });

        imageWrapper.appendChild(img);
        imageWrapper.appendChild(deleteButton);
        masterPreviewContainer.appendChild(imageWrapper);

        updateMoreBadge();
    }

    // Create and update "+X more" badge
    function updateMoreBadge() {
        // Remove any existing badge
        const existingBadge = document.getElementById('more-badge');
        if (existingBadge) existingBadge.remove();

        if (masterFileCounter > MASTER_MAX_VISIBLE_IMAGES) {
            const moreMasterBadge = document.createElement('div');
            moreMasterBadge.id = 'more-master-badge';
            moreMasterBadge.textContent = `+${masterFileCounter - MASTER_MAX_VISIBLE_IMAGES} more`;
            moreMasterBadge.style.margin = '5px';
            moreMasterBadge.style.padding = '5px 10px';
            moreMasterBadge.style.backgroundColor = '#007bff';
            moreMasterBadge.style.color = 'white';
            moreMasterBadge.style.borderRadius = '5px';
            moreMasterBadge.style.cursor = 'pointer';
            moreMasterBadge.style.fontSize = '12px';
            moreMasterBadge.style.textAlign = 'center';
            moreMasterBadge.addEventListener('click', () => {
                // Show all hidden images
                const hiddenImages = masterPreviewContainer.querySelectorAll('div[style*="display: none"]');
                hiddenImages.forEach(img => img.style.display = 'inline-block');
                moreMasterBadge.remove(); // Remove badge after revealing all
            });
            masterPreviewContainer.appendChild(moreMasterBadge);
        }
    }
</script>

@endsection