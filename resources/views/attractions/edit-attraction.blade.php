@extends('layouts.layout')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

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

<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <ul class="nav nav-pills mb-4 mt-4 d-flex justify-content-center" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ request()->routeIs('attraction.edit') ? 'active' : '' }}" 
                href="{{ route('attraction.edit', Crypt::encrypt($attraction->attraction_id)) }}" 
                   role="tab">
                    Attractions
                </a>
            </li>
            
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ request()->routeIs('tickets.add_ticket') ? 'active' : '' }}" 
                   href="{{ route('tickets.add_ticket', Crypt::encrypt($attraction->attraction_id)) }}" 
                   role="tab">
                    Ticket
                </a>
            </li>
        </ul>
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Edit Attraction & Experience
                <a href="{{ route('attraction.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form id="attractionForm" method="POST" action="{{ route('attraction.update', Crypt::encrypt($attraction->attraction_id)) }}" enctype="multipart/form-data" class="card-body">
                @csrf
                @method('PUT')
                <!-- Hidden Fields -->

                <div id="attractionDetailsContainer">
                    <div class="attraction-form">
                        <div class="row">
                            <!-- attraction Name -->
                            <div class="col-md-3 mb-3">
                                <label for="name" class="form-label"><strong>Attraction Name</strong><span
                                        class="text-danger">*</span></label>
                                <input value="{{$attraction->name}}" type="text" class="form-control" name="name"
                                    placeholder="Enter Attraction Name" required {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'readonly' : '' }}>
                                @error('name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Phone Number -->
                            <div class="col-md-3 mb-3">
                                <label for="phone" class="form-label"><strong>Phone Number</strong><span class="text-danger">*</span></label>
                                <input value="{{$attraction->phone}}" type="text" class="form-control" name="phone" placeholder="Enter Phone Number" required {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'readonly' : '' }}>
                                @error('phone')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- Country -->
                            <div class="mb-3 col-md-3">
                                <label for="country" class="form-label"><strong>Country</strong>
                                    <span style="color: red; font-weight: bold;">*</span>
                                </label>
                                <input value="{{$attraction->country}}" type="text" class="form-control" id="country" placeholder="Select DMC First" name="country" required readonly>
                                @error('country')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- Location -->
                            <div class="col-md-3 mb-3">
                                <label for="city" class="form-label"><strong>City</strong><span class="text-danger">*</span></label>
                                <select name="city" id="citySelect" class="form-control" required {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'disabled' : '' }}>
                                    <option value="{{ $attraction->location }}">{{ $attraction->location }}</option>
                                    @foreach($city as $c)
                                        @if($c->name != $attraction->location)
                                            <option value="{{ $c->name }}">{{ $c->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @if(in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]))
                                    <input type="hidden" name="city" value="{{ $attraction->location }}">
                                @endif
                                @error('city')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            

                             <!-- Senior Age Threshold -->
                             <div class="col-md-3 mb-3">
                                <label for="senior_adult_start_age" class="form-label">
                                    <strong>Senior Age Threshold</strong><span class="text-danger">*</span>
                                </label>
                                <input value="{{$attraction->senior_min_age}}" type="number" class="form-control" id="senior_min_age" name="senior_min_age"
                                    placeholder="e.g., 60" required {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'readonly' : '' }}>
                                <small class="text-muted">Age at which an adult is considered a senior.</small>
                            </div>

                            <!-- Maximum Child Age -->
                            <div class="col-md-3 mb-3">
                                <label for="child_end_age" class="form-label">
                                    <strong>Maximum Child Age</strong><span class="text-danger">*</span>
                                </label>
                                <input value="{{$attraction->child_max_age}}" type="number" class="form-control" id="child_end_age" name="child_end_age"
                                    placeholder="e.g., 12" required {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'readonly' : '' }}>
                                <small class="text-muted">Maximum age until a person is considered a child.</small>
                            </div>

                            <!-- Child Price -->
                            <!-- <div class="col-md-3 mb-3">
                                <label for="child_price" class="form-label">
                                    <strong>Child Price</strong><span class="text-danger">*</span>
                                </label>
                                <input value="{{$attraction->child_price}}" name="child_price" type="text" id="child_price" class="form-control"
                                placeholder="Enter Child Price" required
                                oninput="validateNumericPrice(this)">
                                <small class="validation-message text-danger" id="child_price-validation-message"></small>
                                @error('child_price')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div> -->

                            <!-- Adult Price -->
                            <!-- <div class="col-md-3 mb-3">
                                <label for="adult_price" class="form-label"><strong>Adult Price</strong><span
                                        class="text-danger">*</span></label>
                                <input value="{{$attraction->adult_price}}" type="text" class="form-control" id="adult_price" name="adult_price"
                                    placeholder="Enter Adult Price" required
                                    oninput="validateNumericPrice(this)">
                                <small class="validation-message text-danger" id="adult_price-validation-message"></small>
                                @error('adult_price')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div> -->

                            <!-- Senior Adult Price -->
                            <!-- <div class="col-md-3 mb-3">
                                <label for="senior_adult_price" class="form-label">
                                    <strong>Senior Adult Price</strong><span class="text-danger">*</span>
                                </label>
                                <input value="{{$attraction->senior_adult_price}}" type="text" class="form-control" id="senior_adult_price" name="senior_adult_price"
                                       placeholder="Enter Senior Adult Price" required
                                       oninput="validateNumericPrice(this)">
                                <small class="validation-message text-danger" id="senior_adult_price-validation-message"></small>
                                @error('senior_adult_price')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div> -->

                            

                            <!-- Shared Price -->
                            <!-- <div class="col-md-3 mb-3">
                                <label for="price_shared" class="form-label">
                                    <strong>Price with Transport (shared)</strong><span class="text-danger">*</span>
                                </label>
                                <input name="price_shared" type="text" id="price_shared" value="{{$attraction->price_shared}}" class="form-control"
                                    placeholder="Enter Shared Price" required
                                    oninput="validateNumericPrice(this)">
                                <small class="validation-message text-danger" id="price_shared-validation-message"></small>
                                @error('price_shared')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div> -->

                            

                            <!-- Private Price -->
                            <!-- <div class="col-md-3 mb-3">
                                <label for="price_private" class="form-label">
                                    <strong>Price with Transport (private)</strong><span class="text-danger">*</span>
                                </label>
                                <input name="price_private" type="text" id="price_private" value="{{$attraction->price_private}}" class="form-control"
                                    placeholder="Enter Private Price" required
                                    oninput="validateNumericPrice(this)">
                                <small class="validation-message text-danger" id="price_private-validation-message"></small>
                                @error('price_private')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div> -->

                            <!-- Latitude -->
                            <div class="col-md-3 mb-3">
                                <label for="latitude" class="form-label">
                                    <strong>Latitude</strong><span class="text-danger">*</span>
                                </label>
                                <input name="latitude" type="text" id="latitude" value="{{$attraction->latitude}}" class="form-control"
                                    placeholder="Enter Latitude" required {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'readonly' : 'oninput="validateLatitude(this)"' }}>
                                <small class="validation-message text-danger" id="latitude-validation-message"></small>
                                @error('latitude')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Longitude -->
                            <div class="col-md-3 mb-3">
                                <label for="longitude" class="form-label">
                                    <strong>Longitude</strong><span class="text-danger">*</span>
                                </label>
                                <input name="longitude" type="text" id="longitude" value="{{$attraction->longitude}}" class="form-control"
                                    placeholder="Enter Longitude" required {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'readonly' : 'oninput="validateLongitude(this)"' }}>
                                <small class="validation-message text-danger" id="longitude-validation-message"></small>
                                @error('longitude')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Morning Opening -->
                            <div class="mb-3 col-md-3">
                                <label for="morning_opening" class="form-label"><strong>Morning Opening</strong>
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="morning_opening" name="morning_opening" required {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'disabled' : '' }}>
                                    <option value="">Select One</option>
                                    <option value="1" {{ old('morning_opening', $attraction->morning_opening ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ old('morning_opening', $attraction->morning_opening ?? '') == '0' ? 'selected' : '' }}>No</option>
                                </select>
                                @if(in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]))
                                    <input type="hidden" name="morning_opening" value="{{ $attraction->morning_opening }}">
                                @endif
                                @error('morning_opening')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 col-md-2">
                                <label for="afternoon_opening" class="form-label"><strong>Afternoon Opening</strong>
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="afternoon_opening" name="afternoon_opening" required {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'disabled' : '' }}>
                                    <option value="">Select One</option>
                                    <option value="1" {{ old('afternoon_opening', $attraction->afternoon_opening ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ old('afternoon_opening', $attraction->afternoon_opening ?? '') == '0' ? 'selected' : '' }}>No</option>
                                </select>
                                @if(in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]))
                                    <input type="hidden" name="afternoon_opening" value="{{ $attraction->afternoon_opening }}">
                                @endif
                                @error('afternoon_opening')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 col-md-2">
                                <label for="evening_opening" class="form-label"><strong>Evening Opening</strong>
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="evening_opening" name="evening_opening" required {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'disabled' : '' }}>
                                    <option value="">Select One</option>
                                    <option value="1" {{ old('evening_opening', $attraction->evening_opening ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ old('evening_opening', $attraction->evening_opening ?? '') == '0' ? 'selected' : '' }}>No</option>
                                </select>
                                @if(in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]))
                                    <input type="hidden" name="evening_opening" value="{{ $attraction->evening_opening }}">
                                @endif
                                @error('evening_opening')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 col-md-2">
                                <label for="night_opening" class="form-label"><strong>Night Opening</strong>
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="night_opening" name="night_opening" required {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'disabled' : '' }}>
                                    <option value="">Select One</option>
                                    <option value="1" {{ old('night_opening', $attraction->night_opening ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ old('night_opening', $attraction->night_opening ?? '') == '0' ? 'selected' : '' }}>No</option>
                                </select>
                                    @if(in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]))
                                    <input type="hidden" name="night_opening" value="{{ $attraction->night_opening }}">
                                @endif
                                @error('night_opening')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Type of Attraction -->
                            <div class="col-md-3 mb-3">
                                <label for="attraction_type" class="form-label"><strong>Type of Attraction</strong><span class="text-danger">*</span></label>
                                <select class="form-control" id="attraction_type" name="attraction_type" required>
                                    <option value="">Select One</option>
                                    <option value="2" {{ old    ('attraction_type', $attraction->attraction_type ?? '') == '2' ? 'selected' : '' }}>Attraction</option>
                                    <option value="1" {{ old('attraction_type', $attraction->attraction_type ?? '') == '1' ? 'selected' : '' }}>Tour Site</option>
                                </select>
                                @error('attraction_type')
                                    <div class="text-danger mt-1">{{ $message }}</div>  
                                @enderror
                            </div>

                            {{-- <div id="time-container">
                                @php
                                    $openTimes = json_decode($attraction->open_time, true) ?? [];
                                    $closeTimes = json_decode($attraction->close_time, true) ?? [];
                                @endphp

                                <!-- Labels displayed only once at the top -->
                                <div class="row time-row">
                                    <div class="col-md-5 mb-3">
                                        <label for="property" class="form-label"><strong>Open Time:</strong><span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-5 mb-3">
                                        <label for="property" class="form-label"><strong>Close Time:</strong><span class="text-danger">*</span></label>
                                    </div>
                                </div>

                                <!-- Check if there is data in the arrays, else show one default row -->
                                @if(is_array($openTimes) && count($openTimes) > 0)
                                    <!-- Loop through open and close times to create the input fields -->
                                    @foreach($openTimes as $index => $openTime)
                                    <div class="row time-row">
                                        <!-- Open Time -->
                                        <div class="col-md-5 mb-3">
                                            <input type="time" name="open_time[]" class="form-control open-time" 
                                                value="{{ $openTime }}" required>
                                        </div>

                                        <!-- Close Time -->
                                        <div class="col-md-5 mb-3">
                                            <input type="time" name="close_time[]" class="form-control close-time" 
                                                value="{{ $closeTimes[$index] ?? '' }}" required>
                                        </div>

                                        <div class="col-md-2 mb-3 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger remove-time">Remove</button>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <!-- Show one empty row if no data exists -->
                                    <div class="row time-row">
                                        <!-- Open Time -->
                                        <div class="col-md-5 mb-3">
                                            <input type="time" name="open_time[]" class="form-control open-time" required>
                                        </div>

                                        <!-- Close Time -->
                                        <div class="col-md-5 mb-3">
                                            <input type="time" name="close_time[]" class="form-control close-time" required>
                                        </div>

                                        <div class="col-md-2 mb-3 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger remove-time">Remove</button>
                                        </div>
                                    </div>
                                @endif
                            </div> --}}

                            <div id="time-container">
                                @php
                                    $openTimes = json_decode($attraction->open_time, true) ?? [];
                                    $closeTimes = json_decode($attraction->close_time, true) ?? [];
                                @endphp
                            
                                <!-- First row with time inputs -->
                                @if(is_array($openTimes) && count($openTimes) > 0)
                                    @foreach($openTimes as $index => $openTime)
                                    <div class="row time-row">
                                        @if($index === 0)
                                        <div class="col-md-5 mb-3">
                                            <label for="property" class="form-label"><strong>Open Time:</strong><span class="text-danger">*</span></label>
                                            <input type="text" name="open_time[]" class="form-control open-time" 
                                                value="{{ $openTime }}" placeholder="Select open time" required {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-md-5 mb-3">
                                            <label for="property" class="form-label"><strong>Close Time:</strong><span class="text-danger">*</span></label>
                                            <input type="text" name="close_time[]" class="form-control close-time" 
                                                value="{{ $closeTimes[$index] ?? '' }}" placeholder="Select close time" required {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-md-2 mb-3 d-flex align-items-end">
                                            <button type="button" class="btn btn-success add-time" style="margin-bottom: 10px">Add More</button>
                                        </div>
                                        @else
                                        <div class="col-md-5 mb-3">
                                            <input type="text" name="open_time[]" class="form-control open-time" 
                                                value="{{ $openTime }}" placeholder="Select open time" required {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-md-5 mb-3">
                                            <input type="text" name="close_time[]" class="form-control close-time" 
                                                value="{{ $closeTimes[$index] ?? '' }}" placeholder="Select close time" required {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-md-2 mb-3 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger remove-time" style="margin-bottom: 10px" {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'disabled' : '' }}>Remove</button>
                                        </div>
                                        @endif
                                    </div>
                                    @endforeach
                                @else
                                    <!-- Default empty row if no data exists -->
                                    <div class="row time-row">
                                        <div class="col-md-5 mb-3">
                                            <label for="property" class="form-label"><strong>Open Time:</strong><span class="text-danger">*</span></label>
                                            <input type="text" name="open_time[]" class="form-control open-time" placeholder="Select open time" required {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-md-5 mb-3">
                                            <label for="property" class="form-label"><strong>Close Time:</strong><span class="text-danger">*</span></label>
                                            <input type="text" name="close_time[]" class="form-control close-time" placeholder="Select close time" required {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-md-2 mb-3 d-flex align-items-end">
                                            <button type="button" class="btn btn-success add-time" style="margin-bottom: 10px" {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'disabled' : '' }}>Add More</button>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Add More Button -->
                            {{-- <div class="col-md-2 mb-3 d-flex align-items-end">
                                <button type="button" class="btn btn-success" id="add-time">+ Add More</button>
                            </div> --}}

                            <!-- Hidden input for storing JSON -->
                            <input type="hidden" name="time_data" id="time_data">

                        <div class="row col-md-12">
                        <!-- Master image -->
                            <div class="mt-3 mb-3 col-md-4">
                                <div>
                                    <label for="master_image" class="form-label"><strong>Master
                                            Image</strong></label>
                                    <div id="master-drop-area" class="form-control {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'disabled' : '' }}"
                                        style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px; {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'pointer-events: none; background-color: #f8f9fa;' : '' }}">
                                        {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'Image upload disabled for your role' : 'Drag & Drop your files here or click to upload.' }}
                                        <input type="file" id="master_image" name="master_image" multiple
                                            style="display: none;" {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'disabled' : '' }}>
                                    </div>
                                </div>
                                <div id="master-preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                    style="max-width: 30%; overflow-x: auto; white-space: nowrap;"></div>

                                @if($attraction->master_image)
                                <div class="image-preview-container d-flex flex-wrap gap-2">
                                    <div class="image-preview-wrapper position-relative">
                                        <img src="{{$attraction->master_image}}" alt="Room Master Image"
                                            style="max-width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                                        @if(!in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]))
                                        <button
                                            class="delete-image-btn position-absolute top-0 end-0 btn btn-sm btn-danger"
                                            data-image="{{ $attraction->master_image }}"
                                            style="width: 20px; height: 20px; line-height: 18px; padding: 0; text-align: center; font-size: 14px; z-index: 1;">
                                            &times;
                                        </button>
                                        @endif
                                    </div>
                                </div>
                                @endif


                            </div>

                            <!-- Additional Image drop -->
                            <div class="mt-3 mb-3 col-md-8">
                                <div>
                                    <label for="images" class="form-label"><strong>Additional
                                            Images</strong></label>
                                    <div id="drop-area" class="form-control {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'disabled' : '' }}"
                                        style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px; {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'pointer-events: none; background-color: #f8f9fa;' : '' }}">
                                        {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138]) ? 'Image upload disabled for your role' : 'Drag & Drop your files here or click to upload.' }}
                                        <input type="file" id="images" name="images[]" multiple
                                            style="display: none;" {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'disabled' : '' }}>
                                    </div>

                                    <div id="preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                        style="max-width: 100%; overflow-x: auto; white-space: nowrap;"></div>
                                </div>

                                <!-- Existing Image Section -->
                                <div class="existing-image-preview-container d-flex flex-wrap gap-2">
                                    @php
                                    $images = json_decode($attraction->additional_image, true);
                                    @endphp
                                    
                                    @if($images)
                                    @foreach($images as $img)
                                    <!-- Hidden input to hold existing image path -->

                                    <div class="existing-image-preview-wrapper position-relative">
                                        <input type="hidden" name="existing_images[]" value="{{ $img }}">
                                        <img src="{{ asset($img) }}" alt="Facility Image"
                                            style="max-width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                                        @if(!in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]))
                                        <button
                                            class="delete-image-btn position-absolute top-0 end-0 btn btn-sm btn-danger"
                                            data-image="{{ $img }}"
                                            style="width: 20px; height: 20px; line-height: 18px; padding: 0; text-align: center; font-size: 14px; z-index: 1;">
                                            &times;
                                        </button>
                                        @endif
                                    </div>
                                    @endforeach
                                    @endif
                                </div>
                                <input type="file" name="all_images[]" id="all-images" style="display: none;">

                                @error('images')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                                <div id="preview-container" class="mt-3 d-flex flex-wrap gap-2"
                                    style="max-width: 100%; overflow-x: auto; white-space: nowrap;"></div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label"><strong>Important Notes</strong><span
                                    style="color: red;">*</span></label>
                            <textarea id="summernote" name="description" class="form-control" rows="10" {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'readonly' : '' }}>{{ old('description', $attraction->description) }}</textarea required>
                            @error('description')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Remarks -->
                        <div class="col-md-12 mb-3">
                            <label for="remarks" class="form-label"><strong>Remarks</strong> <small class="text-muted">(Optional)</small></label>
                            <textarea id="remarks" name="remarks" class="form-control" rows="4" placeholder="Enter any remarks or notes (optional)" {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138]) ? 'readonly' : '' }}>{{ old('remarks', $attraction->remarks) }}</textarea>
                            @error('remarks')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Terms & Conditions -->
                        <div class="col-md-12 mb-3">
                            <label for="terms_conditions" class="form-label"><strong>Terms & Conditions</strong><span class="text-danger">*</span></label>
                            <textarea id="terms_conditions" name="terms_conditions" class="form-control" rows="6" placeholder="Enter terms and conditions..." required {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'readonly' : '' }}>{{ old('terms_conditions', $attraction->terms_conditions) }}</textarea>
                            @error('terms_conditions')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mt-2 form-check form-switch">
                            <label for="attraction_status" class="form-label"><strong>Status</strong></label>
                            <span style="color: red; font-weight: bold;">*</span>
                            <input {{$attraction->is_active == 1 ? 'checked' : ''}} class="form-check-input" name="attraction_status" type="checkbox" id="attraction_status"
                                value="1" {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'disabled' : '' }}>
                            @if(in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]))
                                <input type="hidden" name="attraction_status" value="{{ $attraction->is_active }}">
                            @endif
                            <label class="form-check-label"></label>
                            @error('attraction_status')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-3 mt-4">
                        @if(in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]))
                            <button type="button" class="btn btn-secondary px-4" disabled>Update (Read Only)</button>
                            <small class="text-muted mt-2">You have read-only access to this attraction.</small>
                        @else
                            <button type="submit" class="btn btn-primary px-4">Update</button>
                        @endif
                    </div>
            </form>
        </div>
    </div>
