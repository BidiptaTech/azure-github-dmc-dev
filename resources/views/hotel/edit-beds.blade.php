@extends('layouts.layout')
@section('title', 'Hotels')
@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@endsection
@section('css')
<link href="{{ URL::asset('build/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
@endsection

@section('content')
@include('hotel.tapview', ['hotel' => $hotel])
<link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Edit Bed Details
                <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form id="hotelForm" method="POST" action="{{ route('bed.update') }}"
                enctype="multipart/form-data" class="card-body">
                @csrf
                <input type="hidden" class="form-control" name="hotel_id"
                    value="{{ $hotel->hotel_unique_id }}">
                <input type="hidden" class="form-control" name="bed_id" value="{{ $hotelBed->bed_id }}">
                <hr>
                <div id="hotelRatesContainer">
                    <div class="hotel-rate-form">
                        <div class="row">
                            <!-- Room Type -->
                            <div class="col-md-3 mb-3">
                                <label for="room_type" class="form-label"><strong>Room Category</strong><span
                                        class="text-danger">*</span></label>
                                <select id="room_type" class="form-control" name="room_type" required>
                                    <option value="">Select Room Type</option>
                                    @foreach($rooms as $room)
                                    <option value="{{ $room->room_id }}"
                                        {{ $room->room_id == $hotelBed->room_id ? 'selected' : '' }}>
                                        {{ $room->room_type }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Bed Type -->
                            <div class="col-md-3 mb-3">
                                <label for="bed_type" class="form-label"><strong>Bed Type</strong><span
                                        class="text-danger">*</span></label>
                                <select id="bed_type" class="form-control" name="bed_type" required onchange="onBedTypeChange()">
                                    <option value="">Select Room Category</option>
                                    @foreach($beds as $bed)
                                    <option value="{{ $bed->bedId }}"
                                        {{ $bed->name == $hotelBed->room_type ? 'selected' : '' }}>
                                        {{ $bed->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- No of rooms -->
                            <div class="mb-3 col-md-3">
                                <label for="no_of_rooms" class="form-label"><strong>No. of
                                        Rooms</strong><span class="text-danger">*</span></label>
                                <select id="no_of_rooms" class="form-control" name="no_of_rooms" required>
                                </select>
                                @error('${bedType}_adult_count')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- Occupancy -->
                            <div class="mb-3 col-md-3">
                                <label for="max-occupancy" class="form-label"><strong>Maximum
                                        Occupancy</strong><span class="text-danger">*</span></label>
                                <input value="{{$hotelBed->max_occupancy}}" type="number"
                                    name="max_occupancy" id="max-occupancy" class="form-control"
                                    placeholder="Enter maximum occupancy">
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="extra_bed" class="form-label"><strong>Extra
                                        Bed</strong><span class="text-danger">*</span></label>
                                <select name="extra_bed" id="extra_bed" class="form-control">
                                    <option value="">Select One</option>
                                    <option {{ $hotelBed->extra_bed == 1 ? 'selected' : '' }} value="1">Yes
                                    </option>
                                    <option {{ $hotelBed->extra_bed == 0 ? 'selected' : '' }} value="0">No
                                    </option>
                                </select>
                            </div>

                            <!-- extra bed type -->
                            <div class="col-md-3 mb-3 extra_bed_type" style="display: none;">
                                <label for="${bedType}-extra-bed-type" class="form-label"><strong>Extra Bed
                                        Type</strong><span class="text-danger">*</span></label>
                                <select name="extra_bed_type" id="extra_bed_type" class="form-control">
                                    <option value="">Select One</option>
                                    <option {{$hotelBed->extra_bed_type == "Sofa Bed" ? 'selected' : ''}} value="Sofa Bed">Sofa Bed</option>
                                    <option {{$hotelBed->extra_bed_type == "Wall Bed" ? 'selected' : ''}} value="Wall Bed">Wall Bed</option>
                                    <option {{$hotelBed->extra_bed_type == "Futon Bed" ? 'selected' : ''}} value="Futon Bed">Futon Bed</option>
                                    <option {{$hotelBed->extra_bed_type == "Rollaway Bed" ? 'selected' : ''}} value="Rollaway Bed">Rollaway bed</option>
                                    <option {{$hotelBed->extra_bed_type == "Bunk Bed" ? 'selected' : ''}} value="Bunk Bed">Bunk bed</option>
                                </select>
                            </div>

                            <!-- extra bed price -->
                            <div class="col-md-3 mb-3 extra_bed_price" style="display: none;">
                                <label for="extra_bed_price" class="form-label"><strong>Extra Bed
                                        Price</strong><span class="text-danger">*</span></label>
                                <input type="number" name="extra_bed_price" id="extra_bed_price"
                                    class="form-control" placeholder="Enter Price">
                            </div>

                            <!-- Adult Count -->
                            <div class="col-md-3 mb-3">
                                <label for="adult_count" class="form-label"><strong>Adults</strong><span class="text-danger">*</span></label>
                                <select id="adult_count" class="form-control" name="adult_count">
                                    <option value="">Select Adults</option>
                                    @foreach(range(1, $hotelBed->max_occupancy) as $i)
                                        <option value="{{ $i }}" {{ $i == $hotelBed->adult_count ? 'selected' : '' }}>{{ $i }}</option>
                                    @endforeach
                                </select>
                                @error('adult_count')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Child Count -->
                            <div class="col-md-3 mb-3">
                                <label for="child_count" class="form-label"><strong>Children</strong><span class="text-danger">*</span></label>
                                <select id="child_count" class="form-control" name="child_count">
                                    <option value="">Select Children</option>
                                    @foreach(range(0, $hotelBed->max_occupancy - $hotelBed->adult_count) as $i)
                                        <option value="{{ $i }}" {{ $i == $hotelBed->child_count ? 'selected' : '' }}>{{ $i }}</option>
                                    @endforeach
                                </select>
                                @error('child_count')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- baby cot -->
                            <div class="col-md-3 mb-3">
                                <label for="baby_cot" class="form-label"><strong>Baby
                                        Cot</strong><span class="text-danger">*</span></label>
                                <select name="baby_cot" id="baby_cot" class="form-control"
                                    onchange="toggleBabyCotPrice()">
                                    <option value="">Select One</option>
                                    <option {{ $hotelBed->baby_cot == 1 ? 'selected' : '' }} value="1">Yes
                                    </option>
                                    <option {{ $hotelBed->baby_cot == 0 ? 'selected' : '' }} value="0">No
                                    </option>
                                </select>
                            </div>

                            <!-- baby cot price -->
                            <div class="col-md-3 mb-3 baby_cot_price" style="display: none;">
                                <label for="baby_cot_price" class="form-label"><strong>Baby Cot
                                        Price</strong><span class="text-danger">*</span></label>
                                <input type="number" name="baby_cot_price" id="baby_cot_price"
                                    class="form-control" placeholder="Enter Price">
                            </div>
                            <hr>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3" id="force_child_count_container" style="display: none;">
                    <label for="force_child_count" class="form-label"><strong>Force Child Count</strong></label>
                    <select class="form-control" name="force_child_count" id="force_child_count">
                        @for ($i = 0; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ $hotelBed->force_child_count == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>

                <div class="form-check form-switch">
                    <label for="force_child" class="form-label"><strong>Force Child</strong></label>
                    <span style="color: red; font-weight: bold;">*</span>
                    <input {{ $hotelBed->force_child == 1 ? 'checked' : '' }} id="force_child" class="form-check-input" name="force_child" type="checkbox" value="1" onchange="toggleForceChildCount()">
                    <label class="form-check-label" for="force_child"></label>
                    @error('force_child')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Status -->
                <div class="form-check form-switch">
                    <label for="beds_status" class="form-label"><strong>Status</strong></label>
                    <span style="color: red; font-weight: bold;">*</span>
                    <input {{$hotelBed->is_active == 1 ? 'checked' : ''}} id="beds_status" class="form-check-input" name="beds_status" type="checkbox" id="beds_status"
                        value="1">
                    <label class="form-check-label"></label>
                    @error('beds_status')
                    <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary px-4">Update</button>

                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')

<!-- DataTable Scripts -->
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    function onBedTypeChange() {
        document.getElementById('extra_bed').value = "";
        toggleExtraBedField(); // Optional: Trigger extra bed field logic
    }
    function toggleExtraBedField() {
        const extraBedValue = document.getElementById('extra_bed').value;
        console.log("Extra bed changed to:", extraBedValue);
    }
</script>
<script>
    $(document).ready(function() {
        $('#example2').DataTable({
            "order": [
                [0, "asc"]
            ],
            lengthChange: false,
            buttons: ['copy', 'excel', 'pdf', 'print']
        });

        $('#example2').DataTable().buttons().container().appendTo('#example2_wrapper .col-md-6:eq(0)');
    });

    function setDeleteForm(action) {
        document.getElementById('deleteForm').action = action;
    }
</script>

</script>
<!-- baby cot price -->
<script>
    const bed = @json($hotelBed);
    // Function to toggle the visibility of the baby cot price field
    const babyCotDropdown = document.getElementById("baby_cot");
    const babyCotPriceField = document.querySelector(`.baby_cot_price`);
    const babyCotPriceInput = babyCotPriceField.querySelector('input');
    const toggleBabyCotPrice = () => {
        if (babyCotDropdown.value === "1") {
            babyCotPriceField.style.display = "block"; // Show price field if "Yes" is selected
            babyCotPriceInput.value = bed.baby_cot_price;
        } else {
            babyCotPriceField.style.display = "none"; // Hide price field if "No" or nothing is selected
        }
    };

    // Attach the function to the dropdown's change event
    document.addEventListener("DOMContentLoaded", () => {
        const babyCotDropdown = document.getElementById("baby_cot");
        if (babyCotDropdown) {
            babyCotDropdown.addEventListener("change", toggleBabyCotPrice);
        }
        if (bed.baby_cot == 1) {
            toggleBabyCotPrice();
            
        }
    });
</script>

<!-- extra bed -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const extraBedSelect = document.getElementById('extra_bed');
        const extraBedTypeDiv = document.querySelector('.extra_bed_type');
        const extraBedPriceDiv = document.querySelector('.extra_bed_price');
        const extraBedPriceInput = extraBedPriceDiv.querySelector('input');
        const bed = @json($hotelBed);

        function toggleExtraBedField() {


            if (extraBedSelect.value === "1") {
                extraBedTypeDiv.style.display = "block";
                extraBedPriceDiv.style.display = "block";
                extraBedPriceInput.value = bed.extra_bed_price;
            } else {
                extraBedTypeDiv.style.display = "none";
                extraBedPriceDiv.style.display = "none";
                document.getElementById('extra_bed_type').value = ""; // Clear the type field
                document.getElementById('extra_bed_price').value = ""; // Clear the price field
            }
        }

        // Call toggleExtraBedField when the page loads
        if (bed.extra_bed) {
            toggleExtraBedField();
        }

        // Attach the onchange event listener
        document.getElementById('extra_bed').addEventListener('change', toggleExtraBedField);
    });
</script>

<script>
$(document).ready(function() {
    $('#room_type').on('change', function() {
        const roomTypeId = $(this).val(); // Get selected room type ID

        if (roomTypeId) {
            $.ajax({
                url: '/get-no-of-rooms', // Your backend route
                type: 'GET',
                data: {
                    room_type_id: roomTypeId
                },
                success: function(response) {
                    console.log('Number of Rooms:', response);
                    // Enable the no_of_rooms dropdown
                    $('#no_of_rooms').prop('disabled', false);

                    // Populate the no_of_rooms dropdown
                    $('#no_of_rooms').empty().append(
                        '<option value="">Select No of Rooms</option>');
                    response.forEach(room => {
                        for (let i = 0; i <= room.no_of_room; i++) {
                            $('#no_of_rooms').append(
                                `<option value="${i}">${i}</option>`);
                        }

                    });
                },
                error: function(xhr) {
                    console.error('An error occurred:', xhr.responseText);
                }
            });
        } else {
            $('#no_of_rooms').prop('disabled', true).empty().append(
                '<option value="">Select Room Type First</option>');
        }
    });
});
</script>

<!-- Adult count, child count -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    attachOccupancyListeners("max-occupancy", "adult_count", "child_count");
});

