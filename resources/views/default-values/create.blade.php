@extends('layouts.layout')

@section('title', 'Add Default Value')

@push('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    .default-value-service-select + .select2-container--default .select2-selection--single {
        height: 38px !important;
        border: 1px solid #d9dee3 !important;
        border-radius: 0.375rem !important;
        display: flex !important;
        align-items: center !important;
    }
    .default-value-service-select + .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 0.75rem !important;
        color: #697a8d !important;
    }
    .default-value-service-select + .select2-container--default .select2-selection--single .select2-selection__arrow {
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

    <!-- Display validation errors -->
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
            <form action="{{ route('default-values.store') }}" method="POST" id="defaultValueForm">
                @csrf

                <div class="row">
                    <!-- Service Type -->
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Service Type <span class="text-danger">*</span></label>
                        <select name="name" id="name" class="form-select @error('name') is-invalid @enderror" required>
                            <option value="">Select Service Type</option>
                            @foreach($availableTypes as $type)
                                <option value="{{ $type }}" {{ old('name') == $type ? 'selected' : '' }}>
                                    @if($type == 'hotel')
                                        Hotel
                                    @elseif($type == 'restaurant')
                                        Restaurant
                                    @elseif($type == 'attraction')
                                        Attraction
                                    @elseif($type == 'car_private')
                                        Car (Private)
                                    @elseif($type == 'car_shared')
                                        Car (Shared)
                                    @elseif($type == 'port')
                                        Port
                                    @elseif($type == 'guide')
                                        Guide
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status -->
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

                <!-- Service Selection (dynamically shown based on service type) -->
                <div class="row">
                    <!-- Hotel -->
                    <div class="col-md-12 mb-3 service-select" id="hotel-select" style="display: none;">
                        <label for="hotel_service_id" class="form-label">Select Hotel <span class="text-danger">*</span></label>
                        <select name="service_id_hotel" id="hotel_service_id" class="form-select default-value-service-select" data-placeholder="Search and select a hotel">
                            <option value="">Select Hotel</option>
                            @foreach($hotels as $hotel)
                                <option value="{{ $hotel->hotel_unique_id }}" {{ old('service_id') == $hotel->hotel_unique_id ? 'selected' : '' }}>
                                    {{ $hotel->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Restaurant -->
                    <div class="col-md-12 mb-3 service-select" id="restaurant-select" style="display: none;">
                        <label for="restaurant_service_id" class="form-label">Select Restaurant <span class="text-danger">*</span></label>
                        <select name="service_id_restaurant" id="restaurant_service_id" class="form-select default-value-service-select" data-placeholder="Search and select a restaurant">
                            <option value="">Select Restaurant</option>
                            @foreach($restaurants as $restaurant)
                                <option value="{{ $restaurant->restaurant_id }}" {{ old('service_id') == $restaurant->restaurant_id ? 'selected' : '' }}>
                                    {{ $restaurant->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Attraction -->
                    <div class="col-md-12 mb-3 service-select" id="attraction-select" style="display: none;">
                        <label for="attraction_service_id" class="form-label">Select Attraction <span class="text-danger">*</span></label>
                        <select name="service_id_attraction" id="attraction_service_id" class="form-select default-value-service-select" data-placeholder="Search and select an attraction">
                            <option value="">Select Attraction</option>
                            @foreach($attractions as $attraction)
                                <option value="{{ $attraction->attraction_id }}" {{ old('service_id') == $attraction->attraction_id ? 'selected' : '' }}>
                                    {{ $attraction->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Car Private -->
                    <div class="col-md-12 mb-3 service-select" id="car_private-select" style="display: none;">
                        <label for="car_private_service_id" class="form-label">Select Car (Private) <span class="text-danger">*</span></label>
                        <select name="service_id_car_private" id="car_private_service_id" class="form-select default-value-service-select" data-placeholder="Search and select a car (private)">
                            <option value="">Select Car (Private)</option>
                            @foreach($privateVehicles as $vehicle)
                                @php
                                    $sharableLabel = match($vehicle->sharable) {
                                        1 => 'Private',
                                        2 => 'Shared',
                                        3 => 'Both',
                                        default => 'Unknown'
                                    };
                                @endphp
                                <option value="{{ $vehicle->vehicle_id }}" {{ old('service_id') == $vehicle->vehicle_id ? 'selected' : '' }}>
                                    {{ $vehicle->vehicle_name }} ({{ ucfirst($vehicle->vehicle_type) }} - {{ $sharableLabel }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Car Shared -->
                    <div class="col-md-12 mb-3 service-select" id="car_shared-select" style="display: none;">
                        <label for="car_shared_service_id" class="form-label">Select Car (Shared) <span class="text-danger">*</span></label>
                        <select name="service_id_car_shared" id="car_shared_service_id" class="form-select default-value-service-select" data-placeholder="Search and select a car (shared)">
                            <option value="">Select Car (Shared)</option>
                            @foreach($sharedVehicles as $vehicle)
                                @php
                                    $sharableLabel = match($vehicle->sharable) {
                                        1 => 'Private',
                                        2 => 'Shared',
                                        3 => 'Both',
                                        default => 'Unknown'
                                    };
                                @endphp
                                <option value="{{ $vehicle->vehicle_id }}" {{ old('service_id') == $vehicle->vehicle_id ? 'selected' : '' }}>
                                    {{ $vehicle->vehicle_name }} ({{ ucfirst($vehicle->vehicle_type) }} - {{ $sharableLabel }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Port -->
                    <div class="col-md-12 mb-3 service-select" id="port-select" style="display: none;">
                        <label for="port_service_id" class="form-label">Select Port <span class="text-danger">*</span></label>
                        <select name="service_id_port" id="port_service_id" class="form-select default-value-service-select" data-placeholder="Search and select a port">
                            <option value="">Select Port</option>
                            @foreach($ports as $port)
                                <option value="{{ $port->port_id }}" {{ old('service_id') == $port->port_id ? 'selected' : '' }}>
                                    {{ $port->port_name }}{{ $port->country ? ' - ' . $port->country : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Guide -->
                    <div class="col-md-12 mb-3 service-select" id="guide-select" style="display: none;">
                        <label for="guide_service_id" class="form-label">Select Guide <span class="text-danger">*</span></label>
                        <select name="service_id_guide" id="guide_service_id" class="form-select default-value-service-select" data-placeholder="Search and select a guide">
                            <option value="">Select Guide</option>
                            @foreach($guides as $guide)
                                <option value="{{ $guide->guide_id }}" {{ old('service_id') == $guide->guide_id ? 'selected' : '' }}>
                                    {{ $guide->name }}{{ $guide->email ? ' - ' . $guide->email : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Hidden field for actual service_id -->
                    <input type="hidden" name="service_id" id="service_id" value="{{ old('service_id') }}">
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
    const $nameSelect = $('#name');
    const $serviceSelects = $('.service-select');
    const $serviceIdInput = $('#service_id');

    function initServiceSelect2(selectElement) {
        const $el = $(selectElement);
        if (!$el.length) return;
        if ($el.data('select2')) {
            $el.select2('destroy');
        }
        $el.select2({
            placeholder: $el.data('placeholder') || 'Search and select',
            allowClear: true,
            width: '100%'
        });
    }

    function destroyServiceSelect2(selectElement) {
        const $el = $(selectElement);
        if ($el.length && $el.data('select2')) {
            $el.select2('destroy');
        }
    }

    function toggleServiceSelect() {
        const selectedType = $nameSelect.val();

        $serviceSelects.each(function() {
            $(this).hide();
            const selectElement = this.querySelector('select');
            if (selectElement) {
                selectElement.removeAttribute('required');
                destroyServiceSelect2(selectElement);
            }
        });

        if (selectedType) {
            const $targetSelect = $('#' + selectedType + '-select');
            if ($targetSelect.length) {
                $targetSelect.show();
                const selectElement = $targetSelect.find('select')[0];
                if (selectElement) {
                    selectElement.setAttribute('required', 'required');
                    initServiceSelect2(selectElement);
                    const savedId = $serviceIdInput.val();
                    if (savedId) {
                        $(selectElement).val(savedId).trigger('change.select2');
                    }
                }
            }
        }
    }

    function updateServiceId() {
        const selectedType = $nameSelect.val();
        if (!selectedType) {
            $serviceIdInput.val('');
            return;
        }
        const $selectElement = $('#' + selectedType + '-select').find('select');
        if ($selectElement.length) {
            $serviceIdInput.val($selectElement.val() || '');
        }
    }

    $nameSelect.on('change', function() {
        $serviceIdInput.val('');
        toggleServiceSelect();
        updateServiceId();
    });

    $(document).on('change select2:select select2:clear', '.default-value-service-select', updateServiceId);

    toggleServiceSelect();
    if ($nameSelect.val()) {
        updateServiceId();
    }

    $('#defaultValueForm').on('submit', function(e) {
        if (!$nameSelect.val()) {
            e.preventDefault();
            alert('Please select a service type.');
            return false;
        }
        if (!$serviceIdInput.val()) {
            e.preventDefault();
            alert('Please select a service.');
            return false;
        }
    });
});
</script>
@endpush
@endsection

