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
        <div class="card mb-4 {{ (!isset($selectedHotels) || count($selectedHotels) === 0) ? 'd-none' : '' }}" id="selectedHotelsSection">
            <div class="card-header">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-2">
                    <h5 class="mb-0" id="selectedHotelsTitle">Selected Hotels ({{ isset($selectedHotels) ? count($selectedHotels) : 0 }})</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="input-group input-group-sm" style="width: 260px;">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" class="form-control" id="selectedSearch" placeholder="Search selected hotels...">
                        </div>
                        <div class="input-group input-group-sm" style="width: 160px;">
                            <span class="input-group-text">Per page</span>
                            <select id="selectedPageSize" class="form-select">
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
                                <th>Hotel Name</th>
                                <th>Location</th>
                                <th>Category</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="selectedHotelsBody">
                            @foreach(($selectedHotels ?? []) as $hotel)
                                <tr class="selected-hotel-row" data-hotel-id="{{ $hotel->id }}" data-name="{{ strtolower($hotel->name) }}" data-location="{{ strtolower($hotel->city) }}, {{ strtolower($hotel->country) }}">
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
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="selectedHotelsPagination"></ul>
                    </nav>
                </div>
            </div>
        </div>

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

                <!-- Advanced Filters -->
                <div class="row g-3 align-items-end mb-4">
                    <div class="col-lg-4 col-md-6">
                        <label for="hotelSearch" class="form-label mb-1">Search</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" class="form-control" id="hotelSearch" placeholder="Search hotels by name..." onkeyup="applyFilters()">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label for="countrySelect" class="form-label mb-1">Country</label>
                        <select id="countrySelect" class="form-select" onchange="onCountryChange()">
                            <option value="">All Countries</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label for="citySelect" class="form-label mb-1">City</label>
                        <select id="citySelect" class="form-select" onchange="applyFilters()" disabled>
                            <option value="">All Cities</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 text-end">
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="resetFilters()"><i class="ri-filter-off-line me-1"></i>Clear Filters</button>
                        <div class="small text-muted mt-2" id="hotelCount">Showing all hotels</div>
                    </div>
                </div>
                
                <div class="row" id="hotelsContainer">
                    @if(isset($availableHotels) && count($availableHotels) > 0)
                        @foreach($availableHotels as $hotel)
                            <div class="col-lg-3 col-md-6 mb-3 hotel-item"
                                 data-hotel-id="{{ $hotel->id }}"
                                 data-hotel-unique-id="{{ $hotel->hotel_unique_id }}"
                                 data-display-name="{{ $hotel->name }}"
                                 data-main-image="{{ $hotel->main_image }}"
                                 data-display-city="{{ $hotel->city }}"
                                 data-display-country="{{ $hotel->country }}"
                                 data-cat-id="{{ $hotel->cat_id }}"
                                 data-edit-url="{{ route('hotels.edit', $hotel->hotel_unique_id) }}"
                                 data-hotel-name="{{ strtolower($hotel->name) }}"
                                 data-country="{{ strtolower($hotel->country) }}"
                                 data-city="{{ strtolower($hotel->city) }}">
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
const defaultCountry = '{{ strtolower(auth()->user()->country ?? '') }}';
let selectedHotelsPaginator = null;

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function resetButton(btn) {
    if (!btn) return;
    btn.classList.remove('loading');
    btn.disabled = false;
}

function updateSelectedCount() {
    const count = document.querySelectorAll('#selectedHotelsBody .selected-hotel-row').length;
    const title = document.getElementById('selectedHotelsTitle');
    const section = document.getElementById('selectedHotelsSection');

    if (title) {
        title.textContent = `Selected Hotels (${count})`;
    }
    if (section) {
        section.classList.toggle('d-none', count === 0);
    }
}

function buildSelectedRowFromItem(hotelItem) {
    const hotelId = hotelItem.getAttribute('data-hotel-id');
    const name = hotelItem.getAttribute('data-display-name') || '';
    const city = hotelItem.getAttribute('data-display-city') || '';
    const country = hotelItem.getAttribute('data-display-country') || '';
    const mainImage = hotelItem.getAttribute('data-main-image') || '';
    const catId = hotelItem.getAttribute('data-cat-id') || '';
    const editUrl = hotelItem.getAttribute('data-edit-url') || '#';
    const categoryLabel = catId ? `Category: ${escapeHtml(catId)}` : 'Standard';
    const imageHtml = mainImage
        ? `<img src="${escapeHtml(mainImage)}" alt="${escapeHtml(name)}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">`
        : `<div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="ri-hotel-line text-muted"></i></div>`;

    const row = document.createElement('tr');
    row.className = 'selected-hotel-row';
    row.setAttribute('data-hotel-id', hotelId);
    row.setAttribute('data-name', name.toLowerCase());
    row.setAttribute('data-location', `${city}, ${country}`.toLowerCase());
    row.innerHTML = `
        <td>
            <div class="d-flex align-items-center">
                ${imageHtml}
                <div><strong>${escapeHtml(name)}</strong></div>
            </div>
        </td>
        <td>${escapeHtml(city)}, ${escapeHtml(country)}</td>
        <td><span class="badge bg-label-primary">${categoryLabel}</span></td>
        <td>
            <div class="btn-group" role="group">
                <a href="${escapeHtml(editUrl)}" class="btn btn-sm btn-outline-primary">
                    <i class="ri-edit-line me-1"></i>Edit
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger remove-hotel-btn"
                        data-hotel-id="${escapeHtml(hotelId)}"
                        data-hotel-name="${escapeHtml(name)}">
                    <i class="ri-delete-bin-line me-1"></i>Remove
                </button>
            </div>
        </td>
    `;
    return row;
}

