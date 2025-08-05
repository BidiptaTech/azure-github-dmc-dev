@extends('layouts.layout')
@extends('layouts.datatablecss')
@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-body">
                {{-- @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif --}}

                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Port Listing</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                        <a href="{{ route('ports.create') }}" class="btn btn-primary">
                            <i class="mdi mdi-plus me-1"></i>Add New Port
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

                {{-- <div class="table-responsive text-nowrap"> --}}
                    {{-- <table class="table table-hover" > --}}
                        <table class="datatables-basic table table-bordered" id="portsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Port Name</th>
                                <th>Type</th>
                                <th>Country</th>
                                <th>City</th>
                                <th>Latitude</th>
                                <th>Longitude</th>
                                {{-- <th>Distance (miles)</th> --}}
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach($ports as $index => $port)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $port->port_name }}</td>
                                <td>{{ $port->type }}</td>
                                <td>{{ $port->country ?? 'N/A' }}</td>
                                <td>{{ $port->city->name ?? 'N/A' }}</td>
                                <td>{{ $port->latitude }}</td>
                                <td>{{ $port->longitude }}</td>
                                {{-- <td>{{ $port->distance }}</td> --}}
                                <td>
                                    <form action="{{ route('port.toggle-status', $port->port_id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $port->status ? 'btn-success' : 'btn-danger' }}">
                                            {{ $port->status ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <!-- View -->
                                        <a href="{{ route('ports.show', $port->port_id) }}" 
                                           class="btn btn-info btn-sm rounded-circle d-flex justify-content-center align-items-center"
                                           style="width: 28px; height: 28px; padding: 0;" title="View">
                                            <i class="ri-eye-line" style="font-size: 16px;"></i>
                                        </a>

                                        <!-- Edit -->
                                        <a href="{{ route('ports.edit', $port->port_id) }}" 
                                           class="btn btn-primary btn-sm rounded-circle d-flex justify-content-center align-items-center"
                                           style="width: 28px; height: 28px; padding: 0;" title="Edit">
                                            <i class="ri-pencil-line" style="font-size: 16px;"></i>
                                        </a>

                                        <!-- Delete -->
                                        <form action="{{ route('ports.destroy', $port->port_id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-danger btn-sm rounded-circle d-flex justify-content-center align-items-center"
                                                    style="width: 28px; height: 28px; padding: 0;" title="Delete"
                                                    onclick="return confirm('Are you sure you want to delete this port?')">
                                                <i class="ri-delete-bin-line" style="font-size: 16px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                {{-- </div> --}}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
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
{{-- <script>
    $(document).ready(function() {
        $('#portsTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [10, 25, 50, 100],
            "ordering": true,
            "info": true,
            "searching": true,
            "responsive": true
        });
    });
</script> --}}
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
@endsection 