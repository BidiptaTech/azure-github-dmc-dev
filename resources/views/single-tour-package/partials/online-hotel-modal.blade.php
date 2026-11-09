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
                    Hotels are fetched live from the supplier mapped to the selected city's country. Select dates, city, guests and rooms, then click <strong>Fetch Hotels</strong>.
                </div>

                {{-- Search criteria (maps to fetchHotels API) --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-building-line me-1"></i>City</label>
                                <select class="form-select form-select-sm" id="onlineHotelCity"></select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-calendar-line me-1"></i>Check-in</label>
                                <input type="date" class="form-control form-control-sm" id="onlineHotelCheckIn">
                            </div>
                            <div class="col-md-2">
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
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-hotel-bed-2-line me-1"></i>Number of Rooms</label>
                                <input type="number" class="form-control form-control-sm" id="onlineNumberOfRooms" value="1" min="1" max="999">
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
                                <select class="form-select form-select-sm" id="onlineHotelSelect">
                                    <option value="">Fetch hotels first</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1 d-flex align-items-center gap-1" style="font-size: 0.8rem;">
                                    <i class="ri-door-open-line me-1"></i>Room Type
                                    <span class="spinner-border spinner-border-sm text-primary d-none" id="onlineRoomLoadingSpinner" role="status" aria-hidden="true" style="width: 0.7rem; height: 0.7rem;"></span>
                                </label>
                                <select class="form-select form-select-sm" id="onlineRoomTypeSelect" disabled>
                                    <option value="">Room Type</option>
                                </select>
                                <small class="text-muted d-block mt-1" id="onlineRoomStatus" style="font-size: 0.72rem;"></small>
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
                        
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-restaurant-line me-1"></i>Meal Plan</label>
                                <select class="form-select form-select-sm" id="onlineMealPlanSelect" disabled>
                                    <option value="">Select Meal Plan</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-money-dollar-circle-line me-1"></i>Price</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">SGD</span>
                                    <input type="text" class="form-control" id="onlineRoomPriceDisplay" value="0.00" readonly style="background:#f8f9fa; text-align:right;">
                                </div>
                            </div>
                        </div>

                        {{-- Compact MG cancellation summary (1–2 lines) for the selected room --}}
                        <div id="onlineCancellationPolicy" class="d-none mb-3 px-2 py-1 rounded"
                             style="background: #fff8e8; border: 1px solid #f0e0b2;">
                            <div id="onlineCancellationPolicyLine1"
                                 style="font-size: 0.72rem; line-height: 1.35; color: #6b5a1a;"></div>
                            <div id="onlineCancellationPolicyLine2" class="d-none"
                                 style="font-size: 0.7rem; line-height: 1.3; color: #7a6a2e; margin-top: 0.1rem;"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold mb-2" style="font-size: 0.85rem;"><i class="ri-chat-quote-line me-1"></i>Remarks</label>
                            <textarea class="form-control form-control-sm" id="onlineHotelRemarks" rows="2" placeholder="Optional notes for this online hotel booking..."></textarea>
                        </div>

                        <!-- <div class="mb-2">
                            <label class="form-label fw-semibold mb-2" style="font-size: 0.85rem;"><i class="ri-calendar-check-line me-1"></i>Select Hotel Nights</label>
                            <div id="onlineNightSelection" class="d-flex flex-wrap gap-2 mb-2"></div>
                            <div id="onlineNightSelectionSummary">
                                <small class="text-muted">No nights selected.</small>
                            </div>
                        </div> -->
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

@push('css')
<style>
    /* Pro page Select2 CSS is aggressive — scope + override so hotel search works */
    #onlineHotelModal.modal { overflow: visible !important; }
    #onlineHotelModal .modal-dialog { overflow: visible !important; }
    #onlineHotelModal .modal-content { overflow: visible !important; }
    #onlineHotelModal select.select2-hidden-accessible {
        display: none !important;
    }
    #onlineHotelModal .select2-container {
        display: block !important;
        width: 100% !important;
        z-index: 2;
    }
    #onlineHotelModal .select2-container--default .select2-selection--single {
        height: 34px !important;
        min-height: 34px !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 0.375rem !important;
        font-size: 0.875rem !important;
        padding: 0 !important;
        background-color: #fff !important;
        cursor: pointer !important;
        pointer-events: auto !important;
    }
    #onlineHotelModal .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 32px !important;
        padding-left: 0.65rem !important;
        color: #212529 !important;
        font-size: 0.875rem !important;
    }
    #onlineHotelModal .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 32px !important;
    }
    #onlineHotelModal .select2-container--default.select2-container--disabled .select2-selection--single {
        background-color: #e9ecef !important;
        cursor: not-allowed !important;
        color: #6c757d !important;
    }
    #onlineHotelModal .select2-search--dropdown {
        display: block !important;
        padding: 8px !important;
    }
    #onlineHotelModal .select2-search--dropdown .select2-search__field {
        height: 34px !important;
        min-height: 34px !important;
        font-size: 0.85rem !important;
        padding: 6px 10px !important;
        border: 1px solid #ced4da !important;
        border-radius: 6px !important;
        width: 100% !important;
    }
    .select2-container--open {
        z-index: 20050 !important;
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
    const roomsUrl = @json(route('fetch-online-hotel-rooms'));
    const csrfToken = @json(csrf_token());

    let onlineHotelsCache = [];
    let onlineSelectedNights = [];
    let onlineCurrentRooms = [];
    let onlineLastSupplierCode = '';
    let onlineLastApiEnvironment = '';
    let onlineHotelLastSearch = { checkIn: '', checkOut: '', city: '', paxInfo: '' };
    /** City/tour stay window — modal date pickers cannot leave this range. */
    let onlineStayDateBounds = { start: '', end: '' };
    let onlineGuestState = { male: 1, female: 0, children: 0, infants: 0, childAges: [] };
    // Two-step suppliers (MG Bedbank) list their whole catalogue first, then price one hotel on demand.
    let onlineTwoStep = false;
    let onlineRoomsLoading = false;
    let onlineSelectedHotelDetail = null;
    let onlineSelectedRoom = null;
    let onlineRoomsRequestId = 0;
    let onlineRoomsAbortController = null;
    let onlineRoomsFetchKey = '';
    let onlineHotelSelectDebounce = null;
    let onlineHotelHydrating = false;

    function bindOnlineHotelSelectEvents() {
        const el = document.getElementById('onlineHotelSelect');
        if (!el) {
            return;
        }

        el.removeEventListener('change', onOnlineHotelSelectChange);
        if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
            // Prefer Select2 events — native change also fires and causes duplicate room fetches.
            jQuery(el).off('.onlineHotel');
            jQuery(el).on('select2:select.onlineHotel select2:clear.onlineHotel', onOnlineHotelSelectChange);
        } else {
            el.addEventListener('change', onOnlineHotelSelectChange);
        }
    }

    function setOnlineHotelSelectEnabled(enabled) {
        const sel = document.getElementById('onlineHotelSelect');
        if (sel) sel.disabled = !enabled;
        if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
            const $sel = jQuery('#onlineHotelSelect');
            if ($sel.length) $sel.prop('disabled', !enabled);
        }
    }

    function initOnlineHotelSelect2(disabled) {
        const sel = document.getElementById('onlineHotelSelect');
        if (sel) sel.disabled = !!disabled;

        if (typeof jQuery === 'undefined' || !jQuery.fn.select2) {
            return;
        }

        const $sel = jQuery('#onlineHotelSelect');
        if (!$sel.length) {
            return;
        }

        if ($sel.hasClass('select2-hidden-accessible')) {
            try { $sel.select2('destroy'); } catch (e) { /* ignore */ }
        }

        const $parent = jQuery('#onlineHotelModal .modal-content');
        try {
            $sel.select2({
                placeholder: 'Search hotel...',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: 0,
                dropdownParent: $parent.length ? $parent : jQuery('#onlineHotelModal'),
                language: { noResults: function () { return 'No matches'; } }
            });
        } catch (e) {
            console.warn('Online hotel Select2 init failed', e);
        }

        setOnlineHotelSelectEnabled(!disabled);
        bindOnlineHotelSelectEvents();
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
        const ages = Array.isArray(onlineGuestState.childAges)
            ? onlineGuestState.childAges.filter(function (a) { return Number.isFinite(a) && a > 0; })
            : [];
        // adults|children|age1,age2 — ages optional; MG defaults missing ages to 8.
        if (children > 0 && ages.length > 0) {
            return adults + '|' + children + '|' + ages.slice(0, children).join(',');
        }
        return adults + '|' + children;
    }

    /**
     * Suppliers that quote per room block (MG Bedbank) charge roughly double for the same
     * pax over two rooms, so the agent's "Number of Rooms" choice has to reach the API
     * instead of being guessed from the guest counts.
     */
    function selectedRoomCount() {
        const rooms = parseInt(document.getElementById('onlineNumberOfRooms')?.value, 10);
        return rooms > 0 ? rooms : 1;
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

        // Default bounds = tour dates; STP Lite host override narrows to that city's stay.
        onlineStayDateBounds = {
            start: tourStart || '',
            end: tourEnd || (checkOutEl?.value || ''),
        };

        // Optional host override (STP Lite per-city stay → city / check-in / check-out)
        if (typeof window.getOnlineHotelSearchDefaults === 'function') {
            try {
                const d = window.getOnlineHotelSearchDefaults() || {};
                if (onlineCity && d.city) {
                    const cityVal = String(d.city);
                    const cityLabel = String(d.cityLabel || d.city);
                    let hasOpt = Array.from(onlineCity.options).some(function (o) { return o.value === cityVal; });
                    if (!hasOpt) {
                        const o = document.createElement('option');
                        o.value = cityVal;
                        o.textContent = cityLabel;
                        onlineCity.appendChild(o);
                    }
                    onlineCity.value = cityVal;
                }
                if (checkInEl && d.checkIn) checkInEl.value = d.checkIn;
                if (checkOutEl && d.checkOut) checkOutEl.value = d.checkOut;
                const boundStart = String(d.stayStart || d.checkIn || '').trim();
                const boundEnd = String(d.stayEnd || d.checkOut || '').trim();
                if (boundStart) onlineStayDateBounds.start = boundStart;
                if (boundEnd) onlineStayDateBounds.end = boundEnd;
            } catch (e) { /* ignore host override errors */ }
        }

        applyOnlineStayDateBounds();

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

    /**
     * Restrict check-in/out to the city (or tour) stay window set when the modal opened.
     * Mirrors STP Lite offline hotel date clamping.
     */
    function applyOnlineStayDateBounds() {
        const checkInEl = document.getElementById('onlineHotelCheckIn');
        const checkOutEl = document.getElementById('onlineHotelCheckOut');
        if (!checkInEl && !checkOutEl) return;

        const min = onlineStayDateBounds.start || '';
        const max = onlineStayDateBounds.end || '';

        if (checkInEl) {
            if (min) checkInEl.setAttribute('min', min);
            else checkInEl.removeAttribute('min');
            if (max) checkInEl.setAttribute('max', max);
            else checkInEl.removeAttribute('max');

            if (!checkInEl.value && min) checkInEl.value = min;
            if (min && checkInEl.value && checkInEl.value < min) checkInEl.value = min;
            if (max && checkInEl.value && checkInEl.value > max) checkInEl.value = max;
        }

        if (checkOutEl) {
            const outMin = (checkInEl && checkInEl.value) || min;
            if (outMin) checkOutEl.setAttribute('min', outMin);
            else checkOutEl.removeAttribute('min');
            if (max) checkOutEl.setAttribute('max', max);
            else checkOutEl.removeAttribute('max');

            if (!checkOutEl.value && max) checkOutEl.value = max;
            if (outMin && checkOutEl.value && checkOutEl.value < outMin) checkOutEl.value = outMin;
            if (max && checkOutEl.value && checkOutEl.value > max) checkOutEl.value = max;

            // Keep at least one night when both dates are set and equal.
            if (
                checkInEl && checkInEl.value && checkOutEl.value
                && checkOutEl.value <= checkInEl.value
                && max && checkInEl.value < max
            ) {
                const next = addDaysToDateStr(checkInEl.value, 1);
                checkOutEl.value = (max && next > max) ? max : next;
            }
        }
    }

    function onlineDatesWithinStayBounds(checkIn, checkOut) {
        const min = onlineStayDateBounds.start || '';
        const max = onlineStayDateBounds.end || '';
        if (!checkIn || !checkOut) return false;
        if (checkOut <= checkIn) return false;
        if (min && checkIn < min) return false;
        if (max && checkIn > max) return false;
        if (min && checkOut < min) return false;
        if (max && checkOut > max) return false;
        return true;
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

    /** Build [1..n] from stay check-in/out when night buttons UI is hidden. */
    function nightsRangeFromPlan(nightPlanOverride) {
        const plan = nightPlanOverride || getOnlineNightPlanContext();
        const planNights = parseInt(plan.nights, 10) || 0;
        const nights = [];
        for (let i = 1; i <= planNights; i++) {
            nights.push(i);
        }
        return { plan: plan, nights: nights };
    }

    function generateOnlineNightButtons(autoSelectAll, nightPlanOverride) {
        const wrap = document.getElementById('onlineNightSelection');
        const summaryEl = document.getElementById('onlineNightSelectionSummary');
        const ranged = nightsRangeFromPlan(nightPlanOverride);
        const plan = ranged.plan;
        const planNights = plan.nights;
        const planStart = plan.start;

        onlineSelectedNights = [];

        // Night picker UI may be commented out — still select all stay nights so Add works.
        if (!wrap) {
            if (autoSelectAll !== false && ranged.nights.length && planStart) {
                onlineSelectedNights = ranged.nights.slice();
            }
            updateOnlineNightSummary();
            validateOnlineAddBtn();
            return;
        }

        wrap.innerHTML = '';
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
            selectAllOnlineNights(nightPlanOverride);
        } else {
            updateOnlineNightSummary();
            validateOnlineAddBtn();
        }
    }

    function selectAllOnlineNights(nightPlanOverride) {
        const buttons = document.querySelectorAll('.online-night-btn');
        onlineSelectedNights = [];

        if (buttons.length) {
            buttons.forEach(function (btn) {
                onlineSelectedNights.push(parseInt(btn.dataset.night, 10));
            });
        } else {
            onlineSelectedNights = nightsRangeFromPlan(nightPlanOverride).nights.slice();
        }

        onlineSelectedNights.sort(function (a, b) { return a - b; });
        buttons.forEach(function (btn) {
            const n = parseInt(btn.dataset.night, 10);
            btn.classList.toggle('active', onlineSelectedNights.includes(n));
            btn.classList.toggle('btn-primary', onlineSelectedNights.includes(n));
            btn.classList.toggle('btn-outline-primary', !onlineSelectedNights.includes(n));
        });
        updateOnlineNightSummary();
        validateOnlineAddBtn();
    }

    function setOnlineSelectedNights(nights) {
        const wanted = (Array.isArray(nights) ? nights : []).map(Number).filter(function (n) { return n > 0; });
        if (!wanted.length) {
            selectAllOnlineNights();
            return;
        }
        onlineSelectedNights = wanted.slice().sort(function (a, b) { return a - b; });
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
        // Catalogue listings have no rate yet, so locality is what makes entries distinguishable.
        if (price <= 0) {
            const where = String(h.area || '').trim() || String(h.address || '').trim();
            if (where) {
                label += ' — ' + (where.length > 60 ? where.slice(0, 60) + '…' : where);
            }
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

    /**
     * Normalize supplier meal text into "Room with …" display / storage labels.
     * More-specific combinations are matched before single meals.
     */
    function formatRoomMealText(rawMeal) {
        const meal = String(rawMeal || '').trim();
        const upper = meal.toUpperCase();
        if (!upper) return '';

        if (upper === 'EP' || upper === 'RO' || upper.includes('ROOM ONLY') || upper === 'STANDARD') {
            return 'Room only';
        }

        const hasBreakfast = upper.includes('BREAKFAST') || upper === 'CP' || upper === 'BB' || upper === 'BDBF';
        const hasLunch = upper.includes('LUNCH');
        const hasDinner = upper.includes('DINNER') || upper.includes('SUPPER');
        const isHalfBoard = upper === 'MAP' || upper === 'HB' || upper.includes('HALF BOARD');
        const isFullBoard = upper === 'AP' || upper === 'FB' || upper === 'AI'
            || upper.includes('FULL BOARD') || upper.includes('ALL INCLUSIVE') || upper.includes('ALL MEALS');

        if (isFullBoard || (hasBreakfast && hasLunch && hasDinner)) {
            return 'Room with breakfast, lunch and dinner';
        }
        if (isHalfBoard || (hasBreakfast && hasDinner && !hasLunch)) {
            return 'Room with breakfast and dinner';
        }
        if (hasBreakfast && hasLunch && !hasDinner) {
            return 'Room with breakfast and lunch';
        }
        if (hasLunch && hasDinner && !hasBreakfast) {
            return 'Room with lunch and dinner';
        }
        if (hasBreakfast) return 'Room with breakfast';
        if (hasLunch) return 'Room with lunch';
        if (hasDinner) return 'Room with dinner';

        // Unknown supplier text — still prefix as a room meal plan when possible.
        if (meal.toLowerCase().startsWith('room ')) return meal.charAt(0).toUpperCase() + meal.slice(1);
        return 'Room with ' + meal.charAt(0).toLowerCase() + meal.slice(1);
    }

    function roomMealLabel(room) {
        const raw = String(
            room?.mealPlanName
            || room?.meal_plan_name
            || room?.meal_plan
            || room?.board_code
            || room?.booking?.room?.meal_plan_name
            || ''
        ).trim();

        if (raw) {
            return formatRoomMealText(raw).toLowerCase();
        }
        if (room?.breakFast === true || room?.breakfast_included === true) {
            return 'room with breakfast';
        }
        return '';
    }

    /** Group key: same physical room / different meal plans share room_id (MG). */
    function roomGroupKey(room) {
        const id = String(room?.room_id || room?.roomId || '').trim();
        if (id) return id;
        return String(room?.room_name || room?.roomName || room?.roomType || room?.name || '').trim() || 'room';
    }

    function roomGroupLabel(room) {
        return String(
            room?.room_name || room?.roomName || room?.roomType || room?.room_type || room?.name || room?.type || ''
        ).trim() || roomGroupKey(room);
    }

    /** Dropdown label: "Room with breakfast", "Room with breakfast and lunch", etc. */
    function roomMealOptionLabel(room) {
        const mapped = roomMealLabel(room);
        if (mapped) {
            return mapped.charAt(0).toUpperCase() + mapped.slice(1);
        }
        return 'Room only';
    }

    function roomsForGroupKey(groupKey) {
        return onlineCurrentRooms
            .map(function (room, idx) { return { room: room, idx: idx }; })
            .filter(function (item) { return roomGroupKey(item.room) === groupKey; });
    }

    function clearOnlineMealPlanSelect() {
        const mealSel = document.getElementById('onlineMealPlanSelect');
        if (!mealSel) return;
        mealSel.innerHTML = '<option value="">Select Meal Plan</option>';
        mealSel.disabled = true;
        mealSel.value = '';
    }

    /**
     * Meal plans available for the selected room type (from live hotel rooms).
     * Option value = index into onlineCurrentRooms so price/rate keys stay in sync.
     */
    function populateOnlineMealsForGroup(groupKey, preferredRoomIdx) {
        const mealSel = document.getElementById('onlineMealPlanSelect');
        if (!mealSel) return null;

        mealSel.innerHTML = '<option value="">Select Meal Plan</option>';
        const matches = roomsForGroupKey(groupKey);

        if (!matches.length) {
            mealSel.disabled = true;
            return null;
        }

        matches.sort(function (a, b) {
            return roomDisplayPrice(a.room) - roomDisplayPrice(b.room);
        });

        matches.forEach(function (item) {
            const opt = document.createElement('option');
            opt.value = String(item.idx);
            const mealLabel = roomMealOptionLabel(item.room);
            const price = roomDisplayPrice(item.room);
            opt.textContent = price > 0
                ? mealLabel + ' — ' + String(item.room.currency || 'SGD') + ' ' + price.toFixed(2)
                : mealLabel;
            opt.dataset.roomIndex = String(item.idx);
            mealSel.appendChild(opt);
        });

        mealSel.disabled = false;

        let selectIdx = matches[0].idx;
        if (preferredRoomIdx !== undefined && preferredRoomIdx !== null && preferredRoomIdx >= 0) {
            const found = matches.find(function (m) { return m.idx === preferredRoomIdx; });
            if (found) selectIdx = found.idx;
        }
        mealSel.value = String(selectIdx);
        return onlineCurrentRooms[selectIdx] || matches[0].room;
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

    function setOnlineRoomStatus(message, tone) {
        const el = document.getElementById('onlineRoomStatus');
        if (!el) return;
        el.textContent = message || '';
        el.className = 'd-block mt-1 ' + (tone === 'error' ? 'text-danger' : (tone === 'success' ? 'text-success' : 'text-muted'));
        el.style.fontSize = '0.72rem';
    }

    function setOnlineRoomsLoading(isLoading) {
        onlineRoomsLoading = !!isLoading;
        document.getElementById('onlineRoomLoadingSpinner')?.classList.toggle('d-none', !isLoading);
        const roomSel = document.getElementById('onlineRoomTypeSelect');
        if (roomSel && isLoading) {
            roomSel.disabled = true;
            roomSel.innerHTML = '<option value="">Checking availability...</option>';
        }
        validateOnlineAddBtn();
    }

    function populateOnlineHotels(hotels) {
        onlineHotelsCache = Array.isArray(hotels) ? hotels.slice() : [];
        onlineSelectedHotelDetail = null;
        onlineSelectedRoom = null;

        if (onlineTwoStep) {
            // No rates yet at this point, so alphabetical is the only useful ordering.
            onlineHotelsCache.sort(function (a, b) {
                return String(hotelLabel(a)).localeCompare(String(hotelLabel(b)));
            });
        } else {
            onlineHotelsCache.sort(function (a, b) {
                const pa = hotelLowestPrice(a) || Infinity;
                const pb = hotelLowestPrice(b) || Infinity;
                return pa - pb;
            });
        }

        const sel = document.getElementById('onlineHotelSelect');
        if (typeof jQuery !== 'undefined' && jQuery.fn.select2 && jQuery(sel).hasClass('select2-hidden-accessible')) {
            try { jQuery(sel).select2('destroy'); } catch (e) { /* ignore */ }
        }
        sel.innerHTML = '<option value="">Select hotel</option>';
        onlineHotelsCache.forEach(function (h, idx) {
            const opt = document.createElement('option');
            opt.value = hotelId(h) || String(idx);
            opt.textContent = hotelSelectLabel(h);
            opt.dataset.index = String(idx);
            opt.dataset.price = String(hotelLowestPrice(h));
            // The supplier code is what SearchHotel is called with on selection.
            opt.dataset.code = hotelId(h);
            opt.dataset.name = hotelLabel(h);
            sel.appendChild(opt);
        });

        const hasItems = onlineHotelsCache.length > 0;
        initOnlineHotelSelect2(!hasItems);
        setOnlineHotelSelectEnabled(hasItems);

        document.getElementById('onlineRoomTypeSelect').innerHTML = '<option value="">Room Type</option>';
        document.getElementById('onlineBedTypeSelect').innerHTML = '<option value="">Bed Type</option>';
        document.getElementById('onlineRoomTypeSelect').disabled = true;
        document.getElementById('onlineBedTypeSelect').disabled = true;
        setOnlineRoomStatus(hasItems && onlineTwoStep ? 'Select a hotel to load live rates.' : '');

        if (onlineTwoStep) {
            // Selecting a hotel costs a supplier call, so let the user choose rather than auto-pricing.
            if (typeof jQuery !== 'undefined') {
                jQuery('#onlineHotelSelect').val(null).trigger('change.select2');
            }
        } else if (hasItems) {
            const firstVal = hotelId(onlineHotelsCache[0]) || String(sel.options[1] && sel.options[1].value) || '';
            sel.disabled = false;
            if (firstVal && typeof jQuery !== 'undefined') {
                jQuery('#onlineHotelSelect').val(firstVal).trigger('change.select2');
            } else {
                sel.selectedIndex = 1;
            }
            handleOnlineHotelSelectChange();
        } else if (typeof jQuery !== 'undefined') {
            jQuery('#onlineHotelSelect').val(null).trigger('change.select2');
            handleOnlineHotelSelectChange();
        }

        validateOnlineAddBtn();
    }

    /**
     * Two-step suppliers: price the picked hotel (SearchHotel) and swap in the live rooms.
     */
    function loadOnlineHotelRooms(hotel) {
        const hotelCode = hotelId(hotel);
        if (!hotelCode) {
            setOnlineRoomStatus('This hotel has no supplier code.', 'error');
            return;
        }

        const paxInfo = onlineHotelLastSearch.paxInfo || buildPaxInfo();
        const roomCount = selectedRoomCount();
        const fetchKey = [
            hotelCode,
            onlineHotelLastSearch.city,
            onlineHotelLastSearch.checkIn,
            onlineHotelLastSearch.checkOut,
            paxInfo,
            roomCount,
        ].join('|');

        if (onlineRoomsLoading && fetchKey === onlineRoomsFetchKey) {
            return;
        }

        onlineRoomsFetchKey = fetchKey;
        onlineHotelLastSearch.rooms = roomCount;

        if (onlineRoomsAbortController) {
            try {
                onlineRoomsAbortController.abort();
            } catch (e) { /* ignore */ }
        }
        onlineRoomsAbortController = typeof AbortController !== 'undefined' ? new AbortController() : null;

        const requestId = ++onlineRoomsRequestId;
        onlineSelectedHotelDetail = null;
        onlineSelectedRoom = null;
        onlineCurrentRooms = [];
        setOnlineRoomsLoading(true);
        setOnlineRoomStatus('Checking live availability...');

        const fetchOptions = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                city: onlineHotelLastSearch.city,
                checkIn: onlineHotelLastSearch.checkIn,
                checkOut: onlineHotelLastSearch.checkOut,
                paxInfo: paxInfo,
                rooms: roomCount,
                hotelCode: hotelCode
            })
        };

        if (onlineRoomsAbortController) {
            fetchOptions.signal = onlineRoomsAbortController.signal;
        }

        fetch(roomsUrl, fetchOptions)
        .then(r => r.json())
        .then(data => {
            if (requestId !== onlineRoomsRequestId) {
                return; // A newer hotel was picked while this was in flight.
            }

            if (!data || !data.success || !data.hotel) {
                populateOnlineRooms({ rooms: [] });
                setOnlineRoomStatus(data?.message || 'No rooms available for these dates.', 'error');
                return;
            }

            if (data.api_environment) {
                onlineLastApiEnvironment = data.api_environment;
            }

            // Keep the catalogue content (images, description) that the live response lacks.
            onlineSelectedHotelDetail = Object.assign({}, hotel, data.hotel);
            onlineSelectedHotelDetail.sessionId = data.session_id || null;
            populateOnlineRooms(onlineSelectedHotelDetail);

            const count = data.total_rooms || (data.rooms || []).length;
            const quotedFor = data.room_count || roomCount;
            setOnlineRoomStatus(
                count > 0
                    ? count + ' room option(s) available, priced for ' + quotedFor + ' room(s).'
                    : 'No rooms available for ' + quotedFor + ' room(s) on these dates.',
                count > 0 ? 'success' : 'error'
            );
        })
        .catch(function (err) {
            if (requestId !== onlineRoomsRequestId) return;
            if (err && err.name === 'AbortError') return;
            console.error(err);
            populateOnlineRooms({ rooms: [] });
            setOnlineRoomStatus('Could not load rooms for this hotel.', 'error');
        })
        .finally(function () {
            if (requestId !== onlineRoomsRequestId) return;
            setOnlineRoomsLoading(false);
            validateOnlineAddBtn();
        });
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
        clearOnlineCancellationPolicy();
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
        if (onlineHotelsCache.length > 0) {
            setOnlineHotelSelectEnabled(true);
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

    /**
     * The rates on screen were quoted for the previous room count, so re-price rather
     * than let the agent add a booking at a total the supplier never gave.
     */
    function onOnlineRoomCountChange() {
        const input = document.getElementById('onlineNumberOfRooms');
        if (input && selectedRoomCount() !== parseInt(input.value, 10)) {
            input.value = String(selectedRoomCount());
        }

        if (onlineHotelLastSearch.rooms === selectedRoomCount()) {
            return;
        }

        const idx = getOnlineHotelSelectIndex();
        const hotel = (idx >= 0 && onlineHotelSelectHasValue()) ? onlineHotelsCache[idx] : null;

        if (!hotel) {
            return;
        }

        if (onlineTwoStep) {
            loadOnlineHotelRooms(hotel);
            return;
        }

        // One-step suppliers price inside the hotel list, so the whole list is now stale.
        resetOnlineHotelFetchResults();
        const statusEl = document.getElementById('onlineHotelFetchStatus');
        if (statusEl) {
            statusEl.textContent = 'Room count changed — fetch hotels again for updated rates.';
        }
    }

    function resetOnlineHotelFetchResults() {
        onlineRoomsRequestId++;
        onlineRoomsFetchKey = '';
        if (onlineRoomsAbortController) {
            try {
                onlineRoomsAbortController.abort();
            } catch (e) { /* ignore */ }
            onlineRoomsAbortController = null;
        }
        setOnlineRoomsLoading(false);
        hideOnlineHotelSelectionPanel();
        populateOnlineHotels([]);
        setOnlineRoomStatus('');
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

    function roomOptionLabel(room, idx) {
        const name = room.roomName || room.room_name || room.roomType || room.room_type || room.name || room.type || ('Room ' + (idx + 1));
        if (!onlineTwoStep) {
            return name;
        }

        const parts = [name];
        const meal = String(room.mealPlanName || room.meal_plan || '').trim();
        if (meal) parts.push(meal);
        const policyType = normalizeCancellationPolicyType(roomCancellationPolicyType(room));
        if (policyType) parts.push(policyType);
        const price = roomDisplayPrice(room);
        if (price > 0) {
            parts.push(String(room.currency || 'SGD') + ' ' + price.toFixed(2));
        }
        return parts.join(' • ');
    }

    function roomCancellationPolicyType(room) {
        if (!room || typeof room !== 'object') return '';
        return String(
            room.booking?.room?.cancellation_policy_type
            || room.cancellation_policy_type
            || room.cancellationPolicyType
            || room.raw?.cancellationPolicyType
            || ''
        ).trim();
    }

    function roomCancellationPolicies(room) {
        if (!room || typeof room !== 'object') return [];

        const candidates = [
            room.cancellation_policy,
            room.cancellationPolicy,
            room.booking?.room?.cancellation_policies,
            room.raw?.cancellationPolicies?.policy,
            room.booking?.raw_room?.cancellationPolicies?.policy,
        ];

        for (let i = 0; i < candidates.length; i++) {
            const value = candidates[i];
            if (Array.isArray(value) && value.length) {
                return value.filter(function (item) { return item && typeof item === 'object'; });
            }
            // Single policy object (MG sometimes returns one rule without wrapping)
            if (value && typeof value === 'object' && !Array.isArray(value) && (value.fromDate || value.toDate)) {
                return [value];
            }
        }

        return [];
    }

    function normalizeCancellationPolicyType(raw) {
        const value = String(raw || '').trim();
        if (!value) return '';
        if (/^nrf$/i.test(value) || /non[-\s]?refund/i.test(value)) return 'Non-refundable';
        if (/flexi/i.test(value)) return 'Flexible';
        return value;
    }

    function isNonRefundablePolicyType(raw) {
        return /nrf|non[-\s]?refund/i.test(String(raw || ''));
    }

    function policyFieldFilled(value) {
        if (value === null || value === undefined) return false;
        if (typeof value === 'string') return value.trim() !== '';
        if (typeof value === 'number') return Number.isFinite(value);
        return !!value;
    }

    function formatOnlineCancelDate(ymd) {
        const raw = String(ymd || '').slice(0, 10);
        if (!/^\d{4}-\d{2}-\d{2}$/.test(raw)) return String(ymd || '');
        const d = new Date(raw + 'T00:00:00');
        if (isNaN(d.getTime())) return raw;
        return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
    }

    function dayBeforeYmd(ymd) {
        const raw = String(ymd || '').slice(0, 10);
        if (!/^\d{4}-\d{2}-\d{2}$/.test(raw)) return '';
        const d = new Date(raw + 'T00:00:00');
        if (isNaN(d.getTime())) return '';
        d.setDate(d.getDate() - 1);
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        return yyyy + '-' + mm + '-' + dd;
    }

    function formatPolicyWindow(fromDate, toDate) {
        const from = String(fromDate || '').slice(0, 10);
        const to = String(toDate || '').slice(0, 10);
        if (!from) return '';
        if (!to || from === to) return formatOnlineCancelDate(from);

        const fromLabel = formatOnlineCancelDate(from);
        const toLabel = formatOnlineCancelDate(to);
        // Same month/year → "6–9 Nov"
        if (from.slice(0, 7) === to.slice(0, 7)) {
            const fromDay = String(parseInt(from.slice(8, 10), 10));
            return fromDay + '–' + toLabel;
        }
        return fromLabel + '–' + toLabel;
    }

    function formatPolicyPenalty(policy, currency) {
        if (policyFieldFilled(policy.nights)) {
            const nights = parseInt(policy.nights, 10);
            if (nights === 1) return '1 night charge';
            if (Number.isFinite(nights) && nights > 0) return nights + ' nights charge';
            return String(policy.nights).trim() + ' night charge';
        }

        if (policyFieldFilled(policy.percent)) {
            const pct = parseFloat(policy.percent);
            const label = Number.isFinite(pct)
                ? (pct % 1 === 0 ? String(pct) : pct.toFixed(2))
                : String(policy.percent).trim();
            return label + '% of stay';
        }

        const amount = policyFieldFilled(policy.grossAmount)
            ? policy.grossAmount
            : (policyFieldFilled(policy.amount) ? policy.amount : null);
        if (amount !== null) {
            const num = parseFloat(amount);
            const money = Number.isFinite(num) ? num.toFixed(2) : String(amount).trim();
            return String(currency || 'SGD') + ' ' + money;
        }

        return 'penalty applies';
    }

    function policyHasCharge(policy) {
        return policyFieldFilled(policy?.nights)
            || policyFieldFilled(policy?.percent)
            || policyFieldFilled(policy?.amount)
            || policyFieldFilled(policy?.grossAmount);
    }

    /**
     * Build a 1–2 line agent-facing summary from MG CancellationPolicies.
     * Amount | Nights | Percent — only one charge type is present per rule.
     */
    function summarizeOnlineCancellationPolicy(room) {
        if (!room) return null;

        const typeRaw = roomCancellationPolicyType(room);
        const typeLabel = normalizeCancellationPolicyType(typeRaw);
        const isNrf = isNonRefundablePolicyType(typeRaw);
        const currency = room.currency || room.booking?.currency || 'SGD';
        const policies = roomCancellationPolicies(room);
        const freeFlag = !!(room.free_cancellation || room.freeCancellation);

        if (!policies.length) {
            if (isNrf) {
                return { line1: 'Non-refundable · Full charge if cancelled', line2: '' };
            }
            if (typeLabel) {
                return { line1: typeLabel + (freeFlag ? ' · Free cancellation' : ''), line2: '' };
            }
            if (freeFlag) {
                return { line1: 'Free cancellation', line2: '' };
            }
            return null;
        }

        const cancelRules = policies.filter(function (p) {
            return !p.noShow && policyHasCharge(p);
        });
        const noShowRules = policies.filter(function (p) {
            return !!p.noShow && policyHasCharge(p);
        });

        const earliestFrom = policies
            .map(function (p) { return String(p.fromDate || '').slice(0, 10); })
            .filter(function (d) { return /^\d{4}-\d{2}-\d{2}$/.test(d); })
            .sort()[0] || '';
        const freeUntil = earliestFrom ? dayBeforeYmd(earliestFrom) : '';

        let line1 = '';
        if (isNrf) {
            line1 = 'Non-refundable · Full charge if cancelled';
        } else if (freeUntil) {
            line1 = (typeLabel || 'Flexible') + ' · Free cancel until ' + formatOnlineCancelDate(freeUntil);
        } else if (freeFlag) {
            line1 = (typeLabel || 'Flexible') + ' · Free cancellation';
        } else {
            line1 = typeLabel || 'Cancellation policy';
        }

        const bits = [];
        cancelRules.slice(0, 2).forEach(function (policy) {
            const window = formatPolicyWindow(policy.fromDate, policy.toDate);
            bits.push((window ? window + ': ' : '') + formatPolicyPenalty(policy, currency));
        });

        if (noShowRules[0]) {
            const policy = noShowRules[0];
            const fromLabel = formatOnlineCancelDate(policy.fromDate);
            const pct = parseFloat(policy.percent);
            // 100% no-show → plain "no cancellation allowed" (agent-facing)
            if (Number.isFinite(pct) && pct >= 100) {
                bits.push((fromLabel ? 'from ' + fromLabel + ': ' : '') + 'No cancellation allowed');
            } else {
                bits.push(
                    'No-show' + (fromLabel ? ' from ' + fromLabel : '') + ': ' + formatPolicyPenalty(policy, currency)
                );
            }
        }

        return {
            line1: line1,
            line2: bits.join(' · '),
        };
    }

    function renderOnlineCancellationPolicy(room) {
        const box = document.getElementById('onlineCancellationPolicy');
        const line1El = document.getElementById('onlineCancellationPolicyLine1');
        const line2El = document.getElementById('onlineCancellationPolicyLine2');
        if (!box || !line1El || !line2El) return;

        const summary = summarizeOnlineCancellationPolicy(room);
        if (!summary || !summary.line1) {
            box.classList.add('d-none');
            line1El.textContent = '';
            line2El.textContent = '';
            line2El.classList.add('d-none');
            return;
        }

        line1El.innerHTML = '<i class="ri-calendar-close-line me-1"></i>' + escapeHtml(summary.line1);
        if (summary.line2) {
            line2El.textContent = summary.line2;
            line2El.classList.remove('d-none');
        } else {
            line2El.textContent = '';
            line2El.classList.add('d-none');
        }
        box.classList.remove('d-none');
    }

    function clearOnlineCancellationPolicy() {
        renderOnlineCancellationPolicy(null);
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    /**
     * Everything needed to confirm this booking with the supplier later: the session and
     * rate keys from SearchHotel plus the choices the agent made in this modal.
     */
    function buildOnlineBookingRecord(hotel, selection) {
        const room = onlineSelectedRoom || onlineCurrentRooms[0] || null;
        const supplied = (room && room.booking) ? room.booking : null;

        if (!supplied && !room) {
            return null;
        }

        const record = supplied
            ? JSON.parse(JSON.stringify(supplied))
            : {
                supplier_code: onlineLastSupplierCode || hotel.supplier_code || 'online',
                session_id: hotel.sessionId || hotel.session_id || null,
                currency: hotelCurrency(hotel),
                check_in: onlineHotelLastSearch.checkIn,
                check_out: onlineHotelLastSearch.checkOut,
                search: {
                    city_name: onlineHotelLastSearch.city,
                    pax_info: onlineHotelLastSearch.paxInfo || buildPaxInfo(),
                    rooms: onlineHotelLastSearch.rooms || selectedRoomCount()
                },
                hotel: {
                    code: hotelId(hotel),
                    name: hotelLabel(hotel),
                    rating: hotel.starRating || hotel.star_rating || '',
                    address: hotel.address || '',
                    image: (hotel.images && hotel.images[0]) || '',
                    check_in_time: hotel.check_in_time || '',
                    check_out_time: hotel.check_out_time || ''
                },
                room: {
                    code: room.roomId || room.room_id || '',
                    name: room.roomName || room.room_name || '',
                    meal_plan_name: room.mealPlanName || room.meal_plan || '',
                    rate_key: room.rateKey || room.rate_plan_id || '',
                    net_price: roomDisplayPrice(room),
                    cancellation_policies: room.cancellationPolicy || room.cancellation_policy || []
                },
                raw_room: room.raw || room,
                fetched_at: new Date().toISOString()
            };

        // The markup stack this room was priced with, so the pre-approval recheck can
        // re-apply it to the supplier's fresh price instead of guessing.
        if (room && room.markup && !record.markup) {
            record.markup = room.markup;
        }

        record.selection = {
            room_type: selection.roomType || '',
            bed_type: selection.bedType || '',
            meal_plan: selection.mealPlan || '',
            persons: selection.selectedPersons || 1,
            number_of_rooms: selection.numberOfRooms || 1,
            price: selection.price || 0,
            nights: selection.nights || [],
            stay_check_in: selection.checkIn || '',
            stay_check_out: selection.checkOut || '',
            remarks: selection.remarks || ''
        };

        record.api_environment = onlineLastApiEnvironment
            || record.api_environment
            || hotel.api_environment
            || (room && room.api_environment)
            || '';

        return record;
    }

    function applySelectedRoomDetails(room) {
        onlineSelectedRoom = room || null;
        if (!room) {
            clearOnlineCancellationPolicy();
            clearOnlineMealPlanSelect();
            return;
        }

        const price = roomDisplayPrice(room);
        if (price > 0) {
            document.getElementById('onlineRoomPriceDisplay').value = price.toFixed(2);
        } else {
            document.getElementById('onlineRoomPriceDisplay').value = '0.00';
        }

        populateOnlineBedTypes(room);
        renderOnlineCancellationPolicy(room);
    }

    function populateOnlineRooms(hotel) {
        const roomSel = document.getElementById('onlineRoomTypeSelect');
        const bedSel = document.getElementById('onlineBedTypeSelect');
        roomSel.innerHTML = '<option value="">Room Type</option>';
        bedSel.innerHTML = '<option value="">Bed Type</option>';
        bedSel.disabled = true;
        clearOnlineMealPlanSelect();

        const rooms = hotel.rooms || hotel.roomTypes || hotel.room_types || [];
        onlineCurrentRooms = Array.isArray(rooms) ? rooms : [];
        onlineSelectedRoom = null;
        if (Array.isArray(rooms) && rooms.length) {
            // One Room Type option per room code; meals are chosen separately.
            const seenGroups = {};
            const groupOrder = [];
            onlineCurrentRooms.forEach(function (room, idx) {
                const key = roomGroupKey(room);
                if (!seenGroups[key]) {
                    seenGroups[key] = { key: key, label: roomGroupLabel(room), firstIdx: idx, minPrice: roomDisplayPrice(room) };
                    groupOrder.push(seenGroups[key]);
                } else {
                    const p = roomDisplayPrice(room);
                    if (p > 0 && (seenGroups[key].minPrice <= 0 || p < seenGroups[key].minPrice)) {
                        seenGroups[key].minPrice = p;
                        seenGroups[key].firstIdx = idx;
                    }
                }
            });

            groupOrder.forEach(function (group) {
                const opt = document.createElement('option');
                opt.value = group.key;
                opt.textContent = group.label;
                opt.dataset.groupKey = group.key;
                opt.dataset.index = String(group.firstIdx);
                if (group.minPrice > 0) {
                    opt.dataset.price = String(group.minPrice);
                }
                roomSel.appendChild(opt);
            });

            roomSel.disabled = false;
            roomSel.selectedIndex = 1;
            const firstGroup = groupOrder[0];
            const selected = populateOnlineMealsForGroup(firstGroup.key, firstGroup.firstIdx);
            applySelectedRoomDetails(selected);
        } else {
            roomSel.disabled = true;
            clearOnlineCancellationPolicy();

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
        let ok = onlineHotelSelectHasValue() && onlineSelectedNights.length > 0;
        if (onlineTwoStep) {
            // Without a priced room there is no rate key to book against.
            ok = ok && !onlineRoomsLoading && !!onlineSelectedRoom;
        } else if (onlineCurrentRooms.length > 0) {
            // Meal plan option value is the room index — require a concrete rate row.
            ok = ok && !!onlineSelectedRoom;
        }
        if (btn) btn.disabled = !ok;
    }

    window.applyOnlineHotelModalState = function (saved) {
        saved = saved || {};
        onlineHotelHydrating = true;
        const booking = saved.onlineHotelBooking || {};
        const sel = booking.selection || {};
        const checkIn = String(saved.checkIn || saved.stay_check_in || sel.stay_check_in || booking.check_in || '').substring(0, 10);
        const checkOut = String(saved.checkOut || saved.stay_check_out || sel.stay_check_out || booking.check_out || '').substring(0, 10);
        const cityName = saved.city || saved.cityValue || '';

        if (checkIn) {
            const checkInEl = document.getElementById('onlineHotelCheckIn');
            if (checkInEl) checkInEl.value = checkIn;
        }
        if (checkOut) {
            const checkOutEl = document.getElementById('onlineHotelCheckOut');
            if (checkOutEl) checkOutEl.value = checkOut;
        }
        const cityEl = document.getElementById('onlineHotelCity');
        if (cityEl && cityName) {
            let has = Array.from(cityEl.options || []).some(function (o) { return o.value === cityName || o.textContent === cityName; });
            if (!has) {
                const o = document.createElement('option');
                o.value = cityName;
                o.textContent = cityName;
                cityEl.appendChild(o);
            }
            cityEl.value = cityName;
        }

        onlineHotelLastSearch = {
            checkIn: checkIn || onlineHotelLastSearch.checkIn,
            checkOut: checkOut || onlineHotelLastSearch.checkOut,
            city: cityEl?.value || cityName || onlineHotelLastSearch.city,
            paxInfo: document.getElementById('onlineHotelPaxInfo')?.value || onlineHotelLastSearch.paxInfo || ''
        };

        const hotelRaw = saved.onlineHotelRaw || {
            hotelId: saved.id,
            hotel_id: saved.id,
            id: saved.id,
            hotelName: saved.name,
            name: saved.name,
            rooms: booking.room ? [booking.room] : []
        };
        onlineTwoStep = !!(booking.room && booking.room.rate_key);
        populateOnlineHotels([hotelRaw]);

        const roomsEl = document.getElementById('onlineNumberOfRooms');
        if (roomsEl) roomsEl.value = String(saved.numberOfRooms || sel.number_of_rooms || 1);
        const personsEl = document.getElementById('onlineSelectedPersons');
        if (personsEl) personsEl.value = String(saved.selectedPersons || sel.selected_persons || 1);
        const mealEl = document.getElementById('onlineMealPlanSelect');
        if (mealEl && (saved.mealPlan || sel.meal_plan)) mealEl.value = saved.mealPlan || sel.meal_plan;
        const remarksEl = document.getElementById('onlineHotelRemarks');
        if (remarksEl) remarksEl.value = saved.remarks || sel.remarks || '';

        const rooms = hotelRaw.rooms || hotelRaw.roomTypes || hotelRaw.room_types || (booking.room ? [booking.room] : []);
        if (Array.isArray(rooms) && rooms.length) {
            populateOnlineRooms(Object.assign({}, hotelRaw, { rooms: rooms }));
            const wantRoom = saved.roomType || sel.room_type || '';
            const roomSel = document.getElementById('onlineRoomTypeSelect');
            if (roomSel && wantRoom) {
                const match = Array.from(roomSel.options).find(function (o) {
                    return o.value === wantRoom || (o.textContent || '').indexOf(wantRoom) !== -1;
                });
                if (match) {
                    roomSel.value = match.value;
                    const idx = parseInt(match.dataset.index, 10);
                    if (!isNaN(idx) && rooms[idx]) {
                        onlineSelectedRoom = rooms[idx];
                        applySelectedRoomDetails(rooms[idx]);
                    }
                }
            }
            const wantBed = saved.bedType || sel.bed_type || '';
            const bedSel = document.getElementById('onlineBedTypeSelect');
            if (bedSel && wantBed) {
                const bedMatch = Array.from(bedSel.options).find(function (o) { return o.value === wantBed || o.textContent === wantBed; });
                if (bedMatch) bedSel.value = bedMatch.value;
                else {
                    const extra = document.createElement('option');
                    extra.value = wantBed;
                    extra.textContent = wantBed;
                    bedSel.appendChild(extra);
                    bedSel.value = wantBed;
                    bedSel.disabled = false;
                }
            }
        } else if (booking.room) {
            onlineSelectedRoom = booking.room;
            onlineCurrentRooms = [booking.room];
        }

        const price = parseFloat(saved.price || saved.combinedRoomTotal || sel.price || 0) || 0;
        const priceEl = document.getElementById('onlineRoomPriceDisplay');
        if (priceEl && price > 0) priceEl.value = price.toFixed(2);

        const nights = Array.isArray(saved.nights) ? saved.nights.slice() : (Array.isArray(sel.nights) ? sel.nights.slice() : []);
        const planNights = checkIn && checkOut ? Math.max(nights.length, countDaysBetween(checkIn, checkOut)) : nights.length;
        showOnlineHotelSelectionPanel({
            start: checkIn || getOnlineNightPlanContext().start,
            nights: planNights || getOnlineNightPlanContext().nights
        });
        if (nights.length) {
            setOnlineSelectedNights(nights.map(Number).filter(function (n) { return n > 0; }));
        }
        setOnlineHotelSelectEnabled(true);
        validateOnlineAddBtn();
        setTimeout(function () { onlineHotelHydrating = false; }, 0);
    };

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
        modalEl.addEventListener('shown.bs.modal', function onShownOnce() {
            modalEl.removeEventListener('shown.bs.modal', onShownOnce);
            const saved = window.__enquiryProOnlineHotelEditSnapshot;
            if (saved && typeof window.applyOnlineHotelModalState === 'function') {
                window.applyOnlineHotelModalState(saved);
            } else if (onlineHotelsCache.length > 0) {
                setOnlineHotelSelectEnabled(true);
            }
        });
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

        applyOnlineStayDateBounds();
        const boundedIn = document.getElementById('onlineHotelCheckIn')?.value || checkIn;
        const boundedOut = document.getElementById('onlineHotelCheckOut')?.value || checkOut;
        if (!onlineDatesWithinStayBounds(boundedIn, boundedOut)) {
            if (typeof showNotification === 'function') {
                const rangeHint = (onlineStayDateBounds.start && onlineStayDateBounds.end)
                    ? (' Allowed: ' + onlineStayDateBounds.start + ' → ' + onlineStayDateBounds.end + '.')
                    : '';
                showNotification('Dates must stay within the selected city stay range.' + rangeHint, 'warning');
            }
            return;
        }

        hideOnlineHotelSelectionPanel();
        setOnlineHotelFetchLoading(true);
        if (statusEl) statusEl.textContent = '';

        const roomCount = selectedRoomCount();
        onlineHotelLastSearch = { checkIn: boundedIn, checkOut: boundedOut, city: city, paxInfo: paxInfo, rooms: roomCount };
        const fetchedNightPlan = { start: boundedIn, nights: countDaysBetween(boundedIn, boundedOut) };

        fetch(fetchUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ checkIn: boundedIn, checkOut: boundedOut, city: city, paxInfo: paxInfo, rooms: roomCount })
        })
        .then(r => r.json())
        .then(data => {
            if (data && data.success) {
                onlineLastSupplierCode = data.supplier_code || data.supplier_name || '';
                onlineLastApiEnvironment = data.api_environment || onlineLastApiEnvironment || '';
                onlineTwoStep = !!data.two_step;
                const hotels = extractHotelsFromResponse(data);
                populateOnlineHotels(hotels);

                if (hotels.length > 0) {
                    showOnlineHotelSelectionPanel(fetchedNightPlan.nights > 0 ? fetchedNightPlan : null);
                    if (statusEl) {
                        statusEl.textContent = hotels.length + ' hotel(s) found.'
                            + (onlineTwoStep ? ' Pick one to load live rates.' : '');
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
        document.getElementById(id)?.addEventListener('change', function () {
            if (id === 'onlineHotelCheckIn' || id === 'onlineHotelCheckOut') {
                applyOnlineStayDateBounds();
            }
            onOnlineHotelSearchCriteriaChange();
        });
    });

    document.getElementById('onlineNumberOfRooms')?.addEventListener('change', onOnlineRoomCountChange);

    function onOnlineHotelSelectChange() {
        clearTimeout(onlineHotelSelectDebounce);
        onlineHotelSelectDebounce = setTimeout(handleOnlineHotelSelectChange, 0);
    }

    function handleOnlineHotelSelectChange() {
        if (onlineHotelHydrating) return;
        const idx = getOnlineHotelSelectIndex();
        const hotel = (idx >= 0 && onlineHotelSelectHasValue()) ? onlineHotelsCache[idx] : null;

        if (!hotel) {
            onlineRoomsRequestId++;
            onlineRoomsFetchKey = '';
            if (onlineRoomsAbortController) {
                try {
                    onlineRoomsAbortController.abort();
                } catch (e) { /* ignore */ }
                onlineRoomsAbortController = null;
            }
            onlineSelectedHotelDetail = null;
            onlineSelectedRoom = null;
            populateOnlineRooms({ rooms: [] });
            setOnlineRoomsLoading(false);
            setOnlineRoomStatus(onlineTwoStep && onlineHotelsCache.length ? 'Select a hotel to load live rates.' : '');
            validateOnlineAddBtn();
            return;
        }

        if (onlineTwoStep) {
            loadOnlineHotelRooms(hotel);
        } else {
            const hasRooms = Array.isArray(hotel.rooms) && hotel.rooms.length > 0;
            if (hasRooms) {
                populateOnlineRooms(hotel);
            } else {
                // Fallback: SearchHotel-first hotels normally ship rooms; refresh one hotel if missing.
                loadOnlineHotelRooms(hotel);
            }
        }

        validateOnlineAddBtn();
    }

    bindOnlineHotelSelectEvents();

    document.getElementById('onlineRoomTypeSelect')?.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const groupKey = opt?.dataset?.groupKey || opt?.value || '';
        if (!groupKey || this.selectedIndex <= 0) {
            onlineSelectedRoom = null;
            clearOnlineMealPlanSelect();
            clearOnlineCancellationPolicy();
            document.getElementById('onlineRoomPriceDisplay').value = '0.00';
            validateOnlineAddBtn();
            return;
        }

        const preferredIdx = opt?.dataset?.index !== undefined ? parseInt(opt.dataset.index, 10) : -1;
        const selected = populateOnlineMealsForGroup(groupKey, preferredIdx);
        applySelectedRoomDetails(selected);
        validateOnlineAddBtn();
    });

    document.getElementById('onlineMealPlanSelect')?.addEventListener('change', function () {
        const idx = parseInt(this.value, 10);
        const room = (!isNaN(idx) && idx >= 0) ? onlineCurrentRooms[idx] : null;
        if (room) {
            applySelectedRoomDetails(room);
        } else {
            onlineSelectedRoom = null;
            clearOnlineCancellationPolicy();
            document.getElementById('onlineRoomPriceDisplay').value = '0.00';
        }
        validateOnlineAddBtn();
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
        // Night buttons UI may be hidden — fill from check-in/out automatically.
        if (!onlineSelectedNights.length) {
            selectAllOnlineNights(nightPlan);
        }
        if (!onlineSelectedNights.length) {
            alert('Please set hotel check-in and check-out dates before adding.');
            return;
        }

        const idx = getOnlineHotelSelectIndex();
        const hotelRaw = onlineSelectedHotelDetail || (idx >= 0 ? onlineHotelsCache[idx] : null);
        if (!hotelRaw) return;

        if ((onlineTwoStep || onlineCurrentRooms.length > 0) && !onlineSelectedRoom) {
            alert('Please select a room and meal plan before adding this hotel.');
            return;
        }

        const nightNumbers = onlineSelectedNights.slice().sort((a, b) => a - b);
        const startNight = Math.min(...nightNumbers);
        const endNight = Math.max(...nightNumbers);
        const checkInDateStr = addDaysToDateStr(planStart, startNight - 1);
        const checkOutDateStr = addDaysToDateStr(planStart, endNight);
        const checkInDate = typeof moment !== 'undefined' ? moment(checkInDateStr).format('MMM DD') : formatNightDateLabel(planStart, startNight - 1);
        const checkOutDate = typeof moment !== 'undefined' ? moment(checkOutDateStr).format('MMM DD') : formatNightDateLabel(planStart, endNight);
        const roomTypeSel = document.getElementById('onlineRoomTypeSelect');
        const roomType = onlineSelectedRoom
            ? roomGroupLabel(onlineSelectedRoom)
            : (roomTypeSel?.selectedOptions?.[0]?.textContent || roomTypeSel?.value || 'Standard');
        const bedType = document.getElementById('onlineBedTypeSelect')?.value || '';
        const mealPlan = onlineSelectedRoom
            ? (roomMealLabel(onlineSelectedRoom) || roomMealOptionLabel(onlineSelectedRoom) || 'Not specified')
            : 'Not specified';
        const selectedPersons = parseInt(document.getElementById('onlineSelectedPersons')?.value, 10) || 1;
        const numberOfRooms = parseInt(document.getElementById('onlineNumberOfRooms')?.value, 10) || 1;
        const price = parseFloat(document.getElementById('onlineRoomPriceDisplay')?.value) || 0;
        const cityName = document.getElementById('onlineHotelCity')?.selectedOptions?.[0]?.textContent || '';
        const remarks = document.getElementById('onlineHotelRemarks')?.value || '';

        const booking = buildOnlineBookingRecord(hotelRaw, {
            roomType: roomType,
            bedType: bedType,
            mealPlan: mealPlan,
            selectedPersons: selectedPersons,
            numberOfRooms: numberOfRooms,
            price: price,
            nights: nightNumbers,
            checkIn: checkInDateStr,
            checkOut: checkOutDateStr,
            remarks: remarks
        });

        const hotelData = {
            id: hotelId(hotelRaw) || ('online-' + Date.now()),
            name: hotelLabel(hotelRaw),
            roomType: roomType,
            roomId: (booking && booking.room && booking.room.code) ? booking.room.code : null,
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
            onlineHotelBooking: booking,
            api_environment: (booking && booking.api_environment) || onlineLastApiEnvironment || hotelRaw.api_environment || '',
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
