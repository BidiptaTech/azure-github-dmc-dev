@extends('layouts.layout')
@section('title', 'Hotels')
@section('css')
<link href="{{ URL::asset('build/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
@endsection

@section('content')
@include('hotel.tapview', ['hotel' => $hotel])
<link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
<div class="content-wrapper">
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="card mb-6">
         <h5 class="card-header d-flex justify-content-between align-items-center">
            Edit Events Details
            <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
               <i class="mdi mdi-arrow-left"></i> Back
            </a>
         </h5>
         <form id="hotelForm" method="POST" action="{{ route('rates.update') }}" enctype="multipart/form-data" class="card-body">
            @csrf
            <input value="{{$rate->rate_id}}" type="text" class="form-control" name="rate_id" hidden>
            <input value="{{$hotel->hotel_unique_id}}" type="text" class="form-control" name="hotel_id" hidden>

            <hr>
            <div id="hotelRatesContainer">
               <div class="hotel-rate-form">
                  <div class="row">
                     <!-- Event Name -->
                     <div class="col-md-3 mb-3">
                        <label for="event" class="form-label"><strong>Event Name</strong><span class="text-danger">*</span></label>
                        <input value="{{$rate->event}}" type="text" class="form-control" name="event" placeholder="Enter Event Name" required>
                     </div>
                     <!-- Event Type -->
                     <div class="col-md-3 mb-3">
                        <label for="event_type" class="form-label"><strong>Event Type</strong><span class="text-danger">*</span></label>
                        <select class="form-control" name="event_type" required>
                           <option value="">Select Event Type</option>
                           <option value="Fair Date" {{ $rate->event_type == "Fair Date" ? 'selected' : '' }}>Fair Date</option>
                           <option value="Blackout Date" {{ $rate->event_type == "Blackout Date" ? 'selected' : '' }}>Blackout Date</option>
                           <option value="Season" {{ $rate->event_type == "Season" ? 'selected' : '' }}>Season</option>
                        </select>
                     </div>
                     
                     <!-- Price -->
                     <div class="col-md-3 mb-3" id="price">
                        <label for="price" class="form-label"><strong>Price</strong></label><span class="text-danger">*</span>
                        <input value="{{$rate->price}}" type="number" class="form-control" name="price" placeholder="Enter Price" required>
                     </div>

                        <!-- Weekday -->
                     <div class="mb-3 col-md-3" id="base_weekday_price" style="display: none;">
                        <label for="weekday_price" class="form-label"><strong>Base Weekday Price</strong></label>
                        <input value="{{$rate->weekday_price}}" type="number" name="weekday_price" class="form-control" placeholder="Enter Base weekday price">
                     </div>

                     <!-- Weekend Price -->
                     <div class="mb-3 col-md-3" id="base_weekend_price" style="display: none;">
                           <label for="weekend_price" class="form-label"><strong>Base Weekend Price</strong></label>
                           <input value="{{$rate->weekend_price}}" type="number" name="weekend_price" class="form-control" placeholder="Enter Base weekend price">
                     </div>

                     <!-- Start Date -->
                     <div class="mb-3 col-md-3">
                        <label for="date_range" class="form-label"><strong>Date Range</strong><span class="text-danger">*</span></label>
                        <div class="input-group">
                              <input type="text" id="date_range" name="date_range" class="form-control"
                                 placeholder="Select date range" value="{{ $rate->start_date ? date('m/d/Y', strtotime($rate->start_date)) . ' - ' . date('m/d/Y', strtotime($rate->end_date)) : '' }}">
                              @error('hotel_owner_company_name')
                                 <div class="text-danger mt-1">{{ $message }}</div>
                              @enderror
                              <div class="input-group-append">
                                 <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                              </div>
                        </div>
                     </div>

                  </div>
               </div>
            </div>
            

            <div class="form-check form-switch">
               <label for="rate_status" class="form-label"><strong>Status</strong></label>
               <span style="color: red; font-weight: bold;">*</span>
               <input {{$rate->is_active == 1 ? 'checked' : ''}} class="form-check-input" name="rate_status" type="checkbox" id="rate_status" value="1">
               <label class="form-check-label"></label>
               @error('rate_status')
                  <div class="text-danger mt-1">{{ $message }}</div>
               @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="d-flex gap-3">
               <a href="{{ route('hotels.rates', $hotel->hotel_unique_id) }}" class="btn btn-secondary px-4">Previous</a>
               <button type="submit" class="btn btn-primary px-4">Update Rates</button>
               
            </div>
         </form>
      </div>
   </div>
</div>
@endsection
@section('scripts') 
<!-- DataTable Scripts -->
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<!-- Required for daterangepicker -->
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
      $(document).ready(function () {
         var hasStartDate = "{{ !empty($rate->start_date) }}" === "1";
         var hasEndDate = "{{ !empty($rate->end_date) }}" === "1";
         
         var options = {
            opens: 'right',
            autoApply: true,
            locale: {
               format: 'MM/DD/YYYY',
               separator: ' - ',
               applyLabel: "Apply",
               cancelLabel: "Clear"
            }
         };
         
         // Only add start and end dates if they exist
         if (hasStartDate && hasEndDate) {
            options.startDate = "{{ date('m/d/Y', strtotime($rate->start_date)) }}";
            options.endDate = "{{ date('m/d/Y', strtotime($rate->end_date)) }}";
         }
         
         $('#date_range').daterangepicker(options);
         
         $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
         });
         
         // Run toggleFields on document load to set correct initial field visibility
         toggleFields();
      });
</script>
<script>
    $(document).ready(function() {
        $('#example2').DataTable({
            "order": [[0, "asc"]],
            lengthChange: false,
            buttons: ['copy', 'excel', 'pdf', 'print']
        });

        $('#example2').DataTable().buttons().container().appendTo('#example2_wrapper .col-md-6:eq(0)');
    });

    function setDeleteForm(action) {
        document.getElementById('deleteForm').action = action;
    }
</script>

<script>
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
         
         // Initialize fields visibility on page load
         toggleFields();
    });
    
    // Make the toggleFields function globally available
    function toggleFields() {
        const eventTypeDropdown = document.querySelector('select[name="event_type"]');
        const priceField = document.getElementById('price');
        const weekdayPriceField = document.getElementById('base_weekday_price');
        const weekendPriceField = document.getElementById('base_weekend_price');
        
        const selectedEventType = eventTypeDropdown.value;
        
        if (selectedEventType === "Season") {
            weekdayPriceField.style.display = "block";
            weekendPriceField.style.display = "block";
            priceField.style.display = "none";
        } else {
            priceField.style.display = "block";
            weekdayPriceField.style.display = "none";
            weekendPriceField.style.display = "none";
        }
    }
</script>


@endsection
