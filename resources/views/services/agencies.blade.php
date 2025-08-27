@extends('layouts.layout')

@section('title', 'Select Agencies')

@section('content')
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12 mb-4 order-0">
                <div class="card">
                    <div class="d-flex align-items-end row">
                        <div class="col-sm-7">
                            <div class="card-body">
                                <h5 class="card-title text-primary">Select Agencies</h5>
                                <p class="mb-4">
                                    Choose the travel agencies you want to work with. Select agencies to add them to your partner network for business collaboration.
                                </p>
                            </div>
                        </div>
                        <div class="col-sm-5 text-center text-sm-left">
                            <div class="card-body pb-0 px-0 px-md-4">
                                <i class="ri-building-line" style="font-size: 4rem; color: #65a30d;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selected Agencies Section -->
        @if(isset($selectedAgencies) && count($selectedAgencies) > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Selected Agencies ({{ count($selectedAgencies) }})</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Agency Name</th>
                                <th>Location</th>
                                <th>Contact</th>
                                <th>Branches</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($selectedAgencies as $agency)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="ri-building-line text-muted"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $agency->agency_name }}</strong>
                                                <br>
                                                <small class="text-muted">ID: {{ $agency->agency_id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $agency->city }}, {{ $agency->country }}</td>
                                    <td>
                                        <div>
                                            <small class="d-block">{{ $agency->email }}</small>
                                            <small class="text-muted">{{ $agency->phone }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info">
                                            {{ $agency->total_branches }} 
                                            {{ $agency->total_branches == 1 ? 'Location' : 'Locations' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('agencies.show', Crypt::encrypt($agency->agency_id)) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="ri-eye-line me-1"></i>View
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger remove-agency-btn" 
                                                    data-agency-id="{{ $agency->agency_id }}"
                                                    data-agency-name="{{ $agency->agency_name }}">
                                                <i class="ri-delete-bin-line me-1"></i>Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Available Agencies Section -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Available Agencies</h5>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">Select All</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">Deselect All</button>
                </div>
            </div>
            
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Search Box -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" class="form-control" id="agencySearch" placeholder="Search agencies by name..." onkeyup="filterAgencies()">
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="text-muted" id="agencyCount">Showing all agencies</span>
                    </div>
                </div>
                
                <div class="row" id="agenciesContainer">
                    @if(isset($availableAgencies) && count($availableAgencies) > 0)
                        @foreach($availableAgencies as $agency)
                            <div class="col-lg-4 col-md-6 mb-3 agency-item" data-agency-name="{{ strtolower($agency->agency_name) }}">
                                <div class="card h-100 agency-card" 
                                     data-bs-toggle="tooltip" 
                                     data-bs-html="true"
                                     data-bs-placement="top"
                                     title="<div class='text-start'>
                                                <strong>{{ $agency->agency_name }}</strong><br>
                                                <small>{{ $agency->city }}, {{ $agency->country }}</small><br>
                                                <small>{{ $agency->email }}</small><br>
                                                <small>{{ $agency->total_branches }} {{ $agency->total_branches == 1 ? 'Location' : 'Locations' }}</small>
                                             </div>">
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <h6 class="agency-name mb-1">{{ Str::limit($agency->agency_name, 25) }}</h6>
                                            <small class="text-muted">ID: {{ $agency->agency_id }}</small>
                                        </div>
                                        
                                        <div class="bg-light d-flex align-items-center justify-content-center mb-2" 
                                             style="height: 80px; border-radius: 6px;">
                                            <i class="ri-building-line text-muted" style="font-size: 2rem;"></i>
                                        </div>
                                        
                                        <div class="agency-info">
                                            <p class="text-muted mb-1 small">
                                                <i class="ri-map-pin-line me-1"></i>
                                                {{ Str::limit($agency->city, 15) }}, {{ Str::limit($agency->country, 15) }}
                                            </p>
                                            
                                            <p class="text-muted mb-1 small">
                                                <i class="ri-mail-line me-1"></i>
                                                {{ Str::limit($agency->email, 25) }}
                                            </p>
                                            
                                            <p class="text-muted mb-2 small">
                                                <i class="ri-phone-line me-1"></i>
                                                {{ $agency->phone }}
                                            </p>
                                            
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-label-info small">
                                                    {{ $agency->total_branches }} {{ $agency->total_branches == 1 ? 'Location' : 'Locations' }}
                                                </span>
                                                <span class="badge bg-label-{{ $agency->status ? 'success' : 'secondary' }} small">
                                                    {{ $agency->status ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                            
                                            @if($agency->address)
                                                <p class="small text-muted mb-2">{{ Str::limit($agency->address, 40) }}</p>
                                            @endif
                                            
                                            <button type="button" 
                                                    class="btn btn-success btn-sm w-100 select-agency-btn" 
                                                    data-agency-id="{{ $agency->agency_id }}"
                                                    data-agency-name="{{ $agency->agency_name }}">
                                                <span class="btn-text">
                                                    <i class="ri-add-line me-1"></i>Select Agency
                                                </span>
                                                <span class="btn-loader d-none">
                                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                                    Selecting...
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="ri-building-line" style="font-size: 4rem; color: #ccc;"></i>
                                <h5 class="mt-3 text-muted">No Agencies Available</h5>
                                <p class="text-muted">There are no agencies available for selection at this time.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- / Content -->
</div>

<!-- Remove Agency Modal -->
<div class="modal fade" id="removeAgencyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Remove Agency</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove <strong id="removeAgencyName"></strong> from your selection?</p>
                <p class="text-muted small">This action will remove the agency from your partner network.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveAgency">Remove Agency</button>
            </div>
        </div>
    </div>
</div>

<style>
.agency-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.agency-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.agency-card.selected {
    border-color: #65a30d;
    background-color: rgba(101, 163, 13, 0.05);
}

.btn-success {
    background-color: #65a30d;
    border-color: #65a30d;
}

.btn-success:hover {
    background-color: #4d7c0f;
    border-color: #4d7c0f;
}
</style>

<script>
let currentAgencyId = null;

function selectAll() {
    document.querySelectorAll('.select-agency-btn').forEach(button => {
        if (!button.disabled) {
            button.click();
        }
    });
}

function deselectAll() {
    document.querySelectorAll('.remove-agency-btn').forEach(button => {
        if (!button.disabled) {
            button.click();
        }
    });
}

function filterAgencies() {
    const searchTerm = document.getElementById('agencySearch').value.toLowerCase();
    const agencyItems = document.querySelectorAll('.agency-item');
    let visibleCount = 0;

    agencyItems.forEach(item => {
        const agencyName = item.getAttribute('data-agency-name');
        const matches = agencyName.includes(searchTerm);
        
        if (matches) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    // Update count
    const countElement = document.getElementById('agencyCount');
    if (searchTerm) {
        countElement.textContent = `Showing ${visibleCount} of ${agencyItems.length} agencies`;
    } else {
        countElement.textContent = 'Showing all agencies';
    }
}

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Agency selection functionality
    document.querySelectorAll('.select-agency-btn').forEach(button => {
        button.addEventListener('click', function() {
            const agencyId = this.getAttribute('data-agency-id');
            const agencyName = this.getAttribute('data-agency-name');
            
            // Show loading state
            const btnText = this.querySelector('.btn-text');
            const btnLoader = this.querySelector('.btn-loader');
            btnText.classList.add('d-none');
            btnLoader.classList.remove('d-none');
            this.disabled = true;
            
            // Make AJAX request
            fetch('{{ route('services.agencies.select') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    agency_id: agencyId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showAlert('success', data.message);
                    // Reload page after short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showAlert('error', data.message);
                    // Reset button state
                    btnText.classList.remove('d-none');
                    btnLoader.classList.add('d-none');
                    this.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'An error occurred while selecting the agency.');
                // Reset button state
                btnText.classList.remove('d-none');
                btnLoader.classList.add('d-none');
                this.disabled = false;
            });
        });
    });

    // Agency removal functionality
    document.querySelectorAll('.remove-agency-btn').forEach(button => {
        button.addEventListener('click', function() {
            currentAgencyId = this.getAttribute('data-agency-id');
            const agencyName = this.getAttribute('data-agency-name');
            
            document.getElementById('removeAgencyName').textContent = agencyName;
            
            const modal = new bootstrap.Modal(document.getElementById('removeAgencyModal'));
            modal.show();
        });
    });

    // Confirm removal
    document.getElementById('confirmRemoveAgency').addEventListener('click', function() {
        if (!currentAgencyId) return;
        
        // Make AJAX request
        fetch('{{ route('services.agencies.remove') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                agency_id: currentAgencyId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hide modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('removeAgencyModal'));
                modal.hide();
                
                // Show success message
                showAlert('success', data.message);
                
                // Reload page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'An error occurred while removing the agency.');
        });
    });
});

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert at the top of the card body
    const cardBody = document.querySelector('.card-body');
    cardBody.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        const alert = cardBody.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}
</script>
@endsection
