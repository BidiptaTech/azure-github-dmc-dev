@extends('layouts.layout')
@section('title', 'Guide')
@extends('layouts.datatablecss')


@section('content')

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Guide Listing</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        {{-- @if($pendingGuides->count() > 0 && ($user->user_type == 1 || $user->user_type == 2)) --}}
                            {{-- <a href="#" data-bs-toggle="modal" data-bs-target="#approveModal" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="24" height="24"><path fill="#f5ad47" d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7 .2 40.1S486.3 480 472 480L40 480c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8 .2-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24l0 112c0 13.3 10.7 24 24 24s24-10.7 24-24l0-112c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/></svg>
                                <div data-i18n="Approve Guide">
                                    Approve Guide
                                </div>
                            </a> --}}
                            
                        {{-- @endif --}}
                        <!-- Add New Category Button -->
                        @if(hasPermission('create guide'))
                            <a href="{{ route('guide.create') }}"
                                class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                                <i class="fas fa-plus"></i> Add New Guide
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
                <hr>

                <table class="datatables-basic table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Name</th>
                            @php
                                $roleId = auth()->user()->role_id;
                            @endphp

                            @if($roleId == 10)
                                <th>DMC</th>
                            @elseif($roleId != 11)
                                <th>Master Dmc</th>
                                <th>DMC</th>
                            @endif
                            <th>Contact No</th>
                            <th>Email</th>
                            <th>Languages</th>
                            {{-- <th>Description</th> --}}
                            <th>Calendar</th>
                            <th>Status</th>
                             @if(auth()->user()->role_id == 1 || auth()->user()->userId == 2 || auth()->user()->role_id == 23  || auth()->user()->role_id == 75 || auth()->user()->role_id == 45 || auth()->user()->role_id ==100 || auth()->user()->role_id == 102 || hasPermission('edit guide') || hasPermission('delete guide'))
                            <th>Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($guides as $key => $guide)
                        <tr>
                            <td>{{ ++$key }}</td>
                            <td class="category-name">{{ $guide->name }}</td>
                            @php
                                $roleId = auth()->user()->role_id;
                            @endphp

                            @if($roleId == 10)
                                @php
                                    $dmcUser = App\Models\User::where('userId', $guide->dmc_id)->first();
                                @endphp
                                <td>{{ $dmcUser ? $dmcUser->company_name : 'N/A' }}</td>
                            @elseif($roleId != 11)
                                @php
                                    $dmcUser = App\Models\User::where('userId', $guide->dmc_id)->first();
                                    $masterdmcUser = $dmcUser ? App\Models\User::where('userId', $dmcUser->master_dmc_id)->first() : null;
                                @endphp
                                <td>{{ $masterdmcUser ? $masterdmcUser->company_name : 'N/A' }}</td>
                                <td>{{ $dmcUser ? $dmcUser->company_name : 'N/A' }}</td>
                            @endif
                            <td>
                                @if($guide->contact_no)
                                    {{ $guide->contact_no }}
                                @else
                                    <span class="badge bg-danger">No details</span>
                                @endif
                            </td>
                            <td>
                                @if($guide->email)
                                    {{$guide->email}}
                                @else
                                    <span class="badge bg-danger">No details</span>
                                @endif
                            </td>
                            <td>
                                @if ($guide->languages->isNotEmpty())
                                    @foreach ($guide->languages as $index => $language)
                                        {{ $language->language }}{{ $index < $guide->languages->count() - 1 ? ', ' : '' }}
                                    @endforeach
                                @else
                                    <span>No languages selected</span>
                                @endif
                            </td>
                            
                            {{-- <td>
                                {{strip_tags($guide->description)}}
                            </td> --}}
                            <td> 
                                <a href="{{ route('guide.calendar', $guide->guide_id) }}" target="_blank"><i class="fa fa-calendar-alt"></i>View Calendar</a></td>
                            </td>
                            <td>
                                @if($guide->is_active == 1)
                                    <span class="badge bg-success">Active</span>
                                    @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                                @if(hasPermission('edit guide') || hasPermission('delete guide'))
                                    @if($guide->status == 1)
                                    <td style="display: inline-block; white-space: nowrap;">
                                        <!-- Edit Button -->
                                        @if(hasPermission('edit guide'))
                                        <a href="{{ route('guide.edit', $guide->guide_id) }}"
                                            class="btn btn-primary btn-sm rounded-circle waves-effect waves-light"
                                            style="min-width: 28px; min-height: 28px; padding: 0;">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960"
                                                width="16px" fill="#ffffff">
                                                <path
                                                    d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z">
                                                </path>
                                            </svg>
                                        </a>
                                        @endif
                                    
                                        <!-- Delete Button -->
                                        @if(hasPermission('delete guide'))
                                        <button type="button"
                                            class="btn btn-danger btn-sm rounded-circle waves-effect waves-light"
                                            style="min-width: 28px; min-height: 28px; padding: 0;" data-toggle="modal"
                                            data-target="#deleteModal"
                                            onclick="setDeleteForm('{{ route('guide.destroy', $guide->guide_id) }}')"
                                            fdprocessedid="ra9z3">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960"
                                                width="16px" fill="#ffffff">
                                                <path
                                                    d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z">
                                                </path>
                                            </svg>
                                        </button>
                                        @endif
                                    </td>
                                       @else

                                            {{-- @if(Auth::user()->role_id == 11)
                                            <td>
                                                @if($guide->status == 2)
                                                    <span>Pending approval from the A.M</span>
                                                @elseif($guide->status == 4)
                                                    <span>Awaiting S.M approval</span>
                                                @elseif($guide->status == 5)
                                                    <span>Awaiting Admin approval</span>
                                                @elseif($guide->status == 3)
                                                    <span>Declined</span>
                                                @endif
                                            </td>
                                            @endif  
                                            @if(Auth::user()->role_id == 4)
                                                <td>
                                                    @if($guide->status == 4)
                                                        <span>Awaiting S.M approval</span>
                                                    @elseif($guide->status == 5)
                                                        <span>Awaiting Admin approval</span>
                                                    @elseif($guide->status == 3)
                                                        <span>Declined</span>
                                                    @endif
                                                </td>
                                            @endif

                                            @if(Auth::user()->role_id == 3)
                                                <td>
                                                    @if($guide->status == 5)
                                                        <span>Awaiting Admin approval</span>
                                                    @elseif($guide->status == 3)
                                                        <span>Declined</span>
                                                    @endif
                                                </td>
                                            @endif --}}

                                            <td>
                                                @if($guide->status == 5)
                                                    <span>Your Guide, awaiting for Admin approval</span>
                                                @elseif($guide->status == 3)
                                                    <span>Declined</span>
                                                @endif
                                            </td>
                                        @endif
                                    @endif
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
                <!-- End Modal -->
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
            ordering: false,
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

<!--AJAX Call to approve guide-->



@endsection