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
            Add Seasons
                <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form id="hotelForm" method="POST" action="{{ route('storeseason') }}" enctype="multipart/form-data" class="card-body">
               @csrf
               <input type="hidden" class="form-control" name="hotel_id" value="{{ $hotel->hotel_unique_id }}">
               <input type="hidden" class="form-control" name="room_id" value="{{ $room->room_id }}">
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
                  <tr>
                     <td class="category-name">{{ \App\Helpers\CommonHelper::DateFormatAdmin($rate->start_date) }}</td>
                     <td class="category-name">{{ \App\Helpers\CommonHelper::DateFormatAdmin($rate->end_date) }}</td>
                     <td>{{ $rate->event }}</td>
                     <td>{{ $rate->event_type }}</td>
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
