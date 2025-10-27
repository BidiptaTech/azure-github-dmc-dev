@extends('layouts.layout')
@section('title', 'Edit Hotel')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    /* Reduce the width of the time selector popup */
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

    /* For Select2 Dropdown */
    .select2-container .select2-selection--single {
        height: 100% !important; /* Adjust as needed */
        line-height: 100% !important;
        padding: 8px 12px;
    }
    /* Increase the height of the dropdown items */
    .select2-container .select2-results__option {
        padding: 12px 10px;
    }
    
    /* Read-only mode styling */
    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20)
    .readonly-mode {
        position: relative;
    }
    
    .readonly-mode::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, transparent 40%, rgba(255, 193, 7, 0.1) 50%, transparent 60%);
        pointer-events: none;
        z-index: 1;
    }
    
    .form-control[readonly],
    .form-control[disabled],
    .form-select[disabled] {
        background-color: #f8f9fa !important;
        border-color: #e9ecef !important;
        color: #6c757d !important;
        opacity: 0.8;
        cursor: not-allowed;
        position: relative;
    }
    
    .form-control[readonly]::after,
    .form-control[disabled]::after {
        content: '\f023';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #ffc107;
        font-size: 12px;
        z-index: 2;
    }
    @endif
    
    /* Lock icon animation */
    @keyframes lockPulse {
        0% { opacity: 0.7; }
        50% { opacity: 1; }
        100% { opacity: 0.7; }
    }
    
    .lock-icon {
        animation: lockPulse 2s infinite;
    }
</style>

