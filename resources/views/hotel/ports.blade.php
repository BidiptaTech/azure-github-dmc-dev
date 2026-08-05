@extends('layouts.layout')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
<style>
    /* Custom styles for Select2 dropdown */
    .select2-container {
        width: 100% !important;
    }
    .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
        padding: 0.375rem 0.75rem !important;
    }
    .select2-selection__rendered {
        line-height: 24px !important;
        padding-left: 0 !important;
    }
    .select2-selection__arrow {
        height: 36px !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #007bff;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        right: 5px;
    }
    .port-coordinates {
        font-size: 11px;
        color: #666;
        display: block;
    }
    .combo-container {
        position: relative;
    }
    .combo-container .input-group-append {
        position: absolute;
        right: 1px;
        top: 1px;
        height: calc(100% - 2px);
        z-index: 5;
    }
    .combo-container .form-control {
        padding-right: 40px;
    }
    .select2-dropdown {
        z-index: 9999;
    }
    .exit-port-block,
    .other-port-block {
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
    }
</style>
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
        @include('hotel.tapview', ['hotel' => $hotel])
            <h5 class="card-header d-flex justify-content-between align-items-center">
            Hotel Ports Data
                <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form id="portsForm" action="{{ route('updateports') }}" method="POST" enctype="multipart/form-data" class="card-body js-submit-loader-form" data-loader-message="Saving..." novalidate>
                @csrf 
                <input type="text" id="hotel_id" name="hotel_id" class="form-control" value="{{ $hotel->hotel_unique_id }}" hidden>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="row">
                    <!-- Port of Exit Section -->
                    <div class="row">
                        <div class="mb-3 col-md-4">
                            <div class="form-check">
                                <input type="checkbox" id="port_of_exit" name="enable_port_of_exit" class="form-check-input" 
                                    {{ old('enable_port_of_exit', $enable_port_of_exit ?? false) ? 'checked' : '' }}
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                <label class="form-check-label" for="port_of_exit"><strong>Enable Port of Entry/Exit</strong></label>
                            </div>
                        </div>
                    </div>

                    <!-- Port of Exit Fields Container -->
                    <div id="exit_fields_container" style="{{ old('enable_port_of_exit', $exit_data ? true : false) ? '' : 'display: none;' }}">
                        @foreach($exit_data as $index => $exit)
                        <div class="exit-port-block" id="exit_fields_{{ $index }}">
                        <div class="row">
                            <!-- Port Type -->
                            <div class="mb-3 col-md-2">
                                <label for="exit_port_name_{{ $index }}" class="form-label"><strong>Port Type</strong><span style="color: red; font-weight: bold;">*</span></label>
                                <select id="exit_port_name_{{ $index }}" name="exit_port_name[]" class="form-control" required
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                    <option value="">Select a Port</option>
                                    <option value="Airport" {{ old('exit_port_name.' . $index, $exit['type'] ?? '') == 'Airport' ? 'selected' : '' }}>Airport</option>
                                    <option value="Seaport" {{ old('exit_port_name.' . $index, $exit['type'] ?? '') == 'Seaport' ? 'selected' : '' }}>Seaport</option>
                                    <option value="LandPort" {{ old('exit_port_name.' . $index, $exit['type'] ?? '') == 'LandPort' ? 'selected' : '' }}>Land Border Crossing</option>
                                    <option value="Railway" {{ old('exit_port_name.' . $index, $exit['type'] ?? '') == 'Railway' ? 'selected' : '' }}>Railway</option>
                                    <option value="BusStand" {{ old('exit_port_name.' . $index, $exit['type'] ?? '') == 'BusStand' ? 'selected' : '' }}>Bus Stand</option>
                                </select>
                            </div>
                            <!-- Port Name -->
                            <div class="mb-3 col-md-2 port-name-field position-relative">
                                <label for="exit_port_specific_name_{{ $index }}" class="form-label"><strong>Port Name</strong><span style="color: red; font-weight: bold;">*</span></label>
                                <input type="text" id="exit_port_specific_name_{{ $index }}" class="form-control port-name-input" name="exit_port_specific_name[]"
                                    value="{{ old('exit_port_specific_name.' . $index, $exit['port_name'] ?? '') }}"
                                    placeholder="Enter port name" required
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <div class="port-suggestions list-group position-absolute w-100" style="z-index: 1000;"></div>
                            </div>
                            <!-- Latitude Field -->
                            <div class="mb-3 col-md-2">
                                <label for="exit_latitude_{{ $index }}" class="form-label"><strong>Latitude</strong><span style="color: red; font-weight: bold;">*</span></label>
                                <input type="text" id="exit_latitude_{{ $index }}" name="exit_latitude[]" class="form-control" placeholder="Enter Latitude" value="{{ old('exit_latitude.' . $index, $exit['latitude'] ?? '') }}" oninput="validateLatitude(this)" required
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="exit_latitude_{{ $index }}-validation-message"></small>
                            </div>
                            <!-- Longitude Field -->
                            <div class="mb-3 col-md-2">
                                <label for="exit_longitude_{{ $index }}" class="form-label"><strong>Longitude</strong><span style="color: red; font-weight: bold;">*</span></label>
                                <input type="text" id="exit_longitude_{{ $index }}" name="exit_longitude[]" class="form-control" placeholder="Enter Longitude" value="{{ old('exit_longitude.' . $index, $exit['longitude'] ?? '') }}" oninput="validateLongitude(this)" required
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="exit_longitude_{{ $index }}-validation-message"></small>
                            </div>
                            <!-- Distance km then miles -->
                            @php
                                $exitMiles = old('exit_distance.' . $index, $exit['distance'] ?? '');
                                $exitKm = ($exitMiles !== '' && is_numeric($exitMiles))
                                    ? number_format((float) $exitMiles * 1.60934, 2, '.', '')
                                    : old('exit_distance_km.' . $index, '');
                            @endphp
                            <div class="mb-3 col-md-2">
                                <label for="exit_distance_km_{{ $index }}" class="form-label"><strong>Distance (km)</strong><span style="color: red; font-weight: bold;">*</span></label>
                                <input type="text" id="exit_distance_km_{{ $index }}" name="exit_distance_km[]" class="form-control distance-km" placeholder="Enter Distance (km)" value="{{ $exitKm }}" data-pair="exit_{{ $index }}" oninput="syncDistancePair(this)" required
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="exit_distance_km_{{ $index }}-validation-message"></small>
                            </div>
                            <div class="mb-3 col-md-2">
                                <label for="exit_distance_{{ $index }}" class="form-label"><strong>Distance (miles)</strong><span style="color: red; font-weight: bold;">*</span></label>
                                <input type="text" id="exit_distance_{{ $index }}" name="exit_distance[]" class="form-control distance-miles" placeholder="Enter Distance (miles)" value="{{ $exitMiles }}" data-pair="exit_{{ $index }}" oninput="syncDistancePair(this)" required
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="exit_distance_{{ $index }}-validation-message"></small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3 col-md-12">
                                <button type="button" class="btn btn-danger remove-exit-field"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>Delete</button>
                            </div>
                        </div>
                        </div>
                        @endforeach
                        <div id="exit_key_locations">
                            <div id="exit_locations-additional-fields"></div>
                            <div class="mb-3 col-md-3">
                                <button type="button" id="exit-locations-add-more" class="btn btn-primary"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>Add More</button>
                            </div>
                        </div>
                    </div>

                    <!-- Others Section -->
                    <div class="row">
                        <div class="mb-3 col-md-4">
                            <div class="form-check">
                                <input type="checkbox" id="others" name="enable_others" class="form-check-input" 
                                    {{ old('enable_others', $others_data ?? false) ? 'checked' : '' }}
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                <label class="form-check-label" for="others"><strong>Near By Attraction</strong></label>
                            </div>
                        </div>
                    </div>

                    <!-- Others Conditional Fields (Initially Hidden/Shown based on enable_others) -->
                    <div id="others_fields_container" style="{{ old('enable_others', $others_data ?? false) ? '' : 'display: none;' }}">
                        @foreach($others as $index => $other)
                        <div class="other-port-block" id="others_fields_{{ $index }}">
                        <div class="row">
                            <!-- Name Select Box -->
                            <div class="mb-3 col-md-2">
                                <label for="others_port_name_{{ $index }}" class="form-label">
                                    <strong>Name</strong><span style="color: red; font-weight: bold;">*</span>
                                </label>
                                <input type="text" id="others_port_name_{{ $index }}" name="others_port_name[]" class="form-control" 
                                    placeholder="Enter Name" 
                                    value="{{ old('others_port_name.' . $index, $other['type'] ?? '') }}" required
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                            </div>

                            <!-- Latitude Field -->
                            <div class="mb-3 col-md-2">
                                <label for="others_latitude_{{ $index }}" class="form-label">
                                    <strong>Latitude</strong><span style="color: red; font-weight: bold;">*</span>
                                </label>
                                <input type="text" id="others_latitude_{{ $index }}" name="others_latitude[]" class="form-control" 
                                    placeholder="Enter Latitude" 
                                    value="{{ old('others_latitude.' . $index, $other['latitude'] ?? '') }}" oninput="validateLatitude(this)" required
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="others_latitude_{{ $index }}-validation-message"></small>
                            </div>

                            <!-- Longitude Field -->
                            <div class="mb-3 col-md-2">
                                <label for="others_longitude_{{ $index }}" class="form-label">
                                    <strong>Longitude</strong><span style="color: red; font-weight: bold;">*</span>
                                </label>
                                <input type="text" id="others_longitude_{{ $index }}" name="others_longitude[]" class="form-control" 
                                    placeholder="Enter Longitude" 
                                    value="{{ old('others_longitude.' . $index, $other['longitude'] ?? '') }}" oninput="validateLongitude(this)" required
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="others_longitude_{{ $index }}-validation-message"></small>
                            </div>

                            <div class="mb-3 col-md-2">
                                <label for="others_type_{{ $index }}" class="form-label">
                                    <strong>Type</strong><span style="color: red; font-weight: bold;">*</span>
                                </label>
                                <select id="others_type_{{ $index }}" name="others_type[]" class="form-select" required
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                    <option value="">Select Type</option>
                                    <option value="Transit" {{ old('others_type.' . $index, $other['port_name'] ?? '') == 'Transit' ? 'selected' : '' }}>Transit</option>
                                    <option value="Transport" {{ old('others_type.' . $index, $other['port_name'] ?? '') == 'Transport' ? 'selected' : '' }}>Transport</option>
                                    <option value="Restaurant" {{ old('others_type.' . $index, $other['port_name'] ?? '') == 'Restaurant' ? 'selected' : '' }}>Restaurant</option>
                                    <option value="Mall" {{ old('others_type.' . $index, $other['port_name'] ?? '') == 'Mall' ? 'selected' : '' }}>Mall</option>
                                    <option value="Local" {{ old('others_type.' . $index, $other['port_name'] ?? '') == 'Local' ? 'selected' : '' }}>Local</option>
                                    <option value="Sightseeing" {{ old('others_type.' . $index, $other['port_name'] ?? '') == 'Sightseeing' ? 'selected' : '' }}>Sightseeing</option>
                                </select>
                                @error('others_type.' . $index)
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Distance Km + Miles -->
                            @php
                                $otherMiles = old('others_distance.' . $index, $other['distance'] ?? '');
                                $otherKm = ($otherMiles !== '' && is_numeric($otherMiles))
                                    ? number_format((float) $otherMiles * 1.60934, 2, '.', '')
                                    : old('others_distance_km.' . $index, '');
                            @endphp
                            <div class="mb-3 col-md-2">
                                <label for="others_distance_km_{{ $index }}" class="form-label">
                                    <strong>Distance (km)</strong><span style="color: red; font-weight: bold;">*</span>
                                </label>
                                <input type="text" id="others_distance_km_{{ $index }}" name="others_distance_km[]" class="form-control distance-km"
                                    placeholder="Enter Distance (km)"
                                    value="{{ $otherKm }}" data-pair="others_{{ $index }}" oninput="syncDistancePair(this)" required
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="others_distance_km_{{ $index }}-validation-message"></small>
                            </div>
                            <div class="mb-3 col-md-2">
                                <label for="others_distance_{{ $index }}" class="form-label">
                                    <strong>Distance (miles)</strong><span style="color: red; font-weight: bold;">*</span>
                                </label>
                                <input type="text" id="others_distance_{{ $index }}" name="others_distance[]" class="form-control distance-miles"
                                    placeholder="Enter Distance (miles)"
                                    value="{{ $otherMiles }}" data-pair="others_{{ $index }}" oninput="syncDistancePair(this)" required
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="others_distance_{{ $index }}-validation-message"></small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3 col-md-12">
                                <button type="button" class="btn btn-danger remove-other-field"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>Delete</button>
                            </div>
                        </div>
                        </div>
                        @endforeach

                        <!-- Add More Button -->
                        <div id="others_key_locations">
                            <div id="others_locations-additional-fields"></div>
                            <div class="mb-3 col-md-4">
                                <button type="button" id="others-locations-add-more" class="btn btn-primary"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>Add More</button>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-primary px-4 js-submit-loader-btn"
                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                        <span class="js-submit-loader-btn-text">Save</span>
                        <span class="js-submit-loader-btn-loading d-none">
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<x-form-submit-loader message="Saving..." />
@endsection

