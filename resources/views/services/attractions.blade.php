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

        <div id="serviceActionAlerts" class="mb-3"></div>

        <!-- Selected Attractions Section -->
        <div class="card mb-4 {{ (!isset($selectedAttractions) || count($selectedAttractions) === 0) ? 'd-none' : '' }}" id="selectedAttractionsSection">
            <div class="card-header">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-2">
                    <div>
                        <h5 class="mb-0" id="selectedAttractionsTitle">Selected Attractions ({{ isset($selectedAttractions) ? count($selectedAttractions) : 0 }})</h5>
                        <small class="text-muted">Attractions selected by this DMC only</small>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="input-group input-group-sm" style="width: 260px;">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" class="form-control" id="selectedAttractionSearch" placeholder="Search selected attractions...">
                        </div>
                        <div class="input-group input-group-sm" style="width: 160px;">
                            <span class="input-group-text">Per page</span>
                            <select id="selectedAttractionPageSize" class="form-select">
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
                <div id="selectedAttractionsBulkToolbar" class="selected-services-bulk-toolbar d-none mb-3" role="region" aria-live="polite">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <i class="ri-checkbox-circle-fill text-primary" aria-hidden="true"></i>
                            <strong id="bulkSelectedCountLabel">0 Attractions Selected</strong>
                            <button type="button" class="btn btn-link btn-sm px-1" id="clearAttractionSelectionBtn">Clear Selection</button>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm" id="bulkRemoveSelectedBtn">
                            <i class="ri-delete-bin-line me-1"></i><span id="bulkRemoveSelectedLabel">Remove Selected</span>
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-2 align-middle">
                        <thead>
                            <tr>
                                <th style="width: 130px;">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="selectAllSelectedAttractions" aria-label="Select all attractions">
                                        <label class="form-check-label fw-semibold" for="selectAllSelectedAttractions">Select All</label>
                                    </div>
                                </th>
                                <th>Attraction Name</th>
                                <th>Location</th>
                                {{-- <th>Type</th> --}}
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="selectedAttractionsBody">
                            @foreach(($selectedAttractions ?? []) as $attraction)
                                <tr class="selected-attraction-row" data-attraction-id="{{ $attraction->attraction_id }}" data-name="{{ strtolower($attraction->name) }}" data-location="{{ strtolower($attraction->location ?? $attraction->city) }}, {{ strtolower($attraction->country) }}">
                                    <td>
                                        <div class="form-check mb-0">
                                            <input class="form-check-input selected-attraction-checkbox" type="checkbox"
                                                   value="{{ $attraction->attraction_id }}"
                                                   aria-label="Select {{ $attraction->name }}">
                                        </div>
                                    </td>
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
                                    {{-- <td>
                                        <span class="badge bg-label-success">
                                            {{ $attraction->adult_price ? 'Paid' : 'Free' }}
                                        </span>
                                    </td> --}}
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('attraction.edit', Crypt::encrypt($attraction->attraction_id)) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="ri-edit-line me-1"></i>Edit
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger remove-attraction-btn" 
                                                    data-attraction-id="{{ $attraction->attraction_id }}"
                                                    data-attraction-name="{{ $attraction->name }}">
                                                <i class="ri-delete-bin-line me-1"></i>Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="selectedAttractionsPagination"></ul>
                    </nav>
                    <div class="small text-muted mt-2" id="selectedAttractionsShowingCount"></div>
                </div>
            </div>
        </div>

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
                <div id="availableAttractionsAlerts"></div>
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Advanced Filters -->
                <div class="row g-3 align-items-end mb-4">
                    <div class="col-lg-5 col-md-6">
                        <label for="attractionSearch" class="form-label mb-1">Search</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" class="form-control" id="attractionSearch" placeholder="Search attractions by name..." onkeyup="applyAttractionFilters()">
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <label for="attractionCitySelect" class="form-label mb-1">
                            City
                            @if(!empty($dmcCountry))
                                <span class="text-muted fw-normal">({{ $dmcCountry }})</span>
                            @endif
                        </label>
                        <select id="attractionCitySelect" class="form-select city-search-select" data-placeholder="Search and select a city">
                            <option value="">All Cities</option>
                            @foreach(($allowedCities ?? []) as $cityName)
                                <option value="{{ strtolower($cityName) }}">{{ $cityName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 text-end">
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="resetAttractionFilters()"><i class="ri-filter-off-line me-1"></i>Clear Filters</button>
                        <div class="small text-muted mt-2" id="attractionCount">Showing all attractions</div>
                    </div>
                </div>
                
                <div class="row" id="attractionsContainer">
                    @if(isset($availableAttractions) && count($availableAttractions) > 0)
                        @foreach($availableAttractions as $attraction)
                            <div class="col-lg-3 col-md-6 mb-3 attraction-item"
                                 data-attraction-id="{{ $attraction->attraction_id }}"
                                 data-attraction-id-encrypted="{{ Crypt::encrypt($attraction->attraction_id) }}"
                                 data-display-name="{{ $attraction->name }}"
                                 data-master-image="{{ $attraction->master_image }}"
                                 data-display-location="{{ $attraction->location ?? $attraction->city }}"
                                 data-display-country="{{ $attraction->country }}"
                                 data-edit-url="{{ route('attraction.edit', Crypt::encrypt($attraction->attraction_id)) }}"
                                 data-attraction-name="{{ strtolower($attraction->name) }}"
                                 data-country="{{ strtolower($attraction->country) }}"
                                 data-city="{{ strtolower($attraction->location ?? $attraction->city) }}">
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
                                            
                                            {{-- <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-label-success small">
                                                    {{ $attraction->adult_price ? 'Paid' : 'Free' }}
                                                </span>
                                            </div> --}}
                                            
                                            @if($attraction->senior_min_age)
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        Senior: {{ number_format($attraction->senior_min_age) }}
                                                        @if($attraction->child_max_age)
                                                            | Child: {{ number_format($attraction->child_max_age) }}
                                                        @endif
                                                    </small>
                                                </div>
                                            @endif
                                            
                                            <button type="button" 
                                                    class="btn btn-success btn-sm w-100 select-attraction-btn" 
                                                    data-attraction-id="{{ Crypt::encrypt($attraction->attraction_id) }}"
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

<!-- Bulk Remove Attractions Modal -->
<div class="modal fade" id="bulkRemoveAttractionsModal" tabindex="-1" aria-labelledby="bulkRemoveAttractionsTitle" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="bulkRemoveModalCloseBtn"></button>
            </div>
            <div class="modal-body text-center pt-0 px-4">
                <div class="bulk-remove-confirm-icon" aria-hidden="true">
                    <i class="ri-delete-bin-line"></i>
                </div>
                <h5 class="modal-title mb-2" id="bulkRemoveAttractionsTitle">Remove Attractions?</h5>
                <p class="text-muted mb-1" id="bulkRemoveAttractionsBody"></p>
                <p class="text-muted small mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0 pb-4">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="cancelBulkRemoveAttractions">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmBulkRemoveAttractions">
                    <span class="btn-text">Remove Attractions</span>
                    <span class="btn-loader d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Removing...
                    </span>
                </button>
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

.selected-services-bulk-toolbar {
    background: #eef4ff;
    border: 1px solid #d6e4ff;
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
}

.selected-services-bulk-toolbar .btn-link {
    color: #0d6efd;
    text-decoration: underline;
    font-weight: 500;
}

.bulk-remove-confirm-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #fee2e2;
    color: #dc2626;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    margin: 0 auto 1rem;
}

.selected-attraction-checkbox,
#selectAllSelectedAttractions {
    cursor: pointer;
}

