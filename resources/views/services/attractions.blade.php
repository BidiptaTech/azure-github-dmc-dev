@extends('layouts.layout')

@section('title', 'Select Attractions')

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
                                <h5 class="card-title text-primary">Select Attractions</h5>
                                <p class="mb-4">
                                    Choose the attractions and experiences you want to offer to your customers. Click on individual attractions to select them.
                                </p>
                            </div>
                        </div>
                        <div class="col-sm-5 text-center text-sm-left">
                            <div class="card-body pb-0 px-0 px-md-4">
                                <i class="ri-landscape-line" style="font-size: 4rem; color: #65a30d;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selected Attractions Section -->
        @if(isset($selectedAttractions) && count($selectedAttractions) > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Selected Attractions ({{ count($selectedAttractions) }})</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Attraction Name</th>
                                <th>Location</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($selectedAttractions as $attraction)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($attraction->master_image)
                                                <img src="{{ $attraction->master_image }}" 
                                                     alt="{{ $attraction->name }}" 
                                                     class="rounded me-2" 
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="ri-landscape-line text-muted"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <strong>{{ $attraction->name }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $attraction->location ?? $attraction->city }}, {{ $attraction->country }}</td>
                                    <td>
                                        <span class="badge bg-label-success">
                                            {{ $attraction->adult_price ? 'Paid' : 'Free' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('attraction.edit', Crypt::encrypt($attraction->attraction_id)) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="ri-edit-line me-1"></i>Edit
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger remove-attraction-btn" 
                                                    data-attraction-id="{{ $attraction->id }}"
                                                    data-attraction-name="{{ $attraction->name }}">
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

        <!-- Available Attractions Section -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Available Attractions & Experiences</h5>
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
                            <input type="text" class="form-control" id="attractionSearch" placeholder="Search attractions by name..." onkeyup="filterAttractions()">
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="text-muted" id="attractionCount">Showing all attractions</span>
                    </div>
                </div>
                
                <div class="row" id="attractionsContainer">
                    @if(isset($availableAttractions) && count($availableAttractions) > 0)
                        @foreach($availableAttractions as $attraction)
                            <div class="col-lg-3 col-md-6 mb-3 attraction-item" data-attraction-name="{{ strtolower($attraction->name) }}">
                                <div class="card h-100 attraction-card" 
                                     data-bs-toggle="tooltip" 
                                     data-bs-html="true"
                                     data-bs-placement="top"
                                     title="<div class='text-start'>
                                                <strong>{{ $attraction->name }}</strong><br>
                                                <small>{{ $attraction->location ?? $attraction->city }}, {{ $attraction->country }}</small><br>
                                                <small>{{ $attraction->adult_price ? 'Paid Experience' : 'Free Experience' }}</small>
                                             </div>">
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <h6 class="attraction-name mb-1">{{ Str::limit($attraction->name, 25) }}</h6>
                                        </div>
                                        
                                        @if($attraction->master_image)
                                            <img src="{{ $attraction->master_image }}" 
                                                 class="card-img-top mb-2" 
                                                 alt="{{ $attraction->name }}"
                                                 style="height: 120px; object-fit: cover; border-radius: 6px;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center mb-2" 
                                                 style="height: 120px; border-radius: 6px;">
                                                <i class="ri-landscape-line text-muted" style="font-size: 2rem;"></i>
                                            </div>
                                        @endif
                                        
                                        <div class="attraction-info">
                                            <p class="text-muted mb-1 small">
                                                <i class="ri-map-pin-line me-1"></i>
                                                {{ Str::limit($attraction->location ?? $attraction->city, 15) }}
                                            </p>
                                            
                                            @if($attraction->description)
                                                <p class="small text-muted mb-2">{{ Str::limit(strip_tags($attraction->description), 40) }}</p>
                                            @endif
                                            
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-label-success small">
                                                    {{ $attraction->adult_price ? 'Paid' : 'Free' }}
                                                </span>
                                            </div>
                                            
                                            @if($attraction->adult_price)
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        Adult: ${{ number_format($attraction->adult_price, 2) }}
                                                        @if($attraction->child_price)
                                                            | Child: ${{ number_format($attraction->child_price, 2) }}
                                                        @endif
                                                    </small>
                                                </div>
                                            @endif
                                            
                                            <button type="button" 
                                                    class="btn btn-success btn-sm w-100 select-attraction-btn" 
                                                    data-attraction-id="{{ $attraction->id }}"
                                                    data-attraction-name="{{ $attraction->name }}">
                                                <span class="btn-text">
                                                    <i class="ri-add-line me-1"></i>Select Attraction
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
                                <i class="ri-landscape-line" style="font-size: 4rem; color: #ccc;"></i>
                                <h5 class="mt-3 text-muted">No Attractions Available</h5>
                                <p class="text-muted">There are no attractions available for selection at this time.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- / Content -->
</div>

<!-- Remove Attraction Modal -->
<div class="modal fade" id="removeAttractionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Remove Attraction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove <strong id="removeAttractionName"></strong> from your selection?</p>
                <p class="text-muted small">This action will remove the attraction from your available offerings.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveAttraction">Remove Attraction</button>
            </div>
        </div>
    </div>
</div>

<style>
.attraction-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.attraction-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.attraction-card.selected {
    border-color: #65a30d;
    background-color: rgba(101, 163, 13, 0.05);
}

.card-img-top {
    border-radius: 8px;
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
let currentAttractionId = null;

function selectAll() {
    document.querySelectorAll('.select-attraction-btn').forEach(button => {
        if (!button.disabled) {
            button.click();
        }
    });
}

function deselectAll() {
    document.querySelectorAll('.remove-attraction-btn').forEach(button => {
        if (!button.disabled) {
            button.click();
        }
    });
}

function filterAttractions() {
    const searchTerm = document.getElementById('attractionSearch').value.toLowerCase();
    const attractionItems = document.querySelectorAll('.attraction-item');
    let visibleCount = 0;

    attractionItems.forEach(item => {
        const attractionName = item.getAttribute('data-attraction-name');
        const matches = attractionName.includes(searchTerm);
        
        if (matches) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    // Update count
    const countElement = document.getElementById('attractionCount');
    if (searchTerm) {
        countElement.textContent = `Showing ${visibleCount} of ${attractionItems.length} attractions`;
    } else {
        countElement.textContent = 'Showing all attractions';
    }
}

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Attraction selection functionality
    document.querySelectorAll('.select-attraction-btn').forEach(button => {
        button.addEventListener('click', function() {
            const attractionId = this.getAttribute('data-attraction-id');
            const attractionName = this.getAttribute('data-attraction-name');
            
            // Show loading state
            const btnText = this.querySelector('.btn-text');
            const btnLoader = this.querySelector('.btn-loader');
            btnText.classList.add('d-none');
            btnLoader.classList.remove('d-none');
            this.disabled = true;
            
            // Make AJAX request
            fetch('{{ route('services.attractions.select') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    attraction_id: attractionId
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
                showAlert('error', 'An error occurred while selecting the attraction.');
                // Reset button state
                btnText.classList.remove('d-none');
                btnLoader.classList.add('d-none');
                this.disabled = false;
            });
        });
    });

    // Attraction removal functionality
    document.querySelectorAll('.remove-attraction-btn').forEach(button => {
        button.addEventListener('click', function() {
            currentAttractionId = this.getAttribute('data-attraction-id');
            const attractionName = this.getAttribute('data-attraction-name');
            
            document.getElementById('removeAttractionName').textContent = attractionName;
            
            const modal = new bootstrap.Modal(document.getElementById('removeAttractionModal'));
            modal.show();
        });
    });

    // Confirm removal
    document.getElementById('confirmRemoveAttraction').addEventListener('click', function() {
        if (!currentAttractionId) return;
        
        // Make AJAX request
        fetch('{{ route('services.attractions.remove') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                attraction_id: currentAttractionId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hide modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('removeAttractionModal'));
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
            showAlert('error', 'An error occurred while removing the attraction.');
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