@extends('layouts.layout')
@section('title', 'Hotels')

{{-- @section('script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@endsection
@section('css')

<link href="{{ URL::asset('build/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
@endsection --}}

@section('content')
@extends('layouts.datatablecss')
@include('hotel.tapview', ['hotel' => $hotel])
<link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
<!-- Add Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">

<style>
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

/* Select2 Custom Styles */
.select2-container .select2-selection--single {
    height: 50px !important;
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
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
            Add Seasons
                <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            
            @if($auth_user->role_id == 11)
            <!-- Tab Navigation for DMC Users -->
            <div class="card-body pb-0">
                <ul class="nav nav-tabs nav-fill" role="tablist">
                    <li class="nav-item">
                        <button
                            type="button"
                            class="nav-link active"
                            role="tab"
                            data-bs-toggle="tab"
                            data-bs-target="#single-season"
                            aria-controls="single-season"
                            aria-selected="true">
                            <i class="tf-icons bx bx-calendar-plus"></i>
                            Add Single Season
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            type="button"
                            class="nav-link"
                            role="tab"
                            data-bs-toggle="tab"
                            data-bs-target="#bulk-upload"
                            aria-controls="bulk-upload"
                            aria-selected="false">
                            <i class="tf-icons bx bx-upload"></i>
                            Bulk Upload
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="tab-content">
                <!-- Single Season Tab -->
                <div class="tab-pane fade show active" id="single-season" role="tabpanel">
            @else
            <!-- No tabs for non-DMC users -->
            <div>
            @endif
            
            <form id="hotelForm" method="POST" action="{{ route('storeseason') }}" enctype="multipart/form-data" class="card-body">
               @csrf
               <input type="hidden" class="form-control" name="hotel_id" value="{{ $hotel->hotel_unique_id }}">
               <input type="hidden" class="form-control" name="room_id" value="{{ $room->room_id }}">
               
               @if($auth_user->role_id == 1 || $auth_user->role_id == 20)
               <!-- DMC Selection (Required for Admin and Role 20) -->
               <div class="row mb-3">
                   <div class="col-md-12">
                    <div style="
                    background: #e8f4ff;
                    border-left: 5px solid #0d6efd;
                    padding: 12px 15px;
                    border-radius: 4px;
                    font-size: 15px;
                    color: #084298;
                    margin-bottom: 15px;
                ">
                    <strong>Note:</strong> As an admin/manager, you must select a DMC to add seasons on their behalf.
                </div>
                   </div>
               </div>
               <div class="row mb-3">
                   <div class="col-md-6">
                       <label for="dmc_selection" class="form-label">
                           <strong><i class="ri-building-line"></i> Select DMC</strong><span class="text-danger">*</span>
                       </label>
                       <select id="dmc_selection" class="form-control" name="dmc_id" required>
                           <option value="">Select DMC</option>
                           @foreach($dmcUsers as $dmc)
                               <option value="{{ $dmc->userId }}">{{ $dmc->company_name }} ({{ $dmc->name }})</option>
                           @endforeach
                       </select>
                       <small class="text-muted">
                           <i class="ri-information-line"></i> You are adding seasons on behalf of the selected DMC.
                       </small>
                   </div>
               </div>
               @endif
               
               <hr>
               <div id="hotelRatesContainer">
                  <div class="hotel-rate-form">
                     <div class="row">
                        <!-- Season Name -->
                        <div class="col-md-3 mb-3">
                           <label for="event" class="form-label"><strong>Season Name</strong><span class="text-danger">*</span></label>
                           <input type="text" class="form-control" name="event" placeholder="Enter Event Name" required>
                           @error('hotel_owner_company_name')
                              <div class="text-danger mt-1">{{ $message }}</div>
                           @enderror
                        </div>
                        <input name="event_type" type="hidden" value="Season">
                     

                        <!-- Single Weekday -->
                        <div class="mb-3 col-md-3" id="base_weekday_price">
                           <label for="weekday_price" class="form-label">
                              <strong>Single Base Weekday Price</strong>
                              <span class="text-danger">*</span>
                              <sup>
                                    <button type="button" 
                                          class="info-button" 
                                          data-bs-toggle="tooltip" 
                                          data-bs-placement="top" 
                                          title="Price applicable on weekdays."
                                          style="border: none;">
                                       <i class="bi bi-info-circle"></i>
                                    </button>
                                    </sup>
                           </label>
                           <input type="number" name="weekday_price" class="form-control" placeholder="Enter Base weekday price">
                           @error('hotel_owner_company_name')
                              <div class="text-danger mt-1">{{ $message }}</div>
                           @enderror
                        </div>

                        <!-- Single Weekend Price -->
                        <div class="mb-3 col-md-3" id="base_weekend_price">
                           <label for="weekend_price" class="form-label"><strong>Single Base Weekend Price</strong><span class="text-danger">*</span>
                              <sup>
                                 <button type="button" 
                                    class="info-button" 
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top" 
                                    title="Price applicable on weekend."
                                    style="border: none;">
                                    <i class="bi bi-info-circle"></i>
                                 </button>
                              </sup>
                           </label>
                              <input type="number" name="weekend_price" class="form-control" placeholder="Enter Base weekend price">
                              @error('hotel_owner_company_name')
                                 <div class="text-danger mt-1">{{ $message }}</div>
                              @enderror
                        </div>

                        <!-- Double Weekday -->
                        <div class="mb-3 col-md-3" id="base_weekday_price">
                           <label for="weekday_price" class="form-label">
                              <strong>Double Base Weekday Price</strong>
                              <span class="text-danger">*</span>
                              <sup>
                                    <button type="button" 
                                          class="info-button" 
                                          data-bs-toggle="tooltip" 
                                          data-bs-placement="top" 
                                          title="Price applicable on weekdays."
                                          style="border: none;">
                                       <i class="bi bi-info-circle"></i>
                                    </button>
                                    </sup>
                           </label>
                           <input type="number" name="double_weekday_price" class="form-control" placeholder="Enter Base weekday price">
                           @error('hotel_owner_company_name')
                              <div class="text-danger mt-1">{{ $message }}</div>
                           @enderror
                        </div>

                        <!-- Double Weekend Price -->
                        <div class="mb-3 col-md-3" id="base_weekend_price">
                           <label for="weekend_price" class="form-label"><strong>Double Base Weekend Price</strong><span class="text-danger">*</span>
                              <sup>
                                 <button type="button" 
                                    class="info-button" 
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top" 
                                    title="Price applicable on weekend."
                                    style="border: none;">
                                    <i class="bi bi-info-circle"></i>
                                 </button>
                              </sup>
                           </label>
                              <input type="number" name="double_weekend_price" class="form-control" placeholder="Enter Base weekend price">
                              @error('hotel_owner_company_name')
                                 <div class="text-danger mt-1">{{ $message }}</div>
                              @enderror
                        </div>

                        <!-- Start Date, End date DateRange -->
                           <div class="mb-3 col-md-3">
                              <label for="date_range" class="form-label"><strong>Season Date Range</strong><span class="text-danger">*</span></label>
                              <div class="input-group">
                                    <input type="text" id="date_range" name="date_range" class="form-control"
                                       placeholder="Select date range">
                                    @error('hotel_owner_company_name')
                                       <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                              </div>
                           </div>
                     </div>
                  </div>
               </div>
               
                <!-- Status -->
               <div class="form-check form-switch">
                  <label for="season_status" class="form-label"><strong>Status</strong></label>
                  <span style="color: red; font-weight: bold;">*</span>
                  <input class="form-check-input" name="season_status" type="checkbox" id="season_status" value="1">
                  <label class="form-check-label"></label>
                  @error('season_status')
                     <div class="text-danger mt-1">{{ $message }}</div>
                  @enderror
               </div>

               <!-- Submit Buttons -->
               <div class="d-flex gap-3">
                  <button type="submit" class="btn btn-primary px-4">Save</button>
               </div>
            </form>
            
            @if($auth_user->role_id == 11)
                </div>
                <!-- End Single Season Tab -->
                
                <!-- Bulk Upload Tab -->
                <div class="tab-pane fade" id="bulk-upload" role="tabpanel">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div>
                                        <h4 class="mb-1">Bulk Upload Seasons</h4>
                                        <p class="text-muted mb-0">Upload multiple seasons at once using CSV file</p>
                                    </div>
                                    <a href="{{ route('seasons.bulk_upload_for_hotel', $hotel->hotel_unique_id) }}" 
                                       class="btn btn-primary">
                                        <i class="bx bx-upload me-1"></i>
                                        Go to Bulk Upload
                                    </a>
                                </div>
                                
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="card border-success shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="d-flex align-items-center justify-content-center me-3" 
                                                         style="width: 70px; height: 70px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="white" viewBox="0 0 16 16">
                                                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h5 class="mb-1">Download Template</h5>
                                                        <p class="text-muted mb-2">Get the CSV template with sample data and proper formatting</p>
                                                        <a href="{{ route('seasons.template_for_hotel', $hotel->hotel_unique_id) }}" 
                                                           class="btn btn-success btn-sm">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                                                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                                                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                                            </svg>
                                                            Download CSV Template
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="card border-primary shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="d-flex align-items-center justify-content-center me-3" 
                                                         style="width: 70px; height: 70px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="white" viewBox="0 0 16 16">
                                                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                                            <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h5 class="mb-1">Upload Seasons</h5>
                                                        <p class="text-muted mb-2">Upload your filled CSV file with season data and pricing</p>
                                                        <a href="{{ route('seasons.bulk_upload_for_hotel', $hotel->hotel_unique_id) }}" 
                                                           class="btn btn-primary btn-sm">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                                                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                                                <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
                                                            </svg>
                                                            Start Bulk Upload
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <h6 class="alert-heading mb-2">
                                                <i class="bx bx-info-circle me-2"></i>
                                                Bulk Upload Benefits
                                            </h6>
                                            <ul class="mb-0">
                                                <li><strong>Time Saving:</strong> Upload up to 100 seasons at once</li>
                                                <li><strong>Validation:</strong> Automatic overlap detection and data validation</li>
                                                <li><strong>Error Reporting:</strong> Detailed error messages for quick fixes</li>
                                                <li><strong>Upload History:</strong> Track all your bulk upload activities</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Bulk Upload Tab -->
            </div>
            <!-- End Tab Content -->
            @else
            </div>
            <!-- End Non-DMC Content -->
            @endif
      </div>
   </div>
</div>

      <!-- Rates List -->
<div class="content-wrapper">
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="card">
         <div class="card-datatable table-responsive pt-0">
            <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
               <div class="d-flex align-items-center">
                  <h5 class="card-title mb-0">Seasons of {{ $hotel->name }}</h5>
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
               <thead class="">
                  <tr>
                     <th>Start Date</th>
                     <th>End Date</th>
                     <th>Event Name</th>
                     <th>Event Type</th>
                     @if($auth_user->role_id == 1)
                     <th>DMC</th>
                     @endif
                     <th>Weekday Price</th>
                     <th>Weekend Price</th>
                     <th>Active</th>
                     <th>Action</th>
                  </tr>
               </thead>
               <tbody>
                  @php
                     use Carbon\Carbon;
                  @endphp
                  @foreach ($rates as $rate)
                  <tr data-dmc-id="{{ $rate->dmc_id ?? 'unknown' }}">
                     <td class="category-name">{{ \App\Helpers\CommonHelper::DateFormatAdmin($rate->start_date) }}</td>
                     <td class="category-name">{{ \App\Helpers\CommonHelper::DateFormatAdmin($rate->end_date) }}</td>
                     <td>{{ $rate->event }}</td>
                     <td>{{ $rate->event_type }}</td>
                     @if($auth_user->role_id == 1)
                     <td>
                        <span class="badge {{ $rate->dmc_id ? 'bg-primary' : 'bg-secondary' }}">
                           {{ $rate->dmc_company ?? 'Unknown DMC' }}
                        </span>
                        @if($rate->dmc_id)
                           <br><small class="text-muted">{{ $rate->dmc_name ?? '' }}</small>
                        @endif
                     </td>
                     @endif
                     <td>{{ $rate->weekday_price }}</td>
                     <td>{{ $rate->weekend_price }}</td>
                     <td>{{$rate->is_active == 1 ? 'Yes' : 'No'}}</td>

                     <td class="gap-2">
                        <!-- Edit Button -->
                        <a href="{{ route('season.edit', ['id' => $rate->rate_id, 'hotel_id' => $hotel->hotel_unique_id]) }}"
                           class="btn btn-primary btn-sm rounded-circle waves-effect waves-light"
                           style="min-width: 28px; min-height: 28px; padding: 0;">
                           <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960"
                                 width="16px" fill="#ffffff">
                                 <path
                                    d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z">
                                 </path>
                           </svg>
                        </a>

                        <!-- Delete Button -->
                        <button type="button"
                           class="btn btn-danger btn-sm rounded-circle waves-effect waves-light"
                           style="min-width: 28px; min-height: 28px; padding: 0;" data-toggle="modal"
                           data-target="#deleteModal"
                           onclick="setDeleteForm('{{ route('season.destroy', ['id' => $rate->rate_id, 'hotel_id' => $hotel->hotel_unique_id]) }}')"
                           fdprocessedid="ra9z3">
                           <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960"
                                 width="16px" fill="#ffffff">
                                 <path
                                    d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z">
                                 </path>
                           </svg>
                        </button>
                     </td>
                  </tr>
                  @endforeach
               </tbody>
            </table>
            <!-- Guide Delete Modal -->
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
         </div>
      </div>
   </div>
</div>
@endsection
@section('scripts')  

<!-- DataTable JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<!-- Add Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- DataTables Initialization Script -->
<script>
    $(document).ready(function() {
        // Initialize Select2 for DMC Filter
        @if($auth_user->role_id == 1)
        $('#dmcFilter').select2({
            placeholder: "Search and Select DMC",
            allowClear: true,
            width: '220px'
        });
        @endif

        // Initialize Select2 for DMC Selection in Form
        @if($auth_user->role_id == 1 || $auth_user->role_id == 20)
        $('#dmc_selection').select2({
            placeholder: "Search and Select DMC",
            allowClear: true,
            width: '100%'
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
                $('.card-title').html('Seasons of {{ $hotel->name }} - ' + dmcText + ' (' + filteredRows + ' of ' + totalRows + ')');
            } else {
                $('.card-title').html('Seasons of {{ $hotel->name }} (' + totalRows + ' total)');
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
        
        @if($auth_user->role_id == 1 || $auth_user->role_id == 20)
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

</script>
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<!-- DataTable Scripts -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<!-- Info Button -->

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

</script>

<script>
    function setDeleteForm(action) {
        document.getElementById('deleteForm').action = action;
    }
</script>

<!-- Date Range -->
<script>
      $(document).ready(function () {
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

{{-- <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Select the event type dropdown
        const eventTypeDropdown = document.querySelector('select[name="event_type"]');
        const priceField = document.getElementById('price');
        const weekdayPriceField = document.getElementById('base_weekday_price');
        const weekendPriceField = document.getElementById('base_weekend_price');

        // Function to toggle field visibility
        function toggleFields() {
            const selectedEventType = eventTypeDropdown.value;
            
            if (selectedEventType === "Season") {
                // Show weekday and weekend price
                weekdayPriceField.style.display = "block";
                weekendPriceField.style.display = "block";

                // Hide the price input field
                priceField.style.display = "none";
            } else {
                // Show price input field
                priceField.style.display = "block";

                // Hide weekday and weekend price
                weekdayPriceField.style.display = "none";
                weekendPriceField.style.display = "none";
            }
        }

        // Add event listener to the dropdown
         eventTypeDropdown.onchange = function() {
            toggleFields(); // Call toggleFields on change
         };

    });
</script> --}}


@endsection
