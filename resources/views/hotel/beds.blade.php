@extends('layouts.layout')
@section('title', 'Hotels')


@section('content')
@extends('layouts.datatablecss')
@include('hotel.tapview', ['hotel' => $hotel])
<link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
{{-- <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet"> --}}

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
</style>
<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Manage Beds for {{ $hotel->name }}</h5>
                <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </div>
            
            <!-- Navigation Tabs -->
            <div class="card-body p-0">
                <ul class="nav nav-tabs" id="bedsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="add-single-tab" data-bs-toggle="tab" data-bs-target="#add-single" 
                                type="button" role="tab" aria-controls="add-single" aria-selected="true">
                            <i class="ri-add-line me-1"></i>Add Single Bed
                        </button>
                    </li>
                    {{-- @if($auth_user->role_id == 11) <!-- Only DMC users can see bulk upload -->
                        <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bulk-upload-tab" data-bs-toggle="tab" data-bs-target="#bulk-upload" 
                                type="button" role="tab" aria-controls="bulk-upload" aria-selected="false">
                            <i class="ri-upload-cloud-2-line me-1"></i>Bulk Upload
                        </button>
                    </li>
                    @endif --}}
                </ul>
                
                <div class="tab-content" id="bedsTabContent">
                    <!-- Add Single Bed Tab -->
                    <div class="tab-pane fade show active" id="add-single" role="tabpanel" aria-labelledby="add-single-tab">
                        <div class="p-4">
            <form id="hotelForm" method="POST" action="{{ route('storebed') }}"
                enctype="multipart/form-data" class="card-body">
                @csrf
                <input type="hidden" class="form-control" name="hotel_id" id="hotel_id"
                    value="{{ $hotel->hotel_unique_id }}">
                
                @if($auth_user->role_id == 1 || $auth_user->role_id == 20)
                <!-- DMC Selection (Required for Admin and Role 20) -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <strong>Note:</strong> As an admin/manager, you must select a DMC to add rooms on their behalf.
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="dmc_selection" class="form-label"><strong>Select DMC</strong><span class="text-danger">*</span></label>
                        <select id="dmc_selection" class="form-control" name="dmc_id" required>
                            <option value="">Select DMC</option>
                            @foreach($dmcUsers as $dmc)
                                <option value="{{ $dmc->userId }}">{{ $dmc->company_name }} ({{ $dmc->name }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">You are adding rooms on behalf of the selected DMC.</small>
                    </div>
                </div>
                @endif
                
                <hr>
                <div id="hotelBedsContainer">
                    <div class="hotel-rate-form">
                        <div class="row">
                            <!-- Room Category -->
                            <div class="col-md-3 mb-3">
                                <label for="room_type" class="form-label"><strong>Room Category</strong><span
                                        class="text-danger">*</span></label>
                                        <select id="room_type" class="form-control" name="room_id" required>
                                            <option value="">Select Room Category</option>
                                            @foreach($rooms as $room)
                                                <option value="{{$room->room_id}}">{{$room->room_type}}</option>
                                            @endforeach
                                        </select>
                            </div>

                            <!-- Bed Type -->
                            <div class="col-md-3 mb-3">
                                <label for="bed_type" class="form-label"><strong>Bed Type</strong><span class="text-danger">*</span></label>
                                <select id="bed_type" class="form-control" name="bed_type" required onchange="onBedTypeChange()">
                                    <option value="">Select Room Category</option>
                                    @foreach($beds as $bed)
                                    <option value="{{$bed->bedId}}">{{$bed->name}}</option>
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
                                <label for="max-occupancy" class="form-label"><strong>Maximum Occupancy</strong></label>
                                <input type="number" id="max-occupancy" name="max_occupancy" class="form-control" readonly>
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

                            <!-- extra bed price -->
                            <div class="col-md-3 mb-3 extra_bed_price" style="display: none;">
                                <label for="extra_bed_price" class="form-label"><strong>Extra Bed
                                        Price</strong><span class="text-danger">*</span></label>
                                <input type="number" name="extra_bed_price" id="extra_bed_price"
                                    class="form-control" placeholder="Enter Price">
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
                                <button type="submit" class="btn btn-primary px-4">Save</button>
                                <!-- <a href="{{ route('policy', $hotel->hotel_unique_id) }}"
                                    class="btn btn-success px-4">Save</a> -->
                            </div>
                        </form>
                        </div>
                    </div>
                    
                    @if($auth_user->role_id == 11) {{-- Only DMC users can see bulk upload --}}
                    <!-- Bulk Upload Tab -->
                    <div class="tab-pane fade" id="bulk-upload" role="tabpanel" aria-labelledby="bulk-upload-tab">
                        <div class="p-4">
                            <div class="bulk-upload-info">
                                <h6 class="mb-3">
                                    <i class="ri-upload-cloud-2-line me-2"></i>Bulk Upload Beds
                                </h6>
                                <p class="mb-3 text-muted">
                                    Upload multiple beds at once using a CSV file. This feature allows you to quickly add 
                                    many bed configurations for this hotel in a single upload operation.
                                </p>
                                
                                <!-- Available Bed Types Info -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <h6 class="text-primary mb-2">Available Bed Types:</h6>
                                        @if($beds->count() > 0)
                                            @foreach($beds as $bedType)
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="badge bg-primary me-2">{{ $bedType->bedId }}</span>
                                                    <span class="small">{{ $bedType->name }}</span>
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-warning small">No bed types configured for this hotel</span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-info mb-2">Available Room Categories:</h6>
                                        @if($rooms->count() > 0)
                                            @foreach($rooms as $room)
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="badge bg-info me-2">{{ $room->room_id }}</span>
                                                    <span class="small">{{ $room->room_type }} ({{ $room->no_of_room }} rooms)</span>
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-warning small">No room categories configured for this hotel</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="alert alert-info mb-3">
                                    <strong>Important:</strong> 
                                    Use the exact Bed Type ID and Room Category ID numbers shown above in your CSV file. 
                                    Download the template to see the required format and field requirements.
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <a href="{{ route('beds.bulk_upload_for_hotel', $hotel->hotel_unique_id) }}" 
                                       class="bulk-upload-btn">
                                        <i class="ri-upload-cloud-2-line"></i>Go to Bulk Upload
                                    </a>
                                    <a href="{{ route('beds.template_for_hotel', $hotel->hotel_unique_id) }}" 
                                       class="btn btn-outline-primary">
                                        <i class="ri-download-cloud-2-line me-1"></i>Download Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
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
                            <label for="dmcFilter" class="form-label mb-0 text-nowrap"><strong>Filter by DMC:</strong></label>
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
                                <th>Bed Type</th>
                                <th>Room Type</th>
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
                                <td>{{ $bed->room_type }}</td>
                                <td>{{ $bed->room->room_type }}</td>
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
                                        <a href="{{ route('bed.edit', ['id' => $bed->bed_id, 'hotel_id' => $hotel->hotel_unique_id]) }}"
                                            class="btn btn-primary btn-sm d-flex align-items-center justify-content-center rounded-circle" style="width: 28px; height: 28px; padding: 0;">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="12px" fill="#ffffff">
                                                    <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/>
                                            </svg>
                                        </a>

                                        <button type="button" 
                                                class="btn btn-danger btn-sm d-flex align-items-center justify-content-center rounded-circle" 
                                                style="width: 28px; height: 28px; padding: 0;" 
                                                data-toggle="modal" 
                                                data-target="#deleteModal" 
                                                onclick="setDeleteForm('{{ route('bed.destroy', ['hotelId' => $hotel->hotel_unique_id, 'bedId' => $bed->bed_id]) }}')">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                                <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                            </svg>
                                        </button>
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
@endsection

@section('scripts')
<!-- DataTable JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
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
            $('#no_of_rooms').prop('disabled', true).empty().append('<option value="">Select Room Category First</option>');
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
        document.getElementById('extra_bed').value = "";
        toggleExtraBedField(); // Optional: Trigger extra bed field logic
    }
    function toggleExtraBedField() {
        const extraBedValue = document.getElementById('extra_bed').value;
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
    // Function to toggle the visibility of the baby cot price field
    const toggleBabyCotPrice = () => {
        const babyCotDropdown = document.getElementById("baby_cot");
        const babyCotPriceField = document.querySelector(`.baby_cot_price`);

        if (babyCotDropdown.value === "1") {
            babyCotPriceField.style.display = "block"; // Show price field if "Yes" is selected
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
    });

</script>
<!-- extra bed -->
<script>
    function toggleExtraBedField() {
        const extraBedSelect = document.getElementById('extra_bed');
        const extraBedTypeDiv = document.querySelector('.extra_bed_type');
        const extraBedPriceDiv = document.querySelector('.extra_bed_price');

        if (extraBedSelect.value === "1") {
            extraBedTypeDiv.style.display = "block";
            extraBedPriceDiv.style.display = "block";
        } else {
            extraBedTypeDiv.style.display = "none";
            extraBedPriceDiv.style.display = "none";
            document.getElementById('extra_bed_type').value = ""; // Clear the type field
            document.getElementById('extra_bed_price').value = ""; // Clear the price field
        }
    }
</script>

<script>
    const BASE_URL = "{{ env('APP_URL') }}";
    $(document).ready(function() {
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
                        $('#no_of_rooms').prop('disabled', false);
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
                    '<option value="">Select Room Category First</option>');
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        let originalOccupancy = 0;  // To keep track of the original occupancy value

        $('#bed_type').on('change', function () {
            const selectedBedType = $(this).val(); // Get the selected bed type
            const hotelId = $('#hotel_id').val();
            const BASE_URL = "{{ env('APP_URL') }}";
            if (selectedBedType) {
                $.ajax({
                    url: `${BASE_URL}/get-bed-type-data`,
                    method: 'GET', 
                    data: {
                        bed_type: selectedBedType, 
                        hotel_id: hotelId,
                        _token: '{{ csrf_token() }}' // CSRF token for security
                    },
                    success: function (response) {
                        // Append total count to the max_occupancy input field
                        if (response.total_count !== undefined) {
                            originalOccupancy = response.total_count;  // Store the original occupancy value
                            $('#max-occupancy').val(originalOccupancy);
                            updateAdultChildOptions(originalOccupancy); // Update adult and child dropdowns
                        } else {
                            $('#max-occupancy').val(''); // Clear the field if no total count
                            resetAdultChildOptions(); // Reset the options if no count
                        }
                    },
                    error: function (xhr) {
                        console.error('Error:', xhr.responseText);
                        alert('Failed to fetch maximum occupancy. Please try again.');
                    }
                });
            } else {
                // Clear the occupancy field and reset dropdowns if no valid bed type is selected
                $('#max-occupancy').val('');
                resetAdultChildOptions();
            }
        });

        // Function to update the adult and child dropdowns based on max occupancy
        function updateAdultChildOptions(maxOccupancy) {
            const adultDropdown = $('#adult_count');
            const childDropdown = $('#child_count');
            adultDropdown.empty().append('<option value="">Select Adults</option>');
            childDropdown.empty().append('<option value="">Select Children</option>');
            childDropdown.prop('disabled', true); // Disable child dropdown until adult is selected

            // Update adult options
            for (let i = 1; i <= maxOccupancy; i++) {
                adultDropdown.append(`<option value="${i}">${i}</option>`);
            }

            // Enable adult dropdown
            adultDropdown.prop('disabled', false);

            // Automatically update child dropdown based on selected adult count
            adultDropdown.on('change', function () {
                updateChildOptions(maxOccupancy, $(this).val());
            });
        }

        // Function to update child dropdown based on the selected number of adults
        function updateChildOptions(maxOccupancy, selectedAdults) {
            const childDropdown = $('#child_count');
            childDropdown.empty().append('<option value="">Select Children</option>');
            const maxChildren = maxOccupancy - selectedAdults;

            if (maxChildren >= 0) {
                for (let i = 0; i <= maxChildren; i++) {
                    childDropdown.append(`<option value="${i}">${i}</option>`);
                }
                childDropdown.prop('disabled', false); // Enable child dropdown
            } else {
                childDropdown.prop('disabled', true); // Disable child dropdown if no space
            }
        }

        // Reset adult and child options if no valid occupancy
        function resetAdultChildOptions() {
            $('#adult_count').empty().append('<option value="">Select Adults</option>').prop('disabled', true);
            $('#child_count').empty().append('<option value="">Select Children</option>').prop('disabled', true);
        }

        // Listen for changes in the "extra_bed" dropdown and adjust the occupancy accordingly
        $('#extra_bed').on('change', function () {
            const extraBed = $(this).val(); // Get the extra bed selection (1 for Yes, 0 for No)
            let currentOccupancy = originalOccupancy;  // Start with the original occupancy value

            // If extra bed is selected as 'Yes', add 1 to the occupancy
            if (extraBed == '1') {
                currentOccupancy += 1;
            }

            // If extra bed is selected as 'No', revert to the original occupancy
            if (extraBed == '0') {
                currentOccupancy = originalOccupancy;  // Revert to the original value
            }

            // Update max-occupancy field with the new value
            $('#max-occupancy').val(currentOccupancy);

            // Update the adult and child options based on the new max occupancy
            updateAdultChildOptions(currentOccupancy);
        });
    });
</script>
@endsection