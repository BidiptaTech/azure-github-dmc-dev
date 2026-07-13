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
        <div class="card mb-4 {{ (!isset($selectedAgencies) || count($selectedAgencies) === 0) ? 'd-none' : '' }}" id="selectedAgenciesSection">
            <div class="card-header">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-2">
                    <h5 class="mb-0" id="selectedAgenciesTitle">Selected Agencies ({{ isset($selectedAgencies) ? count($selectedAgencies) : 0 }})</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="input-group input-group-sm" style="width: 260px;">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" class="form-control" id="selectedAgencySearch" placeholder="Search selected agencies...">
                        </div>
                        <div class="input-group input-group-sm" style="width: 160px;">
                            <span class="input-group-text">Per page</span>
                            <select id="selectedAgencyPageSize" class="form-select">
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
                                <th>Agency Name</th>
                                <th>Location</th>
                                <th>Contact</th>
                                <th>Branches</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="selectedAgenciesBody">
                            @foreach(($selectedAgencies ?? []) as $agency)
                                <tr class="selected-agency-row" data-agency-id="{{ $agency->agency_id }}" data-name="{{ strtolower($agency->agency_name) }}" data-location="{{ strtolower($agency->city) }}, {{ strtolower($agency->country) }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px; overflow: hidden;">
                                                @if($agency->logo && !empty($agency->logo))
                                                    <img src="{{ $agency->logo }}" 
                                                         alt="{{ $agency->agency_name }} Logo" 
                                                         style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <i class="ri-building-line text-muted" style="display: none;"></i>
                                                @else
                                                    <i class="ri-building-line text-muted"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <strong>{{ $agency->agency_name }}</strong>
                                                <br>
                                                {{-- <small class="text-muted">ID: {{ $agency->agency_id }}</small> --}}
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
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="selectedAgenciesPagination"></ul>
                    </nav>
                </div>
            </div>
        </div>

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
                <div id="availableAgenciesAlerts"></div>
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Advanced Filters -->
                <div class="row g-3 align-items-end mb-4">
                    <div class="col-lg-4 col-md-6">
                        <label for="agencySearch" class="form-label mb-1">Search</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" class="form-control" id="agencySearch" placeholder="Search agencies by name..." onkeyup="applyAgencyFilters()">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label for="agencyCountrySelect" class="form-label mb-1">Country</label>
                        <select id="agencyCountrySelect" class="form-select" onchange="onAgencyCountryChange()">
                            <option value="">All Countries</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label for="agencyCitySelect" class="form-label mb-1">City</label>
                        <select id="agencyCitySelect" class="form-select" onchange="applyAgencyFilters()" disabled>
                            <option value="">All Cities</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 text-end">
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="resetAgencyFilters()"><i class="ri-filter-off-line me-1"></i>Clear Filters</button>
                        <div class="small text-muted mt-2" id="agencyCount">Showing all agencies</div>
                    </div>
                </div>
                
                <div class="row" id="agenciesContainer">
                    @if(isset($availableAgencies) && count($availableAgencies) > 0)
                        @foreach($availableAgencies as $agency)
                            <div class="col-lg-4 col-md-6 mb-3 agency-item"
                                 data-agency-id="{{ $agency->agency_id }}"
                                 data-display-name="{{ $agency->agency_name }}"
                                 data-logo="{{ $agency->logo }}"
                                 data-display-city="{{ $agency->city }}"
                                 data-display-country="{{ $agency->country }}"
                                 data-email="{{ $agency->email }}"
                                 data-phone="{{ $agency->phone }}"
                                 data-total-branches="{{ $agency->total_branches }}"
                                 data-view-url="{{ route('agencies.show', Crypt::encrypt($agency->agency_id)) }}"
                                 data-agency-name="{{ strtolower($agency->agency_name) }}"
                                 data-country="{{ strtolower($agency->country) }}"
                                 data-city="{{ strtolower($agency->city) }}">
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
                                    {{-- Top banner image like hotel cards --}}
                                    @if($agency->logo && !empty($agency->logo))
                                        <div style="height: 150px; overflow: hidden; border-top-left-radius: .375rem; border-top-right-radius: .375rem;">
                                            <img src="{{ $agency->logo }}" alt="{{ $agency->agency_name }} Logo" style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 150px; overflow: hidden; border-top-left-radius: .375rem; border-top-right-radius: .375rem;">
                                            <i class="ri-building-line text-muted" style="font-size: 3rem;"></i>
                                        </div>
                                    @endif
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <h6 class="agency-name mb-1">{{ Str::limit($agency->agency_name, 25) }}</h6>
                                            {{-- <small class="text-muted">ID: {{ $agency->agency_id }}</small> --}}
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
const defaultAgencyCountry = '{{ strtolower(auth()->user()->country ?? '') }}';
const csrfToken = '{{ csrf_token() }}';
let selectedAgenciesPaginator = null;

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function resetAgencyButton(btn) {
    if (!btn) return;
    const btnText = btn.querySelector('.btn-text');
    const btnLoader = btn.querySelector('.btn-loader');
    if (btnText) btnText.classList.remove('d-none');
    if (btnLoader) btnLoader.classList.add('d-none');
    btn.disabled = false;
}

