@extends('layouts.layout')

@section('title', 'Add Default Value')

@push('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    .default-value-service-select + .select2-container--default .select2-selection--single,
    #city + .select2-container--default .select2-selection--single {
        height: 38px !important;
        border: 1px solid #d9dee3 !important;
        border-radius: 0.375rem !important;
        display: flex !important;
        align-items: center !important;
    }
    .default-value-service-select + .select2-container--default .select2-selection--single .select2-selection__rendered,
    #city + .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 0.75rem !important;
        color: #697a8d !important;
    }
    .default-value-service-select + .select2-container--default .select2-selection--single .select2-selection__arrow,
    #city + .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #696cff !important;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Default Values /</span> Add New
    </h4>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Validation Error!</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5>Add Default Value</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-4">
                <i class="ri-information-line me-2"></i>
                Set one default per <strong>service type + country + city</strong> (e.g. Hotel for Singapore, Hotel for Batam).
                Once a type is saved for a city, you cannot add another for that same city — use Edit instead.
            </div>

            <form action="{{ route('default-values.store') }}" method="POST" id="defaultValueForm">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                        <select name="country" id="country" class="form-select @error('country') is-invalid @enderror" required>
                            <option value="">Select Country</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->name }}" {{ old('country') == $country->name ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                        <select name="city" id="city" class="form-select @error('city') is-invalid @enderror" data-placeholder="Search and select a city" required>
                            <option value="">Select City</option>
                        </select>
                        @error('city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Service Type <span class="text-danger">*</span></label>
                        <select name="name" id="name" class="form-select @error('name') is-invalid @enderror" required>
                            <option value="">Select Service Type</option>
                            @foreach($availableTypes as $type)
                                @php
                                    $typeLabels = [
                                        'hotel' => 'Hotel',
                                        'restaurant' => 'Restaurant',
                                        'attraction' => 'Attraction',
                                        'car_private' => 'Car (Private)',
                                        'car_shared' => 'Car (Shared)',
                                        'port' => 'Port',
                                        'guide' => 'Guide',
                                    ];
                                    $typeLabel = $typeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type));
                                @endphp
                                <option value="{{ $type }}" data-label="{{ $typeLabel }}" {{ old('name') == $type ? 'selected' : '' }}>
                                    {{ $typeLabel }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted" id="typeHint"></small>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="service_select" class="form-label">Select Service <span class="text-danger">*</span></label>
                        <select id="service_select" class="form-select default-value-service-select" data-placeholder="Search and select a service" disabled>
                            <option value="">Select country, city and service type first</option>
                        </select>
                        <input type="hidden" name="service_id" id="service_id" value="{{ old('service_id') }}">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i>Save Default Value
                    </button>
                    <a href="{{ route('default-values.index') }}" class="btn btn-secondary">
                        <i class="ri-arrow-left-line me-1"></i>Back
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    const citiesByCountry = @json($citiesByCountry ?? []);
    const existingDefaults = @json($existingDefaults ?? []);
    const getServicesUrl = @json(route('default-values.get-services'));
    const oldCity = @json(old('city', ''));
    const oldServiceId = @json(old('service_id', ''));

    const $country = $('#country');
    const $city = $('#city');
    const $name = $('#name');
    const $serviceSelect = $('#service_select');
    const $serviceId = $('#service_id');
    const $typeHint = $('#typeHint');

    function initSelect2() {
        if ($serviceSelect.data('select2')) {
            $serviceSelect.select2('destroy');
        }
        $serviceSelect.select2({
            placeholder: $serviceSelect.data('placeholder') || 'Search and select',
            allowClear: true,
            width: '100%'
        });
    }

    function initCitySelect2(preferredCity) {
        if ($city.data('select2')) {
            $city.select2('destroy');
        }
        $city.select2({
            placeholder: $city.data('placeholder') || 'Search and select a city',
            allowClear: true,
            width: '100%'
        });
        if (preferredCity) {
            $city.val(preferredCity).trigger('change.select2');
        }
    }

    function populateCities(selectedCountry, preferredCity) {
        $city.empty().append('<option value="">Select City</option>');
        const list = citiesByCountry[selectedCountry] || [];
        list.forEach(function(c) {
            const opt = $('<option></option>').val(c.name).text(c.name);
            if (preferredCity && preferredCity === c.name) {
                opt.prop('selected', true);
            }
            $city.append(opt);
        });
        initCitySelect2(preferredCity || '');
    }

    function isTypeTaken(type, country, city) {
        return existingDefaults.some(function(row) {
            return row.name === type
                && String(row.country || '') === String(country || '')
                && String(row.city || '') === String(city || '');
        });
    }

    function refreshTypeOptions() {
        const country = $country.val();
        const city = $city.val();
        $name.find('option').each(function() {
            const $opt = $(this);
            const type = $opt.val();
            if (!type) return;
            const taken = country && city && isTypeTaken(type, country, city);
            $opt.prop('disabled', !!taken);
            if (taken && $opt.is(':selected')) {
                $name.val('');
            }
        });
        if (country && city) {
            $typeHint.text('Types already set for ' + city + ' are disabled.');
        } else {
            $typeHint.text('Select country and city to see which types are still available.');
        }
    }

    function loadServices() {
        const type = $name.val();
        const country = $country.val();
        const city = $city.val();
        $serviceId.val('');

        if (!type || !country || !city) {
            $serviceSelect.prop('disabled', true).empty()
                .append('<option value="">Select country, city and service type first</option>');
            initSelect2();
            return;
        }

        if (isTypeTaken(type, country, city)) {
            $serviceSelect.prop('disabled', true).empty()
                .append('<option value="">Already configured for this city — edit existing</option>');
            initSelect2();
            return;
        }

        $serviceSelect.prop('disabled', true).empty()
            .append('<option value="">Loading...</option>');
        initSelect2();

        $.get(getServicesUrl, { type: type, country: country, city: city })
            .done(function(services) {
                $serviceSelect.empty().append('<option value="">Select Service</option>');
                (services || []).forEach(function(s) {
                    const opt = $('<option></option>').val(s.id).text(s.name);
                    if (oldServiceId && String(oldServiceId) === String(s.id)) {
                        opt.prop('selected', true);
                        $serviceId.val(s.id);
                    }
                    $serviceSelect.append(opt);
                });
                if (!services || !services.length) {
                    $serviceSelect.append('<option value="" disabled>No matching products for this city</option>');
                }
                $serviceSelect.prop('disabled', false);
                initSelect2();
            })
            .fail(function() {
                $serviceSelect.empty().append('<option value="">Failed to load services</option>');
                initSelect2();
            });
    }

    $country.on('change', function() {
        populateCities($country.val(), '');
        refreshTypeOptions();
        loadServices();
    });

    $city.on('change', function() {
        refreshTypeOptions();
        loadServices();
    });

    $name.on('change', loadServices);

    $serviceSelect.on('change select2:select select2:clear', function() {
        $serviceId.val($serviceSelect.val() || '');
    });

    // Init from old input / first load
    if ($country.val()) {
        populateCities($country.val(), oldCity);
    } else {
        initCitySelect2('');
    }
    refreshTypeOptions();
    loadServices();
    initSelect2();

    $('#defaultValueForm').on('submit', function(e) {
        if (!$country.val() || !$city.val() || !$name.val()) {
            e.preventDefault();
            alert('Please select country, city and service type.');
            return false;
        }
        if (isTypeTaken($name.val(), $country.val(), $city.val())) {
            e.preventDefault();
            alert('This service type is already set for the selected city.');
            return false;
        }
        $serviceId.val($serviceSelect.val() || '');
        if (!$serviceId.val()) {
            e.preventDefault();
            alert('Please select a service.');
            return false;
        }
    });
});
</script>
@endpush
@endsection