.city-combobox {
    position: relative;
}

.city-combobox .city-search-select {
    position: absolute;
    opacity: 0;
    pointer-events: none;
    width: 1px;
    height: 1px;
}

.city-combobox-input {
    padding-right: 2.25rem;
}

.city-combobox-caret {
    position: absolute;
    right: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    color: #697a8d;
    pointer-events: none;
}

.city-combobox-menu {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    z-index: 1080;
    background: #fff;
    border: 1px solid #d9dee3;
    border-radius: 0.5rem;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
    padding: 0.35rem;
}

.city-combobox-options {
    max-height: 220px;
    overflow-y: auto;
}

.city-combobox-option {
    display: block;
    width: 100%;
    text-align: left;
    border: 0;
    background: transparent;
    padding: 0.45rem 0.7rem;
    border-radius: 0.375rem;
    color: #566a7f;
}

.city-combobox-option:hover,
.city-combobox-option.active {
    background: #f1f5ff;
    color: #0d6efd;
}

.city-combobox-empty {
    padding: 0.55rem 0.7rem;
    color: #a1acb8;
    font-size: 0.875rem;
}
</style>

<script>
let currentAttractionId = null;
const csrfToken = '{{ csrf_token() }}';
const dmcCountry = '{{ strtolower(trim($dmcCountry ?? '')) }}';
let selectedAttractionsPaginator = null;
const selectedAttractionIds = new Set();
let bulkRemoveIds = [];
let bulkRemoveInProgress = false;

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function initServiceCitySearch(selector, onChange) {
    const select = document.querySelector(selector);
    if (!select || select.dataset.citySearchReady === '1') return;
    select.dataset.citySearchReady = '1';

    const options = Array.from(select.options).map(opt => ({
        value: opt.value,
        label: (opt.textContent || '').trim()
    }));
    const placeholder = select.getAttribute('data-placeholder') || 'Search and select a city';

    const wrapper = document.createElement('div');
    wrapper.className = 'city-combobox';
    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);

    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'form-control city-combobox-input';
    input.placeholder = placeholder;
    input.autocomplete = 'off';
    input.setAttribute('role', 'combobox');
    input.setAttribute('aria-expanded', 'false');
    input.setAttribute('aria-autocomplete', 'list');

    const caret = document.createElement('i');
    caret.className = 'ri-arrow-down-s-line city-combobox-caret';
    caret.setAttribute('aria-hidden', 'true');

    const menu = document.createElement('div');
    menu.className = 'city-combobox-menu d-none';
    const list = document.createElement('div');
    list.className = 'city-combobox-options';
    list.setAttribute('role', 'listbox');
    menu.appendChild(list);

    wrapper.appendChild(input);
    wrapper.appendChild(caret);
    wrapper.appendChild(menu);

    function selectedLabel() {
        const match = options.find(opt => opt.value === select.value);
        return match ? match.label : '';
    }

    function syncInput() {
        input.value = select.value ? selectedLabel() : '';
    }

    function closeMenu() {
        menu.classList.add('d-none');
        input.setAttribute('aria-expanded', 'false');
    }

    function renderList(term) {
        const query = (term || '').toLowerCase().trim();
        const filtered = options.filter(opt => {
            if (!query) return true;
            return opt.label.toLowerCase().includes(query) || opt.value.toLowerCase().includes(query);
        });

        list.innerHTML = '';
        if (!filtered.length) {
            list.innerHTML = '<div class="city-combobox-empty">No cities found</div>';
            return;
        }

        filtered.forEach(opt => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'city-combobox-option' + (opt.value === select.value ? ' active' : '');
            item.textContent = opt.label;
            item.dataset.value = opt.value;
            item.addEventListener('mousedown', function(event) {
                event.preventDefault();
                chooseCity(opt.value, opt.label);
            });
            list.appendChild(item);
        });
    }

    function openMenu() {
        menu.classList.remove('d-none');
        input.setAttribute('aria-expanded', 'true');
        renderList(select.value ? '' : input.value);
    }

    function chooseCity(value, label) {
        select.value = value;
        input.value = value ? label : '';
        closeMenu();
        if (typeof onChange === 'function') onChange();
    }

    input.addEventListener('focus', function() {
        if (select.value) {
            input.value = '';
        }
        openMenu();
    });

    input.addEventListener('input', function() {
        if (!input.value) {
            select.value = '';
            if (typeof onChange === 'function') onChange();
        }
        openMenu();
        renderList(input.value);
    });

    input.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            syncInput();
            closeMenu();
            input.blur();
        }
        if (event.key === 'Enter') {
            event.preventDefault();
            const first = list.querySelector('.city-combobox-option');
            if (first) {
                chooseCity(first.dataset.value, first.textContent);
            }
        }
    });

    input.addEventListener('blur', function() {
        setTimeout(function() {
            if (!wrapper.contains(document.activeElement)) {
                syncInput();
                closeMenu();
            }
        }, 120);
    });

    document.addEventListener('click', function(event) {
        if (!wrapper.contains(event.target)) {
            syncInput();
            closeMenu();
        }
    });

    wrapper._syncCitySearch = syncInput;
    select.addEventListener('change', onChange);
    syncInput();
}