function setAgencyButtonLoading(btn) {
    if (!btn) return;
    const btnText = btn.querySelector('.btn-text');
    const btnLoader = btn.querySelector('.btn-loader');
    if (btnText) btnText.classList.add('d-none');
    if (btnLoader) btnLoader.classList.remove('d-none');
    btn.disabled = true;
}

function updateSelectedAgencyCount() {
    const count = document.querySelectorAll('#selectedAgenciesBody .selected-agency-row').length;
    const title = document.getElementById('selectedAgenciesTitle');
    const section = document.getElementById('selectedAgenciesSection');
    if (title) title.textContent = `Selected Agencies (${count})`;
    if (section) section.classList.toggle('d-none', count === 0);
}

function buildSelectedAgencyRowFromItem(item) {
    const agencyId = item.getAttribute('data-agency-id');
    const name = item.getAttribute('data-display-name') || '';
    const city = item.getAttribute('data-display-city') || '';
    const country = item.getAttribute('data-display-country') || '';
    const logo = item.getAttribute('data-logo') || '';
    const email = item.getAttribute('data-email') || '';
    const phone = item.getAttribute('data-phone') || '';
    const branches = parseInt(item.getAttribute('data-total-branches') || '0', 10);
    const viewUrl = item.getAttribute('data-view-url') || '#';
    const branchLabel = `${branches} ${branches === 1 ? 'Location' : 'Locations'}`;
    const logoHtml = logo
        ? `<img src="${escapeHtml(logo)}" alt="${escapeHtml(name)} Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"><i class="ri-building-line text-muted" style="display: none;"></i>`
        : `<i class="ri-building-line text-muted"></i>`;

    const row = document.createElement('tr');
    row.className = 'selected-agency-row';
    row.setAttribute('data-agency-id', agencyId);
    row.setAttribute('data-name', name.toLowerCase());
    row.setAttribute('data-location', `${city}, ${country}`.toLowerCase());
    row.innerHTML = `
        <td>
            <div class="d-flex align-items-center">
                <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; overflow: hidden;">${logoHtml}</div>
                <div><strong>${escapeHtml(name)}</strong></div>
            </div>
        </td>
        <td>${escapeHtml(city)}, ${escapeHtml(country)}</td>
        <td><small class="d-block">${escapeHtml(email)}</small><small class="text-muted">${escapeHtml(phone)}</small></td>
        <td><span class="badge bg-label-info">${escapeHtml(branchLabel)}</span></td>
        <td>
            <div class="btn-group" role="group">
                <a href="${viewUrl}" class="btn btn-sm btn-outline-primary"><i class="ri-eye-line me-1"></i>View</a>
                <button type="button" class="btn btn-sm btn-outline-danger remove-agency-btn"
                        data-agency-id="${escapeHtml(agencyId)}" data-agency-name="${escapeHtml(name)}">
                    <i class="ri-delete-bin-line me-1"></i>Remove
                </button>
            </div>
        </td>`;
    return row;
}

function addAgencyToSelectedTable(item) {
    const agencyId = item.getAttribute('data-agency-id');
    if (document.querySelector(`#selectedAgenciesBody .selected-agency-row[data-agency-id="${agencyId}"]`)) return;
    const tbody = document.getElementById('selectedAgenciesBody');
    if (!tbody) return;
    tbody.insertBefore(buildSelectedAgencyRowFromItem(item), tbody.firstChild);
    item.classList.add('agency-selected');
}

function removeAgencyFromSelectedTable(agencyId) {
    const row = document.querySelector(`#selectedAgenciesBody .selected-agency-row[data-agency-id="${agencyId}"]`);
    if (row) row.remove();
}

function refreshSelectedAgencyPagination() {
    if (selectedAgenciesPaginator) selectedAgenciesPaginator.refresh(1);
}

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

