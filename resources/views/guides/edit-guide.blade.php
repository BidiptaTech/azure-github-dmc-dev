@extends('layouts.layout')
@section('content')
<style>
    /* Compact form layout (aligned with create-guide) */
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

    .flatpickr-calendar.open {
        width: auto !important;
        min-width: 120px;
    }

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

    .select2-container .select2-results__option {
        padding: 8px 12px;
    }

    .guide-form-compact #language-container .proficiency-select,
    .guide-form-compact #language-container .language-select,
    .guide-form-compact .language-row .form-control,
    .guide-form-compact .language-row .form-select {
        height: 31px;
        font-size: 0.8125rem;
    }

    .guide-form-compact .language-row .remove-language,
    .guide-form-compact #addmore {
        height: 31px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
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

    .readonly-field-styling {
        background-color: #f0f2f5 !important;
        border: 1px solid #dfe3e7 !important;
        color: #6e7781 !important;
        cursor: default !important;
        box-shadow: none !important;
    }

    .readonly-field-styling:focus {
        box-shadow: none !important;
        border-color: #dfe3e7 !important;
        outline: none !important;
    }

    .readonly-field-container {
        position: relative;
    }

    .readonly-field-container::after {
        content: '\f023';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
        font-size: 12px;
        pointer-events: none;
    }

    .zero-value-updated {
        animation: zeroValueUpdate 1s ease-in-out;
        border-left: 3px solid #28a745 !important;
    }

    @keyframes zeroValueUpdate {
        0% { background-color: #d4edda; border-left-color: #28a745; transform: scale(1.02); }
        50% { background-color: #f8f9fa; border-left-color: #17a2b8; }
        100% { background-color: #fff; border-left-color: #ced4da; transform: scale(1); }
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <h4 class="card-title mb-0">Edit Guide Details</h4>
                    @php
                        $guideNoteCountry = trim((string) ($guide->country ?? $selectedCountry ?? ''));
                        $guideNoteCurrency = $guideNoteCountry !== ''
                            ? \App\Models\Country::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($guideNoteCountry)])->value('currency')
                            : null;
                        $guidePrice = static function ($value) {
                            if ($value === null || $value === '') {
                                return '';
                            }
                            return number_format((float) $value, 2, '.', '');
                        };
                    @endphp
                    <x-currency-price-note
                        :country="$guideNoteCountry !== '' ? $guideNoteCountry : null"
                        :currency="$guideNoteCurrency"
                        :watch-country="true"
                        country-select-id="country"
                    />
                </div>
                <a href="{{ route('guide.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </div>
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
                        </div>
                    </div>
                </div>
            @endif

            <form id="guideForm" method="POST" action="{{ route('guide.update', Crypt::encrypt($guide->guide_id)) }}"
                enctype="multipart/form-data" class="card-body guide-form-compact">
                @csrf
                @method('PUT')
                <div id="guideDetailsContainer">
                    <div class="guide-form">
                        <div class="row g-2">
                            <div class="col-12 mt-1 mb-1">
                                <div class="section-title"><i class="ri-user-line me-1"></i> Guide Info</div>
                            </div>

                            @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 23 || auth()->user()->role_id == 25 || auth()->user()->role_id == 45 || auth()->user()->role_id == 61 || auth()->user()->role_id == 100 || auth()->user()->role_id == 101)
                            <div class="col-md-3 mb-2" id="dmc-container">
                                <label for="dmc" class="form-label"><strong>Select DMC</strong><span class="text-danger">*</span></label>
                                <select id="dmc" class="form-control form-control-sm" disabled>
                                    <option value="">Select DMC</option>
                                    @foreach ($dmcs as $dmc)
                                        <option value="{{ $dmc->userId }}" {{ $guide->dmc_id == $dmc->userId ? 'selected' : '' }}>{{ $dmc->company_name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="dmc_id" value="{{ $guide->dmc_id }}">
                            </div>
                            @endif

                            <div class="col-md-2 mb-2">
                                <label for="salutation" class="form-label"><strong>Salutation</strong><span class="text-danger">*</span></label>
                                <select class="form-control form-control-sm" name="salutation" required>
                                    <option value="">Select</option>
                                    <option value="Mr" {{ old('salutation', $guide->salutation ?? '') == 'Mr' ? 'selected' : '' }}>Mr.</option>
                                    <option value="Mrs" {{ old('salutation', $guide->salutation ?? '') == 'Mrs' ? 'selected' : '' }}>Mrs.</option>
                                    <option value="Miss" {{ old('salutation', $guide->salutation ?? '') == 'Miss' ? 'selected' : '' }}>Ms.</option>
                                    <option value="Dear" {{ old('salutation', $guide->salutation ?? '') == 'Dear' ? 'selected' : '' }}>Dear</option>
                                </select>
                                @error('salutation')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="guide_gender" class="form-label"><strong>Gender</strong><span class="text-danger">*</span></label>
                                <select class="form-control form-control-sm" name="guide_gender" id="guide_gender" required>
                                    <option value="">Select</option>
                                    <option value="Male" {{ old('guide_gender', $guide->guide_gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('guide_gender', $guide->guide_gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                                    <option value="Other" {{ old('guide_gender', $guide->guide_gender ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('guide_gender')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3 mb-2">
                                <label for="name" class="form-label"><strong>Guide Name</strong><span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="name" placeholder="Enter Guide Name"
                                    value="{{ old('name', $guide->name) }}" required>
                                @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3 mb-2">
                                <label for="contact_no" class="form-label"><strong>Contact No</strong><span class="text-danger">*</span></label>
                                <input name="contact_no" type="text" id="contact_no" class="form-control form-control-sm"
                                    placeholder="Enter Contact No" value="{{ old('contact_no', $guide->contact_no) }}" required
                                    oninput="validatePhoneNumber(this)">
                                <small class="validation-message text-danger" id="contact_no-validation-message"></small>
                                @error('contact_no')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3 mb-2">
                                <label for="email" class="form-label"><strong>Email</strong><span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="email" name="email"
                                    placeholder="Enter Email..." value="{{ old('email', $guide->email) }}" required
                                    oninput="validateEmail(this)">
                                <small class="validation-message text-danger" id="email-validation-message"></small>
                                @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3 mb-2">
                                <label for="app_password" class="form-label"><strong>App Password</strong></label>
                                <div class="input-group input-group-sm">
                                    <input type="password" class="form-control form-control-sm" id="app_password" name="app_password"
                                        value="" placeholder="Enter app password" autocomplete="new-password">
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
                                    $editSelectedCountry = old('country', $selectedCountry ?? $guide->country ?? '');
                                    if ($editSelectedCountry === '' && $scopedCountries->count() === 1) {
                                        $editSelectedCountry = $scopedCountries->first()->name ?? '';
                                    }
                                    if (filled($editSelectedCountry) && !$scopedCountries->contains(function ($c) use ($editSelectedCountry) {
                                        return strcasecmp(trim((string) ($c->name ?? '')), trim((string) $editSelectedCountry)) === 0;
                                    })) {
                                        $missingCountry = \App\Models\Country::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim((string) $editSelectedCountry))])->first();
                                        if ($missingCountry) {
                                            $scopedCountries = $scopedCountries->prepend($missingCountry)->unique('id')->values();
                                        }
                                    }
                                @endphp
                                <select class="form-control form-control-sm" id="country" name="country" required onchange="validateDriverAge(document.getElementById('guide_age'))">
                                    @if($scopedCountries->count() !== 1)
                                        <option value="">Select Country</option>
                                    @endif
                                    @foreach($scopedCountries as $countryOption)
                                        <option value="{{ $countryOption->name }}"
                                            {{ strcasecmp(trim((string) $editSelectedCountry), trim((string) $countryOption->name)) === 0 ? 'selected' : '' }}>
                                            {{ $countryOption->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('country')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3 mb-2">
                                <label for="city" class="form-label"><strong>City</strong><span class="text-danger">*</span></label>
                                <select name="city" id="citySelect" class="form-control form-control-sm" required>
                                    <option value="">Select City</option>
                                    @foreach($city as $c)
                                        <option value="{{ $c->name }}" {{ old('city', $guide->city) == $c->name ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                @error('city')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="service_type" class="form-label"><strong>Service Type</strong><span class="text-danger">*</span></label>
                                <select id="service_type" class="form-select form-select-sm" name="service_type" required>
                                    <option value="">Select</option>
                                    <option value="1" {{ old('service_type', $guide->service_type) == 1 ? 'selected' : '' }}>Private</option>
                                    <option value="2" {{ old('service_type', $guide->service_type) == 2 ? 'selected' : '' }}>Shared</option>
                                    <option value="3" {{ old('service_type', $guide->service_type) == 3 ? 'selected' : '' }}>Both</option>
                                </select>
                                @error('service_type')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="guide_age" class="form-label"><strong>Age</strong><span class="text-danger">*</span></label>
                                <input id="guide_age" type="number" class="form-control form-control-sm" name="guide_age"
                                    value="{{ old('guide_age', $guide->guide_age) }}" placeholder="Age" oninput="validateDriverAge(this)">
                                <small class="validation-message text-danger" id="guide_age-validation-message"></small>
                                @error('guide_age')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3 mb-2">
                                <label for="wp_number" class="form-label"><strong>Whatsapp Number</strong><span class="text-danger">*</span></label>
                                <input type="text" id="wp_number" class="form-control form-control-sm" name="wp_number"
                                    placeholder="Enter Whatsapp Number" value="{{ old('wp_number', $guide->wp_number) }}" required>
                                @error('wp_number')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mt-2 mb-1" id="guide_language">
                                <div class="section-title"><i class="ri-translate-2 me-1"></i> Languages & Proficiency</div>
                            </div>
                            <div class="col-12 mb-2">
                                @if($languages)
                                    @foreach($languages as $language)
                                        <div class="language-row row g-2 mb-2 align-items-end">
                                            <div class="col-md-5">
                                                <label class="form-label"><strong>Languages</strong><span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm language-select" name="languages[]" required>
                                                    <option value="">Select Language</option>
                                                    @foreach($languagesname as $c)
                                                        <option value="{{ $c->name }}" @if(old('languagesname', $language->language ?? '') == $c->name) selected @endif>
                                                            {{ $c->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label"><strong>Proficiency</strong><span class="text-danger">*</span></label>
                                                <select class="form-select form-select-sm proficiency-select" name="language_proficiency[]" required>
                                                    <option value="">Select</option>
                                                    <option value="Beginner" {{ $language->proficiency == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                                                    <option value="Intermediate" {{ $language->proficiency == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                                                    <option value="Fluent" {{ $language->proficiency == 'Fluent' ? 'selected' : '' }}>Fluent</option>
                                                    <option value="Expert" {{ $language->proficiency == 'Expert' ? 'selected' : '' }}>Expert</option>
                                                    <option value="Mother Tongue" {{ $language->proficiency == 'Mother Tongue' ? 'selected' : '' }}>Mother Tongue</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-sm btn-danger remove-language">Remove</button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                <div id="language-container">
                                    <div class="language-row row g-2 mb-2 align-items-end">
                                        @if(!$languages)
                                        <div class="col-md-5">
                                            <label class="form-label"><strong>Languages</strong><span class="text-danger">*</span></label>
                                            <select class="form-control form-control-sm language-select" name="languages[]" required>
                                                <option value="">Select Language</option>
                                                @foreach($languagesname as $c)
                                                    <option value="{{ $c->name }}">{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label"><strong>Proficiency</strong></label>
                                            <select class="form-select form-select-sm proficiency-select" name="language_proficiency[]">
                                                <option value="">Select</option>
                                                <option value="Beginner">Beginner</option>
                                                <option value="Intermediate">Intermediate</option>
                                                <option value="Fluent">Fluent</option>
                                                <option value="Expert">Expert</option>
                                                <option value="Mother Tongue">Mother Tongue</option>
                                            </select>
                                        </div>
                                        @endif
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" id="addmore" class="btn btn-sm btn-primary">Add More</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-2 mb-1" id="License">
                                <div class="section-title"><i class="ri-id-card-line me-1"></i> License</div>
                            </div>

                            <div class="col-md-3 mb-2">
                                <label for="license_no" class="form-label"><strong>Gov. License No</strong><span class="text-danger">*</span></label>
                                <div class="readonly-field-container">
                                    <input type="text" class="form-control form-control-sm readonly-field-styling" name="license_no"
                                        value="{{ old('license_no', $guide->government_license_no) }}" readonly>
                                </div>
                                <small class="text-muted d-block" style="font-size: 9px;"><i class="fas fa-lock"></i> Locked — cannot be edited.</small>
                                @error('license_no')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="license_exp_date" class="form-label"><strong>License Expiry</strong><span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm" name="license_exp_date"
                                    value="{{ old('license_exp_date', $guide->license_exp_date) }}" required>
                                @error('license_exp_date')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2 mb-2">
                                <label for="experience" class="form-label"><strong>Experience (Yrs)</strong><span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="experience" name="experience"
                                    placeholder="Years" value="{{ old('experience', $guide->experience_years) }}" required
                                    oninput="validateNumericPrice(this)">
                                <small class="validation-message text-danger" id="experience-validation-message"></small>
                                @error('experience')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="license_image" class="form-label"><strong>License Image</strong></label>
                                <div id="license-drop-area" class="form-control form-control-sm"
                                    style="padding: 12px; border: 2px dashed #007bff; text-align: center; cursor: pointer;">
                                    Drag & Drop or click to upload
                                    <input type="file" id="license_image" name="license_image" multiple style="display: none;">
                                </div>
                                <div id="license-preview-container" class="mt-1 d-flex flex-wrap gap-2"
                                    style="max-width: 100%; overflow-x: auto; white-space: nowrap;"></div>
                                @if($guide->license_image)
                                <div class="license-image-preview-container d-flex flex-wrap gap-2 mt-2">
                                    <div class="license-image-preview-wrapper position-relative">
                                        <img src="{{ $guide->license_image }}" alt="License Image"
                                            style="max-width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                                        <button type="button"
                                            class="delete-license-image-btn position-absolute top-0 end-0 btn btn-sm btn-danger"
                                            data-image="{{ $guide->license_image }}"
                                            style="width: 20px; height: 20px; line-height: 18px; padding: 0; text-align: center; font-size: 14px; z-index: 1;">
                                            &times;
                                        </button>
                                    </div>
                                </div>
                                @endif
                            </div>

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
                                    placeholder="0.00" value="{{ $guidePrice(old('night_surcharge', $guide->night_surcharge)) }}" required
                                    oninput="validateNumericPrice(this)">
                                <small class="validation-message text-danger" id="night_surcharge-validation-message"></small>
                                @error('night_surcharge')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2 mb-2">
                                <label for="night_start_time" class="form-label"><strong>Night Start</strong><span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="night_start_time" name="night_start_time"
                                    placeholder="Start time" value="{{ old('night_start_time', $guide->night_start_time) }}">
                                @error('night_start_time')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2 mb-2">
                                <label for="night_end_time" class="form-label"><strong>Night End</strong><span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="night_end_time" name="night_end_time"
                                    placeholder="End time" value="{{ old('night_end_time', $guide->night_end_time) }}">
                                @error('night_end_time')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

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
                                            @php
                                                $priceRows = [
                                                    ['Minimum', 'minimum_cost_price', 'day_rate', false, 'calculateEditHourlyCostRates(); applyGuideProfitToSells(true);', 'calculateEditHourlyRates();'],
                                                    ['1 Hr', 'hourly_cost_price', 'hourly_price', true, 'applyGuideProfitToSells(true);', ''],
                                                    ['2 Hr', 'two_hour_cost_price', 'two_hour_price', true, 'applyGuideProfitToSells(true);', ''],
                                                    ['4 Hr', 'four_hour_cost_price', 'four_hour_price', true, 'applyGuideProfitToSells(true);', ''],
                                                    ['6 Hr', 'six_hour_cost_price', 'six_hour_price', true, 'applyGuideProfitToSells(true);', ''],
                                                    ['8 Hr', 'eight_hour_cost_price', 'eight_hour_price', true, 'applyGuideProfitToSells(true);', ''],
                                                    ['10 Hr', 'ten_hour_cost_price', 'ten_hour_price', true, 'applyGuideProfitToSells(true);', ''],
                                                    ['12 Hr', 'twelve_hour_cost_price', 'twelve_hour_price', true, 'applyGuideProfitToSells(true);', ''],
                                                ];
                                            @endphp
                                            @foreach($priceRows as [$label, $costField, $sellField, $auto, $costExtra, $sellExtra])
                                            <tr>
                                                <td>
                                                    <span class="badge {{ $label === 'Minimum' ? 'bg-primary-subtle text-primary' : 'bg-info-subtle text-info' }} charge-badge">{{ $label }}</span>
                                                </td>
                                                <td>
                                                    <input type="text"
                                                        class="form-control form-control-sm js-guide-cost{{ $auto ? ' auto-calculated-cost' : '' }}"
                                                        id="{{ $costField }}" name="{{ $costField }}" data-sell-target="{{ $sellField }}"
                                                        placeholder="{{ $auto ? 'Auto' : '0.00' }}"
                                                        value="{{ $guidePrice(old($costField, $guide->{$costField})) }}" required
                                                        oninput="validateNumericPrice(this); {{ $costExtra }}">
                                                    <small class="validation-message text-danger" id="{{ $costField }}-validation-message"></small>
                                                    @error($costField)<div class="text-danger small">{{ $message }}</div>@enderror
                                                </td>
                                                <td>
                                                    <input type="text"
                                                        class="form-control form-control-sm js-guide-sell{{ $auto ? ' auto-calculated-sell' : '' }}"
                                                        id="{{ $sellField }}" name="{{ $sellField }}"
                                                        placeholder="{{ $auto ? 'Auto' : '0.00' }}"
                                                        value="{{ $guidePrice(old($sellField, $guide->{$sellField})) }}" required
                                                        oninput="validateNumericPrice(this); {{ $sellExtra }}">
                                                    <small class="validation-message text-danger" id="{{ $sellField }}-validation-message"></small>
                                                    @error($sellField)<div class="text-danger small">{{ $message }}</div>@enderror
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="col-12 mt-2 mb-1">
                                <div class="section-title"><i class="ri-file-text-line me-1"></i> Details</div>
                            </div>

                            <div class="col-md-4 mb-2">
                                <label for="master_image" class="form-label"><strong>Profile Image</strong></label>
                                <div id="master-drop-area" class="form-control form-control-sm"
                                    style="padding: 16px; border: 2px dashed #007bff; text-align: center; cursor: pointer;">
                                    Drag & Drop or click to upload
                                    <input type="file" id="master_image" name="master_image" multiple style="display: none;">
                                </div>
                                <div id="master-preview-container" class="mt-2 d-flex flex-wrap gap-2"
                                    style="max-width: 100%; overflow-x: auto; white-space: nowrap;"></div>
                                @if($guide->image)
                                <div class="image-preview-container d-flex flex-wrap gap-2 mt-2">
                                    <div class="image-preview-wrapper position-relative">
                                        <img src="{{ $guide->image }}" alt="Guide Image"
                                            style="max-width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                                        <button type="button"
                                            class="delete-image-btn position-absolute top-0 end-0 btn btn-sm btn-danger"
                                            data-image="{{ $guide->image }}"
                                            style="width: 20px; height: 20px; line-height: 18px; padding: 0; text-align: center; font-size: 14px; z-index: 1;">
                                            &times;
                                        </button>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <div class="col-md-8 mb-2">
                                <label for="about" class="form-label"><strong>About</strong><span class="text-danger">*</span></label>
                                <textarea id="summernote" name="about" class="form-control form-control-sm" rows="4"
                                    placeholder="Write About Guide..." required>{{ old('about', htmlspecialchars_decode($guide->description)) }}</textarea>
                                <div id="about_error" class="text-danger small mt-1 d-none"></div>
                                @error('about')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4 mb-2">
                                <div class="form-check form-switch mt-1">
                                    <input type="hidden" name="guide_status" value="0">
                                    <input class="form-check-input" name="guide_status" type="checkbox" id="guide_status" value="1"
                                        {{ old('guide_status', $guide->is_active) == 1 ? 'checked' : '' }}>
                                    <label for="guide_status" class="form-check-label"><strong>Active</strong></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary px-4">Update</button>
                        <a href="{{ route('guide.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- End of the form -->
@endsection

@section('scripts')
@include('components.currency-price-note-dmc-script')

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('#summernote').summernote({
            height: 200,      
            minHeight: 200,   
            maxHeight: 500,   
            placeholder: 'Enter your content here...', 
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
            if (getAboutText() === '') {
                e.preventDefault();
                e.stopImmediatePropagation();
                setAboutError('About is required. Please fill in this field.');
                var errorEl = document.getElementById('about_error');
                if (errorEl) {
                    errorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
            setAboutError('');
        });
        $('#summernote').on('summernote.change', function () {
            if (getAboutText() !== '') {
                setAboutError('');
            }
        });
        @error('about')
            setAboutError(@json($message));
        @enderror

        $('#country').select2({
            placeholder: "Search and Select Country",
            allowClear: true,
            width: '100%'
        });
        if (typeof window.updateCurrencyPriceNoteFromCountry === 'function') {
            window.updateCurrencyPriceNoteFromCountry(document.getElementById('country'));
        }
        $('#citySelect').select2({
            placeholder: "Search and Select a City",
            allowClear: true,
            width: '100%'
        });

        var currentCity = @json(old('city', $guide->city ?? ''));

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
                            var selected = (preserveCity && city.name === currentCity) ? 'selected' : '';
                            $('#citySelect').append('<option value="' + city.name + '" ' + selected + '>' + city.name + '</option>');
                        });
                        $('#citySelect').prop('disabled', false);
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

        $('#country').on('change', function() {
            loadCitiesByCountry($(this).val(), false);
        });
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

<script>
    document.addEventListener("DOMContentLoaded", function () {
    const languageContainer = document.getElementById("language-container");
    const addMoreBtn = document.getElementById("addmore");

    // Function to create a new language row
    function createLanguageRow() {
        const newRow = document.createElement("div");
        newRow.classList.add("row", "language-row", "g-2", "mb-2", "align-items-end");

        newRow.innerHTML = `
            <div class="col-md-5">
                <label class="form-label"><strong>Languages</strong><span class="text-danger">*</span></label>
                <select class="form-control form-control-sm language-select" name="languages[]" required>
                    <option value="">Select Language</option>
                    @foreach($languagesname as $c)
                        <option value="{{ $c->name }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-5">
                <label class="form-label"><strong>Proficiency</strong></label>
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
                <button type="button" class="btn btn-sm btn-danger remove-language">Remove</button>
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

            .form-control.is-valid,
            .form-select.is-valid {
                border-color: #d9dee3 !important;
                background-image: none !important;
                padding-right: 0.75rem !important;
            }

            .form-control.is-invalid,
            .form-select.is-invalid {
                border-color: #dc3545 !important;
                background-color: #fff !important;
            }

            .form-control:focus,
            .form-select:focus {
                border-color: #696cff !important;
                box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.15) !important;
            }

            .form-control.is-invalid:focus,
            .form-select.is-invalid:focus {
                border-color: #dc3545 !important;
                box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.15) !important;
            }
        </style>
    `);
    
    // Function to calculate hourly rates (simplified - always updates all fields)
    function calculateEditHourlyRates() {
        const basePriceInput = document.getElementById('day_rate');
        const basePrice = parseFloat(basePriceInput.value) || 0;
        
        if (basePrice <= 0) {
            clearEditHourlyFields('sell');
            return;
        }
        
        const hourMultipliers = {
            'hourly_price': 1,
            'two_hour_price': 2,
            'four_hour_price': 4,
            'six_hour_price': 6,
            'eight_hour_price': 8,
            'ten_hour_price': 10,
            'twelve_hour_price': 12
        };
        
        updateEditCalculatedRates(hourMultipliers, basePrice);
    }

    function calculateEditHourlyCostRates() {
        const baseCostInput = document.getElementById('minimum_cost_price');
        const baseCost = parseFloat(baseCostInput.value) || 0;
        
        if (baseCost <= 0) {
            clearEditHourlyFields('cost');
            return;
        }
        
        const hourMultipliers = {
            'hourly_cost_price': 1,
            'two_hour_cost_price': 2,
            'four_hour_cost_price': 4,
            'six_hour_cost_price': 6,
            'eight_hour_cost_price': 8,
            'ten_hour_cost_price': 10,
            'twelve_hour_cost_price': 12
        };
        
        updateEditCalculatedRates(hourMultipliers, baseCost);
        if (typeof applyGuideProfitToSells === 'function') {
            applyGuideProfitToSells(true);
        }
    }

    function updateEditCalculatedRates(hourMultipliers, baseRate) {
        Object.keys(hourMultipliers).forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                const calculatedValue = Math.round((baseRate * hourMultipliers[fieldId]) * 100) / 100;
                field.value = calculatedValue.toFixed(2);
                field.classList.add('zero-value-updated');
                setTimeout(() => field.classList.remove('zero-value-updated'), 1000);
                validateNumericPrice(field);
            }
        });
    }
    
    function clearEditHourlyFields(type) {
        const sellFields = [
            'hourly_price', 'two_hour_price', 'four_hour_price',
            'six_hour_price', 'eight_hour_price', 'ten_hour_price', 'twelve_hour_price'
        ];
        const costFields = [
            'hourly_cost_price', 'two_hour_cost_price', 'four_hour_cost_price',
            'six_hour_cost_price', 'eight_hour_cost_price', 'ten_hour_cost_price', 'twelve_hour_cost_price'
        ];
        const fields = type === 'cost' ? costFields : sellFields;
        
        fields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.value = '';
                field.classList.remove('is-valid', 'is-invalid', 'zero-value-updated');
                const messageElement = document.getElementById(`${fieldId}-validation-message`);
                if (messageElement) messageElement.innerHTML = '';
            }
        });
    }

    // Removed the auto-validation on page load so validation only happens when user interacts with fields

    // Keep App Password blank on load (no previous password shown)
    window.addEventListener('load', function() {
        const passwordField = document.getElementById('app_password');
        if (passwordField) {
            passwordField.value = '';
            setTimeout(function() {
                const pf = document.getElementById('app_password');
                if (pf) pf.value = '';
            }, 250);
        }
    });

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

    window.applyGuideProfitToSells = function(force) {
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
    };

    document.querySelectorAll('.js-guide-sell').forEach(function (sellEl) {
        sellEl.addEventListener('input', function () {
            sellEl.dataset.userEdited = '1';
        });
    });
    document.querySelectorAll('.js-guide-profit-type, .js-guide-profit-amount').forEach(function (el) {
        el.addEventListener('input', function () { applyGuideProfitToSells(true); });
        el.addEventListener('change', function () { applyGuideProfitToSells(true); });
    });
    </script>

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