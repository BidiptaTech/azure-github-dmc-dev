

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
    .zone-option-wrapper[title] {
        cursor: help;
        border-bottom: 1px dotted #6c757d;
    }
    /* Zone hover tooltip - professional card style */
    .zone-cell-hover {
        cursor: help;
        border-bottom: 1px dotted #6c757d;
        position: relative;
    }
    .zone-hover-tooltip {
        position: fixed;
        z-index: 9999;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        padding: 12px;
        max-width: 320px;
        max-height: 280px;
        overflow-y: auto;
        border: 1px solid #e9ecef;
        display: none;
    }
    .zone-hover-tooltip.show {
        display: block;
        pointer-events: auto;
    }
    .zone-hover-tooltip .tooltip-title {
        font-size: 11px;
        font-weight: 600;
        color: #495057;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
        padding-bottom: 6px;
        border-bottom: 1px solid #e9ecef;
    }
    .zone-hover-tooltip .tooltip-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px 0;
        border-bottom: 1px solid #f1f3f5;
    }
    .zone-hover-tooltip .tooltip-item:last-child {
        border-bottom: none;
    }
    .zone-hover-tooltip .tooltip-item-img {
        width: 40px;
        height: 40px;
        border-radius: 6px;
        object-fit: cover;
        flex-shrink: 0;
    }
    .zone-hover-tooltip .tooltip-item-name {
        font-size: 12px;
        font-weight: 500;
        color: #212529;
        line-height: 1.3;
    }
    /* Enhanced tab styling */
    .port-port-tab {
        transition: all 0.3s ease;
        border-radius: 6px;
        font-weight: 500;
    }

    .port-port-tab:hover {
        background-color: rgba(40, 97, 195, 0.1);
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .port-port-tab.active {
        background-color: rgba(40, 97, 195, 0.15);
        border-color: #2861c3;
        font-weight: 600;
    }

    .port-port-tab .fa-repeat {
        color: #2861c3;
        font-size: 0.9em;
    }

    .port-port-tab.active .fa-repeat {
        animation: spin-once 0.5s ease-in-out;
    }

    @keyframes spin-once {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* First add these styles to the style section at the top */
    .readonly-field-styling {
        background-color: #f0f2f5 !important;
        border: 1px solid #dfe3e7 !important;
        color: #6e7781 !important;
        cursor: default !important;
        position: relative;
        box-shadow: none !important;
    }

    .readonly-field-styling:focus {
        box-shadow: none !important;
        border-color: #dfe3e7 !important;
        outline: none !important;
    }

    .readonly-field-container {
        position: relative;
    }

    .readonly-field-container::after {
        content: '\f023';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
        font-size: 14px;
        pointer-events: none;
    }

    .readonly-field-container input:hover {
        border-color: #dfe3e7 !important;
    }

    .field-info-message {
        margin-top: 8px;
        padding: 8px 12px;
        border-left: 4px solid #696cff;
        background-color: #f5f5ff;
        border-radius: 4px;
        display: flex;
        align-items: center;
        font-size: 13px;
        box-shadow: 0 2px 6px rgba(105, 108, 255, 0.15);
        animation: fadeInMessage 0.4s ease-in-out;
    }

    .field-info-message i {
        font-size: 16px;
        margin-right: 8px;
        color: #696cff;
    }

    @keyframes fadeInMessage {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Main tabs navigation -->
        <ul class="nav nav-tabs mb-4 mt-4 d-flex justify-content-center" id="main-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ !request()->has('zone_mapping') ? 'active' : '' }}" 
                   href="{{ route('vehicle.edit', Crypt::encrypt($vehicle->vehicle_id)) }}" 
                   role="tab">
                    Edit Vehicle
                </a>
            </li>
            @php
                $roleIds = [11, 35, 76, 111, 130, 132, 133, 135, 136, 137, 138, 139, 140];     
            @endphp
            
            @if(in_array(auth()->user()->role_id, $roleIds))
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ request()->has('zone_mapping') ? 'active' : '' }}" 
                   href="{{ route('vehicle.edit', ['vehicle' => Crypt::encrypt($vehicle->vehicle_id), 'zone_mapping' => true, 'mapping_type' => 'port_port']) }}" 
                   role="tab">
                    Zone Mapping
                </a>
            </li>
            @endif
        </ul>
        
        <!-- Zone mapping subtabs, only shown when zone_mapping is active -->
        @if(request()->has('zone_mapping'))
        <ul class="nav nav-pills mb-4 d-flex justify-content-center" id="zone-mapping-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link port-port-tab {{ request()->get('mapping_type') == 'port_port' ? 'active' : '' }}" 
                   href="{{ route('vehicle.edit', ['vehicle' => Crypt::encrypt($vehicle->vehicle_id), 'zone_mapping' => true, 'mapping_type' => 'port_port']) }}" 
                   role="tab">
                    <span class="d-flex align-items-center">
                        <span>Port</span>
                        <i class="fa-solid fa-repeat mx-2" aria-hidden="true"></i>
                        <span>Port</span>
                    </span>
                </a>
            </li>
            
            <li class="nav-item" role="presentation">
                <a class="nav-link port-port-tab {{ request()->get('mapping_type') == 'port_attraction' ? 'active' : '' }}" 
                   href="{{ route('vehicle.edit', ['vehicle' => Crypt::encrypt($vehicle->vehicle_id), 'zone_mapping' => true, 'mapping_type' => 'port_attraction']) }}" 
                   role="tab">
                    <span class="d-flex align-items-center">
                        <span>Port</span>
                        <i class="fa-solid fa-repeat mx-2" aria-hidden="true"></i>
                        <span>Attraction</span>
                    </span>
                </a>
            </li>
            
            <li class="nav-item" role="presentation">
                <a class="nav-link port-port-tab {{ request()->get('mapping_type') == 'port_restaurant' ? 'active' : '' }}" 
                   href="{{ route('vehicle.edit', ['vehicle' => Crypt::encrypt($vehicle->vehicle_id), 'zone_mapping' => true, 'mapping_type' => 'port_restaurant']) }}" 
                   role="tab">
                    <span class="d-flex align-items-center">
                        <span>Port</span>
                        <i class="fa-solid fa-repeat mx-2" aria-hidden="true"></i>
                        <span>Restaurant</span>
                    </span>
                </a>
            </li>

            <!-- Existing tabs -->
            <li class="nav-item" role="presentation">
                <a class="nav-link port-port-tab {{ request()->get('mapping_type') == 'port_hotel' ? 'active' : '' }}" 
                   href="{{ route('vehicle.edit', ['vehicle' => Crypt::encrypt($vehicle->vehicle_id), 'zone_mapping' => true, 'mapping_type' => 'port_hotel']) }}" 
                   role="tab">
                    <span class="d-flex align-items-center">
                        <span>Port</span>
                        <i class="fa-solid fa-repeat mx-2" aria-hidden="true"></i>
                        <span>Hotel</span>
                    </span>
                </a>
            </li>
            
            <li class="nav-item" role="presentation">
                <a class="nav-link port-port-tab {{ request()->get('mapping_type') == 'hotel_attraction' ? 'active' : '' }}" 
                   href="{{ route('vehicle.edit', ['vehicle' => Crypt::encrypt($vehicle->vehicle_id), 'zone_mapping' => true, 'mapping_type' => 'hotel_attraction']) }}" 
                   role="tab">
                    <span class="d-flex align-items-center">
                        <span>Hotel</span>
                        <i class="fa-solid fa-repeat mx-2" aria-hidden="true"></i>
                        <span>Attraction</span>
                    </span>
                </a>
            </li>
            
            <li class="nav-item" role="presentation">
                <a class="nav-link port-port-tab {{ request()->get('mapping_type') == 'hotel_restaurant' ? 'active' : '' }}" 
                   href="{{ route('vehicle.edit', ['vehicle' => Crypt::encrypt($vehicle->vehicle_id), 'zone_mapping' => true, 'mapping_type' => 'hotel_restaurant']) }}" 
                   role="tab">
                    <span class="d-flex align-items-center">
                        <span>Hotel</span>
                        <i class="fa-solid fa-repeat mx-2" aria-hidden="true"></i>
                        <span>Restaurant</span>
                    </span>
                </a>
            </li>
            
            <li class="nav-item" role="presentation">
                <a class="nav-link port-port-tab {{ request()->get('mapping_type') == 'attraction_restaurant' ? 'active' : '' }}" 
                   href="{{ route('vehicle.edit', ['vehicle' => Crypt::encrypt($vehicle->vehicle_id), 'zone_mapping' => true, 'mapping_type' => 'attraction_restaurant']) }}" 
                   role="tab">
                    <span class="d-flex align-items-center">
                        <span>Attraction</span>
                        <i class="fa-solid fa-repeat mx-2" aria-hidden="true"></i>
                        <span>Restaurant</span>
                    </span>
                </a>
            </li>
        </ul>
        @endif
        
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                @if(request()->has('zone_mapping'))
                    <span class="d-flex align-items-center flex-wrap gap-2">
                        <span>Map Zones for Vehicle</span>
                        <span class="fw-bold text-primary">{{ $vehicle->vehicle_name }}</span>
                        <span class="text-danger"
                              data-bs-toggle="tooltip"
                              data-bs-placement="top"
                              data-bs-title="Note: Vice-versa prices will be the same (Zone A → Zone B = Zone B → Zone A).">
                            <i class="fas fa-info-circle"></i>
                        </span>
                    </span>
                @else
                    Edit Vehicle Details
                @endif
                <a href="{{ route('vehicle.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>

        @if ($errors->any())
            <div class="alert alert-danger border-0 border-start border-5 border-danger-subtle shadow-sm px-4 py-3 rounded-3">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                    <div>
                        <h6 class="mb-2 fw-semibold text-danger">Please fix the following errors:</h6>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li class="small">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif


            @if(!request()->has('zone_mapping'))
            <form id="vehicleForm" method="POST" action="{{ route('vehicle.update', Crypt::encrypt($vehicle->vehicle_id)) }}"
                enctype="multipart/form-data" class="card-body">
                @csrf
                @method('PUT')
                <!-- Hidden Fields -->
                <div id="vehicleDetailsContainer">
                    <div class="vehicle-form">
                        <div class="row">
                            <!-- Select DMC -->
                            @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 23 || auth()->user()->role_id == 25 || auth()->user()->role_id == 62 || auth()->user()->role_id == 46 || auth()->user()->role_id == 109 || auth()->user()->role_id == 110)
                            <div class="mb-3 col-md-3" id="dmc-container">
                                <label for="dmc" class="form-label"><strong>DMC</strong><span style="color: red; font-weight: bold;">*</span></label>
                                <select id="dmc" class="form-control" disabled>
                                    <option value="">Select DMC</option>
                                    @foreach ($dmcs as $dmc)
                                        <option value="{{ $dmc->userId }}" {{ $vehicle->dmc_id == $dmc->userId ? 'selected' : '' }}>{{ $dmc->company_name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="dmc_id" value="{{ $vehicle->dmc_id }}">
                            </div>
                            @endif
                            <!-- Select driver -->
                            
                            <div class="col-md-3">
                                <label for="driver_id" class="form-label"><strong>Select Driver</strong></label>
                                <select id="driver" type="text" class="form-select" name="driver_id"
                                    placeholder="">
                                    <option value="">Select driver</option>
                                    @foreach($drivers as $driver)
                                    <option {{$vehicle->driver_id == $driver->driver_id ? 'selected' : ''}} value="{{$driver->driver_id}}">{{$driver->name}}-{{$driver->license_no}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Vehicle Name -->
                                <input type="hidden" name="vehicle_id", value="{{$vehicle->vehicle_id}}">
                            <div class="col-md-3 mb-3">
                                <label for="vehicle_name" class="form-label"><strong>Vehicle Name</strong><span class="text-danger">*</span></label>
                                <input value="{{$vehicle->vehicle_name}}" type="text" class="form-control" name="vehicle_name" placeholder="Enter Vehicle Name" required>
                                @error('vehicle_name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Vehicle Type -->
                            <div class="col-md-3 mb-3">
                                <label for="vehicle_type" class="form-label"><strong>Vehicle
                                        Type</strong><span class="text-danger">*</span></label>
                                <input value="{{$vehicle->vehicle_type}}" type="text" class="form-control"
                                    name="vehicle_type" placeholder="Enter Vehicle Type" required>
                                @error('vehicle_type')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Vehicle Model -->
                            <div class="col-md-3 mb-3">
                                <label for="vehicle_model" class="form-label"><strong>Vehicle
                                        Model</strong><span class="text-danger">*</span></label>
                                <input value="{{$vehicle->vehicle_model}}" type="text" class="form-control"
                                    name="vehicle_model" placeholder="Enter Vehicle Model" required>
                                @error('vehicle_model')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Vehicle Color -->
                            <div class="col-md-3 mb-3">
                                <label for="vehicle_color" class="form-label"><strong>Vehicle Color</strong><span class="text-danger">*</span></label>
                                <input value="{{$vehicle->vehicle_color}}" type="text" class="form-control"
                                    name="vehicle_color" placeholder="Enter Vehicle Color" required>
                                @error('vehicle_color')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Model Year -->
                            <div class="col-md-3 mb-3">
                                <label for="model_year" class="form-label"><strong>Model Year</strong><span
                                        class="text-danger">*</span></label>
                                <input value="{{$vehicle->model_year}}" type="text" class="form-control"
                                    name="model_year" id="model_year" placeholder="Enter Model Year" required 
                                    oninput="validateModelYear(this)">
                                <small class="validation-message text-danger" id="model_year-validation-message"></small>
                                @error('model_year')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- Vehicle Plate No -->
                            <div class="col-md-3 mb-3">
                                <label for="vehicle_plate_no" class="form-label"><strong>Vehicle Plate No</strong><span class="text-danger">*</span></label>
                                <div class="readonly-field-container">
                                    <input value="{{$vehicle->vehicle_plate_no}}" id="vehicle_plate_no" type="text" 
                                        class="form-control readonly-field-styling" 
                                        name="vehicle_plate_no" 
                                        placeholder="Enter Vehicle Plate No"
                                        readonly>
                                </div>

                                <div class="field-info-message">
                                    <i class="fas fa-lock"></i> Vehicle plate number is locked and cannot be edited.
                                </div>

                                @error('vehicle_plate_no')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Seating Capacity-->
                            <div class="col-md-3 mb-3">
                                <label for="seating_capacity" class="form-label"><strong>Seating
                                        Capacity</strong><span class="text-danger">*</span></label>
                                <input value="{{$vehicle->seating_capacity}}" type="text"
                                    class="form-control" name="seating_capacity" id="seating_capacity"
                                    placeholder="Enter Seating Capacity" required
                                    oninput="validateSeatingCapacity(this)">
                                <small class="validation-message text-danger" id="seating_capacity-validation-message"></small>
                                @error('seating_capacity')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Seating Capacity(Arr/Dept) -->

                            <div class="col-md-3 mb-3">
                                <label for="city_tour_seating_capacity" class="form-label"><strong>Seating
                                        Capacity(Arr/Dept)</strong><span class="text-danger">*</span></label>
                                <input value="{{$vehicle->city_tour_seating_capacity}}" type="text"
                                    class="form-control" name="city_tour_seating_capacity" id="city_tour_seating_capacity"
                                    placeholder="Enter Seating Capacity" required
                                    oninput="validateSeatingCapacity(this)">
                                <small class="validation-message text-danger" id="city_tour_seating_capacity-validation-message"></small>
                                @error('city_tour_seating_capacity')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- City Tour No of Guides -->
                            <div class="col-md-3 mb-3">
                                <label for="city_tour_guides" class="form-label"><strong>No of Guides</strong><span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="city_tour_guides" id="city_tour_guides"
                                    placeholder="Enter No of Guides" value="{{ $vehicle->city_tour_guides }}" required>
                                @error('city_tour_guides')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- City Name -->
                            <div class="col-md-3 mb-3">
                                <label for="cities" class="form-label"><strong>City Name</strong><span class="text-danger">*</span></label>
                                <select name="city_name" id="cities" class="form-control">
                                    <option value="">Select a city</option>
                                    @foreach($city as $c)
                                        <option {{$c->name == $vehicle->city ? 'selected' : ''}} value="{{$c->name}}">{{$c->name}}</option>
                                    @endforeach
                                </select>
                                @error('city_name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Sharable -->
                            <!-- <div class="col-md-3 mb-3">
                                <label for="price_type" class="form-label">
                                    <strong>Base Price Type</strong>
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="price_type" id="price_type" class="form-control" required>
                                    <option value="">-- Select Type --</option>
                                    <option {{$vehicle->sharable == 1 ? 'selected' : ''}} value="1">Private</option>
                                    <option {{$vehicle->sharable == 2 ? 'selected' : ''}} value="2">Shared</option>
                                    <option {{$vehicle->sharable == 3 ? 'selected' : ''}} value="3">Both</option>
                                </select>
                                @error('price_type')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div> -->

                            <!-- Regular Pricing Fields -->

                            <fieldset id="tarrifs" class="border p-4 rounded mb-4">
                                <h5 class="card-title mb-3">Private Car Tarrifs</h5>
                                <fieldset id="taxi_day_charges" class="border p-4 rounded mb-4">
                                    <h5 class="card-title mb-3">Day Charges</h5>
                                    <div class="row">
                                        <!-- Base Price -->
                                        <div class="col-md-3 mb-3">
                                            <label for="base_price" class="form-label"><strong>Base Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.1" class="form-control" name="base_price" value="{{ $vehicle->base_price }}" placeholder="Enter Base Price" required>
                                            @error('base_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Cost per KM Below 10 -->
                                        <div class="col-md-3 mb-3">
                                            <label for="cost_per_km_below_10" class="form-label"><strong>Cost per KM Below 10</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="cost_per_km_below_10" value="{{ $vehicle->cost_per_km_below_10 }}" placeholder="Enter Cost" required>
                                            @error('cost_per_km_below_10')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Cost per KM 10 to 25 -->
                                        <div class="col-md-3 mb-3">
                                            <label for="cost_per_km_10_to_25" class="form-label"><strong>Cost per KM (10 to 25)</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="cost_per_km_10_to_25" value="{{ $vehicle->cost_per_km_10_to_25 }}" placeholder="Enter Cost" required>
                                            @error('cost_per_km_10_to_25')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Cost per KM Above 25 -->
                                        <div class="col-md-3 mb-3">
                                            <label for="cost_per_km_above_25" class="form-label"><strong>Cost per KM Above 25km</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="cost_per_km_above_25" value="{{ $vehicle->cost_per_km_above_25 }}" placeholder="Enter Cost" required>
                                            @error('cost_per_km_above_25')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Cost per Hour -->
                                        <div class="col-md-3 mb-3">
                                            <label for="cost_per_hour" class="form-label"><strong>Cost per Hour</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="cost_per_hour" value="{{ $vehicle->cost_per_hour }}" placeholder="Enter Cost" required>
                                            @error('cost_per_hour')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Cancel Cost -->
                                        <div class="col-md-3 mb-3">
                                            <label for="cancel_cost" class="form-label"><strong>Cancel Cost</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="cancel_cost" value="{{ $vehicle->cancel_cost }}" placeholder="Enter Cancel Cost" required>
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
                                        <!-- Base Price -->
                                        <div class="col-md-3 mb-3">
                                            <label for="night_base_price" class="form-label"><strong>Base Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.1" class="form-control" name="night_base_price" value="{{ $vehicle->night_base_price }}" placeholder="Enter Base Price" required>
                                            @error('night_base_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Night Cost per KM Below 10 -->
                                        <div class="col-md-3 mb-3">
                                            <label for="night_cost_per_km_below_10" class="form-label"><strong>Cost per KM Below 10km</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control auto-calculated" name="night_cost_per_km_below_10" value="{{ $vehicle->night_cost_per_km_below_10 }}" placeholder="Enter Cost for night" required>
                                            @error('night_cost_per_km_below_10')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Night Cost per KM 10 to 25 -->
                                        <div class="col-md-3 mb-3">
                                            <label for="night_cost_per_km_10_to_25" class="form-label"><strong>Cost per KM (10km to 25km)</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control auto-calculated" name="night_cost_per_km_10_to_25" value="{{ $vehicle->night_cost_per_km_10_to_25 }}" placeholder="Enter Cost for night" required>
                                            @error('night_cost_per_km_10_to_25')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Night Cost per KM Above 25 -->
                                        <div class="col-md-3 mb-3">
                                            <label for="night_cost_per_km_above_25" class="form-label"><strong>Cost per KM Above 25km</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control auto-calculated" name="night_cost_per_km_above_25" value="{{ $vehicle->night_cost_per_km_above_25 }}" placeholder="Enter Cost for night" required>
                                            @error('night_cost_per_km_above_25')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Cost per Hour(Night) -->
                                        <div class="col-md-3 mb-3">
                                            <label for="night_cost_per_hour" class="form-label"><strong>Cost per Hour</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control auto-calculated" name="night_cost_per_hour" value="{{ $vehicle->night_cost_per_hour }}" placeholder="Enter Cost" required>
                                            @error('night_cost_per_hour')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Cancel Cost -->
                                        <div class="col-md-3 mb-3">
                                            <label for="night_cancel_cost" class="form-label"><strong>Cancel Cost</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control auto-calculated" name="night_cancel_cost" value="{{ $vehicle->night_cancel_cost }}" placeholder="Enter Cancel Cost" required>
                                            @error('night_cancel_cost')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </fieldset>
                            </fieldset>

                            <!-- Sharable Price Fields -->
                            <!-- <fieldset id="sharablePrices" class="border p-4 rounded mb-4 {{ in_array($vehicle->sharable, [2,3]) ? '' : 'd-none' }}">

                                <h5 class="card-title mb-3">Shared Car Tarrifs</h5>
                                <fieldset id="taxi_day_charges" class="border p-4 rounded mb-4">
                                    <h5 class="card-title mb-3">Day Charges</h5>
                                    <div class="row">
                                        
                                        <div class="col-md-3 mb-3">
                                            <label for="sharable_base_price" class="form-label"><strong>Base Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.1" class="form-control" name="sharable_base_price" value="{{ $vehicle->sharable_base_price }}" placeholder="Enter Base Price">
                                            @error('sharable_base_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="sharable_cost_per_km_below_10" class="form-label"><strong>Cost per KM Below 10</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="sharable_cost_per_km_below_10" value="{{ $vehicle->sharable_cost_per_km_below_10 }}" placeholder="Enter Cost">
                                            @error('sharable_cost_per_km_below_10')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="sharable_cost_per_km_10_to_25" class="form-label"><strong>Cost per KM (10 to 25)</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="sharable_cost_per_km_10_to_25" value="{{ $vehicle->sharable_cost_per_km_10_to_25 }}" placeholder="Enter Cost">
                                            @error('sharable_cost_per_km_10_to_25')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-3 mb-3">
                                            <label for="sharable_cost_per_km_above_25" class="form-label"><strong>Cost per KM Above 25km</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="sharable_cost_per_km_above_25" value="{{ $vehicle->sharable_cost_per_km_above_25 }}" placeholder="Enter Cost">
                                            @error('sharable_cost_per_km_above_25')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-3 mb-3">
                                            <label for="sharable_cost_per_hour" class="form-label"><strong>Cost per Hour</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="sharable_cost_per_hour" value="{{ $vehicle->sharable_cost_per_hour }}" placeholder="Enter Cost">
                                            @error('sharable_cost_per_hour')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-3 mb-3">
                                            <label for="sharable_cancel_cost" class="form-label"><strong>Cancel Cost</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="sharable_cancel_cost" value="{{ $vehicle->sharable_cancel_cost }}" placeholder="Enter Cancel Cost">
                                            @error('sharable_cancel_cost')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </fieldset>
                                
                                <fieldset id="taxi_night_charges" class="border p-4 rounded mb-4">
                                    <h5 class="card-title mb-3">Night Charges</h5>
                                    <div class="row">
                                        
                                        <div class="col-md-3 mb-3">
                                            <label for="sharable_night_base_price" class="form-label"><strong>Base Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.1" class="form-control" name="sharable_night_base_price" value="{{ $vehicle->sharable_night_base_price }}" placeholder="Enter Base Price">
                                            @error('sharable_night_base_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="sharable_night_cost_per_km_below_10" class="form-label"><strong>Cost per KM Below 10km</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="sharable_night_cost_per_km_below_10" value="{{ $vehicle->sharable_night_cost_per_km_below_10 }}" placeholder="Enter Cost for night">
                                            @error('sharable_night_cost_per_km_below_10')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="sharable_night_cost_per_km_10_to_25" class="form-label"><strong>Cost per KM (10km to 25km)</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="sharable_night_cost_per_km_10_to_25" value="{{ $vehicle->sharable_night_cost_per_km_10_to_25 }}" placeholder="Enter Cost for night">
                                            @error('sharable_night_cost_per_km_10_to_25')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-3 mb-3">
                                            <label for="sharable_night_cost_per_km_above_25" class="form-label"><strong>Cost per KM Above 25km</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="sharable_night_cost_per_km_above_25" value="{{ $vehicle->sharable_night_cost_per_km_above_25 }}" placeholder="Enter Cost for night">
                                            @error('sharable_night_cost_per_km_above_25')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="sharable_night_cost_per_hour" class="form-label"><strong>Cost per Hour</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="sharable_night_cost_per_hour" value="{{ $vehicle->sharable_night_cost_per_hour }}" placeholder="Enter Cost">
                                            @error('sharable_night_cost_per_hour')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-3 mb-3">
                                            <label for="sharable_night_cancel_cost" class="form-label"><strong>Cancel Cost</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="sharable_night_cancel_cost" value="{{ $vehicle->sharable_night_cancel_cost }}" placeholder="Enter Cancel Cost">
                                            @error('sharable_night_cancel_cost')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </fieldset>
                            </fieldset> -->
                            <!-- Sharable Toggle Switch -->
                            <div class="col-md-3 mb-3">
                                <label for="sharable" class="form-label d-block">
                                    <strong>Vehicle Sharing Option</strong>
                                </label>
                                <select class="form-select mt-2" id="sharable" name="sharable">
                                    <option value="1" {{ $vehicle->sharable == 1 ? 'selected' : '' }}>Private</option>
                                    <option value="2" {{ $vehicle->sharable == 2 ? 'selected' : '' }}>Sharable</option>
                                    <option value="3" {{ $vehicle->sharable == 3 ? 'selected' : '' }}>Both</option>
                                </select>
                            </div>
                            <style>
                                /* Custom toggle switch styling */
                                .form-check-input[type="checkbox"] {
                                    background-color:rgb(237, 243, 249);
                                    border-color: #ced4da;
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
                            

                            <div class="col-md-4">
                                <div>
                                    <label for="master_image" class="form-label"><strong>Vehicle
                                            Image</strong></label>
                                    <div id="master-drop-area" class="form-control"
                                        style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px;">
                                        Drag & Drop your files here or click to upload.
                                        <input type="file" id="master_image" name="master_image" multiple
                                            style="display: none;">
                                    </div>
                                </div>
                                <div id="master-preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                    style="max-width: 30%; overflow-x: auto; white-space: nowrap;"></div>

                                @if($vehicle->image)
                                <div class="image-preview-container d-flex flex-wrap gap-2">
                                    <div class="image-preview-wrapper position-relative">
                                        <img src="{{$vehicle->image}}" alt="Vehicle Image"
                                            style="max-width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                                        <button
                                            class="delete-image-btn position-absolute top-0 end-0 btn btn-sm btn-danger"
                                            data-image="{{ $vehicle->image }}"
                                            style="width: 20px; height: 20px; line-height: 18px; padding: 0; text-align: center; font-size: 14px; z-index: 1;">
                                            &times;
                                        </button>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Description -->
                            <div class="col-md-12 mb-3">
                                <label for="description"
                                    class="form-label"><strong>Description</strong><span
                                        class="text-danger">*</span></label>
                                <textarea id="summernote" name="description" class="form-control" rows="10">{{ old('description', $vehicle->description) }}</textarea required>
                                @error('description')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Status -->
                        <div class="mt-4 form-check form-switch">
                            <label for="vehicle_status" class="form-label"><strong>Status</strong></label>
                            <span style="color: red; font-weight: bold;">*</span>
                            <input {{$vehicle->is_available == 1 ? 'checked' : ''}} class="form-check-input"
                                name="vehicle_status" type="checkbox" id="vehicle_status" value="1">
                            <label class="form-check-label"></label>
                            @error('vehicle_status')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-3 mt-4">
                        <button type="submit" class="btn btn-primary px-4">Update</button>
                    </div>
            </form>
            @else
            <!-- Zone Mapping Form -->
            <form id="zoneMappingForm" method="POST" action="{{ route('vehicle.map_zones') }}" class="card-body">
                @csrf
                <input type="hidden" name="vehicle_id" value="{{ $vehicle->vehicle_id }}">
                <input type="hidden" name="mapping_type" value="{{ request()->get('mapping_type') }}">

                @php
                    $portsSorted = collect($ports ?? [])
                        ->sortByDesc(fn ($p) => mb_strtolower(trim((string) ($p->port_name ?? ''))))
                        ->values();

                    $zonesSorted = collect($zones ?? [])
                        ->sortByDesc(fn ($z) => mb_strtolower(trim((string) ($z->zone_name ?? ''))))
                        ->values();
                @endphp
                
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h6>
                            @if(request()->get('mapping_type') == 'port_port')
                                Map Port to Port Transportation
                            @elseif(request()->get('mapping_type') == 'port_attraction')
                                Map Port to Attraction Transportation
                            @elseif(request()->get('mapping_type') == 'port_restaurant')
                                Map Port to Restaurant Transportation
                            @elseif(request()->get('mapping_type') == 'port_hotel')
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
                
                <!-- Zone Selection Fields with Enhanced UI -->
                <div class="row mb-3">
                    @if(request()->get('mapping_type') == 'port_port')
                    <div class="col-md-6">
                        <label for="from_zone" class="form-label"><strong>From Port</strong><span class="text-danger">*</span></label>
                        <select id="from_zone" name="from_zone" class="form-select" data-show-description="true">
                            <option value="">-- Select From Port --</option>
                            @foreach($portsSorted as $port)
                                <option value="{{ $port->port_id }}" 
                                        data-type="Port" 
                                        data-description="{{ $port->type ?? 'No Type Available' }}">
                                    {{ $port->port_name }} - {{ $port->type ?? 'Unknown Type' }}
                                </option>
                            @endforeach
                        </select>
                        <div id="from_zone_description" class="mt-2 zone-description d-none">
                            <div class="card">
                                <div class="card-body bg-light p-3">
                                    <h6 class="card-subtitle text-muted mb-2"><span id="from_zone_type_label">Port</span>: <span id="from_zone_name_label"></span></h6>
                                    <p class="card-text" id="from_zone_description_text"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @elseif(request()->get('mapping_type') == 'port_attraction')
                    <div class="col-md-6">
                        <label for="from_zone" class="form-label"><strong>From Port</strong><span class="text-danger">*</span></label>
                        <select id="from_zone" name="from_zone" class="form-select" data-show-description="true">
                            <option value="">-- Select From Port --</option>
                            @foreach($portsSorted as $port)
                                <option value="{{ $port->port_id }}" 
                                        data-type="Port" 
                                        data-description="{{ $port->type ?? 'No Type Available' }}">
                                    {{ $port->port_name }} - {{ $port->type ?? 'Unknown Type' }}
                                </option>
                            @endforeach
                        </select>
                        <div id="from_zone_description" class="mt-2 zone-description d-none">
                            <div class="card">
                                <div class="card-body bg-light p-3">
                                    <h6 class="card-subtitle text-muted mb-2"><span id="from_zone_type_label">Port</span>: <span id="from_zone_name_label"></span></h6>
                                    <p class="card-text" id="from_zone_description_text"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @elseif(request()->get('mapping_type') == 'port_restaurant')
                    <div class="col-md-6">
                        <label for="from_zone" class="form-label"><strong>From Port</strong><span class="text-danger">*</span></label>
                        <select id="from_zone" name="from_zone" class="form-select" data-show-description="true">
                            <option value="">-- Select From Port --</option>
                            @foreach($portsSorted as $port)
                                <option value="{{ $port->port_id }}" 
                                        data-type="Port" 
                                        data-description="{{ $port->type ?? 'No Type Available' }}">
                                    {{ $port->port_name }} - {{ $port->type ?? 'Unknown Type' }}
                                </option>
                            @endforeach
                        </select>
                        <div id="from_zone_description" class="mt-2 zone-description d-none">
                            <div class="card">
                                <div class="card-body bg-light p-3">
                                    <h6 class="card-subtitle text-muted mb-2"><span id="from_zone_type_label">Port</span>: <span id="from_zone_name_label"></span></h6>
                                    <p class="card-text" id="from_zone_description_text"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @elseif(request()->get('mapping_type') == 'port_hotel')
                    <div class="col-md-6">
                        <label for="from_zone" class="form-label"><strong>From Port</strong><span class="text-danger">*</span></label>
                        <select id="from_zone" name="from_zone" class="form-select" data-show-description="true">
                            <option value="">-- Select From Port --</option>
                            @foreach($portsSorted as $port)
                                <option value="{{ $port->port_id }}" 
                                        data-type="Port" 
                                        data-description="{{ $port->type ?? 'No Type Available' }}">
                                    {{ $port->port_name }} - {{ $port->type ?? 'Unknown Type' }}
                                </option>
                            @endforeach
                        </select>
                        <div id="from_zone_description" class="mt-2 zone-description d-none">
                            <div class="card">
                                <div class="card-body bg-light p-3">
                                    <h6 class="card-subtitle text-muted mb-2"><span id="from_zone_type_label">Port</span>: <span id="from_zone_name_label"></span></h6>
                                    <p class="card-text" id="from_zone_description_text"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @elseif(request()->get('mapping_type') == 'hotel_attraction')
                    <div class="col-md-6">
                        <label for="from_zone" class="form-label"><strong>Hotel</strong><span class="text-danger">*</span></label>
                        <select id="from_zone" name="from_zone" class="form-select" data-show-description="true">
                            <option value="">-- Select Hotel --</option>
                            @foreach($zonesSorted as $zone)
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
                                        $hotelNames = $assignedHotels->pluck('name')->filter()->implode(', ');
                                        $hotelItems = $assignedHotels->map(fn($h) => ['name' => $h->name ?? '', 'image' => ($h->main_image ?? '') ? (str_starts_with($h->main_image ?? '', 'http') || str_starts_with($h->main_image ?? '', '/') ? $h->main_image : asset($h->main_image)) : ''])->toArray();
                                    @endphp
                                    <option value="{{ $zone->zone_id }}" 
                                            data-type="{{ $zone->zone_type }}" 
                                            data-description="{{ $zone->description ?? 'No description available' }}"
                                            data-zone-name="{{ $zone->zone_name }}"
                                            data-hotel-count="{{ $hotelCount }}"
                                            data-item-names="{{ e($hotelNames) }}"
                                            data-item-images="{{ e(json_encode($hotelItems)) }}">
                                        {{ $zone->zone_name }} ({{ $hotelCount }} hotels) - {{ html_entity_decode(strip_tags($zone->description)) ?? 'Unknown Description' }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <div id="from_zone_description" class="mt-2 zone-description d-none">
                            <div class="card">
                                <div class="card-body bg-light p-3">
                                    <h6 class="card-subtitle text-muted mb-2"><span id="from_zone_type_label">Hotel</span>: <span id="from_zone_name_label"></span></h6>
                                    <p class="card-text" id="from_zone_description_text"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @elseif(request()->get('mapping_type') == 'hotel_restaurant')
                    <div class="col-md-6">
                        <label for="from_zone" class="form-label"><strong>Hotel</strong><span class="text-danger">*</span></label>
                        <select id="from_zone" name="from_zone" class="form-select" data-show-description="true">
                            <option value="">-- Select Hotel --</option>
                            @foreach($zonesSorted as $zone)
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
                                        $hotelNames = $assignedHotels->pluck('name')->filter()->implode(', ');
                                        $hotelItems = $assignedHotels->map(fn($h) => ['name' => $h->name ?? '', 'image' => ($h->main_image ?? '') ? (str_starts_with($h->main_image ?? '', 'http') || str_starts_with($h->main_image ?? '', '/') ? $h->main_image : asset($h->main_image)) : ''])->toArray();
                                    @endphp
                                    <option value="{{ $zone->zone_id }}" 
                                            data-type="{{ $zone->zone_type }}" 
                                            data-description="{{ $zone->description ?? 'No description available' }}"
                                            data-zone-name="{{ $zone->zone_name }}"
                                            data-hotel-count="{{ $hotelCount }}"
                                            data-item-names="{{ e($hotelNames) }}"
                                            data-item-images="{{ e(json_encode($hotelItems)) }}">
                                        {{ $zone->zone_name }} ({{ $hotelCount }} hotels) - {{ html_entity_decode(strip_tags($zone->description)) ?? 'Unknown Description' }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <div id="from_zone_description" class="mt-2 zone-description d-none">
                            <div class="card">
                                <div class="card-body bg-light p-3">
                                    <h6 class="card-subtitle text-muted mb-2"><span id="from_zone_type_label">Hotel</span>: <span id="from_zone_name_label"></span></h6>
                                    <p class="card-text" id="from_zone_description_text"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @elseif(request()->get('mapping_type') == 'attraction_restaurant')
                    <div class="col-md-6">
                        <label for="from_zone" class="form-label"><strong>Attraction</strong><span class="text-danger">*</span></label>
                        <select id="from_zone" name="from_zone" class="form-select" data-show-description="true">
                            <option value="">-- Select Attraction --</option>
                            @foreach($zonesSorted as $zone)
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
                                        $attractionNames = $assignedAttractions->pluck('name')->filter()->implode(', ');
                                        $attractionItems = $assignedAttractions->map(fn($a) => ['name' => $a->name ?? '', 'image' => ($a->master_image ?? '') ? (str_starts_with($a->master_image ?? '', 'http') || str_starts_with($a->master_image ?? '', '/') ? $a->master_image : asset($a->master_image)) : ''])->toArray();
                                    @endphp
                                    <option value="{{ $zone->zone_id }}" 
                                            data-type="{{ $zone->zone_type }}" 
                                            data-description="{{ $zone->description ?? 'No description available' }}"
                                            data-zone-name="{{ $zone->zone_name }}"
                                            data-attraction-count="{{ $attractionCount }}"
                                            data-item-names="{{ e($attractionNames) }}"
                                            data-item-images="{{ e(json_encode($attractionItems)) }}">
                                        {{ $zone->zone_name }} ({{ $attractionCount }} attractions) - {{ html_entity_decode(strip_tags($zone->description)) ?? 'Unknown Description' }}
                                    </option>
                                @endif
                            @endforeach

                        </select>
                        <div id="from_zone_description" class="mt-2 zone-description d-none">
                            <div class="card">
                                <div class="card-body bg-light p-3">
                                    <h6 class="card-subtitle text-muted mb-2"><span id="from_zone_type_label">Attraction</span>: <span id="from_zone_name_label"></span></h6>
                                    <p class="card-text" id="from_zone_description_text"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if(!in_array(request()->get('mapping_type'), ['port_port','port_attraction','port_restaurant','port_hotel','hotel_attraction','hotel_restaurant','attraction_restaurant']))
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" id="addMapping" class="btn btn-primary w-100">Add Mapping</button>
                        </div>
                    @endif
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>
                                            @if(request()->get('mapping_type') == 'port_port')
                                                From Port
                                            @elseif(request()->get('mapping_type') == 'port_attraction')
                                                From Port
                                            @elseif(request()->get('mapping_type') == 'port_restaurant')
                                                From Port
                                            @elseif(request()->get('mapping_type') == 'port_hotel')
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
                                            @if(request()->get('mapping_type') == 'port_port')
                                                To Port
                                            @elseif(request()->get('mapping_type') == 'port_attraction')
                                                To Attraction
                                            @elseif(request()->get('mapping_type') == 'port_restaurant')
                                                To Restaurant
                                            @elseif(request()->get('mapping_type') == 'port_hotel')
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
                                                $fromType = '';
                                                $toType = '';
                                                
                                                // Determine from_zone_type
                                                if ($mapping->from_zone_type) {
                                                    $fromType = $mapping->from_zone_type;
                                                } else {
                                                    // For port-related mappings, set from type as Port
                                                    if (in_array(request()->get('mapping_type'), ['port_port', 'port_attraction', 'port_restaurant', 'port_hotel'])) {
                                                        $fromType = 'Port';
                                                    }
                                                }
                                                
                                                // Determine to_zone_type
                                                if ($mapping->to_zone_type) {
                                                    $toType = $mapping->to_zone_type;
                                                } else {
                                                    // For port to port mapping, set to type as Port
                                                    if (request()->get('mapping_type') === 'port_port') {
                                                        $toType = 'Port';
                                                    }
                                                }
                                                
                                                // Show mapping based on mapping type
                                                if(request()->get('mapping_type') == 'port_port' && $fromType == 'Port' && $toType == 'Port') {
                                                    $showMapping = true;
                                                } elseif(request()->get('mapping_type') == 'port_attraction' && $fromType == 'Port' && $toType == 'Attraction') {
                                                    $showMapping = true;
                                                } elseif(request()->get('mapping_type') == 'port_restaurant' && $fromType == 'Port' && $toType == 'Restaurant') {
                                                    $showMapping = true;
                                                } elseif(request()->get('mapping_type') == 'port_hotel' && $fromType == 'Port' && $toType == 'Hotel') {
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
                                            <tr data-from="{{ $mapping->from_zone_id }}" 
                                                data-to="{{ $mapping->to_zone_id }}" 
                                                data-from-type="{{ $fromType }}" 
                                                data-to-type="{{ $toType }}"
                                                data-mapping-id="{{ $mapping->mapping_id }}">
                                                
                                                @if(request()->get('mapping_type') == 'port_port')
                                                    @php
                                                        // Find port names from ports array
                                                        $fromPortName = '';
                                                        $toPortName = '';
                                                        $fromPortType = '';
                                                        $toPortType = '';
                                                        
                                                        foreach($ports as $port) {
                                                            if($port->port_id == $mapping->from_zone_id) {
                                                                $fromPortName = $port->port_name;
                                                                $fromPortType = $port->type;
                                                            }
                                                            if($port->port_id == $mapping->to_zone_id) {
                                                                $toPortName = $port->port_name;
                                                                $toPortType = $port->type;
                                                            }
                                                        }
                                                    @endphp
                                                    <td>{{ $fromPortName ?: 'Port ID: ' . $mapping->from_zone_id }} - {{ $fromPortType }}</td>
                                                    <td>{{ $toPortName ?: 'Port ID: ' . $mapping->to_zone_id }} - {{ $toPortType }}</td>
                                                @elseif(in_array(request()->get('mapping_type'), ['port_attraction', 'port_restaurant', 'port_hotel']))
                                                    @php
                                                        $portName = '';
                                                        $portType = '';
                                                        foreach($ports as $port) {
                                                            if($port->port_id == $mapping->from_zone_id) {
                                                                $portName = $port->port_name;
                                                                $portType = $port->type;
                                                                break;
                                                            }
                                                        }
                                                        $toZoneItems = $mappingZoneItems[$mapping->mapping_id]['to'] ?? [];
                                                    @endphp
                                                    <td>{{ $portName ?: 'Port ID: ' . $mapping->from_zone_id }} - {{ $portType }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge bg-{{ ($mapping->toZone->zone_type ?? '') == 'Hotel' ? 'success' : (($mapping->toZone->zone_type ?? '') == 'Attraction' ? 'info' : 'warning') }} me-2">{{ $mapping->toZone->zone_type ?? $toType }}</span>
                                                            <span class="zone-cell-hover" data-zone-items="{{ json_encode($toZoneItems) }}" data-zone-type="{{ $mapping->toZone->zone_type ?? $toType }}">{{ strip_tags($mapping->toZone->zone_name ?? 'Zone ID: ' . $mapping->to_zone_id) }} - {{ html_entity_decode(strip_tags($mapping->toZone?->description ?? 'No description for this zone')) }}</span>
                                                        </div>
                                                    </td>
                                                @else
                                                    @php
                                                        $fromZoneItems = $mappingZoneItems[$mapping->mapping_id]['from'] ?? [];
                                                        $toZoneItems = $mappingZoneItems[$mapping->mapping_id]['to'] ?? [];
                                                    @endphp
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge bg-{{ $fromType == 'Hotel' ? 'success' : ($fromType == 'Attraction' ? 'info' : ($fromType == 'Restaurant' ? 'warning' : 'secondary')) }} me-2">{{ $fromType }}</span>
                                                            <span class="zone-cell-hover" data-zone-items="{{ json_encode($fromZoneItems) }}" data-zone-type="{{ $fromType }}">{{ strip_tags($mapping->fromZone->zone_name ?? 'Zone ID: ' . $mapping->from_zone_id) }} - {{ html_entity_decode(strip_tags($mapping->fromZone?->description ?? 'No description for this zone')) }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge bg-{{ $toType == 'Hotel' ? 'success' : ($toType == 'Attraction' ? 'info' : ($toType == 'Restaurant' ? 'warning' : 'secondary')) }} me-2">{{ $toType }}</span>
                                                            <span class="zone-cell-hover" data-zone-items="{{ json_encode($toZoneItems) }}" data-zone-type="{{ $toType }}">{{ strip_tags($mapping->toZone->zone_name ?? 'Zone ID: ' . $mapping->to_zone_id) }} - {{ html_entity_decode(strip_tags($mapping->toZone?->description ?? 'No description for this zone')) }}</span>
                                                        </div>
                                                    </td>
                                                @endif
                                                <td>
                                                    <input type="number" name="private_prices[{{ $mapping->from_zone_id }}][{{ $mapping->to_zone_id }}]" 
                                                        class="form-control" value="{{ $mapping->private_price }}" step="0.01" min="0">
                                                </td>
                                                <td>
                                                    <input type="number" name="shared_prices[{{ $mapping->from_zone_id }}][{{ $mapping->to_zone_id }}]" 
                                                        class="form-control" value="{{ $mapping->shared_price }}" step="0.01" min="0">
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger remove-mapping" data-mapping-id="{{ $mapping->mapping_id }}">
                                                        <i class="ri-delete-bin-line"></i> Remove
                                                    </button>
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

            @if(request()->get('mapping_type') == 'port_port')
                @php
                    $portPortMappings = collect($mappings ?? [])
                        ->filter(function ($m) {
                            return (($m->from_zone_type ?? 'Port') === 'Port') && (($m->to_zone_type ?? 'Port') === 'Port');
                        })
                        ->map(function ($m) {
                            return [
                                'from' => (string) $m->from_zone_id,
                                'to' => (string) $m->to_zone_id,
                                'private_price' => (float) ($m->private_price ?? 0),
                                'shared_price' => (float) ($m->shared_price ?? 0),
                                'mapping_id' => $m->mapping_id ?? null,
                            ];
                        })
                        ->values();
                @endphp

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const mappingType = document.querySelector('input[name="mapping_type"]')?.value;
                        if (mappingType !== 'port_port') return;

                        const ports = @json($ports ?? []);
                        const existing = @json($portPortMappings);

                        const portById = new Map((ports || []).map(p => [String(p.port_id), p]));
                        const existingKeyed = new Map((existing || []).map(m => [`${String(m.from)}__${String(m.to)}`, m]));

                        const tbody = document.getElementById('mappingsTableBody');

                        function buildRow(fromId, toId) {
                            const fromPort = portById.get(String(fromId));
                            const toPort = portById.get(String(toId));

                            const fromText = fromPort
                                ? `${fromPort.port_name ?? fromId} - ${fromPort.type ?? ''}`.trim()
                                : `Port ID: ${fromId}`;

                            const toText = toPort
                                ? `${toPort.port_name ?? toId} - ${toPort.type ?? ''}`.trim()
                                : `Port ID: ${toId}`;

                            const key = `${String(fromId)}__${String(toId)}`;
                            const existingMapping = existingKeyed.get(key);

                            const tr = document.createElement('tr');
                            tr.setAttribute('data-from', String(fromId));
                            tr.setAttribute('data-to', String(toId));
                            tr.setAttribute('data-from-type', 'Port');
                            tr.setAttribute('data-to-type', 'Port');
                            if (existingMapping?.mapping_id) tr.setAttribute('data-mapping-id', existingMapping.mapping_id);

                            tr.innerHTML = `
                                <td>${escapeHtml(fromText)}</td>
                                <td>${escapeHtml(toText)}</td>
                                <td>
                                    <input type="number"
                                           name="private_prices[${String(fromId)}][${String(toId)}]"
                                           class="form-control"
                                           value="${Number(existingMapping?.private_price ?? 0)}"
                                           step="0.01"
                                           min="0">
                                </td>
                                <td>
                                    <input type="number"
                                           name="shared_prices[${String(fromId)}][${String(toId)}]"
                                           class="form-control"
                                           value="${Number(existingMapping?.shared_price ?? 0)}"
                                           step="0.01"
                                           min="0">
                                </td>
                                <td>
                                    <button type="button"
                                            class="btn btn-sm btn-danger remove-mapping"
                                            ${existingMapping?.mapping_id ? `data-mapping-id="${escapeHtml(String(existingMapping.mapping_id))}"` : ''}>
                                        <i class="ri-delete-bin-line"></i> Remove
                                    </button>
                                </td>
                            `;

                            return tr;
                        }

                        function escapeHtml(str) {
                            return String(str)
                                .replaceAll('&', '&amp;')
                                .replaceAll('<', '&lt;')
                                .replaceAll('>', '&gt;')
                                .replaceAll('"', '&quot;')
                                .replaceAll("'", '&#039;');
                        }

                        function priceScore(m) {
                            return Math.max(Number(m?.private_price ?? 0), Number(m?.shared_price ?? 0));
                        }

                        function renderExistingRows() {
                            if (!tbody) return;
                            tbody.innerHTML = '';

                            const rows = (existing || [])
                                .filter(m => priceScore(m) > 0)
                                .slice()
                                .sort((a, b) => priceScore(b) - priceScore(a));

                            if (!rows.length) {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `<td colspan="5" class="text-center text-muted py-4">Select a <strong>From Port</strong> to auto-populate all destination ports.</td>`;
                                tbody.appendChild(tr);
                                return;
                            }

                            rows.forEach(m => {
                                tbody.appendChild(buildRow(String(m.from), String(m.to)));
                            });
                        }

                        function renderForFromPort(fromId) {
                            if (!tbody) return;
                            tbody.innerHTML = '';

                            if (!fromId) {
                                renderExistingRows();
                                return;
                            }

                            const fromStr = String(fromId);
                            (ports || [])
                                .slice()
                                .filter(p => String(p.port_id) && String(p.port_id) !== fromStr)
                                .sort((a, b) => {
                                    const at = `${a?.port_name ?? a?.port_id ?? ''} - ${a?.type ?? ''}`.trim();
                                    const bt = `${b?.port_name ?? b?.port_id ?? ''} - ${b?.type ?? ''}`.trim();
                                    return bt.localeCompare(at);
                                })
                                .forEach(p => {
                                    tbody.appendChild(buildRow(fromStr, String(p.port_id)));
                                });
                        }

                        // Bind in a way that works with Select2 too
                        function getFromValue() {
                            const el = document.getElementById('from_zone');
                            return el ? el.value : '';
                        }

                        document.addEventListener('change', function (e) {
                            if (e.target && e.target.id === 'from_zone') {
                                renderForFromPort(getFromValue());
                            }
                        });

                        if (window.jQuery) {
                            window.jQuery(document).on('select2:select select2:clear', '#from_zone', function () {
                                renderForFromPort(getFromValue());
                            });
                        }

                        // Select2 can apply the selected value after init, so render twice (immediate + next tick)
                        renderForFromPort(getFromValue());
                        setTimeout(function () { renderForFromPort(getFromValue()); }, 0);
                    });
                </script>
            @endif

            @if(request()->get('mapping_type') == 'port_attraction')
                @php
                    $portAttractionMappings = collect($mappings ?? [])
                        ->filter(function ($m) {
                            return (($m->from_zone_type ?? 'Port') === 'Port') && (($m->to_zone_type ?? 'Attraction') === 'Attraction');
                        })
                        ->map(function ($m) {
                            return [
                                'from' => (string) $m->from_zone_id,
                                'to' => (string) $m->to_zone_id,
                                'private_price' => (float) ($m->private_price ?? 0),
                                'shared_price' => (float) ($m->shared_price ?? 0),
                                'mapping_id' => $m->mapping_id ?? null,
                            ];
                        })
                        ->values();

                    $toAttractions = collect($zones ?? [])
                        ->filter(fn ($z) => ($z->zone_type ?? null) === 'Attraction')
                        ->map(fn ($z) => [
                            'zone_id' => (string) $z->zone_id,
                            'zone_name' => (string) ($z->zone_name ?? ''),
                            'zone_type' => (string) ($z->zone_type ?? 'Attraction'),
                            'description' => (string) ($z->description ?? ''),
                        ])
                        ->values();
                @endphp

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const mappingType = document.querySelector('input[name="mapping_type"]')?.value;
                        if (mappingType !== 'port_attraction') return;

                        const ports = @json($ports ?? []);
                        const toZones = @json($toAttractions);
                        const existing = @json($portAttractionMappings);

                        const portById = new Map((ports || []).map(p => [String(p.port_id), p]));
                        const toById = new Map((toZones || []).map(z => [String(z.zone_id), z]));
                        const existingKeyed = new Map((existing || []).map(m => [`${String(m.from)}__${String(m.to)}`, m]));

                        const tbody = document.getElementById('mappingsTableBody');

                        function escapeHtml(str) {
                            return String(str)
                                .replaceAll('&', '&amp;')
                                .replaceAll('<', '&lt;')
                                .replaceAll('>', '&gt;')
                                .replaceAll('"', '&quot;')
                                .replaceAll("'", '&#039;');
                        }

                        function portText(portId) {
                            const p = portById.get(String(portId));
                            if (!p) return `Port ID: ${portId}`;
                            return `${p.port_name ?? portId} - ${p.type ?? ''}`.trim();
                        }

                        function zoneText(zoneId) {
                            const z = toById.get(String(zoneId));
                            if (!z) return `Zone ID: ${zoneId}`;
                            const name = (z.zone_name || '').trim();
                            return name ? `${name}` : `Zone ID: ${zoneId}`;
                        }

                        function buildRow(fromId, toId) {
                            const key = `${String(fromId)}__${String(toId)}`;
                            const existingMapping = existingKeyed.get(key);

                            const tr = document.createElement('tr');
                            tr.setAttribute('data-from', String(fromId));
                            tr.setAttribute('data-to', String(toId));
                            tr.setAttribute('data-from-type', 'Port');
                            tr.setAttribute('data-to-type', 'Attraction');
                            if (existingMapping?.mapping_id) tr.setAttribute('data-mapping-id', existingMapping.mapping_id);

                            tr.innerHTML = `
                                <td>${escapeHtml(portText(fromId))}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-info me-2">Attraction</span>
                                        <span>${escapeHtml(zoneText(toId))}</span>
                                    </div>
                                </td>
                                <td>
                                    <input type="number"
                                           name="private_prices[${String(fromId)}][${String(toId)}]"
                                           class="form-control"
                                           value="${Number(existingMapping?.private_price ?? 0)}"
                                           step="0.01"
                                           min="0">
                                </td>
                                <td>
                                    <input type="number"
                                           name="shared_prices[${String(fromId)}][${String(toId)}]"
                                           class="form-control"
                                           value="${Number(existingMapping?.shared_price ?? 0)}"
                                           step="0.01"
                                           min="0">
                                </td>
                                <td>
                                    <button type="button"
                                            class="btn btn-sm btn-danger remove-mapping"
                                            ${existingMapping?.mapping_id ? `data-mapping-id="${escapeHtml(String(existingMapping.mapping_id))}"` : ''}>
                                        <i class="ri-delete-bin-line"></i> Remove
                                    </button>
                                </td>
                            `;

                            return tr;
                        }

                        function priceScore(m) {
                            return Math.max(Number(m?.private_price ?? 0), Number(m?.shared_price ?? 0));
                        }

                        function renderExistingRows() {
                            if (!tbody) return;
                            tbody.innerHTML = '';

                            const rows = (existing || [])
                                .filter(m => priceScore(m) > 0)
                                .slice()
                                .sort((a, b) => priceScore(b) - priceScore(a));

                            if (!rows.length) {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `<td colspan="5" class="text-center text-muted py-4">Select a <strong>From Port</strong> to auto-populate all attractions.</td>`;
                                tbody.appendChild(tr);
                                return;
                            }

                            rows.forEach(m => tbody.appendChild(buildRow(String(m.from), String(m.to))));
                        }

                        function renderForFromPort(fromId) {
                            if (!tbody) return;
                            tbody.innerHTML = '';

                            if (!fromId) {
                                renderExistingRows();
                                return;
                            }

                            const fromStr = String(fromId);
                            (toZones || [])
                                .slice()
                                .filter(z => String(z.zone_id))
                                .sort((a, b) => zoneText(String(b.zone_id)).localeCompare(zoneText(String(a.zone_id))))
                                .forEach(z => {
                                    tbody.appendChild(buildRow(fromStr, String(z.zone_id)));
                                });
                        }

                        function getFromValue() {
                            const el = document.getElementById('from_zone');
                            return el ? el.value : '';
                        }

                        document.addEventListener('change', function (e) {
                            if (e.target && e.target.id === 'from_zone') {
                                renderForFromPort(getFromValue());
                            }
                        });

                        if (window.jQuery) {
                            window.jQuery(document).on('select2:select select2:clear', '#from_zone', function () {
                                renderForFromPort(getFromValue());
                            });
                        }

                        renderForFromPort(getFromValue());
                        setTimeout(function () { renderForFromPort(getFromValue()); }, 0);
                    });
                </script>
            @endif

            @if(request()->get('mapping_type') == 'port_restaurant')
                @php
                    $portRestaurantMappings = collect($mappings ?? [])
                        ->filter(function ($m) {
                            return (($m->from_zone_type ?? 'Port') === 'Port') && (($m->to_zone_type ?? 'Restaurant') === 'Restaurant');
                        })
                        ->map(function ($m) {
                            return [
                                'from' => (string) $m->from_zone_id,
                                'to' => (string) $m->to_zone_id,
                                'private_price' => (float) ($m->private_price ?? 0),
                                'shared_price' => (float) ($m->shared_price ?? 0),
                                'mapping_id' => $m->mapping_id ?? null,
                            ];
                        })
                        ->values();

                    $toRestaurants = collect($zones ?? [])
                        ->filter(fn ($z) => ($z->zone_type ?? null) === 'Restaurant')
                        ->map(fn ($z) => [
                            'zone_id' => (string) $z->zone_id,
                            'zone_name' => (string) ($z->zone_name ?? ''),
                            'zone_type' => (string) ($z->zone_type ?? 'Restaurant'),
                            'description' => (string) ($z->description ?? ''),
                        ])
                        ->values();
                @endphp

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const mappingType = document.querySelector('input[name="mapping_type"]')?.value;
                        if (mappingType !== 'port_restaurant') return;

                        const ports = @json($ports ?? []);
                        const toZones = @json($toRestaurants);
                        const existing = @json($portRestaurantMappings);

                        const portById = new Map((ports || []).map(p => [String(p.port_id), p]));
                        const toById = new Map((toZones || []).map(z => [String(z.zone_id), z]));
                        const existingKeyed = new Map((existing || []).map(m => [`${String(m.from)}__${String(m.to)}`, m]));

                        const tbody = document.getElementById('mappingsTableBody');

                        function escapeHtml(str) {
                            return String(str)
                                .replaceAll('&', '&amp;')
                                .replaceAll('<', '&lt;')
                                .replaceAll('>', '&gt;')
                                .replaceAll('"', '&quot;')
                                .replaceAll("'", '&#039;');
                        }

                        function portText(portId) {
                            const p = portById.get(String(portId));
                            if (!p) return `Port ID: ${portId}`;
                            return `${p.port_name ?? portId} - ${p.type ?? ''}`.trim();
                        }

                        function zoneText(zoneId) {
                            const z = toById.get(String(zoneId));
                            if (!z) return `Zone ID: ${zoneId}`;
                            const name = (z.zone_name || '').trim();
                            return name ? `${name}` : `Zone ID: ${zoneId}`;
                        }

                        function buildRow(fromId, toId) {
                            const key = `${String(fromId)}__${String(toId)}`;
                            const existingMapping = existingKeyed.get(key);

                            const tr = document.createElement('tr');
                            tr.setAttribute('data-from', String(fromId));
                            tr.setAttribute('data-to', String(toId));
                            tr.setAttribute('data-from-type', 'Port');
                            tr.setAttribute('data-to-type', 'Restaurant');
                            if (existingMapping?.mapping_id) tr.setAttribute('data-mapping-id', existingMapping.mapping_id);

                            tr.innerHTML = `
                                <td>${escapeHtml(portText(fromId))}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-warning me-2">Restaurant</span>
                                        <span>${escapeHtml(zoneText(toId))}</span>
                                    </div>
                                </td>
                                <td>
                                    <input type="number"
                                           name="private_prices[${String(fromId)}][${String(toId)}]"
                                           class="form-control"
                                           value="${Number(existingMapping?.private_price ?? 0)}"
                                           step="0.01"
                                           min="0">
                                </td>
                                <td>
                                    <input type="number"
                                           name="shared_prices[${String(fromId)}][${String(toId)}]"
                                           class="form-control"
                                           value="${Number(existingMapping?.shared_price ?? 0)}"
                                           step="0.01"
                                           min="0">
                                </td>
                                <td>
                                    <button type="button"
                                            class="btn btn-sm btn-danger remove-mapping"
                                            ${existingMapping?.mapping_id ? `data-mapping-id="${escapeHtml(String(existingMapping.mapping_id))}"` : ''}>
                                        <i class="ri-delete-bin-line"></i> Remove
                                    </button>
                                </td>
                            `;

                            return tr;
                        }

                        function priceScore(m) {
                            return Math.max(Number(m?.private_price ?? 0), Number(m?.shared_price ?? 0));
                        }

                        function renderExistingRows() {
                            if (!tbody) return;
                            tbody.innerHTML = '';

                            const rows = (existing || [])
                                .filter(m => priceScore(m) > 0)
                                .slice()
                                .sort((a, b) => priceScore(b) - priceScore(a));

                            if (!rows.length) {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `<td colspan="5" class="text-center text-muted py-4">Select a <strong>From Port</strong> to auto-populate all restaurants.</td>`;
                                tbody.appendChild(tr);
                                return;
                            }

                            rows.forEach(m => tbody.appendChild(buildRow(String(m.from), String(m.to))));
                        }

                        function renderForFromPort(fromId) {
                            if (!tbody) return;
                            tbody.innerHTML = '';

                            if (!fromId) {
                                renderExistingRows();
                                return;
                            }

                            const fromStr = String(fromId);
                            (toZones || [])
                                .slice()
                                .filter(z => String(z.zone_id))
                                .sort((a, b) => zoneText(toById, String(b.zone_id)).localeCompare(zoneText(toById, String(a.zone_id))))
                                .forEach(z => tbody.appendChild(buildRow(fromStr, String(z.zone_id))));
                        }

                        function getFromValue() {
                            const el = document.getElementById('from_zone');
                            return el ? el.value : '';
                        }

                        document.addEventListener('change', function (e) {
                            if (e.target && e.target.id === 'from_zone') {
                                renderForFromPort(getFromValue());
                            }
                        });

                        if (window.jQuery) {
                            window.jQuery(document).on('select2:select select2:clear', '#from_zone', function () {
                                renderForFromPort(getFromValue());
                            });
                        }

                        renderForFromPort(getFromValue());
                        setTimeout(function () { renderForFromPort(getFromValue()); }, 0);
                    });
                </script>
            @endif

            @if(request()->get('mapping_type') == 'port_hotel')
                @php
                    $portHotelMappings = collect($mappings ?? [])
                        ->filter(function ($m) {
                            return (($m->from_zone_type ?? 'Port') === 'Port') && (($m->to_zone_type ?? 'Hotel') === 'Hotel');
                        })
                        ->map(function ($m) {
                            return [
                                'from' => (string) $m->from_zone_id,
                                'to' => (string) $m->to_zone_id,
                                'private_price' => (float) ($m->private_price ?? 0),
                                'shared_price' => (float) ($m->shared_price ?? 0),
                                'mapping_id' => $m->mapping_id ?? null,
                            ];
                        })
                        ->values();

                    $toHotels = collect($zones ?? [])
                        ->filter(fn ($z) => ($z->zone_type ?? null) === 'Hotel')
                        ->map(fn ($z) => [
                            'zone_id' => (string) $z->zone_id,
                            'zone_name' => (string) ($z->zone_name ?? ''),
                            'zone_type' => (string) ($z->zone_type ?? 'Hotel'),
                            'description' => (string) ($z->description ?? ''),
                        ])
                        ->values();
                @endphp

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const mappingType = document.querySelector('input[name="mapping_type"]')?.value;
                        if (mappingType !== 'port_hotel') return;

                        const ports = @json($ports ?? []);
                        const toZones = @json($toHotels);
                        const existing = @json($portHotelMappings);

                        const portById = new Map((ports || []).map(p => [String(p.port_id), p]));
                        const toById = new Map((toZones || []).map(z => [String(z.zone_id), z]));
                        const existingKeyed = new Map((existing || []).map(m => [`${String(m.from)}__${String(m.to)}`, m]));

                        const tbody = document.getElementById('mappingsTableBody');

                        function escapeHtml(str) {
                            return String(str)
                                .replaceAll('&', '&amp;')
                                .replaceAll('<', '&lt;')
                                .replaceAll('>', '&gt;')
                                .replaceAll('"', '&quot;')
                                .replaceAll("'", '&#039;');
                        }

                        function portText(portId) {
                            const p = portById.get(String(portId));
                            if (!p) return `Port ID: ${portId}`;
                            return `${p.port_name ?? portId} - ${p.type ?? ''}`.trim();
                        }

                        function zoneText(zoneId) {
                            const z = toById.get(String(zoneId));
                            if (!z) return `Zone ID: ${zoneId}`;
                            const name = (z.zone_name || '').trim();
                            return name ? `${name}` : `Zone ID: ${zoneId}`;
                        }

                        function buildRow(fromId, toId) {
                            const key = `${String(fromId)}__${String(toId)}`;
                            const existingMapping = existingKeyed.get(key);

                            const tr = document.createElement('tr');
                            tr.setAttribute('data-from', String(fromId));
                            tr.setAttribute('data-to', String(toId));
                            tr.setAttribute('data-from-type', 'Port');
                            tr.setAttribute('data-to-type', 'Hotel');
                            if (existingMapping?.mapping_id) tr.setAttribute('data-mapping-id', existingMapping.mapping_id);

                            tr.innerHTML = `
                                <td>${escapeHtml(portText(fromId))}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-success me-2">Hotel</span>
                                        <span>${escapeHtml(zoneText(toId))}</span>
                                    </div>
                                </td>
                                <td>
                                    <input type="number"
                                           name="private_prices[${String(fromId)}][${String(toId)}]"
                                           class="form-control"
                                           value="${Number(existingMapping?.private_price ?? 0)}"
                                           step="0.01"
                                           min="0">
                                </td>
                                <td>
                                    <input type="number"
                                           name="shared_prices[${String(fromId)}][${String(toId)}]"
                                           class="form-control"
                                           value="${Number(existingMapping?.shared_price ?? 0)}"
                                           step="0.01"
                                           min="0">
                                </td>
                                <td>
                                    <button type="button"
                                            class="btn btn-sm btn-danger remove-mapping"
                                            ${existingMapping?.mapping_id ? `data-mapping-id="${escapeHtml(String(existingMapping.mapping_id))}"` : ''}>
                                        <i class="ri-delete-bin-line"></i> Remove
                                    </button>
                                </td>
                            `;

                            return tr;
                        }

                        function priceScore(m) {
                            return Math.max(Number(m?.private_price ?? 0), Number(m?.shared_price ?? 0));
                        }

                        function renderExistingRows() {
                            if (!tbody) return;
                            tbody.innerHTML = '';

                            const rows = (existing || [])
                                .filter(m => priceScore(m) > 0)
                                .slice()
                                .sort((a, b) => priceScore(b) - priceScore(a));

                            if (!rows.length) {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `<td colspan="5" class="text-center text-muted py-4">Select a <strong>From Port</strong> to auto-populate all hotels.</td>`;
                                tbody.appendChild(tr);
                                return;
                            }

                            rows.forEach(m => tbody.appendChild(buildRow(String(m.from), String(m.to))));
                        }

                        function renderForFromPort(fromId) {
                            if (!tbody) return;
                            tbody.innerHTML = '';

                            if (!fromId) {
                                renderExistingRows();
                                return;
                            }

                            const fromStr = String(fromId);
                            (toZones || [])
                                .slice()
                                .filter(z => String(z.zone_id))
                                .sort((a, b) => zoneText(toById, String(b.zone_id)).localeCompare(zoneText(toById, String(a.zone_id))))
                                .forEach(z => tbody.appendChild(buildRow(fromStr, String(z.zone_id))));
                        }

                        function getFromValue() {
                            const el = document.getElementById('from_zone');
                            return el ? el.value : '';
                        }

                        document.addEventListener('change', function (e) {
                            if (e.target && e.target.id === 'from_zone') {
                                renderForFromPort(getFromValue());
                            }
                        });

                        if (window.jQuery) {
                            window.jQuery(document).on('select2:select select2:clear', '#from_zone', function () {
                                renderForFromPort(getFromValue());
                            });
                        }

                        renderForFromPort(getFromValue());
                        setTimeout(function () { renderForFromPort(getFromValue()); }, 0);
                    });
                </script>
            @endif

            @if(request()->get('mapping_type') == 'hotel_attraction')
                @php
                    $hotelAttractionMappings = collect($mappings ?? [])
                        ->filter(function ($m) {
                            return (($m->from_zone_type ?? 'Hotel') === 'Hotel') && (($m->to_zone_type ?? 'Attraction') === 'Attraction');
                        })
                        ->map(function ($m) {
                            return [
                                'from' => (string) $m->from_zone_id,
                                'to' => (string) $m->to_zone_id,
                                'private_price' => (float) ($m->private_price ?? 0),
                                'shared_price' => (float) ($m->shared_price ?? 0),
                                'mapping_id' => $m->mapping_id ?? null,
                            ];
                        })
                        ->values();

                    $fromHotels = collect($zones ?? [])
                        ->filter(fn ($z) => ($z->zone_type ?? null) === 'Hotel')
                        ->map(fn ($z) => [
                            'zone_id' => (string) $z->zone_id,
                            'zone_name' => (string) ($z->zone_name ?? ''),
                            'zone_type' => (string) ($z->zone_type ?? 'Hotel'),
                            'description' => (string) ($z->description ?? ''),
                        ])
                        ->values();

                    $toAttractions = collect($zones ?? [])
                        ->filter(fn ($z) => ($z->zone_type ?? null) === 'Attraction')
                        ->map(fn ($z) => [
                            'zone_id' => (string) $z->zone_id,
                            'zone_name' => (string) ($z->zone_name ?? ''),
                            'zone_type' => (string) ($z->zone_type ?? 'Attraction'),
                            'description' => (string) ($z->description ?? ''),
                        ])
                        ->values();
                @endphp

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const mappingType = document.querySelector('input[name="mapping_type"]')?.value;
                        if (mappingType !== 'hotel_attraction') return;

                        const fromZones = @json($fromHotels);
                        const toZones = @json($toAttractions);
                        const existing = @json($hotelAttractionMappings);

                        const fromById = new Map((fromZones || []).map(z => [String(z.zone_id), z]));
                        const toById = new Map((toZones || []).map(z => [String(z.zone_id), z]));
                        const existingKeyed = new Map((existing || []).map(m => [`${String(m.from)}__${String(m.to)}`, m]));

                        const tbody = document.getElementById('mappingsTableBody');

                        function escapeHtml(str) {
                            return String(str)
                                .replaceAll('&', '&amp;')
                                .replaceAll('<', '&lt;')
                                .replaceAll('>', '&gt;')
                                .replaceAll('"', '&quot;')
                                .replaceAll("'", '&#039;');
                        }

                        function zoneText(map, zoneId) {
                            const z = map.get(String(zoneId));
                            if (!z) return `Zone ID: ${zoneId}`;
                            const name = (z.zone_name || '').trim();
                            return name ? `${name}` : `Zone ID: ${zoneId}`;
                        }

                        function buildRow(fromId, toId) {
                            const key = `${String(fromId)}__${String(toId)}`;
                            const existingMapping = existingKeyed.get(key);

                            const tr = document.createElement('tr');
                            tr.setAttribute('data-from', String(fromId));
                            tr.setAttribute('data-to', String(toId));
                            tr.setAttribute('data-from-type', 'Hotel');
                            tr.setAttribute('data-to-type', 'Attraction');
                            if (existingMapping?.mapping_id) tr.setAttribute('data-mapping-id', existingMapping.mapping_id);

                            tr.innerHTML = `
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-success me-2">Hotel</span>
                                        <span>${escapeHtml(zoneText(fromById, fromId))}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-info me-2">Attraction</span>
                                        <span>${escapeHtml(zoneText(toById, toId))}</span>
                                    </div>
                                </td>
                                <td>
                                    <input type="number"
                                           name="private_prices[${String(fromId)}][${String(toId)}]"
                                           class="form-control"
                                           value="${Number(existingMapping?.private_price ?? 0)}"
                                           step="0.01"
                                           min="0">
                                </td>
                                <td>
                                    <input type="number"
                                           name="shared_prices[${String(fromId)}][${String(toId)}]"
                                           class="form-control"
                                           value="${Number(existingMapping?.shared_price ?? 0)}"
                                           step="0.01"
                                           min="0">
                                </td>
                                <td>
                                    <button type="button"
                                            class="btn btn-sm btn-danger remove-mapping"
                                            ${existingMapping?.mapping_id ? `data-mapping-id="${escapeHtml(String(existingMapping.mapping_id))}"` : ''}>
                                        <i class="ri-delete-bin-line"></i> Remove
                                    </button>
                                </td>
                            `;

                            return tr;
                        }

                        function priceScore(m) {
                            return Math.max(Number(m?.private_price ?? 0), Number(m?.shared_price ?? 0));
                        }

                        function renderExistingRows() {
                            if (!tbody) return;
                            tbody.innerHTML = '';

                            const rows = (existing || [])
                                .filter(m => priceScore(m) > 0)
                                .slice()
                                .sort((a, b) => priceScore(b) - priceScore(a));

                            if (!rows.length) {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `<td colspan="5" class="text-center text-muted py-4">Select a <strong>Hotel</strong> to auto-populate all attractions.</td>`;
                                tbody.appendChild(tr);
                                return;
                            }

                            rows.forEach(m => tbody.appendChild(buildRow(String(m.from), String(m.to))));
                        }

                        function renderForFrom(fromId) {
                            if (!tbody) return;
                            tbody.innerHTML = '';

                            if (!fromId) {
                                renderExistingRows();
                                return;
                            }

                            const fromStr = String(fromId);
                            (toZones || [])
                                .slice()
                                .filter(z => String(z.zone_id))
                                .sort((a, b) => zoneText(toById, String(b.zone_id)).localeCompare(zoneText(toById, String(a.zone_id))))
                                .forEach(z => tbody.appendChild(buildRow(fromStr, String(z.zone_id))));
                        }

                        function getFromValue() {
                            const el = document.getElementById('from_zone');
                            return el ? el.value : '';
                        }

                        document.addEventListener('change', function (e) {
                            if (e.target && e.target.id === 'from_zone') {
                                renderForFrom(getFromValue());
                            }
                        });

                        if (window.jQuery) {
                            window.jQuery(document).on('select2:select select2:clear', '#from_zone', function () {
                                renderForFrom(getFromValue());
                            });
                        }

                        renderForFrom(getFromValue());
                        setTimeout(function () { renderForFrom(getFromValue()); }, 0);
                    });
                </script>
            @endif

            @if(request()->get('mapping_type') == 'hotel_restaurant')
                @php
                    $hotelRestaurantMappings = collect($mappings ?? [])
                        ->filter(function ($m) {
                            return (($m->from_zone_type ?? 'Hotel') === 'Hotel') && (($m->to_zone_type ?? 'Restaurant') === 'Restaurant');
                        })
                        ->map(function ($m) {
                            return [
                                'from' => (string) $m->from_zone_id,
                                'to' => (string) $m->to_zone_id,
                                'private_price' => (float) ($m->private_price ?? 0),
                                'shared_price' => (float) ($m->shared_price ?? 0),
                                'mapping_id' => $m->mapping_id ?? null,
                            ];
                        })
                        ->values();

                    $fromHotels = collect($zones ?? [])
                        ->filter(fn ($z) => ($z->zone_type ?? null) === 'Hotel')
                        ->map(fn ($z) => [
                            'zone_id' => (string) $z->zone_id,
                            'zone_name' => (string) ($z->zone_name ?? ''),
                            'zone_type' => (string) ($z->zone_type ?? 'Hotel'),
                            'description' => (string) ($z->description ?? ''),
                        ])
                        ->values();

                    $toRestaurants = collect($zones ?? [])
                        ->filter(fn ($z) => ($z->zone_type ?? null) === 'Restaurant')
                        ->map(fn ($z) => [
                            'zone_id' => (string) $z->zone_id,
                            'zone_name' => (string) ($z->zone_name ?? ''),
                            'zone_type' => (string) ($z->zone_type ?? 'Restaurant'),
                            'description' => (string) ($z->description ?? ''),
                        ])
                        ->values();
                @endphp

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const mappingType = document.querySelector('input[name="mapping_type"]')?.value;
                        if (mappingType !== 'hotel_restaurant') return;

                        const fromZones = @json($fromHotels);
                        const toZones = @json($toRestaurants);
                        const existing = @json($hotelRestaurantMappings);

                        const fromById = new Map((fromZones || []).map(z => [String(z.zone_id), z]));
                        const toById = new Map((toZones || []).map(z => [String(z.zone_id), z]));
                        const existingKeyed = new Map((existing || []).map(m => [`${String(m.from)}__${String(m.to)}`, m]));

                        const tbody = document.getElementById('mappingsTableBody');

                        function escapeHtml(str) {
                            return String(str)
                                .replaceAll('&', '&amp;')
                                .replaceAll('<', '&lt;')
                                .replaceAll('>', '&gt;')
                                .replaceAll('"', '&quot;')
                                .replaceAll("'", '&#039;');
                        }

                        function zoneText(map, zoneId) {
                            const z = map.get(String(zoneId));
                            if (!z) return `Zone ID: ${zoneId}`;
                            const name = (z.zone_name || '').trim();
                            return name ? `${name}` : `Zone ID: ${zoneId}`;
                        }

                        function buildRow(fromId, toId) {
                            const key = `${String(fromId)}__${String(toId)}`;
                            const existingMapping = existingKeyed.get(key);

                            const tr = document.createElement('tr');
                            tr.setAttribute('data-from', String(fromId));
                            tr.setAttribute('data-to', String(toId));
                            tr.setAttribute('data-from-type', 'Hotel');
                            tr.setAttribute('data-to-type', 'Restaurant');
                            if (existingMapping?.mapping_id) tr.setAttribute('data-mapping-id', existingMapping.mapping_id);

                            tr.innerHTML = `
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-success me-2">Hotel</span>
                                        <span>${escapeHtml(zoneText(fromById, fromId))}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-warning me-2">Restaurant</span>
                                        <span>${escapeHtml(zoneText(toById, toId))}</span>
                                    </div>
                                </td>
                                <td>
                                    <input type="number"
                                           name="private_prices[${String(fromId)}][${String(toId)}]"
                                           class="form-control"
                                           value="${Number(existingMapping?.private_price ?? 0)}"
                                           step="0.01"
                                           min="0">
                                </td>
                                <td>
                                    <input type="number"
                                           name="shared_prices[${String(fromId)}][${String(toId)}]"
                                           class="form-control"
                                           value="${Number(existingMapping?.shared_price ?? 0)}"
                                           step="0.01"
                                           min="0">
                                </td>
                                <td>
                                    <button type="button"
                                            class="btn btn-sm btn-danger remove-mapping"
                                            ${existingMapping?.mapping_id ? `data-mapping-id="${escapeHtml(String(existingMapping.mapping_id))}"` : ''}>
                                        <i class="ri-delete-bin-line"></i> Remove
                                    </button>
                                </td>
                            `;

                            return tr;
                        }

                        function priceScore(m) {
                            return Math.max(Number(m?.private_price ?? 0), Number(m?.shared_price ?? 0));
                        }

                        function renderExistingRows() {
                            if (!tbody) return;
                            tbody.innerHTML = '';

                            const rows = (existing || [])
                                .filter(m => priceScore(m) > 0)
                                .slice()
                                .sort((a, b) => priceScore(b) - priceScore(a));

                            if (!rows.length) {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `<td colspan="5" class="text-center text-muted py-4">Select a <strong>Hotel</strong> to auto-populate all restaurants.</td>`;
                                tbody.appendChild(tr);
                                return;
                            }

                            rows.forEach(m => tbody.appendChild(buildRow(String(m.from), String(m.to))));
                        }

                        function renderForFrom(fromId) {
                            if (!tbody) return;
                            tbody.innerHTML = '';

                            if (!fromId) {
                                renderExistingRows();
                                return;
                            }

                            const fromStr = String(fromId);
                            (toZones || [])
                                .slice()
                                .filter(z => String(z.zone_id))
                                .sort((a, b) => zoneText(toById, String(b.zone_id)).localeCompare(zoneText(toById, String(a.zone_id))))
                                .forEach(z => tbody.appendChild(buildRow(fromStr, String(z.zone_id))));
                        }

                        function getFromValue() {
                            const el = document.getElementById('from_zone');
                            return el ? el.value : '';
                        }

                        document.addEventListener('change', function (e) {
                            if (e.target && e.target.id === 'from_zone') {
                                renderForFrom(getFromValue());
                            }
                        });

                        if (window.jQuery) {
                            window.jQuery(document).on('select2:select select2:clear', '#from_zone', function () {
                                renderForFrom(getFromValue());
                            });
                        }

                        renderForFrom(getFromValue());
                        setTimeout(function () { renderForFrom(getFromValue()); }, 0);
                    });
                </script>
            @endif

            @if(request()->get('mapping_type') == 'attraction_restaurant')
                @php
                    $attractionRestaurantMappings = collect($mappings ?? [])
                        ->filter(function ($m) {
                            return (($m->from_zone_type ?? 'Attraction') === 'Attraction') && (($m->to_zone_type ?? 'Restaurant') === 'Restaurant');
                        })
                        ->map(function ($m) {
                            return [
                                'from' => (string) $m->from_zone_id,
                                'to' => (string) $m->to_zone_id,
                                'private_price' => (float) ($m->private_price ?? 0),
                                'shared_price' => (float) ($m->shared_price ?? 0),
                                'mapping_id' => $m->mapping_id ?? null,
                            ];
                        })
                        ->values();

                    $fromAttractions = collect($zones ?? [])
                        ->filter(fn ($z) => ($z->zone_type ?? null) === 'Attraction')
                        ->map(fn ($z) => [
                            'zone_id' => (string) $z->zone_id,
                            'zone_name' => (string) ($z->zone_name ?? ''),
                            'zone_type' => (string) ($z->zone_type ?? 'Attraction'),
                            'description' => (string) ($z->description ?? ''),
                        ])
                        ->values();

                    $toRestaurants = collect($zones ?? [])
                        ->filter(fn ($z) => ($z->zone_type ?? null) === 'Restaurant')
                        ->map(fn ($z) => [
                            'zone_id' => (string) $z->zone_id,
                            'zone_name' => (string) ($z->zone_name ?? ''),
                            'zone_type' => (string) ($z->zone_type ?? 'Restaurant'),
                            'description' => (string) ($z->description ?? ''),
                        ])
                        ->values();
                @endphp

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const mappingType = document.querySelector('input[name="mapping_type"]')?.value;
                        if (mappingType !== 'attraction_restaurant') return;

                        const fromZones = @json($fromAttractions);
                        const toZones = @json($toRestaurants);
                        const existing = @json($attractionRestaurantMappings);

                        const fromById = new Map((fromZones || []).map(z => [String(z.zone_id), z]));
                        const toById = new Map((toZones || []).map(z => [String(z.zone_id), z]));
                        const existingKeyed = new Map((existing || []).map(m => [`${String(m.from)}__${String(m.to)}`, m]));

                        const tbody = document.getElementById('mappingsTableBody');

                        function escapeHtml(str) {
                            return String(str)
                                .replaceAll('&', '&amp;')
                                .replaceAll('<', '&lt;')
                                .replaceAll('>', '&gt;')
                                .replaceAll('"', '&quot;')
                                .replaceAll("'", '&#039;');
                        }

                        function zoneText(map, zoneId) {
                            const z = map.get(String(zoneId));
                            if (!z) return `Zone ID: ${zoneId}`;
                            const name = (z.zone_name || '').trim();
                            return name ? `${name}` : `Zone ID: ${zoneId}`;
                        }

                        function buildRow(fromId, toId) {
                            const key = `${String(fromId)}__${String(toId)}`;
                            const existingMapping = existingKeyed.get(key);

                            const tr = document.createElement('tr');
                            tr.setAttribute('data-from', String(fromId));
                            tr.setAttribute('data-to', String(toId));
                            tr.setAttribute('data-from-type', 'Attraction');
                            tr.setAttribute('data-to-type', 'Restaurant');
                            if (existingMapping?.mapping_id) tr.setAttribute('data-mapping-id', existingMapping.mapping_id);

                            tr.innerHTML = `
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-info me-2">Attraction</span>
                                        <span>${escapeHtml(zoneText(fromById, fromId))}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-warning me-2">Restaurant</span>
                                        <span>${escapeHtml(zoneText(toById, toId))}</span>
                                    </div>
                                </td>
                                <td>
                                    <input type="number"
                                           name="private_prices[${String(fromId)}][${String(toId)}]"
                                           class="form-control"
                                           value="${Number(existingMapping?.private_price ?? 0)}"
                                           step="0.01"
                                           min="0">
                                </td>
                                <td>
                                    <input type="number"
                                           name="shared_prices[${String(fromId)}][${String(toId)}]"
                                           class="form-control"
                                           value="${Number(existingMapping?.shared_price ?? 0)}"
                                           step="0.01"
                                           min="0">
                                </td>
                                <td>
                                    <button type="button"
                                            class="btn btn-sm btn-danger remove-mapping"
                                            ${existingMapping?.mapping_id ? `data-mapping-id="${escapeHtml(String(existingMapping.mapping_id))}"` : ''}>
                                        <i class="ri-delete-bin-line"></i> Remove
                                    </button>
                                </td>
                            `;

                            return tr;
                        }

                        function priceScore(m) {
                            return Math.max(Number(m?.private_price ?? 0), Number(m?.shared_price ?? 0));
                        }

                        function renderExistingRows() {
                            if (!tbody) return;
                            tbody.innerHTML = '';

                            const rows = (existing || [])
                                .filter(m => priceScore(m) > 0)
                                .slice()
                                .sort((a, b) => priceScore(b) - priceScore(a));

                            if (!rows.length) {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `<td colspan="5" class="text-center text-muted py-4">Select an <strong>Attraction</strong> to auto-populate all restaurants.</td>`;
                                tbody.appendChild(tr);
                                return;
                            }

                            rows.forEach(m => tbody.appendChild(buildRow(String(m.from), String(m.to))));
                        }

                        function renderForFrom(fromId) {
                            if (!tbody) return;
                            tbody.innerHTML = '';

                            if (!fromId) {
                                renderExistingRows();
                                return;
                            }

                            const fromStr = String(fromId);
                            (toZones || [])
                                .slice()
                                .filter(z => String(z.zone_id))
                                .sort((a, b) => zoneText(toById, String(b.zone_id)).localeCompare(zoneText(toById, String(a.zone_id))))
                                .forEach(z => tbody.appendChild(buildRow(fromStr, String(z.zone_id))));
                        }

                        function getFromValue() {
                            const el = document.getElementById('from_zone');
                            return el ? el.value : '';
                        }

                        document.addEventListener('change', function (e) {
                            if (e.target && e.target.id === 'from_zone') {
                                renderForFrom(getFromValue());
                            }
                        });

                        if (window.jQuery) {
                            window.jQuery(document).on('select2:select select2:clear', '#from_zone', function () {
                                renderForFrom(getFromValue());
                            });
                        }

                        renderForFrom(getFromValue());
                        setTimeout(function () { renderForFrom(getFromValue()); }, 0);
                    });
                </script>
            @endif
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
        });
    });
