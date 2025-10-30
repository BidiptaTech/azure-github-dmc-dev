@extends('layouts.layout')

@section('title', 'Tax Management')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Tax /</span> All Taxes
    </h4>

    <!-- Alert Container for AJAX messages -->
    <div id="alertContainer"></div>

    <!-- Display flash message -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                var alert = document.getElementById('success-alert');
                if (alert) {
                    var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }
            }, 3000);
        });
    </script>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
        <i class="ri-error-warning-line me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                var alert = document.getElementById('error-alert');
                if (alert) {
                    var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }
            }, 5000);
        });
    </script>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Tax Management</h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaxModal">
                <i class="ri-add-line me-1"></i> Add New Tax
            </button>
        </div>
        
        <!-- Filters -->
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('tax.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, country, city..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="percentage" {{ request('type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                        <option value="fixed" {{ request('type') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ri-search-line me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tax Name</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Calculate On</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($taxes as $key => $tax)
                        <tr>
                            <td>{{ $taxes->firstItem() + $key }}</td>
                            <td>{{ $tax->tax_name }}</td>
                            <td>
                                <span class="badge bg-{{ $tax->tax_type == 'percentage' ? 'info' : 'success' }}">
                                    {{ ucfirst($tax->tax_type) }}
                                </span>
                            </td>
                            <td>
                                @if($tax->tax_type == 'percentage')
                                    {{ $tax->tax_value }}%
                                @else
                                    ${{ number_format($tax->tax_value, 2) }}
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ ucfirst(str_replace('_', ' ', $tax->calculate_on)) }}
                                </span>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input status-toggle" type="checkbox" 
                                           data-tax-id="{{ $tax->tax_id }}" 
                                           {{ $tax->is_active ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-icon btn-warning edit-tax" 
                                        data-tax-id="{{ $tax->tax_id }}" 
                                        title="Edit">
                                    <i class="ri-edit-line"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-icon btn-danger delete-tax" 
                                        data-tax-id="{{ $tax->tax_id }}" 
                                        data-bs-toggle="tooltip" title="Delete">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="ri-inbox-line" style="font-size: 48px; color: #ccc;"></i>
                                <p class="text-muted mt-2">No taxes found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $taxes->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Add Tax Modal -->
<div class="modal fade" id="addTaxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Tax</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addTaxForm" action="{{ route('tax.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tax Name <span class="text-danger">*</span></label>
                            <input type="text" name="tax_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tax Type <span class="text-danger">*</span></label>
                            <select name="tax_type" id="add_tax_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tax Value <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="tax_value" class="form-control" required>
                        </div>
                        <div class="col-md-6" id="add_if_fixed_section" style="display: none;">
                            <label class="form-label">If fixed <span class="text-danger">*</span></label>
                            <select name="if_fixed" id="add_if_fixed" class="form-select">
                                <option value="">Select Base</option>
                                <option value="person">Per person</option>
                                <option value="tour">Per tour</option>
                                <option value="per_tour_per_day">Per tour per tour</option>
                                <option value="per_person_per_day">Per person per day</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Calculate On <span class="text-danger">*</span></label>
                            <select name="calculate_on" id="add_calculate_on" class="form-select" required>
                                <option value="">Select Option</option>
                                <option value="total">Total Amount</option>
                                @foreach($allTaxes as $index => $existingTax)
                                    <option value="tax_{{ $existingTax->tax_id }}">{{ $existingTax->tax_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Save Tax
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Tax Modal -->
<div class="modal fade" id="editTaxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Tax</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTaxForm" action="" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_tax_id" name="tax_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tax Name <span class="text-danger">*</span></label>
                            <input type="text" name="tax_name" id="edit_tax_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tax Type <span class="text-danger">*</span></label>
                            <select name="tax_type" id="edit_tax_type" class="form-select" required>
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tax Value <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="tax_value" id="edit_tax_value" class="form-control" required>
                        </div>
                        <div class="col-md-6" id="edit_if_fixed_section" style="display: none;">
                            <label class="form-label">If fixed <span class="text-danger">*</span></label>
                            <select name="if_fixed" id="edit_if_fixed" class="form-select">
                                <option value="">Select Base</option>
                                <option value="person">Per person</option>
                                <option value="tour">Per tour</option>
                                <option value="per_tour_per_day">per tour per day</option>
                                <option value="per_person_per_day">Per person per day</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Calculate On <span class="text-danger">*</span></label>
                            <select name="calculate_on" id="edit_calculate_on" class="form-select" required>
                                <option value="">Select Option</option>
                                <option value="total">Total Amount</option>
                                @foreach($allTaxes as $index => $existingTax)
                                    <option value="tax_{{ $existingTax->tax_id }}" data-tax-id="{{ $existingTax->tax_id }}">{{ $existingTax->tax_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Update Tax
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show alert message function
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show animate__animated animate__fadeInDown" role="alert">
                <i class="ri-${type === 'success' ? 'checkbox-circle' : 'error-warning'}-line me-2"></i>
                <strong>${message}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        document.getElementById('alertContainer').innerHTML = alertHtml;
        
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    }

    // Handle tax type change for Add Modal
    const addTaxType = document.getElementById('add_tax_type');
    const addIfFixedSection = document.getElementById('add_if_fixed_section');
    const addIfFixed = document.getElementById('add_if_fixed');
    
    addTaxType.addEventListener('change', function() {
        if (this.value === 'fixed') {
            addIfFixedSection.style.display = 'block';
            addIfFixed.setAttribute('required', 'required');
        } else {
            addIfFixedSection.style.display = 'none';
            addIfFixed.removeAttribute('required');
            addIfFixed.value = '';
        }
    });

    // Handle tax type change for Edit Modal
    const editTaxType = document.getElementById('edit_tax_type');
    const editIfFixedSection = document.getElementById('edit_if_fixed_section');
    const editIfFixed = document.getElementById('edit_if_fixed');
    
    editTaxType.addEventListener('change', function() {
        if (this.value === 'fixed') {
            editIfFixedSection.style.display = 'block';
            editIfFixed.setAttribute('required', 'required');
        } else {
            editIfFixedSection.style.display = 'none';
            editIfFixed.removeAttribute('required');
            editIfFixed.value = '';
        }
    });

    // Handle Add Tax Form submission via AJAX
    const addTaxForm = document.getElementById('addTaxForm');
    addTaxForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        
        // Disable submit button and show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => {
            return response.json().then(data => ({
                status: response.status,
                ok: response.ok,
                data: data
            }));
        })
        .then(result => {
            if (result.ok && result.data.success) {
                // Show success message
                showAlert('success', result.data.message);
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addTaxModal'));
                modal.hide();
                
                // Reset form
                addTaxForm.reset();
                
                // Reload page to show new tax
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                // Show error message
                showAlert('danger', result.data.message || 'Error creating tax. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Error creating tax. Please try again.');
        })
        .finally(() => {
            // Re-enable submit button
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        });
    });

    // Handle Edit Tax button click
    const editButtons = document.querySelectorAll('.edit-tax');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const taxId = this.getAttribute('data-tax-id');
            
            // Fetch tax data via AJAX
            fetch(`{{ url('tax') }}/${taxId}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Tax data received:', data);
                    
                    // Get tax object from response
                    const tax = data.tax || data;
                    
                    // Populate the form fields
                    document.getElementById('edit_tax_id').value = tax.tax_id;
                    document.getElementById('edit_tax_name').value = tax.tax_name;
                    document.getElementById('edit_tax_type').value = tax.tax_type;
                    document.getElementById('edit_tax_value').value = tax.tax_value;
                    document.getElementById('edit_calculate_on').value = tax.calculate_on;
                    document.getElementById('edit_description').value = tax.description || '';
                    
                    // Handle if_fixed section visibility based on tax type
                    if (tax.tax_type === 'fixed') {
                        editIfFixedSection.style.display = 'block';
                        editIfFixed.setAttribute('required', 'required');
                        document.getElementById('edit_if_fixed').value = tax.if_fixed || '';
                    } else {
                        editIfFixedSection.style.display = 'none';
                        editIfFixed.removeAttribute('required');
                    }
                    
                    // Hide current tax from calculate_on dropdown to prevent circular dependency
                    const calculateOnSelect = document.getElementById('edit_calculate_on');
                    const options = calculateOnSelect.querySelectorAll('option');
                    options.forEach(option => {
                        const optionTaxId = option.getAttribute('data-tax-id');
                        if (optionTaxId && optionTaxId == tax.tax_id) {
                            option.style.display = 'none';
                            option.disabled = true;
                        } else if (optionTaxId) {
                            option.style.display = 'block';
                            option.disabled = false;
                        }
                    });
                    
                    // Update form action
                    document.getElementById('editTaxForm').action = `{{ url('tax') }}/${taxId}`;
                    
                    // Open the modal after data is loaded
                    const editModal = new bootstrap.Modal(document.getElementById('editTaxModal'));
                    editModal.show();
                })
                .catch(error => {
                    console.error('Error fetching tax data:', error);
                    showAlert('danger', 'Error loading tax data: ' + error.message);
                });
        });
    });

    // Handle status toggle
    const statusToggles = document.querySelectorAll('.status-toggle');
    statusToggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const taxId = this.getAttribute('data-tax-id');
            const isActive = this.checked ? 1 : 0;
            const statusText = isActive ? 'activated' : 'deactivated';
            
            fetch(`{{ url('tax') }}/${taxId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ is_active: isActive })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', `Tax ${statusText} successfully!`);
                } else {
                    this.checked = !this.checked;
                    showAlert('danger', data.message || 'Error updating status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.checked = !this.checked;
                showAlert('danger', 'Error updating tax status. Please try again.');
            });
        });
    });

    // Handle delete button
    const deleteButtons = document.querySelectorAll('.delete-tax');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const taxId = this.getAttribute('data-tax-id');
            
            if (confirm('Are you sure you want to delete this tax?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('tax') }}/${taxId}`;
                
                const csrfField = document.createElement('input');
                csrfField.type = 'hidden';
                csrfField.name = '_token';
                csrfField.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                
                form.appendChild(csrfField);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endsection

