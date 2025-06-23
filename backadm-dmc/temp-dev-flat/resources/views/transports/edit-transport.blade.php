@extends('layouts.layout')
@section('content')

<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Edit Local Transport Details
                <a href="{{ route('transport.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form id="guideForm" method="POST" action="{{ route('transport.update', $transport->transport_id) }}"
                enctype="multipart/form-data" class="card-body">
                @csrf
                @method('PUT')
                <!-- Hidden Fields -->

                <div id="transportDetailsContainer">
                    <div class="transport-form">
                        <div class="row">
                            <!-- Vehicle Name -->
                            <div class="col-md-3 mb-3">
                                <label for="vehicle_name" class="form-label"><strong>Vehicle Name</strong><span
                                        class="text-danger">*</span></label>
                                <input value="{{$transport->vehicle_name}}" id="vehicle_name" type="text"
                                    class="form-control" name="vehicle_name" placeholder="Enter Vehicle Name" required>
                                @error('vehicle_name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Driver Name-->
                            <div class="col-md-3 mb-3">
                                <label for="driver_name" class="form-label">
                                    <strong>Driver Name</strong><span class="text-danger">*</span>
                                </label>
                                <input value="{{$transport->driver_name}}" id="driver_name" name="driver_name"
                                    type="text" class="form-control" placeholder="Enter driver name" required></input>
                                @error('driver_name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- contact -->
                            <div class="col-md-3 mb-4">
                                <label for="contact_no" class="form-label"><strong>Contact No</strong><span
                                        class="text-danger">*</span></label>
                                <input value="{{$transport->contact_no}}" type="number" class="form-control"
                                    name="contact_no" placeholder="Enter contact no..." required>
                                @error('contact_no')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- License no-->
                            <div class="col-md-3 mb-3">
                                <label for="license_no" class="form-label">
                                    <strong>License No</strong><span class="text-danger">*</span>
                                </label>
                                <input value="{{$transport->license_no}}" id="license_no" name="license_no" type="text"
                                    class="form-control" placeholder="Enter license no" required></input>
                                @error('license_no')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- License Expiry Date -->
                            <div class="col-md-2 mb-3">
                                <label for="license_expiry" class="form-label"><strong>License Expiry
                                        Date:</strong><span class="text-danger">*</span></label>
                                <input value="{{$transport->license_expiry_date}}" type="date" id="expiry_date"
                                    name="expiry_date" class="form-control" required>
                                @error('expiry_date')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Vehicle Registration No -->
                            <div class="col-md-3 mb-3">
                                <label for="vehicle_reg_no" class="form-label"><strong>Vehicle Reg. No</strong><span
                                        class="text-danger">*</span></label>
                                <input value="{{$transport->vehicle_registration_no}}" id="vehicle_reg_no" type="text"
                                    class="form-control" name="vehicle_reg_no" placeholder="Enter vehicle reg. no..."
                                    required>
                                @error('vehicle_reg_no')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Vehicle No -->
                            <div class="col-md-3 mb-3">
                                <label for="vehicle_no" class="form-label"><strong>Vehicle No</strong><span
                                        class="text-danger">*</span></label>
                                <input value="{{$transport->vehicle_no}}" id="vehicle_no" type="text"
                                    class="form-control" name="vehicle_no" placeholder="Enter vehicle no..." required>
                                @error('vehicle_no')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="mt-2 form-check form-switch">
                            <label for="transport_status" class="form-label"><strong>Status</strong></label>
                            <span style="color: red; font-weight: bold;">*</span>
                            <input {{$transport->is_active == 1 ? 'checked' : ''}} class="form-check-input"
                                name="transport_status" type="checkbox" id="transport_status" value="1">
                            <label class="form-check-label"></label>
                            @error('transport_status')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="row mt-4">
                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
            </form>
        </div>
    </div>
</div>
<!-- End of the form -->
@endsection

@section('scripts')
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