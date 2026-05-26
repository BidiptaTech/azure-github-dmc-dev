@extends('layouts.layout')
@section('content')

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
<style>
   
    .select2-container .select2-selection--single {
        height: 100% !important; /* Adjust as needed */
        line-height: 100% !important;
        padding: 8px 12px;
    }
    /* Increase the height of the dropdown items */
    .select2-container .select2-results__option {
        padding: 12px 10px;
    }

    /* Flatpickr container styling */
    /* .flatpickr-input {
        height: 40px !important;
        width: 100% !important;
        padding: 0.375rem 0.75rem !important;
        font-size: 0.9375rem !important;
        font-weight: 400 !important;
        line-height: 1.53 !important;
        background-color: #fff !important;
        background-clip: padding-box !important;
        border: 1px solid #d9dee3 !important;
        border-radius: 0.375rem !important;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
    }

    .flatpickr-calendar {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
        border-radius: 0.375rem !important;
    }

    .flatpickr-time {
        height: 40px !important;
        line-height: 40px !important;
        border-radius: 0 0 0.375rem 0.375rem !important;
    }

    .flatpickr-time input {
        height: 20px !important;
        width: 20px !important;
        font-size: 0.9375rem !important;
    } */

    .flatpickr-time {
        height: 38px; /* match Bootstrap input height */
        line-height: 38px;
    }

    /* Reduce width of the time dropdown */
    .flatpickr-calendar.open {
        width: auto !important;
        min-width: 120px;
    }

    /* Make time inputs (hour & minute) smaller */
    .flatpickr-time input {
        width: 40px;
        height: 30px;
        padding: 0;
        text-align: center;
        font-size: 14px;
    }

    .flatpickr-input:hover {
        border-color: #697a8d !important;
    }

    .flatpickr-input:focus {
        border-color: #696cff !important;
        box-shadow: 0 0 0.25rem rgba(105, 108, 255, 0.1) !important;
        outline: none !important;
    }