function resetCitySearchSelect(selector) {
    const el = document.querySelector(selector);
    if (!el) return;
    el.value = '';
    const wrapper = el.closest('.city-combobox');
    if (wrapper && typeof wrapper._syncCitySearch === 'function') {
        wrapper._syncCitySearch();
    }
}

function resetAttractionButton(btn) {
    if (!btn) return;
    const btnText = btn.querySelector('.btn-text');
    const btnLoader = btn.querySelector('.btn-loader');
    if (btnText) btnText.classList.remove('d-none');
    if (btnLoader) btnLoader.classList.add('d-none');
    btn.disabled = false;
}

function setAttractionButtonLoading(btn) {
    if (!btn) return;
    const btnText = btn.querySelector('.btn-text');
    const btnLoader = btn.querySelector('.btn-loader');
    if (btnText) btnText.classList.add('d-none');
    if (btnLoader) btnLoader.classList.remove('d-none');
    btn.disabled = true;
}

function updateSelectedAttractionCount() {
    const count = document.querySelectorAll('#selectedAttractionsBody .selected-attraction-row').length;
    const title = document.getElementById('selectedAttractionsTitle');
    const section = document.getElementById('selectedAttractionsSection');

    if (title) {
        title.textContent = `Selected Attractions (${count})`;
    }
    if (section) {
        section.classList.toggle('d-none', count === 0);
    }
}

