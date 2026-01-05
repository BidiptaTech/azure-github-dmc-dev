@extends('layouts.layout')
@section('content')


<!-- Start of the form -->
<div class="page-content">
    <div class="page-container">
        <div class="row justify-content-center">
            <div class="col-lg-12 col-md-10 col-sm-12">
                <div class="card">
                    <div class="card-header px-4 py-3" style="background-color: #e0bbf7; color: white;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Add New Room Type</h5>
                            <!-- Back Button -->
                            <a href="javascript:history.back()" class="btn btn-sm btn-outline-light">
                                <i class="mdi mdi-arrow-left"></i> Back
                            </a>
                        </div>
                        
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('roomType.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf 

                            <div class="mb-3">
                                <label for="hotelName" class="form-label"><strong>Hotel Name</strong></label>
                                <select id="hotelName" name="hotel_id" class="form-control" required>
                                    <option value="">Search for a hotel...</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label"><strong>Room Type Name</strong></label>
                                <input type="text" id="roomType" name="roomType" placeholder="Enter Room Type Name" class="form-control" required>
                            </div>

                            <!-- Facilities Selection (loaded dynamically) -->
                            <div class="mb-3">
                                <label for="facilities" class="form-label"><strong>Select Facilities</strong></label>
                                <div id="facilities-container" class="d-flex flex-wrap">
                                    <!-- Facilities will be appended here dynamically -->
                                    
                                </div>
                                <p class="text-muted" id="no-facilities-msg">Enter a valid Hotel ID to load facilities</p>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label"><strong>Description</strong></label>
                                <input type="text" id="description" name="description" placeholder="Enter Room Description" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="room_status" class="form-label"><strong>Status</strong></label>
                                <select id="room_status" name="room_status" class="form-control" required>
                                    <option value="">Select Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            
                            <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- End of the form -->

@endsection

@section('scripts')

<script>
    $(document).ready(function () {
        $('#hotelName').select2({
            placeholder: 'Search for a hotel...', // Adds the placeholder
            ajax: {
                url: '{{ route("hotels.search") }}', // Backend route for fetching hotel data
                type: 'GET',
                dataType: 'json',
                delay: 20, // Debounce to reduce server load
                data: function (params) {
                    return {
                        query: params.term // Send the user's input as 'query'
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.map(function (hotel) {
                            return {
                                id: hotel.id, // Value submitted with the form
                                text: `${hotel.name} - ${hotel.city || 'No Location'}` // Text displayed
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 1 // Allow search from 1 character
        });
    });
</script>

<!-- Script For Fetching Facilities  -->

<script>
    $(document).ready(function () {
        
        $('#hotelName').on('change', function () {
            
            let hotelId = $(this).val(); // Get the selected hotel's ID
            let facilitiesContainer = $('#facilities-container');
            let noFacilitiesMsg = $('#no-facilities-msg');

            // Clear previous facilities
            facilitiesContainer.empty();
            noFacilitiesMsg.show();

            if (hotelId) {
                // Make AJAX call to fetch facilities
                
                $.ajax({
                    url: `/hotels/${hotelId}/facilities`,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        
                        if (data.success && data.facilities.length > 0) {
                            
                            noFacilitiesMsg.hide();
                            // Append facilities checkboxes
                            data.facilities.forEach(function (facility) {
                                let checkbox = `
                                    <div class="form-check form-check-inline me-3">
                                        <input 
                                            class="form-check-input"
                                            type="checkbox" 
                                            name="facilities[]" 
                                            id="facility_${facility}" 
                                            value="${facility}"
                                        >
                                        <label class="form-check-label" for="facility_${facility}">
                                            ${facility}
                                        </label>
                                    </div>
                                `;
                                facilitiesContainer.append(checkbox);
                            });
                        } else {
                            noFacilitiesMsg.html(`
                            No facilities found for this hotel.
                            <div>
                                <a href="/hotels/${hotelId}/edit" class="text-primary">
                                    Add Facilities
                                </a>
                            </div>
                        `).show();
                        }
                    },
                    error: function () {
                        noFacilitiesMsg.text('Error fetching facilities. Please try again.').show();
                    }
                });
            }
        });
    });
</script>

@endsection
