@extends('layouts.layout')

@section('title', 'Edit Default Value')

@push('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    .default-value-page .workflow-card {
        border: 1px solid #e7eaf3;
        border-radius: 14px;
        box-shadow: 0 4px 14px rgba(31, 41, 55, 0.05);
    }
    .default-value-page .workflow-intro {
        background: linear-gradient(135deg, #f4f2ff 0%, #eef6ff 100%);
        border: 1px solid #e1dcff;
        border-radius: 14px;
        padding: 1rem 1.1rem;
        margin-bottom: 1rem;
    }
    .default-value-page .workflow-intro-title {
        font-size: 1rem;
        font-weight: 700;
        color: #3f3d56;
        margin-bottom: 0.35rem;
    }
    .default-value-page .workflow-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .default-value-page .workflow-step {
        border: 1px solid #ebeef5;
        border-radius: 12px;
        padding: 1rem;
        background: #fff;
        height: 100%;
    }
    .default-value-page .workflow-step-head {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.85rem;
    }
    .default-value-page .step-badge {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #696cff;
        color: #fff;
        font-weight: 700;
        font-size: 0.9rem;
        flex: 0 0 30px;
    }
    .default-value-page .workflow-step-title {
        font-weight: 700;
        color: #344054;
        margin: 0;
    }
    .default-value-page .workflow-step-subtitle {
        color: #98a2b3;
        font-size: 0.82rem;
        margin: 0.15rem 0 0;
    }
    .default-value-page .summary-strip {
        background: #f8faff;
        border: 1px dashed #c9d5ff;
        border-radius: 12px;
        padding: 0.9rem 1rem;
        margin-bottom: 1rem;
    }
    .default-value-page .summary-strip-label {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #98a2b3;
        margin-bottom: 0.4rem;
    }
    .default-value-page .summary-strip-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: #344054;
    }
    .default-value-page .locked-note {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: #f2f4f7;
        color: #667085;
        border-radius: 999px;
        padding: 0.25rem 0.7rem;
        font-size: 0.78rem;
        font-weight: 600;
    }
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
<div class="container-xxl flex-grow-1 container-p-y default-value-page">
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

    <div class="card workflow-card">
        <div class="card-header">
            <h5>Edit Default Value - {{ $defaultValue->getServiceTypeDisplayName() }}</h5>
        </div>
        <div class="card-body">
            <div class="workflow-intro">
                <div class="workflow-intro-title">Update Default Product Mapping</div>
                <div class="text-muted">
                    Keep the same service type, but move it to another market or point it to another product if the business default has changed.
                </div>
            </div>
            <form action="{{ route('default-values.update', Crypt::encrypt($defaultValue->id)) }}" method="POST" id="defaultValueForm">
                @csrf
                @method('PUT')

                <div class="summary-strip">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="summary-strip-label">Service Type</div>
                            <div class="summary-strip-value">{{ $defaultValue->getServiceTypeDisplayName() }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-strip-label">Current Market</div>
                            <div class="summary-strip-value" id="summaryLocation">{{ $defaultValue->city }}, {{ $defaultValue->country }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-strip-label">Status</div>
                            <div class="summary-strip-value" id="summaryStatus">{{ $defaultValue->status ? 'Active' : 'Inactive' }}</div>
                        </div>
                    </div>
                </div>

                <div class="workflow-grid">
                    <div class="workflow-step">
                        <div class="workflow-step-head">
                            <span class="step-badge">1</span>
                            <div>
                                <p class="workflow-step-title">Locked Rule</p>
                                <p class="workflow-step-subtitle">This mapping keeps the same business rule while you update its location or product.</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Service Type</label>
                            <input type="text" class="form-control" value="{{ $defaultValue->getServiceTypeDisplayName() }}" readonly>
                        </div>
                        <span class="locked-note"><i class="ri-lock-line"></i>Service type cannot be changed</span>
                    </div>

                    <div class="workflow-step">
                        <div class="workflow-step-head">
                            <span class="step-badge">2</span>
                            <div>
                                <p class="workflow-step-title">Update Market</p>
                                <p class="workflow-step-subtitle">Move this default to another country or city if needed.</p>
                            </div>
                        </div>
                        <div class="mb-3">
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
                        <div>
                            <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                            <select name="city" id="city" class="form-select @error('city') is-invalid @enderror" data-placeholder="Search and select a city" required>
                                <option value="">Select City</option>
                            </select>
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="workflow-step">
                        <div class="workflow-step-head">
                            <span class="step-badge">3</span>
                            <div>
                                <p class="workflow-step-title">Update Product</p>
                                <p class="workflow-step-subtitle">Choose the new default product for this market and service type.</p>
                            </div>
                        </div>
                        <div class="mb-3">
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
                        <div>
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
    const $summaryLocation = $('#summaryLocation');
    const $summaryStatus = $('#summaryStatus');

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

    function refreshSummary() {
        const country = $country.val();
        const city = $city.val();
        $summaryLocation.text(country && city ? city + ', ' + country : 'Select country and city');
        $summaryStatus.text($('#status').val() === '1' ? 'Active' : 'Inactive');
    }

    $country.on('change', function() {
        populateCities($country.val(), '');
        loadServices(null);
    });
    $city.on('change', function() {
        loadServices(null);
        refreshSummary();
    });
    $('#status').on('change', refreshSummary);
    $country.on('change', refreshSummary);

    if ($country.val()) {
        populateCities($country.val(), initialCity);
    } else {
        initCitySelect2('');
    }
    initSelect2();
    refreshSummary();
});
</script>
@endpush
@endsection
