@extends('layouts.layout')
@section('content')

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="fas fa-arrow-left"></i> New Quote</a></li>
                    <li class="breadcrumb-item"><a href="#">Trips</a></li>
                    <li class="breadcrumb-item active">{{ $packageData->trip_code }} • {{ $packageData->customer_name }} • {{ $packageData->source }} • {{ $packageData->agent }}</li>
                    <li class="breadcrumb-item active">Create Quote</li>
                </ol>
            </nav>
        </div>
        <button class="btn btn-warning">What's New</button>
    </div>

    <!-- Basic Details Section -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Basic Details</h5>
            <small class="text-muted">Please review basic details for this quote. You can edit these details to provide a quote with different configuration, without changing the trip details.</small>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label"><strong>DESTINATION</strong></label>
                    <input type="text" class="form-control" id="destination" value="{{ $packageData->destination }}" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><strong>START DATE</strong></label>
                    <input type="date" class="form-control" id="startDate" value="{{ $packageData->start_date }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label"><strong>DURATION</strong></label>
                    <input type="text" class="form-control" id="duration" value="{{ $packageData->duration }}" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><strong>PAX</strong></label>
                    <input type="text" class="form-control" id="pax" value="{{ $packageData->adults }} Adults" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><strong>Package Types/Categories: 1 Option</strong></label>
                    <button class="btn btn-outline-secondary btn-sm" onclick="editPackageTypes()">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button class="btn btn-primary" onclick="editBasicDetails()">Edit Basic Details</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hotels Section -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <div class="d-flex align-items-center">
                <i class="fas fa-hotel me-2"></i>
                <h5 class="mb-0">Hotels</h5>
            </div>
            <small>Please add hotels details (if included in package) with services provided for each hotels and the selling cost price.</small>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Tip: To speed up the process of adding multiple hotels, use <strong>Next Night</strong> or <strong>Duplicate</strong> actions.
            </div>
            
            <!-- Stay Nights Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Stay Nights</th>
                            <th>Hotel</th>
                            <th>Meal Plan</th>
                            <th>Room Type</th>
                            <th>Prices</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="hotelNightsContainer">
                        @for($night = 1; $night <= $packageData->nights; $night++)
                        @php
                            $date = \Carbon\Carbon::parse($packageData->start_date)->addDays($night - 1);
                            $dayName = $date->format('D');
                            $dateFormat = $date->format('j M');
                        @endphp
                        <tr data-night="{{ $night }}">
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input night-checkbox" type="checkbox" checked data-night="{{ $night }}">
                                    <label class="form-check-label">{{ $night }}{{ $night == 1 ? 'st' : ($night == 2 ? 'nd' : ($night == 3 ? 'rd' : 'th')) }} N ({{ $dayName }} {{ $dateFormat }})</label>
                                </div>
                            </td>
                            <td>
                                <select class="form-select hotel-select" data-night="{{ $night }}">
                                    @foreach($hotels as $hotel)
                                    <option value="{{ $hotel->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $hotel->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted hotel-description">{{ $hotels[0]->description }}</small>
                            </td>
                            <td>
                                <select class="form-select meal-select" data-night="{{ $night }}">
                                    @foreach($mealPlans as $meal)
                                    <option value="{{ $meal->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $meal->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select class="form-select room-select" data-night="{{ $night }}">
                                    @foreach($roomTypes as $room)
                                    <option value="{{ $room->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $room->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <div class="pricing-section">
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="form-label small">Date</label>
                                            <div class="price-item">
                                                <span class="date">{{ $dateFormat }}</span>
                                                <span class="day">{{ $date->format('l') }}</span>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Rate N/A</label>
                                            <div class="alert alert-warning p-1 text-center hotel-rate" data-night="{{ $night }}">
                                                <i class="fas fa-exclamation-triangle"></i> <strong class="rate-amount">0</strong>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Given N/A</label>
                                            <div class="price-given">SGD <span class="given-amount">N/A</span></div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group-vertical">
                                    <button class="btn btn-outline-primary btn-sm" onclick="duplicateHotelNight({{ $night }})">
                                        <i class="fas fa-copy"></i> Duplicate
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="removeHotelNight({{ $night }})">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            <!-- Room Configuration -->
            <div class="row g-3 mb-3">
                <div class="col-md-2">
                    <label class="form-label">Pax/room (Max/FB)</label>
                    <input type="number" class="form-control" id="paxPerRoom" value="{{ $packageData->pax_per_room }}" min="1">
                </div>
                <div class="col-md-2">
                    <label class="form-label">No. of rooms</label>
                    <input type="number" class="form-control" id="numberOfRooms" value="{{ $packageData->rooms }}" min="1">
                </div>
                <div class="col-md-2">
                    <label class="form-label">AWEB</label>
                    <input type="number" class="form-control" id="aweb" value="0" min="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label">CWEB</label>
                    <input type="number" class="form-control" id="cweb" value="0" min="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label">CNB</label>
                    <input type="number" class="form-control" id="cnb" value="0" min="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Comp Child</label>
                    <input type="text" class="form-control" id="compChild" value="Upto 5y (0C)">
                </div>
            </div>

            <button class="btn btn-outline-primary me-2" onclick="addSimilarHotels()">
                <i class="fas fa-plus"></i> Add Similar Hotels
            </button>

            <!-- Special Inclusions -->
            <div class="mt-4">
                <h6>Any special inclusions in hotels</h6>
                <p class="text-muted">Add any extra services for hotels e.g. special dinner, honeymoon cake etc.</p>
                <button class="btn btn-outline-secondary" onclick="addHotelService()">
                    <i class="fas fa-plus"></i> Add Service
                </button>
                <div id="hotelSpecialServices"></div>
            </div>

            <div class="mt-3 text-end">
                <span class="badge bg-warning" id="accommodationTotal">Accommodation's Total: N/A</span>
            </div>
        </div>
    </div>

    <!-- Transportation and Activities Section -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <div class="d-flex align-items-center">
                <i class="fas fa-car me-2"></i>
                <h5 class="mb-0">Transports and Activities</h5>
            </div>
            <small>Please add the transportation services and Activities (if included) details and the selling cost price for each service.</small>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Tip: To speed up the process of adding multiple services, use <strong>Next Day</strong> or <strong>Duplicate</strong> actions.
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="sameCabType">
                <label class="form-check-label" for="sameCabType">
                    Same Cab Type for All
                </label>
            </div>

            <!-- Days Transportation -->
            <div id="transportDaysContainer">
                @for($day = 1; $day <= $packageData->days; $day++)
                @php
                    $date = \Carbon\Carbon::parse($packageData->start_date)->addDays($day - 1);
                    $dayName = $date->format('D');
                    $dateFormat = $date->format('j M');
                @endphp
                <div class="transport-day-section mb-4" data-day="{{ $day }}">
                    <h6 class="bg-light p-2 border">
                        <input type="checkbox" class="form-check-input me-2 day-checkbox" checked data-day="{{ $day }}">
                        {{ $day }}{{ $day == 1 ? 'st' : ($day == 2 ? 'nd' : ($day == 3 ? 'rd' : 'th')) }} Day ({{ $dayName }} {{ $dateFormat }})
                    </h6>
                    
                    <!-- Default Transport Services for each day -->
                    <div class="services-container" data-day="{{ $day }}">
                        @if($day == 1)
                        <!-- Airport Transfer -->
                        <div class="transport-service border rounded p-3 mb-3" data-service-id="1">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0"><i class="fas fa-car"></i> Transport Service</h6>
                            <div class="btn-group">
                                    <button class="btn btn-outline-secondary btn-sm" onclick="addServiceComments(this)">
                                    <i class="fas fa-comment"></i> Comments
                                </button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="refreshService(this)">
                                    <i class="fas fa-sync"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Service Locations</label>
                                    <input type="text" class="form-control service-location" value="Changi Airport to Singapore Hotel">
                            </div>
                            <div class="col-md-6">
                                    <label class="form-label">Transportation and Prices - {{ $date->format('l, j M') }}</label>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Service Type</label>
                                    <select class="form-select service-type">
                                        @foreach($serviceTypes as $serviceType)
                                        <option value="{{ $serviceType->id }}" {{ $serviceType->name == 'Arrival - PVT Transfer' ? 'selected' : '' }}>{{ $serviceType->name }}</option>
                                        @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Type</label>
                                    <select class="form-select vehicle-type">
                                        @foreach($vehicleTypes as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $vehicle->name }}</option>
                                        @endforeach
                                    </select>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Qty.</label>
                                    <input type="number" class="form-control service-qty" value="2" min="1">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Rate</label>
                                <div class="input-group">
                                    <span class="input-group-text">SGD</span>
                                        <input type="number" class="form-control service-rate" value="35" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Given</label>
                                <div class="pricing-display">
                                        <strong class="given-amount">40</strong>
                                        <small class="text-muted d-block total-line">x 2 = 80</small>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                    <button class="btn btn-outline-danger btn-sm" onclick="removeService(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>

                            <button class="btn btn-outline-primary btn-sm mt-2" onclick="addMoreTransport(this)">
                            <i class="fas fa-plus"></i> Add More
                        </button>
                    </div>

                        <!-- Night Safari Activity -->
                        <div class="activity-service border rounded p-3 mb-3 bg-light" data-service-id="2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0"><i class="fas fa-ticket-alt"></i> Activity/Ticket</h6>
                            <div class="btn-group">
                                    <button class="btn btn-outline-secondary btn-sm" onclick="addServiceComments(this)">
                                    <i class="fas fa-comment"></i> Comments
                                </button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="refreshService(this)">
                                    <i class="fas fa-sync"></i>
                                </button>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Name</label>
                                    <select class="form-select activity-name">
                                        @foreach($activities as $activity)
                                        <option value="{{ $activity->id }}" {{ $activity->name == 'Night Safari Admission + Tram Ride' ? 'selected' : '' }} 
                                                data-adult-price="{{ $activity->adult_price }}" 
                                                data-child-price="{{ $activity->child_price }}"
                                                data-duration="{{ $activity->duration }}">{{ $activity->name }}</option>
                                        @endforeach
                                    </select>
                            </div>
                            <div class="col-md-6">
                                    <label class="form-label">Tickets and Prices - {{ $date->format('l, j M') }}</label>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Ticket/Package Type</label>
                                    <select class="form-select ticket-type">
                                        <option value="tickets" selected>Tickets</option>
                                        <option value="package">Package</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Slot</label>
                                    <input type="time" class="form-control activity-slot" value="19:00">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Duration</label>
                                    <input type="text" class="form-control activity-duration" value="3 Hours" readonly>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Type</label>
                                    <select class="form-select pax-type">
                                        <option value="adult" selected>Adult</option>
                                        <option value="child">Child</option>
                                        <option value="senior">Senior</option>
                                    </select>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Qty.</label>
                                    <input type="number" class="form-control activity-qty" value="{{ $packageData->adults }}" min="1">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Rate</label>
                                <div class="input-group">
                                    <span class="input-group-text">SGD</span>
                                        <input type="number" class="form-control activity-rate" value="43" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Given</label>
                                <div class="pricing-display">
                                        <strong class="activity-given-amount">43</strong>
                                        <small class="text-muted d-block activity-total-line">x 16 = 688</small>
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                    <button class="btn btn-outline-danger btn-sm" onclick="removeService(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        </div>
                        @endif
                    </div>

                    <div class="d-flex gap-2 mb-3">
                        <button class="btn btn-outline-primary btn-sm" onclick="addTransportService({{ $day }})">
                            <i class="fas fa-plus"></i> Transport Service
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="addActivityTicket({{ $day }})">
                            <i class="fas fa-plus"></i> Activity/Ticket
                        </button>
                    </div>

                    <div class="text-end">
                        <button class="btn btn-link text-danger" onclick="removeDay({{ $day }})">
                            <i class="fas fa-times"></i> Remove
                        </button>
                    </div>
                </div>
                @endfor
            </div>

            <button class="btn btn-outline-primary" onclick="addMoreServices()">
                <i class="fas fa-plus"></i> Add More Services
            </button>

            <!-- Special Services -->
            <div class="mt-4">
                <h6>Any extra or sightseeing in transportation</h6>
                <p class="text-muted">Add any extra services like any side destination trip that is provided only per customer request</p>
                <button class="btn btn-outline-secondary" onclick="addExtraService()">
                    <i class="fas fa-plus"></i> Add Service
                </button>
                <div id="extraTransportServices"></div>
            </div>

            <div class="mt-3 text-end">
                <div class="pricing-summary bg-warning p-2 rounded" id="transportTotal">
                    <strong>Total: SGD <span id="transportTotalAmount">3,044</span></strong><br>
                    <small>Cabs = SGD <span id="cabsTotal">660</span> • Activity/Tickets = SGD <span id="activitiesTotal">2,384</span></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Flight Details Section -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <div class="d-flex align-items-center">
                <i class="fas fa-plane me-2"></i>
                <h5 class="mb-0">Flight Details</h5>
            </div>
            <small>Please provide flight details for this quote if included.</small>
        </div>
        <div class="card-body">
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" onclick="addRoundTrip()">
                    <i class="fas fa-plus"></i> Add Round trip
                </button>
                <button class="btn btn-outline-secondary" onclick="addOneWay()">
                    <i class="fas fa-plus"></i> Add One way
                </button>
            </div>
            <div id="flightDetailsContainer"></div>
        </div>
    </div>

    <!-- Special Services Section -->
    <div class="card mb-4">
        <div class="card-header bg-warning">
            <div class="d-flex align-items-center">
                <i class="fas fa-star me-2"></i>
                <h5 class="mb-0 text-dark">Any other special service for this trip</h5>
            </div>
            <small class="text-dark">Add any extra services like off road dinner, side trekking etc that are associated with overall trip package</small>
        </div>
        <div class="card-body">
            <button class="btn btn-outline-secondary" onclick="addSpecialService()">
                <i class="fas fa-plus"></i> Add Service
            </button>
            <div id="specialServicesContainer"></div>
        </div>
    </div>

    <!-- Internal Comments -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Any internal comments for this quote <span class="text-muted">(optional)</span></h6>
        </div>
        <div class="card-body">
            <textarea class="form-control" id="internalComments" rows="3" placeholder="Any internal comments regarding customer request or anything special about this quote or anything else..."></textarea>
        </div>
    </div>

    <!-- Summary Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Summary</h5>
            <small class="text-muted">Please review the quote's data before creating.</small>
        </div>
        <div class="card-body" id="summarySection">
            <!-- Summary content will be populated by JavaScript -->
        </div>
    </div>

    <!-- Per-Person Pricing Section -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="includePerPersonPrice" checked>
                <label class="form-check-label" for="includePerPersonPrice">
                    <strong>Include Per-Person Price</strong>
                </label>
            </div>
            <div class="mt-2">
                <label class="form-label">Selling Currency</label>
                <select class="form-select w-auto d-inline" id="sellingCurrency">
                    @foreach($currencies as $currency)
                    <option value="{{ $currency->code }}" {{ $currency->code == 'SGD' ? 'selected' : '' }}>{{ $currency->code }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-body" id="perPersonPricingSection">
            <!-- Per-person pricing will be populated by JavaScript -->
        </div>
    </div>

    <!-- Markup and Pricing Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Set Markup, Tax and Rounding</h6>
        </div>
        <div class="card-body" id="markupPricingSection">
            <!-- Markup pricing will be populated by JavaScript -->
        </div>
    </div>

    <!-- Final Preview Section -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <div class="d-flex align-items-center">
                <i class="fas fa-eye me-2"></i>
                <h5 class="mb-0">Preview Final Package Price</h5>
            </div>
            <small>Here are the final prices for this quote.</small>
        </div>
        <div class="card-body" id="finalPreviewSection">
            <!-- Final preview will be populated by JavaScript -->
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex justify-content-between mb-4">
        <button class="btn btn-secondary" onclick="cancelQuote()">Cancel</button>
        <div>
            <button class="btn btn-outline-primary me-2" onclick="saveDraft()">Save Draft</button>
            <button class="btn btn-primary btn-lg" onclick="saveQuote()">Save Quote</button>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.pricing-section {
    background: #f8f9fa;
    border-radius: 4px;
    padding: 0.5rem;
}

.price-item {
    background: white;
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    margin-bottom: 0.25rem;
}

.price-item .date {
    font-weight: bold;
    display: block;
}

.price-item .day {
    font-size: 0.85em;
    color: #6c757d;
}

.price-given {
    background: #e9ecef;
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    text-align: center;
    font-size: 0.9em;
}

.pricing-display {
    background: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    text-align: center;
}

.transport-day-section {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.transport-service,
.activity-service {
    background: #ffffff;
}

.activity-service {
    background: #f8f9fa !important;
}

.pricing-summary {
    font-size: 1.1em;
}

.pricing-breakdown {
    background: #fff3cd;
    padding: 0.75rem;
    border-radius: 4px;
    border: 1px solid #ffeaa7;
}

.day-summary {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}

.day-summary .d-flex {
    padding: 0.25rem 0;
    border-bottom: 1px solid #e9ecef;
}

.day-summary .d-flex:last-child {
    border-bottom: none;
}

.service-comments {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 4px;
    padding: 0.5rem;
    margin-top: 0.5rem;
    display: none;
}

.loading-spinner {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    border: 2px solid #dee2e6;
    border-radius: 50%;
    border-top-color: #007bff;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .row.g-3 > div {
        margin-bottom: 1rem;
    }
    
    .btn-group-vertical {
        width: 100%;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
}

.fade-in {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Global variables
    let serviceCounter = 100;
    let dayCounter = {{ $packageData->days }};
    let nightCounter = {{ $packageData->nights }};
    
    // Initialize the form
    initializeForm();
    
    // Event listeners
    setupEventListeners();
    
    // Calculate initial totals
    calculateAllTotals();
    
    function initializeForm() {
        console.log('Initializing form...');
        
        // Update summary section
        updateSummarySection();
        
        // Update per-person pricing
        updatePerPersonPricing();
        
        // Update markup pricing
        updateMarkupPricing();
        
        // Update final preview
        updateFinalPreview();
    }
    
    function setupEventListeners() {
        // Hotel selection change
        $(document).on('change', '.hotel-select', function() {
            const night = $(this).data('night');
            updateHotelPricing(night);
            calculateAllTotals();
        });
        
        // Service rate/qty changes
        $(document).on('input', '.service-rate, .service-qty', function() {
            updateServicePricing($(this).closest('.transport-service'));
            calculateAllTotals();
        });
        
        // Activity rate/qty changes
        $(document).on('input', '.activity-rate, .activity-qty', function() {
            updateActivityPricing($(this).closest('.activity-service'));
            calculateAllTotals();
        });
        
        // Activity selection change
        $(document).on('change', '.activity-name', function() {
            const selected = $(this).find(':selected');
            const container = $(this).closest('.activity-service');
            
            container.find('.activity-rate').val(selected.data('adult-price'));
            container.find('.activity-duration').val(selected.data('duration'));
            
            updateActivityPricing(container);
            calculateAllTotals();
        });
        
        // Currency change
        $(document).on('change', '#sellingCurrency', function() {
            updateCurrencyDisplays();
            calculateAllTotals();
        });
        
        // Checkbox changes
        $(document).on('change', '.night-checkbox, .day-checkbox', function() {
            calculateAllTotals();
        });
        
        // Room configuration changes
        $(document).on('input', '#paxPerRoom, #numberOfRooms, #aweb, #cweb, #cnb', function() {
            calculateAllTotals();
        });
    }
    
    function updateHotelPricing(night) {
        const hotelSelect = $(`.hotel-select[data-night="${night}"]`);
        const hotelId = hotelSelect.val();
        const date = $('#startDate').val();
        
        // Show loading
        const rateContainer = $(`.hotel-rate[data-night="${night}"] .rate-amount`);
        rateContainer.html('<div class="loading-spinner"></div>');
        
        // AJAX call to get hotel pricing
        $.ajax({
            url: '/custom-packages/hotel-pricing',
            method: 'POST',
            data: {
                hotel_id: hotelId,
                date: date,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    rateContainer.text(response.pricing.rate);
                    
                    // Update given price
                    const givenContainer = $(`.hotel-rate[data-night="${night}"]`).closest('td').find('.given-amount');
                    givenContainer.text(response.pricing.rate > 0 ? response.pricing.rate : 'N/A');
                    
                    // Update hotel description
                    const description = hotelSelect.closest('td').find('.hotel-description');
                    description.text(getHotelDescription(hotelId));
                }
            },
            error: function() {
                rateContainer.text('0');
            }
        });
    }
    
    function updateServicePricing(serviceContainer) {
        const rate = parseFloat(serviceContainer.find('.service-rate').val()) || 0;
        const qty = parseFloat(serviceContainer.find('.service-qty').val()) || 0;
        const total = rate * qty;
        
        serviceContainer.find('.given-amount').text(rate);
        serviceContainer.find('.total-line').text(`x ${qty} = ${total}`);
    }
    
    function updateActivityPricing(activityContainer) {
        const rate = parseFloat(activityContainer.find('.activity-rate').val()) || 0;
        const qty = parseFloat(activityContainer.find('.activity-qty').val()) || 0;
        const total = rate * qty;
        
        activityContainer.find('.activity-given-amount').text(rate);
        activityContainer.find('.activity-total-line').text(`x ${qty} = ${total}`);
    }
    
    function calculateAllTotals() {
        let hotelTotal = 0;
        let cabsTotal = 0;
        let activitiesTotal = 0;
        
        // Calculate hotel totals
        $('.night-checkbox:checked').each(function() {
            const night = $(this).data('night');
            const rate = parseFloat($(`.hotel-rate[data-night="${night}"] .rate-amount`).text()) || 0;
            const rooms = parseFloat($('#numberOfRooms').val()) || 0;
            hotelTotal += rate * rooms;
        });
        
        // Calculate transport totals
        $('.day-checkbox:checked').each(function() {
            const day = $(this).data('day');
            
            // Transport services
            $(`.transport-day-section[data-day="${day}"] .transport-service`).each(function() {
                const rate = parseFloat($(this).find('.service-rate').val()) || 0;
                const qty = parseFloat($(this).find('.service-qty').val()) || 0;
            cabsTotal += rate * qty;
        });
        
            // Activities
            $(`.transport-day-section[data-day="${day}"] .activity-service`).each(function() {
                const rate = parseFloat($(this).find('.activity-rate').val()) || 0;
                const qty = parseFloat($(this).find('.activity-qty').val()) || 0;
            activitiesTotal += rate * qty;
            });
        });
        
        // Update displays
        $('#accommodationTotal').html(`Accommodation's Total: SGD ${hotelTotal.toLocaleString()}`);
        $('#cabsTotal').text(cabsTotal);
        $('#activitiesTotal').text(activitiesTotal);
        $('#transportTotalAmount').text(cabsTotal + activitiesTotal);
        
        // Update other sections
        updateSummarySection();
        updatePerPersonPricing();
        updateMarkupPricing();
        updateFinalPreview();
    }
    
    function updateSummarySection() {
        // This would be populated with actual summary data
        const summaryHtml = `
            <div class="row mb-4">
                <div class="col-md-3">
                    <strong>START DATE</strong><br>
                    ${new Date($('#startDate').val()).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}
                </div>
                <div class="col-md-3">
                    <strong>DURATION</strong><br>
                    ${$('#duration').val()}
                </div>
                <div class="col-md-3">
                    <strong>PAX</strong><br>
                    ${$('#pax').val()}
                </div>
            </div>
            <!-- Additional summary content would go here -->
        `;
        
        $('#summarySection').html(summaryHtml);
    }
    
    function updatePerPersonPricing() {
        const totalCost = parseFloat($('#transportTotalAmount').text()) || 0;
        const pax = {{ $packageData->adults }};
        const perPersonCost = totalCost / pax;
        
        const html = `
            <h6>Per-Person Settings</h6>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>For</th>
                            <th>Price (SGD)</th>
                            <th>Segregation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Person (Double Sharing) x ${pax} Pax</td>
                            <td><strong>${perPersonCost.toFixed(2)}</strong></td>
                            <td>Cabs: ${(parseFloat($('#cabsTotal').text()) / pax).toFixed(2)} Activities: ${(parseFloat($('#activitiesTotal').text()) / pax).toFixed(2)}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `;
        
        $('#perPersonPricingSection').html(html);
    }
    
    function updateMarkupPricing() {
        const totalCost = parseFloat($('#transportTotalAmount').text()) || 0;
        const pax = {{ $packageData->adults }};
        const perPersonCost = totalCost / pax;
        const markup = 10; // Default 10%
        const perPersonWithMarkup = perPersonCost * (1 + markup/100);
        const roundedPerPerson = Math.round(perPersonWithMarkup);
        const finalTotal = roundedPerPerson * pax;
        
        const html = `
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Cost Price (SGD)</th>
                            <th>Markup %</th>
                            <th></th>
                            <th>Total (SGD)</th>
                            <th>Round</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>(Person (Double Sharing))</strong><br>${pax} Pax</td>
                            <td><strong>${perPersonCost.toFixed(2)}</strong><br>x ${pax}</td>
                            <td><input type="number" class="form-control markup-input" value="${markup}" min="0" step="0.1"></td>
                            <td>-</td>
                            <td><strong>${perPersonWithMarkup.toFixed(2)}</strong><br>SGD</td>
                            <td><strong>${roundedPerPerson}</strong><br>x ${pax}</td>
                        </tr>
                        <tr class="table-secondary">
                            <td><strong>Total</strong></td>
                            <td><strong>${totalCost.toLocaleString()}</strong></td>
                            <td><strong>${(totalCost * markup / 100).toFixed(0)}</strong> <i class="fas fa-edit"></i></td>
                            <td>-</td>
                            <td><strong>${(totalCost * (1 + markup/100)).toFixed(0)}</strong><br>SGD</td>
                            <td><strong>${finalTotal.toLocaleString()}</strong><br>SGD</td>
                        </tr>
                    </tbody>
                </table>
                    </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <label class="form-label">Any internal comments regarding selling price (optional)</label>
                    <textarea class="form-control" rows="3"></textarea>
                                </div>
                <div class="col-md-6">
                    <label class="form-label">Remarks for Agent/Customer (optional)</label>
                    <textarea class="form-control" rows="3" placeholder="Any special remarks for this customer."></textarea>
                    <small class="text-muted">These remarks will be shared with the customer.</small>
                            </div>
                                </div>
        `;
        
        $('#markupPricingSection').html(html);
    }
    
    function updateFinalPreview() {
        const totalCost = parseFloat($('#transportTotalAmount').text()) || 0;
        const pax = {{ $packageData->adults }};
        const markup = 10;
        const finalPerPerson = Math.round((totalCost / pax) * (1 + markup/100));
        const finalTotal = finalPerPerson * pax;
        
        const html = `
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Per Pax</th>
                            <th>Qnty.</th>
                            <th>Sub Total</th>
            </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Person (Double Sharing) x ${pax} Pax</td>
                            <td><strong>${finalPerPerson}</strong></td>
                            <td>${pax}</td>
                            <td><strong>${finalTotal.toLocaleString()}</strong></td>
                        </tr>
                        <tr class="table-light">
                            <td colspan="3"><strong>Total</strong></td>
                            <td><strong>SGD ${finalTotal.toLocaleString()}</strong></td>
                        </tr>
                        <tr>
                            <td colspan="4"><small>Tax - (Excluded)</small></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `;
        
        $('#finalPreviewSection').html(html);
    }
    
    function getHotelDescription(hotelId) {
        const hotels = @json($hotels);
        const hotel = hotels.find(h => h.id == hotelId);
        return hotel ? hotel.description : '';
    }
    
    // Markup input change
    $(document).on('input', '.markup-input', function() {
        updateMarkupPricing();
        updateFinalPreview();
    });
});

