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
    select#city.is-invalid + .select2-container .select2-selection--single,
    select#country.is-invalid + .select2-container .select2-selection--single {
        border-color: #dc3545 !important;
    }

    .zone-type-options {
        min-height: 50px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.15rem;
        padding-top: 0.1rem;
    }

    .zone-type-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem 1rem;
    }

    .zone-type-options .form-check {
        margin-bottom: 0;
        min-height: auto;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .zone-type-options .form-check-input {
        width: 0.85rem;
        height: 0.85rem;
        margin-top: 0;
        margin-right: 0;
        flex-shrink: 0;
    }

    .zone-type-options .form-check-label {
        font-size: 0.78rem;
        line-height: 1.1;
        color: #697a8d;
        margin-bottom: 0;
        cursor: pointer;
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
                                <label class="form-label">Zone Type <span class="text-danger">*</span></label>
                                <div class="zone-type-options">
                                    @php($oldZoneTypes = (array) old('zone_type', []))
                                    <div class="zone-type-row">
                                        <div class="form-check">
                                            <input
                                                class="form-check-input @error('zone_type') is-invalid @enderror"
                                                type="checkbox"
                                                id="zone_type_hotel"
                                                name="zone_type[]"
                                                value="Hotel"
                                                {{ in_array('Hotel', $oldZoneTypes, true) ? 'checked' : '' }}
                                            >
                                            <label class="form-check-label" for="zone_type_hotel">Hotel</label>
                                        </div>

                                        <div class="form-check">
                                            <input
                                                class="form-check-input @error('zone_type') is-invalid @enderror"
                                                type="checkbox"
                                                id="zone_type_attraction"
                                                name="zone_type[]"
                                                value="Attraction"
                                                {{ in_array('Attraction', $oldZoneTypes, true) ? 'checked' : '' }}
                                            >
                                            <label class="form-check-label" for="zone_type_attraction">Attraction</label>
                                        </div>
                                    </div>

                                    <div class="zone-type-row">
                                        <div class="form-check">
                                            <input
                                                class="form-check-input @error('zone_type') is-invalid @enderror"
                                                type="checkbox"
                                                id="zone_type_restaurant"
                                                name="zone_type[]"
                                                value="Restaurant"
                                                {{ in_array('Restaurant', $oldZoneTypes, true) ? 'checked' : '' }}
                                            >
                                            <label class="form-check-label" for="zone_type_restaurant">Restaurant</label>
                                        </div>
                                    </div>
                                </div>

                                @error('zone_type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @error('zone_type.*')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            @if(!empty($isAdmin))
                            <div class="col-md-3">
                                <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                                <select class="form-select @error('country') is-invalid @enderror" id="country" name="country" required>
                                    <option value=""></option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->name }}" {{ old('country') === $country->name ? 'selected' : '' }}>{{ $country->name }}</option>
                                    @endforeach
                                </select>
                                @error('country')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            @endif

                            <div class="col-md-3">
                                <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                <select class="form-select @error('city') is-invalid @enderror" id="city" name="city" required>
                                    <option value=""></option>
                                    @if(empty($isAdmin))
                                        @foreach($city as $c)
                                            <option value="{{ $c->city_id }}" {{ (string) old('city') === (string) $c->city_id ? 'selected' : '' }}>{{ $c->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('city')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
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

        const isAdminZoneCreate = @json(!empty($isAdmin));
        const oldCityId = @json(old('city'));
        const oldCountry = @json(old('country'));

        function initCitySelect2(placeholder) {
            if ($('#city').hasClass('select2-hidden-accessible')) {
                $('#city').select2('destroy');
            }
            $('#city').select2({
                placeholder: placeholder || 'Search and Select a City',
                allowClear: true,
                width: '100%'
            });
        }

        function loadCitiesByCountry(countryName, selectedCityId) {
            if (!countryName) {
                $('#city').html('<option value=""></option>');
                initCitySelect2('Select country first');
                return;
            }

            $('#city').html('<option value="">Loading cities...</option>');
            initCitySelect2('Loading cities...');

            $.ajax({
                url: "{{ route('get.cities.by.country') }}",
                type: 'GET',
                data: { country: countryName },
                dataType: 'json',
                success: function(response) {
                    const cities = response.cities || [];
                    let options = '<option value=""></option>';
                    if (cities.length === 0) {
                        options += '<option value="" disabled>No cities found</option>';
                    } else {
                        cities.forEach(function(city) {
                            const selected = selectedCityId && String(selectedCityId) === String(city.city_id) ? ' selected' : '';
                            options += '<option value="' + city.city_id + '"' + selected + '>' + city.name + '</option>';
                        });
                    }
                    $('#city').html(options);
                    initCitySelect2('Search and Select a City');
                },
                error: function() {
                    $('#city').html('<option value="">Error loading cities</option>');
                    initCitySelect2('Error loading cities');
                }
            });
        }

        if (isAdminZoneCreate) {
            $('#country').select2({
                placeholder: 'Search and Select Country',
                allowClear: true,
                width: '100%'
            });

            initCitySelect2('Select country first');

            $('#country').on('change', function() {
                loadCitiesByCountry($(this).val(), null);
            });

            if (oldCountry) {
                loadCitiesByCountry(oldCountry, oldCityId);
            }
        } else {
            initCitySelect2('Search and Select a City');
        }
    });
</script>
@endsection
