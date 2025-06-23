@extends('layouts.layout')
@section('title', 'Tours')
@extends('layouts.datatablecss')
@section('content')

<style>
    @keyframes glowEffect {
        0% {
            box-shadow: 0px 0px 5px rgba(255, 255, 255, 0.5);
        }
        50% {
            box-shadow: 0px 0px 15px rgba(255, 255, 0, 0.8);
        }
        100% {
            box-shadow: 0px 0px 5px rgba(255, 255, 255, 0.5);
        }
    }

    @keyframes swipeLeftEffect {
        0% {
            transform: translateX(0);
            opacity: 1;
        }
        100% {
            transform: translateX(-105%);
            opacity: 0;
        }
    }

    .glow {
        animation: glowEffect 0.5s ease-in-out;
    }

    .swipe-left {
        animation: swipeLeftEffect 0.8s ease-in-out forwards;
    }

    .light-message {
    margin-left: 10px;
    font-weight: bold;
    opacity: 0.9;
    transition: opacity 0.3s ease-in-out;
}

.row-approved {
        background-color: #d4edda !important; /* Light green */
        transition: background 0.3s ease-in-out;
}

.row-declined {
        background-color: #f8d7da !important; /* Light red */
        transition: background 0.3s ease-in-out;
    }

    /* Loading overlay styles */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        visibility: hidden;
        opacity: 0;
        transition: opacity 0.3s, visibility 0.3s;
    }

    .loading-overlay.active {
        visibility: visible;
        opacity: 1;
    }

    .loading-spinner {
        width: 100px;
        height: 100px;
        border-radius: 10px;
        background-color: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .loading-spinner .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .loading-text {
        margin-top: 10px;
        font-weight: bold;
        color: #333;
    }

    /* Modal loader */
    #approveLoader {
        padding: 20px;
        border-radius: 8px;
        background-color: rgba(245, 247, 250, 0.8);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .spinner-border {
        width: 3rem;
        height: 3rem;
    }
    
    /* Button loading state */
    .btn:disabled {
        cursor: not-allowed;
        opacity: 0.8;
    }

    /* Additional styles for Decline confirmation dialog */
    .custom-confirm-dialog {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        visibility: hidden;
        opacity: 0;
        transition: opacity 0.3s, visibility 0.3s;
    }

    .custom-confirm-dialog.active {
        visibility: visible;
        opacity: 1;
    }

    .confirm-content {
        width: 400px;
        background-color: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        transform: translateY(-20px);
        transition: transform 0.3s;
    }

    .custom-confirm-dialog.active .confirm-content {
        transform: translateY(0);
    }

    .confirm-header {
        padding: 15px 20px;
        background-color: #dc3545;
        color: white;
        font-weight: bold;
    }

    .confirm-body {
        padding: 20px;
    }

    .confirm-footer {
        padding: 15px 20px;
        background-color: #f8f9fa;
        text-align: right;
    }

    .confirm-footer button {
        margin-left: 10px;
    }
    
    /* Tour ID badge in modal header */
    .tour-id-badge {
        background-color: #fff;
        color: #0d6efd;
        font-weight: bold;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 14px;
        margin-left: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
</style>
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Hotel Booking Approval Listing</h5>
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
                            <th>Tour Id</th>
                            <th>Hotel</th>
                            <th>Room</th>
                            <th>Bed</th>
                            <th>Pax</th>
                            <th>Details</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    {{-- <tbody>
                        @if(count($orders) > 0)
                        @foreach ($orders as $order)
                            @foreach ($order['hotel_details'] ?? [] as $hotel) <!-- Check if hotel_details exists -->
                                @foreach ($hotel['rooms'] ?? [] as $room) <!-- Check if rooms exist -->
                                    @php
                                        $bedTypes = [];
                                        $totalHeadCount = 0;
                                    @endphp
                    
                                    @foreach ($room['beds'] ?? [] as $bed) <!-- Check if beds exist -->
                                        @php
                                            $bedTypes[] = $bed['bed_type'] ?? 'N/A';
                                            $totalHeadCount += $bed['head_count'] ?? 0;
                                        @endphp
                                    @endforeach
                    
                                    <tr>
                                        <td>{{ $order->tour_id }}</td>
                                        <td>
                                            <div class="p-2 rounded shadow-sm text-white" style="background: #d4edda; border-radius: 10px;">
                                                <strong class="text-dark">{{ $hotel['hotelDetails']['hotel_name'] ?? 'N/A' }}</strong><br>
                                                <small class="text-success">{{ $hotel['hotelDetails']['location'] ?? 'N/A' }}</small>
                                            </div>
                                        </td>
                                        <td>{{ $room['room_type'] ?? 'N/A' }}</td>
                                        <td>{{ implode(', ', $bedTypes) }}</td> <!-- Combined bed types -->
                                        <td>{{ $totalHeadCount }}</td> <!-- Summed head count -->
                                        <td>
                                            <strong>Email:</strong> {{ $hotel['email'] ?? 'N/A' }} <br>
                                            <strong>Phone:</strong> {{ $hotel['phone'] ?? 'N/A' }} <br>
                                            <strong>Address:</strong> {{ $hotel['address1'] ?? 'N/A' }}, {{ $hotel['state'] ?? 'N/A' }} - {{ $hotel['zip'] ?? 'N/A' }}
                                        </td>
                                        <td>{{ $hotel['totalPrice'] ?? 'N/A' }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-primary approve-btn" data-id="{{ $order['booking_id'] ?? '' }}">
                                                    Approve
                                                </button>
                                                <button class="btn btn-sm btn-danger decline-btn" data-id="{{ $order['booking_id'] ?? '' }}">
                                                    Decline
                                                </button>
                                            </div>
                                        </td>                                                                                                                    
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                        @endif
                    </tbody>                                         --}}

                    <tbody>
                        @if (!empty($orders))
                            @foreach ($orders as $key => $order)
                                @php
                                    $hotelDetails = is_array($order['hotel_details'] ?? null) ? $order['hotel_details'] : json_decode($order['hotel_details'] ?? '[]', true);
                                @endphp
                    
                                @foreach ($hotelDetails as $hotel)
                                    @php
                                        $rooms = is_array($hotel['rooms'] ?? null) ? $hotel['rooms'] : json_decode($hotel['rooms'] ?? '[]', true);
                                    @endphp
                    
                                    @foreach ($rooms as $room)
                                        @php
                                            $bedTypes = [];
                                            $totalHeadCount = 0;
                                            $beds = is_array($room['beds'] ?? null) ? $room['beds'] : json_decode($room['beds'] ?? '[]', true);
                                        @endphp
                    
                                        @foreach ($beds as $bed)
                                            @php
                                                $bedTypes[] = $bed['bed_type'] ?? 'N/A';
                                                $totalHeadCount += $bed['head_count'] ?? 0;
                                            @endphp
                                        @endforeach
                    
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $order['tour_id'] ?? 'N/A' }}</td>  
                                            <td>
                                                <div class="p-2 rounded shadow-sm text-white" style="background: #d4edda; border-radius: 10px;">
                                                    <strong class="text-dark">{{ $hotel['hotelDetails']['hotel_name'] ?? 'N/A' }}</strong><br>
                                                    <small class="text-success">{{ $hotel['hotelDetails']['location'] ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>{{ $room['room_type'] ?? 'N/A' }}</td>
                                            <td>{{ implode(', ', $bedTypes) }}</td>
                                            <td>{{ $totalHeadCount }}</td>
                                            <td>
                                                <strong>Name:</strong> {{ $hotel['fullName'] ?? 'N/A' }} <br>
                                                <strong>Email:</strong> {{ $hotel['email'] ?? 'N/A' }} <br>
                                                <strong>Phone:</strong> {{ $hotel['phone'] ?? 'N/A' }} <br>
                                                <strong>Address:</strong> {{ $hotel['address1'] ?? 'N/A' }}, {{ $hotel['state'] ?? 'N/A' }} - {{ $hotel['zip'] ?? 'N/A' }}
                                            </td>
                                            <td>{{ $hotel['totalPrice'] ?? 'N/A' }}</td>

                                            <td>
                                                @if(auth()->user()->role_id == 34 || auth()->user()->role_id == 124 || auth()->user()->role_id == 125)
                                                <div class="d-flex gap-2">
                                                <button type="button"
                                                        class="btn btn-sm btn-primary approve-btn"
                                                        data-id="{{ $order['booking_id'] ?? '' }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#approveModal">
                                                    Approve
                                                </button>
                                                    <button class="btn btn-sm btn-danger decline-btn" data-id="{{ $order['booking_id'] ?? '' }}">
                                                        Decline
                                                    </button>
                                                </div>
                                                @else
                                                    @if($order['status'] == 2)
                                                    <span class="badge bg-warning">Pending</span>
                                                    @elseif($order['status'] == 1)
                                                    <span class="badge bg-success">Approved</span>
                                                    @elseif($order['status'] == 3)
                                                    <span class="badge bg-danger">Declined</span>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endforeach
                        {{-- @else
                            <tr>
                                <td colspan="7" class="text-center">No bookings available.</td>
                            </tr> --}}
                        @endif
                    </tbody>                                        
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add this after your existing content div but before any scripts -->
<div class="loading-overlay" id="globalLoader">
    <div class="loading-spinner">
        <div class="spinner"></div>
        <div class="loading-text">Processing...</div>
    </div>
</div>

<!-- Add this custom decline confirmation dialog -->
<div class="custom-confirm-dialog" id="declineConfirmDialog">
    <div class="confirm-content">
        <div class="confirm-header">
            <i class="fas fa-exclamation-triangle me-2"></i> Decline Booking
        </div>
        <div class="confirm-body">
            <p>Are you sure you want to decline this booking?</p>
        </div>
        <div class="confirm-footer">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="cancelDecline">Cancel</button>
            <button type="button" class="btn btn-danger btn-sm" id="confirmDecline">Decline</button>
        </div>
    </div>
</div>

<!-- Update the Approve Modal with Tour ID first, then heading -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius: 12px; border: none;">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="approveModalLabel">
          <span class="tour-id-badge" id="modalTourId"></span> Approve Booking
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="approveForm" enctype="multipart/form-data">
        <div class="modal-body" style="background-color: #f8faff;">
          <input type="hidden" id="bookingId" name="booking_id">

          <div class="mb-3">
            <label for="reference_id" class="form-label"><strong>Booking Reference ID</strong><span style="color: red; font-weight: bold;">*</span></label>
            <input type="text" class="form-control" id="reference_id" name="reference_id" required>
          </div>

          <div class="mb-3">
            <label for="invoice_pdf" class="form-label"><strong>Booking Confirmation Documents</strong></label>
            <input type="file" class="form-control" id="invoice_pdf" name="invoice_pdf" accept="application/pdf" >
          </div>
          
          <div id="approveLoader" class="text-center py-3" style="display: none;">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-primary fw-bold">Processing your approval request...</p>
          </div>
        </div>

        <div class="modal-footer" style="background-color: #f8faff;">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="approveSubmitBtn">
            <span id="approveButtonText">Approved</span>
            <span id="approveButtonSpinner" class="spinner-border spinner-border-sm ms-1" role="status" aria-hidden="true" style="display: none;"></span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<!-- DataTable JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<!-- DataTables Initialization Script -->

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const approveButtons = document.querySelectorAll('.approve-btn');
        approveButtons.forEach(button => {
            button.addEventListener('click', function () {
                const bookingId = this.getAttribute('data-id');
                document.getElementById('bookingId').value = bookingId;
            });
        });
    });
</script>
<script>
$(document).ready(function() {
    // Initialize DataTable with export buttons
    var table = $('.datatables-basic').DataTable({
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
        pageLength: 10, // Default number of entries per page
        drawCallback: function() {
            // Reinitialize Select2 for guide and driver dropdowns after each draw
            $(document).ready(function () {
                let currentDeclineBookingId = null;
                let currentDeclineRow = null;
                
                // Open Approve Modal and set booking ID and Tour ID
                $(".approve-btn").on("click", function () {
                    let bookingId = $(this).data("id");
                    let tourId = $(this).closest("tr").find("td:eq(1)").text(); // Get tour ID from the second column
                    
                    // Set values in modal
                    $("#bookingId").val(bookingId);
                    $("#modalTourId").text("Tour ID: #" + tourId.trim());
                    
                    // Reset the form and hide loader when opening modal
                    $("#approveForm")[0].reset();
                    $("#approveLoader").hide();
                    $("#approveButtonText").show();
                    $("#approveButtonSpinner").hide();
                    $("#approveSubmitBtn").prop("disabled", false);
                });

                // Handle Approve Form Submission
                $("#approveForm").on("submit", function (e) {
                    e.preventDefault();

                    // Show loading spinner and disable button
                    $("#approveLoader").show();
                    $("#approveButtonText").text("Processing...");
                    $("#approveButtonSpinner").show();
                    $("#approveSubmitBtn").prop("disabled", true);
                    
                    let formData = new FormData(this);
                    let bookingId = $("#bookingId").val();
                    let row = $("button[data-id='" + bookingId + "']").closest("tr"); // Get table row

                    $.ajax({
                        url: "{{ route('bookings.approve') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        success: function (response) {
                            if (response.success) {
                                // Hide spinner
                                $("#approveLoader").hide();
                                $("#approveModal").modal("hide"); // Hide modal

                                // Apply green background
                                row.addClass("row-approved");
                                
                                // Wait a moment to show the color change, then swipe left
                                setTimeout(function() {
                                    row.addClass("swipe-left");
                                    
                                    // Show global loader during refresh
                                    setTimeout(function() {
                                        $("#globalLoader").addClass("active");
                                        
                                        // Delay and refresh the page
                                        setTimeout(function () {
                                            location.reload();
                                        }, 800);
                                    }, 700); // After swipe animation is mostly done
                                }, 300);
                            }
                        },
                        error: function (xhr) {
                            // Hide spinner and re-enable button on error
                            $("#approveLoader").hide();
                            $("#approveButtonText").text("Approved");
                            $("#approveButtonSpinner").hide();
                            $("#approveSubmitBtn").prop("disabled", false);
                            
                            alert("Error! " + (xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong."));
                        }
                    });
                });

                // Handle Decline Button Click with custom confirmation
                $(".decline-btn").on("click", function () {
                    currentDeclineBookingId = $(this).data("id");
                    currentDeclineRow = $(this).closest("tr");
                    
                    // Show custom confirmation dialog
                    $("#declineConfirmDialog").addClass("active");
                });
                
                // Handle Cancel Decline
                $("#cancelDecline").on("click", function() {
                    $("#declineConfirmDialog").removeClass("active");
                    currentDeclineBookingId = null;
                    currentDeclineRow = null;
                });
                
                // Handle Confirm Decline
                $("#confirmDecline").on("click", function() {
                    if (!currentDeclineBookingId) return;
                    
                    // Hide confirmation dialog
                    $("#declineConfirmDialog").removeClass("active");
                    
                    // Show global loader
                    $("#globalLoader").addClass("active");

                    $.ajax({
                        url: "{{ route('bookings.decline') }}",
                        type: "POST",
                        data: {
                            booking_id: currentDeclineBookingId,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (response) {
                            if (response.success) {
                                // Hide global loader
                                $("#globalLoader").removeClass("active");
                                
                                // Apply red background
                                currentDeclineRow.addClass("row-declined");
                                
                                // Wait a moment to show the color change, then swipe left
                                setTimeout(function() {
                                    currentDeclineRow.addClass("swipe-left");
                                    
                                    // Show global loader during refresh
                                    setTimeout(function() {
                                        $("#globalLoader").addClass("active");
                                        
                                        // Delay and refresh the page
                                        setTimeout(function () {
                                            location.reload();
                                        }, 800);
                                    }, 700); // After swipe animation is mostly done
                                }, 300);
                            }
                        },
                        error: function (xhr) {
                            // Hide global loader on error
                            $("#globalLoader").removeClass("active");
                            alert("Error! " + (xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong."));
                        }
                    });
                });
                
                // Close confirmation dialog when clicking outside
                $(document).on("click", function(e) {
                    if (
                        $("#declineConfirmDialog").hasClass("active") && 
                        !$(e.target).closest(".confirm-content").length && 
                        !$(e.target).closest(".decline-btn").length
                    ) {
                        $("#declineConfirmDialog").removeClass("active");
                        currentDeclineBookingId = null;
                        currentDeclineRow = null;
                    }
                });
            });
        }
    });

    // Custom export button functionality (for the dropdown)
    $('#exportCopy').on('click', function() {
        table.button('.buttons-copy').trigger();
    });

    $('#exportCSV').on('click', function() {
        table.button('.buttons-csv').trigger();
    });

    $('#exportExcel').on('click', function() {
        table.button('.buttons-excel').trigger();
    });

    $('#exportPDF').on('click', function() {
        table.button('.buttons-pdf').trigger();
    });

    $('#exportPrint').on('click', function() {
        table.button('.buttons-print').trigger();
    });
});
</script>


{{-- <script>
    $(document).ready(function () {
    // Open Approve Modal and set booking ID
    $(".approve-btn").on("click", function () {
        let bookingId = $(this).data("id");
        $("#bookingId").val(bookingId);
    });

    // Handle Approve Form Submission
    $("#approveForm").on("submit", function (e) {
        e.preventDefault();

        let formData = new FormData(this);
        let bookingId = $("#bookingId").val();
        let row = $("button[data-id='" + bookingId + "']").closest("tr"); // Get table row

            $.ajax({
            url: "{{ route('bookings.approve') }}",
                type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            success: function (response) {
                if (response.success) {
                    $("#approveModal").modal("hide"); // Hide modal
                    
                    // Swipe left animation
                    row.animate({ marginLeft: "-100%", opacity: 0 }, 500, function () {
                        $(this).remove();
                        location.reload(); // Refresh the page after animation
                    });
                }
            },
            error: function (xhr) {
                alert("Error! " + xhr.responseJSON.message);
            }
        });
    });

    // Handle Decline Button Click
    $(".decline-btn").on("click", function () {
        let bookingId = $(this).data("id");
        let row = $(this).closest("tr"); // Get table row

            $.ajax({
            url: "{{ route('bookings.decline') }}",
                type: "POST",
                data: {
                    booking_id: bookingId,
                    _token: "{{ csrf_token() }}"
                },
            success: function (response) {
                if (response.success) {
                    // Swipe left animation
                    row.animate({ marginLeft: "-100%", opacity: 0 }, 500, function () {
                        $(this).remove();
                        location.reload(); // Refresh the page after animation
                    });
                }
            },
            error: function (xhr) {
                alert("Error! " + xhr.responseJSON.message);
            }
        });
    });
    });

</script> --}}

{{-- <script>
    

</script> --}}



<!-- Toastr for success/error notifications -->
{{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script> --}}

{{-- <script>
    $(document).ready(function() {
        $(".approve-btn, .decline-btn").click(function() {
            var row = $(this).closest("tr");
            
            // Add glow effect first
            row.addClass("glow");

            setTimeout(function() {
                row.addClass("swipe-left"); // Then swipe left

                setTimeout(function() {
                    row.remove(); // Finally remove the row
                }, 500); // Matches swipe animation duration
            }, 800); // Glow effect duration
        });
    });
</script> --}}


<!-- End DataTable JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
function setDeleteForm(url) {
    document.getElementById('deleteForm').action = url;
}
</script>
@endsection