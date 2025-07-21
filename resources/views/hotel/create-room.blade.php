@extends('layouts.layout')
@section('content')
@include('hotel.tapview', ['hotel' => $hotel])
@extends('layouts.datatablecss')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<!-- Add DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">

<style>
/* DataTable Styles */
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

.btn-label-primary {
    background-color: #e7e7ff;
    color: #696cff;
    border: none;
}

.btn-label-primary:hover {
    background-color: #696cff;
    color: #fff;
}

.btn-label-danger {
    background-color: #ffe7e7;
    color: #ff3e1d;
    border: none;
}

.btn-label-danger:hover {
    background-color: #ff3e1d;
    color: #fff;
}

/* Badge Styles */
.badge.bg-label-success {
    background-color: #e8fadf !important;
    color: #71dd37 !important;
}

.badge.bg-label-danger {
    background-color: #ffe7e7 !important;
    color: #ff3e1d !important;
}

/* Image Upload Styles */
.drop-area {
    border: 2px dashed #696cff;
    border-radius: 0.375rem;
    padding: 20px;
    text-align: center;
    background-color: #f8f9fa;
    cursor: pointer;
    transition: all 0.3s ease;
}

.drop-area.highlight {
    background-color: #e7e7ff;
    border-color: #696cff;
}

.preview-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}

