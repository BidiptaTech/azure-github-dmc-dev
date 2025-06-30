@extends('layouts.layout')
@section('title', 'Edit Agent')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .image-preview {
        margin-top: 10px;
    }
    .image-container {
        position: relative;
        display: inline-block;
    }
    .remove-btn {
        position: absolute;
        top: 2px;
        right: 2px;
        background-color: rgba(255, 0, 0, 0.8);
        color: white !important;
        border: none;
        border-radius: 50%;
        font-size: 14px;
        width: 20px;
        height: 20px;
        cursor: pointer;
        line-height: 20px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .remove-btn:hover {
        background-color: rgba(255, 0, 0, 1);
    }
    
    /* Updated styles for new image preview components */
    .image-preview-container {
        margin-top: 10px;
    }
    
    .image-preview-wrapper {
        position: relative;
        display: inline-block;
        margin: 5px;
    }
    
    .delete-image-btn {
        position: absolute;
        top: 2px;
        right: 2px;
        background-color: rgba(255, 0, 0, 0.8);
        color: white !important;
        border: none;
        border-radius: 50%;
        font-size: 14px;
        width: 20px;
        height: 20px;
        cursor: pointer;
        line-height: 18px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }
    
    .delete-image-btn:hover {
        background-color: rgba(255, 0, 0, 1);
    }
</style>
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Edit Agent
                <a href="{{ route('agents.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>

            <form action="{{ route('agents.update', $agent->agent_id) }}" method="POST" enctype="multipart/form-data" class="card-body">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="company_name" class="form-label"><strong>Agency Company</strong><span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('company_name') is-invalid @enderror" name="company_name" value="{{ $agent->company_name }}" placeholder="Enter Company Name" required>
                        @error('name')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label"><strong>Salutation</strong><span class="text-danger">*</span></label>
                        <select class="form-control" name="salutation" required>
                            <option value="Mr" {{ $agent->salutation == 'Mr' ? 'selected' : '' }}>Mr.</option>
                            <option value="Mrs" {{ $agent->salutation == 'Mrs' ? 'selected' : '' }}>Mrs.</option>
                            <option value="Miss" {{ $agent->salutation == 'Miss' ? 'selected' : '' }}>Miss</option>
                            <option value="Dear" {{ $agent->salutation == 'Dear' ? 'selected' : '' }}>Dear</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Name</strong><span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="{{ $agent->name }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Email Address</strong><span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="email" name="email" value="{{ $agent->email }}" required oninput="validateEmail(this)">
                        <small class="validation-message text-danger" id="email-validation-message"></small>
                    </div>
                    
                </div>

                <div class="row">
                    <!-- <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Sales Manager (DMC)</strong><span class="text-danger">*</span></label>
                        <select class="form-control" id="sales_mg" name="sales_mg">
                            <option value="">Select Sales Manager</option>
                            @foreach($sales_mg as $manager)
                                <option value="{{ $manager->userId }}" {{ $agent->sales_manager_dmc == $manager->userId ? 'selected' : '' }}>{{ $manager->name }}</option>
                            @endforeach
                        </select>
                    </div> -->

                    {{-- NEW SECTIONS: Agent Country, Agent City, Country Code --}}
                    <div class="col-md-3 mb-3" id="user_coun">
                        <div class="mb-3">
                            <label for="user_country" class="form-label">
                                <strong>Agent Country</strong>
                                <span style="color: red; font-weight: bold;">*</span>
                            </label>
                            <select class="form-select" id="user_country" name="user_country" required>
                                <option selected disabled value>Choose a country...</option>
                                @foreach($cityCountry as $c)
                                    <option value="{{ $c->name }}" {{ $agent->user_country == $c->name ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('user_country')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 mb-3" id="city_name">
                        <div class="mb-3">
                            <label for="city" class="form-label">
                                <strong>Agent City</strong>
                                <span style="color: red; font-weight: bold;">*</span>
                            </label>
                            <select class="form-select" id="city" name="city" required>
                                <option selected disabled value>Select country first...</option>
                                {{-- Cities will be populated by AJAX based on selected country --}}
                            </select>
                            @error('city')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="inputCountryCode" class="form-label"><strong>Country Code</strong><span
                                style="color: red; font-weight: bold;">*</span></label>
                        <select class="form-select" id="inputCountryCode" name="code" required>
                            <option selected disabled value>Choose...</option>
                            @foreach($countryCodes as $key => $value)
                                <option value="{{ $key }}" {{ $agent->code == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                        @error('code')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Phone No</strong><span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ $agent->phone }}" required oninput="validatePhoneNumber(this)">
                        <small class="validation-message text-danger" id="phone-validation-message"></small>
                    </div>

                    {{-- <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Country</strong><span class="text-danger">*</span></label>
                        <select class="form-control" id="country" name="country" required>
                            @foreach($authUserCountries as $countryName)
                                <option value="{{ $countryName }}" {{ $agent->country == $countryName ? 'selected' : '' }}>{{ $countryName }}</option>
                            @endforeach
                        </select>
                        @error('country')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div> --}}

                    {{-- <pre>{{ var_dump($agent->country) }}</pre> --}}

                    @php
                        $agentCountries = array_map('trim', explode(',', $agent->country ?? ''));
                    @endphp
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Country</strong><span class="text-danger">*</span></label>
                        <select class="form-control select2" id="country" name="country[]" multiple="multiple" required>
                            @foreach($authUserCountries as $countryName)
                                <option value="{{ trim($countryName) }}" 
                                    {{ in_array(trim($countryName), $agentCountries) ? 'selected' : '' }}>
                                    {{ $countryName }}
                                </option>
                            @endforeach
                        </select>
                        @error('country')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>ID Card</strong><span class="text-danger">*</span></label>
                        <select class="form-control" id="id_card" name="id_card">
                            @foreach($card as $cardType)
                                <option value="{{ $cardType->card_type }}" {{ $agent->id_cards == $cardType->card_type ? 'selected' : '' }}>{{ $cardType->card_type }}</option>
                            @endforeach
                        </select>
                        @error('id_card')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Card Number</strong><span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="card_number" value="{{ $agent->id_number }}" required>
                    </div>

                    <!-- Image Upload with Preview & Remove - UPDATED STYLING -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Agency Logo</strong></label>
                        <input type="file" class="form-control" name="agent_image" id="agent_image_input">
                        <div id="agent_image_preview">
                        @if($agent->agent_image)
                                @php
                                    $agentImageUrl = filter_var($agent->agent_image, FILTER_VALIDATE_URL) ? $agent->agent_image : asset('storage/' . $agent->agent_image);
                                @endphp
                                <div class="image-preview-container d-flex flex-wrap gap-2 mt-2">
                                    <div class="image-preview-wrapper position-relative">
                                        <img src="{{ $agentImageUrl }}" alt="Agent Image"
                                            style="max-width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                                        <button
                                            type="button"
                                            class="delete-image-btn position-absolute top-0 end-0 btn btn-sm btn-danger"
                                            data-image="{{ $agent->agent_image }}"
                                            style="width: 20px; height: 20px; line-height: 18px; padding: 0; text-align: center; font-size: 14px; z-index: 1;">
                                            &times;
                                        </button>
                                    </div>
                                </div>
                        @endif
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>ID Proof (Image)</strong></label>
                        <input type="file" class="form-control" name="image" id="id_proof_input">
                        <div id="id_proof_preview">
                        @if($agent->image)
                                @php
                                    $idProofImageUrl = filter_var($agent->image, FILTER_VALIDATE_URL) ? $agent->image : asset('storage/' . $agent->image);
                                @endphp
                                <div class="image-preview-container d-flex flex-wrap gap-2 mt-2">
                                    <div class="image-preview-wrapper position-relative">
                                        <img src="{{ $idProofImageUrl }}" alt="ID Proof Image"
                                            style="max-width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                                        <button
                                            type="button"
                                            class="delete-image-btn position-absolute top-0 end-0 btn btn-sm btn-danger"
                                            data-image="{{ $agent->image }}"
                                            style="width: 20px; height: 20px; line-height: 18px; padding: 0; text-align: center; font-size: 14px; z-index: 1;">
                                            &times;
                                        </button>
                                    </div>
                                </div>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                            <label class="form-label"><strong>Password</strong> <small>(Leave empty to keep current password)</small></label>
                        <div class="input-group">
                                <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Choose Password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fa fa-eye-slash" id="toggleIcon"></i>
                            </button>
                            </div>
                            @error('password')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('agents.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    const APP_URL = @json(config('app.url')); // Get APP_URL from Laravel
</script>

<script>
    $(document).ready(function() {
        $('#country').select2({
            placeholder: "Select countries",
            allowClear: true
        });
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

    function validatePhoneNumber(input) {
        // Force numeric input by immediately replacing non-numeric characters
        input.value = input.value.replace(/[^0-9+]/g, '');
        
        const phoneRegex = /^[+]?[0-9]{8,15}$/;
        const value = input.value.trim();
        
        if (value === '') {
            showValidationMessage(input, false, 'Phone number is required');
        } else if (!phoneRegex.test(value)) {
            showValidationMessage(input, false, `
                Please enter a valid phone number:
                <ul class="mt-1 mb-0">
                    <li>Must contain 8-15 digits</li>
                    <li>Only numbers are allowed (0-9)</li>
                    <li>Optional + at the beginning for country code</li>
                    <li>No spaces or other special characters</li>
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

    // Password show/hide toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (togglePassword && password && toggleIcon) {
            togglePassword.addEventListener('click', function () {
                // Toggle the password visibility
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                // Toggle the eye icon
                if (type === 'password') {
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                } else {
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                }
            });
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Agent Image delete functionality
        const agentImageContainer = document.querySelector('#agent_image_preview .image-preview-container');
        if (agentImageContainer) {
            agentImageContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('delete-image-btn')) {
                    e.preventDefault();
                    e.stopPropagation();
                    const imageWrapper = e.target.closest('.image-preview-wrapper');
                    if (imageWrapper) {
                        // Add a hidden input to track deleted image
                        const form = document.querySelector('form');
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'remove_agent_image';
                        hiddenInput.value = '1';
                        form.appendChild(hiddenInput);
                        
                        // Remove the image preview
                        imageWrapper.remove();
                    }
                }
            });
        }

        // ID Proof Image delete functionality
        const idProofContainer = document.querySelector('#id_proof_preview .image-preview-container');
        if (idProofContainer) {
            idProofContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('delete-image-btn')) {
                    e.preventDefault();
                    e.stopPropagation();
                    const imageWrapper = e.target.closest('.image-preview-wrapper');
                    if (imageWrapper) {
                        // Add a hidden input to track deleted image
                        const form = document.querySelector('form');
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'remove_id_proof_image';
                        hiddenInput.value = '1';
                        form.appendChild(hiddenInput);
                        
                        // Remove the image preview
                        imageWrapper.remove();
                    }
                }
            });
        }
    });

    // Updated previewImage function to match restaurant template style
    function previewImage(event, previewDivId) {
        let reader = new FileReader();
        reader.onload = function(e) {
            const previewContainer = document.getElementById(previewDivId);
            previewContainer.innerHTML = `
                <div class="image-preview-container d-flex flex-wrap gap-2 mt-2">
                    <div class="image-preview-wrapper position-relative">
                        <img src="${e.target.result}" alt="Image Preview"
                            style="max-width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                        <button
                            type="button"
                            class="delete-image-btn position-absolute top-0 end-0 btn btn-sm btn-danger"
                            style="width: 20px; height: 20px; line-height: 18px; padding: 0; text-align: center; font-size: 14px; z-index: 1;">
                            &times;
                        </button>
                    </div>
                </div>`;
                
            // Add event listener to the new delete button
            const deleteBtn = previewContainer.querySelector('.delete-image-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    previewContainer.innerHTML = '';
                    document.getElementById(previewDivId.replace('_preview', '_input')).value = '';
                });
            }
        }
        reader.readAsDataURL(event.target.files[0]);
    }

    // Keep existing event listeners for file input changes
    document.getElementById('agent_image_input').addEventListener('change', function(event) {
        previewImage(event, 'agent_image_preview');
    });

    document.getElementById('id_proof_input').addEventListener('change', function(event) {
        previewImage(event, 'id_proof_preview');
    });
</script>

{{-- NEW AJAX SCRIPT FOR COUNTRY/CITY SELECTION --}}
<script>
    $(document).ready(function() {
        // Pre-populate city dropdown if agent has existing data
        @if($agent->user_country && $agent->city)
            // Fetch cities for the agent's current country and select the current city
            $.ajax({
                url: "{{ route('fetch-cities-by-country') }}",
                type: "GET",
                data: { country: "{{ $agent->user_country }}" },
                dataType: 'json',
                success: function(response) {
                    $('#city').html('<option selected disabled value>Select city...</option>');
                    
                    if (response.cities && response.cities.length > 0) {
                        $.each(response.cities, function(key, city) {
                            let selected = (city.name === "{{ $agent->city }}") ? 'selected' : '';
                            $('#city').append('<option value="' + city.name + '" ' + selected + '>' + city.name + '</option>');
                        });
                    } else {
                        $('#city').append('<option disabled>No cities found</option>');
                    }
                },
                error: function() {
                    $('#city').html('<option disabled value>Error loading cities</option>');
                }
            });
        @endif

        // Country selection change handler
        $('#user_country').on('change', function() {
            const selectedCountry = $(this).val();
            if (selectedCountry) {
                // Show loading state for cities
                $('#city').html('<option>Loading cities...</option>');
                
                // Fetch cities for the selected country
                $.ajax({
                    url: "{{ route('fetch-cities-by-country') }}",
                    type: "GET",
                    data: { country: selectedCountry },
                    dataType: 'json',
                    success: function(response) {
                        // Reset and populate cities dropdown
                        $('#city').html('<option selected disabled value>Select city...</option>');
                        
                        if (response.cities && response.cities.length > 0) {
                            $.each(response.cities, function(key, city) {
                                $('#city').append('<option value="' + city.name + '">' + city.name + '</option>');
                            });
                        } else {
                            $('#city').append('<option disabled>No cities found</option>');
                        }
                    },
                    error: function() {
                        $('#city').html('<option disabled value>Error loading cities</option>');
                    }
                });
                
                // Fetch country code for the selected country
                $.ajax({
                    url: "{{ route('fetch-country-code') }}",
                    type: "GET",
                    data: { country: selectedCountry },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.country_code) {
                            // Find and select the country code option
                            $('#inputCountryCode').val(response.country_code).trigger('change');
                        }
                    }
                });
            } else {
                // Reset cities dropdown if no country selected
                $('#city').html('<option selected disabled value>Select country first...</option>');
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('#sales_mg').on('change', function () {
            let salesManagerId = $(this).val();
            
            if (salesManagerId) {
                $.ajax({
                    url: `${APP_URL}/get-sales-manager-details/${salesManagerId}`,
                    type: 'GET',
                    success: function (response) {
                        if (response.success) {
                            // ✅ Set Country Name instead of ID
                            $('#country').val(response.country.name);

                            // ✅ Populate ID Card dropdown
                            let idCardDropdown = $('#id_card');
                            idCardDropdown.empty().append('<option value="">Select One</option>');

                            let selectedCardType = response.selected_card_type || '';

                            if (response.id_cards && response.id_cards.length > 0) {
                                $.each(response.id_cards, function (key, card) {
                                    let isSelected = (card.card_type === selectedCardType) ? 'selected' : '';
                                    idCardDropdown.append('<option value="' + card.card_type + '" ' + isSelected + '>' + card.card_type + '</option>');
                                });
                            } else {
                                idCardDropdown.append('<option value="">No ID Cards Available</option>');
                            }
                        } else {
                            alert(response.message || 'No data found for this sales manager.');
                            resetFields();
                        }
                    },
                    error: function () {
                        alert('Failed to fetch sales manager data.');
                        resetFields();
                    }
                });
            } else {
                resetFields();
            }
        });

        function resetFields() {
            $('#country').val('');
            $('#id_card').empty().append('<option value="">Select One</option>');
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const removeButtons = document.querySelectorAll('.remove-btn');
        removeButtons.forEach(button => {
            button.style.backgroundColor = 'rgba(255, 0, 0, 0.8)';
            button.style.color = 'white';
            button.style.border = 'none';
            button.style.borderRadius = '50%';
            button.style.fontSize = '14px';
            button.style.width = '20px';
            button.style.height = '20px';
            button.style.cursor = 'pointer';
            button.style.lineHeight = '20px';
            button.style.textAlign = 'center';
            button.style.display = 'flex';
            button.style.alignItems = 'center';
            button.style.justifyContent = 'center';

            button.addEventListener('mouseover', function() {
                button.style.backgroundColor = 'rgba(255, 0, 0, 1)';
            });

            button.addEventListener('mouseout', function() {
                button.style.backgroundColor = 'rgba(255, 0, 0, 0.8)';
            });
        });
    });
</script>

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

    /* Password toggle button style */
    #togglePassword {
        border-top-right-radius: 4px;
        border-bottom-right-radius: 4px;
        border-color: #ced4da;
        background-color: #f8f9fa;
    }

    #togglePassword:hover {
        background-color: #e9ecef;
    }

    /* Existing styles for image container and remove button */
    .image-container {
        position: relative;
        display: inline-block;
    }
    .remove-btn {
        position: absolute;
        top: 2px;
        right: 2px;
        background-color: rgba(255, 0, 0, 0.8);
        color: white;
        border: none;
        border-radius: 50%;
        font-size: 14px;
        width: 20px;
        height: 20px;
        cursor: pointer;
        line-height: 20px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .remove-btn:hover {
        background-color: rgba(255, 0, 0, 1);
    }
</style>
@endsection
