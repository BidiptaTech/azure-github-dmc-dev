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
                        <div class="col-md-4">
                            <label class="form-label">Travel Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="travel_start_date" name="travel_start_date" required min="{{ date('Y-m-d') }}" value="{{ old('travel_start_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Pax <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="pax_count" name="pax_count" min="1" value="{{ old('pax_count', 2) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Agency</label>
                            <select id="agency_id" name="agency_id" class="form-select" onchange="loadAgentsByAgency(this.value, null)">
                                <option value="">Select Agency</option>
                                @foreach($agencies as $a)
                                    <option value="{{ $a->agency_id }}">{{ $a->agency_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
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
                                <option value="">Select travel start date to load packages</option>
                            </select>
                            <small class="text-muted d-block mt-1" id="packageFilterMessage"></small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Travel End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="travel_end_date" name="travel_end_date" required min="{{ date('Y-m-d') }}" value="{{ old('travel_end_date') }}" title="Defaults from package length; you may extend later but not shorten below that">
                        </div>
                    </div>
                </div>
            </div>

            <div id="packageDetailsSection" style="display:none;">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card h-100">
                            <div class="card-header bg-light"><h6 class="mb-0">Hotels</h6></div>
                            <div class="card-body">
                                <div id="hotelsList"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12" id="dayWiseItineraryWrap" style="display:none;">
                        <div class="card h-100 border-primary border-opacity-25">
                            <div class="card-header bg-light"><h6 class="mb-0"><i class="ri-calendar-todo-line me-2 text-primary"></i>Day-wise itinerary</h6></div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">Hotels stay editable in the section above. Below, each accordion is a tour day that has at least one booking. Open a day to see hotel nights, transfers, attractions, and restaurants for that day only.</p>
                                <div id="dayWiseItineraryList"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 legacy-attr-rest-wrap">
                        <div class="card h-100">
                            <div class="card-header bg-light"><h6 class="mb-0">Attractions</h6></div>
                            <div class="card-body">
                                <div id="attractionsList"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 legacy-attr-rest-wrap">
                        <div class="card h-100">
                            <div class="card-header bg-light"><h6 class="mb-0">Restaurants</h6></div>
                            <div class="card-body">
                                <div id="restaurantsList"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-12">
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
                    <div class="col-12">
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
                            <div class="card-header bg-light"><h6 class="mb-0">Price Summary</h6></div>
                            <div class="card-body">
                                <div id="pricingSummary"></div>
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
            <input type="hidden" name="hotel_booking_dates" id="hotel_booking_dates_input">
            <input type="hidden" name="selected_attractions" id="selected_attractions_input">
            <input type="hidden" name="selected_guides" id="selected_guides_input">
            <input type="hidden" name="selected_restaurants" id="selected_restaurants_input">
            <input type="hidden" name="arrival_data" id="arrival_data_input">
            <input type="hidden" name="departure_data" id="departure_data_input">
            <input type="hidden" name="transfer_data" id="transfer_data_input">
            <input type="hidden" name="supplementary_data" id="supplementary_data_input">
            <input type="hidden" name="available_addons" id="available_addons_input">
            <input type="hidden" name="price_data" id="price_data_input">

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" id="createBookingBtn" disabled>Create Booking</button>
            </div>
        </form>
    </div>
</div>

<style>
    .hotel-date-strip { display: flex; flex-wrap: wrap; gap: 6px; }
    .hotel-date-box {
        min-width: 52px;
        border: 1px solid #d8dee8;
        border-radius: 8px;
        background: #fff;
        padding: 6px 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.15s ease;
        user-select: none;
    }
    .hotel-date-box:hover { border-color: #7aa7ff; background: #f5f9ff; }
    .hotel-date-box.selected { border-color: #3f7cff; background: #3f7cff; color: #fff; }
    .hotel-date-box .day { font-size: 13px; font-weight: 700; line-height: 1; }
    .hotel-date-box .wk { font-size: 10px; line-height: 1.1; margin-top: 3px; opacity: 0.85; }
    .day-wise-itinerary-accordion .accordion-button { font-size: 1rem; box-shadow: none; }
    .day-wise-itinerary-accordion .accordion-button:not(.collapsed) { background-color: rgba(105, 108, 255, 0.08); color: inherit; }
    .day-wise-itinerary-accordion .accordion-body { background: #fff; }
    .day-itinerary-section-title {
        font-size: 0.72rem;
        letter-spacing: 0.04em;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e8ecf1;
    }
    .day-itinerary-section:last-child .day-itinerary-section-body > *:last-child { margin-bottom: 0 !important; }
</style>

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
        let selectedHotelDates = {};
        let selectedPackagePriceData = {};
        let selectedPackageType = '';
        let loadedPackageDurationDays = 1;
        /** Minimum allowed travel end date (Y-m-d) once a package is chosen — package-based last day; user may extend only. */
        let packageMinEndDateYmd = '';
        let lastPricingTotals = { total_price: 0, final_price: 0, markup_type: 'flat', markup_amount: 0 };

        function ceilToFive(n) {
            const num = parseFloat(n);
            if (!isFinite(num) || isNaN(num)) return 0;
            return Math.ceil(num / 5) * 5;
        }

        function computeFinalPrice(total, markupType, markupAmount) {
            const t = parseFloat(total) || 0;
            const amt = parseFloat(markupAmount) || 0;
            if (!amt) return t;
            if (String(markupType || '').toLowerCase() === 'percentage') {
                return t + (t * amt / 100);
            }
            return t + amt;
        }

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
        const restaurantsList = document.getElementById('restaurantsList');
        const pricingSummary = document.getElementById('pricingSummary');

        const startDateEl = document.getElementById('travel_start_date');
        const endDateEl = document.getElementById('travel_end_date');
        const travelEndDateHtmlMin = endDateEl ? (endDateEl.getAttribute('min') || '') : '';
        const paxEl = document.getElementById('pax_count');
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        /** End date = last day of tour (inclusive), same rule as server diffInDays + 1 */
        function lastTourDayYmdFromStart(ymd, inclusiveDays) {
            const parts = String(ymd || '').split('-').map(x => parseInt(x, 10));
            if (parts.length !== 3 || parts.some(n => isNaN(n))) return '';
            const d = parseInt(inclusiveDays, 10);
            if (!isFinite(d) || d < 1) return '';
            const dt = new Date(parts[0], parts[1] - 1, parts[2]);
            dt.setDate(dt.getDate() + (d - 1));
            const y = dt.getFullYear();
            const m = String(dt.getMonth() + 1).padStart(2, '0');
            const day = String(dt.getDate()).padStart(2, '0');
            return y + '-' + m + '-' + day;
        }

        function syncEndDateFromPackageDuration(durationDays) {
            if (!startDateEl.value) return;
            const lastDay = lastTourDayYmdFromStart(startDateEl.value, durationDays);
            if (lastDay) {
                packageMinEndDateYmd = lastDay;
                endDateEl.min = packageMinEndDateYmd;
                endDateEl.value = lastDay;
            }
        }

        function clampTravelEndDateToMinimum() {
            if (!packageMinEndDateYmd || !endDateEl.value) return;
            const cur = endDateEl.value;
            if (cur < packageMinEndDateYmd) {
                endDateEl.value = packageMinEndDateYmd;
            }
        }

        /**
         * Departure rows tagged with the previous last tour day move to the new calendar last day.
         * Items sharing the same maximum `day` are treated as the final departure segment(s).
         */
        function syncDepartureDaysToExtendedTour() {
            const lastDay = getTourDurationDaysInclusive();
            if (!departureData || typeof departureData !== 'object') return;
            if (!Array.isArray(departureData.items) || !departureData.items.length) return;

            let maxD = 0;
            departureData.items.forEach(it => {
                const d = parseInt(it && it.day, 10);
                if (!isNaN(d) && d > maxD) maxD = d;
            });
            const pkgBase = parseInt(loadedPackageDurationDays, 10) || 1;
            if (maxD < 1) maxD = pkgBase;

            departureData.items.forEach(it => {
                if (!it || typeof it !== 'object') return;
                const d = parseInt(it.day, 10);
                if (!isNaN(d) && d === maxD) {
                    it.day = lastDay;
                }
            });
        }

        /** Placeholder accordion rows for extension nights after the packaged duration with no services. */
        function appendExtensionTourDayPlaceholders(groups) {
            const lastDay = getTourDurationDaysInclusive();
            const pkgBase = parseInt(loadedPackageDurationDays, 10) || 1;
            if (!lastDay || pkgBase >= lastDay) return groups;

            const existing = new Set(groups.map(g => g.day));
            const merged = groups.slice();
            for (let d = pkgBase + 1; d <= lastDay; d++) {
                if (!existing.has(d)) {
                    merged.push({
                        day: d,
                        html: '<div class="alert alert-light border text-muted small mb-0">No packaged services on this tour day.</div>'
                    });
                    existing.add(d);
                }
            }
            merged.sort((a, b) => a.day - b.day);
            return merged;
        }

        function onTravelEndDateChanged() {
            clampTravelEndDateToMinimum();
            syncDepartureDaysToExtendedTour();
            renderAllSections();
            syncHidden();
        }

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
            if (item && item.addon === true) {
                return formatBadge('Add-on', 'bg-info');
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

        function initSectionSelections(list, sectionKey) {
            if (!Array.isArray(list)) return [];
            const nextList = list.map(item => {
                const next = item || {};
                if (next.compulsory === true) {
                    next.selected = true;
                } else {
                    next.selected = false;
                }
                return next;
            });

            const optionalIndexes = [];
            nextList.forEach((item, index) => {
                if (item && item.optional === true) optionalIndexes.push(index);
            });

            if (optionalIndexes.length > 0) {
                let minIndex = optionalIndexes[0];
                let minTotal = sectionServiceTotal(sectionKey, nextList[minIndex]);
                for (let i = 1; i < optionalIndexes.length; i++) {
                    const idx = optionalIndexes[i];
                    const total = sectionServiceTotal(sectionKey, nextList[idx]);
                    if (total < minTotal) {
                        minTotal = total;
                        minIndex = idx;
                    }
                }
                nextList[minIndex].selected = true;
            }

            return nextList;
        }

        function bindServiceBookingPaxInputs(container, listRef, sectionKey) {
            container.querySelectorAll('.service-booking-pax[data-section="' + sectionKey + '"]').forEach(inp => {
                inp.addEventListener('input', function () {
                    const index = parseInt(this.getAttribute('data-index') || '-1', 10);
                    if (index < 0 || !listRef[index]) return;
                    listRef[index].booking_pax = Math.max(0, parseInt(this.value || '0', 10) || 0);
                    if (sectionKey === 'attractions') {
                        if (useDayWiseItineraryLayout()) renderDayWiseItinerary();
                        else renderAttractions();
                    } else if (sectionKey === 'restaurants') {
                        if (useDayWiseItineraryLayout()) renderDayWiseItinerary();
                        else renderRestaurants();
                    }
                    renderPricingSummary();
                    syncHidden();
                });
            });
        }

        function bindSelectableCheckboxes(container, listRef, sectionKey) {
            container.querySelectorAll('.service-select-checkbox').forEach(cb => {
                cb.addEventListener('change', function () {
                    const index = parseInt(this.getAttribute('data-index') || '-1', 10);
                    const mode = this.getAttribute('data-mode') || '';
                    if (!Array.isArray(listRef) || index < 0 || !listRef[index]) return;

                    if (mode === 'optional') {
                        if (this.checked !== true) {
                            this.checked = true;
                            return;
                        }
                        listRef.forEach((item, idx) => {
                            if (!item) return;
                            if (item.optional === true) item.selected = idx === index;
                        });
                        renderAllSections();
                    } else if (mode === 'addon') {
                        listRef[index].selected = this.checked === true;
                        renderPricingSummary();
                    }

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
                        else if (section === 'restaurants') handleRestaurantCompulsory(idx);
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

        function renderAllSections() {
            renderHotels();
            const useDay = useDayWiseItineraryLayout();
            document.querySelectorAll('.legacy-attr-rest-wrap').forEach(w => { w.style.display = useDay ? 'none' : ''; });
            const dw = document.getElementById('dayWiseItineraryWrap');
            if (dw) dw.style.display = useDay ? '' : 'none';
            if (useDay) renderDayWiseItinerary();
            else {
                renderAttractions();
                renderRestaurants();
            }
            renderArrivalDeparture();
            renderPricingSummary();
        }

        function sectionServiceTotal(sectionKey, item) {
            if (sectionKey === 'hotels') return hotelTotal(item);
            if (sectionKey === 'attractions') return attractionTotal(item);
            if (sectionKey === 'restaurants') return restaurantTotal(item);
            return 0;
        }

        function renderHotels() {
            if (!Array.isArray(hotels) || hotels.length === 0) {
                hotelsList.innerHTML = '<div class="text-muted small">No hotels selected</div>';
                return;
            }
            hotelsList.innerHTML = hotels.map((h, idx) => {
                const rooms = Array.isArray(h.rooms) ? h.rooms : [];
                const nightBreakdown = getHotelNightBreakdown(h, idx);
                const availableDates = getTravelDateRange();
                const hotelDateKey = getHotelDateKey(h, idx);
                const maxAllowedNights = getHotelMaxSelectableNights(h, availableDates.length);
                const dateStripHtml = availableDates.length === 0
                    ? '<div class="small text-muted">Select travel start/end date to choose hotel stay dates.</div>'
                    : '<div class="hotel-date-strip">'
                        + availableDates.map(dateStr => {
                            const dt = parseIsoDate(dateStr);
                            if (!dt) return '';
                            const dayNum = dt.getDate();
                            const wk = dayNames[dt.getDay()].slice(0, 3);
                            const isSelected = nightBreakdown.selected_dates.includes(dateStr);
                            return '<div class="hotel-date-box ' + (isSelected ? 'selected' : '') + '" data-hotel-key="' + esc(hotelDateKey) + '" data-date="' + esc(dateStr) + '" data-max="' + esc(maxAllowedNights) + '">'
                                + '<div class="day">' + esc(dayNum) + '</div>'
                                + '<div class="wk">' + esc(wk) + '</div>'
                                + '</div>';
                        }).join('')
                        + '</div>'
                        + '<div class="small text-muted mt-1">Selected tour day dates: ' + esc(nightBreakdown.selected_dates.length) + ' / ' + esc(maxAllowedNights) + '</div>';
                const roomsHtml = rooms.length
                    ? rooms.map(r => {
                        return '<div class="d-flex align-items-center gap-2 mt-1 py-1 border-bottom">'
                            + '<div class="small fw-semibold">' + esc(r.room_type_name || 'Room') + '</div>'
                            + '</div>';
                    }).join('')
                    : '<div class="small text-muted">No room details</div>';
                const isSelectable = isOptional(h) || !!(h && h.addon === true);
                const selectMode = isOptional(h) ? 'optional' : ((h && h.addon === true) ? 'addon' : '');
                const optionalCheckbox = isSelectable
                    ? '<div class="form-check m-0">' +
                        '<input class="form-check-input service-select-checkbox" type="checkbox" data-section="hotels" data-mode="' + esc(selectMode) + '" data-index="' + idx + '" ' + (h.selected === true ? 'checked' : '') + '>' +
                        '<label class="form-check-label small mb-0">Select</label>' +
                      '</div>'
                    : '';

                const numRooms = getHotelNumRooms(h);
                const numSingle = getHotelSingleRooms(h);
                const allowTriple = hotelAllowsTriple(h);
                const numTriple = allowTriple ? getHotelTripleRooms(h) : 0;
                const numDouble = getHotelDoubleRooms(h);
                const showOccupancy = numRooms > 1 || allowTriple;
                const tripleHtml = allowTriple
                    ? '<div class="col-md-1"><label class="form-label small text-muted mb-1">Triple</label>'
                        + '<input type="number" min="0" max="' + esc(numRooms) + '" step="1" class="form-control form-control-sm hotel-triple-rooms" data-index="' + idx + '" value="' + esc(numTriple) + '"></div>'
                    : '';
                const occupancyHtml = showOccupancy
                    ? '<div class="col-md-1"><label class="form-label small text-muted mb-1">Single</label>'
                        + '<input type="number" min="0" max="' + esc(numRooms) + '" step="1" class="form-control form-control-sm hotel-single-rooms" data-index="' + idx + '" value="' + esc(numSingle) + '"></div>'
                      + '<div class="col-md-1"><label class="form-label small text-muted mb-1">Double</label>'
                        + '<input type="number" min="0" max="' + esc(numRooms) + '" step="1" class="form-control form-control-sm hotel-double-rooms" data-index="' + idx + '" value="' + esc(numDouble) + '"></div>'
                      + tripleHtml
                    : '';

                const stayBounds = getHotelStayTourDayRangeBounds(h);
                const packageDaysRow = (stayBounds && stayBounds.from >= 1)
                    ? '<div class="col-md-12"><div class="text-muted small">Package tour days (stay)</div><div>Day ' + esc(stayBounds.from) + ' – Day ' + esc(stayBounds.to) + '</div></div>'
                    : '';

                return '<div class="border rounded p-3 mb-3 w-100 overflow-hidden" style="word-break: break-word;">'
                    + '<div class="d-flex justify-content-between align-items-center mb-2"><div class="fw-semibold">'
                    + esc(h.hotel_name || h.name || 'Hotel') + '</div>'
                    + '<div class="d-flex align-items-center gap-2 flex-wrap">' + statusBadge(h) + optionalCheckbox + '</div></div>'
                    + '<div class="row g-2">'
                    + '<div class="col-md-2"><div class="text-muted small">City</div><div>' + esc(h.city || selectedPackageCity || '-') + '</div></div>'
                    + '<div class="col-md-2"><div class="text-muted small">Stay dates</div><div>' + esc(nightBreakdown.nights) + '</div></div>'
                    + packageDaysRow
                    + '<div class="col-md-2"><label class="form-label small text-muted mb-1">No. of Rooms</label>'
                    + '<input type="number" min="1" max="' + esc(getMaxHotelRooms()) + '" step="1" class="form-control form-control-sm hotel-num-rooms" data-index="' + idx + '" value="' + esc(numRooms) + '">'
                    + '<div class="form-text small">Max ' + esc(getMaxHotelRooms()) + ' (pax)</div></div>'
                    + occupancyHtml
                    + '<div class="col-md-2"><div class="text-muted small">Total Price</div><div>' + esc(money(hotelTotal(h, idx))) + '</div></div>'
                    + '<div class="col-md-12"><div class="text-muted small">Stay Dates</div>' + dateStripHtml + '</div>'
                    + '<div class="col-md-12"><div class="text-muted small">Room Types</div>' + roomsHtml + '</div>'
                    + '</div></div>';
            }).join('');
            bindSelectableCheckboxes(hotelsList, hotels, 'hotels');
            bindHotelDateBoxes();
            bindHotelRoomInputs();
        }

        function bindHotelDateBoxes() {
            hotelsList.querySelectorAll('.hotel-date-box').forEach(box => {
                box.addEventListener('click', function () {
                    const key = this.getAttribute('data-hotel-key') || '';
                    const dateStr = this.getAttribute('data-date') || '';
                    const maxAllowed = Math.max(0, parseInt(this.getAttribute('data-max') || '0', 10) || 0);
                    if (!key || !dateStr || maxAllowed <= 0) return;

                    const availableDates = getTravelDateRange();
                    let selected = Array.isArray(selectedHotelDates[key]) ? selectedHotelDates[key].slice() : [];
                    selected = orderedUniqueDates(selected, availableDates);
                    const isSelected = selected.includes(dateStr);

                    if (isSelected) {
                        selected = selected.filter(d => d !== dateStr);
                    } else {
                        if (selected.length >= maxAllowed) {
                            return;
                        }
                        selected.push(dateStr);
                        selected = orderedUniqueDates(selected, availableDates);
                    }

                    selectedHotelDates[key] = selected;
                    hotels.forEach((h, idx) => {
                        if (getHotelDateKey(h, idx) === key) {
                            h.hotel_booking_dates = selected.slice();
                        }
                    });

                    renderHotels();
                    renderPricingSummary();
                    syncHidden();
                });
            });
        }

        /**
         * Wire up the per-hotel "No. of Rooms / Single / Double" inputs.
         * - Changing total rooms clamps single rooms and recomputes double = total - single.
         * - Changing single recomputes double = total - single (and vice versa) so the two
         *   counts always sum to the total rooms (rooms must be exactly one occupancy type).
         * - Re-renders the hotel card so the occupancy inputs appear/disappear when total
         *   crosses 1.
         */
        function bindHotelRoomInputs() {
            hotelsList.querySelectorAll('.hotel-num-rooms').forEach(inp => {
                inp.addEventListener('change', function () {
                    const idx = parseInt(this.getAttribute('data-index') || '-1', 10);
                    if (idx < 0 || !hotels[idx]) return;
                    const cap = getMaxHotelRooms();
                    let v = parseInt(this.value || '1', 10);
                    if (isNaN(v) || v < 1) v = 1;
                    if (v > cap) v = cap;
                    // Keep current single, derive double = total - single, then auto-top-up.
                    const oldSingle = parseInt(hotels[idx].num_single_rooms || 0, 10) || 0;
                    hotels[idx].num_rooms = v;
                    hotels[idx].num_single_rooms = Math.min(Math.max(0, oldSingle), v);
                    applyHotelRoomConsistency(hotels[idx]);
                    renderHotels();
                    renderPricingSummary();
                    syncHidden();
                });
            });

            hotelsList.querySelectorAll('.hotel-single-rooms').forEach(inp => {
                inp.addEventListener('change', function () {
                    const idx = parseInt(this.getAttribute('data-index') || '-1', 10);
                    if (idx < 0 || !hotels[idx]) return;
                    const total = getHotelNumRooms(hotels[idx]);
                    let v = parseInt(this.value || '0', 10);
                    if (isNaN(v) || v < 0) v = 0;
                    if (v > total) v = total;
                    hotels[idx].num_single_rooms = v;
                    // Keep total fixed; helper resets double = total - single, then tops up
                    // additional double rooms (growing total) if pax can't be seated.
                    applyHotelRoomConsistency(hotels[idx]);
                    renderHotels();
                    renderPricingSummary();
                    syncHidden();
                });
            });

            hotelsList.querySelectorAll('.hotel-double-rooms').forEach(inp => {
                inp.addEventListener('change', function () {
                    const idx = parseInt(this.getAttribute('data-index') || '-1', 10);
                    if (idx < 0 || !hotels[idx]) return;
                    const total = getHotelNumRooms(hotels[idx]);
                    const trip = getHotelTripleRooms(hotels[idx]);
                    let v = parseInt(this.value || '0', 10);
                    if (isNaN(v) || v < 0) v = 0;
                    if (v > total) v = total;
                    // Helper recomputes double from single+triple, so encode the desired double
                    // count by adjusting single = total - desired_double - triple.
                    hotels[idx].num_single_rooms = Math.max(0, total - v - trip);
                    applyHotelRoomConsistency(hotels[idx]);
                    renderHotels();
                    renderPricingSummary();
                    syncHidden();
                });
            });

            hotelsList.querySelectorAll('.hotel-triple-rooms').forEach(inp => {
                inp.addEventListener('change', function () {
                    const idx = parseInt(this.getAttribute('data-index') || '-1', 10);
                    if (idx < 0 || !hotels[idx]) return;
                    const total = getHotelNumRooms(hotels[idx]);
                    const single = getHotelSingleRooms(hotels[idx]);
                    let v = parseInt(this.value || '0', 10);
                    if (isNaN(v) || v < 0) v = 0;
                    const maxTriple = Math.max(0, total - single);
                    if (v > maxTriple) v = maxTriple;
                    hotels[idx].num_triple_rooms = v;
                    applyHotelRoomConsistency(hotels[idx]);
                    renderHotels();
                    renderPricingSummary();
                    syncHidden();
                });
            });
        }

        function buildAttractionCardMarkup(a, idx, opts) {
            opts = opts || {};
            const hideDayBadge = !!opts.hideDayBadge;
            const card = a || {};
            const guide = card.guide || {};
            const languages = Array.isArray(guide.languages) ? guide.languages.join(', ') : '-';
            const bp = getResolvedServiceBookingPax(card);
            const isSelectable = isOptional(card) || !!(card && card.addon === true);
            const selectMode = isOptional(card) ? 'optional' : ((card && card.addon === true) ? 'addon' : '');
            const optionalCheckbox = isSelectable
                ? '<div class="form-check m-0">' +
                    '<input class="form-check-input service-select-checkbox" type="checkbox" data-section="attractions" data-mode="' + esc(selectMode) + '" data-index="' + idx + '" ' + (card.selected === true ? 'checked' : '') + '>' +
                    '<label class="form-check-label small mb-0">Select</label>' +
                  '</div>'
                : '';
            const hasTransfer = !!card.transfer;
            const transferBlock = hasTransfer
                ? '<div class="col-md-4"><div class="text-muted small">Transfer</div><div>' + esc(card.vehicle_name || '-') + ' / ' + esc(card.transfer_type || '-') + '</div></div>'
                  + '<div class="col-md-12"><div class="text-muted small">Pickup -> Dropoff</div><div style="font-size: 0.8rem;">' + esc(card.pickup_name || '-') + ' -> ' + esc(card.dropoff_name || '-') + '</div></div>'
                : '<div class="col-md-4"><div class="text-muted small">Transfer</div><div>No</div></div>';
            const dayBadge = hideDayBadge ? '' : ((card.day != null && String(card.day).trim() !== '')
                ? '<span class="badge bg-light text-dark border ms-2">Pkg day ' + esc(card.day) + '</span>'
                : '');
            return '<div class="border rounded p-3 mb-3 w-100 overflow-hidden" style="word-break: break-word;">'
                + '<div class="d-flex justify-content-between align-items-center mb-2"><div class="fw-semibold">'
                + esc(card.name || 'Attraction') + dayBadge + '</div>'
                + '<div class="d-flex align-items-center gap-2 flex-wrap">' + statusBadge(card) + optionalCheckbox + '</div></div>'
                + '<div class="row g-2">'
                + '<div class="col-md-4"><div class="text-muted small">Location</div><div>' + esc(card.location || selectedPackageCity || '-') + '</div></div>'
                + '<div class="col-md-4"><div class="text-muted small">Guide</div><div>' + esc(guide.name || '-') + '</div><div class="small text-muted">' + esc(languages) + '</div></div>'
                + transferBlock
                + '<div class="col-md-2"><label class="form-label small mb-1">Pax</label><input type="number" min="0" step="1" class="form-control form-control-sm service-booking-pax" data-section="attractions" data-index="' + idx + '" value="' + esc(bp) + '"></div>'
                + '<div class="col-md-6"><div class="text-muted small">Total Price</div><div>' + esc(money(attractionTotal(card))) + '</div></div>'
                + '</div></div>';
        }

        function buildRestaurantCardMarkup(r, idx, opts) {
            opts = opts || {};
            const hideDayBadge = !!opts.hideDayBadge;
            const card = r || {};
            const mealBadges = card.meal_type_label ? formatBadge(card.meal_type_label, 'bg-info') : '';
            const bp = getResolvedServiceBookingPax(card);
            const isSelectable = isOptional(card) || !!(card && card.addon === true);
            const selectMode = isOptional(card) ? 'optional' : ((card && card.addon === true) ? 'addon' : '');
            const optionalCheckbox = isSelectable
                ? '<div class="form-check m-0">' +
                    '<input class="form-check-input service-select-checkbox" type="checkbox" data-section="restaurants" data-mode="' + esc(selectMode) + '" data-index="' + idx + '" ' + (card.selected === true ? 'checked' : '') + '>' +
                    '<label class="form-check-label small mb-0">Select</label>' +
                  '</div>'
                : '';
            const hasTransfer = !!card.transfer;
            const transferBlock = hasTransfer
                ? '<div class="col-md-2"><div class="text-muted small">Transfer</div><div>Yes</div></div>'
                  + '<div class="col-md-3"><div class="text-muted small">Pickup</div><div>' + esc(card.pickup_name || '-') + '</div></div>'
                  + '<div class="col-md-3"><div class="text-muted small">Dropoff</div><div>' + esc(card.dropoff_name || '-') + '</div></div>'
                : '<div class="col-md-2"><div class="text-muted small">Transfer</div><div>No</div></div>';
            const dayBadge = hideDayBadge ? '' : ((card.day != null && String(card.day).trim() !== '')
                ? '<span class="badge bg-light text-dark border ms-2">Pkg day ' + esc(card.day) + '</span>'
                : '');
            return '<div class="border rounded p-3 mb-3 w-100 overflow-hidden" style="word-break: break-word;">'
                + '<div class="d-flex justify-content-between align-items-center mb-2"><div class="fw-semibold">'
                + esc(card.restaurant_name || card.name || 'Restaurant') + dayBadge + '</div>'
                + '<div class="d-flex align-items-center gap-2 flex-wrap">' + statusBadge(card) + optionalCheckbox + '</div></div>'
                + '<div class="row g-2">'
                + '<div class="col-md-4"><div class="text-muted small">Meals</div><div>' + mealBadges + '</div></div>'
                + transferBlock
                + '<div class="col-md-2"><label class="form-label small mb-1">Pax</label><input type="number" min="0" step="1" class="form-control form-control-sm service-booking-pax" data-section="restaurants" data-index="' + idx + '" value="' + esc(bp) + '"></div>'
                + '<div class="col-md-6"><div class="text-muted small">Total Price</div><div>' + esc(money(restaurantTotal(card))) + '</div></div>'
                + '</div></div>';
        }

        function buildArrivalDaySnippet(item) {
            if (!item || typeof item !== 'object') return '';
            const veh = Array.isArray(item.vehicles) ? item.vehicles : [];
            const vehText = veh.length
                ? veh.map(v => esc(v.vehicle_name || v.vehicle_id || 'Vehicle') + ' (' + esc((v.selected_transfer_type || 'private')) + ')').join(', ')
                : '—';
            return '<div class="border border-info border-opacity-50 rounded p-2 mb-2 bg-info bg-opacity-10 small">'
                + '<div class="fw-semibold text-info mb-1"><i class="ri-flight-land-line me-1"></i>Arrival transfer</div>'
                + '<div>' + esc(item.pickup_port_name || item.pickup_port_id || 'Port') + ' → ' + esc(item.dropoff_hotel_name || item.dropoff_hotel_id || 'Hotel') + '</div>'
                + '<div class="text-muted mt-1">' + vehText + '</div>'
                + '<div class="text-muted mt-1">Adjust vehicle qty/pax under <strong>Arrival Data</strong> below.</div>'
                + '</div>';
        }

        function buildDepartureDaySnippet(item) {
            if (!item || typeof item !== 'object') return '';
            const veh = Array.isArray(item.vehicles) ? item.vehicles : [];
            const vehText = veh.length
                ? veh.map(v => esc(v.vehicle_name || v.vehicle_id || 'Vehicle') + ' (' + esc((v.selected_transfer_type || 'private')) + ')').join(', ')
                : '—';
            return '<div class="border border-warning border-opacity-50 rounded p-2 mb-2 bg-warning bg-opacity-10 small">'
                + '<div class="fw-semibold text-warning mb-1"><i class="ri-flight-takeoff-line me-1"></i>Departure transfer</div>'
                + '<div>' + esc(item.pickup_hotel_name || item.pickup_hotel_id || 'Hotel') + ' → ' + esc(item.dropoff_port_name || item.dropoff_port_id || 'Port') + '</div>'
                + '<div class="text-muted mt-1">' + vehText + '</div>'
                + '<div class="text-muted mt-1">Adjust vehicle qty/pax under <strong>Departure Data</strong> below.</div>'
                + '</div>';
        }

        /**
         * Inclusive tour-day range for a hotel row (prefer city_plan city_day_from/_to).
         */
        function getHotelStayTourDayRangeBounds(hotel) {
            if (!hotel) return null;
            let from = parseInt(hotel.city_day_from, 10);
            let to = parseInt(hotel.city_day_to, 10);
            if (!isNaN(from) && from >= 1 && !isNaN(to) && to >= from) {
                return { from, to };
            }
            from = parseInt(hotel.start_day, 10);
            to = parseInt(hotel.end_day, 10);
            if (!isNaN(from) && from >= 1 && !isNaN(to) && to >= from) {
                return { from, to };
            }
            if (!isNaN(from) && from >= 1) {
                const span = Math.max(1, parseInt(hotel.nights, 10) || 1);
                return { from, to: from + span - 1 };
            }
            return null;
        }

        function getHotelInclusiveTourDaysCount(hotel) {
            const b = getHotelStayTourDayRangeBounds(hotel);
            if (!b) return 1;
            return b.to - b.from + 1;
        }

        /**
         * Tour-day index numbers covered by a hotel stay (package definition / booking).
         */
        function getHotelStayTourDays(hotel) {
            const b = getHotelStayTourDayRangeBounds(hotel);
            if (!b) return [];
            const days = [];
            for (let d = b.from; d <= b.to; d++) days.push(d);
            return days;
        }

        /**
         * Flat arrival_data with vehicles only → treat as tour day 1 (legacy packages).
         */
        function normalizeArrivalItemsForItinerary() {
            if (!arrivalData || typeof arrivalData !== 'object') return [];
            if (Array.isArray(arrivalData.items) && arrivalData.items.length) {
                return arrivalData.items.filter(it => it && typeof it === 'object');
            }
            const enabled = arrivalData.enabled === true || arrivalData.enabled === 1 || arrivalData.enabled === '1';
            const vehicles = Array.isArray(arrivalData.vehicles) ? arrivalData.vehicles : [];
            if (enabled && vehicles.length) {
                return [{
                    day: 1,
                    pickup_port_id: arrivalData.pickup_port_id,
                    dropoff_hotel_id: arrivalData.dropoff_hotel_id,
                    pickup_port_name: arrivalData.pickup_port_name,
                    dropoff_hotel_name: arrivalData.dropoff_hotel_name,
                    vehicles: vehicles,
                    _legacy_flat: true,
                }];
            }
            return [];
        }

        /**
         * Flat departure_data with vehicles only → last tour day (legacy packages).
         */
        function normalizeDepartureItemsForItinerary() {
            if (!departureData || typeof departureData !== 'object') return [];
            if (Array.isArray(departureData.items) && departureData.items.length) {
                return departureData.items.filter(it => it && typeof it === 'object');
            }
            const enabled = departureData.enabled === true || departureData.enabled === 1 || departureData.enabled === '1';
            const vehicles = Array.isArray(departureData.vehicles) ? departureData.vehicles : [];
            if (enabled && vehicles.length) {
                const lastDay = Math.max(1, getTourDurationDaysInclusive());
                return [{
                    day: lastDay,
                    pickup_hotel_id: departureData.pickup_hotel_id,
                    dropoff_port_id: departureData.dropoff_port_id,
                    pickup_hotel_name: departureData.pickup_hotel_name,
                    dropoff_port_name: departureData.dropoff_port_name,
                    vehicles: vehicles,
                    _legacy_flat: true,
                }];
            }
            return [];
        }

        /**
         * Read-only summary for a hotel night inside the day accordion (details stay in Hotels section).
         */
        function buildHotelStaySummaryMarkup(hotel, tourDay) {
            if (!hotel) return '';
            const name = hotel.hotel_name || hotel.name || 'Hotel';
            const b = getHotelStayTourDayRangeBounds(hotel);
            const span = b ? (b.to - b.from + 1) : 1;
            let nightOfStay = '';
            if (b && b.from >= 1) {
                const n = tourDay - b.from + 1;
                if (n >= 1 && n <= span) {
                    nightOfStay = '<div class="text-muted small">Stay: day ' + esc(n) + ' of ' + esc(span) + '</div>';
                }
            }
            const city = hotel.city || hotel.city_plan_city || selectedPackageCity || '';
            return '<div class="border rounded p-3 mb-2 bg-light day-it-hotel-summary">'
                + '<div class="fw-semibold text-body"><i class="ri-hotel-line me-1 text-primary"></i>' + esc(name) + '</div>'
                + (city ? '<div class="small text-muted">' + esc(city) + '</div>' : '')
                + nightOfStay
                + '</div>';
        }

        function buildLocalTransferDaySnippet(transfer, idx) {
            const t = transfer || {};
            const label = t.title || t.name || t.route_label || ('Local transfer ' + (idx + 1));
            const day = t.day != null ? t.day : '';
            return '<div class="border rounded p-2 mb-2 small bg-white">'
                + '<div class="fw-semibold"><i class="ri-car-line me-1"></i>' + esc(label) + '</div>'
                + (day !== '' ? '<div class="text-muted">Tour day ' + esc(day) + '</div>' : '')
                + '</div>';
        }

        function dayItinerarySectionMarkup(title, iconClass, bodyHtml) {
            if (!bodyHtml || String(bodyHtml).trim() === '') return '';
            const icon = iconClass ? ('<i class="' + esc(iconClass) + ' me-1"></i>') : '';
            return '<section class="day-itinerary-section mb-4">'
                + '<h6 class="day-itinerary-section-title">' + icon + esc(title) + '</h6>'
                + '<div class="day-itinerary-section-body">' + bodyHtml + '</div>'
                + '</section>';
        }

        /**
         * Collect unique tour days that have at least one itinerary row, sorted ascending.
         */
        function groupItineraryByUniqueDays() {
            const daySet = new Set();

            (hotels || []).forEach(h => {
                getHotelStayTourDays(h).forEach(d => daySet.add(d));
            });
            (attractions || []).forEach(a => {
                const d = parseInt(a && a.day, 10);
                if (!isNaN(d) && d > 0) daySet.add(d);
            });
            (restaurants || []).forEach(r => {
                const d = parseInt(r && r.day, 10);
                if (!isNaN(d) && d > 0) daySet.add(d);
            });
            normalizeArrivalItemsForItinerary().forEach(it => {
                const d = parseInt(it.day, 10);
                if (!isNaN(d) && d > 0) daySet.add(d);
            });
            normalizeDepartureItemsForItinerary().forEach(it => {
                const d = parseInt(it.day, 10);
                if (!isNaN(d) && d > 0) daySet.add(d);
            });
            (transfers || []).forEach(t => {
                const d = parseInt(t && t.day, 10);
                if (!isNaN(d) && d > 0) daySet.add(d);
            });

            const sortedDays = Array.from(daySet).sort((a, b) => a - b);
            const groups = [];

            sortedDays.forEach(tourDay => {
                const hotelBlocks = [];
                (hotels || []).forEach((h) => {
                    if (h && getHotelStayTourDays(h).includes(tourDay)) {
                        hotelBlocks.push(buildHotelStaySummaryMarkup(h, tourDay));
                    }
                });

                const transferParts = [];
                normalizeArrivalItemsForItinerary().filter(it => serviceDayMatches(it.day, tourDay)).forEach(it => {
                    transferParts.push(buildArrivalDaySnippet(it));
                });
                normalizeDepartureItemsForItinerary().filter(it => serviceDayMatches(it.day, tourDay)).forEach(it => {
                    transferParts.push(buildDepartureDaySnippet(it));
                });
                (transfers || []).forEach((t, tIdx) => {
                    if (t && serviceDayMatches(t.day, tourDay)) {
                        transferParts.push(buildLocalTransferDaySnippet(t, tIdx));
                    }
                });

                const attractionBlocks = [];
                (attractions || []).forEach((a, idx) => {
                    if (a && serviceDayMatches(a.day, tourDay)) {
                        attractionBlocks.push(buildAttractionCardMarkup(a, idx, { hideDayBadge: true }));
                    }
                });

                const restaurantBlocks = [];
                (restaurants || []).forEach((r, idx) => {
                    if (r && serviceDayMatches(r.day, tourDay)) {
                        restaurantBlocks.push(buildRestaurantCardMarkup(r, idx, { hideDayBadge: true }));
                    }
                });

                const hasContent = hotelBlocks.length || transferParts.length || attractionBlocks.length || restaurantBlocks.length;
                if (!hasContent) return;

                groups.push({
                    day: tourDay,
                    html: ''
                        + dayItinerarySectionMarkup('Hotel', 'ri-hotel-line text-primary', hotelBlocks.join(''))
                        + dayItinerarySectionMarkup('Transfers', 'ri-route-line text-info', transferParts.join(''))
                        + dayItinerarySectionMarkup('Attractions', 'ri-map-pin-line text-success', attractionBlocks.join(''))
                        + dayItinerarySectionMarkup('Restaurants', 'ri-restaurant-line text-warning', restaurantBlocks.join('')),
                });
            });

            return groups;
        }

        function renderDayWiseItinerary() {
            const el = document.getElementById('dayWiseItineraryList');
            if (!el) return;

            const groups = appendExtensionTourDayPlaceholders(groupItineraryByUniqueDays());
            if (!groups.length) {
                el.innerHTML = '<div class="alert alert-light border text-muted small mb-0">'
                    + 'No day-tagged itinerary rows yet for this package. If data uses legacy arrival/departure only, set travel dates and ensure services include day numbers. Hotels and port transfers also appear here when linked to tour days.'
                    + '</div>';
                return;
            }

            const accId = 'pkg-booking-day-accordion';
            let html = '<div class="accordion accordion-flush day-wise-itinerary-accordion" id="' + accId + '">';
            groups.forEach((group, index) => {
                const d = group.day;
                const collapseId = 'pkg-day-collapse-' + String(d).replace(/\W/g, '_');
                const headingId = 'pkg-day-heading-' + String(d).replace(/\W/g, '_');
                const isFirst = index === 0;
                html += '<div class="accordion-item">'
                    + '<h2 class="accordion-header" id="' + headingId + '">'
                    + '<button class="accordion-button' + (isFirst ? '' : ' collapsed') + '" type="button" data-bs-toggle="collapse" data-bs-target="#' + collapseId + '" aria-expanded="' + (isFirst ? 'true' : 'false') + '" aria-controls="' + collapseId + '">'
                    + '<span class="d-flex flex-wrap align-items-baseline gap-2 text-start w-100">'
                    + '<span class="fw-semibold">Day ' + esc(d) + '</span>'
                    + '<span class="text-muted small fw-normal">' + formatTourDayDateLine(d) + '</span>'
                    + '</span>'
                    + '</button>'
                    + '</h2>'
                    + '<div id="' + collapseId + '" class="accordion-collapse collapse' + (isFirst ? ' show' : '') + '" aria-labelledby="' + headingId + '">'
                    + '<div class="accordion-body pt-3 pb-4 px-2 px-md-3">' + group.html + '</div>'
                    + '</div>'
                    + '</div>';
            });
            html += '</div>';
            el.innerHTML = html;

            bindSelectableCheckboxes(el, attractions, 'attractions');
            bindServiceBookingPaxInputs(el, attractions, 'attractions');
            bindSelectableCheckboxes(el, restaurants, 'restaurants');
            bindServiceBookingPaxInputs(el, restaurants, 'restaurants');
        }

        function renderAttractions() {
            if (!Array.isArray(attractions) || attractions.length === 0) {
                attractionsList.innerHTML = '<div class="text-muted small">No attractions selected</div>';
                return;
            }
            attractionsList.innerHTML = attractions.map((a, idx) => buildAttractionCardMarkup(a, idx)).join('');
            bindSelectableCheckboxes(attractionsList, attractions, 'attractions');
            bindServiceBookingPaxInputs(attractionsList, attractions, 'attractions');
        }

        function renderRestaurants() {
            if (!Array.isArray(restaurants) || restaurants.length === 0) {
                restaurantsList.innerHTML = '<div class="text-muted small">No restaurants selected</div>';
                return;
            }
            restaurantsList.innerHTML = restaurants.map((r, idx) => buildRestaurantCardMarkup(r, idx)).join('');
            bindSelectableCheckboxes(restaurantsList, restaurants, 'restaurants');
            bindServiceBookingPaxInputs(restaurantsList, restaurants, 'restaurants');
        }

        function renderArrivalDeparture() {
            const arrivalSummary = document.getElementById('arrivalSummary');
            const departureSummary = document.getElementById('departureSummary');
            const arrivalEnabled = arrivalData && arrivalData.enabled;
            const departureEnabled = departureData && departureData.enabled;
            const arrivalBadge = arrivalEnabled ? formatBadge('Enabled', 'bg-success') : formatBadge('Disabled', 'bg-secondary');
            const departureBadge = departureEnabled ? formatBadge('Enabled', 'bg-success') : formatBadge('Disabled', 'bg-secondary');
            const arrivalVehicles = Array.isArray(arrivalData && arrivalData.vehicles) ? arrivalData.vehicles : [];
            const departureVehicles = Array.isArray(departureData && departureData.vehicles) ? departureData.vehicles : [];

            const renderVehicleRows = (vehicles, sectionKey, itemIdxOpt) => {
                if (!vehicles.length) return '<div class="text-muted small">-</div>';
                const itemAttr = (itemIdxOpt != null && itemIdxOpt !== undefined) ? (' data-item-idx="' + itemIdxOpt + '"') : '';
                return vehicles.map((v, idx) => {
                    const type = (v.selected_transfer_type || 'private').toLowerCase();
                    const typeBadge = type === 'shared'
                        ? formatBadge('Shared', 'bg-info')
                        : formatBadge('Private', 'bg-primary');
                    const seatCap = parseInt(v.seating_capacity, 10) || 0;
                    const qty = getResolvedVehicleBookingQty(v);
                    const pax = getResolvedVehicleBookingPax(v);
                    const unit = getVehicleUnitPrice(v);
                    const total = transferVehicleTotal(v);
                    const paxMaxAttr = seatCap > 0 ? (' max="' + seatCap + '"') : '';
                    const capacityNote = seatCap > 0
                        ? 'Max ' + seatCap + ' pax / vehicle.'
                        : '';
                    const formulaNote = type === 'shared'
                        ? 'Shared: unit × qty × pax.'
                        : 'Private: unit × qty (pax not multiplied).';
                    return '<div class="border rounded p-2 mb-2 bg-white" data-vehicle-card="' + esc(sectionKey) + '-' + (itemIdxOpt != null ? itemIdxOpt + '-' : '') + idx + '">'
                        + '<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">'
                        + '<div><span class="fw-semibold">' + esc(v.vehicle_name || v.vehicle_id || 'Vehicle') + '</span>'
                        + (v.vehicle_type ? ' <span class="text-muted small">(' + esc(v.vehicle_type) + ')</span>' : '')
                        + ' ' + typeBadge
                        + ' <span class="text-muted small ms-1">Unit: ' + esc(money(unit)) + '</span>'
                        + '</div>'
                        + '<div class="small"><strong>Total:</strong> <span class="vehicle-total-display">' + esc(money(total)) + '</span></div>'
                        + '</div>'
                        + '<div class="row g-2">'
                        + '<div class="col-md-3"><label class="form-label small mb-1">Qty</label>'
                        + '<input type="number" min="1" step="1" class="form-control form-control-sm ' + sectionKey + '-vehicle-qty" data-idx="' + idx + '"' + itemAttr + ' value="' + esc(qty) + '"></div>'
                        + '<div class="col-md-3"><label class="form-label small mb-1">Pax</label>'
                        + '<input type="number" min="0" step="1"' + paxMaxAttr + ' class="form-control form-control-sm ' + sectionKey + '-vehicle-pax" data-idx="' + idx + '"' + itemAttr + ' value="' + esc(pax) + '"></div>'
                        + '<div class="col-md-6 small text-muted align-self-end">'
                        + formulaNote
                        + (capacityNote ? ' ' + capacityNote : '')
                        + '</div>'
                        + '</div>'
                        + '</div>';
                }).join('');
            };

            const renderArrivalItemsSection = () => {
                if (!arrivalData || !Array.isArray(arrivalData.items) || !arrivalData.items.length) {
                    const ad = arrivalData || {};
                    return '<div class="row g-2">'
                        + '<div class="col-6"><div class="text-muted small">Pickup Port</div><div>' + esc(ad.pickup_port_name || ad.pickup_port_id || '-') + '</div></div>'
                        + '<div class="col-6"><div class="text-muted small">Dropoff Hotel</div><div>' + esc(ad.dropoff_hotel_name || ad.dropoff_hotel_id || '-') + '</div></div>'
                        + '<div class="col-12"><div class="text-muted small mb-1">Vehicles</div>'
                        + renderVehicleRows(arrivalVehicles, 'arrival')
                        + '</div></div>';
                }
                return arrivalData.items.map((item, itemIdx) => {
                    const vehicles = Array.isArray(item.vehicles) ? item.vehicles : [];
                    return '<div class="border rounded p-2 mb-3 bg-light">'
                        + '<div class="fw-semibold mb-2">Tour day ' + esc(item.day != null ? item.day : '-') + (item.city ? (' · ' + esc(item.city)) : '') + '</div>'
                        + '<div class="row g-2 mb-2"><div class="col-6"><div class="text-muted small">Pickup Port</div><div>' + esc(item.pickup_port_name || item.pickup_port_id || '-') + '</div></div>'
                        + '<div class="col-6"><div class="text-muted small">Dropoff Hotel</div><div>' + esc(item.dropoff_hotel_name || item.dropoff_hotel_id || '-') + '</div></div></div>'
                        + '<div class="text-muted small mb-1">Vehicles</div>'
                        + renderVehicleRows(vehicles, 'arrival', itemIdx)
                        + '</div>';
                }).join('');
            };

            const renderDepartureItemsSection = () => {
                if (!departureData || !Array.isArray(departureData.items) || !departureData.items.length) {
                    const dd = departureData || {};
                    return '<div class="row g-2">'
                        + '<div class="col-6"><div class="text-muted small">Pickup Hotel</div><div>' + esc(dd.pickup_hotel_name || dd.pickup_hotel_id || '-') + '</div></div>'
                        + '<div class="col-6"><div class="text-muted small">Dropoff Port</div><div>' + esc(dd.dropoff_port_name || dd.dropoff_port_id || '-') + '</div></div>'
                        + '<div class="col-12"><div class="text-muted small mb-1">Vehicles</div>'
                        + renderVehicleRows(departureVehicles, 'departure')
                        + '</div></div>';
                }
                return departureData.items.map((item, itemIdx) => {
                    const vehicles = Array.isArray(item.vehicles) ? item.vehicles : [];
                    return '<div class="border rounded p-2 mb-3 bg-light">'
                        + '<div class="fw-semibold mb-2">Tour day ' + esc(item.day != null ? item.day : '-') + (item.city ? (' · ' + esc(item.city)) : '') + '</div>'
                        + '<div class="row g-2 mb-2"><div class="col-6"><div class="text-muted small">Pickup Hotel</div><div>' + esc(item.pickup_hotel_name || item.pickup_hotel_id || '-') + '</div></div>'
                        + '<div class="col-6"><div class="text-muted small">Dropoff Port</div><div>' + esc(item.dropoff_port_name || item.dropoff_port_id || '-') + '</div></div></div>'
                        + '<div class="text-muted small mb-1">Vehicles</div>'
                        + renderVehicleRows(vehicles, 'departure', itemIdx)
                        + '</div>';
                }).join('');
            };

            arrivalSummary.innerHTML = '<div class="border rounded p-3 mb-2">'
                + '<div class="d-flex justify-content-between align-items-center mb-2"><div class="fw-semibold">Arrival</div>'
                + '<div class="d-flex align-items-center gap-2 flex-wrap">' + arrivalBadge + '</div></div>'
                + renderArrivalItemsSection()
                + '</div>';

            departureSummary.innerHTML = '<div class="border rounded p-3 mb-2">'
                + '<div class="d-flex justify-content-between align-items-center mb-2"><div class="fw-semibold">Departure</div>'
                + '<div class="d-flex align-items-center gap-2 flex-wrap">' + departureBadge + '</div></div>'
                + renderDepartureItemsSection()
                + '</div>';
        }

        function numVal(v) {
            const n = parseFloat(v);
            return isNaN(n) ? 0 : n;
        }

        /** Booking basics: pax count (from main form). */
        function getPaxCount() {
            const pax = parseInt(paxEl && paxEl.value ? paxEl.value : '0', 10);
            const p = isNaN(pax) ? 0 : pax;
            return Math.max(0, p);
        }

        /**
         * Hotel charging rule: pax rounded UP to the next even number.
         * Examples: 2 -> 2, 3 -> 4, 4 -> 4, 5 -> 6, 6 -> 6, ...
         * Rationale: one room fits 2 persons; a 3rd person needs another room,
         * and that extra room is billed as two persons.
         */
        function getEffectiveHotelPax() {
            const pax = getPaxCount();
            if (pax <= 0) return 0;
            return pax % 2 === 0 ? pax : pax + 1;
        }

        /**
         * Per-head price stored in the package definition.
         * Prefers final_price / total_price (new field) and falls back to base_price for older data.
         */
        function getServicePerHeadPrice(item) {
            if (!item) return 0;
            if (item.final_price != null && item.final_price !== '' && !isNaN(parseFloat(item.final_price))) {
                return parseFloat(item.final_price);
            }
            if (item.total_price != null && item.total_price !== '' && !isNaN(parseFloat(item.total_price))) {
                return parseFloat(item.total_price);
            }
            return numVal(item.base_price);
        }

        function getResolvedServiceBookingPax(item) {
            if (item && item.booking_pax != null && String(item.booking_pax).trim() !== '') {
                const v = parseInt(item.booking_pax, 10);
                if (!isNaN(v) && v >= 0) return v;
            }
            return getPaxCount();
        }

        /** Pax for this attraction/restaurant booking (per-service input, falls back to main pax). */
        function getServiceBookingPax(item) {
            return getResolvedServiceBookingPax(item);
        }

        /** Resolve a vehicle's booking qty (falls back to the qty stored at package definition time, then 1). */
        function getResolvedVehicleBookingQty(vehicle) {
            if (vehicle && vehicle.booking_qty != null && String(vehicle.booking_qty).trim() !== '') {
                const v = parseInt(vehicle.booking_qty, 10);
                if (!isNaN(v) && v > 0) return v;
            }
            const q = parseInt(vehicle && vehicle.qty, 10);
            return !isNaN(q) && q > 0 ? q : 1;
        }

        /**
         * Resolve a vehicle's booking pax, capped at its seating_capacity.
         * Falls back to the main Pax input when `booking_pax` hasn't been set yet.
         */
        function getResolvedVehicleBookingPax(vehicle) {
            const seatCap = parseInt(vehicle && vehicle.seating_capacity, 10) || 0;
            let pax;
            if (vehicle && vehicle.booking_pax != null && String(vehicle.booking_pax).trim() !== '') {
                pax = parseInt(vehicle.booking_pax, 10);
                if (isNaN(pax) || pax < 0) pax = getPaxCount();
            } else {
                pax = getPaxCount();
            }
            if (seatCap > 0 && pax > seatCap) pax = seatCap;
            if (pax < 0) pax = 0;
            return pax;
        }

        /** Per-vehicle unit price (prefers stored unit_price, else private/shared price by type). */
        function getVehicleUnitPrice(vehicle) {
            if (!vehicle) return 0;
            const unit = numVal(vehicle.unit_price);
            if (unit > 0) return unit;
            const type = (vehicle.selected_transfer_type || 'private').toLowerCase();
            return type === 'shared' ? numVal(vehicle.shared_price) : numVal(vehicle.private_price);
        }

        function money(v) {
            return 'SGD ' + numVal(v).toFixed(2);
        }

        function parseIsoDate(dateStr) {
            if (!dateStr || typeof dateStr !== 'string') return null;
            const parts = dateStr.split('-');
            if (parts.length !== 3) return null;
            const y = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10);
            const d = parseInt(parts[2], 10);
            if (!y || !m || !d) return null;
            const dt = new Date(y, m - 1, d);
            if (isNaN(dt.getTime())) return null;
            return dt;
        }

        function formatIsoDate(dt) {
            const y = dt.getFullYear();
            const m = String(dt.getMonth() + 1).padStart(2, '0');
            const d = String(dt.getDate()).padStart(2, '0');
            return y + '-' + m + '-' + d;
        }

        /**
         * Travel start through travel end inclusive. Tour day 1 = travel start date.
         */
        function getTravelDateRange() {
            const start = parseIsoDate(startDateEl && startDateEl.value ? startDateEl.value : '');
            const end = parseIsoDate(endDateEl && endDateEl.value ? endDateEl.value : '');
            if (!start || !end || end < start) return [];
            const dates = [];
            const cursor = new Date(start);
            while (cursor <= end) {
                dates.push(formatIsoDate(cursor));
                cursor.setDate(cursor.getDate() + 1);
            }
            return dates;
        }

        function getTourDurationDaysInclusive() {
            const start = parseIsoDate(startDateEl && startDateEl.value ? startDateEl.value : '');
            const end = parseIsoDate(endDateEl && endDateEl.value ? endDateEl.value : '');
            if (start && end && end >= start) {
                const days = Math.floor((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24)) + 1;
                return Math.max(1, days);
            }
            const fb = parseInt(loadedPackageDurationDays, 10);
            return !isNaN(fb) && fb > 0 ? fb : 1;
        }

        function mapTourDayNumberToDate(dayNum) {
            const start = parseIsoDate(startDateEl && startDateEl.value ? startDateEl.value : '');
            if (!start) return null;
            const d = parseInt(dayNum, 10);
            if (isNaN(d) || d < 1) return null;
            const dt = new Date(start.getFullYear(), start.getMonth(), start.getDate() + (d - 1));
            return formatIsoDate(dt);
        }

        /**
         * Maps each tour day in the hotel's stay span to a calendar date (day 1 = trip start).
         */
        function getExpectedHotelBookingDatesForHotel(hotel, availableDates) {
            const avail = Array.isArray(availableDates) ? availableDates : getTravelDateRange();
            const b = getHotelStayTourDayRangeBounds(hotel);
            if (!b || !avail.length) return null;
            const picked = [];
            for (let d = b.from; d <= b.to; d++) {
                const iso = mapTourDayNumberToDate(d);
                if (iso && avail.includes(iso)) picked.push(iso);
            }
            return picked.length ? picked.slice() : null;
        }

        function initHotelDatesFromDefinitionDays() {
            if (!Array.isArray(hotels) || hotels.length === 0) return;
            const avail = getTravelDateRange();
            if (!avail.length) return;
            hotels.forEach((h, idx) => {
                const expected = getExpectedHotelBookingDatesForHotel(h, avail);
                if (!expected || !expected.length) return;
                const key = getHotelDateKey(h, idx);
                selectedHotelDates[key] = expected.slice();
                h.hotel_booking_dates = expected.slice();
            });
        }

        /**
         * Booking screen UX is day-first only:
         * always render day-wise accordion and hide legacy attractions/restaurants blocks.
         */
        function useDayWiseItineraryLayout() {
            return true;
        }

        function serviceDayMatches(dayField, tourDay) {
            const v = parseInt(dayField, 10);
            if (isNaN(v)) return false;
            return v === tourDay;
        }

        function formatTourDayDateLine(tourDay) {
            const iso = mapTourDayNumberToDate(tourDay);
            if (!iso) return '';
            const dt = parseIsoDate(iso);
            if (!dt) return esc(iso);
            const wk = dayNames[dt.getDay()].slice(0, 3);
            return esc(iso) + ' · ' + esc(wk);
        }

        /**
         * Unique key per selected_hotels row (same hotel_id can appear twice for different city segments).
         */
        function getHotelDateKey(hotel, indexHint) {
            const planId = hotel && hotel.city_plan_id != null && String(hotel.city_plan_id).trim() !== ''
                ? String(hotel.city_plan_id)
                : '';
            const id = hotel && (hotel.hotel_id || hotel.id);
            if (planId && id != null && String(id).trim() !== '') {
                return String(id) + '::plan_' + planId;
            }
            if (id != null && String(id).trim() !== '') {
                return String(id) + '::idx_' + String(indexHint != null ? indexHint : '');
            }
            return 'idx_' + String(indexHint != null ? indexHint : '');
        }

        function getDefaultHotelNights(hotel) {
            return getHotelInclusiveTourDaysCount(hotel);
        }

        function getHotelMaxSelectableNights(hotel, availableDatesCount) {
            return Math.max(0, Math.min(getDefaultHotelNights(hotel), Math.max(0, availableDatesCount || 0)));
        }

        /** Maximum rooms allowed for any hotel = main-form pax (rooms cannot exceed pax). */
        function getMaxHotelRooms() {
            return Math.max(1, getPaxCount());
        }

        /**
         * Number of rooms the user wants to book for this hotel.
         * Capped at pax (rooms cannot exceed pax) and floored at 1.
         */
        function getHotelNumRooms(hotel) {
            const cap = getMaxHotelRooms();
            if (hotel && hotel.num_rooms != null && String(hotel.num_rooms).trim() !== '') {
                const v = parseInt(hotel.num_rooms, 10);
                if (!isNaN(v) && v > 0) return Math.min(v, cap);
            }
            return 1;
        }

        /** Number of single-occupancy rooms (capped at total rooms). */
        function getHotelSingleRooms(hotel) {
            const total = getHotelNumRooms(hotel);
            if (hotel && hotel.num_single_rooms != null && String(hotel.num_single_rooms).trim() !== '') {
                const v = parseInt(hotel.num_single_rooms, 10);
                if (!isNaN(v) && v >= 0) return Math.min(v, total);
            }
            return 0;
        }

        /**
         * Triple occupancy is only available on hotels whose room types include an
         * extra-bed configuration (3rd guest sleeps on the rollaway).
         */
        function hotelAllowsTriple(hotel) {
            if (!hotel || !Array.isArray(hotel.rooms)) return false;
            return hotel.rooms.some(function (r) {
                if (!r) return false;
                const v = r.extra_bed;
                return v === true || v === 1 || v === '1' || v === 'true';
            });
        }

        /** Number of triple-occupancy rooms (only when extra_bed is allowed). */
        function getHotelTripleRooms(hotel) {
            if (!hotelAllowsTriple(hotel)) return 0;
            const total = getHotelNumRooms(hotel);
            const single = getHotelSingleRooms(hotel);
            const remaining = Math.max(0, total - single);
            if (hotel && hotel.num_triple_rooms != null && String(hotel.num_triple_rooms).trim() !== '') {
                const v = parseInt(hotel.num_triple_rooms, 10);
                if (!isNaN(v) && v >= 0) return Math.min(v, remaining);
            }
            return 0;
        }

        /** Number of double-occupancy rooms = total rooms - single rooms - triple rooms. Default: all rooms double. */
        function getHotelDoubleRooms(hotel) {
            return Math.max(0, getHotelNumRooms(hotel) - getHotelSingleRooms(hotel) - getHotelTripleRooms(hotel));
        }

        /**
         * Reconcile a hotel's room/occupancy fields so that:
         *   1. single + double === total              (rooms must be exactly one occupancy type)
         *   2. 2 * double + single >= pax             (every guest has a bed)
         *   3. total <= pax                           (a room must hold at least 1 guest)
         *
         * If the current split can't seat everyone, we grow the booking with *double* rooms
         * (the default occupancy type per spec). A 1-guest deficit still adds a full double
         * room because we can't half-book a room. Caller is responsible for re-rendering.
         */
        function applyHotelRoomConsistency(hotel) {
            if (!hotel) return;
            const pax = getPaxCount();
            const cap = getMaxHotelRooms();
            const allowTriple = hotelAllowsTriple(hotel);

            let total = parseInt(hotel.num_rooms, 10);
            if (isNaN(total) || total < 1) total = 1;
            if (total > cap) total = cap;

            let single = parseInt(hotel.num_single_rooms, 10);
            if (isNaN(single) || single < 0) single = 0;
            if (single > total) single = total;

            let trip = allowTriple ? parseInt(hotel.num_triple_rooms, 10) : 0;
            if (isNaN(trip) || trip < 0) trip = 0;
            if (single + trip > total) trip = Math.max(0, total - single);

            let dbl = Math.max(0, total - single - trip);

            const capacity = 2 * dbl + single + 3 * trip;
            if (pax > 0 && capacity < pax) {
                const deficit = pax - capacity;
                const addDoubles = Math.ceil(deficit / 2);
                dbl += addDoubles;
                total += addDoubles;
            }

            // Defensive cap-clamp; with the math above this branch only runs on bad input.
            if (total > cap) {
                const overflow = total - cap;
                dbl = Math.max(0, dbl - overflow);
                total = single + dbl + trip;
            }

            hotel.num_rooms = total;
            hotel.num_single_rooms = single;
            hotel.num_double_rooms = dbl;
            hotel.num_triple_rooms = allowTriple ? trip : 0;
        }

        /**
         * Initialise room-occupancy defaults on each hotel object.
         * - num_rooms defaults to ceil(pax/2) so the natural starting state matches "2 per room".
         * - num_single_rooms defaults to 0 (all rooms double by default).
         * - num_double_rooms always derives from num_rooms - num_single_rooms.
         */
        function initHotelRoomDefaults(list) {
            if (!Array.isArray(list)) return [];
            const cap = getMaxHotelRooms();
            return list.map(h => {
                if (!h) return h;
                const parsedRooms = parseInt(h.num_rooms, 10);
                if (h.num_rooms == null || isNaN(parsedRooms) || parsedRooms <= 0) {
                    const pax = getPaxCount();
                    h.num_rooms = Math.max(1, Math.min(cap, Math.ceil(pax / 2)));
                } else {
                    h.num_rooms = Math.min(cap, parsedRooms);
                }
                const parsedSingle = parseInt(h.num_single_rooms, 10);
                if (h.num_single_rooms == null || isNaN(parsedSingle) || parsedSingle < 0) {
                    h.num_single_rooms = 0;
                } else {
                    h.num_single_rooms = Math.min(parsedSingle, h.num_rooms);
                }
                if (hotelAllowsTriple(h)) {
                    const parsedTriple = parseInt(h.num_triple_rooms, 10);
                    const tripCap = Math.max(0, h.num_rooms - h.num_single_rooms);
                    if (h.num_triple_rooms == null || isNaN(parsedTriple) || parsedTriple < 0) {
                        h.num_triple_rooms = 0;
                    } else {
                        h.num_triple_rooms = Math.min(parsedTriple, tripCap);
                    }
                } else {
                    h.num_triple_rooms = 0;
                }
                h.num_double_rooms = Math.max(0, h.num_rooms - h.num_single_rooms - h.num_triple_rooms);
                applyHotelRoomConsistency(h);
                return h;
            });
        }

        function orderedUniqueDates(list, availableDates) {
            const set = new Set(Array.isArray(list) ? list : []);
            return (availableDates || []).filter(d => set.has(d));
        }

        function ensureHotelDateSelection(hotel, indexHint) {
            const key = getHotelDateKey(hotel, indexHint);
            const availableDates = getTravelDateRange();
            const maxAllowed = getHotelMaxSelectableNights(hotel, availableDates.length);
            let selected = selectedHotelDates[key];
            if (!Array.isArray(selected)) selected = Array.isArray(hotel && hotel.hotel_booking_dates) ? hotel.hotel_booking_dates : [];
            selected = orderedUniqueDates(selected, availableDates);
            if (selected.length > maxAllowed) {
                selected = selected.slice(0, maxAllowed);
            }
            if (selected.length === 0 && maxAllowed > 0) {
                const aligned = getExpectedHotelBookingDatesForHotel(hotel, availableDates);
                selected = (aligned && aligned.length)
                    ? aligned.slice(0, Math.min(aligned.length, maxAllowed))
                    : availableDates.slice(0, maxAllowed);
            }
            selectedHotelDates[key] = selected;
            if (hotel) hotel.hotel_booking_dates = selected.slice();
            return selected.slice();
        }

        function getHotelNightBreakdown(hotel, indexHint) {
            const selectedDates = ensureHotelDateSelection(hotel, indexHint);
            return {
                nights: selectedDates.length,
                selected_dates: selectedDates
            };
        }

        function serviceStatus(item) {
            if (item && item.compulsory === true) return 'Compulsory';
            if (item && item.optional === true) return 'Optional';
            if (item && item.addon === true) return 'Add-on';
            return '-';
        }

        /**
         * Hotel line total uses a per-night, per-room formula that is independent of pax:
         *   total = Σ (over selected nights) of
         *           [(price * 2 * num_double) + (price * 2 * num_single)
         *            + ((price * 2 + extra_bed_price) * num_triple)]
         * where `price` is the room's weekend_price on weekend days (per hotel.weekend_days)
         * and weekday_price otherwise. Triple sharing = double-sharing rate + extra bed charge.
         */
        function hotelTotal(item, indexHint) {
            if (!item) return 0;
            const breakdown = getHotelNightBreakdown(item, indexHint);
            const selectedDates = breakdown.selected_dates;
            if (!selectedDates.length) return 0;

            const rooms = Array.isArray(item.rooms) ? item.rooms : [];
            if (!rooms.length) return 0;

            // Use the first room type's prices as the hotel's room rate.
            const room = rooms[0] || {};
            const weekendPrice = numVal(room.weekend_price);
            const weekdayPrice = numVal(room.weekday_price);
            const extraBedPrice = numVal(room.extra_bed_price);
            const weekendDays = Array.isArray(item.weekend_days) ? item.weekend_days : [];

            const numSingle = getHotelSingleRooms(item);
            const numTriple = getHotelTripleRooms(item);
            const numDouble = getHotelDoubleRooms(item);
            if (numSingle + numDouble + numTriple <= 0) return 0;

            let total = 0;
            selectedDates.forEach(dateStr => {
                const dt = parseIsoDate(dateStr);
                if (!dt) return;
                const dayName = dayNames[dt.getDay()];
                const price = weekendDays.includes(dayName) ? weekendPrice : weekdayPrice;
                const triplePerRoomPerNight = (price * 2) + extraBedPrice;
                total += (price * 2 * numDouble) + (price * 2 * numSingle) + (triplePerRoomPerNight * numTriple);
            });
            return total;
        }

        /**
         * Attraction line total = per-head price × booking pax.
         * Prefers the final_price stored at definition time; falls back to base + guide + transfer
         * so older package payloads without final_price still work.
         */
        function attractionTotal(item) {
            if (!item) return 0;
            let perPax = getServicePerHeadPrice(item);
            if (perPax <= 0) {
                perPax = numVal(item.base_price)
                    + numVal(item.guide ? item.guide.price : 0)
                    + numVal(item.transfer_price);
            }
            return perPax * getServiceBookingPax(item);
        }

        /**
         * Restaurant line total = per-head price × booking pax.
         * Prefers the final_price stored at definition time; falls back to base + transfer for older data.
         */
        function restaurantTotal(item) {
            if (!item) return 0;
            let perPax = getServicePerHeadPrice(item);
            if (perPax <= 0) {
                perPax = numVal(item.base_price) + numVal(item.transfer_price);
            }
            return perPax * getServiceBookingPax(item);
        }

        /**
         * Vehicle line total.
         *   private -> unit_price * booking_qty                 (pax not multiplied)
         *   shared  -> unit_price * booking_qty * booking_pax   (pax multiplied)
         * booking_qty / booking_pax fall back to definition qty and main-form pax.
         */
        function transferVehicleTotal(vehicle) {
            if (!vehicle) return 0;
            const unit = getVehicleUnitPrice(vehicle);
            if (unit <= 0) return 0;
            const qty = getResolvedVehicleBookingQty(vehicle);
            const type = (vehicle.selected_transfer_type || 'private').toLowerCase();
            if (type === 'shared') {
                return unit * qty * getResolvedVehicleBookingPax(vehicle);
            }
            return unit * qty;
        }

        function buildSupplementaryData(tourStartDate) {
            const hotelAddons = (hotels || [])
                .map((h, idx) => ({ h, idx }))
                .filter(({ h }) => h && h.addon === true && h.selected === true)
                .map(({ h, idx }) => {
                    const breakdown = getHotelNightBreakdown(h, idx);
                    const total = hotelTotal(h, idx);
                    return {
                        hotel_id: h.hotel_id || h.id || null,
                        hotel_name: h.hotel_name || h.name || 'Hotel',
                        service_type: 'addon',
                        base_price: total,
                        selected_price: total,
                        nights: breakdown.nights,
                        hotel_booking_dates: breakdown.selected_dates,
                        tour_start_date: tourStartDate
                    };
                });

            const attractionAddons = (attractions || [])
                .filter(a => a && a.addon === true && a.selected === true)
                .map(a => ({
                    attraction_id: a.attraction_id || a.id || null,
                    attraction_name: a.name || 'Attraction',
                    service_type: 'addon',
                    booking_pax: getResolvedServiceBookingPax(a),
                    pricing: {
                        base_price: numVal(a.base_price),
                        guide_price: numVal(a.guide && a.guide.price),
                        transfer_price: numVal(a.transfer_price),
                        total_price: attractionTotal(a)
                    },
                    tour_start_date: tourStartDate
                }));

            const restaurantAddons = (restaurants || [])
                .filter(r => r && r.addon === true && r.selected === true)
                .map(r => ({
                    restaurant_id: r.restaurant_id || r.id || null,
                    restaurant_name: r.restaurant_name || r.name || 'Restaurant',
                    service_type: 'addon',
                    booking_pax: getResolvedServiceBookingPax(r),
                    pricing: {
                        base_price: numVal(r.base_price),
                        transfer_price: numVal(r.transfer_price),
                        total_price: restaurantTotal(r)
                    },
                    tour_start_date: tourStartDate
                }));

            return {
                hotel: {
                    addons: hotelAddons,
                    single_occupancy: []
                },
                attraction: {
                    addons: attractionAddons
                },
                restaurant: {
                    addons: restaurantAddons
                }
            };
        }

        function getAllArrivalVehicles() {
            if (!arrivalData || typeof arrivalData !== 'object') return [];
            if (Array.isArray(arrivalData.items) && arrivalData.items.length) {
                return arrivalData.items.flatMap(it => (Array.isArray(it && it.vehicles) ? it.vehicles : []));
            }
            return Array.isArray(arrivalData.vehicles) ? arrivalData.vehicles : [];
        }

        function getAllDepartureVehicles() {
            if (!departureData || typeof departureData !== 'object') return [];
            if (Array.isArray(departureData.items) && departureData.items.length) {
                return departureData.items.flatMap(it => (Array.isArray(it && it.vehicles) ? it.vehicles : []));
            }
            return Array.isArray(departureData.vehicles) ? departureData.vehicles : [];
        }

        function renderPricingSummary() {
            if (!pricingSummary) return;

            const hotelRows = (hotels || []).map((h, idx) => ({
                name: h.hotel_name || h.name || 'Hotel',
                status: serviceStatus(h),
                total: hotelTotal(h, idx),
                selected: !!(h && h.selected === true),
                compulsory: !!(h && h.compulsory === true),
                optional: !!(h && h.optional === true),
                addon: !!(h && h.addon === true),
            }));

            const attractionRows = (attractions || []).map(a => ({
                name: a.name || 'Attraction',
                status: serviceStatus(a),
                total: attractionTotal(a),
                selected: !!(a && a.selected === true),
                compulsory: !!(a && a.compulsory === true),
                optional: !!(a && a.optional === true),
                addon: !!(a && a.addon === true),
            }));

            const restaurantRows = (restaurants || []).map(r => ({
                name: r.restaurant_name || r.name || 'Restaurant',
                status: serviceStatus(r),
                total: restaurantTotal(r),
                selected: !!(r && r.selected === true),
                compulsory: !!(r && r.compulsory === true),
                optional: !!(r && r.optional === true),
                addon: !!(r && r.addon === true),
            }));

            const arrivalRows = getAllArrivalVehicles().map(v => ({
                name: 'Arrival - ' + (v.vehicle_name || v.vehicle_id || 'Vehicle'),
                status: 'Compulsory',
                total: transferVehicleTotal(v),
                selected: true,
            }));
            const departureRows = getAllDepartureVehicles().map(v => ({
                name: 'Departure - ' + (v.vehicle_name || v.vehicle_id || 'Vehicle'),
                status: 'Compulsory',
                total: transferVehicleTotal(v),
                selected: true,
            }));

            const allRows = []
                .concat(hotelRows.map(r => ({ section: 'Hotel', ...r })))
                .concat(attractionRows.map(r => ({ section: 'Attraction', ...r })))
                .concat(restaurantRows.map(r => ({ section: 'Restaurant', ...r })))
                .concat(arrivalRows.map(r => ({ section: 'Arrival', ...r })))
                .concat(departureRows.map(r => ({ section: 'Departure', ...r })));

            const selectedHotelAddonsTotal = (hotels || []).reduce((sum, h, idx) => {
                if (!h) return sum;
                if (h.addon === true && h.selected === true) {
                    return sum + hotelTotal(h, idx);
                }
                return sum;
            }, 0);

            const sectionTotals = {
                Hotel: { compulsory: 0, optional: 0, addon: 0 },
                Attraction: { compulsory: 0, optional: 0, addon: 0 },
                Restaurant: { compulsory: 0, optional: 0, addon: 0 },
                Arrival: { compulsory: 0, optional: 0, addon: 0 },
                Departure: { compulsory: 0, optional: 0, addon: 0 }
            };
            allRows.forEach(row => {
                if (!sectionTotals[row.section]) return;
                if (row.status === 'Compulsory') {
                    sectionTotals[row.section].compulsory += row.total;
                } else if (row.status === 'Optional') {
                    if (row.selected === true) sectionTotals[row.section].optional += row.total;
                } else if (row.status === 'Add-on') {
                    if (row.selected === true) sectionTotals[row.section].addon += row.total;
                }
            });
            const grandTotal = allRows.reduce((sum, row) => {
                if (row.status === 'Compulsory') return sum + row.total;
                if ((row.status === 'Optional' || row.status === 'Add-on') && row.selected === true) return sum + row.total;
                return sum;
            }, 0);

            const markupType = String((selectedPackagePriceData && selectedPackagePriceData.markup_type) || 'flat').toLowerCase();
            const markupAmount = parseFloat((selectedPackagePriceData && selectedPackagePriceData.markup_amount) || 0) || 0;
            const totalPriceRounded = ceilToFive(grandTotal);
            const finalPriceRaw = computeFinalPrice(grandTotal, markupType, markupAmount);
            const finalPriceRounded = ceilToFive(finalPriceRaw);

            lastPricingTotals = {
                total_price: totalPriceRounded,
                final_price: finalPriceRounded,
                markup_type: markupType,
                markup_amount: markupAmount
            };

            const priceDataInput = document.getElementById('price_data_input');
            if (priceDataInput) {
                priceDataInput.value = JSON.stringify({
                    total_price: totalPriceRounded,
                    final_price: finalPriceRounded,
                    markup_type: markupType,
                    markup_amount: markupAmount
                });
            }

            if (allRows.length === 0) {
                pricingSummary.innerHTML = '<div class="text-muted small">No pricing data available.</div>';
                return;
            }

            const markupLabel = markupAmount > 0
                ? (markupType === 'percentage'
                    ? ('Markup: ' + esc(markupAmount) + '%')
                    : ('Markup: ' + esc(money(markupAmount)) + ' (flat)'))
                : 'Markup: None';

            pricingSummary.innerHTML =
                '<div class="row g-2 small">'
                + '<div class="col-md-6"><div class="border rounded p-2"><strong>Hotel:</strong> C ' + esc(money(sectionTotals.Hotel.compulsory)) + ' / O ' + esc(money(sectionTotals.Hotel.optional)) + '</div></div>'
                + '<div class="col-md-6"><div class="border rounded p-2"><strong>Hotel Add-ons (selected):</strong> ' + esc(money(selectedHotelAddonsTotal)) + '</div></div>'
                + '<div class="col-md-6"><div class="border rounded p-2"><strong>Attraction:</strong> C ' + esc(money(sectionTotals.Attraction.compulsory)) + ' / O ' + esc(money(sectionTotals.Attraction.optional)) + ' / A ' + esc(money(sectionTotals.Attraction.addon)) + '</div></div>'
                + '<div class="col-md-6"><div class="border rounded p-2"><strong>Restaurant:</strong> C ' + esc(money(sectionTotals.Restaurant.compulsory)) + ' / O ' + esc(money(sectionTotals.Restaurant.optional)) + ' / A ' + esc(money(sectionTotals.Restaurant.addon)) + '</div></div>'
                + '<div class="col-md-6"><div class="border rounded p-2"><strong>Arrival:</strong> C ' + esc(money(sectionTotals.Arrival.compulsory)) + ' / O ' + esc(money(sectionTotals.Arrival.optional)) + ' / A ' + esc(money(sectionTotals.Arrival.addon)) + '</div></div>'
                + '<div class="col-md-6"><div class="border rounded p-2"><strong>Departure:</strong> C ' + esc(money(sectionTotals.Departure.compulsory)) + ' / O ' + esc(money(sectionTotals.Departure.optional)) + ' / A ' + esc(money(sectionTotals.Departure.addon)) + '</div></div>'
                + '<div class="col-md-4"><div class="border rounded p-2 bg-light"><strong>Total Price:</strong> ' + esc(money(totalPriceRounded)) + '</div></div>'
                + '<div class="col-md-4"><div class="border rounded p-2 bg-light"><strong>' + esc(markupLabel) + '</strong></div></div>'
                + '<div class="col-md-4"><div class="border rounded p-2 bg-primary text-white"><strong>Final Price:</strong> ' + esc(money(finalPriceRounded)) + '</div></div>'
                + '</div>';
        }

        function syncHidden() {
            const tourStartDate = startDateEl && startDateEl.value ? startDateEl.value : null;
            const prevArrival = arrivalData && typeof arrivalData === 'object' ? arrivalData : {};
            const prevDeparture = departureData && typeof departureData === 'object' ? departureData : {};
            const arrEnabled = document.getElementById('arrivalEnabled').value === '1';
            const depEnabled = document.getElementById('departureEnabled').value === '1';

            if (Array.isArray(prevArrival.items) && prevArrival.items.length) {
                arrivalData = {
                    ...prevArrival,
                    enabled: arrEnabled,
                    tour_start_date: tourStartDate,
                };
                const first = prevArrival.items[0];
                arrivalData.pickup_port_id = first && first.pickup_port_id != null ? first.pickup_port_id : null;
                arrivalData.dropoff_hotel_id = first && first.dropoff_hotel_id != null ? first.dropoff_hotel_id : null;
                arrivalData.vehicles = [];
            } else {
                arrivalData = {
                    ...prevArrival,
                    enabled: arrEnabled,
                    pickup_port_id: document.getElementById('arrivalPickupPortId').value || null,
                    dropoff_hotel_id: document.getElementById('arrivalDropoffHotelId').value || null,
                    vehicles: Array.isArray(prevArrival.vehicles) ? prevArrival.vehicles : [],
                    tour_start_date: tourStartDate
                };
            }

            if (Array.isArray(prevDeparture.items) && prevDeparture.items.length) {
                departureData = {
                    ...prevDeparture,
                    enabled: depEnabled,
                    tour_start_date: tourStartDate,
                };
                const firstD = prevDeparture.items[0];
                departureData.pickup_hotel_id = firstD && firstD.pickup_hotel_id != null ? firstD.pickup_hotel_id : null;
                departureData.dropoff_port_id = firstD && firstD.dropoff_port_id != null ? firstD.dropoff_port_id : null;
                departureData.vehicles = [];
            } else {
                departureData = {
                    ...prevDeparture,
                    enabled: depEnabled,
                    pickup_hotel_id: document.getElementById('departurePickupHotelId').value || null,
                    dropoff_port_id: document.getElementById('departureDropoffPortId').value || null,
                    vehicles: Array.isArray(prevDeparture.vehicles) ? prevDeparture.vehicles : [],
                    tour_start_date: tourStartDate
                };
            }

            const hotelsPayload = (hotels || []).reduce((acc, h, originalIdx) => {
                if (!h || (h.compulsory !== true && h.selected !== true)) return acc;
                const computedTotal = hotelTotal(h, originalIdx);
                const breakdown = getHotelNightBreakdown(h, originalIdx);
                acc.push({
                    ...h,
                    nights: breakdown.nights,
                    hotel_booking_dates: breakdown.selected_dates,
                    base_price: computedTotal,
                    tour_start_date: tourStartDate
                });
                return acc;
            }, []);
            const hotelBookingDatesPayload = (hotels || []).reduce((acc, h, originalIdx) => {
                if (!h || (h.compulsory !== true && h.selected !== true)) return acc;
                const breakdown = getHotelNightBreakdown(h, originalIdx);
                const key = getHotelDateKey(h, originalIdx);
                acc[key] = Array.isArray(breakdown.selected_dates) ? breakdown.selected_dates : [];
                return acc;
            }, {});
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
            document.getElementById('hotel_booking_dates_input').value = JSON.stringify(hotelBookingDatesPayload);
            document.getElementById('selected_attractions_input').value = JSON.stringify(attractionsPayload);
            document.getElementById('selected_guides_input').value = JSON.stringify(guidesPayload);
            document.getElementById('selected_restaurants_input').value = JSON.stringify(restaurantsPayload);
            document.getElementById('arrival_data_input').value = JSON.stringify(arrivalData || {});
            document.getElementById('departure_data_input').value = JSON.stringify(departureData || {});
            document.getElementById('transfer_data_input').value = JSON.stringify(transfersPayload);
            document.getElementById('supplementary_data_input').value = JSON.stringify(buildSupplementaryData(tourStartDate));

            // Track unselected add-ons (so they can be enabled during edit after booking).
            // Stored in a separate column `available_addons` on package_bookings.
            const isUnselectedAddon = (item) => !!(item && item.addon === true && item.selected !== true);
            const availableAddonsPayload = {
                hotels: (hotels || [])
                    .filter(isUnselectedAddon)
                    .map(h => ({ ...h, selected: false, tour_start_date: tourStartDate })),
                attractions: (attractions || [])
                    .filter(isUnselectedAddon)
                    .map(a => ({ ...a, selected: false, tour_start_date: tourStartDate })),
                restaurants: (restaurants || [])
                    .filter(isUnselectedAddon)
                    .map(r => ({ ...r, selected: false, tour_start_date: tourStartDate })),
            };
            document.getElementById('available_addons_input').value = JSON.stringify(availableAddonsPayload);
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
            selectedHotelDates = {};
            selectedPackagePriceData = {};
            selectedPackageType = '';
            loadedPackageDurationDays = 1;
            packageMinEndDateYmd = '';
            if (travelEndDateHtmlMin && endDateEl) endDateEl.setAttribute('min', travelEndDateHtmlMin);
            else if (endDateEl) endDateEl.removeAttribute('min');
            lastPricingTotals = { total_price: 0, final_price: 0, markup_type: 'flat', markup_amount: 0 };
            packageDetailsSection.style.display = 'none';
            createBookingBtn.disabled = true;
            selectedPackageIdInput.value = '';
            document.querySelectorAll('.legacy-attr-rest-wrap').forEach(w => { w.style.display = ''; });
            const dw = document.getElementById('dayWiseItineraryWrap');
            if (dw) dw.style.display = 'none';
            renderHotels();
            renderAttractions();
            renderRestaurants();
            renderArrivalDeparture();
            renderPricingSummary();
            document.getElementById('arrivalEnabled').value = '0';
            document.getElementById('arrivalPickupPortId').value = '';
            document.getElementById('arrivalDropoffHotelId').value = '';
            document.getElementById('departureEnabled').value = '0';
            document.getElementById('departurePickupHotelId').value = '';
            document.getElementById('departureDropoffPortId').value = '';
            syncHidden();
        }

        async function applyPackageData(pkg) {
            selectedPackageType = pkg.package_type || '';
            loadedPackageDurationDays = parseInt(pkg.duration_days, 10);
            if (isNaN(loadedPackageDurationDays) || loadedPackageDurationDays < 1) loadedPackageDurationDays = 1;

            hotels = initSectionSelections(Array.isArray(pkg.selected_hotels) ? pkg.selected_hotels : [], 'hotels');
            hotels = initHotelRoomDefaults(hotels);
            selectedHotelDates = {};
            const incomingHotelDates = (pkg && pkg.hotel_booking_dates && typeof pkg.hotel_booking_dates === 'object')
                ? pkg.hotel_booking_dates
                : {};
            hotels.forEach((h, idx) => {
                const key = getHotelDateKey(h, idx);
                let incoming = incomingHotelDates[key];
                if (!Array.isArray(incoming) && h && (h.hotel_id || h.id) != null) {
                    incoming = incomingHotelDates[String(h.hotel_id || h.id)];
                }
                if (Array.isArray(incoming)) {
                    selectedHotelDates[key] = incoming.slice();
                } else if (Array.isArray(h && h.hotel_booking_dates)) {
                    selectedHotelDates[key] = h.hotel_booking_dates.slice();
                }
            });
            initHotelDatesFromDefinitionDays();
            attractions = initSectionSelections(Array.isArray(pkg.selected_attractions) ? pkg.selected_attractions : [], 'attractions');
            guides = initOptionalSelections(Array.isArray(pkg.selected_guides) ? pkg.selected_guides : []);
            restaurants = initSectionSelections(Array.isArray(pkg.selected_restaurants) ? pkg.selected_restaurants : [], 'restaurants');
            arrivalData = pkg.arrival_data || {};
            departureData = pkg.departure_data || {};
            transfers = initOptionalSelections(Array.isArray(pkg.transfer_data) ? pkg.transfer_data : []);
            selectedPackageCity = pkg.city || '';
            selectedPackagePriceData = (pkg && pkg.price_data && typeof pkg.price_data === 'object') ? pkg.price_data : {};

            document.getElementById('arrivalEnabled').value = arrivalData && arrivalData.enabled ? '1' : '0';
            if (arrivalData && Array.isArray(arrivalData.items) && arrivalData.items.length) {
                const a0 = arrivalData.items[0];
                document.getElementById('arrivalPickupPortId').value = a0 && a0.pickup_port_id ? a0.pickup_port_id : '';
                document.getElementById('arrivalDropoffHotelId').value = a0 && a0.dropoff_hotel_id ? a0.dropoff_hotel_id : '';
            } else {
                document.getElementById('arrivalPickupPortId').value = arrivalData && arrivalData.pickup_port_id ? arrivalData.pickup_port_id : '';
                document.getElementById('arrivalDropoffHotelId').value = arrivalData && arrivalData.dropoff_hotel_id ? arrivalData.dropoff_hotel_id : '';
            }
            document.getElementById('departureEnabled').value = departureData && departureData.enabled ? '1' : '0';
            if (departureData && Array.isArray(departureData.items) && departureData.items.length) {
                const d0 = departureData.items[0];
                document.getElementById('departurePickupHotelId').value = d0 && d0.pickup_hotel_id ? d0.pickup_hotel_id : '';
                document.getElementById('departureDropoffPortId').value = d0 && d0.dropoff_port_id ? d0.dropoff_port_id : '';
            } else {
                document.getElementById('departurePickupHotelId').value = departureData && departureData.pickup_hotel_id ? departureData.pickup_hotel_id : '';
                document.getElementById('departureDropoffPortId').value = departureData && departureData.dropoff_port_id ? departureData.dropoff_port_id : '';
            }

            packageDetailsSection.style.display = '';
            createBookingBtn.disabled = false;
            selectedPackageIdInput.value = pkg.package_id || '';

            syncDepartureDaysToExtendedTour();
            renderAllSections();
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

        function travelStartDateReady() {
            return !!startDateEl.value;
        }

        async function fetchFilteredPackages() {
            resetPackageDetailsUI();
            endDateEl.value = '';
            packageMinEndDateYmd = '';
            if (travelEndDateHtmlMin) endDateEl.setAttribute('min', travelEndDateHtmlMin);
            packageSelect.innerHTML = '<option value="">Select travel start date to load packages</option>';
            packageSelect.disabled = true;

            if (!travelStartDateReady()) {
                packageFilterMessage.textContent = 'Select a travel start date to load packages.';
                return;
            }

            const params = new URLSearchParams({
                travel_start_date: startDateEl.value,
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
                packageSelect.innerHTML = '<option value="">No packages found</option>';
                packageFilterMessage.textContent = 'No packages are valid for this start date (including full tour within package expiry).';
                return;
            }

            packageSelect.innerHTML = '<option value="">Select package</option>' + packages.map(pkg => {
                const days = parseInt(pkg.duration_days, 10) || 0;
                const dayLabel = days === 1 ? '1 day' : (days + ' days');
                return '<option value="' + esc(pkg.package_id) + '" data-duration-days="' + esc(String(days)) + '">'
                    + esc(pkg.title) + ' (' + esc(dayLabel) + ') — ' + esc(pkg.destination) + ' / ' + esc(pkg.city)
                    + '</option>';
            }).join('');
            packageSelect.disabled = false;
            packageFilterMessage.textContent = packages.length + ' package(s) found.';

            if (prefilledPackageId != null && prefilledPackageId !== '' && packages.some(p => String(p.package_id) === String(prefilledPackageId))) {
                packageSelect.value = String(prefilledPackageId);
                const pre = packages.find(p => String(p.package_id) === String(prefilledPackageId));
                if (pre) syncEndDateFromPackageDuration(pre.duration_days);
                await loadPackageDetails(prefilledPackageId);
            }
        }

        ['arrivalEnabled', 'arrivalPickupPortId', 'arrivalDropoffHotelId', 'departureEnabled', 'departurePickupHotelId', 'departureDropoffPortId']
            .forEach(id => document.getElementById(id).addEventListener('change', syncHidden));

        startDateEl.addEventListener('change', fetchFilteredPackages);

        endDateEl.addEventListener('change', onTravelEndDateChanged);

        packageSelect.addEventListener('change', async function () {
            try {
                const opt = this.selectedOptions[0];
                const durAttr = opt ? opt.getAttribute('data-duration-days') : null;
                if (this.value && durAttr !== null && durAttr !== '') {
                    syncEndDateFromPackageDuration(durAttr);
                } else {
                    endDateEl.value = '';
                }
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

        (function bindArrivalDeparturePaxDelegation() {
            const section = document.getElementById('packageDetailsSection');
            if (!section) return;

            // Map an input element to its owning vehicle object (if any) and the field to mutate.
            // Returns null when the input is not a vehicle qty/pax field.
            function resolveVehicleField(t) {
                if (!t || !t.classList) return null;
                const cls = t.classList;
                const itemIdxRaw = t.getAttribute('data-item-idx');

                if (itemIdxRaw !== null && itemIdxRaw !== '') {
                    const itemIdx = parseInt(itemIdxRaw, 10);
                    if (isNaN(itemIdx) || itemIdx < 0) return null;
                    if (cls.contains('arrival-vehicle-qty') || cls.contains('arrival-vehicle-pax')) {
                        const items = arrivalData && arrivalData.items;
                        if (!Array.isArray(items) || !items[itemIdx] || !Array.isArray(items[itemIdx].vehicles)) return null;
                        const idx = parseInt(t.getAttribute('data-idx') || '-1', 10);
                        if (isNaN(idx) || idx < 0 || !items[itemIdx].vehicles[idx]) return null;
                        const field = cls.contains('arrival-vehicle-qty') ? 'booking_qty' : 'booking_pax';
                        return { vehicle: items[itemIdx].vehicles[idx], field };
                    }
                    if (cls.contains('departure-vehicle-qty') || cls.contains('departure-vehicle-pax')) {
                        const items = departureData && departureData.items;
                        if (!Array.isArray(items) || !items[itemIdx] || !Array.isArray(items[itemIdx].vehicles)) return null;
                        const idx = parseInt(t.getAttribute('data-idx') || '-1', 10);
                        if (isNaN(idx) || idx < 0 || !items[itemIdx].vehicles[idx]) return null;
                        const field = cls.contains('departure-vehicle-qty') ? 'booking_qty' : 'booking_pax';
                        return { vehicle: items[itemIdx].vehicles[idx], field };
                    }
                }

                let list = null;
                let field = null;
                if (cls.contains('arrival-vehicle-qty')) { list = arrivalData.vehicles; field = 'booking_qty'; }
                else if (cls.contains('arrival-vehicle-pax')) { list = arrivalData.vehicles; field = 'booking_pax'; }
                else if (cls.contains('departure-vehicle-qty')) { list = departureData.vehicles; field = 'booking_qty'; }
                else if (cls.contains('departure-vehicle-pax')) { list = departureData.vehicles; field = 'booking_pax'; }
                else return null;
                if (!Array.isArray(list)) return null;
                const idx = parseInt(t.getAttribute('data-idx') || '-1', 10);
                if (isNaN(idx) || idx < 0 || !list[idx]) return null;
                return { vehicle: list[idx], field };
            }

            // On `input`: update the data model live and refresh the pricing summary & hidden
            // payloads, but DO NOT re-render the arrival/departure cards (that would steal focus).
            // We update the "Total" display in place for the active vehicle card.
            section.addEventListener('input', function (e) {
                const resolved = resolveVehicleField(e.target);
                if (!resolved) return;
                const { vehicle, field } = resolved;
                const raw = parseInt(e.target.value || '0', 10);
                if (field === 'booking_qty') {
                    vehicle.booking_qty = !isNaN(raw) && raw > 0 ? raw : 1;
                } else {
                    let pax = !isNaN(raw) && raw >= 0 ? raw : 0;
                    const cap = parseInt(vehicle.seating_capacity, 10) || 0;
                    if (cap > 0 && pax > cap) pax = cap;
                    vehicle.booking_pax = pax;
                }
                const card = e.target.closest('[data-vehicle-card]');
                if (card) {
                    const totalEl = card.querySelector('.vehicle-total-display');
                    if (totalEl) totalEl.textContent = money(transferVehicleTotal(vehicle));
                }
                renderPricingSummary();
                syncHidden();
            });

            // On `change` (blur): rebuild the arrival/departure cards so that any clamped value
            // (e.g. pax capped to seating_capacity) is reflected back into the inputs.
            section.addEventListener('change', function (e) {
                const resolved = resolveVehicleField(e.target);
                if (!resolved) return;
                renderArrivalDeparture();
                renderPricingSummary();
                syncHidden();
            });
        })();

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
