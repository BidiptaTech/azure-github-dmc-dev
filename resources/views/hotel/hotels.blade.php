@extends('layouts.layout')
@section('title', 'Hotel Listing')
@extends('layouts.datatablecss')

@section('css')
<style>
    /* Ensure user profile dropdown is visible on hotels page */
    .topbar-item {
        display: block !important;
        visibility: visible !important;
    }

    .topbar-link {
        display: flex !important;
        visibility: visible !important;
    }

    .navbar-nav {
        display: flex !important;
    }

    /* Force show the dropdown arrow */
    .topbar-link .ri-arrow-down-s-line {
        display: flex !important;
        visibility: visible !important;
    }

    /* Ensure ONLY the user profile dropdown menu is properly positioned */
    .topbar-item .dropdown-menu {
        z-index: 9999 !important;
        display: none;
    }

    .topbar-item .dropdown-menu.show {
        display: block !important;
    }

    /* Ensure Export dropdown works normally */
    #exportDropdown + .dropdown-menu {
        z-index: 1000 !important;
        display: none;
    }

    #exportDropdown + .dropdown-menu.show {
        display: block !important;
    }
</style>
@endsection

@section('content')

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Hotels & Accommodations</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        <!-- Add New Hotel Button -->
                        @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 20)
                        <a href="{{ route('hotels.create') }}"
                            class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                            <i class="fas fa-plus"></i> Add New Hotel
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
                            {{-- <th>Master Dmc</th> --}}
                            @php
                                $roleId = auth()->user()->role_id;
                            @endphp
                            @php
                                $hideRoles = [11, 20, 35, 130, 132, 133, 135, 136, 137, 138, 77, 84, 139, 140];
                            @endphp

                            @if($roleId == 10 || $roleId == 19)
                                <th>DMC</th>
                            @elseif(!in_array($roleId, $hideRoles))
                                <th>Master Dmc</th>
                                <th>DMC</th>
                            @endif
                            {{-- <th>Master Dmc</th>
                            <th>Dmc</th> --}}
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Image</th>
                            <th>Calender</th>
                            @if(auth()->user()->role_id == 1 || auth()->user()->userId == 2 || auth()->user()->role_id == 23  || auth()->user()->role_id == 35 || auth()->user()->role_id == 47 || auth()->user()->role_id == 77 || auth()->user()->role_id ==82 || auth()->user()->role_id == 84 || auth()->user()->role_id == 139 || auth()->user()->role_id == 140 || hasPermission('edit hotel') || hasPermission('delete hotel'))
                            @if(hasPermission('edit hotel') || hasPermission('delete hotel'))
                            <th>Action</th>
                            <th>Created At</th>
                            @endif
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hotels as $key => $hotel)
                        <tr>
                            {{-- {{ dd($hotel->dmc->name) }} --}}
                            <td>{{ ++$key }}</td>
                            <td>{{ $hotel->name }}</td>
                            {{-- <td>
                                @php
                                    $dmcUser = App\Models\User::where('userId', $hotel->dmc_id)->first();
                                @endphp
                                {{ $hotel->dmc && $hotel->dmc->masterDmc ? $hotel->dmc->masterDmc->company_name : 'N/A' }}</td>
                            </td> --}}

                            @php
                                $roleId = auth()->user()->role_id;
                            @endphp
                            @php
                                $hideRoles = [11, 20, 35, 130, 132, 133, 135, 136, 137, 138, 77, 84, 139, 140];
                            @endphp

                            @if($roleId == 10 || $roleId == 19) {{-- Master DMC or Virtual Master DMC --}}
                                @php
                                    $dmcIds = $hotel->getSelectedDmcIds(); // Get array of DMC IDs
                                    $dmcUsers = App\Models\User::whereIn('userId', $dmcIds)->get();
                                @endphp
                                <td>
                                    @if($dmcUsers->count() > 0)
                                        {{ $dmcUsers->first()->company_name }}
                                        @if($dmcUsers->count() > 1)
                                            <br><a href="javascript:void(0)" 
                                                   class="text-primary" 
                                                   onclick="showDmcModal('{{ $hotel->hotel_unique_id }}', 'dmc', {{ $dmcUsers->toJson() }})">
                                                <small>+{{ $dmcUsers->count() - 1 }} More</small>
                                            </a>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                            @elseif(!in_array($roleId, $hideRoles)) {{-- Not DMC or Virtual DMC --}}
                                @php
                                    $dmcIds = $hotel->getSelectedDmcIds(); // Get array of DMC IDs
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
                                                   onclick="showDmcModal('{{ $hotel->hotel_unique_id }}', 'master_dmc', {{ $masterDmcUsers->toJson() }})">
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
                                                   onclick="showDmcModal('{{ $hotel->hotel_unique_id }}', 'dmc', {{ $dmcUsers->toJson() }})">
                                                <small>+{{ $dmcUsers->count() - 1 }} More</small>
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-muted">No DMC assigned</span>
                                    @endif
                                </td>
                            @endif

                            {{-- <td>
                                @php
                                    $dmcUser = App\Models\User::where('userId', $hotel->dmc_id)->first();
                                    $masterdmcUser = App\Models\User::where('userId', $dmcUser->master_dmc_id)->first();
                                @endphp
                                {{ $masterdmcUser ? $masterdmcUser->company_name : 'N/A' }}
                                
                            </td>
                            <td>{{ $dmcUser ? $dmcUser->company_name : 'N/A' }}</td> --}}
                            <td>{{ $hotel->phone }}</td>
                            <td>{{ $hotel->email }}</td>
                            <td>
                                <img src="{{ $hotel->main_image }}" alt="Hotel Image"
                                    style="width: 50px; height: auto;">
                            </td>
                            <td>
                                <a href="{{ route('hotels.viewcalendar', $hotel->hotel_unique_id) }}" target="_blank">
                                    <i class="fa fa-calendar-alt"></i>View Calendar
                                </a>
                            </td>
                            @if(auth()->user()->role_id == 1 || auth()->user()->userId == 2 || auth()->user()->role_id == 23  || auth()->user()->role_id == 35 || auth()->user()->role_id == 47 || auth()->user()->role_id == 77 || auth()->user()->role_id ==82 || auth()->user()->role_id == 84 || in_array(auth()->user()->role_id, [130, 132, 133, 135, 136, 137, 138, 139, 140]) || hasPermission('edit hotel') || hasPermission('delete hotel'))
                                @if($hotel->status == 1)
                                    <td style="display: inline-block; white-space: nowrap;">
                                        @if(hasPermission('edit hotel'))
                                        <a href="{{ route('hotels.edit', $hotel->hotel_unique_id) }}"
                                            class="btn btn-primary btn-sm rounded-circle"
                                            style="min-width: 28px; min-height: 28px; padding: 0;">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960"
                                                width="16px" fill="#ffffff">
                                                <path
                                                    d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z" />
                                            </svg>
                                        </a>
                                        @endif
                                        @if( Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                                        <button type="button"
                                            class="btn btn-danger btn-sm rounded-circle"
                                            style="min-width: 28px; min-height: 28px; padding: 0;" 
                                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                                            onclick="setDeleteForm('{{ route('hotels.destroy', $hotel->hotel_unique_id) }}')">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960"
                                                width="16px" fill="#ffffff">
                                                <path
                                                    d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z" />
                                            </svg>
                                        </button>
                                        @endif
                                    </td>
                                @else
                                    <!-- @if(Auth::user()->role_id == 11)
                                        <td>
                                            @if($hotel->status == 2)
                                                <span>Pending approval from the A.M</span>
                                            @elseif($hotel->status == 4)
                                                <span>Approved by the A.M, awaiting S.M approval</span>
                                            @elseif($hotel->status == 5)
                                                <span>Approved by the S.M, awaiting Admin approval</span>
                                            @elseif($hotel->status == 3)
                                                <span>Declined</span>
                                            @endif
                                        </td>
                                    @endif -->
                                    <!-- @if(Auth::user()->role_id == 4)
                                        <td>
                                            @if($hotel->status == 4)
                                                <span>Approved by the A.M, awaiting S.M approval</span>
                                            @elseif($hotel->status == 5)
                                                <span>Approved by the S.M, awaiting Admin approval</span>
                                            @elseif($hotel->status == 3)
                                                <span>Declined</span>
                                            @endif
                                        </td>
                                    @endif -->
                                    <td>
                                        @if($hotel->status == 5)
                                            <span>Your Hotel, awaiting for Admin approval</span>
                                        @elseif($hotel->status == 3)
                                            <span>Declined</span>
                                        @endif
                                    </td>
                                @endif
                            @endif
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $hotel->created_at->format('D,  M d, Y') }}</span>
                                    <small class="text-muted">{{ $hotel->created_at->format('h:i A') }}</small>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Hotel Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <form id="deleteForm" action="" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- DMC Modal -->
    <div class="modal fade" id="dmcModal" tabindex="-1" role="dialog" aria-labelledby="dmcModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dmcModalLabel">DMC Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="dmcList"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script>
    // Set form action for delete
    function setDeleteForm(url) {
        document.getElementById('deleteForm').action = url;
    }

    // Show DMC modal with details
    function showDmcModal(itemId, type, users) {
        let listHtml = '<ul class="list-group">';
        users.forEach(function(user) {
            listHtml += `<li class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${user.company_name}</strong>
                        <br><small>${user.email || 'No email'}</small>
                        ${user.phone ? `<br><small>${user.phone}</small>` : ''}
                    </div>
                </div>
            </li>`;
        });
        listHtml += '</ul>';
        
        $('#dmcList').html(listHtml);
        $('#dmcModalLabel').text(type === 'dmc' ? 'DMC List' : 'Master DMC List');
        
        // Use Bootstrap 5 modal show method
        var myModal = new bootstrap.Modal(document.getElementById('dmcModal'));
        myModal.show();
    }
    </script>

