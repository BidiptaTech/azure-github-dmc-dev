@extends('layouts.layout')

@section('css')
<!-- Include necessary CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

<style>
    /* Select2 Styling */
    .select2-container .select2-selection--multiple {
        height: auto !important;
        line-height: 1.5;
        padding: 8px 12px;
        border-radius: 0.375rem;
        border-color: #d9dee3;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #696cff;
        box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.25);
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #696cff;
        border: 1px solid #696cff;
        color: #fff;
        border-radius: 0.25rem;
        padding: 2px 8px;
        margin-right: 5px;
        margin-top: 2px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
        margin-right: 5px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #fff;
        opacity: 0.8;
    }
    .select2-container .select2-search--inline .select2-search__field {
        margin-top: 3px;
    }
    .select2-container .select2-results__option {
        padding: 12px 10px;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #696cff;
    }
    .select2-dropdown {
        border-color: #d9dee3;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border-radius: 4px;
        padding: 8px;
        border-color: #d9dee3;
    }
    /* Limit dropdown height */
    .select2-results__options {
        max-height: 250px;
        overflow-y: auto;
    }
    /* End of Select2 styling */
    
    .attraction-preview {
        background: #f8f9fa;
        border-radius: 0.375rem;
        padding: 15px;
        margin-top: 15px;
        border: 1px solid #d9dee3;
    }
    .attraction-preview-item {
        display: flex;
        align-items: center;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 0.375rem;
        background: #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    .attraction-preview-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .attraction-preview-item img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 0.25rem;
        margin-right: 15px;
    }
    .attraction-preview-item .attraction-info {
        flex-grow: 1;
    }
    .image-preview-container {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 15px;
    }
    .image-preview-item {
        position: relative;
        width: 80px;
        height: 80px;
        border-radius: 0.375rem;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    .image-preview-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }
    .image-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .image-preview-item .remove-image {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(255,255,255,0.8);
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #dc3545;
        transition: all 0.2s ease;
    }
    .image-preview-item .remove-image:hover {
        background: rgba(255,255,255,1);
        transform: scale(1.1);
    }
    .custom-file-upload {
        display: inline-block;
        padding: 10px 20px;
        cursor: pointer;
        background: linear-gradient(45deg, #696cff, #8083ff);
        color: white;
        border-radius: 0.375rem;
        transition: all 0.3s ease;
    }
    .custom-file-upload:hover {
        background: linear-gradient(45deg, #5d60ff, #7073ff);
        transform: translateY(-2px);
    }
    .form-label strong {
        color: #566a7f;
    }
    .card {
        border: none;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
        border-radius: 0.5rem;
    }
    .card-header {
        background: linear-gradient(45deg, #696cff, #8083ff);
        color: white;
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }
    .btn-primary {
        background: linear-gradient(45deg, #696cff, #8083ff);
        border: none;
    }
    .btn-primary:hover {
        background: linear-gradient(45deg, #5d60ff, #7073ff);
    }
    .price-input {
        position: relative;
    }
    .price-input::before {
        content: '$';
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #566a7f;
        z-index: 10;
        pointer-events: none;
    }
    .price-input input {
        padding-left: 25px;
    }
    .form-control:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.25);
    }
</style>
@endsection

@section('content')
<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Create Packaged Attraction
                <a href="{{ route('packaged-attractions.index') }}" class="btn btn-sm btn-outline-light">
                    <i class="mdi mdi-arrow-left me-1"></i> Back
                </a>
            </h5>
            <x-alert />
            <form id="packagedAttractionForm" method="POST" action="{{ route('packaged-attractions.store') }}"
                enctype="multipart/form-data" class="card-body">
                @csrf
                
                <div class="row">
                    <!-- Package Attraction ID -->
                    <div class="col-md-6 mb-3">
                        <label for="package_attraction_name" class="form-label">
                            <strong>Package Name</strong><span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="package_attraction_name" name="package_attraction_name" 
                               placeholder="Enter Package Name" required>
                        @error('package_attraction_name')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Status -->
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">
                            <strong>Status</strong>
                        </label>
                        <select class="form-select" id="status" name="status">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        @error('status')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row">
                    <!-- Attractions Selection -->
                    <div class="col-md-12 mb-3">
                        <label for="attractionsSelect" class="form-label">
                            <strong>Select Attractions</strong><span class="text-danger">*</span>
                        </label>
                        <select name="attractions[]" id="attractionsSelect" class="form-select" multiple required>
                            <option value="">Select Attractions</option>
                            @foreach($attractions ?? [] as $attraction)
                                <option data-image="{{ $attraction->master_image }}" value="{{ $attraction->id }}">{{ $attraction->name }}</option>
                            @endforeach
                        </select>
                        @error('attractions')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Attractions Preview -->
                <div class="attraction-preview mb-4" id="attractionPreview">
                    <h6 class="mb-3">Selected Attractions</h6>
                    <div id="selectedAttractions" class="selected-attractions">
                        <div class="text-muted text-center py-3">No attractions selected</div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Senior Citizen Price -->
                    <div class="col-md-4 mb-3">
                        <label for="senior_citizen_price" class="form-label">
                            <strong>Senior Citizen Price</strong><span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control" id="senior_citizen_price" 
                                   name="senior_citizen_price" placeholder="0.00" required>
                        </div>
                        @error('senior_citizen_price')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Adult Price -->
                    <div class="col-md-4 mb-3">
                        <label for="adult_price" class="form-label">
                            <strong>Adult Price</strong><span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control" id="adult_price" 
                                   name="adult_price" placeholder="0.00" required>
                        </div>
                        @error('adult_price')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Child Price -->
                    <div class="col-md-4 mb-3">
                        <label for="child_price" class="form-label">
                            <strong>Child Price</strong><span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control" id="child_price" 
                                   name="child_price" placeholder="0.00" required>
                        </div>
                        @error('child_price')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Description -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="description" class="form-label">
                            <strong>Description</strong>
                        </label>
                        <textarea class="form-control" id="description" name="description" 
                                  rows="5" placeholder="Enter package description"></textarea>
                        @error('description')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Image Upload -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="images" class="form-label">
                            <strong>Images</strong>
                        </label>
                        <div class="input-group">
                            <label class="custom-file-upload">
                                <input type="file" id="images" name="images[]" class="d-none" multiple accept="image/*">
                                <i class="fas fa-cloud-upload-alt me-2"></i> Choose Images
                            </label>
                            <span id="file-chosen" class="ms-3 align-self-center text-muted">No files selected</span>
                        </div>
                        <small class="text-muted">You can select multiple images</small>
                        @error('images')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Image Preview -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="image-preview-container" id="imagePreviewContainer">
                            <!-- Image previews will be added here dynamically -->
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="row">
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Create Packaged Attraction
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Include necessary JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize Select2
        if (typeof $.fn.select2 !== 'undefined') {
            $("#attractionsSelect").select2({
                theme: 'bootstrap-5',
                placeholder: "Search and Select Attractions",
                allowClear: true,
                width: '100%'
            });
        } else {
            console.error("Select2 plugin is not available");
        }
        
        // Initialize Summernote
        if (typeof $.fn.summernote !== 'undefined') {
            $('#description').summernote({
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        } else {
            console.error("Summernote plugin is not available");
        }
        
        // Handle attraction selection change
        $('#attractionsSelect').on('change', function() {
            updateAttractionPreview();
        });
        
        // Handle file input change for image preview
        $('#images').on('change', function(e) {
            const fileInput = e.target;
            const fileCount = fileInput.files.length;
            
            // Update file chosen text
            $('#file-chosen').text(fileCount > 0 ? `${fileCount} file(s) selected` : 'No files selected');
            
            // Clear previous previews
            $('#imagePreviewContainer').empty();
            
            // Create previews for each selected file
            if (fileCount > 0) {
                for (let i = 0; i < fileCount; i++) {
                    const file = fileInput.files[i];
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const preview = $(`
                            <div class="image-preview-item">
                                <img src="${e.target.result}" alt="Preview">
                                <div class="remove-image" data-index="${i}">
                                    <i class="fas fa-times"></i>
                                </div>
                            </div>
                        `);
                        
                        $('#imagePreviewContainer').append(preview);
                    }
                    
                    reader.readAsDataURL(file);
                }
            }
        });
        
        // Handle removing images from preview
        $(document).on('click', '.remove-image', function() {
            const index = $(this).data('index');
            const container = $(this).closest('.image-preview-item');
            
            // Remove the preview
            container.remove();
            
            // Create a new FileList without the removed file
            // Note: FileList is immutable, so we need to recreate the input
            const input = document.getElementById('images');
            const dt = new DataTransfer();
            
            // Add all files except the one to be removed
            for (let i = 0; i < input.files.length; i++) {
                if (i !== index) {
                    dt.items.add(input.files[i]);
                }
            }
            
            // Update the input files
            input.files = dt.files;
            
            // Update file chosen text
            const fileCount = input.files.length;
            $('#file-chosen').text(fileCount > 0 ? `${fileCount} file(s) selected` : 'No files selected');
            
            // Reindex remaining remove buttons
            $('.remove-image').each(function(idx) {
                $(this).data('index', idx);
            });
        });
        
        // Form validation
        $('#packagedAttractionForm').on('submit', function(e) {
            const attractions = $('#attractionsSelect').val();
            if (!attractions || attractions.length === 0) {
                e.preventDefault();
                alert('Please select at least one attraction');
                return false;
            }
            
            return true;
        });
    });
    
    function updateAttractionPreview() {
        const selectedOptions = $('#attractionsSelect option:selected');
        const previewContainer = $('#selectedAttractions');
        
        // Clear previous content
        previewContainer.empty();
        
        if (selectedOptions.length === 0) {
            previewContainer.html('<div class="text-muted text-center py-3">No attractions selected</div>');
            return;
        }
        
        // Add each selected attraction to the preview
        selectedOptions.each(function() {
            const attractionId = $(this).val();
            const attractionName = $(this).text();
            const attractionImage = $(this).data('image');
            const previewItem = $(`
                <div class="attraction-preview-item" data-id="${attractionId}">
                    <img src="${attractionImage}" alt="${attractionName}">
                    <div class="attraction-info">
                        <h6 class="mb-1">${attractionName}</h6>
                        <p class="text-muted mb-0">ID: ${attractionId}</p>
                    </div>
                </div>
            `);
            
            previewContainer.append(previewItem);
        });
    }
</script>
@endsection
