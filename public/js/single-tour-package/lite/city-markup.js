/* === STP LITE: city-markup.js ===
 * Pricing by city — Hotel markup / Other markup / Discount → currency_markups
 * === */
(function (window, document) {
    'use strict';

    var store = {}; // city → entry

    function esc(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function resolveCurrency(city, country) {
        if (typeof window.resolveCurrencyForCityName === 'function') {
            return window.resolveCurrencyForCityName(city, country) || '';
        }
        return (window.STP_LITE_CONFIG && window.STP_LITE_CONFIG.dmcCurrency) || 'SGD';
    }

    function cityTargets() {
        var items = [];
        if (window.StpLiteTourDetails && typeof window.StpLiteTourDetails.getTourCityItems === 'function') {
            items = window.StpLiteTourDetails.getTourCityItems() || [];
        }
        var out = [];
        var seen = {};
        items.forEach(function (it) {
            var city = String(it.name || '').trim();
            if (!city || seen[city.toLowerCase()]) return;
            seen[city.toLowerCase()] = true;
            var country = String(it.country || '').trim();
            out.push({
                city: city,
                country: country,
                currency: resolveCurrency(city, country)
            });
        });
        return out;
    }

    function emptyEntry(t) {
        return {
            city: t.city || '',
            country: t.country || '',
            currency: t.currency || '',
            markup_type: '',
            hotel_markup: 0,
            other_markup: 0,
            markup_value: 0,
            discount_type: '',
            discount_value: 0
        };
    }

    function citiesMatch(a, b) {
        var left = String(a || '').trim().toLowerCase().replace(/\s+/g, ' ');
        var right = String(b || '').trim().toLowerCase().replace(/\s+/g, ' ');
        if (!left || !right) return false;
        return left === right || left.indexOf(right) !== -1 || right.indexOf(left) !== -1;
    }

    function parseChunk(el) {
        try {
            var rows = JSON.parse((el && el.value) || '[]');
            return Array.isArray(rows) ? rows : [];
        } catch (e) {
            return [];
        }
    }

    function rowBelongsToCity(row, cityName) {
        if (!row || typeof row !== 'object') return false;
        var candidates = [
            row.city, row.destination, row.hotelCity, row.hotel_city,
            row.AttractionCity, row.restaurantCity, row.location, row.city_name
        ];
        for (var i = 0; i < candidates.length; i++) {
            if (citiesMatch(candidates[i], cityName)) return true;
        }
        return false;
    }

    /** Per city: has hotel booking? has any non-hotel service? */
    function getCityServiceFlags(cityName) {
        var hasHotel = false;
        var hasOther = false;
        var hotelSelectors = ['.hotel_data_chunk', '#hotel_data'];
        var otherSelectors = [
            '.attraction_data_chunk', '#attraction_data',
            '.restaurant_data_chunk', '#restaurant_data',
            '.guide_data_chunk', '#guide_data',
            '.transport_data_chunk', '#transport_data',
            '.entry_port_data_chunk', '#entry_port_data',
            '.exit_port_data_chunk', '#exit_port_data',
            '.miscellaneous_data_chunk', '#miscellaneous_data'
        ];

        document.querySelectorAll('.stp-lite-country-section').forEach(function (section) {
            var sectionCity = String(section.getAttribute('data-city-name') || '').trim();
            var sectionMatch = citiesMatch(sectionCity, cityName);

            hotelSelectors.forEach(function (sel) {
                section.querySelectorAll(sel).forEach(function (el) {
                    var rows = parseChunk(el);
                    if (!rows.length) return;
                    if (sectionMatch || rows.some(function (r) { return rowBelongsToCity(r, cityName); })) {
                        hasHotel = true;
                    }
                });
            });
            otherSelectors.forEach(function (sel) {
                section.querySelectorAll(sel).forEach(function (el) {
                    var rows = parseChunk(el);
                    if (!rows.length) return;
                    if (sectionMatch || rows.some(function (r) { return rowBelongsToCity(r, cityName); })) {
                        hasOther = true;
                    }
                });
            });
        });

        // Form-level hiddens (merged) — match by row.city
        if (!hasHotel) {
            var hotelHidden = document.getElementById('hotel_data');
            parseChunk(hotelHidden).forEach(function (r) {
                if (rowBelongsToCity(r, cityName)) hasHotel = true;
            });
        }
        if (!hasOther) {
            ['attraction_data', 'restaurant_data', 'guide_data', 'transport_data',
                'entry_port_data', 'exit_port_data', 'miscellaneous_data'].forEach(function (id) {
                parseChunk(document.getElementById(id)).forEach(function (r) {
                    if (rowBelongsToCity(r, cityName)) hasOther = true;
                });
            });
        }

        return { hasHotel: hasHotel, hasOther: hasOther };
    }

    function lockMarkupInput(inp, enabled, locked, lockTitle, restoreValue) {
        if (!inp) return;
        if (!enabled) {
            inp.disabled = true;
            inp.readOnly = false;
            inp.value = 0;
            inp.title = '';
            inp.classList.remove('is-service-locked');
            return;
        }
        inp.disabled = false;
        if (locked) {
            inp.readOnly = true;
            inp.value = 0;
            inp.title = lockTitle || '';
            inp.classList.add('is-service-locked');
        } else {
            inp.readOnly = false;
            inp.title = '';
            inp.classList.remove('is-service-locked');
            if (restoreValue != null) {
                inp.value = Number(restoreValue) || 0;
            }
        }
    }

    function applyServiceLocks(tr, cityName, markupTypeOn) {
        if (!tr) return { hasHotel: false, hasOther: false };
        var flags = getCityServiceFlags(cityName);
        var prev = store[cityName] || {};
        var hotel = tr.querySelector('.city-hotel-markup');
        var other = tr.querySelector('.city-other-markup');
        tr.setAttribute('data-has-hotel', flags.hasHotel ? '1' : '0');
        tr.setAttribute('data-has-other', flags.hasOther ? '1' : '0');
        lockMarkupInput(
            hotel,
            !!markupTypeOn,
            !flags.hasHotel,
            'Add a hotel for this city to enable accommodation markup',
            flags.hasHotel ? (prev.hotel_markup != null ? prev.hotel_markup : hotel && hotel.value) : 0
        );
        lockMarkupInput(
            other,
            !!markupTypeOn,
            !flags.hasOther,
            'Add another service for this city to enable other markup',
            flags.hasOther ? (prev.other_markup != null ? prev.other_markup : other && other.value) : 0
        );
        return flags;
    }

    function refreshServiceLocks() {
        var body = document.getElementById('enquiryProCityMarkupBody');
        if (!body) return;
        Array.prototype.forEach.call(body.querySelectorAll('tr[data-city]'), function (tr) {
            var city = String(tr.getAttribute('data-city') || '').trim();
            if (!city) return;
            var mt = tr.querySelector('.city-markup-type');
            applyServiceLocks(tr, city, !!(mt && mt.value));
        });
        syncHidden();
    }

    function readRowsIntoStore() {
        var body = document.getElementById('enquiryProCityMarkupBody');
        if (!body) return;
        Array.prototype.forEach.call(body.querySelectorAll('tr[data-city]'), function (tr) {
            var city = String(tr.getAttribute('data-city') || '').trim();
            if (!city) return;
            var flags = getCityServiceFlags(city);
            var prev = store[city] || {};
            var mt = tr.querySelector('.city-markup-type');
            var hotel = tr.querySelector('.city-hotel-markup');
            var other = tr.querySelector('.city-other-markup');
            var dt = tr.querySelector('.city-discount-type');
            var dv = tr.querySelector('.city-discount-value');
            var mtVal = mt ? mt.value : '';
            var hotelMk = 0;
            var otherMk = 0;
            if (mtVal) {
                // Keep prior values while services are still hydrating / temporarily absent
                hotelMk = flags.hasHotel
                    ? (parseFloat(hotel && hotel.value) || 0)
                    : (Number(prev.hotel_markup) || 0);
                otherMk = flags.hasOther
                    ? (parseFloat(other && other.value) || 0)
                    : (Number(prev.other_markup) || 0);
            }
            store[city] = {
                city: city,
                country: String(tr.getAttribute('data-country') || '').trim(),
                currency: String(tr.getAttribute('data-currency') || '').trim(),
                markup_type: mtVal,
                hotel_markup: hotelMk,
                other_markup: otherMk,
                markup_value: hotelMk + otherMk,
                discount_type: dt ? dt.value : '',
                discount_value: parseFloat(dv && dv.value) || 0
            };
        });
    }

    function buildPayloadFromStore() {
        var targets = cityTargets();
        return targets.map(function (t) {
            var e = store[t.city] || emptyEntry(t);
            var flags = getCityServiceFlags(t.city);
            var hotelMk = flags.hasHotel ? (Number(e.hotel_markup) || 0) : 0;
            var otherMk = flags.hasOther ? (Number(e.other_markup) || 0) : 0;
            return {
                city: t.city,
                country: t.country || e.country || '',
                currency: t.currency || e.currency || '',
                markup_type: e.markup_type || null,
                hotel_markup: hotelMk,
                other_markup: otherMk,
                markup_value: hotelMk + otherMk,
                discount_type: e.discount_type || null,
                discount_value: Number(e.discount_value) || 0
            };
        });
    }

    function syncHidden() {
        readRowsIntoStore();
        var payload = buildPayloadFromStore();
        var hidden = document.getElementById('currency_markups');
        if (hidden) hidden.value = JSON.stringify(payload);
        var discHidden = document.getElementById('discount_price');
        if (discHidden) {
            var sum = 0;
            payload.forEach(function (row) {
                if (row.discount_type === 'flat' || row.discount_type === 'foc') {
                    sum += Number(row.discount_value) || 0;
                }
            });
            discHidden.value = String(Math.ceil(sum) || 0);
        }
    }

    function collectPayload() {
        readRowsIntoStore();
        return buildPayloadFromStore();
    }

    function rowHtml(t, entry) {
        var mt = entry.markup_type || '';
        var dt = entry.discount_type || '';
        var hotelMk = entry.hotel_markup != null ? entry.hotel_markup : 0;
        var otherMk = entry.other_markup != null ? entry.other_markup : 0;
        var dv = entry.discount_value != null ? entry.discount_value : 0;
        var markupDisabled = mt ? '' : 'disabled';
        var discountDisabled = (!dt || dt === 'foc') ? 'disabled' : '';
        var suffix = (mt === 'flat') ? (t.currency || 'AMT') : '%';
        return ''
            + '<tr data-city="' + esc(t.city) + '" data-country="' + esc(t.country) + '" data-currency="' + esc(t.currency) + '">'
            + '<td><div class="enquiry-md-city">'
            + '<span class="enquiry-md-city__name">' + esc(t.city) + '</span>'
            + (t.country ? '<span class="enquiry-md-city__meta">' + esc(t.country) + '</span>' : '')
            + '</div></td>'
            + '<td class="enquiry-md-cell-markup">'
            + '<select class="city-markup-type enquiry-md-control">'
            + '<option value=""' + (!mt ? ' selected' : '') + '>Type</option>'
            + '<option value="percentage"' + (mt === 'percentage' ? ' selected' : '') + '>%</option>'
            + '<option value="flat"' + (mt === 'flat' ? ' selected' : '') + '>Fixed</option>'
            + '</select></td>'
            + '<td class="enquiry-md-cell-markup"><div class="enquiry-md-markup-input">'
            + '<input type="number" class="city-hotel-markup enquiry-md-control" value="' + hotelMk + '" step="1" min="0" ' + markupDisabled + ' placeholder="0">'
            + '<span class="city-markup-suffix">' + esc(suffix) + '</span>'
            + '</div></td>'
            + '<td class="enquiry-md-cell-markup"><div class="enquiry-md-markup-input">'
            + '<input type="number" class="city-other-markup enquiry-md-control" value="' + otherMk + '" step="1" min="0" ' + markupDisabled + ' placeholder="0">'
            + '<span class="city-markup-suffix">' + esc(suffix) + '</span>'
            + '</div></td>'
            + '<td class="enquiry-md-cell-discount">'
            + '<select class="city-discount-type enquiry-md-control">'
            + '<option value=""' + (!dt ? ' selected' : '') + '>Type</option>'
            + '<option value="percentage"' + (dt === 'percentage' ? ' selected' : '') + '>%</option>'
            + '<option value="flat"' + (dt === 'flat' ? ' selected' : '') + '>Fixed</option>'
            + '</select></td>'
            + '<td class="enquiry-md-cell-discount">'
            + '<input type="number" class="city-discount-value enquiry-md-control" value="' + dv + '" step="1" min="0" ' + discountDisabled + ' placeholder="0">'
            + '</td></tr>';
    }

    function onRowChange(el) {
        var tr = el && el.closest ? el.closest('tr[data-city]') : null;
        if (!tr) return;
        var city = String(tr.getAttribute('data-city') || '').trim();
        var mt = tr.querySelector('.city-markup-type');
        var dt = tr.querySelector('.city-discount-type');
        var dv = tr.querySelector('.city-discount-value');
        applyServiceLocks(tr, city, !!(mt && mt.value));
        var suffix = (mt && mt.value === 'flat')
            ? (String(tr.getAttribute('data-currency') || '').trim().toUpperCase() || 'AMT')
            : '%';
        tr.querySelectorAll('.city-markup-suffix').forEach(function (s) { s.textContent = suffix; });
        if (dv && dt) {
            if (!dt.value) {
                dv.disabled = true;
                dv.value = 0;
            } else {
                dv.disabled = false;
            }
        }
        syncHidden();
    }

    function refresh() {
        var wrap = document.getElementById('enquiryProMarkupMultiWrap');
        var body = document.getElementById('enquiryProCityMarkupBody');
        var countEl = document.getElementById('enquiryProMarkupCityCount');
        var targets = cityTargets();
        if (countEl) countEl.textContent = String(targets.length);
        if (!wrap || !body) return;
        if (!targets.length) {
            wrap.style.display = 'none';
            body.innerHTML = '';
            syncHidden();
            return;
        }
        wrap.style.display = 'block';
        readRowsIntoStore();
        body.innerHTML = targets.map(function (t) {
            var entry = store[t.city] || emptyEntry(t);
            entry.city = t.city;
            entry.country = t.country || entry.country || '';
            entry.currency = t.currency || entry.currency || '';
            store[t.city] = entry;
            return rowHtml(t, entry);
        }).join('');
        refreshServiceLocks();
    }

    function seed(list) {
        store = {};
        (Array.isArray(list) ? list : []).forEach(function (row) {
            if (!row || typeof row !== 'object') return;
            var city = String(row.city || '').trim();
            if (!city) return;
            var hotelMk = Number(row.hotel_markup != null ? row.hotel_markup : row.markup_value) || 0;
            var otherMk = Number(row.other_markup) || 0;
            store[city] = {
                city: city,
                country: String(row.country || '').trim(),
                currency: String(row.currency || '').trim(),
                markup_type: row.markup_type || '',
                hotel_markup: hotelMk,
                other_markup: otherMk,
                markup_value: hotelMk + otherMk,
                discount_type: row.discount_type || '',
                discount_value: Number(row.discount_value) || 0
            };
        });
        refresh();
    }

    function toggle(headEl) {
        var panel = headEl && headEl.closest ? headEl.closest('.enquiry-md-panel') : null;
        if (!panel) return;
        var collapsed = panel.classList.toggle('is-collapsed');
        headEl.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    }

    function init() {
        var body = document.getElementById('enquiryProCityMarkupBody');
        if (body && !body.__stpMarkupBound) {
            body.__stpMarkupBound = true;
            body.addEventListener('change', function (e) {
                if (e.target && e.target.closest && e.target.closest('tr[data-city]')) onRowChange(e.target);
            });
            body.addEventListener('input', function (e) {
                if (e.target && e.target.closest && e.target.closest('tr[data-city]')) syncHidden();
            });
        }
        document.addEventListener('stp:cities-changed', function () { refresh(); });
        document.addEventListener('stp:service-chunk-changed', function () { refreshServiceLocks(); });
        var seedData = (window.STP_LITE_CONFIG && window.STP_LITE_CONFIG.currencyMarkups) || [];
        if (seedData && seedData.length) seed(seedData);
        else refresh();
    }

    window.StpLiteCityMarkup = {
        init: init,
        refresh: refresh,
        refreshServiceLocks: refreshServiceLocks,
        seed: seed,
        collect: collectPayload,
        toggle: toggle,
        syncHidden: syncHidden
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(window, document);
/* === END city-markup.js === */
