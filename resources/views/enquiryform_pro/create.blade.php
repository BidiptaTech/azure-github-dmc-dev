@extends('layouts.layout')
@section('title', 'Enquiry Pro')
@extends('layouts.datatablecss')
@section('css')
<style>
    /* Hide footer on Enquiry Pro page */
    footer.footer {
        display: none !important;
    }

    /* Main Container - Full Height */
    .enquiry-pro-container {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 70px);
        overflow: hidden;
        padding: 0;
        margin: 0;
    }

    /* Fixed Top Header (Red Box) - Compact */
    .enquiry-pro-header {
        position: sticky;
        top: 0;
        z-index: 100;
        background: #fff;
        border-bottom: 3px solid #dc3545;
        padding: 8px 12px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    /* Navigation Tabs - Compact */
    .nav-tabs-custom {
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 8px;
    }

    .nav-tabs-custom .nav-link {
        border: none;
        color: #495057;
        font-weight: 500;
        padding: 6px 18px;
        margin-right: 3px;
        border-radius: 0;
        font-size: 13px;
        border-bottom: 3px solid transparent;
        line-height: 1.3;
    }

    .nav-tabs-custom .nav-link.active {
        color: #0d6efd;
        border-bottom: 3px solid #0d6efd;
        background: transparent;
        font-weight: 600;
    }

    .status-badge {
        background: #28a745;
        color: white;
        padding: 4px 12px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 11px;
        line-height: 1.3;
    }

    /* Customer Details Section - Compact */
    .customer-details {
        background: #fff9e6;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 11px;
        border: 1px solid #ffe69c;
    }

    .customer-details .row {
        margin: 0;
    }

    .customer-details .col-md-12 {
        padding: 0;
    }

    .detail-label {
        font-size: 11px;
        font-weight: 500;
        margin: 0;
        white-space: nowrap;
    }

    .customer-details input[type="radio"] {
        width: 14px;
        height: 14px;
        margin-right: 3px;
    }

    .customer-details label {
        font-size: 11px;
        margin: 0;
        display: flex;
        align-items: center;
    }

    .customer-details .form-control-sm,
    .customer-details .form-select-sm {
        height: 26px;
        padding: 3px 6px;
        font-size: 11px;
    }

    /* Scrollable Middle Content */
    .enquiry-pro-content {
        flex: 1;
        overflow-y: auto;
        padding: 8px 12px;
        background: #f8f9fa;
    }

    /* Section Styling - Compact */
    .section-card {
        background: white;
        border-radius: 3px;
        margin-bottom: 5px;
        border: 1px solid #dee2e6;
        overflow: hidden;
    }

    .section-header {
        background: #6c757d;
        color: white;
        padding: 8px 12px;
        font-weight: 600;
        font-size: 13px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        line-height: 1.3;
    }

    .section-body {
        padding: 0;
        max-height: 160px; /* 4 rows max (header + 4 data rows) */
        overflow-y: auto;
        overflow-x: auto;
    }

    /* Table Styling - Compact */
    .table-custom {
        width: 100%;
        font-size: 10px;
        margin: 0;
    }

    .table-custom th {
        background: #e9ecef;
        font-weight: 600;
        padding: 4px 6px !important;
        border-bottom: 1px solid #dee2e6;
        white-space: nowrap;
        font-size: 11px;
        line-height: 1.3;
    }

    .table-custom thead tr th {
        padding: 4px 6px !important;
    }

    .table-custom td {
        padding: 4px 6px !important;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
        font-size: 11px;
        line-height: 1.3;
    }

    .table-custom tbody tr {
        height: 30px;
    }

    .table-custom input[type="number"],
    .table-custom input[type="text"] {
        width: 55px;
        padding: 1px 3px;
        border: 1px solid #ced4da;
        border-radius: 2px;
        font-size: 10px;
        height: 20px;
    }

    .table-custom input[type="checkbox"] {
        width: 13px;
        height: 13px;
        cursor: pointer;
    }

    /* Fixed Bottom Action Bar (Red Box) - Compact */
    .enquiry-pro-footer {
        position: sticky;
        bottom: 0;
        z-index: 100;
        background: #fff;
        border-top: 3px solid #dc3545;
        padding: 10px 12px;
        box-shadow: 0 -2px 6px rgba(0,0,0,0.1);
    }

    /* Charges Section inside Red Box */
    .charges-section {
        background: #fff9e6;
        border: 1px solid #ffe69c;
        border-radius: 4px;
        padding: 8px 12px;
        margin-bottom: 10px;
    }

    .charges-section label {
        display: inline-flex;
        align-items: center;
        font-size: 11px;
    }

    .action-buttons {
        display: flex;
        gap: 4px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .action-buttons .btn {
        padding: 6px 14px;
        font-weight: 500;
        border-radius: 4px;
        font-size: 12px;
        line-height: 1.4;
    }

    .btn-xs {
        padding: 4px 10px !important;
        font-size: 11px !important;
        line-height: 1.2 !important;
    }

    /* Form Controls - Compact */
    .form-control-sm, .form-select-sm {
        font-size: 10px;
        padding: 3px 5px;
        padding-block: 4px;
        height: 24px;
        line-height: 1.2;
    }

    .form-label {
        font-size: 10px;
        margin-bottom: 2px;
        font-weight: 600;
        line-height: 1.2;
    }

    .form-check-label {
        font-size: 10px;
        line-height: 1.2;
    }

    .form-check-input {
        width: 14px;
        height: 14px;
        margin-top: 0;
    }

    /* Custom Scrollbar */
    .section-body::-webkit-scrollbar,
    .enquiry-pro-content::-webkit-scrollbar {
        width: 5px;
        height: 5px;
    }

    .section-body::-webkit-scrollbar-track,
    .enquiry-pro-content::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .section-body::-webkit-scrollbar-thumb,
    .enquiry-pro-content::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .section-body::-webkit-scrollbar-thumb:hover,
    .enquiry-pro-content::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Remove excess spacing */
    .row {
        margin: 0;
    }

    .col-md-2, .col-md-3, .col-md-6 {
        padding: 0 4px;
    }

    /* Empty section placeholder */
    .empty-section {
        padding: 10px;
        text-align: center;
        color: #6c757d;
        font-size: 10px;
    }

    /* Additional spacing reductions */
    .nav-item {
        margin-bottom: 0;
    }

    .alert {
        padding: 5px 8px;
        margin-bottom: 0;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .enquiry-pro-container {
            height: calc(100vh - 60px);
        }
        
        .section-body {
            max-height: 200px;
        }
    }
</style>
@endsection

@section('content')
<div class="enquiry-pro-container">
    
    <!-- Fixed Top Header (Red Box) -->
    <div class="enquiry-pro-header">
        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs nav-tabs-custom">
            <li class="nav-item">
                <a class="nav-link" href="#">Itinerary</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="#">Quotation</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">FOC</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Invoice</a>
            </li>
            <li class="nav-item ms-auto">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="costSheet">
                    <label class="form-check-label" for="costSheet">Switch to Cost Sheet</label>
                </div>
            </li>
            <li class="nav-item ms-3">
                <span class="status-badge">STATUS: QUOTATION</span>
            </li>
        </ul>

        <!-- Customer Details -->
        <div class="customer-details">
            <!-- Row 1 -->
            <div class="row g-2 mb-2">
                <div class="col-md-12 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <strong style="font-size: 12px;">{{ $user->name }}</strong>
                        <label><input type="radio" name="type" checked> Pax</label>
                        <label><input type="radio" name="type"> Group</label>
                        <select class="form-select form-select-sm" id="salutationSelect" style="width: 80px;">
                            <option value="Mr">Mr</option>
                            <option value="Mrs">Mrs</option>
                            <option value="Ms">Ms</option>
                            <option value="Dr">Dr</option>
                            <option value="Prof">Prof</option>
                            <option value="Other">Other</option>
                        </select>
                        <input type="text" class="form-control form-control-sm" value="To Be Advised" style="width: 120px;">
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="detail-label">Agency:</span>
                        <select class="form-select form-select-sm" id="agencySelect" style="width: 180px;" onchange="loadAgentsByAgency()">
                            <option value="">-- Select Agency --</option>
                            @foreach($agencies as $agency)
                                <option value="{{ $agency->agency_id }}">{{ $agency->agency_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="detail-label">Agent:</span>
                        <select class="form-select form-select-sm" id="agentSelect" style="width: 180px;" disabled>
                            <option value="">-- Select Agency First --</option>
                        </select>
                    </div>
                    
                    <div class="d-flex align-items-center gap-2">
                        <span class="detail-label">Quotation - Ref No:</span>
                        <input type="text" class="form-control form-control-sm" value="382056/IDS/INGURNAN" style="width: 150px;">
                    </div>
                </div>
            </div>
            
            <!-- Row 2 -->
            <div class="row g-2">
                <div class="col-md-12 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="detail-label">Adults:</span>
                        <input type="number" class="form-control form-control-sm" value="2" style="width: 60px;">
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="detail-label">Child:</span>
                        <input type="number" class="form-control form-control-sm" value="1" style="width: 60px;">
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="detail-label">Infant:</span>
                        <input type="number" class="form-control form-control-sm" value="0" style="width: 60px;">
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="detail-label">Destination:</span>
                        <select class="form-select form-select-sm" id="destinationSelect" style="width: 200px;">
                            <option value="">Select Destination</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->name }}" {{ ($destination ?? '') == $country->name ? 'selected' : '' }}>{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scrollable Middle Content -->
    <div class="enquiry-pro-content">
        
        <!-- Book Travel Section -->
        <div class="section-card">
            <div class="section-header">
                <span>Book Travel</span>
                <div>
                    <button class="btn btn-sm btn-light btn-xs">+ Event</button>
                    <button class="btn btn-sm btn-light btn-xs ms-1">- Event</button>
                </div>
            </div>
            <div class="section-body">
                <table class="table table-custom table-hover">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Route</th>
                            <th>Return</th>
                            <th>Adults Qty</th>
                            <th>Cost/Pax</th>
                            <th>Sell/Pax</th>
                            <th>Child Qty</th>
                            <th>Cost/Pax</th>
                            <th>Sell/Pax</th>
                            <th>Transfer</th>
                            <th>Vehicle</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>09 Dec '25 00:00</td>
                            <td>SIN - KUL</td>
                            <td>NA</td>
                            <td><input type="number" value="2"></td>
                            <td><input type="number" value="35.00"></td>
                            <td><input type="number" value="35.00"></td>
                            <td><input type="number" value="1"></td>
                            <td><input type="number" value="35.00"></td>
                            <td><input type="number" value="35.00"></td>
                            <td>Yes</td>
                            <td>Combi</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Accommodation Section -->
        <div class="section-card">
            <div class="section-header">
                <span>Accommodation</span>
                <div>
                    <button class="btn btn-sm btn-light btn-xs" onclick="openAccommodationModal()">+ Event</button>
                    <button class="btn btn-sm btn-light btn-xs ms-1" onclick="removeSelectedAccommodation()">- Event</button>
                </div>
            </div>
            <div class="section-body">
                <table class="table table-custom table-hover" id="accommodationTable" style="display: none;">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllAccommodation"></th>
                            <th>Hotel</th>
                            <th>Check In Date</th>
                            <th>Check Out Date</th>
                            <th>No. of Nights</th>
                            <th>No. of Rooms</th>
                            <th>Adults per Rm</th>
                            <th>Extra Bed</th>
                            <th>Child w/o Bed</th>
                            <th>Meal Plan</th>
                            <th>Supplement</th>
                        </tr>
                    </thead>
                    <tbody id="accommodationTableBody">
                        <!-- Hotels will be added here -->
                    </tbody>
                </table>
                <div class="empty-section" id="emptyAccommodationMessage">No accommodation added yet</div>
            </div>
        </div>

        <!-- Book Tours Section -->
        <div class="section-card">
            <div class="section-header">
                <span>Book Tours</span>
                <div>
                    <button class="btn btn-sm btn-light btn-xs">+ Event</button>
                    <button class="btn btn-sm btn-light btn-xs ms-1">- Event</button>
                </div>
            </div>
            <div class="section-body">
                <table class="table table-custom table-hover">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Tour Name</th>
                            <th>PTE</th>
                            <th>Adults</th>
                            <th>Cost/Pax</th>
                            <th>Sell/Pax</th>
                            <th>Child</th>
                            <th>Cost/Pax</th>
                            <th>Sell/Pax</th>
                            <th>Guide</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>04 Dec '25 17:30</td>
                            <td>Night Safari</td>
                            <td><input type="checkbox" checked></td>
                            <td><input type="number" value="2"></td>
                            <td><input type="number" value="44.00"></td>
                            <td><input type="number" value="44.00"></td>
                            <td><input type="number" value="1"></td>
                            <td><input type="number" value="31.00"></td>
                            <td><input type="number" value="31.00"></td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>05 Dec '25 09:00</td>
                            <td>Singapore Zoo</td>
                            <td><input type="checkbox" checked></td>
                            <td><input type="number" value="2"></td>
                            <td><input type="number" value="50.00"></td>
                            <td><input type="number" value="50.00"></td>
                            <td><input type="number" value="1"></td>
                            <td><input type="number" value="35.00"></td>
                            <td><input type="number" value="35.00"></td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>05 Dec '25 11:00</td>
                            <td>Museum Of Ice Cream</td>
                            <td><input type="checkbox" checked></td>
                            <td><input type="number" value="2"></td>
                            <td><input type="number" value="42.00"></td>
                            <td><input type="number" value="42.00"></td>
                            <td><input type="number" value="1"></td>
                            <td><input type="number" value="30.00"></td>
                            <td><input type="number" value="30.00"></td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>05 Dec '25 11:00</td>
                            <td>Experiential Singapore Tour</td>
                            <td><input type="checkbox" checked></td>
                            <td><input type="number" value="2"></td>
                            <td><input type="number" value="65.00"></td>
                            <td><input type="number" value="65.00"></td>
                            <td><input type="number" value="1"></td>
                            <td><input type="number" value="45.00"></td>
                            <td><input type="number" value="45.00"></td>
                            <td><input type="number" value="1"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Book Meals Section -->
        <div class="section-card">
            <div class="section-header">
                <span>Book Meals</span>
                <div>
                    <button class="btn btn-sm btn-light btn-xs">+ Event</button>
                    <button class="btn btn-sm btn-light btn-xs ms-1">- Event</button>
                </div>
            </div>
            <div class="section-body">
                <div class="empty-section">No meals added yet</div>
            </div>
        </div>

        <!-- Provide Transfers Section -->
        <div class="section-card">
            <div class="section-header">
                <span>Provide Transfers</span>
                <div>
                    <button class="btn btn-sm btn-info btn-xs">Transfer Package</button>
                    <button class="btn btn-sm btn-light btn-xs ms-1">+ Event</button>
                    <button class="btn btn-sm btn-light btn-xs ms-1">- Event</button>
                </div>
            </div>
            <div class="section-body">
                <table class="table table-custom table-hover">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Destination</th>
                            <th>Vehicle Type</th>
                            <th>Adults</th>
                            <th>Child</th>
                            <th>Cost</th>
                            <th>Sell</th>
                            <th>Tax Incl</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>06 Dec '25 19:25</td>
                            <td>Universal Studios Peak</td>
                            <td>Combi</td>
                            <td><input type="number" value="2"></td>
                            <td><input type="number" value="1"></td>
                            <td><input type="number" value="35.00"></td>
                            <td><input type="number" value="35.00"></td>
                            <td><input type="checkbox" checked></td>
                        </tr>
                        <tr>
                            <td>07 Dec '25 14:45</td>
                            <td>V Hotel Lavender / Gardens</td>
                            <td>Combi</td>
                            <td><input type="number" value="2"></td>
                            <td><input type="number" value="1"></td>
                            <td><input type="number" value="35.00"></td>
                            <td><input type="number" value="35.00"></td>
                            <td><input type="checkbox" checked></td>
                        </tr>
                        <tr>
                            <td>07 Dec '25 20:25</td>
                            <td>Gardens By The Bay</td>
                            <td>Combi</td>
                            <td><input type="number" value="2"></td>
                            <td><input type="number" value="1"></td>
                            <td><input type="number" value="35.00"></td>
                            <td><input type="number" value="35.00"></td>
                            <td><input type="checkbox" checked></td>
                        </tr>
                        <tr>
                            <td>08 Dec '25 21:00</td>
                            <td>V Hotel Lavender / Bus Station</td>
                            <td>Combi</td>
                            <td><input type="number" value="2"></td>
                            <td><input type="number" value="1"></td>
                            <td><input type="number" value="30.00"></td>
                            <td><input type="number" value="30.00"></td>
                            <td><input type="checkbox" checked></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>


    </div>

    <!-- Fixed Bottom Action Bar (Red Box) -->
    <div class="enquiry-pro-footer">
        <!-- Charges Section inside Red Box -->
        <div class="charges-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <span style="font-size: 11px; font-weight: 600;">Charges:- [Singapore Dollars - SGD | xRate: 1.0000]</span>
                <button class="btn btn-sm btn-outline-secondary" style="padding: 4px 10px; font-size: 11px;">Show Settings</button>
            </div>
            <div style="display: flex; align-items: center; gap: 20px;">
                <label style="margin: 0;">
                    <input type="checkbox" id="hotelRoom" style="width: 15px; height: 15px; margin-right: 5px;"> Hotel / Room
                </label>
                <label style="margin: 0;">
                    <input type="checkbox" id="otherServices" checked style="width: 15px; height: 15px; margin-right: 5px;"> Other Services
                </label>
                <div style="flex: 1;"></div>
                <div style="font-size: 11px; font-weight: 600; background: #cfe2ff; padding: 6px 16px; border-radius: 4px; white-space: nowrap; border: 1px solid #9ec5fe;">
                    Adult / Pax: 633.00 to 663.00 &nbsp;&nbsp;|&nbsp;&nbsp; Child / Pax: 561.00 to 591.00
                </div>
            </div>
        </div>
        
        <div class="action-buttons">
            <button class="btn btn-primary">Send Email</button>
            <button class="btn btn-info">Print Quote</button>
            <button class="btn btn-secondary">Print Cost Sheet</button>
            <button class="btn btn-warning">Recalculate</button>
            <button class="btn btn-success">Quote</button>
            <button class="btn btn-primary">Confirm</button>
            <button class="btn btn-info">Re-Confirm</button>
            <button class="btn btn-danger">Cancel Booking</button>
            <button class="btn btn-dark">Close</button>
        </div>
    </div>

</div>

<!-- Accommodation Selection Modal -->
<div class="modal fade" id="accommodationModal" tabindex="-1" aria-labelledby="accommodationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 900px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white py-2">
                <h6 class="modal-title mb-0" id="accommodationModalLabel">
                    <i class="ri-hotel-line me-2"></i>Select Hotels
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Hotel Selection Form - 2 Row Layout -->
                <div class="row g-2 mb-1">
                    <div class="col-3">
                        <label class="form-label small">Destination</label>
                        <select class="form-select form-select-sm" id="hotelDestination" onchange="loadHotelsByDestination()">
                            <option value="">-- Select Destination --</option>
                            <option value="Singapore" {{ ($destination ?? '') == 'Singapore' ? 'selected' : '' }}>Singapore</option>
                            <option value="Kuala Lumpur">Kuala Lumpur</option>
                            <option value="Bangkok">Bangkok</option>
                            <option value="Phuket">Phuket</option>
                            <option value="Bali">Bali</option>
                            <option value="Jakarta">Jakarta</option>
                            <option value="Manila">Manila</option>
                            <option value="Ho Chi Minh">Ho Chi Minh</option>
                            <option value="Hanoi">Hanoi</option>
                            <option value="Penang">Penang</option>
                        </select>
                    </div>
                    <div class="col-3">
                        <label class="form-label small">Hotel</label>
                        <select class="form-select form-select-sm" id="hotelSelect" onchange="loadRoomTypes()" disabled>
                            <option value="">-- Select Hotel --</option>
                        </select>
                    </div>
                    <div class="col-2">
                        <label class="form-label small">Check In</label>
                        <input type="date" class="form-control form-control-sm" id="checkInDate" value="2025-12-04" onchange="updateCheckOutMinDate()">
                    </div>
                    <div class="col-2">
                        <label class="form-label small">Check Out</label>
                        <input type="date" class="form-control form-control-sm" id="checkOutDate" value="2025-12-07">
                    </div>
                    <div class="col-1">
                        <label class="form-label small">Nights</label>
                        <input type="number" class="form-control form-control-sm" id="numNights" value="3" readonly>
                    </div>
                    
                </div>

                <div class="row g-2 mb-1">
                    <div class="col-2">
                        <label class="form-label small">Room Type</label>
                        <select class="form-select form-select-sm" id="roomType" onchange="loadBedTypes()" disabled>
                            <option value="">-- Select Room Type --</option>
                        </select>
                    </div>
                    <div class="col-2">
                        <label class="form-label small">Bed Type <span class="text-muted" id="maxOccupancyLabel"></span></label>
                        <select class="form-select form-select-sm" id="bedType" onchange="updatePricing()" disabled>
                            <option value="">-- Select Bed --</option>
                        </select>
                    </div>
                    <div class="col-2">
                        <label class="form-label small">Meal Plan</label>
                        <select class="form-select form-select-sm" id="mealPlan">
                            <option value="CP">{$rooms->breakfast_title}</option>
                            <option value="MAP">{$rooms->lunch_title}</option>
                            <option value="AP">{$rooms->dinner_title}</option>
                            <option value="EP">{$rooms->ep_title}</option>
                        </select>
                    </div>
                    <div class="col-1">
                        <label class="form-label small">Rooms</label>
                        <input type="number" class="form-control form-control-sm" id="numRooms" value="1" min="1" onchange="recalculatePaxValidation()">
                    </div>
                    <div class="col-1">
                        <label class="form-label small">Adults</label>
                        <input type="number" class="form-control form-control-sm" id="adultsPerRoom" value="2" min="1" onchange="recalculatePaxValidation()">
                    </div>
                    <div class="col-1">
                        <label class="form-label small">Extra Bed</label>
                        <input type="number" class="form-control form-control-sm" id="extraBed" value="0" min="0">
                    </div>
                    <div class="col-1">
                        <label class="form-label small">Child w/o</label>
                        <input type="number" class="form-control form-control-sm" id="childWithoutBed" value="0" min="0">
                    </div>
                    <div class="col-1">
                        <label class="form-label small">Price <span class="text-muted" id="bedPriceLabel"></span></label>
                        <input type="text" class="form-control form-control-sm" id="roomPrice" value="0" readonly>
                    </div>
                    <div class="col-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary btn-sm w-100" onclick="addHotelToList()" id="addHotelBtn" disabled>
                            <i class="ri-add-line me-1"></i><span id="addButtonText">Add</span>
                        </button>
                    </div>
                </div>

                <!-- Selected Hotels List - Compact -->
                <div class="border-top pt-1 mt-1">
                    <h6 class="small mb-1 text-muted">Selected Hotels</h6>
                    <div style="max-height: 150px; overflow-y: auto;">
                        <table class="table table-sm table-bordered mb-0" style="font-size: 10px;">
                            <thead class="table-light">
                                <tr>
                                    <th>Hotel</th>
                                    <th>Dates</th>
                                    <th>Rooms</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="selectedHotelsList">
                                <!-- Hotels will be added here -->
                            </tbody>
                        </table>
                        <p class="text-muted small mb-0 text-center py-1" id="noHotelsMessage" style="font-size: 10px;">No hotels selected yet</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Close
                </button>
                <button type="button" class="btn btn-success btn-sm" onclick="saveSelectedHotels()">
                    <i class="ri-check-line me-1"></i>Add Accommodation
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    #accommodationModal .form-label.small {
        font-size: 10px;
        font-weight: 500;
        margin-bottom: 2px;
    }
    #accommodationModal .form-control-sm,
    #accommodationModal .form-select-sm {
        font-size: 10px;
        padding: 2px 4px;
        height: 22px;
        min-height: 0.375rem;
        border: 1px solid #ced4da;
    }
    #accommodationModal .row.g-2 {
        row-gap: 4px !important;
        column-gap: 6px !important;
    }
    .btn-xs {
        padding: 2px 6px;
        font-size: 10px;
        line-height: 1.2;
        height: 20px;
    }
    #selectedHotelsList td {
        vertical-align: middle;
        padding: 3px 4px;
        font-size: 10px;
    }
    #accommodationModal .modal-body {
        padding: 8px 12px;
    }
    #accommodationModal .modal-header {
        padding: 6px 12px;
    }
    #accommodationModal .modal-footer {
        padding: 6px 12px;
    }
    #accommodationModal h6 {
        font-size: 11px;
        margin-bottom: 4px;
    }
    
    /* Editable table cells - Match table-custom style */
    #accommodationTable input[type="date"],
    #accommodationTable input[type="number"],
    #accommodationTable select {
        padding: 1px 3px;
        border: 1px solid #ced4da;
        border-radius: 2px;
        font-size: 10px;
        height: 20px;
        min-height: 0.375rem;
    }
    
    #accommodationTable input[type="date"] {
        width: 95px;
    }
    
    #accommodationTable input[type="number"] {
        width: 45px;
        text-align: center;
    }
    
    #accommodationTable select {
        width: 55px;
        padding: 1px 2px;
    }
    
    #accommodationTable .editable-cell {
        cursor: pointer;
        padding: 0;
        font-size: 11px;
    }
    
    #accommodationTable .editable-cell:hover {
        background-color: #f0f0f0;
    }
    
    #accommodationTable input[readonly] {
        cursor: not-allowed;
        background-color: #f5f5f5;
    }
