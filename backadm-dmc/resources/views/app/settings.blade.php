@extends('layouts.layout')

@section('title', 'App Management Settings')

@section('css')
<style>
    .info-card {
        background: #f8f9fa;
        border-left: 4px solid #007bff;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }
    .setting-label {
        font-weight: 600;
        color: #495057;
    }
    .help-text {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 5px;
    }
    .image-preview-container {
        margin-top: 10px;
    }
    .image-preview {
        max-width: 200px;
        max-height: 200px;
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 5px;
        background: #f8f9fa;
    }
    .current-image {
        margin-top: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #ddd;
    }
    .remove-image-btn {
        margin-top: 10px;
    }
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                <span><i class="mdi mdi-cog me-2"></i>App Management Settings</span>
            </h5>
            
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

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
                <form action="{{ route('app-management.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Image Upload Section -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="past_image" class="form-label setting-label">
                                <i class="mdi mdi-image me-1"></i>Past Image
                            </label>
                            
                            @if(isset($pastImage) && $pastImage)
                                <div class="current-image">
                                    <p class="mb-2"><strong>Current Image:</strong></p>
                                    @if(filter_var($pastImage, FILTER_VALIDATE_URL))
                                        <img src="{{ $pastImage }}" alt="Past" class="image-preview mb-2">
                                    @else
                                        <img src="{{ asset('storage/' . $pastImage) }}" alt="Past" class="image-preview mb-2">
                                    @endif
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remove_past_image" name="remove_past_image" value="1">
                                        <label class="form-check-label" for="remove_past_image">
                                            Remove current image
                                        </label>
                                    </div>
                                </div>
                            @endif
                            
                            <input 
                                type="file" 
                                class="form-control mt-2 @error('past_image') is-invalid @enderror" 
                                id="past_image" 
                                name="past_image" 
                                accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                            <small class="text-muted">JPG, JPEG, PNG, GIF, WEBP (Max: 2MB)</small>
                            @error('past_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <div class="image-preview-container" id="pastImagePreviewContainer" style="display: none;">
                                <p class="mb-2"><strong>New Image Preview:</strong></p>
                                <img src="" alt="Past Preview" class="image-preview" id="pastImagePreview">
                                <button type="button" class="btn btn-sm btn-danger remove-image-btn" id="removePastImageBtn">
                                    <i class="mdi mdi-delete me-1"></i>Remove New Image
                                </button>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="upcoming_image" class="form-label setting-label">
                                <i class="mdi mdi-image me-1"></i>Upcoming Image
                            </label>
                            
                            @if(isset($upcomingImage) && $upcomingImage)
                                <div class="current-image">
                                    <p class="mb-2"><strong>Current Image:</strong></p>
                                    @if(filter_var($upcomingImage, FILTER_VALIDATE_URL))
                                        <img src="{{ $upcomingImage }}" alt="Upcoming" class="image-preview mb-2">
                                    @else
                                        <img src="{{ asset('storage/' . $upcomingImage) }}" alt="Upcoming" class="image-preview mb-2">
                                    @endif
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remove_upcoming_image" name="remove_upcoming_image" value="1">
                                        <label class="form-check-label" for="remove_upcoming_image">
                                            Remove current image
                                        </label>
                                    </div>
                                </div>
                            @endif
                            
                            <input 
                                type="file" 
                                class="form-control mt-2 @error('upcoming_image') is-invalid @enderror" 
                                id="upcoming_image" 
                                name="upcoming_image" 
                                accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                            <small class="text-muted">JPG, JPEG, PNG, GIF, WEBP (Max: 2MB)</small>
                            @error('upcoming_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <div class="image-preview-container" id="upcomingImagePreviewContainer" style="display: none;">
                                <p class="mb-2"><strong>New Image Preview:</strong></p>
                                <img src="" alt="Upcoming Preview" class="image-preview" id="upcomingImagePreview">
                                <button type="button" class="btn btn-sm btn-danger remove-image-btn" id="removeUpcomingImageBtn">
                                    <i class="mdi mdi-delete me-1"></i>Remove New Image
                                </button>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="ongoing_image" class="form-label setting-label">
                                <i class="mdi mdi-image me-1"></i>Ongoing Image
                            </label>
                            
                            @if(isset($ongoingImage) && $ongoingImage)
                                <div class="current-image">
                                    <p class="mb-2"><strong>Current Image:</strong></p>
                                    @if(filter_var($ongoingImage, FILTER_VALIDATE_URL))
                                        <img src="{{ $ongoingImage }}" alt="Ongoing" class="image-preview mb-2">
                                    @else
                                        <img src="{{ asset('storage/' . $ongoingImage) }}" alt="Ongoing" class="image-preview mb-2">
                                    @endif
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remove_ongoing_image" name="remove_ongoing_image" value="1">
                                        <label class="form-check-label" for="remove_ongoing_image">
                                            Remove current image
                                        </label>
                                    </div>
                                </div>
                            @endif
                            
                            <input 
                                type="file" 
                                class="form-control mt-2 @error('ongoing_image') is-invalid @enderror" 
                                id="ongoing_image" 
                                name="ongoing_image" 
                                accept="image/jpeg,image/png,image/jpg,image/gif,webp">
                            <small class="text-muted">JPG, JPEG, PNG, GIF, WEBP (Max: 2MB)</small>
                            @error('ongoing_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <div class="image-preview-container" id="ongoingImagePreviewContainer" style="display: none;">
                                <p class="mb-2"><strong>New Image Preview:</strong></p>
                                <img src="" alt="Ongoing Preview" class="image-preview" id="ongoingImagePreview">
                                <button type="button" class="btn btn-sm btn-danger remove-image-btn" id="removeOngoingImageBtn">
                                    <i class="mdi mdi-delete me-1"></i>Remove New Image
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="mdi mdi-content-save me-1"></i>Save Settings
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="mdi mdi-refresh me-1"></i>Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Image preview functionality for Past
        $('#past_image').on('change', function(e) {
            handleImagePreview(e, 'pastImagePreview', 'pastImagePreviewContainer');
        });
        $('#removePastImageBtn').on('click', function() {
            $('#past_image').val('');
            $('#pastImagePreviewContainer').hide();
            $('#pastImagePreview').attr('src', '');
        });

        // Image preview functionality for Upcoming
        $('#upcoming_image').on('change', function(e) {
            handleImagePreview(e, 'upcomingImagePreview', 'upcomingImagePreviewContainer');
        });
        $('#removeUpcomingImageBtn').on('click', function() {
            $('#upcoming_image').val('');
            $('#upcomingImagePreviewContainer').hide();
            $('#upcomingImagePreview').attr('src', '');
        });

        // Image preview functionality for Ongoing
        $('#ongoing_image').on('change', function(e) {
            handleImagePreview(e, 'ongoingImagePreview', 'ongoingImagePreviewContainer');
        });
        $('#removeOngoingImageBtn').on('click', function() {
            $('#ongoing_image').val('');
            $('#ongoingImagePreviewContainer').hide();
            $('#ongoingImagePreview').attr('src', '');
        });

        // Common image preview handler
        function handleImagePreview(e, previewId, containerId) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (2MB max)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must not exceed 2MB');
                    $(e.target).val('');
                    $('#' + containerId).hide();
                    return;
                }
                
                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPG, JPEG, PNG, GIF, WEBP)');
                    $(e.target).val('');
                    $('#' + containerId).hide();
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#' + previewId).attr('src', e.target.result);
                    $('#' + containerId).show();
                }
                reader.readAsDataURL(file);
            }
        }
    });
</script>
@endsection