</script>
<script>
    $(document).ready(function() {
        // Driver dropdown (search + select)
        $('#driver').select2({
            placeholder: "Search and Select Driver",
            allowClear: true,
            width: '100%'
        });

        $('#city_name').select2({
            placeholder: "Search and Select a City",
            allowClear: true,
            width: '100%'
        });
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggleVisibility = (checkboxId, fieldId) => {
        document.getElementById(checkboxId).addEventListener('change', function() {
            document.getElementById(fieldId).classList.toggle('d-none', !this.checked);
        });
    };

    toggleVisibility('breakfastToggle', 'breakfastFields');
    toggleVisibility('lunchToggle', 'lunchFields');
    toggleVisibility('dinnerToggle', 'dinnerFields');
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

<!-- delete existing Image -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Use event delegation for dynamically added elements
    document.querySelector('.image-preview-container').addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-image-btn')) {
            e.preventDefault(); // Prevent form submission
            e.stopPropagation(); // Stop event propagation
            const button = e.target;

            // Find the image preview wrapper
            const imageWrapper = button.closest('.image-preview-wrapper');
            if (imageWrapper) {
                // Find and remove the associated hidden input field for the image
                const hiddenInput = imageWrapper.querySelector('input[type="hidden"]');
                if (hiddenInput) {
                    hiddenInput.remove(); // Remove the hidden input
                }

                // Remove the image wrapper (image and button)
                imageWrapper.remove();
            }
        }
    });
});
</script>

