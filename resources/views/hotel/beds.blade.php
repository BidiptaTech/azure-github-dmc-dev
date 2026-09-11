@extends('layouts.layout')
@section('title', 'Hotels')


@section('content')
@extends('layouts.datatablecss')
@include('hotel.tapview', ['hotel' => $hotel])
<link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
{{-- <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet"> --}}
<!-- Add Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">

<!-- Add Bootstrap Tab Styles -->
<style>
    .nav-tabs .nav-link {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        color: #495057;
        margin-right: 0.25rem;
        border-radius: 0.375rem 0.375rem 0 0;
    }

    .nav-tabs .nav-link:hover {
        background-color: #e9ecef;
        color: #495057;
    }

    .nav-tabs .nav-link.active {
        background-color: #696cff;
        border-color: #696cff;
        color: white;
    }

    .tab-content {
        background-color: white;
        border: 1px solid #dee2e6;
        border-top: none;
        padding: 0;
        border-radius: 0 0 0.375rem 0.375rem;
    }

    .bulk-upload-info {
        background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1rem;
        border: 1px solid rgba(102, 126, 234, 0.1);
    }

    .bulk-upload-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 0.375rem;
        padding: 0.75rem 1.5rem;
        color: white;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .bulk-upload-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        color: white;
        text-decoration: none;
    }

    /* DMC Filter Styles */
    #dmcFilter {
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        background-color: #fff;
        color: #566a7f;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    #dmcFilter:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
        outline: 0;
    }

    /* DMC Badge Styles */
    .badge.bg-primary {
        background-color: #696cff !important;
    }

    .badge.bg-secondary {
        background-color: #8592a3 !important;
    }

    /* Filter Info Text */
    .filter-info {
        font-size: 0.875rem;
        color: #6c757d;
        font-style: italic;
    }

    /* DataTable Responsive Styles */
    .dataTables_wrapper .dataTables_filter input {
        padding: 0.4rem 0.75rem;
        border-radius: 0.375rem;
        border: 1px solid #d9dee3;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5rem 0.75rem;
        margin: 0 0.125rem;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        background-color: #fff;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background-color: #696cff;
        border-color: #696cff;
        color: #fff !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background-color: #e7e7ff;
        border-color: #696cff;
        color: #696cff !important;
    }

    /* Table Styles */
    .table> :not(caption)>*>* {
        padding: 0.75rem;
    }

    /* Button Styles */
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Select2 Custom Styles */
    .select2-container .select2-selection--single {
        height: 38px !important;
        line-height: 38px !important;
        padding: 0 12px;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
        padding-left: 0;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }

    /* Increase the height of the dropdown items */
    .select2-container .select2-results__option {
        padding: 8px 12px;
    }

    /* Focus state */
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #696cff;
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
    }

    /* Dropdown styling */
    .select2-dropdown {
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    /* Search box styling */
    .select2-search--dropdown .select2-search__field {
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        padding: 6px 12px;
        outline: none;
    }

    .select2-search--dropdown .select2-search__field:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
    }

    /* Highlighted option */
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #696cff;
        color: white;
    }

    /* Selected option */
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #e7e7ff;
        color: #696cff;
    }

    /* Dropdown width */
    .select2-container {
        width: 100% !important;
    }