function buildSelectedAttractionRowFromItem(attractionItem) {
    const attractionId = attractionItem.getAttribute('data-attraction-id');
    const name = attractionItem.getAttribute('data-display-name') || '';
    const location = attractionItem.getAttribute('data-display-location') || '';
    const country = attractionItem.getAttribute('data-display-country') || '';
    const masterImage = attractionItem.getAttribute('data-master-image') || '';
    const editUrl = attractionItem.getAttribute('data-edit-url') || '#';
    const imageHtml = masterImage
        ? `<img src="${escapeHtml(masterImage)}" alt="${escapeHtml(name)}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">`
        : `<div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="ri-landscape-line text-muted"></i></div>`;

    const row = document.createElement('tr');
    row.className = 'selected-attraction-row';
    row.setAttribute('data-attraction-id', attractionId);
    row.setAttribute('data-name', name.toLowerCase());
    row.setAttribute('data-location', `${location}, ${country}`.toLowerCase());
    row.innerHTML = `
        <td>
            <div class="form-check mb-0">
                <input class="form-check-input selected-attraction-checkbox" type="checkbox"
                       value="${escapeHtml(attractionId)}" aria-label="Select ${escapeHtml(name)}">
            </div>
        </td>
        <td>
            <div class="d-flex align-items-center">
                ${imageHtml}
                <div><strong>${escapeHtml(name)}</strong></div>
            </div>
        </td>
        <td>${escapeHtml(location)}, ${escapeHtml(country)}</td>
        <td>
            <div class="btn-group" role="group">
                <a href="${editUrl}" class="btn btn-sm btn-outline-primary">
                    <i class="ri-edit-line me-1"></i>Edit
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger remove-attraction-btn"
                        data-attraction-id="${escapeHtml(attractionId)}"
                        data-attraction-name="${escapeHtml(name)}">
                    <i class="ri-delete-bin-line me-1"></i>Remove
                </button>
            </div>
        </td>
    `;
    return row;
}

function addAttractionToSelectedTable(attractionItem) {
    const attractionId = attractionItem.getAttribute('data-attraction-id');
    if (document.querySelector(`#selectedAttractionsBody .selected-attraction-row[data-attraction-id="${attractionId}"]`)) {
        return;
    }

    const tbody = document.getElementById('selectedAttractionsBody');
    if (!tbody) return;

    tbody.insertBefore(buildSelectedAttractionRowFromItem(attractionItem), tbody.firstChild);
    attractionItem.classList.add('attraction-selected');
}

function removeAttractionFromSelectedTable(attractionId) {
    const row = document.querySelector(`#selectedAttractionsBody .selected-attraction-row[data-attraction-id="${attractionId}"]`);
    if (row) {
        row.remove();
    }
    selectedAttractionIds.delete(String(attractionId));
    syncAttractionSelectionUi();
}

function getSelectedAttractionRows() {
    return Array.from(document.querySelectorAll('#selectedAttractionsBody .selected-attraction-row'));
}

