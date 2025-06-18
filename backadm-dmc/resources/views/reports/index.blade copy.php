@extends('layouts.layout')
@section('title', 'Report')
@extends('layouts.datatablecss')

@section('content')
<style>
    .table-header {
        background: linear-gradient(to right, #4b6cb7, #182848);
        color: white !important;
    }
    .table > :not(caption) > * > * {
        padding: 0.75rem;
        vertical-align: middle;
    }
    .badge {
        padding: 0.5em 1em;
        font-size: 0.85em;
        font-weight: 500;
        letter-spacing: 0.5px;
    }
    .btn-action {
        padding: 0.25rem 0.5rem;
        font-size: 0.85rem;
        margin: 0 0.2rem;
    }
    .filter-container {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }
    .daterangepicker {
        z-index: 9999 !important;
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <!-- Filter Section -->
            <div class="filter-container p-3">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <h5 class="card-title mb-0">Sales Report Analysis</h5>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex justify-content-end flex-wrap gap-2">
                            <!-- Date Range Filter -->
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                <input type="text" id="dateRange" class="form-control" placeholder="Select Date Range..." autocomplete="off">
                            </div>

                            <!-- Role Dropdown -->
                            <select id="roleFilter" class="form-select form-select-sm" style="width: 150px;">
                                <option value="">Select Role...</option>
                                <option value="1">Master DMC</option>
                                <option value="2">DMC</option>
                                <option value="3">Agent</option>
                            </select>

                            <!-- User Type Dropdown -->
                            <select id="userTypeFilter" class="form-select form-select-sm" style="width: 150px;" disabled>
                                <option value="">Select User Type...</option>
                            </select>

                            <!-- Country Dropdown -->
                            <select id="countryFilter" class="form-select form-select-sm" style="width: 150px;" disabled>
                                <option value="">Select Country...</option>
                            </select>

                            <!-- Export Button -->
                            <div class="dropdown">
                                <button class="btn btn-warning btn-sm dropdown-toggle" type="button" id="exportDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-download me-1"></i> Export
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
                </div>
            </div>

            <!-- Table Section -->
            <div class="card-datatable table-responsive p-3">
                <x-alert />
                <table class="datatables-basic table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="text-align: center; width: 80px;">Sl No</th>
                            <th>Name</th>
                            <th>Country</th>
                            <th style="text-align: center; width: 150px;">No. of Tours</th>
                            <th style="text-align: right; width: 150px;">Tour Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tours as $index => $tour)
                            <tr>
                                <td style="text-align: center;">{{ $index + 1 }}</td>
                                <td>{{ $tour->name }}</td>
                                <td>{{ $tour->destination }}</td>
                                <td style="text-align: center;" class="tour-count">{{ $tour->total_tours }}</td>
                                <td style="text-align: right;" class="tour-price">{{ number_format($tour->tour_price, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No tours available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary Section -->
    <div class="d-flex justify-content-end mt-3 p-3">
        <h6 class="me-4">
            <strong>Total Tours:</strong> 
            <span id="totalTours" class="ms-2" style="min-width: 80px; display: inline-block; text-align: right;">
                {{ number_format($tours->sum('total_tours')) }}
            </span>
        </h6>
        <h6>
            <strong>Total Tour Amount:</strong> 
            <span id="totalTourAmount" class="ms-2" style="min-width: 120px; display: inline-block; text-align: right;">
                {{ number_format($tours->sum('tour_price'), 2) }}
            </span>
        </h6>
    </div>
</div>
@endsection

@section('scripts')
<!-- DataTable & Date Libraries -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

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

<script>
    $(document).ready(function () {
        // Initialize Date Range Picker
        $('#dateRange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD',
                applyLabel: "Apply",
                cancelLabel: "Clear"
            }
        });

        // Update input on selection
        $('#dateRange').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            table.ajax.reload();
        });

        // Clear input on cancel
        $('#dateRange').on('cancel.daterangepicker', function () {
            $(this).val('');
            table.ajax.reload();
        });

        // Click icon to open date picker
        $('.input-group-text').on('click', function () {
            $('#dateRange').focus();
        });

        // Role change handler
        $('#roleFilter').on('change', function() {
            const selectedRole = $(this).val();
            const userTypeDropdown = $('#userTypeFilter');
            const countryDropdown = $('#countryFilter');
            
            // Reset dropdowns
            userTypeDropdown.html('<option value="">Select User Type...</option>');
            countryDropdown.html('<option value="">Select Country...</option>');
            
            if(selectedRole === '1') { // If Master DMC is selected
                userTypeDropdown.hide();
                countryDropdown.prop('disabled', false);
                
                // Fetch active countries from database instead of hardcoded list
                $.ajax({
                    url: "{{ route('countries.get-active') }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            response.countries.forEach(country => {
                                countryDropdown.append(
                                    `<option value="${country.name}">${country.name}</option>`
                                );
                            });
                            
                            // Show success toast message
                            toastr.success('Countries loaded successfully!');
                        } else {
                            toastr.error('Error loading countries: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Failed to load countries');
                        console.error(xhr);
                    }
                });
            } else if(selectedRole === '2') { // If DMC is selected
                userTypeDropdown.show();
                userTypeDropdown.html('<option value="">Select Master DMC...</option>');
                userTypeDropdown.prop('disabled', false);
                countryDropdown.prop('disabled', true);

                // Get Master DMC list
                $.ajax({
                    url: '{{ route("get.master.dmc") }}',
                    method: 'GET',
                    success: function(response) {
                        response.forEach(masterDmc => {
                            userTypeDropdown.append(
                                `<option value="${masterDmc.id}">${masterDmc.name}</option>`
                            );
                        });
                    },
                    error: function(xhr) {
                        console.error('Error fetching Master DMCs:', xhr);
                        toastr.error('Error loading Master DMCs');
                    }
                });
            } else if(selectedRole === '3') { // If Agent is selected
                userTypeDropdown.show();
                userTypeDropdown.html('<option value="">Select DMC...</option>');
                userTypeDropdown.prop('disabled', false);
                countryDropdown.prop('disabled', true);

                // Get DMC list
                $.ajax({
                    url: '{{ route("get.dmc") }}',
                    method: 'GET',
                    success: function(response) {
                        response.forEach(dmc => {
                            userTypeDropdown.append(
                                `<option value="${dmc.id}">${dmc.name}</option>`
                            );
                        });
                    },
                    error: function(xhr) {
                        console.error('Error fetching DMCs:', xhr);
                        toastr.error('Error loading DMCs');
                    }
                });
            } else {
                userTypeDropdown.prop('disabled', true);
                countryDropdown.prop('disabled', true);
            }

            table.ajax.reload();
        });

        // User Type change handler
        $('#userTypeFilter').on('change', function() {
            const selectedRole = $('#roleFilter').val();
            const selectedId = $(this).val();
            const countryDropdown = $('#countryFilter');
            
            // Reset country dropdown
            countryDropdown.html('<option value="">Select Country...</option>');
            
            if (selectedId) {
                if (selectedRole === '2') { // For Master DMC
                    $.ajax({
                        url: `{{ url('get-master-dmc-countries') }}/${selectedId}`,
                        method: 'GET',
                        success: function(response) {
                            countryDropdown.prop('disabled', false);
                            
                            if (response && response.length > 0) {
                                response.forEach(country => {
                                    countryDropdown.append(
                                        `<option value="${country}">${country}</option>`
                                    );
                                });
                            }
                        },
                        error: function(xhr) {
                            console.error('Error fetching countries:', xhr);
                            countryDropdown.prop('disabled', true);
                        }
                    });
                } else if (selectedRole === '3') { // For DMC
                    // For DMC, enable country dropdown and show DMC's country
                    $.ajax({
                        url: `{{ url('get-dmc-countries') }}/${selectedId}`,
                        method: 'GET',
                        success: function(response) {
                            countryDropdown.prop('disabled', false);
                            
                            if (response && response.country) {
                                countryDropdown.append(
                                    `<option value="${response.country}">${response.country}</option>`
                                );
                            } else if (typeof response === 'string') {
                                countryDropdown.append(
                                    `<option value="${response}">${response}</option>`
                                );
                            }
                        },
                        error: function(xhr) {
                            console.error('Error fetching DMC country:', xhr);
                            countryDropdown.prop('disabled', true);
                        }
                    });
                }
            } else {
                countryDropdown.prop('disabled', true);
            }

            table.ajax.reload();
        });

        // Country change handler
        $(document).ready(function () {
    $('#countryFilter').on('change', function () {
        const selectedCountry = $(this).val(); // Get selected country
        
        if (selectedCountry) {
            $.ajax({
                url: `{{ url('get-tours-by-country') }}/${selectedCountry}`, // Ensure this route exists
                method: 'GET',
                success: function (response) {
                    let tableBody = $('.datatables-basic tbody');
                    tableBody.empty(); // Clear previous tour list

                    if (response.success && response.tours.length > 0) {
                        response.tours.forEach((tour, index) => {
                            tableBody.append(`
                                <tr>
                                    <td class="category-name text-center">${index + 1}</td>
                                    <td>${tour.name}</td>
                                    <td>${tour.destination}</td>
                                </tr>
                            `);
                        });
                        toastr.success('Tour list updated successfully!');
                    } else {
                        tableBody.append(`<tr><td colspan="3" class="text-center">No tours available</td></tr>`);
                        toastr.warning('No tours found for the selected country.');
                    }
                },
                error: function (xhr) {
                    console.error('Error fetching tours:', xhr);
                    toastr.error('Failed to load tour data.');
                }
            });
        } else {
            $('.datatables-basic tbody').html(`<tr><td colspan="3" class="text-center">Please select a country</td></tr>`);
        }
    });
});
});
</script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
@endsection