</div>
<!-- End of the form -->
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    $(document).ready(function() {
        var isReadOnly = {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'true' : 'false' }};
        
        if (isReadOnly) {
            // Disable Summernote editors for readonly roles
            $('#summernote').summernote({
                height: 200,      
                minHeight: 200,   
                maxHeight: 500,   
                placeholder: 'Enter your content here...',
                toolbar: false,
                disableResizeEditor: true
            });
            $('#summernote').summernote('disable');
            
            $('#remarks').summernote({
                height: 200,      
                minHeight: 200,   
                maxHeight: 500,   
                placeholder: 'Enter any remarks or notes (optional)...',
                toolbar: false,
                disableResizeEditor: true
            });
            $('#remarks').summernote('disable');
            
            $('#terms_conditions').summernote({
                height: 200,      
                minHeight: 200,   
                maxHeight: 500,   
                placeholder: 'Enter terms and conditions...',
                toolbar: false,
                disableResizeEditor: true
            });
            $('#terms_conditions').summernote('disable');
        } else {
            // Normal Summernote for other roles
            $('#summernote').summernote({
                height: 200,      
                minHeight: 200,   
                maxHeight: 500,   
                placeholder: 'Enter your content here...', 
            });
            $('#remarks').summernote({
                height: 200,      
                minHeight: 200,   
                maxHeight: 500,   
                placeholder: 'Enter any remarks or notes (optional)...', 
            });
            $('#terms_conditions').summernote({
                height: 200,      
                minHeight: 200,   
                maxHeight: 500,   
                placeholder: 'Enter terms and conditions...', 
            });
        }
        
        // Initialize Select2 for city (only select from existing cities)
        $('#citySelect').select2({
            placeholder: "Search and Select a City",
            allowClear: true,
            width: '100%'
        });
    });
