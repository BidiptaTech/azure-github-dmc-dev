@extends('layouts.layout')
@section('title', 'Agents')
@extends('layouts.datatablecss')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  .badge {
        font-size: 0.75rem;
        padding: 0.4em 0.6em;
        border-radius: 0.5rem;
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;  
        display: inline-block;
        vertical-align: middle;
        margin-bottom: 0.5em;
        cursor: default;
    }
</style>
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
      <div class="card-datatable table-responsive pt-0">
            <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0">Agents</h5>
                </div>

                <div class="d-flex justify-content-between gap-3">
                    <!-- Add New User Button -->
                    @if(!in_array(auth()->user()->role_id, [1, 2, 3, 4]))
                      <a href="{{ route('agents.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                          <i class="fas fa-plus"></i> Add New Agent
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
              <th>DMC Company</th>
              <th>Agency Company</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Country & City</th>
              <th>Service Country</th>
              {{-- @if(hasPermission('edit agents') || hasPermission('delete agents')) --}}
                  <th>Action</th>
              {{-- @endif --}}
              <th>Created At</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($agents as $key => $agent)

              @php
                $dmcIds = is_array($agent->dmc_id) ? $agent->dmc_id : json_decode($agent->dmc_id, true);

                $agent->dmc_companies = [];
            
                if (is_array($dmcIds)) {
                    $agent->dmc_companies = App\Models\User::whereIn('userId', $dmcIds)
                        ->pluck('company_name')
                        ->filter()
                        ->values()
                        ->toArray();
                }
              @endphp
              <tr>
                <td>{{ ++$key }}</td>
                <td>
                  @php
                      $dmcCompanies = $agent->dmc_companies ?? [];
                      $firstDmc = $dmcCompanies[0] ?? null;
                      $extraDmcs = array_slice($dmcCompanies, 1);
                      $moreCount = count($extraDmcs);
                      $modalId = 'dmcModal' . $loop->index;
                  @endphp
              
                  @if ($firstDmc && ($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4 || $user->role_id == 19 || $user->role_id == 20))
                      <span class="badge bg-primary text-white me-1">
                          {{ \Illuminate\Support\Str::limit($firstDmc, 20) }}
                      </span>
              
                      @if ($moreCount > 0)
                          <button type="button"
                                  class="btn btn-sm btn-outline-secondary"
                                  data-bs-toggle="modal"
                                  data-bs-target="#{{ $modalId }}">
                              +{{ $moreCount }} More
                          </button>
              
                          <!-- Modal for DMC List -->
                          <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered">
                                  <div class="modal-content">
                                      <div class="modal-header">
                                          <h5 class="modal-title" id="{{ $modalId }}Label">DMC Companies</h5>
                                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                      </div>
                                      <div class="modal-body">
                                          <ul class="list-group">
                                              @foreach ($dmcCompanies as $company)
                                                  <li class="list-group-item">{{ $company }}</li>
                                              @endforeach
                                          </ul>
                                      </div>
                                      <div class="modal-footer">
                                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      @endif
                  @elseif($firstDmc && ($user->role_id == 11 || $user->role_id == 33 || $user->role_id == 37 || $user->role_id == 38 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138))
                      <span class="badge bg-primary text-white me-1">
                          {{ $user->company_name }}
                      </span>
                  @else
                      <span class="text-muted">No DMC assigned</span>
                  @endif
                </td>                                               
                <td>{{ $agent->company_name ?? 'N/A' }}</td>
                <td>{{ $agent->name }}</td>
                <td>{{ $agent->email }}</td>
                <td>{{ $agent->phone }}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                        <div>
                            <span class="fw-medium text-primary">{{ $agent->user_country ?? 'N/A' }}</span>
                            <br>
                            <small class="text-muted">{{ $agent->city ?? 'N/A' }}</small>
                        </div>
                    </div>
                </td>
                <td>
                  @if($agent->country)
                    <div class="d-flex align-items-center">
                        <i class="fas fa-globe text-primary me-2"></i>
                        <div>
                            <span class="fw-medium text-dark">{{ str_replace(',', ', ', $agent->country) }}</span>
                        </div>
                    </div>
                  @else
                    <div class="d-flex align-items-center">
                        <i class="fas fa-globe text-muted me-2"></i>
                        <span class="text-muted">-</span>
                    </div>
                  @endif
                </td>
                {{-- @if(hasPermission('edit agents') || hasPermission('delete agents')) --}}
                <td>
                  <div class="d-flex gap-2">
                    <!-- Edit Button -->
                    {{-- @if(hasPermission('edit agents')) --}}
                    <a href="{{ route('agents.edit', Crypt::encrypt($agent->agent_id)) }}"
                      class="btn btn-primary btn-sm d-flex align-items-center justify-content-center rounded-circle" 
                      style="width: 28px; height: 28px; padding: 0;">
                      <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                        <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/>
                      </svg>
                    </a>
                    {{-- @endif --}}
                
                    <!-- Delete Button -->
                    {{-- @if(hasPermission('delete agents')) --}}
                    <button type="button" 
                            class="btn btn-danger btn-sm d-flex align-items-center justify-content-center rounded-circle" 
                            style="width: 28px; height: 28px; padding: 0;" 
                            data-toggle="modal" 
                            data-target="#deleteModal"
                           
                            onclick="setDeleteForm('{{ route('agents.destroy', $agent->agent_id) }}')">
                      <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                        <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                      </svg>
                    </button>
                    {{-- @endif --}}
                  </div>
                </td>
                {{-- @endif --}}
                <td>
                  <div class="d-flex flex-column">
                    <span>{{ $agent->created_at->format('D,  M d, Y') }}</span>
                    <small class="text-muted">{{ $agent->created_at->format('h:i A') }}</small>
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
@endsection 