// Global functions for button actions
function editBasicDetails() {
    alert('Edit Basic Details functionality would open a modal or form');
}

function editPackageTypes() {
    alert('Edit Package Types functionality would open a modal');
}

function duplicateHotelNight(night) {
    const originalRow = $(`tr[data-night="${night}"]`);
    const newNight = nightCounter + 1;
    nightCounter++;
    
    const clonedRow = originalRow.clone();
    clonedRow.attr('data-night', newNight);
    clonedRow.find('[data-night]').attr('data-night', newNight);
    clonedRow.find('.form-check-label').text(`${newNight}${newNight == 1 ? 'st' : (newNight == 2 ? 'nd' : (newNight == 3 ? 'rd' : 'th'))} N (Duplicated)`);
    
    originalRow.after(clonedRow);
    clonedRow.addClass('fade-in');
    
    calculateAllTotals();
}

function removeHotelNight(night) {
    if ($('#hotelNightsContainer tr').length > 1) {
        $(`tr[data-night="${night}"]`).remove();
        calculateAllTotals();
    } else {
        alert('Cannot remove the last hotel night');
    }
}

function addSimilarHotels() {
    alert('Add Similar Hotels functionality would allow bulk addition');
}

function addHotelService() {
    const serviceHtml = `
        <div class="border rounded p-3 mt-3 hotel-special-service fade-in">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Service Name</label>
                    <input type="text" class="form-control" placeholder="e.g. Special Dinner">
                        </div>
                <div class="col-md-3">
                    <label class="form-label">Price (SGD)</label>
                    <input type="number" class="form-control" min="0" step="0.01">
                        </div>
                        <div class="col-md-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control" value="1" min="1">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-outline-danger" onclick="$(this).closest('.hotel-special-service').remove(); calculateAllTotals();">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                </div>
            </div>
        `;
        
    $('#hotelSpecialServices').append(serviceHtml);
}