</script>

{{-- <script>
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
</script> --}}

<!-- Additional Image drop down -->
<script>
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('images');
    const fileList = document.getElementById('preview-container');
    const allImagesInput = document.getElementById('all-images'); // Hidden input
    let files = []; // Store all files manually
    const MAX_VISIBLE_IMAGES = 3; // Maximum number of visible images
    let showAllImages = false; // Toggle for showing all images

    // Trigger file input on click
    dropArea.addEventListener('click', () => fileInput.click());

    // Handle file input change
    fileInput.addEventListener('change', () => handleFiles(fileInput.files));

    // Handle drag-and-drop events
    dropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropArea.style.borderColor = '#000';
    });

    dropArea.addEventListener('dragleave', () => {
        dropArea.style.borderColor = '#ccc';
    });

    dropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        dropArea.style.borderColor = '#ccc';
        handleFiles(e.dataTransfer.files);
    });

    function handleFiles(newFiles) {
        // Append new files to the list
        Array.from(newFiles).forEach(file => {
            if (file.type.startsWith('image/')) {
                files.push(file);
            } else {
                alert(`${file.name} is not a valid image file.`);
            }
        });
        updateFileList();
    }

    function updateFileList() {
        // Clear file list display
        fileList.innerHTML = '';
        const dataTransfer = new DataTransfer();

        // Decide how many files to display based on `showAllImages`
        const visibleFiles = showAllImages ? files : files.slice(0, MAX_VISIBLE_IMAGES);

        visibleFiles.forEach((file, index) => {
            // Create a wrapper for the image and delete button
            const imageWrapper = document.createElement('div');
            imageWrapper.style.position = 'relative';
            imageWrapper.style.display = 'inline-block';
            imageWrapper.style.margin = '10px';
            imageWrapper.style.width = '100px';
            imageWrapper.style.height = '100px';

            // Create an image element for preview
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file); // Create an object URL for the file
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';

            // Create a delete button
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

            // Remove file and update list on delete
            deleteButton.addEventListener('click', () => {
                var isReadOnly = {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'true' : 'false' }};
                
                if (isReadOnly) {
                    alert('You do not have permission to delete images.');
                    return false;
                }
                
                const fileIndex = files.indexOf(file);
                if (fileIndex > -1) {
                    files.splice(fileIndex, 1);
                }
                updateFileList();
            });

            // Append image and delete button to the wrapper
            imageWrapper.appendChild(img);
            imageWrapper.appendChild(deleteButton);
            fileList.appendChild(imageWrapper);

            // Add the file to the DataTransfer object
            dataTransfer.items.add(file);
        });

        // Add all files to the hidden input `all-images`
        const hiddenDataTransfer = new DataTransfer();
        files.forEach(file => hiddenDataTransfer.items.add(file));
        allImagesInput.files = hiddenDataTransfer.files;

        // Add a "More Images" badge if there are more files and not showing all images
        if (!showAllImages && files.length > MAX_VISIBLE_IMAGES) {
            const moreBadge = document.createElement('div');
            moreBadge.textContent = `+${files.length - MAX_VISIBLE_IMAGES} more`;
            moreBadge.style.margin = '10px';
            moreBadge.style.padding = '20px';
            moreBadge.style.backgroundColor = '#007bff';
            moreBadge.style.color = 'white';
            moreBadge.style.borderRadius = '5px';
            moreBadge.style.textAlign = 'center';
            moreBadge.style.fontSize = '14px';
            moreBadge.style.cursor = 'pointer';

            // Add click event to show all images
            moreBadge.addEventListener('click', () => {
                showAllImages = true;
                updateFileList(); // Re-render with all images
            });

            fileList.appendChild(moreBadge);
        }
    }
