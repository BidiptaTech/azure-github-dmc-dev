@extends('layouts.layout')
@section('content')
<style>
    /* Select2 Custom Styling for Bootstrap 5 Integration */
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
                <span class="d-flex align-items-center flex-wrap gap-2">
                    Add New Vehicle
                    <x-currency-price-note
                        :country="old('country', $selectedCountry ?? null)"
                        :watch-country="true"
                        country-select-id="country"
                    />
                </span>
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
                class="card-body js-submit-loader-form" data-loader-message="Saving...">
                @csrf
                <!-- Hidden Fields -->
                <div id="vehicleDetailsContainer">
                    <div class="vehicle-form">
                        <div class="row">
                            <!-- Select DMC Name -->
                        @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 23 || auth()->user()->role_id == 25 || auth()->user()->role_id == 62 || auth()->user()->role_id == 46 || auth()->user()->role_id == 109 || auth()->user()->role_id == 110)
                            <div class="mb-3 col-md-3" id="dmc-container">
                                <label for="dmc" class="form-label"><strong><i class="ri-building-line"></i> DMC</strong><span style="color: red; font-weight: bold;">*</span></label>
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
                                <label for="driver_id" class="form-label"><strong><i class="ri-steering-2-line"></i> Select Driver</strong></label>
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

                            <!-- Vehicle Color -->
                            <div class="col-md-3 mb-3">
                                <label for="vehicle_color" class="form-label"><strong>Vehicle Color</strong><span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="vehicle_color"
                                    placeholder="Enter Vehicle Color" value="{{ old('vehicle_color') }}">
                                @error('vehicle_color')
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
                                <small class="text-muted mt-1 d-block" style="font-size: 9px;">
                                    <i class="fas fa-info-circle"></i> Plate numbers like "WB 26", "WB-26", "WB/26" will all be treated as "WB26".
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
                            <!-- Seating Capacity(Arr/Dept) -->
                            <div class="col-md-3 mb-3">
                                <label for="seating_capacity" class="form-label"><strong>Seating
                                        Capacity(Arr/Dept)</strong><span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="city_tour_seating_capacity" id="city_tour_seating_capacity"
                                    placeholder="Enter Seating Capacity" value="{{ old('city_tour_seating_capacity') }}" oninput="validateSeatingCapacity(this)">
                                <small class="validation-message text-danger" id="city_tour_seating_capacity-validation-message"></small>
                                @error('city_tour_seating_capacity')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div> 
                            
                            {{-- <!-- City Tour No of Guides -->
                            <div class="col-md-3 mb-3">
                                <label for="city_tour_guides" class="form-label"><strong>No of Guides</strong><span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="city_tour_guides" id="city_tour_guides"
                                    placeholder="Enter No of Guides" value="{{ old('city_tour_guides') }}">
                                @error('city_tour_guides')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div> --}}
                            <!-- Country (Master DMC countries) -->
                            <div class="col-md-3 mb-3">
                                <label for="country" class="form-label"><strong><i class="ri-map-pin-line"></i> Country</strong><span class="text-danger">*</span></label>
                                @php
                                    $scopedCountries = $countries ?? collect();
                                    $vehicleSelectedCountry = old('country', $selectedCountry ?? '');
                                @endphp
                                <select name="country" id="country" class="form-control" required>
                                    @if($scopedCountries->count() !== 1)
                                        <option value="">Select Country</option>
                                    @endif
                                    @foreach($scopedCountries as $c)
                                        <option value="{{ $c->name }}" {{ $vehicleSelectedCountry == $c->name ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                @error('country')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- City Name -->
                            <div class="col-md-3 mb-3">
                                <label for="city_name" class="form-label"><strong><i class="ri-map-pin-line"></i> City Name</strong><span class="text-danger">*</span></label>
                                @php
                                    $hasPreloadedCities = isset($cities) && count($cities) > 0 && $vehicleSelectedCountry !== '';
                                    $placeholder = $hasPreloadedCities ? 'Select City' : 'Select Country First';
                                @endphp

                                <select name="city_name" id="city_name" class="form-control" required {{ !$hasPreloadedCities ? 'disabled' : '' }}>
                                    <option value="">{{ $placeholder }}</option>
                                    @if($hasPreloadedCities)
                                        @foreach($cities as $city)
                                            <option value="{{ $city->name }}" {{ old('city_name') == $city->name ? 'selected' : '' }}>{{ $city->name }}</option>
                                        @endforeach
                                    @endif
                                </select>

                                @error('city_name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>



                            <fieldset id="vehicle_profit" class="border p-4 rounded mb-4">
                                <h5 class="card-title mb-3">Profit</h5>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="day_profit_type" class="form-label"><strong>Type</strong></label>
                                        <select id="day_profit_type" name="day_profit_type" class="form-select form-select-sm">
                                            <option value="percentage" {{ old('day_profit_type', 'percentage') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                            <option value="flat" {{ old('day_profit_type') === 'flat' ? 'selected' : '' }}>Flat</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="mark_up" class="form-label"><strong>Mark up</strong></label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm" id="mark_up" name="mark_up" placeholder="0.00" value="{{ old('mark_up') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="night_profit_type" class="form-label"><strong>Type</strong></label>
                                        <select id="night_profit_type" name="night_profit_type" class="form-select form-select-sm">
                                            <option value="percentage" {{ old('night_profit_type', 'percentage') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                            <option value="flat" {{ old('night_profit_type') === 'flat' ? 'selected' : '' }}>Flat</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="night_surcharge" class="form-label"><strong>Night Surcharge</strong></label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm" id="night_surcharge" name="night_surcharge" placeholder="0.00" value="{{ old('night_surcharge') }}">
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset id="privatePrice" class="border p-4 rounded mb-4">
                                <h5 class="card-title mb-3">Private Car Tarrifs</h5>
                                <fieldset id="taxi_day_charges" class="border p-4 rounded mb-4">
                                    <h5 class="card-title mb-3">Day Charges</h5>
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label for="base_cost_price" class="form-label"><strong>Base Cost Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control vehicle-day-cost-input" id="base_cost_price" name="base_cost_price" data-sell-target="base_price" placeholder="Enter Base Cost Price" value="{{ old('base_cost_price') }}">
                                            @error('base_cost_price')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="base_price" class="form-label"><strong>Base Sell Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control vehicle-day-sell-input" id="base_price" name="base_price" placeholder="Enter Base Sell Price" value="{{ old('base_price') }}">
                                            @error('base_price')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        {{-- Per KM pricing temporarily hidden
                                        <div class="col-md-3 mb-3">
                                            <label for="per_km_below_10_cost_price" class="form-label"><strong>Per KM Below 10km Cost Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control vehicle-day-cost-input" name="per_km_below_10_cost_price" data-sell-target="cost_per_km_below_10" placeholder="Enter Cost Price" value="{{ old('per_km_below_10_cost_price') }}">
                                            @error('per_km_below_10_cost_price')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="cost_per_km_below_10" class="form-label"><strong>Per KM Below 10km Sell Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control vehicle-day-sell-input" name="cost_per_km_below_10" placeholder="Enter Sell Price" value="{{ old('cost_per_km_below_10') }}">
                                            @error('cost_per_km_below_10')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="per_km_10_to_25_cost_price" class="form-label"><strong>Per KM 10-25km Cost Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control vehicle-day-cost-input" name="per_km_10_to_25_cost_price" data-sell-target="cost_per_km_10_to_25" placeholder="Enter Cost Price" value="{{ old('per_km_10_to_25_cost_price') }}">
                                            @error('per_km_10_to_25_cost_price')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="cost_per_km_10_to_25" class="form-label"><strong>Per KM 10-25km Sell Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control vehicle-day-sell-input" name="cost_per_km_10_to_25" placeholder="Enter Sell Price" value="{{ old('cost_per_km_10_to_25') }}">
                                            @error('cost_per_km_10_to_25')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="per_km_above_25_cost_price" class="form-label"><strong>Per KM Above 25km Cost Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control vehicle-day-cost-input" name="per_km_above_25_cost_price" data-sell-target="cost_per_km_above_25" placeholder="Enter Cost Price" value="{{ old('per_km_above_25_cost_price') }}">
                                            @error('per_km_above_25_cost_price')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="cost_per_km_above_25" class="form-label"><strong>Per KM Above 25km Sell Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control vehicle-day-sell-input" name="cost_per_km_above_25" placeholder="Enter Sell Price" value="{{ old('cost_per_km_above_25') }}">
                                            @error('cost_per_km_above_25')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        --}}
                                        <div class="col-md-3 mb-3">
                                            <label for="per_hour_cost_price" class="form-label"><strong>Per Hour Cost Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control vehicle-day-cost-input" id="per_hour_cost_price" name="per_hour_cost_price" data-sell-target="cost_per_hour" placeholder="Enter Cost Price" value="{{ old('per_hour_cost_price') }}">
                                            @error('per_hour_cost_price')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="cost_per_hour" class="form-label"><strong>Per Hour Sell Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control vehicle-day-sell-input" id="cost_per_hour" name="cost_per_hour" placeholder="Enter Sell Price" value="{{ old('cost_per_hour') }}">
                                            @error('cost_per_hour')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </fieldset>
                                <!-- Night charges -->
                                <fieldset id="taxi_night_charges" class="border p-4 rounded mb-4">
                                    <h5 class="card-title mb-3">Night Charges</h5>
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label for="night_base_cost_price" class="form-label"><strong>Base Cost Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control vehicle-night-cost-input" id="night_base_cost_price" name="night_base_cost_price" data-sell-target="night_base_price" placeholder="Enter Base Cost Price" value="{{ old('night_base_cost_price') }}">
                                            @error('night_base_cost_price')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="night_base_price" class="form-label"><strong>Base Sell Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control vehicle-night-sell-input" id="night_base_price" name="night_base_price" placeholder="Enter Base Sell Price" value="{{ old('night_base_price') }}">
                                            @error('night_base_price')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        {{-- Per KM pricing temporarily hidden
                                        <div class="col-md-3 mb-3">
                                            <label for="night_per_km_below_10_cost_price" class="form-label"><strong>Per KM Below 10km Cost Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control auto-calculated-cost vehicle-night-cost-input" name="night_per_km_below_10_cost_price" data-sell-target="night_cost_per_km_below_10" placeholder="Auto-calculated" value="{{ old('night_per_km_below_10_cost_price') }}">
                                            @error('night_per_km_below_10_cost_price')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="night_cost_per_km_below_10" class="form-label"><strong>Per KM Below 10km Sell Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control auto-calculated-sell vehicle-night-sell-input" name="night_cost_per_km_below_10" placeholder="Auto-calculated" value="{{ old('night_cost_per_km_below_10') }}">
                                            @error('night_cost_per_km_below_10')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="night_per_km_10_to_25_cost_price" class="form-label"><strong>Per KM 10-25km Cost Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control auto-calculated-cost vehicle-night-cost-input" name="night_per_km_10_to_25_cost_price" data-sell-target="night_cost_per_km_10_to_25" placeholder="Auto-calculated" value="{{ old('night_per_km_10_to_25_cost_price') }}">
                                            @error('night_per_km_10_to_25_cost_price')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="night_cost_per_km_10_to_25" class="form-label"><strong>Per KM 10-25km Sell Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control auto-calculated-sell vehicle-night-sell-input" name="night_cost_per_km_10_to_25" placeholder="Auto-calculated" value="{{ old('night_cost_per_km_10_to_25') }}">
                                            @error('night_cost_per_km_10_to_25')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="night_per_km_above_25_cost_price" class="form-label"><strong>Per KM Above 25km Cost Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control auto-calculated-cost vehicle-night-cost-input" name="night_per_km_above_25_cost_price" data-sell-target="night_cost_per_km_above_25" placeholder="Auto-calculated" value="{{ old('night_per_km_above_25_cost_price') }}">
                                            @error('night_per_km_above_25_cost_price')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="night_cost_per_km_above_25" class="form-label"><strong>Per KM Above 25km Sell Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control auto-calculated-sell vehicle-night-sell-input" name="night_cost_per_km_above_25" placeholder="Auto-calculated" value="{{ old('night_cost_per_km_above_25') }}">
                                            @error('night_cost_per_km_above_25')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        --}}
                                        <div class="col-md-3 mb-3">
                                            <label for="night_per_hour_cost_price" class="form-label"><strong>Per Hour Cost Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control auto-calculated-cost vehicle-night-cost-input" id="night_per_hour_cost_price" name="night_per_hour_cost_price" data-sell-target="night_cost_per_hour" placeholder="Per Hour Cost Price" value="{{ old('night_per_hour_cost_price') }}">
                                            @error('night_per_hour_cost_price')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="night_cost_per_hour" class="form-label"><strong>Per Hour Sell Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control auto-calculated-sell vehicle-night-sell-input" id="night_cost_per_hour" name="night_cost_per_hour" placeholder="Per Hour Sell Price" value="{{ old('night_cost_per_hour') }}">
                                            @error('night_cost_per_hour')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset id="taxi_cancellation" class="border p-4 rounded mb-4">
                                    <h5 class="card-title mb-3">Cancellation</h5>
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label for="cancellation_cost" class="form-label"><strong>Cancellation Cost Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" id="cancellation_cost" name="cancellation_cost" placeholder="From Base Cost Price" value="{{ old('cancellation_cost') }}">
                                            @error('cancellation_cost')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="cancellation_sell" class="form-label"><strong>Cancellation Sell Price</strong><span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" id="cancellation_sell" name="cancellation_sell" placeholder="From Base Sell Price" value="{{ old('cancellation_sell') }}">
                                            @error('cancellation_sell')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <input type="hidden" name="night_cancel_cost_price" id="night_cancel_cost_price" value="{{ old('night_cancel_cost_price') }}">
                                    <input type="hidden" name="night_cancel_cost" id="night_cancel_cost" value="{{ old('night_cancel_cost') }}">
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
                        <button type="submit" class="btn btn-primary px-4 js-submit-loader-btn">
                            <span class="js-submit-loader-btn-text">Save</span>
                            <span class="js-submit-loader-btn-loading d-none">
                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                Saving...
                            </span>
                        </button>
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
<x-form-submit-loader message="Saving..." />
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
        // Initialize Select2 for DMC dropdown
        $('#dmc').select2({
            placeholder: "Search and Select DMC",
            allowClear: true,
            width: '100%'
        });

        // Initialize Select2 for Driver dropdown
        $('#driver').select2({
            placeholder: "Search and Select Driver",
            allowClear: true,
            width: '100%'
        });

        $('#country').select2({
            placeholder: "Search and Select Country",
            allowClear: true,
            width: '100%'
        });

        // Initialize Select2 for City dropdown
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

<script>
    $(document).ready(function () {
        const dmcId = "{{ $resolvedDmcId ?? '' }}";
        const masterCountryNames = @json($masterDmcCountryNames ?? []);

        function populateCountryOptions(countries, selectedCountry) {
            var $country = $('#country');
            $country.empty();
            if (!countries || countries.length !== 1) {
                $country.append('<option value="">Select Country</option>');
            }
            $.each(countries || [], function (i, name) {
                var selected = (name === selectedCountry) ? 'selected' : '';
                $country.append('<option value="' + name + '" ' + selected + '>' + name + '</option>');
            });
            $country.trigger('change.select2');
        }

        function loadCitiesByCountry(countryName) {
            if (!countryName) {
                $('#city_name').prop('disabled', true)
                    .empty()
                    .append('<option value="">Select Country First</option>')
                    .trigger('change');
                return;
            }

            $('#city_name').prop('disabled', true)
                .empty()
                .append('<option value="">Loading cities...</option>')
                .trigger('change');

            $.ajax({
                url: "{{ route('fetch-cities-by-country') }}",
                type: "GET",
                data: { country: countryName },
                dataType: 'json',
                success: function (response) {
                    $('#city_name').empty().append('<option value="">Select a City</option>');
                    if (response.cities && response.cities.length > 0) {
                        $.each(response.cities, function (idx, city) {
                            $('#city_name').append('<option value="' + city.name + '">' + city.name + '</option>');
                        });
                        $('#city_name').prop('disabled', false);
                    } else {
                        $('#city_name').append('<option value="">No cities available</option>');
                    }
                    $('#city_name').trigger('change');
                },
                error: function () {
                    $('#city_name').prop('disabled', true)
                        .empty()
                        .append('<option value="">Error loading cities</option>')
                        .trigger('change');
                }
            });
        }

        function loadCountriesAndCitiesForDmc(selectedDmcId) {
            if (!selectedDmcId) {
                return;
            }

            $.ajax({
                url: "{{ route('fetch.cities_countries') }}",
                type: "GET",
                data: { dmc_id: selectedDmcId },
                dataType: 'json',
                success: function (response) {
                    var countries = response.countries || (response.country ? [response.country] : []);
                    var selected = response.country || (countries[0] || '');
                    populateCountryOptions(countries, selected);
                    loadCitiesByCountry(selected);
                }
            });
        }

        if (dmcId) {
            loadDriversForDmc(dmcId);
        }

        $('#country').on('change', function () {
            loadCitiesByCountry($(this).val());
        });

        $('#dmc').change(function () {
            const selectedDmcId = $(this).val();
            $('#driver').html('<option value="">Loading drivers...</option>').trigger('change');

            if (selectedDmcId) {
                loadCountriesAndCitiesForDmc(selectedDmcId);
                loadDriversForDmc(selectedDmcId);
            } else {
                $('#city_name').html('<option value="">Select Country First</option>').trigger('change');
                $('#driver').html('<option value="">Select a DMC first</option>').trigger('change');
                if (masterCountryNames.length) {
                    populateCountryOptions(masterCountryNames, '');
                } else {
                    $('#country').val('').trigger('change');
                }
            }
        });

        function loadDriversForDmc(dmcId) {
            if (!dmcId) return;

            $('#driver').html('<option value="">Loading drivers...</option>').trigger('change');

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
                    $('#driver').trigger('change');
                },
                error: function () {
                    $('#driver').html('<option value="">Error loading drivers</option>').trigger('change');
                }
            });
        }
    });
</script>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        let dmcSelect = document.getElementById("dmc_id");
        let citySelect = document.getElementById("city_name");
        if (!dmcSelect || !citySelect) {
            return;
        }

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

<script>
function initNightChargeAutoPopulate() {
    if (!document.getElementById('auto-calculated-style')) {
        const style = document.createElement('style');
        style.id = 'auto-calculated-style';
        style.textContent = `
            .auto-calculated, .auto-calculated-sell, .auto-calculated-cost {
                background-color: #f8f9fa !important;
                border-left: 3px solid #17a2b8 !important;
                transition: all 0.2s ease;
            }
            .auto-calculated:focus, .auto-calculated-sell:focus, .auto-calculated-cost:focus {
                background-color: #fff !important;
                border-left-color: #007bff !important;
            }
            .auto-calculated.value-updated, .auto-calculated-sell.value-updated, .auto-calculated-cost.value-updated {
                animation: highlightUpdate 0.8s ease-in-out;
            }
            @keyframes highlightUpdate {
                0% { background-color: #e8f7ff; }
                100% { background-color: #f8f9fa; }
            }
        `;
        document.head.appendChild(style);
    }

    const dayScope = document.querySelector('#taxi_day_charges') || document;
    const nightScope = document.querySelector('#taxi_night_charges') || document;

    const getNum = (el) => {
        if (!el) return null;
        const v = (el.value ?? '').toString().trim();
        if (v === '') return null;
        const n = Number.parseFloat(v);
        return Number.isFinite(n) ? n : null;
    };

    const format = (n) => (Math.round(n * 100) / 100).toFixed(2);
    const approxEqual = (a, b) => Math.abs(a - b) < 0.01;
    const compute = (dayVal, nightBaseVal) => {
        if (dayVal === null) return null;
        return dayVal + (nightBaseVal ?? 0);
    };

    const setupNightGroup = ({ nightBaseName, pairs, cssClass, runInitialRecalc }) => {
        const nightBaseEl = nightScope.querySelector(`input[name="${nightBaseName}"]`);

        const initAutoFlags = () => {
            const nightBaseVal = getNum(nightBaseEl);
            pairs.forEach(({ day, night }) => {
                const dayEl = dayScope.querySelector(`input[name="${day}"]`);
                const nightEl = nightScope.querySelector(`input[name="${night}"]`);
                if (!dayEl || !nightEl) return;

                const dayVal = getNum(dayEl);
                const nightVal = getNum(nightEl);
                const computed = compute(dayVal, nightBaseVal);

                if (nightVal === null) {
                    nightEl.dataset.auto = '1';
                } else if (computed !== null && approxEqual(nightVal, computed)) {
                    nightEl.dataset.auto = '1';
                } else if (!nightEl.dataset.auto) {
                    nightEl.dataset.auto = '0';
                }
            });
        };

        const recalcNight = () => {
            const nightBaseVal = getNum(nightBaseEl) ?? 0;
            pairs.forEach(({ day, night }) => {
                const dayEl = dayScope.querySelector(`input[name="${day}"]`);
                const nightEl = nightScope.querySelector(`input[name="${night}"]`);
                if (!dayEl || !nightEl) return;
                if ((nightEl.dataset.auto ?? '1') !== '1') return;

                const computed = compute(getNum(dayEl), nightBaseVal);
                nightEl.value = computed === null ? '' : format(computed);
                nightEl.classList.add(cssClass, 'value-updated');
                nightEl.dispatchEvent(new Event('input', { bubbles: true }));
                setTimeout(() => nightEl.classList.remove('value-updated'), 800);
            });
        };

        pairs.forEach(({ night }) => {
            const nightEl = nightScope.querySelector(`input[name="${night}"]`);
            if (!nightEl) return;
            nightEl.addEventListener('input', function (e) {
                if (e && e.isTrusted === false) return;
                this.dataset.auto = '0';
                this.classList.remove(cssClass);
            });
        });

        const bindRecalc = (name, scopeEl) => {
            const el = (scopeEl || document).querySelector(`input[name="${name}"]`);
            if (!el) return;
            el.addEventListener('input', () => recalcNight());
            el.addEventListener('change', () => recalcNight());
        };

        bindRecalc(nightBaseName, nightScope);
        pairs.forEach(({ day }) => bindRecalc(day, dayScope));

        initAutoFlags();
        if (runInitialRecalc) recalcNight();
    };

    setupNightGroup({
        nightBaseName: 'night_base_price',
        cssClass: 'auto-calculated-sell',
        runInitialRecalc: false,
        pairs: [
            // Sell prices are calculated from night cost + Night Surcharge (see vehicle profit script)
        ],
    });

    setupNightGroup({
        nightBaseName: 'night_base_cost_price',
        cssClass: 'auto-calculated-cost',
        runInitialRecalc: true,
        pairs: [
            // { day: 'per_km_below_10_cost_price', night: 'night_per_km_below_10_cost_price' },
            // { day: 'per_km_10_to_25_cost_price', night: 'night_per_km_10_to_25_cost_price' },
            // { day: 'per_km_above_25_cost_price', night: 'night_per_km_above_25_cost_price' },
            { day: 'per_hour_cost_price', night: 'night_per_hour_cost_price' },
        ],
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNightChargeAutoPopulate);
} else {
    initNightChargeAutoPopulate();
}
</script>

<script>
(function () {
    function parseAmount(value) {
        const n = parseFloat(String(value || '0').replace(',', '.'));
        return isNaN(n) ? null : n;
    }

    function calculateSell(costValue, typeEl, amountEl) {
        const cost = parseAmount(costValue);
        if (cost === null) return '';
        const type = (typeEl?.value || 'percentage').toLowerCase();
        const amount = parseAmount(amountEl?.value) ?? 0;
        let sell = cost;
        if (type === 'flat') {
            sell = cost + amount;
        } else {
            sell = cost + (cost * amount / 100);
        }
        return Number(Math.max(0, sell).toFixed(2));
    }

    function updateSellFromCost(costInput, typeEl, amountEl) {
        if (!costInput) return;
        const sellId = costInput.getAttribute('data-sell-target');
        const sellInput = sellId ? document.getElementById(sellId) || document.querySelector(`input[name="${sellId}"]`) : null;
        if (!sellInput) return;
        if (costInput.value === '' || costInput.value === null) return;
        sellInput.value = calculateSell(costInput.value, typeEl, amountEl);
        sellInput.dataset.autoFilled = '1';
        if (sellInput.name === 'base_price' || sellInput.id === 'base_price') {
            syncCancellationFromBase();
        }
    }

    function updateGroup(costSelector, typeId, amountId) {
        const typeEl = document.getElementById(typeId);
        const amountEl = document.getElementById(amountId);
        document.querySelectorAll(costSelector).forEach(function (costInput) {
            updateSellFromCost(costInput, typeEl, amountEl);
        });
    }

    function syncCancellationFromBase() {
        const baseCost = document.getElementById('base_cost_price') || document.querySelector('input[name="base_cost_price"]');
        const baseSell = document.getElementById('base_price') || document.querySelector('input[name="base_price"]');
        const cancelCost = document.getElementById('cancellation_cost') || document.querySelector('input[name="cancellation_cost"]');
        const cancelSell = document.getElementById('cancellation_sell') || document.querySelector('input[name="cancellation_sell"]');
        const nightCancelCost = document.getElementById('night_cancel_cost_price');
        const nightCancelSell = document.getElementById('night_cancel_cost');

        if (baseCost && cancelCost && (cancelCost.dataset.manual !== '1')) {
            cancelCost.value = baseCost.value;
        }
        if (baseSell && cancelSell && (cancelSell.dataset.manual !== '1')) {
            cancelSell.value = baseSell.value;
        }
        if (cancelCost && nightCancelCost) nightCancelCost.value = cancelCost.value;
        if (cancelSell && nightCancelSell) nightCancelSell.value = cancelSell.value;
    }

    function bindGroup(costSelector, sellSelector, typeId, amountId) {
        const typeEl = document.getElementById(typeId);
        const amountEl = document.getElementById(amountId);

        document.querySelectorAll(costSelector).forEach(function (costInput) {
            costInput.addEventListener('input', function () {
                updateSellFromCost(this, typeEl, amountEl);
                if (this.name === 'base_cost_price' || this.id === 'base_cost_price') {
                    syncCancellationFromBase();
                }
            });
            costInput.addEventListener('change', function () {
                updateSellFromCost(this, typeEl, amountEl);
                if (this.name === 'base_cost_price' || this.id === 'base_cost_price') {
                    syncCancellationFromBase();
                }
            });
        });

        document.querySelectorAll(sellSelector).forEach(function (sellInput) {
            sellInput.addEventListener('input', function () {
                this.dataset.autoFilled = '0';
                if (this.name === 'base_price' || this.id === 'base_price') {
                    syncCancellationFromBase();
                }
            });
        });

        if (typeEl) typeEl.addEventListener('change', function () { updateGroup(costSelector, typeId, amountId); });
        if (amountEl) {
            amountEl.addEventListener('input', function () { updateGroup(costSelector, typeId, amountId); });
            amountEl.addEventListener('change', function () { updateGroup(costSelector, typeId, amountId); });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindGroup('.vehicle-day-cost-input', '.vehicle-day-sell-input', 'day_profit_type', 'mark_up');
        bindGroup('.vehicle-night-cost-input', '.vehicle-night-sell-input', 'night_profit_type', 'night_surcharge');

        const cancelCost = document.getElementById('cancellation_cost');
        const cancelSell = document.getElementById('cancellation_sell');
        if (cancelCost) {
            if (cancelCost.value !== '') cancelCost.dataset.manual = '1';
            cancelCost.addEventListener('input', function () {
                this.dataset.manual = '1';
                const nightCancelCost = document.getElementById('night_cancel_cost_price');
                if (nightCancelCost) nightCancelCost.value = this.value;
            });
        }
        if (cancelSell) {
            if (cancelSell.value !== '') cancelSell.dataset.manual = '1';
            cancelSell.addEventListener('input', function () {
                this.dataset.manual = '1';
                const nightCancelSell = document.getElementById('night_cancel_cost');
                if (nightCancelSell) nightCancelSell.value = this.value;
            });
        }

        syncCancellationFromBase();
    });
})();
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

@include('components.currency-price-note-dmc-script')
@endsection