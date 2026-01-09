@extends('layouts.layout')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

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

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
                <h5 class="card-header d-flex justify-content-between align-items-center">
                    Approve Guide
                    <a href="{{ route('guide.approval') }}" class="btn btn-sm btn-outline-danger">
                        <i class="mdi mdi-arrow-left"></i> Back
                    </a>
                </h5>

            @if ($errors->any())
                <div class="alert alert-danger border-0 border-start border-5 border-danger-subtle shadow-sm px-4 py-3 rounded-3">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                        <div>
                            <h6 class="mb-2 fw-semibold text-danger">Please fix the following errors:</h6>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li class="small">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
            <form id="guideForm" method="POST" action="{{ route('guide.update.approval',Crypt::encrypt($guide->guide_id)) }}"
                enctype="multipart/form-data" class="card-body">
                @csrf
                @method('PUT')
                <!-- Hidden Fields -->

                <div id="guideDetailsContainer">
                    <div class="guide-form">
                        <div class="row">
                            <!-- Guide Salutation -->
                            <div class="col-md-2 mb-3">
                                <label for="salutation" class="form-label"><strong>Salutation</strong><span class="text-danger">*</span></label>
                                <select class="form-control" name="salutation" required>
                                    <option value="">Select Salutation</option>
                                    <option value="Mr" {{ old('salutation', $guide->salutation ?? '') == 'Mr' ? 'selected' : '' }}>Mr.</option>
                                    <option value="Mrs" {{ old('salutation', $guide->salutation ?? '') == 'Mrs' ? 'selected' : '' }}>Mrs.</option>
                                    <option value="Miss" {{ old('salutation', $guide->salutation ?? '') == 'Miss' ? 'selected' : '' }}>Ms.</option>
                                    <option value="Dear" {{ old('salutation', $guide->salutation ?? '') == 'Dear' ? 'selected' : '' }}>Dear</option>
                                </select>
                                @error('salutation')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 col-md-3">
                                <label for="guide_gender" class="form-label"><strong>Guide Gender</strong><span class="text-danger">*</span></label>
                                <select class="form-control" name="guide_gender" required>
                                    <option value="">Select Salutation</option>
                                    <option value="Male" {{ old('guide_gender', $guide->guide_gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('guide_gender', $guide->guide_gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                                    <option value="Other" {{ old('guide_gender', $guide->guide_gender ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('guide_gender')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Guide Name -->
                            <div class="col-md-3 mb-3">
                                <label for="name" class="form-label"><strong>Guide Name</strong><span
                                    class="text-danger">*</span></label>
                                <input value="{{$guide->name}}" type="text" class="form-control" name="name"
                                    placeholder="Enter Guide Name" required>
                                @error('name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Contact No -->
                            <div class="col-md-3 mb-3">
                                <label for="contact_no" class="form-label">
                                    <strong>Contact No</strong><span class="text-danger">*</span>
                                </label>
                                <input value="{{$guide->contact_no}}" name="contact_no" type="number"
                                    class="form-control" placeholder="Enter Contact No" required></input>
                                @error('contact_no')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- email -->
                            <div class="col-md-3 mb-4">
                                <label for="email" class="form-label"><strong>Email</strong><span
                                        class="text-danger">*</span></label>
                                <input value="{{$guide->email}}" type="email" class="form-control" name="email"
                                    placeholder="Enter Email..." required>
                                @error('email')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Country -->
                            <div class="mb-3 col-md-3">
                                <label for="country" class="form-label"><strong>Country</strong>
                                    <span style="color: red; font-weight: bold;">*</span>
                                </label>
                                <input class="form-control" type="text" value="{{$guide->country}}" readonly>
                                @error('country')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- City -->
                            <div class="col-md-3 mb-3">
                                <label for="city" class="form-label"><strong>City</strong><span class="text-danger">*</span></label>
                                <select name="city" id="citySelect" class="form-control" required>
                                    <option value="{{ $guide->city }}">{{ $guide->city }}</option>
                                    @foreach($city as $c)
                                        @if($c->name != $guide->city)
                                            <option value="{{ $c->name }}">{{ $c->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('city')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Service Type -->
                            <div class="col-md-3 mb-3">
                                <label for="service_type" class="form-label"><strong>Service Type</strong><span class="text-danger">*</span></label>
                                <select id="service_type" type="text" class="form-select" name="service_type"
                                    placeholder="Enter service type..." required>
                                    <option value="">Select</option>
                                    <option {{$guide->service_type == 1 ? 'selected' : ''}} value="1">Private</option>
                                    <option {{$guide->service_type == 2 ? 'selected' : ''}} value="2">Shared</option>
                                    <option {{$guide->service_type == 3 ? 'selected' : ''}} value="2">Both</option>
                                </select>
                                @error('service_type')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Driver Age -->
                            <div class="col-md-3 mb-3">
                                <label for="guide_age" class="form-label"><strong>Guide Age</strong><span class="text-danger">*</span></label>
                                <input value="{{$guide->guide_age}}" id="guide_age" type="number" class="form-control" name="guide_age" placeholder="Enter Driver Age">
                            </div>

                                <!-- Guide image -->
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

                                    @if($guide->image)
                                    <div class="image-preview-container d-flex flex-wrap gap-2">
                                        <div class="image-preview-wrapper position-relative">
                                            <img src="{{$guide->image}}" alt="Room Master Image"
                                                style="max-width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                                            <button
                                                class="delete-image-btn position-absolute top-0 end-0 btn btn-sm btn-danger"
                                                data-image="{{ $guide->image }}"
                                                style="width: 20px; height: 20px; line-height: 18px; padding: 0; text-align: center; font-size: 14px; z-index: 1;">
                                                &times;
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                <div id="guide_language" class="col-md-12 mb-3">
                                    <fieldset>
                                        <h5 class="card-title mb-3">Languages & Proficiency</h5>
                                    
                                        @if($languages)
                                            @foreach($languages as $language)
                                                <div class="row language-row mb-3">
                                                    <!-- Language -->
                                                    <div class="col-md-5">
                                                        <label for="languages" class="form-label"><strong>Languages</strong><span class="text-danger">*</span></label>
                                                        <select class="form-control" name="languages[]" required>
                                                            <option value="">Select Language</option>
                                                            @foreach($languagesname as $c)
                                                                <option value="{{ $c->name }}" @if(old('languagesname', $language->language ?? '') == $c->name) selected @endif>
                                                                    {{ $c->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                    
                                                    <!-- Language Proficiency -->
                                                    <div class="col-md-5">
                                                        <label for="language_proficiency" class="form-label"><strong>Languages Proficiency</strong><span class="text-danger">*</span></label>
                                                        <select class="form-select" name="language_proficiency[]" required>
                                                            <option value="">Select</option>
                                                            <option {{ $language->proficiency == "Beginner" ? 'selected' : '' }} value="Beginner">Beginner</option>
                                                            <option {{ $language->proficiency == "Intermediate" ? 'selected' : '' }} value="Intermediate">Intermediate</option>
                                                            <option {{ $language->proficiency == "Fluent" ? 'selected' : '' }} value="Fluent">Fluent</option>
                                                            <option {{ $language->proficiency == "Expert" ? 'selected' : '' }} value="Expert">Expert</option>
                                                            <option {{ $language->proficiency == "Mother Tongue" ? 'selected' : '' }} value="Mother Tongue">Mother Tongue</option>
                                                        </select>
                                                    </div>
                                    
                                                    <!-- Remove Button -->
                                                    <div class="col-md-2 mb-2 d-flex align-items-end">
                                                        <button type="button" class="btn btn-danger remove-language">Remove</button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    
                                        <div id="language-container">
                                            <div class="row language-row mb-3">
                                                <!-- Language -->
                                                @if(!$languages)
                                                <div class="col-md-5">
                                                    <label for="languages" class="form-label"><strong>Languages</strong><span class="text-danger">*</span></label>
                                                    <select class="form-control language-select" name="languages[]" required>
                                                        <option value="">Select Language</option>
                                                        @foreach($languagesname as $c)
                                                            <option value="{{ $c->name }}">{{ $c->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                    
                                                <!-- Language Proficiency -->
                                                <div class="col-md-5">
                                                    <label for="language_proficiency" class="form-label"><strong>Languages Proficiency</strong></label>
                                                    <select class="form-select" name="language_proficiency[]">
                                                        <option value="">Select</option>
                                                        <option value="Beginner">Beginner</option>
                                                        <option value="Intermediate">Intermediate</option>
                                                        <option value="Fluent">Fluent</option>
                                                        <option value="Expert">Expert</option>
                                                        <option value="Mother Tongue">Mother Tongue</option>
                                                    </select>
                                                </div>
                                                @endif
                                    
                                                <!-- Add More Button -->

                                                <div class="col-md-2 mb-2 d-flex align-items-end">
                                                    <button type="button" id="addmore" class="btn btn-primary">Add More</button>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>       
                            <!-- License -->
                            <fieldset id="License" class="border p-4 rounded mb-4">
                                <h5 class="card-title mb-3">License</h5>
                                <div class="row">

                                    <!-- Government License No -->
                                    <div class="col-md-3">
                                        <label for="license_no" class="form-label"><strong>Government License No</strong><span class="text-danger">*</span></label>
                                        <input value="{{$guide->government_license_no}}" type="text" step="0.1"
                                            class="form-control" name="license_no" placeholder="Enter Gov. License No"
                                            required>
                                        @error('license_no')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <!-- license_exp_date -->
                                    <div class="col-md-3">
                                        <label for="license_exp_date" class="form-label"><strong>License Expiry
                                                Date</strong><span class="text-danger">*</span></label>
                                        <input value="{{$guide->license_exp_date}}" type="date" step="0.01" class="form-control" name="license_exp_date" placeholder="Enter Cost"
                                            required>
                                        @error('license_exp_date')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Experience -->
                                    <div class="col-md-2">
                                        <label for="experience" class="form-label"><strong>Experience (In Years)</strong><span class="text-danger">*</span></label>
                                        <input value="{{$guide->experience_years}}" type="number" step="0.01"
                                            class="form-control" name="experience" placeholder="Enter Experience"
                                            required>
                                        @error('experience')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                        <!-- Guide license image -->
                                    <div class="mt-3 mb-3 col-md-4">
                                        <div>
                                            <label for="license_image" class="form-label"><strong>Upload Licence</strong></label>
                                            <div id="license-drop-area" class="form-control"
                                                style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px;">
                                                Drag & Drop your files here or click to upload.
                                                <input type="file" id="license_image" name="license_image" multiple
                                                    style="display: none;">
                                            </div>
                                        </div>
                                        <div id="license-preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                            style="max-width: 30%; overflow-x: auto; white-space: nowrap;"></div>

                                        @if($guide->license_image)
                                        <div class="license-image-preview-container d-flex flex-wrap gap-2">
                                            <div class="license-image-preview-wrapper position-relative">
                                                <img src="{{$guide->license_image}}" alt="License Image"
                                                    style="max-width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                                                <button
                                                    class="delete-license-image-btn position-absolute top-0 end-0 btn btn-sm btn-danger"
                                                    data-image="{{ $guide->license_image }}"
                                                    style="width: 20px; height: 20px; line-height: 18px; padding: 0; text-align: center; font-size: 14px; z-index: 1;">
                                                    &times;
                                                </button>
                                            </div>
                                        </div>
                                        @endif
                                    </div>


                                </div>
                            </fieldset>

                            <!-- Rate -->
                            <fieldset id="rate" class="border p-4 rounded mb-4">
                                <h5 class="card-title mb-3">Rates</h5>
                                <div class="row">
                                    <!-- Minimum Base Price -->
                                    <div class="col-md-3">
                                        <label for="day_rate" class="form-label"><strong>Minimum Base Price</strong><span
                                                class="text-danger">*</span></label>
                                        <input value="{{$guide->day_rate}}" type="number" step="0.1"
                                            class="form-control" name="day_rate" placeholder="Enter Day Rate" required>
                                        @error('day_rate')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <!-- night_surcharge -->
                                    <div class="col-md-3">
                                        <label for="night_surcharge" class="form-label"><strong>Night
                                                Surcharge</strong><span class="text-danger">*</span></label>
                                        <input value="{{$guide->night_surcharge}}" type="number" step="0.01"
                                            class="form-control" name="night_surcharge"
                                            placeholder="Enter Night Surcharge" required>
                                        @error('night_surcharge')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- <!-- Night Start Time -->
                                    <div class="col-md-2">
                                        <label for="night_start_time" class="form-label"><strong>Night Start
                                                Time</strong><span class="text-danger">*</span></label>
                                        <input value="{{$guide->night_start_time}}" id="night_start_time" type="time" class="form-control"
                                            name="night_start_time" placeholder="Enter night_start_time" required>
                                        @error('night_start_time')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Night End Time -->
                                    <div class="col-md-2">
                                        <label for="night_end_time" class="form-label"><strong>Night End
                                                Time</strong><span class="text-danger">*</span></label>
                                        <input value="{{$guide->night_end_time}}" id="night_end_time" type="time" class="form-control"
                                            name="night_end_time" placeholder="Enter night_end_time" required>
                                        @error('night_end_time')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div> --}}

                                    <!-- Night Start Time -->
                                    <div class="col-md-2">
                                        <label for="night_start_time" class="form-label"><strong>Night Start
                                                Time</strong><span class="text-danger">*</span></label>
                                        <input value="{{$guide->night_start_time}}" id="night_start_time" type="text" class="form-control"
                                            name="night_start_time" placeholder="Select start time">
                                        @error('night_start_time')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Night End Time -->
                                    <div class="col-md-2">
                                        <label for="night_end_time" class="form-label"><strong>Night End
                                                Time</strong><span class="text-danger">*</span></label>
                                        <input value="{{$guide->night_end_time}}" id="night_end_time" type="text" class="form-control"
                                            name="night_end_time" placeholder="Select end time">
                                        @error('night_end_time')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                     <!-- Night Time Error Popup -->
                                     {{-- <div id="timeErrorPopup" class="time-error-popup">
                                        <p>
                                            <i class="fas fa-exclamation-triangle"></i> <!-- Font Awesome Icon -->
                                            Night End Time must be after the Night Start Time.
                                        </p>
                                    </div> --}}

                                    <!-- Hourly Price -->
                                    <div class="col-md-2 mb-3">
                                        <label for="hourly_price" class="form-label"><strong>Hourly Price</strong><span
                                                class="text-danger">*</span></label>
                                        <input value="{{$guide->hourly_price}}" type="number" step="0.01"
                                            class="form-control" name="hourly_price" placeholder="Enter hourly_price"
                                            required>
                                        @error('hourly_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Two Hour Price -->
                                    <div class="col-md-2 mb-3">
                                        <label for="two_hour_price" class="form-label"><strong>Two Hour
                                                Price</strong><span class="text-danger">*</span></label>
                                        <input value="{{$guide->two_hour_price}}" type="number" step="0.01"
                                            class="form-control" name="two_hour_price"
                                            placeholder="Enter two_hourly_price" required>
                                        @error('two_hour_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Four Hourly Price -->
                                    <div class="col-md-2 mb-3">
                                        <label for="four_hour_price" class="form-label"><strong>Four Hour
                                                Price</strong><span class="text-danger">*</span></label>
                                        <input value="{{$guide->four_hour_price}}" type="number" step="0.01"
                                            class="form-control" name="four_hour_price"
                                            placeholder="Enter four_hour_price" required>
                                        @error('four_hour_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Six Hour Price -->
                                    <div class="col-md-2 mb-3">
                                        <label for="six_hour_price" class="form-label"><strong>Six Hour
                                                Price</strong><span class="text-danger">*</span></label>
                                        <input value="{{$guide->six_hour_price}}" type="number" step="0.01"
                                            class="form-control" name="six_hour_price"
                                            placeholder="Enter six_hour_price" required>
                                        @error('six_hour_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Eight Hour Price -->
                                    <div class="col-md-2 mb-3">
                                        <label for="eight_hour_price" class="form-label"><strong>Eight Hour
                                                Price</strong><span class="text-danger">*</span></label>
                                        <input value="{{$guide->eight_hour_price}}" type="number" step="0.01"
                                            class="form-control" name="eight_hour_price"
                                            placeholder="Enter eight_hour_price" required>
                                        @error('eight_hour_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Ten Hour Price -->
                                    <div class="col-md-2 mb-3">
                                        <label for="ten_hour_price" class="form-label"><strong>Ten Hour
                                                Price</strong><span class="text-danger">*</span></label>
                                        <input value="{{$guide->ten_hour_price}}" type="number" step="0.01"
                                            class="form-control" name="ten_hour_price"
                                            placeholder="Enter ten_hour_price" required>
                                        @error('ten_hour_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Twelve Hourly Price -->
                                    <div class="col-md-2 mb-3">
                                        <label for="twelve_hour_price" class="form-label"><strong>Twelve Hour
                                                Price</strong><span class="text-danger">*</span></label>
                                        <input value="{{$guide->twelve_hour_price}}" type="number" step="0.01"
                                            class="form-control" name="twelve_hour_price"
                                            placeholder="Enter twelve_hour_price" required>
                                        @error('twelve_hour_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </fieldset>

                            <!-- about -->
                            <div class="col-md-12 mb-3">
                                <label for="about" class="form-label"><strong>About</strong><span class="text-danger">*</span></label>
                                <textarea
                                id="summernote" name="about" class="form-control" rows="10" placeholder="Write About Guide..." required>{{old('about', htmlspecialchars_decode($guide->description))}}</textarea>
                                @error('about')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-check form-switch">
                            <label for="guide_status" class="form-label"><strong>Status</strong><span style="color: red; font-weight: bold;">*</span></label>
                            <input type="hidden" name="guide_status" value="0">
                            <input {{$guide->is_active == 1 ? 'checked' : ''}} class="form-check-input" name="guide_status" type="checkbox" id="guide_status" value="1" required>
                            <label class="form-check-label"></label>
                        </div>
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

@endsection

@section('scripts')

<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#summernote').summernote({
            height: 200,      
            minHeight: 200,   
            maxHeight: 500,   
            placeholder: 'Enter your content here...', 
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
<!-- Language -->
{{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script> --}}
{{-- <script>
$(document).ready(function() {
    $('.select2').select2({
        placeholder: "Select Languages",
        allowClear: true
    });
});
</script> --}}

<!--Guide license image-->

<!-- delete existing License  Image -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Use event delegation for dynamically added elements
        document.querySelector('.license-image-preview-container').addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-license-image-btn')) {
                e.preventDefault(); // Prevent form submission
                e.stopPropagation(); // Stop event propagation
                const button = e.target;

                // Find the image preview wrapper
                const imageWrapper = button.closest('.license-image-preview-wrapper');
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

<!-- Guide Image drop down -->
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
            const moreLicenseBadge = document.createElement('div');
            moreLicenseBadge.id = 'more-license-badge';
            moreLicenseBadge.textContent = `+${licenseFileCounter - LICENSE_MAX_VISIBLE_IMAGES} more`;
            moreLicenseBadge.style.margin = '5px';
            moreLicenseBadge.style.padding = '5px 10px';
            moreLicenseBadge.style.backgroundColor = '#007bff';
            moreLicenseBadge.style.color = 'white';
            moreLicenseBadge.style.borderRadius = '5px';
            moreLicenseBadge.style.cursor = 'pointer';
            moreLicenseBadge.style.fontSize = '12px';
            moreLicenseBadge.style.textAlign = 'center';
            moreLicenseBadge.addEventListener('click', () => {
                // Show all hidden images
                const hiddenImages = licensePreviewContainer.querySelectorAll('div[style*="display: none"]');
                hiddenImages.forEach(img => img.style.display = 'inline-block');
                morelicenseBadge.remove(); // Remove badge after revealing all
            });
            licensePreviewContainer.appendChild(morelicenseBadge);
        }
    }
</script>

<!--End license image-->

<!-- delete existing Guide Image -->
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

<!-- Guide Image drop down -->
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


{{-- <script>
    document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('language-container');

    // Function to add remove functionality to an element
    function addRemoveFunctionality(button) {
        button.addEventListener('click', function () {
            this.closest('.row').remove();
        });
    }

    // Enable remove functionality for existing languages
    document.querySelectorAll('.remove-btn').forEach(addRemoveFunctionality);

    // Add more functionality
    document.getElementById('addmore').addEventListener('click', function () {
        const newRow = document.createElement('div');
        newRow.classList.add('row', 'mb-3');

        newRow.innerHTML = `
            <div class="col-md-5 mb-3 language-fields">
                <label class="form-label"><strong>Languages</strong><span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="languages[]" placeholder="Enter language" required>
            </div>
            <div class="col-md-5 mb-3 proficiency-fields">
                <label class="form-label"><strong>Languages Proficiency</strong><span class="text-danger">*</span></label>
                <select class="form-select" name="language_proficiency[]" required>
                    <option value="">Select</option>
                    <option value="Beginner">Beginner</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Fluent">Fluent</option>
                    <option value="Expert">Expert</option>
                    <option value="Mother Tongue">Mother Tongue</option>
                </select>
            </div>
            <div class="col-md-2 mb-3" style="margin-top : 25px">
                <button type="button" class="btn btn-danger remove-btn">Remove</button>
            </div>
        `;

        container.appendChild(newRow);

        // Attach remove functionality to the new remove button
        addRemoveFunctionality(newRow.querySelector('.remove-btn'));
    });

    // Select all remove buttons for existing languages
    document.querySelectorAll('.remove-btn').forEach(button => {
        button.addEventListener('click', function () {
            let row = this.closest('.row'); // Find the parent row
            row.remove(); // Remove the row from the frontend
        });
    });
  });

</script> --}}

<script>
    document.addEventListener("DOMContentLoaded", function () {
    const languageContainer = document.getElementById("language-container");
    const addMoreBtn = document.getElementById("addmore");

    // Function to create a new language row
    function createLanguageRow() {
        const newRow = document.createElement("div");
        newRow.classList.add("row", "language-row", "mb-3");

        newRow.innerHTML = `
            <div class="col-md-5">
                <label for="languages" class="form-label"><strong>Languages</strong><span class="text-danger">*</span></label>
                <select class="form-control language-select" name="languages[]" required>
                    <option value="">Select Language</option>
                    @foreach($languagesname as $c)
                        <option value="{{ $c->name }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-5">
                <label for="language_proficiency" class="form-label"><strong>Languages Proficiency</strong></label>
                <select class="form-select proficiency-select" name="language_proficiency[]" required>
                    <option value="">Select</option>
                    <option value="Beginner">Beginner</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Fluent">Fluent</option>
                    <option value="Expert">Expert</option>
                    <option value="Mother Tongue">Mother Tongue</option>
                </select>
            </div>

            <div class="col-md-1 mb-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger remove-language">Remove</button>
            </div>
        `;

        return newRow;
    }

    // Function to add a new row and move the Add More button
    function addNewLanguageRow() {
        const newRow = createLanguageRow();
        
        // Insert new row before the add more button
        languageContainer.insertBefore(newRow, addMoreBtn.closest(".row"));
    }

    // Function to remove language row
    function removeLanguageRow(event) {
        if (event.target.classList.contains("remove-language")) {
            event.target.closest(".language-row").remove();
        }
    }

    // Initial event listeners
    addMoreBtn.addEventListener("click", addNewLanguageRow);
    document.addEventListener("click", removeLanguageRow);
  });

</script>

@endsection