function addTransportService(day) {
    serviceCounter++;
    const date = new Date($('#startDate').val());
    date.setDate(date.getDate() + day - 1);
    const dateStr = date.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'short' });
    
    const serviceHtml = `
        <div class="transport-service border rounded p-3 mb-3 fade-in" data-service-id="${serviceCounter}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0"><i class="fas fa-car"></i> Transport Service</h6>
                    <div class="btn-group">
                    <button class="btn btn-outline-secondary btn-sm" onclick="addServiceComments(this)">
                            <i class="fas fa-comment"></i> Comments
                        </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="refreshService(this)">
                            <i class="fas fa-sync"></i>
                        </button>
                    </div>
                </div>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Service Locations</label>
                    <input type="text" class="form-control service-location" placeholder="Enter service location">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Transportation and Prices - ${dateStr}</label>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Service Type</label>
                    <select class="form-select service-type">
                        @foreach($serviceTypes as $serviceType)
                        <option value="{{ $serviceType->id }}">{{ $serviceType->name }}</option>
                        @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                    <select class="form-select vehicle-type">
                        @foreach($vehicleTypes as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->name }}</option>
                        @endforeach
                    </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Qty.</label>
                    <input type="number" class="form-control service-qty" value="2" min="1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Rate</label>
                        <div class="input-group">
                            <span class="input-group-text">SGD</span>
                        <input type="number" class="form-control service-rate" value="0" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Given</label>
                        <div class="pricing-display">
                            <strong class="given-amount">0</strong>
                            <small class="text-muted d-block total-line">x 2 = 0</small>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-outline-danger btn-sm" onclick="removeService(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
    $(`.transport-day-section[data-day="${day}"] .services-container`).append(serviceHtml);
}

function addActivityTicket(day) {
    serviceCounter++;
    const date = new Date($('#startDate').val());
    date.setDate(date.getDate() + day - 1);
    const dateStr = date.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'short' });
    
    const activityHtml = `
        <div class="activity-service border rounded p-3 mb-3 bg-light fade-in" data-service-id="${serviceCounter}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0"><i class="fas fa-ticket-alt"></i> Activity/Ticket</h6>
                    <div class="btn-group">
                    <button class="btn btn-outline-secondary btn-sm" onclick="addServiceComments(this)">
                            <i class="fas fa-comment"></i> Comments
                        </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="refreshService(this)">
                            <i class="fas fa-sync"></i>
                        </button>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                    <select class="form-select activity-name">
                        @foreach($activities as $activity)
                        <option value="{{ $activity->id }}" 
                                data-adult-price="{{ $activity->adult_price }}" 
                                data-child-price="{{ $activity->child_price }}"
                                data-duration="{{ $activity->duration }}">{{ $activity->name }}</option>
                        @endforeach
                    </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tickets and Prices - ${dateStr}</label>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Ticket/Package Type</label>
                    <select class="form-select ticket-type">
                        <option value="tickets">Tickets</option>
                        <option value="package">Package</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Slot</label>
                    <input type="time" class="form-control activity-slot">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Duration</label>
                    <input type="text" class="form-control activity-duration" readonly>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Type</label>
                    <select class="form-select pax-type">
                        <option value="adult">Adult</option>
                        <option value="child">Child</option>
                        <option value="senior">Senior</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Qty.</label>
                    <input type="number" class="form-control activity-qty" value="{{ $packageData->adults }}" min="1">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Rate</label>
                        <div class="input-group">
                            <span class="input-group-text">SGD</span>
                        <input type="number" class="form-control activity-rate" value="0" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Given</label>
                        <div class="pricing-display">
                            <strong class="activity-given-amount">0</strong>
                        <small class="text-muted d-block activity-total-line">x {{ $packageData->adults }} = 0</small>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                    <button class="btn btn-outline-danger btn-sm" onclick="removeService(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
    $(`.transport-day-section[data-day="${day}"] .services-container`).append(activityHtml);
}