@section('scripts')

<script>
    $(document).ready(function () {
        window.bindPortNameAutocomplete = function (scope) {
            const root = scope || document;
            root.querySelectorAll('.port-name-field').forEach(function (nameField) {
                const input = nameField.querySelector('.port-name-input');
                const suggestionBox = nameField.querySelector('.port-suggestions');
                if (!input || !suggestionBox || input.dataset.autocompleteBound === '1') {
                    return;
                }
                input.dataset.autocompleteBound = '1';
                const row = nameField.closest('.row');

                input.addEventListener('keyup', function () {
                    const hotelId = document.getElementById('hotel_id')?.value;
                    const query = this.value;

                    if (!hotelId || !query) {
                        suggestionBox.innerHTML = '';
                        return;
                    }

                    const baseUrl = "{{ env('APP_URL') }}";
                    fetch(`${baseUrl}/get-ports?hotel_id=${encodeURIComponent(hotelId)}&q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            suggestionBox.innerHTML = '';
                            data.forEach(port => {
                                const item = document.createElement('div');
                                item.className = 'list-group-item list-group-item-action';
                                item.textContent = port.port_name;
                                item.style.cursor = 'pointer';
                                item.addEventListener('click', () => {
                                    input.value = port.port_name;
                                    suggestionBox.innerHTML = '';

                                    const latitudeInput = row.querySelector('input[name="exit_latitude[]"]');
                                    const longitudeInput = row.querySelector('input[name="exit_longitude[]"]');

                                    if (latitudeInput && longitudeInput) {
                                        latitudeInput.value = port.latitude;
                                        longitudeInput.value = port.longitude;

                                        if (typeof validateLatitude === 'function') {
                                            validateLatitude(latitudeInput);
                                        }
                                        if (typeof validateLongitude === 'function') {
                                            validateLongitude(longitudeInput);
                                        }
                                    }
                                });
                                suggestionBox.appendChild(item);
                            });
                        });
                });
            });
        };

        // Update placeholder when Port Type changes
        document.querySelectorAll('select[id^="exit_port_name_"], select.port-type-select').forEach(function (select) {
            select.addEventListener('change', function () {
                const row = this.closest('.row');
                const nameInput = row ? row.querySelector('.port-name-input') : null;
                if (nameInput && this.value) {
                    nameInput.placeholder = `Enter name of the ${this.value}`;
                }
            });
        });

        window.bindPortNameAutocomplete(document);
    });
</script>


<script>
   document.addEventListener('DOMContentLoaded', function () {
    const portOfExitCheckbox = document.getElementById('port_of_exit');
    const exitFieldsContainer = document.getElementById('exit_fields_container');

    portOfExitCheckbox.addEventListener('change', function () {
        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20)
            return; // Prevent unauthorized users from changing
        @endif
        exitFieldsContainer.style.display = this.checked ? 'block' : 'none';
    });
    const exitAddMoreButton = document.getElementById('exit-locations-add-more');
    const exitAdditionalFieldsContainer = document.getElementById('exit_locations-additional-fields');
    exitAddMoreButton.addEventListener('click', function () {
        const newExitContainer = document.createElement('div');
        newExitContainer.classList.add('mt-3', 'exit-port-block');
        const pairId = 'exit_new_' + Date.now();
        const timestamp = Date.now();
        newExitContainer.innerHTML = `
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label class="form-label"><strong>Port Type</strong><span style="color: red; font-weight: bold;">*</span></label>
                        <select name="exit_port_name[]" class="form-select port-type-select" required @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                            <option value="">Select a Port</option>
                            <option value="Airport">Airport</option>
                            <option value="Seaport">Seaport</option>
                            <option value="LandPort">Land Border Crossing</option>
                            <option value="Railway">Railway</option>
                            <option value="BusStand">Bus Stand</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3 port-name-field position-relative">
                        <label class="form-label"><strong>Port Name</strong><span style="color: red; font-weight: bold;">*</span></label>
                        <input type="text" name="exit_port_specific_name[]" class="form-control port-name-input" placeholder="Enter port name" required @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                        <div class="port-suggestions list-group position-absolute w-100" style="z-index: 1000;"></div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label"><strong>Latitude</strong><span style="color: red; font-weight: bold;">*</span></label>
                        <input type="text" id="exit_latitude_new_${timestamp}" name="exit_latitude[]" class="form-control" placeholder="Enter Latitude" required @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                        <small class="validation-message" id="exit_latitude_new_${timestamp}-validation-message"></small>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label"><strong>Longitude</strong><span style="color: red; font-weight: bold;">*</span></label>
                        <input type="text" id="exit_longitude_new_${timestamp}" name="exit_longitude[]" class="form-control" placeholder="Enter Longitude" required @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                        <small class="validation-message" id="exit_longitude_new_${timestamp}-validation-message"></small>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label"><strong>Distance (km)</strong><span style="color: red; font-weight: bold;">*</span></label>
                        <input type="text" id="exit_distance_km_new_${timestamp}" name="exit_distance_km[]" class="form-control distance-km" placeholder="Enter Distance (km)" data-pair="${pairId}" oninput="syncDistancePair(this)" required @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                        <small class="validation-message" id="exit_distance_km_new_${timestamp}-validation-message"></small>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label"><strong>Distance (miles)</strong><span style="color: red; font-weight: bold;">*</span></label>
                        <input type="text" id="exit_distance_new_${timestamp}" name="exit_distance[]" class="form-control distance-miles" placeholder="Enter Distance (miles)" data-pair="${pairId}" oninput="syncDistancePair(this)" required @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                        <small class="validation-message" id="exit_distance_new_${timestamp}-validation-message"></small>
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3 col-md-12">
                        <button type="button" class="btn btn-danger delete-exit" @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>Delete</button>
                    </div>
                </div>
        `;
        exitAdditionalFieldsContainer.appendChild(newExitContainer);
        const deleteButton = newExitContainer.querySelector('.delete-exit');
        deleteButton.addEventListener('click', function () {
            newExitContainer.remove();
        });
        newExitContainer.querySelectorAll('input[name="exit_latitude[]"]').forEach(el => el.addEventListener('input', function() { validateLatitude(this); }));
        newExitContainer.querySelectorAll('input[name="exit_longitude[]"]').forEach(el => el.addEventListener('input', function() { validateLongitude(this); }));
        if (typeof window.bindPortNameAutocomplete === 'function') {
            window.bindPortNameAutocomplete(newExitContainer);
        }
        const portTypeSelect = newExitContainer.querySelector('.port-type-select');
        if (portTypeSelect) {
            portTypeSelect.addEventListener('change', function () {
                const nameInput = newExitContainer.querySelector('.port-name-input');
                if (nameInput && this.value) {
                    nameInput.placeholder = `Enter name of the ${this.value}`;
                }
            });
        }
    });
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.port-name-select').forEach(function (dropdown) {
            dropdown.addEventListener('change', function () {
                const container = this.closest('.row'); // Get the parent row
                const othersInput = container.querySelector('.others-input-container'); // Get the "Others" input container
                if (this.value === 'Others') {
                    othersInput.style.display = 'block';
                } else {
                    othersInput.style.display = 'none';
                }
            });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.remove-entry-field').forEach(button => {
            button.addEventListener('click', function () {
                const row = this.closest('.row');
                row.remove();
            });
        });
        document.querySelectorAll('.remove-exit-field').forEach(button => {
            button.addEventListener('click', function () {
                const block = this.closest('.exit-port-block') || this.closest('.row');
                block.remove();
            });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle visibility for Others fields
        document.getElementById('others').addEventListener('change', function() {
            @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20)
                return; // Prevent unauthorized users from changing
            @endif
            const othersFieldsContainer = document.getElementById('others_fields_container');
            othersFieldsContainer.style.display = this.checked ? 'block' : 'none';
        });

        // Show/hide "Specify Name" field based on "Other" selection in Name dropdown
        document.querySelectorAll('.port-name-select').forEach(function (selectElement) {
            selectElement.addEventListener('change', function() {
                const othersInputContainer = this.closest('.row').querySelector('.others-input-container');
                othersInputContainer.style.display = this.value === 'Other' ? 'block' : 'none';
            });
        });

        // Add More functionality for Others section
        document.getElementById('others-locations-add-more').addEventListener('click', function() {
           
            const container = document.getElementById('others_locations-additional-fields');
            const pairId = 'others_new_' + Date.now();
            const timestamp = Date.now();
            const newFields = `
                <div class="other-port-block mb-3">
                <div class="row">
                    <div class="mb-3 col-md-2">
                        <label class="form-label">
                            <strong>Name</strong><span style="color: red; font-weight: bold;">*</span>
                        </label>
                        <input type="text" name="others_port_name[]" class="form-control" placeholder="Enter Name" required @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                    </div>

                    <div class="mb-3 col-md-2">
                        <label class="form-label"><strong>Latitude</strong><span style="color: red; font-weight: bold;">*</span></label>
                        <input type="text" id="others_latitude_new_${timestamp}" name="others_latitude[]" class="form-control" placeholder="Enter Latitude" oninput="validateLatitude(this)" required @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                        <small class="validation-message" id="others_latitude_new_${timestamp}-validation-message"></small>
                    </div>

                    <div class="mb-3 col-md-2">
                        <label class="form-label"><strong>Longitude</strong><span style="color: red; font-weight: bold;">*</span></label>
                        <input type="text" id="others_longitude_new_${timestamp}" name="others_longitude[]" class="form-control" placeholder="Enter Longitude" oninput="validateLongitude(this)" required @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                        <small class="validation-message" id="others_longitude_new_${timestamp}-validation-message"></small>
                    </div>

                    <div class="mb-3 col-md-2">
                        <label class="form-label"><strong>Type</strong><span style="color: red; font-weight: bold;">*</span></label>
                        <select name="others_type[]" class="form-select" required @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                            <option value="">Select Type</option>
                            <option value="Transit">Transit</option>
                            <option value="Transport">Transport</option>
                            <option value="Restaurant">Restaurant</option>
                            <option value="Mall">Mall</option>
                            <option value="Local">Local</option>
                            <option value="Sightseeing">Sightseeing</option>
                        </select>
                    </div>

                    <div class="mb-3 col-md-2">
                        <label class="form-label"><strong>Distance (km)</strong><span style="color: red; font-weight: bold;">*</span></label>
                        <input type="text" id="others_distance_km_new_${timestamp}" name="others_distance_km[]" class="form-control distance-km" placeholder="Enter Distance (km)" data-pair="${pairId}" oninput="syncDistancePair(this)" required @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                        <small class="validation-message" id="others_distance_km_new_${timestamp}-validation-message"></small>
                    </div>

                    <div class="mb-3 col-md-2">
                        <label class="form-label"><strong>Distance (miles)</strong><span style="color: red; font-weight: bold;">*</span></label>
                        <input type="text" id="others_distance_new_${timestamp}" name="others_distance[]" class="form-control distance-miles" placeholder="Enter Distance (miles)" data-pair="${pairId}" oninput="syncDistancePair(this)" required @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                        <small class="validation-message" id="others_distance_new_${timestamp}-validation-message"></small>
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3 col-md-12">
                        <button type="button" class="btn btn-danger remove-other-field" @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>Delete</button>
                    </div>
                </div>
                </div>`;
            container.insertAdjacentHTML('beforeend', newFields);

            // Reattach event listeners for newly added "Name" selects
            document.querySelectorAll('.port-name-select').forEach(function (selectElement) {
                selectElement.addEventListener('change', function() {
                    const othersInputContainer = this.closest('.row').querySelector('.others-input-container');
                    if (othersInputContainer) {
                        othersInputContainer.style.display = this.value === 'Other' ? 'block' : 'none';
                    }
                });
            });

            // Add Delete functionality to new Delete button
            document.querySelectorAll('.remove-other-field').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const block = this.closest('.other-port-block') || this.closest('.row');
                    block.remove();
                });
            });
        });

        // Remove delete button functionality for initially loaded fields
        document.querySelectorAll('.remove-other-field').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const block = this.closest('.other-port-block') || this.closest('.row');
                block.remove();
            });
        });
    });
</script>

<script>
window.MILES_TO_KM = 1.60934;
window.KM_TO_MILES = 0.621371;

/**
 * Keep Distance (miles) and Distance (km) in sync before save.
 * Saved value remains exit_distance[] / others_distance[] (miles).
 */
window.syncDistancePair = function (input) {
    input.value = String(input.value || '').replace(/[^0-9.]/g, '');
    const parts = input.value.split('.');
    if (parts.length > 2) {
        input.value = parts[0] + '.' + parts.slice(1).join('');
    }

    const pair = input.getAttribute('data-pair');
    if (!pair) return;

    const row = input.closest('.row') || document;
    const milesInput = row.querySelector('.distance-miles[data-pair="' + pair + '"]');
    const kmInput = row.querySelector('.distance-km[data-pair="' + pair + '"]');
    if (!milesInput || !kmInput) return;

    const isKm = input.classList.contains('distance-km');
    const num = parseFloat(input.value);

    if (input.value === '' || isNaN(num)) {
        if (isKm) {
            milesInput.value = '';
        } else {
            kmInput.value = '';
        }
    } else if (isKm) {
        milesInput.value = (num * window.KM_TO_MILES).toFixed(2);
    } else {
        kmInput.value = (num * window.MILES_TO_KM).toFixed(2);
    }

    if (typeof validateDistance === 'function') {
        validateDistance(milesInput);
        validateDistance(kmInput);
    }
};
</script>

<script>
// Add validation functions
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
        return false;
    } else if (!latitudeRegex.test(value)) {
        showValidationMessage(input, false, `
            Please enter a valid latitude:
            <ul class="mt-1 mb-0">
                <li>Must be between -90 and 90 degrees</li>
                <li>Must include decimal point</li>
                <li>Up to 9 decimal places</li>
                <li>Example: 23.456789802</li>
            </ul>
        `);
        return false;
    } else {
        showValidationMessage(input, true, '');
        return true;
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
        return false;
    } else if (!longitudeRegex.test(value)) {
        showValidationMessage(input, false, `
            Please enter a valid longitude:
            <ul class="mt-1 mb-0">
                <li>Must be between -180 and 180 degrees</li>
                <li>Must include decimal point</li>
                <li>Up to 9 decimal places</li>
                <li>Example: 78.123456658</li>
            </ul>
        `);
        return false;
    } else {
        showValidationMessage(input, true, '');
        return true;
    }
}

function validateDistance(input) {
    // Force numeric input by immediately replacing non-numeric characters
    input.value = input.value.replace(/[^0-9.]/g, '');
    
    // Allow only one decimal point
    let value = input.value;
    
    // Ensure only one decimal point
    const decimalCount = (value.match(/\./g) || []).length;
    if (decimalCount > 1) {
        const parts = value.split('.');
        value = parts[0] + '.' + parts.slice(1).join('');
        input.value = value;
    }
    
    const distanceRegex = /^[0-9]+(\.[0-9]{1,2})?$/;
    const unitText = input.classList.contains('distance-km') ? 'kilometers' : 'miles';
    
    if (value === '') {
        showValidationMessage(input, false, `Distance in ${unitText} is required`);
        return false;
    } else if (!distanceRegex.test(value)) {
        showValidationMessage(input, false, `
            Please enter a valid distance in ${unitText}:
            <ul class="mt-1 mb-0">
                <li>Must be a positive number</li>
                <li>Can include up to 2 decimal places</li>
                <li>Example: 25.5</li>
            </ul>
        `);
        return false;
    } else {
        showValidationMessage(input, true, '');
        return true;
    }
}

function markFieldInvalid(field, message) {
    if (!field) return;
    field.classList.add('is-invalid');
    field.classList.remove('is-valid');
    if (typeof showValidationMessage === 'function' && field.tagName === 'INPUT') {
        // Prefer existing validation message slot when available
        const existingMsg = document.getElementById(field.id + '-validation-message');
        if (existingMsg) {
            showValidationMessage(field, false, message);
            return;
        }
    }
    let feedback = field.parentElement.querySelector('.ports-required-feedback');
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'text-danger ports-required-feedback mt-1';
        field.parentElement.appendChild(feedback);
    }
    feedback.textContent = message;
}

function clearFieldInvalid(field) {
    if (!field) return;
    field.classList.remove('is-invalid');
    const feedback = field.parentElement ? field.parentElement.querySelector('.ports-required-feedback') : null;
    if (feedback) feedback.remove();
}

function validateRequiredSelectOrText(field, label) {
    const value = (field.value || '').toString().trim();
    if (!value) {
        markFieldInvalid(field, label + ' is required');
        return false;
    }
    clearFieldInvalid(field);
    field.classList.add('is-valid');
    return true;
}

function validatePortsFormBeforeSave() {
    let isValid = true;
    let firstInvalid = null;

    const exitEnabled = document.getElementById('port_of_exit')?.checked;
    const othersEnabled = document.getElementById('others')?.checked;

    if (exitEnabled) {
        const exitRows = document.querySelectorAll('#exit_fields_container .row');
        exitRows.forEach(function (row) {
            // Skip utility rows that don't contain port fields
            const typeSelect = row.querySelector('select[name="exit_port_name[]"]');
            if (!typeSelect) return;

            if (!validateRequiredSelectOrText(typeSelect, 'Port Type')) {
                isValid = false;
                firstInvalid = firstInvalid || typeSelect;
            }

            const portName = row.querySelector('input[name="exit_port_specific_name[]"]');
            if (typeSelect.value) {
                if (!portName) {
                    isValid = false;
                    markFieldInvalid(typeSelect, 'Port Name is required');
                    firstInvalid = firstInvalid || typeSelect;
                } else if (!validateRequiredSelectOrText(portName, 'Port Name')) {
                    isValid = false;
                    firstInvalid = firstInvalid || portName;
                }
            }

            const lat = row.querySelector('input[name="exit_latitude[]"]');
            const lng = row.querySelector('input[name="exit_longitude[]"]');
            const dist = row.querySelector('input[name="exit_distance[]"]');

            if (lat && !validateLatitude(lat)) {
                isValid = false;
                firstInvalid = firstInvalid || lat;
            }
            if (lng && !validateLongitude(lng)) {
                isValid = false;
                firstInvalid = firstInvalid || lng;
            }
            if (dist && !validateDistance(dist)) {
                isValid = false;
                firstInvalid = firstInvalid || dist;
            }
        });
    }

    if (othersEnabled) {
        const otherRows = document.querySelectorAll('#others_fields_container .row');
        otherRows.forEach(function (row) {
            const nameInput = row.querySelector('input[name="others_port_name[]"]');
            if (!nameInput) return;

            if (!validateRequiredSelectOrText(nameInput, 'Name')) {
                isValid = false;
                firstInvalid = firstInvalid || nameInput;
            }

            const typeSelect = row.querySelector('select[name="others_type[]"]');
            if (typeSelect && !validateRequiredSelectOrText(typeSelect, 'Type')) {
                isValid = false;
                firstInvalid = firstInvalid || typeSelect;
            }

            const lat = row.querySelector('input[name="others_latitude[]"]');
            const lng = row.querySelector('input[name="others_longitude[]"]');
            const dist = row.querySelector('input[name="others_distance[]"]');

            if (lat && !validateLatitude(lat)) {
                isValid = false;
                firstInvalid = firstInvalid || lat;
            }
            if (lng && !validateLongitude(lng)) {
                isValid = false;
                firstInvalid = firstInvalid || lng;
            }
            if (dist && !validateDistance(dist)) {
                isValid = false;
                firstInvalid = firstInvalid || dist;
            }
        });
    }

    if (!isValid && firstInvalid) {
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstInvalid.focus();
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Incomplete or invalid fields',
                text: 'Please fill all mandatory fields with valid values before saving.'
            });
        } else {
            alert('Please fill all mandatory fields with valid values before saving.');
        }
    }

    return isValid;
}

document.addEventListener('DOMContentLoaded', function () {
    const portsForm = document.getElementById('portsForm');
    if (portsForm) {
        portsForm.addEventListener('submit', function (e) {
            if (!validatePortsFormBeforeSave()) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });
    }
});

// Add CSS for validation messages and icons
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

// Validate existing fields on page load (miles/km rows already include both fields)
document.addEventListener('DOMContentLoaded', function() {
    const latitudeFields = document.querySelectorAll('input[id^="exit_latitude_"], input[id^="others_latitude_"]');
    const longitudeFields = document.querySelectorAll('input[id^="exit_longitude_"], input[id^="others_longitude_"]');
    const distanceFields = document.querySelectorAll('.distance-miles, .distance-km');

    latitudeFields.forEach(field => {
        if (field.value && field.value.trim() !== '') {
            validateLatitude(field);
        }
    });

    longitudeFields.forEach(field => {
        if (field.value && field.value.trim() !== '') {
            validateLongitude(field);
        }
    });

    distanceFields.forEach(field => {
        if (field.value && field.value.trim() !== '') {
            validateDistance(field);
        }
    });
});
</script>
@endsection