{{-- <script>
    $(document).ready(function () {
        // Get user role ID
        var userRoleId = {{ auth()->user()->role_id }};
        var dmcId = '';
        
        // If user is DMC, get DMC ID directly
        if (userRoleId == 11) {
            dmcId = "{{ auth()->user()->userId }}";
            loadDriversForDmc(dmcId);
        }
        
        $('#dmc').change(function () {
            var countryName = $(this).val();
            $('#city_name').html('<option value="">Loading...</option>');

            if (countryName) {
                $.ajax({
                    url: "{{ route('fetch.dmc_cities') }}",
                    type: "GET",
                    data: { country_name: countryName },
                    success: function (response) {
                        $('#city_name').html('<option value="">Select City</option>');
                        $.each(response, function (key, city) {
                            $('#city_name').append('<option value="' + city.name + '">' + city.name + '</option>');
                        });
                        // Set the current city if it exists
                        @if($vehicle->city)
                            $('#city_name').val('{{ $vehicle->city }}');
                        @endif
                    }
                });
                
                // Load drivers when DMC changes (for admin users)
                loadDriversForDmc(countryName);
            } else {
                $('#city_name').html('<option value="">Select a dmc first</option>');
            }
        });
        
        // Function to load drivers based on DMC ID
        function loadDriversForDmc(dmcId) {
            if (dmcId) {
                $('#driver').html('<option value="">Loading drivers...</option>');
                
                $.ajax({
                    url: "{{ route('fetch.dmc_drivers') }}",
                    type: "GET",
                    data: { country_name: dmcId },
                    success: function (response) {
                        $('#driver').html('<option value="">Select Driver</option>');
                        $.each(response, function (key, driver) {
                            var selected = ({{ $vehicle->driver_id ?? 0 }} == driver.driver_id) ? 'selected' : '';
                            $('#driver').append('<option value="' + driver.driver_id + '" ' + selected + '>' + driver.name + '-' + driver.license_no + '</option>');
                        });
                    },
                    error: function() {
                        $('#driver').html('<option value="">Error loading drivers</option>');
                    }
                });
            }
        }

        // Trigger change event on page load to populate cities for the selected DMC (only for admin users)
        if (userRoleId != 11) {
            $('#dmc').trigger('change');
        }
    });
</script> --}}

