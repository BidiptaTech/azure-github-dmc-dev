    @extends('layouts.layout')
@section('title', 'Enquiry Pro')
@extends('layouts.datatablecss')
@section('css')
<style>
    /* Hide footer on Enquiry Pro page */
    footer.footer {
        display: none !important;
    }

    /* Hide navigation tabs */
    .nav-tabs-custom {
        display: none !important;
    }

    /* Hide navbar user dropdown */
    .navbar-nav-right {
        display: none !important;
    }

    /* Hide entire navbar */
    #layout-navbar {
        display: none !important;
    }

    /* Remove top padding from layout-page */
    .layout-navbar-fixed .layout-wrapper:not(.layout-without-menu) .layout-page {
        padding-top: 0 !important;
    }
    
    .layout-page {
        padding-top: 0 !important;
    }

    /* Main Container - Full Height */
    .enquiry-pro-container {
        display: flex;
        flex-direction: column;
        height: 100vh;
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
        border-bottom: 3px solid #ffe69c;
        padding: 4px 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    /* Navigation Tabs - Compact */
    .nav-tabs-custom {
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 4px;
    }

    .nav-tabs-custom .nav-link {
        border: none;
        color: #495057;
        font-weight: 500;
        padding: 4px 12px;
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

    /* Customer Details Section - Full Width & Attractive */
    .customer-details {
        background: linear-gradient(135deg, #fff9e6 0%, #fffbf0 100%);
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 11px;
        border: 1px solid #ffd966;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .customer-details .row {
        margin: 0;
        width: 100%;
    }

    .detail-label {
        font-size: 10px;
        font-weight: 600;
        margin: 0;
        white-space: nowrap;
        color: #495057;
        letter-spacing: 0.3px;
    }
    
    .customer-details .form-control-sm,
    .customer-details .form-select-sm {
        border: 1px solid #dee2e6;
        background: #ffffff;
        transition: all 0.2s ease;
    }
    
    .customer-details .form-control-sm:focus,
    .customer-details .form-select-sm:focus {
        border-color: #ffc107;
        box-shadow: 0 0 0 0.15rem rgba(255, 193, 7, 0.15);
    }
    
    .customer-details .col-auto {
        flex: 0 0 auto;
        padding: 0 4px;
    }
    
    .customer-details .col {
        flex: 1 1 auto;
        padding: 0 4px;
        min-width: 0;
    }

    .customer-details input[type="radio"] {
        width: 12px;
        height: 12px;
        margin-right: 2px;
    }

    .customer-details label {
        font-size: 9px;
        margin: 0;
        display: flex;
        align-items: center;
        white-space: nowrap;
    }

    .customer-details .form-control-sm,
    .customer-details .form-select-sm {
        height: 20px;
        padding: 1px 3px;
        font-size: 9px;
        border: 1px solid #ced4da;
        border-radius: 2px;
        min-width: 0;
    }
    
    .customer-details .gap-1 {
        gap: 2px !important;
    }
    
    .customer-details .d-flex {
        flex-shrink: 1;
        min-width: 0;
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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2px 12px;
        font-weight: 600;
        font-size: 13px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        line-height: 1.3;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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
        padding: 2px 6px !important;
    }

    .table-custom td {
        padding: 2px 6px !important;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
        font-size: 11px;
        line-height: 1.3;
    }

    .table-custom tbody tr {
        height: 25px;
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
        background: #fff9e6;
        border: 1px solid #ffe69c;
        border-top: 2px solid #ffe69c;
        padding: 6px 12px;
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
        padding: 4px 10px;
        font-weight: 500;
        border-radius: 3px;
        font-size: 11px;
        line-height: 1.3;
    }
    
    .action-buttons .btn-sm {
        padding: 3px 8px;
        font-size: 10px;
        line-height: 1.2;
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
            <!-- Row 1: User Info & Tour Details -->
            <div class="row g-2 mb-2">
                <div class="col-auto">
                    <strong style="font-size: 11px; color: #2c3e50;">{{ $user->name }}</strong>
                </div>
                <div class="col-auto">
                    <label style="font-size: 10px; margin: 0; font-weight: 500;">
                        <input type="radio" name="type" value="FIT" {{ (isset($initialData['tour_type']) && $initialData['tour_type'] == 'FIT') || !isset($initialData) ? 'checked' : '' }}> FIT
                    </label>
                    <label style="font-size: 10px; margin: 0 0 0 8px; font-weight: 500;">
                        <input type="radio" name="type" value="Group" {{ isset($initialData['tour_type']) && $initialData['tour_type'] == 'Group' ? 'checked' : '' }}> Group
                    </label>
                </div>
                <div class="col-auto d-flex align-items-center">
                    <span class="detail-label me-1">Salutation:</span>
                    <select class="form-select form-select-sm" id="salutationSelect" style="width: 65px; font-size: 10px;">
                        <option value="Mr" {{ (isset($initialData['salutation']) && $initialData['salutation'] == 'Mr') || !isset($initialData) ? 'selected' : '' }}>Mr</option>
                        <option value="Mrs" {{ isset($initialData['salutation']) && $initialData['salutation'] == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                        <option value="Ms" {{ isset($initialData['salutation']) && $initialData['salutation'] == 'Ms' ? 'selected' : '' }}>Ms</option>
                        <option value="Dr" {{ isset($initialData['salutation']) && $initialData['salutation'] == 'Dr' ? 'selected' : '' }}>Dr</option>
                        <option value="Prof">Prof</option>
                    </select>
                </div>
                <div class="col d-flex align-items-center">
                    <span class="detail-label me-1">Name:</span>
                    <input type="text" class="form-control form-control-sm flex-fill" value="{{ $initialData['customer_name'] ?? 'To Be Advised' }}" id="customerNameInput" style="font-size: 10px; max-width: 180px;">
                </div>
                <div class="col d-flex align-items-center">
                    <span class="detail-label me-1">Contact:</span>
                    <input type="text" class="form-control form-control-sm flex-fill" value="{{ $initialData['contact_number'] ?? '' }}" id="contactNumberInput" placeholder="Optional" style="font-size: 10px; max-width: 140px;">
                </div>
                <div class="col d-flex align-items-center">
                    <span class="detail-label me-1">Start:</span>
                    <input type="date" class="form-control form-control-sm" value="{{ $initialData['tour_start_date'] ?? '' }}" id="tourStartDate" style="font-size: 10px; width: 135px;">
                </div>
                <div class="col d-flex align-items-center">
                    <span class="detail-label me-1">End:</span>
                    <input type="date" class="form-control form-control-sm" value="{{ $initialData['tour_end_date'] ?? '' }}" id="tourEndDate" style="font-size: 10px; width: 135px;">
                </div>
            </div>

            <!-- Row 2: Agency & Travel Details -->
            <div class="row g-2">
                <div class="col d-flex align-items-center">
                    <span class="detail-label me-1">Agency:</span>
                    <select class="form-select form-select-sm flex-fill" id="agencySelect" style="font-size: 10px; max-width: 200px;" onchange="loadAgentsByAgency()">
                        <option value="">-- Select Agency --</option>
                        @foreach($agencies as $agency)
                            <option value="{{ $agency->agency_id }}" {{ isset($initialData['agency_id']) && $initialData['agency_id'] == $agency->agency_id ? 'selected' : '' }}>{{ $agency->agency_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col d-flex align-items-center">
                    <span class="detail-label me-1">Agent:</span>
                    <select class="form-select form-select-sm flex-fill" id="agentSelect" style="font-size: 10px; max-width: 180px;" {{ !isset($initialData['agent_id']) ? 'disabled' : '' }}>
                        @if(isset($initialData['agent_id']))
                            <option value="{{ $initialData['agent_id'] }}" selected>{{ $initialData['agent_name'] }}</option>
                        @else
                            <option value="">-- Select Agency First --</option>
                        @endif
                    </select>
                </div>
                <div class="col-auto d-flex align-items-center">
                    <span class="detail-label me-1">Adult:</span>
                    <input type="number" class="form-control form-control-sm" value="{{ $initialData['adult_count'] ?? '2' }}" id="adultCountInput" min="0" style="width: 55px; font-size: 10px;">
                </div>
                <div class="col-auto d-flex align-items-center">
                    <span class="detail-label me-1">Child:</span>
                    <input type="number" class="form-control form-control-sm" value="{{ $initialData['child_count'] ?? '0' }}" id="childCountInput" min="0" style="width: 55px; font-size: 10px;">
                </div>
                <div class="col-auto d-flex align-items-center">
                    <span class="detail-label me-1">Infant:</span>
                    <input type="number" class="form-control form-control-sm" value="{{ $initialData['infant_count'] ?? '0' }}" id="infantCountInput" min="0" style="width: 55px; font-size: 10px;">
                </div>
                <div class="col d-flex align-items-center">
                    <span class="detail-label me-1">Destination:</span>
                    @if(isset($initialData['destinations_array']))
                        <input type="text" class="form-control form-control-sm flex-fill" value="{{ $initialData['destination_display'] ?? '' }}" id="destinationDisplay" style="font-size: 10px; max-width: 250px;" title="{{ $initialData['destination_display'] ?? '' }}">
                    @else
                        <select class="form-select form-select-sm flex-fill" id="destinationSelect" style="font-size: 10px; max-width: 200px;">
                            <option value="">Select Destination</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->name }}" {{ (isset($initialData['destination_display']) && $initialData['destination_display'] == $country->name) || (!isset($initialData) && ($destination ?? '') == $country->name) ? 'selected' : '' }}>{{ $country->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Scrollable Middle Content -->
    <div class="enquiry-pro-content">
        
        <!-- Arrival/Departure Section -->
        <div class="section-card">
            <div class="section-header">
                <span>Arrival / Departure</span>
                <div>
                    <button class="btn btn-sm btn-light btn-xs" onclick="openArrivalDepartureModal()">+ Add</button>
                    <button class="btn btn-sm btn-light btn-xs ms-1" onclick="removeSelectedArrivalDeparture()">- Remove</button>
                </div>
            </div>
            <div class="section-body">
                <table class="table table-custom table-hover" id="arrivalDepartureTable" style="display: none;">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllArrivalDeparture"></th>
                            <th>Date/Time</th>
                            <th>Port</th>
                            <th>Flight/Train/Bus No</th>
                            <th>Type</th>
                            <th>Adults Qty</th>
                            <th>Cost/Pax</th>
                            <th>Sell/Pax</th>
                            <th>Child Qty</th>
                            <th>Cost/Pax</th>
                            <th>Sell/Pax</th>
                            <th>Infant Qty</th>
                            <th>Amount</th>
                            <th>Supplement</th>
                        </tr>
                    </thead>
                    <tbody id="arrivalDepartureTableBody">
                        <!-- Arrival/Departure entries will be added here -->
                    </tbody>
                </table>
                <div class="empty-section" id="emptyArrivalDepartureMessage">No arrival/departure added yet</div>
            </div>
        </div>

        <!-- Book Travel Section - COMMENTED OUT FOR NOW -->
        <!--
        <div class="section-card">
            <div class="section-header">
                <span>Book Travel</span>
                <div>
                    <button class="btn btn-sm btn-light btn-xs">+ Add</button>
                    <button class="btn btn-sm btn-light btn-xs ms-1">- Remove</button>
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
        -->

        <!-- Accommodation Section -->
        <div class="section-card">
            <div class="section-header">
                <span>Accommodation</span>
                <div>
                    <button class="btn btn-sm btn-light btn-xs" onclick="openAccommodationModal()">+ Add</button>
                    <button class="btn btn-sm btn-light btn-xs ms-1" onclick="removeSelectedAccommodation()">- Remove</button>
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

        <!-- Attractions Section -->
        <div class="section-card">
            <div class="section-header">
                <span>Attractions</span>
                <div>
                    <button class="btn btn-sm btn-light btn-xs" onclick="openTourModal()">+ Add</button>
                    <button class="btn btn-sm btn-light btn-xs ms-1">- Remove</button>
                </div>
            </div>
            <div class="section-body">
                <table class="table table-custom table-hover" id="tourTable" style="display: none;">
                    <thead>
                        <tr>
                            <th><input type="checkbox"></th>
                            <th>Date/Time</th>
                            <th>Tour Name</th>
                            <th>PTE</th>
                            <th>Adults Qty</th>
                            <th>Cost/Pax</th>
                            <th>Sell/Pax</th>
                            <th>Child Qty</th>
                            <th>Cost/Pax</th>
                            <th>Sell/Pax</th>
                            <th>Transfer</th>
                            <th>Guide</th>
                        </tr>
                    </thead>
                    <tbody id="tourTableBody">
                    </tbody>
                </table>
                <div class="empty-section" id="emptyTourMessage">No tours added yet</div>
            </div>
        </div>

        <!-- Restaurants Section -->
        <div class="section-card">
            <div class="section-header">
                <span>Restaurants</span>
                <div>
                    <button class="btn btn-sm btn-light btn-xs" onclick="openMealModal()">+ Add</button>
                    <button class="btn btn-sm btn-light btn-xs ms-1">- Remove</button>
                </div>
            </div>
            <div class="section-body">
                <table class="table table-custom table-hover" id="mealTable" style="display: none;">
                    <thead>
                        <tr>
                            <th><input type="checkbox"></th>
                            <th>Date/Time</th>
                            <th>Restaurant</th>
                            <th>Adults Qty</th>
                            <th>Cost/Pax</th>
                            <th>Sell/Pax</th>
                            <th>Child Qty</th>
                            <th>Cost/Pax</th>
                            <th>Sell/Pax</th>
                            <th>Transfer</th>
                        </tr>
                    </thead>
                    <tbody id="mealTableBody">
                    </tbody>
                </table>
                <div class="empty-section" id="emptyMealMessage">No meals added yet</div>
            </div>
        </div>

        <!-- Local Transfer Section -->
        <div class="section-card">
            <div class="section-header">
                <span>Local Transfer</span>
                <div>
                    <button class="btn btn-sm btn-light btn-xs" onclick="openTransferModal()">+ Add</button>
                    <button class="btn btn-sm btn-light btn-xs ms-1" onclick="removeSelectedTransfers()">- Remove</button>
                </div>
            </div>
            <div class="section-body">
                <table class="table table-custom table-hover" id="transferTable" style="display: none;">
                    <thead>
                        <tr>
                            <th><input type="checkbox"></th>
                            <th>Date/Time</th>
                            <th>Destination</th>
                            <th>Mode</th>
                            <th>Vehicle Type</th>
                            <th>Type</th>
                            <th>Way</th>
                            <th>Adults</th>
                            <th>Child</th>
                            <th>Cost</th>
                            <th>Sell</th>
                            <th>Tax Incl</th>
                        </tr>
                    </thead>
                    <tbody id="transferTableBody">
                    </tbody>
                </table>
                <div class="empty-section" id="emptyTransferMessage">No transfers added yet</div>
            </div>
        </div>

        <!-- Tour Guide Section -->
        <div class="section-card">
            <div class="section-header">
                <span>Tour Guide</span>
                <div>
                    <button class="btn btn-sm btn-light btn-xs" onclick="openGuideModal()">+ Add</button>
                    <button class="btn btn-sm btn-light btn-xs ms-1" onclick="removeSelectedGuides()">- Remove</button>
                </div>
            </div>
            <div class="section-body">
                <table class="table table-custom table-hover" id="guideTable" style="display: none;">
                    <thead>
                        <tr>
                            <th><input type="checkbox"></th>
                            <th>Date/Time</th>
                            <th>Tour/Activity</th>
                            <th>Language</th>
                            <th>Guide Name</th>
                            <th>Hours</th>
                            <th>Cost</th>
                            <th>Sell</th>
                        </tr>
                    </thead>
                    <tbody id="guideTableBody">
                    </tbody>
                </table>
                <div class="empty-section" id="emptyGuideMessage">No guides added yet</div>
            </div>
        </div>

    </div>

    <!-- Fixed Bottom Action Bar (Red Box) -->
    <div class="enquiry-pro-footer">
        <!-- Summary Table -->
        <div style="margin-bottom: 8px; overflow-x: auto;">
            <div style="max-height: 80px; overflow-y: auto;">
                <table class="table table-bordered table-sm" style="font-size: 9px; margin-bottom: 0; background: white;">
                    <thead style="background: #e9ecef; position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th style="padding: 3px 5px; vertical-align: middle; border-right: 2px solid #dee2e6; min-width: 180px; background: #e9ecef;">
                                <input type="checkbox" style="width: 12px; height: 12px;"> Hotel / Room
                            </th>
                            <th style="padding: 3px 5px; text-align: center; background: #fff3cd; min-width: 50px;">Single</th>
                            <th style="padding: 3px 5px; text-align: center; background: #d1ecf1; min-width: 50px;">Twin</th>
                            <th style="padding: 3px 5px; text-align: center; background: #f8d7da; min-width: 50px;">Triple</th>
                            <th style="padding: 3px 5px; text-align: center; background: #d4edda; min-width: 60px;">Child w/ bed</th>
                            <th style="padding: 3px 5px; text-align: center; background: #e7d6f5; min-width: 60px;">Child w/o bed</th>
                            <th style="padding: 3px 5px; text-align: center; background: #e2e3e5; min-width: 50px;">Infant</th>
                        </tr>
                    </thead>
                    <tbody id="footerSummaryBody">
                        <!-- Dynamic rows will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="action-buttons">
            <!-- <button class="btn btn-primary btn-sm">Send Email</button>
            <button class="btn btn-info btn-sm">Print Quote</button>
            <button class="btn btn-secondary btn-sm">Print Cost Sheet</button>
            <button class="btn btn-warning btn-sm" onclick="recalculateTotals()">Recalculate</button>
            <button class="btn btn-success btn-sm">Quote</button>
            <button class="btn btn-primary btn-sm">Confirm</button>
            <button class="btn btn-info btn-sm">Re-Confirm</button> -->
            <button class="btn btn-danger btn-sm">Cancel</button>
            <!-- <button class="btn btn-dark btn-sm">Close</button> -->
        </div>
    </div>

</div>

<!-- Accommodation Selection Modal -->
<div class="modal fade" id="accommodationModal" tabindex="-1" aria-labelledby="accommodationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 900px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white py-2">
                <h6 class="modal-title mb-0" id="accommodationModalLabel">
                    <i class="ri-hotel-line me-2" id="modalTitleIcon"></i><span id="modalTitleText">Select Hotels</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Hotel Selection Form - 2 Row Layout -->
                <div class="row g-2 mb-1" id="hotelSelectionRow1">
                    <div class="col-3">
                        <label class="form-label small">Destination</label>
                        <select class="form-select form-select-sm" id="hotelDestination" onchange="loadHotelsByDestination()">
                            <option value="">-- Select Destination --</option>
                            @foreach($destinations as $dest)
                                <option value="{{ $dest->name }}" {{ ($destination ?? '') == $dest->name ? 'selected' : '' }}>{{ $dest->name }}</option>
                            @endforeach
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

                <div class="row g-2 mb-1" id="hotelSelectionRow2">
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

                <!-- Arrival/Departure Flight Information -->
                <div class="border-top pt-2 mt-2" id="arrivalDepartureSection">
                    <h6 class="small mb-1 text-muted" id="arrivalDepartureSectionTitle">Arrival/Departure Flight Information</h6>
                    <div class="row g-2 mb-1">
                        <div class="col-2" id="arrivalDateTimeField">
                            <label class="form-label small">Arrival Date/Time</label>
                            <input type="datetime-local" class="form-control form-control-sm" id="arrivalDateTime">
                        </div>
                        <div class="col-2" id="arrivalPortField">
                            <label class="form-label small">Arrival Port</label>
                            <select class="form-select form-select-sm select2-port" id="arrivalPort">
                                <option value="">Select Port</option>
                                @foreach($ports as $port)
                                    <option value="{{ $port->id }}" data-type="{{ $port->type }}">{{ $port->port_name }} ({{ $port->type }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-2" id="arrivalFlightNoField">
                            <label class="form-label small">Arrival Flight/Train/Bus</label>
                            <input type="text" class="form-control form-control-sm" id="arrivalFlightNo" placeholder="Flight No.">
                        </div>
                        <div class="col-2" id="departureDateTimeField">
                            <label class="form-label small">Departure Date/Time</label>
                            <input type="datetime-local" class="form-control form-control-sm" id="departureDateTime">
                        </div>
                        <div class="col-2" id="departurePortField">
                            <label class="form-label small">Departure Port</label>
                            <select class="form-select form-select-sm select2-port" id="departurePort">
                                <option value="">Select Port</option>
                                @foreach($ports as $port)
                                    <option value="{{ $port->id }}" data-type="{{ $port->type }}">{{ $port->port_name }} ({{ $port->type }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-2" id="departureFlightNoField">
                            <label class="form-label small">Departure Flight/Train/Bus</label>
                            <input type="text" class="form-control form-control-sm" id="departureFlightNo" placeholder="Flight No.">
                        </div>
                    </div>
                </div>

                <!-- Selected Hotels List - Compact -->
                <div class="border-top pt-1 mt-1" id="selectedHotelsSection">
                    <h6 class="small mb-1 text-muted">Selected Hotels (Max 4 displayed)</h6>
                    <div style="max-height: 120px; overflow-y: auto;">
                        <table class="table table-sm table-bordered mb-0" style="font-size: 10px;">
                            <thead class="table-light">
                                <tr>
                                    <th>Hotel / Room</th>
                                    <th>Dates</th>
                                    <th>Qty</th>
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
                <button type="button" class="btn btn-success btn-sm" onclick="saveSelectedHotels()" id="saveAccommodationBtn">
                    <i class="ri-check-line me-1"></i><span id="saveAccommodationBtnText">Add Accommodation</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tour Selection Modal -->
<div class="modal fade" id="tourModal" tabindex="-1" aria-labelledby="tourModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 900px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white py-2">
                <h6 class="modal-title mb-0" id="tourModalLabel">
                    <i class="ri-map-pin-line me-2"></i><span id="tourModalTitleText">Add Tour / Attraction</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 8px 12px;">
                <!-- Tour Selection Form -->
                <div class="row g-1 mb-1">
                    <div class="col-3">
                        <label class="form-label small" style="margin-bottom: 1px;">Destination</label>
                        <select class="form-select form-select-sm" id="tourDestination" onchange="loadAttractionsByDestination()">
                            <option value="">-- Select Destination --</option>
                            @foreach($destinations as $dest)
                                <option value="{{ $dest->name }}" {{ ($destination ?? '') == $dest->name ? 'selected' : '' }}>{{ $dest->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4">
                        <label class="form-label small" style="margin-bottom: 1px;">Attraction / Tour</label>
                        <select class="form-select form-select-sm" id="attractionSelect">
                            <option value="">-- Select Attraction --</option>
                            @foreach($attractions as $attr)
                                <option value="{{ $attr->id }}" data-name="{{ $attr->name }}">{{ $attr->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-3">
                        <label class="form-label small" style="margin-bottom: 1px;">Date/Time</label>
                        <input type="datetime-local" class="form-control form-control-sm" id="tourDateTime">
                    </div>
                    <div class="col-1">
                        <label class="form-label small" style="margin-bottom: 1px;">PTE</label>
                        <input type="checkbox" class="form-check-input" id="tourPTE" style="margin-top: 8px;">
                    </div>
                    <div class="col-1">
                        <label class="form-label small" style="margin-bottom: 1px;">A.Qty</label>
                        <input type="number" class="form-control form-control-sm" id="tourAdultsQty" value="2" min="0">
                    </div>
                </div>

                <div class="row g-1 mb-1">
                    <div class="col-2">
                        <label class="form-label small" style="margin-bottom: 1px;">Adult Cost</label>
                        <input type="number" class="form-control form-control-sm" id="tourAdultCost" value="0" step="0.01">
                    </div>
                    <div class="col-2">
                        <label class="form-label small" style="margin-bottom: 1px;">Adult Sell</label>
                        <input type="number" class="form-control form-control-sm" id="tourAdultSell" value="0" step="0.01">
                    </div>
                    <div class="col-1">
                        <label class="form-label small" style="margin-bottom: 1px;">C.Qty</label>
                        <input type="number" class="form-control form-control-sm" id="tourChildQty" value="0" min="0">
                    </div>
                    <div class="col-2">
                        <label class="form-label small" style="margin-bottom: 1px;">Child Cost</label>
                        <input type="number" class="form-control form-control-sm" id="tourChildCost" value="0" step="0.01">
                    </div>
                    <div class="col-2">
                        <label class="form-label small" style="margin-bottom: 1px;">Child Sell</label>
                        <input type="number" class="form-control form-control-sm" id="tourChildSell" value="0" step="0.01">
                    </div>
                </div>

                <!-- Transfer Section -->
                <div class="border rounded p-1 mb-1" id="tourTransferSection" style="background: #f8f9fa;">
                    <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 3px;">
                        <h6 class="mb-0" style="font-size: 10px; font-weight: 600;">Transfer Options</h6>
                    </div>
                    
                    <div class="row g-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Required?</label>
                            <select class="form-select form-select-sm" id="transferRequired" onchange="toggleTransferFields()">
                                <option value="no">No</option>
                                <option value="yes">Yes</option>
                            </select>
                        </div>
                        <div class="col-2" id="transferTypeField" style="display: none;">
                            <label class="form-label small" style="margin-bottom: 1px;">Type</label>
                            <select class="form-select form-select-sm" id="transferType">
                                <option value="private">Private</option>
                                <option value="sic">SIC</option>
                            </select>
                        </div>
                        <div class="col-2" id="transferWayField" style="display: none;">
                            <label class="form-label small" style="margin-bottom: 1px;">Way</label>
                            <select class="form-select form-select-sm" id="transferWay">
                                <option value="one-way">One Way</option>
                                <option value="both-way">Both Way</option>
                            </select>
                        </div>
                        <div class="col-2" id="vehicleTypeField" style="display: none;">
                            <label class="form-label small" style="margin-bottom: 1px;">Vehicle</label>
                            <select class="form-select form-select-sm" id="vehicleType">
                                <option value="sedan">Sedan</option>
                                <option value="combi">Combi</option>
                                <option value="van">Van</option>
                                <option value="bus">Bus</option>
                            </select>
                        </div>
                        <div class="col-2" id="transferCostFields" style="display: none;">
                            <label class="form-label small" style="margin-bottom: 1px;">Cost</label>
                            <input type="number" class="form-control form-control-sm" id="transferCost" value="0" step="0.01">
                        </div>
                        <div class="col-2" id="transferSellFields" style="display: none;">
                            <label class="form-label small" style="margin-bottom: 1px;">Sell</label>
                            <input type="number" class="form-control form-control-sm" id="transferSell" value="0" step="0.01">
                        </div>
                    </div>
                </div>

                <!-- Guide Section -->
                <div class="border rounded p-1 mb-1" id="tourGuideSection" style="background: #f8f9fa;">
                    <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 3px;">
                        <h6 class="mb-0" style="font-size: 10px; font-weight: 600;">Guide Options</h6>
                    </div>
                    
                    <div class="row g-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Required?</label>
                            <select class="form-select form-select-sm" id="guideRequired" onchange="toggleGuideFields()">
                                <option value="no">No</option>
                                <option value="yes">Yes</option>
                            </select>
                        </div>
                        <div class="col-2" id="guideLanguageField" style="display: none;">
                            <label class="form-label small" style="margin-bottom: 1px;">Language</label>
                            <select class="form-select form-select-sm" id="guideLanguage">
                                <option value="english">English</option>
                                <option value="mandarin">Mandarin</option>
                                <option value="spanish">Spanish</option>
                                <option value="french">French</option>
                                <option value="german">German</option>
                                <option value="japanese">Japanese</option>
                                <option value="korean">Korean</option>
                            </select>
                        </div>
                        <div class="col-3" id="guideNameField" style="display: none;">
                            <label class="form-label small" style="margin-bottom: 1px;">Guide Name</label>
                            <input type="text" class="form-control form-control-sm" id="guideName" placeholder="Guide name">
                        </div>
                        <div class="col-1" id="guideHoursField" style="display: none;">
                            <label class="form-label small" style="margin-bottom: 1px;">Hours</label>
                            <input type="number" class="form-control form-control-sm" id="guideHours" value="4" min="1" step="0.5">
                        </div>
                        <div class="col-2" id="guideCostField" style="display: none;">
                            <label class="form-label small" style="margin-bottom: 1px;">Cost</label>
                            <input type="number" class="form-control form-control-sm" id="guideCost" value="0" step="0.01">
                        </div>
                        <div class="col-2" id="guideSellField" style="display: none;">
                            <label class="form-label small" style="margin-bottom: 1px;">Sell</label>
                            <input type="number" class="form-control form-control-sm" id="guideSell" value="0" step="0.01">
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer py-1">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success btn-sm" onclick="saveTour()" id="saveTourBtn">
                    <i class="ri-check-line me-1"></i><span id="saveTourBtnText">Add Tour</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Guide Modal -->
<div class="modal fade" id="guideModal" tabindex="-1" aria-labelledby="guideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title mb-0" id="guideModalLabel">
                    <i class="ri-user-star-line me-2"></i><span id="guideModalTitleText">Add Guide</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 8px 12px;">
                <div class="row g-1 mb-1">
                    <div class="col-4">
                        <label class="form-label small" style="margin-bottom: 1px;">Date/Time</label>
                        <input type="datetime-local" class="form-control form-control-sm" id="guideModalDateTime">
                    </div>
                    <div class="col-5">
                        <label class="form-label small" style="margin-bottom: 1px;">Tour/Activity</label>
                        <input type="text" class="form-control form-control-sm" id="guideModalTourName" placeholder="Tour name">
                    </div>
                    <div class="col-3">
                        <label class="form-label small" style="margin-bottom: 1px;">Language</label>
                        <select class="form-select form-select-sm" id="guideModalLanguage">
                            <option value="english">English</option>
                            <option value="mandarin">Mandarin</option>
                            <option value="spanish">Spanish</option>
                            <option value="french">French</option>
                            <option value="german">German</option>
                            <option value="japanese">Japanese</option>
                            <option value="korean">Korean</option>
                        </select>
                    </div>
                </div>

                <div class="row g-1 mb-1">
                    <div class="col-5">
                        <label class="form-label small" style="margin-bottom: 1px;">Guide Name</label>
                        <input type="text" class="form-control form-control-sm" id="guideModalName" placeholder="Guide name">
                    </div>
                    <div class="col-2">
                        <label class="form-label small" style="margin-bottom: 1px;">Hours</label>
                        <input type="number" class="form-control form-control-sm" id="guideModalHours" value="4" min="1" step="0.5">
                    </div>
                    <div class="col-2">
                        <label class="form-label small" style="margin-bottom: 1px;">Cost</label>
                        <input type="number" class="form-control form-control-sm" id="guideModalCost" value="0" step="0.01">
                    </div>
                    <div class="col-3">
                        <label class="form-label small" style="margin-bottom: 1px;">Sell</label>
                        <input type="number" class="form-control form-control-sm" id="guideModalSell" value="0" step="0.01">
                    </div>
                </div>
            </div>
            <div class="modal-footer py-1">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success btn-sm" onclick="saveGuide()" id="saveGuideBtn">
                    <i class="ri-check-line me-1"></i><span id="saveGuideBtnText">Add Guide</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Meal/Restaurant Modal -->
<div class="modal fade" id="mealModal" tabindex="-1" aria-labelledby="mealModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 900px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white py-2">
                <h6 class="modal-title mb-0" id="mealModalLabel">
                    <i class="ri-restaurant-line me-2"></i><span id="mealModalTitleText">Add Meal / Restaurant</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 8px 12px;">
                <!-- Meal Selection Form -->
                <div class="row g-1 mb-1">
                    <div class="col-3">
                        <label class="form-label small" style="margin-bottom: 1px;">Destination</label>
                        <select class="form-select form-select-sm" id="mealDestination" onchange="loadRestaurantsByDestination()">
                            <option value="">-- Select Destination --</option>
                            @foreach($destinations as $dest)
                                <option value="{{ $dest->name }}" {{ ($destination ?? '') == $dest->name ? 'selected' : '' }}>{{ $dest->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4">
                        <label class="form-label small" style="margin-bottom: 1px;">Restaurant</label>
                        <select class="form-select form-select-sm" id="restaurantSelect">
                            <option value="">-- Select Restaurant --</option>
                            @foreach($restaurants as $rest)
                                <option value="{{ $rest->id }}" data-name="{{ $rest->name }}">{{ $rest->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-3">
                        <label class="form-label small" style="margin-bottom: 1px;">Date/Time</label>
                        <input type="datetime-local" class="form-control form-control-sm" id="mealDateTime">
                    </div>
                    <div class="col-1">
                        <label class="form-label small" style="margin-bottom: 1px;">A.Qty</label>
                        <input type="number" class="form-control form-control-sm" id="mealAdultsQty" value="2" min="0">
                    </div>
                    <div class="col-1">
                        <label class="form-label small" style="margin-bottom: 1px;">C.Qty</label>
                        <input type="number" class="form-control form-control-sm" id="mealChildQty" value="0" min="0">
                    </div>
                </div>

                <div class="row g-1 mb-1">
                    <div class="col-2">
                        <label class="form-label small" style="margin-bottom: 1px;">Adult Cost</label>
                        <input type="number" class="form-control form-control-sm" id="mealAdultCost" value="0" step="0.01">
                    </div>
                    <div class="col-2">
                        <label class="form-label small" style="margin-bottom: 1px;">Adult Sell</label>
                        <input type="number" class="form-control form-control-sm" id="mealAdultSell" value="0" step="0.01">
                    </div>
                    <div class="col-2">
                        <label class="form-label small" style="margin-bottom: 1px;">Child Cost</label>
                        <input type="number" class="form-control form-control-sm" id="mealChildCost" value="0" step="0.01">
                    </div>
                    <div class="col-2">
                        <label class="form-label small" style="margin-bottom: 1px;">Child Sell</label>
                        <input type="number" class="form-control form-control-sm" id="mealChildSell" value="0" step="0.01">
                    </div>
                </div>

                <!-- Transfer Section for Meals -->
                <div class="border rounded p-1 mb-1" id="mealTransferSection" style="background: #f8f9fa;">
                    <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 3px;">
                        <h6 class="mb-0" style="font-size: 10px; font-weight: 600;">Transfer Options</h6>
                    </div>
                    
                    <div class="row g-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Required?</label>
                            <select class="form-select form-select-sm" id="mealTransferRequired" onchange="toggleMealTransferFields()">
                                <option value="no">No</option>
                                <option value="yes">Yes</option>
                            </select>
                        </div>
                        <div class="col-2" id="mealTransferTypeField" style="display: none;">
                            <label class="form-label small" style="margin-bottom: 1px;">Type</label>
                            <select class="form-select form-select-sm" id="mealTransferType">
                                <option value="private">Private</option>
                                <option value="sic">SIC</option>
                            </select>
                        </div>
                        <div class="col-2" id="mealTransferWayField" style="display: none;">
                            <label class="form-label small" style="margin-bottom: 1px;">Way</label>
                            <select class="form-select form-select-sm" id="mealTransferWay">
                                <option value="one-way">One Way</option>
                                <option value="both-way">Both Way</option>
                            </select>
                        </div>
                        <div class="col-2" id="mealVehicleTypeField" style="display: none;">
                            <label class="form-label small" style="margin-bottom: 1px;">Vehicle</label>
                            <select class="form-select form-select-sm" id="mealVehicleType">
                                <option value="sedan">Sedan</option>
                                <option value="combi">Combi</option>
                                <option value="van">Van</option>
                                <option value="bus">Bus</option>
                            </select>
                        </div>
                        <div class="col-2" id="mealTransferCostFields" style="display: none;">
                            <label class="form-label small" style="margin-bottom: 1px;">Cost</label>
                            <input type="number" class="form-control form-control-sm" id="mealTransferCost" value="0" step="0.01">
                        </div>
                        <div class="col-2" id="mealTransferSellFields" style="display: none;">
                            <label class="form-label small" style="margin-bottom: 1px;">Sell</label>
                            <input type="number" class="form-control form-control-sm" id="mealTransferSell" value="0" step="0.01">
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer py-1">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success btn-sm" onclick="saveMeal()" id="saveMealBtn">
                    <i class="ri-check-line me-1"></i><span id="saveMealBtnText">Add Meal</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Transfer Package Modal -->
<div class="modal fade" id="transferModal" tabindex="-1" aria-labelledby="transferModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 700px;">
        <div class="modal-content">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title mb-0" id="transferModalLabel">
                    <i class="ri-car-line me-2"></i><span id="transferModalTitleText">Add Transfer Package</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 8px 12px;">
                <!-- Transport Mode Selection -->
                <div class="border rounded p-2 mb-2" style="background: #f8f9fa;">
                    <label class="form-label small mb-1" style="font-weight: 600;">Transport Mode</label>
                    <div class="d-flex gap-3 align-items-center">
                        <label class="d-flex align-items-center" style="cursor: pointer;">
                            <input type="radio" name="transferMode" value="local" checked style="margin-right: 5px;" onchange="switchTransferMode('local')">
                            <i class="ri-car-line" style="font-size: 24px; color: #666;"></i>
                            <span class="ms-1" style="font-size: 11px;">Local Transfer</span>
                        </label>
                        <label class="d-flex align-items-center" style="cursor: pointer;">
                            <input type="radio" name="transferMode" value="flight" style="margin-right: 5px;" onchange="switchTransferMode('flight')">
                            <i class="ri-flight-takeoff-line" style="font-size: 24px; color: #666;"></i>
                            <span class="ms-1" style="font-size: 11px;">Flight</span>
                        </label>
                        <label class="d-flex align-items-center" style="cursor: pointer;">
                            <input type="radio" name="transferMode" value="cruise" style="margin-right: 5px;" onchange="switchTransferMode('cruise')">
                            <i class="ri-ship-line" style="font-size: 24px; color: #666;"></i>
                            <span class="ms-1" style="font-size: 11px;">Cruise</span>
                        </label>
                        <label class="d-flex align-items-center" style="cursor: pointer;">
                            <input type="radio" name="transferMode" value="train" style="margin-right: 5px;" onchange="switchTransferMode('train')">
                            <i class="ri-train-line" style="font-size: 24px; color: #666;"></i>
                            <span class="ms-1" style="font-size: 11px;">Train</span>
                        </label>
                        <label class="d-flex align-items-center" style="cursor: pointer;">
                            <input type="radio" name="transferMode" value="bus" style="margin-right: 5px;" onchange="switchTransferMode('bus')">
                            <i class="ri-bus-line" style="font-size: 24px; color: #666;"></i>
                            <span class="ms-1" style="font-size: 11px;">Bus</span>
                        </label>
                    </div>
                </div>

                <!-- Local Transfer Form -->
                <div id="localTransferForm" class="transfer-mode-form">
                    <div class="row g-1 mb-1">
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 1px;">Date/Time</label>
                            <input type="datetime-local" class="form-control form-control-sm" id="localDateTime">
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 1px;">Pickup Location</label>
                            <input type="text" class="form-control form-control-sm" id="localPickup" placeholder="Pickup location">
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 1px;">Drop Location</label>
                            <input type="text" class="form-control form-control-sm" id="localDrop" placeholder="Drop location">
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 1px;">Destination</label>
                            <input type="text" class="form-control form-control-sm" id="localDestination" placeholder="Destination">
                        </div>
                    </div>
                    <div class="row g-1 mb-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Vehicle Type</label>
                            <select class="form-select form-select-sm" id="localVehicleType">
                                <option value="sedan">Sedan</option>
                                <option value="combi">Combi</option>
                                <option value="van">Van</option>
                                <option value="bus">Bus</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Type</label>
                            <select class="form-select form-select-sm" id="localType">
                                <option value="private">Private</option>
                                <option value="sic">SIC</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Way</label>
                            <select class="form-select form-select-sm" id="localWay">
                                <option value="one-way">One Way</option>
                                <option value="both-way">Both Way</option>
                            </select>
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Adults</label>
                            <input type="number" class="form-control form-control-sm" id="localAdults" value="2" min="0">
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Child</label>
                            <input type="number" class="form-control form-control-sm" id="localChild" value="0" min="0">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Cost</label>
                            <input type="number" class="form-control form-control-sm" id="localCost" value="0" step="0.01">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Sell</label>
                            <input type="number" class="form-control form-control-sm" id="localSell" value="0" step="0.01">
                        </div>
                    </div>
                    <div class="row g-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">
                                <input type="checkbox" id="localTaxIncluded"> Tax Included
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Flight Form -->
                <div id="flightForm" class="transfer-mode-form" style="display: none;">
                    <div class="row g-1 mb-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Depart From</label>
                            <input type="text" class="form-control form-control-sm" id="flightDepartFrom" placeholder="e.g., SIN">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Destination</label>
                            <input type="text" class="form-control form-control-sm" id="flightDestination" placeholder="e.g., KUL">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Trip Type</label>
                            <select class="form-select form-select-sm" id="flightTripType">
                                <option value="return">Return</option>
                                <option value="single">Single Trip</option>
                            </select>
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 1px;">Date of Departure</label>
                            <input type="datetime-local" class="form-control form-control-sm" id="flightDepartureDate">
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 1px;">Date of Return</label>
                            <input type="datetime-local" class="form-control form-control-sm" id="flightReturnDate">
                        </div>
                    </div>
                    <div class="row g-1 mb-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Airline</label>
                            <input type="text" class="form-control form-control-sm" id="flightAirline" placeholder="Any Airline">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Flight No</label>
                            <input type="text" class="form-control form-control-sm" id="flightNumber" placeholder="Flight No">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Operator</label>
                            <select class="form-select form-select-sm" id="flightOperator">
                                <option value="">Select Operator</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Seating Class</label>
                            <select class="form-select form-select-sm" id="flightClass">
                                <option value="economy">Economy</option>
                                <option value="business">Business</option>
                                <option value="first">First Class</option>
                            </select>
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Adults</label>
                            <input type="number" class="form-control form-control-sm" id="flightAdults" value="2" min="0">
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Child</label>
                            <input type="number" class="form-control form-control-sm" id="flightChild" value="0" min="0">
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Cost</label>
                            <input type="number" class="form-control form-control-sm" id="flightCost" value="0" step="0.01">
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Sell</label>
                            <input type="number" class="form-control form-control-sm" id="flightSell" value="0" step="0.01">
                        </div>
                    </div>
                    <div class="row g-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">
                                <input type="checkbox" id="flightTaxIncluded"> Tax Included
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Cruise Form -->
                <div id="cruiseForm" class="transfer-mode-form" style="display: none;">
                    <div class="row g-1 mb-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Depart From</label>
                            <input type="text" class="form-control form-control-sm" id="cruiseDepartFrom" placeholder="e.g., SIN">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">From Terminal</label>
                            <input type="text" class="form-control form-control-sm" id="cruiseFromTerminal" placeholder="Terminal">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">By</label>
                            <select class="form-select form-select-sm" id="cruiseBy">
                                <option value="cruise">Cruise</option>
                                <option value="ferry">Ferry</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Cruise Type</label>
                            <select class="form-select form-select-sm" id="cruiseType">
                                <option value="">Select Cruise Type</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Vessel</label>
                            <select class="form-select form-select-sm" id="cruiseVessel">
                                <option value="">Select Vessel</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Arrival To</label>
                            <select class="form-select form-select-sm" id="cruiseArrivalTo">
                                <option value="">Select Arrival To</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-1 mb-1">
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 1px;">Date of Departure</label>
                            <input type="datetime-local" class="form-control form-control-sm" id="cruiseDepartureDate">
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 1px;">Date of Arrival</label>
                            <input type="datetime-local" class="form-control form-control-sm" id="cruiseArrivalDate">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Operator</label>
                            <input type="text" class="form-control form-control-sm" id="cruiseOperator" placeholder="e.g., BINTAN FERRY">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Cabin Class</label>
                            <select class="form-select form-select-sm" id="cruiseCabinClass">
                                <option value="economy">Economy</option>
                                <option value="business">Business</option>
                                <option value="suite">Suite</option>
                            </select>
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Adults</label>
                            <input type="number" class="form-control form-control-sm" id="cruiseAdults" value="2" min="0">
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Child</label>
                            <input type="number" class="form-control form-control-sm" id="cruiseChild" value="0" min="0">
                        </div>
                    </div>
                    <div class="row g-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Cost</label>
                            <input type="number" class="form-control form-control-sm" id="cruiseCost" value="0" step="0.01">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Sell</label>
                            <input type="number" class="form-control form-control-sm" id="cruiseSell" value="0" step="0.01">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">
                                <input type="checkbox" id="cruiseTaxIncluded"> Tax Included
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Train Form -->
                <div id="trainForm" class="transfer-mode-form" style="display: none;">
                    <div class="row g-1 mb-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Depart From</label>
                            <input type="text" class="form-control form-control-sm" id="trainDepartFrom" placeholder="e.g., SIN">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Destination</label>
                            <input type="text" class="form-control form-control-sm" id="trainDestination" placeholder="e.g., KUL">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Trip Type</label>
                            <select class="form-select form-select-sm" id="trainTripType">
                                <option value="return">Return</option>
                                <option value="single">Single Trip</option>
                            </select>
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 1px;">Date of Departure</label>
                            <input type="datetime-local" class="form-control form-control-sm" id="trainDepartureDate">
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 1px;">Date of Return</label>
                            <input type="datetime-local" class="form-control form-control-sm" id="trainReturnDate">
                        </div>
                    </div>
                    <div class="row g-1 mb-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Operator</label>
                            <select class="form-select form-select-sm" id="trainOperator">
                                <option value="">Select Operator</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Seating Class</label>
                            <select class="form-select form-select-sm" id="trainClass">
                                <option value="1st-class">1st Class</option>
                                <option value="2nd-class">2nd Class</option>
                                <option value="sleeper">Sleeper</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Station Transfer?</label>
                            <select class="form-select form-select-sm" id="trainStationTransfer">
                                <option value="">Select</option>
                                <option value="combi">Combi</option>
                                <option value="van">Van</option>
                            </select>
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Adults</label>
                            <input type="number" class="form-control form-control-sm" id="trainAdults" value="2" min="0">
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Child</label>
                            <input type="number" class="form-control form-control-sm" id="trainChild" value="0" min="0">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Cost</label>
                            <input type="number" class="form-control form-control-sm" id="trainCost" value="0" step="0.01">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Sell</label>
                            <input type="number" class="form-control form-control-sm" id="trainSell" value="0" step="0.01">
                        </div>
                    </div>
                    <div class="row g-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">
                                <input type="checkbox" id="trainTaxIncluded"> Tax Included
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Bus Form -->
                <div id="busForm" class="transfer-mode-form" style="display: none;">
                    <div class="row g-1 mb-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Depart From</label>
                            <input type="text" class="form-control form-control-sm" id="busDepartFrom" placeholder="e.g., SIN">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Destination</label>
                            <input type="text" class="form-control form-control-sm" id="busDestination" placeholder="e.g., KUL">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Trip Type</label>
                            <select class="form-select form-select-sm" id="busTripType">
                                <option value="return">Return</option>
                                <option value="single">Single Trip</option>
                            </select>
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 1px;">Date of Departure</label>
                            <input type="datetime-local" class="form-control form-control-sm" id="busDepartureDate">
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 1px;">Date of Return</label>
                            <input type="datetime-local" class="form-control form-control-sm" id="busReturnDate">
                        </div>
                    </div>
                    <div class="row g-1 mb-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Operator</label>
                            <input type="text" class="form-control form-control-sm" id="busOperator" placeholder="e.g., STARMART COACH">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Seating Class</label>
                            <select class="form-select form-select-sm" id="busClass">
                                <option value="executive">Executive</option>
                                <option value="standard">Standard</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Bus Station Transfer?</label>
                            <select class="form-select form-select-sm" id="busStationTransfer">
                                <option value="">Select</option>
                                <option value="combi">Combi</option>
                                <option value="van">Van</option>
                            </select>
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Adults</label>
                            <input type="number" class="form-control form-control-sm" id="busAdults" value="2" min="0">
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Child</label>
                            <input type="number" class="form-control form-control-sm" id="busChild" value="0" min="0">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Cost</label>
                            <input type="number" class="form-control form-control-sm" id="busCost" value="0" step="0.01">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Sell</label>
                            <input type="number" class="form-control form-control-sm" id="busSell" value="0" step="0.01">
                        </div>
                    </div>
                    <div class="row g-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">
                                <input type="checkbox" id="busTaxIncluded"> Tax Included
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-1">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success btn-sm" onclick="saveTransferPackage()" id="saveTransferBtn">
                    <i class="ri-check-line me-1"></i><span id="saveTransferBtnText">Add Transfer</span>
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
    .table th {
        font-size: 0.6rem !important;
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
    
    /* Tour Modal Compact Styling */
    #tourModal .form-label.small {
        font-size: 10px;
        font-weight: 500;
        margin-bottom: 2px;
    }
    #tourModal .form-control-sm,
    #tourModal .form-select-sm {
        font-size: 10px;
        padding: 2px 4px;
        height: 22px;
        min-height: 0.375rem;
        border: 1px solid #ced4da;
    }
    #tourModal .form-check-input {
        width: 14px;
        height: 14px;
        margin-top: 2px;
    }
    #tourModal .row.g-1 {
        row-gap: 2px !important;
        column-gap: 4px !important;
    }
    #tourModal .modal-body {
        padding: 8px 12px;
    }
    #tourModal .modal-header {
        padding: 6px 12px;
    }
    #tourModal .modal-footer {
        padding: 6px 12px;
    }
    #tourModal .border.rounded {
        padding: 4px 6px !important;
        margin-bottom: 4px !important;
    }
    #tourModal h6 {
        font-size: 10px !important;
        margin-bottom: 2px !important;
    }
    
    /* Meal Modal Compact Styling */
    #mealModal .form-label.small {
        font-size: 10px;
        font-weight: 500;
        margin-bottom: 2px;
    }
    #mealModal .form-control-sm,
    #mealModal .form-select-sm {
        font-size: 10px;
        padding: 2px 4px;
        height: 22px;
        min-height: 0.375rem;
        border: 1px solid #ced4da;
    }
    #mealModal .row.g-1 {
        row-gap: 2px !important;
        column-gap: 4px !important;
    }
    #mealModal .modal-body {
        padding: 8px 12px;
    }
    #mealModal .modal-header {
        padding: 6px 12px;
    }
    #mealModal .modal-footer {
        padding: 6px 12px;
    }
    #mealModal .border.rounded {
        padding: 4px 6px !important;
        margin-bottom: 4px !important;
    }
    #mealModal h6 {
        font-size: 10px !important;
        margin-bottom: 2px !important;
    }
    
    /* Guide Modal Compact Styling */
    #guideModal .form-label.small {
        font-size: 10px;
        font-weight: 500;
        margin-bottom: 2px;
    }
    #guideModal .form-control-sm,
    #guideModal .form-select-sm {
        font-size: 10px;
        padding: 2px 4px;
        height: 22px;
        min-height: 0.375rem;
        border: 1px solid #ced4da;
    }
    #guideModal .row.g-1 {
        row-gap: 2px !important;
        column-gap: 4px !important;
    }
    #guideModal .modal-body {
        padding: 8px 12px;
    }
    #guideModal .modal-header {
        padding: 6px 12px;
    }
    #guideModal .modal-footer {
        padding: 6px 12px;
    }

    /* Transfer Modal Styling */
    #transferModal .form-label.small {
        font-size: 10px;
        font-weight: 500;
        margin-bottom: 2px;
    }
    #transferModal .form-control-sm,
    #transferModal .form-select-sm {
        font-size: 10px;
        padding: 2px 4px;
        height: 22px;
        line-height: 1.2;
    }
    #transferModal .row.g-1 {
        row-gap: 2px !important;
        column-gap: 4px !important;
    }
    #transferModal .modal-body {
        padding: 8px 12px;
    }
    #transferModal .modal-header {
        padding: 6px 12px;
    }
    #transferModal .modal-footer {
        padding: 6px 12px;
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

    /* Arrival/Departure table styling */
    #arrivalDepartureTable input[type="number"],
    #arrivalDepartureTable input[type="text"] {
        width: 55px;
        padding: 1px 3px;
        border: 1px solid #ced4da;
        border-radius: 2px;
        font-size: 10px;
        height: 20px;
    }

    /* Tour table styling */
    #tourTable input[type="number"],
    #tourTable input[type="text"],
    #tourTable input[type="checkbox"] {
        padding: 1px 3px;
        border: 1px solid #ced4da;
        border-radius: 2px;
        font-size: 10px;
        height: 20px;
    }

    #tourTable input[type="number"] {
        width: 55px;
        text-align: center;
    }

    /* Meal table styling */
    #mealTable input[type="number"],
    #mealTable input[type="text"] {
        padding: 1px 3px;
        border: 1px solid #ced4da;
        border-radius: 2px;
        font-size: 10px;
        height: 20px;
        width: 55px;
        text-align: center;
    }

    /* Transfer table styling */
    #transferTable input[type="number"],
    #transferTable input[type="text"],
    #transferTable input[type="checkbox"] {
        padding: 1px 3px;
        border: 1px solid #ced4da;
        border-radius: 2px;
        font-size: 10px;
        height: 20px;
    }

    #transferTable input[type="number"],
    #transferTable input[type="text"] {
        text-align: center;
    }

    /* Guide table styling */
    #guideTable input[type="number"],
    #guideTable input[type="text"] {
        padding: 1px 3px;
        border: 1px solid #ced4da;
        border-radius: 2px;
        font-size: 10px;
        height: 20px;
    }

    #guideTable input[type="number"] {
        text-align: center;
    }

    /* Select2 styling for port dropdowns in modal */
    .select2-container--default .select2-selection--single {
        height: 22px !important;
        min-height: 22px !important;
        padding: 0px 4px !important;
        font-size: 10px !important;
        border: 1px solid #ced4da !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 20px !important;
        padding-left: 4px !important;
        font-size: 10px !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 20px !important;
        top: 1px !important;
    }
    
    .select2-dropdown {
        font-size: 10px !important;
    }
    
    .select2-results__option {
        padding: 4px 8px !important;
        font-size: 10px !important;
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
    let arrivalDepartureList = [];
    let tourList = [];
    let guideList = [];
    let transferList = [];
    let mealList = [];
    
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
        window.editingAccommodationIndex = null;
        window.isArrivalDepartureOnlyMode = false;
        window.editingArrivalDepartureIndex = null;
        window.editingArrivalDepartureType = null;
        document.getElementById('selectedHotelsList').innerHTML = '';
        document.getElementById('noHotelsMessage').style.display = 'block';
        document.getElementById('addButtonText').textContent = 'Add to List';
        document.getElementById('saveAccommodationBtnText').textContent = 'Add Accommodation';
        
        // Show hotel sections
        document.getElementById('hotelSelectionRow1').style.display = 'flex';
        document.getElementById('hotelSelectionRow2').style.display = 'flex';
        document.getElementById('selectedHotelsSection').style.display = 'block';
        
        // Show arrival/departure section
        const arrivalDepartureSection = document.getElementById('arrivalDepartureSection');
        if (arrivalDepartureSection) {
            arrivalDepartureSection.style.display = 'block';
        }
        
        // Show all arrival/departure fields
        document.getElementById('arrivalDateTimeField').style.display = 'block';
        document.getElementById('arrivalPortField').style.display = 'block';
        document.getElementById('arrivalFlightNoField').style.display = 'block';
        document.getElementById('departureDateTimeField').style.display = 'block';
        document.getElementById('departurePortField').style.display = 'block';
        document.getElementById('departureFlightNoField').style.display = 'block';
        
        // Reset modal title
        document.getElementById('modalTitleIcon').className = 'ri-hotel-line me-2';
        document.getElementById('modalTitleText').textContent = 'Select Hotels';
        document.getElementById('arrivalDepartureSectionTitle').textContent = 'Arrival/Departure Flight Information';
        
        // Reset form
        resetHotelForm();
        
        // Set initial min date for checkout
        updateCheckOutMinDate();
        
        const accommodationModal = new bootstrap.Modal(document.getElementById('accommodationModal'));
        accommodationModal.show();
        
        // Populate existing arrival/departure data if available AFTER modal is shown
        // Look for any standalone arrival/departure entries (not linked to accommodation)
        setTimeout(() => {
            const standaloneArrival = arrivalDepartureList.find(item => item.type === 'Arrival' && item.accommodationIndex === null);
            const standaloneDeparture = arrivalDepartureList.find(item => item.type === 'Departure' && item.accommodationIndex === null);
            
            // If standalone entries exist, populate them
            if (standaloneArrival) {
                document.getElementById('arrivalDateTime').value = standaloneArrival.dateTime || '';
                $('#arrivalPort').val(standaloneArrival.portId).trigger('change');
                document.getElementById('arrivalFlightNo').value = standaloneArrival.flightNo || '';
            }
            
            if (standaloneDeparture) {
                document.getElementById('departureDateTime').value = standaloneDeparture.dateTime || '';
                $('#departurePort').val(standaloneDeparture.portId).trigger('change');
                document.getElementById('departureFlightNo').value = standaloneDeparture.flightNo || '';
            }
        }, 200);
        
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
            
            // Get pre-selected agent ID if available
            @if(isset($initialData) && isset($initialData['agent_id']))
            const preSelectedAgentId = {{ $initialData['agent_id'] }};
            @else
            const preSelectedAgentId = null;
            @endif
            
            // Populate agent dropdown
            data.agents.forEach(agent => {
                const option = document.createElement('option');
                option.value = agent.agent_id;
                option.textContent = agent.name;
                if (preSelectedAgentId && agent.agent_id == preSelectedAgentId) {
                    option.selected = true;
                }
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
        const mealPlan = document.getElementById('mealPlan').value;
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
                    mealPlan: mealPlan,
                    supplement: '',
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
                mealPlan: mealPlan,
                supplement: '',
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
        
        // Group hotels by hotel name, room type, bed type, and dates
        const groupedHotels = {};
        selectedHotelsTemp.forEach(hotel => {
            const key = `${hotel.hotelName}|${hotel.roomType}|${hotel.bedType}|${hotel.checkIn}|${hotel.checkOut}`;
            if (!groupedHotels[key]) {
                groupedHotels[key] = {
                    ...hotel,
                    ids: [hotel.id],
                    totalRooms: parseInt(hotel.rooms)
                };
            } else {
                groupedHotels[key].ids.push(hotel.id);
                groupedHotels[key].totalRooms += parseInt(hotel.rooms);
            }
        });
        
        // Convert to array and limit to 4 for display
        const displayHotels = Object.values(groupedHotels).slice(0, 4);
        const hasMore = Object.keys(groupedHotels).length > 4;
        
        tbody.innerHTML = displayHotels.map(hotel => `
            <tr>
                <td>
                    ${hotel.hotelName}<br>
                    <small class="text-muted">${hotel.roomType} | ${hotel.bedType}</small>
                </td>
                <td style="white-space: nowrap; font-size: 9px;">
                    ${formatDate(hotel.checkIn)} - ${formatDate(hotel.checkOut)}<br>
                    <small class="text-muted">${hotel.nights} nights</small>
                </td>
                <td style="text-align: center;">${hotel.totalRooms}</td>
                <td>
                    ${hotel.ids.map(id => `
                        <button type="button" class="btn btn-xs btn-warning me-1" onclick="editHotelFromTempList(${id})" title="Edit">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-danger" onclick="removeFromTempList(${id})" title="Delete">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    `).join('<br>')}
                </td>
            </tr>
        `).join('') + (hasMore ? `<tr><td colspan="4" class="text-center text-muted" style="font-size: 9px;">... ${Object.keys(groupedHotels).length - 4} more hotel(s)</td></tr>` : '');
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

    // Save arrival/departure only (without accommodation)
    function saveArrivalDepartureOnly() {
        const arrivalDateTime = document.getElementById('arrivalDateTime').value;
        const arrivalPortSelect = document.getElementById('arrivalPort');
        const arrivalPortId = arrivalPortSelect.value;
        const arrivalPortName = arrivalPortSelect.selectedOptions[0]?.text || '';
        const arrivalFlightNo = document.getElementById('arrivalFlightNo').value;
        const departureDateTime = document.getElementById('departureDateTime').value;
        const departurePortSelect = document.getElementById('departurePort');
        const departurePortId = departurePortSelect.value;
        const departurePortName = departurePortSelect.selectedOptions[0]?.text || '';
        const departureFlightNo = document.getElementById('departureFlightNo').value;

        // Get pax numbers from header
        const adults = parseInt(document.querySelector('.customer-details input[type="number"][value="2"]')?.value || 2);
        const child = parseInt(document.querySelector('.customer-details input[type="number"][value="1"]')?.value || 1);
        const infant = parseInt(document.querySelector('.customer-details input[type="number"][value="0"]')?.value || 0);

        // Check if editing existing entry
        if (window.editingArrivalDepartureIndex !== undefined && window.editingArrivalDepartureIndex !== null) {
            const index = window.editingArrivalDepartureIndex;
            const item = arrivalDepartureList[index];
            
            if (item.type === 'Arrival' && arrivalDateTime && arrivalPortId) {
                arrivalDepartureList[index] = {
                    ...item,
                    dateTime: arrivalDateTime,
                    portId: arrivalPortId,
                    portName: arrivalPortName,
                    flightNo: arrivalFlightNo || '-'
                };
            } else if (item.type === 'Departure' && departureDateTime && departurePortId) {
                arrivalDepartureList[index] = {
                    ...item,
                    dateTime: departureDateTime,
                    portId: departurePortId,
                    portName: departurePortName,
                    flightNo: departureFlightNo || '-'
                };
            }
            
            window.editingArrivalDepartureIndex = null;
        } else {
            // Add new standalone entries
            if (arrivalDateTime && arrivalPortId) {
                arrivalDepartureList.push({
                    id: Date.now(),
                    dateTime: arrivalDateTime,
                    portId: arrivalPortId,
                    portName: arrivalPortName,
                    flightNo: arrivalFlightNo || '-',
                    type: 'Arrival',
                    adultsQty: adults,
                    adultCost: 0,
                    adultSell: 0,
                    childQty: child,
                    childCost: 0,
                    childSell: 0,
                    infantQty: infant,
                    amount: 0,
                    supplement: '',
                    accommodationIndex: null
                });
            }

            if (departureDateTime && departurePortId) {
                arrivalDepartureList.push({
                    id: Date.now() + 1,
                    dateTime: departureDateTime,
                    portId: departurePortId,
                    portName: departurePortName,
                    flightNo: departureFlightNo || '-',
                    type: 'Departure',
                    adultsQty: adults,
                    adultCost: 0,
                    adultSell: 0,
                    childQty: child,
                    childCost: 0,
                    childSell: 0,
                    infantQty: infant,
                    amount: 0,
                    supplement: '',
                    accommodationIndex: null
                });
            }
        }

        // Update table
        updateArrivalDepartureTable();

        // Close modal
        const accommodationModal = bootstrap.Modal.getInstance(document.getElementById('accommodationModal'));
        accommodationModal.hide();

        // Clear fields
        document.getElementById('arrivalDateTime').value = '';
        $('#arrivalPort').val('').trigger('change');
        document.getElementById('arrivalFlightNo').value = '';
        document.getElementById('departureDateTime').value = '';
        $('#departurePort').val('').trigger('change');
        document.getElementById('departureFlightNo').value = '';
        
        window.isArrivalDepartureOnlyMode = false;
    }

    // Save selected hotels to main accommodation table
    function saveSelectedHotels() {
        // Check if we're in arrival/departure only mode
        if (window.isArrivalDepartureOnlyMode) {
            saveArrivalDepartureOnly();
            return;
        }
        
        // Check if we're editing an existing accommodation
        if (window.editingAccommodationIndex !== undefined && window.editingAccommodationIndex !== null) {
            // Get the form values directly
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
            const mealPlan = document.getElementById('mealPlan').value;
            const roomPrice = document.getElementById('roomPrice').value;
            
            // Get room data
            const selectedOption = bedTypeSelect.options[bedTypeSelect.selectedIndex];
            const roomDataStr = selectedOption.getAttribute('data-room-data');
            const roomData = JSON.parse(roomDataStr);
            const bedType = roomData.bed_type;
            const maxOccupancy = roomData.max_occupancy;
            
            // Get arrival/departure data
            const arrivalDateTime = document.getElementById('arrivalDateTime').value;
            const arrivalPortSelect = document.getElementById('arrivalPort');
            const arrivalPortId = arrivalPortSelect.value;
            const arrivalPortName = arrivalPortSelect.selectedOptions[0]?.text || '';
            const arrivalFlightNo = document.getElementById('arrivalFlightNo').value;
            const departureDateTime = document.getElementById('departureDateTime').value;
            const departurePortSelect = document.getElementById('departurePort');
            const departurePortId = departurePortSelect.value;
            const departurePortName = departurePortSelect.selectedOptions[0]?.text || '';
            const departureFlightNo = document.getElementById('departureFlightNo').value;
            
            // Update the existing accommodation
            accommodationList[window.editingAccommodationIndex] = {
                ...accommodationList[window.editingAccommodationIndex],
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
                mealPlan: mealPlan,
                roomPrice: roomPrice,
                // Store arrival/departure info with accommodation
                arrivalDateTime: arrivalDateTime,
                arrivalPortId: arrivalPortId,
                arrivalPortName: arrivalPortName,
                arrivalFlightNo: arrivalFlightNo,
                departureDateTime: departureDateTime,
                departurePortId: departurePortId,
                departurePortName: departurePortName,
                departureFlightNo: departureFlightNo
            };
            
            // Update arrival/departure list
            const adults = parseInt(document.querySelector('.customer-details input[type="number"][value="2"]')?.value || 2);
            const child = parseInt(document.querySelector('.customer-details input[type="number"][value="1"]')?.value || 1);
            const infant = parseInt(document.querySelector('.customer-details input[type="number"][value="0"]')?.value || 0);
            
            // Remove old arrival/departure entries for this accommodation
            // IMPORTANT: Save the IDs BEFORE modifying the accommodation object
            const oldHotel = accommodationList[window.editingAccommodationIndex];
            const oldArrivalDepartureIds = oldHotel.arrivalDepartureIds ? [...oldHotel.arrivalDepartureIds] : [];
            
            console.log('Removing old arrival/departure entries for accommodation index:', window.editingAccommodationIndex);
            console.log('Old arrival/departure IDs:', oldArrivalDepartureIds);
            
            // Remove entries by both ID and accommodation index to ensure clean update
            arrivalDepartureList = arrivalDepartureList.filter(item => {
                const matchesId = oldArrivalDepartureIds.includes(item.id);
                const matchesIndex = item.accommodationIndex === window.editingAccommodationIndex;
                return !(matchesId || matchesIndex);
            });
            
            // Add new arrival/departure entries
            const newArrivalDepartureIds = [];
            
            if (arrivalDateTime && arrivalPortId) {
                const arrivalId = Date.now();
                arrivalDepartureList.push({
                    id: arrivalId,
                    dateTime: arrivalDateTime,
                    portId: arrivalPortId,
                    portName: arrivalPortName,
                    flightNo: arrivalFlightNo || '-',
                    type: 'Arrival',
                    adultsQty: adults,
                    adultCost: 0,
                    adultSell: 0,
                    childQty: child,
                    childCost: 0,
                    childSell: 0,
                    infantQty: infant,
                    amount: 0,
                    supplement: '',
                    accommodationIndex: window.editingAccommodationIndex
                });
                newArrivalDepartureIds.push(arrivalId);
            }
            
            if (departureDateTime && departurePortId) {
                const departureId = Date.now() + 1;
                arrivalDepartureList.push({
                    id: departureId,
                    dateTime: departureDateTime,
                    portId: departurePortId,
                    portName: departurePortName,
                    flightNo: departureFlightNo || '-',
                    type: 'Departure',
                    adultsQty: adults,
                    adultCost: 0,
                    adultSell: 0,
                    childQty: child,
                    childCost: 0,
                    childSell: 0,
                    infantQty: infant,
                    amount: 0,
                    supplement: '',
                    accommodationIndex: window.editingAccommodationIndex
                });
                newArrivalDepartureIds.push(departureId);
            }
            
            // Store the IDs with the accommodation
            accommodationList[window.editingAccommodationIndex].arrivalDepartureIds = newArrivalDepartureIds;
            
            // Update tables
            updateArrivalDepartureTable();
            
            // Clear the editing flag
            window.editingAccommodationIndex = null;
            
            // Update table
            updateAccommodationTable();
            
            // Close modal
            const accommodationModal = bootstrap.Modal.getInstance(document.getElementById('accommodationModal'));
            accommodationModal.hide();
            
            return;
        }
        
        // Normal flow - adding new hotels
        if (selectedHotelsTemp.length === 0) {
            alert('Please add at least one hotel');
            return;
        }

        // Process Arrival/Departure information
        const arrivalDateTime = document.getElementById('arrivalDateTime').value;
        const arrivalPortSelect = document.getElementById('arrivalPort');
        const arrivalPortId = arrivalPortSelect.value;
        const arrivalPortName = arrivalPortSelect.selectedOptions[0]?.text || '';
        const arrivalFlightNo = document.getElementById('arrivalFlightNo').value;
        const departureDateTime = document.getElementById('departureDateTime').value;
        const departurePortSelect = document.getElementById('departurePort');
        const departurePortId = departurePortSelect.value;
        const departurePortName = departurePortSelect.selectedOptions[0]?.text || '';
        const departureFlightNo = document.getElementById('departureFlightNo').value;

        // Get pax numbers from header
        const adults = parseInt(document.querySelector('.customer-details input[type="number"][value="2"]')?.value || 2);
        const child = parseInt(document.querySelector('.customer-details input[type="number"][value="1"]')?.value || 1);
        const infant = parseInt(document.querySelector('.customer-details input[type="number"][value="0"]')?.value || 0);

        const newArrivalDepartureIds = [];

        // Store arrival/departure data with each hotel in temp list first
        selectedHotelsTemp = selectedHotelsTemp.map(hotel => ({
            ...hotel,
            arrivalDateTime: arrivalDateTime,
            arrivalPortId: arrivalPortId,
            arrivalPortName: arrivalPortName,
            arrivalFlightNo: arrivalFlightNo,
            departureDateTime: departureDateTime,
            departurePortId: departurePortId,
            departurePortName: departurePortName,
            departureFlightNo: departureFlightNo,
            arrivalDepartureIds: []
        }));

        // Update standalone arrival/departure entries if they exist
        const standaloneArrival = arrivalDepartureList.find(item => item.type === 'Arrival' && item.accommodationIndex === null);
        const standaloneDeparture = arrivalDepartureList.find(item => item.type === 'Departure' && item.accommodationIndex === null);
        
        if (standaloneArrival && arrivalDateTime && arrivalPortId) {
            // Update existing standalone arrival
            standaloneArrival.dateTime = arrivalDateTime;
            standaloneArrival.portId = arrivalPortId;
            standaloneArrival.portName = arrivalPortName;
            standaloneArrival.flightNo = arrivalFlightNo || '-';
        } else if (!standaloneArrival && arrivalDateTime && arrivalPortId) {
            // Create new standalone arrival if it doesn't exist
            arrivalDepartureList.push({
                id: Date.now(),
                dateTime: arrivalDateTime,
                portId: arrivalPortId,
                portName: arrivalPortName,
                flightNo: arrivalFlightNo || '-',
                type: 'Arrival',
                adultsQty: adults,
                adultCost: 0,
                adultSell: 0,
                childQty: child,
                childCost: 0,
                childSell: 0,
                infantQty: infant,
                amount: 0,
                supplement: '',
                accommodationIndex: null
            });
        }
        
        if (standaloneDeparture && departureDateTime && departurePortId) {
            // Update existing standalone departure
            standaloneDeparture.dateTime = departureDateTime;
            standaloneDeparture.portId = departurePortId;
            standaloneDeparture.portName = departurePortName;
            standaloneDeparture.flightNo = departureFlightNo || '-';
        } else if (!standaloneDeparture && departureDateTime && departurePortId) {
            // Create new standalone departure if it doesn't exist
            arrivalDepartureList.push({
                id: Date.now() + 1,
                dateTime: departureDateTime,
                portId: departurePortId,
                portName: departurePortName,
                flightNo: departureFlightNo || '-',
                type: 'Departure',
                adultsQty: adults,
                adultCost: 0,
                adultSell: 0,
                childQty: child,
                childCost: 0,
                childSell: 0,
                infantQty: infant,
                amount: 0,
                supplement: '',
                accommodationIndex: null
            });
        }
        
        // Add to main accommodation list
        const startIndex = accommodationList.length;
        accommodationList = [...accommodationList, ...selectedHotelsTemp];
        
        // Now create arrival/departure entries linked to each hotel
        selectedHotelsTemp.forEach((hotel, idx) => {
            const accommodationIdx = startIndex + idx;
            const hotelArrivalDepartureIds = [];
            
            // Add Arrival if provided
            if (arrivalDateTime && arrivalPortId) {
                const arrivalId = Date.now() + idx * 10 + 100;
                const arrival = {
                    id: arrivalId,
                    dateTime: arrivalDateTime,
                    portId: arrivalPortId,
                    portName: arrivalPortName,
                    flightNo: arrivalFlightNo || '-',
                    type: 'Arrival',
                    adultsQty: adults,
                    adultCost: 0,
                    adultSell: 0,
                    childQty: child,
                    childCost: 0,
                    childSell: 0,
                    infantQty: infant,
                    amount: 0,
                    supplement: '',
                    accommodationIndex: accommodationIdx
                };
                arrivalDepartureList.push(arrival);
                hotelArrivalDepartureIds.push(arrivalId);
            }

            // Add Departure if provided
            if (departureDateTime && departurePortId) {
                const departureId = Date.now() + idx * 10 + 101;
                const departure = {
                    id: departureId,
                    dateTime: departureDateTime,
                    portId: departurePortId,
                    portName: departurePortName,
                    flightNo: departureFlightNo || '-',
                    type: 'Departure',
                    adultsQty: adults,
                    adultCost: 0,
                    adultSell: 0,
                    childQty: child,
                    childCost: 0,
                    childSell: 0,
                    infantQty: infant,
                    amount: 0,
                    supplement: '',
                    accommodationIndex: accommodationIdx
                };
                arrivalDepartureList.push(departure);
                hotelArrivalDepartureIds.push(departureId);
            }
            
            // Update the accommodation with the IDs
            accommodationList[accommodationIdx].arrivalDepartureIds = hotelArrivalDepartureIds;
        });
        
        updateAccommodationTable();

        // Update Arrival/Departure table
        updateArrivalDepartureTable();
        
        // Recalculate totals
        recalculateTotals();

        // Close modal
        const accommodationModal = bootstrap.Modal.getInstance(document.getElementById('accommodationModal'));
        accommodationModal.hide();

        // Clear temp list and arrival/departure fields
        selectedHotelsTemp = [];
        document.getElementById('arrivalDateTime').value = '';
        $('#arrivalPort').val('').trigger('change');
        document.getElementById('arrivalFlightNo').value = '';
        document.getElementById('departureDateTime').value = '';
        $('#departurePort').val('').trigger('change');
        document.getElementById('departureFlightNo').value = '';
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
                    <a href="javascript:void(0)" onclick="editAccommodation(${index})" style="color: #0d6efd; text-decoration: underline; cursor: pointer;">
                        ${hotel.hotelName}
                    </a>
                </td>
                <td><input type="date" value="${hotel.checkIn}" onchange="updateAccommodationField(${index}, 'checkIn', this.value); recalculateNights(${index})"></td>
                <td><input type="date" value="${hotel.checkOut}" onchange="updateAccommodationField(${index}, 'checkOut', this.value); recalculateNights(${index})"></td>
                <td><input type="number" value="${hotel.nights}" readonly style="background-color: #f5f5f5;"></td>
                <td><input type="number" value="${hotel.rooms}" min="1" onchange="updateAccommodationField(${index}, 'rooms', this.value)"></td>
                <td><input type="number" value="${hotel.adultsPerRoom}" min="1" onchange="updateAccommodationField(${index}, 'adultsPerRoom', this.value)"></td>
                <td><input type="number" value="${hotel.extraBed}" min="0" onchange="updateAccommodationField(${index}, 'extraBed', this.value)"></td>
                <td><input type="number" value="${hotel.childWithoutBed}" min="0" onchange="updateAccommodationField(${index}, 'childWithoutBed', this.value)"></td>
                <td>
                    <select onchange="updateAccommodationField(${index}, 'mealPlan', this.value)">
                        <option value="CP" ${hotel.mealPlan === 'CP' ? 'selected' : ''}>CP</option>
                        <option value="MAP" ${hotel.mealPlan === 'MAP' ? 'selected' : ''}>MAP</option>
                        <option value="AP" ${hotel.mealPlan === 'AP' ? 'selected' : ''}>AP</option>
                        <option value="EP" ${hotel.mealPlan === 'EP' ? 'selected' : ''}>EP</option>
                    </select>
                </td>
                <td><input type="text" value="${hotel.supplement || ''}" onchange="updateAccommodationField(${index}, 'supplement', this.value)" style="width: 80px;"></td>
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

    // Edit accommodation - opens modal with hotel data
    function editAccommodation(index) {
        const hotel = accommodationList[index];
        if (!hotel) return;
        
        // Reset and hide the temp hotels section
        selectedHotelsTemp = [];
        document.getElementById('selectedHotelsList').innerHTML = '';
        document.getElementById('noHotelsMessage').style.display = 'block';
        
        // Make sure arrival/departure section is visible
        const arrivalDepartureSection = document.getElementById('arrivalDepartureSection');
        if (arrivalDepartureSection) {
            arrivalDepartureSection.style.display = 'block';
        }
        
        // Set the destination first
        document.getElementById('hotelDestination').value = hotel.destination;
        
        // Load hotels for the destination
        loadHotelsByDestination();
        
        // Wait a bit for hotels to load, then set the hotel and load rooms
        setTimeout(() => {
            const hotelSelectElement = document.getElementById('hotelSelect');
            hotelSelectElement.value = hotel.hotelId;
            
            // Verify hotel was selected
            if (hotelSelectElement.value != hotel.hotelId) {
                console.log('Hotel not found, trying again...');
                setTimeout(() => {
                    hotelSelectElement.value = hotel.hotelId;
                }, 200);
            }
            
            loadRoomTypes();
            
            // Set dates and nights
            document.getElementById('checkInDate').value = hotel.checkIn;
            document.getElementById('checkOutDate').value = hotel.checkOut;
            document.getElementById('numNights').value = hotel.nights;
            
            // Wait for room types to load
            setTimeout(() => {
                const roomTypeElement = document.getElementById('roomType');
                roomTypeElement.value = hotel.roomType;
                
                // Verify room type was selected
                if (roomTypeElement.value != hotel.roomType) {
                    console.log('Room type not found, trying again...');
                    setTimeout(() => {
                        roomTypeElement.value = hotel.roomType;
                    }, 200);
                }
                
                loadBedTypes();
                
                // Wait for bed types to load
                setTimeout(() => {
                    const bedTypeElement = document.getElementById('bedType');
                    bedTypeElement.value = hotel.roomId;
                    
                    // Verify bed type was selected
                    if (bedTypeElement.value != hotel.roomId) {
                        console.log('Bed type not found, trying again...');
                        setTimeout(() => {
                            bedTypeElement.value = hotel.roomId;
                            updatePricing();
                        }, 200);
                    } else {
                        updatePricing();
                    }
                    
                    // Set other fields
                    document.getElementById('numRooms').value = hotel.rooms;
                    document.getElementById('adultsPerRoom').value = hotel.adultsPerRoom;
                    document.getElementById('extraBed').value = hotel.extraBed;
                    document.getElementById('childWithoutBed').value = hotel.childWithoutBed;
                    document.getElementById('mealPlan').value = hotel.mealPlan || 'CP';
                    
                    // Show all arrival and departure fields
                    document.getElementById('arrivalDateTimeField').style.display = 'block';
                    document.getElementById('arrivalPortField').style.display = 'block';
                    document.getElementById('arrivalFlightNoField').style.display = 'block';
                    document.getElementById('departureDateTimeField').style.display = 'block';
                    document.getElementById('departurePortField').style.display = 'block';
                    document.getElementById('departureFlightNoField').style.display = 'block';
                    
                    // Clear arrival/departure fields first
                    document.getElementById('arrivalDateTime').value = '';
                    document.getElementById('arrivalFlightNo').value = '';
                    document.getElementById('departureDateTime').value = '';
                    document.getElementById('departureFlightNo').value = '';
                    $('#arrivalPort').val('').trigger('change');
                    $('#departurePort').val('').trigger('change');
                    
                    console.log('Editing accommodation index:', index);
                    console.log('Hotel data:', hotel);
                    console.log('All arrival/departure list:', arrivalDepartureList);
                    
                    // Populate arrival/departure data
                    // Check multiple sources for arrival/departure data
                    
                    // Method 1: Check if hotel has direct arrival/departure data stored
                    let arrivalFound = false;
                    let departureFound = false;
                    
                    if (hotel.arrivalDateTime || hotel.arrivalPortId) {
                        console.log('Method 1: Found arrival data in hotel object');
                        document.getElementById('arrivalDateTime').value = hotel.arrivalDateTime || '';
                        if (hotel.arrivalPortId) {
                            setTimeout(() => {
                                $('#arrivalPort').val(hotel.arrivalPortId).trigger('change');
                            }, 100);
                        }
                        document.getElementById('arrivalFlightNo').value = hotel.arrivalFlightNo || '';
                        arrivalFound = true;
                    }
                    
                    if (hotel.departureDateTime || hotel.departurePortId) {
                        console.log('Method 1: Found departure data in hotel object');
                        document.getElementById('departureDateTime').value = hotel.departureDateTime || '';
                        if (hotel.departurePortId) {
                            setTimeout(() => {
                                $('#departurePort').val(hotel.departurePortId).trigger('change');
                            }, 100);
                        }
                        document.getElementById('departureFlightNo').value = hotel.departureFlightNo || '';
                        departureFound = true;
                    }
                    
                    // Method 2: Check if there are linked arrival/departure entries by ID
                    if (!arrivalFound || !departureFound) {
                        if (hotel.arrivalDepartureIds && hotel.arrivalDepartureIds.length > 0) {
                            hotel.arrivalDepartureIds.forEach(adId => {
                                const adEntry = arrivalDepartureList.find(item => item.id === adId);
                                if (adEntry) {
                                    if (adEntry.type === 'Arrival' && !arrivalFound) {
                                        document.getElementById('arrivalDateTime').value = adEntry.dateTime || '';
                                        if (adEntry.portId) {
                                            setTimeout(() => {
                                                $('#arrivalPort').val(adEntry.portId).trigger('change');
                                            }, 100);
                                        }
                                        document.getElementById('arrivalFlightNo').value = adEntry.flightNo || '';
                                        arrivalFound = true;
                                    } else if (adEntry.type === 'Departure' && !departureFound) {
                                        document.getElementById('departureDateTime').value = adEntry.dateTime || '';
                                        if (adEntry.portId) {
                                            setTimeout(() => {
                                                $('#departurePort').val(adEntry.portId).trigger('change');
                                            }, 100);
                                        }
                                        document.getElementById('departureFlightNo').value = adEntry.flightNo || '';
                                        departureFound = true;
                                    }
                                }
                            });
                        }
                    }
                    
                    // Method 3: Look for arrival/departure entries by accommodation index
                    if (!arrivalFound || !departureFound) {
                        console.log('Method 3: Searching by accommodation index:', index);
                        arrivalDepartureList.forEach((adEntry, adIndex) => {
                            console.log(`Checking entry ${adIndex}:`, adEntry, 'accommodationIndex:', adEntry.accommodationIndex);
                            if (adEntry.accommodationIndex === index) {
                                console.log('Found matching entry for index', index, ':', adEntry);
                                if (adEntry.type === 'Arrival' && !arrivalFound) {
                                    console.log('Populating arrival data from entry');
                                    document.getElementById('arrivalDateTime').value = adEntry.dateTime || '';
                                    if (adEntry.portId) {
                                        setTimeout(() => {
                                            $('#arrivalPort').val(adEntry.portId).trigger('change');
                                        }, 150);
                                    }
                                    document.getElementById('arrivalFlightNo').value = adEntry.flightNo || '';
                                    arrivalFound = true;
                                } else if (adEntry.type === 'Departure' && !departureFound) {
                                    console.log('Populating departure data from entry');
                                    document.getElementById('departureDateTime').value = adEntry.dateTime || '';
                                    if (adEntry.portId) {
                                        setTimeout(() => {
                                            $('#departurePort').val(adEntry.portId).trigger('change');
                                        }, 150);
                                    }
                                    document.getElementById('departureFlightNo').value = adEntry.flightNo || '';
                                    departureFound = true;
                                }
                            }
                        });
                    }
                    
                    console.log('Final result - Arrival found:', arrivalFound, 'Departure found:', departureFound);
                    
                    // Hide the "Add to List" button section and show direct save
                    document.getElementById('addHotelBtn').style.display = 'none';
                    
                    // Change the save button text to "Update"
                    document.getElementById('saveAccommodationBtnText').textContent = 'Update Accommodation';
                    
                }, 400);
            }, 400);
        }, 500);
        
        // Store the index for update
        window.editingAccommodationIndex = index;
        
        // Open the modal
        const accommodationModal = new bootstrap.Modal(document.getElementById('accommodationModal'));
        accommodationModal.show();
    }

    // Open standalone Arrival/Departure modal (without accommodation)
    function openArrivalDepartureModal() {
        // Open the modal and hide hotel sections
        openAccommodationModal();
        
        // Hide hotel selection sections
        document.getElementById('hotelSelectionRow1').style.display = 'none';
        document.getElementById('hotelSelectionRow2').style.display = 'none';
        document.getElementById('selectedHotelsSection').style.display = 'none';
        
        // Show all arrival/departure fields (both arrival and departure)
        document.getElementById('arrivalDateTimeField').style.display = 'block';
        document.getElementById('arrivalPortField').style.display = 'block';
        document.getElementById('arrivalFlightNoField').style.display = 'block';
        document.getElementById('departureDateTimeField').style.display = 'block';
        document.getElementById('departurePortField').style.display = 'block';
        document.getElementById('departureFlightNoField').style.display = 'block';
        
        // Update modal title
        document.getElementById('modalTitleIcon').className = 'ri-flight-takeoff-line me-2';
        document.getElementById('modalTitleText').textContent = 'Add Arrival / Departure';
        document.getElementById('arrivalDepartureSectionTitle').textContent = 'Arrival/Departure Flight Information';
        
        // Update button text
        document.getElementById('saveAccommodationBtnText').textContent = 'Add Arrival/Departure';
        
        // Set flag for arrival/departure only mode
        window.isArrivalDepartureOnlyMode = true;
    }

    // Remove selected accommodation
    function removeSelectedAccommodation() {
        const checkboxes = document.querySelectorAll('.accommodation-checkbox:checked');
        
        if (checkboxes.length === 0) {
            alert('Please select hotels to remove');
            return;
        }

        const idsToRemove = Array.from(checkboxes).map(cb => parseInt(cb.value));
        
        // Also remove associated arrival/departure entries
        accommodationList.forEach(hotel => {
            if (idsToRemove.includes(hotel.id) && hotel.arrivalDepartureIds) {
                arrivalDepartureList = arrivalDepartureList.filter(item => !hotel.arrivalDepartureIds.includes(item.id));
            }
        });
        
        accommodationList = accommodationList.filter(hotel => !idsToRemove.includes(hotel.id));
        updateAccommodationTable();
        updateArrivalDepartureTable();
    }

    // Update Arrival/Departure table
    function updateArrivalDepartureTable() {
        const tbody = document.getElementById('arrivalDepartureTableBody');
        const table = document.getElementById('arrivalDepartureTable');
        const emptyMessage = document.getElementById('emptyArrivalDepartureMessage');

        // Filter to show only standalone arrival/departure entries (not linked to accommodation)
        const standaloneEntries = arrivalDepartureList
            .map((item, index) => ({ ...item, originalIndex: index }))
            .filter(item => item.accommodationIndex === null || item.accommodationIndex === undefined);

        if (standaloneEntries.length === 0) {
            table.style.display = 'none';
            emptyMessage.style.display = 'block';
            return;
        }

        table.style.display = 'table';
        emptyMessage.style.display = 'none';

        tbody.innerHTML = standaloneEntries.map((item) => `
            <tr>
                <td><input type="checkbox" class="arrivalDeparture-checkbox" value="${item.id}"></td>
                <td>${formatDateTime(item.dateTime)}</td>
                <td>
                    <a href="javascript:void(0)" onclick="editArrivalDeparture(${item.originalIndex})" style="color: #0d6efd; text-decoration: underline; cursor: pointer;">
                        ${item.portName || '-'}
                    </a>
                </td>
                <td>${item.flightNo}</td>
                <td>${item.type}</td>
                <td><input type="number" value="${item.adultsQty}" onchange="updateArrivalDepartureField(${item.originalIndex}, 'adultsQty', this.value)"></td>
                <td><input type="number" value="${item.adultCost}" onchange="updateArrivalDepartureField(${item.originalIndex}, 'adultCost', this.value)"></td>
                <td><input type="number" value="${item.adultSell}" onchange="updateArrivalDepartureField(${item.originalIndex}, 'adultSell', this.value)"></td>
                <td><input type="number" value="${item.childQty}" onchange="updateArrivalDepartureField(${item.originalIndex}, 'childQty', this.value)"></td>
                <td><input type="number" value="${item.childCost}" onchange="updateArrivalDepartureField(${item.originalIndex}, 'childCost', this.value)"></td>
                <td><input type="number" value="${item.childSell}" onchange="updateArrivalDepartureField(${item.originalIndex}, 'childSell', this.value)"></td>
                <td><input type="number" value="${item.infantQty}" onchange="updateArrivalDepartureField(${item.originalIndex}, 'infantQty', this.value)"></td>
                <td><input type="number" value="${item.amount}" onchange="updateArrivalDepartureField(${item.originalIndex}, 'amount', this.value)"></td>
                <td><input type="text" value="${item.supplement}" onchange="updateArrivalDepartureField(${item.originalIndex}, 'supplement', this.value)" style="width: 80px;"></td>
            </tr>
        `).join('');
    }

    // Format datetime for display
    function formatDateTime(dateTimeString) {
        if (!dateTimeString) return '-';
        const date = new Date(dateTimeString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = date.toLocaleString('default', { month: 'short' });
        const year = String(date.getFullYear()).slice(-2);
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${day} ${month} '${year} ${hours}:${minutes}`;
    }

    // Edit arrival/departure - find and edit the associated accommodation
    function editArrivalDeparture(index) {
        const arrivalDeparture = arrivalDepartureList[index];
        if (!arrivalDeparture) return;
        
        // Find the accommodation index
        const accommodationIdx = arrivalDeparture.accommodationIndex;
        
        if (accommodationIdx !== undefined && accommodationIdx !== null && accommodationList[accommodationIdx]) {
            // Edit the associated accommodation
            editAccommodation(accommodationIdx);
        } else {
            // Standalone arrival/departure - open modal with arrival/departure data only
            openAccommodationModal();
            
            // Hide hotel sections
            document.getElementById('hotelSelectionRow1').style.display = 'none';
            document.getElementById('hotelSelectionRow2').style.display = 'none';
            document.getElementById('selectedHotelsSection').style.display = 'none';
            
            // Always show BOTH arrival and departure fields
            document.getElementById('arrivalDateTimeField').style.display = 'block';
            document.getElementById('arrivalPortField').style.display = 'block';
            document.getElementById('arrivalFlightNoField').style.display = 'block';
            document.getElementById('departureDateTimeField').style.display = 'block';
            document.getElementById('departurePortField').style.display = 'block';
            document.getElementById('departureFlightNoField').style.display = 'block';
            
            // Update modal title based on what was clicked
            if (arrivalDeparture.type === 'Arrival') {
                document.getElementById('modalTitleIcon').className = 'ri-flight-takeoff-line me-2';
                document.getElementById('modalTitleText').textContent = 'Edit Arrival / Departure';
            } else {
                document.getElementById('modalTitleIcon').className = 'ri-flight-land-line me-2';
                document.getElementById('modalTitleText').textContent = 'Edit Arrival / Departure';
            }
            document.getElementById('arrivalDepartureSectionTitle').textContent = 'Arrival/Departure Flight Information';
            
            // Clear all fields first
            document.getElementById('arrivalDateTime').value = '';
            document.getElementById('arrivalFlightNo').value = '';
            document.getElementById('departureDateTime').value = '';
            document.getElementById('departureFlightNo').value = '';
            $('#arrivalPort').val('').trigger('change');
            $('#departurePort').val('').trigger('change');
            
            // Populate the clicked entry data
            if (arrivalDeparture.type === 'Arrival') {
                document.getElementById('arrivalDateTime').value = arrivalDeparture.dateTime || '';
                setTimeout(() => {
                    $('#arrivalPort').val(arrivalDeparture.portId).trigger('change');
                }, 100);
                document.getElementById('arrivalFlightNo').value = arrivalDeparture.flightNo || '';
            } else {
                document.getElementById('departureDateTime').value = arrivalDeparture.dateTime || '';
                setTimeout(() => {
                    $('#departurePort').val(arrivalDeparture.portId).trigger('change');
                }, 100);
                document.getElementById('departureFlightNo').value = arrivalDeparture.flightNo || '';
            }
            
            // Look for the corresponding arrival/departure entry (if arrival clicked, find departure and vice versa)
            arrivalDepartureList.forEach(entry => {
                // Check if this is a related entry (same accommodationIndex or created around same time)
                if (entry.id !== arrivalDeparture.id && 
                    entry.accommodationIndex === arrivalDeparture.accommodationIndex) {
                    if (entry.type === 'Arrival' && arrivalDeparture.type === 'Departure') {
                        // Found the arrival for this departure
                        document.getElementById('arrivalDateTime').value = entry.dateTime || '';
                        setTimeout(() => {
                            $('#arrivalPort').val(entry.portId).trigger('change');
                        }, 100);
                        document.getElementById('arrivalFlightNo').value = entry.flightNo || '';
                    } else if (entry.type === 'Departure' && arrivalDeparture.type === 'Arrival') {
                        // Found the departure for this arrival
                        document.getElementById('departureDateTime').value = entry.dateTime || '';
                        setTimeout(() => {
                            $('#departurePort').val(entry.portId).trigger('change');
                        }, 100);
                        document.getElementById('departureFlightNo').value = entry.flightNo || '';
                    }
                }
            });
            
            // Update button text
            document.getElementById('saveAccommodationBtnText').textContent = 'Update Arrival/Departure';
            
            // Set flags
            window.isArrivalDepartureOnlyMode = true;
            window.editingArrivalDepartureIndex = index;
            window.editingArrivalDepartureType = arrivalDeparture.type;
        }
    }

    // Update arrival/departure field
    function updateArrivalDepartureField(index, field, value) {
        if (arrivalDepartureList[index]) {
            arrivalDepartureList[index][field] = value;
        }
    }

    // Remove selected arrival/departure
    function removeSelectedArrivalDeparture() {
        const checkboxes = document.querySelectorAll('.arrivalDeparture-checkbox:checked');
        
        if (checkboxes.length === 0) {
            alert('Please select arrival/departure entries to remove');
            return;
        }

        const idsToRemove = Array.from(checkboxes).map(cb => parseInt(cb.value));
        arrivalDepartureList = arrivalDepartureList.filter(item => !idsToRemove.includes(item.id));
        updateArrivalDepartureTable();
    }

    // ==================== TOUR FUNCTIONS ====================
    
    // Open Tour Modal
    function openTourModal() {
        window.editingTourIndex = null;
        
        // Reset form
        document.getElementById('tourDestination').value = '';
        document.getElementById('attractionSelect').value = '';
        document.getElementById('tourDateTime').value = '';
        document.getElementById('tourPTE').checked = false;
        document.getElementById('tourAdultsQty').value = '2';
        document.getElementById('tourAdultCost').value = '0';
        document.getElementById('tourAdultSell').value = '0';
        document.getElementById('tourChildQty').value = '0';
        document.getElementById('tourChildCost').value = '0';
        document.getElementById('tourChildSell').value = '0';
        
        // Reset transfer fields
        document.getElementById('transferRequired').value = 'no';
        toggleTransferFields();
        
        // Reset guide fields
        document.getElementById('guideRequired').value = 'no';
        toggleGuideFields();
        
        document.getElementById('tourModalTitleText').textContent = 'Add Tour / Attraction';
        document.getElementById('saveTourBtnText').textContent = 'Add Tour';
        
        const tourModal = new bootstrap.Modal(document.getElementById('tourModal'));
        tourModal.show();
    }
    
    // Toggle transfer fields
    function toggleTransferFields() {
        const required = document.getElementById('transferRequired').value;
        const show = required === 'yes';
        
        document.getElementById('transferTypeField').style.display = show ? 'block' : 'none';
        document.getElementById('transferWayField').style.display = show ? 'block' : 'none';
        document.getElementById('vehicleTypeField').style.display = show ? 'block' : 'none';
        document.getElementById('transferCostFields').style.display = show ? 'block' : 'none';
        document.getElementById('transferSellFields').style.display = show ? 'block' : 'none';
    }
    
    // Toggle guide fields
    function toggleGuideFields() {
        const required = document.getElementById('guideRequired').value;
        const show = required === 'yes';
        
        document.getElementById('guideLanguageField').style.display = show ? 'block' : 'none';
        document.getElementById('guideNameField').style.display = show ? 'block' : 'none';
        document.getElementById('guideHoursField').style.display = show ? 'block' : 'none';
        document.getElementById('guideCostField').style.display = show ? 'block' : 'none';
        document.getElementById('guideSellField').style.display = show ? 'block' : 'none';
    }
    
    // Load attractions by destination
    function loadAttractionsByDestination() {
        const destination = document.getElementById('tourDestination').value;
        const attractionSelect = document.getElementById('attractionSelect');
        
        if (!destination) {
            // Reset to show all attractions
            const allOptions = attractionSelect.querySelectorAll('option:not(:first-child)');
            allOptions.forEach(opt => opt.style.display = '');
            return;
        }
        
        // Note: If attractions need to be filtered by destination,
        // implement AJAX call here. For now, showing all attractions.
        // All attractions are already loaded from database in the select dropdown
    }
    
    // Save tour
    function saveTour() {
        const destination = document.getElementById('tourDestination').value;
        const attractionSelect = document.getElementById('attractionSelect');
        const attractionId = attractionSelect.value;
        const attractionName = attractionSelect.options[attractionSelect.selectedIndex]?.text || '';
        const dateTime = document.getElementById('tourDateTime').value;
        const pte = document.getElementById('tourPTE').checked;
        const adultsQty = parseInt(document.getElementById('tourAdultsQty').value);
        const adultCost = parseFloat(document.getElementById('tourAdultCost').value);
        const adultSell = parseFloat(document.getElementById('tourAdultSell').value);
        const childQty = parseInt(document.getElementById('tourChildQty').value);
        const childCost = parseFloat(document.getElementById('tourChildCost').value);
        const childSell = parseFloat(document.getElementById('tourChildSell').value);
        
        if (!attractionId || !dateTime) {
            alert('Please select attraction and date/time');
            return;
        }
        
        // Get transfer info
        const transferRequired = document.getElementById('transferRequired').value === 'yes';
        let transferInfo = null;
        let transferId = null;
        
        if (transferRequired) {
            const transferType = document.getElementById('transferType').value;
            const transferWay = document.getElementById('transferWay').value;
            const vehicleType = document.getElementById('vehicleType').value;
            const transferCost = parseFloat(document.getElementById('transferCost').value);
            const transferSell = parseFloat(document.getElementById('transferSell').value);
            
            transferId = Date.now() + Math.random();
            transferInfo = {
                id: transferId,
                type: transferType,
                way: transferWay,
                vehicleType: vehicleType,
                cost: transferCost,
                sell: transferSell,
                destination: attractionName,
                dateTime: dateTime,
                adults: adultsQty,
                child: childQty,
                taxIncluded: true,
                isStandalone: false
            };
            
            // Add to transfer list
            transferList.push(transferInfo);
        }
        
        // Get guide info
        const guideRequired = document.getElementById('guideRequired').value === 'yes';
        let guideInfo = null;
        let guideId = null;
        
        if (guideRequired) {
            const language = document.getElementById('guideLanguage').value;
            const guideName = document.getElementById('guideName').value;
            const hours = parseFloat(document.getElementById('guideHours').value);
            const guideCost = parseFloat(document.getElementById('guideCost').value);
            const guideSell = parseFloat(document.getElementById('guideSell').value);
            
            guideId = Date.now() + Math.random() + 1;
            guideInfo = {
                id: guideId,
                language: language,
                name: guideName,
                hours: hours,
                cost: guideCost,
                sell: guideSell,
                tourName: attractionName,
                dateTime: dateTime,
                isStandalone: false
            };
            
            // Add to guide list
            guideList.push(guideInfo);
        }
        
        const tourData = {
            id: Date.now(),
            destination: destination,
            attractionId: attractionId,
            attractionName: attractionName,
            dateTime: dateTime,
            pte: pte,
            adultsQty: adultsQty,
            adultCost: adultCost,
            adultSell: adultSell,
            childQty: childQty,
            childCost: childCost,
            childSell: childSell,
            transferId: transferId,
            transferInfo: transferInfo,
            guideId: guideId,
            guideInfo: guideInfo
        };
        
        // Check if editing
        if (window.editingTourIndex !== undefined && window.editingTourIndex !== null) {
            // Remove old transfer and guide if exists
            const oldTour = tourList[window.editingTourIndex];
            if (oldTour.transferId) {
                transferList = transferList.filter(t => t.id !== oldTour.transferId);
            }
            if (oldTour.guideId) {
                guideList = guideList.filter(g => g.id !== oldTour.guideId);
            }
            
            tourList[window.editingTourIndex] = tourData;
            window.editingTourIndex = null;
        } else {
            tourList.push(tourData);
        }
        
        updateTourTable();
        updateGuideTable();
        updateTransferTable();
        recalculateTotals();
        
        // Close modal
        const tourModal = bootstrap.Modal.getInstance(document.getElementById('tourModal'));
        tourModal.hide();
    }
    
    // Update tour table
    function updateTourTable() {
        const tbody = document.getElementById('tourTableBody');
        const table = document.getElementById('tourTable');
        const emptyMessage = document.getElementById('emptyTourMessage');
        
        if (tourList.length === 0) {
            table.style.display = 'none';
            emptyMessage.style.display = 'block';
            return;
        }
        
        table.style.display = 'table';
        emptyMessage.style.display = 'none';
        
        tbody.innerHTML = tourList.map((tour, index) => `
            <tr>
                <td><input type="checkbox" class="tour-checkbox" value="${tour.id}"></td>
                <td>${formatDateTime(tour.dateTime)}</td>
                <td>
                    <a href="javascript:void(0)" onclick="editTour(${index})" style="color: #0d6efd; text-decoration: underline; cursor: pointer;">
                        ${tour.attractionName}
                    </a>
                </td>
                <td><input type="checkbox" ${tour.pte ? 'checked' : ''} onchange="updateTourField(${index}, 'pte', this.checked)"></td>
                <td><input type="number" value="${tour.adultsQty}" onchange="updateTourField(${index}, 'adultsQty', this.value)"></td>
                <td><input type="number" value="${tour.adultCost}" onchange="updateTourField(${index}, 'adultCost', this.value)" step="0.01"></td>
                <td><input type="number" value="${tour.adultSell}" onchange="updateTourField(${index}, 'adultSell', this.value)" step="0.01"></td>
                <td><input type="number" value="${tour.childQty}" onchange="updateTourField(${index}, 'childQty', this.value)"></td>
                <td><input type="number" value="${tour.childCost}" onchange="updateTourField(${index}, 'childCost', this.value)" step="0.01"></td>
                <td><input type="number" value="${tour.childSell}" onchange="updateTourField(${index}, 'childSell', this.value)" step="0.01"></td>
                <td>${tour.transferInfo ? tour.transferInfo.type + ' / ' + tour.transferInfo.way : '-'}</td>
                <td>${tour.guideInfo ? tour.guideInfo.name || tour.guideInfo.language : '-'}</td>
            </tr>
        `).join('');
    }
    
    // Edit tour
    function editTour(index) {
        const tour = tourList[index];
        if (!tour) return;
        
        window.editingTourIndex = index;
        
        document.getElementById('tourDestination').value = tour.destination;
        setTimeout(() => {
            document.getElementById('attractionSelect').value = tour.attractionId;
        }, 300);
        document.getElementById('tourDateTime').value = tour.dateTime;
        document.getElementById('tourPTE').checked = tour.pte;
        document.getElementById('tourAdultsQty').value = tour.adultsQty;
        document.getElementById('tourAdultCost').value = tour.adultCost;
        document.getElementById('tourAdultSell').value = tour.adultSell;
        document.getElementById('tourChildQty').value = tour.childQty;
        document.getElementById('tourChildCost').value = tour.childCost;
        document.getElementById('tourChildSell').value = tour.childSell;
        
        // Populate transfer info
        if (tour.transferInfo) {
            document.getElementById('transferRequired').value = 'yes';
            toggleTransferFields();
            document.getElementById('transferType').value = tour.transferInfo.type;
            document.getElementById('transferWay').value = tour.transferInfo.way;
            document.getElementById('vehicleType').value = tour.transferInfo.vehicleType;
            document.getElementById('transferCost').value = tour.transferInfo.cost;
            document.getElementById('transferSell').value = tour.transferInfo.sell;
        }
        
        // Populate guide info
        if (tour.guideInfo) {
            document.getElementById('guideRequired').value = 'yes';
            toggleGuideFields();
            document.getElementById('guideLanguage').value = tour.guideInfo.language;
            document.getElementById('guideName').value = tour.guideInfo.name;
            document.getElementById('guideHours').value = tour.guideInfo.hours;
            document.getElementById('guideCost').value = tour.guideInfo.cost;
            document.getElementById('guideSell').value = tour.guideInfo.sell;
        }
        
        document.getElementById('tourModalTitleText').textContent = 'Edit Tour / Attraction';
        document.getElementById('saveTourBtnText').textContent = 'Update Tour';
        
        const tourModal = new bootstrap.Modal(document.getElementById('tourModal'));
        tourModal.show();
    }
    
    // Update tour field
    function updateTourField(index, field, value) {
        if (tourList[index]) {
            tourList[index][field] = value;
        }
    }
    
    // ==================== GUIDE FUNCTIONS ====================
    
    // Open Guide Modal
    function openGuideModal() {
        window.editingGuideIndex = null;
        
        // Reset form
        document.getElementById('guideModalDateTime').value = '';
        document.getElementById('guideModalTourName').value = '';
        document.getElementById('guideModalLanguage').value = 'english';
        document.getElementById('guideModalName').value = '';
        document.getElementById('guideModalHours').value = '4';
        document.getElementById('guideModalCost').value = '0';
        document.getElementById('guideModalSell').value = '0';
        
        document.getElementById('guideModalTitleText').textContent = 'Add Guide';
        document.getElementById('saveGuideBtnText').textContent = 'Add Guide';
        
        const guideModal = new bootstrap.Modal(document.getElementById('guideModal'));
        guideModal.show();
    }
    
    // Save guide
    function saveGuide() {
        const dateTime = document.getElementById('guideModalDateTime').value;
        const tourName = document.getElementById('guideModalTourName').value;
        const language = document.getElementById('guideModalLanguage').value;
        const name = document.getElementById('guideModalName').value;
        const hours = parseFloat(document.getElementById('guideModalHours').value);
        const cost = parseFloat(document.getElementById('guideModalCost').value);
        const sell = parseFloat(document.getElementById('guideModalSell').value);
        
        if (!dateTime || !tourName) {
            alert('Please fill in date/time and tour name');
            return;
        }
        
        const guideData = {
            id: Date.now(),
            dateTime: dateTime,
            tourName: tourName,
            language: language,
            name: name,
            hours: hours,
            cost: cost,
            sell: sell,
            isStandalone: true
        };
        
        // Check if editing
        if (window.editingGuideIndex !== undefined && window.editingGuideIndex !== null) {
            guideList[window.editingGuideIndex] = guideData;
            window.editingGuideIndex = null;
        } else {
            guideList.push(guideData);
        }
        
        updateGuideTable();
        
        // Close modal
        const guideModal = bootstrap.Modal.getInstance(document.getElementById('guideModal'));
        guideModal.hide();
    }
    
    // Update guide table
    function updateGuideTable() {
        const tbody = document.getElementById('guideTableBody');
        const table = document.getElementById('guideTable');
        const emptyMessage = document.getElementById('emptyGuideMessage');
        
        if (guideList.length === 0) {
            table.style.display = 'none';
            emptyMessage.style.display = 'block';
            return;
        }
        
        table.style.display = 'table';
        emptyMessage.style.display = 'none';
        
        tbody.innerHTML = guideList.map((guide, index) => `
            <tr>
                <td>${guide.isStandalone ? `<input type="checkbox" class="guide-checkbox" value="${guide.id}">` : '<span style="color: #999; font-size: 9px;">Linked</span>'}</td>
                <td>${formatDateTime(guide.dateTime)}</td>
                <td>
                    <a href="javascript:void(0)" onclick="editGuide(${index})" style="color: #0d6efd; text-decoration: underline; cursor: pointer;">
                        ${guide.tourName}
                    </a>
                </td>
                <td>${guide.language}</td>
                <td><input type="text" value="${guide.name || ''}" onchange="updateGuideField(${index}, 'name', this.value)" style="width: 100px;"></td>
                <td><input type="number" value="${guide.hours}" onchange="updateGuideField(${index}, 'hours', this.value)" step="0.5" style="width: 60px;"></td>
                <td><input type="number" value="${guide.cost}" onchange="updateGuideField(${index}, 'cost', this.value)" step="0.01"></td>
                <td><input type="number" value="${guide.sell}" onchange="updateGuideField(${index}, 'sell', this.value)" step="0.01"></td>
            </tr>
        `).join('');
    }
    
    // Edit guide
    function editGuide(index) {
        const guide = guideList[index];
        if (!guide) return;
        
        // If it's linked to a tour, don't allow standalone edit
        if (!guide.isStandalone) {
            alert('This guide is linked to a tour. Please edit the associated tour to modify guide details.');
            return;
        }
        
        window.editingGuideIndex = index;
        
        document.getElementById('guideModalDateTime').value = guide.dateTime;
        document.getElementById('guideModalTourName').value = guide.tourName;
        document.getElementById('guideModalLanguage').value = guide.language;
        document.getElementById('guideModalName').value = guide.name;
        document.getElementById('guideModalHours').value = guide.hours;
        document.getElementById('guideModalCost').value = guide.cost;
        document.getElementById('guideModalSell').value = guide.sell;
        
        document.getElementById('guideModalTitleText').textContent = 'Edit Guide';
        document.getElementById('saveGuideBtnText').textContent = 'Update Guide';
        
        const guideModal = new bootstrap.Modal(document.getElementById('guideModal'));
        guideModal.show();
    }
    
    // Remove selected guides
    function removeSelectedGuides() {
        const checkboxes = document.querySelectorAll('.guide-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Please select guides to remove');
            return;
        }
        
        if (!confirm(`Remove ${checkboxes.length} selected guide(s)?`)) {
            return;
        }
        
        const idsToRemove = Array.from(checkboxes).map(cb => parseInt(cb.value));
        guideList = guideList.filter(guide => !idsToRemove.includes(guide.id));
        
        updateGuideTable();
        recalculateTotals();
    }
    
    // Update guide field
    function updateGuideField(index, field, value) {
        if (guideList[index]) {
            guideList[index][field] = value;
        }
    }

    // ==================== MEAL FUNCTIONS ====================
    
    // Open Meal Modal
    function openMealModal() {
        window.editingMealIndex = null;
        
        // Reset form
        document.getElementById('mealDestination').value = '';
        document.getElementById('restaurantSelect').value = '';
        document.getElementById('mealDateTime').value = '';
        document.getElementById('mealAdultsQty').value = '2';
        document.getElementById('mealAdultCost').value = '0';
        document.getElementById('mealAdultSell').value = '0';
        document.getElementById('mealChildQty').value = '0';
        document.getElementById('mealChildCost').value = '0';
        document.getElementById('mealChildSell').value = '0';
        
        // Reset transfer fields
        document.getElementById('mealTransferRequired').value = 'no';
        toggleMealTransferFields();
        
        document.getElementById('mealModalTitleText').textContent = 'Add Meal / Restaurant';
        document.getElementById('saveMealBtnText').textContent = 'Add Meal';
        
        const mealModal = new bootstrap.Modal(document.getElementById('mealModal'));
        mealModal.show();
    }
    
    // Toggle meal transfer fields
    function toggleMealTransferFields() {
        const required = document.getElementById('mealTransferRequired').value;
        const show = required === 'yes';
        
        document.getElementById('mealTransferTypeField').style.display = show ? 'block' : 'none';
        document.getElementById('mealTransferWayField').style.display = show ? 'block' : 'none';
        document.getElementById('mealVehicleTypeField').style.display = show ? 'block' : 'none';
        document.getElementById('mealTransferCostFields').style.display = show ? 'block' : 'none';
        document.getElementById('mealTransferSellFields').style.display = show ? 'block' : 'none';
    }
    
    // Load restaurants by destination
    function loadRestaurantsByDestination() {
        const destination = document.getElementById('mealDestination').value;
        const restaurantSelect = document.getElementById('restaurantSelect');
        
        if (!destination) {
            // Reset to show all restaurants
            const allOptions = restaurantSelect.querySelectorAll('option:not(:first-child)');
            allOptions.forEach(opt => opt.style.display = '');
            return;
        }
        
        // Note: If restaurants need to be filtered by destination,
        // implement AJAX call here. For now, showing all restaurants.
        // All restaurants are already loaded from database in the select dropdown
    }
    
    // Save meal
    function saveMeal() {
        const destination = document.getElementById('mealDestination').value;
        const restaurantSelect = document.getElementById('restaurantSelect');
        const restaurantId = restaurantSelect.value;
        const restaurantName = restaurantSelect.options[restaurantSelect.selectedIndex]?.text || '';
        const dateTime = document.getElementById('mealDateTime').value;
        const adultsQty = parseInt(document.getElementById('mealAdultsQty').value);
        const adultCost = parseFloat(document.getElementById('mealAdultCost').value);
        const adultSell = parseFloat(document.getElementById('mealAdultSell').value);
        const childQty = parseInt(document.getElementById('mealChildQty').value);
        const childCost = parseFloat(document.getElementById('mealChildCost').value);
        const childSell = parseFloat(document.getElementById('mealChildSell').value);
        
        if (!restaurantId || !dateTime) {
            alert('Please select restaurant and date/time');
            return;
        }
        
        // Get transfer info
        const transferRequired = document.getElementById('mealTransferRequired').value === 'yes';
        let transferInfo = null;
        let transferId = null;
        
        if (transferRequired) {
            const transferType = document.getElementById('mealTransferType').value;
            const transferWay = document.getElementById('mealTransferWay').value;
            const vehicleType = document.getElementById('mealVehicleType').value;
            const transferCost = parseFloat(document.getElementById('mealTransferCost').value);
            const transferSell = parseFloat(document.getElementById('mealTransferSell').value);
            
            transferId = Date.now() + Math.random();
            transferInfo = {
                id: transferId,
                type: transferType,
                way: transferWay,
                vehicleType: vehicleType,
                cost: transferCost,
                sell: transferSell,
                destination: restaurantName,
                dateTime: dateTime,
                adults: adultsQty,
                child: childQty,
                taxIncluded: true,
                isStandalone: false
            };
            
            // Add to transfer list
            transferList.push(transferInfo);
        }
        
        const mealData = {
            id: Date.now(),
            destination: destination,
            restaurantId: restaurantId,
            restaurantName: restaurantName,
            dateTime: dateTime,
            adultsQty: adultsQty,
            adultCost: adultCost,
            adultSell: adultSell,
            childQty: childQty,
            childCost: childCost,
            childSell: childSell,
            transferId: transferId,
            transferInfo: transferInfo
        };
        
        // Check if editing
        if (window.editingMealIndex !== undefined && window.editingMealIndex !== null) {
            // Remove old transfer if exists
            const oldMeal = mealList[window.editingMealIndex];
            if (oldMeal.transferId) {
                transferList = transferList.filter(t => t.id !== oldMeal.transferId);
            }
            
            mealList[window.editingMealIndex] = mealData;
            window.editingMealIndex = null;
        } else {
            mealList.push(mealData);
        }
        
        updateMealTable();
        updateTransferTable();
        recalculateTotals();
        
        // Close modal
        const mealModal = bootstrap.Modal.getInstance(document.getElementById('mealModal'));
        mealModal.hide();
    }
    
    // Update meal table
    function updateMealTable() {
        const tbody = document.getElementById('mealTableBody');
        const table = document.getElementById('mealTable');
        const emptyMessage = document.getElementById('emptyMealMessage');
        
        if (mealList.length === 0) {
            table.style.display = 'none';
            emptyMessage.style.display = 'block';
            return;
        }
        
        table.style.display = 'table';
        emptyMessage.style.display = 'none';
        
        tbody.innerHTML = mealList.map((meal, index) => `
            <tr>
                <td><input type="checkbox" class="meal-checkbox" value="${meal.id}"></td>
                <td>${formatDateTime(meal.dateTime)}</td>
                <td>
                    <a href="javascript:void(0)" onclick="editMeal(${index})" style="color: #0d6efd; text-decoration: underline; cursor: pointer;">
                        ${meal.restaurantName}
                    </a>
                </td>
                <td><input type="number" value="${meal.adultsQty}" onchange="updateMealField(${index}, 'adultsQty', this.value)"></td>
                <td><input type="number" value="${meal.adultCost}" onchange="updateMealField(${index}, 'adultCost', this.value)" step="0.01"></td>
                <td><input type="number" value="${meal.adultSell}" onchange="updateMealField(${index}, 'adultSell', this.value)" step="0.01"></td>
                <td><input type="number" value="${meal.childQty}" onchange="updateMealField(${index}, 'childQty', this.value)"></td>
                <td><input type="number" value="${meal.childCost}" onchange="updateMealField(${index}, 'childCost', this.value)" step="0.01"></td>
                <td><input type="number" value="${meal.childSell}" onchange="updateMealField(${index}, 'childSell', this.value)" step="0.01"></td>
                <td>${meal.transferInfo ? meal.transferInfo.type + ' / ' + meal.transferInfo.way : '-'}</td>
            </tr>
        `).join('');
    }
    
    // Edit meal
    function editMeal(index) {
        const meal = mealList[index];
        if (!meal) return;
        
        window.editingMealIndex = index;
        
        document.getElementById('mealDestination').value = meal.destination;
        setTimeout(() => {
            document.getElementById('restaurantSelect').value = meal.restaurantId;
        }, 300);
        document.getElementById('mealDateTime').value = meal.dateTime;
        document.getElementById('mealAdultsQty').value = meal.adultsQty;
        document.getElementById('mealAdultCost').value = meal.adultCost;
        document.getElementById('mealAdultSell').value = meal.adultSell;
        document.getElementById('mealChildQty').value = meal.childQty;
        document.getElementById('mealChildCost').value = meal.childCost;
        document.getElementById('mealChildSell').value = meal.childSell;
        
        // Populate transfer info
        if (meal.transferInfo) {
            document.getElementById('mealTransferRequired').value = 'yes';
            toggleMealTransferFields();
            document.getElementById('mealTransferType').value = meal.transferInfo.type;
            document.getElementById('mealTransferWay').value = meal.transferInfo.way;
            document.getElementById('mealVehicleType').value = meal.transferInfo.vehicleType;
            document.getElementById('mealTransferCost').value = meal.transferInfo.cost;
            document.getElementById('mealTransferSell').value = meal.transferInfo.sell;
        }
        
        document.getElementById('mealModalTitleText').textContent = 'Edit Meal / Restaurant';
        document.getElementById('saveMealBtnText').textContent = 'Update Meal';
        
        const mealModal = new bootstrap.Modal(document.getElementById('mealModal'));
        mealModal.show();
    }
    
    // Update meal field
    function updateMealField(index, field, value) {
        if (mealList[index]) {
            mealList[index][field] = value;
        }
    }

    // ==================== TRANSFER FUNCTIONS ====================
    
    // Update transfer table - displays all transfers from tours and meals
    function updateTransferTable() {
        const tbody = document.getElementById('transferTableBody');
        const table = document.getElementById('transferTable');
        const emptyMessage = document.getElementById('emptyTransferMessage');
        
        if (transferList.length === 0) {
            table.style.display = 'none';
            emptyMessage.style.display = 'block';
            return;
        }
        
        table.style.display = 'table';
        emptyMessage.style.display = 'none';
        
        // Helper function to get transport mode icon
        const getModeIcon = (mode) => {
            const icons = {
                'local': '<i class="ri-car-line" style="font-size: 16px;" title="Local Transfer"></i>',
                'flight': '<i class="ri-flight-takeoff-line" style="font-size: 16px;" title="Flight"></i>',
                'cruise': '<i class="ri-ship-line" style="font-size: 16px;" title="Cruise"></i>',
                'train': '<i class="ri-train-line" style="font-size: 16px;" title="Train"></i>',
                'bus': '<i class="ri-bus-line" style="font-size: 16px;" title="Bus"></i>'
            };
            return icons[mode] || icons['local'];
        };
        
        // Helper function to get destination display text
        const getDestinationText = (transfer) => {
            const mode = transfer.transportMode || 'local';
            if (mode === 'local') {
                return transfer.destination || (transfer.pickup ? transfer.pickup + ' → ' + transfer.drop : '-');
            } else {
                // For flight, train, bus, cruise
                return (transfer.departFrom || '') + (transfer.destination ? ' → ' + transfer.destination : (transfer.arrivalTo ? ' → ' + transfer.arrivalTo : ''));
            }
        };
        
        // Helper function to get vehicle/transport type
        const getVehicleType = (transfer) => {
            const mode = transfer.transportMode || 'local';
            if (mode === 'local') {
                return transfer.vehicleType || '-';
            } else if (mode === 'flight') {
                return transfer.airline || transfer.operator || '-';
            } else if (mode === 'cruise') {
                return transfer.vessel || transfer.by || '-';
            } else if (mode === 'train' || mode === 'bus') {
                return transfer.operator || '-';
            }
            return '-';
        };
        
        // Helper function to get type/class
        const getTypeClass = (transfer) => {
            const mode = transfer.transportMode || 'local';
            if (mode === 'local') {
                return transfer.type || '-';
            } else {
                return transfer.class || transfer.cabinClass || '-';
            }
        };
        
        // Helper function to get way/trip type
        const getWayType = (transfer) => {
            const mode = transfer.transportMode || 'local';
            if (mode === 'local') {
                return transfer.way || '-';
            } else {
                return transfer.tripType || '-';
            }
        };
        
        tbody.innerHTML = transferList.map((transfer, index) => `
            <tr>
                <td>${transfer.isStandalone ? `<input type="checkbox" class="transfer-checkbox" value="${transfer.id}">` : '<span style="color: #999; font-size: 9px;">Linked</span>'}</td>
                <td>${formatDateTime(transfer.dateTime)}</td>
                <td>
                    <a href="javascript:void(0)" onclick="editTransfer(${index})" style="color: #0d6efd; text-decoration: underline; cursor: pointer;">
                        ${getDestinationText(transfer)}
                    </a>
                </td>
                <td>${getModeIcon(transfer.transportMode || 'local')}</td>
                <td>${getVehicleType(transfer)}</td>
                <td>${getTypeClass(transfer)}</td>
                <td>${getWayType(transfer)}</td>
                <td><input type="number" value="${transfer.adults || 0}" onchange="updateTransferField(${index}, 'adults', this.value)" style="width: 50px;"></td>
                <td><input type="number" value="${transfer.child || 0}" onchange="updateTransferField(${index}, 'child', this.value)" style="width: 50px;"></td>
                <td><input type="number" value="${transfer.cost || 0}" onchange="updateTransferField(${index}, 'cost', this.value)" step="0.01" style="width: 70px;"></td>
                <td><input type="number" value="${transfer.sell || 0}" onchange="updateTransferField(${index}, 'sell', this.value)" step="0.01" style="width: 70px;"></td>
                <td><input type="checkbox" ${transfer.taxIncluded ? 'checked' : ''} onchange="updateTransferField(${index}, 'taxIncluded', this.checked)"></td>
            </tr>
        `).join('');
    }
    
    // Update transfer field
    function updateTransferField(index, field, value) {
        if (transferList[index]) {
            transferList[index][field] = value;
        }
    }
    
    // Open transfer modal (for standalone transfers)
    // Switch between transport mode forms
    function switchTransferMode(mode) {
        // Hide all forms
        document.querySelectorAll('.transfer-mode-form').forEach(form => {
            form.style.display = 'none';
        });
        
        // Show selected form
        const formMap = {
            'local': 'localTransferForm',
            'flight': 'flightForm',
            'cruise': 'cruiseForm',
            'train': 'trainForm',
            'bus': 'busForm'
        };
        
        const formId = formMap[mode];
        if (formId) {
            document.getElementById(formId).style.display = 'block';
        }
    }
    
    // Reset all transfer forms
    function resetAllTransferForms() {
        // Local Transfer
        document.getElementById('localDateTime').value = '';
        document.getElementById('localPickup').value = '';
        document.getElementById('localDrop').value = '';
        document.getElementById('localDestination').value = '';
        document.getElementById('localVehicleType').value = 'sedan';
        document.getElementById('localType').value = 'private';
        document.getElementById('localWay').value = 'one-way';
        document.getElementById('localAdults').value = '2';
        document.getElementById('localChild').value = '0';
        document.getElementById('localCost').value = '0';
        document.getElementById('localSell').value = '0';
        document.getElementById('localTaxIncluded').checked = false;
        
        // Flight
        document.getElementById('flightDepartFrom').value = '';
        document.getElementById('flightDestination').value = '';
        document.getElementById('flightTripType').value = 'return';
        document.getElementById('flightDepartureDate').value = '';
        document.getElementById('flightReturnDate').value = '';
        document.getElementById('flightAirline').value = '';
        document.getElementById('flightNumber').value = '';
        document.getElementById('flightOperator').value = '';
        document.getElementById('flightClass').value = 'economy';
        document.getElementById('flightAdults').value = '2';
        document.getElementById('flightChild').value = '0';
        document.getElementById('flightCost').value = '0';
        document.getElementById('flightSell').value = '0';
        document.getElementById('flightTaxIncluded').checked = false;
        
        // Cruise
        document.getElementById('cruiseDepartFrom').value = '';
        document.getElementById('cruiseFromTerminal').value = '';
        document.getElementById('cruiseBy').value = 'cruise';
        document.getElementById('cruiseType').value = '';
        document.getElementById('cruiseVessel').value = '';
        document.getElementById('cruiseArrivalTo').value = '';
        document.getElementById('cruiseDepartureDate').value = '';
        document.getElementById('cruiseArrivalDate').value = '';
        document.getElementById('cruiseOperator').value = '';
        document.getElementById('cruiseCabinClass').value = 'economy';
        document.getElementById('cruiseAdults').value = '2';
        document.getElementById('cruiseChild').value = '0';
        document.getElementById('cruiseCost').value = '0';
        document.getElementById('cruiseSell').value = '0';
        document.getElementById('cruiseTaxIncluded').checked = false;
        
        // Train
        document.getElementById('trainDepartFrom').value = '';
        document.getElementById('trainDestination').value = '';
        document.getElementById('trainTripType').value = 'return';
        document.getElementById('trainDepartureDate').value = '';
        document.getElementById('trainReturnDate').value = '';
        document.getElementById('trainOperator').value = '';
        document.getElementById('trainClass').value = '1st-class';
        document.getElementById('trainStationTransfer').value = '';
        document.getElementById('trainAdults').value = '2';
        document.getElementById('trainChild').value = '0';
        document.getElementById('trainCost').value = '0';
        document.getElementById('trainSell').value = '0';
        document.getElementById('trainTaxIncluded').checked = false;
        
        // Bus
        document.getElementById('busDepartFrom').value = '';
        document.getElementById('busDestination').value = '';
        document.getElementById('busTripType').value = 'return';
        document.getElementById('busDepartureDate').value = '';
        document.getElementById('busReturnDate').value = '';
        document.getElementById('busOperator').value = '';
        document.getElementById('busClass').value = 'executive';
        document.getElementById('busStationTransfer').value = '';
        document.getElementById('busAdults').value = '2';
        document.getElementById('busChild').value = '0';
        document.getElementById('busCost').value = '0';
        document.getElementById('busSell').value = '0';
        document.getElementById('busTaxIncluded').checked = false;
    }
    
    // Open Transfer Modal for standalone transfers
    function openTransferModal() {
        // Reset all forms
        resetAllTransferForms();
        
        // Reset transport mode to local transfer
        const localRadio = document.querySelector('input[name="transferMode"][value="local"]');
        if (localRadio) localRadio.checked = true;
        switchTransferMode('local');
        
        window.editingTransferIndex = null;
        document.getElementById('transferModalTitleText').textContent = 'Add Transfer Package';
        document.getElementById('saveTransferBtnText').textContent = 'Add Transfer';
        
        const transferModal = new bootstrap.Modal(document.getElementById('transferModal'));
        transferModal.show();
    }
    
    // Save Transfer Package
    function saveTransferPackage() {
        // Get selected transport mode
        const transportModeRadio = document.querySelector('input[name="transferMode"]:checked');
        const transportMode = transportModeRadio ? transportModeRadio.value : 'local';
        
        let transferData = {
            id: Date.now(),
            transportMode: transportMode,
            isStandalone: true
        };
        
        // Collect data based on transport mode
        if (transportMode === 'local') {
            const dateTime = document.getElementById('localDateTime').value;
            const pickup = document.getElementById('localPickup').value;
            const drop = document.getElementById('localDrop').value;
            const destination = document.getElementById('localDestination').value;
            
            if (!dateTime) {
                alert('Please select date/time');
                return;
            }
            if (!pickup && !destination) {
                alert('Please enter pickup location or destination');
                return;
            }
            
            transferData = {
                ...transferData,
                dateTime: dateTime,
                pickup: pickup,
                drop: drop,
                destination: destination,
                vehicleType: document.getElementById('localVehicleType').value,
                type: document.getElementById('localType').value,
                way: document.getElementById('localWay').value,
                adults: parseInt(document.getElementById('localAdults').value) || 0,
                child: parseInt(document.getElementById('localChild').value) || 0,
                cost: parseFloat(document.getElementById('localCost').value) || 0,
                sell: parseFloat(document.getElementById('localSell').value) || 0,
                taxIncluded: document.getElementById('localTaxIncluded').checked
            };
        } 
        else if (transportMode === 'flight') {
            const departureDate = document.getElementById('flightDepartureDate').value;
            const departFrom = document.getElementById('flightDepartFrom').value;
            const destination = document.getElementById('flightDestination').value;
            
            if (!departureDate || !departFrom || !destination) {
                alert('Please fill in departure date, depart from, and destination');
                return;
            }
            
            transferData = {
                ...transferData,
                dateTime: departureDate,
                departFrom: departFrom,
                destination: destination,
                tripType: document.getElementById('flightTripType').value,
                returnDate: document.getElementById('flightReturnDate').value,
                airline: document.getElementById('flightAirline').value,
                flightNumber: document.getElementById('flightNumber').value,
                operator: document.getElementById('flightOperator').value,
                class: document.getElementById('flightClass').value,
                adults: parseInt(document.getElementById('flightAdults').value) || 0,
                child: parseInt(document.getElementById('flightChild').value) || 0,
                cost: parseFloat(document.getElementById('flightCost').value) || 0,
                sell: parseFloat(document.getElementById('flightSell').value) || 0,
                taxIncluded: document.getElementById('flightTaxIncluded').checked
            };
        }
        else if (transportMode === 'cruise') {
            const departureDate = document.getElementById('cruiseDepartureDate').value;
            const departFrom = document.getElementById('cruiseDepartFrom').value;
            
            if (!departureDate || !departFrom) {
                alert('Please fill in departure date and depart from');
                return;
            }
            
            transferData = {
                ...transferData,
                dateTime: departureDate,
                departFrom: departFrom,
                fromTerminal: document.getElementById('cruiseFromTerminal').value,
                by: document.getElementById('cruiseBy').value,
                cruiseType: document.getElementById('cruiseType').value,
                vessel: document.getElementById('cruiseVessel').value,
                arrivalTo: document.getElementById('cruiseArrivalTo').value,
                arrivalDate: document.getElementById('cruiseArrivalDate').value,
                operator: document.getElementById('cruiseOperator').value,
                cabinClass: document.getElementById('cruiseCabinClass').value,
                adults: parseInt(document.getElementById('cruiseAdults').value) || 0,
                child: parseInt(document.getElementById('cruiseChild').value) || 0,
                cost: parseFloat(document.getElementById('cruiseCost').value) || 0,
                sell: parseFloat(document.getElementById('cruiseSell').value) || 0,
                taxIncluded: document.getElementById('cruiseTaxIncluded').checked
            };
        }
        else if (transportMode === 'train') {
            const departureDate = document.getElementById('trainDepartureDate').value;
            const departFrom = document.getElementById('trainDepartFrom').value;
            const destination = document.getElementById('trainDestination').value;
            
            if (!departureDate || !departFrom || !destination) {
                alert('Please fill in departure date, depart from, and destination');
                return;
            }
            
            transferData = {
                ...transferData,
                dateTime: departureDate,
                departFrom: departFrom,
                destination: destination,
                tripType: document.getElementById('trainTripType').value,
                returnDate: document.getElementById('trainReturnDate').value,
                operator: document.getElementById('trainOperator').value,
                class: document.getElementById('trainClass').value,
                stationTransfer: document.getElementById('trainStationTransfer').value,
                adults: parseInt(document.getElementById('trainAdults').value) || 0,
                child: parseInt(document.getElementById('trainChild').value) || 0,
                cost: parseFloat(document.getElementById('trainCost').value) || 0,
                sell: parseFloat(document.getElementById('trainSell').value) || 0,
                taxIncluded: document.getElementById('trainTaxIncluded').checked
            };
        }
        else if (transportMode === 'bus') {
            const departureDate = document.getElementById('busDepartureDate').value;
            const departFrom = document.getElementById('busDepartFrom').value;
            const destination = document.getElementById('busDestination').value;
            
            if (!departureDate || !departFrom || !destination) {
                alert('Please fill in departure date, depart from, and destination');
                return;
            }
            
            transferData = {
                ...transferData,
                dateTime: departureDate,
                departFrom: departFrom,
                destination: destination,
                tripType: document.getElementById('busTripType').value,
                returnDate: document.getElementById('busReturnDate').value,
                operator: document.getElementById('busOperator').value,
                class: document.getElementById('busClass').value,
                stationTransfer: document.getElementById('busStationTransfer').value,
                adults: parseInt(document.getElementById('busAdults').value) || 0,
                child: parseInt(document.getElementById('busChild').value) || 0,
                cost: parseFloat(document.getElementById('busCost').value) || 0,
                sell: parseFloat(document.getElementById('busSell').value) || 0,
                taxIncluded: document.getElementById('busTaxIncluded').checked
            };
        }
        
        if (window.editingTransferIndex !== null && window.editingTransferIndex !== undefined) {
            transferList[window.editingTransferIndex] = transferData;
            window.editingTransferIndex = null;
        } else {
            transferList.push(transferData);
        }
        
        updateTransferTable();
        recalculateTotals();
        
        const transferModal = bootstrap.Modal.getInstance(document.getElementById('transferModal'));
        transferModal.hide();
    }
    
    // Edit transfer
    function editTransfer(index) {
        const transfer = transferList[index];
        if (!transfer) return;
        
        // If it's linked to tour/meal, don't allow standalone edit
        if (!transfer.isStandalone) {
            alert('This transfer is linked to a tour or meal. Please edit the associated tour/meal to modify transfer details.');
            return;
        }
        
        window.editingTransferIndex = index;
        
        // Reset all forms first
        resetAllTransferForms();
        
        // Set transport mode
        const transportMode = transfer.transportMode || 'local';
        const modeRadio = document.querySelector(`input[name="transferMode"][value="${transportMode}"]`);
        if (modeRadio) {
            modeRadio.checked = true;
            switchTransferMode(transportMode);
        }
        
        // Load data based on transport mode
        if (transportMode === 'local') {
            document.getElementById('localDateTime').value = transfer.dateTime || '';
            document.getElementById('localPickup').value = transfer.pickup || '';
            document.getElementById('localDrop').value = transfer.drop || '';
            document.getElementById('localDestination').value = transfer.destination || '';
            document.getElementById('localVehicleType').value = transfer.vehicleType || 'sedan';
            document.getElementById('localType').value = transfer.type || 'private';
            document.getElementById('localWay').value = transfer.way || 'one-way';
            document.getElementById('localAdults').value = transfer.adults || 2;
            document.getElementById('localChild').value = transfer.child || 0;
            document.getElementById('localCost').value = transfer.cost || 0;
            document.getElementById('localSell').value = transfer.sell || 0;
            document.getElementById('localTaxIncluded').checked = transfer.taxIncluded || false;
        }
        else if (transportMode === 'flight') {
            document.getElementById('flightDepartFrom').value = transfer.departFrom || '';
            document.getElementById('flightDestination').value = transfer.destination || '';
            document.getElementById('flightTripType').value = transfer.tripType || 'return';
            document.getElementById('flightDepartureDate').value = transfer.dateTime || '';
            document.getElementById('flightReturnDate').value = transfer.returnDate || '';
            document.getElementById('flightAirline').value = transfer.airline || '';
            document.getElementById('flightNumber').value = transfer.flightNumber || '';
            document.getElementById('flightOperator').value = transfer.operator || '';
            document.getElementById('flightClass').value = transfer.class || 'economy';
            document.getElementById('flightAdults').value = transfer.adults || 2;
            document.getElementById('flightChild').value = transfer.child || 0;
            document.getElementById('flightCost').value = transfer.cost || 0;
            document.getElementById('flightSell').value = transfer.sell || 0;
            document.getElementById('flightTaxIncluded').checked = transfer.taxIncluded || false;
        }
        else if (transportMode === 'cruise') {
            document.getElementById('cruiseDepartFrom').value = transfer.departFrom || '';
            document.getElementById('cruiseFromTerminal').value = transfer.fromTerminal || '';
            document.getElementById('cruiseBy').value = transfer.by || 'cruise';
            document.getElementById('cruiseType').value = transfer.cruiseType || '';
            document.getElementById('cruiseVessel').value = transfer.vessel || '';
            document.getElementById('cruiseArrivalTo').value = transfer.arrivalTo || '';
            document.getElementById('cruiseDepartureDate').value = transfer.dateTime || '';
            document.getElementById('cruiseArrivalDate').value = transfer.arrivalDate || '';
            document.getElementById('cruiseOperator').value = transfer.operator || '';
            document.getElementById('cruiseCabinClass').value = transfer.cabinClass || 'economy';
            document.getElementById('cruiseAdults').value = transfer.adults || 2;
            document.getElementById('cruiseChild').value = transfer.child || 0;
            document.getElementById('cruiseCost').value = transfer.cost || 0;
            document.getElementById('cruiseSell').value = transfer.sell || 0;
            document.getElementById('cruiseTaxIncluded').checked = transfer.taxIncluded || false;
        }
        else if (transportMode === 'train') {
            document.getElementById('trainDepartFrom').value = transfer.departFrom || '';
            document.getElementById('trainDestination').value = transfer.destination || '';
            document.getElementById('trainTripType').value = transfer.tripType || 'return';
            document.getElementById('trainDepartureDate').value = transfer.dateTime || '';
            document.getElementById('trainReturnDate').value = transfer.returnDate || '';
            document.getElementById('trainOperator').value = transfer.operator || '';
            document.getElementById('trainClass').value = transfer.class || '1st-class';
            document.getElementById('trainStationTransfer').value = transfer.stationTransfer || '';
            document.getElementById('trainAdults').value = transfer.adults || 2;
            document.getElementById('trainChild').value = transfer.child || 0;
            document.getElementById('trainCost').value = transfer.cost || 0;
            document.getElementById('trainSell').value = transfer.sell || 0;
            document.getElementById('trainTaxIncluded').checked = transfer.taxIncluded || false;
        }
        else if (transportMode === 'bus') {
            document.getElementById('busDepartFrom').value = transfer.departFrom || '';
            document.getElementById('busDestination').value = transfer.destination || '';
            document.getElementById('busTripType').value = transfer.tripType || 'return';
            document.getElementById('busDepartureDate').value = transfer.dateTime || '';
            document.getElementById('busReturnDate').value = transfer.returnDate || '';
            document.getElementById('busOperator').value = transfer.operator || '';
            document.getElementById('busClass').value = transfer.class || 'executive';
            document.getElementById('busStationTransfer').value = transfer.stationTransfer || '';
            document.getElementById('busAdults').value = transfer.adults || 2;
            document.getElementById('busChild').value = transfer.child || 0;
            document.getElementById('busCost').value = transfer.cost || 0;
            document.getElementById('busSell').value = transfer.sell || 0;
            document.getElementById('busTaxIncluded').checked = transfer.taxIncluded || false;
        }
        
        document.getElementById('transferModalTitleText').textContent = 'Edit Transfer Package';
        document.getElementById('saveTransferBtnText').textContent = 'Update Transfer';
        
        const transferModal = new bootstrap.Modal(document.getElementById('transferModal'));
        transferModal.show();
    }
    
    // Remove selected transfers
    function removeSelectedTransfers() {
        const checkboxes = document.querySelectorAll('.transfer-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Please select transfers to remove');
            return;
        }
        
        if (!confirm(`Remove ${checkboxes.length} selected transfer(s)?`)) {
            return;
        }
        
        const idsToRemove = Array.from(checkboxes).map(cb => parseInt(cb.value));
        transferList = transferList.filter(transfer => !idsToRemove.includes(transfer.id));
        
        updateTransferTable();
        recalculateTotals();
    }

    // ==================== TOTALS CALCULATION ====================
    
    // Recalculate all totals and populate footer table
    function recalculateTotals() {
        const tbody = document.getElementById('footerSummaryBody');
        let rows = [];
        
        // Calculate per-pax costs for tours, transfers, meals
        let tourCostPerPax = 0, tourSellPerPax = 0;
        let transferCostPerPax = 0, transferSellPerPax = 0;
        let childCostPerPax = 0, childSellPerPax = 0;
        
        const adults = parseInt(document.querySelector('.customer-details input[type="number"][value="2"]')?.value || 2);
        const children = parseInt(document.querySelector('.customer-details input[type="number"][value="1"]')?.value || 1);
        
        // Calculate tour costs per pax
        tourList.forEach(tour => {
            tourCostPerPax += tour.adultCost;
            tourSellPerPax += tour.adultSell;
            childCostPerPax += tour.childCost;
            childSellPerPax += tour.childSell;
        });
        
        // Calculate transfer costs per pax
        transferList.forEach(transfer => {
            const paxCount = (parseInt(transfer.adults) || 0) + (parseInt(transfer.child) || 0);
            if (paxCount > 0) {
                transferCostPerPax += (parseFloat(transfer.cost) || 0) / paxCount;
                transferSellPerPax += (parseFloat(transfer.sell) || 0) / paxCount;
            }
        });
        
        // Calculate meal costs per pax
        mealList.forEach(meal => {
            tourCostPerPax += meal.adultCost;
            tourSellPerPax += meal.adultSell;
            childCostPerPax += meal.childCost;
            childSellPerPax += meal.childSell;
        });
        
        // Add accommodation rows
        accommodationList.forEach((hotel, index) => {
            const nights = parseInt(hotel.nights) || 0;
            const roomPrice = parseFloat(hotel.roomPrice) || 0;
            const totalRoomCost = nights * roomPrice;
            
            // Calculate per-person cost based on sharing
            const singleCost = totalRoomCost + tourCostPerPax + transferCostPerPax;
            const singleSell = totalRoomCost + tourSellPerPax + transferSellPerPax;
            
            const twinCost = (totalRoomCost / 2) + tourCostPerPax + transferCostPerPax;
            const twinSell = (totalRoomCost / 2) + tourSellPerPax + transferSellPerPax;
            
            const tripleCost = (totalRoomCost / 3) + tourCostPerPax + transferCostPerPax;
            const tripleSell = (totalRoomCost / 3) + tourSellPerPax + transferSellPerPax;
            
            rows.push(`
                <tr>
                    <td style="padding: 3px 5px; border-right: 2px solid #dee2e6;">
                        <input type="checkbox" style="width: 12px; height: 12px; margin-right: 3px;">
                        ${hotel.hotelName}
                        <br><small class="text-muted" style="font-size: 8px;">${hotel.roomType || ''} | ${hotel.bedType || ''} | ${hotel.mealPlan || 'CP'}</small>
                    </td>
                    <td style="padding: 3px 5px; text-align: right; background: #fff3cd;">${singleSell.toFixed(2)}</td>
                    <td style="padding: 3px 5px; text-align: right; background: #d1ecf1;">${twinSell.toFixed(2)}</td>
                    <td style="padding: 3px 5px; text-align: right; background: #f8d7da;">${tripleSell.toFixed(2)}</td>
                    <td style="padding: 3px 5px; text-align: right; background: #d4edda;">${childSellPerPax.toFixed(2)}</td>
                    <td style="padding: 3px 5px; text-align: right; background: #e7d6f5;">${(childSellPerPax * 0.8).toFixed(2)}</td>
                    <td style="padding: 3px 5px; text-align: right; background: #e2e3e5;">0.00</td>
                </tr>
            `);
        });
        
        tbody.innerHTML = rows.join('');
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

        // Select all arrival/departure
        const selectAllArrivalDeparture = document.getElementById('selectAllArrivalDeparture');
        if (selectAllArrivalDeparture) {
            selectAllArrivalDeparture.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.arrivalDeparture-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        }

        // Initialize Select2 for port dropdowns
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2-port').select2({
                placeholder: 'Search and select port',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#accommodationModal')
            });
        }

        // Auto-load agents if agency is pre-selected from initial data
        @if(isset($initialData) && isset($initialData['agency_id']))
        const agencyId = {{ $initialData['agency_id'] }};
        if (agencyId) {
            // Trigger the loadAgentsByAgency function after a short delay
            setTimeout(function() {
                loadAgentsByAgency();
            }, 500);
        }
        @endif
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

