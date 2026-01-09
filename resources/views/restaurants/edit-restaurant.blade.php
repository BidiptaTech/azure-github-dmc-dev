@extends('layouts.layout')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<style>
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
    <ul class="nav nav-pills mb-4 mt-4 d-flex justify-content-center" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ request()->routeIs('restaurant.edit') ? 'active' : '' }}" 
                   href="{{ route('restaurant.edit', Crypt::encrypt($restaurant->restaurant_id)) }}" 
                   role="tab">
                    Restaurant
                </a>
            </li>
            
            <li class="nav-item" role="presentation">
                @if(isset($restaurant) && $restaurant->restaurant_id)
                <a class="nav-link {{ request()->routeIs('meals.restaurant_create') ? 'active' : '' }}" 
                   href="{{ route('meals.restaurant_create', Crypt::encrypt($restaurant->restaurant_id)) }}" 
                   role="tab">
                    Meals
                </a>
                @else
                <a class="nav-link disabled" 
                   href="javascript:void(0);" 
                   role="tab"
                   title="Save this restaurant first before adding meals">
                    Meals
                </a>
                @endif
            </li>
        </ul>
        <x-alert />
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Edit Restaurant
                <a href="{{ route('restaurant.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form id="restaurantForm" method="POST" action="{{ route('restaurant.update', Crypt::encrypt($restaurant->restaurant_id)) }}" enctype="multipart/form-data" class="card-body">
                @csrf
                @method('PUT')
                <!-- Hidden Fields -->

                <div id="restaurantDetailsContainer">
                    <div class="restaurant-form">
                        <div class="row">
                            <!-- Restaurant Name -->
                            <div class="col-md-3 mb-3">
                                <label for="name" class="form-label"><strong>Restaurant Name</strong><span class="text-danger">*</span></label>
                                <input value="{{$restaurant->name}}" type="text" class="form-control" name="name" placeholder="Enter Restaurant Name" required 
                                       @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) readonly @endif>
                                @error('name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Phone Number -->

                            <div class="col-md-3 mb-3">
                                <label for="phone" class="form-label"><strong>Phone Number</strong><span class="text-danger">*</span></label>
                                <input value="{{$restaurant->phone}}" type="text" class="form-control" name="phone" placeholder="Enter Phone Number" required 
                                       @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) readonly @endif>
                                @error('phone')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
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
                                <select name="city" id="citySelect" class="form-control" required 
                                        @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) disabled @endif>
                                    <option value="{{ $restaurant->city }}">{{ $restaurant->city }}</option>
                                    @foreach($city as $c)
                                        @if($c->name != $restaurant->city)
                                            <option value="{{ $c->name }}">{{ $c->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140]))
                                    <input type="hidden" name="city" value="{{ $restaurant->city }}">
                                @endif
                                @error('city')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Latitude -->
                            <div class="col-md-3 mb-3">
                                <label for="latitude" class="form-label">
                                    <strong>Latitude</strong><span class="text-danger">*</span>
                                </label>
                                <input name="latitude" type="text" id="latitude" value="{{$restaurant->latitude}}" class="form-control"
                                    placeholder="Enter Latitude" required
                                    @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) readonly @else oninput="validateLatitude(this)" @endif>
                                <small class="validation-message text-danger" id="latitude-validation-message"></small>
                                @error('latitude')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Longitude -->
                            <div class="col-md-3 mb-3">
                                <label for="longitude" class="form-label">
                                    <strong>Longitude</strong><span class="text-danger">*</span>
                                </label>
                                <input name="longitude" type="text" id="longitude" value="{{$restaurant->longitude}}" class="form-control"
                                    placeholder="Enter Longitude" required
                                    @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) readonly @else oninput="validateLongitude(this)" @endif>
                                <small class="validation-message text-danger" id="longitude-validation-message"></small>
                                @error('longitude')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cuisine Type -->
                            <div class="col-md-3 mb-3">
                                <label for="cuisine" class="form-label"><strong>Cuisine</strong><span class="text-danger">*</span></label>
                                <input value="{{$restaurant->cuisine}}" type="text" class="form-control" name="cuisine" placeholder="Enter Cuisine Type" required 
                                       @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) readonly @endif>
                                @error('cuisine')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Ownership -->
                            <div class="col-md-3 mb-3">
                                <label for="owned_by" class="form-label"><strong>Ownership</strong><span class="text-danger">*</span></label>
                                <select name="owned_by" id="owned_by" class="form-control" required 
                                        @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) disabled @endif>
                                    <option value="" {{ is_null($restaurant->owned_by) ? 'selected' : '' }}>Select</option>
                                    <option value="0" {{ $restaurant->owned_by === "0" ? 'selected' : '' }}>Third Party</option>
                                    @foreach($hotels as $hotel)
                                        <option  value="{{ $hotel->hotel_unique_id }}" {{ $restaurant->owned_by == $hotel->hotel_unique_id ? 'selected' : '' }}>
                                            {{ $hotel->name }} - {{ $hotel->display_id }}
                                        </option>
                                    @endforeach
                                </select>
                                @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140]))
                                    <input type="hidden" name="owned_by" value="{{ $restaurant->owned_by }}">
                                @endif
                                @error('owned_by')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="property" class="form-label"><strong>Property</strong><span class="text-danger">*</span></label>
                                <select name="property" class="form-select" required
                                        @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) disabled @endif>
                                    <option value="">Select</option>
                                    <option value="third_party" {{ old('property', $restaurant->property) == 'third_party' ? 'selected' : '' }}>Third Party</option>
                                    <option value="owner" {{ old('property', $restaurant->property) == 'owner' ? 'selected' : '' }}>Ownership</option>
                                </select>
                                @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140]))
                                    <input type="hidden" name="property" value="{{ $restaurant->property }}">
                                @endif
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
                                        <input value="{{$restaurant->bf_price}}" type="text" class="form-control" id="breakfast_price" name="breakfast_price" 
                                               placeholder="Enter Breakfast Price" oninput="validateNumericPrice(this)">
                                        <small class="validation-message text-danger" id="breakfast_price-validation-message"></small>
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
                                        <label for="lunch_price" class="form-label"><strong>Lunch Price</strong><span class="text-danger">*</span></label>
                                        <input value="{{$restaurant->lunch_price}}" type="text" class="form-control" id="lunch_price" name="lunch_price" 
                                               placeholder="Enter Lunch Price" oninput="validateNumericPrice(this)">
                                        <small class="validation-message text-danger" id="lunch_price-validation-message"></small>
                                        @error('lunch_price')
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
                                        <input value="{{$restaurant->dinner_price}}" type="text" class="form-control" id="dinner_price" name="dinner_price" 
                                               placeholder="Enter Dinner Price" oninput="validateNumericPrice(this)">
                                        <small class="validation-message text-danger" id="dinner_price-validation-message"></small>
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
                                        <input value="{{$restaurant->opening_time_bf}}" type="text" class="form-control @if(!in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) time-picker @endif" id="opening_time_bf" name="opening_time_bf" placeholder="Select opening time" 
                                               @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) readonly @endif>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="closing_time_bf" class="form-label"><strong>Closing Time</strong></label>
                                        <input value="{{$restaurant->closing_time_bf}}" type="text" class="form-control @if(!in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) time-picker @endif" id="closing_time_bf" name="closing_time_bf" placeholder="Select closing time" 
                                               @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) readonly @endif>
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
                                        <input value="{{$restaurant->opening_time_lunch}}" type="text" class="form-control @if(!in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) time-picker @endif" id="opening_time_lunch" name="opening_time_lunch" placeholder="Select opening time" 
                                               @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) readonly @endif>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="closing_time_lunch" class="form-label"><strong>Closing Time</strong></label>
                                        <input value="{{$restaurant->closing_time_lunch}}" type="text" class="form-control @if(!in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) time-picker @endif" id="closing_time_lunch" name="closing_time_lunch" placeholder="Select closing time" 
                                               @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) readonly @endif>
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
                                        <input value="{{$restaurant->opening_time_dinner}}" type="text" class="form-control @if(!in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) time-picker @endif" id="opening_time_dinner" name="opening_time_dinner" placeholder="Select opening time" 
                                               @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) readonly @endif>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="closing_time_dinner" class="form-label"><strong>Closing Time</strong></label>
                                        <input value="{{$restaurant->closing_time_dinner}}" type="text" class="form-control @if(!in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) time-picker @endif" id="closing_time_dinner" name="closing_time_dinner" placeholder="Select closing time" 
                                               @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) readonly @endif>
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
                                    @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140]))
                                        <div class="form-control" style="padding: 20px; border: 2px dashed #6c757d; text-align: center; height: 80px; background-color: #f8f9fa; color: #6c757d;">
                                            <i class="fas fa-lock"></i> File upload disabled (Read Only)
                                        </div>
                                    @else
                                        <div id="master-drop-area" class="form-control"
                                            style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px;">
                                            Drag & Drop your files here or click to upload.
                                            <input type="file" id="master_image" name="master_image" multiple
                                                style="display: none;">
                                        </div>
                                    @endif
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
                                    @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140]))
                                        <div class="form-control" style="padding: 20px; border: 2px dashed #6c757d; text-align: center; height: 80px; background-color: #f8f9fa; color: #6c757d;">
                                            <i class="fas fa-lock"></i> File upload disabled (Read Only)
                                        </div>
                                    @else
                                        <div id="drop-area" class="form-control"
                                            style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px;">
                                            Drag & Drop your files here or click to upload.
                                            <input type="file" id="images" name="images[]" multiple
                                                style="display: none;">
                                        </div>

                                        <div id="preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                            style="max-width: 100%; overflow-x: auto; white-space: nowrap;"></div>
                                    @endif
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
                    <textarea id="summernote" name="description" class="form-control" rows="10" 
                              @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) readonly @endif>{{ old('description', $restaurant->description) }}</textarea required>
                    @error('description')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remarks -->
                <div class="col-md-12 mb-3">
                    <label for="remarks" class="form-label"><strong>Remarks</strong></label>
                    <textarea id="remarks" name="remarks" class="form-control" rows="4" placeholder="Enter any remarks or notes (optional)" 
                              @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) readonly @endif>{{ old('remarks', $restaurant->remarks) }}</textarea>
                    @error('remarks')
                    <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Terms & Conditions -->
                <div class="col-md-12 mb-3">
                    <label for="terms_conditions" class="form-label"><strong>Terms & Conditions</strong><span class="text-danger">*</span></label>
                    <textarea id="terms_conditions" name="terms_conditions" class="form-control" rows="6" placeholder="Enter terms and conditions..." required 
                              @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) readonly @endif>{{ old('terms_conditions', $restaurant->terms_conditions) }}</textarea>
                    @error('terms_conditions')
                    <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Status -->
                <div class="mt-2 form-check form-switch">
                    <label for="restaurant_status" class="form-label"><strong>Status</strong></label>
                    <span style="color: red; font-weight: bold;">*</span>
                    <input {{$restaurant->is_active == 1 ? 'checked' : ''}} class="form-check-input" name="restaurant_status" type="checkbox" id="restaurant_status" required
                        value="1" @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140])) disabled @endif>
                    @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140]))
                        <input type="hidden" name="restaurant_status" value="{{ $restaurant->is_active }}">
                    @endif
                    <label class="form-check-label"></label>
                    @error('restaurant_status')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="d-flex gap-3 mt-4">
                    @if(in_array(auth()->user()->role_id, [11, 35, 78, 120, 139, 140]))
                        <button type="button" class="btn btn-secondary px-4" disabled>Update (Read Only)</button>
                    @else
                        <button type="submit" class="btn btn-primary px-4">Update</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End of the form -->
