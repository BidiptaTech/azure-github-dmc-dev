@extends('layouts.layout')
@section('title', 'Packaged Attractions')

@section('css')
<style>
    :root {
        --table-border: #e2e8f0;
        --table-head-bg: #f8fafc;
        --table-head-text: #334155;
        --table-body-text: #0f172a;
        --table-muted: #64748b;
        /* Primary brand blue used for status / key accents */
        --table-link: #5c61e6;
    }

    #packagedAttractionsTable thead th {
        background: var(--table-head-bg);
        color: var(--table-head-text);
        font-weight: 600;
        font-size: 0.8125rem;
        padding: 0.5rem 0.75rem;
        border-color: var(--table-border);
        white-space: nowrap;
        line-height: 1.4;
    }

    #packagedAttractionsTable tbody td {
        color: var(--table-body-text);
        border-color: var(--table-border);
        vertical-align: top;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.5;
    }

    /* Package name styling */
    #packagedAttractionsTable .package-title {
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--table-body-text);
    }

    #packagedAttractionsTable .package-id {
        color: var(--table-muted);
        font-size: 0.75rem;
    }

    /* Status badges - use brand blue for Active, neutral for Inactive */
    #packagedAttractionsTable .badge {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.35rem 0.65rem;
        border-radius: 0.375rem;
    }

    #packagedAttractionsTable .badge.bg-success {
        background-color: var(--table-link) !important;
        color: #ffffff;
    }

    #packagedAttractionsTable .badge.bg-danger {
        background-color: #e5e7eb !important;
        color: #111827;
    }

    .th-tooltip {
        cursor: help;
    }

    /* Actions column – same soft icon-badge design as Hotels/Attractions */
    #packagedAttractionsTable td.col-actions {
        white-space: nowrap;
        overflow: visible;
    }

    #packagedAttractionsTable .actions-icons-wrap {
        display: grid;
        grid-template-columns: repeat(3, auto);
        row-gap: 0.5rem;
        column-gap: 0.5rem;
        align-items: center;
        justify-content: start;
        max-width: 100%;
    }

    #packagedAttractionsTable .action-icon-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 32px;
        min-width: 32px;
        padding: 0.35rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        cursor: pointer;
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        flex-shrink: 0;
        text-decoration: none;
        color: inherit;
    }

    #packagedAttractionsTable .action-icon-badge:hover {
        background: #eef2ff;
        border-color: #c7d2fe;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        color: inherit;
    }

    #packagedAttractionsTable .action-icon-badge i,
    #packagedAttractionsTable .action-icon-badge svg {
        font-size: 1rem;
        color: var(--action-color, #475569);
    }
</style>
@endsection

@section('content')
@extends('layouts.datatablecss')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Packaged Attractions</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        <!-- Add New Package Button -->
                        <a href="{{ route('packaged-attractions.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                            <i class="fas fa-plus"></i> Create New Package
                        </a>

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
                
                <!-- Alert messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <div class="d-flex">
                            <i class="fas fa-check-circle me-2"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <table class="datatables-basic table table-bordered" id="packagedAttractionsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Serial Number">No</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Package Name & ID">Package Name</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Adult Price">Adult Price</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Child Price">Child Price</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Senior Price">Senior Price</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Current Status">Status</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Available Actions">Actions</th>
                            <th class="th-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Created Date & Time">Created Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packagedAttractions as $key => $attraction)
                            <tr>
                                <td class="align-top">{{ $attraction->id }}</td>
                                <td class="align-top">
                                    <div class="d-flex flex-column">
                                        <span class="package-title">{{ $attraction->name }}</span>
                                        <small class="package-id">ID: {{ $attraction->package_attraction_id ?? 'N/A' }}</small>
                                    </div>
                                </td>
                                <td class="align-top">${{ number_format($attraction->adult_price, 2) }}</td>
                                <td class="align-top">${{ number_format($attraction->child_price, 2) }}</td>
                                <td class="align-top">${{ number_format($attraction->senior_citizen_price, 2) }}</td>
                                <td class="align-top">
                                    @if($attraction->status == 1)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="align-top col-actions">
                                    <div class="actions-icons-wrap">
                                        <!-- View Button -->
                                        <a href="{{ route('packaged-attractions.show', Crypt::encrypt($attraction->package_attraction_id)) }}" 
                                           class="action-icon-badge"
                                           style="--action-color: #0f766e;"
                                           data-tooltip="View Package">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        
                                        <!-- Edit Button -->
                                        <a href="{{ route('packaged-attractions.edit', Crypt::encrypt($attraction->package_attraction_id)) }}" 
                                           class="action-icon-badge"
                                           style="--action-color: #047857;"
                                           data-tooltip="Edit Package">
                                            <i class="ri-pencil-line"></i>
                                        </a>
                                        
                                        <!-- Delete Button -->
                                        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                                        <button type="button"
                                                class="action-icon-badge"
                                                style="--action-color: #dc2626;"
                                                data-tooltip="Delete Package"
                                                onclick="deletePackagedAttraction('{{ route('packaged-attractions.destroy', Crypt::encrypt($attraction->package_attraction_id)) }}', '{{ $attraction->name }}')">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                        @endif
                                    </div>
                                    <!-- Debug info (remove in production) -->
                                    <small class="d-none">
                                        Route: {{ route('packaged-attractions.destroy', Crypt::encrypt($attraction->package_attraction_id)) }}
                                        ID: {{ $attraction->package_attraction_id }}
                                    </small>
                                </td>
                                <td class="align-top">
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

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this packaged attraction?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" action="" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
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
        // Initialize DataTable with export buttons
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

        // Custom export button functionality
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

    // SweetAlert-based delete for packaged attractions (same pattern as other pages)
    window.deletePackagedAttraction = function(deleteUrl, packageName) {
        Swal.fire({
            title: 'Delete Package?',
            text: `Are you sure you want to delete "${packageName}"? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, keep it'
        }).then((result) => {
            if (result.isConfirmed) {
                const button = document.querySelector(`[onclick*="${deleteUrl}"]`);
                const originalContent = button ? button.innerHTML : '';

                if (button) {
                    button.innerHTML = '<i class="ri-loader-4-line spin"></i> Deleting...';
                    button.disabled = true;
                }

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = deleteUrl;

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken.getAttribute('content');
                    form.appendChild(csrfInput);
                }

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

    // Ensure modal is properly initialized
    $(document).ready(function() {
        // Check if delete modal exists
        if ($('#deleteModal').length) {
            console.log('Delete modal found');
        } else {
            console.log('Delete modal not found');
        }
        
        // Check if Bootstrap is available
        if (typeof bootstrap !== 'undefined') {
            console.log('Bootstrap is available');
        } else {
            console.log('Bootstrap is NOT available');
        }
        
        // Handle form submission
        $('#deleteForm').on('submit', function(e) {
            console.log('Delete form submitted');
            // You can add loading state here if needed
            $(this).find('button[type="submit"]').prop('disabled', true).text('Deleting...');
        });

        // Reset form when modal is hidden
        $('#deleteModal').on('hidden.bs.modal', function () {
            $('#deleteForm').find('button[type="submit"]').prop('disabled', false).text('Delete');
        });

        // Add manual close functionality for fallback
        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
            document.getElementById('deleteModal').classList.remove('show');
            document.body.classList.remove('modal-open');
            
            // Remove backdrop if exists
            var backdrop = document.getElementById('modalBackdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }

        // Add close event listeners
        document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(function(button) {
            button.addEventListener('click', closeModal);
        });

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Test modal functionality
        console.log('Testing modal functionality...');
        try {
            var testModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            console.log('Modal object created successfully');
        } catch (error) {
            console.error('Error creating modal object:', error);
        }
    });
</script>
@endsection
