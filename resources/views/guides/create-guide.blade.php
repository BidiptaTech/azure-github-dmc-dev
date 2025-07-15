@extends('layouts.layout')
@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
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

    .time-error-popup {
        position: fixed;
        top: 15%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: rgba(255, 0, 0, 0.85); /* Softer red */
        color: white;
        padding: 10px 20px; /* Adjust padding for better appearance */
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        text-align: center;
        display: none;
        z-index: 9999;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        animation: fadeIn 0.3s ease-in-out, fadeOut 0.5s ease-in-out 2.5s;
        width: 250px; /* Set a specific width for the popup */
    }

    /* Fade-in and fade-out animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translate(-50%, -55%); }
        to { opacity: 1; transform: translate(-50%, -50%); }
    }

    @keyframes fadeOut {
        from { opacity: 1; transform: translate(-50%, -50%); }
        to { opacity: 0; transform: translate(-50%, -55%); }
    }

    .time-error-popup i {
    margin-right: 8px; /* Space between icon and text */
    color: yellow; /* Warning color */
    font-size: 14px; /* Adjust icon size */
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
<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Add New Guide
                <a href="{{ route('guide.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            {{-- @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif --}}

             @if (session('error'))
                <div class="alert alert-danger border-0 border-start border-5 border-danger-subtle shadow-sm px-4 py-3 rounded-3">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                        <div>
                            <h6 class="mb-2 fw-semibold text-danger">Please fix the following errors:</h6>
                            <ul class="mb-0 ps-3"><li class="small">{{ session('error') }}</li></ul>
                        </div>
                    </div>
                </div>
            @endif
            <form id="guideForm" method="POST" action="{{ route('guide.store') }}" enctype="multipart/form-data"
                class="card-body">
                @csrf
                <!-- Hidden Fields -->

                <div id="guideDetailsContainer">
                    <div class="guide-form">
                        <div class="row">
                            <!-- Select DMC Name -->
                            @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 23 || auth()->user()->role_id == 25 || auth()->user()->role_id == 45 || auth()->user()->role_id == 61 || auth()->user()->role_id == 100 || auth()->user()->role_id == 101)
                            <div class="mb-3 col-md-3" id="dmc-container" style="display: none;">
                                <label for="dmc" class="form-label"><strong>DMC</strong><span style="color: red; font-weight: bold;">*</span></label>
                                <select id="dmc" name="dmc" class="form-control" required>
                                    <option value="">Select DMC</option>
                                    @foreach ($dmcs as $dmc)
                                        <option value="{{ $dmc->userId }}">{{ $dmc->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <!-- Guide Salutation -->
                            <div class="col-md-3 mb-3">
                                <label for="salutation" class="form-label"><strong>Salutation</strong><span class="text-danger">*</span></label>
                                <select class="form-control" name="salutation" required>
                                    <option value="">Select Salutation</option>
                                    <option value="Mr" {{ old('salutation') == 'Mr' ? 'selected' : '' }}>Mr.</option>
                                    <option value="Mrs" {{ old('salutation') == 'Mrs' ? 'selected' : '' }}>Mrs.</option>
                                    <option value="Miss" {{ old('salutation') == 'Miss' ? 'selected' : '' }}>Ms.</option>
                                    <option value="Dear" {{ old('salutation') == 'Dear' ? 'selected' : '' }}>Dear</option>
                                </select>
                                @error('salutation')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                    <label for="guide_gender" class="form-label"><strong>Guide Gender</strong><span class="text-danger">*</span></label>
                                    <select id="guide_gender" name="guide_gender" class="form-select">
                                        <option value="">Select gender</option>
                                        <option value="Male" {{ old('guide_gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('guide_gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                        <option value="Other" {{ old('guide_gender') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('guide_gender')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                            </div>

                            <!-- guide Name -->
                            <div class="col-md-3 mb-3">
                                <label for="name" class="form-label"><strong>Guide Name</strong><span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" placeholder="Enter Guide Name"
                                    value="{{ old('name') }}" required>
                                @error('name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Contact No -->
                            <div class="col-md-3 mb-3">
                                <label for="contact_no" class="form-label">
                                    <strong>Contact No</strong><span class="text-danger">*</span>
                                </label>
                                <input name="contact_no" type="text" id="contact_no" class="form-control"
                                    placeholder="Enter Contact No" value="{{ old('contact_no') }}" required
                                    oninput="validatePhoneNumber(this)">
                                <small class="validation-message text-danger" id="contact_no-validation-message"></small>
                                @error('contact_no')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- email -->
                            <div class="col-md-3 mb-4">
                                <label for="email" class="form-label"><strong>Email</strong><span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="email" name="email" 
                                    placeholder="Enter Email..." value="{{ old('email') }}" required
                                    oninput="validateEmail(this)">
                                <small class="validation-message text-danger" id="email-validation-message"></small>
                                @error('email')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Country -->
                            <div class="mb-3 col-md-3">
                                <label for="country" class="form-label"><strong>Country</strong>
                                    <span style="color: red; font-weight: bold;">*</span>
                                </label>
                                
                                @if(in_array(auth()->user()->role_id, [1, 20]))
                                    <select class="form-control" id="country" name="country" required onchange="validateDriverAge(document.getElementById('driver_age'))">
                                        <option value="">Select Country</option>
                                        @foreach($country as $countryOption)
                                            <option value="{{ $countryOption->name }}">{{ $countryOption->name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" class="form-control" id="country" onchange="validateDriverAge(document.getElementById('driver_age'))" 
                                    value="{{in_array(auth()->user()->role_id, [11, 35, 75, 102]) ? $userCountry : ''}}"
                                        placeholder="{{ auth()->user()->role_id == 11 ? 'Your country' : 'Select DMC First' }}" 
                                        name="country" required 
                                        {{ auth()->user()->role_id == 11 ? 'readonly' : 'readonly' }}>
                                @endif
                                
                                @error('country')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- City -->
                            <div class="col-md-3 mb-3">
                                <label for="city" class="form-label"><strong>City</strong><span class="text-danger">*</span></label>
                                @php
                                    $roleId = auth()->user()->role_id;
                                    $placeholder = $roleId == 11 ? 'Select City' : 'Select DMC First';
                                @endphp
                                
                                <select name="city" id="citySelect" class="form-control" required>
                                    <option value="">{{ $placeholder }}</option>
                                    @if(in_array($roleId, [11, 35, 75, 102]))
                                        @foreach($cities as $city)
                                            <option value="{{ $city->name }}" {{ old('city') == $city->name ? 'selected' : '' }}>{{ $city->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('city')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Service Type -->
                            <div class="col-md-3 mb-3">
                                <label for="service_type" class="form-label"><strong>Service Type</strong><span
                                        class="text-danger">*</span></label>
                                <select id="service_type" type="text" class="form-select" name="service_type"
                                    placeholder="Enter service type..." required>
                                    <option value="">Select</option>
                                    <option value="1" {{ old('service_type') == '1' ? 'selected' : '' }}>Private</option>
                                    <option value="2" {{ old('service_type') == '2' ? 'selected' : '' }}>Shared</option>
                                    <option value="3" {{ old('service_type') == '3' ? 'selected' : '' }}>Both</option>
                                </select>
                                @error('service_type')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Guide Age -->
                                <div class="col-md-3 mb-3">
                                    <label for="guide_age" class="form-label"><strong>Guide Age</strong><span class="text-danger">*</span></label>
                                    <input id="guide_age" type="number" class="form-control" name="guide_age" 
                                           value="{{ old('guide_age') }}" placeholder="Enter Guide Age" oninput="validateDriverAge(this)">
                                    <small class="validation-message text-danger" id="guide_age-validation-message"></small>
                                    @error('guide_age')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                            

                            <!-- Guide image -->
                            <div class="col-md-3">
                                <div>
                                    <label for="master_image" class="form-label"><strong>Profile
                                            Image</strong><span style="color: red; font-weight: bold;">*</span></label>
                                    <div id="master-drop-area" class="form-control"
                                        style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px;">
                                        Drag & Drop your files here or click to upload.
                                        <input type="file" id="master_image" name="master_image" style="display: none;"
                                            required>
                                    </div>
                                </div>
                                <div id="master-preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                    style="max-width: 30%; overflow-x: auto; white-space: nowrap;">
                                </div>
                            </div>

                            <div id="guide_language" class="col-md-12 mb-3">
                                <fieldset>
                                    <h5 class="card-title mb-3">Languages & Proficiency</h5>
                                    <div class="row" id="language-container">
                                        @if(old('languages'))
                                            @foreach(old('languages') as $index => $language)
                                                <div class="language-row d-flex mb-3">
                                                    <!-- Languages Dropdown -->
                                                    <div class="col-md-5">
                                                        <label for="languages" class="form-label"><strong>Languages</strong>
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-control language-select" name="languages[]" required>
                                                            <option value="">Select Language</option>
                                                            @foreach($languages as $lang)
                                                                <option value="{{ $lang->name }}" {{ $language == $lang->name ? 'selected' : '' }}>{{ $lang->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('languages.'.$index)
                                                            <div class="text-danger mt-1">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                            
                                                    <!-- Language Proficiency Dropdown -->
                                                    <div class="col-md-5 ms-2">
                                                        <label for="language_proficiency" class="form-label"><strong>Proficiency</strong>
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-select proficiency-select" name="language_proficiency[]" required>
                                                            <option value="">Select</option>
                                                            <option value="Beginner" {{ old('language_proficiency')[$index] == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                                                            <option value="Intermediate" {{ old('language_proficiency')[$index] == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                                                            <option value="Fluent" {{ old('language_proficiency')[$index] == 'Fluent' ? 'selected' : '' }}>Fluent</option>
                                                            <option value="Expert" {{ old('language_proficiency')[$index] == 'Expert' ? 'selected' : '' }}>Expert</option>
                                                            <option value="Mother Tongue" {{ old('language_proficiency')[$index] == 'Mother Tongue' ? 'selected' : '' }}>Mother Tongue</option>
                                                        </select>
                                                        @error('language_proficiency.'.$index)
                                                            <div class="text-danger mt-1">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                            
                                                    <!-- Remove Button (Hidden for First Row) -->
                                                    <div class="col-md-1 mb-2 d-flex align-items-end">
                                                        <button type="button" class="btn btn-danger remove-language {{ $index == 0 ? 'd-none' : '' }}">Remove</button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="language-row d-flex mb-3">
                                                <!-- Languages Dropdown -->
                                                <div class="col-md-5">
                                                    <label for="languages" class="form-label"><strong>Languages</strong>
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select class="form-control language-select" name="languages[]" required>
                                                        <option value="">Select Language</option>
                                                        @foreach($languages as $c)
                                                            <option value="{{ $c->name }}">{{ $c->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('languages.0')
                                                        <div class="text-danger mt-1">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                        
                                                <!-- Language Proficiency Dropdown -->
                                                <div class="col-md-5 ms-2">
                                                    <label for="language_proficiency" class="form-label"><strong>Proficiency</strong>
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select class="form-select proficiency-select" name="language_proficiency[]" required>
                                                        <option value="">Select</option>
                                                        <option value="Beginner">Beginner</option>
                                                        <option value="Intermediate">Intermediate</option>
                                                        <option value="Fluent">Fluent</option>
                                                        <option value="Expert">Expert</option>
                                                        <option value="Mother Tongue">Mother Tongue</option>
                                                    </select>
                                                    @error('language_proficiency.0')
                                                        <div class="text-danger mt-1">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                        
                                                <!-- Remove Button (Hidden for First Row) -->
                                                <div class="col-md-1 mb-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-danger remove-language d-none">Remove</button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                            
                                    <!-- Add More Button -->
                                    <div class="col-md-2 mb-2 d-flex align-items-end">
                                        <button type="button" id="addmore" class="btn btn-primary">Add More</button>
                                    </div>
                                </fieldset>
                            </div>

                            <!-- License -->
                            <fieldset id="License" class="border p-4 rounded mb-4">
                                <h5 class="card-title mb-3">License</h5>
                                <div class="row">

                                    <!-- Government License No -->
                                    <div class="col-md-3">
                                        <label for="license_no" class="form-label"><strong>Government License
                                                No</strong><span class="text-danger">*</span></label>
                                        <input type="text" step="0.1" class="form-control" name="license_no"
                                            placeholder="Enter Gov. License No" value="{{ old('license_no') }}" required>
                                        @error('license_no')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <!-- license_exp_date -->
                                    <div class="col-md-3">
                                        <label for="license_exp_date" class="form-label"><strong>License Expiry
                                                Date</strong><span class="text-danger">*</span></label>
                                        <input type="date" step="0.01" class="form-control" name="license_exp_date"
                                            placeholder="Enter Cost" value="{{ old('license_exp_date') }}" required>
                                        @error('license_exp_date')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Experience -->
                                    <div class="col-md-2">
                                        <label for="experience" class="form-label"><strong>Experience (In
                                                Years)</strong><span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="experience" name="experience"
                                            placeholder="Enter Experience" value="{{ old('experience') }}" required
                                            oninput="validateNumericPrice(this)">
                                        <small class="validation-message text-danger" id="experience-validation-message"></small>
                                        @error('experience')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- License image -->
                                    <div class="mb-3 col-md-3">
                                        <div>
                                            <label for="license_image" class="form-label"><strong>License
                                                    Image</strong><span
                                                    style="color: red; font-weight: bold;">*</span></label>
                                            <div id="license-drop-area" class="form-control"
                                                style="padding: 10px; border: 2px dashed #007bff; text-align: center; height: 50px;">
                                                Drag & Drop your files here or click to upload.
                                                <input type="file" id="license_image" name="license_image"
                                                    style="display: none;" required>
                                            </div>
                                        </div>
                                        <div id="license-preview-container" class="mt-1 d-flex flex-wrap gap-2"
                                            style="max-width: 30%; overflow-x: auto; white-space: nowrap;">
                                        </div>

                                    </div>
                                </div>
                            </fieldset>

                            <!-- Rate -->
                            <fieldset id="rate" class="border p-4 rounded mb-4">
                                <h5 class="card-title mb-3">Rates</h5>
                                <div class="row">

                                    <!-- Minimum Base Price -->
                                    <div class="col-md-3">
                                        <label for="day_rate" class="form-label"><strong>Minimun Base Price</strong><span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="day_rate" name="day_rate"
                                            placeholder="Enter Day Rate" value="{{ old('day_rate') }}" required
                                            oninput="validateNumericPrice(this)">
                                        <small class="validation-message text-danger" id="day_rate-validation-message"></small>
                                        @error('day_rate')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <!-- night_surcharge -->
                                    <div class="col-md-3">
                                        <label for="night_surcharge" class="form-label"><strong>Night
                                                Surcharge</strong><span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="night_surcharge" name="night_surcharge"
                                            placeholder="Enter Night Surcharge" value="{{ old('night_surcharge') }}" required
                                            oninput="validateNumericPrice(this)">
                                        <small class="validation-message text-danger" id="night_surcharge-validation-message"></small>
                                        @error('night_surcharge')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Night Start Time -->
                                    <div class="col-md-2">
                                        <label for="night_start_time" class="form-label"><strong>Night Start Time</strong><span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="night_start_time" name="night_start_time" placeholder="Select start time" value="{{ old('night_start_time') }}">
                                        @error('night_start_time')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Night End Time -->
                                    <div class="col-md-2">
                                        <label for="night_end_time" class="form-label"><strong>Night End Time</strong><span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="night_end_time" name="night_end_time" placeholder="Select end time" value="{{ old('night_end_time') }}">
                                        @error('night_end_time')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Hourly Price -->
                                    <div class="col-md-2 mb-3">
                                        <label for="hourly_price" class="form-label"><strong>Hourly Price</strong><span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="hourly_price" name="hourly_price"
                                            placeholder="Enter hourly_price" value="{{ old('hourly_price') }}" required
                                            oninput="validateNumericPrice(this)">
                                        <small class="validation-message text-danger" id="hourly_price-validation-message"></small>
                                        @error('hourly_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Two Hour Price -->
                                    <div class="col-md-2 mb-3">
                                        <label for="two_hour_price" class="form-label"><strong>Two Hour
                                                Price</strong><span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="two_hour_price" name="two_hour_price"
                                            placeholder="Enter two_hourly_price" value="{{ old('two_hour_price') }}" required
                                            oninput="validateNumericPrice(this)">
                                        <small class="validation-message text-danger" id="two_hour_price-validation-message"></small>
                                        @error('two_hour_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Four Hourly Price -->
                                    <div class="col-md-2 mb-3">
                                        <label for="four_hour_price" class="form-label"><strong>Four Hour
                                                Price</strong><span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="four_hour_price" name="four_hour_price"
                                            placeholder="Enter four_hour_price" value="{{ old('four_hour_price') }}" required
                                            oninput="validateNumericPrice(this)">
                                        <small class="validation-message text-danger" id="four_hour_price-validation-message"></small>
                                        @error('four_hour_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Six Hour Price -->
                                    <div class="col-md-2 mb-3">
                                        <label for="six_hour_price" class="form-label"><strong>Six Hour
                                                Price</strong><span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="six_hour_price" name="six_hour_price"
                                            placeholder="Enter six_hour_price" value="{{ old('six_hour_price') }}" required
                                            oninput="validateNumericPrice(this)">
                                        <small class="validation-message text-danger" id="six_hour_price-validation-message"></small>
                                        @error('six_hour_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Eight Hour Price -->
                                    <div class="col-md-2 mb-3">
                                        <label for="eight_hour_price" class="form-label"><strong>Eight Hour
                                                Price</strong><span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="eight_hour_price" name="eight_hour_price"
                                            placeholder="Enter eight_hour_price" value="{{ old('eight_hour_price') }}" required
                                            oninput="validateNumericPrice(this)">
                                        <small class="validation-message text-danger" id="eight_hour_price-validation-message"></small>
                                        @error('eight_hour_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Ten Hour Price -->
                                    <div class="col-md-2 mb-3">
                                        <label for="ten_hour_price" class="form-label"><strong>Ten Hour
                                                Price</strong><span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="ten_hour_price" name="ten_hour_price"
                                            placeholder="Enter ten_hour_price" value="{{ old('ten_hour_price') }}" required
                                            oninput="validateNumericPrice(this)">
                                        <small class="validation-message text-danger" id="ten_hour_price-validation-message"></small>
                                        @error('ten_hour_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Twelve Hourly Price -->
                                    <div class="col-md-2 mb-3">
                                        <label for="twelve_hour_price" class="form-label"><strong>Twelve Hour
                                                Price</strong><span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="twelve_hour_price" name="twelve_hour_price"
                                            placeholder="Enter twelve_hour_price" value="{{ old('twelve_hour_price') }}" required
                                            oninput="validateNumericPrice(this)">
                                        <small class="validation-message text-danger" id="twelve_hour_price-validation-message"></small>
                                        @error('twelve_hour_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </fieldset>

                            <!-- about -->
                            <div class="col-md-12 mb-3">
                                <label for="about" class="form-label"><strong>About</strong><span
                                        class="text-danger">*</span></label>
                                <textarea id="summernote" name="about" class="form-control" rows="10"
                                    placeholder="Write About Guide..." required>{{ old('about') }}</textarea>
                                @error('about')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>

                    <!-- Status -->
                    <div class="form-check form-switch">
                        <label for="guide_status" class="form-label"><strong>Status</strong><span
                                style="color: red; font-weight: bold;">*</span></label>
                        <input type="hidden" name="guide_status" value="0">
                        <input class="form-check-input" name="guide_status" type="checkbox" id="guide_status" value="1"
                            {{ old('guide_status', '1') == '1' ? 'checked' : '' }} required>
                        <label class="form-check-label"></label>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-3 mt-4">
                        <button type="submit" class="btn btn-primary px-4">Save</button>
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
<script>
$(document).ready(function() {
    $('#summernote').summernote({
        height: 200,
        minHeight: 200,
        maxHeight: 500,
        placeholder: 'Enter your content here...',
        callbacks: {
            onInit: function() {
                // Check if there's old content
                var oldContent = '{!! old("about") !!}';
                if (oldContent) {
                    $('#summernote').summernote('code', oldContent);
                }
            }
        }
    });
});
</script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
    // Initialize flatpickr for all time input fields
    flatpickr("#night_start_time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i", // 24-hour format
        time_24hr: true,
        minuteIncrement: 15
    });

    // Add configuration for check out time
    flatpickr("#night_end_time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        minuteIncrement: 15
    });
</script>

{{-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize flatpickr for night start time
        const nightStartPicker = flatpickr("#night_start_time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 15,
            onChange: function(selectedDates, dateStr) {
                // Handle the existing validation logic
                let endTimeInput = document.getElementById("night_end_time");
                endTimeInput.disabled = false;
                
                if (dateStr) {
                    // Update end time flatpickr with new min time
                    nightEndPicker.set('minTime', dateStr);
                    endTimeInput.value = "";
                } else {
                    endTimeInput.disabled = true;
                    endTimeInput.value = "";
                }
            }
        });
        
        // Initialize flatpickr for night end time
        const nightEndPicker = flatpickr("#night_end_time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 15,
            onOpen: function() {
                // Ensure we have a valid min time
                let startTime = document.getElementById("night_start_time").value;
                if (startTime) {
                    this.set('minTime', startTime);
                }
            },
            onChange: function(selectedDates, dateStr, instance) {
                let startTime = document.getElementById("night_start_time").value;
                
                if (startTime && dateStr) {
                    // Compare times
                    if (dateStr <= startTime) {
                        showTimeErrorPopup();
                        setTimeout(() => {
                            this.clear();
                        }, 100);
                    }
                }
            }
        });
        
        // Keep the existing time validation popup functionality
        function showTimeErrorPopup() {
            let popup = document.getElementById("timeErrorPopup");
            popup.style.display = "block";
            setTimeout(function() {
                popup.style.display = "none";
            }, 3000);
        }
    });
</script> --}}

<!-- <script>
$(document).ready(function() {
    // Initialize Select2 for city
    $('#citySelect').select2({
        placeholder: "Search and Select a City",
        allowClear: true,
        tags: true,
        width: '100%'
    });

    // When country is changed
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
            $('#citySelect').append('<option value="">Select a country first</option>').trigger('change');
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
            // Hide the DMC select box
            $('#dmc-container').hide();
            $('#dmc').prop('required', false);
            
            // Auto-fill the country field with the DMC's country
            $('#country').val(userCountry);
            
            // Load cities for this DMC
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
                    // Clear city select and country
                    $('#citySelect').empty().append('<option value="">Select a DMC first</option>').trigger('change');
                    $('#country').val('');
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
        
        // Handle country change for role_id 1 and 20 to load cities
        if ([1, 20].includes(userRoleId)) {
            $('#country').change(function() {
                var countryName = $(this).val();
                
                // Clear city select
                $('#citySelect').empty().trigger('change');
                
                if (countryName) {
                    // Show loading state
                    $('#citySelect').append('<option value="">Loading cities...</option>').trigger('change');
                    
                    $.ajax({
                        url: "{{ route('get.cities.by.country') }}",
                        type: "GET",
                        data: { 
                            country: countryName
                        },
                        dataType: 'json',
                        success: function(response) {
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
                            
                            // Trigger change to refresh Select2
                            $('#citySelect').trigger('change');
                        },
                        error: function(xhr, status, error) {
                            $('#citySelect').empty();
                            $('#citySelect').append('<option value="">Error loading cities</option>');
                            $('#citySelect').trigger('change');
                        }
                    });
                } else {
                    $('#citySelect').append('<option value="">Select a country first</option>').trigger('change');
                }
            });
        }
    });
</script>

{{-- <script>
    document.getElementById("night_start_time").addEventListener("change", function () {
        let startTime = this.value; // Get selected start time
        let endTimeInput = document.getElementById("night_end_time");

        if (startTime) {
            let [hours, minutes] = startTime.split(":").map(Number);

            // Enable and set min value for Night End Time (must be at least 1 min after start)
            endTimeInput.disabled = false;
            let minEndHours = hours;
            let minEndMinutes = minutes + 1; // Ensure at least 1-minute gap

            if (minEndMinutes === 60) {
                minEndMinutes = 0;
                minEndHours += 1;
            }

            endTimeInput.min = `${String(minEndHours).padStart(2, '0')}:${String(minEndMinutes).padStart(2, '0')}`;
            endTimeInput.max = "23:59"; // Ensure max is 11:59 PM
            endTimeInput.value = ""; // Reset previous selection
        } else {
            endTimeInput.disabled = true;
            endTimeInput.value = "";
        }
    });

    document.getElementById("night_end_time").addEventListener("change", function () {
        let endTime = this.value;
        let startTime = document.getElementById("night_start_time").value;

        if (endTime && startTime) {
            let [startHours, startMinutes] = startTime.split(":").map(Number);
            let [endHours, endMinutes] = endTime.split(":").map(Number);

            // Validate if end time is before start time
            if (endHours < startHours || (endHours === startHours && endMinutes <= startMinutes)) {
                alert("Night End Time must be after the Night Start Time.");
                this.value = "";
            }
        }
    });
</script> --}}

{{-- <script>
    function showTimeErrorPopup() {
        let popup = document.getElementById("timeErrorPopup");
        popup.style.display = "block";

        // Hide popup after 3 seconds
        setTimeout(function () {
            popup.style.display = "none";
        }, 3000);
    }

    // Validate Night End Time
    document.getElementById("night_end_time").addEventListener("change", function () {
        let startTime = document.getElementById("night_start_time").value;
        let endTime = this.value;

        if (startTime && endTime && startTime >= endTime) {
            showTimeErrorPopup(); // Show popup
            this.value = ""; // Reset the wrong selection
        }
    });

    document.getElementById("night_start_time").addEventListener("change", function () {
        let startTime = this.value; // Get selected start time
        let endTimeInput = document.getElementById("night_end_time");

        if (startTime) {
            let [hours, minutes] = startTime.split(":").map(Number);

            // Enable and set min value for Night End Time (must be at least 1 min after start)
            endTimeInput.disabled = false;
            let minEndHours = hours;
            let minEndMinutes = minutes + 1; // Ensure at least 1-minute gap

            if (minEndMinutes === 60) {
                minEndMinutes = 0;
                minEndHours += 1;
            }

            endTimeInput.min = `${String(minEndHours).padStart(2, '0')}:${String(minEndMinutes).padStart(2, '0')}`;
            endTimeInput.max = "23:59"; // Ensure max is 11:59 PM
            endTimeInput.value = ""; // Reset previous selection
        } else {
            endTimeInput.disabled = true;
            endTimeInput.value = "";
        }
    });

    document.getElementById("night_end_time").addEventListener("change", function () {
        let endTime = this.value;
        let startTime = document.getElementById("night_start_time").value;
        let popup = document.getElementById("timeErrorPopup");

        if (endTime && startTime) {
            let [startHours, startMinutes] = startTime.split(":").map(Number);
            let [endHours, endMinutes] = endTime.split(":").map(Number);

            // Validate if end time is before start time
            if (endHours < startHours || (endHours === startHours && endMinutes <= startMinutes)) {
                this.value = ""; // Reset the invalid selection

                // Show popup notification
                popup.style.display = "block";
                setTimeout(() => {
                    popup.style.display = "none";
                }, 3000); // Hide after 3 seconds
            }
        }
    });
</script> --}}



<!-- JavaScript for Adding More Language Fields Dynamically -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const languageContainer = document.getElementById("language-container");
        const addMoreBtn = document.getElementById("addmore");
    
        addMoreBtn.addEventListener("click", function () {
            const newRow = document.createElement("div");
            newRow.classList.add("language-row", "d-flex", "mb-3");
    
            newRow.innerHTML = `
                <div class="col-md-5">
                    <select class="form-control language-select" name="languages[]" required>
                        <option value="">Select Language</option>
                        @foreach($languages as $c)
                            <option value="{{ $c->name }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
    
                <div class="col-md-5 ms-2">
                    <select class="form-select proficiency-select" name="language_proficiency[]" required>
                        <option value="">Select</option>
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Fluent">Fluent</option>
                        <option value="Expert">Expert</option>
                        <option value="Mother Tongue">Mother Tongue</option>
                    </select>
                </div>
    
                <div class="col-md-2 ms-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-language">Remove</button>
                </div>
            `;
    
            languageContainer.appendChild(newRow);
            updateRemoveButtons();
        });
    
        languageContainer.addEventListener("click", function (event) {
            if (event.target.classList.contains("remove-language")) {
                event.target.closest(".language-row").remove();
                updateRemoveButtons();
            }
        });
    
        function updateRemoveButtons() {
            const removeButtons = document.querySelectorAll(".remove-language");
            removeButtons.forEach((button, index) => {
                button.classList.toggle("d-none", index === 0);
            });
        }
    });
</script>

<!-- Language -->
{{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({
        placeholder: "Select Languages",
        allowClear: true
    });
});
</script> --}}

<!-- License Image drop down -->
<script>
    const licenseDropArea = document.getElementById('license-drop-area');
    const licenseFileInput = document.getElementById('license_image');
    const licensePreviewContainer = document.getElementById('license-preview-container');
    let licenseFileCounter = 0; // Track total uploaded files
    const LICENSE_MAX_VISIBLE_IMAGES = 1; // Show only 1 image

    // Open file picker on click
    licenseDropArea.addEventListener('click', () => licenseFileInput.click());

    // Handle drag events
    licenseDropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        licenseDropArea.style.backgroundColor = '#e3f2fd';
    });

    licenseDropArea.addEventListener('dragleave', () => {
        licenseDropArea.style.backgroundColor = 'white';
    });

    licenseDropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        licenseDropArea.style.backgroundColor = 'white';
        licenseHandleFiles(e.dataTransfer.files);
    });

    // Handle file input change
    licenseFileInput.addEventListener('change', () => {
        licenseHandleFiles(licenseFileInput.files);
    });

    // Process and display files
    function licenseHandleFiles(files) {
        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    // If an image already exists, remove it before adding the new one
                    if (licenseFileCounter > 0) {
                        licensePreviewContainer.innerHTML = ''; // Clear the existing preview
                        licenseFileCounter = 0; // Reset the file counter
                    }
                    licenseFileCounter++;
                    licenseImagePreview(e.target.result);
                };
                reader.readAsDataURL(file);
            } else {
                alert(`${file.name} is not a valid image file.`);
            }
        });
    }

    // Add image preview with limited visibility and a "more" badge
    function licenseImagePreview(imageSrc) {
        console.log("license image preview");

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
            licensePreviewContainer.removeChild(imageWrapper);
            licenseFileCounter--;
            updateMoreBadge();
        });

        imageWrapper.appendChild(img);
        imageWrapper.appendChild(deleteButton);
        licensePreviewContainer.appendChild(imageWrapper);

        updateMoreBadge();
    }

    // Create and update "+X more" badge
    function updateMoreBadge() {
        // Remove any existing badge
        const existingBadge = document.getElementById('more-badge');
        if (existingBadge) existingBadge.remove();

        if (licenseFileCounter > LICENSE_MAX_VISIBLE_IMAGES) {
            const morelicenseBadge = document.createElement('div');
            morelicenseBadge.id = 'more-license-badge';
            morelicenseBadge.textContent = `+${licenseFileCounter - LICENSE_MAX_VISIBLE_IMAGES} more`;
            morelicenseBadge.style.margin = '5px';
            morelicenseBadge.style.padding = '5px 10px';
            morelicenseBadge.style.backgroundColor = '#007bff';
            morelicenseBadge.style.color = 'white';
            morelicenseBadge.style.borderRadius = '5px';
            morelicenseBadge.style.cursor = 'pointer';
            morelicenseBadge.style.fontSize = '12px';
            morelicenseBadge.style.textAlign = 'center';
            morelicenseBadge.addEventListener('click', () => {
                // Show all hidden images
                const hiddenImages = licensePreviewContainer.querySelectorAll('div[style*="display: none"]');
                hiddenImages.forEach(img => img.style.display = 'inline-block');
                morelicenseBadge.remove(); // Remove badge after revealing all
            });
            licensePreviewContainer.appendChild(morelicenseBadge);
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
<script>
    document.getElementById('addmore').addEventListener('click', function() {
        // Get the container that holds the language and proficiency fields
        const container = document.getElementById('language-container');

        // Clone the language and proficiency fields
        const languageField = container.querySelector('.language-fields').cloneNode(true);
        const proficiencyField = container.querySelector('.proficiency-fields').cloneNode(true);

        // Clear the values for the new fields
        languageField.querySelector('input').value = '';
        proficiencyField.querySelector('select').value = '';

        // Create a remove button and append it to the language fields
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.classList.add('btn', 'btn-danger', 'remove-btn');
        removeButton.textContent = 'Remove';

        // Create a div to hold both language fields and the remove button
        const fieldsWrapper = document.createElement('div');
        fieldsWrapper.classList.add('d-flex', 'justify-content-between', 'align-items-center');

        // Append the language field and proficiency field to the wrapper
        fieldsWrapper.appendChild(languageField);
        fieldsWrapper.appendChild(proficiencyField);
        fieldsWrapper.appendChild(removeButton);

        // Append the wrapper to the container
        container.appendChild(fieldsWrapper);

        // Add event listener for remove button
        removeButton.addEventListener('click', function() {
            // Remove the entire wrapper when remove is clicked
            fieldsWrapper.remove();
        });
    });
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
        showValidationMessage(input, false, 'This field is required');
    } else if (!priceRegex.test(value)) {
        showValidationMessage(input, false, `
            Please enter a valid numeric value:
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

function validatePhoneNumber(input) {
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

function validateEmail(input) {
    const value = input.value.trim();
    // Standard email regex
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    
    if (value === '') {
        showValidationMessage(input, false, 'Email is required');
    } else if (!emailRegex.test(value)) {
        showValidationMessage(input, false, `
            Please enter a valid email address:
            <ul class="mt-1 mb-0">
                <li>Must contain @ symbol</li>
                <li>Must end with a valid domain (.com, .org, etc.)</li>
                <li>Example: example@domain.com</li>
            </ul>
        `);
    } else {
        showValidationMessage(input, true, '');
    }
}

const driverAgeRules = {
        Singapore: { min: 18, max: 70 },
        India: { min: 18, max: 75 },
        Vietnam: { min: 21, max: 65 },
        Malaysia: { min: 18, max: 70 },
        Thailand: { min: 20, max: 68 },
        // Add more countries as needed
    };


    function validateDriverAge(input) {
        const value = parseInt(input.value);
        const country = document.getElementById('country').value;
        const messageElement = document.getElementById(`${input.id}-validation-message`);

        if (isNaN(value)) {
            showValidationMessage(input, false, 'Please enter a valid age');
            return;
        }

        const rules = driverAgeRules[country];

        if (rules) {
            if (value < rules.min) {
                showValidationMessage(input, false, `In ${country}, guide must be at least ${rules.min} years old`);
            } else if (value > rules.max) {
                showValidationMessage(input, false, `In ${country}, guide age cannot exceed ${rules.max} years`);
            } else {
                showValidationMessage(input, true, '');
            }
        } else {
            // Fallback if country not in rules
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

@endsection