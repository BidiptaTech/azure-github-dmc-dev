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
        <div class="card mb-4" id="selectedRestaurantsSection">
            <div class="card-header">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-2">
                    <h5 class="mb-0">Selected Restaurants ({{ count($selectedRestaurants) }})</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="input-group input-group-sm" style="width: 260px;">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" class="form-control" id="selectedRestaurantSearch" placeholder="Search selected restaurants...">
                        </div>
                        <div class="input-group input-group-sm" style="width: 160px;">
                            <span class="input-group-text">Per page</span>
                            <select id="selectedRestaurantPageSize" class="form-select">
                                <option value="5" selected>5</option>
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="20">20</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-2">
                        <thead>
                            <tr>
                                <th>Restaurant Name</th>
                                <th>Location</th>
                                <th>Cuisine</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="selectedRestaurantsBody">
                            @foreach($selectedRestaurants as $restaurant)
                                <tr class="selected-restaurant-row" data-restaurant-id="{{ $restaurant->restaurant_id }}" data-name="{{ strtolower($restaurant->name) }}" data-location="{{ strtolower($restaurant->city) }}, {{ strtolower($restaurant->country) }}">
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
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="selectedRestaurantsPagination"></ul>
                    </nav>
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

                <!-- Advanced Filters -->
                <div class="row g-3 align-items-end mb-4">
                    <div class="col-lg-4 col-md-6">
                        <label for="restaurantSearch" class="form-label mb-1">Search</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" class="form-control" id="restaurantSearch" placeholder="Search restaurants by name..." onkeyup="applyRestaurantFilters()">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label for="restaurantCountrySelect" class="form-label mb-1">Country</label>
                        <select id="restaurantCountrySelect" class="form-select" onchange="onRestaurantCountryChange()">
                            <option value="">All Countries</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label for="restaurantCitySelect" class="form-label mb-1">City</label>
                        <select id="restaurantCitySelect" class="form-select" onchange="applyRestaurantFilters()" disabled>
                            <option value="">All Cities</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 text-end">
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="resetRestaurantFilters()"><i class="ri-filter-off-line me-1"></i>Clear Filters</button>
                        <div class="small text-muted mt-2" id="restaurantCount">Showing all restaurants</div>
                    </div>
                </div>
                
                <div class="row" id="restaurantsContainer">
                    @if(isset($availableRestaurants) && count($availableRestaurants) > 0)
                        @foreach($availableRestaurants as $restaurant)
                            <div class="col-lg-3 col-md-6 mb-3 restaurant-item" data-restaurant-name="{{ strtolower($restaurant->name) }}" data-country="{{ strtolower($restaurant->country) }}" data-city="{{ strtolower($restaurant->city) }}">
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
const defaultRestaurantCountry = '{{ strtolower(auth()->user()->country ?? '') }}';

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

