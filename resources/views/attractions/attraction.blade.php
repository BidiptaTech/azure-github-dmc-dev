@extends('layouts.layout')
@section('title', 'Attractions & Experiences')
@extends('layouts.datatablecss')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Attractions & Experiences</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        <!-- Add New Category Button -->
                        @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 20)
                        @if(hasPermission('create attraction'))
                        <a href="{{ route('attraction.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                            <i class="fas fa-plus"></i> Add New Attraction
                        </a>
                        @endif
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
                            @php
                                $hideRoles = [11, 20, 35, 130, 132, 133, 135, 136, 137, 138, 74, 93, 90, 139, 140];
                            @endphp

                            @if(in_array($roleId, [10, 19])) {{-- Master DMC or Virtual Master DMC --}}
                                <th>DMC</th>
                            @elseif(!in_array($roleId, $hideRoles)) {{-- Not DMC or Virtual DMC --}}
                                <th>Master Dmc</th>
                                <th>DMC</th>
                            @endif

                            {{-- <th>Adult Price</th>
                            <th>Child Price</th> --}}
                            <th>location</th>
                            
                            <th>Calendar</th>
                            <th>Status</th>
                            @if(auth()->user()->role_id == 1 || auth()->user()->userId == 2 || auth()->user()->role_id == 23  || auth()->user()->role_id == 35 || auth()->user()->role_id == 44 || auth()->user()->role_id == 74 || auth()->user()->role_id ==91 || auth()->user()->role_id == 93 || auth()->user()->role_id == 130 || auth()->user()->role_id == 132 || auth()->user()->role_id == 133 || auth()->user()->role_id == 135 || auth()->user()->role_id == 136 || auth()->user()->role_id == 137 || auth()->user()->role_id == 138 || hasPermission('edit attraction') || hasPermission('delete attraction'))
                                <th>Action</th>
                            @endif
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attractions as $key => $attraction)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td class="category-name">{{ $attraction->name }}</td>
                                @php
                                    $roleId = auth()->user()->role_id;
                                @endphp
                                @php
                                    $hideRoles = [11, 20, 35, 130, 132, 133, 135, 136, 137, 138, 74, 93, 139, 140];
                                @endphp

                                @if(in_array($roleId, [10, 19])) {{-- Master DMC or Virtual Master DMC --}}
                                    @php
                                        $dmcIds = $attraction->getSelectedDmcIds(); // Get array of DMC IDs
                                        $dmcUsers = App\Models\User::whereIn('userId', $dmcIds)->get();
                                    @endphp
                                    <td>
                                        @if($dmcUsers->count() > 0)
                                            {{ $dmcUsers->first()->company_name }}
                                            @if($dmcUsers->count() > 1)
                                                <br><a href="javascript:void(0)" 
                                                       class="text-primary" 
                                                       onclick="showDmcModal('{{ $attraction->attraction_id }}', 'dmc', {{ $dmcUsers->toJson() }})">
                                                    <small>+{{ $dmcUsers->count() - 1 }} More</small>
                                                </a>
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                @elseif(!in_array($roleId, $hideRoles)) {{-- Not DMC or Virtual DMC --}}
                                    @php
                                        $dmcIds = $attraction->getSelectedDmcIds(); // Get array of DMC IDs
                                        $dmcUsers = App\Models\User::whereIn('userId', $dmcIds)->get();
                                        $masterDmcIds = $dmcUsers->pluck('master_dmc_id')->filter()->unique();
                                        $masterDmcUsers = App\Models\User::whereIn('userId', $masterDmcIds)->get();
                                    @endphp
                                    <td>
                                        @if($masterDmcUsers->count() > 0)
                                            <span class="text-primary">{{ $masterDmcUsers->first()->company_name }}</span>
                                            @if($masterDmcUsers->count() > 1)
                                                <br><a href="javascript:void(0)" 
                                                       class="btn btn-primary btn-sm text-white" 
                                                       onclick="showDmcModal('{{ $attraction->attraction_id }}', 'master_dmc', {{ $masterDmcUsers->toJson() }})">
                                                    <small>+{{ $masterDmcUsers->count() - 1 }} More</small>
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-muted">No DMC assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($dmcUsers->count() > 0)
                                            <span class="text-primary">{{ $dmcUsers->first()->company_name }}</span>
                                            @if($dmcUsers->count() > 1)
                                                <br><a href="javascript:void(0)" 
                                                       class="btn btn-primary btn-sm text-white" 
                                                       onclick="showDmcModal('{{ $attraction->attraction_id }}', 'dmc', {{ $dmcUsers->toJson() }})">
                                                    <small>+{{ $dmcUsers->count() - 1 }} More</small>
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-muted">No DMC assigned</span>
                                        @endif
                                    </td>
                                @endif

                                {{-- <td>
                                    @if($attraction->adult_price)
                                        {{ $attraction->adult_price }}
                                    @else
                                        <span class="badge bg-danger">No details</span>
                                    @endif
                                </td>
                                <td>
                                @if($attraction->child_price)
                                    {{$attraction->child_price}}
                                @else
                                    <span class="badge bg-danger">No details</span>
                                @endif
                                </td> --}}
                                <td>
                                    {{$attraction->location}}
                                </td>
                                
                                <td> 
                                    <a href="{{ route('attraction.calendar', ['attraction_id' => Crypt::encrypt($attraction->attraction_id)]) }}" target="_blank"><i class="fa fa-calendar-alt"></i>View Calendar</a></td>
                                <td>
                                @if($attraction->is_active == 1)
                                    <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif  
                                </td>
                                @if(auth()->user()->role_id == 1 || auth()->user()->userId == 2 || auth()->user()->role_id == 23  || auth()->user()->role_id == 35 || auth()->user()->role_id == 44 || auth()->user()->role_id == 74 || auth()->user()->role_id ==91 || auth()->user()->role_id == 130 || auth()->user()->role_id == 132 || auth()->user()->role_id == 133 || auth()->user()->role_id == 135 || auth()->user()->role_id == 136 || auth()->user()->role_id == 137 || auth()->user()->role_id == 138 || auth()->user()->role_id == 93 || auth()->user()->role_id == 139 || auth()->user()->role_id == 140 || hasPermission('edit attraction') || hasPermission('delete attraction'))
                                @if($attraction->status == 1)
                                <td style="display: inline-block; white-space: nowrap;">
                                    <!-- Edit Button -->
                                    @if(hasPermission('edit attraction'))
                                    <a href="{{ route('attraction.edit', Crypt::encrypt($attraction->attraction_id)) }}" 
                                    class="btn btn-primary btn-sm rounded-circle waves-effect waves-light" 
                                    style="min-width: 28px; min-height: 28px; padding: 0;">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                            <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/>
                                        </svg>
                                    </a>
                                    @endif

                                    <!-- Delete Button -->
                                    @if( Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                                    <button type="button" 
                                        class="btn btn-danger btn-sm rounded-circle waves-effect waves-light" 
                                        style="min-width: 28px; min-height: 28px; padding: 0;" 
                                        data-toggle="modal" 
                                        data-target="#deleteModal" 
                                        onclick="setDeleteForm('{{ route('attraction.destroy', Crypt::encrypt($attraction->attraction_id)) }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                            <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                        </svg>
                                    </button>
                                    @endif
                                </td>
                                @else
                                    {{-- @if(Auth::user()->role_id == 11)
                                    <td>
                                        @if($attraction->status == 2)
                                            <span>Pending approval from the A.M</span>
                                        @elseif($attraction->status == 4)
                                            <span>Awaiting S.M approval</span>
                                        @elseif($attraction->status == 5)
                                            <span>Awaiting Admin approval</span>
                                        @elseif($attraction->status == 3)
                                            <span>Declined</span>
                                        @endif
                                    </td>
                                    @endif  
                                    @if(Auth::user()->role_id == 4)
                                        <td>
                                            @if($attraction->status == 4)
                                                <span>Awaiting S.M approval</span>
                                            @elseif($attraction->status == 5)
                                                <span>Awaiting Admin approval</span>
                                            @elseif($attraction->status == 3)
                                                <span>Declined</span>
                                            @endif
                                        </td>
                                    @endif

                                    @if(Auth::user()->role_id == 3)
                                        <td>
                                            @if($attraction->status == 5)
                                                <span>Awaiting Admin approval</span>
                                            @elseif($attraction->status == 3)
                                                <span>Declined</span>
                                            @endif
                                        </td>
                                    @endif --}}
                                    <td>
                                        @if($attraction->status == 5)
                                            <span>Your Attraction, awaiting for Admin approval</span>
                                        @elseif($attraction->status == 3)
                                            <span>Declined</span>
                                        @endif
                                    </td>
                                @endif
                            @endif
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $attraction->created_at->format('D,  M d, Y') }}</span>
                                    <small class="text-muted">{{ $attraction->created_at->format('h:i A') }}</small>
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

