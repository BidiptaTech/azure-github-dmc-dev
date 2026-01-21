@extends('layouts.layout')

@section('title', 'Add Default Value')

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
                        <select name="service_id_hotel" id="hotel_service_id" class="form-select">
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
                        <select name="service_id_restaurant" id="restaurant_service_id" class="form-select">
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
                        <select name="service_id_attraction" id="attraction_service_id" class="form-select">
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
                        <select name="service_id_car_private" id="car_private_service_id" class="form-select">
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
                        <select name="service_id_car_shared" id="car_shared_service_id" class="form-select">
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
                        <select name="service_id_port" id="port_service_id" class="form-select">
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
                        <select name="service_id_guide" id="guide_service_id" class="form-select">
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameSelect = document.getElementById('name');
    const serviceSelects = document.querySelectorAll('.service-select');
    const serviceIdInput = document.getElementById('service_id');

    // Function to show/hide service select based on type
    function toggleServiceSelect() {
        const selectedType = nameSelect.value;
        
        // Hide all service selects
        serviceSelects.forEach(select => {
            select.style.display = 'none';
            const selectElement = select.querySelector('select');
            if (selectElement) {
                selectElement.removeAttribute('required');
            }
        });

        // Show the appropriate service select
        if (selectedType) {
            const targetSelect = document.getElementById(selectedType + '-select');
            if (targetSelect) {
                targetSelect.style.display = 'block';
                const selectElement = targetSelect.querySelector('select');
                if (selectElement) {
                    selectElement.setAttribute('required', 'required');
                }
            }
        }
    }

    // Update hidden service_id when any service select changes
    function updateServiceId() {
        const selectedType = nameSelect.value;
        if (selectedType) {
            const targetSelect = document.getElementById(selectedType + '-select');
            if (targetSelect) {
                const selectElement = targetSelect.querySelector('select');
                if (selectElement) {
                    serviceIdInput.value = selectElement.value;
                }
            }
        }
    }

    // Event listeners
    nameSelect.addEventListener('change', function() {
        toggleServiceSelect();
        updateServiceId();
    });

    // Add change listeners to all service selects
    document.querySelectorAll('.service-select select').forEach(select => {
        select.addEventListener('change', updateServiceId);
    });

    // Initialize on page load
    toggleServiceSelect();
    
    // If there's an old value, update the service_id
    if (nameSelect.value) {
        updateServiceId();
    }

    // Validate before submit
    document.getElementById('defaultValueForm').addEventListener('submit', function(e) {
        const selectedType = nameSelect.value;
        if (!selectedType) {
            e.preventDefault();
            alert('Please select a service type.');
            return false;
        }

        const serviceId = serviceIdInput.value;
        if (!serviceId) {
            e.preventDefault();
            alert('Please select a service.');
            return false;
        }
    });
});
</script>
@endpush
@endsection

