@extends('layouts.layout')
@section('title', 'Hotels')
@section('css')
<link href="{{ URL::asset('build/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Edit Season
                <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form id="hotelForm" method="POST" action="{{ route('season.update') }}" enctype="multipart/form-data" class="card-body">
               @csrf
               <input value="{{$rate->rate_id}}" type="text" class="form-control" name="rate_id" hidden>
               <input value="{{$hotel->hotel_unique_id}}" type="text" class="form-control" name="hotel_id" hidden>
               <input name="event_type" type="hidden" value="Season">

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
                        <!-- Single Weekday -->
                        <div class="mb-3 col-md-3" id="base_weekday_price">
                           <label for="weekday_price" class="form-label"><strong>Single Base Weekday Price</strong></label>
                           <input value="{{$rate->weekday_price}}" type="number" name="weekday_price" class="form-control" placeholder="Enter Base weekday price">
                        </div>

                        <!-- Single Weekend Price -->
                        <div class="mb-3 col-md-3" id="base_weekend_price">
                              <label for="weekend_price" class="form-label"><strong>Single Base Weekend Price</strong></label>
                              <input value="{{$rate->weekend_price}}" type="number" name="weekend_price" class="form-control" placeholder="Enter Base weekend price">
                        </div>

                        <!-- Double Weekday -->
                        <div class="mb-3 col-md-3" id="base_weekday_price">
                           <label for="weekday_price" class="form-label"><strong>Double Base Weekday Price</strong></label>
                           <input value="{{$rate->double_weekday_price}}" type="number" name="double_weekday_price" class="form-control" placeholder="Enter Base weekday price">
                        </div>

                        <!-- Double Weekend Price -->
                        <div class="mb-3 col-md-3" id="base_weekend_price">
                              <label for="weekend_price" class="form-label"><strong>Double Base Weekend Price</strong></label>
                              <input value="{{$rate->double_weekend_price}}" type="number" name="double_weekend_price" class="form-control" placeholder="Enter Base weekend price">
                        </div>

                        <!-- Start Date -->
                        <div class="col-md-3 mb-3">
                           <label for="start_date" class="form-label"><strong>Start Date</strong><span class="text-danger">*</span></label>
                           <input value="{{$rate->start_date}}" type="date" class="form-control" name="start_date" required>
                        </div>

                        <!-- End Date -->
                        <div class="col-md-3 mb-3">
                           <label for="end_date" class="form-label"><strong>End Date</strong><span class="text-danger">*</span></label>
                           <input value="{{$rate->end_date}}" type="date" class="form-control" name="end_date" required>
                        </div>
                        {{-- <div class="col-md-3 mb-3 d-flex align-items-end">
                           <button type="button" class="btn btn-danger remove-rate">Delete</button>
                        </div> --}}
                     </div>
                  </div>
               </div>

               <!-- Status -->
               <div class="form-check form-switch">
                  <label for="season_status" class="form-label"><strong>Status</strong></label>
                  <span style="color: red; font-weight: bold;">*</span>
                  <input {{$rate->is_active == 1 ? 'checked' : ''}} class="form-check-input" name="season_status" type="checkbox" id="season_status" value="1">
                  <label class="form-check-label"></label>
                  @error('season_status')
                     <div class="text-danger mt-1">{{ $message }}</div>
                  @enderror
               </div>

               <!-- Submit Buttons -->
               <div class="d-flex gap-3">
                  <a href="{{ route('hotels.season', $hotel->hotel_unique_id) }}" class="btn btn-secondary px-4">Previous</a>
                  <button type="submit" class="btn btn-primary px-4">Update Seasons</button> 
               </div>
            </form>
      </div>
   </div>
</div>
@endsection
@section('scripts') 
<!-- DataTable Scripts -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>

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

        eventTypeDropdown.addEventListener("change", toggleFields);

      // Initialize the correct visibility state on page load
      toggleFields();

    });
</script> --}}


@endsection
