@extends('layouts.layout')
@section('title', 'Package Booking')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <x-alert />
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
                                <div id="hotelsList"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light"><h6 class="mb-0">Attractions</h6></div>
                            <div class="card-body">
                                <div id="attractionsList"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light"><h6 class="mb-0">Restaurants</h6></div>
                            <div class="card-body">
                                <div id="restaurantsList"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light"><h6 class="mb-0">Guides</h6></div>
                            <div class="card-body">
                                <div id="guidesList"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light"><h6 class="mb-0">Arrival Data</h6></div>
                            <div class="card-body">
                                <div id="arrivalSummary"></div>
                                <select class="d-none" id="arrivalEnabled">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                                <input type="hidden" id="arrivalPickupPortId">
                                <input type="hidden" id="arrivalDropoffHotelId">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light"><h6 class="mb-0">Departure Data</h6></div>
                            <div class="card-body">
                                <div id="departureSummary"></div>
                                <select class="d-none" id="departureEnabled">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                                <input type="hidden" id="departurePickupHotelId">
                                <input type="hidden" id="departureDropoffPortId">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-md-12">
                        <div class="card h-100">
                            <div class="card-header bg-light"><h6 class="mb-0">Transfer Data</h6></div>
                            <div class="card-body">
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
        let bedOptionsBySourceBedId = {};

        const prefilledPackageId = @json($prefilledPackageId ?? null);
        const filterUrl = @json(route('packages.booking.filter'));
        const detailUrlTemplate = @json(route('packages.booking.details', ['packageId' => '__PACKAGE_ID__']));
        const bedOptionsUrl = @json(route('packages.booking.bed-options'));

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

        function formatBadge(text, cls) {
            return '<span class="badge ' + cls + '">' + esc(text) + '</span>';
        }

        function isOptional(item) {
            return !!(item && item.optional === true);
        }

        function isCompulsory(item) {
            return !!(item && item.compulsory === true);
        }

        function statusBadge(item) {
            if (isCompulsory(item)) {
                return formatBadge('Compulsory', 'bg-success');
            }
            if (isOptional(item)) {
                return formatBadge('Optional', 'bg-warning text-dark');
            }
            return formatBadge('-', 'bg-secondary');
        }

        function initOptionalSelections(list) {
            if (!Array.isArray(list)) return [];
            return list.map(item => {
                const next = item || {};
                if (next.optional === true) {
                    next.selected = false; // default unselected for optional items
                }
                return next;
            });
        }

        function bindOptionalCheckboxes(container, listRef) {
            container.querySelectorAll('.optional-checkbox').forEach(cb => {
                cb.addEventListener('change', function () {
                    const index = parseInt(this.getAttribute('data-index') || '-1', 10);
                    if (!Array.isArray(listRef) || index < 0 || !listRef[index]) return;
                    listRef[index].selected = this.checked === true;
                    syncHidden();
                });
            });
        }

        function renderTypeToggle(section, idx, item) {
            const compChecked = isCompulsory(item) ? 'checked' : '';
            const optChecked = isOptional(item) ? 'checked' : '';
            return '<div class="d-flex align-items-center gap-3 flex-wrap">'
                + '<div class="form-check m-0">'
                + '<input class="form-check-input service-type-radio" type="radio" name="type_' + section + '_' + idx + '" value="compulsory" data-section="' + section + '" data-index="' + idx + '" ' + compChecked + '>'
                + '<label class="form-check-label small">Compulsory</label>'
                + '</div>'
                + '<div class="form-check m-0">'
                + '<input class="form-check-input service-type-radio" type="radio" name="type_' + section + '_' + idx + '" value="optional" data-section="' + section + '" data-index="' + idx + '" ' + optChecked + '>'
                + '<label class="form-check-label small">Optional</label>'
                + '</div>'
                + '</div>';
        }

        function bindTypeToggles(container, listRef) {
            container.querySelectorAll('.service-type-radio').forEach(radio => {
                radio.addEventListener('change', function () {
                    const section = this.getAttribute('data-section') || '';
                    const idx = parseInt(this.getAttribute('data-index') || '-1', 10);
                    if (!Array.isArray(listRef) || idx < 0 || !listRef[idx]) return;
                    if (this.value === 'compulsory') {
                        if (section === 'hotels') handleHotelCompulsory(idx);
                        else if (section === 'attractions') handleAttractionCompulsory(idx);
                        else if (section === 'guides') handleGuideCompulsory(idx);
                        else if (section === 'restaurants') handleRestaurantCompulsory(idx);
                        else if (section === 'transfers') handleTransferCompulsory(idx);
                    } else {
                        listRef[idx].compulsory = false;
                        listRef[idx].optional = true;
                        renderAllSections();
                        syncHidden();
                    }
                });
            });
        }

        function enforceSingleCompulsory(list) {
            if (!Array.isArray(list) || list.length === 0) return list;
            let compulsoryIndex = list.findIndex(item => item && item.compulsory === true && item.optional !== true);
            if (compulsoryIndex === -1) compulsoryIndex = 0;
            return list.map((item, i) => {
                const next = item || {};
                if (i === compulsoryIndex) {
                    next.compulsory = true;
                    next.optional = false;
                } else {
                    next.compulsory = false;
                    next.optional = true;
                }
                return next;
            });
        }

        function handleHotelCompulsory(index) {
            hotels = hotels.map((item, i) => {
                const next = item || {};
                if (i === index) {
                    next.compulsory = true;
                    next.optional = false;
                } else {
                    next.compulsory = false;
                    next.optional = true;
                }
                return next;
            });
            renderAllSections();
            syncHidden();
        }

        function handleAttractionCompulsory(index) {
            attractions = attractions.map((item, i) => {
                const next = item || {};
                if (i === index) {
                    next.compulsory = true;
                    next.optional = false;
                } else {
                    next.compulsory = false;
                    next.optional = true;
                }
                return next;
            });
            renderAllSections();
            syncHidden();
        }

        function handleGuideCompulsory(index) {
            guides = guides.map((item, i) => {
                const next = item || {};
                if (i === index) {
                    next.compulsory = true;
                    next.optional = false;
                } else {
                    next.compulsory = false;
                    next.optional = true;
                }
                return next;
            });
            renderAllSections();
            syncHidden();
        }

        function handleRestaurantCompulsory(index) {
            restaurants = restaurants.map((item, i) => {
                const next = item || {};
                if (i === index) {
                    next.compulsory = true;
                    next.optional = false;
                } else {
                    next.compulsory = false;
                    next.optional = true;
                }
                return next;
            });
            renderAllSections();
            syncHidden();
        }

        function handleTransferCompulsory(index) {
            transfers = transfers.map((item, i) => {
                const next = item || {};
                if (i === index) {
                    next.compulsory = true;
                    next.optional = false;
                } else {
                    next.compulsory = false;
                    next.optional = true;
                }
                return next;
            });
            renderAllSections();
            syncHidden();
        }

        function renderAllSections() {
            renderHotels();
            renderAttractions();
            renderGuides();
            renderRestaurants();
            renderTransfers();
            renderArrivalDeparture();
        }

        function getRoomExtraBedKey(hotelIndex, roomIndex) {
            return String(hotelIndex) + '_' + String(roomIndex);
        }

        function collectSourceBedIds(hotelsListRaw) {
            const ids = [];
            (hotelsListRaw || []).forEach(h => {
                const rooms = Array.isArray(h && h.rooms) ? h.rooms : [];
                rooms.forEach(r => {
                    if (r && r.extra_bed === true && r.bed_id != null && String(r.bed_id).trim() !== '') {
                        ids.push(String(r.bed_id).trim());
                    }
                });
            });
            return Array.from(new Set(ids));
        }

        async function ensureBedOptionsLoaded(hotelsListRaw) {
            const bedIds = collectSourceBedIds(hotelsListRaw);
            if (bedIds.length === 0) {
                bedOptionsBySourceBedId = {};
                return;
            }
            const params = new URLSearchParams({ bed_ids: bedIds.join(',') });
            const response = await fetch(bedOptionsUrl + '?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await response.json();
            bedOptionsBySourceBedId = (response.ok && data && data.success && data.options) ? data.options : {};
        }

        function renderHotels() {
            if (!Array.isArray(hotels) || hotels.length === 0) {
                hotelsList.innerHTML = '<div class="text-muted small">No hotels selected</div>';
                return;
            }
            hotelsList.innerHTML = hotels.map((h, idx) => {
                const rooms = Array.isArray(h.rooms) ? h.rooms : [];
                const roomsHtml = rooms.length
                    ? rooms.map((r, roomIdx) => {
                        const mainQty = parseInt(r.quantity, 10) > 0 ? parseInt(r.quantity, 10) : 1;
                        const base = ''
                            + '<div class="d-flex align-items-center justify-content-between gap-2 mt-1 py-1 border-bottom">'
                            +   '<div class="small fw-semibold">' + esc(r.room_type_name || 'Room') + '</div>'
                            +   '<div class="d-flex align-items-center gap-1 flex-shrink-0">'
                            +     '<label class="small text-muted mb-0">Qty</label>'
                            +     '<input type="number" min="1" step="1" value="' + mainQty + '" class="form-control form-control-sm hotel-main-room-qty" style="width:70px; min-height:30px;" data-hotel-index="' + idx + '" data-room-index="' + roomIdx + '">'
                            +   '</div>'
                            + '</div>';
                        if (!(r && r.extra_bed === true)) return base;

                        const sourceBedId = String((r.bed_id != null ? r.bed_id : '')).trim();
                        const selectId = 'extra_bed_type_' + getRoomExtraBedKey(idx, roomIdx);
                        const options = sourceBedId !== '' ? (bedOptionsBySourceBedId[sourceBedId] || []) : [];
                        const selectedType = r.extra_bed_type || '';

                        const fallbackOption = (selectedType && !options.some(opt => (opt.room_type || '') === selectedType))
                            ? ['<option value="' + esc(selectedType) + '" selected>' + esc(selectedType) + '</option>']
                            : [];
                        const optionHtml = ['<option value="">Select extra bed type</option>']
                            .concat(fallbackOption)
                            .concat(options.map(opt => {
                                const value = opt.room_type || '';
                                const label = (opt.room_type || 'Bed') + (opt.extra_bed ? ' (Extra Bed)' : '');
                                const selected = value === selectedType ? ' selected' : '';
                                return '<option value="' + esc(value) + '"' + selected + '>' + esc(label) + '</option>';
                            }))
                            .join('');

                        const extraUi = ''
                            + '<div class="row g-2 mt-1 p-2 rounded bg-label-primary">'
                            +   '<div class="col-12">'
                            +     '<label class="form-label small mb-1">Extra Bed Type</label>'
                            +     '<select id="' + selectId + '" class="form-select form-select-sm hotel-extra-bed-type" data-hotel-index="' + idx + '" data-room-index="' + roomIdx + '" data-bed-id="' + esc(sourceBedId) + '">' + optionHtml + '</select>'
                            +   '</div>'
                            + '</div>';
                        return base + extraUi;
                    }).join('')
                    : '<div class="small text-muted">No room details</div>';
                const showOptionalPrice = isOptional(h);
                const optionalCheckbox = isOptional(h)
                    ? '<div class="form-check m-0">' +
                        '<input class="form-check-input optional-checkbox" type="checkbox" data-section="hotels" data-index="' + idx + '" ' + (h.selected === true ? 'checked' : '') + '>' +
                        '<label class="form-check-label small mb-0">Select</label>' +
                      '</div>'
                    : '';
                return '<div class="border rounded p-3 mb-3 w-100 overflow-hidden" style="word-break: break-word;">'
                    + '<div class="d-flex justify-content-between align-items-center mb-2"><div class="fw-semibold">'
                    + esc(h.hotel_name || h.name || 'Hotel') + '</div>'
                    + '<div class="d-flex align-items-center gap-2 flex-wrap">' + statusBadge(h) + optionalCheckbox + '</div></div>'
                    + '<div class="row g-2">'
                    + '<div class="col-md-4"><div class="text-muted small">City</div><div>' + esc(h.city || selectedPackageCity || '-') + '</div></div>'
                    + '<div class="col-md-2"><div class="text-muted small">Nights</div><div>' + esc(h.nights || 1) + '</div></div>'
                    + '<div class="col-md-4"><div class="text-muted small">Rooms</div>' + roomsHtml + '</div>'
                    + '<div class="col-md-4"><div class="text-muted small">Optional Price</div><div>' + (showOptionalPrice ? esc(h.optional_price || '-') : '-') + '</div></div>'
                    + '</div></div>';
            }).join('');
            bindOptionalCheckboxes(hotelsList, hotels);
            bindHotelExtraBedInputs();
        }

        function bindHotelExtraBedInputs() {
            hotelsList.querySelectorAll('.hotel-main-room-qty').forEach(inp => {
                inp.addEventListener('input', function () {
                    const hIdx = parseInt(this.getAttribute('data-hotel-index') || '-1', 10);
                    const rIdx = parseInt(this.getAttribute('data-room-index') || '-1', 10);
                    if (hIdx < 0 || rIdx < 0 || !hotels[hIdx] || !Array.isArray(hotels[hIdx].rooms) || !hotels[hIdx].rooms[rIdx]) return;
                    const qty = parseInt(this.value || '1', 10);
                    hotels[hIdx].rooms[rIdx].quantity = qty > 0 ? qty : 1;
                    if (qty <= 0) this.value = '1';
                    syncHidden();
                });
            });

            hotelsList.querySelectorAll('.hotel-extra-bed-type').forEach(sel => {
                sel.addEventListener('change', function () {
                    const hIdx = parseInt(this.getAttribute('data-hotel-index') || '-1', 10);
                    const rIdx = parseInt(this.getAttribute('data-room-index') || '-1', 10);
                    if (hIdx < 0 || rIdx < 0 || !hotels[hIdx] || !Array.isArray(hotels[hIdx].rooms) || !hotels[hIdx].rooms[rIdx]) return;
                    hotels[hIdx].rooms[rIdx].extra_bed_type = this.value || '';
                    syncHidden();
                });
            });
        }

        function renderAttractions() {
            if (!Array.isArray(attractions) || attractions.length === 0) {
                attractionsList.innerHTML = '<div class="text-muted small">No attractions selected</div>';
                return;
            }
            attractionsList.innerHTML = attractions.map((a, idx) => {
                const guide = a.guide || {};
                const languages = Array.isArray(guide.languages) ? guide.languages.join(', ') : '-';
                const showOptionalPrice = isOptional(a);
                const optionalCheckbox = isOptional(a)
                    ? '<div class="form-check m-0">' +
                        '<input class="form-check-input optional-checkbox" type="checkbox" data-section="attractions" data-index="' + idx + '" ' + (a.selected === true ? 'checked' : '') + '>' +
                        '<label class="form-check-label small mb-0">Select</label>' +
                      '</div>'
                    : '';
                return '<div class="border rounded p-3 mb-3 w-100 overflow-hidden" style="word-break: break-word;">'
                    + '<div class="d-flex justify-content-between align-items-center mb-2"><div class="fw-semibold">'
                    + esc(a.name || 'Attraction') + '</div>'
                    + '<div class="d-flex align-items-center gap-2 flex-wrap">' + statusBadge(a) + optionalCheckbox + '</div></div>'
                    + '<div class="row g-2">'
                    + '<div class="col-md-4"><div class="text-muted small">Location</div><div>' + esc(a.location || selectedPackageCity || '-') + '</div></div>'
                    + '<div class="col-md-4"><div class="text-muted small">Guide</div><div>' + esc(guide.name || '-') + '</div><div class="small text-muted">' + esc(languages) + '</div></div>'
                    + '<div class="col-md-4"><div class="text-muted small">Transfer</div><div>' + esc(a.vehicle_name || '-') + ' / ' + esc(a.transfer_type || '-') + '</div></div>'
                    + '<div class="col-md-12"><div class="text-muted small">Pickup -> Dropoff</div><div style="font-size: 0.8rem;">' + esc(a.pickup_name || '-') + ' -> ' + esc(a.dropoff_name || '-') + '</div></div>'
                    + '<div class="col-md-4"><div class="text-muted small">Optional Price</div><div>' + (showOptionalPrice ? esc(a.optional_price || '-') : '-') + '</div></div>'
                    + '</div></div>';
            }).join('');
            bindOptionalCheckboxes(attractionsList, attractions);
        }

        function renderRestaurants() {
            if (!Array.isArray(restaurants) || restaurants.length === 0) {
                restaurantsList.innerHTML = '<div class="text-muted small">No restaurants selected</div>';
                return;
            }
            restaurantsList.innerHTML = restaurants.map((r, idx) => {
                const meals = Array.isArray(r.selected_meals) ? r.selected_meals : [];
                const mealBadges = meals.length
                    ? meals.map(m => formatBadge(m, 'bg-info')).join(' ')
                    : '<span class="text-muted small">No meals</span>';
                const showOptionalPrice = isOptional(r);
                const optionalCheckbox = isOptional(r)
                    ? '<div class="form-check m-0">' +
                        '<input class="form-check-input optional-checkbox" type="checkbox" data-section="restaurants" data-index="' + idx + '" ' + (r.selected === true ? 'checked' : '') + '>' +
                        '<label class="form-check-label small mb-0">Select</label>' +
                      '</div>'
                    : '';
                return '<div class="border rounded p-3 mb-3 w-100 overflow-hidden" style="word-break: break-word;">'
                    + '<div class="d-flex justify-content-between align-items-center mb-2"><div class="fw-semibold">'
                    + esc(r.restaurant_name || r.name || 'Restaurant') + '</div>'
                    + '<div class="d-flex align-items-center gap-2 flex-wrap">' + statusBadge(r) + optionalCheckbox + '</div></div>'
                    + '<div class="row g-2">'
                    + '<div class="col-md-4"><div class="text-muted small">Meals</div><div>' + mealBadges + '</div></div>'
                    + '<div class="col-md-2"><div class="text-muted small">Transfer</div><div>' + esc(r.transfer ? 'Yes' : 'No') + '</div></div>'
                    + '<div class="col-md-3"><div class="text-muted small">Pickup</div><div>' + esc(r.pickup_name || '-') + '</div></div>'
                    + '<div class="col-md-3"><div class="text-muted small">Dropoff</div><div>' + esc(r.dropoff_name || '-') + '</div></div>'
                    + '<div class="col-md-3"><div class="text-muted small">Optional Price</div><div>' + (showOptionalPrice ? esc(r.optional_price || '-') : '-') + '</div></div>'
                    + '</div></div>';
            }).join('');
            bindOptionalCheckboxes(restaurantsList, restaurants);
        }

        function renderGuides() {
            if (!Array.isArray(guides) || guides.length === 0) {
                guidesList.innerHTML = '<div class="text-muted small">No guides selected</div>';
                return;
            }
            guidesList.innerHTML = guides.map((g, idx) => {
                const languages = Array.isArray(g.languages) ? g.languages.join(', ') : '-';
                const showOptionalPrice = isOptional(g);
                const optionalCheckbox = isOptional(g)
                    ? '<div class="form-check m-0">' +
                        '<input class="form-check-input optional-checkbox" type="checkbox" data-section="guides" data-index="' + idx + '" ' + (g.selected === true ? 'checked' : '') + '>' +
                        '<label class="form-check-label small mb-0">Select</label>' +
                      '</div>'
                    : '';
                return '<div class="border rounded p-3 mb-3 w-100 overflow-hidden" style="word-break: break-word;">'
                    + '<div class="d-flex justify-content-between align-items-center mb-2"><div class="fw-semibold">'
                    + esc(g.name || 'Guide') + '</div>'
                    + '<div class="d-flex align-items-center gap-2 flex-wrap">' + statusBadge(g) + optionalCheckbox + '</div></div>'
                    + '<div class="row g-2">'
                    + '<div class="col-md-4"><div class="text-muted small">Languages</div><div>' + esc(languages || '-') + '</div></div>'
                    + '<div class="col-md-4"><div class="text-muted small">Contact</div><div>' + esc(g.contact_no || '-') + '</div></div>'
                    + '<div class="col-md-4"><div class="text-muted small">Duration Type</div><div>' + esc(g.duration_key || g.duration_label || '-') + '</div></div>'
                    + '<div class="col-md-4"><div class="text-muted small">Optional Price</div><div>' + (showOptionalPrice ? esc(g.optional_price || '-') : '-') + '</div></div>'
                    + '</div></div>';
            }).join('');
            bindOptionalCheckboxes(guidesList, guides);
        }

        function renderTransfers() {
            if (!Array.isArray(transfers) || transfers.length === 0) {
                transfersList.innerHTML = '<div class="text-muted small">No transfers selected</div>';
                return;
            }
            transfersList.innerHTML = transfers.map((t, idx) => '<div class="border rounded p-3 mb-3 w-100 overflow-hidden" style="word-break: break-word;">'
                + '<div class="d-flex justify-content-between align-items-center mb-2"><div class="fw-semibold">'
                + esc(t.pickup_label || '-') + ' -> ' + esc(t.dropoff_label || '-') + '</div>'
                + '<div class="d-flex align-items-center gap-2 flex-wrap">' + statusBadge(t) + (isOptional(t)
                    ? '<div class="form-check m-0">'
                        + '<input class="form-check-input optional-checkbox" type="checkbox" data-section="transfers" data-index="' + idx + '" ' + (t.selected === true ? 'checked' : '') + '>'
                        + '<label class="form-check-label small mb-0">Select</label>'
                      + '</div>'
                    : '') + '</div></div>'
                + '<div class="row g-2">'
                + '<div class="col-md-3"><div class="text-muted small">Pickup Label</div><div>' + esc(t.pickup_label || '-') + '</div></div>'
                + '<div class="col-md-3"><div class="text-muted small">Dropoff Label</div><div>' + esc(t.dropoff_label || '-') + '</div></div>'
                + '<div class="col-md-3"><div class="text-muted small">Type</div><div>' + esc(t.pickup_type || '-') + ' -> ' + esc(t.dropoff_type || '-') + '</div></div>'
                + '<div class="col-md-3"><div class="text-muted small">Base Price</div><div>' + esc(t.base_price || '-') + '</div></div>'
                + '<div class="col-md-3"><div class="text-muted small">Optional Price</div><div>' + (isOptional(t) ? esc(t.optional_price || '-') : '-') + '</div></div>'
                + '</div></div>').join('');
            bindOptionalCheckboxes(transfersList, transfers);
        }

        function renderArrivalDeparture() {
            const arrivalSummary = document.getElementById('arrivalSummary');
            const departureSummary = document.getElementById('departureSummary');
            const arrivalEnabled = arrivalData && arrivalData.enabled;
            const departureEnabled = departureData && departureData.enabled;
            const arrivalBadge = arrivalEnabled ? formatBadge('Enabled', 'bg-success') : formatBadge('Disabled', 'bg-secondary');
            const departureBadge = departureEnabled ? formatBadge('Enabled', 'bg-success') : formatBadge('Disabled', 'bg-secondary');
            const arrivalVehicles = Array.isArray(arrivalData.vehicles) ? arrivalData.vehicles : [];
            const departureVehicles = Array.isArray(departureData.vehicles) ? departureData.vehicles : [];

            arrivalSummary.innerHTML = '<div class="border rounded p-3 mb-2">'
                + '<div class="d-flex justify-content-between align-items-center mb-2"><div class="fw-semibold">Arrival</div>'
                + '<div class="d-flex align-items-center gap-2 flex-wrap">' + arrivalBadge + '</div></div>'
                + '<div class="row g-2">'
                + '<div class="col-6"><div class="text-muted small">Pickup Port</div><div>' + esc(arrivalData.pickup_port_id || '-') + '</div></div>'
                + '<div class="col-6"><div class="text-muted small">Dropoff Hotel</div><div>' + esc(arrivalData.dropoff_hotel_id || '-') + '</div></div>'
                + '<div class="col-12"><div class="text-muted small">Vehicles</div><div class="small">' + (arrivalVehicles.map(v => esc(v.vehicle_name || v.vehicle_id)).join(', ') || '-') + '</div></div>'
                + '</div></div>';

            departureSummary.innerHTML = '<div class="border rounded p-3 mb-2">'
                + '<div class="d-flex justify-content-between align-items-center mb-2"><div class="fw-semibold">Departure</div>'
                + '<div class="d-flex align-items-center gap-2 flex-wrap">' + departureBadge + '</div></div>'
                + '<div class="row g-2">'
                + '<div class="col-6"><div class="text-muted small">Pickup Hotel</div><div>' + esc(departureData.pickup_hotel_id || '-') + '</div></div>'
                + '<div class="col-6"><div class="text-muted small">Dropoff Port</div><div>' + esc(departureData.dropoff_port_id || '-') + '</div></div>'
                + '<div class="col-12"><div class="text-muted small">Vehicles</div><div class="small">' + (departureVehicles.map(v => esc(v.vehicle_name || v.vehicle_id)).join(', ') || '-') + '</div></div>'
                + '</div></div>';
        }

        function syncHidden() {
            const tourStartDate = startDateEl && startDateEl.value ? startDateEl.value : null;
            arrivalData = {
                enabled: document.getElementById('arrivalEnabled').value === '1',
                pickup_port_id: document.getElementById('arrivalPickupPortId').value || null,
                dropoff_hotel_id: document.getElementById('arrivalDropoffHotelId').value || null,
                vehicles: Array.isArray(arrivalData.vehicles) ? arrivalData.vehicles : [],
                tour_start_date: tourStartDate
            };
            departureData = {
                enabled: document.getElementById('departureEnabled').value === '1',
                pickup_hotel_id: document.getElementById('departurePickupHotelId').value || null,
                dropoff_port_id: document.getElementById('departureDropoffPortId').value || null,
                vehicles: Array.isArray(departureData.vehicles) ? departureData.vehicles : [],
                tour_start_date: tourStartDate
            };

            const hotelsPayload = (hotels || [])
                .filter(h => h && (h.compulsory === true || h.selected === true))
                .map(h => ({ ...h, tour_start_date: tourStartDate }));
            const attractionsPayload = (attractions || [])
                .filter(a => a && (a.compulsory === true || a.selected === true))
                .map(a => ({ ...a, tour_start_date: tourStartDate }));
            const guidesPayload = (guides || [])
                .filter(g => g && (g.compulsory === true || g.selected === true))
                .map(g => ({ ...g, tour_start_date: tourStartDate }));
            const restaurantsPayload = (restaurants || [])
                .filter(r => r && (r.compulsory === true || r.selected === true))
                .map(r => ({ ...r, tour_start_date: tourStartDate }));
            const transfersPayload = (transfers || [])
                .filter(t => t && (t.compulsory === true || t.selected === true))
                .map(t => ({ ...t, tour_start_date: tourStartDate }));

            document.getElementById('selected_hotels_input').value = JSON.stringify(hotelsPayload);
            document.getElementById('selected_attractions_input').value = JSON.stringify(attractionsPayload);
            document.getElementById('selected_guides_input').value = JSON.stringify(guidesPayload);
            document.getElementById('selected_restaurants_input').value = JSON.stringify(restaurantsPayload);
            document.getElementById('arrival_data_input').value = JSON.stringify(arrivalData || {});
            document.getElementById('departure_data_input').value = JSON.stringify(departureData || {});
            document.getElementById('transfer_data_input').value = JSON.stringify(transfersPayload);
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
            bedOptionsBySourceBedId = {};
            packageDetailsSection.style.display = 'none';
            createBookingBtn.disabled = true;
            selectedPackageIdInput.value = '';
            renderHotels();
            renderAttractions();
            renderGuides();
            renderRestaurants();
            renderTransfers();
            renderArrivalDeparture();
            document.getElementById('arrivalEnabled').value = '0';
            document.getElementById('arrivalPickupPortId').value = '';
            document.getElementById('arrivalDropoffHotelId').value = '';
            document.getElementById('departureEnabled').value = '0';
            document.getElementById('departurePickupHotelId').value = '';
            document.getElementById('departureDropoffPortId').value = '';
            syncHidden();
        }

        async function applyPackageData(pkg) {
            hotels = initOptionalSelections(Array.isArray(pkg.selected_hotels) ? pkg.selected_hotels : []);
            await ensureBedOptionsLoaded(hotels);
            attractions = initOptionalSelections(Array.isArray(pkg.selected_attractions) ? pkg.selected_attractions : []);
            guides = initOptionalSelections(Array.isArray(pkg.selected_guides) ? pkg.selected_guides : []);
            restaurants = initOptionalSelections(Array.isArray(pkg.selected_restaurants) ? pkg.selected_restaurants : []);
            arrivalData = pkg.arrival_data || {};
            departureData = pkg.departure_data || {};
            transfers = initOptionalSelections(Array.isArray(pkg.transfer_data) ? pkg.transfer_data : []);
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
            renderArrivalDeparture();
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
            await applyPackageData(data.package);
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
 