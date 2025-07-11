@extends('layouts.layout')

@section('title', 'Select Hotels')

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
                                <h5 class="card-title text-primary">Select Hotels</h5>
                                <p class="mb-4">
                                    Choose the hotels you want to offer to your customers. Click on individual hotels to select them.
                                </p>
                            </div>
                        </div>
                        <div class="col-sm-5 text-center text-sm-left">
                            <div class="card-body pb-0 px-0 px-md-4">
                                <i class="ri-hotel-line" style="font-size: 4rem; color: #0d9488;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selected Hotels Section -->
        @if(isset($selectedHotels) && count($selectedHotels) > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Selected Hotels ({{ count($selectedHotels) }})</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Hotel Name</th>
                                <th>Location</th>
                                <th>Category</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($selectedHotels as $hotel)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($hotel->main_image)
                                                <img src="{{ $hotel->main_image }}" 
                                                     alt="{{ $hotel->name }}" 
                                                     class="rounded me-2" 
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="ri-hotel-line text-muted"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <strong>{{ $hotel->name }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $hotel->city }}, {{ $hotel->country }}</td>
                                    <td>
                                        <span class="badge bg-label-primary">
                                            {{ $hotel->cat_id ? 'Category: ' . $hotel->cat_id : 'Standard' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('hotels.edit', $hotel->hotel_unique_id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="ri-edit-line me-1"></i>Edit
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger remove-hotel-btn" 
                                                    data-hotel-id="{{ $hotel->id }}"
                                                    data-hotel-name="{{ $hotel->name }}">
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

        <!-- Available Hotels Section -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Available Hotels</h5>
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
                            <input type="text" class="form-control" id="hotelSearch" placeholder="Search hotels by name..." onkeyup="filterHotels()">
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="text-muted" id="hotelCount">Showing all hotels</span>
                    </div>
                </div>
                
                <div class="row" id="hotelsContainer">
                    @if(isset($availableHotels) && count($availableHotels) > 0)
                        @foreach($availableHotels as $hotel)
                            <div class="col-lg-3 col-md-6 mb-3 hotel-item" data-hotel-name="{{ strtolower($hotel->name) }}">
                                <div class="card h-100 hotel-card" 
                                     data-bs-toggle="tooltip" 
                                     data-bs-html="true"
                                     data-bs-placement="top"
                                     title="<div class='text-start'>
                                                <strong>{{ $hotel->name }}</strong><br>
                                                <small>{{ $hotel->city }}, {{ $hotel->country }}</small><br>
                                                <small>{{ Str::limit($hotel->address, 100) }}</small>
                                             </div>">
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <h6 class="hotel-name mb-1">{{ Str::limit($hotel->name, 25) }}</h6>
                                        </div>
                                        
                                        @if($hotel->main_image)
                                            <img src="{{ $hotel->main_image }}" 
                                                 class="card-img-top mb-2" 
                                                 alt="{{ $hotel->name }}"
                                                 style="height: 120px; object-fit: cover; border-radius: 6px;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center mb-2" 
                                                 style="height: 120px; border-radius: 6px;">
                                                <i class="ri-hotel-line text-muted" style="font-size: 2rem;"></i>
                                            </div>
                                        @endif
                                        
                                        <div class="hotel-info">
                                            <p class="text-muted mb-1 small">
                                                <i class="ri-map-pin-line me-1"></i>
                                                {{ Str::limit($hotel->city, 15) }}
                                            </p>
                                            
                                            @if($hotel->address)
                                                <p class="small text-muted mb-2">{{ Str::limit($hotel->address, 40) }}</p>
                                            @endif
                                            
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-label-primary small">
                                                    {{ $hotel->cat_id ? 'Cat: ' . $hotel->cat_id : 'Standard' }}
                                                </span>
                                            </div>
                                            
                                            <button type="button" 
                                                    class="btn btn-primary btn-sm w-100 select-hotel-btn" 
                                                    data-hotel-id="{{ $hotel->id }}"
                                                    data-hotel-name="{{ $hotel->name }}">
                                                <span class="btn-text">
                                                    <i class="ri-add-line me-1"></i>Select Hotel
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
                                <i class="ri-hotel-line" style="font-size: 4rem; color: #ccc;"></i>
                                <h5 class="mt-3 text-muted">No Hotels Available</h5>
                                <p class="text-muted">There are no hotels available for selection at this time.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- / Content -->
</div>

<!-- Remove Hotel Modal -->
<div class="modal fade" id="removeHotelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Remove Hotel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove <strong id="removeHotelName"></strong> from your selected hotels?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveBtn">
                    <span class="btn-text">
                        <i class="ri-delete-bin-line me-1"></i>Remove
                    </span>
                    <span class="btn-loader d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                        Removing...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.hotel-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.hotel-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    border-color: #0d9488;
}

.hotel-card.selected {
    border-color: #0d9488;
    background: linear-gradient(135deg, rgba(13, 148, 136, 0.1) 0%, rgba(13, 148, 136, 0.05) 100%);
    box-shadow: 0 4px 15px rgba(13, 148, 136, 0.2);
}

.hotel-card.selected::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #0d9488, #14b8a6);
}

.form-check-input:checked {
    background-color: #0d9488;
    border-color: #0d9488;
}

.card-img-top {
    border-radius: 8px;
    transition: transform 0.3s ease;
}

.hotel-card:hover .card-img-top {
    transform: scale(1.05);
}

.hotel-name {
    font-size: 0.9rem;
    line-height: 1.2;
    font-weight: 600;
}

.hotel-item.hidden {
    display: none;
}

/* Tooltip customization */
.tooltip-inner {
    max-width: 300px;
    text-align: left;
    background-color: #333;
    border-radius: 8px;
    padding: 12px;
}

.bs-tooltip-top .tooltip-arrow::before {
    border-top-color: #333;
}

/* Search highlight */
.highlight {
    background-color: #fff3cd;
    padding: 2px 4px;
    border-radius: 3px;
}

/* Button loader states */
.btn-loader {
    display: none;
}

.btn.loading .btn-text {
    display: none;
}

.btn.loading .btn-loader {
    display: inline-block;
}

/* Selected hotels table styling */
.table-hover tbody tr:hover {
    background-color: rgba(13, 148, 136, 0.05);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
        width: 100%;
    }
    
    .btn-group .btn {
        margin-bottom: 0.25rem;
    }
}
</style>