function getVisibleSelectedAttractionRows() {
    return getSelectedAttractionRows().filter(row => !row.classList.contains('d-none'));
}

function attractionSelectionLabel(count) {
    return count === 1 ? '1 Attraction Selected' : `${count} Attractions Selected`;
}

function attractionRemoveLabel(count) {
    return count === 1 ? 'Remove 1 Attraction' : `Remove ${count} Attractions`;
}

function isAttractionSelected(attractionId) {
    return selectedAttractionIds.has(String(attractionId));
}

function toggleAttractionSelection(attractionId, shouldSelect) {
    const id = String(attractionId);
    if (shouldSelect) {
        selectedAttractionIds.add(id);
    } else {
        selectedAttractionIds.delete(id);
    }
    syncAttractionSelectionUi();
}

function toggleSelectAllSelectedAttractions() {
    const visibleRows = getVisibleSelectedAttractionRows();
    const allVisibleSelected = visibleRows.length > 0 && visibleRows.every(row => {
        return isAttractionSelected(row.getAttribute('data-attraction-id'));
    });

    visibleRows.forEach(row => {
        const id = row.getAttribute('data-attraction-id');
        if (allVisibleSelected) {
            selectedAttractionIds.delete(String(id));
        } else {
            selectedAttractionIds.add(String(id));
        }
    });

    syncAttractionSelectionUi();
}

function clearAttractionSelection() {
    selectedAttractionIds.clear();
    syncAttractionSelectionUi();
}

function syncAttractionSelectionUi() {
    getSelectedAttractionRows().forEach(row => {
        const checkbox = row.querySelector('.selected-attraction-checkbox');
        if (checkbox) {
            checkbox.checked = isAttractionSelected(row.getAttribute('data-attraction-id'));
        }
    });

    const visibleRows = getVisibleSelectedAttractionRows();
    const selectedVisibleCount = visibleRows.filter(row => isAttractionSelected(row.getAttribute('data-attraction-id'))).length;
    const selectAll = document.getElementById('selectAllSelectedAttractions');
    if (selectAll) {
        selectAll.checked = visibleRows.length > 0 && selectedVisibleCount === visibleRows.length;
        selectAll.indeterminate = selectedVisibleCount > 0 && selectedVisibleCount < visibleRows.length;
    }

    const count = selectedAttractionIds.size;
    const toolbar = document.getElementById('selectedAttractionsBulkToolbar');
    const countLabel = document.getElementById('bulkSelectedCountLabel');
    const removeLabel = document.getElementById('bulkRemoveSelectedLabel');
    if (toolbar) toolbar.classList.toggle('d-none', count === 0);
    if (countLabel) countLabel.textContent = attractionSelectionLabel(count);
    if (removeLabel) removeLabel.textContent = count > 0 ? `Remove Selected (${count})` : 'Remove Selected';
}

function openBulkRemoveAttractionsModal() {
    if (bulkRemoveInProgress || selectedAttractionIds.size === 0) return;

    bulkRemoveIds = Array.from(selectedAttractionIds);
    const count = bulkRemoveIds.length;
    const noun = count === 1 ? 'Attraction' : 'Attractions';
    const title = document.getElementById('bulkRemoveAttractionsTitle');
    const body = document.getElementById('bulkRemoveAttractionsBody');
    const confirmBtn = document.getElementById('confirmBulkRemoveAttractions');
    const confirmText = confirmBtn?.querySelector('.btn-text');

    if (title) title.textContent = `Remove ${count} ${noun}?`;
    if (body) {
        body.textContent = count === 1
            ? 'Are you sure you want to remove this selected attraction from your offerings?'
            : `Are you sure you want to remove these ${count} selected attractions from your offerings?`;
    }
    if (confirmText) confirmText.textContent = attractionRemoveLabel(count);

    setBulkRemoveAttractionsLoading(false);
    const modalEl = document.getElementById('bulkRemoveAttractionsModal');
    if (modalEl) new bootstrap.Modal(modalEl).show();
}