function addHotelToSelectedTable(hotelItem) {
    const hotelId = hotelItem.getAttribute('data-hotel-id');
    if (document.querySelector(`#selectedHotelsBody .selected-hotel-row[data-hotel-id="${hotelId}"]`)) {
        return;
    }

    const tbody = document.getElementById('selectedHotelsBody');
    if (!tbody) return;

    tbody.insertBefore(buildSelectedRowFromItem(hotelItem), tbody.firstChild);
    hotelItem.classList.add('hotel-selected');
}

function removeHotelFromSelectedTable(hotelId) {
    const row = document.querySelector(`#selectedHotelsBody .selected-hotel-row[data-hotel-id="${hotelId}"]`);
    if (row) {
        row.remove();
    }
}

function refreshSelectedPagination() {
    if (selectedHotelsPaginator) {
        selectedHotelsPaginator.refresh(1);
    }
}

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

function applyFilters() {
    const searchTerm = document.getElementById('hotelSearch').value.toLowerCase();
    const selectedCountry = (document.getElementById('countrySelect').value || '').toLowerCase();
    const selectedCity = (document.getElementById('citySelect').value || '').toLowerCase();
    const hotelItems = document.querySelectorAll('.hotel-item');
    let visibleCount = 0;

    hotelItems.forEach(item => {
        if (item.classList.contains('hotel-selected')) {
            item.classList.add('hidden');
            return;
        }

        const hotelName = item.getAttribute('data-hotel-name');
        const hotelCountry = item.getAttribute('data-country') || '';
        const hotelCity = item.getAttribute('data-city') || '';
        const hotelNameElement = item.querySelector('.hotel-name');

        const matchSearch = !searchTerm || hotelName.includes(searchTerm);
        const matchCountry = !selectedCountry || hotelCountry === selectedCountry;
        const matchCity = !selectedCity || hotelCity === selectedCity;

        if (matchSearch && matchCountry && matchCity) {
            item.classList.remove('hidden');
            visibleCount++;

            if (searchTerm) {
                const originalText = hotelNameElement.textContent;
                const highlightedText = originalText.replace(
                    new RegExp(searchTerm, 'gi'),
                    match => `<span class="highlight">${match}</span>`
                );
                hotelNameElement.innerHTML = highlightedText;
            } else {
                hotelNameElement.innerHTML = hotelNameElement.textContent;
            }
        } else {
            item.classList.add('hidden');
            hotelNameElement.innerHTML = hotelNameElement.textContent;
        }
    });

    updateHotelCount(visibleCount);
}

function resetFilters() {
    document.getElementById('hotelSearch').value = '';
    document.getElementById('countrySelect').value = '';
    const citySelect = document.getElementById('citySelect');
    citySelect.innerHTML = '<option value="">All Cities</option>';
    citySelect.disabled = true;
    applyFilters();
}

