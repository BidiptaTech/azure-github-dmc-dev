@extends('layouts.layout')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Edit Season
                <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form action="{{ route('upload.facility.image') }}" method="POST" enctype="multipart/form-data" class="card-body">
                @csrf
                <input type="hidden" id="hotel_id" name="hotel_id" value="{{ $hotel->hotel_unique_id }}">
                <input type="hidden" name="selected_facility_id" value="{{ $selectedFacilityId }}">

                <!-- Facility Information Section -->
                <fieldset class="p-4 border rounded shadow-sm">
                    <legend class="w-auto px-3 py-1 text-white bg-primary rounded">
                        <strong>Facility Information</strong>
                    </legend>

                    <div class="row">
                        <!-- Facility Name Section -->
                        <div class="mb-3 col-md-4">
                            <label for="name" class="form-label">
                                <strong>Name</strong>
                                <span style="color: red; font-weight: bold;">*</span>
                            </label>
                            <select style="pointer-events: none; background-color: #e9ecef;" name="name" id="name" class="form-control" readonly>
                                <option value="">Select a Name</option>
                                @foreach($facility as $fac)
                                    <option value="{{ $fac->facilityId }}" {{ (isset($selectedFacilityId) && $selectedFacilityId == $fac->facilityId) ? 'selected' : '' }}>
                                        {{ $fac->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Facility Images Section -->
                        <div class="col-md-8 mb-4">
                            <label for="images" class="form-label"><strong>Facility Images</strong></label>
                            <div id="drop-area" class="form-control d-flex justify-content-center align-items-center" style="padding: 20px; border: 2px dashed #007bff; height: 100px; cursor: pointer;">
                                <span>Drag & Drop your files here or click to upload.</span>
                                <input type="file" id="images" name="images[]" multiple style="display: none;">
                            </div>
                            <div id="preview-container" class="mt-3 d-flex flex-wrap gap-2" style="max-width: 100%; overflow-x: auto; white-space: nowrap;"></div>
                            <!-- Existing Image Section -->
                            <div class="image-preview-container d-flex flex-wrap gap-2">
                                @foreach($imagesForFacility as $img)
                                    <!-- Hidden input to hold existing image path -->
                                    
                                    <div class="image-preview-wrapper position-relative">
                                    <input type="hidden" name="existing_images[]" value="{{ $img }}">
                                        <img src="{{ asset($img) }}" alt="Facility Image" style="max-width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                                        <button class="delete-image-btn position-absolute top-0 end-0 btn btn-sm btn-danger" 
                                                data-image="{{ $img }}" 
                                                data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                style="width: 20px; height: 20px; line-height: 18px; padding: 0; text-align: center; font-size: 14px; z-index: 1;">
                                            &times;
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            <input type="file" name="all_images[]" id="all-images" style="display: none;"> 

                            @error('images')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                            <div id="preview-container" class="mt-3 d-flex flex-wrap gap-2" style="max-width: 100%; overflow-x: auto; white-space: nowrap;"></div>
                        </div>
                    </div>
                </fieldset>


                <!-- Submit Button -->
                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-primary px-4 py-2">
                            Update
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<!-- Image -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Add event listeners to all delete buttons
        document.querySelectorAll('.delete-image-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Find the image preview wrapper
                const imageWrapper = this.closest('.image-preview-wrapper');
                if (imageWrapper) {
                    // Find and remove the associated hidden input field for the image
                    const hiddenInput = imageWrapper.querySelector('input[type="hidden"]');
                    if (hiddenInput) {
                        hiddenInput.remove(); // Remove the hidden input
                    }
                    
                    // Remove the image wrapper (image and button)
                    imageWrapper.remove();
                }
            });
        });
    });
</script>

<!-- image upload -->
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
        files = [...files, ...Array.from(newFiles)];
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
            // deleteButton.style.top = '2px';
            // deleteButton.style.right = '2px';
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
    // Submit the form
    document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('upload-form');

    if (form) {  // Ensure form exists before adding event listener
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(form);

            // Check the hidden input to ensure all files are added
            console.log('Form Data: ', formData);

            // You can now send `formData` to the backend via an AJAX request (e.g., using fetch)
            fetch('/upload', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => console.log(data))
            .catch(error => console.error('Error:', error));
        });
    } else {
        //console.error("Form with ID 'upload-form' not found.");
    }
 });

</script>

@endsection