<div class="page-content mt-5">
    <!-- Added margin-top -->
    <div class="page-container">
        <div class="row justify-content-center">
            <div class="col-lg-12 col-md-10 col-sm-12">
                <div class="card">
                    @include('hotel.tapview', ['hotel' => $hotel])
                    <div class="card mb-6">
                        <h5 class="card-header d-flex justify-content-between align-items-center">
                            @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20)
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-lock text-warning me-2 lock-icon" style="font-size: 1.2rem;" 
                                       data-bs-toggle="tooltip" data-bs-placement="top" 
                                       title="Read-only mode: You don't have permission to edit this hotel"></i>
                                    <span>View Hotel Details</span>
                                    <span class="badge bg-warning ms-2 px-2 py-1" style="font-size: 0.75rem;">
                                        <i class="fas fa-eye me-1"></i>Read Only
                                    </span>
                                </div>
                            @else
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-edit text-success me-2" style="font-size: 1.2rem;"></i>
                                    <span>Edit Hotel</span>
                                </div>
                            @endif
                            {{-- <a href="{{ route('hotels.index') }}" class="btn btn-sm btn-outline-danger">
                                <i class="mdi mdi-arrow-left"></i> Back
                            </a> --}}
                        </h5>
                    </div>
                    
                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20)
                        <!-- Read-Only Mode Alert -->
                        <div class="alert alert-warning alert-dismissible fade show mx-4 mt-3" role="alert" style="border-left: 4px solid #ffc107;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shield-alt me-2" style="font-size: 1.5rem;"></i>
                                <div>
                                    <h6 class="alert-heading mb-1">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Access Restricted - Read Only Mode
                                    </h6>
                                    <p class="mb-0">
                                        You are viewing this hotel in <strong>read-only mode</strong>. 
                                        Only users with administrative privileges (Admin) can modify hotel information.
                                    </p>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    <div class="card-body p-4">
                        <form id="hotelForm" method="POST"
                            action="{{ route('hotels.update', $hotel->hotel_unique_id) }}"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <!-- Hidden input to track removed images -->
                            <input type="hidden" name="removed_images" id="removed_images" value="">
                            <input type="hidden" name="removed_master_image" id="removed_master_image" value="">
                            <div class="row">
                                <!-- Hotel Name -->
                                <div class="mb-3 col-md-4">
                                    <label for="input35" class="form-label"><strong>Hotel Name</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="input35" name="name"
                                        value="{{ old('name', $hotel->name) }}" placeholder="Enter Hotel Name" 
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif required>
                                    @error('name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label for="input35" class="form-label"><strong>Hotel Unique Id</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="input35"
                                        value="{{ $hotel->display_id }}" disabled readonly>
                                    @error('unique_id')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror

                                </div>

                                <!-- Category Type -->
                                <!-- <div class="mb-3 col-md-4">
                                    <label for="category_type" class="form-label"><strong>Category Type</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <select id="category_type" name="category_type" class="form-control" required>
                                        <option value="">Select Category Type</option>
                                        @foreach ($hotel_categories as $category)
                                        <option value="{{ $category->category_id }}"
                                            {{ old('category_type', $hotel->cat_id) == $category->category_id ? 'selected' : '' }}>
                                            {{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_type')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div> -->
                                

                                <!-- Accommodation Type -->
                                <div class="col-md-4 mb-3">
                                    <label for="hotelCategory" class="form-label"><strong>Accomodations Type</strong><span class="text-danger">*</span></label>
                                    <select name="hotel_category" id="hotelCategory" class="form-control" required
                                            @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                        <option value="">Select a Category</option>
                                        <option value="1" {{ $hotel->accomodation_type == 1 ? 'selected' : '' }}>Hotel</option>
                                        <option value="2" {{ $hotel->accomodation_type == 2 ? 'selected' : '' }}>Motel</option>
                                        <option value="3" {{ $hotel->accomodation_type == 3 ? 'selected' : '' }}>Resort</option>
                                        <option value="4" {{ $hotel->accomodation_type == 4 ? 'selected' : '' }}>Bed and Breakfast (B&amp;B)</option>
                                        <option value="5" {{ $hotel->accomodation_type == 5 ? 'selected' : '' }}>Hostels</option>
                                        <option value="6" {{ $hotel->accomodation_type == 6 ? 'selected' : '' }}>Serviced Apartments / Aparthotels</option>
                                        <option value="7" {{ $hotel->accomodation_type == 7 ? 'selected' : '' }}>Guesthouses</option>
                                        <option value="8" {{ $hotel->accomodation_type == 8 ? 'selected' : '' }}>Vacation Rentals</option>
                                        <option value="9" {{ $hotel->accomodation_type == 9 ? 'selected' : '' }}>Boutique Hotels</option>
                                        <option value="10" {{ $hotel->accomodation_type == 10 ? 'selected' : '' }}>Lodges</option>
                                        <option value="11" {{ $hotel->accomodation_type == 11 ? 'selected' : '' }}>Homestays</option>
                                        <option value="12" {{ $hotel->accomodation_type == 12 ? 'selected' : '' }}>Camping &amp; Glamping</option>
                                        <option value="13" {{ $hotel->accomodation_type == 13 ? 'selected' : '' }}>Host Homes / Couchsurfing</option>
                                        <option value="14" {{ $hotel->accomodation_type == 14 ? 'selected' : '' }}>Farm Stays / Agro-tourism</option>
                                    </select>
                                    @error('hotel_category')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Ownership / Affiliation -->
                                <div class="col-md-4 mb-3">
                                    <label for="hotel_ownership" class="form-label"><strong>Ownership</strong><span class="text-danger">*</span></label>
                                    <select name="hotel_ownership" id="hotel_ownership" class="form-control" onchange="toggleChainNameField()" required
                                            @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                        <option value="">Select Ownership</option>
                                        <option value="1" {{ $hotel->ownership_type == 1 ? 'selected' : '' }}>Chain Hotels</option>
                                        <option value="2" {{ $hotel->ownership_type == 2 ? 'selected' : '' }}>Independent Hotels</option>
                                        <option value="3" {{ $hotel->ownership_type == 3 ? 'selected' : '' }}>Franchise Hotels</option>
                                    </select>
                                    @error('hotel_ownership')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Chain Hotel Name - Shown only when Chain Hotels is selected -->
                                <div class="col-md-4 mb-3" id="chain_name_container" style="{{ $hotel->ownership_type == 1 ? 'display:block' : 'display:none' }}">
                                    <label for="chain_name" class="form-label"><strong>Chain Hotel Name</strong><span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="chain_name" name="chain_name" 
                                           placeholder="Enter Chain Hotel Name" value="{{ old('chain_name', $hotel->chain_hotel_name) }}" required
                                           @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                    @error('chain_name')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                
                                
                                <!-- Segment -->
                                <div class="col-md-4 mb-3">
                                    <label for="segment" class="form-label"><strong>Type or Segment</strong><span class="text-danger">*</span></label>
                                    <select name="hotel_segment" id="segment" class="form-control" required
                                            @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                        <option value="">Select Ownership</option>
                                        <option value="1" {{ $hotel->hotel_segment == 1 ? 'selected' : '' }}>Budget/Economy Hotels</option>
                                        <option value="2" {{ $hotel->hotel_segment == 2 ? 'selected' : '' }}>Mid-Range Hotels</option>
                                        <option value="3" {{ $hotel->hotel_segment == 3 ? 'selected' : '' }}>Luxury Hotels</option>
                                        <option value="4" {{ $hotel->hotel_segment == 4 ? 'selected' : '' }}>Boutique Hotels</option>
                                        <option value="5" {{ $hotel->hotel_segment == 5 ? 'selected' : '' }}>Resort Hotels</option>
                                        <option value="6" {{ $hotel->hotel_segment == 6 ? 'selected' : '' }}>Business Hotels</option>
                                        <option value="7" {{ $hotel->hotel_segment == 7 ? 'selected' : '' }}>Airport Hotels</option>
                                        <option value="8" {{ $hotel->hotel_segment == 8 ? 'selected' : '' }}>Extended Stay Hotels</option>
                                        <option value="9" {{ $hotel->hotel_segment == 9 ? 'selected' : '' }}>Family Hotels</option>
                                        <option value="10" {{ $hotel->hotel_segment == 10 ? 'selected' : '' }}>Romantic / Getaway Hotels</option>
                                        <option value="11" {{ $hotel->hotel_segment == 11 ? 'selected' : '' }}>Adventure Hotels</option>
                                        <option value="12" {{ $hotel->hotel_segment == 12 ? 'selected' : '' }}>Wellness / Spa Hotels</option>
                                        <option value="13" {{ $hotel->hotel_segment == 13 ? 'selected' : '' }}>Eco-Friendly / Sustainable Hotels</option>
                                        <option value="14" {{ $hotel->hotel_segment == 14 ? 'selected' : '' }}>Extended Stay / Serviced Apartments</option>
                                        <option value="15" {{ $hotel->hotel_segment == 15 ? 'selected' : '' }}>Conference & Convention Hotels</option>
                                        <option value="16" {{ $hotel->hotel_segment == 16 ? 'selected' : '' }}>Casino Hotels</option>
                                        <option value="17" {{ $hotel->hotel_segment == 17 ? 'selected' : '' }}>Cultural / Heritage Hotels</option>
                                        <option value="18" {{ $hotel->hotel_segment == 18 ? 'selected' : '' }}>Religious or Pilgrimage Hotels</option>
                                        <option value="19" {{ $hotel->hotel_segment == 19 ? 'selected' : '' }}>Medical or Wellness Tourism Hotels</option>
                                    </select>
                                    @error('hotel_segment')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Star Rating -->
                                <div class="col-md-4 mb-3">
                                    <label for="star_rating" class="form-label"><strong>Star Rating</strong><span class="text-danger">*</span></label>
                                    <select name="hotel_star_rating" id="star_rating" class="form-control" required
                                            @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                        <option value="">Star Rating</option>
                                        <option value="1" {{ $hotel->hotel_star_rating == 1 ? 'selected' : '' }}>1-Star</option>
                                        <option value="2" {{ $hotel->hotel_star_rating == 2 ? 'selected' : '' }}>2-Star</option>
                                        <option value="3" {{ $hotel->hotel_star_rating == 3 ? 'selected' : '' }}>3-Star</option>
                                        <option value="4" {{ $hotel->hotel_star_rating == 4 ? 'selected' : '' }}>4-Star</option>
                                        <option value="5" {{ $hotel->hotel_star_rating == 5 ? 'selected' : '' }}>5-Star</option>
                                        <option value="7" {{ $hotel->hotel_star_rating == 7 ? 'selected' : '' }}>7-Star</option>
                                    </select>
                                    @error('hotel_star_rating')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="mb-3 col-md-4">
                                    <label for="email" class="form-label"><strong>General Email</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="{{ old('email', $hotel->email) }}"
                                           placeholder="Enter Email" required
                                           oninput="validateEmail(this)"
                                           @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                    <small class="validation-message text-danger" id="email-validation-message"></small>
                                    @error('email')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Address -->
                                <div class="mb-3 col-md-4">
                                    <label for="address" class="form-label"><strong>Address</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="address" name="address" required
                                        value="{{ old('address', $hotel->address) }}" placeholder="Enter Address"
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif required>
                                    @error('address')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Country -->
                                <div class="mb-3 col-md-4">
                                    <label for="country" class="form-label"><strong>Country</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <input name="country" class="form-control" type="text" value="{{$hotel->country}}" readonly>
                                    {{-- <select class="form-control" id="country" name="country" required>
                                        <option value="">Select Country</option>
                                        @foreach($country as $c)
                                            <option value="{{ $c->name }}" @if(old('country', $hotel->country ?? '') == $c->name) selected @endif>
                                                {{ $c->name }}
                                            </option>
                                        @endforeach
                                    </select> --}}
                                    @error('country')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- City -->
                                <div class="mb-3 col-md-4">
                                    <label for="city" class="form-label"><strong>City</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <select name="city" id="citySelect" class="form-control" required
                                            @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                        <option value="{{ $hotel->city }}">{{ $hotel->city }}</option>
                                        @foreach($city as $c)
                                            @if($c->name != $hotel->city)
                                                <option value="{{ $c->name }}">{{ $c->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('city')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- State -->
                                <div class="mb-3 col-md-4">
                                    <label for="state" class="form-label"><strong>State/Provision</strong></label>
                                    <input type="text" class="form-control" id="state" name="state"
                                        value="{{ old('state', $hotel->state) }}" placeholder="Enter State"
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                    @error('state')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>


                                <!-- Postal Code -->
                                <div class="mb-3 col-md-4">
                                    <label for="pincode" class="form-label"><strong>Postal Code</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="pincode" name="pincode"
                                           value="{{ old('zipcode', $hotel->zipcode) }}"
                                           placeholder="Enter Postal Code" required
                                           oninput="validatePincode(this)"
                                           @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                    <small class="validation-message text-danger" id="pincode-validation-message"></small>
                                    @error('pincode')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>


                                <!-- Latitude -->
                                <div class="mb-3 col-md-4">
                                    <label for="latitude" class="form-label"><strong>Latitude</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="latitude" name="latitude"
                                           value="{{ old('latitude', $hotel->latitude) }}"
                                           placeholder="Enter Latitude" required
                                           oninput="validateLatitude(this)"
                                           @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                    <small class="validation-message text-danger" id="latitude-validation-message"></small>
                                    @error('latitude')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Longitude -->
                                <div class="mb-3 col-md-3">
                                    <label for="longitude" class="form-label"><strong>Longitude</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="longitude" name="longitude"
                                           value="{{ old('longitude', $hotel->longitude) }}"
                                           placeholder="Enter Longitude" required
                                           oninput="validateLongitude(this)"
                                           @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                    <small class="validation-message text-danger" id="longitude-validation-message"></small>
                                    @error('longitude')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3 col-md-2">
                                    <label for="country_code" class="form-label"><strong>Country Code</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <select class="form-control" id="country_code" name="country_code" required
                                            @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                        <option value="">Select Country</option>
                                        @foreach ($country_code as $code => $name)
                                        <option value="{{ $code }}"
                                            {{ old('country_code', $hotel->country_code) == $code ? 'selected' : '' }}>
                                            {{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Phone Number -->
                                <div class="mb-3 col-md-3">
                                    <label for="phone" class="form-label"><strong>General Phone No</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="phone" name="phone" 
                                           value="{{ old('phone', $hotel->phone) }}" 
                                           placeholder="Enter Phone" required
                                           pattern="^[0-9]{8,15}$"
                                           oninput="validatePhone(this)"
                                           @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                    <small class="validation-message text-danger" id="phone-validation-message"></small>
                                    @error('phone')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- <div class="mb-3 col-md-4">
                                    <label for="phone" class="form-label"><strong>Phone</strong><span
                                            style="color: red; font-weight: bold;">*</span></label>
                                    <input type="text" class="form-control" id="phone" name="phone"
                                        value="{{ old('phone', $hotel->phone) }}" placeholder="Enter Phone" required>
                                    @error('phone')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label for="email" class="form-label"><strong>Email</strong><span
                                            style="color: red; font-weight: bold;">*</span></label>
                                    <input type="text" class="form-control" id="email" name="email"
                                        value="{{ old('email', $hotel->email) }}" placeholder="Enter Email" required>
                                    @error('email')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div> -->

                                <div class="mb-3 col-md-4">
                                    <label for="weekend_days" class="form-label">
                                        <strong>Weekend Days</strong>
                                        <sup>
                                            <button type="button" class="info-button" data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="Select the weekend days applicable for your hotel."
                                                style="border: none;">
                                                <i class="bi bi-info-circle"></i>
                                            </button>
                                        </sup>
                                    </label>
                                    <select name="weekend_days[]" id="weekend_days" class="form-control" multiple
                                        required @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                        <option value="Saturday"
                                            {{ in_array('Saturday', $selectedDays) ? 'selected' : '' }}>Saturday
                                        </option>
                                        <option value="Sunday"
                                            {{ in_array('Sunday', $selectedDays) ? 'selected' : '' }}>Sunday</option>
                                        <option value="Friday"
                                            {{ in_array('Friday', $selectedDays) ? 'selected' : '' }}>Friday</option>
                                        <option value="Thursday"
                                            {{ in_array('Thursday', $selectedDays) ? 'selected' : '' }}>Thursday
                                        </option>
                                        <option value="Wednesday"
                                            {{ in_array('Wednesday', $selectedDays) ? 'selected' : '' }}>Wednesday
                                        </option>
                                        <option value="Tuesday"
                                            {{ in_array('Tuesday', $selectedDays) ? 'selected' : '' }}>Tuesday</option>
                                        <option value="Monday"
                                            {{ in_array('Monday', $selectedDays) ? 'selected' : '' }}>Monday</option>
                                    </select>
                                    <small class="form-text text-muted">Select the weekend days (use Ctrl or Cmd to
                                        select multiple days).</small>
                                </div>

                                <div class="mb-3 col-md-3">
                                    <label for="infant_age_limit" class="form-label"><strong>Infant Upper Age
                                            Limit</strong><span style="color: red; font-weight: bold;">*</span></label>
                                    <input type="number" class="form-control" id="infant_age_limit"
                                        name="infant_age_limit"
                                        value="{{ old('infant_age_limit', $hotel->infant_age_limit) }}"
                                        required
                                        placeholder="Enter Infant Age Limit"
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                    @error('infant_age_limit')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3 col-md-2">
                                    <label for="child_age_limit" class="form-label"><strong>Child Upper Age
                                            Limit</strong><span style="color: red; font-weight: bold;">*</span></label>
                                    <input type="number" class="form-control" id="child_age_limit"
                                        name="child_age_limit"
                                        value="{{ old('child_age_limit', $hotel->child_age_limit) }}"
                                        required
                                        placeholder="Enter Child Age Limit"
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                    @error('child_age_limit')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3 col-md-3">
                                    <label for="extra_bed_age_limit" class="form-label"><strong>Min Age of Extra
                                            Bed</strong><span style="color: red; font-weight: bold;">*</span></label>
                                    <input type="number" class="form-control" id="extra_bed_age_limit"
                                        name="extra_bed_age_limit"
                                        value="{{ old('extra_bed_age_limit', $hotel->extra_bed_age_limit) }}"
                                        required
                                        placeholder="Enter Hotel Owner Company Name"
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                    @error('extra_bed_age_limit')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3 col-md-12">
                                    <div class="mb-3">
                                        <fieldset class="border p-1 position-relative row">
                                            <div class="mb-3 col-md-3">
                                                <label for="time_range" class="form-label"><strong>Check In Time (hr)</strong></label>
                                                <div class="input-group">
                                                    <input 
                                                    value="{{ old('check_in_time', $hotel->{'check_in_time'} ?? '') }}"
                                                    type="text" id="check_in_time" name="check_in_time" class="form-control"
                                                        placeholder="Enter start time (e.g., 09:00)"
                                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                                </div>
                                                @error('time_range')
                                                <div class="text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3 col-md-3">
                                                <label for="time_range" class="form-label"><strong>Check Out Time (hr)</strong></label>
                                                <div class="input-group">
                                                    <input 
                                                    value="{{ old('check_out_time', $hotel->{'check_out_time'} ?? '') }}"
                                                    type="text" id="check_out_time" name="check_out_time" class="form-control"
                                                        placeholder="Enter start time (e.g., 09:00)"
                                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                                </div>
                                                @error('time_range')
                                                <div class="text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3 col-md-3">
                                                <label for="time_range" class="form-label"><strong>Day Use Start
                                                        Time (hr)</strong></label>
                                                <div class="input-group">
                                                    <input
                                                        value="{{ old('time_range', $hotel->{'12_hour_book'} ?? '') }}"
                                                        type="text" id="time_range" name="time_range"
                                                        class="form-control"
                                                        placeholder="Enter time range (e.g., 09:00)"
                                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                                </div>
                                                @error('time_range')
                                                <div class="text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3 col-md-2">
                                            <label for="time_range" class="form-label"><strong>Hour</strong></label>
                                                <select id="duration" name="duration" class="form-control"
                                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                                    <option value="">Select a value</option>
                                                    <option value="1"
                                                        {{ old('duration', $hotel->duration) == 1 ? 'selected' : '' }}>1
                                                    </option>
                                                    <option value="2"
                                                        {{ old('duration', $hotel->duration) == 2 ? 'selected' : '' }}>2
                                                    </option>
                                                    <option value="3"
                                                        {{ old('duration', $hotel->duration) == 3 ? 'selected' : '' }}>3
                                                    </option>
                                                    <option value="4"
                                                        {{ old('duration', $hotel->duration) == 4 ? 'selected' : '' }}>4
                                                    </option>
                                                    <option value="5"
                                                        {{ old('duration', $hotel->duration) == 5 ? 'selected' : '' }}>5
                                                    </option>
                                                    <option value="6"
                                                        {{ old('duration', $hotel->duration) == 6 ? 'selected' : '' }}>6
                                                    </option>
                                                    <option value="7"
                                                        {{ old('duration', $hotel->duration) == 7 ? 'selected' : '' }}>7
                                                    </option>
                                                    <option value="8"
                                                        {{ old('duration', $hotel->duration) == 8 ? 'selected' : '' }}>8
                                                    </option>
                                                    <option value="9"
                                                        {{ old('duration', $hotel->duration) == 9 ? 'selected' : '' }}>9
                                                    </option>
                                                    <option value="10"
                                                        {{ old('duration', $hotel->duration) == 10 ? 'selected' : '' }}>10
                                                    </option>
                                                    <option value="11"
                                                        {{ old('duration', $hotel->duration) == 11 ? 'selected' : '' }}>11
                                                    </option>
                                                    <option value="12"
                                                        {{ old('duration', $hotel->duration) == 12 ? 'selected' : '' }}>12
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="mb-3 col-md-3">
                                                <label for="end_time" class="form-label"><strong>Day End
                                                        Time (hr)</strong></label>
                                                <div class="input-group">
                                                    <input type="text" id="end_time" name="end_time"
                                                        class="form-control" placeholder="Calculated end time" readonly>
                                                </div>
                                            </div>

                                            <div class="mb-3 col-md-2">
                                                <label for="end_time" class="form-label"><strong>Type</strong></label>
                                                <select id="day_usage_type" name="day_usage_type" class="form-control"
                                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                                    <option value="" disabled
                                                        {{ old('day_usage_type', $hotel->twelve_hours_charge) === null ? 'selected' : '' }}>
                                                        Select a value
                                                    </option>
                                                    <option @selected($hotel->twelve_hours_charge == '0') value="0">Flat
                                                    </option>
                                                    <option @selected($hotel->twelve_hours_charge == '1')
                                                        value="1">Percentage</option>
                                                </select>
                                                @error('day_usage_type')
                                                <div class="text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3 col-md-2">
                                                <label for="percentPrice"
                                                    class="form-label"><strong>Charge</strong></label>
                                                <div class="input-group">
                                                    <input
                                                        value="{{ old('twelve_hours_charge', $hotel->{'twelve_hours_charge'} ?? '') }}"
                                                        type="number" id="percentPrice" name="percentPrice"
                                                        class="form-control" placeholder="Enter Charge"
                                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                                </div>
                                                @error('percentPrice')
                                                <div class="text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>

                                <div class="row col-md-12">
                                    <!-- Master image -->
                                    <div class="mt-3 mb-3 col-md-4">
                                        <div>
                                            <label for="master_image" class="form-label"><strong>Master
                                                    Image</strong></label>
                                            <div id="master-drop-area" class="form-control"
                                                style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px; 
                                                @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) background-color: #f8f9fa; opacity: 0.6; pointer-events: none; @endif">
                                                @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                                                    Drag & Drop your files here or click to upload.
                                                @else
                                                    Image upload is restricted for your role.
                                                @endif
                                                <input type="file" id="master_image" name="master_image" multiple
                                                    style="display: none;"
                                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                            </div>
                                            <small class="text-muted mt-1">
                                                <i class="fas fa-info-circle"></i> 
                                                Images will be automatically compressed for faster upload.
                                            </small>
                                        </div>
                                        <div id="master-preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                            style="max-width: 30%; overflow-x: auto; white-space: nowrap;"></div>

                                        @if($hotel->main_image)
                                        <div class="image-preview-container d-flex flex-wrap gap-2">
                                            <div class="image-preview-wrapper position-relative">
                                                <img src="{{$hotel->main_image}}" alt="Hotel Main Image"
                                                    style="max-width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                                                <button
                                                    class="master-delete-image-btn position-absolute top-0 end-0 btn btn-sm btn-danger"
                                                    data-image="{{ $hotel->main_image }}"
                                                    style="width: 20px; height: 20px; line-height: 18px; padding: 0; text-align: center; font-size: 14px; z-index: 1;"
                                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
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
                                            style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px;
                                            @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) background-color: #f8f9fa; opacity: 0.6; pointer-events: none; @endif">
                                            @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                                                Drag & Drop your files here or click to upload.
                                            @else
                                                Image upload is restricted for your role.
                                            @endif
                                            <input type="file" id="images" name="images[]" multiple
                                                style="display: none;"
                                                @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                        </div>
                                        <small class="text-muted mt-1">
                                            <i class="fas fa-info-circle"></i> 
                                            Images will be automatically compressed for faster upload and better performance.
                                        </small>

                                            <div id="preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                                style="max-width: 100%; overflow-x: auto; white-space: nowrap;"></div>
                                        </div>

                                        <!-- Existing Image Section -->
                                        <div class="existing-image-preview-container d-flex flex-wrap gap-2">
                                            @php
                                            $images = json_decode($hotel->images, true);
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
                                                    style="width: 20px; height: 20px; line-height: 18px; padding: 0; text-align: center; font-size: 14px; z-index: 1;"
                                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
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

                                <!-- Description -->
                                <div class="col-md-12 mb-3">
                                    <label for="description" class="form-label"><strong>Hotel Description</strong><span
                                            style="color: red;">*</span></label>
                                    <textarea id="summernote" name="description" class="form-control" rows="10"
                                              @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>{{ old('description', $hotel->description) }}</textarea required>
                                    @error('description')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="form-check form-switch">
                                <label for="hotel_status" class="form-label"><strong>Status</strong><span style="color: red; font-weight: bold;">*</span></label>
                                <input type="hidden" name="hotel_status" value="0">
                                <input class="form-check-input" name="hotel_status" 
                                    @if($hotel->is_active == 1) checked @endif 
                                    type="checkbox" id="hotel_status" value="1" required
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                <label class="form-check-label"></label>
                                @error('hotel_status')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Submit and Reset Buttons -->
                            <div class="d-flex align-items-center gap-3">

                                <button type="submit" class="btn btn-success px-4"
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
</div>
@endsection

@section('scripts')
<!-- start editor -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- end editor -->

<script>
    // Initialize flatpickr for all time input fields
    flatpickr("#check_in_time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i", // 24-hour format
        time_24hr: true,
        minuteIncrement: 15
    });

    // Add configuration for check out time
    flatpickr("#check_out_time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        minuteIncrement: 15
    });

    // Add configuration for day use start time
    flatpickr("#time_range", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        minuteIncrement: 15,
        // onChange: function(selectedDates, dateStr, instance) {
        //     calculateEndTime(); // Recalculate end time when start time changes
        // }
    });

    // Add configuration for day use end time
    flatpickr("#end_time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        minuteIncrement: 15,
        // Make it read-only since it's calculated
        clickOpens: false
    });

    // Update the calculateEndTime function to work with flatpickr
    function calculateEndTime() {
        const startTimeInput = document.getElementById('time_range');
        const durationInput = document.getElementById('duration');
        const endTimeInput = document.getElementById('end_time');

        if (!startTimeInput.value || !durationInput.value) {
            endTimeInput.value = ''; // Clear end time if inputs are invalid
            return;
        }

        // Parse the start time into hours and minutes
        const [startHours, startMinutes] = startTimeInput.value.split(':').map(Number);
        const duration = parseInt(durationInput.value, 10);

        // Calculate the new end time
        const endDate = new Date();
        endDate.setHours(startHours + duration, startMinutes);

        // Format the end time back to HH:MM
        const endHours = String(endDate.getHours()).padStart(2, '0');
        const endMinutes = String(endDate.getMinutes()).padStart(2, '0');
        endTimeInput.value = `${endHours}:${endMinutes}`;
    }

    document.getElementById('time_range').addEventListener('change', calculateEndTime);
    document.getElementById('duration').addEventListener('change', calculateEndTime);

    // Initialize the end time on page load if values are set
    window.addEventListener('DOMContentLoaded', () => {
        calculateEndTime();
    });
</script>

<script>
    $(document).ready(function () {
        $('#hotelName').select2({
            placeholder: 'Search for a hotel...', // Adds the placeholder
            ajax: {
                url: '{{ route("hotels.search") }}', // Backend route for fetching hotel data
                type: 'GET',
                dataType: 'json',
                delay: 20, // Debounce to reduce server load
                data: function (params) {
                    return {
                        query: params.term // Send the user's input as 'query'
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.map(function (hotel) {
                            return {
                                id: hotel.id, // Value submitted with the form
                                text: `${hotel.name} - ${hotel.city || 'No Location'}` // Text displayed
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 1 // Allow search from 1 character
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#hotelCategory').select2({
            placeholder: "Search and Select Category",
            allowClear: true,
            width: '100%'
        });
        $('#ownership').select2({
            placeholder: "Search and Select Ownership Type",
            allowClear: true,
            width: '100%'
        });
        $('#segment').select2({
            placeholder: "Search and Select Segment",
            allowClear: true,
            width: '100%'
        });
        $('#star_rating').select2({
            placeholder: "Search and Select Star",
            allowClear: true,
            width: '100%'
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#weekend_days').select2({
            placeholder: "Select weekend days",
            allowClear: true
        });
    });
</script>


<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
            $('#summernote').summernote({
                height: 200,      
                minHeight: 200,   
                maxHeight: 500,   
                placeholder: 'Enter your content here...', 
            });
        @else
            $('#summernote').summernote({
                height: 200,      
                minHeight: 200,   
                maxHeight: 500,   
                placeholder: 'Description editing is restricted for your role.',
                toolbar: false,
                disableResizeEditor: true
            });
            $('#summernote').summernote('disable');
        @endif
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
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

</script>

<script>
    document.getElementById('country').addEventListener('input', function() {
    var inputValue = this.value.toLowerCase();  // Get the input value in lowercase
    var countryCodes = {!! json_encode($country_code) !!}; // Pass the country codes from PHP to JavaScript
    var select = document.getElementById('country_code');
    
    // Loop through all the options in the country code dropdown
    for (var option of select.options) {
        var optionText = option.text.toLowerCase();
        var optionValue = option.value;
        
        // Check if the country input matches the country name in the dropdown
        if (optionText.includes(inputValue)) {
            select.value = optionValue; // Set the value of the select to the matched country code
            return; // Stop looping once a match is found
        }
    }
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

    async function handleFiles(newFiles) {
        // Show loading message
        const loadingDiv = document.createElement('div');
        loadingDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Compressing images for faster upload...</div>';
        fileList.appendChild(loadingDiv);

        // Process files sequentially to avoid overwhelming the browser
        for (const file of Array.from(newFiles)) {
            if (file.type.startsWith('image/')) {
                try {
                    // Check file size before compression
                    const fileSizeMB = file.size / 1024 / 1024;
                    
                    // Only compress if file is larger than 1MB or if we already have many files
                    let finalFile = file;
                    if (fileSizeMB > 1 || files.length >= 3) {
                        finalFile = await compressImage(file, 0.7, 1600, 1200); // More aggressive compression for multiple files
                    }
                    
                    // Check total size limit (keep under 80MB total)
                    const currentTotalSize = files.reduce((total, f) => total + f.size, 0);
                    const totalSizeMB = (currentTotalSize + finalFile.size) / 1024 / 1024;
                    
                    if (totalSizeMB > 80) {
                        alert(`Total upload size would exceed 80MB limit. Please remove some images or upload in smaller batches.`);
                        break;
                    }
                    
                    files.push(finalFile);
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
        updateFileList();
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

        // Show total size information
        if (files.length > 0) {
            const totalSize = files.reduce((total, f) => total + f.size, 0);
            const totalSizeMB = (totalSize / 1024 / 1024).toFixed(1);
            const sizeInfo = document.createElement('div');
            sizeInfo.innerHTML = `<small class="text-muted">Total: ${files.length} images (${totalSizeMB}MB)</small>`;
            fileList.appendChild(sizeInfo);
        }
    }

    // Form submission handler with upload progress and error handling
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('hotelForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Check if we have files to upload
                const totalFiles = files.length;
                if (totalFiles > 10) {
                    e.preventDefault();
                    alert('Please upload maximum 10 images at a time to avoid server limits.');
                    return false;
                }

                // Check total upload size
                const totalSize = files.reduce((total, f) => total + f.size, 0);
                const totalSizeMB = totalSize / 1024 / 1024;
                
                if (totalSizeMB > 90) {
                    e.preventDefault();
                    alert('Total upload size is too large. Please reduce image sizes or upload fewer images.');
                    return false;
                }

                // Show upload progress
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
                    // Find and get the image path from the hidden input field
                    const hiddenInput = imageWrapper.querySelector('input[type="hidden"]');
                    let imagePath = '';
                    
                    if (hiddenInput) {
                        imagePath = hiddenInput.value;
                        hiddenInput.remove(); // Remove the hidden input
                    }
                    
                    // Add the removed image path to the tracking input
                    if (imagePath) {
                        const removedImagesInput = document.getElementById('removed_images');
                        let removedImages = removedImagesInput.value ? removedImagesInput.value.split(',') : [];
                        removedImages.push(imagePath);
                        removedImagesInput.value = removedImages.join(',');
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
    async function masterHandleFiles(files) {
        // Show compression progress
        showCompressionProgress('master');
        
        for (const file of Array.from(files)) {
            if (file.type.startsWith('image/')) {
                try {
                    // Compress the image
                    const compressedFile = await compressImage(file);
                    
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        // If an image already exists, remove it before adding the new one
                        if (masterFileCounter > 0) {
                            masterPreviewContainer.innerHTML = ''; // Clear the existing preview
                            masterFileCounter = 0; // Reset the file counter
                        }
                        masterFileCounter++;
                        masterImagePreview(e.target.result);
                        
                        // Update the file input with compressed file
                        const dt = new DataTransfer();
                        dt.items.add(compressedFile);
                        masterFileInput.files = dt.files;
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
        hideCompressionProgress('master');
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
            
            if (container === 'master') {
                masterPreviewContainer.appendChild(progressDiv);
            } else if (container === 'additional') {
                fileList.appendChild(progressDiv);
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

<!-- delete existing master Image -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Use event delegation for dynamically added elements
        document.querySelector('.image-preview-container').addEventListener('click', function(e) {
            if (e.target.classList.contains('master-delete-image-btn')) {
                e.preventDefault(); // Prevent form submission
                e.stopPropagation(); // Stop event propagation
                const button = e.target;

                // Find the image preview wrapper
                const imageWrapper = button.closest('.image-preview-wrapper');
                if (imageWrapper) {
                    // Get the image path from the data attribute or img src
                    const img = imageWrapper.querySelector('img');
                    let imagePath = '';
                    
                    if (img) {
                        // Extract the image path from the src attribute
                        imagePath = button.getAttribute('data-image') || img.src;
                    }
                    
                    // Add the removed master image path to the tracking input
                    if (imagePath) {
                        const removedMasterImageInput = document.getElementById('removed_master_image');
                        removedMasterImageInput.value = imagePath;
                    }

                    // Remove the image wrapper (image and button)
                    imageWrapper.remove();
                }
            }
        });
    });
</script>


<script>
    function calculateEndTime() {
        const startTimeInput = document.getElementById('time_range');
        const durationInput = document.getElementById('duration');
        const endTimeInput = document.getElementById('end_time');

        if (!startTimeInput.value || !durationInput.value) {
            endTimeInput.value = ''; // Clear end time if inputs are invalid
            return;
        }

        // Parse the start time into hours and minutes
        const [startHours, startMinutes] = startTimeInput.value.split(':').map(Number);
        const duration = parseInt(durationInput.value, 10);

        // Calculate the new end time
        const endDate = new Date();
        endDate.setHours(startHours + duration, startMinutes);

        // Format the end time back to HH:MM
        const endHours = String(endDate.getHours()).padStart(2, '0');
        const endMinutes = String(endDate.getMinutes()).padStart(2, '0');
        endTimeInput.value = `${endHours}:${endMinutes}`;
    }

    document.getElementById('time_range').addEventListener('change', calculateEndTime);
    document.getElementById('duration').addEventListener('change', calculateEndTime);

    // Initialize the end time on page load if values are set
    window.addEventListener('DOMContentLoaded', () => {
        calculateEndTime();
    });
</script>
<script>
    $(document).ready(function() {
        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
            $('#summernote').summernote({
                height: 200,      
                minHeight: 200,   
                maxHeight: 500,   
                placeholder: 'Enter your content here...', 
            });
        @else
            $('#summernote').summernote({
                height: 200,      
                minHeight: 200,   
                maxHeight: 500,   
                placeholder: 'Description editing is restricted for your role.',
                toolbar: false,
                disableResizeEditor: true
            });
            $('#summernote').summernote('disable');
        @endif
    });
</script>

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

    function validateEmail(input) {
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        const value = input.value.trim();
        
        if (value === '') {
            showValidationMessage(input, false, 'Email address is required');
        } else if (!emailRegex.test(value)) {
            showValidationMessage(input, false, `
                Please enter a valid email address:
                <ul class="mt-1 mb-0">
                    <li>Must contain @ symbol</li>
                    <li>Must end with a valid domain (e.g., .com, .org)</li>
                    <li>Example: john.doe@example.com</li>
                </ul>
            `);
        } else {
            showValidationMessage(input, true, '');
        }
    }

    function validatePhone(input) {
        // Force numeric input by immediately replacing non-numeric characters
        input.value = input.value.replace(/[^0-9]/g, '');
        
        const phoneRegex = /^[0-9]{8,15}$/;
        const value = input.value.trim();
        
        if (value === '') {
            showValidationMessage(input, false, 'Phone number is required');
        } else if (!phoneRegex.test(value)) {
            showValidationMessage(input, false, `
                Please enter a valid phone number:
                <ul class="mt-1 mb-0">
                    <li>Must contain 8-15 digits</li>
                    <li>Only numbers are allowed (0-9)</li>
                    <li>No spaces or special characters</li>
                </ul>
            `);
        } else {
            showValidationMessage(input, true, '');
        }
    }

    function validatePincode(input) {
    // Remove all characters except letters and numbers
    input.value = input.value.replace(/[^a-zA-Z0-9]/g, '');

    const pincodeRegex = /^[A-Za-z0-9]{4,10}$/;
    const value = input.value.trim();

    if (value === '') {
        showValidationMessage(input, false, 'Postal code is required');
    } else if (!pincodeRegex.test(value)) {
        showValidationMessage(input, false, `
            Please enter a valid postal code:
            <ul class="mt-1 mb-0">
                <li>4–10 characters long</li>
                <li>Only letters (A–Z) and digits (0–9)</li>
                <li>No spaces or special characters</li>
                <li>Examples: 560001, AB1234, H0H0H0</li>
            </ul>
        `);
    } else {
        showValidationMessage(input, true, '');
    }
}

    function validateLatitude(input) {
        // Force numeric input by immediately replacing non-numeric characters
        input.value = input.value.replace(/[^0-9.-]/g, '');

        const latitudeRegex = /^-?([1-8]?[0-9]\.{1}\d{1,9}$|90\.{1}0{1,9}$)/;
        const value = input.value.trim();
        
        if (value === '') {
            showValidationMessage(input, false, 'Latitude is required');
        } else if (!latitudeRegex.test(value)) {
            showValidationMessage(input, false, `
                Please enter a valid latitude:
                <ul class="mt-1 mb-0">
                    <li>Must be between -90 and 90 degrees</li>
                    <li>Must include decimal point</li>
                    <li>Up to 9 decimal places</li>
                    <li>Example: 23.456789801</li>
                </ul>
            `);
        } else {
            showValidationMessage(input, true, '');
        }
    }

    function validateLongitude(input) {
        // Force numeric input by immediately replacing non-numeric characters
        input.value = input.value.replace(/[^0-9.-]/g, '');

        const longitudeRegex = /^-?([1-9]?[0-9]\.{1}\d{1,9}$|1[0-7][0-9]\.{1}\d{1,9}$|180\.{1}0{1,9}$)/;
        const value = input.value.trim();
        
        if (value === '') {
            showValidationMessage(input, false, 'Longitude is required');
        } else if (!longitudeRegex.test(value)) {
            showValidationMessage(input, false, `
                Please enter a valid longitude:
                <ul class="mt-1 mb-0">
                    <li>Must be between -180 and 180 degrees</li>
                    <li>Must include decimal point</li>
                    <li>Up to 9 decimal places</li>
                    <li>Example: 78.123456656</li>
                </ul>
            `);
        } else {
            showValidationMessage(input, true, '');
        }
    }

    // Add CSS for validation messages
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

<!-- Add this script for toggling chain field -->
<script>
    // Function to toggle the chain name field based on ownership selection
    function toggleChainNameField() {
        const ownershipSelect = document.getElementById('hotel_ownership');
        const chainNameContainer = document.getElementById('chain_name_container');
        const chainNameInput = document.getElementById('chain_name');
        
        // Show chain name field if "Chain Hotels" (value 1) is selected
        if (ownershipSelect.value === '1') {
            chainNameContainer.style.display = 'block';
            chainNameInput.setAttribute('required', 'required');
        } else {
            chainNameContainer.style.display = 'none';
            chainNameInput.removeAttribute('required');
        }
    }
    
    // Call the function on page load to set initial state
    document.addEventListener('DOMContentLoaded', function() {
        toggleChainNameField();
    });
</script>

@endsection