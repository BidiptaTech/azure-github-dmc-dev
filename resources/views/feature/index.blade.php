@extends('layouts.layout')
@section('title', 'Features')
@extends('layouts.datatablecss')
<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
<style>
#toast-container { z-index: 999999 !important; }
#toast-container.toast-top-right { top: 70px; right: 12px; }
/* Override Bootstrap .toast styles that conflict with Toastr, without breaking auto-hide */
#toast-container .toast { 
    /* Do NOT force display/opacity; let Toastr control them */
    padding: 12px 15px !important;
    border-radius: 4px !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2) !important;
    color: #fff !important;
    background-image: none !important; /* remove default toastr icon */
    padding-left: 15px !important; /* remove left space reserved for icon */
}
#toast-container .toast-success { background-color: #28a745 !important; background-image: none !important; }
#toast-container .toast-error { background-color: #dc3545 !important; background-image: none !important; }
#toast-container .toast-info { background-color: #17a2b8 !important; background-image: none !important; }
#toast-container .toast-warning { background-color: #ffc107 !important; color: #212529 !important; background-image: none !important; }
#toast-container .toast-title, 
#toast-container .toast-message { color: inherit !important; }
</style>
@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card">
                <div class="card-datatable table-responsive pt-0">
                    <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                        <div class="d-flex align-items-center">
                            <h5 class="card-title mb-0">Features Listing</h5>
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
                                <th>Feature</th>
                                <th>Assign Roles</th>
                                <th width="280px">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($features as $key => $f)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td>{{ $f->name }}</td>
                                    <td>
                                        <!-- Button to open modal -->
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#featureModal{{ $f->id }}">
                                            View Roles
                                        </button>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" style="min-width: 14% !important" type="checkbox" id="flexSwitchCheckChecked_{{ $f->id }}" 
                                                @if($f->status == 1) checked @endif 
                                                onclick="checkStatus(this, '{{ $f->id }}')">
                                            <label class="form-check-label" for="flexSwitchCheckChecked_{{ $f->id }}"></label>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals Section -->
    @foreach ($features as $f)
        <div class="modal fade" id="featureModal{{ $f->id }}" tabindex="-1" aria-labelledby="featureModalLabel{{ $f->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="featureModalLabel{{ $f->id }}">{{ $f->name }} Roles</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('save-feature-roles', $f->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <!-- Search Box -->
                            {{-- <div class="mb-3">
                                <input type="text" class="form-control" id="roleSearch{{ $f->id }}" 
                                    placeholder="Search roles..." data-feature-id="{{ $f->id }}">
                            </div> --}}

                            <!-- Hidden input to send empty array if no checkboxes are checked -->
                            <input type="hidden" name="roles[]" value="">

                            <div id="rolesContainer{{ $f->id }}" class="roles-list">
                                @foreach($roles as $role)
                                    <div class="form-check role-item">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->role_id }}"
                                            @if(is_array(json_decode($f->feature_roles)) && in_array($role->role_id, json_decode($f->feature_roles))) 
                                                checked 
                                            @endif>
                                        <label>{{ $role->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endforeach
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

        let searchTimer;

        // Function to handle role search
        $('[id^="roleSearch"]').on('keyup', function() {
            const searchText = $(this).val();
            const featureId = $(this).data('feature-id');
            const rolesContainer = $(`#rolesContainer${featureId}`);
            
            // Clear previous timeout
            clearTimeout(searchTimer);

            // Set new timeout (300ms delay)
            searchTimer = setTimeout(function() {
                if (searchText.length >= 1) {
                    // Show loading indicator
                    rolesContainer.html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');

                    // Make AJAX call
                    $.ajax({
                        url: '{{ env("APP_URL") }}' + '/search-roles',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            search: searchText,
                            feature_id: featureId
                        },
                        success: function(response) {
                            let html = '';
                            if (response.roles.length > 0) {
                                response.roles.forEach(function(role) {
                                    const isChecked = response.feature_roles.includes(role.role_id) ? 'checked' : '';
                                    html += `
                                        <div class="form-check role-item">
                                            <input class="form-check-input" type="checkbox" name="roles[]" 
                                                value="${role.role_id}" ${isChecked}>
                                            <label>${role.name}</label>
                                        </div>
                                    `;
                                });
                            } else {
                                html = '<div class="text-center">No roles found</div>';
                            }
                            rolesContainer.html(html);
                        },
                        error: function(xhr, status, error) {
                            notify.error('Error searching roles: ' + error);
                            rolesContainer.html('<div class="text-center text-danger">Error loading roles</div>');
                        }
                    });
                } else if (searchText.length === 0) {
                    // If search is empty, reload all roles
                    $.ajax({
                        url: '{{ env("APP_URL") }}' + '/get-all-roles',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            feature_id: featureId
                        },
                        success: function(response) {
                            let html = '';
                            response.roles.forEach(function(role) {
                                const isChecked = response.feature_roles.includes(role.role_id) ? 'checked' : '';
                                html += `
                                    <div class="form-check role-item">
                                        <input class="form-check-input" type="checkbox" name="roles[]" 
                                            value="${role.role_id}" ${isChecked}>
                                        <label>${role.name}</label>
                                    </div>
                                `;
                            });
                            rolesContainer.html(html);
                        }
                    });
                }
            }, 300);
        });
    });
</script>
<!-- End DataTable JS -->  
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<!-- DataTable Scripts -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<!-- Toastr JS (optional, used if available) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    // Ensure toastr has sane defaults and appears above footer
    if (window.toastr) {
        toastr.options = Object.assign({
            positionClass: 'toast-top-right',
            timeOut: 1000,
            extendedTimeOut: 100,
            hideMethod: 'fadeOut',
            hideDuration: 300,
            closeButton: true,
            newestOnTop: false,
            progressBar: true,
            preventDuplicates: true,
            tapToDismiss: true
        }, toastr.options || {});
    }

    const notify = {
        success: function(message) {
            if (window.toastr && typeof toastr.success === 'function') {
                toastr.success(message || 'Success');
            } else {
                alert(message || 'Success');
            }
        },
        error: function(message) {
            if (window.toastr && typeof toastr.error === 'function') {
                toastr.error(message || 'Something went wrong');
            } else {
                alert(message || 'Something went wrong');
            }
        }
    };
</script>

<script>
    function checkStatus(checkbox, id) {
        const newStatus = checkbox.checked ? 1 : 0;
        checkbox.disabled = true; // Disable the checkbox while the request is in progress

        fetch('{{ url('/update-status') }}', { 
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                status: newStatus,
                id: id 
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'An error occurred while updating the status.');
                });
            }
            return response.json();
        })
        .then(data => {
            // If the response is successful, enable the checkbox
            notify.success((data && data.message) ? data.message : 'Status updated successfully');
            checkbox.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            notify.error(error && error.message ? error.message : 'An error occurred while updating the status.');

            // Revert the checkbox state if there is an error
            checkbox.checked = !checkbox.checked;
            checkbox.disabled = false;
        });
    }
</script>
@endsection
