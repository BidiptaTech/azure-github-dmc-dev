@extends('layouts.layout')
@section('title', 'Local Transport')
@extends('layouts.datatablecss')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Local Transport Listing</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        <!-- Add New Local Transport Button -->
                        @if(hasPermission('create transport'))
                            <a href="{{ route('transport.create') }}"
                                class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                                <i class="fas fa-plus"></i> Add New Local Transport
                            </a>
                        @endif

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
                            <th>Vehicle Name</th>
                            <th>Driver Name</th>
                            <th>Driver Contact No</th>
                            <th>License No</th>
                            <th>Vehicle No</th>
                            <th>Status</th>
                            @if(hasPermission('edit transport') || hasPermission('delete transport'))
                                <th>Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transports as $transport)
                        <tr>
                            <td class="category-name">{{ $transport->vehicle_name }}</td>
                            <td class="category-name">{{$transport->driver_name}}</td>
                            <td>
                                @if($transport->contact_no)
                                {{ $transport->contact_no }}
                                @else
                                <span class="badge bg-danger">No details</span>
                                @endif
                            </td>
                            <td>
                                @if($transport->license_no)
                                {{$transport->license_no}}
                                @else
                                <span class="badge bg-danger">No details</span>
                                @endif
                            </td>
                            <td>
                                @if($transport->vehicle_no)
                                {{$transport->vehicle_no}}
                                @else
                                <span class="badge bg-danger">No details</span>
                                @endif
                            </td>
                            <td>
                                @if($transport->is_active == 1)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            @if(hasPermission('edit transport') || hasPermission('delete transport'))
                            <td class="d-flex gap-2">
                                <!-- Edit Button -->
                                @if(hasPermission('edit transport'))
                                    <a href="{{ route('transport.edit', $transport->transport_id) }}"
                                        class="btn btn-primary btn-sm d-flex align-items-center justify-content-center rounded-circle"
                                        style="width: 28px; height: 28px; padding: 0;">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960"
                                            width="16px" fill="#ffffff">
                                            <path
                                                d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z" />
                                        </svg>
                                    </a>
                                @endif

                                    <!-- Delete Button -->
                                    @if(hasPermission('delete transport'))
                                    <button type="button" 
                                            class="btn btn-danger btn-sm d-flex align-items-center justify-content-center rounded-circle" 
                                            style="width: 28px; height: 28px; padding: 0;" 
                                            data-toggle="modal" 
                                            data-target="#deleteModal" 
                                            onclick="setDeleteForm('{{ route('transport.destroy', $transport->transport_id) }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                            <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                        </svg>
                                    </button>
                                    @endif
                                </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- transport Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" Category="dialog" aria-labelledby="deleteModalLabel"
    aria-hidden="true">
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

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
function setDeleteForm(action) {
    document.getElementById('deleteForm').action = action;
}
</script>
@endsection