@extends('layouts.layout')
@section('content')
<style>
    .select2-container .select2-selection--single {
        height: 100% !important;
        line-height: 100% !important;
        padding: 8px 12px;
    }
    .select2-container .select2-results__option {
        padding: 12px 10px;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Main tabs navigation -->
        <ul class="nav nav-tabs mb-4 mt-4 d-flex justify-content-center" id="main-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active"
                   href="{{ route('vehicle.create') }}"
                   role="tab">
                    Add Vehicle
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link disabled"
                   href="javascript:void(0);"
                   role="tab"
                   title="Save this vehicle first before mapping zones">
                    Zone Mapping
                </a>
            </li>
        </ul>

        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Add New Vehicle
                <a href="{{ route('vehicle.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            {{-- @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif --}}
            @if (session('error'))
                <div class="alert alert-danger border-0 border-start border-5 border-danger-subtle shadow-sm px-4 py-3 rounded-3">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                        <div>
                            <h6 class="mb-2 fw-semibold text-danger">Please fix the following errors:</h6>
                            <ul class="mb-0 ps-3"><li class="small">{{ session('error') }}</li></ul>
                        </div>
                    </div>
                </div>
            @endif


            @if(!request()->has('zone_mapping'))
            <form id="restaurantForm" method="POST" action="{{ route('vehicle.store') }}" enctype="multipart/form-data"
                class="card-body">
                @csrf
                <!-- Hidden Fields -->
                <div id="vehicleDetailsContainer">
                    <div class="vehicle-form">
                        <div class="row">
                            <!-- Select DMC Name -->
                        @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 23 || auth()->user()->role_id == 25 || auth()->user()->role_id == 62 || auth()->user()->role_id == 46 || auth()->user()->role_id == 109 || auth()->user()->role_id == 110)
                            <div class="mb-3 col-md-3" id="dmc-container">
                                <label for="dmc" class="form-label"><strong>DMC</strong><span style="color: red; font-weight: bold;">*</span></label>
                                <select id="dmc" name="dmc" class="form-control" required>
                                    <option value="">Select DMC</option>
                                    @foreach ($dmcs as $dmc)
                                        <option value="{{ $dmc->userId }}">{{ $dmc->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                            <!--
                             -->
                            <div class="col-md-3">
                                <label for="driver_id" class="form-label"><strong>Select Driver</strong></label>
                                <select id="driver" type="text" class="form-select" name="driver_id" placeholder="">
                                    <option value="">Select driver</option>
                                </select>
                            </div>

                            <!-- Vehicle Name -->
                            <div class="col-md-3 mb-3">
                                <label for="vehicle_name" class="form-label"><strong>Vehicle Name</strong><span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="vehicle_name"
                                    placeholder="Enter Vehicle Name" value="{{ old('vehicle_name') }}">
                                @error('vehicle_name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Vehicle Type -->
                            <div class="col-md-3 mb-3">
                                <label for="vehicle_type" class="form-label"><strong>Vehicle
                                        Type</strong><span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="vehicle_type"
                                    placeholder="Enter Vehicle Type" value="{{ old('vehicle_type') }}">
                                @error('vehicle_type')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Vehicle Model -->
                            <div class="col-md-3 mb-4">
                                <label for="vehicle_model" class="form-label"><strong>Vehicle
                                        Model</strong><span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="vehicle_model"
                                    placeholder="Enter Vehicle Model" value="{{ old('vehicle_model') }}">
                                @error('vehicle_model')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Model Year -->
                            <div class="col-md-3 mb-3">
                                <label for="model_year" class="form-label"><strong>Model Year</strong><span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="model_year" id="model_year"
                                    placeholder="Enter Model Year" value="{{ old('model_year') }}" oninput="validateModelYear(this)">
                                <small class="validation-message text-danger" id="model_year-validation-message"></small>
                                @error('model_year')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- Vehicle Plate No -->
                            <div class="col-md-3 mb-3">
                                <label for="vehicle_plate_no" class="form-label"><strong>Vehicle Plate Number</strong><span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="vehicle_plate_no" id="vehicle_plate_no"
                                    placeholder="Enter Vehicle Plate Number" value="{{ old('vehicle_plate_no') }}" oninput="validatePlateNumber(this)">
                                <small class="validation-message text-danger" id="vehicle_plate_no-validation-message"></small>
                                <small class="text-muted mt-1 d-block">
                                    <i class="fas fa-info-circle"></i> Special characters are automatically removed. Plate numbers like "WB 26", "WB-26", "WB/26" will all be treated as "WB26".
                                </small>
                                @error('vehicle_plate_no')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Seating Capacity -->
                            <div class="col-md-3 mb-3">
                                <label for="seating_capacity" class="form-label"><strong>Seating
                                        Capacity</strong><span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="seating_capacity" id="seating_capacity"
                                    placeholder="Enter Seating Capacity" value="{{ old('seating_capacity') }}" oninput="validateSeatingCapacity(this)">
                                <small class="validation-message text-danger" id="seating_capacity-validation-message"></small>
                                @error('seating_capacity')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- City Name -->
                            <div class="col-md-3 mb-3">
                                <label for="city_name" class="form-label"><strong>City Name</strong><span class="text-danger">*</span></label>
                                @php
                                    $roleId = auth()->user()->role_id;
                                    $placeholder = $roleId == 11 ? 'Select City' : 'Select DMC First';
                                @endphp

                                <select name="city_name" id="city_name" class="form-control" required>
                                    <option value="">{{ $placeholder }}</option>

                                    @if(in_array($roleId, [11, 35, 76, 111]))
                                        @foreach($cities as $city)
                                            <option value="{{ $city->name }}">{{ $city->name }}</option>
                                        @endforeach
                                    @endif
                                </select>

                                @error('city_name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>



                            <fieldset id="privatePrice" class="border p-4 rounded mb-4">
                                <h5 class="card-title mb-3">Private Car Tarrifs</h5>
                                <fieldset id="taxi_day_charges" class="border p-4 rounded mb-4">
                                    <h5 class="card-title mb-3">Day Charges</h5>
                                    <div class="row">

                                        <!-- Base Price -->
                                        <div class="col-md-3 mb-3">
                                            <label for="day_base_price" class="form-label"><strong>Base
                                                    Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="base_price"
                                                placeholder="Enter Base Price" value="{{ old('base_price') }}">
                                            @error('base_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <!-- Cost per KM Below 10 -->
                                        <div class="col-md-3 mb-3">
                                            <label for="cost_per_km_below_10" class="form-label"><strong>Cost per KM
                                                    Below 10</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control"
                                                name="cost_per_km_below_10" placeholder="Enter Cost" value="{{ old('cost_per_km_below_10') }}">
                                            @error('cost_per_km_below_10')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Cost per KM 10 to 25 -->
                                        <div class="col-md-3 mb-3">
                                            <label for="cost_per_km_10_to_25" class="form-label"><strong>Cost per KM (10 to 25)</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control"
                                                name="cost_per_km_10_to_25" placeholder="Enter Cost" value="{{ old('cost_per_km_10_to_25') }}">
                                            @error('cost_per_km_10_to_25')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Cost per KM Above 25 -->
                                        <div class="col-md-3 mb-3">
                                            <label for="cost_per_km_above_25" class="form-label"><strong>Cost per KM
                                                Above 25km</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control"
                                                name="cost_per_km_above_25" placeholder="Enter Cost" value="{{ old('cost_per_km_above_25') }}">
                                            @error('cost_per_km_above_25')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Cost per Hour -->
                                        <div class="col-md-3 mb-3">
                                            <label for="cost_per_hour" class="form-label"><strong>Cost per
                                                    Hour</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="cost_per_hour"
                                                placeholder="Enter Cost" value="{{ old('cost_per_hour') }}">
                                            @error('cost_per_hour')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Cancel Cost -->
                                        <div class="col-md-3 mb-3">
                                            <label for="cancel_cost" class="form-label"><strong>Cancel
                                                    Cost</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="cancel_cost"
                                                placeholder="Enter Cancel Cost" value="{{ old('cancel_cost') }}">
                                            @error('cancel_cost')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror

                                        </div>
                                    </div>
                                </fieldset>
                                <!-- Night charges -->
                                <fieldset id="taxi_night_charges" class="border p-4 rounded mb-4">
                                    <h5 class="card-title mb-3">Night Charges</h5>
                                    <div class="row">
                                        <!-- Night Cost per KM Below 10 -->
                                        <!-- Base Price -->
                                        <div class="col-md-3 mb-3">
                                            <label for="base_price" class="form-label"><strong>Base
                                                    Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.1" class="form-control" name="night_base_price"
                                                placeholder="Enter Base Price" value="{{ old('night_base_price') }}">
                                            @error('base_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="night_cost_per_km_below_10" class="form-label"><strong>Cost per KM Below 10km</strong><span
                                                    class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control"
                                                name="night_cost_per_km_below_10" placeholder="Enter Cost for night" value="{{ old('night_cost_per_km_below_10') }}">
                                            @error('night_cost_per_km_below_10')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Night Cost per KM 10 to 25 -->
                                        <div class="col-md-3 mb-3">
                                            <label for="night_cost_per_km_10_to_25" class="form-label"><strong>Cost per KM (10km to 25km)</strong><span
                                                class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control"
                                                name="night_cost_per_km_10_to_25" placeholder="Enter Cost for night" value="{{ old('night_cost_per_km_10_to_25') }}">
                                            @error('night_cost_per_km_10_to_25')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Night Cost per KM Above 25 -->
                                        <div class="col-md-3 mb-3">
                                            <label for="night_cost_per_km_above_25" class="form-label"><strong>Cost per KM Above 25km</strong><span
                                                    class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control"
                                                name="night_cost_per_km_above_25" placeholder="Enter Cost for night" value="{{ old('night_cost_per_km_above_25') }}">
                                            @error('night_cost_per_km_above_25')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Cost per Hour(Night) -->
                                        <div class="col-md-3 mb-3">
                                            <label for="night_cost_per_hour" class="form-label"><strong>Cost per
                                                    Hour</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="night_cost_per_hour"
                                                placeholder="Enter Cost" value="{{ old('night_cost_per_hour') }}">
                                            @error('night_cost_per_hour')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Cancel Cost -->
                                        <div class="col-md-3 mb-3">
                                            <label for="night_cancel_cost" class="form-label"><strong>Cancel
                                                    Cost</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="night_cancel_cost"
                                                placeholder="Enter Cancel Cost" value="{{ old('night_cancel_cost') }}">
                                            @error('night_cancel_cost')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </fieldset>
                            </fieldset>

                            <!-- Sharable -->
                            <!-- <div class="col-md-3 mb-3">
                                <label for="price_type" class="form-label">
                                    <strong>Base Price Type</strong>
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="price_type" id="price_type" class="form-control" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="1">Shared</option>
                                    <option value="2">Private</option>
                                </select>
                                @error('price_type')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div> -->

                            <!-- Sharable Toggle Switch -->
                            <div class="col-md-3 mb-3">
                                <label for="sharable" class="form-label d-block">
                                    <strong>Vehicle Sharing Option</strong>
                                </label>
                                <select class="form-select mt-2" id="sharable" name="sharable">
                                    <option value="1">Private</option>
                                    <option value="2">Sharable</option>
                                    <option value="3">Both</option>
                                </select>
                            </div>

                            <style>
                                /* Custom toggle switch styling */
                                .form-check-input[type="checkbox"] {
                                    background-color:rgb(246, 249, 253);
                                    border-color:rgb(192, 199, 207);

                                    transition: all 0.3s ease;
                                }

                                .form-check-input:checked[type="checkbox"] {
                                    background-color: #28a745;
                                    border-color: #28a745;
                                }

                                .form-check-input:focus {
                                    box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
                                }

                                /* Animation for sharable fields */
                                .sharable-field {
                                    transition: all 0.3s ease;
                                    opacity: 0;
                                    height: 0;
                                    overflow: hidden;
                                    padding-top: 0;
                                    padding-bottom: 0;
                                    margin-top: 0;
                                    margin-bottom: 0;
                                }

                                .sharable-field.visible {
                                    opacity: 1;
                                    height: auto;
                                    padding-top: 0.5rem;
                                    padding-bottom: 0.5rem;
                                    margin-bottom: 1rem;
                                }
                            </style>

                            <!-- attraction_privae_transport_price -->
                            <div class="col-md-3 mb-3 private-fields">
                                <label for="attraction_private_transport_price" class="form-label"><strong>Attraction Private Transport Price</strong><span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" name="attraction_private_transport_price"
                                    placeholder="Enter Cost" value="{{ old('attraction_private_transport_price') }}" id="attraction_private_transport_price">
                                @error('attraction_private_transport_price')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- attraction_shared_transport_price -->
                            <div class="col-md-3 mb-3 sharable-field">
                                <label for="attraction_shared_transport_price" class="form-label"><strong>Attraction Shared Transport Price</strong><span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" name="attraction_shared_transport_price"
                                    placeholder="Enter Cost" value="{{ old('attraction_shared_transport_price') }}" id="attraction_shared_transport_price">
                            </div>

                            <!-- restaurant_private_transport_price -->
                            <div class="col-md-3 mb-3 private-fields">
                                <label for="restaurant_private_transport_price" class="form-label"><strong>Restaurant Private Transport Price</strong><span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" name="restaurant_private_transport_price"
                                    placeholder="Enter Cost" value="{{ old('restaurant_private_transport_price') }}" id="restaurant_private_transport_price">
                            </div>

                            <!-- restaurant_shared_transport_price -->
                            <div class="col-md-3 mb-3 sharable-field">
                                <label for="restaurant_shared_transport_price" class="form-label"><strong>Restaurant Shared Transport Price</strong><span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" name="restaurant_shared_transport_price"
                                    placeholder="Enter Cost" value="{{ old('restaurant_shared_transport_price') }}" id="restaurant_shared_transport_price">
                            </div>



                            <!-- Vehicle image -->
                            <div class="mb-3 col-md-4">
                                <label for="master_image" class="form-label"><strong>Vehicle
                                        Image</strong><span style="color: red; font-weight: bold;">*</span></label>
                                <div id="master-drop-area" class="form-control"
                                    style="padding: 20px; border: 2px dashed #007bff; text-align: center;">
                                    Drag & Drop your files here or click to upload.
                                    <input type="file" id="master_image" name="master_image" style="display: none;"
                                        required>
                                </div>

                                <div id="master-preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                    style="max-width: 30%; overflow-x: auto; white-space: nowrap;">
                                </div>
                            </div>


                            <div class="col-md-12">
                                <label for="description" class="form-label"><strong>Description</strong><span
                                        class="text-danger">*</span></label>
                                <textarea id="summernote" name="description" class="form-control" rows="10"
                                    placeholder="Write Description...">{{ old('description') }}</textarea>
                                @error('description')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="row">
                        <div class="mt-4 col-md-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="vehicle_status" value="0">
                                <input class="form-check-input" name="vehicle_status" type="checkbox"
                                    id="vehicle_status" {{ old('vehicle_status', '1') == '1' ? 'checked' : '' }} value="1">
                                <label for="vehicle_status" class="form-check-label"><strong>Status</strong></label>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-3 mt-4">
                        <button type="submit" class="btn btn-primary px-4">Save</button>
                    </div>
            </form>
            @else
            <!-- Zone Mapping Form -->
            <form id="zoneMappingForm" method="POST" action="{{ route('vehicle.map_zones') }}" class="card-body">
                @csrf
                <input type="hidden" name="vehicle_id" value="{{ request()->get('vehicle_id') }}">
                <input type="hidden" name="mapping_type" value="{{ request()->get('mapping_type') }}">

                <div class="row mb-4">
                    <div class="col-md-12">
                        <h6>
                            @if(request()->get('mapping_type') == 'port_hotel')
                                Map Port to Hotel Transportation
                            @elseif(request()->get('mapping_type') == 'hotel_attraction')
                                Map Hotel to Attraction Transportation
                            @elseif(request()->get('mapping_type') == 'hotel_restaurant')
                                Map Hotel to Restaurant Transportation
                            @elseif(request()->get('mapping_type') == 'attraction_restaurant')
                                Map Attraction to Restaurant Transportation
                            @else
                                Map Transportation Services
                            @endif
                        </h6>
                        <p class="text-muted">Select zones to create transportation mapping between locations.</p>
                    </div>
                </div>

                <div class="row mb-3">
                    @if(request()->get('mapping_type') == 'port_hotel')
                    <div class="col-md-5">
                        <label for="from_zone" class="form-label"><strong>Port</strong><span class="text-danger">*</span></label>
                        <select id="from_zone" name="from_zone" class="form-select" required>
                            <option value="">-- Select Port --</option>
                            @foreach($ports ?? [] as $port)
                                <option value="{{ $port->port_id }}" data-type="Port">{{ $port->port_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label for="to_zone" class="form-label"><strong>Hotel</strong><span class="text-danger">*</span></label>
                        <select id="to_zone" name="to_zone" class="form-select" required>
                            <option value="">-- Select Hotel --</option>
                            @foreach($zones ?? [] as $zone)
                                @if($zone->zone_type == 'Hotel')
                                    @php
                                        // Get hotels assigned to this zone by the current DMC
                                        $assignedHotels = App\Models\Hotel::where('status', 1)
                                            ->whereJsonContains('dmc_id', $zone->dmc_id)
                                            ->get()
                                            ->filter(function($hotel) use ($zone) {
                                                return $hotel->getZoneForDmc($zone->dmc_id) == $zone->zone_id;
                                            });
                                        $hotelCount = $assignedHotels->count();
                                    @endphp
                                    <option value="{{ $zone->zone_id }}" data-type="{{ $zone->zone_type }}" data-hotel-count="{{ $hotelCount }}">
                                        {{ $zone->zone_name }} ({{ $hotelCount }} hotels)
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    @elseif(request()->get('mapping_type') == 'hotel_attraction')
                    <div class="col-md-5">
                        <label for="from_zone" class="form-label"><strong>Hotel</strong><span class="text-danger">*</span></label>
                        <select id="from_zone" name="from_zone" class="form-select" required>
                            <option value="">-- Select Hotel --</option>
                            @foreach($zones ?? [] as $zone)
                                @if($zone->zone_type == 'Hotel')
                                    @php
                                        // Get hotels assigned to this zone by the current DMC
                                        $assignedHotels = App\Models\Hotel::where('status', 1)
                                            ->whereJsonContains('dmc_id', $zone->dmc_id)
                                            ->get()
                                            ->filter(function($hotel) use ($zone) {
                                                return $hotel->getZoneForDmc($zone->dmc_id) == $zone->zone_id;
                                            });
                                        $hotelCount = $assignedHotels->count();
                                    @endphp
                                    <option value="{{ $zone->zone_id }}" data-type="{{ $zone->zone_type }}" data-hotel-count="{{ $hotelCount }}">
                                        {{ $zone->zone_name }} ({{ $hotelCount }} hotels)
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label for="to_zone" class="form-label"><strong>Attraction</strong><span class="text-danger">*</span></label>
                        <select id="to_zone" name="to_zone" class="form-select" required>
                            <option value="">-- Select Attraction --</option>
                            @foreach($zones ?? [] as $zone)
                                @if($zone->zone_type == 'Attraction')
                                    @php
                                        // Get attractions assigned to this zone by the current DMC
                                        $assignedAttractions = App\Models\Attraction::where('status', 1)
                                            ->whereJsonContains('dmc_id', $zone->dmc_id)
                                            ->get()
                                            ->filter(function($attraction) use ($zone) {
                                                return $attraction->getZoneForDmc($zone->dmc_id) == $zone->zone_id;
                                            });
                                        $attractionCount = $assignedAttractions->count();
                                    @endphp
                                    <option value="{{ $zone->zone_id }}" data-type="{{ $zone->zone_type }}" data-attraction-count="{{ $attractionCount }}">
                                        {{ $zone->zone_name }} ({{ $attractionCount }} attractions)
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    @elseif(request()->get('mapping_type') == 'hotel_restaurant')
                    <div class="col-md-5">
                        <label for="from_zone" class="form-label"><strong>Hotel</strong><span class="text-danger">*</span></label>
                        <select id="from_zone" name="from_zone" class="form-select" required>
                            <option value="">-- Select Hotel --</option>
                            @foreach($zones ?? [] as $zone)
                                @if($zone->zone_type == 'Hotel')
                                    @php
                                        // Get hotels assigned to this zone by the current DMC
                                        $assignedHotels = App\Models\Hotel::where('status', 1)
                                            ->whereJsonContains('dmc_id', $zone->dmc_id)
                                            ->get()
                                            ->filter(function($hotel) use ($zone) {
                                                return $hotel->getZoneForDmc($zone->dmc_id) == $zone->zone_id;
                                            });
                                        $hotelCount = $assignedHotels->count();
                                    @endphp
                                    <option value="{{ $zone->zone_id }}" data-type="{{ $zone->zone_type }}" data-hotel-count="{{ $hotelCount }}">
                                        {{ $zone->zone_name }} ({{ $hotelCount }} hotels)
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label for="to_zone" class="form-label"><strong>Restaurant</strong><span class="text-danger">*</span></label>
                        <select id="to_zone" name="to_zone" class="form-select" required>
                            <option value="">-- Select Restaurant --</option>
                            @foreach($zones ?? [] as $zone)
                                @if($zone->zone_type == 'Restaurant')
                                    @php
                                        // Get restaurants assigned to this zone by the current DMC
                                        $assignedRestaurants = App\Models\Restaurant::where('status', 1)
                                            ->whereJsonContains('dmc_id', $zone->dmc_id)
                                            ->get()
                                            ->filter(function($restaurant) use ($zone) {
                                                return $restaurant->getZoneForDmc($zone->dmc_id) == $zone->zone_id;
                                            });
                                        $restaurantCount = $assignedRestaurants->count();
                                    @endphp
                                    <option value="{{ $zone->zone_id }}" data-type="{{ $zone->zone_type }}" data-restaurant-count="{{ $restaurantCount }}">
                                        {{ $zone->zone_name }} ({{ $restaurantCount }} restaurants)
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    @elseif(request()->get('mapping_type') == 'attraction_restaurant')
                    <div class="col-md-5">
                        <label for="from_zone" class="form-label"><strong>Attraction</strong><span class="text-danger">*</span></label>
                        <select id="from_zone" name="from_zone" class="form-select" required>
                            <option value="">-- Select Attraction --</option>
                            @foreach($zones ?? [] as $zone)
                                @if($zone->zone_type == 'Attraction')
                                    @php
                                        // Get attractions assigned to this zone by the current DMC
                                        $assignedAttractions = App\Models\Attraction::where('status', 1)
                                            ->whereJsonContains('dmc_id', $zone->dmc_id)
                                            ->get()
                                            ->filter(function($attraction) use ($zone) {
                                                return $attraction->getZoneForDmc($zone->dmc_id) == $zone->zone_id;
                                            });
                                        $attractionCount = $assignedAttractions->count();
                                    @endphp
                                    <option value="{{ $zone->zone_id }}" data-type="{{ $zone->zone_type }}" data-attraction-count="{{ $attractionCount }}">
                                        {{ $zone->zone_name }} ({{ $attractionCount }} attractions)
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label for="to_zone" class="form-label"><strong>Restaurant</strong><span class="text-danger">*</span></label>
                        <select id="to_zone" name="to_zone" class="form-select" required>
                            <option value="">-- Select Restaurant --</option>
                            @foreach($zones ?? [] as $zone)
                                @if($zone->zone_type == 'Restaurant')
                                    @php
                                        // Get restaurants assigned to this zone by the current DMC
                                        $assignedRestaurants = App\Models\Restaurant::where('status', 1)
                                            ->whereJsonContains('dmc_id', $zone->dmc_id)
                                            ->get()
                                            ->filter(function($restaurant) use ($zone) {
                                                return $restaurant->getZoneForDmc($zone->dmc_id) == $zone->zone_id;
                                            });
                                        $restaurantCount = $assignedRestaurants->count();
                                    @endphp
                                    <option value="{{ $zone->zone_id }}" data-type="{{ $zone->zone_type }}" data-restaurant-count="{{ $restaurantCount }}">
                                        {{ $zone->zone_name }} ({{ $restaurantCount }} restaurants)
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" id="addMapping" class="btn btn-primary w-100">Add Mapping</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>
                                            @if(request()->get('mapping_type') == 'port_hotel')
                                                From Port
                                            @elseif(request()->get('mapping_type') == 'hotel_attraction')
                                                From Hotel
                                            @elseif(request()->get('mapping_type') == 'hotel_restaurant')
                                                From Hotel
                                            @elseif(request()->get('mapping_type') == 'attraction_restaurant')
                                                From Attraction
                                            @else
                                                From Zone
                                            @endif
                                        </th>
                                        <th>
                                            @if(request()->get('mapping_type') == 'port_hotel')
                                                To Hotel
                                            @elseif(request()->get('mapping_type') == 'hotel_attraction')
                                                To Attraction
                                            @elseif(request()->get('mapping_type') == 'hotel_restaurant')
                                                To Restaurant
                                            @elseif(request()->get('mapping_type') == 'attraction_restaurant')
                                                To Restaurant
                                            @else
                                                To Zone
                                            @endif
                                        </th>
                                        <th>Private Price</th>
                                        <th>Shared Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="mappingsTableBody">
                                    @if(isset($mappings) && count($mappings) > 0)
                                        @foreach($mappings as $mapping)
                                            @php
                                                $showMapping = false;
                                                $fromType = $mapping->fromZone->zone_type ?? '';
                                                $toType = $mapping->toZone->zone_type ?? '';

                                                if(request()->get('mapping_type') == 'port_hotel' && $fromType == 'Port' && $toType == 'Hotel') {
                                                    $showMapping = true;
                                                } elseif(request()->get('mapping_type') == 'hotel_attraction' && $fromType == 'Hotel' && $toType == 'Attraction') {
                                                    $showMapping = true;
                                                } elseif(request()->get('mapping_type') == 'hotel_restaurant' && $fromType == 'Hotel' && $toType == 'Restaurant') {
                                                    $showMapping = true;
                                                } elseif(request()->get('mapping_type') == 'attraction_restaurant' && $fromType == 'Attraction' && $toType == 'Restaurant') {
                                                    $showMapping = true;
                                                }
                                            @endphp

                                            @if($showMapping)
                                            <tr data-from="{{ $mapping->from_zone_id }}" data-to="{{ $mapping->to_zone_id }}">
                                                <td>{{ $mapping->fromZone->zone_name }}</td>
                                                <td>{{ $mapping->toZone->zone_name }}</td>
                                                <td>
                                                    <input type="number" name="private_prices[{{ $mapping->from_zone_id }}][{{ $mapping->to_zone_id }}]"
                                                        class="form-control" value="{{ $mapping->private_price }}" step="0.01" min="0">
                                                </td>
                                                <td>
                                                    <input type="number" name="shared_prices[{{ $mapping->from_zone_id }}][{{ $mapping->to_zone_id }}]"
                                                        class="form-control" value="{{ $mapping->shared_price }}" step="0.01" min="0">
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger remove-mapping">Remove</button>
                                                </td>
                                            </tr>
                                            @endif
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3 mt-4">
                    <button type="submit" class="btn btn-primary px-4">Save Mappings</button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
<!-- End of the form -->
@endsection

@section('scripts')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js"></script>
@if(!request()->has('zone_mapping'))
<script>
    $(document).ready(function() {
        $('#summernote').summernote({
            height: 200,
            minHeight: 200,
            maxHeight: 500,
            placeholder: 'Enter your content here...',
            callbacks: {
            onInit: function() {
                // Check if there's old content
                var oldContent = '{!! old("description") !!}';
                if (oldContent) {
                    $('#summernote').summernote('code', oldContent);
                }
            }
        }
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('#city_name').select2({
            placeholder: "Search and Select a City",
            allowClear: true,
            width: '100%'
        });
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // This section is now handled by the sharable select dropdown functionality
    // See the script below for the implementation
});
</script>

<!-- Master Image drop down -->
<script>
const masterDropArea = document.getElementById('master-drop-area');
const masterFileInput = document.getElementById('master_image');
const masterPreviewContainer = document.getElementById('master-preview-container');
let masterFileCounter = 0; // Track total uploaded files
const MASTER_MAX_VISIBLE_IMAGES = 1; // Show only 1 image

// Open file picker on click
masterDropArea.addEventListener('click', () => masterFileInput.click());

// Handle drag events
masterDropArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    masterDropArea.style.backgroundColor = '#e3f2fd';
});

masterDropArea.addEventListener('dragleave', () => {
    masterDropArea.style.backgroundColor = 'white';
});

masterDropArea.addEventListener('drop', (e) => {
    e.preventDefault();
    masterDropArea.style.backgroundColor = 'white';
    masterHandleFiles(e.dataTransfer.files);
});

// Handle file input change
masterFileInput.addEventListener('change', () => {
    masterHandleFiles(masterFileInput.files);
});

// Process and display files
function masterHandleFiles(files) {
    Array.from(files).forEach(file => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                // If an image already exists, remove it before adding the new one
                if (masterFileCounter > 0) {
                    masterPreviewContainer.innerHTML = ''; // Clear the existing preview
                    masterFileCounter = 0; // Reset the file counter
                }
                masterFileCounter++;
                masterImagePreview(e.target.result);
            };
            reader.readAsDataURL(file);
        } else {
            alert(`${file.name} is not a valid image file.`);
        }
    });
}

// Add image preview with limited visibility and a "more" badge
function masterImagePreview(imageSrc) {
    console.log("master image preview");

    const imageWrapper = document.createElement('div');
    imageWrapper.style.position = 'relative';
    imageWrapper.style.width = '70px';
    imageWrapper.style.height = '70px';
    imageWrapper.style.margin = '5px';
    imageWrapper.style.overflow = 'hidden';
    imageWrapper.style.borderRadius = '5px';

    const img = document.createElement('img');
    img.src = imageSrc;
    img.style.width = '100%';
    img.style.height = '100%';
    img.style.objectFit = 'cover';

    const deleteButton = document.createElement('button');
    deleteButton.textContent = '×';
    deleteButton.style.position = 'absolute';
    deleteButton.style.top = '2px';
    deleteButton.style.right = '2px';
    deleteButton.style.background = 'rgba(255, 0, 0, 0.8)';
    deleteButton.style.color = 'white';
    deleteButton.style.border = 'none';
    deleteButton.style.borderRadius = '50%';
    deleteButton.style.cursor = 'pointer';
    deleteButton.style.width = '20px';
    deleteButton.style.height = '20px';
    deleteButton.style.fontSize = '12px';
    deleteButton.style.lineHeight = '16px';
    deleteButton.addEventListener('click', () => {
        masterPreviewContainer.removeChild(imageWrapper);
        masterFileCounter--;
        updateMoreBadge();
    });

    imageWrapper.appendChild(img);
    imageWrapper.appendChild(deleteButton);
    masterPreviewContainer.appendChild(imageWrapper);

    updateMoreBadge();
}

// Create and update "+X more" badge
function updateMoreBadge() {
    // Remove any existing badge
    const existingBadge = document.getElementById('more-badge');
    if (existingBadge) existingBadge.remove();

    if (masterFileCounter > MASTER_MAX_VISIBLE_IMAGES) {
        const moreMasterBadge = document.createElement('div');
        moreMasterBadge.id = 'more-master-badge';
        moreMasterBadge.textContent = `+${masterFileCounter - MASTER_MAX_VISIBLE_IMAGES} more`;
        moreMasterBadge.style.margin = '5px';
        moreMasterBadge.style.padding = '5px 10px';
        moreMasterBadge.style.backgroundColor = '#007bff';
        moreMasterBadge.style.color = 'white';
        moreMasterBadge.style.borderRadius = '5px';
        moreMasterBadge.style.cursor = 'pointer';
        moreMasterBadge.style.fontSize = '12px';
        moreMasterBadge.style.textAlign = 'center';
        moreMasterBadge.addEventListener('click', () => {
            // Show all hidden images
            const hiddenImages = masterPreviewContainer.querySelectorAll('div[style*="display: none"]');
            hiddenImages.forEach(img => img.style.display = 'inline-block');
            moreMasterBadge.remove(); // Remove badge after revealing all
        });
        masterPreviewContainer.appendChild(moreMasterBadge);
    }
}
</script>

@php
    $currentUser = auth()->user();
    $userRoleId = $currentUser->role_id;
    $resolvedDmcId = '';

    if ($userRoleId == 11) {
        $resolvedDmcId = $currentUser->userId;
    } elseif ($userRoleId == 35) {
        $resolvedDmcId = \App\Models\User::where('userId', $currentUser->userId)->value('created_by');
    } elseif ($userRoleId == 76) {
        $pm = \App\Models\User::where('userId', $currentUser->userId)->first();
        $ph = \App\Models\User::where('userId', $pm?->created_by)->first();
        $resolvedDmcId = $ph?->created_by;
    } elseif ($userRoleId == 111) {
        $apm = \App\Models\User::where('userId', $currentUser->userId)->first();
        $pm = \App\Models\User::where('userId', $apm?->created_by)->first();
        $ph = \App\Models\User::where('userId', $pm?->created_by)->first();
        $resolvedDmcId = $ph?->created_by;
    }
@endphp

<script>
    $(document).ready(function () {
        const dmcId = "{{ $resolvedDmcId }}";

        // Auto-load drivers if user role resolves DMC directly
        if (dmcId) {
            loadDriversForDmc(dmcId);
        }

        // If DMC dropdown is visible, load drivers and cities on change
        $('#dmc').change(function () {
            const selectedDmcId = $(this).val();
            console.log(selectedDmcId);
            $('#city_name').html('<option value="">Loading...</option>');
            $('#driver').html('<option value="">Loading drivers...</option>');

            if (selectedDmcId) {
                // Load cities
                $.ajax({
                    url: "{{ route('fetch.dmc_cities') }}",
                    type: "GET",
                    data: { country_name: selectedDmcId },
                    success: function (response) {
                        $('#city_name').html('<option value="">Select City</option>');
                        $.each(response, function (key, city) {
                            $('#city_name').append('<option value="' + city.name + '">' + city.name + '</option>');
                        });
                    }
                });

                // Load drivers
                loadDriversForDmc(selectedDmcId);
            } else {
                $('#city_name').html('<option value="">Select a DMC first</option>');
                $('#driver').html('<option value="">Select a DMC first</option>');
            }
        });

        function loadDriversForDmc(dmcId) {
            if (!dmcId) return;

            $('#driver').html('<option value="">Loading drivers...</option>');

            $.ajax({
                url: "{{ route('fetch.dmc_drivers') }}",
                type: "GET",
                data: {
                    country_name: dmcId
                },
                success: function (response) {
                    $('#driver').html('<option value="">Select Driver</option>');

                    if (response.length === 0) {
                        $('#driver').html('<option value="">No drivers found</option>');
                    } else {
                        $.each(response, function (key, driver) {
                            const id = driver.driver_id;
                            $('#driver').append('<option value="' + id + '">' + driver.name + ' - ' + (driver.license_no ?? '') + '</option>');
                        });
                    }
                },
                error: function () {
                    $('#driver').html('<option value="">Error loading drivers</option>');
                }
            });
        }
    });
</script>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        let dmcSelect = document.getElementById("dmc_id");
        let citySelect = document.getElementById("city_name");

        dmcSelect.addEventListener("change", function () {
            let dmc = this.value;
            citySelect.innerHTML = '<option value="">Select City</option>'; // Reset cities dropdown

            if (dmc) {
                fetch(`{{ route('getCities') }}?dmc_id=${encodeURIComponent(dmc)}`)
                    .then(response => response.json())
                    .then(data => {
                        data.cities.forEach(city => {
                            let option = document.createElement("option");
                            option.value = city.name;
                            option.textContent = city.name;
                            citySelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error("Error fetching cities:", error);
                        alert("Failed to fetch cities. Try again.");
                    });
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#name').select2({
            placeholder: "Search and Select a Country",
            allowClear: true,
            width: '100%'
        });
    });
</script>


<!-- Add Font Awesome for icons in validation messages -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<!-- Validation Scripts -->
<script>
    function showValidationMessage(inputElement, isValid, message) {
        const messageElement = document.getElementById(`${inputElement.id}-validation-message`);

        if (!messageElement) return;

        if (isValid) {
            messageElement.innerHTML = `
                <div class="valid-feedback d-block">
                    <i class="fas fa-check-circle text-success"></i>
                    Looks good!
                </div>`;
            inputElement.classList.remove('is-invalid');
            inputElement.classList.add('is-valid');
        } else {
            messageElement.innerHTML = `
                <div class="invalid-feedback d-block">
                    <i class="fas fa-exclamation-circle"></i>
                    ${message}
                </div>`;
            inputElement.classList.remove('is-valid');
            inputElement.classList.add('is-invalid');
        }
    }

    function validateModelYear(input) {
        // Force numeric input for year and limit to 4 digits
        input.value = input.value.replace(/[^\d]/g, '').slice(0, 4);

        const yearRegex = /^(19|20)\d{2}$/;  // Year format: 1900-2099
        const value = input.value.trim();
        const currentYear = new Date().getFullYear();

        if (value === '') {
            showValidationMessage(input, false, 'Model year is required');
        } else if (!yearRegex.test(value)) {
            showValidationMessage(input, false, `
                Please enter a valid model year:
                <ul class="mt-1 mb-0">
                    <li>Must be a 4-digit year (1900-2099)</li>
                    <li>Only numbers are allowed</li>
                </ul>
            `);
        } else if (parseInt(value) > currentYear + 1) {
            showValidationMessage(input, false, `
                Please enter a valid model year:
                <ul class="mt-1 mb-0">
                    <li>Year cannot be more than ${currentYear + 1}</li>
                </ul>
            `);
        } else {
            showValidationMessage(input, true, '');
        }
    }

    function validatePlateNumber(input) {
        // Only allow alphanumeric characters (letters and numbers)
        // Remove all special characters including spaces, hyphens, etc.
        input.value = input.value.replace(/[^a-zA-Z0-9]/g, '');

        const plateRegex = /^[a-zA-Z0-9]{2,15}$/;
        const value = input.value.trim();

        if (value === '') {
            showValidationMessage(input, false, 'Plate number is required');
        } else if (!plateRegex.test(value)) {
            showValidationMessage(input, false, `
                Please enter a valid plate number:
                <ul class="mt-1 mb-0">
                    <li>Must be 2-15 characters long</li>
                    <li>Can only contain letters and numbers</li>
                    <li>Special characters are not allowed</li>
                </ul>
            `);
        } else {
            showValidationMessage(input, true, '');
        }
    }

    function validateSeatingCapacity(input) {
        // Force numeric input only and limit reasonable range
        input.value = input.value.replace(/[^\d]/g, '');

        const capacityRegex = /^[1-9][0-9]?$/;  // 1-99 passengers
        const value = input.value.trim();

        if (value === '') {
            showValidationMessage(input, false, 'Seating capacity is required');
        } else if (!capacityRegex.test(value)) {
            showValidationMessage(input, false, `
                Please enter a valid seating capacity:
                <ul class="mt-1 mb-0">
                    <li>Must be a number between 1-99</li>
                    <li>Only whole numbers are allowed</li>
                </ul>
            `);
        } else if (parseInt(value) > 99) {
            showValidationMessage(input, false, `
                Please enter a reasonable seating capacity:
                <ul class="mt-1 mb-0">
                    <li>Maximum capacity allowed is 99 passengers</li>
                </ul>
            `);
        } else {
            showValidationMessage(input, true, '');
        }
    }

    // Add CSS for validation messages and input styles
    document.head.insertAdjacentHTML('beforeend', `
        <style>
            /* Base validation message styles */
            .validation-message {
                margin-top: 0.5rem;
                font-size: 0.85rem;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            }

            /* Error state styles */
            .validation-message .invalid-feedback {
                display: block;
                color: #e74c3c;
                background-color: #fef5f5;
                border-left: 3px solid #e74c3c;
                padding: 0.75rem 1rem;
                border-radius: 4px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                animation: slideIn 0.3s ease-in-out;
            }

            /* Success state styles */
            .validation-message .valid-feedback {
                display: block;
                color: #2ecc71;
                background-color: #f4fff6;
                border-left: 3px solid #2ecc71;
                padding: 0.75rem 1rem;
                border-radius: 4px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                animation: slideIn 0.3s ease-in-out;
            }

            /* List styles within validation messages */
            .validation-message ul {
                margin: 0.5rem 0 0 0;
                padding-left: 1.5rem;
                list-style-type: none;
            }

            .validation-message ul li {
                position: relative;
                padding: 0.2rem 0;
                color: #666;
            }

            .validation-message ul li::before {
                content: "•";
                color: #e74c3c;
                font-weight: bold;
                position: absolute;
                left: -1rem;
            }

            /* Icon styles */
            .validation-message i {
                margin-right: 0.5rem;
                font-size: 1rem;
            }

            /* Input field styles with validation icons */
            .form-control.is-valid {
                border-color: #2ecc71 !important;
                background-color: #fff !important;
                padding-right: calc(1.5em + 0.75rem);
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%232ecc71' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right calc(0.375em + 0.1875rem) center;
                background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            }

            .form-control.is-invalid {
                border-color: #e74c3c !important;
                background-color: #fff !important;
                padding-right: calc(1.5em + 0.75rem);
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23e74c3c'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23e74c3c' stroke='none'/%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right calc(0.375em + 0.1875rem) center;
                background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            }

            /* Animation for validation messages */
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Hover effect for validation messages */
            .validation-message .invalid-feedback:hover,
            .validation-message .valid-feedback:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
                transition: all 0.3s ease;
            }

            /* Required field indicator */
            .required-field::after {
                content: "*";
                color: #e74c3c;
                margin-left: 4px;
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {
                .validation-message {
                    font-size: 0.8rem;
                }

                .validation-message .invalid-feedback,
                .validation-message .valid-feedback {
                    padding: 0.5rem 0.75rem;
                }
            }

            /* Focus state styles */
            .form-control:focus {
                box-shadow: 0 0 0 0.2rem rgba(46, 204, 113, 0.25);
                border-color: #2ecc71;
            }

            .form-control.is-invalid:focus {
                box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25);
                border-color: #e74c3c;
            }
        </style>
    `);
</script>

<!-- Add JavaScript for toggle functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sharableSelect = document.getElementById('sharable');
    const sharableFields = document.querySelectorAll('.sharable-field');
    const privateFields = document.querySelectorAll('.private-fields');

    // Toggle required attributes
    function toggleRequiredAttributes(elements, isRequired) {
        elements.forEach(element => {
            const inputs = element.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (isRequired) {
                    input.setAttribute('required', 'required');
                } else {
                    input.removeAttribute('required');
                }
            });
        });
    }

    // Show/hide fields based on selected option
    function updateFieldVisibility(value) {
        // Hide all first
        sharableFields.forEach(field => {
            field.style.display = 'none';
            field.classList.remove('visible');
        });
        privateFields.forEach(field => {
            field.style.display = 'none';
            field.classList.remove('visible');
        });

        toggleRequiredAttributes(sharableFields, false);
        toggleRequiredAttributes(privateFields, false);

        if (value === '1') {
            // Private only
            privateFields.forEach(field => {
                field.style.display = 'block';
                toggleRequiredAttributes([field], true);
                setTimeout(() => field.classList.add('visible'), 10);
            });
        } else if (value === '2') {
            // Sharable only
            sharableFields.forEach(field => {
                field.style.display = 'block';
                toggleRequiredAttributes([field], true);
                setTimeout(() => field.classList.add('visible'), 10);
            });
        } else if (value === '3') {
            // Both
            sharableFields.forEach(field => {
                field.style.display = 'block';
                toggleRequiredAttributes([field], true);
                setTimeout(() => field.classList.add('visible'), 10);
            });
            privateFields.forEach(field => {
                field.style.display = 'block';
                toggleRequiredAttributes([field], true);
                setTimeout(() => field.classList.add('visible'), 10);
            });
        }
    }

    // Initial load
    updateFieldVisibility(sharableSelect.value);

    // On change
    sharableSelect.addEventListener('change', function() {
        updateFieldVisibility(this.value);
    });
});
</script>

@else
<!-- Zone mapping scripts -->
<script>
    $(document).ready(function() {
        // Initialize Select2 for zone selects
        $('#from_zone, #to_zone').select2({
            placeholder: "Search and select a zone",
            allowClear: true,
            width: '100%'
        });

        // Add mapping button click handler
        $('#addMapping').click(function() {
            // Get selected values
            const fromZone = $('#from_zone').val();
            const toZone = $('#to_zone').val();
            const fromZoneText = $('#from_zone option:selected').text();
            const toZoneText = $('#to_zone option:selected').text();

            // Validate the selection
            if (!fromZone || !toZone) {
                alert('Please select both From and To zones');
                return;
            }

            if (fromZone === toZone) {
                alert('From and To zones cannot be the same');
                return;
            }

            // Check if mapping already exists
            if ($(`tr[data-from="${fromZone}"][data-to="${toZone}"]`).length > 0) {
                alert('This mapping already exists');
                return;
            }

            // Create new row
            const newRow = `
                <tr data-from="${fromZone}" data-to="${toZone}">
                    <td>${fromZoneText}</td>
                    <td>${toZoneText}</td>
                    <td>
                        <input type="number" name="private_prices[${fromZone}][${toZone}]"
                            class="form-control" value="0" step="0.01" min="0">
                    </td>
                    <td>
                        <input type="number" name="shared_prices[${fromZone}][${toZone}]"
                            class="form-control" value="0" step="0.01" min="0">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-mapping">Remove</button>
                    </td>
                </tr>
            `;

            // Add to table
            $('#mappingsTableBody').append(newRow);

            // Reset selection
            $('#from_zone, #to_zone').val('').trigger('change');
        });

        // Remove mapping handler
        $(document).on('click', '.remove-mapping', function() {
            $(this).closest('tr').remove();
        });
    });
</script>
@endif

@endsection