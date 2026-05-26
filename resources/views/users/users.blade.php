@extends('layouts.layout')
@section('title', 'Users')
@extends('layouts.datatablecss')

@section('content')
@php
  $settingsRoleIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
  $authRoleId = (int) (auth()->user()->role_id ?? 0);
  $authUserIsPro = (int) (auth()->user()->is_pro ?? 0);
  $usersCollection = $users instanceof \Illuminate\Pagination\AbstractPaginator ? $users->getCollection() : collect($users);
  $showSettingsColumn = $usersCollection->contains(function ($u) use ($settingsRoleIds, $authRoleId) {
      $rowRoleId = (int) ($u->role_id ?? 0);
      return in_array($rowRoleId, $settingsRoleIds, true)
          || ($authRoleId === 10 && $rowRoleId === 11);
  });
  $showBookingTypeColumn = $usersCollection->contains(function ($u) use ($authRoleId) {
      $rowRoleId = (int) ($u->role_id ?? 0);
      return ($authRoleId === 1 && $rowRoleId === 10) || ($authRoleId === 10 && $rowRoleId === 11);
  });
@endphp
<style>
  /* Compact users table layout */
  .datatables-basic {
    font-size: 12px;
    line-height: 1.2;
  }

  .datatables-basic thead th,
  .datatables-basic tbody td {
    padding: 0.35rem 0.45rem !important;
    vertical-align: middle;
  }

  .datatables-basic thead th {
    font-weight: 600;
    white-space: nowrap;
  }

  .datatables-basic thead th.settings-controls-col {
    white-space: normal;
    vertical-align: bottom;
  }

  .datatables-basic .badge {
    font-size: 10px;
    padding: 0.25rem 0.45rem;
  }

  .datatables-basic .form-check {
    margin-bottom: 0;
    min-height: 0;
  }

  .datatables-basic .form-check-input {
    margin-top: 0;
  }

  /* Settings column: 6 aligned slots (Auto Cancel Status = toggle, Auto Cancel = D-n dropdown) */
  .settings-header-grid,
  .settings-controls-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 0.35rem;
    width: 100%;
    min-width: 320px;
  }

  .settings-header-grid {
    align-items: end;
    font-size: 10px;
    line-height: 1.15;
    text-align: center;
  }

  .settings-controls-grid {
    align-items: start;
  }

  .settings-header-grid > span {
    display: block;
    padding: 0 2px;
    word-break: break-word;
    hyphens: auto;
  }

  .settings-controls-grid .settings-col {
    display: flex;
    justify-content: center;
    align-items: center;
    min-width: 0;
  }

  .settings-controls-grid .form-check.form-switch {
    padding-left: 0;
    margin-bottom: 0;
    display: flex;
    justify-content: center;
  }

  .settings-controls-grid .form-check.form-switch .form-check-input {
    margin-left: 0;
    float: none;
  }

  .datatables-basic .booking-type-select,
  .datatables-basic .auto-cancel-dropdown,
  .datatables-basic .guide-pax-input {
    height: 26px !important;
    font-size: 11px !important;
    padding: 0.1rem 0.35rem !important;
  }

  .datatables-basic .btn.btn-sm {
    width: 26px !important;
    height: 26px !important;
    min-width: 26px !important;
    min-height: 26px !important;
    padding: 0 !important;
  }

  .dataTables_wrapper .dataTables_filter input,
  .dataTables_wrapper .dataTables_length select {
    height: 30px;
    font-size: 12px;
    padding: 0.2rem 0.45rem;
  }