function applyRestaurantFilters() {
    const searchTerm = (document.getElementById('restaurantSearch').value || '').toLowerCase();
    const selectedCountry = (document.getElementById('restaurantCountrySelect').value || '').toLowerCase();
    const selectedCity = (document.getElementById('restaurantCitySelect').value || '').toLowerCase();
    const items = document.querySelectorAll('.restaurant-item');
    let visibleCount = 0;

    items.forEach(item => {
        const name = item.getAttribute('data-restaurant-name') || '';
        const country = item.getAttribute('data-country') || '';
        const city = item.getAttribute('data-city') || '';

        const matchSearch = !searchTerm || name.includes(searchTerm);
        const matchCountry = !selectedCountry || country === selectedCountry;
        const matchCity = !selectedCity || city === selectedCity;

        if (matchSearch && matchCountry && matchCity) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    const countElement = document.getElementById('restaurantCount');
    const total = document.querySelectorAll('.restaurant-item').length;
    countElement.textContent = `Showing ${visibleCount} of ${total} restaurants`;
}

function resetRestaurantFilters() {
    document.getElementById('restaurantSearch').value = '';
    document.getElementById('restaurantCountrySelect').value = '';
    const citySelect = document.getElementById('restaurantCitySelect');
    citySelect.innerHTML = '<option value="">All Cities</option>';
    citySelect.disabled = true;
    applyRestaurantFilters();
}

function onRestaurantCountryChange() {
    const country = (document.getElementById('restaurantCountrySelect').value || '').toLowerCase();
    const citySelect = document.getElementById('restaurantCitySelect');
    citySelect.innerHTML = '<option value="">All Cities</option>';

    if (!country) {
        citySelect.disabled = true;
        applyRestaurantFilters();
        return;
    }

    const items = document.querySelectorAll('.restaurant-item');
    const cities = new Set();
    items.forEach(item => {
        if ((item.getAttribute('data-country') || '') === country) {
            const c = item.getAttribute('data-city') || '';
            if (c) cities.add(c);
        }
    });

    Array.from(cities).sort().forEach(city => {
        const opt = document.createElement('option');
        opt.value = city;
        opt.textContent = city.replace(/\b\w/g, ch => ch.toUpperCase());
        citySelect.appendChild(opt);
    });
    citySelect.disabled = cities.size === 0;

    applyRestaurantFilters();
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
            try { localStorage.setItem('last_selected_restaurant_id', String(restaurantId)); } catch (e) {}
            
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
    // Populate country dropdown
    const countrySelect = document.getElementById('restaurantCountrySelect');
    const items = document.querySelectorAll('.restaurant-item');
    const allowedCountriesFromServer = @json($allowedCountries ?? []);
    const normalizedAllowed = Array.isArray(allowedCountriesFromServer)
        ? allowedCountriesFromServer
            .map(c => String(c || '').trim())
            .filter(Boolean)
            .map(c => c.toLowerCase())
        : [];

    const countrySet = new Set();
    if (normalizedAllowed.length > 0) {
        normalizedAllowed.forEach(c => countrySet.add(c));
    } else {
        items.forEach(item => {
            const c = item.getAttribute('data-country');
            if (c) countrySet.add(c);
        });
    }
    Array.from(countrySet).sort().forEach(country => {
        const opt = document.createElement('option');
        opt.value = country;
        opt.textContent = country.replace(/\b\w/g, ch => ch.toUpperCase());
        countrySelect.appendChild(opt);
    });
    // Only auto-filter to user's country when there's a single option.
    // Otherwise show all Master DMC countries by default.
    if (defaultRestaurantCountry && countrySet.size === 1 && Array.from(countrySet).includes(defaultRestaurantCountry)) {
        countrySelect.value = defaultRestaurantCountry;
        onRestaurantCountryChange();
    } else {
        applyRestaurantFilters();
    }

    // Selected Restaurants: client-side pagination + search
    const selectedBody = document.getElementById('selectedRestaurantsBody');
    if (selectedBody) {
        const rows = Array.from(selectedBody.querySelectorAll('.selected-restaurant-row'));
        const pagination = document.getElementById('selectedRestaurantsPagination');
        const searchInput = document.getElementById('selectedRestaurantSearch');
        const pageSizeSelect = document.getElementById('selectedRestaurantPageSize');

        function getPageSize() { return parseInt(pageSizeSelect.value, 10) || 10; }
        function getFilteredRows() {
            const term = (searchInput.value || '').toLowerCase();
            return rows.filter(r => {
                if (!term) return true;
                const name = r.getAttribute('data-name') || '';
                const loc = r.getAttribute('data-location') || '';
                return name.includes(term) || loc.includes(term);
            });
        }
        function renderPagination(total, page, pageSize) {
            const totalPages = Math.max(1, Math.ceil(total / pageSize));
            pagination.innerHTML = '';
            const createItem = (label, p, disabled=false, active=false) => {
                const li = document.createElement('li');
                li.className = `page-item${disabled ? ' disabled' : ''}${active ? ' active' : ''}`;
                const a = document.createElement('a');
                a.className = 'page-link';
                a.href = 'javascript:void(0)';
                a.textContent = label;
                a.addEventListener('click', () => { if (!disabled) render(p); });
                li.appendChild(a);
                return li;
            };
            pagination.appendChild(createItem('«', Math.max(1, page-1), page===1));
            for (let i = 1; i <= Math.max(1, Math.ceil(total / pageSize)); i++) pagination.appendChild(createItem(String(i), i, false, i===page));
            pagination.appendChild(createItem('»', Math.min(Math.max(1, Math.ceil(total / pageSize)), page+1), page===Math.max(1, Math.ceil(total / pageSize))));
        }
        function render(page=1) {
            const pageSize = getPageSize();
            const filtered = getFilteredRows();
            const total = filtered.length;
            const totalPages = Math.max(1, Math.ceil(total / pageSize));
            const safePage = Math.min(Math.max(1, page), totalPages);
            const start = (safePage - 1) * pageSize;
            const end = start + pageSize;
            rows.forEach(r => r.classList.add('d-none'));
            filtered.slice(start, end).forEach(r => r.classList.remove('d-none'));
            renderPagination(total, safePage, pageSize);
        }
        try {
            const lastId = localStorage.getItem('last_selected_restaurant_id');
            if (lastId) {
                const row = rows.find(r => String(r.getAttribute('data-restaurant-id')) === String(lastId));
                if (row && row.parentElement) row.parentElement.insertBefore(row, row.parentElement.firstChild);
                localStorage.removeItem('last_selected_restaurant_id');
            }
        } catch (e) {}
        render(1);
        searchInput.addEventListener('input', () => render(1));
        pageSizeSelect.addEventListener('change', () => render(1));
    }
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