@php
    $currentUser = auth()->user();
    $userRoleId = $currentUser->role_id;
    $resolvedDmcId = '';

    if ($userRoleId == 11) {
        $resolvedDmcId = $currentUser->userId;
    } elseif ($userRoleId == 35) {
        $resolvedDmcId = \App\Models\User::where('userId', $currentUser->userId)->value('created_by');
    } elseif ($userRoleId == 76 || $userRoleId == 139) {
        $pm = \App\Models\User::where('userId', $currentUser->userId)->first();
        $ph = \App\Models\User::where('userId', $pm?->created_by)->first();
        $resolvedDmcId = $ph?->created_by;
    } elseif ($userRoleId == 111 || $userRoleId == 140) {
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
                        const currentDriverId = {{ $vehicle->driver_id ?? 0 }};
                        $.each(response, function (key, driver) {
                            const id = driver.driver_id;
                            const isSelected = (currentDriverId == id) ? 'selected' : '';
                            $('#driver').append('<option value="' + id + '" ' + isSelected + '>' + driver.name + ' - ' + (driver.license_no ?? '') + '</option>');
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

    // Initialize validation state - to prevent validation messages from showing on page load
    document.addEventListener('DOMContentLoaded', function() {
        const fieldsToValidate = ['model_year', 'vehicle_plate_no', 'seating_capacity'];
        
        fieldsToValidate.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                // Store the original value to detect actual changes
                field.dataset.originalValue = field.value;
                
                // Add a blur event to validate when user leaves the field
                field.addEventListener('blur', function() {
                    // Only validate if the value has changed from original
                    if (this.value !== this.dataset.originalValue) {
                        switch(fieldId) {
                            case 'model_year': 
                                validateModelYear(this);
                                break;
                            case 'vehicle_plate_no':
                                validatePlateNumber(this);
                                break;
                            case 'seating_capacity':
                                validateSeatingCapacity(this);
                                break;
                        }
                    }
                });
            }
        });
    });
