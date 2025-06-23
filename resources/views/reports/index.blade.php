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
            
            <!-- 🔹 Filter Section -->
            <div class="filter-container p-3">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <h5 class="card-title mb-0">Sales Report Analysis</h5>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex justify-content-end flex-wrap gap-2">
                            
                            <!-- Tour Status -->
                            <select id="statusFilter" class="form-select form-select-sm" style="width: 150px;">
                                <option value="1">Completed</option>
                                <option value="2">In Progress</option>
                                <option value="3">Cancelled</option>
                                <option value="4">Enquired</option>
                            </select>

                        <!-- Date Range Filter -->
                                <div class="input-group input-group-sm" style="width: 250px;">
                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    <input type="text" id="dateRange" class="form-control" placeholder="Select Date Range..." autocomplete="off">
                                </div>

                            <!-- Role Filter -->
                                <select id="roleFilter" class="form-select form-select-sm" style="width: 150px;">
                                    <option value="">Select Role...</option>
                                <option value="1">Master DMC</option>
                                <option value="2">DMC</option>
                                    <option value="3">Agent</option>
                                </select>

                            <!-- User Type Filter -->
                                <select id="userTypeFilter" class="form-select form-select-sm" style="width: 150px;" disabled>
                                    <option value="">Select User Type...</option>
                        </select>

                            <!-- Country Filter -->
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

            <!-- 🔹 Table Section -->
            <div class="card-datatable table-responsive p-3">
                <x-alert />

                <table class="datatables-basic table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="text-align: center; width: 80px;">Sl No</th>
                            <th>Name</th>
                            <th style="text-align: center;">Total Tour</th>
                            <th style="text-align: right;">Tour Price</th>
                            <th style="text-align: right;">Tour Discount</th>
                            <th style="text-align: right;">Final Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($alltours as $index => $tour)
                            <tr>
                                <td style="text-align: center;">{{ $index + 1 }}</td>
                                <td>@if (auth()->user()->userId == 1) Travclicks @endif</td>
                                <td style="text-align: center;" class="tour-count">{{ $summary['total_tours'] }}</td>
                                <td style="text-align: right;" class="tour-price">{{ number_format($tour['tour_price'], 2) }}</td>
                                <td>{{ number_format($tour['tour_discount'], 2) }}</td>
                                <td style="text-align: right;" class="final-amount">{{ number_format($tour['final_amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No tours available</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: bold; background: #f8f9fa;">
                            <td colspan="2" style="text-align: right;">Total:</td>
                            <td id="totalToursFooter" style="text-align: center;"></td>
                            <td id="totalPriceFooter" style="text-align: right;"></td>
                            <td id="totalDiscountFooter" style="text-align: right;"></td>
                            <td id="finalAmountFooter" style="text-align: right;"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')

<!-- DataTable & Date Libraries -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<!-- Toastr Notification Library -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    // Initialize toastr settings
    $(document).ready(function(){
        // Configure toastr options
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-right",
            timeOut: 3000
        };
    });
    
    // Fallback function in case toastr isn't loaded
    function showNotification(type, message) {
        if (typeof toastr !== 'undefined') {
            if (type === 'success') {
                toastr.success(message);
            } else if (type === 'error') {
                toastr.error(message);
            } else if (type === 'warning') {
                toastr.warning(message);
            } else if (type === 'info') {
                toastr.info(message);
            }
        } else {
            console.log(type + ': ' + message);
        }
    }
</script>
<!-- End Toastr -->

<!-- DataTables Initialization Script -->
<script>
    // Remove this script block as we have a consolidated initialization below
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

        // Update input value when date range is applied
        $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            // Will trigger the change event which will call applyFilters()
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
                            showNotification('success', 'Countries loaded successfully!');
                        } else {
                            showNotification('error', 'Error loading countries: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        showNotification('error', 'Failed to load countries');
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
                        showNotification('error', 'Error loading Master DMCs');
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
                        showNotification('error', 'Error loading DMCs');
                    }
                });
            } else {
                userTypeDropdown.prop('disabled', true);
                countryDropdown.prop('disabled', true);
            }
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
        });
    });
