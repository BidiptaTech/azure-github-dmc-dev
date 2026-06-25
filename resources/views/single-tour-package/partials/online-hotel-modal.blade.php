{{-- Online hotel booking modal (country-mapped supplier API) --}}
<div class="modal fade" id="onlineHotelModal" tabindex="-1" aria-labelledby="onlineHotelModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
                <h5 class="modal-title" id="onlineHotelModalLabel">
                    <i class="ri-global-line me-1"></i> Online Hotel Booking
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="background: #f8f9fa;">
                <div class="alert alert-info py-2 mb-3" style="font-size: 0.85rem;">
                    <i class="ri-information-line me-1"></i>
                    Hotels are fetched live from the supplier mapped to the selected city's country. Select dates, city and guests, then click <strong>Fetch Hotels</strong>.
                </div>

                {{-- Search criteria (maps to fetchHotels API) --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-building-line me-1"></i>City</label>
                                <select class="form-select form-select-sm" id="onlineHotelCity"></select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-calendar-line me-1"></i>Check-in</label>
                                <input type="date" class="form-control form-control-sm" id="onlineHotelCheckIn">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-calendar-line me-1"></i>Check-out</label>
                                <input type="date" class="form-control form-control-sm" id="onlineHotelCheckOut">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-group-line me-1"></i>Guests</label>
                                <div class="guest-selector">
                                    <div class="guest-display border rounded d-flex align-items-start justify-content-between" style="min-height: 34px; padding: 0.3rem 0.75rem; background: #f8f9fa; border: 1px solid #dee2e6 !important; border-radius: 8px;">
                                        <div class="guest-info d-flex flex-column gap-1" style="flex: 1;">
                                            <span id="onlineHotelGuestSummary" class="d-flex flex-column gap-1" style="font-size: 0.8rem;">
                                                <span class="text-muted small">—</span>
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="onlineHotelGuestEditBtn" style="border-radius: 6px; padding: 0.25rem 0.5rem; margin-left: 0.5rem; flex-shrink: 0;">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" id="onlineHotelPaxInfo" value="">
                            </div>
                        </div>
                        <div class="mt-3 d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-primary" id="onlineHotelFetchBtn">
                                <span class="spinner-border spinner-border-sm d-none me-1" id="onlineHotelFetchSpinner"></span>
                                <i class="ri-search-line me-1"></i> Fetch Hotels
                            </button>
                            <small class="text-muted" id="onlineHotelFetchStatus" style="font-size: 0.8rem;"></small>
                        </div>
                    </div>
                </div>

                {{-- Hotel selection (mirrors offline fields) --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row g-2 mb-2">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-hotel-line me-1"></i>Select Hotel</label>
                                <select class="form-select form-select-sm" id="onlineHotelSelect" disabled>
                                    <option value="">Fetch hotels first</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-door-open-line me-1"></i>Room Type</label>
                                <select class="form-select form-select-sm" id="onlineRoomTypeSelect" disabled>
                                    <option value="">Room Type</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-hotel-bed-line me-1"></i>Bed Type</label>
                                <select class="form-select form-select-sm" id="onlineBedTypeSelect" disabled>
                                    <option value="">Bed Type</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-group-line me-1"></i>Number of Persons</label>
                                <input type="number" class="form-control form-control-sm" id="onlineSelectedPersons" value="1" min="1" max="99">
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-restaurant-line me-1"></i>Meal Plan</label>
                                <select class="form-select form-select-sm" id="onlineMealPlanSelect">
                                    <option value="">Select Meal Plan</option>
                                    <option value="room only">Room Only</option>
                                    <option value="room with breakfast">Room with Breakfast</option>
                                    <option value="room with breakfast + dinner">Room with Breakfast + Dinner</option>
                                    <option value="room with breakfast + lunch">Room with Breakfast + Lunch</option>
                                    <option value="room with all meals (breakfast + lunch + dinner)">Room with All Meals</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-hotel-bed-2-line me-1"></i>Number of Rooms</label>
                                <input type="number" class="form-control form-control-sm" id="onlineNumberOfRooms" value="1" min="1" max="999">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-money-dollar-circle-line me-1"></i>Price</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">SGD</span>
                                    <input type="text" class="form-control" id="onlineRoomPriceDisplay" value="0.00" readonly style="background:#f8f9fa; text-align:right;">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold mb-2" style="font-size: 0.85rem;"><i class="ri-chat-quote-line me-1"></i>Remarks</label>
                            <textarea class="form-control form-control-sm" id="onlineHotelRemarks" rows="2" placeholder="Optional notes for this online hotel booking..."></textarea>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-semibold mb-2" style="font-size: 0.85rem;"><i class="ri-calendar-check-line me-1"></i>Select Hotel Nights</label>
                            <div id="onlineNightSelection" class="d-flex flex-wrap gap-2 mb-2"></div>
                            <div id="onlineNightSelectionSummary">
                                <small class="text-muted">No nights selected.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="onlineHotelAddBtn" disabled>
                    <i class="ri-add-line me-1"></i> Add Hotel
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Guest selector for online hotel search (editable pax) --}}
<div class="modal fade" id="onlineHotelGuestModal" tabindex="-1" aria-labelledby="onlineHotelGuestModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 10px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 1rem 1.25rem;">
                <h5 class="modal-title fw-bold d-flex align-items-center mb-0 text-white" id="onlineHotelGuestModalLabel" style="font-size: 1rem;">
                    <i class="ri-group-line me-2"></i> Select Guests for Hotel Search
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.25rem; background: #ffffff;">
                <p class="text-muted small mb-3" id="onlineHotelGuestLimitHint" style="font-size: 0.8rem;"></p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card" style="border: 1px solid #e9ecef; border-radius: 8px;">
                            <div class="card-header py-2" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                                <h6 class="mb-0 fw-semibold" style="font-size: 0.875rem;"><i class="ri-user-line me-1 text-primary"></i>Adults</h6>
                            </div>
                            <div class="card-body py-3">
                                <div class="guest-counter mb-3 text-center">
                                    <label class="form-label fw-semibold mb-2 d-block" style="font-size: 0.85rem;">Total Adults</label>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-outline-secondary btn-sm online-guest-adults-minus" style="width: 36px; height: 36px; border-radius: 6px;"><i class="ri-subtract-line"></i></button>
                                        <span class="mx-3 fw-bold" id="onlineModalAdults" style="font-size: 1.5rem; min-width: 32px;">1</span>
                                        <button type="button" class="btn btn-outline-secondary btn-sm online-guest-adults-plus" style="width: 36px; height: 36px; border-radius: 6px;"><i class="ri-add-line"></i></button>
                                    </div>
                                </div>
                                <div class="guest-counter mb-3 text-center">
                                    <label class="form-label fw-semibold mb-2 d-block" style="font-size: 0.85rem;">Male</label>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-online-guest="male" data-delta="-1" style="width: 36px; height: 36px; border-radius: 6px;"><i class="ri-subtract-line"></i></button>
                                        <span class="mx-3 fw-bold" id="onlineModalMale" style="font-size: 1.5rem; min-width: 32px;">1</span>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-online-guest="male" data-delta="1" style="width: 36px; height: 36px; border-radius: 6px;"><i class="ri-add-line"></i></button>
                                    </div>
                                </div>
                                <div class="guest-counter text-center">
                                    <label class="form-label fw-semibold mb-2 d-block" style="font-size: 0.85rem;">Female</label>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-online-guest="female" data-delta="-1" style="width: 36px; height: 36px; border-radius: 6px;"><i class="ri-subtract-line"></i></button>
                                        <span class="mx-3 fw-bold" id="onlineModalFemale" style="font-size: 1.5rem; min-width: 32px;">0</span>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-online-guest="female" data-delta="1" style="width: 36px; height: 36px; border-radius: 6px;"><i class="ri-add-line"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card" style="border: 1px solid #e9ecef; border-radius: 8px;">
                            <div class="card-header py-2" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                                <h6 class="mb-0 fw-semibold" style="font-size: 0.875rem;"><i class="ri-user-smile-line me-1 text-primary"></i>Children & Infants</h6>
                            </div>
                            <div class="card-body py-3">
                                <div class="guest-counter mb-3 text-center">
                                    <label class="form-label fw-semibold mb-2 d-block" style="font-size: 0.85rem;">Children <small class="text-muted fw-normal">(1–17)</small></label>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-online-guest="children" data-delta="-1" style="width: 36px; height: 36px; border-radius: 6px;"><i class="ri-subtract-line"></i></button>
                                        <span class="mx-3 fw-bold" id="onlineModalChildren" style="font-size: 1.5rem; min-width: 32px;">0</span>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-online-guest="children" data-delta="1" style="width: 36px; height: 36px; border-radius: 6px;"><i class="ri-add-line"></i></button>
                                    </div>
                                    <div id="onlineChildAgesSection" class="mt-3 text-start" style="display: none;">
                                        <label class="form-label fw-semibold mb-2 d-block" style="font-size: 0.8rem;">Child ages</label>
                                        <div id="onlineChildAgeDropdowns" class="d-flex flex-column gap-2"></div>
                                    </div>
                                </div>
                                <div class="guest-counter text-center">
                                    <label class="form-label fw-semibold mb-2 d-block" style="font-size: 0.85rem;">Infants <small class="text-muted fw-normal">(under 1)</small></label>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-online-guest="infants" data-delta="-1" style="width: 36px; height: 36px; border-radius: 6px;"><i class="ri-subtract-line"></i></button>
                                        <span class="mx-3 fw-bold" id="onlineModalInfants" style="font-size: 1.5rem; min-width: 32px;">0</span>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-online-guest="infants" data-delta="1" style="width: 36px; height: 36px; border-radius: 6px;"><i class="ri-add-line"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="onlineHotelGuestApplyBtn">Apply</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    function bindHotelSourceToggle() {
        $(document).off('change.hotelSource', 'input[name="hotelSourceType"]');
        $(document).on('change.hotelSource', 'input[name="hotelSourceType"]', function () {
            const isOnline = this.value === 'online';
            const offlinePanel = document.getElementById('offlineHotelPanel');
            if (offlinePanel) {
                offlinePanel.style.display = isOnline ? 'none' : '';
            }
            if (isOnline) {
                window.openOnlineHotelModal();
            } else {
                const modalEl = document.getElementById('onlineHotelModal');
                if (modalEl && window.bootstrap) {
                    bootstrap.Modal.getInstance(modalEl)?.hide();
                }
            }
        });
    }

    const fetchUrl = @json(route('fetch-online-hotels'));
    const csrfToken = @json(csrf_token());

    let onlineHotelsCache = [];
    let onlineSelectedNights = [];
    let onlineCurrentRooms = [];
    let onlineLastSupplierCode = '';
    let onlineGuestState = { male: 1, female: 0, children: 0, infants: 0, childAges: [] };

    function getMainGuestLimits() {
        const mainMale = parseInt(document.getElementById('male')?.value, 10) || 0;
        const mainFemale = parseInt(document.getElementById('female')?.value, 10) || 0;
        const mainAdultsField = parseInt(document.getElementById('adults')?.value, 10) || 1;
        const mainChildren = parseInt(document.getElementById('children')?.value, 10) || 0;
        const mainInfants = parseInt(document.getElementById('infants')?.value, 10) || 0;

        const maxAdults = Math.max(1, (mainMale + mainFemale) > 0 ? (mainMale + mainFemale) : mainAdultsField);

        return {
            adults: maxAdults,
            children: mainChildren,
            infants: mainInfants,
            totalPax: maxAdults + mainChildren + mainInfants,
        };
    }

    function getOnlineTotalAdults() {
        return Math.max(1, onlineGuestState.male + onlineGuestState.female);
    }

    function notifyOnlineGuestLimit(message) {
        if (typeof showNotification === 'function') {
            showNotification(message, 'warning');
        }
    }

    function clampOnlineGuestState() {
        const limits = getMainGuestLimits();

        onlineGuestState.children = Math.min(Math.max(0, onlineGuestState.children), limits.children);
        onlineGuestState.infants = Math.min(Math.max(0, onlineGuestState.infants), limits.infants);

        onlineGuestState.male = Math.max(0, onlineGuestState.male);
        onlineGuestState.female = Math.max(0, onlineGuestState.female);

        let totalAdults = getOnlineTotalAdults();
        if (totalAdults > limits.adults) {
            let excess = totalAdults - limits.adults;
            const maleReduce = Math.min(onlineGuestState.male, excess);
            onlineGuestState.male -= maleReduce;
            excess -= maleReduce;
            onlineGuestState.female = Math.max(0, onlineGuestState.female - excess);
        }

        if (getOnlineTotalAdults() < 1) {
            onlineGuestState.male = Math.min(1, limits.adults);
            onlineGuestState.female = 0;
        }
    }

    function loadOnlineGuestStateFromMain() {
        const mainMale = parseInt(document.getElementById('male')?.value, 10) || 0;
        const mainFemale = parseInt(document.getElementById('female')?.value, 10) || 0;
        const mainAdults = parseInt(document.getElementById('adults')?.value, 10) || 1;
        const mainChildren = parseInt(document.getElementById('children')?.value, 10) || 0;
        const mainInfants = parseInt(document.getElementById('infants')?.value, 10) || 0;

        onlineGuestState.male = mainMale;
        onlineGuestState.female = mainFemale;
        if (mainMale + mainFemale === 0) {
            onlineGuestState.male = mainAdults;
            onlineGuestState.female = 0;
        }
        onlineGuestState.children = mainChildren;
        onlineGuestState.infants = mainInfants;

        try {
            const raw = document.getElementById('child_ages')?.value || '[]';
            const parsed = JSON.parse(raw);
            onlineGuestState.childAges = Array.isArray(parsed) ? parsed.map(function (a) { return parseInt(a, 10); }).filter(function (a) { return !isNaN(a); }) : [];
        } catch (e) {
            onlineGuestState.childAges = [];
        }

        clampOnlineGuestState();
    }

    function getGuestCounts() {
        const male = Math.max(0, onlineGuestState.male);
        const female = Math.max(0, onlineGuestState.female);
        const adults = Math.max(1, male + female);
        const children = Math.max(0, onlineGuestState.children);
        const infants = Math.max(0, onlineGuestState.infants);
        return { adults, male, female, children, infants };
    }

    function syncOnlineGuestDerivedFields() {
        renderOnlineGuestSummary();
        const paxEl = document.getElementById('onlineHotelPaxInfo');
        if (paxEl) paxEl.value = buildPaxInfo();
        const personsEl = document.getElementById('onlineSelectedPersons');
        if (personsEl) {
            const { adults, children } = getGuestCounts();
            const limits = getMainGuestLimits();
            const maxPersons = limits.adults + limits.children;
            personsEl.max = String(Math.max(1, maxPersons));
            personsEl.value = Math.min(Math.max(1, adults + children), maxPersons);
        }
    }

    function renderOnlineGuestSummary() {
        const { adults, male, female, children, infants } = getGuestCounts();
        const guestSummary = document.getElementById('onlineHotelGuestSummary');
        if (!guestSummary) return;

        let summaryHTML = '<span class="d-flex align-items-center gap-1">';
        summaryHTML += '<span class="badge d-flex align-items-center gap-1" style="background: #667eea; border-radius: 4px; font-size: 0.7rem; padding: 0.2rem 0.4rem;" title="Adults"><i class="ri-group-line" style="font-size: 0.75rem;"></i><span>' + adults + ' Adults</span></span>';
        summaryHTML += ' <span class="badge d-flex align-items-center gap-1" style="background: #667eea; border-radius: 4px; font-size: 0.7rem; padding: 0.2rem 0.4rem; opacity: 0.8;" title="Male"><i class="ri-men-line" style="font-size: 0.75rem;"></i><span>' + male + '</span></span>';
        summaryHTML += ' <span class="badge d-flex align-items-center gap-1" style="background: #667eea; border-radius: 4px; font-size: 0.7rem; padding: 0.2rem 0.4rem; opacity: 0.8;" title="Female"><i class="ri-women-line" style="font-size: 0.75rem;"></i><span>' + female + '</span></span>';
        summaryHTML += '</span>';
        summaryHTML += '<span class="d-flex align-items-center gap-1">';
        summaryHTML += ' <span class="badge d-flex align-items-center gap-1" style="background: #28a745; border-radius: 4px; font-size: 0.7rem; padding: 0.2rem 0.4rem;" title="Children"><i class="ri-user-smile-line" style="font-size: 0.75rem;"></i><span>' + children + '</span></span>';
        summaryHTML += ' <span class="badge d-flex align-items-center gap-1" style="background: #ffc107; color: #000; border-radius: 4px; font-size: 0.7rem; padding: 0.2rem 0.4rem;" title="Infants"><i class="ri-user-heart-line" style="font-size: 0.75rem;"></i><span>' + infants + '</span></span>';
        summaryHTML += '</span>';
        guestSummary.innerHTML = summaryHTML;
    }

    function getAdultsChildren() {
        const { adults, children } = getGuestCounts();
        return { adults, children };
    }

    function buildPaxInfo() {
        const { adults, children } = getAdultsChildren();
        return adults + '|' + children;
    }

    function renderOnlineGuestModalCounters() {
        const { adults, male, female, children, infants } = getGuestCounts();
        const setText = function (id, val) {
            const el = document.getElementById(id);
            if (el) el.textContent = String(val);
        };
        setText('onlineModalAdults', adults);
        setText('onlineModalMale', male);
        setText('onlineModalFemale', female);
        setText('onlineModalChildren', children);
        setText('onlineModalInfants', infants);
    }

    function updateOnlineChildAgeDropdowns() {
        const section = document.getElementById('onlineChildAgesSection');
        const container = document.getElementById('onlineChildAgeDropdowns');
        if (!section || !container) return;

        const count = Math.max(0, onlineGuestState.children);
        if (count === 0) {
            section.style.display = 'none';
            container.innerHTML = '';
            onlineGuestState.childAges = [];
            return;
        }

        section.style.display = '';
        while (onlineGuestState.childAges.length < count) {
            onlineGuestState.childAges.push(8);
        }
        onlineGuestState.childAges = onlineGuestState.childAges.slice(0, count);

        container.innerHTML = '';
        for (let i = 0; i < count; i++) {
            let ageOptions = '';
            for (let age = 1; age <= 17; age++) {
                ageOptions += '<option value="' + age + '"' + (onlineGuestState.childAges[i] === age ? ' selected' : '') + '>' + age + ' years</option>';
            }
            const row = document.createElement('div');
            row.className = 'd-flex align-items-center gap-2';
            row.innerHTML = '<label class="text-success fw-semibold mb-0" style="min-width: 70px; font-size: 0.8rem;">Child ' + (i + 1) + ':</label>' +
                '<select class="form-select form-select-sm online-child-age-select" data-child-index="' + i + '">' + ageOptions + '</select>';
            container.appendChild(row);
        }
    }

    function adjustOnlineGuest(type, delta) {
        delta = parseInt(delta, 10) || 0;
        const limits = getMainGuestLimits();

        if (type === 'male') {
            if (delta > 0) {
                if (onlineGuestState.female < 1) {
                    notifyOnlineGuestLimit('Cannot increase male without reducing female. Use Female − or increase total adults.');
                    return;
                }
                onlineGuestState.male += 1;
                onlineGuestState.female -= 1;
            } else if (delta < 0) {
                if (onlineGuestState.male < 1) {
                    return;
                }
                onlineGuestState.male -= 1;
                onlineGuestState.female += 1;
            }
        } else if (type === 'female') {
            if (delta > 0) {
                if (onlineGuestState.male < 1) {
                    notifyOnlineGuestLimit('Cannot increase female without reducing male. Use Male − or increase total adults.');
                    return;
                }
                onlineGuestState.female += 1;
                onlineGuestState.male -= 1;
            } else if (delta < 0) {
                if (onlineGuestState.female < 1) {
                    return;
                }
                onlineGuestState.female -= 1;
                onlineGuestState.male += 1;
            }
        } else if (type === 'children') {
            if (delta > 0 && onlineGuestState.children >= limits.children) {
                notifyOnlineGuestLimit('Children cannot exceed tour guests (' + limits.children + ').');
                return;
            }
            onlineGuestState.children = Math.max(0, onlineGuestState.children + delta);
            updateOnlineChildAgeDropdowns();
        } else if (type === 'infants') {
            if (delta > 0 && onlineGuestState.infants >= limits.infants) {
                notifyOnlineGuestLimit('Infants cannot exceed tour guests (' + limits.infants + ').');
                return;
            }
            onlineGuestState.infants = Math.max(0, onlineGuestState.infants + delta);
        }

        clampOnlineGuestState();
        renderOnlineGuestModalCounters();
    }

    function adjustOnlineAdults(delta) {
        delta = parseInt(delta, 10) || 0;
        const limits = getMainGuestLimits();
        const currentTotal = getOnlineTotalAdults();
        const newTotal = currentTotal + delta;

        if (newTotal < 1) {
            return;
        }
        if (newTotal > limits.adults) {
            notifyOnlineGuestLimit('Adults cannot exceed tour guests (' + limits.adults + ').');
            return;
        }

        if (delta > 0) {
            onlineGuestState.male += delta;
        } else {
            let toRemove = -delta;
            const fromMale = Math.min(onlineGuestState.male, toRemove);
            onlineGuestState.male -= fromMale;
            toRemove -= fromMale;
            onlineGuestState.female = Math.max(0, onlineGuestState.female - toRemove);
        }

        clampOnlineGuestState();
        renderOnlineGuestModalCounters();
    }

    function renderOnlineGuestLimitHint() {
        const el = document.getElementById('onlineHotelGuestLimitHint');
        if (!el) return;
        const limits = getMainGuestLimits();
        el.textContent = 'Total adults = male + female (max ' + limits.adults + ' from tour). Children max ' + limits.children + ', infants max ' + limits.infants + '.';
    }

    function openOnlineHotelGuestSelector() {
        renderOnlineGuestModalCounters();
        updateOnlineChildAgeDropdowns();
        renderOnlineGuestLimitHint();
        const modalEl = document.getElementById('onlineHotelGuestModal');
        if (!modalEl || !window.bootstrap) return;
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function applyOnlineGuestSelection() {
        clampOnlineGuestState();
        const childAgeSelects = document.querySelectorAll('#onlineChildAgeDropdowns .online-child-age-select');
        if (onlineGuestState.children > 0) {
            if (childAgeSelects.length !== onlineGuestState.children) {
                if (typeof showNotification === 'function') {
                    showNotification('Please select ages for all children.', 'warning');
                } else {
                    alert('Please select ages for all children.');
                }
                return;
            }
            onlineGuestState.childAges = [];
            childAgeSelects.forEach(function (select) {
                onlineGuestState.childAges.push(parseInt(select.value, 10) || 8);
            });
        } else {
            onlineGuestState.childAges = [];
        }

        syncOnlineGuestDerivedFields();

        const guestModal = document.getElementById('onlineHotelGuestModal');
        if (guestModal && window.bootstrap) {
            bootstrap.Modal.getInstance(guestModal)?.hide();
        }
    }

    function syncOnlineSearchDefaults() {
        loadOnlineGuestStateFromMain();
        const citySelect = document.getElementById('hotelCitySelect');
        const onlineCity = document.getElementById('onlineHotelCity');
        if (citySelect && onlineCity) {
            onlineCity.innerHTML = '';
            Array.from(citySelect.options).forEach(function (opt) {
                if (!opt.value) return;
                const o = document.createElement('option');
                o.value = opt.value;
                o.textContent = opt.textContent;
                onlineCity.appendChild(o);
            });
            if (citySelect.value) {
                onlineCity.value = citySelect.value;
            }
        }

        const planStart = (typeof getHotelNightPlanStart === 'function') ? getHotelNightPlanStart() : (window.tourStartDate || '');
        const planNights = (typeof getHotelNightPlanNightCount === 'function') ? getHotelNightPlanNightCount() : (window.tourNights || 0);
        const checkInEl = document.getElementById('onlineHotelCheckIn');
        const checkOutEl = document.getElementById('onlineHotelCheckOut');
        if (checkInEl && planStart) {
            checkInEl.value = moment(planStart).format('YYYY-MM-DD');
        }
        if (checkOutEl && planStart && planNights > 0) {
            checkOutEl.value = moment(planStart).add(planNights, 'days').format('YYYY-MM-DD');
        }

        renderOnlineGuestSummary();
        const paxEl = document.getElementById('onlineHotelPaxInfo');
        if (paxEl) paxEl.value = buildPaxInfo();

        const personsEl = document.getElementById('onlineSelectedPersons');
        if (personsEl) {
            const { adults, children } = getGuestCounts();
            const limits = getMainGuestLimits();
            const maxPersons = limits.adults + limits.children;
            personsEl.max = String(Math.max(1, maxPersons));
            personsEl.value = Math.min(Math.max(1, adults + children), maxPersons);
        }

        generateOnlineNightButtons();
    }

    function generateOnlineNightButtons() {
        const wrap = document.getElementById('onlineNightSelection');
        if (!wrap) return;
        wrap.innerHTML = '';
        onlineSelectedNights = [];

        const planNights = (typeof getHotelNightPlanNightCount === 'function') ? getHotelNightPlanNightCount() : (window.tourNights || 0);
        const planStart = (typeof getHotelNightPlanStart === 'function') ? getHotelNightPlanStart() : (window.tourStartDate || '');
        if (!planNights || !planStart) {
            document.getElementById('onlineNightSelectionSummary').innerHTML = '<small class="text-muted">Set travel dates first.</small>';
            return;
        }

        for (let i = 1; i <= planNights; i++) {
            const startDate = moment(planStart).add(i - 1, 'days');
            const endDate = moment(planStart).add(i, 'days');
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-primary btn-sm online-night-btn';
            btn.dataset.night = String(i);
            btn.innerHTML = '<strong>Night ' + i + '</strong><br><small>' + startDate.format('MMM DD') + ' - ' + endDate.format('MMM DD') + '</small>';
            btn.addEventListener('click', function () {
                toggleOnlineNight(parseInt(this.dataset.night, 10));
            });
            wrap.appendChild(btn);
        }
        updateOnlineNightSummary();
    }

    function toggleOnlineNight(nightNum) {
        const idx = onlineSelectedNights.indexOf(nightNum);
        if (idx >= 0) {
            onlineSelectedNights.splice(idx, 1);
        } else {
            onlineSelectedNights.push(nightNum);
            onlineSelectedNights.sort((a, b) => a - b);
        }
        document.querySelectorAll('.online-night-btn').forEach(function (btn) {
            const n = parseInt(btn.dataset.night, 10);
            btn.classList.toggle('active', onlineSelectedNights.includes(n));
            btn.classList.toggle('btn-primary', onlineSelectedNights.includes(n));
            btn.classList.toggle('btn-outline-primary', !onlineSelectedNights.includes(n));
        });
        updateOnlineNightSummary();
        validateOnlineAddBtn();
    }

    function updateOnlineNightSummary() {
        const el = document.getElementById('onlineNightSelectionSummary');
        if (!el) return;
        if (!onlineSelectedNights.length) {
            el.innerHTML = '<small class="text-muted">No nights selected.</small>';
            return;
        }
        el.innerHTML = '<small class="text-success">' + onlineSelectedNights.length + ' night(s) selected: ' + onlineSelectedNights.join(', ') + '</small>';
    }

    function hotelLabel(h) {
        return h.hotelName || h.name || h.hotel_name || h.title || ('Hotel #' + (h.hotelId || h.id || ''));
    }

    function hotelStarLabel(h) {
        const star = h.starRating || h.star_rating || '';
        if (!star) return '';
        const n = parseInt(star, 10);
        if (Number.isFinite(n) && n > 0) {
            return n + ' Star';
        }
        return String(star);
    }

    function hotelLowestPrice(h) {
        const raw = h.raw || {};
        const apiMin = toNumber(h.minRate ?? h.min_rate ?? raw.minRate ?? 0);
        if (apiMin > 0) {
            return apiMin;
        }

        const rooms = h.rooms || h.roomTypes || h.room_types || [];
        if (Array.isArray(rooms) && rooms.length) {
            let min = Infinity;
            rooms.forEach(function (room) {
                const p = roomDisplayPrice(room);
                if (p > 0 && p < min) {
                    min = p;
                }
            });
            if (min !== Infinity) {
                return min;
            }
        }

        return toNumber(
            h.lowestPrice ??
            h.price ??
            h.totalPrice ??
            h.amount ??
            0
        );
    }

    function hotelCurrency(h) {
        return String(h.currency || h.raw?.currency || 'SGD').trim() || 'SGD';
    }

    function hotelSelectLabel(h) {
        const name = hotelLabel(h);
        const star = hotelStarLabel(h);
        const price = hotelLowestPrice(h);
        const currency = hotelCurrency(h);

        let label = name;
        if (star) {
            label += ' (' + star + ')';
        }
        if (price > 0) {
            label += ' - ' + currency + ' ' + price.toFixed(2);
        }
        return label;
    }

    function hotelId(h) {
        return String(h.hotelId || h.hotel_id || h.id || h.hotel_unique_id || '');
    }

    function toNumber(value) {
        const n = parseFloat(value);
        return Number.isFinite(n) ? n : 0;
    }

    function roomMealLabel(room) {
        const mealPlanName = String(room?.mealPlanName || room?.meal_plan || '').toUpperCase();
        if (mealPlanName === 'EP' || mealPlanName === 'RO' || mealPlanName.includes('ROOM ONLY')) return 'room only';
        if (mealPlanName === 'CP' || mealPlanName === 'BB' || mealPlanName.includes('BREAKFAST')) return 'room with breakfast';
        if (mealPlanName === 'MAP' || mealPlanName === 'HB' || mealPlanName.includes('HALF BOARD')) return 'room with breakfast + dinner';
        if (mealPlanName === 'AP' || mealPlanName === 'FB' || mealPlanName === 'AI' || mealPlanName.includes('FULL BOARD') || mealPlanName.includes('ALL INCLUSIVE')) return 'room with all meals (breakfast + lunch + dinner)';
        if (room?.breakFast === true || room?.breakfast_included === true) return 'room with breakfast';
        return '';
    }

    function roomDisplayPrice(room) {
        const p = room?.currencyConvertedPrice || room?.price || {};
        return toNumber(
            p.actual ??
            p.discounted ??
            p.total ??
            room?.actual ??
            room?.totalPrice ??
            room?.amount ??
            0
        );
    }

    function extractHotelsFromResponse(data) {
        if (!data || typeof data !== 'object') return [];
        if (Array.isArray(data.hotels) && data.hotels.length) return data.hotels;
        if (Array.isArray(data?.provider?.HotelDetails)) return data.provider.HotelDetails;
        if (Array.isArray(data?.provider?.hotels)) return data.provider.hotels;
        if (Array.isArray(data?.provider?.data)) return data.provider.data;
        if (Array.isArray(data?.provider?.results)) return data.provider.results;
        if (Array.isArray(data?.HotelDetails)) return data.HotelDetails;
        return [];
    }

    function populateOnlineHotels(hotels) {
        onlineHotelsCache = Array.isArray(hotels) ? hotels.slice() : [];
        onlineHotelsCache.sort(function (a, b) {
            const pa = hotelLowestPrice(a) || Infinity;
            const pb = hotelLowestPrice(b) || Infinity;
            return pa - pb;
        });

        const sel = document.getElementById('onlineHotelSelect');
        sel.innerHTML = '<option value="">Select hotel</option>';
        onlineHotelsCache.forEach(function (h, idx) {
            const opt = document.createElement('option');
            opt.value = hotelId(h) || String(idx);
            opt.textContent = hotelSelectLabel(h);
            opt.dataset.index = String(idx);
            opt.dataset.price = String(hotelLowestPrice(h));
            sel.appendChild(opt);
        });
        sel.disabled = onlineHotelsCache.length === 0;
        document.getElementById('onlineRoomTypeSelect').innerHTML = '<option value="">Room Type</option>';
        document.getElementById('onlineBedTypeSelect').innerHTML = '<option value="">Bed Type</option>';
        document.getElementById('onlineRoomTypeSelect').disabled = true;
        document.getElementById('onlineBedTypeSelect').disabled = true;
        validateOnlineAddBtn();
    }

    function inferBedTypeFromRoom(room) {
        const direct = room?.bedType || room?.bed_type;
        if (direct) return String(direct);

        const rawRoomName = room?.raw?.room?.name || room?.onlineHotelRaw?.raw?.room?.name || '';
        const name = String(rawRoomName || room?.roomName || room?.room_name || room?.roomType || '').toUpperCase();
        if (!name) return '';

        if (name.includes('KING')) return 'King Bed';
        if (name.includes('QUEEN')) return 'Queen Bed';
        if (name.includes('DOUBLE SINGLE USE') || name.includes('SINGLE USE')) return 'Double (Single Use)';
        if (name.includes('DOUBLE OR TWIN')) return 'Double or Twin';
        if (name.includes('TWIN')) return 'Twin Beds';
        if (name.includes('DOUBLE')) return 'Double Bed';
        if (name.includes('SINGLE')) return 'Single Bed';
        if (name.includes('SUITE')) return 'Suite';

        return 'Standard';
    }

    function populateOnlineBedTypes(room) {
        const bedSel = document.getElementById('onlineBedTypeSelect');
        if (!bedSel) return;

        bedSel.innerHTML = '<option value="">Bed Type</option>';
        if (!room) {
            bedSel.disabled = true;
            return;
        }

        const bedTypes = [];
        const primaryBed = inferBedTypeFromRoom(room);
        const extraBed = room.extraBedType || room.extra_bed_type;
        if (primaryBed) bedTypes.push(String(primaryBed));
        if (extraBed && !bedTypes.includes(String(extraBed))) {
            bedTypes.push(String(extraBed));
        }

        if (!bedTypes.length) {
            bedSel.disabled = true;
            return;
        }

        bedTypes.forEach(function (name) {
            const opt = document.createElement('option');
            opt.value = name;
            opt.textContent = name;
            bedSel.appendChild(opt);
        });
        bedSel.disabled = false;
        bedSel.value = bedTypes[0];
    }

    function applySelectedRoomDetails(room) {
        if (!room) return;

        const price = roomDisplayPrice(room);
        if (price > 0) {
            document.getElementById('onlineRoomPriceDisplay').value = price.toFixed(2);
        }

        populateOnlineBedTypes(room);

        const meal = roomMealLabel(room);
        const mealSel = document.getElementById('onlineMealPlanSelect');
        if (mealSel && meal) {
            mealSel.value = meal;
        }
    }

    function populateOnlineRooms(hotel) {
        const roomSel = document.getElementById('onlineRoomTypeSelect');
        const bedSel = document.getElementById('onlineBedTypeSelect');
        roomSel.innerHTML = '<option value="">Room Type</option>';
        bedSel.innerHTML = '<option value="">Bed Type</option>';
        bedSel.disabled = true;

        const rooms = hotel.rooms || hotel.roomTypes || hotel.room_types || [];
        onlineCurrentRooms = Array.isArray(rooms) ? rooms : [];
        if (Array.isArray(rooms) && rooms.length) {
            rooms.forEach(function (room, idx) {
                const opt = document.createElement('option');
                const name = room.roomName || room.room_name || room.roomType || room.room_type || room.name || room.type || ('Room ' + (idx + 1));
                opt.value = name;
                opt.textContent = name;
                opt.dataset.index = String(idx);
                const price = roomDisplayPrice(room);
                if (price > 0) {
                    opt.dataset.price = String(price);
                }
                roomSel.appendChild(opt);
            });
            roomSel.disabled = false;
            roomSel.selectedIndex = 1;
            applySelectedRoomDetails(onlineCurrentRooms[0]);
        } else {
            roomSel.disabled = true;

            const beds = hotel.beds || hotel.bedTypes || [];
            if (Array.isArray(beds) && beds.length) {
                beds.forEach(function (bed) {
                    const opt = document.createElement('option');
                    const name = bed.bedType || bed.bed_type || bed.name || '';
                    if (!name) return;
                    opt.value = name;
                    opt.textContent = name;
                    bedSel.appendChild(opt);
                });
                bedSel.disabled = bedSel.options.length <= 1;
            }

            const hotelPrice = toNumber(hotel.price || hotel.totalPrice || hotel.lowestPrice || hotel.amount || 0);
            document.getElementById('onlineRoomPriceDisplay').value = hotelPrice.toFixed(2);
        }
    }

    function validateOnlineAddBtn() {
        const btn = document.getElementById('onlineHotelAddBtn');
        const ok = document.getElementById('onlineHotelSelect')?.value &&
            onlineSelectedNights.length > 0;
        if (btn) btn.disabled = !ok;
    }

    window.openOnlineHotelModal = function () {
        syncOnlineSearchDefaults();
        const modalEl = document.getElementById('onlineHotelModal');
        if (!modalEl) {
            console.error('onlineHotelModal element not found');
            return;
        }
        if (!window.bootstrap || !window.bootstrap.Modal) {
            console.error('Bootstrap Modal is not available');
            return;
        }
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    bindHotelSourceToggle();
    $(function () { bindHotelSourceToggle(); });

    document.getElementById('onlineHotelGuestEditBtn')?.addEventListener('click', openOnlineHotelGuestSelector);
    document.getElementById('onlineHotelGuestApplyBtn')?.addEventListener('click', applyOnlineGuestSelection);
    document.querySelectorAll('[data-online-guest]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            adjustOnlineGuest(this.dataset.onlineGuest, this.dataset.delta);
        });
    });
    document.querySelector('.online-guest-adults-minus')?.addEventListener('click', function () { adjustOnlineAdults(-1); });
    document.querySelector('.online-guest-adults-plus')?.addEventListener('click', function () { adjustOnlineAdults(1); });

    document.getElementById('onlineHotelFetchBtn')?.addEventListener('click', function () {
        const city = document.getElementById('onlineHotelCity')?.value;
        const checkIn = document.getElementById('onlineHotelCheckIn')?.value;
        const checkOut = document.getElementById('onlineHotelCheckOut')?.value;
        const paxInfo = document.getElementById('onlineHotelPaxInfo')?.value || buildPaxInfo();
        const statusEl = document.getElementById('onlineHotelFetchStatus');
        const spinner = document.getElementById('onlineHotelFetchSpinner');

        if (!city || !checkIn || !checkOut) {
            if (typeof showNotification === 'function') {
                showNotification('City, check-in and check-out are required.', 'warning');
            }
            return;
        }

        if (spinner) spinner.classList.remove('d-none');
        if (statusEl) statusEl.textContent = 'Fetching hotels...';

        fetch(fetchUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ checkIn: checkIn, checkOut: checkOut, city: city, paxInfo: paxInfo })
        })
        .then(r => r.json())
        .then(data => {
            if (data && data.success) {
                onlineLastSupplierCode = data.supplier_code || data.supplier_name || '';
                const hotels = extractHotelsFromResponse(data);
                populateOnlineHotels(hotels);
                const supplierLabel = data.supplier_name || data.supplier_code || 'supplier';
                if (statusEl) {
                    statusEl.textContent = hotels.length + ' hotel(s) via ' + supplierLabel + '.';
                }
                if (typeof showNotification === 'function') {
                    showNotification('Online hotels loaded successfully.', 'success');
                }
            } else {
                populateOnlineHotels([]);
                if (statusEl) statusEl.textContent = data?.message || 'No hotels found.';
                if (typeof showNotification === 'function') {
                    showNotification(data?.message || 'Failed to fetch online hotels.', 'error');
                }
            }
        })
        .catch(function (err) {
            populateOnlineHotels([]);
            if (statusEl) statusEl.textContent = 'Request failed.';
            console.error(err);
            if (typeof showNotification === 'function') {
                showNotification('Error fetching online hotels.', 'error');
            }
        })
        .finally(function () {
            if (spinner) spinner.classList.add('d-none');
        });
    });

    document.getElementById('onlineHotelSelect')?.addEventListener('change', function () {
        const idx = this.selectedIndex >= 0 ? this.options[this.selectedIndex].dataset.index : null;
        const hotel = idx !== null && idx !== undefined ? onlineHotelsCache[parseInt(idx, 10)] : null;
        if (hotel) populateOnlineRooms(hotel);
        validateOnlineAddBtn();
    });

    document.getElementById('onlineRoomTypeSelect')?.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const roomIdx = opt?.dataset?.index !== undefined ? parseInt(opt.dataset.index, 10) : -1;
        const room = roomIdx >= 0 ? onlineCurrentRooms[roomIdx] : null;
        if (room) {
            applySelectedRoomDetails(room);
        } else if (opt && opt.dataset.price) {
            document.getElementById('onlineRoomPriceDisplay').value = parseFloat(opt.dataset.price).toFixed(2);
        }
    });

    document.getElementById('onlineHotelAddBtn')?.addEventListener('click', function () {
        if (typeof selectedHotels === 'undefined' || typeof displaySelectedHotels !== 'function') {
            alert('Hotel list is not ready. Please reload the page.');
            return;
        }
        if (!tourStartDate) {
            alert('Please add travel dates first before adding hotels.');
            return;
        }

        const hotelSel = document.getElementById('onlineHotelSelect');
        const idx = hotelSel.selectedIndex >= 0 ? parseInt(hotelSel.options[hotelSel.selectedIndex].dataset.index, 10) : -1;
        const hotelRaw = idx >= 0 ? onlineHotelsCache[idx] : null;
        if (!hotelRaw) return;

        const nightNumbers = onlineSelectedNights.slice().sort((a, b) => a - b);
        const planStart = (typeof getHotelNightPlanStart === 'function') ? getHotelNightPlanStart() : tourStartDate;
        const startNight = Math.min(...nightNumbers);
        const endNight = Math.max(...nightNumbers);
        const checkInDate = moment(planStart).add(startNight - 1, 'days');
        const checkOutDate = moment(planStart).add(endNight, 'days');
        const roomType = document.getElementById('onlineRoomTypeSelect')?.value || 'Standard';
        const bedType = document.getElementById('onlineBedTypeSelect')?.value || '';
        const mealPlan = document.getElementById('onlineMealPlanSelect')?.value || 'Not specified';
        const selectedPersons = parseInt(document.getElementById('onlineSelectedPersons')?.value, 10) || 1;
        const numberOfRooms = parseInt(document.getElementById('onlineNumberOfRooms')?.value, 10) || 1;
        const price = parseFloat(document.getElementById('onlineRoomPriceDisplay')?.value) || 0;
        const cityName = document.getElementById('onlineHotelCity')?.selectedOptions?.[0]?.textContent || '';
        const remarks = document.getElementById('onlineHotelRemarks')?.value || '';

        const hotelData = {
            id: hotelId(hotelRaw) || ('online-' + Date.now()),
            name: hotelLabel(hotelRaw),
            roomType: roomType,
            bedType: bedType || null,
            selectedPersons: selectedPersons,
            price: price,
            combinedRoomTotal: price,
            roomPriceManuallyEdited: true,
            customRoomPrice: price,
            mealPlan: mealPlan,
            mealPrices: { breakfast_price: 0, lunch_price: 0, dinner_price: 0 },
            numberOfRooms: numberOfRooms,
            nights: nightNumbers,
            checkInDate: checkInDate.format('MMM DD'),
            checkOutDate: checkOutDate.format('MMM DD'),
            totalNights: nightNumbers.length,
            remarks: remarks,
            isOnlineHotel: true,
            onlineHotelSource: onlineLastSupplierCode || hotelRaw.supplier_code || 'online',
            onlineHotelRaw: hotelRaw,
            city: cityName,
            pricePerNight: nightNumbers.length ? price / nightNumbers.length : price
        };

        selectedHotels.push(hotelData);
        if (typeof lastSelectedHotelId !== 'undefined') {
            lastSelectedHotelId = hotelData.id;
        }
        displaySelectedHotels();
        if (typeof window.updateHotelDataField === 'function') {
            window.updateHotelDataField();
        }
        if (typeof showNotification === 'function') {
            showNotification('Online hotel "' + hotelData.name + '" added successfully.', 'success');
        }

        const modalEl = document.getElementById('onlineHotelModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getInstance(modalEl)?.hide();
        }
        document.querySelector('input[name="hotelSourceType"][value="offline"]')?.click();
    });

    document.getElementById('onlineHotelModal')?.addEventListener('hidden.bs.modal', function () {
        const onlineRadio = document.querySelector('input[name="hotelSourceType"][value="online"]');
        if (onlineRadio && onlineRadio.checked) {
            document.querySelector('input[name="hotelSourceType"][value="offline"]')?.click();
        }
    });
})();
</script>
@endpush
