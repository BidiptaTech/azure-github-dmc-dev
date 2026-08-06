@extends('layouts.layout')
@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- Start of the form -->
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

    /* Match Flatpickr time dropdown with Bootstrap styling */
    .flatpickr-calendar {
        font-size: 14px; /* match your form font size */
        border-radius: 0.375rem; /* like Bootstrap rounded-md */
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
    }

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
    
    /* Phone input group styling */
    .phone-input-group .input-group select {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        width: 40%;
        z-index: 0;
    }
    
    .phone-input-group .input-group input {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        width: 60%;
    }

    .hotel-save-loader {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        z-index: 99999;
        display: none;
        align-items: center;
        justify-content: center;
    }

    .hotel-save-loader.active {
        display: flex;
    }

    .hotel-save-loader__box {
        background: #fff;
        border-radius: 0.5rem;
        padding: 1.25rem 1.5rem;
        min-width: 180px;
        text-align: center;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.2);
    }

</style>

<div id="hotelSaveLoader" class="hotel-save-loader" aria-live="polite" aria-busy="false">
    <div class="hotel-save-loader__box">
        <div class="spinner-border text-primary mb-2" role="status" aria-hidden="true"></div>
        <div class="fw-semibold">Saving hotel...</div>
    </div>
</div>
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <ul class="nav nav-pills mb-4 mt-4 d-flex justify-content-center" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="pills-hotel-tab" data-bs-toggle="pill"
                        href="{{ route('hotels.create') }}" role="tab" aria-controls="pills-hotel" aria-selected="true">
                        Hotel
                    </a>
                </li>
                <x-alert />
                <li class="nav-item" role="presentation">
                    <a class="nav-link disabled" id="pills-contact-tab" href="#" role="tab"
                        aria-controls="pills-contact" aria-selected="false" tabindex="-1">
                        Contact Details
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link disabled" id="pills-room-tab" href="#" role="tab" aria-controls="pills-room"
                        aria-selected="false" tabindex="-1">
                        Ports & NearBy
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link disabled" id="pills-season-tab" href="#" role="tab" aria-controls="pills-season"
                        aria-selected="false" tabindex="-1">
                        Facilities
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link disabled" id="pills-event-tab" href="#" role="tab" aria-controls="pills-event"
                        aria-selected="false" tabindex="-1">
                        Room Type
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link disabled" id="pills-calendar-tab" href="#" role="tab"
                        aria-controls="pills-calendar" aria-selected="false" tabindex="-1">
                        Bed Type
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link disabled" id="pills-calendar-tab" href="#" role="tab"
                        aria-controls="pills-calendar" aria-selected="false" tabindex="-1">
                        Rooms
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link disabled" id="pills-calendar-tab" href="#" role="tab"
                        aria-controls="pills-calendar" aria-selected="false" tabindex="-1">
                        Seasons
                    </a>
                </li>
                
                <li class="nav-item" role="presentation">
                    <a class="nav-link disabled" id="pills-calendar-tab" href="#" role="tab"
                        aria-controls="pills-calendar" aria-selected="false" tabindex="-1">
                        Blackout/Fair Dates
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link disabled" id="pills-calendar-tab" href="#" role="tab"
                        aria-controls="pills-calendar" aria-selected="false" tabindex="-1">
                        Hotel Restaurants
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link disabled" id="pills-calendar-tab" href="#" role="tab"
                        aria-controls="pills-calendar" aria-selected="false" tabindex="-1">
                        Policy
                    </a>
                </li>
                {{-- <li class="nav-item" role="presentation">
                    <a class="nav-link disabled" id="pills-calendar-tab" href="#" role="tab"
                        aria-controls="pills-calendar" aria-selected="false" tabindex="-1">
                        Cancellation Policy
                    </a>
                </li> --}}
                <li class="nav-item" role="presentation">
                    <a class="nav-link disabled" id="pills-calendar-tab" href="#" role="tab"
                        aria-controls="pills-calendar" aria-selected="false" tabindex="-1">
                        Conference & Meeting
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link disabled" id="pills-calendar-tab" href="#" role="tab"
                        aria-controls="pills-calendar" aria-selected="false" tabindex="-1">
                        Calender
                    </a>
                </li>
            </ul>
            <div class="card mb-6">
                <h5 class="card-header d-flex justify-content-between align-items-center">
                    Add New Hotel
                    <a href="{{ route('hotels.index') }}" class="btn btn-sm btn-outline-danger">
                        <i class="mdi mdi-arrow-left"></i> Back
                    </a>
                </h5>
                <form id="hotelForm" method="POST" action="{{ route('hotels.store') }}" enctype="multipart/form-data"
                    class="card-body">
                    @csrf
                    <div class="row">
                    <!-- @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 23 || auth()->user()->role_id == 25 || auth()->user()->role_id == 47 || auth()->user()->role_id == 59 || auth()->user()->role_id == 82|| auth()->user()->role_id == 83)
                        <div class="mb-3 col-md-4" id="dmc-container" style="display: none;">
                            
                            <label for="dmc" class="form-label"><strong>DMC</strong><span style="color: red; font-weight: bold;">*</span></label>
                            <select id="dmc" name="dmc" class="form-control" required>
                                <option value="">Select DMC</option>
                                @foreach ($dmcs as $dmc)
                                    <option value="{{ $dmc->userId }}">{{ $dmc->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif -->
                        <div class="mb-3 col-md-4">
                            <label for="input35" class="form-label"><strong>Hotel Name</strong>
                                <span style="color: red; font-weight: bold;">*</span>
                            </label>
                            <input type="text" class="form-control" id="input35" name="name" placeholder="Enter Hotel Name" required value="{{ old('name') }}">
                            @error('name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Category Type -->
                        <!-- <div class="mb-3 col-md-4">
                            <label for="category_type" class="form-label"><strong>Category Type</strong>
                                <span style="color: red; font-weight: bold;">*</span>
                            </label>
                            <select id="category_type" name="category_type" class="form-control">
                                <option value="">Select Category Type</option>
                                @forelse ($categories as $category)
                                <option value="{{ $category->category_id }}">{{ $category->name }}</option>
                                @empty
                                <option>No categories available</option>
                                @endforelse
                            </select>
                            @error('category_type')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div> -->

                        <div class="col-md-4 mb-3">
                            <label for="hotelCategory" class="form-label"><strong>Accomodations  Type</strong><span class="text-danger">*</span></label>
                            <select name="hotel_category" id="hotelCategory" class="form-control" required>
                                <option value="">Select a Category</option>
                                <!-- <option value="0">Third Party</option> -->
                                <option value="1" {{ old('hotel_category') == '1' ? 'selected' : '' }}>Hotel</option>
                                <option value="2" {{ old('hotel_category') == '2' ? 'selected' : '' }}>Motel</option>
                                <option value="3" {{ old('hotel_category') == '3' ? 'selected' : '' }}>Resort</option>
                                <option value="4" {{ old('hotel_category') == '4' ? 'selected' : '' }}>Bed and Breakfast (B&amp;B)</option>
                                <option value="5" {{ old('hotel_category') == '5' ? 'selected' : '' }}>Hostels</option>
                                <option value="6" {{ old('hotel_category') == '6' ? 'selected' : '' }}>Serviced Apartments / Aparthotels</option>
                                <option value="7" {{ old('hotel_category') == '7' ? 'selected' : '' }}>Guesthouses</option>
                                <option value="8" {{ old('hotel_category') == '8' ? 'selected' : '' }}>Vacation Rentals</option>
                                <option value="9" {{ old('hotel_category') == '9' ? 'selected' : '' }}>Boutique Hotels</option>
                                <option value="10" {{ old('hotel_category') == '10' ? 'selected' : '' }}>Lodges</option>
                                <option value="11" {{ old('hotel_category') == '11' ? 'selected' : '' }}>Homestays</option>
                                <option value="12" {{ old('hotel_category') == '12' ? 'selected' : '' }}>Camping &amp; Glamping</option>
                                <option value="13" {{ old('hotel_category') == '13' ? 'selected' : '' }}>Host Homes / Couchsurfing</option>
                                <option value="14" {{ old('hotel_category') == '14' ? 'selected' : '' }}>Farm Stays / Agro-tourism</option>
                            </select>
                            @error('hotel_category')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ownership and affiliation -->
                        <div class="col-md-4 mb-3">
                            <label for="ownership" class="form-label"><strong>Ownership / Affiliation</strong><span class="text-danger">*</span></label>
                            <select name="hotel_ownership" id="ownership" class="form-control" onchange="toggleChainField()" required>
                                <option value="">Select Ownership</option>
                                <!-- <option value="0">Third Party</option> -->
                                <option value="1" {{ old('hotel_ownership') == '1' ? 'selected' : '' }}>Chain Hotels</option>
                                <option value="2" {{ old('hotel_ownership') == '2' ? 'selected' : '' }}>Independent Hotels</option>
                                <option value="3" {{ old('hotel_ownership') == '3' ? 'selected' : '' }}>Franchise Hotels</option>
                            </select>
                            @error('hotel_ownership')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Chain Name - Shown only when Chain Hotels is selected -->
                        <div class="col-md-4 mb-3" id="chain_name_container" style="display: none;">
                            <label for="chain_name" class="form-label"><strong>Chain Hotel Name</strong><span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="chain_name" name="chain_name" 
                                   placeholder="Enter Chain Name" value="{{ old('chain_name') }}" required>
                            @error('chain_name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Segment  -->
                        <div class="col-md-4 mb-3">
                            <label for="segment" class="form-label"><strong>Type or Segment
                            </strong><span class="text-danger">*</span></label>
                            <select name="hotel_segment" id="segment" class="form-control" required>
                                <option value="">Select Ownership</option>
                                <!-- <option value="0">Third Party</option> -->
                                <option value="1" {{ old('hotel_segment') == '1' ? 'selected' : '' }}>Budget/Economy Hotels</option>
                                <option value="2" {{ old('hotel_segment') == '2' ? 'selected' : '' }}>Mid-Range Hotels</option>
                                <option value="3" {{ old('hotel_segment') == '3' ? 'selected' : '' }}>Luxury Hotels</option>
                                <option value="4" {{ old('hotel_segment') == '4' ? 'selected' : '' }}>Boutique Hotels</option>
                                <option value="5" {{ old('hotel_segment') == '5' ? 'selected' : '' }}>Resort Hotels</option>
                                <option value="6" {{ old('hotel_segment') == '6' ? 'selected' : '' }}>Business Hotels</option>
                                <option value="7" {{ old('hotel_segment') == '7' ? 'selected' : '' }}>Airport Hotels</option>
                                <option value="8" {{ old('hotel_segment') == '8' ? 'selected' : '' }}>Extended Stay Hotels</option>
                                <option value="9" {{ old('hotel_segment') == '9' ? 'selected' : '' }}>Family Hotels</option>
                                <option value="10" {{ old('hotel_segment') == '10' ? 'selected' : '' }}>Romantic / Getaway Hotels</option>
                                <option value="11" {{ old('hotel_segment') == '11' ? 'selected' : '' }}>Adventure Hotels</option>
                                <option value="12" {{ old('hotel_segment') == '12' ? 'selected' : '' }}>Wellness / Spa Hotels</option>
                                <option value="13" {{ old('hotel_segment') == '13' ? 'selected' : '' }}>Eco-Friendly / Sustainable Hotels</option>
                                <option value="14" {{ old('hotel_segment') == '14' ? 'selected' : '' }}>Extended Stay / Serviced Apartments</option>
                                <option value="15" {{ old('hotel_segment') == '15' ? 'selected' : '' }}>Conference & Convention Hotels</option>
                                <option value="16" {{ old('hotel_segment') == '16' ? 'selected' : '' }}>Casino Hotels</option>
                                <option value="17" {{ old('hotel_segment') == '17' ? 'selected' : '' }}>Cultural / Heritage Hotels</option>
                                <option value="18" {{ old('hotel_segment') == '18' ? 'selected' : '' }}>Religious or Pilgrimage Hotels</option>
                                <option value="19" {{ old('hotel_segment') == '19' ? 'selected' : '' }}>Medical or Wellness Tourism Hotels</option>
                            </select>
                            @error('hotel_segment')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Star Rating  -->
                        <div class="col-md-4 mb-3">
                            <label for="star_rating" class="form-label"><strong>Star Rating</strong><span class="text-danger">*</span></label>
                            <select name="hotel_star_rating" id="star_rating" class="form-control" required>
                                <option value="">Star Rating </option>
                                <!-- <option value="0">Third Party</option> -->
                                <option value="1" {{ old('hotel_star_rating') == '1' ? 'selected' : '' }}>1-Star</option>
                                <option value="2" {{ old('hotel_star_rating') == '2' ? 'selected' : '' }}>2-Star</option>
                                <option value="3" {{ old('hotel_star_rating') == '3' ? 'selected' : '' }}>3-Star</option>
                                <option value="4" {{ old('hotel_star_rating') == '4' ? 'selected' : '' }}>4-Star</option>
                                <option value="5" {{ old('hotel_star_rating') == '5' ? 'selected' : '' }}>5-Star</option>
                                <option value="7" {{ old('hotel_star_rating') == '7' ? 'selected' : '' }}>7-Star</option>
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
                                   placeholder="Enter Email" required
                                   oninput="validateEmail(this)"
                                   value="{{ old('email') }}">
                            <small class="validation-message text-danger" id="email-validation-message"></small>
                            @error('email')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Address -->
                        <div class="mb-3 col-md-4">
                            <label for="address" class="form-label"><strong>Address</strong><span
                                    style="color: red; font-weight: bold;">*</span></label>
                            <input type="text" class="form-control" id="address" name="address"
                                placeholder="Enter Address" required value="{{ old('address') }}">
                            @error('address')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Country -->
                            <div class="mb-3 col-md-4">
                                <label for="country" class="form-label"><strong>Country</strong>
                                    <span style="color: red; font-weight: bold;">*</span>
                                </label>
                                <select class="form-control" id="country" name="country" required>
                                    <option value="">Select Country</option>
                                    @foreach($country as $c)
                                        <option value="{{ $c->name }}" {{ old('country') == $c->name ? 'selected' : '' }}>
                                            {{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('country')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- City -->
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label"><strong>City</strong><span class="text-danger">*</span></label>
                                <select name="location" id="citySelect" class="form-control" required disabled>
                                    <option value="">{{ in_array(auth()->user()->role_id, [11, 35, 77, 84, 139, 140]) ? 'Select Country First' : 'Select Country First' }}</option>
                                </select>
                                @error('location')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                        <!-- State -->
                        <div class="mb-3 col-md-4">
                            <label for="state" class="form-label"><strong>State/Province</strong>
                                <span class="text-muted small">(Optional)</span>
                            </label>
                            <input type="text" class="form-control" id="state" name="state" placeholder="Enter State" value="{{ old('state') }}">
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
                                   placeholder="Enter Postal Code" required
                                   oninput="validatePincode(this)"
                                   value="{{ old('pincode') }}">
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
                                   placeholder="Enter Latitude" required
                                   oninput="validateLatitude(this)"
                                   value="{{ old('latitude') }}">
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
                                   placeholder="Enter Longitude" required
                                   oninput="validateLongitude(this)"
                                   value="{{ old('longitude') }}">
                            <small class="validation-message text-danger" id="longitude-validation-message"></small>
                            @error('longitude')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Country Code & Phone Number Combined -->
                        <div class="mb-3 col-md-5 phone-input-group">
                            <label for="phone" class="form-label"><strong>General Phone No</strong>
                                <span style="color: red; font-weight: bold;">*</span>
                            </label>
                            <div class="input-group">
                                <select class="form-control" id="country_code" name="country_code" required style="max-width: 40%;">
                                    <option value="">Select</option>
                                    @foreach ($country_code as $code => $name)
                                    <option value="{{ $code }}" {{ old('country_code') == $code ? 'selected' : '' }}> {{ $name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" class="form-control" id="phone" name="phone" 
                                   placeholder="Enter Phone Number" required
                                   oninput="validatePhone(this)"
                                   value="{{ old('phone') }}">
                            </div>
                            <small class="validation-message text-danger" id="phone-validation-message"></small>
                            @error('country_code')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                            @error('phone')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label for="weekend_days" class="form-label">
                                <strong>Weekend Days</strong><span style="color: red; font-weight: bold;">*</span>
                                <sup>
                                    <button type="button" class="info-button" data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Select the weekend days applicable for your hotel."
                                        style="border: none;">
                                        <i class="bi bi-info-circle"></i>
                                    </button>
                                </sup>
                            </label>
                            <select name="weekend_days[]" id="weekend_days" class="form-control" multiple required>
                                @php $oldWeekends = old('weekend_days', []); @endphp
                                <option value="Saturday" {{ in_array('Saturday', $oldWeekends) ? 'selected' : '' }}>Saturday</option>
                                <option value="Sunday" {{ in_array('Sunday', $oldWeekends) ? 'selected' : '' }}>Sunday</option>
                                <option value="Friday" {{ in_array('Friday', $oldWeekends) ? 'selected' : '' }}>Friday</option>
                                <option value="Thursday" {{ in_array('Thursday', $oldWeekends) ? 'selected' : '' }}>Thursday</option>
                                <option value="Wednesday" {{ in_array('Wednesday', $oldWeekends) ? 'selected' : '' }}>Wednesday</option>
                                <option value="Tuesday" {{ in_array('Tuesday', $oldWeekends) ? 'selected' : '' }}>Tuesday</option>
                                <option value="Monday" {{ in_array('Monday', $oldWeekends) ? 'selected' : '' }}>Monday</option>
                            </select>
                            <small class="form-text text-muted">Select the weekend days (use Ctrl or Cmd to select
                                multiple
                                days).</small>
                        </div>

                        <!-- Additional Fields (Phone, Email, etc.) -->
                        <div class="mb-3 col-md-3">
                            <label for="infant_age_limit" class="form-label"><strong>Infant Upper Age
                                    Limit</strong><span style="color: red; font-weight: bold;">*</span></label>
                            <input type="number" class="form-control" id="infant_age_limit" name="infant_age_limit"
                                placeholder="Enter Infant Upper Age Limit" required value="{{ old('infant_age_limit') }}">
                            @error('infant_age_limit')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-2">
                            <label for="child_age_limit" class="form-label"><strong>Child Upper Age Limit</strong><span
                                    style="color: red; font-weight: bold;">*</span></label>
                            <input type="number" class="form-control" id="child_age_limit" name="child_age_limit"
                                placeholder="Enter Child Upper Age Limit" required value="{{ old('child_age_limit') }}">
                            @error('child_age_limit')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        

                        <div class="mb-3 col-md-3">
                            <label for="extra_bed_age_limit" class="form-label"><strong>Min Age of Extra
                                    Bed</strong><span style="color: red; font-weight: bold;">*</span></label>
                            <input type="number" class="form-control" id="extra_bed_age_limit"
                                name="extra_bed_age_limit" placeholder="Enter Min Age of Extra Bed" required value="{{ old('extra_bed_age_limit') }}">
                            @error('extra_bed_age_limit')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-12">
                            <div class="mb-3">
                                <fieldset class="border p-1 position-relative row">
                                    {{-- <div class="mb-3 col-md-3">
                                        <label for="check_in_time" class="form-label"><strong>Check In Time (hr)</strong></label>
                                        <div class="input-group">
                                            <input type="text" id="check_in_time" name="check_in_time" class="form-control"
                                                placeholder="Enter start time (e.g., 09:00)">
                                        </div>
                                        @error('check_in_time')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>                    --}}
                                    
                                    <!-- Check In Time -->
                                    <div class="mb-3 col-md-3">
                                        <label for="check_in_time" class="form-label"><strong>Check In Time (hr)</strong><span style="color: red; font-weight: bold;">*</span></label>
                                        <div class="input-group">
                                            <input type="text" id="check_in_time" name="check_in_time" class="form-control"
                                                placeholder="Select check-in time" required value="{{ old('check_in_time') }}">
                                        </div>
                                        @error('check_in_time')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- <div class="mb-3 col-md-3">
                                        <label for="start_time" class="form-label"><strong>Check Out Time (hr)</strong></label>
                                        <div class="input-group">
                                            <input type="time" id="check_out_time" name="check_out_time" class="form-control"
                                                placeholder="Enter start time (e.g., 09:00)">
                                        </div>
                                        @error('check_out_time')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div> --}}

                                    <!-- Check Out Time -->
                                    <div class="mb-3 col-md-3">
                                        <label for="check_out_time" class="form-label"><strong>Check Out Time (hr)</strong><span style="color: red; font-weight: bold;">*</span></label>
                                        <div class="input-group">
                                            <input type="text" id="check_out_time" name="check_out_time" class="form-control"
                                                placeholder="Select check-out time" required value="{{ old('check_out_time') }}">
                                        </div>
                                        @error('check_out_time')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- <div class="mb-3 col-md-3">
                                        <label for="start_time" class="form-label"><strong>Day Use Start
                                                Time (hr)</strong></label>
                                        <div class="input-group">
                                            <input type="time" id="start_time" name="start_time" class="form-control"
                                                placeholder="Enter start time (e.g., 09:00)">
                                        </div>
                                    </div> --}}

                                    <!-- Day Use Start Time -->
                                    <div class="mb-3 col-md-3">
                                        <label for="start_time" class="form-label"><strong>Day Use Start Time (hr)</strong><span style="color: red; font-weight: bold;">*</span></label>
                                        <div class="input-group">
                                        <input type="text" id="start_time" name="start_time" class="form-control"
                                            placeholder="Select start time" required value="{{ old('start_time') }}">
                                        </div>
                                    </div>

                                    <div class="mb-3 col-md-2">
                                    <label for="start_time" class="form-label"><strong>Hour</strong><span style="color: red; font-weight: bold;">*</span></label>
                                        <select id="duration" name="duration" class="form-control" required>
                                            <option value="">Select a value</option>
                                            <option value="1" {{ old('duration') == '1' ? 'selected' : '' }}>1</option>
                                            <option value="2" {{ old('duration') == '2' ? 'selected' : '' }}>2</option>
                                            <option value="3" {{ old('duration') == '3' ? 'selected' : '' }}>3</option>
                                            <option value="4" {{ old('duration') == '4' ? 'selected' : '' }}>4</option>
                                            <option value="5" {{ old('duration') == '5' ? 'selected' : '' }}>5</option>
                                            <option value="6" {{ old('duration') == '6' ? 'selected' : '' }}>6</option>
                                            <option value="7" {{ old('duration') == '7' ? 'selected' : '' }}>7</option>
                                            <option value="8" {{ old('duration') == '8' ? 'selected' : '' }}>8</option>
                                            <option value="9" {{ old('duration') == '9' ? 'selected' : '' }}>9</option>
                                            <option value="10" {{ old('duration') == '10' ? 'selected' : '' }}>10</option>
                                            <option value="11" {{ old('duration') == '11' ? 'selected' : '' }}>11</option>
                                            <option value="12" {{ old('duration') == '12' ? 'selected' : '' }}>12</option>
                                        </select>
                                    </div>
                                    
                                    {{-- <div class="mb-3 col-md-3">
                                        <label for="end_time" class="form-label"><strong>Day Use End
                                                Time (hr)</strong></label>
                                        <div class="input-group">
                                            <input type="time" id="end_time" name="end_time" class="form-control"
                                                placeholder="Calculated end time" readonly>
                                        </div>
                                    </div> --}}

                                    <!-- Day Use End Time -->
                                    <div class="mb-3 col-md-3">
                                        <label for="end_time" class="form-label"><strong>Day End Time (hr)</strong><span style="color: red; font-weight: bold;">*</span></label>
                                        <div class="input-group">
                                            <input type="text" id="end_time" name="end_time" class="form-control"
                                                placeholder="Calculated end time" readonly required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3 col-md-2">
                                    <label for="start_time" class="form-label"><strong>Type</strong><span style="color: red; font-weight: bold;">*</span></label>
                                        <select id="day_usage_type" name="day_usage_type" class="form-control" onchange="updateChargeLabel()" required>
                                            <option value="">Select a value</option>
                                            <option value="0" {{ old('day_usage_type') == '0' ? 'selected' : '' }}>Flat</option>
                                            <option value="1" {{ old('day_usage_type') == '1' ? 'selected' : '' }}>Percentage</option>
                                        </select>
                                    </div>

                                    <div class="mb-3 col-md-2">
                                        <label for="charge" class="form-label"><strong>Charge <span id="chargeType"></span></strong><span style="color: red; font-weight: bold;">*</span></label>
                                        <div class="input-group">
                                            <input type="number" id="charge" name="charge" class="form-control"
                                                placeholder="Enter charge" required value="{{ old('charge') }}">
                                        </div>
                                    </div>

                                    <!-- Auto Cancel Date -->
                                    <div class="mb-3 col-md-2">
                                        <label for="auto_cancel_date" class="form-label"><strong>Auto Cancel Date</strong><span style="color: red; font-weight: bold;">*</span></label>
                                        <select name="auto_cancel_date" id="auto_cancel_date" class="form-control" required>
                                            <option value="">Select Days</option>
                                            <option value="1" {{ old('auto_cancel_date') == '1' ? 'selected' : '' }}>D-1</option>
                                            <option value="2" {{ old('auto_cancel_date') == '2' ? 'selected' : '' }}>D-2</option>
                                            <option value="3" {{ old('auto_cancel_date') == '3' ? 'selected' : '' }}>D-3</option>
                                            <option value="4" {{ old('auto_cancel_date') == '4' ? 'selected' : '' }}>D-4</option>
                                            <option value="5" {{ old('auto_cancel_date') == '5' ? 'selected' : '' }}>D-5</option>
                                            <option value="6" {{ old('auto_cancel_date') == '6' ? 'selected' : '' }}>D-6</option>
                                            <option value="7" {{ old('auto_cancel_date') == '7' ? 'selected' : '' }}>D-7</option>
                                            <option value="8" {{ old('auto_cancel_date') == '8' ? 'selected' : '' }}>D-8</option>
                                            <option value="9" {{ old('auto_cancel_date') == '9' ? 'selected' : '' }}>D-9</option>
                                            <option value="10" {{ old('auto_cancel_date') == '10' ? 'selected' : '' }}>D-10</option>
                                            <option value="11" {{ old('auto_cancel_date') == '11' ? 'selected' : '' }}>D-11</option>
                                            <option value="12" {{ old('auto_cancel_date') == '12' ? 'selected' : '' }}>D-12</option>
                                            <option value="13" {{ old('auto_cancel_date') == '13' ? 'selected' : '' }}>D-13</option>
                                            <option value="14" {{ old('auto_cancel_date') == '14' ? 'selected' : '' }}>D-14</option>
                                        </select>
                                        @error('auto_cancel_date')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </fieldset>
                            </div>
                        </div>

                        <div class="row col-md-12">
                            <!-- Master image -->
                            <div class="mt-3 mb-3 col-md-4" id="master-image-field-wrap">
                                <div>
                                    <label for="master_image" class="form-label"><strong>Master
                                            Image</strong><span style="color: red; font-weight: bold;">*</span></label>
                                    <div id="master-drop-area" class="form-control"
                                        style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px;">
                                        Drag & Drop your files here or click to upload.
                                        {{-- No HTML required — validate on Save so we can show a clear message --}}
                                        <input type="file" id="master_image" name="master_image" accept="image/jpeg,image/png,image/webp,image/gif" style="display: none;">
                                    </div>
                                    <small class="text-muted d-block mt-1">Accepted: JPEG, PNG, WEBP, GIF. Maximum size: <strong>5 MB</strong>.</small>
                                    <div id="master-image-error" class="text-danger mt-1" style="display:none;">Please upload master image.</div>
                                    @error('master_image')
                                        <div class="text-danger mt-1 master-image-server-error">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div id="master-preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                    style="max-width: 30%; overflow-x: auto; white-space: nowrap;">
                                </div>
                            </div>

                            <!-- Additional Image drop -->
                            <div class="mt-3 mb-3 col-md-8">
                                <div>
                                    <label for="images" class="form-label"><strong>Additional
                                            Images</strong> <span class="text-muted small">(Optional)</span></label>
                                    <div id="drop-area" class="form-control"
                                        style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px;">
                                        Drag & Drop your files here or click to upload.
                                        {{-- Picker only (no name) — files are synced into all_images[] below --}}
                                        <input type="file" id="images" accept="image/jpeg,image/png,image/webp,image/gif" multiple style="display: none;">
                                    </div>
                                    <small class="text-muted d-block mt-1">Optional gallery images. Large images are auto-compressed. Max <strong>5 MB</strong> per file after compression.</small>
                                    <div id="additional-images-error" class="text-danger mt-1" style="display:none;"></div>
                                    @error('all_images')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                    @error('all_images.*')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror

                                    <div id="preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                        style="max-width: 100%; overflow-x: auto; white-space: nowrap;">
                                    </div>
                                </div>
                                {{-- Must include multiple or only 1 file is submitted --}}
                                <input type="file" name="all_images[]" id="all-images" accept="image/jpeg,image/png,image/webp,image/gif" multiple style="display: none;">

                            </div>
                        </div>

                        <div class="col-md-12 mb-3" id="description-field-wrap">
                            <label for="description" class="form-label"><strong>Hotel Description</strong><span
                                    style="color: red;">*</span></label>
                            <textarea id="summernote" name="description" class="form-control @error('description') is-invalid @enderror" rows="10">{{ old('description') }}</textarea>
                            <div id="description-client-error" class="text-danger mt-1" style="display:none;">Hotel description is mandatory.</div>
                        @error('description')
                            <div class="text-danger mt-1 description-server-error">{{ $message }}</div>
                        @enderror
                        </div>

                    <!-- Status -->
                    <div class="form-check form-switch" style = "padding-left: 3.450em;">
                        <label for="hotel_status" class="form-label"><strong>Status</strong><span style="color: red; font-weight: bold;">*</span></label>
                        <input type="hidden" name="hotel_status" value="0">
                        <input class="form-check-input" name="hotel_status" type="checkbox" id="hotel_status" value="1" {{ old('hotel_status', '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label"></label>
                        @error('hotel_status')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" id="hotelSaveBtn" class="btn btn-primary px-4">
                            <span class="hotel-save-btn-text">Save</span>
                            <span class="hotel-save-btn-loading d-none">
                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End of the form -->
@endsection
@section('scripts')
<!-- start editor -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

{{-- Find this section in your script and update it to include all time fields --}}
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
    flatpickr("#start_time", {
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
        const startTimeInput = document.getElementById('start_time');
        const durationInput = document.getElementById('duration');
        const endTimeInput = document.getElementById('end_time');

        if (!startTimeInput.value || !durationInput.value) {
            endTimeInput.value = ''; // Clear the end time if inputs are invalid
            return;
        }

        // Parse the start time into hours and minutes
        const [startHours, startMinutes] = startTimeInput.value.split(':').map(Number);
        const duration = parseInt(durationInput.value, 10);

        // Calculate the new end time
        const endDate = new Date();
        endDate.setHours(startHours + duration, startMinutes);

        // Format the end time as HH:MM
        const endHours = String(endDate.getHours()).padStart(2, '0');
        const endMinutes = String(endDate.getMinutes()).padStart(2, '0');
        endTimeInput.value = `${endHours}:${endMinutes}`;
    }

    // Add event listeners to update the end time
    document.getElementById('start_time').addEventListener('change', calculateEndTime);
    document.getElementById('duration').addEventListener('change', calculateEndTime);
</script>

<script>
    $(document).ready(function() {
        $('#summernote').summernote({
            height: 200,      
            minHeight: 200,   
            maxHeight: 500,   
            placeholder: 'Enter your content here...', 
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
        $('#country').select2({
            placeholder: "Search and Select Country",
            allowClear: true,
            width: '100%'
        });
        $('#auto_cancel_date').select2({
            placeholder: "Select Days",
            allowClear: true,
            width: '100%'
        });
    });
</script>

<script>
        $(document).ready(function() {
        // Get the user's role ID
        var userRoleId = {{ auth()->user()->role_id }};
        
        // Get the current user's country if they are a DMC or specific roles
        var userCountry = "{{ in_array(auth()->user()->role_id, [11, 35, 77, 84, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? auth()->user()->country : '' }}";
        var dmcId = "{{ in_array(auth()->user()->role_id, [11, 35, 77, 84, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? auth()->user()->userId : '' }}";
        
        // // Initialize Select2 for city (disabled until country is selected)
        $('#citySelect').select2({
            placeholder: "Select Country First",
            allowClear: true,
            width: '100%',
            disabled: true
        });
        
        // Check if the user role is DMC (role_id = 11) or similar roles
        if ([11, 35, 77, 84, 139, 140].includes(userRoleId)) {
            // Hide the DMC select box
            $('#dmc-container').hide();
            $('#dmc').prop('required', false);
            
            // Auto-fill the country field with the user's country
            $('#country').val(userCountry);
            
            // Load cities for this user
            loadCitiesForDmc(dmcId);
        } 
        // Check if the user role is admin or similar roles
        else if ([1, 2, 3, 4].includes(userRoleId)) {
            $('#dmc-container').show();
            $('#dmc').prop('required', true);
            
            // When DMC is changed (for admin users)
            $('#dmc').change(function() {
                var dmcId = $(this).val();
                if (dmcId) {
                    loadCitiesForDmc(dmcId);
                } else {
                    // Clear city select and country, disable city field
                    $('#citySelect').prop('disabled', true).empty().append('<option value="">Select Country First</option>').trigger('change');
                    $('#country').val('').trigger('change');
                }
            });
        } 
        // For other roles
        else {
            $('#dmc-container').hide();
            $('#dmc').prop('required', false);
        }
        
        // Function to load cities for DMC
        function loadCitiesForDmc(dmcId) {
            if (dmcId) {
                // Show loading state
                $('#citySelect').prop('disabled', true).empty().append('<option value="">Loading cities...</option>').trigger('change');
                
                // Add a debug statement
                console.log("Loading cities for DMC ID:", dmcId);
                
                $.ajax({
                    url: "{{ env('APP_URL') }}{{ route('fetch.cities_countries', [], false) }}",
                    type: "GET",
                    data: { dmc_id: dmcId },
                    dataType: 'json',
                    success: function(response) {
                        console.log("Response received:", response);
                        
                        // Clear loading state
                        $('#citySelect').empty();
                        
                        // Add default option
                        $('#citySelect').append('<option value="">Select a City</option>');
                        
                        // Add cities from response
                        if (response.cities && response.cities.length > 0) {
                            $.each(response.cities, function(key, city) {
                                $('#citySelect').append('<option value="' + city.name + '">' + city.name + '</option>');
                            });
                            // Enable city field when cities are loaded
                            $('#citySelect').prop('disabled', false);
                        } else {
                            // No cities found, keep disabled
                            $('#citySelect').append('<option value="">No cities available</option>');
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
                        
                        $('#citySelect').prop('disabled', true).empty().append('<option value="">Error loading cities</option>').trigger('change');
                    }
                });
            } else {
                // DMC cleared, disable city field
                $('#citySelect').prop('disabled', true).empty().append('<option value="">Select Country First</option>').trigger('change');
            }
        }
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
<script>
    $(document).ready(function () {
    // Conference Room Logic
    $('#conference').on('change', function () {
        if ($(this).val() == '1') {
            $('#conference-options').show();
        } else {
            $('#conference-options').hide();
            $('#conference-additional-fields').empty();
        }
    });

    $('#conference-add-more').on('click', function () {
        const newConferenceFields = `
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="conference_head" class="form-label"><strong>Head</strong></label>
                    <input type="text" class="form-control" name="conference_head[]" placeholder="Enter Head">
                </div>
                <div class="col-md-4">
                    <label for="conference_duration" class="form-label"><strong>Duration</strong></label>
                    <input type="text" class="form-control" name="conference_duration[]" placeholder="Enter Duration">
                </div>
                <div class="col-md-4">
                    <label for="conference_price" class="form-label"><strong>Price</strong></label>
                    <input type="number" class="form-control" name="conference_price[]" placeholder="Enter Price">
                </div>
            </div>
        `;
        $('#conference-additional-fields').append(newConferenceFields);
    });
    // Cancellation Details Logic
    $('#cancellation_type').on('change', function () {
        if ($(this).val() == '1') {
            $('#cancellation-options').show();
        } else {
            $('#cancellation-options').hide();
            $('#cancellation-additional-fields').empty();
        }
    });

    $('#cancellation-add-more').on('click', function () {
        const newCancellationFields = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="cancellation_duration" class="form-label"><strong>Duration</strong></label>
                    <input type="text" class="form-control" name="cancellation_duration[]" placeholder="Enter Duration">
                </div>
                <div class="col-md-6">
                    <label for="cancellation_price" class="form-label"><strong>Price</strong></label>
                    <input type="number" class="form-control" name="cancellation_price[]" placeholder="Enter Price">
                </div>
            </div>
        `;
        $('#cancellation-additional-fields').append(newCancellationFields);
    });
    });
</script>
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

</script>
<script>
    $(document).ready(function () {
        $('#date_range').daterangepicker({
            opens: 'right', // Opens to the right of the input
            autoApply: true, // Automatically apply the selected range
            locale: {
                format: 'MM/DD/YYYY', // Format of the dates
                separator: ' - ', // Separator between start and end dates
                applyLabel: "Apply",
                cancelLabel: "Clear"
            }
        });
        $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = document.querySelectorAll('.form-floating input');
        inputs.forEach(input => {
            input.addEventListener('input', function () {
                if (this.value) {
                    this.classList.add('has-value');
                } else {
                    this.classList.remove('has-value');
                }
            });
        });
    });
</script>
<script>
    $(document).ready(function() {
        // Function to update country code based on country name
        function updateCountryCode(countryName) {
            if (!countryName) return;
            
            // Convert country name to lowercase for case-insensitive comparison
            const countryNameLower = countryName.toLowerCase().trim();
            const countryCodeSelect = document.getElementById('country_code');
            
            // Loop through all options in the country code dropdown
            for (let i = 0; i < countryCodeSelect.options.length; i++) {
                const option = countryCodeSelect.options[i];
                const optionText = option.text.toLowerCase().trim();
                
                // If country name is found in the option text, select it
                if (optionText.includes(countryNameLower)) {
                    countryCodeSelect.value = option.value;
                    break;
                }
            }
        }
        
        // NEW: Fetch and populate city list based on selected country -----------------------------
        function loadCitiesByCountry(countryName) {
            if (!countryName) {
                // Disable city field if no country is selected
                $('#citySelect').prop('disabled', true).empty().append('<option value="">Select Country First</option>').trigger('change');
                return;
            }

            // Display loading option while fetching cities
            $('#citySelect').prop('disabled', true).empty().append('<option value="">Loading cities...</option>').trigger('change');

            $.ajax({
                url: "{{ env('APP_URL') }}{{ route('fetch-cities-by-country', [], false) }}",
                type: "GET",
                data: { country: countryName },
                dataType: 'json',
                success: function(response) {
                    // Clear current options and add a default placeholder
                    $('#citySelect').empty().append('<option value="">Select a City</option>');

                    if (response.cities && response.cities.length > 0) {
                        $.each(response.cities, function(index, city) {
                            $('#citySelect').append('<option value="' + city.name + '">' + city.name + '</option>');
                        });
                        // Enable city field when cities are loaded
                        $('#citySelect').prop('disabled', false);
                    } else {
                        // No cities found, keep disabled
                        $('#citySelect').append('<option value="">No cities available</option>');
                    }
                    // Refresh Select2 display
                    $('#citySelect').trigger('change');
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching cities by country:", error);
                    $('#citySelect').prop('disabled', true).empty().append('<option value="">Error loading cities</option>').trigger('change');
                }
            });
        }
        // ---------------------------------------------------------------------------

        // Update country code AND city list whenever the country selection changes
        $('#country').on('change', function() {
            const selectedCountryName = $(this).find('option:selected').text().trim();
            const selectedCountryValue = $(this).val();
            
            if (!selectedCountryValue || selectedCountryValue === '') {
                // Country cleared, disable city field
                $('#citySelect').prop('disabled', true).empty().append('<option value="">Select Country First</option>').trigger('change');
                return;
            }
            
            updateCountryCode(selectedCountryName);
            loadCitiesByCountry(selectedCountryName);
        });

        // For DMC and similar role users, update country code when page loads
        if ([11, 35, 77, 84, 139, 140].includes({{ auth()->user()->role_id }})) {
            const initDmcCountryName = $('#country option:selected').text().trim();
            updateCountryCode(initDmcCountryName);
            loadCitiesByCountry(initDmcCountryName);
        }

        // Update country code (and city list) when a DMC is selected (for admin users)
        $('#dmc').on('change', function() {
            const dmcId = $(this).val();
            if (dmcId) {
                $.ajax({
                    url: "{{ route('fetch.cities_countries') }}",
                    type: "GET",
                    data: { dmc_id: dmcId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.country) {
                            $('#country').val(response.country).trigger('change');
                            updateCountryCode(response.country);
                            loadCitiesByCountry(response.country);
                        }
                    }
                });
            } else {
                // DMC cleared, disable city field
                $('#citySelect').prop('disabled', true).empty().append('<option value="">Select Country First</option>').trigger('change');
                $('#country').val('').trigger('change');
            }
        });

        // Initialize on page load - update country code & cities if a country is pre-selected
        if ($('#country').val()) {
            const initialCountryName = $('#country option:selected').text().trim();
            updateCountryCode(initialCountryName);
            loadCitiesByCountry(initialCountryName);
        }
    });
</script>

{{-- Regex validation message --}}

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

        const longitudeRegex = /^-?(180(\.0{1,9})?|((1[0-7][0-9]|[1-9]?[0-9])(\.\d{1,9})?))$/;

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
                    <li>Example: 78.1234566584</li>
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

    const MAX_IMAGE_BYTES = 5 * 1024 * 1024; // 5 MB after compression

    function compressHotelImage(file, quality = 0.8, maxWidth = 1920, maxHeight = 1080) {
        return new Promise((resolve, reject) => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();
            img.onload = function () {
                let { width, height } = img;
                if (width > maxWidth) {
                    height = (height * maxWidth) / width;
                    width = maxWidth;
                }
                if (height > maxHeight) {
                    width = (width * maxHeight) / height;
                    height = maxHeight;
                }
                canvas.width = width;
                canvas.height = height;
                ctx.drawImage(img, 0, 0, width, height);
                const outType = (file.type === 'image/png' || file.type === 'image/webp') ? file.type : 'image/jpeg';
                canvas.toBlob((blob) => {
                    if (!blob) {
                        reject(new Error('Compression failed'));
                        return;
                    }
                    const name = outType === 'image/jpeg' && !/\.jpe?g$/i.test(file.name)
                        ? file.name.replace(/\.\w+$/, '.jpg')
                        : file.name;
                    resolve(new File([blob], name, { type: outType, lastModified: Date.now() }));
                }, outType, quality);
            };
            img.onerror = () => reject(new Error('Failed to load image'));
            img.src = URL.createObjectURL(file);
        });
    }

    async function compressUntilUnderLimit(file) {
        if (file.size <= MAX_IMAGE_BYTES) {
            return file;
        }
        let quality = 0.8;
        let compressed = await compressHotelImage(file, quality, 1600, 1200);
        while (compressed.size > MAX_IMAGE_BYTES && quality > 0.4) {
            quality -= 0.15;
            compressed = await compressHotelImage(file, quality, 1280, 960);
        }
        return compressed;
    }

    function syncAllImagesInput() {
        if (!allImagesInput) return;
        const dt = new DataTransfer();
        files.forEach((file) => dt.items.add(file));
        allImagesInput.files = dt.files;
    }

    async function handleFiles(newFiles) {
        const errEl = document.getElementById('additional-images-error');
        const rejected = [];
        const accepted = [];

        const loadingDiv = document.createElement('div');
        loadingDiv.innerHTML = '<div class="alert alert-info py-2 px-3 mb-0"><i class="fas fa-spinner fa-spin"></i> Preparing images...</div>';
        fileList.appendChild(loadingDiv);

        for (const file of Array.from(newFiles || [])) {
            if (!file.type.startsWith('image/')) {
                rejected.push(`${file.name} is not a valid image file.`);
                continue;
            }
            // Hard reject extremely large originals (>25MB) before trying compression
            if (file.size > 25 * 1024 * 1024) {
                rejected.push(`${file.name} is too large (${(file.size / (1024 * 1024)).toFixed(1)} MB). Please use an image under 25 MB.`);
                continue;
            }
            try {
                const prepared = await compressUntilUnderLimit(file);
                if (prepared.size > MAX_IMAGE_BYTES) {
                    rejected.push(`${file.name} could not be compressed under 5 MB. Please use a smaller image.`);
                    continue;
                }
                accepted.push(prepared);
            } catch (e) {
                rejected.push(`Could not process ${file.name}. Please try another image.`);
            }
        }

        loadingDiv.remove();

        if (errEl) {
            if (rejected.length) {
                errEl.style.display = 'block';
                errEl.textContent = rejected.join(' ');
            } else {
                errEl.style.display = 'none';
                errEl.textContent = '';
            }
        }

        files = [...files, ...accepted];
        updateFileList();
        // Reset picker so the same file can be chosen again if needed
        if (fileInput) fileInput.value = '';
    }

    function updateFileList() {
        fileList.innerHTML = '';
        const visibleFiles = showAllImages ? files : files.slice(0, MAX_VISIBLE_IMAGES);

        visibleFiles.forEach((file) => {
            const imageWrapper = document.createElement('div');
            imageWrapper.style.position = 'relative';
            imageWrapper.style.display = 'inline-block';
            imageWrapper.style.margin = '10px';
            imageWrapper.style.width = '100px';
            imageWrapper.style.height = '100px';

            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';

            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
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
            deleteButton.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const fileIndex = files.indexOf(file);
                if (fileIndex > -1) files.splice(fileIndex, 1);
                updateFileList();
            });

            imageWrapper.appendChild(img);
            imageWrapper.appendChild(deleteButton);
            fileList.appendChild(imageWrapper);
        });

        // Always sync ALL selected files into the submit input
        syncAllImagesInput();

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
            moreBadge.addEventListener('click', () => {
                showAllImages = true;
                updateFileList();
            });
            fileList.appendChild(moreBadge);
        }

        const sizeInfo = document.createElement('div');
        sizeInfo.className = 'w-100';
        const totalMb = (files.reduce((t, f) => t + f.size, 0) / (1024 * 1024)).toFixed(1);
        sizeInfo.innerHTML = files.length
            ? `<small class="text-muted">${files.length} image(s) ready · ${totalMb} MB total</small>`
            : '';
        if (files.length) fileList.appendChild(sizeInfo);
    }

    // Expose for submit handler
    window.syncHotelAllImagesInput = syncAllImagesInput;
    window.getHotelAdditionalFiles = () => files;

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

    // Process and display files (compress large master images so they still save)
    async function masterHandleFiles(selectedFiles) {
        const maxBytes = 5 * 1024 * 1024; // 5 MB
        const errEl = document.getElementById('master-image-error');
        const file = selectedFiles && selectedFiles[0] ? selectedFiles[0] : null;
        if (!file) {
            return;
        }

        if (!file.type.startsWith('image/')) {
            if (errEl) {
                errEl.style.display = 'block';
                errEl.textContent = `${file.name} is not a valid image file. Please upload JPEG, PNG, WEBP or GIF.`;
            }
            masterFileInput.value = '';
            return;
        }

        if (file.size > 25 * 1024 * 1024) {
            if (errEl) {
                errEl.style.display = 'block';
                errEl.textContent = `Master image is ${(file.size / (1024 * 1024)).toFixed(1)} MB. Please use an image under 25 MB.`;
            }
            masterFileInput.value = '';
            return;
        }

        let finalFile = file;
        try {
            if (typeof compressUntilUnderLimit === 'function') {
                finalFile = await compressUntilUnderLimit(file);
            }
        } catch (e) {
            console.error(e);
        }

        if (finalFile.size > maxBytes) {
            if (errEl) {
                errEl.style.display = 'block';
                errEl.textContent = `Master image could not be compressed under 5 MB. Please use a smaller image.`;
            }
            masterFileInput.value = '';
            return;
        }

        if (errEl) {
            errEl.style.display = 'none';
            errEl.textContent = '';
        }
        if (masterDropArea) {
            masterDropArea.style.borderColor = '#007bff';
            masterDropArea.style.backgroundColor = 'white';
        }

        // Put compressed file back into the real input used for submit
        try {
            const dt = new DataTransfer();
            dt.items.add(finalFile);
            masterFileInput.files = dt.files;
        } catch (e) {
            console.warn('Could not assign compressed master file', e);
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            if (masterFileCounter > 0) {
                masterPreviewContainer.innerHTML = '';
                masterFileCounter = 0;
            }
            masterFileCounter++;
            masterImagePreview(e.target.result);
        };
        reader.readAsDataURL(finalFile);
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

<!-- Form validation for master image -->
<script>
    // Function to toggle the chain name field based on ownership selection
    function toggleChainField() {
        const ownershipSelect = document.getElementById('ownership');
        const chainNameContainer = document.getElementById('chain_name_container');
        const chainNameInput = document.getElementById('chain_name');
        
        // Show chain name field if "Chain Hotels" (value 1) is selected
        if (ownershipSelect.value === '1') {
            chainNameContainer.style.display = 'block';
            chainNameInput.setAttribute('required', 'required');
        } else {
            chainNameContainer.style.display = 'none';
            chainNameInput.removeAttribute('required');
            chainNameInput.value = ''; // Clear the input when hidden
        }
    }
    
    // Call the function on page load to set initial state
    document.addEventListener('DOMContentLoaded', function() {
        toggleChainField();
        updateChargeLabel();
    });
    
    // Function to update the charge label based on the selected day usage type
    function updateChargeLabel() {
        const dayUsageType = document.getElementById('day_usage_type').value;
        const chargeTypeSpan = document.getElementById('chargeType');
        
        if (dayUsageType === '1') {
            chargeTypeSpan.textContent = '(%)';
        } else if (dayUsageType === '0') {
            chargeTypeSpan.textContent = '(Flat)';
        } else {
            chargeTypeSpan.textContent = '';
        }
    }
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const hotelForm = document.getElementById('hotelForm');
    const saveBtn = document.getElementById('hotelSaveBtn');
    const MAX_IMAGE_BYTES = 5 * 1024 * 1024; // 5 MB
    const oldCountry = @json(old('country'));
    const oldLocation = @json(old('location'));
    const focusField = @json(session('focus_field'));

    function stripHtml(html) {
        const tmp = document.createElement('div');
        tmp.innerHTML = html || '';
        return (tmp.textContent || tmp.innerText || '').replace(/\u00a0/g, ' ').trim();
    }

    function focusDescription() {
        const wrap = document.getElementById('description-field-wrap');
        if (wrap) {
            wrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        try {
            if (window.jQuery && $('#summernote').length) {
                $('#summernote').summernote('focus');
            }
        } catch (e) {}
        const err = document.getElementById('description-client-error');
        if (err) err.style.display = 'block';
        const note = document.querySelector('#description-field-wrap .note-editor');
        if (note) {
            note.style.borderColor = '#dc3545';
        }
    }

    function focusMasterImage(message) {
        const wrap = document.getElementById('master-image-field-wrap');
        const drop = document.getElementById('master-drop-area');
        const masterErr = document.getElementById('master-image-error');
        if (wrap) wrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
        if (drop) {
            drop.style.borderColor = '#dc3545';
            drop.style.backgroundColor = '#fff5f5';
        }
        if (masterErr) {
            masterErr.style.display = 'block';
            masterErr.textContent = message || 'Please upload master image.';
        }
    }

    // Only after Save validation redirect — never on first page load
    if (focusField === 'description' || document.querySelector('#description-field-wrap .description-server-error')) {
        setTimeout(focusDescription, 400);
    }
    if (focusField === 'master_image' || document.querySelector('#master-image-field-wrap .master-image-server-error')) {
        setTimeout(() => focusMasterImage(), 400);
    }
    if (focusField === 'all_images') {
        const drop = document.getElementById('drop-area');
        if (drop) {
            setTimeout(() => drop.scrollIntoView({ behavior: 'smooth', block: 'center' }), 400);
        }
    }

    // Restore country / city after validation failure
    if (oldCountry) {
        const countryEl = document.getElementById('country');
        if (countryEl && !countryEl.value) {
            countryEl.value = oldCountry;
        }
        // Trigger city load if select2/country change handlers exist
        setTimeout(function () {
            if (window.jQuery) {
                $('#country').val(oldCountry).trigger('change');
                const trySelectCity = function (attempts) {
                    if (!oldLocation || attempts <= 0) return;
                    const citySelect = $('#citySelect');
                    if (citySelect.find('option[value="' + oldLocation.replace(/"/g, '\\"') + '"]').length) {
                        citySelect.val(oldLocation).trigger('change');
                    } else {
                        setTimeout(() => trySelectCity(attempts - 1), 400);
                    }
                };
                setTimeout(() => trySelectCity(15), 600);
            }
        }, 300);
    }

    if (!hotelForm || !saveBtn) return;

    hotelForm.addEventListener('submit', function (e) {
        // Sync Summernote into textarea
        try {
            if (window.jQuery && $('#summernote').length) {
                $('#summernote').val($('#summernote').summernote('code'));
            }
        } catch (err) {}

        const descVal = document.getElementById('summernote') ? document.getElementById('summernote').value : '';
        if (!stripHtml(descVal)) {
            e.preventDefault();
            focusDescription();
            return false;
        }
        const descErr = document.getElementById('description-client-error');
        if (descErr) descErr.style.display = 'none';

        // Master image — only validate on Save (same pattern as description)
        const masterInput = document.getElementById('master_image');
        const masterErr = document.getElementById('master-image-error');
        const hasMasterFile = masterInput && masterInput.files && masterInput.files.length > 0;
        const hasMasterPreview = typeof masterFileCounter !== 'undefined' && masterFileCounter > 0;
        if (!hasMasterFile && !hasMasterPreview) {
            e.preventDefault();
            focusMasterImage('Please upload master image.');
            return false;
        }
        if (hasMasterFile) {
            const f = masterInput.files[0];
            if (f.size > MAX_IMAGE_BYTES) {
                e.preventDefault();
                focusMasterImage(`Master image is ${(f.size / (1024 * 1024)).toFixed(1)} MB. Maximum allowed size is 5 MB. Please compress the image and try again.`);
                return false;
            }
        }
        if (masterErr) masterErr.style.display = 'none';
        const masterDrop = document.getElementById('master-drop-area');
        if (masterDrop) {
            masterDrop.style.borderColor = '#007bff';
            masterDrop.style.backgroundColor = '';
        }

        // Ensure additional images are attached to the form input before submit
        if (typeof window.syncHotelAllImagesInput === 'function') {
            window.syncHotelAllImagesInput();
        }
        const additionalFiles = typeof window.getHotelAdditionalFiles === 'function'
            ? window.getHotelAdditionalFiles()
            : (typeof files !== 'undefined' ? files : []);
        if (additionalFiles.length) {
            const oversized = additionalFiles.filter(f => f.size > MAX_IMAGE_BYTES);
            if (oversized.length) {
                e.preventDefault();
                const addErr = document.getElementById('additional-images-error');
                if (addErr) {
                    addErr.style.display = 'block';
                    addErr.textContent = oversized.map(f => `${f.name} exceeds 5 MB.`).join(' ') + ' Please remove or use smaller images.';
                }
                document.getElementById('drop-area')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
        }

        // Loading state
        saveBtn.disabled = true;
        const textEl = saveBtn.querySelector('.hotel-save-btn-text');
        const loadingEl = saveBtn.querySelector('.hotel-save-btn-loading');
        if (textEl) textEl.classList.add('d-none');
        if (loadingEl) loadingEl.classList.remove('d-none');
    });
});
</script>
@endsection