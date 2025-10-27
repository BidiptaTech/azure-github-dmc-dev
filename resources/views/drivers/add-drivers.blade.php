@extends('layouts.layout')
@section('content')

<style>
    .select2-container .select2-selection--single {
        height: 100% !important;
        line-height: 100% !important;
        padding: 8px 12px;
    }
    .select2-container .select2-results__option {
        padding: 12px 10px;
    }
</style>


<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Add New Driver
                <a href="{{ route('driver.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
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

            <form id="driverForm" method="POST" action="{{ route('driver.store') }}" enctype="multipart/form-data" class="card-body">
                @csrf
                <!-- Hidden Fields -->
                <input id="userId" type="hidden" class="form-control" name="user_id" >
                <div id="driverDetailsContainer">
                    <div class="driver-form">
                        <div class="row">
                            <!-- First row - 4 columns -->
                            <div class="row mb-3">
                            @if(in_array(auth()->user()->role_id, [1, 2, 3, 4, 23, 25, 62, 46, 109, 110]))
                                <!-- Select DMC Name -->
                                <div class="mb-3 col-md-3" id="dmc-container">
                                    <label for="dmc" class="form-label"><strong>DMC</strong><span style="color: red; font-weight: bold;">*</span></label>
                                    <select id="dmc" name="dmc" class="form-control" required>
                                        <option value="">Select DMC</option>
                                        @foreach ($dmcs as $dmc)
                                            <option value="{{ $dmc->userId }}">{{ $dmc->company_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                                <!-- Driver Salutation -->
                                <div class="col-md-3 mb-3">
                                    <label for="salutation" class="form-label"><strong>Salutation</strong><span class="text-danger">*</span></label>
                                    <select class="form-control" name="salutation">
                                        <option value="">Select Salutation</option>
                                        <option value="Mr" {{ old('salutation') == 'Mr' ? 'selected' : '' }}>Mr.</option>
                                        <option value="Mrs" {{ old('salutation') == 'Mrs' ? 'selected' : '' }}>Mrs.</option>
                                        <option value="Miss" {{ old('salutation') == 'Miss' ? 'selected' : '' }}>Miss</option>
                                        <option value="Dear" {{ old('salutation') == 'Dear' ? 'selected' : '' }}>Dear</option>
                                    </select>
                                    @error('salutation')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Add this field after the driver_id select field, around line 128 -->
                                <div class="col-md-3 mb-3">
                                    <label for="driver_gender" class="form-label"><strong>Driver Gender</strong><span class="text-danger">*</span></label>
                                    <select id="driver_gender" name="driver_gender" class="form-select">
                                        <option value="">Select gender</option>
                                        <option value="Male" {{ old('driver_gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('driver_gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                        <option value="Other" {{ old('driver_gender') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('driver_gender')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Name -->
                                <div class="col-md-3 mb-3">
                                    <label for="name" class="form-label"><strong>Name</strong><span class="text-danger">*</span></label>
                                    <input id="name" type="name" class="form-control" name="name" placeholder="Driver Name" value="{{ old('name') }}">
                                    @error('name')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Email -->
                                <div class="col-md-3 mb-3">
                                    <label for="email" class="form-label"><strong>Email</strong><span class="text-danger">*</span></label>
                                    <input id="email" type="text" class="form-control" name="email" placeholder="Email" value="{{ old('email') }}" 
                                           oninput="validateEmail(this)">
                                    <small class="validation-message text-danger" id="email-validation-message"></small>
                                    @error('email')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            
                                <!-- Phone -->
                                <div class="col-md-3 mb-3">
                                    <label for="phone" class="form-label"><strong>Phone No</strong><span class="text-danger">*</span></label>
                                    <input id="phone" type="text" class="form-control" name="phone" placeholder="phone" value="{{ old('phone') }}"
                                           oninput="validatePhoneNumber(this)">
                                    <small class="validation-message text-danger" id="phone-validation-message"></small>
                                    @error('phone')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Address -->
                                <div class="col-md-3 mb-3">
                                    <label for="address" class="form-label"><strong>Address</strong><span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="address" value="{{ old('address') }}" placeholder="Enter Address">
                                    @error('address')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Country -->
                                <div class="mb-3 col-md-3">
                                    <label for="country" class="form-label"><strong>Country</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 20)
                                        <select name="country" id="country" class="form-control" required onchange="validateDriverAge(document.getElementById('driver_age'))">
                                            <option value="">Select Country</option>
                                            @foreach($country as $countryItem)
                                                <option value="{{ $countryItem->name }}" {{ old('country') == $countryItem->name ? 'selected' : '' }}>
                                                    {{ $countryItem->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text" class="form-control" id="country" onchange="validateDriverAge(document.getElementById('driver_age'))" value="{{in_array(auth()->user()->role_id, [11, 35, 76, 111, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? $userCountry : ''}}"
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
                                    if ($roleId == 1 || $roleId == 20) {
                                        $placeholder = 'Select Country First';
                                    } elseif ($roleId == 11) {
                                        $placeholder = 'Select City';
                                    } else {
                                        $placeholder = 'Select DMC First';
                                    }
                                @endphp
                                
                                <select name="city" id="citySelect" class="form-control" required>
                                    <option value="">{{ $placeholder }}</option>
                                    @if(in_array($roleId, [11, 35, 76, 111, 130, 132, 133, 135, 136, 137, 138, 139, 140]))
                                        @foreach($cities as $city)
                                            <option value="{{ $city->name }}">{{ $city->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('city')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                                <!-- State -->
                                <div class="col-md-3 mb-3">
                                    <label for="state" class="form-label"><strong>State</strong></label>
                                    <input id="state" type="text" class="form-control" name="state" value="{{ old('state') }}" placeholder="Enter State">
                                    @error('state')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- License -->
                                <div class="col-md-3 mb-3">
                                    <label for="license_no" class="form-label"><strong>License No</strong><span class="text-danger">*</span></label>
                                    <input id="license_no" type="text" class="form-control" name="license_no" value="{{ old('license_no') }}" placeholder="Enter License No"
                                           oninput="validateLicenseNumber(this)">
                                            @error('license_no')
                                                <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                    <small class="validation-message text-danger" id="license_no-validation-message"></small>
                                </div>
                                
                                <!-- License Expiry -->
                                <div class="col-md-3 mb-3">
                                    <label for="license_exp_date" class="form-label"><strong>License Expiry Date</strong><span class="text-danger">*</span></label>
                                    <input id="license_exp_date" type="date" class="form-control" name="license_exp_date" value="{{ old('license_exp_date') }}" placeholder="License Expiry">
                                    @error('license_exp_date')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Driver Age -->
                                <div class="col-md-3 mb-3">
                                    <label for="driver_age" class="form-label"><strong>Driver Age</strong><span class="text-danger">*</span></label>
                                    <input id="driver_age" type="number" class="form-control" name="driver_age" 
                                           value="{{ old('driver_age') }}" placeholder="Enter Driver Age" oninput="validateDriverAge(this)">
                                    <small class="validation-message text-danger" id="driver_age-validation-message"></small>
                                    @error('driver_age')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- App Password -->
                                <div class="col-md-3 mb-3">
                                    <label for="app_password" class="form-label"><strong>App Password</strong><span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input id="app_password" type="password" class="form-control" name="app_password" placeholder="Enter App Password" autocomplete="new-password">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleAppPassword">
                                            <i class="ri-eye-off-line" id="appPasswordIcon"></i>
                                        </button>
                                    </div>
                                    @error('app_password')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                                
                            <!-- Bank Details -->
                            <fieldset class="border p-3 rounded mb-3">
                                <h5 class="d-flex justify-content-between align-items-center mb-3" style="margin-top: 10px;">
                                    Bank Details
                                </h5>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="bank_account_holder_name" class="form-label"><strong>Bank Account Holder Name</strong></label>
                                        <input type="text" class="form-control" name="bank_account_holder_name" value="{{ old('bank_account_holder_name') }}" placeholder="Enter Account Holder Name">
                                        @error('bank_account_holder_name')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="account_number" class="form-label"><strong>Account Number</strong></label>
                                        <input type="text" class="form-control" id="account_number" name="account_number" value="{{ old('account_number') }}" placeholder="Enter Account Number"
                                               oninput="validateAccountNumber(this)">
                                        <small class="validation-message text-danger" id="account_number-validation-message"></small>
                                        @error('account_number')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="bank_name" class="form-label"><strong>Bank Name</strong></label>
                                        <input type="text" class="form-control" name="bank_name" value="{{ old('bank_name') }}" placeholder="Enter Bank Name">
                                        @error('bank_name')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="bank_code" class="form-label"><strong>Bank Code</strong></label>
                                        <input type="text" class="form-control" id="bank_code" name="bank_code" value="{{ old('bank_code') }}" placeholder="Enter Bank Code"
                                               oninput="validateBankCode(this)">
                                        <small class="validation-message text-danger" id="bank_code-validation-message"></small>
                                        @error('bank_code')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="swift_code" class="form-label"><strong>SWIFT Code</strong></label>
                                        <input type="text" class="form-control" id="swift_code" name="swift_code" value="{{ old('swift_code') }}" placeholder="Enter SWIFT Code"
                                               oninput="validateSwiftCode(this)">
                                        <small class="validation-message text-danger" id="swift_code-validation-message"></small>
                                        @error('swift_code')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <!-- Additional bank fields can be added here if needed -->
                                </div>
                            </fieldset>
                        </div>

                        <!-- Profile Image -->
                        <div class="row mb-3">
                            <div class="col-md-3 mb-3">
                                <label for="master_image" class="form-label"><strong>Profile Image</strong><span style="color: red; font-weight: bold;">*</span></label>
                                <div id="master-drop-area" class="form-control" style="border: 2px dashed #007bff; text-align: center;">
                                    Drag & Drop your files here or click to upload.
                                    <input type="file" id="master_image" name="master_image" style="display: none;" required>
                                </div>
                            </div>
                            <div class="col-md-9 mb-3">
                                <div id="master-preview-container" class="d-flex flex-wrap gap-2" style="margin-top: 30px; overflow-x: auto; white-space: nowrap;"></div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="driver_status" value="0">
                                    <input class="form-check-input" name="driver_status" type="checkbox" id="driver_status"
                                        value="1">
                                    <label for="driver_status" class="form-check-label"><strong>Status</strong></label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-3 mt-4">
                            <button type="submit" class="btn btn-primary px-4">Save</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End of the form -->
@endsection

@section('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<!-- Ajax to driver user details -->
<script>
    // Wait until the DOM is fully loaded
    document.addEventListener("DOMContentLoaded", function () {
        // Get the select element
        const phoneSelect = document.getElementById("phone");
        const email = document.getElementById("email");
        const name = document.getElementById("name");
        const userId = document.getElementById("userId");

        // Add an event listener to handle the change event
        phoneSelect.addEventListener("change", function () {
            const userPhone = phoneSelect.value;
            if (userPhone) {
                fetch(`/get-user-details/${userPhone}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error("Network response was not ok");
                        }
                        return response.json();
                    })
                    .then(data => {
                        const userDetailsContainer = document.getElementById("user-details");

                        // Check if the request was successful
                        if (data.success) {
                            const user = data.user[0];
                            console.log("username = ", user.name)
                            name.value = user.name;
                            email.value = user.email;
                            userId.value = user.userId;
                        } else {
                            // Display an error message if the user is not found
                            userDetailsContainer.style.display = "block";
                        }
                    })
                    .catch(error => {
                        // Handle any errors that occur during the fetch
                        document.getElementById("user-details").innerHTML = `
                            <p class="text-danger">An error occurred: ${error.message}</p>
                        `;
                    });
            } else {
                // Clear the user details if no user is selected
                document.getElementById("user-details").innerHTML = "";
            }
        });
    });

</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const holidayDatesInput = document.getElementById('holiday_dates_input');
        
        // Initialize Flatpickr
        flatpickr("#holiday_dates", {
            mode: "multiple", // Allow multiple date selection
            dateFormat: "Y-m-d", // Format of the selected dates
            onChange: function (selectedDates, dateStr) {
                // Update hidden input with selected dates
                holidayDatesInput.value = dateStr;
            }
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Initialize Flatpickr with multiple date selection
        flatpickr("#holiday_dates", {
            mode: "multiple",       // Allows multiple dates selection
            dateFormat: "Y-m-d",     // Date format to store
            defaultDate: JSON.parse(document.getElementById("holiday_dates_input").value), // Preselect dates
            onChange: function(selectedDates) {
                // Update the hidden field with the selected dates in JSON format
                const selectedDatesArray = selectedDates.map(date => date.toISOString().split('T')[0]);
                document.getElementById("holiday_dates_input").value = JSON.stringify(selectedDatesArray);

                // Update the visible input field to show selected dates
                document.getElementById("holiday_dates").value = selectedDatesArray.join(", ");
            },
        });
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggleVisibility = (checkboxId, fieldId) => {
        document.getElementById(checkboxId).addEventListener('change', function() {
            document.getElementById(fieldId).classList.toggle('d-none', !this.checked);
        });
    };

    toggleVisibility('breakfastToggle', 'breakfastFields');
    toggleVisibility('lunchToggle', 'lunchFields');
    toggleVisibility('dinnerToggle', 'dinnerFields');
});
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
    $(document).ready(function() {
        // Assuming you have a way to get the user's role ID
        var userRoleId = {{ auth()->user()->role_id }}; // Adjust this line based on your authentication method

        // Check if the user role is one of the specified IDs that should see DMC dropdown
        if ([1, 2, 3, 4, 23, 25, 62, 46, 109, 110].includes(userRoleId)) {
            $('#dmc-container').show(); // Show the DMC select box
            $('#dmc').prop('required', true); // Set DMC as required
        } else {
            $('#dmc-container').hide(); // Hide the DMC select box
            $('#dmc').prop('required', false); // Remove required attribute
        }
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

    function validateLicenseNumber(input) {
        // Allow alphanumeric characters and some special characters (-, /)
        input.value = input.value.replace(/[^a-zA-Z0-9\-\/]/g, '');
        
        const value = input.value.trim();
        // License numbers are usually alphanumeric and vary by country, so this is a basic pattern
        const licenseRegex = /^[a-zA-Z0-9\-\/]{4,20}$/;
        
        if (value === '') {
            // License is optional, so don't show error if empty
            input.classList.remove('is-valid');
            input.classList.remove('is-invalid');
            document.getElementById(`${input.id}-validation-message`).innerHTML = '';
        } else if (!licenseRegex.test(value)) {
            showValidationMessage(input, false, `
                Please enter a valid license number:
                <ul class="mt-1 mb-0">
                    <li>Must be 4-20 characters</li>
                    <li>Can contain letters, numbers, hyphens, and slashes</li>
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
                showValidationMessage(input, false, `In ${country}, driver must be at least ${rules.min} years old`);
            } else if (value > rules.max) {
                showValidationMessage(input, false, `In ${country}, driver age cannot exceed ${rules.max} years`);
            } else {
                showValidationMessage(input, true, '');
            }
        } else {
            // Fallback if country not in rules
            showValidationMessage(input, true, '');
        }
    }


    // function validateAccountNumber(input) {
    //     // Allow only numbers and possibly some formatting characters
    //     input.value = input.value.replace(/[^\d\-]/g, '');
        
    //     const value = input.value.trim();
    //     // Account numbers can vary, but typically they're just digits
    //     const accountRegex = /^[\d\-]{6,20}$/;
        
    //     if (value === '') {
    //         // Account number is optional, so don't show error if empty
    //         input.classList.remove('is-valid');
    //         input.classList.remove('is-invalid');
    //         document.getElementById(`${input.id}-validation-message`).innerHTML = '';
    //     } else if (!accountRegex.test(value)) {
    //         showValidationMessage(input, false, `
    //             Please enter a valid account number:
    //             <ul class="mt-1 mb-0">
    //                 <li>Must be 6-20 characters</li>
    //                 <li>Can contain numbers and hyphens</li>
    //             </ul>
    //         `);
    //     } else {
    //         showValidationMessage(input, true, '');
    //     }
    // }

    // function validateBankCode(input) {
    //     // Allow alphanumeric characters
    //     input.value = input.value.replace(/[^a-zA-Z0-9]/g, '');
        
    //     const value = input.value.trim();
    //     // Bank codes are typically 8-11 characters
    //     const bankCodeRegex = /^[a-zA-Z0-9]{4,11}$/;
        
    //     if (value === '') {
    //         // Bank code is optional, so don't show error if empty
    //         input.classList.remove('is-valid');
    //         input.classList.remove('is-invalid');
    //         document.getElementById(`${input.id}-validation-message`).innerHTML = '';
    //     } else if (!bankCodeRegex.test(value)) {
    //         showValidationMessage(input, false, `
    //             Please enter a valid bank code:
    //             <ul class="mt-1 mb-0">
    //                 <li>Must be 4-11 characters</li>
    //                 <li>Can contain letters and numbers</li>
    //             </ul>
    //         `);
    //     } else {
    //         showValidationMessage(input, true, '');
    //     }
    // }

    // function validateSwiftCode(input) {
    //     // Convert to uppercase as SWIFT codes are conventionally uppercase
    //     input.value = input.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        
    //     const value = input.value.trim();
    //     // SWIFT/BIC codes are 8 or 11 characters, all letters except country code and location code which can be alphanumeric
    //     const swiftRegex = /^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$/;
        
    //     if (value === '') {
    //         // SWIFT code is optional, so don't show error if empty
    //         input.classList.remove('is-valid');
    //         input.classList.remove('is-invalid');
    //         document.getElementById(`${input.id}-validation-message`).innerHTML = '';
    //     } else if (!swiftRegex.test(value)) {
    //         showValidationMessage(input, false, `
    //             Please enter a valid SWIFT code:
    //             <ul class="mt-1 mb-0">
    //                 <li>Must be 8 or 11 characters</li>
    //                 <li>Format: 4 letters (bank code) + 2 letters (country code) + 2 alphanumeric (location code) + optional 3 alphanumeric (branch code)</li>
    //                 <li>Example: BOFAUS3N or BNPAFRPPXXX</li>
    //             </ul>
    //         `);
    //     } else {
    //         showValidationMessage(input, true, '');
    //     }
    // }

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

<!-- <script>
$(document).ready(function() {
    // Initialize Select2 for city
    $('#citySelect').select2({
        placeholder: "Search and Select a City",
        allowClear: true,
        tags: true,
        width: '100%'
    });

    // When DMC is changed
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
            $('#citySelect').append('<option value="">Select a DMC first</option>').trigger('change');
            $('#country').val('');
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
    
    // Initialize Select2 for city
    $('#citySelect').select2({
        placeholder: "Search and Select a City",
        allowClear: true,
        tags: true,
        width: '100%'
    });
    
    // Function to load cities for DMC
    function loadCitiesForDmc(dmcId) {
        if (dmcId) {
            // Show loading state
            $('#citySelect').empty().append('<option value="">Loading cities...</option>').trigger('change');
            
            console.log("Loading cities for DMC ID:", dmcId);
            
            $.ajax({
                url: "{{ route('fetch.cities_countries') }}",
                type: "GET",
                data: { dmc_id: dmcId },
                dataType: 'json',
                success: function(response) {
                    console.log("DMC Response received:", response);
                    
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
                    console.error("Error loading cities for DMC:", error);
                    console.log("XHR Status:", xhr.status);
                    console.log("Response:", xhr.responseText);
                    
                    $('#citySelect').empty();
                    $('#citySelect').append('<option value="">Error loading cities</option>');
                    $('#citySelect').trigger('change');
                }
            });
        }
    }
    
    // Function to load cities by country name
    function loadCitiesByCountry(countryName) {
        if (!countryName) return;
        
        // Show loading state
        $('#citySelect').empty().append('<option value="">Loading cities...</option>').trigger('change');
        
        $.ajax({
            url: "{{ route('get.cities.by.country') }}",
            type: "GET",
            data: { country: countryName },
            dataType: 'json',
            success: function(response) {
                console.log("Cities loaded for country:", response);
                
                // Clear loading state
                $('#citySelect').empty();
                
                // Add default option
                $('#citySelect').append('<option value="">Select City</option>');
                
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
                console.error("Error loading cities:", error);
                $('#citySelect').empty();
                $('#citySelect').append('<option value="">Error loading cities</option>');
                $('#citySelect').trigger('change');
            }
        });
    }
    
    // Role-based initialization
    if (userRoleId == 11) {
        // DMC user - hide DMC selector and auto-populate
        $('#dmc-container').hide();
        $('#dmc').prop('required', false);
        
        // Auto-fill the country field with the DMC's country
        $('#country').val(userCountry);
        
        // Load cities for this DMC
        if (dmcId) {
            loadCitiesForDmc(dmcId);
        }
    } 
    else if ([1, 2, 3, 4, 23, 25, 62, 46, 109, 110].includes(userRoleId)) {
        // Admin roles - show DMC dropdown and handle DMC selection
        $('#dmc-container').show();
        $('#dmc').prop('required', true);
        
        // When DMC is changed (for admin users)
        $('#dmc').change(function() {
            var selectedDmcId = $(this).val();
            if (selectedDmcId) {
                loadCitiesForDmc(selectedDmcId);
            } else {
                // Clear city select and country
                $('#citySelect').empty().append('<option value="">Select a DMC first</option>').trigger('change');
                $('#country').val('');
            }
        });
        
        // Also handle direct country changes for these roles
        $('#country').change(function() {
            var selectedCountry = $(this).val();
            if (selectedCountry) {
                loadCitiesByCountry(selectedCountry);
            } else {
                $('#citySelect').empty().append('<option value="">Select Country First</option>').trigger('change');
            }
        });
    } 
    else if ([35, 76, 111, 130, 132, 133, 135, 136, 137, 138, 139, 140].includes(userRoleId)) {
        // Other roles with specific DMC relationships
        $('#dmc-container').hide();
        $('#dmc').prop('required', false);
        
        // These roles should auto-populate their country and cities
        if (userCountry) {
            $('#country').val(userCountry);
            loadCitiesByCountry(userCountry);
        }
    }
    else {
        // Default case for other roles
        $('#dmc-container').hide();
        $('#dmc').prop('required', false);
        
        // Handle country changes if country dropdown is available
        $('#country').change(function() {
            var selectedCountry = $(this).val();
            if (selectedCountry) {
                loadCitiesByCountry(selectedCountry);
            } else {
                $('#citySelect').empty().append('<option value="">Select Country First</option>').trigger('change');
            }
        });
    }
    
    // Toggle App Password Visibility
    $('#toggleAppPassword').on('click', function() {
        const passwordInput = $('#app_password');
        const passwordIcon = $('#appPasswordIcon');
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            passwordIcon.removeClass('ri-eye-off-line').addClass('ri-eye-line');
        } else {
            passwordInput.attr('type', 'password');
            passwordIcon.removeClass('ri-eye-line').addClass('ri-eye-off-line');
        }
    });
});
</script>

@endsection