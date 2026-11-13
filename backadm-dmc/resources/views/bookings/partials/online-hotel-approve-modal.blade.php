{{--
  Shared online hotel approve modal + booking API flow.
  Used by confirmed (Approve) and new-enquiries (after acceptance pay).
  Endpoints: get-hotel-data, recheck-online-hotel-booking, approve-hotel-booking.

  Pass ['inlineJs' => true] when including inside an existing <script> tag.
--}}
@once
@if(empty($inlineJs))
<style id="online-hotel-approve-swal-z">
    /* Sit above Bootstrap modals/backdrops (often 1050–1090 in this app). */
    .swal2-container.online-hotel-approve-swal {
        z-index: 20050 !important;
    }
    .hotel-approve-modal .modal-dialog {
        max-width: min(1100px, 96vw);
    }
    .hotel-approve-modal .modal-body {
        overflow: visible;
        max-height: none;
        padding: 0.75rem 1rem !important;
    }
    .hotel-approve-modal .modal-header,
    .hotel-approve-modal .modal-footer {
        padding: 0.65rem 1rem !important;
    }
    .hotel-approve-form-wide .form-label {
        font-size: 0.78rem;
        margin-bottom: 0.2rem;
    }
    .hotel-approve-form-wide .form-text {
        font-size: 0.7rem;
        margin-top: 0.15rem;
    }
    .hotel-approve-form-wide .form-control,
    .hotel-approve-form-wide .form-select {
        font-size: 0.85rem;
        padding: 0.35rem 0.6rem;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }
</style>
<script>
@endif
(function () {
    if (window.__onlineHotelApproveModalLoaded) {
        return;
    }
    window.__onlineHotelApproveModalLoaded = true;

    window._hotelApproveContext = window._hotelApproveContext || {};
    window.__pendingOnlineHotelsByTour = window.__pendingOnlineHotelsByTour || {};
    window.__onlineHotelApproveQueue = window.__onlineHotelApproveQueue || [];

    if (!document.getElementById('online-hotel-approve-swal-z')) {
        const style = document.createElement('style');
        style.id = 'online-hotel-approve-swal-z';
        style.textContent = [
            '.swal2-container.online-hotel-approve-swal{z-index:20050 !important;}',
            '.hotel-approve-modal .modal-dialog{max-width:min(1100px,96vw);}',
            '.hotel-approve-modal .modal-body{overflow:visible;max-height:none;padding:0.75rem 1rem !important;}',
            '.hotel-approve-modal .modal-header,.hotel-approve-modal .modal-footer{padding:0.65rem 1rem !important;}',
            '.hotel-approve-form-wide .form-label{font-size:0.78rem;margin-bottom:0.2rem;}',
            '.hotel-approve-form-wide .form-text{font-size:0.7rem;margin-top:0.15rem;}',
            '.hotel-approve-form-wide .form-control,.hotel-approve-form-wide .form-select{font-size:0.85rem;padding:0.35rem 0.6rem;border-radius:6px;border:1px solid #e2e8f0;}'
        ].join('');
        document.head.appendChild(style);
    }

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function closeIndividualHotelModal(modalId) {
        try {
            const modalElement = document.getElementById(modalId);
            if (!modalElement) return;
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) modal.hide();
            else {
                modalElement.classList.remove('show');
                modalElement.style.display = 'none';
                modalElement.setAttribute('aria-hidden', 'true');
            }
            // If approve modal was the last Bootstrap modal, clear leftover backdrop/body lock.
            setTimeout(function () {
                if (!document.querySelector('.modal.show')) {
                    document.querySelectorAll('.modal-backdrop').forEach(function (el) { el.remove(); });
                    document.body.classList.remove('modal-open');
                    document.body.style.removeProperty('padding-right');
                    document.body.style.removeProperty('overflow');
                }
            }, 50);
        } catch (error) {
            console.error('Error closing individual hotel modal:', error);
        }
    }
    window.closeIndividualHotelModal = closeIndividualHotelModal;

    function showOnlineHotelBookedSuccess(tourId, hotelOrderIndex, bookingIndex) {
        const modalId = `individualHotelModal_${tourId}_${hotelOrderIndex}_${bookingIndex}_approve`;
        closeIndividualHotelModal(modalId);

        const hasMore = (window.__onlineHotelApproveQueue || []).length > 0;
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Online hotel booked',
                text: hasMore
                    ? 'Supplier booking completed. Opening the next online hotel…'
                    : 'Supplier booking completed and hotel order approved.',
                timer: hasMore ? 1400 : 2200,
                showConfirmButton: false,
                customClass: { container: 'online-hotel-approve-swal' },
            }).then(() => advanceOnlineHotelApproveQueueOrReload());
        } else {
            alert(hasMore
                ? 'Online hotel booked. Opening the next online hotel…'
                : 'Online hotel booked and approved successfully.');
            setTimeout(() => advanceOnlineHotelApproveQueueOrReload(), 800);
        }
    }

    function generateApproveHotelForm(tourId, hotelOrderIndex, bookingIndex, autoCancelDate) {
        return `
        <form id="approveIndividualHotelForm_${tourId}_${hotelOrderIndex}_${bookingIndex}" class="hotel-approve-form-wide">
            <input type="hidden" name="tour_id" value="${tourId}">
            <input type="hidden" name="hotel_order_index" value="${hotelOrderIndex}">
            <input type="hidden" name="booking_index" value="${bookingIndex}">

            <div class="row g-2 align-items-stretch mb-2">
                <div class="col-lg-5">
                    <div class="card border-0 h-100" style="border-radius: 8px; background:#f8fafc;">
                        <div class="card-body py-2 px-3">
                            <div class="d-flex align-items-center gap-2">
                                <div id="approve_hotel_image_${tourId}_${hotelOrderIndex}_${bookingIndex}" class="flex-shrink-0">
                                    <div class="bg-white rounded border d-flex align-items-center justify-content-center" style="width: 52px; height: 40px;">
                                        <i class="ri-hotel-line text-muted"></i>
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <div class="fw-semibold text-truncate" id="approve_hotel_name_${tourId}_${hotelOrderIndex}_${bookingIndex}">Hotel Booking</div>
                                    <div class="text-muted small">Order ${hotelOrderIndex + 1}, Booking ${bookingIndex + 1}</div>
                                    <span id="approve_hotel_order_type_badge_${tourId}_${hotelOrderIndex}_${bookingIndex}" class="badge d-none mt-1" style="font-size:0.65rem;"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="alert alert-success border-0 mb-0 h-100 py-2 px-3 d-flex align-items-center" style="background:#ecfdf5; border-radius: 8px;">
                        <i class="ri-check-circle-line me-2 text-success"></i>
                        <div>
                            <strong class="text-success" style="font-size:0.85rem;">Confirm Approval</strong>
                            <div class="text-muted small mb-0">Confirm this hotel booking. Online hotels recheck availability then book with the supplier.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-md-6" id="referenceIdWrap_${tourId}_${hotelOrderIndex}_${bookingIndex}">
                    <label for="referenceId_${tourId}_${hotelOrderIndex}_${bookingIndex}" class="form-label fw-semibold" id="referenceIdLabel_${tourId}_${hotelOrderIndex}_${bookingIndex}">
                        <i class="ri-file-text-line me-1"></i>Reference ID <span class="text-danger reference-required-mark">*</span>
                    </label>
                    <input type="text" class="form-control" id="referenceId_${tourId}_${hotelOrderIndex}_${bookingIndex}" name="reference_id" required
                           placeholder="Booking reference / confirmation number">
                </div>
                <div class="col-md-6">
                    <label for="referenceFile_${tourId}_${hotelOrderIndex}_${bookingIndex}" class="form-label fw-semibold">
                        <i class="ri-attachment-line me-1"></i>Reference File (Optional)
                    </label>
                    <input type="file" class="form-control" id="referenceFile_${tourId}_${hotelOrderIndex}_${bookingIndex}" name="reference_file"
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    <div class="form-text">PDF, DOC, JPG, PNG</div>
                </div>
            </div>

            <div class="row g-2">
                <div class="col-md-4">
                    <label for="actualDueDate_${tourId}_${hotelOrderIndex}_${bookingIndex}" class="form-label fw-semibold">
                        <i class="ri-calendar-line me-1"></i>Free Cancellation Date <span class="text-danger">*</span>
                    </label>
                    <input type="text" readonly class="form-control" id="actualDueDate_${tourId}_${hotelOrderIndex}_${bookingIndex}" name="actual_due_date" required
                           value="${autoCancelDate || ''}">
                    <div class="form-text" id="actualDueDateHint_${tourId}_${hotelOrderIndex}_${bookingIndex}">Select free cancellation date</div>
                </div>
                <div class="col-md-4">
                    <label for="displayDueDateDays_${tourId}_${hotelOrderIndex}_${bookingIndex}" class="form-label fw-semibold">
                        <i class="ri-time-line me-1"></i>Display Due (Days Before) <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="displayDueDateDays_${tourId}_${hotelOrderIndex}_${bookingIndex}" name="display_due_date_days" required
                            onchange="calculateDisplayDueDate('${tourId}', '${hotelOrderIndex}', '${bookingIndex}')">
                        <option value="">Select days before</option>
                        <option value="1">1 day before</option>
                        <option value="2">2 days before</option>
                        <option value="3">3 days before</option>
                        <option value="4">4 days before</option>
                        <option value="5">5 days before</option>
                        <option value="7">1 week before</option>
                        <option value="14">2 weeks before</option>
                    </select>
                    <div class="form-text">Days before free cancel date</div>
                </div>
                <div class="col-md-4">
                    <label for="displayDueDate_${tourId}_${hotelOrderIndex}_${bookingIndex}" class="form-label fw-semibold">
                        <i class="ri-calendar-check-line me-1"></i>Display Due Date
                    </label>
                    <input type="text" class="form-control" id="displayDueDate_${tourId}_${hotelOrderIndex}_${bookingIndex}" name="display_due_date" readonly
                           placeholder="dd-mm-yyyy" style="background-color: #f8f9fa;">
                    <div class="form-text">Calculated automatically</div>
                </div>
            </div>
        </form>`;
    }
    window.generateApproveHotelForm = generateApproveHotelForm;

    /** Free cancel = day before earliest cancellation_policies.fromDate */
    function freeCancelDateFromOnlinePolicies(policies) {
        const earliest = (Array.isArray(policies) ? policies : [])
            .map(function (p) { return String(p && p.fromDate ? p.fromDate : '').slice(0, 10); })
            .filter(function (d) { return /^\d{4}-\d{2}-\d{2}$/.test(d); })
            .sort()[0] || '';
        if (!earliest) return '';

        const d = new Date(earliest + 'T00:00:00');
        if (isNaN(d.getTime())) return '';
        d.setDate(d.getDate() - 1);
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        return yyyy + '-' + mm + '-' + dd;
    }
    window.freeCancelDateFromOnlinePolicies = freeCancelDateFromOnlinePolicies;

    function applyOnlineHotelApproveFormRules(tourId, hotelOrderIndex, bookingIndex, isOnline, freeCancelDate, tourAutoCancelDate) {
        const refWrap = document.getElementById(`referenceIdWrap_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        const refInput = document.getElementById(`referenceId_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        const refLabel = document.getElementById(`referenceIdLabel_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        const dueInput = document.getElementById(`actualDueDate_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        const dueHint = document.getElementById(`actualDueDateHint_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        const badge = document.getElementById(`approve_hotel_order_type_badge_${tourId}_${hotelOrderIndex}_${bookingIndex}`);

        if (badge) {
            badge.classList.remove('d-none', 'bg-info', 'bg-secondary');
            if (isOnline) {
                badge.classList.add('bg-info');
                badge.textContent = 'Online order';
            } else {
                badge.classList.add('bg-secondary');
                badge.textContent = 'Offline order';
            }
        }

        if (refWrap) {
            refWrap.classList.toggle('d-none', !!isOnline);
        }
        if (refInput) {
            if (isOnline) {
                refInput.removeAttribute('required');
                refInput.value = '';
            } else {
                refInput.setAttribute('required', 'required');
                refInput.placeholder = 'Enter booking reference or confirmation number';
            }
        }
        if (refLabel) {
            const star = refLabel.querySelector('.reference-required-mark');
            if (star) star.classList.toggle('d-none', !!isOnline);
        }

        if (dueInput) {
            if (isOnline && freeCancelDate) {
                dueInput.value = freeCancelDate;
                if (dueHint) {
                    dueHint.textContent = 'From supplier cancellation policy (free cancel until this date).';
                }
            } else {
                dueInput.value = tourAutoCancelDate || freeCancelDate || '';
                if (dueHint) {
                    dueHint.textContent = isOnline
                        ? 'Free cancellation date from supplier policy (fallback: tour auto-cancel date).'
                        : 'Select the Free Cancellation Date for this booking';
                }
            }
        }
    }
    window.applyOnlineHotelApproveFormRules = applyOnlineHotelApproveFormRules;

    function loadHotelDataForApprove(tourId, hotelOrderIndex, bookingIndex) {
        try {
            fetch(@json(url('/booking/get-hotel-data')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken()
                },
                body: JSON.stringify({
                    tour_id: tourId,
                    hotel_order_index: hotelOrderIndex,
                    booking_index: bookingIndex
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!(data.success && data.data && data.data.hotel_booking)) {
                    const hotelNameElement = document.getElementById(`approve_hotel_name_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
                    if (hotelNameElement) hotelNameElement.textContent = 'Error loading hotel data';
                    return;
                }

                const hotelData = data.data.hotel_booking;
                const tourData = data.data.tour || {};
                const ctxKey = `${tourId}_${hotelOrderIndex}_${bookingIndex}`;
                window._hotelApproveContext[ctxKey] = {
                    isOnline: hotelData.is_online_hotel === true || hotelData.order_type === 'online',
                    orderType: hotelData.order_type || null,
                    onlineHotelSource: hotelData.online_hotel_source || null,
                    storedPrice: parseFloat(hotelData.total_price || 0),
                    currency: hotelData.currency || 'SGD',
                    cancellationPolicyType: hotelData.cancellation_policy_type || null,
                    cancellationPolicies: Array.isArray(hotelData.cancellation_policies)
                        ? hotelData.cancellation_policies
                        : [],
                    recheckToken: null,
                    recheckData: null,
                };

                const hotelImageElement = document.getElementById(`approve_hotel_image_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
                if (hotelImageElement && hotelData.image) {
                    hotelImageElement.innerHTML = `
                        <img src="${hotelData.image}"
                             alt="${hotelData.hotel_name || 'Hotel'}"
                             class="img-fluid rounded shadow-sm"
                             style="width: 80px; height: 60px; object-fit: cover;">
                    `;
                } else if (hotelImageElement) {
                    hotelImageElement.innerHTML = `
                        <div class="bg-light rounded p-3 d-flex align-items-center justify-content: center" style="width: 80px; height: 60px;">
                            <i class="ri-hotel-line text-muted fs-2"></i>
                        </div>
                    `;
                }

                const hotelNameElement = document.getElementById(`approve_hotel_name_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
                if (hotelNameElement) {
                    hotelNameElement.textContent = hotelData.hotel_name || 'Hotel Booking';
                }

                const isOnline = hotelData.is_online_hotel === true || hotelData.order_type === 'online';
                const tourAutoCancelDate = tourData.auto_cancel_date || '';
                const freeCancelFromPolicy = freeCancelDateFromOnlinePolicies(hotelData.cancellation_policies);

                applyOnlineHotelApproveFormRules(
                    tourId,
                    hotelOrderIndex,
                    bookingIndex,
                    isOnline,
                    freeCancelFromPolicy,
                    tourAutoCancelDate
                );
            })
            .catch(error => {
                console.error('Error loading hotel data:', error);
            });
        } catch (error) {
            console.error('Error in loadHotelDataForApprove:', error);
        }
    }
    window.loadHotelDataForApprove = loadHotelDataForApprove;

    window.calculateDisplayDueDate = function (tourId, hotelOrderIndex, bookingIndex) {
        try {
            const actualDueDateElement = document.getElementById(`actualDueDate_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
            const displayDueDateDaysElement = document.getElementById(`displayDueDateDays_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
            const displayDueDateField = document.getElementById(`displayDueDate_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
            if (!actualDueDateElement || !displayDueDateDaysElement || !displayDueDateField) return;

            const actualDueDate = actualDueDateElement.value;
            const daysBefore = displayDueDateDaysElement.value;
            if (!actualDueDate || !daysBefore) {
                displayDueDateField.value = '';
                return;
            }

            let actualDate;
            if (actualDueDate.includes('-')) {
                const parts = actualDueDate.split('-');
                if (parts[0].length === 4) {
                    actualDate = new Date(actualDueDate);
                } else {
                    actualDate = new Date(`${parts[2]}-${parts[1]}-${parts[0]}`);
                }
            } else {
                actualDate = new Date(actualDueDate);
            }

            if (isNaN(actualDate.getTime())) {
                displayDueDateField.value = '';
                return;
            }

            const displayDate = new Date(actualDate);
            displayDate.setDate(actualDate.getDate() - parseInt(daysBefore, 10));
            const day = String(displayDate.getDate()).padStart(2, '0');
            const month = String(displayDate.getMonth() + 1).padStart(2, '0');
            const year = displayDate.getFullYear();
            displayDueDateField.value = `${day}-${month}-${year}`;
        } catch (error) {
            console.error('Error calculating hotel display due date:', error);
        }
    };

    function onlineCancelPolicyFieldFilled(value) {
        if (value === null || value === undefined) return false;
        if (typeof value === 'string') return value.trim() !== '';
        if (typeof value === 'number') return Number.isFinite(value);
        return !!value;
    }

    function formatOnlineCancelDateConfirm(ymd) {
        const raw = String(ymd || '').slice(0, 10);
        if (!/^\d{4}-\d{2}-\d{2}$/.test(raw)) return String(ymd || '');
        const d = new Date(raw + 'T00:00:00');
        if (isNaN(d.getTime())) return raw;
        return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
    }

    function dayBeforeYmdConfirm(ymd) {
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

    function formatOnlineCancelWindowConfirm(fromDate, toDate) {
        const from = String(fromDate || '').slice(0, 10);
        const to = String(toDate || '').slice(0, 10);
        if (!from) return '';
        if (!to || from === to) return formatOnlineCancelDateConfirm(from);
        const toLabel = formatOnlineCancelDateConfirm(to);
        if (from.slice(0, 7) === to.slice(0, 7)) {
            return String(parseInt(from.slice(8, 10), 10)) + '–' + toLabel;
        }
        return formatOnlineCancelDateConfirm(from) + '–' + toLabel;
    }

    function formatOnlineCancelPenaltyConfirm(policy, currency) {
        if (onlineCancelPolicyFieldFilled(policy.nights)) {
            const nights = parseInt(policy.nights, 10);
            if (nights === 1) return '1 night charge';
            if (Number.isFinite(nights) && nights > 0) return nights + ' nights charge';
            return String(policy.nights).trim() + ' night charge';
        }
        if (onlineCancelPolicyFieldFilled(policy.percent)) {
            const pct = parseFloat(policy.percent);
            const label = Number.isFinite(pct)
                ? (pct % 1 === 0 ? String(pct) : pct.toFixed(2))
                : String(policy.percent).trim();
            return label + '% of stay';
        }
        const amount = onlineCancelPolicyFieldFilled(policy.grossAmount)
            ? policy.grossAmount
            : (onlineCancelPolicyFieldFilled(policy.amount) ? policy.amount : null);
        if (amount !== null) {
            const num = parseFloat(amount);
            return String(currency || 'SGD') + ' ' + (Number.isFinite(num) ? num.toFixed(2) : String(amount).trim());
        }
        return 'penalty applies';
    }

    function policyHasOnlineCancelCharge(policy) {
        return onlineCancelPolicyFieldFilled(policy?.nights)
            || onlineCancelPolicyFieldFilled(policy?.percent)
            || onlineCancelPolicyFieldFilled(policy?.amount)
            || onlineCancelPolicyFieldFilled(policy?.grossAmount);
    }

    function summarizeOnlineCancellationForConfirm(policyType, policies, currency) {
        const typeRaw = String(policyType || '').trim();
        const isNrf = /nrf|non[-\s]?refund/i.test(typeRaw);
        const typeLabel = isNrf
            ? 'Non-refundable'
            : (/flexi/i.test(typeRaw) ? 'Flexible' : (typeRaw || ''));
        const list = Array.isArray(policies) ? policies.filter(function (p) { return p && typeof p === 'object'; }) : [];

        if (!list.length) {
            if (isNrf) return { line1: 'Non-refundable · Full charge if cancelled', line2: '' };
            if (typeLabel) return { line1: typeLabel, line2: '' };
            return null;
        }

        const cancelRules = list.filter(function (p) { return !p.noShow && policyHasOnlineCancelCharge(p); });
        const noShowRules = list.filter(function (p) { return !!p.noShow && policyHasOnlineCancelCharge(p); });
        const earliestFrom = list
            .map(function (p) { return String(p.fromDate || '').slice(0, 10); })
            .filter(function (d) { return /^\d{4}-\d{2}-\d{2}$/.test(d); })
            .sort()[0] || '';
        const freeUntil = earliestFrom ? dayBeforeYmdConfirm(earliestFrom) : '';

        let line1 = '';
        if (isNrf) {
            line1 = 'Non-refundable · Full charge if cancelled';
        } else if (freeUntil) {
            line1 = (typeLabel || 'Flexible') + ' · Free cancel until ' + formatOnlineCancelDateConfirm(freeUntil);
        } else {
            line1 = typeLabel || 'Cancellation policy';
        }

        const bits = [];
        cancelRules.slice(0, 2).forEach(function (policy) {
            const window = formatOnlineCancelWindowConfirm(policy.fromDate, policy.toDate);
            bits.push((window ? window + ': ' : '') + formatOnlineCancelPenaltyConfirm(policy, currency));
        });

        if (noShowRules[0]) {
            const policy = noShowRules[0];
            const fromLabel = formatOnlineCancelDateConfirm(policy.fromDate);
            const pct = parseFloat(policy.percent);
            if (Number.isFinite(pct) && pct >= 100) {
                bits.push((fromLabel ? 'from ' + fromLabel + ': ' : '') + 'No cancellation allowed');
            } else {
                bits.push(
                    'No-show' + (fromLabel ? ' from ' + fromLabel : '') + ': ' + formatOnlineCancelPenaltyConfirm(policy, currency)
                );
            }
        }

        return { line1: line1, line2: bits.join(' · ') };
    }

    function escapeHtmlConfirm(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function buildOnlineCancellationHtmlForConfirm(recheckData, approveCtx) {
        const policies = (Array.isArray(recheckData?.cancellation_policies) && recheckData.cancellation_policies.length)
            ? recheckData.cancellation_policies
            : (Array.isArray(approveCtx?.cancellationPolicies) ? approveCtx.cancellationPolicies : []);
        const policyType = recheckData?.cancellation_policy_type
            || approveCtx?.cancellationPolicyType
            || '';
        const currency = recheckData?.currency || approveCtx?.currency || 'SGD';
        const summary = summarizeOnlineCancellationForConfirm(policyType, policies, currency);
        if (!summary || !summary.line1) return '';

        return (
            '<div class="mt-2 mb-1 px-2 py-1 rounded text-start" style="background:#fff8e8;border:1px solid #f0e0b2;">' +
                '<div style="font-size:0.72rem;line-height:1.35;color:#6b5a1a;">' +
                    '<i class="ri-calendar-close-line me-1"></i>' + escapeHtmlConfirm(summary.line1) +
                '</div>' +
                (summary.line2
                    ? '<div style="font-size:0.7rem;line-height:1.3;color:#7a6a2e;margin-top:0.1rem;">' +
                        escapeHtmlConfirm(summary.line2) +
                      '</div>'
                    : '') +
            '</div>'
        );
    }
    window.buildOnlineCancellationHtmlForConfirm = buildOnlineCancellationHtmlForConfirm;

    function createAndShowOnlineHotelApproveModal(tourId, hotelOrderIndex, bookingIndex, autoCancelDate) {
        const modalId = `individualHotelModal_${tourId}_${hotelOrderIndex}_${bookingIndex}_approve`;
        const existingModal = document.getElementById(modalId);
        if (existingModal) existingModal.remove();

        const modalHTML = `
            <div class="modal fade hotel-approve-modal" id="${modalId}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                        <div class="modal-header border-0" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                            <div class="d-flex align-items-center">
                                <div class="bg-white rounded-circle shadow-sm me-2" style="padding: 6px; display: flex; align-items: center; justify-content: center;">
                                    <i class="ri-hotel-line text-primary"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title fw-bold text-white mb-0" style="font-size:0.95rem;">Approve Individual Hotel Booking</h5>
                                    <p class="text-white-50 mb-0" style="font-size: 0.75rem;">Hotel Order ${hotelOrderIndex + 1}, Booking ${bookingIndex + 1}</p>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" onclick="closeIndividualHotelModal('${modalId}')" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            ${generateApproveHotelForm(tourId, hotelOrderIndex, bookingIndex, autoCancelDate)}
                        </div>
                        <div class="modal-footer border-0" style="background: #f8f9fa;">
                            <div class="d-flex gap-2 w-100 justify-content-end">
                                <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeIndividualHotelModal('${modalId}')" style="border-radius: 6px;">
                                    <i class="ri-close-line me-1"></i>Cancel
                                </button>
                                <button type="button" class="btn btn-success btn-sm px-3 py-1" onclick="confirmIndividualHotelApproval(${tourId}, ${hotelOrderIndex}, ${bookingIndex})" style="border-radius: 6px;">
                                    <i class="ri-check-line me-1"></i>Confirm Approval
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        const modalElement = document.getElementById(modalId);
        const modal = new bootstrap.Modal(modalElement);
        modal.show();

        modalElement.addEventListener('hidden.bs.modal', function () {
            modalElement.remove();
        }, { once: true });

        setTimeout(() => loadHotelDataForApprove(tourId, hotelOrderIndex, bookingIndex), 100);
    }
    window.createAndShowOnlineHotelApproveModal = createAndShowOnlineHotelApproveModal;
    window.openOnlineHotelApproveModal = createAndShowOnlineHotelApproveModal;

    function confirmIndividualHotelApproval(tourId, hotelOrderIndex, bookingIndex) {
        try {
            const form = document.getElementById(`approveIndividualHotelForm_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
            if (!form) {
                console.error('Individual hotel approve form not found');
                return;
            }
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const ctxKey = `${tourId}_${hotelOrderIndex}_${bookingIndex}`;
            const approveCtx = (window._hotelApproveContext && window._hotelApproveContext[ctxKey]) || {};
            const isOnlineHotel = approveCtx.isOnline === true || approveCtx.orderType === 'online';

            if (isOnlineHotel) {
                submitOnlineHotelApproval(tourId, hotelOrderIndex, bookingIndex, window.event);
                return;
            }

            if (typeof window.submitOfflineHotelApproval === 'function') {
                window.submitOfflineHotelApproval(tourId, hotelOrderIndex, bookingIndex, window.event);
                return;
            }

            alert('Offline hotel approval is not available on this page.');
        } catch (error) {
            console.error('Error approving individual hotel booking:', error);
            alert('Error approving booking. Please try again.');
        }
    }
    window.confirmIndividualHotelApproval = confirmIndividualHotelApproval;

    function submitOnlineHotelApproval(tourId, hotelOrderIndex, bookingIndex, evt) {
        const actualDueDate = document.getElementById(`actualDueDate_${tourId}_${hotelOrderIndex}_${bookingIndex}`).value;
        const displayDueDateDays = document.getElementById(`displayDueDateDays_${tourId}_${hotelOrderIndex}_${bookingIndex}`).value;
        const displayDueDate = document.getElementById(`displayDueDate_${tourId}_${hotelOrderIndex}_${bookingIndex}`).value;
        const referenceFile = document.getElementById(`referenceFile_${tourId}_${hotelOrderIndex}_${bookingIndex}`).files[0];

        if (!actualDueDate || !displayDueDateDays || !displayDueDate) {
            alert('Please fill in Free Cancellation Date and Display Due Date.');
            return;
        }

        const approveButton = evt && evt.target ? evt.target : null;
        const originalText = approveButton ? approveButton.innerHTML : '';
        const ctxKey = `${tourId}_${hotelOrderIndex}_${bookingIndex}`;

        if (approveButton) {
            approveButton.innerHTML = '<i class="ri-loader-4-line me-2"></i>Checking availability...';
            approveButton.disabled = true;
        }

        fetch(@json(url('/booking/recheck-online-hotel-booking')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken()
            },
            body: JSON.stringify({
                tour_id: tourId,
                hotel_order_index: hotelOrderIndex,
                booking_index: bookingIndex
            })
        })
        .then(response => response.json())
        .then(recheck => {
            if (approveButton) {
                approveButton.innerHTML = originalText;
                approveButton.disabled = false;
            }

            if (!recheck.success || !recheck.data) {
                throw new Error(recheck.message || 'Availability recheck failed');
            }

            window._hotelApproveContext[ctxKey] = Object.assign(window._hotelApproveContext[ctxKey] || {}, {
                recheckToken: recheck.recheck_token,
                recheckData: recheck.data,
                cancellationPolicyType: recheck.data.cancellation_policy_type
                    || (window._hotelApproveContext[ctxKey] || {}).cancellationPolicyType
                    || null,
                cancellationPolicies: (Array.isArray(recheck.data.cancellation_policies) && recheck.data.cancellation_policies.length)
                    ? recheck.data.cancellation_policies
                    : ((window._hotelApproveContext[ctxKey] || {}).cancellationPolicies || []),
            });

            const approveCtx = window._hotelApproveContext[ctxKey] || {};
            const currency = recheck.data.currency || 'SGD';
            const money = value => currency + ' ' + parseFloat(value || 0).toFixed(2);

            const storedPrice = parseFloat(recheck.data.stored_price || 0);
            const supplierPrice = parseFloat(recheck.data.supplier_gross_price || recheck.data.supplier_net_price || 0);
            const storedSupplierPrice = recheck.data.stored_supplier_price;
            const customerPrice = recheck.data.customer_price;
            const markupApplied = !!recheck.data.markup_applied;
            const priceChanged = !!recheck.data.price_changed;

            let priceRows =
                '<div class="d-flex justify-content-between border-top pt-2 mt-2">' +
                    '<span>Quoted to customer</span><strong>' + money(storedPrice) + '</strong>' +
                '</div>';

            if (markupApplied) {
                priceRows +=
                    '<div class="d-flex justify-content-between">' +
                        '<span>Customer price now</span>' +
                        '<strong class="' + (priceChanged ? 'text-warning' : 'text-primary') + '">' + money(customerPrice) + '</strong>' +
                    '</div>';
            } else {
                priceRows +=
                    '<div class="d-flex justify-content-between">' +
                        '<span>Supplier price now</span>' +
                        '<strong class="' + (priceChanged ? 'text-warning' : 'text-primary') + '">' + money(supplierPrice) + '</strong>' +
                    '</div>';

                if (storedSupplierPrice) {
                    priceRows +=
                        '<div class="d-flex justify-content-between text-muted" style="font-size: 0.8rem;">' +
                            '<span>Supplier price at enquiry</span><span>' + money(storedSupplierPrice) + '</span>' +
                        '</div>';
                }
            }

            let priceNote = priceChanged
                ? '<div class="alert alert-warning py-2 px-3 small mb-2">Price has changed since enquiry. The booking will be confirmed at the supplier\'s current rate.</div>'
                : '';

            if (!markupApplied) {
                priceNote +=
                    '<div class="alert alert-secondary py-2 px-3 small mb-2">' +
                    'No markup rules were found, so the figure above is the supplier price before markup.' +
                    '</div>';
            }

            const cancellationHtml = buildOnlineCancellationHtmlForConfirm(recheck.data, approveCtx);

            if (typeof Swal === 'undefined') {
                const comparisonLine = markupApplied
                    ? `Customer price now: ${money(customerPrice)}`
                    : `Supplier price now: ${money(supplierPrice)}`;
                if (!confirm(`Confirm online booking with supplier?\nQuoted to customer: ${money(storedPrice)}\n${comparisonLine}`)) {
                    return;
                }
                finalizeOnlineHotelApproval(tourId, hotelOrderIndex, bookingIndex, recheck.recheck_token, referenceFile, approveButton, originalText);
                return;
            }

            Swal.fire({
                title: 'Confirm online hotel booking?',
                icon: priceChanged ? 'warning' : 'question',
                width: '28rem',
                html:
                    priceNote +
                    '<div class="text-start small">' +
                        '<div class="mb-2"><strong>' + (recheck.data.hotel_name || 'Hotel') + '</strong></div>' +
                        '<div class="mb-1">Room: ' + (recheck.data.room_name || '—') + '</div>' +
                        '<div class="mb-1">Meal plan: ' + (recheck.data.meal_plan_name || '—') + '</div>' +
                        '<div class="mb-1">Stay: ' + (recheck.data.check_in || '') + ' → ' + (recheck.data.check_out || '') + '</div>' +
                        cancellationHtml +
                        priceRows +
                    '</div>',
                showCancelButton: true,
                confirmButtonText: 'Confirm booking',
                confirmButtonColor: '#198754',
                cancelButtonText: 'Review again',
                showLoaderOnConfirm: true,
                heightAuto: false,
                customClass: { container: 'online-hotel-approve-swal' },
                preConfirm: () => {
                    const latestToken = (window._hotelApproveContext
                        && window._hotelApproveContext[ctxKey]
                        && window._hotelApproveContext[ctxKey].recheckToken)
                        || recheck.recheck_token;
                    // API only — do not open nested Swal / close Bootstrap modal here or the loader sticks.
                    return finalizeOnlineHotelApproval(
                        tourId,
                        hotelOrderIndex,
                        bookingIndex,
                        latestToken,
                        referenceFile,
                        null,
                        '',
                        true
                    );
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then(function (result) {
                if (result.isConfirmed) {
                    showOnlineHotelBookedSuccess(tourId, hotelOrderIndex, bookingIndex);
                }
            });
        })
        .catch(error => {
            console.error('Online hotel recheck failed:', error);
            alert('Online availability check failed: ' + error.message);
            if (approveButton) {
                approveButton.innerHTML = originalText;
                approveButton.disabled = false;
            }
        });
    }
    window.submitOnlineHotelApproval = submitOnlineHotelApproval;

    function advanceOnlineHotelApproveQueueOrReload() {
        const queue = window.__onlineHotelApproveQueue || [];
        if (queue.length > 0) {
            const next = queue.shift();
            setTimeout(function () {
                createAndShowOnlineHotelApproveModal(
                    next.tourId,
                    next.hotelOrderIndex,
                    next.bookingIndex,
                    next.autoCancelDate || null
                );
            }, 400);
            return;
        }
        location.reload();
    }

    function finalizeOnlineHotelApproval(tourId, hotelOrderIndex, bookingIndex, recheckToken, referenceFile, approveButton, originalText, returnPromise) {
        const referenceIdEl = document.getElementById(`referenceId_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        const actualDueDateEl = document.getElementById(`actualDueDate_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        const displayDueDateDaysEl = document.getElementById(`displayDueDateDays_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        const displayDueDateEl = document.getElementById(`displayDueDate_${tourId}_${hotelOrderIndex}_${bookingIndex}`);
        const ctxKey = `${tourId}_${hotelOrderIndex}_${bookingIndex}`;
        const ctxToken = (window._hotelApproveContext
            && window._hotelApproveContext[ctxKey]
            && window._hotelApproveContext[ctxKey].recheckToken) || '';
        const tokenToSend = (recheckToken || ctxToken || '').trim();

        const formData = new FormData();
        formData.append('tour_id', tourId);
        formData.append('hotel_order_index', hotelOrderIndex);
        formData.append('booking_index', bookingIndex);
        formData.append('reference_id', referenceIdEl ? referenceIdEl.value : '');
        formData.append('actual_due_date', actualDueDateEl ? actualDueDateEl.value : '');
        formData.append('display_due_date_days', displayDueDateDaysEl ? displayDueDateDaysEl.value : '');
        formData.append('display_due_date', displayDueDateEl ? displayDueDateEl.value : '');
        formData.append('confirm_online_booking', '1');
        formData.append('recheck_token', tokenToSend);
        if (referenceFile) {
            formData.append('reference_file', referenceFile);
        }

        const request = fetch(@json(url('/booking/approve-hotel-booking')), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData
        })
        .then(async function (response) {
            let data = {};
            try {
                data = await response.json();
            } catch (e) {
                throw new Error(response.ok
                    ? 'Invalid response from booking server'
                    : ('Booking failed (' + response.status + ')'));
            }
            if (!response.ok || !data.success) {
                const err = new Error(data.message || 'Online booking approval failed');
                err.requiresRecheck = !!data.requires_recheck;
                throw err;
            }
            return data;
        })
        .then(data => {
            if (approveButton) {
                approveButton.innerHTML = originalText;
                approveButton.disabled = false;
            }

            // When called from Swal preConfirm, only resolve — UI closes in Swal.then().
            if (!returnPromise) {
                showOnlineHotelBookedSuccess(tourId, hotelOrderIndex, bookingIndex);
            }

            return data;
        })
        .catch(error => {
            if (approveButton) {
                approveButton.innerHTML = originalText;
                approveButton.disabled = false;
            }

            if (error && error.requiresRecheck) {
                if (typeof Swal !== 'undefined') {
                    Swal.close();
                }
                setTimeout(function () {
                    submitOnlineHotelApproval(tourId, hotelOrderIndex, bookingIndex, null);
                }, 250);
                // Keep Swal open / stop loader when used as preConfirm.
                if (returnPromise) {
                    return false;
                }
                return;
            }

            if (returnPromise && typeof Swal !== 'undefined') {
                Swal.showValidationMessage(error.message || 'Booking failed');
                return false;
            }

            alert('Online booking failed: ' + (error && error.message ? error.message : error));
            if (returnPromise) {
                return false;
            }
            throw error;
        });

        return returnPromise ? request : undefined;
    }
    window.finalizeOnlineHotelApproval = finalizeOnlineHotelApproval;

    /**
     * After acceptance pay: open approve modal for each unpaid online hotel on the tour.
     * @returns {boolean} true if at least one modal was queued/opened (caller should skip reload)
     */
    window.openPendingOnlineHotelApprovalsAfterPay = function (tourId) {
        const tid = parseInt(tourId, 10);
        const list = (window.__pendingOnlineHotelsByTour && window.__pendingOnlineHotelsByTour[tid]) || [];
        const pending = (Array.isArray(list) ? list : []).filter(function (item) {
            return item && (item.isOnline === true || item.orderType === 'online');
        });

        if (!pending.length) {
            return false;
        }

        window.__onlineHotelApproveQueue = pending.slice(1).map(function (item) {
            return {
                tourId: tid,
                hotelOrderIndex: item.hotelOrderIndex,
                bookingIndex: item.bookingIndex,
                autoCancelDate: item.autoCancelDate || null,
            };
        });

        const first = pending[0];
        createAndShowOnlineHotelApproveModal(
            tid,
            first.hotelOrderIndex,
            first.bookingIndex,
            first.autoCancelDate || null
        );
        return true;
    };
})();
@if(empty($inlineJs))
</script>
@endif
@endonce
