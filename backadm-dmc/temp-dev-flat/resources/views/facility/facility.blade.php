@extends('layouts.layout')
@section('title', 'Facility')
{{-- @section('css')
    <link rel="stylesheet" href="{{ URL::asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet"
        href="{{ URL::asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet"
        href="{{ URL::asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/ekko-lightbox/dist/ekko-lightbox.css" rel="stylesheet">
    <link href="{{ URL::asset('build/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
@endsection --}}
@section('css')
<style>
    .accordion-button {
        background: linear-gradient(90deg, #e3f2fd, #bbdefb);
        font-weight: 600;
        font-size: 16px;
    }

    .accordion-button:not(.collapsed) {
        background: linear-gradient(90deg, #90caf9, #64b5f6);
        color: #fff;
    }

    .facility-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .facility-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    }

    .facility-card.chargeable {
        background-color: #ffe0b2 !important;
    }

    .facility-card.free {
        background-color: #e8f5e9 !important;
    }

    .card-title {
        font-size: 13px;
        font-weight: bold;
        color: #333;
    }
</style>

@endsection
@extends('layouts.datatablecss')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Facilities</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        <!-- Add New Facility Button -->
                        @if(hasPermission('create facility'))
                        <a href="{{ route('facility.create') }}"
                            class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                            <i class="fas fa-plus"></i> Add New Facility
                        </a>
                        @endif
                    </div>
                </div>
                <x-alert />
                <hr>

                <!-- Facility Cards Section -->
                @php
    $groupedFacilities = $facilities->groupBy(fn($facility) => $facility->categories->name ?? 'Uncategorized');
@endphp

<div class="accordion mt-4" id="facilityAccordion">
    @foreach($groupedFacilities as $category => $facilitiesGroup)
    <div class="accordion-item border rounded shadow-sm mb-2">
        <h2 class="accordion-header" id="heading-{{ Str::slug($category) }}">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapse-{{ Str::slug($category) }}" aria-expanded="false"
                aria-controls="collapse-{{ Str::slug($category) }}">
                🎯 {{ $category }}
            </button>
        </h2>
        <div id="collapse-{{ Str::slug($category) }}" class="accordion-collapse collapse"
            aria-labelledby="heading-{{ Str::slug($category) }}" data-bs-parent="#facilityAccordion">
            <div class="accordion-body bg-light rounded-bottom">
                <div class="row">
                    @foreach($facilitiesGroup as $facility)
                    <div class="col-md-2 col-sm-4 col-6 mb-3 mt-4">
                        <div class="card position-relative facility-card {{ $facility->is_chargeable ? 'chargeable' : 'free' }}"
                            style="border: 1px solid #d0d0d0; border-radius: 12px; height: 100%;">
                            
                            <!-- Action Buttons -->
                            <div class="position-absolute" style="top: -8px; right: -8px; display: flex; gap: 5px;">
                                @if(hasPermission('edit facility'))
                                <a href="{{ route('facility.edit', $facility->id) }}" class="btn btn-outline-primary btn-sm"
                                    title="Edit"
                                    style="border-radius: 50%; width: 32px; height: 32px; display: flex; justify-content: center; align-items: center;">
                                    ✏️
                                </a>
                                @endif

                                @if(hasPermission('delete facility'))
                                <button type="button"
                                    class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center rounded-circle"
                                    style="width: 32px; height: 32px; padding: 0;"
                                    data-bs-toggle="modal" data-bs-target="#deleteModal"
                                    onclick="setDeleteForm('{{ route('facility.destroy', $facility->id) }}')">
                                    🗑️
                                </button>
                                @endif
                            </div>

                            <!-- Facility Icon and Name -->
                            <div class="card-body d-flex justify-content-between align-items-center text-center"
                                style="padding: 10px; cursor: pointer;" data-bs-toggle="modal"
                                data-bs-target="#facilityModal-{{ $facility->id }}">
                                <div class="d-flex justify-content-center align-items-center" style="flex-grow: 1;">
                                    <img src="{{ $facility->icon }}" alt="Facility Icon"
                                        style="height: 30px; object-fit: cover; border-radius: 5px;">
                                </div>
                                <h6 class="card-title m-0" style="flex-grow: 2;">
                                    {{ $facility->name }}
                                </h6>
                            </div>
                        </div>
                    </div>

                    <!-- Modal -->
                    <div class="modal fade" id="facilityModal-{{ $facility->id }}" tabindex="-1"
                        aria-labelledby="facilityModalLabel-{{ $facility->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="facilityModalLabel-{{ $facility->id }}">
                                        Facility: {{ $facility->name }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <img src="{{ $facility->icon }}" alt="Facility Icon"
                                        style="height: 60px; object-fit: cover; border-radius: 10px; margin-bottom: 15px;">
                                    <h5>{{ $facility->name }}</h5>
                                    <span><b>Category:</b> {{ $facility->categories->name ?? 'No category' }}</span><br>
                                    @if($facility->is_chargeable == 1)
                                    <span><b>Chargeable:</b> Yes</span><br>
                                    <span><b>Comment:</b> {{ $facility->chargable_comment ?? 'No comment available' }}</span>
                                    @else
                                    <span><b>Chargeable:</b> No</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>


                <!-- Delete Confirmation Modal -->
                @if(hasPermission('delete facility'))
                <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteModalLabel">Confirmation</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to delete this facility?
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
                @endif
                <!-- End of Modal -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')

{{-- <script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script> --}}

{{-- <script>
    $(document).ready(function() {
        $('#example').DataTable();
    });

    $(document).ready(function() {
        var table = $('#example2').DataTable({
            order: [
                [4, 'desc']
            ], // Adjust column index based on where `created_at` would logically be
            lengthChange: false,
            buttons: ['copy', 'excel', 'pdf', 'print']
        });

        table.buttons().container().appendTo('#example2_wrapper .col-md-6:eq(0)');
    });

    function setDeleteForm(action) {
        document.getElementById('deleteForm').action = action;
    }
</script> --}}

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
@endsection