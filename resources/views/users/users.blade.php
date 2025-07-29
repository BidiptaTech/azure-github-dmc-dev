@extends('layouts.layout')
@section('title', 'Users')
@extends('layouts.datatablecss')

@section('content')
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

              @if((auth::user()->role_id == 10 || auth::user()->role_id == 9 || auth::user()->role_id == 8 || auth::user()->role_id == 7 || auth::user()->role_id == 6 || auth::user()->role_id == 5 || auth::user()->role_id == 4 || auth::user()->role_id == 3 || auth::user()->role_id == 2 || auth::user()->role_id == 1))
                <th style="min-width: 140px;">
                  <div class="text-center">
                   
                    <div class="d-flex justify-content-between mt-1" style="font-size: 10px;">
                      <span class="fw-bold">Zone On</span>
                      <span class="fw-bold">Price Hide</span>
                      <span class="fw-bold">Email On</span>
                    </div>
                  </div>
                </th>
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
              <tr>
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

                @if((auth::user()->role_id == 10 || auth::user()->role_id == 9 || auth::user()->role_id == 8 || auth::user()->role_id == 7 || auth::user()->role_id == 6 || auth::user()->role_id == 5 || auth::user()->role_id == 4 || auth::user()->role_id == 3 || auth::user()->role_id == 2 || auth::user()->role_id == 1))
                <td>
                  @if($user->role_id == 11)
                    <div class="d-flex justify-content-between align-items-center gap-2" style="min-width: 130px;">
                      @if(auth::user()->role_id == 10)
                        <!-- Zone On Toggle -->
                        <div class="form-check form-switch">
                            <input type="hidden" name="zone_on" value="0">
                            <input {{$user->zone_on == 1 ? 'checked' : ''}} 
                                class="form-check-input zone-toggle" 
                                data-user-id="{{ $user->userId }}"
                                type="checkbox" 
                                id="zone_on_{{ $user->userId }}"
                                value="1" 
                                style="width: 25px; height: 15px;">
                        </div>
                        
                        <!-- Price Hide Toggle -->
                        <div class="form-check form-switch">
                            <input type="hidden" name="price_hide" value="0">
                            <input {{$user->price_hide == 1 ? 'checked' : ''}} 
                                class="form-check-input price-hide_toggle" 
                                data-user-id="{{ $user->userId }}"
                                type="checkbox" 
                                id="price_hide_{{ $user->userId }}"
                                value="1" 
                                style="width: 25px; height: 15px;">
                        </div>
                        
                        <!-- Email On Toggle -->
                        <div class="form-check form-switch">
                            <input type="hidden" name="email_on" value="0">
                            <input {{$user->email_on == 1 ? 'checked' : ''}} 
                                class="form-check-input email-toggle" 
                                data-user-id="{{ $user->userId }}"
                                type="checkbox" 
                                id="email_on_{{ $user->userId }}"
                                value="1" 
                                style="width: 25px; height: 15px;">
                        </div>
                      @else
                        <!-- Zone On Toggle (Disabled) -->
                        <div class="form-check form-switch">
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
                        
                        <!-- Price Hide Toggle (Disabled) -->
                        <div class="form-check form-switch">
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
                        
                        <!-- Email On Toggle (Disabled) -->
                        <div class="form-check form-switch">
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
                      @endif
                    </div>
                  @else
                    <div class="text-center">--</div>
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
                      <a href="{{ route('users.edit', $user->userId) }}" 
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
    $('.price-hide_toggle').on('change', function() {
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
    $('.zone-toggle').on('change', function() {
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
    $('.email-toggle').on('change', function() {
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

<!-- Add Typeahead.js library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.11.1/typeahead.bundle.min.js"></script>
@endsection 
 