function removeService(button) {
    $(button).closest('.transport-service, .activity-service').remove();
    calculateAllTotals();
}

function addServiceComments(button) {
    const container = $(button).closest('.transport-service, .activity-service');
    let commentsDiv = container.find('.service-comments');
    
    if (commentsDiv.length === 0) {
        const commentsHtml = `
            <div class="service-comments mt-3">
                <label class="form-label">Service Comments</label>
                <textarea class="form-control" rows="2" placeholder="Add any special comments for this service..."></textarea>
            </div>
        `;
        container.append(commentsHtml);
        commentsDiv = container.find('.service-comments');
    }
    
    commentsDiv.slideToggle();
}

function refreshService(button) {
    const container = $(button).closest('.transport-service, .activity-service');
    const icon = $(button).find('i');
    
    icon.addClass('fa-spin');
    
    setTimeout(() => {
        icon.removeClass('fa-spin');
        alert('Service refreshed successfully');
    }, 1000);
}

function removeDay(day) {
    if ($('.transport-day-section').length > 1) {
        $(`.transport-day-section[data-day="${day}"]`).remove();
        calculateAllTotals();
    } else {
        alert('Cannot remove the last day');
    }
}

function addMoreServices() {
    dayCounter++;
    const date = new Date($('#startDate').val());
    date.setDate(date.getDate() + dayCounter - 1);
    const dayName = date.toLocaleDateString('en-GB', { weekday: 'short' });
    const dateFormat = date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
    
    const newDayHtml = `
        <div class="transport-day-section mb-4 fade-in" data-day="${dayCounter}">
            <h6 class="bg-light p-2 border">
                <input type="checkbox" class="form-check-input me-2 day-checkbox" checked data-day="${dayCounter}">
                ${dayCounter}${dayCounter == 1 ? 'st' : (dayCounter == 2 ? 'nd' : (dayCounter == 3 ? 'rd' : 'th'))} Day (${dayName} ${dateFormat})
            </h6>
            
            <div class="services-container" data-day="${dayCounter}">
                <!-- Empty initially -->
            </div>

            <div class="d-flex gap-2 mb-3">
                <button class="btn btn-outline-primary btn-sm" onclick="addTransportService(${dayCounter})">
                    <i class="fas fa-plus"></i> Transport Service
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="addActivityTicket(${dayCounter})">
                    <i class="fas fa-plus"></i> Activity/Ticket
                </button>
            </div>

            <div class="text-end">
                <button class="btn btn-link text-danger" onclick="removeDay(${dayCounter})">
                    <i class="fas fa-times"></i> Remove
                </button>
            </div>
        </div>
    `;
    
    $('#transportDaysContainer').append(newDayHtml);
}