<script>
// CSRF Token
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function selectAll() {
    document.querySelectorAll('.select-hotel-btn').forEach(button => {
        if (!button.disabled) {
            button.click();
        }
    });
}

function deselectAll() {
    document.querySelectorAll('.remove-hotel-btn').forEach(button => {
        if (!button.disabled) {
            button.click();
        }
    });
}

function filterHotels() {
    const searchTerm = document.getElementById('hotelSearch').value.toLowerCase();
    const hotelItems = document.querySelectorAll('.hotel-item');
    let visibleCount = 0;

    hotelItems.forEach(item => {
        const hotelName = item.getAttribute('data-hotel-name');
        const hotelNameElement = item.querySelector('.hotel-name');
        
        if (hotelName.includes(searchTerm)) {
            item.classList.remove('hidden');
            visibleCount++;
            
            // Highlight search term in hotel name
            if (searchTerm) {
                const originalText = hotelNameElement.textContent;
                const highlightedText = originalText.replace(
                    new RegExp(searchTerm, 'gi'),
                    match => `<span class="highlight">${match}</span>`
                );
                hotelNameElement.innerHTML = highlightedText;
            } else {
                // Remove highlighting when search is empty
                hotelNameElement.innerHTML = hotelNameElement.textContent;
            }
        } else {
            item.classList.add('hidden');
            // Remove highlighting for hidden items
            hotelNameElement.innerHTML = hotelNameElement.textContent;
        }
    });

    updateHotelCount(visibleCount);
}

function updateHotelCount(visibleCount = null) {
    const countElement = document.getElementById('hotelCount');
    const totalHotels = document.querySelectorAll('.hotel-item').length;
    
    if (visibleCount !== null) {
        countElement.textContent = `Showing ${visibleCount} of ${totalHotels} hotels`;
    } else {
        countElement.textContent = `Showing ${totalHotels} hotels`;
    }
}

function selectHotel(hotelId, hotelName) {
    fetch('{{ route("services.hotels.select") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            hotel_id: hotelId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert('success', `${hotelName} has been selected successfully!`);
            
            // Reload page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showAlert('error', data.message || 'An error occurred while selecting the hotel.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred while selecting the hotel.');
    });
}

function removeHotel(hotelId, hotelName) {
    fetch('{{ route("services.hotels.remove") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            hotel_id: hotelId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert('success', `${hotelName} has been removed successfully!`);
            
            // Reload page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showAlert('error', data.message || 'An error occurred while removing the hotel.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred while removing the hotel.');
    });
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert alert at the top of the card body
    const cardBody = document.querySelector('.card-body');
    cardBody.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = cardBody.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Add event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            html: true,
            delay: { show: 300, hide: 100 }
        });
    });

    // Handle hotel selection
    document.querySelectorAll('.select-hotel-btn').forEach(button => {
        button.addEventListener('click', function() {
            const hotelId = this.getAttribute('data-hotel-id');
            const hotelName = this.getAttribute('data-hotel-name');
            
            // Show loading state
            this.classList.add('loading');
            this.disabled = true;
            
            // Call select hotel function
            selectHotel(hotelId, hotelName);
        });
    });

    // Handle hotel removal
    document.querySelectorAll('.remove-hotel-btn').forEach(button => {
        button.addEventListener('click', function() {
            const hotelId = this.getAttribute('data-hotel-id');
            const hotelName = this.getAttribute('data-hotel-name');
            
            // Set modal content
            document.getElementById('removeHotelName').textContent = hotelName;
            document.getElementById('confirmRemoveBtn').setAttribute('data-hotel-id', hotelId);
            document.getElementById('confirmRemoveBtn').setAttribute('data-hotel-name', hotelName);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('removeHotelModal'));
            modal.show();
        });
    });

    // Handle confirm remove
    document.getElementById('confirmRemoveBtn').addEventListener('click', function() {
        const hotelId = this.getAttribute('data-hotel-id');
        const hotelName = this.getAttribute('data-hotel-name');
        
        // Show loading state
        this.classList.add('loading');
        this.disabled = true;
        
        // Call remove hotel function
        removeHotel(hotelId, hotelName);
        
        // Hide modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('removeHotelModal'));
        modal.hide();
    });

    // Initialize hotel count
    updateHotelCount();
});
</script>
@endsection 