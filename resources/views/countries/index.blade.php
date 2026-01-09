@extends('layouts.layout')
@section('title', 'Country')
@extends('layouts.datatablecss')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Country Listing</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        <!-- Add New Country Button -->
                        {{-- @if(hasPermission('create country')) --}}
                            <a href="{{ route('countries.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                                <i class="fas fa-plus"></i> Add New Country
                            </a>
                        {{-- @endif --}}

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
                            <th>Country Name</th>
                            <th>Country Code</th>
                            <th>Currency</th>
                            <th>Tax Percentage</th>
                            <th>Gateway Percentage</th>
                            <th>Commission Percentage</th>
                            <th>Status</th>
                            {{-- @if(hasPermission('edit country') || hasPermission('delete country')) --}}
                                <th>Action</th>
                            {{-- @endif --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($countries as $key => $country)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td class="country-name">{{ $country->name }}</td>
                                <td class="city-name">{{ $country->country_code }}</td>
                                <td>{{ $country->currency }}</td>
                                <td>{{ $country->tax_percentage }}</td>
                                
                                <td>{{ $country->gateway_percentage }}</td>
                                <td>{{ $country->commission_percentage }}</td>
                                <td>
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input status-toggle" type="checkbox" role="switch" 
                                               data-id="{{ $country->id }}" 
                                               {{ $country->is_active ? 'checked' : '' }}
                                               style="width: 2.5em; height: 1.25em; cursor: pointer;">
                                    </div>
                                </td>
                                {{-- @if(hasPermission('edit country') || hasPermission('delete country')) --}}
                                <td style="display: inline-block; white-space: nowrap;">
                                    <!-- Edit Button -->
                                    {{-- @if(hasPermission('edit country')) --}}
                                    <a href="{{ route('countries.edit',  Crypt::encrypt($country->id)) }}" 
                                    class="btn btn-primary btn-sm rounded-circle waves-effect waves-light" 
                                    style="min-width: 28px; min-height: 28px; padding: 0;">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                            <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/>
                                        </svg>
                                    </a>
                                    {{-- @endif --}}

                                    <!-- Delete Button -->
                                    {{-- @if(hasPermission('delete country')) --}}
                                    {{-- <button type="button" 
                                            class="btn btn-danger btn-sm rounded-circle waves-effect waves-light" 
                                            style="min-width: 28px; min-height: 28px; padding: 0;" 
                                            data-toggle="modal" 
                                            data-target="#deleteModal" 
                                            onclick="setDeleteForm('{{ route('countries.destroy', $country->id) }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                            <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                        </svg>
                                    </button> --}}
                                    {{-- @endif --}}
                                </td>
                                {{-- @endif --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Vehicle Delete Modal -->
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
<!-- End Modal -->
@endsection

@section('styles')
<!-- Add Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endsection

@section('scripts')
<!-- Add Toastr JS before your other scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- Configure Toastr -->
<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "3000"
    };
</script>

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

        // Status toggle functionality
        $(document).on('change', '.status-toggle', function() {
            const countryId = $(this).data('id');
            const isActive = $(this).prop('checked') ? 1 : 0;
            const toggleElement = $(this);

            // Disable the toggle while processing to prevent multiple clicks
            toggleElement.prop('disabled', true);

            $.ajax({
                url: "{{ route('countries.toggle-status') }}",
                type: 'POST',
                dataType: 'json',
                data: {
                    id: countryId,
                    is_active: isActive,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    toggleElement.prop('disabled', false);
                    if (response.success) {
                        toastr.success('Status updated successfully!');
                    } else {
                        toggleElement.prop('checked', !isActive);
                        toastr.error(response.message || 'Error updating status');
                    }
                },
                error: function(xhr, status, error) {
                    toggleElement.prop('disabled', false);
                    toggleElement.prop('checked', !isActive);
                    toastr.error('An error occurred while updating status');
                    console.error("Error details:", xhr.responseText);
                }
            });
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
@endsection
