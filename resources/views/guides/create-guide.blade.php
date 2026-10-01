
@extends('layouts.layout')
@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    /* Compact form layout (aligned with add-vehicle) */
    .guide-form-compact .form-label { margin-bottom: 0.2rem; font-size: 0.8125rem; }
    .guide-form-compact .section-title {
        font-size: 0.9375rem;
        font-weight: 600;
        color: #405189;
        margin-bottom: 0.5rem;
        padding-bottom: 0.25rem;
        border-bottom: 1px solid #e9ecef;
    }
    .guide-price-table { font-size: 0.8125rem; margin-bottom: 0; }
    .guide-price-table th,
    .guide-price-table td { padding: 0.35rem 0.5rem; vertical-align: middle; }
    .guide-price-table thead th { font-size: 0.75rem; font-weight: 600; white-space: nowrap; }
    .guide-price-table .form-control { max-width: 100%; }
    .guide-price-table .charge-badge { font-size: 0.7rem; padding: 0.2em 0.45em; }
    .guide-price-table .visitor-group {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #6c757d;
    }
    .guide-form-compact .form-control-sm,
    .guide-form-compact .form-select-sm { font-size: 0.8125rem; }
    .guide-form-compact textarea.form-control { min-height: auto; }

    .flatpickr-time {
        height: 31px;
        line-height: 31px;
    }

    /* Reduce width of the time dropdown */
    .flatpickr-calendar.open {
        width: auto !important;
        min-width: 120px;
    }

    /* Make time inputs (hour & minute) smaller */
    .flatpickr-time input {
        width: 40px;
        height: 28px;
        padding: 0;
        text-align: center;
        font-size: 13px;
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
        background-color: rgba(255, 0, 0, 0.85);
        color: white;
        padding: 10px 20px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        text-align: center;
        display: none;
        z-index: 9999;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        animation: fadeIn 0.3s ease-in-out, fadeOut 0.5s ease-in-out 2.5s;
        width: 250px;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translate(-50%, -55%); }
        to { opacity: 1; transform: translate(-50%, -50%); }
    }

    @keyframes fadeOut {
        from { opacity: 1; transform: translate(-50%, -50%); }
        to { opacity: 0; transform: translate(-50%, -55%); }
    }

    .time-error-popup i {
        margin-right: 8px;
        color: yellow;
        font-size: 14px;
    }

    /* Select2 Custom Styling for Bootstrap 5 Integration */
    .guide-form-compact .select2-container--default .select2-selection--single {
        height: 31px !important;
        border: 1px solid #d9dee3 !important;
        border-radius: 0.375rem !important;
        padding: 0.2rem 0.5rem !important;
        display: flex !important;
        align-items: center !important;
    }

    .guide-form-compact .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 24px !important;
        padding: 0 !important;
        color: #697a8d !important;
        font-size: 0.8125rem !important;
    }

    .guide-form-compact .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 28px !important;
        right: 5px !important;
    }

    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single:hover {
        border-color: #697a8d !important;
    }

    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #696cff !important;
        box-shadow: 0 0 0.25rem rgba(105, 108, 255, 0.1) !important;
        outline: none !important;
    }

    .select2-dropdown {
        border: 1px solid #d9dee3 !important;
        border-radius: 0.375rem !important;
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1) !important;
    }

    .select2-container--default .select2-results__option {
        padding: 8px 12px !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #696cff !important;
        color: white !important;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #d9dee3 !important;
        border-radius: 0.375rem !important;
        padding: 0.375rem 0.75rem !important;
        outline: none !important;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: #696cff !important;
        box-shadow: 0 0 0.25rem rgba(105, 108, 255, 0.1) !important;
    }

    .guide-form-compact #language-container .proficiency-select,
    .guide-form-compact #language-container .language-select {
        height: 31px;
        font-size: 0.8125rem;
    }

    .guide-form-compact #language-container .proficiency-select {
        padding-top: 0.2rem;
        padding-bottom: 0.2rem;
        line-height: 1.4;
    }

    .guide-form-compact #language-container .remove-language {
        height: 31px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        font-size: 0.8125rem;
        padding: 0.2rem 0.5rem;
    }

    .form-check-input[type="checkbox"] {
        background-color: rgb(246, 249, 253);
        border-color: rgb(192, 199, 207);
        transition: all 0.3s ease;
    }
    .form-check-input:checked[type="checkbox"] {
        background-color: #28a745;
        border-color: #28a745;
    }
    .form-check-input:focus {
        box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
    }

    /* Auto-calculated field styles */
    .auto-calculated,
    .auto-calculated-sell,
    .auto-calculated-cost {
        background-color: #f8f9fa !important;
        border-left: 3px solid #17a2b8 !important;
        position: relative;
    }

    .auto-calculated:focus,
    .auto-calculated-sell:focus,
    .auto-calculated-cost:focus {
        background-color: #fff !important;
        border-left-color: #007bff !important;
        box-shadow: 0 0 0.2rem rgba(23, 162, 184, 0.25) !important;
    }

    .auto-calculated.value-updated,
    .auto-calculated-sell.value-updated,
    .auto-calculated-cost.value-updated {
        animation: highlightUpdate 0.8s ease-in-out;
    }

    @keyframes highlightUpdate {
        0% { background-color: #d4edda; border-left-color: #28a745; }
        100% { background-color: #f8f9fa; border-left-color: #17a2b8; }
    }
</style>
<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <h4 class="card-title mb-0">Add New Guide</h4>
                    <x-currency-price-note
                        :country="old('country')"
                        :watch-country="true"
                        country-select-id="country"
                    />
                </div>
                <a href="{{ route('guide.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </div>
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

            @if ($errors->any())
                <div id="guide-validation-summary" class="alert alert-danger border-0 border-start border-5 border-danger-subtle shadow-sm px-4 py-3 rounded-3">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                        <div>
                            <h6 class="mb-2 fw-semibold text-danger">Please fix the following errors:</h6>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li class="small" data-server-error-text="{{ $error }}">{{ $error }}</li>
                                @endforeach
                            </ul>
                            @if ($errors->any())
                                <p class="small mb-0 mt-2 text-danger fw-semibold">
                                    Profile Image and License Image were cleared by the browser after the failed save.
                                    Please upload both images again before clicking Save.
                                </p>
                            @endif
                            @if ($errors->has('master_image') || $errors->has('license_image') || $errors->has('email') || $errors->has('city') || $errors->has('country'))
                                <p class="small mb-0 mt-2 text-muted">
                                    Also confirm City matches the selected Country.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
            <form id="guideForm" method="POST" action="{{ route('guide.store') }}" enctype="multipart/form-data"
                class="card-body guide-form-compact js-submit-loader-form" data-loader-message="Saving...">
                @csrf
                <div id="guideDetailsContainer">
                    <div class="guide-form">
                        <div class="row g-2">
                            {{-- Guide basics --}}
                            <div class="col-12 mt-1 mb-1">
                                <div class="section-title"><i class="ri-user-line me-1"></i> Guide Info</div>
                            </div>

                            @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 23 || auth()->user()->role_id == 25 || auth()->user()->role_id == 45 || auth()->user()->role_id == 61 || auth()->user()->role_id == 100 || auth()->user()->role_id == 101)
                            <div class="col-md-3 mb-2" id="dmc-container" style="display: none;">
                                <label for="dmc" class="form-label"><strong>Select DMC</strong><span class="text-danger">*</span></label>
                                <select id="dmc" name="dmc" class="form-control form-control-sm" required>
                                    <option value="">Select DMC</option>
                                    @foreach ($dmcs as $dmc)
                                        <option value="{{ $dmc->userId }}">{{ $dmc->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <div class="col-md-2 mb-2">
                                <label for="salutation" class="form-label"><strong>Salutation</strong><span class="text-danger">*</span></label>
                                <select class="form-control form-control-sm" name="salutation" required>
                                    <option value="">Select</option>
                                    <option value="Mr" {{ old('salutation') == 'Mr' ? 'selected' : '' }}>Mr.</option>
                                    <option value="Mrs" {{ old('salutation') == 'Mrs' ? 'selected' : '' }}>Mrs.</option>
                                    <option value="Miss" {{ old('salutation') == 'Miss' ? 'selected' : '' }}>Ms.</option>
                                    <option value="Dear" {{ old('salutation') == 'Dear' ? 'selected' : '' }}>Dear</option>
                                </select>
                                @error('salutation')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="guide_gender" class="form-label"><strong>Gender</strong><span class="text-danger">*</span></label>
                                <select id="guide_gender" name="guide_gender" class="form-select form-select-sm">
                                    <option value="">Select</option>
                                    <option value="Male" {{ old('guide_gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('guide_gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                    <option value="Other" {{ old('guide_gender') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('guide_gender')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3 mb-2">
                                <label for="name" class="form-label"><strong>Guide Name</strong><span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="name" placeholder="Enter Guide Name"
                                    value="{{ old('name') }}" required>
                                @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3 mb-2">
                                <label for="contact_no" class="form-label"><strong>Contact No</strong><span class="text-danger">*</span></label>
                                <input name="contact_no" type="text" id="contact_no" class="form-control form-control-sm"
                                    placeholder="Enter Contact No" value="{{ old('contact_no') }}" required
                                    oninput="validatePhoneNumber(this)">
                                <small class="validation-message text-danger" id="contact_no-validation-message"></small>
                                @error('contact_no')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3 mb-2">
                                <label for="email" class="form-label"><strong>Email</strong><span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="email" name="email"
                                    placeholder="Enter Email..." value="{{ old('email') }}" required
                                    oninput="validateEmail(this)">
                                <small class="validation-message text-danger" id="email-validation-message"></small>
                                @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3 mb-2">
                                <label for="app_password" class="form-label"><strong>App Password</strong></label>
                                <div class="input-group input-group-sm">
                                    <input type="password" class="form-control form-control-sm" id="app_password" name="app_password"
                                        placeholder="Enter app password" autocomplete="new-password">
                                    <button class="btn btn-outline-secondary" type="button" id="toggleAppPassword">
                                        <i class="ri-eye-off-line" id="appPasswordIcon"></i>
                                    </button>
                                </div>
                                @error('app_password')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3 mb-2">
                                <label for="country" class="form-label"><strong>Country</strong><span class="text-danger">*</span></label>
                                @php
                                    $scopedCountries = collect($dmcBaseCountries ?? $masterDmcCountries ?? $country ?? []);
                                    $preselectedCountry = old('country', $userCountry ?? '');
                                    if ($preselectedCountry === '' && $scopedCountries->count() === 1) {
                                        $preselectedCountry = $scopedCountries->first()->name ?? '';
                                    }
                                @endphp
                                <select class="form-control form-control-sm" id="country" name="country" required onchange="validateDriverAge(document.getElementById('guide_age'))">
                                    @if($scopedCountries->count() !== 1)
                                        <option value="">Select Country</option>
                                    @endif
                                    @foreach($scopedCountries as $countryOption)
                                        <option value="{{ $countryOption->name }}" {{ $preselectedCountry == $countryOption->name ? 'selected' : '' }}>
                                            {{ $countryOption->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('country')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3 mb-2">
                                <label for="city" class="form-label"><strong>City</strong><span class="text-danger">*</span></label>
                                @php
                                    $hasPreloadedCities = isset($cities) && count($cities) > 0 && $preselectedCountry !== '';
                                    $placeholder = $hasPreloadedCities ? 'Select City' : 'Select Country First';
                                @endphp
                                <select name="city" id="citySelect" class="form-control form-control-sm" required {{ !$hasPreloadedCities ? 'disabled' : '' }}>
                                    <option value="">{{ $placeholder }}</option>
                                    @if($hasPreloadedCities)
                                        @foreach($cities as $city)
                                            <option value="{{ $city->name }}" {{ old('city') == $city->name ? 'selected' : '' }}>{{ $city->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('city')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="service_type" class="form-label"><strong>Service Type</strong><span class="text-danger">*</span></label>
                                <select id="service_type" class="form-select form-select-sm" name="service_type" required>
                                    <option value="">Select</option>
                                    <option value="1" {{ old('service_type') == '1' ? 'selected' : '' }}>Private</option>
                                    <option value="2" {{ old('service_type') == '2' ? 'selected' : '' }}>Shared</option>
                                    <option value="3" {{ old('service_type') == '3' ? 'selected' : '' }}>Both</option>
                                </select>
                                @error('service_type')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="guide_age" class="form-label"><strong>Age</strong><span class="text-danger">*</span></label>
                                <input id="guide_age" type="number" class="form-control form-control-sm" name="guide_age"
                                    value="{{ old('guide_age') }}" placeholder="Age" oninput="validateDriverAge(this)">
                                <small class="validation-message text-danger" id="guide_age-validation-message"></small>
                                @error('guide_age')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3 mb-2">
                                <label for="wp_number" class="form-label"><strong>Whatsapp Number</strong><span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="wp_number" placeholder="Enter Whatsapp Number" value="{{ old('wp_number') }}" required>
                                @error('wp_number')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            {{-- Languages --}}
                            <div class="col-12 mt-2 mb-1" id="guide_language">
                                <div class="section-title"><i class="ri-translate-2 me-1"></i> Languages & Proficiency</div>
                            </div>
                            <div class="col-12 mb-2">
                                <div id="language-container">
                                    @if(old('languages'))
                                        @foreach(old('languages') as $index => $language)
                                            <div class="language-row row g-2 mb-2 align-items-end">
                                                <div class="col-md-5">
                                                    <label class="form-label"><strong>Languages</strong><span class="text-danger">*</span></label>
                                                    <select class="form-control form-control-sm language-select" name="languages[]" required>
                                                        <option value="">Select Language</option>
                                                        @foreach($languages as $lang)
                                                            <option value="{{ $lang->name }}" {{ $language == $lang->name ? 'selected' : '' }}>{{ $lang->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('languages.'.$index)<div class="text-danger small">{{ $message }}</div>@enderror
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label"><strong>Proficiency</strong><span class="text-danger">*</span></label>
                                                    <select class="form-select form-select-sm proficiency-select" name="language_proficiency[]" required>
                                                        <option value="">Select</option>
                                                        <option value="Beginner" {{ old('language_proficiency')[$index] == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                                                        <option value="Intermediate" {{ old('language_proficiency')[$index] == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                                                        <option value="Fluent" {{ old('language_proficiency')[$index] == 'Fluent' ? 'selected' : '' }}>Fluent</option>
                                                        <option value="Expert" {{ old('language_proficiency')[$index] == 'Expert' ? 'selected' : '' }}>Expert</option>
                                                        <option value="Mother Tongue" {{ old('language_proficiency')[$index] == 'Mother Tongue' ? 'selected' : '' }}>Mother Tongue</option>
                                                    </select>
                                                    @error('language_proficiency.'.$index)<div class="text-danger small">{{ $message }}</div>@enderror
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-sm btn-danger remove-language {{ $index == 0 ? 'd-none' : '' }}">Remove</button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="language-row row g-2 mb-2 align-items-end">
                                            <div class="col-md-5">
                                                <label class="form-label"><strong>Languages</strong><span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm language-select" name="languages[]" required>
                                                    <option value="">Select Language</option>
                                                    @foreach($languages as $c)
                                                        <option value="{{ $c->name }}">{{ $c->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('languages.0')<div class="text-danger small">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label"><strong>Proficiency</strong><span class="text-danger">*</span></label>
                                                <select class="form-select form-select-sm proficiency-select" name="language_proficiency[]" required>
                                                    <option value="">Select</option>
                                                    <option value="Beginner">Beginner</option>
                                                    <option value="Intermediate">Intermediate</option>
                                                    <option value="Fluent">Fluent</option>
                                                    <option value="Expert">Expert</option>
                                                    <option value="Mother Tongue">Mother Tongue</option>
                                                </select>
                                                @error('language_proficiency.0')<div class="text-danger small">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-sm btn-danger remove-language d-none">Remove</button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" id="addmore" class="btn btn-sm btn-primary mt-1">Add More</button>
                            </div>

                            {{-- License --}}
                            <div class="col-12 mt-2 mb-1" id="License">
                                <div class="section-title"><i class="ri-id-card-line me-1"></i> License</div>
                            </div>

                            <div class="col-md-3 mb-2">
                                <label for="license_no" class="form-label"><strong>Gov. License No</strong><span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="license_no"
                                    placeholder="Enter Gov. License No" value="{{ old('license_no') }}" required>
                                @error('license_no')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="license_exp_date" class="form-label"><strong>License Expiry</strong><span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm" name="license_exp_date"
                                    value="{{ old('license_exp_date') }}" required>
                                @error('license_exp_date')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2 mb-2">
                                <label for="experience" class="form-label"><strong>Experience (Yrs)</strong><span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="experience" name="experience"
                                    placeholder="Years" value="{{ old('experience') }}" required
                                    oninput="validateNumericPrice(this)">
                                <small class="validation-message text-danger" id="experience-validation-message"></small>
                                @error('experience')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="license_image" class="form-label"><strong>License Image</strong><span class="text-danger">*</span></label>
                                <div id="license-drop-area" class="form-control form-control-sm"
                                    style="padding: 12px; border: 2px dashed #007bff; text-align: center; cursor: pointer;">
                                    Drag & Drop or click to upload
                                    <input type="file" id="license_image" name="license_image" style="display: none;" accept="image/*">
                                </div>
                                <div id="license_image_error" class="text-danger small mt-1 d-none"></div>
                                <div id="license-preview-container" class="mt-1 d-flex flex-wrap gap-2"
                                    style="max-width: 100%; overflow-x: auto; white-space: nowrap;"></div>
                            </div>

                            {{-- Profit helpers --}}
                            <div class="col-12 mt-2 mb-1" id="rate">
                                <div class="section-title"><i class="ri-percent-line me-1"></i> Profit <small class="text-muted fw-normal">(helper only — auto-fills Sell from Cost · not saved)</small></div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="guide_profit_margin" class="form-label"><strong>Profit Type</strong></label>
                                <select id="guide_profit_margin" class="form-select form-select-sm js-guide-profit-type">
                                    <option value="percentage" selected>%</option>
                                    <option value="flat">Flat</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="guide_profit_amount" class="form-label"><strong>Profit Amount</strong></label>
                                <input type="number" id="guide_profit_amount" class="form-control form-control-sm js-guide-profit-amount"
                                       value="0" min="0" step="0.01" placeholder="0.00">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label for="night_surcharge" class="form-label"><strong>Night Surcharge</strong><span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="night_surcharge" name="night_surcharge"
                                    placeholder="0.00" value="{{ old('night_surcharge') }}" required
                                    oninput="validateNumericPrice(this)">
                                <small class="validation-message text-danger" id="night_surcharge-validation-message"></small>
                                @error('night_surcharge')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2 mb-2">
                                <label for="night_start_time" class="form-label"><strong>Night Start</strong><span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="night_start_time" name="night_start_time" placeholder="Start time" value="{{ old('night_start_time') }}">
                                @error('night_start_time')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2 mb-2">
                                <label for="night_end_time" class="form-label"><strong>Night End</strong><span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="night_end_time" name="night_end_time" placeholder="End time" value="{{ old('night_end_time') }}">
                                @error('night_end_time')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            {{-- Pricing grid --}}
                            <div class="col-12 mt-2 mb-2">
                                <div class="section-title">
                                    <i class="ri-money-dollar-circle-line me-1"></i> Pricing
                                    <small class="text-muted fw-normal">(Cost = supplier fee · Sell = customer pays · multi-hour auto-calcs from Minimum)</small>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm guide-price-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:18%">Charge</th>
                                                <th class="text-center visitor-group" style="width:41%">Cost <span class="text-danger">*</span></th>
                                                <th class="text-center visitor-group" style="width:41%">Sell <span class="text-danger">*</span></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><span class="badge bg-primary-subtle text-primary charge-badge">Minimum</span></td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm js-guide-cost" id="minimum_cost_price" name="minimum_cost_price"
                                                        data-sell-target="day_rate" placeholder="0.00" value="{{ old('minimum_cost_price') }}" required
                                                        oninput="validateNumericPrice(this); calculateHourlyCostRates(); applyGuideProfitToSells(true);">
                                                    <small class="validation-message text-danger" id="minimum_cost_price-validation-message"></small>
                                                    @error('minimum_cost_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm js-guide-sell" id="day_rate" name="day_rate"
                                                        placeholder="0.00" value="{{ old('day_rate') }}" required
                                                        oninput="validateNumericPrice(this); calculateHourlyRates();">
                                                    <small class="validation-message text-danger" id="day_rate-validation-message"></small>
                                                    @error('day_rate')<div class="text-danger small">{{ $message }}</div>@enderror
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-info-subtle text-info charge-badge">1 Hr</span></td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm auto-calculated-cost js-guide-cost" id="hourly_cost_price" name="hourly_cost_price"
                                                        data-sell-target="hourly_price" placeholder="Auto" value="{{ old('hourly_cost_price') }}" required
                                                        oninput="validateNumericPrice(this); applyGuideProfitToSells(true);">
                                                    <small class="validation-message text-danger" id="hourly_cost_price-validation-message"></small>
                                                    @error('hourly_cost_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm auto-calculated-sell js-guide-sell" id="hourly_price" name="hourly_price"
                                                        placeholder="Auto" value="{{ old('hourly_price') }}" required
                                                        oninput="validateNumericPrice(this)">
                                                    <small class="validation-message text-danger" id="hourly_price-validation-message"></small>
                                                    @error('hourly_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-info-subtle text-info charge-badge">2 Hr</span></td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm auto-calculated-cost js-guide-cost" id="two_hour_cost_price" name="two_hour_cost_price"
                                                        data-sell-target="two_hour_price" placeholder="Auto" value="{{ old('two_hour_cost_price') }}" required
                                                        oninput="validateNumericPrice(this); applyGuideProfitToSells(true);">
                                                    <small class="validation-message text-danger" id="two_hour_cost_price-validation-message"></small>
                                                    @error('two_hour_cost_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm auto-calculated-sell js-guide-sell" id="two_hour_price" name="two_hour_price"
                                                        placeholder="Auto" value="{{ old('two_hour_price') }}" required
                                                        oninput="validateNumericPrice(this)">
                                                    <small class="validation-message text-danger" id="two_hour_price-validation-message"></small>
                                                    @error('two_hour_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-info-subtle text-info charge-badge">4 Hr</span></td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm auto-calculated-cost js-guide-cost" id="four_hour_cost_price" name="four_hour_cost_price"
                                                        data-sell-target="four_hour_price" placeholder="Auto" value="{{ old('four_hour_cost_price') }}" required
                                                        oninput="validateNumericPrice(this); applyGuideProfitToSells(true);">
                                                    <small class="validation-message text-danger" id="four_hour_cost_price-validation-message"></small>
                                                    @error('four_hour_cost_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm auto-calculated-sell js-guide-sell" id="four_hour_price" name="four_hour_price"
                                                        placeholder="Auto" value="{{ old('four_hour_price') }}" required
                                                        oninput="validateNumericPrice(this)">
                                                    <small class="validation-message text-danger" id="four_hour_price-validation-message"></small>
                                                    @error('four_hour_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-info-subtle text-info charge-badge">6 Hr</span></td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm auto-calculated-cost js-guide-cost" id="six_hour_cost_price" name="six_hour_cost_price"
                                                        data-sell-target="six_hour_price" placeholder="Auto" value="{{ old('six_hour_cost_price') }}" required
                                                        oninput="validateNumericPrice(this); applyGuideProfitToSells(true);">
                                                    <small class="validation-message text-danger" id="six_hour_cost_price-validation-message"></small>
                                                    @error('six_hour_cost_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm auto-calculated-sell js-guide-sell" id="six_hour_price" name="six_hour_price"
                                                        placeholder="Auto" value="{{ old('six_hour_price') }}" required
                                                        oninput="validateNumericPrice(this)">
                                                    <small class="validation-message text-danger" id="six_hour_price-validation-message"></small>
                                                    @error('six_hour_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-info-subtle text-info charge-badge">8 Hr</span></td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm auto-calculated-cost js-guide-cost" id="eight_hour_cost_price" name="eight_hour_cost_price"
                                                        data-sell-target="eight_hour_price" placeholder="Auto" value="{{ old('eight_hour_cost_price') }}" required
                                                        oninput="validateNumericPrice(this); applyGuideProfitToSells(true);">
                                                    <small class="validation-message text-danger" id="eight_hour_cost_price-validation-message"></small>
                                                    @error('eight_hour_cost_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm auto-calculated-sell js-guide-sell" id="eight_hour_price" name="eight_hour_price"
                                                        placeholder="Auto" value="{{ old('eight_hour_price') }}" required
                                                        oninput="validateNumericPrice(this)">
                                                    <small class="validation-message text-danger" id="eight_hour_price-validation-message"></small>
                                                    @error('eight_hour_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-info-subtle text-info charge-badge">10 Hr</span></td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm auto-calculated-cost js-guide-cost" id="ten_hour_cost_price" name="ten_hour_cost_price"
                                                        data-sell-target="ten_hour_price" placeholder="Auto" value="{{ old('ten_hour_cost_price') }}" required
                                                        oninput="validateNumericPrice(this); applyGuideProfitToSells(true);">
                                                    <small class="validation-message text-danger" id="ten_hour_cost_price-validation-message"></small>
                                                    @error('ten_hour_cost_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm auto-calculated-sell js-guide-sell" id="ten_hour_price" name="ten_hour_price"
                                                        placeholder="Auto" value="{{ old('ten_hour_price') }}" required
                                                        oninput="validateNumericPrice(this)">
                                                    <small class="validation-message text-danger" id="ten_hour_price-validation-message"></small>
                                                    @error('ten_hour_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-info-subtle text-info charge-badge">12 Hr</span></td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm auto-calculated-cost js-guide-cost" id="twelve_hour_cost_price" name="twelve_hour_cost_price"
                                                        data-sell-target="twelve_hour_price" placeholder="Auto" value="{{ old('twelve_hour_cost_price') }}" required
                                                        oninput="validateNumericPrice(this); applyGuideProfitToSells(true);">
                                                    <small class="validation-message text-danger" id="twelve_hour_cost_price-validation-message"></small>
                                                    @error('twelve_hour_cost_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm auto-calculated-sell js-guide-sell" id="twelve_hour_price" name="twelve_hour_price"
                                                        placeholder="Auto" value="{{ old('twelve_hour_price') }}" required
                                                        oninput="validateNumericPrice(this)">
                                                    <small class="validation-message text-danger" id="twelve_hour_price-validation-message"></small>
                                                    @error('twelve_hour_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Details --}}
                            <div class="col-12 mt-2 mb-1">
                                <div class="section-title"><i class="ri-file-text-line me-1"></i> Details</div>
                            </div>

                            <div class="col-md-4 mb-2">
                                <label for="master_image" class="form-label"><strong>Profile Image</strong><span class="text-danger">*</span></label>
                                <div id="master-drop-area" class="form-control form-control-sm"
                                    style="padding: 16px; border: 2px dashed #007bff; text-align: center; cursor: pointer;">
                                    Drag & Drop or click to upload
                                    <input type="file" id="master_image" name="master_image" style="display: none;" accept="image/*">
                                </div>
                                <div id="master_image_error" class="text-danger small mt-1 d-none"></div>
                                <div id="master-preview-container" class="mt-2 d-flex flex-wrap gap-2"
                                    style="max-width: 100%; overflow-x: auto; white-space: nowrap;"></div>
                            </div>

                            <div class="col-md-8 mb-2">
                                <label for="about" class="form-label"><strong>About</strong><span class="text-danger">*</span></label>
                                <textarea id="summernote" name="about" class="form-control form-control-sm" rows="4"
                                    placeholder="Write About Guide..." required>{{ old('about') }}</textarea>
                                <div id="about_error" class="text-danger small mt-1 d-none"></div>
                                @error('about')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4 mb-2">
                                <div class="form-check form-switch mt-1">
                                    <input type="hidden" name="guide_status" value="0">
                                    <input class="form-check-input" name="guide_status" type="checkbox" id="guide_status" value="1"
                                        {{ old('guide_status', '1') == '1' ? 'checked' : '' }}>
                                    <label for="guide_status" class="form-check-label"><strong>Active</strong></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <x-button-spinner id="saveGuideBtn" class="js-submit-loader-btn" label="Save" loadingText="Saving..." />
                        <a href="{{ route('guide.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
<!-- End of the form -->
<x-form-submit-loader message="Saving..." />
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

    // Summernote hides the textarea, so HTML5 required does not work.
    function getAboutText() {
        return $('<div>').html($('#summernote').summernote('code')).text().trim();
    }
    function setAboutError(message) {
        var errorEl = document.getElementById('about_error');
        var editor = $('#summernote').next('.note-editor');
        if (!errorEl) return;
        if (message) {
            errorEl.textContent = message;
            errorEl.classList.remove('d-none');
            editor.css('border-color', '#dc3545');
        } else {
            errorEl.classList.add('d-none');
            errorEl.textContent = '';
            editor.css('border-color', '');
        }
    }
    $('#guideForm').on('submit', function (e) {
        let blocked = false;

        function setFileError(id, message) {
            const el = document.getElementById(id);
            if (!el) return;
            if (message) {
                el.textContent = message;
                el.classList.remove('d-none');
            } else {
                el.textContent = '';
                el.classList.add('d-none');
            }
        }

        const masterInput = document.getElementById('master_image');
        const licenseInput = document.getElementById('license_image');

        if (!masterInput || !masterInput.files || masterInput.files.length === 0) {
            setFileError('master_image_error', 'Profile image is required.');
            blocked = true;
        } else {
            setFileError('master_image_error', '');
        }

        if (!licenseInput || !licenseInput.files || licenseInput.files.length === 0) {
            setFileError('license_image_error', 'License image is required.');
            blocked = true;
        } else {
            setFileError('license_image_error', '');
        }

        if (getAboutText() === '') {
            setAboutError('About is required. Please fill in this field.');
            blocked = true;
        } else {
            setAboutError('');
        }

        if (blocked) {
            e.preventDefault();
            e.stopImmediatePropagation();
            if (typeof window.resetFormSubmitSpinners === 'function') {
                window.resetFormSubmitSpinners(this);
            } else {
                const saveBtn = document.getElementById('saveGuideBtn');
                if (saveBtn) {
                    saveBtn.disabled = false;
                    const icon = saveBtn.querySelector('.btn-spinner-icon');
                    const label = saveBtn.querySelector('.btn-spinner-label');
                    if (icon) icon.classList.add('d-none');
                    if (label) label.textContent = 'Save';
                }
                const overlay = document.getElementById('formSubmitLoader');
                if (overlay) {
                    overlay.classList.remove('active');
                    overlay.setAttribute('aria-busy', 'false');
                }
            }
            const firstError = document.querySelector('#master_image_error:not(.d-none), #license_image_error:not(.d-none), #about_error:not(.d-none)');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return false;
        }

        // Disabled selects are not posted — enable city before submit.
        const citySelect = document.getElementById('citySelect');
        if (citySelect) {
            citySelect.disabled = false;
            if (window.jQuery) {
                window.jQuery(citySelect).prop('disabled', false);
            }
        }
    });

    $('#master_image, #license_image').on('change', function () {
        const errorId = this.id + '_error';
        const el = document.getElementById(errorId);
        if (el && this.files && this.files.length > 0) {
            el.textContent = '';
            el.classList.add('d-none');
        }
    });
    $('#summernote').on('summernote.change', function () {
        if (getAboutText() !== '') {
            setAboutError('');
        }
    });
    @error('about')
        setAboutError(@json($message));
    @enderror
});
</script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        function initLanguageSelect2(scope) {
            const $scope = scope ? $(scope) : $(document);
            $scope.find('select.language-select').each(function () {
                const $el = $(this);
                if ($el.hasClass('select2-hidden-accessible')) return; // already initialized
                $el.select2({
                    placeholder: "Search and Select Language",
                    allowClear: true,
                    width: '100%'
                });
            });
        }

        // Initialize Select2 for DMC dropdown
        $('#dmc').select2({
            placeholder: "Search and Select DMC",
            allowClear: true,
            width: '100%'
        });

        // Country's Select2 is initialised once, further down with the city/DMC wiring.

        // Initialize Select2 for Languages dropdown(s)
        initLanguageSelect2();
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
    var userRoleId = {{ auth()->user()->role_id }};
    var userCountry = @json($userCountry ?? '');
    var currentCity = @json(old('city', ''));
    var oldCountry = @json($preselectedCountry ?? ($userCountry ?? ''));

    $('#citySelect').select2({
        placeholder: "Select Country First",
        allowClear: true,
        width: '100%',
        disabled: true
    });

    $('#country').select2({
        placeholder: "Search and Select Country",
        allowClear: true,
        width: '100%'
    });

    function populateCountryOptions(countries) {
        var $country = $('#country');
        $country.empty();
        if (!countries || countries.length !== 1) {
            $country.append('<option value="">Select Country</option>');
        }
        $.each(countries || [], function(i, name) {
            $country.append('<option value="' + name + '">' + name + '</option>');
        });
        // Auto-select when DMC has a single base country
        if (countries && countries.length === 1) {
            $country.val(countries[0]).trigger('change');
        } else {
            $country.val('').trigger('change.select2');
            $('#citySelect').prop('disabled', true).empty()
                .append('<option value="">Select Country First</option>').trigger('change');
        }
    }

    function loadCitiesByCountry(countryName, preserveCity) {
        if (!countryName) {
            $('#citySelect').prop('disabled', true).empty().append('<option value="">Select Country First</option>').trigger('change');
            return;
        }

        $('#citySelect').prop('disabled', true).empty().append('<option value="">Loading cities...</option>').trigger('change');

        $.ajax({
            url: "{{ route('get.cities.by.country') }}",
            type: "GET",
            data: { country: countryName },
            dataType: 'json',
            success: function(response) {
                $('#citySelect').empty().append('<option value="">Select a City</option>');
                if (response.cities && response.cities.length > 0) {
                    $.each(response.cities, function(key, city) {
                        var selected = (preserveCity && currentCity && city.name === currentCity) ? 'selected' : '';
                        $('#citySelect').append('<option value="' + city.name + '" ' + selected + '>' + city.name + '</option>');
                    });
                    $('#citySelect').prop('disabled', false);
                    if (preserveCity && currentCity) {
                        $('#citySelect').val(currentCity);
                    }
                } else {
                    $('#citySelect').append('<option value="">No cities available</option>');
                }
                $('#citySelect').trigger('change');
            },
            error: function() {
                $('#citySelect').prop('disabled', true).empty().append('<option value="">Error loading cities</option>').trigger('change');
            }
        });
    }

    function loadCountriesAndCitiesForDmc(selectedDmcId) {
        if (!selectedDmcId) {
            $('#citySelect').prop('disabled', true).empty().append('<option value="">Select Country First</option>').trigger('change');
            return;
        }

        $.ajax({
            url: "{{ route('fetch.cities_countries') }}",
            type: "GET",
            data: { dmc_id: selectedDmcId },
            dataType: 'json',
            success: function(response) {
                var countries = response.countries || (response.country ? [response.country] : []);
                populateCountryOptions(countries);
            },
            error: function() {
                $('#country').empty().append('<option value="">Select Country</option>').trigger('change.select2');
                $('#citySelect').prop('disabled', true).empty().append('<option value="">Error loading cities</option>').trigger('change');
            }
        });
    }

    $('#country').on('change', function() {
        var selectedCountry = $(this).val();
        if (!selectedCountry) {
            currentCity = '';
            $('#citySelect').prop('disabled', true).empty().append('<option value="">Select Country First</option>').trigger('change');
            return;
        }
        // Manual country change should reset city unless we are restoring old input on first load.
        loadCitiesByCountry(selectedCountry, false);
    });

    if ([1, 2, 3, 4, 20, 23].includes(userRoleId)) {
        $('#dmc-container').show();
        $('#dmc').prop('required', true);
        $('#dmc').change(function() {
            var selectedDmcId = $(this).val();
            if (selectedDmcId) {
                loadCountriesAndCitiesForDmc(selectedDmcId);
            } else {
                $('#citySelect').prop('disabled', true).empty().append('<option value="">Select Country First</option>').trigger('change');
                $('#country').empty().append('<option value="">Select Country</option>').val('').trigger('change.select2');
            }
        });
    } else {
        $('#dmc-container').hide();
        $('#dmc').prop('required', false);
    }

    // Always reload cities for the currently selected country so they never stay
    // out of sync after a validation redirect (old country vs DMC-default cities).
    var initialCountry = oldCountry || $('#country').val() || userCountry;
    if (initialCountry) {
        if (!$('#country').val()) {
            $('#country').val(initialCountry).trigger('change.select2');
        }
        if (oldCountry && $('#country').val() !== oldCountry) {
            $('#country').val(oldCountry).trigger('change.select2');
        }
        loadCitiesByCountry(initialCountry, !!currentCity);
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
            newRow.classList.add("language-row", "row", "g-2", "mb-3", "align-items-end");
    
            newRow.innerHTML = `
                <div class="col-md-5">
                    <label class="form-label invisible"><strong>Languages</strong><span class="text-danger">*</span></label>
                    <select class="form-control form-control-sm language-select" name="languages[]" required>
                        <option value="">Select Language</option>
                        @foreach($languages as $c)
                            <option value="{{ $c->name }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
    
                <div class="col-md-5">
                    <label class="form-label invisible"><strong>Proficiency</strong><span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm proficiency-select" name="language_proficiency[]" required>
                        <option value="">Select</option>
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Fluent">Fluent</option>
                        <option value="Expert">Expert</option>
                        <option value="Mother Tongue">Mother Tongue</option>
                    </select>
                </div>
    
                <div class="col-md-2 d-flex align-items-end">
                    <label class="form-label invisible">Remove</label>
                    <button type="button" class="btn btn-sm btn-danger remove-language">Remove</button>
                </div>
            `;
    
            languageContainer.appendChild(newRow);
            if (window.jQuery && typeof $(newRow).find('.language-select').select2 === 'function') {
                $(newRow).find('.language-select').select2({
                    placeholder: "Search and Select Language",
                    allowClear: true,
                    width: '100%'
                });
            }
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
        // Ensure dropped files are actually submitted with the form
        if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
            const dt = new DataTransfer();
            Array.from(e.dataTransfer.files).forEach((file) => dt.items.add(file));
            licenseFileInput.files = dt.files;
        }
        licenseHandleFiles(licenseFileInput.files);
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
            licensePreviewContainer.removeChild(imageWrapper);
            licenseFileCounter--;
            // Clear the hidden file input so submit validation stays accurate
            licenseFileInput.value = '';
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
        // Ensure dropped files are actually submitted with the form
        if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
            const dt = new DataTransfer();
            Array.from(e.dataTransfer.files).forEach((file) => dt.items.add(file));
            masterFileInput.files = dt.files;
        }
        masterHandleFiles(masterFileInput.files);
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
            masterPreviewContainer.removeChild(imageWrapper);
            masterFileCounter--;
            masterFileInput.value = '';
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
    // Note: there is already a working "Add More" implementation above.
    // This older block referenced `.language-fields` / `.proficiency-fields` which don't exist
    // and could throw runtime JS errors, so it is intentionally disabled.
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
        // Keep valid fields clean — no green border / "Looks good!" clutter
        messageElement.innerHTML = '';
        inputElement.classList.remove('is-invalid', 'is-valid');
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
        if (!input) {
            return;
        }

        const value = parseInt(input.value, 10);
        const countryEl = document.getElementById('country');
        const country = countryEl ? countryEl.value : '';

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

// Function to calculate hourly sell rates based on minimum sell price
function calculateHourlyRates() {
    const basePriceInput = document.getElementById('day_rate');
    const basePrice = parseFloat(basePriceInput.value) || 0;
    
    if (basePrice <= 0) {
        clearHourlyRates('sell');
        return;
    }
    
    const hourlyRate = basePrice;
    const hourMultipliers = {
        'hourly_price': 1,
        'two_hour_price': 2,
        'four_hour_price': 4,
        'six_hour_price': 6,
        'eight_hour_price': 8,
        'ten_hour_price': 10,
        'twelve_hour_price': 12
    };
    
    updateCalculatedRates(hourMultipliers, hourlyRate, 'auto-calculated-sell');
}

// Function to calculate hourly cost rates based on minimum cost price
function calculateHourlyCostRates() {
    const baseCostInput = document.getElementById('minimum_cost_price');
    const baseCost = parseFloat(baseCostInput.value) || 0;
    
    if (baseCost <= 0) {
        clearHourlyRates('cost');
        return;
    }
    
    const hourlyCostRate = baseCost;
    const hourMultipliers = {
        'hourly_cost_price': 1,
        'two_hour_cost_price': 2,
        'four_hour_cost_price': 4,
        'six_hour_cost_price': 6,
        'eight_hour_cost_price': 8,
        'ten_hour_cost_price': 10,
        'twelve_hour_cost_price': 12
    };
    
    updateCalculatedRates(hourMultipliers, hourlyCostRate, 'auto-calculated-cost');
    if (typeof applyGuideProfitToSells === 'function') {
        applyGuideProfitToSells(true);
    }
}

function updateCalculatedRates(hourMultipliers, baseRate, cssClass) {
    Object.keys(hourMultipliers).forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            const calculatedValue = Math.round((baseRate * hourMultipliers[fieldId]) * 100) / 100;
            if (field.value === '' || field.classList.contains(cssClass)) {
                field.value = calculatedValue.toFixed(2);
                field.classList.add('value-updated');
                setTimeout(() => field.classList.remove('value-updated'), 800);
                validateNumericPrice(field);
            }
        }
    });
}

// Function to clear hourly rate fields
function clearHourlyRates(type) {
    const sellFields = [
        'hourly_price', 'two_hour_price', 'four_hour_price',
        'six_hour_price', 'eight_hour_price', 'ten_hour_price', 'twelve_hour_price'
    ];
    const costFields = [
        'hourly_cost_price', 'two_hour_cost_price', 'four_hour_cost_price',
        'six_hour_cost_price', 'eight_hour_cost_price', 'ten_hour_cost_price', 'twelve_hour_cost_price'
    ];
    const fields = type === 'cost' ? costFields : (type === 'sell' ? sellFields : sellFields.concat(costFields));
    const cssClass = type === 'cost' ? 'auto-calculated-cost' : 'auto-calculated-sell';
    
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field && field.classList.contains(cssClass)) {
            field.value = '';
            field.classList.remove('is-valid', 'is-invalid');
            const messageElement = document.getElementById(`${fieldId}-validation-message`);
            if (messageElement) messageElement.innerHTML = '';
        }
    });
}

// Initialize calculation on page load if there's already a value
document.addEventListener('DOMContentLoaded', function() {
    const basePriceInput = document.getElementById('day_rate');
    if (basePriceInput && basePriceInput.value) {
        calculateHourlyRates();
    }
    const baseCostInput = document.getElementById('minimum_cost_price');
    if (baseCostInput && baseCostInput.value) {
        calculateHourlyCostRates();
    }

    document.querySelectorAll('.js-guide-sell').forEach(function (sellEl) {
        sellEl.addEventListener('input', function () {
            sellEl.dataset.userEdited = '1';
        });
    });
    document.querySelectorAll('.js-guide-profit-type, .js-guide-profit-amount').forEach(function (el) {
        el.addEventListener('input', function () { applyGuideProfitToSells(true); });
        el.addEventListener('change', function () { applyGuideProfitToSells(true); });
    });
});

function applyGuideProfitToSells(force) {
    function round2(n) {
        return Math.round((Number(n) + Number.EPSILON) * 100) / 100;
    }
    function calcSellFromCost(cost, type, amount) {
        const c = parseFloat(cost);
        const a = parseFloat(amount);
        const costVal = isNaN(c) ? 0 : c;
        const amtVal = isNaN(a) ? 0 : a;
        if (costVal <= 0) return 0;
        if (type === 'flat') return round2(costVal + amtVal);
        return round2(costVal + (costVal * amtVal / 100));
    }
    const typeEl = document.querySelector('.js-guide-profit-type');
    const amountEl = document.querySelector('.js-guide-profit-amount');
    const type = typeEl ? typeEl.value : 'percentage';
    const amount = amountEl ? amountEl.value : 0;

    document.querySelectorAll('.js-guide-cost[data-sell-target]').forEach(function (costEl) {
        const sellId = costEl.getAttribute('data-sell-target');
        const sellEl = document.getElementById(sellId);
        if (!sellEl) return;
        if (!force && sellEl.dataset.userEdited === '1') return;
        sellEl.value = calcSellFromCost(costEl.value, type, amount).toFixed(2);
        sellEl.dataset.userEdited = '';
        if (typeof validateNumericPrice === 'function') {
            validateNumericPrice(sellEl);
        }
    });
}

// Add CSS for validation messages and input styles
document.head.insertAdjacentHTML('beforeend', `
    <style>
        .validation-message {
            margin-top: 0.25rem;
            font-size: 0.75rem;
        }

        .validation-message .invalid-feedback {
            display: block;
            color: #dc3545;
            padding: 0;
            margin: 0;
            background: none;
            border: none;
            box-shadow: none;
        }

        .validation-message ul {
            margin: 0.25rem 0 0 0;
            padding-left: 1.25rem;
            list-style-type: disc;
        }

        .validation-message ul li {
            padding: 0.1rem 0;
            color: #6c757d;
        }

        .validation-message i {
            margin-right: 0.35rem;
            font-size: 0.8rem;
        }

        /* Suppress Bootstrap success styling — only show errors */
        .guide-form-compact .form-control.is-valid,
        .guide-form-compact .form-select.is-valid {
            border-color: #d9dee3 !important;
            background-image: none !important;
            padding-right: 0.5rem !important;
        }

        .guide-form-compact .form-control.is-invalid,
        .guide-form-compact .form-select.is-invalid {
            border-color: #dc3545 !important;
            background-color: #fff !important;
        }

        .guide-form-compact .form-control:focus,
        .guide-form-compact .form-select:focus {
            border-color: #696cff !important;
            box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.15) !important;
        }

        .guide-form-compact .form-control.is-invalid:focus,
        .guide-form-compact .form-select.is-invalid:focus {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.15) !important;
        }
    </style>
`);

    // Toggle App Password visibility
    document.getElementById('toggleAppPassword').addEventListener('click', function() {
        const passwordField = document.getElementById('app_password');
        const icon = document.getElementById('appPasswordIcon');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.classList.remove('ri-eye-off-line');
            icon.classList.add('ri-eye-line');
        } else {
            passwordField.type = 'password';
            icon.classList.remove('ri-eye-line');
            icon.classList.add('ri-eye-off-line');
        }
    });
</script>

@include('components.currency-price-note-dmc-script')

{{-- Clear Laravel / server field errors as soon as the user edits that field --}}
<script>
(function () {
    const form = document.getElementById('guideForm');
    if (!form) return;

    function markServerFieldErrors() {
        form.querySelectorAll('div.text-danger.mt-1, div.text-danger.small.mt-1').forEach(function (el) {
            if (el.closest('label')) return;
            if (el.classList.contains('validation-message')) return;
            el.classList.add('js-server-field-error');
        });
    }

    function refreshSummaryAlert() {
        const summary = document.getElementById('guide-validation-summary');
        if (!summary) return;

        const remainingFieldErrors = form.querySelectorAll('.js-server-field-error');
        const list = summary.querySelector('ul');

        if (remainingFieldErrors.length === 0) {
            summary.remove();
            return;
        }

        if (!list) return;

        // Drop summary items whose matching field error was cleared.
        const remainingTexts = Array.from(remainingFieldErrors).map(function (el) {
            return (el.textContent || '').trim().toLowerCase();
        });

        list.querySelectorAll('li').forEach(function (li) {
            const text = (li.getAttribute('data-server-error-text') || li.textContent || '').trim().toLowerCase();
            if (text && !remainingTexts.includes(text)) {
                li.remove();
            }
        });

        if (!list.querySelector('li')) {
            summary.remove();
        }
    }

    function clearServerErrorForField(field) {
        if (!field || !field.name) return;

        const wrap = field.closest('.mb-3, .mb-4, [class*="col-"]') || field.parentElement;
        if (!wrap) return;

        wrap.querySelectorAll('.js-server-field-error').forEach(function (el) {
            el.remove();
        });

        // Also clear any unmarked Laravel error divs next to this control.
        wrap.querySelectorAll(':scope > div.text-danger.mt-1, :scope > div.text-danger.small').forEach(function (el) {
            if (el.closest('label')) return;
            el.remove();
        });

        field.classList.remove('is-invalid');
        refreshSummaryAlert();
    }

    markServerFieldErrors();

    form.addEventListener('input', function (e) {
        const t = e.target;
        if (!t || !['INPUT', 'TEXTAREA', 'SELECT'].includes(t.tagName)) return;
        clearServerErrorForField(t);
    });

    form.addEventListener('change', function (e) {
        const t = e.target;
        if (!t || !['INPUT', 'TEXTAREA', 'SELECT'].includes(t.tagName)) return;
        clearServerErrorForField(t);
    });
})();
</script>
@endsection