const attachOccupancyListeners = (occupancyId, adultId, childId) => {

    const occupancyInput = document.getElementById(occupancyId);
    const adultDropdown = document.getElementById(adultId);
    const childDropdown = document.getElementById(childId);
    const bed = @json($hotelBed);
    const room = @json($room);
    if (bed.no_of_rooms > 0) {
        $('#no_of_rooms').empty().append('<option value="">Select No of Rooms</option>');
        for (let i = 0; i <= bed.no_of_rooms; i++) {
            const isSelected = (i === bed.no_of_rooms) ? 'selected' : ''; // If the current option is the highest room number, mark it as selected
            $('#no_of_rooms').append(
                `<option value="${i}" ${isSelected}>${i}</option>`
            );
        }
    }
};
</script>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Prepopulate from the server
        const maxOccupancyInput = document.getElementById("max-occupancy");
        const adultDropdown = document.getElementById("adult_count");
        const childDropdown = document.getElementById("child_count");

        const savedAdultCount = @json($hotelBed->adult_count); // Pre-saved from database
        const savedChildCount = @json($hotelBed->child_count); // Pre-saved from database
        const maxOccupancy = parseInt(maxOccupancyInput.value) || 0;

        // Function to populate adult and child dropdowns based on saved data
        const populateDropdowns = () => {
            // Reset adult and child dropdowns
            adultDropdown.innerHTML = `<option value="">Select Adults</option>`;
            childDropdown.innerHTML = `<option value="">Select Children</option>`;

            // Populate adult count options based on max occupancy and saved value
            if (savedAdultCount) {
                for (let i = 1; i <= maxOccupancy; i++) {
                    adultDropdown.innerHTML += `<option value="${i}" ${i === savedAdultCount ? 'selected' : ''}>${i}</option>`;
                }
            }

            // Populate child count options based on max occupancy and saved value
            if (savedChildCount !== null) {
                for (let i = 0; i <= maxOccupancy; i++) {
                    childDropdown.innerHTML += `<option value="${i}" ${i === savedChildCount ? 'selected' : ''}>${i}</option>`;
                }
            }

            // Enable adult and child dropdowns based on max occupancy
            if (maxOccupancy > 0) {
                adultDropdown.disabled = false;
                childDropdown.disabled = false;
            }
        };

        // Function to update options based on selected max occupancy
        const updateOptions = () => {
            const occupancy = parseInt(maxOccupancyInput.value) || 0;

            // Reset adult and child dropdowns
            adultDropdown.innerHTML = `<option value="">Select Adults</option>`;
            childDropdown.innerHTML = `<option value="">Select Children</option>`;

            if (occupancy > 0) {
                for (let i = 1; i <= occupancy; i++) {
                    adultDropdown.innerHTML += `<option value="${i}">${i}</option>`;
                }
                adultDropdown.disabled = false;
                updateChildOptions();
            } else {
                adultDropdown.disabled = true;
                childDropdown.disabled = true;
            }
        };

        // Function to update child options based on selected adults
        const updateChildOptions = () => {
            const occupancy = parseInt(maxOccupancyInput.value) || 0;
            const adults = parseInt(adultDropdown.value) || 0;
            const maxChildren = occupancy - adults;

            childDropdown.innerHTML = `<option value="">Select Children</option>`; // Reset options

            if (maxChildren > 0) {
                for (let i = 0; i <= maxChildren; i++) {
                    childDropdown.innerHTML += `<option value="${i}">${i}</option>`;
                }
                childDropdown.disabled = false;
            } else {
                childDropdown.disabled = true;
            }
        };

        // Initial population of adult and child count dropdowns based on saved data
        populateDropdowns();

        // Add event listeners to dynamically update options
        maxOccupancyInput.addEventListener("input", updateOptions);
        adultDropdown.addEventListener("change", updateChildOptions);
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Get the bed type select box, extra bed select box, and max occupancy input
        const bedTypeSelect = document.getElementById("bed_type");
        const maxOccupancyInput = document.getElementById("max-occupancy");
        const extraBedSelect = document.getElementById("extra_bed");
        const hotelIdInput = document.querySelector("input[name='hotel_id']");
        const preselectedBedType = bedTypeSelect.value;  // Get preselected bed type (for editing)
        
        // Function to update max occupancy based on bed type
        const fetchMaxOccupancy = (bedType, hotelId) => {
            if (!bedType || !hotelId) {
                maxOccupancyInput.value = '';
                return;
            }

            // Construct the URL with query parameters
            const url = `{{ route('bed.type.data') }}?bed_type=${encodeURIComponent(bedType)}&hotel_id=${encodeURIComponent(hotelId)}`;

            const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfTokenMeta ? csrfTokenMeta.content : '';
            // Make AJAX request to fetch max occupancy
            fetch(url, {
                method: "GET",  // Use GET method
                headers: csrfToken ? { "X-CSRF-TOKEN": csrfToken } : {}
            })
                .then(response => response.json())
                .then(data => {
                    if (data.total_count !== undefined) {
                        let maxOccupancy = data.total_count; // Set the max occupancy

                        // Add 1 to max occupancy if extra bed is selected
                        if (extraBedSelect.value == '1') {
                            maxOccupancy += 1; // Add 1 if extra bed is selected
                        }

                        maxOccupancyInput.value = maxOccupancy; // Set the updated max occupancy
                    } else {
                        maxOccupancyInput.value = ''; // Clear input if no data found
                    }
                })
                .catch(error => {
                    console.error("Error fetching max occupancy:", error);
                    maxOccupancyInput.value = ''; // Clear input on error
                });
        };

        // Event listener for bed type change
        bedTypeSelect.addEventListener("change", function () {
            const selectedBedType = this.value;
            const hotelId = hotelIdInput.value;
            fetchMaxOccupancy(selectedBedType, hotelId);
        });

        // Event listener for extra bed change
        extraBedSelect.addEventListener("change", function () {
            const selectedBedType = bedTypeSelect.value;
            const hotelId = hotelIdInput.value;
            fetchMaxOccupancy(selectedBedType, hotelId);
        });

        // If editing, fetch max occupancy for preselected bed type on page load
        if (preselectedBedType) {
            fetchMaxOccupancy(preselectedBedType, hotelIdInput.value);
        }

        // If there's already a value for max occupancy from previous selection, set it in the input
        const initialMaxOccupancy = maxOccupancyInput.value;
        if (initialMaxOccupancy) {
            maxOccupancyInput.value = initialMaxOccupancy;
        }

        // Ensure that the form submission includes the correct bed type
        document.getElementById("hotelForm").addEventListener("submit", function () {
            // Ensure that the bed type is correctly selected in the form
            const selectedBedType = bedTypeSelect.value;
            if (!selectedBedType) {
                alert("Please select a bed type before submitting.");
                return false;  // Prevent form submission if bed type is not selected
            }
        });
    });
</script>

<!-- Toggle Force Child Count -->
<script>
    function toggleForceChildCount() {
        const forceChildCheckbox = document.getElementById('force_child');
        const forceChildCountContainer = document.getElementById('force_child_count_container');
        
        if (forceChildCheckbox.checked) {
            forceChildCountContainer.style.display = 'block';
        } else {
            forceChildCountContainer.style.display = 'none';
        }
    }
    
    // Run on page load to set initial state
    document.addEventListener('DOMContentLoaded', function() {
        toggleForceChildCount();
    });
</script>
<!-- End Toggle Force Child Count -->
@endsection