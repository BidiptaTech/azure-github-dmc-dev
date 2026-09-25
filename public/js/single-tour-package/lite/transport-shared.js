/* === STP LITE: transport-shared.js ===
 * Shared zone / port / vehicle fetch + Shared/Private/hourly pricing helpers
 * === */
(function (window) {
    'use strict';

    function cfg() {
        return window.STP_LITE_CONFIG || {};
    }

    function esc(s) {
        return String(s || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function zoneOn() {
        return parseInt(cfg().zoneOn, 10) === 1;
    }

    /** zone_on for a sibling/inventory DMC id (Master booking SG → SG DMC flag). */
    function zoneOnForDmc(dmcId) {
        var id = dmcId != null && dmcId !== '' ? String(dmcId) : '';
        var map = cfg().siblingDmcZoneOnMap || {};
        if (id !== '' && (map[id] != null || map[parseInt(id, 10)] != null)) {
            var flag = map[id] != null ? map[id] : map[parseInt(id, 10)];
            return parseInt(flag, 10) === 1;
        }
        return zoneOn();
    }

    /** zone_on for the inventory DMC of this stay city/country. */
    function zoneOnForStay(city, country, dmcIdHint) {
        if (dmcIdHint != null && dmcIdHint !== '') {
            return zoneOnForDmc(dmcIdHint);
        }
        var q = inv(city, country);
        return zoneOnForDmc(q && q.dmc_id);
    }

    function inv(city, country) {
        return window.buildInventoryDmcQuery
            ? window.buildInventoryDmcQuery(city, country)
            : { qs: 'city=' + encodeURIComponent(city || '') + '&country=' + encodeURIComponent(country || '') + '&dmc_id=' + (cfg().dmcId || ''), dmc_id: cfg().dmcId || 0, city: city, country: country };
    }

    function tourGuests() {
        if (window.StpLiteGuestCaps && window.StpLiteGuestCaps.getCaps) {
            return window.StpLiteGuestCaps.getCaps();
        }
        return {
            adults: parseInt((document.getElementById('adults') || {}).value, 10) || 1,
            children: parseInt((document.getElementById('children') || {}).value, 10) || 0,
            infants: parseInt((document.getElementById('infants') || {}).value, 10) || 0
        };
    }

    function fetchJson(url, opts) {
        opts = opts || {};
        var headers = Object.assign({
            'Accept': 'application/json',
            'X-CSRF-TOKEN': cfg().csrfToken || '',
            'X-Requested-With': 'XMLHttpRequest'
        }, opts.headers || {});
        return fetch(url, Object.assign({ credentials: 'same-origin' }, opts, { headers: headers }))
            .then(function (r) { return r.json(); });
    }

    /** Dedupe in-flight + short cache (backup fetchJsonDeduped) — speeds hotel/rooms/etc. */
    function fetchJsonCached(url, opts) {
        window.__stpLiteFetchCache = window.__stpLiteFetchCache || Object.create(null);
        window.__stpLiteFetchInflight = window.__stpLiteFetchInflight || Object.create(null);
        if (Object.prototype.hasOwnProperty.call(window.__stpLiteFetchCache, url)) {
            return Promise.resolve(window.__stpLiteFetchCache[url]);
        }
        if (window.__stpLiteFetchInflight[url]) {
            return window.__stpLiteFetchInflight[url];
        }
        var p = fetchJson(url, opts).then(function (data) {
            window.__stpLiteFetchCache[url] = data;
            delete window.__stpLiteFetchInflight[url];
            return data;
        }).catch(function (err) {
            delete window.__stpLiteFetchInflight[url];
            throw err;
        });
        window.__stpLiteFetchInflight[url] = p;
        return p;
    }

    function fetchZones(city, country) {
        var q = inv(city, country);
        return fetchJsonCached((cfg().routes.fetchZonesByDmc || '') + '?' + q.qs);
    }

    function fetchZoneLocations(city) {
        return fetchJsonCached((cfg().routes.fetchZoneAssignedLocations || '') + '?city=' + encodeURIComponent(city || ''));
    }

    function fetchPorts(countryIdOrName, cityName) {
        var params = new URLSearchParams();
        var country = countryIdOrName || '';
        if (!country && cityName && window.resolveCountryForCity) {
            country = window.resolveCountryForCity(cityName) || '';
        }
        // API expects country_id (numeric id OR country name — same as backup)
        if (country) params.set('country_id', country);
        if (cityName) params.set('city', cityName);
        return fetchJsonCached((cfg().routes.fetchPortsByCountry || '') + '?' + params.toString());
    }

    function fetchHotels(city, country) {
        var q = inv(city, country);
        return fetchJsonCached((cfg().routes.fetchHotelsByDmc || '') + '?' + q.qs);
    }

    function fetchAttractions(city, country) {
        var q = inv(city, country);
        return fetchJsonCached((cfg().routes.fetchAttractionsByDmc || '') + '?' + q.qs);
    }

    function fetchRestaurants(city, country) {
        var q = inv(city, country);
        return fetchJsonCached((cfg().routes.fetchRestaurantsByDmc || '') + '?' + q.qs);
    }

    /** Backup-style AM/PM time control. Stores "HH:MM AM" in .stp-lite-time-hidden */
    function ampmTimeHtml(prefix, extraClass) {
        return (
            '<div class="stp-lite-ampm-time ' + (extraClass || '') + '" data-ampm-root="' + esc(prefix) + '">' +
            '  <div class="stp-lite-ampm-box">' +
            '    <input type="text" class="form-control form-control-sm stp-lite-time-input border-0 ' + esc(prefix) + '-time-input" ' +
            '           placeholder="00:00" maxlength="5" inputmode="numeric" autocomplete="off" data-no-select2="true">' +
            '    <span class="stp-lite-ampm-sep" aria-hidden="true"></span>' +
            '    <select class="stp-lite-time-ampm ' + esc(prefix) + '-time-ampm" data-no-select2="true" aria-label="AM or PM">' +
            '      <option value="AM" selected>AM</option><option value="PM">PM</option>' +
            '    </select>' +
            '  </div>' +
            '  <input type="hidden" class="stp-lite-time-hidden ' + esc(prefix) + '-time-hidden" value="">' +
            '</div>'
        );
    }

    function formatAmPmInput(input) {
        if (!input) return;
        var v = String(input.value || '').replace(/\D/g, '').slice(0, 4);
        if (!v.length) { input.value = ''; return; }
        if (v.length === 1) {
            var d = parseInt(v, 10);
            input.value = (d >= 2 && d <= 9) ? String(d).padStart(2, '0') : v;
            return;
        }
        var hour = parseInt(v.slice(0, 2), 10);
        if (isNaN(hour) || hour <= 0) hour = 12;
        if (hour > 12) hour = 12;
        if (v.length === 2) {
            input.value = String(hour).padStart(2, '0');
            return;
        }
        var minutesRaw = v.slice(2);
        if (minutesRaw.length === 1) {
            input.value = String(hour).padStart(2, '0') + ':' + minutesRaw;
            return;
        }
        var min = parseInt(minutesRaw.slice(0, 2), 10);
        if (isNaN(min) || min < 0) min = 0;
        if (min > 59) min = 59;
        input.value = String(hour).padStart(2, '0') + ':' + String(min).padStart(2, '0');
    }

    function syncAmPmHidden(root) {
        if (!root) return '';
        var input = root.querySelector('.stp-lite-time-input');
        var ampm = root.querySelector('.stp-lite-time-ampm');
        var hidden = root.querySelector('.stp-lite-time-hidden');
        if (!input || !ampm || !hidden) return '';
        formatAmPmInput(input);
        var raw = String(input.value || '').trim();
        if (!raw || raw.indexOf(':') === -1 || raw.length < 4) {
            hidden.value = '';
            return '';
        }
        var parts = raw.split(':');
        var hour = parseInt(parts[0], 10) || 0;
        var min = parseInt((parts[1] || '00').slice(0, 2), 10) || 0;
        if (hour < 1) hour = 12;
        if (hour > 12) hour = 12;
        if (min > 59) min = 59;
        hidden.value = String(hour).padStart(2, '0') + ':' + String(min).padStart(2, '0') + ' ' + (ampm.value || 'AM');
        return hidden.value;
    }

    function readAmPmValue(scope, prefix) {
        var root = scope.querySelector('[data-ampm-root="' + prefix + '"]') || scope;
        syncAmPmHidden(root);
        var hidden = root.querySelector('.' + prefix + '-time-hidden') || root.querySelector('.stp-lite-time-hidden');
        return hidden ? (hidden.value || '') : '';
    }

    function setAmPmValue(scope, prefix, value) {
        var root = scope.querySelector('[data-ampm-root="' + prefix + '"]');
        if (!root) return;
        var input = root.querySelector('.stp-lite-time-input');
        var ampm = root.querySelector('.stp-lite-time-ampm');
        var str = String(value || '').trim();
        var m = str.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)?$/i);
        if (m) {
            var h = parseInt(m[1], 10) || 12;
            if (h > 12) {
                // convert 24h
                var ap = h >= 12 ? 'PM' : 'AM';
                h = h % 12;
                if (h === 0) h = 12;
                if (input) input.value = String(h).padStart(2, '0') + ':' + m[2];
                if (ampm) ampm.value = (m[3] || ap).toUpperCase();
            } else {
                if (input) input.value = String(h).padStart(2, '0') + ':' + m[2];
                if (ampm) ampm.value = (m[3] || 'AM').toUpperCase();
            }
        } else if (input) {
            input.value = '';
        }
        syncAmPmHidden(root);
    }

    function bindAmPm(scope) {
        scope.querySelectorAll('[data-ampm-root]').forEach(function (root) {
            if (root.__ampmBound) return;
            root.__ampmBound = true;
            var input = root.querySelector('.stp-lite-time-input');
            var ampm = root.querySelector('.stp-lite-time-ampm');
            function sync() {
                syncAmPmHidden(root);
                root.dispatchEvent(new CustomEvent('stp:time-changed', { bubbles: true }));
            }
            if (input) {
                // Clicking anywhere on the field focuses + selects for easy typing (backup UX)
                input.addEventListener('click', function () {
                    input.focus();
                    try { input.select(); } catch (e) { /* ignore */ }
                });
                input.addEventListener('focus', function () {
                    try { input.select(); } catch (e) { /* ignore */ }
                });
                input.addEventListener('input', function () { formatAmPmInput(input); sync(); });
                input.addEventListener('change', sync);
                input.addEventListener('blur', sync);
            }
            if (ampm) ampm.addEventListener('change', sync);
            // Whole control click focuses the time field
            root.addEventListener('click', function (e) {
                if (e.target === ampm || (ampm && ampm.contains(e.target))) return;
                if (input && document.activeElement !== input) {
                    input.focus();
                    try { input.select(); } catch (err) { /* ignore */ }
                }
            });
        });
    }

    function clampDateInput(input, stay) {
        if (!input || !stay) return;
        var min = stay.start || '';
        var max = stay.end || stay.start || '';
        if (min) input.setAttribute('min', min);
        if (max) input.setAttribute('max', max);
        var val = input.value || min;
        if (min && val && val < min) val = min;
        if (max && val && val > max) val = max;
        if (!input.value && min) val = min;
        input.value = val || input.value || '';
    }

    function bindStayDate(input, stay, onChange) {
        if (!input) return;
        clampDateInput(input, stay);
        input.addEventListener('change', function () {
            clampDateInput(input, stay);
            if (typeof onChange === 'function') onChange();
        });
        input.addEventListener('input', function () {
            clampDateInput(input, stay);
            if (typeof onChange === 'function') onChange();
        });
    }

    /** Adults + seniors share the global adults pool. */
    function bindGuestCaps(root) {
        var adultsEl = root.querySelector('[data-guest-cap="adults"]');
        var childrenEl = root.querySelector('[data-guest-cap="children"]');
        var seniorsEl = root.querySelector('[data-guest-cap="seniors"]') || root.querySelector('.attraction-seniors');
        function apply() {
            var g = tourGuests();
            var maxAdults = parseInt(g.adults, 10) || 0;
            var maxChildren = parseInt(g.children, 10) || 0;
            if (adultsEl) adultsEl.setAttribute('max', String(maxAdults));
            if (childrenEl) childrenEl.setAttribute('max', String(maxChildren));
            var a = parseInt((adultsEl && adultsEl.value) || '0', 10) || 0;
            var s = parseInt((seniorsEl && seniorsEl.value) || '0', 10) || 0;
            var c = parseInt((childrenEl && childrenEl.value) || '0', 10) || 0;
            if (a > maxAdults) { a = maxAdults; if (adultsEl) adultsEl.value = String(a); }
            if (c > maxChildren) { c = maxChildren; if (childrenEl) childrenEl.value = String(c); }
            if (seniorsEl) {
                var seniorMax = Math.max(0, maxAdults - a);
                seniorsEl.setAttribute('max', String(seniorMax));
                if (s > seniorMax) { seniorsEl.value = String(seniorMax); }
            }
            // If adults + seniors exceed pool, shrink seniors first then adults
            a = parseInt((adultsEl && adultsEl.value) || '0', 10) || 0;
            s = parseInt((seniorsEl && seniorsEl.value) || '0', 10) || 0;
            if (a + s > maxAdults) {
                var overflow = a + s - maxAdults;
                if (seniorsEl && s > 0) {
                    var cut = Math.min(s, overflow);
                    s -= cut;
                    overflow -= cut;
                    seniorsEl.value = String(s);
                }
                if (overflow > 0 && adultsEl) {
                    adultsEl.value = String(Math.max(0, a - overflow));
                }
            }
        }
        [adultsEl, childrenEl, seniorsEl].forEach(function (el) {
            if (!el) return;
            el.addEventListener('change', apply);
            el.addEventListener('input', apply);
        });
        document.addEventListener('stp:guests-changed', apply);
        apply();
    }

    function editingMarkHtml(editing) {
        if (!editing) return '';
        return ' <span class="stp-lite-editing-label">Editing</span>';
    }

    function addedTableActions(prefix, idx, hasBreakdown) {
        return (
            '<div class="stp-lite-hotel-card__btns justify-content-end">' +
            (hasBreakdown
                ? ('<button type="button" class="btn btn-sm btn-outline-primary ' + prefix + '-view-breakup" data-idx="' + idx + '" title="Price breakdown">' +
                   '<i class="ri-file-list-3-line me-1"></i>Price breakdown</button>')
                : '') +
            '<button type="button" class="btn btn-sm btn-outline-secondary ' + prefix + '-edit-added" data-idx="' + idx + '" title="Modify">' +
            '<i class="ri-pencil-line me-1"></i>Modify</button>' +
            '<button type="button" class="btn btn-sm btn-outline-danger ' + prefix + '-remove" data-idx="' + idx + '" title="Remove">' +
            '<i class="ri-delete-bin-line"></i></button>' +
            '</div>'
        );
    }

    function showPriceBreakdownModal(title, currency, total, detailHtml) {
        var id = 'stpLiteSvcPriceModal';
        var modalEl = document.getElementById(id);
        if (!modalEl) {
            var wrap = document.createElement('div');
            wrap.innerHTML =
                '<div class="modal fade" id="' + id + '" tabindex="-1" aria-hidden="true">' +
                '  <div class="modal-dialog modal-dialog-centered modal-sm">' +
                '    <div class="modal-content stp-lite-price-modal">' +
                '      <div class="modal-header py-2"><h5 class="modal-title mb-0" id="' + id + 'Label" style="font-size:0.9rem;">Price breakdown</h5>' +
                '        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>' +
                '      <div class="modal-body p-3" id="' + id + 'Body"></div>' +
                '      <div class="modal-footer py-2"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button></div>' +
                '    </div></div></div>';
            document.body.appendChild(wrap.firstChild);
            modalEl = document.getElementById(id);
        }
        var titleEl = document.getElementById(id + 'Label');
        var bodyEl = document.getElementById(id + 'Body');
        if (titleEl) titleEl.textContent = title || 'Price breakdown';
        var detail = String(detailHtml || '');
        // Tour infants: always show at 0 in every service breakup (not added to total)
        var tourInfants = 0;
        try {
            tourInfants = Math.max(0, parseInt((tourGuests() || {}).infants, 10) || 0);
        } catch (eInf) { tourInfants = 0; }
        if (tourInfants > 0 && !/\bInfant\b/i.test(detail)) {
            detail += priceFormulaRowHtml(
                '<strong>Infant</strong> 0.00 × ' + tourInfants,
                String(currency || 'SGD').trim() + ' 0.00'
            );
        }
        if (bodyEl) {
            bodyEl.innerHTML =
                '<div class="stp-lite-svc-price-card">' +
                detail +
                '<div class="stp-lite-summary-row mt-2 pt-2" style="border-top:1px dashed #93c5fd;">' +
                '<span class="stp-lite-summary-row__left"><strong>Total</strong></span>' +
                '<strong class="stp-lite-summary-row__amt">' + esc(currency || '') + ' ' + Number(total || 0).toFixed(2) + '</strong></div></div>';
        }
        try {
            if (window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
                return;
            }
        } catch (e) { /* fall through */ }
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
    }

    /** Left formula / right amount line for service price panels & popups. */
    function priceFormulaRowHtml(labelHtml, amountText) {
        return (
            '<div class="stp-lite-summary-row">' +
            '<span class="stp-lite-summary-row__left">' + (labelHtml || '') + '</span>' +
            '<strong class="stp-lite-summary-row__amt">' + esc(String(amountText || '')) + '</strong></div>'
        );
    }

    /**
     * Segregated pax lines (same Coaster-Bus modal design):
     * Adult 25.00 × 2 | CUR 50.00
     * Child 30.00 × 2 | CUR 60.00
     * Infant 0.00 × N | CUR 0.00  (when infants present)
     */
    function paxPriceLinesHtml(opts) {
        opts = opts || {};
        var cur = String(opts.currency || 'SGD').trim() || 'SGD';
        var adults = Math.max(0, parseInt(opts.adults, 10) || 0);
        var children = Math.max(0, parseInt(opts.children, 10) || 0);
        var seniors = Math.max(0, parseInt(opts.seniors, 10) || 0);
        var infants = Math.max(0, parseInt(opts.infants, 10) || 0);
        if (infants <= 0) {
            try {
                infants = Math.max(0, parseInt((tourGuests() || {}).infants, 10) || 0);
            } catch (eInf) { infants = 0; }
        }
        var adultUnit = Number(opts.adultPrice) || 0;
        var childUnit = Number(opts.childPrice) || 0;
        var seniorUnit = Number(opts.seniorPrice) || 0;
        // Infants always display at 0 — never priced
        var infantUnit = 0;

        var html = '';
        if (opts.metaHtml) html += opts.metaHtml;

        function line(label, unit, qty) {
            var amt = unit * qty;
            return priceFormulaRowHtml(
                '<strong>' + esc(label) + '</strong> ' + unit.toFixed(2) + ' × ' + qty,
                cur + ' ' + amt.toFixed(2)
            );
        }

        if (adults > 0) html += line('Adult', adultUnit, adults);
        if (children > 0) html += line('Child', childUnit, children);
        if (seniors > 0) html += line('Senior', seniorUnit, seniors);
        if (infants > 0) html += line('Infant', infantUnit, infants);

        if (Array.isArray(opts.extraRows)) {
            opts.extraRows.forEach(function (row) {
                if (!row) return;
                html += priceFormulaRowHtml(row.labelHtml || '', row.amountText || '');
            });
        }
        if (opts.extraHtml) html += opts.extraHtml;
        return html;
    }

    /**
     * Attraction/restaurant transfer breakup: Shared = Adult/Child unit × qty (Infant always 0);
     * Private = vehicle rate (× count). Matches Coaster-Bus style lines.
     */
    function transferPriceDetailHtml(xfer, adults, children, infants, currency) {
        if (!xfer || typeof xfer !== 'object') return '';
        if (!(xfer.transfer_required || xfer.vehicle_id || Number(xfer.cost) > 0)) return '';

        var cur = String(currency || 'SGD').trim() || 'SGD';
        var typeRaw = String(xfer.type || '').trim();
        var type = typeRaw.toLowerCase();
        var cost = Number(xfer.cost) || 0;
        var vd = xfer.vehicle_details || {};
        var a = Math.max(0, parseInt(adults != null ? adults : xfer.adults, 10) || 0);
        var c = Math.max(0, parseInt(children != null ? children : xfer.children, 10) || 0);
        var i = Math.max(0, parseInt(infants != null ? infants : xfer.infants, 10) || 0);
        if (i <= 0) {
            try {
                i = Math.max(0, parseInt((tourGuests() || {}).infants, 10) || 0);
            } catch (eInf) { i = 0; }
        }
        var header = '<div class="small text-muted mb-1 mt-2"><strong>Transfer'
            + (typeRaw ? ' (' + esc(typeRaw) + ')' : '')
            + '</strong></div>';

        if (type === 'shared') {
            var adultUnit = Number(xfer.adult_price != null && xfer.adult_price !== ''
                ? xfer.adult_price
                : (vd.adult_price != null && vd.adult_price !== '' ? vd.adult_price : 0)) || 0;
            var childUnit = Number(xfer.child_price != null && xfer.child_price !== ''
                ? xfer.child_price
                : (vd.child_price != null && vd.child_price !== '' ? vd.child_price : 0)) || 0;

            var sharedUnit = Number(
                (xfer.shared_price != null && xfer.shared_price !== '') ? xfer.shared_price
                    : (vd.shared_price != null && vd.shared_price !== '') ? vd.shared_price
                        : (xfer.zone_pricing ? 0 : xfer.base_cost)
            ) || 0;

            if (adultUnit <= 0) adultUnit = sharedUnit;
            if (childUnit <= 0) childUnit = sharedUnit > 0 ? sharedUnit : adultUnit;

            // Infants never priced — derive unit from adults + children only
            var pax = a + c;
            if (adultUnit <= 0 && pax > 0 && cost > 0) {
                adultUnit = cost / pax;
                if (childUnit <= 0) childUnit = adultUnit;
            }

            return paxPriceLinesHtml({
                currency: cur,
                metaHtml: header,
                adults: a,
                children: c,
                infants: i,
                adultPrice: adultUnit,
                childPrice: childUnit,
                infantPrice: 0
            }) || (header + priceFormulaRowHtml('<strong>Shared</strong>', cur + ' ' + cost.toFixed(2)));
        }

        var vehCount = Math.max(
            1,
            parseInt(xfer.vehicle_count != null ? xfer.vehicle_count
                : (xfer.arrival_vehicle_count || xfer.departure_vehicle_count || 1), 10) || 1
        );
        var privateUnit = Number(
            (xfer.private_price != null && xfer.private_price !== '') ? xfer.private_price
                : (vd.private_price != null && vd.private_price !== '') ? vd.private_price
                    : (xfer.zone_pricing ? 0 : xfer.base_cost)
        ) || 0;
        if (privateUnit <= 0 && vehCount > 0 && cost > 0) {
            privateUnit = cost / vehCount;
        }
        var label = '<strong>Private</strong> ' + privateUnit.toFixed(2);
        if (vehCount > 1) label += ' × ' + vehCount + ' vehicles';
        return header + priceFormulaRowHtml(label, cur + ' ' + cost.toFixed(2));
    }

    /**
     * Shared vehicle popup/panel: Adult N × rate (+ Child …) on left, amount on right.
     * Private: vehicle rate × count.
     */
    function vehiclePriceDetailHtml(row, currency) {
        row = row || {};
        var cur = String(currency || row.currency || '').trim() || 'SGD';
        var type = String(row.type || row.mode || row.price_detail || '').toLowerCase();
        var adults = parseInt(row.adults, 10) || 0;
        var children = parseInt(row.children, 10) || 0;
        var infants = parseInt(row.infants, 10) || 0;
        if (infants <= 0) {
            try {
                var tg = tourGuests();
                infants = Math.max(0, parseInt(tg.infants, 10) || 0);
            } catch (eInf) { /* ignore */ }
        }
        var vehCount = Math.max(1, parseInt(row.vehicle_count != null ? row.vehicle_count : row.booked_vehicles, 10) || 1);
        var total = Number(row.totalPrice != null ? row.totalPrice : (row.total != null ? row.total : row.grand_total)) || 0;
        var adultUnit = Number(row.adult_price != null ? row.adult_price
            : (row.adultUnit != null ? row.adultUnit
                : (row.shared_price != null ? row.shared_price : row.unit))) || 0;
        var childUnit = Number(row.child_price != null ? row.child_price
            : (row.childUnit != null ? row.childUnit : adultUnit)) || 0;
        var infantUnit = 0;
        var privateUnit = Number(row.private_price != null ? row.private_price
            : (row.unit != null ? row.unit : 0)) || 0;

        // Infer shared adult unit from total when rate fields missing
        if (type === 'shared' && adultUnit <= 0 && adults > 0 && total > 0) {
            var childPart = children > 0 && childUnit > 0 ? (childUnit * children) : 0;
            var adultPart = Math.max(0, total - childPart);
            adultUnit = adults > 0 ? (adultPart / adults) : 0;
            if (childUnit <= 0 && children > 0 && adultPart >= total) {
                adultUnit = total / Math.max(1, adults + children);
                childUnit = adultUnit;
            }
        }
        if (type === 'shared' && adultUnit <= 0 && Number(row.shared_price) > 0) {
            adultUnit = Number(row.shared_price);
            if (childUnit <= 0) childUnit = adultUnit;
        }

        var metaParts = [];
        if (row.type || row.mode) metaParts.push(esc(row.type || row.mode));
        var routeFrom = row.entrypickup || row.exitpickup || row.pickup || '';
        var routeTo = row.entrydropoff || row.exitdropoff || row.dropoff || '';
        if (routeFrom || routeTo) {
            metaParts.push(esc(routeFrom) + (routeTo ? ' → ' + esc(routeTo) : ''));
        }
        if (row.selectedHours || row.hours) {
            metaParts.push(esc(String(row.selectedHours || row.hours)) + 'h');
        }
        if (row.entrytime || row.exittime || row.time) {
            metaParts.push(esc(row.entrytime || row.exittime || row.time));
        }

        var metaHtml = metaParts.length
            ? '<div class="small text-muted mb-1">' + metaParts.join(' · ') + '</div>'
            : '';

        if (type === 'shared') {
            var sharedHtml = paxPriceLinesHtml({
                currency: cur,
                metaHtml: metaHtml,
                adults: adults,
                children: children,
                infants: infants,
                adultPrice: adultUnit,
                childPrice: childUnit,
                infantPrice: infantUnit
            });
            if (!sharedHtml || (adults <= 0 && children <= 0 && infants <= 0)) {
                return metaHtml + priceFormulaRowHtml('<strong>Shared</strong>', cur + ' ' + total.toFixed(2));
            }
            return sharedHtml;
        }

        var unit = privateUnit > 0 ? privateUnit : (vehCount > 0 ? (total / vehCount) : total);
        var label = '<strong>Private</strong> ' + unit.toFixed(2);
        if (vehCount > 1) label += ' × ' + vehCount + ' vehicles';
        var html = metaHtml + priceFormulaRowHtml(label, cur + ' ' + total.toFixed(2));
        if (adults || children || infants) {
            html += '<div class="small text-muted mt-1">' + adults + 'A / ' + children + 'C'
                + (infants > 0 ? (' / ' + infants + 'I') : '') + '</div>';
        }
        if (infants > 0) {
            html += priceFormulaRowHtml(
                '<strong>Infant</strong> 0.00 × ' + infants,
                cur + ' 0.00'
            );
        }
        return html;
    }

    function fetchVehiclesByZones(payload) {
        var body = Object.assign({}, payload || {});
        return fetchJson(cfg().routes.fetchVehiclesByZones || '', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        });
    }

    function fetchVehiclesByCity(city, country, showAll) {
        var q = inv(city, country);
        var qs = q.qs + '&show_all=' + (showAll ? '1' : '0');
        return fetchJsonCached((cfg().routes.fetchVehiclesByCityAndDmc || '') + '?' + qs);
    }

    function fillSelect(select, placeholder, items, mapFn) {
        if (!select) return;
        select.innerHTML = '<option value="">' + esc(placeholder || 'Select') + '</option>';
        (items || []).forEach(function (item) {
            var mapped = mapFn(item);
            if (!mapped) return;
            var opt = document.createElement('option');
            opt.value = mapped.value;
            opt.textContent = mapped.label;
            if (mapped.dataset) {
                Object.keys(mapped.dataset).forEach(function (k) {
                    opt.dataset[k] = mapped.dataset[k];
                });
            }
            select.appendChild(opt);
        });
        select.disabled = false;
    }

    function numOrZero(v) {
        var n = parseFloat(v);
        return isNaN(n) ? 0 : n;
    }

    /**
     * Map API vehicle → <option> dataset.
     * Zone search (mapping_id / *_cost_price): use zone private/shared only — never vehicle master base_price.
     * City search (no mapping): use base_price / sharable_base_price.
     */
    function vehicleOptionMapper(v) {
        var mappingId = v.mapping_id != null && v.mapping_id !== '' ? String(v.mapping_id) : '';
        var zonePrivate = numOrZero(v.private_price);
        var zoneShared = numOrZero(v.shared_price);
        var masterBase = numOrZero(v.base_price);
        var masterShared = numOrZero(v.sharable_base_price);
        // Zone payload always has mapping_id and/or private_cost_price / shared_cost_price
        var fromZone = !!mappingId
            || v.private_cost_price != null
            || v.shared_cost_price != null;

        var privatePrice = fromZone ? zonePrivate : (masterBase || zonePrivate);
        var sharedPrice = fromZone ? zoneShared : (masterShared || zoneShared);
        var sellBase = fromZone ? privatePrice : (masterBase || privatePrice);
        var sellShared = fromZone ? sharedPrice : (masterShared || sharedPrice);

        var dataset = {
            vehicleName: v.vehicle_name || v.name || '',
            vehicleType: v.vehicle_type || '',
            seating: v.seating_capacity || '',
            privatePrice: privatePrice,
            sharedPrice: sharedPrice,
            privateCost: numOrZero(v.private_cost_price),
            sharedCost: numOrZero(v.shared_cost_price),
            costPerHour: numOrZero(v.cost_per_hour),
            sharableCostPerHour: numOrZero(v.sharable_cost_per_hour),
            basePrice: sellBase,
            sharableBasePrice: sellShared,

            fromZone: fromZone ? '1' : '0',
            sharable: v.sharable != null ? v.sharable : 1,
            mappingId: mappingId,
            adultPrice: v.adult_price != null && v.adult_price !== '' ? numOrZero(v.adult_price) : sellShared,
            childPrice: v.child_price != null && v.child_price !== '' ? numOrZero(v.child_price) : sellShared,
            infantPrice: numOrZero(v.infant_price)
        };
        // Vehicle package tiers from admin "Hourly prices" (hourly_price_1 … hourly_price_12)
        for (var h = 1; h <= 12; h++) {
            dataset['hourlyPrice' + h] = numOrZero(v['hourly_price_' + h]);
        }

        return {
            value: String(v.vehicle_id || v.id || ''),
            label: (v.vehicle_name || v.name || 'Vehicle') +
                (v.seating_capacity ? ' (' + v.seating_capacity + ' seats)' : ''),
            dataset: dataset
        };
    }

    function populateVehicles(select, vehicles) {
        fillSelect(select, 'Select vehicle', vehicles || [], vehicleOptionMapper);
    }

    /** Re-select vehicle after list reload (Modify / Search). */
    function selectVehicleValue(select, vehicleId, fallbackName) {
        if (!select || vehicleId == null || vehicleId === '') return false;
        var id = String(vehicleId);
        select.value = id;
        if (select.value === id) return true;
        var opt = document.createElement('option');
        opt.value = id;
        opt.textContent = fallbackName || ('Vehicle #' + id);
        opt.dataset.vehicleName = fallbackName || opt.textContent;
        select.appendChild(opt);
        select.value = id;
        select.disabled = false;
        return select.value === id;
    }

    function loadTransferPickupLocations(root, stay, prefix, selectedId) {
        var select = root.querySelector('.' + prefix + '-transfer-pickup');
        if (!select || !stay || !stay.cityName) return Promise.resolve();
        select.disabled = true;
        select.innerHTML = '<option value="">Loading locations…</option>';
        return Promise.all([
            fetchHotels(stay.cityName, stay.country),
            fetchAttractions(stay.cityName, stay.country),
            fetchRestaurants(stay.cityName, stay.country)
        ]).then(function (results) {
            var hotels = (results[0] && results[0].hotels) || [];
            var attractions = (results[1] && results[1].attractions) || [];
            var restaurants = (results[2] && results[2].restaurants) || [];
            select.innerHTML = '<option value="">Select pickup location</option>';
            function addGroup(label, items, type, idKey, nameKey) {
                if (!items.length) return;
                var og = document.createElement('optgroup');
                og.label = label;
                items.forEach(function (item) {
                    var opt = document.createElement('option');
                    opt.value = String(item[idKey] || item.id || '');
                    opt.textContent = item[nameKey] || item.name || label;
                    opt.dataset.type = type;
                    opt.dataset.label = opt.textContent;
                    og.appendChild(opt);
                });
                select.appendChild(og);
            }
            addGroup('Hotels', hotels, 'Hotel', 'hotel_unique_id', 'name');
            addGroup('Attractions', attractions, 'Attraction', 'attraction_id', 'name');
            addGroup('Restaurants', restaurants, 'Restaurant', 'restaurant_id', 'name');
            select.disabled = false;
            if (selectedId) {
                select.value = String(selectedId);
                if (select.value !== String(selectedId)) {
                    var miss = document.createElement('option');
                    miss.value = String(selectedId);
                    miss.textContent = String(selectedId);
                    select.appendChild(miss);
                    select.value = String(selectedId);
                }
            }
        }).catch(function () {
            select.innerHTML = '<option value="">Error loading locations</option>';
            select.disabled = false;
        });
    }

    /**
     * Backup-style pricing:
     * Private = fixed vehicle price
     * Shared = adult*adults + child*children (+ infant*infants often 0)
     * Hourly private = base + cost_per_hour * hours
     * Hourly shared = (base + sharable_cost_per_hour * hours) * guests
     * Zone mapping (fromZone/mappingId): use private_price/shared_price only — never master base_price.
     */
    function calcVehiclePrice(opt, serviceType, adults, children, infants, hours) {
        if (!opt) return { total: 0, unit: 0, mode: serviceType || 'private' };
        var a = parseInt(adults, 10) || 0;
        var c = parseInt(children, 10) || 0;
        var i = parseInt(infants, 10) || 0;
        var h = parseInt(hours, 10) || 0;
        var type = String(serviceType || 'private').toLowerCase();
        var fromZone = String(opt.dataset.fromZone || '') === '1' || !!(opt.dataset.mappingId);
        var privatePrice = parseFloat(opt.dataset.privatePrice) || 0;
        var sharedPrice = parseFloat(opt.dataset.sharedPrice) || 0;
        var adultUnit = parseFloat(opt.dataset.adultPrice) || sharedPrice;
        var childUnit = parseFloat(opt.dataset.childPrice) || sharedPrice;
        var infantUnit = parseFloat(opt.dataset.infantPrice) || 0;
        var masterBase = parseFloat(opt.dataset.basePrice) || 0;
        var sharableBase = parseFloat(opt.dataset.sharableBasePrice) || 0;
        // Zone: never fall back to vehicle master base. Non-zone: allow base / sharable base.
        var base = fromZone ? privatePrice : (masterBase || privatePrice);
        var cph = parseFloat(opt.dataset.costPerHour) || 0;
        var scph = parseFloat(opt.dataset.sharableCostPerHour) || 0;
        var total = 0;
        var source = fromZone ? 'zone' : 'base';

        if (hours > 0 && (type === 'hourly' || opt.__forceHourly)) {
            if (String(serviceType).toLowerCase() === 'shared') {
                total = (base + scph * h) * Math.max(1, a + c);
            } else {
                total = base + cph * h;
            }
            return { total: total, unit: total, mode: 'hourly', source: source };
        }

        if (type === 'shared') {
            // Infants never charged on shared transfer
            total = (adultUnit * a) + (childUnit * c);
            if (total <= 0 && sharedPrice > 0) {
                total = sharedPrice * Math.max(1, a + c);
                adultUnit = sharedPrice;
                childUnit = sharedPrice;
            }
            if (total <= 0 && !fromZone && sharableBase > 0) {
                total = sharableBase * Math.max(1, a + c);
                adultUnit = sharableBase;
                childUnit = sharableBase;
            }
            return {
                total: total,
                unit: adultUnit || sharedPrice || sharableBase,
                adultUnit: adultUnit,
                childUnit: childUnit,
                infantUnit: 0,
                adults: a,
                children: c,
                infants: i,
                mode: 'shared',
                source: source
            };
        }

        if (fromZone) {
            total = privatePrice;
        } else {
            total = privatePrice > 0 ? privatePrice : base;
        }
        return {
            total: total,
            unit: total,
            adultUnit: 0,
            childUnit: 0,
            adults: a,
            children: c,
            infants: i,
            mode: 'private',
            source: source
        };
    }

    /**
     * Sell price from vehicle admin "Hourly prices" (hourly_price_1 … hourly_price_12).
     * Hourly is always Private (flat package) — never Shared × pax.
     */
    function packageHourlySellPrice(opt, hours) {
        if (!opt || !opt.dataset) return 0;
        var h = parseInt(hours, 10) || 1;
        if (h < 1) h = 1;
        if (h > 12) h = 12;
        var pkg = parseFloat(opt.dataset['hourlyPrice' + h]) || 0;
        if (pkg > 0) return pkg;
        // If exact tier missing, scale from 1-hour package when present
        var one = parseFloat(opt.dataset.hourlyPrice1) || 0;
        if (one > 0) return one * h;
        return 0;
    }

    function calcHourlyPrice(opt, serviceType, adults, children, infants, hours) {
        if (!opt) return { total: 0, unit: 0, mode: 'hourly', source: 'hourly_package' };
        var h = parseInt(hours, 10) || 1;
        var total = packageHourlySellPrice(opt, h);
        if (total > 0) {
            return {
                total: total,
                unit: total,
                mode: 'hourly',
                source: 'hourly_package',
                hours: h
            };
        }
        // Legacy fallback only when package tiers are empty: base + cost_per_hour × hours
        var clone = { dataset: Object.assign({}, opt.dataset), __forceHourly: true };
        var priced = calcVehiclePrice(clone, 'private', adults, children, infants, h);
        priced.mode = 'hourly';
        priced.source = priced.source || 'base';
        priced.hours = h;
        return priced;
    }

    function syncHiddenJson(hiddenId, chunkSelector) {
        var all = [];
        document.querySelectorAll(chunkSelector).forEach(function (el) {
            try {
                var parsed = JSON.parse(el.value || '[]');
                if (Array.isArray(parsed)) all = all.concat(parsed);
            } catch (e) { /* ignore */ }
        });
        var hidden = document.getElementById(hiddenId);
        if (!hidden) {
            var form = document.getElementById('singleTourPackageForm');
            if (form) {
                hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.id = hiddenId;
                hidden.name = hiddenId;
                form.appendChild(hidden);
            }
        }
        if (hidden) hidden.value = JSON.stringify(all);
        return all;
    }

    function stayFromPanel(panelOrRoot, serviceKey) {
        var panel = panelOrRoot && panelOrRoot.closest
            ? (panelOrRoot.closest('[data-service="' + serviceKey + '"]') || panelOrRoot)
            : panelOrRoot;
        if (!panel || !panel.getAttribute) return {};
        return {
            cityId: panel.getAttribute('data-city-id') || '',
            cityName: panel.getAttribute('data-city-name') || '',
            country: panel.getAttribute('data-country') || '',
            currency: panel.getAttribute('data-currency') || cfg().dmcCurrency || 'SGD',
            dmcId: panel.getAttribute('data-dmc-id') || cfg().dmcId || '',
            zoneOn: panel.getAttribute('data-zone-on'),
            planIndex: panel.getAttribute('data-plan-index') || '',
            isReturn: panel.getAttribute('data-is-return') === '1',
            start: panel.getAttribute('data-stay-start') || '',
            end: panel.getAttribute('data-stay-end') || ''
        };
    }

    function cityLabel(stay) {
        var label = stay.cityName || '';
        if (stay.isReturn) label += ' (Return)';
        if (stay.start && stay.end && typeof moment !== 'undefined') {
            label += ' · ' + moment(stay.start, 'YYYY-MM-DD').format('MMM D') + '–' +
                moment(stay.end, 'YYYY-MM-DD').format('MMM D');
        }
        return label || '—';
    }

    function autoSupplement(serviceAdults) {
        var g = tourGuests();
        return (parseInt(serviceAdults, 10) || 0) < (parseInt(g.adults, 10) || 0);
    }

    function pricePanelHtml(currency, prefix) {
        return (
            '<div class="stp-lite-svc-price d-none" data-' + prefix + '-price-panel>' +
            '  <div class="stp-lite-svc-price-card">' +
            '    <div class="d-flex justify-content-between align-items-center gap-2">' +
            '      <div>' +
            '        <div class="stp-lite-svc-price-title">Price</div>' +
            '        <div class="stp-lite-svc-price-sub text-muted">After Get Price · ' + esc(currency) + '</div>' +
            '      </div>' +
            '      <strong class="' + prefix + '-price-total">' + esc(currency) + ' 0.00</strong>' +
            '    </div>' +
            '    <div class="' + prefix + '-price-detail text-muted mt-1" style="font-size:0.72rem;"></div>' +
            '  </div>' +
            '</div>'
        );
    }

    /** Compact Transfer Required + expandable fields (attraction / restaurant).
     * Zone ON: Pickup first → zone-mapped vehicles → readonly zone cost.
     * Zone OFF: Google Maps pickup + editable car cost (Shared × pax, Private as-is).
     */
    function transferExtrasHtml(prefix, stay) {
        var maps = !(zoneOnForStay(
            stay && stay.cityName,
            stay && stay.country,
            stay && stay.dmcId
        ));
        var pickupHtml = maps
            ? ('<input type="text" class="form-control form-control-sm google-maps-autocomplete ' + prefix + '-transfer-pickup-text" ' +
               'placeholder="Search pickup on map…" autocomplete="off">')
            : ('<select class="form-select form-select-sm ' + prefix + '-transfer-pickup" disabled data-no-select2="true">' +
               '<option value="">Select pickup</option></select>');
        var costHtml = maps
            ? ('<input type="number" min="0" step="0.01" class="form-control form-control-sm ' + prefix + '-transfer-cost" ' +
               'placeholder="Car cost" data-zone-off-cost="1">')
            : ('<input type="text" class="form-control form-control-sm ' + prefix + '-transfer-cost" readonly placeholder="—">');
        var vehiclePlaceholder = maps ? 'Select type' : 'Select pickup first';
        return (
            '<div class="stp-lite-extras-block">' +
            '  <div class="stp-lite-opt-card stp-lite-opt-card--compact d-none" data-' + prefix + '-transfer-card>' +
            '    <div class="row g-1 align-items-end stp-lite-xfer-row">' +
            '      <div class="col stp-lite-xfer-type"><label class="stp-lite-label">Type</label>' +
            '        <select class="form-select form-select-sm ' + prefix + '-transfer-type" data-no-select2="true">' +
            '          <option value="">Select</option><option value="Shared">Shared</option><option value="Private">Private</option></select></div>' +
            '      <div class="col stp-lite-xfer-pickup"><label class="stp-lite-label">Pickup location</label>' +
            pickupHtml +
            '</div>' +
            '      <div class="col stp-lite-xfer-vehicle"><label class="stp-lite-label">Vehicle</label>' +
            '        <select class="form-select form-select-sm ' + prefix + '-transfer-vehicle" disabled data-no-select2="true"><option value="">' + vehiclePlaceholder + '</option></select></div>' +
            '      <div class="col-auto stp-lite-xfer-time"><label class="stp-lite-label">Pickup time</label>' +
            ampmTimeHtml(prefix + '-xfer', '') +
            '      </div>' +
            '      <div class="col-auto stp-lite-xfer-cost"><label class="stp-lite-label">' + (maps ? 'Car cost' : 'Cost') + '</label>' +
            costHtml +
            '</div>' +
            '    </div>' +
            (maps
                ? ('    <div class="text-muted mt-1 ' + prefix + '-transfer-cost-hint" style="font-size:0.7rem;">' +
                   'Private = car cost · Shared = car cost × pax</div>')
                : ('    <div class="text-muted mt-1 ' + prefix + '-transfer-cost-hint" style="font-size:0.7rem;">' +
                   'Vehicles &amp; price from zone mapping for selected pickup</div>')) +
            '  </div>' +
            '</div>'
        );
    }

    function transferRequiredSelectHtml(prefix) {
        return (
            '<label class="stp-lite-label"><i class="ri-car-line me-1"></i>Transfer?</label>' +
            '<select class="form-select form-select-sm ' + prefix + '-transfer-required" data-no-select2="true">' +
            '<option value="No">No</option><option value="Yes">Yes</option></select>'
        );
    }

    /** Same package tiers as standalone guide.js — only show tiers with price > 0. */
    var GUIDE_PACKAGE_TIERS = [
        { hours: 1,  key: 'hourly_price',      label: '1 Hour' },
        { hours: 2,  key: 'two_hour_price',    label: '2 Hours' },
        { hours: 4,  key: 'four_hour_price',   label: '4 Hours' },
        { hours: 6,  key: 'six_hour_price',    label: '6 Hours' },
        { hours: 8,  key: 'eight_hour_price',  label: '8 Hours' },
        { hours: 10, key: 'ten_hour_price',    label: '10 Hours' },
        { hours: 12, key: 'twelve_hour_price', label: '12 Hours' }
    ];

    /** Compact Guide Required expandable fields (attraction / restaurant). */
    function guideExtrasHtml(prefix) {
        return (
            '<div class="stp-lite-extras-block">' +
            '  <div class="stp-lite-opt-card stp-lite-opt-card--compact d-none" data-' + prefix + '-guide-card>' +
            '    <div class="row g-1 align-items-end">' +
            '      <div class="col-6 col-md-5"><label class="stp-lite-label">Guide</label>' +
            '        <select class="form-select form-select-sm ' + prefix + '-guide-select" disabled><option value="">Loading…</option></select></div>' +
            '      <div class="col-6 col-md-3"><label class="stp-lite-label">Package</label>' +
            '        <select class="form-select form-select-sm ' + prefix + '-guide-hours" disabled><option value="">Select guide</option></select></div>' +
            '      <div class="col-6 col-md-4"><label class="stp-lite-label">Pickup time</label>' +
            ampmTimeHtml(prefix + '-guide', '') +
            '      </div>' +
            '    </div>' +
            '  </div>' +
            '</div>'
        );
    }

    function guideRequiredSelectHtml(prefix) {
        return (
            '<label class="stp-lite-label"><i class="ri-user-star-line me-1"></i>Guide?</label>' +
            '<select class="form-select form-select-sm ' + prefix + '-guide-required" data-no-select2="true">' +
            '<option value="No">No</option><option value="Yes">Yes</option></select>'
        );
    }

    function masterFlagYes(v) {
        return v === true || v === 1 || v === '1' || v === 'yes' || v === 'Yes' || v === 'true' || v === 'TRUE';
    }

    function setRequiredSelectLock(el, lock) {
        if (!el) return;
        el.disabled = !!lock;
        el.classList.toggle('stp-lite-master-locked', !!lock);
        if (lock) el.setAttribute('title', 'Set from master data');
        else el.removeAttribute('title');
    }

    function toggleVehicleCountRow(root, prefix, show) {
        var row = root.querySelector('[data-' + prefix + '-vehicle-counts]');
        if (row) row.classList.toggle('d-none', !show);
    }

    /**
     * Copy Guide / Vehicle from bundle attraction or multi-restaurant master.
     * Yes → auto-enable + read-only. No → unlock (reset unless preserve/keepUserChoice).
     */
    function applyMasterGuideVehicle(root, prefix, vehicleYes, guideYes, opts) {
        if (!root || !prefix) return;
        opts = opts || {};
        var tReq = root.querySelector('.' + prefix + '-transfer-required');
        var gReq = root.querySelector('.' + prefix + '-guide-required');
        var vYes = masterFlagYes(vehicleYes);
        var gYes = masterFlagYes(guideYes);

        if (tReq) {
            if (vYes) {
                if (tReq.value !== 'Yes') {
                    tReq.value = 'Yes';
                    if (!opts.silent) tReq.dispatchEvent(new Event('change', { bubbles: true }));
                }
                setRequiredSelectLock(tReq, true);
            } else {
                setRequiredSelectLock(tReq, false);
                if (!opts.preserve && !opts.keepUserChoice && tReq.value !== 'No') {
                    tReq.value = 'No';
                    if (!opts.silent) tReq.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        }
        if (gReq) {
            if (gYes) {
                if (gReq.value !== 'Yes') {
                    gReq.value = 'Yes';
                    if (!opts.silent) gReq.dispatchEvent(new Event('change', { bubbles: true }));
                }
                setRequiredSelectLock(gReq, true);
            } else {
                setRequiredSelectLock(gReq, false);
                if (!opts.preserve && !opts.keepUserChoice && gReq.value !== 'No') {
                    gReq.value = 'No';
                    if (!opts.silent) gReq.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        }
        toggleVehicleCountRow(root, prefix, !!(tReq && tReq.value === 'Yes'));
    }

    function unlockMasterGuideVehicle(root, prefix) {
        applyMasterGuideVehicle(root, prefix, false, false, {});
    }

    /** Packages with sell price > 0 for the selected guide (same as guide.js). */
    function availableGuidePackages(guide) {
        if (!guide) return [];
        return GUIDE_PACKAGE_TIERS
            .map(function (t) {
                var price = parseFloat(guide[t.key]) || 0;
                return { hours: t.hours, label: t.label, price: price };
            })
            .filter(function (p) { return p.price > 0; });
    }

    /** Fill attraction/restaurant guide hours with priced packages only. */
    function fillInlineGuideHours(root, prefix, guide, preferredHours) {
        var hoursEl = root.querySelector('.' + prefix + '-guide-hours');
        if (!hoursEl) return;
        var pkgs = availableGuidePackages(guide);
        var keep = preferredHours != null && preferredHours !== ''
            ? String(preferredHours)
            : String(hoursEl.value || '');
        hoursEl.innerHTML = '<option value="">Select package</option>';
        pkgs.forEach(function (p) {
            var opt = document.createElement('option');
            opt.value = String(p.hours);
            opt.textContent = p.label;
            opt.dataset.price = String(p.price);
            hoursEl.appendChild(opt);
        });
        if (!pkgs.length) {
            var empty = document.createElement('option');
            empty.value = '';
            empty.textContent = guide ? 'No priced packages' : 'Select guide';
            empty.disabled = true;
            hoursEl.appendChild(empty);
            hoursEl.disabled = true;
            hoursEl.value = '';
            return;
        }
        hoursEl.disabled = false;
        if (keep && hoursEl.querySelector('option[value="' + keep.replace(/"/g, '\\"') + '"]')) {
            hoursEl.value = keep;
        } else {
            hoursEl.value = '';
        }
    }

    function findInlineGuide(root, guideId) {
        return (root.__guides || []).find(function (g) {
            return String(g.guide_id || g.id) === String(guideId);
        }) || null;
    }

    function filterTransferVehiclesByType(select, type) {
        if (!select) return;
        var allowed = null;
        if (type === 'Private') allowed = ['1', '3'];
        else if (type === 'Shared') allowed = ['2', '3'];
        Array.from(select.options).forEach(function (opt) {
            if (!opt.value) { opt.hidden = false; opt.disabled = false; return; }
            var sharable = String(opt.dataset.sharable || '');
            var ok = !allowed || !sharable || allowed.indexOf(sharable) !== -1;
            opt.hidden = !ok;
            opt.disabled = !ok;
        });
        if (select.value && select.selectedOptions[0] && select.selectedOptions[0].disabled) {
            select.value = '';
        }
    }

    function parseManualCarCost(costEl) {
        if (!costEl) return 0;
        var raw = String(costEl.value || '').replace(/[^0-9.]/g, '');
        return parseFloat(raw) || 0;
    }

    /**
     * Zone OFF: user-entered car cost — Shared × (adults+children), Private as-is.
     * Zone ON: AJAX zone base (data-ajax-base-price) or vehicle catalog prices.
     * Infants are never charged on transfer.
     */
    function calcInlineTransferPrice(root, prefix, adults, children, infants) {
        var req = root.querySelector('.' + prefix + '-transfer-required');
        if (!req || req.value !== 'Yes') return 0;
        var typeEl = root.querySelector('.' + prefix + '-transfer-type');
        var type = typeEl ? String(typeEl.value).toLowerCase() : '';
        // Infants excluded from shared transfer pricing
        var guests = Math.max(1, (parseInt(adults, 10) || 0) + (parseInt(children, 10) || 0));
        var costEl = root.querySelector('.' + prefix + '-transfer-cost');
        var stay = stayFromPanel(root, prefix) || {};
        var useZone = zoneOnForStay(stay.cityName, stay.country, stay.dmcId);

        if (!useZone) {
            var car = parseManualCarCost(costEl);
            if (car <= 0 || !type) return 0;
            return type === 'shared' ? (car * guests) : car;
        }

        if (!type) return 0;

        // Prefer zone-mapping AJAX base when available
        var ajaxBase = costEl ? parseFloat(costEl.getAttribute('data-ajax-base-price') || '') : NaN;
        if (!isNaN(ajaxBase) && ajaxBase > 0) {
            return type === 'shared' ? (ajaxBase * guests) : ajaxBase;
        }

        var veh = root.querySelector('.' + prefix + '-transfer-vehicle');
        var opt = veh && veh.options[veh.selectedIndex];
        if (!opt || !opt.value) return 0;
        var privateP = parseFloat(opt.dataset.privatePrice) || 0;
        var sharedP = parseFloat(opt.dataset.sharedPrice) || 0;
        // Fallback: some vehicles only have one of the two prices set
        if (type === 'shared') {
            var unit = sharedP > 0 ? sharedP : privateP;
            return unit * guests;
        }
        return privateP > 0 ? privateP : sharedP;
    }

    function refreshTransferCostDisplay(root, prefix, adults, children, infants) {
        var costEl = root.querySelector('.' + prefix + '-transfer-cost');
        if (!costEl) return 0;
        var total = calcInlineTransferPrice(root, prefix, adults, children, infants);
        var hint = root.querySelector('.' + prefix + '-transfer-cost-hint');
        var cur = root.getAttribute('data-currency') || 'SGD';
        var stay = stayFromPanel(root, prefix) || {};
        var useZone = zoneOnForStay(stay.cityName, stay.country, stay.dmcId);

        if (!useZone || costEl.getAttribute('data-zone-off-cost') === '1') {
            if (hint) {
                var car = parseManualCarCost(costEl);
                if (car > 0 && total > 0) {
                    hint.textContent = 'Total transfer: ' + cur + ' ' + total.toFixed(2) + ' (Private = car · Shared = car × pax)';
                } else {
                    hint.textContent = 'Private = car cost · Shared = car cost × pax';
                }
            }
            return total;
        }

        // Zone ON readonly field: show computed total (or clear → placeholder "—")
        if (costEl.getAttribute('data-pricing-loading') === '1') {
            costEl.value = 'Loading…';
            return total;
        }
        if (total > 0) {
            costEl.value = cur + ' ' + total.toFixed(2);
        } else {
            // Keep empty so placeholder "—" shows only when no price yet
            costEl.value = '';
        }
        return total;
    }

    function clearAjaxTransferPrice(costEl) {
        if (!costEl) return;
        costEl.removeAttribute('data-ajax-base-price');
        costEl.removeAttribute('data-ajax-final-price');
        costEl.removeAttribute('data-pricing-loading');
        costEl.removeAttribute('data-pricing-fetch-failed');
        // Zone readonly cost must blank when pickup/vehicle changes (stale IDR xxx)
        if (costEl.getAttribute('data-zone-off-cost') !== '1') {
            costEl.value = '';
        }
    }

    /** Clear transfer cost display + ajax base (pickup / vehicle refresh). */
    function clearTransferCostDisplay(root, prefix) {
        var costEl = root.querySelector('.' + prefix + '-transfer-cost');
        clearAjaxTransferPrice(costEl);
        if (costEl && costEl.getAttribute('data-zone-off-cost') !== '1') {
            costEl.value = '';
        }
    }

    /** Zone ON only: fetch restaurant/attraction zone transfer price into cost field. */
    function fetchInlineTransferZonePrice(root, stay, prefix, guestCountsFn, onDone) {
        var useZone = zoneOnForStay(stay && stay.cityName, stay && stay.country, stay && stay.dmcId);
        if (!useZone) return Promise.resolve(0);
        var costEl = root.querySelector('.' + prefix + '-transfer-cost');
        var typeEl = root.querySelector('.' + prefix + '-transfer-type');
        var veh = root.querySelector('.' + prefix + '-transfer-vehicle');
        var pickup = root.querySelector('.' + prefix + '-transfer-pickup');
        var serviceSel = root.querySelector('.' + prefix + '-select');
        if (!typeEl || !typeEl.value || !veh || !veh.value || !pickup || !pickup.value || !serviceSel || !serviceSel.value) {
            clearAjaxTransferPrice(costEl);
            if (costEl && costEl.getAttribute('data-zone-off-cost') !== '1') costEl.value = '';
            var g0 = typeof guestCountsFn === 'function' ? guestCountsFn() : { adults: 1, children: 0, infants: 0 };
            refreshTransferCostDisplay(root, prefix, g0.adults, g0.children, g0.infants);
            if (typeof onDone === 'function') onDone(0);
            return Promise.resolve(0);
        }
        var pOpt = pickup.options[pickup.selectedIndex];
        var pickupType = (pOpt && pOpt.dataset.type) || 'Hotel';
        var routeKey = prefix === 'restaurant'
            ? 'fetchRestaurantTransferPricing'
            : (prefix === 'attraction' ? 'fetchAttractionTransferPricing' : '');
        var url = (cfg().routes && cfg().routes[routeKey]) || '';
        if (!url) return Promise.resolve(0);

        var q = inv(stay && stay.cityName, stay && stay.country);
        var body = {
            vehicle_id: String(veh.value),
            pickup_location_id: String(pickup.value),
            pickup_location_type: pickupType,
            transfer_type: typeEl.value,
            transfer_way: 'One Way',
            city: q.city || (stay && stay.cityName) || '',
            country: q.country || (stay && stay.country) || '',
            dmc_id: q.dmc_id || (stay && stay.dmcId) || cfg().dmcId || ''
        };
        var entityId = serviceEntityIdForZone(prefix, serviceSel);
        if (prefix === 'restaurant') body.restaurant_id = entityId;
        else body.attraction_id = entityId;

        if (costEl) {
            costEl.setAttribute('data-pricing-loading', '1');
            costEl.value = 'Loading…';
        }

        return fetchJson(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(body)
        }).then(function (data) {
            if (costEl) costEl.removeAttribute('data-pricing-loading');
            if (data && data.success && data.price != null) {
                var base = parseFloat(data.price) || 0;
                if (costEl) {
                    costEl.setAttribute('data-ajax-base-price', String(base));
                    costEl.removeAttribute('data-pricing-fetch-failed');
                }
            } else {
                clearAjaxTransferPrice(costEl);
                if (costEl) costEl.setAttribute('data-pricing-fetch-failed', '1');
            }
            var g = typeof guestCountsFn === 'function' ? guestCountsFn() : { adults: 1, children: 0, infants: 0 };
            var total = refreshTransferCostDisplay(root, prefix, g.adults, g.children, g.infants);
            if (typeof onDone === 'function') onDone(total);
            return total;
        }).catch(function () {
            if (costEl) {
                costEl.removeAttribute('data-pricing-loading');
                clearAjaxTransferPrice(costEl);
                costEl.setAttribute('data-pricing-fetch-failed', '1');
            }
            var g2 = typeof guestCountsFn === 'function' ? guestCountsFn() : { adults: 1, children: 0, infants: 0 };
            var total2 = refreshTransferCostDisplay(root, prefix, g2.adults, g2.children, g2.infants);
            if (typeof onDone === 'function') onDone(total2);
            return total2;
        });
    }

    function collectTransferOptions(root, prefix, adults, children, infants) {
        var req = root.querySelector('.' + prefix + '-transfer-required');
        if (!req || req.value !== 'Yes') return null;
        var typeEl = root.querySelector('.' + prefix + '-transfer-type');
        var veh = root.querySelector('.' + prefix + '-transfer-vehicle');
        var pickup = root.querySelector('.' + prefix + '-transfer-pickup');
        var pickupText = root.querySelector('.' + prefix + '-transfer-pickup-text');
        var opt = veh && veh.options[veh.selectedIndex];
        var pOpt = pickup && pickup.options[pickup.selectedIndex];
        var type = typeEl ? typeEl.value : '';
        var stay = stayFromPanel(root, prefix) || {};
        var useZone = zoneOnForStay(stay.cityName, stay.country, stay.dmcId);
        var cost = calcInlineTransferPrice(root, prefix, adults, children, infants);
        var baseCost = !useZone ? parseManualCarCost(root.querySelector('.' + prefix + '-transfer-cost')) : cost;
        var vehicleDetails = null;
        var adultUnit = 0;
        var childUnit = 0;
        var infantUnit = 0;
        var sharedUnit = 0;
        var privateUnit = 0;
        if (opt && opt.value) {
            sharedUnit = parseFloat(opt.dataset.sharedPrice) || 0;
            privateUnit = parseFloat(opt.dataset.privatePrice) || 0;
            adultUnit = parseFloat(opt.dataset.adultPrice) || sharedUnit;
            childUnit = parseFloat(opt.dataset.childPrice) || sharedUnit;
            infantUnit = 0;
            vehicleDetails = {
                vehicle_id: opt.value,
                vehicle_name: opt.dataset.vehicleName || opt.textContent || '',
                vehicle_type: opt.dataset.vehicleType || '',
                seating_capacity: opt.dataset.seating || '',
                private_price: privateUnit,
                shared_price: sharedUnit,
                adult_price: adultUnit,
                child_price: childUnit,
                infant_price: 0
            };
        }
        if (!useZone) {
            var carUnit = parseManualCarCost(root.querySelector('.' + prefix + '-transfer-cost'));
            if (carUnit > 0) {
                sharedUnit = carUnit;
                privateUnit = carUnit;
                adultUnit = carUnit;
                childUnit = carUnit;
                infantUnit = 0;
            }
        } else if (String(type).toLowerCase() === 'shared' && sharedUnit <= 0) {
            var ajaxBase = parseFloat((root.querySelector('.' + prefix + '-transfer-cost') || {}).getAttribute('data-ajax-base-price') || '');
            if (!isNaN(ajaxBase) && ajaxBase > 0) {
                sharedUnit = ajaxBase;
                if (adultUnit <= 0) adultUnit = ajaxBase;
                if (childUnit <= 0) childUnit = ajaxBase;
                infantUnit = 0;
            }
        }
        var pickupName = '';
        var pickupId = '';
        var pickupType = '';
        if (!useZone && pickupText) {
            pickupName = String(pickupText.value || '').trim();
            pickupId = pickupName;
            pickupType = 'maps';
        } else if (pickup) {
            pickupId = pickup.value || '';
            pickupName = pOpt ? (pOpt.dataset.label || pOpt.textContent || '') : '';
            pickupType = pOpt ? (pOpt.dataset.type || '') : '';
        }
        return {
            transfer_required: true,
            type: type,
            way: 'One Way',
            vehicle_id: opt ? opt.value : '',
            vehicle_details: vehicleDetails,
            cost: cost,
            base_cost: baseCost,
            zone_pricing: useZone ? 1 : 0,
            shared_price: sharedUnit,
            private_price: privateUnit,
            adult_price: adultUnit,
            child_price: childUnit,
            infant_price: 0,
            adults: parseInt(adults, 10) || 0,
            children: parseInt(children, 10) || 0,
            infants: parseInt(infants, 10) || 0,
            pickup_location_id: pickupId,
            pickup_location_name: pickupName,
            pickup_location_type: pickupType,
            pickup_time: readAmPmValue(root, prefix + '-xfer'),
            arrival_vehicle_count: parseInt((root.querySelector('.' + prefix + '-arrival-vehicle-count') || {}).value, 10) || 0,
            departure_vehicle_count: parseInt((root.querySelector('.' + prefix + '-departure-vehicle-count') || {}).value, 10) || 0
        };
    }

    function hourPriceFromGuide(g, hours) {
        hours = parseInt(hours, 10) || 0;
        if (!g || hours < 1) return 0;
        // Prefer named package tiers (same as guide.js / classic form) before hourly × hours
        var named = {
            1: 'hourly_price',
            2: 'two_hour_price',
            4: 'four_hour_price',
            6: 'six_hour_price',
            8: 'eight_hour_price',
            10: 'ten_hour_price',
            12: 'twelve_hour_price'
        };
        if (named[hours] && g[named[hours]] != null && g[named[hours]] !== '') {
            var pkg = parseFloat(g[named[hours]]) || 0;
            if (pkg > 0) return pkg;
        }
        var key = hours + '_hour_price';
        if (g[key] != null && g[key] !== '') {
            var keyed = parseFloat(g[key]) || 0;
            if (keyed > 0) return keyed;
        }
        if (hours === 1 && g.hourly_price) return parseFloat(g.hourly_price) || 0;
        if (g.hourly_price && !named[hours]) return (parseFloat(g.hourly_price) || 0) * hours;
        return 0;
    }

    /**
     * Ticket/meal base from a saved attraction/restaurant row (excludes guide + transfer).
     */
    function ticketOrMealBaseFromRow(row) {
        if (!row || typeof row !== 'object') return 0;
        var td = row.ticket_details || {};
        var hasTicket = row.ticketId != null || row.ticket_details
            || row.adultCount != null || row.AttractionId != null;
        if (hasTicket && (td.adult_price != null || td.child_price != null
            || td.senior_adult_price != null || td.senior_price != null)) {
            return (Number(row.adultCount != null ? row.adultCount : (row.adults || 0)) * (Number(td.adult_price) || 0))
                + (Number(row.childCount != null ? row.childCount : (row.children || 0)) * (Number(td.child_price) || 0))
                + (Number(row.seniorCount || 0) * (Number(td.senior_adult_price != null ? td.senior_adult_price : td.senior_price) || 0));
        }
        var meal0 = (Array.isArray(row.MealDescription) && row.MealDescription[0]) ? row.MealDescription[0] : null;
        var adultP = Number(row.adult_price != null ? row.adult_price : (meal0 && meal0.adult_price)) || 0;
        var childP = Number(row.child_price != null ? row.child_price : (meal0 && meal0.child_price)) || 0;
        if (adultP || childP || row.restaurantId != null || meal0) {
            return (Number(row.adults || 0) * adultP) + (Number(row.children || 0) * childP);
        }
        return 0;
    }

    /**
     * Display/header total for attraction/restaurant rows.
     * Stored totalPrice is ticket/meal-only; guide + transfer live in nested options.
     * Compose for UI / package header. Legacy rows that already stored the full
     * composed total are returned as-is (no double-add).
     */
    function serviceRowDisplayTotal(row) {
        if (!row || typeof row !== 'object') return 0;
        var stored = Number(row.totalPrice != null ? row.totalPrice
            : (row.grand_total != null ? row.grand_total : (row.price != null ? row.price : 0))) || 0;
        var xfer = (row.transfer_options && (row.transfer_options.transfer_required
            || row.transfer_options.vehicle_id || row.transfer_options.cost))
            ? (Number(row.transfer_options.cost) || 0) : 0;
        var guide = (row.guide_options && (row.guide_options.guide_required
            || row.guide_options.guide_id || row.guide_options.total_price))
            ? (Number(row.guide_options.total_price) || 0) : 0;
        if (xfer <= 0 && guide <= 0) return stored;
        var base = ticketOrMealBaseFromRow(row);
        var composed = (base > 0 ? base : 0) + xfer + guide;
        if (composed <= 0) return stored;
        // Legacy: totalPrice already included extras
        if (stored + 0.009 >= composed) return stored;
        // Ticket/meal-only stored — compose for display / package total
        if (base > 0 && Math.abs(stored - base) < 0.02) return composed;
        if (stored < composed) return composed;
        return stored;
    }

    function isNightTime(timeStr, start, end) {
        if (!timeStr || !start || !end) return false;
        function toMin(t) {
            var str = String(t).trim();
            var m = str.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)?$/i);
            if (m) {
                var h = parseInt(m[1], 10) || 0;
                var min = parseInt(m[2], 10) || 0;
                var ap = (m[3] || '').toUpperCase();
                if (ap === 'PM' && h < 12) h += 12;
                if (ap === 'AM' && h === 12) h = 0;
                return h * 60 + min;
            }
            var p = str.split(':');
            return (parseInt(p[0], 10) || 0) * 60 + (parseInt(p[1], 10) || 0);
        }
        var cur = toMin(timeStr);
        var s = toMin(start);
        var e = toMin(end);
        if (s <= e) return cur >= s && cur < e;
        return cur >= s || cur < e;
    }

    function calcInlineGuidePrice(root, prefix) {
        var req = root.querySelector('.' + prefix + '-guide-required');
        if (!req || req.value !== 'Yes') return { total: 0, base: 0, surcharge: 0, hours: 0 };
        var select = root.querySelector('.' + prefix + '-guide-select');
        var hoursEl = root.querySelector('.' + prefix + '-guide-hours');
        if (!select || !select.value) return { total: 0, base: 0, surcharge: 0, hours: 0 };
        var guide = (root.__guides || []).find(function (g) {
            return String(g.guide_id || g.id) === String(select.value);
        }) || {};
        var hours = hoursEl ? hoursEl.value : '';
        if (!hours) return { total: 0, base: 0, surcharge: 0, hours: 0 };
        var base = hourPriceFromGuide(guide, hours);
        var pickup = readAmPmValue(root, prefix + '-guide');
        var surcharge = 0;
        var nightStart = guide.night_start || guide.night_start_time || '';
        var nightEnd = guide.night_end || guide.night_end_time || '';
        if (isNightTime(pickup, nightStart, nightEnd)) {
            surcharge = parseFloat(guide.night_surcharge) || 0;
        }
        return { total: base + surcharge, base: base, surcharge: surcharge, hours: hours };
    }

    function collectGuideOptions(root, prefix) {
        var req = root.querySelector('.' + prefix + '-guide-required');
        if (!req || req.value !== 'Yes') return null;
        var select = root.querySelector('.' + prefix + '-guide-select');
        var opt = select && select.options[select.selectedIndex];
        var hoursEl = root.querySelector('.' + prefix + '-guide-hours');
        var priced = calcInlineGuidePrice(root, prefix);
        return {
            guide_required: true,
            guide_id: select ? select.value : '',
            guide_name: opt ? (opt.dataset.name || opt.textContent) : '',
            language: '',
            pickup_time: readAmPmValue(root, prefix + '-guide'),
            package_hours: hoursEl ? hoursEl.value : '',
            base_price: priced.base,
            hours: parseInt(priced.hours, 10) || 0,
            surcharge: priced.surcharge,
            total_price: priced.total
        };
    }

    function serviceEntityIdForZone(prefix, serviceSel) {
        var raw = String((serviceSel && serviceSel.value) || '');
        if (prefix === 'attraction') return raw.replace(/^bundle_/, '');
        if (prefix === 'restaurant') return raw.replace(/^multi_restaurant_/, '');
        return raw;
    }

    /**
     * Load transfer vehicles.
     * Zone ON: only after pickup + attraction/restaurant selected → fetchVehiclesByZones.
     * Zone OFF: city vehicle catalog.
     */
    function loadTransferVehicles(root, stay, prefix) {
        var select = root.querySelector('.' + prefix + '-transfer-vehicle');
        if (!select) return Promise.resolve();
        var useZone = zoneOnForStay(stay && stay.cityName, stay && stay.country, stay && stay.dmcId);
        var typeEl = root.querySelector('.' + prefix + '-transfer-type');

        function applyList(list) {
            root.__transferVehicles = list || [];
            populateVehicles(select, list || []);
            Array.from(select.options).forEach(function (opt, i) {
                if (!opt.value) return;
                var v = (list || [])[i - 1];
                if (!v) return;
                if (v.sharable != null && v.sharable !== '') opt.dataset.sharable = String(v.sharable);
                else delete opt.dataset.sharable;
            });
            filterTransferVehiclesByType(select, typeEl ? typeEl.value : '');
            select.disabled = false;
        }

        if (!useZone) {
            select.disabled = true;
            select.innerHTML = '<option value="">Loading vehicles…</option>';
            return fetchVehiclesByCity(stay.cityName, stay.country, true)
                .then(function (res) {
                    applyList((res && res.vehicles) || []);
                })
                .catch(function () {
                    select.innerHTML = '<option value="">Error loading</option>';
                    select.disabled = false;
                });
        }

        var pickup = root.querySelector('.' + prefix + '-transfer-pickup');
        var serviceSel = root.querySelector('.' + prefix + '-select');
        var fromId = serviceEntityIdForZone(prefix, serviceSel);
        if (!pickup || !pickup.value || !fromId) {
            select.innerHTML = '<option value="">Select pickup first</option>';
            select.disabled = true;
            root.__transferVehicles = [];
            root.__transferVehiclesLoaded = false;
            clearTransferCostDisplay(root, prefix);
            return Promise.resolve();
        }

        var pOpt = pickup.options[pickup.selectedIndex];
        var q = inv(stay.cityName, stay.country);
        select.disabled = true;
        select.innerHTML = '<option value="">Loading zone vehicles…</option>';
        clearTransferCostDisplay(root, prefix);
        return fetchVehiclesByZones({
            from_zone_id: fromId,
            to_zone_id: pickup.value,
            from_zone_type: prefix,
            to_zone_type: (pOpt && pOpt.dataset.type) || 'Hotel',
            city: q.city,
            country: q.country,
            dmc_id: q.dmc_id || stay.dmcId || '',
            zone_status: 1
        })
            .then(function (res) {
                var list = (res && res.vehicles) || [];
                root.__transferVehiclesLoaded = true;
                if (!list.length) {
                    select.innerHTML = '<option value="">No zone-mapped vehicles</option>';
                    select.disabled = false;
                    root.__transferVehicles = [];
                    clearTransferCostDisplay(root, prefix);
                    return;
                }
                applyList(list);
            })
            .catch(function () {
                select.innerHTML = '<option value="">Error loading</option>';
                select.disabled = false;
                root.__transferVehicles = [];
                clearTransferCostDisplay(root, prefix);
            });
    }

    function loadGuidesForExtras(root, stay, prefix) {
        var select = root.querySelector('.' + prefix + '-guide-select');
        if (!select) return Promise.resolve();
        select.disabled = true;
        select.innerHTML = '<option value="">Loading guides…</option>';
        fillInlineGuideHours(root, prefix, null);
        var q = inv(stay.cityName, stay.country);
        return fetchJsonCached((cfg().routes.fetchGuidesByDmc || '') + '?' + q.qs)
            .then(function (res) {
                var list = (res && res.guides) || [];
                root.__guides = list;
                select.innerHTML = '<option value="">Select guide</option>';
                list.forEach(function (g) {
                    var opt = document.createElement('option');
                    opt.value = g.guide_id || g.id;
                    opt.textContent = g.name || g.guide_name || 'Guide';
                    opt.dataset.name = g.name || g.guide_name || '';
                    select.appendChild(opt);
                });
                select.disabled = false;
                if (select.value) {
                    fillInlineGuideHours(root, prefix, findInlineGuide(root, select.value));
                } else {
                    fillInlineGuideHours(root, prefix, null);
                }
            })
            .catch(function () {
                select.innerHTML = '<option value="">Error loading</option>';
                select.disabled = false;
                fillInlineGuideHours(root, prefix, null);
            });
    }

    function bindTransferExtras(root, stay, prefix, guestCountsFn, onChange) {
        var req = root.querySelector('.' + prefix + '-transfer-required');
        var card = root.querySelector('[data-' + prefix + '-transfer-card]');
        var typeEl = root.querySelector('.' + prefix + '-transfer-type');
        var veh = root.querySelector('.' + prefix + '-transfer-vehicle');
        var costEl = root.querySelector('.' + prefix + '-transfer-cost');
        var mapsMode = !zoneOnForStay(stay && stay.cityName, stay && stay.country, stay && stay.dmcId);
        function guests() {
            return typeof guestCountsFn === 'function' ? guestCountsFn() : { adults: 1, children: 0, infants: 0 };
        }
        function refresh() {
            var g = guests();
            refreshTransferCostDisplay(root, prefix, g.adults, g.children, g.infants);
            if (typeof onChange === 'function') onChange();
        }
        function refreshZonePrice() {
            if (mapsMode) {
                refresh();
                return;
            }
            // Zone ON: load zone mapping price when type+vehicle+pickup+service ready
            fetchInlineTransferZonePrice(root, stay, prefix, guests, function () {
                if (typeof onChange === 'function') onChange();
            });
        }
        function initMaps() {
            if (!mapsMode || !window.StpLiteMaps) return;
            window.StpLiteMaps.initIn(root, stay && stay.country, stay && stay.cityName);
        }
        function reloadZoneVehiclesThenPrice() {
            clearTransferCostDisplay(root, prefix);
            return loadTransferVehicles(root, stay, prefix).then(refreshZonePrice);
        }
        function toggle() {
            var yes = req && req.value === 'Yes';
            if (card) card.classList.toggle('d-none', !yes);
            toggleVehicleCountRow(root, prefix, yes);
            if (yes) {
                if (mapsMode) {
                    if (!root.__transferVehiclesLoaded) {
                        root.__transferVehiclesLoaded = true;
                        loadTransferVehicles(root, stay, prefix).then(refresh);
                    } else {
                        refresh();
                    }
                    initMaps();
                } else if (!root.__transferPickupsLoaded) {
                    root.__transferPickupsLoaded = true;
                    loadTransferPickupLocations(root, stay, prefix).then(function () {
                        // Pickup first — vehicles load after user picks a location
                        if (veh) {
                            veh.innerHTML = '<option value="">Select pickup first</option>';
                            veh.disabled = true;
                        }
                        reloadZoneVehiclesThenPrice();
                    });
                } else {
                    reloadZoneVehiclesThenPrice();
                }
            } else {
                clearAjaxTransferPrice(costEl);
                refresh();
            }
        }
        if (req) req.addEventListener('change', toggle);
        if (typeEl) {
            typeEl.addEventListener('change', function () {
                filterTransferVehiclesByType(veh, typeEl.value);
                clearAjaxTransferPrice(costEl);
                refreshZonePrice();
            });
        }
        if (veh) {
            veh.addEventListener('change', function () {
                clearAjaxTransferPrice(costEl);
                refreshZonePrice();
            });
        }
        if (mapsMode && costEl) {
            costEl.addEventListener('input', refresh);
            costEl.addEventListener('change', refresh);
        }
        var pickup = root.querySelector('.' + prefix + '-transfer-pickup');
        if (pickup) {
            pickup.addEventListener('change', function () {
                // Zone: pickup change → reload mapped vehicles → price
                reloadZoneVehiclesThenPrice();
            });
        }
        var pickupText = root.querySelector('.' + prefix + '-transfer-pickup-text');
        if (pickupText) {
            pickupText.addEventListener('change', function () { if (typeof onChange === 'function') onChange(); });
            pickupText.addEventListener('input', function () { if (typeof onChange === 'function') onChange(); });
        }
        ['.' + prefix + '-arrival-vehicle-count', '.' + prefix + '-departure-vehicle-count'].forEach(function (sel) {
            var countEl = root.querySelector(sel);
            if (!countEl) return;
            countEl.addEventListener('change', function () { if (typeof onChange === 'function') onChange(); });
            countEl.addEventListener('input', function () { if (typeof onChange === 'function') onChange(); });
        });
        var serviceSel = root.querySelector('.' + prefix + '-select');
        if (serviceSel && !mapsMode) {
            serviceSel.addEventListener('change', function () {
                // Attraction/restaurant change → remapping for same pickup
                reloadZoneVehiclesThenPrice();
            });
        }
        root.addEventListener('stp:time-changed', function (e) {
            var ampm = e.target && e.target.closest ? e.target.closest('[data-ampm-root="' + prefix + '-xfer"]') : null;
            if (ampm && typeof onChange === 'function') onChange();
        });
        toggle();
    }

    function bindGuideExtras(root, stay, prefix, onChange) {
        var req = root.querySelector('.' + prefix + '-guide-required');
        var card = root.querySelector('[data-' + prefix + '-guide-card]');
        var guideSelect = root.querySelector('.' + prefix + '-guide-select');
        function onGuidePicked() {
            var g = guideSelect && guideSelect.value ? findInlineGuide(root, guideSelect.value) : null;
            fillInlineGuideHours(root, prefix, g);
            if (typeof onChange === 'function') onChange();
        }
        function toggle() {
            var yes = req && req.value === 'Yes';
            if (card) card.classList.toggle('d-none', !yes);
            if (yes && !root.__guidesLoaded) {
                root.__guidesLoaded = true;
                loadGuidesForExtras(root, stay, prefix).then(function () {
                    if (typeof onChange === 'function') onChange();
                });
            } else if (typeof onChange === 'function') {
                onChange();
            }
            if (!yes) fillInlineGuideHours(root, prefix, null);
        }
        if (req) req.addEventListener('change', toggle);
        if (guideSelect) guideSelect.addEventListener('change', onGuidePicked);
        var hoursEl = root.querySelector('.' + prefix + '-guide-hours');
        if (hoursEl) {
            hoursEl.addEventListener('change', function () {
                if (typeof onChange === 'function') onChange();
            });
        }
        root.addEventListener('stp:time-changed', function (e) {
            var ampm = e.target && e.target.closest ? e.target.closest('[data-ampm-root="' + prefix + '-guide"]') : null;
            if (ampm && typeof onChange === 'function') onChange();
        });
        toggle();
    }

    function hydrateTransferExtras(root, prefix, transferOptions, stay) {
        var req = root.querySelector('.' + prefix + '-transfer-required');
        var card = root.querySelector('[data-' + prefix + '-transfer-card]');
        if (!transferOptions || !transferOptions.transfer_required) {
            if (req) req.value = 'No';
            if (card) card.classList.add('d-none');
            toggleVehicleCountRow(root, prefix, false);
            return Promise.resolve();
        }
        if (req) req.value = 'Yes';
        if (card) card.classList.remove('d-none');
        toggleVehicleCountRow(root, prefix, true);
        var arrCount = root.querySelector('.' + prefix + '-arrival-vehicle-count');
        var depCount = root.querySelector('.' + prefix + '-departure-vehicle-count');
        if (arrCount) arrCount.value = String(transferOptions.arrival_vehicle_count != null ? transferOptions.arrival_vehicle_count : 0);
        if (depCount) depCount.value = String(transferOptions.departure_vehicle_count != null ? transferOptions.departure_vehicle_count : 0);
        var typeEl = root.querySelector('.' + prefix + '-transfer-type');
        if (typeEl) typeEl.value = transferOptions.type || '';
        setAmPmValue(root, prefix + '-xfer', transferOptions.pickup_time || '');
        var stayObj = stay || stayFromPanel(root, prefix) || {};
        var pickupId = transferOptions.pickup_location_id || '';
        var mapsMode = !zoneOnForStay(stayObj.cityName, stayObj.country, stayObj.dmcId);

        function afterVehicles() {
            var veh = root.querySelector('.' + prefix + '-transfer-vehicle');
            selectVehicleValue(veh, transferOptions.vehicle_id, (transferOptions.vehicle_details && transferOptions.vehicle_details.vehicle_name) || '');
            filterTransferVehiclesByType(veh, typeEl ? typeEl.value : '');
            var costEl = root.querySelector('.' + prefix + '-transfer-cost');
            if (mapsMode && costEl) {
                var base = transferOptions.base_cost != null
                    ? Number(transferOptions.base_cost)
                    : Number(transferOptions.cost || 0);
                if (String(transferOptions.type || '').toLowerCase() === 'shared' && transferOptions.base_cost == null && base > 0) {
                    costEl.value = base > 0 ? String(base) : '';
                } else {
                    costEl.value = base > 0 ? String(base) : '';
                }
            }
            refreshTransferCostDisplay(root, prefix, 1, 0, 0);
            if (!mapsMode) {
                fetchInlineTransferZonePrice(root, stayObj, prefix, function () {
                    return { adults: 1, children: 0, infants: 0 };
                });
            }
        }

        if (mapsMode) {
            var pickupText = root.querySelector('.' + prefix + '-transfer-pickup-text');
            if (pickupText) {
                pickupText.value = transferOptions.pickup_location_name || transferOptions.pickup_location_id || '';
            }
            if (window.StpLiteMaps) {
                window.StpLiteMaps.initIn(root, stayObj.country, stayObj.cityName);
            }
            var vehP = root.__transferVehiclesLoaded
                ? Promise.resolve(afterVehicles())
                : loadTransferVehicles(root, stayObj, prefix).then(function () {
                    root.__transferVehiclesLoaded = true;
                    afterVehicles();
                });
            return vehP;
        }

        // Zone ON: pickup list first, then zone-mapped vehicles for that pickup
        return loadTransferPickupLocations(root, stayObj, prefix, pickupId).then(function () {
            root.__transferPickupsLoaded = true;
            return loadTransferVehicles(root, stayObj, prefix).then(afterVehicles);
        });
    }

    function hydrateGuideExtras(root, prefix, guideOptions, stay) {
        var req = root.querySelector('.' + prefix + '-guide-required');
        var card = root.querySelector('[data-' + prefix + '-guide-card]');
        if (!guideOptions || !guideOptions.guide_required) {
            if (req) req.value = 'No';
            if (card) card.classList.add('d-none');
            fillInlineGuideHours(root, prefix, null);
            return Promise.resolve();
        }
        if (req) req.value = 'Yes';
        if (card) card.classList.remove('d-none');
        setAmPmValue(root, prefix + '-guide', guideOptions.pickup_time || '');
        var preferHours = guideOptions.package_hours || guideOptions.hours || '';
        function afterGuides() {
            var select = root.querySelector('.' + prefix + '-guide-select');
            if (select && guideOptions.guide_id) select.value = String(guideOptions.guide_id);
            var guide = findInlineGuide(root, guideOptions.guide_id);
            fillInlineGuideHours(root, prefix, guide, preferHours);
        }
        if (!root.__guidesLoaded) {
            root.__guidesLoaded = true;
            return loadGuidesForExtras(root, stay || stayFromPanel(root, prefix) || {}, prefix).then(afterGuides);
        }
        afterGuides();
        return Promise.resolve();
    }

    /**
     * Sum chunk totals and update accordion header total if present;
     * always dispatches stp:service-chunk-changed.
     */
    function updateServiceHeaderTotal(root, serviceKey) {
        if (!root) return 0;
        var key = serviceKey || '';
        if (!key && root.closest) {
            var p = root.closest('[data-service]');
            if (p) key = p.getAttribute('data-service') || '';
        }
        var chunkMap = {
            hotel: '.hotel_data_chunk',
            arrival: '.entry_port_data_chunk',
            departure: '.exit_port_data_chunk',
            transport: '.transport_data_chunk',
            attraction: '.attraction_data_chunk',
            guide: '.guide_data_chunk',
            restaurant: '.restaurant_data_chunk',
            miscellaneous: '.miscellaneous_data_chunk'
        };
        var chunk = root.querySelector(chunkMap[key] || ('.' + key + '_data_chunk')) ||
            root.querySelector('.hotel_data_chunk, .entry_port_data_chunk, .exit_port_data_chunk, .transport_data_chunk, .attraction_data_chunk, .guide_data_chunk, .restaurant_data_chunk, .miscellaneous_data_chunk');
        var rows = [];
        try { rows = JSON.parse((chunk && chunk.value) || '[]') || []; } catch (e) { rows = []; }
        if (!Array.isArray(rows)) rows = [];
        var total = rows.reduce(function (sum, r) {
            return sum + serviceRowDisplayTotal(r);
        }, 0);
        var curHost = root.closest ? root.closest('[data-currency], [data-service]') : null;
        var currency = root.getAttribute('data-currency') ||
            (curHost && curHost.getAttribute('data-currency')) ||
            cfg().dmcCurrency || 'SGD';
        var panel = root.closest ? root.closest('[data-service]') : null;
        var item = panel && panel.closest ? panel.closest('.stp-lite-service-item') : null;
        var header = item && item.querySelector ? item.querySelector('.stp-lite-service-header') : null;
        if (header) {
            var el = header.querySelector('.stp-lite-svc-header-total');
            if (!el) {
                el = document.createElement('span');
                el.className = 'stp-lite-svc-header-total';
                var titles = header.querySelector('.stp-lite-svc-titles');
                if (titles && titles.parentNode) {
                    titles.parentNode.insertBefore(el, titles.nextSibling);
                } else {
                    header.appendChild(el);
                }
            }
            if (rows.length) {
                el.innerHTML = '<span class="stp-lite-svc-header-total-count">' + rows.length + '</span>' +
                    '<span class="stp-lite-svc-header-total-amt">' + esc(currency) + ' ' + total.toFixed(2) + '</span>';
                el.classList.remove('d-none');
            } else {
                el.innerHTML = '';
                el.classList.add('d-none');
            }
        }
        try {
            root.dispatchEvent(new CustomEvent('stp:service-chunk-changed', {
                bubbles: true,
                detail: { service: key, total: total, count: rows.length, currency: currency, root: root }
            }));
        } catch (e) { /* ignore */ }
        var section = root.closest ? root.closest('.stp-lite-country-section') : null;
        if (section) updateStaySectionHeaderTotal(section);
        return total;
    }

    /**
     * Sum all service chunks inside a city/stay section and show total on the city header.
     */
    function updateStaySectionHeaderTotal(section) {
        if (!section || !section.querySelector) return 0;
        var chunkSel = [
            '.hotel_data_chunk',
            '.entry_port_data_chunk',
            '.exit_port_data_chunk',
            '.transport_data_chunk',
            '.attraction_data_chunk',
            '.guide_data_chunk',
            '.restaurant_data_chunk',
            '.miscellaneous_data_chunk'
        ].join(', ');
        var total = 0;
        var count = 0;
        section.querySelectorAll(chunkSel).forEach(function (chunk) {
            var rows = [];
            try { rows = JSON.parse(chunk.value || '[]') || []; } catch (e) { rows = []; }
            if (!Array.isArray(rows)) return;
            rows.forEach(function (r) {
                total += serviceRowDisplayTotal(r);
                count += 1;
            });
        });
        var currency = section.getAttribute('data-currency')
            || (cfg().dmcCurrency || 'SGD');
        var header = section.querySelector('.stp-lite-country-header');
        if (!header) return total;
        var el = header.querySelector('[data-city-header-total]');
        if (!el) {
            el = document.createElement('span');
            el.className = 'stp-lite-city-header-total d-none';
            el.setAttribute('data-city-header-total', '1');
            var badge = header.querySelector('.stp-lite-currency-badge');
            if (badge && badge.parentNode) {
                badge.parentNode.insertBefore(el, badge);
            } else {
                header.appendChild(el);
            }
        }
        if (count > 0) {
            el.innerHTML = '<span class="stp-lite-city-header-total-count">' + count + '</span>' +
                '<span class="stp-lite-city-header-total-amt">' + esc(currency) + ' ' + total.toFixed(2) + '</span>';
            el.classList.remove('d-none');
        } else {
            el.innerHTML = '';
            el.classList.add('d-none');
        }
        return total;
    }

    function refreshAllStaySectionTotals(scope) {
        var root = scope || document;
        root.querySelectorAll('.stp-lite-country-section').forEach(function (section) {
            updateStaySectionHeaderTotal(section);
        });
    }

    /** Reset a select/input; sync Select2 if present so UI clears after Add. */
    function clearSelectOrInput(el, emptyValue) {
        if (!el) return;
        var next = emptyValue != null ? String(emptyValue) : '';
        if (el.tagName === 'SELECT') {
            if (next === '' && el.options.length) {
                // Prefer blank/placeholder option when present
                var blankIdx = -1;
                for (var i = 0; i < el.options.length; i++) {
                    if (!String(el.options[i].value || '').trim()) {
                        blankIdx = i;
                        break;
                    }
                }
                el.selectedIndex = blankIdx >= 0 ? blankIdx : 0;
                if (blankIdx < 0 && !el.options[0].value) {
                    /* already on placeholder */
                } else if (blankIdx < 0) {
                    el.value = el.options[0] ? el.options[0].value : '';
                }
            } else {
                el.value = next;
            }
            if (window.jQuery && jQuery(el).data('select2')) {
                jQuery(el).val(el.value || null).trigger('change');
            } else {
                try { el.dispatchEvent(new Event('change', { bubbles: true })); } catch (e) { /* ignore */ }
            }
        } else {
            el.value = next;
            try { el.dispatchEvent(new Event('input', { bubbles: true })); } catch (e2) { /* ignore */ }
        }
    }

    function hotelRowMeta(h) {
        var hd = (h && h.hotelDetails) || {};
        return {
            id: String(h.hotel_unique_id || h.hotel_id || hd.hotel_id || '').trim(),
            name: String(h.hotel_name || hd.hotel_name || hd.name || '').trim(),
            city: String(h.city || hd.city || hd.location || '').trim().toLowerCase(),
            planIndex: h.plan_index != null ? String(h.plan_index) : '',
            isReturn: !!(h.is_return || h.isReturn)
        };
    }

    function hotelsForStay(hotels, stay) {
        var stayCity = String((stay && stay.cityName) || '').trim().toLowerCase();
        var stayPlan = stay && stay.planIndex != null ? String(stay.planIndex) : '';
        var stayReturn = !!(stay && stay.isReturn);
        return (hotels || []).filter(function (h) {
            var m = hotelRowMeta(h);
            if (!m.id && !m.name) return false;
            if (stayCity && m.city && m.city !== stayCity) return false;
            if (stayPlan !== '' && m.planIndex !== '' && m.planIndex !== stayPlan) return false;
            if (m.planIndex === '' && stayCity) {
                // Fall back: match return flag when plan_index missing
                if (!!m.isReturn !== stayReturn && (h.is_return != null || h.isReturn != null)) {
                    return false;
                }
            }
            return true;
        });
    }

    function setLocationToHotel(selectEl, textEl, hotelId, hotelName) {
        var applied = false;
        if (selectEl && selectEl.tagName === 'SELECT' && selectEl.options && selectEl.options.length) {
            var matchVal = '';
            var idStr = String(hotelId || '');
            var nameLower = String(hotelName || '').toLowerCase();
            for (var i = 0; i < selectEl.options.length; i++) {
                var opt = selectEl.options[i];
                if (!opt.value) continue;
                var label = String(opt.dataset.label || opt.textContent || '').trim();
                if ((idStr && String(opt.value) === idStr) ||
                    (idStr && String(opt.dataset.zoneId || '') === idStr) ||
                    (nameLower && label.toLowerCase() === nameLower) ||
                    (nameLower && label.toLowerCase().indexOf(nameLower) === 0)) {
                    matchVal = opt.value;
                    break;
                }
            }
            if (matchVal && String(selectEl.value) !== String(matchVal)) {
                selectEl.__stpAutoHotel = true;
                clearSelectOrInput(selectEl, matchVal);
                selectEl.__stpAutoHotel = false;
                applied = true;
            } else if (matchVal) {
                applied = true;
            }
        }
        if (textEl && hotelName) {
            if (String(textEl.value || '').trim() !== hotelName) {
                textEl.__stpAutoHotel = true;
                textEl.value = hotelName;
                try { textEl.dispatchEvent(new Event('change', { bubbles: true })); } catch (e) { /* ignore */ }
                textEl.__stpAutoHotel = false;
                applied = true;
            } else {
                applied = true;
            }
        }
        return applied;
    }

    /**
     * When hotels are added: Arrival dropoff = first hotel of stay;
     * Departure pickup = last hotel of stay (same hotel when only one).
     */
    function applyBookedHotelsToArrivalDeparture(hotels) {
        var list = Array.isArray(hotels) ? hotels : [];
        try {
            if (!list.length) {
                var hidden = document.getElementById('hotel_data');
                if (hidden && hidden.value) {
                    var parsed = JSON.parse(hidden.value || '[]');
                    if (Array.isArray(parsed)) list = parsed;
                }
            }
        } catch (e) { /* ignore */ }

        document.querySelectorAll('.stp-lite-arrival').forEach(function (root) {
            if (root.__hydrating) return;
            var stay = stayFromPanel(root, 'arrival');
            var matched = hotelsForStay(list, stay);
            if (!matched.length) return;
            var first = hotelRowMeta(matched[0]);
            setLocationToHotel(
                root.querySelector('.arrival-dropoff'),
                root.querySelector('.arrival-dropoff-text'),
                first.id,
                first.name
            );
        });

        document.querySelectorAll('.stp-lite-departure').forEach(function (root) {
            if (root.__hydrating) return;
            var stay = stayFromPanel(root, 'departure');
            var matched = hotelsForStay(list, stay);
            if (!matched.length) return;
            var last = hotelRowMeta(matched[matched.length - 1]);
            setLocationToHotel(
                root.querySelector('.departure-pickup'),
                root.querySelector('.departure-pickup-text'),
                last.id,
                last.name
            );
        });
    }

    if (!window.__stpLiteHotelTransferBound) {
        window.__stpLiteHotelTransferBound = true;
        document.addEventListener('stp:hotel-data-changed', function (ev) {
            var hotels = (ev && ev.detail && ev.detail.hotels) || [];
            applyBookedHotelsToArrivalDeparture(hotels);
        });
    }

    /** Reset Transfer?/Guide? extras after Add/Update so user can search again. */
    function resetTransferGuideExtras(root, prefix) {
        if (!root || !prefix) return;
        var tReq = root.querySelector('.' + prefix + '-transfer-required');
        var gReq = root.querySelector('.' + prefix + '-guide-required');
        if (tReq) {
            setRequiredSelectLock(tReq, false);
            tReq.value = 'No';
            tReq.dispatchEvent(new Event('change', { bubbles: true }));
        }
        if (gReq) {
            setRequiredSelectLock(gReq, false);
            gReq.value = 'No';
            gReq.dispatchEvent(new Event('change', { bubbles: true }));
        }
        var tCard = root.querySelector('[data-' + prefix + '-transfer-card]');
        var gCard = root.querySelector('[data-' + prefix + '-guide-card]');
        if (tCard) tCard.classList.add('d-none');
        if (gCard) gCard.classList.add('d-none');
        var veh = root.querySelector('.' + prefix + '-transfer-vehicle');
        if (veh) {
            veh.innerHTML = '<option value="">Select type</option>';
            veh.disabled = true;
        }
        var pickup = root.querySelector('.' + prefix + '-transfer-pickup');
        if (pickup) {
            pickup.innerHTML = '<option value="">Select pickup</option>';
            pickup.disabled = true;
        }
        var pickupText = root.querySelector('.' + prefix + '-transfer-pickup-text');
        if (pickupText) pickupText.value = '';
        var cost = root.querySelector('.' + prefix + '-transfer-cost');
        if (cost) {
            cost.value = '';
            cost.removeAttribute('data-ajax-base-price');
            cost.removeAttribute('data-ajax-final-price');
            cost.removeAttribute('data-pricing-loading');
            cost.removeAttribute('data-pricing-fetch-failed');
        }
        var typeEl = root.querySelector('.' + prefix + '-transfer-type');
        if (typeEl) typeEl.value = '';
        var arrCount = root.querySelector('.' + prefix + '-arrival-vehicle-count');
        var depCount = root.querySelector('.' + prefix + '-departure-vehicle-count');
        if (arrCount) arrCount.value = '0';
        if (depCount) depCount.value = '0';
        toggleVehicleCountRow(root, prefix, false);
        setAmPmValue(root, prefix + '-xfer', '');
        setAmPmValue(root, prefix + '-guide', '');
        var guideSel = root.querySelector('.' + prefix + '-guide-select');
        if (guideSel) guideSel.selectedIndex = 0;
        var hours = root.querySelector('.' + prefix + '-guide-hours');
        if (hours) {
            hours.innerHTML = '<option value="">Select guide</option>';
            hours.disabled = true;
            hours.value = '';
        }
        root.__transferVehiclesLoaded = false;
        root.__transferPickupsLoaded = false;
    }

    /**
     * Zone-off car-cost pricing for arrival/departure/transport Get Price.
     * Shared = car × (adults+children); infants never charged. Private = car as-is.
     */
    function calcManualCarTotal(carCost, serviceType, adults, children, infants) {
        var car = parseFloat(carCost) || 0;
        if (car <= 0) return { total: 0, unit: 0, mode: String(serviceType || 'private').toLowerCase(), base: 0 };
        var type = String(serviceType || 'private').toLowerCase();
        var a = parseInt(adults, 10) || 0;
        var c = parseInt(children, 10) || 0;
        var i = parseInt(infants, 10) || 0;
        var guests = Math.max(1, a + c);
        var total = type === 'shared' ? (car * guests) : car;
        return {
            total: total,
            unit: car,
            base: car,
            mode: type,
            adultUnit: type === 'shared' ? car : 0,
            childUnit: type === 'shared' ? car : 0,
            infantUnit: 0,
            adults: a,
            children: c,
            infants: i
        };
    }

    function confirmRemoveService() {
        return window.confirm('Are you sure you want to remove this service?');
    }

    /**
     * Match classic orderSelect*: enquiry for New Enquiry / Prospect / Tentative;
     * booking for Definite / Actual / Confirmed / etc. Edit must not force enquiry.
     */
    function defaultBookingType() {
        var status = String(
            (window.STP_LITE_EDIT && window.STP_LITE_EDIT.tourStatus) ||
            (cfg().edit && cfg().edit.tourStatus) ||
            (cfg().tourStatus) ||
            ''
        ).trim();
        if (['New Enquiry', 'Prospect', 'Tentative'].indexOf(status) >= 0) {
            return 'enquiry';
        }
        if (status) {
            return 'booking';
        }
        return 'enquiry';
    }

    /**
     * Prefer tour-status bookingType. Do not keep a stale "enquiry" when the tour
     * is already Confirmed / Definite / Actual.
     */
    function resolveRowBookingType(existing) {
        var fromTour = defaultBookingType();
        if (fromTour === 'booking') {
            return 'booking';
        }
        var bt = existing && existing.bookingType != null
            ? String(existing.bookingType).toLowerCase().trim()
            : '';
        if (bt === 'enquiry' || bt === 'booking') {
            return bt;
        }
        return fromTour;
    }

    window.StpLiteTransportShared = {
        cfg: cfg,
        esc: esc,
        zoneOn: zoneOn,
        zoneOnForDmc: zoneOnForDmc,
        zoneOnForStay: zoneOnForStay,
        inv: inv,
        tourGuests: tourGuests,
        fetchJson: fetchJson,
        fetchJsonCached: fetchJsonCached,
        fetchZones: fetchZones,
        fetchZoneLocations: fetchZoneLocations,
        fetchPorts: fetchPorts,
        fetchHotels: fetchHotels,
        fetchAttractions: fetchAttractions,
        fetchRestaurants: fetchRestaurants,
        fetchVehiclesByZones: fetchVehiclesByZones,
        fetchVehiclesByCity: fetchVehiclesByCity,
        fillSelect: fillSelect,
        populateVehicles: populateVehicles,
        selectVehicleValue: selectVehicleValue,
        loadTransferPickupLocations: loadTransferPickupLocations,
        vehicleOptionMapper: vehicleOptionMapper,
        calcVehiclePrice: calcVehiclePrice,
        calcHourlyPrice: calcHourlyPrice,
        packageHourlySellPrice: packageHourlySellPrice,
        syncHiddenJson: syncHiddenJson,
        stayFromPanel: stayFromPanel,
        cityLabel: cityLabel,
        autoSupplement: autoSupplement,
        pricePanelHtml: pricePanelHtml,
        transferExtrasHtml: transferExtrasHtml,
        transferRequiredSelectHtml: transferRequiredSelectHtml,
        guideExtrasHtml: guideExtrasHtml,
        guideRequiredSelectHtml: guideRequiredSelectHtml,
        masterFlagYes: masterFlagYes,
        applyMasterGuideVehicle: applyMasterGuideVehicle,
        unlockMasterGuideVehicle: unlockMasterGuideVehicle,
        filterTransferVehiclesByType: filterTransferVehiclesByType,
        calcInlineTransferPrice: calcInlineTransferPrice,
        refreshTransferCostDisplay: refreshTransferCostDisplay,
        collectTransferOptions: collectTransferOptions,
        hourPriceFromGuide: hourPriceFromGuide,
        availableGuidePackages: availableGuidePackages,
        fillInlineGuideHours: fillInlineGuideHours,
        confirmRemoveService: confirmRemoveService,
        defaultBookingType: defaultBookingType,
        resolveRowBookingType: resolveRowBookingType,
        isNightTime: isNightTime,
        calcInlineGuidePrice: calcInlineGuidePrice,
        collectGuideOptions: collectGuideOptions,
        loadTransferVehicles: loadTransferVehicles,
        loadGuidesForExtras: loadGuidesForExtras,
        bindTransferExtras: bindTransferExtras,
        bindGuideExtras: bindGuideExtras,
        hydrateTransferExtras: hydrateTransferExtras,
        hydrateGuideExtras: hydrateGuideExtras,
        ticketOrMealBaseFromRow: ticketOrMealBaseFromRow,
        serviceRowDisplayTotal: serviceRowDisplayTotal,
        updateServiceHeaderTotal: updateServiceHeaderTotal,
        updateStaySectionHeaderTotal: updateStaySectionHeaderTotal,
        refreshAllStaySectionTotals: refreshAllStaySectionTotals,
        ampmTimeHtml: ampmTimeHtml,
        formatAmPmInput: formatAmPmInput,
        syncAmPmHidden: syncAmPmHidden,
        readAmPmValue: readAmPmValue,
        setAmPmValue: setAmPmValue,
        bindAmPm: bindAmPm,
        clampDateInput: clampDateInput,
        bindStayDate: bindStayDate,
        bindGuestCaps: bindGuestCaps,
        addedTableActions: addedTableActions,
        editingMarkHtml: editingMarkHtml,
        showPriceBreakdownModal: showPriceBreakdownModal,
        priceFormulaRowHtml: priceFormulaRowHtml,
        paxPriceLinesHtml: paxPriceLinesHtml,
        vehiclePriceDetailHtml: vehiclePriceDetailHtml,
        transferPriceDetailHtml: transferPriceDetailHtml,
        clearSelectOrInput: clearSelectOrInput,
        resetTransferGuideExtras: resetTransferGuideExtras,
        applyBookedHotelsToArrivalDeparture: applyBookedHotelsToArrivalDeparture,
        calcManualCarTotal: calcManualCarTotal,
        parseManualCarCost: parseManualCarCost,
        fetchInlineTransferZonePrice: fetchInlineTransferZonePrice
    };
})(window);
/* === END transport-shared.js === */