function addExtraService() {
    const serviceHtml = `
        <div class="border rounded p-3 mt-3 extra-service fade-in">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Service Name</label>
                    <input type="text" class="form-control" placeholder="e.g. Off Road Dinner">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Price (SGD)</label>
                    <input type="number" class="form-control" min="0" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control" value="1" min="1">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-outline-danger" onclick="$(this).closest('.extra-service').remove();">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    $('#extraTransportServices').append(serviceHtml);
}

function addRoundTrip() {
    alert('Add Round Trip functionality would open flight booking form');
}

function addOneWay() {
    alert('Add One Way functionality would open flight booking form');
}

function addSpecialService() {
    const serviceHtml = `
        <div class="border rounded p-3 mt-3 special-service fade-in">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Service Name</label>
                    <input type="text" class="form-control" placeholder="e.g. Side Trekking">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Price (SGD)</label>
                    <input type="number" class="form-control" min="0" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-control" placeholder="Service description">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-outline-danger" onclick="$(this).closest('.special-service').remove();">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    $('#specialServicesContainer').append(serviceHtml);
}

function cancelQuote() {
    if (confirm('Are you sure you want to cancel? All changes will be lost.')) {
        window.location.href = '/custom-packages';
    }
}

function saveDraft() {
    alert('Save Draft functionality - would save without validation');
}

