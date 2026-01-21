@extends('layouts.layout')

@section('title', 'Edit Default Value')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Default Values /</span> Edit
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
            <h5>Edit Default Value - {{ ucfirst(str_replace('_', ' ', $defaultValue->name)) }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('default-values.update', Crypt::encrypt($defaultValue->id)) }}" method="POST" id="defaultValueForm">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Service Type (Read-only) -->
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Service Type</label>
                        <input type="text" class="form-control" value="{{ $defaultValue->getServiceTypeDisplayName() }}" readonly>
                        <small class="text-muted">Service type cannot be changed. Delete and create a new one if needed.</small>
                    </div>

                    <!-- Status -->
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

                <!-- Service Selection based on type -->
                <div class="row">
                    @if($defaultValue->name == 'hotel')
                    <div class="col-md-12 mb-3">
                        <label for="service_id" class="form-label">Select Hotel <span class="text-danger">*</span></label>
                        <select name="service_id" id="service_id" class="form-select @error('service_id') is-invalid @enderror" required>
                            <option value="">Select Hotel</option>
                            @foreach($hotels as $hotel)
                                <option value="{{ $hotel->hotel_unique_id }}" 
                                    {{ old('service_id', $defaultValue->service_id) == $hotel->hotel_unique_id ? 'selected' : '' }}>
                                    {{ $hotel->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('service_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @elseif($defaultValue->name == 'restaurant')
                    <div class="col-md-12 mb-3">
                        <label for="service_id" class="form-label">Select Restaurant <span class="text-danger">*</span></label>
                        <select name="service_id" id="service_id" class="form-select @error('service_id') is-invalid @enderror" required>
                            <option value="">Select Restaurant</option>
                            @foreach($restaurants as $restaurant)
                                <option value="{{ $restaurant->restaurant_id }}" 
                                    {{ old('service_id', $defaultValue->service_id) == $restaurant->restaurant_id ? 'selected' : '' }}>
                                    {{ $restaurant->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('service_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @elseif($defaultValue->name == 'attraction')
                    <div class="col-md-12 mb-3">
                        <label for="service_id" class="form-label">Select Attraction <span class="text-danger">*</span></label>
                        <select name="service_id" id="service_id" class="form-select @error('service_id') is-invalid @enderror" required>
                            <option value="">Select Attraction</option>
                            @foreach($attractions as $attraction)
                                <option value="{{ $attraction->attraction_id }}" 
                                    {{ old('service_id', $defaultValue->service_id) == $attraction->attraction_id ? 'selected' : '' }}>
                                    {{ $attraction->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('service_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @elseif($defaultValue->name == 'car_private')
                    <div class="col-md-12 mb-3">
                        <label for="service_id" class="form-label">Select Car (Private) <span class="text-danger">*</span></label>
                        <select name="service_id" id="service_id" class="form-select @error('service_id') is-invalid @enderror" required>
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
                                <option value="{{ $vehicle->vehicle_id }}" 
                                    {{ old('service_id', $defaultValue->service_id) == $vehicle->vehicle_id ? 'selected' : '' }}>
                                    {{ $vehicle->vehicle_name }} ({{ ucfirst($vehicle->vehicle_type) }} - {{ $sharableLabel }})
                                </option>
                            @endforeach
                        </select>
                        @error('service_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @elseif($defaultValue->name == 'car_shared')
                    <div class="col-md-12 mb-3">
                        <label for="service_id" class="form-label">Select Car (Shared) <span class="text-danger">*</span></label>
                        <select name="service_id" id="service_id" class="form-select @error('service_id') is-invalid @enderror" required>
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
                                <option value="{{ $vehicle->vehicle_id }}" 
                                    {{ old('service_id', $defaultValue->service_id) == $vehicle->vehicle_id ? 'selected' : '' }}>
                                    {{ $vehicle->vehicle_name }} ({{ ucfirst($vehicle->vehicle_type) }} - {{ $sharableLabel }})
                                </option>
                            @endforeach
                        </select>
                        @error('service_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @elseif($defaultValue->name == 'port')
                    <div class="col-md-12 mb-3">
                        <label for="service_id" class="form-label">Select Port <span class="text-danger">*</span></label>
                        <select name="service_id" id="service_id" class="form-select @error('service_id') is-invalid @enderror" required>
                            <option value="">Select Port</option>
                            @foreach($ports as $port)
                                <option value="{{ $port->port_id }}" 
                                    {{ old('service_id', $defaultValue->service_id) == $port->port_id ? 'selected' : '' }}>
                                    {{ $port->port_name }}{{ $port->country ? ' - ' . $port->country : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('service_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @elseif($defaultValue->name == 'guide')
                    <div class="col-md-12 mb-3">
                        <label for="service_id" class="form-label">Select Guide <span class="text-danger">*</span></label>
                        <select name="service_id" id="service_id" class="form-select @error('service_id') is-invalid @enderror" required>
                            <option value="">Select Guide</option>
                            @foreach($guides as $guide)
                                <option value="{{ $guide->guide_id }}" 
                                    {{ old('service_id', $defaultValue->service_id) == $guide->guide_id ? 'selected' : '' }}>
                                    {{ $guide->name }}{{ $guide->email ? ' - ' . $guide->email : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('service_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @endif
                </div>

                <div class="alert alert-info">
                    <i class="ri-information-line me-2"></i>
                    <strong>Note:</strong> You can change the selected {{ $defaultValue->getServiceTypeDisplayName() }} or update the status. 
                    The service type itself cannot be modified.
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
@endsection

