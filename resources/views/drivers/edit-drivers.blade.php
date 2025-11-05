@extends('layouts.layout')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">

<style>
    .select2-container .select2-selection--single {
        height: 100% !important;
        line-height: 100% !important;
        padding: 8px 12px;
    }
    .select2-container .select2-results__option {
        padding: 12px 10px;
    }

    .readonly-field-styling {
        background-color: #f0f2f5 !important;
        border: 1px solid #dfe3e7 !important;
        color: #6e7781 !important;
        cursor: default !important;
        position: relative;
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
        font-size: 14px;
        pointer-events: none;
    }

    .readonly-field-container input:hover {
        border-color: #dfe3e7 !important;
    }

    .field-info-message {
        margin-top: 8px;
        padding: 8px 12px;
        border-left: 4px solid #696cff;
        background-color: #f5f5ff;
        border-radius: 4px;
        display: flex;
        align-items: center;
        font-size: 13px;
        box-shadow: 0 2px 6px rgba(105, 108, 255, 0.15);
        animation: fadeInMessage 0.4s ease-in-out;
    }

    .field-info-message i {
        font-size: 16px;
        margin-right: 8px;
        color: #696cff;
    }

    @keyframes fadeInMessage {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Edit Driver Details
                <a href="{{ route('driver.index') }}" class="btn btn-sm btn-outline-danger">
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

            <form id="driverForm" method="POST" action="{{ route('driver.update', Crypt::encrypt($driver->driver_id)) }}" enctype="multipart/form-data" class="card-body">
                @csrf
                @method('PUT')
                <!-- Hidden Fields -->
                <input id="userId" type="hidden" class="form-control" name="user_id" >
                <div id="driverDetailsContainer">
                    <div class="driver-form">
                        <div class="row">
                            @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 23 || auth()->user()->role_id == 25 || auth()->user()->role_id == 62 || auth()->user()->role_id == 46 || auth()->user()->role_id == 109 || auth()->user()->role_id == 110)
                                <div class="mb-3 col-md-3" id="dmc-container">
                                    <label for="dmc" class="form-label"><strong>DMC</strong><span style="color: red; font-weight: bold;">*</span></label>
                                    <input value="{{$dmc->company_name}}" id="name" type="name" class="form-control" name="dmc_company_name" placeholder="Name" readonly disabled>
                                    
                                    <input type="hidden" name="dmc_id" value="{{ $driver->dmc_id }}">
                                </div>
                            @endif
                            <!-- Driver Salutation -->
                            <div class="mb-3 col-md-3">
                                <label for="salutation" class="form-label"><strong>Salutation</strong><span class="text-danger">*</span></label>
                                <select class="form-control" name="salutation" required>
                                    <option value="">Select Salutation</option>
                                    <option value="Mr" {{ old('salutation', $driver->salutation ?? '') == 'Mr' ? 'selected' : '' }}>Mr.</option>
                                    <option value="Mrs" {{ old('salutation', $driver->salutation ?? '') == 'Mrs' ? 'selected' : '' }}>Mrs.</option>
                                    <option value="Miss" {{ old('salutation', $driver->salutation ?? '') == 'Miss' ? 'selected' : '' }}>Miss</option>
                                    <option value="Dear" {{ old('salutation', $driver->salutation ?? '') == 'Dear' ? 'selected' : '' }}>Dear</option>
                                </select>
                                @error('salutation')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 col-md-3">
                                <label for="driver_gender" class="form-label"><strong>Driver Gender</strong><span class="text-danger">*</span></label>
                                <select class="form-control" name="driver_gender" required>
                                    <option value="">Select Salutation</option>
                                    <option value="Male" {{ old('driver_gender', $driver->driver_gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('driver_gender', $driver->driver_gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                                    <option value="Other" {{ old('driver_gender', $driver->driver_gender ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('driver_gender')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Name -->
                            <div class="col-md-3 mb-3">
                                <label for="name" class="form-label"><strong>Name</strong><span class="text-danger">*</span></label>
                                <input value="{{$driver->name}}" id="name" type="name" class="form-control" name="name" placeholder="Name" required>
                                @error('name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Email -->
                            <div class="col-md-3 mb-3">
                                <label for="email" class="form-label"><strong>Email</strong><span class="text-danger">*</span></label>
                                <input value="{{$driver->email}}" id="email" type="text" class="form-control" name="email" placeholder="Email" required oninput="validateEmail(this)">
                                <small class="validation-message text-danger" id="email-validation-message"></small>
                                @error('email')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- phone -->
                            <div class="col-md-3 mb-3">
                                <label for="phone" class="form-label"><strong>Phone</strong><span class="text-danger">*</span></label>
                                <input value="{{$driver->phone}}" id="phone" type="text" class="form-control" name="phone" placeholder="Phone" required oninput="validatePhoneNumber(this)">
                               <small class="validation-message text-danger" id="phone-validation-message"></small>
                                @error('phone')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- WP Number -->
                            <div class="col-md-3 mb-3">
                                <label for="wp_number" class="form-label"><strong>Whatsapp Number</strong><span class="text-danger">*</span></label>
                                <input value="{{$driver->wp_number}}" type="text" class="form-control" name="wp_number" placeholder="Enter Whatsapp Number" required>
                                @error('wp_number')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Address -->
                            <div class="col-md-3 mb-3">
                                <label for="address" class="form-label"><strong>Address</strong><span class="text-danger">*</span></label>
                                <input value="{{$driver->address}}" type="text" class="form-control" name="address" placeholder="Enter Address" required>
                                @error('address')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Country -->
                            <div class="col-md-3 mb-3">
                                <label for="country" class="form-label"><strong>Country</strong>
                                    <span style="color: red; font-weight: bold;">*</span>
                                </label>
                                <input name="country" class="form-control" type="text" value="{{$driver->country}}" readonly>
                                {{-- <select class="form-control" id="country" name="country" required>
                                    <option value="">Select Country</option>
                                    @foreach($country as $c)
                                        <option value="{{ $c->name }}" @if(old('country', $driver->country ?? '') == $c->name) selected @endif>
                                            {{ $c->name }}
                                        </option>
                                    @endforeach
                                </select> --}}
                                @error('country')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- City -->
                            <div class="col-md-3 mb-3">
                                <label for="city" class="form-label"><strong>City</strong><span class="text-danger">*</span></label>
                                <select name="city" id="citySelect" class="form-control" required>
                                    <option value="{{ $driver->city }}">{{ $driver->city }}</option>
                                    @foreach($city as $c)
                                        @if($c->name != $driver->city)
                                            <option value="{{ $c->name }}">{{ $c->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                {{-- <input value="{{$driver->city}}" type="text" class="form-control" name="city" placeholder="Enter City" required> --}}
                                @error('city')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- State -->
                            <div class="col-md-3 mb-3">
                                <label for="state" class="form-label"><strong>State</strong></label>
                                <input value="{{$driver->state}}" id="state" type="text" class="form-control" name="state" placeholder="Enter State">
                            </div>

                            <!-- License -->
                            <div class="col-md-3 mb-3">
                                <label for="license_no" class="form-label"><strong>License No</strong><span class="text-danger">*</span></label>
                                <div class="readonly-field-container">
                                    <input value="{{$driver->license_no}}" id="license_no" type="text" 
                                        class="form-control readonly-field-styling" 
                                        name="license_no" 
                                        placeholder="Enter License No"
                                        readonly>
                                </div>

                                <div class="field-info-message">
                                    <i class="fas fa-lock"></i> License number is locked and cannot be edited.
                                </div>

                                @error('license_no')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- License Expiry -->
                            <div class="col-md-3 mb-3">
                                <label for="license_exp_date" class="form-label"><strong>License Expiry Date</strong><span class="text-danger">*</span></label>
                                <input value="{{$driver->license_exp_date}}" id="license_exp_date" type="date" class="form-control" name="license_exp_date" placeholder="License Expiry">
                            </div>

                            <!-- Driver Age -->
                            <div class="col-md-3 mb-3">
                                <label for="driver_age" class="form-label"><strong>Driver Age</strong><span class="text-danger">*</span></label>
                                <input value="{{$driver->driver_age}}" id="driver_age" type="number" class="form-control" name="driver_age" placeholder="Enter Driver Age">
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

                            <!-- Bank Details -->
                            <fieldset class="border p-3 rounded mb-3">
                                <h5 class="d-flex justify-content-between align-items-center mb-3" style="margin-top: 10px;">
                                    Edit Bank Details
                                </h5>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="bank_account_holder_name" class="form-label"><strong>Bank Account Holder Name</strong></label>
                                        <input value="{{$driver->bank_account_holder_name}}" type="text" class="form-control" name="bank_account_holder_name" placeholder="Enter Account Holder Name">
                                        @error('bank_account_holder_name')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="account_number" class="form-label"><strong>Account Number</strong></label>
                                        <input value="{{$driver->account_number}}" type="text" class="form-control" id="account_number" name="account_number" placeholder="Enter Account Number" oninput="validateAccountNumber(this)">
                                        <small class="validation-message text-danger" id="account_number-validation-message"></small>
                                        @error('account_number')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="bank_name" class="form-label"><strong>Bank Name</strong></label>
                                        <input value="{{$driver->bank_name}}"  type="text" class="form-control" name="bank_name" placeholder="Enter Bank Name">
                                        @error('bank_name')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="bank_code" class="form-label"><strong>Bank Code</strong></label>
                                        <input value="{{$driver->bank_code}}" type="text" class="form-control" id="bank_code" name="bank_code" placeholder="Enter Bank Code" oninput="validateBankCode(this)">
                                        <small class="validation-message text-danger" id="bank_code-validation-message"></small>
                                        @error('bank_code')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="swift_code" class="form-label"><strong>SWIFT Code</strong></label>
                                        <input value="{{$driver->swift_code}}" type="text" class="form-control" id="swift_code" name="swift_code" placeholder="Enter SWIFT Code" oninput="validateSwiftCode(this)">
                                        <small class="validation-message text-danger" id="swift_code-validation-message"></small>
                                        @error('swift_code')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </fieldset>   
                        </div>

                        <!-- Profile image -->
                        <div class="row mb-3">
                            <div class="col-md-3 mb-3">
                                <label for="master_image" class="form-label"><strong>Profile
                                        Image</strong><span class="text-danger">*</span></label>
                                <div id="master-drop-area" class="form-control"
                                    style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px;">
                                    Drag & Drop your files here or click to upload.
                                    <input type="file" id="master_image" name="master_image" multiple
                                        style="display: none;">
                                </div>
                            </div>
                            <div id="master-preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                style="max-width: 30%; overflow-x: auto; white-space: nowrap;">
                            </div>

                            @if($driver->image)
                            <div class="image-preview-container d-flex flex-wrap gap-2">
                                <div class="image-preview-wrapper position-relative">
                                    <img src="{{$driver->image}}" alt="Driver Profile Image"
                                        style="max-width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                                    <button
                                        class="delete-image-btn position-absolute top-0 end-0 btn btn-sm btn-danger"
                                        data-image="{{ $driver->image }}"
                                        style="width: 20px; height: 20px; line-height: 18px; padding: 0; text-align: center; font-size: 14px; z-index: 1;">
                                        &times;
                                    </button>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Status -->
                        <div class="mt-2 form-check form-switch">
                            <label for="driver_status" class="form-label"><strong>Status</strong></label>
                            <span style="color: red; font-weight: bold;">*</span>
                            <input {{$driver->is_active == 1 ? 'checked' : ''}} class="form-check-input" name="driver_status" type="checkbox" id="driver_status"
                                value="1">
                            <label class="form-check-label"></label>
                            @error('driver_status')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-3 mt-4">
                            <button type="submit" class="btn btn-primary px-4">Update</button>
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
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js"></script>

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
            allowClear: false,
            tags: true,
            width: '100%'
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

<!-- delete existing Master Image -->
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
    //     // Bank codes are typically 4-11 characters
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

    // Initialize - Clear validation states on page load
    document.addEventListener('DOMContentLoaded', function() {
        // List of fields with validation
        const fieldsToValidate = [
            'email', 'phone', 'license_no', 'account_number', 'bank_code', 'swift_code'
        ];
        
        // Remove validation classes and clear messages on page load
        fieldsToValidate.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.classList.remove('is-valid', 'is-invalid');
                
                const messageElem = document.getElementById(`${fieldId}-validation-message`);
                if (messageElem) {
                    messageElem.innerHTML = '';
                }
            }
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

<script>
    // Toggle App Password visibility
    document.getElementById('toggleAppPassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('app_password');
        const icon = document.getElementById('appPasswordIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('ri-eye-off-line');
            icon.classList.add('ri-eye-line');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('ri-eye-line');
            icon.classList.add('ri-eye-off-line');
        }
    });
</script>

@endsection