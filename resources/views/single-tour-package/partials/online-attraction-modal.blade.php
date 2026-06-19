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
                    Attractions are fetched live from SG Attractions. Select visit date and pax, then click <strong>Fetch Attractions</strong>.
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
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-group-line me-1"></i>Pax Info</label>
                                <input type="text" class="form-control form-control-sm" id="onlineAttractionPaxInfo" readonly style="background: #fff;">
                                <small class="text-muted" style="font-size: 0.7rem;">Format: adults|children</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-list-ordered me-1"></i>Attraction Slot</label>
                                <select class="form-select form-select-sm" id="onlineAttractionTargetIndex">
                                    <option value="1">Attraction #1</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3 d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-primary" id="onlineAttractionFetchBtn" style="background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%); border: none;">
                                <span class="spinner-border spinner-border-sm d-none me-1" id="onlineAttractionFetchSpinner"></span>
                                <i class="ri-search-line me-1"></i> Fetch Attractions
                            </button>
                            <small class="text-muted" id="onlineAttractionFetchStatus" style="font-size: 0.8rem;"></small>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
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
                <button type="button" class="btn btn-success" id="onlineAttractionAddBtn" disabled>
                    <i class="ri-add-line me-1"></i> Add Attraction
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const fetchUrl = @json(route('fetch-online-attractions'));
    const csrfToken = @json(csrf_token());

    let onlineAttractionsCache = [];
    let onlineCurrentTickets = [];
    let onlineAttractionTarget = { day: 1, index: 1 };

    function bindAttractionSourceToggle() {
        $(document).off('change.attractionSource', '.attraction-source-radio');
        $(document).on('change.attractionSource', '.attraction-source-radio', function () {
            const day = parseInt(this.dataset.day, 10) || 1;
            const isOnline = this.value === 'online';
            const offlinePanel = document.getElementById('day' + day + '_offlineAttractionPanel');
            if (offlinePanel) {
                offlinePanel.style.display = isOnline ? 'none' : '';
            }
            if (isOnline) {
                window.openOnlineAttractionModal(day, 1);
            } else {
                const modalEl = document.getElementById('onlineAttractionModal');
                if (modalEl && window.bootstrap) {
                    bootstrap.Modal.getInstance(modalEl)?.hide();
                }
            }
        });
    }

    function getAdultsChildren() {
        const adults = parseInt(document.getElementById('adults')?.value, 10) || 1;
        const children = parseInt(document.getElementById('children')?.value, 10) || 0;
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
        const sel = document.getElementById('onlineAttractionTargetIndex');
        if (!sel) return;
        sel.innerHTML = '';
        const items = document.querySelectorAll('#day' + day + '_attractions_container .attraction-item');
        const count = items.length || 1;
        for (let i = 1; i <= count; i++) {
            const opt = document.createElement('option');
            opt.value = String(i);
            opt.textContent = 'Attraction #' + i;
            sel.appendChild(opt);
        }
        sel.value = String(onlineAttractionTarget.index || 1);
    }

    function syncOnlineAttractionDefaults(day) {
        onlineAttractionTarget.day = day;

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

        const paxEl = document.getElementById('onlineAttractionPaxInfo');
        if (paxEl) paxEl.value = buildPaxInfo();

        syncOnlineAttractionTargetOptions(day);

        const label = document.getElementById('onlineAttractionTargetLabel');
        if (label) label.textContent = 'Assigning to Day ' + day;
    }

    function populateOnlineAttractions(attractions) {
        onlineAttractionsCache = Array.isArray(attractions) ? attractions : [];
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
            opt.textContent = attractionLabel(item);
            opt.dataset.index = String(idx);
            sel.appendChild(opt);
        });

        sel.disabled = onlineAttractionsCache.length === 0;
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
        const ok = document.getElementById('onlineAttractionSelect')?.value &&
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
        const targetIndexSel = document.getElementById('onlineAttractionTargetIndex');
        if (targetIndexSel) {
            targetIndexSel.value = String(onlineAttractionTarget.index);
        }

        const modalEl = document.getElementById('onlineAttractionModal');
        if (!modalEl || !window.bootstrap?.Modal) {
            console.error('onlineAttractionModal or Bootstrap Modal is not available');
            return;
        }
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    bindAttractionSourceToggle();
    $(function () { bindAttractionSourceToggle(); });

    document.getElementById('onlineAttractionTargetIndex')?.addEventListener('change', function () {
        onlineAttractionTarget.index = parseInt(this.value, 10) || 1;
    });

    document.getElementById('onlineAttractionFetchBtn')?.addEventListener('click', function () {
        const city = document.getElementById('onlineAttractionCity')?.value;
        const visitDate = document.getElementById('onlineAttractionVisitDate')?.value;
        const paxInfo = document.getElementById('onlineAttractionPaxInfo')?.value || buildPaxInfo();
        const statusEl = document.getElementById('onlineAttractionFetchStatus');
        const spinner = document.getElementById('onlineAttractionFetchSpinner');

        if (!city || !visitDate) {
            if (typeof showNotification === 'function') {
                showNotification('Visit date is required.', 'warning');
            }
            return;
        }

        if (spinner) spinner.classList.remove('d-none');
        if (statusEl) statusEl.textContent = 'Fetching attractions...';

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
                if (statusEl) statusEl.textContent = attractions.length + ' attraction(s) found.';
                if (typeof showNotification === 'function') {
                    showNotification('Online attractions loaded successfully.', 'success');
                }
            } else {
                populateOnlineAttractions([]);
                if (statusEl) statusEl.textContent = data?.message || 'No attractions found.';
                if (typeof showNotification === 'function') {
                    showNotification(data?.message || 'Failed to fetch online attractions.', 'error');
                }
            }
        })
        .catch(function (err) {
            populateOnlineAttractions([]);
            if (statusEl) statusEl.textContent = 'Request failed.';
            console.error(err);
            if (typeof showNotification === 'function') {
                showNotification('Error fetching online attractions.', 'error');
            }
        })
        .finally(function () {
            if (spinner) spinner.classList.add('d-none');
        });
    });

    document.getElementById('onlineAttractionSelect')?.addEventListener('change', function () {
        const idx = this.selectedIndex >= 0 ? this.options[this.selectedIndex].dataset.index : null;
        const attraction = idx !== null && idx !== undefined ? onlineAttractionsCache[parseInt(idx, 10)] : null;
        if (attraction) populateOnlineAttractionDetails(attraction);
        validateOnlineAttractionAddBtn();
    });

    document.getElementById('onlineAttractionTicketSelect')?.addEventListener('change', function () {
        applySelectedTicketPrice();
        validateOnlineAttractionAddBtn();
    });

    document.getElementById('onlineAttractionAddBtn')?.addEventListener('click', function () {
        const day = onlineAttractionTarget.day;
        const index = parseInt(document.getElementById('onlineAttractionTargetIndex')?.value, 10) || onlineAttractionTarget.index || 1;

        const attrSel = document.getElementById('onlineAttractionSelect');
        const attrIdx = attrSel.selectedIndex >= 0 ? parseInt(attrSel.options[attrSel.selectedIndex].dataset.index, 10) : -1;
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
            showNotification('Online attraction "' + payload.attractionName + '" added to Day ' + day + '.', 'success');
        }

        const modalEl = document.getElementById('onlineAttractionModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getInstance(modalEl)?.hide();
        }
        document.querySelector('input[name="attractionSourceType_day' + day + '"][value="offline"]')?.click();
    });

    document.getElementById('onlineAttractionModal')?.addEventListener('hidden.bs.modal', function () {
        const day = onlineAttractionTarget.day;
        const onlineRadio = document.querySelector('input[name="attractionSourceType_day' + day + '"][value="online"]');
        if (onlineRadio && onlineRadio.checked) {
            document.querySelector('input[name="attractionSourceType_day' + day + '"][value="offline"]')?.click();
        }
    });
})();
</script>
@endpush
