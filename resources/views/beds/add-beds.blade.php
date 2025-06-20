@extends('layouts.layout')
@extends('layouts.datatablecss')
@section('content')
@include('hotel.tapview', ['hotel' => $hotel])
<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Add New Bed Type
                <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form action="{{ route('beds.store') }}" method="POST" enctype="multipart/form-data" class="card-body">
                @csrf
                <fieldset class="p-3 border rounded shadow-sm">
                    <div class="row">
                        {{--  <div class="mb-3 col-md-6">
                            <label for="hotel_id" class="form-label"><strong>Hotel</strong><span style="color: red; font-weight: bold;">*</span></label>
                            <select name="hotel_id" id="hotel_id" class="form-control" required>
                                <option value="">Select a Hotel</option>
                                @foreach($hotel as $h)
                                <option value="{{ $h->hotel_unique_id }}">{{ $h->name }}</option>
                                @endforeach
                            </select>
                        </div> --}}

                        <!-- Hidden hotel_id field -->
                        <input type="hidden" name="hotel_id" value="{{ $id }}">

                        <div class="col-md-6 mb-6">
                            <label for="name" class="form-label"><strong>Name</strong>
                                <span style="color: red; font-weight: bold;">*</span>
                            </label>
                            <input type="text" id="name" name="bed_type" placeholder="Enter Bed Name" class="form-control" required>
                            @error('bed_type')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Number of Single Bed -->
                        <div class="col-md-3 mb-3">
                            <label for="single_bed" class="form-label"><strong>No. of Single Bed</strong></label>
                            <select id="single_bed" name="single_bed" class="form-control" required>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                            </select>
                            @error('single_bed')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Number of King Beds -->
                        <div class="col-md-3 mb-3">
                            <label for="king_beds" class="form-label"><strong>No. of King Beds</strong></label>
                            <select id="king_beds" name="king_beds" class="form-control" required>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                            </select>
                            @error('king_beds')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Number of Queen Beds -->
                        <div class="col-md-2 mb-3">
                            <label for="queen_beds" class="form-label"><strong>No. of Queen Beds</strong></label>
                            <select id="queen_beds" name="queen_beds" class="form-control" required>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                            </select>
                            @error('queen_beds')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Number of Twin Beds -->
                        <div class="col-md-2 mb-3">
                            <label for="twin_beds" class="form-label"><strong>No. of Twin Beds</strong></label>
                            <select id="twin_beds" name="twin_beds" class="form-control" required>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                            </select>
                            @error('twin_beds')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Number of Bunk Beds -->
                        <div class="col-md-2 mb-3">
                            <label for="bunk_beds" class="form-label"><strong>No. of Bunk Beds</strong></label>
                            <select id="bunk_beds" name="bunk_beds" class="form-control" required>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                            </select>
                            @error('bunk_beds')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="form-check form-switch" style = "padding-left: 3.450em;">
                        <label for="bed_stcomatus" class="form-label"><strong>Status</strong><span style="color: red; font-weight: bold;">*</span></label>
                        <input type="hidden" name="bed_status" value="0">
                        <input class="form-check-input" name="bed_status" type="checkbox" id="bed_status" value="1">
                        <label class="form-check-label"></label>
                        @error('bed_status')
                    <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="row mt-4">
                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn btn-primary">Save And Add More</button>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</div>
<!-- End of the form -->

<!-- Bed Types Listing Section -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Bed Types
                <div class="dropdown">
                    <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" id="exportDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-download me-1"></i> Export
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportCopy"><i class="fas fa-copy me-1"></i> Copy</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportCSV"><i class="fas fa-file-csv me-1"></i> CSV</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportExcel"><i class="fas fa-file-excel me-1"></i> Excel</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportPDF"><i class="fas fa-file-pdf me-1"></i> PDF</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportPrint"><i class="fas fa-print me-1"></i> Print</a></li>
                    </ul>
                </div>
            </h5>
            <div class="card-datatable table-responsive">
                <x-alert />
                <table class="datatables-basic table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Hotel Name</th>
                            <th>Bed Name</th>
                            <th>Single</th>
                            <th>King</th>
                            <th>Queen</th>
                            <th>Twin</th>
                            <th>Bunk</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($beds) > 0)
                        @foreach($beds as $key => $bed)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>
                                    @if($bed->hotel)
                                        {{ $bed->hotel->name }}
                                    @else
                                        Unknown
                                    @endif
                                </td>
                                <td>{{ $bed->name }}</td>
                                <td>{{ $bed->no_of_single_bed }}</td>
                                <td>{{ $bed->no_of_king_bed }}</td>
                                <td>{{ $bed->no_of_queen_bed }}</td>
                                <td>{{ $bed->no_of_twin_bed }}</td>
                                <td>{{ $bed->no_of_bunk_bed }}</td>
                                <td>
                                    <span class="badge bg-label-{{ $bed->is_active == 1 ? 'success' : 'danger' }}">
                                        {{ $bed->is_active == 1 ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <!-- Edit Button -->
                                        <a href="{{ route('beds.edit', $bed->bedId) }}"
                                           class="btn btn-primary btn-sm rounded-circle" 
                                           style="width: 28px; height: 28px; padding: 0;"
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           title="Edit Bed Type">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                                <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z" />
                                            </svg>
                                        </a>
                                
                                        <!-- Delete Button -->
                                        <button type="button"
                                               class="btn btn-danger btn-sm rounded-circle"
                                               style="width: 28px; height: 28px; padding: 0;"
                                               data-bs-toggle="tooltip" 
                                               data-bs-placement="top" 
                                               title="Delete Bed Type" 
                                               onclick="setDeleteForm('{{ route('beds.destroy', $bed->bedId) }}')">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                                <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal - Bootstrap 5 Version -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Bed Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this bed type? </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" action="#" method="post">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End of the Bed Types Listing Section -->
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

<script>
function setDeleteForm(url) {
    document.getElementById('deleteForm').action = url;
    // Bootstrap 5 modal
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>
@endsection