</script>

<!-- Add JavaScript for toggle functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sharableSelect = document.getElementById('sharable');
    const sharableFields = document.querySelectorAll('.sharable-field');
    const privateFields = document.querySelectorAll('.private-fields');

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

    function updateFieldVisibility(value) {
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
            privateFields.forEach(field => {
                field.style.display = 'block';
                toggleRequiredAttributes([field], true);
                setTimeout(() => field.classList.add('visible'), 10);
            });
        } else if (value === '2') {
            sharableFields.forEach(field => {
                field.style.display = 'block';
                toggleRequiredAttributes([field], true);
                setTimeout(() => field.classList.add('visible'), 10);
            });
        } else if (value === '3') {
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

    // Get value from backend on initial load
    const initialValue = sharableSelect.value; // Already selected via Blade
    updateFieldVisibility(initialValue);

    // On change
    sharableSelect.addEventListener('change', function() {
        updateFieldVisibility(this.value);
    });
});
</script>

<script>
function initNightChargeAutoPopulate() {
    // Styling (same idea as Guides "Rates" auto-calculated fields)
    if (!document.getElementById('auto-calculated-style')) {
        const style = document.createElement('style');
        style.id = 'auto-calculated-style';
        style.textContent = `
            .auto-calculated {
                background-color: #f8f9fa !important;
                border-left: 3px solid #17a2b8 !important;
                transition: all 0.2s ease;
            }
            .auto-calculated:focus {
                background-color: #fff !important;
                border-left-color: #007bff !important;
            }
            .auto-calculated.value-updated {
                animation: highlightUpdate 0.8s ease-in-out;
            }
            @keyframes highlightUpdate {
                0% { background-color: #e8f7ff; }
                100% { background-color: #f8f9fa; }
            }
        `;
        document.head.appendChild(style);
    }

    // IMPORTANT: edit page contains duplicate IDs `taxi_day_charges` / `taxi_night_charges`
    // for sharable blocks (commented now) and private block. Always target the FIRST ones.
    const dayScope = document.querySelectorAll('#taxi_day_charges')[0] || document;
    const nightScope = document.querySelectorAll('#taxi_night_charges')[0] || document;

    const nightBaseEl = nightScope.querySelector('input[name="night_base_price"]') || document.querySelector('input[name="night_base_price"]');
    const pairs = [
        { day: 'cost_per_km_below_10', night: 'night_cost_per_km_below_10' },
        { day: 'cost_per_km_10_to_25', night: 'night_cost_per_km_10_to_25' },
        { day: 'cost_per_km_above_25', night: 'night_cost_per_km_above_25' },
        { day: 'cost_per_hour', night: 'night_cost_per_hour' },
        { day: 'cancel_cost', night: 'night_cancel_cost' },
    ];

    const getNum = (el) => {
        if (!el) return null;
        const v = (el.value ?? '').toString().trim();
        if (v === '') return null;
        const n = Number.parseFloat(v);
        return Number.isFinite(n) ? n : null;
    };

    const format = (n) => (Math.round(n * 100) / 100).toFixed(2);

    const compute = (dayVal, nightBaseVal) => {
        if (dayVal === null) return null;
        const base = nightBaseVal ?? 0;
        return dayVal + base;
    };

    const initAutoFlags = () => {
        pairs.forEach(({ day, night }) => {
            const nightEl = nightScope.querySelector(`input[name="${night}"]`);
            if (!nightEl) return;

            // Edit screen requirement: always keep night prices synced to (day + night_base)
            // unless the user manually edits a specific night field in this session.
            nightEl.dataset.auto = '1';
            nightEl.classList.add('auto-calculated');
        });
    };

    const recalcNight = () => {
        const nightBaseVal = getNum(nightBaseEl) ?? 0;
        pairs.forEach(({ day, night }) => {
            const dayEl = dayScope.querySelector(`input[name="${day}"]`);
            const nightEl = nightScope.querySelector(`input[name="${night}"]`);
            if (!dayEl || !nightEl) return;
            if ((nightEl.dataset.auto ?? '1') !== '1') return;

            const dayVal = getNum(dayEl);
            const computed = compute(dayVal, nightBaseVal);
            nightEl.value = computed === null ? '' : format(computed);

            // visual feedback for auto updates
            nightEl.classList.add('auto-calculated');
            nightEl.classList.add('value-updated');
            setTimeout(() => nightEl.classList.remove('value-updated'), 800);

            // Do not dispatch synthetic input events here; it can mark fields as "manual"
            // and stop further auto-syncing.
        });
    };

    // Mark a night field as manual when user edits it
    pairs.forEach(({ night }) => {
        const nightEl = nightScope.querySelector(`input[name="${night}"]`);
        if (!nightEl) return;
        nightEl.addEventListener('input', function (e) {
            if (e && e.isTrusted === false) return; // ignore programmatic updates
            this.dataset.auto = '0';
            this.classList.remove('auto-calculated');
        });
    });

    // Recalc when day values or night base change
    const bindRecalc = (name, scopeEl) => {
        const el = (scopeEl || document).querySelector(`input[name="${name}"]`);
        if (!el) return;
        el.addEventListener('input', () => recalcNight());
        el.addEventListener('change', () => recalcNight());
    };

    bindRecalc('night_base_price', nightScope);
    pairs.forEach(({ day }) => bindRecalc(day, dayScope));

    // On edit screen: show DB values first on initial load.
    // Auto-recalculate only after user changes Day charges or Night Base Price.
    initAutoFlags();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNightChargeAutoPopulate);
} else {
    initNightChargeAutoPopulate();
}
</script>


