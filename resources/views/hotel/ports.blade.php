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
            <form action="{{ route('updateports') }}" method="POST" enctype="multipart/form-data" class="card-body">
                @csrf 
                <input type="text" id="hotel_id" name="hotel_id" class="form-control" value="{{ $hotel->hotel_unique_id }}" hidden>
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
                        <div class="row" id="exit_fields_{{ $index }}">
                            <!-- Name Select Box -->
                            <div class="mb-3 col-md-3">
                                <label for="exit_port_name_{{ $index }}" class="form-label"><strong>Port Type</strong><span style="color: red; font-weight: bold;">*</span></label>
                                <select id="exit_port_name_{{ $index }}" name="exit_port_name[]" class="form-control"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                    <option value="">Select a Port</option>
                                    <option value="Airport" {{ old('exit_port_name.' . $index, $exit['type'] ?? '') == 'Airport' ? 'selected' : '' }}>Airport</option>
                                    <option value="Seaport" {{ old('exit_port_name.' . $index, $exit['type'] ?? '') == 'Seaport' ? 'selected' : '' }}>Seaport</option>
                                    <option value="LandPort" {{ old('exit_port_name.' . $index, $exit['type'] ?? '') == 'LandPort' ? 'selected' : '' }}>Land Border Crossing</option>
                                    <option value="Railway" {{ old('exit_port_name.' . $index, $exit['type'] ?? '') == 'Railway' ? 'selected' : '' }}>Railway</option>
                                    <option value="BusStand" {{ old('exit_port_name.' . $index, $exit['type'] ?? '') == 'BusStand' ? 'selected' : '' }}>Bus Stand</option>
                                </select>
                            </div>
                            <!-- Latitude Field -->
                            <div class="mb-3 col-md-3">
                                <label for="exit_latitude_{{ $index }}" class="form-label"><strong>Latitude</strong><span style="color: red; font-weight: bold;">*</span></label>
                                <input type="text" id="exit_latitude_{{ $index }}" name="exit_latitude[]" class="form-control" placeholder="Enter Latitude" value="{{ old('exit_latitude.' . $index, $exit['latitude'] ?? '') }}" oninput="validateLatitude(this)"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="exit_latitude_{{ $index }}-validation-message"></small>
                            </div>
                            <!-- Longitude Field -->
                            <div class="mb-3 col-md-3">
                                <label for="exit_longitude_{{ $index }}" class="form-label"><strong>Longitude</strong><span style="color: red; font-weight: bold;">*</span></label>
                                <input type="text" id="exit_longitude_{{ $index }}" name="exit_longitude[]" class="form-control" placeholder="Enter Longitude" value="{{ old('exit_longitude.' . $index, $exit['longitude'] ?? '') }}" oninput="validateLongitude(this)"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="exit_longitude_{{ $index }}-validation-message"></small>
                            </div>
                            <!-- Distance Field -->
                            <div class="mb-3 col-md-2">
                                <label for="exit_distance_{{ $index }}" class="form-label"><strong>Distance</strong><span style="color: red; font-weight: bold;">*</span></label>
                                <input type="text" id="exit_distance_{{ $index }}" name="exit_distance[]" class="form-control" placeholder="Enter Distance" value="{{ old('exit_distance.' . $index, $exit['distance'] ?? '') }}" oninput="validateDistance(this)"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="exit_distance_{{ $index }}-validation-message"></small>
                            </div>
                            
                            <!-- Delete Button -->
                            <div class="mb-3 col-md-1">
                                <button type="button" class="btn btn-danger remove-exit-field" style="margin-top: 27px;"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>Delete</button>
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
                        <div class="row" id="others_fields_{{ $index }}">
                            <!-- Name Select Box -->
                            <div class="mb-3 col-md-3">
                                <label for="others_port_name_{{ $index }}" class="form-label">
                                    <strong>Name</strong><span style="color: red; font-weight: bold;">*</span>
                                </label>
                                <input type="text" id="others_port_name_{{ $index }}" name="others_port_name[]" class="form-control" 
                                    placeholder="Enter Name" 
                                    value="{{ old('others_port_name.' . $index, $other['type'] ?? '') }}"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                            </div>

                            <!-- Latitude Field -->
                            <div class="mb-3 col-md-2">
                                <label for="others_latitude_{{ $index }}" class="form-label">
                                    <strong>Latitude</strong><span style="color: red; font-weight: bold;">*</span>
                                </label>
                                <input type="text" id="others_latitude_{{ $index }}" name="others_latitude[]" class="form-control" 
                                    placeholder="Enter Latitude" 
                                    value="{{ old('others_latitude.' . $index, $other['latitude'] ?? '') }}" oninput="validateLatitude(this)"
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
                                    value="{{ old('others_longitude.' . $index, $other['longitude'] ?? '') }}" oninput="validateLongitude(this)"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="others_longitude_{{ $index }}-validation-message"></small>
                            </div>

                            <div class="mb-3 col-md-2">
                                <label for="others_type_{{ $index }}" class="form-label">
                                    <strong>Type</strong><span style="color: red; font-weight: bold;">*</span>
                                </label>
                                <select id="others_type_{{ $index }}" name="others_type[]" class="form-select"
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

                            <!-- Distance Field -->
                            <div class="mb-3 col-md-2">
                                <label for="others_distance_{{ $index }}" class="form-label">
                                    <strong>Distance</strong><span style="color: red; font-weight: bold;">*</span>
                                </label>
                                <input type="text" id="others_distance_{{ $index }}" name="others_distance[]" class="form-control" 
                                    placeholder="Enter Distance" 
                                    value="{{ old('others_distance.' . $index, $other['distance'] ?? '') }}" oninput="validateDistance(this)"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="others_distance_{{ $index }}-validation-message"></small>
                            </div>

                            <!-- Delete Button -->
                            <div class="mb-3 col-md-1">
                                <button type="button" class="btn btn-danger remove-other-field" style="margin-top: 30px;"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>Delete</button>
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
                    <button type="submit" class="btn btn-primary px-4"
                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')