function saveQuote() {
    // Validate form
    const validation = validateQuoteForm();
    
    if (validation.isValid) {
        alert('Quote saved successfully! (Demo mode - no actual saving)');
    } else {
        alert('Please fix the following errors:\n' + validation.errors.join('\n'));
    }
}

function validateQuoteForm() {
    const errors = [];
    
    // Check if at least one hotel night is selected
    if ($('.night-checkbox:checked').length === 0) {
        errors.push('Please select at least one hotel night');
    }
    
    // Check if at least one day is selected
    if ($('.day-checkbox:checked').length === 0) {
        errors.push('Please select at least one day for transportation');
    }
    
    return {
        isValid: errors.length === 0,
        errors: errors
    };
}

// Setup CSRF token for all AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Initialize tooltips and popovers if needed
$(function () {
    $('[data-bs-toggle="tooltip"]').tooltip();
    $('[data-bs-toggle="popover"]').popover();
    
    // Auto-save draft every 30 seconds
    setInterval(function() {
        if ($('#autoSaveDraft').is(':checked')) {
            autoSaveDraft();
        }
    }, 30000);
    
    // Initialize location autocomplete
    initializeLocationAutocomplete();
    
    // Initialize currency conversion
    initializeCurrencyConversion();
});

// Location autocomplete functionality
function initializeLocationAutocomplete() {
    $(document).on('input', '.service-location', function() {
        const input = $(this);
        const query = input.val();
        
        if (query.length >= 2) {
            $.ajax({
                url: '/custom-packages/location-suggestions',
                method: 'POST',
                data: { query: query },
                success: function(response) {
                    if (response.success && response.suggestions.length > 0) {
                        showLocationSuggestions(input, response.suggestions);
                    }
                }
            });
        }
    });
}