</style>
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
      <div class="card-datatable table-responsive pt-0">
            <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0">Users</h5>
                </div>

                <div class="d-flex justify-content-between gap-3">
                    <!-- Add New User Button -->
                    @if(hasPermission('create users'))
                      <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                          <i class="fas fa-plus"></i> Add New User
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
              <th>Company Name</th>
              <th>User Details</th>
              <th>Contact Information</th>
              <th>Country & City</th>

              @if($showSettingsColumn)
                <th class="settings-controls-col" style="min-width: 320px;">
                  <div class="settings-header-grid fw-bold text-uppercase">
                    <span>Zone On</span>
                    <span>Price Hide</span>
                    <span>Email On</span>
                    <span>Auto Cancel Status</span>
                    <span>Auto Cancel</span>
                    <span>Guide Pax</span>
                  </div>
                </th>
              @endif
              @if($showBookingTypeColumn)
                <th class="booking-type-col" style="min-width: 150px;">Booking Type</th>
              @endif

              <th>User Type</th>

              @if(hasPermission('edit users') || hasPermission('delete users'))
                  <th>Action</th>
              @endif
              @if(auth::user()->user_type == 1 || auth::user()->user_type == 2 || auth::user()->user_type == 3 )
              <th>Auto Login</th>
              @endif
            </tr>
          </thead>
          <tbody>
            @foreach ($users as $key => $user)
              @php
                $rowRoleId = (int) ($user->role_id ?? 0);
                $showSettingsForThisRow = in_array($rowRoleId, $settingsRoleIds, true)
                    || ($authRoleId === 10 && $rowRoleId === 11);
                $showBookingTypeForThisRow = ($authRoleId === 1 && (int) ($user->role_id ?? 0) === 10)
                    || ($authRoleId === 10 && (int) ($user->role_id ?? 0) === 11);
                $bookingOptionsForRow = [
                    1 => 'Lite Form',
                    2 => 'Pro Form',
                    3 => 'Both',
                ];
                $selectedBookingTypeForRow = (int) ($user->is_pro ?? 1);
                $bookingTypeLockedForRow = false;

                if ($authRoleId === 10 && (int) ($user->role_id ?? 0) === 11) {
                    if ($authUserIsPro === 1) {
                        $bookingOptionsForRow = [1 => 'Lite Form'];
                        $selectedBookingTypeForRow = 1;
                        $bookingTypeLockedForRow = true;
                    } elseif ($authUserIsPro === 2) {
                        $bookingOptionsForRow = [2 => 'Pro Form'];
                        $selectedBookingTypeForRow = 2;
                        $bookingTypeLockedForRow = true;
                    } elseif ($authUserIsPro === 3) {
                        // Full access: show all 3 options, default to Both on initialization.
                        $bookingOptionsForRow = [
                            1 => 'Lite Form',
                            2 => 'Pro Form',
                            3 => 'Both',
                        ];
                        $selectedBookingTypeForRow = 3;
                    }
                } elseif (!in_array($selectedBookingTypeForRow, [1, 2, 3], true)) {
                    $selectedBookingTypeForRow = 1;
                }
              @endphp
              <tr class="{{ $showSettingsForThisRow ? '' : 'no-settings-row' }}">
                <td>{{ ++$key }}</td>
                <td>{{ $user->company_name ?? 'N/A' }}</td>
                <td>
                  <div class="d-flex flex-column">
                    <span class="fw-medium">{{ $user->name }}</span>
                    <span class="badge bg-secondary mt-1">{{ $user->role->name ?? 'No Role' }}</span>
                  </div>
                </td>
                
                <td>
                  <div class="d-flex flex-column user-contact">
                    <span class="fw-medium">{{ $user->email }}</span>
                    @if($user->phone)
                      <span class="text-muted fw-medium mt-1 text-decoration-underline text-primary">📞 {{ $user->phone }}</span>
                    @endif
                  </div>
                </td>

                <td>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                        <div>
                            <span class="fw-medium text-primary">{{ $user->user_country ?? 'N/A' }}</span>
                            <br>
                            <small class="text-muted">{{ $user->city ?? 'N/A' }}</small>
                        </div>
                    </div>
                </td>

                @if($showSettingsColumn)
                <td class="settings-controls-cell">
                  @if($showSettingsForThisRow)
                    <div class="settings-controls-grid">
                      @if(auth::user()->role_id == 10)
                        <div class="settings-col">
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="zone_on" value="0">
                                <input {{$user->zone_on == 1 ? 'checked' : ''}} 
                                    class="form-check-input zone-toggle" 
                                    data-user-id="{{ $user->userId }}"
                                    type="checkbox" 
                                    id="zone_on_{{ $user->userId }}"
                                    value="1" 
                                    style="width: 25px; height: 15px;">
                            </div>
                        </div>
                        <div class="settings-col">
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="price_hide" value="0">
                                <input {{$user->price_hide == 1 ? 'checked' : ''}} 
                                    class="form-check-input price-hide_toggle" 
                                    data-user-id="{{ $user->userId }}"
                                    type="checkbox" 
                                    id="price_hide_{{ $user->userId }}"
                                    value="1" 
                                    style="width: 25px; height: 15px;">
                            </div>
                        </div>
                        <div class="settings-col">
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="email_on" value="0">
                                <input {{$user->email_on == 1 ? 'checked' : ''}} 
                                    class="form-check-input email-toggle" 
                                    data-user-id="{{ $user->userId }}"
                                    type="checkbox" 
                                    id="email_on_{{ $user->userId }}"
                                    value="1" 
                                    style="width: 25px; height: 15px;">
                            </div>
                        </div>
                        <div class="settings-col">
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="auto_cancel_on" value="0">
                                <input {{ ($user->auto_cancel_date !== null && $user->auto_cancel_date >= 1) ? 'checked' : '' }}
                                    class="form-check-input auto-cancel-toggle"
                                    data-user-id="{{ $user->userId }}"
                                    type="checkbox"
                                    id="auto_cancel_toggle_{{ $user->userId }}"
                                    value="1"
                                    style="width: 25px; height: 15px;">
                            </div>
                        </div>
                        <div class="settings-col">
                            <div class="form-group auto-cancel-day-wrap mb-0" data-user-id="{{ $user->userId }}" style="display: {{ ($user->auto_cancel_date !== null && $user->auto_cancel_date >= 1) ? 'block' : 'none' }}; padding-right: 5px;">
                                <select class="form-select auto-cancel-dropdown"
                                    data-user-id="{{ $user->userId }}"
                                    id="auto_cancel_{{ $user->userId }}"
                                    style="width: 60px; height: 25px; font-size: 12px; padding: 1px; ">
                                    <option value="1" {{ ($user->auto_cancel_date == 1 || is_null($user->auto_cancel_date)) ? 'selected' : '' }}>D-1</option>
                                    <option value="2" {{ $user->auto_cancel_date == 2 ? 'selected' : '' }}>D-2</option>
                                    <option value="3" {{ $user->auto_cancel_date == 3 ? 'selected' : '' }}>D-3</option>
                                    <option value="4" {{ $user->auto_cancel_date == 4 ? 'selected' : '' }}>D-4</option>
                                    <option value="5" {{ $user->auto_cancel_date == 5 ? 'selected' : '' }}>D-5</option>
                                    <option value="6" {{ $user->auto_cancel_date == 6 ? 'selected' : '' }}>D-6</option>
                                    <option value="7" {{ $user->auto_cancel_date == 7 ? 'selected' : '' }}>D-7</option>
                                    <option value="8" {{ $user->auto_cancel_date == 8 ? 'selected' : '' }}>D-8</option>
                                    <option value="9" {{ $user->auto_cancel_date == 9 ? 'selected' : '' }}>D-9</option>
                                    <option value="10" {{ $user->auto_cancel_date == 10 ? 'selected' : '' }}>D-10</option>
                                    <option value="11" {{ $user->auto_cancel_date == 11 ? 'selected' : '' }}>D-11</option>
                                    <option value="12" {{ $user->auto_cancel_date == 12 ? 'selected' : '' }}>D-12</option>
                                    <option value="13" {{ $user->auto_cancel_date == 13 ? 'selected' : '' }}>D-13</option>
                                    <option value="14" {{ $user->auto_cancel_date == 14 ? 'selected' : '' }}>D-14</option>
                                </select>
                            </div>
                        </div>
                        <div class="settings-col">
                            <div class="form-group mb-0">
                                <input type="text" class="form-control guide-pax-input"
                                    data-user-id="{{ $user->userId }}"
                                    id="guide_pax_{{ $user->userId }}"
                                    value="{{ $user->guide_pax ?? 0 }}"
                                    maxlength="2" inputmode="numeric" pattern="[0-9]*"
                                    style="width: 50px; height: 25px; font-size: 12px; padding: 1px; text-align: center; padding-left: 5px;"
                                    oninput="this.value = this.value.replace(/\D/g, '').slice(0, 2);">
                            </div>
                        </div>
                      @else
                        <div class="settings-col">
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="zone_on" value="0">
                                <input {{$user->zone_on == 1 ? 'checked' : ''}} 
                                    class="form-check-input" 
                                    name="zone_on" 
                                    type="checkbox" 
                                    id="zone_on"
                                    value="1" 
                                    style="width: 25px; height: 15px;" 
                                    disabled>
                            </div>
                        </div>
                        <div class="settings-col">
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="price_hide" value="0">
                                <input {{$user->price_hide == 1 ? 'checked' : ''}} 
                                    class="form-check-input" 
                                    name="price_hide" 
                                    type="checkbox" 
                                    id="price_hide"
                                    value="1" 
                                    style="width: 25px; height: 15px;" 
                                    disabled>
                            </div>
                        </div>
                        <div class="settings-col">
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="email_on" value="0">
                                <input {{$user->email_on == 1 ? 'checked' : ''}} 
                                    class="form-check-input" 
                                    name="email_on" 
                                    type="checkbox" 
                                    id="email_on"
                                    value="1" 
                                    style="width: 25px; height: 15px;" 
                                    disabled>
                            </div>
                        </div>
                        <div class="settings-col">
                            <div class="form-check form-switch mb-0">
                                <input {{ ($user->auto_cancel_date !== null && $user->auto_cancel_date >= 1) ? 'checked' : '' }}
                                    class="form-check-input" type="checkbox" value="1"
                                    style="width: 25px; height: 15px;" disabled>
                            </div>
                        </div>
                        <div class="settings-col">
                            <div class="form-group mb-0" style="display: {{ ($user->auto_cancel_date !== null && $user->auto_cancel_date >= 1) ? 'block' : 'none' }};">
                                <select class="form-select" id="auto_cancel_disabled"
                                    style="width: 50px; height: 25px; font-size: 10px; padding: 2px;" disabled>
                                    <option value="">--</option>
                                    <option value="3" {{ $user->auto_cancel_date == 3 ? 'selected' : '' }}>D-3</option>
                                    <option value="7" {{ $user->auto_cancel_date == 7 ? 'selected' : '' }}>D-7</option>
                                    <option value="14" {{ $user->auto_cancel_date == 14 ? 'selected' : '' }}>D-14</option>
                                </select>
                            </div>
                        </div>
                        <div class="settings-col">
                            <span class="text-muted small">—</span>
                        </div>
                      @endif
                    </div>
                  @else
                    <span class="d-none">N/A</span>
                  @endif
                </td>
                @endif
                @if($showBookingTypeColumn)
                <td class="booking-type-cell">
                  @if($showBookingTypeForThisRow)
                    <select class="form-select form-select-sm booking-type-select"
                            data-user-id="{{ $user->userId }}"
                            data-previous-value="{{ $selectedBookingTypeForRow }}"
                            style="min-width: 130px;"
                            {{ $bookingTypeLockedForRow ? 'disabled' : '' }}>
                      @foreach($bookingOptionsForRow as $bookingValue => $bookingLabel)
                        <option value="{{ $bookingValue }}" {{ $selectedBookingTypeForRow === $bookingValue ? 'selected' : '' }}>
                          {{ $bookingLabel }}
                        </option>
                      @endforeach
                    </select>
                  @else
                    <span class="d-none">N/A</span>
                  @endif
                </td>
                @endif
                <td>{{ $user->getUserTypeName() }}</td>
                
                
                @if((auth::user()->user_type == 1 || auth::user()->user_type == 2 || auth::user()->user_type == 3))
                <!-- <td>
                  <button type="button" class="btn btn-primary btn-sm" 
                          style="padding: 4px 8px; border-radius: 4px;"
                          data-toggle="modal" data-target="#walletmodal"
                          data-id="{{ $user->id }}">
                      Add Balance
                  </button>
                </td> -->
                @endif
                @if(hasPermission('edit users') || hasPermission('delete users'))
                <td>
                  <div class="d-flex gap-2">
                    <!-- Edit Button -->
                    @if(hasPermission('edit users'))
                      <a href="{{ route('users.edit', Crypt::encrypt($user->userId)) }}" 
                        class="btn btn-primary btn-sm d-flex align-items-center justify-content-center rounded-circle" 
                        style="width: 28px; height: 28px; padding: 0;">
                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                          <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/>
                        </svg>
                      </a>
                    @endif
                
                    <!-- Delete Button -->
                    @if(hasPermission('delete users'))
                    <button type="button" 
                            class="btn btn-danger btn-sm d-flex align-items-center justify-content-center rounded-circle" 
                            style="width: 28px; height: 28px; padding: 0;" 
                            data-toggle="modal" 
                            data-target="#deleteModal"      
                           
                            onclick="setDeleteForm('{{ route('users.destroy', $user->userId) }}')">
                      <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                        <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                      </svg>
                    </button>
                    @endif
                  </div>
                </td>
                @endif
                
                <td>
                    <a href="{{ route('admin.loginAsUser', $user->userId) }}" class="btn btn-primary" >
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-right" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z"/>
                        <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/>
                      </svg>
                    </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!--User Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
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