<!--AJAX Call to approve guide-->
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
            let hotelId = $(this).data("id");
            let action = $(this).data("action");
            var url = "{{ env('APP_URL') }}" + "/hotel/approve-or-decline/" + hotelId;
            $.ajax({
                url: url,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    action: action
                },
                success: function(response) {
                    if (response.success) {
                        let guideRow = $("#guide-" + hotelId);
                        guideRow.find(".approve-btn, .decline-btn").remove();
                        guideRow.find(".status-message").text(action === 'approve' ? "Guide Approved!" : "Guide Declined!").css("color", action === 'approve' ? "green" : "red").show();
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

<!-- Hotels Page Specific Dropdown Fix -->
<script>
$(document).ready(function() {
    // Ensure user profile dropdown works on hotels page
    setTimeout(function() {
        // Target only the user profile dropdown, not the export dropdown
        const dropdownToggle = $('.topbar-item .topbar-link.dropdown-toggle');
        const dropdownMenu = $('.topbar-item .dropdown-menu');
        
        if (dropdownToggle.length && dropdownMenu.length) {
            // Remove any existing event handlers to avoid conflicts
            dropdownToggle.off('click.profile-dropdown-fix');
            
            // Add click event handler
            dropdownToggle.on('click.profile-dropdown-fix', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Close any open export dropdowns first
                $('#exportDropdown').attr('aria-expanded', 'false');
                $('#exportDropdown').next('.dropdown-menu').removeClass('show');
                
                // Toggle user profile dropdown
                if (dropdownMenu.hasClass('show')) {
                    dropdownMenu.removeClass('show');
                    dropdownToggle.attr('aria-expanded', 'false');
                } else {
                    dropdownMenu.addClass('show');
                    dropdownToggle.attr('aria-expanded', 'true');
                }
            });
            
            // Close user profile dropdown when clicking outside
            $(document).on('click.profile-dropdown-fix', function(e) {
                if (!dropdownToggle.is(e.target) && dropdownToggle.has(e.target).length === 0 && 
                    !dropdownMenu.is(e.target) && dropdownMenu.has(e.target).length === 0) {
                    dropdownMenu.removeClass('show');
                    dropdownToggle.attr('aria-expanded', 'false');
                }
            });
            
            console.log('Hotels page user profile dropdown fix applied');
        } else {
            console.log('User profile dropdown elements not found on hotels page');
        }
    }, 1000); // Wait 1 second for all scripts to load
});
</script>

@endsection