function showLocationSuggestions(input, suggestions) {
    let dropdown = input.next('.location-dropdown');
    if (dropdown.length === 0) {
        dropdown = $('<div class="location-dropdown list-group position-absolute" style="z-index: 1000; width: 100%;"></div>');
        input.after(dropdown);
    }
    
    dropdown.empty();
    suggestions.forEach(function(location) {
        const item = $(`<a href="#" class="list-group-item list-group-item-action">${location}</a>`);
        item.on('click', function(e) {
            e.preventDefault();
            input.val(location);
            dropdown.remove();
        });
        dropdown.append(item);
    });
}

// Currency conversion functionality
function initializeCurrencyConversion() {
    $(document).on('change', '#sellingCurrency', function() {
        const currency = $(this).val();
        updateCurrencyRates(currency);
    });
}

function updateCurrencyRates(currency) {
    $.ajax({
        url: '/custom-packages/currency-rates',
        method: 'POST',
        data: { base: currency },
        success: function(response) {
            if (response.success) {
                // Update all currency displays
                $('.input-group-text').text(currency);
                
                // Recalculate totals with new currency
                calculateAllTotals();
            }
        }
    });
}

// Auto-save draft functionality
function autoSaveDraft() {
    const formData = collectFormData();
    
    $.ajax({
        url: '/custom-packages/save-draft',
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                showNotification('Draft saved automatically', 'success');
            }
        },
        error: function() {
            showNotification('Auto-save failed', 'warning');
        }
    });
}

