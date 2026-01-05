@extends('layouts.layout')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endsection

@section('content')
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
</style>
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-4">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Edit Port: {{ $port->port_name }}
                <a href="{{ route('ports.index') }}" class="btn btn-secondary">
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

                <form action="{{ route('ports.update', $port->port_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="port_name" class="form-label">Port Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="port_name" name="port_name" value="{{ old('port_name', $port->port_name) }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Port Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="Airport" {{ old('type', $port->type) == 'Airport' ? 'selected' : '' }}>Airport</option>
                                <option value="Seaport" {{ old('type', $port->type) == 'Seaport' ? 'selected' : '' }}>Seaport</option>
                                <option value="LandPort" {{ old('type', $port->type) == 'LandPort' ? 'selected' : '' }}>Land Border Crossing</option>
                                <option value="Railway" {{ old('type', $port->type) == 'Railway' ? 'selected' : '' }}>Railway</option>
                                <option value="BusStand" {{ old('type', $port->type) == 'BusStand' ? 'selected' : '' }}>Bus Stand</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                            <select class="form-select select2" id="country" name="country" required>
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->name }}" {{ old('country', $port->country) == $country->name ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="city_id" class="form-label">City <span class="text-danger">*</span></label>
                            <select class="form-select select2" id="city_id" name="city_id" required>
                                <option value="">Select Country First</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="latitude" class="form-label">Latitude <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="latitude" name="latitude" value="{{ old('latitude', $port->latitude) }}" required placeholder="e.g. 23.456789">
                            <small class="text-muted">Must be between -90 and 90 degrees with decimal point</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="longitude" class="form-label">Longitude <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="longitude" name="longitude" value="{{ old('longitude', $port->longitude) }}" required placeholder="e.g. 78.123456">
                            <small class="text-muted">Must be between -180 and 180 degrees with decimal point</small>
                        </div>

                        {{-- <div class="col-md-4 mb-3">
                            <label for="distance" class="form-label">Distance (miles) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="distance" name="distance" value="{{ old('distance', $port->distance) }}" required placeholder="e.g. 25.5">
                            <small class="text-muted">Distance in miles from reference point</small>
                        </div> --}}

                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="status" name="status" value="1" {{ old('status', $port->status) == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="status">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary me-2">Update Port</button>
                        <a href="{{ route('ports.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
        
        // Initialize Select2 for city dropdown
        $('#city_id').select2({
            theme: 'bootstrap-5',
            placeholder: "Search or type a new city",
            allowClear: true,
            width: '100%',
            tags: true,
            createTag: function(params) {
                return {
                    id: 'new:' + params.term,
                    text: params.term,
                    isNew: true
                };
            }
        });

        // Function to load cities based on country selection
        function loadCities(countryId, selectedCityId = null) {
            if (!countryId) {
                $('#city_id').html('<option value="">Select Country First</option>');
                $('#city_id').trigger('change');
                return;
            }

            $.ajax({
                url: "{{ route('port.getCities') }}",
                type: "GET",
                data: {
                    country: countryId,
                    _token: "{{ csrf_token() }}"
                },
                dataType: "json",
                success: function(data) {
                    var options = '<option value="">Select or type new city</option>';
                    $.each(data, function(key, value) {
                        options += '<option value="' + value.city_id + '"';
                        if (value.city_id == selectedCityId) {
                            options += ' selected';
                        }
                        options += '>' + value.name + '</option>';
                    });
                    $('#city_id').html(options);
                    $('#city_id').trigger('change');
                }
            });
        }

        // Load cities when country changes
        $('#country').on('change', function() {
            var countryId = $(this).val();
            loadCities(countryId);
        });

        // Load cities on page load for the selected country
        var countryId = $('#country').val();
        var cityId = "{{ old('city_id', $port->city_id) }}";
        if (countryId) {
            loadCities(countryId, cityId);
        }

        // Input validation
        function validateLatitude(input) {
            const latitudeRegex = /^-?([1-8]?[0-9]\.{1}\d{1,9}$|90\.{1}0{1,9}$)/;
            return latitudeRegex.test(input.value);
        }

        function validateLongitude(input) {
            const longitudeRegex = /^-?([1-9]?[0-9]\.{1}\d{1,9}$|1[0-7][0-9]\.{1}\d{1,9}$|180\.{1}0{1,9}$)/;
            return longitudeRegex.test(input.value);
        }

        $('#latitude').on('input', function() {
            // Force numeric input with one decimal point
            this.value = this.value.replace(/[^0-9.-]/g, '');
            
            // Ensure only one decimal point
            const decimalCount = (this.value.match(/\./g) || []).length;
            if (decimalCount > 1) {
                const parts = this.value.split('.');
                this.value = parts[0] + '.' + parts.slice(1).join('');
            }
            
            // Ensure minus sign is only at the beginning
            if (this.value.lastIndexOf('-') > 0) {
                this.value = this.value.replace(/-/g, '');
                if (this.value.charAt(0) !== '-') {
                    this.value = '-' + this.value;
                }
            }
            
            if (validateLatitude(this)) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else {
                $(this).removeClass('is-valid').addClass('is-invalid');
            }
        });

        $('#longitude').on('input', function() {
            // Force numeric input with one decimal point
            this.value = this.value.replace(/[^0-9.-]/g, '');
            
            // Ensure only one decimal point
            const decimalCount = (this.value.match(/\./g) || []).length;
            if (decimalCount > 1) {
                const parts = this.value.split('.');
                this.value = parts[0] + '.' + parts.slice(1).join('');
            }
            
            // Ensure minus sign is only at the beginning
            if (this.value.lastIndexOf('-') > 0) {
                this.value = this.value.replace(/-/g, '');
                if (this.value.charAt(0) !== '-') {
                    this.value = '-' + this.value;
                }
            }
            
            if (validateLongitude(this)) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else {
                $(this).removeClass('is-valid').addClass('is-invalid');
            }
        });

        $('#distance').on('input', function() {
            // Force numeric input with one decimal point
            this.value = this.value.replace(/[^0-9.]/g, '');
            
            // Ensure only one decimal point
            const decimalCount = (this.value.match(/\./g) || []).length;
            if (decimalCount > 1) {
                const parts = this.value.split('.');
                this.value = parts[0] + '.' + parts.slice(1).join('');
            }
            
            const distanceRegex = /^[0-9]+(\.[0-9]{1,2})?$/;
            if (distanceRegex.test(this.value)) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else {
                $(this).removeClass('is-valid').addClass('is-invalid');
            }
        });
    });
</script>
@endsection 