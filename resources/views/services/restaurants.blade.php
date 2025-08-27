@extends('layouts.layout')

@section('title', 'Select Restaurants')

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
                                <h5 class="card-title text-primary">Select Restaurants</h5>
                                <p class="mb-4">
                                    Choose the restaurants you want to offer to your customers. Click on individual restaurants to select them.
                                </p>
                            </div>
                        </div>
                        <div class="col-sm-5 text-center text-sm-left">
                            <div class="card-body pb-0 px-0 px-md-4">
                                <i class="ri-restaurant-2-line" style="font-size: 4rem; color: #ea580c;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selected Restaurants Section -->
        @if(isset($selectedRestaurants) && count($selectedRestaurants) > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Selected Restaurants ({{ count($selectedRestaurants) }})</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Restaurant Name</th>
                                <th>Location</th>
                                <th>Cuisine</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($selectedRestaurants as $restaurant)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($restaurant->master_image)
                                                <img src="{{ $restaurant->master_image }}" 
                                                     alt="{{ $restaurant->name }}" 
                                                     class="rounded me-2" 
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="ri-restaurant-2-line text-muted"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <strong>{{ $restaurant->name }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $restaurant->city }}, {{ $restaurant->country }}</td>
                                    <td>
                                        <span class="badge bg-label-info">
                                            {{ $restaurant->cuisine ?: 'Various' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('restaurant.edit', Crypt::encrypt($restaurant->restaurant_id)) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="ri-edit-line me-1"></i>Edit
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger remove-restaurant-btn" 
                                                    data-restaurant-id="{{ $restaurant->restaurant_id }}"
                                                    data-restaurant-name="{{ $restaurant->name }}">
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

        <!-- Available Restaurants Section -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Available Restaurants</h5>
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
                            <input type="text" class="form-control" id="restaurantSearch" placeholder="Search restaurants by name..." onkeyup="filterRestaurants()">
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="text-muted" id="restaurantCount">Showing all restaurants</span>
                    </div>
                </div>
                
                <div class="row" id="restaurantsContainer">
                    @if(isset($availableRestaurants) && count($availableRestaurants) > 0)
                        @foreach($availableRestaurants as $restaurant)
                            <div class="col-lg-3 col-md-6 mb-3 restaurant-item" data-restaurant-name="{{ strtolower($restaurant->name) }}">
                                <div class="card h-100 restaurant-card" 
                                     data-bs-toggle="tooltip" 
                                     data-bs-html="true"
                                     data-bs-placement="top"
                                     title="<div class='text-start'>
                                                <strong>{{ $restaurant->name }}</strong><br>
                                                <small>{{ $restaurant->city }}, {{ $restaurant->country }}</small><br>
                                                <small>{{ $restaurant->cuisine ?: 'Various Cuisines' }}</small>
                                             </div>">
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <h6 class="restaurant-name mb-1">{{ Str::limit($restaurant->name, 25) }}</h6>
                                        </div>
                                        
                                        @if($restaurant->master_image)
                                            <img src="{{ $restaurant->master_image }}" 
                                                 class="card-img-top mb-2" 
                                                 alt="{{ $restaurant->name }}"
                                                 style="height: 120px; object-fit: cover; border-radius: 6px;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center mb-2" 
                                                 style="height: 120px; border-radius: 6px;">
                                                <i class="ri-restaurant-2-line text-muted" style="font-size: 2rem;"></i>
                                            </div>
                                        @endif
                                        
                                        <div class="restaurant-info">
                                            <p class="text-muted mb-1 small">
                                                <i class="ri-map-pin-line me-1"></i>
                                                {{ Str::limit($restaurant->city, 15) }}
                                            </p>
                                            
                                            @if($restaurant->cuisine)
                                                <p class="small text-muted mb-2">
                                                    <i class="ri-restaurant-line me-1"></i>
                                                    {{ Str::limit($restaurant->cuisine, 20) }}
                                                </p>
                                            @endif
                                            
                                            <div class="meal-types mb-2">
                                                @if($restaurant->breakfast_available)
                                                    <span class="badge bg-label-warning me-1">Breakfast</span>
                                                @endif
                                                @if($restaurant->lunch_available)
                                                    <span class="badge bg-label-info me-1">Lunch</span>
                                                @endif
                                                @if($restaurant->dinner_available)
                                                    <span class="badge bg-label-dark me-1">Dinner</span>
                                                @endif
                                            </div>
                                            
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-label-primary small">
                                                    {{ $restaurant->owned_by == 'hotel' ? 'Hotel Restaurant' : 'Independent' }}
                                                </span>
                                            </div>
                                            
                                            <button type="button" 
                                                    class="btn btn-warning btn-sm w-100 select-restaurant-btn" 
                                                    data-restaurant-id="{{ Crypt::encrypt($restaurant->restaurant_id) }}"
                                                    data-restaurant-name="{{ $restaurant->name }}">
                                                <span class="btn-text">
                                                    <i class="ri-add-line me-1"></i>Select Restaurant
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
                                <i class="ri-restaurant-2-line" style="font-size: 4rem; color: #ccc;"></i>
                                <h5 class="mt-3 text-muted">No Restaurants Available</h5>
                                <p class="text-muted">There are no restaurants available for selection at this time.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- / Content -->
</div>

<!-- Remove Restaurant Modal -->
<div class="modal fade" id="removeRestaurantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Remove Restaurant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove <strong id="removeRestaurantName"></strong> from your selection?</p>
                <p class="text-muted small">This action will remove the restaurant from your available offerings.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveRestaurant">Remove Restaurant</button>
            </div>
        </div>
    </div>
</div>

<style>
.restaurant-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.restaurant-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.restaurant-card.selected {
    border-color: #ea580c;
    background-color: rgba(234, 88, 12, 0.05);
}

.card-img-top {
    border-radius: 8px;
}

.meal-types {
    min-height: 24px;
}

.btn-warning {
    background-color: #ea580c;
    border-color: #ea580c;
}

.btn-warning:hover {
    background-color: #d97706;
    border-color: #d97706;
}
</style>

<script>
let currentRestaurantId = null;

function selectAll() {
    document.querySelectorAll('.select-restaurant-btn').forEach(button => {
        if (!button.disabled) {
            button.click();
        }
    });
}

function deselectAll() {
    document.querySelectorAll('.remove-restaurant-btn').forEach(button => {
        if (!button.disabled) {
            button.click();
        }
    });
}

function filterRestaurants() {
    const searchTerm = document.getElementById('restaurantSearch').value.toLowerCase();
    const restaurantItems = document.querySelectorAll('.restaurant-item');
    let visibleCount = 0;

    restaurantItems.forEach(item => {
        const restaurantName = item.getAttribute('data-restaurant-name');
        const matches = restaurantName.includes(searchTerm);
        
        if (matches) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    // Update count
    const countElement = document.getElementById('restaurantCount');
    if (searchTerm) {
        countElement.textContent = `Showing ${visibleCount} of ${restaurantItems.length} restaurants`;
    } else {
        countElement.textContent = 'Showing all restaurants';
    }
}

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Restaurant selection functionality
    document.querySelectorAll('.select-restaurant-btn').forEach(button => {
        button.addEventListener('click', function() {
            const restaurantId = this.getAttribute('data-restaurant-id');
            const restaurantName = this.getAttribute('data-restaurant-name');
            
            // Show loading state
            const btnText = this.querySelector('.btn-text');
            const btnLoader = this.querySelector('.btn-loader');
            btnText.classList.add('d-none');
            btnLoader.classList.remove('d-none');
            this.disabled = true;
            
            // Make AJAX request
            fetch('{{ route('services.restaurants.select') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    restaurant_id: restaurantId
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
                showAlert('error', 'An error occurred while selecting the restaurant.');
                // Reset button state
                btnText.classList.remove('d-none');
                btnLoader.classList.add('d-none');
                this.disabled = false;
            });
        });
    });

    // Restaurant removal functionality
    document.querySelectorAll('.remove-restaurant-btn').forEach(button => {
        button.addEventListener('click', function() {
            currentRestaurantId = this.getAttribute('data-restaurant-id');
            const restaurantName = this.getAttribute('data-restaurant-name');
            
            document.getElementById('removeRestaurantName').textContent = restaurantName;
            
            const modal = new bootstrap.Modal(document.getElementById('removeRestaurantModal'));
            modal.show();
        });
    });

    // Confirm removal
    document.getElementById('confirmRemoveRestaurant').addEventListener('click', function() {
        if (!currentRestaurantId) return;
        
        // Make AJAX request
        fetch('{{ route('services.restaurants.remove') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                restaurant_id: currentRestaurantId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hide modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('removeRestaurantModal'));
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
            showAlert('error', 'An error occurred while removing the restaurant.');
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