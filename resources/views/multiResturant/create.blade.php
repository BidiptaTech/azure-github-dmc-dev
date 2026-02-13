@extends('layouts.layout')

@section('css')
<!-- Include necessary CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
    .select2-results__options {
        max-height: 250px;
        overflow-y: auto;
    }

    .restaurant-preview {
        background: #f8f9fa;
        border-radius: 0.375rem;
        padding: 15px;
        margin-top: 15px;
        border: 1px solid #d9dee3;
    }
    .restaurant-preview-item {
        display: flex;
        align-items: center;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 0.375rem;
        background: #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    .restaurant-preview-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .restaurant-preview-item img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 0.25rem;
        margin-right: 15px;
    }
    .restaurant-preview-item .restaurant-info {
        flex-grow: 1;
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
    .form-control:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.25);
    }
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Create Multi Restaurant
                <a href="{{ route('multiResturant.index') }}" class="btn btn-sm btn-outline-light">
                    <i class="mdi mdi-arrow-left me-1"></i> Back
                </a>
            </h5>
            <x-alert />
            <form id="multiRestaurantForm" method="POST" action="{{ route('multiResturant.store') }}" class="card-body">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="package_name" class="form-label">
                            <strong>Package Name</strong><span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="package_name" name="package_name"
                               placeholder="Enter Package Name" required>
                        @error('package_name')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

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
                    <div class="col-md-12 mb-3">
                        <label for="restaurantsSelect" class="form-label">
                            <strong>Select Restaurants</strong><span class="text-danger">*</span>
                        </label>
                        <select name="restaurants[]" id="restaurantsSelect" class="form-select" multiple required>
                            <option value="">Select Restaurants</option>
                            @foreach($restaurants ?? [] as $restaurant)
                                <option data-image="{{ $restaurant->master_image ?? '' }}" value="{{ $restaurant->restaurant_id }}">{{ $restaurant->name }}</option>
                            @endforeach
                        </select>
                        @error('restaurants')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="restaurant-preview mb-4" id="restaurantPreview">
                    <h6 class="mb-3">Selected Restaurants</h6>
                    <div id="selectedRestaurants" class="selected-restaurants">
                        <div class="text-muted text-center py-3">No restaurants selected</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="price" class="form-label">
                            <strong>Price</strong><span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control" id="price"
                                   name="price" placeholder="0.00" required>
                        </div>
                        @error('price')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Create Multi Restaurant
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        if (typeof $.fn.select2 !== 'undefined') {
            $("#restaurantsSelect").select2({
                theme: 'bootstrap-5',
                placeholder: "Search and Select Restaurants",
                allowClear: true,
                width: '100%'
            });
        }

        $('#restaurantsSelect').on('change', function() {
            updateRestaurantPreview();
        });

        $('#multiRestaurantForm').on('submit', function(e) {
            var restaurants = $('#restaurantsSelect').val();
            if (!restaurants || restaurants.length === 0) {
                e.preventDefault();
                alert('Please select at least one restaurant');
                return false;
            }
            return true;
        });
    });

    function updateRestaurantPreview() {
        var selectedOptions = $('#restaurantsSelect option:selected');
        var previewContainer = $('#selectedRestaurants');
        previewContainer.empty();
        if (selectedOptions.length === 0) {
            previewContainer.html('<div class="text-muted text-center py-3">No restaurants selected</div>');
            return;
        }
        selectedOptions.each(function() {
            var id = $(this).val();
            var name = $(this).text();
            var img = $(this).data('image') || '';
            var src = img ? (img.indexOf('http') === 0 || img.indexOf('/') === 0 ? img : ('/' + img)) : 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60"><rect fill="%23ddd" width="60" height="60"/><text x="50%" y="50%" fill="%23999" text-anchor="middle" dy=".3em" font-size="10">No img</text></svg>';
            var html = '<div class="restaurant-preview-item" data-id="' + id + '"><img src="' + src + '" alt="' + name + '"><div class="restaurant-info"><h6 class="mb-1">' + name + '</h6><p class="text-muted mb-0">ID: ' + id + '</p></div></div>';
            previewContainer.append(html);
        });
    }
</script>
@endsection