@endsection
@section('scripts') 
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        var userRoleId = {{ auth()->user()->role_id }};
        var readOnlyRoles = [11, 35, 78, 120, 139, 140];
        var isReadOnly = readOnlyRoles.includes(userRoleId);
        
        // Initialize Summernote editors
        if (isReadOnly) {
            // For readonly roles, disable Summernote editors
            $('#summernote').summernote('disable');
            $('#remarks').summernote('disable');
            $('#terms_conditions').summernote('disable');
        } else {
            // For editable roles, initialize normally
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
        }
        
        // Initialize Select2 for city (only select from existing cities)
        $('#citySelect').select2({
            placeholder: "Search and Select a City",
            allowClear: true,
            width: '100%',
            disabled: isReadOnly
        });
        
        // Initialize Select2 for Ownership
        $('#owned_by').select2({
            placeholder: "Search and Select Ownership",
            allowClear: true,
            width: '100%',
            disabled: isReadOnly
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var userRoleId = {{ auth()->user()->role_id }};
        var readOnlyRoles = [11, 35, 78, 120, 139, 140];
        var isReadOnly = readOnlyRoles.includes(userRoleId);
        
        // Function to initialize flatpickr for time inputs
        function initializeTimePickers(container) {
            if (isReadOnly) return; // Don't initialize time pickers for readonly roles
            
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
                
                // Reinitialize flatpickr for newly visible time inputs (only if not readonly)
                if (checkbox.checked && !isReadOnly) {
                    initializeTimePickers(fieldsDiv);
                }
            };
            
            // Add event listener for checkbox changes (only if not readonly)
            if (!isReadOnly) {
                checkbox.addEventListener('change', updateVisibility);
            }
            
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
        Array.from(newFiles).forEach(file => {
            if (file.type.startsWith('image/')) {
                files.push(file);
            } else {
                alert(`${file.name} is not a valid image file.`);
            }
        });
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

<script>
    $(document).ready(function() {
        // Assuming you have a way to get the user's role ID
        var userRoleId = {{ auth()->user()->role_id }}; // Adjust this line based on your authentication method

        // Check if the user role is one of the specified IDs
        if ([1, 2, 3, 4].includes(userRoleId)) {
            $('#dmc-container').show(); // Show the DMC select box
            $('#dmc').prop('required', true); // Set DMC as required
        } else {
            $('#dmc-container').hide(); // Hide the DMC select box
            $('#dmc').prop('required', false); // Remove required attribute
        }
    });
</script>

<!-- Validation Scripts -->
<script>
function showValidationMessage(inputElement, isValid, message) {
    const messageElement = document.getElementById(`${inputElement.id}-validation-message`);
    
    if (!messageElement) return;
    
    if (isValid) {
        messageElement.innerHTML = `
            <div class="valid-feedback d-block">
                <i class="fas fa-check-circle text-success"></i> 
                Looks good!
            </div>`;
        inputElement.classList.remove('is-invalid');
        inputElement.classList.add('is-valid');
    } else {
        messageElement.innerHTML = `
            <div class="invalid-feedback d-block">
                <i class="fas fa-exclamation-circle"></i> 
                ${message}
            </div>`;
        inputElement.classList.remove('is-valid');
        inputElement.classList.add('is-invalid');
    }
}

function validateNumericPrice(input) {
    // Allow only digits and decimal point
    input.value = input.value.replace(/[^\d.]/g, '');
    
    // Allow only one decimal point
    const decimalCount = (input.value.match(/\./g) || []).length;
    if (decimalCount > 1) {
        const parts = input.value.split('.');
        input.value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    const value = input.value.trim();
    const priceRegex = /^\d+(\.\d{1,2})?$/;  // Allows whole numbers or up to 2 decimal places
    
    if (value === '') {
        showValidationMessage(input, false, 'Price is required');
    } else if (!priceRegex.test(value)) {
        showValidationMessage(input, false, `
            Please enter a valid price:
            <ul class="mt-1 mb-0">
                <li>Must be a positive number</li>
                <li>Can have up to 2 decimal places</li>
                <li>Example: 99.99</li>
            </ul>
        `);
    } else {
        showValidationMessage(input, true, '');
    }
}

function validateLatitude(input) {
    // Force numeric input by immediately replacing non-numeric characters
    input.value = input.value.replace(/[^0-9.-]/g, '');
    
    // Allow only one decimal point and ensure minus sign is only at the beginning
    let value = input.value;
    
    // Ensure only one decimal point
    const decimalCount = (value.match(/\./g) || []).length;
    if (decimalCount > 1) {
        const parts = value.split('.');
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Ensure minus sign is only at the beginning
    if (value.lastIndexOf('-') > 0) {
        value = value.replace(/-/g, '');
        if (value.charAt(0) !== '-') {
            value = '-' + value;
        }
    }
    
    input.value = value;
    
    const latitudeRegex = /^-?([1-8]?[0-9]\.{1}\d{1,9}$|90\.{1}0{1,9}$)/;
    
    if (value === '') {
        showValidationMessage(input, false, 'Latitude is required');
    } else if (!latitudeRegex.test(value)) {
        showValidationMessage(input, false, `
            Please enter a valid latitude:
            <ul class="mt-1 mb-0">
                <li>Must be between -90 and 90 degrees</li>
                <li>Must include decimal point</li>
                <li>Up to 9 decimal places</li>
                <li>Example: 23.456789803</li>
            </ul>
        `);
    } else {
        showValidationMessage(input, true, '');
    }
}

function validateLongitude(input) {
    // Force numeric input by immediately replacing non-numeric characters
    input.value = input.value.replace(/[^0-9.-]/g, '');
    
    // Allow only one decimal point and ensure minus sign is only at the beginning
    let value = input.value;
    
    // Ensure only one decimal point
    const decimalCount = (value.match(/\./g) || []).length;
    if (decimalCount > 1) {
        const parts = value.split('.');
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Ensure minus sign is only at the beginning
    if (value.lastIndexOf('-') > 0) {
        value = value.replace(/-/g, '');
        if (value.charAt(0) !== '-') {
            value = '-' + value;
        }
    }
    
    input.value = value;
    
    const longitudeRegex = /^-?([1-9]?[0-9]\.{1}\d{1,9}$|1[0-7][0-9]\.{1}\d{1,9}$|180\.{1}0{1,9}$)/;
    
    if (value === '') {
        showValidationMessage(input, false, 'Longitude is required');
    } else if (!longitudeRegex.test(value)) {
        showValidationMessage(input, false, `
            Please enter a valid longitude:
            <ul class="mt-1 mb-0">
                <li>Must be between -180 and 180 degrees</li>
                <li>Must include decimal point</li>
                <li>Up to 9 decimal places</li>
                <li>Example: 78.123456789</li>
            </ul>
        `);
    } else {
        showValidationMessage(input, true, '');
    }
}

// Add CSS for validation messages and input styles
document.head.insertAdjacentHTML('beforeend', `
    <style>
        /* Base validation message styles */
        .validation-message {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        /* Error state styles */
        .validation-message .invalid-feedback {
            display: block;
            color: #e74c3c;
            background-color: #fef5f5;
            border-left: 3px solid #e74c3c;
            padding: 0.75rem 1rem;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            animation: slideIn 0.3s ease-in-out;
        }

        /* Success state styles */
        .validation-message .valid-feedback {
            display: block;
            color: #2ecc71;
            background-color: #f4fff6;
            border-left: 3px solid #2ecc71;
            padding: 0.75rem 1rem;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            animation: slideIn 0.3s ease-in-out;
        }

        /* List styles within validation messages */
        .validation-message ul {
            margin: 0.5rem 0 0 0;
            padding-left: 1.5rem;
            list-style-type: none;
        }

        .validation-message ul li {
            position: relative;
            padding: 0.2rem 0;
            color: #666;
        }

        .validation-message ul li::before {
            content: "•";
            color: #e74c3c;
            font-weight: bold;
            position: absolute;
            left: -1rem;
        }

        /* Icon styles */
        .validation-message i {
            margin-right: 0.5rem;
            font-size: 1rem;
        }

        /* Input field styles with validation icons */
        .form-control.is-valid {
            border-color: #2ecc71 !important;
            background-color: #fff !important;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%232ecc71' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .form-control.is-invalid {
            border-color: #e74c3c !important;
            background-color: #fff !important;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23e74c3c'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23e74c3c' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        /* Animation for validation messages */
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

        /* Hover effect for validation messages */
        .validation-message .invalid-feedback:hover,
        .validation-message .valid-feedback:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        /* Required field indicator */
        .required-field::after {
            content: "*";
            color: #e74c3c;
            margin-left: 4px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .validation-message {
                font-size: 0.8rem;
            }
            
            .validation-message .invalid-feedback,
            .validation-message .valid-feedback {
                padding: 0.5rem 0.75rem;
            }
        }

        /* Focus state styles */
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(46, 204, 113, 0.25);
            border-color: #2ecc71;
        }

        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25);
            border-color: #e74c3c;
        }

        .select2-container .select2-selection--single {
            height: 100% !important;
            line-height: 100% !important;
            padding: 8px 12px;
        }
        .select2-container .select2-results__option {
            padding: 12px 10px;
        }
    </style>
`);
</script>

@endsection