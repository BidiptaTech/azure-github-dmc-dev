@extends('layouts.layout')
@section('title', 'Tours')
@extends('layouts.datatablecss')
@section('content')

<!-- Add SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<!-- Add SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>




<!-- Add in the head section of your layout -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .select2-container .select2-selection--single {
        height: 100% !important; /* Adjust as needed */
        line-height: 100% !important;
        padding: 8px 12px;
    }
    /* Increase the height of the dropdown items */
    .select2-container .select2-results__option {
        padding: 12px 10px;
    }

    .hotel-status {
        padding: 6px 14px;
        font-size: 10px;
        font-weight: bold;
        border-radius: 8px;
        display: inline-block;
        text-shadow: 1px 1px 2px rgba(253, 245, 245, 0.722);
        transition: all 0.3s ease-in-out;
        box-shadow: 2px 4px 6px rgba(0, 0, 0, 0.15);
    }

    /* Light green effect for Approved */
    .hotel-approved {
        background-color: #a3eea3 !important; /* Light green */
        color: #1b5e20 !important; /* Dark green text */
        box-shadow: 0px 0px 10px rgba(76, 175, 80, 0.5);
    }

    /* Light red effect for Declined */
    .hotel-declined {
        background-color: #e5a6ab !important; /* Light red */
        color: #a71d2a !important; /* Dark red text */
        box-shadow: 0px 0px 10px rgba(220, 53, 69, 0.5);
    }

    /* For Custom Warning  */
    .custom-warning {
        background-color: #fff3cd; /* Light yellow background similar to Bootstrap warning */
        color: #856404; /* Darker yellow/brown text */
        padding: 15px;
        border: 1px solid #ffeeba; /* Border similar to alert */
        border-radius: 5px; /* Rounded corners */
        font-size: 16px;
        font-weight: 600;
        display: inline-block;
        width: 100%;
    }

    /* Payment Processing Overlay */
    .payment-processing-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        color: white;
        visibility: hidden;
        opacity: 0;
        transition: opacity 0.3s, visibility 0.3s;
    }
    
    .payment-processing-overlay.active {
        visibility: visible;
        opacity: 1;
    }
    
    .payment-spinner {
        width: 80px;
        height: 80px;
        border: 8px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: #38ef7d;
        animation: spin 1s ease-in-out infinite;
        margin-bottom: 20px;
    }
    
    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Modal Styles */
    .modal-content {
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        border-bottom: none;
        padding: 1rem;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        border-top: none;
        padding: 1rem;
    }

    /* Table Styles */
    .table {
        margin-bottom: 0;
    }

    .table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }

    .table td {
        vertical-align: middle;
    }

    /* Button Styles */
    .btn {
        padding: 0.5rem 1rem;
        font-weight: 500;
    }

    .btn-close {
        opacity: 1;
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .modal-dialog {
            margin: 0.5rem;
        }
        
        .table-responsive {
            margin-bottom: 1rem;
        }
    }

    /* Loading Overlay */
    .payment-processing-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        color: white;
    }

    .payment-spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 1rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Modern Loader Styles */
    .approve-booking-loader {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 9999;
        backdrop-filter: blur(5px);
    }

    .approve-loader-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0,0,0,0.2);
        width: 300px;
    }

    .approve-spinner {
        margin-bottom: 1rem;
    }

    .approve-spinner-circle {
        display: inline-block;
        width: 15px;
        height: 15px;
        background-color: #28a745;
        border-radius: 50%;
        margin: 0 5px;
        animation: bounce 0.5s ease-in-out infinite;
    }

    .approve-spinner-circle:nth-child(2) {
        animation-delay: 0.1s;
    }

    .approve-spinner-circle:nth-child(3) {
        animation-delay: 0.2s;
    }

    .approve-loader-text {
        color: #333;
        font-size: 1.1rem;
        margin-bottom: 1rem;
        font-weight: 500;
    }

    .approve-loader-progress {
        width: 100%;
        height: 4px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }

    .approve-loader-bar {
        width: 0%;
        height: 100%;
        background: #28a745;
        border-radius: 4px;
        animation: progress 2s ease-in-out infinite;
    }

    /* Success Message Styles */
    .success-message {
        display: none;
        position: fixed;
        top: 20px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        animation: slideIn 0.5s ease-out;
    }

    /* Animations */
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    @keyframes progress {
        0% { width: 0%; }
        50% { width: 70%; }
        100% { width: 100%; }
    }

    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* Payment validation styles */
    .is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 5px rgba(220, 53, 69, 0.3) !important;
    }

    .payment-validation-error {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 5px;
        padding: 8px 12px;
        margin-top: 5px;
    }

    /* Custom pagination styles to match DataTables */
    .dataTables_info {
        color: #6c757d;
        font-size: 0.875rem;
        font-weight: 400;
        line-height: 1.5;
    }
    
    .dataTables_paginate .pagination {
        margin-bottom: 0;
    }
    
    .dataTables_paginate .page-link {
        color: #6c757d;
        padding: 0.375rem 0.75rem;
        margin-left: -1px;
        line-height: 1.25;
        border: 1px solid #dee2e6;
        background-color: #fff;
    }
    
    .dataTables_paginate .page-link:hover {
        color: #495057;
        background-color: #e9ecef;
        border-color: #dee2e6;
    }
    
    .dataTables_paginate .page-item.active .page-link {
        color: #fff;
        background-color: #007bff;
        border-color: #007bff;
    }
    
    .dataTables_paginate .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #fff;
        border-color: #dee2e6;
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Tour Listing</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">

                        <!-- Export Dropdown Button -->
                        <div class="dropdown">
                            <button class="btn btn-warning btn-sm dropdown-toggle" type="button" id="exportDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportCopy">Copy</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportCSV">CSV</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportExcel">Excel</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportPDF">PDF</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportPrint">Print</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <x-alert />
                <table class="datatables-basic table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tour ID</th>
                            @if(auth()->user()->role_id == 10)
                                <th>DMC</th>
                                <th>Agent Name</th>
                            @elseif(auth()->user()->role_id == 11)
                                <th>Agent Name</th>
                            @else
                                <th>Master DMC</th>
                                <th>DMC</th>
                                <th>Agent Name</th>
                            @endif
                            <th>Tour Date Range</th>
                            <th>Destination</th>
                            <th>Pax</th>
                            <th>Service Info</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th>Total Amount</th>
                            <th>Discount Amount</th>
                            <th>Final Amount</th>
                            <th>Due Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($tours) > 0)
                            @foreach($tours as $key => $tour)
                            <tr data-tour-id="{{ $tour->tour_id }}">  <!-- Tour ID added here -->
                                <td>{{ ++$key }}</td>
                                <td>{{ $tour->display_id }}</td>
                                {{-- Add these columns after the Tour Id column --}}
                                @if(auth()->user()->role_id == 10)
                                <td>{{ $tour->dmc_company ?? 'N/A' }}</td>
                                <td>{{ $tour->agent_name ?? 'N/A' }}</td>
                                @elseif(auth()->user()->role_id == 11)
                                <td>{{ $tour->agent_name ?? 'N/A' }}</td>
                                @else
                                <td>{{ $tour->master_dmc_company ?? 'N/A' }}</td>
                                {{-- <td>{{ $tour->dmc_name ?? 'N/A' }}</td> --}}
                                <td>{{ $tour->dmc_company ?? 'N/A' }}</td>
                                <td>{{ $tour->agent->name ?? 'N/A' }}</td>
                                @endif
                                <td class="category-name">
                                    {{ $tour->check_in_time ? \App\Helpers\CommonHelper::DateFormatAdmin($tour->check_in_time) : 'N/A' }} - {{ $tour->check_out_time ? \App\Helpers\CommonHelper::DateFormatAdmin($tour->check_out_time) : 'N/A' }}</td>
                                <td class="category-name">{{ $tour->destination }}</td>
                                <td>
                                    @if($tour->child || $tour->adult)
                                        {{ ($tour->adult ?? 0) + ($tour->child ?? 0) }}
                                        @else
                                        {{ 0 }}
                                    @endif
                                </td>
                                    <td>
                                        <!-- Services Button (Opens Modal) -->
                                        <button type="button" class="btn btn-sm btn-primary w-100 p-2"
                                            style="background: linear-gradient(135deg, #6a11cb, #2575fc); border: none; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);"
                                            data-bs-toggle="modal" data-bs-target="#servicesModal{{ $tour->tour_id }}">
                                            <i class="fas fa-concierge-bell"></i> Services
                                        </button>

                                        <!-- Services Modal -->
                                        <div class="modal fade" id="servicesModal{{ $tour->tour_id }}" tabindex="-1"
                                            aria-labelledby="servicesModalLabel{{ $tour->tour_id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content shadow-lg rounded">
                                                    <div class="modal-header bg-primary text-white d-flex align-items-center justify-content-start" style="padding: 15px; border-radius: 8px;">
                                                        <h5 class="modal-title d-flex align-items-center" id="servicesModalLabel{{ $tour->tour_id }}" style="margin: 0; font-weight: bold; color: white;">
                                                            <i class="fas fa-concierge-bell me-2" style="color: #FFD700; font-size: 1.4rem;"></i> 
                                                            <span style="color: white;">Tour Services for <strong>{{ $tour->destination }}</strong> 
                                                                <span class="badge bg-warning text-dark ms-2" style="font-size: 0.9rem; padding: 5px 10px; border-radius: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                                                    #{{ $tour->tour_id }}
                                                                </span>
                                                            </span>
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                                                    </div>

                                                    <div class="modal-body">
                                                        <div class="row g-4 text-center">
                                                            <!-- Hotels -->
                                                            <div class="col-4">
                                                                <div class="position-relative w-100">
                                                                    <button type="button" class="btn w-100 text-white p-1 rounded shadow"
                                                                        style="background: linear-gradient(135deg, #ff7e5f, #feb47b); border: none;"
                                                                        data-bs-toggle="modal" data-bs-target="#hotelModal{{ $tour->tour_id }}">
                                                                        <i class="fas fa-hotel"></i><br> Hotels
                                                                    </button>
                                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark px-2 py-1">
                                                                        {{ !empty($hotels[$tour->tour_id]) ? count($hotels[$tour->tour_id]) : 0 }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                        
                                                            <!-- Attractions -->
                                                            <div class="col-4">
                                                                <div class="position-relative w-100">
                                                                    <button type="button" class="btn w-100 text-white p-1 rounded shadow"
                                                                        style="background: linear-gradient(135deg, #56ccf2, #2f80ed); border: none;"
                                                                        data-bs-toggle="modal" data-bs-target="#attractionModal{{ $tour->tour_id }}">
                                                                        <i class="fas fa-map-marked-alt"></i><br> Attractions
                                                                    </button>
                                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark px-2 py-1">
                                                                        {{ !empty($attractions[$tour->tour_id]) ? count($attractions[$tour->tour_id]) : 0 }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                        
                                                            <!-- Guide -->
                                                            <div class="col-4">
                                                                <div class="position-relative w-100">
                                                                    <button type="button" class="btn w-100 text-white p-1 rounded shadow"
                                                                        style="background: linear-gradient(135deg, #11998e, #38ef7d); border: none;"
                                                                        data-bs-toggle="modal" data-bs-target="#guideModal{{ $tour->tour_id }}">
                                                                        <i class="fas fa-user-tie"></i><br> Guide
                                                                    </button>
                                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark px-2 py-1">
                                                                        {{ $tour->booking->filter(function($booking) {
                                                                            return in_array($booking->status, [1,2, 3]) && $booking->type === 'guide' && $booking->bookingType === 'booking' && $booking->is_approve === true;
                                                                        })->count() }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                        
                                                            <!-- Travel -->
                                                            <div class="col-4">
                                                                <div class="position-relative w-100">
                                                                    <button type="button" class="btn w-100 text-white p-1 rounded shadow"
                                                                        style="background: linear-gradient(135deg, #ff416c, #ff4b2b); border: none;"
                                                                        data-bs-toggle="modal" data-bs-target="#travelModal{{ $tour->tour_id }}">
                                                                        <i class="fas fa-bus"></i><br> Travel
                                                                    </button>
                                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark px-2 py-1">
                                                                        {{ $tour->booking->filter(function($booking) {
                                                                            return in_array($booking->status, [1,2, 3]) && in_array($booking->type, ['travel_hourly', 'travel_point']) && $booking->bookingType === 'booking' && $booking->is_approve === true;
                                                                        })->count() }}                                                                                                                                            
                                                                    </span>
                                                                </div>
                                                            </div>
                                        
                                                            <!-- Restaurants -->
                                                            <div class="col-4">
                                                                <div class="position-relative w-100">
                                                                    <button type="button" class="btn w-100 text-white p-1 rounded shadow"
                                                                        style="background: linear-gradient(135deg, #fc4a1a, #f7b733); border: none;"
                                                                        data-bs-toggle="modal" data-bs-target="#restaurantModal{{ $tour->tour_id }}">
                                                                        <i class="fas fa-utensils"></i><br> Restaurants
                                                                    </button>
                                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark px-2 py-1">
                                                                        {{-- {{ $tour->restaurants ? $tour->restaurants->count() : 0 }} --}}
                                                                        {{ $tour->booking->filter(function($booking) {
                                                                            return in_array($booking->status, [1,2, 3]) && $booking->type === 'restaurant' && $booking->bookingType === 'booking' && $booking->is_approve === true;
                                                                        })->count() }}
                                                                    </span>
                                                                </div>
                                                            </div>


                                                            <!-- Pick Up & Drop -->
                                                            <div class="col-4">
                                                                <div class="position-relative w-100">
                                                                    <button type="button" class="btn w-100 text-white p-1 rounded shadow"
                                                                        style="background: linear-gradient(135deg, #1e3c72, #2a5298); border: none;"
                                                                        data-bs-toggle="modal" data-bs-target="#pickUpDropModal{{ $tour->tour_id }}">
                                                                        <i class="fas fa-shuttle-van"></i><br> Pick Up & Drop
                                                                    </button>
                                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark px-2 py-1">
                                                                        {{ $tour->booking->filter(function($booking) {
                                                                            return in_array($booking->status, [1,2, 3]) && $booking->type === 'entry_port' && $booking->bookingType === 'booking' && $booking->is_approve === true;
                                                                        })->count() }}
                                                                    </span>
                                                                </div>
                                                            </div>



                                                        </div>
                                                    </div>
                                                    <div class="modal-header bg-light text-white d-flex align-items-center justify-content-end" style="padding: 15px; border-radius: 8px;">
                                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                             Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>                                        
                                        <!-- Hotel Modal -->
                                        <div class="modal fade" id="hotelModal{{ $tour->tour_id }}" tabindex="-1"
                                            aria-labelledby="hotelModalLabel{{ $tour->tour_id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                                <div class="modal-content shadow-lg rounded">

                                                    <div class="modal-header bg-primary text-white d-flex align-items-center justify-content-start" style="padding: 15px; border-radius: 8px;">
                                                        <h5 class="modal-title d-flex align-items-center" id="hotelModalLabel{{ $tour->tour_id }}" style="margin: 0; font-weight: bold; color: white;">
                                                            <i class="fas fa-hotel" style="color: #dee12e; font-size: 1.4rem; margin-right:5px;"></i> 
                                                            <span style="color: white;">Hotels for Tour: <strong>{{ $tour->destination }}</strong></span>
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                                                    </div>
                                                    
                                                    <div class="modal-body">
                                                    @php
                                                            // Ensure $tourHotels is correctly assigned for each tour
                                                        $tourHotels = $hotels[$tour->tour_id] ?? [];
                                                    @endphp
                                                    
                                                        @if(!empty($tourHotels))
                                                        <!-- Table -->
                                                        <div class="table-responsive">
                                                                <table id="hotelTable_{{ $tour->tour_id }}" class="table table-bordered table-striped align-middle datatables-basic"
                                                                    data-tour-id="{{ $tour->tour_id }}">
                                                                    <thead class="table-light">
                                                                    <tr>
                                                                        <th>No</th>
                                                                        <th>Hotel Name</th>
                                                                        <th>Room</th>
                                                                        <th>Bed</th>
                                                                        <th>Pax</th>
                                                                        <th>Details</th>
                                                                        <th>Price</th>
                                                                        <th>Status</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($tourHotels as $key => $order)
                                                                        @foreach ($order['hotel_details'] as $hotel)
                                                                            @php
                                                                                // Collect all unique room types from all rooms
                                                                                $roomTypes = collect($hotel['rooms'])->pluck('room_type')->unique()->implode(', ');
                                                                
                                                                                // Collect all beds from all rooms
                                                                                $allBeds = collect($hotel['rooms'])->pluck('beds')->flatten(1);
                                                                
                                                                                // Get unique bed types
                                                                                $bedTypes = $allBeds->pluck('bed_type')->unique()->implode(', ');
                                                                
                                                                                // Calculate total head count
                                                                                $totalHeadCount = $allBeds->sum('head_count');
                                                                                @endphp
                                                                                <tr>
                                                                                <td>{{ $key + 1 }}</td> 
                                                                                    <td>
                                                                                        <div class="p-2 rounded shadow-sm text-white" style="background: #d4edda; border-radius: 10px;">
                                                                                            <strong class="text-dark">{{ $hotel['hotelDetails']['hotel_name'] ?? 'N/A' }}</strong><br>
                                                                                            <small class="text-success">{{ $hotel['hotelDetails']['location'] ?? 'N/A' }}</small>
                                                                                        </div>
                                                                                    </td>
                                                                                <td>{{ $roomTypes }}</td>
                                                                                <td>{{ $bedTypes }}</td>
                                                                                <td>{{ $totalHeadCount }}</td>
                                                                                <td class="text-wrap text-break" style="min-width: 170px;">
                                                                                        <strong>Name:</strong> {{ $hotel['fullName'] ?? 'N/A' }} <br>
                                                                                        <strong>Email:</strong> {{ $hotel['email'] ?? 'N/A' }} <br>
                                                                                        <strong>Phone:</strong> {{ $hotel['phone'] ?? 'N/A' }} <br>
                                                                                    <strong>Address:</strong> {{ $hotel['address1'] ?? 'N/A' }}, 
                                                                                    {{ $hotel['state'] ?? 'N/A' }} - {{ $hotel['zip'] ?? 'N/A' }}
                                                                                    </td>
                                                                                    <td>{{ $hotel['totalPrice'] ?? 'N/A' }}</td>
                                                                                    <td>
                                                                                        @if($order['status'] == 1)
                                                                                            <span class="hotel-status hotel-approved">Approved</span>
                                                                                        @elseif($order['status'] == 3)
                                                                                            <span class="hotel-status hotel-declined">Declined</span>
                                                                                        @endif
                                                                                    </td>                                                                                                                                                                                                                                                                                                                                                                                      
                                                                                </tr>
                                                                            @endforeach
                                                                    @endforeach
                                                                </tbody>                                                                                                                                
                                                                </table>
                                                            </div>
                                                        @else
                                                            <div class="custom-warning text-center">
                                                                <i class="fas fa-info-circle"></i> No hotels found for this tour.
                                                            </div>                                                        
                                                        @endif

                                                    </div>
                                                    <div class="modal-header bg-light text-white d-flex align-items-center justify-content-end" style="padding: 15px; border-radius: 8px;">
                                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                             Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Hotel Modal -->

                                        <!-- Attraction Modal -->
                                        <div class="modal fade" id="attractionModal{{ $tour->tour_id }}" tabindex="-1"
                                            aria-labelledby="attractionModalLabel{{ $tour->tour_id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                                <div class="modal-content shadow-lg rounded">

                                                    <div class="modal-header bg-primary text-white d-flex align-items-center justify-content-start" style="padding: 15px; border-radius: 8px;">
                                                        <h5 class="modal-title d-flex align-items-center" id="attractionModalLabel{{ $tour->tour_id }}" style="margin: 0; font-weight: bold; color: white;">
                                                            <i class="fas fa-map-marked-alt" style="color: #42dd47; font-size: 1.4rem; margin-right:5px;"></i> 
                                                            <span style="color: white;"> Attractions for Tour: <strong>{{ $tour->destination }}</strong></span>
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                                                    </div>
                                                    
                                                    <div class="modal-body">
                                                        @php
                                                            // Ensure $tourAttractions is correctly assigned for each tour
                                                            $tourAttractions = $attractions[$tour->tour_id] ?? [];
                                                        @endphp
                                                        
                                                        @if(!empty($tourAttractions))
                                                            <!-- Table -->
                                                            <div class="table-responsive">
                                                                <table id="hotelTable_{{ $tour->tour_id }}" class="table table-bordered table-striped align-middle datatables-basic"
                                                                    data-tour-id="{{ $tour->tour_id }}">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th>No</th>
                                                                            <th>Attraction Name</th>
                                                                            <th>Booking Date</th>
                                                                            <th>Pax</th>
                                                                            <th>Visit Time</th>
                                                                            <th>Details</th>
                                                                            <th>Price</th>
                                                                            <th>Status</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($tourAttractions as $key => $order)
                                                                            @foreach ($order['attraction_details'] as $attraction)
                                                                                <tr>
                                                                                    <td>{{  $key + 1}}</td>
                                                                                    <!-- Attraction Name -->
                                                                                    <td>
                                                                                        <div class="p-2 rounded shadow-sm text-white" style="background: #d4edda; border-radius: 10px;">
                                                                                            <strong class="text-dark">{{ $attraction['AttractionName'] ?? 'N/A' }}</strong>
                                                                                        </div>
                                                                                    </td>
                                                                    
                                                                                    <!-- Booking Date -->
                                                                                    <td>{{ isset($attraction['bookingDate']) && $attraction['bookingDate'] ? \App\Helpers\CommonHelper::DateFormatAdmin($attraction['bookingDate']) : 'N/A' }}</td>

                                                                                    <!-- Pax (Adult + Child) -->
                                                                                    <td>{{ ($attraction['adultCount'] ?? 0) + ($attraction['childCount'] ?? 0) }}</td>

                                                                                    <!-- Visit Time -->
                                                                                    <td>{{ $attraction['visitTime'] ?? 'N/A' }}</td>
                                                                    
                                                                                    <!-- Contact & Address Details -->
                                                                                    <td class="text-wrap text-break" style="min-width: 170px;">
                                                                                        <strong>Name:</strong> {{ $attraction['fullName'] ?? 'N/A' }} <br>
                                                                                        <strong>Email:</strong> {{ $attraction['email'] ?? 'N/A' }} <br>
                                                                                        <strong>Phone:</strong>{{ $attraction['countryCode'] ?? 'N/A' }} - {{ $attraction['phone'] ?? 'N/A' }} <br>
                                                                                        <strong>Address:</strong> {{ $attraction['address1'] ?? 'N/A' }}, 
                                                                                        {{ $attraction['state'] ?? 'N/A' }} - {{ $attraction['zip'] ?? 'N/A' }}
                                                                                    </td>

                                                                                    <!-- Price Details -->
                                                                                    <td>{{ $attraction['totalPrice'] ?? 'N/A' }}</td>
                                                                    
                                                                                    <!-- Status -->
                                                                                    <td>
                                                                                        @if($order['status'] == 1)
                                                                                            <span class="hotel-status hotel-approved">Approved</span>
                                                                                        @elseif($order['status'] == 3)
                                                                                            <span class="hotel-status hotel-declined">Declined</span>
                                                                                         @endif
                                                                                    </td>
                                                                                </tr>
                                                                        @endforeach
                                                                    @endforeach
                                                                </tbody>                                                                
                                                            </table>
                                                            </div>
                                                            
                                                        @else
                                                            <div class="custom-warning text-center">
                                                                <i class="fas fa-info-circle"></i> No attractions found for this tour.
                                                        </div>
                                                    @endif

                                                    </div>
                                                    <div class="modal-header bg-light text-white d-flex align-items-center justify-content-end" style="padding: 15px; border-radius: 8px;">
                                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                             Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Attraction Modal -->

                                        <!-- Guide Modal -->
                                        <div class="modal fade" id="guideModal{{ $tour->tour_id }}" tabindex="-1"
                                            aria-labelledby="guideModalLabel{{ $tour->tour_id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                                <div class="modal-content shadow-lg rounded">

                                                    <div class="modal-header bg-primary text-white d-flex align-items-center justify-content-start" style="padding: 15px; border-radius: 8px;">
                                                        <h5 class="modal-title d-flex align-items-center" id="guideModalLabel{{ $tour->tour_id }}" style="margin: 0; font-weight: bold; color: white;">
                                                            <i class="fas fa-user-tie" style="color: #2f2f2e; font-size: 1.4rem; margin-right:5px;"></i> 
                                                            <span style="color: white;"> Guides for Tour: <strong>{{ $tour->destination }}</strong></span>
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        @php
                                                            $tourGuides = $tour_guides[$tour->tour_id] ?? [];
                                                        @endphp
                                                        
                                                        @if(!empty($tourGuides))
                                                            <div class="table-responsive">
                                                                <table id="guideTable_{{ $tour->tour_id }}" class="table table-bordered table-striped align-middle datatables-basic"
                                                                    data-tour-id="{{ $tour->tour_id }}">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th>Guide Name</th>
                                                                            <th>Booking Date</th>
                                                                            <th>Pax</th>
                                                                            <th>Entry Pick Up</th>
                                                                            <th>Pick Date</th>
                                                                            <th>Entry Time</th>
                                                                            <th>Details</th>
                                                                            <th>Price</th>
                                                                            <th>Status</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($tourGuides as $order)
                                                                            @foreach ($order['guide_details'] as $tour_guide)
                                                                                <tr>
                                                                                    <td>
                                                                                        <div class="p-2 rounded shadow-sm text-white" style="background: #d4edda; border-radius: 10px;">
                                                                                            <strong class="text-dark">{{ $tour_guide['guide_name'] ?? 'N/A' }}</strong>
                                                                                        </div>
                                    </td>                                    
                                                <td>{{ isset($tour_guide['bookingDate']) && $tour_guide['bookingDate'] ? \App\Helpers\CommonHelper::DateFormatAdmin($tour_guide['bookingDate']) : 'N/A' }}</td>
                                                <td>{{ ($tour_guide['adults'] ?? 0) + ($tour_guide['children'] ?? 0) }}</td>
                                                <td>{{ $tour_guide['entrypickup'] ?? 'N/A' }}</td>
                                                <td>{{ isset($tour_guide['pickupdate']) && $tour_guide['pickupdate'] ? \App\Helpers\CommonHelper::DateFormatAdmin($tour_guide['pickupdate']) : 'N/A' }}</td>
                                                <td>{{ $tour_guide['entrytime'] ?? 'N/A' }}</td>
                                                <td class="text-wrap text-break" style="min-width: 170px;">
                                                    <strong>Name:</strong> {{ $tour_guide['fullName'] ?? 'N/A' }} <br>
                                                    <strong>Email:</strong> {{ $tour_guide['email'] ?? 'N/A' }} <br>
                                                    <strong>Phone:</strong> {{ $tour_guide['countryCode'] ?? '' }} {{ $tour_guide['phone'] ?? 'N/A' }} <br>
                                                    <strong>Address:</strong> {{ $tour_guide['address1'] ?? 'N/A' }}, 
                                                    {{ $tour_guide['state'] ?? 'N/A' }} - {{ $tour_guide['zip'] ?? 'N/A' }}
                                                </td>
                                                <td>{{ $tour_guide['totalPrice'] ?? 'N/A' }}</td>
                                                <td>
                                                    @if($order['status'] == 1)
                                                        <span class="hotel-status hotel-approved">Approved</span>
                                                    @elseif($order['status'] == 3)
                                                        <span class="hotel-status hotel-declined">Declined</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                                                        @endforeach
                                                                    </tbody>                                                                                                                                                                                          
                                                                </table>
                                                            </div>
                                                        @else
                                                            <div class="custom-warning text-center">
                                                                <i class="fas fa-info-circle"></i> No guides found for this tour.
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-header bg-light text-white d-flex align-items-center justify-content-end" style="padding: 15px; border-radius: 8px;">
                                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                             Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Guide Modal -->

                                        <!-- Travel Modal -->
                                        <div class="modal fade" id="travelModal{{ $tour->tour_id }}" tabindex="-1"
                                            aria-labelledby="travelModalLabel{{ $tour->tour_id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                                <div class="modal-content shadow-lg rounded">
                                                    
                                                    <div class="modal-header bg-primary text-white d-flex align-items-center justify-content-start" style="padding: 15px; border-radius: 8px;">
                                                        <h5 class="modal-title d-flex align-items-center" id="guideModalLabel{{ $tour->tour_id }}" style="margin: 0; font-weight: bold; color: white;">
                                                            <i class="fas fa-bus" style="color: #d7b52e; font-size: 1.4rem; margin-right:5px;"></i> 
                                                            <span style="color: white;"> Travels for Tour: <strong>{{ $tour->destination }}</strong></span>
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        @php
                                                            // Ensure $tourTravels is correctly assigned for each tour
                                                            $tourTravels = $travels[$tour->tour_id] ?? [];
                                                        @endphp
                                                        
                                                        @if(!empty($tourTravels))
                                                            <!-- Table -->
                                                            <div class="table-responsive">
                                                                <table id="travelTable_{{ $tour->tour_id }}" class="table table-bordered table-striped align-middle datatables-basic"
                                                                    data-tour-id="{{ $tour->tour_id }}">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th>Vehicle Name</th>
                                                                            <th>Booking Date</th>
                                                                            <th>Pax</th>
                                                                            {{-- <th>Exit Pick Up Date</th> --}}
                                                                            <th>Entry Time</th>
                                                                            <th>City</th>
                                                                            <th>Country</th>
                                                                            <th>User Details</th>
                                                                            <th>Price</th>
                                                                            <th>Status</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($tourTravels as $order)
                                                                            @foreach ($order['travel_details'] as $travel)
                                                                                <tr>
                                                                                    <td>
                                                                                        <div class="p-2 rounded shadow-sm text-white" style="background: #d4edda; border-radius: 10px;">
                                                                                            <strong class="text-dark">{{ $travel['vehicles_name'] ?? 'N/A' }}</strong>
                                    </div>
                                </td>
                                                                                    <td>{{ isset($travel['bookingDate']) && $travel['bookingDate'] ? \App\Helpers\CommonHelper::DateFormatAdmin($travel['bookingDate']) : 'N/A' }}</td>
                                                                                    <td>{{ ($travel['adults'] ?? 0) + ($travel['children'] ?? 0) }}</td>
                                                                                    <td>{{ $travel['entrytime'] ?? 'N/A' }}</td>
                                                                                    <td>{{ $travel['city'] ?? 'N/A' }}</td>
                                                                                    <td>{{ $travel['country'] ?? 'N/A' }}</td>
                                                                                    <td class="text-wrap text-break" style="min-width: 170px;">
                                                                                        <strong>Name:</strong> {{ $travel['fullName'] ?? 'N/A' }} <br>
                                                                                        <strong>Email:</strong> {{ $travel['email'] ?? 'N/A' }} <br>
                                                                                        <strong>Phone:</strong> {{ $travel['countryCode'] ?? '' }} {{ $travel['phone'] ?? 'N/A' }} <br>
                                                                                        <strong>Address:</strong> {{ $travel['address1'] ?? 'N/A' }}, 
                                                                                        {{ $travel['state'] ?? 'N/A' }} - {{ $travel['zip'] ?? 'N/A' }}
                                                                                    </td>
                                                                                    <td>{{ $travel['totalPrice'] ?? 'N/A' }}</td>
                                                                                    <td>
                                                                                        @if($order['status'] == 1)
                                                                                            <span class="hotel-status hotel-approved">Approved</span>
                                                                                        @elseif($order['status'] == 3)
                                                                                            <span class="hotel-status hotel-declined">Declined</span>
                                                                                        @endif
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        @endforeach
                                                                    </tbody>                                                                                                                                                                                          
                                                                </table>
                                                            </div>
                                                            
                                                        @else
                                                            <div class="custom-warning text-center">
                                                                <i class="fas fa-info-circle"></i> No travels found for this tour.
                                                            </div>                                                        
                                                        @endif

                                                    </div>
                                                    <div class="modal-header bg-light text-white d-flex align-items-center justify-content-end" style="padding: 15px; border-radius: 8px;">
                                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                             Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Travel Modal -->

                                        <!-- Restaurant Modal -->
                                        <div class="modal fade" id="restaurantModal{{ $tour->tour_id }}" tabindex="-1"
                                            aria-labelledby="restaurantModalLabel{{ $tour->tour_id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                                <div class="modal-content shadow-lg rounded">

                                                    <div class="modal-header bg-primary text-white d-flex align-items-center justify-content-start" style="padding: 15px; border-radius: 8px;">
                                                        <h5 class="modal-title d-flex align-items-center" id="restaurantModalLabel{{ $tour->tour_id }}" style="margin: 0; font-weight: bold; color: white;">
                                                            <i class="fas fa-utensils" style="color: #d7b52e; font-size: 1.4rem; margin-right:5px;"></i> 
                                                            <span style="color: white;"> Restaurants for Tour: <strong>{{ $tour->destination }}</strong></span>
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        @php
                                                            // Ensure $tourRestaurants is correctly assigned for each tour
                                                            $tourRestaurants = $restaurants[$tour->tour_id] ?? [];
                                                        @endphp
                                                        
                                                        @if(!empty($tourRestaurants))
                                                            <!-- Table -->
                                                            <div class="table-responsive">
                                                                <table id="restaurantTable_{{ $tour->tour_id }}" class="table table-bordered table-striped align-middle datatables-basic"
                                                                    data-tour-id="{{ $tour->tour_id }}">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th>Restaurant Name</th>
                                                                            <th>Booking Date</th>
                                                                            <th>Pax</th>
                                                                            <th>Visit Time</th>
                                                                            <th>Meal Type</th>
                                                                            <th>Details</th>
                                                                            <th>Meal Details</th>
                                                                            <th>Price</th>
                                                                            <th>Status</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($tourRestaurants as $order)
                                                                            @foreach ($order['restaurant_details'] as $restaurant)
                                                                                <tr>
                                                                                    <td>
                                                                                        <div class="p-2 rounded shadow-sm text-white" style="background: #d4edda; border-radius: 10px;">
                                                                                            <strong class="text-dark">{{ $restaurant['restaurantName'] ?? 'N/A' }}</strong>
                                                                                        </div>
                                                                                    </td>
                                                                                    <td>{{ isset($restaurant['bookingDate']) && $restaurant['bookingDate'] ? \App\Helpers\CommonHelper::DateFormatAdmin($restaurant['bookingDate']) : 'N/A' }}</td>
                                                                                    <td>{{ ($restaurant['adultCount'] ?? 0) + ($restaurant['childCount'] ?? 0) }}</td>
                                                                                    <td>{{ $restaurant['visitTime'] ?? 'N/A' }}</td>
                                                                                    <td>{{ $restaurant['mealType'] ?? 'N/A' }}</td>
                                                                                    <td class="text-wrap text-break" style="min-width: 170px;">
                                                                                        <strong>Name:</strong> {{ $restaurant['fullName'] ?? 'N/A' }} <br>
                                                                                        <strong>Email:</strong> {{ $restaurant['email'] ?? 'N/A' }} <br>
                                                                                        <strong>Phone:</strong> {{ $restaurant['countryCode'] ?? '' }} {{ $restaurant['phone'] ?? 'N/A' }} <br>
                                                                                        <strong>Address:</strong> {{ $restaurant['address1'] ?? 'N/A' }}, 
                                                                                        {{ $restaurant['state'] ?? 'N/A' }} - {{ $restaurant['zip'] ?? 'N/A' }}
                                                                                    </td>
                                                                                    <td class="text-wrap text-break" style="min-width: 150px;">
                                                                                        @if (!empty($restaurant['MealDescription']) && is_array($restaurant['MealDescription']))
                                                                                            @foreach ($restaurant['MealDescription'] as $meal)
                                                                                                <strong>Item Name:</strong> {{ $meal['item_name'] ?? 'N/A' }} <br>
                                                                                                {{-- <strong>Name:</strong> {{ $meal['name'] ?? 'N/A' }} <br> --}}
                                                                                                <strong>Price:</strong> {{ $meal['price'] ?? '' }}<br>
                                                                                                <strong>Category:</strong> {{ $meal['category'] ?? 'N/A' }}<br>
                                                                                                <strong>Item Type:</strong> {{ $meal['item_type'] ?? 'N/A' }}<br>
                                                                                                <strong>Quantity:</strong> {{ $meal['quantity'] ?? 'N/A' }}<br>
                                                                                                {{-- <hr> <!-- Optional: Add a separator for multiple meals --> --}}
                                            @endforeach
                                                                                        {{-- @else
                                                                                            N/A --}}
                                                                                        @endif
                                                                                    </td>
                                                                                    
                                                                                    <td>{{ $restaurant['totalPrice'] ?? 'N/A' }}</td>
                                                                                    <td>
                                                                                        @if($order['status'] == 1)
                                                                                            <span class="hotel-status hotel-approved">Approved</span>
                                                                                        @elseif($order['status'] == 3)
                                                                                            <span class="hotel-status hotel-declined">Declined</span>
                                                                                        @endif
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        @endforeach
                                                                    </tbody>                                                                                                                                                                                          
                                                                </table>
                                                            </div>
                                                            
                                                        @else
                                                            <div class="custom-warning text-center">
                                                                <i class="fas fa-info-circle"></i> No restaurants found for this tour.
                                                            </div>
                                                        @endif

                                                    </div>
                                                    <div class="modal-header bg-light text-white d-flex align-items-center justify-content-end" style="padding: 15px; border-radius: 8px;">
                                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                             Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Restaurant Modal -->

                                        <!-- Pick Up & Drop Modal -->
                                        <div class="modal fade" id="pickUpDropModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="pickUpDropLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content rounded-4 shadow-lg">
                                                    <!-- Modal Header -->

                                                    <div class="modal-header bg-primary text-white d-flex align-items-center justify-content-start" style="padding: 15px; border-radius: 8px;">
                                                        <h5 class="modal-title d-flex align-items-center" style="margin: 0; font-weight: bold; color: white;">
                                                            <i class="fas fa-shuttle-van me-2" style="color: #d7b52e; font-size: 1.4rem; margin-right:5px;"></i> 
                                                            <span style="color: white;"> Pick Up & Drop Details for Tour: <strong>{{ $tour->destination }}</strong></span>
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                                                    </div>

                                                    <!-- Modal Body -->
                                                    <div class="modal-body text-center p-4">
                                                        <div class="row justify-content-center">
                                                            <!-- Entry Port Button (Pick Up) -->
                                                            <div class="col-md-5">
                                                                <button type="button" class="btn btn-outline-primary position-relative w-100 p-3 shadow-sm rounded-3"
                                                                    data-bs-toggle="modal" data-bs-target="#pickupListModal{{ $tour->tour_id }}" data-type="entry">
                                                                    <i class="fas fa-sign-in-alt me-2"></i> Pick Up (Entry)
                                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                                        {{ $tour->booking->filter(fn($booking) => in_array($booking->status, [1,2, 3]) && $booking->type === 'entry_port' && $booking->bookingType === 'booking' && $booking->is_approve === true)->count() }}
                                                                    </span>
                                                                </button>
                                                            </div>

                                                            <!-- Exit Port Button (Drop Off) -->
                                                            <div class="col-md-5">
                                                                <button type="button" class="btn btn-outline-secondary position-relative w-100 p-3 shadow-sm rounded-3"
                                                                    data-bs-toggle="modal" data-bs-target="#pickupDropListModal{{ $tour->tour_id }}" data-type="exit">
                                                                    <i class="fas fa-sign-out-alt me-2"></i> Drop Off (Exit)
                                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                                        {{ $tour->booking->filter(fn($booking) => in_array($booking->status, [1,2, 3]) && $booking->type === 'exit_port' && $booking->bookingType === 'booking' && $booking->is_approve === true)->count() }}
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Middle Close Button -->
                                                        <div class="text-center mt-4">
                                                            <button type="button" class="btn btn-danger px-4 py-2 rounded-pill" data-bs-dismiss="modal">
                                                                <i class="fas fa-times me-2"></i> Close
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Pick Up & Drop Modal -->

                                        <!-- Drop Off Modal -->
                                        <div class="modal fade" id="pickupListModal{{ $tour->tour_id }}" tabindex="-1" aria-hidden="true"
                                            data-bs-backdrop="static" data-bs-keyboard="false">
                                            <div class="modal-dialog modal-dialog-centered" style="max-width: 90%;">
                                                <div class="modal-content shadow-lg rounded">
                                                    <!-- Modal Header -->
                                                    {{-- <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title">
                                                            <i class="fas fa-shuttle-van me-2"></i> Drop Off Details - <strong>{{ $tour->destination }}</strong>
                                                        </h5>
                                                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div> --}}

                                                    <div class="modal-header bg-primary text-white d-flex align-items-center justify-content-start" style="padding: 15px; border-radius: 8px;">
                                                        <h5 class="modal-title d-flex align-items-center" style="margin: 0; font-weight: bold; color: white;">
                                                            <i class="fas fa-sign-in-alt me-2" style="color: #d7b52e; font-size: 1.4rem; margin-right:5px;"></i>
                                                            <span style="color: white;">
                                                                Pick-Up Details for Tour: <strong>{{ $tour->destination }}</strong>
                                                            </span>                                                            
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                                                    </div>


                                                    <!-- Modal Body -->
                                                    <div class="modal-body">
                                                        @php
                                                            // Separate Pick Up and Drop Off Data
                                                            $pickUpList = $tour->booking->filter(fn($booking) => in_array($booking->status, [1,2, 3]) && $booking->type === 'entry_port' && $booking->bookingType === 'booking');
                                                    
                                                            // Ensure data assignment
                                                            $tourPickUps = $entrypickups[$tour->tour_id] ?? [];
                                                        @endphp
                                                    
                                                        @if(!empty($tourPickUps))
                                                            {{-- <h4 class="text-primary">Entry Pickups</h4> --}}
                                                            <div class="table-responsive">
                                                                <table id="entryPickupTable_{{ $tour->tour_id }}" class="table table-bordered table-striped align-middle datatables-basic">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th>Vehicle Name</th>
                                                                            <th>Booking Date</th>
                                                                            <th>Pick Up Date</th>
                                                                            <th>Pax</th>
                                                                            <th>Pick Up Location</th>
                                                                            <th>Drop Off Location</th>
                                                                            <th>Entry Time</th>
                                                                            <th>City</th>
                                                                            <th>Country</th>
                                                                            <th>Details</th>
                                                                            <th>Price</th>
                                                                            <th>Status</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($tourPickUps as $order)
                                                                            @foreach ($order['entrypickup_details'] as $pickup)
                                                                                <tr>
                                                                                    <td>
                                                                                        <div class="p-2 rounded shadow-sm text-white" style="background: #d4edda; border-radius: 10px;">
                                                                                            <strong class="text-dark">{{ $pickup['vehicles_name'] ?? 'N/A' }}</strong>
                                    </div>
                                </td>
                                                                                    <td>{{ isset($pickup['bookingDate']) && $pickup['bookingDate'] ? \App\Helpers\CommonHelper::DateFormatAdmin($pickup['bookingDate']) : 'N/A' }}</td>
                                                                                    <td>{{ isset($pickup['pickupdate']) && $pickup['pickupdate'] ? \App\Helpers\CommonHelper::DateFormatAdmin($pickup['pickupdate']) : 'N/A' }}</td>
                                                                                    <td>{{ ($pickup['adults'] ?? 0) + ($pickup['children'] ?? 0) }}</td>
                                                                                    <td class="text-wrap text-break" style="min-width: 95px;">{{ $pickup['entrypickup'] ?? 'N/A' }}</td>
                                                                                    <td class="text-wrap text-break" style="min-width: 95px;">{{ $pickup['entrydropoff'] ?? 'N/A' }}</td>
                                                                                    <td>{{ $pickup['entrytime'] ?? 'N/A' }}</td>
                                                                                    <td>{{ $pickup['city'] ?? 'N/A' }}</td>
                                                                                    <td>{{ $pickup['country'] ?? 'N/A' }}</td>
                                                                                    <td class="text-wrap text-break" style="min-width: 170px;">
                                                                                        <strong>Name:</strong> {{ $pickup['fullName'] ?? 'N/A' }} <br>
                                                                                        <strong>Email:</strong> {{ $pickup['email'] ?? 'N/A' }} <br>
                                                                                        <strong>Phone:</strong> {{ $pickup['countryCode'] ?? '' }} {{ $pickup['phone'] ?? 'N/A' }} <br>
                                                                                        <strong>Address:</strong> {{ $pickup['address1'] ?? 'N/A' }}, {{ $pickup['state'] ?? 'N/A' }} - {{ $pickup['zip'] ?? 'N/A' }}
                                                                                    </td>
                                                                                    <td>{{ $pickup['totalPrice'] ?? 'N/A' }}</td>
                                                                                    <td>
                                                                                        @if($order['status'] == 1)
                                                                                            <span class="hotel-status hotel-approved">Approved</span>
                                                                                        @elseif($order['status'] == 3)
                                                                                            <span class="hotel-status hotel-declined">Declined</span>
                                                                                        @endif
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            @else
                                                            <div class="custom-warning text-center">
                                                                <i class="fas fa-info-circle text-warning"></i> No Pick-Up bookings found for this tour.
                                                            </div>                                                            
                                                        @endif
                                                    
                                                        {{-- @if(empty($tourPickUps) && empty($tourDropOffs)) --}}
                                                           
                                                        {{-- @endif --}}
                                                    </div>
                                                                                                    
                                                    <!-- Modal Footer -->
                                                    <div class="modal-header bg-light text-white d-flex align-items-center justify-content-end" style="padding: 15px; border-radius: 8px;">
                                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                             Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Drop Off Modal -->

                                        <!-- Drop Off Modal -->
                                        <div class="modal fade" id="pickupDropListModal{{ $tour->tour_id }}" tabindex="-1" aria-hidden="true"
                                            data-bs-backdrop="static" data-bs-keyboard="false">
                                            <div class="modal-dialog modal-dialog-centered" style="max-width: 90%;">
                                                <div class="modal-content shadow-lg rounded">
                                                    <!-- Modal Header -->
                                                    {{-- <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title">
                                                            <i class="fas fa-shuttle-van me-2"></i> Drop Off Details - <strong>{{ $tour->destination }}</strong>
                                                        </h5>
                                                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div> --}}

                                                    <div class="modal-header bg-primary text-white d-flex align-items-center justify-content-start" style="padding: 15px; border-radius: 8px;">
                                                        <h5 class="modal-title d-flex align-items-center" style="margin: 0; font-weight: bold; color: white;">
                                                            <i class="fas fa-sign-out-alt me-2" style="color: #FFD700; margin-right: 5px;"></i> 
                                                            <span style="color: white;">Drop-Off Details for Tour: <strong>{{ $tour->destination }}</strong></span>
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                                                    </div>

                                                    <!-- Modal Body -->
                                                    <div class="modal-body">
                                                        @php
                                                            // Separate Pick Up and Drop Off Data
                                                            $dropOffList = $tour->booking->filter(fn($booking) => in_array($booking->status, [1,2, 3]) && $booking->type === 'exit_port' && $booking->bookingType === 'booking');
                                                    
                                                            // Ensure data assignment
                                                            $tourDropOffs = $exitdropoffs[$tour->tour_id] ?? [];
                                                        @endphp
                                                    
                                                        
                                                        @if(!empty($tourDropOffs))
                                                            {{-- <h4 class="text-danger mt-4">Exit Pickups</h4> --}}
                                                            <div class="table-responsive">
                                                                <table id="exitPickupTable_{{ $tour->tour_id }}" class="table table-bordered table-striped align-middle datatables-basic">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th>Vehicle Name</th>
                                                                            <th>Booking Date</th>
                                                                            <th>Pick Up Date</th>
                                                                            <th>Pax</th>
                                                                            <th>Pick Up Location</th>
                                                                            <th>Drop Off Location</th>
                                                                            <th>Entry Time</th>
                                                                            <th>City</th>
                                                                            <th>Country</th>
                                                                            <th>Details</th>
                                                                            <th>Price</th>
                                                                            <th>Status</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($tourDropOffs as $order)
                                                                            @foreach ($order['exitdropoff_details'] as $dropoff)
                                                                                <tr>
                                                                                    <td>
                                                                                        <div class="p-2 rounded shadow-sm text-white" style="background: #d4edda; border-radius: 10px;">
                                                                                            <strong class="text-dark">{{ $dropoff['vehicles_name'] ?? 'N/A' }}</strong>
                                                                                        </div>
                                                                                    </td>
                                                                                    <td>{{ isset($dropoff['bookingDate']) && $dropoff['bookingDate'] ? \App\Helpers\CommonHelper::DateFormatAdmin($dropoff['bookingDate']) : 'N/A' }}</td>
                                                                                    <td>{{ isset($dropoff['exitpickupdate']) && $dropoff['exitpickupdate'] ? \App\Helpers\CommonHelper::DateFormatAdmin($dropoff['exitpickupdate']) : 'N/A' }}</td>
                                                                                    <td>{{ ($dropoff['adults'] ?? 0) + ($dropoff['children'] ?? 0) }}</td>
                                                                                    <td class="text-wrap text-break" style="min-width: 95px;">{{ $dropoff['exitpickup'] ?? 'N/A' }}</td>
                                                                                    <td class="text-wrap text-break" style="min-width: 95px;">{{ $dropoff['exitdropoff'] ?? 'N/A' }}</td>
                                                                                    <td>{{ $dropoff['entrytime'] ?? 'N/A' }}</td>
                                                                                    <td>{{ $dropoff['city'] ?? 'N/A' }}</td>
                                                                                    <td>{{ $dropoff['country'] ?? 'N/A' }}</td>
                                                                                    <td class="text-wrap text-break" style="min-width: 170px;">
                                                                                        <strong>Name:</strong> {{ $dropoff['fullName'] ?? 'N/A' }} <br>
                                                                                        <strong>Email:</strong> {{ $dropoff['email'] ?? 'N/A' }} <br>
                                                                                        <strong>Phone:</strong> {{ $dropoff['countryCode'] ?? '' }} {{ $dropoff['phone'] ?? 'N/A' }} <br>
                                                                                        <strong>Address:</strong> {{ $dropoff['address1'] ?? 'N/A' }}, {{ $dropoff['state'] ?? 'N/A' }} - {{ $dropoff['zip'] ?? 'N/A' }}
                                                                                    </td>
                                                                                    <td>{{ $dropoff['totalPrice'] ?? 'N/A' }}</td>
                                                                                    <td>
                                                                                        @if($order['status'] == 1)
                                                                                            <span class="hotel-status hotel-approved">Approved</span>
                                                                                        @elseif($order['status'] == 3)
                                                                                            <span class="hotel-status hotel-declined">Declined</span>
                                                                                        @endif
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                    @else
                                                        <div class="custom-warning text-center">
                                                            <i class="fas fa-info-circle text-warning"></i> No drop-off bookings found for this tour.
                                                        </div>
                                                        
                                    @endif
                                                    
                                                        
                                                    </div>
                                                                                                    
                                                    <!-- Modal Footer -->
                                                    <div class="modal-header bg-light text-white d-flex align-items-center justify-content-end" style="padding: 15px; border-radius: 8px;">
                                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                             Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Drop Off Modal -->

                                </td>
                                    
                                    @php
                                    $tourTotalPrice = 0;
                                    foreach ($tour->booking as $booking) {
                                        if (in_array($booking->status, [1,2, 3])) { // Only count approved or declined bookings
                                            $data = is_string($booking->data) ? json_decode($booking->data, true) : $booking->data;
                                            if (is_array($data)) {
                                                foreach ($data as $item) {
                                                    if (isset($item['totalPrice'])) {
                                                        $tourTotalPrice += (float)$item['totalPrice'];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                @endphp
                                 @php
                                 $enquiry = \App\Models\Enquiry::where('tour_id', $tour->tour_id)->where('status', 2)->first();
                                 $discountAmount = $enquiry ? ($enquiry->actual_amount - $enquiry->amount) : 0;
                                 $finalAmount = ceil($tourTotalPrice) - $discountAmount;
                                 @endphp
                                  @php
                                  $paymentData = is_string($tour->payment_details) ? json_decode($tour->payment_details, true) : $tour->payment_details;
                                  $totalPaid = 0;
                                  if (is_array($paymentData) && !empty($paymentData)) {
                                      foreach ($paymentData as $payment) {
                                          // Only count payments that are verified (status 1)
                                          // Exclude declined payments (status 2) and pending payments (status 0)
                                          if (isset($payment['status']) && $payment['status'] == 1) {
                                              $totalPaid += isset($payment['amount']) ? (float)$payment['amount'] : 0;
                                          }
                                      }
                                  }
                                  $remainingAmount = $finalAmount - $totalPaid;
                              @endphp
                              @php
                              $paymentData = is_string($tour->payment_details) ? json_decode($tour->payment_details, true) : $tour->payment_details;
                              $totalPaid = 0;
                              if (is_array($paymentData) && !empty($paymentData)) {
                                  foreach ($paymentData as $payment) {
                                      // Only count payments that are verified (status 1)
                                      // Exclude declined payments (status 2) and pending payments (status 0)
                                      if (isset($payment['status']) && $payment['status'] == 1) {
                                          $totalPaid += isset($payment['amount']) ? (float)$payment['amount'] : 0;
                                      }
                                  }
                              }
                              $remainingAmount = $finalAmount - $totalPaid;
                          @endphp
                          @php
                          $paymentData = is_string($tour->payment_details) ? json_decode($tour->payment_details, true) : $tour->payment_details;
                          $totalPaid = 0;
                          if (is_array($paymentData) && !empty($paymentData)) {
                              foreach ($paymentData as $payment) {
                                  // Only count payments that are verified (status 1)
                                  // Exclude declined payments (status 2) and pending payments (status 0)
                                  if (isset($payment['status']) && $payment['status'] == 1) {
                                      $totalPaid += isset($payment['amount']) ? (float)$payment['amount'] : 0;
                                  }
                              }
                          }
                          $remainingAmount = $finalAmount - $totalPaid;
                      @endphp
                      @php
                      $paymentData = is_string($tour->payment_details) ? json_decode($tour->payment_details, true) : $tour->payment_details;
                      $hasPendingPayments = false;
                      if (is_array($paymentData) && !empty($paymentData)) {
                          foreach ($paymentData as $payment) {
                              if (isset($payment['status']) && $payment['status'] == 0) {
                                  $hasPendingPayments = true;
                                  break;
                              }
                          }
                      }
                  @endphp

                                <td>
                                                                    

                                    @if(empty($paymentData))
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-exclamation-circle me-1"></i> Payment Not Started
                                        </span>
                                    @elseif($remainingAmount > 0)
                                        <span class="badge bg-info text-white">
                                            <i class="fas fa-money-bill-wave me-1"></i> Partial Payment ({{ number_format($totalPaid, 2) }})
                                        </span>
                                    @else
                                        <span class="badge bg-success text-white">
                                            <i class="fas fa-check-circle me-1"></i> Fully Paid
                                        </span>
                                    @endif
                                </td>

                                <td>{{ $tour->created_at->format('d M Y, h:i A') }}</td>
                                
                                <td>{{ number_format(ceil($tourTotalPrice), 2) }}</td>
                               
                                <td>{{ number_format($discountAmount, 2) }}</td>
                                <td>{{ number_format($finalAmount, 2) }}</td>
                                <td>
                                    {{ number_format($remainingAmount, 2) }}
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-2">
                                            
                                            
                                            @if(auth()->user()->role_id == 36 || auth()->user()->role_id == 126 || auth()->user()->role_id == 127 || auth()->user()->role_id == 124 || auth()->user()->role_id == 125)
                                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#showPaymentModal{{ $tour->tour_id }}">
                                                    <i class="fas fa-history me-1"></i> Payment History
                                            </button>
                                            @else
                                                <!-- For other roles -->
                                                @if(!empty($paymentData))
                                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#showPaymentModal{{ $tour->tour_id }}">
                                                        <i class="fas fa-history me-1"></i> Payment History
                                                    </button>
                                                @endif
    
                                                {{-- @if(auth()->user()->role_id = 1 || auth()->user()->role_id = 11 || auth()->user()->role_id = 33 || auth()->user()->role_id = 37 || auth()->user()->role_id = 38) --}}
                                                @if(in_array(auth()->user()->role_id, [1, 2, 3, 4, 10, 11, 12, 24, 28, 33, 37, 38]))
                                                
                                                    @if($remainingAmount > 0 && !$hasPendingPayments)
                                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal{{ $tour->tour_id }}" onclick="checkPendingPayments({{ $tour->tour_id }})">
                                                            <i class="fas fa-plus-circle me-1"></i> Add Payment
                                                        </button>
                                                    @endif
    
                                                    <!-- @if(!empty($paymentData) && $tour->is_approve == 0)
                                                    <button type="button" 
                                                        class="btn btn-sm btn-success" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#approveBookingModal{{ $tour->tour_id }}" 
                                                        onclick="showLoader(this)">
                                                        <i class="fas fa-check-circle me-1"></i> Start Servicing
                                                    </button>
                                                    @elseif($tour->is_approve == 1)
                                                    <span class="btn btn-sm btn-success">
                                                        <i class="fas fa-check-circle me-1"></i> Servicing Started
                                                    </span>
                                                    @endif -->
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Links -->
            @if($tours->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4 px-3">
                    <div class="dataTables_info">
                        Showing {{ $tours->firstItem() }} to {{ $tours->lastItem() }} of {{ $tours->total() }} entries
                    </div>
                    <div class="dataTables_paginate">
                        {{ $tours->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @elseif($tours->count() > 0)
                <div class="d-flex justify-content-start mt-4 px-3">
                    <div class="dataTables_info">
                        Showing {{ $tours->count() }} {{ $tours->count() === 1 ? 'entry' : 'entries' }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Payment Processing Overlay -->
<div id="paymentProcessingOverlay" class="payment-processing-overlay">
    <div class="payment-spinner"></div>
    <div style="text-align: center; font-size: 1.2rem; font-weight: 500;">
        Processing Payment...
    </div>
</div>

<!-- Payment Processing Overlay -->
<div id="paymentProcessingOverlay" class="payment-processing-overlay">
    <div class="payment-spinner"></div>
    <div style="text-align: center; font-size: 1.2rem; font-weight: 500;">
        Processing Payment...
    </div>
</div>

<!-- Move modals outside the table -->
@foreach($tours as $tour)
    <!-- Payment History Modal -->
    <div class="modal fade" id="showPaymentModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="showPaymentModalLabel{{ $tour->tour_id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg rounded">
                <div class="modal-header bg-primary text-white d-flex align-items-center justify-content-start" style="padding: 15px; border-radius: 8px;">
                    <h5 class="modal-title d-flex align-items-center" id="showPaymentModalLabel{{ $tour->tour_id }}" style="margin: 0; font-weight: bold; color: white;">
                        <i class="fas fa-history me-2" style="color: #38ef7d; font-size: 1.4rem;"></i> 
                        <span style="color: white;">Payment History for Tour #{{ $tour->tour_id }}</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                </div>
                <div class="modal-body p-4">
                    @php
                        $paymentData = is_string($tour->payment_details) ? json_decode($tour->payment_details, true) : $tour->payment_details;
                    @endphp
                    
                    @if(!empty($paymentData))
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">Payment Date</th>
                                        <th class="text-center">Record Date</th>
                                        <th class="text-center">Amount (SGD)</th>
                                        <th class="text-center">Original Amount</th>
                                        <th class="text-center">Currency</th>
                                        <th class="text-center">Exchange Rate</th>
                                        <th class="text-center">Amount (SGD)</th>
                                        <th class="text-center">Original Amount</th>
                                        <th class="text-center">Currency</th>
                                        <th class="text-center">Exchange Rate</th>
                                        <th class="text-center">Payment Mode</th>
                                        <th class="text-center">Transaction ID</th>
                                        <th class="text-center">Remarks</th>
                                        <th class="text-center">Status</th>
                                        @if(auth()->user()->role_id == 36 || auth()->user()->role_id == 126 || auth()->user()->role_id == 127)
                                            <th class="text-center">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paymentData as $index => $payment)
                                        <tr>
                                            <td class="text-center">{{ \App\Helpers\CommonHelper::DateFormatAdmin($payment['payment_date'] ?? '') }}</td>
                                            <td class="text-center">{{ \App\Helpers\CommonHelper::DateFormatAdmin($payment['date'] ?? '') }}</td>
                                            <td class="text-center">{{ \App\Helpers\CommonHelper::DateFormatAdmin($payment['date'] ?? '') }}</td>
                                            <td class="text-center">{{ number_format($payment['amount'] ?? 0, 2) }}</td>
                                            <td class="text-center">
                                                @if(isset($payment['original_amount']) && isset($payment['currency']) && $payment['currency'] !== 'SGD')
                                                    {{ number_format($payment['original_amount'], 2) }}
                                                @else
                                                    {{ number_format($payment['amount'] ?? 0, 2) }}
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info">{{ $payment['currency'] ?? 'SGD' }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if(isset($payment['exchange_rate']) && $payment['currency'] !== 'SGD')
                                                    1 SGD = {{ number_format($payment['exchange_rate'], 4) }} {{ $payment['currency'] }}
                                                @else
                                                    1.0000
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(isset($payment['original_amount']) && isset($payment['currency']) && $payment['currency'] !== 'SGD')
                                                    {{ number_format($payment['original_amount'], 2) }}
                                                @else
                                                    {{ number_format($payment['amount'] ?? 0, 2) }}
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info">{{ $payment['currency'] ?? 'SGD' }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if(isset($payment['exchange_rate']) && $payment['currency'] !== 'SGD')
                                                    1 SGD = {{ number_format($payment['exchange_rate'], 4) }} {{ $payment['currency'] }}
                                                @else
                                                    1.0000
                                                @endif
                                            </td>
                                            <td class="text-center">{{ ucfirst($payment['payment_type'] ?? 'N/A') }}</td>
                                            <td class="text-center">{{ $payment['transaction_id'] ?? 'N/A' }}</td>
                                            <td class="text-center">{{ $payment['remarks'] ?? 'N/A' }}</td>
                                            <td class="text-center">
                                                @if(isset($payment['status']))
                                                    @if($payment['status'] == 1)
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check-circle me-1"></i> Verified
                                                        </span>
                                                    @elseif($payment['status'] == 2)
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-times-circle me-1"></i> Declined
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="fas fa-clock me-1"></i> Pending
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-clock me-1"></i> Pending
                                                    </span>
                                                @endif
                                            </td>
                                            @if(auth()->user()->role_id == 36 || auth()->user()->role_id == 126 || auth()->user()->role_id == 127)
                                                <td class="text-center">
                                                    @if(!isset($payment['status']) || $payment['status'] == 0)
                                                        <div class="d-flex justify-content-center gap-2">
                                                            <button type="button" class="btn btn-sm btn-success" onclick="verifyPayment({{ $tour->tour_id }}, {{ $index }})">
                                                                <i class="fas fa-check-circle me-1"></i> Verify
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="declinePayment({{ $tour->tour_id }}, {{ $index }})">
                                                                <i class="fas fa-times-circle me-1"></i> Decline
                                                            </button>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">No action needed</span>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle text-warning fa-2x mb-3"></i>
                            <p class="mb-0">No payment history found for this tour.</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer bg-light d-flex justify-content-end" style="padding: 15px; border-radius: 8px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="addPaymentModalLabel{{ $tour->tour_id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg rounded">
            <div class="modal-header bg-primary text-white d-flex align-items-center justify-content-start" style="padding: 15px; border-radius: 8px;">
                <h5 class="modal-title d-flex align-items-center" id="addPaymentModalLabel{{ $tour->tour_id }}" style="margin: 0; font-weight: bold; color: white;">
                    <i class="fas fa-money-bill-wave me-2" style="color: #38ef7d; font-size: 1.4rem;"></i> 
                    <span style="color: white;">Add Payment for Tour #{{ $tour->tour_id }}</span>
                </h5>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
            </div>
            <div class="modal-body p-4">
                <form id="paymentForm{{ $tour->tour_id }}" action="{{ route('tour.add-payment', $tour->tour_id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="tour_id" value="{{ $tour->tour_id }}">
                    
                    <div class="mb-4">
                        <label for="amount{{ $tour->tour_id }}" class="form-label fw-bold">
                                <i class="fas fa-dollar-sign text-success me-2"></i>Due Amount (SGD)
                                <i class="fas fa-dollar-sign text-success me-2"></i>Due Amount (SGD)
                        </label>
                                    
                            @php
                                $tourTotalPrice = 0;
                                foreach ($tour->booking as $booking) {
                                    if (in_array($booking->status, [1, 2,3])) { // Only count approved or declined bookings
                                        $data = is_string($booking->data) ? json_decode($booking->data, true) : $booking->data;
                                        if (is_array($data)) {
                                            foreach ($data as $item) {
                                                if (isset($item['totalPrice'])) {
                                                    $tourTotalPrice += (float)$item['totalPrice'];
                                                }
                                            }
                                        }
                                    }
                                }
                                $enquiry = \App\Models\Enquiry::where('tour_id', $tour->tour_id)->where('status', 2)->first();
                                $discountAmount = $enquiry ? ($enquiry->actual_amount - $enquiry->amount) : 0;
                                $finalAmount = ceil($tourTotalPrice) - $discountAmount;
                                $paymentData = is_string($tour->payment_details) ? json_decode($tour->payment_details, true) : $tour->payment_details;
                                $totalPaid = 0;
                                if (is_array($paymentData) && !empty($paymentData)) {
                                    foreach ($paymentData as $payment) {
                                        // Only count payments that are verified (status 1)
                                        // Exclude declined payments (status 2) and pending payments (status 0)
                                        if (isset($payment['status']) && $payment['status'] == 1) {
                                            $totalPaid += isset($payment['amount']) ? (float)$payment['amount'] : 0;
                                        }
                                    }
                                }
                                $remainingAmount = $finalAmount - $totalPaid;
                            @endphp
                        <div class="input-group">
                            <span class="input-group-text bg-light">SGD</span>
                                <input type="number" 
                                    class="form-control form-control-lg" 
                                    id="amount{{ $tour->tour_id }}" 
                                    name="amount" 
                                    value="{{ $remainingAmount }}" 
                                    required
                                    min="0" 
                                    max="{{ $remainingAmount }}" 
                                    step="1" 
                                    oninput="validateAmount(this, {{ $remainingAmount }})"
                                    onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                                    readonly>
                        </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <span class="text-primary">Total Amount: {{ number_format($finalAmount, 2) }}</span> | 
                                    <span class="text-success">Paid Amount: {{ number_format($totalPaid, 2) }}</span> | 
                                    <span class="text-danger">Remaining Amount: {{ number_format($remainingAmount, 2) }}</span>
                                </small>
                            </div>
                        </div>
                        
                        <!-- Currency Selection -->
                        <div class="mb-4">
                            <label for="currency{{ $tour->tour_id }}" class="form-label fw-bold">
                                <i class="fas fa-coins text-warning me-2"></i>Select Currency
                            </label>
                            <select class="form-select form-control-lg" 
                                id="currency{{ $tour->tour_id }}" 
                                name="currency" 
                                onchange="updatePaymentAmountEnhanced({{ $tour->tour_id }}, this.value)"
                                required>
                                <option value="">Select Currency</option>
                                @foreach(\App\Models\Setting::getCurrencyCodes() as $currency)
                                    <option value="{{ $currency }}" {{ $currency == 'SGD' ? 'selected' : '' }}>
                                        {{ $currency }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Exchange Rate (Editable) -->
                        <div class="mb-4" id="exchangeRateSection{{ $tour->tour_id }}" style="display: none;">
                            <label for="exchange_rate{{ $tour->tour_id }}" class="form-label fw-bold">
                                <i class="fas fa-calculator text-primary me-2"></i>Exchange Rate
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">1 SGD =</span>
                                <input type="number" 
                                    class="form-control form-control-lg" 
                                    id="exchange_rate{{ $tour->tour_id }}" 
                                    name="exchange_rate" 
                                    value="1.00" 
                                    min="0" 
                                    step="0.0001"
                                    oninput="recalculateFromExchangeRate({{ $tour->tour_id }})">
                                <span class="input-group-text bg-light" id="exchangeRateCurrency{{ $tour->tour_id }}">SGD</span>
                            </div>
                            <div class="mt-1">
                                <small class="text-success" id="exchangeRateSource{{ $tour->tour_id }}">
                                    <i class="fas fa-globe me-1"></i>
                                    Rate Source: <span id="rateSourceText{{ $tour->tour_id }}">API</span>
                                </small>
                            </div>
                        </div>
                        
                        <!-- Payment Amount in Selected Currency -->
                        <div class="mb-4">
                            <label for="payment_amount{{ $tour->tour_id }}" class="form-label fw-bold">
                                <i class="fas fa-money-bill-wave text-success me-2"></i>Payment Amount
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light" id="currencySymbol{{ $tour->tour_id }}">SGD</span>
                                <input type="number" 
                                    class="form-control form-control-lg" 
                                    id="payment_amount{{ $tour->tour_id }}" 
                                    name="payment_amount" 
                                    value="{{ $remainingAmount }}" 
                                    required
                                    min="0" 
                                    step="0.01"
                                    oninput="validatePaymentAmountInput({{ $tour->tour_id }})"
                                    onblur="validatePaymentAmountInput({{ $tour->tour_id }})">
                            </div>
                            <div class="mt-2" id="conversionInfoContainer{{ $tour->tour_id }}" style="display: none;">
                                <small class="text-info" id="conversionInfo{{ $tour->tour_id }}">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Amount in SGD: {{ number_format($remainingAmount, 2) }}
                                </small>
                            </div>
                            <div class="mt-1">
                                <small class="text-danger" id="paymentValidationError{{ $tour->tour_id }}" style="display: none;">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    <span id="validationMessage{{ $tour->tour_id }}"></span>
                                </small>
                            </div>
                        </div>

                        
                        <!-- Currency Selection -->
                        <div class="mb-4">
                            <label for="currency{{ $tour->tour_id }}" class="form-label fw-bold">
                                <i class="fas fa-coins text-warning me-2"></i>Select Currency
                            </label>
                            <select class="form-select form-control-lg" 
                                id="currency{{ $tour->tour_id }}" 
                                name="currency" 
                                onchange="updatePaymentAmountEnhanced({{ $tour->tour_id }}, this.value)"
                                required>
                                <option value="">Select Currency</option>
                                @foreach(\App\Models\Setting::getCurrencyCodes() as $currency)
                                    <option value="{{ $currency }}" {{ $currency == 'SGD' ? 'selected' : '' }}>
                                        {{ $currency }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Exchange Rate (Editable) -->
                        <div class="mb-4" id="exchangeRateSection{{ $tour->tour_id }}" style="display: none;">
                            <label for="exchange_rate{{ $tour->tour_id }}" class="form-label fw-bold">
                                <i class="fas fa-calculator text-primary me-2"></i>Exchange Rate
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">1 SGD =</span>
                                <input type="number" 
                                    class="form-control form-control-lg" 
                                    id="exchange_rate{{ $tour->tour_id }}" 
                                    name="exchange_rate" 
                                    value="1.00" 
                                    min="0" 
                                    step="0.0001"
                                    oninput="recalculateFromExchangeRate({{ $tour->tour_id }})">
                                <span class="input-group-text bg-light" id="exchangeRateCurrency{{ $tour->tour_id }}">SGD</span>
                            </div>
                            <div class="mt-1">
                                <small class="text-success" id="exchangeRateSource{{ $tour->tour_id }}">
                                    <i class="fas fa-globe me-1"></i>
                                    Rate Source: <span id="rateSourceText{{ $tour->tour_id }}">API</span>
                                </small>
                            </div>
                        </div>
                        
                        <!-- Payment Amount in Selected Currency -->
                        <div class="mb-4">
                            <label for="payment_amount{{ $tour->tour_id }}" class="form-label fw-bold">
                                <i class="fas fa-money-bill-wave text-success me-2"></i>Payment Amount
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light" id="currencySymbol{{ $tour->tour_id }}">SGD</span>
                                <input type="number" 
                                    class="form-control form-control-lg" 
                                    id="payment_amount{{ $tour->tour_id }}" 
                                    name="payment_amount" 
                                    value="{{ $remainingAmount }}" 
                                    required
                                    min="0" 
                                    step="0.01"
                                    oninput="validatePaymentAmountInput({{ $tour->tour_id }})"
                                    onblur="validatePaymentAmountInput({{ $tour->tour_id }})">
                            </div>
                            <div class="mt-2" id="conversionInfoContainer{{ $tour->tour_id }}" style="display: none;">
                                <small class="text-info" id="conversionInfo{{ $tour->tour_id }}">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Amount in SGD: {{ number_format($remainingAmount, 2) }}
                                </small>
                            </div>
                            <div class="mt-1">
                                <small class="text-danger" id="paymentValidationError{{ $tour->tour_id }}" style="display: none;">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    <span id="validationMessage{{ $tour->tour_id }}"></span>
                                </small>
                            </div>
                        </div>

                        
                        <div class="mb-4">
                            <label for="payment_date{{ $tour->tour_id }}" class="form-label fw-bold">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>Payment Date
                            </label>
                            <input type="date" 
                                class="form-control form-control-lg" 
                                id="payment_date{{ $tour->tour_id }}" 
                                name="payment_date" 
                                value="{{ date('Y-m-d') }}"
                                required>
                        </div>

                        <div class="mb-4">
                            <label for="payment_type{{ $tour->tour_id }}" class="form-label fw-bold">
                                <i class="fas fa-credit-card text-primary me-2"></i>Payment Type
                            </label>
                            <select class="form-select form-control-lg" 
                                id="payment_type{{ $tour->tour_id }}" 
                                name="payment_type" 
                                required>
                                <option value="">Select Payment Type</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="cheque">Cheque</option>
                                <option value="online">Bank Transfer</option>
                            </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="transaction_id{{ $tour->tour_id }}" class="form-label fw-bold">
                            <i class="fas fa-hashtag text-primary me-2"></i>Transaction ID (Optional)
                        </label>
                            <input type="text" class="form-control form-control-lg" id="transaction_id{{ $tour->tour_id }}" name="transaction_id">
                    </div>
                    
                    <div class="mb-4">
                        <label for="remarks{{ $tour->tour_id }}" class="form-label fw-bold">
                            <i class="fas fa-comment-alt text-warning me-2"></i>Remarks (Optional)
                        </label>
                        <textarea class="form-control" id="remarks{{ $tour->tour_id }}" name="remarks" rows="3" placeholder="Enter payment remarks here..."></textarea>
                    </div>                                       
                </form>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between" style="padding: 15px; border-radius: 8px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-success" id="savePaymentBtn{{ $tour->tour_id }}" onclick="submitPaymentForm({{ $tour->tour_id }})">
                        <i class="fas fa-save me-2"></i>Verify Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Booking Modal -->
<div class="modal fade" id="approveBookingModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="approveBookingModalLabel{{ $tour->tour_id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveBookingModalLabel{{ $tour->tour_id }}">
                    <i class="fas fa-check-circle me-2"></i> Approve Booking
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="approveBookingForm{{ $tour->tour_id }}" action="{{ route('tour.approve-booking', $tour->tour_id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="tour_id" value="{{ $tour->tour_id }}">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Are you want to approve this booking?
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tour Details</label>
                        <div class="border rounded p-3">
                            <p class="mb-1"><strong>Tour ID:</strong> {{ $tour->tour_id }}</p>
                            <p class="mb-1"><strong>Destination:</strong> {{ $tour->destination }}</p>
                            <p class="mb-0"><strong>Total Amount:</strong> {{ number_format($finalAmount, 2) }}</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Status</label>
                        <div class="border rounded p-3">
                            <p class="mb-1"><strong>Total Paid:</strong> {{ number_format($totalPaid, 2) }}</p>
                            <p class="mb-1"><strong>Remaining Amount:</strong> {{ number_format($remainingAmount, 2) }}</p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="approveBookingForm{{ $tour->tour_id }}" class="btn btn-success" onclick="showApproveLoader({{ $tour->tour_id }})">
                    <i class="fas fa-check-circle me-1"></i> Approve Booking
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add the loader HTML -->
<div class="approve-booking-loader" id="approveBookingLoader{{ $tour->tour_id }}">
    <div class="approve-loader-content">
        <div class="approve-spinner">
            <div class="approve-spinner-circle"></div>
            <div class="approve-spinner-circle"></div>
            <div class="approve-spinner-circle"></div>
        </div>
        <div class="approve-loader-text">Processing Booking Approval</div>
        <div class="approve-loader-progress">
            <div class="approve-loader-bar"></div>
        </div>
    </div>
</div>
</div>


<!-- Add this modal for role_id 36 -->
@if(auth()->user()->role_id == 36)
    <div class="modal fade" id="verifyTourModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="verifyTourModalLabel{{ $tour->tour_id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg rounded">
                <div class="modal-header bg-primary text-white d-flex align-items-center justify-content-start" style="padding: 15px; border-radius: 8px;">
                    <h5 class="modal-title d-flex align-items-center" id="verifyTourModalLabel{{ $tour->tour_id }}" style="margin: 0; font-weight: bold; color: white;">
                        <i class="fas fa-history me-2" style="color: #38ef7d; font-size: 1.4rem;"></i> 
                        <span style="color: white;">Tour Verification #{{ $tour->tour_id }}</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Payment History Section -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-money-bill-wave me-2"></i>Payment History
                        </h6>
                        @php
                            $paymentData = is_string($tour->payment_details) ? json_decode($tour->payment_details, true) : $tour->payment_details;
                        @endphp
                        
                        @if(!empty($paymentData))
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">Payment Date</th>
                                            <th class="text-center">Record Date</th>
                                            <th class="text-center">Amount</th>
                                            <th class="text-center">Payment Mode</th>
                                            <th class="text-center">Transaction ID</th>
                                            <th class="text-center">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($paymentData as $payment)
                                            <tr>
                                                <td class="text-center">{{ \App\Helpers\CommonHelper::DateFormatAdmin($payment['payment_date'] ?? '') }}</td>
                                                <td class="text-center">{{ \App\Helpers\CommonHelper::DateFormatAdmin($payment['created_at'] ?? '') }}</td>
                                                <td class="text-center">{{ number_format($payment['amount'] ?? 0, 2) }}</td>
                                                <td class="text-center">{{ ucfirst($payment['payment_type'] ?? 'N/A') }}</td>
                                                <td class="text-center">{{ $payment['transaction_id'] ?? 'N/A' }}</td>
                                                <td class="text-center">{{ $payment['remarks'] ?? 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-info-circle text-warning fa-2x mb-3"></i>
                                <p class="mb-0">No payment history found for this tour.</p>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-end" style="padding: 15px; border-radius: 8px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
@endforeach
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- DataTable JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<!-- DataTables Initialization Script -->
<script>
 $(document).ready(function() {
    // Initialize DataTable with export buttons but disable pagination and length changing
    $('.datatables-basic').DataTable({
        responsive: true,
        paging: false, // Disable DataTables pagination
        lengthChange: false, // Disable length changing
        info: false, // Disable info display ("Showing 1 to 10 of X entries")
        searching: true, // Keep search functionality
        ordering: true, // Keep column sorting
        buttons: [
            'copy',
            'csv',
            'excel',
            'pdf',
            'print' // Enable copy, CSV, Excel, PDF, and Print buttons
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search...",
        },
        drawCallback: function() {
            // Reinitialize Select2 for guide and driver dropdowns after each draw
            $('.guideSelect').select2({
                placeholder: "Select a Guide",
                allowClear: true,
                width: '100%'
            });

            $('.driverSelect').select2({
                placeholder: "Select a Driver",
                allowClear: true,
                width: '100%'
            });
        }
    });

    // Custom export button functionality (for the dropdown)
    $('#exportCopy').on('click', function() {
        $('.datatables-basic').DataTable().button('.buttons-copy').trigger();
    });

    $('#exportCSV').on('click', function() {
        $('.datatables-basic').DataTable().button('.buttons-csv').trigger();
    });

    $('#exportExcel').on('click', function() {
        $('.datatables-basic').DataTable().button('.buttons-excel').trigger();
    });

    $('#exportPDF').on('click', function() {
        $('.datatables-basic').DataTable().button('.buttons-pdf').trigger();
    });

    $('#exportPrint').on('click', function() {
        $('.datatables-basic').DataTable().button('.buttons-print').trigger();
    });
 });
</script>
<!-- End DataTable JS -->

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
    function setDeleteForm(url) {
        document.getElementById('deleteForm').action = url;
    }
</script>

<!-- Toastr JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script>
    toastr.options = {
        "closeButton": true, // Show a close button
        "progressBar": true, // Show progress bar
        "positionClass": "toast-top-right", // Set position
        "timeOut": "1000", // Display for 1 second (fast)
        "extendedTimeOut": "500", // Extra time after user hovers
        "showMethod": "fadeIn", // Animation effect when showing
        "hideMethod": "fadeOut", // Animation effect when hiding
        "showDuration": "300", // Show animation duration
        "hideDuration": "300", // Hide animation duration
        "preventDuplicates": true, // Prevent duplicate messages
        "newestOnTop": true // New messages appear on top
    };
</script>

<!-- For Guide Assign and Guide Remove JS -->
<script>
    $(document).ready(function() {
    $('.guideSelect').select2({
        placeholder: "Select a Guide",
        allowClear: true,
        width: '100%'
    });

    // Detect change event on dynamically added select elements
    $(document).on('change', '.guideSelect', function() {
        var guideId = $(this).val(); // Get selected guide ID
        var tourId = $(this).closest('tr').data('tour-id'); // Assuming each tour is in a <tr>

        //console.log("Selected Guide ID:", guideId, "for Tour ID:", tourId); // Added console log

        if (guideId) {
            updateGuide(guideId, tourId);
            console.log("Selected Guide ID:", guideId, "for Tour ID:", tourId); // Added console log
        } else {
            removeGuide(tourId);
            console.log("Removed Guide ID:", guideId, "for Tour ID:", tourId); // Added console log
        }
    });

    // Function to assign guide
    function updateGuide(guideId, tourId) {
        if (!tourId) {
            toastr.error("Tour ID is missing.");
            return;
        }

        $.ajax({
            url: "{{ route('tour.assign-guide') }}",
            type: "POST",
            data: {
                guide_id: guideId,
                tour_id: tourId,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.status === 'success') {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error("An error occurred while assigning the guide.");
            }
        });
    }

    // Function to remove guide
    function removeGuide(tourId) {
        if (!tourId) {
            toastr.error("Tour ID is missing.");
            return;
        }

        $.ajax({
            url: "{{ route('tour.remove-guide') }}",
            type: "POST",
            data: {
                tour_id: tourId,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.status === 'success') {
                    toastr.success(response.message);

                // Change row background color when guide is removed
                $("tr[data-tour-id='" + tourId + "']").css("background-color", "#ffcccc"); // Light Red

                // Optional: Reset background color after 2 seconds
                setTimeout(function() {
                    $("tr[data-tour-id='" + tourId + "']").css("background-color", "");
                }, 2000);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error("An error occurred while removing the guide.");
            }
        });
    }
  });

</script>

<!-- For Drver Assign and Driver Remove JS -->
<script>
    $(document).ready(function() {
    $('.driverSelect').select2({
        placeholder: "Select a Driver",
        allowClear: true,
        width: '100%'
    });

    // Detect change event on dynamically added select elements
    $(document).on('change', '.driverSelect', function() {
        var driverId = $(this).val(); // Get selected driver ID
        var tourId = $(this).closest('tr').data('tour-id'); // Assuming each tour is in a <tr>

        //console.log("Selected Driver ID:", driverId, "for Tour ID:", tourId); // Added console log

        if (driverId) {
            updateDriver(driverId, tourId);
            console.log("Selected Driver ID:", driverId, "for Tour ID:", tourId); // Added console log
        } else {
            removeDriver(tourId);
            console.log("Removed Driver ID:", driverId, "for Tour ID:", tourId); // Added console log
        }
    });

    // Function to assign driver
    function updateDriver(driverId, tourId) {
        if (!tourId) {
            toastr.error("Tour ID is missing.");
            return;
        }

        $.ajax({
            url: "{{ route('tour.assign-driver') }}",
            type: "POST",
            data: {
                driver_id: driverId,
                tour_id: tourId,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.status === 'success') {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error("An error occurred while assigning the guide.");
            }
        });
    }

    // Function to remove driver
    function removeDriver(tourId) {
        if (!tourId) {
            toastr.error("Tour ID is missing.");
            return;
        }

        $.ajax({
            url: "{{ route('tour.remove-driver') }}",
            type: "POST",
            data: {
                tour_id: tourId,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.status === 'success') {
                    toastr.success(response.message);

                // Change row background color when guide is removed
                $("tr[data-tour-id='" + tourId + "']").css("background-color", "#ffcccc"); // Light Red

                // Optional: Reset background color after 2 seconds
                setTimeout(function() {
                    $("tr[data-tour-id='" + tourId + "']").css("background-color", "");
                }, 2000);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error("An error occurred while removing the guide.");
            }
        });
    }
  });

</script>

<!-- Function to handle payment form submission with loading overlay -->
<script>
 // Function to handle payment form submission with loading overlay
    function submitPaymentForm(tourId) {
        // Get the form
        const form = document.getElementById('paymentForm' + tourId);
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Show the processing overlay
        const overlay = document.getElementById('paymentProcessingOverlay');
        if (overlay) {
        overlay.classList.add('active');
        }
        
        // Submit the form
        form.submit();
        
        // For better UX, add a minimum display time for the loader
        setTimeout(function() {
            if (overlay && overlay.classList.contains('active')) {
                overlay.classList.remove('active');
            }
        }, 3000);
    }

// Add this CSS if not already present
    const style = document.createElement('style');
    style.textContent = `
        .payment-processing-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            color: white;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s, visibility 0.3s;
        }
        
        .payment-processing-overlay.active {
            visibility: visible;
            opacity: 1;
        }
        
        .payment-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);

    // Listen for message from backend and display success message
    $(document).ready(function() {
        // Check if there's a success message in the session
        @if(session('success'))
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000",
                "extendedTimeOut": "1000",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };
            toastr.success("{{ session('success') }}", "Success!");
        @endif
        
        // Customize the success message for payment specifically
        @if(session('success') && str_contains(session('success'), 'Payment'))
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };
            toastr.success("Payment was successfully added to the tour!", "Payment Successful");
        @endif
    });
</script>

<script>
function validateAmount(input, maxAmount) {
    if (parseInt(input.value) > maxAmount) {
        input.value = maxAmount; // Reset to max value
    }
}

// Enhanced form submission validation
function validateBeforeSubmit(tourId) {
    const isValid = validatePaymentAmountInput(tourId);
    if (!isValid) {
        toastr.error('Please correct the payment amount before submitting.');
        return false;
    }
    return true;
}

// Check for pending payments before allowing new payment
function checkPendingPayments(tourId) {
    // This function can be used to check for pending payments
    // For now, it's just a placeholder since the condition is already checked in the PHP code
    console.log('Checking pending payments for tour:', tourId);
    return true;
}

// Enhanced form submission validation
function validateBeforeSubmit(tourId) {
    const isValid = validatePaymentAmountInput(tourId);
    if (!isValid) {
        toastr.error('Please correct the payment amount before submitting.');
        return false;
    }
    return true;
}

// Check for pending payments before allowing new payment
function checkPendingPayments(tourId) {
    // This function can be used to check for pending payments
    // For now, it's just a placeholder since the condition is already checked in the PHP code
    console.log('Checking pending payments for tour:', tourId);
    return true;
}
</script>
<!-- Add this JavaScript for payment verification -->
<script>
    var APP_URL = "{{ config('app.url') }}";
</script>
<script>

    // Define base URL using Laravel's URL helper
    const BASE_URL = "{{ url('/') }}";
    
    function verifyPayment(tourId, paymentIndex) {
        if (confirm('Are you sure you want to verify this payment?')) {
            // Show loading overlay
            const overlay = document.getElementById('paymentProcessingOverlay');
            if (overlay) {
                overlay.classList.add('active');
            }
            
            // Use jQuery AJAX with proper CSRF token handling and absolute URL
            $.ajax({
                url: `${BASE_URL}/tour/${tourId}/verify-payment`,
                type: 'POST',
                data: {
                    payment_index: paymentIndex,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // Hide loading overlay
                    if (overlay) {
                        overlay.classList.remove('active');
                    }
                    
                    if (response.success) {
                        // Show success message
                        toastr.success('Payment verified successfully!');
                        
                        // Reload the page after a short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        // Show error message
                        toastr.error(response.message || 'Failed to verify payment');
                    }
                },
                error: function(xhr, status, error) {
                    // Hide loading overlay
                    if (overlay) {
                        overlay.classList.remove('active');
                    }
                    
                    // Log detailed error information
                    console.error('Error details:', {
                        status: status,
                        error: error,
                        response: xhr.responseText,
                        url: `${BASE_URL}/tour/${tourId}/verify-payment`
                    });
                    
                    // Show appropriate error message
                    let errorMessage = 'An error occurred while verifying the payment';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch (e) {
                        // If response is not JSON, use the raw response text
                        errorMessage = xhr.responseText || errorMessage;
                    }
                    
                    toastr.error(errorMessage);
                }
            });
        }
    }

    function declinePayment(tourId, paymentIndex) {
        if (confirm('Are you sure you want to decline this payment?')) {
            // Show loading overlay
            const overlay = document.getElementById('paymentProcessingOverlay');
            if (overlay) {
                overlay.classList.add('active');
            }
            
            // Use jQuery AJAX with proper CSRF token handling and absolute URL
            $.ajax({
                url: `${BASE_URL}/tour/${tourId}/decline-payment`,
                type: 'POST',
                data: {
                    payment_index: paymentIndex,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // Hide loading overlay
                    if (overlay) {
                        overlay.classList.remove('active');
                    }
                    
                    if (response.success) {
                        // Show success message
                        toastr.success('Payment declined successfully!');
                        
                        // Reload the page after a short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        // Show error message
                        toastr.error(response.message || 'Failed to decline payment');
                    }
                },
                error: function(xhr, status, error) {
                    // Hide loading overlay
                    if (overlay) {
                        overlay.classList.remove('active');
                    }
                    
                    // Log detailed error information
                    console.error('Error details:', {
                        status: status,
                        error: error,
                        response: xhr.responseText,
                        url: `${BASE_URL}/tour/${tourId}/decline-payment`
                    });
                    
                    // Show appropriate error message
                    let errorMessage = 'An error occurred while declining the payment';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch (e) {
                        // If response is not JSON, use the raw response text
                        errorMessage = xhr.responseText || errorMessage;
                    }
                    
                    toastr.error(errorMessage);
                }
            });
        }
    }
</script>

{{-- <script>
    $(document).ready(function() {
        // ... existing code ...

        // Handle Approve Booking Form Submission
        $('[id^="approveBookingForm"]').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.html();

            // Disable submit button and show loading state
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Processing...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Booking has been approved successfully.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Reload the page to reflect changes
                        location.reload();
                    });
                },
                error: function(xhr) {
                    // Show error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Something went wrong. Please try again.',
                    });
                },
                complete: function() {
                    // Reset button state
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            });
        });
    });
</script> --}}

<script>
    function showApproveLoader(tourId) {
        // Show the loader
        const loader = document.getElementById(`approveBookingLoader${tourId}`);
        loader.style.display = 'flex';
        
        // Hide the modal
        $(`#approveBookingModal${tourId}`).modal('hide');
        
        // Submit the form
        document.getElementById(`approveBookingForm${tourId}`).submit();
    }

    // Handle form submission
    $(document).ready(function() {
        $('[id^="approveBookingForm"]').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const tourId = form.find('input[name="tour_id"]').val();
            
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // First hide the loader
                    const loader = document.getElementById(`approveBookingLoader${tourId}`);
                    loader.style.display = 'none';
                    
                    // Then show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Booking has been approved successfully.',
                        showConfirmButton: true,
                        confirmButtonColor: '#28a745',
                        confirmButtonText: 'OK',
                        timer: 3000,
                        timerProgressBar: true,
                        didClose: () => {
                            // Reload the page after the alert is closed
                            window.location.reload();
                        }
                    });
                },
                error: function(xhr, status, error) {
                    // Hide loader
                    const loader = document.getElementById(`approveBookingLoader${tourId}`);
                    loader.style.display = 'none';
                    
                    // Show detailed error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'An error occurred while approving the booking.',
                        confirmButtonColor: '#dc3545',
                        showConfirmButton: true
                    });
                    
                    console.error('Error:', error);
                }
            });
        });
    });
</script>

<!-- Currency Conversion Script -->
<script>
    // Store exchange rates cache
    let exchangeRatesCache = {};
    
    // Currency symbols mapping
    const currencySymbols = {
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
        'JPY': '¥',
        'CNY': '¥',
        'INR': '₹',
        'SGD': 'S$',
        'AUD': 'A$',
        'CAD': 'C$',
        'CHF': 'CHF',
        'KRW': '₩',
        'THB': '฿',
        'MYR': 'RM',
        'IDR': 'Rp',
        'PHP': '₱',
        'VND': '₫',
        'HKD': 'HK$',
        'NZD': 'NZ$',
        'SEK': 'kr',
        'NOK': 'kr',
        'DKK': 'kr',
        'PLN': 'zł',
        'CZK': 'Kč',
        'HUF': 'Ft',
        'RON': 'lei',
        'BGN': 'лв',
        'HRK': 'kn',
        'RUB': '₽',
        'TRY': '₺',
        'ZAR': 'R',
        'BRL': 'R$',
        'MXN': '$',
        'ILS': '₪',
        'ISK': 'kr'
    };
    
    async function updatePaymentAmount(tourId, selectedCurrency) {
        if (!selectedCurrency) {
            return;
        }
        
        const amountField = document.getElementById(`payment_amount${tourId}`);
                const currencySymbol = document.getElementById(`currencySymbol${tourId}`);
        const conversionInfo = document.getElementById(`conversionInfo${tourId}`);
        const exchangeRateSection = document.getElementById(`exchangeRateSection${tourId}`);
        const exchangeRateField = document.getElementById(`exchange_rate${tourId}`);
        const exchangeRateCurrency = document.getElementById(`exchangeRateCurrency${tourId}`);
        const rateSourceText = document.getElementById(`rateSourceText${tourId}`);
        const dueAmountSGD = parseFloat(document.getElementById(`amount${tourId}`).value);
        
        // Update currency symbol and exchange rate currency display
        currencySymbol.textContent = currencySymbols[selectedCurrency] || selectedCurrency;
        exchangeRateCurrency.textContent = selectedCurrency;
        
        if (selectedCurrency === 'SGD') {
            // If SGD is selected, hide exchange rate and conversion info
            amountField.value = dueAmountSGD.toFixed(2);
            document.getElementById(`conversionInfoContainer${tourId}`).style.display = 'none';
            exchangeRateSection.style.display = 'none';
            return;
        } else {
            // Show exchange rate section for non-SGD currencies
            exchangeRateSection.style.display = 'block';
        }
        
        // Show loading state
        conversionInfo.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i>Fetching exchange rate...`;
        exchangeRateSection.style.display = 'block';
        
        try {
            // Check cache first
            const cacheKey = `SGD_${selectedCurrency}`;
            let exchangeRate;
            
            if (exchangeRatesCache[cacheKey] && exchangeRatesCache[cacheKey].timestamp > Date.now() - 300000) { // 5 minutes cache
                exchangeRate = exchangeRatesCache[cacheKey].rate;
            } else {
                // Fetch exchange rate from your currency service
                const response = await fetch(`{{ route('get-exchange-rate') }}?from=SGD&to=${selectedCurrency}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Failed to fetch exchange rate');
                }
                
                const data = await response.json();
                exchangeRate = data.rate;
                
                // Cache the rate
                exchangeRatesCache[cacheKey] = {
                    rate: exchangeRate,
                    timestamp: Date.now()
                };
            }
            
            if (exchangeRate) {
                const convertedAmount = (dueAmountSGD * exchangeRate).toFixed(2);
                amountField.value = convertedAmount;
                
                // Update exchange rate field and show conversion info
                exchangeRateField.value = exchangeRate.toFixed(4);
                rateSourceText.textContent = 'API';
                document.getElementById(`conversionInfoContainer${tourId}`).style.display = 'block';
                conversionInfo.innerHTML = `<i class="fas fa-info-circle me-1"></i>Amount in SGD: ${dueAmountSGD.toFixed(2)}`;
                
                // Add validation for the converted amount
                amountField.setAttribute('data-sgd-equivalent', dueAmountSGD);
                amountField.setAttribute('data-exchange-rate', exchangeRate);
            } else {
                throw new Error('Exchange rate not available');
            }
        } catch (error) {
            console.error('Error fetching exchange rate:', error);
            
            // Fallback to manual entry
            amountField.value = '';
            exchangeRateField.value = '1.00';
            rateSourceText.textContent = 'Manual';
            document.getElementById(`conversionInfoContainer${tourId}`).style.display = 'block';
            conversionInfo.innerHTML = `<i class="fas fa-exclamation-triangle text-warning me-1"></i>Exchange rate unavailable. Please enter rate and amount manually.`;
            
            // Show a toast notification
            toastr.warning(`Unable to fetch exchange rate for ${selectedCurrency}. Please enter the amount manually.`);
        }
    }
    
    // Add validation for payment amount changes
    function validatePaymentAmount(tourId) {
        const amountField = document.getElementById(`payment_amount${tourId}`);
        const selectedCurrency = document.getElementById(`currency${tourId}`).value;
        const sgdEquivalent = parseFloat(amountField.getAttribute('data-sgd-equivalent'));
        const exchangeRate = parseFloat(amountField.getAttribute('data-exchange-rate'));
        
        if (selectedCurrency !== 'SGD' && sgdEquivalent && exchangeRate) {
            const enteredAmount = parseFloat(amountField.value);
            const calculatedSGD = enteredAmount / exchangeRate;
            const dueAmountSGD = parseFloat(document.getElementById(`amount${tourId}`).value);
            
            if (calculatedSGD > dueAmountSGD + 0.01) { // Allow small rounding differences
                toastr.warning(`The entered amount exceeds the due amount when converted to SGD.`);
                amountField.value = (dueAmountSGD * exchangeRate).toFixed(2);
            }
        }
    }
    
    // Update form submission to handle currency conversion
    function updateSubmitPaymentForm(tourId) {
        const originalFunction = window.submitPaymentForm;
        
        window.submitPaymentForm = function(tourId) {
            // Validate before submission
            if (!validateBeforeSubmit(tourId)) {
                return false;
            }
            
            // Get the form
            const form = document.getElementById('paymentForm' + tourId);
            
            // Get currency conversion data
            const selectedCurrency = document.getElementById(`currency${tourId}`).value;
            const paymentAmount = parseFloat(document.getElementById(`payment_amount${tourId}`).value);
            const exchangeRate = parseFloat(document.getElementById(`payment_amount${tourId}`).getAttribute('data-exchange-rate')) || 1;
            
            // Calculate SGD converted amount
            let sgdConvertedAmount = paymentAmount;
            if (selectedCurrency !== 'SGD' && exchangeRate > 0) {
                sgdConvertedAmount = paymentAmount / exchangeRate;
            }
            
            // Remove any existing hidden currency fields to avoid duplicates
            const existingFields = form.querySelectorAll('input[name="selected_currency"], input[name="exchange_rate"], input[name="converted_amount"], input[name="sgd_amount"], input[name="original_amount"], input[name="original_currency"]');
            existingFields.forEach(field => field.remove());
            
            // Add hidden inputs for currency data
            const currencyInput = document.createElement('input');
            currencyInput.type = 'hidden';
            currencyInput.name = 'selected_currency';
            currencyInput.value = selectedCurrency;
            form.appendChild(currencyInput);
            
            const rateInput = document.createElement('input');
            rateInput.type = 'hidden';
            rateInput.name = 'exchange_rate';
            rateInput.value = exchangeRate.toFixed(4);
            form.appendChild(rateInput);
            
            const originalAmountInput = document.createElement('input');
            originalAmountInput.type = 'hidden';
            originalAmountInput.name = 'original_amount';
            originalAmountInput.value = paymentAmount.toFixed(2);
            form.appendChild(originalAmountInput);
            
            const originalCurrencyInput = document.createElement('input');
            originalCurrencyInput.type = 'hidden';
            originalCurrencyInput.name = 'original_currency';
            originalCurrencyInput.value = selectedCurrency;
            form.appendChild(originalCurrencyInput);
            
            const sgdAmountInput = document.createElement('input');
            sgdAmountInput.type = 'hidden';
            sgdAmountInput.name = 'sgd_amount';
            sgdAmountInput.value = sgdConvertedAmount.toFixed(2);
            form.appendChild(sgdAmountInput);
            
            // Update the main amount field to SGD converted amount for backend processing
            document.getElementById(`amount${tourId}`).value = sgdConvertedAmount.toFixed(2);
            
            // Call original function
            if (originalFunction) {
                originalFunction(tourId);
            }
        };
    }
    

    
         // Validation function for payment amount
    function validatePaymentAmountInput(tourId) {
        const paymentAmount = parseFloat(document.getElementById(`payment_amount${tourId}`).value) || 0;
        const selectedCurrency = document.getElementById(`currency${tourId}`).value;
        const exchangeRate = parseFloat(document.getElementById(`payment_amount${tourId}`).getAttribute('data-exchange-rate')) || 1;
        const originalDueAmount = parseFloat(document.getElementById(`amount${tourId}`).getAttribute('data-original-due')) || parseFloat(document.getElementById(`amount${tourId}`).value);
        
        let sgdEquivalent = paymentAmount;
        
        // Convert to SGD if different currency
        if (selectedCurrency !== 'SGD' && exchangeRate > 0) {
            sgdEquivalent = paymentAmount / exchangeRate;
        }
        
        // Update the SGD equivalent display
        updateSGDEquivalentDisplay(tourId, sgdEquivalent, selectedCurrency);
        
        const validationError = document.getElementById(`paymentValidationError${tourId}`);
        const validationMessage = document.getElementById(`validationMessage${tourId}`);
        const paymentField = document.getElementById(`payment_amount${tourId}`);
        
        if (sgdEquivalent > originalDueAmount) {
            // Show validation error
            validationMessage.textContent = `Payment amount exceeds due amount. Maximum allowed: ${(originalDueAmount * exchangeRate).toFixed(2)} ${selectedCurrency}`;
            validationError.style.display = 'block';
            paymentField.classList.add('is-invalid');
            
            // Auto-correct to maximum allowed
            const maxAllowed = (originalDueAmount * exchangeRate).toFixed(2);
            paymentField.value = maxAllowed;
            
            // Recalculate SGD equivalent for corrected amount
            const correctedSGD = parseFloat(maxAllowed) / exchangeRate;
            updateSGDEquivalentDisplay(tourId, correctedSGD, selectedCurrency);
            
            // Show toast notification
            toastr.warning(`Payment amount adjusted to maximum allowed: ${maxAllowed} ${selectedCurrency}`);
            
            return false;
        } else {
            // Hide validation error
            validationError.style.display = 'none';
            paymentField.classList.remove('is-invalid');
            return true;
        }
    }
    
    // Function to update SGD equivalent display
    function updateSGDEquivalentDisplay(tourId, sgdAmount, selectedCurrency) {
        const conversionInfo = document.getElementById(`conversionInfo${tourId}`);
        const conversionInfoContainer = document.getElementById(`conversionInfoContainer${tourId}`);
        
        if (selectedCurrency !== 'SGD') {
            conversionInfoContainer.style.display = 'block';
            conversionInfo.innerHTML = `<i class="fas fa-info-circle me-1"></i>Amount in SGD: ${sgdAmount.toFixed(2)}`;
        } else {
            conversionInfoContainer.style.display = 'none';
        }
    }


    
         // Recalculate payment amount when exchange rate is changed (new simplified function)
    function recalculateFromExchangeRate(tourId) {
        const exchangeRate = parseFloat(document.getElementById(`exchange_rate${tourId}`).value) || 1;
        const selectedCurrency = document.getElementById(`currency${tourId}`).value;
        const dueAmountSGD = parseFloat(document.getElementById(`amount${tourId}`).value);
        const paymentAmountField = document.getElementById(`payment_amount${tourId}`);
        const rateSourceText = document.getElementById(`rateSourceText${tourId}`);
        
        if (selectedCurrency !== 'SGD') {
            const convertedAmount = (dueAmountSGD * exchangeRate).toFixed(2);
            paymentAmountField.value = convertedAmount;
            
            // Update rate source to manual
            rateSourceText.textContent = 'Manual';
            
            // Update SGD equivalent display
            updateSGDEquivalentDisplay(tourId, dueAmountSGD, selectedCurrency);
        }
        
        // Update data attributes
        paymentAmountField.setAttribute('data-exchange-rate', exchangeRate);
        
        // Validate the final payment amount
        validatePaymentAmountInput(tourId);
    }

     // Legacy function for backward compatibility (updated)
    function recalculateFromRate(tourId) {
        recalculateFromExchangeRate(tourId);
    }
    
         // Enhanced updatePaymentAmount function (simplified)
    async function updatePaymentAmountEnhanced(tourId, selectedCurrency) {
        // Use the original automatic function
        await updatePaymentAmount(tourId, selectedCurrency);
    }

    // Initialize currency conversion on page load
    $(document).ready(function() {
        // Apply to all payment modals
        $('[id^="currency"]').each(function() {
            const tourId = this.id.replace('currency', '');
            if (tourId) {
                updateSubmitPaymentForm(tourId);
                
                // Add event listener for amount validation
                $(`#payment_amount${tourId}`).on('blur', function() {
                    validatePaymentAmount(tourId);
                });
                
                // Initialize exchange rate field
                $(`#exchange_rate${tourId}`).val('1.00');
                
                // Store original due amount for validation
                const originalAmount = parseFloat($(`#amount${tourId}`).val());
                $(`#amount${tourId}`).attr('data-original-due', originalAmount);
            }
        });
    });
</script>

<!-- Currency Conversion Script -->
<script>
    // Store exchange rates cache
    let exchangeRatesCache = {};
    
    // Currency symbols mapping
    const currencySymbols = {
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
        'JPY': '¥',
        'CNY': '¥',
        'INR': '₹',
        'SGD': 'S$',
        'AUD': 'A$',
        'CAD': 'C$',
        'CHF': 'CHF',
        'KRW': '₩',
        'THB': '฿',
        'MYR': 'RM',
        'IDR': 'Rp',
        'PHP': '₱',
        'VND': '₫',
        'HKD': 'HK$',
        'NZD': 'NZ$',
        'SEK': 'kr',
        'NOK': 'kr',
        'DKK': 'kr',
        'PLN': 'zł',
        'CZK': 'Kč',
        'HUF': 'Ft',
        'RON': 'lei',
        'BGN': 'лв',
        'HRK': 'kn',
        'RUB': '₽',
        'TRY': '₺',
        'ZAR': 'R',
        'BRL': 'R$',
        'MXN': '$',
        'ILS': '₪',
        'ISK': 'kr'
    };
    
    async function updatePaymentAmount(tourId, selectedCurrency) {
        if (!selectedCurrency) {
            return;
        }
        
        const amountField = document.getElementById(`payment_amount${tourId}`);
                const currencySymbol = document.getElementById(`currencySymbol${tourId}`);
        const conversionInfo = document.getElementById(`conversionInfo${tourId}`);
        const exchangeRateSection = document.getElementById(`exchangeRateSection${tourId}`);
        const exchangeRateField = document.getElementById(`exchange_rate${tourId}`);
        const exchangeRateCurrency = document.getElementById(`exchangeRateCurrency${tourId}`);
        const rateSourceText = document.getElementById(`rateSourceText${tourId}`);
        const dueAmountSGD = parseFloat(document.getElementById(`amount${tourId}`).value);
        
        // Update currency symbol and exchange rate currency display
        currencySymbol.textContent = currencySymbols[selectedCurrency] || selectedCurrency;
        exchangeRateCurrency.textContent = selectedCurrency;
        
        if (selectedCurrency === 'SGD') {
            // If SGD is selected, hide exchange rate and conversion info
            amountField.value = dueAmountSGD.toFixed(2);
            document.getElementById(`conversionInfoContainer${tourId}`).style.display = 'none';
            exchangeRateSection.style.display = 'none';
            return;
        } else {
            // Show exchange rate section for non-SGD currencies
            exchangeRateSection.style.display = 'block';
        }
        
        // Show loading state
        conversionInfo.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i>Fetching exchange rate...`;
        exchangeRateSection.style.display = 'block';
        
        try {
            // Check cache first
            const cacheKey = `SGD_${selectedCurrency}`;
            let exchangeRate;
            
            if (exchangeRatesCache[cacheKey] && exchangeRatesCache[cacheKey].timestamp > Date.now() - 300000) { // 5 minutes cache
                exchangeRate = exchangeRatesCache[cacheKey].rate;
            } else {
                // Fetch exchange rate from your currency service
                const response = await fetch(`{{ route('get-exchange-rate') }}?from=SGD&to=${selectedCurrency}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Failed to fetch exchange rate');
                }
                
                const data = await response.json();
                exchangeRate = data.rate;
                
                // Cache the rate
                exchangeRatesCache[cacheKey] = {
                    rate: exchangeRate,
                    timestamp: Date.now()
                };
            }
            
            if (exchangeRate) {
                const convertedAmount = (dueAmountSGD * exchangeRate).toFixed(2);
                amountField.value = convertedAmount;
                
                // Update exchange rate field and show conversion info
                exchangeRateField.value = exchangeRate.toFixed(4);
                rateSourceText.textContent = 'API';
                document.getElementById(`conversionInfoContainer${tourId}`).style.display = 'block';
                conversionInfo.innerHTML = `<i class="fas fa-info-circle me-1"></i>Amount in SGD: ${dueAmountSGD.toFixed(2)}`;
                
                // Add validation for the converted amount
                amountField.setAttribute('data-sgd-equivalent', dueAmountSGD);
                amountField.setAttribute('data-exchange-rate', exchangeRate);
            } else {
                throw new Error('Exchange rate not available');
            }
        } catch (error) {
            console.error('Error fetching exchange rate:', error);
            
            // Fallback to manual entry
            amountField.value = '';
            exchangeRateField.value = '1.00';
            rateSourceText.textContent = 'Manual';
            document.getElementById(`conversionInfoContainer${tourId}`).style.display = 'block';
            conversionInfo.innerHTML = `<i class="fas fa-exclamation-triangle text-warning me-1"></i>Exchange rate unavailable. Please enter rate and amount manually.`;
            
            // Show a toast notification
            toastr.warning(`Unable to fetch exchange rate for ${selectedCurrency}. Please enter the amount manually.`);
        }
    }
    
    // Add validation for payment amount changes
    function validatePaymentAmount(tourId) {
        const amountField = document.getElementById(`payment_amount${tourId}`);
        const selectedCurrency = document.getElementById(`currency${tourId}`).value;
        const sgdEquivalent = parseFloat(amountField.getAttribute('data-sgd-equivalent'));
        const exchangeRate = parseFloat(amountField.getAttribute('data-exchange-rate'));
        
        if (selectedCurrency !== 'SGD' && sgdEquivalent && exchangeRate) {
            const enteredAmount = parseFloat(amountField.value);
            const calculatedSGD = enteredAmount / exchangeRate;
            const dueAmountSGD = parseFloat(document.getElementById(`amount${tourId}`).value);
            
            if (calculatedSGD > dueAmountSGD + 0.01) { // Allow small rounding differences
                toastr.warning(`The entered amount exceeds the due amount when converted to SGD.`);
                amountField.value = (dueAmountSGD * exchangeRate).toFixed(2);
            }
        }
    }
    
    // Update form submission to handle currency conversion
    function updateSubmitPaymentForm(tourId) {
        const originalFunction = window.submitPaymentForm;
        
        window.submitPaymentForm = function(tourId) {
            // Validate before submission
            if (!validateBeforeSubmit(tourId)) {
                return false;
            }
            
            // Get the form
            const form = document.getElementById('paymentForm' + tourId);
            
            // Get currency conversion data
            const selectedCurrency = document.getElementById(`currency${tourId}`).value;
            const paymentAmount = parseFloat(document.getElementById(`payment_amount${tourId}`).value);
            const exchangeRate = parseFloat(document.getElementById(`payment_amount${tourId}`).getAttribute('data-exchange-rate')) || 1;
            
            // Calculate SGD converted amount
            let sgdConvertedAmount = paymentAmount;
            if (selectedCurrency !== 'SGD' && exchangeRate > 0) {
                sgdConvertedAmount = paymentAmount / exchangeRate;
            }
            
            // Remove any existing hidden currency fields to avoid duplicates
            const existingFields = form.querySelectorAll('input[name="selected_currency"], input[name="exchange_rate"], input[name="converted_amount"], input[name="sgd_amount"], input[name="original_amount"], input[name="original_currency"]');
            existingFields.forEach(field => field.remove());
            
            // Add hidden inputs for currency data
            const currencyInput = document.createElement('input');
            currencyInput.type = 'hidden';
            currencyInput.name = 'selected_currency';
            currencyInput.value = selectedCurrency;
            form.appendChild(currencyInput);
            
            const rateInput = document.createElement('input');
            rateInput.type = 'hidden';
            rateInput.name = 'exchange_rate';
            rateInput.value = exchangeRate.toFixed(4);
            form.appendChild(rateInput);
            
            const originalAmountInput = document.createElement('input');
            originalAmountInput.type = 'hidden';
            originalAmountInput.name = 'original_amount';
            originalAmountInput.value = paymentAmount.toFixed(2);
            form.appendChild(originalAmountInput);
            
            const originalCurrencyInput = document.createElement('input');
            originalCurrencyInput.type = 'hidden';
            originalCurrencyInput.name = 'original_currency';
            originalCurrencyInput.value = selectedCurrency;
            form.appendChild(originalCurrencyInput);
            
            const sgdAmountInput = document.createElement('input');
            sgdAmountInput.type = 'hidden';
            sgdAmountInput.name = 'sgd_amount';
            sgdAmountInput.value = sgdConvertedAmount.toFixed(2);
            form.appendChild(sgdAmountInput);
            
            // Update the main amount field to SGD converted amount for backend processing
            document.getElementById(`amount${tourId}`).value = sgdConvertedAmount.toFixed(2);
            
            // Call original function
            if (originalFunction) {
                originalFunction(tourId);
            }
        };
    }
    

    
         // Validation function for payment amount
    function validatePaymentAmountInput(tourId) {
        const paymentAmount = parseFloat(document.getElementById(`payment_amount${tourId}`).value) || 0;
        const selectedCurrency = document.getElementById(`currency${tourId}`).value;
        const exchangeRate = parseFloat(document.getElementById(`payment_amount${tourId}`).getAttribute('data-exchange-rate')) || 1;
        const originalDueAmount = parseFloat(document.getElementById(`amount${tourId}`).getAttribute('data-original-due')) || parseFloat(document.getElementById(`amount${tourId}`).value);
        
        let sgdEquivalent = paymentAmount;
        
        // Convert to SGD if different currency
        if (selectedCurrency !== 'SGD' && exchangeRate > 0) {
            sgdEquivalent = paymentAmount / exchangeRate;
        }
        
        // Update the SGD equivalent display
        updateSGDEquivalentDisplay(tourId, sgdEquivalent, selectedCurrency);
        
        const validationError = document.getElementById(`paymentValidationError${tourId}`);
        const validationMessage = document.getElementById(`validationMessage${tourId}`);
        const paymentField = document.getElementById(`payment_amount${tourId}`);
        
        if (sgdEquivalent > originalDueAmount) {
            // Show validation error
            validationMessage.textContent = `Payment amount exceeds due amount. Maximum allowed: ${(originalDueAmount * exchangeRate).toFixed(2)} ${selectedCurrency}`;
            validationError.style.display = 'block';
            paymentField.classList.add('is-invalid');
            
            // Auto-correct to maximum allowed
            const maxAllowed = (originalDueAmount * exchangeRate).toFixed(2);
            paymentField.value = maxAllowed;
            
            // Recalculate SGD equivalent for corrected amount
            const correctedSGD = parseFloat(maxAllowed) / exchangeRate;
            updateSGDEquivalentDisplay(tourId, correctedSGD, selectedCurrency);
            
            // Show toast notification
            toastr.warning(`Payment amount adjusted to maximum allowed: ${maxAllowed} ${selectedCurrency}`);
            
            return false;
        } else {
            // Hide validation error
            validationError.style.display = 'none';
            paymentField.classList.remove('is-invalid');
            return true;
        }
    }
    
    // Function to update SGD equivalent display
    function updateSGDEquivalentDisplay(tourId, sgdAmount, selectedCurrency) {
        const conversionInfo = document.getElementById(`conversionInfo${tourId}`);
        const conversionInfoContainer = document.getElementById(`conversionInfoContainer${tourId}`);
        
        if (selectedCurrency !== 'SGD') {
            conversionInfoContainer.style.display = 'block';
            conversionInfo.innerHTML = `<i class="fas fa-info-circle me-1"></i>Amount in SGD: ${sgdAmount.toFixed(2)}`;
        } else {
            conversionInfoContainer.style.display = 'none';
        }
    }


    
         // Recalculate payment amount when exchange rate is changed (new simplified function)
    function recalculateFromExchangeRate(tourId) {
        const exchangeRate = parseFloat(document.getElementById(`exchange_rate${tourId}`).value) || 1;
        const selectedCurrency = document.getElementById(`currency${tourId}`).value;
        const dueAmountSGD = parseFloat(document.getElementById(`amount${tourId}`).value);
        const paymentAmountField = document.getElementById(`payment_amount${tourId}`);
        const rateSourceText = document.getElementById(`rateSourceText${tourId}`);
        
        if (selectedCurrency !== 'SGD') {
            const convertedAmount = (dueAmountSGD * exchangeRate).toFixed(2);
            paymentAmountField.value = convertedAmount;
            
            // Update rate source to manual
            rateSourceText.textContent = 'Manual';
            
            // Update SGD equivalent display
            updateSGDEquivalentDisplay(tourId, dueAmountSGD, selectedCurrency);
        }
        
        // Update data attributes
        paymentAmountField.setAttribute('data-exchange-rate', exchangeRate);
        
        // Validate the final payment amount
        validatePaymentAmountInput(tourId);
    }

     // Legacy function for backward compatibility (updated)
    function recalculateFromRate(tourId) {
        recalculateFromExchangeRate(tourId);
    }
    
         // Enhanced updatePaymentAmount function (simplified)
    async function updatePaymentAmountEnhanced(tourId, selectedCurrency) {
        // Use the original automatic function
        await updatePaymentAmount(tourId, selectedCurrency);
    }

    // Initialize currency conversion on page load
    $(document).ready(function() {
        // Apply to all payment modals
        $('[id^="currency"]').each(function() {
            const tourId = this.id.replace('currency', '');
            if (tourId) {
                updateSubmitPaymentForm(tourId);
                
                // Add event listener for amount validation
                $(`#payment_amount${tourId}`).on('blur', function() {
                    validatePaymentAmount(tourId);
                });
                
                // Initialize exchange rate field
                $(`#exchange_rate${tourId}`).val('1.00');
                
                // Store original due amount for validation
                const originalAmount = parseFloat($(`#amount${tourId}`).val());
                $(`#amount${tourId}`).attr('data-original-due', originalAmount);
            }
        });
    });
</script>
@endsection