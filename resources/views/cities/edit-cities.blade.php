@extends('layouts.layout')
@section('title', 'Edit City')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endsection

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
    .image-preview-container {
        margin-top: 10px;
    }
    .image-preview {
        max-width: 300px;
        max-height: 300px;
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 5px;
        background: #f8f9fa;
    }
    .remove-image-btn {
        margin-top: 10px;
    }
    .current-image {
        margin-top: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #ddd;
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-4">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Edit City
                <a href="{{ route('cities.index') }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left me-1"></i> Back to List
                </a>
            </h5>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('cities.update', $city->city_id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                            <select class="form-select select2" id="country" name="country" required>
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" {{ old('country', $city->country) == $country->name ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('country')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">City Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                value="{{ old('name', $city->name) }}" required 
                                placeholder="Enter city name">
                            @error('name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="image" class="form-label">City Image</label>
                            
                            @if(isset($city->image) && $city->image)
                                <div class="current-image">
                                    <p class="mb-2"><strong>Current Image:</strong></p>
                                    @if(filter_var($city->image, FILTER_VALIDATE_URL))
                                        <img src="{{ $city->image }}" alt="{{ $city->name }}" class="image-preview mb-2">
                                    @else
                                        <img src="{{ asset('storage/' . $city->image) }}" alt="{{ $city->name }}" class="image-preview mb-2">
                                    @endif
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remove_existing_image" name="remove_existing_image" value="1">
                                        <label class="form-check-label" for="remove_existing_image">
                                            Remove current image
                                        </label>
                                    </div>
                                </div>
                            @endif
                            
                            <input type="file" class="form-control mt-2" id="image" name="image" 
                                accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                            <small class="text-muted">Accepted formats: JPG, JPEG, PNG, GIF, WEBP (Max: 2MB)</small>
                            @error('image')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                            
                            <div class="image-preview-container" id="imagePreviewContainer" style="display: none;">
                                <p class="mb-2"><strong>New Image Preview:</strong></p>
                                <img src="" alt="Image Preview" class="image-preview" id="imagePreview">
                                <button type="button" class="btn btn-sm btn-danger remove-image-btn" id="removeImageBtn">
                                    <i class="mdi mdi-delete me-1"></i>Remove New Image
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="ri-information-line me-2"></i>
                                <strong>Note:</strong> The system will check if the city already exists in the selected country (case-insensitive). 
                                If a city with the same name already exists, you'll receive an error message.
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary me-2">Update City</button>
                        <a href="{{ route('cities.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 for country dropdown
        $('#country').select2({
            theme: 'bootstrap-5',
            placeholder: "Search and Select Country",
            allowClear: true,
            width: '100%'
        });
        
        // Real-time validation for city name
        $('#name').on('input', function() {
            const cityName = $(this).val().trim();
            if (cityName.length > 0) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else {
                $(this).removeClass('is-valid').addClass('is-invalid');
            }
        });

        // Image preview functionality
        $('#image').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (2MB max)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must not exceed 2MB');
                    $(this).val('');
                    $('#imagePreviewContainer').hide();
                    return;
                }
                
                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPG, JPEG, PNG, GIF, WEBP)');
                    $(this).val('');
                    $('#imagePreviewContainer').hide();
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').attr('src', e.target.result);
                    $('#imagePreviewContainer').show();
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Remove new image
        $('#removeImageBtn').on('click', function() {
            $('#image').val('');
            $('#imagePreviewContainer').hide();
            $('#imagePreview').attr('src', '');
        });

        // Form validation
        $('form').on('submit', function(e) {
            const country = $('#country').val();
            const cityName = $('#name').val().trim();
            
            if (!country) {
                e.preventDefault();
                alert('Please select a country');
                $('#country').focus();
                return false;
            }
            
            if (!cityName) {
                e.preventDefault();
                alert('Please enter a city name');
                $('#name').focus();
                return false;
            }
        });
    });
</script>
@endsection