</script>
<!-- delete existing Image -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var isReadOnly = {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'true' : 'false' }};
        
        // Use event delegation for dynamically added elements
        document.querySelector('.existing-image-preview-container').addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-image-btn')) {
                e.preventDefault(); // Prevent form submission
                e.stopPropagation(); // Stop event propagation
                
                // Check if user has read-only access
                if (isReadOnly) {
                    alert('You do not have permission to delete images.');
                    return false;
                }
                
                const button = e.target;

                // Find the image preview wrapper
                const imageWrapper = button.closest('.existing-image-preview-wrapper');
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

<script>
    $(document).ready(function() {
        // Assuming you have a way to get the user's role ID
        var userRoleId = {{ auth()->user()->role_id }}; // Adjust this line based on your authentication method

        // Check if the user role is one of the specified IDs
        if ([1, 2, 3, 4].includes(userRoleId)) {
            $('#dmc-container').show(); // Show the DMC select box
            $('#dmc').prop('required', true); // Set DMC as required
        } else {
            $('#dmc-container').hide(); // Hide the DMC select box
            $('#dmc').prop('required', false); // Remove required attribute
        }
    });
</script>

<!-- delete existing Master Image -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var isReadOnly = {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'true' : 'false' }};
        
        // Use event delegation for dynamically added elements
        document.querySelector('.image-preview-container').addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-image-btn')) {
                e.preventDefault(); // Prevent form submission
                e.stopPropagation(); // Stop event propagation
                
                // Check if user has read-only access
                if (isReadOnly) {
                    alert('You do not have permission to delete images.');
                    return false;
                }
                
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
            var isReadOnly = {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'true' : 'false' }};
            
            if (isReadOnly) {
                alert('You do not have permission to delete images.');
                return false;
            }
            
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
<!-- Script for append open and close time -->
{{-- <script>
    document.addEventListener("DOMContentLoaded", function () {
        let timeContainer = document.getElementById("time-container");

        // Add More Time Fields
        document.getElementById("add-time").addEventListener("click", function () {
            let newRow = document.createElement("div");
            newRow.classList.add("row", "time-row");

            newRow.innerHTML = `
                <div class="col-md-5 mb-3">
                    <input type="time" name="open_time[]" class="form-control open-time" required>
                </div>
                <div class="col-md-5 mb-3">
                    <input type="time" name="close_time[]" class="form-control close-time" required>
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-time">Remove</button>
                </div>
            `;

            timeContainer.appendChild(newRow);

            // Remove Row Button
            newRow.querySelector(".remove-time").addEventListener("click", function () {
                newRow.remove();
            });
        });

        // Remove Existing Rows
        document.querySelectorAll(".remove-time").forEach(button => {
            button.addEventListener("click", function () {
                this.closest(".time-row").remove();
            });
        });

        // Convert data to JSON before submitting the form
        document.querySelector("form").addEventListener("submit", function () {
            let openTimes = [];
            let closeTimes = [];

            document.querySelectorAll(".open-time").forEach(input => openTimes.push(input.value));
            document.querySelectorAll(".close-time").forEach(input => closeTimes.push(input.value));

            let jsonData = JSON.stringify({ open_times: openTimes, close_times: closeTimes });
            document.getElementById("time_data").value = jsonData;
        });
    });
</script> --}}

