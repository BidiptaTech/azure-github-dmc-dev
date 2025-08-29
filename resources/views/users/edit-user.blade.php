@extends('layouts.layout')
@section('title', 'Edit User')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Edit User
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>

            <form action="{{ route('users.update', $users->userId) }}" method="POST" enctype="multipart/form-data" class="card-body">
                @csrf
                @method('PATCH')
                
                <!-- Display validation errors -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <!-- Debug information (temporary) -->
                @if (session('debug'))
                    <div class="alert alert-info">
                        <strong>Debug Info:</strong>
                        <ul class="mb-0">
                            <li>User ID: {{ session('debug')['user_id'] ?? 'N/A' }}</li>
                            <li>Role ID: {{ session('debug')['role_id'] ?? 'N/A' }}</li>
                            <li>Updated Fields: {{ implode(', ', session('debug')['updated_fields'] ?? []) }}</li>
                        </ul>
                    </div>
                @endif
                
                <!-- Hidden field to preserve role_id -->
                <input type="hidden" name="role" value="{{ $users->role_id }}">

                <div class="row">
                    <!-- Salutation -->
                    <div class="col-md-3 mb-3">
                        <label for="salutation" class="form-label"><strong>Salutation</strong><span class="text-danger">*</span></label>
                        <select class="form-control" name="salutation" required>
                            <option value="">Select Salutation</option>
                            <option value="Mr" {{ $users->salutation == 'Mr' ? 'selected' : '' }}>Mr.</option>
                            <option value="Mrs" {{ $users->salutation == 'Mrs' ? 'selected' : '' }}>Mrs.</option>
                            <option value="Miss" {{ $users->salutation == 'Miss' ? 'selected' : '' }}>Miss</option>
                            <option value="Dear" {{ $users->salutation == 'Dear' ? 'selected' : '' }}>Dear</option>
                        </select>
                        @error('salutation')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    <!-- Name -->
                    <div class="col-md-3 mb-3">
                        <label for="yourname" class="form-label"><strong>Name</strong><span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="yourname" value="{{ $users->name }}" required>
                        @error('yourname')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    <!-- Role (Display Only) -->
                    <div class="col-md-4 mb-3">
                        <label for="current_role" class="form-label"><strong>Role</strong></label>
                        <input type="text" class="form-control" value="{{ $users->role->name ?? 'No Role' }}" readonly>
                        <small class="text-muted">Role cannot be changed</small>
                    </div>

                    @if(auth()->user()->user_type == 1)
                        <!-- Master DMC Select -->
                        <div class="col-md-2" id="inputRoleContainer">
                            <div class="mb-3">
                                <label for="master" class="form-label"><strong>Master DMC</strong><span class="text-danger">*</span></label>
                                @if($users->role_id == 11 && $users->master_dmc_id)
                                    <!-- Read-only for existing DMC users -->
                                    <input type="text" class="form-control" value="{{ $users->masterDmc->name ?? 'No Master DMC' }}" readonly style="background-color: #f8f9fa;">
                                    <input type="hidden" name="master_dmc" value="{{ $users->master_dmc_id }}">
                                    <small class="text-muted">Master DMC cannot be changed for existing DMC</small>
                                @else
                                    <!-- Editable for new users or non-DMC users -->
                                    <select class="form-select" id="master" name="master_dmc">
                                        <option disabled value>Choose...</option>
                                        @foreach($master_dmc as $mdmc)
                                            <option value="{{ $mdmc->userId }}" {{ $users->master_dmc_id == $mdmc->userId ? 'selected' : '' }}>
                                                {{ $mdmc->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                                @error('master_dmc')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Dynamic Sections -->
                <div class="row">
                    <!-- Multiple Countries for role 10 (Master DMC) -->
                    <div class="col-md-4" id="country_names" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label"><strong>Country Names</strong><span class="text-danger">*</span></label>
                            <select class="form-select select2" name="country_names[]" multiple>
                                @foreach($country as $c)
                                    <option value="{{ $c->name }}" 
                                        {{ in_array($c->name, explode(',', $users->country)) ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Single Country for role 11 (DMC) -->
                    <div class="col-md-4" id="country_name" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label"><strong>Country Name</strong><span class="text-danger">*</span></label>
                            <select class="form-select" name="country_name">
                                @foreach($country as $c)
                                    <option value="{{ $c->name }}" {{ $users->country == $c->name ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- DMC Select -->
                    <div class="col-md-4" id="inputDmcContainer" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label"><strong>DMC</strong><span class="text-danger">*</span></label>
                            <select class="form-select" name="dmc">
                                <option disabled value>Choose...</option>
                                @foreach($dmcs as $dmc)
                                    <option value="{{ $dmc->userId }}" {{ $users->dmcId == $dmc->userId ? 'selected' : '' }}>
                                        {{ $dmc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Sales Manager Admin -->
                    <div class="col-md-4" id="inputSalespersonContainerAdmin" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label"><strong>Sales Manager (Admin)</strong><span class="text-danger">*</span></label>
                            <select class="form-select" name="salemg_admin">
                                <option disabled value>Choose...</option>
                                @foreach($adminSalesManager as $manager)
                                    <option value="{{ $manager->userId }}" {{ $users->sales_manager_admin == $manager->userId ? 'selected' : '' }}>
                                        {{ $manager->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Logo and Company Name for roles 10 and 11 -->
                <div class="row">
                    <!-- Master/DMC Logo -->
                    <div class="col-md-4" id="master_logo" style="display: none;">
                        <div class="mb-3">
                            <label for="master_logo" class="form-label"><strong>Logo</strong></label>
                            <input type="file" class="form-control" id="master_logo" name="master_logo">
                            @if($users->logo)
                                <div class="mt-2">
                                    <small class="text-muted">Current logo:</small>
                                    <img src="{{ $users->logo }}" alt="Current Logo" style="max-height: 50px; max-width: 100px;">
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Company Name -->
                    <div class="col-md-4" id="company_name" style="display: none;">
                        <div class="mb-3">
                            <label for="company_name" class="form-label"><strong>Company Name</strong><span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="company_name" name="company_name" value="{{ $users->company_name }}" placeholder="Enter Company Name">
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label"><strong>User Country</strong><span class="text-danger">*</span></label>
                            <select class="form-select" id="user_country" name="user_country">
                                <option disabled value>Choose a country...</option>
                                @foreach($country as $c)
                                    <option value="{{ $c->name }}" {{ $users->user_country == $c->name ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label"><strong>City Name</strong><span class="text-danger">*</span></label>
                            <select class="form-select" id="city" name="city">
                                <option disabled value>Loading cities...</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label"><strong>Address</strong><span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="address" name="address" value="{{ $users->address }}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Country Code -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><strong>Country Code</strong><span class="text-danger">*</span></label>
                        <select class="form-select" id="inputCountryCode" name="code" required>
                            <option disabled value>Choose...</option>
                            @foreach($countryCodes as $key => $value)
                                <option value="{{ $key }}" {{ $users->country_code == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Phone -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><strong>Phone No</strong><span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="phone" name="phone" value="{{ $users->phone }}" required oninput="">
                        <small class="validation-message text-danger" id="phone-validation-message"></small>
                    </div>

                    <!-- Email (Read-only) -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><strong>Email Address</strong></label>
                        <input type="text" class="form-control" value="{{ $users->email }}" readonly style="background-color: #f8f9fa;">
                        <input type="hidden" name="email" value="{{ $users->email }}">
                        <small class="text-muted">Email cannot be changed</small>
                    </div>
                </div>

                <!-- Password -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><strong>Password</strong> <small>(Leave empty to keep current password)</small></label>
                        <input type="password" class="form-control" name="password">
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="reset" class="btn btn-secondary">Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<script>
    $(document).ready(function() {
        // Initialize select2
        $('.select2').select2({
            placeholder: "Choose countries...",
            allowClear: true
        });

        // Add form submission validation
        $('form').on('submit', function(e) {
            let missingFields = [];
            
            // Check required fields based on role
            const currentUserRole = {{ $users->role_id }};
            
            // Always check basic required fields
            if (!$('input[name="yourname"]').val()) missingFields.push('Name');
            if (!$('select[name="salutation"]').val()) missingFields.push('Salutation');
            if (!$('input[name="phone"]').val()) missingFields.push('Phone');
            if (!$('select[name="user_country"]').val()) missingFields.push('User Country');
            if (!$('select[name="city"]').val()) missingFields.push('City');
            if (!$('input[name="address"]').val()) missingFields.push('Address');
            if (!$('select[name="code"]').val()) missingFields.push('Country Code');
            
            // Check role-specific fields
            if (currentUserRole === 10 || currentUserRole === 19) {
                if (!$('select[name="country_names[]"]').val() || $('select[name="country_names[]"]').val().length === 0) {
                    missingFields.push('Country Names');
                }
                if (!$('input[name="company_name"]').val()) missingFields.push('Company Name');
            } else if (currentUserRole === 11 || currentUserRole === 20) {
                if (!$('input[name="company_name"]').val()) missingFields.push('Company Name');
                // Only check master_dmc if it's visible and not hidden
                const masterDmcSelect = $('select[name="master_dmc"]');
                if (masterDmcSelect.is(':visible') && !masterDmcSelect.val()) {
                    missingFields.push('Master DMC');
                }
            }
            
            if (missingFields.length > 0) {
                e.preventDefault();
                alert('Please fill in the following required fields: ' + missingFields.join(', '));
                return false;
            }
            
            console.log('Form validation passed, submitting...');
        });

        // Get current user's role_id and show appropriate fields
        var currentUserRole = {{ $users->role_id }};
        
        // Initial field display based on current role
        updateFields();

        // Master DMC change handler
        $('#master').on('change', function() {
            const masterDmcId = $(this).val();
            if (masterDmcId) {
                // Fetch countries for selected Master DMC
                $.ajax({
                    url: '/admin/get-countries-by-master-dmc',
                    type: 'POST',
                    data: {
                        master_dmc_id: masterDmcId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        // Update country select options
                        const countrySelect = $('select[name="country_names[]"]');
                        countrySelect.empty();
                        
                        response.countries.forEach(function(country) {
                            countrySelect.append(new Option(country.name, country.name, false, false));
                        });
                        
                        // Trigger select2 update
                        countrySelect.trigger('change');
                    }
                });

                // Fetch sales managers for selected Master DMC
                $.ajax({
                    url: '/admin/get-sales-managers-by-master-dmc',
                    type: 'POST',
                    data: {
                        master_dmc_id: masterDmcId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        // Update sales manager select options
                        const salesManagerSelect = $('select[name="salemg_admin"]');
                        salesManagerSelect.empty();
                        salesManagerSelect.append('<option disabled value>Choose...</option>');
                        
                        response.sales_managers.forEach(function(manager) {
                            salesManagerSelect.append(new Option(manager.name, manager.userId, false, false));
                        });
                    }
                });
            } else {
                // Reset selects if no Master DMC is selected
                $('select[name="country_names[]"]').empty();
                $('select[name="salemg_admin"]').empty().append('<option disabled value>Choose...</option>');
            }
        });

        // Country selection change handler
        $('#user_country').on('change', function() {
            const selectedCountry = $(this).val();
            
            if (selectedCountry) {
                // Show loading state for cities
                $('#city').html('<option>Loading cities...</option>');
                
                // Fetch cities for the selected country
                $.ajax({
                    url: "{{ route('get.cities.by.country') }}",
                    type: "GET",
                    data: { country: selectedCountry },
                    dataType: 'json',
                    success: function(response) {
                        // Reset and populate cities dropdown
                        $('#city').html('<option disabled value>Select city...</option>');
                        
                        if (response.cities && response.cities.length > 0) {
                            let userCity = "{{ $users->city }}";
                            $.each(response.cities, function(key, city) {
                                let selected = (city.name === userCity) ? 'selected' : '';
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
                
                // Fetch country code for the selected country
                $.ajax({
                    url: "{{ route('get.country.code') }}",
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
                $('#city').html('<option disabled value>Select country first...</option>');
            }
        });

        // Load cities and country code for initial country on page load
        const initialCountry = $('#user_country').val();
        if (initialCountry) {
            $('#user_country').trigger('change');
        }

        function updateFields() {
            const userRole = currentUserRole;
            const containers = {
                inputRoleContainer: $('#inputRoleContainer'),
                inputDmcContainer: $('#inputDmcContainer'),
                country_name: $('#country_name'),
                country_names: $('#country_names'),
                master_logo: $('#master_logo'),
                company_name: $('#company_name'),
                inputSalespersonContainerAdmin: $('#inputSalespersonContainerAdmin'),
                markuptypes: $('#markuptypes')
            };

            // Hide all containers
            Object.values(containers).forEach(container => container.hide());

            // Show relevant containers based on role
            if (userRole >= 5 && userRole <= 9) {
                containers.country_names.show();
            } else if (userRole === 10 || userRole === 19) {
                // Master DMC - show multiple countries, logo, and company name
                containers.country_names.show();
                containers.master_logo.show();
                containers.company_name.show();
            } else if (userRole === 11 || userRole === 20) {
                // DMC - show single country (if created by Master DMC), master DMC selection, logo, and company name
                if ({{ auth()->user()->role_id }} == 10 || {{ auth()->user()->role_id }} == 19) {
                    containers.country_name.show();
                }
                containers.inputRoleContainer.show();
                containers.master_logo.show();
                containers.company_name.show();
            } else if (userRole === 4) {
                containers.inputSalespersonContainerAdmin.show();
            } else if (userRole === 3) {
                containers.country_name.show();
            }
        }
    });

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
                    <i class="fas fa-exclamation-circle text-danger"></i> 
                    ${message}
                </div>`;
            inputElement.classList.remove('is-valid');
            inputElement.classList.add('is-invalid');
        }
    }

    function validatePhoneNumber(input) {
        const phoneNumber = input.value;
        const isValid = /^\d{10}$/.test(phoneNumber);
        showValidationMessage(input, isValid, 'Please enter a valid 10-digit phone number');
    }

    function validateEmail(input) {
        const email = input.value;
        const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        showValidationMessage(input, isValid, 'Please enter a valid email address');
    }

    // Add eye icon to password field if it exists
    document.addEventListener('DOMContentLoaded', function() {
        // Find password fields (they might have different IDs)
        const passwordFields = document.querySelectorAll('input[type="password"]');
        
        passwordFields.forEach(function(passwordField) {
            // Get the parent element
            const parentEl = passwordField.parentElement;
            
            // Create wrapper div with input-group class if not already an input-group
            if (!parentEl.classList.contains('input-group')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'input-group';
                
                // Replace the password field with the wrapper
                passwordField.parentNode.insertBefore(wrapper, passwordField);
                wrapper.appendChild(passwordField);
                
                // Add the toggle button
                const toggleButton = document.createElement('button');
                toggleButton.className = 'btn btn-outline-secondary';
                toggleButton.type = 'button';
                toggleButton.innerHTML = '<i class="fa fa-eye-slash"></i>';
                wrapper.appendChild(toggleButton);
                
                // Add click event to toggle button
                toggleButton.addEventListener('click', function() {
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);
                    
                    // Toggle the eye icon
                    const icon = toggleButton.querySelector('i');
                    if (type === 'password') {
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            }
        });
        
        // Initialize validation state for pre-populated fields
        const phoneInput = document.getElementById('phone');
        const emailInput = document.getElementById('email');
        
        if (phoneInput) {
            phoneInput.dataset.originalValue = phoneInput.value;
        }
        
        if (emailInput) {
            emailInput.dataset.originalValue = emailInput.value;
        }
    });

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

            /* Button styles for password toggle */
            .btn-outline-secondary {
                border-top-right-radius: 4px;
                border-bottom-right-radius: 4px;
                border-color: #ced4da;
                background-color: #f8f9fa;
            }

            .btn-outline-secondary:hover {
                background-color: #e9ecef;
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