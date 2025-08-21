@extends('layouts.layout')
@section('title', 'Drivers')
@extends('layouts.datatablecss')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Drivers</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        {{-- @if($pendingDrivers->count() > 0 && ($user->user_type == 1)) --}}
                            {{-- <a href="#" data-bs-toggle="modal" data-bs-target="#approveModal" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="24" height="24"><path fill="#f5ad47" d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7 .2 40.1S486.3 480 472 480L40 480c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8 .2-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24l0 112c0 13.3 10.7 24 24 24s24-10.7 24-24l0-112c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/></svg>
                                <div data-i18n="Approve Driver">
                                    Approve Driver
                                </div>
                            </a> --}}
                        {{-- @endif --}}
                        <!-- Add New Driver Button -->
                        @if(hasPermission('create driver'))
                            <a href="{{ route('driver.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                                <i class="fas fa-plus"></i> Add New Driver
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
                            <th>No</th>
                            <th>Driver Name</th>
                            @php
                                $roleId = auth()->user()->role_id;
                            @endphp

                            @if($roleId == 10)
                                <th>DMC</th>
                            @elseif($roleId != 11)
                                <th>Master Dmc</th>
                                <th>DMC</th>
                            @endif
                            <th>City</th>
                            <th>Driver Mobile No</th>
                            <th>Email</th>
                            <th>License No</th>
                            <th>Calendar</th>
                            <th>Status</th>
                            @if(auth()->user()->role_id == 1 || auth()->user()->userId == 2 || auth()->user()->role_id == 46  || auth()->user()->role_id == 23 || auth()->user()->role_id == 76 || auth()->user()->role_id ==109 || auth()->user()->role_id == 111 || hasPermission('edit driver') || hasPermission('delete driver'))
                                <th>Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($drivers as $key => $driver)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td class="driver-name">{{ $driver->name }} </td>
                                @php
                                    $roleId = auth()->user()->role_id;
                                @endphp

                                @if($roleId == 10)
                                    @php
                                        $dmcUser = App\Models\User::where('userId', $driver->dmc_id)->first();
                                    @endphp
                                    <td>{{ $dmcUser ? $dmcUser->company_name : 'N/A' }}</td>
                                @elseif($roleId != 11)
                                    @php
                                        $dmcUser = App\Models\User::where('userId', $driver->dmc_id)->first();
                                        $masterdmcUser = $dmcUser ? App\Models\User::where('userId', $dmcUser->master_dmc_id)->first() : null;
                                    @endphp
                                    <td>{{ $masterdmcUser ? $masterdmcUser->company_name : 'N/A' }}</td>
                                    <td>{{ $dmcUser ? $dmcUser->company_name : 'N/A' }}</td>
                                @endif
                                <td>{{ $driver->city }}</td>
                                <td>{{ $driver->phone }}</td>
                                <td>{{ $driver->email }}</td>
                                <td>{{ $driver->license_no }}</td>
                                <td> 
                                    <a href="{{ route('driver.calendar', Crypt::encrypt($driver->driver_id)) }}" target="_blank"><i class="fa fa-calendar-alt"></i>View Calendar</a></td>
                                </td>
                                <td>
                                    @if($driver->is_active == 1)
                                        <span class="badge bg-success">Available</span>
                                    @else
                                        <span class="badge bg-danger">Not Available</span>
                                    @endif
                                </td>
                                @if(hasPermission('edit driver') || hasPermission('delete driver'))
                                    @if($driver->status == 1)
                                        <td style="display: inline-block; white-space: nowrap;">
                                            <!-- Edit Button -->
                                            @if(hasPermission('edit driver'))
                                            <a href="{{ route('driver.edit', Crypt::encrypt($driver->driver_id)) }}" 
                                            class="btn btn-primary btn-sm rounded-circle waves-effect waves-light" 
                                            style="min-width: 28px; min-height: 28px; padding: 0;">
                                                <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                                    <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/>
                                                </svg>
                                            </a>
                                            @endif

                                            <!-- Delete Button -->
                                            @if(hasPermission('delete driver'))
                                            <button type="button" 
                                                class="btn btn-danger btn-sm rounded-circle waves-effect waves-light" 
                                                style="min-width: 28px; min-height: 28px; padding: 0;" 
                                                data-toggle="modal" 
                                                data-target="#deleteModal" 
                                                onclick="setDeleteForm('{{ route('driver.destroy', Crypt::encrypt($driver->driver_id)) }}')">
                                                <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                                    <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </td>
                                    @else

                                            {{-- @if(Auth::user()->role_id == 11)
                                            <td>
                                                @if($driver->status == 2)
                                                    <span>Pending approval from the A.M</span>
                                                @elseif($driver->status == 4)
                                                    <span>Awaiting S.M approval</span>
                                                @elseif($driver->status == 5)
                                                    <span>Awaiting Admin approval</span>
                                                @elseif($driver->status == 3)
                                                    <span>Declined</span>
                                                @endif
                                            </td>
                                            @endif  
                                            @if(Auth::user()->role_id == 4)
                                                <td>
                                                    @if($driver->status == 4)
                                                        <span>Awaiting S.M approval</span>
                                                    @elseif($driver->status == 5)
                                                        <span>Awaiting Admin approval</span>
                                                    @elseif($driver->status == 3)
                                                        <span>Declined</span>
                                                    @endif
                                                </td>
                                            @endif

                                            @if(Auth::user()->role_id == 3)
                                                <td>
                                                    @if($driver->status == 5)
                                                        <span>Awaiting Admin approval</span>
                                                    @elseif($driver->status == 3)
                                                        <span>Declined</span>
                                                    @endif
                                                </td>
                                            @endif --}}

                                            <td>
                                                @if($driver->status == 5)
                                                    <span>Your Driver, awaiting for Admin approval</span>
                                                @elseif($driver->status == 3)
                                                    <span>Declined</span>
                                                @endif
                                            </td>
                                    @endif
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>





<!-- driver Delete Modal -->
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

<!--AJAX Call to approve driver-->

<script>
    $(document).ready(function() {
        $('#approveTable').DataTable({
            responsive: true,
            ordering: false,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            },
            lengthMenu: [10, 25, 50, 100],
        });
    
        $(".approve-btn, .decline-btn").click(function() {
            let driverId = $(this).data("id");
            let action = $(this).data("action");
    
            $.ajax({
                url: "/driver/approve-or-decline/" + driverId,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    action: action
                },
                success: function(response) {
                    if (response.success) {
                        let driverRow = $("#driver-" + driverId);
                        driverRow.find(".approve-btn, .decline-btn").remove();
                        driverRow.find(".status-message").text(action === 'approve' ? "Guide Approved!" : "Guide Declined!").css("color", action === 'approve' ? "green" : "red").show();
                    } else {
                        alert("Error processing request.");
                    }
                },
                error: function() {
                    alert("Something went wrong!");
                }
            });
        });
    });
    </script>
@endsection