</style>
@endsection

@section('scripts')
<script>
    // Accommodation Modal Management
    let selectedHotelsTemp = [];
    let accommodationList = [];
    let editingHotelId = null;
    let currentHotelData = null;
    
    // Get total pax from header
    function getTotalPax() {
        const adults = parseInt(document.getElementById('adults')?.value || 2);
        const child = parseInt(document.getElementById('child')?.value || 0);
        return adults + child;
    }
    
    // Dynamic hotel room data from database
    const hotelRoomsData = {
        '1': { // The Singapore Riv
            'Deluxe Room': {
                beds: {
                    'King Bed': { price: 150, maxOccupancy: 2, extraBedPrice: 30 },
                    'Twin Bed': { price: 150, maxOccupancy: 2, extraBedPrice: 30 }
                }
            },
            'Superior Room': {
                beds: {
                    'King Bed': { price: 180, maxOccupancy: 2, extraBedPrice: 35 },
                    'Queen Bed': { price: 175, maxOccupancy: 2, extraBedPrice: 35 },
                    'Two Queen Beds': { price: 200, maxOccupancy: 4, extraBedPrice: 40 }
                }
            }
        },
        '2': { // Marina Bay Sands
            'Premier Room': {
                beds: {
                    'King Bed': { price: 350, maxOccupancy: 2, extraBedPrice: 50 },
                    'Twin Bed': { price: 350, maxOccupancy: 2, extraBedPrice: 50 }
                }
            },
            'Deluxe Room': {
                beds: {
                    'King Bed': { price: 450, maxOccupancy: 2, extraBedPrice: 60 },
                    'Two Double Beds': { price: 450, maxOccupancy: 4, extraBedPrice: 60 }
                }
            }
        },
        '3': { // Raffles Hotel
            'Classic Room': {
                beds: {
                    'King Bed': { price: 500, maxOccupancy: 2, extraBedPrice: 70 }
                }
            },
            'Grand Suite': {
                beds: {
                    'King Bed': { price: 800, maxOccupancy: 3, extraBedPrice: 100 },
                    'Two King Beds': { price: 900, maxOccupancy: 4, extraBedPrice: 120 }
                }
            }
        },
        '4': { // Mandarin Oriental
            'Deluxe Room': {
                beds: {
                    'King Bed': { price: 280, maxOccupancy: 2, extraBedPrice: 45 },
                    'Twin Bed': { price: 280, maxOccupancy: 2, extraBedPrice: 45 }
                }
            },
            'Suite Room': {
                beds: {
                    'King Bed': { price: 420, maxOccupancy: 3, extraBedPrice: 65 },
                    'King + Sofa Bed': { price: 450, maxOccupancy: 4, extraBedPrice: 70 }
                }
            }
        }
    };

    // Open Accommodation Modal
    function openAccommodationModal() {
        selectedHotelsTemp = [];
        editingHotelId = null;
        document.getElementById('selectedHotelsList').innerHTML = '';
        document.getElementById('noHotelsMessage').style.display = 'block';
        document.getElementById('addButtonText').textContent = 'Add to List';
        
        // Reset form
        resetHotelForm();
        
        // Set initial min date for checkout
        updateCheckOutMinDate();
        
        const accommodationModal = new bootstrap.Modal(document.getElementById('accommodationModal'));
        accommodationModal.show();
        
        // Calculate nights when dates change
        document.getElementById('checkInDate').addEventListener('change', calculateNights);
        document.getElementById('checkOutDate').addEventListener('change', calculateNights);
    }
    
    // Update check-out minimum date based on check-in date
    function updateCheckOutMinDate() {
        const checkInDate = document.getElementById('checkInDate').value;
        const checkOutInput = document.getElementById('checkOutDate');
        
        if (checkInDate) {
            // Set minimum checkout date to one day after check-in
            const checkIn = new Date(checkInDate);
            checkIn.setDate(checkIn.getDate() + 1);
            const minCheckOut = checkIn.toISOString().split('T')[0];
            checkOutInput.min = minCheckOut;
            
            // If current checkout is before or equal to check-in, reset it
            const currentCheckOut = checkOutInput.value;
            if (currentCheckOut && currentCheckOut <= checkInDate) {
                checkOutInput.value = minCheckOut;
            }
            
            // Calculate nights after updating
            calculateNights();
        }
    }

    // Load agents by agency via AJAX
    function loadAgentsByAgency() {
        const agencyId = document.getElementById('agencySelect').value;
        const agentSelect = document.getElementById('agentSelect');
        
        // Reset agent select
        agentSelect.innerHTML = '<option value="">-- Loading agents... --</option>';
        agentSelect.disabled = true;
        
        if (!agencyId) {
            agentSelect.innerHTML = '<option value="">-- Select Agency First --</option>';
            return;
        }
        
        // Fetch agents via AJAX
        fetch('{{ route("enquiry-form-pro.get-agents") }}?agency_id=' + encodeURIComponent(agencyId), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            agentSelect.innerHTML = '<option value="">-- Select Agent --</option>';
            
            if (!data.success || !data.agents || data.agents.length === 0) {
                agentSelect.innerHTML = '<option value="">No agents available for this agency</option>';
                return;
            }
            
            // Populate agent dropdown
            data.agents.forEach(agent => {
                const option = document.createElement('option');
                option.value = agent.agent_id;
                option.textContent = agent.name;
                agentSelect.appendChild(option);
            });
            
            agentSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error loading agents:', error);
            agentSelect.innerHTML = '<option value="">-- Error loading agents --</option>';
            alert('Error loading agents. Please try again.');
        });
    }

    // Load hotels by destination via AJAX
    function loadHotelsByDestination() {
        const destination = document.getElementById('hotelDestination').value;
        const hotelSelect = document.getElementById('hotelSelect');
        
        // Reset hotel select
        hotelSelect.innerHTML = '<option value="">-- Loading hotels... --</option>';
        hotelSelect.disabled = true;
        
        // Reset other fields
        document.getElementById('roomType').innerHTML = '<option value="">-- Select Room Type --</option>';
        document.getElementById('bedType').innerHTML = '<option value="">-- Select Bed --</option>';
        document.getElementById('roomType').disabled = true;
        document.getElementById('bedType').disabled = true;
        document.getElementById('addHotelBtn').disabled = true;
        document.getElementById('maxOccupancyLabel').textContent = '';
        document.getElementById('bedPriceLabel').textContent = '';
        document.getElementById('roomPrice').value = '0';
        currentHotelData = null;
        
        if (!destination) {
            hotelSelect.innerHTML = '<option value="">-- Select Hotel --</option>';
            return;
        }
        
        // Fetch hotels via AJAX
        fetch('{{ route("enquiry-form-pro.get-hotels") }}?destination=' + encodeURIComponent(destination), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            hotelSelect.innerHTML = '<option value="">-- Select Hotel --</option>';
            
            if (!data.success || !data.hotels || data.hotels.length === 0) {
                alert('No hotels available for this destination');
                return;
            }
            
            // Populate hotel dropdown
            data.hotels.forEach(hotel => {
                const option = document.createElement('option');
                option.value = hotel.id;
                option.setAttribute('data-hotel-name', hotel.name);
                option.setAttribute('data-hotel-data', JSON.stringify({
                    id: hotel.id,
                    name: hotel.name,
                    rooms: hotel.rooms
                }));
                option.textContent = hotel.name;
                hotelSelect.appendChild(option);
            });
            
            hotelSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error loading hotels:', error);
            hotelSelect.innerHTML = '<option value="">-- Error loading hotels --</option>';
            alert('Error loading hotels. Please try again.');
        });
    }

    // Reset hotel form
    function resetHotelForm() {
        document.getElementById('hotelSelect').value = '';
        document.getElementById('roomType').value = '';
        document.getElementById('bedType').value = '';
        document.getElementById('numRooms').value = '1';
        document.getElementById('adultsPerRoom').value = '2';
        document.getElementById('extraBed').value = '0';
        document.getElementById('childWithoutBed').value = '0';
        // document.getElementById('mealPlan').value = 'CP';
        document.getElementById('roomPrice').value = '0';
        document.getElementById('maxOccupancyLabel').textContent = '';
        document.getElementById('bedPriceLabel').textContent = '';
        document.getElementById('roomType').disabled = true;
        document.getElementById('bedType').disabled = true;
        document.getElementById('addHotelBtn').disabled = true;
        editingHotelId = null;
        document.getElementById('addButtonText').textContent = 'Add';
    }

    // Load room types when hotel is selected
    function loadRoomTypes() {
        const hotelSelect = document.getElementById('hotelSelect');
        const hotelId = hotelSelect.value;
        const roomTypeSelect = document.getElementById('roomType');
        const bedTypeSelect = document.getElementById('bedType');
        const addBtn = document.getElementById('addHotelBtn');
        
        // Reset room and bed type
        roomTypeSelect.innerHTML = '<option value="">-- Select Room Type --</option>';
        bedTypeSelect.innerHTML = '<option value="">-- Select Bed --</option>';
        bedTypeSelect.disabled = true;
        addBtn.disabled = true;
        document.getElementById('maxOccupancyLabel').textContent = '';
        document.getElementById('bedPriceLabel').textContent = '';
        document.getElementById('roomPrice').value = '0';
        
        if (!hotelId) {
            roomTypeSelect.disabled = true;
            currentHotelData = null;
            return;
        }
        
        // Get hotel data from selected option
        const selectedOption = hotelSelect.options[hotelSelect.selectedIndex];
        const hotelDataStr = selectedOption.getAttribute('data-hotel-data');
        
        if (!hotelDataStr) {
            roomTypeSelect.disabled = true;
            return;
        }
        
        try {
            currentHotelData = JSON.parse(hotelDataStr);
            
            if (!currentHotelData.rooms || currentHotelData.rooms.length === 0) {
                roomTypeSelect.disabled = true;
                alert('No rooms available for this hotel');
                return;
            }
            
            // Get unique room types
            const roomTypes = [...new Set(currentHotelData.rooms.map(room => room.room_type))];
            
            roomTypes.forEach(roomType => {
                const option = document.createElement('option');
                option.value = roomType;
                option.textContent = roomType;
                roomTypeSelect.appendChild(option);
            });
            
            roomTypeSelect.disabled = false;
        } catch (e) {
            console.error('Error parsing hotel data:', e);
            roomTypeSelect.disabled = true;
        }
    }

    // Load bed types when room type is selected
    function loadBedTypes() {
        const roomType = document.getElementById('roomType').value;
        const bedTypeSelect = document.getElementById('bedType');
        const maxOccupancyLabel = document.getElementById('maxOccupancyLabel');
        
        bedTypeSelect.innerHTML = '<option value="">-- Select Bed --</option>';
        bedTypeSelect.disabled = true;
        maxOccupancyLabel.textContent = '';
        document.getElementById('bedPriceLabel').textContent = '';
        document.getElementById('roomPrice').value = '0';
        document.getElementById('addHotelBtn').disabled = true;
        
        if (!currentHotelData || !roomType) {
            return;
        }
        
        // Filter rooms by room type
        const roomsOfType = currentHotelData.rooms.filter(room => room.room_type === roomType);
        
        if (roomsOfType.length === 0) {
            return;
        }
        
        // Get the first room of this type (they should all have same bed_types)
        const room = roomsOfType[0];
        
        // Check if bed_types array exists
        if (!room.bed_types || room.bed_types.length === 0) {
            console.error('No bed types available for this room');
            return;
        }
        
        // Populate bed types from bed_types array
        room.bed_types.forEach(bedType => {
            const option = document.createElement('option');
            option.value = bedType.bed_type_id;
            
            // Store complete bed and room data
            const combinedData = {
                ...room,
                bed_type: bedType.bed_type,
                max_occupancy: bedType.max_occupancy,
                extra_bed_price: bedType.extra_bed_price,
                has_extra_bed: bedType.has_extra_bed
            };
            
            option.setAttribute('data-room-data', JSON.stringify(combinedData));
            option.textContent = `${bedType.bed_type} (Max: ${bedType.max_occupancy})`;
            bedTypeSelect.appendChild(option);
        });
        
        bedTypeSelect.disabled = false;
    }

    // Update pricing when bed type is selected
    function updatePricing() {
        const bedTypeSelect = document.getElementById('bedType');
        const roomId = bedTypeSelect.value;
        const bedPriceLabel = document.getElementById('bedPriceLabel');
        const maxOccupancyLabel = document.getElementById('maxOccupancyLabel');
        const roomPriceInput = document.getElementById('roomPrice');
        const addBtn = document.getElementById('addHotelBtn');
        
        if (!roomId) {
            bedPriceLabel.textContent = '';
            maxOccupancyLabel.textContent = '';
            roomPriceInput.value = '0';
            addBtn.disabled = true;
            return;
        }
        
        // Get room data from selected option
        const selectedOption = bedTypeSelect.options[bedTypeSelect.selectedIndex];
        const roomDataStr = selectedOption.getAttribute('data-room-data');
        
        if (!roomDataStr) {
            addBtn.disabled = true;
            return;
        }
        
        try {
            const roomData = JSON.parse(roomDataStr);
            const basePrice = roomData.double_weekday_price;
            const maxOccupancy = roomData.max_occupancy;
            
            bedPriceLabel.textContent = `($${basePrice}/night)`;
            maxOccupancyLabel.textContent = `(Max: ${maxOccupancy})`;
            roomPriceInput.value = basePrice;
            
            // Validate and auto-calculate extra beds
            validatePaxAndCalculateExtraBeds(roomData);
            
            addBtn.disabled = false;
        } catch (e) {
            console.error('Error parsing room data:', e);
            addBtn.disabled = true;
        }
    }
    
    // Validate total pax and calculate extra beds needed
    function validatePaxAndCalculateExtraBeds(roomData) {
        const totalPax = getTotalPax();
        const numRooms = parseInt(document.getElementById('numRooms').value) || 1;
        const adultsPerRoom = parseInt(document.getElementById('adultsPerRoom').value) || 2;
        const maxOccupancy = roomData.max_occupancy;
        const extraBedInput = document.getElementById('extraBed');
        
        // Calculate total occupancy
        const baseOccupancy = numRooms * adultsPerRoom;
        
        // Check if we need extra beds
        if (baseOccupancy < totalPax) {
            const paxDeficit = totalPax - baseOccupancy;
            
            // Check if max occupancy allows extra beds
            const maxTotalOccupancy = numRooms * maxOccupancy;
            
            if (maxTotalOccupancy >= totalPax) {
                // Auto-fill extra beds
                extraBedInput.value = paxDeficit;
            } else {
                alert(`Total pax (${totalPax}) exceeds maximum room capacity (${maxTotalOccupancy}). Please add more rooms or reduce pax.`);
            }
        } else {
            extraBedInput.value = 0;
        }
    }
    
    // Recalculate when rooms or adults change
    function recalculatePaxValidation() {
        const bedTypeSelect = document.getElementById('bedType');
        const roomId = bedTypeSelect.value;
        
        if (!roomId) return;
        
        const selectedOption = bedTypeSelect.options[bedTypeSelect.selectedIndex];
        const roomDataStr = selectedOption.getAttribute('data-room-data');
        
        if (roomDataStr) {
            try {
                const roomData = JSON.parse(roomDataStr);
                validatePaxAndCalculateExtraBeds(roomData);
            } catch (e) {
                console.error('Error:', e);
            }
        }
    }

    // Calculate number of nights
    function calculateNights() {
        const checkIn = new Date(document.getElementById('checkInDate').value);
        const checkOut = new Date(document.getElementById('checkOutDate').value);
        
        if (checkIn && checkOut && checkOut > checkIn) {
            const diffTime = Math.abs(checkOut - checkIn);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            document.getElementById('numNights').value = diffDays;
        }
    }

    // Add or update hotel in temporary list
    function addHotelToList() {
        const hotelSelect = document.getElementById('hotelSelect');
        const hotelId = hotelSelect.value;
        const hotelName = hotelSelect.options[hotelSelect.selectedIndex].text;
        const destination = document.getElementById('hotelDestination').value;
        const roomType = document.getElementById('roomType').value;
        const bedTypeSelect = document.getElementById('bedType');
        const roomId = bedTypeSelect.value;
        const checkIn = document.getElementById('checkInDate').value;
        const checkOut = document.getElementById('checkOutDate').value;
        const nights = document.getElementById('numNights').value;
        const rooms = document.getElementById('numRooms').value;
        const adultsPerRoom = document.getElementById('adultsPerRoom').value;
        const extraBed = document.getElementById('extraBed').value;
        const childWithoutBed = document.getElementById('childWithoutBed').value;
        // const mealPlan = document.getElementById('mealPlan').value;
        const roomPrice = document.getElementById('roomPrice').value;

        if (!hotelId) {
            alert('Please select a hotel');
            return;
        }

        if (!roomType || !roomId) {
            alert('Please select room type and bed type');
            return;
        }

        if (!checkIn || !checkOut) {
            alert('Please select check-in and check-out dates');
            return;
        }
        
        // Get room data
        const selectedOption = bedTypeSelect.options[bedTypeSelect.selectedIndex];
        const roomDataStr = selectedOption.getAttribute('data-room-data');
        const roomData = JSON.parse(roomDataStr);
        const bedType = roomData.bed_type;
        const maxOccupancy = roomData.max_occupancy;
        
        // Final validation
        const totalPax = getTotalPax();
        const maxTotalOccupancy = parseInt(rooms) * maxOccupancy;
        
        if (totalPax > maxTotalOccupancy) {
            alert(`Total pax (${totalPax}) exceeds maximum room capacity (${maxTotalOccupancy}). Please adjust rooms or pax.`);
            return;
        }

        if (editingHotelId !== null) {
            // Update existing hotel
            const index = selectedHotelsTemp.findIndex(h => h.id === editingHotelId);
            if (index !== -1) {
                selectedHotelsTemp[index] = {
                    ...selectedHotelsTemp[index],
                    hotelId: hotelId,
                    hotelName: hotelName,
                    destination: destination,
                    roomId: roomId,
                    roomType: roomType,
                    bedType: bedType,
                    maxOccupancy: maxOccupancy,
                    checkIn: checkIn,
                    checkOut: checkOut,
                    nights: nights,
                    rooms: rooms,
                    adultsPerRoom: adultsPerRoom,
                    extraBed: extraBed,
                    childWithoutBed: childWithoutBed,
                    // mealPlan: mealPlan,
                    roomPrice: roomPrice
                };
            }
        } else {
            // Add new hotel
            const hotel = {
                id: Date.now(), // Temporary ID
                hotelId: hotelId,
                hotelName: hotelName,
                destination: destination,
                roomId: roomId,
                roomType: roomType,
                bedType: bedType,
                maxOccupancy: maxOccupancy,
                checkIn: checkIn,
                checkOut: checkOut,
                nights: nights,
                rooms: rooms,
                adultsPerRoom: adultsPerRoom,
                extraBed: extraBed,
                childWithoutBed: childWithoutBed,
                // mealPlan: mealPlan,
                roomPrice: roomPrice
            };
            selectedHotelsTemp.push(hotel);
        }

        updateSelectedHotelsList();
        resetHotelForm();
    }

    // Update the selected hotels list in modal
    function updateSelectedHotelsList() {
        const tbody = document.getElementById('selectedHotelsList');
        const noMessage = document.getElementById('noHotelsMessage');
        
        if (selectedHotelsTemp.length === 0) {
            tbody.innerHTML = '';
            noMessage.style.display = 'block';
            return;
        }

        noMessage.style.display = 'none';
        tbody.innerHTML = selectedHotelsTemp.map(hotel => `
            <tr>
                <td>${hotel.hotelName}<br><small class="text-muted">${hotel.roomType} - ${hotel.bedType}</small></td>
                <td style="white-space: nowrap;">${formatDate(hotel.checkIn)} - ${formatDate(hotel.checkOut)}<br><small class="text-muted">${hotel.nights} nights</small></td>
                <td>${hotel.rooms}<br><small class="text-muted">$${hotel.roomPrice}/night</small></td>
                <td>
                    <button type="button" class="btn btn-xs btn-warning me-1" onclick="editHotelFromTempList(${hotel.id})" title="Edit">
                        <i class="ri-edit-line"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-danger" onclick="removeFromTempList(${hotel.id})" title="Delete">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    // Format date for display
    function formatDate(dateString) {
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = date.toLocaleString('default', { month: 'short' });
        const year = String(date.getFullYear()).slice(-2);
        return `${day} ${month} '${year}`;
    }

    // Edit hotel from temporary list
    function editHotelFromTempList(id) {
        const hotel = selectedHotelsTemp.find(h => h.id === id);
        if (!hotel) return;

        // Populate form with hotel data
        document.getElementById('hotelDestination').value = hotel.destination;
        document.getElementById('hotelSelect').value = hotel.hotelId;
        
        // Load room types then set values
        loadRoomTypes();
        setTimeout(() => {
            document.getElementById('roomType').value = hotel.roomType;
            loadBedTypes();
            setTimeout(() => {
                document.getElementById('bedType').value = hotel.bedType;
                updatePricing();
            }, 100);
        }, 100);
        
        document.getElementById('checkInDate').value = hotel.checkIn;
        document.getElementById('checkOutDate').value = hotel.checkOut;
        document.getElementById('numNights').value = hotel.nights;
        document.getElementById('numRooms').value = hotel.rooms;
        document.getElementById('adultsPerRoom').value = hotel.adultsPerRoom;
        document.getElementById('extraBed').value = hotel.extraBed;
        document.getElementById('childWithoutBed').value = hotel.childWithoutBed;
        // document.getElementById('mealPlan').value = hotel.mealPlan;

        // Set editing mode
        editingHotelId = id;
        document.getElementById('addButtonText').textContent = 'Update';
    }

    // Remove hotel from temporary list
    function removeFromTempList(id) {
        selectedHotelsTemp = selectedHotelsTemp.filter(hotel => hotel.id !== id);
        updateSelectedHotelsList();
    }

    // Save selected hotels to main accommodation table
    function saveSelectedHotels() {
        if (selectedHotelsTemp.length === 0) {
            alert('Please add at least one hotel');
            return;
        }

        // Add to main accommodation list
        accommodationList = [...accommodationList, ...selectedHotelsTemp];
        updateAccommodationTable();

        // Close modal
        const accommodationModal = bootstrap.Modal.getInstance(document.getElementById('accommodationModal'));
        accommodationModal.hide();

        // Clear temp list
        selectedHotelsTemp = [];
    }

    // Update main accommodation table
    function updateAccommodationTable() {
        const tbody = document.getElementById('accommodationTableBody');
        const table = document.getElementById('accommodationTable');
        const emptyMessage = document.getElementById('emptyAccommodationMessage');

        if (accommodationList.length === 0) {
            table.style.display = 'none';
            emptyMessage.style.display = 'block';
            return;
        }

        table.style.display = 'table';
        emptyMessage.style.display = 'none';

        tbody.innerHTML = accommodationList.map((hotel, index) => `
            <tr>
                <td><input type="checkbox" class="accommodation-checkbox" value="${hotel.id}"></td>
                <td>
                    <div class="editable-cell" onclick="makeEditableHotelName(${index})" id="hotelName_${index}">
                        ${hotel.hotelName}
                    </div>
                </td>
                <td>
                    <input type="date" class="form-control form-control-sm" 
                           value="${hotel.checkIn}" 
                           onchange="updateAccommodationField(${index}, 'checkIn', this.value); recalculateNights(${index})">
                </td>
                <td>
                    <input type="date" class="form-control form-control-sm" 
                           value="${hotel.checkOut}" 
                           onchange="updateAccommodationField(${index}, 'checkOut', this.value); recalculateNights(${index})">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" 
                           value="${hotel.nights}" 
                           readonly style="background-color: #f5f5f5;">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" 
                           value="${hotel.rooms}" min="1"
                           onchange="updateAccommodationField(${index}, 'rooms', this.value)">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" 
                           value="${hotel.adultsPerRoom}" min="1"
                           onchange="updateAccommodationField(${index}, 'adultsPerRoom', this.value)">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" 
                           value="${hotel.extraBed}" min="0"
                           onchange="updateAccommodationField(${index}, 'extraBed', this.value)">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" 
                           value="${hotel.childWithoutBed}" min="0"
                           onchange="updateAccommodationField(${index}, 'childWithoutBed', this.value)">
                </td>
            </tr>
        `).join('');
    }

    // Update accommodation field
    function updateAccommodationField(index, field, value) {
        if (accommodationList[index]) {
            accommodationList[index][field] = value;
        }
    }

    // Recalculate nights when dates change
    function recalculateNights(index) {
        const hotel = accommodationList[index];
        if (!hotel) return;

        const checkIn = new Date(hotel.checkIn);
        const checkOut = new Date(hotel.checkOut);
        
        if (checkIn && checkOut && checkOut > checkIn) {
            const diffTime = Math.abs(checkOut - checkIn);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            accommodationList[index].nights = diffDays;
            updateAccommodationTable();
        }
    }

    // Make hotel name editable (for future enhancement with dropdown)
    function makeEditableHotelName(index) {
        // For now, just show it's clickable
        // You can add dropdown functionality later
        console.log('Edit hotel name for index:', index);
    }

    // Remove selected accommodation
    function removeSelectedAccommodation() {
        const checkboxes = document.querySelectorAll('.accommodation-checkbox:checked');
        
        if (checkboxes.length === 0) {
            alert('Please select hotels to remove');
            return;
        }

        const idsToRemove = Array.from(checkboxes).map(cb => parseInt(cb.value));
        accommodationList = accommodationList.filter(hotel => !idsToRemove.includes(hotel.id));
        updateAccommodationTable();
    }

    // Select all accommodation
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAllAccommodation');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.accommodation-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        }
    });

    // Collapse sidebar on page load for Enquiry Pro
    window.addEventListener('load', function() {
        setTimeout(function() {
            const html = document.documentElement;
            const layoutMenu = document.querySelector('.layout-menu');
            const menuToggle = document.querySelector('.layout-menu-toggle');
            
            // Check if Helpers object exists (from helpers.js)
            if (typeof Helpers !== 'undefined' && Helpers.toggleCollapsed) {
                // Use the template's built-in collapse function
                if (!html.classList.contains('layout-menu-collapsed')) {
                    Helpers.toggleCollapsed();
                }
            } else {
                // Fallback method if Helpers not available
                if (html) {
                    html.classList.remove('layout-menu-expanded');
                    html.classList.add('layout-menu-collapsed');
                }
                
                if (layoutMenu) {
                    layoutMenu.classList.add('closed');
                }
                
                // Update body/content margin
                const layoutPage = document.querySelector('.layout-page');
                if (layoutPage) {
                    layoutPage.style.marginLeft = '0';
                    layoutPage.style.marginInlineStart = '0';
                }
            }
        }, 100); // Small delay to ensure all scripts are loaded
    });
</script>
@endsection

