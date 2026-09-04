{{-- Online attraction booking modal (SG Attractions live API) --}}
<div class="modal fade" id="onlineAttractionModal" tabindex="-1" aria-labelledby="onlineAttractionModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%); color: #fff;">
                <h5 class="modal-title" id="onlineAttractionModalLabel">
                    <i class="ri-global-line me-1"></i> Online Attraction Booking
                    <small class="d-block opacity-75" id="onlineAttractionTargetLabel" style="font-size: 0.75rem;"></small>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="background: #f8f9fa;">
                <div class="alert alert-info py-2 mb-3" style="font-size: 0.85rem;">
                    <i class="ri-information-line me-1"></i>
                    Attractions are fetched live from SG Attractions. Select visit date and guests, then click <strong>Fetch Attractions</strong>.
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-building-line me-1"></i>City</label>
                                <select class="form-select form-select-sm" id="onlineAttractionCity"></select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-calendar-line me-1"></i>Visit Date</label>
                                <input type="date" class="form-control form-control-sm" id="onlineAttractionVisitDate">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-group-line me-1"></i>Guests</label>
                                <div class="guest-selector">
                                    <div class="guest-display border rounded d-flex align-items-start justify-content-between" style="min-height: 34px; padding: 0.3rem 0.75rem; background: #f8f9fa; border: 1px solid #dee2e6 !important; border-radius: 8px;">
                                        <div class="guest-info d-flex flex-column gap-1" style="flex: 1;">
                                            <span id="onlineAttractionGuestSummary" class="d-flex flex-column gap-1" style="font-size: 0.8rem;">
                                                <span class="text-muted small">—</span>
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="onlineAttractionGuestEditBtn" style="border-radius: 6px; padding: 0.25rem 0.5rem; margin-left: 0.5rem; flex-shrink: 0;">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" id="onlineAttractionPaxInfo" value="">
                            </div>
                        </div>
                        <div class="mt-3 d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-primary" id="onlineAttractionFetchBtn" style="background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%); border: none;">
                                <span class="spinner-border spinner-border-sm d-none me-1" id="onlineAttractionFetchSpinner" role="status" aria-hidden="true"></span>
                                <i class="ri-search-line me-1" id="onlineAttractionFetchIcon"></i> Fetch Attractions
                            </button>
                            <small class="text-muted" id="onlineAttractionFetchStatus" style="font-size: 0.8rem;"></small>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm d-none" id="onlineAttractionSelectionPanel">
                    <div class="card-body">
                        <div class="row g-2 mb-2">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-ticket-line me-1"></i>Select Attraction</label>
                                <select class="form-select form-select-sm" id="onlineAttractionSelect" disabled>
                                    <option value="">Fetch attractions first</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-time-line me-1"></i>Time Slot</label>
                                <select class="form-select form-select-sm" id="onlineAttractionTimeSelect" disabled>
                                    <option value="">Select attraction first</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-coupon-line me-1"></i>Select Ticket</label>
                                <select class="form-select form-select-sm" id="onlineAttractionTicketSelect" disabled>
                                    <option value="">Select attraction first</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-money-dollar-circle-line me-1"></i>Estimated Price</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text" id="onlineAttractionCurrency">SGD</span>
                                    <input type="text" class="form-control" id="onlineAttractionPriceDisplay" value="0.00" readonly style="background:#f8f9fa; text-align:right;">
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold mb-2" style="font-size: 0.85rem;"><i class="ri-chat-quote-line me-1"></i>Remarks</label>
                            <textarea class="form-control form-control-sm" id="onlineAttractionRemarks" rows="2" placeholder="Optional notes for this online attraction booking..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success d-none" id="onlineAttractionAddBtn" disabled>
                    <i class="ri-add-line me-1"></i> Add Attraction
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Guest selector for online attraction search (editable pax) --}}
<div class="modal fade" id="onlineAttractionGuestModal" tabindex="-1" aria-labelledby="onlineAttractionGuestModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 10px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%); border: none; padding: 1rem 1.25rem;">
                <h5 class="modal-title fw-bold d-flex align-items-center mb-0 text-white" id="onlineAttractionGuestModalLabel" style="font-size: 1rem;">
                    <i class="ri-group-line me-2"></i> Select Guests for Attraction Search
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.25rem; background: #ffffff;">
                <p class="text-muted small mb-3" id="onlineAttractionGuestLimitHint" style="font-size: 0.8rem;"></p>
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
                                        <button type="button" class="btn btn-outline-secondary btn-sm online-attraction-guest-adults-minus" style="width: 36px; height: 36px; border-radius: 6px;"><i class="ri-subtract-line"></i></button>
                                        <span class="mx-3 fw-bold" id="onlineAttractionModalAdults" style="font-size: 1.5rem; min-width: 32px;">1</span>
                                        <button type="button" class="btn btn-outline-secondary btn-sm online-attraction-guest-adults-plus" style="width: 36px; height: 36px; border-radius: 6px;"><i class="ri-add-line"></i></button>
                                    </div>
                                </div>
                                <div class="guest-counter mb-3 text-center">
                                    <label class="form-label fw-semibold mb-2 d-block" style="font-size: 0.85rem;">Male</label>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-online-attraction-guest="male" data-delta="-1" style="width: 36px; height: 36px; border-radius: 6px;"><i class="ri-subtract-line"></i></button>
                                        <span class="mx-3 fw-bold" id="onlineAttractionModalMale" style="font-size: 1.5rem; min-width: 32px;">1</span>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-online-attraction-guest="male" data-delta="1" style="width: 36px; height: 36px; border-radius: 6px;"><i class="ri-add-line"></i></button>
                                    </div>
                                </div>
                                <div class="guest-counter text-center">
                                    <label class="form-label fw-semibold mb-2 d-block" style="font-size: 0.85rem;">Female</label>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-online-attraction-guest="female" data-delta="-1" style="width: 36px; height: 36px; border-radius: 6px;"><i class="ri-subtract-line"></i></button>
                                        <span class="mx-3 fw-bold" id="onlineAttractionModalFemale" style="font-size: 1.5rem; min-width: 32px;">0</span>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-online-attraction-guest="female" data-delta="1" style="width: 36px; height: 36px; border-radius: 6px;"><i class="ri-add-line"></i></button>
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
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-online-attraction-guest="children" data-delta="-1" style="width: 36px; height: 36px; border-radius: 6px;"><i class="ri-subtract-line"></i></button>
                                        <span class="mx-3 fw-bold" id="onlineAttractionModalChildren" style="font-size: 1.5rem; min-width: 32px;">0</span>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-online-attraction-guest="children" data-delta="1" style="width: 36px; height: 36px; border-radius: 6px;"><i class="ri-add-line"></i></button>
                                    </div>
                                    <div id="onlineAttractionChildAgesSection" class="mt-3 text-start" style="display: none;">
                                        <label class="form-label fw-semibold mb-2 d-block" style="font-size: 0.8rem;">Child ages</label>
                                        <div id="onlineAttractionChildAgeDropdowns" class="d-flex flex-column gap-2"></div>
                                    </div>
                                </div>
                                <div class="guest-counter text-center">
                                    <label class="form-label fw-semibold mb-2 d-block" style="font-size: 0.85rem;">Infants <small class="text-muted fw-normal">(under 1)</small></label>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-online-attraction-guest="infants" data-delta="-1" style="width: 36px; height: 36px; border-radius: 6px;"><i class="ri-subtract-line"></i></button>
                                        <span class="mx-3 fw-bold" id="onlineAttractionModalInfants" style="font-size: 1.5rem; min-width: 32px;">0</span>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-online-attraction-guest="infants" data-delta="1" style="width: 36px; height: 36px; border-radius: 6px;"><i class="ri-add-line"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="onlineAttractionGuestApplyBtn" style="background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%); border: none;">Apply</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    #onlineAttractionModal .select2-container { width: 100% !important; }
    #onlineAttractionModal .select2-container--default .select2-selection--single {
        height: 31px;
        min-height: 31px;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }
    #onlineAttractionModal .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 29px;
        padding-left: 0.5rem;
    }
    #onlineAttractionModal .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 29px;
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const fetchUrl = @json(route('fetch-online-attractions'));
    const csrfToken = @json(csrf_token());

    let onlineAttractionsCache = [];
    let onlineCurrentTickets = [];
    let onlineAttractionTarget = { day: 1, index: 1 };
    let onlineAttractionGuestState = { male: 1, female: 0, children: 0, infants: 0, childAges: [] };

    function initOnlineAttractionSelect2(disabled) {
        if (typeof jQuery === 'undefined' || !jQuery.fn.select2) {
            return;
        }

        const $sel = jQuery('#onlineAttractionSelect');
        if (!$sel.length) {
            return;
        }

        if ($sel.hasClass('select2-hidden-accessible')) {
            $sel.select2('destroy');
        }

        $sel.select2({
            placeholder: 'Search attraction...',
            allowClear: true,
            width: '100%',
            dropdownParent: jQuery('#onlineAttractionModal'),
        });

        $sel.prop('disabled', !!disabled);
    }

    function getOnlineAttractionSelectIndex() {
        if (typeof jQuery !== 'undefined') {
            const $sel = jQuery('#onlineAttractionSelect');
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

        const sel = document.getElementById('onlineAttractionSelect');
        if (sel && sel.selectedIndex >= 0 && sel.options[sel.selectedIndex]) {
            const idx = sel.options[sel.selectedIndex].dataset.index;
            if (idx !== undefined) {
                return parseInt(idx, 10);
            }
        }

        return -1;
    }

    function onlineAttractionSelectHasValue() {
        if (typeof jQuery !== 'undefined') {
            const value = jQuery('#onlineAttractionSelect').val();
            if (value) {
                return true;
            }
        }

        return !!document.getElementById('onlineAttractionSelect')?.value;
    }

    function bindAttractionSourceToggle() {
        $(document).off('change.attractionSlotSource', '.attraction-slot-source-radio');
        $(document).on('change.attractionSlotSource', '.attraction-slot-source-radio', function () {
            const day = parseInt(this.dataset.day, 10) || 1;
            const index = parseInt(this.dataset.index, 10) || 1;
            const isOnline = this.value === 'online';
            const offlinePanel = document.getElementById('day' + day + '_attraction_' + index + '_offline_panel');
            const onlineHint = document.getElementById('day' + day + '_attraction_' + index + '_online_hint');
            if (offlinePanel) {
                offlinePanel.classList.toggle('d-none', isOnline);
            }
            if (onlineHint) {
                onlineHint.classList.toggle('d-none', !isOnline);
            }
            if (isOnline) {
                window.openOnlineAttractionModal(day, index);
            } else {
                const modalEl = document.getElementById('onlineAttractionModal');
                if (modalEl && window.bootstrap) {
                    bootstrap.Modal.getInstance(modalEl)?.hide();
                }
            }
        });
    }

    window.buildAttractionSlotSourceToggleHtml = function (day, index) {
        return '<div class="mb-3 attraction-slot-source-block d-none">' +
            '<label class="form-label fw-semibold mb-2" style="color: #495057; font-size: 0.85rem;"><i class="ri-toggle-line me-1"></i>Attraction Source · Slot #' + index + '</label>' +
            '<div class="d-flex flex-wrap gap-4">' +
            '<div class="form-check">' +
            '<input class="form-check-input attraction-slot-source-radio" type="radio" name="attractionSourceType_day' + day + '_slot' + index + '" id="attractionSourceOffline_day' + day + '_slot' + index + '" value="offline" data-day="' + day + '" data-index="' + index + '" checked>' +
            '<label class="form-check-label" for="attractionSourceOffline_day' + day + '_slot' + index + '" style="font-size: 0.85rem;"><i class="ri-database-2-line me-1"></i> Offline Attractions</label>' +
            '</div>' +
            '<div class="form-check">' +
            '<input class="form-check-input attraction-slot-source-radio" type="radio" name="attractionSourceType_day' + day + '_slot' + index + '" id="attractionSourceOnline_day' + day + '_slot' + index + '" value="online" data-day="' + day + '" data-index="' + index + '">' +
            '<label class="form-check-label" for="attractionSourceOnline_day' + day + '_slot' + index + '" style="font-size: 0.85rem;"><i class="ri-global-line me-1"></i> Online Attractions</label>' +
            '</div>' +
            '</div>' +
            '<small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Offline uses DMC inventory. Online opens live API search for this slot.</small>' +
            '</div>' +
            '<div class="attraction-slot-online-hint d-none alert alert-info py-2 mb-3" id="day' + day + '_attraction_' + index + '_online_hint" style="font-size: 0.8rem;">' +
            '<i class="ri-global-line me-1"></i>Use the popup to fetch and select an online attraction for this slot.' +
            '</div>' +
            '<div class="attraction-slot-offline-panel" id="day' + day + '_attraction_' + index + '_offline_panel">';
    };

    function setSlotAttractionSource(day, index, source) {
        const selector = 'input[name="attractionSourceType_day' + day + '_slot' + index + '"][value="' + source + '"]';
        const radio = document.querySelector(selector);
        if (radio && !radio.checked) {
            radio.click();
        } else if (radio && source === 'offline') {
            const offlinePanel = document.getElementById('day' + day + '_attraction_' + index + '_offline_panel');
            const onlineHint = document.getElementById('day' + day + '_attraction_' + index + '_online_hint');
            if (offlinePanel) offlinePanel.classList.remove('d-none');
            if (onlineHint) onlineHint.classList.add('d-none');
        }
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

    function getOnlineAttractionTotalAdults() {
        return Math.max(1, onlineAttractionGuestState.male + onlineAttractionGuestState.female);
    }

    function notifyOnlineAttractionGuestLimit(message) {
        if (typeof showNotification === 'function') {
            showNotification(message, 'warning');
        }
    }

    function clampOnlineAttractionGuestState() {
        const limits = getMainGuestLimits();

        onlineAttractionGuestState.children = Math.min(Math.max(0, onlineAttractionGuestState.children), limits.children);
        onlineAttractionGuestState.infants = Math.min(Math.max(0, onlineAttractionGuestState.infants), limits.infants);
        onlineAttractionGuestState.male = Math.max(0, onlineAttractionGuestState.male);
        onlineAttractionGuestState.female = Math.max(0, onlineAttractionGuestState.female);

        let totalAdults = getOnlineAttractionTotalAdults();
        if (totalAdults > limits.adults) {
            let excess = totalAdults - limits.adults;
            const maleReduce = Math.min(onlineAttractionGuestState.male, excess);
            onlineAttractionGuestState.male -= maleReduce;
            excess -= maleReduce;
            onlineAttractionGuestState.female = Math.max(0, onlineAttractionGuestState.female - excess);
        }

        if (getOnlineAttractionTotalAdults() < 1) {
            onlineAttractionGuestState.male = Math.min(1, limits.adults);
            onlineAttractionGuestState.female = 0;
        }
    }

    function loadOnlineAttractionGuestStateFromMain() {
        const mainMale = parseInt(document.getElementById('male')?.value, 10) || 0;
        const mainFemale = parseInt(document.getElementById('female')?.value, 10) || 0;
        const mainAdults = parseInt(document.getElementById('adults')?.value, 10) || 1;
        const mainChildren = parseInt(document.getElementById('children')?.value, 10) || 0;
        const mainInfants = parseInt(document.getElementById('infants')?.value, 10) || 0;

        onlineAttractionGuestState.male = mainMale;
        onlineAttractionGuestState.female = mainFemale;
        if (mainMale + mainFemale === 0) {
            onlineAttractionGuestState.male = mainAdults;
            onlineAttractionGuestState.female = 0;
        }
        onlineAttractionGuestState.children = mainChildren;
        onlineAttractionGuestState.infants = mainInfants;

        try {
            const raw = document.getElementById('child_ages')?.value || '[]';
            const parsed = JSON.parse(raw);
            onlineAttractionGuestState.childAges = Array.isArray(parsed) ? parsed.map(function (a) { return parseInt(a, 10); }).filter(function (a) { return !isNaN(a); }) : [];
        } catch (e) {
            onlineAttractionGuestState.childAges = [];
        }

        clampOnlineAttractionGuestState();
    }

    function getAttractionGuestCounts() {
        const male = Math.max(0, onlineAttractionGuestState.male);
        const female = Math.max(0, onlineAttractionGuestState.female);
        const adults = Math.max(1, male + female);
        const children = Math.max(0, onlineAttractionGuestState.children);
        const infants = Math.max(0, onlineAttractionGuestState.infants);
        return { adults, male, female, children, infants };
    }

    function renderOnlineAttractionGuestSummary() {
        const { adults, male, female, children, infants } = getAttractionGuestCounts();
        const guestSummary = document.getElementById('onlineAttractionGuestSummary');
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

    function syncOnlineAttractionGuestDerivedFields() {
        renderOnlineAttractionGuestSummary();
        const paxEl = document.getElementById('onlineAttractionPaxInfo');
        if (paxEl) paxEl.value = buildPaxInfo();
    }

    function renderOnlineAttractionGuestModalCounters() {
        const { adults, male, female, children, infants } = getAttractionGuestCounts();
        const setText = function (id, val) {
            const el = document.getElementById(id);
            if (el) el.textContent = String(val);
        };
        setText('onlineAttractionModalAdults', adults);
        setText('onlineAttractionModalMale', male);
        setText('onlineAttractionModalFemale', female);
        setText('onlineAttractionModalChildren', children);
        setText('onlineAttractionModalInfants', infants);
    }

    function updateOnlineAttractionChildAgeDropdowns() {
        const section = document.getElementById('onlineAttractionChildAgesSection');
        const container = document.getElementById('onlineAttractionChildAgeDropdowns');
        if (!section || !container) return;

        const count = Math.max(0, onlineAttractionGuestState.children);
        if (count === 0) {
            section.style.display = 'none';
            container.innerHTML = '';
            onlineAttractionGuestState.childAges = [];
            return;
        }

        section.style.display = '';
        while (onlineAttractionGuestState.childAges.length < count) {
            onlineAttractionGuestState.childAges.push(8);
        }
        onlineAttractionGuestState.childAges = onlineAttractionGuestState.childAges.slice(0, count);

        container.innerHTML = '';
        for (let i = 0; i < count; i++) {
            let ageOptions = '';
            for (let age = 1; age <= 17; age++) {
                ageOptions += '<option value="' + age + '"' + (onlineAttractionGuestState.childAges[i] === age ? ' selected' : '') + '>' + age + ' years</option>';
            }
            const row = document.createElement('div');
            row.className = 'd-flex align-items-center gap-2';
            row.innerHTML = '<label class="text-success fw-semibold mb-0" style="min-width: 70px; font-size: 0.8rem;">Child ' + (i + 1) + ':</label>' +
                '<select class="form-select form-select-sm online-attraction-child-age-select" data-child-index="' + i + '">' + ageOptions + '</select>';
            container.appendChild(row);
        }
    }

    function adjustOnlineAttractionGuest(type, delta) {
        delta = parseInt(delta, 10) || 0;
        const limits = getMainGuestLimits();

        if (type === 'male') {
            if (delta > 0) {
                if (onlineAttractionGuestState.female < 1) {
                    notifyOnlineAttractionGuestLimit('Cannot increase male without reducing female. Use Female − or increase total adults.');
                    return;
                }
                onlineAttractionGuestState.male += 1;
                onlineAttractionGuestState.female -= 1;
            } else if (delta < 0) {
                if (onlineAttractionGuestState.male < 1) return;
                onlineAttractionGuestState.male -= 1;
                onlineAttractionGuestState.female += 1;
            }
        } else if (type === 'female') {
            if (delta > 0) {
                if (onlineAttractionGuestState.male < 1) {
                    notifyOnlineAttractionGuestLimit('Cannot increase female without reducing male. Use Male − or increase total adults.');
                    return;
                }
                onlineAttractionGuestState.female += 1;
                onlineAttractionGuestState.male -= 1;
            } else if (delta < 0) {
                if (onlineAttractionGuestState.female < 1) return;
                onlineAttractionGuestState.female -= 1;
                onlineAttractionGuestState.male += 1;
            }
        } else if (type === 'children') {
            if (delta > 0 && onlineAttractionGuestState.children >= limits.children) {
                notifyOnlineAttractionGuestLimit('Children cannot exceed tour guests (' + limits.children + ').');
                return;
            }
            onlineAttractionGuestState.children = Math.max(0, onlineAttractionGuestState.children + delta);
            updateOnlineAttractionChildAgeDropdowns();
        } else if (type === 'infants') {
            if (delta > 0 && onlineAttractionGuestState.infants >= limits.infants) {
                notifyOnlineAttractionGuestLimit('Infants cannot exceed tour guests (' + limits.infants + ').');
                return;
            }
            onlineAttractionGuestState.infants = Math.max(0, onlineAttractionGuestState.infants + delta);
        }

        clampOnlineAttractionGuestState();
        renderOnlineAttractionGuestModalCounters();
    }

    function adjustOnlineAttractionAdults(delta) {
        delta = parseInt(delta, 10) || 0;
        const limits = getMainGuestLimits();
        const newTotal = getOnlineAttractionTotalAdults() + delta;

        if (newTotal < 1) return;
        if (newTotal > limits.adults) {
            notifyOnlineAttractionGuestLimit('Adults cannot exceed tour guests (' + limits.adults + ').');
            return;
        }

        if (delta > 0) {
            onlineAttractionGuestState.male += delta;
        } else {
            let toRemove = -delta;
            const fromMale = Math.min(onlineAttractionGuestState.male, toRemove);
            onlineAttractionGuestState.male -= fromMale;
            toRemove -= fromMale;
            onlineAttractionGuestState.female = Math.max(0, onlineAttractionGuestState.female - toRemove);
        }

        clampOnlineAttractionGuestState();
        renderOnlineAttractionGuestModalCounters();
    }

    function renderOnlineAttractionGuestLimitHint() {
        const el = document.getElementById('onlineAttractionGuestLimitHint');
        if (!el) return;
        const limits = getMainGuestLimits();
        el.textContent = 'Total adults = male + female (max ' + limits.adults + ' from tour). Children max ' + limits.children + ', infants max ' + limits.infants + '.';
    }

    function openOnlineAttractionGuestSelector() {
        renderOnlineAttractionGuestModalCounters();
        updateOnlineAttractionChildAgeDropdowns();
        renderOnlineAttractionGuestLimitHint();
        const modalEl = document.getElementById('onlineAttractionGuestModal');
        if (!modalEl || !window.bootstrap) return;
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function applyOnlineAttractionGuestSelection() {
        clampOnlineAttractionGuestState();
        const childAgeSelects = document.querySelectorAll('#onlineAttractionChildAgeDropdowns .online-attraction-child-age-select');
        if (onlineAttractionGuestState.children > 0) {
            if (childAgeSelects.length !== onlineAttractionGuestState.children) {
                if (typeof showNotification === 'function') {
                    showNotification('Please select ages for all children.', 'warning');
                } else {
                    alert('Please select ages for all children.');
                }
                return;
            }
            onlineAttractionGuestState.childAges = [];
            childAgeSelects.forEach(function (select) {
                onlineAttractionGuestState.childAges.push(parseInt(select.value, 10) || 8);
            });
        } else {
            onlineAttractionGuestState.childAges = [];
        }

        syncOnlineAttractionGuestDerivedFields();
        applySelectedTicketPrice();

        const guestModal = document.getElementById('onlineAttractionGuestModal');
        if (guestModal && window.bootstrap) {
            bootstrap.Modal.getInstance(guestModal)?.hide();
        }
    }

    function getAdultsChildren() {
        const { adults, children } = getAttractionGuestCounts();
        return { adults, children };
    }

    function buildPaxInfo() {
        const { adults, children } = getAdultsChildren();
        return adults + '|' + children;
    }

    function toNumber(value) {
        const n = parseFloat(value);
        return Number.isFinite(n) ? n : 0;
    }

    function attractionId(item) {
        return String(
            item.sku_id ||
            item.attractionId ||
            item.activityId ||
            item.productId ||
            item.id ||
            item.propertyDetail?.productId ||
            ''
        );
    }

    function attractionLabel(item) {
        return item.title ||
            item.attractionName ||
            item.activityName ||
            item.name ||
            item.hotelName ||
            item.propertyDetail?.attractionName ||
            item.propertyDetail?.hotelName ||
            ('Attraction #' + (attractionId(item) || ''));
    }

    function attractionLowestPrice(item) {
        const low = toNumber(item.lowest_ticket_price ?? item.lowestPrice ?? item.lowest_price ?? 0);
        if (low > 0) {
            return low;
        }

        const tickets = buildSgTickets(item);
        if (tickets.length) {
            let min = Infinity;
            tickets.forEach(function (ticket) {
                const p = ticketAdultPrice(ticket);
                if (p > 0 && p < min) {
                    min = p;
                }
            });
            if (min !== Infinity) {
                return min;
            }
        }

        return toNumber(item.price ?? item.amount ?? 0);
    }

    function attractionCurrency(item) {
        return String(item.currency || item.currencyCode || item.currency_code || 'SGD').trim() || 'SGD';
    }

    function attractionSelectLabel(item) {
        const name = attractionLabel(item);
        const price = attractionLowestPrice(item);
        const currency = attractionCurrency(item);

        if (price > 0) {
            return name + ' - ' + currency + ' ' + price.toFixed(2);
        }
        return name;
    }

    function buildSgTickets(item) {
        const existing = item.tickets || item.ticketDetails || item.products || item.rooms || [];
        if (Array.isArray(existing) && existing.length) {
            return existing;
        }

        const low = toNumber(item.lowest_ticket_price);
        const high = toNumber(item.highest_ticket_price);
        const sku = attractionId(item);
        const tickets = [];

        if (low > 0) {
            tickets.push({
                ticketId: sku + '-standard',
                ticketName: 'Standard Ticket',
                price: { adult: low, child: low }
            });
        }
        if (high > 0 && Math.abs(high - low) > 0.0001) {
            tickets.push({
                ticketId: sku + '-premium',
                ticketName: 'Premium Ticket',
                price: { adult: high, child: high }
            });
        }
        if (!tickets.length && sku) {
            tickets.push({
                ticketId: sku + '-default',
                ticketName: 'General Admission',
                price: { adult: 0, child: 0 }
            });
        }
        return tickets;
    }

    function attractionTickets(item) {
        return buildSgTickets(item);
    }

    function attractionTimeSlots(item) {
        if (Array.isArray(item.timeSlots)) return item.timeSlots;
        if (Array.isArray(item.time_slots)) return item.time_slots;
        if (Array.isArray(item.slots)) return item.slots;
        return [];
    }

    function ticketAdultPrice(ticket) {
        const p = ticket.price || ticket.currencyConvertedPrice || {};
        return toNumber(p.adult ?? p.actual ?? p.adultPrice ?? ticket.adult_price ?? ticket.adultPrice ?? 0);
    }

    function ticketChildPrice(ticket) {
        const p = ticket.price || ticket.currencyConvertedPrice || {};
        return toNumber(p.child ?? p.childPrice ?? ticket.child_price ?? ticket.childPrice ?? 0);
    }

    function ticketSeniorPrice(ticket) {
        const p = ticket.price || ticket.currencyConvertedPrice || {};
        return toNumber(p.senior ?? p.seniorPrice ?? ticket.senior_adult_price ?? ticket.seniorPrice ?? 0);
    }

    function ticketId(ticket) {
        return String(ticket.ticketId || ticket.ticket_id || ticket.productId || ticket.id || ticket.ratePlanId || '');
    }

    function ticketLabel(ticket) {
        return ticket.ticketName || ticket.name || ticket.ratePlanName || ticket.roomName || ('Ticket #' + (ticketId(ticket) || ''));
    }

    function extractAttractionsFromResponse(data) {
        if (!data || typeof data !== 'object') return [];
        if (Array.isArray(data.attractions)) return data.attractions;
        if (Array.isArray(data?.provider?.response?.data)) return data.provider.response.data;
        if (Array.isArray(data?.provider?.data)) return data.provider.data;
        return [];
    }

    function syncOnlineAttractionTargetOptions(day) {
        // Slot is chosen when opening the modal from a specific attraction slot.
    }

    function resetOnlineAttractionFetchResults() {
        hideOnlineAttractionSelectionPanel();
        populateOnlineAttractions([]);
        const statusEl = document.getElementById('onlineAttractionFetchStatus');
        if (statusEl) statusEl.textContent = '';
    }

    function hideOnlineAttractionSelectionPanel() {
        const panel = document.getElementById('onlineAttractionSelectionPanel');
        if (panel) panel.classList.add('d-none');
        const addBtn = document.getElementById('onlineAttractionAddBtn');
        if (addBtn) {
            addBtn.classList.add('d-none');
            addBtn.disabled = true;
        }
    }

    function showOnlineAttractionSelectionPanel() {
        const panel = document.getElementById('onlineAttractionSelectionPanel');
        if (panel) panel.classList.remove('d-none');
        const addBtn = document.getElementById('onlineAttractionAddBtn');
        if (addBtn) addBtn.classList.remove('d-none');
        validateOnlineAttractionAddBtn();
    }

    function setOnlineAttractionFetchLoading(isLoading) {
        const btn = document.getElementById('onlineAttractionFetchBtn');
        const spinner = document.getElementById('onlineAttractionFetchSpinner');
        const icon = document.getElementById('onlineAttractionFetchIcon');
        if (btn) btn.disabled = !!isLoading;
        if (spinner) spinner.classList.toggle('d-none', !isLoading);
        if (icon) icon.classList.toggle('d-none', !!isLoading);
    }

    function syncOnlineAttractionDefaults(day) {
        resetOnlineAttractionFetchResults();
        onlineAttractionTarget.day = day;

        loadOnlineAttractionGuestStateFromMain();

        const citySelect = document.getElementById('day' + day + '_attraction_city_1') ||
            document.querySelector('.attraction-city-select');
        const onlineCity = document.getElementById('onlineAttractionCity');
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

        const visitDateEl = document.getElementById('onlineAttractionVisitDate');
        if (visitDateEl && typeof getTourDateForDay === 'function') {
            visitDateEl.value = getTourDateForDay(day);
        }

        syncOnlineAttractionGuestDerivedFields();

        const label = document.getElementById('onlineAttractionTargetLabel');
        if (label) {
            label.textContent = 'Day ' + day + ' · Attraction Slot #' + (onlineAttractionTarget.index || 1);
        }
    }

    function populateOnlineAttractions(attractions) {
        onlineAttractionsCache = Array.isArray(attractions) ? attractions.slice() : [];
        onlineAttractionsCache.sort(function (a, b) {
            const pa = attractionLowestPrice(a) || Infinity;
            const pb = attractionLowestPrice(b) || Infinity;
            return pa - pb;
        });

        const sel = document.getElementById('onlineAttractionSelect');
        const timeSel = document.getElementById('onlineAttractionTimeSelect');
        const ticketSel = document.getElementById('onlineAttractionTicketSelect');

        sel.innerHTML = '<option value="">Select attraction</option>';
        timeSel.innerHTML = '<option value="">Select attraction first</option>';
        ticketSel.innerHTML = '<option value="">Select attraction first</option>';
        timeSel.disabled = true;
        ticketSel.disabled = true;
        onlineCurrentTickets = [];

        onlineAttractionsCache.forEach(function (item, idx) {
            const opt = document.createElement('option');
            opt.value = attractionId(item) || String(idx);
            opt.textContent = attractionSelectLabel(item);
            opt.dataset.index = String(idx);
            opt.dataset.price = String(attractionLowestPrice(item));
            sel.appendChild(opt);
        });

        initOnlineAttractionSelect2(onlineAttractionsCache.length === 0);

        if (typeof jQuery !== 'undefined' && onlineAttractionsCache.length > 0) {
            const firstVal = attractionId(onlineAttractionsCache[0]) || '0';
            jQuery('#onlineAttractionSelect').val(firstVal).trigger('change');
        } else if (onlineAttractionsCache.length > 0) {
            sel.selectedIndex = 1;
            populateOnlineAttractionDetails(onlineAttractionsCache[0]);
        } else if (typeof jQuery !== 'undefined') {
            jQuery('#onlineAttractionSelect').val(null).trigger('change');
        }

        validateOnlineAttractionAddBtn();
    }

    function populateOnlineAttractionDetails(attraction) {
        const timeSel = document.getElementById('onlineAttractionTimeSelect');
        const ticketSel = document.getElementById('onlineAttractionTicketSelect');
        const currencyEl = document.getElementById('onlineAttractionCurrency');

        timeSel.innerHTML = '<option value="">Select Time Slot</option>';
        ticketSel.innerHTML = '<option value="">Select Ticket</option>';
        onlineCurrentTickets = attractionTickets(attraction);

        const slots = attractionTimeSlots(attraction);
        const timing = String(attraction.timing || '').toLowerCase();
        if (slots.length) {
            slots.forEach(function (slot) {
                const opt = document.createElement('option');
                if (slot.open && slot.close) {
                    opt.value = slot.open + ' - ' + slot.close;
                    opt.textContent = slot.open + ' - ' + slot.close;
                } else {
                    opt.value = slot.slot || slot.value || slot.time || '';
                    opt.textContent = slot.slot || slot.label || slot.time || opt.value;
                }
                timeSel.appendChild(opt);
            });
            timeSel.disabled = false;
            if (timeSel.options.length > 1) timeSel.selectedIndex = 1;
        } else if (timing === 'fixed_date_time') {
            const opt = document.createElement('option');
            opt.value = '10:00 - 18:00';
            opt.textContent = '10:00 AM - 6:00 PM';
            timeSel.appendChild(opt);
            timeSel.disabled = false;
            timeSel.selectedIndex = 1;
        } else if (timing === 'fixed_date' || timing === 'open_date') {
            const visitDate = document.getElementById('onlineAttractionVisitDate')?.value || 'Open Date';
            const opt = document.createElement('option');
            opt.value = visitDate;
            opt.textContent = timing === 'open_date' ? 'Open Date' : visitDate;
            timeSel.appendChild(opt);
            timeSel.disabled = false;
            timeSel.selectedIndex = 1;
        } else if (attraction.openTime && attraction.closeTime) {
            const opt = document.createElement('option');
            opt.value = attraction.openTime + ' - ' + attraction.closeTime;
            opt.textContent = opt.value;
            timeSel.appendChild(opt);
            timeSel.disabled = false;
            timeSel.selectedIndex = 1;
        } else {
            const opt = document.createElement('option');
            opt.value = '10:00 - 18:00';
            opt.textContent = '10:00 - 18:00';
            timeSel.appendChild(opt);
            timeSel.disabled = false;
            timeSel.selectedIndex = 1;
        }

        if (onlineCurrentTickets.length) {
            onlineCurrentTickets.forEach(function (ticket, idx) {
                const opt = document.createElement('option');
                opt.value = ticketId(ticket) || String(idx);
                opt.textContent = ticketLabel(ticket);
                opt.dataset.index = String(idx);
                opt.dataset.adultPrice = String(ticketAdultPrice(ticket));
                opt.dataset.childPrice = String(ticketChildPrice(ticket));
                opt.dataset.seniorPrice = String(ticketSeniorPrice(ticket));
                ticketSel.appendChild(opt);
            });
            ticketSel.disabled = false;
            ticketSel.selectedIndex = 1;
            applySelectedTicketPrice();
        } else {
            ticketSel.disabled = true;
            document.getElementById('onlineAttractionPriceDisplay').value = '0.00';
        }

        if (currencyEl && attraction.currency) {
            currencyEl.textContent = attraction.currency;
        }
    }

    function applySelectedTicketPrice() {
        const ticketSel = document.getElementById('onlineAttractionTicketSelect');
        const opt = ticketSel?.options[ticketSel.selectedIndex];
        if (!opt || !opt.dataset.adultPrice) {
            document.getElementById('onlineAttractionPriceDisplay').value = '0.00';
            return;
        }

        const { adults, children } = getAdultsChildren();
        const adultPrice = toNumber(opt.dataset.adultPrice);
        const childPrice = toNumber(opt.dataset.childPrice);
        const seniorPrice = toNumber(opt.dataset.seniorPrice);
        const total = (adults * adultPrice) + (children * childPrice);
        document.getElementById('onlineAttractionPriceDisplay').value = total.toFixed(2);
    }

    function validateOnlineAttractionAddBtn() {
        const btn = document.getElementById('onlineAttractionAddBtn');
        const ok = onlineAttractionSelectHasValue() &&
            document.getElementById('onlineAttractionTicketSelect')?.value;
        if (btn) btn.disabled = !ok;
    }

    function ensureAttractionSlot(day, index) {
        let guard = 0;
        while (!document.getElementById('day' + day + '_attraction_' + index) && guard < 10) {
            if (typeof window.addMoreAttractions !== 'function') break;
            const before = document.querySelectorAll('#day' + day + '_attractions_container .attraction-item').length;
            window.addMoreAttractions(day);
            const after = document.querySelectorAll('#day' + day + '_attractions_container .attraction-item').length;
            if (after <= before) break;
            guard++;
        }
        return index;
    }

    function applyOnlineAttractionToForm(day, index, payload) {
        ensureAttractionSlot(day, index);

        const citySel = document.getElementById('day' + day + '_attraction_city_' + index);
        if (citySel && payload.cityValue) {
            citySel.value = payload.cityValue;
        }

        const attrSel = document.getElementById('day' + day + '_attraction_' + index);
        if (!attrSel) return false;

        attrSel.innerHTML = '';
        const attrOpt = document.createElement('option');
        attrOpt.value = payload.attractionId;
        attrOpt.textContent = payload.attractionName;
        attrOpt.dataset.isOnline = '1';
        attrOpt.dataset.openTime = payload.openTime || '';
        attrOpt.dataset.closeTime = payload.closeTime || '';
        attrOpt.dataset.timeSlots = JSON.stringify(payload.timeSlots || []);
        attrSel.appendChild(attrOpt);
        attrOpt.selected = true;
        attrSel.disabled = false;

        const timeSel = document.getElementById('day' + day + '_attraction_' + index + '_time');
        if (timeSel) {
            timeSel.innerHTML = '<option value="">Select Time Slot</option>';
            (payload.timeSlotOptions || []).forEach(function (slot) {
                const opt = document.createElement('option');
                opt.value = slot.value;
                opt.textContent = slot.label;
                timeSel.appendChild(opt);
            });
            if (payload.timeSlot) {
                timeSel.value = payload.timeSlot;
            }
        }

        const ticketSel = document.getElementById('day' + day + '_attraction_' + index + '_ticket');
        if (ticketSel) {
            ticketSel.innerHTML = '<option value="">Select Ticket</option>';
            const ticketOpt = document.createElement('option');
            ticketOpt.value = payload.ticketId;
            ticketOpt.textContent = payload.ticketName;
            ticketOpt.dataset.adultPrice = String(payload.adultPrice || 0);
            ticketOpt.dataset.childPrice = String(payload.childPrice || 0);
            ticketOpt.dataset.seniorPrice = String(payload.seniorPrice || 0);
            ticketSel.appendChild(ticketOpt);
            ticketOpt.selected = true;
        }

        const remarksEl = document.getElementById('day' + day + '_attraction_' + index + '_remarks');
        if (remarksEl) remarksEl.value = payload.remarks || '';

        const item = document.querySelector('#day' + day + '_attractions_container .attraction-item[data-attraction-index="' + index + '"]');
        if (item) {
            item.dataset.isOnlineAttraction = '1';
        }

        if (typeof window.updateAttractionPricing === 'function') {
            window.updateAttractionPricing(day, index);
        }
        if (typeof window.updateAttractionDataField === 'function') {
            window.updateAttractionDataField();
        }
        return true;
    }

    window.openOnlineAttractionModal = function (day, index) {
        onlineAttractionTarget = {
            day: parseInt(day, 10) || 1,
            index: parseInt(index, 10) || 1
        };
        syncOnlineAttractionDefaults(onlineAttractionTarget.day);

        const modalEl = document.getElementById('onlineAttractionModal');
        if (!modalEl || !window.bootstrap?.Modal) {
            console.error('onlineAttractionModal or Bootstrap Modal is not available');
            return;
        }
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    bindAttractionSourceToggle();
    $(function () {
        bindAttractionSourceToggle();
        initOnlineAttractionSelect2(true);
    });

    document.getElementById('onlineAttractionGuestEditBtn')?.addEventListener('click', openOnlineAttractionGuestSelector);
    document.getElementById('onlineAttractionGuestApplyBtn')?.addEventListener('click', applyOnlineAttractionGuestSelection);
    document.querySelectorAll('[data-online-attraction-guest]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            adjustOnlineAttractionGuest(this.dataset.onlineAttractionGuest, this.dataset.delta);
        });
    });
    document.querySelector('.online-attraction-guest-adults-minus')?.addEventListener('click', function () { adjustOnlineAttractionAdults(-1); });
    document.querySelector('.online-attraction-guest-adults-plus')?.addEventListener('click', function () { adjustOnlineAttractionAdults(1); });

    ['onlineAttractionCity', 'onlineAttractionVisitDate'].forEach(function (id) {
        document.getElementById(id)?.addEventListener('change', resetOnlineAttractionFetchResults);
    });

    document.getElementById('onlineAttractionFetchBtn')?.addEventListener('click', function () {
        const city = document.getElementById('onlineAttractionCity')?.value;
        const visitDate = document.getElementById('onlineAttractionVisitDate')?.value;
        const paxInfo = document.getElementById('onlineAttractionPaxInfo')?.value || buildPaxInfo();
        const statusEl = document.getElementById('onlineAttractionFetchStatus');

        if (!city || !visitDate) {
            if (typeof showNotification === 'function') {
                showNotification('City and visit date are required.', 'warning');
            }
            return;
        }

        hideOnlineAttractionSelectionPanel();
        setOnlineAttractionFetchLoading(true);
        if (statusEl) statusEl.textContent = '';

        fetch(fetchUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ visitDate: visitDate, city: city, paxInfo: paxInfo })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data && data.success) {
                const attractions = extractAttractionsFromResponse(data);
                populateOnlineAttractions(attractions);
                if (attractions.length > 0) {
                    showOnlineAttractionSelectionPanel();
                    if (statusEl) statusEl.textContent = attractions.length + ' attraction(s) found.';
                    if (typeof showNotification === 'function') {
                        showNotification('Online attractions loaded successfully.', 'success');
                    }
                } else {
                    hideOnlineAttractionSelectionPanel();
                    if (statusEl) statusEl.textContent = '0 attractions found.';
                    if (typeof showNotification === 'function') {
                        showNotification('No attractions found for the selected criteria.', 'warning');
                    }
                }
            } else {
                hideOnlineAttractionSelectionPanel();
                populateOnlineAttractions([]);
                if (statusEl) statusEl.textContent = data?.message || 'No attractions found.';
                if (typeof showNotification === 'function') {
                    showNotification(data?.message || 'Failed to fetch online attractions.', 'error');
                }
            }
        })
        .catch(function (err) {
            hideOnlineAttractionSelectionPanel();
            populateOnlineAttractions([]);
            if (statusEl) statusEl.textContent = 'Request failed.';
            console.error(err);
            if (typeof showNotification === 'function') {
                showNotification('Error fetching online attractions.', 'error');
            }
        })
        .finally(function () {
            setOnlineAttractionFetchLoading(false);
        });
    });

    function onOnlineAttractionSelectChange() {
        const idx = getOnlineAttractionSelectIndex();
        const attraction = idx >= 0 ? onlineAttractionsCache[idx] : null;
        if (attraction) {
            populateOnlineAttractionDetails(attraction);
        }
        validateOnlineAttractionAddBtn();
    }

    if (typeof jQuery !== 'undefined') {
        jQuery('#onlineAttractionSelect').on('change.onlineAttraction select2:select.onlineAttraction select2:clear.onlineAttraction', onOnlineAttractionSelectChange);
    } else {
        document.getElementById('onlineAttractionSelect')?.addEventListener('change', onOnlineAttractionSelectChange);
    }

    document.getElementById('onlineAttractionTicketSelect')?.addEventListener('change', function () {
        applySelectedTicketPrice();
        validateOnlineAttractionAddBtn();
    });

    document.getElementById('onlineAttractionAddBtn')?.addEventListener('click', function () {
        const day = onlineAttractionTarget.day;
        const index = onlineAttractionTarget.index || 1;

        const attrIdx = getOnlineAttractionSelectIndex();
        const attractionRaw = attrIdx >= 0 ? onlineAttractionsCache[attrIdx] : null;
        if (!attractionRaw) return;

        const ticketSel = document.getElementById('onlineAttractionTicketSelect');
        const ticketOpt = ticketSel?.options[ticketSel.selectedIndex];
        const timeSel = document.getElementById('onlineAttractionTimeSelect');

        const timeSlotOptions = [];
        if (timeSel) {
            Array.from(timeSel.options).forEach(function (opt) {
                if (!opt.value) return;
                timeSlotOptions.push({ value: opt.value, label: opt.textContent });
            });
        }

        const slots = attractionTimeSlots(attractionRaw);
        const payload = {
            cityValue: document.getElementById('onlineAttractionCity')?.value || '',
            attractionId: attractionId(attractionRaw) || ('online-' + Date.now()),
            attractionName: attractionLabel(attractionRaw),
            openTime: attractionRaw.openTime || '',
            closeTime: attractionRaw.closeTime || '',
            timeSlots: slots,
            timeSlot: timeSel?.value || '',
            timeSlotOptions: timeSlotOptions,
            ticketId: ticketOpt?.value || ticketId(onlineCurrentTickets[0] || {}) || ('online-ticket-' + Date.now()),
            ticketName: ticketOpt?.textContent || ticketLabel(onlineCurrentTickets[0] || {}),
            adultPrice: toNumber(ticketOpt?.dataset?.adultPrice),
            childPrice: toNumber(ticketOpt?.dataset?.childPrice),
            seniorPrice: toNumber(ticketOpt?.dataset?.seniorPrice),
            remarks: document.getElementById('onlineAttractionRemarks')?.value || ''
        };

        if (!applyOnlineAttractionToForm(day, index, payload)) {
            alert('Could not apply attraction to Day ' + day + '. Please reload the page.');
            return;
        }

        if (typeof showNotification === 'function') {
            showNotification('Online attraction "' + payload.attractionName + '" added to Day ' + day + ', Slot #' + index + '.', 'success');
        }

        const modalEl = document.getElementById('onlineAttractionModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getInstance(modalEl)?.hide();
        }
        setSlotAttractionSource(day, index, 'offline');
    });

    document.getElementById('onlineAttractionModal')?.addEventListener('hidden.bs.modal', function () {
        const day = onlineAttractionTarget.day;
        const index = onlineAttractionTarget.index || 1;
        const onlineRadio = document.querySelector('input[name="attractionSourceType_day' + day + '_slot' + index + '"][value="online"]');
        if (onlineRadio && onlineRadio.checked) {
            setSlotAttractionSource(day, index, 'offline');
        }
        resetOnlineAttractionFetchResults();
    });
})();
</script>
@endpush




