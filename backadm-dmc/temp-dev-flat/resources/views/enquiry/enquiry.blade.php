@extends('layouts.layout')
@section('title', 'Tours')
@extends('layouts.datatablecss')
@section('content')
<script src="https://cdn.tailwindcss.com"></script>
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

    .warning-message {
        position: relative;
        background-color: #fff3cd;
        color: #856404;
        border-left: 4px solid #ffc107;
        padding: 0.75rem 1.25rem;
        margin-bottom: 1rem;
        border-radius: 0.25rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        opacity: 0;
        transform: translateY(-10px);
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    .warning-message.show {
        opacity: 1;
        transform: translateY(0);
    }

    .warning-icon {
        margin-right: 0.5rem;
    }

</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Enquiry Listing</h5>
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
                            <th>Display ID</th>
                            <th>Initiated Date</th>
                            <th>Comment</th>
                            
                            <th>Actual Amount</th>
                            <th>Settlement Amount</th>
                            <th>Status</th>
                            @if($currentUser->role_id == 33 || $currentUser->role_id == 37 || $currentUser->role_id == 38 || $currentUser->role_id == 11)
                                <th>Update Price</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($enquiries) > 0)
                            @foreach($enquiries as $key => $enquiry)
                                <tr data-enquiry-id="{{ $enquiry->enquiry_id }}">
                                    <td>{{ ++$key }}</td>
                                    <td>{{ $enquiry->display->display_id ?? 'N/A' }}</td>
                                    <td>
                                        {{\App\Helpers\CommonHelper::DateFormatAdmin($enquiry->created_at)}}
                                    </td>
                                    <td class="category-name">{{ $enquiry->comment }}</td>
                                    
                                    <td class="category-name">
                                        {{ ($enquiry->actual_amount) }}</td>
                                    <td class="category-name">
                                        {{ ($enquiry->amount) }}</td>
                                    
                                    <td>
                                        @if($enquiry->status == 1)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    @if($currentUser->role_id == 33 || $currentUser->role_id == 37 || $currentUser->role_id == 38 || $currentUser->role_id == 11)
                                        <td>
                                            <button 
                                                onclick="openModal({{ $enquiry->enquiry_id ?? 0 }}, 
                                                    '{{ route('update-price-comment') }}', 
                                                    {{ $enquiry->amount ?? 0 }}, 
                                                    {{ $enquiry->actual_amount ?? 0 }})"
                                                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
                                            >
                                                Update
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="updateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white w-full max-w-md p-6 rounded-2xl shadow-lg border border-blue-100 relative">
            <h2 class="text-2xl font-bold text-blue-700 mb-4">Update Price & Comment</h2>
            <form id="updateForm" method="POST" action="">
                @csrf
                <input type="hidden" name="enquiry_id" id="modal_item_id">
                <div class="mb-4">
                    <label class="block text-blue-700 font-semibold mb-1">Price</label>
                    <div class="form-group">
                        <input id="current_price" type="number" name="price" class="w-full border border-blue-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" onkeyup="validatePrice(this)" required>
                        <div id="warning-message" class="warning-message">
                            <i class="warning-icon bi bi-exclamation-triangle-fill"></i>
                            Enquiry price cannot exceed the actual amount.
                        </div>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-blue-700 font-semibold mb-1">Comment</label>
                    <textarea name="comment" rows="3" class="w-full border border-blue-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded">Cancel</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Submit</button>
                </div>
            </form>
            <button onclick="closeModal()" class="absolute top-2 right-2 text-blue-600 hover:text-red-500 text-lg font-bold">&times;</button>
        </div>
    </div>
</div>

@if(in_array(auth()->user()->role_id, [16, 52, 66]))
    <!-- Display hotel enquiry data -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Hotel Enquiry Details</h5>
            <!-- Display the hotel enquiry data here -->
        </div>
    </div>
@endif

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
        pageLength: 10, // Default number of entries per page
        drawCallback: function() {
            // Reinitialize Select2 for guide and driver dropdowns after each draw
            $('.guideSelect').select2({
                placeholder: "Select a Asst. Manager",
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

<!-- Open modal for price update -->
<script>
    function openModal(id, route, price, actual_amount) {
        document.getElementById('updateModal').classList.remove('hidden');
        document.getElementById('modal_item_id').value = id;
        const form = document.getElementById('updateForm');
        form.action = route;
        const currentPrice = document.getElementById('current_price');
        currentPrice.value = price;
        currentPrice.max = actual_amount;
    }

    function closeModal() {
        document.getElementById('updateModal').classList.add('hidden');
        document.getElementById('updateForm').reset();
    }
</script>

<script>
function validatePrice(input) {
    var maxValue = parseFloat(input.getAttribute('max'));
    var currentValue = parseFloat(input.value);
    var warningMessage = document.getElementById('warning-message');
    
    if (currentValue > maxValue) {
        input.value = maxValue; // Reset to maximum allowed value
        warningMessage.classList.add('show');
        
        // Hide the message after 3 seconds
        setTimeout(() => {
            warningMessage.classList.remove('show');
        }, 3000);
    }
}
</script>

<!-- For Guide Assign and Guide Remove JS -->
<script>
    $(document).ready(function() {
    $('.guideSelect').select2({
        placeholder: "Select a Asst. Manager",
        allowClear: true,
        width: '100%'
    });

    // Detect change event on dynamically added select elements
    $(document).on('change', '.guideSelect', function() {
        var aomId = $(this).val(); // Get selected guide ID
        var enquiryId = $(this).closest('tr').data('enquiry-id'); // Assuming each enquiry is in a <tr>

        //console.log("Selected Guide ID:", guideId, "for enquiry ID:", enquiryId); // Added console log
        if (aomId) {
            updateGuide(aomId, enquiryId);
            console.log("Selected Guide ID:", aomId, "for enquiry ID:", enquiryId); // Added console log
        } else {
            removeManager(enquiryId);
            console.log("Removed Guide ID:", aomId, "for enquiry ID:", enquiryId); // Added console log
        }
    });

    // Function to assign guide
    function updateGuide(aomId, enquiryId) {
        if (!enquiryId) {
            toastr.error("enquiry ID is missing.");
            return;
        }

        $.ajax({
            url: "{{ route('enquiry.assign-manager') }}",
            type: "POST",
            data: {
                aom_id: aomId,
                enquiry_id: enquiryId,
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

    function removeManager(enquiryId) {
        if (!enquiryId) {
            toastr.error("Enquiry ID is missing.");
            return;
        }

        $.ajax({
            url: "{{ route('enquiry.remove-manager') }}", // Make sure this route exists in your Laravel app
            type: "POST",
            data: {
                enquiry_id: enquiryId,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.status === 'success') {
                    toastr.success(response.message);

                    // Highlight the row to indicate manager removal
                    $("tr[data-enquiry-id='" + enquiryId + "']").css("background-color", "#ffe6cc"); // Light Orange

                    // Optional: Reset the background after a short time
                    setTimeout(function() {
                        $("tr[data-enquiry-id='" + enquiryId + "']").css("background-color", "");
                    }, 2000);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error("An error occurred while removing the manager.");
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
@endsection