function setBulkRemoveAttractionsLoading(isLoading) {
    bulkRemoveInProgress = isLoading;
    const confirmBtn = document.getElementById('confirmBulkRemoveAttractions');
    const toolbarBtn = document.getElementById('bulkRemoveSelectedBtn');
    const closeBtn = document.getElementById('bulkRemoveModalCloseBtn');
    const cancelBtn = document.getElementById('cancelBulkRemoveAttractions');
    if (confirmBtn) {
        confirmBtn.disabled = isLoading;
        const btnText = confirmBtn.querySelector('.btn-text');
        const btnLoader = confirmBtn.querySelector('.btn-loader');
        if (btnText) btnText.classList.toggle('d-none', isLoading);
        if (btnLoader) btnLoader.classList.toggle('d-none', !isLoading);
    }
    if (toolbarBtn) toolbarBtn.disabled = isLoading;
    if (closeBtn) closeBtn.disabled = isLoading;
    if (cancelBtn) cancelBtn.disabled = isLoading;
}

function removeSelectedAttractions() {
    if (bulkRemoveInProgress || bulkRemoveIds.length === 0) return;

    setBulkRemoveAttractionsLoading(true);

    fetch('{{ route('services.attractions.remove-bulk') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ attraction_ids: bulkRemoveIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            selectedAttractionIds.clear();
            bulkRemoveInProgress = false;
            const modal = bootstrap.Modal.getInstance(document.getElementById('bulkRemoveAttractionsModal'));
            if (modal) modal.hide();
            flashAndReload('success', data.message || `${bulkRemoveIds.length} attractions removed successfully.`);
            return;
        }

        setBulkRemoveAttractionsLoading(false);
        showAlert('error', data.message || 'Unable to remove the selected attractions. Please try again.');
    })
    .catch(error => {
        console.error('Error:', error);
        setBulkRemoveAttractionsLoading(false);
        showAlert('error', 'Unable to remove the selected attractions. Please try again.');
    });
}

function refreshSelectedAttractionPagination() {
    if (selectedAttractionsPaginator) {
        selectedAttractionsPaginator.refresh(1);
    }
}

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

