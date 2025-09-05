@extends('layouts.layout')
@section('title', 'Add Agent')
@section('content')
<style>
   /* Adjust the height and padding of the multi-select container */
    .select2-container--default .select2-selection--multiple {
        min-height: 42px;
        padding: 6px 12px;
        border: 1px solid #111111;
        border-radius: 4px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
    }

    /* Style each selected item (tag) */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #007bff;
        border: none;
        color: rgb(9, 7, 7);
        padding: 5px 10px;
        margin: 2px 4px 2px 0;
        border-radius: 20px;
        font-size: 0.875rem;
    }

    /* Increase padding in dropdown options */
    .select2-container .select2-results__option {
        padding: 12px 10px;
    }

    /* Ensure proper height for single select elements */
    .select2-container .select2-selection--single {
        height: 100% !important;
        line-height: 100% !important;
        padding: 8px 12px;
    }

    .select2-container .select2-selection--single {
    height: 46px !important;
    padding: 6px 12px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 24px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
    right: 10px;
}


</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(auth()->user()->role_id == 11)
        <!-- Search Section - Only visible to DMC -->
        <div class="card mb-4">
            <h5 class="card-header">Search Existing Agent</h5>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select class="form-control select2" id="search_country" name="search_country">
                            <option value="">Select Country</option>
                            @foreach($cityCountry as $c)
                                <option value="{{ $c->name }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control select2" id="search_city" name="search_city">
                            <option value="">Select City</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="search_agent_name" placeholder="Agent Name">
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" id="search_agency_name" placeholder="Agency Name">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-primary w-100" id="searchAgentBtn">
                            <i class="mdi mdi-magnify"></i>
                        </button>
                    </div>
                </div>

                <!-- Search Results Table -->
                <div class="mt-4" id="searchResults" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Agent Name</th>
                                    <th>Agency Name</th>
                                    <th>Country</th>
                                    <th>City</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="searchResultsBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Rest of your form -->
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Add New Agent
                <a href="{{ route('agents.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form action="{{ route('agents.store') }}" method="POST" enctype="multipart/form-data" class="card-body">
                @csrf
                <input type="hidden" name="dmc_ids" id="dmc_ids" value="">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="company_name" class="form-label"><strong>Select Agency Company</strong><span class="text-danger">*</span></label>
                        <select class="form-control select2" id="agency_id" name="agency_id">
                            <option value="">Select Agency</option>
                            @foreach($agency as $a)
                                <option value="{{ $a->agency_id }}">{{ $a->agency_name }}</option>
                            @endforeach
                        </select>
                        @error('agency_id')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="salutation" class="form-label"><strong>Salutation</strong><span class="text-danger">*</span></label>
                        <select class="form-control" name="salutation">
                            <option value="">Select</option>
                            <option value="Mr" {{ old('salutation') == 'Mr' ? 'selected' : '' }}>Mr.</option>
                            <option value="Mrs" {{ old('salutation') == 'Mrs' ? 'selected' : '' }}>Mrs.</option>
                            <option value="Miss" {{ old('salutation') == 'Miss' ? 'selected' : '' }}>Miss</option>
                            <option value="Dear" {{ old('salutation') == 'Dear' ? 'selected' : '' }}>Dear</option>
                        </select>
                        @error('salutation')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="name" class="form-label"><strong>Name</strong><span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="Enter Name">
                        @error('name')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="email" class="form-label"><strong>Email Address</strong><span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Enter Email" oninput="validateEmail(this)">
                        <small class="validation-message text-danger" id="email-validation-message"></small>
                        @error('email')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                </div>
                <div class="row">
                    <!-- <div class="col-md-3 mb-3">
                        <label for="sales_mg" class="form-label"><strong>Sales Manager (DMC)</strong><span class="text-danger">*</span></label>
                        <select class="form-control" id="sales_mg" name="sales_mg">
                            <option value="">Select Sales Manager</option>
                            @foreach($sales_mg as $manager)
                                <option value="{{ $manager->userId }}" {{ old('sales_mg') == $manager->userId ? 'selected' : '' }}>{{ $manager->name }}</option>
                            @endforeach
                        </select>
                    </div> -->


                    <div class="col-md-3 mb-3" id="user_coun">
                        <div class="mb-3">
                            <label for="user_country" class="form-label">
                                <strong>Agent Country</strong>
                                <span style="color: red; font-weight: bold;">*</span>
                            </label>
                            <select class="form-control select2" id="user_country" name="user_country">
                                <option value="">Choose a country...</option>
                                @foreach($cityCountry as $c)
                                    <option value="{{ $c->name }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3" id="city_name">
                        <div class="mb-3">
                            <label for="city" class="form-label">
                                <strong>Agent City</strong>
                                <span style="color: red; font-weight: bold;">*</span>
                            </label>
                            <select class="form-control select2" id="city" name="city">
                                <option value="">Select country first...</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="agent_address" class="form-label"><strong>Agent Address</strong><span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('agent_address') is-invalid @enderror" name="agent_address" value="{{ old('agent_address') }}" placeholder="Enter Address">
                        @error('agent_address')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-4">
                        <label for="country" class="form-label"><strong>Service Country</strong><span class="text-danger">*</span></label>
                        <select class="form-control select2" id="country" name="country[]" multiple required>
                            @foreach($authUserCountries as $countryName)
                                <option value="{{ $countryName }}" {{ collect(old('country'))->contains($countryName) ? 'selected' : '' }}>
                                    {{ $countryName }}
                                </option>
                            @endforeach
                        </select>
                        @error('country')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>                  

                    <div class="col-md-2 mb-3">
                        <label for="inputCountryCode" class="form-label"><strong>Country Code</strong><span
                                style="color: red; font-weight: bold;">*</span></label>
                        <select class="form-control select2" id="inputCountryCode" name="code" required>
                            <option value="">Choose...</option>
                            {{-- {{ dd($countryCodes) }} --}}
                            @foreach($countryCodes as $key => $value)
                            <option value="{{ $key }}" @if($key == '65') selected @endif >{{ $value }}</option>
                            @endforeach
                        </select>
                        @error('code')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="phone" class="form-label"><strong>Phone No</strong><span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Phone No" oninput="validatePhoneNumber(this)">
                        <small class="validation-message text-danger" id="phone-validation-message"></small>
                        @error('phone')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- <div class="col-md-3 mb-3">
                        <label for="id_card" class="form-label">
                            <strong>ID Card</strong><span class="text-danger">*</span>
                        </label>
                        <select class="form-select select2" id="id_card" name="id_card">
                            <option value="">Select ID Card Type...</option>
                            @foreach($card as $cardType)
                                <option value="{{ $cardType->card_type }}" {{ old('id_card') == $cardType->card_type ? 'selected' : '' }}>
                                    {{ $cardType->card_type }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_card')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>                    

                    <div class="col-md-3 mb-3">
                        <label for="card_number" class="form-label"><strong>Card Number</strong><span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('card_number') is-invalid @enderror" name="card_number" value="{{ old('card_number') }}">
                        @error('card_number')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div> --}}
                    <div class="col-md-3 mb-3">
                        <label for="agent_image" class="form-label"><strong>Agency Logo</strong><span class="text-danger">*</span></label>
                        <input type="file" class="form-control @error('agent_image') is-invalid @enderror" name="agent_image" required>
                        @error('agent_image')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="image" class="form-label"><strong>ID Proof (Image)</strong><span class="text-danger">*</span></label>
                        <input type="file" class="form-control @error('image') is-invalid @enderror" name="image" required>
                        @error('image')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="password" class="form-label"><strong>Password</strong><span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Choose Password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fa fa-eye-slash" id="toggleIcon"></i>
                            </button>
                        </div>
                        <small class="validation-message text-danger" id="password-validation-message"></small>
                        @error('password')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mt-4 text-center">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <button type="reset" class="btn btn-secondary px-4">Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css">
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<script>
    const APP_URL = @json(config('app.url')); // Get APP_URL from Laravel
