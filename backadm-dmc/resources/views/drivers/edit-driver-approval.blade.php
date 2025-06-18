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
</style>
<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Edit Driver Details
                {{-- <a href="{{ route('driver.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a> --}}
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
            
            <form id="driverForm" method="POST" action="{{ route('driver.update.approval', $driver->driver_id) }}" enctype="multipart/form-data" class="card-body">
                @csrf
                @method('PUT')
                <!-- Hidden Fields -->
                <input id="userId" type="hidden" class="form-control" name="user_id" >
                <div id="driverDetailsContainer">
                    <div class="driver-form">
                        <div class="row">
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
                            <div class="col-md-2 mb-3">
                                <label for="license_no" class="form-label"><strong>License No</strong><span class="text-danger">*</span></label>
                                <input value="{{$driver->license_no}}" id="license_no" type="text" class="form-control" name="license_no" placeholder="Enter License No" oninput="validateLicenseNumber(this)">
                                <small class="validation-message text-danger" id="license_no-validation-message"></small>
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

                            <!-- Bank Details -->
                            <fieldset class="border p-3 rounded">
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
                                        <input value="{{$driver->account_number}}" type="text" class="form-control" name="account_number" placeholder="Enter Account Number">
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
                                        <input value="{{$driver->bank_code}}"  type="text" class="form-control" name="bank_code" placeholder="Enter Bank Code">
                                        @error('bank_code')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="swift_code" class="form-label"><strong>SWIFT Code</strong></label>
                                        <input value="{{$driver->swift_code}}"  type="text" class="form-control" name="swift_code" placeholder="Enter SWIFT Code">
                                        @error('swift_code')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </fieldset>   
                        </div>

                        <!-- Profile image -->
                        <div class="mt-3 mb-3 col-md-4">
                            <div>
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
                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-primary px-4" name="approval_status" value="">
                                <i class="fas fa-check-circle me-2"></i> Approve and Save
                            </button>
                            
                            <button type="submit" class="btn btn-danger px-4" name="decline_status" value="">
                                <i class="fas fa-times-circle me-2"></i> Decline
                            </button>
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
<!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script> -->

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

@endsection