function applyAttractionFilters() {
    const searchTerm = (document.getElementById('attractionSearch').value || '').toLowerCase();
    const selectedCity = (document.getElementById('attractionCitySelect').value || '').toLowerCase();
    const attractionItems = document.querySelectorAll('.attraction-item');
    let visibleCount = 0;

    attractionItems.forEach(item => {
        if (item.classList.contains('attraction-selected')) {
            item.style.display = 'none';
            return;
        }

        const name = item.getAttribute('data-attraction-name') || '';
        const city = item.getAttribute('data-city') || '';

        const matchSearch = !searchTerm || name.includes(searchTerm);
        const matchCity = !selectedCity || city === selectedCity;

        if (matchSearch && matchCity) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    const countElement = document.getElementById('attractionCount');
    const total = document.querySelectorAll('.attraction-item').length;
    countElement.textContent = `Showing ${visibleCount} of ${total} attractions`;
}

function resetAttractionFilters() {
    document.getElementById('attractionSearch').value = '';
    resetCitySearchSelect('#attractionCitySelect');
    applyAttractionFilters();
}

function selectAttraction(encryptedAttractionId, attractionName, buttonEl) {
    fetch('{{ route('services.attractions.select') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            attraction_id: encryptedAttractionId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            flashAndReload('success', data.message || `${attractionName} has been selected successfully!`);
        } else {
            resetAttractionButton(buttonEl);
            showAlert('error', data.message || 'An error occurred while selecting the attraction.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        resetAttractionButton(buttonEl);
        showAlert('error', 'An error occurred while selecting the attraction.');
    });
}

function removeAttraction(attractionId, attractionName) {
    fetch('{{ route('services.attractions.remove') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            attraction_id: attractionId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            flashAndReload('success', data.message || `${attractionName} has been removed successfully!`);
        } else {
            showAlert('error', data.message || 'An error occurred while removing the attraction.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred while removing the attraction.');
    });
}

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    showStoredFlash();
    initServiceCitySearch('#attractionCitySelect', applyAttractionFilters);
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Attraction selection functionality
    document.querySelectorAll('.select-attraction-btn').forEach(button => {
        button.addEventListener('click', function() {
            if (this.disabled) return;

            const attractionId = this.getAttribute('data-attraction-id');
            const attractionName = this.getAttribute('data-attraction-name');

            setAttractionButtonLoading(this);
            selectAttraction(attractionId, attractionName, this);
        });
    });

    // Attraction removal (including dynamically added rows)
    document.addEventListener('click', function(event) {
        const removeBtn = event.target.closest('.remove-attraction-btn');
        if (!removeBtn || !document.getElementById('selectedAttractionsBody')?.contains(removeBtn)) {
            return;
        }

        currentAttractionId = removeBtn.getAttribute('data-attraction-id');
        const attractionName = removeBtn.getAttribute('data-attraction-name');

        document.getElementById('removeAttractionName').textContent = attractionName;

        const modal = new bootstrap.Modal(document.getElementById('removeAttractionModal'));
        modal.show();
    });

    // Confirm removal
    document.getElementById('confirmRemoveAttraction').addEventListener('click', function() {
        if (!currentAttractionId) return;

        const attractionName = document.getElementById('removeAttractionName').textContent;
        const confirmBtn = this;
        confirmBtn.disabled = true;

        removeAttraction(currentAttractionId, attractionName);

        const modal = bootstrap.Modal.getInstance(document.getElementById('removeAttractionModal'));
        if (modal) {
            modal.hide();
        }

        confirmBtn.disabled = false;
    });

    const selectAllSelected = document.getElementById('selectAllSelectedAttractions');
    if (selectAllSelected) {
        selectAllSelected.addEventListener('change', function() {
            toggleSelectAllSelectedAttractions();
        });
    }

    document.getElementById('selectedAttractionsBody')?.addEventListener('change', function(event) {
        const checkbox = event.target.closest('.selected-attraction-checkbox');
        if (!checkbox) return;
        toggleAttractionSelection(checkbox.value, checkbox.checked);
    });

    document.getElementById('clearAttractionSelectionBtn')?.addEventListener('click', function() {
        clearAttractionSelection();
    });

    document.getElementById('bulkRemoveSelectedBtn')?.addEventListener('click', function() {
        openBulkRemoveAttractionsModal();
    });

    document.getElementById('confirmBulkRemoveAttractions')?.addEventListener('click', function() {
        removeSelectedAttractions();
    });

    document.getElementById('bulkRemoveAttractionsModal')?.addEventListener('hide.bs.modal', function(event) {
        if (bulkRemoveInProgress) {
            event.preventDefault();
        }
    });
    // Attractions are already limited to the DMC country server-side.
    // City options come from the DMC country cities list.
    applyAttractionFilters();

    // Selected Attractions: client-side pagination + search
    const selectedBody = document.getElementById('selectedAttractionsBody');
    if (selectedBody) {
        const pagination = document.getElementById('selectedAttractionsPagination');
        const searchInput = document.getElementById('selectedAttractionSearch');
        const pageSizeSelect = document.getElementById('selectedAttractionPageSize');

        function getPageSize() { return parseInt(pageSizeSelect.value, 10) || 10; }

        function getAllRows() {
            return Array.from(selectedBody.querySelectorAll('.selected-attraction-row'));
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
            for (let i = 1; i <= totalPages; i++) pagination.appendChild(createItem(String(i), i, false, i===page));
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
            const showingEl = document.getElementById('selectedAttractionsShowingCount');
            if (showingEl) {
                const pageCount = filtered.slice(start, end).length;
                showingEl.textContent = `Showing ${pageCount} of ${total} attractions`;
            }
            syncAttractionSelectionUi();
        }

        selectedAttractionsPaginator = {
            refresh(page = 1) {
                render(page);
            }
        };

        render(1);
        searchInput.addEventListener('input', () => render(1));
        pageSizeSelect.addEventListener('change', () => render(1));
    }
});

function flashAndReload(type, message) {
    try {
        sessionStorage.setItem('servicesFlash', JSON.stringify({ type: type || 'success', message: message || '' }));
    } catch (e) {}
    window.location.reload();
}

function showStoredFlash() {
    try {
        const raw = sessionStorage.getItem('servicesFlash');
        if (!raw) return;
        sessionStorage.removeItem('servicesFlash');
        const flash = JSON.parse(raw);
        if (flash && flash.message) {
            showAlert(flash.type || 'success', flash.message);
        }
    } catch (e) {}
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    const alertContainer = document.getElementById('serviceActionAlerts') || document.getElementById('availableAttractionsAlerts');
    if (!alertContainer) return;

    alertContainer.innerHTML = alertHtml;

    setTimeout(() => {
        const alert = alertContainer.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}
</script>
@endsection 