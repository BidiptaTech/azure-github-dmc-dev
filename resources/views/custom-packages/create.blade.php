@extends('layouts.layout')
@section('content')
<div class="container-fluid py-4">
    <!-- Progress Bar / Stepper -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="badge bg-primary">Step 1</span>
                        <span class="fw-bold ms-2">Create Custom Package</span>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary btn-sm">Save & Exit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Package Info Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-1">🎁 Let's Book Your Holiday</h5>
                        <div class="small">Fill in the details below to create your custom package.</div>
                    </div>
                    <div>
                        <button class="btn btn-light btn-sm">+ Save New Custom Package</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hotel Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary mb-3">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <span><i class="ri-hotel-line me-2"></i>Hotel Services</span>
                    <button id="addHotelBtn" type="button" class="btn btn-light btn-sm">+ Add Hotel</button>
                </div>
                <div class="card-body" id="hotelsContainer">
                    <!-- Dynamic hotel items will appear here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Attraction Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-danger mb-3">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <span><i class="ri-camera-3-line me-2"></i>Attraction Services</span>
                    <button id="addAttractionBtn" type="button" class="btn btn-light btn-sm">+ Add Attraction</button>
                </div>
                <div class="card-body" id="attractionsContainer">
                    <!-- Dynamic attraction items will appear here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Restaurant Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-success mb-3">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <span><i class="ri-restaurant-2-line me-2"></i>Restaurant Services</span>
                    <button id="addRestaurantBtn" type="button" class="btn btn-light btn-sm">+ Add Restaurant</button>
                </div>
                <div class="card-body" id="restaurantsContainer">
                    <!-- Dynamic restaurant items will appear here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Transport Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-danger mb-3">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <span><i class="ri-steering-2-line me-2"></i>Transport Services</span>
                    <button id="addTransportBtn" type="button" class="btn btn-light btn-sm">+ Add Transport</button>
                </div>
                <div class="card-body" id="transportsContainer">
                    <!-- Dynamic transport items will appear here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Guide Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary mb-3">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <span><i class="ri-user-line me-2"></i>Guide Services</span>
                    <button id="addGuideBtn" type="button" class="btn btn-light btn-sm">+ Add Guide</button>
                </div>
                <div class="card-body" id="guidesContainer">
                    <!-- Dynamic guide items will appear here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light fw-bold">Customer Details</div>
                <div class="card-body">
                    <form>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" placeholder="Enter full name">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" placeholder="Enter email">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" placeholder="Enter phone">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Country</label>
                                <input type="text" class="form-control" placeholder="Enter country">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">State</label>
                                <input type="text" class="form-control" placeholder="Enter state">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Zip</label>
                                <input type="text" class="form-control" placeholder="Enter zip">
                            </div>
                        </div>
                        <div class="text-end">
                            <button class="btn btn-primary">Submit Custom Package</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer / Call to Action -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card text-center bg-light">
                <div class="card-body">
                    <h6 class="mb-2">Want to save and manage this package later?</h6>
                    <button class="btn btn-outline-primary">Save as Draft</button>
                </div>
            </div>
        </div>
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
});
</script>
@endpush