<script>
    // Function to initialize flatpickr for time inputs
    function initializeTimePickers(container) {
        var isReadOnly = {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'true' : 'false' }};
        
        if (!isReadOnly) {
            container.querySelectorAll('.open-time').forEach(function(input) {
                if (!input._flatpickr) { // Only initialize if not already initialized
                    flatpickr(input, {
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i", // 24-hour format
                        time_24hr: true,
                        minuteIncrement: 15
                    });
                }
            });
            
            container.querySelectorAll('.close-time').forEach(function(input) {
                if (!input._flatpickr) { // Only initialize if not already initialized
                    flatpickr(input, {
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i", // 24-hour format
                        time_24hr: true,
                        minuteIncrement: 15
                    });
                }
            });
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Initialize flatpickr for initial time inputs
        initializeTimePickers(document);
        
        // Add event listener to all add-time buttons
        document.querySelectorAll(".add-time").forEach(function(button) {
            button.addEventListener("click", function() {
                var isReadOnly = {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'true' : 'false' }};
                
                // Prevent adding new rows for readonly roles
                if (isReadOnly) {
                    return false;
                }
                
                let timeContainer = document.getElementById("time-container");
                
                let newRow = document.createElement("div");
                newRow.classList.add("row", "time-row");
                
                var readonlyAttr = isReadOnly ? 'readonly' : '';
                var disabledAttr = isReadOnly ? 'disabled' : '';
                
                newRow.innerHTML = `
                    <div class="col-md-5 mb-3">
                        <input type="text" name="open_time[]" class="form-control open-time" placeholder="Select open time" required ${readonlyAttr}>
                    </div>
                    <div class="col-md-5 mb-3">
                        <input type="text" name="close_time[]" class="form-control close-time" placeholder="Select close time" required ${readonlyAttr}>
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-time" style="margin-bottom: 10px" ${disabledAttr}>Remove</button>
                    </div>
                `;
                
                timeContainer.appendChild(newRow);
                
                // Initialize flatpickr for the new time inputs
                initializeTimePickers(newRow);
                
                // Add event listener to the new remove button
                newRow.querySelector(".remove-time").addEventListener("click", function() {
                    if (!isReadOnly) {
                        newRow.remove();
                    }
                });
            });
        });
        
        // Add event listeners to all remove-time buttons
        document.querySelectorAll(".remove-time").forEach(function(button) {
            button.addEventListener("click", function() {
                var isReadOnly = {{ in_array(auth()->user()->role_id, [11, 74, 35, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140]) ? 'true' : 'false' }};
                
                // Prevent removing rows for readonly roles
                if (!isReadOnly) {
                    this.closest(".time-row").remove();
                }
            });
        });
        
        // Before submitting the form, convert input values to JSON
        document.querySelector("form").addEventListener("submit", function() {
            let openTimes = [];
            let closeTimes = [];
            
            document.querySelectorAll(".open-time").forEach(input => {
                openTimes.push(input.value);
            });
            
            document.querySelectorAll(".close-time").forEach(input => {
                closeTimes.push(input.value);
            });
            
            let jsonData = JSON.stringify({
                open_times: openTimes,
                close_times: closeTimes
            });
            
            document.getElementById("time_data").value = jsonData;
        });
    });
