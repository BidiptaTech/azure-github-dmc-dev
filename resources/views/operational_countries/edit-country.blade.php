@extends('layouts.layout')
@section('content')

<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Edit Country Details
                <a href="{{ route('country.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form id="OperationalCountryForm" method="POST" action="{{ route('country.update', $country->operational_country_id) }}"
                enctype="multipart/form-data" class="card-body">
                @csrf
                @method('PUT')
                <input type="hidden" value="{{$country->operational_country_id}}" name="operational_country_id">
                <fieldset class="border p-4 rounded mb-4">
                    <h5 class="card-title mb-3">City Details</h5>
                    <div class="row">
                        <!-- country Name -->
                        <div class="col-md-3 mb-3">
                            <label for="name" class="form-label"><strong>Country Name</strong><span
                                class="text-danger">*</span></label>
                            <input value="{{$country->name}}" type="text" class="form-control" name="name"
                                placeholder="Enter country Name" required readonly>
                            @error('name')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- City Name -->
                        <div class="col-md-3 mb-3">
                            <label for="city_name" class="form-label"><strong>City Name</strong><span
                                    class="text-danger">*</span></label>
                            <input value="{{$country->city}}" type="text" class="form-control" name="city_name"
                                placeholder="Enter City Name" required readonly>
                            @error('city_name')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        
                    </div>
                </fieldset>

                {{-- <fieldset id="tarrifs" class="border-0 border-top p-4 rounded mb-4" style="border-top: 2px solid green !important;">
                    <h5 class="card-title mb-3">Country Car Tarrifs</h5>
                    <!-- vehicle -->
                    <div class="mt-2 col-md-3 form-check form-switch">
                        <label for="vehicle" class="form-label"><strong>Select Vehicle</strong></label>
                        <span style="color: red; font-weight: bold;">*</span>
                        <select class="form-select col-md-3" name="vehicle" type="" id="vehicle">
                            <option value="">Select</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{$vehicle->vehicle_id}}">{{$vehicle->vehicle_name}}</option>
                            @endforeach
                        </select>
                        <label class="form-check-label"></label>
                        @error('vehicle')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <fieldset id="taxi_day_charges" class="border-0 border-top p-4 rounded mb-4" style="border-top: 2px solid green !important;">
                        <h5 class="card-title mb-3">Day Charges</h5>
                        <div class="row">

                            <!-- Base Distance -->
                            <div class="col-md-3 mb-3">
                                <label for="base_distance" class="form-label"><strong>Base
                                        Distance</strong><span class="text-danger">*</span></label>
                                <input value="{{$country->base_distance}}" type="number" step="0.1" class="form-control" name="base_distance"
                                    placeholder="Enter Base Distance" required>
                                @error('base_distance')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- Cost per KM Below 10 -->
                            <div class="col-md-3 mb-3">
                                <label for="cost_per_km_below_10" class="form-label"><strong>Cost per KM
                                        Below 10</strong><span class="text-danger">*</span></label>
                                <input value="{{$country->cost_per_km_below_10}}" type="number" step="0.01" class="form-control"
                                    name="cost_per_km_below_10" placeholder="Enter Cost" required>
                                @error('cost_per_km_below_10')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cost per KM 10 to 25 -->
                            <div class="col-md-3 mb-3">
                                <label for="cost_per_km_10_to_25" class="form-label"><strong>Cost per KM (10 to 25)</strong><span class="text-danger">*</span></label>
                                <input value="{{$country->cost_per_km_10_to_25}}" type="number" step="0.01" class="form-control"
                                    name="cost_per_km_10_to_25" placeholder="Enter Cost" required>
                                @error('cost_per_km_10_to_25')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cost per KM Above 25 -->
                            <div class="col-md-3 mb-3">
                                <label for="cost_per_km_above_25" class="form-label"><strong>Cost per KM
                                        Above 25km</strong><span class="text-danger">*</span></label>
                                <input value="{{$country->cost_per_km_above_25}}" type="number" step="0.01" class="form-control"
                                    name="cost_per_km_above_25" placeholder="Enter Cost" required>
                                @error('cost_per_km_above_25')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cost per Hour -->
                            <div class="col-md-3 mb-3">
                                <label for="cost_per_hour" class="form-label"><strong>Cost per Hour</strong><span class="text-danger">*</span></label>
                                <input value="{{$country->cost_per_hour}}" type="number" step="0.01" class="form-control" name="cost_per_hour"
                                    placeholder="Enter Cost" required>
                                @error('cost_per_hour')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cancel Cost -->
                            <div class="col-md-3 mb-3">
                                <label for="cancel_cost" class="form-label"><strong>Cancel
                                    Cost</strong><span class="text-danger">*</span></label>
                                <input value="{{$country->cancel_cost}}" type="number" class="form-control" name="cancel_cost"
                                    placeholder="Enter Cancel Cost" required>
                                @error('cancel_cost')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </fieldset>
                    <!-- Night charges -->
                    <fieldset id="taxi_night_charges" class="border-0 border-top p-4 rounded mb-4" style="border-top: 2px solid green !important;">
                        <h5 class="card-title mb-3">Night Charges</h5>
                        <div class="row">
                            <!-- Night Cost per KM Below 10 -->
                            <div class="col-md-3 mb-3">
                                <label for="night_cost_per_km_below_10" class="form-label"><strong>Cost per
                                        KM Below 10km</strong><span
                                        class="text-danger">*</span></label>
                                <input value="{{$country->night_cost_per_km_below_10}}" type="number" step="0.01" class="form-control"
                                    name="night_cost_per_km_below_10" placeholder="Enter Cost for night"
                                    required>
                                @error('night_cost_per_km_below_10')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Night Cost per KM 10 to 25 -->
                            <div class="col-md-3 mb-3">
                                <label for="night_cost_per_km_10_to_25" class="form-label"><strong>Cost per
                                        KM (10km to 25km)</strong><span
                                        class="text-danger">*</span></label>
                                <input value="{{$country->night_cost_per_km_10_to_25}}" type="number" step="0.01" class="form-control"
                                    name="night_cost_per_km_10_to_25" placeholder="Enter Cost for night"
                                    required>
                                @error('night_cost_per_km_10_to_25')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Night Cost per KM Above 25 -->
                            <div class="col-md-3 mb-3">
                                <label for="night_cost_per_km_above_25" class="form-label"><strong>Cost per
                                        KM Above 25km</strong><span
                                        class="text-danger">*</span></label>
                                <input value="{{$country->night_cost_per_km_above_25}}" type="number" step="0.01" class="form-control"
                                    name="night_cost_per_km_above_25" placeholder="Enter Cost for night"
                                    required>
                                @error('night_cost_per_km_above_25')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cost per Hour -->
                            <div class="col-md-3 mb-3">
                                <label for="night_cost_per_hour" class="form-label"><strong>Cost per
                                        Hour(Night)</strong><span class="text-danger">*</span></label>
                                <input value="{{$country->night_cost_per_km_below_10}}" type="number" step="0.01" class="form-control" name="night_cost_per_hour"
                                    placeholder="Enter Cost" required>
                                @error('night_cost_per_hour')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cancel Cost -->
                            <div class="col-md-3 mb-3">
                                <label for="night_cancel_cost" class="form-label"><strong>Cancel
                                        Cost</strong><span class="text-danger">*</span></label>
                                <input value="{{$country->night_cancel_cost}}" type="number" step="0.01" class="form-control" name="night_cancel_cost"
                                    placeholder="Enter Cancel Cost" required>
                                @error('night_cancel_cost')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- Night Start Time -->
                            <div class="col-md-2">
                                <label for="night_start_time" class="form-label"><strong>Night Start
                                        Time</strong><span class="text-danger">*</span></label>
                                <input value="{{$country->night_start_time}}" type="time" class="form-control"
                                    name="night_start_time" placeholder="Enter night_start_time" required>
                                @error('night_start_time')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Night End Time -->
                            <div class="col-md-2">
                                <label for="night_end_time" class="form-label"><strong>Night End
                                        Time</strong><span class="text-danger">*</span></label>
                                <input value="{{$country->night_end_time}}" type="time" class="form-control"
                                    name="night_end_time" placeholder="Enter night_end_time" required>
                                @error('night_end_time')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </fieldset>
                </fieldset> --}}

                <fieldset class="border p-4 rounded mb-4">
                    <h5 class="card-title mb-3">City Charges</h5>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="holiday_charges" class="form-label"><strong>Holiday Charges(Multiplier)</strong><span class="text-danger">*</span></label>
                            <input value="{{$country->holiday_charges}}" type="number" step="0.01" class="form-control" name="holiday_charges"
                                placeholder="Enter Holiday Charges" required="" fdprocessedid="xw2ask">
                        </div>

                        <!-- Night Start Time -->
                        <div class="col-md-2">
                            <label for="night_start_time" class="form-label"><strong>Night Start
                                    Time</strong><span class="text-danger">*</span></label>
                            <input value="{{$country->night_start_time}}" type="time" class="form-control"
                                name="night_start_time" placeholder="Enter night_start_time" required>
                            @error('night_start_time')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Night End Time -->
                        <div class="col-md-2">
                            <label for="night_end_time" class="form-label"><strong>Night End
                                    Time</strong><span class="text-danger">*</span></label>
                            <input value="{{$country->night_end_time}}" type="time" class="form-control"
                                name="night_end_time" placeholder="Enter night_end_time" required>
                            @error('night_end_time')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Peak Period Start Time -->
                        <div class="col-md-2">
                            <label for="peakPeriod_start_time" class="form-label"><strong>Peak Period Start
                                    Time</strong><span class="text-danger">*</span></label>
                            <input type="time" value="{{$country->peakPeriod_start_time}}" class="form-control"
                                name="peakPeriod_start_time" placeholder="Enter peak period start time" required>
                            @error('peakPeriod_start_time')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Peak Period End Time -->
                        <div class="col-md-2">
                            <label for="peakPeriod_end_time" class="form-label"><strong>Peak Period End Time</strong><span class="text-danger">*</span></label>
                            <input type="time" value="{{$country->peakPeriod_end_time}}" class="form-control"
                                name="peakPeriod_end_time" placeholder="Enter peak period time" required>
                            @error('peakPeriod_end_time')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="peak_period_charge" class="form-label"><strong>Peak Period Charges(Multiplier)</strong><span class="text-danger">*</span></label>
                            <input value="{{$country->peak_period_charge}}" type="number" step="0.01" class="form-control" name="peak_period_charge"
                                placeholder="Enter Peak Period Charges" required="" fdprocessedid="xw2ask">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="holiday_dates" class="form-label"><strong>Holiday
                                Dates</strong><span class="text-danger">*</span></label>
                            <input value="{{$country->holiday_dates}}" type="text" class="form-control flatpickr-input active" id="holiday_dates"
                                name="holiday_dates" placeholder="Select holiday dates" required=""
                                readonly="readonly" fdprocessedid="jp1a0f">
                        </div>

                        {{-- <div class="col-md-3 mb-3">
                            <label for="entry_port_pickup_charge" class="form-label"><strong>Entry Port Pickup
                                Charge</strong></label>
                            <input value="{{$country->entry_port_pickup_charge}}" type="number" step="0.01" class="form-control"
                                name="entry_port_pickup_charge" placeholder="Enter Pickup Charge"
                                fdprocessedid="qjslg">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="exit_port_drop_charge" class="form-label"><strong>Exit Port Drop
                                    Charge</strong></label>
                            <input value="{{$country->exit_port_drop_charge}}" type="number" step="0.01" class="form-control" name="exit_port_drop_charge"
                                placeholder="Enter Drop Charge" fdprocessedid="popqn">
                        </div> --}}
                    </div>
                </fieldset>

                <!-- Other sections can follow a similar structure -->
                <div class="d-flex gap-3 mt-4">
                    <button type="submit" class="btn btn-primary px-4">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End of the form -->