function onCountryChange() {
    const country = (document.getElementById('countrySelect').value || '').toLowerCase();
    const citySelect = document.getElementById('citySelect');
    citySelect.innerHTML = '<option value="">All Cities</option>';

    if (!country) {
        citySelect.disabled = true;
        applyFilters();
        return;
    }

    const hotelItems = document.querySelectorAll('.hotel-item');
    const cities = new Set();
    hotelItems.forEach(item => {
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

    applyFilters();
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

function selectHotel(hotelId, hotelName, buttonEl) {
    fetch('{{ route("services.hotels.select") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            hotel_id: hotelId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const hotelItem = document.querySelector(`.hotel-item[data-hotel-id="${hotelId}"]`);
            if (hotelItem) {
                addHotelToSelectedTable(hotelItem);
                hotelItem.classList.add('hidden');
            }

            updateSelectedCount();
            refreshSelectedPagination();
            applyFilters();
            showAlert('success', `${hotelName} has been selected successfully!`);
        } else {
            resetButton(buttonEl);
            showAlert('error', data.message || 'An error occurred while selecting the hotel.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        resetButton(buttonEl);
        showAlert('error', 'An error occurred while selecting the hotel.');
    });
}

function removeHotel(hotelId, hotelName, buttonEl) {
    fetch('{{ route("services.hotels.remove") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            hotel_id: hotelId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            removeHotelFromSelectedTable(hotelId);

            const hotelItem = document.querySelector(`.hotel-item[data-hotel-id="${hotelId}"]`);
            if (hotelItem) {
                hotelItem.classList.remove('hotel-selected');
                resetButton(hotelItem.querySelector('.select-hotel-btn'));
            }

            updateSelectedCount();
            refreshSelectedPagination();
            applyFilters();
            showAlert('success', `${hotelName} has been removed successfully!`);
        } else {
            showAlert('error', data.message || 'An error occurred while removing the hotel.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred while removing the hotel.');
    })
    .finally(() => {
        resetButton(buttonEl);
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

    const alertContainer = document.getElementById('availableHotelsAlerts');
    if (!alertContainer) return;

    alertContainer.insertAdjacentHTML('beforeend', alertHtml);

    setTimeout(() => {
        const alert = alertContainer.querySelector('.alert');
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
            if (this.disabled) return;

            const hotelId = this.getAttribute('data-hotel-id');
            const hotelName = this.getAttribute('data-hotel-name');

            this.classList.add('loading');
            this.disabled = true;

            selectHotel(hotelId, hotelName, this);
        });
    });

    // Handle hotel removal (including dynamically added rows)
    document.addEventListener('click', function(event) {
        const removeBtn = event.target.closest('.remove-hotel-btn');
        if (!removeBtn || !document.getElementById('selectedHotelsBody')?.contains(removeBtn)) {
            return;
        }

        const hotelId = removeBtn.getAttribute('data-hotel-id');
        const hotelName = removeBtn.getAttribute('data-hotel-name');

        document.getElementById('removeHotelName').textContent = hotelName;
        document.getElementById('confirmRemoveBtn').setAttribute('data-hotel-id', hotelId);
        document.getElementById('confirmRemoveBtn').setAttribute('data-hotel-name', hotelName);

        const modal = new bootstrap.Modal(document.getElementById('removeHotelModal'));
        modal.show();
    });

    // Handle confirm remove
    document.getElementById('confirmRemoveBtn').addEventListener('click', function() {
        const hotelId = this.getAttribute('data-hotel-id');
        const hotelName = this.getAttribute('data-hotel-name');
        const confirmBtn = this;

        confirmBtn.classList.add('loading');
        confirmBtn.disabled = true;

        removeHotel(hotelId, hotelName, confirmBtn);

        const modal = bootstrap.Modal.getInstance(document.getElementById('removeHotelModal'));
        if (modal) {
            modal.hide();
        }
    });

    // Prime Country dropdown from server-allowed list (Master DMC countries / Created DMC country)
    // Fallback: if not provided, derive from DOM hotel cards (unique countries).
    const countrySelect = document.getElementById('countrySelect');
    const hotelItems = document.querySelectorAll('.hotel-item');

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
        hotelItems.forEach(item => {
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
    if (defaultCountry && countrySet.size === 1 && Array.from(countrySet).includes(defaultCountry)) {
        countrySelect.value = defaultCountry;
        onCountryChange();
    } else {
        applyFilters();
    }

    // Initialize hotel count and apply initial filters
    updateHotelCount();
    applyFilters();

    // Selected Hotels: client-side pagination + search
    const selectedBody = document.getElementById('selectedHotelsBody');
    if (selectedBody) {
        const pagination = document.getElementById('selectedHotelsPagination');
        const searchInput = document.getElementById('selectedSearch');
        const pageSizeSelect = document.getElementById('selectedPageSize');

        function getPageSize() { return parseInt(pageSizeSelect.value, 10) || 10; }

        function getAllRows() {
            return Array.from(selectedBody.querySelectorAll('.selected-hotel-row'));
        }

        function getFilteredRows() {
            const term = (searchInput.value || '').toLowerCase();
            return getAllRows().filter(r => {
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
            for (let i = 1; i <= totalPages; i++) {
                pagination.appendChild(createItem(String(i), i, false, i===page));
            }
            pagination.appendChild(createItem('»', Math.min(totalPages, page+1), page===totalPages));
        }

        function render(page=1) {
            const pageSize = getPageSize();
            const filtered = getFilteredRows();
            const total = filtered.length;
            const totalPages = Math.max(1, Math.ceil(total / pageSize));
            const safePage = Math.min(Math.max(1, page), totalPages);
            const start = (safePage - 1) * pageSize;
            const end = start + pageSize;

            getAllRows().forEach(r => r.classList.add('d-none'));
            filtered.slice(start, end).forEach(r => r.classList.remove('d-none'));

            renderPagination(total, safePage, pageSize);
        }

        selectedHotelsPaginator = {
            refresh(page = 1) {
                render(page);
            }
        };

        render(1);

        searchInput.addEventListener('input', () => render(1));
        pageSizeSelect.addEventListener('change', () => render(1));
    }
});
</script>
@endsection 