</script>

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
    
    function validateNumericPrice(input) {
        // Allow only digits and decimal point
        input.value = input.value.replace(/[^\d.]/g, '');
        
        // Allow only one decimal point
        const decimalCount = (input.value.match(/\./g) || []).length;
        if (decimalCount > 1) {
            const parts = input.value.split('.');
            input.value = parts[0] + '.' + parts.slice(1).join('');
        }
        
        const value = input.value.trim();
        const priceRegex = /^\d+(\.\d{1,2})?$/;  // Allows whole numbers or up to 2 decimal places
        
        if (value === '') {
            showValidationMessage(input, false, 'Price is required');
        } else if (!priceRegex.test(value)) {
            showValidationMessage(input, false, `
                Please enter a valid price:
                <ul class="mt-1 mb-0">
                    <li>Must be a positive number</li>
                    <li>Can have up to 2 decimal places</li>
                    <li>Example: 99.99</li>
                </ul>
            `);
        } else {
            showValidationMessage(input, true, '');
        }
    }
    
    function validateLatitude(input) {
        // Force numeric input by immediately replacing non-numeric characters
        input.value = input.value.replace(/[^0-9.-]/g, '');
        
        // Allow only one decimal point and ensure minus sign is only at the beginning
        let value = input.value;
        
        // Ensure only one decimal point
        const decimalCount = (value.match(/\./g) || []).length;
        if (decimalCount > 1) {
            const parts = value.split('.');
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        
        // Ensure minus sign is only at the beginning
        if (value.lastIndexOf('-') > 0) {
            value = value.replace(/-/g, '');
            if (value.charAt(0) !== '-') {
                value = '-' + value;
            }
        }
        
        input.value = value;
        
        const latitudeRegex = /^-?([1-8]?[0-9]\.{1}\d{1,9}$|90\.{1}0{1,9}$)/;
        
        if (value === '') {
            showValidationMessage(input, false, 'Latitude is required');
        } else if (!latitudeRegex.test(value)) {
            showValidationMessage(input, false, `
                Please enter a valid latitude:
                <ul class="mt-1 mb-0">
                    <li>Must be between -90 and 90 degrees</li>
                    <li>Must include decimal point</li>
                    <li>Up to 9 decimal places</li>
                    <li>Example: 23.456789362</li>
                </ul>
            `);
        } else {
            showValidationMessage(input, true, '');
        }
    }
    
    function validateLongitude(input) {
        // Force numeric input by immediately replacing non-numeric characters
        input.value = input.value.replace(/[^0-9.-]/g, '');
        
        // Allow only one decimal point and ensure minus sign is only at the beginning
        let value = input.value;
        
        // Ensure only one decimal point
        const decimalCount = (value.match(/\./g) || []).length;
        if (decimalCount > 1) {
            const parts = value.split('.');
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        
        // Ensure minus sign is only at the beginning
        if (value.lastIndexOf('-') > 0) {
            value = value.replace(/-/g, '');
            if (value.charAt(0) !== '-') {
                value = '-' + value;
            }
        }
        
        input.value = value;
        
        const longitudeRegex = /^-?([1-9]?[0-9]\.{1}\d{1,9}$|1[0-7][0-9]\.{1}\d{1,9}$|180\.{1}0{1,9}$)/;
        
        if (value === '') {
            showValidationMessage(input, false, 'Longitude is required');
        } else if (!longitudeRegex.test(value)) {
            showValidationMessage(input, false, `
                Please enter a valid longitude:
                <ul class="mt-1 mb-0">
                    <li>Must be between -180 and 180 degrees</li>
                    <li>Must include decimal point</li>
                    <li>Up to 9 decimal places</li>
                    <li>Example: 78.123456789</li>
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
@endsection