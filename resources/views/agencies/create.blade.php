@extends('layouts.layout')

@section('title', 'Create Agency')

@section('content')
<style>
    /* Modern Card Styling */
    .modern-card {
        border: none;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border-radius: 16px;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .modern-card:hover {
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
        transform: translateY(-3px);
    }

    /* Header Styling */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2.5rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        opacity: 0.3;
    }

    .page-header > * {
        position: relative;
        z-index: 1;
    }

    /* Enhanced Head Office Section Header */
    .section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem 2rem;
        border-radius: 12px 12px 0 0;
        margin: 0;
        font-weight: 600;
        position: relative;
        overflow: hidden;
    }

    .section-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%, transparent 75%, rgba(255,255,255,0.1) 75%, rgba(255,255,255,0.1)), 
                    linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%, transparent 75%, rgba(255,255,255,0.1) 75%, rgba(255,255,255,0.1));
        background-size: 20px 20px;
        background-position: 0 0, 10px 10px;
        opacity: 0.3;
    }

    .section-header > * {
        position: relative;
        z-index: 1;
    }

    .section-header i {
        font-size: 1.2rem;
        margin-right: 0.5rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .section-header .badge {
        background: rgba(255,255,255,0.2) !important;
        color: white !important;
        border: 1px solid rgba(255,255,255,0.3);
        backdrop-filter: blur(10px);
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: 20px;
    }

    .branch-header {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    /* Form Controls */
    .form-control, .form-select {
        border: 2px solid #e3e6f0;
        border-radius: 10px;
        padding: 0.875rem 1.125rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
        background: rgba(255,255,255,0.8);
        backdrop-filter: blur(5px);
    }

    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
        transform: translateY(-1px);
        background: white;
    }

    /* Labels */
    .form-label {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
    }

    .form-label i {
        margin-right: 0.5rem;
        font-size: 1rem;
    }

    /* Buttons */
    .btn-modern {
        border-radius: 10px;
        padding: 0.875rem 1.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        border: none;
        position: relative;
        overflow: hidden;
    }

    .btn-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .btn-modern:hover::before {
        left: 100%;
    }

    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    }

    .btn-primary.btn-modern {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .btn-success.btn-modern {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .btn-danger.btn-modern {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    }

    .btn-secondary.btn-modern {
        background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    }

    /* Add Branch Button */
    .add-branch-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 2px dashed #dee2e6;
        border-radius: 16px;
        padding: 2.5rem;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .add-branch-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 50% 50%, rgba(102, 126, 234, 0.05) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .add-branch-section:hover::before {
        opacity: 1;
    }

    .add-branch-section:hover {
        border-color: #667eea;
        background: linear-gradient(135deg, #667eea05 0%, #764ba205 100%);
        transform: translateY(-2px);
    }

    /* Instructions */
    .instruction-box {
        background: linear-gradient(135deg, #e8f5e8 0%, #f0fff0 100%);
        border-left: 4px solid #28a745;
        padding: 1.5rem 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.1);
    }

    .instruction-icon {
        color: #28a745;
        font-size: 1.3rem;
        margin-right: 0.75rem;
    }

    /* Branch Counter */
    .branch-counter {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.75rem 1.25rem;
        border-radius: 25px;
        font-size: 0.875rem;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    /* Select2 Enhanced Styling */
    .select2-container--default .select2-selection--single {
        border: 2px solid #e3e6f0;
        border-radius: 10px;
        height: 52px;
        padding: 0.5rem;
        background: rgba(255,255,255,0.8);
        backdrop-filter: blur(5px);
    }

    .select2-container--default .select2-selection--single:focus {
        border-color: #667eea;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        color: #2d3748;
        padding-left: 0.5rem;
    }

    .select2-dropdown {
        border: 2px solid #e3e6f0;
        border-radius: 10px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #667eea;
    }

    .select2-container .select2-selection--single {
        height: 100% !important;
        line-height: 100% !important;
        padding: 8px 12px;
    }
    .select2-container .select2-results__option {
        padding: 12px 10px;
    }

    /* Loading States */
    .loading-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 0.5rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Enhanced Card Body */
    .card-body {
        background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(248,249,250,0.9) 100%);
        backdrop-filter: blur(10px);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
            text-align: center;
        }
        
        .section-header {
            padding: 1rem 1.25rem;
            font-size: 0.9rem;
        }
        
        .btn-modern {
            width: 100%;
            margin-bottom: 0.5rem;
        }
        
        .add-branch-section {
            padding: 1.5rem;
        }
    }

    /* Validation Styling */
    .is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
    }

    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        font-weight: 500;
    }

    /* Notification Styling */
    .notification {
        border-radius: 10px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        backdrop-filter: blur(10px);
    }
