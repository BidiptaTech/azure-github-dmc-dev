@extends('layouts.layout')

@section('title', 'Edit Default Value')

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
        <span class="text-muted fw-light">Default Values /</span> Edit
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
            <h5>Edit Default Value - {{ $defaultValue->getServiceTypeDisplayName() }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('default-values.update', Crypt::encrypt($defaultValue->id)) }}" method="POST" id="defaultValueForm">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Service Type</label>
                        <input type="text" class="form-control" value="{{ $defaultValue->getServiceTypeDisplayName() }}" readonly>
                        <small class="text-muted">Service type cannot be changed.</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="1" {{ old('status', $defaultValue->status) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $defaultValue->status) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                        <select name="country" id="country" class="form-select @error('country') is-invalid @enderror" required>
                            <option value="">Select Country</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->name }}" {{ old('country', $defaultValue->country) == $country->name ? 'selected' : '' }}>
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
                    <div class="col-md-12 mb-3">
                        <label for="service_id" class="form-label">Select {{ $defaultValue->getServiceTypeDisplayName() }} <span class="text-danger">*</span></label>
                        <select name="service_id" id="service_id" class="form-select default-value-service-select @error('service_id') is-invalid @enderror" data-placeholder="Search and select" required>
                            <option value="">Select Service</option>
                            @foreach($services as $service)
                                <option value="{{ $service['id'] }}" {{ old('service_id', $defaultValue->service_id) == $service['id'] ? 'selected' : '' }}>
                                    {{ $service['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('service_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i>Update Default Value
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
    const getServicesUrl = @json(route('default-values.get-services'));
    const serviceType = @json($defaultValue->name);
    const initialCity = @json(old('city', $defaultValue->city));
    const initialServiceId = @json(old('service_id', $defaultValue->service_id));

    const $country = $('#country');
    const $city = $('#city');
    const $serviceSelect = $('#service_id');

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
        (citiesByCountry[selectedCountry] || []).forEach(function(c) {
            const opt = $('<option></option>').val(c.name).text(c.name);
            if (preferredCity && preferredCity === c.name) opt.prop('selected', true);
            $city.append(opt);
        });
        initCitySelect2(preferredCity || '');
    }

    function loadServices(preserveId) {
        const country = $country.val();
        const city = $city.val();
        if (!country || !city) return;

        $serviceSelect.prop('disabled', true).empty().append('<option value="">Loading...</option>');
        initSelect2();

        $.get(getServicesUrl, { type: serviceType, country: country, city: city })
            .done(function(services) {
                $serviceSelect.empty().append('<option value="">Select Service</option>');
                (services || []).forEach(function(s) {
                    const opt = $('<option></option>').val(s.id).text(s.name);
                    if (preserveId && String(preserveId) === String(s.id)) {
                        opt.prop('selected', true);
                    }
                    $serviceSelect.append(opt);
                });
                $serviceSelect.prop('disabled', false);
                initSelect2();
            });
    }

    $country.on('change', function() {
        populateCities($country.val(), '');
        loadServices(null);
    });
    $city.on('change', function() {
        loadServices(null);
    });

    if ($country.val()) {
        populateCities($country.val(), initialCity);
    } else {
        initCitySelect2('');
    }
    initSelect2();
});
</script>
@endpush
@endsection