<!-- DMC Companies Modal -->
<div class="modal fade" id="dmcCompaniesModal" tabindex="-1" role="dialog" aria-labelledby="dmcCompaniesModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dmcCompaniesModalLabel">DMC Companies</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeDmcModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="dmcCompaniesModalBody">
                <!-- Company names will be populated here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeDmcModal()">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Attraction Delete Modal -->
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
    
    function showDmcModal(attractionId, type, companies) {
        // Set modal title
        const modalTitle = document.getElementById('dmcCompaniesModalLabel');
        modalTitle.textContent = type === 'master_dmc' ? 'Master DMC Companies' : 'DMC Companies';
        
        // Clear and populate modal body
        const modalBody = document.getElementById('dmcCompaniesModalBody');
        modalBody.innerHTML = '';
        
        if (companies && companies.length > 0) {
            companies.forEach(function(company) {
                const companyDiv = document.createElement('div');
                companyDiv.className = 'mb-2 p-2 border-bottom';
                companyDiv.innerHTML = `
                    <strong>${company.company_name || 'N/A'}</strong>
                    ${company.name ? `<br><small class="text-muted">${company.name}</small>` : ''}
                `;
                modalBody.appendChild(companyDiv);
            });
        } else {
            modalBody.innerHTML = '<p class="text-muted">No companies found.</p>';
        }
        
        // Show modal using Bootstrap 4 syntax
        $('#dmcCompaniesModal').modal('show');
    }
    
    // Add explicit close functionality
    function closeDmcModal() {
        $('#dmcCompaniesModal').modal('hide');
    }
    
    // Ensure modal can be closed with escape key and click outside
    $(document).ready(function() {
        $('#dmcCompaniesModal').on('hidden.bs.modal', function () {
            // Clean up when modal is closed
            $('#dmcCompaniesModalBody').html('');
        });
    });
</script>
@endsection
