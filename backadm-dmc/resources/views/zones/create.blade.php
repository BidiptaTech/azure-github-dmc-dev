@extends('layouts.layout')

@section('title', 'Create Zone')

@section('content')
<style>
    /* Select2 — same integration as vehicles add-vehicle */
    .select2-container--default .select2-selection--single {
        height: 50px !important;
        border: 1px solid #d9dee3 !important;
        border-radius: 0.375rem !important;
        padding: 0.375rem 0.75rem !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 24px !important;
        padding: 0 !important;
        color: #697a8d !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 5px !important;
    }
    .select2-container--default .select2-selection--single:hover {
        border-color: #697a8d !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #696cff !important;
        box-shadow: 0 0 0.25rem rgba(105, 108, 255, 0.1) !important;
        outline: none !important;
    }
    .select2-dropdown {
        border: 1px solid #d9dee3 !important;
        border-radius: 0.375rem !important;
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1) !important;
    }
    .select2-container--default .select2-results__option {
        padding: 8px 12px !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #696cff !important;
        color: white !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #d9dee3 !important;
        border-radius: 0.375rem !important;
        padding: 0.375rem 0.75rem !important;
        outline: none !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: #696cff !important;
        box-shadow: 0 0 0.25rem rgba(105, 108, 255, 0.1) !important;
    }
    select#city.is-invalid + .select2-container .select2-selection--single {
        border-color: #dc3545 !important;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Zone /</span> Create Zone
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Add New Zone</h5>
                    <a href="{{ route('zones.index') }}" class="btn btn-secondary">Back to List</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('zones.store') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="zone_name" class="form-label">Zone Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('zone_name') is-invalid @enderror" id="zone_name" name="zone_name" value="{{ old('zone_name') }}" required>
                                @error('zone_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label for="zone_type" class="form-label">Zone Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('zone_type') is-invalid @enderror" id="zone_type" name="zone_type" required>
                                    <option value="">-- Select Zone Type --</option>
                                    <option value="Hotel" {{ old('zone_type') == 'Hotel' ? 'selected' : '' }}>Hotel</option>
                                    <option value="Attraction" {{ old('zone_type') == 'Attraction' ? 'selected' : '' }}>Attraction</option>
                                    <option value="Restaurant" {{ old('zone_type') == 'Restaurant' ? 'selected' : '' }}>Restaurant</option>
                                </select>
                                @error('zone_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="vehicle_type" class="form-label">Vehicle Type<span class="text-danger">*</span></label>
                                <select class="form-select @error('vehicle_type') is-invalid @enderror" id="vehicle_type" name="vehicle_type" required>
                                    <option value="">-- Select Vehicle Type --</option>
                                    <option value="Shared" {{ old('vehicle_type') == 'Shared' ? 'selected' : '' }}>Shared</option>
                                    <option value="Private" {{ old('vehicle_type') == 'Private' ? 'selected' : '' }}>Private</option>
                                    <option value="Both" {{ old('vehicle_type') == 'Both' ? 'selected' : '' }}>Both</option>
                                </select>
                                @error('vehicle_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                <select class="form-select @error('city') is-invalid @enderror" id="city" name="city" required>
                                    <option value=""></option>
                                    @foreach($city as $c)
                                        <option value="{{ $c->city_id }}" {{ (string) old('city') === (string) $c->city_id ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                @error('city')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="summernote" class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch">
                            <label for="status" class="form-label"><strong>Status</strong><span style="color: red; font-weight: bold;">*</span></label>
                            <input type="hidden" name="status" value="0">
                            <input class="form-check-input" name="status" type="checkbox" id="status" value="1" required>
                            <label class="form-check-label"></label>
                            @error('status')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Create Zone</button>
                            <a href="{{ route('zones.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 

@section('scripts')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js"></script>
<script>
    $(document).ready(function() {
        $('#summernote').summernote({
            height: 200,
            minHeight: 200,
            maxHeight: 500,
            placeholder: 'Enter your content here...',
        });

        $('#city').select2({
            placeholder: 'Search and Select a City',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection
