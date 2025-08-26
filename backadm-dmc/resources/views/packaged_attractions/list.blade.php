@extends('layouts.layout')
@section('title', 'Packaged Attractions')

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

                <table class="datatables-basic table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Package Name</th>
                            <th>Adult Price</th>
                            <th>Child Price</th>
                            <th>Senior Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                            <th>Created Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packagedAttractions as $key => $attraction)
                            <tr>
                                <td>{{ $attraction->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h6 class="mb-0">{{ $attraction->name }}</h6>
                                            <small class="text-muted">ID: {{ $attraction->package_attraction_id ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>${{ number_format($attraction->adult_price, 2) }}</td>
                                <td>${{ number_format($attraction->child_price, 2) }}</td>
                                <td>${{ number_format($attraction->senior_citizen_price, 2) }}</td>
                                <td>
                                    @if($attraction->status == 1)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td style="display: inline-block; white-space: nowrap;">
                                    <!-- View Button -->
                                    <a href="{{ route('packaged-attractions.show', Crypt::encrypt($attraction->package_attraction_id)) }}" 
                                        class="btn btn-primary btn-sm rounded-circle" 
                                        style="width: 28px; height: 28px; padding: 0;">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 0 24 24" width="16px" fill="#ffffff">
                                            <path d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                        </svg>
                                    </a>
                                    
                                    <!-- Edit Button -->
                                    <a href="{{ route('packaged-attractions.edit', Crypt::encrypt($attraction->package_attraction_id)) }}" 
                                        class="btn btn-info btn-sm rounded-circle" 
                                        style="width: 28px; height: 28px; padding: 0;">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                            <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/>
                                        </svg>
                                    </a>
                                    
                                    <!-- Delete Button -->
                                    <button type="button" 
                                        class="btn btn-danger btn-sm rounded-circle" 
                                        style="width: 28px; height: 28px; padding: 0;" 
                                        onclick="setDeleteForm('{{ route('packaged-attractions.destroy', Crypt::encrypt($attraction->package_attraction_id)) }}', '{{ $attraction->name }}')"
                                        title="Delete {{ $attraction->name }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                            <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                        </svg>
                                    </button>
                                    <!-- Debug info (remove in production) -->
                                    <small class="d-none">
                                        Route: {{ route('packaged-attractions.destroy', Crypt::encrypt($attraction->package_attraction_id)) }}
                                        ID: {{ $attraction->package_attraction_id }}
                                    </small>
                                </td>
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

    function setDeleteForm(action, attractionName) {
        console.log('Setting delete form action:', action);
        console.log('Attraction name:', attractionName);
        
        document.getElementById('deleteForm').action = action;
        
        // Update modal body with attraction name
        var modalBody = document.querySelector('#deleteModal .modal-body p:first-child');
        if (modalBody) {
            modalBody.innerHTML = `Are you sure you want to delete the packaged attraction "<strong>${attractionName}</strong>"?`;
        }
        
        // Try to show the modal using Bootstrap 5
        try {
            if (typeof bootstrap !== 'undefined') {
                var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                deleteModal.show();
            } else {
                // Fallback: show modal manually
                document.getElementById('deleteModal').style.display = 'block';
                document.getElementById('deleteModal').classList.add('show');
                document.body.classList.add('modal-open');
                
                // Add backdrop
                var backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.id = 'modalBackdrop';
                document.body.appendChild(backdrop);
            }
        } catch (error) {
            console.error('Error showing modal:', error);
            // Fallback: show modal manually
            document.getElementById('deleteModal').style.display = 'block';
            document.getElementById('deleteModal').classList.add('show');
            document.body.classList.add('modal-open');
        }
    }

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