</style>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2">
                        <i class="ri-building-add-line me-2"></i>
                        Create New Agency
                    </h2>
                    <p class="mb-0 opacity-90">Set up a new agency with head office and branch locations</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="{{ route('agencies.index') }}" class="btn btn-light btn-modern">
                        <i class="ri-arrow-left-line me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="instruction-box">
            <div class="d-flex align-items-start">
                <i class="ri-information-line instruction-icon"></i>
                <div>
                    <h6 class="mb-2 text-success">How to create an agency:</h6>
                    <ul class="mb-0 small">
                        <li><strong>Step 1:</strong> Fill in the head office information below (marked with "Head Office" badge)</li>
                        <li><strong>Step 2:</strong> Select country first, then city will auto-populate with search functionality</li>
                        <li><strong>Step 3:</strong> Click "Add Branch" to add additional branch offices (optional)</li>
                        <li><strong>Step 4:</strong> Each branch requires all fields except agency name</li>
                        <li><strong>Step 5:</strong> Review and submit to create the agency</li>
                    </ul>
                </div>
            </div>
        </div>

        <form action="{{ route('agencies.store') }}" method="POST" id="agencyForm">
            @csrf
            
            <!-- Head Office Section -->
            <div class="modern-card mb-4">
                <div class="section-header d-flex align-items-center justify-content-between">
                    <div>
                        <i class="ri-building-line"></i>
                        Head Office Information
                    </div>
                    <span class="badge">Head Office</span>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <!-- Agency Name -->
                        <div class="col-lg-6 col-md-6 mb-3">
                            <label for="agency_name" class="form-label">
                                <i class="ri-building-2-line text-primary"></i>
                                Agency Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('agency_name') is-invalid @enderror" 
                                   id="agency_name" 
                                   name="agency_name" 
                                   value="{{ old('agency_name') }}" 
                                   placeholder="Enter agency name (e.g., Global Travel Solutions)"
                                   required>
                            @error('agency_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="col-lg-6 col-md-6 mb-3">
                            <label for="email" class="form-label">
                                <i class="ri-mail-line text-primary"></i>
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="Enter email (e.g., info@agency.com)"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="col-lg-6 col-md-6 mb-3">
                            <label for="phone" class="form-label">
                                <i class="ri-phone-line text-primary"></i>
                                Phone Number <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone') }}" 
                                   placeholder="Enter phone number (e.g., +1234567890)"
                                   required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Country -->
                        <div class="col-lg-6 col-md-6 mb-3">
                            <label for="country" class="form-label">
                                <i class="ri-earth-line text-primary"></i>
                                Country <span class="text-danger">*</span>
                            </label>
                            <select class="form-select select2 @error('country') is-invalid @enderror" 
                                    id="country" 
                                    name="country" 
                                    required>
                                <option value="">Search and select country...</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->name }}" 
                                            {{ old('country') == $country->name ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- City -->
                        <div class="col-lg-6 col-md-6 mb-3">
                            <label for="city" class="form-label">
                                <i class="ri-map-pin-line text-primary"></i>
                                City <span class="text-danger">*</span>
                            </label>
                            <select class="form-select select2 @error('city') is-invalid @enderror" 
                                    id="city" 
                                    name="city" 
                                    required>
                                <option value="">Select country first...</option>
                            </select>
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Postal Code -->
                        <div class="col-lg-6 col-md-6 mb-3">
                            <label for="postal_code" class="form-label">
                                <i class="ri-map-2-line text-primary"></i>
                                Postal Code
                            </label>
                            <input type="text" 
                                   class="form-control @error('postal_code') is-invalid @enderror" 
                                   id="postal_code" 
                                   name="postal_code" 
                                   value="{{ old('postal_code') }}" 
                                   placeholder="Enter postal code (e.g., 10001)">
                            @error('postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Address -->
                        <div class="col-12 mb-3">
                            <label for="address" class="form-label">
                                <i class="ri-home-line text-primary"></i>
                                Complete Address <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" 
                                      name="address" 
                                      rows="3" 
                                      placeholder="Enter complete address with street, area, landmarks..."
                                      required>{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Branch Sections Container -->
            <div id="branchesContainer">
                <!-- Branch sections will be added here dynamically -->
            </div>

            <!-- Add Branch Button -->
            <div class="add-branch-section mb-4">
                <div class="mb-3">
                    <i class="ri-add-circle-line" style="font-size: 3rem; color: #667eea;"></i>
                </div>
                <h5 class="mb-2">Add Branch Office</h5>
                <p class="text-muted mb-3">Expand your agency network by adding branch locations</p>
                <button type="button" class="btn btn-success btn-modern" id="addBranchBtn">
                    <i class="ri-add-line me-1"></i> Add Branch Office
                </button>
                <div class="mt-3">
                    <span class="branch-counter" id="branchCounter">0 Branches Added</span>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="modern-card">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-1">Ready to create agency?</h6>
                            <small class="text-muted">Please review all information before submitting</small>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <button type="submit" class="btn btn-primary btn-modern me-2">
                                <i class="ri-save-line me-1"></i> Create Agency
                            </button>
                            <a href="{{ route('agencies.index') }}" class="btn btn-secondary btn-modern">
                                <i class="ri-close-line me-1"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Branch Template (Hidden) -->
<template id="branchTemplate">
    <div class="modern-card mb-4 branch-section">
        <div class="section-header branch-header d-flex align-items-center justify-content-between">
            <div>
                <i class="ri-building-2-line"></i>
                Branch Office Information
            </div>
            <div>
                <span class="badge me-2">Branch</span>
                <button type="button" class="btn btn-sm btn-danger btn-modern remove-branch-btn">
                    <i class="ri-delete-bin-line me-1"></i> Remove
                </button>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <!-- Email -->
                <div class="col-lg-6 col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-mail-line text-primary"></i>
                        Email Address <span class="text-danger">*</span>
                    </label>
                    <input type="email" 
                           class="form-control" 
                           name="branches[INDEX][email]" 
                           placeholder="Enter branch email address"
                           required>
                </div>

                <!-- Phone -->
                <div class="col-lg-6 col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-phone-line text-primary"></i>
                        Phone Number <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           class="form-control" 
                           name="branches[INDEX][phone]" 
                           placeholder="Enter branch phone number"
                           required>
                </div>

                <!-- Country -->
                <div class="col-lg-6 col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-earth-line text-primary"></i>
                        Country <span class="text-danger">*</span>
                    </label>
                    <select class="form-select select2 branch-country" 
                            name="branches[INDEX][country]" 
                            required>
                        <option value="">Search and select country...</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->name }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- City -->
                <div class="col-lg-6 col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-map-pin-line text-primary"></i>
                        City <span class="text-danger">*</span>
                    </label>
                    <select class="form-select select2 branch-city" 
                            name="branches[INDEX][city]" 
                            required>
                        <option value="">Select country first...</option>
                    </select>
                </div>

                <!-- Postal Code -->
                <div class="col-lg-6 col-md-6 mb-3">
                    <label class="form-label">
                        <i class="ri-map-2-line text-primary"></i>
                        Postal Code
                    </label>
                    <input type="text" 
                           class="form-control" 
                           name="branches[INDEX][postal_code]" 
                           placeholder="Enter postal code">
                </div>

                <!-- Address -->
                <div class="col-12 mb-3">
                    <label class="form-label">
                        <i class="ri-home-line text-primary"></i>
                        Complete Address <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" 
                              name="branches[INDEX][address]" 
                              rows="3" 
                              placeholder="Enter complete branch address with street, area, landmarks..."
                              required></textarea>
                </div>
            </div>
        </div>
    </div>
</template>
@endsection

@section('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    let branchIndex = 0;

    // Initialize Select2 for main form
    initializeSelect2('#country', 'Search for country...');
    initializeSelect2('#city', 'Search for city...');

    // Function to initialize Select2
    function initializeSelect2(selector, placeholder) {
        $(selector).select2({
            placeholder: placeholder,
            allowClear: true,
            width: '100%',
            theme: 'default'
        });
    }

    // Country change handler for head office
    $('#country').on('change', function() {
        const selectedCountry = $(this).val();
        console.log('Country selected:', selectedCountry); // Debug log
        loadCitiesForElement('#city', selectedCountry);
    });

    // Function to load cities for any element
    function loadCitiesForElement(citySelector, country) {
        console.log('Loading cities for:', country, 'into selector:', citySelector); // Debug log
        
        if (country) {
            // Show loading state
            $(citySelector).html('<option value="">Loading cities...</option>');
            $(citySelector).prop('disabled', true);
            
            // Ajax call to get cities
            $.ajax({
                url: "{{ route('agencies.getCitiesByCountry') }}",
                type: "GET",
                data: { country: country },
                dataType: 'json',
                beforeSend: function() {
                    console.log('Ajax request started for country:', country); // Debug log
                },
                success: function(response) {
                    console.log('Ajax response received:', response); // Debug log
                    
                    $(citySelector).prop('disabled', false);
                    $(citySelector).html('<option value="">Select city...</option>');
                    
                    if (response.success && response.cities && response.cities.length > 0) {
                        $.each(response.cities, function(key, city) {
                            $(citySelector).append('<option value="' + city.name + '">' + city.name + '</option>');
                        });
                        showNotification('Cities loaded successfully!', 'success');
                    } else {
                        $(citySelector).append('<option disabled>No cities found for this country</option>');
                        showNotification('No cities found for ' + country, 'warning');
                    }
                    
                    // Refresh Select2
                    $(citySelector).trigger('change');
                },
                error: function(xhr, status, error) {
                    console.error('Ajax error:', xhr, status, error); // Debug log
                    $(citySelector).prop('disabled', false);
                    $(citySelector).html('<option disabled>Error loading cities</option>');
                    showNotification('Error loading cities: ' + error, 'error');
                }
            });
        } else {
            $(citySelector).html('<option value="">Select country first...</option>');
            $(citySelector).prop('disabled', false);
            $(citySelector).trigger('change');
        }
    }

    // Add Branch functionality
    $('#addBranchBtn').on('click', function() {
        const template = document.getElementById('branchTemplate').content.cloneNode(true);
        
        // Update all INDEX placeholders with actual index
        $(template).find('input, select, textarea').each(function() {
            const name = $(this).attr('name');
            if (name) {
                $(this).attr('name', name.replace('INDEX', branchIndex));
            }
        });

        // Add to container
        $('#branchesContainer').append(template);
        
        // Initialize Select2 for new branch elements
        const branchContainer = $('#branchesContainer .branch-section').last();
        const countrySelect = branchContainer.find('.branch-country');
        const citySelect = branchContainer.find('.branch-city');
        
        initializeSelect2(countrySelect, 'Search for country...');
        initializeSelect2(citySelect, 'Search for city...');
        
        // Add change handler for branch country
        countrySelect.on('change', function() {
            const selectedCountry = $(this).val();
            const correspondingCitySelect = $(this).closest('.branch-section').find('.branch-city');
            loadCitiesForElement(correspondingCitySelect, selectedCountry);
        });
        
        branchIndex++;
        updateBranchCounter();
        
        // Smooth scroll to new branch
        $('html, body').animate({
            scrollTop: branchContainer.offset().top - 100
        }, 500);
        
        showNotification('Branch office section added successfully!', 'success');
    });

    // Remove Branch functionality
    $(document).on('click', '.remove-branch-btn', function() {
        $(this).closest('.branch-section').fadeOut(300, function() {
            $(this).remove();
            updateBranchCounter();
            showNotification('Branch office removed successfully!', 'info');
        });
    });

    // Update branch counter
    function updateBranchCounter() {
        const branchCount = $('.branch-section').length;
        $('#branchCounter').text(branchCount + ' Branch' + (branchCount !== 1 ? 'es' : '') + ' Added');
    }

    // Form validation
    $('#agencyForm').on('submit', function(e) {
        let isValid = true;
        let errors = [];
        
        // Clear previous validation states
        $('.is-invalid').removeClass('is-invalid');
        
        // Validate required fields
        $(this).find('input[required], select[required], textarea[required]').each(function() {
            if (!$(this).val() || !$(this).val().trim()) {
                $(this).addClass('is-invalid');
                isValid = false;
                
                const label = $(this).closest('.mb-3').find('label').text().replace('*', '').trim();
                errors.push(label);
            }
        });

        if (!isValid) {
            e.preventDefault();
            showNotification('Please fill in all required fields: ' + errors.slice(0, 3).join(', ') + (errors.length > 3 ? '...' : ''), 'error');
            
            // Scroll to first error
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 100
            }, 500);
        } else {
            // Show loading state
            $(this).find('button[type="submit"]').html('<span class="loading-spinner"></span>Creating Agency...').prop('disabled', true);
        }
    });

    // Remove validation error on input
    $(document).on('input change', 'input, select, textarea', function() {
        $(this).removeClass('is-invalid');
    });

    // Phone number formatting
    $(document).on('input', 'input[name="phone"], input[name*="[phone]"]', function() {
        let value = $(this).val().replace(/[^\d+]/g, '');
        $(this).val(value);
    });

    // Email validation
    $(document).on('blur', 'input[type="email"]', function() {
        const email = $(this).val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            $(this).addClass('is-invalid');
            showNotification('Please enter a valid email address', 'warning');
        }
    });

    // Enhanced notification function
    function showNotification(message, type = 'info') {
        const alertClass = type === 'error' ? 'alert-danger' : 
                          type === 'success' ? 'alert-success' : 
                          type === 'warning' ? 'alert-warning' : 'alert-info';
        
        const icon = type === 'error' ? 'ri-error-warning-line' : 
                     type === 'success' ? 'ri-check-line' : 
                     type === 'warning' ? 'ri-alert-line' : 'ri-information-line';
        
        const notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed notification" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px;">
                <i class="${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(notification);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            notification.alert('close');
        }, 5000);
    }

    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Test the Ajax route on page load (for debugging)
    console.log('Agency create page loaded. Testing Ajax route...');
});
</script>
@endsection 