</style>
<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <ul class="nav nav-pills mb-4 mt-4 d-flex justify-content-center" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ request()->routeIs('restaurant.create') ? 'active' : '' }}" 
                   href="{{ route('restaurant.create') }}" 
                   role="tab">
                    Restaurant
                </a>
            </li>
            
            <li class="nav-item" role="presentation">
                @if(isset($restaurants) && count($restaurants) > 0)
                <a class="nav-link {{ request()->routeIs('meals.create') ? 'active' : '' }}" 
                   href="{{ route('meals.create') }}" 
                   role="tab">
                    Meals
                </a>
                @else
                <a class="nav-link disabled" 
                   href="javascript:void(0);" 
                   role="tab"
                   title="Save this restaurant first before adding meals">
                    Meals
                </a>
                @endif
            </li>
        </ul>
        <x-alert />
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Add New Restaurant
                <a href="{{ route('restaurant.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form id="restaurantForm" method="POST" action="{{ route('restaurant.store') }}"
                enctype="multipart/form-data" class="card-body">
                @csrf
                <!-- Hidden Fields -->

                <div id="restaurantDetailsContainer">
                    <div class="restaurant-form">
                        <div class="row">
                            <!-- Select DMC Name -->
                            <!-- @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 23 || auth()->user()->role_id == 25 || auth()->user()->role_id == 48 || auth()->user()->role_id == 63 || auth()->user()->role_id == 118|| auth()->user()->role_id == 119)
                            <div class="mb-3 col-md-3" id="dmc-container" style="display: none;">
                                <label for="dmc" class="form-label"><strong>DMC</strong><span style="color: red; font-weight: bold;">*</span></label>
                                <select id="dmc" name="dmc" class="form-control" required>
                                    <option value="">Select DMC</option>
                                    @foreach ($dmcs as $dmc)
                                        <option value="{{ $dmc->userId }}">{{ $dmc->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif -->

                            <!-- Restaurant Name -->
                            <div class="col-md-3 mb-3">
                                <label for="name" class="form-label"><strong>Restaurant Name</strong><span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Enter Restaurant Name" required>
                                @error('name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Phone Number -->
                            <div class="col-md-3 mb-3">
                                <label for="phone" class="form-label"><strong>Phone Number</strong><span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="phone" value="{{ old('phone') }}" placeholder="Enter Phone Number" required>
                                @error('phone')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- Country -->
                            <div class="mb-3 col-md-3">
                                <label for="country" class="form-label"><strong>Country</strong>
                                    <span style="color: red; font-weight: bold;">*</span>
                                </label>
                                <select class="form-select" id="country" name="country" required>
                                    <option value="">Select Country</option>
                                    @foreach($country as $c)
                                        <option value="{{ $c->name }}" {{ (old('country') == $c->name || (isset($userCountry) && $userCountry == $c->name)) ? 'selected' : '' }}>
                                            {{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('country')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 
                             -->
                            <div class="col-md-3 mb-3">
                                <label for="city" class="form-label"><strong>City</strong><span class="text-danger">*</span></label>
                                @php
                                    $roleId = auth()->user()->role_id;
                                    $placeholder = 'Select Country First';
                                @endphp
                                
                                <select name="city" id="citySelect" class="form-control" required disabled>
                                    <option value="">{{ $placeholder }}</option>

                                    @if(in_array($roleId, [11, 20, 35, 78, 120, 139, 140]))
                                        @foreach($cities as $city)
                                            <option value="{{ $city->name }}">{{ $city->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('city')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Latitude -->
                            <div class="col-md-3 mb-3">
                                <label for="latitude" class="form-label">
                                    <strong>Latitude</strong><span class="text-danger">*</span>
                                </label>
                                <input name="latitude" type="text" id="latitude" class="form-control" value="{{ old('latitude') }}" placeholder="Enter Latitude" oninput="validateLatitude(this)" required>
                                <small class="validation-message text-danger" id="latitude-validation-message"></small>
                                @error('latitude')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Longitude -->
                            <div class="col-md-3 mb-3">
                                <label for="longitude" class="form-label">
                                    <strong>Longitude</strong><span class="text-danger">*</span>
                                </label>
                                <input name="longitude" type="text" id="longitude" class="form-control" value="{{ old('longitude') }}" placeholder="Enter Longitude" required oninput="validateLongitude(this)">
                                <small class="validation-message text-danger" id="longitude-validation-message"></small>
                                @error('longitude')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cuisine Type -->
                            <div class="col-md-3 mb-3">
                                <label for="cuisine" class="form-label"><strong>Cuisine</strong><span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="cuisine" value="{{ old('cuisine') }}" placeholder="Enter Cuisine Type" required>
                                @error('cuisine')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Ownership -->
                            <div class="col-md-3 mb-3">
                                <label for="owned_by" class="form-label"><strong>Ownership</strong><span class="text-danger">*</span></label>
                                <select name="owned_by" id="owned_by" class="form-control" required>
                                    <option value="" selected>Select</option>
                                    <option value="0" {{ old('owned_by') == '0' ? 'selected' : '' }}>Third Party</option>
                                    @foreach($hotels as $hotel)
                                        <option value="{{ $hotel->hotel_unique_id }}" {{ old('owned_by') == $hotel->hotel_unique_id ? 'selected' : '' }}>
                                            {{ $hotel->name }} - {{ $hotel->display_id }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('owned_by')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="property" class="form-label"><strong>Property</strong><span
                                        class="text-danger">*</span></label>
                                <select name="property" class="form-select" required>
                                    <option value="">Select</option>
                                    <option value="third_party" {{ old('property') == 'third_party' ? 'selected' : '' }}>Third Party</option>
                                    <option value="owner" {{ old('property') == 'owner' ? 'selected' : '' }}>Ownership</option>
                                </select>
                                @error('property')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- email-->
                             <div class="col-md-3 mb-3">
                                <label for="restaurant_email" class="form-label"><strong>Email</strong><span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="restaurant_email" placeholder="Enter Email"   autocomplete="new-email"required>
                                @error('restaurant_email')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div> 
                             <!-- password-->
                            <div class="col-md-3 mb-3">
                                <label for="password" class="form-label"><strong>Password</strong><span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input id="password" type="password" class="form-control" name="password" placeholder="Enter Password" required>
                                  <!-- Show / Hide -->
                                  <button type="button"id="togglePasswordBtn" class="btn btn-outline-secondary  btn-sm px-2" onclick="togglePassword()">
                                        <i id="eyeIcon" class="bi bi-eye-slash"></i>
                                    </button>
                                    <!-- Auto Generate -->
                                    <button type="button" class="btn btn-outline-secondary  btn-sm px-2" onclick="generatePassword()">
                                        🔐
                                    </button>
                                </div>
                                @error('password')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        

                        <!-- Meal Availability -->
                        <div class="row">
                            <!-- Breakfast -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label"><strong>Breakfast Availability</strong></label>
                                <div class="form-check form-switch">
                                    <input id="breakfastToggle" class="form-check-input" type="checkbox" name="breakfast_available" value="1" {{ old('breakfast_available') ? 'checked' : '' }}>
                                    <label class="form-check-label">Available</label>
                                </div>
                                <div id="breakfastFields" class="row mt-2 d-none">
                                    <div class="col-md-3">
                                        <label for="opening_time_bf" class="form-label"><strong>Opening Time</strong></label>
                                        <input type="text" class="form-control time-picker" id="opening_time_bf" name="opening_time_bf" value="{{ old('opening_time_bf') }}" placeholder="Select opening time">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="closing_time_bf" class="form-label"><strong>Closing Time</strong></label>
                                        <input type="text" class="form-control time-picker" id="closing_time_bf" name="closing_time_bf" value="{{ old('closing_time_bf') }}" placeholder="Select closing time">
                                    </div>
                                </div>
                            </div>

                            <!-- Lunch -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label"><strong>Lunch Availability</strong></label>
                                <div class="form-check form-switch">
                                    <input id="lunchToggle" class="form-check-input" type="checkbox" name="lunch_available" value="1" {{ old('lunch_available') ? 'checked' : '' }}>
                                    <label class="form-check-label">Available</label>
                                </div>
                                <div id="lunchFields" class="row mt-2 d-none">
                                    <div class="col-md-3">
                                        <label for="opening_time_lunch" class="form-label"><strong>Opening Time</strong></label>
                                        <input type="text" class="form-control time-picker" id="opening_time_lunch" name="opening_time_lunch" value="{{ old('opening_time_lunch') }}" placeholder="Select opening time">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="closing_time_lunch" class="form-label"><strong>Closing Time</strong></label>
                                        <input type="text" class="form-control time-picker" id="closing_time_lunch" name="closing_time_lunch" value="{{ old('closing_time_lunch') }}" placeholder="Select closing time">
                                    </div>
                                </div>
                            </div>

                            <!-- Dinner -->
                            <div class="col-md-12">
                                <label class="form-label"><strong>Dinner Availability</strong></label>
                                <div class="form-check form-switch">
                                    <input id="dinnerToggle" class="form-check-input" type="checkbox" name="dinner_available" value="1" {{ old('dinner_available') ? 'checked' : '' }}>
                                    <label class="form-check-label">Available</label>
                                </div>
                                <div id="dinnerFields" class="row mt-2 d-none">
                                    <div class="col-md-3">
                                        <label for="opening_time_dinner" class="form-label"><strong>Opening Time</strong></label>
                                        <input type="text" class="form-control time-picker" id="opening_time_dinner" name="opening_time_dinner" value="{{ old('opening_time_dinner') }}" placeholder="Select opening time">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="closing_time_dinner" class="form-label"><strong>Closing Time</strong></label>
                                        <input type="text" class="form-control time-picker" id="closing_time_dinner" name="closing_time_dinner" value="{{ old('closing_time_dinner') }}" placeholder="Select closing time">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row col-md-12">
                            <!-- Master image -->
                            <div class="mt-3 mb-3 col-md-4">
                                <div>
                                    <label for="master_image" class="form-label"><strong>Master
                                            Image</strong><span
                                            style="color: red; font-weight: bold;">*</span></label>
                                    <div id="master-drop-area" class="form-control"
                                        style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px;">
                                        Drag & Drop your files here or click to upload.
                                        <input type="file" id="master_image" name="master_image"
                                            style="display: none;" required>
                                    </div>
                                </div>
                                <div id="master-preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                    style="max-width: 30%; overflow-x: auto; white-space: nowrap;"></div>
                            </div>

                            <!-- Additional Image drop -->
                            <div class="mt-3 mb-3 col-md-8">
                                <div>
                                    <label for="images" class="form-label"><strong>Additional
                                        Images</strong></label>
                                    <div id="drop-area" class="form-control"
                                        style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px;">
                                        Drag & Drop your files here or click to upload.
                                        <input type="file" id="images" name="images[]" multiple style="display: none;">
                                    </div>

                                    <div id="preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                        style="max-width: 100%; overflow-x: auto; white-space: nowrap;">
                                    </div>
                                </div>
                                <input type="file" name="all_images[]" id="all-images"
                                    style="display: none;">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="property" class="form-label"><strong>Description</strong><span
                                class="text-danger">*</span></label>
                        <textarea id="summernote" name="description" class="form-control" rows="10" placeholder="Write Description...">{{ old('description') }}</textarea>
                        @error('description')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Remarks -->
                    <div class="col-md-12 mb-3">
                        <label for="remarks" class="form-label"><strong>Remarks</strong></label>
                        <textarea id="remarks" name="remarks" class="form-control" rows="4" placeholder="Enter any remarks or notes (optional)">{{ old('remarks') }}</textarea>
                        @error('remarks')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="col-md-12 mb-3">
                        <label for="terms_conditions" class="form-label"><strong>Terms & Conditions</strong><span class="text-danger">*</span></label>
                        <textarea id="terms_conditions" name="terms_conditions" class="form-control" rows="6" placeholder="Enter terms and conditions..." required>{{ old('terms_conditions') }}</textarea>
                        @error('terms_conditions')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="restaurant_status" value="0">
                                <input class="form-check-input" name="restaurant_status" type="checkbox" id="restaurant_status" value="1" {{ old('restaurant_status') == '1' ? 'checked' : '' }} required>
                                <label for="restaurant_status" class="form-check-label"><strong>Status</strong><span style="color: red; font-weight: bold;">*</span></label>
                                @error('restaurant_status')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-3 mt-4">
                        <button type="submit" class="btn btn-primary px-4">Save</button>
                    </div>
            </form>
        </div>
    </div>
</div>
<!-- End of the form -->
@endsection

@section('scripts')

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js"></script>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize time pickers
        const timePickerConfig = {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 15
        };
        
        document.querySelectorAll('.time-picker').forEach(function(input) {
            flatpickr(input, timePickerConfig);
        });
        
        // Set up toggle functionality and check initial state
        function setupToggle(checkboxId, fieldId) {
            const checkbox = document.getElementById(checkboxId);
            const fieldsDiv = document.getElementById(fieldId);
            
            // Check initial state on page load
            fieldsDiv.classList.toggle('d-none', !checkbox.checked);
            
            // Set up change event
            checkbox.addEventListener('change', function() {
                fieldsDiv.classList.toggle('d-none', !this.checked);
            });
        }
        
        // Initialize toggle states
        setupToggle('breakfastToggle', 'breakfastFields');
        setupToggle('lunchToggle', 'lunchFields');
        setupToggle('dinnerToggle', 'dinnerFields');
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

<script>
    $(document).ready(function() {
        // Initialize Summernote for description
        $('#summernote').summernote({
            height: 200,      
            minHeight: 200,   
            maxHeight: 500,   
            placeholder: 'Enter your content here...'
        });
        
        // Initialize Summernote for remarks
        $('#remarks').summernote({
            height: 200,      
            minHeight: 200,   
            maxHeight: 500,   
            placeholder: 'Enter any remarks or notes (optional)...'
        });
        
        // Initialize Summernote for terms & conditions
        $('#terms_conditions').summernote({
            height: 200,      
            minHeight: 200,   
            maxHeight: 500,   
            placeholder: 'Enter terms and conditions...'
        });
        
        // Set old content properly after initialization
        @if(old('description'))
            $('#summernote').summernote('code', {!! json_encode(old('description')) !!});
        @endif
        
        @if(old('remarks'))
            $('#remarks').summernote('code', {!! json_encode(old('remarks')) !!});
        @endif
        
        @if(old('terms_conditions'))
            $('#terms_conditions').summernote('code', {!! json_encode(old('terms_conditions')) !!});
        @endif
    });
</script>

<!-- Additional Image drop down -->
<script>
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('images');
    const fileList = document.getElementById('preview-container');
    const allImagesInput = document.getElementById('all-images'); // Hidden input
    let files = []; // Store all files manually
    const MAX_VISIBLE_IMAGES = 3; // Maximum number of visible images
    let showAllImages = false; // Toggle for showing all images

    // Trigger file input on click
    dropArea.addEventListener('click', () => fileInput.click());

    // Handle file input change
    fileInput.addEventListener('change', () => handleFiles(fileInput.files));

    // Handle drag-and-drop events
    dropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropArea.style.borderColor = '#000';
    });

    dropArea.addEventListener('dragleave', () => {
        dropArea.style.borderColor = '#ccc';
    });

    dropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        dropArea.style.borderColor = '#ccc';
        handleFiles(e.dataTransfer.files);
    });

    function handleFiles(newFiles) {
        // Append new files to the list
        Array.from(newFiles).forEach(file => {
            if (file.type.startsWith('image/')) {
                files.push(file);
            } else {
                alert(`${file.name} is not a valid image file.`);
            }
        });
        updateFileList();
    }

    function updateFileList() {
        // Clear file list display
        fileList.innerHTML = '';
        const dataTransfer = new DataTransfer();

        // Decide how many files to display based on `showAllImages`
        const visibleFiles = showAllImages ? files : files.slice(0, MAX_VISIBLE_IMAGES);

        visibleFiles.forEach((file, index) => {
            // Create a wrapper for the image and delete button
            const imageWrapper = document.createElement('div');
            imageWrapper.style.position = 'relative';
            imageWrapper.style.display = 'inline-block';
            imageWrapper.style.margin = '10px';
            imageWrapper.style.width = '100px';
            imageWrapper.style.height = '100px';

            // Create an image element for preview
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file); // Create an object URL for the file
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';

            // Create a delete button
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

            // Remove file and update list on delete
            deleteButton.addEventListener('click', () => {
                const fileIndex = files.indexOf(file);
                if (fileIndex > -1) {
                    files.splice(fileIndex, 1);
                }
                updateFileList();
            });

            // Append image and delete button to the wrapper
            imageWrapper.appendChild(img);
            imageWrapper.appendChild(deleteButton);
            fileList.appendChild(imageWrapper);

            // Add the file to the DataTransfer object
            dataTransfer.items.add(file);
        });

        // Add all files to the hidden input `all-images`
        const hiddenDataTransfer = new DataTransfer();
        files.forEach(file => hiddenDataTransfer.items.add(file));
        allImagesInput.files = hiddenDataTransfer.files;

        // Add a "More Images" badge if there are more files and not showing all images
        if (!showAllImages && files.length > MAX_VISIBLE_IMAGES) {
            const moreBadge = document.createElement('div');
            moreBadge.textContent = `+${files.length - MAX_VISIBLE_IMAGES} more`;
            moreBadge.style.margin = '10px';
            moreBadge.style.padding = '20px';
            moreBadge.style.backgroundColor = '#007bff';
            moreBadge.style.color = 'white';
            moreBadge.style.borderRadius = '5px';
            moreBadge.style.textAlign = 'center';
            moreBadge.style.fontSize = '14px';
            moreBadge.style.cursor = 'pointer';

            // Add click event to show all images
            moreBadge.addEventListener('click', () => {
                showAllImages = true;
                updateFileList(); // Re-render with all images
            });

            fileList.appendChild(moreBadge);
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('#hotelSelect').select2({
            placeholder: "Search and Select a Hotel",
            allowClear: true,
            width: '100%'
        });
    });
</script>

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
        showValidationMessage(input, false, 'Price is required');
    } else if (!priceRegex.test(value)) {
        showValidationMessage(input, false, `
            Please enter a valid price:
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

function validateLatitude(input) {
    // Force numeric input by immediately replacing non-numeric characters
    input.value = input.value.replace(/[^0-9.-]/g, '');
    
    // Allow only one decimal point and ensure minus sign is only at the beginning
    let value = input.value;
    
    // Ensure only one decimal point
    const decimalCount = (value.match(/\./g) || []).length;
    if (decimalCount > 1) {
        const parts = value.split('.');
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Ensure minus sign is only at the beginning
    if (value.lastIndexOf('-') > 0) {
        value = value.replace(/-/g, '');
        if (value.charAt(0) !== '-') {
            value = '-' + value;
        }
    }
    
    input.value = value;
    
    const latitudeRegex = /^-?([1-8]?[0-9]\.{1}\d{1,9}$|90\.{1}0{1,9}$)/;
    
    if (value === '') {
        showValidationMessage(input, false, 'Latitude is required');
    } else if (!latitudeRegex.test(value)) {
        showValidationMessage(input, false, `
            Please enter a valid latitude:
            <ul class="mt-1 mb-0">
                <li>Must be between -90 and 90 degrees</li>
                <li>Must include decimal point</li>
                <li>Up to 9 decimal places</li>
                <li>Example: 23.456789803</li>
            </ul>
        `);
    } else {
        showValidationMessage(input, true, '');
    }
}

function validateLongitude(input) {
    // Force numeric input by immediately replacing non-numeric characters
    input.value = input.value.replace(/[^0-9.-]/g, '');
    
    // Allow only one decimal point and ensure minus sign is only at the beginning
    let value = input.value;
    
    // Ensure only one decimal point
    const decimalCount = (value.match(/\./g) || []).length;
    if (decimalCount > 1) {
        const parts = value.split('.');
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Ensure minus sign is only at the beginning
    if (value.lastIndexOf('-') > 0) {
        value = value.replace(/-/g, '');
        if (value.charAt(0) !== '-') {
            value = '-' + value;
        }
    }
    
    input.value = value;
    
    const longitudeRegex = /^-?([1-9]?[0-9]\.{1}\d{1,9}$|1[0-7][0-9]\.{1}\d{1,9}$|180\.{1}0{1,9}$)/;
    
    if (value === '') {
        showValidationMessage(input, false, 'Longitude is required');
    } else if (!longitudeRegex.test(value)) {
        showValidationMessage(input, false, `
            Please enter a valid longitude:
            <ul class="mt-1 mb-0">
                <li>Must be between -180 and 180 degrees</li>
                <li>Must include decimal point</li>
                <li>Up to 9 decimal places</li>
                <li>Example: 78.123456658</li>
            </ul>
        `);
    } else {
        showValidationMessage(input, true, '');
    }
}

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
        var userCountry = "{{ in_array(auth()->user()->role_id, [11,20,35,78,120,130,132,133,135,136,137,138,139,140]) ? auth()->user()->country : '' }}";
        var dmcId = "{{ in_array(auth()->user()->role_id, [11,20,35,78,120,130,132,133,135,136,137,138,139,140]) ? auth()->user()->userId : '' }}";
        
        // Initialize Select2 for country & city
        $('#country').select2({
            placeholder: "Search and Select Country",
            allowClear: true,
            width: '100%'
        });
        
        // Initialize Select2 for city (disabled until country is selected)
        $('#citySelect').select2({
            placeholder: "Select Country First",
            allowClear: true,
            width: '100%',
            disabled: true
        });
        
        // Initialize Select2 for Ownership
        $('#owned_by').select2({
            placeholder: "Search and Select Ownership",
            allowClear: true,
            width: '100%'
        });
        
        // Check if the user role corresponds to DMC-like roles
        if ([11,20,35,78,120,139,140].includes(userRoleId)) {
            // Hide the DMC select box
            $('#dmc-container').hide();
            $('#dmc').prop('required', false);
            
            // Auto-fill the country field with the DMC's country
            $('#country').val(userCountry).trigger('change');
            
            // Load cities for this DMC
            loadCitiesForDmc(dmcId);
        } 
        // Check if the user role is admin or similar roles
        else if ([1, 2, 3, 4].includes(userRoleId)) {
            $('#dmc-container').show();
            $('#dmc').prop('required', true);
            
            // When DMC is changed (for admin users)
            $('#dmc').change(function() {
                var dmcId = $(this).val();
                if (dmcId) {
                    loadCitiesForDmc(dmcId);
                } else {
                    // Clear city select and country, disable city field
                    $('#citySelect').prop('disabled', true).empty().append('<option value="">Select Country First</option>').trigger('change');
                    $('#country').val('').trigger('change');
                }
            });
        } 
        // For other roles
        else {
            $('#dmc-container').hide();
            $('#dmc').prop('required', false);
        }
        
        // Function to load cities for DMC
        function loadCitiesForDmc(dmcId) {
            if (dmcId) {
                // Show loading state
                $('#citySelect').prop('disabled', true).empty().append('<option value="">Loading cities...</option>').trigger('change');
                
                // Add a debug statement
                console.log("Loading cities for DMC ID:", dmcId);
                
                $.ajax({
                    url: "{{ route('fetch.cities_countries') }}",
                    type: "GET",
                    data: { dmc_id: dmcId },
                    dataType: 'json',
                    success: function(response) {
                        console.log("Response received:", response);
                        
                        // Clear loading state
                        $('#citySelect').empty();
                        
                        // Add default option
                        $('#citySelect').append('<option value="">Select a City</option>');
                        
                        // Add cities from response
                        if (response.cities && response.cities.length > 0) {
                            $.each(response.cities, function(key, city) {
                                $('#citySelect').append('<option value="' + city.name + '">' + city.name + '</option>');
                            });
                            // Enable city field when cities are loaded
                            $('#citySelect').prop('disabled', false);
                        } else {
                            // No cities found, keep disabled
                            $('#citySelect').append('<option value="">No cities available</option>');
                        }
                        
                        // Set the country value
                        if (response.country) {
                            $('#country').val(response.country).trigger('change');
                        }

                        // Trigger change to refresh Select2
                        $('#citySelect').trigger('change');
                    },
                    error: function(xhr, status, error) {
                        console.error("Error loading cities:", error);
                        console.log("XHR Status:", xhr.status);
                        console.log("Response:", xhr.responseText);
                        
                        $('#citySelect').prop('disabled', true).empty().append('<option value="">Error loading cities</option>').trigger('change');
                    }
                });
            } else {
                // DMC cleared, disable city field
                $('#citySelect').prop('disabled', true).empty().append('<option value="">Select Country First</option>').trigger('change');
            }
        }

        // -----------------------------------------------------------
        // Helper: load cities when country is chosen (non-DMC flow)
        function loadCitiesByCountry(countryName) {
            if (!countryName) {
                // Disable city field if no country is selected
                $('#citySelect').prop('disabled', true).empty().append('<option value="">Select Country First</option>').trigger('change');
                return;
            }
            $('#citySelect').prop('disabled', true).empty().append('<option value="">Loading cities...</option>').trigger('change');

            $.ajax({
                url: "{{ env('APP_URL') }}{{ route('fetch-cities-by-country', [], false) }}",
                type: "GET",
                data: { country: countryName },
                dataType: 'json',
                success: function(res){
                    $('#citySelect').empty().append('<option value="">Select a City</option>');
                    if (res.cities && res.cities.length){
                        $.each(res.cities, function(i, ct){
                            $('#citySelect').append('<option value="'+ct.name+'">'+ct.name+'</option>');
                        });
                        // Enable city field when cities are loaded
                        $('#citySelect').prop('disabled', false);
                    } else {
                        // No cities found, keep disabled
                        $('#citySelect').append('<option value="">No cities available</option>');
                    }
                    $('#citySelect').trigger('change');
                },
                error: function(){
                    $('#citySelect').prop('disabled', true).empty().append('<option value="">Error loading cities</option>').trigger('change');
                }
            });
        }

        // when country changes by user
        $('#country').on('change', function(){
            const selectedCountryValue = $(this).val();
            
            if (!selectedCountryValue || selectedCountryValue === '') {
                // Country cleared, disable city field
                $('#citySelect').prop('disabled', true).empty().append('<option value="">Select Country First</option>').trigger('change');
                return;
            }
            
            loadCitiesByCountry(selectedCountryValue);
        });

        // initial load if country pre-selected
        if ($('#country').val()) {
            loadCitiesByCountry($('#country').val());
        }

       

    }); 
     // password show/hide toggle functionality  
  function togglePassword() {
        const passwordField = document.getElementById('password');
        console.log(passwordField);
        const eyeBtn = document.getElementById('togglePasswordBtn');

if (passwordField.type === 'password') {
    passwordField.type = 'text';
    eyeBtn.innerHTML = '<i id="eyeIcon" class="bi bi-eye"></i>';      // open eye
} else {
    passwordField.type = 'password';
    eyeBtn.innerHTML = ' <i id="eyeIcon" class="bi bi-eye-slash"></i>';      // closed eye
}
}
    
    // password auto generate functionality
    function generatePassword() {
        const length = 10;
        const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#$!";
        let password = "";

        for (let i = 0; i < length; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }

        const passwordField = document.getElementById('password');
        passwordField.value = password;
        passwordField.type = 'text'; // show generated password
    }
</script>

@endsection