</style>
<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="d-flex align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">Manage Beds for {{ $hotel->name }}</h5>
                    <x-currency-price-note :country="$hotel->country ?? null" :watch-dmc="in_array($auth_user->role_id, [1, 20])" />
                </span>
                <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </div>
            
            @if(!empty($canManageBedConfig))
            <!-- Navigation Tabs -->
            <div class="card-body p-0">
                <ul class="nav nav-tabs" id="bedsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="add-single-tab" data-bs-toggle="tab" data-bs-target="#add-single" 
                                type="button" role="tab" aria-controls="add-single" aria-selected="true">
                            <i class="ri-add-line me-1"></i>Add Single Bed
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="bedsTabContent">
                    <!-- Add Single Bed Tab -->
                    <div class="tab-pane fade show active" id="add-single" role="tabpanel" aria-labelledby="add-single-tab">
                        <div class="p-4">
            <form id="hotelForm" method="POST" action="{{ route('storebed') }}"
                enctype="multipart/form-data" class="card-body js-submit-loader-form" data-loader-message="Saving...">
                @csrf
                <input type="hidden" class="form-control" name="hotel_id" id="hotel_id"
                    value="{{ $hotel->hotel_unique_id }}">
                
                @if($auth_user->role_id == 1 || $auth_user->role_id == 20)
                <!-- DMC Selection (Required for Admin and Role 20) -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <strong>Note:</strong> As an admin/manager, you must select a DMC that has already selected this hotel.
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="dmc_selection" class="form-label">
                            <strong><i class="ri-building-line"></i> Select DMC</strong><span class="text-danger">*</span>
                        </label>
                        <select id="dmc_selection" class="form-control" name="dmc_id" required>
                            <option value="">Search and Select DMC</option>
                            @forelse($dmcUsers as $dmc)
                                <option value="{{ $dmc->userId }}" data-currency="{{ $dmc->currency ?? '' }}">{{ $dmc->company_name }} ({{ $dmc->name }})</option>
                            @empty
                            @endforelse
                        </select>
                        <small class="text-muted">
                            @if($dmcUsers->isEmpty())
                                <i class="ri-information-line"></i> No DMC has selected this hotel yet. Ask a DMC to select it first.
                            @else
                                <i class="ri-information-line"></i> Only DMCs that have selected this hotel are listed.
                            @endif
                        </small>
                    </div>
                </div>
                @endif
                
                <hr>
                <div id="hotelBedsContainer">
                    <div class="hotel-rate-form">
                        <div class="row">
                            <!-- Room Category -->
                            <div class="col-md-3 mb-3">
                                <label for="room_type" class="form-label">
                                    <strong><i class="ri-door-open-line"></i> Room Category</strong><span class="text-danger">*</span>
                                </label>
                                <select id="room_type" class="form-control" name="room_id" required>
                                    <option value="">Select Room Category</option>
                                    @foreach($rooms as $room)
                                        <option value="{{$room->room_id}}">{{$room->room_type}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Bed Type -->
                            <div class="col-md-3 mb-3">
                                <label for="bed_type" class="form-label">
                                    <strong><i class="ri-hotel-bed-line"></i> Bed Type</strong><span class="text-danger">*</span>
                                </label>
                                <select id="bed_type" class="form-control" name="bed_type" required onchange="onBedTypeChange()">
                                    <option value="">Select Bed Type</option>
                                    @foreach($beds as $bed)
                                        <option value="{{$bed->bedId}}">{{$bed->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- No of rooms -->
                            <div class="mb-3 col-md-3">
                                <label for="no_of_rooms" class="form-label"><strong>No. of
                                        Rooms</strong><span class="text-danger">*</span></label>
                                <input type="number" id="no_of_rooms" class="form-control" name="no_of_rooms"
                                       min="1" step="1" required disabled
                                       placeholder="Select Room Category First">
                                <small id="no_of_rooms_hint" class="text-muted">Select a room category first</small>
                                @error('no_of_rooms')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- Occupancy -->
                            <div class="mb-3 col-md-3">
                                <label for="max-occupancy" class="form-label"><strong>Maximum Occupancy</strong></label>
                                <input type="number" id="max-occupancy" name="max_occupancy" class="form-control" readonly>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="bed_profit_margin" class="form-label"><strong>Profit (margin)</strong></label>
                                <select id="bed_profit_margin" class="form-select js-bed-profit-type">
                                    <option value="percentage" selected>%</option>
                                    <option value="flat">Flat</option>
                                </select>
                                <small class="text-muted">Helper only — not saved</small>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="bed_profit_amount" class="form-label"><strong>Profit amount</strong></label>
                                <input type="number" id="bed_profit_amount" class="form-control js-bed-profit-amount"
                                       value="0" min="0" step="0.01" placeholder="Enter profit amount">
                                <small class="text-muted">Auto-fills Sell from Cost</small>
                            </div>

                            <!-- extra bed -->
                            <div class="col-md-3 mb-3">
                                <label for="extra_bed" class="form-label"><strong>Extra
                                        Bed</strong><span class="text-danger">*</span></label>
                                <select name="extra_bed" id="extra_bed" class="form-control"
                                    onchange="toggleExtraBedField()">
                                    <option value="">Select One</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>

                            <!-- extra bed type -->
                            <div class="col-md-3 mb-3 extra_bed_type" style="display: none;">
                                <label for="${bedType}-extra-bed-type" class="form-label"><strong>Extra Bed
                                        Type</strong><span class="text-danger">*</span></label>
                                <select name="extra_bed_type" id="extra_bed_type" class="form-control">
                                    <option value="">Select One</option>
                                    <option value="Sofa Bed">Sofa Bed</option>
                                    <option value="Wall Bed">Wall Bed</option>
                                    <option value="Futon">Futon</option>
                                    <option value="Rollaway bed">Rollaway bed</option>
                                    <option value="Bunk bed">Bunk bed</option>
                                </select>
                            </div>

                            <!-- extra bed price: Cost then Sell -->
                            <div class="col-md-3 mb-3 extra_bed_price" style="display: none;">
                                <label for="extra_bed_cost_price" class="form-label"><strong>Extra Bed
                                        Price(Cost)</strong><span class="text-danger">*</span></label>
                                <input type="number" name="extra_bed_cost_price" id="extra_bed_cost_price"
                                    class="form-control js-bed-cost" data-sell-target="extra_bed_price"
                                    placeholder="Enter Cost Price" min="0" step="0.01">
                            </div>
                            <div class="col-md-3 mb-3 extra_bed_price" style="display: none;">
                                <label for="extra_bed_price" class="form-label"><strong>Extra Bed
                                        Price(Sell)</strong><span class="text-danger">*</span></label>
                                <input type="number" name="extra_bed_price" id="extra_bed_price"
                                    class="form-control js-bed-sell" placeholder="Enter Sell Price" min="0" step="0.01">
                            </div>

                            <div class="mb-3 col-md-3">
                                <label for="adult_count" class="form-label"><strong>Adults</strong></label>
                                <select id="adult_count" name="adult_count" class="form-control">
                                    <option value="">Select Adults</option>
                                </select>
                            </div>

                            <div class="mb-3 col-md-3">
                                <label for="child_count" class="form-label"><strong>Children</strong></label>
                                <select id="child_count" name="child_count" class="form-control">
                                    <option value="">Select Children</option>
                                </select>
                            </div>

                            <!-- baby cot -->
                            <div class="col-md-3 mb-3">
                                <label for="baby_cot" class="form-label"><strong>Baby
                                        Cot</strong><span class="text-danger">*</span></label>
                                <select name="baby_cot" id="baby_cot" class="form-control"
                                    onchange="toggleBabyCotPrice()">
                                    <option value="">Select One</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>

                            <!-- baby cot price: Cost then Sell -->
                            <div class="col-md-3 mb-3 baby_cot_price" style="display: none;">
                                <label for="baby_cot_cost_price" class="form-label"><strong>Baby Cot
                                        Price(Cost)</strong><span class="text-danger">*</span></label>
                                <input type="number" name="baby_cot_cost_price" id="baby_cot_cost_price"
                                    class="form-control js-bed-cost" data-sell-target="baby_cot_price"
                                    placeholder="Enter Cost Price" min="0" step="0.01">
                            </div>
                            <div class="col-md-3 mb-3 baby_cot_price" style="display: none;">
                                <label for="baby_cot_price" class="form-label"><strong>Baby Cot
                                        Price(Sell)</strong><span class="text-danger">*</span></label>
                                <input type="number" name="baby_cot_price" id="baby_cot_price"
                                    class="form-control js-bed-sell" placeholder="Enter Sell Price" min="0" step="0.01">
                            </div>
                            <hr>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3" id="force_child_count_container" style="display: none;">
                    <label for="force_child_count" class="form-label"><strong>Force Child Count</strong></label>
                    <select class="form-control" name="force_child_count" id="force_child_count">
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="force_child" class="form-label"><strong>Force Child</strong></label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="force_child" id="force_child" value="1" onchange="toggleForceChildCount()">
                        <label class="form-check-label" for="force_child">Force Child</label>
                    </div>
                </div>

                <div class="form-check form-switch">
                    <label for="bed_status" class="form-label"><strong>Status</strong></label>
                    <span style="color: red; font-weight: bold;">*</span>
                    <input class="form-check-input" name="bed_status" type="checkbox" id="bed_status"
                        value="1">
                    <label class="form-check-label"></label>
                    @error('bed_status')
                    <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary px-4 js-submit-loader-btn">
                                    <span class="js-submit-loader-btn-text">Save</span>
                                    <span class="js-submit-loader-btn-loading d-none">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                        Saving...
                                    </span>
                                </button>
                                <!-- <a href="{{ route('policy', $hotel->hotel_unique_id) }}"
                                    class="btn btn-success px-4">Save</a> -->
                            </div>
                        </form>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="card-body">
                <div class="alert alert-info mb-0">
                    Bed configuration is added by Travclicks. You can open a bed below to update Extra Bed and Baby Cot prices.
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Beds List -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Beds of {{ $hotel->name }}</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        @if($auth_user->role_id == 1)
                        <!-- DMC Filter Dropdown for Admin -->
                        <div class="d-flex align-items-center gap-2">
                            <label for="dmcFilter" class="form-label mb-0 text-nowrap">
                                <strong><i class="ri-filter-line"></i> Filter by DMC:</strong>
                            </label>
                            <select class="form-select" id="dmcFilter" style="min-width: 220px;">
                                <option value="">All DMCs</option>
                                @foreach($dmcUsers as $dmc)
                                    <option value="{{ $dmc->userId }}">{{ $dmc->company_name }} ({{ $dmc->name }})</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

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
                                <th>Room Type</th>
                                <th>Bed Type</th>
                                @if($auth_user->role_id == 1)
                                <th>DMC</th>
                                @endif
                                <th>No. of Rooms</th>
                                <th>Max Occupancy</th>
                                <th>Extra Bed</th>
                                <th>Baby Cot</th>
                                <th>Active</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bedsData as $bed)
                            <tr data-dmc-id="{{ $bed->dmc_id ?? 'unknown' }}">
                            <td>{{ $bed->room->room_type }}</td>
                            <td>{{ $bed->room_type }}</td>
                                
                                @if($auth_user->role_id == 1)
                                <td>
                                    <span class="badge {{ $bed->dmc_id ? 'bg-primary' : 'bg-secondary' }}">
                                        {{ $bed->dmc_company ?? 'Unknown DMC' }}
                                    </span>
                                    @if($bed->dmc_id)
                                        <br><small class="text-muted">{{ $bed->dmc_name ?? '' }}</small>
                                    @endif
                                </td>
                                @endif
                                <td>{{ $bed->no_of_rooms }}</td>
                                <td>{{ $bed->max_occupancy }}</td>
                                <td>{{ $bed->extra_bed ? 'Available' : 'Not Available' }}</td>
                                <td>{{ $bed->baby_cot ? 'Available' : 'Not Available' }}</td>
                                <td>{{$bed->is_active == 1 ? 'Yes' : 'No'}}</td>
                                <td >
                                    <div style="display:flex; flex-direction:row; gap:5px">
                                        <a href="{{ route('bed.edit', ['id' => Crypt::encrypt($bed->bed_id), 'hotel_id' => $hotel->hotel_unique_id]) }}"
                                            class="btn btn-primary btn-sm d-flex align-items-center justify-content-center rounded-circle" style="width: 28px; height: 28px; padding: 0;">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="12px" fill="#ffffff">
                                                    <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/>
                                            </svg>
                                        </a>

                                        @if(!empty($canManageBedConfig))
                                        <button type="button" 
                                                class="btn btn-danger btn-sm d-flex align-items-center justify-content-center rounded-circle" 
                                                style="width: 28px; height: 28px; padding: 0;" 
                                                data-toggle="modal" 
                                                data-target="#deleteModal" 
                                                onclick="setDeleteForm('{{ route('bed.destroy', ['hotelId' => $hotel->hotel_unique_id, 'bedId' => Crypt::encrypt($bed->bed_id)]) }}')">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                                <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                            </svg>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<!-- Delete Model -->
<div class="modal fade" id="deleteModal" tabindex="-1" Category="dialog" 
        aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" Category="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmation</h5>
            </div>
            <div class="modal-body">
                Are you sure want to delete?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <form id="deleteForm" action="" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
<x-form-submit-loader message="Saving..." />
@endsection

@section('scripts')
<!-- DataTable JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<!-- Add Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- DataTables Initialization Script -->
<script>
    $(document).ready(function() {
        // Initialize Select2 for DMC selection
        $('#dmc_selection').select2({
            placeholder: "Search and Select DMC",
            allowClear: true,
            width: '100%'
        });

        // Initialize Select2 for Room Category
        $('#room_type').select2({
            placeholder: "Search and Select Room Category",
            allowClear: true,
            width: '100%'
        });

        // Initialize Select2 for Bed Type
        $('#bed_type').select2({
            placeholder: "Search and Select Bed Type",
            allowClear: true,
            width: '100%'
        });

        // Initialize Select2 for DMC Filter (Admin only)
        @if($auth_user->role_id == 1)
        $('#dmcFilter').select2({
            placeholder: "Search and Select DMC",
            allowClear: true,
            width: '220px'
        });
        @endif

        // Initialize DataTable with export buttons
        var dataTable = $('.datatables-basic').DataTable({
            responsive: true,
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
            lengthMenu: [10, 25, 50, 100], // Customize number of entries per page
        });

        // DMC Filter functionality (only for admin users)
        @if($auth_user->role_id == 1)
        
        // Custom search function for DMC filtering
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                var selectedDmc = $('#dmcFilter').val();
                var row = $(settings.nTable).DataTable().row(dataIndex).node();
                var dmcId = $(row).attr('data-dmc-id');
                
                // If no filter selected, show all
                if (selectedDmc === '') {
                    return true;
                }
                
                // Check if the row matches the selected DMC
                return dmcId === selectedDmc;
            }
        );
        
        $('#dmcFilter').on('change', function() {
            var selectedDmc = $(this).val();
            
            // Redraw the table with the new filter
            dataTable.draw();
            
            // Update the table title
            var totalRows = dataTable.data().length;
            var filteredRows = dataTable.rows({search: 'applied'}).count();
            
            if (selectedDmc !== '') {
                var dmcText = $('#dmcFilter option:selected').text();
                $('.card-title').html('Beds of {{ $hotel->name }} - ' + dmcText + ' (' + filteredRows + ' of ' + totalRows + ')');
            } else {
                $('.card-title').html('Beds of {{ $hotel->name }} (' + totalRows + ' total)');
            }
        });
        @endif

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
        
        // Initialize the force child count visibility on page load
        toggleForceChildCount();
        
        @if($auth_user->role_id == 1 || $auth_user->role_id == 20)
        // DMC Selection Change Handler
        $('#dmc_selection').on('change', function() {
            const selectedDmcId = $(this).val();
            const hotelId = $('#hotel_id').val();

            if (typeof updateCurrencyPriceNoteFromDmc === 'function') {
                updateCurrencyPriceNoteFromDmc(this);
            }
            
            if (selectedDmcId) {
                // Enable room dropdown and fetch DMC-specific rooms
                $('#room_type').prop('disabled', false);
                fetchRoomsByDmc(selectedDmcId, hotelId);
            } else {
                // Disable room dropdown and reset
                $('#room_type').prop('disabled', true)
                    .empty()
                    .append('<option value="">Select DMC First</option>');
                // Reset dependent dropdowns
                resetDependentDropdowns();
            }
        });

        // Function to fetch rooms by DMC
        function fetchRoomsByDmc(dmcId, hotelId) {
            $.ajax({
                url: `${BASE_URL}/get-rooms-by-dmc`,
                type: 'GET',
                data: {
                    dmc_id: dmcId,
                    hotel_id: hotelId
                },
                success: function(response) {
                    $('#room_type').empty().append('<option value="">Select Room Category</option>');
                    
                    if (response.length > 0) {
                        response.forEach(room => {
                            $('#room_type').append(
                                `<option value="${room.room_id}">${room.room_type}</option>`
                            );
                        });
                    } else {
                        $('#room_type').append('<option value="">No rooms available for this DMC</option>');
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching rooms:', xhr.responseText);
                    $('#room_type').empty().append('<option value="">Error loading rooms</option>');
                }
            });
        }

        // Function to reset dependent dropdowns
        function resetDependentDropdowns() {
            $('#bed_type').val('').trigger('change');
            $('#no_of_rooms')
                .prop('disabled', true)
                .val('')
                .removeAttr('max')
                .attr('placeholder', 'Select Room Category First');
            $('#no_of_rooms_hint').text('Select a room category first');
            $('#max-occupancy').val('');
            $('#adult_count').empty().append('<option value="">Select Adults</option>').prop('disabled', true);
            $('#child_count').empty().append('<option value="">Select Children</option>').prop('disabled', true);
        }

        // DMC Selection Validation for Admin and Role 20
        $('#hotelForm').on('submit', function(e) {
            const dmcSelection = $('#dmc_selection').val();
            if (!dmcSelection) {
                e.preventDefault();
                alert('Please select a DMC before submitting the form.');
                $('#dmc_selection').focus();
                return false;
            }
        });
        @endif
    });
</script>
<!-- End DataTable JS -->

<!-- Toggle Force Child Count -->
<script>
    function toggleForceChildCount() {
        const forceChildCheckbox = document.getElementById('force_child');
        const forceChildCountContainer = document.getElementById('force_child_count_container');
        if (!forceChildCheckbox || !forceChildCountContainer) {
            return;
        }
        
        if (forceChildCheckbox.checked) {
            forceChildCountContainer.style.display = 'block';
        } else {
            forceChildCountContainer.style.display = 'none';
        }
    }
</script>
<!-- End Toggle Force Child Count -->

<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<!-- clear extra bed field -->
<script>
    function onBedTypeChange() {
        const extraBed = document.getElementById('extra_bed');
        if (!extraBed) {
            return;
        }
        extraBed.value = "";
        toggleExtraBedField();
    }
    function toggleExtraBedField() {
        const extraBedSelect = document.getElementById('extra_bed');
        if (!extraBedSelect) {
            return;
        }
        const extraBedValue = extraBedSelect.value;
        console.log("Extra bed changed to:", extraBedValue);
    }
</script>
<!-- end clear extra bed field -->
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
<!-- Date Range -->
<script>
    $(document).ready(function() {
        $('#date_range').daterangepicker({
            opens: 'right', // Opens to the right of the input
            autoApply: true, // Automatically apply the selected range
            locale: {
                format: 'MM/DD/YYYY', // Format of the dates
                separator: ' - ', // Separator between start and end dates
                applyLabel: "Apply",
                cancelLabel: "Clear"
            }
        });
        $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    });
</script>
<script>
    // Function to toggle the visibility of the baby cot price fields
    const toggleBabyCotPrice = () => {
        const babyCotDropdown = document.getElementById("baby_cot");
        const babyCotPriceFields = document.querySelectorAll(`.baby_cot_price`);
        const sellInput = document.getElementById('baby_cot_price');
        const costInput = document.getElementById('baby_cot_cost_price');
        const isYes = babyCotDropdown.value === "1";

        babyCotPriceFields.forEach(function(field) {
            field.style.display = isYes ? "block" : "none";
        });
        if (sellInput) {
            sellInput.required = isYes;
            if (!isYes) sellInput.value = "";
        }
        if (costInput) {
            costInput.required = isYes;
            if (!isYes) costInput.value = "";
        }
    };

    // Attach the function to the dropdown's change event
    document.addEventListener("DOMContentLoaded", () => {
        const babyCotDropdown = document.getElementById("baby_cot");
        if (babyCotDropdown) {
            babyCotDropdown.addEventListener("change", toggleBabyCotPrice);
        }
    });

</script>
<!-- extra bed -->
<script>
    function toggleExtraBedField() {
        const extraBedSelect = document.getElementById('extra_bed');
        if (!extraBedSelect) {
            return;
        }
        const extraBedTypeDiv = document.querySelector('.extra_bed_type');
        const extraBedPriceDivs = document.querySelectorAll('.extra_bed_price');
        const typeEl = document.getElementById('extra_bed_type');
        const priceEl = document.getElementById('extra_bed_price');
        const costEl = document.getElementById('extra_bed_cost_price');
        const isYes = extraBedSelect.value === "1";

        if (extraBedTypeDiv) extraBedTypeDiv.style.display = isYes ? "block" : "none";
        extraBedPriceDivs.forEach(function(div) { div.style.display = isYes ? "block" : "none"; });
        if (typeEl) {
            typeEl.required = isYes;
            if (!isYes) typeEl.value = "";
        }
        if (priceEl) {
            priceEl.required = isYes;
            if (!isYes) priceEl.value = "";
        }
        if (costEl) {
            costEl.required = isYes;
            if (!isYes) costEl.value = "";
        }
    }
</script>

<script>
    const BASE_URL = "{{ env('APP_URL') }}";
    $(document).ready(function() {
        function clampNoOfRoomsInput() {
            const $input = $('#no_of_rooms');
            if ($input.prop('disabled')) return;

            const max = parseInt($input.attr('max'), 10);
            let value = parseInt($input.val(), 10);

            if ($input.val() === '' || isNaN(value)) {
                return;
            }
            if (value < 1) {
                $input.val(1);
                return;
            }
            if (!isNaN(max) && value > max) {
                $input.val(max);
            }
        }

        $('#no_of_rooms').on('input change blur', clampNoOfRoomsInput);

        $('#room_type').on('change', function() {
            const roomTypeId = $(this).val(); 

            if (roomTypeId) {
                $.ajax({
                    url: `${BASE_URL}/get-no-of-rooms`, 
                    type: 'GET',
                    data: {
                        room_type_id: roomTypeId
                    },
                    success: function(response) {
                        console.log('Number of Rooms:', response);
                        let maxRooms = 0;
                        (response || []).forEach(function(room) {
                            const count = parseInt(room.no_of_room, 10) || 0;
                            if (count > maxRooms) {
                                maxRooms = count;
                            }
                        });

                        const $input = $('#no_of_rooms');
                        if (maxRooms > 0) {
                            $input
                                .prop('disabled', false)
                                .attr({ min: 1, max: maxRooms })
                                .attr('placeholder', 'Enter no. of rooms (max ' + maxRooms + ')')
                                .val('');
                            $('#no_of_rooms_hint').text('Maximum allowed: ' + maxRooms);
                        } else {
                            $input
                                .prop('disabled', true)
                                .val('')
                                .removeAttr('max')
                                .attr('placeholder', 'No rooms available');
                            $('#no_of_rooms_hint').text('No rooms available for this category');
                        }
                    },
                    error: function(xhr) {
                        console.error('An error occurred:', xhr.responseText);
                        $('#no_of_rooms')
                            .prop('disabled', true)
                            .val('')
                            .removeAttr('max')
                            .attr('placeholder', 'Select Room Category First');
                        $('#no_of_rooms_hint').text('Could not load room limit');
                    }
                });
            } else {
                $('#no_of_rooms')
                    .prop('disabled', true)
                    .val('')
                    .removeAttr('max')
                    .attr('placeholder', 'Select Room Category First');
                $('#no_of_rooms_hint').text('Select a room category first');
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        let originalOccupancy = 0;
        let defaultAdultCount = 0;
        let defaultChildCount = 0;
        let hasChildWoBed = false;

        function extraBedEnabled() {
            return $('#extra_bed').val() == '1';
        }

        function currentMaxOccupancy() {
            return originalOccupancy + (extraBedEnabled() ? 1 : 0);
        }

        function maxAdultOptions() {
            const bedAdults = Math.max(0, defaultAdultCount);
            return extraBedEnabled() ? bedAdults + 1 : bedAdults;
        }

        $('#bed_type').on('change', function () {
            const selectedBedType = $(this).val();
            const hotelId = $('#hotel_id').val();
            const BASE_URL = "{{ env('APP_URL') }}";
            if (selectedBedType) {
                $.ajax({
                    url: `${BASE_URL}/get-bed-type-data`,
                    method: 'GET', 
                    data: {
                        bed_type: selectedBedType, 
                        hotel_id: hotelId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.total_count !== undefined) {
                            originalOccupancy = parseInt(response.total_count, 10) || 0;
                            defaultAdultCount = parseInt(response.adult_count, 10) || 0;
                            defaultChildCount = parseInt(response.child_count, 10) || 0;
                            hasChildWoBed = !!response.has_child_wo_bed || defaultChildCount > 0;
                            $('#max-occupancy').val(currentMaxOccupancy());
                            updateAdultChildOptions(false);
                        } else {
                            $('#max-occupancy').val('');
                            resetAdultChildOptions();
                        }
                    },
                    error: function (xhr) {
                        console.error('Error:', xhr.responseText);
                        alert('Failed to fetch maximum occupancy. Please try again.');
                    }
                });
            } else {
                $('#max-occupancy').val('');
                resetAdultChildOptions();
            }
        });

        function updateAdultChildOptions(preserveChild) {
            const adultDropdown = $('#adult_count');
            const childDropdown = $('#child_count');
            const maxOccupancy = currentMaxOccupancy();
            const maxAdults = Math.max(0, maxAdultOptions());
            const selectedAdults = extraBedEnabled() ? maxAdults : defaultAdultCount;
            const previousChild = parseInt(childDropdown.val(), 10);

            adultDropdown.empty();
            childDropdown.empty();

            if (hasChildWoBed) {
                if (maxAdults <= 1) {
                    adultDropdown.append('<option value="1" selected>1</option>');
                } else {
                    adultDropdown.append('<option value="">Select Adults</option>');
                    for (let i = 1; i <= maxAdults; i++) {
                        adultDropdown.append(`<option value="${i}">${i}</option>`);
                    }
                    adultDropdown.val(String(selectedAdults));
                }
                adultDropdown.prop('disabled', false);

                let selectedChild = 1;
                if (preserveChild && (previousChild === 0 || previousChild === 1)) {
                    selectedChild = previousChild;
                }
                childDropdown.append('<option value="0">0</option>');
                childDropdown.append('<option value="1">1</option>');
                childDropdown.val(String(selectedChild));
                childDropdown.prop('required', true);
                childDropdown.prop('disabled', false);
                adultDropdown.off('change.bedOccupancy');
                return;
            }

            adultDropdown.append('<option value="">Select Adults</option>');
            childDropdown.append('<option value="">Select Children</option>');
            for (let i = 1; i <= maxOccupancy; i++) {
                adultDropdown.append(`<option value="${i}">${i}</option>`);
            }
            adultDropdown.prop('disabled', false);
            if (defaultAdultCount > 0) {
                adultDropdown.val(String(defaultAdultCount));
            }
            updateChildOptions(maxOccupancy, adultDropdown.val(), defaultChildCount);
            adultDropdown.off('change.bedOccupancy').on('change.bedOccupancy', function () {
                updateChildOptions(maxOccupancy, $(this).val(), defaultChildCount);
            });
        }

        function updateChildOptions(maxOccupancy, selectedAdults, preferredChildCount) {
            const childDropdown = $('#child_count');
            childDropdown.empty().append('<option value="">Select Children</option>');
            const maxChildren = maxOccupancy - (parseInt(selectedAdults, 10) || 0);

            if (maxChildren >= 0) {
                for (let i = 0; i <= maxChildren; i++) {
                    childDropdown.append(`<option value="${i}">${i}</option>`);
                }
                childDropdown.prop('disabled', false);
                if (preferredChildCount !== undefined && preferredChildCount !== null) {
                    const childVal = Math.min(parseInt(preferredChildCount, 10) || 0, maxChildren);
                    childDropdown.val(String(childVal));
                }
            } else {
                childDropdown.prop('disabled', true);
            }
        }

        function resetAdultChildOptions() {
            originalOccupancy = 0;
            defaultAdultCount = 0;
            defaultChildCount = 0;
            hasChildWoBed = false;
            $('#adult_count').empty().append('<option value="">Select Adults</option>').prop('disabled', true);
            $('#child_count').empty().append('<option value="">Select Children</option>').prop('disabled', true).prop('required', false);
        }

        $('#extra_bed').on('change', function () {
            $('#max-occupancy').val(currentMaxOccupancy());
            if (originalOccupancy > 0 || defaultAdultCount > 0 || defaultChildCount > 0) {
                updateAdultChildOptions(true);
            }
        });
    });
</script>
<script>
(function () {
    function round2(n) {
        return Math.round((Number(n) + Number.EPSILON) * 100) / 100;
    }

    function calcSellFromCost(cost, type, amount) {
        const c = parseFloat(cost);
        const a = parseFloat(amount);
        const costVal = isNaN(c) ? 0 : c;
        const amtVal = isNaN(a) ? 0 : a;
        if (costVal <= 0) return 0;
        if (type === 'flat') return round2(costVal + amtVal);
        return round2(costVal + (costVal * amtVal / 100));
    }

    function getProfitSettings() {
        const typeEl = document.querySelector('.js-bed-profit-type');
        const amountEl = document.querySelector('.js-bed-profit-amount');
        return {
            type: typeEl ? typeEl.value : 'percentage',
            amount: amountEl ? amountEl.value : 0
        };
    }

    function updateSellFromCost(costEl, force) {
        if (!costEl) return;
        const sellId = costEl.getAttribute('data-sell-target');
        if (!sellId) return;
        const sellEl = document.getElementById(sellId);
        if (!sellEl) return;
        if (!force && sellEl.dataset.userEdited === '1') return;
        const g = getProfitSettings();
        sellEl.value = calcSellFromCost(costEl.value, g.type, g.amount);
        sellEl.dataset.userEdited = '';
    }

    function recalculateAll(force) {
        document.querySelectorAll('.js-bed-cost[data-sell-target]').forEach(function (costEl) {
            updateSellFromCost(costEl, force);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.js-bed-cost[data-sell-target]').forEach(function (costEl) {
            costEl.addEventListener('input', function () {
                updateSellFromCost(costEl, true);
            });
        });
        document.querySelectorAll('.js-bed-sell').forEach(function (sellEl) {
            sellEl.addEventListener('input', function () {
                sellEl.dataset.userEdited = '1';
            });
        });
        document.querySelectorAll('.js-bed-profit-type, .js-bed-profit-amount').forEach(function (el) {
            el.addEventListener('input', function () { recalculateAll(true); });
            el.addEventListener('change', function () { recalculateAll(true); });
        });
    });
})();
</script>
@include('components.currency-price-note-dmc-script')
@endsection