</script>

<script>
    $(document).ready(function() {
        // Initialize Service Country field
        $('#country').select2({
            placeholder: "Select countries...",
            allowClear: true,
            width: '100%'
        });
        
        // Initialize Agent Country field with search functionality
        $('#user_country').select2({
            placeholder: "Search and select a country...",
            allowClear: true,
            width: '100%'
        });
        
        // Initialize Agent City field with search functionality
        $('#city').select2({
            placeholder: "Search and select a city...",
            allowClear: true,
            width: '100%'
        });
        
        // Initialize Country Code field with search functionality
        $('#inputCountryCode').select2({
            placeholder: "Search country code...",
            allowClear: true,
            width: '100%'
        });
        
        // Initialize ID Card field with search functionality
        $('#id_card').select2({
            placeholder: "Search ID card type...",
            allowClear: true,
            width: '100%'
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

    function validatePassword(input) {
        const value = input.value.trim();
        // Password regex for moderate security (at least 8 characters, with at least one number and one letter)
        const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{8,}$/;
        
        if (value === '') {
            showValidationMessage(input, false, 'Password is required');
        } else if (!passwordRegex.test(value)) {
            showValidationMessage(input, false, `
                Please enter a valid password:
                <ul class="mt-1 mb-0">
                    <li>At least 8 characters long</li>
                    <li>Must contain at least one letter</li>
                    <li>Must contain at least one number</li>
                    <li>Can contain special characters (@$!%*#?&)</li>
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

<!-- Country and City Selection -->

<script>
    $(document).ready(function() {
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
                        $('#city').html('<option value="">Select city...</option>');
                        
                        if (response.cities && response.cities.length > 0) {
                            $.each(response.cities, function(key, city) {
                                $('#city').append('<option value="' + city.name + '">' + city.name + '</option>');
                            });
                        } else {
                            $('#city').append('<option disabled>No cities found</option>');
                        }
                        
                        // Refresh Select2 dropdown
                        $('#city').trigger('change');
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
                $('#city').html('<option value="">Select country first...</option>');
                $('#city').trigger('change');
            }
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

    /* Hover effect for validation messages */
    .validation-message .invalid-feedback:hover,
    .validation-message .valid-feedback:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
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

<!-- DMC Search Scripts -->
<script>
$(document).ready(function() {
    // Initialize all select2 dropdowns
    $('.select2').select2();

    // Handle country change for search
    $('#search_country').on('change', function() {
        const selectedCountry = $(this).val();
        if (selectedCountry) {
            $('#search_city').html('<option value="">Loading cities...</option>');
            
            $.ajax({
                url: "{{ route('fetch-cities-by-country') }}",
                type: "GET",
                data: { country: selectedCountry },
                success: function(response) {
                    $('#search_city').html('<option value="">Select city...</option>');
                    if (response.cities && response.cities.length > 0) {
                        response.cities.forEach(function(city) {
                            $('#search_city').append(`<option value="${city.name}">${city.name}</option>`);
                        });
                    }
                    $('#search_city').trigger('change');
                }
            });
        } else {
            $('#search_city').html('<option value="">Search by City...</option>');
            $('#search_city').trigger('change');
        }
    });

    // Handle search
    $('#searchAgentBtn').on('click', function() {
        const searchData = {
            country: $('#search_country').val(),
            city: $('#search_city').val(),
            agent_name: $('#search_agent_name').val(),
            agency_name: $('#search_agency_name').val()
        };

        // Show loading state
        $(this).prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i>');
        $('#searchResults').hide();

        $.ajax({
            url: `${APP_URL}/search-agents`,
            method: 'GET',
            data: searchData,
            success: function(response) {
                if (!response.success) {
                    const message = $('<div>')
                        .addClass('alert alert-warning mt-2')
                        .text(response.message || 'Access denied')
                        .insertBefore($('#searchResults'))
                        .delay(3000)
                        .fadeOut(function() { $(this).remove(); });
                    return;
                }

                if (response.agents.length > 0) {
                    let html = '';
                    response.agents.forEach(function(agent) {
                        html += `
                            <tr data-agent-id="${agent.agent_id}">
                                <td>${agent.name || '-'}</td>
                                <td>${agent.company_name || '-'}</td>
                                <td>${agent.user_country || '-'}</td>
                                <td>${agent.city || '-'}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary select-agent">
                                        <i class="mdi mdi-check"></i> Select
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    $('#searchResultsBody').html(html);
                } else {
                    $('#searchResultsBody').html('<tr><td colspan="5" class="text-center">No agents found</td></tr>');
                }
                $('#searchResults').show();
            },
            error: function() {
                $('#searchResultsBody').html('<tr><td colspan="5" class="text-center text-danger">Error occurred while searching</td></tr>');
                $('#searchResults').show();
            },
            complete: function() {
                // Reset button state
                $('#searchAgentBtn').prop('disabled', false).html('<i class="mdi mdi-magnify"></i>');
            }
        });
    });

    // Handle clear search
    $('#clearSearchBtn').on('click', function() {
        $('#search_agent_name').val('');
        $('#search_agency_name').val('');
        $('#search_country').val('').trigger('change');
        $('#search_city').val('').trigger('change');
        $('#searchResults').hide();
    });

    // Add this at the top of your script section
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Handle agent selection
    $(document).on('click', '.select-agent', function() {
        const $btn = $(this);
        const $row = $btn.closest('tr');
        const agentId = $row.data('agent-id');
        
        if (!agentId) {
            console.error('No agent ID found');
            return;
        }
        
        // Disable button and show loading
        $btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i>');
        
        // First update the DMC ID
        $.ajax({
            url: "{{ route('agents.update-dmc') }}",
            method: 'POST',
            data: {
                agent_id: agentId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Fill form fields
                    $('input[name="agency_id"]').val($row.find('td:eq(1)').text().trim());
                    $('input[name="name"]').val($row.find('td:eq(0)').text().trim());
                    
                    const country = $row.find('td:eq(2)').text().trim();
                    const city = $row.find('td:eq(3)').text().trim();
                    
                    // Set country and wait for cities to load
                    $('#user_country').val(country).trigger('change');
                    
                    // Wait for cities to load then set city
                    setTimeout(function() {
                        $('#city').val(city).trigger('change');
                    }, 1000);
                    
                    // Remove the selected row with animation
                    $row.fadeOut(400, function() {
                        $(this).remove();
                        // If no more rows, show "No agents found" message
                        if ($('#searchResultsBody tr').length === 0) {
                            $('#searchResultsBody').html('<tr><td colspan="5" class="text-center">No agents found</td></tr>');
                        }
                    });
                    
                    // Show success message
                    const message = $('<div>')
                        .addClass('alert alert-success alert-dismissible fade show mt-2')
                        .html(`
                            Agent selected successfully!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `)
                        .insertBefore($('#searchResults'))
                        .delay(3000)
                        .fadeOut(function() { $(this).remove(); });
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseJSON || xhr);
                // Show error message
                const errorMsg = xhr.responseJSON?.message || 'Failed to select agent';
                const message = $('<div>')
                    .addClass('alert alert-danger alert-dismissible fade show mt-2')
                    .html(`
                        ${errorMsg}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `)
                    .insertBefore($('#searchResults'))
                    .delay(3000)
                    .fadeOut(function() { $(this).remove(); });
            
                // Reset button state
                $btn.prop('disabled', false)
                    .html('<i class="mdi mdi-check"></i> Select');
            }
        });
    });
});
</script>
@endsection