.preview-container img {
    max-width: 100px;
    height: auto;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Form Field Styles */
.form-floating>.form-control {
    padding-top: 1.625rem;
    padding-bottom: 0.625rem;
}

.form-floating>label {
    padding: 1rem 0.75rem;
}

fieldset {
    border-radius: 0.375rem;
    border-color: #d9dee3;
}

fieldset legend {
    font-size: 0.875rem;
    font-weight: 600;
    color: #566a7f;
    width: auto;
    padding: 0 0.5rem;
    margin-bottom: 0;
}

/* Validation Messages */
.validation-message {
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

/* Modal Styles */
.modal-content {
    border-radius: 0.5rem;
}

.modal-header {
    border-bottom: 1px solid #d9dee3;
    padding: 1.5rem;
}

.modal-footer {
    border-top: 1px solid #d9dee3;
    padding: 1.5rem;
}

/* Toggle Switch Styles for Data Table */
.table .form-check.form-switch {
    margin: 0;
    padding-left: 2.5em;
}

.table .form-check-input {
    height: 1.5em;
    width: 2.75em;
    cursor: pointer;
}

.table .form-check-label {
    margin-left: 0.5em;
    font-size: 0.875rem;
    cursor: pointer;
    vertical-align: middle;
}

/* Spinner and icon styles */
.table .spinner-border-sm {
    width: 1rem;
    height: 1rem;
    vertical-align: middle;
}

.table td .text-success,
.table td .text-danger {
    font-size: 0.875rem;
}

/* Calculation Display Styles */
.calculation-display {
    font-size: 0.8rem;
    font-weight: 500;
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 0.25rem 0.5rem;
    margin-top: 0.25rem;
    font-family: monospace;
    text-align: center;
    color: #495057;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.calculation-display i {
    margin-right: 0.25rem;
    color: #6c757d;
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
.badge.bg-info {
    background-color: #54a3ff !important;
}

.badge.bg-primary {
    background-color: #696cff !important;
}

/* Filter Info Text */
.filter-info {
    font-size: 0.875rem;
    color: #6c757d;
    font-style: italic;
}
</style>

<!-- Start of the form - Only for Admin and Virtual DMC -->
@if(in_array($auth_user->role_id, [1, 20]))
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Add New Room Category
                <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
                <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form id="roomCategoryForm" method="POST" action="{{ route('storeroom') }}" enctype="multipart/form-data"
                class="card-body">
                @csrf
                <input type="hidden" name="hotel_id" value="{{ $hotel->hotel_unique_id }}">
                
                <div class="mb-3 row">
                    <!-- Base Room Category -->
                    <div class="col-md-3 mb-3" id="base_room_type">
                        <label for="base_room_type_input" class="form-label"><strong>Base Room
                                Category</strong><span class="text-danger">*</span></label>
                        <input id="base_room_type_input" type="text" name="base_room_type" class="form-control"
                            placeholder="Enter Base Room Category" required>
                        @error('base_room_type')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Room Category -->
                    <div class="col-md-3 mb-3" id="room_type" style="display: none;">
                        <label for="room_type_input" class="form-label"><strong>Room Category</strong><span
                                class="text-danger">*</span></label>
                        <input id="room_type_input" name="room_type" class="form-control" placeholder="Enter Room Category">
                        <div class="form-text">Enter name for this room variant (e.g. Deluxe, Premium)</div>
                        @error('room_type')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- varient_price -->
                    <div class="col-md-3 mb-3" id="varient_price" style="display: none;">
                        <label for="varient_price_input" class="form-label"><strong>Room Rate
                                Variant</strong><span class="text-danger">*</span></label>
                        <input name="varient_price" id="varient_price_input" class="form-control" type="number" step="0.01"
                            placeholder="Enter Variant Price (e.g. 1 for +1, -5 for -5)">
                        <div class="form-text">Price difference from base room (Example: Base 1 + Variant 1 = Final 2)</div>
                        @error('varient_price')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Number of Rooms -->
                    <div class="col-md-3 mb-3">
                        <label for="total_rooms" class="form-label"><strong>Total No of Rooms</strong><span
                                class="text-danger">*</span></label>
                <input type="text" class="form-control" name="total_no_of_room" id="total_rooms"
                    placeholder="Enter Number of Rooms" oninput="validateTotalRooms(this)">
                        <small class="validation-message text-danger" id="total_rooms-validation-message"></small>
                        @error('total_no_of_room')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- dimension -->
                    <div class="mb-3 col-md-3" id="dimension">
                        <label for="dimension_input" class="form-label"><strong>Dimension(sq.m)</strong></label>
                        <input type="number" name="dimension" id="dimension_input" class="form-control"
                    placeholder="Enter Dimension">
                        <small class="validation-message text-danger" id="dimension_input-validation-message"></small>
                    </div>

                    <!-- Children Price -->
                    <div class="mb-3 col-md-3">
                        <label for="children_price" class="form-label"><strong>Meal Children
                                Price</strong></label>
                        <select name="children_price" id="children_price" class="form-control">
                            <option value="">Please Select One</option>
                            <option value="0">Free</option>
                            <option value="1">Half Price</option>
                            <option value="2">Full Price</option>
                        </select>
                    </div>

                    <!-- Single weekday weekend price -->
                    <div class="col-md-6" id="single_price" style="display: none;">
                        <div class="mb-3">
                            <fieldset class="border p-1 position-relative">
                                <legend>Single</legend>
                                <div class="row g-2">
                                    <div class="col-md-6 form-floating">
                                        <input type="text" id="singleWeekdayPrice" name="singleWeekdayPrice"
                                            class="form-control" placeholder=" " onkeyup="calculatePrice()">
                                        <label for="singleWeekdayPrice">Weekday Price</label>
                                        @if($auth_user->user_type == 2)
                                <span class="text-primary">Your calculated price: <span
                                        id="totalSingleWeekdayPrice">0</span></span>
                                        @endif
                                        <div class="calculation-display text-primary small mt-1" id="single-weekday-calc" style="display: none;"></div>
                                    </div>
                                    <div class="col-md-6 form-floating">
                                        <input type="text" id="singleWeekendPrice" name="singleWeekendPrice"
                                            class="form-control" placeholder=" " onkeyup="calculatePrice()">
                                        <label for="singleWeekendPrice">Weekend Price</label>
                                        @if($auth_user->user_type == 2)
                                <span class="text-primary">Your calculated price: <span
                                        id="totalSingleWeekendPrice">0</span></span>
                                        @endif
                                        <div class="calculation-display text-primary small mt-1" id="single-weekend-calc" style="display: none;"></div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    <!-- Double weekday weekend price -->
                    <div class="col-md-6" id="double_price" style="display: none;">
                        <div class="mb-3">
                            <fieldset class="border p-1 position-relative">
                                <legend>Double</legend>
                                <div class="row g-2">
                                    <div class="col-md-6 form-floating">
                                        <input type="text" id="doubleWeekdayPrice" name="doubleWeekdayPrice"
                                            class="form-control" placeholder=" " onkeyup="calculatePrice()">
                                        <label for="doubleWeekdayPrice">Weekday Price</label>
                                        @if($auth_user->user_type == 2)
                                <span class="text-primary">Your calculated price: <span
                                        id="totalDoubleWeekdayPrice">0</span></span>
                                        @endif
                                        <div class="calculation-display text-primary small mt-1" id="double-weekday-calc" style="display: none;"></div>
                                    </div>
                                    <div class="col-md-6 form-floating">
                                        <input type="text" id="doubleWeekendPrice" name="doubleWeekendPrice"
                                            class="form-control" placeholder=" " onkeyup="calculatePrice()">
                                        <label for="doubleWeekendPrice">Weekend Price</label>
                                        @if($auth_user->user_type == 2)
                                <span class="text-primary">Your calculated price: <span
                                        id="totalDoubleWeekendPrice">0</span></span>
                                        @endif
                                        <div class="calculation-display text-primary small mt-1" id="double-weekend-calc" style="display: none;"></div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    <!-- Base Single weekday weekend -->
                    <div class="col-md-6" id="base_single_price" style="display: none;">
                        <!-- First Row -->
                        <div class="mb-3">
                            <fieldset class="border p-1 position-relative">
                                <legend>Single</legend>
                                <div class="row g-2">
                                    <div class="col-md-6 form-floating">
                                <input type="text" id="weekdayPrice" name="baseSingleWeekdayPrice" class="form-control"
                                    placeholder=" " onkeyup="calculatePrice()">
                                        <label for="weekdayPrice">Base Weekday Price</label>
                                        @if($auth_user->user_type == 2)
                                <span class="text-primary">Your calculated price: <span
                                        id="totalWeekdayPrice">0</span></span>
                                        @endif
                                    </div>
                                    <div class="col-md-6 form-floating">
                                <input type="text" id="weekendPrice" name="baseSingleWeekendPrice" class="form-control"
                                    placeholder=" " onkeyup="calculatePrice()">
                                        <label for="weekendPrice">Base Weekend Price</label>
                                        @if($auth_user->user_type == 2)
                                <span class="text-primary">Your calculated price: <span
                                        id="totalWeekendPrice">0</span></span>
                                        @endif
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    <!--  Base Double weekday weekend -->
                    <div class="col-md-6" id="base_double_price" style="display: none;">
                        <div class="mb-3">
                            <fieldset class="border p-1 position-relative">
                                <legend>Double</legend>
                                <div class="row g-2">
                                    <!-- Weekday Price -->
                                    <div class="col-md-6 form-floating">
                                        <input type="text" id="doubleweekdayPrice" name="baseDoubleWeekdayPrice"
                                            class="form-control" placeholder=" " onkeyup="calculatePrice()">
                                        <label for="doubleweekdayPrice">Base Weekday Price</label>
                                        @if($auth_user->user_type == 2)
                                <span class="text-primary">Your calculated price: <span
                                        id="totalBaseDoubleWeekdayPrice">0</span></span>
                                        @endif
                                    </div>
                                    
                                    <!-- Weekend Price -->
                                    <div class="col-md-6 form-floating">
                                        <input type="text" id="doubleweekendPrice" name="baseDoubleWeekendPrice"
                                            class="form-control" placeholder=" " onkeyup="calculatePrice()">
                                        <label for="doubleweekendPrice">Base Weekend Price</label>
                                        @if($auth_user->user_type == 2)
                                <span class="text-primary">Your calculated price: <span
                                        id="totalBaseDoubleWeekendPrice">0</span></span>
                                        @endif
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </div>

                <!-- Meal Options Section -->
                <div class="mb-3 row">
                    <!-- Breakfast Toggle -->
                    <div class="col-md-3 mb-3">
                        <label for="breakfast_included" class="form-label"><strong>Breakfast Included</strong></label>
                        <select name="breakfast_included" id="breakfast_included" class="form-control" onchange="toggleMealOptions('breakfast')">
                            <option value="">Select One</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    
                    <!-- Breakfast Type - Shows when breakfast is included -->
                    <div class="col-md-3 mb-3 breakfast-options" style="display: none;">
                        <label for="breakfast_type" class="form-label"><strong>Breakfast Type</strong><span class="text-danger">*</span></label>
                        <select name="breakfast_type" id="breakfast_type" class="form-control">
                            <option value="">Select Type</option>
                            <option value="Buffet">Buffet</option>
                            <option value="Set Menu">Set Menu</option>
                        </select>
                    </div>
                    
                    <!-- Breakfast Price - Shows when breakfast is included -->
                    <div class="col-md-3 mb-3 breakfast-options" style="display: none;">
                        <label for="breakfast_price" class="form-label"><strong>Breakfast Price</strong><span class="text-danger">*</span></label>
                        <input type="number" name="breakfast_price" id="breakfast_price" class="form-control" placeholder="Enter Price" min="0" step="0.01">
                    </div>
                    
                    <!-- Lunch Toggle -->
                    <div class="col-md-3 mb-3">
                        <label for="lunch_included" class="form-label"><strong>Lunch Included</strong></label>
                        <select name="lunch_included" id="lunch_included" class="form-control" onchange="toggleMealOptions('lunch')">
                            <option value="">Select One</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    
                    <!-- Lunch Type - Shows when lunch is included -->
                    <div class="col-md-3 mb-3 lunch-options" style="display: none;">
                        <label for="lunch_type" class="form-label"><strong>Lunch Type</strong><span class="text-danger">*</span></label>
                        <select name="lunch_type" id="lunch_type" class="form-control">
                            <option value="">Select Type</option>
                            <option value="Buffet">Buffet</option>
                            <option value="Set Menu">Set Menu</option>
                        </select>
                    </div>
                    
                    <!-- Lunch Price - Shows when lunch is included -->
                    <div class="col-md-3 mb-3 lunch-options" style="display: none;">
                        <label for="lunch_price" class="form-label"><strong>Lunch Price</strong><span class="text-danger">*</span></label>
                        <input type="number" name="lunch_price" id="lunch_price" class="form-control" placeholder="Enter Price" min="0" step="0.01">
                    </div>
                    
                    <!-- Dinner Toggle -->
                    <div class="col-md-3 mb-3">
                        <label for="dinner_included" class="form-label"><strong>Dinner Included</strong></label>
                        <select name="dinner_included" id="dinner_included" class="form-control" onchange="toggleMealOptions('dinner')">
                            <option value="">Select One</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    
                    <!-- Dinner Type - Shows when dinner is included -->
                    <div class="col-md-3 mb-3 dinner-options" style="display: none;">
                        <label for="dinner_type" class="form-label"><strong>Dinner Type</strong><span class="text-danger">*</span></label>
                        <select name="dinner_type" id="dinner_type" class="form-control">
                            <option value="">Select Type</option>
                            <option value="Buffet">Buffet</option>
                            <option value="Set Menu">Set Menu</option>
                        </select>
                    </div>
                    
                    <!-- Dinner Price - Shows when dinner is included -->
                    <div class="col-md-3 mb-3 dinner-options" style="display: none;">
                        <label for="dinner_price" class="form-label"><strong>Dinner Price</strong><span class="text-danger">*</span></label>
                        <input type="number" name="dinner_price" id="dinner_price" class="form-control" placeholder="Enter Price" min="0" step="0.01">
                    </div>
                    
                    <!-- Supplementary Breakfast Toggle -->
                    <div class="col-md-3 mb-3">
                        <label for="supplementary_breakfast" class="form-label"><strong>Supplementary Breakfast Included</strong></label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="supplementary_breakfast" id="supplementary_breakfast" value="1">
                            <label class="form-check-label" for="supplementary_breakfast">Enable Supplementary Breakfast</label>
                        </div>
                    </div>
                </div>

                <div class="row col-md-12">
                    <!-- Master image -->
                    <div class="mt-3 mb-3 col-md-4">
                        <div>
                            <label for="master_image" class="form-label"><strong>Master
                        Image</strong><span style="color: red; font-weight: bold;">*</span></label>
                <div id="master-drop-area" class="drop-area">
                                Drag & Drop your files here or click to upload.
                    <input type="file" id="master_image" name="master_image" style="display: none;" required>
                            </div>
                            <div id="image-warning" class="text-danger mt-1" style="display: none;">
                                Please upload an image before submitting.
                            </div>
                        </div>
            <div id="master-preview-container" class="preview-container mt-3"></div>
                    </div>

                    <!-- Additional Image drop -->
                    <div class="mt-3 mb-3 col-md-8">
                        <div>
                            <label for="images" class="form-label"><strong>Additional
                                    Images</strong></label>
                <div id="drop-area" class="drop-area">
                                Drag & Drop your files here or click to upload.
                    <input type="file" id="images" name="images[]" multiple style="display: none;">
                            </div>
                <div id="preview-container" class="preview-container mt-3"></div>
                        </div>
                        <!-- Existing Image Section -->
                        <div class="image-preview-container d-flex flex-wrap gap-2">
                        </div>
                        <input type="file" name="all_images[]" id="all-images" multiple style="display: none;">
                        @error('images')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Status -->
                <div class="form-check form-switch">
                    <label for="room_status" class="form-label"><strong>Status</strong></label>
                    <span style="color: red; font-weight: bold;">*</span>
        <input class="form-check-input" name="room_status" type="checkbox" id="room_status" value="1">
                    <label class="form-check-label"></label>
                    @error('room_status')
                    <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <!-- Submit Buttons -->
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary px-4">Save and Add More Rooms</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
<!-- End of the form -->

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Hotel Rooms</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        @if($auth_user->role_id == 1)
                        <!-- DMC Filter Dropdown for Admin -->
                        <div class="d-flex align-items-center gap-2">
                            <label for="dmcFilter" class="form-label mb-0 text-nowrap"><strong>Filter by DMC:</strong></label>
                            <select class="form-select" id="dmcFilter" style="min-width: 220px;">
                                <option value="">All DMCs</option>
                                <option value="admin">Admin Base Rooms</option>
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
                            <th>No</th>
                            <th>Hotel</th>
                            <th>Brand</th>
                            @if($auth_user->role_id == 1)
                            <th>DMC</th>
                            @endif
                            <th>Room Category</th>
                            <th>No of Rooms</th>
                            <th>Base Room Type</th>
                            <th>Rooms Only Off</th>

                            <th>Single Weekdays Price</th>
                            <th>Single Weekend Price</th>
                            <th>Double Weekdays Price</th>
                            <th>Double Weekend Price</th>

                            <th>Status</th>
                            {{-- @if(hasPermission('edit room') || hasPermission('delete room')) --}}
                                <th>Action</th>
                            {{-- @endif --}}
                        </tr>
                    </thead>
                    <tbody>
                            @foreach ($rooms as $key => $room)
                            <tr data-dmc-id="{{ $room->dmc_id ?? 'admin' }}">
                                <td>{{ ++$key }}</td>
                                <td>
                                    <a href="{{ route('hotel_details', ['hotel' => $room->hotel->hotel_unique_id]) }}"
                                        target="_blank">
                                        {{ $room->hotel->name ?? 'Unknown Hotel' }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('hotel_brand_details', ['brand' => $room->hotel->hotel_unique_id]) }}"
                                        target="_blank">
                                        {{ $room->hotel->hotel_owner_company_name ?? 'Unknown Owner' }}
                                    </a>
                                </td>
                                @if($auth_user->role_id == 1)
                                <td>
                                    <span class="badge {{ $room->dmc_id == 'admin' ? 'bg-info' : 'bg-primary' }}">
                                        {{ $room->dmc_company ?? 'Admin Base Room' }}
                                    </span>
                                    @if($room->dmc_id != 'admin')
                                        <br><small class="text-muted">{{ $room->dmc_name ?? '' }}</small>
                                    @endif
                                </td>
                                @endif
                                <td>{{ $room->room_type }}</td>
                                <td>{{ $room->no_of_room }}</td>
                                <td>
                                    <div class="form-check form-switch d-flex align-items-center">
                                        <input class="form-check-input toggle-base-room" 
                                               type="checkbox" 
                                               id="baseRoomToggle{{ $room->room_id }}" 
                                               data-room-id="{{ $room->room_id }}" 
                                               style="width: 2.00em !important;"
                                               {{ $room->base_room ? 'checked' : '' }}>
                                        <label class="form-check-label ms-2" for="baseRoomToggle{{ $room->room_id }}">
                                            {{ $room->base_room ? 'Yes' : 'No' }}
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check form-switch d-flex align-items-center">
                                        <input class="form-check-input toggle-rooms-only" 
                                               type="checkbox" 
                                               id="roomsOnlyToggle{{ $room->room_id }}" 
                                               data-room-id="{{ $room->room_id }}" 
                                               style="width: 2.00em !important;"
                                               {{ $room->rooms_only ?? false ? 'checked' : '' }}>
                                        <label class="form-check-label ms-2" for="roomsOnlyToggle{{ $room->room_id }}">
                                            {{ $room->rooms_only ?? false ? 'Yes' : 'No' }}
                                        </label>
                                    </div>
                                </td>
                                <td>{{ $room->weekday_price }}</td>
                                <td>{{ $room->weekend_price }}</td>
                                <td>{{ $room->double_weekday_price }}</td>
                                <td>{{ $room->double_weekend_price }}</td>
                                <td>
                                    @if($room->status == 1)
                                        <span class="badge bg-success">Available</span>
                                    @else
                                        <span class="badge bg-danger">Not Available</span>
                                    @endif
                                </td>
                                {{-- @if(hasPermission('edit room') || hasPermission('delete room')) --}}
                                <td>
                                    <div class="d-flex gap-2">
                                    <!-- Edit Button -->
                                    {{-- @if(hasPermission('edit room')) --}}
                                    <a href="{{ route('rooms.edit', $room->room_id) }}" 
                                        class="btn btn-primary btn-sm rounded-circle" 
                                        style="width: 28px; height: 28px; padding: 0;">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960"
                                                width="16px" fill="#ffffff">
                                                <path
                                                    d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z" />
                                        </svg>
                                    </a>
                                    {{-- @endif --}}
                                
                                    <!-- Delete Button -->
                                    {{-- @if(hasPermission('delete room')) --}}
                                    <button type="button" 
                                            class="btn btn-danger btn-sm rounded-circle" 
                                            style="width: 28px; height: 28px; padding: 0;" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal" 
                                            onclick="setDeleteForm('/deleteroom/' + '{{ $room->room_id }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                            <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                        </svg>
                                    </button>
                                    {{-- @endif --}}
                                    </div>
                                </td>
                                {{-- @endif --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this room?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Add DataTables JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<!-- DataTables Initialization Script -->
<script>
    $(document).ready(function() {
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
                var dmcText = selectedDmc === 'admin' ? 'Admin Base Rooms' : $('#dmcFilter option:selected').text();
                $('.card-title').html('Hotel Rooms - ' + dmcText + ' (' + filteredRows + ' of ' + totalRows + ')');
            } else {
                $('.card-title').html('Hotel Rooms (' + totalRows + ' total)');
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
    });
</script>

<script>
    $(document).ready(function() {
        $('#example').DataTable();
    });

    $(document).ready(function() {
        var table = $('#example2').DataTable({
            order: [[4, 'desc']], // Adjust column index based on where `created_at` would logically be
            lengthChange: false,
            buttons: ['copy', 'excel', 'pdf', 'print']
        });

        table.buttons().container()
        .appendTo('#example2_wrapper .col-md-6:eq(0)');
    });

    function setDeleteForm(action) {
        document.getElementById('deleteForm').action = action;
    }
</script>

<script>
$(document).ready(function() {
    const hotelId = "{{ $hotel->hotel_unique_id }}";
    const allRooms = @json($rooms);
    console.log("Current hotel ID:", hotelId);
    console.log("All rooms:", allRooms);
    
    const hotelRooms = allRooms.filter(room => {
        console.log(`Comparing room.hotel_id (${room.hotel_id}) with hotelId (${hotelId})`);
        return room.hotel_id === hotelId;
    });
    const hasRooms = hotelRooms.length > 0;
    
    console.log("Filtered hotel rooms:", hotelRooms);

    // Store prices for calculations
    const standardPrices = {
        singleWeekday: 0,
        singleWeekend: 0,
        doubleWeekday: 0,
        doubleWeekend: 0
    };

    // Price calculation for DMC users
    function calculatePrice() {
        const commission_type = {{ $commission_type }};
        const commission_price = {{ $commission_price }};

        function calculate(price) {
            if (!price) return 0;
            price = parseFloat(price);
            if (commission_type === 1) { // Percentage
                return price + (price * commission_price / 100);
            } else if (commission_type === 2) { // Fixed
                return price + commission_price;
            }
            return price;
        }

        // Calculate for all price inputs
        const priceInputs = {
            'singleWeekdayPrice': 'totalSingleWeekdayPrice',
            'singleWeekendPrice': 'totalSingleWeekendPrice',
            'doubleWeekdayPrice': 'totalDoubleWeekdayPrice',
            'doubleWeekendPrice': 'totalDoubleWeekendPrice',
            'weekdayPrice': 'totalWeekdayPrice',
            'weekendPrice': 'totalWeekendPrice',
            'doubleweekdayPrice': 'totalBaseDoubleWeekdayPrice',
            'doubleweekendPrice': 'totalBaseDoubleWeekendPrice'
        };

        Object.entries(priceInputs).forEach(([inputId, totalId]) => {
            const price = $(`#${inputId}`).val();
            const calculatedPrice = calculate(price);
            $(`#${totalId}`).text(calculatedPrice.toFixed(2));
        });
    }

    // Attach the calculate function to price input changes
    $('input[type="text"]').on('input', calculatePrice);
    
    // Also trigger calculation when variant price changes
    $('#varient_price_input').on('input change keyup blur', function() {
        console.log('Variant price input changed:', $(this).val());
        updateVariantPrices.call(this);
    });

    // Image Upload Functionality
    function initializeImageUpload(dropAreaId, inputId, previewContainerId, isMaster = false) {
        const dropArea = document.getElementById(dropAreaId);
        const fileInput = document.getElementById(inputId);
        const previewContainer = document.getElementById(previewContainerId);

        if (!dropArea || !fileInput || !previewContainer) {
            console.error("Missing element", {
                dropArea,
                fileInput,
                previewContainer
            });
            return;
        }

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        // Highlight drop zone when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });

        // Handle dropped files
        dropArea.addEventListener('drop', handleDrop, false);

        // Handle click to upload
        dropArea.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', function(e) {
            handleFiles(e.target.files);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function highlight(e) {
            dropArea.classList.add('highlight');
        }

        function unhighlight(e) {
            dropArea.classList.remove('highlight');
        }

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }

        function handleFiles(files) {
            if (files.length === 0) return;
            
            if (isMaster) {
                // For master image, only show the first file
                previewFile(files[0]);
                
                // Create a new FileList containing only the first file
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(files[0]);
                const masterImageElement = document.getElementById('master_image');
                if (masterImageElement) {
                    masterImageElement.files = dataTransfer.files;
                }
                
                // Hide warning
                const imageWarning = document.getElementById('image-warning');
                if (imageWarning) {
                    imageWarning.style.display = 'none';
                }
            } else {
                // For additional images, show all files
                previewContainer.innerHTML = ''; // Clear previous previews
                
                // Create a new FileList for all images
                const dataTransfer = new DataTransfer();
                
                [...files].forEach(file => {
                    previewFile(file);
                    dataTransfer.items.add(file);
                });

                // Set the files to the all_images input
                const allImagesElement = document.getElementById('all-images');
                if (allImagesElement) {
                    allImagesElement.files = dataTransfer.files;
                }
            }
        }

        function previewFile(file) {
            if (file.type.startsWith('image/')) {
                let reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onloadend = function() {
                    let img = document.createElement('img');
                    img.src = reader.result;
                    img.style.maxWidth = '100px';
                    img.style.height = 'auto';

                    let container = document.createElement('div');
                    container.className = 'position-relative d-inline-block';
                    container.appendChild(img);

                    // Add remove button
                    let removeBtn = document.createElement('button');
                    removeBtn.innerHTML = '×';
                    removeBtn.className = 'btn btn-danger btn-sm position-absolute top-0 end-0';
                    removeBtn.style.padding = '0 6px';
                    removeBtn.onclick = function(e) {
                        e.preventDefault();
                        container.remove();
                        
                        // Remove file from input when remove button is clicked
                        if (isMaster) {
                            // Clear master image
                            const masterImageElement = document.getElementById('master_image');
                            if (masterImageElement) {
                                masterImageElement.value = '';
                            }
                            
                            const imageWarning = document.getElementById('image-warning');
                            if (imageWarning) {
                                imageWarning.style.display = 'block';
                            }
        } else {
                            // Remove this file from all_images
                            removeFileFromInput('all-images', file);
                        }
                    };
                    container.appendChild(removeBtn);

                    if (isMaster) {
                        previewContainer.innerHTML = ''; // Clear previous preview for master image
                    }
                    previewContainer.appendChild(container);
                }
            }
        }
        
        // Helper function to remove a specific file from an input
        function removeFileFromInput(inputId, fileToRemove) {
            const input = document.getElementById(inputId);
            if (!input || input.files.length === 0) return;
            
            const dataTransfer = new DataTransfer();
            
            for (let i = 0; i < input.files.length; i++) {
                const file = input.files[i];
                if (file !== fileToRemove) {
                    dataTransfer.items.add(file);
                }
            }
            
            input.files = dataTransfer.files;
        }
    }

    // Initialize both drop zones if elements exist
    if (document.getElementById('master-drop-area') && 
        document.getElementById('master_image') &&
        document.getElementById('master-preview-container')) {
        initializeImageUpload('master-drop-area', 'master_image', 'master-preview-container', true);
    }
    
    if (document.getElementById('drop-area') && 
        document.getElementById('images') &&
        document.getElementById('preview-container')) {
        initializeImageUpload('drop-area', 'images', 'preview-container', false);
    }

    // Auto-populate and set up form fields based on existing rooms
    function setupFormFields() {
        // Filter rooms to just this hotel
        const currentHotelRooms = allRooms.filter(room => room.hotel_id === hotelId);
        const hasRoomsForHotel = currentHotelRooms.length > 0;
        
        // Find base room if it exists
        let baseRoom = null;
        if (hasRoomsForHotel) {
            console.log("Looking for base room in:", currentHotelRooms);
            currentHotelRooms.forEach(room => {
                console.log(`Room ${room.room_type}: base_room = ${room.base_room} (type: ${typeof room.base_room})`);
            });
            
            // Try both numeric and boolean comparison
            baseRoom = currentHotelRooms.find(room => room.base_room === 1 || room.base_room === true || room.base_room === "1");
        }
        
        const hasBaseRoom = baseRoom !== null;
        
        console.log("Hotel rooms for this hotel:", currentHotelRooms);
        console.log("Base room found:", baseRoom);
        console.log("Has base room:", hasBaseRoom);
        
        if (!hasRoomsForHotel) {
            // No rooms exist for this hotel - show base room fields
            $('#base_room_type_input').val('').prop('readonly', false);
            $('#room_type, #varient_price').hide();
            $('#single_price, #double_price').hide();
            $('#base_single_price, #base_double_price').show();
            
            // Make sure the base_room_type input is required
            $('#base_room_type_input').prop('required', true);
            
            // Show message indicating this will be the base room
            $('<div class="alert alert-info mb-3">This will be the base room for price calculations.</div>')
                .insertBefore('#base_room_type');
        } else if (hasBaseRoom) {
            // Base room exists, allow variant room creation
            $('#base_room_type').hide();
            $('#room_type, #varient_price').show();
            $('#single_price, #double_price').show().css('display', 'block');
            $('#base_single_price, #base_double_price').hide();
            
            console.log('Showing variant room sections - Single visible:', $('#single_price').is(':visible'), 'Double visible:', $('#double_price').is(':visible'));

            // Remove required from hidden field
            $('#base_room_type_input').prop('required', false);
            
            // Set room type field as required
            $('input[name="room_type"]').attr('required', true);

            // Extract base room prices and display them
            if (baseRoom) {
                // Extract and display base room prices
                standardPrices.singleWeekday = parseFloat(baseRoom.weekday_price) || 0;
                standardPrices.singleWeekend = parseFloat(baseRoom.weekend_price) || 0;
                standardPrices.doubleWeekday = parseFloat(baseRoom.double_weekday_price) || 0;
                standardPrices.doubleWeekend = parseFloat(baseRoom.double_weekend_price) || 0;
                
                // Set base room prices as default values in variant room form fields
                $('#singleWeekdayPrice').val(standardPrices.singleWeekday.toFixed(2));
                $('#singleWeekendPrice').val(standardPrices.singleWeekend.toFixed(2));
                $('#doubleWeekdayPrice').val(standardPrices.doubleWeekday.toFixed(2));
                $('#doubleWeekendPrice').val(standardPrices.doubleWeekend.toFixed(2));

                // Add helper text showing base room prices with variant calculation for form fields
                $('#singleWeekdayPrice').after(
                    `<div class="form-text text-info base-price-info" data-base="${standardPrices.singleWeekday.toFixed(2)}">Base price: ${standardPrices.singleWeekday.toFixed(2)}</div>`
                );
                $('#singleWeekendPrice').after(
                    `<div class="form-text text-info base-price-info" data-base="${standardPrices.singleWeekend.toFixed(2)}">Base price: ${standardPrices.singleWeekend.toFixed(2)}</div>`
                );
                $('#doubleWeekdayPrice').after(
                    `<div class="form-text text-info base-price-info" data-base="${standardPrices.doubleWeekday.toFixed(2)}">Base price: ${standardPrices.doubleWeekday.toFixed(2)}</div>`
                );
                $('#doubleWeekendPrice').after(
                    `<div class="form-text text-info base-price-info" data-base="${standardPrices.doubleWeekend.toFixed(2)}">Base price: ${standardPrices.doubleWeekend.toFixed(2)}</div>`
                );

                // Calculation display elements are now added directly in HTML
                
                // Make price fields read-only initially to show they're calculated from base + variant
                $('#singleWeekdayPrice, #singleWeekendPrice, #doubleWeekdayPrice, #doubleWeekendPrice').prop('readonly', true);
                $('#singleWeekdayPrice, #singleWeekendPrice, #doubleWeekdayPrice, #doubleWeekendPrice').addClass('bg-light').css('cursor', 'not-allowed');
                
                // Add note that prices are auto-calculated
                $('#single_price').before('<div class="alert alert-info mb-3"><i class="fas fa-info-circle"></i> Prices are automatically calculated based on base room prices + variant price</div>');
            }

            // Add a change handler for variant price to automatically update all price fields
            $('#varient_price_input').on('input', updateVariantPrices);
            
            // Initialize with current variant price if any exists
            const currentVariantPrice = $('#varient_price_input').val();
            if (currentVariantPrice) {
                setTimeout(function() {
                    updateVariantPrices.call($('#varient_price_input')[0]);
                }, 200);
            }
            
            // Test the calculation
            console.log('Base room setup complete. Standard prices:', standardPrices);
        } else {
            // Rooms exist but no base room, create a base room first
            $('#base_room_type_input').val('').prop('readonly', false);
            $('#room_type, #varient_price').hide();
            $('#single_price, #double_price').hide();
            $('#base_single_price, #base_double_price').show();
            
            // Make sure the base_room_type input is required
            $('#base_room_type_input').prop('required', true);
            
            // Show an alert or message that base room must be created first
            $('<div class="alert alert-warning mb-3">You must create a base room first before adding other room types.</div>')
                .insertBefore('#base_room_type');
        }

        // Calculate prices if DMC user
        calculatePrice();
    }

    // Function to update prices based on variant value
    function updateVariantPrices() {
        const variantPrice = parseFloat($(this).val()) || 0;
        
        console.log('Variant Price:', variantPrice);
        console.log('Standard Prices:', standardPrices);
        
        // Show price calculation explanation for variant price field
        if (variantPrice !== 0) {
            const operation = variantPrice > 0 ? "+" : "";
            const message = `Variant: ${operation}${variantPrice.toFixed(2)}`;
            
            if (!$('#variant_calculation_info').length) {
                $('#varient_price_input').after(`<div id="variant_calculation_info" class="form-text text-primary">${message}</div>`);
            } else {
                $('#variant_calculation_info').text(message);
            }
        } else {
            $('#variant_calculation_info').remove();
        }

        // Calculate and update all variant price fields: Base Price + Variant Price = Final Price
        const newSingleWeekdayPrice = standardPrices.singleWeekday + variantPrice;
        const newSingleWeekendPrice = standardPrices.singleWeekend + variantPrice;
        const newDoubleWeekdayPrice = standardPrices.doubleWeekday + variantPrice;
        const newDoubleWeekendPrice = standardPrices.doubleWeekend + variantPrice;
        
        $('#singleWeekdayPrice').val(newSingleWeekdayPrice.toFixed(2));
        $('#singleWeekendPrice').val(newSingleWeekendPrice.toFixed(2));
        $('#doubleWeekdayPrice').val(newDoubleWeekdayPrice.toFixed(2));
        $('#doubleWeekendPrice').val(newDoubleWeekendPrice.toFixed(2));

        console.log('Updated Prices:', {
            singleWeekday: newSingleWeekdayPrice,
            singleWeekend: newSingleWeekendPrice,
            doubleWeekday: newDoubleWeekdayPrice,
            doubleWeekend: newDoubleWeekendPrice
        });

        // Update base price info for each field to show calculation
        $('.base-price-info').each(function() {
            const basePrice = parseFloat($(this).data('base'));
            
            if (variantPrice !== 0) {
                const operation = variantPrice >= 0 ? "+" : "";
                const totalPrice = (basePrice + variantPrice).toFixed(2);
                $(this).html(`Base price ${basePrice.toFixed(2)} ${operation}${variantPrice.toFixed(2)} = ${totalPrice}`);
                $(this).removeClass('text-info').addClass('text-primary');
            } else {
                $(this).html(`Base price: ${basePrice.toFixed(2)}`);
                $(this).removeClass('text-primary').addClass('text-info');
            }
        });

        // Update calculation display in Single and Double sections
        if (variantPrice !== 0) {
            const operation = variantPrice >= 0 ? "+" : "";
            const calc1 = `<i class="fas fa-calculator"></i> ${standardPrices.singleWeekday.toFixed(2)} ${operation}${variantPrice.toFixed(2)} = ${newSingleWeekdayPrice.toFixed(2)}`;
            const calc2 = `<i class="fas fa-calculator"></i> ${standardPrices.singleWeekend.toFixed(2)} ${operation}${variantPrice.toFixed(2)} = ${newSingleWeekendPrice.toFixed(2)}`;
            const calc3 = `<i class="fas fa-calculator"></i> ${standardPrices.doubleWeekday.toFixed(2)} ${operation}${variantPrice.toFixed(2)} = ${newDoubleWeekdayPrice.toFixed(2)}`;
            const calc4 = `<i class="fas fa-calculator"></i> ${standardPrices.doubleWeekend.toFixed(2)} ${operation}${variantPrice.toFixed(2)} = ${newDoubleWeekendPrice.toFixed(2)}`;
            
            $('#single-weekday-calc').html(calc1).show();
            $('#single-weekend-calc').html(calc2).show();
            $('#double-weekday-calc').html(calc3).show();
            $('#double-weekend-calc').html(calc4).show();
            
            console.log('Updated calculation displays:', {calc1, calc2, calc3, calc4});
        } else {
            $('.calculation-display').hide();
        }

        // Recalculate DMC prices
        calculatePrice();
    }

    // Make room status checked by default
    $('#room_status').prop('checked', true);

    // Run setup on page load
    setupFormFields();

    // Form validation before submission
    $('#roomCategoryForm').on('submit', function(e) {
        let isValid = true;
        
        // Validate required fields depending on room type
        const isBaseRoomInputVisible = $('#base_room_type').is(':visible');
        
        if (isBaseRoomInputVisible) {
            if (!$('#base_room_type_input').val().trim()) {
                $('#base_room_type_input').addClass('is-invalid');
                isValid = false;
            }
            
            if (!$('#weekdayPrice').val().trim()) {
                $('#weekdayPrice').addClass('is-invalid');
                isValid = false;
            }
            
            if (!$('#weekendPrice').val().trim()) {
                $('#weekendPrice').addClass('is-invalid');
                isValid = false;
            }
        } else {
            if (!$('#room_type_input').val().trim()) {
                $('#room_type_input').addClass('is-invalid');
                isValid = false;
            }
            
            if (!$('#varient_price_input').val().trim()) {
                $('#varient_price_input').addClass('is-invalid');
                isValid = false;
            }
        }
        
        // For all rooms, require total rooms
        if (!$('#total_rooms').val().trim()) {
            $('#total_rooms').addClass('is-invalid');
            isValid = false;
        }
    });
});

// Delete modal functionality
function setDeleteForm(action) {
    // Make sure the form is set to the correct route
    console.log("Setting delete form action to: " + action);
    document.getElementById('deleteForm').action = action;
    
    // Display the modal
    var deleteModal = document.getElementById('deleteModal');
    if (typeof bootstrap !== 'undefined') {
        var bsModal = new bootstrap.Modal(deleteModal);
        bsModal.show();
            } else {
        // Fallback for Bootstrap 4
        $('#deleteModal').modal('show');
            }
 }
</script>

<script>
    function showValidationMessage(inputElement, isValid, message) {
        const messageElement = document.getElementById(`${inputElement.id}-validation-message`);
        
        if (!messageElement) return;
        
        if (isValid) {
            messageElement.innerHTML = `
                <div class="valid-feedback d-block">
                    <i class="fas fa-check-circle text-success"></i> 
                    Looks good!
                </div>`;
            inputElement.classList.remove('is-invalid');
            inputElement.classList.add('is-valid');
        } else {
            messageElement.innerHTML = `
                <div class="invalid-feedback d-block">
                    <i class="fas fa-exclamation-circle"></i> 
                    ${message}
                </div>`;
            inputElement.classList.remove('is-valid');
            inputElement.classList.add('is-invalid');
        }
    }

    function validateTotalRooms(input) {
        // Force numeric input by immediately replacing non-numeric characters
        input.value = input.value.replace(/[^0-9]/g, '');
        
        const value = input.value.trim();
        const roomsRegex = /^[1-9][0-9]{0,3}$/;  // 1-9999 rooms
        
        if (value === '') {
            showValidationMessage(input, false, 'Total number of rooms is required');
        } else if (!roomsRegex.test(value)) {
            showValidationMessage(input, false, `
                Please enter a valid number of rooms:
                <ul class="mt-1 mb-0">
                    <li>Must be a positive number (1-9999)</li>
                    <li>No decimal places allowed</li>
                    <li>No leading zeros</li>
                </ul>
            `);
        } else {
            showValidationMessage(input, true, '');
        }
    }

    // function validateDimension(input) {
    //     // Allow only digits, 'x', '*', and spaces
    //     input.value = input.value.replace(/[^0-9x*\s]/g, '');
        
    //     // Format to standard format: replace all * with x and normalize spacing
    //     let value = input.value.trim().replace(/\*/g, 'x');
        
    //     // Replace multiple spaces with a single space
    //     value = value.replace(/\s+/g, ' ');
        
    //     // Ensure only one 'x' separator
    //     if ((value.match(/x/g) || []).length > 1) {
    //         const parts = value.split('x');
    //         value = parts[0] + 'x' + parts.slice(1).join('');
    //     }
        
    //     // Update the input value with the formatted value
    //     input.value = value;
        
    //     // Validate the format: number x number
    //     const dimensionRegex = /^[1-9][0-9]{0,2}(\s*[x]\s*)[1-9][0-9]{0,2}$/;
        
    //     if (value === '') {
    //         // Since dimension is optional, don't show error if empty
    //         input.classList.remove('is-invalid');
    //         input.classList.remove('is-valid');
    //         const messageElement = document.getElementById(`${input.id}-validation-message`);
    //         if (messageElement) messageElement.innerHTML = '';
    //     } else if (!dimensionRegex.test(value)) {
    //         showValidationMessage(input, false, `
    //             Please enter a valid dimension:
    //             <ul class="mt-1 mb-0">
    //                 <li>Format: length x width (e.g., 12x10)</li>
    //                 <li>Use 'x' as separator</li>
    //                 <li>Both length and width must be positive numbers (1-999)</li>
    //             </ul>
    //         `);
    //     } else {
    //         showValidationMessage(input, true, '');
    //     }
    // }

    // Add CSS for validation messages if not already included
    document.head.insertAdjacentHTML('beforeend', `
        <style>
            /* Base validation message styles */
            .validation-message {
                margin-top: 0.5rem;
                font-size: 0.85rem;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            }

            /* Error state styles */
            .validation-message .invalid-feedback {
                display: block;
                color: #e74c3c;
                background-color: #fef5f5;
                border-left: 3px solid #e74c3c;
                padding: 0.75rem 1rem;
                border-radius: 4px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                animation: slideIn 0.3s ease-in-out;
            }

            /* Success state styles */
            .validation-message .valid-feedback {
                display: block;
                color: #2ecc71;
                background-color: #f4fff6;
                border-left: 3px solid #2ecc71;
                padding: 0.75rem 1rem;
                border-radius: 4px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                animation: slideIn 0.3s ease-in-out;
            }

            /* List styles within validation messages */
            .validation-message ul {
                margin: 0.5rem 0 0 0;
                padding-left: 1.5rem;
                list-style-type: none;
            }

            .validation-message ul li {
                position: relative;
                padding: 0.2rem 0;
                color: #666;
            }

            .validation-message ul li::before {
                content: "•";
                color: #e74c3c;
                font-weight: bold;
                position: absolute;
                left: -1rem;
            }

            /* Icon styles */
            .validation-message i {
                margin-right: 0.5rem;
                font-size: 1rem;
            }

            /* Input field styles */
            .is-invalid {
                border-color: #e74c3c !important;
                background-color: #fff !important;
            }

            .is-valid {
                border-color: #2ecc71 !important;
                background-color: #fff !important;
            }

            /* Animation for validation messages */
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Hover effect for validation messages */
            .validation-message .invalid-feedback:hover,
            .validation-message .valid-feedback:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
                transition: all 0.3s ease;
            }

            /* Required field indicator */
            .required-field::after {
                content: "*";
                color: #e74c3c;
                margin-left: 4px;
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {
                .validation-message {
                    font-size: 0.8rem;
                }
                
                .validation-message .invalid-feedback,
                .validation-message .valid-feedback {
                    padding: 0.5rem 0.75rem;
                }
            }

            /* Focus state styles */
            .form-control:focus {
                box-shadow: 0 0 0 0.2rem rgba(46, 204, 113, 0.25);
                border-color: #2ecc71;
            }

            .form-control.is-invalid:focus {
                box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25);
                border-color: #e74c3c;
            }
        </style>
    `);
</script>

<script>
    $(document).ready(function() {
    // Base Room Type Toggle Handler
    $('.toggle-base-room').on('change', function() {
        const roomId = $(this).data('room-id');
        const isBaseRoom = $(this).prop('checked');
        const label = $(this).siblings('label');
        
        // Update label text
        label.text(isBaseRoom ? 'Yes' : 'No');
        
        // Show loading indicator
        const originalHtml = label.html();
        label.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');
        
        // Send AJAX request
        $.ajax({
            url: '{{ route("rooms.update-base-room") }}',
            type: 'POST',
            data: {
                room_id: roomId,
                base_room: isBaseRoom ? 1 : 0,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Show success indicator
                    label.html('<i class="fas fa-check-circle text-success"></i> ' + (isBaseRoom ? 'Yes' : 'No'));
                    
                    // Reload the page after a short delay
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    // Show error and revert the toggle
                    label.html('<i class="fas fa-times-circle text-danger"></i> Error');
                    $(this).prop('checked', !isBaseRoom);
                    
                    // Revert to normal label after 2 seconds
                    setTimeout(function() {
                        label.text(!isBaseRoom ? 'Yes' : 'No');
                    }, 2000);
                    
                    console.error('Failed to update base room status:', response.message);
                }
            },
            error: function(xhr) {
                // Show error and revert the toggle
                label.html('<i class="fas fa-times-circle text-danger"></i> Error');
                $(this).prop('checked', !isBaseRoom);
                
                // Revert to normal label after 2 seconds
                setTimeout(function() {
                    label.text(!isBaseRoom ? 'Yes' : 'No');
                }, 2000);
                
                console.error('Failed to update base room status:', xhr.responseText);
            }
        });
        });
    
    // Rooms Only Toggle Handler
    $('.toggle-rooms-only').on('change', function() {
        const roomId = $(this).data('room-id');
        const isRoomsOnly = $(this).prop('checked');
        const label = $(this).siblings('label');
        
        // Update label text
        label.text(isRoomsOnly ? 'Yes' : 'No');
        
        // Show loading indicator
        const originalHtml = label.html();
        label.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');
        
        // Send AJAX request
        $.ajax({
            url: '{{ route("rooms.update-rooms-only") }}',
            type: 'POST',
            data: {
                room_id: roomId,
                rooms_only: isRoomsOnly ? 1 : 0,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Show success indicator
                    label.html('<i class="fas fa-check-circle text-success"></i> ' + (isRoomsOnly ? 'Yes' : 'No'));
                    
                    // Revert to normal label after 2 seconds
                    setTimeout(function() {
                        label.text(isRoomsOnly ? 'Yes' : 'No');
                    }, 2000);
                } else {
                    // Show error and revert the toggle
                    label.html('<i class="fas fa-times-circle text-danger"></i> Error');
                    $(this).prop('checked', !isRoomsOnly);
                    
                    // Revert to normal label after 2 seconds
                    setTimeout(function() {
                        label.text(!isRoomsOnly ? 'Yes' : 'No');
                    }, 2000);
                    
                    console.error('Failed to update rooms only status:', response.message);
                }
            },
            error: function(xhr) {
                // Show error and revert the toggle
                label.html('<i class="fas fa-times-circle text-danger"></i> Error');
                $(this).prop('checked', !isRoomsOnly);
                
                // Revert to normal label after 2 seconds
                setTimeout(function() {
                    label.text(!isRoomsOnly ? 'Yes' : 'No');
                }, 2000);
                
                console.error('Failed to update rooms only status:', xhr.responseText);
            }
        });
    });
    });
</script>

<script>
    // Function to handle meal option toggles
    function toggleMealOptions(mealType) {
        const isIncluded = document.getElementById(`${mealType}_included`).value === "1";
        const optionElements = document.querySelectorAll(`.${mealType}-options`);
        
        optionElements.forEach(element => {
            if (isIncluded) {
                element.style.display = "block";
                
                // Set required attribute for inputs when visible
                const typeInput = document.getElementById(`${mealType}_type`);
                const priceInput = document.getElementById(`${mealType}_price`);
                
                if (typeInput) typeInput.required = true;
                if (priceInput) priceInput.required = true;
            } else {
                element.style.display = "none";
                
                // Remove required attribute when hidden
                const typeInput = document.getElementById(`${mealType}_type`);
                const priceInput = document.getElementById(`${mealType}_price`);
                
                if (typeInput) typeInput.required = false;
                if (priceInput) priceInput.required = false;
            }
        });
    }
    
    // Initialize meal options on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleMealOptions('breakfast');
        toggleMealOptions('lunch');
        toggleMealOptions('dinner');
        
        // Validate price inputs to prevent negative values
        document.querySelectorAll('input[type="number"][id$="_price"]').forEach(input => {
            input.addEventListener('input', function() {
                if (parseFloat(this.value) < 0) {
                    this.value = 0;
                }
            });
            
            input.addEventListener('blur', function() {
                if (this.value === '' || parseFloat(this.value) < 0) {
                    this.value = 0;
                }
            });
        });
    });
</script>

@endsection