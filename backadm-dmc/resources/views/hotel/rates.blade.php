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
<div class="content-wrapper">
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="card mb-6">
         <h5 class="card-header d-flex justify-content-between align-items-center">
               Add Events
               <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
                  <i class="mdi mdi-arrow-left"></i> Back
               </a>
         </h5>
         <form id="hotelForm" method="POST" action="{{ route('storerates') }}" enctype="multipart/form-data" class="card-body">
            @csrf
            <input type="hidden" class="form-control" name="hotel_id" value="{{ $hotel->hotel_unique_id }}">
            
            
            <hr>
            <div id="hotelRatesContainer">
               <div class="hotel-rate-form">
                  <div class="row">
                     <!-- Event Name -->
                     <div class="col-md-3 mb-3">
                        <label for="event" class="form-label"><strong>Event Name</strong><span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="event" placeholder="Enter Event Name" required>
                     </div>
                     <!-- Event Type -->
                     <div class="col-md-3 mb-3">
                        <label for="event_type" class="form-label"><strong>Event Type</strong><span class="text-danger">*</span></label>
                        <select id="event_type" class="form-control" name="event_type" required>
                           <option value="">Select Event Type</option>
                           <option value="Fair Date">Fair Date</option>
                           <option value="Blackout Date">Blackout Date</option>
                        </select>
                     </div>
            
                     <!-- Price -->
                     <div class="col-md-3 mb-3" id="price" style="display: none;">
                        <label for="price" class="form-label"><strong>Price</strong></label><span class="text-danger">*</span>
                        <input type="number" class="form-control" name="price" placeholder="Enter Price">
                     </div>
                     <!-- Surcharge -->
                     <div class="col-md-3 mb-3" id="surcharge" style="display: none;">
                        <label for="surcharge" class="form-label"><strong>Surcharge</strong></label><span class="text-danger">*</span>
                        <input type="number" class="form-control" name="surcharge" placeholder="Enter Surcharge">
                     </div>

                     <!-- Start Date, End date DateRange -->
                        <div class="mb-3 col-md-3">
                           <label for="date_range" class="form-label"><strong>Date Range</strong><span class="text-danger">*</span></label>
                           <div class="input-group">
                                 <input type="text" id="date_range" name="date_range" class="form-control"
                                    placeholder="Select date range">
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
               <input class="form-check-input" name="rate_status" type="checkbox" id="rate_status" value="1">
               <label class="form-check-label"></label>
               @error('rate_status')
                  <div class="text-danger mt-1">{{ $message }}</div>
               @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="d-flex gap-3">
               <button type="submit" class="btn btn-primary px-4">Save</button>
               {{-- <a href="{{ route('policy', $hotel->hotel_unique_id) }}" class="btn btn-success px-4">Save</a> --}}
            </div>
         </form>
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
                  <h5 class="card-title mb-0">Events of {{ $hotel->name }}</h5>
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
               <thead class="">
                  <tr>
                     <th>Start Date</th>
                     <th>End Date</th>
                     <th>Event Name</th>
                     <th>Event Type</th>
                     <th>Price/Surcharge</th>
                     <th>Action</th>
                  </tr>
               </thead>
               <tbody>
                  @php
                     use Carbon\Carbon;
                  @endphp
                  @foreach ($rates as $rate)
                  <tr>
                     <td class="category-name">{{ \App\Helpers\CommonHelper::DateFormatAdmin($rate->start_date) }}</td>
                     <td class="category-name">{{ \App\Helpers\CommonHelper::DateFormatAdmin($rate->end_date) }}</td>
                     <td>{{ $rate->event }}</td>
                     <td>{{ $rate->event_type }}</td>
                     <td>{{ $rate->price }}</td>

                     <td>
                        {{-- <a href="{{ route('rates.edit', ['id' => $rate->rate_id, 'hotel_id' => $hotel->hotel_unique_id]) }}" class="btn btn-warning btn-sm">Edit</a> --}}
                        
                        <a href="{{ route('rates.edit', ['id' => $rate->rate_id, 'hotel_id' => $hotel->hotel_unique_id]) }}"
                           class="btn btn-primary btn-sm rounded-circle"
                           style="min-width: 28px; min-height: 28px; padding: 0;">
                           <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960"
                               width="16px" fill="#ffffff">
                               <path
                                   d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z" />
                           </svg>
                       </a>
                     </td>
                  </tr>
                  @endforeach
               </tbody>
            </table>
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
        $('.datatables-basic').DataTable({
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

<!-- DataTable Scripts -->
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

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

<!-- <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Select the event type dropdown
        const eventTypeDropdown = document.getElementById('event_type');
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
</script> -->

<!-- Price and surcharge -->
<script>

    const eventTypeSelect = document.getElementById('event_type');
    const priceContainer = document.getElementById('price');
    const surchargeContainer = document.getElementById('surcharge');

    // Add event listener for change on event type
    eventTypeSelect.addEventListener('change', function () {
        const selectedValue = this.value;

        // Show/Hide fields based on selection
        if (selectedValue === 'Fair Date') {
            surchargeContainer.style.display = 'block';
            priceContainer.style.display = 'none';
        } else if (selectedValue === 'Blackout Date') {
            priceContainer.style.display = 'block';
            surchargeContainer.style.display = 'none';
        } else {
            // Hide both if no valid selection
            priceContainer.style.display = 'none';
            surchargeContainer.style.display = 'none';
        }
    });
</script>
@endsection
