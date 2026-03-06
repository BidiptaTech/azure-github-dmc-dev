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
<!-- Add SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<style>
    /* Professional Standard Styling */
    .accordion-button {
        background-color: #f8f9fa;
        font-weight: 600;
        font-size: 15px;
        color: #495057;
        border: 1px solid #dee2e6;
    }

    .accordion-button:not(.collapsed) {
        background-color: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }

    .accordion-button:focus {
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .accordion-item {
        border: 1px solid #dee2e6;
        margin-bottom: 0.5rem;
    }

    .accordion-body {
        background-color: #fff;
        padding: 1.25rem;
    }

    /* Facility Card Styling - Improved Layout */
    .facility-card {
        transition: all 0.2s ease;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        height: 100%;
        position: relative;
        background: white;
        overflow: hidden;
    }

    .facility-card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        border-color: #0d6efd;
        transform: translateY(-2px);
    }

    .facility-card.chargeable {
        border-top: 3px solid #ffc107;
    }

    .facility-card.free {
        border-top: 3px solid #198754;
    }

    /* Card Body Layout - Reduced Height */
    .facility-card-body {
        padding: 0.75rem;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    /* Icon Section - Reduced Height */
    .facility-icon-section {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem;
        background: #f8f9fa;
        border-radius: 0.25rem;
        margin-bottom: 0.5rem;
        margin-top: 1rem;
        min-height: 50px;
    }

    .facility-icon {
        max-height: 35px;
        max-width: 100%;
        object-fit: contain;
    }

    /* Card Content */
    .facility-card-content {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .facility-card-title {
        font-size: 13px;
        font-weight: 600;
        color: #212529;
        margin: 0;
        text-align: center;
        line-height: 1.3;
        min-height: 2.4em;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Status Badge - Top Right Corner */
    .facility-status-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        padding: 0.2rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        z-index: 5;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .facility-status-badge.chargeable {
        background-color: #ffc107;
        color: #000;
        border: 1px solid #ffc107;
    }

    .facility-status-badge.free {
        background-color: #198754;
        color: #fff;
        border: 1px solid #198754;
    }

    /* Action Buttons - Simple Icon Styling */
    .facility-card-actions {
        display: flex;
        justify-content: center;
        gap: 0.75rem;
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid #e9ecef;
    }

    .btn-action {
        padding: 0;
        border: none;
        background: transparent;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .btn-action:hover {
        transform: scale(1.15);
    }

    .btn-action i {
        font-size: 16px;
        transition: all 0.2s ease;
    }

    .btn-action-edit {
        color: #047857;
    }

    .btn-action-edit:hover {
        color: #065f46;
    }

    .btn-action-delete {
        color: #dc2626;
    }

    .btn-action-delete:hover {
        color: #b91c1c;
    }

    /* Modal Styling */
    .modal-header {
        background-color: #0d6efd;
        color: white;
        border-bottom: 1px solid #0d6efd;
    }

    .modal-body {
        padding: 1.5rem;
    }

    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        background: white;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 0;
    }

    .page-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #212529;
        margin: 0;
    }

    /* 5 Cards Per Row on Large Screens */
    @media (min-width: 992px) {
        .facility-cards-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem 0.75rem;
        }
        .facility-card-wrapper {
            flex: 0 0 calc(20% - 0.6rem) !important;
            max-width: calc(20% - 0.6rem) !important;
            width: calc(20% - 0.6rem) !important;
            margin-bottom: 1rem;
        }
    }
    
    @media (min-width: 768px) and (max-width: 991px) {
        .facility-card-wrapper {
            flex: 0 0 33.333333% !important;
            max-width: 33.333333% !important;
            width: 33.333333% !important;
            margin-bottom: 1rem;
        }
    }

    /* Add gap for all screen sizes */
    .facility-card-wrapper {
        margin-bottom: 1rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .action-buttons {
            opacity: 1;
        }
    }
</style>
@endsection
@extends('layouts.datatablecss')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="d-flex align-items-center">
                        <h5 class="page-title mb-0">Facilities</h5>
                    </div>

                    <div class="d-flex align-items-center">
                        <!-- Add New Facility Button -->
                        @if(hasPermission('create facility'))
                        <a href="{{ route('facility.create') }}"
                            class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                            <i class="fas fa-plus"></i> Add New Facility
                        </a>
                        @endif
                    </div>
                </div>

                <div style="padding: 15px;">
                    <x-alert />
                </div>
                <hr>

                <!-- Facility Cards Section -->
                @php
    $groupedFacilities = $facilities->groupBy(fn($facility) => $facility->categories->name ?? 'Uncategorized');
@endphp

<div class="accordion mt-3" id="facilityAccordion" style="padding: 0 15px 15px;">
    @foreach($groupedFacilities as $category => $facilitiesGroup)
    <div class="accordion-item">
        <h2 class="accordion-header" id="heading-{{ Str::slug($category) }}">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapse-{{ Str::slug($category) }}" aria-expanded="false"
                aria-controls="collapse-{{ Str::slug($category) }}">
                {{ $category }}
                <span class="badge bg-secondary ms-2">{{ $facilitiesGroup->count() }}</span>
            </button>
        </h2>
        <div id="collapse-{{ Str::slug($category) }}" class="accordion-collapse collapse"
            aria-labelledby="heading-{{ Str::slug($category) }}" data-bs-parent="#facilityAccordion">
            <div class="accordion-body">
                <div class="row g-2 facility-cards-row">
                    @foreach($facilitiesGroup as $facility)
                    <div class="facility-card-wrapper col-lg-2 col-md-3 col-sm-4 col-6">
                        <div class="card facility-card {{ $facility->is_chargeable ? 'chargeable' : 'free' }}">
                            <!-- Status Badge - Top Right Corner -->
                            @if($facility->is_chargeable)
                            <span class="facility-status-badge chargeable">Chargeable</span>
                            @else
                            <span class="facility-status-badge free">Free</span>
                            @endif
                            
                            <div class="facility-card-body">
                                <!-- Icon Section -->
                                <div class="facility-icon-section" 
                                     style="cursor: pointer;" 
                                     data-bs-toggle="modal"
                                     data-bs-target="#facilityModal-{{ $facility->id }}">
                                    <img src="{{ $facility->icon }}" alt="{{ $facility->name }}"
                                        class="facility-icon">
                                </div>

                                <!-- Card Content -->
                                <div class="facility-card-content">
                                    <!-- Title -->
                                    <h6 class="facility-card-title" 
                                        style="cursor: pointer;" 
                                        data-bs-toggle="modal"
                                        data-bs-target="#facilityModal-{{ $facility->id }}">
                                        {{ $facility->name }}
                                    </h6>
                                </div>

                                <!-- Action Buttons -->
                                <div class="facility-card-actions">
                                    @if(hasPermission('edit facility'))
                                    <a href="{{ route('facility.edit', $facility->id) }}" 
                                        class="btn-action btn-action-edit"
                                        title="Edit Facility">
                                        <i class="ri-pencil-line"></i>
                                    </a>
                                    @endif

                                    @if(hasPermission('delete facility'))
                                    <button type="button"
                                        class="btn-action btn-action-delete"
                                        title="Delete Facility"
                                        onclick="deleteFacility('{{ route('facility.destroy', $facility->id) }}', '{{ addslashes($facility->name) }}')">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal -->
                    <div class="modal fade" id="facilityModal-{{ $facility->id }}" tabindex="-1"
                        aria-labelledby="facilityModalLabel-{{ $facility->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="facilityModalLabel-{{ $facility->id }}">
                                        Facility: {{ $facility->name }}
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <img src="{{ $facility->icon }}" alt="{{ $facility->name }}"
                                        style="height: 60px; object-fit: cover; border-radius: 0.375rem; margin-bottom: 15px;">
                                    <h5>{{ $facility->name }}</h5>
                                    <p class="mb-2"><strong>Category:</strong> {{ $facility->categories->name ?? 'No category' }}</p>
                                    @if($facility->is_chargeable == 1)
                                    <p class="mb-2"><strong>Chargeable:</strong> <span class="badge bg-warning text-dark">Yes</span></p>
                                    @if($facility->chargable_comment)
                                    <p class="mb-0"><strong>Comment:</strong> {{ $facility->chargable_comment }}</p>
                                    @endif
                                    @else
                                    <p class="mb-0"><strong>Chargeable:</strong> <span class="badge bg-success">No</span></p>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    @if(hasPermission('edit facility'))
                                    <a href="{{ route('facility.edit', $facility->id) }}" class="btn btn-primary">Edit</a>
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


                <!-- Delete Modal removed - using SweetAlert instead -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Add SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

<!-- DataTable JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable with export buttons (if table exists)
    if ($('.datatables-basic').length) {
        $('.datatables-basic').DataTable({
            responsive: true,
            buttons: [
                'copy',
                'csv',
                'excel',
                'pdf',
                'print'
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            },
            lengthMenu: [10, 25, 50, 100],
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
    }
});

// Facility deletion function with SweetAlert
window.deleteFacility = function(deleteUrl, facilityName) {
    Swal.fire({
        title: 'Delete Facility?',
        text: `Are you sure you want to delete "${facilityName}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create a form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = deleteUrl;

            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken.getAttribute('content');
                form.appendChild(csrfInput);
            }

            // Add method spoofing for DELETE
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
};
</script>

@endsection