<!--User Add Wallet money -->
<div class="modal fade" id="walletmodal" tabindex="-1" role="dialog" aria-labelledby="walletmodalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="walletmodalLabel">Add Wallet Money</h5>
        </div>
        <div class="modal-body">
            <form id="addMoneyForm" action="{{ route('add-money', ':id') }}" method="POST">
                @csrf
                <input type="hidden" id="userId" name="userId"> <!-- Hidden field for user ID -->
                <div class="form-group">
                    <label for="amount">Amount:</label>
                    <input type="number" class="form-control" id="amount" name="amount" required placeholder="Enter amount">
                </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Add Money</button>
            </div>
                </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- In <head> -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

<!-- Before </body> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- DataTable JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<!-- DataTables Initialization Script -->
<script>
    $(document).ready(function() {
        // Initialize DataTable with export buttons
        const usersTable = $('.datatables-basic').DataTable({
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

        function hideSettingsForNonEligibleRows() {
            const settingsColIndex = usersTable.column('.settings-controls-col').index();
            if (settingsColIndex === undefined) return;

            // In responsive child rows, hide this column's label/value for rows not eligible.
            $('.datatables-basic tbody tr.no-settings-row.parent').each(function() {
                const child = $(this).next('tr.child');
                if (!child.length) return;
                child.find('li[data-dt-column="' + settingsColIndex + '"]').hide();
            });
        }

        hideSettingsForNonEligibleRows();
        $('.datatables-basic').on('responsive-display.dt draw.dt', function() {
            hideSettingsForNonEligibleRows();
        });

        // Custom export button functionality (for the dropdown)
        $('#exportCopy').on('click', function() {
            usersTable.button('.buttons-copy').trigger();
        });

        $('#exportCSV').on('click', function() {
            usersTable.button('.buttons-csv').trigger();
        });

        $('#exportExcel').on('click', function() {
            usersTable.button('.buttons-excel').trigger();
        });

        $('#exportPDF').on('click', function() {
            usersTable.button('.buttons-pdf').trigger();
        });

        $('#exportPrint').on('click', function() {
            usersTable.button('.buttons-print').trigger();
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


  <script>
      $(document).ready(function() {
          $('#walletmodal').on('show.bs.modal', function(event) {
              var button = $(event.relatedTarget); 
              var userId = button.data('id'); 
              var modal = $(this);
              modal.find('#userId').val(userId); 
              modal.find('#addMoneyForm').attr('action', '{{ url("add-money") }}/' + userId); 
          });
      });
  </script>

<script>
$(document).ready(function() {
    $('.travclicks-toggle').on('change', function() {
        const userId = $(this).data('user-id');
        const isChecked = $(this).is(':checked') ? 1 : 0;
        
        // Show loading indicator
        $(this).prop('disabled', true);
        
        $.ajax({
            url: "{{ route('users.update.travclicks') }}",
            type: "POST",
            data: {
                user_id: userId,
                travclicks_on: isChecked,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                // Enable the toggle again
                $('.travclicks-toggle[data-user-id="' + userId + '"]').prop('disabled', false);
                
                // Show success message
                if (response.success) {
                    toastr.success(response.message || 'TravClicks status updated successfully');
                } else {
                    toastr.error(response.message || 'Error updating TravClicks status');
                    // Revert the toggle if there was an error
                    $('.travclicks-toggle[data-user-id="' + userId + '"]').prop('checked', !isChecked);
                }
            },
            error: function(xhr) {
                // Enable the toggle again
                $('.travclicks-toggle[data-user-id="' + userId + '"]').prop('disabled', false);
                
                // Show error message
                toastr.error('Error updating TravClicks status');
                
                // Revert the toggle
                $('.travclicks-toggle[data-user-id="' + userId + '"]').prop('checked', !isChecked);
                
                console.error(xhr.responseText);
            }
        });
    });
});
</script>

<script>
$(document).ready(function() {
    // Use event delegation for price-hide toggle to work with pagination
    $(document).on('change', '.price-hide_toggle', function() {
        const userId = $(this).data('user-id');
        console.log("userId price-hide = ", userId);
        const isChecked = $(this).is(':checked') ? 1 : 0;
        
        // Show loading indicator
        $(this).prop('disabled', true);
        
        $.ajax({
            url: "{{ route('users.update.price-hide') }}",
            type: "POST",
            data: {
                user_id: userId,
                price_hide: isChecked,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                // Enable the toggle again
                $('.price-hide_toggle[data-user-id="' + userId + '"]').prop('disabled', false);
                console.log("userId price-hide = ", $('.price-hide_toggle[data-user-id="' + userId + '"]').prop('disabled', false));

                
                // Show success message
                if (response.success) {
                    toastr.success(response.message || 'Price Hide status updated successfully');
                } else {
                    toastr.error(response.message || 'Error updating Price Hide status');
                    // Revert the toggle if there was an error
                    $('.price-hide_toggle[data-user-id="' + userId + '"]').prop('checked', !isChecked);
                }
            },
            error: function(xhr) {
                // Enable the toggle again
                $('.price-hide_toggle[data-user-id="' + userId + '"]').prop('disabled', false);
                
                // Show error message
                toastr.error('Error updating Price Hide status');
                
                // Revert the toggle
                $('.price-hide_toggle[data-user-id="' + userId + '"]').prop('checked', !isChecked);
                console.error(xhr.responseText);
            }
        });
    });
});
</script>

<script>
$(document).ready(function() {
    // Use event delegation for zone toggle to work with pagination
    $(document).on('change', '.zone-toggle', function() {
        const userId = $(this).data('user-id');
        const isChecked = $(this).is(':checked') ? 1 : 0;
        console.log("this in zone-on");

        $(this).prop('disabled', true);  

        $.ajax({
            url: "{{ route('update.zoneon') }}",
            type: "POST",
            data: {
                user_id: userId,
                zone_on: isChecked,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                $('.zone-toggle[data-user-id="' + userId + '"]').prop('disabled', false);
                if (response.success) {
                    toastr.success(response.message || 'Zone on status updated successfully');
                } else {
                    toastr.error(response.message || 'Error updating Zone on status');
                    $('.zone-toggle[data-user-id="' + userId + '"]').prop('checked', !isChecked);
                }
            },
            error: function(xhr) {
                $('.zone-toggle[data-user-id="' + userId + '"]').prop('disabled', false);
                toastr.error('Error updating Zone on status');
                $('.zone-toggle[data-user-id="' + userId + '"]').prop('checked', !isChecked);
                console.error(xhr.responseText);
            }
        });
    });
});
</script>

<script>
$(document).ready(function() {
    // Use event delegation for email toggle to work with pagination
    $(document).on('change', '.email-toggle', function() {
        const userId = $(this).data('user-id');
        const isChecked = $(this).is(':checked') ? 1 : 0;
        
        // Show loading indicator
        $(this).prop('disabled', true);
        
        $.ajax({
            url: "{{ route('users.update.email') }}",
            type: "POST",
            data: {
                user_id: userId,
                email_on: isChecked,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                // Enable the toggle again
                $('.email-toggle[data-user-id="' + userId + '"]').prop('disabled', false);
                
                // Show success message
                if (response.success) {
                    toastr.success(response.message || 'Email status updated successfully');
                } else {
                    toastr.error(response.message || 'Error updating Email status');
                    // Revert the toggle if there was an error
                    $('.email-toggle[data-user-id="' + userId + '"]').prop('checked', !isChecked);
                }
            },
            error: function(xhr) {
                // Enable the toggle again
                $('.email-toggle[data-user-id="' + userId + '"]').prop('disabled', false);
                
                // Show error message
                toastr.error('Error updating Email status');
                
                // Revert the toggle
                $('.email-toggle[data-user-id="' + userId + '"]').prop('checked', !isChecked);
                
                console.error(xhr.responseText);
            }
        });
    });
});
</script>

<script>
$(document).ready(function() {
    // Booking Type select: Lite Form(1) / Pro From(2) / Both(3)
    $(document).on('change', '.booking-type-select', function() {
        const $select = $(this);
        const userId = $select.data('user-id');
        const bookingType = parseInt($select.val(), 10);
        const previousValue = parseInt($select.data('previous-value') || $select.find('option[selected]').val() || '1', 10);

        if (![1, 2, 3].includes(bookingType)) {
            toastr.error('Invalid booking type selected');
            $select.val(previousValue);
            return;
        }

        $select.prop('disabled', true);

        $.ajax({
            url: "{{ route('users.update.booking-type') }}",
            type: "POST",
            data: {
                user_id: userId,
                booking_type: bookingType,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                $select.prop('disabled', false);
                if (response.success) {
                    $select.data('previous-value', bookingType);
                    toastr.success(response.message || 'Booking type updated successfully');
                } else {
                    toastr.error(response.message || 'Error updating booking type');
                    $select.val(previousValue);
                }
            },
            error: function(xhr) {
                $select.prop('disabled', false);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error updating booking type';
                toastr.error(msg);
                $select.val(previousValue);
                console.error(xhr.responseText);
            }
        });
    });

    // Auto Cancel toggle: show/hide day dropdown and sync with backend
    $(document).on('change', '.auto-cancel-toggle', function() {
        const $toggle = $(this);
        const userId = $toggle.data('user-id');
        const isOn = $toggle.prop('checked');
        const $wrap = $('.auto-cancel-day-wrap[data-user-id="' + userId + '"]');
        const $dropdown = $('.auto-cancel-dropdown[data-user-id="' + userId + '"]');

        $wrap.css('display', isOn ? 'block' : 'none');

        $toggle.prop('disabled', true);
        $.ajax({
            url: "{{ route('update.autocancel') }}",
            type: "POST",
            data: {
                user_id: userId,
                auto_cancel_date: isOn ? ($dropdown.val() || '1') : '',
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                $toggle.prop('disabled', false);
                if (response.success) {
                    toastr.success(response.message || (isOn ? 'Auto cancel enabled' : 'Auto cancel disabled'));
                } else {
                    toastr.error(response.message || 'Error updating auto cancel');
                }
            },
            error: function() {
                $toggle.prop('disabled', false);
                toastr.error('Error updating auto cancel');
                $wrap.css('display', isOn ? 'none' : 'block');
                $toggle.prop('checked', !isOn);
            }
        });
    });

    // Use event delegation for auto cancel dropdown to work with pagination
    $(document).on('change', '.auto-cancel-dropdown', function() {
        const userId = $(this).data('user-id');
        const selectedValue = $(this).val();
        console.log("Auto Cancel dropdown changed for user:", userId, "Value:", selectedValue);

        $(this).prop('disabled', true);

        $.ajax({
            url: "{{ route('update.autocancel') }}",
            type: "POST",
            data: {
                user_id: userId,
                auto_cancel_date: selectedValue,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                $('.auto-cancel-dropdown[data-user-id="' + userId + '"]').prop('disabled', false);
                if (response.success) {
                    toastr.success(response.message || 'Auto cancel date updated successfully');
                } else {
                    toastr.error(response.message || 'Error updating auto cancel date');
                    // Revert the dropdown to previous value
                    $('.auto-cancel-dropdown[data-user-id="' + userId + '"]').val(response.previous_value || '');
                }
            },
            error: function(xhr) {
                $('.auto-cancel-dropdown[data-user-id="' + userId + '"]').prop('disabled', false);
                toastr.error('Error updating auto cancel date');
                // Revert the dropdown to previous value
                $('.auto-cancel-dropdown[data-user-id="' + userId + '"]').val('');
                console.error(xhr.responseText);
            }
        });
    });

    // Guide Pax input: save via AJAX on blur (when user leaves the field)
    $(document).on('blur', '.guide-pax-input', function() {
        const $input = $(this);
        const userId = $input.data('user-id');
        let value = $input.val().replace(/\D/g, '').slice(0, 2);
        if (value === '') value = '0';
        const num = parseInt(value, 10);
        const guidePax = isNaN(num) ? 0 : Math.max(0, Math.min(99, num));
        $input.val(guidePax);

        $input.prop('disabled', true);
        $.ajax({
            url: "{{ route('update.guidepax') }}",
            type: "POST",
            data: {
                user_id: userId,
                guide_pax: guidePax,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                $input.prop('disabled', false);
                if (response.success) {
                    toastr.success(response.message || 'Guide pax updated successfully');
                } else {
                    toastr.error(response.message || 'Error updating guide pax');
                    if (response.previous_value !== undefined) {
                        $input.val(response.previous_value);
                    }
                }
            },
            error: function(xhr) {
                $input.prop('disabled', false);
                toastr.error('Error updating guide pax');
                if (xhr.responseJSON && xhr.responseJSON.previous_value !== undefined) {
                    $input.val(xhr.responseJSON.previous_value);
                }
                console.error(xhr.responseText);
            }
        });
    });
});
</script>

<!-- Add Typeahead.js library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.11.1/typeahead.bundle.min.js"></script>
@endsection 
 