@else
<!-- Zone mapping scripts -->
{{-- <script>
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
            
            // Get selected types
            const fromType = $('#from_zone option:selected').data('type') || 'Port';
            const toType = $('#to_zone option:selected').data('type') || 'Unknown';
            
            // Validate the selection
            if (!fromZone || !toZone) {
                alert('Please select both From and To zones');
                return;
            }
            
            // Only consider same IDs as duplicates if they are of the same type
            if (fromZone === toZone && fromType === toType) {
                alert('From and To zones cannot be the same');
                return;
            }
            
            // Check if mapping already exists
            if ($(`tr[data-from="${fromZone}"][data-to="${toZone}"]`).length > 0) {
                alert('This mapping already exists');
                return;
            }
            
            // Create new row with proper data attributes
            const newRow = `
                <tr data-from="${fromZone}" data-to="${toZone}" data-from-type="${fromType}" data-to-type="${toType}">
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
</script> --}}

<!-- Zone description handling -->
{{-- <script>
$(document).ready(function() {
    // Enhance select2 dropdowns with custom templates to show zone type
    $('#from_zone, #to_zone').select2({
        placeholder: "Search and select a zone",
        allowClear: true,
        width: '100%',
        templateResult: formatZoneOption
    });
    
    // Format the dropdown options to show zone types
    function formatZoneOption(zone) {
        if (!zone.id) return zone.text;
        
        const zoneType = $(zone.element).data('type');
        return $(`<span>
            <span class="badge bg-${getZoneTypeBadgeColor(zoneType)} me-2">${zoneType}</span>
            ${zone.text}
        </span>`);
    }
    
    // Get color for zone type badge
    function getZoneTypeBadgeColor(type) {
        switch(type) {
            case 'Hotel': return 'success';
            case 'Attraction': return 'info';
            case 'Restaurant': return 'warning';
            case 'Port': return 'primary';
            default: return 'secondary';
        }
    }
    
    // Show description when zone is selected
    $('#from_zone').on('change', function() {
        showZoneDescription($(this), 'from_zone');
    });
    
    $('#to_zone').on('change', function() {
        showZoneDescription($(this), 'to_zone');
    });
    
    function showZoneDescription(selectElement, fieldId) {
        const selectedOption = selectElement.find('option:selected');
        const description = selectedOption.data('description') || 'No description available';
        const zoneName = selectedOption.text();
        const zoneType = selectedOption.data('type');
        
        // Update description display
        $(`#${fieldId}_description_text`).html(description);
        $(`#${fieldId}_name_label`).text(zoneName);
        $(`#${fieldId}_type_label`).text(zoneType);
        
        // Show/hide description card
        if (selectedOption.val()) {
            $(`#${fieldId}_description`).removeClass('d-none').addClass('d-block');
        } else {
            $(`#${fieldId}_description`).removeClass('d-block').addClass('d-none');
        }
    }
    
    // Initialize existing selections (if editing)
    showZoneDescription($('#from_zone'), 'from_zone');
    showZoneDescription($('#to_zone'), 'to_zone');
    
    // Add zone mapping to table with description preview in tooltip
    $('#addMapping').click(function() {
        // Get selected values
        const fromZone = $('#from_zone').val();
        const toZone = $('#to_zone').val();
        const fromZoneText = $('#from_zone option:selected').text();
        const toZoneText = $('#to_zone option:selected').text();
        const fromType = $('#from_zone option:selected').data('type') || 'Unknown';
        const toType = $('#to_zone option:selected').data('type') || 'Unknown';
        const fromDescription = $('#from_zone option:selected').data('description') || 'No description available';
        const toDescription = $('#to_zone option:selected').data('description') || 'No description available';
        
        // Validate the selection
        if (!fromZone || !toZone) {
            alert('Please select both From and To zones');
            return;
        }
        
        if (fromZone === toZone && fromType === toType) {
            alert('From and To zones cannot be the same');
            return;
        }
        
        // Check if mapping already exists
        if ($(`tr[data-from="${fromZone}"][data-to="${toZone}"]`).length > 0) {
            alert('This mapping already exists');
            return;
        }
        
        // Create new row with zone badges and tooltips
        const newRow = `
            <tr data-from="${fromZone}" data-to="${toZone}" data-from-type="${fromType}" data-to-type="${toType}">
                <td>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-${getZoneTypeBadgeColor(fromType)} me-2">${fromType}</span>
                        <span data-bs-toggle="tooltip" title="${escapeHtml(fromDescription)}">${fromZoneText}</span>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-${getZoneTypeBadgeColor(toType)} me-2">${toType}</span>
                        <span data-bs-toggle="tooltip" title="${escapeHtml(toDescription)}">${toZoneText}</span>
                    </div>
                </td>
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
        
        // Initialize tooltips on the new row
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        // Reset selection
        $('#from_zone, #to_zone').val('').trigger('change');
    });
    
    // Helper function to escape HTML
    function escapeHtml(str) {
        return str
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    // Add some styling to make the zone descriptions look better
    $('<style>').text(`
        .zone-description .card {
            transition: all 0.3s ease;
            border-left: 4px solid #696cff;
        }
        .zone-description .card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] .badge {
            background-color: white !important;
            color: #696cff !important;
        }
        [data-bs-toggle="tooltip"] {
            cursor: help;
            text-decoration: underline dotted #6c757d;
        }
    `).appendTo('head');
    
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Update existing mappings in the table to use the new UI style
    $('#mappingsTableBody tr').each(function() {
        const fromType = $(this).data('from-type');
        const toType = $(this).data('to-type');
        
        // Update first cell (from zone)
        const fromCell = $(this).find('td:first');
        const fromText = fromCell.text().trim();
        fromCell.html(`
            <div class="d-flex align-items-center">
                <span class="badge bg-${getZoneTypeBadgeColor(fromType)} me-2">${fromType}</span>
                <span>${fromText}</span>
            </div>
        `);
        
        // Update second cell (to zone)
        const toCell = $(this).find('td:eq(1)');
        const toText = toCell.text().trim();
        toCell.html(`
            <div class="d-flex align-items-center">
                <span class="badge bg-${getZoneTypeBadgeColor(toType)} me-2">${toType}</span>
                <span>${toText}</span>
            </div>
        `);
    });
});
</script> --}}

<!-- Zone description handling -->
{{-- <script>
    $(document).ready(function() {
        // Enhance select2 dropdowns with custom templates to show zone type
        $('#from_zone, #to_zone').select2({
            placeholder: "Search and select a zone",
            allowClear: true,
            width: '100%',
            templateResult: formatZoneOption
        });
        
        // Format the dropdown options to show zone types and item names on hover
        function formatZoneOption(zone) {
            if (!zone.id) return zone.text;
            
            const zoneType = $(zone.element).data('type');
            const itemNames = $(zone.element).data('item-names');
            const $span = $(`<span class="zone-option-wrapper">
                <span class="badge bg-${getZoneTypeBadgeColor(zoneType)} me-2">${zoneType}</span>
                ${zone.text}
            </span>`);
            if (itemNames) {
                const label = zoneType === 'Hotel' ? 'Hotels' : (zoneType === 'Attraction' ? 'Attractions' : (zoneType === 'Restaurant' ? 'Restaurants' : 'Items'));
                $span.attr('title', label + ' in this zone: ' + itemNames);
                $span.css('cursor', 'help');
            }
            return $span;
        }
        
        // Get color for zone type badge
        function getZoneTypeBadgeColor(type) {
            switch(type) {
                case 'Hotel': return 'success';
                case 'Attraction': return 'info';
                case 'Restaurant': return 'warning';
                case 'Port': return 'primary';
                default: return 'secondary';
            }
        }
        
        // Show description when zone is selected
        $('#from_zone').on('change', function() {
            showZoneDescription($(this), 'from_zone');
        });
        
        $('#to_zone').on('change', function() {
            showZoneDescription($(this), 'to_zone');
        });
        
        function showZoneDescription(selectElement, fieldId) {
            const selectedOption = selectElement.find('option:selected');
            const description = selectedOption.data('description') || 'No description available';
            const zoneName = selectedOption.text();
            const zoneType = selectedOption.data('type');
            
            // Update description display
            $(`#${fieldId}_description_text`).html(description);
            $(`#${fieldId}_name_label`).text(zoneName);
            $(`#${fieldId}_type_label`).text(zoneType);
            
            // Show/hide description card
            if (selectedOption.val()) {
                $(`#${fieldId}_description`).removeClass('d-none').addClass('d-block');
            } else {
                $(`#${fieldId}_description`).removeClass('d-block').addClass('d-none');
            }
        }
        
        // Initialize existing selections (if editing)
        showZoneDescription($('#from_zone'), 'from_zone');
        showZoneDescription($('#to_zone'), 'to_zone');
        
        // Add zone mapping to table with description preview in tooltip
        $('#addMapping').click(function() {
            // Get selected values
            const fromZone = $('#from_zone').val();
            const toZone = $('#to_zone').val();
            const fromZoneText = $('#from_zone option:selected').text();
            const toZoneText = $('#to_zone option:selected').text();
            const fromType = $('#from_zone option:selected').data('type') || 'Unknown';
            const toType = $('#to_zone option:selected').data('type') || 'Unknown';
            const fromDescription = $('#from_zone option:selected').data('description') || 'No description available';
            const toDescription = $('#to_zone option:selected').data('description') || 'No description available';
            
            // Validate the selection
            if (!fromZone || !toZone) {
                alert('Please select both From and To zones');
                return;
            }
            
            if (fromZone === toZone && fromType === toType) {
                alert('From and To zones cannot be the same');
                return;
            }
            
            // Check if mapping already exists
            if ($(`tr[data-from="${fromZone}"][data-to="${toZone}"]`).length > 0) {
                alert('This mapping already exists');
                return;
            }
            
            // Create new row with zone badges and tooltips
            const newRow = `
                <tr data-from="${fromZone}" data-to="${toZone}" data-from-type="${fromType}" data-to-type="${toType}">
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-${getZoneTypeBadgeColor(fromType)} me-2">${fromType}</span>
                            <span data-bs-toggle="tooltip" title="${escapeHtml(fromDescription)}">${fromZoneText}</span>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-${getZoneTypeBadgeColor(toType)} me-2">${toType}</span>
                            <span data-bs-toggle="tooltip" title="${escapeHtml(toDescription)}">${toZoneText}</span>
                        </div>
                    </td>
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
            
            // Initialize tooltips on the new row
            $('[data-bs-toggle="tooltip"]').tooltip();
            
            // Reset selection
            $('#from_zone, #to_zone').val('').trigger('change');
        });
        
        // FIX: Use event delegation for the remove button since rows can be dynamic
        $(document).on('click', '.remove-mapping', function() {
            // Show confirmation dialog
            if (confirm('Are you sure you want to remove this mapping?')) {
                $(this).closest('tr').remove();
            }
        });
        
        // Helper function to escape HTML
        function escapeHtml(str) {
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
        
        // Add some styling to make the zone descriptions look better
        $('<style>').text(`
            .zone-description .card {
                transition: all 0.3s ease;
                border-left: 4px solid #696cff;
            }
            .zone-description .card:hover {
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            .select2-container--default .select2-results__option--highlighted[aria-selected] .badge {
                background-color: white !important;
                color: #696cff !important;
            }
            [data-bs-toggle="tooltip"] {
                cursor: help;
                text-decoration: underline dotted #6c757d;
            }
            
            /* Add hover effect for remove button */
            .remove-mapping {
                transition: all 0.2s ease;
            }
            .remove-mapping:hover {
                transform: scale(1.05);
                box-shadow: 0 2px 5px rgba(0,0,0,0.15);
            }
            
            /* Add subtle highlight for table rows on hover */
            #mappingsTableBody tr {
                transition: background-color 0.2s ease;
            }
            #mappingsTableBody tr:hover {
                background-color: rgba(105, 108, 255, 0.05);
            }
        `).appendTo('head');
        
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        // Update existing mappings in the table to use the new UI style
        $('#mappingsTableBody tr').each(function() {
            const fromType = $(this).data('from-type');
            const toType = $(this).data('to-type');
            
            // Update first cell (from zone)
            const fromCell = $(this).find('td:first');
            const fromText = fromCell.text().trim();
            fromCell.html(`
                <div class="d-flex align-items-center">
                    <span class="badge bg-${getZoneTypeBadgeColor(fromType)} me-2">${fromType}</span>
                    <span>${fromText}</span>
                </div>
            `);
            
            // Update second cell (to zone)
            const toCell = $(this).find('td:eq(1)');
            const toText = toCell.text().trim();
            toCell.html(`
                <div class="d-flex align-items-center">
                    <span class="badge bg-${getZoneTypeBadgeColor(toType)} me-2">${toType}</span>
                    <span>${toText}</span>
                </div>
            `);
        });
    });
</script> --}}

<!-- Zone mapping scripts with AJAX delete -->
{{-- <script>
    $(document).ready(function() {
        // Enhance select2 dropdowns with custom templates to show zone type
        $('#from_zone, #to_zone').select2({
            placeholder: "Search and select a zone",
            allowClear: true,
            width: '100%',
            templateResult: formatZoneOption
        });
        
        // Format the dropdown options to show zone types and item names on hover
        function formatZoneOption(zone) {
            if (!zone.id) return zone.text;
            
            const zoneType = $(zone.element).data('type');
            const itemNames = $(zone.element).data('item-names');
            const $span = $(`<span class="zone-option-wrapper">
                <span class="badge bg-${getZoneTypeBadgeColor(zoneType)} me-2">${zoneType}</span>
                ${zone.text}
            </span>`);
            if (itemNames) {
                const label = zoneType === 'Hotel' ? 'Hotels' : (zoneType === 'Attraction' ? 'Attractions' : (zoneType === 'Restaurant' ? 'Restaurants' : 'Items'));
                $span.attr('title', label + ' in this zone: ' + itemNames);
                $span.css('cursor', 'help');
            }
            return $span;
        }
        
        // Get color for zone type badge
        function getZoneTypeBadgeColor(type) {
            switch(type) {
                case 'Hotel': return 'success';
                case 'Attraction': return 'info';
                case 'Restaurant': return 'warning';
                case 'Port': return 'primary';
                default: return 'secondary';
            }
        }
        
        // Show description when zone is selected
        $('#from_zone').on('change', function() {
            showZoneDescription($(this), 'from_zone');
        });
        
        $('#to_zone').on('change', function() {
            showZoneDescription($(this), 'to_zone');
        });
        
        function showZoneDescription(selectElement, fieldId) {
            const selectedOption = selectElement.find('option:selected');
            const description = selectedOption.data('description') || 'No description available';
            const zoneName = selectedOption.text();
            const zoneType = selectedOption.data('type');
            
            // Update description display
            $(`#${fieldId}_description_text`).html(description);
            $(`#${fieldId}_name_label`).text(zoneName);
            $(`#${fieldId}_type_label`).text(zoneType);
            
            // Show/hide description card
            if (selectedOption.val()) {
                $(`#${fieldId}_description`).removeClass('d-none').addClass('d-block');
            } else {
                $(`#${fieldId}_description`).removeClass('d-block').addClass('d-none');
            }
        }
        
        // Initialize existing selections (if editing)
        showZoneDescription($('#from_zone'), 'from_zone');
        showZoneDescription($('#to_zone'), 'to_zone');
        
        // Add zone mapping to table with description preview in tooltip
        $('#addMapping').click(function() {
            // Get selected values
            const fromZone = $('#from_zone').val();
            const toZone = $('#to_zone').val();
            const fromZoneText = $('#from_zone option:selected').text();
            const toZoneText = $('#to_zone option:selected').text();
            const fromType = $('#from_zone option:selected').data('type') || 'Port'; // Default to Port for port mappings
            const toType = $('#to_zone option:selected').data('type') || 'Port'; // Default to Port for port mappings
            const fromDescription = $('#from_zone option:selected').data('description') || 'No description available';
            const toDescription = $('#to_zone option:selected').data('description') || 'No description available';
            let fromZoneItems = $('#from_zone option:selected').data('item-images') || [];
            let toZoneItems = $('#to_zone option:selected').data('item-images') || [];
            if (typeof fromZoneItems === 'string') try { fromZoneItems = JSON.parse(fromZoneItems); } catch(e) { fromZoneItems = []; }
            if (typeof toZoneItems === 'string') try { toZoneItems = JSON.parse(toZoneItems); } catch(e) { toZoneItems = []; }
            const vehicleId = $('input[name="vehicle_id"]').val();
            const mappingType = $('input[name="mapping_type"]').val();
            
            // Validate the selection
            if (!fromZone || !toZone) {
                alert('Please select both From and To zones');
                return;
            }
            
            if (fromZone === toZone && fromType === toType) {
                alert('From and To zones cannot be the same');
                return;
            }
            
            // Check if mapping already exists in UI
            if ($(`tr[data-from="${fromZone}"][data-to="${toZone}"]`).length > 0) {
                alert('This mapping already exists in the table');
                return;
            }
            
            // Show loading indicator
            const loadingHtml = '<div class="d-flex justify-content-center my-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            $('#mappingsTableBody').append(loadingHtml);
                    
                    // Create new mapping entry in database via AJAX
                    $.ajax({
                        url: "{{ route('vehicle.add_mapping') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            vehicle_id: vehicleId,
                            from_zone_id: fromZone,
                            to_zone_id: toZone,
                            from_zone_type: fromType,
                    to_zone_type: toType,
                    mapping_type: mappingType
                },
                success: function(response) {
                    // Remove loading indicator
                    $('#mappingsTableBody .d-flex.justify-content-center').remove();
                    
                    if (response.success) {
                            // Add the row to the table with the returned mapping ID
                            addMappingRowToTable(
                                fromZone, toZone, fromZoneText, toZoneText, 
                                fromType, toType, fromDescription, toDescription,
                            0, 0, response.mapping_id, fromZoneItems, toZoneItems
                            );
                            showSuccessToast("Mapping added successfully");
                    } else {
                        showErrorToast(response.message || "Error adding mapping");
                        }
                },
                error: function(xhr) {
                    // Remove loading indicator
                    $('#mappingsTableBody .d-flex.justify-content-center').remove();
                    showErrorToast("Error adding mapping: " + xhr.responseText);
                }
            });
            
            // Reset selection
            $('#from_zone, #to_zone').val('').trigger('change');
        });
        
        function addMappingRowToTable(fromZone, toZone, fromZoneText, toZoneText, fromType, toType, fromDescription, toDescription, privatePrice, sharedPrice, mappingId, fromZoneItems, toZoneItems) {
            fromZoneItems = fromZoneItems || [];
            toZoneItems = toZoneItems || [];
            const fromItemsAttr = (['Hotel','Attraction','Restaurant'].includes(fromType)) ? ' data-zone-items="' + escapeHtml(JSON.stringify(fromZoneItems || [])) + '" data-zone-type="' + fromType + '"' : '';
            const toItemsAttr = (['Hotel','Attraction','Restaurant'].includes(toType)) ? ' data-zone-items="' + escapeHtml(JSON.stringify(toZoneItems || [])) + '" data-zone-type="' + toType + '"' : '';
            const fromSpan = fromItemsAttr ? '<span class="zone-cell-hover"' + fromItemsAttr + '>' + fromZoneText + '</span>' : '<span data-bs-toggle="tooltip" title="' + escapeHtml(fromDescription) + '">' + fromZoneText + '</span>';
            const toSpan = toItemsAttr ? '<span class="zone-cell-hover"' + toItemsAttr + '>' + toZoneText + '</span>' : '<span data-bs-toggle="tooltip" title="' + escapeHtml(toDescription) + '">' + toZoneText + '</span>';
            const existingById = mappingId ? $('#mappingsTableBody').find('tr[data-mapping-id="' + String(mappingId) + '"]') : $();
            const existingByPair = $('#mappingsTableBody').find('tr[data-from="' + String(fromZone) + '"][data-to="' + String(toZone) + '"][data-from-type="' + String(fromType) + '"][data-to-type="' + String(toType) + '"]');
            const newRow = `
                <tr data-from="${fromZone}" data-to="${toZone}" data-from-type="${fromType}" data-to-type="${toType}" data-mapping-id="${mappingId}">
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-${getZoneTypeBadgeColor(fromType)} me-2">${fromType}</span>
                            ${fromSpan}
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-${getZoneTypeBadgeColor(toType)} me-2">${toType}</span>
                            ${toSpan}
                        </div>
                    </td>
                    <td>
                        <input type="number" name="private_prices[${fromZone}][${toZone}]" 
                            class="form-control" value="${privatePrice}" step="0.01" min="0">
                    </td>
                    <td>
                        <input type="number" name="shared_prices[${fromZone}][${toZone}]" 
                            class="form-control" value="${sharedPrice}" step="0.01" min="0">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-mapping" data-mapping-id="${mappingId}">
                            <i class="ri-delete-bin-line"></i> Remove
                        </button>
                    </td>
                </tr>
            `;
            
            // Replace existing row (avoid duplicate mapping_id / pair)
            if (existingById.length) {
                existingById.first().replaceWith(newRow);
            } else if (existingByPair.length) {
                existingByPair.first().replaceWith(newRow);
            } else {
                $('#mappingsTableBody').append(newRow);
            }
            
            // Initialize tooltips on the new row
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
        
        // Delete mapping from database and remove from UI 
        $(document).on('click', '.remove-mapping', function() {
            const mappingId = $(this).data('mapping-id');
            const row = $(this).closest('tr');
            
            // If no mapping ID (for new unmapped rows) just remove from UI
            if (!mappingId) {
                row.remove();
                return;
            }
            
            // Show confirmation dialog
            if (confirm('Are you sure you want to remove this mapping? This will soft delete it from the database.')) {
                // Show loading state
                $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Removing...').prop('disabled', true);
                
                // Make AJAX request to delete the mapping
                $.ajax({
                    url: "{{ route('vehicle.delete_mapping') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        mapping_id: mappingId
                    },
                    success: function(response) {
                        // Remove the row from the table
                        row.fadeOut(300, function() {
                            $(this).remove();
                        });
                        showSuccessToast("Mapping removed successfully");
                    },
                    error: function(xhr) {
                        // Restore button state
                        $(this).html('<i class="ri-delete-bin-line"></i> Remove').prop('disabled', false);
                        showErrorToast("Error removing mapping: " + xhr.responseText);
                    }
                });
            }
        });
        
        // Helper function to show success toast
        function showSuccessToast(message) {
            const toast = `
                <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
                    <div class="toast show bg-success text-white" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header bg-success text-white">
                            <strong class="me-auto">Success</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            ${message}
                        </div>
                    </div>
                </div>
            `;
            const toastElement = $(toast);
            $('body').append(toastElement);
            setTimeout(function() {
                toastElement.remove();
            }, 3000);
        }
        
        // Helper function to show error toast
        function showErrorToast(message) {
            const toast = `
                <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
                    <div class="toast show bg-danger text-white" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header bg-danger text-white">
                            <strong class="me-auto">Error</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            ${message}
                        </div>
                    </div>
                </div>
            `;
            const toastElement = $(toast);
            $('body').append(toastElement);
            setTimeout(function() {
                toastElement.remove();
            }, 5000);
        }
        
        // Helper function to escape HTML
        function escapeHtml(str) {
            if (!str) return '';
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
        
        // Add some styling to make the zone descriptions look better
        $('<style>').text(`
            .zone-description .card {
                transition: all 0.3s ease;
                border-left: 4px solid #696cff;
            }
            .zone-description .card:hover {
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            .select2-container--default .select2-results__option--highlighted[aria-selected] .badge {
                background-color: white !important;
                color: #696cff !important;
            }
            [data-bs-toggle="tooltip"] {
                cursor: help;
                text-decoration: underline dotted #6c757d;
            }
            
            /* Add hover effect for remove button */
            .remove-mapping {
                transition: all 0.2s ease;
            }
            .remove-mapping:hover {
                transform: scale(1.05);
                box-shadow: 0 2px 5px rgba(0,0,0,0.15);
            }
            
            /* Add subtle highlight for table rows on hover */
            #mappingsTableBody tr {
                transition: background-color 0.2s ease;
            }
            #mappingsTableBody tr:hover {
                background-color: rgba(105, 108, 255, 0.05);
            }
            
            /* Toast styling */
            .toast {
                border-radius: 8px;
                box-shadow: 0 8px 20px rgba(0,0,0,0.2);
                transition: all 0.3s ease;
                border: none;
                overflow: hidden;
            }
            .toast-header {
                padding: 0.75rem 1rem;
                border-bottom: none;
            }
            .toast-body {
                padding: 1rem;
                font-weight: 500;
            }
            .btn-close {
                font-size: 0.75rem;
                opacity: 0.8;
            }
            .btn-close:hover {
                opacity: 1;
            }

            /* Add animations */
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes fadeOut {
                from { opacity: 1; transform: translateY(0); }
                to { opacity: 0; transform: translateY(-20px); }
            }
            .animate__animated {
                animation-duration: 0.5s;
            }
            .animate__fadeIn {
                animation-name: fadeIn;
            }
            .animate__fadeOut {
                animation-name: fadeOut;
            }
        `).appendTo('head');
        
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        // Update existing mappings - preserve zone-cell-hover (server-rendered), only fix delete button
        $('#mappingsTableBody tr').each(function() {
            const mappingId = $(this).data('mapping-id');
            const deleteButton = $(this).find('.remove-mapping');
            if (deleteButton.length && !deleteButton.data('mapping-id')) {
                deleteButton.attr('data-mapping-id', mappingId);
            }
        });
        
        // Zone hover tooltip - show items with images on hover
        const $tooltip = $('#zoneHoverTooltip');
        const defaultImg = 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40"><rect fill="#e9ecef" width="40" height="40"/><text x="50%" y="50%" fill="#adb5bd" text-anchor="middle" dy=".3em" font-size="10">No img</text></svg>');
        $(document).on('mouseenter', '.zone-cell-hover', function(e) {
            let items = $(this).attr('data-zone-items');
            try { items = items ? JSON.parse(items) : []; } catch(x) { items = []; }
            const zoneType = $(this).attr('data-zone-type') || 'Item';
            const label = zoneType === 'Hotel' ? 'Hotels' : (zoneType === 'Attraction' ? 'Attractions' : (zoneType === 'Restaurant' ? 'Restaurants' : 'Items'));
            let html = '<div class="tooltip-title">' + label + ' in this zone</div>';
            if (!items || !items.length) {
                html += '<div class="tooltip-item"><span class="tooltip-item-name text-muted">No ' + label.toLowerCase() + ' assigned</span></div>';
            } else {
                items.forEach(function(item) {
                    const imgSrc = (item.image && (item.image.startsWith('http') || item.image.startsWith('/'))) ? item.image : (item.image ? '{{ url("/") }}/' + item.image.replace(/^\/+/, '') : defaultImg);
                    html += '<div class="tooltip-item"><img class="tooltip-item-img" src="' + imgSrc + '" alt=""><span class="tooltip-item-name">' + escapeHtml(item.name || '') + '</span></div>';
                });
            }
            $tooltip.html(html).addClass('show');
            const rect = this.getBoundingClientRect();
            $tooltip.css({ left: rect.left + (rect.width/2) - 160, top: rect.bottom + 8 });
        });
        $(document).on('mouseleave', '.zone-cell-hover', function() {
            $tooltip.removeClass('show');
        });
    });
