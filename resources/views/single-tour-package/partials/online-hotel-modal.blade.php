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
                                <span class="spinner-border spinner-border-sm d-none me-1" id="onlineHotelFetchSpinner" role="status" aria-hidden="true"></span>
                                <i class="ri-search-line me-1" id="onlineHotelFetchIcon"></i> Fetch Hotels
                            </button>
                            <small class="text-muted" id="onlineHotelFetchStatus" style="font-size: 0.8rem;"></small>
                        </div>
                    </div>
                </div>

                {{-- Hotel selection (shown only after a successful fetch with results) --}}
                <div class="card border-0 shadow-sm d-none" id="onlineHotelSelectionPanel">
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
                <button type="button" class="btn btn-success d-none" id="onlineHotelAddBtn" disabled>
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

@push('styles')
<style>
    #onlineHotelModal .select2-container { width: 100% !important; }
    #onlineHotelModal .select2-container--default .select2-selection--single {
        height: 31px;
        min-height: 31px;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }
    #onlineHotelModal .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 29px;
        padding-left: 0.5rem;
    }
    #onlineHotelModal .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 29px;
    }
</style>
@endpush

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
    let onlineHotelLastSearch = { checkIn: '', checkOut: '', city: '' };
    let onlineGuestState = { male: 1, female: 0, children: 0, infants: 0, childAges: [] };

    function initOnlineHotelSelect2(disabled) {
        if (typeof jQuery === 'undefined' || !jQuery.fn.select2) {
            return;
        }

        const $sel = jQuery('#onlineHotelSelect');
        if (!$sel.length) {
            return;
        }

        if ($sel.hasClass('select2-hidden-accessible')) {
            $sel.select2('destroy');
        }

        $sel.select2({
            placeholder: 'Search hotel...',
            allowClear: true,
            width: '100%',
            dropdownParent: jQuery('#onlineHotelModal'),
        });

        $sel.prop('disabled', !!disabled);
    }

    function getOnlineHotelSelectIndex() {
        if (typeof jQuery !== 'undefined') {
            const $sel = jQuery('#onlineHotelSelect');
            if ($sel.length && $sel.data('select2')) {
                try {
                    const data = $sel.select2('data');
                    if (data && data.length && data[0].element) {
                        const idx = data[0].element.getAttribute('data-index');
                        if (idx !== null && idx !== '') {
                            return parseInt(idx, 10);
                        }
                    }
                } catch (e) { /* select2 not ready */ }
            }
        }

        const sel = document.getElementById('onlineHotelSelect');
        if (sel && sel.selectedIndex >= 0 && sel.options[sel.selectedIndex]) {
            const idx = sel.options[sel.selectedIndex].dataset.index;
            if (idx !== undefined) {
                return parseInt(idx, 10);
            }
        }

        return -1;
    }

    function onlineHotelSelectHasValue() {
        if (typeof jQuery !== 'undefined') {
            const value = jQuery('#onlineHotelSelect').val();
            if (value) {
                return true;
            }
        }

        return !!document.getElementById('onlineHotelSelect')?.value;
    }

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

        resetOnlineHotelFetchResults();

        const guestModal = document.getElementById('onlineHotelGuestModal');
        if (guestModal && window.bootstrap) {
            bootstrap.Modal.getInstance(guestModal)?.hide();
        }
    }

    function syncOnlineSearchDefaults() {
        resetOnlineHotelFetchResults();
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

        const tourStart = document.getElementById('start_date')?.value || '';
        const tourEnd = document.getElementById('end_date')?.value || '';
        const checkInEl = document.getElementById('onlineHotelCheckIn');
        const checkOutEl = document.getElementById('onlineHotelCheckOut');
        if (checkInEl && tourStart) {
            checkInEl.value = tourStart;
        }
        if (checkOutEl && tourEnd) {
            checkOutEl.value = tourEnd;
        } else if (checkOutEl && tourStart) {
            const planNights = (typeof window.getHotelNightPlanNightCount === 'function')
                ? window.getHotelNightPlanNightCount()
                : countDaysBetween(tourStart, tourEnd);
            if (planNights > 0) {
                checkOutEl.value = addDaysToDateStr(tourStart, planNights);
            }
        }

        onlineHotelLastSearch = {
            checkIn: checkInEl?.value || '',
            checkOut: checkOutEl?.value || '',
            city: onlineCity?.value || '',
        };

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

        generateOnlineNightButtons(false, getOnlineNightPlanContext());
    }

    function parseDateInput(value) {
        if (!value) return null;
        const parts = String(value).split('-').map(function (p) { return parseInt(p, 10); });
        if (parts.length !== 3 || parts.some(function (n) { return isNaN(n); })) {
            return null;
        }
        return new Date(parts[0], parts[1] - 1, parts[2]);
    }

    function countDaysBetween(startStr, endStr) {
        const start = parseDateInput(startStr);
        const end = parseDateInput(endStr);
        if (!start || !end) return 0;
        const diffMs = end.getTime() - start.getTime();
        return Math.max(0, Math.round(diffMs / 86400000));
    }

    function addDaysToDateStr(dateStr, days) {
        const date = parseDateInput(dateStr);
        if (!date) return dateStr;
        date.setDate(date.getDate() + days);
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + d;
    }

    function formatNightDateLabel(dateStr, addDays) {
        const date = parseDateInput(dateStr);
        if (!date) return dateStr || '';
        date.setDate(date.getDate() + addDays);
        if (typeof moment !== 'undefined') {
            return moment(date).format('MMM DD');
        }
        return date.toLocaleDateString('en-US', { month: 'short', day: '2-digit' });
    }

    function getOnlineNightPlanContext() {
        const checkIn = document.getElementById('onlineHotelCheckIn')?.value || onlineHotelLastSearch.checkIn || '';
        const checkOut = document.getElementById('onlineHotelCheckOut')?.value || onlineHotelLastSearch.checkOut || '';
        const nightsFromModalDates = countDaysBetween(checkIn, checkOut);
        if (checkIn && checkOut && nightsFromModalDates > 0) {
            return { start: checkIn, nights: nightsFromModalDates };
        }

        if (typeof window.getHotelNightPlanStart === 'function' && typeof window.getHotelNightPlanNightCount === 'function') {
            const planStart = window.getHotelNightPlanStart();
            const planNights = window.getHotelNightPlanNightCount();
            if (planStart && planNights > 0) {
                return { start: planStart, nights: planNights };
            }
        }

        const tourStart = document.getElementById('start_date')?.value || '';
        const tourEnd = document.getElementById('end_date')?.value || '';
        const nightsFromTour = countDaysBetween(tourStart, tourEnd);
        if (tourStart && nightsFromTour > 0) {
            return { start: tourStart, nights: nightsFromTour };
        }

        return { start: '', nights: 0 };
    }

    function generateOnlineNightButtons(autoSelectAll, nightPlanOverride) {
        const wrap = document.getElementById('onlineNightSelection');
        const summaryEl = document.getElementById('onlineNightSelectionSummary');
        if (!wrap) return;
        wrap.innerHTML = '';
        onlineSelectedNights = [];

        const plan = nightPlanOverride || getOnlineNightPlanContext();
        const planNights = plan.nights;
        const planStart = plan.start;
        if (!planNights || !planStart) {
            if (summaryEl) {
                summaryEl.innerHTML = '<small class="text-muted">Set travel dates first.</small>';
            }
            validateOnlineAddBtn();
            return;
        }

        for (let i = 1; i <= planNights; i++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-primary btn-sm online-night-btn';
            btn.dataset.night = String(i);
            btn.innerHTML = '<strong>Night ' + i + '</strong><br><small>'
                + formatNightDateLabel(planStart, i - 1) + ' - ' + formatNightDateLabel(planStart, i)
                + '</small>';
            btn.addEventListener('click', function () {
                toggleOnlineNight(parseInt(this.dataset.night, 10));
            });
            wrap.appendChild(btn);
        }

        if (autoSelectAll) {
            selectAllOnlineNights();
        } else {
            updateOnlineNightSummary();
            validateOnlineAddBtn();
        }
    }

    function selectAllOnlineNights() {
        onlineSelectedNights = [];
        document.querySelectorAll('.online-night-btn').forEach(function (btn) {
            onlineSelectedNights.push(parseInt(btn.dataset.night, 10));
        });
        onlineSelectedNights.sort(function (a, b) { return a - b; });
        document.querySelectorAll('.online-night-btn').forEach(function (btn) {
            const n = parseInt(btn.dataset.night, 10);
            btn.classList.toggle('active', onlineSelectedNights.includes(n));
            btn.classList.toggle('btn-primary', onlineSelectedNights.includes(n));
            btn.classList.toggle('btn-outline-primary', !onlineSelectedNights.includes(n));
        });
        updateOnlineNightSummary();
        validateOnlineAddBtn();
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

        initOnlineHotelSelect2(onlineHotelsCache.length === 0);

        document.getElementById('onlineRoomTypeSelect').innerHTML = '<option value="">Room Type</option>';
        document.getElementById('onlineBedTypeSelect').innerHTML = '<option value="">Bed Type</option>';
        document.getElementById('onlineRoomTypeSelect').disabled = true;
        document.getElementById('onlineBedTypeSelect').disabled = true;

        if (typeof jQuery !== 'undefined' && onlineHotelsCache.length > 0) {
            const firstVal = hotelId(onlineHotelsCache[0]) || '0';
            jQuery('#onlineHotelSelect').val(firstVal).trigger('change');
        } else if (onlineHotelsCache.length > 0) {
            sel.selectedIndex = 1;
            populateOnlineRooms(onlineHotelsCache[0]);
        } else if (typeof jQuery !== 'undefined') {
            jQuery('#onlineHotelSelect').val(null).trigger('change');
        }

        validateOnlineAddBtn();
    }

    function hideOnlineHotelSelectionPanel() {
        const panel = document.getElementById('onlineHotelSelectionPanel');
        if (panel) {
            panel.classList.add('d-none');
        }
        const addBtn = document.getElementById('onlineHotelAddBtn');
        if (addBtn) {
            addBtn.classList.add('d-none');
            addBtn.disabled = true;
        }
    }

    function showOnlineHotelSelectionPanel(nightPlanOverride) {
        const panel = document.getElementById('onlineHotelSelectionPanel');
        if (panel) {
            panel.classList.remove('d-none');
        }
        generateOnlineNightButtons(true, nightPlanOverride);
        const addBtn = document.getElementById('onlineHotelAddBtn');
        if (addBtn) {
            addBtn.classList.remove('d-none');
        }
        validateOnlineAddBtn();
    }

    function setOnlineHotelFetchLoading(isLoading) {
        const btn = document.getElementById('onlineHotelFetchBtn');
        const spinner = document.getElementById('onlineHotelFetchSpinner');
        const icon = document.getElementById('onlineHotelFetchIcon');

        if (btn) {
            btn.disabled = !!isLoading;
        }
        if (spinner) {
            spinner.classList.toggle('d-none', !isLoading);
        }
        if (icon) {
            icon.classList.toggle('d-none', !!isLoading);
        }
    }

    function onOnlineHotelSearchCriteriaChange() {
        onlineHotelLastSearch = {
            checkIn: document.getElementById('onlineHotelCheckIn')?.value || '',
            checkOut: document.getElementById('onlineHotelCheckOut')?.value || '',
            city: document.getElementById('onlineHotelCity')?.value || '',
        };
        resetOnlineHotelFetchResults();
        if (!document.getElementById('onlineHotelSelectionPanel')?.classList.contains('d-none')) {
            generateOnlineNightButtons(false);
        }
    }

    function resetOnlineHotelFetchResults() {
        hideOnlineHotelSelectionPanel();
        populateOnlineHotels([]);
        const statusEl = document.getElementById('onlineHotelFetchStatus');
        if (statusEl) {
            statusEl.textContent = '';
        }
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
        const ok = onlineHotelSelectHasValue() && onlineSelectedNights.length > 0;
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
    $(function () {
        bindHotelSourceToggle();
        initOnlineHotelSelect2(true);
    });

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

        if (!city || !checkIn || !checkOut) {
            if (typeof showNotification === 'function') {
                showNotification('City, check-in and check-out are required.', 'warning');
            }
            return;
        }

        hideOnlineHotelSelectionPanel();
        setOnlineHotelFetchLoading(true);
        if (statusEl) statusEl.textContent = '';

        onlineHotelLastSearch = { checkIn: checkIn, checkOut: checkOut, city: city };
        const fetchedNightPlan = { start: checkIn, nights: countDaysBetween(checkIn, checkOut) };

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

                if (hotels.length > 0) {
                    showOnlineHotelSelectionPanel(fetchedNightPlan.nights > 0 ? fetchedNightPlan : null);
                    if (statusEl) {
                        statusEl.textContent = hotels.length + ' hotel(s) found.';
                    }
                    if (typeof showNotification === 'function') {
                        showNotification('Online hotels loaded successfully.', 'success');
                    }
                } else {
                    hideOnlineHotelSelectionPanel();
                    if (statusEl) {
                        statusEl.textContent = '0 hotels found.';
                    }
                    if (typeof showNotification === 'function') {
                        showNotification('No hotels found for the selected criteria.', 'warning');
                    }
                }
            } else {
                hideOnlineHotelSelectionPanel();
                populateOnlineHotels([]);
                if (statusEl) statusEl.textContent = data?.message || 'No hotels found.';
                if (typeof showNotification === 'function') {
                    showNotification(data?.message || 'Failed to fetch online hotels.', 'error');
                }
            }
        })
        .catch(function (err) {
            hideOnlineHotelSelectionPanel();
            populateOnlineHotels([]);
            if (statusEl) statusEl.textContent = 'Request failed.';
            console.error(err);
            if (typeof showNotification === 'function') {
                showNotification('Error fetching online hotels.', 'error');
            }
        })
        .finally(function () {
            setOnlineHotelFetchLoading(false);
        });
    });

    ['onlineHotelCity', 'onlineHotelCheckIn', 'onlineHotelCheckOut'].forEach(function (id) {
        document.getElementById(id)?.addEventListener('change', onOnlineHotelSearchCriteriaChange);
    });

    function onOnlineHotelSelectChange() {
        const idx = getOnlineHotelSelectIndex();
        const hotel = idx >= 0 ? onlineHotelsCache[idx] : null;
        if (hotel) {
            populateOnlineRooms(hotel);
        }
        validateOnlineAddBtn();
    }

    if (typeof jQuery !== 'undefined') {
        jQuery('#onlineHotelSelect').on('change.onlineHotel select2:select.onlineHotel select2:clear.onlineHotel', onOnlineHotelSelectChange);
    } else {
        document.getElementById('onlineHotelSelect')?.addEventListener('change', onOnlineHotelSelectChange);
    }

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
        if (typeof window.pushSelectedHotel !== 'function') {
            alert('Hotel list is not ready. Please reload the page.');
            return;
        }

        const nightPlan = getOnlineNightPlanContext();
        const planStart = nightPlan.start || document.getElementById('start_date')?.value || onlineHotelLastSearch.checkIn;
        if (!planStart) {
            alert('Please add travel dates first before adding hotels.');
            return;
        }
        if (!onlineSelectedNights.length) {
            alert('Please select at least one hotel night.');
            return;
        }

        const idx = getOnlineHotelSelectIndex();
        const hotelRaw = idx >= 0 ? onlineHotelsCache[idx] : null;
        if (!hotelRaw) return;

        const nightNumbers = onlineSelectedNights.slice().sort((a, b) => a - b);
        const startNight = Math.min(...nightNumbers);
        const endNight = Math.max(...nightNumbers);
        const checkInDateStr = addDaysToDateStr(planStart, startNight - 1);
        const checkOutDateStr = addDaysToDateStr(planStart, endNight);
        const checkInDate = typeof moment !== 'undefined' ? moment(checkInDateStr).format('MMM DD') : formatNightDateLabel(planStart, startNight - 1);
        const checkOutDate = typeof moment !== 'undefined' ? moment(checkOutDateStr).format('MMM DD') : formatNightDateLabel(planStart, endNight);
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
            checkInDate: checkInDate,
            checkOutDate: checkOutDate,
            totalNights: nightNumbers.length,
            remarks: remarks,
            isOnlineHotel: true,
            onlineHotelSource: onlineLastSupplierCode || hotelRaw.supplier_code || 'online',
            onlineHotelRaw: hotelRaw,
            city: cityName,
            pricePerNight: nightNumbers.length ? price / nightNumbers.length : price
        };

        window.pushSelectedHotel(hotelData);
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
