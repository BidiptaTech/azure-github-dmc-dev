@extends('layouts.layout')
@section('content')

<!-- Add Hotel Modal -->
{{-- Tour Basic Info --}}
<div class="mb-4 p-4 bg-light border rounded shadow-sm">
    <h4 class="mb-3">🧳 Tour Package: Bali Adventure</h4>
    <div class="row g-3">
        <div class="col-md-3"><strong>From:</strong> Delhi</div>
        <div class="col-md-3"><strong>To:</strong> Bali</div>
        <div class="col-md-3"><strong>Departure:</strong> 2025-08-01</div>
        <div class="col-md-3"><strong>Agent:</strong> John Doe</div>
    </div>
</div>

{{-- Hotel Section --}}
<div class="card border-success mb-4 shadow-sm">
    <div class="card-header bg-success text-white d-flex justify-content-between">
        <h5 class="mb-0">🏨 Hotel Services</h5>
        <button class="btn btn-sm btn-light">+ Add Hotel</button>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-2">
            <div class="col-md-4">
                <label>Hotel Name</label>
                <input type="text" class="form-control" value="Hotel Grand Palace">
            </div>
            <div class="col-md-4">
                <label>Check-In</label>
                <input type="date" class="form-control" value="2025-08-01">
            </div>
            <div class="col-md-4">
                <label>Check-Out</label>
                <input type="date" class="form-control" value="2025-08-04">
            </div>
        </div>
    </div>
</div>

{{-- Flight Section --}}
<div class="card border-primary mb-4 shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between">
        <h5 class="mb-0">✈️ Flight Transport Services</h5>
        <button class="btn btn-sm btn-light">+ Add Flight</button>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-2">
            <div class="col-md-3">
                <label>Airline</label>
                <input type="text" class="form-control" value="Indigo">
            </div>
            <div class="col-md-3">
                <label>From</label>
                <input type="text" class="form-control" value="Delhi">
            </div>
            <div class="col-md-3">
                <label>To</label>
                <input type="text" class="form-control" value="Bali">
            </div>
            <div class="col-md-3">
                <label>Departure</label>
                <input type="datetime-local" class="form-control" value="2025-08-01T09:00">
            </div>
        </div>
    </div>
</div>

{{-- Guide Services --}}
<div class="card border-info mb-4 shadow-sm">
    <div class="card-header bg-info text-white d-flex justify-content-between">
        <h5 class="mb-0">🧍‍♂️ Guide Services</h5>
        <button class="btn btn-sm btn-light">+ Add Guide</button>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-2">
            <div class="col-md-4">
                <label>Guide Name</label>
                <input type="text" class="form-control" value="Mr. Komang">
            </div>
            <div class="col-md-4">
                <label>Start Date</label>
                <input type="date" class="form-control" value="2025-08-02">
            </div>
            <div class="col-md-4">
                <label>End Date</label>
                <input type="date" class="form-control" value="2025-08-05">
            </div>
        </div>
    </div>
</div>

{{-- Visa Services --}}
<div class="card border-danger mb-4 shadow-sm">
    <div class="card-header bg-danger text-white d-flex justify-content-between">
        <h5 class="mb-0">🛂 Visa Services</h5>
        <button class="btn btn-sm btn-light">+ Add Visa</button>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-2">
            <div class="col-md-4">
                <label>Visa Type</label>
                <input type="text" class="form-control" value="Tourist">
            </div>
            <div class="col-md-4">
                <label>Applied On</label>
                <input type="date" class="form-control" value="2025-07-25">
            </div>
            <div class="col-md-4">
                <label>Status</label>
                <select class="form-control">
                    <option selected>Approved</option>
                    <option>Pending</option>
                    <option>Rejected</option>
                </select>
            </div>
        </div>
    </div>
</div>

{{-- Notes --}}
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-light">
        <h5 class="mb-0">📝 Notes</h5>
    </div>
    <div class="card-body">
        <textarea class="form-control" rows="4">Client requested sea-view hotel rooms.</textarea>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function() {
    // Add Hotel
    $('#addHotelBtn').on('click', function() {
        $('#hotelsContainer').append(`
            <div class="mb-3 p-3 border rounded bg-light hotel-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Hotel Name</strong> <span class="badge bg-success ms-2">Confirmed</span>
                        <div class="small text-muted">Check-in: 2024-07-01 | Check-out: 2024-07-05</div>
                    </div>
                    <button class="btn btn-outline-danger btn-sm remove-hotel">Remove</button>
                </div>
            </div>
        `);
    });

    // Remove Hotel
    $(document).on('click', '.remove-hotel', function() {
        $(this).closest('.hotel-item').remove();
    });

    // Add Attraction
    $('#addAttractionBtn').on('click', function() {
        $('#attractionsContainer').append(`
            <div class="mb-3 p-3 border rounded bg-light attraction-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Attraction Name</strong> <span class="badge bg-warning ms-2">Pending</span>
                        <div class="small text-muted">Date: 2024-07-02</div>
                    </div>
                    <button class="btn btn-outline-danger btn-sm remove-attraction">Remove</button>
                </div>
            </div>
        `);
    });
    $(document).on('click', '.remove-attraction', function() {
        $(this).closest('.attraction-item').remove();
    });

    // Add Restaurant
    $('#addRestaurantBtn').on('click', function() {
        $('#restaurantsContainer').append(`
            <div class="mb-3 p-3 border rounded bg-light restaurant-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Restaurant Name</strong> <span class="badge bg-info ms-2">Reserved</span>
                        <div class="small text-muted">Date: 2024-07-03</div>
                    </div>
                    <button class="btn btn-outline-danger btn-sm remove-restaurant">Remove</button>
                </div>
            </div>
        `);
    });
    $(document).on('click', '.remove-restaurant', function() {
        $(this).closest('.restaurant-item').remove();
    });

    // Add Transport
    $('#addTransportBtn').on('click', function() {
        $('#transportsContainer').append(`
            <div class="mb-3 p-3 border rounded bg-light transport-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Transport Type</strong> <span class="badge bg-secondary ms-2">Scheduled</span>
                        <div class="small text-muted">Pickup: 2024-07-04</div>
                    </div>
                    <button class="btn btn-outline-danger btn-sm remove-transport">Remove</button>
                </div>
            </div>
        `);
    });
    $(document).on('click', '.remove-transport', function() {
        $(this).closest('.transport-item').remove();
    });

    // Add Guide
    $('#addGuideBtn').on('click', function() {
        $('#guidesContainer').append(`
            <div class="mb-3 p-3 border rounded bg-light guide-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Guide Name</strong> <span class="badge bg-primary ms-2">Assigned</span>
                        <div class="small text-muted">Date: 2024-07-05</div>
                    </div>
                    <button class="btn btn-outline-danger btn-sm remove-guide">Remove</button>
                </div>
            </div>
        `);
    });
    $(document).on('click', '.remove-guide', function() {
        $(this).closest('.guide-item').remove();
    });

    $('#hotelForm').on('submit', function(e) {
        e.preventDefault();
        let hotelName = $('#hotelName option:selected').text();
        let checkIn = $('#checkIn').val();
        let checkOut = $('#checkOut').val();
        $('#hotelsContainer').append(`
            <div class="mb-3 p-3 border rounded bg-light hotel-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${hotelName}</strong>
                        <div class="small text-muted">Check-in: ${checkIn} | Check-out: ${checkOut}</div>
                    </div>
                    <button class="btn btn-outline-danger btn-sm remove-hotel">Remove</button>
                </div>
            </div>
        `);
        $('#addHotelModal').modal('hide');
        this.reset();
    });
});
</script>
@endpush