// Collect all form data for saving
function collectFormData() {
    const data = {
        destination: $('#destination').val(),
        start_date: $('#startDate').val(),
        duration: $('#duration').val(),
        pax: $('#pax').val(),
        internal_comments: $('#internalComments').val(),
        hotels: [],
        transport_services: [],
        activities: [],
        special_services: []
    };
    
    // Collect hotel data
    $('.night-checkbox:checked').each(function() {
        const night = $(this).data('night');
        const hotelData = {
            night: night,
            hotel_id: $(`.hotel-select[data-night="${night}"]`).val(),
            meal_plan: $(`.meal-select[data-night="${night}"]`).val(),
            room_type: $(`.room-select[data-night="${night}"]`).val(),
            selected: true
        };
        data.hotels.push(hotelData);
    });
    
    // Collect transport services
    $('.transport-service').each(function() {
        const service = {
            location: $(this).find('.service-location').val(),
            service_type: $(this).find('.service-type').val(),
            vehicle_type: $(this).find('.vehicle-type').val(),
            rate: $(this).find('.service-rate').val(),
            quantity: $(this).find('.service-qty').val()
        };
        data.transport_services.push(service);
    });
    
    // Collect activities
    $('.activity-service').each(function() {
        const activity = {
            activity_id: $(this).find('.activity-name').val(),
            slot: $(this).find('.activity-slot').val(),
            rate: $(this).find('.activity-rate').val(),
            quantity: $(this).find('.activity-qty').val(),
            pax_type: $(this).find('.pax-type').val()
        };
        data.activities.push(activity);
    });
    
    return data;
}

// Show notification function
function showNotification(message, type = 'info') {
    const notification = $(`
        <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(notification);
    
    setTimeout(function() {
        notification.fadeOut(function() {
            $(this).remove();
        });
    }, 3000);
}

// Enhanced vehicle type change handler
$(document).on('change', '.vehicle-type', function() {
    const vehicleId = $(this).val();
    const serviceContainer = $(this).closest('.transport-service');
    const date = $('#startDate').val();
    
    $.ajax({
        url: '/custom-packages/vehicle-pricing',
        method: 'POST',
        data: {
            vehicle_id: vehicleId,
            date: date,
            service_type: serviceContainer.find('.service-type').val()
        },
        success: function(response) {
            if (response.success && response.pricing.available) {
                serviceContainer.find('.service-rate').val(response.pricing.rate);
                updateServicePricing(serviceContainer);
                calculateAllTotals();
            }
        }
    });
});

// Real-time availability checking
function checkServiceAvailability(serviceContainer) {
    const serviceId = serviceContainer.data('service-id');
    const date = $('#startDate').val();
    const quantity = serviceContainer.find('.service-qty, .activity-qty').val();
    
    $.ajax({
        url: '/custom-packages/check-availability',
        method: 'POST',
        data: {
            service_id: serviceId,
            date: date,
            quantity: quantity
        },
        success: function(response) {
            if (response.success) {
                const indicator = serviceContainer.find('.availability-indicator');
                if (indicator.length === 0) {
                    const newIndicator = $(`
                        <small class="availability-indicator ${response.available ? 'text-success' : 'text-danger'}">
                            <i class="fas fa-${response.available ? 'check-circle' : 'times-circle'}"></i>
                            ${response.message}
                        </small>
                    `);
                    serviceContainer.find('.row:first').append(newIndicator);
                }
            }
        }
    });
}

// Add auto-save checkbox to the form
$(document).ready(function() {
    const autoSaveHtml = `
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="autoSaveDraft" checked>
            <label class="form-check-label" for="autoSaveDraft">
                <i class="fas fa-save"></i> Auto-save draft every 30 seconds
            </label>
        </div>
    `;
    
    $('#internalComments').closest('.card').after(autoSaveHtml);
});

// Enhanced save quote with real validation
function saveQuote() {
    const formData = collectFormData();
    
    // Show loading
    const saveBtn = $('.btn-primary.btn-lg');
    const originalText = saveBtn.text();
    saveBtn.html('<span class="loading-spinner"></span> Saving...').prop('disabled', true);
    
    $.ajax({
        url: '/custom-packages/save',
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                showNotification(`Quote ${response.quote_id} saved successfully!`, 'success');
                
                // Show additional options
                const optionsHtml = `
                    <div class="alert alert-success mt-3">
                        <h6><i class="fas fa-check-circle"></i> Quote Saved Successfully!</h6>
                        <p>Quote ID: <strong>${response.quote_id}</strong></p>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="exportToPDF('${response.quote_id}')">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="sendQuoteEmail('${response.quote_id}')">
                                <i class="fas fa-envelope"></i> Send Email
                            </button>
                            <button class="btn btn-outline-info btn-sm" onclick="window.print()">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>
                `;
                
                $('.d-flex.justify-content-between.mb-4').after(optionsHtml);
                
                setTimeout(function() {
                    if (response.redirect_url) {
                        window.location.href = response.redirect_url;
                    }
                }, 3000);
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            if (response && response.errors) {
                showNotification('Please fix the following errors:\n' + response.errors.join('\n'), 'danger');
            } else {
                showNotification('Error saving quote. Please try again.', 'danger');
            }
        },
        complete: function() {
            saveBtn.text(originalText).prop('disabled', false);
        }
    });
}

function exportToPDF(quoteId) {
    $.ajax({
        url: '/custom-packages/export-pdf',
        method: 'POST',
        data: { quote_id: quoteId },
        success: function(response) {
            if (response.success) {
                window.open(response.pdf_url, '_blank');
                showNotification('PDF generated successfully', 'success');
            }
        }
    });
}

function sendQuoteEmail(quoteId) {
    const email = prompt('Enter email address:');
    if (email) {
        $.ajax({
            url: '/custom-packages/send-email',
            method: 'POST',
            data: { quote_id: quoteId, email: email },
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                }
            }
        });
    }
}
</script>
@endpush