@endsection

@section('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const holidayDatesInput = document.getElementById('holiday_dates_input');

    // Initialize Flatpickr
    flatpickr("#holiday_dates", {
        mode: "multiple", // Allow multiple date selection
        dateFormat: "Y-m-d", // Format of the selected dates
        onChange: function(selectedDates, dateStr) {
            // Update hidden input with selected dates
            holidayDatesInput.value = dateStr;
        }
    });
});
</script>

<script>
// Initialize Flatpickr with multiple date selection
flatpickr("#holiday_dates", {
    mode: "multiple", // Allows multiple dates selection
    dateFormat: "Y-m-d", // Date format to store
    defaultDate: JSON.parse(document.getElementById("holiday_dates_input").value), // Preselect dates
    onChange: function(selectedDates) {
        // Update the hidden field with the selected dates in JSON format
        const selectedDatesArray = selectedDates.map(date => date.toISOString().split('T')[0]);
        document.getElementById("holiday_dates_input").value = JSON.stringify(selectedDatesArray);

        // Update the visible input field to show selected dates
        document.getElementById("holiday_dates").value = selectedDatesArray.join(", ");
    },
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


<!-- Taxi tarrif -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const taxiTarriffSelect = document.getElementById('vehicle');
    const taxi_day_charges = document.getElementById('taxi_day_charges');
    const taxi_night_charges = document.getElementById('taxi_night_charges');

    taxiTarriffSelect.addEventListener('change', () => {
        const selectedValue = taxiTarriffSelect.value;
        if (selectedValue !== "") {
            taxi_day_charges.classList.remove('d-none');
            taxi_night_charges.classList.remove('d-none');
        } else {
            taxi_day_charges.classList.add('d-none');
            taxi_night_charges.classList.add('d-none');
        }
    })
})
</script>

<script>
    document.addEventListener('DOMContentLoaded', ()=>{
        const vehicle_type = document.getElementById('vehicle');
        const vehicles = @json($vehicles);
        const country = @json($country);

        setTimeout(() => {
            vehicles.forEach(vehicle => {
                if (vehicle.vehicle_id == country.vehicle_id) {
                    console.log("vehicle id = ", vehicle.vehicle_id)
                    console.log("vehicle id = ", country.vehicle_id)
                    // Set the select element value to the matching vehicle_id
                    document.getElementById('vehicle').value = vehicle.vehicle_id;

                    // Trigger the change event to simulate user interaction
                    const selectElement = document.getElementById('vehicle');
                    const event = new Event('change');
                    selectElement.dispatchEvent(event);
                }
            });
        }, 100);
    })
</script>
@endsection