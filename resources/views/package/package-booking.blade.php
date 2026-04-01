@extends('layouts.layout')
@section('title', 'Package Booking')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="ri-suitcase-line me-2 text-primary"></i>Package Booking</h4>
                <p class="text-muted mb-0">Choose booking criteria, load a package, then modify services.</p>
            </div>
            <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i>Back
            </a>
        </div>

        <form action="{{ route('packages.booking.store') }}" method="POST" id="package-booking-form">
            @csrf
            <input type="hidden" name="package_id" id="selected_package_id" value="">

            <div class="card mb-4">
                <div class="card-header bg-light"><h5 class="mb-0">Booking Basics</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Travel Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="travel_start_date" name="travel_start_date" required min="{{ date('Y-m-d') }}" value="{{ old('travel_start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Travel End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="travel_end_date" name="travel_end_date" required min="{{ date('Y-m-d') }}" value="{{ old('travel_end_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Adults <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="adult_count" name="adult_count" min="1" value="{{ old('adult_count', 2) }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Children</label>
                            <input type="number" class="form-control" id="child_count" name="child_count" min="0" value="{{ old('child_count', 0) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Agency</label>
                            <select id="agency_id" name="agency_id" class="form-select" onchange="loadAgentsByAgency(this.value, null)">
                                <option value="">Select Agency</option>
                                @foreach($agencies as $a)
                                    <option value="{{ $a->agency_id }}">{{ $a->agency_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Agent</label>
                            <select id="agent_id" name="agent_id" class="form-select">
                                <option value="">Select Agency first</option>
                                
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-8">
                            <label class="form-label">Package <span class="text-danger">*</span></label>
                            <select id="package_select" class="form-select" disabled>
                                <option value="">Select date range and pax to load packages</option>
                            </select>
                            <small class="text-muted d-block mt-1" id="packageFilterMessage"></small>
                        </div>
                    </div>
                </div>
            </div>

            <div id="packageDetailsSection" style="display:none;">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light"><h6 class="mb-0">Hotels</h6></div>
                            <div class="card-body">
                                <div class="d-flex gap-2 mb-3">
                                    <input type="text" class="form-control form-control-sm" id="newHotelName" placeholder="Hotel name">
                                    <button type="button" class="btn btn-sm btn-primary" id="addHotelBtn">Add</button>
                                </div>
                                <div id="hotelsList"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light"><h6 class="mb-0">Attractions</h6></div>
                            <div class="card-body">
                                <div class="d-flex gap-2 mb-3">
                                    <input type="text" class="form-control form-control-sm" id="newAttractionName" placeholder="Attraction name">
                                    <button type="button" class="btn btn-sm btn-success" id="addAttractionBtn">Add</button>
                                </div>
                                <div id="attractionsList"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light"><h6 class="mb-0">Restaurants</h6></div>
                            <div class="card-body">
                                <div class="d-flex gap-2 mb-3">
                                    <input type="text" class="form-control form-control-sm" id="newRestaurantName" placeholder="Restaurant name">
                                    <button type="button" class="btn btn-sm btn-warning" id="addRestaurantBtn">Add</button>
                                </div>
                                <div id="restaurantsList"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light"><h6 class="mb-0">Guides</h6></div>
                            <div class="card-body">
                                <div class="d-flex gap-2 mb-3">
                                    <input type="text" class="form-control form-control-sm" id="newGuideName" placeholder="Guide name">
                                    <button type="button" class="btn btn-sm btn-info" id="addGuideBtn">Add</button>
                                </div>
                                <div id="guidesList"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-header bg-light"><h6 class="mb-0">Arrival Data</h6></div>
                            <div class="card-body">
                                <label class="form-label">Enabled</label>
                                <select class="form-select form-select-sm mb-2" id="arrivalEnabled">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                                <label class="form-label">Pickup Port ID</label>
                                <input type="text" class="form-control form-control-sm mb-2" id="arrivalPickupPortId">
                                <label class="form-label">Dropoff Hotel ID</label>
                                <input type="text" class="form-control form-control-sm mb-2" id="arrivalDropoffHotelId">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-header bg-light"><h6 class="mb-0">Departure Data</h6></div>
                            <div class="card-body">
                                <label class="form-label">Enabled</label>
                                <select class="form-select form-select-sm mb-2" id="departureEnabled">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                                <label class="form-label">Pickup Hotel ID</label>
                                <input type="text" class="form-control form-control-sm mb-2" id="departurePickupHotelId">
                                <label class="form-label">Dropoff Port ID</label>
                                <input type="text" class="form-control form-control-sm mb-2" id="departureDropoffPortId">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-header bg-light"><h6 class="mb-0">Transfer Data</h6></div>
                            <div class="card-body">
                                <div class="d-flex gap-2 mb-2">
                                    <input type="text" class="form-control form-control-sm" id="newTransferLabel" placeholder="Pickup -> Dropoff">
                                    <button type="button" class="btn btn-sm btn-secondary" id="addTransferBtn">Add</button>
                                </div>
                                <div id="transfersList"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" rows="3">{{ old('notes') }}</textarea>
                </div> 
            </div>

            <input type="hidden" name="selected_hotels" id="selected_hotels_input">
            <input type="hidden" name="selected_attractions" id="selected_attractions_input">
            <input type="hidden" name="selected_guides" id="selected_guides_input">
            <input type="hidden" name="selected_restaurants" id="selected_restaurants_input">
            <input type="hidden" name="arrival_data" id="arrival_data_input">
            <input type="hidden" name="departure_data" id="departure_data_input">
            <input type="hidden" name="transfer_data" id="transfer_data_input">

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" id="createBookingBtn" disabled>Create Booking</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        let hotels = [];
        let attractions = [];
        let guides = [];
        let restaurants = [];
        let arrivalData = {};
        let departureData = {};
        let transfers = [];
        let selectedPackageCity = '';

        const prefilledPackageId = @json($prefilledPackageId ?? null);
        const filterUrl = @json(route('packages.booking.filter'));
        const detailUrlTemplate = @json(route('packages.booking.details', ['packageId' => '__PACKAGE_ID__']));

        const packageSelect = document.getElementById('package_select');
        const packageFilterMessage = document.getElementById('packageFilterMessage');
        const packageDetailsSection = document.getElementById('packageDetailsSection');
        const createBookingBtn = document.getElementById('createBookingBtn');
        const selectedPackageIdInput = document.getElementById('selected_package_id');

        const hotelsList = document.getElementById('hotelsList');
        const attractionsList = document.getElementById('attractionsList');
        const guidesList = document.getElementById('guidesList');
        const restaurantsList = document.getElementById('restaurantsList');
        const transfersList = document.getElementById('transfersList');

        const startDateEl = document.getElementById('travel_start_date');
        const endDateEl = document.getElementById('travel_end_date');
        const adultsEl = document.getElementById('adult_count');
        const childrenEl = document.getElementById('child_count');

        function esc(v) { return String(v || '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

        function renderList(container, list, nameKey) {
            if (!Array.isArray(list) || list.length === 0) {
                container.innerHTML = '<div class="text-muted small">No items</div>';
                return;
            }
            container.innerHTML = list.map((item, idx) => {
                const name = item[nameKey] || item.name || item.label || ('Item ' + (idx + 1));
                return '<div class="d-flex justify-content-between align-items-center border rounded px-2 py-1 mb-1 small">'
                    + '<span>' + esc(name) + '</span>'
                    + '<button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" data-remove-idx="' + idx + '">x</button>'
                    + '</div>';
            }).join('');
        }

        function bindRemove(container, getList, setList, renderFn) {
            container.querySelectorAll('[data-remove-idx]').forEach(btn => {
                btn.addEventListener('click', function () {
                    const idx = parseInt(this.getAttribute('data-remove-idx'), 10);
                    const list = getList();
                    list.splice(idx, 1);
                    setList(list);
                    renderFn();
                    syncHidden();
                });
            });
        }

        function renderHotels() { renderList(hotelsList, hotels, 'hotel_name'); bindRemove(hotelsList, () => hotels, v => hotels = v, renderHotels); }
        function renderAttractions() { renderList(attractionsList, attractions, 'name'); bindRemove(attractionsList, () => attractions, v => attractions = v, renderAttractions); }
        function renderGuides() { renderList(guidesList, guides, 'name'); bindRemove(guidesList, () => guides, v => guides = v, renderGuides); }
        function renderRestaurants() { renderList(restaurantsList, restaurants, 'restaurant_name'); bindRemove(restaurantsList, () => restaurants, v => restaurants = v, renderRestaurants); }
        function renderTransfers() { renderList(transfersList, transfers, 'pickup_label'); bindRemove(transfersList, () => transfers, v => transfers = v, renderTransfers); }

        function syncHidden() {
            arrivalData = {
                enabled: document.getElementById('arrivalEnabled').value === '1',
                pickup_port_id: document.getElementById('arrivalPickupPortId').value || null,
                dropoff_hotel_id: document.getElementById('arrivalDropoffHotelId').value || null,
                vehicles: Array.isArray(arrivalData.vehicles) ? arrivalData.vehicles : []
            };
            departureData = {
                enabled: document.getElementById('departureEnabled').value === '1',
                pickup_hotel_id: document.getElementById('departurePickupHotelId').value || null,
                dropoff_port_id: document.getElementById('departureDropoffPortId').value || null,
                vehicles: Array.isArray(departureData.vehicles) ? departureData.vehicles : []
            };

            document.getElementById('selected_hotels_input').value = JSON.stringify(hotels || []);
            document.getElementById('selected_attractions_input').value = JSON.stringify(attractions || []);
            document.getElementById('selected_guides_input').value = JSON.stringify(guides || []);
            document.getElementById('selected_restaurants_input').value = JSON.stringify(restaurants || []);
            document.getElementById('arrival_data_input').value = JSON.stringify(arrivalData || {});
            document.getElementById('departure_data_input').value = JSON.stringify(departureData || {});
            document.getElementById('transfer_data_input').value = JSON.stringify(transfers || []);
        }

        function resetPackageDetailsUI() {
            hotels = [];
            attractions = [];
            guides = [];
            restaurants = [];
            arrivalData = {};
            departureData = {};
            transfers = [];
            selectedPackageCity = '';
            packageDetailsSection.style.display = 'none';
            createBookingBtn.disabled = true;
            selectedPackageIdInput.value = '';
            renderHotels();
            renderAttractions();
            renderGuides();
            renderRestaurants();
            renderTransfers();
            document.getElementById('arrivalEnabled').value = '0';
            document.getElementById('arrivalPickupPortId').value = '';
            document.getElementById('arrivalDropoffHotelId').value = '';
            document.getElementById('departureEnabled').value = '0';
            document.getElementById('departurePickupHotelId').value = '';
            document.getElementById('departureDropoffPortId').value = '';
            syncHidden();
        }

        function applyPackageData(pkg) {
            hotels = Array.isArray(pkg.selected_hotels) ? pkg.selected_hotels : [];
            attractions = Array.isArray(pkg.selected_attractions) ? pkg.selected_attractions : [];
            guides = Array.isArray(pkg.selected_guides) ? pkg.selected_guides : [];
            restaurants = Array.isArray(pkg.selected_restaurants) ? pkg.selected_restaurants : [];
            arrivalData = pkg.arrival_data || {};
            departureData = pkg.departure_data || {};
            transfers = Array.isArray(pkg.transfer_data) ? pkg.transfer_data : [];
            selectedPackageCity = pkg.city || '';

            document.getElementById('arrivalEnabled').value = arrivalData && arrivalData.enabled ? '1' : '0';
            document.getElementById('arrivalPickupPortId').value = arrivalData && arrivalData.pickup_port_id ? arrivalData.pickup_port_id : '';
            document.getElementById('arrivalDropoffHotelId').value = arrivalData && arrivalData.dropoff_hotel_id ? arrivalData.dropoff_hotel_id : '';
            document.getElementById('departureEnabled').value = departureData && departureData.enabled ? '1' : '0';
            document.getElementById('departurePickupHotelId').value = departureData && departureData.pickup_hotel_id ? departureData.pickup_hotel_id : '';
            document.getElementById('departureDropoffPortId').value = departureData && departureData.dropoff_port_id ? departureData.dropoff_port_id : '';

            packageDetailsSection.style.display = '';
            createBookingBtn.disabled = false;
            selectedPackageIdInput.value = pkg.package_id || '';

            renderHotels();
            renderAttractions();
            renderGuides();
            renderRestaurants();
            renderTransfers();
            syncHidden();
        }

        function buildDetailsUrl(packageId) {
            return detailUrlTemplate.replace('__PACKAGE_ID__', encodeURIComponent(packageId));
        }

        async function loadPackageDetails(packageId) {
            if (!packageId) {
                resetPackageDetailsUI();
                return;
            }
            const response = await fetch(buildDetailsUrl(packageId), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await response.json();
            if (!response.ok || !data.success || !data.package) {
                throw new Error(data.message || 'Unable to load package details.');
            }
            applyPackageData(data.package);
        }

        function allFilterInputsReady() {
            return startDateEl.value && endDateEl.value && parseInt(adultsEl.value || '0', 10) > 0 && childrenEl.value !== '';
        }

        async function fetchFilteredPackages() {
            resetPackageDetailsUI();
            packageSelect.innerHTML = '<option value="">Select date range and pax to load packages</option>';
            packageSelect.disabled = true;

            if (!allFilterInputsReady()) {
                packageFilterMessage.textContent = 'Fill travel dates and pax to load matching packages.';
                return;
            }

            const params = new URLSearchParams({
                travel_start_date: startDateEl.value,
                travel_end_date: endDateEl.value,
                adult_count: adultsEl.value || '0',
                child_count: childrenEl.value || '0'
            });

            packageFilterMessage.textContent = 'Loading matching packages...';
            const response = await fetch(filterUrl + '?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await response.json();
            if (!response.ok || !data.success) {
                packageFilterMessage.textContent = 'Unable to fetch packages.';
                return;
            }

            const packages = Array.isArray(data.packages) ? data.packages : [];
            if (packages.length === 0) {
                packageSelect.innerHTML = '<option value="">No matching packages found</option>';
                packageFilterMessage.textContent = 'No packages match selected duration and pax.';
                return;
            }

            packageSelect.innerHTML = '<option value="">Select package</option>' + packages.map(pkg =>
                '<option value="' + esc(pkg.package_id) + '">' + esc(pkg.title) + ' (' + esc(pkg.destination) + ' / ' + esc(pkg.city) + ')</option>'
            ).join('');
            packageSelect.disabled = false;
            packageFilterMessage.textContent = packages.length + ' package(s) found.';

            if (prefilledPackageId && packages.some(p => p.package_id === prefilledPackageId)) {
                packageSelect.value = prefilledPackageId;
                await loadPackageDetails(prefilledPackageId);
            }
        }

        document.getElementById('addHotelBtn').addEventListener('click', function () {
            const name = document.getElementById('newHotelName').value.trim();
            if (!name) return;
            const uid = Date.now().toString();
            hotels.push({ id: uid, hotel_id: uid, hotel_name: name, name: name, city: selectedPackageCity });
            document.getElementById('newHotelName').value = '';
            renderHotels(); syncHidden();
        });
        document.getElementById('addAttractionBtn').addEventListener('click', function () {
            const name = document.getElementById('newAttractionName').value.trim();
            if (!name) return;
            const uid = Date.now();
            attractions.push({ id: uid, attraction_id: uid, name: name, location: selectedPackageCity });
            document.getElementById('newAttractionName').value = '';
            renderAttractions(); syncHidden();
        });
        document.getElementById('addRestaurantBtn').addEventListener('click', function () {
            const name = document.getElementById('newRestaurantName').value.trim();
            if (!name) return;
            const uid = Date.now().toString();
            restaurants.push({ id: uid, restaurant_id: uid, restaurant_name: name, name: name });
            document.getElementById('newRestaurantName').value = '';
            renderRestaurants(); syncHidden();
        });
        document.getElementById('addGuideBtn').addEventListener('click', function () {
            const name = document.getElementById('newGuideName').value.trim();
            if (!name) return;
            guides.push({ id: Date.now(), name: name, languages: [] });
            document.getElementById('newGuideName').value = '';
            renderGuides(); syncHidden();
        });
        document.getElementById('addTransferBtn').addEventListener('click', function () {
            const label = document.getElementById('newTransferLabel').value.trim();
            if (!label) return;
            const parts = label.split('->');
            transfers.push({
                pickup_label: (parts[0] || label).trim(),
                dropoff_label: (parts[1] || '').trim(),
                vehicles: [],
                compulsory: false,
                optional: false
            });
            document.getElementById('newTransferLabel').value = '';
            renderTransfers(); syncHidden();
        });

        ['arrivalEnabled', 'arrivalPickupPortId', 'arrivalDropoffHotelId', 'departureEnabled', 'departurePickupHotelId', 'departureDropoffPortId']
            .forEach(id => document.getElementById(id).addEventListener('change', syncHidden));

        [startDateEl, endDateEl, adultsEl, childrenEl, document.getElementById('agent_id')].forEach(el => {
            el.addEventListener('change', fetchFilteredPackages);
        });

        packageSelect.addEventListener('change', async function () {
            try {
                await loadPackageDetails(this.value);
            } catch (e) {
                resetPackageDetailsUI();
                packageFilterMessage.textContent = e.message || 'Unable to load package details.';
            }
        });

        document.getElementById('package-booking-form').addEventListener('submit', function (e) {
            if (!selectedPackageIdInput.value) {
                e.preventDefault();
                packageFilterMessage.textContent = 'Please select a package before creating booking.';
            }
        });

        resetPackageDetailsUI();
        fetchFilteredPackages();
    })();

    function loadAgentsByAgency(agencyId, preSelectedAgentId = null) {
        const agentSelect = document.getElementById('agent_id');
        agentSelect.innerHTML = '<option value="">Loading agents...</option>';
        agentSelect.disabled = true;
        const url = `{{ route('packages.getAgentsByAgency') }}?agency_id=${encodeURIComponent(agencyId)}`;
        fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(response => response.json()).then(data => {
            if (data.success) {
                agentSelect.innerHTML = '<option value="">Select Agent</option>' + data.agents.map(a => `<option value="${a.agent_id}">${a.name} - ${a.company_name}</option>`).join('');
                agentSelect.disabled = false;
            } else {
                agentSelect.innerHTML = '<option value="">No agents found</option>';
                agentSelect.disabled = false;
            }
        });
    }
</script>
@endsection
 