</script> --}}

<script>
    $(document).ready(function() {
        // Enhance select2 dropdowns with custom templates to show zone type
        $('#from_zone, #to_zone').select2({
            placeholder: "Search and select a zone",
            allowClear: true,
            width: '100%',
            templateResult: formatZoneOption
        });
        
        // Format the dropdown options to show zone types and item names on hover
        function formatZoneOption(zone) {
            if (!zone.id) return zone.text;
            
            const zoneType = $(zone.element).data('type');
            const itemNames = $(zone.element).data('item-names');
            const $span = $(`<span class="zone-option-wrapper">
                <span class="badge bg-${getZoneTypeBadgeColor(zoneType)} me-2">${zoneType}</span>
                ${zone.text}
            </span>`);
            if (itemNames) {
                const label = zoneType === 'Hotel' ? 'Hotels' : (zoneType === 'Attraction' ? 'Attractions' : (zoneType === 'Restaurant' ? 'Restaurants' : 'Items'));
                $span.attr('title', label + ' in this zone: ' + itemNames);
                $span.css('cursor', 'help');
            }
            return $span;
        }
        
        // Get color for zone type badge
        function getZoneTypeBadgeColor(type) {
            switch(type) {
                case 'Hotel': return 'success';
                case 'Attraction': return 'info';
                case 'Restaurant': return 'warning';
                case 'Port': return 'primary';
                default: return 'secondary';
            }
        }
        
        // Show description when zone is selected
        $('#from_zone').on('change', function() {
            showZoneDescription($(this), 'from_zone');
        });
        
        $('#to_zone').on('change', function() {
            showZoneDescription($(this), 'to_zone');
        });
        
        function showZoneDescription(selectElement, fieldId) {
            const selectedOption = selectElement.find('option:selected');
            const description = selectedOption.data('description') || 'No description available';
            const zoneName = selectedOption.text();
            const zoneType = selectedOption.data('type');
            
            // Update description display
            $(`#${fieldId}_description_text`).html(description);
            $(`#${fieldId}_name_label`).text(zoneName);
            $(`#${fieldId}_type_label`).text(zoneType);
            
            // Show/hide description card
            if (selectedOption.val()) {
                $(`#${fieldId}_description`).removeClass('d-none').addClass('d-block');
            } else {
                $(`#${fieldId}_description`).removeClass('d-block').addClass('d-none');
            }
        }
        
        // Initialize existing selections (if editing)
        showZoneDescription($('#from_zone'), 'from_zone');
        showZoneDescription($('#to_zone'), 'to_zone');
        
        // Add zone mapping to table with description preview in tooltip
        $('#addMapping').click(function() {
            // Get selected values
            const fromZone = $('#from_zone').val();
            const toZone = $('#to_zone').val();
            const fromZoneText = $('#from_zone option:selected').text();
            const toZoneText = $('#to_zone option:selected').text();
            const fromType = $('#from_zone option:selected').data('type') || 'Unknown';
            const toType = $('#to_zone option:selected').data('type') || 'Unknown';
            const fromDescription = $('#from_zone option:selected').data('description') || 'No description available';
            const toDescription = $('#to_zone option:selected').data('description') || 'No description available';
            let fromZoneItems = $('#from_zone option:selected').data('item-images') || [];
            let toZoneItems = $('#to_zone option:selected').data('item-images') || [];
            if (typeof fromZoneItems === 'string') try { fromZoneItems = JSON.parse(fromZoneItems); } catch(e) { fromZoneItems = []; }
            if (typeof toZoneItems === 'string') try { toZoneItems = JSON.parse(toZoneItems); } catch(e) { toZoneItems = []; }
            const vehicleId = $('input[name="vehicle_id"]').val();

            function showToast(message, type = 'danger') {
                const toastContainer = document.getElementById('customToastContainer') || createToastContainer();
                const toastId = 'toast-' + Date.now();

                toastContainer.innerHTML += `
                    <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0 mb-2 shadow" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                        <div class="d-flex">
                            <div class="toast-body">
                                ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;

                const toastEl = new bootstrap.Toast(document.getElementById(toastId));
                toastEl.show();
            }

            function createToastContainer() {
                const container = document.createElement('div');
                container.id = 'customToastContainer';
                container.className = 'toast-container position-fixed p-3 z-1055';
                container.style.top = '30%';
                container.style.left = '50%';
                container.style.transform = 'translate(-30%, -20%)';
                document.body.appendChild(container);
                return container;
            }
            
            // Validate the selection
            if (!fromZone || !toZone) {
                showToast('Please select both From and To zones', 'warning');
                return;
            }

            if (fromZone === toZone && fromType === toType) {
                showToast('From and To zones cannot be the same', 'danger');
                return;
            }

            if ($(`tr[data-from="${fromZone}"][data-to="${toZone}"]`).length > 0) {
                showToast('This mapping already exists in the table', 'info');
                return;
            }

            
            // Show loading indicator
            const loadingHtml = '<div class="d-flex justify-content-center my-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            $('#mappingsTableBody').append(loadingHtml);
            
            // First check if this mapping exists (including soft deleted records)
            $.ajax({
                url: "{{ route('vehicle.check_mapping_exists') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    vehicle_id: vehicleId,
                    from_zone_id: fromZone,
                    to_zone_id: toZone,
                    from_zone_type: fromType,
                    to_zone_type: toType
                },
                success: function(response) {
                    // Remove loading indicator
                    $('#mappingsTableBody .d-flex.justify-content-center').remove();
                    
                    if (response.exists) {
                        // If exists but was soft deleted, restore it
                        if (response.was_deleted) {
                            $.ajax({
                                url: "{{ route('vehicle.restore_mapping') }}",
                                type: "POST",
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    mapping_id: response.mapping_id
                                },
                                success: function(restoreResponse) {
                                    addMappingRowToTable(
                                        fromZone, toZone, fromZoneText, toZoneText, 
                                        fromType, toType, fromDescription, toDescription,
                                        restoreResponse.private_price, restoreResponse.shared_price,
                                        restoreResponse.mapping_id, fromZoneItems, toZoneItems
                                    );
                                    showSuccessToast("Mapping restored successfully");
                                },
                                error: function(xhr) {
                                    showErrorToast("Error restoring mapping: " + xhr.responseText);
                                }
                            });
                        } else {
                            alert('This mapping already exists in the database');
                        }
                        return;
                    }
                    
                    // Create new mapping entry in database via AJAX
                    $.ajax({
                        url: "{{ route('vehicle.add_mapping') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            vehicle_id: vehicleId,
                            from_zone_id: fromZone,
                            to_zone_id: toZone,
                            from_zone_type: fromType,
                            to_zone_type: toType
                        },
                        success: function(addResponse) {
                            // Add the row to the table with the returned mapping ID
                            addMappingRowToTable(
                                fromZone, toZone, fromZoneText, toZoneText, 
                                fromType, toType, fromDescription, toDescription,
                                0, 0, addResponse.mapping_id, fromZoneItems, toZoneItems
                            );
                            showSuccessToast("Mapping added successfully");
                        },
                        error: function(xhr) {
                            showErrorToast("Error adding mapping: " + xhr.responseText);
                        }
                    });
                },
                error: function(xhr) {
                    // Remove loading indicator
                    $('#mappingsTableBody .d-flex.justify-content-center').remove();
                    showErrorToast("Error checking mapping: " + xhr.responseText);
                }
            });
            
            // Reset selection
            $('#from_zone, #to_zone').val('').trigger('change');
        });
        
        function addMappingRowToTable(fromZone, toZone, fromZoneText, toZoneText, fromType, toType, fromDescription, toDescription, privatePrice, sharedPrice, mappingId, fromZoneItems, toZoneItems) {
            fromZoneItems = fromZoneItems || [];
            toZoneItems = toZoneItems || [];
            const fromItemsAttr = (['Hotel','Attraction','Restaurant'].includes(fromType)) ? ' data-zone-items="' + escapeHtml(JSON.stringify(fromZoneItems || [])) + '" data-zone-type="' + fromType + '"' : '';
            const toItemsAttr = (['Hotel','Attraction','Restaurant'].includes(toType)) ? ' data-zone-items="' + escapeHtml(JSON.stringify(toZoneItems || [])) + '" data-zone-type="' + toType + '"' : '';
            const fromSpan = fromItemsAttr ? '<span class="zone-cell-hover"' + fromItemsAttr + '>' + fromZoneText + '</span>' : '<span data-bs-toggle="tooltip" title="' + escapeHtml(fromDescription) + '">' + fromZoneText + '</span>';
            const toSpan = toItemsAttr ? '<span class="zone-cell-hover"' + toItemsAttr + '>' + toZoneText + '</span>' : '<span data-bs-toggle="tooltip" title="' + escapeHtml(toDescription) + '">' + toZoneText + '</span>';
            const existingById = mappingId ? $('#mappingsTableBody').find('tr[data-mapping-id="' + String(mappingId) + '"]') : $();
            const existingByPair = $('#mappingsTableBody').find('tr[data-from="' + String(fromZone) + '"][data-to="' + String(toZone) + '"][data-from-type="' + String(fromType) + '"][data-to-type="' + String(toType) + '"]');
            const newRow = `
                <tr data-from="${fromZone}" data-to="${toZone}" data-from-type="${fromType}" data-to-type="${toType}" data-mapping-id="${mappingId}">
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-${getZoneTypeBadgeColor(fromType)} me-2">${fromType}</span>
                            ${fromSpan}
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-${getZoneTypeBadgeColor(toType)} me-2">${toType}</span>
                            ${toSpan}
                        </div>
                    </td>
                    <td>
                        <input type="number" name="private_prices[${fromZone}][${toZone}]" 
                            class="form-control" value="${privatePrice}" step="0.01" min="0">
                    </td>
                    <td>
                        <input type="number" name="shared_prices[${fromZone}][${toZone}]" 
                            class="form-control" value="${sharedPrice}" step="0.01" min="0">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-mapping" data-mapping-id="${mappingId}">
                            <i class="ri-delete-bin-line"></i> Remove
                        </button>
                    </td>
                </tr>
            `;
            
            // Replace existing row (avoid duplicate mapping_id / pair)
            if (existingById.length) {
                existingById.first().replaceWith(newRow);
            } else if (existingByPair.length) {
                existingByPair.first().replaceWith(newRow);
            } else {
                $('#mappingsTableBody').append(newRow);
            }
            
            // Initialize tooltips on the new row
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
        
        // Delete mapping from database and remove from UI 
        $(document).on('click', '.remove-mapping', function() {
            const mappingId = $(this).data('mapping-id');
            const row = $(this).closest('tr');
            
            // If no mapping ID (for new unmapped rows) just remove from UI
            if (!mappingId) {
                row.remove();
                return;
            }
            
            // Show confirmation dialog
            if (confirm('Are you sure you want to remove this mapping? This will soft delete it from the database.')) {
                // Show loading state
                $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Removing...').prop('disabled', true);
                
                // Make AJAX request to delete the mapping
                $.ajax({
                    url: "{{ route('vehicle.delete_mapping') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        mapping_id: mappingId
                    },
                    success: function(response) {
                        // Remove the row from the table
                        row.fadeOut(300, function() {
                            $(this).remove();
                        });
                        showSuccessToast("Mapping removed successfully");
                    },
                    error: function(xhr) {
                        // Restore button state
                        $(this).html('<i class="ri-delete-bin-line"></i> Remove').prop('disabled', false);
                        showErrorToast("Error removing mapping: " + xhr.responseText);
                    }
                });
            }
        });
        
        // Helper function to show success toast
        function showSuccessToast(message) {
            const toast = `
                <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
                    <div class="toast show bg-success text-white" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header bg-success text-white">
                            <strong class="me-auto">Success</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            ${message}
                        </div>
                    </div>
                </div>
            `;
            const toastElement = $(toast);
            $('body').append(toastElement);
            setTimeout(function() {
                toastElement.remove();
            }, 3000);
        }
        
        // Helper function to show error toast
        function showErrorToast(message) {
            const toast = `
                <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
                    <div class="toast show bg-danger text-white" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header bg-danger text-white">
                            <strong class="me-auto">Error</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            ${message}
                        </div>
                    </div>
                </div>
            `;
            const toastElement = $(toast);
            $('body').append(toastElement);
            setTimeout(function() {
                toastElement.remove();
            }, 5000);
        }
        
        // Helper function to escape HTML
        function escapeHtml(str) {
            if (!str) return '';
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
        
        // Add some styling to make the zone descriptions look better
        $('<style>').text(`
            .zone-description .card {
                transition: all 0.3s ease;
                border-left: 4px solid #696cff;
            }
            .zone-description .card:hover {
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            .select2-container--default .select2-results__option--highlighted[aria-selected] .badge {
                background-color: white !important;
                color: #696cff !important;
            }
            [data-bs-toggle="tooltip"] {
                cursor: help;
                text-decoration: underline dotted #6c757d;
            }
            
            /* Add hover effect for remove button */
            .remove-mapping {
                transition: all 0.2s ease;
            }
            .remove-mapping:hover {
                transform: scale(1.05);
                box-shadow: 0 2px 5px rgba(0,0,0,0.15);
            }
            
            /* Add subtle highlight for table rows on hover */
            #mappingsTableBody tr {
                transition: background-color 0.2s ease;
            }
            #mappingsTableBody tr:hover {
                background-color: rgba(105, 108, 255, 0.05);
            }
            
            /* Toast styling */
            .toast {
                border-radius: 8px;
                box-shadow: 0 8px 20px rgba(0,0,0,0.2);
                transition: all 0.3s ease;
                border: none;
                overflow: hidden;
            }
            .toast-header {
                padding: 0.75rem 1rem;
                border-bottom: none;
            }
            .toast-body {
                padding: 1rem;
                font-weight: 500;
            }
            .btn-close {
                font-size: 0.75rem;
                opacity: 0.8;
            }
            .btn-close:hover {
                opacity: 1;
            }

            /* Add animations */
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes fadeOut {
                from { opacity: 1; transform: translateY(0); }
                to { opacity: 0; transform: translateY(-20px); }
            }
            .animate__animated {
                animation-duration: 0.5s;
            }
            .animate__fadeIn {
                animation-name: fadeIn;
            }
            .animate__fadeOut {
                animation-name: fadeOut;
            }
        `).appendTo('head');
        
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        // Update existing mappings - preserve zone-cell-hover (server-rendered), only fix delete button
        $('#mappingsTableBody tr').each(function() {
            const mappingId = $(this).data('mapping-id');
            const deleteButton = $(this).find('.remove-mapping');
            if (deleteButton.length && !deleteButton.data('mapping-id')) {
                deleteButton.attr('data-mapping-id', mappingId);
            }
        });
        
        // Zone hover tooltip - show items with images, stay visible when hovering tooltip (scrollable)
        if (!$('#zoneHoverTooltip').length) $('body').append('<div id="zoneHoverTooltip" class="zone-hover-tooltip"></div>');
        const $zoneTooltip = $('#zoneHoverTooltip');
        const defaultImg = 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40"><rect fill="#e9ecef" width="40" height="40"/><text x="50%" y="50%" fill="#adb5bd" text-anchor="middle" dy=".3em" font-size="10">No img</text></svg>');
        let hideTid = null;
        function scheduleHide() {
            if (hideTid) clearTimeout(hideTid);
            hideTid = setTimeout(function() { $zoneTooltip.removeClass('show'); hideTid = null; }, 300);
        }
        function cancelHide() { if (hideTid) { clearTimeout(hideTid); hideTid = null; } }
        $zoneTooltip.on('mouseenter', cancelHide).on('mouseleave', scheduleHide);
        $(document).on('mouseenter', '.zone-cell-hover', function(e) {
            cancelHide();
            let items = $(this).attr('data-zone-items');
            try { items = items ? JSON.parse(items) : []; } catch(x) { items = []; }
            const zoneType = $(this).attr('data-zone-type') || 'Item';
            const label = zoneType === 'Hotel' ? 'Hotels' : (zoneType === 'Attraction' ? 'Attractions' : (zoneType === 'Restaurant' ? 'Restaurants' : 'Items'));
            let html = '<div class="tooltip-title">' + label + ' in this zone</div>';
            if (!items || !items.length) {
                html += '<div class="tooltip-item"><span class="tooltip-item-name text-muted">No ' + label.toLowerCase() + ' assigned</span></div>';
            } else {
                items.forEach(function(item) {
                    const imgSrc = (item.image && (item.image.startsWith('http') || item.image.startsWith('/'))) ? item.image : (item.image ? '{{ url("/") }}/' + (item.image || '').replace(/^\/+/, '') : defaultImg);
                    html += '<div class="tooltip-item"><img class="tooltip-item-img" src="' + imgSrc + '" alt=""><span class="tooltip-item-name">' + escapeHtml(item.name || '') + '</span></div>';
                });
            }
            $zoneTooltip.html(html).addClass('show');
            const rect = this.getBoundingClientRect();
            $zoneTooltip.css({ left: Math.min(rect.left + (rect.width/2) - 160, window.innerWidth - 330) + 'px', top: (rect.bottom + 4) + 'px' });
        });
        $(document).on('mouseleave', '.zone-cell-hover', scheduleHide);
    });
</script>
@endif

@endsection