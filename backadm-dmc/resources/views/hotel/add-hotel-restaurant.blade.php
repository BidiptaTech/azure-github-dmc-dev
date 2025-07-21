@extends('layouts.layout')
@section('content')
@include('hotel.tapview', ['hotel' => $hotel])
@extends('layouts.datatablecss')

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* Navigation Pills Styling */
    .nav-pills .nav-link {
        border-radius: 50px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
        margin: 0 8px;
        color: #6c757d;
        background-color: #f8f9fa;
        border: 2px solid transparent;
    }
    
    .nav-pills .nav-link:hover {
        background-color: #e9ecef;
        color: #495057;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .nav-pills .nav-link.active {
        background-color: #696cff;
        color: white;
        box-shadow: 0 4px 12px rgba(105, 108, 255, 0.3);
    }
    
    .nav-pills .nav-link.disabled {
        background-color: #f8f9fa;
        color: #adb5bd;
        cursor: not-allowed;
        opacity: 0.6;
    }
    
    .nav-pills .nav-link.disabled:hover {
        transform: none;
        box-shadow: none;
    }
    
    .nav-pills .nav-link i {
        font-size: 1.1rem;
    }
    
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
        height: 20px !important;
        width: 20px !important;
        font-size: 0.9375rem !important;
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
        <!-- Navigation Pills for Restaurant and Meals -->
        <ul class="nav nav-pills mb-4 mt-4 d-flex justify-content-center" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ request()->routeIs('hotel-restaurant-create') ? 'active' : '' }}" 
                   href="{{ route('hotel-restaurant-create', $hotel->hotel_unique_id) }}" 
                   role="tab">
                    <i class="fas fa-utensils me-2"></i>Restaurant
                </a>
            </li>
            
            <li class="nav-item" role="presentation">
                @if(isset($restaurants) && count($restaurants) > 0 && $userDMC)
                    <a class="nav-link {{ request()->routeIs('meals.create') || request()->routeIs('meals.edit') ? 'active' : '' }}" 
                       href="{{ route('hotel-meals-create', ['dmc_id' => $userDMC->userId, 'hotel_id' => $hotel->hotel_unique_id]) }}" 
                       role="tab">
                        <i class="fas fa-hamburger me-2"></i>Meals
                    </a>
                @else
                    <a class="nav-link disabled" 
                       href="javascript:void(0);" 
                       role="tab"
                       title="Save this restaurant first before adding meals">
                        <i class="fas fa-hamburger me-2"></i>Meals
                        <small class="d-block" style="font-size: 0.75rem;">Save restaurant first</small>
                    </a>
                @endif
            </li>
        </ul>
        <x-alert />
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Add New Hotel Restaurant
            </h5>
            <form id="restaurantForm" method="POST" action="{{ route('hotel-restaurant-store', $hotel->hotel_unique_id) }}"
                enctype="multipart/form-data" class="card-body">
                @csrf
                <!-- Hidden Fields -->
                <input type="hidden" name="hotel_id" value="{{ $hotel->hotel_unique_id }}">
                <div id="restaurantDetailsContainer">
                    <div class="restaurant-form">
                        <div class="row">

                        <!-- Hidden DMC ID -->
                            @if($userDMC)
                            <input type="hidden" name="dmc" value="{{$userDMC->userId}}">
                            @endif

                            <!-- Country -->
                            <div class="mb-3 col-md-3">
                                <label for="country" class="form-label"><strong>Country</strong>
                                    <span style="color: red; font-weight: bold;">*</span>
                                </label>
                                <input type="text" class="form-control" id="country"
                                value="{{ $userDMC ? $userDMC->country : '' }}"
                                    placeholder="{{ auth()->user()->role_id == 11 ? 'Your country' : 'Select DMC First' }}" 
                                    name="country" required 
                                    {{ auth()->user()->role_id == 11 ? 'readonly' : 'readonly' }}>
                                @error('country')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- City -->
                            <div class="col-md-3 mb-3">
                                <label for="city" class="form-label"><strong>City</strong><span class="text-danger">*</span></label>
                                <select name="city" id="citySelect" class="form-control" required 
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                    <option value="">Select City</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->name }}">{{ $city->name }}</option>
                                        @endforeach
                                </select>
                                @error('location')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                             <!-- Restaurant Name -->
                             <div class="col-md-3 mb-3">
                                <label for="name" class="form-label"><strong>Restaurant Name</strong><span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name"
                                    placeholder="Enter Restaurant Name" required
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                @error('name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Latitude -->
                            <div class="col-md-3 mb-3">
                                <label for="latitude" class="form-label">
                                    <strong>Latitude</strong><span class="text-danger">*</span>
                                </label>
                                <input name="latitude" type="text" id="latitude" class="form-control"
                                    placeholder="Enter Latitude" required
                                    oninput="validateLatitude(this)"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
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
                                <input name="longitude" type="text" id="longitude" class="form-control"
                                    placeholder="Enter Longitude" required
                                    oninput="validateLongitude(this)"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message text-danger" id="longitude-validation-message"></small>
                                @error('longitude')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cuisine Type -->
                            <div class="col-md-3 mb-3">
                                <label for="cuisine" class="form-label"><strong>Cuisine</strong><span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="cuisine"
                                    placeholder="Enter Cuisine Type" required
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                @error('cuisine')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                           

                            <div class="col-md-3 mb-3">
                                <label for="property" class="form-label"><strong>Property</strong><span
                                        class="text-danger">*</span></label>
                                <select name="property" class="form-select" 
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                    <option value="">Select</option>
                                    <option value="third_party">Third Party</option>
                                    <option value="owner">Ownership</option>
                                </select>
                                @error('property')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Meal Availability -->
                        <div class="row">
                            <!-- Breakfast -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label"><strong>Breakfast Availability</strong></label>
                                <div class="form-check form-switch">
                                    <input id="breakfastToggle" class="form-check-input" type="checkbox"
                                        name="breakfast_available" value="1"
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                    <label class="form-check-label">Available</label>
                                </div>
                                {{-- <div id="breakfastFields" class="row mt-2 d-none">
                                    <div class="col-md-3">
                                        <label for="opening_time_bf" class="form-label"><strong>Opening
                                                Time</strong></label>
                                        <input type="time" class="form-control" name="opening_time_bf">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="closing_time_bf" class="form-label"><strong>Closing
                                                Time</strong></label>
                                        <input type="time" class="form-control" name="closing_time_bf">
                                    </div>
                                    <!-- Breakfast Price -->
                                    <div class="col-md-3 mb-3">
                                        <label for="breakfast_price" class="form-label"><strong>Breakfast Price</strong><span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="breakfast_price" name="breakfast_price" 
                                               placeholder="Enter Breakfast Price" oninput="validateNumericPrice(this)">
                                        <small class="validation-message text-danger" id="breakfast_price-validation-message"></small>
                                        @error('breakfast_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div> --}}

                                <div id="breakfastFields" class="row mt-2 d-none">
                                    <div class="col-md-3">
                                        <label for="opening_time_bf" class="form-label"><strong>Opening Time</strong></label>
                                        <input type="text" class="form-control time-picker" id="opening_time_bf" name="opening_time_bf" placeholder="Select opening time"
                                            @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="closing_time_bf" class="form-label"><strong>Closing Time</strong></label>
                                        <input type="text" class="form-control time-picker" id="closing_time_bf" name="closing_time_bf" placeholder="Select closing time"
                                            @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                    </div>
                                    <!-- Breakfast Price (unchanged) -->
                                    {{-- <div class="col-md-3 mb-3">
                                        <label for="breakfast_price" class="form-label"><strong>Breakfast Price</strong><span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="breakfast_price" name="breakfast_price" 
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
                                    <input id="lunchToggle" class="form-check-input" type="checkbox"
                                        name="lunch_available" value="1"
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                    <label class="form-check-label">Available</label>
                                </div>
                                {{-- <div id="lunchFields" class="row mt-2 d-none">
                                    <div class="col-md-3">
                                        <label for="opening_time_lunch" class="form-label"><strong>Opening
                                                Time</strong></label>
                                        <input type="time" class="form-control" name="opening_time_lunch">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="closing_time_lunch" class="form-label"><strong>Closing
                                                Time</strong></label>
                                        <input type="time" class="form-control" name="closing_time_lunch">
                                    </div>
                                    <!-- Lunch Price -->
                                    <div class="col-md-3 mb-3">
                                        <label for="lunch_price" class="form-label"><strong>Lunch Price</strong><span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="lunch_price" name="lunch_price" 
                                               placeholder="Enter Lunch Price" oninput="validateNumericPrice(this)">
                                        <small class="validation-message text-danger" id="lunch_price-validation-message"></small>
                                        @error('lunch_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div> --}}

                                <div id="lunchFields" class="row mt-2 d-none">
                                    <div class="col-md-3">
                                        <label for="opening_time_lunch" class="form-label"><strong>Opening Time</strong></label>
                                        <input type="text" class="form-control time-picker" id="opening_time_lunch" name="opening_time_lunch" placeholder="Select opening time"
                                            @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="closing_time_lunch" class="form-label"><strong>Closing Time</strong></label>
                                        <input type="text" class="form-control time-picker" id="closing_time_lunch" name="closing_time_lunch" placeholder="Select closing time"
                                            @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                    </div>
                                    <!-- Lunch Price (unchanged) -->
                                    {{-- <div class="col-md-3 mb-3">
                                        <label for="lunch_price" class="form-label"><strong>Lunch Price</strong><span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="lunch_price" name="lunch_price" 
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
                                    <input id="dinnerToggle" class="form-check-input" type="checkbox"
                                        name="dinner_available" value="1"
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                    <label class="form-check-label">Available</label>
                                </div>
                                {{-- <div id="dinnerFields" class="row mt-2 d-none">
                                    <div class="col-md-3">
                                        <label for="opening_time_dinner" class="form-label"><strong>Opening
                                                Time</strong></label>
                                        <input type="time" class="form-control" name="opening_time_dinner">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="closing_time_dinner" class="form-label"><strong>Closing
                                                Time</strong></label>
                                        <input type="time" class="form-control" name="closing_time_dinner">
                                    </div>
                                    <!-- Dinner Price -->
                                    <div class="col-md-3 mb-3">
                                        <label for="dinner_price" class="form-label"><strong>Dinner Price</strong><span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="dinner_price" name="dinner_price" 
                                               placeholder="Enter Dinner Price" oninput="validateNumericPrice(this)">
                                        <small class="validation-message text-danger" id="dinner_price-validation-message"></small>
                                        @error('dinner_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div> --}}

                                <div id="dinnerFields" class="row mt-2 d-none">
                                    <div class="col-md-3">
                                        <label for="opening_time_dinner" class="form-label"><strong>Opening Time</strong></label>
                                        <input type="text" class="form-control time-picker" id="opening_time_dinner" name="opening_time_dinner" placeholder="Select opening time"
                                            @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="closing_time_dinner" class="form-label"><strong>Closing Time</strong></label>
                                        <input type="text" class="form-control time-picker" id="closing_time_dinner" name="closing_time_dinner" placeholder="Select closing time"
                                            @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                    </div>
                                    <!-- Dinner Price (unchanged) -->
                                    {{-- <div class="col-md-3 mb-3">
                                        <label for="dinner_price" class="form-label"><strong>Dinner Price</strong><span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="dinner_price" name="dinner_price" 
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
                                            Image</strong><span
                                            style="color: red; font-weight: bold;">*</span></label>
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20)
                                        <div class="form-control" style="padding: 20px; border: 2px dashed #ccc; text-align: center; height: 80px; background-color: #f8f9fa; color: #6c757d; cursor: not-allowed;">
                                            <i class="fas fa-lock"></i> Image upload restricted for your role
                                        </div>
                                    @else
                                        <div id="master-drop-area" class="form-control"
                                            style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px;">
                                            Drag & Drop your files here or click to upload.
                                            <input type="file" id="master_image" name="master_image"
                                                style="display: none;" required>
                                        </div>
                                    @endif
                                </div>
                                <div id="master-preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                    style="max-width: 30%; overflow-x: auto; white-space: nowrap;"></div>
                            </div>

                            <!-- Additional Image drop -->
                            <div class="mt-3 mb-3 col-md-8">
                                <div>
                                    <label for="images" class="form-label"><strong>Additional
                                        Images</strong></label>
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20)
                                        <div class="form-control" style="padding: 20px; border: 2px dashed #ccc; text-align: center; height: 80px; background-color: #f8f9fa; color: #6c757d; cursor: not-allowed;">
                                            <i class="fas fa-lock"></i> Additional image upload restricted for your role
                                        </div>
                                    @else
                                        <div id="drop-area" class="form-control"
                                            style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px;">
                                            Drag & Drop your files here or click to upload.
                                            <input type="file" id="images" name="images[]" multiple style="display: none;">
                                        </div>
                                    @endif

                                    <div id="preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                        style="max-width: 100%; overflow-x: auto; white-space: nowrap;">
                                    </div>
                                </div>
                                <input type="file" name="all_images[]" id="all-images"
                                    style="display: none;">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="property" class="form-label"><strong>Description</strong><span
                                class="text-danger">*</span></label>
                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20)
                            <div class="alert alert-info">
                                <i class="fas fa-eye"></i> <strong>View Only Mode:</strong> Description editing is restricted for your role.
                            </div>
                        @endif
                        <textarea id="summernote" name="description" class="form-control" rows="10"
                            placeholder="Write Description..."
                            @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif></textarea>
                        @error('description')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Remarks -->
                    <div class="col-md-12 mb-3">
                        <label for="remarks" class="form-label"><strong>Remarks</strong></label>
                        <textarea id="remarks" name="remarks" class="form-control" rows="4" placeholder="Enter any remarks or notes (optional)"
                            @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif></textarea>
                        @error('remarks')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="col-md-12 mb-3">
                        <label for="terms_conditions" class="form-label"><strong>Terms & Conditions</strong><span class="text-danger">*</span></label>
                        <textarea id="terms_conditions" name="terms_conditions" class="form-control" rows="6" placeholder="Enter terms and conditions..." required
                            @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif></textarea>
                        @error('terms_conditions')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="restaurant_status" value="0">
                                <input class="form-check-input" name="restaurant_status" type="checkbox" id="restaurant_status"
                                    value="1"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                <label for="restaurant_status" class="form-check-label"><strong>Status</strong></label>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-3 mt-4">
                        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                            <button type="submit" class="btn btn-primary px-4">Save</button>
                        @else
                            <button type="button" class="btn btn-secondary px-4" disabled>
                                <i class="fas fa-lock"></i> Save Restricted
                            </button>
                            <small class="text-muted mt-2">
                                <i class="fas fa-info-circle"></i> You don't have permission to save restaurant data. Contact your administrator.
                            </small>
                        @endif
                    </div>
            </form>
        </div>
    </div>
</div>
<!-- End of the form -->

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Restaurant Listing</h5>
                    </div>
                    <div class="d-flex justify-content-between gap-3">

                        <!-- Export Dropdown Button -->
                        <div class="dropdown">
                            <button class="btn btn-warning btn-sm dropdown-toggle" type="button" id="exportDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportCopy">Copy</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportCSV">CSV</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportExcel">Excel</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportPDF">PDF</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportPrint">Print</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <x-alert />
                
                    <table class="datatables-basic table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                @php
                                    $roleId = auth()->user()->role_id;
                                @endphp

                                @if($roleId == 10)
                                    <th>DMC</th>
                                @elseif($roleId != 11)
                                    <th>Master Dmc</th>
                                    <th>DMC</th>
                                @endif
                                <th>Cuisine</th>
                                <th>BreakFast</th>
                                <th>Lunch</th>
                                <th>Dinner</th>
                                <th>Owned_By</th>
                                <th>Calendar</th>
                                <th>Status</th>
                                {{-- @if(hasPermission('edit restaurant') || hasPermission('delete restaurant')) --}}
                                    <th>Action</th>
                                {{-- @endif --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($restaurants as $key => $restaurant)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td class="category-name">{{ $restaurant->name }}</td>
                                    @php
                                        $roleId = auth()->user()->role_id;
                                    @endphp

                                    @if($roleId == 10)
                                        @php
                                            $dmcUser = App\Models\User::where('userId', $restaurant->dmc_id)->first();
                                        @endphp
                                        <td>{{ $dmcUser ? $dmcUser->company_name : 'N/A' }}</td>
                                    @elseif($roleId != 11)
                                        @php
                                            $dmcUser = App\Models\User::where('userId', $restaurant->dmc_id)->first();
                                            $masterdmcUser = $dmcUser ? App\Models\User::where('userId', $dmcUser->master_dmc_id)->first() : null;
                                        @endphp
                                        <td>{{ $masterdmcUser ? $masterdmcUser->company_name : 'N/A' }}</td>
                                        <td>{{ $dmcUser ? $dmcUser->company_name : 'N/A' }}</td>
                                    @endif
                                    <td>
                                        {{ $restaurant->cuisine }}
                                    </td>
                                    <td>
                                    @if($restaurant->breakfast_available == 1)
                                        <span class="badge bg-success">Available</span>
                                        @else
                                            <span class="badge bg-danger">Not Available</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($restaurant->lunch_available == 1)
                                        <span class="badge bg-success">Available</span>
                                        @else
                                        <span class="badge bg-danger">Not Available</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($restaurant->dinner_available == 1)
                                        <span class="badge bg-success">Available</span>
                                        @else
                                        <span class="badge bg-danger">Not Available</span>
                                        @endif
                                    </td>
                                    <td>
                                        <p>{{$restaurant->property}}</p>
                                    </td>
                                    <td> 
                                        <a href="{{ route('restaurant.calendar', $restaurant->restaurant_id) }}" target="_blank"><i class="fa fa-calendar-alt"></i>View Calendar</a></td>
                                    </td>
                                    <td>
                                        @if($restaurant->is_active == 1)
                                            <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        {{-- @if(hasPermission('edit restaurant') || hasPermission('delete restaurant')) --}}
                                            @if($restaurant->status == 1)
                                                <td style="display: inline-block; white-space: nowrap;">
                                                    <!-- Edit Button -->
                                                    
                                                    <a href="{{ route('hotel-restaurant-edit', $restaurant->restaurant_id) }}"
                                                    class="btn btn-primary btn-sm rounded-circle waves-effect waves-light" 
                                                    style="min-width: 28px; min-height: 28px; padding: 0;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                                            <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/>
                                                        </svg>
                                                    </a>
                                                   
                                                    @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 20)
                                                    <!-- Delete Button -->
                                                    {{-- @if(hasPermission('delete restaurant')) --}}
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm rounded-circle waves-effect waves-light" 
                                                            style="min-width: 28px; min-height: 28px; padding: 0;" 
                                                            data-toggle="modal" 
                                                            data-target="#deleteModal" 
                                                            onclick="setDeleteForm('{{ route('hotel-restaurant-destroy', $restaurant->restaurant_id) }}')" fdprocessedid="ra9z3">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                                            <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                                        </svg>
                                                    </button>
                                                    {{-- @endif --}}
                                                    @endif
                                                </td>

                                                @else

                                                {{-- @if(Auth::user()->role_id == 11)
                                                <td>
                                                    @if($restaurant->status == 2)
                                                        <span>Pending approval from the A.M</span>
                                                    @elseif($restaurant->status == 4)
                                                        <span>Awaiting S.M approval</span>
                                                    @elseif($restaurant->status == 5)
                                                        <span>Awaiting Admin approval</span>
                                                    @elseif($restaurant->status == 3)
                                                        <span>Declined</span>
                                                    @endif
                                                </td>
                                                @endif  
                                                @if(Auth::user()->role_id == 4)
                                                    <td>
                                                        @if($restaurant->status == 4)
                                                            <span>Awaiting S.M approval</span>
                                                        @elseif($restaurant->status == 5)
                                                            <span>Awaiting Admin approval</span>
                                                        @elseif($restaurant->status == 3)
                                                            <span>Declined</span>
                                                        @endif
                                                    </td>
                                                @endif

                                                @if(Auth::user()->role_id == 3)
                                                    <td>
                                                        @if($restaurant->status == 5)
                                                            <span>Awaiting Admin approval</span>
                                                        @elseif($restaurant->status == 3)
                                                            <span>Declined</span>
                                                        @endif
                                                    </td>
                                                @endif --}}

                                                <td>
                                                    @if($restaurant->status == 5)
                                                        <span>Your Restaurant, awaiting for Admin approval</span>
                                                    @elseif($restaurant->status == 3)
                                                        <span>Declined</span>
                                                    @endif
                                                </td>
                                            @endif
                                        {{-- @endif --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
            </div>
        </div>
    </div>
</div>
        <!-- Restaurant Delete Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" Category="dialog" 
             aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog" Category="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirmation</h5>
                    </div>
                    <div class="modal-body">
                        Are you sure want to delete?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <form id="deleteForm" action="" method="POST" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal -->
<!-- Navigation Tab Enhancement Script -->
<script>
$(document).ready(function() {
    // Handle clicks on disabled meals tab
    $('.nav-link.disabled').on('click', function(e) {
        e.preventDefault();
        
        // Show a more informative tooltip/alert
        var message = $(this).attr('title');
        
        // Create a temporary tooltip-like notification
        if (message) {
            // Remove any existing notifications
            $('.tab-notification').remove();
            
            var notification = $('<div class="alert alert-info alert-dismissible fade show tab-notification" style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 300px;">' +
                '<i class="fas fa-info-circle me-2"></i>' + message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>');
            
            $('body').append(notification);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                notification.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
    });
    
    // Add hover effect for better visual feedback
    $('.nav-link.disabled').hover(
        function() {
            $(this).css('opacity', '0.8');
        },
        function() {
            $(this).css('opacity', '0.6');
        }
    );
});
</script>

@endsection

@section('scripts')

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js"></script>
<script>
    $(document).ready(function() {
        // Assuming you have a way to get the user's role ID
        var userRoleId = {{ auth()->user()->role_id }}; // Adjust this line based on your authentication method

        // No special handling needed anymore since DMC section is removed
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Function to initialize flatpickr for time inputs
        function initializeTimePickers() {
            // Configure all time pickers
            const timePickerConfig = {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i", // 24-hour format
                time_24hr: true,
                minuteIncrement: 15
            };
            
            // Initialize all elements with the time-picker class
            document.querySelectorAll('.time-picker').forEach(function(input) {
                flatpickr(input, timePickerConfig);
            });
        }
        
        // Initialize time pickers when the page loads
        initializeTimePickers();
        
        // Show/hide meal availability sections
        const toggleVisibility = (checkboxId, fieldId) => {
            const checkbox = document.getElementById(checkboxId);
            const fieldsDiv = document.getElementById(fieldId);
            
            // Update visibility on toggle
            checkbox.addEventListener('change', function() {
                fieldsDiv.classList.toggle('d-none', !this.checked);
                
                // Reinitialize flatpickr for newly visible time inputs
                if (this.checked) {
                    fieldsDiv.querySelectorAll('.time-picker').forEach(function(input) {
                        // Only initialize if not already initialized
                        if (!input._flatpickr) {
                            flatpickr(input, {
                                enableTime: true,
                                noCalendar: true,
                                dateFormat: "H:i",
                                time_24hr: true,
                                minuteIncrement: 15
                            });
                        }
                    });
                }
            });
        };
        
        // Set up toggle functionality
        toggleVisibility('breakfastToggle', 'breakfastFields');
        toggleVisibility('lunchToggle', 'lunchFields');
        toggleVisibility('dinnerToggle', 'dinnerFields');
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggleVisibility = (checkboxId, fieldId) => {
        document.getElementById(checkboxId).addEventListener('change', function() {
            document.getElementById(fieldId).classList.toggle('d-none', !this.checked);
        });
    };
    toggleVisibility('breakfastToggle', 'breakfastFields');
    toggleVisibility('lunchToggle', 'lunchFields');
    toggleVisibility('dinnerToggle', 'dinnerFields');
});
</script>

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('#hotelSelect').select2({
            placeholder: "Search and Select a Hotel",
            allowClear: true,
            width: '100%'
        });
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
                <li>Example: 78.123456658</li>
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
    </style>
`);
</script>

<!-- <script>
$(document).ready(function() {
    // Initialize Select2 for city
    $('#citySelect').select2({
        placeholder: "Search and Select a City",
        allowClear: true,
        tags: true,
        width: '100%'
    });

    // When DMC is changed
    $('#dmc').change(function() {
        var dmcId = $(this).val();
        $('#citySelect').empty().trigger('change');

        if (dmcId) {
            // Show loading state
            $('#citySelect').append('<option value="">Loading cities...</option>').trigger('change');

            $.ajax({
                url: "{{ route('fetch.cities_countries') }}",
                type: "GET",
                data: { dmc_id: dmcId },
                success: function(response) {
                    // Clear loading state
                    $('#citySelect').empty();
                    
                    // Add default option
                    $('#citySelect').append('<option value="">Select or type a city</option>');
                    
                    // Add cities from response
                    $.each(response.cities, function(key, city) {
                        $('#citySelect').append('<option value="' + city.name + '">' + city.name + '</option>');
                    });
                    $('#country').val(response.country);

                    // Trigger change to refresh Select2
                    $('#citySelect').trigger('change');
                },
                error: function() {
                    $('#citySelect').empty();
                    $('#citySelect').append('<option value="">Error loading cities</option>');
                    $('#citySelect').trigger('change');
                }
            });
        } else {
            $('#citySelect').append('<option value="">Select a DMC first</option>').trigger('change');
            $('#country').val('');
        }
    });
});
</script> -->


<script>
        $(document).ready(function() {
        // Get the user's role ID
        var userRoleId = {{ auth()->user()->role_id }};
        // Get the current user's country if they are a DMC
        var userCountry = "{{ auth()->user()->role_id == 11 ? auth()->user()->country : '' }}";
        var dmcId = "{{ auth()->user()->role_id == 11 ? auth()->user()->userId : '' }}";
        
        // // Initialize Select2 for city
        $('#citySelect').select2({
            placeholder: "Search and Select a City",
            allowClear: true,
            tags: true,
            width: '100%'
        });
        
        // Check if the user role is DMC (role_id = 11)
        if (userRoleId == 11) {
            // Auto-fill the country field with the DMC's country
            $('#country').val(userCountry);
            
            // Load cities for this DMC
            loadCitiesForDmc(dmcId);
        }
        
        // Function to load cities for DMC
        function loadCitiesForDmc(dmcId) {
            if (dmcId) {
                // Show loading state
                $('#citySelect').empty().append('<option value="">Loading cities...</option>').trigger('change');
                
                // Add a debug statement
                console.log("Loading cities for DMC ID:", dmcId);
                
                $.ajax({
                    url: "{{ route('fetch.cities_countries') }}",
                    type: "GET",
                    data: { dmc_id: dmcId },
                    dataType: 'json',
                    success: function(response) {
                        console.log("Response received:", response);
                        
                        // Clear loading state
                        $('#citySelect').empty();
                        
                        // Add default option
                        $('#citySelect').append('<option value="">Select or type a city</option>');
                        
                        // Add cities from response
                        if (response.cities && response.cities.length > 0) {
                            $.each(response.cities, function(key, city) {
                                $('#citySelect').append('<option value="' + city.name + '">' + city.name + '</option>');
                            });
                        }
                        
                        // Set the country value
                        if (response.country) {
                            $('#country').val(response.country);
                        }

                        // Trigger change to refresh Select2
                        $('#citySelect').trigger('change');
                    },
                    error: function(xhr, status, error) {
                        console.error("Error loading cities:", error);
                        console.log("XHR Status:", xhr.status);
                        console.log("Response:", xhr.responseText);
                        
                        $('#citySelect').empty();
                        $('#citySelect').append('<option value="">Error loading cities</option>');
                        $('#citySelect').trigger('change');
                    }
                });
            }
        }
    });
</script>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
    function setDeleteForm(action) {
        document.getElementById('deleteForm').action = action;
    }
</script>

<!-- Access Control JavaScript Protection -->
<script>
$(document).ready(function() {
    var userRoleId = {{ auth()->user()->role_id }};
    
    // Check if user is unauthorized (not role_id 1 or 20)
    if (userRoleId != 1 && userRoleId != 20) {
        
        // Disable Summernote editors for unauthorized users
        if (typeof $('#summernote').summernote === 'function') {
            $('#summernote').summernote('disable');
        }
        if (typeof $('#remarks').summernote === 'function') {
            $('#remarks').summernote('disable');
        }
        if (typeof $('#terms_conditions').summernote === 'function') {
            $('#terms_conditions').summernote('disable');
        }
        
        // Block form submission
        $('#restaurantForm').on('submit', function(e) {
            e.preventDefault();
            alert('You do not have permission to save restaurant data. Contact your administrator.');
            return false;
        });
        
        // Disable drag and drop functionality
        $('#master-drop-area, #drop-area').off('click dragover dragleave drop');
        
        // Disable time picker functionality for unauthorized users
        $('.time-picker').each(function() {
            if (this._flatpickr) {
                this._flatpickr.destroy();
            }
        });
        
        // Block checkbox toggle events
        $('#breakfastToggle, #lunchToggle, #dinnerToggle, #restaurant_status').off('change');
        
        // Add visual feedback for readonly mode
        $('body').addClass('readonly-mode');
        
        // Show restriction message for file uploads
        $('#master-drop-area, #drop-area').css({
            'cursor': 'not-allowed',
            'opacity': '0.6'
        });
        
        console.log('Restaurant form in readonly mode for user role:', userRoleId);
    }
});
</script>

<!-- CSS for readonly mode styling -->
<style>
.readonly-mode input[readonly],
.readonly-mode select[disabled],
.readonly-mode textarea[readonly] {
    background-color: #f8f9fa !important;
    opacity: 0.8;
    cursor: not-allowed;
}

.readonly-mode .form-check-input[disabled] {
    opacity: 0.6;
    cursor: not-allowed;
}

.readonly-mode .btn[disabled] {
    opacity: 0.6;
    cursor: not-allowed;
}

.readonly-mode .note-editable {
    background-color: #f8f9fa !important;
    color: #6c757d !important;
}
</style>

@endsection