function applyAgencyFilters() {
    const searchTerm = (document.getElementById('agencySearch').value || '').toLowerCase();
    const selectedCountry = (document.getElementById('agencyCountrySelect').value || '').toLowerCase();
    const selectedCity = (document.getElementById('agencyCitySelect').value || '').toLowerCase();
    const items = document.querySelectorAll('.agency-item');
    let visibleCount = 0;

    items.forEach(item => {
        if (item.classList.contains('agency-selected')) {
            item.style.display = 'none';
            return;
        }

        const name = item.getAttribute('data-agency-name') || '';
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

    const countElement = document.getElementById('agencyCount');
    const total = document.querySelectorAll('.agency-item').length;
    countElement.textContent = `Showing ${visibleCount} of ${total} agencies`;
}

function resetAgencyFilters() {
    document.getElementById('agencySearch').value = '';
    document.getElementById('agencyCountrySelect').value = '';
    const citySelect = document.getElementById('agencyCitySelect');
    citySelect.innerHTML = '<option value="">All Cities</option>';
    citySelect.disabled = true;
    applyAgencyFilters();
}

function onAgencyCountryChange() {
    const country = (document.getElementById('agencyCountrySelect').value || '').toLowerCase();
    const citySelect = document.getElementById('agencyCitySelect');
    citySelect.innerHTML = '<option value="">All Cities</option>';

    if (!country) {
        citySelect.disabled = true;
        applyAgencyFilters();
        return;
    }

    const items = document.querySelectorAll('.agency-item');
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

    applyAgencyFilters();
}

function selectAgency(agencyId, agencyName, buttonEl) {
    fetch('{{ route('services.agencies.select') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ agency_id: agencyId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = buttonEl ? buttonEl.closest('.agency-item') : null;
            if (item) {
                addAgencyToSelectedTable(item);
                item.style.display = 'none';
            }
            updateSelectedAgencyCount();
            refreshSelectedAgencyPagination();
            applyAgencyFilters();
            showAlert('success', data.message || `${agencyName} has been selected successfully!`);
        } else {
            resetAgencyButton(buttonEl);
            showAlert('error', data.message || 'An error occurred while selecting the agency.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        resetAgencyButton(buttonEl);
        showAlert('error', 'An error occurred while selecting the agency.');
    });
}

function removeAgency(agencyId, agencyName) {
    fetch('{{ route('services.agencies.remove') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ agency_id: agencyId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            removeAgencyFromSelectedTable(agencyId);
            const item = document.querySelector(`.agency-item[data-agency-id="${agencyId}"]`);
            if (item) {
                item.classList.remove('agency-selected');
                resetAgencyButton(item.querySelector('.select-agency-btn'));
            }
            updateSelectedAgencyCount();
            refreshSelectedAgencyPagination();
            applyAgencyFilters();
            showAlert('success', data.message || `${agencyName} has been removed successfully!`);
        } else {
            showAlert('error', data.message || 'An error occurred while removing the agency.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred while removing the agency.');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    document.querySelectorAll('.select-agency-btn').forEach(button => {
        button.addEventListener('click', function() {
            if (this.disabled) return;
            setAgencyButtonLoading(this);
            selectAgency(this.getAttribute('data-agency-id'), this.getAttribute('data-agency-name'), this);
        });
    });

    document.addEventListener('click', function(event) {
        const removeBtn = event.target.closest('.remove-agency-btn');
        if (!removeBtn || !document.getElementById('selectedAgenciesBody')?.contains(removeBtn)) return;
        currentAgencyId = removeBtn.getAttribute('data-agency-id');
        document.getElementById('removeAgencyName').textContent = removeBtn.getAttribute('data-agency-name');
        new bootstrap.Modal(document.getElementById('removeAgencyModal')).show();
    });

    document.getElementById('confirmRemoveAgency').addEventListener('click', function() {
        if (!currentAgencyId) return;
        const agencyName = document.getElementById('removeAgencyName').textContent;
        removeAgency(currentAgencyId, agencyName);
        const modal = bootstrap.Modal.getInstance(document.getElementById('removeAgencyModal'));
        if (modal) modal.hide();
    });
    // Populate country dropdown from DOM
    const countrySelect = document.getElementById('agencyCountrySelect');
    const items = document.querySelectorAll('.agency-item');
    const countrySet = new Set();
    items.forEach(item => {
        const c = item.getAttribute('data-country');
        if (c) countrySet.add(c);
    });
    Array.from(countrySet).sort().forEach(country => {
        const opt = document.createElement('option');
        opt.value = country;
        opt.textContent = country.replace(/\b\w/g, ch => ch.toUpperCase());
        countrySelect.appendChild(opt);
    });
    if (defaultAgencyCountry && Array.from(countrySet).includes(defaultAgencyCountry)) {
        countrySelect.value = defaultAgencyCountry;
        onAgencyCountryChange();
    }

    // Selected Agencies: client-side pagination + search
    const selectedBody = document.getElementById('selectedAgenciesBody');
    if (selectedBody) {
        const pagination = document.getElementById('selectedAgenciesPagination');
        const searchInput = document.getElementById('selectedAgencySearch');
        const pageSizeSelect = document.getElementById('selectedAgencyPageSize');

        function getPageSize() { return parseInt(pageSizeSelect.value, 10) || 10; }
        function getAllRows() { return Array.from(selectedBody.querySelectorAll('.selected-agency-row')); }
        function getFilteredRows() {
            const term = (searchInput.value || '').toLowerCase();
            return getAllRows().filter(r => {
                if (!term) return true;
                return (r.getAttribute('data-name') || '').includes(term) || (r.getAttribute('data-location') || '').includes(term);
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
        }
        selectedAgenciesPaginator = { refresh(page = 1) { render(page); } };
        render(1);
        searchInput.addEventListener('input', () => render(1));
        pageSizeSelect.addEventListener('change', () => render(1));
    }
});

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
    const alertContainer = document.getElementById('availableAgenciesAlerts');
    if (!alertContainer) return;
    alertContainer.insertAdjacentHTML('beforeend', alertHtml);
    setTimeout(() => { const alert = alertContainer.querySelector('.alert'); if (alert) alert.remove(); }, 5000);
}
</script>
@endsection

