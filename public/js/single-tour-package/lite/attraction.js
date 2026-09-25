/* === STP LITE: attraction.js ===
 * Attraction tickets — cascade → Transfer/Guide extras → Get Price → Add → list
 * Payload: attraction_data
 * === */
(function (window, document) {
    'use strict';

    var S = function () { return window.StpLiteTransportShared || {}; };
    var PREFIX = 'attraction';

    function cfg() { return window.STP_LITE_CONFIG || {}; }

    function guestCounts(root) {
        var T = S();
        var tour = (T.tourGuests && T.tourGuests()) || {};
        return {
            adults: parseInt((root.querySelector('.attraction-adults') || {}).value, 10) || 0,
            children: parseInt((root.querySelector('.attraction-children') || {}).value, 10) || 0,
            seniors: parseInt((root.querySelector('.attraction-seniors') || {}).value, 10) || 0,
            infants: Math.max(0, parseInt(tour.infants, 10) || 0)
        };
    }

    function shellHtml(stay) {
        var T = S();
        var cur = stay.currency || 'SGD';
        var g = T.tourGuests();
        return (
            '<div class="stp-lite-svc stp-lite-attraction" data-currency="' + T.esc(cur) + '">' +
            '  <div class="row g-2 mb-2">' +
            '    <div class="col-md-3"><label class="stp-lite-label">City</label>' +
            '      <div class="stp-lite-city-static">' + T.esc(T.cityLabel(stay)) + '</div></div>' +
            '    <div class="col-md-3"><label class="stp-lite-label">Attraction</label>' +
            '      <select class="form-select form-select-sm attraction-select" disabled data-no-select2="true"><option value="">Loading…</option></select></div>' +
            '    <div class="col-md-3"><label class="stp-lite-label">Ticket</label>' +
            '      <select class="form-select form-select-sm attraction-ticket" disabled data-no-select2="true"><option value="">Select attraction</option></select></div>' +
            '    <div class="col-md-3"><label class="stp-lite-label">Visit time</label>' +
            T.ampmTimeHtml('attraction', '') +
            '</div>' +
            '  </div>' +
            '  <div class="row g-2 mb-2">' +
            '    <div class="col-md-2"><label class="stp-lite-label">Date</label>' +
            '      <input type="date" class="form-control form-control-sm attraction-date" value="' + T.esc(stay.start || '') + '"></div>' +
            '    <div class="col-md-2"><label class="stp-lite-label">Adults</label>' +
            '      <input type="number" min="0" class="form-control form-control-sm stp-lite-int attraction-adults" data-guest-cap="adults" value="' + (g.adults || 1) + '"></div>' +
            '    <div class="col-md-2" data-guest-child-ui><label class="stp-lite-label">Children</label>' +
            '      <input type="number" min="0" class="form-control form-control-sm stp-lite-int attraction-children" data-guest-cap="children" value="' + (g.children || 0) + '"></div>' +
            '    <div class="col-md-2"><label class="stp-lite-label">Seniors</label>' +
            '      <input type="number" min="0" class="form-control form-control-sm stp-lite-int attraction-seniors" data-guest-cap="seniors" value="0"></div>' +
            '    <div class="col-md-2">' + T.transferRequiredSelectHtml(PREFIX) + '</div>' +
            '    <div class="col-md-2">' + T.guideRequiredSelectHtml(PREFIX) + '</div>' +
            '  </div>' +
            T.transferExtrasHtml(PREFIX, stay) +
            T.guideExtrasHtml(PREFIX) +
            '  <div class="row g-2 mb-2">' +
            '    <div class="col-md-12 d-flex align-items-end gap-2 flex-wrap">' +
            '      <button type="button" class="btn btn-sm stp-lite-get-price-btn attraction-get-price-btn">' +
            '        <i class="ri-price-tag-3-line me-1"></i>Get Price</button>' +
            '      <button type="button" class="btn btn-sm btn-primary attraction-add-btn" disabled>' +
            '        <i class="ri-add-line me-1"></i>Add</button></div>' +
            '  </div>' +
            T.pricePanelHtml(cur, 'attraction') +
            '  <div class="stp-lite-svc-added mt-2" data-attraction-added-list></div>' +
            '  <input type="hidden" class="attraction_data_chunk" value="[]">' +
            '</div>'
        );
    }

    function readChunk(root) {
        try {
            var v = JSON.parse((root.querySelector('.attraction_data_chunk') || {}).value || '[]');
            return Array.isArray(v) ? v : [];
        } catch (e) { return []; }
    }

    function writeChunk(root, rows) {
        var T = S();
        // Persist ticket-only totals; header/list compose ticket+xfer+guide via serviceRowDisplayTotal
        var el = root.querySelector('.attraction_data_chunk');
        if (el) el.value = JSON.stringify(rows || []);
        T.syncHiddenJson('attraction_data', '.attraction_data_chunk');
        if (typeof T.updateServiceHeaderTotal === 'function') {
            T.updateServiceHeaderTotal(root, 'attraction');
        } else {
            try {
                root.dispatchEvent(new CustomEvent('stp:service-chunk-changed', {
                    bubbles: true,
                    detail: {
                        service: 'attraction',
                        total: (rows || []).reduce(function (s, r) {
                            return s + (typeof T.serviceRowDisplayTotal === 'function'
                                ? T.serviceRowDisplayTotal(r)
                                : (Number(r.totalPrice) || 0));
                        }, 0),
                        currency: root.getAttribute('data-currency') || 'SGD',
                        root: root
                    }
                }));
            } catch (e) { /* ignore */ }
        }
    }

    function invalidate(root) {
        root.__lastPrice = null;
        var panel = root.querySelector('[data-attraction-price-panel]');
        if (panel) panel.classList.add('d-none');
        var add = root.querySelector('.attraction-add-btn');
        if (add) add.disabled = true;
    }

    function bundleIconUrl() {
        return (cfg().bundleAttractionIcon)
            || ((window.location && window.location.origin) ? (window.location.origin + '/assets/images/bundle-attraction-icon.png') : '');
    }

    function formatAttractionOption(option) {
        if (!option.id) return option.text;
        var el = option.element;
        if (el && el.dataset && el.dataset.isBundle === '1') {
            var icon = bundleIconUrl();
            if (window.jQuery && icon) {
                var $wrap = window.jQuery('<span class="stp-lite-bundle-opt"></span>');
                $wrap.append(window.jQuery('<img>', {
                    src: icon,
                    alt: 'Bundle',
                    class: 'stp-lite-bundle-opt__icon'
                }));
                $wrap.append(document.createTextNode(option.text || el.dataset.name || 'Bundle'));
                return $wrap;
            }
            return '📦 ' + (option.text || 'Bundle');
        }
        return option.text;
    }

    function destroyAttractionSelect2(select) {
        if (!select || typeof window.jQuery === 'undefined' || !window.jQuery.fn || !window.jQuery.fn.select2) return;
        var $sel = window.jQuery(select);
        if ($sel.hasClass('select2-hidden-accessible')) {
            try { $sel.select2('destroy'); } catch (e) { /* ignore */ }
        }
    }

    function initAttractionSelect2(select) {
        if (!select || typeof window.jQuery === 'undefined' || !window.jQuery.fn || !window.jQuery.fn.select2) return;
        var $sel = window.jQuery(select);
        destroyAttractionSelect2(select);
        $sel.select2({
            placeholder: 'Select attraction',
            allowClear: true,
            width: '100%',
            templateResult: formatAttractionOption,
            templateSelection: formatAttractionOption
        });
    }

    function loadAttractions(root, stay) {
        var T = S();
        var select = root.querySelector('.attraction-select');
        if (!select) return Promise.resolve();
        var q = T.inv(stay.cityName, stay.country);
        destroyAttractionSelect2(select);
        select.disabled = true;
        select.innerHTML = '<option value="">Loading attractions…</option>';
        return T.fetchJson((cfg().routes.fetchAttractionsByDmc || '') + '?' + q.qs)
            .then(function (res) {
                var list = (res && res.attractions) || [];
                var bundles = (res && res.bundles) || [];
                root.__attractions = list;
                root.__bundles = bundles;
                select.innerHTML = '<option value="">Select attraction</option>';
                list.forEach(function (a) {
                    var opt = document.createElement('option');
                    opt.value = a.attraction_id || a.id;
                    opt.textContent = a.name || a.attraction_name || 'Attraction';
                    opt.dataset.name = a.name || a.attraction_name || '';
                    opt.dataset.isBundle = '0';
                    select.appendChild(opt);
                });
                // Packaged attraction bundles — shown with bundle icon (classic parity)
                bundles.forEach(function (b) {
                    var opt = document.createElement('option');
                    var pid = b.package_attraction_id || b.id;
                    opt.value = 'bundle_' + pid;
                    opt.textContent = b.name || 'Bundle';
                    opt.dataset.name = b.name || 'Bundle';
                    opt.dataset.isBundle = '1';
                    opt.dataset.packageAttractionId = String(pid || '');
                    opt.dataset.vehicleIncluded = b.vehicle_included ? '1' : '0';
                    opt.dataset.guideIncluded = b.guide_included ? '1' : '0';
                    opt.dataset.adultPrice = b.adult_price || 0;
                    opt.dataset.childPrice = b.child_price || 0;
                    opt.dataset.seniorPrice = b.senior_adult_price || b.senior_citizen_price || 0;
                    select.appendChild(opt);
                });
                select.disabled = false;
                initAttractionSelect2(select);
            })
            .catch(function () {
                select.innerHTML = '<option value="">Error loading</option>';
                select.disabled = false;
                initAttractionSelect2(select);
            });
    }

    function applyBundleSelection(root, stay, aOpt) {
        var T = S();
        var ticket = root.querySelector('.attraction-ticket');
        if (!ticket || !aOpt) return;
        ticket.innerHTML = '<option value="">Select ticket</option>';
        var opt = document.createElement('option');
        opt.value = aOpt.value;
        opt.textContent = (aOpt.dataset.name || aOpt.textContent || 'Bundle') + ' (Bundle)';
        opt.dataset.name = aOpt.dataset.name || '';
        opt.dataset.adultPrice = aOpt.dataset.adultPrice || 0;
        opt.dataset.childPrice = aOpt.dataset.childPrice || 0;
        opt.dataset.seniorPrice = aOpt.dataset.seniorPrice || 0;
        ticket.appendChild(opt);
        ticket.value = opt.value;
        ticket.disabled = false;

        T.applyMasterGuideVehicle(
            root,
            PREFIX,
            aOpt.dataset.vehicleIncluded === '1',
            aOpt.dataset.guideIncluded === '1',
            { preserve: !!root.__hydrating }
        );
    }

    function loadTickets(root, stay, attractionId) {
        var T = S();
        var ticket = root.querySelector('.attraction-ticket');
        if (!ticket) return Promise.resolve();
        if (!attractionId) {
            ticket.innerHTML = '<option value="">Select attraction</option>';
            ticket.disabled = true;
            if (typeof T.unlockMasterGuideVehicle === 'function' && !root.__hydrating) {
                T.unlockMasterGuideVehicle(root, PREFIX);
            }
            return Promise.resolve();
        }
        // Bundle: no ticket API — use package prices as a single ticket option
        var attr = root.querySelector('.attraction-select');
        var aOpt = attr && attr.options[attr.selectedIndex];
        if (aOpt && aOpt.dataset.isBundle === '1') {
            applyBundleSelection(root, stay, aOpt);
            return Promise.resolve();
        }
        if (typeof T.unlockMasterGuideVehicle === 'function' && !root.__hydrating) {
            T.unlockMasterGuideVehicle(root, PREFIX);
        }
        var q = T.inv(stay.cityName, stay.country);
        ticket.disabled = true;
        ticket.innerHTML = '<option value="">Loading tickets…</option>';
        var qs = 'attraction_id=' + encodeURIComponent(attractionId) + '&' + q.qs;
        return T.fetchJson((cfg().routes.fetchTicketsByAttraction || '') + '?' + qs)
            .then(function (res) {
                var list = (res && res.tickets) || [];
                if (!list.length && root.__attractions) {
                    var hit = root.__attractions.find(function (a) {
                        return String(a.attraction_id || a.id) === String(attractionId);
                    });
                    if (hit && Array.isArray(hit.tickets)) list = hit.tickets;
                }
                ticket.innerHTML = '<option value="">Select ticket</option>';
                list.forEach(function (t) {
                    var opt = document.createElement('option');
                    opt.value = t.ticket_id || t.id;
                    opt.textContent = t.name || t.ticket_name || 'Ticket';
                    opt.dataset.name = t.name || t.ticket_name || '';
                    opt.dataset.adultPrice = t.adult_price || 0;
                    opt.dataset.childPrice = t.child_price || 0;
                    opt.dataset.seniorPrice = t.senior_adult_price || t.senior_price || 0;
                    ticket.appendChild(opt);
                });
                ticket.disabled = false;
            })
            .catch(function () {
                ticket.innerHTML = '<option value="">Error loading tickets</option>';
                ticket.disabled = false;
            });
    }

    function getPrice(root) {
        var T = S();
        var ticket = root.querySelector('.attraction-ticket');
        var opt = ticket && ticket.options[ticket.selectedIndex];
        if (!opt || !opt.value) {
            alert('Select a ticket first.');
            return;
        }
        var g = guestCounts(root);
        var adultP = parseFloat(opt.dataset.adultPrice) || 0;
        var childP = parseFloat(opt.dataset.childPrice) || 0;
        var seniorP = parseFloat(opt.dataset.seniorPrice) || 0;
        var ticketTotal = (adultP * g.adults) + (childP * g.children) + (seniorP * g.seniors);
        var xfer = T.calcInlineTransferPrice(root, PREFIX, g.adults + g.seniors, g.children, g.infants);
        T.refreshTransferCostDisplay(root, PREFIX, g.adults + g.seniors, g.children, g.infants);
        var guide = T.calcInlineGuidePrice(root, PREFIX);
        var total = ticketTotal + (Number(xfer) || 0) + (Number(guide.total) || 0);
        var cur = root.getAttribute('data-currency') || 'SGD';
        var xferOpts = (Number(xfer) > 0 && typeof T.collectTransferOptions === 'function')
            ? T.collectTransferOptions(root, PREFIX, g.adults + g.seniors, g.children, g.infants)
            : null;
        var transferHtml = (xferOpts && typeof T.transferPriceDetailHtml === 'function')
            ? T.transferPriceDetailHtml(xferOpts, g.adults + g.seniors, g.children, g.infants, cur)
            : '';
        if (!transferHtml && xfer && typeof T.priceFormulaRowHtml === 'function') {
            transferHtml = T.priceFormulaRowHtml(
                '<strong>Transfer</strong>',
                cur + ' ' + Number(xfer).toFixed(2)
            );
        }
        var guideHtml = '';
        if (guide.total && typeof T.priceFormulaRowHtml === 'function') {
            guideHtml = T.priceFormulaRowHtml(
                '<strong>Guide</strong>',
                cur + ' ' + Number(guide.total).toFixed(2)
            );
        }
        var breakdownHtml = typeof T.paxPriceLinesHtml === 'function'
            ? T.paxPriceLinesHtml({
                currency: cur,
                metaHtml: '<div class="small text-muted mb-1">' + T.esc((opt.dataset.name || opt.textContent || 'Ticket')) + '</div>',
                adults: g.adults,
                children: g.children,
                seniors: g.seniors,
                infants: g.infants,
                adultPrice: adultP,
                childPrice: childP,
                seniorPrice: seniorP,
                infantPrice: 0,
                extraHtml: (transferHtml || '') + guideHtml
            })
            : '';
        root.__lastPrice = {
            total: total,
            ticketTotal: ticketTotal,
            transferTotal: Number(xfer) || 0,
            guideTotal: Number(guide.total) || 0,
            adultPrice: adultP,
            childPrice: childP,
            seniorPrice: seniorP,
            infantPrice: 0,
            adults: g.adults,
            children: g.children,
            seniors: g.seniors,
            infants: g.infants,
            breakdown: breakdownHtml
        };
        var panel = root.querySelector('[data-attraction-price-panel]');
        var totalEl = root.querySelector('.attraction-price-total');
        var detail = root.querySelector('.attraction-price-detail');
        if (panel) panel.classList.remove('d-none');
        if (totalEl) totalEl.textContent = cur + ' ' + total.toFixed(2);
        if (detail) detail.innerHTML = breakdownHtml;
        var add = root.querySelector('.attraction-add-btn');
        if (add) add.disabled = false;
    }

    function collectPayload(root, stay) {
        var T = S();
        var attr = root.querySelector('.attraction-select');
        var ticket = root.querySelector('.attraction-ticket');
        var aOpt = attr && attr.options[attr.selectedIndex];
        var tOpt = ticket && ticket.options[ticket.selectedIndex];
        var g = guestCounts(root);
        var adultP = tOpt ? (parseFloat(tOpt.dataset.adultPrice) || 0) : 0;
        var childP = tOpt ? (parseFloat(tOpt.dataset.childPrice) || 0) : 0;
        var seniorP = tOpt ? (parseFloat(tOpt.dataset.seniorPrice) || 0) : 0;
        var ticketTotal = (adultP * g.adults) + (childP * g.children) + (seniorP * g.seniors);
        var xferGuests = g.adults + g.seniors;
        var transferOptions = T.collectTransferOptions(root, PREFIX, xferGuests, g.children, g.infants);
        var guideOptions = T.collectGuideOptions(root, PREFIX);
        // Store ticket-only; transfer/guide costs live in nested options (classic parity)
        var total = ticketTotal;
        if (root.__lastPrice && root.__lastPrice.ticketTotal != null) {
            total = Number(root.__lastPrice.ticketTotal) || total;
        }
        var supplement = T.autoSupplement(g.adults + g.seniors);
        var visitTime = T.readAmPmValue(root, 'attraction');

        return {
            AttractionId: attr ? attr.value : '',
            AttractionName: aOpt ? (aOpt.dataset.name || aOpt.textContent) : '',
            ticketId: ticket ? ticket.value : '',
            ticketName: tOpt ? (tOpt.dataset.name || tOpt.textContent) : '',
            ticket_details: {
                adult_price: adultP,
                child_price: childP,
                senior_adult_price: seniorP
            },
            adultCount: g.adults,
            childCount: g.children,
            seniorCount: g.seniors,
            infantCount: g.infants,
            adults: g.adults,
            children: g.children,
            infants: g.infants,
            visitTime: visitTime,
            bookingDate: (root.querySelector('.attraction-date') || {}).value || stay.start || '',
            totalPrice: total,
            grand_total: total,
            supplement: !!supplement,
            is_supplement: !!supplement,
            transfer_options: transferOptions,
            guide_options: guideOptions,
            is_bundle: !!(aOpt && aOpt.dataset.isBundle === '1'),
            package_attraction_id: (aOpt && aOpt.dataset.isBundle === '1')
                ? (aOpt.dataset.packageAttractionId || String(attr.value || '').replace(/^bundle_/, ''))
                : null,
            vehicle_included: !!(aOpt && aOpt.dataset.vehicleIncluded === '1'),
            guide_included: !!(aOpt && aOpt.dataset.guideIncluded === '1'),
            city: stay.cityName || '',
            country: stay.country || '',
            currency: stay.currency || '',
            plan_index: stay.planIndex || '',
            remarks: '',
            bookingType: T.resolveRowBookingType ? T.resolveRowBookingType(null) : 'enquiry'
        };
    }

    function setAddMode(root, editing) {
        var btn = root.querySelector('.attraction-add-btn');
        if (!btn) return;
        btn.innerHTML = editing
            ? '<i class="ri-save-line me-1"></i>Update'
            : '<i class="ri-add-line me-1"></i>Add';
    }

    function renderAdded(root) {
        var T = S();
        var host = root.querySelector('[data-attraction-added-list]');
        if (!host) return;
        var rows = readChunk(root);
        var cur = root.getAttribute('data-currency') || 'SGD';
        if (!rows.length) { host.innerHTML = ''; return; }
        var html = '<div class="table-responsive stp-lite-svc-added-wrap"><table class="table table-sm align-middle mb-0 stp-lite-svc-added-table"><thead><tr>' +
            '<th>Attraction</th><th>Ticket</th><th>Guests</th><th class="text-end">Total</th><th>Supplement</th><th class="text-end">Actions</th>' +
            '</tr></thead><tbody>';
        rows.forEach(function (row, idx) {
            var editing = root.__editingIdx === idx;
            var extras = [];
            if (row.transfer_options && row.transfer_options.transfer_required) extras.push('Transfer');
            if (row.guide_options && row.guide_options.guide_required) extras.push('Guide');
            html += '<tr class="' + (editing ? 'is-editing' : '') + '" data-idx="' + idx + '">' +
                '<td><div class="fw-semibold">' + T.esc(row.AttractionName || 'Attraction') + '</div>' +
                '<small class="text-muted">' + T.esc(row.bookingDate || '') +
                (extras.length ? ' · ' + extras.join(' + ') : '') + '</small>' +
                T.editingMarkHtml(editing) + '</td>' +
                '<td><small>' + T.esc(row.ticketName || '—') + '</small></td>' +
                '<td><small>' + T.esc(row.adultCount || 0) + 'A / ' + T.esc(row.childCount || 0) + 'C' +
                (row.seniorCount ? ' / ' + T.esc(row.seniorCount) + 'S' : '') + '</small></td>' +
                '<td class="text-end fw-semibold text-nowrap">' + cur + ' ' + Number(
                    (typeof T.serviceRowDisplayTotal === 'function' ? T.serviceRowDisplayTotal(row) : row.totalPrice) || 0
                ).toFixed(2) + '</td>' +
                '<td><div class="form-check mb-0"><input class="form-check-input attraction-is-supplement" type="checkbox" data-idx="' + idx + '"' +
                (row.supplement || row.is_supplement ? ' checked' : '') + '>' +
                '<label class="form-check-label" style="font-size:0.72rem;">Supplement</label></div></td>' +
                '<td class="text-end">' + T.addedTableActions('attraction', idx, true) + '</td>' +
                '</tr>';
        });
        html += '</tbody></table></div>';
        host.innerHTML = html;
    }

    function hydrate(root, stay, row) {
        if (!row) return;
        var T = S();
        root.__hydrating = true;
        var dateEl = root.querySelector('.attraction-date');
        var adultsEl = root.querySelector('.attraction-adults');
        var childrenEl = root.querySelector('.attraction-children');
        var seniorsEl = root.querySelector('.attraction-seniors');
        var attr = root.querySelector('.attraction-select');
        if (dateEl) dateEl.value = row.bookingDate || stay.start || '';
        if (adultsEl) adultsEl.value = String(row.adultCount != null ? row.adultCount : (row.adults || 0));
        if (childrenEl) childrenEl.value = String(row.childCount != null ? row.childCount : (row.children || 0));
        if (seniorsEl) seniorsEl.value = String(row.seniorCount || 0);
        T.setAmPmValue(root, 'attraction', row.visitTime || '');

        function afterTickets() {
            var ticket = root.querySelector('.attraction-ticket');
            if (ticket && row.ticketId) ticket.value = String(row.ticketId);
            var ticketOnly = Number(row.totalPrice != null ? row.totalPrice : (row.grand_total || 0)) || 0;
            var displayTotal = typeof T.serviceRowDisplayTotal === 'function'
                ? T.serviceRowDisplayTotal(row)
                : ticketOnly;
            root.__lastPrice = {
                total: displayTotal,
                ticketTotal: ticketOnly,
                transferTotal: (row.transfer_options && Number(row.transfer_options.cost)) || 0,
                guideTotal: (row.guide_options && Number(row.guide_options.total_price)) || 0,
                breakdown: (row.ticket_details
                    ? ((row.adultCount || 0) + '×' + Number((row.ticket_details || {}).adult_price || 0).toFixed(2))
                    : '')
            };
            var cur = root.getAttribute('data-currency') || 'SGD';
            var panel = root.querySelector('[data-attraction-price-panel]');
            var totalEl = root.querySelector('.attraction-price-total');
            if (panel) panel.classList.remove('d-none');
            if (totalEl) totalEl.textContent = cur + ' ' + Number(displayTotal || 0).toFixed(2);
            var add = root.querySelector('.attraction-add-btn');
            if (add) add.disabled = false;
            root.__hydrating = false;
        }

        var extras = Promise.all([
            T.hydrateTransferExtras(root, PREFIX, row.transfer_options, stay),
            T.hydrateGuideExtras(root, PREFIX, row.guide_options, stay)
        ]).then(function () {
            var opt = attr && attr.options[attr.selectedIndex];
            var vehicleYes = row.vehicle_included || (opt && opt.dataset && opt.dataset.vehicleIncluded === '1');
            var guideYes = row.guide_included || (opt && opt.dataset && opt.dataset.guideIncluded === '1');
            if ((row.is_bundle || row.package_attraction_id) && typeof T.applyMasterGuideVehicle === 'function') {
                T.applyMasterGuideVehicle(root, PREFIX, vehicleYes, guideYes, { preserve: true });
            }
        });

        if (attr && row.AttractionId) {
            var attrVal = String(row.AttractionId);
            if ((row.is_bundle || row.package_attraction_id) && attrVal.indexOf('bundle_') !== 0) {
                attrVal = 'bundle_' + (row.package_attraction_id || attrVal);
            }
            attr.value = attrVal;
            if (window.jQuery && window.jQuery(attr).data('select2')) {
                window.jQuery(attr).val(attrVal).trigger('change.select2');
            }
            Promise.all([loadTickets(root, stay, attrVal), extras]).then(afterTickets);
        } else {
            extras.then(afterTickets);
        }
    }

    function addRow(root, stay) {
        if (!root.__lastPrice) { alert('Please Get Price first.'); return; }
        var rows = readChunk(root);
        var payload = collectPayload(root, stay);
        var T = S();
        if (root.__editingIdx != null && root.__editingIdx >= 0 && root.__editingIdx < rows.length) {
            payload.supplement = rows[root.__editingIdx].supplement;
            payload.is_supplement = rows[root.__editingIdx].is_supplement;
            payload.bookingType = T.resolveRowBookingType
                ? T.resolveRowBookingType(rows[root.__editingIdx])
                : (rows[root.__editingIdx].bookingType || payload.bookingType);
            rows[root.__editingIdx] = payload;
            root.__editingIdx = null;
            setAddMode(root, false);
        } else {
            rows.push(payload);
        }
        writeChunk(root, rows);
        renderAdded(root);
        invalidate(root);
        var attr = root.querySelector('.attraction-select');
        if (attr) S().clearSelectOrInput(attr, '');
        var ticket = root.querySelector('.attraction-ticket');
        if (ticket) {
            ticket.innerHTML = '<option value="">Select attraction</option>';
            ticket.disabled = true;
            S().clearSelectOrInput(ticket, '');
        }
        S().resetTransferGuideExtras(root, PREFIX);
        S().setAmPmValue(root, 'attraction', '');
        // Keep Get Price enabled so user can search/add another item
        var getBtn = root.querySelector('.attraction-get-price-btn');
        if (getBtn) getBtn.disabled = false;
        var addBtn = root.querySelector('.attraction-add-btn');
        if (addBtn) addBtn.disabled = true;
    }

    function bindShell(root, stay) {
        if (!root || root.__bound) return;
        root.__bound = true;
        var T = S();
        T.bindAmPm(root);
        T.bindStayDate(root.querySelector('.attraction-date'), stay, function () { invalidate(root); });
        T.bindGuestCaps(root);
        loadAttractions(root, stay);

        T.bindTransferExtras(root, stay, PREFIX, function () {
            var g = guestCounts(root);
            return { adults: g.adults + g.seniors, children: g.children, infants: g.infants };
        }, function () { if (!root.__hydrating) invalidate(root); });
        T.bindGuideExtras(root, stay, PREFIX, function () { if (!root.__hydrating) invalidate(root); });

        var attr = root.querySelector('.attraction-select');
        if (attr) {
            // Native + Select2 both fire change
            var onAttrChange = function () {
                if (!root.__hydrating) invalidate(root);
                loadTickets(root, stay, attr.value);
            };
            attr.addEventListener('change', onAttrChange);
            if (window.jQuery) {
                window.jQuery(attr).off('select2:select.stpBundle select2:clear.stpBundle')
                    .on('select2:select.stpBundle select2:clear.stpBundle', onAttrChange);
            }
        }

        root.querySelector('.attraction-get-price-btn').addEventListener('click', function () { getPrice(root); });
        root.querySelector('.attraction-add-btn').addEventListener('click', function () { addRow(root, stay); });

        root.querySelectorAll('.attraction-ticket, .attraction-adults, .attraction-children, .attraction-seniors').forEach(function (el) {
            el.addEventListener('change', function () {
                if (!root.__hydrating) invalidate(root);
                var g = guestCounts(root);
                T.refreshTransferCostDisplay(root, PREFIX, g.adults + g.seniors, g.children, g.infants);
            });
            el.addEventListener('input', function () {
                if (!root.__hydrating) invalidate(root);
                var g = guestCounts(root);
                T.refreshTransferCostDisplay(root, PREFIX, g.adults + g.seniors, g.children, g.infants);
            });
        });
        root.addEventListener('stp:time-changed', function () { if (!root.__hydrating) invalidate(root); });

        root.addEventListener('change', function (e) {
            var chk = e.target.closest('.attraction-is-supplement');
            if (!chk) return;
            var rows = readChunk(root);
            var i = parseInt(chk.getAttribute('data-idx'), 10) || 0;
            if (rows[i]) {
                rows[i].supplement = !!chk.checked;
                rows[i].is_supplement = !!chk.checked;
                writeChunk(root, rows);
            }
        });
        root.addEventListener('click', function (e) {
            var removeBtn = e.target.closest('.attraction-remove');
            if (removeBtn) {
                if (!(window.StpLiteTransportShared && window.StpLiteTransportShared.confirmRemoveService
                    ? window.StpLiteTransportShared.confirmRemoveService()
                    : window.confirm('Are you sure you want to remove this service?'))) return;
                var rows = readChunk(root);
                rows.splice(parseInt(removeBtn.getAttribute('data-idx'), 10) || 0, 1);
                writeChunk(root, rows);
                root.__editingIdx = null;
                setAddMode(root, false);
                renderAdded(root);
                return;
            }
            var editBtn = e.target.closest('.attraction-edit-added');
            if (editBtn) {
                var idx = parseInt(editBtn.getAttribute('data-idx'), 10) || 0;
                var row = readChunk(root)[idx];
                if (!row) return;
                root.__editingIdx = idx;
                setAddMode(root, true);
                hydrate(root, stay, row);
                renderAdded(root);
                return;
            }
            var viewBtn = e.target.closest('.attraction-view-breakup');
            if (viewBtn) {
                var r = readChunk(root)[parseInt(viewBtn.getAttribute('data-idx'), 10) || 0];
                if (!r) return;
                var td = r.ticket_details || {};
                var xfer = r.transfer_options || {};
                var guide = r.guide_options || {};
                var cur = r.currency || root.getAttribute('data-currency') || 'SGD';
                var infants = Math.max(0, parseInt(r.infantCount != null ? r.infantCount : r.infants, 10) || 0);
                var adultsXfer = Math.max(0, parseInt(r.adultCount != null ? r.adultCount : r.adults, 10) || 0)
                    + Math.max(0, parseInt(r.seniorCount, 10) || 0);
                var childrenXfer = Math.max(0, parseInt(r.childCount != null ? r.childCount : r.children, 10) || 0);
                var transferHtml = (xfer.transfer_required && typeof T.transferPriceDetailHtml === 'function')
                    ? T.transferPriceDetailHtml(
                        xfer,
                        xfer.adults != null ? xfer.adults : adultsXfer,
                        xfer.children != null ? xfer.children : childrenXfer,
                        xfer.infants != null ? xfer.infants : infants,
                        cur
                    )
                    : '';
                if (!transferHtml && xfer.transfer_required && typeof T.priceFormulaRowHtml === 'function') {
                    transferHtml = T.priceFormulaRowHtml(
                        '<strong>Transfer</strong> (' + T.esc(xfer.type || '') + ')',
                        cur + ' ' + Number(xfer.cost || 0).toFixed(2)
                    );
                }
                var guideHtml = '';
                if (guide.guide_required && typeof T.priceFormulaRowHtml === 'function') {
                    guideHtml = T.priceFormulaRowHtml(
                        '<strong>Guide</strong>',
                        cur + ' ' + Number(guide.total_price || 0).toFixed(2)
                    );
                }
                var detailHtml = typeof T.paxPriceLinesHtml === 'function'
                    ? T.paxPriceLinesHtml({
                        currency: cur,
                        metaHtml: '<div class="small text-muted mb-1">' + T.esc(r.ticketName || '') +
                            (r.visitTime ? ' · ' + T.esc(r.visitTime) : '') + '</div>',
                        adults: r.adultCount || r.adults || 0,
                        children: r.childCount || r.children || 0,
                        seniors: r.seniorCount || 0,
                        infants: infants,
                        adultPrice: Number(td.adult_price || 0),
                        childPrice: Number(td.child_price || 0),
                        seniorPrice: Number(td.senior_adult_price || 0),
                        infantPrice: 0,
                        extraHtml: (transferHtml || '') + guideHtml
                    })
                    : '';
                T.showPriceBreakdownModal(
                    r.AttractionName || 'Attraction',
                    cur,
                    (typeof T.serviceRowDisplayTotal === 'function' ? T.serviceRowDisplayTotal(r) : r.totalPrice),
                    detailHtml
                );
            }
        });

        renderAdded(root);
        T.updateServiceHeaderTotal(root, 'attraction');
    }

    function mountAll(scope) {
        var T = S();
        (scope || document).querySelectorAll('[data-stp-attraction-mount]').forEach(function (el) {
            var stay = T.stayFromPanel(el, 'attraction');
            el.innerHTML = shellHtml(stay);
            bindShell(el.querySelector('.stp-lite-attraction'), stay);
        });
        if (window.StpLiteGuestCaps) window.StpLiteGuestCaps.refreshGuestDependentUI();
    }

    window.StpLiteAttraction = {
        mountAll: mountAll,
        seedAdded: function (root, rows) {
            if (!root) return;
            writeChunk(root, rows || []);
            renderAdded(root);
        }
    };
})(window, document);
/* === END attraction.js === */
