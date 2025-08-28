@extends('layouts.layout')
@section('title', 'Validations')
@section('content')
<style>
    .select2-container--default .select2-selection--multiple {
        width : 200px
}
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Add New User
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" class="card-body">
                @csrf
                <input type="hidden" class="form-control" name="code" value="{{ $user_countryCode }}">

                <div class="row">
                    <!-- User Salutation -->
                    <div class="col-md-3 mb-3">
                        <label for="salutation" class="form-label"><strong>Salutation</strong><span class="text-danger">*</span></label>
                        <select class="form-control" name="salutation" required>
                            <option value="">Select Salutation</option>
                            <option value="Mr">Mr.</option>
                            <option value="Mrs">Mrs.</option>
                            <option value="Miss">Ms.</option>
                            <option value="Dear">Dear</option>
                        </select>
                        @error('salutation')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Name Field -->
                    <div class="col-md-3 mb-3">
                        <label for="yourname" class="form-label"><strong>Enter Your Name</strong>
                            <span style="color: red; font-weight: bold;">*</span>
                        </label>
                        <input type="text" class="form-control @error('yourname') is-invalid @enderror" id="yourname"
                            name="yourname" placeholder="Enter Your Name" required>
                        @error('yourname')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="inputRoleselect" class="form-label"><strong>Role</strong><span
                                    style="color: red; font-weight: bold;">*</span></label>
                            <select class="form-select" id="inputRoleselect" name="role" required>
                                <option selected disabled value>Choose...</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->role_id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    @if(auth()->user()->user_type == 1)
                    <!-- MAster Dmc Select -->
                    <div class="col-md-2" id="inputRoleContainer">
                        <div class="mb-3">
                            <label for="master" class="form-label"><strong>Master DMC</strong>
                                <span style="color: red; font-weight: bold;">*</span>
                            </label>
                            <select class="form-select" id="master" name="master_dmc">
                                <option selected disabled value>Choose...</option>
                                @if(count($master_dmc) > 0)
                                    @foreach($master_dmc as $mdmc)
                                        <option value="{{ $mdmc->userId }}">{{ $mdmc->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('master_dmc')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    @endif
                    
                    @if(auth()->user()->role_id == 28)
                    <div class="col-md-2" id="country_name">
                        <div class="mb-3">
                            <label for="country_name" class="form-label">
                                <strong>Country Name</strong>
                                <span style="color: red; font-weight: bold;">*</span>
                            </label>
                            <input type="text" class="form-control" id="country_name" name="country_name" 
                                value="{{ auth()->user()->country }}" readonly required>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Toggle Master Dmc Checkbox (initially hidden) -->
                @if(auth()->user()->user_type == 1)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                            <!-- Dependent Country Select Box (Initially Hidden) -->
                            <div class="col-md-4" id="mastercountryContainer" style="display: none;">
                                <!-- <div class="mb-3">
                                    <label for="masater_country_name" class="form-label"><strong>Country Names</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <select class="form-select" id="masater_country_name" name="country_name">
                                        <option selected disabled>Choose...</option>
                                    </select>
                                </div> -->
                            </div>
                            
                            <!-- Multiple country select -->
                            <div class="col-md-4" id="country_names" style="display: none;">
                                <div class="mb-3">
                                    <label for="country_names" class="form-label">
                                        <strong>Country Names</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <select class="form-select select2" id="country_names" name="country_names[]" multiple>
                                        <option id="default-option" disabled selected>Choose a country...</option>
                                        @foreach($country as $c)
                                            <option value="{{ $c->name }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('country_names')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Assistant Manager Select -->
                            <div class="col-md-4" id="assistant_manager_container" style="display: none;">
                                <!-- <div class="mb-3">
                                    <label for="assistant_manager" class="form-label">
                                        <strong>Assistant Manager</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <select class="form-select" id="assistant_manager" name="assistant_manager">
                                        <option selected disabled value>Choose Assistant Manager...</option>
                                    </select>
                                    @error('assistant_manager')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div> -->
                            </div>

                            <!-- Master Dmc Logo -->
                            <div class="col-md-3" id="master_logo" style="display: none;">
                                <div class="mb-3">
                                    <label for="master_logo" class="form-label">
                                        <strong>Master Dmc Logo</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <input type="file" class="form-control" id="master_logo"
                                        name="master_logo" placeholder="Enter Your Name">
                                </div>
                                
                            </div>
                        @endif
                            <div class="col-md-3" id="company_name" style="display: none;">
                                <div class="mb-3">
                                    <label for="company_name" class="form-label">
                                        <strong>Company Name</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="company_name"
                                        name="company_name" placeholder="Enter Your Name">
                                </div>
                            </div>
                        @if(auth()->user()->user_type == 1 || auth()->user()->user_type == 3 || auth()->user()->user_type == 2)
                            <!-- Single Country Select-->
                            <div class="col-md-4" id="country_name" style="display: none;">
                                <div class="mb-3">
                                    <label for="country_name" class="form-label">
                                        <strong>Country Name</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <select class="form-select" id="country_name" name="country_name">
                                        @if(count($country) > 0)
                                            @foreach($country as $c)
                                            <option value="{{ $c->name }}">{{ $c->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            @endif

                            <div class="col-md-4" id="user_coun">
                                <div class="mb-3">
                                    <label for="user_country" class="form-label">
                                        <strong>User Country</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <select class="form-select" id="user_country" name="user_country">
                                        <option selected disabled value>Choose a country...</option>
                                        @foreach($country as $c)
                                            <option value="{{ $c->name }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4" id="city_name">
                                <div class="mb-3">
                                    <label for="city" class="form-label">
                                        <strong>User City</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <select class="form-select" id="city" name="city">
                                        <option selected disabled value>Select country first...</option>
                                    </select>
                                </div>
                            </div>
                            

                            <div class="col-md-4" id="city_name">
                                <div class="mb-3">
                                    <label for="address" class="form-label">
                                        <strong>Address</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror" id="address"
                                        name="address" placeholder="Enter Address" required>
                                    @error('address')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            @if(auth()->user()->user_type == 1)
                            <!-- Dmc Select-->
                            <div class="col-md-4" id="inputDmcContainer" style="display: none;">
                                <div class="mb-3">
                                    <label for="inputDmc" class="form-label"><strong>Dmc</strong><span
                                            style="color: red; font-weight: bold;">*</span></label>
                                    <select class="form-select" id="inputDmc" name="dmc">
                                        <option selected disabled value>Choose...</option>
                                        @foreach($dmcs as $dmcd)
                                            <option value="{{ $dmcd->userId }}">{{ $dmcd->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('dmc')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <!-- Sales Manager Admin Select--> 
                            <div class="col-md-4" id="inputSalespersonContainerAdmin" style="display: none;">
                                @if(auth()->user()->role_id != 3)
                                <div class="mb-3">
                                    <label for="inputSalesperson" class="form-label"><strong>Sales Manager (Admin)</strong><span
                                            style="color: red; font-weight: bold;">*</span></label>
                                    <select class="form-select" id="inputSalespersonAdmin" name="salemg_admin">
                                        <option selected disabled value>Choose...</option>
                                        @foreach($adminSalesManager as $salesperson)
                                            <option value="{{ $salesperson->userId }}">{{ $salesperson->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                            </div>
                            </div>
                        </div>
                    </div>
                @endif
               

                <!-- All -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="inputCountryCode" class="form-label"><strong>Country Code</strong><span
                                    style="color: red; font-weight: bold;">*</span></label>
                            <select class="form-select" id="inputCountryCode" name="code" required>
                                <option selected disabled value>Choose...</option>
                                @foreach($countryCodes as $key => $value)
                                <option value="{{ $key }}" @if($key == '65') selected @endif >{{ $value }}</option>
                                @endforeach
                            </select>
                            @error('code')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <!-- Phone Field -->
                    <div class="col-md-4 mb-3">
                        <label for="phone" class="form-label"><strong>Phone No</strong>
                            <span style="color: red; font-weight: bold;">*</span>
                        </label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone"
                            name="phone" placeholder="Phone No" required oninput="validatePhoneNumber(this)">
                        <small class="validation-message text-danger" id="phone-validation-message"></small>
                        @error('phone')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Email Field -->
                    <div class="col-md-4 mb-3">
                        <label for="email" class="form-label"><strong>Email Address</strong>
                            <span style="color: red; font-weight: bold;">*</span>
                        </label>
                        <input type="text" class="form-control @error('email') is-invalid @enderror" id="email"
                            name="email" placeholder="Email Address" required oninput="validateEmail(this)">
                        <small class="validation-message text-danger" id="email-validation-message"></small>
                        @error('email')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row d-flex" >
                    <!-- Password Field with Show/Hide Toggle -->
                    <div class="col-md-3 mb-3">
                        <label for="password" class="form-label"><strong>Choose Password</strong>
                            <span style="color: red; font-weight: bold;">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password" placeholder="Choose Password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fa fa-eye-slash" id="toggleIcon"></i>
                            </button>
                        </div>
                        {{-- oninput="validatePassword(this)" --}}
                        <small class="validation-message text-danger" id="password-validation-message"></small>
                        @error('password')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Markup Type -->
                    <!-- <div class="col-md-8"id="markuptypes" style="display:none; gap: 15px;" >
                        <div class="col-md-5">
                            <label for="markup_type" class="form-label"><strong>Markup Type</strong></label>
                            <input type="text" class="form-control" name="markup_type" value="Percentage" readonly>
                        </div>

                        <div class="col-md-3">
                            <label for="markup_type" class="form-label"><strong>Markup Percentage</strong></label>
                            <input type="text" class="form-control" id="markup_percentage" name="markup_type" value="" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="markup_type" class="form-label"><strong>Gateway Percentage</strong></label>
                            <input type="text" class="form-control" id="gateway" name="markup_type" value="" readonly>
                        </div>
                    </div> -->
                </div>

                <div class="row">
                    <!-- Agree Checkbox -->
                    <div class="col-md-12 mb-3">
                        <div class="form-check">
                            <input class="form-check-input @error('agree') is-invalid @enderror" type="checkbox"
                                id="input41" name="agree" required>
                            <label class="form-check-label" for="input41"><strong>I agree to the terms</strong>
                                <span style="color: red; font-weight: bold;">*</span>
                            </label>
                        </div>
                        @error('agree')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="row mt-4">
                    <div class="col-md-12 text-center">
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script>
    const APP_URL = @json(config('app.url')); // Get APP_URL from Laravel
</script>
<script>
    $(document).ready(function() {
    $('.select2').select2({
        placeholder: "Choose countries...",
        allowClear: true
    });

    // Handle country selection change
    $('#masater_country_name').on('change', function() {
        let country = $(this).val();
        if (country) {
            $.ajax({
                url: `${APP_URL}/get-assistant-manager/${encodeURIComponent(country)}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    let assistantManagerSelect = $('#assistant_manager');
                    assistantManagerSelect.empty();
                    assistantManagerSelect.append('<option selected disabled value>Choose Assistant Manager...</option>');

                    if (response.assistant_managers) {
                        $.each(response.assistant_managers, function(key, value) {
                            assistantManagerSelect.append(`<option value="${value.id}">${value.name}</option>`);
                        });
                        $('#assistant_manager_container').show();
                        $('#assistant_manager').prop('required', true);
                    } else {
                        $('#assistant_manager_container').hide();
                        $('#assistant_manager').prop('required', false);
                    }
                },
                error: function() {
                    alert('Error fetching assistant managers. Please try again.');
                }
            });
        } else {
            $('#assistant_manager_container').hide();
        }
    });
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    var userTypeSelect = document.getElementById('inputRoleselect');
    var inputRoleContainer = document.getElementById('inputRoleContainer');
    var inputDmcContainer = document.getElementById('inputDmcContainer');
    var country_name = document.getElementById('country_name');
    var country_names = document.getElementById('country_names');
    var master_logo = document.getElementById('master_logo');
    var company_name = document.getElementById('company_name');
    var inputSalespersonContainerAdmin = document.getElementById('inputSalespersonContainerAdmin');
    var markuptypes = document.getElementById('markuptypes');
    var mastercountryContainer = document.getElementById('mastercountryContainer');

    function resetHiddenFieldValues() {
        document.querySelectorAll(
            '#inputRoleContainer input, #inputRoleContainer select, ' +
            '#inputDmcContainer input, #inputDmcContainer select, ' +
            '#country_name input, #country_name select, ' +
            '#inputSalespersonContainerAdmin input, #inputSalespersonContainerAdmin select, ' +
            '#markuptypes select'
        ).forEach(function (element) {
            element.value = '';
        });
    }

    function updateFields() {
        if (!userTypeSelect) return; // Ensure userTypeSelect exists
        var userRole = parseInt(userTypeSelect.value, 10); // Convert to number

        // Hide all elements safely
        if (inputRoleContainer) inputRoleContainer.style.display = 'none';
        if (inputDmcContainer) inputDmcContainer.style.display = 'none';
        if (country_name) country_name.style.display = 'none';
        if (country_names) country_names.style.display = 'none';
        if (master_logo) master_logo.style.display = 'none';
        if (company_name) company_name.style.display = 'none';
        if (inputSalespersonContainerAdmin) inputSalespersonContainerAdmin.style.display = 'none';
        if (markuptypes) markuptypes.style.display = 'none';
        if (mastercountryContainer) mastercountryContainer.style.display = 'none';

        resetHiddenFieldValues(); // Reset input fields

        // Show elements based on userRole
        if (userRole >= 5 && userRole <= 9) {
            if (country_names) country_names.style.display = 'block';
        } else if (userRole === 10 || userRole === 19) {
            if (country_names) country_names.style.display = 'block';
            if (master_logo) master_logo.style.display = 'block';
            if (company_name) company_name.style.display = 'block';
        } else if (userRole === 11 || userRole === 20) {
            if ({{ auth()->user()->role_id }} == 10 || {{ auth()->user()->role_id }} == 19) {
                if (country_name) country_name.style.display = 'block';
            }
            if (inputRoleContainer) inputRoleContainer.style.display = 'block';
            if (company_name) company_name.style.display = 'block';
        } else if (userRole === 4) {
            if (inputSalespersonContainerAdmin) inputSalespersonContainerAdmin.style.display = 'block';
        } else if ([3, 24, 25, 26, 27].includes(userRole)) {
            if (country_name) country_name.style.display = 'block';
        }
    }

    if (userTypeSelect) {
        userTypeSelect.addEventListener('change', updateFields);
        updateFields(); // Initialize fields on page load
    }
    });


    $(document).ready(function () {
    $('#master').on('change', function () {
        let masterDmcId = $(this).val();
        if (masterDmcId) {
            $.ajax({
                url: `${APP_URL}/get-countries/${masterDmcId}`,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    let countrySelect = $('#masater_country_name');
                    countrySelect.empty();
                    countrySelect.append('<option selected disabled>Choose...</option>');
                    if (response.countries.length > 0) {
                        $.each(response.countries, function (key, value) {
                            countrySelect.append(`<option value="${value}">${value}</option>`);
                        });

                        $('#mastercountryContainer').show();
                    } else {
                        $('#mastercountryContainer').hide();
                    }
                },
                error: function () {
                    alert('Error fetching countries. Please try again.');
                }
            });
        } else {
            $('#mastercountryContainer').hide();
        }
    });

    $(document).ready(function () {
    $('#master').on('change', function () {
        let master = $(this).val(); // Get selected country value
        if (master) {
            $.ajax({
                url: APP_URL + "/get-markup/" + master,  
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.markup_percentage) {
                        $('#markup_percentage').val(response.markup_percentage); // Set markup percentage
                    } else {
                        $('#markup_percentage').val(''); // Clear if no data found
                    }
                    if (response.gateway_percentage) {
                        $('#gateway').val(response.gateway_percentage); // Set markup percentage
                    } else {
                        $('#gateway').val(''); // Clear if no data found
                    }
                },
                error: function () {
                    alert('Error fetching markup percentage. Please try again.');
                }
            });
        } else {
            $('#markup_percentage').val(''); // Reset if no country is selected
        }
    });
});
});
</script>

<!-- Add Font Awesome for icons in validation messages -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

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

    function validatePhoneNumber(input) {
        // Force numeric input by immediately replacing non-numeric characters
        input.value = input.value.replace(/[^0-9+]/g, '');
        
        const phoneRegex = /^[+]?[0-9]{8,15}$/;
        const value = input.value.trim();
        
        if (value === '') {
            showValidationMessage(input, false, 'Phone number is required');
            input.dataset.valid = 'false';
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
            input.dataset.valid = 'false';
        } else {
            showValidationMessage(input, true, '');
            input.dataset.valid = 'true';
        }
    }

    function validateEmail(input) {
        const value = input.value.trim();
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        
        if (value === '') {
            showValidationMessage(input, false, 'Email is required');
            input.dataset.valid = 'false';
        } else if (!emailRegex.test(value)) {
            showValidationMessage(input, false, `
                Please enter a valid email address:
                <ul class="mt-1 mb-0">
                    <li>Must contain @ symbol</li>
                    <li>Must end with a valid domain (.com, .org, etc.)</li>
                    <li>Example: example@domain.com</li>
                </ul>
            `);
            input.dataset.valid = 'false';
        } else {
            showValidationMessage(input, true, '');
            input.dataset.valid = 'true';
        }
    }

    function validatePassword(input) {
        const value = input.value.trim();
        const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{8,}$/;
        
        if (value === '') {
            showValidationMessage(input, false, 'Password is required');
            input.dataset.valid = 'false';
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
            input.dataset.valid = 'false';
        } else {
            showValidationMessage(input, true, '');
            input.dataset.valid = 'true';
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
    `);
</script>

<!-- Add this script after your existing validation scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        
        form.addEventListener('submit', function(event) {
            // Immediately prevent default to control form submission manually
            event.preventDefault();
            
            const phoneInput = document.getElementById('phone');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            // Validate all fields
            validatePhoneNumber(phoneInput);
            validateEmail(emailInput);
            validatePassword(passwordInput);
            
            // Check if any field has validation errors (look for is-invalid class)
            const hasPhoneError = phoneInput.classList.contains('is-invalid');
            const hasEmailError = emailInput.classList.contains('is-invalid');
            const hasPasswordError = passwordInput.classList.contains('is-invalid');
            
            // Determine if form is valid
            const isValid = !hasPhoneError && !hasEmailError && !hasPasswordError;

            if (!isValid) {
                // Create error message array
                const errorMessages = [];
                if (hasPhoneError) errorMessages.push('Invalid phone number');
                if (hasEmailError) errorMessages.push('Invalid email address');
                if (hasPasswordError) errorMessages.push('Invalid password');

                // Remove any existing error alerts
                const existingAlert = document.querySelector('.alert-danger');
                if (existingAlert) {
                    existingAlert.remove();
                }

                // Show error message at the top of the form
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger alert-dismissible fade show';
                errorDiv.innerHTML = `
                    <strong>Please fix the following errors:</strong>
                    <ul>
                        ${errorMessages.map(msg => `<li>${msg}</li>`).join('')}
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                
                // Insert error message at the top of the form
                form.insertBefore(errorDiv, form.firstChild);

                // Scroll to the top of the form
                window.scrollTo({
                    top: form.offsetTop - 20,
                    behavior: 'smooth'
                });
            } else {
                // If form is valid, submit it
                form.submit();
            }
        });

        // Initialize validation on load for any pre-filled fields
        const phoneInput = document.getElementById('phone');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        
        if (phoneInput.value) validatePhoneNumber(phoneInput);
        if (emailInput.value) validateEmail(emailInput);
        if (passwordInput.value) validatePassword(passwordInput);
    });
</script>

<!-- Add this CSS for the error message -->
<style>
    .alert {
        margin-bottom: 1rem;
        padding: 1rem;
        border-radius: 0.25rem;
    }

    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }

    .alert-danger ul {
        margin-bottom: 0;
        padding-left: 1.5rem;
    }

    .alert-dismissible {
        padding-right: 4rem;
    }

    .alert-dismissible .btn-close {
        position: absolute;
        top: 0;
        right: 0;
        padding: 1.25rem 1rem;
    }
</style>

<!-- Add this script after your existing validation scripts -->
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
                    url: "{{ route('get.cities.by.country') }}",
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
                $('#city').html('<option selected disabled value>Select country first...</option>');
            }
        });
    });
</script>
@endsection