<script>
    $(document).ready(function () {
    function setupPortTypeSelects() {
        document.querySelectorAll('select[id^="exit_port_name_"]').forEach(select => {
            const newSelect = select.cloneNode(true);
            select.parentNode.replaceChild(newSelect, select);
            
                newSelect.addEventListener('change', function () {
                const row = this.closest('.row');
                let nameField = row.querySelector('.port-name-field');
                const isExit = this.id.includes('exit_port_name');
                const index = parseInt(this.id.split('_').pop());
                const dataArray = @json($exit_data);
                const portNameValue = dataArray && dataArray[index] ? dataArray[index].port_name || '' : '';
                
                if (!nameField) {
                    nameField = document.createElement('div');
                        nameField.className = 'mb-3 col-md-2 port-name-field position-relative';
                    nameField.innerHTML = `
                        <label class="form-label"><strong>Port Name</strong><span style="color: red; font-weight: bold;">*</span></label>
                            <input type="text" class="form-control port-name-input" name="${'exit_port_specific_name[]'}" 
                            value="${portNameValue}" placeholder="Enter name of the ${this.value}"
                            @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                            <div class="port-suggestions list-group position-absolute w-100" style="z-index: 1000;"></div>
                    `;
                    const latitudeField = row.querySelector('div.mb-3:nth-child(2)');
                    row.insertBefore(nameField, latitudeField);
                    
                        row.querySelectorAll('.mb-3.col-md-3').forEach(col => {
                        col.classList.remove('col-md-3');
                        col.classList.add('col-md-2');
                    });
                } else {
                    const input = nameField.querySelector('input');
                    input.placeholder = `Enter name of the ${this.value}`;
                    input.name = 'exit_port_specific_name[]';
                    if (portNameValue) {
                        input.value = portNameValue;
                    }
                }
                
                nameField.style.display = this.value ? 'block' : 'none';

                    // Autocomplete setup
                    const input = nameField.querySelector('input');
                    const suggestionBox = nameField.querySelector('.port-suggestions');
                    
                    input.addEventListener('keyup', function () {
                        const hotelId = document.getElementById('hotel_id')?.value;
                        const query = this.value;

                        if (!hotelId || !query) {
                            suggestionBox.innerHTML = '';
                            return;
                        }

                        // Get base URL from environment variable
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
                                        
                                        // FIX: Use the correct field names for latitude and longitude
                                        const latitudeInput = row.querySelector('input[name="exit_latitude[]"]');
                                        const longitudeInput = row.querySelector('input[name="exit_longitude[]"]');
                                        
                                        if (latitudeInput && longitudeInput) {
                                            latitudeInput.value = port.latitude;
                                            longitudeInput.value = port.longitude;
                                            
                                            // Trigger validation if available
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

            if (newSelect.value) {
                const event = new Event('change');
                newSelect.dispatchEvent(event);
            }
        });
    }

    setupPortTypeSelects();

        function setupAddMoreButton(buttonId, containerId, isExit = false) {
            const originalClick = document.getElementById(buttonId).onclick;
            document.getElementById(buttonId).onclick = function (e) {
                if (originalClick) originalClick.call(this, e);
        setTimeout(() => {
                    const container = document.getElementById(containerId);
            const newContainer = container.lastElementChild;
            if (newContainer) {
                const portTypeSelect = newContainer.querySelector('.port-type-select');
                        if (!portTypeSelect) return;
                        
                        portTypeSelect.addEventListener('change', function () {
                            let nameField = newContainer.querySelector('.port-name-field');
                    
                    if (!nameField) {
                        nameField = document.createElement('div');
                                nameField.className = 'port-name-field col-md-3 position-relative';
                        nameField.innerHTML = `
                                    <label>Port Name</label>
                                    <input type="text" name="${'exit_port_specific_name[]'}" class="form-control port-name-input" 
                                    placeholder="Enter name of the ${this.value}" @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                    <div class="port-suggestions list-group position-absolute w-100" style="z-index: 1000;"></div>
                                `;
                                this.closest('.col-md-3').insertAdjacentElement('afterend', nameField);
                    } else {
                        const input = nameField.querySelector('input');
                        input.placeholder = `Enter name of the ${this.value}`;
                    }
                    
                    nameField.style.display = this.value ? 'block' : 'none';

                            const input = nameField.querySelector('input');
                            const suggestionBox = nameField.querySelector('.port-suggestions');
                            input.addEventListener('keyup', function () {
                                const hotelId = document.getElementById('hotel_id')?.value;
                                const query = this.value;

                                if (!hotelId || !query) {
                                    suggestionBox.innerHTML = '';
                                    return;
                                }

                                // Get base URL from environment variable
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
                                                
                                                // FIX: Use the correct field names for latitude and longitude
                                                const latitudeInput = newContainer.querySelector('input[name="exit_latitude[]"]');
                                                const longitudeInput = newContainer.querySelector('input[name="exit_longitude[]"]');
                                                
                                                if (latitudeInput && longitudeInput) {
                                                    latitudeInput.value = port.latitude;
                                                    longitudeInput.value = port.longitude;
                                                    
                                                    // Trigger validation if available
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
            }
        }, 100);
    };
        }

        setupAddMoreButton('exit-locations-add-more', 'exit_locations-additional-fields', true);
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
        newExitContainer.classList.add('mt-3', 'border', 'p-3');
        newExitContainer.innerHTML = `
                <div class="row">
                    <div  class="col-md-3">
                        <label for="exit_port_type">Port Type</label>
                        <select name="exit_port_name[]" class="form-select port-type-select" @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                            <option value="">Select a Port</option>
                            <option value="Airport">Airport</option>
                            <option value="Seaport">Seaport</option>
                            <option value="Landport">Land Border Crossing</option>
                            <option value="Railway">Railway</option>
                            <option value="BusStand">Bus Stand</option>
                        </select>
                        <!-- Removed duplicate input field with same name -->
                    </div>
                    <div class="col-md-3">
                        <label for="exit_latitude">Latitude</label>
                        <input type="text" name="exit_latitude[]" class="form-control" placeholder="Enter Latitude" @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                    </div>
                    <div class="col-md-3">
                        <label for="exit_longitude">Longitude</label>
                        <input type="text" name="exit_longitude[]" class="form-control" placeholder="Enter Longitude" @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                    </div>
                    <div class="col-md-2">
                        <label for="exit_distance">Distance</label>
                        <input type="text" name="exit_distance[]" class="form-control" placeholder="Enter Distance" @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                    </div>
                    <div class="mb-3 col-md-1">
                        <button type="button" class="btn btn-danger delete-exit" style="margin-top: 27px;" @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>Delete</button>
                    </div>
                </div>
            </div>
        `;
        exitAdditionalFieldsContainer.appendChild(newExitContainer);
        const portTypeSelect = newExitContainer.querySelector('.port-type-select');
        const otherPortInput = newExitContainer.querySelector('.other-port-input');
        if (otherPortInput) {
            portTypeSelect.addEventListener('change', function () {
                otherPortInput.style.display = this.value === 'Others' ? 'block' : 'none';
            });
        }
        const deleteButton = newExitContainer.querySelector('.delete-exit');
        deleteButton.addEventListener('click', function () {
            newExitContainer.remove();
        });
        
        // Add validation for dynamically created exit fields
        const latitudeInput = newExitContainer.querySelector('input[name="exit_latitude[]"]');
        const longitudeInput = newExitContainer.querySelector('input[name="exit_longitude[]"]');
        const distanceInput = newExitContainer.querySelector('input[name="exit_distance[]"]');
        
        // Generate unique IDs for validation
        const timestamp = new Date().getTime();
        latitudeInput.id = `exit_latitude_new_${timestamp}`;
        longitudeInput.id = `exit_longitude_new_${timestamp}`;
        distanceInput.id = `exit_distance_new_${timestamp}`;
        
        // Add validation message elements
        latitudeInput.insertAdjacentHTML('afterend', `<small class="validation-message" id="${latitudeInput.id}-validation-message"></small>`);
        longitudeInput.insertAdjacentHTML('afterend', `<small class="validation-message" id="${longitudeInput.id}-validation-message"></small>`);
        distanceInput.insertAdjacentHTML('afterend', `<small class="validation-message" id="${distanceInput.id}-validation-message"></small>`);
        
        // Add validation event listeners
        latitudeInput.addEventListener('input', function() {
            validateLatitude(this);
        });
        
        longitudeInput.addEventListener('input', function() {
            validateLongitude(this);
        });
        
        distanceInput.addEventListener('input', function() {
            validateDistance(this);
        });
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
                const row = this.closest('.row');
                row.remove();
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
            const newFields = `
                <div class="row mb-3">
                    <div class="mb-3 col-md-3">
                        <label for="others_port_name" class="form-label">
                            <strong>Name</strong><span style="color: red; font-weight: bold;">*</span>
                        </label>
                        <input type="text" name="others_port_name[]" class="form-control" placeholder="Enter Name" required @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                    </div>

                    <div class="mb-3 col-md-2">
                        <label class="form-label"><strong>Latitude</strong><span style="color: red; font-weight: bold;">*</span></label>
                        <input type="text" name="others_latitude[]" class="form-control" placeholder="Enter Latitude" @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                    </div>

                    <div class="mb-3 col-md-2">
                        <label class="form-label"><strong>Longitude</strong><span style="color: red; font-weight: bold;">*</span></label>
                        <input type="text" name="others_longitude[]" class="form-control" placeholder="Enter Longitude" @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                    </div>

                    <div class="mb-3 col-md-2">
                        <label class="form-label"><strong>Type</strong><span style="color: red; font-weight: bold;">*</span></label>
                        <select name="others_type[]" class="form-select" @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
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
                        <label class="form-label"><strong>Distance</strong><span style="color: red; font-weight: bold;">*</span></label>
                        <input type="text" name="others_distance[]" class="form-control" placeholder="Enter Distance" @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                    </div>

                    <div class="mb-3 col-md-1">
                        <button type="button" class="btn btn-danger remove-other-field" style="margin-top: 27px;" @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>Delete</button>
                    </div>
                </div>`;
            container.insertAdjacentHTML('beforeend', newFields);

            // Reattach event listeners for newly added "Name" selects
            document.querySelectorAll('.port-name-select').forEach(function (selectElement) {
                selectElement.addEventListener('change', function() {
                    const othersInputContainer = this.closest('.row').querySelector('.others-input-container');
                    othersInputContainer.style.display = this.value === 'Other' ? 'block' : 'none';
                });
            });

            // Add Delete functionality to new Delete button
            document.querySelectorAll('.remove-other-field').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    this.closest('.row').remove();
                });
            });
        });

        // Remove delete button functionality for initially loaded fields
        document.querySelectorAll('.remove-other-field').forEach(function (btn) {
            btn.addEventListener('click', function () {
                this.closest('.row').remove();
            });
        });
    });
</script>

<script>
    // Add this function to the scripts section
document.addEventListener('DOMContentLoaded', function() {
    // Update existing distance labels to specify miles
    function updateExistingLabels() {
        // Update entry section labels
        document.querySelectorAll('label[for^="distance_"]').forEach(label => {
            if (!label.innerHTML.includes('(miles)')) {
                label.innerHTML = label.innerHTML.replace('Distance', 'Distance (miles)');
            }
        });
        
        // Update exit section labels
        document.querySelectorAll('label[for^="exit_distance_"]').forEach(label => {
            if (!label.innerHTML.includes('(miles)')) {
                label.innerHTML = label.innerHTML.replace('Distance', 'Distance (miles)');
            }
        });
        
        // Update others section labels
        document.querySelectorAll('label[for^="others_distance_"]').forEach(label => {
            if (!label.innerHTML.includes('(miles)')) {
                label.innerHTML = label.innerHTML.replace('Distance', 'Distance (miles)');
            }
        });
    }
    
    // Add km fields next to the existing miles fields
    function addKmFieldsToExistingRows() {
        // Constants for conversion - make them global
        window.MILES_TO_KM = 1.60934;
        window.KM_TO_MILES = 0.621371;
        
        // Process entry section
        document.querySelectorAll('input[name="distanceentry[]"]').forEach((milesInput, index) => {
            const row = milesInput.closest('.row');
            const milesContainer = milesInput.closest('.mb-3');
            
            // Skip if km field already exists
            if (row.querySelector('.km-field')) return;
            
            // Create km field
            const kmContainer = document.createElement('div');
            kmContainer.className = 'mb-3 col-md-2 km-field';
            kmContainer.innerHTML = `
                <label for="distance_km_${index}" class="form-label"><strong>Distance (km)</strong><span style="color: red; font-weight: bold;">*</span></label>
                <input type="text" id="distance_km_${index}" name="distanceentry_km[]" class="form-control" 
                    placeholder="Enter Distance (km)" value="${milesInput.value ? (parseFloat(milesInput.value) * window.MILES_TO_KM).toFixed(2) : ''}" 
                    oninput="validateDistance(this)" @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                <small class="validation-message" id="distance_km_${index}-validation-message"></small>
            `;
            
            // Insert after miles field
            milesContainer.insertAdjacentElement('afterend', kmContainer);
            
            // Get the km input
            const kmInput = kmContainer.querySelector('input');
            
            // Add conversion event listeners
            milesInput.addEventListener('input', function() {
                if (this.value && !isNaN(parseFloat(this.value))) {
                    kmInput.value = (parseFloat(this.value) * window.MILES_TO_KM).toFixed(2);
                    // Trigger validation on km field
                    if (typeof validateDistance === 'function') {
                        validateDistance(kmInput);
                    }
                } else {
                    kmInput.value = '';
                }
            });
            
            kmInput.addEventListener('input', function() {
                if (this.value && !isNaN(parseFloat(this.value))) {
                    milesInput.value = (parseFloat(this.value) * window.KM_TO_MILES).toFixed(2);
                    // Trigger validation on miles field
                    if (typeof validateDistance === 'function') {
                        validateDistance(milesInput);
                    }
                } else {
                    milesInput.value = '';
                }
            });
        });
        
        // Process exit section
        document.querySelectorAll('input[name="exit_distance[]"]').forEach((milesInput, index) => {
            const row = milesInput.closest('.row');
            const milesContainer = milesInput.closest('.mb-3');
            
            // Skip if km field already exists
            if (row.querySelector('.km-field')) return;
            
            // Create km field
            const kmContainer = document.createElement('div');
            kmContainer.className = 'mb-3 col-md-2 km-field';
            kmContainer.innerHTML = `
                <label for="exit_distance_km_${index}" class="form-label"><strong>Distance (km)</strong><span style="color: red; font-weight: bold;">*</span></label>
                <input type="text" id="exit_distance_km_${index}" name="exit_distance_km[]" class="form-control" 
                    placeholder="Enter Distance (km)" value="${milesInput.value ? (parseFloat(milesInput.value) * window.MILES_TO_KM).toFixed(2) : ''}" 
                    oninput="validateDistance(this)" @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                <small class="validation-message" id="exit_distance_km_${index}-validation-message"></small>
            `;
            
            // Insert after miles field
            milesContainer.insertAdjacentElement('afterend', kmContainer);
            
            // Get the km input
            const kmInput = kmContainer.querySelector('input');
            
            // Add conversion event listeners
            milesInput.addEventListener('input', function() {
                if (this.value && !isNaN(parseFloat(this.value))) {
                    kmInput.value = (parseFloat(this.value) * window.MILES_TO_KM).toFixed(2);
                    // Trigger validation on km field
                    if (typeof validateDistance === 'function') {
                        validateDistance(kmInput);
                    }
                } else {
                    kmInput.value = '';
                }
            });
            
            kmInput.addEventListener('input', function() {
                if (this.value && !isNaN(parseFloat(this.value))) {
                    milesInput.value = (parseFloat(this.value) * window.KM_TO_MILES).toFixed(2);
                    // Trigger validation on miles field
                    if (typeof validateDistance === 'function') {
                        validateDistance(milesInput);
                    }
                } else {
                    milesInput.value = '';
                }
            });
        });
        
        // Process others section
        document.querySelectorAll('input[name="others_distance[]"]').forEach((milesInput, index) => {
            const row = milesInput.closest('.row');
            const milesContainer = milesInput.closest('.mb-3');
            
            // Skip if km field already exists
            if (row.querySelector('.km-field')) return;
            
            // Create km field
            const kmContainer = document.createElement('div');
            kmContainer.className = 'mb-3 col-md-2 km-field';
            kmContainer.innerHTML = `
                <label for="others_distance_km_${index}" class="form-label"><strong>Distance (km)</strong><span style="color: red; font-weight: bold;">*</span></label>
                <input type="text" id="others_distance_km_${index}" name="others_distance_km[]" class="form-control" 
                    placeholder="Enter Distance (km)" value="${milesInput.value ? (parseFloat(milesInput.value) * window.MILES_TO_KM).toFixed(2) : ''}" 
                    oninput="validateDistance(this)" @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                <small class="validation-message" id="others_distance_km_${index}-validation-message"></small>
            `;
            
            // Insert after miles field
            milesContainer.insertAdjacentElement('afterend', kmContainer);
            
            // Get the km input
            const kmInput = kmContainer.querySelector('input');
            
            // Add conversion event listeners
            milesInput.addEventListener('input', function() {
                if (this.value && !isNaN(parseFloat(this.value))) {
                    kmInput.value = (parseFloat(this.value) * window.MILES_TO_KM).toFixed(2);
                    // Trigger validation on km field
                    if (typeof validateDistance === 'function') {
                        validateDistance(kmInput);
                    }
                } else {
                    kmInput.value = '';
                }
            });
            
            kmInput.addEventListener('input', function() {
                if (this.value && !isNaN(parseFloat(this.value))) {
                    milesInput.value = (parseFloat(this.value) * window.KM_TO_MILES).toFixed(2);
                    // Trigger validation on miles field
                    if (typeof validateDistance === 'function') {
                        validateDistance(milesInput);
                    }
                } else {
                    milesInput.value = '';
                }
            });
        });
    }
    
    // Update "Add More" buttons to include km fields in new rows
    function updateAddMoreButtons() {
        // Constants for conversion - make them global
        window.MILES_TO_KM = 1.60934;
        window.KM_TO_MILES = 0.621371;
        
        // Update entry add more button
        const entryAddMoreBtn = document.getElementById('entry-locations-add-more');
        const originalEntryHandler = entryAddMoreBtn.onclick;
        
        entryAddMoreBtn.onclick = function(e) {
            // Call original handler
            if (originalEntryHandler) {
                originalEntryHandler.call(this, e);
            }
            
            // Add km field to the new row
            setTimeout(() => {
                const container = document.getElementById('entry_locations-additional-fields');
                const newContainers = container.querySelectorAll('.entry-input-fields, .mt-3.border');
                
                // Get only the newly added container (the last one)
                if (newContainers.length === 0) return;
                const newContainer = newContainers[newContainers.length - 1];
                
                if (newContainer) {
                    // Check if this container already has a km field
                    if (newContainer.querySelector('.km-field')) return;
                    
                    // Find the miles distance field
                    const milesInput = newContainer.querySelector('input[name="distanceentry[]"]');
                    
                    if (milesInput) {
                        // Clear any default value that might have been carried over
                        milesInput.value = '';
                        
                        // Get the container
                        const milesContainer = milesInput.closest('div');
                        
                        // Generate timestamp for unique ID
                        const timestamp = new Date().getTime();
                        
                        // Create km field with empty value
                        const kmContainer = document.createElement('div');
                        kmContainer.className = milesContainer.className + ' km-field';
                        kmContainer.innerHTML = `
                            <label for="distance_km_${timestamp}">Distance (km)</label>
                            <input type="text" id="distance_km_${timestamp}" name="distanceentry_km[]" class="form-control" placeholder="Enter Distance (km)" value="" @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                            <small class="validation-message" id="distance_km_${timestamp}-validation-message"></small>
                        `;
                        
                        // Insert after miles field
                        milesContainer.insertAdjacentElement('afterend', kmContainer);
                        
                        // Get the km input
                        const kmInput = kmContainer.querySelector('input');
                        
                        // Update miles label
                        const milesLabel = milesContainer.querySelector('label');
                        if (milesLabel && !milesLabel.textContent.includes('(miles)')) {
                            milesLabel.textContent = 'Distance (miles)';
                        }
                        
                        // Add conversion event listeners
                        milesInput.addEventListener('input', function() {
                            if (this.value && !isNaN(parseFloat(this.value))) {
                                kmInput.value = (parseFloat(this.value) * window.MILES_TO_KM).toFixed(2);
                                // Trigger validation on km field
                                if (typeof validateDistance === 'function') {
                                    validateDistance(kmInput);
                                }
                            } else {
                                kmInput.value = '';
                            }
                        });
                        
                        kmInput.addEventListener('input', function() {
                            if (this.value && !isNaN(parseFloat(this.value))) {
                                milesInput.value = (parseFloat(this.value) * window.KM_TO_MILES).toFixed(2);
                                // Trigger validation on miles field
                                if (typeof validateDistance === 'function') {
                                    validateDistance(milesInput);
                                }
                            } else {
                                milesInput.value = '';
                            }
                        });
                    }
                }
            }, 100);
        };
        
        // Update exit add more button
        const exitAddMoreBtn = document.getElementById('exit-locations-add-more');
        const originalExitHandler = exitAddMoreBtn.onclick;
        
        exitAddMoreBtn.onclick = function(e) {
            // Call original handler
            if (originalExitHandler) {
                originalExitHandler.call(this, e);
            }
            
            // Add km field to the new row
            setTimeout(() => {
                const container = document.getElementById('exit_locations-additional-fields');
                const newContainers = container.querySelectorAll('.mt-3.border');
                
                // Get only the newly added container (the last one)
                if (newContainers.length === 0) return;
                const newRow = newContainers[newContainers.length - 1];
                
                if (newRow) {
                    // Check if this container already has a km field
                    if (newRow.querySelector('.km-field')) return;
                    
                    // Find the miles distance field
                    const milesInput = newRow.querySelector('input[name="exit_distance[]"]');
                    
                    if (milesInput) {
                        // Clear any default value that might have been carried over
                        milesInput.value = '';
                        
                        // Get the container
                        const milesContainer = milesInput.closest('div');
                        
                        // Generate timestamp for unique ID
                        const timestamp = new Date().getTime();
                        
                        // Create km field with empty value
                        const kmContainer = document.createElement('div');
                        kmContainer.className = milesContainer.className + ' km-field';
                        kmContainer.innerHTML = `
                            <label for="exit_distance_km_${timestamp}">Distance (km)</label>
                            <input type="text" id="exit_distance_km_${timestamp}" name="exit_distance_km[]" class="form-control" placeholder="Enter Distance (km)" value="" @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                            <small class="validation-message" id="exit_distance_km_${timestamp}-validation-message"></small>
                        `;
                        
                        // Insert after miles field
                        milesContainer.insertAdjacentElement('afterend', kmContainer);
                        
                        // Get the km input
                        const kmInput = kmContainer.querySelector('input');
                        
                        // Update miles label
                        const milesLabel = milesContainer.querySelector('label');
                        if (milesLabel && !milesLabel.textContent.includes('(miles)')) {
                            milesLabel.textContent = 'Distance (miles)';
                        }
                        
                        // Add conversion event listeners
                        milesInput.addEventListener('input', function() {
                            if (this.value && !isNaN(parseFloat(this.value))) {
                                kmInput.value = (parseFloat(this.value) * window.MILES_TO_KM).toFixed(2);
                                // Trigger validation on km field
                                if (typeof validateDistance === 'function') {
                                    validateDistance(kmInput);
                                }
                            } else {
                                kmInput.value = '';
                            }
                        });
                        
                        kmInput.addEventListener('input', function() {
                            if (this.value && !isNaN(parseFloat(this.value))) {
                                milesInput.value = (parseFloat(this.value) * window.KM_TO_MILES).toFixed(2);
                                // Trigger validation on miles field
                                if (typeof validateDistance === 'function') {
                                    validateDistance(milesInput);
                                }
                            } else {
                                milesInput.value = '';
                            }
                        });
                    }
                }
            }, 100);
        };
        
        // Update others add more button
        const othersAddMoreBtn = document.getElementById('others-locations-add-more');
        const originalOthersHandler = othersAddMoreBtn.onclick;
        
        othersAddMoreBtn.onclick = function(e) {
            // Call original handler
            if (originalOthersHandler) {
                originalOthersHandler.call(this, e);
            }
            
            // Add km field to the new row
            setTimeout(() => {
                const container = document.getElementById('others_locations-additional-fields');
                const rows = container.querySelectorAll('.row');
                
                // Get only the newly added row (the last one)
                if (rows.length === 0) return;
                const newRow = rows[rows.length - 1];
                
                if (newRow) {
                    // Check if this row already has a km field
                    if (newRow.querySelector('.km-field')) return;
                    
                    // Find the miles distance field
                    const milesInput = newRow.querySelector('input[name="others_distance[]"]');
                    
                    if (milesInput) {
                        // Clear any default value that might have been carried over
                        milesInput.value = '';
                        
                        // Get the container
                        const milesContainer = milesInput.closest('div');
                        
                        // Generate timestamp for unique ID
                        const timestamp = new Date().getTime();
                        
                        // Create km field with empty value
                        const kmContainer = document.createElement('div');
                        kmContainer.className = milesContainer.className + ' km-field';
                        kmContainer.innerHTML = `
                            <label class="form-label"><strong>Distance (km)</strong><span style="color: red; font-weight: bold;">*</span></label>
                            <input type="text" id="others_distance_km_${timestamp}" name="others_distance_km[]" class="form-control" placeholder="Enter Distance (km)" value="" @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                            <small class="validation-message" id="others_distance_km_${timestamp}-validation-message"></small>
                        `;
                        
                        // Insert after miles field
                        milesContainer.insertAdjacentElement('afterend', kmContainer);
                        
                        // Get the km input
                        const kmInput = kmContainer.querySelector('input');
                        
                        // Update miles label
                        const milesLabel = milesContainer.querySelector('label');
                        if (milesLabel && !milesLabel.textContent.includes('(miles)')) {
                            milesLabel.innerHTML = milesLabel.innerHTML.replace('Distance', 'Distance (miles)');
                        }
                        
                        // Add conversion event listeners
                        milesInput.addEventListener('input', function() {
                            if (this.value && !isNaN(parseFloat(this.value))) {
                                kmInput.value = (parseFloat(this.value) * window.MILES_TO_KM).toFixed(2);
                                // Trigger validation on km field
                                if (typeof validateDistance === 'function') {
                                    validateDistance(kmInput);
                                }
                            } else {
                                kmInput.value = '';
                            }
                        });
                        
                        kmInput.addEventListener('input', function() {
                            if (this.value && !isNaN(parseFloat(this.value))) {
                                milesInput.value = (parseFloat(this.value) * window.KM_TO_MILES).toFixed(2);
                                // Trigger validation on miles field
                                if (typeof validateDistance === 'function') {
                                    validateDistance(milesInput);
                                }
                            } else {
                                milesInput.value = '';
                            }
                        });
                    }
                }
            }, 100);
        };
    }
    
    // Initialize
    updateExistingLabels();
    addKmFieldsToExistingRows();
    updateAddMoreButtons();
    
    // Update the validateDistance function to work with both fields
    window.validateDistance = function(input) {
        // Preserve original function behavior
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
        
        // Determine unit type from input ID
        const isKm = input.id.includes('_km_');
        const unitText = isKm ? 'kilometers' : 'miles';
        
        if (value === '') {
            showValidationMessage(input, false, `Distance in ${unitText} is required`);
        } else if (!distanceRegex.test(value)) {
            showValidationMessage(input, false, `
                Please enter a valid distance in ${unitText}:
                <ul class="mt-1 mb-0">
                    <li>Must be a positive number</li>
                    <li>Can include up to 2 decimal places</li>
                    <li>Example: 25.5</li>
                </ul>
            `);
        } else {
            showValidationMessage(input, true, '');
        }
    };
 });
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
                <li>Example: 78.123456658</li>
            </ul>
        `);
    } else {
        showValidationMessage(input, true, '');
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
    
    if (value === '') {
        showValidationMessage(input, false, 'Distance is required');
    } else if (!distanceRegex.test(value)) {
        showValidationMessage(input, false, `
            Please enter a valid distance:
            <ul class="mt-1 mb-0">
                <li>Must be a positive number</li>
                <li>Can include up to 2 decimal places</li>
                <li>Example: 25.5</li>
            </ul>
        `);
    } else {
        showValidationMessage(input, true, '');
    }
}

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

// Fix for "Add More" buttons - ensure validation works for dynamically added fields
document.addEventListener('DOMContentLoaded', function() {
    // Entry Add More Button
    const entryAddMoreButton = document.getElementById('entry-locations-add-more');
    if (entryAddMoreButton) {
        const originalClickHandler = entryAddMoreButton.onclick;
        entryAddMoreButton.onclick = function(e) {
            if (originalClickHandler) {
                originalClickHandler.call(this, e);
            }
            
            // Find newly added elements and set up validation
            setTimeout(function() {
                const container = document.getElementById('entry_locations-additional-fields');
                    const newContainer = container.querySelector('.entry-input-fields:last-child, .row:last-child');
                if (newContainer) {
                    const latitudeInput = newContainer.querySelector('input[name="latitudentry[]"]');
                    const longitudeInput = newContainer.querySelector('input[name="longitudeentry[]"]');
                    const distanceInput = newContainer.querySelector('input[name="distanceentry[]"]');
                    
                    if (latitudeInput) {
                        const timestamp = new Date().getTime();
                        latitudeInput.id = `latitude_dyn_${timestamp}`;
                        latitudeInput.insertAdjacentHTML('afterend', `<small class="validation-message" id="${latitudeInput.id}-validation-message"></small>`);
                        latitudeInput.oninput = function() { validateLatitude(this); };
                    }
                    
                    if (longitudeInput) {
                        const timestamp = new Date().getTime();
                        longitudeInput.id = `longitude_dyn_${timestamp}`;
                        longitudeInput.insertAdjacentHTML('afterend', `<small class="validation-message" id="${longitudeInput.id}-validation-message"></small>`);
                        longitudeInput.oninput = function() { validateLongitude(this); };
                    }
                    
                    if (distanceInput) {
                        const timestamp = new Date().getTime();
                        distanceInput.id = `distance_dyn_${timestamp}`;
                        distanceInput.insertAdjacentHTML('afterend', `<small class="validation-message" id="${distanceInput.id}-validation-message"></small>`);
                        distanceInput.oninput = function() { validateDistance(this); };
                    }
                }
            }, 100); // Small delay to ensure DOM is updated
        };
    }

    // Exit Add More Button
    const exitAddMoreButton = document.getElementById('exit-locations-add-more');
    if (exitAddMoreButton) {
        const originalClickHandler = exitAddMoreButton.onclick;
        exitAddMoreButton.onclick = function(e) {
            if (originalClickHandler) {
                originalClickHandler.call(this, e);
            }
            
            // Find newly added elements and set up validation
            setTimeout(function() {
                const container = document.getElementById('exit_locations-additional-fields');
                const newContainer = container.lastElementChild;
                if (newContainer) {
                    const latitudeInput = newContainer.querySelector('input[name="exit_latitude[]"]');
                    const longitudeInput = newContainer.querySelector('input[name="exit_longitude[]"]');
                    const distanceInput = newContainer.querySelector('input[name="exit_distance[]"]');
                    
                    if (latitudeInput) {
                        const timestamp = new Date().getTime();
                        latitudeInput.id = `exit_latitude_dyn_${timestamp}`;
                        latitudeInput.insertAdjacentHTML('afterend', `<small class="validation-message" id="${latitudeInput.id}-validation-message"></small>`);
                        latitudeInput.oninput = function() { validateLatitude(this); };
                    }
                    
                    if (longitudeInput) {
                        const timestamp = new Date().getTime();
                        longitudeInput.id = `exit_longitude_dyn_${timestamp}`;
                        longitudeInput.insertAdjacentHTML('afterend', `<small class="validation-message" id="${longitudeInput.id}-validation-message"></small>`);
                        longitudeInput.oninput = function() { validateLongitude(this); };
                    }
                    
                    if (distanceInput) {
                        const timestamp = new Date().getTime();
                        distanceInput.id = `exit_distance_dyn_${timestamp}`;
                        distanceInput.insertAdjacentHTML('afterend', `<small class="validation-message" id="${distanceInput.id}-validation-message"></small>`);
                        distanceInput.oninput = function() { validateDistance(this); };
                    }
                }
            }, 100); // Small delay to ensure DOM is updated
        };
    }

    // Others Add More Button
    const othersAddMoreButton = document.getElementById('others-locations-add-more');
    if (othersAddMoreButton) {
        const originalClickHandler = othersAddMoreButton.onclick;
        othersAddMoreButton.onclick = function(e) {
            if (originalClickHandler) {
                originalClickHandler.call(this, e);
            }
            
            // Find newly added elements and set up validation
            setTimeout(function() {
                const container = document.getElementById('others_locations-additional-fields');
                    const rows = container.querySelectorAll('.row');
                    
                    // Get only the newly added row (the last one)
                    if (rows.length === 0) return;
                    const newRow = rows[rows.length - 1];
                    
                if (newRow) {
                        // Check if this row already has a km field
                        if (newRow.querySelector('.km-field')) return;
                        
                        // Find the miles distance field
                        const milesInput = newRow.querySelector('input[name="others_distance[]"]');
                        
                        if (milesInput) {
                            // Clear any default value that might have been carried over
                            milesInput.value = '';
                            
                            // Get the container
                            const milesContainer = milesInput.closest('div');
                            
                            // Generate timestamp for unique ID
                        const timestamp = new Date().getTime();
                            
                            // Create km field with empty value
                            const kmContainer = document.createElement('div');
                            kmContainer.className = milesContainer.className + ' km-field';
                            kmContainer.innerHTML = `
                                <label class="form-label"><strong>Distance (km)</strong><span style="color: red; font-weight: bold;">*</span></label>
                                <input type="text" id="others_distance_km_${timestamp}" name="others_distance_km[]" class="form-control" placeholder="Enter Distance (km)" value="" @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="others_distance_km_${timestamp}-validation-message"></small>
                            `;
                            
                            // Insert after miles field
                            milesContainer.insertAdjacentElement('afterend', kmContainer);
                            
                            // Get the km input
                            const kmInput = kmContainer.querySelector('input');
                            
                            // Update miles label
                            const milesLabel = milesContainer.querySelector('label');
                            if (milesLabel && !milesLabel.textContent.includes('(miles)')) {
                                milesLabel.innerHTML = milesLabel.innerHTML.replace('Distance', 'Distance (miles)');
                            }
                            
                            // Add conversion event listeners
                            milesInput.addEventListener('input', function() {
                                if (this.value && !isNaN(parseFloat(this.value))) {
                                    kmInput.value = (parseFloat(this.value) * window.MILES_TO_KM).toFixed(2);
                                    // Trigger validation on km field
                                    if (typeof validateDistance === 'function') {
                                        validateDistance(kmInput);
                                    }
                                } else {
                                    kmInput.value = '';
                                }
                            });
                            
                            kmInput.addEventListener('input', function() {
                                if (this.value && !isNaN(parseFloat(this.value))) {
                                    milesInput.value = (parseFloat(this.value) * window.KM_TO_MILES).toFixed(2);
                                    // Trigger validation on miles field
                                    if (typeof validateDistance === 'function') {
                                        validateDistance(milesInput);
                                    }
                                } else {
                                    milesInput.value = '';
                                }
                            });
                    }
                }
            }, 100); // Small delay to ensure DOM is updated
        };
    }

    // Validate existing fields on page load
    const latitudeFields = document.querySelectorAll('input[id^="exit_latitude_"], input[id^="others_latitude_"]');
    const longitudeFields = document.querySelectorAll('input[id^="exit_longitude_"], input[id^="others_longitude_"]');
    const distanceFields = document.querySelectorAll('input[id^="exit_distance_"], input[id^="others_distance_"]');
    
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