</script>

<!-- sum and count data in data table -->
<script>
var dataTable;

$(document).ready(function () {
    // Initialize DataTable function
    function initializeDataTable() {
        // If table is already initialized, destroy it
        if ($.fn.DataTable.isDataTable('.datatables-basic')) {
            $('.datatables-basic').DataTable().destroy();
        }
        
        // Initialize with minimal settings
        dataTable = $('.datatables-basic').DataTable({
            processing: true,
            responsive: true,
            // Don't add buttons here - we'll use the custom export dropdown
            lengthMenu: [10, 25, 50, 100],
            footerCallback: function (row, data, start, end, display) {
                var api = this.api();

                // Get data from cells directly for better reliability
                function getColumnTotals(col) {
                    let total = 0;
                    $('.datatables-basic tbody tr').each(function() {
                        let cellText = $(this).find('td').eq(col).text().trim();
                        if (cellText) {
                            // Remove commas and any non-numeric characters except decimal point
                            let value = parseFloat(cellText.replace(/,/g, '').replace(/[^0-9.-]/g, ''));
                            if (!isNaN(value)) {
                                total += value;
                            }
                        }
                    });
                    return total;
                }

                // Calculate totals directly from the DOM
                let totalTours = getColumnTotals(2);
                let totalPrice = getColumnTotals(3);
                let totalDiscount = getColumnTotals(4);
                let finalAmount = getColumnTotals(5);

                // Update footer cells
                $('#totalToursFooter').text(totalTours);
                $('#totalPriceFooter').text(totalPrice.toLocaleString(undefined, {minimumFractionDigits: 2}));
                $('#totalDiscountFooter').text(totalDiscount.toLocaleString(undefined, {minimumFractionDigits: 2}));
                $('#finalAmountFooter').text(finalAmount.toLocaleString(undefined, {minimumFractionDigits: 2}));
            }
        });
        
        // Connect export buttons to DataTable functions
        $('#exportCopy').on('click', function() {
            navigator.clipboard.writeText(getTableText());
            showNotification('success', 'Table data copied to clipboard');
        });
        
        // Simple function to get table data as text
        function getTableText() {
            let text = '';
            // Add headers
            $('.datatables-basic thead th').each(function() {
                text += $(this).text() + '\t';
            });
            text += '\n';
            
            // Add rows
            $('.datatables-basic tbody tr').each(function() {
                $(this).find('td').each(function() {
                    text += $(this).text() + '\t';
                });
                text += '\n';
            });
            
            return text;
        }
        
        return dataTable;
    }

    // Shared function for updating the table with any response data
    function updateTable(tours, summary) {
        if (!tours || tours.length === 0) {
            $('.datatables-basic tbody').html(`<tr><td colspan="6" class="text-center">No tours available</td></tr>`);
            showNotification('warning', 'No tours found.');
            initializeDataTable();
            return;
        }
        
        // Update the table body
                    let tableBody = $('.datatables-basic tbody');
        tableBody.empty();
        
        // Create HTML for rows
        tours.forEach((tour, index) => {
            // Get the correct property - some responses use total_tours, others use total_tour
            const tourCount = tour.total_tours || tour.total_tour || 0;
            
                            tableBody.append(`
                                <tr>
                    <td style="text-align: center;">${index + 1}</td>
                    <td>{{ auth()->user()->userId == 1 ? 'Travclicks' : $tour->name }}</td>
                    <td style="text-align: center;" class="tour-count">${tourCount}</td>
                    <td style="text-align: right;" class="tour-price">${parseFloat(tour.tour_price).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                    <td style="text-align: right;" class="tour-discount">${parseFloat(tour.tour_discount).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                    <td style="text-align: right;" class="final-amount">${parseFloat(tour.final_amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                                </tr>
                            `);
                        });
        
        showNotification('success', 'Tour list updated successfully!');
        
        // Reinitialize DataTable after updating the content
        initializeDataTable();
        
        // Force footer calculation (in case DataTable didn't trigger it)
        updateFooterTotals();
    }
    
    // Function to manually update footer totals
    function updateFooterTotals() {
        function getColumnTotals(col) {
            let total = 0;
            $('.datatables-basic tbody tr').each(function() {
                let cellText = $(this).find('td').eq(col).text().trim();
                if (cellText) {
                    // Remove commas and any non-numeric characters except decimal point
                    let value = parseFloat(cellText.replace(/,/g, '').replace(/[^0-9.-]/g, ''));
                    if (!isNaN(value)) {
                        total += value;
                    }
                }
            });
            return total;
        }

        // Calculate totals directly from the DOM
        let totalTours = getColumnTotals(2);
        let totalPrice = getColumnTotals(3);
        let totalDiscount = getColumnTotals(4);
        let finalAmount = getColumnTotals(5);

        // Update footer cells
        $('#totalToursFooter').text(totalTours);
        $('#totalPriceFooter').text(totalPrice.toLocaleString(undefined, {minimumFractionDigits: 2}));
        $('#totalDiscountFooter').text(totalDiscount.toLocaleString(undefined, {minimumFractionDigits: 2}));
        $('#finalAmountFooter').text(finalAmount.toLocaleString(undefined, {minimumFractionDigits: 2}));
    }

    // Initial table setup
    initializeDataTable();
    updateFooterTotals(); // Calculate initial values

    // Filter handlers
    $('#statusFilter, #countryFilter, #roleFilter, #userTypeFilter, #dateRange').on('change apply.daterangepicker', function (event, picker) {
        applyFilters();
    });

    // Handle date range picker cancel
    $('#dateRange').on('cancel.daterangepicker', function () {
            $(this).val('');
        applyFilters();
    });
    
    // Function to gather all filter values and send the request
    function applyFilters() {
        // Show loading indicator
        $('.datatables-basic tbody').html('<tr><td colspan="6" class="text-center">Loading data...</td></tr>');
        
        // Gather all filter values
        const filters = {
            status: $('#statusFilter').val(),
            country: $('#countryFilter').val(),
            role: $('#roleFilter').val(),
            userType: $('#userTypeFilter').val(),
            _token: "{{ csrf_token() }}"
        };
        
        // Add date range if set
        const dateRangeValue = $('#dateRange').val();
        if (dateRangeValue) {
            filters.dateRange = dateRangeValue;
        }
        
        // Send request to get filtered data
        $.ajax({
            url: "{{ route('reports.get-filtered-data') }}",
            method: 'GET',
            data: filters,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    // Determine which data to display based on status filter
                    let dataToShow;
                    const status = $('#statusFilter').val();
                    
                    if (status === '1') {
                        dataToShow = response.completedTours;
                    } else if (status === '2') {
                        dataToShow = response.progressTours;
                    } else if (status === '3') {
                        dataToShow = response.cancelTours;
                    } else if (status === '4') {
                        dataToShow = response.enquiredTours;
                    } else {
                        // If no status specified or invalid, show all completed tours as default
                        dataToShow = response.completedTours || response.allTours;
                    }
                    
                    if (dataToShow && dataToShow.length > 0) {
                        updateTableDirectly(dataToShow, response.summary);
                    } else {
                        $('.datatables-basic tbody').html(`<tr><td colspan="6" class="text-center">No matching tours found</td></tr>`);
                        calculateFooterTotals();
                    }
                } else {
                    showNotification('error', response.message || 'Error fetching data');
                    $('.datatables-basic tbody').html(`<tr><td colspan="6" class="text-center">Error loading data</td></tr>`);
                    calculateFooterTotals();
                    }
                },
                error: function (xhr) {
                    console.error('Error fetching tours:', xhr);
                showNotification('error', 'Failed to load tour data.');
                $('.datatables-basic tbody').html(`<tr><td colspan="6" class="text-center">Error loading data</td></tr>`);
                calculateFooterTotals();
            }
        });
    }
    
    // This function updates the table and footer directly without DataTable destroying/reinitializing
    function updateTableDirectly(tours, summary) {
        if (!tours || tours.length === 0) {
            $('.datatables-basic tbody').html(`<tr><td colspan="6" class="text-center">No tours available</td></tr>`);
            showNotification('warning', 'No tours found.');
            calculateFooterTotals();
            return;
        }
        
        // Build HTML for table rows
        let html = '';
        tours.forEach((tour, index) => {
            // Get the correct property - some responses use total_tours, others use total_tour
            const tourCount = tour.total_tours || tour.total_tour || 0;
            
            html += `
                <tr>
                    <td style="text-align: center;">${index + 1}</td>
                    <td>{{ auth()->user()->userId == 1 ? 'Travclicks' : $tour->name }}</td>
                    <td style="text-align: center;" class="tour-count">${tourCount}</td>
                    <td style="text-align: right;" class="tour-price">${parseFloat(tour.tour_price).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                    <td style="text-align: right;" class="tour-discount">${parseFloat(tour.tour_discount).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                    <td style="text-align: right;" class="final-amount">${parseFloat(tour.final_amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                </tr>
            `;
        });
        
        // Update the table body
        $('.datatables-basic tbody').html(html);
        
        // Update the footer totals
        calculateFooterTotals();
        
        showNotification('success', 'Tour list updated successfully!');
    }
    
    // Calculate footer totals directly from the table content
    function calculateFooterTotals() {
        let totalTours = 0;
        let totalPrice = 0;
        let totalDiscount = 0;
        let finalAmount = 0;
        
        // Calculate sums from the table rows
        $('.datatables-basic tbody tr').each(function() {
            // Skip rows with no data or error messages
            if ($(this).find('td').length < 6) return;
            
            // Parse the numeric values from cells
            const tourCount = parseFloat($(this).find('td:eq(2)').text().replace(/,/g, '')) || 0;
            const tourPrice = parseFloat($(this).find('td:eq(3)').text().replace(/,/g, '')) || 0;
            const tourDiscount = parseFloat($(this).find('td:eq(4)').text().replace(/,/g, '')) || 0;
            const tourFinalAmount = parseFloat($(this).find('td:eq(5)').text().replace(/,/g, '')) || 0;
            
            // Add to totals
            totalTours += tourCount;
            totalPrice += tourPrice;
            totalDiscount += tourDiscount;
            finalAmount += tourFinalAmount;
        });
        
        // Update the footer cells
        $('#totalToursFooter').text(totalTours);
        $('#totalPriceFooter').text(totalPrice.toLocaleString(undefined, {minimumFractionDigits: 2}));
        $('#totalDiscountFooter').text(totalDiscount.toLocaleString(undefined, {minimumFractionDigits: 2}));
        $('#finalAmountFooter').text(finalAmount.toLocaleString(undefined, {minimumFractionDigits: 2}));
    }
    
    // Call calculateFooterTotals on initial page load
    calculateFooterTotals();
    
    // Connect export buttons to actions
    $('#exportCSV').on('click', function() {
        exportTable('csv');
    });
    
    $('#exportExcel').on('click', function() {
        exportTable('excel');
    });
    
    $('#exportPDF').on('click', function() {
        exportTable('pdf');
    });
    
    $('#exportPrint').on('click', function() {
        window.print();
    });
    
    // Simple export function
    function exportTable(type) {
        let filename = 'tour_report_' + new Date().toISOString().slice(0, 10);
        let csvContent = '';
        
        // Add headers
        let headers = [];
        $('.datatables-basic thead th').each(function() {
            headers.push($(this).text().trim());
        });
        csvContent += headers.join(',') + '\n';
        
        // Add rows
        $('.datatables-basic tbody tr').each(function() {
            let row = [];
            $(this).find('td').each(function() {
                // Clean the data for CSV
                let cellText = $(this).text().trim().replace(/,/g, ' ');
                row.push(cellText);
            });
            csvContent += row.join(',') + '\n';
        });
        
        // Create and trigger download
        let blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        let link = document.createElement('a');
        let url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename + '.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    });
</script>
<!-- End sum  -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
@endsection